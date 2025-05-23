<?php
if (!class_exists('ARM_email_settings'))
{
	class ARM_email_settings
	{
		var $templates;
		var $isOptInsFeature;
		function __construct()
		{
			global $wpdb, $ARMember, $arm_slugs;
			$is_opt_ins_feature = get_option('arm_is_opt_ins_feature', 0);
			$this->isOptInsFeature = ($is_opt_ins_feature == '1') ? true : false;
			
			add_action('wp_ajax_arm_submit_email_template', array($this, 'arm_submit_email_template'));
			add_action('wp_ajax_arm_edit_template_data', array($this, 'arm_edit_template_data'));
			add_action('wp_ajax_arm_update_email_template_status', array($this, 'arm_update_email_template_status'));

			add_action('wp_ajax_arm_update_opt_ins_settings', array($this, 'arm_update_opt_ins_settings'));

			
			$this->templates = new stdClass;
			$this->templates->new_reg_user_admin = 'new-reg-user-admin';
			$this->templates->new_reg_user_with_payment = 'new-reg-user-with-payment';
			$this->templates->new_reg_user_without_payment = 'new-reg-user-without-payment';
			$this->templates->email_verify_user = 'email-verify-user';			
			$this->templates->account_verified_user = 'account-verified-user';
			$this->templates->change_password_user = 'change-password-user';	
			$this->templates->forgot_passowrd_user = 'forgot-passowrd-user';
			$this->templates->profile_updated_user = 'profile-updated-user';
                        $this->templates->profile_updated_notification_to_admin = 'profile-updated-notification-admin';
			$this->templates->grace_failed_payment = 'grace-failed-payment';
			$this->templates->grace_eot = 'grace-eot';
			$this->templates->failed_payment_admin = 'failed-payment-admin';
			$this->templates->on_menual_activation = 'on-menual-activation';

			add_filter('arm_pro_email_notification_automated_notification',array($this,'arm_pro_email_notification_automated_notification_func'),10,1);

			add_filter('arm_pro_email_notification_automated_notification_form',array($this,'arm_pro_email_notification_automated_notification_form_func'),10,1);

			add_filter('arm_pro_add_new_auto_messages_btn',array($this,'arm_pro_add_new_auto_messages_btn_func'),10,1);
		}

		function arm_pro_add_new_auto_messages_btn_func($arm_pro_add_new_auto_message_btn){
			global $wpdb, $ARMember;
			$messages = $wpdb->get_results("SELECT * FROM `".$ARMember->tbl_arm_auto_message."` ORDER BY `arm_message_id` DESC");
			if ( empty( $messages ) ) {
				
				$arm_pro_add_new_auto_message_btn = '<div class="arm_add_new_item_box" style="margin: 0 0 20px 0;">			
					<a class="greensavebtn arm_add_new_message_btn arm_margin_right_20" href="javascript:void(0);" ><img align="absmiddle" src="'. MEMBERSHIP_IMAGES_URL .'/add_new_icon.png"><span>'. esc_html__('Add New Response', 'ARMember') .'</span></a>
				</div>';
			}
			return $arm_pro_add_new_auto_message_btn;
		}

		function arm_pro_email_notification_automated_notification_form_func(){
			
			if ( file_exists( MEMBERSHIP_VIEWS_DIR . '/arm_add_edit_template_forms.php' ) ) {
				include MEMBERSHIP_VIEWS_DIR . '/arm_add_edit_template_forms.php';
			}
		}

		function arm_pro_email_notification_automated_notification_func(){
			global $wpdb, $ARMember;
			$messages = $wpdb->get_results("SELECT * FROM `".$ARMember->tbl_arm_auto_message."` ORDER BY `arm_message_id` DESC");
			if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_email_templates.php') && !empty($messages)) {
				include( MEMBERSHIP_VIEWS_DIR . '/arm_email_templates.php');
			}
		}

		function arm_get_email_template($temp_slug)
		{
			global $wpdb,$ARMember;
			$res = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$ARMember->tbl_arm_email_templates."` WHERE `arm_template_slug`=%s",$temp_slug)); //phpcs:ignore --Reason $ARMember->tbl_arm_email_templates is a table name
			if (!empty($res)) {
				$res->arm_template_subject = isset($res->arm_template_subject) ? stripslashes($res->arm_template_subject) : '';
				$res->arm_template_content = isset($res->arm_template_content) ? stripslashes($res->arm_template_content) : '';

				return $res;
			}
			return false;
		}
		function arm_update_email_settings()
		{
			$arm_email_from_name = isset($_POST['arm_email_from_name']) ? sanitize_text_field($_POST['arm_email_from_name']) : ''; //phpcs:ignore
			$arm_email_from_email = isset($_POST['arm_email_from_email']) ? sanitize_email($_POST['arm_email_from_email']) : ''; //phpcs:ignore
			$arm_email_admin_email = isset($_POST['arm_email_admin_email']) ? sanitize_text_field($_POST['arm_email_admin_email']) : ''; //phpcs:ignore
			$server = isset($_POST['arm_email_server']) ? sanitize_text_field($_POST['arm_email_server']) : 'wordpress_server'; //phpcs:ignore

			$arm_mail_authentication = isset($_POST['arm_mail_authentication']) ? intval($_POST['arm_mail_authentication']) : '0'; //phpcs:ignore
			$smtp_mail_server = isset($_POST['arm_mail_server']) ? sanitize_text_field($_POST['arm_mail_server']) : '';//phpcs:ignore
			$smtp_mail_port = isset($_POST['arm_mail_port']) ? sanitize_text_field($_POST['arm_mail_port']) : '';//phpcs:ignore
			$smtp_mail_login_name = isset($_POST['arm_mail_login_name']) ? sanitize_text_field($_POST['arm_mail_login_name']) : '';//phpcs:ignore
			$smtp_mail_password = isset($_POST['arm_mail_password']) ? $_POST['arm_mail_password'] : ''; //phpcs:ignore
			$smtp_mail_enc = isset($_POST['arm_smtp_enc']) ? sanitize_text_field($_POST['arm_smtp_enc']) : 'none';//phpcs:ignore
			
			/** Google */
			$is_email_verified = isset($_POST['arm_gmail_verified_status']) ? sanitize_text_field( $_POST['arm_gmail_verified_status'] ) : '';//phpcs:ignore
			$old_settings = $this->arm_get_all_email_settings();
			$email_tools = (isset($old_settings['arm_email_tools'])) ? $old_settings['arm_email_tools'] : array();
			$email_tools['aweber']['consumer_key'] = '';
			$email_tools['aweber']['consumer_secret'] = '';
			$email_settings = array(
				'arm_email_from_name' => $arm_email_from_name,
				'arm_email_from_email' => $arm_email_from_email,
                                'arm_email_admin_email' => $arm_email_admin_email,
				'arm_email_server' => $server,
				'arm_mail_server' => $smtp_mail_server,
				'arm_mail_port' => $smtp_mail_port,
				'arm_mail_login_name' => $smtp_mail_login_name,
				'arm_mail_password' => $smtp_mail_password,
				'arm_smtp_enc' => $smtp_mail_enc,
				'arm_email_tools' => $email_tools,
				'arm_mail_authentication' => $arm_mail_authentication,
			);
			if($server == 'google_gmail' && $_POST['arm_gmail_verified_status'] == 1)//phpcs:ignore
			{
				$email_settings['arm_email_server'] = 'google_gmail';
				$email_settings['arm_google_client_id'] = isset($_POST['arm_google_client_id']) ? sanitize_text_field($_POST['arm_google_client_id']) : '';//phpcs:ignore
				$email_settings['arm_google_client_secret'] = isset($_POST['arm_google_client_secret']) ? sanitize_text_field($_POST['arm_google_client_secret']) : '';//phpcs:ignore
				$email_settings['arm_google_auth_url'] = isset($_POST['arm_google_auth_url']) ? sanitize_text_field($_POST['arm_google_auth_url']) : '';//phpcs:ignore
				$email_settings['arm_google_auth_token'] = isset($_POST['arm_google_auth_token']) ? sanitize_text_field($_POST['arm_google_auth_token']) : '';//phpcs:ignore
				$email_settings['arm_gmail_verified_status'] = isset($_POST['arm_gmail_verified_status']) ? intval($_POST['arm_gmail_verified_status']) : 0;//phpcs:ignore
				$email_settings['arm_google_connected_account'] = isset($_POST['arm_google_connected_account']) ? sanitize_text_field($_POST['arm_google_connected_account']) : '';//phpcs:ignore
				$email_settings['arm_google_auth_response'] = isset($_POST['arm_google_auth_response']) ? sanitize_textarea_field($_POST['arm_google_auth_response']) : '';//phpcs:ignore
			}
			update_option('arm_email_settings', $email_settings);
		}
		function arm_update_opt_ins_settings()
		{
			global $ARMember, $arm_capabilities_global;
			$response = array('type' => 'error', 'msg' => esc_html__('There is an error while updating opt-ins settings, please try again.', 'ARMember'));
			$posted_data = array_map( array( $ARMember, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore
			if (isset($posted_data['action']) && $posted_data['action'] == 'arm_update_opt_ins_settings') {
				$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1',1); //phpcs:ignore --Reason:Verifying nonce
				$email_settings = $this->arm_get_all_email_settings();
				$email_tools = (isset($posted_data['arm_email_tools'])) ? $posted_data['arm_email_tools'] : array();
				$old_email_tools = (isset($email_settings['arm_email_tools'])) ? $email_settings['arm_email_tools'] : array();
			
				$email_tools = apply_filters('arm_change_optin_settings_before_save', $email_tools);
				
				$email_settings['arm_email_tools'] = arm_array_map($email_tools);
				update_option('arm_email_settings', $email_settings);
                                
				do_action('arm_update_add_on_opt_in_settings', $_POST);//phpcs:ignore
				$response = array('type' => 'success', 'msg' => esc_html__('Opt-ins Settings Saved Successfully.', 'ARMember'));
			}
			echo json_encode($response);
			die();
		}	
		function object2array($object) {

		    return @json_decode(@json_encode($object), 1);
		}
		function arm_get_optin_settings()
		{
			global $wpdb, $ARMember;
			$emailTools = array();
			if ($this->isOptInsFeature)
			{
				$email_settings = $this->arm_get_all_email_settings();
				if (isset($email_settings['arm_email_tools']) && !empty($email_settings['arm_email_tools'])) {
					$all_email_tools = $email_settings['arm_email_tools'];
					foreach ($all_email_tools as $tool => $et) {
						if (isset($et['status']) && $et['status'] == '1') {
							$emailTools[$tool] = $et;
						}
					}
				}
				$emailTools = apply_filters('arm_get_optin_settings', $emailTools, $email_settings);
			}
			return $emailTools;
		}
		function arm_get_all_email_settings()
		{
			global $wpdb;
			$email_settings_unser = get_option('arm_email_settings');
			$all_email_settings = maybe_unserialize($email_settings_unser);
			$all_email_settings = apply_filters('arm_get_all_email_settings', $all_email_settings);
			return $all_email_settings;
		}
		function arm_get_single_email_template($template_id, $fields = array())
		{
			global $wpdb, $ARMember;
			if ($template_id == '') {
				return false;
			}
			$select_fields = "*";
			if (is_array($fields) && !empty($fields)) {
				$select_fields = implode(',', $fields);
			}
			$res = $wpdb->get_row( $wpdb->prepare("SELECT $select_fields FROM `".$ARMember->tbl_arm_email_templates."` WHERE  `arm_template_id`=%d",$template_id) );//phpcs:ignore --Reason $ARMember->tbl_arm_email_templates is a table name
			if (!empty($res)) {
				if (!empty($res->arm_template_subject)) {
					$res->arm_template_subject = stripslashes($res->arm_template_subject);
				}
				if (!empty($res->arm_template_content)) {
					$res->arm_template_content = stripslashes($res->arm_template_content);
				}
				return $res;
			}
			return false;
		}
		function arm_get_all_email_template($field = array())
		{
			global $wpdb, $ARMember;
			if (is_array($field) && !empty($field)) {
				$field_name = implode(',', $field);
				$sql = "SELECT " . $field_name . " FROM `".$ARMember->tbl_arm_email_templates."` ORDER BY `arm_template_id` ASC ";
			} else {
				$sql = "SELECT * FROM `".$ARMember->tbl_arm_email_templates."` ORDER BY `arm_template_id` ASC ";
			}
			$results = $wpdb->get_results($sql);//phpcs:ignore --Reason Query is a select without where so no need to prepare
			if (!empty($results->arm_template_subject)) {
				$results->arm_template_subject = stripslashes($results->arm_template_subject);
			}
			if (!empty($results->arm_template_content)) {
				$results->arm_template_content = stripslashes($results->arm_template_content);
			}
			return $results;
		}
		function arm_edit_template_data()
		{
			global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_global_settings, $arm_email_settings, $arm_manage_coupons, $arm_manage_communication, $arm_capabilities_global;
			$return = array('status' => 'error');
			$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_email_notifications'], '1',1); //phpcs:ignore --Reason:Verifying nonce
			if (isset($_REQUEST['action']) && isset($_REQUEST['temp_id']) && $_REQUEST['temp_id'] != '') {
				$template_id = intval($_REQUEST['temp_id']);
				$temp_detail = $arm_email_settings->arm_get_single_email_template($template_id);
				if (!empty($temp_detail)) {
					$return = array(
						'status' => 'success',
						'id' => $template_id,
						'popup_heading' => esc_html(stripslashes($temp_detail->arm_template_name)),
						'arm_template_slug' => $temp_detail->arm_template_slug,
						'arm_template_subject' => esc_html(stripslashes($temp_detail->arm_template_subject)),
						'arm_template_content' => stripslashes($temp_detail->arm_template_content),
						'arm_template_status' => $temp_detail->arm_template_status,
					);
					$return = apply_filters('arm_email_attachment_file_outside',$return);
				}
			}
			echo json_encode($return);
			exit;
		}
		function arm_submit_email_template()
		{
			global $wpdb, $ARMember, $arm_capabilities_global;
			$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_email_notifications'], '1',1); //phpcs:ignore --Reason:Verifying nonce
			$response = array('type'=>'error', 'msg'=>esc_html__('Sorry, Something went wrong. Please try again.', 'ARMember'));
			if (!empty($_POST['arm_template_id']) && intval($_POST['arm_template_id']) != 0)//phpcs:ignore
			{
				$template_id = intval($_POST['arm_template_id']);//phpcs:ignore
				$arm_email_template_subject = (!empty($_POST['arm_template_subject'])) ? sanitize_text_field($_POST['arm_template_subject']) : '';//phpcs:ignore
				$arm_email_template_content = (!empty($_POST['arm_template_content'])) ? $_POST['arm_template_content'] : ''; //phpcs:ignore
				$arm_email_template_status = (!empty($_POST['arm_template_status'])) ? intval($_POST['arm_template_status']) : 0;//phpcs:ignore
				$temp_data = array(
					'arm_template_subject' => $arm_email_template_subject,
					'arm_template_content' => $arm_email_template_content,
					'arm_template_status' => $arm_email_template_status
				);
				$temp_data=apply_filters('arm_email_template_save_before',$temp_data,$_POST);//phpcs:ignore
				$update_temp = $wpdb->update($ARMember->tbl_arm_email_templates, $temp_data, array('arm_template_id' => $template_id));
				$response = array('type'=>'success', 'msg'=>esc_html__('Email Template Updated Successfully.', 'ARMember'));
			}
			echo json_encode($response);
			exit;
		}
		function arm_update_email_template_status($posted_data=array())
		{
			global $wpdb, $ARMember, $arm_capabilities_global;
			$ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_email_notifications'], '1',1); //phpcs:ignore --Reason:Verifying nonce
			$response = array('type'=>'error', 'msg'=>esc_html__('Sorry, Something went wrong. Please try again.', 'ARMember'));
			if (!empty($_POST['arm_template_id']) && $_POST['arm_template_id'] != 0  && intval( $_POST['arm_template_id'] ) )//phpcs:ignore
			{
				$template_id = intval($_POST['arm_template_id']);//phpcs:ignore
				$arm_email_template_status = (!empty($_POST['arm_template_status'])) ? intval($_POST['arm_template_status']) : 0;//phpcs:ignore
				$temp_data = array(
					'arm_template_status' => $arm_email_template_status,
				);
				$update_temp = $wpdb->update($ARMember->tbl_arm_email_templates, $temp_data, array('arm_template_id' => $template_id));
				$response = array('type'=>'success', 'msg'=>esc_html__('Email Template Updated Successfully.', 'ARMember'));
			}
			echo json_encode($response);
			exit;
		}
		function arm_insert_default_email_templates()
		{
			global $wpdb, $ARMember;
			$default_email_template = $this->arm_default_email_templates();
			if (!empty($default_email_template)) {
				foreach ($default_email_template as $slug => $email_template) {
                    $oldTemp = $this->arm_get_email_template($slug);
                    if (!empty($oldTemp)) {
                        continue;
                    } else {
                        $email_template['arm_template_slug'] = $slug;
                        $email_template['arm_template_status'] = '1';
                        $ins = $wpdb->insert($ARMember->tbl_arm_email_templates, $email_template);
                    }
				}
			}
		}
		function arm_default_email_templates()
		{
			$temp_slugs = $this->templates;
			$email_templates = array(
				$temp_slugs->new_reg_user_admin => array(
					'arm_template_name' => 'Signup Completed Notification To Admin',
					'arm_template_subject' => 'New user registration at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hello Administrator,</p><br><p>A new user has just registered at {ARM_BLOGNAME}. Here are some basic details of that newly registered user.</p><br><p>Firstname: {ARM_FIRST_NAME}</p><br><p>Lastname: {ARM_LAST_NAME}</p><br><p>Username: {ARM_USERNAME}</p><br><p>Email: {ARM_EMAIL}</p><br><p>To check further details of this user, please click on the following link:</p><br><p>{ARM_PROFILE_LINK}</p><br><br><p>Thank You</p><br><p>{ARM_BLOGNAME}</p>',
				),
				$temp_slugs->new_reg_user_with_payment => array(
					'arm_template_name' => 'Signup Completed (With Payment) Notification To User',
					'arm_template_subject' => 'Confirmation of your membership at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Thank you for subscribing to the {ARM_PLAN} at {ARM_BLOGNAME}.</p><br><p>You can review and edit your membership details here:</p><br><p>{ARM_PROFILE_LINK}</p><br><p>Here is your latest payment information:</p><br><p>Paid With: {ARM_PAYMENT_GATEWAY}</p><br><p>Plan Name: {ARM_PLAN}</p><br><p>Plan Type: {ARM_PAYMENT_TYPE}</p><br><p>Amount: {ARM_PLAN_AMOUNT}</p><br><p>Transaction Id: {ARM_TRANSACTION_ID}</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->new_reg_user_without_payment => array(
					'arm_template_name' => 'Signup Completed (Without Payment) Notification To User',
					'arm_template_subject' => 'Confirmation of your membership at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Thank you for subscribing to the {ARM_PLAN} at {ARM_BLOGNAME}.</p><br><p>You can review and edit your membership details here:</p><br><p>{ARM_PROFILE_LINK}</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->email_verify_user => array(
					'arm_template_name' => 'Email Verification',
					'arm_template_subject' => 'Email verification at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>You must confirm/validate your email account before logging in.</p><br><p>Please click on the following link to  activate your account:</p><br><p>{ARM_VALIDATE_URL}</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->account_verified_user => array(
					'arm_template_name' => 'Email Verified',
					'arm_template_subject' => 'Email verified successfully at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Your account is now verified at {ARM_BLOGNAME}.</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->change_password_user => array(
					'arm_template_name' => 'Change Password',
					'arm_template_subject' => 'Your password has been changed at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Your Password has been changed.</p><br><p>To login please fill out your credentials on:</p><br><p>{ARM_LOGIN_URL}</p><br><p>Your Username: {ARM_USERNAME}</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->forgot_passowrd_user => array(
					'arm_template_name' => 'Forgot Password',
					'arm_template_subject' => 'Reset password request at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Someone requested that the password be reset for the following account: {ARM_BLOG_URL}</p><br><p>Username: {ARM_USERNAME},</p><br><p>If this was a mistake, just ignore this email and nothing will happen.</p><br><p>To reset your password, visit the following address:{ARM_RESET_PASSWORD_LINK}</p><br><p>If you have any problems, please contact us at {ARM_ADMIN_EMAIL}.</p>',
				),
				$temp_slugs->profile_updated_user => array(
					'arm_template_name' => 'Profile Updated',
					'arm_template_subject' => 'Your account has been updated at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Your account has been updated.</p><br><p>To visit your profile page follow the next link:</p><br>
<p>{ARM_PROFILE_LINK}</p><br><p>Have a nice day!</p>',
				),
                                $temp_slugs->profile_updated_notification_to_admin => array(
					'arm_template_name' => 'Profile Updated Notification To Admin',
					'arm_template_subject' => 'Account of {ARM_USERNAME} has been updated at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hello Administrator,</p><br><p>An account has been updated at {ARM_BLOGNAME}. Here are some basic details of that updated user.</p><br><p>Firstname: {ARM_FIRST_NAME}</p><br><p>Lastname: {ARM_LAST_NAME}</p><br><p>Username: {ARM_USERNAME}</p><br><p>Email: {ARM_EMAIL}</p><br><br><p>Thank You</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->grace_failed_payment => array(
					'arm_template_name' => 'Grace Period For Failed Payment',
					'arm_template_subject' => 'Reminder for failed payment at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Unfortunately your recurring payment for {ARM_PLAN} at {ARM_BLOGNAME} has not succeeded for some reason.</p><br><p>Here are some payment details:</p><br><p>Paid With: {ARM_PAYMENT_GATEWAY}</p><br><p>Amount: {ARM_PLAN_AMOUNT}</p><br><p>Please contact the payment service provider about this.</p><br><p><strong>Note: </strong>If you do not take appropriate action within {ARM_GRACE_PERIOD_DAYS} days, than any current membership may lapse.</p><br><p>If you have any further queries, feel free to contact us at {ARM_BLOGNAME}</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->grace_eot => array(
					'arm_template_name' => 'User Enters Grace Period Notification',
					'arm_template_subject' => 'Reminder for membership expiration at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Your {ARM_PLAN} membership has just expired.</p><br><p>But you can still access our website without any problem,</p><br><p>If you want to renew/update your membership plan, than please click on the following link:</p><br><p>{ARM_BLOG_URL}</p><br><p><strong>Note: </strong>If you do not renew/change your membership within {ARM_GRACE_PERIOD_DAYS} days, than the relevant action will be performed by system.</p><br><p>Have a nice day!</p>',
				),
				$temp_slugs->failed_payment_admin => array(
					'arm_template_name' => 'Failed Payment Notification To Admin',
					'arm_template_subject' => 'Reminder for failed payment at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hello Administrator,</p><br><p>This is a reminder that the following member\'s recurring payment for {ARM_PLAN} membership has failed for some reason at {ARM_BLOGNAME}</p><br><p>Here are some details.</p><br><p>Username: {ARM_USERNAME}</p><br><p>Email: {ARM_EMAIL}</p><br><p>Paid With: {ARM_PAYMENT_GATEWAY}</p><br><p>Amount: {ARM_PLAN_AMOUNT}</p><br><p>Please take appropriate action.</p><br><p>Thank You.</p><br>',
				),
                                $temp_slugs->on_menual_activation => array(
					'arm_template_name' => 'Manual User Activation',
					'arm_template_subject' => 'Your account has been activated at {ARM_BLOGNAME}',
					'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Your Account has been activated.</p><br><p> Please click on the following link:</p><br><p>{ARM_BLOG_URL}</p><br><p>Have a nice day!</p>',
				),
			);
			$email_templates = apply_filters('arm_default_email_templates', $email_templates);
			return $email_templates;
		}
	}
}
global $arm_email_settings;
$arm_email_settings = new ARM_email_settings();