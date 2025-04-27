<?php 
global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings, $arm_social_feature, $arm_slugs, $arm_subscription_plans, $arm_manage_communication;

$arm_all_email_settings = $arm_email_settings->arm_get_all_email_settings();
$template_list = $arm_email_settings->arm_get_all_email_template();
$messages = $wpdb->get_results("SELECT * FROM `".$ARMember->tbl_arm_auto_message."` ORDER BY `arm_message_id` DESC"); //phpcs:ignore --Reason $ARMember->tbl_arm_auto_message is a table name and query is without where so need to skip

$get_page = !empty($_GET['page']) ? sanitize_text_field( $_GET['page'] ) : '';

$form_id = 'arm_add_message_wrapper_frm';
$mid = 0;
$edit_mode = false;
$msg_type = 'on_new_subscription';

?>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
var current_language = '<?php echo !empty($local) ? $local : ''; //phpcs:ignore?>';

function arm_load_communication_list_filtered_grid(data)
{
    var tbl = jQuery('#armember_datatable_1').dataTable(); 
        
        tbl.fnDeleteRow(data);
      
        jQuery('#armember_datatable_1').dataTable().fnDestroy();
        arm_load_communication_messages_list_grid();
}

function arm_load_communication_messages_list_grid() {
	
	
	jQuery('#armember_datatable_1').dataTable({
		"sDom": '<"H"Cfr>t<"footer"ipl>',
		"sPaginationType": "four_button",
		"oLanguage": {
			"sEmptyTable": "No any automated email message found.",
			"sZeroRecords": "No matching records found."
			},
		"bJQueryUI": true,
		"bPaginate": true,
		"bAutoWidth": false,
		"aaSorting": [],
		"aoColumnDefs": [
			{"bVisible": false, "aTargets": []},
			{"bSortable": false, "aTargets": [0, 2, 5]}
		],
		"language":{
            "searchPlaceholder": "Search",
            "search":"",
        },
		"oColVis": {
			"aiExclude": [0, 5]
		},
		"fnDrawCallback": function () {
			jQuery("#cb-select-all-1").prop("checked", false);
		},
	});
        
	var filter_box = jQuery('#arm_filter_wrapper_after_filter').html();
         
	jQuery('div#armember_datatable_1_filter').parent().append(filter_box);
	jQuery('#arm_filter_wrapper').remove(); 
	
}

// ]]>
</script>
<div class="arm_email_notifications_main_wrapper">

<?php if(!empty($messages)):?>
	<div class="arm_solid_divider"></div>
        <div class="arm_filter_wrapper" id="arm_filter_wrapper_after_filter" style="display:none;">
			<div class="arm_datatable_filters_options">
				<div class='sltstandard'>
					<input type="hidden" id="arm_communication_bulk_action1" name="action1" value="-1" />
					<dl class="arm_selectbox column_level_dd arm_width_250">
						<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
						<dd>
							<ul data-id="arm_communication_bulk_action1">
								<li data-label="<?php esc_attr_e('Bulk Actions','ARMember');?>" data-value="-1"><?php esc_html_e('Bulk Actions','ARMember');?></li>
								<li data-label="<?php esc_attr_e('Delete', 'ARMember');?>" data-value="delete_communication"><?php esc_html_e('Delete', 'ARMember');?></li>
							</ul>
						</dd>
					</dl>
				</div>
				<input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php esc_attr_e('Go','ARMember');?>"/>
			</div>
		</div>
	<div class="page_sub_content">
		<div class="page_sub_title" style="float: <?php echo (is_rtl()) ? 'right' : 'left';?>;" ><?php esc_html_e('Automated Email Messages','ARMember');?></div>
		<div class="arm_add_new_item_box" style="margin: 0 0 20px 0;">			
			<a class="greensavebtn arm_add_new_message_btn arm_margin_right_40" href="javascript:void(0);" ><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL //phpcs:ignore?>/add_new_icon.png"><span><?php esc_html_e('Add New Response', 'ARMember') ?></span></a>
		</div>
		<div class="armclear"></div>
		<div class="arm_filter_wrapper" id="arm_filter_wrapper" style="display:none;">
			<div class="arm_datatable_filters_options">
				<div class='sltstandard'>
					<input type="hidden" id="arm_communication_bulk_action1" name="action1" value="-1" />
					<dl class="arm_selectbox column_level_dd arm_width_120">
						<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"  /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
						<dd>
							<ul data-id="arm_communication_bulk_action1">
								<li data-label="<?php esc_attr_e('Bulk Actions','ARMember');?>" data-value="-1"><?php esc_html_e('Bulk Actions','ARMember');?></li>
								<li data-label="<?php esc_attr_e('Delete', 'ARMember');?>" data-value="delete_communication"><?php esc_html_e('Delete', 'ARMember');?></li>
							</ul>
						</dd>
					</dl>
				</div>
				<input type="submit" id="doaction1" class="armbulkbtn armemailaddbtn" value="<?php esc_attr_e('Go','ARMember');?>"/>
			</div>
		</div>
		<form method="GET" id="communication_list_form" class="data_grid_list arm_email_settings_wrapper" onsubmit="return apply_bulk_action_communication_list();return false;">
			<input type="hidden" name="page" value="<?php echo esc_attr( $get_page ); ?>" />
			<input type="hidden" name="armaction" value="list" />
			<div id="armmainformnewlist">
				<table cellpadding="0" cellspacing="0" border="0" class="display" id="armember_datatable_1">
					<thead>
						<tr>
							<th class="cb-select-all-th arm_max_width_60 arm_padding_left_17"><input id="cb-select-all-1" type="checkbox" class="chkstanard"></th>
							<th style="text-align: <?php echo (is_rtl()) ? 'right' : 'left';?>;"><?php esc_html_e('Message Subject', 'ARMember');?></th>
							<th class="arm_width_100 arm_text_align_center"><?php esc_html_e('Active', 'ARMember');?></th>
							<th style="text-align: <?php echo (is_rtl()) ? 'right' : 'left';?>;"><?php esc_html_e('Subscription', 'ARMember');?></th>
							<th style="text-align: <?php echo (is_rtl()) ? 'right' : 'left';?>;"><?php esc_html_e('Type', 'ARMember');?></th>
							<th class="armGridActionTD"></th>
						</tr>
					</thead>
					<tbody id="">
					<?php if(!empty($messages)):?>
					<?php 
					foreach ($messages as $key => $rc) {
						$messageID = $rc->arm_message_id;
						$edit_link = admin_url('admin.php?page=' . $arm_slugs->email_notifications . '&action=edit_communication&message_id=' . $messageID);
						?>
						<tr class="arm_message_tr_<?php echo esc_attr($messageID);?> row_<?php echo esc_attr($messageID);?>">
							<td class="arm_padding_left_17">
								<input class="chkstanard arm_bulk_select_single" type="checkbox" value="<?php echo esc_attr($messageID);?>" name="item-action[]">
							</td>
							<td>
								<a class="arm_edit_message_btn" href="javascript:void(0);" data-message_id="<?php echo esc_attr($messageID);?>"><?php echo esc_html(stripslashes($rc->arm_message_subject));?></a>
							</td>
							<td class="center"><?php 
								$switchChecked = ($rc->arm_message_status == '1') ? 'checked="checked"' : '';
								echo '<div class="armswitch">
									<input type="checkbox" class="armswitch_input arm_communication_status_action" id="arm_communication_status_input_'.esc_attr($messageID).'" value="1" data-item_id="'.esc_attr($messageID).'" '.esc_attr($switchChecked).'>
									<label class="armswitch_label" for="arm_communication_status_input_'.esc_attr($messageID).'"></label>
									<span class="arm_status_loader_img"></span>
								</div>'; //phpcs:ignore
							?></td>
							<?php
							$subs_plan_title = '';
							if(!empty($rc->arm_message_subscription)){
								$plans_id = @explode(',', $rc->arm_message_subscription);
								$subs_plan_title = $arm_subscription_plans->arm_get_comma_plan_names_by_ids($plans_id);
								$subs_plan_title = (!empty($subs_plan_title)) ? $subs_plan_title : '--';
							} else {
								$subs_plan_title = esc_html__('All Membership Plans', 'ARMember');
							}
							?>
							<td class=""><?php echo esc_html($subs_plan_title);?></td>
							<td><?php 
							$msge_type = '';
							switch ($rc->arm_message_type)
							{
								case 'on_new_subscription':
									$msge_type = esc_html__('On New Subscription', 'ARMember');
									break;
                                                                case 'on_menual_activation':
									$msge_type = esc_html__('On Manual User Activation', 'ARMember');
									break;
								case 'on_change_subscription':
									$msge_type = esc_html__('On Change Subscription', 'ARMember');
									break;
								case 'on_renew_subscription':
									$msge_type = esc_html__('On Renew Subscription', 'ARMember');
									break;
								case 'on_failed':
									$msge_type = esc_html__('On Failed Payment', 'ARMember');
									break;
                                                                case 'on_next_payment_failed':
									$msge_type = esc_html__('On Semi Automatic Subscription Failed Payment', 'ARMember');
									break;
								case 'trial_finished':
									$msge_type = esc_html__('Trial Finished', 'ARMember');
									break;
								case 'on_expire':
									$msge_type = esc_html__('On Membership Expired', 'ARMember');
									break;
								case 'before_expire':
									$msge_per_unit = $rc->arm_message_period_unit;
									$msge_per_type = $rc->arm_message_period_type;
									$msge_type = $msge_per_unit . ' ' . $msge_per_type . '(s) ' . esc_html__('Before Membership Expired', 'ARMember');
									break;
	                            case 'manual_subscription_reminder':
	                                    $msge_per_unit = $rc->arm_message_period_unit;
									$msge_per_type = $rc->arm_message_period_type;
									$msge_type = esc_html__('Semi Automatic Subscription Payment due', 'ARMember');
                                    $msge_type.= "(BeFore ".$msge_per_unit . ' ' . $msge_per_type . "(s))";
									break;
								case 'automatic_subscription_reminder':
	                                    $msge_per_unit = $rc->arm_message_period_unit;
									$msge_per_type = $rc->arm_message_period_type;
									$msge_type = esc_html__('Automatic Subscription Payment due', 'ARMember');
                                    $msge_type.= "(BeFore ".$msge_per_unit . ' ' . $msge_per_type . "(s))";
									break;
                                case 'on_change_subscription_by_admin':
                                         $msge_type = esc_html__('On Change Subscription By Admin', 'ARMember');
									break;
                                case 'before_dripped_content_available':
                                        $msge_per_unit = $rc->arm_message_period_unit;
									$msge_per_type = $rc->arm_message_period_type;
									$msge_type = $msge_per_unit . ' ' . $msge_per_type . '(s) ' . esc_html__('Before Dripped Content Available', 'ARMember');
									break;
                                case 'on_cancel_subscription':
									$msge_type = esc_html__('On Cancel Membership', 'ARMember');
									break;
								case 'on_recurring_subscription':
                                    $msge_type = esc_html__('On Recurring Subscription', 'ARMember');
									break;
								case 'on_close_account':
                                    $msge_type = esc_html__('On Close User Account', 'ARMember');
									break;
								case 'on_login_account':
                                    $msge_type = esc_html__('On User Login', 'ARMember');
									break;
								case 'on_new_subscription_post':
                                    $msge_type = esc_html__('On new paid post purchase', 'ARMember');
									break;	
								case 'on_recurring_subscription_post':
                                    $msge_type = esc_html__('On recurring paid post purchase', 'ARMember');
									break;
								case 'on_renew_subscription_post':
                                    $msge_type = esc_html__('On renew paid post purchase', 'ARMember');
									break;
								case 'on_cancel_subscription_post':
                                    $msge_type = esc_html__('On cancel paid post', 'ARMember');
									break;
								case 'before_expire_post':
                                    $msge_type = esc_html__('Before paid post expire', 'ARMember');
									break;
								case 'on_expire_post':
                                    $msge_type = esc_html__('On Expire paid post', 'ARMember');
									break;
								case 'on_purchase_subscription_bank_transfer':
                                    $msge_type = esc_html__('On Purchase membership plan using Bank Transfer', 'ARMember');
									break;
								default:
                                    $msge_type = apply_filters('arm_notification_get_list_msg_type',$rc->arm_message_type);
									break;
							}
							echo $msge_type; //phpcs:ignore
							?></td>
							<td class="armGridActionTD"><?php
								
								$gridAction = "<div class='arm_grid_action_btn_container'>";
								$gridAction .= "<a class='arm_edit_message_btn' href='javascript:void(0);' data-message_id='".$messageID."'><img src='".MEMBERSHIP_IMAGES_URL."/grid_edit.png' onmouseover=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_edit_hover.png';\" class='armhelptip' title='".esc_attr__('Edit Message','ARMember')."' onmouseout=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_edit.png';\" /></a>";
								$gridAction .= "<a href='javascript:void(0)' onclick='showConfirmBoxCallback({$messageID});'><img src='".MEMBERSHIP_IMAGES_URL."/grid_delete.png' class='armhelptip' title='".esc_attr__('Delete','ARMember')."' onmouseover=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_delete_hover.png';\" onmouseout=\"this.src='".MEMBERSHIP_IMAGES_URL."/grid_delete.png';\" /></a>";
								$gridAction .= $arm_global_settings->arm_get_confirm_box($messageID, esc_html__("Are you sure you want to delete this message?", 'ARMember'), 'arm_communication_delete_btn');
								$gridAction .= "</div>";
								echo '<div class="arm_grid_action_wrapper">'.$gridAction.'</div>'; //phpcs:ignore
							?></td>
						</tr>
						<?php } ?>  
					<?php endif;?>
					</tbody>
				</table>
				<div class="armclear"></div>
				<input type="hidden" name="search_grid" id="automated_search_grid" value="<?php esc_html_e('Search', 'ARMember');?>"/>
				<input type="hidden" name="entries_grid" id="automated_entries_grid" value="<?php esc_html_e('messages', 'ARMember');?>"/>
				<input type="hidden" name="show_grid" id="automated_show_grid" value="<?php esc_html_e('Show', 'ARMember');?>"/>
				<input type="hidden" name="showing_grid" id="automated_showing_grid" value="<?php esc_html_e('Showing', 'ARMember');?>"/>
				<input type="hidden" name="to_grid" id="automated_to_grid" value="<?php esc_html_e('to', 'ARMember');?>"/>
				<input type="hidden" name="of_grid" id="automated_of_grid" value="<?php esc_html_e('of', 'ARMember');?>"/>
				<input type="hidden" name="no_match_record_grid" id="automated_no_match_record_grid" value="<?php esc_html_e('No matching messages found', 'ARMember');?>"/>
				<input type="hidden" name="no_record_grid" id="automated_no_record_grid" value="<?php esc_html_e('There is no any communication message found.', 'ARMember');?>"/>
				<input type="hidden" name="filter_grid" id="automated_filter_grid" value="<?php esc_html_e('filtered from', 'ARMember');?>"/>
				<input type="hidden" name="totalwd_grid" id="automated_totalwd_grid" value="<?php esc_html_e('total', 'ARMember');?>"/>
				<?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
			</div>
			<div class="footer_grid"></div>
		</form>
		<div class="armclear"></div>
		<?php 
		/* **********./Begin Bulk Delete Communication Popup/.********** */
		$bulk_delete_message_popup_content = '<span class="arm_confirm_text">'.esc_html__("Are you sure you want to delete this message(s)?",'ARMember' ).'</span>';
		$bulk_delete_message_popup_content .= '<input type="hidden" value="false" id="bulk_delete_flag"/>';
		$bulk_delete_message_popup_arg = array(
			'id' => 'delete_bulk_communication_message',
			'class' => 'delete_bulk_communication_message',
			'title' => 'Delete Communication Message(s)',
			'content' => $bulk_delete_message_popup_content,
			'button_id' => 'arm_bulk_delete_message_ok_btn',
			'button_onclick' => "arm_delete_bulk_communication('true');",
		);
		echo $arm_global_settings->arm_get_bpopup_html($bulk_delete_message_popup_arg); //phpcs:ignore
		/* **********./End Bulk Delete Communication Popup/.********** */
		?>
		<div class="armclear"></div>
	</div>
<?php endif;?>
</div>
<!--./******************** Add New Member Form ********************/.-->

<script type="text/javascript">
    __ARM_ADDNEWRESPONSE = '<?php esc_html_e('Add New Response','ARMember'); ?>';
    __ARM_VALUE = '<?php esc_html_e('Value','ARMember'); ?>';
</script>