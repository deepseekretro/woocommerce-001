<?php

global $wpdb, $arm_newdbversion, $ARMember;

if (version_compare($arm_newdbversion, '1.1', '<')) {
    /* for signup */
    $fp_id = $wpdb->get_results( $wpdb->prepare("SELECT `arm_form_settings` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_id`=%d",9) ); //phpcs:ignore --Reason $ARMember->tbl_arm_forms is a table name
    $arm_form_settings = maybe_unserialize($fp_id[0]->arm_form_settings);
    $arm_form_settings['style']['field_focus_color'] = '#b8b8b8';
    $update_settings = $wpdb->update($ARMember->tbl_arm_forms, array('arm_form_settings' => maybe_serialize($arm_form_settings)), array('arm_form_id' => '9'));

    /* for login */
    $fp_id = $wpdb->get_results( $wpdb->prepare("SELECT `arm_form_settings` FROM `" . $ARMember->tbl_arm_forms . "` WHERE `arm_form_id`=%d",10) ); //phpcs:ignore --Reason $ARMember->tbl_arm_forms is a table name
    $arm_form_settings = maybe_unserialize($fp_id[0]->arm_form_settings);
    $arm_form_settings['style']['field_focus_color'] = '#b8b8b8';
    $update_settings = $wpdb->update($ARMember->tbl_arm_forms, array('arm_form_settings' => maybe_serialize($arm_form_settings)), array('arm_form_id' => '10'));
}

if (version_compare($arm_newdbversion, '1.2', '<')) {
    /* Installing New Table for Login History */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = '';
    if ($wpdb->has_cap('collation')) {
        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
    }
    /* Installing Login History Table */
    $tbl_arm_login_history = $ARMember->tbl_arm_login_history;
    $query = "CREATE TABLE IF NOT EXISTS `{$tbl_arm_login_history}`(
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
    dbDelta($query);

    /* Update Block Settings (switch for login history) */
    $block_settings = get_option('arm_block_settings');
    $block_opts = maybe_unserialize($block_settings);
    if (!isset($block_opts['track_login_history']) || $block_opts['track_login_history'] == '' || empty($block_opts['track_login_history'])) {
        $block_opts['track_login_history'] = 1;
    }
    update_option('arm_block_settings', $block_opts);

    /* Update Directory Template & Profile Template for Display Member Badges & Joining Date */
    $membership_template_table = $ARMember->tbl_arm_member_templates;
    $get_membership_templates = $wpdb->get_results("SELECT arm_id,arm_options FROM `{$membership_template_table}`"); //phpcs:ignore --Reason $membership_template_table is a table name
    if (!empty($get_membership_templates) && is_array($get_membership_templates)) {
        foreach ($get_membership_templates as $key => $membership_template) {
            $template_opts = maybe_unserialize($membership_template->arm_options);
            if (!isset($template_opts['show_badges']) || @$template_opts['show_badges'] == '' || @empty($template_opts['show_badges'])) {
                $template_opts['show_badges'] = 1;
            }

            if (!isset($template_opts['show_joining']) || @$template_opts['show_joining'] == '' || @empty($template_opts['show_badges'])) {
                $template_opts['show_joining'] = 1;
            }

            $new_options = maybe_serialize($template_opts);
            $wpdb->query($wpdb->prepare("UPDATE `{$membership_template_table}` SET `arm_options` = %s", $new_options)); //phpcs:ignore --Reason $membership_template_table is a table name
        }
    }

    /* --- Access Rule Update --- */

    /* Get Meta from wp_postmeta for Post, Page, Navigation Menu Item and Custom Posts if it has protection */
    $post_meta_table = $wpdb->prefix . 'postmeta';
    $get_arm_protected_meta = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM `{$post_meta_table}` WHERE `meta_key` = %s AND `meta_value` = %s", 'arm_protection', '1')); //phpcs:ignore --Reason $post_meta_table is a table name
    if (!empty($get_arm_protected_meta) && is_array($get_arm_protected_meta)) {
        /* Loop through all enable metas */
        foreach ($get_arm_protected_meta as $key => $protected_post_meta) {
            $protected_post_id = $protected_post_meta->post_id;
            /* Retrieving Plan IDs of protected post types */
            $protected_metas = get_post_meta($protected_post_id, 'arm_access_plans', true);
            if (!empty($protected_metas)) {
                $all_protected_metas = explode(',', $protected_metas);
                if (is_array($all_protected_metas) && !empty($all_protected_metas)) {
                    foreach ($all_protected_metas as $key => $new_plan) {
                        /* Update existing plans with new plan */
                        add_post_meta($protected_post_id, 'arm_access_plan', $new_plan);
                    }
                }
            }
        }
    }

    /* Get Term meta from arm_termmeta table for categories and tags if it has protection */
    $arm_term_meta = $ARMember->tbl_arm_termmeta;
    $get_protected_terms = $wpdb->get_results($wpdb->prepare("SELECT arm_term_id FROM `{$arm_term_meta}` WHERE `meta_key` = %s AND `meta_value` = %s", 'arm_protection', '1')); //phpcs:ignore --Reason $arm_term_meta is a table name
    if (!empty($get_protected_terms) && is_array($get_protected_terms)) {
        /* Loop through all enables term metas */
        foreach ($get_protected_terms as $key => $protected_term_meta) {
            $protected_term_id = $protected_term_meta->arm_term_id;
            /* Getting Plan Ids of Protected terms */
            $protected_term_metas = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM `{$arm_term_meta}` WHERE `arm_term_id` = %d and `meta_key` = %s", $protected_term_id, 'arm_access_plans')); //phpcs:ignore --Reason $arm_term_meta is a table name
            if (!empty($protected_term_metas)) {
                $protected_term_metas = $protected_term_metas[0]->meta_value;
                $term_metas = explode(',', $protected_term_metas);
                foreach ($term_metas as $key => $term_meta) {
                    /* Update existing plans with new plan */
                    $wpdb->query($wpdb->prepare("INSERT INTO `{$arm_term_meta}` (arm_term_id,meta_key,meta_value) VALUES (%d,%s,%s)", $protected_term_id, 'arm_access_plan', $term_meta));//phpcs:ignore --Reason $arm_term_meta is a table name
                }
            }
        }
    }
    $get_unprotected_terms = $wpdb->get_results($wpdb->prepare("SELECT arm_term_id FROM `{$arm_term_meta}` WHERE `meta_key` = %s AND `meta_value` = %s", 'arm_protection', '0'));//phpcs:ignore --Reason $arm_term_meta is a table name
    if (!empty($get_unprotected_terms) && is_array($get_unprotected_terms)) {
        /* Loop through all enables term metas */
        foreach ($get_unprotected_terms as $key => $unprotected_term_meta) {
            $unprotected_term_id = $unprotected_term_meta->arm_term_id;
            $wpdb->query($wpdb->prepare("UPDATE `{$arm_term_meta}` SET meta_key = %s, meta_value = %s WHERE arm_term_id = %d AND meta_key = %s", 'arm_access_plan', '0', $unprotected_term_id, 'arm_access_plans'));//phpcs:ignore --Reason $arm_term_meta is a table name
        }
    }
}

if (version_compare($arm_newdbversion, '1.5', '<')) {
    global $wpdb, $wp, $ARMember;
    $bt_log_table = $ARMember->tbl_arm_bank_transfer_log;
    $pt_log_table = $ARMember->tbl_arm_payment_log;

    $wpdb->query("ALTER TABLE `{$bt_log_table}` ADD `arm_payment_mode` VARCHAR( 255 ) NULL AFTER `arm_transaction_id`");//phpcs:ignore --Reason $bt_log_table is a table name

    $wpdb->query("ALTER TABLE `{$pt_log_table}` ADD `arm_payment_mode` VARCHAR( 255 ) NULL AFTER `arm_payment_date`");//phpcs:ignore --Reason $pt_log_table is a table name

    $plan_table = $ARMember->tbl_arm_subscription_plans;

    $paid_plans = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$plan_table}` WHERE `arm_subscription_plan_type` != %s AND `arm_subscription_plan_is_delete` = %d ", "free", 0));//phpcs:ignore --Reason $plan_table is a table name

    if (!empty($paid_plans)) {
        foreach ($paid_plans as $key => $paid_plan) {
            $plan_options = maybe_unserialize($paid_plan->arm_subscription_plan_options);
            $plan_id = $paid_plan->arm_subscription_plan_id;
            $plan_access_type = $plan_options['access_type'];
            $plan_payment_type = $plan_options['payment_type'];
            $new_payment_mode = 'auto_debit_subscription';
            $plan_type = "paid_infinite";
            $plan_options['recurring']['payment_mode'] = 'manual_subscription';
            $plan_options['recurring']['manual_billing_start'] = "transaction_day";
            if ($plan_access_type == 'finite') {
                if ($plan_payment_type == 'subscription') {
                    $plan_type = "recurring";
                    $plan_options['recurring']['payment_mode'] = 'auto_debit_subscription';
                    $plan_options['recurring']['manual_billing_start'] = "transaction_day";
                } else {
                    $plan_type = "paid_finite";
                }
            } else {
                $plan_type = "paid_infinite";
            }

            $newPlanOptions = maybe_serialize($plan_options);
            $updateData = array(
                'arm_subscription_plan_type' => $plan_type,
                'arm_subscription_plan_options' => $newPlanOptions
            );
            $wpdb->update($plan_table, $updateData, array('arm_subscription_plan_id' => $plan_id));
        }
    }

    $user_meta = $wpdb->usermeta;
    $users = $wpdb->get_results($wpdb->prepare("SELECT user_id,meta_value FROM `$user_meta` WHERE `meta_key` = %s AND `meta_value` != %s", "arm_current_plan_detail", ''));//phpcs:ignore --Reason $user_meta is a table name
    if (!empty($users)) {
        foreach ($users as $key => $user) {
            $user_id = $user->user_id;

            $planDetail = maybe_unserialize($user->meta_value);

            $planOpt = maybe_unserialize($planDetail['arm_subscription_plan_options']);

            $plan_access_type = $planOpt['access_type'];

            $plan_payment_type = $planOpt['payment_type'];

            $new_payment_mode = 'auto_debit_subscription';

            $plan_type = "paid_infinite";
            $planOpt['recurring']['payment_mode'] = 'manual_subscription';
            $planOpt['recurring']['manual_billing_start'] = "transaction_day";

            if ($plan_access_type == 'finite') {
                if ($plan_payment_type == 'subscription') {
                    $plan_type = "recurring";
                    $planOpt['recurring']['payment_mode'] = 'auto_debit_subscription';
                    $planOpt['recurring']['manual_billing_start'] = "transaction_day";
                } else {
                    $plan_type = "paid_finite";
                }
            } else {
                $plan_type = "paid_infinite";
            }
            $newPlanOptions = maybe_serialize($planOpt);

            $planDetail['arm_subscription_plan_type'] = $plan_type;
            $planDetail['arm_subscription_plan_options'] = $newPlanOptions;

            update_user_meta($user_id, 'arm_current_plan_detail', $planDetail);
        }
    }

    update_option('arm_is_woocommerce_feature', 0);

    $default_rules = maybe_unserialize(get_option('arm_default_rules'));

    if (empty($default_rules) || !isset($default_rules['redirect'])) {
        $default_rules['redirect']['type'] = 'home';
        $default_rules['redirect']['page_id'] = 0;
    }

    $default_rules['redirect_logged_in_user']['type'] = 'home';
    $default_rules['redirect_logged_in_user']['page_id'] = 0;

    $default_rules['redirect_blocked_user']['type'] = 'home';
    $default_rules['redirect_blocked_user']['page_id'] = 0;

    $default_rules['redirect_pending_user']['type'] = 'home';
    $default_rules['redirect_pending_user']['page_id'] = 0;

    update_option('arm_default_rules', $default_rules);
}

if (version_compare($arm_newdbversion, '1.6', '<')) {
    global $wp, $wpdb, $ARMember;



    $bt_log_table = $ARMember->tbl_arm_bank_transfer_log;
    $payment_log = $ARMember->tbl_arm_payment_log;

    $wpdb->query("ALTER TABLE `{$bt_log_table}` ADD `arm_coupon_code` VARCHAR( 255 ) NULL AFTER `arm_currency`");//phpcs:ignore --Reason $bt_log_table is a table name
    $wpdb->query("ALTER TABLE `{$bt_log_table}` ADD `arm_coupon_discount` double NOT NULL AFTER `arm_coupon_code`");//phpcs:ignore --Reason $bt_log_table is a table name
    $wpdb->query("ALTER TABLE `{$bt_log_table}` ADD `arm_coupon_discount_type` VARCHAR( 50 ) NULL AFTER `arm_coupon_discount`");//phpcs:ignore --Reason $bt_log_table is a table name
    $wpdb->query("ALTER TABLE `{$bt_log_table}` ADD `arm_is_trial` INT( 1 ) NOT NULL  DEFAULT 0 AFTER `arm_status`");//phpcs:ignore --Reason $bt_log_table is a table name
    $wpdb->query("ALTER TABLE`{$payment_log}` ADD `arm_is_trial` INT( 1 ) NOT NULL DEFAULT 0 AFTER `arm_coupon_discount_type`");//phpcs:ignore --Reason $payment_log is a table name




    /* Add grace period email templates */
    $email_templates_table = $ARMember->tbl_arm_email_templates;
    $failed_payment_grace_email_template_args = array(
        'arm_template_name' => 'Grace Period For Failed Payment',
        'arm_template_subject' => 'Reminder for failed payment at {ARM_BLOGNAME}',
        'arm_template_slug' => 'grace_failed_payment',
        'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Unfortunately your recurring payment for {ARM_PLAN} at {ARM_BLOGNAME} has been failed for some reason.</p><br><p>Here are some payment details:</p><br><p>Paid With: {ARM_PAYMENT_GATEWAY}</p><br><p>Amount: {ARM_PLAN_AMOUNT}</p><br><p>Please contact to payment service provider for the same.</p><br><p><strong>Note: </strong>If you will not take appropriate action within {ARM_GRACE_PERIOD_DAYS} days, than relevant action will be performed by system,</p><br><p>If you have any further queries, Then feel free to contact us at {ARM_BLOGNAME}</p><br><p>Have a nice day!</p>'
    );
    $wpdb->insert($email_templates_table, $failed_payment_grace_email_template_args);
    $eot_grace_email_template_args = array(
        'arm_template_name' => 'Grace Period For End Of Term',
        'arm_template_subject' => 'Reminder for membership expiration at {ARM_BLOGNAME}',
        'arm_template_slug' => 'grace_eot',
        'arm_template_content' => '<p>Hi {ARM_FIRST_NAME} {ARM_LAST_NAME},</p><br><p>Your {ARM_PLAN} membership has just expired.</p><br><p>But still you can access our website without any problem,</p><br><p>If you want to renew/update your membership plan, than please click on following link:</p><br><p>{ARM_BLOG_URL}</p><br><p><strong>Note: </strong>If will not renew/change membership within {ARM_GRACE_PERIOD_DAYS} days, than relevant action will be performed by system.</p><br><p>Have a nice day!</p>'
    );
    $wpdb->insert($email_templates_table, $eot_grace_email_template_args);


    /* MIGRATE ALL USER METAS */
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
    if (!empty($amTotalUsers)) {
        foreach ($amTotalUsers as $usr) {
            $user_id = $usr->ID;
            $plan_detail = get_user_meta($user_id, 'arm_current_plan_detail', true);
            $plan_id = get_user_meta($user_id, 'arm_user_plan', true);
            if (!empty($plan_detail)) {
                $planObj = new ARM_Plan(0);
                $planObj->init((object) $plan_detail);
            } else {
                $planObj = new ARM_Plan($plan_id);
            }

            $planDetail = maybe_unserialize($plan_detail);

            $planID = $planDetail['arm_subscription_plan_id'];
            $planType = $planDetail['arm_subscription_plan_type'];

            $planOpt = maybe_unserialize($planDetail['arm_subscription_plan_options']);
            $plan_access_type = isset($planOpt['access_type']) ? $planOpt['access_type'] : ""; // finite
            $plan_payment_type = isset($planOpt['payment_type']) ? $planOpt['payment_type'] : "";  // subscription
            $payment_mode = get_user_meta($user_id, 'arm_selected_payment_mode', true);

            $subscription_plan_option = maybe_unserialize($plan_detail['arm_subscription_plan_options']);


            $recurring_time = isset($subscription_plan_option['recurring']['time']) ? $subscription_plan_option['recurring']['time'] : '';
            $plan_start_date = get_user_meta($user_id, 'arm_start_plan_' . $plan_id, true);

            /* in case of manual subscription next due date will be expire date of version 1.5 o_0 */
            $next_due_date = get_user_meta($user_id, 'arm_expire_plan_' . $planID, true);


            if ($planObj->has_trial_period()) {
                $all_date = $planObj->arm_trial_and_plan_start_date($plan_start_date, $payment_mode);

                $arm_trial_start_date = $all_date['arm_trial_start_date'];
                $arm_start_plan_date = $all_date['subscription_start_date'];
                $arm_expire_plan_trial = $all_date['arm_expire_plan_trial'];

                update_user_meta($user_id, 'arm_trial_start_date', $arm_trial_start_date);
                update_user_meta($user_id, 'arm_start_plan_' . $plan_id, $arm_start_plan_date);
                update_user_meta($user_id, 'arm_expire_plan_trial', $arm_expire_plan_trial);
            }

            /* if infinite recurring than delete user meta else update expire time */
            if ($planObj->is_recurring() && $recurring_time == 'infinite') {
                delete_user_meta($user_id, 'arm_expire_plan_' . $planID);
            } else if ($planObj->is_recurring() && $payment_mode == 'manual_subscription') {
                /* CHANGE EXPIRATION DATE OF PLAN in case of manual payment and subscription */

                if ($planObj->has_trial_period()) {
                    $plan_expire_date = $planObj->arm_plan_expire_time($arm_start_plan_date, $payment_mode);
                } else {
                    $plan_expire_date = $planObj->arm_plan_expire_time($plan_start_date, $payment_mode);
                }


                update_user_meta($user_id, 'arm_expire_plan_' . $plan_id, $plan_expire_date);
            }


            /* ADD NEXT DUE DATE OF PLAN in manual payment case */
            if ($planObj->is_recurring() && $payment_mode == 'manual_subscription') {
                update_user_meta($user_id, 'arm_selected_payment_mode', 'manual_subscription');
                update_user_meta($user_id, 'arm_next_due_payment_' . $plan_id, $next_due_date);
            } else if ($planObj->is_recurring()) {
                update_user_meta($user_id, 'arm_selected_payment_mode', 'auto_debit_subscription');
            }
        }
    }

    /* Get Meta from wp_postmeta for Post, Page, Navigation Menu Item and Custom Posts if it has protection */
    $post_meta_table = $wpdb->prefix . 'postmeta';
    $get_arm_protected_meta = array();
    $get_arm_protected_meta = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM `{$post_meta_table}` WHERE `meta_key` = %s AND `meta_value` = %s", 'arm_protection', '0'));//phpcs:ignore --Reason $post_meta_table is a table name
    if (!empty($get_arm_protected_meta) && is_array($get_arm_protected_meta)) {
        /* Loop through all enable metas */
        foreach ($get_arm_protected_meta as $key => $protected_post_meta) {
            $protected_post_id = $protected_post_meta->post_id;
            delete_post_meta($protected_post_id, 'arm_access_plan');
        }
    }
    $get_arm_protected_meta = array();
    $get_arm_protected_meta = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM `{$post_meta_table}` WHERE `meta_key` = %s AND `meta_value` = %s", 'arm_protection', '1'));//phpcs:ignore --Reason $post_meta_table is a table name
    if (!empty($get_arm_protected_meta) && is_array($get_arm_protected_meta)) {
        /* Loop through all enable metas */
        foreach ($get_arm_protected_meta as $key => $protected_post_meta) {
            $protected_post_id = $protected_post_meta->post_id;
            add_post_meta($protected_post_id, 'arm_access_plan', '0');
        }
    }
}

if (version_compare($arm_newdbversion, '1.7', '<')) {
    global $wpdb, $ARMember;
    $btTable = $ARMember->tbl_arm_bank_transfer_log;
    $ptTable = $ARMember->tbl_arm_payment_log;
    update_option('arm_new_version_installed', 1);
    $wpdb->query("ALTER TABLE `{$ptTable}` ADD `arm_display_log` INT( 1 ) NOT NULL DEFAULT '1'");//phpcs:ignore --Reason $ptTable is a table name
    $wpdb->query("ALTER TABLE `{$btTable}` ADD `arm_display_log` INT( 1 ) NOT NULL DEFAULT '1'");//phpcs:ignore --Reason $btTable is a table name

    $fmTable = $ARMember->tbl_arm_forms;
    $ffTable = $ARMember->tbl_arm_form_field;

    $forms = $wpdb->get_results($wpdb->prepare("SELECT arm_form_id FROM `" . $fmTable . "` WHERE arm_form_id > %d AND arm_is_template = %d", 100, 0));//phpcs:ignore --Reason $fmTable is a table name

    if (!empty($forms)) {
        foreach ($forms as $key => $form) {
            $form_id = $form->arm_form_id;
            $getFieldData = $wpdb->get_results($wpdb->prepare("SELECT arm_form_field_id,arm_form_field_option FROM `" . $ffTable . "` WHERE arm_form_field_form_id = %d", $form_id));//phpcs:ignore --Reason $ffTable is a table name
            if (!empty($getFieldData)) {
                foreach ($getFieldData as $k => $fieldData) {
                    $field_id = $fieldData->arm_form_field_id;
                    $field_options = maybe_unserialize($fieldData->arm_form_field_option);
                    if (array_key_exists('prefix', $field_options) && $field_options['prefix'] != '') {
                        if (substr($field_options['prefix'], 0, 3) != 'arm') {
                            $field_options['prefix'] = 'arm' . $field_options['prefix'];
                        }
                    }
                    if (array_key_exists('suffix', $field_options) && $field_options['suffix'] != '') {
                        if (substr($field_options['suffix'], 0, 3) != 'arm') {
                            $field_options['suffix'] = 'arm' . $field_options['suffix'];
                        }
                    }
                    $new_field_options = maybe_serialize($field_options);
                    $updateArray = array('arm_form_field_option' => $new_field_options);
                    $wpdb->update($ffTable, $updateArray, array('arm_form_field_id' => $field_id));
                }
            }
        }
    }

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
    if (!empty($amTotalUsers)) {
        foreach ($amTotalUsers as $usr) {
            $user_id = $usr->ID;
            $plan_detail = get_user_meta($user_id, 'arm_current_plan_detail', true);
            $plan_id = get_user_meta($user_id, 'arm_user_plan', true);

            if (!empty($plan_detail)) {
                $arm_user_selected_payment_mode = get_user_meta($user_id, 'arm_selected_payment_mode', true);
                $arm_user_payment_mode = get_user_meta('arm_user_payment_mode', true);

                if ($arm_user_payment_mode != '' && $arm_user_selected_payment_mode == '') {
                    update_user_meta($user_id, 'arm_selected_payment_mode', $arm_user_payment_mode);
                    delete_user_meta($user_id, 'arm_user_payment_mode');
                }
                delete_user_meta($user_id, 'arm_first_occurance_date_' . $plan_id);
                delete_user_meta($user_id, 'arm_user_selected_payment_mode');
                delete_user_meta($user_id, 'arm_user_from_setup');
                delete_user_meta($user_id, 'arm_total_payable_amount');
                delete_user_meta($user_id, 'arm_front_gateway_skin_type');
                delete_user_meta($user_id, 'arm_front_plan_skin_type');
                delete_user_meta($user_id, 'arm_user_old_plan');
                delete_user_meta($user_id, 'arm_user_old_plan_total_cycle');
                delete_user_meta($user_id, 'arm_user_done_payment');
                delete_user_meta($user_id, 'arm_payment_mode');


                if (!empty($plan_id) && $plan_id != 0) {
                    $plan = new ARM_Plan($plan_id);

                    if (!$plan->is_recurring()) {
                        delete_user_meta($user_id, 'arm_user_payment_mode');
                        delete_user_meta($user_id, 'arm_selected_payment_mode');
                        delete_user_meta($user_id, 'arm_next_due_payment_' . $plan_id);
                    }
                }
            }
        }
    }

    $wpdb->query( $wpdb->prepare("UPDATE `{$ptTable}` SET `arm_payment_mode` = '' WHERE `arm_payment_type`=%s",'one_time') ); //phpcs:ignore --Reason $ptTable is a table name
}

if (version_compare($arm_newdbversion, '1.8', '<')) {
    global $wpdb, $ARMember, $arm_members_class;
    $current_db_version = get_option('arm_version');
    if ($current_db_version == '1.7.1') {
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
        if (!empty($amTotalUsers)) {
            foreach ($amTotalUsers as $usr) {
                $user_id = $usr->ID;
                $plan_detail = get_user_meta($user_id, 'arm_current_plan_detail', true);
                $plan_id = get_user_meta($user_id, 'arm_user_plan', true);

                if (!empty($plan_detail)) {
                    $plan = new ARM_Plan($plan_id);

                    if ($plan->is_recurring()) {
                        $arm_user_payement_gateway = get_user_meta($user_id, 'arm_using_gateway_' . $plan_id, true);
                        if ($arm_user_payement_gateway == 'bank_transfer') {
                            $arm_user_selected_payment_mode = get_user_meta($user_id, 'arm_selected_payment_mode', true);
                            $arm_next_renewal_date = get_user_meta($user_id, 'arm_next_due_payment_' . $plan_id, true);
                            if ($arm_user_selected_payment_mode == '') {
                                update_user_meta($user_id, 'arm_selected_payment_mode', 'manual_subscription');
                                if ($arm_next_renewal_date == '') {
                                    $arm_next_renewal_date_new = $arm_members_class->arm_get_next_due_date($user_id, $plan_id);
                                    update_user_meta($user_id, 'arm_next_due_payment_' . $plan_id, $arm_next_renewal_date_new);
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    $email_settings_unser = get_option('arm_email_settings');
    $arm_email_settings = maybe_unserialize($email_settings_unser);
    $mailchimpOpt = (isset($arm_email_settings['arm_email_tools']['mailchimp'])) ? $arm_email_settings['arm_email_tools']['mailchimp'] : array();
    if (!empty($mailchimpOpt)) {
        $mailchimpOpt['enable_double_opt_in'] = 1;
        $arm_email_settings['arm_email_tools']['mailchimp'] = $mailchimpOpt;
        update_option('arm_email_settings', $arm_email_settings);
    }
}


if (version_compare($arm_newdbversion, '1.8.1', '<')) {
    global $wp, $wpdb, $ARMember, $arm_crons, $arm_global_settings;
    $arm_tbl_coupon = $ARMember->tbl_arm_coupons;
    $wpdb->query("ALTER TABLE `{$arm_tbl_coupon}` ADD `arm_coupon_label` VARCHAR(255) NULL AFTER `arm_coupon_code`"); //phpcs:ignore --Reason $arm_tbl_coupon is a table name
    $arm_tbl_email_template = $ARMember->tbl_arm_auto_message;
    $wpdb->query("ALTER TABLE `{$arm_tbl_email_template}` ADD `arm_message_send_copy_to_admin` INT(1) NOT NULL DEFAULT '0' AFTER `arm_message_status`, ADD `arm_message_send_diff_msg_to_admin` INT(1) NOT NULL DEFAULT '0' AFTER `arm_message_send_copy_to_admin`, ADD `arm_message_admin_message` LONGTEXT AFTER `arm_message_send_diff_msg_to_admin`");//phpcs:ignore --Reason $arm_tbl_email_template is a table name


    $email_templates_table = $ARMember->tbl_arm_email_templates;
    $arm_profile_updated_notification_to_admin = array(
        'arm_template_name' => 'Profile Updated Notification To Admin',
        'arm_template_subject' => 'Account of {ARM_USERNAME} has been updated at {ARM_BLOGNAME}',
        'arm_template_slug' => 'profile-updated-notification-admin',
        'arm_template_content' => '<p>Hello Administrator,</p><br><p>An account has been updated at {ARM_BLOGNAME}. Here are some basic details of that updated user.</p><br><p>Firstname: {ARM_FIRST_NAME}</p><br><p>Lastname: {ARM_LAST_NAME}</p><br><p>Username: {ARM_USERNAME}</p><br><p>Email: {ARM_EMAIL}</p><br><br><p>Thank You</p><br><p>Have a nice day!</p>',
    );
    $wpdb->insert($email_templates_table, $arm_profile_updated_notification_to_admin);
    $all_global_settings = $arm_global_settings->arm_get_all_global_settings();

    $all_general_settings = isset($all_global_settings['general_settings']) ? $all_global_settings['general_settings'] : array();
    if (!empty($all_general_settings)) {
        $all_global_settings['general_settings']['arm_email_schedular_time'] = 12;
        update_option('arm_global_settings', $all_global_settings);
    }

    $arm_all_crons = $arm_crons->arm_get_cron_hook_names();
    foreach ($arm_all_crons as $arm_cron_hook_name) {
        $arm_crons->arm_clear_cron($arm_cron_hook_name);
    }
}


if (version_compare($arm_newdbversion, '2.0', '<')) {
    update_option('arm_update_to_new_version',true);
    update_option('arm_new_version','2.0');
    $url = admin_url('admin.php?page=arm_update_page');
    if( $_REQUEST['page'] != 'arm_update_page' ){//phpcs:ignore
        wp_redirect($url);
        die();
    }
}

if (version_compare($arm_newdbversion, '2.0.1', '<')) {

    global $wpdb, $ARMember,$arm_global_settings;
    @set_time_limit(0);
    $arm_global_settings->arm_set_ini_for_importing_users();

    $arm_form_field_options_obj = $wpdb->get_results( $wpdb->prepare("SELECT `arm_form_field_option`  FROM " . $ARMember->tbl_arm_form_field . " WHERE `arm_form_field_status` = %d AND `arm_form_field_option` LIKE %s ",1,'%type";s:4:"date%') );//phpcs:ignore --Reason $ARMember->tbl_arm_form_field is a table name


    $arm_date_keys = array();

    if (!empty($arm_form_field_options_obj)) {
        foreach ($arm_form_field_options_obj as $arm_form_field_options) {
            $arm_form_field_option = maybe_unserialize($arm_form_field_options->arm_form_field_option);
            if ($arm_form_field_option['meta_key'] != '') {
                $arm_date_keys[] = $arm_form_field_option['meta_key'];
            }
        }
    }

    $arm_date_keys = array_unique($arm_date_keys);


    array_push($arm_date_keys, 'arm_form_id');
    $arm_date_keys = array_values($arm_date_keys);
    // $imploded_date_keys = implode("','", $arm_date_keys);
    $super_admin_placeholders = ' meta_key IN (';
    $super_admin_placeholders .= rtrim( str_repeat( '%s,', count( $arm_date_keys ) ), ',' );
    $super_admin_placeholders .= ')';
    array_unshift( $arm_date_keys, $super_admin_placeholders );
    $user_where = call_user_func_array(array( $wpdb, 'prepare' ), $arm_date_keys );

    $arm_user_query_obj = $wpdb->get_results("SELECT `user_id`, `meta_key`, `meta_value` FROM " . $wpdb->usermeta . " WHERE ".$user_where." ORDER BY `user_id` ASC"); //phpcs:ignore --Reason $wpdb->usermeta is a table name


    $arm_user_array = array();
    $arm_default_array = array();

    foreach ($arm_date_keys as $key) {
        $arm_default_array[$key] = '';
    }

    $default_array = array($arm_date_keys);
    if (!empty($arm_user_query_obj)) {
        foreach ($arm_user_query_obj as $arm_users) {

            $arm_user_id = $arm_users->user_id;
            if (!isset($arm_user_array[$arm_user_id])) {
                $arm_user_array[$arm_user_id] = array();
            }

            $arm_user_array[$arm_user_id] = shortcode_atts($arm_default_array, $arm_user_array[$arm_user_id]);


            if (in_array($arm_users->meta_key, $arm_date_keys)) {
                $arm_user_array[$arm_user_id][$arm_users->meta_key] = $arm_users->meta_value;
            } else {
                $arm_user_array[$arm_user_id]['arm_form_id'] = $arm_users->meta_value;
            }
        }
    }


    $form_id_date_format_array = array();

    if (!empty($arm_user_array) && is_array($arm_user_array)) {

        foreach ($arm_user_array as $arm_user_ID => $arm_meta_key_array) {
            $arm_user_form_id = $arm_meta_key_array['arm_form_id'];

            if (isset($form_id_date_format_array[$arm_user_form_id])) {
                $form_date_format = $form_id_date_format_array[$arm_user_form_id];
            } else {

                if ($arm_user_form_id == '') {
                    $form_date_format = 'd/m/Y';
                } else {
                    $arm_form_settings = $wpdb->get_var( $wpdb->prepare("SELECT `arm_form_settings`  FROM " . $ARMember->tbl_arm_forms . " WHERE `arm_form_id` = %d" , $arm_user_form_id)); //phpcs:ignore --Reason $ARMember->tbl_arm_forms is a table name

                    $arm_unserialized_settings = maybe_unserialize($arm_form_settings);

                    $form_date_format = $arm_unserialized_settings['date_format'];

                    if ($form_date_format == '') {
                        $form_date_format = 'd/m/Y';
                    }

                    $form_id_date_format_array[$arm_user_form_id] = $form_date_format;
                }
            }


            unset($arm_meta_key_array['arm_form_id']);

            if($form_date_format != '') {
                 

            foreach ($arm_meta_key_array as $date_metakey => $date_value) {


                    if ($date_value != '') {
                        try {
                            if (!$arm_date_key = DateTime::createFromFormat($form_date_format, $date_value)) {
                                $arm_date_key = arm_check_date_format($date_value);
                            }

                            $val = $arm_date_key->format('Y-m-d H:i:s');
                        } catch (Exception $e) {

                        $date1_ = str_replace('/', '-', $date_value);
                        $arm_date_key = new DateTime($date1_);


                            $val = $arm_date_key->format('Y-m-d H:i:s');
                        }
                        update_user_meta($arm_user_ID, $date_metakey, $val);
                    }
                }
            }
        }
    }
}

if (version_compare($arm_newdbversion, '2.1', '<')) {
     global $arm_global_settings;

     $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
     $all_global_settings['general_settings']['enable_crop'] = 1;
     update_option('arm_global_settings', $all_global_settings);
     update_option('arm_is_invoice_tax_feature', 1);
}

if (version_compare($arm_newdbversion, '3.0', '<')) {
    global $wpdb, $wp, $ARMember;
    $arm_tbl_coupon = $ARMember->tbl_arm_coupons;
    $pt_log_table = $ARMember->tbl_arm_payment_log;
    $bt_log_table = $ARMember->tbl_arm_bank_transfer_log;
    $badges_achievement_table = $ARMember->tbl_arm_badges_achievements;

    /*require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = '';
    if ($wpdb->has_cap('collation')) {
        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
    }*/

    $wpdb->query("ALTER TABLE `{$arm_tbl_coupon}` ADD `arm_coupon_on_each_subscriptions` TINYINT(1) NULL DEFAULT '0' AFTER `arm_coupon_period_type`");//phpcs:ignore --Reason $arm_tbl_coupon is a table name

    $wpdb->query("ALTER TABLE `{$pt_log_table}` ADD `arm_coupon_on_each_subscriptions` TINYINT(1) NULL DEFAULT '0' AFTER `arm_coupon_discount_type`; ");//phpcs:ignore --Reason $pt_log_table is a table name

    $wpdb->query("ALTER TABLE `{$bt_log_table}` ADD `arm_coupon_on_each_subscriptions` TINYINT(1) NULL DEFAULT '0' AFTER `arm_coupon_discount_type`; ");//phpcs:ignore --Reason $bt_log_table is a table name

    $wpdb->query("ALTER TABLE `{$badges_achievement_table}` ADD `arm_badges_tooltip` VARCHAR(255) NULL DEFAULT NULL AFTER `arm_badges_achievement_type`;");//phpcs:ignore --Reason $badges_achievement_table is a table name

    //Add Capabilities to administrator users
    $capabilities_field_name = $wpdb->prefix.'capabilities';
    $qargs = array(
            'meta_query' => array(
                    array(
                            'key' => $capabilities_field_name,
                            'value' => 'arm_manage_general_settings',
                            'compare' => 'LIKE',
                        ),
                ),
        );
    $usersQuery = new WP_User_Query($qargs);
    $users = $usersQuery->get_results();
    if (count($users) > 0) {
        foreach ($users as $key => $user) {
            $userObj = new WP_User($user->ID);
            $userObj->add_cap('arm_manage_license');
        }
    }

    //$wpdb->query("ALTER TABLE `{$pt_log_table}` CHANGE `arm_transaction_payment_type` `arm_transaction_payment_type` VARCHAR(100) CHARACTER SET {$charset_collate} NULL DEFAULT NULL; ");
    $wpdb->query("ALTER TABLE `{$pt_log_table}` CHANGE `arm_transaction_payment_type` `arm_transaction_payment_type` VARCHAR(100) NULL DEFAULT NULL;");//phpcs:ignore --Reason $pt_log_table is a table name
}

if (version_compare($arm_newdbversion, '3.3', '<')) {
     global $arm_global_settings, $arm_access_rules, $wpdb;

     $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
     $all_global_settings['general_settings']['spam_protection'] = 1;
     update_option('arm_global_settings', $all_global_settings);

    //Add Capabilities to administrator users
    $capabilities_field_name = $wpdb->prefix.'capabilities';
    $qargs = array(
            'meta_query' => array(
                    array(
                            'key' => $capabilities_field_name,
                            'value' => 'arm_manage_general_settings',
                            'compare' => 'LIKE',
                        ),
                ),
        );

    $usersQuery = new WP_User_Query($qargs);
    $users = $usersQuery->get_results();
    if (count($users) > 0) {
        foreach ($users as $key => $user) {
            $userObj = new WP_User($user->ID);
            $userObj->add_cap('arm_manage_private_content');
        }
    }

    $default_rules = $arm_access_rules->arm_get_default_access_rules();
    if(!empty($default_rules))
    {
        foreach ($default_rules as $default_rules_key => $default_rules_value) {
            if(is_array($default_rules_value) && in_array('-2', $default_rules_value))
            {
                $default_rules[$default_rules_key] = array('-2');
            }
        }

        update_option('arm_default_rules', $default_rules);
    }
}
if (version_compare($arm_newdbversion, '3.3.2', '<')) {
    global $arm_member_forms;
    $old_preset_fields     = get_option("arm_preset_form_fields");
    $old_preset_fields     = maybe_unserialize($old_preset_fields);
    $old_preset_fields     = maybe_unserialize($old_preset_fields);

    $default_preset_fields = $arm_member_forms->arm_default_preset_user_fields();
    if (isset($default_preset_fields['country']['options']) && !empty($default_preset_fields['country']['options'])) {
        if(isset($old_preset_fields['default']['country']) && is_array($old_preset_fields['default']['country']))
        {
            $old_preset_fields['default']['country']['options'] = $default_preset_fields['country']['options'];
        }
        update_option("arm_preset_form_fields", $old_preset_fields);
    }
    
    
    /*Stripe update changes starts*/
    $payment_gateway_options = maybe_unserialize( get_option('arm_payment_gateway_settings') );

    $payment_method = 'popup';
    if( version_compare( $arm_newdbversion, '3.3', '<' ) ){
        $payment_method = 'fields';
    }

    $payment_gateway_options['stripe']['stripe_payment_method'] = $payment_method;
    $payment_gateway_options['stripe']['stripe_popup_title'] = get_bloginfo('name');
    $payment_gateway_options['stripe']['stripe_popup_button_lbl'] = 'Pay Now';
    $payment_gateway_options['stripe']['stripe_popup_icon'] = '';

    update_option( 'arm_payment_gateway_settings', $payment_gateway_options );
    /*Stripe update changes End*/
}
if (version_compare($arm_newdbversion, '3.4', '<')) {
    //Add Capabilities to administrator users
    global $ARMember, $wpdb;
    $cap_obj = $ARMember->arm_slugs;

    $capabilities_field_name = $wpdb->prefix.'capabilities';
    $qargs = array(
            'meta_query' => array(
                    array(
                            'key' => $capabilities_field_name,
                            'value' => 'arm_manage_members',
                            'compare' => 'LIKE',
                        ),
                ),
        );

    $usersQuery = new WP_User_Query($qargs);
    $users = $usersQuery->get_results();

    if (count($users) > 0) {
        foreach ($users as $key => $user) {
            $userObj = new WP_User($user->ID);
            // Add Capabilities for Reports Analitycs Content Page
            $analitycs_cap = isset($cap_obj->report_analytics) ? $cap_obj->report_analytics : 'arm_report_analytics';
            $userObj->add_cap($analitycs_cap);            
        }
    }
}
if (version_compare($arm_newdbversion, '3.5', '<')) {
    global $wpdb, $wp, $ARMember,$arm_member_forms, $arm_global_settings;
            
    $arm_pt_log_table = $ARMember->tbl_arm_payment_log;
    $bt_log_table = $ARMember->tbl_arm_bank_transfer_log;
    $arm_bank_table_log_flag=get_option('arm_bank_table_log_flag');

    $arm_old_plan_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_old_plan_id') );
    if(empty($arm_old_plan_row)){
        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_old_plan_id` bigint(20) NOT NULL DEFAULT '0' AFTER `arm_plan_id`");//phpcs:ignore --Reason $arm_pt_log_table is a table name
    }    

    $arm_payment_cycle_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_payment_cycle') );
    if(empty($arm_payment_cycle_row)){
        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_payment_cycle` INT(11) NOT NULL DEFAULT '0' AFTER `arm_payment_mode`");//phpcs:ignore --Reason $arm_pt_log_table is a table name
    }

    $arm_bank_name_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_bank_name') );
    if(empty($arm_bank_name_row)){
        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_bank_name` VARCHAR(255) DEFAULT NULL AFTER `arm_payment_cycle`");//phpcs:ignore --Reason $arm_pt_log_table is a table name
    }

    $arm_account_name_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_account_name') );
    if(empty($arm_account_name_row)){
        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_account_name` VARCHAR(255) DEFAULT NULL AFTER `arm_bank_name`");//phpcs:ignore --Reason $arm_pt_log_table is a table name
    }

    $arm_additional_info_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_additional_info') );
    if(empty($arm_additional_info_row)){
        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_additional_info` LONGTEXT AFTER `arm_account_name`");//phpcs:ignore --Reason $arm_pt_log_table is a table name
    }

    $arm_first_name_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_first_name') );
    if(empty($arm_first_name_row)){
        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_first_name` VARCHAR(255) DEFAULT NULL AFTER `arm_user_id`");//phpcs:ignore --Reason $arm_pt_log_table is a table name
    }

    $arm_last_name_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = 'arm_last_name'",DB_NAME,$arm_pt_log_table,'arm_last_name') );
    if(empty($arm_last_name_row)){
        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_last_name` VARCHAR(255) DEFAULT NULL AFTER `arm_first_name`");//phpcs:ignore --Reason $arm_pt_log_table is a table name
    }

    $arm_payment_transfer_mode_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = 'arm_payment_transfer_mode'",DB_NAME,$arm_pt_log_table,'arm_payment_transfer_mode') );
    if(empty($arm_payment_transfer_mode_row)) {
        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_payment_transfer_mode` VARCHAR( 255 ) NULL AFTER `arm_additional_info`");//phpcs:ignore --Reason $arm_pt_log_table is a table name
    }

    if(empty($arm_bank_table_log_flag)){
        
        update_option('arm_bank_table_log_flag','1');

        $btquery = "SELECT * FROM `" . $bt_log_table . "`";
        $bt_payment_log = $wpdb->get_results($btquery, ARRAY_A);//phpcs:ignore --Reason $btquery is a predefined query
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
    

    $all_global_settings = $arm_global_settings->global_settings;
    $arm_recaptcha_site_key = !empty($all_global_settings['arm_recaptcha_site_key']) ? $all_global_settings['arm_recaptcha_site_key'] : '';
    $arm_recaptcha_private_key = !empty($all_global_settings['arm_recaptcha_private_key']) ? $all_global_settings['arm_recaptcha_private_key'] : '';
    
    if(!empty($arm_recaptcha_site_key) || !empty($arm_recaptcha_private_key)){
        if(!get_option('arm_recaptcha_notice_flag')){
            update_option('arm_recaptcha_notice_flag','1');
        }
    }

}

if(version_compare($arm_newdbversion, '4.0', '<')) {
    
    global $wpdb, $wp, $ARMember;

    $arm_pt_log_table = $ARMember->tbl_arm_payment_log;
    $arm_entries_table = $ARMember->tbl_arm_entries;
    $arm_subscription_plans_table = $ARMember->tbl_arm_subscription_plans;
    $arm_activity_table = $ARMember->tbl_arm_activity;
    $arm_membership_setup_table = $ARMember->tbl_arm_membership_setup;

    $arm_add_payment_log_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_is_post_payment') );
    if(empty($arm_add_payment_log_col)){
        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_is_post_payment` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_is_trial`");//phpcS:ignore --Reason $arm_pt_log_table is a table name
    }
    
    $arm_add_payment_log_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_paid_post_id') );
    if(empty($arm_add_payment_log_col)){
        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_is_post_payment`");//phpcs:ignore --Reason $arm_pt_log_table is a table name
    }
    
    $arm_add_entries_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_entries_table,'arm_is_post_entry') );
    if(empty($arm_add_entries_col)){
        $wpdb->query("ALTER TABLE `{$arm_entries_table}` ADD `arm_is_post_entry` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_plan_id`");//phpcs:ignore --Reason $arm_entries_table is a table name
    }
    
    $arm_add_entries_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_entries_table,'arm_paid_post_id') );
    if(empty($arm_add_entries_col)){
        $wpdb->query("ALTER TABLE `{$arm_entries_table}` ADD `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_is_post_entry`");//phpcs:ignore --Reason $arm_entries_table is a table name
    }

    $arm_add_subscription_plans = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_subscription_plans_table,'arm_subscription_plan_post_id') );
    if(empty($arm_add_subscription_plans)){
        $wpdb->query("ALTER TABLE `{$arm_subscription_plans_table}` ADD `arm_subscription_plan_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_subscription_plan_role`");//phpcs:ignore --Reason $arm_subscription_plans_table is a table name
    }

    $arm_add_activity_post_id = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_activity_table,'arm_paid_post_id') );
    if(empty($arm_add_activity_post_id)){
        $wpdb->query("ALTER TABLE `{$arm_activity_table}` ADD `arm_paid_post_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_item_id`");//phpcs:ignore --Reason $arm_activity_table is a table name
    }

    $arm_add_setup_type = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_membership_setup_table,'arm_setup_type') );
    if(empty($arm_add_setup_type)){
        $wpdb->query("ALTER TABLE `{$arm_membership_setup_table}` ADD `arm_setup_type` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_setup_name`");//phpcs:ignore --Reason $arm_membership_setup_table is a table name
    }

    //Add Capabilities to administrator users
    $cap_obj = $ARMember->arm_slugs;
    $capabilities_field_name = $wpdb->prefix.'capabilities';
    $qargs = array(
                'meta_query' => array(
                        array(
                                'key' => $capabilities_field_name,
                                'value' => 'arm_manage_plans',
                                'compare' => 'LIKE',
                            ),
                    ),
            );

    $usersQuery = new WP_User_Query($qargs);
    $users = $usersQuery->get_results();

    if (count($users) > 0) {
        foreach ($users as $key => $user) {
            $userObj = new WP_User($user->ID);
            // Add Capabilities for Pay Per Post Content Page
            $pay_per_post_cap = isset($cap_obj->pay_per_post) ? $cap_obj->pay_per_post : 'arm_manage_pay_per_post';
            $userObj->add_cap($pay_per_post_cap);            
        }
    }


    
}
if (version_compare($arm_newdbversion, '4.2', '<')) {
    //Add Capabilities to administrator users
    global $ARMember, $wpdb, $updated_with_badges;
    $update_db_tables = $ARMember->arm_update_badges();
	if($update_db_tables == 1)
	{
		$updated_with_badges = 'latest';
	}

}
if (version_compare($arm_newdbversion, '4.3', '<')) {
	delete_site_transient('notice-three');
}

if(version_compare($arm_newdbversion, '4.3.2', '<'))
{
    global $wpdb, $ARMember;
    $updt_payment_data_qury = $wpdb->prepare("UPDATE " . $ARMember->tbl_arm_payment_log . " a INNER JOIN " . $ARMember->tbl_arm_payment_log . " b ON a.arm_log_id = b.arm_log_id SET a.arm_token=b.arm_transaction_id WHERE a.arm_payment_gateway=%s and a.arm_payment_type=%s and a.arm_transaction_status=%s and a.arm_transaction_id like %s ", 'stripe', 'subscription', 'success', '%sub_%' ); //phpcs:ignore --Reason $ARMember->tbl_arm_payment_log is a table name

    $wpdb->query($updt_payment_data_qury ); //phpcs:ignore --Reason $updt_payment_data_qury is a query
}

if(version_compare($arm_newdbversion, '4.3', '>=') && version_compare($arm_newdbversion, '4.3.3', '<') )
{
    global $wpdb, $ARMember;
    $check_payment_date_after = "2021-04-28 00:00:00";

    $get_payment_data = $wpdb->get_results( $wpdb->prepare("SELECT `arm_log_id`,`arm_transaction_id`,`arm_token`,`arm_user_id`,`arm_plan_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE (`arm_payment_gateway`=%s || `arm_payment_gateway`=%s || `arm_payment_gateway`=%s || `arm_payment_gateway`=%s ) and arm_payment_type=%s and arm_created_date>=%s and arm_token NOT like %s and arm_token!=%s",'paypal','stripe','2checkout', 'authorize_net', 'subscription', $check_payment_date_after, '%-%', ''), ARRAY_A );  //phpcs:ignore --Reason $ARMember->tbl_arm_payment_log is a table name

    $arm_total_payment_data = count($get_payment_data);
    if($arm_total_payment_data>0)
    {
        foreach ($get_payment_data as $get_payment_data_arr ) 
        {
            $arm_payment_log_user_id = $get_payment_data_arr['arm_user_id'];
            $arm_payment_log_plan_id = $get_payment_data_arr['arm_plan_id'];
            if( !empty($arm_payment_log_user_id) && !empty($arm_payment_log_plan_id) )
            {
                $arm_update_plan_data_check = get_user_meta($arm_payment_log_user_id, 'arm_user_plan_' . $arm_payment_log_plan_id, true);
                if(!empty($arm_update_plan_data_check) && is_array($arm_update_plan_data_check) )
                {
                    if(!empty($arm_update_plan_data_check['arm_payment_mode']) && $arm_update_plan_data_check['arm_payment_mode']!="auto_debit_subscription")
                    {
                        update_user_meta($arm_payment_log_user_id, 'arm_user_plan_backup_'.$arm_payment_log_plan_id, $arm_update_plan_data_check);
                        //manual_subscription set for automatic payments
                        $arm_update_plan_data_check['arm_payment_mode'] = 'auto_debit_subscription'; 
                        update_user_meta($arm_payment_log_user_id, 'arm_user_plan_'.$arm_payment_log_plan_id, $arm_update_plan_data_check);
                    }
                }
            }
        }
    }
}

if(version_compare($arm_newdbversion, '4.4', '<'))
{
    global $wpdb, $ARMember;
    $updt_payment_data_qury = $wpdb->prepare("UPDATE " . $ARMember->tbl_arm_payment_log . " a INNER JOIN " . $ARMember->tbl_arm_payment_log . " b ON a.arm_log_id = b.arm_log_id SET a.arm_token=b.arm_transaction_id WHERE a.arm_payment_gateway=%s and a.arm_payment_type=%s and a.arm_transaction_id like %s and a.arm_token!=a.arm_transaction_id", 'stripe', 'subscription', '%sub_%' ); //phpcs:ignore --Reason $ARMember->tbl_arm_payment_log is a table name

    $wpdb->query(  $updt_payment_data_qury ); //phpcs:ignore --Reason $updt_payment_data_qury is a table name
}

if(version_compare($arm_newdbversion, '4.5', '<'))
{
	global $wpdb, $ARMember;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = '';
    if ($wpdb->has_cap('collation')) {
        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
    }
    
    $tbl_arm_debug_payment_log = $ARMember->tbl_arm_debug_payment_log;
    $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_debug_payment_log}`;
    CREATE TABLE IF NOT EXISTS `{$tbl_arm_debug_payment_log}`(
        `arm_payment_log_id` int(11) NOT NULL AUTO_INCREMENT,
        `arm_payment_log_ref_id` int(11) NOT NULL DEFAULT '0',
        `arm_payment_log_gateway` varchar(255) DEFAULT NULL,
        `arm_payment_log_event` varchar(255) DEFAULT NULL,
        `arm_payment_log_event_from` varchar(255) DEFAULT NULL,
        `arm_payment_log_status` TINYINT(1) DEFAULT '1',
        `arm_payment_log_raw_data` TEXT,
        `arm_payment_log_added_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY (`arm_payment_log_id`)
    ) {$charset_collate};";
    $arm_dbtbl_create[$tbl_arm_debug_payment_log] = dbDelta($sql_table);

    $tbl_arm_debug_general_log = $ARMember->tbl_arm_debug_general_log;
    $sql_table = "DROP TABLE IF EXISTS `{$tbl_arm_debug_general_log}`;
    CREATE TABLE IF NOT EXISTS `{$tbl_arm_debug_general_log}`(
        `arm_general_log_id` int(11) NOT NULL AUTO_INCREMENT,
        `arm_general_log_event` varchar(255) DEFAULT NULL,
        `arm_general_log_event_name` varchar(255) DEFAULT NULL,
        `arm_general_log_event_from` varchar(255) DEFAULT NULL,
        `arm_general_log_raw_data` TEXT,
        `arm_general_log_added_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY (`arm_general_log_id`)
    ) {$charset_collate};";
    $arm_dbtbl_create[$tbl_arm_debug_general_log] = dbDelta($sql_table);
}

if(version_compare($arm_newdbversion, '4.6', '<'))
{
    global $wpdb, $ARMember;
    $arm_tbl_coupon = $ARMember->tbl_arm_coupons;

    $arm_paid_post_type_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_tbl_coupon,'arm_coupon_paid_posts') );
    if( empty($arm_paid_post_type_row) ){
        $wpdb->query("ALTER TABLE `{$arm_tbl_coupon}` ADD `arm_coupon_paid_posts` TEXT DEFAULT NULL AFTER `arm_coupon_subscription`"); //phpcs:ignore --Reason $arm_tbl_coupon is a table name
    }
    
    //Add coupon type column to coupons table.
    $arm_coupon_type_row = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_tbl_coupon,'arm_coupon_type') );
    if( empty($arm_coupon_type_row) ){
        $wpdb->query("ALTER TABLE `{$arm_tbl_coupon}` ADD `arm_coupon_type` TINYINT(1) DEFAULT '0' AFTER `arm_coupon_expire_date`"); //phpcs:ignore --Reason $arm_tbl_coupon is a table name
    }
    
    $payment_gateway_options = maybe_unserialize( get_option('arm_payment_gateway_settings') );
    if( !empty($payment_gateway_options['stripe']['status']) )
    {
        update_option('arm-stripe-dismiss-admin-notice', true);
    }
}
/*if(version_compare($arm_newdbversion, '5.0', '<'))
{
    global $ARMember;
    
    //update form style as new design
    $arm_form_style_settings = $ARMember->arm_update_template_style_armember_5();
    
}*/
if(version_compare($arm_newdbversion, '5.0.2', '<'))
{    
    global $wpdb, $ARMember;

    $arm_subscription_plans_table = $ARMember->tbl_arm_subscription_plans;
    $arm_activity_table = $ARMember->tbl_arm_activity;
    $arm_pt_log_table = $ARMember->tbl_arm_payment_log;
    $arm_entries_table = $ARMember->tbl_arm_entries;

    //Add the arm_subscription_plan_gift_status for the Gift
    $arm_add_subscription_plan_gift_status_column = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_subscription_plans_table,'arm_subscription_plan_gift_status') );
    if(empty($arm_add_subscription_plan_gift_status_column)) {
        $wpdb->query("ALTER TABLE `{$arm_subscription_plans_table}` ADD `arm_subscription_plan_gift_status` INT(1) NOT NULL DEFAULT '0' AFTER `arm_subscription_plan_post_id`");//phpcs:ignore --Reason $arm_subscription_plans_table is atable name
    }    

    //Add the arm_gift_plan_id for the Gift 
    $arm_add_activity_column = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_activity_table,'arm_gift_plan_id') );
    if( empty($arm_add_activity_column) ) {
        $wpdb->query("ALTER TABLE `{$arm_activity_table}` ADD `arm_gift_plan_id` BIGINT(20) NOT NULL DEFAULT '0' AFTER `arm_paid_post_id`");//phpcs:ignore --Reason $arm_activity_table is atable name
    }

    // Add column arm_is_gift_payment for gift.
    $arm_add_payment_log_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_pt_log_table,'arm_is_gift_payment') );
    if(empty($arm_add_payment_log_col)) {
        $wpdb->query("ALTER TABLE `{$arm_pt_log_table}` ADD `arm_is_gift_payment` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_paid_post_id`");//phpcs:ignore --Reason $arm_pt_log_table is atable name
    }

    $arm_add_entries_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_entries_table,'arm_is_gift_entry') );
    if(empty($arm_add_entries_col)) {
        $wpdb->query("ALTER TABLE `{$arm_entries_table}` ADD `arm_is_gift_entry` TINYINT(1) NOT NULL DEFAULT '0' AFTER `arm_paid_post_id`");//phpcs:ignore --Reason $arm_entries_table is atable name
    }

}

if(version_compare($arm_newdbversion, '5.1', '<'))
{
    global $wpdb, $ARMember, $arm_members_directory;
    $data = $wpdb->get_results(  $wpdb->prepare("SELECT * FROM `" . $ARMember->tbl_arm_member_templates . "` WHERE arm_type = %s ",'profile'), ARRAY_A); //phpcs:ignore --Reason $ARMember->tbl_arm_member_templates is a table name

    if(!empty($data))
    {
        foreach ($data as $data_key => $data_value) {
            $_POST['arm_profile_template_name'] = isset($data_value['arm_title']) ? $data_value['arm_title'] : '';
            $_POST['arm_profile_template'] = isset($data_value['arm_slug']) ? $data_value['arm_slug'] : 'profiletemplate1';

            $plans = explode(',', $data_value['arm_subscription_plan']);
            $_POST['template_options']['plans'] = $plans;
            $_POST['arm_before_profile_fields_content'] = isset($data_value['arm_html_before_fields']) ? $data_value['arm_html_before_fields'] : '';
            $_POST['show_admin_users'] = isset($data_value['arm_enable_admin_profile']) ? intval($data_value['arm_enable_admin_profile']) : 0;
            $_POST['arm_after_profile_fields_content'] = isset($data_value['arm_html_after_fields']) ? $data_value['arm_html_after_fields'] : '';
            $_POST['arm_profile_template_id'] = isset($data_value['arm_ref_template']) ? $data_value['arm_ref_template'] : 1;
            $_POST['template_id'] = isset($data_value['arm_id'] ) ? intval($data_value['arm_id']) : 0;

            $options = maybe_unserialize($data_value['arm_options']);
            $options['plans'] = $plans;
            $_POST['template_options'] =$options;
            $_POST['arf_profile_action'] = 'edit_profile';
            $_POST['arm_new_profile_update'] = 'yes';

            $nonce_flag = 0;
            $arm_members_directory->arm_save_profile_template_func( $nonce_flag );
        } 
    }
}
if(version_compare($arm_newdbversion, '5.5', '<'))
{
    global $wpdb, $ARMember, $arm_payment_gateways;
    $ARMember->arn_add_default_template(6);
   
    //update form style as new design
    $ARMember->arm_update_template_style_armember_5();
    
    $social_settings = maybe_unserialize( get_option('arm_social_settings') );
    $social_options =isset($social_settings['options']) ? $social_settings['options'] : array();
    if(is_array($social_options) && isset($social_options["google"]) && !empty($social_options["google"]["status"]))
    {
    	update_option('arm-google-dismiss-admin-notice', true);
    }

    $arm_members_table = $ARMember->tbl_arm_members;

    $arm_add_arm_user_plan_ids_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_members_table,'arm_user_plan_ids') );
    if(empty($arm_add_arm_user_plan_ids_col)){
        $wpdb->query("ALTER TABLE `{$arm_members_table}` ADD `arm_user_plan_ids` TEXT NULL AFTER `arm_secondary_status`");//phpcs:ignore --Reason $arm_members_table is a table name
    }

    $arm_add_arm_user_suspended_plan_ids_col = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_members_table,'arm_user_suspended_plan_ids') );
    if(empty($arm_add_arm_user_suspended_plan_ids_col)){
        $wpdb->query("ALTER TABLE `{$arm_members_table}` ADD `arm_user_suspended_plan_ids` TEXT NULL AFTER `arm_user_plan_ids`"); //phpcs:ignore --Reason $arm_members_table is a table name
    }
    
    // Code to update stripe webhook verified flag as verified if stripe payment gateway is enabled.
    $payment_gateways = $arm_payment_gateways->arm_get_all_payment_gateways_for_setup();
    $arm_stripe_data = isset($payment_gateways['stripe']) ? $payment_gateways['stripe'] : array();
    if(!empty($arm_stripe_data)) {
        $arm_stripe_status = (isset($arm_stripe_data['status']) && $arm_stripe_data['status']) == 1 ? $arm_stripe_data['status'] : 0;
        if($arm_stripe_status) {
            $payment_gateways['stripe']['stripe_webhook_verified'] = 1;
            update_option('arm_payment_gateway_settings', $payment_gateways);
        }
    }
}
if(version_compare($arm_newdbversion, '5.6', '<'))
{
    update_option('arm_updates_cron_db_initialize', 1);
    update_option('arm_updates_cron_db_notice', 0);
    update_option('arm_updates_cron_db_total_users_updated', 0);
}

if (version_compare($arm_newdbversion, '5.9.1', '<')) {

    global $wpdb, $ARMember;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = '';
    if ($wpdb->has_cap('collation')) {
        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }
        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE $wpdb->collate";
        }
    }

    $tbl_arm_dripped_contents = $ARMember->tbl_arm_dripped_contents;
    $sql_table = "CREATE TABLE IF NOT EXISTS `{$tbl_arm_dripped_contents}`(
        `arm_dripped_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `arm_user_id` int(11) unsigned NOT NULL DEFAULT '0',
        `arm_rule_id` int(11) unsigned NOT NULL DEFAULT '0',
        `arm_added_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
        PRIMARY KEY (`arm_dripped_id`)
    ) {$charset_collate};";
    $arm_dbtbl_create[$tbl_arm_dripped_contents] = dbDelta($sql_table);
}
if (version_compare($arm_newdbversion, '6.0', '<')) {

    update_option('arm_is_wizard_complete',1);
    //Add Capabilities to administrator users
    global $ARMember, $wpdb;
    $cap_obj = $ARMember->arm_slugs;

    $capabilities_field_name = $wpdb->prefix.'capabilities';
    $qargs = array(
            'meta_query' => array(
                    array(
                            'key' => $capabilities_field_name,
                            'value' => 'arm_manage_members',
                            'compare' => 'LIKE',
                        ),
                ),
        );

    $usersQuery = new WP_User_Query($qargs);
    $users = $usersQuery->get_results();

    if (count($users) > 0) {
        foreach ($users as $key => $user) {
            $userObj = new WP_User($user->ID);
            // Add Capabilities for Reports Analitycs Content Page
            $subscription_cap = isset($cap_obj->arm_manage_subscriptions) ? $cap_obj->arm_manage_subscriptions : 'arm_manage_subscriptions';
            $userObj->add_cap($subscription_cap);            
        }
    }
}
if (version_compare($arm_newdbversion, '6.0.1', '<')) {
    global $ARMember, $wpdb;
    
    $armember_check_db_permission = $ARMember->armember_check_db_permission();
    if(!empty($armember_check_db_permission))
    {
        $arm_members_table = $ARMember->tbl_arm_members;
        $arm_tbl_arm_payment_log = $ARMember->tbl_arm_payment_log;
        $arm_tbl_arm_debug_payment_log = $ARMember->tbl_arm_debug_payment_log;
        $arm_tbl_arm_debug_general_log = $ARMember->tbl_arm_debug_general_log;
        
        //Add the arm-user-id INDEX for the Members table
        $arm_members_add_index_arm_user_id = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_members_table." where Key_name=%s ",'arm-user-id') ); //phpcs:ignore --Reason $arm_members_table is a table name
        if(empty($arm_members_add_index_arm_user_id))
        {
            $wpdb->query("ALTER TABLE `{$arm_members_table}` ADD INDEX `arm-user-id` (`arm_user_id`)"); //phpcs:ignore --Reason $arm_members_table is a table name
        }

        //Add the arm-user-id INDEX for the Payment table
        $arm_payment_log_add_index_arm_user_id = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_payment_log." where Key_name=%s ",'arm-user-id') );//phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
        if(empty($arm_payment_log_add_index_arm_user_id))
        {
            $wpdb->query("ALTER TABLE `{$arm_tbl_arm_payment_log}` ADD INDEX `arm-user-id` (`arm_user_id`)"); //phpcs:ignore --Reason $arm_tbl_arm_payment_log is a table name
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
        $arm_debug_payment_log_add_index_arm_status = $wpdb->get_results( $wpdb->prepare("SHOW INDEX FROM ".$arm_tbl_arm_debug_payment_log." where Key_name=%s ",'arm-debug-payment-log-status') );//phpcs:ignore --Reason $arm_tbl_arm_debug_payment_log is a table name
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
if (version_compare($arm_newdbversion, '6.4', '<')) {

    //Add Capabilities to administrator users
    global $ARMember, $wpdb;
    $cap_obj = $ARMember->arm_slugs;

    $capabilities_field_name = $wpdb->prefix.'capabilities';
    $qargs = array(
            'meta_query' => array(
                    array(
                            'key' => $capabilities_field_name,
                            'value' => 'arm_manage_members',
                            'compare' => 'LIKE',
                        ),
                ),
        );

    $usersQuery = new WP_User_Query($qargs);
    $users = $usersQuery->get_results();

    if (count($users) > 0) {
        foreach ($users as $key => $user) {
            $userObj = new WP_User($user->ID);
            // Add Capabilities for Manage Subscriptions Page
            $arm_growth_plugins_cap = isset($cap_obj->arm_growth_plugins) ? $cap_obj->arm_growth_plugins : 'arm_growth_plugins';
            $userObj->add_cap($arm_growth_plugins_cap);            
        }
    }
}
if(version_compare($arm_newdbversion,'6.5','<')){
    global $wpdb, $ARMember, $arm_social_feature;

    $arm_tbl_arm_forms = $ARMember->tbl_arm_forms;
    $arm_tbl_arm_form_field = $ARMember->tbl_arm_form_field;

    
    $current_user_pass_field_options = array(
        'id' => 'current_user_pass',
        'label' => esc_html__('Current Password', 'ARMember'),
        'placeholder' => esc_html__('Current Password', 'ARMember'),
        'type' => 'current_user_pass',
        'options' => array('strength_meter' => 0, 'strong_password' => 0, 'minlength' => 0, 'maxlength' => '', 'special' => 0, 'numeric' => 0, 'uppercase' => 0, 'lowercase' => 0),
        'meta_key' => 'current_user_pass',
        'required' => 1,
        'blank_message' => esc_html__('Current password can not be left blank.', 'ARMember'),
        'invalid_message' => esc_html__('Please enter valid current password.', 'ARMember'),
        'default_field' => 1,
        'ref_field_id' => 0,
        'enable_repeat_field' => 0,
    );
    
    $arm_fetch_change_password_forms = $wpdb->prepare( "SELECT arm_form_id FROM $arm_tbl_arm_forms WHERE arm_form_slug LIKE %s",'%change-password%' ); //phpcs:ignore --Reason $arm_tbl_arm_forms is a table name
    $arm_change_password_ids = $wpdb->get_results($arm_fetch_change_password_forms); //phpcs:ignore
    foreach($arm_change_password_ids as $arm_forms_data)
    {
        $arm_form_id = $arm_forms_data->arm_form_id;

        $arm_check_current_pass_field_exists = $wpdb->prepare("SELECT `arm_form_field_id` FROM $arm_tbl_arm_form_field WHERE arm_form_field_form_id=%d AND arm_form_field_slug=%s",$arm_form_id, 'current_user_pass');  //phpcs:ignore
        $arm_check_current_pass_field_exists_arr = $wpdb->get_row($arm_check_current_pass_field_exists);  //phpcs:ignore

        if(empty($arm_check_current_pass_field_exists_arr))
        {

            $get_form_fields_data = $wpdb->prepare("SELECT `arm_form_field_id`,`arm_form_field_slug` FROM $arm_tbl_arm_form_field WHERE arm_form_field_form_id=%d",$arm_form_id);  //phpcs:ignore
            $arm_change_password_forms_field_ids = $wpdb->get_results($get_form_fields_data);  //phpcs:ignore

            foreach($arm_change_password_forms_field_ids as $arm_change_password_forms_field_id)
            {
                $arm_form_field_id = $arm_change_password_forms_field_id->arm_form_field_id;
                $arm_form_field_slug = !empty($arm_change_password_forms_field_id->arm_form_field_slug) ?$arm_change_password_forms_field_id->arm_form_field_slug :'';
                $arm_form_field_order = 4;
                if(!empty($arm_form_field_slug) && $arm_form_field_slug =='user_pass')
                {
                    $arm_form_field_order = 2;
                }
                else if(!empty($arm_form_field_slug) && $arm_form_field_slug =='repeat_pass')
                {
                    $arm_form_field_order = 3;
                }

                if(!empty($arm_form_field_slug) && $arm_form_field_slug !='current_user_pass')
                {
                    $wpdb->update($arm_tbl_arm_form_field,array('arm_form_field_order'=>$arm_form_field_order),array('arm_form_field_id'=>$arm_form_field_id));
                }
                
            }
            $arm_change_password_forms_field_id_arr = array(
                'arm_form_field_form_id' => $arm_form_id,
                'arm_form_field_order' => 1,
                'arm_form_field_slug' => $current_user_pass_field_options['meta_key'],
                'arm_form_field_created_date' => current_time('mysql'),
                'arm_form_field_option' => maybe_serialize($current_user_pass_field_options)
            );

            $wpdb->insert($arm_tbl_arm_form_field,$arm_change_password_forms_field_id_arr);
        }
    }

    //Check LinkedIn Social Account is enabled
    $arm_social_settings_check = !empty($arm_social_feature->social_settings) ? $arm_social_feature->social_settings : array();
    $arm_social_options_check =isset($arm_social_settings_check['options']) ? $arm_social_settings_check['options'] : array();
    if( !empty($arm_social_options_check) && !empty($arm_social_options_check['linkedin']) && is_array($arm_social_options_check['linkedin']) && !empty($arm_social_options_check['linkedin']['status']) )
    {
        update_option('arm-linkedin-openid-admin-notice', true);
    }
    
}
if(version_compare($arm_newdbversion,'6.7','<'))
{
    global $wpdb, $wp, $ARMember; 
    
    $arm_activity_table = $ARMember->tbl_arm_activity;

    $arm_activity_plan_name = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_activity_table,'arm_activity_plan_name') ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	if ( empty( $arm_activity_plan_name ) ) {
        $wpdb->query("ALTER TABLE `{$arm_activity_table}` ADD `arm_activity_plan_name` VARCHAR( 255 ) NULL AFTER `arm_link`");//phpcs:ignore --Reason $bt_log_table is a table name
    }

    $arm_activity_plan_type = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_activity_table,'arm_activity_plan_type') ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

    if ( empty( $arm_activity_plan_name ) ) {
        $wpdb->query("ALTER TABLE `{$arm_activity_table}` ADD `arm_activity_plan_type` VARCHAR( 255 ) NULL AFTER `arm_activity_plan_name`");//phpcs:ignore --Reason $bt_log_table is a table name
    }

    $arm_activity_payment_gateway = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_activity_table,'arm_activity_payment_gateway') ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

    if(empty($arm_activity_payment_gateway))
    {
        $wpdb->query("ALTER TABLE `{$arm_activity_table}` ADD `arm_activity_payment_gateway` VARCHAR( 255 ) NULL AFTER `arm_activity_plan_type`");//phpcs:ignore --Reason $bt_log_table is a table name
    }

    $arm_activity_plan_amount = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_activity_table,'arm_activity_plan_amount') ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    
    if(empty($arm_activity_plan_amount))
    {
        $wpdb->query("ALTER TABLE `{$arm_activity_table}` ADD `arm_activity_plan_amount` DOUBLE NOT NULL DEFAULT '0' AFTER `arm_activity_payment_gateway`");//phpcs:ignore --Reason $bt_log_table is a table name
    }

    $arm_activity_plan_start_date = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_activity_table,'arm_activity_plan_start_date') ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    
    if(empty($arm_activity_plan_start_date))
    {
        $wpdb->query("ALTER TABLE `{$arm_activity_table}` ADD `arm_activity_plan_start_date` datetime NOT NULL DEFAULT '1970-01-01 00:00:00' AFTER `arm_activity_plan_amount`");//phpcs:ignore --Reason $bt_log_table is a table name
    }

    $arm_activity_plan_end_date = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_activity_table,'arm_activity_plan_end_date') ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

    if(empty($arm_activity_plan_end_date))
    {

        $wpdb->query("ALTER TABLE `{$arm_activity_table}` ADD `arm_activity_plan_end_date` datetime NULL DEFAULT '1970-01-01 00:00:00' AFTER `arm_activity_plan_start_date`");//phpcs:ignore --Reason $bt_log_table is a table name
    }

    $arm_activity_plan_next_cycle_date = $wpdb->get_results( $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=%s AND TABLE_NAME = %s AND column_name = %s",DB_NAME,$arm_activity_table,'arm_activity_plan_next_cycle_date') ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

    if(empty($arm_activity_plan_next_cycle_date))
    {
        $wpdb->query("ALTER TABLE `{$arm_activity_table}` ADD `arm_activity_plan_next_cycle_date` datetime NULL DEFAULT '1970-01-01 00:00:00' AFTER `arm_activity_plan_end_date`");//phpcs:ignore --Reason $bt_log_table is a table name
    }

    update_option('arm_updates_cron_db_initialize', 1);
    update_option('arm_updates_cron_db_activity_notice', 0);
    update_option('arm_updates_cron_db_total_activity_updated', 0);
}
if(version_compare($arm_newdbversion,'6.8','<'))
{
    global $arm_global_settings;
    /* Update Reset password Common Message Settings (If Reset password Common Message not updated) */
    $common_message_settings = $arm_global_settings->common_message;
    if ( !empty( $common_message_settings['arm_password_reset'] ) && 'Your Password has been reset. [LOGINLINK]Log in [/LOGINLINK]' == $common_message_settings['arm_password_reset'] ) {
        $common_message_settings['arm_password_reset'] = esc_html__('Password Reset Successfully!', 'ARMember') . ' [SUBTITLE]' . esc_html__('Your Password has been reset, Login now and get started.', 'ARMember') . ' [/SUBTITLE]';
        update_option('arm_common_message_settings', $common_message_settings);
    }
}

if(version_compare($arm_newdbversion,'6.9', '<'))
{
    global $arm_social_feature, $arm_email_settings;
    if( !empty( $arm_social_feature->isSocialFeature ) && current_user_can( 'arm_manage_feature_settings' ) )
    {
        $all_email_settings = $arm_email_settings->arm_get_all_email_settings();

        $emailTools = (!empty($all_email_settings['arm_email_tools'])) ? $all_email_settings['arm_email_tools'] : array();
        if( !empty( $emailTools ) )
        {
            $force_check = 1;
            $arm_addon_resp = $arm_social_feature->addons_page( $force_check );

            if ($arm_addon_resp != "") {
                $arm_addon_resp_exp = explode("|^^|", $arm_addon_resp);
                if ($arm_addon_resp_exp[0] == 1) 
                {
                    $ARMember->arm_session_start(true);
                    
                    $arm_addon_myplugarr = array();
                    $arm_addon_myplugarr = unserialize(base64_decode($arm_addon_resp_exp[1]));

                    $_SESSION['arm_member_addon'] = $arm_addon_myplugarr;

                    if( !empty( $_SESSION['arm_member_addon'] ) )
                    {
                        $wpnonce_check = 0;
                        if(!empty($emailTools['aweber']) && !empty($emailTools['aweber']['status']) && $emailTools['aweber']['status'] == 1){
                            $arm_social_feature->arm_plugin_install('armemberaweber/armemberaweber.php',$wpnonce_check);
                            $arm_social_feature->arm_activate_plugin('armemberaweber/armemberaweber.php',$wpnonce_check);
                        } 
                        if(!empty($emailTools['mailchimp']) && !empty($emailTools['mailchimp']['status']) && $emailTools['mailchimp']['status'] == 1){
                            $arm_social_feature->arm_plugin_install('armembermailchimp/armembermailchimp.php',$wpnonce_check);
                            $arm_social_feature->arm_activate_plugin('armembermailchimp/armembermailchimp.php',$wpnonce_check);
                        }
                        if(!empty($emailTools['constantcontact']) && !empty($emailTools['constantcontact']['status']) && $emailTools['constantcontact']['status'] == 1){
                            $arm_social_feature->arm_plugin_install('armemberconstantcontact/armemberconstantcontact.php',$wpnonce_check);
                            $arm_social_feature->arm_activate_plugin('armemberconstantcontact/armemberconstantcontact.php',$wpnonce_check);
                        }
                        if(!empty($emailTools['getresponse']) && !empty($emailTools['getresponse']['status']) && $emailTools['getresponse']['status'] == 1){
                            $arm_social_feature->arm_plugin_install('armembergetresponse/armembergetresponse.php',$wpnonce_check);
                            $arm_social_feature->arm_activate_plugin('armembergetresponse/armembergetresponse.php',$wpnonce_check);
                        }
                        if(!empty($emailTools['mailerlite']) && !empty($emailTools['mailerlite']['status']) && $emailTools['mailerlite']['status'] == 1){
                            $arm_social_feature->arm_plugin_install('armembermailerlite/armembermailerlite.php',$wpnonce_check);
                            $arm_social_feature->arm_activate_plugin('armembermailerlite/armembermailerlite.php',$wpnonce_check);
                        }
                        if(!empty($emailTools['sendinblue']) && !empty($emailTools['sendinblue']['status']) && $emailTools['sendinblue']['status'] == 1){
                            $arm_social_feature->arm_plugin_install('armemberbrevo/armemberbrevo.php',$wpnonce_check);
                            $arm_social_feature->arm_activate_plugin('armemberbrevo/armemberbrevo.php',$wpnonce_check);
                        }
                        if (is_plugin_active('myMail/myMail.php') || is_plugin_active('mailster/mailster.php')) { 
                            if(!empty($emailTools['mailster']) && isset($emailTools['mailster']['enable_double_opt_in']) ){
                                $arm_social_feature->arm_plugin_install('armembermailster/armembermailster.php',$wpnonce_check);
                                $arm_social_feature->arm_activate_plugin('armembermailster/armembermailster.php',$wpnonce_check);
                            }
                        }
                    }
                }
            }
        }
    }

}

$arm_update_lite_response = $ARMember->update_armember_lite();

if( false == $arm_update_lite_response ) {
    update_option( 'arm_show_lite_update_failed_notice', 1 );
}

$arm_newdbversion = '6.9.8';
update_option('arm_new_version_installed',1);
update_option('arm_version', $arm_newdbversion);

$arm_version_updated_date_key = 'arm_version_updated_date_'.$arm_newdbversion;
$arm_version_updated_date = current_time('mysql');
update_option($arm_version_updated_date_key, $arm_version_updated_date);
