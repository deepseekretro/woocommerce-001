<?php
global $wpdb, $ARMember, $arm_global_settings, $arm_access_rules, $arm_subscription_plans, $arm_restriction ,$arm_drip_rules;
$all_global_settings = $arm_global_settings->arm_get_all_global_settings();

$general_settings = $all_global_settings['general_settings'];
$page_settings = $all_global_settings['page_settings'];
$general_settings['hide_feed'] = isset($general_settings['hide_feed']) ? $general_settings['hide_feed'] : 0;
$all_plans_data = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name, arm_subscription_plan_type', ARRAY_A, true);
$defaultRulesTypes = $arm_access_rules->arm_get_access_rule_types();
$default_rules = $arm_access_rules->arm_get_default_access_rules();
$all_roles = $arm_global_settings->arm_get_all_roles();
$is_restricted_admin_panel_checked = ($general_settings['restrict_admin_panel'] == 1) ? 'checked="checked"' : '';
$arm_restriction_settings ='';
if($section =='general_restriction')
{

    $arm_restriction_settings = '<table class="form-table">
        <tr class="form-field">
            <th class="arm-form-table-label">'. esc_html__('Restrict admin panel','ARMember').'</th>
            <td class="arm-form-table-content">
                <div class="armswitch arm_global_setting_switch">
                    <input type="checkbox" id="restrict_admin_panel" '.$is_restricted_admin_panel_checked.' value="1" class="armswitch_input" name="arm_general_settings[restrict_admin_panel]"/>
                    <label for="restrict_admin_panel" class="armswitch_label"></label>
                </div>
                <label for="restrict_admin_panel" class="arm_global_setting_switch_label">'. esc_html__('Restrict admin panel for non-admin users','ARMember').'</label>
            </td>
        </tr>';
        $restrict_admin_panel_section = ($general_settings['restrict_admin_panel'] == '1') ? '' : ' hidden_section';     
        $arm_restriction_settings .= '<tr class="form-field arm_exclude_role_for_restrict_admin '.$restrict_admin_panel_section.'">
            <th class="arm-form-table-label">'. esc_html__('Exclude role for restriction','ARMember').'</th>
                                <td class="arm-form-table-content">';
                                    $arm_exclude_role_for_restrict_admin = isset($general_settings['arm_exclude_role_for_restrict_admin']) ? explode(',', $general_settings['arm_exclude_role_for_restrict_admin']) : array();
                                    $arm_restriction_settings .= '<select id="arm_access_page_for_restrict_site" class="arm_chosen_selectbox arm_width_500" name="arm_general_settings[arm_exclude_role_for_restrict_admin][]" data-placeholder="'. esc_attr__('Select Role(s)..', 'ARMember').'" multiple="multiple" >';
                                            if (!empty($all_roles)){
                                                foreach ($all_roles as $role_key => $role_value) {
                                                    $selected_roles = (in_array($role_key, $arm_exclude_role_for_restrict_admin)) ? ' selected="selected"' : '';
                                                    $arm_restriction_settings .= '<option class="arm_message_selectbox_op" value="'. esc_attr($role_key).'" '.$selected_roles.'>'. stripslashes($role_value) .'</option>';
                                                }
                                            }
                                            else
                                            {
                                                $arm_restriction_settings .= '<option value="">'. esc_html__('No Pages Available', 'ARMember').'</option>';
                                            }
                                            $arm_restriction_settings .= '</select>
                                    <span class="arm_info_text arm_info_text_style" >
                                        ('. esc_html__('Selected roles will be able to access admin.','ARMember').')
                                    </span>
                                </td>
        </tr>';
        $is_hidden_field_checked = ($general_settings['hide_feed'] == 1) ? 'checked="checked"' : '';
        $arm_restriction_settings .= '<tr class="form-field">
            <th class="arm-form-table-label">'. esc_html__('Block RSS feeds', 'ARMember').'</th>
            <td class="arm-form-table-content">
                <div class="armswitch arm_global_setting_switch">
                    <input type="checkbox" id="hide_feed" '. $is_hidden_field_checked.' value="1" class="armswitch_input" name="arm_general_settings[hide_feed]"/>
                    <label for="hide_feed" class="armswitch_label"></label>
                </div>
                <label for="hide_feed" class="arm_global_setting_switch_label">'. esc_html__('Disable feeds access to everyone','ARMember').'</label>
            </td>
        </tr>';
        $rtl_float = (is_rtl()) ? 'right' : 'left';
        $rtl_margin = (is_rtl()) ? '7px 0 0 10px' : '7px 10px 0 0';
        $is_restricted_site_access = ($general_settings['restrict_site_access'] == 1) ? 'checked="checked"' :'';
        $restrict_site_access = ($general_settings['restrict_site_access'] == 1) ? '':'hidden_section';
        $arm_restriction_settings .= '<tr class="form-field">
            <th class="arm-form-table-label">'. esc_html__('Restrict entire website without login','ARMember').'</th>
            <td class="arm-form-table-content">						
                <div class="armswitch arm_global_setting_switch" style="display: inline-block;float: '. $rtl_float.';margin: '. $rtl_margin .';">
                    <input type="checkbox" id="restrict_site_access" '.$is_restricted_site_access.' value="1" class="armswitch_input" name="arm_general_settings[restrict_site_access]"/>
                    <label for="restrict_site_access" class="armswitch_label"></label>
                </div>
                <div class="restrict_site_access '.$restrict_site_access.'">
                    <div class="arm_info_text arm_margin_bottom_20" style="">'. esc_html__('If website is restricted, redirect visitor to following page','ARMember').':</div>';
    
                    $arm_restriction_settings .= $arm_global_settings->arm_wp_dropdown_pages(
                        array(
                            'selected'              => isset($page_settings['guest_page_id']) ? $page_settings['guest_page_id'] : 0,
                            'name'                  => 'arm_page_settings[guest_page_id]',
                            'id'                    => 'guest_page_id',
                            'show_option_none'      => esc_html__('Select Page','ARMember'),
                            'option_none_value'     => '0',
                            'echo'                  => 0,
                            'class'     => 'arm_regular_select',
                        )
                    );
                    $arm_restriction_settings .= '<span id="guest_page_id_error" class="arm_error_msg guest_page_id_error" style="display:none;">'.esc_html__('Please select guest page.', 'ARMember').'</span>
                </div>
            </td>
        </tr>';
        $restrict_site_css = ($general_settings['restrict_site_access'] != 1) ? 'style="display:none;"' : '';
        $arm_restriction_settings .='<tr class="form_field page_access_for_restrict_site" '.$restrict_site_css.'>
            <th class="arm-form-table-label">'. esc_html__('Exclude pages for restriction','ARMember').'</th>
            <td class="arm-form-table-content">';
                $defaults = array(
                        'depth' => 0, 'child_of' => 0,
                        'selected' => 0, 'echo' => 1,
                        'name' => 'page_id', 'id' => '',
                        'show_option_none' => 'Select Page', 'show_option_no_change' => '',
                        'option_none_value' => '',
                        'class' => '',
                        'required' => false,
                        'required_msg' => false,
                );
                $pages = get_pages($defaults);
                $arm_sel_access_page_for_restrict_site = array();
                if(isset($page_settings['arm_access_page_for_restrict_site']))
                {
                    $arm_sel_access_page_for_restrict_site = explode(',', $page_settings['arm_access_page_for_restrict_site']);
                }
                $global_setting_page = $arm_global_settings->arm_get_single_global_settings('page_settings');
                $allow_page_ids = $arm_restriction->arm_filter_allow_page_ids($global_setting_page);
                $is_allow_content_listed_checked = (!empty($default_rules['arm_allow_content_listing']) && $default_rules['arm_allow_content_listing'] == 1) ? "checked='checked'" : '';
                $arm_restriction_settings .='<select id="arm_access_page_for_restrict_site" class="arm_chosen_selectbox arm_width_500" name="arm_general_settings[arm_access_page_for_restrict_site][]" data-placeholder="'. esc_attr__('Select Page(s)..', 'ARMember').'" multiple="multiple" >';
                            if (!empty($pages)){
                                foreach ($pages as $p) {
                                    if(in_array($p->ID, $allow_page_ids)){ continue; }
                                    $selected_pages = (in_array($p->ID, $arm_sel_access_page_for_restrict_site)) ? ' selected="selected"' : '';
                                    $arm_restriction_settings .='<option class="arm_message_selectbox_op" value="'. esc_attr($p->ID).'" '. $selected_pages .'>'. stripslashes($p->post_title).'</option>';
                                }
                            }
                            else{
    
                                $arm_restriction_settings .='<option value="">'. esc_html__('No Pages Available', 'ARMember').'</option>';
                            }
                            $arm_restriction_settings .='</select>
                <span class="arm_info_text arm_info_text_style">
                    ('. esc_html__('Selected pages will be accessible to users without login.','ARMember') .')
                </span>
            </td>
        </tr>
        
        <tr class="form-field">
                <th class="arm-form-table-label">'. esc_html__('Allow restricted Pages/Posts in listing', 'ARMember').'</th>
                <td class="arm-form-table-content">
                    <div class="armswitch arm_global_setting_switch">
                            <input type="checkbox" id="arm_allow_content_listing" value="1" class="armswitch_input" name="arm_default_rules[arm_allow_content_listing]" '. $is_allow_content_listed_checked .'/>
                            <label for="arm_allow_content_listing" class="armswitch_label"></label>
                    </div>
                    <span class="arm_info_text arm_info_text_style">
                        ('. esc_html__('If you enable this switch than, restricted content will be displayed in listing only.','ARMember') .')
                    </span>
                </td>
        </tr>
        
    </table>';
}
if($arm_drip_rules->isDripFeature && $section =="drip_rules")
{ 
        
    $arm_drip_rules_sql = $wpdb->prepare("SELECT `arm_rule_id` FROM ".$ARMember->tbl_arm_drip_rules." WHERE arm_rule_status=%d",1); //phpcs:ignore --Reason $ARMember->tbl_arm_drip_rules is a table name
    $all_drip_id = $wpdb->get_results($arm_drip_rules_sql);//phpcs:ignore --Reason $arm_drip_rules_sql is a predefined query
    $arm_drip_array = array();
    foreach($all_drip_id as $ids)
    {
        $drip_id = $ids->arm_rule_id;
        if(!in_array($drip_id,$arm_drip_array))
        {
            array_push($arm_drip_array,$drip_id);
        }
    }
    $drip_ids = implode(',',$arm_drip_array);
    $arm_restriction_settings .= '<div class="arm_solid_divider"></div>
    <div class="page_sub_title" id="arm_global_drip_rules">
        '. esc_html__('Restriction Rules for Drip Contents', 'ARMember').'
        <i class="arm_helptip_icon armfa armfa-question-circle" title="'. esc_attr__("This Drip restriction content will be applied to all drip contents.", 'ARMember').'"></i>
    </div>
    <table class="form-table">
        <tbody>';
        $general_settings['arm_drip_restrict_old_posts'] = !empty($general_settings['arm_drip_restrict_old_posts'])? $general_settings['arm_drip_restrict_old_posts'] : 0;
        $general_settings['arm_allow_drip_expired_plan'] = !empty($general_settings['arm_allow_drip_expired_plan'])? $general_settings['arm_allow_drip_expired_plan'] : 0; 
        $general_settings['arm_allow_drip_expired_plan_is_sync'] = !empty($general_settings['arm_allow_drip_expired_plan_is_sync'])? $general_settings['arm_allow_drip_expired_plan_is_sync'] : 0; 

        $arm_drip_restrict_old_posts_checked = ($general_settings['arm_drip_restrict_old_posts'] == '1') ? "checked='checked'" : '';
        $arm_allow_drip_expired_plan_checked = ($general_settings['arm_allow_drip_expired_plan'] == '1') ? "checked='checked'" : '';

        $arm_allow_drip_expired_plan_hidden = (!empty($general_settings['arm_allow_drip_expired_plan']) && empty($general_settings['arm_allow_drip_expired_plan_is_sync'])) ? '' : 'hidden_section';

        $arm_restriction_settings .= '<tr class="form-field">
            <th class="arm-form-table-label">'. esc_html__("Allow content prior subscription", 'ARMember').' </th>
            <td class="arm-form-table-content">
                <div class="armswitch arm_global_setting_switch">
                    <input type="checkbox" id="arm_drip_restrict_old_posts" value="1" class="armswitch_input" name="arm_general_settings[arm_drip_restrict_old_posts]" '.$arm_drip_restrict_old_posts_checked.'>
                    <label for="arm_drip_restrict_old_posts" class="armswitch_label"></label>
                </div>
                <label for="arm_drip_restrict_old_posts" class="arm_global_setting_switch_label">'. esc_html__("Allow access to all content publish before subscription/plan purchase.", 'ARMember').'</label>
            </td>
        </tr>
        <tr class="form-field">
            <th class="arm-form-table-label">'. esc_html__("Allow content post subscription",'ARMember').' </th>
            <td class="arm-form-table-content">
                <div class="armswitch arm_global_setting_switch">
                    <input type="checkbox" id="arm_allow_drip_expired_plan" value="1" '.$arm_allow_drip_expired_plan_checked.' class="armswitch_input" name="arm_general_settings[arm_allow_drip_expired_plan]">
                    <label for="arm_allow_drip_expired_plan" class="armswitch_label"></label>
                </div>
                <label for="arm_allow_drip_expired_plan" class="arm_global_setting_switch_label">'. esc_html__("Allow access to content even after subscription/plan expired which was allowed during subscription.",'ARMember').'</label>
            </td>
        </tr>
        <tr class="form-field arm_sync_drip_row '.$arm_allow_drip_expired_plan_hidden.'" >
            <th class="arm-form-table-label"></th>
            <td class="arm-form-table-content arm_drip_rule_sync_btn_div">
                <input type="hidden" value="'. esc_attr($drip_ids).'" class="arm_drip_ids_sync">
                <input type="hidden" value="'. count($arm_drip_array).'" class="arm_drip_ids_sync_total">
                <input type="hidden" value="0" class="arm_drip_ids_sync_process">
                <input type="hidden" value="'. esc_attr($general_settings['arm_allow_drip_expired_plan_is_sync']).'" name="arm_general_settings[arm_allow_drip_expired_plan_is_sync]" id="arm_allow_drip_expired_plan_is_sync">

                <span class="arm_warning_text arm_margin_0 arm_margin_left_10 arm_margin_bottom_10">
                    <em>'. esc_html__('You need to sync dripped content once after enabling "Allow content post subscription" option. Please click the "Sync Dripped Content" button.','ARMember').'</em>
                </span>
                <img src="'. MEMBERSHIP_IMAGES_URL.'/arm_loader.gif" id="arm_loader_img_sync" class="arm_submit_btn_loader" style="display:none;" width="24" height="24">&nbsp;<button class="arm_save_btn arm_drip_rule_sync_btn arm_margin_left_5" data-total-users="3065" type="button" id="arm_drip_rule_sync_btn" name="arm_drip_rule_sync_btn" onClick="showConfirmBoxCallback_sync();">'. esc_html__('Sync Dripped Content','ARMember').'</button>';
                $arm_restriction_settings .= wp_nonce_field( 'arm_wp_nonce' );
                $arm_restriction_settings .= '<div class="armclear"></div>
                <div class="arm_drip_rule_sync_progressbar arm_margin_left_10">
                    <div class="arm_drip_rule_sync_progressbar_inner"></div>
                </div>
                <div class="arm_confirm_box" id="arm_confirm_box_arm_drip_sync">
                    <div class="arm_confirm_box_body">
                        <div class="arm_confirm_box_arrow"></div>
                        <div class="arm_confirm_box_text">'. esc_html__('Are you sure you want to sync dripped content data?','ARMember').' </div>
                        <div class="arm_confirm_box_btn_container">
                            <button type="button" class="arm_confirm_box_btn armok arm_start_drip_data" data-item_id="arm_start_drip_sync" data-type="arm_start_drip_sync">'. esc_html__('Yes', 'ARMember') .'</button>
                            <button type="button" class="arm_confirm_box_btn armcancel" onclick="hideConfirmBoxCallback_sync();">'. esc_html__('No', 'ARMember').'</button>
                        </div>
                    </div>
                </div>
            </td>
            <td></td>
        </tr>
        </tbody>
    </table>';

}