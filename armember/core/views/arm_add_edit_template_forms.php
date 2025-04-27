<?php
global $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_email_settings, $arm_social_feature, $arm_slugs, $arm_subscription_plans, $arm_manage_communication;

$arm_all_email_settings = $arm_email_settings->arm_get_all_email_settings();
$template_list = $arm_email_settings->arm_get_all_email_template();
$messages = $wpdb->get_results("SELECT * FROM `".$ARMember->tbl_arm_auto_message."` ORDER BY `arm_message_id` DESC"); //phpcs:ignore --Reason $ARMember->tbl_arm_auto_message is a table name and query is without where so need to skip
$message_types = array(
    'on_new_subscription' => esc_html__('On New Subscription', 'ARMember'),
    'on_change_subscription' => esc_html__('On Change Subscription', 'ARMember'),
    'on_change_subscription_by_admin' => esc_html__('On Change Subscription By Admin', 'ARMember'),
    'on_renew_subscription' => esc_html__('On Renew Subscription', 'ARMember'),
    'on_recurring_subscription' => esc_html__('On Recurring Subscription', 'ARMember'),
    'on_cancel_subscription' => esc_html__('On Cancel Membership', 'ARMember'),
    'before_expire' => esc_html__('Before Membership Expired', 'ARMember'),
    'manual_subscription_reminder' => esc_html__('Before Semi Automatic Subscription Payment due','ARMember'),
    'automatic_subscription_reminder' => esc_html__('Before Automatic Subscription Payment due','ARMember'),
    'on_close_account' => esc_html__('On Close User Account', 'ARMember'),
    'on_login_account' => esc_html__('On User Login', 'ARMember'),
    
    'on_expire' => esc_html__('On Membership Expired', 'ARMember'),
    'on_failed' => esc_html__('On Failed Payment', 'ARMember'),
    'on_next_payment_failed' => esc_html__('On Semi Automatic Subscription Failed Payment', 'ARMember'),
    'trial_finished' => esc_html__('Trial Finished', 'ARMember'),
    'on_purchase_subscription_bank_transfer' => esc_html__('On Purchase membership plan using Bank Transfer','ARMember'),
    
);

$message_types = apply_filters('arm_notification_add_message_types',$message_types);

$get_page = !empty($_GET['page']) ? sanitize_text_field( $_GET['page'] ) : '';

$form_id = 'arm_add_message_wrapper_frm';
$mid = 0;
$edit_mode = false;
$msg_type = 'on_new_subscription';
$local = get_locale();
$local = apply_filters('arm_get_current_locale',$local);
?>
<div class="add_new_message_wrapper arm_common_setting_multi_language_wrapper popup_wrapper" >
	<form method="post" action="#" id="<?php echo esc_attr($form_id);?>" class="arm_admin_form arm_communication_message_wrapper_frm">
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="add_new_message_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php esc_html_e('Add New Response','ARMember');?></td>
				<td class="popup_content_text">
					<table class="arm_table_label_on_top">	
						<tr class="">
							<th><?php esc_html_e('Message To Be Sent', 'ARMember');?></th>
							<td>
								<div class="arm_message_period_post">
									<input type='hidden' id='arm_message_type' class="arm_message_select_box" name="arm_message_type" value='<?php echo esc_attr($msg_type);?>' />
									<dl class="arm_selectbox column_level_dd arm_width_512">
										<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete" /><i class="armfa armfa-caret-down armfa-lg"></i></dt>
										<dd>
											<ul data-id="arm_message_type">
											<?php foreach($message_types as $type => $label):?>
												<li data-label="<?php echo esc_attr($label);?>" data-value="<?php echo esc_attr($type);?>"><?php echo esc_html($label);?></li>
											<?php endforeach;?>
											</ul>
										</dd>
									</dl>
								</div>
								<div class="arm_message_period_section arm_margin_top_10" >
									<span class=""><?php esc_html_e('Send Message before', 'ARMember'); ?></span>
									<div class="arm_message_periodunit_type arm_margin_left_10" >
										<input type='hidden' id="arm_message_period_unit" class="arm_message_select_box_unit" name="arm_message_period_unit" value="1" />
										<dl class="arm_selectbox column_level_dd arm_width_100">
											<dt><span id="arm_message_period_unit_span"></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_message_period_unit">
													<?php for ($i = 1; $i <= 5; $i++) { ?>
														<li data-label="<?php echo esc_attr($i); ?>" data-value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></li>
													<?php } ?>
												</ul>
											</dd>
										</dl>
									</div>
									<div class="arm_message_periodunit_type">
										<input type='hidden' id="arm_message_period_type" class="arm_message_select_box_type" name="arm_message_period_type" value="day" />
										<dl class="arm_selectbox column_level_dd arm_width_120">
											<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
											<dd>
												<ul data-id="arm_message_period_type">
													<li data-label="<?php esc_html_e('Day(s)', 'ARMember');?>" data-value="day"><?php esc_html_e('Day(s)', 'ARMember');?></li>
													<li data-label="<?php esc_html_e('Week(s)', 'ARMember');?>" data-value="week"><?php esc_html_e('Week(s)', 'ARMember');?></li>
													<li data-label="<?php esc_html_e('Month(s)', 'ARMember');?>" data-value="month"><?php esc_html_e('Month(s)', 'ARMember');?></li>
													<li data-label="<?php esc_html_e('Year(s)', 'ARMember');?>" data-value="year"><?php esc_html_e('Year(s)', 'ARMember');?></li>
												</ul>
											</dd>
										</dl>
									</div>
								</div>
                                                                <div class="arm_message_period_section_form_manual_subscription arm_margin_top_10" >
                                                                <span><?php esc_html_e('Send Message Before', 'ARMember'); ?></span>
                                    <div class="arm_message_periodunit_type arm_margin_left_10" >
                                        <input type='hidden' id="arm_message_period_unit_manual_subscription" class="arm_message_select_box_unit_manual_subscription" name="arm_message_period_unit_manual_subscription" value="1" />
                                        <dl class="arm_selectbox column_level_dd arm_width_100">
                                            <dt><span id="arm_message_period_unit_span_manual_subscription"></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_message_period_unit_manual_subscription">
                                                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                                                        <li data-label="<?php echo esc_attr($i); ?>" data-value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></li>
                                                    <?php } ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="arm_message_periodunit_type">
                                        <input type='hidden' id="arm_message_period_type_manual_subscription" class="arm_message_select_box_type_manual_subscription" name="arm_message_period_type_manual_subscription" value="day" />
                                        <dl class="arm_selectbox column_level_dd arm_width_120">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_message_period_type_manual_subscription">
                                                    <li data-label="<?php esc_html_e('Day(s)', 'ARMember'); ?>" data-value="day"><?php esc_html_e('Day(s)', 'ARMember'); ?></li>
                                                    <li data-label="<?php esc_html_e('Week(s)', 'ARMember'); ?>" data-value="week"><?php esc_html_e('Week(s)', 'ARMember'); ?></li>
                                                    <li data-label="<?php esc_html_e('Month(s)', 'ARMember'); ?>" data-value="month"><?php esc_html_e('Month(s)', 'ARMember'); ?></li>
                                                    <li data-label="<?php esc_html_e('Year(s)', 'ARMember'); ?>" data-value="year"><?php esc_html_e('Year(s)', 'ARMember'); ?></li>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                                                            <div class="arm_message_period_section_for_dripped_content arm_margin_top_10" >
                                                            <span><?php esc_html_e('Send Message Before', 'ARMember'); ?></span>
                                    <div class="arm_message_periodunit_type arm_margin_left_10" >
                                        <input type='hidden' id="arm_message_period_unit_dripped_content" class="arm_message_select_box_unit_dripped_content" name="arm_message_period_unit_dripped_content" value="1" />
                                        <dl class="arm_selectbox column_level_dd arm_width_100">
                                            <dt><span id="arm_message_period_unit_span_dripped_content"></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_message_period_unit_dripped_content">
                                                    <?php for ($i = 0; $i <= 5; $i++) { ?>
                                                        <li data-label="<?php echo esc_attr($i); ?>" data-value="<?php echo esc_attr($i); ?>"><?php echo esc_attr($i); ?></li>
                                                    <?php } ?>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                    <div class="arm_message_periodunit_type">
                                        <input type='hidden' id="arm_message_period_type_dripped_content" class="arm_message_select_box_type_dripped_content" name="arm_message_period_type_dripped_content" value="day" />
                                        <dl class="arm_selectbox column_level_dd arm_width_120">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd>
                                                <ul data-id="arm_message_period_type_dripped_content">
                                                    <li data-label="<?php esc_html_e('Day(s)', 'ARMember'); ?>" data-value="day"><?php esc_html_e('Day(s)', 'ARMember'); ?></li>
                                                    <li data-label="<?php esc_html_e('Week(s)', 'ARMember'); ?>" data-value="week"><?php esc_html_e('Week(s)', 'ARMember'); ?></li>
                                                    <li data-label="<?php esc_html_e('Month(s)', 'ARMember'); ?>" data-value="month"><?php esc_html_e('Month(s)', 'ARMember'); ?></li>
                                                    <li data-label="<?php esc_html_e('Year(s)', 'ARMember'); ?>" data-value="year"><?php esc_html_e('Year(s)', 'ARMember'); ?></li>
                                                </ul>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
							</td>
						</tr>
						<tr class="arm_membership_plan_selection_row">
							<th><?php esc_html_e('Select Membership Plan', 'ARMember');?></th>
							<td>
								<select id="arm_message_subscription" class="arm_chosen_selectbox arm_width_532" data-msg-required="<?php esc_html_e('Subscription Plan Required', 'ARMember');?>" name="arm_message_subscription[]" data-placeholder="<?php esc_html_e('Select Plan(s)..', 'ARMember');?>" multiple="multiple"  required>
								<?php $subs_data = $arm_subscription_plans->arm_get_all_subscription_plans('arm_subscription_plan_id, arm_subscription_plan_name');?>
								<?php if (!empty($subs_data)): $c_subs = (!empty($c_subs)) ? $c_subs : array('-1');?>
									<?php foreach ($subs_data as $sd): ?>
										<option class="arm_message_selectbox_op" value="<?php echo esc_attr($sd['arm_subscription_plan_id']);?>" <?php echo(in_array($sd['arm_subscription_plan_id'], $c_subs) ? 'selected="selected"' : "" );?>><?php echo stripslashes( esc_html($sd['arm_subscription_plan_name']) ); //phpcs:ignore?></option>
									<?php endforeach;?>
								<?php endif;?>
								</select>
								<div class="armclear" style="max-height: 1px;"></div>
								<span class="arm_info_text">(<?php esc_html_e('Leave blank for all plans.', 'ARMember')?>)</span>
							</td>
						</tr>
						<?php 
							$email_notifications_page_language_setting = apply_filters('arm_email_notifications_page_language_setting',array());
							$all_supported_language = apply_filters('arm_get_armember_multi_languages_list',array());
							$default_supported_language = apply_filters('arm_get_default_multi_languages',array());
							
							if(!empty($default_supported_language)){
								$all_supported_language = array_merge($all_supported_language,$default_supported_language);
							}
							if(empty($email_notifications_page_language_setting)){ ?>
								<tr class="">
									<th><?php esc_html_e('Subject', 'ARMember');?></th>
									<td>
										<input id="arm_message_subject" type="text" data-msg-required="<?php esc_html_e('Message Subject Required', 'ARMember');?>" name="arm_message_subject" value="" >
									</td>
								</tr>
								<tr class="">
									<th><?php esc_html_e('Message', 'ARMember');?></th>
									<td>
										<div class="arm_email_content_area_left">
											<?php 
											$arm_message_editor = array('textarea_name' => 'arm_message_content',
												'editor_class' => 'arm_message_content',
												'media_buttons' => false,
												'textarea_rows' => 5,
												'default_editor' => 'html',
												'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>'
											);
											wp_editor('', 'arm_message_content', $arm_message_editor);
											?>
											<span id="arm_comm_wp_validate_msg" class="error" style="display:none;"><?php esc_html_e('Content Cannot Be Empty.', 'ARMember');?></span>
										</div>
										<div class="arm_email_content_area_right">
											<span class="arm_sec_head"><?php esc_html_e('Template Tags', 'ARMember');?></span>
											<div class="arm_constant_variables_wrapper arm_shortcode_wrapper" id="arm_shortcode_wrapper">
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Admin Email", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_ADMIN_EMAIL}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Admin Email", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Blogname", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_BLOGNAME}"><?php esc_html_e("Blog Name", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display BlogURL", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_BLOGURL}" ><?php esc_html_e("Blog URL", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Login URL", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_LOGIN_URL}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Login URL", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Username", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERNAME}" ><?php esc_html_e("Username", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User ID", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USER_ID}" ><?php esc_html_e("User ID", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip arm_communication_email_code_password_reset" title="<?php esc_html_e("Reset Password Link", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_RESET_PASSWORD_LINK}" ><?php esc_html_e("Reset Password Link", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Firstname", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERFIRSTNAME}" ><?php esc_html_e("First Name", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Lastname", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERLASTNAME}" ><?php esc_html_e("Last Name", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Nickname", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERNICENAME}" ><?php esc_html_e("Nickname", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Displayname", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERDISPLAYNAME}" ><?php esc_html_e("Display Name", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Email Address", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_EMAIL}" ><?php esc_html_e("User Email Address", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display NetworkName", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_NETWORKNAME}" ><?php esc_html_e("Network Name", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display NetworkURL", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_NETWORKURL}" ><?php esc_html_e("Network URL", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Membership Plan Name", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTIONNAME}" ><?php esc_html_e("Plan Name", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Plan Description", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code " data-code="{ARM_MESSAGE_SUBSCRIPTIONDESCRIPTION}" ><?php esc_html_e("Plan Description", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Plan Expire Date", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_EXPIRE}" ><?php esc_html_e("Plan Expire Date", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Subscription Next Due Date", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code arm_no_bank_notification" data-code="{ARM_MESSAGE_SUBSCRIPTION_NEXT_DUE}" ><?php esc_html_e("Plan Next Due Date", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Plan Amount", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_AMOUNT}" ><?php esc_html_e("Plan Amount", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip " title="<?php esc_html_e("Display Coupon Code", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_COUPON_CODE}" ><?php esc_html_e("Coupon Code", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Coupon Discount", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_COUPON_DISCOUNT}" ><?php esc_html_e("Coupon Discount", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Plan Trial Amount", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_TRIAL_AMOUNT}" ><?php esc_html_e("Trial Amount", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Tax Percentage", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_TAX_PERCENTAGE}" ><?php esc_html_e("Tax Percentage", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Tax Amount", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_TAX_AMOUNT}" ><?php esc_html_e("Tax Amount", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Final Payable Amount", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_PAYABLE_AMOUNT}" ><?php esc_html_e("Payable Amount", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Payment Currency", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_CURRENCY}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Currency', 'ARMember'); ?> </span>                    
												</div>
												<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Payment Type", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_TYPE}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Payment Type", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Payment Gateway", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_GATEWAY}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Payment Gateway", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Transaction ID", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_TRANSACTION_ID}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Transaction ID", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Payment Date", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_DATE}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Payment Date", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Profile Link", 'ARMember'); ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_PROFILE_LINK}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("User Profile Link", 'ARMember'); ?></span>
												</div>
												<div class="arm_shortcode_row armhelptip" title="<?php echo esc_attr__("To Display User's meta field value.", 'ARMember') . ' (' . esc_attr__("Where", 'ARMember') . ' `meta_key` ' . esc_attr__("is meta field name.", 'ARMember') . ')'; ?>">
													<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_USERMETA_meta_key}"><?php esc_html_e("User Meta Key", 'ARMember');?></span>
												</div>
												<?php
													$arm_other_custom_shortcode_arr = array();
													$arm_other_custom_shortcode_arr = apply_filters('arm_email_notification_shortcodes_outside', $arm_other_custom_shortcode_arr);
													if(count($arm_other_custom_shortcode_arr)>0){
														foreach ($arm_other_custom_shortcode_arr as $arm_other_custom_shortcode_key => $arm_other_custom_shortcode_value) {
															if(is_array($arm_other_custom_shortcode_value))
															{
																$arm_en_title_on_hover = isset($arm_other_custom_shortcode_value['title_on_hover']) ? $arm_other_custom_shortcode_value['title_on_hover'] : '';
																$arm_en_shortcode = isset($arm_other_custom_shortcode_value['shortcode']) ? $arm_other_custom_shortcode_value['shortcode'] : '';
																$arm_en_shortcode_label = isset($arm_other_custom_shortcode_value['shortcode_label']) ? $arm_other_custom_shortcode_value['shortcode_label'] : '';
																$arm_en_shortcode_class = isset($arm_other_custom_shortcode_value['shortcode_class']) ? ' '.$arm_other_custom_shortcode_value['shortcode_class'].' ' : '';

																echo '<div class="arm_shortcode_row armhelptip'.esc_attr($arm_en_shortcode_class).'" title="'.esc_attr($arm_en_title_on_hover).'">';
																	echo '<span class="arm_variable_code arm_communication_email_code" data-code="'.esc_attr($arm_en_shortcode).'">'.esc_html($arm_en_shortcode_label).'</span>';
																echo '</div>';
															}
														}
													}
												?>
											</div>
										</div>
									</td>
								</tr>
						<?php }else{  ?>
							<div class="form-table arm_table_label_on_top">	
								<div class="arm_general_settings_tab_wrapper arm_width_auto">
									<?php 
									
									if(in_array($local,$email_notifications_page_language_setting)){
										unset($email_notifications_page_language_setting[array_search($local,$email_notifications_page_language_setting)]);
									}
									array_unshift($email_notifications_page_language_setting,$local);
									
									foreach ($email_notifications_page_language_setting as $language){
										if(isset($all_supported_language[$language])){ ?>
											<a class="arm_general_settings_tab arm_general_settings_languages_tab" data-selected-value="<?php echo $language; ?>">&nbsp;<?php esc_html_e($all_supported_language[$language], 'ARMember'); ?>&nbsp;&nbsp;</a>
										<?php } ?>
									<?php } ?>
								<div class="armclear"></div>
								</div>
								
								<?php 
								foreach ($email_notifications_page_language_setting as $lan) {
								?>
								
									<tr class="arm_general_settings_multi_languages_tab arm_width_auto" data-multi-lang-value="<?php echo $lan; ?>">
										<div class="">
											<th><?php esc_html_e('Subject', 'ARMember');?></th>
											<td>
												<?php if($lan==$local){ ?>
													<input id="arm_message_subject" type="text" data-msg-required="<?php esc_html_e('Message Subject Required', 'ARMember');?>" name="arm_message_subject" value="" >
												<?php }else{ ?>
													<input id="arm_message_subject_translated_<?php echo $lan; ?>" type="text" data-msg-required="<?php esc_html_e('Message Subject Required', 'ARMember');?>" name="arm_message_subject_translated[<?php echo $lan; ?>]" value="" >
												<?php } ?>
											</td>
										</div>
										<div class="">
											<th><?php esc_html_e('Message', 'ARMember');?></th>
											<td>
												<div class="arm_email_content_area_left">
													<?php 
													if($lan==$local){
														$arm_message_editor = array('textarea_name' => 'arm_message_content',
															'editor_class' => 'arm_message_content',
															'media_buttons' => false,
															'textarea_rows' => 5,
															'default_editor' => 'html',
															'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>'
														);
														wp_editor('', 'arm_message_content', $arm_message_editor);
													}else{
														$arm_message_editor = array('textarea_name' => 'arm_message_content_translated['.$lan.']',
															'editor_class' => 'arm_message_content_translated_'.$lan,
															'media_buttons' => false,
															'textarea_rows' => 5,
															'default_editor' => 'html',
															'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>'
														);
														wp_editor('', 'arm_message_content_translated_'.$lan, $arm_message_editor);
														
													}
													?>
													<span id="arm_comm_wp_validate_msg" class="error" style="display:none;"><?php esc_html_e('Content Cannot Be Empty.', 'ARMember');?></span>
												</div>
												<div class="arm_email_content_area_right">
													<span class="arm_sec_head"><?php esc_html_e('Template Tags', 'ARMember');?></span>
													<div class="arm_constant_variables_wrapper arm_shortcode_wrapper" id="arm_shortcode_wrapper">
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Admin Email", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_ADMIN_EMAIL}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Admin Email", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Blogname", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_BLOGNAME}"><?php esc_html_e("Blog Name", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display BlogURL", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_BLOGURL}" ><?php esc_html_e("Blog URL", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Login URL", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_LOGIN_URL}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Login URL", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Username", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERNAME}" ><?php esc_html_e("Username", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User ID", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USER_ID}" ><?php esc_html_e("User ID", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip arm_communication_email_code_password_reset" title="<?php esc_html_e("Reset Password Link", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_RESET_PASSWORD_LINK}" ><?php esc_html_e("Reset Password Link", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Firstname", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERFIRSTNAME}" ><?php esc_html_e("First Name", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Lastname", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERLASTNAME}" ><?php esc_html_e("Last Name", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Nickname", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERNICENAME}" ><?php esc_html_e("Nickname", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Displayname", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_USERDISPLAYNAME}" ><?php esc_html_e("Display Name", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Email Address", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_EMAIL}" ><?php esc_html_e("User Email Address", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display NetworkName", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_NETWORKNAME}" ><?php esc_html_e("Network Name", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display NetworkURL", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_NETWORKURL}" ><?php esc_html_e("Network URL", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Membership Plan Name", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTIONNAME}" ><?php esc_html_e("Plan Name", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Plan Description", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code " data-code="{ARM_MESSAGE_SUBSCRIPTIONDESCRIPTION}" ><?php esc_html_e("Plan Description", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Plan Expire Date", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_EXPIRE}" ><?php esc_html_e("Plan Expire Date", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Subscription Next Due Date", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code arm_no_bank_notification" data-code="{ARM_MESSAGE_SUBSCRIPTION_NEXT_DUE}" ><?php esc_html_e("Plan Next Due Date", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Plan Amount", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_AMOUNT}" ><?php esc_html_e("Plan Amount", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip " title="<?php esc_html_e("Display Coupon Code", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_COUPON_CODE}" ><?php esc_html_e("Coupon Code", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Coupon Discount", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_COUPON_DISCOUNT}" ><?php esc_html_e("Coupon Discount", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Plan Trial Amount", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_TRIAL_AMOUNT}" ><?php esc_html_e("Trial Amount", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Tax Percentage", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_TAX_PERCENTAGE}" ><?php esc_html_e("Tax Percentage", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Tax Amount", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_TAX_AMOUNT}" ><?php esc_html_e("Tax Amount", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Final Payable Amount", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_PAYABLE_AMOUNT}" ><?php esc_html_e("Payable Amount", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Payment Currency", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_CURRENCY}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Currency', 'ARMember'); ?> </span>                    
														</div>
														<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Payment Type", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_TYPE}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Payment Type", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Payment Gateway", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_GATEWAY}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Payment Gateway", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Transaction ID", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_TRANSACTION_ID}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Transaction ID", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Payment Date", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_DATE}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Payment Date", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Profile Link", 'ARMember'); ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_PROFILE_LINK}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("User Profile Link", 'ARMember'); ?></span>
														</div>
														<div class="arm_shortcode_row armhelptip" title="<?php echo esc_attr__("To Display User's meta field value.", 'ARMember') . ' (' . esc_attr__("Where", 'ARMember') . ' `meta_key` ' . esc_attr__("is meta field name.", 'ARMember') . ')'; ?>">
															<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_USERMETA_meta_key}"><?php esc_html_e("User Meta Key", 'ARMember');?></span>
														</div>
														<?php
															$arm_other_custom_shortcode_arr = array();
															$arm_other_custom_shortcode_arr = apply_filters('arm_email_notification_shortcodes_outside', $arm_other_custom_shortcode_arr);
															if(count($arm_other_custom_shortcode_arr)>0){
																foreach ($arm_other_custom_shortcode_arr as $arm_other_custom_shortcode_key => $arm_other_custom_shortcode_value) {
																	if(is_array($arm_other_custom_shortcode_value))
																	{
																		$arm_en_title_on_hover = isset($arm_other_custom_shortcode_value['title_on_hover']) ? $arm_other_custom_shortcode_value['title_on_hover'] : '';
																		$arm_en_shortcode = isset($arm_other_custom_shortcode_value['shortcode']) ? $arm_other_custom_shortcode_value['shortcode'] : '';
																		$arm_en_shortcode_label = isset($arm_other_custom_shortcode_value['shortcode_label']) ? $arm_other_custom_shortcode_value['shortcode_label'] : '';
																		$arm_en_shortcode_class = isset($arm_other_custom_shortcode_value['shortcode_class']) ? ' '.$arm_other_custom_shortcode_value['shortcode_class'].' ' : '';

																		echo '<div class="arm_shortcode_row armhelptip'.esc_attr($arm_en_shortcode_class).'" title="'.esc_attr($arm_en_title_on_hover).'">';
																			echo '<span class="arm_variable_code arm_communication_email_code" data-code="'.esc_attr($arm_en_shortcode).'">'.esc_html($arm_en_shortcode_label).'</span>';
																		echo '</div>';
																	}
																}
															}
														?>
													</div>
												</div>
											</td>
										</div>
									</tr>
									
								<?php } ?>
								<script>
									jQuery(document).ready(function () {
										jQuery('.arm_common_setting_multi_language_wrapper .arm_general_settings_languages_tab:first').click();
									});
								</script>
							</div>
							<?php } ?>
                        <tr>
                        	<th></th>
                        	<td>
                        		<span class="arm-note-message --warning"><?php printf( esc_html__('NOTE : Please add %sbr%s to use line break in plain text.','ARMember'),'&lt;','&gt;'); //phpcs:ignore?></span>
							</td>
						</tr>
						<tr>
							<th></th>
							<td>
								<div class="arm_email_content_area_left">
									<div class="arm_send_one_copy_to_admin_div arm_float_left arm_width_280" >
										<?php esc_html_e('Send email to admin for this event', 'ARMember'); ?>
									</div>
									<div class="arm_send_one_copy_to_admin_right arm_float_left" style="margin-top: 3px; ">
										<div class="armswitch">
											<input type="checkbox" class="armswitch_input arm_email_send_to_admin" id="arm_email_send_to_admin" name="arm_email_send_to_admin">
											<label class="armswitch_label" for="arm_email_send_to_admin"></label>
											<span class="arm_status_loader_img"></span>
										</div>
									</div>
								</div>
							</td>
						</tr>
						<tr class="arm_seperate_email_content_for_admin_switch hidden_section">
							<th></th>
							<td>
								<div class="arm_email_content_area_left">
									<div class="arm_send_one_copy_to_admin_div arm_float_left arm_width_280" >
										<?php esc_html_e('Set different email content for admin', 'ARMember'); ?>
									</div>
									<div class="arm_send_one_copy_to_admin_right arm_float_left" style="margin-top: 3px;">
										<div class="armswitch">
											<input type="checkbox" class="armswitch_input arm_email_different_content_for_admin" id="arm_email_different_content_for_admin" name="arm_email_different_content_for_admin">
											<label class="armswitch_label" for="arm_email_different_content_for_admin"></label>
											<span class="arm_status_loader_img"></span>
										</div>
									</div>
								</div>
							</td>
						</tr>
						<tr class="arm_seperate_email_content_for_admin hidden_section" >
							<th><?php esc_html_e('Message For Admin', 'ARMember');?></th>
							<td>
								<div class="arm_email_content_area_left">
									<?php 
									$arm_admin_message_editor = array('textarea_name' => 'arm_admin_message_content',
										'editor_class' => 'arm_admin_message_content',
										'media_buttons' => false,
										'textarea_rows' => 5,
										'default_editor' => 'html',
										'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>'
									);
									wp_editor('', 'arm_admin_message_content', $arm_admin_message_editor);
									?>
									<span id="arm_comm_wp_validate_admin_msg" class="error" style="display:none;"><?php esc_html_e('Message for admin Cannot Be Empty.', 'ARMember');?></span>
								</div>
								<div class="arm_email_content_area_right">
									<span class="arm_sec_head"><?php esc_html_e('Template Tags', 'ARMember');?></span>
									<div class="arm_constant_variables_wrapper arm_shortcode_wrapper" id="arm_shortcode_wrapper">
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Admin Email", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_ADMIN_EMAIL}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Admin Email", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Blogname", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_BLOGNAME}"><?php esc_html_e("Blog Name", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display BlogURL", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_BLOGURL}" ><?php esc_html_e("Blog URL", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Login URL", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_LOGIN_URL}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Login URL", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Username", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_USERNAME}" ><?php esc_html_e("Username", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User ID", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_USER_ID}" ><?php esc_html_e("User ID", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip arm_communication_email_code_password_reset" title="<?php esc_html_e("Reset Password Link", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_RESET_PASSWORD_LINK}" ><?php esc_html_e("Reset Password Link", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Firstname", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_USERFIRSTNAME}" ><?php esc_html_e("First Name", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Lastname", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_USERLASTNAME}" ><?php esc_html_e("Last Name", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Nickname", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_USERNICENAME}" ><?php esc_html_e("Nickname", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Displayname", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_USERDISPLAYNAME}" ><?php esc_html_e("Display Name", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Email Address", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_EMAIL}" ><?php esc_html_e("User Email Address", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display NetworkName", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_NETWORKNAME}" ><?php esc_html_e("Network Name", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display NetworkURL", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_NETWORKURL}" ><?php esc_html_e("Network URL", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Subscription Name", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTIONNAME}" ><?php esc_html_e("Plan Name", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Subscription Expire Date", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_EXPIRE}" ><?php esc_html_e("Plan Expire Date", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Subscription Next Due Date", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_NEXT_DUE}" ><?php esc_html_e("Plan Next Due Date", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Subscription Amount", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_SUBSCRIPTION_AMOUNT}" ><?php esc_html_e("Plan Amount", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Coupon Code", 'ARMember'); ?>">
											<span class="arm_variable_code arm_communication_email_code" data-code="{ARM_MESSAGE_COUPON_CODE}" ><?php esc_html_e("Coupon Code", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Coupon Discount", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_COUPON_DISCOUNT}" ><?php esc_html_e("Coupon Discount", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Trial Amount", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_TRIAL_AMOUNT}" ><?php esc_html_e("Trial Amount", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Final Payable Amount", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_PAYABLE_AMOUNT}" ><?php esc_html_e("Payable Amount", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Payment Currency", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_CURRENCY}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Currency', 'ARMember'); ?> </span>                    
										</div>
										<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Payment Type", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_TYPE}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Payment Type", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip arm_no_bank_notification" title="<?php esc_html_e("Display Payment Gateway", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_GATEWAY}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Payment Gateway", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Transaction ID", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_TRANSACTION_ID}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Transaction ID", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display Payment Date", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_MESSAGE_PAYMENT_DATE}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("Payment Date", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php esc_html_e("Display User Profile Link", 'ARMember'); ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_PROFILE_LINK}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e("User Profile Link", 'ARMember'); ?></span>
										</div>
										<div class="arm_shortcode_row armhelptip" title="<?php echo esc_attr__("To Display User's meta field value.", 'ARMember') . ' (' . esc_attr__("Where", 'ARMember') . ' `meta_key` ' . esc_attr__("is meta field name.", 'ARMember') . ')'; ?>">
											<span class="arm_variable_code arm_admin_communication_email_code" data-code="{ARM_USERMETA_meta_key}"><?php esc_html_e("User Meta Key", 'ARMember');?></span>
										</div>

										<?php
											$arm_other_custom_shortcode_arr = array();
											$arm_other_custom_shortcode_arr = apply_filters('arm_admin_email_notification_shortcodes_outside', $arm_other_custom_shortcode_arr);
											if(count($arm_other_custom_shortcode_arr)>0)
											{
												foreach ($arm_other_custom_shortcode_arr as $arm_other_custom_shortcode_key => $arm_other_custom_shortcode_value) {
													if(is_array($arm_other_custom_shortcode_value))
													{
														$arm_en_title_on_hover = isset($arm_other_custom_shortcode_value['title_on_hover']) ? $arm_other_custom_shortcode_value['title_on_hover'] : '';
														$arm_en_shortcode = isset($arm_other_custom_shortcode_value['shortcode']) ? $arm_other_custom_shortcode_value['shortcode'] : '';
														$arm_en_shortcode_label = isset($arm_other_custom_shortcode_value['shortcode_label']) ? $arm_other_custom_shortcode_value['shortcode_label'] : '';
														$arm_en_shortcode_class = isset($arm_other_custom_shortcode_value['shortcode_class']) ? ' '.$arm_other_custom_shortcode_value['shortcode_class'].' ' : '';

														echo '<div class="arm_shortcode_row armhelptip'.esc_attr($arm_en_shortcode_class).'" title="'.esc_attr($arm_en_title_on_hover).'">';
															echo '<span class="arm_variable_code arm_admin_communication_email_code" data-code="'.esc_attr($arm_en_shortcode).'">'.esc_attr($arm_en_shortcode_label).'</span>';
														echo '</div>';
													}
												}
												
											}
										?>
									</div>        
								</div>
							</td>
						</tr>
						<tr class="arm_seperate_email_content_for_admin hidden_section">
                        	<th></th>
                        	<td>
                        		<span class="arm-note-message --warning"><?php printf( esc_html__('NOTE : Please add %sbr%s to use line break in plain text.','ARMember'),'&lt;','&gt;'); //phpcs:ignore?></span>
							</td>
						</tr>
						<?php 
						$arm_automated_field_html='';
						$arm_automated_field_html=apply_filters('arm_add_automated_email_template_field_html',$arm_automated_field_html);
						echo $arm_automated_field_html; //phpcs:ignore
						?>
						<input type="hidden" id="arm_message_status" name="arm_message_status" value="1"/>
					</table>
					<div class="armclear"></div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' //phpcs:ignore?>" id="arm_loader_img" class="arm_loader_img arm_submit_btn_loader" style="top: 15px;display: none;float: <?php echo (is_rtl()) ? 'right' : 'left';?>;" width="20" height="20" />
						<input type="hidden" id="arm_message_id_box" name="edit_id" value="<?php echo esc_attr($mid);?>" />
						<button class="arm_save_btn arm_button_manage_message" type="submit" data-type="add"><?php esc_html_e('Save', 'ARMember') ?></button>
						<button class="arm_cancel_btn add_new_message_close_btn" type="button"><?php esc_html_e('Cancel','ARMember');?></button>
					</div>
				</td>
			</tr>
		</table>
		<div class="armclear"></div>
	</form>
</div>
<div class="add_edit_message_wrapper_container"></div>
<div class="edit_email_template_wrapper arm_common_setting_multi_language_wrapper popup_wrapper" >
	<form method="post" id="arm_edit_email_temp_frm" class="arm_admin_form arm_responses_message_wrapper_frm" action="#" onsubmit="return false;">
		<input type='hidden' name="arm_template_id" id="arm_template_id" value="0"/>
		<table cellspacing="0">
			<tr class="popup_wrapper_inner">	
				<td class="edit_template_close_btn arm_popup_close_btn"></td>
				<td class="popup_header"><?php esc_html_e('Edit Email Template','ARMember');?></td>
				<td class="popup_content_text">
					<table class="arm_table_label_on_top">	
						<?php 
							$email_notifications_page_language_setting = apply_filters('arm_email_notifications_page_language_setting',array());
							$all_supported_language = apply_filters('arm_get_armember_multi_languages_list',array());
							$default_supported_language = apply_filters('arm_get_default_multi_languages',array());
						
							if(empty($all_supported_language)){
								$email_notifications_page_language_setting = $all_supported_language;
							}else{
								if(!empty($default_supported_language)){
									$all_supported_language = array_merge($all_supported_language,$default_supported_language);
								}
							}
							if(empty($email_notifications_page_language_setting)){ ?>
								<tr class="">
									<th><?php esc_html_e('Subject', 'ARMember'); ?></th>
									<td>
										<input class="arm_input_tab arm_width_510" type="text" name="arm_template_subject" id="arm_template_subject" value="" data-msg-required="<?php esc_attr_e('Email Subject Required.', 'ARMember');?>"/>
									</td>
								</tr>
								<tr class="form-field">
									<th><?php esc_html_e('Message', 'ARMember'); ?></th>
									<td>
										<div class="arm_email_content_area_left">
										<?php 
										$email_setting_editor = array(
											'textarea_name' => 'arm_template_content',
											'editor_class' => 'arm_message_content',
											'media_buttons' => false,
											'textarea_rows' => 5,
											'default_editor' => 'html',
											'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
										);
										wp_editor('', 'arm_template_content', $email_setting_editor);
										?>
											<span id="arm_responses_wp_validate_msg" class="error" style="display:none;"><?php esc_html_e('Content Cannot Be Empty.', 'ARMember');?></span>
										</div>
										<div class="arm_email_content_area_right">
											<span class="arm_sec_head"><?php esc_html_e('Template Tags', 'ARMember');?></span>
											<div class="arm_constant_variables_wrapper arm_shortcode_wrapper" id="arm_shortcode_wrapper">
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_ADMIN_EMAIL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Admin Email', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the admin email that users can contact you at. You can configure it under Mail settings.", 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_BLOGNAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Blog Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays blog name', 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_BLOG_URL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Blog URL', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays blog URL', 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_LOGIN_URL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Login URL', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the ARM login page', 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USERNAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Username', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the Username of user', 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USER_ID}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('User ID', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the User ID of user', 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_reset_password">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_RESET_PASSWORD_LINK}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Reset Password Link', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the Reset Password Link for user', 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_FIRST_NAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('First Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the user first name', 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_LAST_NAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Last Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the user last name', 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_NAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Display Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the user display name or public name", 'ARMember'); ?>"></i>
												</div>                                        
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_EMAIL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Email', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the E-mail address of user", 'ARMember'); ?>"></i>
												</div>                                        
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PROFILE_LINK}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('User Profile Link', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the User Profile address", 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_VALIDATE_URL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Validation URL', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("The account validation URL that user receives after signing up (If you enable e-mail validation feature)", 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USERMETA_meta_key}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('User Meta Key', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php echo esc_attr__("To Display User's meta field value.", 'ARMember').' ('.esc_attr__("Where", 'ARMember').' `meta_key` '.esc_attr__("is meta field name.", 'ARMember').')';?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_plan_name">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Plan Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the plan name of user', 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_plan_desc">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_DESCRIPTION}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Plan Description', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the plan description of user', 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_plan_amount">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_AMOUNT}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Plan Amount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the plan amount of user", 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_plan_discount">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_COUPON_CODE}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Coupon Code', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the used coupon code by user", 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_plan_discount">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_DISCOUNT}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Coupon Discount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the plan discount of user", 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_trial_amount">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TRIAL_AMOUNT}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Trial Amount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the trial amount of plan", 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_tax_percentage">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TAX_PERCENTAGE}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Tax Percentage', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays tax percentage", 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_tax_amount">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TAX_AMOUNT}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Tax Amount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays tax amount", 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_payable_amount">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYABLE_AMOUNT}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Payable Amount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the Final Payable Amount of user", 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_payment_type">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_TYPE}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Payment Type', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the payment type of user", 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_payment_gateway">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_GATEWAY}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Payment Gateway', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the payment gateway of user", 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_transaction_id">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TRANSACTION_ID}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Transaction Id', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the payment transaction Id of user", 'ARMember'); ?>"></i>
												</div>
												<div class="arm_shortcode_row arm_email_code_payment_date">
													<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_DATE}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Payment Date', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the payment date of user", 'ARMember'); ?>"></i>
												</div>
												<?php do_action("arm_email_notification_template_shortcode"); ?>
											</div>
										</div>
									</td>
								</tr>
								<tr>
									<th></th>
									<td>	
										<span class="arm-note-message --warning"><?php printf( esc_html__('NOTE : Please add %sbr%s to use line break in plain text.','ARMember'),'&lt;','&gt;'); //phpcs:ignore?></span>
									</td>
								</tr>
						
           					<?php }else{ ?>
								<div class="form-table arm_table_label_on_top">	
								<div class="arm_general_settings_tab_wrapper arm_width_auto">
									<?php 
									
									if(in_array($local,$email_notifications_page_language_setting)){
										unset($email_notifications_page_language_setting[array_search($local,$email_notifications_page_language_setting)]);
									}
									array_unshift($email_notifications_page_language_setting,$local);
									foreach ($email_notifications_page_language_setting as $language){ ?>
										<a class="arm_general_settings_tab arm_general_settings_languages_tab_standard" data-selected-value="<?php echo $language; ?>">&nbsp;<?php echo $all_supported_language[$language] ?>&nbsp;&nbsp;</a>
									<?php } ?>
								<div class="armclear"></div>
								</div>
								<?php 
								foreach ($email_notifications_page_language_setting as $lan) { ?>
								<tr class="arm_general_settings_multi_languages_standard_tab arm_width_auto" data-multi-lang-value="<?php echo $lan; ?>">
									<div class="">
										<th><?php esc_html_e('Subject', 'ARMember'); ?></th>
										<td>
											<?php if($local==$lan){ ?>
												<input class="arm_input_tab arm_width_510" type="text" name="arm_template_subject" id="arm_template_subject" value="" data-msg-required="<?php esc_attr_e('Email Subject Required.', 'ARMember');?>"/>
											<?php }else{ ?>
												<input class="arm_input_tab arm_width_510" type="text" name="arm_template_subject_translated[<?php echo $lan; ?>]" id="arm_template_subject_translated_<?php echo $lan; ?>" value="" data-msg-required="<?php esc_attr_e('Email Subject Required.', 'ARMember');?>"/>
											<?php } ?>
										</td>
									</div>
									<div class="form-field">
										<th><?php esc_html_e('Message', 'ARMember'); ?></th>
										<td>
											<div class="arm_email_content_area_left">
											<?php 
											if($local==$lan){
												
												$email_setting_editor = array(
													'textarea_name' => 'arm_template_content',
													'editor_class' => 'arm_message_content',
													'media_buttons' => false,
													'textarea_rows' => 5,
													'default_editor' => 'html',
													'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
												);
												wp_editor('', 'arm_template_content', $email_setting_editor);
											}else{
												$email_setting_editor = array(
													'textarea_name' => 'arm_template_content_translated['.$lan.']',
													'editor_class' => 'arm_message_content_translated_'.$lan,
													'media_buttons' => false,
													'textarea_rows' => 5,
													'default_editor' => 'html',
													'editor_css' => '<style type="text/css"> body#tinymce{margin:0px !important;} </style>',
												);
												wp_editor('', 'arm_template_content_translated_'.$lan, $email_setting_editor);

											} ?>
												<span id="arm_responses_wp_validate_msg" class="error" style="display:none;"><?php esc_html_e('Content Cannot Be Empty.', 'ARMember');?></span>
											</div>
											<div class="arm_email_content_area_right">
												<span class="arm_sec_head"><?php esc_html_e('Template Tags', 'ARMember');?></span>
												<div class="arm_constant_variables_wrapper arm_shortcode_wrapper" id="arm_shortcode_wrapper">
													<div class="arm_shortcode_row">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_ADMIN_EMAIL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Admin Email', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the admin email that users can contact you at. You can configure it under Mail settings.", 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_BLOGNAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Blog Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays blog name', 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_BLOG_URL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Blog URL', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays blog URL', 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_LOGIN_URL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Login URL', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the ARM login page', 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USERNAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Username', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the Username of user', 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USER_ID}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('User ID', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the User ID of user', 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row arm_email_code_reset_password">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_RESET_PASSWORD_LINK}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Reset Password Link', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the Reset Password Link for user', 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_FIRST_NAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('First Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the user first name', 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_LAST_NAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Last Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the user last name', 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_NAME}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Display Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the user display name or public name", 'ARMember'); ?>"></i>
													</div>                                        
													<div class="arm_shortcode_row">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_EMAIL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Email', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the E-mail address of user", 'ARMember'); ?>"></i>
													</div>                                        
													<div class="arm_shortcode_row">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PROFILE_LINK}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('User Profile Link', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the User Profile address", 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_VALIDATE_URL}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Validation URL', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("The account validation URL that user receives after signing up (If you enable e-mail validation feature)", 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_USERMETA_meta_key}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('User Meta Key', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php echo esc_attr__("To Display User's meta field value.", 'ARMember').' ('.esc_attr__("Where", 'ARMember').' `meta_key` '.esc_attr__("is meta field name.", 'ARMember').')';?>"></i>
													</div>
													<div class="arm_shortcode_row arm_email_code_plan_name">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Plan Name', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the plan name of user', 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row arm_email_code_plan_desc">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_DESCRIPTION}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Plan Description', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e('Displays the plan description of user', 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row arm_email_code_plan_amount">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_AMOUNT}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Plan Amount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the plan amount of user", 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row arm_email_code_plan_discount">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_COUPON_CODE}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Coupon Code', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the used coupon code by user", 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row arm_email_code_plan_discount">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PLAN_DISCOUNT}" title="<?php esc_html_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Coupon Discount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the plan discount of user", 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row arm_email_code_trial_amount">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TRIAL_AMOUNT}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Trial Amount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the trial amount of plan", 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row arm_email_code_tax_percentage">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TAX_PERCENTAGE}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Tax Percentage', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays tax percentage", 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row arm_email_code_tax_amount">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TAX_AMOUNT}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Tax Amount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays tax amount", 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row arm_email_code_payable_amount">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYABLE_AMOUNT}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Payable Amount', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the Final Payable Amount of user", 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row arm_email_code_payment_type">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_TYPE}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Payment Type', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the payment type of user", 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row arm_email_code_payment_gateway">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_GATEWAY}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Payment Gateway', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the payment gateway of user", 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row arm_email_code_transaction_id">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_TRANSACTION_ID}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Transaction Id', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the payment transaction Id of user", 'ARMember'); ?>"></i>
													</div>
													<div class="arm_shortcode_row arm_email_code_payment_date">
														<span class="arm_variable_code arm_standard_email_code" data-code="{ARM_PAYMENT_DATE}" title="<?php esc_attr_e("Click to add shortcode in textarea", 'ARMember'); ?>"><?php esc_html_e('Payment Date', 'ARMember'); ?></span><i class="arm_email_helptip_icon armfa armfa-question-circle" title="<?php esc_attr_e("Displays the payment date of user", 'ARMember'); ?>"></i>
													</div>
													<?php do_action("arm_email_notification_template_shortcode"); ?>
												</div>
											</div>
										</td>
									</div>
									<div>
										<th></th>
										<td>	
											<span class="arm-note-message --warning"><?php printf( esc_html__('NOTE : Please add %sbr%s to use line break in plain text.','ARMember'),'&lt;','&gt;'); //phpcs:ignore?></span>
										</td>
									</div>
								</tr>
								<?php } ?>
								<script>
									jQuery(document).ready(function () {
										jQuery('.arm_common_setting_multi_language_wrapper .arm_general_settings_languages_tab_standard:first').click();
									});
								</script>
						<?php } 
						$arm_field_html='';
						$arm_field_html=apply_filters('arm_add_standard_email_template_field_html',$arm_field_html);
						echo $arm_field_html; //phpcs:ignore
						?>
					</table>
					<input type=hidden name="arm_template_status" id="arm_template_status" value=""/>
					<div class="armclear"></div>
				</td>
				<td class="popup_content_btn popup_footer">
					<div class="popup_content_btn_wrapper">
						<img src="<?php echo MEMBERSHIP_IMAGES_URL.'/arm_loader.gif' //phpcs:ignore?>" id="arm_loader_img_temp" class="arm_loader_img arm_submit_btn_loader" style="top: 15px;display: none;float: <?php echo (is_rtl()) ? 'right' : 'left';?>;" width="20" height="20" />
						<button class="arm_save_btn" id="arm_email_template_submit" type="submit"><?php esc_html_e('Save', 'ARMember');?></button>
						<button class="arm_cancel_btn edit_template_close_btn" type="button"><?php esc_html_e('Cancel','ARMember');?></button>
					</div>
				</td>
			</tr>
		</table>
		<div class="armclear"></div>
	</form>
</div>