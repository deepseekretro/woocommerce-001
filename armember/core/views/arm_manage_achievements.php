<?php
global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings,$arm_members_badges,$arm_email_settings,$arm_manage_coupons, $arm_subscription_plans;

$badges_and_achievements_list = $arm_members_badges->arm_get_all_achievements();
$allAchievements = $badges_and_achievements_list['achievements'];
$badges_list = $badges_and_achievements_list['badges'];
$achieve_types = $arm_members_badges->arm_get_achievement_types();
$user_roles = $arm_global_settings->arm_get_all_roles_for_badges();
$all_plans =  $arm_subscription_plans->arm_get_all_subscription_plans();
$global_settings = $arm_global_settings->global_settings;
$badge_width = !empty($global_settings['badge_width']) ? $global_settings['badge_width'] : 30;
$badge_height = !empty($global_settings['badge_height']) ? $global_settings['badge_height'] : 30;
$badgeIconStyle = "width:" . $badge_width . "px; height:" . $badge_height . "px;";
$aid = 0;
$achieve_type = 'posts';
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
    arm_load_achievements_list_grid();
});

function arm_load_achievements_list_filtered_grid(data)
{
    var tbl = jQuery('#armember_datatable').dataTable(); 
    tbl.fnDeleteRow(data);
    jQuery('#armember_datatable').dataTable().fnDestroy();
    arm_load_achievements_list_grid();
}
function arm_load_achievements_list_grid() {
    var __ARM_Showing = '<?php echo addslashes(esc_html__('Showing','ARMember')); //phpcs:ignore?>';
    var __ARM_Showing_empty = '<?php echo addslashes(esc_html__('Showing 0 to 0 of 0 entries','ARMember')); //phpcs:ignore?>';
    var __ARM_to = '<?php echo addslashes(esc_html__('to','ARMember')); //phpcs:ignore?>';
    var __ARM_of = '<?php echo addslashes(esc_html__('of','ARMember')); //phpcs:ignore?>';
    var __ARM_RECORDS = '<?php echo addslashes(esc_html__('entries','ARMember')); //phpcs:ignore?>';
    var __ARM_Show = '<?php echo addslashes(esc_html__('Show','ARMember')); //phpcs:ignore?>';
    var __ARM_NO_FOUND = '<?php echo addslashes(esc_html__('No any achievement found.','ARMember')); //phpcs:ignore?>';
    var __ARM_NO_MATCHING = '<?php echo addslashes(esc_html__('No matching records found.','ARMember')); //phpcs:ignore?>';
	jQuery('#armember_datatable').dataTable({
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
			{ "bVisible": false, "aTargets": [] },
			{ "bSortable": false, "aTargets": [2] },
            { "sWidth": '30%', "aTargets": [0] },
            { "sWidth": '35%', "aTargets": [1] },
            { "sWidth": '35%', "aTargets": [2] }
		],
		"oColVis": {"aiExclude": [0]},
        "language":{
            "searchPlaceholder": "Search",
            "search":"",
        },
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
            <a class="greensavebtn arm_add_achievements_btn arm_margin_right_20" href="javascript:void(0);" ><img align="absmiddle" src="<?php echo MEMBERSHIP_IMAGES_URL ?>/add_new_icon.png"><span><?php esc_html_e('Add New Achievement', 'ARMember') ?></span></a>
        </div> */ ?>
        <form method="GET" id="achievements_list_form" class="data_grid_list">
            <input type="hidden" name="page" value="<?php echo esc_attr($get_page); ?>" />
            <input type="hidden" name="armaction" value="list" />
            <div id="armmainformnewlist">
                <div class="arm_loading_grid" style="display: none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL; //phpcs:ignore?>/loader.gif" alt="Loading.."></div>
                <table cellpadding="0" cellspacing="0" border="0" class="display arm_achievements_list_grid" id="armember_datatable">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Achievement Type', 'ARMember'); ?></th>
                            <th class="center arm_text_align_center" ><?php esc_html_e('Badge', 'ARMember'); ?></th>
                            <th class="arm_text_align_left arm_padding_left_10"><?php esc_html_e('Required', 'ARMember'); ?></th>
                            <?php /* ?><th style="text-align: left;padding-left: 10px;"><?php esc_html_e('Badge Title', 'ARMember'); ?></th><?php */?>
                            <th class="armGridActionTD"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($allAchievements)): ?>
                            <?php $i = 1; foreach ($allAchievements as $badges): ?>
                                <?php 
                                $badges = (object) $badges;
                                $badgesID = $badges->arm_badges_id;
                                if (!empty($badges->arm_badges_achievement)) {
                                    $achieve = maybe_unserialize($badges->arm_badges_achievement);
                                    $arm_achieve_badge_id = (isset($achieve['arm_achieve_badge_id']) && $achieve['arm_achieve_badge_id'] != 0) ? $achieve['arm_achieve_badge_id'] : 0;
                                    $arm_achieve_require_badge_id_array = maybe_unserialize($arm_achieve_badge_id);

                                    ?>
                                    <tr class="achieve_row_<?php echo esc_attr($badgesID);?>">
                                        <td><?php 
                                        if ($badges->arm_badges_achievement_type == 'require') {
                                            echo ucwords($achieve['arm_achieve']); //phpcs:ignore
                                        } else {
                                            if($badges->arm_badges_achievement_type=='defaultbadge')
                                            {
                                                echo 'Default Badge';
                                            }
                                            else
                                            {
                                                echo ucwords($badges->arm_badges_achievement_type); //phpcs:ignore
                                            }
                                        }
                                        ?></td>
                                        <td class="center"><?php 
                                        if($badges->arm_badges_achievement_type == 'require')
                                        {
                                            if(is_array($arm_achieve_require_badge_id_array))
                                            {
                                                foreach ($arm_achieve_require_badge_id_array as $arm_achieve_require_badge_id_value) 
                                                {
                                                    $arm_badges_require_icon = $arm_members_badges->arm_get_single_badge($arm_achieve_require_badge_id_value);
                                                    
                                                    if(empty($arm_badges_require_icon['arm_badges_icon']))
                                                    { 
                                                        echo '--';
                                                    }
                                                    else 
                                                    {
                                                        /*
                                                        if(file_exists(strstr($arm_badges_require_icon['arm_badges_icon'], "//")))
                                                        {
                                                            $arm_badges_require_icon['arm_badges_icon'] =strstr($arm_badges_require_icon['arm_badges_icon'], "//");
                                                        }
                                                        else if(file_exists($arm_badges_require_icon['arm_badges_icon']))
                                                        {
                                                           $arm_badges_require_icon['arm_badges_icon'] = $arm_badges_require_icon['arm_badges_icon'];
                                                        }
                                                        else
                                                        {
                                                            $arm_badges_require_icon['arm_badges_icon'] = $arm_badges_require_icon['arm_badges_icon'];
                                                        }
                                                        */
                                                        echo '<img src="' . esc_attr($arm_badges_require_icon['arm_badges_icon']) . '" class="arm_grid_badges_icon armhelptip_front" alt="" title="'. esc_attr($arm_badges_require_icon['arm_badges_name']).'" style="' . $badgeIconStyle . '" >';//phpcs:ignore
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                if(empty($badges->arm_badges_icon))
                                                {
                                                    echo '--';
                                                } 
                                                else 
                                                {
                                                    /*
                                                    if(file_exists(strstr($badges->arm_badges_icon, "//")))
                                                    {
                                                        $badges->arm_badges_icon =strstr($badges->arm_badges_icon, "//");
                                                    }
                                                    else if(file_exists($badges->arm_badges_icon))
                                                    {
                                                       $badges->arm_badges_icon = $badges->arm_badges_icon;
                                                    }
                                                    else
                                                    {
                                                        $badges->arm_badges_icon = $badges->arm_badges_icon;
                                                    }
                                                    */
                                                    echo '<img src="' . esc_attr($badges->arm_badges_icon) . '" class="arm_grid_badges_icon armhelptip_front" title="'.esc_attr($badges->arm_badges_name).'" alt="" style="' . $badgeIconStyle . '" >';//phpcs:ignore
                                                }
                                            }
                                        }
                                        else
                                        {
                                            if(empty($badges->arm_badges_icon))
                                            {
                                                echo '--';
                                            } 
                                            else 
                                            {
                                                /*
                                                if(file_exists(strstr($badges->arm_badges_icon, "//")))
                                                {
                                                    $badges->arm_badges_icon =strstr($badges->arm_badges_icon, "//");
                                                }
                                                else if(file_exists($badges->arm_badges_icon))
                                                {
                                                   $badges->arm_badges_icon = $badges->arm_badges_icon;
                                                }
                                                else
                                                {
                                                    $badges->arm_badges_icon = $badges->arm_badges_icon;
                                                }
                                                */

                                                echo '<img src="' . esc_attr($badges->arm_badges_icon) . '" class="arm_grid_badges_icon armhelptip_front" title="'.esc_attr($badges->arm_badges_name).'" alt="" style="' . $badgeIconStyle . '" >'; //phpcs:ignore
                                            }
                                        }
                                        ?></td>
                                        <td><?php 
                                        $achieveNum = (isset($achieve['arm_achieve_num']) && $achieve['arm_achieve_num'] != 0) ? $achieve['arm_achieve_num'] : 0;

                                        if ($badges->arm_badges_achievement_type == 'defaultbadge') {
                                            echo "-";
                                        } elseif ($badges->arm_badges_achievement_type == 'require') {
                                            $arm_achive_num_ser =  maybe_unserialize($achieve['arm_achieve_num']);
                                            if(is_array($arm_achive_num_ser))
                                            {
                                                echo implode(',', $arm_achive_num_ser); //phpcs:ignore
                                            }
                                            else
                                            {
                                                echo esc_html($achieveNum);
                                            }
                                        } elseif ($badges->arm_badges_achievement_type == 'plans') {
                                            $plans_id = @explode(',', $achieve['arm_achieve']);
                                            $subs_plan_title = $arm_subscription_plans->arm_get_comma_plan_names_by_ids($plans_id);
                                            echo (!empty($subs_plan_title)) ? esc_html($subs_plan_title) : '--';
                                        }
                                        elseif ($badges->arm_badges_achievement_type == 'roles') {
                                            $roles_key = @explode(',', $achieve['arm_achieve']);
                                            $user_roles = $arm_global_settings->arm_get_all_roles_for_badges();
                                            $role_names = array();    
                                            foreach($roles_key as $role_key_val)
                                            {
                                                if (!empty($user_roles)){
                                                    foreach ($user_roles as $key => $val){
                                                        if($key == $role_key_val)
                                                        {
                                                            $role_names[]= $val;
                                                        }
                                                    }
                                                }
                                            }
                                            echo implode(", ",$role_names); //phpcs:ignore
                                        }
                                        else {
                                            echo str_replace(',', ', ', $achieve['arm_achieve']); //phpcs:ignore
                                        }
                                        ?></td>
                                        <td class="armGridActionTD">
                                            <?php
                                            $gridAction = "<div class='arm_grid_action_btn_container'>";
                                            $gridAction .= "<a class='arm_edit_achievements_btn' href='javascript:void(0);' data-achievement_id='" . $badgesID . "'><img src='" . MEMBERSHIP_IMAGES_URL . "/grid_edit.png' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_edit_hover.png';\" class='armhelptip' title='" . esc_html__('Edit Achievement', 'ARMember') . "' onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_edit.png';\" /></a>";
                                            $gridAction .= "<a href='javascript:void(0)' onclick='showConfirmBoxCallback({$badgesID});'><img src='" . MEMBERSHIP_IMAGES_URL . "/grid_delete.png' class='armhelptip' title='" . esc_html__('Delete', 'ARMember') . "' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_delete_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_delete.png';\" /></a>";
                                            $gridAction .= $arm_global_settings->arm_get_confirm_box($badgesID, esc_html__("Are you sure you want to delete this achievement?", 'ARMember'), 'arm_delete_achievements_btn', 'achievement');
                                            $gridAction .= "</div>";
                                            echo '<div class="arm_grid_action_wrapper">' . $gridAction . '</div>'; //phpcs:ignore
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $i++;
                                }
                                ?>
                            <?php endforeach;?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="armclear"></div>
                <input type="hidden" name="search_grid" id="search_grid" value="<?php esc_html_e('Search', 'ARMember'); ?>"/>
                <input type="hidden" name="entries_grid" id="entries_grid" value="<?php esc_html_e('achievements', 'ARMember'); ?>"/>
                <input type="hidden" name="show_grid" id="show_grid" value="<?php esc_html_e('Show', 'ARMember'); ?>"/>
                <input type="hidden" name="showing_grid" id="showing_grid" value="<?php esc_html_e('Showing', 'ARMember'); ?>"/>
                <input type="hidden" name="to_grid" id="to_grid" value="<?php esc_html_e('to', 'ARMember'); ?>"/>
                <input type="hidden" name="of_grid" id="of_grid" value="<?php esc_html_e('of', 'ARMember'); ?>"/>
                <input type="hidden" name="no_match_record_grid" id="no_match_record_grid" value="<?php esc_html_e('No matching records found.', 'ARMember'); ?>"/>
                <input type="hidden" name="no_record_grid" id="no_record_grid" value="<?php esc_html_e('No any achievement found.', 'ARMember'); ?>"/>
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
<!--./******************** Add New Achievement Form ********************/.-->
<div class="arm_add_achievements_wrapper popup_wrapper">
	<form method="post" action="#" id="arm_add_achievements_wrapper_frm" class="arm_admin_form arm_add_achievements_wrapper_frm">
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="add_new_achievements_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php esc_html_e('Add New Achievement','ARMember');?></td>
				<td class="popup_content_text">
					<table class="arm_table_label_on_top">
						<tr>
                            <th><?php esc_html_e('How user will get this badge?','ARMember'); ?></th>
                            <td>
                                <input type="hidden" name="arm_achievement_type" value="defaultbadge" id="arm_add_achievement_type" class="arm_achievement_type_change_input"/>
                                <dl class="arm_selectbox arm_subscription_plan_form_dropdown arm_margin_right_0 arm_width_100_pct">
                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_add_achievement_type">
                                            <li data-label="<?php esc_html_e('Default Badge for All', 'ARMember');?>" data-value="defaultbadge"><?php esc_html_e('Default Badge for All', 'ARMember');?></li>
                                            <li data-label="<?php esc_html_e('Give this badge to roles', 'ARMember');?>" data-value="roles"><?php esc_html_e('Give this badge to roles', 'ARMember');?></li>
                                            <li data-label="<?php esc_html_e('Give this badge to plans', 'ARMember');?>" data-value="plans"><?php esc_html_e('Give this badge to plans', 'ARMember');?></li>
                                            <li data-label="<?php esc_html_e('Require achievement', 'ARMember');?>" data-value="require"><?php esc_html_e('Require achievement', 'ARMember');?></li>
                                        </ul>
                                    </dd>
                                </dl>
                                <input type="hidden" name="arm_achievement_options[defaultbadge]" value="default"/>
                            </td>
                        </tr>
                        <tr class="arm_badge_roles_options arm_badge_achieve_options" style="display:none;">
                            <th><?php esc_html_e('Choose which users should receive this badge','ARMember'); ?></th>
                            <td>
                                <input type="hidden" id="arm_add_achieve_roles" name="arm_achievement_options[roles]" value="" data-msg-required="<?php esc_html_e('Please select role.', 'ARMember');?>"/>
                                <?php if (!empty($user_roles)): ?>
                                <dl class="arm_selectbox arm_width_100_pct"><?php /*arm_multiple_selectbox*/?>
                                    <dt><span>Please select role</span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_add_achieve_roles" data-placeholder="<?php esc_html_e('Select Roles', 'ARMember');?>">
                                            <?php foreach ($user_roles as $roleKey => $roleName): ?>
                                            <li data-label="<?php echo esc_attr($roleName); ?>" data-value="<?php echo esc_attr($roleKey);?>"><?php /*?><input type="checkbox" class="arm_icheckbox" value="<?php echo $roleKey;?>"/><?php */?><?php echo esc_html($roleName);?></li>
                                            <?php endforeach;?>
                                        </ul>
                                    </dd>
                                </dl>
                                <?php else: ?>
                                    <?php esc_html_e('There is no any role availabel.', 'ARMember');?>
                                <?php endif;?>
                            </td>
                        </tr>
                        <tr class="arm_badge_plans_options arm_badge_achieve_options" style="display:none;">
                            <th><?php esc_html_e('Choose which users should receive this badge','ARMember'); ?></th>
                            <td>
                                <input type="hidden" id="arm_add_achieve_plans" name="arm_achievement_options[plans]" value="" data-msg-required="<?php esc_html_e('Please select plan.', 'ARMember');?>"/>
                                <?php if (!empty($all_plans)): ?>
                                <dl class="arm_selectbox arm_width_100_pct">
                                    <dt><span>Please select plan</span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_add_achieve_plans" data-placeholder="<?php esc_html_e('Select Plans', 'ARMember');?>">
                                            <?php foreach ($all_plans as $plan): ?>
                                            <li data-label="<?php echo stripslashes(esc_attr($plan['arm_subscription_plan_name'])); //phpcs:ignore?>" data-value="<?php echo esc_attr($plan['arm_subscription_plan_id']); ?>"><?php echo stripslashes( esc_attr($plan['arm_subscription_plan_name']) ); //phpcs:ignore?></li>
                                            <?php endforeach;?>
                                        </ul>
                                    </dd>
                                </dl>
                                <?php else: ?>
                                    <?php esc_html_e('There is no any plan configured yet.', 'ARMember');?>, <a href="<?php echo esc_url( admin_url('admin.php?page='.$arm_slugs->manage_plans.'&action=new') );?>"><?php esc_html_e('Please add new plan.', 'ARMember');?></a>
                                <?php endif;?>
                            </td>
                        </tr>
                        <tr class="arm_badge_require_options arm_badge_achieve_options" style="display:none;">
                            <th><?php esc_html_e('Setup Achievement','ARMember'); ?></th>
                            <td>
                                <input type="hidden" id="arm_add_achieve_require" name="arm_achievement_options[require]" value="post"/>
                                <dl class="arm_selectbox column_level_dd arm_width_100_pct">
                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_add_achieve_require">
                                        <?php foreach($achieve_types as $type => $label): 
                                            if($type == 'reply') { continue; } ?>
                                            <li data-label="<?php echo esc_attr($label);?>" data-value="<?php echo esc_attr($type);?>"><?php echo esc_html($label);?></li>
                                        <?php endforeach; ?>
                                        </ul>
                                    </dd>
                                </dl>
                            </td>
                            <td>
                                <div class="arm_achievement_helptip">
                                <span>(<?php echo esc_html__("Please add value in numerical order of achievements (Lower Value First)", 'ARMember')?>)</span>
                                </div>
                                <div class="arm_achievement_has_complete ">
                                <span><?php esc_html_e('User has completed', 'ARMember');?>
                                </span>
                                <input type="text" id="arm_add_achieve" name="arm_achieve_num[]" class="arm_achieve_num arm_width_50 arm_min_width_50 arm_text_align_center arm_padding_left_0" onkeypress="javascript:return isNumber (event);"  value="" data-msg-required="<?php esc_html_e('Please enter number', 'ARMember');?>" >

                                <input type="hidden" id="arm_require_achive_badges_id" name="arm_require_achive_badges_id[]" class="arm_achivement_badge_icon" value=""/>
                                <dl class="arm_selectbox arm_achievement_badge_select arm_badge_select column_level_dd" id="arm_achievement_badge_select">
                                    <dt><span>Please Select Badge Icon</span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_require_achive_badges_id">
                                        <?php 
                                                if(!empty($badges_list))
                                                {
                                                    foreach ($badges_list as $badge) 
                                                    {
                                                        /*
                                                        if(file_exists(strstr($badge->arm_badges_icon, "//")))
                                                        {
                                                            $badge->arm_badges_icon =strstr($badge->arm_badges_icon, "//");
                                                        }
                                                        else if(file_exists($badge->arm_badges_icon))
                                                        {
                                                            $badge->arm_badges_icon = $badge->arm_badges_icon;
                                                        }
                                                        else
                                                        {
                                                            $badge->arm_badges_icon = $badge->arm_badges_icon;
                                                        }
                                                        */
                                                        echo '<li data-value="'.esc_attr($badge->arm_badges_id).'" ><img src="' . esc_attr($badge->arm_badges_icon) . '" alt="" align="middle" class="arm_badge_icon arm_padding_top_5" style="'.$badgeIconStyle.'" /><span class="arm_badge_title">'.esc_html($badge->arm_badges_name).'</span></li>'; //phpcs:ignore
                                                
                                                }}
                                        ?>
                                        </ul>
                                    </dd>
                                </dl>
                                <input type="text" id="arm_require_badges_tootip" name="arm_require_badges_tootip[]" class="arm_achivement_badges_tootip" placeholder="Tooltip Title" value="">
                                <div class="arm_achievement_helptip_icon">
                                    <div class="arm_achievement_plus_icon arm_achieve_add_plus_icon arm_helptip_icon tipso_style " title="<?php esc_html_e('Add Achievement', 'ARMember'); ?>"  ></div>

                                    <div class="arm_achievement_minus_icon arm_achieve_add_minus_icon arm_helptip_icon tipso_style " title="<?php esc_html_e('Remove Achievement', 'ARMember'); ?>" ></div>
                                </div>
                                </div>
                                <input type="hidden" id="arm_require_achive_counter" name="arm_require_achive_counter" value="1">
                            </td>
                        </tr>
                        <tr class="arm_badge_icon_require_options arm_badge_achieve_options" >
							<th><?php esc_html_e('Select Badge Icon','ARMember'); ?></th>
							<td>
                                <input type="hidden" id="arm_badges_id" name="arm_badges_id" value=""/>
                                <dl class="arm_selectbox arm_achievement_badge_select arm_badge_select column_level_dd arm_width_100_pct">
                                    <dt><span>Please Select Badge Icon</span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_badges_id">
                                        <?php 
                                                if(!empty($badges_list))
                                                {
                                                    foreach ($badges_list as $badge) 
                                                    {
                                                        /*
                                                        if(file_exists(strstr($badge->arm_badges_icon, "//")))
                                                        {
                                                            $badge->arm_badges_icon =strstr($badge->arm_badges_icon, "//");
                                                        }
                                                        else if(file_exists($badge->arm_badges_icon))
                                                        {
                                                            $badge->arm_badges_icon = $badge->arm_badges_icon;
                                                        }
                                                        else
                                                        {
                                                            $badge->arm_badges_icon = $badge->arm_badges_icon;
                                                        }
                                                        */
                                                        echo '<li data-value="'.esc_attr($badge->arm_badges_id).'" ><img src="' . esc_attr($badge->arm_badges_icon) . '" alt="" align="middle" class="arm_badge_icon arm_padding_top_5" style="'.$badgeIconStyle.'" /><span class="arm_badge_title">'. esc_html($badge->arm_badges_name).'</span></li>'; //phpcs:ignore
                                                }}
                                        ?>
                                        </ul>
                                    </dd>
                                </dl>
                                
							</td>
						</tr>
                        <tr class="arm_badge_tootip_require_options arm_badge_achieve_options" >
                            <th><?php esc_html_e('Tooltip Title','ARMember'); ?></th>
                            <td>
                                <input type="text" id="arm_badges_tooltip" name="arm_badges_tooltip" class="arm_badges_tooltip arm_width_100_pct arm_max_width_100_pct" value=""/>
                            </td>
                        </tr>
						
                        
					</table>
					<div class="armclear"></div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
                        <input type="hidden" name="b_action" value="add"/>
						<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' //phpcs:ignore?>" id="arm_loader_img" class="arm_loader_img arm_submit_btn_loader" style="top: 15px;display: none;float: <?php echo (is_rtl()) ? 'right' : 'left';?>;" width="20" height="20" />
						<button class="arm_save_btn arm_button_manage_achievements" type="submit" data-type="add"><?php esc_html_e('Save', 'ARMember') ?></button>
						<button class="arm_cancel_btn add_new_achievements_close_btn" type="button"><?php esc_html_e('Cancel','ARMember');?></button>
					</div>
				</td>
			</tr>
		</table>
        <?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
		<div class="armclear"></div>
	</form>
</div>
<!--./******************** Edit Achievement Form ********************/.-->
<div class="arm_edit_achievements_wrapper popup_wrapper" >
	<form method="post" action="#" id="arm_edit_achievements_wrapper_frm" class="arm_admin_form arm_edit_achievements_wrapper_frm">
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="add_edit_achievements_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php esc_html_e('Edit Achievement','ARMember');?></td>
				<td class="popup_content_text">
					<table class="arm_table_label_on_top">
                        <tr>
                            <th><?php esc_html_e('How user will get this badge?','ARMember'); ?></th>
                            <td>
                                <input type="hidden" name="arm_achievement_type" value="defaultbadge" id="arm_edit_achievement_type" class="arm_achievement_type_change_input"/>
                                <dl class="arm_selectbox arm_subscription_plan_form_dropdown arm_margin_right_0 arm_width_100_pct" >
                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_edit_achievement_type">
                                            <li data-label="<?php esc_html_e('Default Badge for All', 'ARMember');?>" data-value="defaultbadge"><?php esc_html_e('Default Badge for All', 'ARMember');?></li>
                                            <li data-label="<?php esc_html_e('Give this badge to roles', 'ARMember');?>" data-value="roles"><?php esc_html_e('Give this badge to roles', 'ARMember');?></li>
                                            <li data-label="<?php esc_html_e('Give this badge to plans', 'ARMember');?>" data-value="plans"><?php esc_html_e('Give this badge to plans', 'ARMember');?></li>
                                            <li data-label="<?php esc_html_e('Require achievement', 'ARMember');?>" data-value="require"><?php esc_html_e('Require achievement', 'ARMember');?></li>
                                        </ul>
                                    </dd>
                                </dl>
                                <input type="hidden" name="arm_achievement_options[defaultbadge]" value="default"/>
							</td>
						</tr>
                        <tr class="arm_badge_roles_options arm_badge_achieve_options" style="display:none;">
							<th><?php esc_html_e('Choose which users should receive this badge','ARMember'); ?></th>
							<td>
                                <input type="hidden" id="arm_edit_achieve_roles" name="arm_achievement_options[roles]" value="" data-msg-required="<?php esc_html_e('Please select role.', 'ARMember');?>"/>
                                <?php if (!empty($user_roles)): ?>
                                <dl class="arm_selectbox arm_width_100_pct">
                                    <dt><span>Please select role</span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_edit_achieve_roles" data-placeholder="<?php esc_html_e('Select Roles', 'ARMember');?>">
                                            <?php foreach ($user_roles as $roleKey => $roleName): ?>
                                            <li data-label="<?php echo esc_attr($roleName); ?>" data-value="<?php echo esc_attr($roleKey);?>"><?php echo esc_html($roleName);?></li>
                                            <?php endforeach;?>
                                        </ul>
                                    </dd>
                                </dl>
                                <?php else: ?>
                                    <?php esc_html_e('There is no any role availabel.', 'ARMember');?>
                                <?php endif;?>
                            </td>
                        </tr>
                        <tr class="arm_badge_plans_options arm_badge_achieve_options" style="display:none;">
							<th><?php esc_html_e('Choose which users should receive this badge','ARMember'); ?></th>
							<td>
                                <input type="hidden" id="arm_edit_achieve_plans" name="arm_achievement_options[plans]" value="" data-msg-required="<?php esc_html_e('Please select plan.', 'ARMember');?>"/>
                                <?php if (!empty($all_plans)): ?>
                                <dl class="arm_selectbox arm_width_100_pct">
                                    <dt><span>Please select plan</span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_edit_achieve_plans" data-placeholder="<?php esc_html_e('Select Plans', 'ARMember');?>">
                                            <?php foreach ($all_plans as $plan): ?>
                                            <li data-label="<?php echo stripslashes(esc_attr($plan['arm_subscription_plan_name'])); //phpcs:ignore?>" data-value="<?php echo esc_attr($plan['arm_subscription_plan_id']); ?>"><?php echo stripslashes( esc_html($plan['arm_subscription_plan_name']) ); //phpcs:ignore ?></li>
                                            <?php endforeach;?>
                                        </ul>
                                    </dd>
                                </dl>
                                <?php else: ?>
                                    <?php esc_html_e('There is no any plan configured yet.', 'ARMember');?>, <a href="<?php echo esc_url(admin_url('admin.php?page='.$arm_slugs->manage_plans.'&action=new'));?>"><?php esc_html_e('Please add new plan.', 'ARMember');?></a>
                                <?php endif;?>
                            </td>
                        </tr>
                        <tr class="arm_badge_require_options arm_badge_achieve_options" style="display:none;">
							<th><?php esc_html_e('Setup Achievement','ARMember'); ?></th>
                            <td>
                                <input type="hidden" id="arm_edit_achieve_require" name="arm_achievement_options[require]" value="post"/>
                                <dl class="arm_selectbox column_level_dd arm_width_100_pct">
                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd>
                                        <ul data-id="arm_edit_achieve_require">
                                        <?php foreach($achieve_types as $type => $label): ?>
                                            <li data-label="<?php echo esc_attr($label);?>" data-value="<?php echo esc_attr($type);?>"><?php echo esc_attr($label);?></li>
                                        <?php endforeach; ?>
                                        </ul>
                                    </dd>
                                </dl>                     
                            </td>
							<td id="arm_achieve_has_complete">
                                
							</td>
						</tr>
                        <tr class="arm_badge_tootip_require_options arm_badge_achieve_options" >
                            <th><?php esc_html_e('Tooltip Title','ARMember'); ?></th>
                            <td>
                                <input type="text" id="arm_badges_tooltip" name="arm_badges_tooltip" class="arm_badges_tooltip arm_width_100_pct arm_max_width_100_pct" value=""/>
                            </td>
                        </tr>
					</table>
					<div class="armclear"></div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
                        <input type="hidden" name="b_action" value="update"/>
                        <input type="hidden" id="arm_parent_badge_id" name="arm_badges_id" value="0"/>
                        <input type="hidden" id="arm_edit_badge_id" name="edit_badge_id" value="0"/>
						<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' //phpcs:ignore?>" id="arm_loader_img" class="arm_loader_img arm_submit_btn_loader" style="top: 15px;display: none;float: <?php echo (is_rtl()) ? 'right' : 'left';?>;" width="20" height="20" />
						<button class="arm_save_btn arm_button_manage_achievements" type="submit" data-type="edit"><?php esc_html_e('Save', 'ARMember') ?></button>
						<button class="arm_cancel_btn add_edit_achievements_close_btn" type="button"><?php esc_html_e('Cancel','ARMember');?></button>
					</div>
				</td>
			</tr>
		</table>
        <?php $wpnonce = wp_create_nonce( 'arm_wp_nonce' );?>
				<input type="hidden" name="arm_wp_nonce" value="<?php echo esc_attr($wpnonce);?>"/>
		<div class="armclear"></div>
	</form>
</div>