<?php
global $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_access_rules, $arm_subscription_plans, $arm_drip_rules;
$dripRulesMembers = array();
$all_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');
$date_format = $arm_global_settings->arm_get_wp_date_format();
$drip_types = $arm_drip_rules->arm_drip_rule_types();

$filter_search = (!empty($_POST['search'])) ? sanitize_text_field($_POST['search']) : '';//phpcs:ignore
$filter_dctype = (!empty($_POST['dctype'])) ? sanitize_text_field($_POST['dctype']) : '0';//phpcs:ignore
$filter_plan_id = (!empty($_POST['plan_id']) && $_POST['plan_id'] != '0') ? intval($_POST['plan_id']) : '';//phpcs:ignore
$filter_drip_type = (!empty($_POST['drip_type']) && $_POST['drip_type'] != '0') ? sanitize_text_field($_POST['drip_type']) : '0';//phpcs:ignore

/* Custom Post Types */
$custom_post_types = get_post_types(array('public' => true, '_builtin' => false, 'show_ui' => true), 'objects');
$dripContentTypes = array('page' => esc_html__('Page', 'ARMember'), 'post' => esc_html__('Post', 'ARMember'));
if (!empty($custom_post_types)) {
	foreach ($custom_post_types as $cpt) {
		$dripContentTypes[$cpt->name] = $cpt->label;
	}
}
/* Add `Custom Content` Option */
$dripContentTypes['custom_content'] = esc_html__('Custom Content', 'ARMember');
?>
<script type="text/javascript">
// <![CDATA[
jQuery(document).ready( function () {
   arm_load_drip_rules_list_grid(false);

   jQuery('#armmanagesearch_new_drip').on('keyup', function (e) {
	   e.stopPropagation();
	   var arm_check_disable = jQuery('#arm_drip_rule_grid_filter_btn').attr('disabled');
		if (e.keyCode == 13 && arm_check_disable!='disabled') {
			arm_load_drip_rules_list_filtered_grid();
			return false;
		}
	});
    
});

function arm_load_drip_rules_list_filtered_grid(data)
{
	jQuery('#arm_drip_rule_grid_filter_btn').attr('disabled', 'disabled');
	jQuery('#armember_datatable').dataTable().fnDestroy();
	arm_load_drip_rules_list_grid(true);
}
function arm_load_drip_rules_list_grid(is_filtered) {


	var ajax_url = '<?php echo esc_url(admin_url("admin-ajax.php"));?>';
	var __ARM_Showing = '<?php echo addslashes(esc_html__('Showing','ARMember')); //phpcs:ignore?>';
    var __ARM_Showing_empty = '<?php echo addslashes(esc_html__('Showing 0 to 0 of 0 entries','ARMember')); //phpcs:ignore?>';
    var __ARM_to = '<?php echo addslashes(esc_html__('to','ARMember')); //phpcs:ignore?>';
    var __ARM_of = '<?php echo addslashes(esc_html__('of','ARMember')); //phpcs:ignore?>';
    var __ARM_RECORDS = '<?php echo addslashes(esc_html__('entries','ARMember')); //phpcs:ignore?>';
    var __ARM_Show = '<?php echo addslashes(esc_html__('Show','ARMember')); //phpcs:ignore?>';
    var __ARM_NO_FOUND = '<?php echo addslashes(esc_html__('No any record found.','ARMember')); //phpcs:ignore?>';
    var __ARM_NO_MATCHING = '<?php echo addslashes(esc_html__('No matching records found.','ARMember')); //phpcs:ignore?>';
	var filtered_data = (typeof is_filtered !== 'undefined' && is_filtered !== false) ? true : false;
	var filter_search = jQuery('#armmanagesearch_new_drip').val();
	var filter_plan_id = jQuery('#arm_filter_dplan_id').val();
	var filter_drip_type = jQuery('#arm_filter_drip_type').val();
	var filter_dctype = jQuery('#arm_filter_dctype').val();
	var _wpnonce = jQuery('input[name="arm_wp_nonce"]').val();

	
	var oTables = jQuery('#armember_datatable').dataTable({
            "oLanguage": {
                "sProcessing": show_grid_loader(),
                "sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_RECORDS,
                "sInfoEmpty": __ARM_Showing_empty,
               
                "sLengthMenu": __ARM_Show + "_MENU_" + __ARM_RECORDS,
                "sEmptyTable": __ARM_NO_FOUND,
                "sZeroRecords": __ARM_NO_MATCHING,
            },
            "bDestroy": true,
            "language":{
                "searchPlaceholder": "Search",
                "search":"",
            },
            "buttons":[{
                "extend":"colvis",
                "columns":":not(.noVis)",
                "className":"ColVis_Button TableTools_Button ui-button ui-state-default ColVis_MasterButton",
                "text":"<span class=\"armshowhideicon\" style=\"background-image: url(<?php echo MEMBERSHIP_IMAGES_URL; //phpcs:ignore?>/show_hide_icon.png);background-repeat: no-repeat;background-position: 0 center;padding: 0 0 0 30px;\"><?php esc_html_e('Show / Hide columns','ARMember');?></span>",
            }],
            "bProcessing": false,
            "bServerSide": true,
            "sAjaxSource": ajax_url,
            "sServerMethod": "POST",
            "fnServerParams": function (aoData) {
                aoData.push({'name': 'action', 'value': 'arm_filter_drip_rules_list'});
                aoData.push({'name': 'search', 'value': filter_search});
                aoData.push({'name': 'plan_id', 'value': filter_plan_id});
                aoData.push({'name': 'drip_type', 'value': filter_drip_type});
                aoData.push({'name': 'dctype','value': filter_dctype});
                aoData.push({'name': 'sColumns', 'value':null});
                aoData.push({'name': '_wpnonce', 'value': _wpnonce});
            },
            "bRetrieve": false,
            "sDom": '<"H"CBfr>t<"footer"ipl>',
            "sPaginationType": "four_button",
            "bJQueryUI": true,
            "bPaginate": true,
            "bAutoWidth": false,
            "sScrollX": "100%",
            "bScrollCollapse": true,
            "aoColumnDefs": [
				{ "sType": "html", "bVisible": false, "aTargets": [] },
				{"sClass": "center", "aTargets": [0]},
                {"bSortable": false, "aTargets": [ 0, 1, 2, 3, 4, 5, 6, 7]},
            ],
            "fixedColumns": false,
            "bStateSave": true,
            "iCookieDuration": 60 * 60,
            "sCookiePrefix": "arm_datatable_",
            "aLengthMenu": [10, 25, 50, 100, 150, 200],
            "fnStateSave": function (oSettings, oData) {
                oData.aaSorting = [];
                oData.abVisCols = [];
                oData.aoSearchCols = [];
                this.oApi._fnCreateCookie(
                    oSettings.sCookiePrefix + oSettings.sInstance,
                    this.oApi._fnJsonString(oData),
                    oSettings.iCookieDuration,
                    oSettings.sCookiePrefix,
                    oSettings.fnCookieCallback
                );
            },
            "stateSaveParams":function(oSettings,oData){
                oData.start=0;
            },
            "fnStateLoadParams": function (oSettings, oData) {
                oData.iLength = 10;
                oData.iStart = 1;
                //oData.oSearch.sSearch = db_search_term;
            },
            "fnPreDrawCallback": function () {
                show_grid_loader();
            },
			"fnCreatedRow": function (nRow, aData, iDataIndex) {
                jQuery(nRow).find('.arm_grid_action_btn_container').each(function () {
                    jQuery(this).parent().addClass('armGridActionTD');
                    jQuery(this).parent().attr('data-key', 'armGridActionTD');
                });
            },
            "fnDrawCallback": function (oSettings) {
                jQuery('.arm_loading_grid').hide();
                arm_show_data();
		jQuery(".cb-select-all-th").removeClass('sorting_asc').addClass('sorting_disabled');
                jQuery("#cb-select-all-1").prop("checked", false);
                arm_selectbox_init();
                jQuery('#arm_filter_wrapper').hide();
                filtered_data = false;
                if (jQuery.isFunction(jQuery().tipso)) {
                    jQuery('.armhelptip').each(function () {
                        jQuery(this).tipso({
                            position: 'top',
                            size: 'small',
                            background: '#939393',
                            color: '#ffffff',
                            width: false,
                            maxWidth: 400,
                            useTitle: true
                        });
                    });
                }
                oTables.dataTable().fnAdjustColumnSizing(false);
                jQuery('#arm_drip_rule_grid_filter_btn').removeAttr('disabled');
            }
        });
		var filter_box = jQuery('#arm_filter_wrapper').html();
        jQuery('.arm_filter_grid_list_container').find('.arm_datatable_filters_options').remove();
		jQuery('div#armember_datatable_filter').parent().append(filter_box);
		jQuery('div#armember_datatable_filter').hide();
	}
// ]]>
function show_grid_loader() {
        jQuery(".arm_hide_datatable").css('visibility', 'hidden');
        jQuery('.arm_loading_grid').show();
    }
</script>
<?php if (!empty($all_plans)) { ?>
<div class="arm_filter_wrapper" id="arm_filter_wrapper_after_filter" style="display:none;">
			<div class="arm_datatable_filters_options">
				<div class='sltstandard'>
					<input type="hidden" id="arm_drip_rule_bulk_action" name="action1" value="-1" />
					<dl class="arm_selectbox column_level_dd arm_width_200">
						<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
						<dd>
							<ul data-id="arm_drip_rule_bulk_action">
								<li data-label="<?php esc_attr_e('Bulk Actions','ARMember');?>" data-value="-1"><?php esc_html_e('Bulk Actions','ARMember');?></li>
								<li data-label="<?php esc_attr_e('Delete', 'ARMember');?>" data-value="delete_drip_rule"><?php esc_html_e('Delete', 'ARMember');?></li>
							</ul>
						</dd>
					</dl>
				</div>
				<input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php esc_attr_e('Go','ARMember');?>"/>
			</div>
		</div>
	<div class="arm_drip_rule_list">
		<div class="arm_filter_wrapper" id="arm_filter_wrapper" style="display:none;">
			<div class="arm_datatable_filters_options">
				<div class='sltstandard'>
					<input type="hidden" id="arm_drip_rule_bulk_action" name="action1" value="-1" />
					<dl class="arm_selectbox column_level_dd arm_width_200">
						<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
						<dd>
							<ul data-id="arm_drip_rule_bulk_action">
								<li data-label="<?php esc_attr_e('Bulk Actions','ARMember');?>" data-value="-1"><?php esc_html_e('Bulk Actions','ARMember');?></li>
								<li data-label="<?php esc_attr_e('Delete', 'ARMember');?>" data-value="delete_drip_rule"><?php esc_html_e('Delete', 'ARMember');?></li>
							</ul>
						</dd>
					</dl>
				</div>
				<input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php esc_attr_e('Go','ARMember');?>"/>
			</div>
		</div>
		<form method="GET" id="drip_rule_list_form" class="data_grid_list drip_rule_list_form" onsubmit="return apply_bulk_action_drip_list();">
			<input type="hidden" name="page" value="<?php echo esc_attr($arm_slugs->drip_rules);?>" />
			<input type="hidden" name="armaction" value="list" />
			<div class="arm_datatable_filters">
				<div class="arm_dt_filter_block arm_datatable_searchbox">
					<div class="arm_datatable_filter_item">
						<label><input type="text" placeholder="<?php esc_attr_e('Search', 'ARMember');?>" id="armmanagesearch_new_drip" value="<?php echo esc_attr($filter_search);?>" tabindex="-1"></label>
					</div>
					<!--./====================Begin Filter By Content Type Box====================/.-->
					<div class="arm_datatable_filter_item arm_filter_dctype_label">
						<input type="hidden" id="arm_filter_dctype" class="arm_filter_dctype" value="<?php echo esc_attr($filter_dctype);?>" />
						<dl class="arm_selectbox column_level_dd arm_width_200">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_filter_dctype">
									<li data-label="<?php esc_attr_e('Select Content Type','ARMember');?>" data-value="0"><?php esc_html_e('Select Content Type','ARMember');?></li>
									<?php 
									if (!empty($dripContentTypes)) {
										foreach ($dripContentTypes as $key => $val) {
											?><li data-label="<?php echo esc_attr($val);?>" data-value="<?php echo esc_attr($key);?>"><?php echo esc_html($val);?></li><?php
										}
									}
									?>
								</ul>
							</dd>
						</dl>
					</div>
					<!--./====================End Filter By Content Type Box====================/.-->
					<!--./====================Begin Filter By Plan Box====================/.-->
					<?php if (!empty($all_plans)): ?>
					<div class="arm_datatable_filter_item arm_filter_plan_id_label">
						<input type="hidden" id="arm_filter_dplan_id" class="arm_filter_dplan_id" value="<?php echo esc_attr($filter_plan_id);?>" />
						<dl class="arm_multiple_selectbox arm_width_200">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_filter_dplan_id" data-placeholder="<?php esc_attr_e('Select Plans', 'ARMember');?>">
									<?php foreach ($all_plans as $plan): ?>
									<li data-label="<?php echo esc_html(stripslashes($plan['arm_subscription_plan_name'])); ?>" data-value="<?php echo esc_attr($plan['arm_subscription_plan_id']); ?>"><input type="checkbox" class="arm_icheckbox" value="<?php echo esc_attr($plan['arm_subscription_plan_id']);?>"/><?php echo esc_html(stripslashes($plan['arm_subscription_plan_name'])); ?></li>
									<?php endforeach;?>
								</ul>
							</dd>
						</dl>
					</div>
					<?php endif;?>
					<!--./====================End Filter By Plan Box====================/.-->
					<!--./====================Begin Filter By Drip Type====================/.-->
					<div class="arm_datatable_filter_item arm_filter_drip_type_label" style="">
						<input type="hidden" id="arm_filter_drip_type" class="arm_filter_drip_type" value="<?php echo esc_attr($filter_drip_type);?>" />
						<dl class="arm_selectbox column_level_dd arm_width_230">
							<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
							<dd>
								<ul data-id="arm_filter_drip_type">
									<li data-label="<?php esc_attr_e('Select Drip Type','ARMember');?>" data-value="0"><?php esc_html_e('Select Drip Type','ARMember');?></li>
									<?php 
									if (!empty($dripContentTypes)) {
										foreach ($drip_types as $key => $val) {
											?><li data-label="<?php echo esc_attr($val);?>" data-value="<?php echo esc_attr($key);?>"><?php echo esc_html($val);?></li><?php
										}
									}
									?>
								</ul>
							</dd>
						</dl>
					</div>
					<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
					<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
					<!--./====================End Filter By Drip Type====================/.-->
				</div>
				<div class="arm_dt_filter_block arm_dt_filter_submit">
					<input type="button" class="armemailaddbtn" id="arm_drip_rule_grid_filter_btn" onclick="arm_load_drip_rules_list_grid();" value="<?php esc_attr_e('Apply','ARMember');?>"/>
				</div>
				<div class="armclear"></div>
			</div>
			<div id="armmainformnewlist" class="arm_filter_grid_list_container">
				<div class="arm_loading_grid" style="display: none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL; //phpcs:ignore?>/loader.gif" alt="Loading.."></div>
				<div class="response_messages"></div>
				<table cellpadding="0" cellspacing="0" border="0" class="display arm_hide_datatable" id="armember_datatable">
					<thead>
						<tr>
							<th class="center cb-select-all-th arm_max_width_60 arm_width_80" ><input id="cb-select-all-1" type="checkbox" class="chkstanard"></th>
							<th class="arm_width_80 center"><?php esc_html_e('Enable','ARMember');?></th>
							<th class="arm_width_120"><?php esc_html_e('Content Type','ARMember');?></th>
							<th class="arm_width_250"><?php esc_html_e('Page/Post Name','ARMember');?></th>
							<th class="arm_width_400"><?php esc_html_e('Drip Type', 'ARMember'); ?></th>
							<th class="arm_width_200"><?php esc_html_e('Shortcode','ARMember');?></th>
							<th class="arm_width_300"><?php esc_html_e('Plans','ARMember');?></th>
							<th class="armGridActionTD"></th>
						</tr>
					</thead>
					<tbody id="arm_drip_rules_wrapper">
					</tbody>
				</table>
				<div class="armclear"></div>
				<input type="hidden" name="search_grid" id="search_grid" value="<?php esc_attr_e('Search','ARMember');?>"/>
				<input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_attr_e('rules','ARMember');?>"/>
				<input type="hidden" name="show_grid" id="show_grid" value="<?php esc_attr_e('Show','ARMember');?>"/>
				<input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_attr_e('Showing','ARMember');?>"/>
				<input type="hidden" name="to_grid" id="to_grid" value="<?php esc_attr_e('to','ARMember');?>"/>
				<input type="hidden" name="of_grid" id="of_grid" value="<?php esc_attr_e('of','ARMember');?>"/>
				<input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_attr_e('No matching rule found','ARMember');?>"/>
				<input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_attr_e('No any rule found.','ARMember');?>"/>
				<input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_html_e('filtered from', 'ARMember'); ?>"/>
				<input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_html_e('total', 'ARMember'); ?>"/>
			 </div>
			 <div class="footer_grid"></div>
		</form>
	</div>
<?php 
} else {
	?>
<h4 class="arm_no_access_rules_message"><?php esc_html_e('There is no any plan configured yet', 'ARMember'); ?>, <a href="<?php echo esc_url(admin_url('admin.php?page=' . $arm_slugs->manage_plans . '&action=new')); ?>" class="arm_ref_info_links" target="_blank"><?php esc_html_e('Please add new plan.', 'ARMember'); ?></a></h4>
	<?php
}
?>
<script type="text/javascript">
    __ARM_Showing = '<?php esc_html_e('Showing','ARMember'); ?>';
    __ARM_Showing_empty = '<?php esc_html_e('Showing 0 to 0 of 0 members','ARMember'); ?>';
    __ARM_to = '<?php esc_html_e('to','ARMember'); ?>';
    __ARM_of = '<?php esc_html_e('of','ARMember'); ?>';
    __ARM_members = '<?php esc_html_e('members','ARMember'); ?>';
    __ARM_Show = '<?php esc_html_e('Show','ARMember'); ?>';
    __ARM_NO_FOUNT = '<?php esc_html_e('No any member found.','ARMember'); ?>';
    __ARM_NO_MATCHING = '<?php esc_html_e('No matching members found.','ARMember'); ?>';
</script>
