<?php
global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings,$arm_members_badges,$arm_email_settings,$arm_manage_coupons;
$badges_list = $arm_members_badges->arm_get_all_badges();
$bid = 0;
$achievementsList = '';
$ARMember->arm_session_start(true);
$_SESSION['arm_file_upload_arr']['arm_badges_icon'] = "-";
$get_page = !empty($_GET['page']) ? sanitize_text_field( $_GET['page'] ) : '';
?>
<style type="text/css" title="currentStyle">
	.paginate_page a{display:none;}
	#poststuff #post-body {margin-top: 32px;}
	.delete_box{float:left;}
	.row-actions{text-align: center;}
	.ColVis_Button{ display: none !important;}
</style>
<script type="text/javascript" charset="utf-8">
// <![CDATA[
jQuery(document).ready( function () {
    arm_load_badge_list_grid();
  });

function arm_load_badge_list_filtered_grid(data)
{
    var tbl = jQuery('#armember_datatable').dataTable(); 
    tbl.fnDeleteRow(data);
    jQuery('#armember_datatable').dataTable().fnDestroy();
    arm_load_badge_list_grid();
}


function arm_load_badge_list_grid() {
    var __ARM_Showing = '<?php echo addslashes(esc_html__('Showing','ARMember')); //phpcs:ignore?>';
    var __ARM_Showing_empty = '<?php echo addslashes(esc_html__('Showing 0 to 0 of 0 entries','ARMember')); //phpcs:ignore?>';
    var __ARM_to = '<?php echo addslashes(esc_html__('to','ARMember')); //phpcs:ignore?>';
    var __ARM_of = '<?php echo addslashes(esc_html__('of','ARMember')); //phpcs:ignore?>';
    var __ARM_RECORDS = '<?php echo addslashes(esc_html__('entries','ARMember')); //phpcs:ignore?>';
    var __ARM_Show = '<?php echo addslashes(esc_html__('Show','ARMember')); //phpcs:ignore?>';
    var __ARM_NO_FOUND = '<?php echo addslashes(esc_html__('No any badge found.','ARMember')); //phpcs:ignore?>';
    var __ARM_NO_MATCHING = '<?php echo addslashes(esc_html__('No matching records found.','ARMember')); //phpcs:ignore?>';
	jQuery('#armember_datatable').dataTable( {
		"sDom": '<"H"Cfr>t<"footer"ipl>',
		"sPaginationType": "four_button",
                "oLanguage": {
                    "sInfo": __ARM_Showing + " _START_ " + __ARM_to + " _END_ " + __ARM_of + " _TOTAL_ " + __ARM_RECORDS,
                    "sInfoEmpty": __ARM_Showing_empty,
                    "sLengthMenu": __ARM_Show + "_MENU_" + __ARM_RECORDS,
                    "sEmptyTable": __ARM_NO_FOUND,
                    "sZeroRecords": __ARM_NO_MATCHING
                  },
		"bJQueryUI": true,
		"bPaginate": true,
		"bAutoWidth" : false,					
		"aoColumnDefs": [
			{"bVisible": false, "aTargets": []},
			{"bSortable": false, "aTargets": [2]}
		],
        "language":{
            "searchPlaceholder": "Search",
            "search":"",
        },
		"oColVis": {"aiExclude": [0]},
        "aLengthMenu": [10, 15, 20, 25, 50, 100],
        "iDisplayLength": 15,
        "fnDrawCallback":function(){
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
    }
	
					
// ]]>
</script>
<div class="arm_global_settings_main_wrapper arm_margin_0">
    <div class="page_sub_content arm_padding_0">
        <?php /* <div class="arm_add_new_item_box arm_margin_bottom_20" >			
            <a class="greensavebtn arm_add_new_badges_btn arm_margin_right_20" href="javascript:void(0);" ><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL ?>/add_new_icon.png"><span><?php esc_html_e('Add New Badge', 'ARMember') ?></span></a>
        </div> */ ?>
        <form method="GET" id="badges_list_form" class="data_grid_list">
            <input type="hidden" name="page" value="<?php echo esc_attr($get_page); ?>" />
            <input type="hidden" name="armaction" value="list" />
            <div id="armmainformnewlist">								
                <table cellpadding="0" cellspacing="0" border="0" class="display" id="armember_datatable">
                    <thead>
                        <tr>
                            <th class="center arm_text_align_center"><?php esc_html_e('Badge', 'ARMember'); ?></th>
                            <th class="arm_text_align_left arm_padding_left_10"><?php esc_html_e('Badge Title', 'ARMember'); ?></th>
                            <th class="arm_text_align_center"><?php esc_html_e('No of Achievement', 'ARMember'); ?></th>
                            <th class="armGridActionTD"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($badges_list)) {
                            $global_settings = $arm_global_settings->global_settings;
                            $badge_width = !empty($global_settings['badge_width']) ? $global_settings['badge_width'] : 30;
                            $badge_height = !empty($global_settings['badge_height']) ? $global_settings['badge_height'] : 30;
                            $badge_css = "width:" . $badge_width . "px; height:" . $badge_height . "px;";
                            //$arm_all_achievements_count = $arm_members_badges->arm_get_all_achievements_count_by_badge();
                            ?>
                            <?php foreach ($badges_list as $key => $badges) { ?>
                                <?php $badgeID = $badges->arm_badges_id; ?>
                                <tr class="badges_row_<?php echo esc_attr($badgeID); ?>">
                                    <td class="center"><?php
                                    if(empty($badges->arm_badges_icon)){
                                        echo '--';
                                    } else {
										
										$arm_badges_icon = $badges->arm_badges_icon;
										$arm_badges_icon_arr = explode('/', $arm_badges_icon);
										$arm_badges_icon_end = end($arm_badges_icon_arr);
										
                                        if( file_exists(MEMBERSHIP_UPLOAD_DIR.'/social_badges/'.$arm_badges_icon_end) ){
                                            $badges->arm_badges_icon =strstr($badges->arm_badges_icon, "//");
                                        }else if( file_exists(MEMBERSHIP_UPLOAD_DIR.'/social_badges/'.$arm_badges_icon_end) ){
                                            $badges->arm_badges_icon = $badges->arm_badges_icon;
                                        }else{
                                            $badges->arm_badges_icon = $badges->arm_badges_icon;
                                        }
                                        echo '<img src="' . esc_attr($badges->arm_badges_icon) . '" class="arm_grid_badges_icon" alt="" style="' . $badge_css . '" >';//phpcs:ignore
                                    }
                                    ?></td>
                                    <td><?php echo esc_html($badges->arm_badges_name);?></td>
                                    <td class="center"><?php 

                                    $childAchivements = $arm_members_badges->arm_get_all_achievements_count_by_badge($badgeID);
                                    //$childAchivements = isset($arm_all_achievements_count[$badgeID]) ? $arm_all_achievements_count[$badgeID] : 0; 
                                    $arm_badge_confirm_box = '';
                                    if ($childAchivements > 0) {
                                        echo '<a href="javascript:void(0);" class="arm_preview_badge_achievements" data-id="' . esc_attr($badgeID) . '">' . esc_html($childAchivements) . '</a>';
                                        $arm_badge_confirm_box = $arm_global_settings->arm_get_confirm_box($badgeID, esc_html__("Are you sure you want to delete this badge and achievement(s)?", 'ARMember'), 'arm_delete_badges_btn');
                                    } else {
                                        echo '0';
                                        $arm_badge_confirm_box = $arm_global_settings->arm_get_confirm_box($badgeID, esc_html__("Are you sure you want to delete this badge?", 'ARMember'), 'arm_delete_badges_btn');
                                    }
                                    ?></td>
                                    <td class="armGridActionTD">
                                        <?php
                                        $gridAction = "<div class='arm_grid_action_btn_container'>";
                                        $gridAction .= "<a class='arm_edit_badges_btn' href='javascript:void(0);' data-badge_id='" . $badgeID . "'><img src='" . MEMBERSHIP_IMAGES_URL . "/grid_edit.png' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_edit_hover.png';\" class='armhelptip' title='" . esc_html__('Edit Badge', 'ARMember') . "' onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_edit.png';\" /></a>";
                                        $gridAction .= "<a href='javascript:void(0)' onclick='showConfirmBoxCallback({$badgeID});'><img src='" . MEMBERSHIP_IMAGES_URL . "/grid_delete.png' class='armhelptip' title='" . esc_html__('Delete', 'ARMember') . "' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_delete_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_delete.png';\" /></a>";
                                        $gridAction .= $arm_badge_confirm_box;
                                        $gridAction .= "</div>";
                                        echo '<div class="arm_grid_action_wrapper">' . $gridAction . '</div>';//phpcs:ignore
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        ?>  
                    </tbody>
                </table>
                <div class="armclear"></div>
                <input type="hidden" name="search_grid" id="search_grid" value="<?php esc_html_e('Search', 'ARMember'); ?>"/>
                <input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_html_e('badges', 'ARMember'); ?>"/>
                <input type="hidden" name="show_grid" id="show_grid" value="<?php esc_html_e('Show', 'ARMember'); ?>"/>
                <input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_html_e('Showing', 'ARMember'); ?>"/>
                <input type="hidden" name="to_grid" id="to_grid" value="<?php esc_html_e('to', 'ARMember'); ?>"/>
                <input type="hidden" name="of_grid" id="of_grid" value="<?php esc_html_e('of', 'ARMember'); ?>"/>
                <input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_html_e('No matching records found.', 'ARMember'); ?>"/>
                <input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_html_e('No any badge found.', 'ARMember'); ?>"/>
                <input type="hidden" name="filter_grid" id="filter_grid" value="<?php esc_html_e('filtered from', 'ARMember'); ?>"/>
                <input type="hidden" name="totalwd_grid" id="totalwd_grid" value="<?php esc_html_e('total', 'ARMember'); ?>"/>
                <?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
            </div>
            <div class="footer_grid"></div>
        </form>
        <div class="armclear"></div>
    </div>
</div>
<!--./******************** Add New Badge Form ********************/.-->
<div class="add_new_badges_wrapper popup_wrapper">
	<form method="post" action="#" id="arm_add_badges_wrapper_frm" class="arm_admin_form arm_add_badges_wrapper_frm">
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="add_new_badges_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php esc_html_e('Add New Badge','ARMember');?></td>
				<td class="popup_content_text">
					<table class="arm_table_label_on_top">	
						<tr>
							<th><?php esc_html_e('Badge Title', 'ARMember');?></th>
							<td class="arm_required_wrapper">
								<input type="text" id="arm_badges_name" class="arm_width_100_pct" name="arm_badges_name" data-msg-required="<?php esc_html_e('Title can not be left blank.', 'ARMember');?>" value="" >
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e('Badge Icon', 'ARMember');?></th>
							<td class="arm_required_wrapper">
								<div id="arm_add_badge_file_container"></div>
								<div id="arm_edit_badge_file_container"></div>
							</td>
						</tr>
                        <?php $nonce = wp_create_nonce('arm_wp_nonce');?>
                        <input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($nonce);?>"/>
					</table>
					<div class="armclear"></div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif'; //phpcs:ignore?>" id="arm_loader_img" class="arm_loader_img arm_submit_btn_loader" style="top: 15px;display: none;float: <?php echo (is_rtl()) ? 'right' : 'left';?>;" width="20" height="20" />
						<input type="hidden" id="arm_badges_id_box" name="edit_id" value="<?php echo esc_attr($bid);?>" />
						<input type="hidden" id="arm_membership_url" name="arm_membership_url" value="<?php echo MEMBERSHIP_URL; //phpcs:ignore?>" />
						<input type="hidden" id="arm_membership_view_url" name="arm_membership_view_url" value="<?php echo MEMBERSHIP_VIEWS_URL; //phpcs:ignore?>" />						
						<?php  $browser_info = $ARMember->getBrowser($_SERVER['HTTP_USER_AGENT']); //phpcs:ignore?>
						<input type="hidden" id="arm_badge_icon_browser_name" name="arm_badge_icon_browser_name" value="<?php echo esc_attr($browser_info['name']);?>" />
						<input type="hidden" id="arm_badge_icon_browser_version" name="arm_badge_icon_browser_version" value="<?php echo esc_attr($browser_info['version']);?>" />
						
						<button class="arm_save_btn arm_button_manage_badges" type="submit" data-type="add"><?php esc_html_e('Save', 'ARMember') ?></button>
						<button class="arm_cancel_btn add_new_badges_close_btn" type="button"><?php esc_html_e('Cancel','ARMember');?></button>
					</div>
				</td>
			</tr>
		</table>
		<div class="armclear"></div>
	</form>
</div>
<!--./******************** Preview Badge Details Form ********************/.-->
<div class="arm_badge_details_popup_container" style="display:none;"></div>

<script type="text/javascript">
    __ARM_ADDNEWBADGE = '<?php echo esc_html__('Add New Badge','ARMember'); ?>';
    __ARM_UPLOAD = '<?php echo esc_html__('Upload','ARMember'); ?>';
    __ARM_REMOVE = '<?php echo esc_html__('Remove','ARMember'); ?>';
    __ARM_SELECTFILE = '<?php echo esc_html__('Please select file','ARMember'); ?>';
    __ARM_INVALIDEFILE = '<?php echo esc_html__('Invalid file selected','ARMember'); ?>';

</script>