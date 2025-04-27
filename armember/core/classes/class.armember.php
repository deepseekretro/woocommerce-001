<?php

$arm_geoip_file = MEMBERSHIP_LIBRARY_DIR.'/geoip/autoload.php';
if( file_exists($arm_geoip_file) ){
    include $arm_geoip_file; //phpcs:ignore
}
use GeoIp2\Database\Reader;

if (!class_exists('ARMember')) {
   class ARMember {

    var $arm_slugs;
    var $tbl_arm_activity;
    var $tbl_arm_auto_message;
    var $tbl_arm_coupons;
    var $tbl_arm_email_templates;
    var $tbl_arm_entries;
    var $tbl_arm_fail_attempts;
    var $tbl_arm_forms;
    var $tbl_arm_form_field;
    var $tbl_arm_lockdown;
    var $tbl_arm_members;
    var $tbl_arm_membership_setup;
    var $tbl_arm_payment_log;
    var $tbl_arm_bank_transfer_log;
    var $tbl_arm_subscription_plans;
    var $tbl_arm_termmeta;
    var $tbl_arm_member_templates;
    var $tbl_arm_drip_rules;
    var $tbl_arm_dripped_contents;
    var $tbl_arm_badges_achievements;
    var $tbl_arm_login_history;
    var $tbl_arm_debug_payment_log;
    var $tbl_arm_debug_general_log;


    function __construct() {
        global $wp, $wpdb, $arm_db_tables, $arm_access_rules, $arm_capabilities_global, $ARMemberAllowedHTMLTagsArray;

        $arm_db_tables = array(
            'tbl_arm_activity' => $wpdb->prefix . 'arm_activity',
            'tbl_arm_auto_message' => $wpdb->prefix . 'arm_auto_message',
            'tbl_arm_coupons' => $wpdb->prefix . 'arm_coupons',
            'tbl_arm_email_templates' => $wpdb->prefix . 'arm_email_templates',
            'tbl_arm_entries' => $wpdb->prefix . 'arm_entries',
            'tbl_arm_fail_attempts' => $wpdb->prefix . 'arm_fail_attempts',
            'tbl_arm_forms' => $wpdb->prefix . 'arm_forms',
            'tbl_arm_form_field' => $wpdb->prefix . 'arm_form_field',
            'tbl_arm_lockdown' => $wpdb->prefix . 'arm_lockdown',
            'tbl_arm_members' => $wpdb->prefix . 'arm_members',
            'tbl_arm_membership_setup' => $wpdb->prefix . 'arm_membership_setup',
            'tbl_arm_payment_log' => $wpdb->prefix . 'arm_payment_log',
            'tbl_arm_bank_transfer_log' => $wpdb->prefix . 'arm_bank_transfer_log',
            'tbl_arm_subscription_plans' => $wpdb->prefix . 'arm_subscription_plans',
            'tbl_arm_termmeta' => $wpdb->prefix . 'arm_termmeta',
            'tbl_arm_member_templates' => $wpdb->prefix . 'arm_member_templates',
            'tbl_arm_drip_rules' => $wpdb->prefix . 'arm_drip_rules',
            'tbl_arm_dripped_contents' => $wpdb->prefix . 'arm_dripped_contents',
            'tbl_arm_badges_achievements' => $wpdb->prefix . 'arm_badges_achievements',
            'tbl_arm_login_history' => $wpdb->prefix . 'arm_login_history',
            'tbl_arm_debug_payment_log' => $wpdb->prefix . 'arm_debug_payment_log',
            'tbl_arm_debug_general_log' => $wpdb->prefix . 'arm_debug_general_log',
        );
        /* Set Database Table Variables. */
        foreach ($arm_db_tables as $key => $table) {
            $this->$key = $table;
        }

        /* Set Page Slugs Global */
        $this->arm_slugs = $this->arm_page_slugs();
        /* Set Page Capabilities Global */
        $arm_capabilities_global = array(
            'arm_manage_subscriptions' => 'arm_manage_subscriptions',
            'arm_manage_members' => 'arm_manage_members',
            'arm_manage_plans' => 'arm_manage_plans',
            'arm_manage_setups' => 'arm_manage_setups',
            'arm_manage_forms' => 'arm_manage_forms',
            'arm_manage_access_rules' => 'arm_manage_access_rules',
            'arm_manage_drip_rules' => 'arm_manage_drip_rules',
            'arm_manage_transactions' => 'arm_manage_transactions',
            'arm_manage_email_notifications' => 'arm_manage_email_notifications',
            'arm_manage_communication' => 'arm_manage_communication',
            'arm_manage_member_templates' => 'arm_manage_member_templates',
            'arm_manage_general_settings' => 'arm_manage_general_settings',
            'arm_manage_private_content' => 'arm_manage_private_content',
            'arm_manage_pay_per_post' => 'arm_manage_pay_per_post',
            'arm_manage_feature_settings' => 'arm_manage_feature_settings',
            'arm_manage_block_settings' => 'arm_manage_block_settings',
            'arm_manage_coupons' => 'arm_manage_coupons',
            'arm_manage_payment_gateways' => 'arm_manage_payment_gateways',
            'arm_import_export' => 'arm_import_export',
            'arm_badges' => 'arm_badges',
            'arm_report_analytics' => 'arm_report_analytics',
            'arm_growth_plugins' => 'arm_growth_plugins',
        );

        register_activation_hook(MEMBERSHIP_DIR.'/armember.php', array('ARMember', 'install'));
        register_activation_hook(MEMBERSHIP_DIR.'/armember.php', array('ARMember', 'armember_check_network_activation'));
        /* Load Language TextDomain */
        add_action('init', array($this, 'arm_load_textdomain' ) );

        /* Add 'Addon' link in plugin list */
        add_filter('plugin_action_links', array($this, 'armPluginActionLinks'), 10, 2);
        add_action('switch_blog', array($this, 'wpdbfix'));

        add_filter('arm_admin_license_notice_html',array($this,'arm_admin_license_notice_html_func'),10,1);
	
        /* Add Document Video For First Time */
        add_action('wp_ajax_arm_do_not_show_video', array($this, 'arm_do_not_show_video'), 1);
        add_action('wp_ajax_arm_reinit_nonce_var',array($this,'arm_reinit_nonce_var_func'));
        add_action( 'wp_ajax_nopriv_arm_reinit_nonce_var', array($this,'arm_reinit_nonce_var_func'));
        /* Add what's new popup */
        add_action('wp_ajax_arm_dont_show_upgrade_notice', array($this, 'arm_dont_show_upgrade_notice'), 1);

        add_filter( 'plugin_action_links_armember-membership/armember-membership.php', array( $this, 'armember_remove_deactivation_links' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'armember_add_plugin_dependency' ) );

        add_action('arm_admin_messages', array($this, 'arm_admin_messages_init'));
        /* Include All Class Files. */
        
        //Query Monitor
        if( !function_exists('is_plugin_active') ){
            require(ABSPATH.'/wp-admin/includes/plugin.php');
        }
        if(is_plugin_active('elementor/elementor.php'))
        {
            require_once(MEMBERSHIP_CORE_DIR . '/classes/arm_builder/class.arm_elementor_membership_shortcode.php');
            global $ARMelementor;
            $ARMelementor = new arm_membership_elementcontroller();
        }
        if (is_plugin_active('js_composer/js_composer.php') && defined('MEMBERSHIPLITE_CORE_DIR') && file_exists(MEMBERSHIPLITE_CORE_DIR . '/vc/class_vc_extend.php')) {
            require_once(MEMBERSHIPLITE_CORE_DIR . '/vc/class_vc_extend.php');
            global $armlite_vcextend;
            $armlite_vcextend = new ARMLite_VCExtend();
        }

        if (is_plugin_active('js_composer/js_composer.php') && file_exists(MEMBERSHIP_CORE_DIR . '/vc/class_vc_extend.php')) {
            require_once(MEMBERSHIP_CORE_DIR . '/vc/class_vc_extend.php');
            global $arm_vcextend;
            $arm_vcextend = new ARM_VCExtend();
        }

        add_action('wp_ajax_arm_perform_update',array($this,'arm_perform_update_function'),1);
        add_action('arm_before_last_menu',array($this,'arm_update_plugin_to_new_version'),51);

        add_filter( 'heartbeat_received', array($this, 'arm_receive_heartbeat_func'), 10, 2 );
        add_filter( 'heartbeat_settings', array($this,'arm_heartbeat_settings') );


        add_action('admin_notices', array($this, 'arm_addon_version_admin_notices'));

        add_action('arm_payment_log_entry', array($this, 'arm_write_payment_log'), 10, 6);

        add_action('arm_general_log_entry', array( $this, 'arm_write_general_log'), 10, 4);

        add_action('wp_ajax_arm_get_need_help_content', array( $this, 'arm_get_need_help_content_func' ), 10, 1);

        add_action('wp_ajax_arm_google_dismisss_admin_notice',array($this, 'arm_google_dismisss_admin_notice'),10);

        add_action('wp_ajax_arm_dismiss_linkedin_openid_admin_notice',array($this, 'arm_dismiss_linkedin_openid_admin_notice'),10);

        add_action('wp_ajax_arm_armemberactivatelicense',array($this, 'arm_armember_package_update'),10);

        $ARMemberAllowedHTMLTagsArray = $this->armember_allowed_html_tags();

        add_filter( 'arm_alert_message_pro', array($this,'arm_alert_message_pro_func'), 10, 1 );
    }

    function arm_alert_message_pro_func($alertMessages){
        $alertMessages = $this->arm_alert_messages();
        return $alertMessages;
    }
    

    /**
     * Loading plugin text domain
     */
    function arm_load_textdomain() {
        load_plugin_textdomain('ARMember', false, dirname( plugin_basename( MEMBERSHIP_DIR . '/armember.php' ) ) . '/languages/');

        global $armPrimaryStatus, $armSecondaryStatus;
        $armPrimaryStatus = array(
            '1' => esc_html__('Active', 'ARMember'),
            '2' => esc_html__('Inactive', 'ARMember'),
            '3' => esc_html__('Pending', 'ARMember'),
            '4' => esc_html__('Terminated', 'ARMember'),
        );

        $armSecondaryStatus = array(
            '0' => esc_html__('by admin', 'ARMember'),
            '1' => esc_html__('Account Closed', 'ARMember'),
            '2' => esc_html__('Suspended', 'ARMember'),
            '3' => esc_html__('Expired', 'ARMember'),
            '4' => esc_html__('User Cancelled', 'ARMember'),
            '5' => esc_html__('Payment Failed', 'ARMember'),
            '6' => esc_html__('Cancelled', 'ARMember'),
        );

    }

    function armember_remove_deactivation_links($links){
        if( isset( $links['deactivate'] ) ){
			unset( $links['deactivate'] );
		}

        $required_note = esc_html__('Required by ARMember Premium', 'ARMember' );

        array_unshift( $links, '<label class="armember-pro-notice-plugin-label" style="color:#32373c;">'.$required_note.'</label>' );

        return $links;
    }

    function armember_add_plugin_dependency(){

        global $pagenow;
        
        if( 'plugins.php' == $pagenow){
            wp_register_script( 'armember-dependency', MEMBERSHIP_URL . '/js/arm_admin_dependency.js', array( 'jquery' ), MEMBERSHIP_VERSION );
            wp_enqueue_script( 'armember-dependency' );
        }

    }

    function arm_reinit_nonce_var_func(){
        global $ARMember,$arm_capabilities_global;

        $ARMember->arm_session_start();
        $form_key    = sanitize_text_field( $_POST['form_key'] ); //phpcs:ignore
        if ( ! empty( $form_key ) ) {
            if( !empty($_SESSION['ARM_FILTER_INPUT']) && !empty($_SESSION['ARM_FILTER_INPUT'][ $form_key ]) )
			{
                $action = isset($_POST['action']) ? sanitize_text_field( $_POST['action'] ) : ''; //phpcs:ignore
                if($action == 'arm_reinit_nonce_var'){ //phpcs:ignore
                    echo json_encode(array( 'nonce' => wp_create_nonce('arm_wp_nonce')));
                }
            }
        }
        die();
    }

    function arm_google_dismisss_admin_notice()
    {
        if(current_user_can( 'manage_options' ))
        {
            update_option('arm-google-dismiss-admin-notice', false);
            die();
        }
    }

    function arm_dismiss_linkedin_openid_admin_notice()
    {
        if(current_user_can( 'manage_options' ))
        {
            update_option('arm-linkedin-openid-admin-notice', false);
            die();
        }
    }
    function arm_addon_version_admin_notices()
    {
        $class = 'notice notice-error is-dismissible';

        $arm_plugin_list = "";
        if( file_exists(WP_PLUGIN_DIR.'/armembermultisite/armembermultisite.php') ){
            $arm_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/armembermultisite/armembermultisite.php' );
            $arm_addon_data_version = $arm_addon_data['Version'];

            if( $arm_addon_data_version < '1.1' ){
                $arm_plugin_list .= $arm_addon_data['Name'].', ';
            }
        }


        if( file_exists(WP_PLUGIN_DIR.'/armemberdirectlogins/armemberdirectlogins.php') ){
            $arm_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/armemberdirectlogins/armemberdirectlogins.php' );
            $arm_addon_data_version = $arm_addon_data['Version'];

            if( $arm_addon_data_version < '1.8' ){
                $arm_plugin_list .= $arm_addon_data['Name'].', ';
            }
        }

        if( file_exists(WP_PLUGIN_DIR.'/armemberdigitaldownload/armemberdigitaldownload.php') ){
            $arm_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/armemberdigitaldownload/armemberdigitaldownload.php' );
            $arm_addon_data_version = $arm_addon_data['Version'];

            if( $arm_addon_data_version < '1.7' ){
                $arm_plugin_list .= $arm_addon_data['Name'].', ';
            }
        }

        if( file_exists(WP_PLUGIN_DIR.'/armembercommunity/armembercommunity.php') ){
            $arm_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/armembercommunity/armembercommunity.php' );
            $arm_addon_data_version = $arm_addon_data['Version'];

            if( $arm_addon_data_version < '1.5' ){
                $arm_plugin_list .= $arm_addon_data['Name'].', ';
            }
        }

        if( file_exists(WP_PLUGIN_DIR.'/armemberaffiliate/armemberaffiliate.php') ){
            $arm_addon_data = get_plugin_data( WP_PLUGIN_DIR.'/armemberaffiliate/armemberaffiliate.php' );
            $arm_addon_data_version = $arm_addon_data['Version'];

            if( $arm_addon_data_version < '3.2' ){
                $arm_plugin_list .= $arm_addon_data['Name'].', ';
            }
        }

        if(!empty($arm_plugin_list)) {
            $arm_plugin_list = rtrim(trim($arm_plugin_list),',');
            printf( '<div class="%1$s" style="display: block !important;"><p><b>One or more add-on of ARMember must be updated with latest version</b> (%2$s).</p></div>', esc_attr( $class ), esc_html( $arm_plugin_list ) ); 
        }

        $arm_is_dismiss_notice = get_option('arm-stripe-dismiss-admin-notice');
        $arm_allowed_slugs = (array) $this->arm_slugs;
        if($arm_is_dismiss_notice && isset($_REQUEST['page']) && in_array($_REQUEST['page'], $arm_allowed_slugs))
        {
            $gateway_options = get_option('arm_payment_gateway_settings');
            $pgoptions = maybe_unserialize($gateway_options);
            $nonce = wp_create_nonce('arm_wp_nonce');
            if( !empty($pgoptions['stripe']['status']) )
            {
                printf("<div class='{$class} arm_dismiss_stripe_webhook_notice' data-arm_confirm='".esc_attr__('Are you sure you have added subscription_schedule.canceled Webhook Event at stripe Account?', 'ARMember')."' style='display:block;background: #ffbfc0;color:#a01a1b;'><p>". esc_html__('Please add','ARMember') ." <b>subscription_schedule.canceled</b> ".esc_html__('event for added Stripe Webhook at your stripe.com Account. To add this event please Login to your Stripe Account -> Developers -> Webhooks page -> Edit Webhook and select the event for the "Events to send" option.', 'ARMember')."</p></div><input type='hidden' name='arm_wp_nonce' value='".esc_attr($nonce)."'/>"); //phpcs:ignore
            }
        }
	
        $social_login_feature_admin_notice = get_option('arm-linkedin-openid-admin-notice');
        if ((!empty($social_login_feature_admin_notice) && $social_login_feature_admin_notice == 1) && isset($_REQUEST['page']) && in_array($_REQUEST['page'], $arm_allowed_slugs)) {
            printf( '<div class="%1$s arm_dismiss_linkedin_openid_admin_notice" data-arm_confirm="'.esc_attr__('Are you sure you have configured Sign In with LinkedIn using OpenID LinkedIn App?', 'ARMember').'" style="display: block !important;"><p><b>'. esc_html__('LinkedIn Sign API method is Deprecated so, please configure new "Sign In with LinkedIn using OpenID" connect to your LinkedIn App -> Select or add App -> Products -> "Sign In with LinkedIn using OpenID" click on "Request Access". For more information, %2$sclick here%3$s.', 'ARMember') .'</b></p></div>', esc_attr( $class ), '<a href="https://www.armemberplugin.com/documents/linkedin-signuplogin-integration/" target="_blank">','</a>' ); //phpcs:ignore
        }

        $arm_is_dismiss_notice = get_option('arm-google-dismiss-admin-notice');
        if( ($arm_is_dismiss_notice) && isset($_REQUEST['page']) && in_array($_REQUEST['page'], $arm_allowed_slugs) )
        {
            $nonce = wp_create_nonce('arm_wp_nonce');
            printf("<div class='".esc_attr( $class )." arm_dismiss_google_social_login_notice' data-arm_confirm='".esc_attr__('Are you sure you have updated Google Secret and Authorized Redirect URIs at Google Console?', 'ARMember')."' style='display:block;background: #ffbfc0;color:#a01a1b;'><p>". esc_html__('Please add','ARMember'). " <b>". esc_html__('Google Secret','ARMember')." </b>".esc_html__('and','ARMember')." <b>".esc_html__('Authorized Redirect URIs','ARMember')." </b>". esc_html__("provided at the General Settings -> Social Connect page at Google Configuration page.", 'ARMember')."</p></div><input type='hidden' name='arm_wp_nonce' value='".esc_attr($nonce)."'/>"); //phpcs:ignore
        }

        /** Lite version update failed notice */
        $arm_check_lite_update_notice = get_option( 'arm_show_lite_update_failed_notice' );

        $wp_plugin_update_notification = sprintf( esc_html__( 'To ensure full compatibility with the ARMember Pro version, please update the ARMember Lite to the latest version. It seems that the automatic update for ARMember Lite has been failed due to some reasons. For manual update instructions, please refer to %s', 'ARMember'), '<a href="https://www.armemberplugin.com/documents/getting-started-with-armember/#armember-manual-update" target="_blank">'.esc_html__('our documentation', 'ARMember').'</a>'); //phpcs:ignore

        if( !file_exists(WP_PLUGIN_DIR.'/armember-membership/armember-membership.php') ){
            $arm_lite_hide_plugin_notice = 0;
        }
        else {
            $arm_lite_hide_plugin_notice = is_plugin_active( 'armember-membership/armember-membership.php' );
        }

        if( empty($arm_lite_hide_plugin_notice) )
        {
            // show admin notice
            if( empty( $arm_check_lite_update_notice ) ) {
                echo "<div class='".esc_attr( $class )." arm_dismiss_arm_lite_update_force_notice' style='display:block;'><p>".$wp_plugin_update_notification."</p></div>"; //phpcs:ignore
                // end show admin notice
            }
        }

        if( 1 == $arm_check_lite_update_notice ) {
            
            /** recheck the lite version first */
            $arm_recheck_version = $this->arm_pro_force_check_for_plugin_update();
            // show admin notice
            if( true === $arm_recheck_version ) {

                echo "<div class='".esc_attr( $class )." arm_dismiss_arm_lite_update_force_notice' style='display:block;'><p>".$wp_plugin_update_notification."</p></div>"; //phpcs:ignore
            }
            // end show admin notice
        }
    }

    function wpdbfix() {
        global $wpdb, $arm_db_tables, $ARMember;
        $wpdb->arm_termmeta = $ARMember->tbl_arm_termmeta;
    }

    function arm_init_action() {
        global $wp, $wpdb, $arm_db_tables;
        $this->arm_slugs = $this->arm_page_slugs();
        /**
         * Start Session
         */
        //session_start();
        ob_start();
        /**
         * Plugin Hook for `Init` Actions
         */
        do_action('arm_init', $this);
    }

    /**
     * Include All File From Directory
     */
    function arm_include_class_files($dir_path = '') {

    }

    /**
     * Hide WordPress Update Notifications In Plugin's Pages
     */
    function arm_hide_update_notice() {
        global $wp, $wpdb, $arm_errors, $current_user, $ARMember, $pagenow, $arm_slugs;
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
            remove_action('admin_notices', 'update_nag', 3);
            remove_action('network_admin_notices', 'update_nag', 3);
            remove_action('admin_notices', 'maintenance_nag');
            remove_action('network_admin_notices', 'maintenance_nag');
            remove_action('admin_notices', 'site_admin_notice');
            remove_action('network_admin_notices', 'site_admin_notice');
            remove_action('load-update-core.php', 'wp_update_plugins');
            add_filter('pre_site_transient_update_core', array($this, 'arm_remove_core_updates'));
            add_filter('pre_site_transient_update_plugins', array($this, 'arm_remove_core_updates'));
            add_filter('pre_site_transient_update_themes', array($this, 'arm_remove_core_updates'));
            /* Remove BuddyPress Admin Notices */
            remove_action('bp_admin_init', 'bp_core_activation_notice', 1010);
            if (!in_array($_REQUEST['page'], array($arm_slugs->manage_forms))) {
                add_action('admin_notices', array($this, 'arm_admin_notices'));
            }
            global $arm_drip_rules, $arm_social_feature, $arm_manage_coupons, $arm_members_badges, $arm_private_content_feature, $arm_pay_per_post_feature;
            if ($_REQUEST['page'] == $arm_slugs->drip_rules && !$arm_drip_rules->isDripFeature) {
                $armAddonsLink = admin_url('admin.php?page=' . $arm_slugs->feature_settings.'&arm_activate_drip_feature=1');
                wp_safe_redirect( $armAddonsLink);
                exit;
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->profiles_directories, $arm_slugs->badges_achievements)) && !$arm_social_feature->isSocialFeature) {
                $armAddonsLink = admin_url('admin.php?page=' . $arm_slugs->feature_settings.'&arm_activate_social_feature=1');
                wp_safe_redirect( $armAddonsLink);
                exit;
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->private_content)) && !$arm_private_content_feature->isPrivateContentFeature) {
                $armAddonsLink = admin_url('admin.php?page=' . $arm_slugs->feature_settings.'&arm_activate_private_content_feature=1');
                wp_safe_redirect( $armAddonsLink);
                exit;
            }
            if ($_REQUEST['page'] == $arm_slugs->coupon_management && !$arm_manage_coupons->isCouponFeature) {
                $armAddonsLink = admin_url('admin.php?page=' . $arm_slugs->feature_settings.'&arm_activate_coupon_feature=1');
                wp_safe_redirect( $armAddonsLink);
                exit;
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->pay_per_post)) && !$arm_pay_per_post_feature->isPayPerPostFeature) {
                $armAddonsLink = admin_url('admin.php?page=' . $arm_slugs->feature_settings.'&arm_activate_pay_per_pst_feature=1');
                wp_safe_redirect( $armAddonsLink);
                exit;
            }
        }
    }

    function arm_admin_notices() {
        global $wp, $wpdb, $arm_errors, $ARMember, $pagenow, $arm_global_settings;
        $notice_html = '';
        $notices = array();
        $notices = apply_filters('arm_display_admin_notices', $notices);
        
        if (!empty($notices)) {
            $notice_html .= '<div class="arm_admin_notices_container">';
            $notice_html .= '<ul class="arm_admin_notices">';
            foreach ($notices as $notice) {
                $notice_html .= '<li class="arm_notice arm_notice_' . $notice['type'] . '">' . $notice['message'] . '</li>';
            }
            $notice_html .= '</ul>';
            $notice_html .= '<div class="armclear"></div></div>';
        }
        
        $arm_get_php_version = (function_exists('phpversion')) ? phpversion() : 0;
        if(version_compare($arm_get_php_version, '5.6', '<')) {
            $notice_html .= '<div class="notice notice-warning" style="display:block;">';
            $notice_html .= '<p>'.esc_html__('ARMember recommend to use Minimum PHP version 5.6 or greater.', 'ARMember').'</p>';
            $notice_html .= '</div>';
        }
        if(!empty($arm_global_settings->global_settings['enable_crop'])) {
            if (!function_exists('gd_info')) {
                $notice_html .= '<div class="notice notice-error" style="display:block;">';
                $notice_html .= '<p>'.esc_html__("ARMember requires PHP GD Extension module at the server. And it seems that it's not installed or activated. Please contact your hosting provider for the same.", "ARMember").'</p>';
                $notice_html .= '</div>';
            }
            if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON) {
                $notice_html .= '<div class="notice notice-error" style="display:block;">';
                $notice_html .= '<p>'.esc_html__("ARMember Plugin recommends to keep enable WordPress Cron (Scheduler) so if you have disabled Cron using DISABLE_WP_CRON from WordPress Config file or thrid party plugin(s) then, kindly enable WordPress CRON in order to work ARMember properly.", "ARMember").'</p>';
                $notice_html .= '</div>';
            }
        }

        echo $notice_html; //phpcs:ignore
    }

    function arm_set_message($type = 'error', $message = '') {
        global $wp, $wpdb, $arm_errors, $ARMember, $pagenow;
        if (!empty($message)) {
            $ARMember->arm_session_start();
            $_SESSION['arm_message'][] = array(
                'type' => $type,
                'message' => $message,
            );
        }
        return;
    }

    function arm_remove_core_updates() {
        global $wp_version;
        return(object) array('last_checked' => time(), 'version_checked' => $wp_version,);
    }

    function arm_set_adminmenu() {
        global $menu, $submenu, $parent_file, $ARMember;
        $ARMember->arm_session_start();
        if(isset($_SESSION['arm_admin_menus']))
        {
            unset($_SESSION['arm_admin_menus']);
        }
        $_SESSION['arm_admin_menus'] = array('main_menu' => $menu, 'submenu' => $submenu);
        if (isset($submenu['arm_manage_members']) && !empty($submenu['arm_manage_members'])) {
            $armAdminMenuScript = '<script type="text/javascript">';
            $armAdminMenuScript .= 'jQuery(document).ready(function ($) {';
            $armAdminMenuScript .= 'jQuery("#toplevel_page_arm_manage_members").find("ul li").each(function(){
					var thisLI = jQuery(this);
					thisLI.addClass("arm-submenu-item");
					var thisLinkHref = thisLI.find("a").attr("href");
					if(thisLinkHref != "" && thisLinkHref != undefined){
						var thisLinkClass = thisLinkHref.replace("admin.php?page=","");
						thisLI.addClass(thisLinkClass);
					}
				});
				jQuery(".arm_documentation a, .arm-submenu-item a[href=\"admin.php?page=arm_documentation\"]").attr("target", "_blank");';

            $docLink = MEMBERSHIP_DOCUMENTATION_URL;
            $armAdminMenuScript .= 'jQuery(".arm_documentation a, .arm-submenu-item a[href=\"admin.php?page=arm_documentation\"]").attr("href", "' . $docLink . '");';

            $armAdminMenuScript .= '});';

            $armAdminMenuScript .= '</script>';
            $armAdminMenuScript .= '<style type="text/css">';
            global $arm_drip_rules, $arm_social_feature, $arm_manage_coupons, $arm_members_badges, $arm_private_content_feature,$arm_global_settings, $arm_pay_per_post_feature;
            $arm_all_block_settings = $arm_global_settings->arm_get_all_block_settings();
            if (!$arm_private_content_feature->isPrivateContentFeature) {
                $armAdminMenuScript .= '.arm-submenu-item.arm_manage_private_content{display:none;}';
            }
            if (!$arm_drip_rules->isDripFeature) {
                $armAdminMenuScript .= '.arm-submenu-item.arm_drip_rules{display:none;}';
            }
            if (!$arm_social_feature->isSocialFeature) {
                $armAdminMenuScript .= '.arm-submenu-item.arm_profiles_directories{display:none;}';
            }
            if (!$arm_manage_coupons->isCouponFeature) {
                $armAdminMenuScript .= '.arm-submenu-item.arm_coupon_management{display:none;}';
            }
            if (!$arm_social_feature->isSocialFeature) {
                $armAdminMenuScript .= '.arm-submenu-item.badges_achievements{display:none;}';
            }
            if(empty($arm_all_block_settings['track_login_history'])){
                $armAdminMenuScript .= '.arm-submenu-item.arm_member_login_report_analytics{display:none;}';
            }
            if (!$arm_pay_per_post_feature->isPayPerPostFeature) {
                $armAdminMenuScript .= '.arm-submenu-item.arm_manage_pay_per_post{display:none;}';
            }
            $armAdminMenuScript .= '.arm-submenu-item.arm_feature_settings a{color:#ffff00 !important;}';
            $armAdminMenuScript .= '</style>';
            echo $armAdminMenuScript; //phpcs:ignore
        }
    }

    function ARM_EndSession() {
        //@session_destroy();
        $arm_action = isset($_POST['arm_action']) ? sanitize_text_field($_POST['arm_action']) : ''; //phpcs:ignore

        if(isset($_SESSION['arm_bp_sync_users'])) { unset($_SESSION['arm_bp_sync_users']); }
        if(isset($_SESSION['arm_site_permalink_is_changed'])) { unset($_SESSION['arm_site_permalink_is_changed']); }
        if(isset($_SESSION['arm_restricted_page_url'])) { unset($_SESSION['arm_restricted_page_url']); }
        if(isset($_SESSION['imported_users'])) { unset($_SESSION['imported_users']); }
        if(isset($_SESSION['arm_member_addon'])) { unset($_SESSION['arm_member_addon']); }
        if(isset($_SESSION['arm_message'])) { unset($_SESSION['arm_message']); }
        if(isset($_SESSION['arm_admin_menus'])) { unset($_SESSION['arm_admin_menus']); }
        if(isset($_SESSION['arm_completed_dripped'])) { unset($_SESSION['arm_completed_dripped']); }
        if(isset($_SESSION['arm_completed_member'])) { unset($_SESSION['arm_completed_member']); }
        if(isset($_SESSION['arm_total_drips'])) { unset($_SESSION['arm_total_drips']); }
        if(isset($_SESSION['arm_optins_sync_users'])) { unset($_SESSION['arm_optins_sync_users']); }
        if(isset($_SESSION['arm_optins_total_users'])) { unset($_SESSION['arm_optins_total_users']); }

        if($arm_action!="change-password")
		{
			if(isset($_SESSION['ARM_FILTER_INPUT'])) { unset($_SESSION['ARM_FILTER_INPUT']); };
			if(isset($_SESSION['ARM_VALIDATE_SCRIPT'])) { unset($_SESSION['ARM_VALIDATE_SCRIPT']); }
		}
    }

   

    /* Setting Capabilities for user */

    function arm_capabilities() {
        $cap = array(
            'arm_manage_subscriptions' => esc_html__('Manage Subscriptions', 'ARMember'),
            'arm_manage_members' => esc_html__('Manage Members', 'ARMember'),
            'arm_manage_plans' => esc_html__('Manage Plans', 'ARMember'),
            'arm_manage_setups' => esc_html__('Manage Setups', 'ARMember'),
            'arm_manage_forms' => esc_html__('Manage Form Settings', 'ARMember'),
            'arm_manage_access_rules' => esc_html__('Manage Access Rules', 'ARMember'),
            'arm_manage_drip_rules' => esc_html__('Manage Drip Rules', 'ARMember'),
            'arm_manage_transactions' => esc_html__('Manage Transactions', 'ARMember'),
            'arm_manage_email_notifications' => esc_html__('Manage Email Notifications', 'ARMember'),
            'arm_manage_communication' => esc_html__('Manage Communication', 'ARMember'),
            'arm_manage_member_templates' => esc_html__('Manage Member Templates', 'ARMember'),
            'arm_manage_general_settings' => esc_html__('Manage General Settings', 'ARMember'),
            'arm_manage_feature_settings' => esc_html__('Manage Feature Settings', 'ARMember'),
            'arm_manage_private_content' => esc_html__('Manage Private Content', 'ARMember'),
            'arm_manage_pay_per_post' => esc_html__('Manage Paid Posts', 'ARMember'),
            'arm_manage_license' => esc_html__('Manage License', 'ARMember'),
            'arm_manage_block_settings' => esc_html__('Manage Block Settings', 'ARMember'),
            'arm_manage_coupons' => esc_html__('Manage coupons', 'ARMember'),
            'arm_manage_payment_gateways' => esc_html__('Manage Payment Gateways', 'ARMember'),
            'arm_import_export' => esc_html__('Manage Import/Export', 'ARMember'),
            'arm_badges' => esc_html__('Badge And Achievements Management', 'ARMember'),
            'arm_report_analytics' => esc_html__('Reports', 'ARMember'),
            'arm_growth_plugins'             => esc_html__( 'Growth Plugins', 'ARMember' ),
        );
        return $cap;
    }

    function arm_page_slugs() {
        global $ARMember, $arm_slugs;
        $arm_slugs = new stdClass;
        /* Admin-Pages-Slug */
        $arm_slugs->main = 'arm_manage_members';
        $arm_slugs->manage_members = 'arm_manage_members';
        $arm_slugs->manage_subscriptions = 'arm_manage_subscriptions';
        $arm_slugs->manage_plans = 'arm_manage_plans';
        $arm_slugs->membership_setup = 'arm_membership_setup';
        $arm_slugs->manage_forms = 'arm_manage_forms';
        $arm_slugs->access_rules = 'arm_access_rules';
        $arm_slugs->drip_rules = 'arm_drip_rules';
        $arm_slugs->transactions = 'arm_transactions';
        $arm_slugs->email_notifications = 'arm_email_notifications';
        $arm_slugs->coupon_management = 'arm_coupon_management';
        $arm_slugs->general_settings = 'arm_general_settings';
        $arm_slugs->feature_settings = 'arm_feature_settings';
        $arm_slugs->licensing = 'arm_manage_license';
        $arm_slugs->documentation = 'arm_documentation';
        $arm_slugs->profiles_directories = 'arm_profiles_directories';
        $arm_slugs->private_content = 'arm_manage_private_content';
        $arm_slugs->pay_per_post = 'arm_manage_pay_per_post';
        $arm_slugs->badges_achievements = 'badges_achievements';
        $arm_slugs->report_analytics = 'arm_report_analytics';
        $arm_slugs->arm_setup_wizard = 'arm_setup_wizard';
        $arm_slugs->arm_growth_plugins = 'arm_growth_plugins';

        $arm_slugs = apply_filters('arm_page_slugs_modify_external', $arm_slugs);

        return $arm_slugs;
    }

    /**
     * Setting Menu Position
     */
    function get_free_menu_position($start, $increment = 0.1) {
        foreach ($GLOBALS['menu'] as $key => $menu) {
            $menus_positions[] = floatval($key);
        }
        if (!in_array($start, $menus_positions)) {
            $start = strval($start);
            return $start;
        } else {
            $start += $increment;
        }
        /* the position is already reserved find the closet one */
        while (in_array($start, $menus_positions)) {
            $start += $increment;
        }
        $start = strval($start);
        return $start;
    }

    function arm_admin_license_notice_html_func($arm_license_notice){
        global $arm_members_activity;
        $setact = 0;
        global $check_sorting;
        $setact = $arm_members_activity->$check_sorting();
        if ($setact != 1) {
            $admin_css_url = admin_url('admin.php?page=arm_manage_license');
        
            $arm_license_notice = '<div style="margin-top:20px;margin-bottom:20px;border-left: 4px solid #ffba00;box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);height:20px;width:99%;padding:10px 0px 10px 10px;background-color:#ffffff;color:#000000;font-size:16px;display:block;visibility:visible;text-align:left;" >ARMember License is not activated. Please activate license from <a href="'. esc_url($admin_css_url).'">here</a></div>';
        }
        return $arm_license_notice;
    }

    function armPluginActionLinks($links, $file) {
        global $wp, $wpdb, $ARMember, $arm_slugs;
        if ($file == plugin_basename(MEMBERSHIP_DIR.'/armember.php')) {
            $armAddonsLink = admin_url('admin.php?page=' . $arm_slugs->feature_settings);
            $link = '<a title="' . esc_html__('Add-ons', 'ARMember') . '" href="' . esc_url($armAddonsLink) . '">' . esc_html__('Add-ons', 'ARMember') . '</a>';
            array_unshift($links, $link); /* Add Link To First Position */
        }
        return $links;
    }

    function arm_admin_body_class($classes) {
        global $pagenow, $arm_slugs;
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
            $classes .= ' arm_wpadmin_page ';
        }
        return $classes;
    }

    /**
     * Adding Membership Admin Menu(s)
     */
    function arm_menu() {
        global $wp, $wpdb, $current_user, $arm_errors, $ARMember, $arm_slugs, $arm_global_settings, $arm_social_feature, $arm_membership_setup, $arm_manage_coupons;

        $arm_is_wizard_complete = get_option('arm_is_wizard_complete');

        $place = $this->get_free_menu_position(26.1, 0.3);
        if (version_compare($GLOBALS['wp_version'], '3.8', '<')) {
            echo "<style type='text/css'>.toplevel_page_arm_manage_members .wp-menu-image img{margin-top:-4px !important;}.toplevel_page_arm_manage_members .wp-menu-image .wp-menu-name{padding-left:30px !important;;}</style>";
        }
        if(empty($arm_is_wizard_complete) || $arm_is_wizard_complete == 0)
        {
            $arm_menu_hook = add_menu_page('ARMember', esc_html__('ARMember', 'ARMember'), 'arm_manage_members', $arm_slugs->arm_setup_wizard, array($this, 'route'), MEMBERSHIP_IMAGES_URL . '/armember_menu_icon.png', $place);
        }
        else{
            $arm_menu_hook = add_menu_page('ARMember', esc_html__('ARMember', 'ARMember'), 'arm_manage_members', $arm_slugs->main, array($this, 'route'), MEMBERSHIP_IMAGES_URL . '/armember_menu_icon.png', $place);
        }
        $admin_menu_items = array(
            $arm_slugs->manage_members => array(
                'name' => esc_html__('Manage Members', 'ARMember'),
                'title' => esc_html__('Manage Members', 'ARMember'),
                'capability' => 'arm_manage_members'
            ),
            $arm_slugs->manage_plans => array(
                'name' => esc_html__('Manage Plans', 'ARMember'),
                'title' => esc_html__('Manage Plans', 'ARMember'),
                'capability' => 'arm_manage_plans'
            ),
            $arm_slugs->membership_setup => array(
                'name' => esc_html__('Configure Plan + Signup Page', 'ARMember'),
                'title' => esc_html__('Configure Plan + Signup Page', 'ARMember'),
                'capability' => 'arm_manage_setups'
            ),
            $arm_slugs->manage_forms => array(
                'name' => esc_html__('Manage Forms', 'ARMember'),
                'title' => esc_html__('Manage Forms', 'ARMember'),
                'capability' => 'arm_manage_forms'
            ),
            $arm_slugs->access_rules => array(
                'name' => esc_html__('Content Access Rules', 'ARMember'),
                'title' => esc_html__('Content Access Rules', 'ARMember'),
                'capability' => 'arm_manage_access_rules'
            ),
            $arm_slugs->drip_rules => array(
                'name' => esc_html__('Drip Content', 'ARMember'),
                'title' => esc_html__('Drip Content', 'ARMember'),
                'capability' => 'arm_manage_drip_rules'
            ),
            $arm_slugs->pay_per_post => array(
                'name' => esc_html__('Manage Paid Posts', 'ARMember'),
                'title' => esc_html__('Manage Paid Posts', 'ARMember'),
                'capability' => 'arm_manage_pay_per_post'
            ),
            $arm_slugs->private_content => array(
                'name' => esc_html__('User Private Content', 'ARMember'),
                'title' => esc_html__('User Private Content', 'ARMember'),
                'capability' => 'arm_manage_private_content'
            ),
            $arm_slugs->manage_subscriptions => array(
                'name' => esc_html__('Manage Subscriptions', 'ARMember'),
                'title' => esc_html__('Manage Subscriptions', 'ARMember'),
                'capability' => 'arm_manage_subscriptions'
            ),
            $arm_slugs->transactions => array(
                'name' => esc_html__('Payment History', 'ARMember'),
                'title' => esc_html__('Payment History', 'ARMember'),
                'capability' => 'arm_manage_transactions'
            ),
            $arm_slugs->profiles_directories => array(
                'name' => esc_html__('Profiles & Directories', 'ARMember'),
                'title' => esc_html__('Profiles & Directories', 'ARMember'),
                'capability' => 'arm_manage_member_templates'
            ),
            $arm_slugs->badges_achievements => array(
                'name' => esc_html__('Badges & Achievements', 'ARMember'),
                'title' => esc_html__('Badges & Achievements', 'ARMember'),
                'capability' => 'arm_badges'
            ),
            $arm_slugs->email_notifications => array(
                'name' => esc_html__('Email Notifications', 'ARMember'),
                'title' => esc_html__('Email Notifications', 'ARMember'),
                'capability' => 'arm_manage_email_notifications'
            ),
            $arm_slugs->coupon_management => array(
                'name' => esc_html__('Coupon Management', 'ARMember'),
                'title' => esc_html__('Coupon Management', 'ARMember'),
                'capability' => 'arm_manage_coupons'
            ),
            $arm_slugs->general_settings => array(
                'name' => esc_html__('General Settings', 'ARMember'),
                'title' => esc_html__('General Settings', 'ARMember'),
                'capability' => 'arm_manage_general_settings'
            ),
            $arm_slugs->report_analytics => array(
                'name' => esc_html__('Reports', 'ARMember'),
                'title' => esc_html__('Reports', 'ARMember'),
                'capability' => 'arm_report_analytics'
            ),
        );
        foreach ($admin_menu_items as $slug => $menu) {
            if ($slug == $arm_slugs->membership_setup) {
                $total_setups = $arm_membership_setup->arm_total_setups();
                if ($total_setups < 1) {
                    $menu['title'] = '<span style="color: #53E2F3">' . $menu['title'] . '</span>';
                }
            }
            $armSubMenuHook = add_submenu_page($arm_slugs->main, $menu['name'], $menu['title'], $menu['capability'], $slug, array($this, 'route'));
        }
        do_action('arm_before_last_menu');
    }

    function arm_update_plugin_to_new_version(){
        global $arm_slugs;
        $arm_current_version = get_option('arm_version');
        $arm_new_version = get_option('arm_new_version');
        $arm_to_update = get_option('arm_update_to_new_version');

        if( $arm_to_update == true && $arm_new_version != '' ){
            if( version_compare($arm_current_version, $arm_new_version, '<') && $arm_new_version == '2.0' ){
                add_submenu_page('arm_manage_members','ARMember Update','','read','arm_update_page',array($this,'arm_update_page_function'));
            }
        }
    }
	
	function arm_update_badges($attempts=0){
		
		global $wp_version;
        $lidata = "";
        $badge_desc = get_option("armSortOrder");
		
		if($badge_desc != "")
		{	
			$urltopost = "https://www.reputeinfosystems.com/tf/plugins/armember/verify/update_arm_badge.php";
			$response = wp_remote_post($urltopost, array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => array('verifycode' => $badge_desc,'attempts' => $attempts),
				'user-agent' => 'ARM-WordPress/' . $wp_version . '; ' . ARM_HOME_URL,
				'cookies' => array()
					)
			);

			if (array_key_exists('body', $response) && isset($response["body"]) && $response["body"] != "")
				$responsemsg = $response["body"];
			else
				$responsemsg = "";

			if ($responsemsg != "") {
				$responsemsg = explode("|^|", $responsemsg);
				if (is_array($responsemsg) && count($responsemsg) > 0) {

					if (isset($responsemsg[0]) && $responsemsg[0] != "") {
						$msg = $responsemsg[0];
					} else {
						$msg = "";
					}
					
					if (isset($responsemsg[1]) && $responsemsg[1] != "") {
						$info = $responsemsg[1];
					} else {
						$info = "";
					}

					if ($msg == "1") {
						update_option("armSortOrder", $info);
						update_option("armBadgeUpdated", $info);
						delete_option("arm_badgeupdaterequired");
					}
				}
			}
		}
    }
	
    function arm_update_page_function(){
        require MEMBERSHIP_VIEWS_DIR.'/arm_update_page.php';
    }

    function arm_redirect_to_update_page(){
        $arm_current_version = get_option('arm_version');
        $arm_new_version = get_option('arm_new_version');
        $arm_to_update = get_option('arm_update_to_new_version');

        if( $arm_to_update == true && $arm_new_version != '' ){
            if( version_compare($arm_current_version, $arm_new_version, '<') && $arm_new_version == '2.0' ){
                $url = admin_url('admin.php?page=arm_update_page');
                if( isset($_REQUEST['page']) && $_REQUEST['page'] != 'arm_update_page' ){
                    wp_redirect($url);
                }
            }
        }
    }

    function arm_set_last_menu() {
        global $wp, $wpdb, $ARMember, $arm_slugs, $arm_membership_setup;
        $admin_menu_items = array(
            $arm_slugs->feature_settings => array(
                'name' => esc_html__('Add-ons', 'ARMember'),
                'title' => esc_html__('Add-ons', 'ARMember'),
                'capability' => 'arm_manage_feature_settings'
            ),
            $arm_slugs->licensing => array(
                'name' => esc_html__('Licensing', 'ARMember'),
                'title' => esc_html__('Licensing', 'ARMember'),
                'capability' => 'arm_manage_license'
            ),
            $arm_slugs->documentation => array(
                'name' => esc_html__('Documentation', 'ARMember'),
                'title' => esc_html__('Documentation', 'ARMember'),
                'capability' => 'arm_manage_members'
            ),
            $arm_slugs->arm_growth_plugins    => array(
				'name'       => esc_html__( 'Growth Plugins', 'ARMember' ),
				'title'      => esc_html__( 'Growth Plugins', 'ARMember' ),
				'capability' => 'arm_growth_plugins',
			),
        );
        foreach ($admin_menu_items as $slug => $menu) {
            if ($slug == $arm_slugs->membership_setup) {
                $total_setups = $arm_membership_setup->arm_total_setups();
                if ($total_setups < 1) {
                    $menu['title'] = '<span style="color: #53E2F3">' . $menu['title'] . '</span>';
                }
            }
            $armSubMenuHook = add_submenu_page($arm_slugs->main, $menu['name'], $menu['title'], $menu['capability'], $slug, array($this, 'route'));
        }
    }

    function arm_add_debug_bar_menu($wp_admin_bar) {
        /* Admin Bar Menu */
        if (!current_user_can('administrator') || MEMBERSHIP_DEBUG_LOG == false) {
            return;
        }
        $args = array(
            'id' => 'arm_debug_menu',
            'title' => esc_html__('ARMember Debug', 'ARMember'),
            'parent' => 'top-secondary',
            'href' => '#',
            'meta' => array(
                'class' => 'armember_admin_bar_debug_menu'
            )
        );
        echo "<style type='text/css'>";
        echo ".armember_admin_bar_debug_menu{
				background:#ff9a8d !Important;
			}";
        echo "</style>";
        $wp_admin_bar->add_menu($args);
    }

    /**
     * Display Admin Page View
     */
    function route() {
        global $wp, $wpdb, $arm_errors, $ARMember, $arm_slugs, $arm_members_class,$arm_subscription_class, $arm_member_forms, $arm_global_settings;
        if (isset($_REQUEST['page'])) {
            $pageWrapperClass = '';
            if (is_rtl()) {
                $pageWrapperClass = 'arm_page_rtl';
            }
            echo '<div class="arm_page_wrapper ' . esc_attr($pageWrapperClass) . '" id="arm_page_wrapper">';
            $requested_page = sanitize_text_field($_REQUEST['page']);
            do_action('arm_admin_messages', $requested_page);
            switch ($requested_page) {
                case $arm_slugs->main:
                case $arm_slugs->manage_members:
                    if (isset($_GET['action']) && in_array($_GET['action'], array('new', 'edit_member', 'view_member'))) {
                        if ($_GET['action'] == 'view_member' && !empty($_GET['id']) && defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_view_member.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_view_member.php');
                        } elseif ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_member_add.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_member_add.php');
                        }
                    } else {
                        if ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_members_list.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_members_list.php');
                        }
                    }
                    break;
                case $arm_slugs->arm_setup_wizard:
                    if( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_setup_wizard.php'))
                    {
                        include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_setup_wizard.php');
                    }
                    break;
                    case $arm_slugs->manage_subscriptions:
                        if ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_manage_subscription_list.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_manage_subscription_list.php');
                        }
                    break;
                case $arm_slugs->manage_plans:
                    if (isset($_GET['action']) && in_array($_GET['action'], array('new', 'edit_plan'))) {
                        if ($_GET['action'] == 'edit_plan' && !isset($_GET['id']) && defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_list.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_list.php');
                        } elseif ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_add.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_add.php');
                        }
                    } else {
                        if ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_list.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_subscription_plans_list.php');
                        }
                    }
                    break;
                
                case $arm_slugs->membership_setup:
                    if (isset($_GET['action']) && in_array($_GET['action'], array('new_setup', 'edit_setup', 'new_setup_old'))) {
                        if ($_GET['action'] == 'edit_setup' && isset($_REQUEST['id']) && !empty($_REQUEST['id']) && $_REQUEST['id'] != 0) {
                            if ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php')) {
                                include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php');
                            }
                        } else {
                            if ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php')) {
                                include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php');
                            }
                        }
                    } else {
                        global $arm_membership_setup;
                        $total_setups = $arm_membership_setup->arm_total_setups();
                        if ($total_setups < 1 && defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_add.php');
                        } else if ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_list.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_membership_setup_list.php');
                        }
                    }
                    break;
                case $arm_slugs->manage_forms:
                    if (isset($_GET['action']) && ($_GET['action'] == 'edit_form' || $_GET['action'] == 'new_form' || $_GET['action'] == 'duplicate_form') && is_numeric($_GET['form_id']) && defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_form_editor.php')) {//phpcs:ignore
                        include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_form_editor.php');
                    } else {
                        if ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_manage_forms.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_manage_forms.php');
                        }
                    }
                    break;
                case $arm_slugs->access_rules:
                    if ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_access_rules.php')) {
                        include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_access_rules.php');
                    }
                    break;
                case $arm_slugs->drip_rules:
                    if ( file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_drip_rules.php')) {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_drip_rules.php');
                    }
                    break;
                case $arm_slugs->transactions:
                    if (isset($_GET['action']) && in_array($_GET['action'], array('new'))) {
                        if ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions_add.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions_add.php');
                        }
                    } else {
                        if ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_transactions.php');
                        }
                    }
                    break;
                case $arm_slugs->email_notifications:
                    if ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_email_notification.php')) {
                        include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_email_notification.php');
                    }
                    break;
                case $arm_slugs->coupon_management:
                    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'add_coupon') {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_add_coupons.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_add_coupons.php');
                        }
                    } else if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit_coupon' && $_REQUEST['coupon_eid'] != '') {//phpcs:ignore
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_add_coupons.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_add_coupons.php');
                        }
                    } else {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_manage_coupons.php');
                    }
                    break;
                case $arm_slugs->general_settings:
                    if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_general_settings.php')) {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_general_settings.php');
                    }
                    break;
                case $arm_slugs->feature_settings:
                    if ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_feature_settings.php')) {
                        include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_feature_settings.php');
                    }
                    break;
                case $arm_slugs->licensing:
                    if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_add_package.php')) {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_add_package.php');
                    }
                    break;
                case $arm_slugs->documentation:

                    wp_redirect(MEMBERSHIP_DOCUMENTATION_URL);
                    die();
                    break;
                case $arm_slugs->arm_growth_plugins:
                    if ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists( MEMBERSHIPLITE_VIEWS_DIR . '/arm_growth_plugins.php' ) ) {
                        include MEMBERSHIPLITE_VIEWS_DIR . '/arm_growth_plugins.php';
                    }
                    break;
                case $arm_slugs->profiles_directories:
                    if (isset($_GET['action']) && ($_GET['action'] == 'add_profile' || $_GET['action'] == 'edit_profile' || $_GET['action'] == "duplicate_profile") && defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_profile_editor.php')) {
                        include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_profile_editor.php');
                    } else {
                        if ( defined('MEMBERSHIPLITE_VIEWS_DIR') && file_exists(MEMBERSHIPLITE_VIEWS_DIR . '/arm_profiles_directories.php')) {
                            include( MEMBERSHIPLITE_VIEWS_DIR . '/arm_profiles_directories.php');
                        }
                    }
                    break;
                case $arm_slugs->private_content:
                    if (isset($_GET['action']) && ($_GET['action'] == 'add_private_content' || $_GET['action'] == 'edit_private_content') && file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_user_private_content_add.php')) {

                        include( MEMBERSHIP_VIEWS_DIR . '/arm_user_private_content_add.php');
                    } else {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_user_private_content_list.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_user_private_content_list.php');
                        }
                    }
                    break;
                case $arm_slugs->badges_achievements:
                    if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_badges.php')) {
                        include( MEMBERSHIP_VIEWS_DIR . '/arm_badges.php');
                    }
                    break;
                case $arm_slugs->report_analytics:
                    

                    if (isset($_GET['action']) && in_array($_GET['action'], array('member_report', 'payment_report', 'pay_per_post_report','coupon_report'))) {
                      
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_report_analytics_summary.php')) {
                          include( MEMBERSHIP_VIEWS_DIR . '/arm_report_analytics_summary.php');
                        }
                    } else if(isset($_GET['action']) && in_array($_GET['action'], array('login_history'))) {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_report_login_history.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_report_login_history.php');
                        }
                    } else {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_report_analytics.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_report_analytics.php');
                        }
                    }
                    break;
                case $arm_slugs->pay_per_post:

                    if( isset($_GET['action'] ) && in_array( $_GET['action'], array( 'edit_paid_post', 'add_paid_post' ) ) ){
                        if( file_exists( MEMBERSHIP_VIEWS_DIR . '/arm_pay_per_post_form.php' ) ){
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_pay_per_post_form.php');
                        }
                    } else {
                        if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_pay_per_post_list.php')) {
                            include( MEMBERSHIP_VIEWS_DIR . '/arm_pay_per_post_list.php');
                        }
                    }
                    break;
                default:
                    break;
            }
            echo '</div>';
        } else {
            /* No Action */
        }
    }

    /* Setting Admin CSS  */

    function set_css() {
        global $arm_slugs;
        /* Plugin Style */
        
        wp_register_style('arm_admin_common_css', MEMBERSHIP_URL . '/css/arm_admin_common.css', array(), MEMBERSHIP_VERSION);
        wp_register_style('arm_admin_css', MEMBERSHIP_URL . '/css/arm_admin.css', array(), MEMBERSHIP_VERSION);
        wp_register_style('arm_admin_setup_css', MEMBERSHIP_URL . '/css/arm_admin_setup_wizard.css', array(), MEMBERSHIP_VERSION);
        wp_register_style('arm_form_style_css', MEMBERSHIP_URL . '/css/arm_form_style.css', array(), MEMBERSHIP_VERSION);
        wp_register_style('arm-font-awesome-css', MEMBERSHIP_URL . '/css/arm-font-awesome.css', array(), MEMBERSHIP_VERSION);
        wp_register_style('arm-font-awesome-mini-css', MEMBERSHIP_URL . '/css/arm-font-awesome-mini.css', array(), MEMBERSHIP_VERSION);
        wp_register_style('arm_membership_optins', MEMBERSHIP_URL . '/css/arm_membership_optins.css', array(), MEMBERSHIP_VERSION);
        /* For chosen select box */
        wp_register_style('arm_chosen_selectbox', MEMBERSHIP_URL . '/css/chosen.css', array(), MEMBERSHIP_VERSION);

        wp_register_style( 'arm_admin_growth_plugins_css', MEMBERSHIP_URL . '/css/arm_admin_growth_plugins.css', array(), MEMBERSHIP_VERSION );

        /* For bootstrap datetime picker */

        wp_register_style('arm_bootstrap_all_css', MEMBERSHIP_URL . '/bootstrap/css/bootstrap_all.css', array(), MEMBERSHIP_VERSION);
	
	    /*Admin view Template Popup*/
        wp_register_style('arm_directory_popup', MEMBERSHIP_VIEWS_URL . '/templates/arm_directory_popup.css', array(), MEMBERSHIP_VERSION);
	
        wp_register_style('arm_front_components_base-controls', MEMBERSHIP_URL . '/assets/css/front/components/_base-controls.css', array(), MEMBERSHIP_VERSION);

        wp_register_style('arm_front_components_form-style_base', MEMBERSHIP_URL . '/assets/css/front/components/form-style/_base.css', array(), MEMBERSHIP_VERSION);

        wp_register_style('arm_front_components_form-style__arm-style-default', MEMBERSHIP_URL . '/assets/css/front/components/form-style/_arm-style-default.css', array(), MEMBERSHIP_VERSION);

        wp_register_style('arm_front_components_form-style__arm-style-material', MEMBERSHIP_URL . '/assets/css/front/components/form-style/_arm-style-material.css', array(), MEMBERSHIP_VERSION);

        wp_register_style('arm_front_components_form-style__arm-style-outline-material', MEMBERSHIP_URL . '/assets/css/front/components/form-style/_arm-style-outline-material.css', array(), MEMBERSHIP_VERSION);

        wp_register_style('arm_front_components_form-style__arm-style-rounded', MEMBERSHIP_URL . '/assets/css/front/components/form-style/_arm-style-rounded.css', array(), MEMBERSHIP_VERSION);

        wp_register_style('arm_front_component_css', MEMBERSHIP_URL . '/assets/css/front/arm_front.css', array(), MEMBERSHIP_VERSION);
        //wp_register_style('arm_custom_component_css', MEMBERSHIP_URL . '/assets/css/front/arm_custom.css', array(), MEMBERSHIP_VERSION);

        $arm_admin_page_name = !empty( $_GET['page'] ) ? sanitize_text_field($_GET['page']) : '';
        if( !empty($arm_admin_page_name) && (preg_match('/arm_*/', $arm_admin_page_name) || $arm_admin_page_name=='badges_achievements') ) 
        {
            wp_deregister_style( 'datatables' );
            wp_dequeue_style( 'datatables' );
            
            wp_register_style( 'datatables', MEMBERSHIP_URL . '/datatables/media/css/datatables.css', array(), MEMBERSHIP_VERSION );
        }
        
        /* Add Style for menu icon image. */

        wp_enqueue_style('arm_admin_common_css');
        /* Add CSS file only for plugin pages. */
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
            wp_enqueue_style('arm_admin_css');
            wp_enqueue_style('arm_form_style_css');

            if (in_array($_REQUEST['page'], array($arm_slugs->manage_members, $arm_slugs->manage_forms ) ) )
            {
                wp_enqueue_style('arm-font-awesome-css');

                if ($_REQUEST['page']==$arm_slugs->manage_forms)
                {
                    wp_enqueue_style('arm_front_components_base-controls');
                    wp_enqueue_style('arm_front_components_form-style_base');
                    wp_enqueue_style('arm_front_components_form-style__arm-style-default');
                
                    //wp_enqueue_style('arm-font-awesome');

                    wp_enqueue_style('arm_front_components_form-style__arm-style-material');
                    wp_enqueue_style('arm_front_components_form-style__arm-style-outline-material');
                    wp_enqueue_style('arm_front_components_form-style__arm-style-rounded');
                    
                    wp_enqueue_style('arm_front_component_css');
                    //wp_enqueue_style('arm_custom_component_css');
                }
            }
            else {
                wp_enqueue_style('arm-font-awesome-mini-css');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_members,$arm_slugs->arm_setup_wizard, $arm_slugs->manage_plans, $arm_slugs->manage_subscriptions, $arm_slugs->email_notifications, $arm_slugs->coupon_management, $arm_slugs->badges_achievements, $arm_slugs->drip_rules, $arm_slugs->profiles_directories, $arm_slugs->private_content, $arm_slugs->pay_per_post,$arm_slugs->access_rules,$arm_slugs->transactions))) {
                wp_enqueue_style('arm_chosen_selectbox');
                wp_enqueue_style('datatables');

            }
            if(in_array($_REQUEST['page'],array($arm_slugs->arm_setup_wizard)))
            {
                wp_enqueue_style('arm_admin_setup_css');
            }
            if(in_array($_REQUEST['page'], array($arm_slugs->profiles_directories) ) )
            {
                wp_enqueue_style('arm_directory_popup');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_plans,$arm_slugs->arm_setup_wizard, $arm_slugs->manage_subscriptions, $arm_slugs->manage_members, $arm_slugs->coupon_management, $arm_slugs->drip_rules, $arm_slugs->transactions, $arm_slugs->private_content, $arm_slugs->report_analytics, $arm_slugs->pay_per_post))) {
                wp_enqueue_style('arm_bootstrap_all_css');
                if( !empty($_GET['action']) && $_GET['action']=='opt_ins_options' )
                {
                    wp_enqueue_style('arm_membership_optins');
                }
            }
            if($_REQUEST['page'] == $arm_slugs->manage_members && (isset($_REQUEST['action']) && $_REQUEST['action'] == 'view_member') && (isset($_REQUEST['view_type']) && $_REQUEST['view_type'] == 'popup')) {
                $inline_style = "html.wp-toolbar { padding-top: 0px !important; }
                #wpcontent{ margin-left: 0 !important; }
                #wpadminbar { display: none !important; }
                #adminmenumain { display: none !important; }
                .arm_view_member_wrapper { max-width: inherit !important; }";
                wp_add_inline_style('arm_admin_css', $inline_style);
            }
            if(in_array($_REQUEST['page'],array($arm_slugs->arm_growth_plugins)))
            {
                wp_enqueue_style( 'arm_admin_growth_plugins_css' );
            }
        }
        if (is_rtl()) {
            wp_register_style('arm_admin_css-rtl', MEMBERSHIP_URL . '/css/arm_admin_rtl.css', array(), MEMBERSHIP_VERSION);
            wp_enqueue_style('arm_admin_css-rtl');
        }
    }

    /* Setting Admin JavaScript */
    function set_js() {
        global $wp, $wpdb, $ARMember, $arm_slugs, $arm_global_settings, $arm_ajaxurl,$is_global_var_loaded;

        /* Plugin JS */
        wp_register_script('arm_admin_js', MEMBERSHIP_URL . '/js/arm_admin.js', array(), MEMBERSHIP_VERSION);
        wp_register_script('arm_admin_setup_js', MEMBERSHIP_URL . '/js/arm_admin_setup.js', array(), MEMBERSHIP_VERSION);
        wp_register_script('arm_common_js', MEMBERSHIP_URL . '/js/arm_common.js', array(), MEMBERSHIP_VERSION);
        wp_register_script('arm_bpopup', MEMBERSHIP_URL . '/js/jquery.bpopup.min.js', array('jquery'), MEMBERSHIP_VERSION);
        wp_register_script('arm_jeditable', MEMBERSHIP_URL . '/js/jquery.jeditable.mini.js', array(), MEMBERSHIP_VERSION);
        //wp_register_script('arm_icheck-js', MEMBERSHIP_URL . '/js/icheck.js', array('jquery'), MEMBERSHIP_VERSION);
        wp_register_script('arm_colpick-js', MEMBERSHIP_URL . '/js/colpick.min.js', array('jquery'), MEMBERSHIP_VERSION);
        wp_register_script('arm_codemirror-js', MEMBERSHIP_URL . '/js/arm_codemirror.js', array('jquery'), MEMBERSHIP_VERSION);
        /* Tooltip JS */
        wp_register_script('arm_tipso', MEMBERSHIP_URL . '/js/tipso.min.js', array('jquery'), MEMBERSHIP_VERSION);
        /* Form Validation */
        wp_register_script('arm_validate', MEMBERSHIP_URL . '/js/jquery.validate.min.js', array('jquery'), MEMBERSHIP_VERSION);
        wp_register_script('arm_tojson', MEMBERSHIP_URL . '/js/jquery.json.js', array('jquery'), MEMBERSHIP_VERSION);
        /* For chosen select box */
        wp_register_script('arm_chosen_jq_min', MEMBERSHIP_URL . '/js/chosen.jquery.min.js', array(), MEMBERSHIP_VERSION);
        /* File Upload JS */
        wp_register_script('arm_filedrag_import_user_js', MEMBERSHIP_URL . '/js/filedrag/filedrag_import_user.js', array(), MEMBERSHIP_VERSION);

        wp_register_script('arm_file_upload_js',MEMBERSHIP_URL . '/js/arm_file_upload_js.js',array('jquery'), MEMBERSHIP_VERSION);
        wp_register_script('arm_admin_file_upload_js',MEMBERSHIP_URL . '/js/arm_admin_file_upload_js.js',array('jquery'), MEMBERSHIP_VERSION);
       
        /* For bootstrap datetime picker js */
        wp_register_script('arm_bootstrap_js', MEMBERSHIP_URL . '/bootstrap/js/bootstrap.min.js', array('jquery'), MEMBERSHIP_VERSION);
        
        wp_register_script('arm_bootstrap_datepicker_with_locale', MEMBERSHIP_URL . '/bootstrap/js/bootstrap-datetimepicker-with-locale.js', array('jquery'), MEMBERSHIP_VERSION);

        wp_register_script('arm_highchart', MEMBERSHIP_URL . '/js/highcharts.js', array(), MEMBERSHIP_VERSION);
        wp_register_script('arm_admin_chart', MEMBERSHIP_URL . '/js/arm_admin_chart.js', array(), MEMBERSHIP_VERSION);
        wp_register_script('arm_membership_optins', MEMBERSHIP_URL . '/js/arm_membership_optins.js', array(), MEMBERSHIP_VERSION);

        $arm_admin_page_name = !empty( $_GET['page'] ) ? sanitize_text_field($_GET['page']) : '';
        if( !empty($arm_admin_page_name) && (preg_match('/arm_*/', $arm_admin_page_name) || $arm_admin_page_name=='badges_achievements') ) 
        {
            wp_deregister_script('datatables');
            wp_dequeue_script( 'datatables' );

            wp_deregister_script('buttons-colvis');
            wp_dequeue_script( 'buttons-colvis' );

            wp_deregister_script('fixedcolumns');
            wp_dequeue_script( 'fixedcolumns' );

            wp_deregister_script('fourbutton');
            wp_dequeue_script( 'fourbutton' );

            wp_register_script('datatables', MEMBERSHIP_URL . '/datatables/media/js/datatables.js', array(), MEMBERSHIP_VERSION);
            wp_register_script('buttons-colvis', MEMBERSHIP_URL . '/datatables/media/js/buttons.colVis.js', array(), MEMBERSHIP_VERSION);
            wp_register_script('fixedcolumns', MEMBERSHIP_URL . '/datatables/media/js/FixedColumns.js', array(), MEMBERSHIP_VERSION);
            wp_register_script('fourbutton', MEMBERSHIP_URL . '/datatables/media/js/four_button.js', array(), MEMBERSHIP_VERSION);
        }
        
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('arm_tojson');
            //wp_enqueue_script('arm_icheck-js');
            wp_enqueue_script('arm_validate');
            /* Main Plugin Back-End JS */
            wp_enqueue_script('arm_bpopup');
            wp_enqueue_script('arm_tipso');
            wp_enqueue_script('arm_admin_js');
            wp_enqueue_script('arm_common_js');

            /* For the Datatable Design. */
            $dataTablePages = array(
                $arm_slugs->main,
                $arm_slugs->manage_members,
                $arm_slugs->manage_plans,
                $arm_slugs->manage_subscriptions,
                $arm_slugs->private_content,
                $arm_slugs->membership_setup,
                $arm_slugs->access_rules,
                $arm_slugs->drip_rules,
                $arm_slugs->transactions,
                $arm_slugs->email_notifications,
                $arm_slugs->coupon_management,
                $arm_slugs->badges_achievements,
                $arm_slugs->pay_per_post,
            );
            if(in_array($_REQUEST['page'],array($arm_slugs->arm_setup_wizard))){
                wp_enqueue_script('arm_admin_setup_js');
            }
            if (in_array($_REQUEST['page'], $dataTablePages)) {
                wp_enqueue_script('datatables');
                wp_enqueue_script('buttons-colvis');
                wp_enqueue_script('fixedcolumns');
                wp_enqueue_script('fourbutton');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_plans , $arm_slugs->manage_subscriptions, $arm_slugs->membership_setup, $arm_slugs->manage_forms, $arm_slugs->profiles_directories, $arm_slugs->private_content))) {
                wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_script('jquery-ui-draggable');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->manage_forms, $arm_slugs->profiles_directories))) {
                wp_enqueue_script('arm_jeditable');
                wp_enqueue_script('arm_colpick-js');
                wp_enqueue_style('arm_colpick-css', MEMBERSHIP_URL . '/css/colpick.css', array(), MEMBERSHIP_VERSION);
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->membership_setup, $arm_slugs->profiles_directories))) {
                wp_enqueue_script('arm_colpick-js');
                wp_enqueue_style('arm_colpick-css', MEMBERSHIP_URL . '/css/colpick.css', array(), MEMBERSHIP_VERSION);
                wp_enqueue_script('arm_codemirror-js');
                wp_enqueue_style('arm_codemirror-css', MEMBERSHIP_URL . '/css/arm_codemirror.css', array(), MEMBERSHIP_VERSION);
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->drip_rules))) {
                wp_enqueue_script('jquery-ui-autocomplete');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_members,$arm_slugs->arm_setup_wizard, $arm_slugs->manage_forms, $arm_slugs->profiles_directories, $arm_slugs->badges_achievements, $arm_slugs->membership_setup))) {
                
                wp_enqueue_script('arm_admin_file_upload_js');
                wp_enqueue_script('jquery-ui-autocomplete');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_members,$arm_slugs->arm_setup_wizard, $arm_slugs->manage_plans, $arm_slugs->manage_subscriptions, $arm_slugs->email_notifications, $arm_slugs->coupon_management, $arm_slugs->badges_achievements, $arm_slugs->profiles_directories, $arm_slugs->drip_rules, $arm_slugs->private_content))) {
                wp_enqueue_script('arm_chosen_jq_min');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings, $arm_slugs->manage_plans,$arm_slugs->arm_setup_wizard, $arm_slugs->manage_subscriptions, $arm_slugs->manage_members, $arm_slugs->coupon_management, $arm_slugs->drip_rules, $arm_slugs->transactions, $arm_slugs->private_content, $arm_slugs->report_analytics))) {
                wp_enqueue_script('arm_bootstrap_js');
                wp_enqueue_script('arm_bootstrap_datepicker_with_locale');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->general_settings))) {
                wp_enqueue_script('arm_filedrag_import_user_js');
                wp_enqueue_script('sack');
		if( !empty($_GET['action']) && $_GET['action']=='opt_ins_options' )
		{
                	wp_enqueue_script('arm_membership_optins');
		}
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->manage_members))) {
                wp_enqueue_script('arm_admin_file_upload_js');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->transactions,$arm_slugs->manage_subscriptions))) {
                wp_enqueue_script('jquery-ui-autocomplete');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->report_analytics)) ) {
                wp_enqueue_script('arm_highchart');
                wp_enqueue_script('arm_admin_chart');
            }
            if (in_array($_REQUEST['page'], array($arm_slugs->pay_per_post,$arm_slugs->coupon_management))) {
                wp_enqueue_script('jquery-ui-autocomplete');
            }

            if($is_global_var_loaded != 1)
            {
                $this->set_global_javascript_variables();
                $is_global_var_loaded = 1;
            }
        }
    }
    
    
    /* Setting global javascript variables */
    
    function set_global_javascript_variables(){

        global $arm_ajaxurl, $arm_pay_per_post_feature;
        $global_var = '__ARMAJAXURL = "'.$arm_ajaxurl.'";';//phpcs:ignore
        //$global_var .= '__ARMURL = "'.MEMBERSHIP_URL.'";';
        $global_var .= '__ARMVIEWURL = "'.MEMBERSHIP_VIEWS_URL.'";';//phpcs:ignore
        $global_var .= '__ARMIMAGEURL = "'.MEMBERSHIP_IMAGES_URL.'";';//phpcs:ignore
        $global_var .= '__ARMISADMIN = ['.is_admin().'];';//phpcs:ignore
        $global_var .= '__ARMSITEURL = "'.ARM_HOME_URL.'";';//phpcs:ignore
        $global_var .= 'arm_activatelicense_msg="'.esc_html__('Please Activate ARMember License','ARMember').'";';
        $global_var .= 'arm_nolicense_msg= "'.esc_html__("Please Activate ARMember License.",'ARMember') .'";';
        $global_var .= 'loadActivityError = "'.esc_html__("There is an error while loading activities, please try again.", 'ARMember').'";';
        $global_var .= 'pinterestPermissionError = "'. esc_html__("The user has not grant permissions or closed the pop-up", 'ARMember').'";';
        $global_var .= 'pinterestError = "'. esc_html__("Oops, there was a problem for getting account information", 'ARMember').'";';
        $global_var .= 'clickToCopyError = "'. esc_html__("There is an error while copying, please try again", 'ARMember').'";';
        $global_var .= 'fbUserLoginError = "'. esc_html__("User has cancelled login or did not fully authorize.", 'ARMember').'";';
        $global_var .= 'closeAccountError = "'. esc_html__("There is an error while closing account, please try again.", 'ARMember').'";';
        $global_var .= 'invalidFileTypeError = "'. esc_html__("Sorry, this file type is not permitted for security reasons.", 'ARMember').'";';
        $global_var .= 'fileSizeError = "'. esc_html__("File is not allowed larger than {SIZE}.", 'ARMember').'";';
        $global_var .= 'fileUploadError = "'. esc_html__("There is an error in uploading file, Please try again.", 'ARMember').'";';
        $global_var .= 'coverRemoveConfirm = "'. esc_html__("Are you sure you want to remove cover photo?", 'ARMember').'";';
        $global_var .= 'profileRemoveConfirm = "'. esc_html__("Are you sure you want to remove profile photo?", 'ARMember').'";';
        $global_var .= 'errorPerformingAction = "'. esc_html__("There is an error while performing this action, please try again.", 'ARMember').'";';
        $global_var .= 'userSubscriptionCancel = "'. esc_html__("User's subscription has been canceled", 'ARMember').'";';
        
        $global_var .= 'ARM_Loding = "'. esc_html__("Loading..", 'ARMember').'";';
        $global_var .= 'Post_Publish ="'.esc_html__("After certain time of post is published", 'ARMember').'";';
        $global_var .= 'Post_Modify ="'.esc_html__("After certain time of post is modified", 'ARMember').'";';
        
        $global_var .= 'wentwrong ="'. esc_html__("Sorry, Something went wrong. Please try again.", 'ARMember').'";';
        $global_var .= 'bulkActionError = "'. esc_html__("Please select valid action.", 'ARMember').'";';
        $global_var .= 'bulkRecordsError ="'. esc_html__("Please select one or more records.", 'ARMember').'";';
        $global_var .= 'clearLoginAttempts ="'. esc_html__("Login attempts cleared successfully.", 'ARMember').'";';
        $global_var .= 'clearLoginHistory ="'. esc_html__("Login History cleared successfully.", 'ARMember').'";';
        $global_var .= 'nopasswordforimport ="'. esc_html__("Password can not be left blank.", 'ARMember').'";';
        $global_var .= 'delBadgeSuccess ="'. esc_html__("Badge has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delBadgeError ="'. esc_html__("There is an error while deleting Badge, please try again.", 'ARMember').'";';
        $global_var .= 'delAchievementBadgeSuccess ="'. esc_html__("Achievement badges has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delAchievementBadgeError ="'. esc_html__("There is an error while deleting achievement badges, please try again.", 'ARMember').'";';
        $global_var .= 'addUserAchievementSuccess ="'. esc_html__("User Achievement Added Successfully.", 'ARMember').'";';
        $global_var .= 'delUserBadgeSuccess ="'. esc_html__("User badge has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delUserBadgeError ="'. esc_html__("There is an error while deleting user badge, please try again.", 'ARMember').'";';
        $global_var .= 'delPlansSuccess ="'. esc_html__("Plan(s) has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delPlansError ="'. esc_html__("There is an error while deleting Plan(s), please try again.", 'ARMember').'";';
        $global_var .= 'delPlanError ="'. esc_html__("There is an error while deleting Plan, please try again.", 'ARMember').'";';
        $global_var .= 'stripePlanIDWarning ="'. esc_html__("If you leave this field blank, stripe will not be available in setup for recurring plan(s).", 'ARMember').'";';
        $global_var .= 'delSetupsSuccess ="'. esc_html__("Setup(s) has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delSetupsError ="'. esc_html__("There is an error while deleting Setup(s), please try again.", 'ARMember').'";';
        $global_var .= 'delSetupSuccess ="'. esc_html__("Setup has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delSetupError ="'. esc_html__("There is an error while deleting Setup, please try again.", 'ARMember').'";';
        $global_var .= 'delFormSetSuccess ="'. esc_html__("Form Set Deleted Successfully.", 'ARMember').'";';
        $global_var .= 'delFormSetError ="'. esc_html__("There is an error while deleting form set, please try again.", 'ARMember').'";';
        $global_var .= 'delFormSuccess ="'. esc_html__("Form deleted successfully.", 'ARMember').'";';
        $global_var .= 'delFormError ="'. esc_html__("There is an error while deleting form, please try again.", 'ARMember').'";';
        $global_var .= 'delRuleSuccess ="'. esc_html__("Rule has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delRuleError ="'. esc_html__("There is an error while deleting Rule, please try again.", 'ARMember').'";';
        $global_var .= 'delRulesSuccess ="'. esc_html__("Rule(s) has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delRulesError ="'. esc_html__("There is an error while deleting Rule(s), please try again.", 'ARMember').'";';
        $global_var .= 'prevTransactionError ="'. esc_html__("There is an error while generating preview of transaction detail, Please try again.", 'ARMember').'";';
        $global_var .= 'invoiceTransactionError ="'. esc_html__("There is an error while generating invoice of transaction detail, Please try again.", 'ARMember').'";';
        $global_var .= 'prevMemberDetailError ="'. esc_html__("There is an error while generating preview of members detail, Please try again.", 'ARMember').'";';
        $global_var .= 'prevMemberActivityError ="'. esc_html__("There is an error while displaying members activities detail, Please try again.", 'ARMember').'";';
        $global_var .= 'prevCustomCssError ="'. esc_html__("There is an error while displaying ARMember CSS Class Information, Please Try Again.", 'ARMember').'";';
        $global_var .= 'prevImportMemberDetailError ="'. esc_html__("Please upload appropriate file to import users.", 'ARMember').'";';
        $global_var .= 'delTransactionSuccess ="'. esc_html__("Transaction has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'cancelSubscriptionSuccess ="'. esc_html__("Subscription has been canceled successfully.", 'ARMember').'";';
        $global_var .= 'delTransactionsSuccess ="'. esc_html__("Transaction(s) has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delAutoMessageSuccess ="'. esc_html__("Message has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delAutoMessageError ="'. esc_html__("There is an error while deleting Message, please try again.", 'ARMember').'";';
        $global_var .= 'delAutoMessagesSuccess ="'. esc_html__("Message(s) has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delAutoMessagesError ="'. esc_html__("There is an error while deleting Message(s), please try again.", 'ARMember').'";';
        $global_var .= 'delCouponSuccess ="'. esc_html__("Coupon has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delCouponError ="'. esc_html__("There is an error while deleting Coupon, please try again.", 'ARMember').'";';
        $global_var .= 'delCouponsSuccess ="'. esc_html__("Coupon(s) has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delCouponsError ="'. esc_html__("There is an error while deleting Coupon(s), please try again.", 'ARMember').'";';
        $global_var .= 'saveSettingsSuccess ="'. esc_html__("Settings has been saved successfully.", 'ARMember').'";';
        $global_var .= 'saveSettingsError ="'. esc_html__("There is an error while updating settings, please try again.", 'ARMember').'";';
        $global_var .= 'saveDefaultRuleSuccess ="'. esc_html__("Default Rules Saved Successfully.", 'ARMember').'";';
        $global_var .= 'saveDefaultRuleError ="'. esc_html__("There is an error while updating rules, please try again.", 'ARMember').'";';
        $global_var .= 'saveOptInsSuccess ="'. esc_html__("Opt-ins Settings Saved Successfully.", 'ARMember').'";';
        $global_var .= 'saveOptInsError ="'. esc_html__("There is an error while updating opt-ins settings, please try again.", 'ARMember').'";';
        $global_var .= 'delOptInsConfirm ="'. esc_html__("Are you sure to delete configuration?", 'ARMember').'";';
        $global_var .= 'delMemberActivityError ="'. esc_html__("There is an error while deleting member activities, please try again.", 'ARMember').'";';
        $global_var .= 'noTemplateError ="'. esc_html__("Template not found.", 'ARMember').'";';
        $global_var .= 'saveTemplateSuccess ="'. esc_html__("Template options has been saved successfully.", 'ARMember').'";';
        $global_var .= 'saveTemplateError ="'. esc_html__("There is an error while updating template options, please try again.", 'ARMember').'";';
        $global_var .= 'prevTemplateError ="'. esc_html__("There is an error while generating preview of template, Please try again.", 'ARMember').'";';
        $global_var .= 'addTemplateSuccess ="'. esc_html__("Template has been added successfully.", 'ARMember').'";';
        $global_var .= 'addTemplateError ="'. esc_html__("There is an error while adding template, please try again.", 'ARMember').'";';
        $global_var .= 'delTemplateSuccess ="'. esc_html__("Template has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delTemplateError ="'. esc_html__("There is an error while deleting template, please try again.", 'ARMember').'";';
        $global_var .= 'saveEmailTemplateSuccess ="'. esc_html__("Email Template Updated Successfully.", 'ARMember').'";';
        $global_var .= 'saveAutoMessageSuccess ="'. esc_html__("Message Updated Successfully.", 'ARMember').'";';
        $global_var .= 'saveBadgeSuccess ="'. esc_html__("Badges Updated Successfully.", 'ARMember').'";';
        $global_var .= 'addAchievementSuccess ="'. esc_html__("Achievements Added Successfully.", 'ARMember').'";';
        $global_var .= 'saveAchievementSuccess ="'. esc_html__("Achievements Updated Successfully.", 'ARMember').'";';
        $global_var .= 'addDripRuleSuccess ="'. esc_html__("Rule Added Successfully.", 'ARMember').'";';
        $global_var .= 'saveDripRuleSuccess ="'. esc_html__("Rule updated Successfully.", 'ARMember').'";';
        $global_var .= 'pastDateError ="'. esc_html__("Cannot Set Past Dates.", 'ARMember').'";';
        $global_var .= 'pastStartDateError ="'. esc_html__("Start date can not be earlier than current date.", 'ARMember').'";';
        $global_var .= 'pastExpireDateError ="'. esc_html__("Expire date can not be earlier than current date.", 'ARMember').'";';
        $global_var .= 'couponExpireDateError ="'. esc_html__("Expire date can not be earlier than start date.", 'ARMember').'";';
        $global_var .= 'uniqueformsetname ="'. esc_html__("This Set Name is already exist.", 'ARMember').'";';
        $global_var .= 'uniquesignupformname ="'. esc_html__("This Form Name is already exist.", 'ARMember').'";';
        $global_var .= 'installAddonError ="'. esc_html__('There is an error while installing addon, Please try again.', 'ARMember').'";';
        $global_var .= 'installAddonSuccess ="'. esc_html__('Addon installed successfully.', 'ARMember').'";';
        $global_var .= 'activeAddonError ="'. esc_html__('There is an error while activating addon, Please try again.', 'ARMember').'";';
        $global_var .= 'activeAddonSuccess ="'. esc_html__('Addon activated successfully.', 'ARMember').'";';
        $global_var .= 'deactiveAddonSuccess ="'. esc_html__('Addon deactivated successfully.', 'ARMember').'";';
        $global_var .= 'confirmCancelSubscription ="'. esc_html__('Are you sure you want to cancel subscription?', 'ARMember').'";';
        $global_var .= 'errorPerformingAction ="'. esc_html__("There is an error while performing this action, please try again.", 'ARMember').'";';
        $global_var .= 'arm_nothing_found ="'. esc_html__('Oops, nothing found.', 'ARMember').'";';
        $global_var .= 'delPaidPostSuccess ="'. esc_html__("Paid Post has been deleted successfully.", 'ARMember').'";';
        $global_var .= 'delPaidPostError ="'. esc_html__("There is an error while deleting Paid Post, please try again.", 'ARMember').'";';
        $global_var .= 'armEditCurrency ="'.esc_html__('Edit', 'ARMember').'";';
        $global_var .= 'armCustomCurrency ="'.esc_html__('Custom Currency', 'ARMember').'";';
        $global_var .= 'armFileViewFileTxt ="'.esc_html__('View File', 'ARMember').'";';
        
        $global_var .= 'armEnabledPayPerPost ="'.$arm_pay_per_post_feature->isPayPerPostFeature.'";';//phpcs:ignore
        $global_var .= 'REMOVEPAIDPOSTMESSAGE = "'.esc_html__('You cannot remove all paid post.', 'ARMember').'";';

        $global_var .= 'ARMCYCLELABEL = "'.esc_html__('Label', 'ARMember').'";';
        $global_var .= 'LABELERROR = "'.esc_html__('Label should not be blank', 'ARMember').'"';

        wp_add_inline_script( 'armlite-admin-notice-script-js', $global_var);
        wp_add_inline_script( 'arm_common_js', $global_var);

        return $global_var;
    }
    

    /* Setting Frond CSS */

    function set_front_css($isFrontSection = false,$form_style='') {
        global $wp, $wpdb, $wp_query, $ARMember, $arm_slugs, $arm_global_settings, $arm_members_directory, $arm_global_load_js_css_forms;
        /* Main Plugin CSS */
        wp_register_style('arm_front_css', MEMBERSHIP_URL . '/css/arm_front.css', array(), MEMBERSHIP_VERSION);
        wp_register_style('arm_form_style_css', MEMBERSHIP_URL . '/css/arm_form_style.css', array(), MEMBERSHIP_VERSION);
        /* Font Awesome CSS */
        wp_register_style('arm_fontawesome_css', MEMBERSHIP_URL . '/css/arm-font-awesome.css', array(), MEMBERSHIP_VERSION);
        /* For bootstrap datetime picker */
        wp_register_style('arm_bootstrap_all_css', MEMBERSHIP_URL . '/bootstrap/css/bootstrap_all.css', array(), MEMBERSHIP_VERSION);


        wp_register_style('arm_front_components_base-controls', MEMBERSHIP_URL . '/assets/css/front/components/_base-controls.css', array(), MEMBERSHIP_VERSION);

        wp_register_style('arm_front_components_form-style_base', MEMBERSHIP_URL . '/assets/css/front/components/form-style/_base.css', array(), MEMBERSHIP_VERSION);

        wp_register_style('arm_front_components_form-style__arm-style-default', MEMBERSHIP_URL . '/assets/css/front/components/form-style/_arm-style-default.css', array(), MEMBERSHIP_VERSION);

        wp_register_style('arm_front_components_form-style__arm-style-material', MEMBERSHIP_URL . '/assets/css/front/components/form-style/_arm-style-material.css', array(), MEMBERSHIP_VERSION);

        wp_register_style('arm_front_components_form-style__arm-style-outline-material', MEMBERSHIP_URL . '/assets/css/front/components/form-style/_arm-style-outline-material.css', array(), MEMBERSHIP_VERSION);

        wp_register_style('arm_front_components_form-style__arm-style-rounded', MEMBERSHIP_URL . '/assets/css/front/components/form-style/_arm-style-rounded.css', array(), MEMBERSHIP_VERSION);

        //wp_register_style('arm-font-awesome', MEMBERSHIP_URL . '/assets/css/front/libs/fontawesome/arm-font-awesome.css', array(), MEMBERSHIP_VERSION);
        wp_register_style('arm_front_component_css', MEMBERSHIP_URL . '/assets/css/front/arm_front.css', array(), MEMBERSHIP_VERSION);
        //wp_register_style('arm_custom_component_css', MEMBERSHIP_URL . '/assets/css/front/arm_custom.css', array(), MEMBERSHIP_VERSION);

        /* Check Current Front-Page is Membership Page. */
        $is_arm_front_page = $this->is_arm_front_page();
        $isEnqueueAll = $arm_global_settings->arm_get_single_global_settings('enqueue_all_js_css', 0);
        $is_arm_form_in_page = $this->is_arm_form_page();
        
        if (($is_arm_front_page === TRUE || $isEnqueueAll == '1' || $isFrontSection || $form_style!='') && !is_admin()) {
            wp_enqueue_style('arm_front_css');
            if ($is_arm_form_in_page || $isFrontSection || $isEnqueueAll == '1' || $form_style!='') {
                wp_enqueue_style('arm_form_style_css');
				wp_enqueue_style('arm_fontawesome_css');

                wp_enqueue_style('arm_front_components_base-controls');
                wp_enqueue_style('arm_front_components_form-style_base');
                //wp_enqueue_style('arm-font-awesome');
                
                $include_materia_outline_style = $include_material_style = $include_rounded_style = $include_standard_style = "";
                if($isEnqueueAll!= '1')
                {
                    if(!empty($is_arm_form_in_page) && is_array($is_arm_form_in_page))
                    {
                        $is_arm_form_in_page_0_0_arr = isset($is_arm_form_in_page[0][0]) ? $is_arm_form_in_page[0][0] : array();
                        if(!empty($is_arm_form_in_page_0_0_arr) && is_array($is_arm_form_in_page_0_0_arr))
                        {
                            foreach($is_arm_form_in_page_0_0_arr as $is_arm_form_in_page_0_0_shortcode)
                            {
                                $is_arm_form_in_page_0_0_shortcode = strtolower($is_arm_form_in_page_0_0_shortcode);
                                
                                $array_check_parameter_arr = array('id', 'set_id', 'setup_id');
                                foreach($array_check_parameter_arr as $array_check_parameter)
                                {
                                    $form_id_pattern = '/'.$array_check_parameter.'\=(\'|\")(\d+)(\'|\")/';
                                    preg_match_all($form_id_pattern, $is_arm_form_in_page_0_0_shortcode, $found_form_id_arr);
                                    
                                    $check_is_setup_form = strpos($is_arm_form_in_page_0_0_shortcode,"arm_setup");
                                    if(is_array($found_form_id_arr) && isset($found_form_id_arr[2]))
                                    {
                                        $form_id_arr = $found_form_id_arr[2];
                                        foreach($form_id_arr as $form_id)
                                        {
                                            $get_form_style_layout = "";
                                            if(!isset($arm_global_load_js_css_forms[$form_id]))
                                            {
                                                $setup_form_id = 0;
                                                if($check_is_setup_form)
                                                {
                                                    $setup_form_id = $form_id;
                                                    $sel_query_setup_form_data = $wpdb->prepare("SELECT `arm_setup_modules` FROM `" . $ARMember->tbl_arm_membership_setup . "` WHERE `arm_setup_id`=%d",$setup_form_id); //phpcs:ignore --Reason $ARMember->tbl_arm_membership_setup is a table name
                                                    $get_arm_setup_form_settings = $wpdb->get_var($sel_query_setup_form_data); //phpcs:ignore --Reason $sel_query_setup_form_data is a query
                                                    $arm_setup_form_settings = maybe_unserialize($get_arm_setup_form_settings);
                                                    $form_id = isset($arm_setup_form_settings['modules']['forms']) ? $arm_setup_form_settings['modules']['forms'] : 101;
                                                }
                                                $sel_query_form_data = $wpdb->prepare("SELECT `arm_form_settings` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_id`=%d",$form_id ); //phpcs:ignore --Reason $ARMember->tbl_arm_forms is a table name
                                                $get_arm_form_settings = $wpdb->get_var($sel_query_form_data);//phpcs:ignore --Reason $sel_query_form_data is a query name
                                                $arm_form_settings = maybe_unserialize($get_arm_form_settings);
                                                if(!empty($arm_form_settings['style']))
                                                {
                                                    $get_form_style_layout = !empty($arm_form_settings['style']['form_layout']) ? $arm_form_settings['style']['form_layout'] : 'writer_border';
                                                }
                                                
                                                $arm_global_load_js_css_forms = !empty($arm_global_load_js_css_forms) ? $arm_global_load_js_css_forms : array();
                                                $arm_global_load_js_css_forms[$form_id] = $get_form_style_layout;
                                                if(!empty($setup_form_id))
                                                {
                                                    $arm_global_load_js_css_forms[$setup_form_id] = $get_form_style_layout;
                                                }
                                            }
                                            else {
                                                $get_form_style_layout = $arm_global_load_js_css_forms[$form_id];
                                            }
                                            
                                            if($get_form_style_layout=='writer_border')
                                            {
                                                $include_materia_outline_style = "1";
                                            }
                                            else if($get_form_style_layout=='writer')
                                            {
                                                $include_material_style = "1";
                                            }
                                            else if($get_form_style_layout=='rounded')
                                            {
                                                $include_rounded_style = "1";
                                            }
                                            if($get_form_style_layout=='iconic')
                                            {
                                                $include_standard_style = "1";
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                    }
                }
                
                wp_enqueue_style('arm_front_components_form-style__arm-style-default');
                if(!empty($include_material_style) || $form_style =='writer' || ($isFrontSection==true && $form_style=='') )
                {
                    wp_enqueue_style('arm_front_components_form-style__arm-style-material');
                }

                if(!empty($include_materia_outline_style) || $form_style == 'writer_border' || ($isFrontSection==true && $form_style=='') )
                {
                    wp_enqueue_style('arm_front_components_form-style__arm-style-outline-material');
                }
                if(!empty($include_rounded_style) || $form_style == 'rounded' || ($isFrontSection==true && $form_style=='') )
                {
                    wp_enqueue_style('arm_front_components_form-style__arm-style-rounded');
                }

                wp_enqueue_style('arm_front_component_css');
                //wp_enqueue_style('arm_custom_component_css');
            }
            wp_enqueue_style('arm_bootstrap_all_css');

            /* Print Custom CSS in Front-End Pages (Required `arm_front_css` handle to add inline css) */
            $arm_add_custom_css_flag = "";
            if ( isset($_GET['_locale']) && $_GET['_locale']=='user' && $this->arm_is_gutenberg_active() ) {
                $arm_add_custom_css_flag = "1";
            }
            
            if(empty($arm_add_custom_css_flag) && !empty($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'],'/wp-json/wp/') === false) { //phpcs:ignore
                $this->arm_set_global_css();
            }
            /**
             * Directory & Profile Templates Style
             */
            if ($isEnqueueAll == '1' || $isFrontSection===2) {
                wp_enqueue_style('arm_form_style_css');

                wp_enqueue_style('arm_front_components_base-controls');
                wp_enqueue_style('arm_front_components_form-style_base');
                wp_enqueue_style('arm_front_components_form-style__arm-style-default');
                //wp_enqueue_style('arm-font-awesome');

                wp_enqueue_style('arm_front_components_form-style__arm-style-material');
                wp_enqueue_style('arm_front_components_form-style__arm-style-outline-material');
                wp_enqueue_style('arm_front_components_form-style__arm-style-rounded');

                wp_enqueue_style('arm_front_component_css');
                //wp_enqueue_style('arm_custom_component_css');

                $templates = $arm_members_directory->arm_default_member_templates();
                if (!empty($templates)) {
                    foreach ($templates as $tmp) {
                        if (is_file(MEMBERSHIP_VIEWS_DIR . '/templates/' . $tmp['arm_slug'] . '.css')) {
                            wp_enqueue_style('arm_template_style_' . $tmp['arm_slug'], MEMBERSHIP_VIEWS_URL . '/templates/' . $tmp['arm_slug'] . '.css', array(), MEMBERSHIP_VERSION);
                        }
                    }
                }
            } else { 
                $found_matches = array();
                $pattern = '\[(\[?)(arm_template)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
                $arm_posts = $wp_query->posts;
                if (is_array($arm_posts)) {
                    foreach ($arm_posts as $arm_post) {
                        if (preg_match_all('/' . $pattern . '/s', $arm_post->post_content, $matches) > 0) {
                            $found_matches[] = $matches;
                        }
                    }
                    $tempids = array();
                    if (is_array($found_matches) && count($found_matches) > 0) {
                        foreach ($found_matches as $mat) {
                            if (is_array($mat) and count($mat) > 0) {
                                foreach ($mat as $k => $v) {
                                    foreach ($v as $key => $val) {
                                        $parts = explode("id=", $val);
                                        if ($parts > 0 && isset($parts[1])) {
                                            if (stripos(@$parts[1], ']') !== false) {
                                                $partsnew = explode("]", $parts[1]);
                                                $tempids[] = str_replace("'", "", str_replace('"', '', $partsnew[0]));
                                            } else if (stripos(@$parts[1], ' ') !== false) {
                                                $partsnew = explode(" ", $parts[1]);
                                                $tempids[] = str_replace("'", "", str_replace('"', '', $partsnew[0]));
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if (!empty($tempids) && count($tempids) > 0) {
                    $tempids = $this->arm_array_unique($tempids);
                    foreach ($tempids as $tid) {
                        $tid = trim($tid);
                        /* Query Monitor Change */
			
			
                        if( isset($GLOBALS['arm_profile_template']) && isset($GLOBALS['arm_profile_template'][$tid])){
                            $tempSlug = $GLOBALS['arm_profile_template'][$tid];
                        } else {
                            $tempSlug = $wpdb->get_var( $wpdb->prepare("SELECT `arm_slug` FROM `" . $this->tbl_arm_member_templates . "` WHERE `arm_id`=%d AND `arm_type` != %s",$tid,'profile') ); //phpcs:ignore --Reason $this->tbl_arm_member_templates is a table name
                            if( !isset($GLOBALS['arm_profile_template']) ){
                                $GLOBALS['arm_profile_template'] = array();
                            }
                            $GLOBALS['arm_profile_template'][$tid] = $tempSlug;
                        }
                        
                        if (is_file(MEMBERSHIP_VIEWS_DIR . '/templates/' . $tempSlug . '.css')) {
                            wp_enqueue_style('arm_template_style_' . $tempSlug, MEMBERSHIP_VIEWS_URL . '/templates/' . $tempSlug . '.css', array(), MEMBERSHIP_VERSION);
                        }
                    }
                }
            }
        }
    }

    /**
     * Set global css for related pages shortcode + widget
     */
    function arm_set_global_css($is_echo = true) {
        global $is_globalcss_added, $arm_global_settings;
        $return_global_css = '';
        if (!$is_globalcss_added) {
            $global_custom_css = $arm_global_settings->arm_get_single_global_settings('global_custom_css');
            if (!empty($global_custom_css)) {
                $return_global_css .= '<style type="text/css">';
                $return_global_css .= stripslashes_deep($global_custom_css);
                $return_global_css .= '</style>';
            }
            $is_globalcss_added = true;
        }
        if ($is_echo) {
            echo $return_global_css; //phpcs:ignore
        } else {
            return $return_global_css;
        }
    }

    /* Setting Front Side JavaScript */

    function set_front_js($isFrontSection = false) {
        global $wp, $wpdb, $post, $wp_scripts, $ARMember, $arm_ajaxurl, $arm_slugs, $arm_global_settings,$is_inline_loaded;
        /* Check Current Front-Page is Membership Page. */       
        $is_arm_front_page = $this->is_arm_front_page();
        $isEnqueueAll = $arm_global_settings->arm_get_single_global_settings('enqueue_all_js_css', 0);
        if (($is_arm_front_page === TRUE || $isEnqueueAll == '1' || $isFrontSection) && !is_admin()) {
            if (version_compare($GLOBALS['wp_version'], '3.8', '<')) {
                wp_deregister_script('jquery');
                wp_dequeue_script('jquery');
                wp_enqueue_script('jquery', MEMBERSHIP_URL . '/js/jquery_1.12.4.js', array(), MEMBERSHIP_VERSION);
            } else {
                wp_enqueue_script('jquery');
            }
            
           
            
            /* Main Plugin Front-End JS */
            $arm_common_js_dependencies = array( 'jquery' );
            if (wp_script_is('heartbeat') && is_user_logged_in() )
            {
                $arm_common_js_dependencies[] = 'heartbeat';
            }
            wp_register_script('arm_common_js', MEMBERSHIP_URL . '/js/arm_common.js', $arm_common_js_dependencies, MEMBERSHIP_VERSION);
            wp_register_script('arm_bpopup', MEMBERSHIP_URL . '/js/jquery.bpopup.min.js', array('jquery'), MEMBERSHIP_VERSION);
            /* Tooltip JS */
            wp_register_script('arm_tipso_front', MEMBERSHIP_URL . '/js/tipso.min.js', array('jquery'), MEMBERSHIP_VERSION);
            /* File Upload JS */
            wp_register_script('arm_file_upload_js', MEMBERSHIP_URL . '/js/arm_file_upload_js.js', array('jquery'), MEMBERSHIP_VERSION);
            
            /* For bootstrap datetime picker js */
            wp_register_script('arm_bootstrap_js', MEMBERSHIP_URL . '/bootstrap/js/bootstrap.min.js', array('jquery'), MEMBERSHIP_VERSION);

            wp_register_script('arm_bootstrap_datepicker_with_locale_js', MEMBERSHIP_URL . '/bootstrap/js/bootstrap-datetimepicker-with-locale.js', array('jquery'), MEMBERSHIP_VERSION);
           
            /* Enqueue Javascripts */
            wp_enqueue_script('jquery-ui-core');
            if (!wp_script_is('arm_bpopup', 'enqueued')) {
                wp_enqueue_script('arm_bpopup');
            }
            
            if (!wp_script_is('arm_bootstrap_js', 'enqueued')) {
                wp_enqueue_script('arm_bootstrap_js');
            }

            if ($isEnqueueAll == '1') {
                if (!wp_script_is('arm_bootstrap_datepicker_with_locale_js', 'enqueued')) {
                    wp_enqueue_script('arm_bootstrap_datepicker_with_locale_js');
                }
                if (!wp_script_is('arm_bpopup', 'enqueued')) {
                    wp_enqueue_script('arm_bpopup');
                }
                if (!wp_script_is('arm_file_upload_js', 'enqueued')) {
                    wp_enqueue_script('arm_file_upload_js');
                }
                if (!wp_script_is('arm_tipso_front', 'enqueued')) {
                    wp_enqueue_script('arm_tipso_front');
                }
            }

            if (!wp_script_is('arm_common_js', 'enqueued')) {
                wp_enqueue_script('arm_common_js');
            }
            /* Load Angular Assets */
            if ($isEnqueueAll == '1') {
                $this->enqueue_angular_script();
            }
            if($is_inline_loaded != 1)
            {
                $this->set_global_javascript_variables();
                $is_inline_loaded = 1;
            }
        }
    }

    function enqueue_angular_script($include_card_validation = false) {
        global $wp, $wpdb, $post, $arm_errors, $ARMember, $arm_ajaxurl;
        /* Design CSS */
        wp_register_style('arm_angular_material_css', MEMBERSHIP_URL . '/materialize/arm_materialize.css', array(), MEMBERSHIP_VERSION);
        wp_enqueue_style('arm_angular_material_css');
        $ValidationJSFiles = array(
            'arm_angular_with_material' => MEMBERSHIP_URL . '/materialize/arm_materialize.js',
            'arm_jquery_validation' => MEMBERSHIP_URL . '/bootstrap/js/jqBootstrapValidation.js',
            'arm_form_validation' => MEMBERSHIP_URL . '/bootstrap/js/arm_form_validation.js',
        );
        foreach ($ValidationJSFiles as $handle => $src) {
            if (!wp_script_is($handle, 'registered')) {
                wp_register_script($handle, $src, array(), MEMBERSHIP_VERSION, true);
            }
            if (!wp_script_is($handle) || ( wp_script_is( $handle ) && !wp_script_is( $handle, 'done' ) ) ) {
                wp_enqueue_script($handle);
            }
        }
    }

    /**
     * Check front page has plugin content.
     */
    function is_arm_front_page() {
        global $wp, $wpdb, $wp_query, $post, $arm_errors, $ARMember, $arm_global_settings;
        if (!is_admin()) {
            $found_matches = array();
            $pattern = '\[(\[?)(arm.*)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            $arm_posts = $wp_query->posts;
            if (is_array($arm_posts)) {
                foreach ($arm_posts as $arm_post) {
                    $arm_post_post_content = isset($arm_post->post_content) ? $arm_post->post_content : '';
                    if (preg_match_all('/' . $pattern . '/s', $arm_post_post_content, $matches) > 0) {
                        $found_matches[] = $matches;
                    }
                }
            }
            /* Remove empty array values. */
            $found_matches = $this->arm_array_trim($found_matches);
            if (!empty($found_matches) && count($found_matches) > 0) {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    function is_arm_setup_page() {
        global $wp, $wpdb, $wp_query, $post, $arm_errors, $ARMember, $arm_global_settings;
        if (!is_admin()) {
            $found_matches = array();
            $pattern = '\[(\[?)(arm_setup)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            $arm_posts = $wp_query->posts;
            if (is_array($arm_posts)) {
                foreach ($arm_posts as $arm_post) {
                    if (preg_match_all('/' . $pattern . '/s', $arm_post->post_content, $matches) > 0) {
                        $found_matches[] = $matches;
                    }
                }
            }
            /* Remove empty array values. */
            $found_matches = $this->arm_array_trim($found_matches);
            if (!empty($found_matches) && count($found_matches) > 0) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * Check if front page content has plugin shortcode and has form.
     */
    function is_arm_form_page() {
        global $wp, $wpdb, $wp_query, $post, $ARMember, $arm_global_settings;
        if (!is_admin()) {
            $found_matches = array();
            $pattern = '\[(\[?)(arm_form|arm_edit_profile|arm_close_account|arm_setup|arm_template)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
            $arm_posts = $wp_query->posts;
            if (is_array($arm_posts) && !empty($arm_posts)) {
                foreach ($arm_posts as $key => $arm_post) {
                    if (preg_match_all('/' . $pattern . '/s', $arm_post->post_content, $matches) > 0) {
                        $found_matches[] = $matches;
                    }
                }
            }

            $found_matches = $this->arm_array_trim($found_matches);
            if (!empty($found_matches) && count($found_matches) > 0) {
                return $found_matches;
            }
        }
        return FALSE;
    }

    /*
     * Trim Array Values.
     */

    function arm_array_trim($array) {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = $this->arm_array_trim($value);
                } else {
                    $array[$key] = trim($value);
                }
                if (empty($array[$key]))
                    unset($array[$key]);
            }
        } else {
            $array = trim($array);
        }
        return $array;
    }

    /**
     * Removes duplicate values from multidimensional array 
     */
    function arm_array_unique($array) {
        $result = array_map("unserialize", array_unique(array_map("serialize", $array)));
        if (is_array($result)) {
            foreach ($result as $key => $value) {
                if (is_array($value)) {
                    $result[$key] = $this->arm_array_unique($value);
                }
            }
        }
        return $result;
    }

    /**
    * Check is gutenberg active or not function start
    */
    function arm_is_gutenberg_active() {
        //Check Gutenberg plugin is installed and activated.
        $gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

        //Version Check Block editor since 5.0.
        $block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

        if ( ! $gutenberg && ! $block_editor ) {
            return false;
        }

        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if ( ! is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
            return true;
        }

        $use_block_editor = get_option( 'classic-editor-replace' ) === 'no-replace';

        return $use_block_editor;
    }
    /**
    * Check is gutenberg active or not function end
    */

     function arm_check_is_gutenberg_page()
     {
        $return = false;
        global $ARMember;
        if($ARMember->arm_check_is_elementor_page())
        {
            $return = false;
        }
        if(function_exists('is_gutenberg_page'))
        {
            if(is_gutenberg_page())
            {
                $return =  true;
            }
        }
        else {
            if ( function_exists( 'get_current_screen' )) {
                $arm_get_current_screen = get_current_screen();
                if(is_object($arm_get_current_screen))
                {
                    if ( isset($arm_get_current_screen->base) && $arm_get_current_screen->base==='post' && $this->arm_is_gutenberg_active() ) {
                        $return =  true;
                    }
                }
            }
        }
        
        return $return;
     }

     function arm_check_is_elementor_page()
     {
        /* if(is_admin() && ( ( !empty($_REQUEST['action']) && $_REQUEST['action']!='elementor_ajax' ) || !empty($_REQUEST['elementor-preview']) ) )
        {
            return true;
        } */
        if( (!empty($_REQUEST['action']) && $_REQUEST['action']!='elementor_ajax' ) || !empty($_REQUEST['elementor-preview']) )
        {
            return true;
        }
        return false;
     }


    /**
     * Restrict Network Activation
     */
    public static function armember_check_network_activation($network_wide) {
        if (!$network_wide)
            return;

        deactivate_plugins(plugin_basename(MEMBERSHIP_DIR.'/armember.php'), TRUE, TRUE);

        header('Location: ' . network_admin_url('plugins.php?deactivate=true'));
        exit;
    }

    public static function install() {


        global $ARMember, $arm_version;
        $armemberlite_exists = 0;
        if(file_exists(WP_PLUGIN_DIR.'/armember-membership/armember-membership.php')){
            $armemberlite_exists = 1;
        }

        
        if ( file_exists( WP_PLUGIN_DIR . '/armember-membership/armember-membership.php' ) && ! is_plugin_active( 'armember-membership/armember-membership.php' ) ) {
            activate_plugin( 'armember-membership/armember-membership.php' );
        }
        
        $armemberlite_version = get_option('armlite_version', '');
        
        // if armemberlite folder exists and activated once ( even though currently not activated )
        if( $armemberlite_version != '' && $armemberlite_exists == 1){  
                 
            $ARMember->update_armember_lite();

            $_version = get_option('arm_version');
            if (empty($_version) || $_version == '') {
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                @set_time_limit(0);
                global $wpdb, $arm_version, $arm_global_settings;
                $arm_global_settings->arm_set_ini_for_access_rules();
                $charset_collate = '';
                if ($wpdb->has_cap('collation')) {
                    if (!empty($wpdb->charset)) {
                        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                    }
                    if (!empty($wpdb->collate)) {
                        $charset_collate .= " COLLATE $wpdb->collate";
                    }
                }

                update_option('arm_version', $arm_version);
                update_option('arm_plugin_activated', 1);
                update_option('arm_show_document_video', 1);
           
                update_option('arm_is_social_login_feature', 0);
                update_option('arm_is_pro_ration_feature', 0);
                update_option('arm_is_drip_content_feature', 0);
                //update_option('arm_is_opt_ins_feature', 0);
                update_option('arm_is_coupon_feature', 0);
                update_option('arm_is_buddypress_feature', 0);
                update_option('arm_is_woocommerce_feature', 0);
                update_option('arm_is_multiple_membership_feature', 0);
                update_option('arm_is_gutenberg_block_restriction_feature', 0);
                update_option('arm_is_mycred_feature', 0);
		update_option('arm_is_plan_limit_feature', 0);
                update_option('arm_is_beaver_builder_restriction_feature', 0);
                update_option('arm_is_divi_builder_restriction_feature', 0);
                update_option('arm_is_wpbakery_page_builder_restriction_feature', 0);
                update_option('arm_is_fusion_builder_restriction_feature', 0);
                update_option('arm_is_oxygen_builder_restriction_feature', 0);
                update_option('arm_is_siteorigin_builder_restriction_feature', 0);
                update_option('arm_is_bricks_builder_restriction_feature', 0);


                update_option('arm_is_invoice_tax_feature', 0);
                $arm_hide_admin_rand_no = wp_rand();
                update_option('arm_hide_wp_amin_disable', $arm_hide_admin_rand_no);

                $arm_dbtbl_create = array();
                
                /* Table structure for `auto message` */
                $tbl_arm_auto_message = $wpdb->prefix . 'arm_auto_message';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_auto_message}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_auto_message}`(
                    `arm_message_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_message_type` VARCHAR(50) NOT NULL,
                    `arm_message_period_unit` INT(11) DEFAULT NULL,
                    `arm_message_period_type` VARCHAR(50) DEFAULT NULL,
                    `arm_message_subscription` VARCHAR(255) NOT NULL,
                    `arm_message_subject` TEXT NOT NULL,
                    `arm_message_content` LONGTEXT NOT NULL,
                    `arm_message_status` INT(1) NOT NULL DEFAULT '1',
                                    `arm_message_send_copy_to_admin` INT(1) NOT NULL DEFAULT '0',
                                    `arm_message_send_diff_msg_to_admin` INT(1) NOT NULL DEFAULT '0',
                    `arm_message_admin_message` LONGTEXT,
                    PRIMARY KEY (`arm_message_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_auto_message] = dbDelta($sql_table);

                /* Table structure for `restricted urls` */
                $tbl_arm_coupons = $wpdb->prefix . 'arm_coupons';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_coupons}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_coupons}`(
                    `arm_coupon_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_coupon_code` VARCHAR(255) NOT NULL,
                                    `arm_coupon_label` VARCHAR(255),
                    `arm_coupon_discount` double NOT NULL DEFAULT '0',
                    `arm_coupon_discount_type` VARCHAR(50) NOT NULL,
                    `arm_coupon_period_type` VARCHAR(50) NOT NULL,
                    `arm_coupon_on_each_subscriptions` TINYINT(1) NULL DEFAULT '0',
                    `arm_coupon_start_date` datetime NOT NULL,
                    `arm_coupon_expire_date` datetime NOT NULL,
                    `arm_coupon_type` TINYINT(1) DEFAULT '0',
                    `arm_coupon_subscription` TEXT,
                    `arm_coupon_paid_posts` TEXT,
                    `arm_coupon_allow_trial` INT(11) NOT NULL DEFAULT '0',
                    `arm_coupon_allowed_uses` INT(11) NOT NULL DEFAULT '0',
                    `arm_coupon_used` INT(11) NOT NULL DEFAULT '0',
                    `arm_coupon_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_coupon_added_date` datetime NOT NULL,
                    PRIMARY KEY (`arm_coupon_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_coupons] = dbDelta($sql_table);

                

                /* Table structure for `Drip Rules` */
                $tbl_arm_drip_rules = $wpdb->prefix . 'arm_drip_rules';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_drip_rules}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_drip_rules}`(
                    `arm_rule_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `arm_item_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                    `arm_item_type` varchar(50) DEFAULT NULL,
                    `arm_rule_type` varchar(50) DEFAULT NULL,
                    `arm_show_old_items` INT(11) NOT NULL DEFAULT '0',
                    `arm_rule_options` longtext,
                    `arm_rule_plans` text,
                    `arm_rule_status` int(1) NOT NULL DEFAULT '1',
                    `arm_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_rule_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_drip_rules] = dbDelta($sql_table);

                $tbl_arm_dripped_contents = $wpdb->prefix . 'arm_dripped_contents';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_dripped_contents}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_dripped_contents}`(
                    `arm_dripped_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `arm_user_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `arm_rule_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `arm_added_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_dripped_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_dripped_contents] = dbDelta($sql_table);

                $tbl_arm_badges_achievements = $wpdb->prefix . 'arm_badges_achievements';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_badges_achievements}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_badges_achievements}`(
                    `arm_badges_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_badges_parent` int(11) NOT NULL DEFAULT '0',
                    `arm_badges_name` varchar(255) DEFAULT NULL,
                    `arm_badges_type` varchar(50) DEFAULT NULL,
                    `arm_badges_icon` TEXT,
                    `arm_badges_achievement` LONGTEXT,
                    `arm_badges_achievement_type` varchar(50) DEFAULT NULL,
                    `arm_badges_tooltip` varchar(255) DEFAULT NULL,
                    PRIMARY KEY (`arm_badges_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_badges_achievements] = dbDelta($sql_table);


                $tbl_arm_debug_payment_log = $wpdb->prefix . 'arm_debug_payment_log';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_debug_payment_log}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_debug_payment_log}`(
                    `arm_payment_log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_payment_log_ref_id` int(11) NOT NULL DEFAULT '0',
                    `arm_payment_log_gateway` varchar(255) DEFAULT NULL,
                    `arm_payment_log_event` varchar(255) DEFAULT NULL,
                    `arm_payment_log_event_from` varchar(255) DEFAULT NULL,
                    `arm_payment_log_status` TINYINT(1) DEFAULT '1',
                    `arm_payment_log_raw_data` TEXT,
                    `arm_payment_log_added_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_payment_log_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_debug_payment_log] = dbDelta($sql_table);

                $tbl_arm_debug_general_log = $wpdb->prefix . 'arm_debug_general_log';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_debug_general_log}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_debug_general_log}`(
                    `arm_general_log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_general_log_event` varchar(255) DEFAULT NULL,
                    `arm_general_log_event_name` varchar(255) DEFAULT NULL,
                    `arm_general_log_event_from` varchar(255) DEFAULT NULL,
                    `arm_general_log_raw_data` TEXT,
                    `arm_general_log_added_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_general_log_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_debug_general_log] = dbDelta($sql_table);


                if(version_compare($armemberlite_version, '2.1', '<')) 
                {
                    global $wpdb, $wp, $ARMember,$arm_member_forms, $arm_global_settings;
                    
                    $arm_pt_log_table = $ARMember->tbl_arm_payment_log;
                    $bt_log_table = $ARMember->tbl_arm_bank_transfer_log;
                    $arm_bank_table_log_flag=get_option('arm_bank_table_log_flag');

                    $arm_old_plan_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_old_plan_id')); //phpcs:ignore 
                    if(empty($arm_old_plan_row)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_old_plan_id` bigint(20) NOT NULL DEFAULT '0' AFTER `arm_plan_id`"); //phpcs:ignore --Reason $arm_pt_log_table is a table name
                    }    

                    $arm_payment_cycle_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_payment_cycle') ); //phpcs:ignore 
                    if(empty($arm_payment_cycle_row)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_payment_cycle` INT(11) NOT NULL DEFAULT '0' AFTER `arm_payment_mode`"); //phpcs:ignore --Reason $arm_pt_log_table is a table name
                    }

                    $arm_bank_name_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_bank_name') ); //phpcs:ignore
                    if(empty($arm_bank_name_row)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_bank_name` VARCHAR(255) DEFAULT NULL AFTER `arm_payment_cycle`"); //phpcs:ignore --Reason $arm_pt_log_table is a table name
                    }

                    $arm_account_name_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_account_name') ); //phpcs:ignore
                    if(empty($arm_account_name_row)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_account_name` VARCHAR(255) DEFAULT NULL AFTER `arm_bank_name`"); //phpcs:ignore --Reason $arm_pt_log_table is a table name
                    }

                    $arm_additional_info_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_additional_info') ); //phpcs:ignore
                    if(empty($arm_additional_info_row)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_additional_info` LONGTEXT AFTER `arm_account_name`"); //phpcs:ignore --Reason $arm_pt_log_table is a table name
                    }

                    $arm_first_name_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_first_name') ); //phpcs:ignore
                    if(empty($arm_first_name_row)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_first_name` VARCHAR(255) DEFAULT NULL AFTER `arm_user_id`"); //phpcs:ignore --Reason $arm_pt_log_table is a table name
                    }

                    $arm_last_name_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%d AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_last_name') ); //phpcs:ignore
                    if(empty($arm_last_name_row)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_last_name` VARCHAR(255) DEFAULT NULL AFTER `arm_first_name`"); //phpcs:ignore --Reason $arm_pt_log_table is a table name
                    }

                    $arm_payment_transfer_mode_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_payment_transfer_mode') ); //phpcs:ignore
                    if(empty($arm_payment_transfer_mode_row)) {
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_payment_transfer_mode` VARCHAR( 255 ) NULL AFTER `arm_additional_info`"); //phpcs:ignore --Reason $arm_pt_log_table is a table name
                    }

                    if(empty($arm_bank_table_log_flag)){
                        
                        update_option('arm_bank_table_log_flag','1');

                        $btquery = "SELECT * FROM `" . $bt_log_table . "`";
                        $bt_payment_log = $wpdb->get_results($btquery, ARRAY_A); //phpcs:ignore --Reason $btquery is a query without where clause
                        if(count($bt_payment_log)>0){
                            foreach ($bt_payment_log as $bt_payment_log_data) {
                                $arm_first_name=get_user_meta($bt_payment_log_data["arm_user_id"],'first_name',true);
                                $arm_last_name=get_user_meta($bt_payment_log_data["arm_user_id"],'last_name',true);
                                $arm_payment_mode=(!empty($bt_payment_log_data["arm_payment_mode"]))? $bt_payment_log_data["arm_payment_mode"]:'one_time';
                                $arm_payment_type=(!empty($bt_payment_log_data["arm_payment_mode"]) && $bt_payment_log_data["arm_payment_mode"]=='manual_subscription')?'subscription':'one_time';
                                $bt_insert_result=$wpdb->insert($arm_pt_log_table, array(
                                    'arm_invoice_id' => $bt_payment_log_data["arm_invoice_id"],
                                    'arm_user_id' => $bt_payment_log_data["arm_user_id"],
                                    'arm_first_name' => $arm_first_name,
                                    'arm_last_name' => $arm_last_name,
                                    'arm_plan_id' => $bt_payment_log_data["arm_plan_id"],
                                    'arm_old_plan_id' =>$bt_payment_log_data["arm_old_plan_id"],
                                    'arm_payer_email' => $bt_payment_log_data["arm_payer_email"],
                                    'arm_transaction_id' => $bt_payment_log_data["arm_transaction_id"],
                                    'arm_transaction_payment_type'=>$arm_payment_type,
                                    'arm_payment_mode' => $arm_payment_mode,
                                    'arm_payment_type' => $arm_payment_type,
                                    'arm_payment_gateway' => 'bank_transfer',
                                    'arm_payment_cycle' => $bt_payment_log_data["arm_payment_cycle"],
                                    'arm_bank_name' => $bt_payment_log_data["arm_bank_name"],
                                    'arm_account_name' => $bt_payment_log_data["arm_account_name"],
                                    'arm_additional_info' => $bt_payment_log_data["arm_additional_info"],
                                    'arm_amount' => $bt_payment_log_data["arm_amount"],
                                    'arm_currency' => $bt_payment_log_data["arm_currency"],
                                    'arm_extra_vars' => $bt_payment_log_data["arm_extra_vars"],
                                    'arm_coupon_code' => $bt_payment_log_data["arm_coupon_code"],
                                    'arm_coupon_discount' => $bt_payment_log_data["arm_coupon_discount"],
                                    'arm_coupon_discount_type' => $bt_payment_log_data["arm_coupon_discount_type"],
                                    'arm_coupon_on_each_subscriptions' => $bt_payment_log_data["arm_coupon_on_each_subscriptions"],
                                    'arm_transaction_status' => $bt_payment_log_data["arm_status"],
                                    'arm_is_trial' => $bt_payment_log_data["arm_is_trial"],
                                    'arm_display_log' => $bt_payment_log_data["arm_display_log"],
                                    'arm_payment_date' => $bt_payment_log_data["arm_created_date"],
                                    'arm_created_date'=> $bt_payment_log_data["arm_created_date"],
                                ));
                            }
                        }
                    }
                }
                if(version_compare($armemberlite_version, '2.4', '<')) 
                {
                    global $wpdb, $wp, $ARMember;

                    $arm_pt_log_table = $ARMember->tbl_arm_payment_log;
                    $arm_entries_table = $ARMember->tbl_arm_entries;
                    $arm_subscription_plans_table = $ARMember->tbl_arm_subscription_plans;
                    $arm_activity_table = $ARMember->tbl_arm_activity;
                    $arm_membership_setup_table = $ARMember->tbl_arm_membership_setup;

                    $arm_add_payment_log_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_is_post_payment') ); //phpcs:ignore
                    if(empty($arm_add_payment_log_col)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_is_post_payment` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_is_trial`"); //phpcs:ignore --Reason $arm_pt_log_table ia a table name
                    }
                    
                    $arm_add_payment_log_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_paid_post_id') ); //phpcs:ignore
                    if(empty($arm_add_payment_log_col)){
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_is_post_payment`");//phpcs:ignore --Reason $arm_pt_log_table ia a table name
                    }
                    
                    $arm_add_entries_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_entries_tablem,'arm_is_post_entry') ); //phpcs:ignore
                    if(empty($arm_add_entries_col)){
                        $wpdb->query("ALTER TABLE `{$arm_entries_table}` ADD `arm_is_post_entry` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_plan_id`"); //phpcs:ignore --Reason $arm_entries_table is a table name
                    }
                    
                    $arm_add_entries_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_entries_table,'arm_paid_post_id') ); //phpcs:ignore
                    if(empty($arm_add_entries_col)){
                        $wpdb->query("ALTER TABLE `{$arm_entries_table}` ADD `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_is_post_entry`");//phpcs:ignore --Reason $arm_entries_table is a table name
                    }

                    $arm_add_subscription_plans = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_subscription_plans_table,'arm_subscription_plan_post_id') ); //phpcs:ignore
                    if(empty($arm_add_subscription_plans)){
                        $wpdb->query("ALTER TABLE `{$arm_subscription_plans_table}` ADD `arm_subscription_plan_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_subscription_plan_role`");//phpcs:ignore --Reason $arm_subscription_plans_table is a table 
                    }

                    $arm_add_activity_post_id = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_activity_table,'arm_paid_post_id') );
                    if(empty($arm_add_activity_post_id)){
                        $wpdb->query("ALTER TABLE `{$arm_activity_table}` ADD `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_item_id`"); //phpcs:ignore --Reason $arm_activity_table is a table 
                    }

                    $arm_add_setup_type = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = 'arm_setup_type'",DB_NAME,$arm_membership_setup_table,'arm_setup_type') ); //phpcs:ignore
                    if(empty($arm_add_setup_type)){
                        $wpdb->query("ALTER TABLE `{$arm_membership_setup_table}` ADD `arm_setup_type` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_setup_name`"); //phpcs:ignore --Reason $arm_membership_setup_table is a table 
                    }
                }

                if(version_compare($armemberlite_version, '3.4.4', '<')) 
                {
                    $arm_subscription_plans_table = $ARMember->tbl_arm_subscription_plans;
                    $arm_activity_table = $ARMember->tbl_arm_activity;
                    $arm_pt_log_table = $ARMember->tbl_arm_payment_log;
                    $arm_entries_table = $ARMember->tbl_arm_entries;

                    //Add the arm_subscription_plan_gift_status for the Gift
                    $arm_add_subscription_plan_gift_status_column = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_subscription_plans_table,'arm_subscription_plan_gift_status') ); //phpcs:ignore
                    if(empty($arm_add_subscription_plan_gift_status_column)) {
                        $wpdb->query("ALTER TABLE `{$arm_subscription_plans_table}` ADD `arm_subscription_plan_gift_status` INT(1) NOT NULL DEFAULT '0' AFTER `arm_subscription_plan_post_id`"); //phpcs:ignore --Reason $arm_subscription_plans_table is a table name
                    }

                    //Add the arm_gift_plan_id for the Gift 
                    $arm_add_activity_column = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_activity_table,'arm_gift_plan_id') ); //phpcs:ignore
                    if( empty($arm_add_activity_column) ) {
                        $wpdb->query("ALTER TABLE `{$arm_activity_table}` ADD `arm_gift_plan_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_paid_post_id`");//phpcs:ignore --Reason $arm_activity_table is a table name
                    }

                    // Add column arm_is_gift_payment for gift.
                    $arm_add_payment_log_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_is_gift_payment') ); //phpcs:ignore
                    if(empty($arm_add_payment_log_col)) {
                        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_is_gift_payment` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_paid_post_id`"); //phpcs:ignore --Reason $arm_pt_log_table is a table name
                    }

                    $arm_add_entries_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_entries_table,'arm_is_gift_entry') ); //phpcs:ignore
                    if(empty($arm_add_entries_col)) {
                        $wpdb->query("ALTER TABLE `{$arm_entries_table}` ADD `arm_is_gift_entry` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_paid_post_id`");//phpcs:ignore --Reason $arm_entries_table is a table name
                    }
                }

                if(version_compare($armemberlite_version, '3.4.9', '<')) 
                {
                    $arm_members_table = $ARMember->tbl_arm_members;
                    //Add the arm_user_plan_ids for the Members table
                    $arm_add_arm_user_plan_ids_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_members_table,'arm_user_plan_ids') ); //phpcs:ignore
                    if(empty($arm_add_arm_user_plan_ids_col)){
                        $wpdb->query("ALTER TABLE `{$arm_members_table}` ADD `arm_user_plan_ids` TEXT NULL AFTER `arm_secondary_status`");//phpcs:ignore --Reason $arm_members_table is a table name
                    }

                    //Add the arm_user_suspended_plan_ids for the Members table
                    $arm_add_arm_user_suspended_plan_ids_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_members_table,'arm_user_suspended_plan_ids') );//phpcs:ignore
                    if(empty($arm_add_arm_user_suspended_plan_ids_col)){
                        $wpdb->query("ALTER TABLE `{$arm_members_table}` ADD `arm_user_suspended_plan_ids` TEXT NULL AFTER `arm_user_plan_ids`"); //phpcs:ignore --Reason $arm_members_table is a table name
                    }
                }

                if(version_compare($armemberlite_version, '4.0.9', '<'))
                {
                    $armember_check_db_permission = $ARMember->armember_check_db_permission();
                    if(!empty($armember_check_db_permission))
                    {
                        $arm_members_table = $ARMember->tbl_arm_members;
                        $arm_tbl_arm_payment_log = $ARMember->tbl_arm_payment_log;
                        $arm_tbl_arm_debug_payment_log = $ARMember->tbl_arm_debug_payment_log;
                        $arm_tbl_arm_debug_general_log = $ARMember->tbl_arm_debug_general_log;
                        
                        //Add the arm-user-id INDEX for the Members table
                        $arm_members_add_index_arm_user_id = $wpdb->get_results(  $wpdb->prepare("SHOW INDEX FROM ".$arm_members_table." where Key_name=%s ",'arm-user-id') ); //phpcs:ignore --Reason $arm_members_table is a table name
                        if(empty($arm_members_add_index_arm_user_id))
                        {
                            $wpdb->query("ALTER TABLE `{$arm_members_table}` ADD INDEX `arm-user-id` (`arm_user_id`)");//phpcs:ignore --Reason $arm_members_table is a table name
                        }

                        //Add the arm-user-id INDEX for the Payment table
                        $arm_payment_log_add_index_arm_user_id = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_payment_log." where Key_name=%s ",'arm-user-id') ); //phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                        if(empty($arm_payment_log_add_index_arm_user_id))
                        {
                            $wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}` ADD INDEX `arm-user-id` (`arm_user_id`)");//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                        }

                        //Add the arm-plan-id INDEX for the Payment table
                        $arm_payment_log_add_index_arm_plan_id = $wpdb->get_results(  $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_payment_log." where Key_name=%s ",'arm-plan-id') );//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                        if(empty($arm_payment_log_add_index_arm_plan_id))
                        {
                            $wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}` ADD INDEX `arm-plan-id` (`arm_plan_id`)");//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                        }

                        //Add the arm-paid-post-id INDEX for the Payment table
                        $arm_payment_log_add_index_arm_paid_post_id = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_payment_log." where Key_name=%s ",'arm-paid-post-id') ); //phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                        if(empty($arm_payment_log_add_index_arm_paid_post_id))
                        {
                            $wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}` ADD INDEX `arm-paid-post-id` (`arm_paid_post_id`)");//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                        }

                        //Add the arm-is-gift-payment INDEX for the Payment table
                        $arm_payment_log_add_index_arm_is_gift_payment = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_payment_log." where Key_name=%s",'arm-is-gift-payment') ); //phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                        if(empty($arm_payment_log_add_index_arm_is_gift_payment))
                        {
                            $wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}` ADD INDEX `arm-is-gift-payment` (`arm_is_gift_payment`)");//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                        }

                        //Add the arm-display-log INDEX for the Payment table
                        $arm_payment_log_add_index_arm_display_log = $wpdb->get_results($wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_payment_log." where Key_name=%s ",'arm-display-log'));//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                        if(empty($arm_payment_log_add_index_arm_display_log))
                        {
                            $wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}` ADD INDEX `arm-display-log` (`arm_display_log`)");//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                        }

                        //Add the arm-debug-payment-log-gateway INDEX for the Payment table
                        $arm_debug_payment_log_add_index_arm_gateway = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_debug_payment_log." where Key_name=%s ",'arm-debug-payment-log-gateway') );//phpcs:ignore --Reason $arm_tbl_arm_debug_payment_log is a table name
                        if(empty($arm_debug_payment_log_add_index_arm_gateway))
                        {
                            $wpdb->query("ALTER TABLE `{$arm_tbl_arm_debug_payment_log}` ADD INDEX `arm-debug-payment-log-gateway` (`arm_payment_log_gateway`)");//phpcs:ignore --Reason $arm_tbl_arm_debug_payment_log is a table name
                        }

                        //Add the arm-debug-payment-log-status INDEX for the Payment table
                        $arm_debug_payment_log_add_index_arm_status = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_debug_payment_log." where Key_name=%s ",'arm-debug-payment-log-status') ); //phpcs:ignore --Reason $arm_tbl_arm_debug_payment_log is a table name
                        if(empty($arm_debug_payment_log_add_index_arm_status))
                        {
                            $wpdb->query("ALTER TABLE `{$arm_tbl_arm_debug_payment_log}` ADD INDEX `arm-debug-payment-log-status` (`arm_payment_log_status`)");//phpcs:ignore --Reason $arm_tbl_arm_debug_payment_log is a table name
                        }

                        //Add the arm-debug-general-log-event INDEX for the general table
                        $arm_debug_general_log_add_index_arm_event = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_debug_general_log." where Key_name=%s ",'arm-debug-general-log-event') );//phpcs:ignore --Reason $arm_tbl_arm_debug_general_log is a table name
                        if(empty($arm_debug_general_log_add_index_arm_event))
                        {
                            $wpdb->query("ALTER TABLE `{$arm_tbl_arm_debug_general_log}` ADD INDEX `arm-debug-general-log-event` (`arm_general_log_event`)");//phpcs:ignore --Reason $arm_tbl_arm_debug_general_log is a table name
                        }
                    }
                }

                
                $buddypress_settings_array = array('avatar_map'=> 1, 'profile_cover_map' => 1, 'show_armember_profile' => 0);
                $serialized_buddypress_options = $buddypress_settings_array;
                update_option('arm_buddypress_options', $serialized_buddypress_options);
                
                /* Plugin Action Hook After Install Process */
                do_action('arm_after_activation_hook');
                do_action('arm_after_install');

                add_option('armember_pro_install_date', current_time('mysql') );
                $armlite_is_wizard_complete = get_option('arm_lite_is_wizard_complete');
                if(!empty($armlite_is_wizard_complete))
                {
                    update_option('arm_is_wizard_complete',1);
                }
                else {
                    update_option('arm_lite_is_wizard_complete',1);
                }
                
                global $arm_members_activity;
                $arm_members_activity->getwpversion();
            } else {
                
                $ARMember->wpdbfix();
                do_action('arm_reactivate_plugin');
            }
            $args = array(
                'role' => 'administrator',
                'fields' => 'id'
            );
            $users = get_users($args);
            if (count($users) > 0) {
                foreach ($users as $key => $user_id) {
                    $armroles = $ARMember->arm_capabilities();
                    $userObj = new WP_User($user_id);
                    foreach ($armroles as $armrole => $armroledescription) {
                        $userObj->add_cap($armrole);
                    }
                    unset($armrole);
                    unset($armroles);
                    unset($armroledescription);
                }
            }

        }
        else{
              
            global $ARMember, $arm_version, $arm_access_rules;

            $_version = get_option('arm_version');
            if (empty($_version) || $_version == '') {
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                @set_time_limit(0);
                global $wpdb, $arm_version, $arm_global_settings;
                $arm_global_settings->arm_set_ini_for_access_rules();
                $charset_collate = '';
                if ($wpdb->has_cap('collation')) {
                    if (!empty($wpdb->charset)) {
                        $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                    }
                    if (!empty($wpdb->collate)) {
                        $charset_collate .= " COLLATE $wpdb->collate";
                    }
                }

                update_option('arm_version', $arm_version);
                update_option('arm_plugin_activated', 1);
                update_option('arm_show_document_video', 1);
                update_option('arm_is_social_feature', 0);
                update_option('arm_is_user_private_content_feature', 0);
                update_option('arm_is_social_login_feature', 0);
                update_option('arm_is_pro_ration_feature', 0);
                update_option('arm_is_drip_content_feature', 0);
                //update_option('arm_is_opt_ins_feature', 0);
                update_option('arm_is_coupon_feature', 0);
                update_option('arm_is_buddypress_feature', 0);
                update_option('arm_is_woocommerce_feature', 0);
                update_option('arm_is_multiple_membership_feature', 0);
                update_option('arm_is_gutenberg_block_restriction_feature', 0);
                update_option('arm_is_mycred_feature', 0);
		update_option('arm_is_plan_limit_feature', 0);
                update_option('arm_is_beaver_builder_restriction_feature', 0);
                update_option('arm_is_divi_builder_restriction_feature', 0);
                update_option('arm_is_wpbakery_page_builder_restriction_feature', 0);
                update_option('arm_is_fusion_builder_restriction_feature', 0);
                update_option('arm_is_oxygen_builder_restriction_feature', 0);
                update_option('arm_is_siteorigin_builder_restriction_feature', 0);
                update_option('arm_is_bricks_builder_restriction_feature', 0);
                update_option('arm_is_invoice_tax_feature', 0);
                
                $arm_hide_admin_rand_no = wp_rand();
                update_option('arm_hide_wp_amin_disable', $arm_hide_admin_rand_no);

                $arm_dbtbl_create = array();
                /* Table structure for `Members activity` */
                $tbl_arm_members_activity = $wpdb->prefix . 'arm_activity';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_members_activity}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_members_activity}`(
                    `arm_activity_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_type` VARCHAR(50) NOT NULL,
                    `arm_action` VARCHAR(50) NOT NULL,
                    `arm_content` LONGTEXT NOT NULL,
                    `arm_item_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_paid_post_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_gift_plan_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_link` VARCHAR(255) DEFAULT NULL,
                    `arm_activity_plan_name` VARCHAR(255) NOT NULL,
                    `arm_activity_plan_type` VARCHAR(255) NOT NULL,
                    `arm_activity_payment_gateway` VARCHAR(255) NOT NULL,
                    `arm_activity_plan_amount` DOUBLE NOT NULL DEFAULT '0',
                    `arm_activity_plan_start_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_activity_plan_end_date` datetime NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_activity_plan_next_cycle_date` datetime NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_ip_address` VARCHAR(50) NOT NULL,
                    `arm_date_recorded` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_activity_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_members_activity] = dbDelta($sql_table);

                /* Table structure for `auto message` */
                $tbl_arm_auto_message = $wpdb->prefix . 'arm_auto_message';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_auto_message}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_auto_message}`(
                    `arm_message_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_message_type` VARCHAR(50) NOT NULL,
                    `arm_message_period_unit` INT(11) DEFAULT NULL,
                    `arm_message_period_type` VARCHAR(50) DEFAULT NULL,
                    `arm_message_subscription` VARCHAR(255) NOT NULL,
                    `arm_message_subject` TEXT NOT NULL,
                    `arm_message_content` LONGTEXT NOT NULL,
                    `arm_message_status` INT(1) NOT NULL DEFAULT '1',
                                    `arm_message_send_copy_to_admin` INT(1) NOT NULL DEFAULT '0',
                                    `arm_message_send_diff_msg_to_admin` INT(1) NOT NULL DEFAULT '0',
                    `arm_message_admin_message` LONGTEXT,
                    PRIMARY KEY (`arm_message_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_auto_message] = dbDelta($sql_table);

                /* Table structure for `restricted urls` */
                $tbl_arm_coupons = $wpdb->prefix . 'arm_coupons';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_coupons}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_coupons}`(
                    `arm_coupon_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_coupon_code` VARCHAR(255) NOT NULL,
                                    `arm_coupon_label` VARCHAR(255),
                    `arm_coupon_discount` double NOT NULL DEFAULT '0',
                    `arm_coupon_discount_type` VARCHAR(50) NOT NULL,
                    `arm_coupon_period_type` VARCHAR(50) NOT NULL,
                    `arm_coupon_on_each_subscriptions` TINYINT(1) NULL DEFAULT '0',
                    `arm_coupon_start_date` datetime NOT NULL,
                    `arm_coupon_expire_date` datetime NOT NULL,
                    `arm_coupon_type` TINYINT(1) DEFAULT '0',
                    `arm_coupon_subscription` TEXT,
                    `arm_coupon_paid_posts` TEXT,
                    `arm_coupon_allow_trial` INT(11) NOT NULL DEFAULT '0',
                    `arm_coupon_allowed_uses` INT(11) NOT NULL DEFAULT '0',
                    `arm_coupon_used` INT(11) NOT NULL DEFAULT '0',
                    `arm_coupon_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_coupon_added_date` datetime NOT NULL,
                    PRIMARY KEY (`arm_coupon_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_coupons] = dbDelta($sql_table);

                /* Table structure for `email settings` */
                $tbl_arm_email_settings = $wpdb->prefix . 'arm_email_templates';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_email_settings}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_email_settings}`(
                    `arm_template_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_template_name` VARCHAR(255) NOT NULL,
                    `arm_template_slug` VARCHAR(255) NOT NULL ,
                    `arm_template_subject` VARCHAR(255) NOT NULL,
                    `arm_template_content` longtext NOT NULL,
                    `arm_template_status` INT(1) NOT NULL DEFAULT '1',
                    PRIMARY KEY (`arm_template_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_email_settings] = dbDelta($sql_table);

                /* Table structure for `Entries` */
                $tbl_arm_entries = $wpdb->prefix . 'arm_entries';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_entries}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_entries}` (
                    `arm_entry_id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `arm_entry_email` varchar(255) DEFAULT NULL,
                    `arm_name` varchar(255) DEFAULT NULL,
                    `arm_description` LONGTEXT,
                    `arm_ip_address` text,
                    `arm_browser_info` text,
                    `arm_entry_value` LONGTEXT,
                    `arm_form_id` int(11) DEFAULT NULL,
                    `arm_user_id` bigint(20) DEFAULT NULL,
                    `arm_plan_id` int(11) DEFAULT NULL,
                    `arm_is_post_entry` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0',
                    `arm_is_gift_entry` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_entry_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_entries] = dbDelta($sql_table);

                /* Table structure for `failed login` */
                $tbl_arm_fail_attempts = $wpdb->prefix . 'arm_fail_attempts';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_fail_attempts}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_fail_attempts}`(
                    `arm_fail_attempts_id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` bigint(20) NOT NULL,
                    `arm_fail_attempts_detail` text,
                    `arm_fail_attempts_ip` varchar(200) DEFAULT NULL,
                    `arm_is_block` int(1) NOT NULL DEFAULT '0',
                    `arm_fail_attempts_datetime` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_fail_attempts_release_datetime` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_fail_attempts_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_fail_attempts] = dbDelta($sql_table);

                /* Table structure for `arm_forms` */
                $tbl_arm_forms = $wpdb->prefix . 'arm_forms';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_forms}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_forms}` (
                    `arm_form_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_form_label` VARCHAR(255) DEFAULT NULL,
                    `arm_form_title` VARCHAR(255) DEFAULT NULL,
                    `arm_form_type` VARCHAR(100) DEFAULT NULL,
                    `arm_form_slug` VARCHAR(255) DEFAULT NULL,
                    `arm_is_default` INT(1) NOT NULL DEFAULT '0',
                    `arm_set_name` VARCHAR(255) DEFAULT NULL,
                    `arm_set_id` INT(11) NOT NULL DEFAULT '0',
                    `arm_is_template` INT(11) NOT NULL DEFAULT '0',
                    `arm_ref_template` INT(11) NOT NULL DEFAULT '0',
                    `arm_form_settings` LONGTEXT,
                    `arm_form_updated_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_form_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_form_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_forms] = dbDelta($sql_table);

                /* Table structure for `arm_form_field` */
                $tbl_arm_form_field = $wpdb->prefix . 'arm_form_field';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_form_field}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_form_field}`(
                    `arm_form_field_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_form_field_form_id` INT(11) NOT NULL,
                    `arm_form_field_order` INT(11) NOT NULL DEFAULT '0',
                    `arm_form_field_slug` VARCHAR(255) DEFAULT NULL,
                    `arm_form_field_option` LONGTEXT,
                                    `arm_form_field_bp_field_id` INT(11) NOT NULL DEFAULT '0',
                    `arm_form_field_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_form_field_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_form_field_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_form_field] = dbDelta($sql_table);

                /* Table structure for `lockdown` */
                $tbl_arm_lockdown = $wpdb->prefix . 'arm_lockdown';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_lockdown}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_lockdown}`(
                    `arm_lockdown_ID` bigint(20) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` bigint(20) NOT NULL,
                    `arm_lockdown_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_release_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_lockdown_IP` VARCHAR(255) DEFAULT NULL,
                    PRIMARY KEY  (`arm_lockdown_ID`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_lockdown] = dbDelta($sql_table);

                /* Table structure for `arm_members` */
                $tbl_arm_members = $wpdb->prefix . 'arm_members';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_members}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_members}` (
                  `arm_member_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                  `arm_user_id` bigint(20) unsigned NOT NULL,
                  `arm_user_login` VARCHAR(60) NOT NULL DEFAULT '',
                  `arm_user_nicename` VARCHAR(50) NOT NULL DEFAULT '',
                  `arm_user_email` VARCHAR(100) NOT NULL DEFAULT '',
                  `arm_user_url` VARCHAR(100) NOT NULL DEFAULT '',
                  `arm_user_registered` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                  `arm_user_activation_key` VARCHAR(60) NOT NULL DEFAULT '',
                  `arm_user_status` INT(11) NOT NULL DEFAULT '0',
                  `arm_display_name` VARCHAR(250) NOT NULL DEFAULT '',
                  `arm_user_type` int(1) NOT NULL DEFAULT '0',
                  `arm_primary_status` int(1) NOT NULL DEFAULT '1',
                  `arm_secondary_status` int(1) NOT NULL DEFAULT '0',
                  `arm_user_plan_ids` TEXT NULL,
                  `arm_user_suspended_plan_ids` TEXT NULL,
                  PRIMARY KEY (`arm_member_id`),
                  KEY `arm_user_login_key` (`arm_user_login`),
                  KEY `arm_user_nicename` (`arm_user_nicename`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_members] = dbDelta($sql_table);

                /* Table structure for `Membership Setup Wizard` */
                $tbl_arm_membership_setup = $wpdb->prefix . 'arm_membership_setup';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_membership_setup}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_membership_setup}`(
                    `arm_setup_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_setup_name` VARCHAR(255) NOT NULL,
                    `arm_setup_type` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_setup_modules` LONGTEXT,
                    `arm_setup_labels` LONGTEXT,
                    `arm_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_setup_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_membership_setup] = dbDelta($sql_table);

                /* Table structure for `Payment Log` */
                $tbl_arm_payment_log = $wpdb->prefix . 'arm_payment_log';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_payment_log}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_payment_log}`(
                    `arm_log_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_invoice_id` INT(11) NOT NULL DEFAULT '0',
                    `arm_user_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_first_name` VARCHAR(255) DEFAULT NULL,
                    `arm_last_name` VARCHAR(255) DEFAULT NULL,
                    `arm_plan_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_old_plan_id` bigint(20) NOT NULL DEFAULT '0',
                    `arm_payment_gateway` VARCHAR(50) NOT NULL,
                    `arm_payment_type` VARCHAR(50) NOT NULL,
                    `arm_token` TEXT,
                    `arm_payer_email` VARCHAR(255) DEFAULT NULL,
                    `arm_receiver_email` VARCHAR(255) DEFAULT NULL,
                    `arm_transaction_id` TEXT,
                    `arm_transaction_payment_type` VARCHAR(100) DEFAULT NULL,
                    `arm_transaction_status` TEXT,
                    `arm_payment_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    `arm_payment_mode` VARCHAR(255),
                    `arm_payment_cycle` INT(11) NOT NULL DEFAULT '0',
                    `arm_bank_name` VARCHAR(255) DEFAULT NULL,
                    `arm_account_name` VARCHAR(255) DEFAULT NULL,
                    `arm_additional_info` LONGTEXT,
                    `arm_payment_transfer_mode` VARCHAR(255) DEFAULT NULL,
                    `arm_amount` double NOT NULL DEFAULT '0',
                    `arm_currency` VARCHAR(50) DEFAULT NULL,
                    `arm_extra_vars` LONGTEXT,
                    `arm_coupon_code` VARCHAR(255) DEFAULT NULL,
                    `arm_coupon_discount` double NOT NULL DEFAULT '0',
                    `arm_coupon_discount_type` VARCHAR(50) DEFAULT NULL,
                    `arm_coupon_on_each_subscriptions` TINYINT(1) NULL DEFAULT '0',
                    `arm_is_post_payment` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0',
                    `arm_is_gift_payment` TINYINT(1) NOT NULL DEFAULT '0',
                    `arm_is_trial` INT(1) NOT NULL DEFAULT '0',
                    `arm_display_log` INT(1) NOT NULL DEFAULT '1',
                    `arm_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_log_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_payment_log] = dbDelta($sql_table);

                
                /* Table structure for `arm_subscription_plans` */
                $tbl_arm_subscription_plans = $wpdb->prefix . 'arm_subscription_plans';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_subscription_plans}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_subscription_plans}`(
                    `arm_subscription_plan_id` INT(11) NOT NULL AUTO_INCREMENT,
                    `arm_subscription_plan_name` VARCHAR(255) NOT NULL,
                    `arm_subscription_plan_description` TEXT,
                    `arm_subscription_plan_type` VARCHAR(50) NOT NULL,
                    `arm_subscription_plan_options` LONGTEXT,
                    `arm_subscription_plan_amount` double NOT NULL DEFAULT '0',
                    `arm_subscription_plan_status` INT(1) NOT NULL DEFAULT '1',
                    `arm_subscription_plan_role` VARCHAR(100) DEFAULT NULL,
                    `arm_subscription_plan_post_id` BIGINT(20) NOT NULL DEFAULT '0',
                    `arm_subscription_plan_gift_status` INT(1) NOT NULL DEFAULT '0',
                    `arm_subscription_plan_is_delete` INT(1) NOT NULL DEFAULT '0',
                    `arm_subscription_plan_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_subscription_plan_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_subscription_plans] = dbDelta($sql_table);

                /* Table structure for `Taxonomy Term Meta` */
                $tbl_arm_termmeta = $wpdb->prefix . 'arm_termmeta';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_termmeta}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_termmeta}`(
                    `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `arm_term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                    `meta_key` VARCHAR(255) DEFAULT NULL,
                    `meta_value` longtext,
                    PRIMARY KEY (`meta_id`),
                    KEY `arm_term_id` (`arm_term_id`),
                    KEY `meta_key` (`meta_key`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_termmeta] = dbDelta($sql_table);

                /* Table structure for `Member Templates` */
                $tbl_arm_member_templates = $wpdb->prefix . 'arm_member_templates';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_member_templates}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_member_templates}`(
                    `arm_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_title` text,
                    `arm_slug` varchar(255) DEFAULT NULL,
                    `arm_type` varchar(50) DEFAULT NULL,
                    `arm_default` int(1) NOT NULL DEFAULT '0',
                                    `arm_subscription_plan` text NULL,
                    `arm_core` int(1) NOT NULL DEFAULT '0',
                                    `arm_template_html` longtext,
                                    `arm_ref_template` int(11) NOT NULL DEFAULT '0',
                    `arm_options` longtext,
                    `arm_html_before_fields` longtext,
                    `arm_html_after_fields` longtext,
                    `arm_enable_admin_profile` int(1) NOT NULL DEFAULT '0',
                    `arm_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_member_templates] = dbDelta($sql_table);

                /* Table structure for `Drip Rules` */
                $tbl_arm_drip_rules = $wpdb->prefix . 'arm_drip_rules';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_drip_rules}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_drip_rules}`(
                    `arm_rule_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    `arm_item_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                    `arm_item_type` varchar(50) DEFAULT NULL,
                    `arm_rule_type` varchar(50) DEFAULT NULL,
                    `arm_show_old_items` INT(11) NOT NULL DEFAULT '0',
                    `arm_rule_options` longtext,
                    `arm_rule_plans` text,
                    `arm_rule_status` int(1) NOT NULL DEFAULT '1',
                    `arm_created_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_rule_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_drip_rules] = dbDelta($sql_table);

                $tbl_arm_dripped_contents = $wpdb->prefix . 'arm_dripped_contents';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_dripped_contents}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_dripped_contents}`(
                    `arm_dripped_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `arm_user_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `arm_rule_id` int(11) unsigned NOT NULL DEFAULT '0',
                    `arm_added_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_dripped_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_dripped_contents] = dbDelta($sql_table);

                $tbl_arm_badges_achievements = $wpdb->prefix . 'arm_badges_achievements';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_badges_achievements}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_badges_achievements}`(
                    `arm_badges_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_badges_parent` int(11) NOT NULL DEFAULT '0',
                    `arm_badges_name` varchar(255) DEFAULT NULL,
                    `arm_badges_type` varchar(50) DEFAULT NULL,
                    `arm_badges_icon` TEXT,
                    `arm_badges_achievement` LONGTEXT,
                    `arm_badges_achievement_type` varchar(50) DEFAULT NULL,
                    `arm_badges_tooltip` varchar(255) DEFAULT NULL,
                    PRIMARY KEY (`arm_badges_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_badges_achievements] = dbDelta($sql_table);

                $tbl_arm_login_history = $wpdb->prefix . 'arm_login_history';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_login_history}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_login_history}`(
                    `arm_history_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_user_id` int(11) NOT NULL,
                    `arm_logged_in_ip` varchar(255) NOT NULL,
                    `arm_logged_in_date` DATETIME NOT NULL,
                    `arm_logout_date` DATETIME NOT NULL,
                    `arm_login_duration` TIME NOT NULL,
                    `arm_history_browser` VARCHAR(255) NOT NULL,
                    `arm_history_session` VARCHAR(255) NOT NULL,
                    `arm_login_country` VARCHAR(255) NOT NULL,
                                    `arm_user_current_status` int(1) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`arm_history_id`)
                ){$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_login_history] = dbDelta($sql_table);


                $tbl_arm_debug_payment_log = $wpdb->prefix . 'arm_debug_payment_log';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_debug_payment_log}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_debug_payment_log}`(
                    `arm_payment_log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_payment_log_ref_id` int(11) NOT NULL DEFAULT '0',
                    `arm_payment_log_gateway` varchar(255) DEFAULT NULL,
                    `arm_payment_log_event` varchar(255) DEFAULT NULL,
                    `arm_payment_log_event_from` varchar(255) DEFAULT NULL,
                    `arm_payment_log_status` TINYINT(1) DEFAULT '1',
                    `arm_payment_log_raw_data` TEXT,
                    `arm_payment_log_added_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
                    PRIMARY KEY (`arm_payment_log_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_debug_payment_log] = dbDelta($sql_table);

                $tbl_arm_debug_general_log = $wpdb->prefix . 'arm_debug_general_log';
                $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_debug_general_log}`;
                CREATE TABLE IF NOT EXISTS `{$tbl_arm_debug_general_log}`(
                    `arm_general_log_id` int(11) NOT NULL AUTO_INCREMENT,
                    `arm_general_log_event` varchar(255) DEFAULT NULL,
                    `arm_general_log_event_name` varchar(255) DEFAULT NULL,
                    `arm_general_log_event_from` varchar(255) DEFAULT NULL,
                    `arm_general_log_raw_data` TEXT,
                    `arm_general_log_added_date` datetime NOT NULL DEFAULT '1970-01-01 01:00:00',
                    PRIMARY KEY (`arm_general_log_id`)
                ) {$charset_collate};";
                $arm_dbtbl_create[$tbl_arm_debug_general_log] = dbDelta($sql_table);

                /* Install Default Template Forms & Fields */
                $ARMember->install_default_templates();
                $wpdb->query("ALTER TABLE `{$tbl_arm_forms}` AUTO_INCREMENT = 101"); //phpcs:ignore --Reason $tbl_arm_forms is a table name
                /* Install Default Member Forms & Fields. */
                $ARMember->install_member_form_fields();
                /* Install Default Pages. */
                $ARMember->install_default_pages();
                /* Update Page in default template */
                $ARMember->update_default_pages_for_templates();
                /* Create Custom User Role & Capabilities. */
                $ARMember->add_user_role_and_capabilities();

                $arm_access_rules->install_redirection_settings();

                $armember_check_db_permission = $ARMember->armember_check_db_permission();
                if(!empty($armember_check_db_permission))
                {
                    $arm_members_table = $ARMember->tbl_arm_members;
                    $arm_tbl_arm_payment_log = $ARMember->tbl_arm_payment_log;
                    $arm_tbl_arm_debug_payment_log = $ARMember->tbl_arm_debug_payment_log;
                    $arm_tbl_arm_debug_general_log = $ARMember->tbl_arm_debug_general_log;
                    
                    //Add the arm-user-id INDEX for the Members table
                    $arm_members_add_index_arm_user_id = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_members_table." where Key_name=%s ",'arm-user-id') );//phpcs:ignore --Reason $arm_members_table is a table name
                    if(empty($arm_members_add_index_arm_user_id))
                    {
                        $wpdb->query("ALTER TABLE `{$arm_members_table}` ADD INDEX `arm-user-id` (`arm_user_id`)"); //phpcs:ignore --Reason $arm_members_table is a table name
                    }

                    //Add the arm-user-id INDEX for the Payment table
                    $arm_payment_log_add_index_arm_user_id = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_payment_log." where Key_name=%s ",'arm-user-id') );//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                    if(empty($arm_payment_log_add_index_arm_user_id))
                    {
                        $wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}` ADD INDEX `arm-user-id` (`arm_user_id`)");//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                    }

                    //Add the arm-plan-id INDEX for the Payment table
                    $arm_payment_log_add_index_arm_plan_id = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_payment_log." where Key_name=%s ",'arm-plan-id') );//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                    if(empty($arm_payment_log_add_index_arm_plan_id))
                    {
                        $wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}` ADD INDEX `arm-plan-id` (`arm_plan_id`)");//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                    }

                    //Add the arm-paid-post-id INDEX for the Payment table
                    $arm_payment_log_add_index_arm_paid_post_id = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_payment_log." where Key_name=%s ",'arm-paid-post-id') );//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                    if(empty($arm_payment_log_add_index_arm_paid_post_id))
                    {
                        $wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}` ADD INDEX `arm-paid-post-id` (`arm_paid_post_id`)");//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                    }

                    //Add the arm-is-gift-payment INDEX for the Payment table
                    $arm_payment_log_add_index_arm_is_gift_payment = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_payment_log." where Key_name=%s ",'arm-is-gift-payment') );//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                    if(empty($arm_payment_log_add_index_arm_is_gift_payment))
                    {
                        $wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}` ADD INDEX `arm-is-gift-payment` (`arm_is_gift_payment`)");//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                    }

                    //Add the arm-display-log INDEX for the Payment table
                    $arm_payment_log_add_index_arm_display_log = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_payment_log." where Key_name=%s ",'arm-display-log') );//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                    if(empty($arm_payment_log_add_index_arm_display_log))
                    {
                        $wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}` ADD INDEX `arm-display-log` (`arm_display_log`)");//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                    }

                    //Add the arm-debug-payment-log-gateway INDEX for the Payment table
                    $arm_debug_payment_log_add_index_arm_gateway = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_debug_payment_log." where Key_name=%s ",'arm-debug-payment-log-gateway') );//phpcs:ignore --Reason $arm_tbl_arm_debug_payment_log is a table name
                    if(empty($arm_debug_payment_log_add_index_arm_gateway))
                    {
                        $wpdb->query("ALTER TABLE `{$arm_tbl_arm_debug_payment_log}` ADD INDEX `arm-debug-payment-log-gateway` (`arm_payment_log_gateway`)");//phpcs:ignore --Reason $arm_tbl_arm_debug_payment_log is a table name
                    }

                    //Add the arm-debug-payment-log-status INDEX for the Payment table
                    $arm_debug_payment_log_add_index_arm_status = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_debug_payment_log." where Key_name=%s ",'arm-debug-payment-log-status') ); //phpcs:ignore --Reason $arm_tbl_arm_debug_payment_log is a table name
                    if(empty($arm_debug_payment_log_add_index_arm_status))
                    {
                        $wpdb->query("ALTER TABLE `{$arm_tbl_arm_debug_payment_log}` ADD INDEX `arm-debug-payment-log-status` (`arm_payment_log_status`)");//phpcs:ignore --Reason $arm_tbl_arm_debug_payment_log is a table name
                    }

                    //Add the arm-debug-general-log-event INDEX for the general table
                    $arm_debug_general_log_add_index_arm_event = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_debug_general_log." where Key_name=%s ",'arm-debug-general-log-event') );//phpcs:ignore --Reason $arm_tbl_arm_debug_general_log is a table name
                    if(empty($arm_debug_general_log_add_index_arm_event))
                    {
                        $wpdb->query("ALTER TABLE `{$arm_tbl_arm_debug_general_log}` ADD INDEX `arm-debug-general-log-event` (`arm_general_log_event`)");//phpcs:ignore --Reason $arm_tbl_arm_debug_general_log is a table name
                    }

                }
                
                $buddypress_settings_array = array('avatar_map'=> 1, 'profile_cover_map' => 1, 'show_armember_profile' => 0);
                $serialized_buddypress_options = maybe_serialize($buddypress_settings_array);
                update_option('arm_buddypress_options', $serialized_buddypress_options);
                
                /* Plugin Action Hook After Install Process */
                do_action('arm_after_activation_hook');
                do_action('arm_after_install');

                
                
                global $arm_members_activity;
                $arm_members_activity->getwpversion();
            } else {
                
                $ARMember->wpdbfix();
                do_action('arm_reactivate_plugin');
            }
            $args = array(
                'role' => 'administrator',
                'fields' => 'id'
            );
            $users = get_users($args);
            if (count($users) > 0) {
                foreach ($users as $key => $user_id) {
                    $armroles = $ARMember->arm_capabilities();
                    $userObj = new WP_User($user_id);
                    foreach ($armroles as $armrole => $armroledescription) {
                        $userObj->add_cap($armrole);
                    }
                    unset($armrole);
                    unset($armroles);
                    unset($armroledescription);
                }
            }

            $ARMember->install_armember_lite();
        }
    }

    public static function arm_pro_force_check_for_plugin_update( $param = [], $force_update = false ){
        global $wp_version;

        $arm_lite_plugin_version = '';
        if( file_exists( WP_PLUGIN_DIR . '/armember-membership/armember-membership.php' ) ){
            $arm_lite_plugin_data         = get_plugin_data( WP_PLUGIN_DIR . '/armember-membership/armember-membership.php' );
            $arm_lite_plugin_version 	  = $arm_lite_plugin_data['Version'];
        }

        $arm_api_url = 'https://www.arpluginshop.com';
        $args = array(
            'slug' => 'armember-membership',
        );
    
        $request_string = array(
            'body' => array(
                'action' => 'lite_plugin_new_version_check',
                'request' => serialize( $args ),
                'api-key' => md5( ARM_HOME_URL ),
                'is_update' => $force_update,
            ),
            'sslverify' => false,
            'user-agent' => 'ARMLITE-WordPress/'.$wp_version.';'.ARM_HOME_URL
        );
    
        //Start checking for an update
        $raw_response = wp_remote_post( $arm_api_url, $request_string );
    
        if( !is_wp_error( $raw_response ) && ( $raw_response['response']['code'] == 200 ) ){
            $response = @unserialize( $raw_response['body'] );
        }
        
        
        if( isset( $response['access_request'] ) && !empty( $response['access_request'] ) && 'success' == $response['access_request'] ){
            if( isset( $response['access_package'] ) && !empty( $response['access_package'] ) ){
                $update_package = @unserialize( $response['access_package'] );
                if( isset( $update_package ) && is_array( $update_package ) && !empty( $update_package ) ){
                    $version = $update_package['version'];
                    
                    if( !empty( $param ) ){
                        $response_arr = [];
                        foreach( $param as  $post_key ){
                            $response_arr[ $post_key ] = !empty( $update_package[ $post_key ] ) ? $update_package[ $post_key ] : '';
                        }
    
                        return $response_arr;
                    }

                    $current_version = $arm_lite_plugin_version;
                    
                    if( version_compare( $current_version, $version, '>=') ){
                        delete_option( 'arm_show_lite_update_failed_notice' );
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public static function update_armember_lite()
    {
        global $ARMember;
        if ( file_exists( WP_PLUGIN_DIR . '/armember-membership/armember-membership.php' ) ) 
        {
            if ( ! function_exists( 'plugins_api' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            }
            $response                         = plugins_api(
                'plugin_information',
                array(
                    'slug'   => 'armember-membership',
                    'fields' => array(
                        'sections' => false,
                        'versions' => true,
                    ),
                )
            );
            $arm_plugin_data         = get_plugin_data( WP_PLUGIN_DIR . '/armember-membership/armember-membership.php' );
            $arm_plugin_data_version = $arm_plugin_data['Version'];
            if ( ! is_wp_error( $response ) ) {
                $latest_version = ! empty( $response->version ) ? $response->version : '';
            }

            if ( ! empty( $latest_version ) && version_compare( $arm_plugin_data_version, $latest_version, '<' ) ) 
            {
                if ( ! is_wp_error( $response ) && property_exists( $response, 'versions' ) ) {
                    if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                    }
                    $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                    $source   = 'armember-membership/armember-membership.php';

                    if ( ! empty( $source ) ) {
                        if ( $upgrader->upgrade( $source ) === true ) {
                            activate_plugin( 'armember-membership/armember-membership.php' );
                            return true;
                        }
                        else {
                            return false;
                        }
                    }
                }
                else {
                    $package_data = $ARMember->arm_pro_force_check_for_plugin_update( ['version', 'dwlurl'], true );

                    if( !empty( $package_data ) && !empty( $package_data['version'] ) && version_compare( $arm_plugin_data_version, $package_data['version'], '<' ) ){
                        $package_url = !empty( $package_data['dwlurl'] ) ? $package_data['dwlurl'] : '';
                    }

                    if( !empty( $package_url ) ){							
                        if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                        }
                        $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );

                        if ( ! empty( $package_url ) ) {
                            if ( $upgrader->install( $package_url, ['overwrite_package' => true ] ) === true ) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    }
                }
            }
            else if( is_wp_error( $response ) ) 
            {

                $package_data = $ARMember->arm_pro_force_check_for_plugin_update( ['version', 'dwlurl'], true );

                if( !empty( $package_data ) && !empty( $package_data['version'] ) && version_compare( $arm_plugin_data_version, $package_data['version'], '<' ) ){
                    $package_url = !empty( $package_data['dwlurl'] ) ? $package_data['dwlurl'] : '';
                }

                if( !empty( $package_url ) ){
                    if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                    }
                    $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );

                    if ( ! empty( $package_url ) ) {
                        if ( $upgrader->install( $package_url, ['overwrite_package' => true ] ) === true ) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            }
        } else {
            global $ARMember;
            return $ARMember->install_armember_lite();
        }
    }

    public function install_armember_lite()
    {
        global $ARMember;
        if ( ! file_exists( WP_PLUGIN_DIR . '/armember-membership/armember-membership.php' ) ) {
        
            if ( ! function_exists( 'plugins_api' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            }
            $response = plugins_api(
                'plugin_information',
                array(
                    'slug'   => 'armember-membership',
                    'fields' => array(
                        'sections' => false,
                        'versions' => true,
                    ),
                )
            );
            if ( ! is_wp_error( $response ) && property_exists( $response, 'versions' ) ) {
                if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                }
                $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                $source   = ! empty( $response->download_link ) ? $response->download_link : '';
                update_option('arm_lite_download_automatic', 1);

                if ( ! empty( $source ) ) {
                    if ( $upgrader->install( $source ) === true ) {
                        activate_plugin( 'armember-membership/armember-membership.php' );
                        return true;
                    }
                    else {
                        return false;
                    }
                }
            }
            else
            {
                $package_data = $ARMember->arm_pro_force_check_for_plugin_update( ['dwlurl'] );

                if( !empty( $package_data ) && !empty( $package_data['dwlurl'] ) ){
                    
                    $package_url = $package_data['dwlurl'];

                    if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                    }

                    $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );

                    update_option('arm_lite_download_automatic', 1);
                    
                    if ( ! empty( $package_url ) ) {
                        if ( $upgrader->install( $package_url ) === true ) {
                            activate_plugin( 'armember-membership/armember-membership.php' );
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            }
        }
    }

    function armember_check_db_permission()
    {
        global $wpdb;
        $results = $wpdb->get_results("SHOW GRANTS FOR CURRENT_USER;");
        $allowed_index = 0;
        foreach($results as $result)
        {
            if(is_object($result))
            {
                foreach($result as $res)
                {
                    $result_data = stripslashes_deep($res);
                }
            }
            else {
                $result_data = stripslashes_deep($result);
            }
            if( (strpos($result_data, "ALL PRIVILEGES") !== false || strpos($result_data, "INDEX") !== false) && (strpos($result_data, "ON *.*") || strpos($result_data, "`".DB_NAME."`") ) )
            {
                $allowed_index = 1;
                break;
            }
        }
        return $allowed_index;
    }

    function install_default_templates() {
        include(MEMBERSHIP_CLASSES_DIR . '/templates.arm_member_forms_templates.php');
    }
    function arm_update_template_style_armember_5() {
    	global $ARMember,$wpdb;
        $arm_form_style_settings = array(   
            'registration_template1' => array('form_layout' => 'writer','form_width' => '550','form_width_type' => 'px','form_border_width' => '2','form_border_radius' => '12','form_border_style' => 'solid','form_padding_left' => '30','form_padding_top' => '40','form_padding_bottom' => '40','form_padding_right' => '30','form_position' => 'left','form_bg' => '','form_title_font_family' => 'Poppins','form_title_font_size' => '24','form_title_font_bold' => '1','form_title_font_italic' => '0','form_title_font_decoration' => '','form_title_position' => 'center','validation_position' => 'bottom','color_scheme' => 'blue',    'lable_font_color' => '#1A2538','field_font_color' => '#2F3F5C','field_border_color' => '#D3DEF0','field_focus_color' => '#637799','button_back_color' => '#005AEE','button_font_color' => '#FFFFFF','button_hover_color' => '#0D54C9','button_hover_font_color' => '#ffffff', 'form_title_font_color' => '#1A2538','form_bg_color' => "#FFFFFF",'form_border_color' => "#CED4DE",'prefix_suffix_color' => '#bababa','error_font_color' => '#FF3B3B','error_field_border_color' => '#FF3B3B','error_field_bg_color' => '#ffffff',   'field_width' => '100','field_width_type' => '%','field_height' => '44','field_spacing' => '18','field_border_width' => '1','field_border_radius' => '0','field_border_style' => 'solid','field_font_family' => 'Poppins','field_font_size' => '15','field_font_bold' => '0','field_font_italic' => '0','field_font_decoration' => '','field_position' => 'left','rtl' => '0','label_width' => '250','label_width_type' => 'px','label_position' => 'block','label_align' => 'left','label_hide' => '0','label_font_family' => 'Poppins','label_font_size' => '14','description_font_size' => '14','label_font_bold' => '0','label_font_italic' => '0','label_font_decoration' => '','button_width' => '360','button_width_type' => 'px','button_height' => '40','button_height_type' => 'px','button_border_radius' => '6','button_style' => 'border','button_font_family' => 'Poppins','button_font_size' => '15','button_font_bold' => '0','button_font_italic' => '0','button_font_decoration' => '','button_margin_left' => '0','button_margin_top' => '10','button_margin_right' => '0','button_margin_bottom' => '0','button_position' => 'center' ),

            'login_template1' => array('social_btn_position' => 'bottom','social_btn_type' => 'horizontal','social_btn_align' => 'center','enable_social_btn_separator' => '1','social_btn_separator' => '<center>OR</center>','form_layout' => 'writer','form_width' => '550','form_width_type' => 'px','form_border_width' => '2','form_border_radius' => '12','form_border_style' => 'solid','form_padding_left' => '30','form_padding_top' => '40','form_padding_right' => '30','form_padding_bottom' => '40','form_position' => 'left','form_bg' => '','form_title_font_family' => 'Poppins','form_title_font_size' => '24','form_title_font_bold' => '1','form_title_font_italic' => '0','form_title_font_decoration' => '','form_title_position' => 'center','validation_position' => 'bottom','color_scheme' => 'blue',    'lable_font_color' => '#1A2538','field_font_color' => '#2F3F5C','field_border_color' => '#D3DEF0','field_focus_color' => '#637799','button_back_color' => '#005AEE','button_font_color' => '#FFFFFF','button_hover_color' => '#0D54C9','button_hover_font_color' => '#ffffff', 'form_title_font_color' => '#1A2538','form_bg_color' => "#FFFFFF",'form_border_color' => "#CED4DE",'prefix_suffix_color' => '#bababa','error_font_color' => '#FF3B3B','error_field_border_color' => '#FF3B3B','error_field_bg_color' => '#ffffff',   'field_width' => '100','field_width_type' => '%','field_height' => '44','field_spacing' => '18','field_border_width' => '1','field_border_radius' => '0','field_border_style' => 'solid','field_font_family' => 'Poppins','field_font_size' => '15','field_font_bold' => '0','field_font_italic' => '0','field_font_decoration' => '','field_position' => 'left','rtl' => '0','label_width' => '250','label_width_type' => 'px','label_position' => 'block','label_align' => 'left','label_hide' => '0','label_font_family' => 'Poppins','label_font_size' => '14','description_font_size' => '14','label_font_bold' => '0','label_font_italic' => '0','label_font_decoration' => '','button_width' => '360','button_width_type' => 'px','button_height' => '40','button_height_type' => 'px','button_border_radius' => '6','button_style' => 'border','button_font_family' => 'Poppins','button_font_size' => '15','button_font_bold' => '0','button_font_italic' => '0','button_font_decoration' => '','button_margin_left' => '0','button_margin_top' => '10','button_margin_right' => '0','button_margin_bottom' => '0','button_position' => 'center' ),

            'registration_template2' => array('form_layout' => 'writer','form_width' => '550','form_width_type' => 'px','form_border_width' => '2','form_border_radius' => '12','form_border_style' => 'solid','form_padding_left' => '30','form_padding_top' => '40','form_padding_bottom' => '40','form_padding_right' => '30','form_position' => 'left','form_bg' => '','form_title_font_family' => 'Poppins','form_title_font_size' => '24','form_title_font_bold' => '1','form_title_font_italic' => '0','form_title_font_decoration' => '','form_title_position' => 'center','validation_position' => 'bottom','color_scheme' => 'blue',    'lable_font_color' => '#1A2538','field_font_color' => '#2F3F5C','field_border_color' => '#D3DEF0','field_focus_color' => '#637799','button_back_color' => '#005AEE','button_font_color' => '#FFFFFF','button_hover_color' => '#0D54C9','button_hover_font_color' => '#ffffff', 'login_link_font_color' => '#005AEE','register_link_font_color' => '#005AEE','form_title_font_color' => '#1A2538','form_bg_color' => "#FFFFFF",'form_border_color' => "#CED4DE",'prefix_suffix_color' => '#bababa','error_font_color' => '#FF3B3B','error_field_border_color' => '#FF3B3B','error_field_bg_color' => '#ffffff',   'field_width' => '100','field_width_type' => '%','field_height' => '44','field_spacing' => '18','field_border_width' => '1','field_border_radius' => '0','field_border_style' => 'solid','field_font_family' => 'Poppins','field_font_size' => '15','field_font_bold' => '0','field_font_italic' => '0','field_font_decoration' => '','field_position' => 'left','rtl' => '0','label_width' => '250','label_width_type' => 'px','label_position' => 'block','label_align' => 'left','label_hide' => '0','label_font_family' => 'Poppins','label_font_size' => '14','description_font_size' => '14','label_font_bold' => '0','label_font_italic' => '0','label_font_decoration' => '','button_width' => '110','button_width_type' => 'px','button_height' => '100','button_height_type' => 'px','button_border_radius' => '90','button_style' => 'border','button_font_family' => 'Poppins','button_font_size' => '15','button_font_bold' => '0','button_font_italic' => '0','button_font_decoration' => '','button_margin_left' => '0','button_margin_top' => '10','button_margin_right' => '0','button_margin_bottom' => '0','button_position' => 'center' ),

            'login_template2' =>array('form_layout' => 'writer','form_width' => '550','form_width_type' => 'px','form_border_width' => '2','form_border_radius' => '12','form_border_style' => 'solid','form_padding_left' => '30','form_padding_top' => '40','form_padding_right' => '30','form_padding_bottom' => '40','form_position' => 'left','form_bg' => '','form_title_font_family' => 'Poppins','form_title_font_size' => '24','form_title_font_bold' => '1','form_title_font_italic' => '0','form_title_font_decoration' => '','form_title_position' => 'center','validation_position' => 'bottom','color_scheme' => 'blue',    'lable_font_color' => '#1A2538','field_font_color' => '#2F3F5C','field_border_color' => '#D3DEF0','field_focus_color' => '#637799','button_back_color' => '#005AEE','button_font_color' => '#FFFFFF','button_hover_color' => '#0D54C9','button_hover_font_color' => '#ffffff', 'login_link_font_color' => '#005AEE','register_link_font_color' => '#005AEE','form_title_font_color' => '#1A2538','form_bg_color' => "#FFFFFF",'form_border_color' => "#CED4DE",'prefix_suffix_color' => '#bababa','error_font_color' => '#FF3B3B','error_field_border_color' => '#FF3B3B','error_field_bg_color' => '#ffffff',   'field_width' => '100','field_width_type' => '%','field_height' => '44','field_spacing' => '18','field_border_width' => '1','field_border_radius' => '0','field_border_style' => 'solid','field_font_family' => 'Poppins','field_font_size' => '15','field_font_bold' => '0','field_font_italic' => '0','field_font_decoration' => '','field_position' => 'left','rtl' => '0','label_width' => '250','label_width_type' => 'px','label_position' => 'block','label_align' => 'left','label_hide' => '0','label_font_family' => 'Poppins','label_font_size' => '14','description_font_size' => '14','label_font_bold' => '0','label_font_italic' => '0','label_font_decoration' => '','button_width' => '110','button_width_type' => 'px','button_height' => '110','button_height_type' => 'px','button_border_radius' => '90','button_style' => 'border','button_font_family' => 'Poppins','button_font_size' => '15','button_font_bold' => '0','button_font_italic' => '0','button_font_decoration' => '','button_margin_left' => '0','button_margin_top' => '5','button_margin_right' => '0','button_margin_bottom' => '0','button_position' => 'center' ),

            'registration_template3' => array('form_layout' => 'rounded','form_width' => '550','form_width_type' => 'px','form_border_width' => '2','form_border_radius' => '12','form_border_style' => 'solid','form_padding_left' => '30','form_padding_top' => '40','form_padding_bottom' => '40','form_padding_right' => '30','form_position' => 'left','form_bg' => '','form_title_font_family' => 'Poppins','form_title_font_size' => '24','form_title_font_bold' => '1','form_title_font_italic' => '0','form_title_font_decoration' => '','form_title_position' => 'center','validation_position' => 'bottom','color_scheme' => 'red','lable_font_color' => '#1a2538','field_font_color' => '#242424','field_border_color' => '#dbdbdb','field_focus_color' => '#a38ea3','button_back_color' => '#dd2476','button_back_color_gradient' => '#ff512f','button_font_color' => '#ffffff','button_hover_color' => '#dd2476','button_hover_font_color' => '#ffffff','button_hover_color_gradient' => '#ff512f',"login_link_font_color" => '#e65e80',"register_link_font_color" => '#e65e80','form_title_font_color' => '#dd2476','form_bg_color' => '#ffffff','form_border_color' => '#e6e7f5','prefix_suffix_color' => '#997a88','error_font_color' => '#ffffff','error_field_border_color' => '#f05050','error_field_bg_color' => '#e6594d','field_width' => '100','field_width_type' => '%','field_height' => '44','field_spacing' => '8','field_border_width' => '2','field_border_radius' => '40','field_border_style' => 'solid','field_font_family' => 'Poppins','field_font_size' => '15','field_font_bold' => '0','field_font_italic' => '0','field_font_decoration' => '','field_position' => 'left','rtl' => '0','label_width' => '250','label_width_type' => 'px','label_position' => 'block','label_align' => 'left','label_hide' => '1','label_font_family' => 'Poppins','label_font_size' => '14','description_font_size' => '14','label_font_bold' => '0','label_font_italic' => '0','label_font_decoration' => '','button_width' => '180','button_width_type' => 'px','button_height' => '48','button_height_type' => 'px','button_border_radius' => '50','button_style' => 'flat','button_font_family' => 'Poppins','button_font_size' => '15','button_font_bold' => '1','button_font_italic' => '0','button_font_decoration' => '','button_margin_left' => '0','button_margin_top' => '5','button_margin_right' => '0','button_margin_bottom' => '0','button_position' => 'center' ),

            'login_template3' => array('form_layout' => 'rounded','form_width' => '550','form_width_type' => 'px','form_border_width' => '2','form_border_radius' => '12','form_border_style' => 'solid','form_padding_left' => '30','form_padding_top' => '40','form_padding_right' => '30','form_padding_bottom' => '40','form_position' => 'left','form_bg' => '','form_title_font_family' => 'Poppins','form_title_font_size' => '24','form_title_font_bold' => '1','form_title_font_italic' => '0','form_title_font_decoration' => '','form_title_position' => 'center','validation_position' => 'bottom','color_scheme' => 'red','lable_font_color' => '#1a2538','field_font_color' => '#242424','field_border_color' => '#dbdbdb','field_focus_color' => '#a38ea3','button_back_color' => '#dd2476','button_back_color_gradient' => '#ff512f','button_hover_color' => '#dd2476','button_hover_color_gradient' => '#ff512f','button_font_color' => '#ffffff','button_hover_font_color' => '#ffffff',"login_link_font_color" => '#e65e80',"register_link_font_color" => '#e65e80','form_title_font_color' => '#dd2476','form_bg_color' => '#ffffff','form_border_color' => '#e6e7f5','prefix_suffix_color' => '#997a88','error_font_color' => '#ffffff','error_field_border_color' => '#f05050','error_field_bg_color' => '#e6594d','field_width' => '100','field_width_type' => '%','field_height' => '44','field_spacing' => '8','field_border_width' => '2','field_border_radius' => '40','field_border_style' => 'solid','field_font_family' => 'Poppins','field_font_size' => '15','field_font_bold' => '0','field_font_italic' => '0','field_font_decoration' => '','field_position' => 'left','rtl' => '0','label_width' => '250','label_width_type' => 'px','label_position' => 'block','label_align' => 'left','label_hide' => '1','label_font_family' => 'Poppins','label_font_size' => '14','description_font_size' => '14','label_font_bold' => '0','label_font_italic' => '0','label_font_decoration' => '','button_width' => '180','button_width_type' => 'px','button_height' => '48','button_height_type' => 'px','button_border_radius' => '50','button_style' => 'flat','button_font_family' => 'Poppins','button_font_size' => '15','button_font_bold' => '1','button_font_italic' => '0','button_font_decoration' => '','button_margin_left' => '0','button_margin_top' => '5','button_margin_right' => '0','button_margin_bottom' => '0','button_position' => 'center' ),

            'registration_template4' => array('form_layout' => 'iconic','form_width' => '550','form_width_type' => 'px','form_border_width' => '2','form_border_radius' => '12','form_border_style' => 'solid','form_padding_left' => '30','form_padding_top' => '40','form_padding_bottom' => '40','form_padding_right' => '30','form_position' => 'left','form_bg' => '','form_title_font_family' => 'Poppins','form_title_font_size' => '24','form_title_font_bold' => '1','form_title_font_italic' => '0','form_title_font_decoration' => '','form_title_position' => 'center','validation_position' => 'bottom','color_scheme' => 'green','lable_font_color' => '#131a15','field_font_color' => '#242424','field_border_color' => '#e6e6e6','field_focus_color' => '#27c24c','field_bg_color' => '#f0f0f0','button_back_color' => '#27c24c','button_font_color' => '#fcfcfc','button_hover_color' => '#29cc50','button_hover_font_color' => '#ffffff','form_title_font_color' => '#131a15','form_bg_color' => '#ffffff','form_border_color' => '#e6e7f5','prefix_suffix_color' => '#997a88','error_font_color' => '#ffffff','error_field_border_color' => '#f05050','error_field_bg_color' => '#e6594d','login_link_font_color' => '#27c24c','register_link_font_color' => '#27c24c','field_width' => '100','field_width_type' => '%','field_height' => '44','field_spacing' => '8','field_border_width' => '1','field_border_radius' => '6','field_border_style' => 'solid','field_font_family' => 'Poppins','field_font_size' => '15','field_font_bold' => '0','field_font_italic' => '0','field_font_decoration' => '','field_position' => 'left','rtl' => '0','label_width' => '250','label_width_type' => 'px','label_position' => 'block','label_align' => 'left','label_hide' => '1','label_font_family' => 'Poppins','label_font_size' => '14','description_font_size' => '14','label_font_bold' => '0','label_font_italic' => '0','label_font_decoration' => '','button_width' => '360','button_width_type' => 'px','button_height' => '44','button_height_type' => 'px','button_border_radius' => '6','button_style' => 'reverse_border','button_font_family' => 'Poppins','button_font_size' => '15','button_font_bold' => '1','button_font_italic' => '0','button_font_decoration' => '','button_margin_left' => '0','button_margin_top' => '10','button_margin_right' => '0','button_margin_bottom' => '0','button_position' => 'center' ),

            'login_template4' => array('social_btn_position' => 'bottom','social_btn_type' => 'horizontal','social_btn_align' => 'center','enable_social_btn_separator' => '1','social_btn_separator' => '<center>OR</center>','form_layout' => 'iconic','form_width' => '550','form_width_type' => 'px','form_border_width' => '2','form_border_radius' => '12','form_border_style' => 'solid','form_padding_left' => '80','form_padding_top' => '40','form_padding_right' => '80','form_padding_bottom' => '40','form_position' => 'left','form_bg' => '','form_title_font_family' => 'Poppins','form_title_font_size' => '24','form_title_font_bold' => '1','form_title_font_italic' => '0','form_title_font_decoration' => '','form_title_position' => 'center','validation_position' => 'bottom','color_scheme' => 'green','lable_font_color' => '#131a15','field_font_color' => '#242424','field_border_color' => '#e6e6e6','field_focus_color' => '#27c24c','field_bg_color' => '#f0f0f0','button_back_color' => '#27c24c','button_font_color' => '#fcfcfc','button_hover_color' => '#29cc50','button_hover_font_color' => '#ffffff','form_title_font_color' => '#131a15','form_bg_color' => '#ffffff','form_border_color' => '#e6e7f5','prefix_suffix_color' => '#997a88','error_font_color' => '#ffffff','error_field_border_color' => '#f05050','error_field_bg_color' => '#e6594d','login_link_font_color' => '#27c24c','register_link_font_color' => '#27c24c','field_width' => '100','field_width_type' => '%','field_height' => '44','field_spacing' => '8','field_border_width' => '1','field_border_radius' => '6','field_border_style' => 'solid','field_font_family' => 'Poppins','field_font_size' => '15','field_font_bold' => '0','field_font_italic' => '0','field_font_decoration' => '','field_position' => 'center','rtl' => '0','label_width' => '250','label_width_type' => 'px','label_position' => 'block','label_align' => 'left','label_hide' => '1','label_font_family' => 'Poppins','label_font_size' => '14','description_font_size' => '14','label_font_bold' => '0','label_font_italic' => '0','label_font_decoration' => '','button_width' => '360','button_width_type' => 'px','button_height' => '44','button_height_type' => 'px','button_border_radius' => '6','button_style' => 'reverse_border','button_font_family' => 'Poppins','button_font_size' => '15','button_font_bold' => '1','button_font_italic' => '0','button_font_decoration' => '','button_margin_left' => '0','button_margin_top' => '10','button_margin_right' => '0','button_margin_bottom' => '0','button_position' => 'center'),

            'registration_template5' => array('form_layout' => 'iconic','form_width' => '550','form_width_type' => 'px','form_border_width' => '2','form_border_radius' => '12','form_border_style' => 'solid','form_padding_left' => '30','form_padding_top' => '40','form_padding_bottom' => '40','form_padding_right' => '30','form_position' => 'left','form_bg' => '','form_title_font_family' => 'Poppins','form_title_font_size' => '24','form_title_font_bold' => '1','form_title_font_italic' => '0','form_title_font_decoration' => '','form_title_position' => 'center','validation_position' => 'bottom','color_scheme' => 'purple','lable_font_color' => '#919191','field_font_color' => '#242424','field_border_color' => '#c7c7c7','field_focus_color' => '#6164c1','field_bg_color' => '#ffffff','button_back_color' => '#6164c1','button_font_color' => '#ffffff','button_hover_color' => '#8072cc','button_hover_font_color' => '#ffffff','form_title_font_color' => '#313131','form_bg_color' => '#ffffff','form_border_color' => '#CED4DE','prefix_suffix_color' => '#bababa','error_font_color' => '#ffffff','error_field_border_color' => '#f05050','error_field_bg_color' => '#e6594d','login_link_font_color' => '#27c24c','register_link_font_color' => '#27c24c','field_width' => '100','field_width_type' => '%','field_height' => '44','field_spacing' => '12','field_border_width' => '1','field_border_radius' => '6','field_border_style' => 'solid','field_font_family' => 'Poppins','field_font_size' => '15','field_font_bold' => '0','field_font_italic' => '0','field_font_decoration' => '','field_position' => 'left','rtl' => '0','label_width' => '250','label_width_type' => 'px','label_position' => 'block','label_align' => 'left','label_hide' => '0','label_font_family' => 'Poppins','label_font_size' => '14','description_font_size' => '14','label_font_bold' => '0','label_font_italic' => '0','label_font_decoration' => '','button_width' => '240','button_width_type' => 'px','button_height' => '44','button_height_type' => 'px','button_border_radius' => '6','button_style' => 'classic','button_font_family' => 'Poppins','button_font_size' => '15','button_font_bold' => '1','button_font_italic' => '0','button_font_decoration' => '','button_margin_left' => '0','button_margin_top' => '20','button_margin_right' => '0','button_margin_bottom' => '10','button_position' => 'center' ),

            'login_template5' => array('social_btn_position' => 'bottom','social_btn_type' => 'horizontal','social_btn_align' => 'center','enable_social_btn_separator' => '1','social_btn_separator' => '<center>OR</center>','form_layout' => 'iconic','form_width' => '550','form_width_type' => 'px','form_border_width' => '2','form_border_radius' => '12','form_border_style' => 'solid','form_padding_left' => '30','form_padding_top' => '40','form_padding_right' => '30','form_padding_bottom' => '40','form_position' => 'left','form_bg' => '','form_title_font_family' => 'Poppins','form_title_font_size' => '24','form_title_font_bold' => '1','form_title_font_italic' => '0','form_title_font_decoration' => '','form_title_position' => 'center','validation_position' => 'bottom','color_scheme' => 'purple','lable_font_color' => '#919191','field_font_color' => '#242424','field_border_color' => '#c7c7c7','field_focus_color' => '#6164c1','field_bg_color' => '#ffffff','button_back_color' => '#6164c1','button_font_color' => '#ffffff','button_hover_color' => '#8072cc','button_hover_font_color' => '#ffffff','form_title_font_color' => '#313131','form_bg_color' => '#ffffff','form_border_color' => '#CED4DE','prefix_suffix_color' => '#bababa','error_font_color' => '#ffffff','error_field_border_color' => '#f05050','error_field_bg_color' => '#e6594d','login_link_font_color' => '#6164c1','register_link_font_color' => '#6164c1','field_width' => '100','field_width_type' => '%','field_height' => '44','field_spacing' => '12','field_border_width' => '1','field_border_radius' => '6','field_border_style' => 'solid','field_font_family' => 'Poppins','field_font_size' => '15','field_font_bold' => '0','field_font_italic' => '0','field_font_decoration' => '','field_position' => 'left','rtl' => '0','label_width' => '250','label_width_type' => 'px','label_position' => 'block','label_align' => 'left','label_hide' => '0','label_font_family' => 'Poppins','label_font_size' => '14','description_font_size' => '14','label_font_bold' => '0','label_font_italic' => '0','label_font_decoration' => '','button_width' => '240','button_width_type' => 'px','button_height' => '44','button_height_type' => 'px','button_border_radius' => '6','button_style' => 'classic','button_font_family' => 'Poppins','button_font_size' => '15','button_font_bold' => '1','button_font_italic' => '0','button_font_decoration' => '','button_margin_left' => '0','button_margin_top' => '10','button_margin_right' => '0','button_margin_bottom' => '0','button_position' => 'center' ),
        );   
        
	$arm_update_form_style_settings = array('template-registration','template-login','template-forgot-password','template-change-password', 'template-registration-2','template-login-2','template-forgot-password-2', 'template-change-password-2', 'template-registration-3','template-login-3','template-forgot-password-3', 'template-change-password-3', 'template-registration-4','template-login-4','template-forgot-password-4', 'template-change-password-4','template-registration-5','template-login-5','template-forgot-password-5', 'template-change-password-5');

	    if(!empty($arm_form_style_settings)) {
	        foreach($arm_update_form_style_settings as $key => $value) {
	            if(!empty($value)) {      
	                $arm_get_all_form_settings = $wpdb->get_results( $wpdb->prepare("SELECT `arm_form_settings` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_slug`=%s AND `arm_is_default` = %d ",$value,1), ARRAY_A); //phpcs:ignore --Reason $ARMember->tbl_arm_forms is a table name
	                if(!empty($arm_get_all_form_settings[0]['arm_form_settings']) ) {
	                    $arm_form_settings= maybe_unserialize($arm_get_all_form_settings[0]['arm_form_settings']);            
	                    if(!empty($arm_form_settings['style'])) {  
	                        if($value == 'template-registration') {
	                            $arm_form_settings['style'] = $arm_form_style_settings['registration_template1'];                    
	                        }elseif($value == 'template-login' || $value == 'template-change-password' || $value == 'template-forgot-password'){                    
	                            $arm_form_settings['style'] = $arm_form_style_settings['login_template1'];                        
	                        }elseif($value == 'template-registration-5') {
	                            $arm_form_settings['style'] = $arm_form_style_settings['registration_template5'];    
	                        }elseif($value == 'template-login-5' || $value == 'template-change-password-2' || $value == 'template-forgot-password-2') {
	                            $arm_form_settings['style'] = $arm_form_style_settings['login_template2'];                        
	                        }
                            elseif($value == 'template-registration-3') {
	                            $arm_form_settings['style'] = $arm_form_style_settings['registration_template3'];
	                        }elseif($value == 'template-login-3' || $value == 'template-change-password-3' || $value == 'template-forgot-password-3') {
	                            $arm_form_settings['style'] = $arm_form_style_settings['login_template3'];                        
	                        }
                            elseif($value == 'template-registration-4') {
	                            $arm_form_settings['style'] = $arm_form_style_settings['registration_template4'];
	                        }elseif($value == 'template-login-4' || $value == 'template-change-password-4' || $value == 'template-forgot-password-4') {
	                            $arm_form_settings['style'] = $arm_form_style_settings['login_template4'];
	                        }
                            elseif($value == 'template-registration-5') {
	                            $arm_form_settings['style'] = $arm_form_style_settings['registration_template5'];
	                        }elseif($value == 'template-login-5' || $value == 'template-change-password-5' || $value == 'template-forgot-password-5') {
	                            $arm_form_settings['style'] = $arm_form_style_settings['login_template5'];
	                        }
	                        $update_id = $wpdb->update($ARMember->tbl_arm_forms, array('arm_form_settings' => maybe_serialize($arm_form_settings)), array('arm_form_slug' => $value,'arm_is_default' => '1'));
	                    }
	                }
	            }    
	        }
	    }
    }

    function update_default_pages_for_templates() {
        global $wpdb, $ARMember;
        $global_settings = get_option('arm_global_settings');
        $arm_settings = maybe_unserialize($global_settings);
        $page_settings = $arm_settings['page_settings'];
        $template_slugs_query = $wpdb->prepare(" WHERE (`arm_form_slug` LIKE %s OR `arm_form_slug` LIKE %s OR `arm_form_slug` LIKE %s OR `arm_form_slug` LIKE %s) AND arm_is_template = %d",'template-login%','template-registration%','template-forgot%','template-change%',1);
        $forms = $wpdb->get_results("SELECT * FROM `" . $ARMember->tbl_arm_forms . "` {$template_slugs_query}"); //phpcs:ignore --Reason $ARMember->tbl_arm_forms is a table name
        if (count($forms) > 0) {
            foreach ($forms as $key => $value) {
                $form_id = $value->arm_form_id;
                $form_settings = maybe_unserialize($value->arm_form_settings);
                $form_settings['redirect_page'] = $page_settings['edit_profile_page_id'];
                $form_settings['registration_link_type_page'] = $page_settings['register_page_id'];
                $form_settings['forgot_password_link_type_page'] = $page_settings['forgot_password_page_id'];
                $form_settings = maybe_serialize($form_settings);
                $formData = array('arm_form_settings' => $form_settings);
                $form_update = $wpdb->update($ARMember->tbl_arm_forms, $formData, array('arm_form_id' => $form_id));
            }
        }
    }

    function arm_install_plugin_data() {
        global $wp, $wpdb, $arm_members_directory, $arm_access_rules, $arm_email_settings, $arm_subscription_plans, $arm_members_badges;
        $is_activate = get_option('arm_plugin_activated', 0);
        if ($is_activate == '1') {
            delete_option('arm_plugin_activated');
            /**
             * Install Plugin Default Data For The First Time.
             */
            /* Create Free Plan. */
            $arm_subscription_plans->arm_insert_sample_subscription_plan();
            /* Install default templates */
            $arm_email_settings->arm_insert_default_email_templates();
            /* Install Default Profile Template */
            $arm_members_directory->arm_insert_default_member_templates();
            /* Install default badges */
            $arm_members_badges->arm_insert_default_badges();
            /* Install Default Rules */
            $arm_access_rules->install_rule_data();
            
           
        }


    

    }

    /**
     * Add Custom User Role & Capabilities
     */
    function add_user_role_and_capabilities() {
        global $wp, $wpdb, $wp_roles, $ARMember, $arm_members_class, $arm_global_settings;
        $role_name = "ARMember";
        $role_slug = sanitize_title($role_name);
        $basic_caps = array(
            $role_slug => true,
            'read' => true,
            'level_0' => true,
        );

        $wp_roles->add_role($role_slug, $role_name, $basic_caps);
        $arm_user_role = $wp_roles->get_role($role_slug);

        $wpdb->query("DELETE FROM `$ARMember->tbl_arm_members`"); //phpcs:ignore --Reason $ARMember->tbl_arm_members is a table name and query without where clause

        $user_table = $wpdb->users;
        $usermeta_table = $wpdb->usermeta;
        if (is_multisite()) {
            $capability_column = $wpdb->get_blog_prefix($GLOBALS['blog_id']) . 'capabilities';
            $query_to_get_remainig_users = $wpdb->prepare("SELECT * FROM `{$user_table}` u INNER JOIN `{$usermeta_table}` um  ON u.ID = um.user_id WHERE 1=1 AND um.meta_key = %s",$capability_column); //phpcs:ignore --Reason $user_table is a table name
        } else {
            $query_to_get_remainig_users = "SELECT * FROM $wpdb->users";
        }
        $allMembers = $wpdb->get_results($query_to_get_remainig_users);//phpcs:ignore --Reason $query_to_get_remainig_users is a query name
        $chunk_size = 100;
        if (!empty($allMembers)) {

            $arm_total_users = count($allMembers);

            if ($arm_total_users <= 15000) {
                $chunk_size = 100;
            } else if ($arm_total_users > 15000 && $arm_total_users <= 25000) {
                $chunk_size = 200;
            } else if ($arm_total_users > 25000 && $arm_total_users <= 50000) {
                $chunk_size = 300;
            } else if ($arm_total_users > 50000 && $arm_total_users <= 100000) {
                $chunk_size = 400;
            } else {
                $chunk_size = 500;
            }

            $i = 0;
            $chunked_values = '';
            foreach ($allMembers as $member) {
                $i++;
                $user_id = $member->ID;
                $arm_user_id = $user_id;
                $arm_user_login = $member->user_login;
                $arm_user_nicename = $member->user_nicename;
                $arm_user_email = $member->user_email;
                $arm_user_url = $member->user_url;
                $arm_user_registered = $member->user_registered;
                $arm_user_activation_key = $member->user_activation_key;
                $arm_user_status = $member->user_status;
                $arm_display_name = $member->display_name;
                $arm_user_type = 0;
                $arm_primary_status = 1;
                $arm_secondary_status = 0;
                if ($i == 1) {
                    $chunked_values .= "(" . $arm_user_id . ",\"" . $arm_user_login . "\",\"" . $arm_user_nicename . "\",\"" . $arm_user_email . "\",\"\",\"" . $arm_user_registered . "\",\"" . $arm_user_activation_key . "\"," . $arm_user_status . ",\"" . $arm_display_name . "\",0,1,0)";
                } else {
                    $chunked_values .= ",(" . $arm_user_id . ",\"" . $arm_user_login . "\",\"" . $arm_user_nicename . "\",\"" . $arm_user_email . "\",\"\",\"" . $arm_user_registered . "\",\"" . $arm_user_activation_key . "\"," . $arm_user_status . ",\"" . $arm_display_name . "\",0,1,0)";
                }
                if ($i == $chunk_size && (!empty($chunked_values) || $chunked_values != '')) {
                    $wpdb->query('INSERT INTO `' . $ARMember->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values);//phpcs:ignore -- Reason $ARMember->tbl_arm_members is a table name
                    $i = 0;
                    $chunked_values = '';
                }
            }
            if (!empty($chunked_values) || $chunked_values != '') {
                $wpdb->query('INSERT INTO `' . $ARMember->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values);//phpcs:ignore --Reason $ARMember->tbl_arm_members is a table name
            }
        }
    }

    /**
     * Check and Add Custom User Role & Capabilities for new users - after plugin reactivation
     */
    
     function check_new_users_after_plugin_reactivation() {

        global $wpdb, $ARMember;
        $user_table = $wpdb->users;
        $usermeta_table = $wpdb->usermeta;

        $get_all_armembers = $wpdb->get_results("select * from $ARMember->tbl_arm_members", ARRAY_A); //phpcs:ignore --Reason $ARMember->tbl_arm_members is a table name and query without where clause
        $push_user_ids = array();
        $where = "WHERE 1=1";
        $where1 = '';
        foreach ($get_all_armembers as $new_user_id) {
            $push_user_ids[] = $new_user_id['arm_user_id'];
        }
        if (!empty($push_user_ids)) {
            if (is_multisite()) {
                $where1 = " AND u.ID NOT IN (" . implode(", ", $push_user_ids) . ") "; //phpcs:ignore
            } else {
                $where .= " AND `ID` NOT IN (" . implode(", ", $push_user_ids) . ") "; //phpcs:ignore
            }
        }

        if (is_multisite()) {
            $capability_column = $wpdb->get_blog_prefix($GLOBALS['blog_id']) . 'capabilities';
            $query_to_get_remainig_users = "SELECT * FROM `{$user_table}` u INNER JOIN `{$usermeta_table}` um  ON u.ID = um.user_id WHERE 1=1 AND um.meta_key = '{$capability_column}' {$where1}";
        } else {
            $query_to_get_remainig_users = "SELECT * FROM $wpdb->users {$where}";
        }
        
        $list_to_include_new_users = $wpdb->get_results($query_to_get_remainig_users, ARRAY_A);//phpcs:ignore --Reason $user and $query_to_get_remainig_users is table name

        if (!empty($list_to_include_new_users)) {

            $arm_total_users = count($list_to_include_new_users);

            if ($arm_total_users <= 15000) {
                $chunk_size = 100;
            } else if ($arm_total_users > 15000 && $arm_total_users <= 25000) {
                $chunk_size = 200;
            } else if ($arm_total_users > 25000 && $arm_total_users <= 50000) {
                $chunk_size = 300;
            } else if ($arm_total_users > 50000 && $arm_total_users <= 100000) {
                $chunk_size = 400;
            } else {
                $chunk_size = 500;
            }

            $chunked_values = '';
            $i = 0;
            foreach ($list_to_include_new_users as $key => $new_users_data) {
                $i++;
                $arm_user_id = $new_users_data['ID'];
                $arm_user_login = $new_users_data['user_login'];
                $arm_user_nicename = $new_users_data['user_nicename'];
                $arm_user_email = $new_users_data['user_email'];
                $arm_user_url = $new_users_data['user_url'];
                $arm_user_registered = $new_users_data['user_registered'];
                $arm_user_activation_key = $new_users_data['user_activation_key'];
                $arm_user_status = $new_users_data['user_status'];
                $arm_display_name = $new_users_data['display_name'];
                $arm_user_type = 0;
                $arm_primary_status = 1;
                $arm_secondary_status = 0;
                if ($i == 1) {
                    $chunked_values .= "(" . $arm_user_id . ",\"" . $arm_user_login . "\",\"" . $arm_user_nicename . "\",\"" . $arm_user_email . "\",\"\",\"" . $arm_user_registered . "\",\"" . $arm_user_activation_key . "\"," . $arm_user_status . ",\"" . $arm_display_name . "\",0,1,0)";
                } else {
                    $chunked_values .= ",(" . $arm_user_id . ",\"" . $arm_user_login . "\",\"" . $arm_user_nicename . "\",\"" . $arm_user_email . "\",\"\",\"" . $arm_user_registered . "\",\"" . $arm_user_activation_key . "\"," . $arm_user_status . ",\"" . $arm_display_name . "\",0,1,0)";
                }
                if ($i == $chunk_size && $chunked_values != '') {
                    $wpdb->query('INSERT INTO `' . $ARMember->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values);//phpcs:ignore --Reason $ARMember->tbl_arm_members is a table name
                    $i = 0;
                    $chunked_values = '';
                }
            }


            if (!empty($chunked_values) || $chunked_values != '') {
                $wpdb->query('INSERT INTO `' . $ARMember->tbl_arm_members . '` (arm_user_id, arm_user_login, arm_user_nicename, arm_user_email, arm_user_url,arm_user_registered, arm_user_activation_key, arm_user_status,arm_display_name, arm_user_type, arm_primary_status,arm_secondary_status) VALUES ' . $chunked_values);//phpcs:ignore --Reason $ARMember->tbl_arm_members is a table name
            }
        }
    }

    /**
     * Install Default Member Forms & thier fields into Database
     */
    function install_member_form_fields() {
        global $wp, $wpdb, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
        /* Add Default Preset Fields */
        $defaultFields = $arm_member_forms->arm_default_preset_user_fields();
        unset($defaultFields['social_fields']);
        $defaultPresetFields = array('default' => $defaultFields);
        update_option('arm_preset_form_fields', $defaultPresetFields);
        /* Add Default Forms */
        $tbl_arm_forms = $wpdb->prefix . 'arm_forms';
        $tbl_arm_form_field = $wpdb->prefix . 'arm_form_field';

        $default_member_forms_data = $arm_member_forms->arm_default_member_forms_data();
        $insertedFields = array();
        foreach ($default_member_forms_data as $key => $val) {
            $arm_set_id = 0;
            $arm_set_name = '';
            if (in_array($key, array('login', 'forgot_password', 'change_password'))) {
                $arm_set_name = esc_html__('Default Set', 'ARMember');
                $arm_set_id = 1;
            }
            $form_data = array(
                'arm_form_label' => $val['name'],
                'arm_form_title' => $val['name'],
                'arm_form_type' => $key,
                'arm_form_slug' => $val['form_slug'],
                'arm_is_default' => '1',
                'arm_set_name' => $arm_set_name,
                'arm_set_id' => $arm_set_id,
                'arm_ref_template' => '1',
                'arm_form_updated_date' => date('Y-m-d H:i:s'),
                'arm_form_created_date' => date('Y-m-d H:i:s'),
                'arm_form_settings' => maybe_serialize($val['settings'])
            );
            /* Insert Form Data */
            $wpdb->insert($tbl_arm_forms, $form_data);
            $form_id = $wpdb->insert_id;
            if (!empty($val['fields'])) {
                $i = 1;
                foreach ($val['fields'] as $field) {
                    $fid = isset($field['id']) ? $field['id'] : $field['meta_key'];
                    if ($fid == 'repeat_pass') {
                        $field['ref_field_id'] = $insertedFields[$key]['user_pass'];
                    }
                    $form_field_data = array(
                        'arm_form_field_form_id' => $form_id,
                        'arm_form_field_order' => $i,
                        'arm_form_field_slug' => isset($field['meta_key']) ? $field['meta_key'] : '',
                        'arm_form_field_created_date' => date('Y-m-d H:i:s'),
                        'arm_form_field_option' => maybe_serialize($field)
                    );
                    /* Insert Form Fields. */
                    $wpdb->insert($tbl_arm_form_field, $form_field_data);
                    $insert_field_id = $wpdb->insert_id;
                    $insertedFields[$key][$fid] = $insert_field_id;
                    $i++;
                }
            }
        }
    }

    /**
     * Install Default Plugin Pages into Database
     */
    function install_default_pages() {
        global $wp, $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
        /* Default Global Settings */
        $arm_settings = $arm_global_settings->arm_default_global_settings();
        /* Default Pages */
        $arm_pages = $arm_global_settings->arm_default_pages_content();
        if (!empty($arm_pages)) {
            foreach ($arm_pages as $pageIDKey => $page) {
                $page_id = wp_insert_post($page);
                if ($page_id != 0) {
                    $arm_settings['page_settings'][$pageIDKey] = $page_id;
                }
            }
        }
        /* Store Global Setting into DB */
        if (!empty($arm_settings)) {
            $new_global_settings = maybe_serialize($arm_settings);
            update_option('arm_global_settings', $new_global_settings);
            /**
             * Update Redirection pages in member forms
             */
            $allForms = $arm_member_forms->arm_get_all_member_forms('`arm_form_id`, `arm_form_type`, `arm_form_settings`');
            if (!empty($allForms)) {
                foreach ($allForms as $form) {
                    $form_id = $form['arm_form_id'];
                    $form_settings = $form['arm_form_settings'];
                    $isFormUpdate = false;
                    switch ($form['arm_form_type']) {
                        case 'registration':
                            $isFormUpdate = true;
                            $form_settings['redirect_type'] = 'page';
                            $form_settings['redirect_page'] = $arm_settings['page_settings']['edit_profile_page_id'];
                            break;
                        case 'login':
                            $isFormUpdate = true;
                            $form_settings['redirect_type'] = 'page';
                            $form_settings['redirect_page'] = $arm_settings['page_settings']['edit_profile_page_id'];
                            $form_settings['registration_link_type'] = 'page';
                            $form_settings['registration_link_type_page'] = $arm_settings['page_settings']['register_page_id'];
                            $form_settings['forgot_password_link_type_page'] = $arm_settings['page_settings']['forgot_password_page_id'];
                            break;
                    }
                    if ($isFormUpdate) {
                        $formData = array('arm_form_settings' => maybe_serialize($form_settings));
                        $form_update = $wpdb->update($ARMember->tbl_arm_forms, $formData, array('arm_form_id' => $form_id));
                    }
                }
            }
        }
        /* Update Security Settings */
        $securitySettings = $arm_global_settings->arm_get_all_block_settings();
        update_option('arm_block_settings', $securitySettings);
    }

    public static function uninstall() {
        global $wpdb;
        $armember_uninstall = false;
        if(!file_exists(WP_PLUGIN_DIR.'/armember-membership/armember-membership.php')){
            $armember_uninstall = true;
        }
        else{
            $armemberlite_version = get_option('armlite_version'); 
            if (empty($armemberlite_version) || $armemberlite_version == '') {
                $armember_uninstall = true;
            }
        }
        if (is_multisite()) {
            $blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
            if ($blogs) {
                foreach ($blogs as $blog) {
                    switch_to_blog($blog['blog_id']);
                    delete_option('arm_version');
                    self::arm_uninstall( $armember_uninstall );

                    delete_option('arm_is_user_private_content_feature');
                    delete_option('arm_is_social_login_feature');
                    delete_option('arm_is_pro_ration_feature');
                    delete_option('arm_is_drip_content_feature');
                    //delete_option('arm_is_opt_ins_feature');
                    delete_option('arm_is_coupon_feature');
                    delete_option('arm_is_buddypress_feature');
                    delete_option('arm_is_invoice_tax_feature');
                    delete_option('arm_is_multiple_membership_feature');
                    delete_option('arm_is_gutenberg_block_restriction_feature');
                    delete_option('arm_is_mycred_feature');
                    delete_option('arm_is_woocommerce_feature');
                    delete_option('arm_is_pay_per_post_feature');
                    delete_option('arm_is_plan_limit_feature');
                    delete_option('arm_is_beaver_builder_restriction_feature');
                    delete_option('arm_is_divi_builder_restriction_feature');
                    delete_option('arm_is_wpbakery_page_builder_restriction_feature');
                    delete_option('arm_is_fusion_builder_restriction_feature');
                    delete_option('arm_is_oxygen_builder_restriction_feature');
                    delete_option('arm_is_siteorigin_builder_restriction_feature');
                    delete_option('arm_is_bricks_builder_restriction_feature');
                    
                }
                restore_current_blog();
            }
        } else {
            self::arm_uninstall( $armember_uninstall );
            delete_option('arm_is_user_private_content_feature');
            delete_option('arm_is_social_login_feature');
            delete_option('arm_is_pro_ration_feature');
            delete_option('arm_is_drip_content_feature');
            //delete_option('arm_is_opt_ins_feature');
            delete_option('arm_is_coupon_feature');
            delete_option('arm_is_buddypress_feature');
            delete_option('arm_is_invoice_tax_feature');
            delete_option('arm_is_multiple_membership_feature');
            delete_option('arm_is_gutenberg_block_restriction_feature');
            delete_option('arm_is_mycred_feature');
            delete_option('arm_is_woocommerce_feature');
            delete_option('arm_is_pay_per_post_feature');
            delete_option('arm_is_plan_limit_feature');
	    delete_option('arm_is_beaver_builder_restriction_feature');
	    delete_option('arm_is_divi_builder_restriction_feature');
	    delete_option('arm_is_wpbakery_page_builder_restriction_feature');
	    delete_option('arm_is_fusion_builder_restriction_feature');
	    delete_option('arm_is_oxygen_builder_restriction_feature');
	    delete_option('arm_is_siteorigin_builder_restriction_feature');
	    delete_option('arm_is_bricks_builder_restriction_feature');
        }
        /* Plugin Action Hook After Uninstall Process */
        do_action('arm_after_uninstall');
    }

    public static function arm_uninstall( $armember_uninstall = true ) {
        global $wpdb, $arm_members_class;
        /**
         * To Cancel User's Recurring Subscription from Payment Gateway
         */

        $select_member_users = "SELECT arm_user_id FROM ". $wpdb->prefix . 'arm_members';
        $query_member_users = $wpdb->get_results($select_member_users); //phpcs:ignore --Reason $select_member_users is a query without where clause
        if(!empty($query_member_users))
        {
            foreach ($query_member_users as $query_member_user) {
                $chk_subscription_arm_user_id = $query_member_user->arm_user_id;
                $arm_members_class->arm_before_delete_user_action($chk_subscription_arm_user_id);
            }
        }

        delete_option("armIsSorted");
        delete_option("armSortOrder");
        delete_option("armSortId");
        delete_option("armSortInfo");
        delete_option("armBadgeUpdated");
        delete_option("armIsBadgeUpdated");
        delete_option('arm_badgeupdaterequired');
        delete_option("arm_new_version_installed");

        delete_site_option("armIsSorted");
        delete_site_option("armSortOrder");
        delete_site_option("armSortId");
        delete_site_option("armSortInfo");
        delete_site_option("armBadgeUpdated");
        delete_site_option("armIsBadgeUpdated");
        delete_site_option('arm_badgeupdaterequired');		
        delete_site_option("arm_version_1_7_installed");

        if( $armember_uninstall )
        {
            /**
             * Delete Meta Values
             */
            $wpdb->query( $wpdb->prepare("DELETE FROM `" . $wpdb->options . "` WHERE  `option_name` LIKE  %s",'%arm_%')); //phpcs:ignore --Reason $wpdb->options is a table name
            $wpdb->query( $wpdb->prepare("DELETE FROM `" . $wpdb->postmeta . "` WHERE  `meta_key` LIKE  %s",'%arm_%') ); //phpcs:ignore --Reason $wpdb->options is a table name
            $wpdb->query( $wpdb->prepare("DELETE FROM `" . $wpdb->usermeta . "` WHERE  `meta_key` LIKE  %s",'%arm_%') ); //phpcs:ignore --Reason $wpdb->options is a table name

            /**
             * Delete Plugin DB Tables
             */
            $blog_tables = array(
                $wpdb->prefix . 'arm_activity',
                $wpdb->prefix . 'arm_auto_message',
                $wpdb->prefix . 'arm_coupons',
                $wpdb->prefix . 'arm_email_templates',
                $wpdb->prefix . 'arm_entries',
                $wpdb->prefix . 'arm_fail_attempts',
                $wpdb->prefix . 'arm_forms',
                $wpdb->prefix . 'arm_form_field',
                $wpdb->prefix . 'arm_lockdown',
                $wpdb->prefix . 'arm_members',
                $wpdb->prefix . 'arm_membership_setup',
                $wpdb->prefix . 'arm_payment_log',
                $wpdb->prefix . 'arm_payment_log_temp',
                $wpdb->prefix . 'arm_bank_transfer_log',
                $wpdb->prefix . 'arm_subscription_plans',
                $wpdb->prefix . 'arm_termmeta',
                $wpdb->prefix . 'arm_member_templates',
                $wpdb->prefix . 'arm_drip_rules',
                $wpdb->prefix . 'arm_badges_achievements',
                $wpdb->prefix . 'arm_login_history',
                $wpdb->prefix . 'arm_debug_payment_log',
                $wpdb->prefix . 'arm_debug_general_log',
                $wpdb->prefix . 'arm_dripped_contents',
            );
            foreach ($blog_tables as $table) {
                $wpdb->query( "DROP TABLE IF EXISTS ".$table );//phpcs:ignore --Reason: $table is a table name. False Positive Alarm
            }
        }
        return true;
    }

    /**
     * Get Current Browser Info
     */
    function getBrowser($user_agent) {
        $u_agent = $user_agent;
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";
        $ub = "";
        /* First get the platform? */
        if (@preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (@preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (@preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        /* Next get the name of the useragent yes seperately and for good reason */
        if (@preg_match('/MSIE/i', $u_agent) && !@preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (@preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (@preg_match('/OPR/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "OPR";
        } elseif (@preg_match('/Edg/i', $u_agent)) {
            $bname = 'Microsoft Edge';
            $ub = "Edg";
        } elseif (@preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (@preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (@preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (@preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        } elseif (@preg_match('/Trident/', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "rv";
        }
        /* finally get the correct version number */
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ |:]+(?<version>[0-9.|a-zA-Z.]*)#';

        if (!@preg_match_all($pattern, $u_agent, $matches)) {
            /* we have no matching number just continue */
        }

        /* see how many we have */
        $i = count($matches['browser']);
        if ($i != 1) {
            /* we will have two since we are not using 'other' argument yet */
            /* see if version is before or after the name */
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        /* check if we have a number */
        if ($version == null || $version == "") {
            $version = "?";
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

    /**
     * Get Current IP Address of User/Guest
     */
    function arm_get_ip_address() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP']; //phpcs:ignore
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR']; //phpcs:ignore
        } else if (isset($_SERVER['HTTP_X_FORWARDED']) && !empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED']; //phpcs:ignore
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR']) && !empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR']; //phpcs:ignore
        } else if (isset($_SERVER['HTTP_FORWARDED']) && !empty($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED']; //phpcs:ignore
        } else if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR']; //phpcs:ignore
        } else {
            $ipaddress = 'UNKNOWN';
        }
        /* For Public IP Address. */
        /* $publicIP = trim(shell_exec("dig +short myip.opendns.com @resolver1.opendns.com")); */
        return $ipaddress;
    }

    function arm_write_response($response_data, $file_name = '') {
        global $wp, $wpdb, $wp_filesystem;
        if (!empty($file_name)) {
            $file_path = MEMBERSHIP_DIR . '/log/' . $file_name;
        } else {
            $file_path = MEMBERSHIP_DIR . '/log/response.txt';
        }
        if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            if (false === ($creds = request_filesystem_credentials($file_path, '', false, false) )) {
                /**
                 * if we get here, then we don't have credentials yet,
                 * but have just produced a form for the user to fill in,
                 * so stop processing for now
                 */
                return true; /* stop the normal page form from displaying */
            }
            /* now we have some credentials, try to get the wp_filesystem running */
            if (!WP_Filesystem($creds)) {
                /* our credentials were no good, ask the user for them again */
                request_filesystem_credentials($file_path, $method, true, false);
                return true;
            }
            @$file_data = $wp_filesystem->get_contents($file_path);
            $file_data .= $response_data;
            $file_data .= "\r\n===========================================================================\r\n";
            $breaks = array("<br />", "<br>", "<br/>");
            $file_data = str_ireplace($breaks, "\r\n", $file_data);
            
            @$write_file = $wp_filesystem->put_contents($file_path, $file_data, 0755);
            if (!$write_file) {
                /* esc_html_e('Error Saving Log.', 'ARMember'); */
            }
        }
        return;
    }


    function arm_write_payment_log($arm_log_payment_gateway, $arm_log_event, $arm_log_event_from = 'armember', $arm_payment_log_raw_data = '', $arm_ref_id = 0, $arm_log_status = 1)
    {
        global $wpdb, $ARMember, $arm_payment_gateways, $arm_debug_payment_log_id, $arm_capabilities_global, $arm_payment_gateways_data_logs, $arm_payment_gateways_data_logs_flag;

        if(empty($arm_payment_gateways_data_logs) && empty($arm_payment_gateways_data_logs_flag) )
        {
            $arm_payment_gateways_data_logs = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();
            $arm_payment_gateways_data_logs_flag = 1;
        }
        $inserted_id = 0;
        if( !empty($arm_payment_gateways_data_logs[$arm_log_payment_gateway]['payment_debug_logs']) )
        {
            $tbl_arm_debug_payment_log = $ARMember->tbl_arm_debug_payment_log;

            if($arm_ref_id==NULL) { $arm_ref_id = 0; }
            $arm_database_log_data = array(
                'arm_payment_log_ref_id' => $arm_ref_id,
                'arm_payment_log_gateway' => $arm_log_payment_gateway,
                'arm_payment_log_event' => $arm_log_event,
                'arm_payment_log_event_from' => $arm_log_event_from,
                'arm_payment_log_status' => $arm_log_status,
                'arm_payment_log_raw_data' => maybe_serialize(stripslashes_deep($arm_payment_log_raw_data)),
                'arm_payment_log_added_date' => current_time('mysql'),
            );
            
            //If reference id empty then insert log.
            $wpdb->insert($tbl_arm_debug_payment_log, $arm_database_log_data);
            $inserted_id = $wpdb->insert_id;
            if(empty($arm_ref_id))
            {
                $arm_ref_id = $inserted_id;
            }
        }
        $arm_debug_payment_log_id = $arm_ref_id;

        return $inserted_id;
    }



    function arm_write_general_log($arm_log_event, $arm_log_event_name, $arm_log_event_from = 'armember', $arm_payment_log_raw_data = '')
    {
        global $wpdb, $ARMember, $arm_debug_general_log_id, $arm_capabilities_global, $arm_email_settings, $arm_is_cron_log_enabled,$arm_is_email_log_enabled ,$arm_is_cron_log_check_flag, $arm_is_opt_ins_log_enabled, $arm_is_opt_ins_log_check_flag,$arm_is_email_log_check_flag;

        if ($arm_log_event == 'cron') 
        {
            if ( empty($arm_is_cron_log_enabled) && empty($arm_is_cron_log_check_flag) ) 
            {
                $arm_is_cron_log_enabled = get_option('arm_cron_debug_log');
                $arm_is_cron_log_check_flag = 1;
            }
            $arm_is_log_enabled = $arm_is_cron_log_enabled;
        } 
        else if ($arm_log_event == 'email') 
        {
            if ( empty($arm_is_email_log_enabled) && empty($arm_is_email_log_check_flag) ) 
            {
                $arm_is_email_log_enabled = get_option('arm_email_debug_log');
                $arm_is_email_log_check_flag = 1;
            }
            $arm_is_log_enabled = $arm_is_email_log_enabled;
        } 
        else {
            if ($arm_email_settings->isOptInsFeature && empty($arm_is_opt_ins_log_enabled) && empty($arm_is_opt_ins_log_check_flag)) 
            {
                $arm_is_opt_ins_log_enabled = get_option('arm_optins_debug_log');
                $arm_is_opt_ins_log_check_flag = 1;
            }
            $arm_is_log_enabled = $arm_is_opt_ins_log_enabled;
        }
        
        $inserted_id = 0;
        if($arm_log_event != 'email') {
            $arm_payment_log_raw_data = maybe_serialize(stripslashes_deep($arm_payment_log_raw_data));
        }
        if ( !empty($arm_is_log_enabled) ) 
        {
            $tbl_arm_debug_general_log = $ARMember->tbl_arm_debug_general_log;
            $arm_database_log_data = array(
                'arm_general_log_event' => $arm_log_event,
                'arm_general_log_event_name' => $arm_log_event_name,
                'arm_general_log_event_from' => $arm_log_event_from,
                'arm_general_log_raw_data' => $arm_payment_log_raw_data,
                'arm_general_log_added_date' => current_time('mysql'),  
            );
            
            $wpdb->insert($tbl_arm_debug_general_log, $arm_database_log_data);
            $inserted_id = $wpdb->insert_id;
        }
        
        return $inserted_id;
    }

    /**
     * Function for Write Degug Log
     */
    function arm_debug_response_log($callback = '', $arm_restricted_cases = array(), $query_obj = array(), $executed_query = '', $is_mail_log = false) {
        global $wp, $wpdb, $wp_filesystem;
        if (!defined('MEMBERSHIP_DEBUG_LOG') || MEMBERSHIP_DEBUG_LOG == false) {
            return;
        }
        $arm_restricted_cases_filtered = "";
        if ($executed_query == "") {
            $executed_query = $wpdb->last_query;
        }
        $arm_restriction_type = 'redirect';
        if (!empty($arm_restricted_cases)) {
            foreach ($arm_restricted_cases as $key => $restricted_case) {
                if ($restricted_case['protected'] == true) {
                    $arm_restricted_cases_filtered = $arm_restricted_cases[$key]["message"];
                    $arm_restriction_type = isset($arm_restricted_cases[$key]['type']) ? $arm_restricted_cases[$key]['type'] : '';
                }
            }
        }
        $arm_debug_file_path = MEMBERSHIP_DIR . '/log/restriction_response.txt';
        $date = "[ " . date(get_option('date_format') . ' ' . get_option('time_format')) . " ]";
        if (file_exists(ABSPATH . 'wp-admin/includes/file.php')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            if (false === ($creds = request_filesystem_credentials($arm_debug_file_path, '', false, false) )) {
                return true;
            }
            if (!WP_Filesystem($creds)) {
                request_filesystem_credentials($arm_debug_file_path, $method, true, false);
                return true;
            }
            $debug_log_type = MEMBERSHIP_DEBUG_LOG_TYPE;
            $content = " Date: " . $date . "\r\n";
            $content .= "\r\n Function :" . $callback . "\r\n";
            if ($is_mail_log == true) {
                $content .= "\r\n Log Type : Mail Notification Log \r\n";
                $content .= "\r\n Mail Content : " . $arm_restricted_cases_filtered . " \r\n";
            } else {
                $content .= "\r\n Log Type : " . $debug_log_type . "\r\n";
                $content .= "\r\n Content : " . $arm_restricted_cases_filtered . "\r\n";
                
            }
            $content .= "\r\n Last Executed Query:" . $executed_query . "\r\n";
            $arm_debug_file_data = $wp_filesystem->get_contents($arm_debug_file_path);
            $arm_debug_file_data .= $content;
            $arm_debug_file_data .= "\r\n===========================================================================\r\n";
            $breaks = array("<br />", "<br>", "<br/>");
            $arm_debug_file_data = str_ireplace($breaks, "\r\n", $arm_debug_file_data);
            
            @$write_file = $wp_filesystem->put_contents($arm_debug_file_path, $arm_debug_file_data, 0755);
            if (!$write_file) {
                /* esc_html_e('Error Saving Log.', 'ARMember'); */
            }
        }
    }

    function arm_admin_messages_init($page = '') {
        global $wp, $wpdb, $arm_errors, $ARMember, $pagenow, $arm_slugs;
        $success_msgs = '';
        $error_msgs = '';
        $ARMember->arm_session_start();
        if (isset($_SESSION['arm_message']) && !empty($_SESSION['arm_message'])) {
            foreach ($_SESSION['arm_message'] as $snotice) {
                if ($snotice['type'] == 'success') {
                    $success_msgs .= $snotice['message'];
                } else {
                    $error_msgs .= $snotice['message'];
                }
            }
            if (!empty($success_msgs)) {
                ?>
                <script type="text/javascript">jQuery(window).on("load", function () {
                        armToast('<?php echo $snotice['message']; //phpcs:ignore?>', 'success');
                    });</script>
                <?php
            } elseif (!empty($error_msgs)) {
                ?>
                <script type="text/javascript">jQuery(window).on("load", function () {
                        armToast('<?php echo $snotice['message']; //phpcs:ignore?>', 'error');
                    });</script>
                <?php
            }
            unset($_SESSION['arm_message']);
        }
        ?>
        <div class="armclear"></div>
        <div class="arm_message arm_success_message" id="arm_success_message">
            <div class="arm_message_text"><?php echo $success_msgs; //phpcs:ignore?></div>
        </div>
        <div class="arm_message arm_error_message" id="arm_error_message">
            <div class="arm_message_text"><?php echo $error_msgs; //phpcs:ignore?></div>
        </div>
        <div class="armclear"></div>
        <div class="arm_toast_container" id="arm_toast_container"></div>
        <div class="arm_loading" style="display: none;"><img src="<?php echo MEMBERSHIP_IMAGES_URL; //phpcs:ignore?>/loader.gif" alt="Loading.."></div>
        <?php
    }

    function arm_do_not_show_video() {
        global $wp, $wpdb, $ARMember, $pagenow;
        //$ARMember->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1' ); //phpcs:ignore --Reason:Verifying nonce
        $isShow = (isset($_POST['isShow']) && $_POST['isShow'] == '0') ? 0 : 1;//phpcs:ignore
        $now = strtotime(current_time('mysql'));
        $time = strtotime('+10 day', $now);
        update_option('arm_show_document_video', $isShow);
        update_option('arm_show_document_video_on', $time);
        exit;
    }

    function arm_add_document_video() {
        global $wp, $wpdb, $ARMember, $pagenow, $arm_slugs;
        $popupData = '';
        $slugs = json_encode($arm_slugs);
        $arm_slugs = json_decode(json_encode($arm_slugs), true);
        if ( isset($_REQUEST['page']) && in_array($_REQUEST['page'], $arm_slugs) && $arm_slugs['arm_setup_wizard'] != $_REQUEST['page'] ) {
            $now = strtotime(current_time('mysql'));
            $show_document_video = get_option('arm_show_document_video', 0);
            $show_document_video_on = get_option('arm_show_document_video_on', strtotime(current_time('mysql')));
            if ($show_document_video == '0') {
                return;
            }
            if ($show_document_video_on > $now) {
                return;
            }
            /* Document Video Popup */
            $popupData = '<div id="arm_document_video_popup" class="popup_wrapper arm_document_video_popup"><div class="popup_wrapper_inner">';
            $popupData .= '<div class="popup_header">';
            $popupData .= '<span class="popup_close_btn arm_popup_close_btn" onclick="armHideDocumentVideo();"></span>';
            $popupData .= '<span class="popup_header_text">' . esc_html__('Help Tutorial', 'ARMember') . '</span>';
            $popupData .= '</div>';
            $popupData .= '<div class="popup_content_text">';
            $popupData .= '<iframe src="' . MEMBERSHIP_VIDEO_URL . '" allowfullscreen="" frameborder="0"> </iframe> ';
            $popupData .= '</div>';
            $popupData .= '<div class="armclear"></div>';
            $popupData .= '<div class="popup_content_btn popup_footer">';
            $nonce = wp_create_nonce('arm_wp_nonce');
            $popupData .= '<input type="hidden" name="arm_wp_nonce" value="'.esc_attr( $nonce ).'">';
            $popupData .= '<label><input type="checkbox" id="arm_do_not_show_video" class="arm_do_not_show_video arm_icheckbox"><span>' . esc_html__('Do not show again.', 'ARMember') . '</span></label>';
            $popupData .= '<div class="popup_content_btn_wrapper">';
            $popupData .= '<button class="arm_cancel_btn popup_close_btn" onclick="armHideDocumentVideo();" type="button">' . esc_html__('Close', 'ARMember') . '</button>';
            $popupData .= '</div>';
            $popupData .= '<div class="armclear"></div>';
            $popupData .= '</div>';
            $popupData .= '<div class="armclear"></div>';
            $popupData .= '</div></div>';
            $popupData .= '<script type="text/javascript">jQuery(window).on("load", function(){
				var v_width = jQuery( window ).width();
				if(v_width <= "1350")
		        {
		          var poup_width = "720";
		          var poup_height = "400";
		          jQuery("#arm_document_video_popup").css("width","760");
		          jQuery(".popup_content_text iframe").css("width",poup_width);
		          jQuery(".popup_content_text iframe").css("height",poup_height);
		          
		        }
		        if(v_width > "1350" && v_width <= "1600")
		        {
		          var poup_width = "750";
		          var poup_height = "430";

		          jQuery("#arm_document_video_popup").css("width","790");
		          jQuery(".popup_content_text iframe").css("width",poup_width);
		          jQuery(".popup_content_text iframe").css("height",poup_height);
		        }
		        if(v_width > "1600")
		        {
		          var poup_width = "800";
		          var poup_height = "450";
		          jQuery("#arm_document_video_popup").css("width","840");
		          jQuery(".popup_content_text iframe").css("width",poup_width);
		          jQuery(".popup_content_text iframe").css("height",poup_height);
		        }
				jQuery("#arm_document_video_popup").bPopup({
					modalClose: false,
					closeClass: "popup_close_btn",
					onClose: function(){
               			 jQuery(this).find(".popup_wrapper_inner .popup_content_text").html("");
         			},
				});
			});</script>';
            echo $popupData;//phpcs:ignore
        }
    }

    function arm_add_new_version_release_note() {
        global $wp, $wpdb, $ARMember, $pagenow, $arm_slugs, $arm_version;
        $popupData = '';
        if (isset($_REQUEST['page']) && in_array($_REQUEST['page'], (array) $arm_slugs)) {

            if(is_multisite())
            {
                $show_document_video = get_site_option('arm_new_version_installed', 0);
            }
            else {
                $show_document_video = get_option('arm_new_version_installed', 0);
            }

            if ($show_document_video == '0') {
                return;
            }


            /*changes for dynamic addon listing */
            //$plugins = get_plugins();
            /*$installed_plugins = array();
            foreach ($plugins as $key => $plugin) {
                $is_active = is_plugin_active($key);
                $installed_plugin = array("plugin" => $key, "name" => $plugin["Name"], "is_active" => $is_active);
                $installed_plugin["activation_url"] = $is_active ? "" : wp_nonce_url("plugins.php?action=activate&plugin={$key}", "activate-plugin_{$key}");
                $installed_plugin["deactivation_url"] = !$is_active ? "" : wp_nonce_url("plugins.php?action=deactivate&plugin={$key}", "deactivate-plugin_{$key}");

                $installed_plugins[] = $installed_plugin;
            }*/

            /*global $arm_version, $arm_social_feature;
            $bloginformation = array();
            $str = $arm_social_feature->get_rand_alphanumeric(10);

            if (is_multisite())
                $multisiteenv = "Multi Site";
            else
                $multisiteenv = "Single Site";

            $addon_listing = 1;

            $bloginformation[] = get_bloginfo('name');
            $bloginformation[] = get_bloginfo('description');
            $bloginformation[] = ARM_HOME_URL;
            $bloginformation[] = get_bloginfo('admin_email');
            $bloginformation[] = get_bloginfo('version');
            $bloginformation[] = get_bloginfo('language');
            $bloginformation[] = $arm_version;
            $bloginformation[] = $_SERVER['REMOTE_ADDR'];
            $bloginformation[] = $str;
            $bloginformation[] = $multisiteenv;
            $bloginformation[] = $addon_listing;

            $valstring = implode("||", $bloginformation);
            $encodedval = base64_encode($valstring);*/

            $urltopost = 'https://www.armemberplugin.com/armember_addons/addon_whatsnew_list.php?arm_version='.$arm_version.'&arm_list_type=whatsnew_list';

            $raw_response = wp_remote_post($urltopost, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                //'body' => array('plugins' => urlencode(serialize($installed_plugins)), 'wpversion' => $encodedval),
                'cookies' => array()
                    )
            );
            $addon_list_html = "";
            $arm_whtsnew_wrapper_width = "";
            if (is_wp_error($raw_response) || $raw_response['response']['code'] != 200) {
                $addon_list_html .= "<div class='error_message' style='margin-top:100px; padding:20px;'>" . esc_html__("Add-On listing is currently unavailable. Please try again later.", 'ARMember') . "</div>";
            } else {
                $addon_list = json_decode($raw_response['body']);
                $addon_count = count($addon_list);
                $arm_whtsnew_wrapper_width = $addon_count * 141;
                foreach ( $addon_list as $list) {

                    $addon_list_html .= '<div class="arm_add_on"><a href="'.$list->addon_url.'" target="_blank"><img src="' . $list->addon_icon_url . '" /></a><div class="arm_add_on_text"><a href="'.$list->addon_url.'" target="_blank">'.$list->addon_name.'</a></div></div>';
                }
            }
            /*complete changes*/

            $popupData = '<div id="arm_update_note" class="popup_wrapper arm_update_note">'
                    . '<div class="popup_wrapper_inner">';
            $popupData .= '<div class="popup_header">';
            $popupData .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/logo_addon.png" />';
            $popupData .= '</div>';
            $popupData .= '<div class="popup_content_text">';
            $i = 1;
            $major_changes = false;
            $change_log = $this->arm_new_version_changelog();

            if (isset($change_log) && !empty($change_log)) {



                $arm_show_critical_change_title = isset($change_log['show_critical_title']) ? $change_log['show_critical_title'] : 0;
                $arm_critical_title = isset($change_log['critical_title']) ? $change_log['critical_title'] : '';
                $arm_critical_changes = (isset($change_log['critical']) && !empty($change_log['critical'])) ? $change_log['critical'] : array();

                $arm_show_major_change_title = isset($change_log['show_major_title']) ? $change_log['show_major_title'] : 0;
                $arm_major_title = isset($change_log['major_title']) ? $change_log['major_title'] : '';
                $arm_major_changes = (isset($change_log['major']) && !empty($change_log['major'])) ? $change_log['major'] : array();

                $arm_show_other_change_title = isset($change_log['show_other_title']) ? $change_log['show_other_title'] : 0;
                $arm_other_title = isset($change_log['other_title']) ? $change_log['other_title'] : '';
                $arm_other_changes = (isset($change_log['other']) && !empty($change_log['other'])) ? $change_log['other'] : array();


                if (!empty($arm_critical_changes)) {
                    if ($arm_show_critical_change_title == 1) {
                        $popupData .= '<div class="arm_critical_change_title">' . $arm_critical_title . '</div>';//phpcs:ignore
                    }
                    $popupData .= '<div class="arm_critical_change_list"><ul>';
                    foreach ($arm_critical_changes as $value) {
                        $popupData .='<li>' . $value . '</li>';//phpcs:ignore
                    }
                    $popupData .= '</ul></div>';
                }

                if (!empty($arm_major_changes)) {
                    if ($arm_show_major_change_title == 1) {
                        $popupData .= '<div class="arm_major_change_title">' . $arm_major_title . '</div>';//phpcs:ignore
                    }
                    $popupData .= '<div class="arm_major_change_list"><ul>';
                    foreach ($arm_major_changes as $value) {
                        $popupData .='<li>' . $value . '</li>';//phpcs:ignore
                    }
                    $popupData .= '</ul></div>';
                }

                if (!empty($arm_other_changes)) {
                    if ($arm_show_other_change_title == 1) {
                        $popupData .= '<div class="arm_other_change_title">' . $arm_other_title . '</div>';//phpcs:ignore
                    }
                    $popupData .= '<div class="arm_other_change_list"><ul>';
                    foreach ($arm_other_changes as $value) {
                        $popupData .='<li>' . $value . '</li>';//phpcs:ignore
                    }
                    $popupData .= '</ul></div>';
                }
            }

            $popupData .= '</div>';
            $popupData .= '<div class="arm_addons_list_title">' . esc_html__('Available Add-ons', 'ARMember') . '</div>';

            
            $popupData .= '<div class="arm_addons_list_div">';
            $popupData .= '<div class="arm_addons_list" style="width:'.$arm_whtsnew_wrapper_width.'px;">';
            $popupData .= $addon_list_html;
            $popupData .= '</div>';
            $popupData .= '</div>';



            $popupData .= '<div class="armclear"></div>';
            $popupData .= '<div class="popup_content_btn popup_footer">';
            if (!empty($arm_critical_changes)) {
                $popupData .= '<label><input type="checkbox" id="arm_hide_update_notice" class="arm_icheckbox"><span>' . esc_html__('I agree', 'ARMember') . '</span></label>';
                $popupData .= '<div class="popup_content_btn_wrapper">';
                $popupData .= '<button class="arm_cancel_btn popup_close_btn" onclick="arm_hide_update_notice();" type="button">' . esc_html__('Close', 'ARMember') . '</button>';
                $popupData .= '</div>';
                $popupData .= '<div class="armclear"></div>';
            } else {
                $popupData .= '<div style="display: none;"><input type="checkbox" id="arm_hide_update_notice" class="arm_icheckbox" value="1" checked="checked"></div>';
            }
            $popupData .= '</div>';
            $popupData .= '<div class="armclear"></div>';
            $popupData .= '</div></div>';
            $popupData .= '<script type="text/javascript">jQuery(window).on("load", function(){
				
				jQuery("#arm_update_note").bPopup({
					modalClose: false,  
                    escClose : false                                        
				});

			});
            function arm_hide_update_notice()
            {
                var ishide = 0;
                if (jQuery("#arm_hide_update_notice").is(":checked")) {
                    var ishide = 1;                   
                    jQuery("#arm_update_note").bPopup().close(); 
                }else{
                    return;
                }
                var _arm_wpnonce   = jQuery( \'input[name="arm_wp_nonce"]\' ).val();
                jQuery.ajax({
                type: "POST",
                url: __ARMAJAXURL,
                data: "action=arm_dont_show_upgrade_notice&is_hide=" + ishide+"&_wpnonce="+_arm_wpnonce,
                success: function (res) {

                        return false;
                        
                }
                });
                return false;
            }
            </script>';
            echo $popupData;//phpcs:ignore
        }
    }

    /*
     * for red color note `|^|Use coupon for invitation link`
     * Add important note to `major`
     * Add normal changelog to `other`  
     */

    function arm_new_version_changelog() {
        $arm_change_log = array();
        global $arm_payment_gateways, $arm_global_settings, $arm_slugs;
        $active_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();

        $arm_change_log = array(
            'show_critical_title' => 1,
            'critical_title' =>'Version 6.9.8 Changes',
            'critical' =>array(
                    "Minor bug fixes.",
                ),
            'show_major_title' => 0,
            'major_title' =>'Major Changes',
            'major' => array( ),
            'show_other_title' =>0,
            'other_title' => 'Other Changes',
            'other' => array(
               
            )
        );
        return $arm_change_log;
    }

    function arm_dont_show_upgrade_notice() {
        global $wp, $wpdb, $ARMember, $pagenow, $arm_capabilities_global;

        $ARMember->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1'); //phpcs:ignore --Reason:Verifying nonce
        
        $is_hide = (isset($_POST['is_hide']) && $_POST['is_hide'] == '1') ? 1 : 0;//phpcs:ignore
        if ($is_hide == 1) 
        {
            $get_det = $updt_det = 0;
            if(is_multisite()) 
            {
                $blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
                if($blogs)
                {
                    foreach($blogs as $blog)
                    {
                        switch_to_blog($blog['blog_id']);
                        $arm_del_option_notice = delete_site_option('arm_new_version_installed');
                    }
                }
                update_option('arm_new_version_installed', 0);
            }
            else 
            {
                $arm_del_option_notice = delete_option('arm_new_version_installed');
            }

            echo json_encode( array('status' => 'success', 'response' => $arm_del_option_notice ) );
        }
        die();
    }

    /* Cornerstone Methods */

    function arm_front_alert_messages() {
        $alertMessages = array(
            'loadActivityError' => esc_html__("There is an error while loading activities, please try again.", 'ARMember'),
            'pinterestPermissionError' => esc_html__("The user has not grant permissions or closed the pop-up", 'ARMember'),
            'pinterestError' => esc_html__("Oops, there was a problem for getting account information", 'ARMember'),
            'clickToCopyError' => esc_html__("There is an error while copying, please try again", 'ARMember'),
            'fbUserLoginError' => esc_html__("User has cancelled login or did not fully authorize.", 'ARMember'),
            'closeAccountError' => esc_html__("There is an error while closing account, please try again.", 'ARMember'),
            'invalidFileTypeError' => esc_html__("Sorry, this file type is not permitted for security reasons.", 'ARMember'),
            'fileSizeError' => esc_html__("File is not allowed larger than {SIZE}.", 'ARMember'),
            'fileUploadError' => esc_html__("There is an error in uploading file, Please try again.", 'ARMember'),
            'coverRemoveConfirm' => esc_html__("Are you sure you want to remove cover photo?", 'ARMember'),
            'profileRemoveConfirm' => esc_html__("Are you sure you want to remove profile photo?", 'ARMember'),
            'errorPerformingAction' => esc_html__("There is an error while performing this action, please try again.", 'ARMember'),
            'userSubscriptionCancel' => esc_html__("User's subscription has been canceled", 'ARMember'),
            'cancelSubscriptionAlert' => esc_html__("Are you sure you want to cancel subscription?", 'ARMember'),
            'ARM_Loding' => esc_html__("Loading..", 'ARMember')
        );
        return $alertMessages;
    }

    function arm_alert_messages() {
        $alertMessages = array(
            'wentwrong' => esc_html__("Sorry, Something went wrong. Please try again.", 'ARMember'),
            'bulkActionError' => esc_html__("Please select valid action.", 'ARMember'),
            'bulkRecordsError' => esc_html__("Please select one or more records.", 'ARMember'),
            'clearLoginAttempts' => esc_html__("Login attempts cleared successfully.", 'ARMember'),
            'clearLoginHistory' => esc_html__("Login History cleared successfully.", 'ARMember'),
            'nopasswordforimport' => esc_html__("Password can not be left blank.", 'ARMember'),
            'delBadgeSuccess' => esc_html__("Badge has been deleted successfully.", 'ARMember'),
            'delBadgeError' => esc_html__("There is an error while deleting Badge, please try again.", 'ARMember'),
            'delAchievementBadgeSuccess' => esc_html__("Achievement badges has been deleted successfully.", 'ARMember'),
            'delAchievementBadgeError' => esc_html__("There is an error while deleting achievement badges, please try again.", 'ARMember'),
            'addUserAchievementSuccess' => esc_html__("User Achievement Added Successfully.", 'ARMember'),
            'delUserBadgeSuccess' => esc_html__("User badge has been deleted successfully.", 'ARMember'),
            'delUserBadgeError' => esc_html__("There is an error while deleting user badge, please try again.", 'ARMember'),
            'delPlansSuccess' => esc_html__("Plan(s) has been deleted successfully.", 'ARMember'),
            'delPlansError' => esc_html__("There is an error while deleting Plan(s), please try again.", 'ARMember'),
            'delPlanSuccess' => esc_html__("Plan has been deleted successfully.", 'ARMember'),
            'delPlanError' => esc_html__("There is an error while deleting Plan, please try again.", 'ARMember'),
            'stripePlanIDWarning' => esc_html__("If you leave this field blank, stripe will not be available in setup for recurring plan(s).", 'ARMember'),
            'delSetupsSuccess' => esc_html__("Setup(s) has been deleted successfully.", 'ARMember'),
            'delSetupsError' => esc_html__("There is an error while deleting Setup(s), please try again.", 'ARMember'),
            'delSetupSuccess' => esc_html__("Setup has been deleted successfully.", 'ARMember'),
            'delSetupError' => esc_html__("There is an error while deleting Setup, please try again.", 'ARMember'),
            'delFormSetSuccess' => esc_html__("Form Set Deleted Successfully.", 'ARMember'),
            'delFormSetError' => esc_html__("There is an error while deleting form set, please try again.", 'ARMember'),
            'delFormSuccess' => esc_html__("Form deleted successfully.", 'ARMember'),
            'delFormError' => esc_html__("There is an error while deleting form, please try again.", 'ARMember'),
            'delRuleSuccess' => esc_html__("Rule has been deleted successfully.", 'ARMember'),
            'delRuleError' => esc_html__("There is an error while deleting Rule, please try again.", 'ARMember'),
            'delRulesSuccess' => esc_html__("Rule(s) has been deleted successfully.", 'ARMember'),
            'delRulesError' => esc_html__("There is an error while deleting Rule(s), please try again.", 'ARMember'),
            'prevTransactionError' => esc_html__("There is an error while generating preview of transaction detail, Please try again.", 'ARMember'),
            'invoiceTransactionError' => esc_html__("There is an error while generating invoice of transaction detail, Please try again.", 'ARMember'),
            'prevMemberDetailError' => esc_html__("There is an error while generating preview of members detail, Please try again.", 'ARMember'),
            'prevMemberActivityError' => esc_html__("There is an error while displaying members activities detail, Please try again.", 'ARMember'),
            'prevCustomCssError' => esc_html__("There is an error while displaying ARMember CSS Class Information, Please Try Again.", 'ARMember'),
            'prevImportMemberDetailError' => esc_html__("Please upload appropriate file to import users.", 'ARMember'),
            'delTransactionSuccess' => esc_html__("Transaction has been deleted successfully.", 'ARMember'),
            'delTransactionsSuccess' => esc_html__("Transaction(s) has been deleted successfully.", 'ARMember'),
            'delAutoMessageSuccess' => esc_html__("Message has been deleted successfully.", 'ARMember'),
            'delAutoMessageError' => esc_html__("There is an error while deleting Message, please try again.", 'ARMember'),
            'delAutoMessagesSuccess' => esc_html__("Message(s) has been deleted successfully.", 'ARMember'),
            'delAutoMessagesError' => esc_html__("There is an error while deleting Message(s), please try again.", 'ARMember'),
            'delCouponSuccess' => esc_html__("Coupon has been deleted successfully.", 'ARMember'),
            'delCouponError' => esc_html__("There is an error while deleting Coupon, please try again.", 'ARMember'),
            'delCouponsSuccess' => esc_html__("Coupon(s) has been deleted successfully.", 'ARMember'),
            'delCouponsError' => esc_html__("There is an error while deleting Coupon(s), please try again.", 'ARMember'),
            'saveSettingsSuccess' => esc_html__("Settings has been saved successfully.", 'ARMember'),
            'saveSettingsError' => esc_html__("There is an error while updating settings, please try again.", 'ARMember'),
            'saveDefaultRuleSuccess' => esc_html__("Default Rules Saved Successfully.", 'ARMember'),
            'saveDefaultRuleError' => esc_html__("There is an error while updating rules, please try again.", 'ARMember'),
            'saveOptInsSuccess' => esc_html__("Opt-ins Settings Saved Successfully.", 'ARMember'),
            'saveOptInsError' => esc_html__("There is an error while updating opt-ins settings, please try again.", 'ARMember'),
            'delOptInsConfirm' => esc_html__("Are you sure to delete configuration?", 'ARMember'),
            'delMemberActivityError' => esc_html__("There is an error while deleting member activities, please try again.", 'ARMember'),
            'noTemplateError' => esc_html__("Template not found.", 'ARMember'),
            'saveTemplateSuccess' => esc_html__("Template options has been saved successfully.", 'ARMember'),
            'saveTemplateError' => esc_html__("There is an error while updating template options, please try again.", 'ARMember'),
            'prevTemplateError' => esc_html__("There is an error while generating preview of template, Please try again.", 'ARMember'),
            'addTemplateSuccess' => esc_html__("Template has been added successfully.", 'ARMember'),
            'addTemplateError' => esc_html__("There is an error while adding template, please try again.", 'ARMember'),
            'delTemplateSuccess' => esc_html__("Template has been deleted successfully.", 'ARMember'),
            'delTemplateError' => esc_html__("There is an error while deleting template, please try again.", 'ARMember'),
            'saveEmailTemplateSuccess' => esc_html__("Email Template Updated Successfully.", 'ARMember'),
            'saveAutoMessageSuccess' => esc_html__("Message Updated Successfully.", 'ARMember'),
            'saveBadgeSuccess' => esc_html__("Badges Updated Successfully.", 'ARMember'),
            'addAchievementSuccess' => esc_html__("Achievements Added Successfully.", 'ARMember'),
            'saveAchievementSuccess' => esc_html__("Achievements Updated Successfully.", 'ARMember'),
            'addDripRuleSuccess' => esc_html__("Rule Added Successfully.", 'ARMember'),
            'saveDripRuleSuccess' => esc_html__("Rule updated Successfully.", 'ARMember'),
            'pastDateError' => esc_html__("Cannot Set Past Dates.", 'ARMember'),
            'pastStartDateError' => esc_html__("Start date can not be earlier than current date.", 'ARMember'),
            'pastExpireDateError' => esc_html__("Expire date can not be earlier than current date.", 'ARMember'),
            'couponExpireDateError' => esc_html__("Expire date can not be earlier than start date.", 'ARMember'),
            'uniqueformsetname' => esc_html__("This Set Name is already exist.", 'ARMember'),
            'uniquesignupformname' => esc_html__("This Form Name is already exist.", 'ARMember'),
            'installAddonError' => esc_html__('There is an error while installing addon, Please try again.', 'ARMember'),
            'installAddonSuccess' => esc_html__('Addon installed successfully.', 'ARMember'),
            'activeAddonError' => esc_html__('There is an error while activating addon, Please try agina.', 'ARMember'),
            'activeAddonSuccess' => esc_html__('Addon activated successfully.', 'ARMember'),
            'deactiveAddonSuccess' => esc_html__('Addon deactivated successfully.', 'ARMember'),
            'pwdstrength_vweak' => esc_html__('Strength: Very Weak', 'ARMember'),
            'pwdstrength_weak' => esc_html__('Strength: Weak', 'ARMember'),
            'pwdstrength_good' => esc_html__('Strength: Good', 'ARMember'),
            'pwdstrength_vgood' => esc_html__('Strength: Strong', 'ARMember'),
            'confirmCancelSubscription' => esc_html__('Are you sure you want to cancel subscription?', 'ARMember'),
            'errorPerformingAction' => esc_html__("There is an error while performing this action, please try again.", 'ARMember'),
            'userSubscriptionCancel' => esc_html__("User's subscription has been canceled", 'ARMember'),
            'cancelSubscriptionAlert' => esc_html__("Are you sure you want to cancel subscription?", 'ARMember'),
            'ARM_Loding' => esc_html__("Loading..", 'ARMember'),
            'arm_nothing_found' => esc_html__('Oops, nothing found.', 'ARMember')
        );
        $frontMessages = $this->arm_front_alert_messages();
        $alertMessages = array_merge($alertMessages, $frontMessages);
        return $alertMessages;
    }

    function arm_prevent_rocket_loader_script($tag, $handle) {        

        $is_skip = false;
        $is_skip = apply_filters('arm_script_loader_tag_check_external', $is_skip);

        if( $is_skip ) {
            return $tag;
        }
        
        $script = htmlspecialchars($tag);
        $pattern2 = '/\/(wp\-content\/plugins\/armember)|(wp\-includes\/js)|(apis\.google\.com)/';
        preg_match($pattern2,$script,$match_script);

        /* Check if current script is loaded from ARMember only */
        if( !isset($match_script[0]) || $match_script[0] == '' ){
            return $tag;
        }

        $pattern = '/(.*?)(data\-cfasync\=)(.*?)/';
        preg_match_all($pattern,$tag,$matches);
        if( !is_array($matches) ){
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if( !empty($matches) && !empty($matches[2]) && !empty($matches[2][0]) && strtolower(trim($matches[2][0])) != 'data-cfasync=' ){
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if( !empty($matches) && empty($matches[2]) ) {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else {
            return $tag;
        }
    }

    function arm_prevent_rocket_loader_script_clf( $tag, $handle ){

        $is_skip = false;
        $is_skip = apply_filters('arm_script_loader_tag_check_external', $is_skip);

        if( $is_skip ) {
            return $tag;
        }

    	$script = htmlspecialchars($tag);
        $pattern2 = '/\/(wp\-content\/plugins\/armember)|(wp\-includes\/js)|(apis\.google\.com)/';
        preg_match($pattern2,$script,$match_script);

        /* Check if current script is loaded from ARMember only */
        if( !isset($match_script[0]) || $match_script[0] == '' ){
            return $tag;
        }

        $pattern = '/(.*?)(data\-cfasync\=)(.*?)/';
        preg_match_all($pattern, $tag, $matches);

        $pattern3 = '/type\=(\'|")[a-zA-Z0-9]+\-(text\/javascript)(\'|")/';
        preg_match_all($pattern3, $tag, $match_tag);

        if( !isset( $match_tag[0] ) || '' == $match_tag[0] ){
            return $tag;
        }

        if (!is_array($matches)) {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if (!empty($matches) && !empty($matches[2]) && !empty($matches[2][0]) && strtolower(trim($matches[2][0])) != 'data-cfasync=') {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else if (!empty($matches) && empty($matches[2])) {
            return str_replace(' src', ' data-cfasync="false" src', $tag);
        } else {
            return $tag;
        }
    }

    function arm_set_js_css_conditionally() {
        global $arm_datepicker_loaded, $arm_avatar_loaded, $arm_file_upload_field, $bpopup_loaded, $arm_load_tipso, $arm_load_icheck, $arm_font_awesome_loaded, $arm_grecaptcha_is_enqueue, $arm_global_settings;
        if (!is_admin()) {
            if ($arm_datepicker_loaded == 1) {
                if (!wp_script_is('arm_bootstrap_datepicker_with_locale_js', 'enqueued')) {
                    wp_enqueue_script('arm_bootstrap_datepicker_with_locale_js');
                }
            }
            if ($arm_avatar_loaded == 1 || $arm_file_upload_field == 1) {
                if (!wp_script_is('arm_file_upload_js', 'enqueued')) {
                    wp_enqueue_script('arm_file_upload_js');
                }
            }
            if ($bpopup_loaded == 1) {
                if (!wp_script_is('arm_bpopup', 'enqueued')) {
                    wp_enqueue_script('arm_bpopup');
                }
            }
            if ($arm_load_tipso == 1) {
                if (!wp_script_is('arm_tipso_front', 'enqueued')) {
                    wp_enqueue_script('arm_tipso_front');
                }
            }
            if ($arm_font_awesome_loaded == 1) {
                wp_enqueue_style('arm_fontawesome_css');
            }
            if($arm_grecaptcha_is_enqueue)
            {
                $all_global_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
                $arm_recaptcha_site_key = !empty($all_global_settings['arm_recaptcha_site_key']) ? $all_global_settings['arm_recaptcha_site_key'] : '';
                $arm_recaptcha_private_key = !empty($all_global_settings['arm_recaptcha_private_key']) ? $all_global_settings['arm_recaptcha_private_key'] : '';
                $arm_recaptcha_lang = !empty($all_global_settings['arm_recaptcha_lang']) ? $all_global_settings['arm_recaptcha_lang'] : 'en';

                if(!empty($arm_recaptcha_site_key) && !empty($arm_recaptcha_private_key)) 
                {
                    $arm_google_recaptcha_url = add_query_arg(
                        array(
                            'hl'=> $arm_recaptcha_lang,
                            'render' => $arm_recaptcha_site_key,
                            'onload'=>'render_arm_captcha_v3',
                        ),
                        'https://www.google.com/recaptcha/api.js'
                    );
                    
                    wp_enqueue_script('arm-google-recaptcha',$arm_google_recaptcha_url, array('jquery'), MEMBERSHIP_VERSION);
                }
            }
        }
    }

    function arm_check_font_awesome_icons($content) {
        global $arm_font_awesome_loaded;

        $fa_class = "/armfa|arm_user_social_icons|arm_user_social_fields/";
        $matches = array();
        preg_match_all($fa_class, $content, $matches);

        if (count($matches) > 0 && count($matches[0]) > 0) {
            $arm_font_awesome_loaded = 1;
        }

        return $content;
    }

    function arm_perform_update_function(){
        @set_time_limit(0);
        global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_access_rules, $arm_members_class, $arm_payment_gateways, $arm_member_forms, $arm_members_directory,$arm_newdbversion,$arm_subscription_plans;
        $date = date('Y-m-d H:i:s');
        update_option('armember_update_started', $date);
        $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members']);
        /* 1) Whole badge image url was stored in database, now only image name will be stored in database */
        $sql = $wpdb->prepare("SELECT * FROM `" . $ARMember->tbl_arm_badges_achievements . "` WHERE `arm_badges_type` = 'badge' ORDER BY `arm_badges_id` DESC"); //phpcs:ignore --Reason $ARMember->tbl_arm_badges_achievements is a table name
        $results = $wpdb->get_results($sql);//phpcs:ignore --Reason $sql is a query name

        $arm_db_logs = "";

        $first_step_data = array();
        if( $wpdb->last_error != "" ){
        	$first_step_data['info_1'] = 'selecting all images of badges from database';
        	$first_step_data['query_1'] = $wpdb->last_query;
        	$first_step_data['error_1'] = $wpdb->last_error;
        }

        if (!empty($results)) {
            $badge_data = array();
            foreach ($results as $badge) {
                $badge_arr = explode('/', $badge->arm_badges_icon);
                $badgeicon = end($badge_arr);
                $badges_data = array('arm_badges_icon' => $badgeicon);
                $where = array('arm_badges_id' => $badge->arm_badges_id);
                $wpdb->update($ARMember->tbl_arm_badges_achievements, $badges_data, $where);

                if( $wpdb->last_error != "" ){
                	$first_step_data['info_2'] = 'Whole badge image url was stored in database, now only image name will be stored in database';
                	$first_step_data['query_2'] = $wpdb->last_query;
                	$first_step_data['error_2'] = $wpdb->last_error;
                }
            }
        }

        $arm_db_logs .= " Step 1 => ". stripslashes_deep(json_encode($first_step_data)). " === ";
        update_option('armember_update_logs',$arm_db_logs);
        /* 2) In bank transfer payment log table two columns are added :
         * a) arm_extra_vars ( to show trial amount )
         * b) arm_invoice_id
         *  */
        $second_step_data = array();
        $arm_tbl_bank_transfer_log = $ARMember->tbl_arm_bank_transfer_log;
        $wpdb->query("ALTER TABLE `{$arm_tbl_bank_transfer_log}` ADD `arm_extra_vars` LONGTEXT NULL AFTER `arm_currency`"); //phpcs:ignore --Reason $arm_tbl_bank_transfer_log is a table name
        if( $wpdb->last_error != "" ){
        	$second_step_data['info_1'] = "adding new column `arm_extra_vars` in bank_transfer table";
        	$second_step_data['query_1'] = $wpdb->last_query;
        	$second_step_data['error_1'] = $wpdb->last_error;
        }

        $wpdb->query("ALTER TABLE `{$arm_tbl_bank_transfer_log}`  ADD `arm_invoice_id` INT(11) NOT NULL DEFAULT '0' AFTER `arm_log_id`");//phpcs:ignore --Reason $arm_tbl_bank_transfer_log is a table name
        if( $wpdb->last_error != "" ){
        	$second_step_data['info_2'] = "adding new column `arm_invoice_id` in bank_transfer table";
        	$second_step_data['query_2'] = $wpdb->last_query;
        	$second_step_data['error_2'] = $wpdb->last_error;
        }

        $wpdb->query("ALTER TABLE `{$arm_tbl_bank_transfer_log}`  ADD `arm_payment_cycle` INT(11) NOT NULL DEFAULT '0' AFTER `arm_payment_mode`");//phpcs:ignore --Reason $arm_tbl_bank_transfer_log is a table name
        if( $wpdb->last_error != "" ){
        	$second_step_data['info_3'] = "adding new column `arm_payment_cycle` in bank_transfer table";
        	$second_step_data['query_3'] = $wpdb->last_query;
        	$second_step_data['error_3'] = $wpdb->last_error;
        }

        $arm_db_logs .= " Step 2 => ". stripslashes_deep(json_encode($second_step_data)). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 3) In payment log table one columns is added :
         * a) arm_invoice_id
         */
        $arm_tbl_arm_payment_log = $ARMember->tbl_arm_payment_log;
        $wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}`  ADD `arm_invoice_id` INT(11) NOT NULL DEFAULT '0' AFTER `arm_log_id`");//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name

        $third_step_data = array();

        if( $wpdb->last_error != "" ){
	        $third_step_data['info_1'] = 'adding new column `arm_invoid_id` in payment_log table';
        	$third_step_data['query_1'] = $wpdb->last_query;
            $third_step_data['error_1'] = $wpdb->last_error;
        }
        $arm_db_logs .= " Step 3 => ". stripslashes_deep(json_encode($third_step_data)). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 4) New column 'arm_form_field_bp_field_id' is added in  'arm_form_field table' and all old mapped fields are stored in this column */

        $arm_tbl_arm_form_field = $ARMember->tbl_arm_form_field;
        $wpdb->query("ALTER TABLE `{$arm_tbl_arm_form_field}`  ADD `arm_form_field_bp_field_id` INT(11) NOT NULL DEFAULT '0' AFTER `arm_form_field_option`"); //phpcs:ignore --Reason $arm_tbl_arm_form_field is a table name
        $fourth_step_data = array();
        if( $wpdb->last_error != "" ){
	        $fourth_step_data['info_1'] = 'adding new column `arm_form_field_bp_field_id` in arm_form_field table';
        	$fourth_step_data['query_1'] = $wpdb->last_query;
        	$fourth_step_data['error_1'] = $wpdb->last_error;
        }

        $form_field_data = $wpdb->get_results("SELECT `arm_form_field_option`,`arm_form_field_id` FROM " . $ARMember->tbl_arm_form_field);//phpcs:ignore --Reason $ARMember->tbl_arm_form_field is a table name

        if (!empty($form_field_data)) {
            foreach ($form_field_data as $form_field_option) {
                $arm_form_field_option = maybe_unserialize($form_field_option->arm_form_field_option);
                $arm_buddypress_map_field = (isset($arm_form_field_option['mapfield']) && !empty($arm_form_field_option['mapfield'])) ? $arm_form_field_option['mapfield'] : 0;
                $wpdb->update(
                        $ARMember->tbl_arm_form_field, array(
                            'arm_form_field_bp_field_id' => $arm_buddypress_map_field, // string
                        ), array('arm_form_field_id' => $form_field_option->arm_form_field_id), array('%d'), array('%d')
                );

                if( $wpdb->last_error != "" ){
                	$fourth_step_data['info_2'] = 'store old mapped fields in columns';
                	$fourth_step_data['query_2'] = $wpdb->last_query;
                	$fourth_step_data['error_2'] = $wpdb->last_error;
                }
            }
        }

        
        $arm_db_logs .= " Step 4 => ". stripslashes_deep(json_encode($fourth_step_data)). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 5) Buddypress profile cover , avatar and mapped profile page option is added in database with default value */
        $buddypress_settings_array = array('avatar_map' => 1, 'profile_cover_map' => 1, 'show_armember_profile' => 0);
        $serialized_buddypress_options = maybe_serialize($buddypress_settings_array);
        update_option('arm_buddypress_options', $serialized_buddypress_options);
        $fifth_step_data = array();

        if( $wpdb->last_error != "" ){
	        $fifth_step_data['info_1'] = 'store BuddyPress profile cover, avatar and mapped profile page option in database with default value';
        	$fifth_step_data['query_1'] = $wpdb->last_query;
        	$fifth_step_data['error_1'] = $wpdb->last_error;
        }

        $arm_db_logs .= " Step 5 => ". stripslashes_deep(json_encode($fifth_step_data)). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 6) Default invoice template is added in database option */
        $arm_default_invoice_template = '<div id="arm_invoice_div" class="entry-content ms-invoice">';
        $arm_default_invoice_template .= '<style>';
        $arm_default_invoice_template .= '#arm_invoice_div table, th, td { margin: 0; font-size: 14px; }';
        $arm_default_invoice_template .= '#arm_invoice_div table { padding: 0; border: 1px solid #DDD; width: 100%; background-color: #FFF; box-shadow: 0 1px 8px #F0F0F0; }';
        $arm_default_invoice_template .= '#arm_invoice_div th, td { border: 0; padding: 8px; }';
        $arm_default_invoice_template .= '#arm_invoice_div th { font-weight: bold; text-align: left; text-transform: none; font-size: 13px; }';
        $arm_default_invoice_template .= '#arm_invoice_div tr.alt { background-color: #F9F9F9; }';
        $arm_default_invoice_template .= '#arm_invoice_div tr.sep th, #arm_invoice_div tr.sep td { border-top: 1px solid #DDD; padding-top: 16px; }';
        $arm_default_invoice_template .= '#arm_invoice_div tr.space th, #arm_invoice_div tr.space td { padding-bottom: 16px; }';
        $arm_default_invoice_template .= '#arm_invoice_div tr.ms-inv-sep th,#arm_invoice_div tr.ms-inv-sep td { line-height: 1px; height: 1px; padding: 0; border-bottom: 1px solid #DDD; background-color: #F9F9F9; }';
        $arm_default_invoice_template .= '#arm_invoice_div .ms-inv-total .ms-inv-price { font-weight: bold; font-size: 18px; text-align: right; }';
        $arm_default_invoice_template .= '#arm_invoice_div h2 { text-align: right; padding: 0 10px 0 0;margin:0 auto; }';
        $arm_default_invoice_template .= '#arm_invoice_div h2 a { color: #000; }';
        $arm_default_invoice_template .= '</style>';
        $arm_default_invoice_template .= '<div class="ms-invoice-details ms-status-paid">';
        $arm_default_invoice_template .= '<table class="ms-purchase-table" cellspacing="0">';
        $arm_default_invoice_template .= '<tbody>';
        $arm_default_invoice_template .= '<tr class="ms-inv-title">';
        $arm_default_invoice_template .= '<td colspan="2" align="right">';
        $arm_default_invoice_template .= '<h2>Invoice {ARM_INVOICE_INVOICEID}</h2>';
        $arm_default_invoice_template .= '<div style="text-align: right; padding: 0px 10px 10px 0px;">{ARM_INVOICE_PAYMENTDATE}</div>';
        $arm_default_invoice_template .= '</td>';
        $arm_default_invoice_template .= '</tr>';


        $arm_default_invoice_template .= '<tr class="ms-inv-to alt space sep">';
        $arm_default_invoice_template .= '<th>Invoice to</th>';
        $arm_default_invoice_template .= '<td class="ms-inv-text">{ARM_INVOICE_USERFIRSTNAME} {ARM_INVOICE_USERLASTNAME} ( {ARM_INVOICE_PAYEREMAIL} )</td>';
        $arm_default_invoice_template .= '</tr>';


        $arm_default_invoice_template .= '<tr class="ms-inv-item-name space">';
        $arm_default_invoice_template .= '<th>Plan Name</th>';
        $arm_default_invoice_template .= '<td class="ms-inv-text">{ARM_INVOICE_SUBSCRIPTIONNAME}</td>';
        $arm_default_invoice_template .= '</tr>';

        $arm_default_invoice_template .= '<tr class="ms-inv-description alt space">';
        $arm_default_invoice_template .= '<th>Description</th>';
        $arm_default_invoice_template .= '<td class="ms-inv-text">{ARM_INVOICE_SUBSCRIPTIONDESCRIPTION}</td>';
        $arm_default_invoice_template .= '</tr>';

        $arm_default_invoice_template .= '<tr class="ms-inv-amount space">';
        $arm_default_invoice_template .= '<th>Plan Amount</th>';
        $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_AMOUNT}</td>';
        $arm_default_invoice_template .= '</tr>';

        $arm_default_invoice_template .= '<tr class="ms-inv-amount alt space">';
        $arm_default_invoice_template .= '<th>transaction Id</th>';
        $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TRANSACTIONID}</td>';
        $arm_default_invoice_template .= '</tr>';

        $arm_default_invoice_template .= '<tr class="ms-inv-amount space">';
        $arm_default_invoice_template .= '<th>subscription id</th>';
        $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_SUBSCRIPTIONID}</td>';
        $arm_default_invoice_template .= '</tr>';

        $arm_default_invoice_template .= '<tr class="ms-inv-amount space alt">';
        $arm_default_invoice_template .= '<th>payment gateway</th>';
        $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_GATEWAY}</td>';
        $arm_default_invoice_template .= '</tr>';

        $arm_default_invoice_template .= '<tr class="ms-inv-amount space">';
        $arm_default_invoice_template .= '<th>trial amount</th>';
        $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TRIALAMOUNT}</td>';
        $arm_default_invoice_template .= '</tr>';

        $arm_default_invoice_template .= '<tr class="ms-inv-amount space alt">';
        $arm_default_invoice_template .= '<th>trial period</th>';
        $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TRIALPERIOD}</td>';
        $arm_default_invoice_template .= '</tr>';

        $arm_default_invoice_template .= '<tr class="ms-inv-amount space">';
        $arm_default_invoice_template .= '<th>coupon code</th>';
        $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_COUPONCODE}</td>';
        $arm_default_invoice_template .= '</tr>';

        $arm_default_invoice_template .= '<tr class="ms-inv-amount alt space">';
        $arm_default_invoice_template .= '<th>coupon discount</th>';
        $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_COUPONAMOUNT}</td>';
        $arm_default_invoice_template .= '</tr>';

        $arm_default_invoice_template .= '<tr class="ms-inv-amount space">';
        $arm_default_invoice_template .= '<th>Tax Percentage</th>';
        $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TAXPERCENTAGE}</td>';
        $arm_default_invoice_template .= '</tr>';

        $arm_default_invoice_template .= '<tr class="ms-inv-amount alt space">';
        $arm_default_invoice_template .= '<th>Tax Amount</th>';
        $arm_default_invoice_template .= '<td class="ms-inv-price">{ARM_INVOICE_TAXAMOUNT}</td>';
        $arm_default_invoice_template .= '</tr>';

 

        $arm_default_invoice_template .= '</tbody>';
        $arm_default_invoice_template .= '</table>';
        $arm_default_invoice_template .= '</div>';
        $arm_default_invoice_template .= '</div>';

        $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
        $all_general_settings = isset($all_global_settings['general_settings']) ? $all_global_settings['general_settings'] : array();
        if (!empty($all_general_settings)) {
            $all_global_settings['general_settings']['arm_invoice_template'] = $arm_default_invoice_template;
            $arm_all_glbal_settings_updated = maybe_serialize($all_global_settings);
            update_option('arm_global_settings', $all_global_settings);
            $sixth_step_data = array();
            if( $wpdb->last_error != "" ){
	            $sixth_step_data['info_1'] = 'Installing new Invoice template in database';
            	$sixth_step_data['query_1'] = $wpdb->last_query;
            	$sixth_step_data['error_1'] = $wpdb->last_error;
            }
        }

        /* Creating Usermeta backup for meta 'arm_primary_status', 'arm_completed_recurring_PLAN_ID' and 'arm_next_due_payment_PLAN_ID' */

        $temp_backup_table = $wpdb->prefix.'arm_temp_usermeta';
        $original_meta_table = $wpdb->prefix.'usermeta';

        $wpdb->query("CREATE TABLE ".$temp_backup_table." LIKE ".$original_meta_table); //phpcs:ignore --Reason $temp_backup_table is a backup table name
        if( $wpdb->last_error != "" ){
        	$sixth_step_data['info_2'] = 'creating backup for user meta';
        	$sixth_step_data['query_2'] = $wpdb->last_query;
        	$sixth_step_data['error_2'] = $wpdb->last_error;
        }

        $wpdb->query($wpdb->prepare("INSERT INTO ".$temp_backup_table." (SELECT * FROM ".$original_meta_table." um WHERE um.meta_key = %s OR um.meta_key LIKE %s OR um.meta_key LIKE %s )",'arm_primary_status','%arm_completed_recurring_%','%arm_next_due_payment_%'));//phpcs:ignore --Reason $temp_backup_table and $original_meta_table are tables name

        if( $wpdb->last_error != "" ){
        	$sixth_step_data['info_3'] = 'Copy users meta data into new table';
        	$sixth_step_data['query_3'] = $wpdb->last_query;
        	$sixth_step_data['error_3'] = $wpdb->last_error;
        }

        /* 7) Update inactive users to active who have secondary status in (suspended, expired, user cancelled, failed payment ) and add user's plan in suspended
         *  update completed_recurrence and next due meta in case payment failed
         *  add failed payment history in case of manual subscription
         */

        $args = array(
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'arm_primary_status',
                    'value' => '2',
                    'compare' => '='
                ),
                array(
                    'key' => 'arm_secondary_status',
                    'value' => array('2', '3', '4', '5'),
                    'compare' => 'IN'
                )
            )
        );
        $amTotalUsers = get_users($args);
        $seventh_step_data = array();
        if( $wpdb->last_error != "" ){
	        $seventh_step_data['info_1'] = 'Fetching all inactive users from database';
        	$seventh_step_data['query_1'] = $wpdb->last_query;
        	$seventh_step_data['error_1'] = $wpdb->last_error;
        }
        if (!empty($amTotalUsers)) {
            foreach ($amTotalUsers as $usr) {
                $user_id = $usr->ID;
                $arm_secondary_status = get_user_meta($user_id, 'arm_secondary_status', true);
                arm_set_member_status($user_id, 1, $arm_secondary_status); // activate user

                if( $wpdb->last_error != "" ){
                	$seventh_step_data['info_2'] = 'Updating inactive users to active who have secondary status in (suspended, expired, user cancelled, failed payment )';
                	$seventh_step_data['query_2'] = $wpdb->last_query;
                	$seventh_step_data['error_2'] = $wpdb->last_error;
                }

                if (in_array($arm_secondary_status, array('2', '5'))) { //if user payment is failed or suspended status 
                    $arm_user_plan_id = get_user_meta($user_id, 'arm_user_plan', true);
                    if (!empty($arm_user_plan_id)) {

                        //Update suspended meta id 
                        $suspended_plan_ids = array($arm_user_plan_id);
                        update_user_meta($user_id, 'arm_user_suspended_plan_ids', $suspended_plan_ids);

                        if( $wpdb->last_error != "" ){
                        	$seventh_step_data['info_3'] = 'Add suspended plan into users who have failed or suspended status';
                        	$seventh_step_data['query_3'] = $wpdb->last_query;
                        	$seventh_step_data['error_3'] = $wpdb->last_error;
                        }
                                
                        $plan_expire_date = get_user_meta($user_id, 'arm_expire_plan_' . $arm_user_plan_id, true);
                        $now = current_time('mysql');


                        if (empty($plan_expire_date) || $plan_expire_date > strtotime($now)) { //if plan is not expired
                            $arm_next_due_date = get_user_meta($user_id, 'arm_next_due_payment_' . $arm_user_plan_id, true);
                            $arm_completed_recurring = get_user_meta($user_id, 'arm_completed_recurring_' . $arm_user_plan_id, true);
                            $arm_payment_mode = get_user_meta($user_id, 'arm_selected_payment_mode', true);
                            $arm_current_plan_detail = get_user_meta($user_id, 'arm_current_plan_detail', true);
                            $plan_data = maybe_unserialize($arm_current_plan_detail);
                            $plan_amount = $plan_data['arm_subscription_plan_amount'];
                            $user_detail = get_userdata($user_id);
                            $payer_email = $user_detail->user_email;
                            $extraParam = array();
                            $extraParam['manual_by'] = 'Paid By system';
                            if ($arm_payment_mode == 'manual_subscription') {
                                while ($arm_next_due_date < strtotime($now)) {

                                    $payment_data = array(
                                        'arm_user_id' => $user_id,
                                        'arm_first_name'=>$user_detail->first_name,
                                        'arm_last_name'=>$user_detail->last_name,
                                        'arm_plan_id' => $arm_user_plan_id,
                                        'arm_payment_gateway' => 'manual',
                                        'arm_payment_type' => 'subscription',
                                        'arm_token' => '-',
                                        'arm_payer_email' => $payer_email,
                                        'arm_transaction_payment_type' => 'subscription',
                                        'arm_transaction_status' => 'failed',
                                        'arm_payment_mode' => 'manual_subscription',
                                        'arm_payment_date' => date('Y-m-d H:i:s', $arm_next_due_date),
                                        'arm_amount' => $plan_amount,
                                        'arm_extra_vars' => maybe_serialize($extraParam),
                                    );
                                    $payment_log_id = $arm_payment_gateways->arm_save_payment_log($payment_data);


                                    if( $wpdb->last_error != "" ){
                                    	$seventh_step_data['info_6'] = 'save payment log';
                                    	$seventh_step_data['query_6'] = $wpdb->last_query;
                                    	$seventh_step_data['error_6'] = $wpdb->last_error;
                                    }


                                    $arm_completed_recurring++;
                                    update_user_meta($user_id, 'arm_completed_recurring_' . $arm_user_plan_id, $arm_completed_recurring);

                                    if( $wpdb->last_error != "" ){
                                    	$seventh_step_data['info_4'] = 'Updating user meta for auto complete recurring';
                                    	$seventh_step_data['query_4'] = $wpdb->last_query;
                                    	$seventh_step_data['error_4'] = $wpdb->last_error;
                                    }

                                    $arm_next_due_date = $arm_members_class->arm_get_next_due_date_old($user_id, $arm_user_plan_id, false);
                                    if( $wpdb->last_error != "" ){
                                    	$seventh_step_data['info_5'] = 'next due date calculation'.$arm_next_due_date.'---------user_id: '.$user_id;
                                    	$seventh_step_data['query_5'] = $wpdb->last_query;
                                    	$seventh_step_data['error_5'] = $wpdb->last_error;
                                    }
                                    if (empty($arm_next_due_date)) {
                                        break;
                                    }
                                }

                                if (!empty($arm_next_due_date)) {
                                    update_user_meta($user_id, 'arm_next_due_payment_' . $arm_user_plan_id, $arm_next_due_date);

                                    if( $wpdb->last_error != "" ){
                                    	$seventh_step_data['info_6'] = 'Updating user meta for next due payment';
                                    	$seventh_step_data['query_6'] = $wpdb->last_query;
                                    	$seventh_step_data['error_6'] = $wpdb->last_error;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $arm_db_logs .= " Step 7 => ". stripslashes_deep(json_encode($seventh_step_data)). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 8) All old users invoice ID are updated and last invoice ID stored in wp-option table in `arm_last_invoice_id` meta */
        $arm_payment_log_data = $wpdb->get_results("SELECT `arm_log_id` FROM " . $arm_tbl_arm_payment_log); //phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
        $i = 0;
        $eighth_step_data = array();
        if (!empty($arm_payment_log_data)) {
            foreach ($arm_payment_log_data as $log_data) {
                $i++;
                $arm_log_id = $log_data->arm_log_id;
                $wpdb->update(
                        $arm_tbl_arm_payment_log, array(
                    		'arm_invoice_id' => $i, // string
                        ), array('arm_log_id' => $arm_log_id), array('%d'), array('%d')
                );
                if( $wpdb->last_error != "" ){
                    $eighth_step_data['info_1_'.$i] = 'Update old users invoice ID and store last invoice id in wp-option table with `arm_last_invoice_id` meta';
                	$eighth_step_data['query_1_'.$i] = $wpdb->last_query;
                	$eighth_step_data['error_1_'.$i] = $wpdb->last_error;
                }
            }
        }

        $arm_bank_log_data = $wpdb->get_results("SELECT `arm_log_id` FROM " . $arm_tbl_bank_transfer_log); //phpcs:ignore --Reason $arm_tbl_bank_transfer_log is a table without where clause query

        if (!empty($arm_bank_log_data)) {

            foreach ($arm_bank_log_data as $bank_log_data) {
                $i++;
                $arm_bank_log_id = $bank_log_data->arm_log_id;
                $wpdb->update(
                        $arm_tbl_bank_transfer_log, array(
                    'arm_invoice_id' => $i, // string
                        ), array('arm_log_id' => $arm_bank_log_id), array('%d'), array('%d')
                );

                if( $wpdb->last_error != "" ){
                	$eighth_step_data['info_2'] = 'Update old users invoice ID and store last invoice id in wp-option table with `arm_last_invoice_id` meta for bank transfer log';
                	$eighth_step_data['query_2'] = $wpdb->last_query;
                	$eighth_step_data['error_2'] = $wpdb->last_error;
                }
            }
        }

        update_option('arm_last_invoice_id', $i);

                
        if( $wpdb->last_error != "" ){
        	$eighth_step_data['info_3'] = 'updating option `arm_last_invoice_id` ';
        	$eighth_step_data['query_3'] = $wpdb->last_query;
        	$eighth_step_data['error_3'] = $wpdb->last_error;
        }
        $arm_db_logs .= " Step 8 => ". stripslashes_deep(json_encode($eighth_step_data)). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 9) All old plan related user metas are stored in new meta  */

        $args = array(
            'meta_query' => array(
                array(
                    'key' => 'arm_user_plan',
                    'value' => 0,
                    'compare' => '>'
                ),
            )
        );

        $amTotalUsers = get_users($args);
        $ninth_step_data = array();
        if (!empty($amTotalUsers)) {
            $cn = 1;
            foreach ($amTotalUsers as $usr) {
                $user_id = $usr->ID;
                $arm_user_plan = get_user_meta($user_id, 'arm_user_plan', true);
                /*$arm_current_plan_detail = get_user_meta($user_id, 'arm_current_plan_detail', true);
                $arm_start_plan = get_user_meta($user_id, 'arm_start_plan_' . $arm_user_plan, true);
                $arm_expire_plan = get_user_meta($user_id, 'arm_expire_plan_' . $arm_user_plan, true);
                $arm_is_user_in_trial = get_user_meta($user_id, 'arm_is_plan_trial_' . $arm_user_plan, true);
                $arm_trial_start = get_user_meta($user_id, 'arm_trial_start_date', true);
                $arm_trial_end = get_user_meta($user_id, 'arm_expire_plan_trial', true);
                $arm_payment_mode = get_user_meta($user_id, 'arm_selected_payment_mode', true);
                $arm_payment_cycle = get_user_meta($user_id, 'arm_selected_payment_cycle', true);
                $arm_is_user_in_grace = get_user_meta($user_id, 'arm_is_grace_period', true);
                $arm_grace_period_end = get_user_meta($user_id, 'arm_grace_period_end', true);
                $arm_grace_period_action = get_user_meta($user_id, 'arm_grace_period_action', true);
                $arm_subscr_effective = get_user_meta($user_id, 'arm_subscr_effective', true);
                $arm_change_plan_to = get_user_meta($user_id, 'arm_change_plan_to', true);
                $arm_user_gateway = get_user_meta($user_id, 'arm_using_gateway_' . $arm_user_plan, true);
                $arm_gateway = get_user_meta($user_id, 'arm_' . $arm_user_gateway . '_' . $arm_user_plan, true);
                $arm_subscr_id = get_user_meta($user_id, 'arm_subscr_id_' . $arm_user_plan, true);
                $arm_next_due_payment = get_user_meta($user_id, 'arm_next_due_payment_' . $arm_user_plan, true);
                $arm_completed_recurring = get_user_meta($user_id, 'arm_completed_recurring_' . $arm_user_plan, true);
                $arm_sent_msgs = get_user_meta($user_id, 'arm_sent_msgs_' . $arm_user_plan, true);
                $arm_cancelled_plan = get_user_meta($user_id, 'arm_cencelled_plan_' . $arm_user_plan, true);*/

                /*$arm_user_plan_array['arm_current_plan_detail'] = maybe_unserialize($arm_current_plan_detail);
                $arm_user_plan_array['arm_start_plan'] = $arm_start_plan;
                $arm_user_plan_array['arm_expire_plan'] = $arm_expire_plan;
                $arm_user_plan_array['arm_is_trial_plan'] = $arm_is_user_in_trial;
                $arm_user_plan_array['arm_trial_start'] = $arm_trial_start;
                $arm_user_plan_array['arm_trial_end'] = $arm_trial_end;
                $arm_user_plan_array['arm_payment_mode'] = $arm_payment_mode;
                $arm_user_plan_array['arm_payment_cycle'] = $arm_payment_cycle;
                $arm_user_plan_array['arm_is_user_in_grace'] = $arm_is_user_in_grace;
                $arm_user_plan_array['arm_grace_period_end'] = $arm_grace_period_end;
                $arm_user_plan_array['arm_grace_period_action'] = $arm_grace_period_action;
                $arm_user_plan_array['arm_subscr_effective'] = $arm_subscr_effective;
                $arm_user_plan_array['arm_change_plan_to'] = $arm_change_plan_to;
                $arm_user_plan_array['arm_user_gateway'] = $arm_user_gateway;
                $arm_user_plan_array['arm_' . $arm_user_gateway] = $arm_gateway;
                $arm_user_plan_array['arm_subscr_id'] = $arm_subscr_id;
                $arm_user_plan_array['arm_next_due_payment'] = $arm_next_due_payment;
                $arm_user_plan_array['arm_completed_recurring'] = $arm_completed_recurring;
                $arm_user_plan_array['arm_sent_msgs'] = maybe_unserialize($arm_sent_msgs);
                $arm_user_plan_array['arm_cencelled_plan'] = $arm_cancelled_plan;*/
                

                $arm_user_plan_array = array();
                $arm_user_plan_id_array = array();

                $arm_user_plan_id_array[] = $arm_user_plan;

                $default_plan_array = array(
                    'arm_current_plan_detail' => array(),
                    'arm_start_plan' => '',
                    'arm_expire_plan' => '',
                    'arm_is_trial_plan' => 0,
                    'arm_trial_start' => '',
                    'arm_trial_end' => '',
                    'arm_payment_mode' => '',
                    'arm_payment_cycle' => '',
                    'arm_is_user_in_grace' => 0,
                    'arm_grace_period_end' => '',
                    'arm_grace_period_action' => '',
                    'arm_subscr_effective' => '',
                    'arm_change_plan_to' => '',
                    'arm_user_gateway' => '',
                    'arm_subscr_id' => '',
                    'arm_next_due_payment' => '',
                    'arm_completed_recurring' => '',
                    'arm_sent_msgs' => array(),
                    'arm_cencelled_plan' => '',
                    'arm_authorize_net' => '',
                    'arm_2checkout' => '',
                    'arm_stripe' => '',
                    'payment_detail' => array(),
                );

                $arm_user_metas = $wpdb->get_results("SELECT meta_key,meta_value FROM ".$wpdb->usermeta." WHERE meta_key IN ('arm_user_plan',  'arm_current_plan_detail',  'arm_start_plan_{$arm_user_plan}',  'arm_expire_plan_{$arm_user_plan}',  'arm_is_plan_trial_{$arm_user_plan}',  'arm_trial_start_date',  'arm_expire_plan_trial',  'arm_selected_payment_mode',  'arm_selected_payment_cycle',  'arm_is_grace_period',  'arm_grace_period_end', 'arm_grace_period_action',  'arm_subscr_effective',  'arm_change_plan_to',  'arm_using_gateway_{$arm_user_plan}',  'arm_subscr_id_{$arm_user_plan}',  'arm_next_due_payment_{$arm_user_plan}',  'arm_completed_recurring_{$arm_user_plan}',  'arm_sent_msgs_{$arm_user_plan}',  'arm_cencelled_plan_{$arm_user_plan}') AND user_id = {$user_id}"); //phpcs:ignore --Reason $wpdb->usermeta is a table name

                foreach($arm_user_metas as $key => $user_meta){
                    $meta_key = $user_meta->meta_key;
                    $meta_value = $user_meta->meta_value;
                    switch ($meta_key) {
                        case 'arm_user_plan':
                            break;
                        case 'arm_current_plan_detail':
                            $arm_user_plan_array['arm_current_plan_detail'] = maybe_unserialize($meta_value);
                            break;
                        case 'arm_start_plan_'.$arm_user_plan:
                            $arm_user_plan_array['arm_start_plan'] = $meta_value;
                            break;
                        case 'arm_expire_plan_'.$arm_user_plan:
                            $arm_user_plan_array['arm_expire_plan'] = $meta_value;
                            break;
                        case 'arm_is_plan_trial_'.$arm_user_plan:
                            $arm_user_plan_array['arm_is_trial_plan'] = $meta_value;
                            break;
                        case 'arm_trial_start_date':
                            $arm_user_plan_array['arm_trial_start'] = $meta_value;
                            break;
                        case 'arm_expire_plan_trial':
                            $arm_user_plan_array['arm_trial_end'] = $meta_value;
                            break;
                        case 'arm_selected_payment_mode':
                            $arm_user_plan_array['arm_payment_mode'] = $meta_value;
                            break;
                        case 'arm_selected_payment_cycle':
                            $arm_user_plan_array['arm_payment_cycle'] = $meta_value;
                            break;
                        case 'arm_is_grace_period':
                            $arm_user_plan_array['arm_is_user_in_grace'] = $meta_value;
                            break;
                        case 'arm_grace_period_end':
                            $arm_user_plan_array['arm_grace_period_end'] = $meta_value;
                            break;
                        case 'arm_grace_period_action':
                            $arm_user_plan_array['arm_grace_period_action'] = $meta_value;
                            break;
                        case 'arm_subscr_effective':
                            $arm_user_plan_array['arm_subscr_effective'] = $meta_value;
                            break;
                        case 'arm_change_plan_to':
                            $arm_user_plan_array['arm_change_plan_to'] = $meta_value;
                            break;
                        case 'arm_using_gateway_'.$arm_user_plan:
                            $arm_user_plan_array['arm_user_gateway'] = $meta_value;
                            $arm_user_gateway = $meta_value;
                            $arm_user_plan_array['arm_'.$arm_user_gateway] = get_user_meta($user_id,'arm_'.$arm_user_gateway.'_'.$arm_user_plan,true);
                            break;
                        case 'arm_subscr_id_'.$arm_user_plan:
                            $arm_user_plan_array['arm_subscr_id'] = $meta_value;
                            break;
                        case 'arm_next_due_payment_'.$arm_user_plan:
                            $arm_user_plan_array['arm_next_due_payment'] = $meta_value;
                            break;
                        case 'arm_completed_recurring_'.$arm_user_plan:
                            $arm_user_plan_array['arm_completed_recurring'] = $meta_value;
                            break;
                        case 'arm_sent_msgs_'.$arm_user_plan:
                            $arm_user_plan_array['arm_sent_msgs'] = maybe_unserialize($meta_value);
                            break;
                        case 'arm_cencelled_plan_'.$arm_user_plan:
                            $arm_user_plan_array['arm_cencelled_plan'] = $meta_value;
                            break;
                        default:
                            break;
                    }
                }

                $arm_user_plan_array = shortcode_atts($default_plan_array,$arm_user_plan_array);

                update_user_meta($user_id, 'arm_user_plan_ids', $arm_user_plan_id_array);
                if( $wpdb->last_error != "" ){
                	$ninth_step_data['info_' . $cn . '_1'] = 'Updating arm_user_plan_ids';
                	$ninth_step_data['query_' . $cn . '_1'] = $wpdb->last_query;
                	$ninth_step_data['error_' . $cn . '_1'] = $wpdb->last_error;
                }

                update_user_meta($user_id, 'arm_user_last_plan', $arm_user_plan);
                if( $wpdb->last_error != "" ){
                	$ninth_step_data['info_' . $cn . '_2'] = 'Updating arm_user_last_plan';
                	$ninth_step_data['query_' . $cn . '_2'] = $wpdb->last_query;
                	$ninth_step_data['error_' . $cn . '_2'] = $wpdb->last_error;
                }

                update_user_meta($user_id, 'arm_user_plan_' . $arm_user_plan, $arm_user_plan_array);
                if( $wpdb->last_error != "" ){
                	$ninth_step_data['info_' . $cn . '_3'] = 'Updating arm_user_plan_' . $arm_user_plan;
                	$ninth_step_data['query_' . $cn . '_3'] = $wpdb->last_query;
                	$ninth_step_data['error_' . $cn . '_3'] = $wpdb->last_error;
                }
                $cn++;
            }
            $arm_db_logs .= " Step 9 => ". json_encode($ninth_step_data). " === ";
            update_option('armember_update_logs',$arm_db_logs);
        }


        /* 10) add manual user activation email notification in default email notifications */

        $arm_manual_email_notification_content = $wpdb->get_row( $wpdb->prepare("SELECT `arm_message_content`, `arm_message_subject`, `arm_message_status` FROM `" . $ARMember->tbl_arm_auto_message . "` WHERE `arm_message_type`=%s LIMIT 1",'on_menual_activation'), ARRAY_A); //phpcs:ignore --Reason $ARMember->tbl_arm_auto_message is a table name
        if (isset($arm_manual_email_notification_content) && !empty($arm_manual_email_notification_content)) {

            $email_content = $arm_manual_email_notification_content['arm_message_content'];

            $email_content = str_replace('{ARM_MESSAGE_BLOGNAME}', '{ARM_BLOGNAME}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_BLOGURL}', '{ARM_BLOG_URL}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_USERNAME}', '{ARM_USERNAME}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_USER_ID}', '{ARM_USER_ID}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_RESET_PASSWORD_LINK}', '{ARM_RESET_PASSWORD_LINK}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_USERFIRSTNAME}', '{ARM_FIRST_NAME}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_USERLASTNAME}', '{ARM_LAST_NAME}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_USERNICENAME}', '{ARM_NAME}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_USERDISPLAYNAME}', '{ARM_NAME}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_EMAIL}', '{ARM_EMAIL}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_SUBSCRIPTIONNAME}', '{ARM_PLAN}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_SUBSCRIPTION_EXPIRE}', '{ARM_PLAN_EXPIRE}', $email_content);
            $email_content = str_replace('{ARM_MESSAGE_CURRENCY}', '{ARM_CURRENCY}', $email_content);



            $arm_manual_email_notification = array(
                'arm_template_name' => 'Manual User Activation',
                'arm_template_slug' => 'on-menual-activation',
                'arm_template_subject' => $arm_manual_email_notification_content['arm_message_subject'],
                'arm_template_content' => $email_content,
                'arm_template_status' => $arm_manual_email_notification_content['arm_message_status']
            );
        } else {
            $arm_manual_email_notification = array(
                'arm_template_name' => 'Manual User Activation',
                'arm_template_slug' => 'on-menual-activation',
                'arm_template_subject' => 'Your account has been activated at {ARM_BLOGNAME}',
                'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Your Account has been activated.</p><br><p> Please click on following link:</p><br><p>{ARM_BLOG_URL}</p><br><p>Have a nice day!</p>',
                'arm_template_status' => 1
            );
        }


        $arm_email_template_formate = array('%s', '%s', '%s', '%s', '%d');
        $wpdb->insert($ARMember->tbl_arm_email_templates, $arm_manual_email_notification, $arm_email_template_formate);

        $tenth_step_data = array();

        if( $wpdb->last_error != "" ){
	        $tenth_step_data['info_1'] = 'add manual user activation email notification in default email notifications';
	        $tenth_step_data['query_1'] = $wpdb->last_query;
	        $tenth_step_data['error_1'] = $wpdb->last_error;
        }


        $wpdb->delete($ARMember->tbl_arm_auto_message,array('arm_message_type' => 'on_menual_activation'));
        if( $wpdb->last_error != "" ){
	        $tenth_step_data['info_2'] = 'deleting on manual activation mail from `arm_auto_message` table';
	        $tenth_step_data['query_2'] = $wpdb->last_query;
	        $tenth_step_data['error_2'] = $wpdb->last_error;
        }

        $arm_db_logs .= " Step 10 => ". json_encode($tenth_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 11) Login form redirections, sign up form redirections, setup redirection, default redirection are moved to General Settings->Redirection Rules tab

          Login - redirection settings of Login form which is used in mapped login page will be saved
          sign up - form wise redirection will be saved
          setup - thank you page

         */

        $arm_redirection_settings = array();
        /* ----------------sign up  ------------------------ */
        $page_settings = $all_global_settings['page_settings'];

        $page_settings['register_page_id'] = isset($page_settings['register_page_id']) ? $page_settings['register_page_id'] : 0;
        $is_valid_reg_page = $arm_global_settings->arm_shortcode_exist_in_page('registration', $page_settings['register_page_id']);
        $register_page_id = $page_settings['register_page_id'];

        if ($is_valid_reg_page) {
            $registration_form_id = $arm_global_settings->arm_registration_form_shortcode_exist_in_page('registration', $register_page_id);
        } else {
            $registration_form_id = $arm_member_forms->arm_get_default_form_id('registration');
        }

        if (!empty($registration_form_id)) {
            $armform = new ARM_Form('id', $registration_form_id);
            $form_type = $armform->type;
            $form_settings = $armform->settings;
            $arm_redirection_settings['signup']['redirect_type'] = 'formwise';


            if ($form_settings['redirect_type'] == 'page') {
                $arm_redirection_settings['signup']['type'] = 'page';
                $registration_redirect_id = (!empty($form_settings['redirect_page'])) ? $form_settings['redirect_page'] : '0';
                $arm_redirection_settings['signup']['page_id'] = $registration_redirect_id;
                $arm_redirection_settings['signup']['url'] = '';
                $arm_redirection_settings['signup']['refferel'] ='';
            } 
            else if ($form_settings['redirect_type'] == 'referral') {
                $arm_redirection_settings['signup']['type'] = 'referral';
                $registration_redirect_url = (!empty($form_settings['referral_url'])) ? $form_settings['referral_url'] : ARM_HOME_URL;
                $arm_redirection_settings['signup']['page_id'] = '';
                $arm_redirection_settings['signup']['url'] = '';
                $arm_redirection_settings['signup']['refferel'] = $registration_redirect_url;
            
            }  else {
                $arm_redirection_settings['signup']['type'] = 'url';
                $registration_redirect_url = (!empty($form_settings['redirect_url'])) ? $form_settings['redirect_url'] : ARM_HOME_URL;
                $arm_redirection_settings['signup']['page_id'] = '';
                $arm_redirection_settings['signup']['url'] = $registration_redirect_url;
                $arm_redirection_settings['signup']['refferel'] = '';
            }

            $registration_forms = $wpdb->get_results( $wpdb->prepare("SELECT `arm_form_id`, `arm_form_label`, `arm_form_slug`, `arm_is_default`, `arm_form_updated_date` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_type`=%s ORDER BY `arm_form_id` DESC",'registration'), ARRAY_A);//phpcs:ignore --Reason $ARMember->tbl_arm_forms is a table name

            if (!empty($registration_forms)) {

                foreach ($registration_forms as $_form) {
                    $_fid = $_form['arm_form_id'];
                    if (!empty($_fid)) {
                        $regarmform = new ARM_Form('id', $_fid);
                        $regform_type = $regarmform->type;
                        $regform_settings = $regarmform->settings;
                        if ($regform_settings['redirect_type'] == 'page') {
                            $registration_redirect_id_new = (!empty($regform_settings['redirect_page'])) ? $regform_settings['redirect_page'] : '';
                            if (empty($registration_redirect_id_new) || $registration_redirect_id_new == 0) {
                                continue;
                            } else {
                                $arm_redirection_settings['signup']['conditional_redirect'][] = array('form_id' => $_fid,
                                    'url' => $registration_redirect_id_new);
                            }
                        } else {
                            $registration_redirect_url_new = (!empty($regform_settings['redirect_url'])) ? $regform_settings['redirect_url'] : '';
                                            if(isset($registration_redirect_url_new) && !empty($registration_redirect_url_new) && $registration_redirect_url_new != ''){
                                                    $registration_redirect_id_reg_new = url_to_postid($registration_redirect_url_new);
                                                $arm_redirection_settings['signup']['conditional_redirect'][] = array('form_id' => $_fid,
                                                    'url' => $registration_redirect_id_reg_new);
                                            }
                                            else{
                                                    continue;
                                            }
                        }
                    }
                }
            }
        }

        /* ---------------- log in  ------------------------ */

        $page_settings['login_page_id'] = isset($page_settings['login_page_id']) ? $page_settings['login_page_id'] : 0;
        $is_valid_login_page = $arm_global_settings->arm_shortcode_exist_in_page('login', $page_settings['login_page_id']);
        $login_page_id = $page_settings['login_page_id'];

        if ($is_valid_login_page) {
            $login_form_id = $arm_global_settings->arm_registration_form_shortcode_exist_in_page('login', $login_page_id);
        } else {
            $login_form_id = $arm_member_forms->arm_get_default_form_id('login');
        }

        if (!empty($login_form_id)) {
            $armform = new ARM_Form('id', $login_form_id);
            $form_type = $armform->type;
            $form_settings = $armform->settings;
            if ($form_settings['redirect_type'] == 'page') {
                $arm_redirection_settings['login']['main_type'] = 'fixed';
                $arm_redirection_settings['login']['type'] = 'page';
                $login_redirect_id = (!empty($form_settings['redirect_page'])) ? $form_settings['redirect_page'] : '0';
                $arm_redirection_settings['login']['page_id'] = $login_redirect_id;
                $arm_redirection_settings['login']['url'] = '';
                $arm_redirection_settings['login']['refferel'] = '';
                $arm_redirection_settings['login']['conditional_redirect'] = '';
            } else if ($form_settings['redirect_type'] == 'referral') {
                $arm_redirection_settings['login']['main_type'] = 'fixed';
                $arm_redirection_settings['login']['type'] = 'referral';
                $login_redirect_url = (!empty($form_settings['referral_url'])) ? $form_settings['referral_url'] : ARM_HOME_URL;
                $arm_redirection_settings['login']['page_id'] = '';
                $arm_redirection_settings['login']['url'] = '';
                $arm_redirection_settings['login']['refferel'] = $login_redirect_url;
                $arm_redirection_settings['login']['conditional_redirect'] = '';
            } else if ($form_settings['redirect_type'] == 'conditional_redirect') {
                $arm_redirection_settings['login']['main_type'] = 'conditional_redirect';
                $arm_redirection_settings['login']['type'] = 'conditional_redirect';
                $arm_redirection_settings['login']['page_id'] = '';
                $arm_redirection_settings['login']['url'] = '';
                $arm_redirection_settings['login']['refferel'] = '';

                $login_default_redirect_url = (!empty($form_settings['conditional_redirect_url'])) ? $form_settings['conditional_redirect_url'] : ARM_HOME_URL;
                $login_redirection_conditions = (isset($form_settings['conditional_redirects']) && !empty($form_settings['conditional_redirects'])) ? $form_settings['conditional_redirects'] : array();

                if (!empty($login_redirection_conditions) && is_array($login_redirection_conditions)) {
                    foreach ($login_redirection_conditions as $login_redirection_condition) {
                        $conditional_plan_id = $login_redirection_condition['plan_id'];
                        $conditional_redirect = url_to_postid($login_redirection_condition['redirect']);
                        if (!empty($conditional_redirect)) {
                            $arm_redirection_settings['login']['conditional_redirect'][] = array('plan_id' => $conditional_plan_id,
                                'condition' => '',
                                'expire' => 0,
                                'url' => $conditional_redirect);
                        }
                    }
                } else {
                    $arm_redirection_settings['login']['conditional_redirect'][] = array('plan_id' => 0,
                        'condition' => '',
                        'expire' => 0,
                        'url' => ARM_HOME_URL);
                }
                $arm_redirection_settings['login']['conditional_redirect']['default'] = $login_default_redirect_url;
            } else {
                $arm_redirection_settings['login']['main_type'] = 'fixed';
                $arm_redirection_settings['login']['type'] = 'url';
                $login_redirect_url = (!empty($form_settings['redirect_url'])) ? $form_settings['redirect_url'] : ARM_HOME_URL;
                $arm_redirection_settings['login']['page_id'] = '';
                $arm_redirection_settings['login']['url'] = $login_redirect_url;
                $arm_redirection_settings['login']['refferel'] = '';
                $arm_redirection_settings['login']['conditional_redirect'] = '';
            }
        }

        /* ---------------- setup  ------------------------ */
        $globalSettings = $arm_global_settings->global_settings;
        $ty_pageid = isset($globalSettings['thank_you_page_id']) ? $globalSettings['thank_you_page_id'] : 0;
        $arm_redirection_settings['setup_signup']['type'] = $arm_redirection_settings['setup_change']['type'] = $arm_redirection_settings['setup_renew']['type'] = 'page';
        $arm_redirection_settings['setup_signup']['page_id'] = $arm_redirection_settings['setup_change']['page_id'] = $arm_redirection_settings['setup_renew']['page_id'] = $ty_pageid;
        $arm_redirection_settings['setup_signup']['url'] = $arm_redirection_settings['setup_change']['url'] = $arm_redirection_settings['setup_renew']['url'] = ARM_HOME_URL;
        $redirection_settings['setup']['default'] = ARM_HOME_URL;

        /* ----------------social connect  ------------------------ */
        $arm_redirection_settings['social']['type'] = 'page';
        $edit_profile_page_id = isset($page_settings['edit_profile_page_id']) ? $page_settings['edit_profile_page_id'] : 0;
        $arm_redirection_settings['social']['page_id'] = $edit_profile_page_id;
        $arm_redirection_settings['social']['url'] = ARM_HOME_URL;

        /* ---------------- default redirection rules  ------------------------ */

        $default_rules = $arm_access_rules->arm_get_default_access_rules();
        $arm_redirection_settings['default_access_rules']['logged_in']['type'] = (!empty($default_rules['redirect_logged_in_user']['type'])) ? $default_rules['redirect_logged_in_user']['type'] : 'home';
        $arm_redirection_settings['default_access_rules']['blocked']['type'] = (!empty($default_rules['redirect_blocked_user']['type'])) ? $default_rules['redirect_blocked_user']['type'] : 'home';
        //$arm_redirection_settings['default_access_rules']['pending']['type'] = (!empty($default_rules['redirect_pending_user']['type'])) ? $default_rules['redirect_pending_user']['type'] : 'home';
        $arm_redirection_settings['default_access_rules']['drip']['type'] = (!empty($default_rules['redirect_drip']['type'])) ? $default_rules['redirect_drip']['type'] : 'home';
        $arm_redirection_settings['default_access_rules']['non_logged_in']['type'] = (!empty($default_rules['redirect']['type'])) ? $default_rules['redirect']['type'] : 'home';

        $arm_redirection_settings['default_access_rules']['non_logged_in']['redirect_to'] = $default_rules['redirect']['page_id'];
        $arm_redirection_settings['default_access_rules']['logged_in']['redirect_to'] = $default_rules['redirect_logged_in_user']['page_id'];
        $arm_redirection_settings['default_access_rules']['blocked']['redirect_to'] = $default_rules['redirect_blocked_user']['page_id'];
        //$arm_redirection_settings['default_access_rules']['pending']['redirect_to'] = $default_rules['redirect_pending_user']['page_id'];
        $arm_redirection_settings['default_access_rules']['drip']['redirect_to'] = isset($default_rules['redirect_drip']['page_id']) ? $default_rules['redirect_drip']['page_id'] : '';

        $arm_redirection_settings = maybe_serialize($arm_redirection_settings);
        update_option('arm_redirection_settings', $arm_redirection_settings);
        $eleventh_step_data = array();

        if( $wpdb->last_error != "" ){
        	$eleventh_step_data['info_1'] = 'updating redirection settings';
            $eleventh_step_data['redirection_data'] = $arm_redirection_settings;
            $eleventh_step_data['query_1'] = $wpdb->last_query;
            $eleventh_step_data['error_1'] = $wpdb->last_error;
        }
        $arm_db_logs .= " Step 11 => ". json_encode($eleventh_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 12) Change 'arm_payment_date' column type from 'text' to 'datetime' of arm_payment_log' table.
          for that first create temporary table
         */

        if ($wpdb->has_cap('collation')) {

            if (!empty($wpdb->charset))
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

            if (!empty($wpdb->collate))
                $charset_collate .= " COLLATE $wpdb->collate";
        }

        $backup_table = $wpdb->prefix . 'arm_payment_log_temp';
        $original_tab = $wpdb->prefix . 'arm_payment_log';

        $wpdb->query("CREATE TABLE ".$backup_table." LIKE ".$original_tab); //phpcs:ignore --Reason backup_table and original_tab are table names

        $twelth_step_data = array();
        if( $wpdb->last_error != "" ){
	        $twelth_step_data['info_1'] = 'Creating Temporary Backup';
	        $twelth_step_data['query_1'] = $wpdb->last_query;
	        $twelth_step_data['error_1'] = $wpdb->last_error;
        }

        $wpdb->query("INSERT INTO ".$backup_table." SELECT * FROM ".$original_tab); //phpcs:ignore --Reason $backup_table and $original_tab are a table names
        if( $wpdb->last_error != "" ){
	        $twelth_step_data['info_2'] = 'Copy whole table data into new table';
	        $twelth_step_data['query_2'] = $wpdb->last_query;
	        $twelth_step_data['error_2'] = $wpdb->last_error;
	    }
        /* alter table */

        $wpdb->query("ALTER TABLE `" . $arm_tbl_arm_payment_log . "` MODIFY `arm_payment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'"); //phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name 
        if( $wpdb->last_error != "" ){
	        $twelth_step_data['info_3'] = 'setting default value for payment_date column';
	        $twelth_step_data['query_3'] = $wpdb->last_query;
	        $twelth_step_data['error_3'] = $wpdb->last_error;
	    }

        if (!empty($paypal_pro_array)) {
            foreach ($paypal_pro_array as $log_id => $payment_date) {
                $new_payment_date = substr($payment_date, 4, 4) . "-" . substr($payment_date, 0, 2) . "-" . substr($payment_date, 2, 2) . " 00:00:00";
                $wpdb->update($arm_tbl_arm_payment_log, array('arm_payment_date' => $new_payment_date), array('arm_log_id' => $log_id), array('%s'), array('%d'));
            }
        }

        $arm_db_logs .= " Step 12 => ". json_encode($twelth_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);


        /* 13) add arm_comleted_recurring and arm_next_due_date meta in case of auto_debit_subscription */

        $args = array(
            'meta_query' => array(
                array(
                    'key' => 'arm_user_plan_ids',
                    'value' => '',
                    'compare' => '!='
                ),
            )
        );

        $amTotalUsers = get_users($args);
        if (!empty($amTotalUsers)) {
            foreach ($amTotalUsers as $usr) {
                $user_id = $usr->ID;
                $arm_user_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $arm_user_plan_ids = !empty($arm_user_plan_ids) ? $arm_user_plan_ids : array();
                if (!empty($arm_user_plan_ids)) {
                    foreach ($arm_user_plan_ids as $arm_user_plan_id) {
                        $user_plan_data = get_user_meta($user_id, 'arm_user_plan_' . $arm_user_plan_id, true);

                        $planDetail = $user_plan_data['arm_current_plan_detail'];
                        if (!empty($planDetail)) {
                            $plan = new ARM_Plan(0);
                            $plan->init((object) $planDetail);
                        } else {
                            $plan = new ARM_Plan($arm_user_plan_id);
                        }

                        if ($plan->is_recurring()) {
                            $arm_user_payment_mode = $user_plan_data['arm_payment_mode'];
                            $arm_user_payment_gateway = $user_plan_data['arm_user_gateway'];
                            if ($arm_user_payment_mode == 'auto_debit_subscription') {

                                $actual_trial_start_date = isset($user_plan_data['arm_trial_start']) ? $user_plan_data['arm_trial_start'] : '';
                                $actual_trial_end_date = isset($user_plan_data['arm_trial_end']) ? $user_plan_data['arm_trial_end'] : '';
                                $actual_plan_start_date = isset($user_plan_data['arm_start_plan']) ? $user_plan_data['arm_start_plan'] : '';

                                $plan_start_date = '';
                                if (!empty($actual_plan_start_date)) {
                                    $plan_start_date = strtotime("-30 Minutes", $actual_plan_start_date);
                                    $plan_start_date = date('Y-m-d H:i:s', $plan_start_date);
                                }


                                $trial_start_date = '';
                                if (!empty($actual_trial_start_date)) {
                                    $trial_start_date = strtotime("-30 Minutes", $actual_trial_start_date);
                                    $trial_start_date = date('Y-m-d H:i:s', $trial_start_date);
                                }

                                $arm_user_payment_cycle = $user_plan_data['arm_payment_cycle'];

                                if (!empty($actual_trial_start_date) && !empty($actual_trial_end_date)) {

                                    $total_completed_recurrence = $wpdb->get_var($wpdb->prepare("SELECT COUNT(`arm_log_id`) FROM `" . $arm_tbl_arm_payment_log . "` WHERE `arm_payment_date` >= %s AND `arm_user_id` = %d AND `arm_payment_gateway`=%s AND `arm_display_log`=%d", $trial_start_date, $user_id, $arm_user_payment_gateway, 1)); //phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                                    $total_completed_recurrence--;
                                    if ($arm_user_payment_gateway == 'stripe') {
                                        $recurring_data = $plan->prepare_recurring_data($arm_user_payment_cycle);
                                        $trial_amount = $recurring_data['trial']['amount'];
                                        if ($trial_amount > 0) {
                                            $total_completed_recurrence--;
                                        }
                                    }

                                    $allow_trial = true;
                                } else {
                                    $total_completed_recurrence = $wpdb->get_var($wpdb->prepare("SELECT COUNT(`arm_log_id`) FROM `" . $arm_tbl_arm_payment_log . "` WHERE `arm_payment_date` >= %s AND `arm_user_id` = %d AND `arm_payment_gateway`=%s AND `arm_display_log`=%d", $plan_start_date, $user_id, $arm_user_payment_gateway, 1)); //phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
                                    $allow_trial = false;
                                }
                                $user_plan_data['arm_completed_recurring'] = $total_completed_recurrence;
                                update_user_meta($user_id, 'arm_user_plan_' . $arm_user_plan_id, $user_plan_data);

                                $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $arm_user_plan_id, $allow_trial, $arm_user_payment_cycle);
                                $user_plan_data['arm_next_due_payment'] = $arm_next_payment_date;
                                update_user_meta($user_id, 'arm_user_plan_' . $arm_user_plan_id, $user_plan_data);
                            }
                        }
                    }
                }
            }
        }
        $thirteenth_step_data = array();

        if( $wpdb->last_error != "" ){
	        $thirteenth_step_data['info_1'] = ' add arm_comleted_recurring and arm_next_due_date meta in case of auto_debit_subscription';
	        $thirteenth_step_data['query_1'] = $wpdb->last_query;
	        $thirteenth_step_data['error_1'] = $wpdb->last_error;
	    }
        
        $arm_db_logs .= " Step 13 => ". json_encode($thirteenth_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

                
        /* 14) Add arm_subscription plan, arm_template_html, arm_ref_template, arm_html_before_fields, arm_html_after_fields, arm_enable_admin_profile column in arm_member_templates table */

        $arm_member_template_tbl = $ARMember->tbl_arm_member_templates;

        $wpdb->query("ALTER TABLE `" . $arm_member_template_tbl . "` ADD `arm_subscription_plan` TEXT NULL AFTER `arm_default`"); //phpcs:ignore --Reason $arm_member_template_tbl is a table name
        $fourtinth_step_data = array();
        if( $wpdb->last_error != "" ){
	        $fourtinth_step_data['info_1'] = 'adding column `arm_subscription_plan` in member_template_table';
	        $fourtinth_step_data['query_1'] = $wpdb->last_query;
	        $fourtinth_step_data['error_1'] = $wpdb->last_error;
        }

        $wpdb->query("ALTER TABLE `" . $arm_member_template_tbl . "` ADD `arm_template_html` longtext AFTER `arm_subscription_plan`"); //phpcs:ignore --Reason $arm_member_template_tbl is table name
        
        if( $wpdb->last_error != "" ){
	        $fourtinth_step_data['info_2'] = 'adding column `arm_template_html` in member_template_table';
	        $fourtinth_step_data['query_2'] = $wpdb->last_query;
	        $fourtinth_step_data['error_2'] = $wpdb->last_error;
	    }

        $wpdb->query("ALTER TABLE `" . $arm_member_template_tbl . "` ADD `arm_ref_template` int(11) NOT NULL DEFAULT '0' AFTER `arm_default`");//phpcs:ignore --Reason $arm_member_template_tbl is table name
        
        if( $wpdb->last_error != "" ){       
	        $fourtinth_step_data['info_3'] = 'adding column `arm_ref_template` in member_template_table';
	        $fourtinth_step_data['query_3'] = $wpdb->last_query;
	        $fourtinth_step_data['error_3'] = $wpdb->last_error;
	    }

        $wpdb->query("ALTER TABLE `" . $arm_member_template_tbl . "` ADD `arm_html_before_fields` longtext AFTER `arm_options`");//phpcs:ignore --Reason $arm_member_template_tbl is table name
        
        if( $wpdb->last_error != "" ){
	        $fourtinth_step_data['info_4'] = 'adding column `arm_html_before_fields` in member_template_table';
	        $fourtinth_step_data['query_4'] = $wpdb->last_query;
	        $fourtinth_step_data['error_4'] = $wpdb->last_error;
	    }

        $wpdb->query("ALTER TABLE `" . $arm_member_template_tbl . "` ADD `arm_html_after_fields` longtext AFTER `arm_html_before_fields`");//phpcs:ignore --Reason $arm_member_template_tbl is table name
        
        if( $wpdb->last_error != "" ){
	        $fourtinth_step_data['info_5'] = 'adding column `arm_html_after_fields` in member_template_table';
	        $fourtinth_step_data['query_5'] = $wpdb->last_query;
	        $fourtinth_step_data['error_5'] = $wpdb->last_error;
	    }
                

        $wpdb->query("ALTER TABLE `" . $arm_member_template_tbl . "` ADD `arm_enable_admin_profile` int(1) NOT NULL DEFAULT '0' AFTER `arm_html_after_fields`");//phpcs:ignore --Reason $arm_member_template_tbl is table name
        
        if( $wpdb->last_error != "" ){
        	$fourtinth_step_data['info_6'] = 'adding column `arm_enable_admin_profile` in member_template_table';
        	$fourtinth_step_data['query_6'] = $wpdb->last_query;
        	$fourtinth_step_data['error_6'] = $wpdb->last_error;
        }
                
        $arm_db_logs .= " Step 14 => ". json_encode($fourtinth_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 15) update existing all user's meta 'arm_firsttime_login' for first time login */
        $fiftinth_step_data = array();
        $users = get_users(array('fields' => 'ID'));
        if (!empty($users)) {
            $un = 1;
            foreach ($users as $user) {
                $user_update = update_user_meta($user, 'arm_firsttime_login', 1);
                if( $wpdb->last_error != "" ){
	                $fiftinth_step_data['info_1_' . $un] = 'update existing all users meta `arm_firsttime_login` for first time login';
	                $fiftinth_step_data['query_1_' . $un] = $wpdb->last_query;
	                $fiftinth_step_data['error_1_' . $un] = $wpdb->last_error;
	            }
                $un++;
            }
        }
        $arm_db_logs .= " Step 15 => ". json_encode($fiftinth_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);


        /* 16) set log file permission 

        $log_response_file = MEMBERSHIP_DIR . '/log/response.txt';
        @chmod($log_response_file, 0755);
        $log_dron_file = MEMBERSHIP_DIR . '/log/cron_log.txt';
        @chmod($log_dron_file, 0755);
        $log_payment_response_file = MEMBERSHIP_DIR . '/log/payment_response.txt';
        @chmod($log_payment_response_file, 0755);
        $log_restriction_response_file = MEMBERSHIP_DIR . '/log/restriction_response.txt';
        @chmod($log_restriction_response_file, 0755);*/

        $arm_db_logs .= " Step 16 => no any database query executed === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 17) add troubleshoot link for disabling rename wp-admin */
        $arm_hide_admin_rand_no = wp_rand();
        update_option('arm_hide_wp_amin_disable', $arm_hide_admin_rand_no);
        $seventeen_step_data = array();
        if( $wpdb->last_error != "" ){
	        $seventeen_step_data['info_1'] = 'add troubleshoot link for disabling rename wp-admin';
	        $seventeen_step_data['query_1'] = $wpdb->last_query;
	        $seventeen_step_data['error_1'] = $wpdb->last_error;
	    }
        $arm_db_logs .= " Step 17 => ". json_encode($seventeen_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 16) add 'arm_show_old_items' column in drip rule table */
        //$arm_tbl_arm_drip_rule = $ARMember->tbl_arm_drip_rules;
        //$wpdb->query("ALTER TABLE `" . $arm_tbl_arm_drip_rule . "` ADD `arm_show_old_items` INT(11) NOT NULL DEFAULT '0' AFTER `arm_rule_type`");

        /* 18) update user profile field labels in profile template options in database */
        $profile_data = $wpdb->get_row($wpdb->prepare('SELECT `arm_options` FROM `' . $arm_member_template_tbl . '` WHERE arm_id = %d', 1));//phpcs:ignore --Reason $arm_member_template_tbl is table name
        $options = maybe_unserialize($profile_data->arm_options);
        $profile_fields = $options['profile_fields'];
        $dbProfileFields = $arm_members_directory->arm_template_profile_fields();
        $labels = array();
        foreach ($profile_fields as $k => $v) {
            $labels[$k] = isset($dbProfileFields[$k]) ? $dbProfileFields[$k]['label'] : '';
        }
        $options['label'] = $labels;
        $wpdb->update($arm_member_template_tbl, array('arm_options' => maybe_serialize($options)), array('arm_id' => 1));
        $eighteen_step_data = array();
        if( $wpdb->last_error != "" ){
	        $eighteen_step_data['info_1'] = 'update user profile field labels in profile template options in database';
	        $eighteen_step_data['query_1'] = $wpdb->last_query;
	        $eighteen_step_data['error_1'] = $wpdb->last_error;
        }
        $arm_db_logs .= " Step 18 => ". json_encode($eighteen_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 19) Migrate for display admin users field */
        $profile_temp_data = $wpdb->get_row($wpdb->prepare("SELECT `arm_options`,`arm_slug` FROM `" . $arm_member_template_tbl . "` WHERE arm_id = %d", 1));//phpcs:ignore --Reason $arm_member_template_tbl is table name
        $options = maybe_unserialize($profile_temp_data->arm_options);
        $profileTemplateOptions = $options;
        $templateSlug = $profile_temp_data->arm_slug;
        $display_admin_users = isset($options['show_admin_users']) ? $options['show_admin_users'] : 0;
        if ($profileTemplateOptions['default_cover'] != '') {
            $profileTemplateOptions['default_cover_photo'] = 1;
        }
        $profileTemplateOptions['hide_empty_profile_fields'] = 1;
        $wpdb->update($arm_member_template_tbl, array('arm_options' => maybe_serialize($profileTemplateOptions)), array('arm_id' => 1));
        $ninteen_step_data = array();
        if( $wpdb->last_error != "" ){
	        $ninteen_step_data['info_1'] = 'pdate user profile field options in profile template options in database';
	        $ninteen_step_data['query_1'] = $wpdb->last_query;
	        $ninteen_step_data['error_1'] = $wpdb->last_error;
        }

        $wpdb->update($arm_member_template_tbl, array('arm_enable_admin_profile' => $display_admin_users), array('arm_id' => 1));

        if( $wpdb->last_error != "" ){
	        $ninteen_step_data['info_2'] = 'update show_admin_profile option in database';
	        $ninteen_step_data['query_2'] = $wpdb->last_query;
	        $ninteen_step_data['error_2'] = $wpdb->last_error;
	    }
                
        $arm_db_logs .= " Step 19 => ". json_encode($ninteen_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        /* 20) Update profile template for default template */
        if ($templateSlug == 'profiletemplate1') {
            $arm_template_html = '<div class="arm_profile_defail_container arm_profile_tabs_container">
        <div class="arm_profile_detail_wrapper">
          <div class="arm_profile_picture_block armCoverPhoto" style="{ARM_Profile_Cover_Image}">
            <div class="arm_profile_picture_block_inner">
              <div class="arm_user_avatar">{ARM_Profile_Avatar_Image}</div>
              <div class="arm_profile_separator"></div>
              <div class="arm_profile_header_info"> <span class="arm_profile_name_link">{ARM_Profile_User_Name}</span>
                {ARM_Profile_Badges}
                <span class="arm_user_last_active_text">{ARM_Profile_Join_Date}</span>
                <div class="social_profile_fields">
                  {ARM_Profile_Social_Icons}
                </div>
              </div>
            </div>
              {ARM_Cover_Upload_Button}
          </div>
          <div class="armclear"></div>
          {ARM_PROFILE_FIELDS_BEFORE_CONTENT}
          <div class="arm_profile_field_before_content_wrapper"></div>
          <div class="arm_profile_tab_detail" data-tab="general">
            <div class="arm_general_info_container">
              <table class="arm_profile_detail_tbl">
                <tbody>';
            foreach ($options['profile_fields'] as $k => $value) {
                $arm_template_html .= "<tr>";
                $arm_template_html .= "<td>" . $options['label'][$k] . "</td>";
                $arm_template_html .= "<td>[arm_usermeta meta='" . $k . "']</td>";
                $arm_template_html .= "</tr>";
            }
            $arm_template_html .= '</tbody>
              </table>
            </div>
          </div>
          <div class="arm_profile_field_after_content_wrapper"></div>
          {ARM_PROFILE_FIELDS_AFTER_CONTENT}
        </div>
        </div>
        <div class="armclear"></div>';
        } else if ($templateSlug == 'profiletemplate2') {
            $arm_template_html = '<div class="arm_profile_detail_wrapper">
        <div class="arm_profile_picture_block armCoverPhoto" style="{ARM_Profile_Cover_Image}">
            <div class="arm_profile_picture_block_inner">
                <div class="armclear"></div>
                <div class="arm_profile_header_info arm_profile_header_bottom_box">
                    <span class="arm_profile_name_link">
                        {ARM_Profile_User_Name}
                    </span>
                    {ARM_Profile_Badges}
                </div>
                <span class="arm_user_last_active_text">{ARM_Profile_Join_Date}</span>
                <div class="armclear"></div>
                <div class="arm_user_social_icons_all social_profile_fields arm_mobile">
                    {ARM_Profile_Social_Icons_Mobile}
                </div>
                <div class="arm_profile_header_top_box">
                    <div class="arm_user_social_icons_left arm_desktop">
                        {ARM_Profile_Social_Icons_Left}
                    </div>
                    <div class="arm_user_avatar">
                        {ARM_Profile_Avatar_Image}
                    </div>
                    <div class="arm_user_social_icons_right arm_desktop">
                                
                            {ARM_Profile_Social_Icons_Right}
                               
                    </div>
                </div>
            </div>
            {ARM_Cover_Upload_Button}
        </div>
        <div class="arm_profile_defail_container arm_profile_tabs_container">
            {ARM_PROFILE_FIELDS_BEFORE_CONTENT}
            <div class="arm_profile_field_before_content_wrapper"></div>
            <div class="arm_profile_tab_detail" data-tab="general">
                <div class="arm_general_info_container">
                    <table class="arm_profile_detail_tbl">
                        <tbody>';
            foreach ($profileTemplateOptions['profile_fields'] as $k => $value) {
                $arm_template_html .= "<tr>";
                $arm_template_html .= "<td>" . $profileTemplateOptions['label'][$k] . "</td>";
                $arm_template_html .= "<td>[arm_usermeta meta='" . $k . "']</td>";
                $arm_template_html .= "</tr>";
            }
            $arm_template_html .= '</tbody>
                    </table>
                </div>
            </div>
            <div class="arm_profile_field_after_content_wrapper"></div>
            {ARM_PROFILE_FIELDS_AFTER_CONTENT}
        </div>
        </div><div class="armclear"></div>';
        } else if ($templateSlug == 'profiletemplate3') {
            $arm_template_html = '<div class="arm_profile_detail_wrapper">
        <div class="arm_profile_picture_block armCoverPhoto" style="{ARM_Profile_Cover_Image}">
            <div class="arm_profile_picture_block_inner">
                <div class="arm_profile_header_info">
                    <span class="arm_profile_name_link">{ARM_Profile_User_Name}</span>
                        {ARM_Profile_Badges}
                    <div class="armclear"></div>
                    <span class="arm_user_last_active_text">{ARM_Profile_Join_Date}</span>
                </div>
                <div class="social_profile_fields">
                    {ARM_Profile_Social_Icons}
                </div>
                <div class="armclear"></div>
            </div><div class="arm_user_avatar">
                {ARM_Profile_Avatar_Image}
            </div> {ARM_Cover_Upload_Button}</div><div class="arm_profile_defail_container arm_profile_tabs_container">
                {ARM_PROFILE_FIELDS_BEFORE_CONTENT}
                <div class="arm_profile_field_before_content_wrapper"></div>
                <div class="arm_profile_tab_detail" data-tab="general">
                    <div class="arm_general_info_container">
                        <table class="arm_profile_detail_tbl">
                            <tbody>';
            foreach ($options['profile_fields'] as $k => $value) {
                $arm_template_html .= "<tr>";
                $arm_template_html .= "<td>" . $options['label'][$k] . "</td>";
                $arm_template_html .= "<td>[arm_usermeta meta='" . $k . "']</td>";
                $arm_template_html .= "</tr>";
            }
            $arm_template_html .= '</tbody>
                        </table>
                    </div>
                </div>
                <div class="arm_profile_field_after_content_wrapper"></div>
                {ARM_PROFILE_FIELDS_AFTER_CONTENT}
            </div>
        </div><div class="armclear"></div>';
        } else if ($templateSlug == 'profiletemplate4') {
            $arm_template_html = '<div class="arm_profile_defail_container arm_profile_tabs_container">
        <div class="arm_profile_detail_wrapper">
            <div class="arm_profile_picture_block armCoverPhoto" style="{ARM_Profile_Cover_Image}">

                <div class="arm_profile_picture_block_inner">
                    <div class="arm_user_avatar">
                        {ARM_Profile_Avatar_Image}
                    </div>
                    <div class="arm_profile_separator"></div>
                    <div class="arm_profile_header_info">
                        <span class="arm_profile_name_link">{ARM_Profile_User_Name}</span>
                                
                            {ARM_Profile_Badges}
                               
                        <span class="arm_user_last_active_text">{ARM_Profile_Join_Date}</span>
                        <div class="social_profile_fields">
                            {ARM_Profile_Social_Icons}
                        </div>
                    </div>
                </div>
                {ARM_Cover_Upload_Button}
            </div>
            <div class="armclear"></div>
            {ARM_PROFILE_FIELDS_BEFORE_CONTENT}
            <div class="arm_profile_field_before_content_wrapper"></div>
            <div class="arm_profile_tab_detail" data-tab="general">
                <div class="arm_general_info_container">
                    <table class="arm_profile_detail_tbl">
                        <tbody>';
            foreach ($options['profile_fields'] as $k => $value) {
                $arm_template_html .= "<tr>";
                $arm_template_html .= "<td>" . $options['label'][$k] . "</td>";
                $arm_template_html .= "<td>[arm_usermeta meta='" . $k . "']</td>";
                $arm_template_html .= "</tr>";
            }
            $arm_template_html .= '</tbody>
                    </table>
                </div>
            </div>
            <div class="arm_profile_field_before_content_wrapper"></div>
            {ARM_PROFILE_FIELDS_AFTER_CONTENT}
        </div>
        </div><div class="armclear"></div>';
        } else {
            $arm_template_html = '<div class="arm_profile_detail_wrapper">
        <div class="arm_profile_picture_block armCoverPhoto" style="{ARM_Profile_Cover_Image}">
            <div class="arm_profile_picture_block_inner">
                <div class="armclear"></div>
                <div class="arm_profile_header_info arm_profile_header_bottom_box">
                    <span class="arm_profile_name_link">
                        {ARM_Profile_User_Name}
                    </span>
                    {ARM_Profile_Badges}
                </div>
                <span class="arm_user_last_active_text">{ARM_Profile_Join_Date}</span>
                <div class="armclear"></div>
                <div class="arm_user_social_icons_all social_profile_fields arm_mobile">
                    {ARM_Profile_Social_Icons_Mobile}
                </div>
                <div class="arm_profile_header_top_box">
                    <div class="arm_user_social_icons_left arm_desktop">
                                
                            {ARM_Profile_Social_Icons_Left}
                               
                    </div>
                    <div class="arm_user_avatar">
                        {ARM_Profile_Avatar_Image}
                    </div>
                    <div class="arm_user_social_icons_right arm_desktop">
                                
                            {ARM_Profile_Social_Icons_Right}
                               
                    </div>
                </div>
            </div>
            {ARM_Cover_Upload_Button}
        </div>
        <div class="arm_profile_defail_container arm_profile_tabs_container">
            {ARM_PROFILE_FIELDS_BEFORE_CONTENT}
            <div class="arm_profile_field_before_content_wrapper"></div>
            <div class="arm_profile_tab_detail" data-tab="general">
                <div class="arm_general_info_container">
                    <table class="arm_profile_detail_tbl">
                        <tbody>';
            foreach ($profileTemplateOptions['profile_fields'] as $k => $value) {
                $arm_template_html .= "<tr>";
                $arm_template_html .= "<td>" . $profileTemplateOptions['label'][$k] . "</td>";
                $arm_template_html .= "<td>[arm_usermeta meta='" . $k . "']</td>";
                $arm_template_html .= "</tr>";
            }
            $arm_template_html .= '</tbody>
                    </table>
                </div>
            </div>
            <div class="arm_profile_field_after_content_wrapper"></div>
            {ARM_PROFILE_FIELDS_AFTER_CONTENT}
        </div>
        </div><div class="armclear"></div>';
        }

        $wpdb->update($arm_member_template_tbl, array('arm_template_html' => $arm_template_html), array('arm_id' => 1));
        $twenty_step_data = array();
        if( $wpdb->last_error != "" ){
	        $twenty_step_data['info_1'] = 'update profile template html in database';
	        $twenty_step_data['query_1'] = $wpdb->last_query;
	        $twenty_step_data['error_1'] = $wpdb->last_error;
        }
        $arm_db_logs .= " Step 20 => ". json_encode($twenty_step_data). " === ";
        update_option('armember_update_logs',$arm_db_logs);

        $date = date('Y-m-d H:i:s');
        update_option('armember_update_end', $date);

        delete_option('arm_update_to_new_version');
        delete_option('arm_new_version');

        update_option('arm_new_version_installed', 1);
        update_option('arm_version', '2.0');
        $arm_newdbversion = '2.0';

        die();
    }

    function arm_check_user_cap($arm_capabilities = '', $is_ajax_call='',$check_nonce=0)
    {
        global $arm_global_settings;

        $errors = array();
        $message = "";
        if($is_ajax_call==true || $is_ajax_call=='1' || $is_ajax_call==1)
        {
            if (!current_user_can($arm_capabilities)) 
            {
                $errors[] = esc_html__('Sorry, You do not have permission to perform this action.', 'ARMember');
                $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
                $return_array['message'] = $return_array['msg'];

                echo json_encode($return_array);
                exit;
            }
        }
        
        $wpnonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field($_REQUEST['_wpnonce']) : '';
        if(empty($wpnonce))
        {
            $wpnonce = isset($_REQUEST['arm_wp_nonce']) ? sanitize_text_field($_REQUEST['arm_wp_nonce']) : '';
        }
        $arm_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'arm_wp_nonce' );
        if( empty( $wpnonce) && $check_nonce == 1 )
        {
            $errors[]                = esc_html__( 'Sorry, Your request can not process due to nonce not found.', 'ARMember' );
            $return_array            = $arm_global_settings->handle_return_messages( $errors, $message );
            $return_array['message'] = $return_array['msg'];
            echo json_encode( $return_array );
            exit;
        }
        else if( !$arm_verify_nonce_flag && $check_nonce == 1 ) {
            $errors[]                = esc_html__( 'Sorry, Your request can not process due to security reason.', 'ARMember' );
            $return_array            = $arm_global_settings->handle_return_messages( $errors, $message );
            $return_array['message'] = $return_array['msg'];
            echo json_encode($return_array);
            exit;
        }
    }
    function arm_get_country_from_ip($logged_in_ip="")
    {
        global $ARMember;
        
        if( '' == $logged_in_ip ){
            return '';
        }

        $country = "";
        try{
            $country_reader = new Reader(MEMBERSHIP_LIBRARY_DIR.'/geoip/inc/GeoLite2-Country.mmdb');
            $record = $country_reader->country($logged_in_ip);
            $country = $record->country->name;
        } catch(Exception $e){
            $country = "";
        }
        
        return $country;
    }

    function arm_session_start( $force = false ) {
        /**
         * Start Session
         */
        $arm_session_id = session_id();
        if( empty($arm_session_id) || $force == true ) {
            //if(!isset($_SESSION)){
                @session_start();
            //}            
        }
    }

    function armember_allowed_html_tags(){

        $arm_allowed_html = array(
            'a' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'href' => array(),
                    'rel' => array(),
                    'target' => array(),
                )
            ),
            'b' => $this->armember_global_attributes(),
            'br' => $this->armember_global_attributes(),
            'button' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'name' => array(),
                    'type' => array(),
                    'value' => array()
                )
            ),
            'code' => $this->armember_global_attributes(),
            'div' => $this->armember_global_attributes(),
            /* 'embed' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'height' => array(),
                    'src' => array(),
                    'type' => array(),
                    'width' => array(),
                )
            ), */
            'font' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'color' => array(),
                    'face' => array(),
                    'size' => array()
                )
            ),
            'h1' => $this->armember_global_attributes(),
            'h2' => $this->armember_global_attributes(),
            'h3' => $this->armember_global_attributes(),
            'h4' => $this->armember_global_attributes(),
            'h5' => $this->armember_global_attributes(),
            'h6' => $this->armember_global_attributes(),
            'hr' => $this->armember_global_attributes(),
            'i' => $this->armember_global_attributes(),
            'img' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'alt' => array(),
                    'height' => array(),
                    'src' => array(),
                    'width' => array()
                )
            ),
            'input' => array_merge(
                $this->armember_global_attributes(),
                $this->armember_visible_tag_attributes(),
                array(
                    'accept' => array(),
                    'alt' => array(),
                    'autocomplete' => array(),
                    //'autofocus' => array(),
                    'checked' => array(),
                    //'dirname' => array(),
                    'disabled' => array(),
                    //'height' => array(),
                    //'list' => array(),
                    'max' => array(),
                    'maxlength' => array(),
                    'min' => array(),
                    //'multiple' => array(),
                    'name' => array(),
                    'onsearch' => array(),
                    //'pattern' => array(),
                    'placeholder' => array(),
                    'readonly' => array(),
                    'required' => array(),
                    'size' => array(),
                    'src' => array(),
                    'step' => array(),
                    'type' => array(),
                    'value' => array(),
                    'width' => array()
                )
            ),
            'ins' => $this->armember_global_attributes(),
            'label' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'for' => array(),
                )
            ),
            'li' => $this->armember_global_attributes(),
            'ol' => $this->armember_global_attributes(),
            'optgroup' => $this->armember_global_attributes(),
            'p' => $this->armember_global_attributes(),
            'section' => $this->armember_global_attributes(),
            'span' => $this->armember_global_attributes(),
            'strong' => $this->armember_global_attributes(),
            'sub' => $this->armember_global_attributes(),
            'sup' => $this->armember_global_attributes(),
            'table' => $this->armember_global_attributes(),
            'tbody' => $this->armember_global_attributes(),
            'thead' => $this->armember_global_attributes(),
            'tfooter' => $this->armember_global_attributes(),
            'th' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'colspan' => array(),
                    'headers' => array(),
                    'rowspan' => array(),
                    'scope' => array()
                )
            ),
            'td' => array_merge(
                $this->armember_global_attributes(),
                array(
                    'colspan' => array(),
                    'headers' => array(),
                    'rowspan' => array()
                )
            ),
            'tr' => $this->armember_global_attributes(),
            'textarea' => array_merge(
                $this->armember_global_attributes(),
                $this->armember_visible_tag_attributes(),
                array(
                    'cols' => array(),
                    'maxlength' => array(),
                    'name' => array(),
                    'placeholder' => array(),
                    'readonly' => array(),
                    'required' => array(),
                    'rows' => array(),
                )
            ),
            'u' => $this->armember_global_attributes(),
            'ul' => $this->armember_global_attributes(),
        );

        return $arm_allowed_html;
    }

    function arm_recursive_sanitize_data( $posted_data ) {
		global $ARMember;

        if( empty( $posted_data ) ) {
            return $posted_data;
        }

		if ( is_array( $posted_data ) ) {
			return array_map( array( $ARMember, __FUNCTION__ ), json_decode( json_encode( $posted_data ), true ) );
		} elseif ( is_object( $posted_data ) ) {
			return array_map( array( $ARMember, __FUNCTION__ ), json_decode( json_encode( $posted_data ), true ) );
		}
		
		/*
        if ( preg_match( '/^(\d+)$/', $posted_data ) ) {
			return intval( $posted_data );
		} elseif ( preg_match( '/^(\d+(|\.\d+))$/', $posted_data ) ) {
			return floatval( $posted_data );
		} else*/ 
        if ( preg_match( '/<[^<]+>/', $posted_data ) ) {
			$arm_allowed_html = $ARMember->armember_allowed_html_tags();
			return wp_kses( $posted_data, $arm_allowed_html );
		} elseif ( filter_var( $posted_data, FILTER_VALIDATE_URL ) ) {
			return sanitize_url( $posted_data ); //phpcs:ignore
		} else {
			return sanitize_text_field( $posted_data );
		}
		return $posted_data;
	}
    
    function arm_recursive_sanitize_data_extend_only_kses( $posted_data ) {
		global $ARMember;

        if( empty( $posted_data ) ) {
            return $posted_data;
        }
        
		if ( is_array( $posted_data ) ) {
			return array_map( array( $ARMember, __FUNCTION__ ), json_decode( json_encode( $posted_data ), true ) );
		} elseif ( is_object( $posted_data ) ) {
			return array_map( array( $ARMember, __FUNCTION__ ), json_decode( json_encode( $posted_data ), true ) );
		}
		
		$arm_allowed_html = $ARMember->armember_allowed_html_tags();
		return wp_kses( $posted_data, $arm_allowed_html );
	
	}

    function arm_recursive_sanitize_data_extend( $posted_data ) {
		global $ARMember;
		if( empty( $posted_data ) ) {
			return $posted_data;
		}
		if ( is_array( $posted_data ) ) {
			return array_map( array( $ARMember, __FUNCTION__ ), json_decode( json_encode( $posted_data ), true ) );
		} elseif ( is_object( $posted_data ) ) {
			return array_map( array( $ARMember, __FUNCTION__ ), json_decode( json_encode( $posted_data ), true ) );
		}
		
		/*
        if ( preg_match( '/^(\d+)$/', $posted_data ) ) {
			return intval( $posted_data );
		} elseif ( preg_match( '/^(\d+(|\.\d+))$/', $posted_data ) ) {
			return floatval( $posted_data );
		} else*/
        if ( preg_match( '/<[^<]+>/', $posted_data ) ) {
			$armlite_allowed_html = $ARMember->armember_allowed_html_tags();
			return wp_kses( $posted_data, $armlite_allowed_html );
		} elseif ( filter_var( $posted_data, FILTER_VALIDATE_URL ) ) {
			return esc_url_raw( $posted_data );
		} else {
			return sanitize_textarea_field( $posted_data );
		}
		return $posted_data;
	}

    function armember_global_attributes(){
        return array(
            'class' => array(),
            'id' => array(),
            'title' => array(),
            'tabindex' => array(),
            'lang' => array(),
            'style' => array(),
        );
    }

    function armember_visible_tag_attributes(){
        return array(
            /* 'onblur' => array(),
            'onchange' => array(),
            'onclick' => array(),
            'oncontextmenu' => array(),
            'oncopy' => array(),
            'oncut' => array(),
            'ondblclick' => array(),
            'ondrag' => array(),
            'ondragend' => array(),
            'ondragenter' => array(),
            'ondragleave' => array(),
            'ondragover' => array(),
            'ondragstart' => array(),
            'ondrop' => array(),
            'onfocus' => array(),
            'oninput' => array(),
            'oninvalid' => array(),
            'onkeydown' => array(),
            'onkeypress' => array(),
            'onkeyup' => array(),
            'onmousedown' => array(),
            'onmousemove' => array(),
            'onmouseout' => array(),
            'onmouseover' => array(),
            'onmouseup' => array(),
            'onmousewheel' => array(),
            'onpaste' => array(),
            'onscroll' => array(),
            'onselect' => array(),
            'onwheel' => array() */
        );
    }

    function arm_receive_heartbeat_func($response, $data) {
        if ( empty( $data['arm_update_user_logout_status'] ) ) {
            return $response;
        }
        
        if( is_user_logged_in() ) {
            global $wpdb, $ARMember;
            $user_id = get_current_user_id();

            if (empty($user_id) || user_can($user_id, 'administrator')) {
                return $response;
            }
            
            $login_date = "0000-00-00 00:00:00";
            $login_history_id = 0;

            if(!empty($_COOKIE['arm_cookie_' . $user_id])) {
                $stored_cookie = $_COOKIE['arm_cookie_' . $user_id]; //phpcs:ignore
                $inserted_id = explode('||', $stored_cookie);
                $session_id = trim($inserted_id[0]);
                $wp_insert_id = trim($inserted_id[1]);

                $login_date_res = $wpdb->get_row("SELECT `arm_history_id`, `arm_logged_in_date` FROM `" . $ARMember->tbl_arm_login_history . "` WHERE `arm_history_id`=".$wp_insert_id, ARRAY_A); //phpcs:ignore --Reason $ARMember->tbl_arm_login_history is a table name
                //$login_date_res = $wpdb->get_row("SELECT `arm_history_id`, `arm_logged_in_date` FROM `" . $ARMember->tbl_arm_login_history . "` WHERE `arm_user_id`=".$user_id." AND arm_user_current_status=1 ORDER BY arm_history_id desc", ARRAY_A);
                
                if(!empty($login_date_res)) {
                    $login_date = $login_date_res['arm_logged_in_date'];
                    $login_history_id = $login_date_res['arm_history_id'];

                    if($login_history_id != 0 && $login_date != "0000-00-00 00:00:00") {

                        //$arm_current_time = date('Y-m-d H:i:s');
                        $arm_current_time = current_time('timestamp');
                        $arm_current_time_date_time = date('Y-m-d H:i:s',$arm_current_time);


                        $login_duration = $arm_current_time - strtotime($login_date);
                        $arm_login_duration = date('H:i:s', $login_duration);

                        $wpdb->update(
                            $ARMember->tbl_arm_login_history,
                            array('arm_logout_date'=>$arm_current_time_date_time, 'arm_login_duration'=>$arm_login_duration),
                            array('arm_history_id'=>$login_history_id)
                        );
                        $response['arm_user_logout_status_updated'] = '1';

                    }
                }
            }
            
        }
        
        return $response;
    }
    function arm_heartbeat_settings( $settings ) {
        $settings['interval'] = 30;
        return $settings;
    }

    function arm_get_need_help_html_content($page_name) {
        $return_html = '';
        if(!empty($page_name)) {
            $return_html .= '<div class="arm_need_help_main_wrapper arm_need_help_main_wrapper_active">';
                $return_html .= '<span class="arm_need_help_wrapper arm_need_help_icon arm_need_help_btn arm_help_question_icon armhelptip" data-param="'.esc_attr($page_name).'" title="' . esc_attr__('Documentation', 'ARMember') . '"><svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M24.9129 12C25.6432 12 26.3735 12 27.0964 12C27.111 12.0584 27.1621 12.0511 27.2059 12.0511C27.7171 12.0877 28.221 12.1607 28.7176 12.263C34.5523 13.4319 38.9849 18.0927 39.8539 23.9517C39.8978 24.2658 39.8832 24.5945 40 24.9014C40 25.6465 40 26.399 40 27.1441C39.9124 27.1734 39.9489 27.2537 39.9416 27.3049C39.8905 27.8235 39.8174 28.3349 39.7079 28.8463C38.43 34.866 33.4861 39.3296 27.3958 39.9286C24.1681 40.2428 21.1667 39.5195 18.421 37.7662C13.5648 34.6541 11.1112 28.7879 12.2942 23.1554C13.5064 17.3914 18.1143 13.0447 23.9563 12.1534C24.2849 12.1023 24.6135 12.1242 24.9129 12ZM24.577 27.1295C24.577 27.2683 24.5697 27.3998 24.577 27.5386C24.6135 28.1596 24.8472 28.3934 25.4679 28.4445C25.6797 28.4591 25.8842 28.4372 26.0887 28.3861C26.4757 28.2838 26.6437 28.0573 26.6583 27.6555C26.6656 27.4802 26.6583 27.3122 26.6656 27.1368C26.6875 26.5816 26.8919 26.1141 27.3009 25.7342C27.4615 25.5881 27.6295 25.442 27.7975 25.3105C28.4182 24.8064 28.9659 24.2439 29.3456 23.5353C30.1781 21.9792 29.7034 20.3063 28.1407 19.5173C26.6802 18.7867 25.1612 18.7648 23.6715 19.4661C22.9924 19.7876 22.4812 20.299 22.2621 21.0514C22.0869 21.6432 22.2986 22.1618 22.8025 22.3883C23.3429 22.6367 23.708 22.5125 24.0951 21.9573C24.7012 21.0879 25.8185 20.8103 26.7751 21.2852C27.3009 21.5482 27.5346 22.0523 27.4031 22.6294C27.3228 22.9874 27.1183 23.2796 26.8627 23.528C26.6144 23.7836 26.3296 23.9955 26.0448 24.2074C25.059 24.9306 24.5551 25.9022 24.577 27.1295ZM27.0088 31.1475C27.0161 30.3731 26.3881 29.7303 25.6213 29.7157C24.8472 29.7083 24.2119 30.3293 24.1973 31.1037C24.19 31.9 24.818 32.5502 25.5921 32.5502C26.3589 32.5575 27.0015 31.9146 27.0088 31.1475Z" fill="white"/>
                </svg></span>';
                $return_html .= '<a href="https://ideas.armemberplugin.com" target="_blank" class="arm_need_help_icon arm_help_ideas_icon armhelptip" title="' . esc_attr__('Feature Request', 'ARMember') . '"><svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M26.8205 12C27.2077 12.073 27.5949 12.1388 27.982 12.2191C31.503 12.9496 34.4104 15.9958 34.9729 19.5533C35.4258 22.4461 34.7026 25.0028 32.7595 27.2016C31.6857 28.4215 30.9552 29.7948 30.736 31.4165C30.6922 31.7233 30.663 32.0301 30.663 32.3442C30.663 32.5269 30.6045 32.5707 30.4292 32.5634C28.9901 32.5561 27.551 32.5561 26.112 32.5561C24.6071 32.5561 23.1023 32.5488 21.6048 32.5634C21.4075 32.5634 21.3491 32.5196 21.3418 32.315C21.2907 30.4669 20.677 28.8233 19.4352 27.4426C18.2518 26.1277 17.4336 24.6448 17.0757 22.9063C16.3379 19.3122 17.9231 15.5064 21.0058 13.5121C22.2257 12.7232 23.5479 12.2411 24.9943 12.0657C25.0454 12.0584 25.1112 12.0877 25.1331 12.0073C25.6883 12 26.2508 12 26.8205 12Z" fill="white"/>
                <path d="M26.0024 36.6103C24.7459 36.6103 23.4895 36.6103 22.233 36.6103C21.634 36.6103 21.3491 36.3181 21.3418 35.7264C21.3418 35.215 21.3491 34.7037 21.3418 34.1997C21.3418 34.0463 21.3783 33.9951 21.539 33.9951C24.5049 34.0024 27.478 34.0024 30.4438 33.9951C30.6191 33.9951 30.6557 34.0463 30.6557 34.2143C30.6484 34.7402 30.6557 35.2735 30.6483 35.7994C30.641 36.3035 30.3342 36.6176 29.8302 36.6176C28.5518 36.6176 27.2808 36.6103 26.0024 36.6103Z" fill="white"/>
                <path d="M23.3287 38.0781C25.1038 38.0781 26.879 38.0781 28.6541 38.0781C28.4641 39.0789 27.2369 39.9847 26.0535 39.9993C24.819 40.0139 23.5552 39.13 23.3287 38.0781Z" fill="white"/>
                </svg></a>';

                $return_html .= '<a href="https://www.facebook.com/groups/arplugins" target="_blank" class="arm_need_help_icon arm_help_join_icon armhelptip" title="' . esc_attr__('Join Community', 'ARMember') . '"><svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M34.5741 31.3248C34.3082 29.5176 33.4033 27.8656 32.0236 26.6685C30.6439 25.4714 28.8808 24.8085 27.0541 24.8H25.3517C23.5251 24.8085 21.762 25.4714 20.3823 26.6685C19.0026 27.8656 18.0977 29.5176 17.8317 31.3248L17.0157 37.0304C16.9897 37.2148 17.0069 37.4028 17.066 37.5794C17.1252 37.7561 17.2246 37.9165 17.3565 38.048C17.6765 38.368 19.6397 40 26.2045 40C32.7693 40 34.7277 38.3744 35.0525 38.048C35.1844 37.9165 35.2839 37.7561 35.343 37.5794C35.4022 37.4028 35.4194 37.2148 35.3933 37.0304L34.5741 31.3248ZM19.0973 25.68C17.551 27.1055 16.5448 29.0217 16.2493 31.104L15.6573 35.2C10.9053 35.168 9.46533 33.44 9.22533 33.088C9.13256 32.9601 9.06635 32.815 9.03063 32.6611C8.9949 32.5072 8.99038 32.3477 9.01733 32.192L9.36933 30.208C9.55272 29.1711 9.98344 28.1938 10.625 27.3588C11.2665 26.5238 12.0999 25.8558 13.0545 25.4115C14.0092 24.9671 15.0569 24.7595 16.1088 24.8063C17.1607 24.853 18.1859 25.1527 19.0973 25.68ZM43.3853 32.192C43.4123 32.3477 43.4078 32.5072 43.372 32.6611C43.3363 32.815 43.2701 32.9601 43.1773 33.088C42.9373 33.44 41.4973 35.168 36.7453 35.2L36.1533 31.104C35.8578 29.0217 34.8517 27.1055 33.3053 25.68C34.2168 25.1527 35.2419 24.853 36.2939 24.8063C37.3458 24.7595 38.3935 24.9671 39.3481 25.4115C40.3028 25.8558 41.1362 26.5238 41.7777 27.3588C42.4192 28.1938 42.8499 29.1711 43.0333 30.208L43.3853 32.192ZM19.3693 22.16C18.9666 22.7312 18.4319 23.1967 17.8107 23.5171C17.1895 23.8374 16.5002 24.0031 15.8013 24C15.1041 24 14.4169 23.8343 13.7963 23.5166C13.1757 23.1989 12.6395 22.7383 12.2319 22.1727C11.8242 21.6072 11.5568 20.9528 11.4516 20.2636C11.3465 19.5744 11.4067 18.87 11.6271 18.2086C11.8476 17.5472 12.2221 16.9476 12.7197 16.4594C13.2174 15.9711 13.8239 15.608 14.4894 15.4002C15.1549 15.1923 15.8602 15.1455 16.5473 15.2637C17.2344 15.3819 17.8836 15.6617 18.4413 16.08C18.2809 16.7074 18.2003 17.3525 18.2013 18C18.2025 19.4675 18.6066 20.9064 19.3693 22.16ZM41.0013 19.6C41.0017 20.1779 40.8882 20.7503 40.6673 21.2843C40.4463 21.8183 40.1222 22.3036 39.7135 22.7122C39.3049 23.1209 38.8197 23.445 38.2856 23.6659C37.7516 23.8869 37.1793 24.0004 36.6013 24C35.9024 24.0031 35.2131 23.8374 34.5919 23.5171C33.9708 23.1967 33.4361 22.7312 33.0333 22.16C33.7961 20.9064 34.2001 19.4675 34.2013 18C34.2024 17.3525 34.1218 16.7074 33.9613 16.08C34.615 15.5897 35.3924 15.2912 36.2062 15.2178C37.02 15.1444 37.8382 15.2991 38.5691 15.6645C39.2999 16.03 39.9146 16.5917 40.3442 17.2868C40.7738 17.9819 41.0013 18.7829 41.0013 19.6Z" fill="white"/><path d="M26.2013 24C29.515 24 32.2013 21.3137 32.2013 18C32.2013 14.6863 29.515 12 26.2013 12C22.8876 12 20.2013 14.6863 20.2013 18C20.2013 21.3137 22.8876 24 26.2013 24Z" fill="white"/></svg></a>';

                $return_html .= '<a href="https://www.youtube.com/@armember/videos?sub_confirmation=1" target="_blank" class="arm_need_help_icon arm_need_help_btn arm_help_video_icon armhelptip" title="' . esc_attr__('Video Tutorials', 'ARMember') . '"><svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M40.4606 24.8142C40.3578 24.66 40.4178 24.4887 40.4178 24.3174C40.4349 24.3174 40.452 24.3174 40.4606 24.3174C40.4606 24.4887 40.4606 24.6514 40.4606 24.8142Z" fill="#F5B11D"/><path d="M40.4606 24.3258C40.4434 24.3258 40.4263 24.3258 40.4178 24.3258C40.4178 24.2659 40.3749 24.1973 40.4606 24.1631C40.4606 24.2145 40.4606 24.2659 40.4606 24.3258Z" fill="#F5B11D"/><path d="M40 24.703C40 25.8508 40 26.9985 40 28.1463C39.9229 28.172 39.9486 28.2319 39.9486 28.2833C39.8972 29.688 39.7687 31.0842 39.6231 32.4803C39.4947 33.7737 38.287 35.0071 37.0107 35.1527C34.7409 35.4011 32.4626 35.6152 30.1757 35.718C27.0237 35.8636 23.8716 35.8465 20.7196 35.6666C18.8353 35.5553 16.9595 35.3668 15.0837 35.1613C13.7561 35.0156 12.5398 33.8765 12.3856 32.5488C12.1801 30.8101 12.0602 29.0628 12.0173 27.3154C11.9488 24.6345 12.0859 21.9536 12.3856 19.2898C12.5313 18.005 13.7389 16.7973 15.0066 16.6602C17.285 16.4118 19.5548 16.1977 21.8417 16.0949C24.9766 15.9493 28.1114 15.975 31.2463 16.1463C33.1478 16.2491 35.0407 16.4461 36.9336 16.6516C38.2613 16.7973 39.4861 17.9793 39.6231 19.3069C39.7687 20.7287 39.8972 22.1591 39.9486 23.5895C39.9486 23.6409 39.9229 23.7009 40 23.7266C40 23.8379 40 23.9493 40 24.0521C39.9058 24.0863 39.9572 24.1548 39.9572 24.2148C39.9572 24.3775 39.9058 24.5489 40 24.703ZM30.6639 25.9107C28.3084 24.3433 25.9958 22.8015 23.6661 21.2426C23.6661 24.369 23.6661 27.4525 23.6661 30.5703C26.013 29.0114 28.317 27.4782 30.6639 25.9107Z" fill="white"/></svg></a>';

                $return_html .= '<span class="arm_need_help_icon arm_need_help_btn arm_help_close_icon armhelptip" title="' . esc_attr__('Close', 'ARMember') . '"><svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M40.4606 24.8142C40.3578 24.66 40.4178 24.4887 40.4178 24.3174C40.4349 24.3174 40.452 24.3174 40.4606 24.3174C40.4606 24.4887 40.4606 24.6514 40.4606 24.8142Z" fill="#F5B11D"/><path d="M40.4606 24.3258C40.4434 24.3258 40.4263 24.3258 40.4178 24.3258C40.4178 24.2659 40.3749 24.1973 40.4606 24.1631C40.4606 24.2145 40.4606 24.2659 40.4606 24.3258Z" fill="#F5B11D"/><path d="M17.8776 34.9777C17.8392 34.923 17.779 34.9284 17.7242 34.9065C16.9902 34.6053 16.7602 33.6687 17.286 33.0662C17.3353 33.0114 17.3901 32.9566 17.4448 32.9018C19.7015 30.6452 21.9582 28.3885 24.2204 26.1319C24.3354 26.0169 24.33 25.9676 24.2204 25.858C21.9418 23.5904 19.6687 21.3119 17.401 19.0388C16.8752 18.513 16.8752 17.7626 17.3955 17.297C17.8337 16.9026 18.4856 16.8972 18.9347 17.2806C19.0059 17.3408 19.0716 17.412 19.1374 17.4778C21.3776 19.718 23.6179 21.9582 25.8527 24.2039C25.9842 24.3353 26.0389 24.3134 26.1594 24.1984C28.4271 21.9253 30.6947 19.6577 32.9624 17.3901C33.4554 16.8972 34.151 16.8753 34.633 17.3189C34.8192 17.4887 34.9014 17.7133 35 17.9379C35 18.1022 35 18.2665 35 18.4308C34.8247 18.9183 34.4249 19.225 34.0798 19.5701C31.9819 21.6734 29.8786 23.7767 27.7698 25.8745C27.6657 25.9785 27.6712 26.0223 27.7753 26.1264C29.9662 28.3064 32.1463 30.4973 34.3318 32.6773C34.6056 32.9511 34.8905 33.214 35 33.6084C35 33.7618 35 33.9151 35 34.063C34.9069 34.2438 34.8466 34.441 34.7042 34.5998C34.5399 34.7806 34.3318 34.8791 34.1181 34.9777C33.9209 34.9777 33.7183 34.9777 33.5211 34.9777C33.0665 34.7915 32.7707 34.4136 32.442 34.0849C30.3387 31.9816 28.2354 29.8838 26.1375 27.7751C26.028 27.6655 25.9787 27.6655 25.8691 27.7751C23.6672 29.9824 21.4598 32.1898 19.2524 34.3971C19.0114 34.6381 18.7759 34.8737 18.4472 34.9777C18.2555 34.9777 18.0638 34.9777 17.8776 34.9777Z" fill="white"/></svg></span>';
            $return_html .= '</div>';
            $return_html .= '<div class="arm_need_help_main_wrapper_inactive armhelptip" title="' . esc_attr__('Need Help?', 'ARMember') . '">';
                $return_html .= '<a class="arm_need_help_icon arm_need_help_btn"><svg width="52" height="52" viewBox="0 0 70 70" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="35" cy="35" r="35" fill="#F5B11D"/><path d="M21.5694 48.4327C22.0846 48.9479 22.6131 49.4232 23.1726 49.8647C23.2914 49.9584 23.464 49.9468 23.571 49.8398L29.9484 43.4625C30.0822 43.3287 30.0549 43.1126 29.8954 43.0107C29.3236 42.6455 28.7837 42.214 28.286 41.7163C27.8041 41.2345 27.3881 40.7107 27.0278 40.1654C26.9244 40.009 26.7097 39.9847 26.5772 40.1173L20.2078 46.4866C20.0993 46.595 20.0883 46.7707 20.1844 46.8903C20.6145 47.4252 21.0723 47.9356 21.5694 48.4327Z" fill="white"/><path d="M44.2454 51.601C44.4184 51.5045 44.4502 51.2634 44.3101 51.1233L37.4925 44.3058C37.4215 44.2348 37.3196 44.2074 37.222 44.2306C35.7444 44.5819 34.2125 44.5818 32.7422 44.2233C32.6441 44.1994 32.5417 44.2267 32.4703 44.298L25.6612 51.107C25.5213 51.247 25.5528 51.4878 25.7255 51.5845C31.461 54.7971 38.5021 54.8051 44.2454 51.601Z" fill="white"/><path d="M46.8073 49.8893C47.3753 49.4399 47.9121 48.9563 48.4356 48.4328C48.9245 47.9439 49.3741 47.4421 49.796 46.9155C49.8918 46.796 49.8808 46.6205 49.7724 46.5122L43.4053 40.1451C43.2719 40.0117 43.0567 40.0384 42.9541 40.1967C42.6051 40.7355 42.1917 41.2437 41.7191 41.7164C41.2134 42.222 40.6654 42.6612 40.0802 43.0289C39.9192 43.13 39.8905 43.3469 40.025 43.4813L46.4086 49.8648C46.5157 49.9719 46.6884 49.9834 46.8073 49.8893Z" fill="white"/><path d="M18.4499 44.3439C18.5469 44.5159 18.7872 44.5471 18.9269 44.4074L25.7274 37.6069C25.7994 37.535 25.8265 37.4318 25.8017 37.3331C25.4272 35.845 25.4122 34.2812 25.7706 32.7785C25.7939 32.6808 25.7665 32.5792 25.6956 32.5082L18.8777 25.6904C18.7377 25.5504 18.4966 25.582 18.4 25.7549C15.1798 31.5161 15.2038 38.5907 18.4499 44.3439Z" fill="white"/><path d="M32.6259 25.8058C34.1715 25.4088 35.7996 25.4085 37.3381 25.7982C37.4366 25.8232 37.5397 25.7961 37.6116 25.7242L44.4121 18.9238C44.5518 18.7841 44.5207 18.5437 44.3486 18.4466C38.552 15.1763 31.4106 15.1845 25.6297 18.4713C25.4582 18.5688 25.4276 18.8086 25.567 18.948L32.3512 25.7321C32.4233 25.8043 32.527 25.8312 32.6259 25.8058Z" fill="white"/><path d="M43.0135 29.8927C43.1154 30.0523 43.3316 30.0795 43.4653 29.9457L49.8427 23.5684C49.9497 23.4614 49.9613 23.2888 49.8676 23.17C49.0453 22.1277 48.0073 21.0775 46.8932 20.1818C46.7736 20.0857 46.5979 20.0967 46.4894 20.2051L40.12 26.5745C39.9875 26.7071 40.0118 26.9217 40.1682 27.0251C41.3158 27.7833 42.2703 28.7288 43.0135 29.8927Z" fill="white"/><path d="M51.5886 25.7225C51.4919 25.5498 51.251 25.5182 51.111 25.6582L44.302 32.467C44.2306 32.5384 44.2035 32.6408 44.2276 32.7388C44.6011 34.2593 44.5861 35.8554 44.1962 37.376C44.1708 37.4749 44.1977 37.5788 44.27 37.651L51.0538 44.4348C51.1933 44.5743 51.4332 44.5435 51.5306 44.372C54.8012 38.609 54.8255 31.5012 51.5886 25.7225Z" fill="white"/><path d="M23.0868 20.2069C21.9806 21.0934 20.9396 22.1512 20.1131 23.1956C20.019 23.3144 20.0304 23.4872 20.1376 23.5944L26.5212 29.9779C26.6556 30.1123 26.8724 30.0836 26.9735 29.9227C27.6883 28.7851 28.6935 27.7695 29.8057 27.0487C29.964 26.9461 29.9907 26.731 29.8573 26.5976L23.4903 20.2306C23.3819 20.1222 23.2064 20.111 23.0868 20.2069Z" fill="white"/></svg></a>';
            $return_html .= '</div>';

            $return_html .= '<div class="arm_sidebar_drawer_main_wrapper">';
                $return_html .= '<div class="arm_sidebar_drawer_inner_wrapper">';
                    $return_html .= '<div class="arm_sidebar_drawer_content">';
                        $return_html .= '<div class="arm_sidebar_drawer_close_container">';
                            $return_html .= '<div class="arm_sidebar_drawer_close_btn"></div>';
                        $return_html .= '</div>';
                        $return_html .= '<div class="arm_sidebar_drawer_body">';
                            $return_html .= '<div class="arm_sidebar_content_wrapper">';
                                $return_html .= '<div class="arm_sidebar_content_header">';
                                    $return_html .= '<h1 class="arm_sidebar_content_heading"></h1>';                                    
                                $return_html .= '</div>';
                                $return_html .= '<div class="arm_sidebar_content_body">';
                                $return_html .= '</div>';
                                $return_html .= '<div class="arm_sidebar_content_footer"><a href="https://www.armemberplugin.com/documentation/" target="_blank" class="arm_readmore_link">Read More</a></div>';
                            $return_html .= '</div>';
                        $return_html .= '</div>';

                        $return_html .= '<div class="arm_loading"><img src="../wp-content/plugins/armember/images/loader.gif" alt="Loading.."></div>';

                    $return_html .= '</div>';
                $return_html .= '</div>';
            $return_html .= '</div>';
        }

        return $return_html;
    }

    function arm_get_need_help_content_func($param) {
        
        $wpnonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';
        $arm_verify_nonce_flag = wp_verify_nonce( $wpnonce, 'arm_wp_nonce' );//phpcs:ignore --Reason:Verifying nonce
        if ( ! $arm_verify_nonce_flag ) {
            $response['status'] = 'error';
            $response['title'] = esc_html__( 'Error', 'ARMember' );
            $response['msg'] = esc_html__( 'Sorry, Your request can not process due to security reason.', 'ARMember' );
            wp_send_json( $response );
            die();
        }
        $arm_doc_content = "";
        if ( !empty($_POST['action']) && $_POST['action'] == 'arm_get_need_help_content' && !empty($_POST['page']) ) {
            $help_page = sanitize_text_field( $_POST['page'] );
            $arm_get_data_url = 'https://www.armemberplugin.com/';
                $arm_get_data_params = array(
                    'method' => 'POST',
                    'body' => array(
                        'action' => 'get_documentation',
                        'page' => $help_page,
                    ),
                    'timeout' => 45,
                );
                $arm_doc_res = wp_remote_post( $arm_get_data_url, $arm_get_data_params );
                if(!is_wp_error($arm_doc_res)){
                    $arm_doc_content = ! empty( $arm_doc_res['body'] ) ? $arm_doc_res['body'] : esc_html__('No data found', 'ARMember');


                    $arm_json_paresed_data = json_decode($arm_doc_content);
                    $arm_doc_url = !empty($arm_json_paresed_data->data->url) ? $arm_json_paresed_data->data->url : ARM_HOME_URL;
                    $arm_json_paresed_data = !empty($arm_json_paresed_data->data->content) ? urldecode($arm_json_paresed_data->data->content) : esc_html__('No data found', 'ARMember');

                    //Replace the anchor tag if anchor tag has any image url
                    $arm_json_paresed_data = preg_replace(array('"<a href=(.*(png|jpg|gif|jpeg|webp))(.*?)>"', '"</a>"'), array('',''), $arm_json_paresed_data);

                    //Add target='_blank' to anchor tag.
                    if(preg_match('/<a.*?target=[^>]*?>/', $arm_json_paresed_data)){
                        preg_replace('/<a.*?target="([^"]?)"[^>]*?>/', 'blank', $arm_json_paresed_data);
                    }else{
                        $arm_json_paresed_data = str_replace('<a', '<a target="_blank"', $arm_json_paresed_data);
                    }

                    //Replace the URL if it not strats with 'https' or 'http'.
                    if(extension_loaded('xml')){
                        $arm_xml_obj = new DOMDocument();
                        $arm_xml_obj->loadHTML($arm_json_paresed_data);
                        foreach($arm_xml_obj->getElementsByTagName('a') as $arm_anchor_tag_data){
                            $arm_anchor_href = $arm_anchor_tag_data->getAttribute('href');
                            if( false === strpos($arm_anchor_href, 'https://') && false === strpos($arm_anchor_href, 'http://') ){
                                $arm_anchor_tag_data->setAttribute('href', $arm_doc_url.$arm_anchor_href);
                            }
                        }

                        $arm_json_paresed_data = $arm_xml_obj->saveHTML();
                    }

                    $arm_doc_content = json_decode($arm_doc_content);
                    if(!empty($arm_doc_content) && is_object($arm_doc_content))
                    {
                        $arm_doc_content->data->content = rawurlencode($arm_json_paresed_data);
                        $arm_doc_content = json_encode($arm_doc_content);
                    }
                } else{
                    $arm_doc_content = $arm_doc_res->get_error_message();
                }

            echo $arm_doc_content; //phpcs:ignore
            exit;
        }
    }

    function arn_add_default_template($arm_template_to_add = 'all') {

        global $ARMember, $arm_global_settings, $arm_social_feature, $wpdb, $arm_errors;
    
        $globalSettings = $arm_global_settings->global_settings;
    
        $register_page_id = isset($globalSettings['register_page_id']) ? $globalSettings['register_page_id'] : 0;
        $forgot_password_page_id = isset($globalSettings['forgot_password_page_id']) ? $globalSettings['forgot_password_page_id'] : 0;
        $reg_redirect_id = isset($globalSettings['thank_you_page_id']) ? $globalSettings['thank_you_page_id'] : 0;
        $login_redirect_id = isset($globalSettings['edit_profile_page_id']) ? $globalSettings['edit_profile_page_id'] : 0;
    
        $wp_upload_dir = wp_upload_dir();
        $upload_dir = $wp_upload_dir['basedir'] . '/armember/';
    
        if($arm_template_to_add == 6 || $arm_template_to_add == 'all') {
            
            /* Sixth Set Start */
            $forms = array();
            $forms['arm_form_label'] = esc_html__('Template 6','ARMember');
            $forms['arm_form_title'] = esc_html__('Please Signup','ARMember');
            $forms['arm_form_type'] = 'template';
            $forms['arm_form_slug'] = 'template-registration-6';
            $forms['arm_set_name'] = esc_html__('Template 6','ARMember');
            $forms['arm_is_default'] = 1;
            $forms['arm_is_template'] = 1;
            $forms['arm_ref_template'] = 6;
            $forms['arm_set_id'] = 0;
            $forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
            $forms['arm_form_created_date'] = date('Y-m-d H:i:s');
    
            $form_settings = array(
                'redirect_type' => 'page',
                'redirect_page' => $reg_redirect_id,
                'redirect_url' => '',
                'auto_login' => '1',
                'style' => array(
                    'form_layout' => 'writer_border',
                    'form_width' => '550',
                    'form_width_type' => 'px',
                    'form_border_width' => '2',
                    'form_border_radius' => '12',
                    'form_border_style' => 'solid',
                    'form_padding_left' => '30',
                    'form_padding_top' => '40',
                    'form_padding_bottom' => '40',
                    'form_padding_right' => '30',
                    'form_position' => 'left',
                    'form_bg' => '',
                    'form_title_font_family' => 'Poppins',
                    'form_title_font_size' => '24',
                    'form_title_font_bold' => '1',
                    'form_title_font_italic' => '0',
                    'form_title_font_decoration' => '',
                    'form_title_position' => 'center',
                    'validation_position' => 'bottom',
                    'color_scheme' => 'blue',    
                    'lable_font_color' => '#1A2538',
                    'field_font_color' => '#2F3F5C',
                    'field_border_color' => '#D3DEF0',
                    'field_focus_color' => '#637799',
                    'button_back_color' => '#005AEE',
                    'button_font_color' => '#FFFFFF',
                    'button_hover_color' => '#0D54C9',
                    'button_hover_font_color' => '#ffffff',                                                                           
                    'form_title_font_color' => '#1A2538',
                    'form_bg_color' => "#FFFFFF",
                    'form_border_color' => "#E6E7F5",
                    'prefix_suffix_color' => '#bababa',
                    'error_font_color' => '#FF3B3B',
                    'error_field_border_color' => '#FF3B3B',
                    'error_field_bg_color' => '#ffffff',   
                    'field_width' => '100',
                    'field_width_type' => '%',
                    "field_height" => "52",
                    'field_spacing' => '18',
                    'field_border_width' => '1',
                    'field_border_radius' => '0',
                    'field_border_style' => 'solid',
                    'field_font_family' => 'Poppins',
                    'field_font_size' => '15',
                    'field_font_bold' => '0',
                    'field_font_italic' => '0',
                    'field_font_decoration' => '',
                    'field_position' => 'left',
                    'rtl' => '0',
                    'label_width' => '250',
                    'label_width_type' => 'px',
                    'label_position' => 'block',
                    'label_align' => 'left',
                    'label_hide' => '0',
                    'label_font_family' => 'Poppins',
                    'label_font_size' => '14',
                    'description_font_size' => '14',
                    'label_font_bold' => '0',
                    'label_font_italic' => '0',
                    'label_font_decoration' => '',
                    'button_width' => '360',
                    'button_width_type' => 'px',
                    'button_height' => '45',
                    'button_height_type' => 'px',
                    'button_border_radius' => '6',
                    'button_style' => 'flat',
                    'button_font_family' => 'Poppins',
                    'button_font_size' => '15',
                    'button_font_bold' => '1',
                    'button_font_italic' => '0',
                    'button_font_decoration' => '',
                    'button_margin_left' => '0',
                    'button_margin_top' => '10',
                    'button_margin_right' => '0',
                    'button_margin_bottom' => '0',
                    'button_position' => 'center'
                )
            );
    
            $forms['arm_form_settings'] = maybe_serialize($form_settings);
            $wpdb->insert($ARMember->tbl_arm_forms, $forms);
            $form_id = $wpdb->insert_id;
    
            $field_options = array(
                'id' => 'user_login',
                'label' => esc_html__('Username','ARMember'),
                'placeholder' => '',
                'type' => 'text',
                'meta_key' => 'user_login',
                'required' => '1',
                'hide_username' => '0',
                'blank_message' => esc_html__('Username can not be left blank','ARMember'),
                'invalid_message' => esc_html__('Please enter valid username','ARMember'),
                'default_field' => '1',
            );
    
            $fields = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 1,
                'arm_form_field_slug' => 'user_login',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => '1',
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
            );
    
            $wpdb->insert($ARMember->tbl_arm_form_field, $fields);
    
            unset($field_options);
            unset($fields);
    
            $field_options = array(
                'id' => 'first_name',
                'label' => esc_html__('First Name','ARMember'),
                'placeholder' => '',
                'type' => 'text',
                'meta_key' => 'first_name',
                'required' => '1',
                'hide_firstname' => '0',
                'blank_message' => esc_html__('First Name can not be left blank.','ARMember'),
                'default_field' => '1'
            );
    
            $fields = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 2,
                'arm_form_field_slug' => 'first_name',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => '1',
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
                );
    
            $wpdb->insert($ARMember->tbl_arm_form_field, $fields);
    
            unset($field_options);
            unset($fields);
    
            $field_options = array(
                'id' => 'last_name',
                'label' => esc_html__('Last Name','ARMember'),
                'placeholder' => '',
                'type' => 'text',
                'meta_key' => 'last_name',
                'required' => '1',
                'hide_lastname' => '0',
                'blank_message' => esc_html__('Last Name can not be left blank.','ARMember'),
                'default_field' => '1'
            );
    
            $fields = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 3,
                'arm_form_field_slug' => 'last_name',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => '1',
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
            );
    
            $wpdb->insert($ARMember->tbl_arm_form_field, $fields);
    
            unset($field_options);
            unset($fields);
    
            $field_options = array(
                'id' => 'user_email',
                'label' => esc_html__('Email Address','ARMember'),
                'placeholder' => '',
                'type' => 'email',
                'meta_key' => 'user_email',
                'required' => '1',
                'blank_message' => esc_html__('Email Address can not be left blank.','ARMember'),
                'invalid_message' => esc_html__('Please enter valid email address.','ARMember'),
                'default_field' => '1'
            );
    
            $fields = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 4,
                'arm_form_field_slug' => 'user_email',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => '1',
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
            );
    
            $wpdb->insert($ARMember->tbl_arm_form_field, $fields);
    
            unset($field_options);
            unset($fields);
    
            $field_options = array(
                'id' => 'user_pass',
                'label' => esc_html__("Password",'ARMember'),
                'placeholder' => '',
                'type' => 'password',
                'options' => array(
                    'strength_meter' => '1',
                    'strong_password' => '0',
                    'minlength' => '6',
                    'maxlength' => '',
                    'special' => '1',
                    'numeric' => '1',
                    'uppercase' => '1',
                    'lowercase' => '1'
                ),
                'meta_key' => 'user_pass',
                'required' => '1',
                'blank_message' => esc_html__('Password can not be left blank.','ARMember'),
                'invalid_message' => esc_html__('Please enter valid password.','ARMember')
            );
    
            $fields = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 5,
                'arm_form_field_slug' => 'user_pass',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => '1',
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
            );
    
            $wpdb->insert($ARMember->tbl_arm_form_field, $fields);
    
            unset($field_options);
            unset($fields);
    
            $field_options = array(
            'id' => 'submit',
            'label' => esc_html__('Submit','ARMember'),
            'type' => 'submit',
            'default_field' => '1'
            );
    
            $fields = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 6,
                'arm_form_field_slug' => '',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => '1',
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
            );
    
            $wpdb->insert($ARMember->tbl_arm_form_field, $fields);
    
            unset($field_options);
            unset($fields);
            unset($form_id);
            unset($forms);
    
            /* Registration Template */
    
    
    
    
            /* Login Form Template Start */
            $forms = array();
            $forms['arm_form_label'] = esc_html__('Please Login', 'ARMember');
            $forms['arm_form_title'] = esc_html__('Please Login','ARMember');
            $forms['arm_form_type'] = 'template';
            $forms['arm_form_slug'] = 'template-login-6';
            $forms['arm_set_name'] = esc_html__('Template 6', 'ARMember');
            $forms['arm_is_default'] = '1';
            $forms['arm_is_template'] = '1';
            $forms['arm_ref_template'] = 6;
            $forms['arm_set_id'] = '-6';
            $forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
            $forms['arm_form_created_date'] = date('Y-m-d H:i:s');
    
            $form_template_settings = array();
            $form_settings = array();
            $form_settings['display_direction'] = 'vertical';
            $form_settings['redirect_type'] = 'page';
            $form_settings['redirect_page'] = $login_redirect_id;
            $form_settings['redirect_url'] = '';
            $form_settings['show_rememberme'] = '1';
            $form_settings['show_registration_link'] = '1';
            $form_settings['registration_link_label'] = '<center>Dont have account? [ARMLINK]SIGNUP[/ARMLINK]</center>';
            $form_settings['registration_link_type'] = 'page';
            $form_settings['registration_link_type_modal'] = '1';
            $form_settings['registration_link_type_page'] = $register_page_id;
            $form_settings['show_forgot_password_link'] = '1';
            $form_settings['forgot_password_link_label'] = 'Lost Your Password';
            $form_settings['forgot_password_link_type'] = 'modal';
            $form_settings['forgot_password_link_type_page'] = $forgot_password_page_id;
            $form_settings['forgot_password_link_margin']['bottom'] = '0';
            $form_settings['forgot_password_link_margin']['top'] = '-132';
            $form_settings['forgot_password_link_margin']['left'] = '315';
            $form_settings['forgot_password_link_margin']['right'] = '0';
            $form_settings['registration_link_margin']['top'] = 0;
            $form_settings['registration_link_margin']['bottom'] = 0;
            $form_settings['registration_link_margin']['left'] = 0;
            $form_settings['registration_link_margin']['right'] = 0;
    
            if ($arm_social_feature->isSocialFeature && !empty($arm_social_feature->isSocialFeature)) {
            $social_networks = $arm_social_feature->social_settings['options'];
            $forms_networks = array('facebook','twitter');
            $networks = '';
            $counter = 0;
            $network_order = '';
            if(!empty($social_networks) && is_array($social_networks))
            {
                foreach ($social_networks as $key => $network) {
                    if (in_array($key, $forms_networks) && $network['status'] == '1') {
                        $networks .= $key . ',';
                        $counter++;
                    }
                    $network_order .= $key . ',';
                }
            }
            if ($counter > 0) {
                $networks = rtrim($networks, ',');
                $network_order = rtrim($network_order, ',');
                $form_settings['enable_social_login'] = '1';
                $form_settings['social_networks'] = $networks;
                $form_settings['social_networks_order'] = $network_order;
                $form_settings['social_network_settings'] = $social_networks;
            }
            }
    
            $form_style = array(
                'social_btn_position' => 'bottom',
                'social_btn_type' => 'horizontal',
                'social_btn_align' => 'center',
                'enable_social_btn_separator' => '1',
                'social_btn_separator' => '<center>OR</center>',
                'form_layout' => 'writer_border',
                'form_width' => '550',
                'form_width_type' => 'px',
                'form_border_width' => '2',
                'form_border_radius' => '12',
                'form_border_style' => 'solid',
                'form_padding_left' => '30',
                'form_padding_top' => '30',
                'form_padding_right' => '30',
                'form_padding_bottom' => '30',
                'form_position' => 'left',
                'form_bg' => '',
                'form_title_font_family' => 'Poppins',
                'form_title_font_size' => '24',
                'form_title_font_bold' => '1',
                'form_title_font_italic' => '0',
                'form_title_font_decoration' => '',
                'form_title_position' => 'center',
                'validation_position' => 'bottom',
                'color_scheme' => 'blue',    
                'lable_font_color' => '#1A2538',
                'field_font_color' => '#2F3F5C',
                'field_border_color' => '#D3DEF0',
                'field_focus_color' => '#637799',
                'button_back_color' => '#005AEE',
                'button_font_color' => '#FFFFFF',
                'button_hover_color' => '#0D54C9',
                'button_hover_font_color' => '#ffffff',                                                           
                'form_title_font_color' => '#1A2538',
                'form_bg_color' => "#FFFFFF",
                'form_border_color' => "#E6E7F5",
                'prefix_suffix_color' => '#bababa',
                'error_font_color' => '#FF3B3B',
                'error_field_border_color' => '#FF3B3B',
                'error_field_bg_color' => '#ffffff',   
                'field_width' => '100',
                'field_width_type' => '%',
                "field_height" => "52",
                'field_spacing' => '18',
                'field_border_width' => '1',
                'field_border_radius' => '0',
                'field_border_style' => 'solid',
                'field_font_family' => 'Poppins',
                'field_font_size' => '15',
                'field_font_bold' => '0',
                'field_font_italic' => '0',
                'field_font_decoration' => '',
                'field_position' => 'left',
                'rtl' => '0',
                'label_width' => '250',
                'label_width_type' => 'px',
                'label_position' => 'block',
                'label_align' => 'left',
                'label_hide' => '0',
                'label_font_family' => 'Poppins',
                'label_font_size' => '14',
                'description_font_size' => '14',
                'label_font_bold' => '0',
                'label_font_italic' => '0',
                'label_font_decoration' => '',
                'button_width' => '360',
                'button_width_type' => 'px',
                'button_height' => '45',
                'button_height_type' => 'px',
                'button_border_radius' => '6',
                'button_style' => 'flat',
                'button_font_family' => 'Poppins',
                'button_font_size' => '15',
                'button_font_bold' => '1',
                'button_font_italic' => '0',
                'button_font_decoration' => '',
                'button_margin_left' => '0',
                'button_margin_top' => '10',
                'button_margin_right' => '0',
                'button_margin_bottom' => '0',
                'button_position' => 'center'
            );
    
            $form_settings['style'] = $form_style;
    
            $form_template_settings = $form_settings;
            $forms['arm_form_settings'] = maybe_serialize($form_template_settings);
    
            $wpdb->insert($ARMember->tbl_arm_forms, $forms);
            $form_id = $wpdb->insert_id;
            $field_options = array(
                'id' => 'user_login',
                'type' => 'text',
                'default_field' => '1',
                'label' => esc_html__('Username', 'ARMember'),
                'placeholder' => '',
                'options' => array(
                    'minlength' => '',
                    'maxlength' => ''
                ),
                'required' => '1',
                'meta_key' => 'user_login',
                'blank_message' => esc_html__('Username can not be left blank.', 'ARMember'),
                'invalid_message' => esc_html__('Please enter valid username.', 'ARMember'),
                'prefix' => '',
                'suffix' => '',
                'ref_field_id' => '0'
            );
    
            $form_field_data = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 1,
                'arm_form_field_slug' => 'user_login',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => 1,
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
            );
    
            $wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
    
            unset($form_field_data);
            unset($field_options);
    
            $field_options = array(
                'id' => 'user_pass',
                'label' => esc_html__('Password', 'ARMember'),
                'placeholder' => '',
                'type' => 'password',
                'default_field' => '1',
                'options' => array(
                    'strength_meter' => '0',
                    'strong_password' => '0',
                    'minlength' => '1',
                    'maxlength' => '0',
                    'special' => '0',
                    'numeric' => '0',
                    'uppercase' => '0',
                    'lowercase' => '0'
                ),
                'meta_key' => 'user_pass',
                'required' => '1',
                'blank_message' => esc_html__('Password can not be left blank.', 'ARMember'),
                'invalid_message' => esc_html__('Please enter valid password', 'ARMember')
            );
    
            $form_field_data = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 2,
                'arm_form_field_slug' => 'user_pass',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => 1,
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
            );
    
            $wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
    
            unset($form_field_data);
            unset($field_options);
    
            $field_options = array(
                'id' => 'rememberme',
                'type' => 'rememberme',
                'default_field' => '1',
                'default_val' => 'forever',
                'label' => esc_html__('Remember me', 'ARMember'),
                'meta_key' => 'rememberme',
                'ref_field_id' => '0'
            );
    
            $form_field_data = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 3,
                'arm_form_field_slug' => 'rememberme',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => 1,
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
            );
    
            $wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
    
            unset($form_field_data);
            unset($field_options);
    
            $field_options = array(
                'id' => 'submit',
                'type' => 'submit',
                'default_field' => '1',
                'label' => 'LOGIN',
                'meta_key' => ''
            );
    
            $form_field_data = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 4,
                'arm_form_field_slug' => '',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => 1,
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
            );
    
            $wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
    
            unset($form_field_data);
            unset($field_options);
            unset($forms);
            unset($form_id);
    
            /* Login Form Template End */
    
            /* Forgot Password Form Start */
            $forms = array();
            $forms['arm_form_label'] = esc_html__('Forgot Password', 'ARMember');
            $forms['arm_form_title'] = esc_html__('Forgot Password','ARMember');
            $forms['arm_form_type'] = 'template';
            $forms['arm_form_slug'] = 'template-forgot-password-6';
            $forms['arm_set_name'] = esc_html__('Template 6', 'ARMember');
            $forms['arm_is_default'] = '1';
            $forms['arm_is_template'] = '1';
            $forms['arm_ref_template'] = 6;
            $forms['arm_set_id'] = '-6';
            $forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
            $forms['arm_form_created_date'] = date('Y-m-d H:i:s');
    
    
            $form_template_settings['redirect_type'] = 'message';
    
            $form_template_settings['description'] = esc_html__('Please enter your email address or username below.','ARMember');
    
            $forms['arm_form_settings'] = maybe_serialize($form_template_settings);
    
            $wpdb->insert($ARMember->tbl_arm_forms, $forms);
            $form_id = $wpdb->insert_id;
    
            $field_options = array(
                'id' => 'user_login',
                'type' => 'text',
                'default_field' => '1',
                'label' => esc_html__('Username', 'ARMember'),
                'placeholder' => '',
                'options' => array(
                    'minlength' => '',
                    'maxlength' => ''
                ),
                'required' => '1',
                'meta_key' => 'user_login',
                'blank_message' => esc_html__('Username can not be left blank.', 'ARMember'),
                'invalid_message' => esc_html__('Please enter valid username.', 'ARMember'),
                'prefix' => '',
                'suffix' => '',
                'ref_field_id' => '0'
            );
    
            $form_field_data = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 1,
                'arm_form_field_slug' => 'user_login',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => 1,
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
            );
    
            $wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
    
            unset($form_field_data);
            unset($field_options);
    
            $field_options = array(
                'id' => 'submit',
                'type' => 'submit',
                'default_field' => '1',
                'label' => 'Submit',
                'meta_key' => ''
            );
    
            $form_field_data = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 2,
                'arm_form_field_slug' => '',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => 1,
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
            );
    
            $wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
    
            unset($form_field_data);
            unset($field_options);
            unset($forms);
            unset($form_id);
    
            /* Forgot Password Form End */
    
            /* Change Password Form Start */
            $forms = array();
            $forms['arm_form_label'] = esc_html__('Change Password', 'ARMember');
            $forms['arm_form_title'] = esc_html__('Change Password','ARMember');
            $forms['arm_form_type'] = 'template';
            $forms['arm_form_slug'] = 'template-change-password-6';
            $forms['arm_set_name'] = esc_html__('Template 6', 'ARMember');
            $forms['arm_is_default'] = '1';
            $forms['arm_is_template'] = '1';
            $forms['arm_ref_template'] = 6;
            $forms['arm_set_id'] = '-6';
            $forms['arm_form_updated_date'] = date('Y-m-d H:i:s');
            $forms['arm_form_created_date'] = date('Y-m-d H:i:s');
    
            $form_template_settings['redirect_type'] = 'message';
            $form_template_settings['message'] = esc_html__('Your password changed successfully.','ARMember');
    
    
            $forms['arm_form_settings'] = maybe_serialize($form_template_settings);
    
            $wpdb->insert($ARMember->tbl_arm_forms, $forms);
            $form_id = $wpdb->insert_id;
    
            $field_options = array(
                'id' => 'user_pass',
                'type' => 'password',
                'default_field' => '1',
                'label' => esc_html__('New Password', 'ARMember'),
                'placeholder' => '',
                'options' => array(
                    'minlength' => '6',
                    'maxlength' => '',
                    'strength_meter' => '1',
                    'special' => '1',
                    'numeric' => '1',
                    'uppercase' => '1',
                    'lowercase' => '1'
                ),
                'required' => '1',
                'meta_key' => 'user_pass',
                'blank_message' => esc_html__('Password can not be left blank.', 'ARMember'),
                'prefix' => '',
                'suffix' => '',
                'ref_field_id' => '0'
            );
    
            $form_field_data = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 1,
                'arm_form_field_slug' => 'user_pass',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => 1,
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
            );
    
            $wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
            $form_field_id = $wpdb->insert_id;
            unset($form_field_data);
            unset($field_options);
    
            $field_options = array(
                'id' => 'repeat_pass',
                'type' => 'repeat_pass',
                'default_field' => '1',
                'label' => esc_html__('Confirm Password', 'ARMember'),
                'required' => '1',
                'meta_key' => 'repeat_pass',
                'blank_message' => esc_html__('Confirm Password can not be left blank.', 'ARMember'),
                'invalid_message' => esc_html__('Passwords don\'t match.','ARMember'),
                'prefix' => '',
                'suffix' => '',
                'ref_field_id' => $form_field_id
            );
    
            $form_field_data = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 2,
                'arm_form_field_slug' => 'repeat_pass',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => 1,
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
            );
            $wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
            $form_field_id = $wpdb->insert_id;
            unset($form_field_data);
            unset($field_options);
            unset($form_field_id);
    
            $field_options = array(
                'id' => 'submit',
                'type' => 'submit',
                'default_field' => '1',
                'label' => 'Submit',
                'meta_key' => ''
            );
    
            $form_field_data = array(
                'arm_form_field_form_id' => $form_id,
                'arm_form_field_order' => 3,
                'arm_form_field_slug' => '',
                'arm_form_field_option' => maybe_serialize($field_options),
                'arm_form_field_status' => 1,
                'arm_form_field_created_date' => date('Y-m-d H:i:s')
            );
    
            $wpdb->insert($ARMember->tbl_arm_form_field, $form_field_data);
    
            unset($form_field_data);
            unset($field_options);
            unset($forms);
            unset($form_id);
            unset($form_template_settings);
    
            /* Change Password Form End */
    
            /* Sixth Set End */
        }
        /* Sixth set End */
    }

    function arm_get_basename($filename){
        if( empty( $filename ) ) {
            return;
        }
        return preg_replace('/^.+[\\\\\\/]/', '', $filename);
    }

    function arm_armember_package_update( $return_type_response = 0 ){
        global $wp, $wpdb, $ARMember, $arm_capabilities_global,$arm_slugs, $arm_members_activity;
        
        $response = array('type' => 'error', 'msg' => esc_html('Sorry, Something went wrong. Please try again.', 'ARMember'),'arm_license_status'=>0);
        
        /*check the license page capabilities to user */
        $arm_manage_license_key = !isset(($arm_capabilities_global['arm_manage_license']))?$arm_slugs->licensing:$arm_capabilities_global['arm_manage_license'];
        $ARMember->arm_check_user_cap($arm_manage_license_key, '1', '1');
        
        if(isset($_POST['arm_license_key']) && !empty($_POST['arm_license_key'])){ //phpcs:ignore

            $arm_package_dactvte=(isset($_POST['arm_package_dactvte']))?$_POST['arm_package_dactvte']:'0'; //phpcs:ignore
            // activate license for this plugin
            $posted_license_key = trim($_POST['arm_license_key']); //phpcs:ignore
            $arm_domain_name = isset( $_SERVER["SERVER_NAME"] ) ? $_SERVER["SERVER_NAME"] : ''; //phpcs:ignore
            $posted_license_package = '25519';

            $api_params = array(
                'action' => 'helpdesk_edd_license_details_get',
                'pd_edd_purchase_code'    => $posted_license_key,
            );
            
            $response_verify = wp_remote_post( ARMADDON_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
            $response_verify_data = json_decode( wp_remote_retrieve_body( $response_verify ), true );
            if( !empty( $response_verify_data ) )
            {
                if( !empty( $response_verify_data['type']) && $response_verify_data['type'] == 'success' )
                {
                    $posted_license_package = !empty($response_verify_data['data']['download_id'] ) ? $response_verify_data['data']['download_id'] : $posted_license_package;
                }
            }

            $edd_action='activate_license';
            if($arm_package_dactvte=='1'){
                $edd_action='deactivate_license';
                $posted_license_key=get_option('arm_pkg_key');
                $posted_license_package=get_option('arm_pkg');
            }

            $api_params = array(
                'edd_action' => $edd_action,
                'license'    => $posted_license_key,
                'item_id'  => $posted_license_package,
                // 'url'        => home_url()
            );

            if($edd_action!='deactivate_license'){
                $api_params['url'] = home_url();
            }

            // Call the custom API.
            $response = wp_remote_post( ARMADDON_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
            $response_return = array();
            $message = "";
            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

                $message =  ( is_wp_error( $response ) && ! empty( $response->get_error_message() ) ) ? $response->get_error_message() : esc_html( 'An error occurred, please try again.', 'ARMember' );
                $response_return['msg']=$message;
                $response_return['arm_license_status']=0;

            } else {

                $license_data = json_decode( wp_remote_retrieve_body( $response ) );
                
                $license_data_string = wp_remote_retrieve_body( $response );
                if ( false === $license_data->success && ! empty( $license_data->error ) ) {
                    switch( $license_data->error ) {
                        case 'expired' :
                            $message = sprintf(
                                esc_html( "Your license key expired on %1\$s.", 'ARMember'),
                                date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
                            );
                            break;
                        case 'revoked' :
                            $message = esc_html( 'Your license key has been disabled.', 'ARMember');
                            break;
                        case 'missing' :
                            $message = esc_html( 'Invalid license.', 'ARMember');
                            break;
                        case 'invalid' :
                        case 'site_inactive' :
                            $message = esc_html( 'Your license is not active for this URL.', 'ARMember');
                            break;
                        case 'item_name_mismatch' :
                            $message = esc_html('This appears to be an invalid license key for your selected package.', 'ARMember');
                            break;
                        case 'invalid_item_id' :
                                $message = esc_html('This appears to be an invalid license key for your selected package.', 'ARMember');
                                break;
                        case 'no_activations_left':
                            $message = esc_html( 'Your license key has reached its activation limit.', 'ARMember');
                            break;
                        default :
                            $message = esc_html( 'An error occurred, please try again.', 'ARMember');
                            break;
                    }
                    $response_return['msg']=$message;
                    $response_return['arm_license_status']=0;

                    $check_license_data_error = array('expired','revoked','no_activations_left');
                    if($arm_package_dactvte == '0' && !in_array($license_data->error, $check_license_data_error) )
                    {
                        $force_check = 1;
                        $_POST['license_key'] = $posted_license_key;
                        $armreact = $arm_members_activity->armreqact( $force_check, 1, $message );
                        if( !empty( $armreact ) && $armreact == 'VERIFIED' )
                        {
                            $ARMember->arm_pkg_key_update( 0 );

                            $message = "License Activated Successfully.";
                            $response_return['type']='success';
                            $response_return['msg']=$message;
                            $response_return['arm_license_status']=1;
                        }
                    }

                }else if($license_data->license === "valid"){
                    update_option('arm_pkg_key', $posted_license_key );
                    update_option('arm_pkg', $posted_license_package );
                    update_option('arm_pkg_status', $license_data->license );
                    update_option('arm_pkg_dmn', $arm_domain_name );
                    
                    $api_params = array(
                        'action' => 'helpdesk_edd_license_details_get',
                        'pd_edd_purchase_code'    => $posted_license_key,
                        'license_data_string'      => $license_data_string
                    );
                    
                    $response_verify = wp_remote_post( ARMADDON_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );
                    
                    $response_verify_data = json_decode( wp_remote_retrieve_body( $response_verify ), true );

                    if( !empty( $response_verify_data ) )
                    {
                        if( !empty( $response_verify_data['type']) && $response_verify_data['type'] == 'success' )
                        {
			                $license_data_string = json_decode( $license_data_string );
                            $license_data_string->date_created = $response_verify_data['data']['date_created'];
                            $license_data_string->expires = $response_verify_data['data']['date_expires'];
			                $license_data_string = json_encode( $license_data_string );
                        }
                    }
                    update_option('arm_pkg_data_actvte_respnc', $license_data_string );

                    $message = "License Activated Successfully.";
                    $response_return['type']='success';
                    $response_return['msg']=$message;
                    $response_return['arm_license_status']=1;
                } else if( ( $license_data->license === "deactivated" || $license_data->license === "failed" ) && $arm_package_dactvte=='1' ) {
                    update_option('arm_is_user_private_content_feature', 0);
                    update_option('arm_is_social_feature', 0);
                    update_option('arm_is_social_login_feature', 0);
                    update_option('arm_is_pro_ration_feature', 0);
                    update_option('arm_is_drip_content_feature', 0);
                    //update_option('arm_is_opt_ins_feature', 0);
                    update_option('arm_is_coupon_feature', 0);
                    update_option('arm_is_buddypress_feature', 0);
                    update_option('arm_is_woocommerce_feature', 0);
                    update_option('arm_is_multiple_membership_feature', 0);
                    update_option('arm_is_mycred_feature', 0);
                    update_option('arm_is_plan_limit_feature',0);
                    update_option('arm_is_invoice_tax_feature', 0);
                    update_option('arm_is_gutenberg_block_restriction_feature', 0);
                    update_option('arm_is_beaver_builder_restriction_feature', 0);
                    update_option('arm_is_divi_builder_restriction_feature', 0);
                    update_option('arm_is_wpbakery_page_builder_restriction_feature', 0);
                    update_option('arm_is_fusion_builder_restriction_feature', 0);
                    update_option('arm_is_oxygen_builder_restriction_feature', 0);
                    update_option('arm_is_siteorigin_builder_restriction_feature', 0);
                    update_option('arm_is_bricks_builder_restriction_feature', 0);
                    update_option('arm_is_pay_per_post_feature', 0);
            
                    delete_option("armIsSorted");
                    delete_option("armSortOrder");
                    delete_option("armSortId");
                    delete_option("armSortInfo");
                    delete_option("armBadgeUpdated");
                    delete_option("armIsBadgeUpdated");
                    
                    delete_option('arm_pkg_key');
                    delete_option('arm_pkg');
                    delete_option('arm_pkg_status');
                    delete_option('arm_pkg_data_actvte_respnc');
                    delete_option('arm_pkg_dmn');
            
                    delete_site_option("armIsSorted");
                    delete_site_option("armSortOrder");
                    delete_site_option("armSortId");
                    delete_site_option("armSortInfo");
                    delete_site_option("armBadgeUpdated");
                    delete_site_option("armIsBadgeUpdated");

                    delete_site_option('arm_pkg_key');
                    delete_site_option('arm_pkg');
                    delete_site_option('arm_pkg_status');
                    delete_site_option('arm_pkg_data_actvte_respnc');
                    delete_site_option('arm_pkg_dmn');
                    
                    $message = "License Deactivated Successfully.";
                    $response_return['type']='success';
                    $response_return['msg']=$message;
                    $response_return['arm_license_status']=0;
                }    

            }
            
        }

        if( !empty( $return_type_response ) ){
            return $response_return;
        }

        echo json_encode($response_return);
        die();
    }

    function arm_armember_pkg_content_external( $arm_license_armember_content, $form_flag = 0 ){
        $arm_pkg_status=get_option('arm_pkg_status');
        $arm_pkg_key=get_option('arm_pkg_key');
        $arm_package_dactvte=0;
        $arm_license_active_style='display:inline';
        $arm_package_dactvte_style='display:none;';
        if($arm_pkg_status=='valid' && !empty($arm_pkg_key)){
            $arm_package_dactvte=1;
            $arm_license_active_style='display:none';
            $arm_package_dactvte_style='display:inline';        
        }
        $hostname = isset( $_SERVER["SERVER_NAME"] ) ? $_SERVER["SERVER_NAME"] : ''; //phpcs:ignore
        $arm_admin_form_cls = empty( $form_flag ) ? " arm_admin_form" : '';
        $arm_admin_form_btn_style = "width:180px; border:0px; color:#FFFFFF; height:40px; border-radius:3px;cursor:pointer;line-height: 15px;";
        $arm_admin_form_link_position = "";
        if( !empty( $form_flag ) )
        {
            $arm_admin_form_btn_style = "width:150px;cursor:pointer;background-color:#53ba73;border:0px;color:#FFFFFF;height:40px;border-radius:3px;";
            $arm_admin_form_link_position = "<br><br>";
        }
        $arm_license_armember_content .='<form method="post" action="#" id="arm_armember_license_settings" class="arm_license_settings'.$arm_admin_form_cls.'" onsubmit="return false;">
            <table class="form-table">
                <tr class="form-field">
                    <th class="arm-form-table-label">'.esc_html('Customer Name', 'ARMember').'</th>
                    <td class="arm-form-table-content">';
                    
                        $arm_license_armember_content .='<div id="licenseactivatedmessage" class="arm_armember_remove_license_section" style="width:300px; vertical-align:top;padding:5px;'.$arm_package_dactvte_style.'">'.$arm_pkg_key.'</div>';
                    
                        $arm_license_armember_content .='<div class="arm_armember_add_license_section" style="'.$arm_license_active_style.'"><input type="text" name="cust_name" id="li_customer_name" value="'.$arm_pkg_key.'" autocomplete="off" /><div class="arperrmessage" id="li_customer_name_error" style="display:none;">'. esc_html('Please enter customer name.', 'ARMember').'</div></div>'; 

                    $arm_license_armember_content .='</td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label">'.esc_html('Customer Email', 'ARMember').'</th>
                    <td class="arm-form-table-content">';
                    
                        $arm_license_armember_content .='<div id="licenseactivatedmessage" class="arm_armember_remove_license_section" style="width:300px; vertical-align:top;padding:5px;'.$arm_package_dactvte_style.'">'.$arm_pkg_key.'</div>';
                    
                        $arm_license_armember_content .='<div class="arm_armember_add_license_section" style="'.$arm_license_active_style.'"><input type="text" name="cust_email" id="li_customer_email" value="'.$arm_pkg_key.'" autocomplete="off" /><div class="arperrmessage" id="li_customer_email_error" style="display:none;">'. esc_html('Please enter customer email.', 'ARMember').'</div></div>'; 

                    $arm_license_armember_content .='</td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label">'.esc_html('Purchase Code', 'ARMember').'</th>
                    <td class="arm-form-table-content">';
                    
                        $arm_license_armember_content .='<div id="licenseactivatedmessage" class="arm_armember_remove_license_section" style="width:300px; vertical-align:top;padding:5px;'.$arm_package_dactvte_style.'">'.$arm_pkg_key.'</div>';
                    
                        $arm_license_armember_content .='<div class="arm_armember_add_license_section" style="'.$arm_license_active_style.'"><input type="text" name="arm_license_key" id="arm_license_key" value="'.$arm_pkg_key.'" autocomplete="off" /><div class="arperrmessage" id="arm_license_key_error" style="display:none;">'. esc_html('Please enter purchase code.', 'ARMember').'</div></div>'; 

                    $arm_license_armember_content .='</td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label">'. esc_html('Domain Name', 'ARMember').'</th>
                    <td class="arm-form-table-content">
                        <label class="lblsubtitle">'.esc_html($hostname).'</label>
                        <input type="hidden" name="arm_domain_name" id="arm_domain_name" value="'.esc_attr($hostname).'" autocomplete="off" />
                        <input type="hidden" name="domain_name" id="domain_name" value="'.esc_attr($hostname).'" autocomplete="off" />
                    </td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label">&nbsp;</th>
                    <td class="arm-form-table-content">';
                    $arm_license_armember_content .='<input type="hidden" name="arm_package_dactvte" id="arm_package_dactvte" value="'.$arm_package_dactvte.'" />';
                        $arm_license_armember_content .='<span id="license_link" class="arm_armember_remove_license_section" style="'.$arm_package_dactvte_style.'"><button type="button" id="arm_armember-remove-verify-purchase-code" name="arm_armember_remove_license" style="width:170px; border:0px; color:#FFFFFF; height:40px; border-radius:6px;" class="red_remove_license_btn">'.esc_html('Remove License', 'ARMember').'</button></span>';

                        $arm_license_armember_content .='<span id="license_link" class="arm_armember_add_license_section" style="'.$arm_license_active_style.'"><button type="button" id="arm_armember-verify-purchase-code" name="arm_armember_activate_license" style="'.$arm_admin_form_btn_style.'" class="greensavebtn">'.esc_html('Activate License', 'ARMember').'</button>
                        &nbsp;&nbsp;'.$arm_admin_form_link_position.'<a href="https://www.armemberplugin.com/documents/how-to-manage-armember-license/#ARMemberWebsiteLicense" target="_blank" title="Get Your Purchase Code">Where can I find my Purchase Code?</a><br><br>
                            Don\'t have direct license yet? <a href="https://www.armemberplugin.com/pricing" target="_blank" title="Purchase ARMember License">Purchase ARMember license.</a></span>';
                    
                    $arm_license_armember_content .='<span id="license_loader" style="display:none;">&nbsp;<img src="'.MEMBERSHIP_IMAGES_URL.'/loading_activation.gif" height="15" /></span>
                        <span id="license_error" style="display:none;">&nbsp;</span>
                        <span id="license_reset" style="display:none;">&nbsp;&nbsp;<a onclick="javascript:return false;" href="#">Click here to submit RESET request</a></span>
                        <span id="license_success" style="display:none;">'.esc_html('License Activated Successfully.', 'ARMember').'</span>                        
                    </td>
                </tr>
            </table>
        </form>
        <div class="armclear"></div>';    
        
        global $arm_global_settings;
        /* **********./Begin remove License Popup/.********** */
        $arm_remove_license_popup_content = '<span class="arm_confirm_text">'.esc_html("Are you sure you want to Remove this License?",'ARMember' );		
        $arm_remove_license_popup_content .= '<input type="hidden" value="" id="arm_armember_remove_license_flag"/>';
        $arm_remove_license_popup_title = '<span class="arm_confirm_text">'.esc_html('Remove License', 'ARMember').'</span>';		
        
        $arm_remove_license_popup_arg = array(
            'id' => 'arm_armember_remove_license_form_message',
            'class' => 'arm_armember_remove_license_form_message',
            'title' => $arm_remove_license_popup_title,
            'content' => $arm_remove_license_popup_content,
            'button_id' => 'arm_armember_remove_license_ok_btn',
            'button_onclick' => "arm_armember_deactivate_license();",
        );
        $arm_license_armember_content .=$arm_global_settings->arm_get_bpopup_html($arm_remove_license_popup_arg);
        /* **********./End remove License Popup/.********** */
            
        return $arm_license_armember_content;
    }

    function arm_pkg_key_update( $flag = 0 ){

        global $ARMember, $arm_capabilities_global, $arm_slugs;

        $arm_manage_license_key = !isset(($arm_capabilities_global['arm_manage_license']))?$arm_slugs->licensing:$arm_capabilities_global['arm_manage_license'];
        $ARMember->arm_check_user_cap($arm_manage_license_key, '1', '0');

        if( !function_exists('WP_Filesystem' ) ){
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        WP_Filesystem();
        global $wp_filesystem;
        $arm_autoloadfile = MEMBERSHIP_DIR . "/autoload.php";
        $arm_autoloadfile_content = $wp_filesystem->get_contents($arm_autoloadfile);

        $write_file = 0;
        if( !empty ( $arm_autoloadfile_content ) )
        {
            if( strpos( $arm_autoloadfile_content, 'armember_check_plugin_copy =' ) !== false )
            {
                $arm_change_flag_search = '1';
                $arm_change_flag_replace = '0';
                if( !empty( $flag ) )
                {
                    $arm_change_flag_search = '0';
                    $arm_change_flag_replace = '1';
                }
                $arm_autoloadfile_content = str_replace('armember_check_plugin_copy = '.$arm_change_flag_search.';', 'armember_check_plugin_copy = '.$arm_change_flag_replace.';', $arm_autoloadfile_content);

                $write_file = $wp_filesystem->put_contents($arm_autoloadfile, $arm_autoloadfile_content, FS_CHMOD_FILE);
            }
        }

        return $write_file;
    }
}
}

global $ARMember, $arm_debug_payment_log_id, $arm_debug_general_log_id;
$ARMember = new ARMember();
$arm_debug_payment_log_id = 0;
$arm_debug_general_log_id = 0;


if( !class_exists('ARM_rename_wp') ){
class ARM_rename_wp{
        
        var $enable_rename_wp;
        var $new_wp_admin_name;
        public $arm_replace;
        public $arm_rewrites;

        function __construct(){

        	if( isset($_REQUEST['action']) && $_REQUEST['action'] == 'arm_logout_after_rename_wp' ){
        		wp_destroy_current_session();
		        wp_clear_auth_cookie();
		        $_REQUEST['redirect_to'] = $redirect_to = wp_login_url();
		        wp_redirect($redirect_to);
		        exit;
        	}

            $global_settings = get_option('arm_global_settings');

            $all_settings = maybe_unserialize($global_settings);
            
            $all_settings = $all_settings['general_settings'];

            $this->enable_rename_wp = !empty($all_settings['rename_wp_admin'])?$all_settings['rename_wp_admin']:'';
            $this->new_wp_admin_name = (isset($all_settings['new_wp_admin_path']) && $all_settings['new_wp_admin_path'] != '') ? $all_settings['new_wp_admin_path'] : 'wp-admin';

            register_deactivation_hook(MEMBERSHIP_DIR.'/armember.php',array($this,'arm_flush_rules'));

            $this->armBuildRedirect();
            add_action('generate_rewrite_rules',array($this,'arm_rewrite_rules'),1);

            if( get_option('permalink_structure')){
                add_filter('admin_url',array($this,'admin_url'),1,1);
                add_filter('network_admin_url',array($this,'network_admin_url'),1,1);
                add_filter('site_url',array($this,'site_url'),1,2);
                add_filter('login_redirect',array($this,'arm_sanitize_redirect'),1,1);
                add_action('wp_logout',array($this,'arm_wp_logout'),1,1);

                $this->armHideUrl();
            }

            global $arm_ajaxurl;
            $arm_ajaxurl = admin_url('admin-ajax.php');
            
            add_action('admin_notices',array($this,'arm_license_admin_notices'));

            add_action( 'admin_enqueue_scripts', array($this, 'arm_load_script' ) );
            add_action( 'wp_ajax_dismiss_admin_notice', array($this, 'arm_dismiss_admin_notice' ) );

            if(!empty($GLOBALS['wp_version']) && version_compare( $GLOBALS['wp_version'], '5.7.2', '>' ))
            {
                add_filter('block_categories_all', array($this,'arm_gutenberg_category'), 10, 2);
            }
            else {
                add_filter('block_categories', array($this,'arm_gutenberg_category'), 10, 2);
            }

            add_action('enqueue_block_editor_assets',array($this,'arm_enqueue_gutenberg_assets'));

            add_action('activated_plugin',array($this,'arm_is_addon_activated'),11,2);

        }

        function arm_is_addon_activated($plugin,$network_activation){
            
            global $arm_social_feature,$arm_members_activity,$ARMember;
            $setact = 0;
            global $check_sorting;
            $setact = $arm_members_activity->$check_sorting();
            if( $setact != 0 ){
                return;
            }
			
			
			
            $addon_resp = $arm_social_feature->addons_page();
            $armember_addons = array();
            if ($addon_resp != "") {
                $resp = explode("|^^|", $addon_resp);
                if ($resp[0] == 1){
                    $myplugarr = array();
                    $myplugarr = unserialize(base64_decode($resp[1]));
                    if( is_array($myplugarr) && count($myplugarr) > 0 ){
                        foreach( $myplugarr as $plug){
                            if( isset( $plug['plugin_installer'] ) )
                            {
                                $armember_addons[$plug['plugin_installer']] = isset( $plug['full_name'] ) ? $plug['full_name'] : '';
                            }
                        }
                    }
                }
            }
            
            if( is_array($armember_addons) && count($armember_addons) > 0 && array_key_exists($plugin, $armember_addons) && $setact == 0 ){
                
				$ARMember->arm_session_start(true);
                //$_SESSION['arm_deactivate_plugin'] = $armember_addons[$plugin];
                deactivate_plugins($plugin, TRUE);
                header('Location: ' . network_admin_url('plugins.php?deactivate=true&arm_license_deactivate=true&arm_deactivate_plugin='.$armember_addons[$plugin]));
                die;
            }
			
        }

        function arm_gutenberg_category($category,$post){
            $new_category = array(
                array(
                    'slug' => 'armember',
                    'title' => 'ARMember Blocks'
                )
            );

            $final_categories = array_merge($category,$new_category);

            return $final_categories;
        }

        function arm_enqueue_gutenberg_assets(){
            global $arm_version,$arm_subscription_plans, $arm_gutenberg_block_restriction;
            $server_php_self = isset($_SERVER['PHP_SELF']) ? basename(sanitize_text_field($_SERVER['PHP_SELF'])) : '';
            if( !in_array( $server_php_self, array( 'site-editor.php', 'widgets.php' ) ) ) {
                $all_membership_plans = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');

                $all_plan_values_and_labels = array(
                    array('value' => 'any_plan' , 'label' => esc_html__('Any Plan', 'ARMember')),
                    array('value' => 'unregistered' , 'label' => esc_html__('Non Loggedin Users', 'ARMember')),
                    array('value' => 'registered' , 'label' => esc_html__('Loggedin Users', 'ARMember'))
                );
                if(!empty($all_membership_plans)) { 
                    foreach( $all_membership_plans as $plan ) {        
                        $all_plan_values_and_labels[] = array( 'value' => $plan['arm_subscription_plan_id'], 'label' => $plan['arm_subscription_plan_name'] );
                    }
                }

                wp_register_script('arm_gutenberg_script',MEMBERSHIP_URL.'/js/arm_gutenberg_script.js',array('wp-blocks','wp-element', 'wp-i18n', 'wp-components','wp-editor','wp-block-editor'),$arm_version);
                wp_enqueue_script('arm_gutenberg_script');
                
                $is_gutenberg_block_restriction_feature = $arm_gutenberg_block_restriction->isGutenbergBlockRestrictionFeature;
                $arm_block_esc_html = array();
                $arm_block_esc_html['membership_shortcodes'] = array(
                    'block_title' => esc_html__('Membership Shortcodes', 'ARMember'),
                    'keywords' => array(esc_html__('Membership', 'ARMember'),esc_html__('ARMember', 'ARMember'),esc_html__('Shortcode', 'ARMember')),
                );
                $arm_block_esc_html['restrict_content_shortcode'] = array(
                    'block_title' => esc_html__('Restrict Content Shortcode', 'ARMember'),
                    'keywords' => array(esc_html__('Membership', 'ARMember'),esc_html__('ARMember', 'ARMember'),esc_html__('Restriction', 'ARMember')),
                );
                $arm_block_esc_html['armember_block_restriction'] = array(
                    'block_title' => esc_html__('ARMember Block Restriction', 'ARMember'),
                    'keywords' => array(esc_html__('Membership', 'ARMember'),esc_html__('ARMember', 'ARMember'),esc_html__('Block', 'ARMember'),esc_html__('Restriction', 'ARMember')),
                    'description' => esc_html__('Nest blocks within this wrapper to control the inner block visibility by membership plans or for free membership only.', 'ARMember'),
                    'restriction_type' => array( 
                        'type' =>  esc_html__( "Restriction Type", "ARMember" ),
                        'show' => esc_html__("Show content only for", "ARMember"),
                        'hide' => esc_html__("Hide content only for", "ARMember"),
                    ),
                    'membership_plan' => esc_html( "Membership Plans", "ARMember" ),
                    'membership_plan_help' => esc_html__("If any of the following conditions will true then restrictions will apply on content", "ARMember"),
                );


                wp_localize_script( 'arm_gutenberg_script', 'armember_block_admin', array(
                    'all_membership_plans' => $all_plan_values_and_labels,
                    'arm_gutenberg_block_restriction_feature' => $is_gutenberg_block_restriction_feature,
                    'arm_block_esc_html' => $arm_block_esc_html,
                ));

                wp_register_style('arm_gutenberg_style',MEMBERSHIP_URL.'/css/arm_gutenberg_style.css',array(), $arm_version);
                wp_enqueue_style('arm_gutenberg_style');
            }

        }


        function arm_load_script() {

            global $wp_scripts;

            if( $this->new_wp_admin_name != 'wp-admin' && $this->enable_rename_wp == 1){

                $arm_wp_script_data = $wp_scripts->get_data('wp-util', 'data');
                
                $arm_default_site_path=get_site_url( '', 'wp-admin/admin-ajax.php', 'relative' );
                $arm_default_site_path=wp_json_encode($arm_default_site_path);

                $remove_amr_script_var='var _wpUtilSettings = {"ajax":{"url":'.$arm_default_site_path.'}};';
                
                if(!is_array($arm_wp_script_data)) {
                    $arm_wp_script_data=str_replace($remove_amr_script_var,' ', $arm_wp_script_data);
                }
                if(empty($arm_wp_script_data)){
                    $wp_scripts->add_data('wp-util', 'data','');
                }else{
                    $wp_scripts->add_data('wp-util', 'data',$arm_wp_script_data);
                }
                
                $wp_scripts->localize(
                    'wp-util',
                    '_wpUtilSettings',
                    array(
                        'ajax' => array(
                            'url' => admin_url( 'admin-ajax.php', 'relative' ),
                        ),
                    )
                );
            }
            
            if(is_customize_preview()) return;
            
            wp_enqueue_script('arm-admin-dismissible-notices', MEMBERSHIP_URL . '/js/dismiss_admin_notice.js', array(), MEMBERSHIP_VERSION);

            wp_localize_script(
                'dismissible-notices',
                'dismissible_notice',
                array(
                    'nonce' => wp_create_nonce( 'dismissible-notice' ),
                )
            );
        }

        function arm_dismiss_admin_notice() {
            $option_name        = sanitize_text_field( $_POST['option_name'] ); //phpcs:ignore 
            $dismissible_length = sanitize_text_field( $_POST['dismissible_length'] ); //phpcs:ignore
            $transient          = 0;
            if ( 'forever' != $dismissible_length ) {
                // If $dismissible_length is not an integer default to 1
                //$dismissible_length = ( 0 == absint( $dismissible_length ) ) ? 1 : $dismissible_length;
                $dismissible_length = 1;
                //$transient          = absint( $dismissible_length ) * DAY_IN_SECONDS;

                $transient          = time() + ($dismissible_length * MONTH_IN_SECONDS);
                $dismissible_length = strtotime( absint( $dismissible_length ) . ' month' );
            }
            //check_ajax_referer( 'dismissible-notice', 'nonce' );
            $return = set_site_transient( $option_name, $dismissible_length, $transient );
            wp_die();
        }

        function arm_is_admin_notice_active( $arg ) {
            $array       = explode( '-', $arg );
            $length      = array_pop( $array );
            $option_name = implode( '-', $array );
            $db_record   = get_site_transient( $option_name );
            
            if($db_record == "")
                return true;

            if ( 'forever' == $db_record ) {
                return false;
            } elseif ( absint( $db_record ) >= time() ) {
                return false;
            } else {
                return true;
            }
        }

        function arm_license_admin_notices(){
            global $ARMember,$arm_email_settings, $arm_global_settings;
            
            $arm_check_is_gutenberg_page = $ARMember->arm_check_is_gutenberg_page();
            if($arm_check_is_gutenberg_page)
            {
                return;
            }
            
            $class = 'notice notice-error arf-notice-update-warning is-dismissible';
            global $arm_members_activity;
            $setact = 0;
            global $check_sorting;
            $setact = $arm_members_activity->$check_sorting();
            if($setact != 1)
            {
                if ( $this->arm_is_admin_notice_active( 'notice-one-2' ) ) {

                $admin_css_url = admin_url('admin.php?page=arm_manage_license');
                printf( '<div data-dismissible="notice-one-2" class="%1$s"><p><b>ARMember license is not activated. To receive regular updates, please activate license from <a href="%2$s">here</a></b></p></div>', esc_attr( $class ), esc_html( $admin_css_url )); 
                }
            }

            global $armember_check_plugin_copy;

            $ordering = array();

            $pcodeinfo = "";
            $pcodedate = "";
            $pcodedateexp = "";
            $pcodelastverified = "";
            $pcodecustemail = "";

            if( !empty( $armember_check_plugin_copy ) )
            {
                $get_purchased_info = get_option('armSortInfo');
                $sortorderval = base64_decode($get_purchased_info);

                $ordering = explode("^", $sortorderval);

                if (is_array($ordering)) { 
             
                if (isset($ordering[2]) && $ordering[2] != "") {
                    $pcodedateexp = $ordering[2];
                } else {
                    $pcodedateexp = "";
                }
            }
            else {
                $get_purchased_info = json_decode( get_option('arm_pkg_data_actvte_respnc'), true );
                if( !empty ( $get_purchased_info['expires']) )
                {
                    $pcodedateexp = $get_purchased_info['expires'];
                }
            }

            if (is_array($ordering)) { 

                if($pcodedateexp != "")
                { 
                    $exp_date=strtotime($pcodedateexp);
                    $today = strtotime("today"); 

                if($exp_date < $today)
                {
                    if ( $this->arm_is_admin_notice_active( 'notice-two-2' ) ) {
                    
                        $admin_css_url = admin_url('admin.php?page=arm_manage_license');
                        printf( '<div data-dismissible="notice-two-2" class="%1$s"><p><b>It seems your ARMember support period is expired. To continue receiving our prompt support you need to renew your support. Please <a href="%2$s">click  here</a> to extend support.</b></p></div>', esc_attr( $class ), esc_html( $admin_css_url ));  
                    }
                }
                }
                }
            }
			
            if( isset($_GET['arm_license_deactivate']) && isset($_GET['arm_deactivate_plugin']) && $_GET['arm_deactivate_plugin'] != '' ){
                $arm_deactivate_plugin = sanitize_text_field($_GET['arm_deactivate_plugin']);
                echo "<div class='notice notice-error arm_auto_deactivate_addon_plugin' style='background:#dc3232;border-left-color:#dc3232;color:#fff;font-weight:bold;'><p>".sprintf(esc_html__('Please activate %s license to enable %s','ARMember'),'ARMember',$arm_deactivate_plugin)."</p></div>";//phpcs:ignore
                unset($_GET['arm_deactivate_plugin']);
            }
        }


        function arm_flush_rules(){
            global $wp_rewrite,$arm_global_settings;
            $new_settings = maybe_unserialize(get_option('arm_global_settings'));
            $arm_general_settings = $new_settings['general_settings'];
            if( $arm_general_settings['rename_wp_admin'] == 1 ){
                $new_wp_admin_name = $arm_general_settings['new_wp_admin_path'];
                $removeTag = $new_wp_admin_name.'/(.*)';
                $wp_rewrite->remove_rewrite_tag($removeTag);
                $arm_global_settings->remove_config_file();
                require_once ABSPATH . 'wp-admin/includes/misc.php';
                require_once ABSPATH . 'wp-admin/includes/file.php';
                $new_settings['general_settings']['rename_wp_admin'] = 0;
                $new_settings['general_settings']['new_wp_admin_path'] = 'wp-admin';
                $new_settings['general_settings']['temp_wp_admin_path'] = 'wp-admin';
                update_option('arm_global_settings',$new_settings);
                if( function_exists('save_mod_rewrite_rules')){
                    save_mod_rewrite_rules();
                }
                
                $active_plugins = get_option('active_plugins');
                $armember_plugin = 'armember/armember.php';
                if( in_array($armember_plugin,$active_plugins) ){
                    $key = array_search($armember_plugin,$active_plugins);
                    unset($active_plugins[$key]);
                    $active_plugins = array_values($active_plugins);
                    update_option('active_plugins',$active_plugins);
                }

                wp_destroy_current_session();
                wp_clear_auth_cookie();
                wp_logout();
                die();
            }
        }

        function arm_rewrite_rules_array($rules){
            global $ARMember,$wp_rewrite;
            return $rules;
        }

        function isHtaccessWritable(){
            if( is_multisite() ){
                return false;
            }

            global $wp_rewrite;

            $home_path = get_home_path();
            $htaccess_file = $home_path . '.htaccess';

            if ((!file_exists($htaccess_file) && is_writable($home_path) && $wp_rewrite->using_mod_rewrite_permalinks()) || is_writable($htaccess_file)) {
                if (got_mod_rewrite()) {
                    return true;
                }
            }

            return false;
        }

        function site_url($url,$path){

            if( $url == '' ){
                return $url;
            }

            return $url;
        }

        function arm_sanitize_redirect($redirect){
            return $redirect;
        }

        function armBuildRedirect(){
            $default_admin_url = admin_url();
            if( $this->new_wp_admin_name != 'wp-admin'){
                $this->arm_replace['to'][] = $this->new_wp_admin_name.'/';
                $this->arm_replace['from'][] = 'wp-admin/';
                $this->arm_replace['rewrite'][] = true;
            }
            return $this;
        }

        function arm_rewrite_rules($wp_rewrite){
            global $ARMember;
            require_once ABSPATH . 'wp-admin/includes/misc.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';

            $rewrites = array();
            $rewritecode = '';

            if( !empty($this->arm_replace)){
                foreach($this->arm_replace['to'] as $key => $replace ){
                    if( $this->arm_replace['rewrite'][$key]){
                        $rewrites[] = array(
                            'from' => $this->arm_replace['to'][$key].'(.*)',
                            'to' => $this->arm_replace['from'][$key].'$'.(substr_count($this->arm_replace['to'][$key], '(') + 1)
                        );
                    }
                }

                $this->arm_rewrites = array();
                $arm_non_wp_rules = array();
                foreach($rewrites as $rewrite){
                    add_rewrite_tag($rewrite['from'], $rewrite['to']);
                    $arm_non_wp_rules[$rewrite['from']] = $rewrite['to'];
                }

                $this->arm_rewrites = $rewrites;
                $wp_rewrite->non_wp_rules = $arm_non_wp_rules + $wp_rewrite->non_wp_rules;
                if( function_exists('save_mod_rewrite_rules') ){
                    if(!save_mod_rewrite_rules()){
                        return false;
                    }
                } else {
                    return false;
                }
            }
            return true;
        }

        function admin_url($url){

            if (!defined('ADMIN_COOKIE_PATH')) {
                return $url;
            }

            if( $this->new_wp_admin_name == 'wp-admin'){
                return $url;
            }

            if( $this->enable_rename_wp == 1){
                $find = '/wp-admin/';
                $replace = '/'.$this->new_wp_admin_name.'/';

                if( strpos($url,$find) !== false ){
                    $url = str_replace($find,$replace,$url);
                }
            }
            return $url;
        }

        function network_admin_url($url){
            if (!defined('ADMIN_COOKIE_PATH')) {
                return $url;
            }

            if( $this->new_wp_admin_name == 'wp-admin'){
                return $url;
            }

            if( $this->enable_rename_wp == 1 ){
                $renameTo = $this->new_wp_admin_name;
                $renameFrom = 'wp-admin';
                $find = network_site_url($renameFrom.'/',$renameTo);
                $replace = network_site_url('/'.$renameTo.'/',$renameTo);
                if( strpos($url,$find) === 0){
                    $url = $replace.substr($url,strlen($find));
                }
            }
            return $url;
        }

        function armHideUrl(){

            if(isset($_SERVER['SERVER_NAME'])){
                $url = $_SERVER['REQUEST_URI']; //phpcs:ignore
                if( $url == wp_make_link_relative(get_bloginfo('url')) . '/' . $this->new_wp_admin_name ){
                    wp_redirect(admin_url());
                    exit;
                }
            }



            if( $this->enable_rename_wp == 1 && strpos($_SERVER['REQUEST_URI'],$this->new_wp_admin_name) === false && strpos($_SERVER['REQUEST_URI'],'wp-admin') !== false){ //phpcs:ignore
                    wp_redirect(home_url('/404_Not_Found'));
                    exit;
            }
        }

        function arm_wp_logout(){
            
            if( !isset($_REQUEST['action']) ){
                wp_destroy_current_session();
                wp_clear_auth_cookie();
                // $_REQUEST['redirect_to'] = $redirect_to = network_site_url();
                $redirect_to = isset($_REQUEST['redirect_to']) ? sanitize_url($_REQUEST['redirect_to']) : network_site_url(); //phpcs:ignore
                wp_safe_redirect($redirect_to);
                die();
            }
        }

    }

}