<?php

global $arm_global_settings,$arm_member_forms,$wp,$wpdb,$ARMember;
$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
$is_permalink = $arm_global_settings->is_permalink();
$general_settings = $all_global_settings['general_settings'];
$arm_html_content = '';

if($section=='wp-admin'){
    $arm_html_content = '<tr class="form-field">
    <th class="arm-form-table-label">'. esc_html__('Rename','ARMember').' wp-admin</th>
    <td class="arm-form-table-content">';
    $is_disabled = "";

    if(( $general_settings['rename_wp_admin'] != 1 && strpos(admin_url(),'wp-admin') === false ) || is_multisite()){
        $is_disabled = 'disabled="disabled"';
    }
    $is_wp_rename_enabled = ($general_settings['rename_wp_admin'] == '1') ? 'checked' : '';
    $is_disabled = (!$is_permalink || $is_disabled != '') ? 'disabled' : '';
    $label = (!$is_permalink) ? 'no_rename_wp_admin' : 'rename_wp_admin';
    $arm_html_content .= '<div class="armswitch arm_global_setting_switch"'. $is_disabled .'>
            <input type="checkbox" id="rename_wp_admin" '. $is_wp_rename_enabled .' '. $is_disabled.' value="1" class="armswitch_input" name="arm_general_settings[rename_wp_admin]"/>
            <label for="rename_wp_admin" class="armswitch_label"></label>
        </div>
        <label for="'. $label .'" class="arm_global_setting_switch_label">'. esc_html__('Rename', 'ARMember').' <strong>wp-admin</strong> '. esc_html__('folder', 'ARMember').'</label>';
        if (!$is_permalink){
            $arm_html_content .= '<span class="arm_warning_text"><em>'. esc_html__('Change permalink structure to enable this option', 'ARMember').'</em></span>';
        }
       

        if(is_multisite()){
            $arm_html_content .= '<br/><span class="arm_warning_text"><em> '. esc_html__('You cannot rename','ARMember').' wp-admin '. esc_html__('in multisite environment', 'ARMember').'.</em></span>';
        }
   else if($is_disabled != '' ){
    $arm_html_content .= '<br/><span class="arm_warning_text"><em> wp-admin '. esc_html__('is already renamed from other plugin. If you want to rename admin directory from', 'ARMember').' ARMember '. esc_html__('than, please remove admin directory renaming settings from other plugin','ARMember').'.</em></span>';
   }
   
   $arm_html_content .= '</td>
    </tr>';

    if($is_permalink){
        $rename_hidden_section = ($general_settings['rename_wp_admin'] == 1) ? '':'hidden_section';
        $arm_html_content .= '<tr class="arm_global_settings_sub_content rename_wp_admin '. $rename_hidden_section .'">
            <th class="arm-form-table-label">'. esc_html__('New','ARMember').' wp-admin '. esc_html__('Path','ARMember').'*</th>';
            $arm_html_content .= '<td class="arm-form-table-content">
                <input  type="text" id="new_wp_admin_path" value="'. esc_attr($general_settings['new_wp_admin_path']).'" class="arm_general_input" name="arm_general_settings[new_wp_admin_path]" placeholder="wp-admin"/>
                <br/>
                <em>('. esc_html__('EXPERIMENTAL', 'ARMember').') Change "/wp-admin" (e.g. panel, cp).</em>
                <br/>
                <br/>
                <span class="arm_warning_text arm_margin_0" ><em>'. esc_html__('Do Not change permalink structure to default in order to work this option. if you set permalink structure to default, You will need to DELETE or comment (//) line which start with', 'ARMember').': <code>define("ADMIN_COOKIE_PATH","...</code></em></span>';

                $arm_get_hide_wp_admin_option = get_option('arm_hide_wp_amin_disable');
            if (!empty($arm_get_hide_wp_admin_option)) {

                $arm_html_content .= '<br/><span class="arm_warning_text arm_margin_0" >';
                
                $arm_html_content .= '<em> '. esc_html__('If you can\'t login after renaming wp-admin, run below URL and all changes are rollback to default :', 'ARMember').'</em><br/>';
                $arm_html_content .= '<div class="arm_shortcode_text arm_form_shortcode_box">
            <span class="armCopyText arm_font_size_13" >'. home_url().'?arm_wpdisable='.$arm_get_hide_wp_admin_option.'</span>';
            $arm_html_content .= '<span class="arm_click_to_copy_text" data-code="'. home_url().'?arm_wpdisable='.esc_attr($arm_get_hide_wp_admin_option).'">'. esc_html__('Click to copy', 'ARMember').'</span>';
            $arm_html_content .= '<span class="arm_copied_text"><img src="'. MEMBERSHIP_IMAGES_URL.'/copied_ok.png" alt="ok">'. esc_html__('Code Copied', 'ARMember').'</span></div>';
            }
            
                
            $arm_html_content .= '</td>
        </tr>';
    }
    
}
if($section == 'armember_styling'){
    $is_arm_styling_disabled = ($general_settings['disable_wp_login_style'] == '1') ? 'checked' : '';
    $arm_html_content = '<tr class="form-field">
    <th class="arm-form-table-label">'. esc_html__('Disable ARMember styling on wp-login page', 'ARMember').'</th>
    <td class="arm-form-table-content">
        <div class="armswitch arm_global_setting_switch">
            <input type="checkbox" id="disable_wp_login_style" '. $is_arm_styling_disabled .' value="1" class="armswitch_input" name="arm_general_settings[disable_wp_login_style]"/>
            <label for="disable_wp_login_style" class="armswitch_label"></label>
        </div>
    </td>
</tr>';
}
if($section == 'currency_decimal'){
    $arm_html_content = apply_filters('arm_general_settings_after_currency_option','');
    $arm_html_content .= '<tr class="form-field" id="changeCurrency">
        <th class="arm-form-table-label">'. esc_html__('Number of decimals','ARMember').'</th>
        <td class="arm-form-table-content">';
        $general_settings['arm_currency_decimal_digit'] = (isset($general_settings['arm_currency_decimal_digit']) && $general_settings['arm_currency_decimal_digit']!= '') ? $general_settings['arm_currency_decimal_digit'] : 2;
        $arm_html_content .= '<input type="hidden" id="arm_currency_decimal_digit" name="arm_general_settings[arm_currency_decimal_digit]" value="'. esc_attr($general_settings['arm_currency_decimal_digit']).'" />
            <dl class="arm_selectbox column_level_dd" id="arm_currency_decimal">
                <dt><span class="arm_no_auto_complete">'. esc_html($general_settings['arm_currency_decimal_digit']).'</span><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                <dd>
                    <ul data-id="arm_currency_decimal_digit">
                        <li data-label="0" data-value="0">0</li>
                        <li data-label="1" data-value="1">1</li>
                        <li data-label="2" data-value="2">2</li>
                        <li data-label="3" data-value="3">3</li>
                    </ul>
                </dd>
            </dl>
        </td>
    </tr>';

    
}
if($section == 'badge_icons'){
    $badge_width = (!empty($general_settings['badge_width']) ? esc_attr($general_settings['badge_width']) : 30 );
    $badge_height = (!empty($general_settings['badge_height']) ? esc_attr($general_settings['badge_height']) : 30 );
    $arm_html_content = '<tr class="form-field">
        <th class="arm-form-table-label">'. esc_html__('Badge icon size', 'ARMember').'</th>
        <td class="arm-form-table-content">
            <div style="display: inline-block;margin-top:-5px;">
                <span class="arm_badge_size_field_label">'. esc_html__('Width', 'ARMember').'</span>
                <input id="arm_badge_width" class="arm_width_80"type="text" name="arm_general_settings[badge_width]" value="'.$badge_width.'"><span> (px)</span>
            </div>
            <div class="arm_margin_top_5">
                <span class="arm_badge_size_field_label" >'. esc_html__('Height', 'ARMember').'</span>
                <input id="arm_badge_height" type="text" class="arm_width_80" name="arm_general_settings[badge_height]" value="'.$badge_height.'" ><span> (px)</span>
            </div>
        </td>
    </tr>';
}
if($section == "gmail_options"){
	global $arm_email_settings;
	$all_email_settings = $arm_email_settings->arm_get_all_email_settings();
	if(empty($all_email_settings))
	{
		$all_email_settings = array();
	}
    $all_email_settings['arm_email_server'] = (isset($all_email_settings['arm_email_server'])) ? $all_email_settings['arm_email_server'] : 'wordpress_server';

    $is_google_server_checked = ($all_email_settings['arm_email_server'] == 'google_gmail') ? 'checked' : '';
    
    $arm_html_content = '<div class="arm_email_settings_select_text_inner">                
        <input type="radio" id="arm_email_server_gm" class="arm_general_input arm_email_notification_radio arm_iradio" '. $is_google_server_checked .' name="arm_email_server" value="google_gmail" />
        <label for="arm_email_server_gm" class="arm_email_settings_help_text">'. esc_html__('Google/Gmail','ARMember').'</label>';
        $ae_tooltip = esc_html__("The Gmail mailer works well for sites that send low numbers of emails. However, Gmail's API has rate limitations and a number of additional restrictions that can lead to challenges during setup. If you expect to send a high volume of emails, or if you find that your web host is not compatible with the Gmail API restrictions, then we recommend considering a different mailer option.", 'ARMember');
    $arm_html_content .= '<i class="arm_helptip_icon armfa armfa-question-circle" title="'. esc_attr($ae_tooltip).'"></i>
    </div>';
}
if($section == 'gmail_section'){
    global $arm_email_settings;
	$all_email_settings = $arm_email_settings->arm_get_all_email_settings();
	if(empty($all_email_settings))
	{
		$all_email_settings = array();
	}
    $arm_html_content = '<div class="arm_smtp_slide_form arm_email_server_google">
                <table class="form-sub-table" width="100%">
                    <tr>
                        <th class="arm_email_settings_content_label arm_min_width_100">'. esc_html__('Client ID','ARMember').' *</th>
                        <td class="arm_email_settings_content_text">';
                            $arm_gm_client_id = (isset($all_email_settings['arm_google_client_id'])) ? esc_attr($all_email_settings['arm_google_client_id']) : '';
                            $arm_html_content .= '<input type="text" id="arm_google_client_id" name="arm_google_client_id" value="'. $arm_gm_client_id.'" class="arm_google_client_id_input arm_width_390" >
                            <span class="error arm_invalid" id="arm_google_client_id_error" style="display: none;">'. esc_html__('Client ID should not be blank.', 'ARMember').'</span>
                        </td>
                    </tr>
                    <tr>
                        <th class="arm_email_settings_content_label">'. esc_html__('Client Secret','ARMember').' *</th>
                        <td class="arm_email_settings_content_text">';
                            $arm_google_client_secret = (isset($all_email_settings['arm_google_client_secret'])) ? esc_attr($all_email_settings['arm_google_client_secret']) : '';
                            $arm_html_content .= '<input type="text" id="arm_google_client_secret" class="arm_width_390" name="arm_google_client_secret" value="'.$arm_google_client_secret.'" />
                            <span class="error arm_invalid" id="arm_google_client_secret_error" style="display: none;">'. esc_html__('Client Secret can not be left blank.', 'ARMember').'</span>
                        </td>
                    </tr>
                    <tr>
                        <th class="arm_email_settings_content_label">'. esc_html__('Authorized redirect URI','ARMember').'*</th>
                        <td class="arm_email_settings_content_text">';
                            $arm_html_content .= '<input type="text" id="arm_google_auth_url" class="arm_width_390" value="'. get_home_url().'?page=arm_gmailapi" name="arm_google_auth_url" readonly/>
                            <span class="error arm_invalid" id="arm_google_auth_url_error" style="display: none;">'. esc_html__('Authorized redirect URL can not be left blank.', 'ARMember').'</span>
                        </td>
                    </tr>
                    <tr>
                        <th class="arm_email_settings_content_label">'. esc_html__('Authentication Token','ARMember').' *</th>
                        <td class="arm_email_settings_content_text">';
                            $arm_google_auth_token = (isset($all_email_settings['arm_google_auth_token'])) ? esc_attr($all_email_settings['arm_google_auth_token']) : '';
                            $arm_html_content .= '<input type="text" id="arm_google_auth_token" class="arm_width_390" value="'.$arm_google_auth_token.'" name="arm_google_auth_token" readonly/>
                            <span class="error arm_invalid" id="arm_google_auth_token_error" style="display: none;">'. esc_html__('Authorized redirect URL can not be left blank.', 'ARMember').'</span>
                        </td>
                    </tr>
                    <tr>
                        <th class="arm_email_settings_content_label"></th>
                        <td class="arm_email_settings_content_text">';
                            $arm_google_connected_account = (isset($all_email_settings['arm_google_connected_account'])) ? esc_attr($all_email_settings['arm_google_connected_account']) :'';
                            
                            $arm_google_auth_response = (isset($all_email_settings['arm_google_auth_response'])) ? esc_attr($all_email_settings['arm_google_auth_response']) :'';

                            $arm_gmail_verified_status = (isset($all_email_settings['arm_gmail_verified_status'])) ? esc_attr($all_email_settings['arm_gmail_verified_status']) : '';

                            $arm_gmail_verified_status_style = (isset($all_email_settings['arm_gmail_verified_status'])) ? '' : 'display:none';

                            $arm_google_auth_response_token = !empty($all_email_settings['arm_google_auth_response']) ? esc_html__('Gmail token is expired. Please Authenticate again.','ARMember') :'';

                            $arm_html_content .= '<input type="hidden" id="arm_google_connected_account" name="arm_google_connected_account" value="'.$arm_google_connected_account.'"/>
                            <input type="hidden" id="arm_google_auth_response" class="hidden_section" name="arm_google_auth_response" value="'.stripslashes_deep($arm_google_auth_response).'"/>
                            <button type="button" class="arm_connect_google arm_save_btn" id="arm_connect_google">'. esc_html__('Connect With Google Account', 'ARMember').'</button><img src="'. MEMBERSHIP_IMAGES_URL . '/arm_loader.gif" id="arm_connect_mail_loader" class="arm_connect_btn_loader" width="24" height="24" style="display: none;" />                                        
                            <input type="hidden" id="arm_gmail_verified_status" value="'.$arm_gmail_verified_status.'" name="arm_gmail_verified_status"/>
                            <span id="arm_google_gmail_api_verify" class="arm_success_msg arm_margin_left_10 " style="'. $arm_gmail_verified_status_style .'">'. esc_html__('Connected with Gmail API','ARMember').'</span>
                            <span id="arm_google_gmail_api_error" class="payment-errors arm_width_70_pct arm_margin_left_10 " style="display:none">'.$arm_google_auth_response_token.'</span>
                        </td>
                    </tr> 
                </table>
            </div>
            ';
}
if($section=='invoices_tax'){
    $arm_invoice_tax_feature = get_option('arm_is_invoice_tax_feature', 0);
    if($arm_invoice_tax_feature == 1) {
        $enable_tax = isset($general_settings['enable_tax']) ? $general_settings['enable_tax'] : 0;
        $tax_type = isset($general_settings['tax_type']) ? $general_settings['tax_type'] : 'common_tax';
        $tax_amount = isset($general_settings['tax_amount']) ? $general_settings['tax_amount'] : 0;
        $country_tax_default_val = isset($general_settings['arm_country_tax_default_val']) ? $general_settings['arm_country_tax_default_val'] : 0;
        $country_tax_field = isset($general_settings['country_tax_field']) ? $general_settings['country_tax_field'] : '';
        $country_tax_selected_opts = (isset($general_settings['arm_tax_country_name']) && $country_tax_field != '') ? maybe_unserialize($general_settings['arm_tax_country_name']) : array('');
        $country_tax_val_arr = (isset($general_settings['arm_country_tax_val']) && $country_tax_field != '') ? maybe_unserialize($general_settings['arm_country_tax_val']) : array('0');
        $total_selected_country = ($country_tax_field != '') ? count($country_tax_selected_opts) : 1;
        $country_tax_field_li = "<li data-label='".esc_html__('Select Country Field','ARMember')."' data-value=''>".esc_html__('Select Country Field', 'ARMember')."</li>";
        $country_tax_selected_field_opt_li = "<li data-label='".esc_html__('Select Country','ARMember')."' data-value=''>".esc_html__('Select Country', 'ARMember')."</li>";
        $dbFormFields = $arm_member_forms->arm_get_db_form_fields(true);
        if(!empty($dbFormFields)) {
            $all_fields = array();
            foreach ($dbFormFields as $meta_key => $opts) {
                if($opts["type"] == "radio" || $opts["type"] == "select") {
                    $all_fields[] = $meta_key;
                }
            }
            if(!empty($all_fields)) {
                $all_fields_placeholders = ' WHERE arm_form_field_slug IN (';
                $all_fields_placeholders .= rtrim( str_repeat( '%s,', count( $all_fields ) ), ',' );
                $all_fields_placeholders .= ')';

                array_unshift( $all_fields, $all_fields_placeholders );

                $fields_where = call_user_func_array(array( $wpdb, 'prepare' ), $all_fields );
                $form_field_arr = $wpdb->get_results("SELECT `arm_form_field_option` FROM `" . $ARMember->tbl_arm_form_field . "` ".$fields_where, ARRAY_A);//phpcs:ignore --Reason $ARMember->tbl_arm_form_field is a table name
                if(!empty($form_field_arr)) {
                    $form_field_arr = array_column($form_field_arr, "arm_form_field_option");
                    foreach ($form_field_arr as $key => $value) {
                        $arm_unsrlz_opts = maybe_unserialize($value);
                        $country_tax_field_li .= "<li data-label='".$arm_unsrlz_opts['label']."' data-value='".$arm_unsrlz_opts['meta_key']."'>".$arm_unsrlz_opts['label']."</li>";
                        if($country_tax_field != '' && $country_tax_field == $arm_unsrlz_opts['meta_key'] && !empty($arm_unsrlz_opts['options'])) {
                            $country_tax_selected_field_opt_li = '';
                            for($t = 0; $t < count($arm_unsrlz_opts['options']); $t++) {
                                $li_label = $li_value = $arm_unsrlz_opts['options'][$t];
                                if(strpos($arm_unsrlz_opts['options'][$t], ":") !== false) {
                                    $li_label = substr($arm_unsrlz_opts['options'][$t], 0, strpos($arm_unsrlz_opts['options'][$t], ":"));
                                    $li_value = substr($arm_unsrlz_opts['options'][$t], (strpos($arm_unsrlz_opts['options'][$t], ":")+1), strlen($arm_unsrlz_opts['options'][$t]));
                                }
                                $country_tax_selected_field_opt_li .= "<li data-label='".$li_label."' data-value='".$li_value."'>".$li_label."</li>";
                            }
                        }
                    }
                }
                else {
                    $country_tax_field = '';
                    $country_tax_selected_opts = array('');
                }
            }
            else {
                $country_tax_field = '';
                $country_tax_selected_opts = array('');
            }
        }
        else {
            $country_tax_field = '';
            $country_tax_selected_opts = array('');
        }
        $is_tax_option_enabled = ($enable_tax == '1') ? 'checked' : '';
        $arm_html_content = '<div class="arm_solid_divider"></div>
    <div class="page_sub_title">'. esc_html__('Sales Tax Settings','ARMember').'</div>
    <table class="form-table">
        <tr class="form-field">
            <th class="arm-form-table-label">'. esc_html__('Sales Tax','ARMember').'</th>
            <td class="arm-form-table-content">
                <div class="armswitch arm_global_setting_switch">
                    <input type="checkbox" id="enable_tax" '. $is_tax_option_enabled.' value="1" class="armswitch_input" name="arm_general_settings[enable_tax]"/>
                    <label for="enable_tax" class="armswitch_label"></label>
                </div>
                <label for="enable_tax" class="arm_global_setting_switch_label">'. esc_html__('Enable tax module on plan purchase','ARMember').'</label>
                <input type="hidden" id="arm_selected_tax_type" value="'. esc_attr($tax_type).'">
            </td>
        </tr>';
        $is_tax_enabled = ($enable_tax == '1') ? '' : 'hidden_section';
        $is_common_tax = ($tax_type == 'common_tax') ? 'checked' : '';
        $arm_html_content .= '<tr class="form-field arm_enable_tax '. $is_tax_enabled .'">
            <th class="arm-form-table-label">&nbsp;</th>
            <td class="arm-form-table-content">
                <input type="radio" id="arm_common_tax" class="arm_general_input arm_tax_type_radio arm_iradio" name="arm_general_settings[tax_type]" value="common_tax" '. $is_common_tax .' />
                <label for="arm_common_tax" class="arm_email_settings_help_text">'. esc_html__('Common Tax','ARMember').'&nbsp;('. esc_html__('for all countries','ARMember').')</label>
            </td>
        </tr>';
        $common_tax_section = ($enable_tax == '1' && $tax_type == 'common_tax') ? '' : 'hidden_section';
        $country_wise_tax = ($tax_type == 'country_tax') ? 'checked' : '';
        $arm_html_content .= '<tr class="form-field arm_enable_tax arm_enable_common_tax '.$common_tax_section.'">
            <th class="arm-form-table-label">&nbsp;</th>
            <td class="arm-form-table-content arm_common_tax_td">
                <input type="text" name="arm_general_settings[tax_amount]" id="tax_amount" value="'. esc_attr($tax_amount).'" placeholder="0"  onkeypress="return isNumber(event)"><b> %</b>

                <span class="arm_info_text">
                    ('. esc_html__('Percentage tax will be applied on Final Payable Amount on plan+signup page.','ARMember').')
                </span>
            </td>
        </tr>

        <tr class="form-field arm_enable_tax '.$is_tax_enabled.'">
            <th class="arm-form-table-label">&nbsp;</th>
            <td class="arm-form-table-content">
                <input type="radio" id="arm_country_tax" class="arm_general_input arm_tax_type_radio arm_iradio" name="arm_general_settings[tax_type]" value="country_tax" '.$country_wise_tax.' />
                <label for="arm_country_tax" class="arm_email_settings_help_text">'. esc_html__('Countrywise Tax','ARMember').'</label>
            </td>
        </tr>';
        $country_tax_section = ($enable_tax == '1' && $tax_type == 'country_tax') ? '' : 'hidden_section';
        $arm_html_content .= '<tr class="form-field arm_enable_tax arm_enable_country_tax '.$country_tax_section.'">
            <th class="arm-form-table-label">&nbsp;</th>
            <td class="arm-form-table-content arm_country_tax_td">
                '. esc_html__('Select Country Field','ARMember').'
                <input type="hidden" id="arm_country_tax_field" name="arm_general_settings[country_tax_field]" value="'. esc_attr($country_tax_field).'" />
                <dl class="arm_selectbox column_level_dd arm_country_tax_field_list">
                    <dt class="arm_country_tax_field_list_dt"><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                    <dd>
                        <ul data-id="arm_country_tax_field">'. $country_tax_field_li.'</ul>
                    </dd>
                    <span class="arm_country_field_err error arm_invalid hidden_section"><?php esc_html_e("Please Select Country Field", "ARMember"); ?></span>
                </dl>
                <i class="arm_helptip_icon armfa armfa-question-circle" title="'. esc_html__("select country field of signup form to be mapped, so tax can be applied upon country selection.", 'ARMember').'"></i>
                <img src="'. MEMBERSHIP_IMAGES_URL . '/arm_loader.gif" class="hidden_section" id="arm_country_tax_loader" />
            </td>
        </tr>';
        for($x = 0; $x < $total_selected_country; $x++) {
            $country_selected_options = !empty($country_tax_selected_opts[$x]) ? esc_attr($country_tax_selected_opts[$x]) : '';
            $country_selected_options_val = !empty($country_tax_val_arr[$x]) ? esc_attr($country_tax_val_arr[$x]) : '0'; 
            $arm_html_content .= '<tr class="form-field arm_enable_tax arm_enable_country_tax arm_country_tr '.$country_tax_section.'" data-ttl-tr="'. esc_attr($total_selected_country) .'">
            <th class="arm-form-table-label">&nbsp;</th>
            <td class="arm-form-table-content arm_country_tax_td">
                <div class="arm_tax_country_list_wrapper">
                    <input type="hidden" class="arm_country_tax_field_inpt" id="arm_country_tax_field_list_'. esc_attr($x).'" name="arm_general_settings[arm_tax_country_name][]" value="'.$country_selected_options.'" />
                    <dl class="arm_selectbox column_level_dd arm_tax_country_list_dl">
                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                        <dd>
                            <ul data-id="arm_country_tax_field_list_'. esc_attr($x).'">'. $country_tax_selected_field_opt_li.'</ul>
                        </dd>
                    </dl>

                    <input type="text" name="arm_general_settings[arm_country_tax_val][]" class="arm_country_tax_val_inpt arm_max_width_100" id="arm_country_tax_val_'. esc_attr($x).'" value="'.$country_selected_options_val.'"   onkeypress="return isNumber(event)"><b> %</b>
                    <div class="arm_country_tax_action_buttons">
                        <div class="arm_country_tax_plus_icon arm_helptip_icon tipso_style" title="'. esc_html__('Add Country', 'ARMember').'"></div>
                        <div class="arm_country_tax_minus_icon arm_helptip_icon tipso_style" title="'. esc_html__('Remove Country', 'ARMember').'"></div>
                    </div>
                </div>
            </td>
        </tr>
        <script type="text/javascript" charset="utf-8">
        var ARM_IMAGE_URL = "'. MEMBERSHIP_IMAGES_URL .'";
        var ARM_UPDATE_LABEL = "'. esc_html__('Update', 'ARMember').'";
        var EMESSAGE = "'. esc_html__('Minimum one country is required.', 'ARMember').'";
        var ARM_COUNTRY_DEFAULT_OPT = "'. esc_html__('Select Country', 'ARMember').'";
        var ARM_COUNTRY_NAME_ERROR = "'. esc_html__('Please Select Country', 'ARMember').'";
        </script>
        ';
        }
        $arm_html_content .= '<tr class="form-field arm_enable_tax arm_enable_country_tax '.$country_tax_section.'">
            <th class="arm-form-table-label">&nbsp;</th>
            <td class="arm-form-table-content arm_country_tax_td">
                '. esc_html__('Default Tax','ARMember').'
                
                <input type="text" name="arm_general_settings[arm_country_tax_default_val]" id="arm_country_tax_default_val" value="'. esc_attr($country_tax_default_val).'" style="max-width: 100px;" placeholder="0"  onkeypress="return isNumber(event)"><b> %</b>
                <i class="arm_helptip_icon armfa armfa-question-circle arm_helptip_icon_dtax" title="'. esc_html__("For any other country which is not in above list.", 'ARMember').'"></i>
            </td>
        </tr>';
        $tax_display_type = !empty($general_settings['arm_tax_include_exclude_flag']) ? $general_settings['arm_tax_include_exclude_flag'] : 0;
        $is_included_tax = ($tax_display_type == 1) ? 'checked' : '';
        $is_excluded_tax = ($tax_display_type == 0) ? 'checked' : '';
        $arm_html_content .= '<tr class="form-field arm_enable_tax arm_enable_include_exclude_tax '. $is_tax_enabled .'">
            <th class="arm-form-table-label">'.esc_html__('Include/Exclude Tax','ARMember').'</th>
            <td class="arm-form-table-content">

                <input type="radio" id="arm_excluded_tax" class="arm_general_input arm_tax_display_type_radio arm_iradio" name="arm_general_settings[arm_tax_include_exclude_flag]" value="0" '. $is_excluded_tax .' />
                <label for="arm_excluded_tax" class="arm_email_settings_help_text">'. esc_html__('Excluded Taxes','ARMember').'</label>

                <input type="radio" id="arm_included_tax" class="arm_general_input arm_tax_display_type_radio arm_iradio" name="arm_general_settings[arm_tax_include_exclude_flag]" value="1" '. $is_included_tax .' />
                <label for="arm_included_tax" class="arm_email_settings_help_text">'. esc_html__('Included Taxes','ARMember').'</label>                
            </td>
        </tr>
    </table>';                
    $invc_pre_sfx_mode = isset($general_settings['invc_pre_sfx_mode']) ? $general_settings['invc_pre_sfx_mode'] : 0;
    $invc_prefix_val = isset($general_settings['invc_prefix_val']) ? $general_settings['invc_prefix_val'] : '#';
    $invc_suffix_val = isset($general_settings['invc_suffix_val']) ? $general_settings['invc_suffix_val'] : '';
    $invc_min_digit = isset($general_settings['invc_min_digit']) ? $general_settings['invc_min_digit'] : 0;
    $is_invoice_pre_sfx_enabled = ($invc_pre_sfx_mode == '1') ? 'checked' : '';
    $arm_html_content .= '<div class="arm_solid_divider"></div>
    <div class="page_sub_title">'. esc_html__('Invoice Prefix/Suffix Settings','ARMember').'</div>
    <table class="form-table">
        <tr class="form-field">
            <th class="arm-form-table-label">'. esc_html__('Invoice Prefix/Suffix','ARMember').'</th>
            <td class="arm-form-table-content" colspan="3">
                <div class="armswitch arm_global_setting_switch">
                    <input type="checkbox" id="invc_pre_sfx_mode" '. $is_invoice_pre_sfx_enabled .' value="1" class="armswitch_input" name="arm_general_settings[invc_pre_sfx_mode]"/>
                    <label for="invc_pre_sfx_mode" class="armswitch_label"></label>
                </div>
                <label for="invc_pre_sfx_mode" class="arm_global_setting_switch_label">'. esc_html__('Enable Invoice Prefix/Suffix ?','ARMember').'</label>
            </td>
        </tr>';
        $invoice_suffix_mode = ($invc_pre_sfx_mode == '1') ? '' : ' hidden_section' ;
        $arm_html_content .= '<tr class="form-field arm_invc_pre_sfx_tr'.$invoice_suffix_mode.'">
            <th class="arm-form-table-label">'. esc_html__('Enter Invoice Prefix', 'ARMember').'</th>
            <td class="arm-form-table-content">
                <div>
                    <input type="text" name="arm_general_settings[invc_prefix_val]" id="arm_invc_prefix_val" value="'. esc_attr($invc_prefix_val).'" >
                </div>
            </td>
        </tr>
        <tr class="form-field arm_invc_pre_sfx_tr'.$invoice_suffix_mode.'">
            <th class="arm-form-table-label">'. esc_html__('Enter Invoice Suffix', 'ARMember').'</th>
            <td class="arm-form-table-content">
                <div>
                    <input type="text" name="arm_general_settings[invc_suffix_val]" id="arm_invc_suffix_val" value="'. esc_attr($invc_suffix_val).'" >
                </div>
            </td>
        </tr>
        <tr class="form-field arm_invc_pre_sfx_tr'.$invoice_suffix_mode.'">
            <th class="arm-form-table-label">'. esc_html__('Enter Minimum Invoice Digit(s)', 'ARMember').'</th>
            <td class="arm-form-table-content">
                <div>
                    <input type="number" name="arm_general_settings[invc_min_digit]" id="arm_invc_min_digit" value="'. esc_attr($invc_min_digit).'" >
                </div>
            </td>
        </tr>
    </table>';
    }
}
if($section=='google_recaptcha'){
    $arm_html_content = '<div class="arm_solid_divider"></div>
    <div class="page_sub_title">'. esc_html__('Google reCAPTCHA Configuration', 'ARMember');
    $arm_recaptcha_tooltip = esc_html__("reCAPTCHA requires an API key, consisting of a 'site' and a 'private' key. You can sign up for a", 'ARMember').' <a href="https://www.google.com/recaptcha/admin" target="_blank">'.esc_html__('free reCAPTCHA key.', 'ARMember').'</a>';
        $arm_html_content .= '<i class="arm_helptip_icon armfa armfa-question-circle" title="'. htmlentities( $arm_recaptcha_tooltip ) .'" ></i>
    </div>
    <table class="form-table">
        <tbody>
            <tr class="form-field">
                <th>'. esc_html__('Site Key', 'ARMember').' </th>
                <td>';
                    $arm_recaptcha_site_key = isset($general_settings['arm_recaptcha_site_key']) ? $general_settings['arm_recaptcha_site_key'] : '';
                    $arm_html_content .= '<input type="text" name="arm_general_settings[arm_recaptcha_site_key]" id="arm_recaptcha_site_key" value="'. esc_attr($arm_recaptcha_site_key) .'" />
                </td>
            </tr>
            <tr class="form-field">
                <th>'. esc_html__('Private Key', 'ARMember').' </th>
                <td>';
                    $arm_recaptcha_private_key = isset($general_settings['arm_recaptcha_private_key']) ? $general_settings['arm_recaptcha_private_key'] : '';
                    $arm_html_content .= '<input type="text" name="arm_general_settings[arm_recaptcha_private_key]" id="arm_recaptcha_private_key" value="'. esc_attr($arm_recaptcha_private_key).'" />
                </td>
            </tr>
            <tr class="form-field">
                <th>'. esc_html__('reCAPTCHA Theme', 'ARMember').' </th>
                <td>';
                    $arm_recaptcha_theme = !empty($general_settings['arm_recaptcha_theme']) ? $general_settings['arm_recaptcha_theme'] : 'light';
                    $arm_html_content .= '<input type="hidden" id="arm_recaptcha_theme" name="arm_general_settings[arm_recaptcha_theme]" value="'. esc_attr($arm_recaptcha_theme).'" />
                    <dl class="arm_selectbox column_level_dd">
                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                        <dd>
                            <ul data-id="arm_recaptcha_theme">
                                <li data-label="'. esc_html__('Light','ARMember').'" data-value="light">'. esc_html__('Light', 'ARMember').'</li>
                                <li data-label="'. esc_html__('Dark','ARMember').'" data-value="dark">'. esc_html__('Dark', 'ARMember').'</li>
                            </ul>
                        </dd>
                    </dl>
                </td>
            </tr>
            <tr class="form-field">
                <th>'. esc_html__('reCAPTCHA Language', 'ARMember').' </th>
                <td>';
                    $arm_rc_lang_list_option = '';
                    $arm_rc_selected_list_id = 'en';
                    $arm_rc_selected_list_label = esc_html__('English (US)', 'ARMember');
                    $arm_rc_lang = array();
                    $arm_rc_lang['en'] = esc_html__('English (US)', 'ARMember');
                    $arm_rc_lang['ar'] = esc_html__('Arabic', 'ARMember');
                    $arm_rc_lang['bn'] = esc_html__('Bengali', 'ARMember');
                    $arm_rc_lang['bg'] = esc_html__('Bulgarian', 'ARMember');
                    $arm_rc_lang['ca'] = esc_html__('Catalan', 'ARMember');
                    $arm_rc_lang['zh-CN'] = esc_html__('Chinese(Simplified)', 'ARMember');
                    $arm_rc_lang['zh-TW'] = esc_html__('Chinese(Traditional)', 'ARMember');
                    $arm_rc_lang['hr'] = esc_html__('Croatian', 'ARMember');
                    $arm_rc_lang['cs'] = esc_html__('Czech', 'ARMember');
                    $arm_rc_lang['da'] = esc_html__('Danish', 'ARMember');
                    $arm_rc_lang['nl'] = esc_html__('Dutch', 'ARMember');
                    $arm_rc_lang['en-GB'] = esc_html__('English (UK)', 'ARMember');
                    $arm_rc_lang['et'] = esc_html__('Estonian', 'ARMember');
                    $arm_rc_lang['fil'] = esc_html__('Filipino', 'ARMember');
                    $arm_rc_lang['fi'] = esc_html__('Finnish', 'ARMember');
                    $arm_rc_lang['fr'] = esc_html__('French', 'ARMember');
                    $arm_rc_lang['fr-CA'] = esc_html__('French (Canadian)', 'ARMember');
                    $arm_rc_lang['de'] = esc_html__('German', 'ARMember');
                    $arm_rc_lang['gu'] = esc_html__('Gujarati', 'ARMember');
                    $arm_rc_lang['de-AT'] = esc_html__('German (Autstria)', 'ARMember');
                    $arm_rc_lang['de-CH'] = esc_html__('German (Switzerland)', 'ARMember');
                    $arm_rc_lang['el'] = esc_html__('Greek', 'ARMember');
                    $arm_rc_lang['iw'] = esc_html__('Hebrew', 'ARMember');
                    $arm_rc_lang['hi'] = esc_html__('Hindi', 'ARMember');
                    $arm_rc_lang['hu'] = esc_html__('Hungarian', 'ARMember');
                    $arm_rc_lang['id'] = esc_html__('Indonesian', 'ARMember');
                    $arm_rc_lang['it'] = esc_html__('Italian', 'ARMember');
                    $arm_rc_lang['ja'] = esc_html__('Japanese', 'ARMember');
                    $arm_rc_lang['kn'] = esc_html__('Kannada', 'ARMember');
                    $arm_rc_lang['ko'] = esc_html__('Korean', 'ARMember');
                    $arm_rc_lang['lv'] = esc_html__('Latvian', 'ARMember');
                    $arm_rc_lang['lt'] = esc_html__('Lithuanian', 'ARMember');
                    $arm_rc_lang['ms'] = esc_html__('Malay', 'ARMember');
                    $arm_rc_lang['ml'] = esc_html__('Malayalam', 'ARMember');
                    $arm_rc_lang['mr'] = esc_html__('Marathi', 'ARMember');
                    $arm_rc_lang['no'] = esc_html__('Norwegian', 'ARMember');
                    $arm_rc_lang['fa'] = esc_html__('Persian', 'ARMember');
                    $arm_rc_lang['pl'] = esc_html__('Polish', 'ARMember');
                    $arm_rc_lang['pt'] = esc_html__('Portuguese', 'ARMember');
                    $arm_rc_lang['pt-BR'] = esc_html__('Portuguese (Brazil)', 'ARMember');
                    $arm_rc_lang['pt-PT'] = esc_html__('Portuguese (Portugal)', 'ARMember');
                    $arm_rc_lang['ro'] = esc_html__('Romanian', 'ARMember');
                    $arm_rc_lang['ru'] = esc_html__('Russian', 'ARMember');
                    $arm_rc_lang['sr'] = esc_html__('Serbian', 'ARMember');
                    $arm_rc_lang['sk'] = esc_html__('Slovak', 'ARMember');
                    $arm_rc_lang['sl'] = esc_html__('Slovenian', 'ARMember');
                    $arm_rc_lang['es'] = esc_html__('Spanish', 'ARMember');
                    $arm_rc_lang['es-149'] = esc_html__('Spanish (Latin America)', 'ARMember');
                    $arm_rc_lang['sv'] = esc_html__('Swedish', 'ARMember');
                    $arm_rc_lang['ta'] = esc_html__('Tamil', 'ARMember');
                    $arm_rc_lang['te'] = esc_html__('Telugu', 'ARMember');
                    $arm_rc_lang['th'] = esc_html__('Thai', 'ARMember');
                    $arm_rc_lang['tr'] = esc_html__('Turkish', 'ARMember');
                    $arm_rc_lang['uk'] = esc_html__('Ukrainian', 'ARMember');
                    $arm_rc_lang['ur'] = esc_html__('Urdu', 'ARMember');
                    $arm_rc_lang['vi'] = esc_html__('Vietnamese', 'ARMember');
                    foreach ($arm_rc_lang as $lang => $lang_name) {
                        if (isset($general_settings['arm_recaptcha_lang']) && $general_settings['arm_recaptcha_lang'] == $lang) {
                            $arm_rc_selected_list_id = esc_attr($lang);
                            $arm_rc_selected_list_label = $lang_name;
                        }
                        $arm_rc_lang_list_option .= '<li data-label="' . $lang_name . '" data-value="' . esc_attr($lang) . '" >' . $lang_name . '</li>';
                    }
                    $arm_html_content .= '<input type="hidden" id="arm_recaptcha_lang" name="arm_general_settings[arm_recaptcha_lang]" value="'. esc_attr($arm_rc_selected_list_id).'" />
                    <dl class="arm_selectbox column_level_dd">
                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                        <dd>
                            <ul data-id="arm_recaptcha_lang">
                                '.$arm_rc_lang_list_option.'
                            </ul>
                        </dd>
                    </dl>
                </td>
            </tr>
        </tbody>
    </table>';
}
if($section=='custom_css'){
    $arm_html_content = '<div class="arm_solid_divider"></div>';
    $arm_html_content .= '<div class="page_sub_title">'. esc_html__('Global CSS','ARMember').'
    <i class="arm_helptip_icon armfa armfa-question-circle arm_fix_toltip" title="'. esc_html__("The css you have entered here, will be applied to all the frontend pages which contains ARMember short code.", 'ARMember').'"></i>
    </div>
    <table class="form-table">
        <tr class="form-field">
            <th class="arm-form-table-label">'. esc_html__('Custom CSS','ARMember').'</th>
            <td class="arm-form-table-content">
                <div class="arm_custom_css_wrapper">';
                    $general_settings['global_custom_css'] = isset($general_settings['global_custom_css']) ? $general_settings['global_custom_css'] : '';
                    $arm_html_content .= '<textarea class="arm_codemirror_field" name="arm_general_settings[global_custom_css]" rows="9" cols="40">'. stripslashes_deep( esc_attr($general_settings['global_custom_css']) ).'</textarea>
                </div>
            </td>
        </tr>
        <tr class="form-field">
            <th class="arm-form-table-label"></th>
            <td class="arm-form-table-content">
                <span class="arm_global_setting_custom_css_eg">(e.g.)&nbsp;&nbsp; .arm_form_field_submit_button{color:#000000;}</span>
                <span class="arm_global_setting_custom_css_section">
                    <a class="arm_custom_css_detail arm_custom_css_detail_link" href="javascript:void(0)" data-section="arm_general">'. esc_html__('CSS Class Information', 'ARMember').'</a>
                </span>
            </td>
        </tr>
    </table>';
    $nonce = wp_create_nonce( 'arm_wp_nonce' );
    $arm_html_content .= '<input type="hidden" name="arm_wp_nonce" value = "'.$nonce.'"/>';
}
