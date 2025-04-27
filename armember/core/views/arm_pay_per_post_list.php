<?php
$filter_search = (!empty($_POST['search'])) ? sanitize_text_field($_POST['search']) : '';//phpcs:ignore
if (isset($_REQUEST['arm_default_paid_post_save'])) {
	do_action('arm_save_default_paid_post', $_REQUEST);
}

wp_enqueue_style('arm_post_metaboxes_css', MEMBERSHIP_URL . '/css/arm_post_metaboxes.css', array(), MEMBERSHIP_VERSION);
wp_enqueue_script('arm_tinymce', MEMBERSHIP_URL . '/js/arm_tinymce_member.js', array(), MEMBERSHIP_VERSION);

?>

<style type="text/css" title="currentStyle">
	.paginate_page a{display:none;}
	#poststuff #post-body {margin-top: 32px;}
	.ColVis_Button{display:none;}
</style>

<script type="text/javascript" charset="utf-8">
// <![CDATA[
jQuery(document).ready( function () {
    arm_load_paid_post_list_grid(false);
});

function arm_load_paid_post_list_filtered_grid(data)
{   
    var tbl = jQuery('#armember_datatable').dataTable(); 
        
    tbl.fnDeleteRow(data);
      
    jQuery('#armember_datatable').dataTable().fnDestroy();
        
    arm_load_paid_post_list_grid();
}

function show_grid_loader() {
    jQuery('.arm_loading_grid').show();
}

function arm_load_paid_post_list_grid(is_filtered){

	var __ARM_Showing = '<?php echo addslashes(esc_html__('Showing','ARMember')); //phpcs:ignore?>';
    var __ARM_Showing_empty = '<?php echo addslashes(esc_html__('Showing 0 to 0 of 0 entries','ARMember')); //phpcs:ignore?>';
    var __ARM_to = '<?php echo addslashes(esc_html__('to','ARMember')); //phpcs:ignore?>';
    var __ARM_of = '<?php echo addslashes(esc_html__('of','ARMember')); //phpcs:ignore?>';
    var __ARM_Entries = ' <?php echo addslashes(esc_html__('entries','ARMember')); //phpcs:ignore?>';
    var __ARM_Show = '<?php echo addslashes(esc_html__('Show','ARMember')); //phpcs:ignore?> ';
    var __ARM_NO_FOUND = '<?php echo addslashes(esc_html__('No paid post found.','ARMember')); //phpcs:ignore?>';
    var __ARM_NO_MATCHING = '<?php echo addslashes(esc_html__('No matching records found.','ARMember')); //phpcs:ignore?>';
	var nonce=jQuery('input[name="arm_wp_nonce"]').val();

	var ajax_url = '<?php echo esc_url(admin_url("admin-ajax.php")); //phpcs:ignore?>';
	var table = jQuery('#armember_datatable').dataTable({
		"sDom": '<"H"Cfr>t<"footer"ipl>',
		"sPaginationType": "four_button",
		"sProcessing": show_grid_loader(),
        "oLanguage": {
            "sProcessing": show_grid_loader(),
            "sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_Entries,
            "sInfoEmpty": __ARM_Showing_empty,
           
            "sLengthMenu": __ARM_Show + "_MENU_" + __ARM_Entries,
            "sEmptyTable": __ARM_NO_FOUND,
            "sZeroRecords": __ARM_NO_MATCHING,
        },
        "language":{
            "searchPlaceholder": "Search",
            "search":"",
        },
        "bProcessing": false,
        "bServerSide": true,
        "sAjaxSource": ajax_url,
		"bJQueryUI": true,
		"bPaginate": true,
		"sServerMethod": "POST",
		"bAutoWidth" : false,
		"aaSorting": [],
		"aoColumnDefs": [
			{ "bVisible": false, "aTargets": [] },
			{ "sClass": 'center', "aTargets": [3]},
			{ "bSortable": false, "aTargets": [2,3] }
		],

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
		"fnPreDrawCallback": function () {
            show_grid_loader();
        },
        "fnCreatedRow": function (nRow, aData, iDataIndex) {
            jQuery(nRow).find('.arm_grid_action_wrapper').each(function () {
                jQuery(this).parent().addClass('armGridActionTD');
                jQuery(this).parent().attr('data-key', 'armGridActionTD');
            });
        },
        "fnStateLoadParams": function (oSettings, oData) {
            oData.iLength = 10;
            oData.iStart = 0;
        },
		"fnServerParams": function (aoData) {
            aoData.push({'name': 'action', 'value': 'get_paid_post_data'});
			aoData.push({'name': '_wpnonce', 'value': nonce});
        },
		"fnDrawCallback":function(){
			arm_show_data()
			jQuery('.arm_loading_grid').hide();
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
		}
	});
	var filter_box = jQuery('#arm_filter_wrapper').html();
	jQuery('div#armember_datatable_filter').parent().append(filter_box);
	jQuery('#arm_filter_wrapper').remove();
}
function ChangeID(id) {
	document.getElementById('delete_id').value = id;
}

// ]]>
</script>

<?php

$get_msg = !empty($_GET['msg']) ? esc_html( sanitize_text_field($_GET['msg']) ) : '';
if( isset( $_GET['status'] ) && 'success' == $_GET['status'] ){
	echo "<script type='text/javascript'>";
		echo "jQuery(document).ready(function(){";
			echo "armToast('" . $get_msg . "','success');"; //phpcs:ignore
			echo "var pageurl = ArmRemoveVariableFromURL( document.URL, 'status' );";  
			echo "pageurl = ArmRemoveVariableFromURL( pageurl, 'msg' );";  
			echo "window.history.pushState( { path: pageurl }, '', pageurl );";
		echo "});";
	echo "</script>";
}

$filter_search = (!empty($_POST['search'])) ? sanitize_text_field($_POST['search']) : '';//phpcs:ignore

global $arm_members_activity;
$setact = 0;
global $check_sorting;
$setact = $arm_members_activity->$check_sorting();

global $wpdb, $ARMember, $arm_global_settings;
$user_table = $ARMember->tbl_arm_members;
$user_meta_table = $wpdb->usermeta;

$PaidPostContentTypes = array('page' => esc_html__('Page', 'ARMember'), 'post' => esc_html__('Post', 'ARMember'));
$custom_post_types = get_post_types(array('public' => true, '_builtin' => false, 'show_ui' => true), 'objects');
if (!empty($custom_post_types)) {
	foreach ($custom_post_types as $cpt) {
		$PaidPostContentTypes[$cpt->name] = $cpt->label;
	}
}
?>
<div class="wrap arm_page arm_paid_posts_main_wrapper">
	<?php
    if ($setact != 1) {
        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        ?>
        <div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 0px 10px 10px;background-color:#ffffff;color:#000000;font-size:16px;display:block;visibility:visible;text-align:left;" >ARMember License is not activated. Please activate license from <a href="<?php echo esc_url($admin_css_url); ?>">here</a></div>
    <?php } ?>
	<div class="content_wrapper arm_paid_posts_wrapper arm_position_relative" id="content_wrapper" >
		<div class="arm_loading_grid" style="display: none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL; //phpcs:ignore?>/loader.gif" alt="Loading.."></div>
		<div class="page_title">
			<?php esc_html_e('Manage Paid Posts','ARMember');?>
			<div class="arm_add_new_item_box">
				<a class="greensavebtn arm_add_paid_post_link" href="<?php echo admin_url('admin.php?page=arm_manage_pay_per_post&action=add_paid_post'); ?>"><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL //phpcs:ignore?>/add_new_icon.png"><span><?php esc_html_e('Add Paid Post', 'ARMember') ?></span></a>
			</div>	
			<div class="armclear"></div>
		</div>

			

		<div class="armclear"></div>

		<div class="arm_paid_posts_list arm_main_wrapper_seperator" >
			<form method="GET" id="subscription_plans_list_form" class="data_grid_list" onsubmit="return apply_bulk_action_subscription_plans_list();">
				<input type="hidden" name="page" value="<?php echo isset( $arm_slugs->paid_post ) ? esc_attr($arm_slugs->paid_post) : '';?>" />
				<input type="hidden" name="armaction" value="list" />

				<div id="armmainformnewlist">
					<table cellpadding="0" cellspacing="0" border="0" class="display arm_on_display arm_hide_datatable" id="armember_datatable">
						<thead>
							<tr>
								<?php /*<th style="max-width:140px"><?php esc_html_e('Enable / Disable Paid Post','ARMember');?></th>*/ ?>
								<th class="arm_min_width_50"><?php esc_html_e('Post ID','ARMember');?></th>
								<th class="arm_min_width_200"><?php esc_html_e('Post Title','ARMember');?></th>
								<th class="arm_max_width_100"><?php esc_html_e('Post Type','ARMember');?></th>
								<th class="arm_width_200"><?php esc_html_e('Paid Post Members','ARMember');?></th>
								
								
								<th class="armGridActionTD"></th>
							</tr>
						</thead>
					</table>
					<div class="armclear"></div>
					<input type="hidden" name="show_hide_columns" id="show_hide_columns" value="<?php esc_html_e('Show / Hide columns','ARMember');?>"/>
					<input type="hidden" name="search_grid" id="search_grid" value="<?php esc_html_e('Search','ARMember');?>"/>
					<input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_html_e('plans','ARMember');?>"/>
					<input type="hidden" name="show_grid" id="show_grid" value="<?php esc_html_e('Show','ARMember');?>"/>
					<input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_html_e('Showing','ARMember');?>"/>
					<input type="hidden" name="to_grid" id="to_grid" value="<?php esc_html_e('to','ARMember');?>"/>
					<input type="hidden" name="of_grid" id="of_grid" value="<?php esc_html_e('of','ARMember');?>"/>
					<input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_html_e('No matching plans found','ARMember');?>"/>
					<input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_html_e('No any subscription plan found.','ARMember');?>"/>
					<input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_html_e('filtered from','ARMember');?>"/>
					<input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_html_e('total','ARMember');?>"/>
					<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
                    <input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
				</div>
				<div class="footer_grid"></div>
			</form>
		</div>
		<div class="armclear"></div>
		<br>
		
	</div>
</div>

<div class="arm_member_view_detail_container"></div>

<!--./******************** Paid Post Members List ********************/.-->
<div class="arm_members_list_detail_popup popup_wrapper arm_members_list_detail_popup_wrapper" >
	<div class="arm_loading_grid" id="arm_loading_grid_members" style="display: none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL; //phpcs:ignore?>/loader.gif" alt="Loading.."></div>
    <div class="popup_wrapper_inner" style="overflow: hidden;">
        <div class="popup_header">
            <span class="popup_close_btn arm_popup_close_btn arm_members_list_detail_close_btn"></span>
            <span class="add_rule_content"><?php esc_html_e('Members Details', 'ARMember'); ?><span class="arm_member_paid_post_name"></span></span>
        </div>
        <div class="popup_content_text arm_members_list_detail_popup_text">
            <table width="100%" cellspacing="0" class="display arm_min_width_802" id="armember_datatable_1" >
                <thead>
                    <tr>
                        <th><?php esc_html_e('Username', 'ARMember'); ?></th>
                        <th><?php esc_html_e('Email', 'ARMember'); ?></th>
                        <th class="arm-no-sort arm_width_170" ><?php esc_html_e('View Detail', 'ARMember'); ?></th>
                    </tr>
                </thead>
            </table>
            <input type="hidden" name="search_grid" id="search_grid" value="<?php esc_html_e('Search','ARMember');?>"/>
            <input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_html_e('members','ARMember');?>"/>
            <input type="hidden" name="show_grid" id="show_grid" value="<?php esc_html_e('Show','ARMember');?>"/>
            <input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_html_e('Showing','ARMember');?>"/>
            <input type="hidden" name="to_grid" id="to_grid" value="<?php esc_html_e('to','ARMember');?>"/>
            <input type="hidden" name="of_grid" id="of_grid" value="<?php esc_html_e('of','ARMember');?>"/>
            <input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_html_e('No matching members found','ARMember');?>"/>
            <input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_html_e('There is no any member found.','ARMember');?>"/>
            <input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_html_e('filtered from','ARMember');?>"/>
            <input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_html_e('total','ARMember');?>"/>
            <?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
            <input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
        </div>
        <div class="armclear"></div>
    </div>
</div>
<!--./******************** Add New Paid Post Form ********************/.-->


<div class="arm_paid_post_cycle_detail_popup popup_wrapper arm_import_user_list_detail_popup_wrapper <?php echo (is_rtl()) ? 'arm_page_rtl' : ''; ?>" style="width:850px; min-height: 200px;">    
    <div>
        <div class="popup_header">
            <span class="popup_close_btn arm_popup_close_btn arm_paid_post_cycle_detail_close_btn"></span>
            <input type="hidden" id="arm_edit_plan_user_id" />
            <span class="add_rule_content"><?php esc_html_e('Paid Post Cycles', 'ARMember'); ?> <span class="arm_paid_post_name"></span></span>
        </div>
        <div class="popup_content_text arm_paid_post_cycle_text arm_text_align_center" >
        	<div class="arm_width_100_pct" style="margin: 45px auto;">	<img src="<?php echo MEMBERSHIP_IMAGES_URL."/arm_loader.gif"; //phpcs:ignore?>"></div>
        </div>
        <div class="armclear"></div>
    </div>

</div>
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

<script type="text/javascript" charset="utf-8">
var ARM_IMAGE_URL = "<?php echo MEMBERSHIP_IMAGES_URL; //phpcs:ignore?>";
// <![CDATA[
jQuery(window).on("load", function () {
	document.onkeypress = stopEnterKey;
});
// ]]>
jQuery(document).ready( function ($) {
	jQuery(document).on('click', '.arm_remove_selected_itembox', function () {
		jQuery(this).parents('.arm_paid_post_itembox').remove();
		if(jQuery('#arm_paid_post_items .arm_paid_post_itembox').length == 0) {
			jQuery('#arm_paid_post_items_input').attr('required', 'required');
			jQuery('#arm_paid_post_items').hide();
		}
		return false;
	});	
});



</script>
<?php
	echo $ARMember->arm_get_need_help_html_content('paid-posts-list'); //phpcs:ignore
?>