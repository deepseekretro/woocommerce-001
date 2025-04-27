<?php

if (!class_exists('ARM_subscription_plans')) {

    class ARM_subscription_plans {

        function __construct() {  
            global $wpdb, $ARMember, $arm_global_settings, $arm_is_plan_limit_feature;
            $arm_is_plan_limit_feature = get_option('arm_is_plan_limit_feature', 0);
            if( $arm_is_plan_limit_feature == '1')
            {
                add_action('arm_display_field_add_membership_plan',array($this,'arm_display_plan_limit'));
            }
            add_action('wp_ajax_arm_delete_single_plan', array($this, 'arm_delete_single_plan'));
            add_action('wp_ajax_arm_cancel_membership', array($this, 'arm_ajax_stop_user_subscription'));
            add_action('wp_ajax_arm_display_plan_cycle', array($this, 'arm_ajax_display_plan_cycle'));
            add_action('arm_save_subscription_plans', array($this, 'arm_save_subscription_plans_func'));
            /* Hook for update user's last subscriptions */
            add_action('arm_before_update_user_subscription', array($this, 'arm_before_update_user_subscription_action'), 10, 2);

            add_action('wp_ajax_arm_membership_history_paging_action', array($this, 'arm_membership_history_paging_action'));
            /* Post Meta Box Functions */
            add_action('add_meta_boxes', array($this, 'arm_add_meta_boxes_func'));

            add_action('arm_apply_plan_to_member', array($this, 'arm_apply_plan_to_member_function'), 10, 3);

            add_shortcode('arm_update_subscription_card',array($this, 'arm_update_subscription_card'));

            add_filter('arm_plan_cycle_action_btns',array($this,'arm_plan_cycle_action_btns_func'),10,2);

            add_filter('arm_warning_notices_for_payment_gateways',array($this,'arm_warning_notices_for_payment_gateways_func'),10,1);
        }

        function arm_warning_notices_for_payment_gateways_func($arm_payment_gateway_notice){
            global $arm_global_settings;

            $payment_gateway_notices = "<span class='arm_invalid arm_margin_bottom_25 arm_font_size_16' >" . esc_html__("Please consider following limitations of various payment gateways while configuring automatic subscription plan.", 'ARMember') . "</span>";
            $payment_gateway_notices .= "<span class='arm_margin_bottom_10'><b>" . esc_html__('Paypal (if paypal payment gateway is enabled)', 'ARMember') . "</b><br/><ol class='arm_margin_left_30'><li>" . esc_html__('If your plan is Automatic Subscription and you have enabled Coupon Module then.', 'ARMember') . "<br/>" . esc_html__('- Due to paypal limitation it would be considered as a Trial Period of first installment.', 'ARMember') . "<br/>" . esc_html__('So, it would affect ( Your Recurring Time MINUS 1 ) number of occurance unless you have infinite duration.', 'ARMember') . " " . esc_html__('So, Please make sure you have set proper recurring time ( occurance ) in such case.', 'ARMember') . "</li><li>".esc_html__('Paypal supports maximum 90 days of trial duration for Auto Debit Subscription method.', 'ARMember')."</li></ol></span>";
            
            $payment_gateway_notices .= "<span class='arm_margin_bottom_10'><b>" . esc_html__('Stripe (if Stripe payment gateway is enabled)', 'ARMember') . "</b><br/><ol class='arm_margin_left_30'><li>" . esc_html__('Stripe payment gateway supports only "Days" in Trial Duration Unit.', 'ARMember') . "</li></ol></span>";
            $payment_gateway_notices .= "<span class='arm_margin_bottom_10'><b>" . esc_html__('Authorize.net (if Authorize.net payment gateway is enabled)', 'ARMember') . "</b><br/><ol class='arm_margin_left_30'><li>" . esc_html__('Authorize.net does not support billing cycle less than 7 Days. Also you can not set "Year" in billing cycle, as it is not supported in authorize.net.', 'ARMember') . "</li></ol></span>";
            $payment_gateway_notices .= "<span class='arm_margin_bottom_10'><b>" . esc_html__('2Checkout (if 2Checkout payment gateway is enabled)', 'ARMember') . "</b><br/><ol class='arm_margin_left_30'><li>" . esc_html__('2Checkout does not support "Day" in billing cycle and free trial.', 'ARMember') . "<li>" . esc_html__('2checkout supports only first occurence of billing cycle as trial duration. So if you want to give trial,then set same parameters in "Billing cycle" and "Trial Period Duration", otherwise plan expiration willnot work properly with 2checkout.', 'ARMember') . "</li><li>" . esc_html__('In Case of Automatic subscription, If total payable amount will be 0 (Zero), then 2checkout gateway will not work.', 'ARMember') . "</li></ol></span>";


            $payment_gateway_notices = apply_filters('arm_set_gateway_warning_in_plan_with_recurring', $payment_gateway_notices);
            $payment_gateway_notices_popup_arg = array(
                'id' => 'arm_payment_gateway_notices',
                'class' => 'payment_gateway_notices',
                'title' => 'Important Notes',
                'content' => $payment_gateway_notices,
                'button_id' => 'payment_gateway_notices_ok_btn',
                'button_onclick' => "payment_gateway_notices('true');",
            );
            $arm_payment_gateway_notice = $arm_global_settings->arm_get_bpopup_html_payment($payment_gateway_notices_popup_arg); //phpcs:ignore
            $arm_payment_gateway_notice .='<a class="arm_add_new_item_box arm_page_title_link arm_ref_info_links arm_pg_important_note" id="arm_payment_gateway_notices_link" href="#">'. esc_html__('Important note on payment integration with automatic subscription', 'ARMember').'</a>';
            return $arm_payment_gateway_notice;
        }

        function arm_plan_cycle_action_btns_func($arm_plan_cycle_action_btns,$total_inirecurring_cycle){

            $total_inirecurring_cycle = isset($total_inirecurring_cycle) ? esc_attr($total_inirecurring_cycle) : 1;
            $arm_plan_cycle_action_btns = '<div class="arm_plan_payment_cycle_action_buttons">
                <div class="arm_plan_cycle_plus_icon" id="arm_add_payment_cycle_recurring" data-field_index="'. esc_attr($total_inirecurring_cycle) .'"></div>
                <div class="arm_plan_cycle_minus_icon" id="arm_remove_recurring_payment_cycle" data_index="'. esc_attr($total_inirecurring_cycle) .'"></div>
                <div class="arm_plan_cycle_sortable_icon"></div>
            </div>';
            return $arm_plan_cycle_action_btns;
        }

        function arm_is_membership_purchase_limit_exceeded($plan_id)
        {
            global $wp,$wpdb,$ARMember,$arm_is_plan_limit_feature;
            $plan = new ARM_Plan($plan_id);
            $plan_options = $plan->options;
            $is_plan_limitted = 0;
            if( $arm_is_plan_limit_feature == '1' && !empty($plan_options['limit']) && $plan_options['limit'] > 0)
            {
                $sql = $wpdb->prepare("SELECT count(`arm_activity_id`) as total_rec FROM $ARMember->tbl_arm_activity WHERE (arm_action = %s OR arm_action = %s) AND arm_item_id=%d",'new_subscription','renew_subscription',$plan_id); //phpcs:ignore --Reason $ARMember->tbl_arm_activity is a table name

                $limit_response_result = $wpdb->get_row($sql); //phpcs:ignore --Reason $Asql is a prepared SQL

                if($limit_response_result->total_rec >= $plan_options['limit']){
                    $is_plan_limitted = 1;
                }
                
            }
            return $is_plan_limitted;
        }

        function arm_display_plan_limit($plan_options)
        {
            $arm_display_plan_limit = $this->arm_get_plan_limit_settings_content($plan_options);
            echo $arm_display_plan_limit; //phpcs:ignore
        }

        function arm_get_plan_limit_settings_content($plan_options){
            global $wp, $wpdb,$ARMember,$arm_payment_gateways;
            $arm_limit_plan_settings = '';
            $is_hidden = (empty($allow_plan_purchase_limit)) ? 'hidden_section':'';
            $plan_purchase_limit = (isset($plan_options["limit"])) ? $plan_options["limit"] : 0; 
            $arm_paid_post_plan_settings = '';
                $arm_paid_post_plan_settings .= '<div class="arm_solid_divider"></div>';
                $arm_paid_post_plan_settings .= '<div id="arm_plan_price_box_content" class="arm_plan_price_box">';
                    $arm_paid_post_plan_settings .= '<div class="page_sub_content">';
                        $arm_paid_post_plan_settings .= '<div class="page_sub_title">'. esc_html__('Membership Plan Purchase Limit Settings','ARMember') . '</div>';
                    
                    $arm_paid_post_plan_settings .= '<div class="arm_enable_limit_action">';
                    
                    $arm_paid_post_plan_settings .= '<table width="100%" class="form-table">';
                        $arm_paid_post_plan_settings .= '<tr class = "form-field form-required">';
                        $arm_paid_post_plan_settings .= '<th>';
                                $arm_paid_post_plan_settings .= '
                                <strong>'. esc_html__('Membership plan purchase limit', 'ARMember') .'</strong>';
                            $arm_paid_post_plan_settings .= '</th>';
                            $arm_paid_post_plan_settings .= '<td>';
                            
                                $arm_paid_post_plan_settings .= '<input type="text" name="arm_subscription_plan_options[limit]" value="'. esc_attr($plan_purchase_limit).'" size="50" class="arm_no_paste arm_width_235" data-msg-required="Label should not be blank." wfd-id="id28" aria-invalid="false" onkeypress="javascript:return ArmNumberValidation(event, this)">';
                                $arm_paid_post_plan_settings .= '<i class="arm_helptip_icon armfa armfa-question-circle" title="'. esc_html__('The membership Limit count will be considered on "New" and "Renew" purchase actions performed either from the admin panel or from the front-end. Admin can assign unlimited time membership plans. Also, when members pay for their next "Recurring (Renewal) Cycle" payment the membership Limit count will not be considered.', 'ARMember') .'"></i>';
                                $arm_paid_post_plan_settings .= '<span class="arm_subscription_limit_note">'.esc_html__('Leave blank or 0 for unlimited membership plans.','ARMember').'</span>';
                            $arm_paid_post_plan_settings .= '</td>';
                        $arm_paid_post_plan_settings .= '</tr>';
                    $arm_paid_post_plan_settings .= '</table>';
                    $arm_paid_post_plan_settings .= '</div>';
                $arm_paid_post_plan_settings .= '</div>';
            $arm_paid_post_plan_settings .= '</div>';
            return $arm_paid_post_plan_settings;    
        }

        function arm_ajax_display_plan_cycle() {
            global $arm_payment_gateways, $ARMember, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_plans'], '1',1);//phpcs:ignore --Reason:Verifying nonce
            $arm_currency = $arm_payment_gateways->arm_get_global_currency();
            $type = 'failed';
            $plan_name = '';
            $content = '';
            if( isset($_POST['plan_id']) && !empty($_POST['plan_id']) ) {//phpcs:ignore
                $count_cycle = '';
                $planObj = new ARM_Plan(intval($_POST['plan_id']));//phpcs:ignore
                $plan_name = esc_html(stripslashes($planObj->name));
                if($planObj->options['payment_cycles'] > 0) {
                    $type = 'success';
                    $typeArrayMany = array(
                        'D' => esc_html__("days", 'ARMember'),
                        'W' => esc_html__("weeks", 'ARMember'),
                        'M' => esc_html__("months", 'ARMember'),
                        'Y' => esc_html__("years", 'ARMember'),
                    );
                    $typeArray = array(
                        'D' => esc_html__("day", 'ARMember'),
                        'W' => esc_html__("week", 'ARMember'),
                        'M' => esc_html__("month", 'ARMember'),
                        'Y' => esc_html__("year", 'ARMember'),
                    );

                    $content .= '<table class="arm_user_edit_plan_table" cellspacing="1" style="text-align: center; width:calc(100% - 40px); border-left: 1px solid #eaeaea; margin: 20px; border-right: 1px solid #eaeaea;">';
                    $content .= '<tr class="arm_user_plan_row arm_user_plan_head odd">';
                    $content .= '<th class="arm_edit_plan_name">' . esc_html__('Label', 'ARMember') . '</th>';
                    $content .= '<th class="arm_edit_plan_type">' . esc_html__('Amount', 'ARMember') . '</th>';
                    $content .= '<th class="arm_edit_plan_start">' . esc_html__('Billing Cycle', 'ARMember') . '</th>';
                    $content .= '<th class="arm_edit_plan_expire">' . esc_html__('Recurring Time', 'ARMember') . '</th>';
                    $content .= '</tr>';

                    foreach ($planObj->options['payment_cycles'] as $arm_cycle) {
                        $count_cycle++;
                        $row_class = ($count_cycle % 2 == 0) ? 'odd' : 'even';
                        $arm_label = $arm_cycle['cycle_label'];
                        $arm_amount = $arm_payment_gateways->arm_amount_set_separator($arm_currency, $arm_cycle['cycle_amount']) . ' ' . $arm_currency;
                        $arm_billing_cycle = $arm_cycle['billing_cycle'];
                        $arm_billing_type = $arm_cycle['billing_type'];
                        $arm_recurring_time = $arm_cycle['recurring_time'];

                        $arm_billing_text = '';
                        if($arm_billing_cycle > 1) {
                            $arm_billing_text = $arm_billing_cycle . ' ' . $typeArrayMany[$arm_billing_type];
                        } else {
                            $arm_billing_text = $arm_billing_cycle . ' ' . $typeArray[$arm_billing_type];
                        }

                        $content .= '<tr class="arm_user_plan_row arm_plan_cycle ' . esc_attr($row_class) . '">';
                            $content .= '<td class="arm_edit_plan_name">' . $arm_label . '</td>';
                            $content .= '<td class="arm_edit_plan_type">' . $arm_amount . '</td>';
                            $content .= '<td class="arm_edit_plan_start">' . $arm_billing_text . '</td>';
                            $content .= '<td class="arm_edit_plan_expire">' . $arm_recurring_time . '</td>';
                        $content .= '</tr>';

                    }

                    $content .= '</table>';
                } else {
                    $content = '<center>'.esc_html__('Plan does not have any cycle.', 'ARMember').'</center>';
                }
            } else {
                $content = '<center>'.esc_html__('Plan does not have any cycle.', 'ARMember').'</center>';
            }
            echo $plan_name . '^|^' . $content; //phpcs:ignore
            die;
        }

        function arm_save_subscription_plans_func($posted_data = array()) {
            global $wp, $wpdb, $arm_slugs, $ARMember, $arm_global_settings, $arm_access_rules, $arm_stripe, $arm_capabilities_global, $ARMemberAllowedHTMLTagsArray,$arm_pay_per_post_feature;
            $redirect_to = admin_url('admin.php?page=' . $arm_slugs->manage_plans);

            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_plans'], '1',1); //phpcs:ignore --Reason:Verifying nonce

            if (isset($posted_data) && !empty($posted_data) && in_array($posted_data['action'], array('add', 'update'))) {
                $plan_name = (!empty($posted_data['plan_name'])) ? sanitize_text_field($posted_data['plan_name']) : esc_html__('Untitled Plan', 'ARMember');
                $plan_description = (!empty($posted_data['plan_description'])) ? wp_kses($posted_data['plan_description'],$ARMemberAllowedHTMLTagsArray) : ''; //phpcs:ignore
                $plan_status = (!empty($posted_data['plan_status']) && $posted_data['plan_status'] != 0) ? 1 : 0;
                $plan_role = (!empty($posted_data['plan_role'])) ? sanitize_text_field($posted_data['plan_role']) : get_option('default_role');
                $plan_type = (!empty($posted_data['arm_subscription_plan_type'])) ? sanitize_text_field($posted_data['arm_subscription_plan_type']) : 'free';
                
                $payment_type = $plan_amount = $stripe_plan = '';
                $plan_options = $plan_payment_gateways = array();
                if ($plan_type != 'free') {
                    $plan_options = (!empty($posted_data['arm_subscription_plan_options'])) ? $posted_data['arm_subscription_plan_options'] : array();

                    $plan_options['access_type'] = (!empty($plan_options['access_type'])) ? $plan_options['access_type'] : 'lifetime';
                    $plan_options['payment_type'] = (!empty($plan_options['payment_type'])) ? $plan_options['payment_type'] : 'one_time';

                    if ($plan_type == 'paid_finite') {
                        $plan_options['expiry_type'] = (isset($plan_options['expiry_type']) && !empty($plan_options["expiry_type"])) ? $plan_options["expiry_type"] : 'joined_date_expiry';
                        $expiry_date = !empty($plan_options["expiry_date"]) ? $plan_options["expiry_date"] : '';
                        $plan_options["expiry_date"] = ( $expiry_date != '' ) ? date('Y-m-d 23:59:59', strtotime($expiry_date)) : '';
                    } else {
                        unset($plan_options['expiry_type']);
                        unset($plan_options["expiry_date"]);
                        unset($plan_options["eopa"]);
                    }

                    if ($plan_type == 'paid_infinite') {
                        unset($plan_options['grace_period']);
                        unset($plan_options['eot']);
                        unset($plan_options['limit']);
                    }

                    if ($plan_options['payment_type'] == "one_time") {
                        $plan_options['trial'] = array();
                    }
                    $plan_amount = (!empty($posted_data['arm_subscription_plan_amount']) && $posted_data['arm_subscription_plan_amount'] != 0) ? $posted_data['arm_subscription_plan_amount'] : 0;

                    if ($plan_type == 'recurring') {
                        $manual_billing_start = (!empty($plan_options['recurring'])) ? $plan_options['recurring']['manual_billing_start'] : 'transaction_day';

                        if (isset($plan_options['trial']) && isset($plan_options['trial']['is_trial_period']) && $plan_options['trial']['is_trial_period'] == '1') {
                            $plan_options['trial'] = (!empty($plan_options['trial'])) ? $plan_options['trial'] : array();
                        }
                        $plan_options['payment_cycles'] = (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) ? array_values($plan_options['payment_cycles']) : array();

                        $plan_amount = (!empty($plan_options['payment_cycles'])) ? $plan_options['payment_cycles'][0]['cycle_amount'] : 0;
                        $first_payment_cycle = $plan_options['payment_cycles'][0];

                        $arm_billing_type = $first_payment_cycle['billing_type'];
                        $arm_recurring_time = $first_payment_cycle['recurring_time'];
                        $arm_billing_cycle = $first_payment_cycle['billing_cycle'];
                        $arm_months = 1;
                        $arm_days = 1;
                        $arm_years = 1;

                        if ($arm_billing_type == 'D') {
                            $arm_days = $arm_billing_cycle;
                        } else if ($arm_billing_type == 'M') {
                            $arm_months = $arm_billing_cycle;
                        } else {
                            $arm_years = $arm_billing_cycle;
                        }
                        $plan_options['recurring'] = array('days' => $arm_days,
                            'months' => $arm_months,
                            'years' => $arm_years,
                            'type' => $arm_billing_type,
                            'time' => $arm_recurring_time,
                            'manual_billing_start' => $manual_billing_start
                        );
                    } else {
                        unset($plan_options['payment_cycles']);
                        unset($plan_options['recurring']);
                        unset($plan_options['trial']);
                        unset($plan_options['cancel_action']);
                        unset($plan_options['cancel_plan_action']);
                        unset($plan_options['payment_failed_action']);
                    }
                }
                $plan_options['pricetext'] = isset($posted_data['arm_subscription_plan_options']['pricetext']) ? $posted_data['arm_subscription_plan_options']['pricetext'] : esc_html__('Free Membership', 'ARMember');
                $plan_options['limit'] = !empty($posted_data['arm_subscription_plan_options']['limit']) ? intval($posted_data['arm_subscription_plan_options']['limit']) : 0;
                if($arm_pay_per_post_feature->isPayPerPostFeature)
                {
                    $plan_options["arm_allow_paid_post_purchase"] = (!empty($posted_data['arm_subscription_plan_options']["arm_allow_paid_post_purchase"])) ? $posted_data['arm_subscription_plan_options']["arm_allow_paid_post_purchase"] : 0;
                    $plan_options["arm_assign_paid_posts"] = (!empty($posted_data['arm_subscription_plan_options']["arm_assign_paid_posts"])) ? $posted_data['arm_subscription_plan_options']["arm_assign_paid_posts"] : array();
                }
                $plan_options = apply_filters('arm_befor_save_field_membership_plan', $plan_options, $posted_data);
                $subscription_plans_data = array(
                    'arm_subscription_plan_name' => $plan_name,
                    'arm_subscription_plan_description' => $plan_description,
                    'arm_subscription_plan_status' => $plan_status,
                    'arm_subscription_plan_type' => $plan_type,
                    'arm_subscription_plan_options' => maybe_serialize($plan_options),
                    'arm_subscription_plan_amount' => $plan_amount,
                    'arm_subscription_plan_role' => $plan_role
                );
                if ($posted_data['action'] == 'add') {
                    $subscription_plans_data['arm_subscription_plan_created_date'] = current_time( 'mysql' );
                    //Insert Form Fields.

                    $wpdb->insert($ARMember->tbl_arm_subscription_plans, $subscription_plans_data);
                    $plan_id = $wpdb->insert_id;
                    //Action After Adding Plan
                    do_action('arm_saved_subscription_plan', $plan_id, $subscription_plans_data,$posted_data);
                    $inherit_plan_id = isset($posted_data['arm_inherit_plan_rules']) ? intval($posted_data['arm_inherit_plan_rules']) : 0;
                    if (!empty($plan_id) && $plan_id != 0 && !empty($inherit_plan_id) && $inherit_plan_id != 0) {
                        $arm_access_rules->arm_inherit_plan_rules($plan_id, $inherit_plan_id);
                    }
                    $ARMember->arm_set_message('success', esc_html__('Plan has been added successfully.', 'ARMember'));
                    
                    $redirect_to = $arm_global_settings->add_query_arg("action", "edit_plan", $redirect_to);
                    $redirect_to = $arm_global_settings->add_query_arg("id", $plan_id, $redirect_to);
                    wp_redirect($redirect_to);
                    exit;
                } elseif ($posted_data['action'] == 'update' && !empty($posted_data['id']) && $posted_data['id'] != 0) {
                    $update_plan_id = intval($posted_data['id']);
                    $field_update = $wpdb->update($ARMember->tbl_arm_subscription_plans, $subscription_plans_data, array('arm_subscription_plan_id' => $update_plan_id));
                    //Action After Updating Plan
                    do_action('arm_saved_subscription_plan', $update_plan_id, $subscription_plans_data,$posted_data);
                    $ARMember->arm_set_message('success', esc_html__('Plan has been updated successfully.', 'ARMember'));
                    $redirect_to = $arm_global_settings->add_query_arg("action", "edit_plan", $redirect_to);
                    $redirect_to = $arm_global_settings->add_query_arg("id", $update_plan_id, $redirect_to);
                    wp_redirect($redirect_to);
                    exit;
                }
            }
            return;
        }

        function arm_get_subscription_plan($plan_id = 0, $columns = 'all') {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $selectColumns = '*';
            if (!empty($columns)) {
                if ($columns != 'all' && $columns != '*') {
                    $selectColumns = $columns;
                }
            }
            if (is_numeric($plan_id) && $plan_id != 0) {
                $plan_data = $wpdb->get_row( $wpdb->prepare("SELECT {$selectColumns}, `arm_subscription_plan_id` FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_id`=%d AND `arm_subscription_plan_is_delete`=%s AND `arm_subscription_plan_gift_status`=%d LIMIT 1",$plan_id,0,0), ARRAY_A); //phpcs:ignore --Reason $ARMember->tbl_arm_subscription_plans is a table name
                if (!empty($plan_data)) {
                    if (isset($plan_data['arm_subscription_plan_name'])) {
                        $plan_data['arm_subscription_plan_name'] = stripslashes($plan_data['arm_subscription_plan_name']);
                    }
                    if (isset($plan_data['arm_subscription_plan_description'])) {
                        $plan_data['arm_subscription_plan_description'] = stripslashes($plan_data['arm_subscription_plan_description']);
                    }
                    if (isset($plan_data['arm_subscription_plan_options'])) {
                        $plan_data['arm_subscription_plan_options'] = maybe_unserialize($plan_data['arm_subscription_plan_options']);
                    }
                }
                return $plan_data;
            } else {
                return FALSE;
            }
        }

        function arm_get_plan_id_by_name($name = '') {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $plan_id = 0;
            if (!empty($name)) {
                $plan_id = $wpdb->get_var( $wpdb->prepare("SELECT `arm_subscription_plan_id` FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_name` LIKE %s AND `arm_subscription_plan_gift_status`=%d",'%' . $wpdb->esc_like($name) . '%',0) ); //phpcs:ignore --Reason $ARMember->tbl_arm_subscription_plans is a table name
                if (empty($plan_id)) {
                    $plan_id = 0;
                }
            }
            return $plan_id;
        }

        function arm_get_plan_role_by_id($plan_ids = array()) {
            global $wp, $wpdb, $ARMember;
            $plan_role = array();
            if (!empty($plan_ids)) {
                $where_placeholders = ' `arm_subscription_plan_id` IN (';
                $where_placeholders .= rtrim( str_repeat( '%d,', count( $plan_ids ) ), ',' );
                $where_placeholders .= ')';
                array_unshift( $plan_ids, $where_placeholders );
                $where = call_user_func_array(array( $wpdb, 'prepare' ), $plan_ids );
                $plan_role = $wpdb->get_results("SELECT `arm_subscription_plan_role`, `arm_subscription_plan_id` FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE ".$where, ARRAY_A); //phpcs:ignore --Reason $ARMember->tbl_arm_subscription_plans is a table name
            }
            return $plan_role;
        }

        function arm_get_plan_name_by_id_from_array($skipDeleted = false) {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $plan_name = "";
            $whereSql = '';
            if ($skipDeleted) {
                $whereSql = $wpdb->prepare(" WHERE `arm_subscription_plan_is_delete`=%d",0);
            }
            $plan_array = $wpdb->get_results("SELECT `arm_subscription_plan_id`, `arm_subscription_plan_name` FROM `" . $ARMember->tbl_arm_subscription_plans . "` {$whereSql}"); //phpcs:ignore --Reason $ARMember->tbl_arm_subscription_plans is a table name
            $plan_id_name_array = array();
            if (!empty($plan_array)) {

                foreach ($plan_array as $plan_arr) {
                    $plan_id_name_array[$plan_arr->arm_subscription_plan_id] = $plan_arr->arm_subscription_plan_name;
                }
            }

            return $plan_id_name_array;
        }

        function arm_get_plan_name_by_id($id = 0, $skipDeleted = false) {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $plan_name = "";
            if (!empty($id) && $id != 0) {

                if( isset( $GLOBALS['arm_get_subscription_plan_name_'.$id.'_'.$skipDeleted] ) )
                {
                    $plan_name = $GLOBALS['arm_get_subscription_plan_name_'.$id.'_'.$skipDeleted];
                }
                else {
                    $whereSql = $wpdb->prepare("WHERE `arm_subscription_plan_id` = %d",$id);
                    if ($skipDeleted) {
                        $whereSql .= $wpdb->prepare(" AND `arm_subscription_plan_is_delete`=%d",0);
                    }

                    $plan_name = $wpdb->get_var("SELECT `arm_subscription_plan_name` FROM `" . $ARMember->tbl_arm_subscription_plans . "` {$whereSql}"); //phpcs:ignore --Reason $ARMember->tbl_arm_subscription_plans is a table name
                    if (empty($plan_name)) {
                        $plan_name = "";
                    }
                    
                    $GLOBALS['arm_get_subscription_plan_name_'.$id.'_'.$skipDeleted] = $plan_name;
                }
                $plan_name = apply_filters("arm_modify_membership_plan_name_external",$plan_name,$id);
            }
            return stripslashes($plan_name);
        }

        function arm_get_comma_plan_names_by_ids($ids = array(), $exclude_paid_posts=1) {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $plan_names = "";
            if (!empty($ids)) {
                // from here call function arm_get_plan_name_by_id and query for each plan so, make it change during query monitor
                // $plan_ids = @implode(',', $ids);
                $paid_post_qur = " WHERE ";
                if($exclude_paid_posts==1)
                {
                    $paid_post_qur .= $wpdb->prepare(" `arm_subscription_plan_post_id`=%d ",0);
                }
                $admin_placeholders = ' AND `arm_subscription_plan_id` IN (';
                $admin_placeholders .= rtrim( str_repeat( '%s,', count( $ids ) ), ',' );
                $admin_placeholders .= ')';
                array_unshift( $ids, $admin_placeholders );
                $paid_post_qur .= call_user_func_array(array( $wpdb, 'prepare' ), $ids );   
                
                $implode_ids = implode( '_', $ids );
                if( isset( $GLOBALS['arm_get_comma_plan_by_ids_'.$implode_ids] ) && isset( $GLOBALS['arm_get_comma_plan_by_ids_'.$implode_ids] ) ) {
                    $plans = $GLOBALS['arm_get_comma_plan_by_ids_'.$implode_ids];
                } else {
                    $plans = $wpdb->get_col( "SELECT `arm_subscription_plan_name` FROM `" . $ARMember->tbl_arm_subscription_plans . "` ".$paid_post_qur." ORDER BY `arm_subscription_plan_id` DESC" );//phpcs:ignore --Reason $ARMember->tbl_arm_subscription_plans is a table name
                    $GLOBALS['arm_get_comma_plan_by_ids_'.$implode_ids] = $plans;
                }
                $plan_names = implode(', ', $plans);
            }
            return $plan_names;
        }

        /**
         * Get all subscritpion plans
         * @return array of plans, False if there is no plan(s).
         */
        function arm_get_plans_data($fields = 'all') {
            global $wp, $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
            $selectFields = '*';
            if (!empty($fields)) {
                if ($fields != 'all' && $fields != '*') {
                    $selectFields = $fields;
                }
            }

            $results = $wpdb->get_results( $wpdb->prepare("SELECT {$selectFields}, `arm_subscription_plan_id` FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_gift_status`=%d ORDER BY `arm_subscription_plan_id` DESC",0,0), ARRAY_A); //phpcs:ignore --Reason $ARMember->tbl_arm_subscription_plans is a table name
            if (!empty($results)) {
                $plans_data = array();
                foreach ($results as $sp) {
                    $plnID = $sp['arm_subscription_plan_id'];
                    if (isset($sp['arm_subscription_plan_name'])) {
                        $sp['arm_subscription_plan_name'] = stripslashes($sp['arm_subscription_plan_name']);
                    }
                    if (isset($sp['arm_subscription_plan_description'])) {
                        $sp['arm_subscription_plan_description'] = stripslashes($sp['arm_subscription_plan_description']);
                    }
                    if (isset($sp['arm_subscription_plan_options'])) {
                        $sp['arm_subscription_plan_options'] = maybe_unserialize($sp['arm_subscription_plan_options']);
                    }
                    $plans_data[$plnID] = $sp;
                }
                return $plans_data;
            } else {
                return FALSE;
            }
        }

        /**
         * Get all subscritpion plans
         * @return array of plans, False if there is no plan(s).
         */
        function arm_get_paid_post_data($fields = 'all') {
            global $wp, $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
            $selectFields = '*';
            if (!empty($fields)) {
                if ($fields != 'all' && $fields != '*') {
                    $selectFields = $fields;
                }
            }

            $results = $wpdb->get_results( $wpdb->prepare("SELECT {$selectFields}, `arm_subscription_plan_id` FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_post_id`!=%d AND `arm_subscription_plan_gift_status`=%d ORDER BY `arm_subscription_plan_id` DESC",0,0,0), ARRAY_A); //phpcs:ignore --Reason $ARMember->tbl_arm_subscription_plans is a table name
            if (!empty($results)) {
                $plans_data = array();
                foreach ($results as $sp) {
                    $plnID = $sp['arm_subscription_plan_id'];
                    if (isset($sp['arm_subscription_plan_name'])) {
                        $sp['arm_subscription_plan_name'] = stripslashes($sp['arm_subscription_plan_name']);
                    }
                    if (isset($sp['arm_subscription_plan_description'])) {
                        $sp['arm_subscription_plan_description'] = stripslashes($sp['arm_subscription_plan_description']);
                    }
                    if (isset($sp['arm_subscription_plan_options'])) {
                        $sp['arm_subscription_plan_options'] = maybe_unserialize($sp['arm_subscription_plan_options']);
                    }
                    $plans_data[$plnID] = $sp;
                }
                return $plans_data;
            } else {
                return FALSE;
            }
        }

        function arm_get_all_free_plans($fields = 'all', $object_type = ARRAY_A) {
            global $wp, $wpdb, $ARMember;
            $selectFields = '*';
            if (!empty($fields)) {
                if ($fields != 'all' && $fields != '*') {
                    $selectFields = $fields;
                }
            }
            $object_type = !empty($object_type) ? $object_type : ARRAY_A;

            $results = $wpdb->get_results( $wpdb->prepare("SELECT {$selectFields}, `arm_subscription_plan_id` FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_type` = %s AND `arm_subscription_plan_gift_status`=%d ORDER BY `arm_subscription_plan_id` DESC",0,'free',0), $object_type);//phpcs:ignore --Reason $ARMember->tbl_arm_member_templates is a table name
            if (!empty($results)) {
                $plans_data = array();
                if (!empty($results)) {
                    foreach ($results as $sp) {
                        if ($object_type == OBJECT || $object_type == OBJECT_K) {
                            $plnID = $sp->arm_subscription_plan_id;
                            if (isset($sp->arm_subscription_plan_name)) {
                                $sp->arm_subscription_plan_name = stripslashes($sp->arm_subscription_plan_name);
                            }
                            if (isset($sp->arm_subscription_plan_description)) {
                                $sp->arm_subscription_plan_description = stripslashes($sp->arm_subscription_plan_description);
                            }
                            if (isset($sp->arm_subscription_plan_options)) {
                                $sp->arm_subscription_plan_options = maybe_unserialize($sp->arm_subscription_plan_options);
                            }
                        } else {
                            $plnID = $sp['arm_subscription_plan_id'];
                            if (isset($sp['arm_subscription_plan_name'])) {
                                $sp['arm_subscription_plan_name'] = stripslashes($sp['arm_subscription_plan_name']);
                            }
                            if (isset($sp['arm_subscription_plan_description'])) {
                                $sp['arm_subscription_plan_description'] = stripslashes($sp['arm_subscription_plan_description']);
                            }
                            if (isset($sp['arm_subscription_plan_options'])) {
                                $sp['arm_subscription_plan_options'] = maybe_unserialize($sp['arm_subscription_plan_options']);
                            }
                        }
                        $plans_data[$plnID] = $sp;
                    }
                }
                return $plans_data;
            } else {
                return FALSE;
            }
        }

        /**
         * Get all subscritpion plans
         * @return array of plans, False if there is no plan(s).
         */
        function arm_get_all_subscription_plans($fields = 'all', $object_type = ARRAY_A, $allow_user_no_plan = false) {
            global $wp, $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
            $selectFields = '*';
            if (!empty($fields)) {
                if ($fields != 'all' && $fields != '*') {
                    $selectFields = $fields;
                }
            }
            $object_type = !empty($object_type) ? $object_type : ARRAY_A;

            if( isset( $GLOBALS['arm_get_all_subscription_plan_data'] ) && isset( $GLOBALS['arm_get_all_subscription_plan_data'][$selectFields] ) ) {
                $results = $GLOBALS['arm_get_all_subscription_plan_data'][$selectFields];
            } else {
                $results = $wpdb->get_results( $wpdb->prepare("SELECT {$selectFields}, `arm_subscription_plan_id` FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_post_id`=%d AND `arm_subscription_plan_gift_status`=%d ORDER BY `arm_subscription_plan_id` DESC",0,0,0), $object_type);//phpcs:ignore --Reason $ARMember->tbl_arm_subscription_plans is a table name
                $GLOBALS['arm_get_all_subscription_plan_data'][$selectFields] = $results;
            }
            if (!empty($results) || $allow_user_no_plan) {
                $plans_data = array();
                if ($allow_user_no_plan) {
                    $plnID = -2;
                    $plnName = esc_html__('Users Having No Plan', 'ARMember');
                    if ($object_type == OBJECT || $object_type == OBJECT_K) {
                        $sp->arm_subscription_plan_id = $plnID;
                        $sp->arm_subscription_plan_name = $plnName;
                        $sp->arm_subscription_plan_description = '';
                        $sp->arm_subscription_plan_options = array();
                    } else {
                        $sp['arm_subscription_plan_id'] = $plnID;
                        $sp['arm_subscription_plan_name'] = $plnName;
                        $sp['arm_subscription_plan_description'] = '';
                        $sp['arm_subscription_plan_options'] = array();
                    }
                    $plans_data[$plnID] = $sp;
                }
                if (!empty($results)) {
                    foreach ($results as $sp) {
                        if ($object_type == OBJECT || $object_type == OBJECT_K) {
                            $plnID = $sp->arm_subscription_plan_id;
                            if (isset($sp->arm_subscription_plan_name)) {
                                $sp->arm_subscription_plan_name = stripslashes($sp->arm_subscription_plan_name);
                            }
                            if (isset($sp->arm_subscription_plan_description)) {
                                $sp->arm_subscription_plan_description = stripslashes($sp->arm_subscription_plan_description);
                            }
                            if (isset($sp->arm_subscription_plan_options)) {
                                $sp->arm_subscription_plan_options = maybe_unserialize($sp->arm_subscription_plan_options);
                            }
                        } else {
                            $plnID = $sp['arm_subscription_plan_id'];
                            if (isset($sp['arm_subscription_plan_name'])) {
                                $sp['arm_subscription_plan_name'] = stripslashes($sp['arm_subscription_plan_name']);
                            }
                            if (isset($sp['arm_subscription_plan_description'])) {
                                $sp['arm_subscription_plan_description'] = stripslashes($sp['arm_subscription_plan_description']);
                            }
                            if (isset($sp['arm_subscription_plan_options'])) {
                                $sp['arm_subscription_plan_options'] = maybe_unserialize($sp['arm_subscription_plan_options']);
                            }
                        }
                        $plans_data[$plnID] = $sp;
                    }
                }
                return $plans_data;
            } else {
                return array();
            }
        }

        function arm_get_all_active_subscription_plans($orderby = '', $order = '', $allow_user_no_plan = false) {
            global $wp, $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
            $orderby = (!empty($orderby)) ? $orderby : 'arm_subscription_plan_id';
            $order = (!empty($order) && $order == 'ASC') ? 'ASC' : 'DESC';
            /* Query Monitor Settings */
            if( isset($GLOBALS['arm_active_subscription_plan_data'])){
                $results = $GLOBALS['arm_active_subscription_plan_data'];
            } else {
                $results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_status`=%d AND `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_post_id`=%d AND `arm_subscription_plan_gift_status`=%d ORDER BY `" . $orderby . "` " . $order . "",1,0,0,0), ARRAY_A);//phpcs:ignore --Reason $ARMember->tbl_arm_member_templates is a table name
                $GLOBALS['arm_active_subscription_plan_data'] = $results;
            }
            if (!empty($results) || $allow_user_no_plan) {
                $plans_data = array();
                if ($allow_user_no_plan) {
                    $sp['arm_subscription_plan_id'] = -2;
                    $sp['arm_subscription_plan_name'] = esc_html__('Users Having No Plan', 'ARMember');
                    $sp['arm_subscription_plan_description'] = '';
                    $sp['arm_subscription_plan_options'] = array();
                    $plans_data[$sp['arm_subscription_plan_id']] = $sp;
                }
                if (!empty($results)) {
                    foreach ($results as $sp) {
                        $sp['arm_subscription_plan_name'] = stripslashes($sp['arm_subscription_plan_name']);
                        $sp['arm_subscription_plan_description'] = stripslashes($sp['arm_subscription_plan_description']);
                        $sp['arm_subscription_plan_options'] = maybe_unserialize($sp['arm_subscription_plan_options']);
                        $plans_data[$sp['arm_subscription_plan_id']] = $sp;
                    }
                }

                $plans_data = apply_filters( 'arm_all_active_subscription_plans', $plans_data );
                
                return $plans_data;
            } else {
                return FALSE;
            }
        }

        function arm_get_all_active_subscription_plans_and_posts($orderby = '', $order = '', $allow_user_no_post = false) {
            global $wp, $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
            $orderby = (!empty($orderby)) ? $orderby : 'arm_subscription_plan_id';
            $order = (!empty($order) && $order == 'ASC') ? 'ASC' : 'DESC';
            /* Query Monitor Settings */
            if( isset($GLOBALS['arm_active_subscription_plan_and_post_data'])){
                $results = $GLOBALS['arm_active_subscription_plan_and_post_data'];
            } else {
                $results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_status`=%d AND `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_gift_status`=%d ORDER BY `" . $orderby . "` " . $order . "",1,0,0), ARRAY_A);//phpcs:ignore --Reason $ARMember->tbl_arm_subscription_plans is a table name

                $GLOBALS['arm_active_subscription_plan_and_post_data'] = $results;
            }

            if (!empty($results) || $allow_user_no_post) {
                $posts_data = array();
                if ($allow_user_no_post) {
                    $sp['arm_subscription_plan_id'] = -2;
                    $sp['arm_subscription_plan_name'] = esc_html__('Users Having No Plan', 'ARMember');
                    $sp['arm_subscription_plan_description'] = '';
                    $sp['arm_subscription_plan_options'] = array();
                    $posts_data[$sp['arm_subscription_plan_id']] = $sp;
                }
                if (!empty($results)) {
                    foreach ($results as $sp) {
                        $sp['arm_subscription_plan_name'] = !empty($sp['arm_subscription_plan_name']) ? stripslashes($sp['arm_subscription_plan_name']) : '';
                        $sp['arm_subscription_plan_description'] = !empty($sp['arm_subscription_plan_description']) ? stripslashes($sp['arm_subscription_plan_description']) : '';
                        $sp['arm_subscription_plan_options'] = !empty($sp['arm_subscription_plan_options']) ? maybe_unserialize($sp['arm_subscription_plan_options']) : '';
                        $posts_data[$sp['arm_subscription_plan_id']] = $sp;
                    }
                }
                return $posts_data;
            } else {
                return FALSE;
            }
        }
        function arm_get_total_active_plan_counts() {
            global $wp, $wpdb, $ARMember, $arm_global_settings;

            $plan_counts = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(`arm_subscription_plan_id`) FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_status`=%d AND `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_post_id`=%d AND `arm_subscription_plan_gift_status`=%d",1,0,0,0) );//phpcs:ignore --Reason $ARMember->tbl_arm_member_templates is a table name
            return $plan_counts;
        }

        function arm_get_total_plan_counts() {
            global $wp, $wpdb, $ARMember, $arm_global_settings;

            $plan_counts = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(`arm_subscription_plan_id`) FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_post_id`=%d AND `arm_subscription_plan_gift_status`=%d",0,0,0) );//phpcs:ignore --Reason $ARMember->tbl_arm_member_templates is a table name
            return $plan_counts;
        }

        function arm_delete_subscription_plan($plan_id) {
            global $wp, $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
            $res_var = FALSE;
            if (!empty($plan_id) && $plan_id != 0) {
                $plan_detail = new ARM_Plan($plan_id);
                $res_var = $wpdb->update($ARMember->tbl_arm_subscription_plans, array('arm_subscription_plan_is_delete' => '1', 'arm_subscription_plan_status' => '0'), array('arm_subscription_plan_id' => $plan_id));
                if ($res_var) {
                    do_action('arm_deleted_subscription_plan', $plan_id, $plan_detail);
                }
            }
            return $res_var;
        }

        function arm_delete_single_plan() {
            global $wp, $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings,$arm_capabilities_global;
            $ARMember->arm_check_user_cap( $arm_capabilities_global['arm_manage_plans'], '1',1);//phpcs:ignore --Reason:Verifying nonce
            $action = sanitize_text_field( $_POST['act'] );//phpcs:ignore
            $id = intval($_POST['id']);//phpcs:ignore
            if ($action == 'delete') {
                if (empty($id)) {
                    $errors[] = esc_html__('Invalid action.', 'ARMember');
                } else {
                    if (!current_user_can('arm_manage_plans')) {
                        $errors[] = esc_html__('Sorry, You do not have permission to perform this action', 'ARMember');
                    } else {
                        $res_var = self::arm_delete_subscription_plan($id);
                        if ($res_var) {
                            $message = esc_html__('Plan has been deleted successfully.', 'ARMember');
                        }
                    }
                }
            }
            $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            echo json_encode($return_array);
            exit;
        }

        function arm_insert_sample_subscription_plan() {
            global $wp, $wpdb, $wp_roles, $ARMember, $arm_global_settings;
            $totalPlans = $this->arm_get_total_plan_counts();
            if ($totalPlans == 0) {
                $defaultRole = ($wp_roles->is_role('armember')) ? 'armember' : get_option('default_role');
                $plan_options['pricetext'] = esc_html__('Free Membership', 'ARMember');
                $sample_plan_data = array(
                    'arm_subscription_plan_name' => esc_html__('Free Membership', 'ARMember'),
                    'arm_subscription_plan_description' => esc_html__('This is Free Membership Plan.', 'ARMember'),
                    'arm_subscription_plan_type' => 'free',
                    'arm_subscription_plan_options' => maybe_serialize($plan_options),
                    'arm_subscription_plan_amount' => 0,
                    'arm_subscription_plan_status' => 1,
                    'arm_subscription_plan_role' => $defaultRole,
                    'arm_subscription_plan_created_date' => current_time( 'mysql' ),
                );
                //Insert First(Sample) Subscription Plan.
                $wpdb->insert($ARMember->tbl_arm_subscription_plans, $sample_plan_data);
                $plan_id = $wpdb->insert_id;
            }
            return true;
        }

        function arm_user_plan_status_action($atts, $failed_by_system = false) {

            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_email_settings, $arm_members_class, $arm_manage_communication;
            $atts = (!empty($atts)) ? $atts : array();
            $defaults = array(
                'plan_id' => '0', // Plan ID, Pass `all` to get all plans options.
                'user_id' => '0', // User ID.
                'action' => '',
            );
            //Extract Shortcode Attributes
            $args = shortcode_atts($defaults, $atts);


            extract($args);
            if ($plan_id != 0 && $user_id != 0 && !empty($action)) {
                $user_detail = get_userdata($user_id);
                $user_email = $user_detail->user_email;
                $user_login = stripslashes($user_detail->user_login);
                $nowDate = current_time('mysql');

                $action_opt = '';
                $secondary_status = 5;
                $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $user_plans = !empty($user_plans) ? $user_plans : array();

                if (in_array($plan_id, $user_plans)) {

                    $defaultPlanData = $this->arm_default_plan_array();
                    $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                    $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                    $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                    $plan_detail = $planData['arm_current_plan_detail'];
                    $curPlan = new ARM_Plan(0);

                    if (is_array($plan_detail)) {
                        $plan_detail = (object) $plan_detail;
                    }
                    $curPlan->init($plan_detail);

                    $planGracePeriod = 0;
                    if ($curPlan->exists()) {
                        $plan_options = $curPlan->options;
                        if ($curPlan->is_paid() && !$curPlan->is_lifetime()) {
                            if (!empty($plan_options['grace_period'])) {
                                switch ($action) {
                                    case 'eot':
                                        $planGracePeriod = isset($plan_options['grace_period']['end_of_term']) ? $plan_options['grace_period']['end_of_term'] : 0;
                                        $temp_detail_user = $arm_email_settings->arm_get_email_template($arm_email_settings->templates->grace_eot);
                                        $secondary_status = 3;

                                        $u_gateway = $planData['arm_user_gateway'];
                                        $plan_end_date = !empty($planData['arm_expire_plan']) ? $planData['arm_expire_plan'] : $nowDate;
                                        
                                        $u_other_payment_gateway = '';
                                        $u_other_payment_gateway = apply_filters('arm_get_cancel_subscription_gateway_eot', $u_other_payment_gateway, $u_gateway, $user_id, $plan_id, $planData);
                                        if ($u_gateway == 'stripe' || ( !empty($u_gateway) && $u_gateway==$u_other_payment_gateway ) ) {
                                            do_action('arm_cancel_subscription_gateway_action', $user_id, $plan_id);
                                        }
                                        
                                        $action_opt = $plan_options['eot'];
                                        $change_plan_to = $planData['arm_change_plan_to'];
                                        if (!empty($change_plan_to) && $change_plan_to != 0) {
                                            $action_opt = $change_plan_to;
                                        }
                                        break;
                                    case 'failed_payment':
                                        $planGracePeriod = isset($plan_options['grace_period']['failed_payment']) ? $plan_options['grace_period']['failed_payment'] : 0;
                                        $temp_detail_user = $arm_email_settings->arm_get_email_template($arm_email_settings->templates->grace_failed_payment);
                                        
                                        $secondary_status = 5;

                                        $action_opt = $plan_options['payment_failed_action'];
                                        $plan_end_date = !empty($planData['arm_next_due_payment']) ? $planData['arm_next_due_payment'] : $nowDate;
                                        break;
                                    default:
                                        break;
                                }
                            }
                        }
                        $user_in_grace = $planData['arm_is_user_in_grace'];

                        if (!empty($user_in_grace) && $user_in_grace == '1') {
                            $graceEnd = $planData['arm_grace_period_end'];
                            $planGracePeriod = 0;

                            if ($graceEnd > strtotime($nowDate)) {
                                return;
                            }
                        }                        
                        /* Do Action Before Change User's Subscription Status */
                        if ($planGracePeriod > 0) {
                            $graceEndDate = strtotime(date('Y-m-d', $plan_end_date) . " +$planGracePeriod day");

                            $planData['arm_is_user_in_grace'] = '1';
                            $planData['arm_grace_period_end'] = $graceEndDate;
                            $planData['arm_grace_period_action'] = $action;

                            update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $planData);

                            if (isset($temp_detail_user) && $temp_detail_user->arm_template_status == '1') {
                                $user_send_mail = $arm_manage_communication->arm_common_send_email($temp_detail_user,$user_email,$user_id,$plan_id);
                            }
                        } else {
                            do_action('arm_user_plan_status_action_' . $action, $args, $curPlan);
                            if (!empty($action_opt) && !empty($action)) {
                                
                                if(!$failed_by_system){
                                    $this->arm_add_membership_history($user_id, $plan_id, $action, array(), 'system');
                                }

                                if ($this->isPlanExist($action_opt)) {

                                    $this->arm_clear_user_plan_detail($user_id, $plan_id);
                                    $arm_members_class->arm_new_plan_assigned_by_system($action_opt, $plan_id, $user_id);
                                } else {

                                    if ($action == 'eot') {
                                        $this->arm_clear_user_plan_detail($user_id, $plan_id);
                                    } else {
                                        $payment_mode = $planData['arm_payment_mode'];
                                        $arm_user_payment_gateway = $planData['arm_user_gateway'];
                                        $old_next_due_date = $planData['arm_next_due_payment'];
                                        $payment_cycle = $planData['arm_payment_cycle'];
                                        $recurring_data = $curPlan->prepare_recurring_data($payment_cycle);
                                        $amount = $recurring_data['amount'];
                                        if( $payment_mode == 'manual_subscription' ) {
                                            $completed_recurrence = $planData['arm_completed_recurring'];
                                            $completed_recurrence++;
                                            $planData['arm_completed_recurring'] = $completed_recurrence;
                                            update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $planData);  //necessary to update this meta.

                                            $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $plan_id, false, $payment_cycle);
                                            $planData['arm_next_due_payment'] = $arm_next_payment_date;
                                            
                                            update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $planData);
                                            
                                        }

                                        //if ($payment_mode == 'manual_subscription') {
                                            
                                            $extraParam = array();
                                            $extraParam['manual_by'] = 'Paid By system';
                                            $payment_data = array(
                                                'arm_user_id' => $user_id,
                                                'arm_first_name' => $user_detail->first_name,
                                                'arm_last_name' => $user_detail->last_name,
                                                'arm_plan_id' => $plan_id,
                                                'arm_payment_gateway' => $arm_user_payment_gateway,
                                                'arm_payment_type' => 'subscription',
                                                'arm_token' => '-',
                                                'arm_payer_email' => $user_email,
                                                'arm_transaction_payment_type' => 'subscription',
                                                'arm_transaction_status' => 'failed',
                                                'arm_payment_mode' => $payment_mode,
                                                'arm_payment_date' => date('Y-m-d H:i:s', $old_next_due_date),
                                                'arm_extra_vars' => maybe_serialize($extraParam),
                                                
                                                'arm_amount' => $amount,
                                            );
                                            $payment_log_id = $arm_payment_gateways->arm_save_payment_log($payment_data);
                                        //}

                                        $total_user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                        if (!empty($total_user_plans)) {
                                            $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                            $suspended_plan_id = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();

                                            if (!in_array($plan_id, $suspended_plan_id)) {
                                                $suspended_plan_id[] = $plan_id;
                                                update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($suspended_plan_id));
                                            }
                                        }
                                    }
                                }
                            }
                            $planData['arm_is_user_in_grace'] = '0';
                            $planData['arm_grace_period_end'] = '';
                            $planData['arm_grace_period_action'] = '';
                            update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $planData);
                            
                        }

                        switch ($action) {
                            case 'failed_payment':

                                if( empty($planData['arm_is_user_in_grace']) )
                                {
                                    $temp_detail_admin = $arm_email_settings->arm_get_email_template($arm_email_settings->templates->failed_payment_admin);
                                    if ($temp_detail_admin->arm_template_status == '1') {
                                        $all_email_settings = $arm_email_settings->arm_get_all_email_settings();
					
                                        $temp_detail_admin = apply_filters( 'arm_get_modify_admin_mail_template_object_for_send_mail', $temp_detail_admin, $user_id, $plan_id);
					
                                        $admin_send_mail = $arm_global_settings->arm_send_message_to_armember_admin_users('', $temp_detail_admin->arm_template_subject, $temp_detail_admin->arm_template_content);
                                    }
                                    
                                }
                                break;
                            default:
                                break;
                        }




                    } /* End `($curPlan->exists() && $user_plan == $plan_id)` */
                }
            }
        }

        function isFreePlanExist($planID = 0) {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways;
            $isPlan = false;
            if (!empty($planID) && is_numeric($planID) && $planID != 0) {
                $plan = new ARM_Plan($planID);
                $isPlan = ($plan->exists() && $plan->is_active() && $plan->is_free());
            }
            return $isPlan;
        }

        function isPlanExist($planID = 0) {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways;
            $isPlan = false;
            if (!empty($planID) && is_numeric($planID) && $planID != 0) {
                $plan = new ARM_Plan($planID);
                $isPlan = ($plan->exists() && $plan->is_active());
            }
            return $isPlan;
        }

        function arm_ajax_stop_user_subscription($user_id=0, $plan_id=0) {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_members_class, $arm_subscription_cancel_msg, $arm_capabilities_global;

            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $err_msg = $arm_global_settings->common_message['arm_general_msg'];
            $err_msg = (!empty($err_msg)) ? $err_msg : esc_html__('Sorry, Something went wrong. Please try again.', 'ARMember');
            $success_msg = (isset($_POST['cancel_message']) && !empty($_POST['cancel_message'])) ? sanitize_text_field($_POST['cancel_message']) : esc_html__("Your subscription has been cancelled.", 'ARMember');//phpcs:ignore
            $return = array('type' => 'error', 'msg' => $err_msg);
            if (isset($_POST['action']) && $_POST['action'] == 'arm_cancel_membership' && isset($_POST['type']) && $_POST['type'] == 'front') {//phpcs:ignore
                $ARMember->arm_check_user_cap('',0,1); //phpcs:ignore --Reason:Verifying nonce
                $user_id = get_current_user_id();
                $plan_id = !empty( $_REQUEST['plan_id'] ) ? intval($_REQUEST['plan_id']) : '';
            } else if( empty($user_id) && empty($plan_id) ) {
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1);//phpcs:ignore --Reason:Verifying nonce
                $user_id = !empty( $_REQUEST['user_id'] ) ? intval($_REQUEST['user_id']) : '';
                $plan_id = !empty( $_REQUEST['plan_id'] ) ? intval($_REQUEST['plan_id']) : '';
            }

            $defaultPlanData = $this->arm_default_plan_array();
            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();

            $planDataDefault = shortcode_atts($defaultPlanData, $userPlanDatameta);
            $planData = !empty($userPlanDatameta) ? $userPlanDatameta : $planDataDefault;

            $planDetail = $planData['arm_current_plan_detail'];
            if (!empty($planDetail)) {
                $plan = new ARM_Plan(0);
                $plan->init((object) $planDetail);
            } else {
                $plan = new ARM_Plan($plan_id);
            }

            if ($plan->exists()) {
                $cancel_plan_action = isset($plan->options['cancel_plan_action']) ? $plan->options['cancel_plan_action'] : 'immediate';


                if ( ( $plan->is_paid() && !$plan->is_lifetime() && $plan->is_recurring() ) || ( $plan->is_paid() || $plan->is_lifetime() || $plan->is_free() ) ) {
                    global $arm_manage_communication;
                    
                    $cancel_plan_action = apply_filters('arm_before_cancel_subscription', $cancel_plan_action, $plan, $user_id);
                    if ($cancel_plan_action == 'immediate') {

                        if($plan->is_paid() && !$plan->is_lifetime() && ($plan->is_recurring() || $plan->has_trial_period())) {
                            do_action('arm_cancel_subscription_gateway_action', $user_id, $plan_id);
                            if(!empty($planData['arm_user_gateway']) && ($planData['arm_user_gateway'] == "manual"))
                            {
                                $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $plan_id, 'user_id' => $user_id, 'action' => 'on_cancel_subscription'));    
                            }
                        } else {
                            $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $plan_id, 'user_id' => $user_id, 'action' => 'on_cancel_subscription'));
                        }
                        
                        do_action('arm_cancel_subscription', $user_id, $plan_id);
                        if(!empty($arm_subscription_cancel_msg))
                        {
                            $return = array('type' => 'error', 'msg' => $arm_subscription_cancel_msg);
                        }
                        else
                        {
                            $planData['arm_cencelled_plan'] = 'yes';
                            update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $planData);
                            $this->arm_add_membership_history($user_id, $plan_id, 'cancel_subscription');
                            $this->arm_clear_user_plan_detail($user_id, $plan_id);
                            $cancel_plan_act = isset($plan->options['cancel_action']) ? $plan->options['cancel_action'] : 'block';
                            if ($this->isPlanExist($cancel_plan_act)) {
                                $arm_members_class->arm_new_plan_assigned_by_system($cancel_plan_act, $plan_id, $user_id);
                            } else {
                            }
                            $return = array('type' => 'success', 'msg' => $success_msg);
                        }
                    } else if ($cancel_plan_action == 'on_expire') {
                        $payment_mode = !empty($planData['arm_payment_mode']) ? $planData['arm_payment_mode'] : '' ;
                        $plan_cycle = isset($planData['arm_payment_cycle']) ? $planData['arm_payment_cycle'] : '';
                        $paly_cycle_data = $plan->prepare_recurring_data($plan_cycle);

                        if($payment_mode == "auto_debit_subscription" && $cancel_plan_action == "on_expire" && $paly_cycle_data['rec_time'] == 'infinite')
                        {
                            do_action('arm_on_expire_cancel_subscription', $user_id, $plan, $cancel_plan_action, $planData);
                        }

                        if(!empty($arm_subscription_cancel_msg))
                        {
                            $return = array('type' => 'error', 'msg' => $arm_subscription_cancel_msg);
                        }
                        else
                        {
                            $planData['arm_cencelled_plan'] = 'yes';
                            update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $planData);
                            $expire_strtime = '';
                            if ($paly_cycle_data['rec_time'] == 'infinite') {
                                $expire_strtime = $planData['arm_next_due_payment'];
                            } else {
                                $expire_strtime = $planData['arm_expire_plan'];
                            }
                            $expire_time = date_i18n($date_format, $expire_strtime);
                            $success_msg = esc_html__('Your Subscription will be canceled on', 'ARMember') . ' ' . $expire_time;
                            $return = array('type' => 'success', 'msg' => $success_msg);

                            if(!empty($planData['arm_user_gateway']) && ($planData['arm_user_gateway'] == "manual"))
                            {
                                $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $plan_id, 'user_id' => $user_id, 'action' => 'on_cancel_subscription'));    
                            }
                        }
                    }
                    do_action('arm_after_cancel_subscription', $user_id, $plan, $cancel_plan_action, $planData);
                }
            }
            if (isset($_POST['action']) && $_POST['action'] == 'arm_cancel_membership' && isset($_POST['type']) && $_POST['type'] == 'front') {//phpcs:ignore
                echo json_encode($return);
                exit;
            } else {
                return $return;
            }
        }

        function arm_clear_user_plan_detail($user_id = 0, $plan_id = 0, $is_paid_post = false) {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_members_badges, $arm_subscription_plans, $is_multiple_membership_feature, $arm_pay_per_post_feature, $arm_member_forms;
            if (!empty($user_id) && $user_id != 0) {

                $user = get_userdata($user_id);
                $defaultPlanData = $this->arm_default_plan_array();
                $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                if($is_paid_post==false && $arm_pay_per_post_feature->isPayPerPostFeature)
                {
                    $user_paid_posts = get_user_meta($user_id, 'arm_user_post_ids', true);
                    $user_paid_posts = !empty($user_paid_posts) ? $user_paid_posts : array();

                    if (!empty($user_paid_posts) && is_array($user_paid_posts) && array_key_exists($plan_id, $user_paid_posts)) {
                        $is_paid_post = true;
                    }
                }

                $planDetail = $planData['arm_current_plan_detail'];
                if (!empty($planDetail)) {
                    $plan = new ARM_Plan(0);
                    $plan->init((object) $planDetail);
                } else {
                    $plan = new ARM_Plan($plan_id);
                }

                $is_cancelled_by_user = $planData['arm_cencelled_plan'];
                $payment_mode = $planData['arm_payment_mode'];
                $completed_recurrence = isset($planData['arm_completed_recurring']) ? $planData['arm_completed_recurring'] : 0;
                $payment_cycle = $planData['arm_payment_cycle'];
                $total_recurrence = 0;
                if ($plan->is_recurring()) {
                    if ($payment_cycle === '') {
                        $total_recurrence = $plan->options['recurring']['time'];
                    } else {
                        $total_recurrence = $plan->options['payment_cycles'][$payment_cycle]['recurring_time'];
                    }
                }

                if ($plan->is_recurring() && $payment_mode == 'manual_subscription' && $total_recurrence > $completed_recurrence && empty($is_cancelled_by_user)) {
                    
                } else {

                    $arm_changed_expiry_date_plan = get_user_meta($user_id, 'arm_changed_expiry_date_plans', true);
                    $arm_changed_expiry_date_plan = !empty($arm_changed_expiry_date_plan) ? $arm_changed_expiry_date_plan : array();
                    if (!empty($arm_changed_expiry_date_plan)) {
                        if (in_array($plan_id, $arm_changed_expiry_date_plan)) {
                            unset($arm_changed_expiry_date_plan[array_search($plan_id, $arm_changed_expiry_date_plan)]);
                        }
                    }
                    delete_user_meta($user_id, 'arm_user_plan_' . $plan_id);

                    if ($user->has_cap("armember_access_plan_{$plan_id}")) {
                        $user->remove_cap("armember_access_plan_{$plan_id}");
                    }

                    $plan_id_role_array = $arm_subscription_plans->arm_get_plan_role_by_id(array($plan_id));
                    if ($is_multiple_membership_feature->isMultipleMembershipFeature || ($is_paid_post == true)) {
                        $old_plan_ids   = get_user_meta($user_id, 'arm_user_plan_ids', true);
                        $old_plan_ids   = !empty($old_plan_ids) ? $old_plan_ids : array();
                        $old_plan_ids   = array_diff($old_plan_ids, array($plan_id));
                        if (!empty($old_plan_ids)) {
                            $all_plan_roles = $arm_subscription_plans->arm_get_plan_role_by_id($old_plan_ids);
			    $plan_roles = array();
                            if (!empty($all_plan_roles)) {
                                foreach ($all_plan_roles as $all_plan_role) {
                                    $plan_roles[] = $all_plan_role['arm_subscription_plan_role'];
                                }
                            }
                            array_unique($plan_roles);

                            if (!empty($plan_id_role_array) && is_array($plan_id_role_array)) {
                                foreach ($plan_id_role_array as $key => $value) {
                                    $plan_role = $value['arm_subscription_plan_role'];
                                    if (!empty($plan_role) && !in_array($plan_role, $plan_roles)) {
                                        $user->remove_role($plan_role);
                                    }
                                }
                            }
                        } else {
                            if (!empty($plan_id_role_array) && is_array($plan_id_role_array)) {
                                foreach ($plan_id_role_array as $key => $value) {
                                    $plan_role = $value['arm_subscription_plan_role'];
                                    if (!empty($plan_role)) {
                                        $user->remove_role($plan_role);
                                    }
                                }
                            }
                        }
                    } else {
                        if (!empty($plan_id_role_array) && is_array($plan_id_role_array)) {
                            foreach ($plan_id_role_array as $key => $value) {
                                $plan_role = $value['arm_subscription_plan_role'];
                                if (!empty($plan_role)) {
                                    $user->remove_role($plan_role);
                                }
                            }
                        }
                    }


                    if($arm_pay_per_post_feature->isPayPerPostFeature)
                    {
                        $arm_member_forms->arm_update_paid_post_meta($user_id);
                    }

                    $user_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                    $user_plan_ids = !empty($user_plan_ids) ? $user_plan_ids : array();

                    $user_future_plan_ids = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
                    $user_future_plan_ids = !empty($user_future_plan_ids) ? $user_future_plan_ids : array();

                    $user_suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                    $user_suspended_plan_ids = !empty($user_suspended_plan_ids) ? $user_suspended_plan_ids : array();

                    if (in_array($plan_id, $user_plan_ids)) {
                        unset($user_plan_ids[array_search($plan_id, $user_plan_ids)]);
                    }

                    if (in_array($plan_id, $user_future_plan_ids)) {
                        unset($user_future_plan_ids[array_search($plan_id, $user_future_plan_ids)]);
                    }

                    if (in_array($plan_id, $user_suspended_plan_ids)) {
                        unset($user_suspended_plan_ids[array_search($plan_id, $user_suspended_plan_ids)]);
                        update_user_meta($user_id, 'arm_user_suspended_plan_ids', array_values($user_suspended_plan_ids));
                    }

                    if (empty($user_future_plan_ids)) {
                        delete_user_meta($user_id, 'arm_user_future_plan_ids');
                    } else {
                        update_user_meta($user_id, 'arm_user_future_plan_ids', array_values($user_future_plan_ids));
                    }

                    if (empty($user_plan_ids)) {
                        $arm_default_wordpress_role = get_option('default_role','subscriber');
                        $user->add_role($arm_default_wordpress_role);
                        delete_user_meta($user_id, 'arm_user_plan_ids');
                        delete_user_meta($user_id, 'arm_user_last_plan');
                        delete_user_meta($user_id, 'arm_user_suspended_plan_ids');
                        delete_user_meta($user_id, 'arm_changed_expiry_date_plans', true);
                    } else {
                        update_user_meta($user_id, 'arm_user_plan_ids', array_values($user_plan_ids));
                    }
                    $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'plans');
                }
            }
            return;
        }

        /**
         * Update User's Last Subscriptions
         */
        function arm_before_update_user_subscription_action($user_id = 0, $new_plan_id = 0) {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways;
            if (!empty($user_id) && $user_id != 0) {
                $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $old_plan_ids = !empty($old_plan_ids) ? $old_plan_ids : array();
                if (!empty($old_plan_ids) && !in_array($new_plan_id, $old_plan_ids)) {
                    //Cancel User's Last Subscription
                    foreach ($old_plan_ids as $old_plan_id) {
                        do_action('arm_cancel_subscription_gateway_action', $user_id, $old_plan_id);
                    }
                }
            }
        }

        function arm_update_user_subscription($user_id = 0, $new_plan_id = 0, $action_by = '', $allow_trial = true, $arm_last_payment_status = 'success',$start_date='',$entry_id='', $is_plan_assigned = 0) {

            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_manage_communication, $arm_members_badges, $arm_members_class, $is_multiple_membership_feature, $arm_subscription_plans, $arm_pay_per_post_feature,$arm_pro_ration_feature;

            $arm_is_allow_plan_assign = 1;
            $arm_is_allow_plan_assign = apply_filters('arm_is_allow_membership_plan_assign', $arm_is_allow_plan_assign, $user_id, $new_plan_id);

            if (!empty($user_id) && $user_id != 0 && $arm_is_allow_plan_assign) {

                $user_register_verification = isset($arm_global_settings->global_settings['user_register_verification']) ? $arm_global_settings->global_settings['user_register_verification'] : 'auto';
                if($user_register_verification == 'auto')
                {
                    arm_set_member_status($user_id, 1);
                }
                /* Only update plan if Member is active */

                $user = new WP_User($user_id);
                $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                $suspended_plan_ids = !empty($suspended_plan_ids) ? $suspended_plan_ids : array();
                $old_plan = (isset($old_plan_ids) && !empty($old_plan_ids)) ? $old_plan_ids : array();
                $old_plans = $old_plan;
                if (in_array($new_plan_id, $suspended_plan_ids)) {
                    unset($suspended_plan_ids[array_search($new_plan_id, $suspended_plan_ids)]);
                    update_user_meta($user_id, 'arm_user_suspended_plan_ids', $suspended_plan_ids);
                }
                $arm_return_data = array();
                if(!empty($entry_id))
                {
                    $entry_data = $arm_payment_gateways->arm_get_entry_data_by_id($entry_id);
                    $entry_values = maybe_unserialize($entry_data['arm_entry_value']);
                    $arm_return_data = !empty($entry_values['arm_return_data']) ? $entry_values['arm_return_data'] : array();
                    if(empty($arm_return_data))
                    {
                        $arm_return_data = !empty($entry_values['arm_return_data_used']) ? $entry_values['arm_return_data_used'] : array();
                    }
                }

                if (!in_array($new_plan_id, $old_plan)) {

                    $new_plan = !empty($arm_return_data['arm_plan_obj']) ? $arm_return_data['arm_plan_obj'] : new ARM_Plan($new_plan_id);
                    
                    if ($new_plan->exists() && $new_plan->is_active()) {

                        $new_plan = apply_filters('arm_change_plan_before_user_change_plan', $new_plan, $user_id, $old_plan, $new_plan_id);
                        do_action('arm_before_change_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan);

                        $arm_is_allow_plan = 0;
                        $arm_is_allow_plan = apply_filters('arm_allow_subscription_plan_externally', $arm_is_allow_plan, $new_plan);

                        if ($is_multiple_membership_feature->isMultipleMembershipFeature || !empty( $new_plan->isPaidPost ) || $arm_is_allow_plan ) {
                            $mail_type = 'new_subscription';

                            if (!empty($new_plan->plan_role)) {
                                    $user->add_role($new_plan->plan_role);
                            }

                            $old_plans[] = $new_plan_id;

                            update_user_meta($user_id, 'arm_user_plan_ids', array_values($old_plans));
                        } else {
                            if ($action_by == 'admin') {
                                $arm_change_subscription_mail_type = 'on_change_subscription_by_admin';
                            } else {
                                $arm_change_subscription_mail_type = 'change_subscription';
                            }
                            
                            if($arm_pay_per_post_feature->isPayPerPostFeature) {
                                $user_post_ids = get_user_meta($user_id, 'arm_user_post_ids', true);
                                if(!empty($user_post_ids))
                                {
                                    foreach($old_plan_ids as $arm_plan_key => $arm_plan_val)
                                    {
                                       if(isset($user_post_ids[$arm_plan_val]) && in_array($user_post_ids[$arm_plan_val], $user_post_ids))
                                       {
                                            unset($old_plan[$arm_plan_key]);
                                        }
                                    }
                                }
                            } 
			    
                            $old_plan = apply_filters('arm_modify_plan_ids_externally',$old_plan,$user_id);
                            
                            $mail_type = (empty($old_plan)) ? 'new_subscription' : $arm_change_subscription_mail_type;


                            if($arm_pay_per_post_feature->isPayPerPostFeature)
                            {
                                $new_plan_ids = array();

                                $arm_paid_post_ids = get_user_meta($user_id, 'arm_user_post_ids', true);
                                if(!empty($arm_paid_post_ids))
                                {
                                    foreach($arm_paid_post_ids as $arm_paid_post_key => $arm_paid_post_val)
                                    {
                                        array_push($new_plan_ids, $arm_paid_post_key);
                                    }
                                }

                                $arm_gift_ids = get_user_meta($user_id, 'arm_user_gift_ids', true);

                                if(!empty($arm_gift_ids))
                                {
                                    foreach($arm_gift_ids as $arm_gift_key => $arm_gift_val)
                                    {
                                        array_push($new_plan_ids, $arm_gift_val);
                                    }
                                }    

                                array_push($new_plan_ids, $new_plan_id);                                          

                                update_user_meta($user_id, 'arm_user_plan_ids', $new_plan_ids);
                            }
                            else
                            {
                                update_user_meta($user_id, 'arm_user_plan_ids', array($new_plan_id));
                            }
                            

                            if (!empty($old_plan)) {
                                foreach ($old_plan as $old_plan_id) {
                                    $user->remove_cap('armember_access_plan_' . $old_plan_id);
                                    if(!$arm_pro_ration_feature->isProRationFeature)
                                    {
                                        delete_user_meta($user_id, 'arm_user_plan_' . $old_plan_id);
                                    }
                                }

                                $plan_id_role_array = $arm_subscription_plans->arm_get_plan_role_by_id($old_plan);
                                if (!empty($plan_id_role_array) && is_array($plan_id_role_array)) {
                                    foreach ($plan_id_role_array as $key => $value) {
                                        $plan_role = $value['arm_subscription_plan_role'];
                                        if (!empty($plan_role)) {
                                            $user->remove_role($plan_role);
                                            $arm_default_wordpress_role = get_option('default_role','subscriber');
                                            $user->add_role($arm_default_wordpress_role);
                                        }
                                    }
                                }
                            }
                            if (!empty($new_plan->plan_role)) {
                                $user->add_role($new_plan->plan_role);
                            } 
                        }


                        if(empty($is_plan_assigned))
                        {
                            update_user_meta($user_id, 'arm_user_last_plan', $new_plan_id);
                        }

                        $user->add_cap('armember_access_plan_' . $new_plan_id);
                        $defaultPlanData = $this->arm_default_plan_array();
                        $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, true);
                        $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                        $newPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                        $payment_mode = (isset($newPlanData['arm_payment_mode']) && !empty($newPlanData['arm_payment_mode'])) ? $newPlanData['arm_payment_mode'] : 'manual_subscription'; 
                        $payment_cycle = (isset($newPlanData['arm_payment_cycle']) && !empty($newPlanData['arm_payment_cycle'])) ? $newPlanData['arm_payment_cycle'] : 0;

                        if($arm_pro_ration_feature->isProRationFeature && !empty($old_plan) && !empty($user_id)){
                            update_user_meta( $user_id, 'arm_user_old_plan_id', $old_plan);
                        }

                        //Start Plan
                        $start_time = !empty($start_date) ? strtotime($start_date) : strtotime(current_time('mysql'));

                        if ($new_plan->is_recurring()) {
                            if ($new_plan->has_trial_period() && $action_by != 'system') {
                                
                                if (!$arm_pro_ration_feature->isProRationFeature && (isset($old_plan) && !empty($old_plan) ) )  {
                                    $newPlanData['arm_completed_recurring'] = 1;
                                } else {
                                    if($arm_pro_ration_feature->isProRationFeature && !empty($old_plan))
                                    {
                                        $start_time = apply_filters( 'arm_get_recurring_plan_start_date',$user_id,$old_plan,$new_plan_id);
                                    }
                                    $trial_and_sub_start_date = $new_plan->arm_trial_and_plan_start_date($start_time, $payment_mode, $allow_trial, $payment_cycle);
                                    $start_time = isset($trial_and_sub_start_date['subscription_start_date']) ? $trial_and_sub_start_date['subscription_start_date'] : '';
                                    if (isset($trial_and_sub_start_date['arm_expire_plan_trial']) && $trial_and_sub_start_date['arm_expire_plan_trial'] != '') {

                                        $newPlanData['arm_trial_end'] = $trial_and_sub_start_date['arm_expire_plan_trial'];
                                        $newPlanData['arm_trial_start'] = $trial_and_sub_start_date['arm_trial_start_date'];
                                        $newPlanData['arm_is_trial_plan'] = 1;
                                        $newPlanData['arm_completed_recurring'] = 0;
                                    }
                                }
                            } else {
                                $newPlanData['arm_completed_recurring'] = 1;
                            }
                        }
                        $newPlanData['arm_start_plan'] = $start_time;

                        $newPlanData['arm_payment_mode'] = $payment_mode;
                        $newPlanData['arm_payment_cycle'] = $payment_cycle;

                        //Expire Plan
                        $expire_time = $new_plan->arm_plan_expire_time($start_time, $payment_mode, $payment_cycle);
                        if ($expire_time != false) {
                            $newPlanData['arm_expire_plan'] = $expire_time;
                        }

                        /* Set Current Plan Detail */
                        $curPlanDetail = (array) $new_plan->plan_detail;
                        $curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;

                        $newPlanData['arm_current_plan_detail'] = $curPlanDetail;
                        update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $newPlanData);


                        if ($new_plan->is_recurring()) {

                            $arm_user_selected_payment_cycle = $newPlanData['arm_current_plan_detail']['arm_user_selected_payment_cycle'];
                            $arm_subscription_plan_options = maybe_unserialize($newPlanData['arm_current_plan_detail']['arm_subscription_plan_options']);
                            $arm_subscription_plan_options_recurring_time = $arm_subscription_plan_options['payment_cycles'][$arm_user_selected_payment_cycle]['recurring_time'];
                            if($arm_subscription_plan_options_recurring_time=='infinite')
                            {
                                $newPlanData['arm_expire_plan'] = '';
                            }

                            $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $new_plan_id, $allow_trial, $payment_cycle);
                            if ($arm_next_payment_date != '') {
                                $newPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                            }
                        }

                        update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $newPlanData);

                        $this->arm_add_membership_history($user_id, $new_plan_id, 'new_subscription', array(), $action_by);
                        /**
                         * Send Email Notification for Successful Payment
                         */
                        if(empty($is_plan_assigned))
                        {
                            $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $new_plan_id, 'user_id' => $user_id, 'action' => $mail_type));
                        }
                        /**
                         * Update User's Achievements.
                         */
                        $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'plans');
                        do_action('arm_after_user_plan_change', $user_id, $new_plan_id);
                    }
                } else {
 
                    $mail_type = (empty($old_plan)) ? 'new_subscription' : 'renew_subscription';
                    $user = new WP_User($user_id);
                    $new_plan = new ARM_Plan($new_plan_id);
                    if ($new_plan->exists() && $new_plan->is_active()) {

                        $new_plan = apply_filters('arm_change_plan_before_user_renew_subscription', $new_plan, $user_id, $old_plan, $new_plan_id);
                        if (!empty($new_plan->plan_role)) {
                            $user->add_role($new_plan->plan_role);
                        }

                        $defaultPlanData = $this->arm_default_plan_array();
                        $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, true);
                        $userPlanDefaultData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                        $oldPlanData = !empty($userPlanDatameta) ? $userPlanDatameta : $userPlanDefaultData;

                        $payment_mode = isset($oldPlanData['arm_payment_mode']) ? $oldPlanData['arm_payment_mode'] : 'manual_subscription';
                        $payment_cycle = isset($oldPlanData['arm_payment_cycle']) ? $oldPlanData['arm_payment_cycle'] : '';
                        $arm_old_plan_detail = $oldPlanData['arm_current_plan_detail'];
                        $arm_user_old_payment_cycle = '';
                        $arm_user_old_payment_mode = 'manual_subscription';
                        if (!empty($arm_old_plan_detail)) {
                            $arm_user_old_plan_info = new ARM_Plan(0);
                            $arm_user_old_plan_info->init((object) $arm_old_plan_detail);
                            $arm_user_old_payment_cycle = isset($arm_old_plan_detail['arm_user_selected_payment_cycle']) ? $arm_old_plan_detail['arm_user_selected_payment_cycle'] : '';
                            $arm_user_old_payment_mode = isset($arm_old_plan_detail['arm_user_old_payment_mode']) ? $arm_old_plan_detail['arm_user_old_payment_mode'] : '';
                        } else {
                            $arm_user_old_plan_info = new ARM_Plan($new_plan_id);
                        }

                        $arm_user_old_plan_data = $arm_user_old_plan_info->prepare_recurring_data($arm_user_old_payment_cycle);

                        $planObj = new ARM_Plan($new_plan_id);

                        if ($planObj->is_recurring() && $payment_mode == 'manual_subscription') {

                          
                            $total_recurrence = $arm_user_old_plan_data['rec_time'];
                            $completed_rec = $oldPlanData['arm_completed_recurring'];
                            $expiry_time = $oldPlanData['arm_expire_plan'];
                            
                            
                            if ($arm_user_old_payment_mode != 'manual_subscription') {
                               
                                $plan_action = 'renew_subscription';
                            } else {
                             
                                $plan_action = 'renew_or_recurring';
                            }


                            //if ((($completed_rec == $total_recurrence || $completed_rec === '') && $total_recurrence != 'infinite' ) || $plan_action == 'renew_subscription') 
                            if($total_recurrence!='infinite' && $completed_rec >= $total_recurrence)
                            {

                                //Code for keep started plan date show at Current Membership Shortcode which was showing future date.
                                $arm_check_current_time = strtotime(current_time('mysql'));
                                if($arm_check_current_time<$oldPlanData['arm_expire_plan'])
                                {
                                    if( empty($oldPlanData['arm_started_plan_date']) )
                                    {
                                        $oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
                                    }
                                    else if( $oldPlanData['arm_start_plan'] < $oldPlanData['arm_started_plan_date'] ) 
                                    {
                                        $oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
                                    }
                                }

                                $now = !empty($start_date) ? strtotime($start_date) : strtotime(current_time('mysql'));
                                if($expiry_time != ''){
                                    $start_time = $expiry_time;
                                    $oldPlanData['arm_start_plan'] = $start_time;
                                }
                                else{
                                    $start_time = $now;
                                    $oldPlanData['arm_start_plan'] = $start_time;
                                }
                                


                                do_action('arm_before_renew_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan);
                                
                                $expire_time = $new_plan->arm_plan_expire_time($start_time, $payment_mode, $payment_cycle);
                                if ($expire_time != false) {
                                    $oldPlanData['arm_expire_plan'] = $expire_time;
                                }

                                $oldPlanData['arm_completed_recurring'] = 1;

                                $curPlanDetail = (array) $new_plan->plan_detail;
                                $curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
                                $oldPlanData['arm_current_plan_detail'] = $curPlanDetail;
                                update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);

                                $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $new_plan_id, false, $payment_cycle);
                                if ($arm_next_payment_date != '') {
                                    $oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                }

                                $oldPlanData['arm_sent_msgs'] = array();
                                $oldPlanData['arm_trial_end'] = '';
                                $oldPlanData['arm_trial_start'] = '';
                                $oldPlanData['arm_is_trial_plan'] = 0;
                                $oldPlanData['arm_is_user_in_grace'] = 0;
                                $oldPlanData['arm_grace_period_end'] = '';
                                $oldPlanData['arm_grace_period_action'] = '';
                                $oldPlanData['arm_cencelled_plan'] = '';
                                $oldPlanData['arm_subscr_effective'] = '';
                                $oldPlanData['arm_change_plan_to'] = '';

                                update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);

                                $this->arm_add_membership_history($user_id, $new_plan_id, 'renew_subscription');
                                $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $new_plan_id, 'user_id' => $user_id, 'action' => $mail_type));
                                do_action('arm_after_user_plan_renew', $user_id, $new_plan_id);
                            } 
                            else {

                               
                               
                                $completed_rec = $oldPlanData['arm_completed_recurring'];
                                $old_next_due_payment = $oldPlanData['arm_next_due_payment'];

                                

                                $now = !empty($start_date) ? strtotime($start_date) : strtotime(current_time('mysql'));
                                if ($now < $old_next_due_payment) {
                                    
                                    if ($arm_last_payment_status != 'failed') {
                                        $completed_rec = !empty($completed_rec) ? $completed_rec : 0;
                                        $oldPlanData['arm_completed_recurring'] = ($completed_rec + 1);
                                        update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                                        $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $new_plan_id, false, $payment_cycle);
                                        if ($arm_next_payment_date != '') {
                                            $oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                        }
                                    }
                                } else {
                                   
                                    $completed_rec = !empty($completed_rec) ? $completed_rec : 0;
                                    $oldPlanData['arm_completed_recurring'] = ($completed_rec + 1);
                                    update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                                    $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $new_plan_id, false, $payment_cycle);
                                    if ($arm_next_payment_date != '') {
                                        $oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                    }
                                }

                                $oldPlanData['arm_is_user_in_grace'] = 0;
                                $oldPlanData['arm_grace_period_end'] = '';
                                $oldPlanData['arm_grace_period_action'] = '';
                                
                                update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                                do_action('arm_after_user_recurring_payment_done', $user_id, $new_plan_id);

                                $user_subsdata = array();

                                do_action('arm_after_recurring_payment_success_outside', $user_id, $new_plan_id, 'woocommerce', $payment_mode, $user_subsdata);
                            }
                        } else {

                            
                            do_action('arm_before_renew_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan);

                            //Code for keep started plan date show at Current Membership Shortcode which was showing future date.
                            $arm_check_current_time = strtotime(current_time('mysql'));
                            if($arm_check_current_time<$oldPlanData['arm_expire_plan'])
                            {
                                if( empty($oldPlanData['arm_started_plan_date']) )
                                {
                                    $oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
                                }
                                else if( $oldPlanData['arm_start_plan'] < $oldPlanData['arm_started_plan_date'] ) 
                                {
                                    $oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
                                }
                            }

                            //Start Plan
                            $start_time = !empty($start_date) ? strtotime($start_date) :strtotime(current_time('mysql'));
                            if ($planObj->is_paid() && !$planObj->is_lifetime() && !$planObj->is_recurring()) {
                                $payment_type = $arm_user_old_plan_info->options['payment_type'];
                                if ($payment_type == 'one_time') {
                                    $start_time = !empty($oldPlanData['arm_expire_plan']) ? $oldPlanData['arm_expire_plan'] : $start_time;
                                }
                            } else if ($planObj->is_recurring()) {
                                $arm_user_gateway = !empty($oldPlanData['arm_user_gateway']) ? $oldPlanData['arm_user_gateway'] : '';
                                $need_to_cancel_payment_gateway_array = $arm_payment_gateways->arm_need_to_cancel_old_subscription_gateways();
                                $need_to_cancel_payment_gateway_array = !empty($need_to_cancel_payment_gateway_array) ? $need_to_cancel_payment_gateway_array : array();
                                if (!in_array($arm_user_gateway, $need_to_cancel_payment_gateway_array)) {
                                    $start_time = !empty($oldPlanData['arm_expire_plan']) ? $oldPlanData['arm_expire_plan'] : $start_time;
                                }
                            }
                            $oldPlanData['arm_start_plan'] = $start_time;
                            //Expire Plan
                            $expire_time = $new_plan->arm_plan_expire_time($start_time, $payment_mode, $payment_cycle);
                            if ($expire_time != false) {
                                $oldPlanData['arm_expire_plan'] = $expire_time;
                            }

                            $curPlanDetail = (array) $new_plan->plan_detail;
                            $curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
                            $oldPlanData['arm_current_plan_detail'] = $curPlanDetail;

                            $oldPlanData['arm_sent_msgs'] = array();
                            $oldPlanData['arm_trial_end'] = '';
                            $oldPlanData['arm_trial_start'] = '';
                            $oldPlanData['arm_is_trial_plan'] = 0;
                            $oldPlanData['arm_is_user_in_grace'] = 0;
                            $oldPlanData['arm_grace_period_end'] = '';
                            $oldPlanData['arm_grace_period_action'] = '';
                            if ($planObj->is_recurring()) {
                                $oldPlanData['arm_completed_recurring'] = 1;
                                update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                                $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $new_plan_id, false, $payment_cycle);
                                if ($arm_next_payment_date != '') {
                                    $oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                }
                            } else {
                                $oldPlanData['arm_completed_recurring'] = '';
                                $oldPlanData['arm_next_due_payment'] = '';
                            }
                            $oldPlanData['arm_cencelled_plan'] = '';
                            $oldPlanData['arm_subscr_effective'] = '';
                            $oldPlanData['arm_change_plan_to'] = '';

                            update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);

                            $this->arm_add_membership_history($user_id, $new_plan_id, 'renew_subscription');
                            $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $new_plan_id, 'user_id' => $user_id, 'action' => $mail_type));
                            do_action('arm_after_user_plan_renew', $user_id, $new_plan_id);
                        }
                        //Update User's Last Subscriptions
                        $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'plans');
                    }
                }
            }
        }

        function arm_update_user_subscription_for_bank_transfer($user_id = 0, $new_plan_id = 0, $payment_gateway = 'bank_transfer', $payment_cycle = 0, $arm_last_payment_status = 'success') {

            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_manage_communication, $arm_members_badges, $arm_members_class, $is_multiple_membership_feature, $arm_pay_per_post_feature, $arm_pro_ration_feature;
            if (!empty($user_id) && $user_id != 0) {
                arm_set_member_status($user_id, 1);
                /* Only update plan if Member is active */

                $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                $suspended_plan_ids = !empty($suspended_plan_ids) ? $suspended_plan_ids : array();
                $old_plan = (isset($old_plan_ids) && !empty($old_plan_ids)) ? $old_plan_ids : array();
                $old_plans = $old_plan;
                $payment_mode = 'manual_subscription';

                if (!empty($suspended_plan_ids) && in_array($new_plan_id, $suspended_plan_ids)) {
                    unset($suspended_plan_ids[array_search($new_plan_id, $suspended_plan_ids)]);
                    update_user_meta($user_id, 'arm_user_suspended_plan_ids', $suspended_plan_ids);
                }


                if (!in_array($new_plan_id, $old_plan)) {

                    $new_plan = new ARM_Plan($new_plan_id);
                    if ($new_plan->exists() && $new_plan->is_active()) {
                        $user = new WP_User($user_id);

                        $new_plan = apply_filters('arm_change_plan_before_user_change_plan', $new_plan, $user_id, $old_plan, $new_plan_id);
                        do_action('arm_before_change_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan);
                        $is_update_plan = true;

                        $arm_is_allow_multiple_plan = 0;
                        $arm_is_allow_multiple_plan = apply_filters('arm_allow_subscription_plan_externally', $arm_is_allow_multiple_plan, $new_plan);
                        if ( $is_multiple_membership_feature->isMultipleMembershipFeature || !empty( $new_plan->isPaidPost ) || $arm_is_allow_multiple_plan ) {
                            $mail_type = 'new_subscription';

                            if (!empty($new_plan->plan_role)) {
                                $user->add_role($new_plan->plan_role);
                            }


                            $old_plans[] = $new_plan_id;
                            update_user_meta($user_id, 'arm_user_plan_ids', array_values($old_plans));
                        } else {
                            $mail_type = (empty($old_plan)) ? 'new_subscription' : 'change_subscription';

                            /*                             * *********************************** */
                            if (!empty($old_plan)) {
                                $defaultPlanData = $this->arm_default_plan_array();
                                $old_plan_id = isset($old_plans[0]) ? $old_plans[0] : 0;
                                $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, true);
                                $oldPlanData = !empty($oldPlanData) ? $oldPlanData : array();
                                $oldPlanData = shortcode_atts($defaultPlanData, $oldPlanData);
                                $oldPlanDetail = isset($oldPlanData['arm_current_plan_detail']) ? $oldPlanData['arm_current_plan_detail'] : array();
                                if (!empty($oldPlanDetail)) {
                                    $old_plan1 = new ARM_Plan(0);
                                    $old_plan1->init((object) $oldPlanDetail);
                                } else {
                                    $old_plan1 = new ARM_Plan($old_plan_id);
                                }

                                if ($old_plan1->exists()) {
                                    if ($old_plan1->is_lifetime() || $old_plan1->is_free() || ($old_plan1->is_recurring() && $new_plan->is_recurring())) {
                                        $is_update_plan = true;
                                    } else {
                                        $change_act = 'immediate';
                                        if ($old_plan1->enable_upgrade_downgrade_action == 1) {
                                            if (!empty($old_plan1->downgrade_plans) && in_array($new_plan->ID, $old_plan1->downgrade_plans)) {
                                                $change_act = $old_plan1->downgrade_action;
                                            }
                                            if (!empty($old_plan1->upgrade_plans) && in_array($new_plan->ID, $old_plan1->upgrade_plans)) {
                                                $change_act = $old_plan1->upgrade_action;
                                            }
                                            $change_act = apply_filters( 'arm_update_upgrade_downgrade_action_external', $change_act  );
                                        }
                                        $subscr_effective = $oldPlanData['arm_expire_plan'];
                                        if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                            $is_update_plan = false;
                                            $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                            $oldPlanData['arm_change_plan_to'] = $new_plan_id;
                                            update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                        }
                                    }
                                }
                            }


                            if ($is_update_plan) {
                                if($arm_pay_per_post_feature->isPayPerPostFeature)
                                {
                                    $new_plan_id_arr[] = $new_plan_id;

                                    //get post data
                                    $arm_post_data = get_user_meta($user_id, 'arm_user_post_ids', true);                                    

                                    //get gift data
                                    $arm_gift_data = get_user_meta($user_id, 'arm_user_gift_ids', true);

                                    if(!empty($arm_post_data))
                                    {
                                        foreach($arm_post_data as $arm_post_key => $arm_post_value)
                                        {
                                            if(!empty($arm_post_key))
                                            {
                                                array_push($new_plan_id_arr, $arm_post_key);
                                            }
                                        }
                                    }

                                    if(!empty($arm_gift_data))
                                    {
                                        foreach($arm_gift_data as $arm_gift_key => $arm_gift_value)
                                        {
                                            if(!empty($arm_gift_value))
                                            {
                                                array_push($new_plan_id_arr, $arm_gift_value);
                                            }
                                        }    
                                    } 
                                    update_user_meta($user_id, 'arm_user_plan_ids', $new_plan_id_arr);
                                }
                                else
                                {
                                    update_user_meta($user_id, 'arm_user_plan_ids', array($new_plan_id));    
                                }

                                if (!empty($old_plan)) {
                                    foreach ($old_plan as $old_plan_id) {
                                        $user->remove_cap('armember_access_plan_' . $old_plan_id);
                                        delete_user_meta($user_id, 'arm_user_plan_' . $old_plan_id);
                                    }

                                    $plan_id_role_array = $this->arm_get_plan_role_by_id($old_plan);
                                    if (!empty($plan_id_role_array) && is_array($plan_id_role_array)) {
                                        foreach ($plan_id_role_array as $key => $value) {
                                            $plan_role = $value['arm_subscription_plan_role'];
                                            if (!empty($plan_role)) {
                                                $user->remove_role($plan_role);
                                                $arm_default_wordpress_role = get_option('default_role','subscriber');
                                                $user->set_role($arm_default_wordpress_role);
                                            }
                                        }
                                    }
                                }

                                if (!empty($new_plan->plan_role)) {
                                    $user->set_role($new_plan->plan_role);
                                }
                            }
                        }

                        if ($is_update_plan) {
                            $defaultPlanData = $this->arm_default_plan_array();
                            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, true);
                            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                            $newPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                            $arm_old_plan_detail = $newPlanData['arm_current_plan_detail'];

                            if($arm_pro_ration_feature->isProRationFeature && !empty($old_plan) && !empty($user_id)){
                                update_user_meta( $user_id, 'arm_user_old_plan_id', $old_plan);
                            }

                            update_user_meta($user_id, 'arm_user_last_plan', $new_plan_id);

                            $user->add_cap('armember_access_plan_' . $new_plan_id);

                            //Start Plan
                            $start_time = strtotime(current_time('mysql'));
                            if ($new_plan->is_recurring()) {
                                if ($new_plan->has_trial_period()) {
                                    if (isset($old_plan) && !empty($old_plan)) {
                                        $newPlanData['arm_completed_recurring'] = 1;
                                    } else {
                                        $trial_and_sub_start_date = $new_plan->arm_trial_and_plan_start_date('', $payment_mode, true, $payment_cycle);
                                        $start_time = isset($trial_and_sub_start_date['subscription_start_date']) ? $trial_and_sub_start_date['subscription_start_date'] : '';
                                        if (isset($trial_and_sub_start_date['arm_expire_plan_trial']) && $trial_and_sub_start_date['arm_expire_plan_trial'] != '') {

                                            $newPlanData['arm_trial_end'] = $trial_and_sub_start_date['arm_expire_plan_trial'];
                                            $newPlanData['arm_trial_start'] = $trial_and_sub_start_date['arm_trial_start_date'];
                                            $newPlanData['arm_is_trial_plan'] = 1;
                                            $newPlanData['arm_completed_recurring'] = 0;
                                        }
                                    }
                                } else {
                                    $newPlanData['arm_completed_recurring'] = 1;
                                }
                            } else {
                                $payment_mode = '';
                            }

                            $newPlanData['arm_start_plan'] = $start_time;


                            //Expire Plan
                            $expire_time = $new_plan->arm_plan_expire_time($start_time, $payment_mode, $payment_cycle);
                            if ($expire_time != false) {
                                $newPlanData['arm_expire_plan'] = $expire_time;
                            }

                            /* Set Current Plan Detail */
                            $curPlanDetail = (array) $new_plan->plan_detail;
                            $curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
                            $newPlanData['arm_current_plan_detail'] = $curPlanDetail;

                            if ($new_plan->is_recurring()) {
                                $arm_user_selected_payment_cycle = $newPlanData['arm_current_plan_detail']['arm_user_selected_payment_cycle'];
                                $arm_subscription_plan_options = maybe_unserialize($newPlanData['arm_current_plan_detail']['arm_subscription_plan_options']);
                                $arm_subscription_plan_options_recurring_time = $arm_subscription_plan_options['payment_cycles'][$arm_user_selected_payment_cycle]['recurring_time'];
                                if($arm_subscription_plan_options_recurring_time=='infinite')
                                {
                                    $newPlanData['arm_expire_plan'] = '';
                                }
                            }

                            $newPlanData['arm_payment_mode'] = $payment_mode;
                            $newPlanData['arm_payment_cycle'] = $payment_cycle;
                            update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $newPlanData);

                            $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $new_plan_id, true, $payment_cycle);
                            if ($arm_next_payment_date != '') {
                                $newPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                            }
                            $newPlanData['arm_user_gateway'] = 'bank_transfer';
                            update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $newPlanData);

                            /**
                             * Update User's Achievements.
                             */
                            $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'plans');
                            do_action('arm_after_user_plan_change', $user_id, $new_plan_id);
                        }

                        //Update User's Last Subscriptions
                        $this->arm_add_membership_history($user_id, $new_plan_id, 'new_subscription');
                        /**
                         * Send Email Notification for Successful Payment
                         */
                        $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $new_plan_id, 'user_id' => $user_id, 'action' => $mail_type));
                    }
                } else {
                    
                    $mail_type = (empty($old_plan)) ? 'new_subscription' : 'renew_subscription';
                    $user = new WP_User($user_id);
                    $new_plan = new ARM_Plan($new_plan_id);
                    if ($new_plan->exists() && $new_plan->is_active()) {


                        $new_plan = apply_filters('arm_change_plan_before_user_renew_subscription', $new_plan, $user_id, $old_plan, $new_plan_id);
                        if (!empty($new_plan->plan_role)) {
                            $user->set_role($new_plan->plan_role);
                        }

                        $defaultPlanData = $this->arm_default_plan_array();
                        $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, true);
                        $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                        $oldPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                        $arm_old_plan_detail = $oldPlanData['arm_current_plan_detail'];
                        $arm_user_old_payment_cycle = $arm_user_old_payment_mode = '';
                        if (!empty($arm_old_plan_detail)) {
                            $arm_user_old_plan_info = new ARM_Plan(0);
                            $arm_user_old_plan_info->init((object) $arm_old_plan_detail);
                            $arm_user_old_payment_cycle = isset($arm_old_plan_detail['arm_user_selected_payment_cycle']) ? $arm_old_plan_detail['arm_user_selected_payment_cycle'] : '';
                            $arm_user_old_payment_mode = isset($arm_old_plan_detail['arm_user_old_payment_mode']) ? $arm_old_plan_detail['arm_user_old_payment_mode'] : '';
                        } else {
                            $arm_user_old_plan_info = new ARM_Plan($new_plan_id);
                        }

                        $arm_user_old_plan_data = $arm_user_old_plan_info->prepare_recurring_data($arm_user_old_payment_cycle);

                        $planObj = new ARM_Plan($new_plan_id);

                        if ($planObj->is_recurring()) {

                            if ($arm_user_old_payment_mode != 'manual_subscription') {
                                $plan_action = 'renew_subscription';
                            } else {
                                $plan_action = 'renew_or_recurring';
                            }

                            $total_recurrence = $arm_user_old_plan_data['rec_time'];
                            $completed_rec = $oldPlanData['arm_completed_recurring'];
                            $expiry_time = $oldPlanData['arm_expire_plan'];
                            $oldPlanData['arm_payment_mode'] = 'manual_subscription';
                            $oldPlanData['arm_payment_cycle'] = $payment_cycle;

                            update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                            if ((($completed_rec == $total_recurrence || $completed_rec === '') && $total_recurrence != 'infinite') || $plan_action == 'renew_subscription') {
                                do_action('arm_before_renew_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan);
                                $start_time = $expiry_time;

                                //Code for keep started plan date show at Current Membership Shortcode which was showing future date.
                                $arm_check_current_time = strtotime(current_time('mysql'));
                                if($arm_check_current_time<$oldPlanData['arm_expire_plan'])
                                {
                                    if( empty($oldPlanData['arm_started_plan_date']) )
                                    {
                                        $oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
                                    }
                                    else if( $oldPlanData['arm_start_plan'] < $oldPlanData['arm_started_plan_date'] ) 
                                    {
                                        $oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
                                    }
                                }

                                $oldPlanData['arm_start_plan'] = $start_time;
                                $expire_time = $new_plan->arm_plan_expire_time($start_time, $payment_mode, $payment_cycle);
                                if ($expire_time != false) {
                                    $oldPlanData['arm_expire_plan'] = $expire_time;
                                }
                                $oldPlanData['arm_completed_recurring'] = 1;
                                $curPlanDetail = (array) $new_plan->plan_detail;
                                $curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
                                $oldPlanData['arm_current_plan_detail'] = $curPlanDetail;
                                update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                                $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $new_plan_id, true, $payment_cycle);
                                if ($arm_next_payment_date != '') {
                                    $oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                }
                                $oldPlanData['arm_sent_msgs'] = atrray();
                                $oldPlanData['arm_trial_end'] = '';
                                $oldPlanData['arm_trial_start'] = '';
                                $oldPlanData['arm_is_trial_plan'] = 0;
                                $oldPlanData['arm_is_user_in_grace'] = 0;
                                $oldPlanData['arm_grace_period_end'] = '';
                                $oldPlanData['arm_grace_period_action'] = '';
                                $oldPlanData['arm_cencelled_plan'] = '';
                                $oldPlanData['arm_subscr_effective'] = '';
                                $oldPlanData['arm_change_plan_to'] = '';
                                $oldPlanData['arm_user_gateway'] = 'bank_transfer';
                                update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                                $this->arm_add_membership_history($user_id, $new_plan_id, 'renew_subscription');
                                $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $new_plan_id, 'user_id' => $user_id, 'action' => $mail_type));
                                do_action('arm_after_user_plan_renew', $user_id, $new_plan_id);
                            } else {

                                $completed_rec = $oldPlanData['arm_completed_recurring'];
                                $old_next_due_payment = $oldPlanData['arm_next_due_payment'];

                                $now = strtotime(current_time('mysql'));
                                if ($now < $old_next_due_payment) {

                                    if ($arm_last_payment_status != 'failed') {
                                        $completed_rec = !empty($completed_rec) ? $completed_rec : 0;
                                        $oldPlanData['arm_completed_recurring'] = ($completed_rec + 1);
                                        update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                                        $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $new_plan_id, false, $payment_cycle);
                                        if ($arm_next_payment_date != '') {
                                            $oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                        }
                                    }
                                } else {
                                    $completed_rec = !empty($completed_rec) ? $completed_rec : 0;
                                    $oldPlanData['arm_completed_recurring'] = ($completed_rec + 1);
                                    update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                                    $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $new_plan_id, false, $payment_cycle);
                                    if ($arm_next_payment_date != '') {
                                        $oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                    }
                                }
                                $oldPlanData['arm_user_gateway'] = 'bank_transfer';
                                $oldPlanData['arm_is_user_in_grace'] = 0;
                                $oldPlanData['arm_grace_period_end'] = '';
                                $oldPlanData['arm_grace_period_action'] = '';
                                update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                                do_action('arm_after_user_recurring_payment_done', $user_id, $new_plan_id);
                            }
                        } else {

                            do_action('arm_before_renew_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan);

                            //Code for keep started plan date show at Current Membership Shortcode which was showing future date.
                            $arm_check_current_time = strtotime(current_time('mysql'));
                            if($arm_check_current_time<$oldPlanData['arm_expire_plan'])
                            {
                                if( empty($oldPlanData['arm_started_plan_date']) )
                                {
                                    $oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
                                }
                                else if( $oldPlanData['arm_start_plan'] < $oldPlanData['arm_started_plan_date'] ) 
                                {
                                    $oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
                                }
                            }

                            //Start Plan    
                            $start_time = strtotime(current_time('mysql'));
                            if ($planObj->is_paid() && !$planObj->is_lifetime() && !$planObj->is_recurring()) {
                                $payment_type = $arm_user_old_plan_info->options['payment_type'];
                                if ($payment_type == 'one_time') {
                                    $start_time = !empty($oldPlanData['arm_expire_plan']) ? $oldPlanData['arm_expire_plan'] : $start_time;
                                }
                            }
                            $oldPlanData['arm_start_plan'] = $start_time;

                            //Expire Plan
                            $expire_time = $new_plan->arm_plan_expire_time($start_time, $payment_mode, $payment_cycle);

                            if ($expire_time != false) {
                                $oldPlanData['arm_expire_plan'] = $expire_time;
                            }

                            $curPlanDetail = (array) $new_plan->plan_detail;
                            $curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
                            $oldPlanData['arm_current_plan_detail'] = $curPlanDetail;

                            $oldPlanData['arm_payment_mode'] = '';
                            $oldPlanData['arm_payment_cycle'] = '';
                            $oldPlanData['arm_sent_msgs'] = array();
                            $oldPlanData['arm_trial_end'] = 0;
                            $oldPlanData['arm_trial_start'] = '';
                            $oldPlanData['arm_is_trial_plan'] = '';
                            $oldPlanData['arm_is_user_in_grace'] = 0;
                            $oldPlanData['arm_grace_period_end'] = '';
                            $oldPlanData['arm_grace_period_action'] = '';
                            $oldPlanData['arm_completed_recurring'] = '';
                            $oldPlanData['arm_next_due_payment'] = '';
                            $oldPlanData['arm_cencelled_plan'] = '';
                            $oldPlanData['arm_subscr_effective'] = '';
                            $oldPlanData['arm_change_plan_to'] = '';
                            $oldPlanData['arm_user_gateway'] = 'bank_transfer';

                            update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                            $this->arm_add_membership_history($user_id, $new_plan_id, 'renew_subscription');
                            $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $new_plan_id, 'user_id' => $user_id, 'action' => $mail_type));
                            do_action('arm_after_user_plan_renew', $user_id, $new_plan_id);
                        }
                        //Update User's Last Subscriptions

                        $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'plans');
                    }
                }
            }
        }

        function arm_update_user_subscription_for_manual($user_id = 0, $new_plan_id = 0, $payment_gateway = 'manual', $payment_cycle = 0, $arm_last_payment_status = 'success') {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_manage_communication, $arm_members_badges, $arm_members_class, $is_multiple_membership_feature, $arm_pro_ration_feature;
            if (!empty($user_id) && $user_id != 0) {
                arm_set_member_status($user_id, 1);
                /* Only update plan if Member is active */

                $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $future_plan_ids = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
                $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                $suspended_plan_ids = !empty($suspended_plan_ids) ? $suspended_plan_ids : array();
                $old_plan = (isset($old_plan_ids) && !empty($old_plan_ids)) ? $old_plan_ids : array();
                $old_plans = $old_plan;
                $payment_mode = 'manual_subscription';

                if (in_array($new_plan_id, $suspended_plan_ids)) {
                    unset($suspended_plan_ids[array_search($new_plan_id, $suspended_plan_ids)]);
                    update_user_meta($user_id, 'arm_user_suspended_plan_ids', $suspended_plan_ids);
                }


                
                if (!in_array($new_plan_id, $old_plan) && !in_array($new_plan_id, $future_plan_ids)) {

                    $new_plan = new ARM_Plan($new_plan_id);
                    if ($new_plan->exists() && $new_plan->is_active()) {
                        $user = new WP_User($user_id);

                        $new_plan = apply_filters('arm_change_plan_before_user_change_plan', $new_plan, $user_id, $old_plan, $new_plan_id);
                        do_action('arm_before_change_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan);
                        $is_update_plan = true;

                        $arm_is_allow_multiple_plan = 0;
                        $arm_is_allow_multiple_plan = apply_filters('arm_allow_subscription_plan_externally', $arm_is_allow_multiple_plan, $new_plan);
                        if ( $is_multiple_membership_feature->isMultipleMembershipFeature || !empty( $new_plan->isPaidPost ) || $arm_is_allow_multiple_plan ) {
                            $mail_type = 'new_subscription';

                            if (!empty($new_plan->plan_role)) {
                                if (empty($old_plan)) {
                                    $user->set_role($new_plan->plan_role);
                                } else {

                                    $user->add_role($new_plan->plan_role);
                                }
                            }


                            $old_plans[] = $new_plan_id;
                            update_user_meta($user_id, 'arm_user_plan_ids', array_values($old_plans));
                        } else {
                            $mail_type = (empty($old_plan)) ? 'new_subscription' : 'change_subscription';

                            /************************************* */
                            if (!empty($old_plan)) {
                                
                                $defaultPlanData = $this->arm_default_plan_array();
                                $old_plan_id = isset($old_plans[0]) ? $old_plans[0] : 0;
                                $oldPlanData = get_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, true);
                                $oldPlanData = !empty($oldPlanData) ? $oldPlanData : array();
                                $oldPlanData = shortcode_atts($defaultPlanData, $oldPlanData);
                                $oldPlanDetail = isset($oldPlanData['arm_current_plan_detail']) ? $oldPlanData['arm_current_plan_detail'] : array();
                                if (!empty($oldPlanDetail)) {
                                    $old_plan1 = new ARM_Plan(0);
                                    $old_plan1->init((object) $oldPlanDetail);
                                } else {
                                    $old_plan1 = new ARM_Plan($old_plan_id);
                                }

                                if ($old_plan1->exists()) {
                                    if ($old_plan1->is_lifetime() || $old_plan1->is_free() || ($old_plan1->is_recurring() && $new_plan->is_recurring())) {
                                        $is_update_plan = true;
                                    } else {
                                        $change_act = 'immediate';
                                        if ($old_plan1->enable_upgrade_downgrade_action == 1) {
                                            if (!empty($old_plan1->downgrade_plans) && in_array($new_plan->ID, $old_plan1->downgrade_plans)) {
                                                $change_act = $old_plan1->downgrade_action;
                                            }
                                            if (!empty($old_plan1->upgrade_plans) && in_array($new_plan->ID, $old_plan1->upgrade_plans)) {
                                                $change_act = $old_plan1->upgrade_action;
                                            }
                                            $change_act = apply_filters( 'arm_update_upgrade_downgrade_action_external', $change_act  );
                                        }
                                        $subscr_effective = $oldPlanData['arm_expire_plan'];
                                        if ($change_act == 'on_expire' && !empty($subscr_effective)) {
                                            $is_update_plan = false;
                                            $oldPlanData['arm_subscr_effective'] = $subscr_effective;
                                            $oldPlanData['arm_change_plan_to'] = $new_plan_id;
                                            update_user_meta($user_id, 'arm_user_plan_' . $old_plan_id, $oldPlanData);
                                        }
                                    }
                                }
                            }


                            if ($is_update_plan) {
                                update_user_meta($user_id, 'arm_user_plan_ids', array($new_plan_id));

                                if (!empty($old_plan)) {
                                    foreach ($old_plan as $old_plan_id) {
                                        $user->remove_cap('armember_access_plan_' . $old_plan_id);
                                        delete_user_meta($user_id, 'arm_user_plan_' . $old_plan_id);
                                    }

                                    $plan_id_role_array = $this->arm_get_plan_role_by_id($old_plan);
                                    if (!empty($plan_id_role_array) && is_array($plan_id_role_array)) {
                                        foreach ($plan_id_role_array as $key => $value) {
                                            $plan_role = $value['arm_subscription_plan_role'];
                                            if (!empty($plan_role)) {
                                                $user->remove_role($plan_role);
                                                $arm_default_wordpress_role = get_option('default_role','subscriber');
                                                $user->set_role($arm_default_wordpress_role);
                                            }
                                        }
                                    }
                                }

                                if (!empty($new_plan->plan_role)) {
                                    $user->set_role($new_plan->plan_role);
                                }
                            }
                        }

                        if ($is_update_plan) {

                            $defaultPlanData = $this->arm_default_plan_array();
                            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, true);
                            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                            $newPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                            $arm_old_plan_detail = $newPlanData['arm_current_plan_detail'];
                            if($arm_pro_ration_feature->isProRationFeature && !empty($old_plan) && !empty($user_id)){
                                update_user_meta( $user_id, 'arm_user_old_plan_id', $old_plan);
                            }
                            update_user_meta($user_id, 'arm_user_last_plan', $new_plan_id);

                            $user->add_cap('armember_access_plan_' . $new_plan_id);

                            //Start Plan
                            $start_time = strtotime(current_time('mysql'));
                            if ($new_plan->is_recurring()) {
                                if ($new_plan->has_trial_period()) {
                                    if (isset($old_plan) && !empty($old_plan)) {
                                        $newPlanData['arm_completed_recurring'] = 1;
                                    } else {
                                        $trial_and_sub_start_date = $new_plan->arm_trial_and_plan_start_date('', $payment_mode, true, $payment_cycle);
                                        $start_time = isset($trial_and_sub_start_date['subscription_start_date']) ? $trial_and_sub_start_date['subscription_start_date'] : '';
                                        if (isset($trial_and_sub_start_date['arm_expire_plan_trial']) && $trial_and_sub_start_date['arm_expire_plan_trial'] != '') {

                                            $newPlanData['arm_trial_end'] = $trial_and_sub_start_date['arm_expire_plan_trial'];
                                            $newPlanData['arm_trial_start'] = $trial_and_sub_start_date['arm_trial_start_date'];
                                            $newPlanData['arm_is_trial_plan'] = 1;
                                            $newPlanData['arm_completed_recurring'] = 0;
                                        }
                                    }
                                } else {
                                    $newPlanData['arm_completed_recurring'] = 1;
                                }
                            } else {
                                $payment_mode = '';
                            }

                            $newPlanData['arm_start_plan'] = $start_time;


                            //Expire Plan
                            $expire_time = $new_plan->arm_plan_expire_time($start_time, $payment_mode, $payment_cycle);
                            if ($expire_time != false) {
                                $newPlanData['arm_expire_plan'] = $expire_time;
                            }

                            /* Set Current Plan Detail */
                            $curPlanDetail = (array) $new_plan->plan_detail;
                            $curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
                            $newPlanData['arm_current_plan_detail'] = $curPlanDetail;

                            if ($new_plan->is_recurring()) {
                                $arm_user_selected_payment_cycle = $newPlanData['arm_current_plan_detail']['arm_user_selected_payment_cycle'];
                                $arm_subscription_plan_options = maybe_unserialize($newPlanData['arm_current_plan_detail']['arm_subscription_plan_options']);
                                $arm_subscription_plan_options_recurring_time = $arm_subscription_plan_options['payment_cycles'][$arm_user_selected_payment_cycle]['recurring_time'];
                                if($arm_subscription_plan_options_recurring_time=='infinite')
                                {
                                    $newPlanData['arm_expire_plan'] = '';
                                }
                            }

                            $newPlanData['arm_payment_mode'] = $payment_mode;
                            $newPlanData['arm_payment_cycle'] = $payment_cycle;
                            update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $newPlanData);

                            $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $new_plan_id, true, $payment_cycle);
                            if ($arm_next_payment_date != '') {
                                $newPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                            }
                            $newPlanData['arm_user_gateway'] = $payment_gateway;
                            update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $newPlanData);

                            /**
                             * Update User's Achievements.
                             */
                            $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'plans');
                            do_action('arm_after_user_plan_change', $user_id, $new_plan_id);
                        }

                        //Update User's Last Subscriptions
                        $this->arm_add_membership_history($user_id, $new_plan_id, 'new_subscription');
                        /**
                         * Send Email Notification for Successful Payment
                         */
                        $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $new_plan_id, 'user_id' => $user_id, 'action' => $mail_type));
                    }
                } else {
                    
                    $mail_type = (empty($old_plan)) ? 'new_subscription' : 'renew_subscription';
                    $user = new WP_User($user_id);
                    $new_plan = new ARM_Plan($new_plan_id);
                    if ($new_plan->exists() && $new_plan->is_active()) {


                        $new_plan = apply_filters('arm_change_plan_before_user_renew_subscription', $new_plan, $user_id, $old_plan, $new_plan_id);
                        if (!empty($new_plan->plan_role)) {
                            $user->set_role($new_plan->plan_role);
                        }

                        $defaultPlanData = $this->arm_default_plan_array();
                        $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, true);
                        $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                        $oldPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                        $arm_old_plan_detail = $oldPlanData['arm_current_plan_detail'];
                        $arm_user_old_payment_cycle = $arm_user_old_payment_mode = '';
                        if (!empty($arm_old_plan_detail)) {
                            $arm_user_old_plan_info = new ARM_Plan(0);
                            $arm_user_old_plan_info->init((object) $arm_old_plan_detail);
                            $arm_user_old_payment_cycle = isset($arm_old_plan_detail['arm_user_selected_payment_cycle']) ? $arm_old_plan_detail['arm_user_selected_payment_cycle'] : '';
                            $arm_user_old_payment_mode = isset($arm_old_plan_detail['arm_user_old_payment_mode']) ? $arm_old_plan_detail['arm_user_old_payment_mode'] : '';
                        } else {
                            $arm_user_old_plan_info = new ARM_Plan($new_plan_id);
                        }

                        $arm_user_old_plan_data = $arm_user_old_plan_info->prepare_recurring_data($arm_user_old_payment_cycle);

                        $planObj = new ARM_Plan($new_plan_id);

                        if ($planObj->is_recurring()) {

                            if ($arm_user_old_payment_mode != 'manual_subscription') {
                                $plan_action = 'renew_subscription';
                            } else {
                                $plan_action = 'renew_or_recurring';
                            }

                            $total_recurrence = $arm_user_old_plan_data['rec_time'];
                            $completed_rec = $oldPlanData['arm_completed_recurring'];
                            $expiry_time = $oldPlanData['arm_expire_plan'];
                            $oldPlanData['arm_payment_mode'] = 'manual_subscription';
                            $oldPlanData['arm_payment_cycle'] = $payment_cycle;

                            update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                            if ((($completed_rec == $total_recurrence || $completed_rec === '') && $total_recurrence != 'infinite') || $plan_action == 'renew_subscription') {
                                do_action('arm_before_renew_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan);
                                $start_time = $expiry_time;

                                //Code for keep started plan date show at Current Membership Shortcode which was showing future date.
                                $arm_check_current_time = strtotime(current_time('mysql'));
                                if($arm_check_current_time<$oldPlanData['arm_expire_plan'])
                                {
                                    if( empty($oldPlanData['arm_started_plan_date']) )
                                    {
                                        $oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
                                    }
                                    else if( $oldPlanData['arm_start_plan'] < $oldPlanData['arm_started_plan_date'] ) 
                                    {
                                        $oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
                                    }
                                }

                                $oldPlanData['arm_start_plan'] = $start_time;
                                $expire_time = $new_plan->arm_plan_expire_time($start_time, $payment_mode, $payment_cycle);
                                if ($expire_time != false) {
                                    $oldPlanData['arm_expire_plan'] = $expire_time;
                                }
                                $oldPlanData['arm_completed_recurring'] = 1;
                                $curPlanDetail = (array) $new_plan->plan_detail;
                                $curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
                                $oldPlanData['arm_current_plan_detail'] = $curPlanDetail;
                                update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                                $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $new_plan_id, true, $payment_cycle);
                                if ($arm_next_payment_date != '') {
                                    $oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                }
                                $oldPlanData['arm_sent_msgs'] = array();
                                $oldPlanData['arm_trial_end'] = '';
                                $oldPlanData['arm_trial_start'] = '';
                                $oldPlanData['arm_is_trial_plan'] = 0;
                                $oldPlanData['arm_is_user_in_grace'] = 0;
                                $oldPlanData['arm_grace_period_end'] = '';
                                $oldPlanData['arm_grace_period_action'] = '';
                                $oldPlanData['arm_cencelled_plan'] = '';
                                $oldPlanData['arm_subscr_effective'] = '';
                                $oldPlanData['arm_change_plan_to'] = '';
                                $oldPlanData['arm_user_gateway'] = $payment_gateway;
                                update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                                $this->arm_add_membership_history($user_id, $new_plan_id, 'renew_subscription');
                                $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $new_plan_id, 'user_id' => $user_id, 'action' => $mail_type));
                                do_action('arm_after_user_plan_renew', $user_id, $new_plan_id);
                            } else {

                                $completed_rec = $oldPlanData['arm_completed_recurring'];
                                $old_next_due_payment = $oldPlanData['arm_next_due_payment'];

                                $now = strtotime(current_time('mysql'));
                                if ($now < $old_next_due_payment) {

                                    if ($arm_last_payment_status != 'failed') {
                                        $completed_rec = !empty($completed_rec) ? $completed_rec : 0;
                                        $oldPlanData['arm_completed_recurring'] = ($completed_rec + 1);
                                        update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                                        $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $new_plan_id, false, $payment_cycle);
                                        if ($arm_next_payment_date != '') {
                                            $oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                        }
                                    }
                                } else {
                                    $completed_rec = !empty($completed_rec) ? $completed_rec : 0;
                                    $oldPlanData['arm_completed_recurring'] = ($completed_rec + 1);
                                    update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                                    $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_id, $new_plan_id, false, $payment_cycle);
                                    if ($arm_next_payment_date != '') {
                                        $oldPlanData['arm_next_due_payment'] = $arm_next_payment_date;
                                    }
                                }
                                $oldPlanData['arm_user_gateway'] = $payment_gateway;
                                $oldPlanData['arm_is_user_in_grace'] = 0;
                                $oldPlanData['arm_grace_period_end'] = '';
                                $oldPlanData['arm_grace_period_action'] = '';
                                update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                                do_action('arm_after_user_recurring_payment_done', $user_id, $new_plan_id);
                            }
                        } else {

                            do_action('arm_before_renew_user_plans', $user_id, $old_plan, $new_plan_id, $new_plan);

                            //Code for keep started plan date show at Current Membership Shortcode which was showing future date.
                            $arm_check_current_time = strtotime(current_time('mysql'));
                            if($arm_check_current_time<$oldPlanData['arm_expire_plan'])
                            {
                                if( empty($oldPlanData['arm_started_plan_date']) )
                                {
                                    $oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
                                }
                                else if( $oldPlanData['arm_start_plan'] < $oldPlanData['arm_started_plan_date'] ) 
                                {
                                    $oldPlanData['arm_started_plan_date'] = $oldPlanData['arm_start_plan'];
                                }
                            }

				            //Start Plan    
                            $start_time = strtotime(current_time('mysql'));
                            if ($planObj->is_paid() && !$planObj->is_lifetime() && !$planObj->is_recurring()) {
                                $payment_type = $arm_user_old_plan_info->options['payment_type'];
                                if ($payment_type == 'one_time') {
                                                                        
                                    $start_time = !empty($oldPlanData['arm_expire_plan']) ? $oldPlanData['arm_expire_plan'] : $start_time;
                                    
                                }
                            }
                            $oldPlanData['arm_start_plan'] = $start_time;

				            //Expire Plan
                            $expire_time = $new_plan->arm_plan_expire_time($start_time, $payment_mode, $payment_cycle);

                            if ($expire_time != false) {
                                $oldPlanData['arm_expire_plan'] = $expire_time;
                            }

                            $curPlanDetail = (array) $new_plan->plan_detail;
                            $curPlanDetail['arm_user_selected_payment_cycle'] = $payment_cycle;
                            $oldPlanData['arm_current_plan_detail'] = $curPlanDetail;

                            $oldPlanData['arm_payment_mode'] = '';
                            $oldPlanData['arm_payment_cycle'] = '';
                            $oldPlanData['arm_sent_msgs'] = array();
                            $oldPlanData['arm_trial_end'] = 0;
                            $oldPlanData['arm_trial_start'] = '';
                            $oldPlanData['arm_is_trial_plan'] = 0;
                            $oldPlanData['arm_is_user_in_grace'] = 0;
                            $oldPlanData['arm_grace_period_end'] = '';
                            $oldPlanData['arm_grace_period_action'] = '';
                            $oldPlanData['arm_completed_recurring'] = '';
                            $oldPlanData['arm_next_due_payment'] = '';
                            $oldPlanData['arm_cencelled_plan'] = '';
                            $oldPlanData['arm_subscr_effective'] = '';
                            $oldPlanData['arm_change_plan_to'] = '';
                            $oldPlanData['arm_user_gateway'] = $payment_gateway;

                            update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $oldPlanData);
                            $this->arm_add_membership_history($user_id, $new_plan_id, 'renew_subscription');
                            $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $new_plan_id, 'user_id' => $user_id, 'action' => $mail_type));
                            do_action('arm_after_user_plan_renew', $user_id, $new_plan_id);
                        }
                        //Update User's Last Subscriptions

                        $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'plans');

                        $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                        $future_plan_ids = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
                        if(!is_array($old_plan_ids))
                        {
                            $old_plan_ids = array();
                        }
                        if(!in_array($new_plan_id,$old_plan_ids))
                        {
                            $old_plan_ids[] = $new_plan_id;
                            update_user_meta($user_id, 'arm_user_plan_ids', $old_plan_ids);
                        }

                        if(is_array($future_plan_ids))
                        {
                            if (($key = array_search($new_plan_id, $future_plan_ids)) !== false) {
                                unset($future_plan_ids[$key]);
                                update_user_meta($user_id, 'arm_user_future_plan_ids', $future_plan_ids);
                            }
                        }
                    }
                }
            }
        }

        function arm_get_user_membership_detail($user_id = 0, $plan_id = 0, $action = 'new_subscription', $action_by = '') {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_manage_communication;
            $membershipData = array();
            if (!empty($user_id) && $user_id != 0) {
                $current_user_user_id = $user_id;
                if(is_admin())
                {
                    $current_user_user_id = (is_user_logged_in()) ? get_current_user_id() : $user_id;
                }
                $membershipData['current_user'] = $current_user_user_id;
                $membershipData['plan_id'] = $plan_id;
                $membershipData['action_by'] = $action_by;
                $defaultPlanData = $this->arm_default_plan_array();
                $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                if(empty($userPlanDatameta['arm_current_plan_detail']['arm_user_old_payment_mode']))
                {
                    unset($userPlanDatameta['arm_current_plan_detail']['arm_user_old_payment_mode']);
                }
                $planDetail = $userPlanDatameta['arm_current_plan_detail'];
                $payment_cycle = $userPlanDatameta['arm_payment_cycle'];
                if (!empty($planDetail)) {
                    $plan = new ARM_Plan(0);
                    $plan->init((object) $planDetail);
                } else {
                    $plan = new ARM_Plan($plan_id);
                }

                if ($plan->is_recurring()) {
                    $recurring_data = $plan->prepare_recurring_data($payment_cycle);
                    $amount = $recurring_data['amount'];
                } else {
                    $amount = !empty($plan->amount) ? $plan->amount : 0;
                }
                if ($plan->exists()) {
                    $membershipData['plan_name'] = $plan->name;
                    $membershipData['plan_amount'] = $amount;
                    $membershipData['plan_type'] = $plan->type;
                    $membershipData['plan_payment_type'] = $plan->payment_type;
                    $membershipData['plan_text'] = $plan->user_plan_text(false, $payment_cycle);
                    $membershipData['plan_detail'] = (array) $plan->plan_detail;
                }
               
                $changePlanTo = $planData['arm_change_plan_to'];

                $membershipData['arm_subscr_effective'] = $planData['arm_subscr_effective'];
                $membershipData['arm_change_plan_to'] = $changePlanTo;

                if (!empty($changePlanTo) && $changePlanTo == $plan_id) {
                    $membershipData['start'] = $planData['arm_subscr_effective'];
                } else {
                    
                    /*rpt_log changes for trial recurring start date if payment done by admin*/
                    if(isset($plan->recurring_data['trial']) && !empty($plan->recurring_data['trial'])) {
                    $plan_start_date = empty($planData['arm_start_plan']) ? current_time('mysql') : date('Y-m-d H:i:s', $planData['arm_start_plan']);
                    $start_date = "";    
                                            
                    if ( "D" == $plan->recurring_data['trial']['period'] ) {
                        //$day += $plan->recurring_data['trial']['interval'];
                        $start_date = date('Y-m-d H:i:s', strtotime($plan_start_date . " + ".$plan->recurring_data['trial']['interval']." days"));
                    } else if ( "M" == $plan->recurring_data['trial']['period'] ) {
                        //$month += $plan->recurring_data['trial']['interval'];
                        $start_date = date('Y-m-d H:i:s', strtotime($plan_start_date . " + ".$plan->recurring_data['trial']['interval']." months"));
                    } else if ( "Y" == $plan->recurring_data['trial']['period'] ) {
                        //$year += $plan->recurring_data['trial']['interval'];
                        $start_date = date('Y-m-d H:i:s', strtotime($plan_start_date . " + ".$plan->recurring_data['trial']['interval']." years"));
                    }

                    $membershipData['start'] = strtotime($start_date);
                    
                    } else {
                        if(empty($planData['arm_start_plan'])) {
                            $membershipData['start'] = strtotime(current_time('mysql'));    
                        } else {
                            $membershipData['start'] = $planData['arm_start_plan'];
                        }
                    }                   
                    $membershipData['expire'] = $planData['arm_expire_plan'];
                    $membershipData['arm_next_due_payment'] = $planData['arm_next_due_payment'];
                }
                $using_gateway = $planData['arm_user_gateway'];
                $membershipData['gateway'] = (!empty($using_gateway)) ? $using_gateway : 'manual';
                $payment_data = array();
                if ($using_gateway == 'stripe') {
                    $payment_data = $planData['arm_stripe'];
                }
                if ($using_gateway == 'authorize_net') {
                    $payment_data = $planData['arm_authorize_net'];
                }
                if ($using_gateway == '2checkout') {
                    $payment_data = $planData['arm_2checkout'];
                }
                $subscr_id = $planData['arm_subscr_id'];
                if (!empty($subscr_id)) {
                    $payment_data['arm_subscr_id'] = $subscr_id;
                }
                $membershipData['payment_data'] = $payment_data;
            }
            return $membershipData;
        }

        function arm_membership_history_paging_action() {
            if (isset($_POST['action']) && $_POST['action'] == 'arm_membership_history_paging_action') { //phpcs:ignore
                global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_subscription_plans, $arm_capabilities_global;
                
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1); //phpcs:ignore --Reason:Verifying nonce

                $user_id = isset($_POST['user_id']) ? intval( $_POST['user_id'] ) : 0;//phpcs:ignore
                $current_page = isset($_POST['page']) ? intval( $_POST['page'] ) : 1;//phpcs:ignore
                $per_page = isset($_POST['per_page']) ? intval( $_POST['per_page'] ) : 5;//phpcs:ignore
                $is_paid_post = isset($_POST['is_paid_post']) ? intval( $_POST['is_paid_post'] ) : 0;//phpcs:ignore
                $plan_id_name_array = $arm_subscription_plans->arm_get_plan_name_by_id_from_array();
                echo $this->arm_get_user_membership_history($user_id, $current_page, $per_page, $plan_id_name_array,$is_paid_post); //phpcs:ignore
            }
            exit;
        }

        function arm_get_user_membership_history($user_id = 0, $current_page = 1, $perPage = 2, $plan_id_name_array = array(),$is_paid_post = 0) {

            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways;
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $date_time_format = $arm_global_settings->arm_get_wp_date_time_format();
            $general_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
            $arm_currency_decimal = isset($general_settings['arm_currency_decimal_digit']) ? $general_settings['arm_currency_decimal_digit']:2;
            $historyHtml = '';
            if (!empty($user_id) && $user_id != 0) {

                $nowDate = current_time('mysql');

                $perPage = (!empty($perPage) && is_numeric($perPage)) ? $perPage : 5;
                $offset = 0;
                if (!empty($current_page) && $current_page > 1) {
                    $offset = ($current_page - 1) * $perPage;
                }
		
                $where_mhlog = ' AND `arm_paid_post_id` = 0 ';
                if($is_paid_post!=2)
                {
                    $where_mhlog .= ' AND `arm_gift_plan_id` = 0 ';
                }

                $where_mhlog = apply_filters('arm_modify_user_membership_history_where_condition', $where_mhlog, $is_paid_post);

                $mh_label = esc_html__('Plan', 'ARMember');
                if($is_paid_post == 1){
                    $mh_label = esc_html__('Post', 'ARMember');
                    $where_mhlog = $wpdb->prepare('AND `arm_paid_post_id` > %d',0);
                }

                $mh_label = apply_filters('arm_modify_user_membership_history_label_name', $mh_label, $is_paid_post);

                $historyLimit = (!empty($perPage)) ? " LIMIT $offset, $perPage " : "";
                $totalRecord = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(`arm_activity_id`) FROM `" . $ARMember->tbl_arm_activity . "` WHERE `arm_type`=%s AND `arm_user_id`=%d {$where_mhlog}",'membership',$user_id ) );//phpcs:ignore --Reason $ARMember->tbl_arm_activity is a table name
                $historyRecords = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `" . $ARMember->tbl_arm_activity . "` WHERE `arm_type`=%s AND `arm_user_id`=%d AND `arm_action` != %s {$where_mhlog} ORDER BY `arm_activity_id` DESC {$historyLimit}",'membership',$user_id,'recurring_subscription'), ARRAY_A); //phpcs:ignore --Reason $ARMember->tbl_arm_activity is a table name

                    $historyHtml .= '<div class="arm_membership_history_wrapper" data-user_id="' . esc_attr($user_id) . '" data-is_paid_post="'.esc_attr($is_paid_post).'">';
                    $historyHtml .= '<table class="form-table arm_member_last_subscriptions_table" width="100%">';
                    $historyHtml .= '<tr>';
                    $historyHtml .= '<td>'.$mh_label.'</td>';
                    $historyHtml .= '<td>' . esc_html__('Type', 'ARMember') . '</td>';
                    if($is_paid_post != 2){
                        $historyHtml .= '<td>' . esc_html__('Start Date', 'ARMember') . '</td>';
                        $historyHtml .= '<td>' . esc_html__('Expire Date', 'ARMember') . '</td>';
                    }
                    $historyHtml .= '<td>' . esc_html__('Amount', 'ARMember') . '</td>';
                    $historyHtml .= '<td>' . esc_html__('Payment Gateway', 'ARMember') . '</td>';
                    $historyHtml .= '<td>' . esc_html__('Added Date', 'ARMember') . '</td>';
                    $historyHtml .= '</tr>';
                    $isCurrent = false;
                    $item_id_arrray = array();
                    $defaultPlanData = $this->arm_default_plan_array();
                    $change_plan_array = array();
                    $subscr_effective_array = array();
                    $change_plan = '';
                    $subscr_effective = '';
                if (!empty($historyRecords)) {

                    $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                    
                    
                    $user_plans = !empty($user_plans) ? $user_plans : array();
                    $user_plan = isset($user_plans[0]) ? $user_plans[0] : 0;

                    $user_suspended_plans = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                    $user_suspended_plans = (isset($user_suspended_plans)  && !empty($user_suspended_plans))? $user_suspended_plans : array();                    
                    
                    $curPlanName = isset($plan_id_name_array[$user_plan]) ? $plan_id_name_array[$user_plan] : '';
                        
                    foreach ($historyRecords as $mh) {
                        $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $mh['arm_item_id'], true);
                        $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                        $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                        $arm_is_user_in_trial = $planData['arm_is_trial_plan'];
                        if(!empty($planData['arm_change_plan_to']))
                        {
                            if(!in_array($planData['arm_change_plan_to'], $change_plan_array)) {
                                $change_plan_array[] = isset($planData['arm_change_plan_to']) ? $planData['arm_change_plan_to'] : '';
                            }
                            $subscr_effective_array[$planData['arm_change_plan_to']] = isset($planData['arm_subscr_effective']) ? $planData['arm_subscr_effective'] : '';
                        }
                    }

                    foreach ($historyRecords as $mh) {
                        $mh_content = maybe_unserialize($mh['arm_content']);
                        if ($user_plan == $mh['arm_item_id']) {
                            $default_plan_name = $curPlanName;
                        } else {
                            $default_plan_name = $this->arm_get_plan_name_by_id($mh['arm_item_id']);
                        }

                        $activity_date = !empty($mh['arm_date_recorded']) ? $mh['arm_date_recorded'] : '-';                 
                        if( $activity_date != '-' ) {                             
                            $activity_date = date_i18n($date_time_format, strtotime($activity_date));                        
                        }  
                        $plan_expire_date = (!empty($mh['arm_activity_plan_end_date']) && $mh['arm_activity_plan_end_date'] != '0000-00-00 00:00:00') ? strtotime($mh['arm_activity_plan_end_date']) : '';
                        $plan_name = (isset($mh['arm_activity_plan_name'])) ? $mh['arm_activity_plan_name'] : $default_plan_name;
                        if (in_array($mh['arm_item_id'], $user_plans) && !in_array($mh['arm_item_id'], $item_id_arrray )) {
                            
                                if(strtotime($mh['arm_activity_plan_start_date']) <= strtotime($nowDate) || $arm_is_user_in_trial){
                                if(in_array($mh['arm_item_id'], $user_suspended_plans)){
                                    $plan_name .= ' <span style="color: red;">(' . esc_html__('Suspended', 'ARMember') . ')</span>';
                                }
                                else{
                                    if($is_paid_post!='2')
                                    {
                                        $plan_name .= ' <span class="arm_item_status_text active">(' . esc_html__('Current', 'ARMember') . ')</span>';
                                    }
                                    //$plan_expire_date = (!empty($userPlanDatameta['arm_expire_plan'])) ? $userPlanDatameta['arm_expire_plan'] : '';
                                }
                                    $item_id_arrray[] = $mh['arm_item_id'];
                                }
                                if($is_paid_post!='2'){
                                    $plan_additional_label = "";
                                    $plan_additional_label = apply_filters('arm_content_after_membership_plan_name_external', $plan_additional_label, $user_id, $mh['arm_item_id'],array());
                                    $plan_name .= $plan_additional_label;
                                }
                        }
                        $newStartDate = "";
                        
                        if(in_array($mh['arm_item_id'], $change_plan_array))
                        {
                            $change_plan = $mh['arm_item_id'];
                            $subscr_effective = isset($subscr_effective_array[$mh['arm_item_id']]) ? $subscr_effective_array[$mh['arm_item_id']] : '';
                            $newStartDate = date_i18n($date_format, $subscr_effective);
                        }
                        else
                        {
                            $newStartDate = date_i18n($date_format, strtotime($mh['arm_activity_plan_start_date']));
                        }

                        $historyHtml .= '<tr class="arm_member_last_subscriptions_data">';
                        $historyHtml .= '<td>' . $plan_name . '</td>';
                        $historyHtml .= '<td>';
                        $historyhtml_action = '';
                        switch ($mh['arm_action']) {
                            case 'new_subscription':
                                $historyhtml_action .= esc_html__('New Subscription', 'ARMember');
                                break;
                            case 'failed_payment':
                                $historyhtml_action .= esc_html__('Failed Payment', 'ARMember');
                                $mh_content['expire'] = strtotime($mh['arm_date_recorded']);
                                break;
                            case 'cancel_payment':
                            case 'cancel_subscription':
                                $historyhtml_action .= esc_html__('Cancel Subscription', 'ARMember');
                                $mh_content['expire'] = strtotime($mh['arm_date_recorded']);
                                break;
                            case 'eot':
                                $historyhtml_action .= esc_html__('Expire Subscription', 'ARMember');
                                /* manual subscription if user expired */
                                $mh['arm_activity_plan_end_date'] = ($mh['arm_activity_plan_end_date']);
                                break;
                            case 'change_subscription':
                                $historyhtml_action .= esc_html__('Change Subscription', 'ARMember');
                                break;
                            case 'renew_subscription':
                                $historyhtml_action .= esc_html__('Renew Subscription', 'ARMember');
                                break;
                            case 'recurring_subscription':
                                $historyhtml_action .= esc_html__('Recurring Payment', 'ARMember');
                                break;
                            default:
                                break;
                        }
                        $historyhtml_action = apply_filters('arm_change_membership_history_type_text', $historyhtml_action, $mh, $is_paid_post);
                        $historyHtml .= $historyhtml_action;
                        if (isset($mh_content['current_user']) && $mh_content['current_user'] != '0' && $mh_content['current_user'] != $mh['arm_user_id']) {
                            if (isset($mh_content['action_by']) && $mh_content['action_by'] == 'terminate') {
                                $historyHtml .= '<div class="arm_font_size_12"><em>(' . esc_html__('Admin Terminated Account', 'ARMember') . ')</em></div>';
                            } else {
                                $historyHtml .= '<div class="arm_font_size_12"><em>(' . esc_html__('Action By Admin', 'ARMember') . ')</em></div>';
                            }
                        } else if (isset($mh_content['action_by']) && $mh_content['action_by'] == 'system') {
                            $historyHtml .= '<div class="arm_font_size_12"><em>(' . esc_html__('Action by system', 'ARMember') . ')</em></div>';
                        } else if (isset($mh_content['action_by']) && $mh_content['action_by'] == 'close_account') {
                            $historyHtml .= '<div class="arm_font_size_12"><em>(' . esc_html__('User Closed Account', 'ARMember') . ')</em></div>';
                        }
                        $historyHtml .= '</td>';
                        $startDetail = '-';
                        if (isset($mh['arm_activity_plan_start_date']) && !empty($mh['arm_activity_plan_start_date'])) {
                            $startDetail = '';
                
                            if (!in_array($mh['arm_item_id'], $user_plans) && !empty($change_plan) && $subscr_effective > strtotime($nowDate)) {
                                $change_plan_name = $this->arm_get_plan_name_by_id($change_plan);
                                $startDetail .= "<div class='arm_member_detail_confirm_wrapper armGridActionTD'>";
                                $startDetail .= "<div>" . esc_html__('Effective from', 'ARMember') . "</div>";
                                $startDetail .= "<a href='javascript:void(0)' onclick='showConfirmBoxCallback({$mh_content['start']});'>{$newStartDate}</a>";
                                $startDetail .= "<div class='arm_confirm_box arm_confirm_box_".esc_attr(strtotime($mh['arm_activity_plan_start_date']))."' id='arm_confirm_box_".esc_attr( strtotime($mh['arm_activity_plan_start_date']) )."'>";
                                $startDetail .= "<div class='arm_confirm_box_body'>";
                                $startDetail .= "<div class='arm_confirm_box_arrow'></div>";
                                $startDetail .= "<div class='arm_confirm_box_text'>";
                                $startDetail .= "<div class='arm_effective_detail_rows'>";
                                $startDetail .= "<div class='arm_effective_detail_label'>" . esc_html__('Current plan', 'ARMember') . ":</div>";
                                $startDetail .= "<div class='arm_effective_detail_value'>{$curPlanName}</div>";
                                $startDetail .= "</div>";
                                $startDetail .= "<div class='arm_effective_detail_rows'>";
                                $startDetail .= "<div class='arm_effective_detail_label'>" . esc_html__('Plan expiration date', 'ARMember') . ":</div>";
                                $startDetail .= "<div class='arm_effective_detail_value'>{$newStartDate}</div>";
                                $startDetail .= "</div>";
                                $startDetail .= "<div class='arm_effective_detail_rows'>";
                                $startDetail .= "<div class='arm_effective_detail_label'>" . esc_html__('New plan', 'ARMember') . " ({$change_plan_name}) " . esc_html__('will be effective from', 'ARMember') . ":</div>";
                                $startDetail .= "<div class='arm_effective_detail_value'>{$newStartDate}</div>";
                                $startDetail .= "</div>";
                                $startDetail .= "</div>";
                                $startDetail .= "<div class='arm_confirm_box_btn_container'>";
                                $startDetail .= "<button type='button' class='arm_confirm_box_btn armemailaddbtn' onclick='hideConfirmBoxCallback();'>" . esc_html__('Ok', 'ARMember') . "</button>";
                                $startDetail .= "</div>";
                                $startDetail .= "</div>";
                                $startDetail .= "</div>";
                                $startDetail .= "</div>";
                            } else {
                                $startDetail .= $newStartDate;
                            }
                        }
                        if($is_paid_post != 2) 
                        {
                            $historyHtml .= '<td>' . $startDetail . '</td>';
                            $historyHtml .= '<td>';
                            
                            if (!empty($plan_expire_date)) {
                                $historyHtml .= date_i18n($date_format, $plan_expire_date);
                            } else {
                                $historyHtml .= esc_html__('Never Expire', 'ARMember');
                            }
                            $historyHtml .= '</td>';
                        }
                        $historyHtml .= '<td>';
                        if (in_array($mh['arm_action'], array('new_subscription', 'change_subscription', 'renew_subscription', 'recurring_subscription')) && isset($mh_content['plan_text']) && !empty($mh_content['plan_text'])) {
                            $arm_paid_amount = $mh_content['plan_text'];
                            if($is_paid_post==2)
                            {
                                $planTextfor_liftime = esc_html__('For Lifetime', 'ARMember');
                                $arm_paid_amount = trim(str_replace($planTextfor_liftime, '', $mh_content['plan_text']));
                            }
                            $historyHtml .= apply_filters('arm_change_membership_history_paid_amount', $arm_paid_amount, $mh);
                        } else {
                            $historyHtml .= '-';
                        }
                        $historyHtml .= '</td>';
                        $historyHtml .= '<td>';
                        if (isset($mh['arm_activity_payment_gateway']) && !empty($mh['arm_activity_payment_gateway'])) {
                            $historyHtml .= $arm_payment_gateways->arm_gateway_name_by_key($mh['arm_activity_payment_gateway']);
                        } else {
                            $historyHtml .= '-';
                        }
                        $historyHtml .= '</td>';
                        $historyHtml .= '<td>' . $activity_date . '</td>';
                        $historyHtml .= '</tr>';
                    }
                    
                }

                if($totalRecord <= 0)
                {
                    $arm_empty_message = !empty($is_paid_post) ? esc_html__('No Record Found.', 'ARMember') : esc_html__('No Membership History Found.', 'ARMember');
                    $total_column = 7;
                    $historyHtml .= '<tr>';
                    $historyHtml .= '<td colspan="'.esc_attr($total_column).'" class="arm_text_align_center">' . $arm_empty_message . '</td>';
                    $historyHtml .= '</tr>';
                }
                                            
                $historyHtml .= '</table>';
                $historyHtml .= '<div class="arm_membership_history_pagination_block">';
                $historyPaging = $arm_global_settings->arm_get_paging_links($current_page, $totalRecord, $perPage, 'membership_history');
                $historyHtml .= '<div class="arm_membership_history_paging_container">' . $historyPaging . '</div>';
                $historyHtml .= '</div>';
                $historyHtml .= '</div>';
            }
            return $historyHtml;
        }

        function arm_get_membership_history($user_id = 0, $limit = '') {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $history = array();
            if (!empty($user_id) && $user_id != 0) {
                $limit = (!empty($limit)) ? " LIMIT " . $limit : "";
                $actType = 'membership';
                $result_history = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `" . $ARMember->tbl_arm_activity . "` WHERE `arm_type`=%s AND `arm_user_id`=%d ORDER BY `arm_activity_id` DESC {$limit}",$actType,$user_id), ARRAY_A);//phpcs:ignore --Reason $ARMember->tbl_arm_activity is a table name
                if (!empty($result_history)) {
                    foreach ($result_history as $mh) {
                        $activity_id = $mh['arm_activity_id'];
                        $mh['arm_type'] = $actType;
                        $mh['arm_user_id'] = $user_id;
                        $mh['arm_content'] = maybe_unserialize($mh['arm_content']);
                        $history[$activity_id] = $mh;
                    }
                }
            }
            return $history;
        }

        function arm_add_membership_history($user_id = 0, $plan_id = 0, $action = 'new_subscription', $extraVars = array(), $action_by = '') {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_manage_communication;
            if (!empty($user_id) && $user_id != 0) {
                $ip_address = $ARMember->arm_get_ip_address();
                $content = $this->arm_get_user_membership_detail($user_id, $plan_id, $action, $action_by);
                $arm_paid_post_id = isset($content['plan_detail']['arm_subscription_plan_post_id']) ? $content['plan_detail']['arm_subscription_plan_post_id'] : 0;
                $arm_gift_plan_id = (isset($content['plan_detail']['arm_subscription_plan_gift_status']) && $content['plan_detail']['arm_subscription_plan_gift_status'] == 1) ? $plan_id : 0;
                $content_arm_currency = $arm_payment_gateways->arm_get_global_currency();
                $content['arm_currency'] = apply_filters('arm_modify_global_currency_outside', $content_arm_currency);
                $arm_plan_name = $content['plan_name'];
                $arm_plan_type = $content['plan_type'];
                $arm_plan_payment_gateway = $content['gateway'];
                $arm_plan_amount = $content['plan_amount'];
                $arm_plan_start_date = !empty($content['start']) ? date('Y-m-d H:i:s',$content['start']) :'';
                $arm_plan_end_date = !empty($content['expire']) ? date('Y-m-d H:i:s',$content['expire']): '';
                $arm_plan_next_cycle_date = !empty($content['arm_next_due_payment']) ? date('Y-m-d H:i:s',$content['arm_next_due_payment']) : '';
                $membershipActivity = array(
                    'arm_user_id' => $user_id,
                    'arm_type' => 'membership',
                    'arm_action' => $action,
                    'arm_content' => maybe_serialize($content),
                    'arm_item_id' => $plan_id,
                    'arm_paid_post_id' => $arm_paid_post_id,
                    'arm_gift_plan_id' => $arm_gift_plan_id,
                    'arm_link' => '',
                    'arm_activity_plan_name'=>$arm_plan_name,
                    'arm_activity_plan_type'=>$arm_plan_type,
                    'arm_activity_payment_gateway'=>$arm_plan_payment_gateway,
                    'arm_activity_plan_amount'=>$arm_plan_amount,
                    'arm_activity_plan_start_date'=>$arm_plan_start_date,
                    'arm_activity_plan_end_date'=>$arm_plan_end_date,
                    'arm_activity_plan_next_cycle_date'=>$arm_plan_next_cycle_date,
                    'arm_ip_address' => $ip_address,
                    'arm_date_recorded' => current_time('mysql'),
                );
                $membershipActivity = apply_filters('arm_change_membership_activity_before_save', $membershipActivity);
                $_activity = $wpdb->insert($ARMember->tbl_arm_activity, $membershipActivity);
                if ($_activity) {
                    return $wpdb->insert_id;
                }
            }
            return;
        }

        function arm_get_total_members_in_plan($plan_id = 0) {
           global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways;
            $res = 0;
            if (!empty($plan_id) && $plan_id != 0) {
                $user_arg = array(
                    'meta_key' => 'arm_user_plan_ids',
                    'meta_value' => $plan_id,
                    'meta_compare' => 'like',
                    'role__not_in' => 'administrator'
                );
                $users = get_users($user_arg);
                $res = 0;
                foreach ($users as $user) {
                    $plan_ids = get_user_meta($user->ID, 'arm_user_plan_ids', true);
                    if (!empty($plan_ids) && is_array($plan_ids)) {
                        if (in_array($plan_id, $plan_ids)) {
                            $res++;
                        }
                    }
                }
            }
            return $res;
        }

        function arm_get_payment_detail_by_plan($plan_id = 0) {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways;
            $array_return = array();
            if (!empty($plan_id) && $plan_id != 0) {
                $res = $wpdb->get_row( $wpdb->prepare("SELECT `arm_subscription_plan_type`, `arm_subscription_plan_options`, `arm_subscription_plan_amount` FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_id`=%d",$plan_id));//phpcs:ignore --Reason $ARMember->tbl_arm_subscription_plans is a table name
                $plan_type = $res->arm_subscription_plan_type;
                $plan_option = maybe_unserialize($res->arm_subscription_plan_options);
                $plan_amount = $res->arm_subscription_plan_amount;
                if (isset($plan_option['access_type']) && $plan_option['access_type'] == 'lifetime') {
                    $array_return = array('access_type' => 'lifetime', 'plan_type' => $plan_type, 'plan_amount' => $plan_amount);
                } elseif (isset($plan_option['access_type']) && $plan_option['access_type'] == 'finite') {
                    if ($plan_option['payment_type'] == 'subscription') {
                        $rec_time = $plan_option['recurring']['time'];
                        $rec_type = $plan_option['recurring']['type'];
                        $rec_display_type = "";
                        $rec_display_type_ly = "";
                        $rec_per = "";
                        if ($rec_type == 'D') {
                            $rec_display_type = esc_html__("Day(s)", 'ARMember');
                            $rec_display_type_ly = esc_html__("Daily", 'ARMember');
                            $rec_per = $plan_option['recurring']['days'];
                        } elseif ($rec_type == 'M') {
                            $rec_display_type = esc_html__("Months(s)", 'ARMember');
                            $rec_display_type_ly = esc_html__("Monthly", 'ARMember');
                            $rec_per = $plan_option['recurring']['months'];
                        } elseif ($rec_type == 'W') {
                            $rec_display_type = esc_html__("Week(s)", 'ARMember');
                            $rec_display_type_ly = esc_html__("Weekly", 'ARMember');
                            $rec_per = $plan_option['recurring']['weeks'];
                        } elseif ($rec_type == 'Y') {
                            $rec_display_type = esc_html__("Year(s)", 'ARMember');
                            $rec_display_type_ly = esc_html__("Yearly", 'ARMember');
                            $rec_per = $plan_option['recurring']['years'];
                        }

                        $array_return = array('access_type' => 'finite', 'plan_type' => $plan_type, 'plan_amount' => $plan_amount, 'type' => $rec_type, 'display_type' => $rec_display_type, 'plan_period' => $rec_per, 'rec_time' => $rec_time, 'payment_type' => 'subscription', 'display_type_ly' => $rec_display_type_ly);
                    } elseif ($plan_option['payment_type'] == 'one_time') {
                        $rec_type = $plan_option['eopa']['type'];
                        $rec_display_type = "";
                        $rec_display_type_ly = "";
                        $rec_per = "";
                        if ($rec_type == 'D') {
                            $rec_display_type = esc_html__("Day(s)", 'ARMember');
                            $rec_display_type_ly = esc_html__("Daily", 'ARMember');
                            $rec_per = $plan_option['eopa']['days'];
                        } elseif ($rec_type == 'M') {
                            $rec_display_type = esc_html__("Months(s)", 'ARMember');
                            $rec_display_type_ly = esc_html__("Monthly", 'ARMember');
                            $rec_per = $plan_option['eopa']['months'];
                        } elseif ($rec_type == 'W') {
                            $rec_display_type = esc_html__("Week(s)", 'ARMember');
                            $rec_display_type_ly = esc_html__("Weekly", 'ARMember');
                            $rec_per = $plan_option['eopa']['weeks'];
                        } elseif ($rec_type == 'Y') {
                            $rec_display_type = esc_html__("Year(s)", 'ARMember');
                            $rec_display_type_ly = esc_html__("Yearly", 'ARMember');
                            $rec_per = $plan_option['eopa']['years'];
                        }
                        $array_return = array('access_type' => 'finite', 'plan_type' => $plan_type, 'plan_amount' => $plan_amount, 'type' => $rec_type, 'display_type' => $rec_display_type, 'plan_period' => $rec_per, 'payment_type' => 'one_time', 'display_type_ly' => $rec_display_type_ly);
                    }
                } elseif ($plan_type == 'free') {
                    $array_return = array('plan_type' => 'free');
                }
            }
            return $array_return;
        }

        function arm_convert_to_format($type, $count = 0) {
            $string_format = '';
            if (!empty($type) && $count != 0) {
                switch ($type) {
                    case 'D':
                        $datetime = new DateTime();
                        $diff = $datetime->diff(
                                new DateTime(date("Y-m-d H:i:s", strtotime("$count Days")))
                        );
                        $year = $diff->y;
                        $month = $diff->m;
                        $days = $diff->d;
                        $year_s = ( $year != 0 ) ? $year . " " . esc_html__("Year(s)", 'ARMember') : "";
                        $month_s = ( $month != 0 ) ? $month . " " . esc_html__("Month(s)", 'ARMember') : "";
                        $day_s = ( $days != 0 ) ? $days . " " . esc_html__("Day(s)", 'ARMember') : "";
                        $string_format = "$year_s $month_s $day_s";
                        break;
                    case 'M':
                        $datetime = new DateTime();
                        $diff = $datetime->diff(
                                new DateTime(date("Y-m-d H:i:s", strtotime("$count Months")))
                        );
                        $year = $diff->y;
                        $month = $diff->m;
                        $days = $diff->d;
                        $year_s = ( $year != 0 ) ? $year . " " . esc_html__("Year(s)", 'ARMember') : "";
                        $month_s = ( $month != 0 ) ? $month . " " . esc_html__("Month(s)", 'ARMember') : "";
                        $day_s = ( $days != 0 ) ? $days . " " . esc_html__("Day(s)", 'ARMember') : "";
                        $string_format = "$year_s $month_s $day_s";
                        break;
                    case 'Y':
                        $string_format = $count . " " . esc_html__("Year(s)", 'ARMember');
                        break;
                }
            }
            return $string_format;
        }

        /**
         * Add Custom Metaboxes in page/post/custom-post-type screen
         */
        function arm_add_meta_boxes_func() {
            global $wpdb, $post, $pagenow, $ARMember, $arm_global_settings, $arm_access_rules;
            if (current_user_can('administrator') || current_user_can('arm_content_access_rules_metabox')) {
                $totalPlans = $this->arm_get_total_plan_counts();
                if ($totalPlans > 0) {
                    $arm_screens = array('post' => 'post', 'page' => 'page');
                    $custom_post_types = get_post_types(array('public' => true, '_builtin' => false, 'show_ui' => true), 'objects');
                    if (!empty($custom_post_types)) {
                        foreach ($custom_post_types as $cpt) {
                            $arm_screens[$cpt->name] = $cpt->name;
                        }
                    }
                    /* For remove meta box from plugin pages */
                    $arm_current_screen = get_current_screen();
                    if ($arm_current_screen->post_type == 'page' && !empty($post->ID)) {
                        $page_settings = $arm_global_settings->arm_get_single_global_settings('page_settings');

                        $arm_default_redirection_settings = get_option('arm_redirection_settings');
                        $arm_default_redirection_settings = maybe_unserialize($arm_default_redirection_settings);
                        $default_access_rules = $arm_default_redirection_settings['default_access_rules'];

                        unset($page_settings['member_profile_page_id']);
                        unset($page_settings['thank_you_page_id']);
                        unset($page_settings['cancel_payment_page_id']);
                        $page_settings = array_filter($page_settings);
                        if (!empty($default_access_rules['non_logged_in'])) {
                            if ($default_access_rules['non_logged_in']['type'] == 'specific' && !empty($default_access_rules['non_logged_in']['redirect_to'])) {
                                $page_settings[] = $default_access_rules['non_logged_in']['redirect_to'];
                            }
                        }
                        if (!empty($page_settings) && in_array($post->ID, array_values($page_settings))) {
                            unset($arm_screens['page']);
                        }
                    }
                    /* Create meta box for membership access */
                    $arm_context = 'side';
                    $arm_priority = 'high';
                    foreach ($arm_screens as $screen) {
                        do_action('arm_add_meta_boxes', $screen, $arm_context, $arm_priority);
                    }
                    /* Add CSS for Metaboxes */
                    wp_enqueue_style('arm_post_metaboxes_css', MEMBERSHIP_URL . '/css/arm_post_metaboxes.css', array(), MEMBERSHIP_VERSION);
                }
            }
        }

        function arm_apply_plan_to_member_function($plan_id = 0, $user_id = 0, $is_plan_assigned = 0) {
            global $wpdb, $ARMember, $arm_members_class;
            if ($plan_id == 0 || $user_id == 0) {
                return false;
            }
            $plan = new ARM_Plan($plan_id);
            if (empty($plan->ID)) {
                return false;
            }

            $user = get_user_by('id', $user_id);
            if (empty($user) || user_can($user, 'administrator')) {
                return false;
            }
            $old_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
            $old_plan_ids = !empty($old_plan_ids) ? $old_plan_ids : array();
            $old_plan_id = isset($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
            $arm_members_class->arm_new_plan_assigned_by_system($plan_id, $old_plan_id, $user_id, $is_plan_assigned);
            return true;
        }

        function arm_default_plan_array() {
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
                'arm_authorize_net' => array(),
                'arm_2checkout' => array(),
                'arm_paypal' => array(),
                'arm_stripe' => array(),
                'payment_detail' => array(),
                'arm_started_plan_date' => '',
            );

            return apply_filters('arm_default_plan_array_filter', $default_plan_array);
        }

        function arm_is_recurring_payment_of_user($user_id = 0, $plan_id = 0, $payment_mode = '', $recurring_payment_flag = 0) {
            global $arm_subscription_plans;
            $arm_user_plan = $plan_id;
            
            $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $arm_user_plan, true);
            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
            $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);
            
            $return = false;
            if (!empty($arm_user_plan)) {
                $arm_current_plan_detail = $planData['arm_current_plan_detail'];
                if (!empty($arm_current_plan_detail)) {
                    $plan = new ARM_Plan(0);
                    $plan->init((object) $arm_current_plan_detail);

                    if ($plan->is_recurring()) {
                        $arm_payment_mode = $planData['arm_payment_mode'];
                        if ($arm_payment_mode == 'manual_subscription' && $payment_mode == 'manual_subscription') {
                            $arm_completed_recurrence = $planData['arm_completed_recurring'];
                            $arm_user_payment_cycle = $planData['arm_payment_cycle'];

                            $recurring_data = $plan->prepare_recurring_data($arm_user_payment_cycle);
                            $total_recurrence = isset($recurring_data['rec_time']) && !empty($recurring_data['rec_time']) ? $recurring_data['rec_time'] : 0;

                            //if ($arm_completed_recurrence < $total_recurrence) {
                            if($total_recurrence=='infinite' && ( ( (!$plan->has_trial_period() && $arm_completed_recurrence > 1) || (!$plan->has_trial_period() && $arm_completed_recurrence == 1 && $recurring_payment_flag == '1') ) || ( ($plan->has_trial_period() && $arm_completed_recurrence > 0) || ($plan->has_trial_period() && $arm_completed_recurrence == 0 && $recurring_payment_flag == '1') ) ) )
                            {
                                $return = true;
                            }
                            else if (($total_recurrence!='infinite' && $arm_completed_recurrence < $total_recurrence && ((!$plan->has_trial_period() && $arm_completed_recurrence > 1) || ($plan->has_trial_period() && $arm_completed_recurrence > 0)) ) || ($total_recurrence!='infinite' && $arm_completed_recurrence < $total_recurrence && $recurring_payment_flag=='1')) {
                                $return = true;
                            }
                        }
                    }
                }
            }
            return $return;
        }

        function arm_get_plan_payment_cycle($plan_id = 0) {
            global $wp, $wpdb, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
            if($plan_id > 0){$where_condition = $wpdb->prepare(' AND `arm_subscription_plan_id`=%d',$plan_id);}
            $results = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_is_delete`=%d " . $where_condition . " ORDER BY `arm_subscription_plan_id` DESC",0), ARRAY_A);//phpcs:ignore --Reason  $ARMember->tbl_arm_subscription_plans is a table name
            if (!empty($results)) {
                $plans_data = array();
                foreach ($results as $sp) {
                    $plnID = $sp['arm_subscription_plan_id'];
                    $plnName = stripslashes($sp['arm_subscription_plan_name']);
                    $plan_options = maybe_unserialize($sp['arm_subscription_plan_options']);
                    $plan_options['payment_cycles'] = (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) ? $plan_options['payment_cycles'] : array();
                    if (!empty($plan_options['payment_cycles'])) {
                        $plans_data[$plnID] = $plan_options['payment_cycles'];
                    }
                }
                return $plans_data;
            } else {
                return FALSE;
            }
        }

        function arm_update_subscription_card($atts =array(), $tag='')
        {
            global $arm_payment_gateways, $arm_member_forms, $arm_payment_gateways;
            
            /* ====================/.Begin Set Shortcode Attributes./==================== */
            $atts = shortcode_atts(array(
                'title' => '',
                'form_id' => '',
                'submit_text' => esc_html__('Update Card Details', 'ARMember'),
                'message' => '',
                'class' => '',
                //'form_position' => 'center',
                    ), $atts, $tag);
            $atts['message'] = (!empty($atts['message'])) ? $atts['message'] : esc_html__('Your card has been updated successfully.', 'ARMember');
            $atts['type'] = 'edit_card_details';
            /* ====================/.End Set Shortcode Attributes./==================== */
            global $wp, $wpdb, $current_user, $ARMember, $arm_global_settings;
            $content = '';
            $formRandomID = '';
            if (is_user_logged_in()) {
                $default_form_id = $arm_member_forms->arm_get_default_form_id('registration');
                $user_id = get_current_user_id();
                if (isset($atts['form_id']) && !empty($atts['form_id'])) {
                    $user_form_id = $atts['form_id'];
                } else {
                    $user_form_id = get_user_meta($user_id, 'arm_form_id', true);
                }
                $form = new ARM_Form('id', $user_form_id);
                if (!$form->exists() || $form->type != 'registration') {
                    $form = new ARM_Form('id', $default_form_id);
                }
                do_action('arm_before_render_form', $form, $atts);
                if ($form->exists() && !empty($form->fields)) {
                    $form_id = $form->ID;
                    $form_settings = $form->settings;
                    $ref_template = $form->form_detail['arm_ref_template'];
                    $form_style = $form_settings['style'];
                    $form_color_scheme = !empty($form_style['color_scheme']) ? $form_style['color_scheme'] : 'default';
                    /* Form Classes */
                    $form_style['button_position'] = (!empty($form_style['button_position'])) ? $form_style['button_position'] : 'left';
                    $formRandomID = $form_id . '_' . arm_generate_random_code();
                    $form_style_class = ' arm_form_' . $form_id;
                    $form_style_class .= ' arm_form_layout_' . $form_style['form_layout'];

                    if($form_style['form_layout']=='writer')
                    {
                        $form_style_class .= ' arm-default-form arm-material-style ';
                    }
                    else if($form_style['form_layout']=='rounded')
                    {
                        $form_style_class .= ' arm-default-form arm-rounded-style ';
                    }
                    else if($form_style['form_layout']=='writer_border')
                    {
                        $form_style_class .= ' arm-default-form arm--material-outline-style ';
                    }
                    else {
                        $form_style_class .= ' arm-default-form ';
                    }

                    $form_style_class .= ($form_style['label_hide'] == '1') ? ' armf_label_placeholder' : '';
                    $form_style_class .= ' armf_alignment_' . $form_style['label_align'];
                    $form_style_class .= ' armf_layout_' . $form_style['label_position'];
                    $form_style_class .= ' armf_button_position_' . $form_style['button_position'];
                    $form_style_class .= ($form_style['rtl'] == '1') ? ' arm_form_rtl' : ' arm_form_ltr';
                    if (is_rtl()) {
                        $form_style_class .= ' arm_rtl_site';
                    }
                    $form_style_class .= ' ' . $atts['class'];
                    $form_attr = ' name="arm_form" id="arm_form' . esc_attr($formRandomID) . '"';
                    
                    $captcha_code = arm_generate_captcha_code();
                    if (!isset($_SESSION['ARM_FILTER_INPUT'])) {
                        $_SESSION['ARM_FILTER_INPUT'] = array();
                    }
                    if (isset($_SESSION['ARM_FILTER_INPUT'][$formRandomID])) {
                        unset($_SESSION['ARM_FILTER_INPUT'][$formRandomID]);
                    }
                    $_SESSION['ARM_FILTER_INPUT'][$formRandomID] = $captcha_code;
                    $_SESSION['ARM_VALIDATE_SCRIPT'] = true;
                    
                    $form_attr .= ' data-random-id="' . esc_attr($formRandomID) . '" ';
                    
                    $general_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
                    $spam_protection = isset($general_settings['spam_protection']) ? $general_settings['spam_protection'] : '';
                    if (!empty($spam_protection))
                    {
                        $form_attr .= ' data-submission-key="' . esc_attr($captcha_code) . '" ';
                    }

                    /* Add Form Style on front page. */
                    if (!empty($form_style['form_layout']) && $form_style['form_layout'] != '') {
                        $form_style_class .= ' arm_form_style_' . $form_color_scheme;
                    }
                    $form_css = $arm_member_forms->arm_ajax_generate_form_styles($form_id, $form_settings, $atts, $ref_template);
                    /* Form Inner Content */
                    $field_position = !empty($form_style['field_position']) ? $form_style['field_position'] : 'left';
                    $validation_pos = !empty($form_style['validation_position']) ? $form_style['validation_position'] : 'bottom';
                    $content = apply_filters('arm_change_content_before_display_form', $content, 0, $atts);
                    $content .= $form_css['arm_link'];
                    $content .= '<style type="text/css" id="arm_form_style_' . esc_attr($form_id) . '">' . $form_css['arm_css'] . '</style>';
                    $content .= '<div class="arm-form-container">';
                    $content .= '<div class="arm_form_message_container arm_editor_form_fileds_container arm_editor_form_fileds_wrapper arm_form_' . esc_attr($form_id) . '"></div>';
                    $content .= '<div class="armclear"></div>';
                    $content .= '<form method="post" class="arm_form arm_form_edit_profile ' . esc_attr($form_style_class) . '" enctype="multipart/form-data" novalidate ' . $form_attr . '>';
                    $content .= '<div class="arm-df-wrapper arm_msg_pos_' . esc_attr($validation_pos) . '">';
                    /* 20aug2016 */

                    $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                    $general_settings = $all_global_settings['general_settings'];
                    $enable_crop = isset($general_settings['enable_crop']) ? $general_settings['enable_crop'] : 0;

                    $content .= '<div class="arm-df__fields-wrapper arm-df__fields-wrapper_edit_profile arm_field_position_' . esc_attr($field_position) . '" data-form_id="edit_profile">';
                    
                    if (!empty($atts['title'])) {
                        $form_title_position = (!empty($form_style['form_title_position'])) ? $form_style['form_title_position'] : 'left';
                        $content .= '<div class="arm-df__heading armalign' . esc_attr($form_title_position) . '">';
                        $content .= '<span class="arm-df__heading-text">' . $atts['title'] . '</span>';
                        $content .= '</div>';
                    }
                    
                    $content .= $arm_payment_gateways->arm_get_credit_card_box();
                    $content .= $arm_member_forms->arm_member_form_get_single_form_fields($form, $atts, $formRandomID);
                    $content .= '<div class="armclear"></div>';
                    
                    $content .= '</div>';
                    $content .= '<div class="armclear"></div>';
                    $content .= '<input type="hidden" name="arm_action" value="edit_card_details"/>';
                    $content .= '<input type="hidden" name="isAdmin" value="' . ((is_admin()) ? '1' : '0') . '"/>';
                    $content .= '<input type="hidden" name="arm_parent_form_id" value="' . esc_attr($form_id) . '"/>';
                    $content .= '<input type="hidden" name="arm_success_message" value="' . esc_attr($atts['message']) . '"/>';

                    $content .= '<input type="hidden" name="id" value="' . esc_attr($user_id) . '"/>';
                    $content .= do_shortcode('[armember_spam_filters]');
                    $content .= '</div>';
                    $content .= '</form>';
                    $content .= '<div class="armclear"></div>';

                    
                    global $arm_members_activity, $arm_version;
                    $arm_request_version = get_bloginfo('version');
                    $setact = 0;
                    global $check_version;
                    $setact = $arm_members_activity->$check_version();

                    if ($setact != 1) {
                        global $armember_check_plugin_copy;
                        $content .= "<div><span style='color:#FF0000; margin-top:10px; font-size:12px !important; text-align:center; display:block !important;' data-pkg-arm-id='". $armember_check_plugin_copy ."'>Powered by <a href='https://www.armemberplugin.com/redirect.php?rdt=t2&arm_version=$arm_version&arm_request_version=$arm_request_version' target='_blank'>ARMember</a></span></div>";
                        $content .= "<div><span style='color:#FF0000; font-size:12px !important; text-align:center; display:block !important;'>&nbsp;&nbsp;(Unlicensed)</span></div>";
                    }

                    $content .= '</div>';
                    $content = apply_filters('arm_change_content_after_display_form', $content, 0, $atts);
                }
            } else {
                $default_login_form_id = $arm_member_forms->arm_get_default_form_id('login');

                $arm_all_global_settings = $arm_global_settings->arm_get_all_global_settings();

                $page_settings = $arm_all_global_settings['page_settings'];
                $general_settings = $arm_all_global_settings['general_settings'];

                $login_page_id = (isset($page_settings['login_page_id']) && $page_settings['login_page_id'] != '' && $page_settings['login_page_id'] != 404 ) ? $page_settings['login_page_id'] : 0;
                if ($login_page_id == 0) {

                    if ($general_settings['hide_wp_login'] == 1) {
                        $login_page_url = ARM_HOME_URL;
                    } else {
                        $referral_url = wp_get_current_page_url();
                        $referral_url = (!empty($referral_url) && $referral_url != '') ? $referral_url : wp_get_current_page_url();
                        $login_page_url = wp_login_url($referral_url);
                    }
                } else {
                    $login_page_url = get_permalink($login_page_id) . '?arm_redirect=' . urlencode(wp_get_current_page_url());
                }
                $login_page_url = apply_filters('arm_modify_redirection_page_external', $login_page_url,0,$login_page_id);
                
                if (is_home()) {
                    return '';
                } else {
                    if (preg_match_all('/arm_redirect/', $login_page_url, $matche) < 2) {
                        wp_redirect($login_page_url);
                    }
                }
            }
            $ARMember->enqueue_angular_script();
            $ARMember->arm_check_font_awesome_icons($content);

            $inbuild = '';
            $hiddenvalue = '';
            
            global $arm_members_activity, $arm_version;
            $arm_request_version = get_bloginfo('version');
            $setact = 0;
            global $check_version;
            $setact = $arm_members_activity->$check_version();

            if($setact != 1)
                $inbuild = " (U)";

            $hiddenvalue = '  
            <!--Plugin Name: ARMember    
                Plugin Version: ' . get_option('arm_version') . ' ' . $inbuild . '
                Developed By: Repute Infosystems
                Developer URL: http://www.reputeinfosystems.com/
            -->';

            return $content.$hiddenvalue;
        
        }

        function arm_member_memberships($user_id, $is_paid_post, $arm_page, $arm_perpage)
        {
            global $arm_global_settings, $arm_pay_per_post_feature, $arm_manage_gift;
            $date_format = $arm_global_settings->arm_get_wp_date_format();

            $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
            $user_plans = !empty($user_plans) ? $user_plans : array();        

            $user_posts = get_user_meta($user_id, 'arm_user_post_ids', true);
            $user_posts = !empty($user_posts) ? $user_posts : array();

            $user_gifts = get_user_meta($user_id, 'arm_user_gift_ids', true);
            $user_gifts = !empty($user_gifts) ? $user_gifts : array();             

            $user_future_plans = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
            $user_future_plans = !empty($user_future_plans) ? $user_future_plans : array();

            if($is_paid_post == 1)
            {
                $user_plans = !empty($user_posts) ? $user_posts : array();
                $updated_user_plans = array();
                if( !empty( $user_plans ) )
                {
                    foreach( $user_plans as $uplan_id => $upost_id )
                    {
                        $updated_user_plans[] = $uplan_id;
                    }
                }
                if( !empty( $updated_user_plans ) )
                {
                    $user_plans = $updated_user_plans;
                }
            }
            else if($is_paid_post == 2)
            {
                $user_plans = !empty($user_gifts) ? $user_gifts : array();
                $updated_user_plans = array();
                if( !empty( $user_plans ) )
                {
                    foreach( $user_plans as $uplan_key => $uplan_id )
                    {
                        $updated_user_plans[] = $uplan_id;
                    }
                }
                if( !empty( $updated_user_plans ) )
                {
                    $user_plans = $updated_user_plans;
                }
            }
            else
            {
                if(!empty($user_plans) && !empty($user_posts))
                {
                    foreach ($user_plans as $user_plans_key => $user_plans_val) {
                        if(!empty($user_posts)){
                            foreach ($user_posts as $user_post_key => $user_post_val) {
                                if($user_post_key==$user_plans_val){
                                    unset($user_plans[$user_plans_key]);
                                }
                            }
                        }
                    }
                }

                if(!empty($user_plans) && !empty($user_gifts))
                {
                    foreach ($user_plans as $user_plans_key => $user_plans_val) {
                        if(!empty($user_gifts)){
                            foreach ($user_gifts as $user_gift_key => $user_gift_val) {
                                if($user_gift_val==$user_plans_val){
                                    unset($user_plans[$user_plans_key]);
                                }
                            }
                        }
                    }
                }

                if( !empty( $user_future_plans ) && !empty($user_posts) ){
                    foreach( $user_future_plans as $f_plan_key => $f_plan_id ){
                        $paid_post_id = $arm_pay_per_post_feature->arm_get_post_from_plan_id( $f_plan_id );
                        if( !empty( $paid_post_id[0]['arm_subscription_plan_id'] && !empty( $paid_post_id[0]['arm_subscription_plan_post_id'] ) ) ){
                            unset( $user_future_plans[$f_plan_key] );
                        }
                    }
                }

                if( !empty( $user_future_plans ) && !empty($user_gifts) ){
                    foreach( $user_future_plans as $f_plan_key => $f_plan_id ){
                        $arm_gift_id = $arm_manage_gift->arm_get_gift_from_plan_id( $f_plan_id );
                        if( !empty( $arm_gift_id[0]['arm_subscription_plan_id'] && !empty( $arm_gift_id[0]['arm_subscription_plan_post_id'] ) ) ){
                            unset( $user_future_plans[$f_plan_key] );
                        }
                    }
                }
            }

            $offset = (!empty($arm_page) && $arm_page > 1) ? (($arm_page - 1) * $arm_perpage) : 0;
            $membership_count = count($user_plans);
            $user_plans = array_slice($user_plans, $offset, $arm_perpage);

            $user_all_plans = array();
            if(!empty($user_future_plans))
            {
                if($is_paid_post)
                {
                    if( !empty( $user_future_plans ) ){
                        foreach( $user_future_plans as $fPlanKey => $fPlanId ){
                            $fPlanData = $arm_pay_per_post_feature->arm_get_post_from_plan_id( $fPlanId );

                            if( !empty( $fPlanData[0]['arm_subscription_plan_id'] ) && !empty( $fPlanData[0]['arm_subscription_plan_post_id'] ) ){
                                array_push($user_all_plans, $fPlanData[0]['arm_subscription_plan_id']);
                            }
                        }
                    }
                    $user_all_plans = array_merge($user_plans, $user_all_plans);
                }
                else
                {
                    $user_all_plans = array_merge($user_plans, $user_future_plans);
                }
            }
            else 
            {
                $user_all_plans = $user_plans;
            }

            $memberships_array = array();
            if (!empty($user_all_plans)) {
                $sr_no = 0;
                $change_plan_to_array = array();
                foreach ($user_all_plans as $user_plan) {
                    $planData = get_user_meta($user_id, 'arm_user_plan_' . $user_plan, true);
                    $curPlanDetail = !empty($planData['arm_current_plan_detail']) ? $planData['arm_current_plan_detail'] : '';
                    $start_plan = !empty($planData['arm_start_plan']) ? $planData['arm_start_plan'] : '';
                    if(!empty($planData['arm_started_plan_date']) && $planData['arm_started_plan_date']<=$start_plan)
                    {
                        $start_plan = $planData['arm_started_plan_date'];
                    }
                    $expire_plan = !empty($planData['arm_expire_plan']) ? $planData['arm_expire_plan'] : '';
                    $change_plan = !empty($planData['arm_change_plan_to']) ? $planData['arm_change_plan_to'] : '';
                    $effective_from  = !empty($planData['arm_subscr_effective']) ? $planData['arm_subscr_effective'] : '';

                    if($change_plan != '' && $effective_from != '' && !empty($effective_from) && !empty($change_plan)){
                        $change_plan_to_array[$change_plan] = $effective_from;
                    }
                    $payment_mode = '';
                    $payment_cycle = '';
                    $is_plan_cancelled = '';
                    $completed = '';
                    $recurring_time = '';
                    $recurring_profile = '';
                    $next_due_date = '-';
                    $user_payment_mode = '';
                    if (!empty($curPlanDetail)) {
                        $plan_info = new ARM_Plan(0);
                        $plan_info->init((object) $curPlanDetail);
                    } else {
                        $plan_info = new ARM_Plan($user_plan);
                    }

                    $arm_plan_is_suspended = '';
                    $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                    $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
                    if (!empty($suspended_plan_ids)) {
                        if (in_array($user_plan, $suspended_plan_ids)) {
                            $arm_plan_is_suspended = esc_html__('Suspended', 'ARMember');
                        }
                    }

                    if ($plan_info->exists()) {
                        $sr_no++;
                        $plan_options = $plan_info->options;

                        if ($plan_info->is_recurring()) {
                            $completed = $planData['arm_completed_recurring'];
                            $is_plan_cancelled = $planData['arm_cencelled_plan'];
                            $payment_mode = $planData['arm_payment_mode'];
                            $payment_cycle = $planData['arm_payment_cycle'];
                            $recurring_plan_options = $plan_info->prepare_recurring_data($payment_cycle);
                            $recurring_time = $recurring_plan_options['rec_time'];
                            $next_due_date = $planData['arm_next_due_payment'];

                            if ($payment_mode == 'auto_debit_subscription') {
                                $user_payment_mode = esc_html__('Auto Debit', 'ARMember');
                            }
                            $arm_trial_start_date = $planData['arm_trial_start'];
                            $arm_is_user_in_trial = $planData['arm_is_trial_plan'];

                            if ($recurring_time == 'infinite' || empty($expire_plan)) {
                                $remaining_occurence = esc_html__('Never Expires', 'ARMember');
                            } else {
                                $remaining_occurence = $recurring_time - $completed;
                            }

                            if ($remaining_occurence > 0 || $recurring_time == 'infinite') {
                                if (!empty($next_due_date)) {
                                    $next_due_date = date_i18n($date_format, $next_due_date);
                                }
                            } else {
                                $next_due_date = '';
                            }

                            $arm_is_user_in_grace = $planData['arm_is_user_in_grace'];

                            $arm_grace_period_end = $planData['arm_grace_period_end'];
                        } else {
                            $recurring_profile = '-';
                            $arm_trial_start_date = '';
                            $remaining_occurence = '-';
                            $arm_is_user_in_grace = 0;
                            $arm_grace_period_end = '';
                            $arm_is_user_in_trial = 0;
                        }

                        $recurring_profile = $plan_info->new_user_plan_text(false, $payment_cycle);

                        $trial_active_text = '';
                        if (!empty($arm_trial_start_date)) {
                            if($arm_is_user_in_trial == 1 || $arm_is_user_in_trial == '1'){
                                if($arm_trial_start_date <  $start_plan){
                                    $trial_active_text = esc_html__('trial active', 'ARMember');
                                }
                            }
                        }

                        $end_date = '-';
                        if ($plan_info->is_free() || $plan_info->is_lifetime() || ($plan_info->is_recurring() && $recurring_time == 'infinite')) {
                            if($plan_info->is_recurring() && $recurring_time == 'infinite' && (isset($planData['arm_cencelled_plan']) && $planData['arm_cencelled_plan']=='yes')) 
                            {
                                $end_date = date_i18n($date_format, $planData['arm_next_due_payment']);
                            } else {
                                $end_date = esc_html__('Never Expires', 'ARMember');    
                            }
                        } else {
                            if (isset($plan_options['access_type']) && !in_array($plan_options['access_type'], array('infinite', 'lifetime'))) {
                                if (!empty($expire_plan)) {
                                    $membership_expire_content = date_i18n($date_format, $expire_plan);
                                    $end_date = $membership_expire_content;
                                }
                            }
                        }

                        $trial_period = '-';
                        if (!empty($arm_trial_start_date)) {
                            $trial_period = date_i18n($date_format, $arm_trial_start_date);
                            $trial_period .= " " . esc_html__('To', 'ARMember');
                            $trial_period .= " " . date_i18n($date_format, strtotime('-1 day', $start_plan));
                        }

                        $renew_date = $next_due_date;
                        $next_cycle_due = '';
                        if($plan_info->is_recurring()){
                            if(!empty($expire_plan)){
                                if($remaining_occurence == 0){
                                    $next_cycle_due = esc_html__('No cycles due', 'ARMember');
                                }
                                else{
                                    $next_cycle_due = "<br/>(". $remaining_occurence." ".esc_html__('cycles due', 'ARMember').")";
                                }
                            }
                            if($arm_is_user_in_grace == "1" || $arm_is_user_in_grace == 1){
                                $arm_grace_period_end = date_i18n($date_format, $arm_grace_period_end );
                            }
                        }

                        $membership['sr_no'] = $sr_no;
                        $membership['plan_id'] = $user_plan;
                        $membership['name'] = stripslashes($plan_info->name);
                        $membership['is_suspended'] = !empty($arm_plan_is_suspended) ? 1 : 0;
                        $membership['is_suspended_text'] = $arm_plan_is_suspended;
                        $membership['change_plan'] = $change_plan;
                        $membership['is_plan_cancelled'] = $is_plan_cancelled;
                        $membership['payment_mode'] = $payment_mode;
                        $membership['user_payment_mode'] = $user_payment_mode;
                        $membership['recurring_time'] = $recurring_time;
                        $membership['recurring_profile'] = strip_tags($recurring_profile);
                        $membership['recurring_profile_html'] = $recurring_profile;
                        $membership['start_date'] = !empty($start_plan) ? date_i18n($date_format, $start_plan): '';
                        $membership['is_trial'] = !empty($trial_active_text) ? 1 : 0;
                        $membership['is_trial_text'] = $trial_active_text;
                        $membership['arm_trial_start_date'] = $arm_trial_start_date;
                        $membership['end_date'] = $end_date;
                        $membership['trial_period'] = $trial_period;
                        $membership['remaining_occurence'] = $remaining_occurence;
                        $membership['renew_date'] = $renew_date;
                        $membership['next_cycle_due'] = $next_cycle_due;
                        $membership['grace_period_end'] = $arm_grace_period_end;
                        $memberships_array[] = $membership;
                    }
                }
                if(!empty($change_plan_to_array)){
                    foreach ($change_plan_to_array as $change_user_plan => $effective_from_date) {
                        if(!empty($change_user_plan) && !empty($effective_from_date)){
                            $change_plan_info = new ARM_Plan($change_user_plan);
                            if ($change_plan_info->exists()) {
                                $sr_no++;
                                $membership['sr_no'] = $sr_no;
                                $membership['plan_id'] = $change_user_plan;
                                $membership['name'] = stripslashes($change_plan_info->name);
                                $membership['is_suspended'] = 0;
                                $membership['is_suspended_text'] = '';
                                $membership['change_plan'] = '';
                                $membership['is_plan_cancelled'] = '';
                                $membership['payment_mode'] = '';
                                $membership['user_payment_mode'] = '';
                                $membership['recurring_time'] = '';
                                $membership['recurring_profile'] = strip_tags($change_plan_info->new_user_plan_text(false, ''));
                                $membership['recurring_profile_html'] = $change_plan_info->new_user_plan_text(false, '');
                                $membership['start_date'] = !empty($effective_from_date) ? date_i18n($date_format, $effective_from_date) : '';
                                $membership['is_trial'] = 0;
                                $membership['is_trial_text'] = '';
                                $membership['arm_trial_start_date'] = '';
                                $membership['end_date'] = '';
                                $membership['trial_period'] = '';
                                $membership['remaining_occurence'] = '';
                                $membership['renew_date'] = '';
                                $membership['next_cycle_due'] = '';
                                $membership['grace_period_end'] = '';
                                $memberships_array[] = $membership;
                            }
                        }
                    }
                }
            }
            $reponse['memberships'] = $memberships_array;
            $reponse['total'] = $membership_count;
            return $reponse;
        }
        function arm_member_payments($user_id, $is_paid_post, $arm_page, $arm_perpage)
        {
            global $arm_global_settings, $arm_transaction, $arm_payment_gateways, $arm_subscription_plans;
            $arm_invoice_tax_feature = get_option('arm_is_invoice_tax_feature', 0);
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $date_time_format = $arm_global_settings->arm_get_wp_date_time_format();
            $offset = (!empty($arm_page) && $arm_page > 1) ? (($arm_page - 1) * $arm_perpage) : 0;
            $trans_count = $arm_transaction->arm_get_total_transaction($user_id, $is_paid_post);
            $transactions = $arm_transaction->arm_get_all_transaction($user_id, $offset, $arm_perpage, $is_paid_post);
            $general_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
            $arm_currency_decimal = isset($general_settings['arm_currency_decimal_digit']) ? $general_settings['arm_currency_decimal_digit'] : 2;
            $payments_array = array();
            if (!empty($transactions)) {
                $global_currency = $arm_payment_gateways->arm_get_global_currency();
                $all_currencies = $arm_payment_gateways->arm_get_all_currencies();
                $global_currency_sym = isset($all_currencies) ? $all_currencies[strtoupper($global_currency)] : '';
                foreach ($transactions as $transaction) {
                    $transaction = (object) $transaction;
                    $currency = (!empty($transaction->arm_currency) && isset($all_currencies[strtoupper($transaction->arm_currency)])) ? $all_currencies[strtoupper($transaction->arm_currency)] : $global_currency_sym;
                    $arm_order_id = '';
                    $arm_log_id = $transaction->arm_log_id;
                    if (!empty($transaction->arm_transaction_id)) {
                        $arm_transaction_id = $transaction->arm_transaction_id;
                        $arm_token_transaction = $arm_transaction->arm_get_single_transaction($transaction->arm_log_id);
                        if(!empty($arm_token_transaction))
                        {
                            if($arm_token_transaction['arm_payment_mode']=='auto_debit_subscription' && $transaction->arm_payment_gateway=='2checkout')
                            {
                                $arm_order_id = $arm_token_transaction['arm_token'];
                            }
                        }
                    } else {
                        $arm_transaction_id = esc_html__('Manual', 'ARMember');
                    }
                    $arm_invoice_id = $arm_global_settings->arm_manipulate_invoice_id(((!empty($transaction->arm_invoice_id)) ? $transaction->arm_invoice_id : 0));
                    $arm_plan = $arm_subscription_plans->arm_get_plan_name_by_id($transaction->arm_plan_id);
                    $arm_payment_gateway = $arm_payment_gateways->arm_gateway_name_by_key($transaction->arm_payment_gateway);

                    $payment_type = (isset($transaction->arm_payment_type) && $transaction->arm_payment_type == 'subscription') ? esc_html__('Subscription', 'ARMember') : esc_html__('One Time', 'ARMember');
                    $arm_is_trial = (isset($transaction->arm_is_trial) && $transaction->arm_is_trial == 1) ? esc_html__('(Trial Transaction)', 'ARMember') : '';

                    $arm_transaction_status = $transaction->arm_transaction_status;
                    switch ($arm_transaction_status) {
                        case '0':
                            $arm_transaction_status = 'pending';
                        break;
                        case '1':
                            $arm_transaction_status = 'success';
                        break;
                        case '2':
                            $arm_transaction_status = 'canceled';
                        break;
                        default:
                            $arm_transaction_status = $transaction->arm_transaction_status;
                        break;
                    }
                    $arm_transaction_status_html = $arm_transaction->arm_get_transaction_status_text($arm_transaction_status);

                    $extraVars = (!empty($transaction->arm_extra_vars)) ? maybe_unserialize($transaction->arm_extra_vars) : array();
                    $arm_plan_amount = '';
                    if (!empty($extraVars) && !empty($extraVars['plan_amount']) && $extraVars['plan_amount'] != 0 && $extraVars['plan_amount'] != $transaction->arm_amount) {
                        $arm_plan_amount = $arm_payment_gateways->arm_prepare_amount($transaction->arm_currency, $extraVars['plan_amount']);
                    }
                    if (!empty($transaction->arm_amount) && $transaction->arm_amount > 0) {
                        $arm_amount = $arm_payment_gateways->arm_prepare_amount($transaction->arm_currency, $transaction->arm_amount);
                        if ($global_currency_sym == $currency && strtoupper($global_currency) != strtoupper($transaction->arm_currency)) {
                            $arm_amount .= ' '.strtoupper($transaction->arm_currency);
                        }
                    } else {
                        $arm_amount = $arm_payment_gateways->arm_prepare_amount($transaction->arm_currency, $transaction->arm_amount);
                    }
                    $arm_trial_text = '';
                    if (!empty($extraVars) && isset($extraVars['trial'])) {
                        $trialInterval = $extraVars['trial']['interval'];
                        $arm_trial_text .= esc_html__('Trial Period', 'ARMember') . ": {$trialInterval} ";
                        if ($extraVars['trial']['period'] == 'Y') {
                            $arm_trial_text .= ($trialInterval > 1) ? esc_html__('Years', 'ARMember') : esc_html__('Year', 'ARMember');
                        } elseif ($extraVars['trial']['period'] == 'M') {
                            $arm_trial_text .= ($trialInterval > 1) ? esc_html__('Months', 'ARMember') : esc_html__('Month', 'ARMember');
                        } elseif ($extraVars['trial']['period'] == 'W') {
                            $arm_trial_text .= ($trialInterval > 1) ? esc_html__('Weeks', 'ARMember') : esc_html__('Week', 'ARMember');
                        } elseif ($extraVars['trial']['period'] == 'D') {
                            $arm_trial_text .= ($trialInterval > 1) ? esc_html__('Days', 'ARMember') : esc_html__('Day', 'ARMember');
                        }
                    }

                    $arm_coupon_code = '';
                    if (!empty($transaction->arm_coupon_code)) {
                        $arm_coupon_code = $transaction->arm_coupon_code;
                    }

                    $arm_coupon_discount_type = '';
                    if (!empty($transaction->arm_coupon_code)) {
                        if (!empty($transaction->arm_coupon_discount) && $transaction->arm_coupon_discount > 0) {
                            $arm_coupon_discount_type = number_format((float) $transaction->arm_coupon_discount, $arm_currency_decimal);
                            $discount_type = ($transaction->arm_coupon_discount_type != "percentage") ? ' ' . $transaction->arm_coupon_discount_type : '%';
                            $arm_coupon_discount_type .= $discount_type;
                        } else {
                            $arm_coupon_discount_type = '0.00';
                        }
                    }

                    $payment_date = date_i18n($date_time_format, strtotime($transaction->arm_created_date));

                    $tax_percentage = '';
                    if (!empty($extraVars) && isset($extraVars['tax_percentage']) && !empty($extraVars['tax_percentage'])) {
                        $arm_tax_percent = number_format((float)$extraVars['tax_percentage'], $arm_currency_decimal);
                        $tax_percentage = $arm_tax_percent . "%";
                    }

                    $tax_amount = '';
                    if (!empty($extraVars) && isset($extraVars['tax_amount']) && $extraVars['tax_amount'] != "" ) {
                        $tax_amount = $arm_payment_gateways->arm_prepare_amount($transaction->arm_currency, $extraVars['tax_amount']);
                    }
                    
                    $payment['arm_log_id'] = $arm_log_id;
                    $payment['arm_transaction_id'] = $arm_transaction_id;
                    $payment['arm_2checkout_order_id'] = $arm_order_id;
                    $payment['arm_invoice_id'] = $arm_invoice_id;
                    $payment['arm_plan'] = $arm_plan;
                    $payment['arm_plan_id'] = $transaction->arm_plan_id;
                    $payment['arm_payment_gateway'] = $arm_payment_gateway;
                    $payment['arm_payment_type'] = $payment_type;
                    $payment['arm_is_trial'] = $arm_is_trial;
                    $payment['arm_payment_status'] = $arm_transaction_status;
                    $payment['arm_payment_status_text'] = strip_tags($arm_transaction_status_html);
                    $payment['arm_payment_status_html'] = $arm_transaction_status_html;
                    $payment['arm_plan_amount'] = $arm_plan_amount;
                    $payment['arm_paid_amount'] = $arm_amount;
                    $payment['arm_trial_text'] = $arm_trial_text;
                    $payment['arm_coupon_code'] = $arm_coupon_code;
                    $payment['arm_coupon_discount'] = $arm_coupon_discount_type;
                    $payment['arm_payment_date'] = $payment_date;
                    $payment['arm_tax_percentage'] = $tax_percentage;
                    $payment['arm_tax_amount'] = $tax_amount;
                    $payments_array[] = $payment;
                }
            }
            $reponse['payments'] = $payments_array;
            $reponse['total'] = $trans_count;
            return $reponse;
        }

        function arm_update_subscription_plan_data( $check, $user_id, $user_meta_key, $user_meta_value ) 
        {
             if ('arm_user_plan_ids'==$user_meta_key && !empty($user_id)) 
             {
                global $ARMember, $wpdb;

                $user_meta_value_array = array();
                if(!empty($user_meta_value))
                {
                    $user_meta_value_arr = maybe_unserialize($user_meta_value);
                    if(!empty($user_meta_value_arr) && is_array($user_meta_value_arr))
                    {
                        foreach($user_meta_value_arr as $user_meta_value)
                        {
                            $user_meta_value_array[] = (int)$user_meta_value;
                        }
                    }
                }
                $user_meta_value_array = maybe_serialize($user_meta_value_array);
                $wpdb->update($ARMember->tbl_arm_members, array('arm_user_plan_ids' => $user_meta_value_array), array('arm_user_id' => $user_id));
            }
            else if ('arm_user_suspended_plan_ids'==$user_meta_key && !empty($user_id)) 
             {
                global $ARMember, $wpdb;

                $user_meta_value_array = array();
                if(!empty($user_meta_value))
                {
                    $user_meta_value_arr = maybe_unserialize($user_meta_value);
                    if(!empty($user_meta_value_arr) && is_array($user_meta_value_arr))
                    {
                        foreach($user_meta_value_arr as $user_meta_value)
                        {
                            $user_meta_value_array[] = (int)$user_meta_value;
                        }
                    }
                }
                $user_meta_value_array = maybe_serialize($user_meta_value_array);
                $wpdb->update($ARMember->tbl_arm_members, array('arm_user_suspended_plan_ids' => $user_meta_value_array), array('arm_user_id' => $user_id));
            }
            return $check;
         }

         function arm_delete_subscription_plan_data($check, $user_id, $user_meta_key, $meta_value, $delete_all) 
         {
            if ('arm_user_plan_ids'==$user_meta_key && !empty($user_id)) 
            {
                global $ARMember, $wpdb;
                $wpdb->update($ARMember->tbl_arm_members, array('arm_user_plan_ids' => ''), array('arm_user_id' => $user_id));
            }
            else if ('arm_user_suspended_plan_ids'==$user_meta_key && !empty($user_id)) 
            {
                global $ARMember, $wpdb;
                $wpdb->update($ARMember->tbl_arm_members, array('arm_user_suspended_plan_ids' => ''), array('arm_user_id' => $user_id));
            }
            return $check;
        }

        public function get_member_current_subscription_plans($member_id=0)
        {
            if($member_id==0)
            {
                $member_id = get_current_user_id();
            }

            $get_user_plans = get_user_meta($member_id, 'arm_user_plan_ids', true);
            $get_user_plans = !empty($get_user_plans) ? $get_user_plans : array();

            $get_user_suspended_plans = get_user_meta($member_id, 'arm_user_suspended_plan_ids', true);
            $get_user_suspended_plans = !empty($get_user_suspended_plans) ? $get_user_suspended_plans : array();

            if( !empty($get_user_suspended_plans) && is_array($get_user_suspended_plans) )
            {
                foreach( $get_user_suspended_plans as  $get_user_suspended_plan_key => $get_user_suspended_plan_id)
                {
                    if (in_array($get_user_suspended_plan_id, $get_user_plans)) 
                    {
                        unset( $get_user_plans[$get_user_suspended_plan_key] );
                    }
                }
            }

            return $get_user_plans;

        }

        public function get_member_current_subscription_plan_details( $user_id = 0, $plan_id = 0 ) //get_user_meta replaces for the arm_user_plan_{plan_id}
        {
            if( empty($user_id) )
            {
                $user_id = get_current_user_id();
            }
            
            if( empty($plan_id) )
            {
                $get_user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $get_user_plans = !empty($get_user_plans) ? $get_user_plans : array();
            }
            else
            {
                $get_user_plans = array($plan_id);
            }

            $get_user_suspended_plans = get_user_meta( $user_id, 'arm_user_suspended_plan_ids', true );
            $get_user_suspended_plans = !empty($get_user_suspended_plans) ? $get_user_suspended_plans : array();

            if( !empty($get_user_suspended_plans) && is_array($get_user_suspended_plans) )
            {
                foreach( $get_user_suspended_plans as  $get_user_suspended_plan_key => $get_user_suspended_plan_id)
                {
                    if (in_array($get_user_suspended_plan_id, $get_user_plans)) 
                    {
                        unset( $get_user_plans[$get_user_suspended_plan_key] );
                    }
                }
            }

            $user_meta_arr = array();
            if( !empty($get_user_plans) && is_array($get_user_plans) )
            {
                foreach( $get_user_plans as $get_user_plan_key => $get_user_plan_id )
                {
                    $user_meta_arr[$get_user_plan_id] = get_user_meta($user_id, 'arm_user_plan_'.$get_user_plan_id, true);
                }
            }

            return $user_meta_arr;
        }

    }

}
global $arm_subscription_plans;
$arm_subscription_plans = new ARM_subscription_plans();

if (!class_exists('ARM_Plan')) {

    class ARM_Plan {

        var $ID;
        var $name;
        var $type;
        var $status;
        var $amount;
        var $level;
        var $options;
        var $arm_subscription_plan_options;
        var $payment_type;
        var $plan_role;
        var $recurring_data;
        var $description;
        var $plan_text;
        var $plan_price;
        var $plan_price_text;
        var $enable_upgrade_downgrade_action;
        var $upgrade_action;
        var $upgrade_plans;
        var $downgrade_action;
        var $downgrade_plans;
        var $is_delete;
        var $plan_detail;
        var $isPaidPost;
        var $isGiftPlan;

        public function __construct($id = 0) {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            if (is_numeric($id) && $id != 0) {
                $data = self::arm_get_plan_detail($id);
                if ($data) {
                    $this->init($data);
                }
            }
        }

        public function arm_get_plan_detail($plan_id = 0) {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            if (is_numeric($plan_id) && $plan_id != 0) {
                $plan = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_id`=%d LIMIT 1",$plan_id) );//phpcs:ignore --Reason $ARMember->tbl_arm_subscription_plans is a table name
                if (!empty($plan)) {
                    return $plan;
                }
            }
            return FALSE;
        }

        public function init($data) {
            global $arm_global_settings;
            $general_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
            $arm_currency_decimal = isset($general_settings['arm_currency_decimal_digit']) ? $general_settings['arm_currency_decimal_digit'] : 2;
            $this->ID = (isset($data->arm_subscription_plan_id)) ? $data->arm_subscription_plan_id : 0;
            $this->name = (isset($data->arm_subscription_plan_name)) ? stripslashes($data->arm_subscription_plan_name) : '';
            $this->type = (isset($data->arm_subscription_plan_type)) ? $data->arm_subscription_plan_type : 'free';
            $this->status = (isset($data->arm_subscription_plan_status)) ? $data->arm_subscription_plan_status : 1;
            $this->amount = (isset($data->arm_subscription_plan_amount)) ? number_format((float)$data->arm_subscription_plan_amount, $arm_currency_decimal, '.', '') : 0;
            
            $this->amount = apply_filters('arm_modify_amount_for_default_membership_plan_data',  $this->amount, $data);
            
            $this->options = (isset($data->arm_subscription_plan_options)) ? maybe_unserialize($data->arm_subscription_plan_options) : array();
            $this->arm_subscription_plan_options = (isset($data->arm_subscription_plan_options)) ? maybe_unserialize($data->arm_subscription_plan_options) : array();
            $this->payment_type = (isset($this->options['payment_type'])) ? $this->options['payment_type'] : '';
            $this->plan_role = (isset($data->arm_subscription_plan_role)) ? $data->arm_subscription_plan_role : '';
            $this->recurring_data = $this->prepare_recurring_data();
            $this->description = (isset($data->arm_subscription_plan_description)) ? stripslashes($data->arm_subscription_plan_description) : '';
            $this->plan_text = $this->plan_text();
            $this->plan_price = $this->plan_price();
            $this->plan_price_text = $this->plan_price_text();
            $this->enable_upgrade_downgrade_action = (isset($this->options['enable_upgrade_downgrade_action']) && $this->options['enable_upgrade_downgrade_action'] == 1) ? 1 : 0;
            $this->upgrade_action = (isset($this->options['upgrade_action'])) ? $this->options['upgrade_action'] : 'immediate';
            $this->upgrade_plans = (isset($this->options['upgrade_plans'])) ? $this->options['upgrade_plans'] : array();
            $this->downgrade_action = (isset($this->options['downgrade_action'])) ? $this->options['downgrade_action'] : 'immediate';
            $this->downgrade_plans = (isset($this->options['downgrade_plans'])) ? $this->options['downgrade_plans'] : array();
            $this->is_delete = (isset($this->arm_subscription_plan_is_delete)) ? $this->arm_subscription_plan_is_delete : 0;
            $this->plan_detail = $data;
            $this->isPaidPost = isset( $data->arm_subscription_plan_post_id ) ? $data->arm_subscription_plan_post_id : 0;
            $this->isGiftPlan = !empty( $data->arm_subscription_plan_gift_status ) ? $data->arm_subscription_plan_gift_status : 0;
        }

        /**
         * Check whether plan exist or not.
         */
        public function exists() {
            return !empty($this->ID);
        }

        /**
         * Check whether plan exist or not.
         */
        public function is_active() {
            return (isset($this->status) && $this->status == '1' && isset($this->is_delete) && $this->is_delete == '0');
        }

        /**
         * Check whether plan exist or not.
         */
        public function is_deleted() {
            return (isset($this->is_delete) && $this->is_delete == '1');
        }

        /**
         * Check whether plan exist or not.
         */
        public function is_lifetime() {
            return (isset($this->options['access_type']) && $this->options['access_type'] == 'lifetime');
        }

        /**
         * Check plan is recurring or single time payment plan
         */
        public function is_recurring() {
            return (!$this->is_lifetime() && $this->payment_type == 'subscription');
        }

        /**
         * Check plan has trial period or not.
         */
        public function has_trial_period() {
            $trialOptions = isset($this->options['trial']) ? $this->options['trial'] : array();
            if ($this->is_recurring() && isset($trialOptions['is_trial_period']) && $trialOptions['is_trial_period'] == 1) {
                return true;
            }
            return false;
        }

        /**
         * Check plan is free or not
         */
        public function is_free() {
            return ($this->type == 'free');
        }

        /**
         * Check plan is paid or not
         */
        public function is_paid() {
            return ($this->type == 'paid_infinite' || $this->type == 'paid_finite' || $this->type == 'recurring');
        }

        /**
         * Check plan is supported in Authorize.Net
         */
        public function is_support_authorize_net($payment_cycle = 0) {
            $auth_allow = true;
            if ($this->is_recurring()) {
                $auth_allow = false;

                $plan_options = $this->options;
                if (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) {
                    $arm_user_payment_cycle = $plan_options['payment_cycles'][$payment_cycle];
                    $opt_recurring = array();
                    $opt_recurring['type'] = !empty($arm_user_payment_cycle['billing_type']) ? $arm_user_payment_cycle['billing_type'] : 'M';
                    $billing_cycle = !empty($arm_user_payment_cycle['billing_cycle']) ? $arm_user_payment_cycle['billing_cycle'] : '1';
                    switch ($opt_recurring['type']) {
                        case 'D':
                            $opt_recurring['days'] = $billing_cycle;
                            break;
                        case 'M':
                            $opt_recurring['months'] = $billing_cycle;
                            break;
                        case 'Y':
                            $opt_recurring['years'] = $billing_cycle;
                            break;
                        default:
                            $opt_recurring['days'] = $billing_cycle;
                            break;
                    }
                } else {
                    $opt_recurring = $this->options['recurring'];
                }
                switch ($opt_recurring['type']) {
                    case 'D':
                        if ($opt_recurring['days'] >= 7) {
                            $auth_allow = true;
                        }
                        break;
                    case 'M':
                        if ($opt_recurring['months'] <= 12) {
                            $auth_allow = true;
                        }
                        break;
                    default:
                        break;
                }
            }
            return $auth_allow;
        }

        /**
         * Check plan is supported in 2Checkout
         */
        public function is_support_2checkout($payment_cycle = 0, $plan_action = 'new_subscription') {

            $twoco_allow = true;
            if ($this->is_recurring()) {

                $plan_options = $this->options;
                if (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) {
                    $arm_user_payment_cycle = $plan_options['payment_cycles'][$payment_cycle];
                    $opt_recurring = array();
                    $opt_recurring['type'] = !empty($arm_user_payment_cycle['billing_type']) ? $arm_user_payment_cycle['billing_type'] : 'M';
                    $billing_cycle = !empty($arm_user_payment_cycle['billing_cycle']) ? $arm_user_payment_cycle['billing_cycle'] : '1';
                    switch ($opt_recurring['type']) {
                        case 'D':
                            $opt_recurring['days'] = $billing_cycle;
                            break;
                        case 'M':
                            $opt_recurring['months'] = $billing_cycle;
                            break;
                        case 'Y':
                            $opt_recurring['years'] = $billing_cycle;
                            break;
                        default:
                            $opt_recurring['days'] = $billing_cycle;
                            break;
                    }
                } else {
                    $opt_recurring = $this->options['recurring'];
                }

                if ($opt_recurring['type'] == 'D') {

                    $twoco_allow = false;
                }

                $recurring_data = $this->prepare_recurring_data($payment_cycle);
                $amount = $recurring_data['amount'];

                if ($this->has_trial_period() && $plan_action == 'new_subscription') {
                    $opt_trial = $this->options['trial'];
                    if ($opt_trial['amount'] == 0 || $opt_trial['amount'] == $amount || $opt_trial['amount'] > $amount) {
                        $twoco_allow = false;
                    }
                }
            }

            return $twoco_allow;
        }

        /**
         * Check plan is supported in Stripe
         */
        public function is_support_stripe_old() {

            $stripe_allow = true;
            if ($this->is_recurring()) {
                $stripe_allow = false;
                $opt_recurring = $this->options['recurring'];
                switch ($opt_recurring['type']) {
                    case 'D':
                        if ($opt_recurring['days'] <= 365) {
                            $stripe_allow = true;
                        }
                        break;
                    case 'M':
                        if ($opt_recurring['months'] <= 12) {
                            $stripe_allow = true;
                        }
                        break;
                    case 'Y':
                        if ($opt_recurring['years'] == 1) {
                            $stripe_allow = true;
                        }
                        break;
                    default:
                        break;
                }
                if ($this->has_trial_period()) {
                    $opt_trial = $this->options['trial'];
                    if ($opt_trial['amount']) {
                        switch ($opt_trial['type']) {
                            case 'D':
                                if ($opt_trial['days'] <= 730) {
                                    $stripe_allow = true;
                                }
                                break;
                            case 'M':

                                $stripe_allow = false;

                                break;
                            case 'Y':

                                $stripe_allow = false;

                                break;
                            default:
                                break;
                        }
                    }
                }
            } else if ($this->is_recurring() && !$this->has_trial_period()) {
                $stripe_allow = true;
                $opt_recurring = $this->options['recurring'];
                switch ($opt_recurring['type']) {
                    case 'D':
                        $stripe_allow = false;
                        break;
                    case 'M':
                        $stripe_allow = true;
                        break;
                    case 'Y':
                        $stripe_allow = true;
                        break;
                    default:
                        break;
                }
            }
            return $stripe_allow;
        }

        public function is_support_stripe($payment_cycle = 0) {

            $stripe_allow = true;
            if ($this->is_recurring()) {
                $stripe_allow = false;

                $plan_options = $this->options;
                if (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) {
                    $arm_user_payment_cycle = $plan_options['payment_cycles'][$payment_cycle];
                    $opt_recurring = array();
                    $opt_recurring['type'] = !empty($arm_user_payment_cycle['billing_type']) ? $arm_user_payment_cycle['billing_type'] : 'M';
                    $billing_cycle = !empty($arm_user_payment_cycle['billing_cycle']) ? $arm_user_payment_cycle['billing_cycle'] : '1';
                    switch ($opt_recurring['type']) {
                        case 'D':
                            $opt_recurring['days'] = $billing_cycle;
                            break;
                        case 'M':
                            $opt_recurring['months'] = $billing_cycle;
                            break;
                        case 'Y':
                            $opt_recurring['years'] = $billing_cycle;
                            break;
                        default:
                            $opt_recurring['days'] = $billing_cycle;
                            break;
                    }
                } else {
                    $opt_recurring = $this->options['recurring'];
                }

                switch ($opt_recurring['type']) {
                    case 'D':
                        if ($opt_recurring['days'] <= 365) {
                            $stripe_allow = true;
                        }
                        break;
                    case 'M':
                        if ($opt_recurring['months'] <= 12) {
                            $stripe_allow = true;
                        }
                        break;
                    case 'Y':
                        if ($opt_recurring['years'] == 1) {
                            $stripe_allow = true;
                        }
                        break;
                    default:
                        break;
                }
                if ($this->has_trial_period() && $stripe_allow == true) {

                    $opt_trial = $this->options['trial'];
                    
                    switch ($opt_trial['type']) {
                        case 'D':
                            if ($opt_trial['days'] <= 730) {
                                $stripe_allow = true;
                            }
                            break;
                        case 'M':
                            $stripe_allow = false;
                            break;
                        case 'Y':
                            $stripe_allow = false;
                            break;
                        default:
                            break;
                    }
                }
            }
            return $stripe_allow;
        }

        public function is_support_stripe_without_trial($payment_cycle = 0) {

            $stripe_allow = true;
            if ($this->is_recurring()) {
                $stripe_allow = false;

                $plan_options = $this->options;
                if (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) {
                    $arm_user_payment_cycle = $plan_options['payment_cycles'][$payment_cycle];
                    $opt_recurring = array();
                    $opt_recurring['type'] = !empty($arm_user_payment_cycle['billing_type']) ? $arm_user_payment_cycle['billing_type'] : 'M';
                    $billing_cycle = !empty($arm_user_payment_cycle['billing_cycle']) ? $arm_user_payment_cycle['billing_cycle'] : '1';
                    switch ($opt_recurring['type']) {
                        case 'D':
                            $opt_recurring['days'] = $billing_cycle;
                            break;
                        case 'M':
                            $opt_recurring['months'] = $billing_cycle;
                            break;
                        case 'Y':
                            $opt_recurring['years'] = $billing_cycle;
                            break;
                        default:
                            $opt_recurring['days'] = $billing_cycle;
                            break;
                    }
                } else {
                    $opt_recurring = $this->options['recurring'];
                }


                switch ($opt_recurring['type']) {
                    case 'D':
                        if ($opt_recurring['days'] <= 365) {
                            $stripe_allow = true;
                        }
                        break;
                    case 'M':
                        if ($opt_recurring['months'] <= 12) {
                            $stripe_allow = true;
                        }
                        break;
                    case 'Y':
                        if ($opt_recurring['years'] == 1) {
                            $stripe_allow = true;
                        }
                        break;
                    default:
                        break;
                }
            }
            return $stripe_allow;
        }

        /**
         * Prepare Reccuring Data Array
         */
        public function prepare_recurring_data($arm_user_selected_payment_cycle = 0,$currency='') {
            global $ARMember;
            $dataArray = array();
            if ($this->is_recurring()) {

                if ($arm_user_selected_payment_cycle === '') {
                    $dataArray['amount'] = !empty($this->amount) ? $this->amount : 0;
                    $opt_recurring = $this->options['recurring'];
                    $dataArray['period'] = !empty($opt_recurring['type']) ? $opt_recurring['type'] : 'M';
                    switch ($dataArray['period']) {
                        case 'D':
                            $dataArray['interval'] = !empty($opt_recurring['days']) ? $opt_recurring['days'] : '1';
                            break;
                        case 'W':
                            $dataArray['interval'] = !empty($opt_recurring['weeks']) ? $opt_recurring['weeks'] : '1';
                            break;
                        case 'M':
                            $dataArray['interval'] = !empty($opt_recurring['months']) ? $opt_recurring['months'] : '1';
                            break;
                        case 'Y':
                            $dataArray['interval'] = !empty($opt_recurring['years']) ? $opt_recurring['years'] : '1';
                            break;
                        default:
                            $dataArray['interval'] = 1;
                            break;
                    }
                    $dataArray['cycles'] = (!empty($opt_recurring['time']) && $opt_recurring['time'] != 'infinite') ? $opt_recurring['time'] : '';
                    $dataArray['rec_time'] = $opt_recurring['time'];
                } else {
                    if (isset($this->options['payment_cycles']) && !empty($this->options['payment_cycles'])) {
                        $opt_recurring = !empty($this->options['payment_cycles'][$arm_user_selected_payment_cycle]) ? $this->options['payment_cycles'][$arm_user_selected_payment_cycle] : array();
                        $dataArray['cycle_label'] = !empty($opt_recurring['cycle_label']) ? $opt_recurring['cycle_label'] : 0;
                        $dataArray['amount'] = !empty($opt_recurring['cycle_amount']) ? $opt_recurring['cycle_amount'] : 0;
                        $dataArray['period'] = !empty($opt_recurring['billing_type']) ? $opt_recurring['billing_type'] : 'M';
                        $dataArray['interval'] = !empty($opt_recurring['billing_cycle']) ? $opt_recurring['billing_cycle'] : '1';
                        $dataArray['cycles'] = (!empty($opt_recurring['recurring_time']) && $opt_recurring['recurring_time'] != 'infinite') ? $opt_recurring['recurring_time'] : '';
                        $dataArray['rec_time'] = !empty($opt_recurring['recurring_time']) ? $opt_recurring['recurring_time'] : '';

                        $dataArray = apply_filters('arm_modify_prepare_recurring_data', $dataArray, $opt_recurring);
                        
                    } else {
                        $dataArray['amount'] = !empty($this->amount) ? $this->amount : 0;
                        $opt_recurring = $this->options['recurring'];
                        $dataArray['period'] = !empty($opt_recurring['type']) ? $opt_recurring['type'] : 'M';
                        switch ($dataArray['period']) {
                            case 'D':
                                $dataArray['interval'] = !empty($opt_recurring['days']) ? $opt_recurring['days'] : '1';
                                break;
                            case 'W':
                                $dataArray['interval'] = !empty($opt_recurring['weeks']) ? $opt_recurring['weeks'] : '1';
                                break;
                            case 'M':
                                $dataArray['interval'] = !empty($opt_recurring['months']) ? $opt_recurring['months'] : '1';
                                break;
                            case 'Y':
                                $dataArray['interval'] = !empty($opt_recurring['years']) ? $opt_recurring['years'] : '1';
                                break;
                            default:
                                $dataArray['interval'] = 1;
                                break;
                        }
                        $dataArray['cycles'] = (!empty($opt_recurring['time']) && $opt_recurring['time'] != 'infinite') ? $opt_recurring['time'] : '';
                        $dataArray['rec_time'] = $opt_recurring['time'];
                    }
                }

                $dataArray['manual_billing_start'] = isset($this->options['recurring']['manual_billing_start']) ? $this->options['recurring']['manual_billing_start'] : 'transaction_day';
                //Trial Period Options
                $opt_trial = isset($this->options['trial']) ? $this->options['trial'] : array();
                if (isset($opt_trial['is_trial_period']) && $opt_trial['is_trial_period'] == 1) {
                    $dataArray['trial']['amount'] = !empty($opt_trial['amount']) ? $opt_trial['amount'] : 0;
                    $dataArray['trial']['period'] = !empty($opt_trial['type']) ? $opt_trial['type'] : 'M';
                    switch ($opt_trial['type']) {
                        case 'D':
                            $dataArray['trial']['interval'] = !empty($opt_trial['days']) ? $opt_trial['days'] : '1';
                            $dataArray['trial']['type'] = 'Day';
                            break;
                        case 'W':
                            $dataArray['trial']['interval'] = !empty($opt_trial['weeks']) ? $opt_trial['weeks'] : '1';
                            $dataArray['trial']['type'] = 'Week';
                            break;
                        case 'M':
                            $dataArray['trial']['interval'] = !empty($opt_trial['months']) ? $opt_trial['months'] : '1';
                            $dataArray['trial']['type'] = 'Month';
                            break;
                        case 'Y':
                            $dataArray['trial']['interval'] = !empty($opt_trial['years']) ? $opt_trial['years'] : '1';
                            $dataArray['trial']['type'] = 'Year';
                            break;
                        default:
                            $dataArray['trial']['interval'] = 1;
                            $dataArray['trial']['type'] = 'Month';
                            break;
                    }
                }
            }
            return $dataArray;
        }

        /**
         * Get subscription plan expire time
         * @param type $start_time
         * @return expire time
         */
        function arm_plan_expire_time($start_time = '', $payment_mode = 'manual_subscription', $payment_cycle = 0) {

            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $start_time = (!empty($start_time)) ? $start_time : strtotime(current_time('mysql'));
            $expire_time = false;
            if ($this->exists()) {
                $plan_options = $this->options;
                if (!empty($plan_options)) {



                    if ($this->is_recurring()) {
                        if (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) {

                            if ($payment_cycle === '') {
                                $payment_cycle = 0;
                            }
                            else if(is_array($payment_cycle))
				{
					$plan_id_payment_cycle = $this->ID;
					$payment_cycle = isset($payment_cycle['arm_plan_cycle_'.$plan_id_payment_cycle]) ? $payment_cycle['arm_plan_cycle_'.$plan_id_payment_cycle] : 0;
				}
                            $opt_recurring = !empty($plan_options['payment_cycles'][$payment_cycle]) ? $plan_options['payment_cycles'][$payment_cycle] : array();
                            $period_options = array();
                            $period_options['type'] = !empty($opt_recurring['billing_type']) ? $opt_recurring['billing_type'] : 'M';
                            $billing_cycle = !empty($opt_recurring['billing_cycle']) ? $opt_recurring['billing_cycle'] : '1';
                            switch ($period_options['type']) {
                                case 'D':
                                    $period_options['days'] = $billing_cycle;
                                    break;
                                case 'M':
                                    $period_options['months'] = $billing_cycle;
                                    break;
                                case 'Y':
                                    $period_options['years'] = $billing_cycle;
                                    break;
                                default:
                                    $period_options['days'] = $billing_cycle;
                                    break;
                            }
                            $period_options['time'] = (!empty($opt_recurring['recurring_time'])) ? $opt_recurring['recurring_time'] : 'infinite';
                        } else {
                            $period_options = $plan_options['recurring'];
                        }
                    }

                    if ($this->is_paid() && !$this->is_lifetime() && !($this->is_recurring() && $period_options['time'] == 'infinite')) {
                        $payment_type = $plan_options['payment_type'];
                        $num_of_recurring = 1;
                        $trial_option = array();

                        $intervalDate = '';
                        if ($payment_type == 'one_time') {
                            $period_options = $plan_options['eopa'];
                        } elseif ($payment_type == 'subscription') {


                            $trial_option = $plan_options['trial'];
                            //No Expiry date for infinite options.
                            if (isset($period_options['time']) && ($period_options['time'] == 'infinite' || $period_options['time'] < 2) && $payment_mode == 'auto_debit_subscription') {
                                return false;
                            }
                            //Add recurring time for number of recurring subscription
                            if (isset($period_options['time']) && ($period_options['time'] != 'infinite' || $period_options['time'] > 1)) {
                                $num_of_recurring = $period_options['time'];
                            }
                        } else {
                            $period_options = array('type' => 'D', 'months' => '0');
                        }
                        if (($this->is_recurring() && $payment_mode == 'auto_debit_subscription') || ($this->options['access_type'] == 'finite' && $payment_type == 'one_time')) {

                            $arm_subscription_plan_type = $this->type;
                            $expiry_type = (isset($this->options['expiry_type']) && $this->options['expiry_type'] != '') ? $this->options['expiry_type'] : 'joined_date_expiry';
                            if ($arm_subscription_plan_type == 'recurring' || ($arm_subscription_plan_type == 'paid_finite' && $expiry_type == 'joined_date_expiry')) {
                                switch ($period_options['type']) {
                                    case 'D':
                                        $num = (isset($period_options['days'])) ? ($period_options['days'] * $num_of_recurring) : $num_of_recurring;
                                        $intervalDate = "+$num day";
                                        break;
                                    case 'W':
                                        $num = (isset($period_options['weeks'])) ? ($period_options['weeks'] * $num_of_recurring) : ($num_of_recurring);
                                        $intervalDate = "+$num week";
                                        break;
                                    case 'M':
                                        $num = (isset($period_options['months'])) ? ($period_options['months'] * $num_of_recurring) : ($num_of_recurring);
                                        $intervalDate = "+$num month";
                                        break;
                                    case 'Y':
                                        $num = (isset($period_options['years'])) ? ($period_options['years'] * $num_of_recurring) : ($num_of_recurring);
                                        $intervalDate = "+$num year";
                                        break;
                                    default:
                                        $num = (isset($period_options['days'])) ? ($period_options['days'] * $num_of_recurring) : $num_of_recurring;
                                        $intervalDate = "+$num day";
                                        break;
                                }
                            } else {
                                return $expire_time = strtotime($this->options['expiry_date']);
                            }
                        } else if ($this->is_recurring() && $payment_mode == 'manual_subscription') {
                            $billing_start_day = $this->options['recurring']['manual_billing_start'];
                            $current_day = date('Y-m-d', $start_time);
                            if ($billing_start_day == 'transaction_day') {
                                $billing_type = $period_options['type'];
                                if ($billing_type == 'D') {
                                    $days = $period_options['days'] * $num_of_recurring;
                                    $intervalDate = date('Y-m-d', strtotime("$current_day+$days day"));
                                } else if ($billing_type == 'M') {
                                    $months = $period_options['months'] * $num_of_recurring;
                                    $intervalDate = date('Y-m-d', strtotime("$current_day+$months month"));
                                } else if ($billing_type == 'Y') {
                                    $years = $period_options['years'] * $num_of_recurring;
                                    $intervalDate = date('Y-m-d', strtotime("$current_day+$years year"));
                                }
                            } else {

                                $billing_type = $period_options['type'];
                                $days = isset($period_options['days']) ? $period_options['days'] : 0;
                                $months = isset($period_options['months']) ? $period_options['months'] : 0;
                                $years = isset($period_options['years']) ? $period_options['years'] : 0;
                                if ($billing_type == 'D') {
                                    $tdays = ($days > 0 ) ? ( $days * $num_of_recurring ) : $days;
                                    $intervalDate = date('Y-m-d', strtotime(date('Y-m-d', strtotime("$current_day+$tdays day"))));
                                }

                                if (date('d', strtotime($current_day)) < $billing_start_day) {

                                    if ($billing_type == 'M') {


                                        $tmonths = ($months > 0 ) ? ( $months * $num_of_recurring ) : $months;

                                        $intervalDate = date('Y-m-' . $billing_start_day, strtotime("$current_day+$tmonths month"));
                                    } else if ($billing_type == 'Y') {
                                        $tyears = ($years > 0) ? ( $years * $num_of_recurring ) : $years;

                                        $intervalDate = date('Y-m-' . $billing_start_day, strtotime("$current_day+$tyears year"));
                                    }
                                } else if (date('d', strtotime($current_day)) >= $billing_start_day) {

                                    $tdays = ($days > 0 ) ? ( $days * $num_of_recurring ) : $days;
                                    $tmonths = ($months > 0 ) ? ( $months * $num_of_recurring ) : $months;
                                    $tyears = ($years > 0) ? ( $years * $num_of_recurring ) : $years;

                                    if ($billing_type == 'M') {
                                        $intervalDate = date('Y-m-d', strtotime(date('Y-m-' . $billing_start_day, strtotime("$current_day+$tmonths month"))));
                                    } else if ($billing_type == 'Y') {
                                        $intervalDate = date('Y-m-d', strtotime(date('Y-m-' . $billing_start_day, strtotime("$current_day+$tyears year"))));
                                    }
                                }
                            }
                        }
                        $expire_time = strtotime($intervalDate, $start_time);
                    }
                }
            }
            return $expire_time;
        }

        function arm_plan_expire_time_for_renew_action($start_time = '', $mail_type = 'renew_subscription', $payment_cycle = 0) {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $start_time = (!empty($start_time)) ? $start_time : strtotime(current_time('mysql'));
            $expire_time = false;
            if ($this->exists()) {
                $plan_options = $this->options;
                if ($this->is_paid() && !$this->is_lifetime()) {
                    $num_of_recurring = 1;
                    $trial_option = array();
                    $payment_type = $plan_options['payment_type'];
                    $intervalDate = '';
                    if ($payment_type == 'one_time') {
                        $period_options = $plan_options['eopa'];
                    } elseif ($payment_type == 'subscription') {


                        if (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) {

                            if ($payment_cycle === '') {
                                $payment_cycle = 0;
                            }
                            $opt_recurring = $plan_options['payment_cycles'][$payment_cycle];
                            $period_options = array();
                            $period_options['type'] = !empty($opt_recurring['billing_type']) ? $opt_recurring['billing_type'] : 'M';
                            $billing_cycle = !empty($opt_recurring['billing_cycle']) ? $opt_recurring['billing_cycle'] : '1';
                            switch ($period_options['type']) {
                                case 'D':
                                    $period_options['days'] = $billing_cycle;
                                    break;
                                case 'M':
                                    $period_options['months'] = $billing_cycle;
                                    break;
                                case 'Y':
                                    $period_options['years'] = $billing_cycle;
                                    break;
                                default:
                                    $period_options['days'] = $billing_cycle;
                                    break;
                            }
                            $period_options['time'] = (!empty($opt_recurring['recurring_time'])) ? $opt_recurring['recurring_time'] : 'infinite';
                        } else {
                            $period_options = $plan_options['recurring'];
                        }

                        $trial_option = $plan_options['trial']; //No Expiry date for infinite options.
                        if (isset($period_options['time']) && ($period_options['time'] == 'infinite' || $period_options['time'] < 2)) {
                            return false;
                        }//Add recurring time for number of recurring subscription
                        if (isset($period_options['time']) && ($period_options['time'] != 'infinite' || $period_options['time'] > 1)) {
                            $num_of_recurring = $period_options['time'];
                        }
                    } else {
                        $period_options = array('type' => 'D', 'months' => '0');
                    }
                    switch ($period_options['type']) {
                        case 'D':
                            $num = (isset($period_options['days'])) ? ($period_options['days'] * $num_of_recurring) : $num_of_recurring;
                            $intervalDate = "+$num day";
                            break;
                        case 'W':
                            $num = (isset($period_options['weeks'])) ? ($period_options['weeks'] * $num_of_recurring) : ($num_of_recurring);
                            $intervalDate = "+$num week";
                            break;
                        case 'M':
                            $num = (isset($period_options['months'])) ? ($period_options['months'] * $num_of_recurring) : ($num_of_recurring);
                            $intervalDate = "+$num month";
                            break;
                        case 'Y':
                            $num = (isset($period_options['years'])) ? ($period_options['years'] * $num_of_recurring) : ($num_of_recurring);
                            $intervalDate = "+$num year";
                            break;
                        default:
                            $num = (isset($period_options['days'])) ? ($period_options['days'] * $num_of_recurring) : $num_of_recurring;
                            $intervalDate = "+$num day";
                            break;
                    }
                    $user = wp_get_current_user();
                    $user_id = $user->ID;

                    $expire_time = strtotime($intervalDate, $start_time);
                    if (isset($trial_option['is_trial_period']) && $trial_option['is_trial_period'] != 0 && $mail_type != 'renew_subscription') {
                        if ($trial_option['type'] == "W") {
                            $trial_num = ( isset($trial_option['weeks']) ) ? ($trial_option['weeks']) : 7;
                            $trial_days = "+$trial_num week";
                        } else if ($trial_option['type'] == "M") {
                            $trial_num = ( isset($trial_option['months']) ) ? ($trial_option['months']) : 30;
                            $trial_days = "+$trial_num month";
                        } else if ($trial_option['type'] == "Y") {
                            $trial_num = ( isset($trial_option['years']) ) ? ($trial_option['years']) : 365;
                            $trial_days = "+$trial_num year";
                        } else {
                            $trial_num = ( isset($trial_option['days']) ) ? $trial_option['days'] : 1;
                            $trial_days = "+$trial_num day";
                        }
                        $expire_time = strtotime($trial_days, $expire_time);
                    }
                }
            }
            return $expire_time;
        }

        function arm_plan_next_renew_date($start_time, $payment_mode = 'manual_subscription') {
            $current_day = date('Y-m-d', $start_time);

            $billing_start_day = $this->options['recurring']['manual_billing_start'];

            if ($billing_start_day == 'transaction_day' || $payment_mode == 'auto_debit_subscription') {
                $billing_type = $this->options['recurring']['type'];
                if ($billing_type == 'D') {
                    $days = $this->options['recurring']['days'];
                    $intervalDate = date('Y-m-d', strtotime("$current_day +$days day"));
                } else if ($billing_type == 'M') {
                    $months = $this->options['recurring']['months'];
                    $intervalDate = date('Y-m-d', strtotime("$current_day +$months month"));
                } else if ($billing_type == 'Y') {
                    $years = $this->options['recurring']['years'];
                    $intervalDate = date('Y-m-d', strtotime("$current_day +$years year"));
                }
            } else {

                $billing_type = $this->options['recurring']['type'];
                $days = $this->options['recurring']['days'];
                $months = $this->options['recurring']['months'];
                $years = $this->options['recurring']['years'];

                if (date('d', strtotime($current_day)) < $billing_start_day) {

                    if ($billing_type == 'D') {
                        $tdays = ($days > 0 ) ? $days - 1 : $days;
                        $intervalDate = date('Y-m-' . $billing_start_day, strtotime("$current_day + $tdays day"));
                    } else if ($billing_type == 'M') {
                        $tmonths = ($months > 0 ) ? $months - 1 : $months;
                        $intervalDate = date('Y-m-' . $billing_start_day, strtotime("$current_day + $tmonths month"));
                    } else if ($billing_type == 'Y') {
                        $tyears = ($years > 1) ? $years - 1 : $years;
                        $intervalDate = date('Y-m-' . $billing_start_day, strtotime("$current_day + $tyears year"));
                    }
                } else if (date('d', strtotime($current_day)) >= $billing_start_day) {

                    if ($billing_type == 'D') {
                        $intervalDate = date('Y-m-d', strtotime(date('Y-m-' . $billing_start_day, strtotime("$current_day + $days day"))));
                    } else if ($billing_type == 'M') {
                        $intervalDate = date('Y-m-d', strtotime(date('Y-m-' . $billing_start_day, strtotime("$current_day + $months month"))));
                    } else if ($billing_type == 'Y') {
                        $intervalDate = date('Y-m-d', strtotime(date('Y-m-' . $billing_start_day, strtotime("$current_day + $years year"))));
                    }
                }
            }

            $expire_time = strtotime($intervalDate, $start_time);
            return $expire_time;
        }

        public function plan_text($showTrialInfo = false, $showPlanType = true, $showDuration = true) {
            global $arm_subscription_plans, $arm_payment_gateways, $arm_global_settings;
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $currency = $arm_payment_gateways->arm_get_global_currency();
            $general_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
            $arm_currency_decimal = isset($general_settings['arm_currency_decimal_digit']) ? $general_settings['arm_currency_decimal_digit'] : 2;
            $planText = '';
	    $arm_plan_amount = $this->amount;
	    $arm_plan_amount = str_replace(',', '', $arm_plan_amount);
            $arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator($currency, $arm_plan_amount);
            if ($this->is_paid()) {
                if ($showPlanType) {
                    $planText .= '<span class="arm_item_status_text active">' . esc_html__('Paid', 'ARMember') . '</span><br/>';
                }
                if ($this->is_lifetime()) {
                    if($showDuration==true) { 
                        $planText .= $arm_plan_amount . " " . $currency . " " . esc_html__('For Lifetime', 'ARMember');
                    } else {
                        $planText .= $arm_plan_amount . " " . $currency;
                    }
                } else {
                    if ($this->payment_type == 'subscription') {
                        if ($showTrialInfo) {
                            if (!empty($this->recurring_data['trial'])) {
                                if ($this->recurring_data['trial']['amount'] > 0) {
                                    $arm_plan_trial_amount = $arm_payment_gateways->arm_amount_set_separator($currency, $this->recurring_data['trial']['amount']);
                                    $planText .= "{$arm_plan_trial_amount} {$currency}";
                                } else {
                                    $planText .= esc_html__('Free', 'ARMember');
                                }
                                $planText .= " " . esc_html__('for the first', 'ARMember') . " ";
                                $trialInterval = $this->recurring_data['trial']['interval'];
                                if ($this->recurring_data['trial']['period'] == 'Y') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('years', 'ARMember') : esc_html__('year', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'M') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('months', 'ARMember') : esc_html__('month', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'W') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('weeks', 'ARMember') : esc_html__('week', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'D') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('days', 'ARMember') : esc_html__('day', 'ARMember');
                                }
                                $planText .= ",<br/>" . esc_html__('Then', 'ARMember') . " ";
                            }
                        }
                        $typeArrayMany = array(
                            'D' => esc_html__("days", 'ARMember'),
                            'W' => esc_html__("weeks", 'ARMember'),
                            'M' => esc_html__("months", 'ARMember'),
                            'Y' => esc_html__("years", 'ARMember'),
                        );
                        $typeArray = array(
                            'D' => esc_html__("day", 'ARMember'),
                            'W' => esc_html__("week", 'ARMember'),
                            'M' => esc_html__("month", 'ARMember'),
                            'Y' => esc_html__("year", 'ARMember'),
                        );
                        $period = $this->recurring_data['period'];
                        $interval = $this->recurring_data['interval'];
                        $cycles = $this->recurring_data['rec_time'];
                        $recText = ($interval > 1) ? "{$interval} {$typeArrayMany[$period]}" : "{$typeArray[$period]}";
                        if($showDuration==true) {
                            $planText .= "{$arm_plan_amount} {$currency} " . esc_html__('for each', 'ARMember') . " {$recText}";
                        } else {
                            $planText .= "{$arm_plan_amount} {$currency} ";
                        }
                        if (!empty($cycles) && $cycles != '0' && is_numeric($cycles)) {
                            $planText .= ", " . esc_html__('for', 'ARMember') . " {$cycles} " . esc_html__('installments', 'ARMember');
                        }
                    } elseif ($this->payment_type == 'one_time') {
                        $expiry_type = (isset($this->options['expiry_type']) && $this->options['expiry_type'] != '') ? $this->options['expiry_type'] : 'joined_date_expiry';
                        if ($expiry_type == 'joined_date_expiry') {
                            $period_options = !empty($this->options['eopa'])? $this->options['eopa'] : 1 ;
                            $eopaType = !empty($period_options['type']) ? $period_options['type'] : 'D';
                            $eopaTime = '';
                            switch ($eopaType) {
                                case 'D':
                                    $num = (isset($period_options['days'])) ? $period_options['days'] : 1;
                                    $planperiod = ($num > 1) ? esc_html__("days", 'ARMember') : esc_html__("day", 'ARMember');
                                    $eopaTime = $num . " " . $planperiod;
                                    break;
                                case 'W':
                                    $num = (isset($period_options['weeks'])) ? $period_options['weeks'] : 1;
                                    $planperiod = ($num > 1) ? esc_html__("weeks", 'ARMember') : esc_html__("week", 'ARMember');
                                    $eopaTime = $num . " " . $planperiod;
                                    break;
                                case 'M':
                                    $num = (isset($period_options['months'])) ? $period_options['months'] : 1;
                                    $planperiod = ($num > 1) ? esc_html__("months", 'ARMember') : esc_html__("month", 'ARMember');
                                    $eopaTime = $num . " " . $planperiod;
                                    break;
                                case 'Y':
                                    $num = (isset($period_options['years'])) ? $period_options['years'] : 1;
                                    $planperiod = ($num > 1) ? esc_html__("years", 'ARMember') : esc_html__("year", 'ARMember');
                                    $eopaTime = $num . " " . $planperiod;
                                    break;
                                default:
                                    $num = (isset($period_options['days'])) ? $period_options['days'] : 1;
                                    $planperiod = ($num > 1) ? esc_html__("days", 'ARMember') : esc_html__("day", 'ARMember');
                                    $eopaTime = $num . " " . $planperiod;
                                    break;
                            }
                            if($showDuration==true) {
                                $planText .= "{$arm_plan_amount} {$currency} " . esc_html__('as One Time payment for', 'ARMember') . " {$eopaTime}";
                            } else {
                                $planText .= "{$arm_plan_amount} {$currency} ";
                            }
                        } else {
                            $expiry_time = date_i18n($date_format, strtotime($this->options['expiry_date']));
                            if($showDuration==true) {
                                $planText .= "{$arm_plan_amount} {$currency} " . esc_html__('as One Time payment till', 'ARMember') . " {$expiry_time}";
                            } else {
                                $planText .= "{$arm_plan_amount} {$currency} ";
                            }
                        }
                    }
                }
            } else {
                $planText = esc_html__('Free', 'ARMember');
            }
            return $planText;
        }

        public function user_plan_text($showTrialInfo = false, $payment_cycle = 0) {
            global $arm_subscription_plans, $arm_payment_gateways, $ARMember, $arm_global_settings;
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $currency = $arm_payment_gateways->arm_get_global_currency();
            $general_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
            $arm_currency_decimal = isset($general_settings['arm_currency_decimal_digit']) ? $general_settings['arm_currency_decimal_digit']:2;
            $planText = '';
            $arm_plan_amount = $this->amount;
	    $arm_plan_amount = str_replace(',', '', $arm_plan_amount);
            if ($this->is_paid()) {
                $planText .= '<span class="arm_item_status_text active">' . esc_html__('Paid', 'ARMember') . '</span><br/>';
                if ($this->is_lifetime()) {
                    $arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator($currency, $arm_plan_amount);
		    $planText .= $arm_plan_amount . " " . $currency . " " . esc_html__('For Lifetime', 'ARMember');
                } else {
                    if ($this->payment_type == 'subscription') {
                        if ($showTrialInfo) {
                            if (!empty($this->recurring_data['trial'])) {
                                if ($this->recurring_data['trial']['amount'] > 0) {
                                    $arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator($currency, $this->recurring_data['trial']['amount']);
                                    $planText .= "{$arm_plan_amount} {$currency}";
                                } else {
                                    $planText .= esc_html__('Free', 'ARMember');
                                }
                                $planText .= " " . esc_html__('for the first', 'ARMember') . " ";
                                $trialInterval = $this->recurring_data['trial']['interval'];
                                if ($this->recurring_data['trial']['period'] == 'Y') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('years', 'ARMember') : esc_html__('year', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'M') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('months', 'ARMember') : esc_html__('month', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'W') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('weeks', 'ARMember') : esc_html__('week', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'D') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('days', 'ARMember') : esc_html__('day', 'ARMember');
                                }
                                $planText .= ",<br/>" . esc_html__('Then', 'ARMember') . " ";
                            }
                        }
                        $typeArrayMany = array(
                            'D' => esc_html__("days", 'ARMember'),
                            'W' => esc_html__("weeks", 'ARMember'),
                            'M' => esc_html__("months", 'ARMember'),
                            'Y' => esc_html__("years", 'ARMember'),
                        );
                        $typeArray = array(
                            'D' => esc_html__("day", 'ARMember'),
                            'W' => esc_html__("week", 'ARMember'),
                            'M' => esc_html__("month", 'ARMember'),
                            'Y' => esc_html__("year", 'ARMember'),
                        );

                        $recurring_data = $this->prepare_recurring_data($payment_cycle);

                        $period = $recurring_data['period'];
                        $interval = $recurring_data['interval'];
                        $cycles = $recurring_data['rec_time'];
                        $recText = ($interval > 1) ? "{$interval} {$typeArrayMany[$period]}" : "{$typeArray[$period]}";
			
			$arm_plan_amount = str_replace(',', '', $recurring_data['amount']);
                        $arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator($currency, $arm_plan_amount);
                        
                        $arm_plan_amount = "<span class='arm_plan_amount_span'>" . $arm_plan_amount . "</span>";
                        $planText .= "{$arm_plan_amount} {$currency} " . esc_html__('for each', 'ARMember') . " {$recText}";
                        if (!empty($cycles) && $cycles != '0' && is_numeric($cycles)) {
                            $planText .= ", " . esc_html__('for', 'ARMember') . " {$cycles} " . esc_html__('installments', 'ARMember');
                        }
                    } elseif ($this->payment_type == 'one_time') {
                        $expiry_type = (isset($this->options['expiry_type']) && $this->options['expiry_type'] != '') ? $this->options['expiry_type'] : 'joined_date_expiry';
                        if ($expiry_type == 'joined_date_expiry') {
                            $period_options = $this->options['eopa'];
                            $eopaType = $period_options['type'];
                            $eopaTime = '';
                            switch ($eopaType) {
                                case 'D':
                                    $num = (isset($period_options['days'])) ? $period_options['days'] : 1;
                                    $planperiod = ($num > 1) ? esc_html__("days", 'ARMember') : esc_html__("day", 'ARMember');
                                    $eopaTime = $num . " " . $planperiod;
                                    break;
                                case 'W':
                                    $num = (isset($period_options['weeks'])) ? $period_options['weeks'] : 1;
                                    $planperiod = ($num > 1) ? esc_html__("weeks", 'ARMember') : esc_html__("week", 'ARMember');
                                    $eopaTime = $num . " " . $planperiod;
                                    break;
                                case 'M':
                                    $num = (isset($period_options['months'])) ? $period_options['months'] : 1;
                                    $planperiod = ($num > 1) ? esc_html__("months", 'ARMember') : esc_html__("month", 'ARMember');
                                    $eopaTime = $num . " " . $planperiod;
                                    break;
                                case 'Y':
                                    $num = (isset($period_options['years'])) ? $period_options['years'] : 1;
                                    $planperiod = ($num > 1) ? esc_html__("years", 'ARMember') : esc_html__("year", 'ARMember');
                                    $eopaTime = $num . " " . $planperiod;
                                    break;
                                default:
                                    $num = (isset($period_options['days'])) ? $period_options['days'] : 1;
                                    $planperiod = ($num > 1) ? esc_html__("days", 'ARMember') : esc_html__("day", 'ARMember');
                                    $eopaTime = $num . " " . $planperiod;
                                    break;
                            }
                            $arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator($currency, $this->amount);
                            $planText .= "{$arm_plan_amount} {$currency} " . esc_html__('as One Time payment for', 'ARMember') . " {$eopaTime}";
                        } else {
                            $expiry_time = date_i18n($date_format, strtotime($this->options['expiry_date']));
                            $arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator($currency, $this->amount);
                            $planText .= "{$arm_plan_amount} {$currency} " . esc_html__('as One Time payment till', 'ARMember') . " {$expiry_time}";
                        }
                    }
                }
            } else {
                $planText = esc_html__('Free', 'ARMember');
            }
            return $planText;
        }



        public function new_user_plan_text($showTrialInfo = false, $payment_cycle = 0 ,$show_title = true, $userPlanCurrencymeta="") {
            global $arm_subscription_plans, $arm_payment_gateways, $ARMember, $arm_global_settings;
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $currency = $arm_payment_gateways->arm_get_global_currency();
            $currency = !empty($userPlanCurrencymeta) ? $userPlanCurrencymeta : $currency;
            
            $planText = '';
            if ($this->is_paid()) {
              
                if ($this->is_lifetime()) {
                   
                    $planText .= $arm_payment_gateways->arm_prepare_amount($currency, $this->amount)." - ".esc_html__('Onetime', 'ARMember');
                } else {
                    if ($this->payment_type == 'subscription') {

                        if($show_title){
                            $planText .=   esc_html__('Subscription', 'ARMember')."<br/>";
                        }
                        if ($showTrialInfo) {
                            if (!empty($this->recurring_data['trial'])) {
                                if ($this->recurring_data['trial']['amount'] > 0) {
                                    $arm_plan_amount = $arm_payment_gateways->arm_amount_set_separator($currency, $this->recurring_data['trial']['amount']);
                                    $planText .= "{$arm_plan_amount} {$currency}";
                                } else {
                                    $planText .= esc_html__('Free', 'ARMember');
                                }
                                $planText .= " " . esc_html__('for the first', 'ARMember') . " ";
                                $trialInterval = $this->recurring_data['trial']['interval'];
                                if ($this->recurring_data['trial']['period'] == 'Y') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('years', 'ARMember') : esc_html__('year', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'M') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('months', 'ARMember') : esc_html__('month', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'W') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('weeks', 'ARMember') : esc_html__('week', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'D') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('days', 'ARMember') : esc_html__('day', 'ARMember');
                                }
                                $planText .= ",<br/>" . esc_html__('Then', 'ARMember') . " ";
                            }
                        }
                        $typeArrayMany = array(
                            'D' => esc_html__("days", 'ARMember'),
                            'W' => esc_html__("weeks", 'ARMember'),
                            'M' => esc_html__("months", 'ARMember'),
                            'Y' => esc_html__("years", 'ARMember'),
                        );
                        $typeArray = array(
                            'D' => esc_html__("Daily", 'ARMember'),
                            'W' => esc_html__("Weekly", 'ARMember'),
                            'M' => esc_html__("Monthly", 'ARMember'),
                            'Y' => esc_html__("Yearly", 'ARMember'),
                        );

                        $recurring_data = $this->prepare_recurring_data($payment_cycle,$currency);

                        $period = $recurring_data['period'];
                        $interval = $recurring_data['interval'];
                        $cycles = $recurring_data['rec_time'];
                        $recText = ($interval > 1) ? esc_html__("every", 'ARMember')." ".$interval." ".$typeArrayMany[$period] : "{$typeArray[$period]}";
                        ;
                        $planText .= $arm_payment_gateways->arm_prepare_amount($currency,  $recurring_data['amount'])." - ".$recText;
                    } elseif ($this->payment_type == 'one_time') {
                        
                    $planText .= $arm_payment_gateways->arm_prepare_amount($currency,  $this->amount)." - ".esc_html__('Onetime', 'ARMember');
                      
                    }
                }
            } else {
                $planText = esc_html__('Free', 'ARMember');
            }
            return $planText;
        }

        public function plan_price_text($showTrialInfo = false) {
            global $arm_subscription_plans, $arm_payment_gateways;
            $currency = $arm_payment_gateways->arm_get_global_currency();
            $planText = '';
            if ($this->is_paid()) {
                if ($this->is_lifetime()) {
                    $planText .= esc_html__('For Lifetime', 'ARMember');
                } else {
                    if ($this->payment_type == 'subscription') {
                        if ($showTrialInfo) {
                            if (!empty($this->recurring_data['trial'])) {
                                $planText .= " " . esc_html__('for the first', 'ARMember') . " ";
                                $trialInterval = $this->recurring_data['trial']['interval'];
                                if ($this->recurring_data['trial']['period'] == 'Y') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('years', 'ARMember') : esc_html__('year', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'M') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('months', 'ARMember') : esc_html__('month', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'W') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('weeks', 'ARMember') : esc_html__('week', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'D') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('days', 'ARMember') : esc_html__('day', 'ARMember');
                                }
                                $planText .= ",<br/>" . esc_html__('Then', 'ARMember') . " ";
                            }
                        }
                        $typeArrayMany = array(
                            'D' => esc_html__("days", 'ARMember'),
                            'W' => esc_html__("weeks", 'ARMember'),
                            'M' => esc_html__("months", 'ARMember'),
                            'Y' => esc_html__("years", 'ARMember'),
                        );
                        $typeArray = array(
                            'D' => esc_html__("day", 'ARMember'),
                            'W' => esc_html__("week", 'ARMember'),
                            'M' => esc_html__("month", 'ARMember'),
                            'Y' => esc_html__("year", 'ARMember'),
                        );
                        $period = $this->recurring_data['period'];
                        $interval = $this->recurring_data['interval'];
                        $cycles = $this->recurring_data['rec_time'];
                        $recText = ($interval > 1) ? "{$interval} {$typeArrayMany[$period]}" : "{$typeArray[$period]}";
                        $planText .= esc_html__('for each', 'ARMember') . " {$recText}";
                        if (!empty($cycles) && $cycles != '0' && is_numeric($cycles)) {
                            $planText .= ", " . esc_html__('for', 'ARMember') . " {$cycles} " . esc_html__('installments', 'ARMember');
                        }
                    } elseif ($this->payment_type == 'one_time') {
                        $period_options = !empty($this->options['eopa']) ? $this->options['eopa'] : 1;
                        $eopaType = !empty($period_options['type']) ? $period_options['type'] :'D';
                        $eopaTime = '';
                        switch ($eopaType) {
                            case 'D':
                                $num = (isset($period_options['days'])) ? $period_options['days'] : 1;
                                $eopaTime = $num . " " . esc_html__("day(s)", 'ARMember');
                                break;
                            case 'W':
                                $num = (isset($period_options['weeks'])) ? $period_options['weeks'] : 1;
                                $eopaTime = $num . " " . esc_html__("week(s)", 'ARMember');
                                break;
                            case 'M':
                                $num = (isset($period_options['months'])) ? $period_options['months'] : 1;
                                $eopaTime = $num . " " . esc_html__("month(s)", 'ARMember');
                                break;
                            case 'Y':
                                $num = (isset($period_options['years'])) ? $period_options['years'] : 1;
                                $eopaTime = $num . " " . esc_html__("year(s)", 'ARMember');
                                break;
                            default:
                                $num = (isset($period_options['days'])) ? $period_options['days'] : 1;
                                $eopaTime = $num . " " . esc_html__("day(s)", 'ARMember');
                                break;
                        }
                        $planText .= esc_html__('as One Time payment for', 'ARMember') . " {$eopaTime}";
                    }
                }
            }
            return $planText;
        }

        public function plan_price($showTrialInfo = false) {
            global $arm_subscription_plans, $arm_payment_gateways,$arm_global_settings;
            $currency = $arm_payment_gateways->arm_get_global_currency();
            $currency = apply_filters('arm_set_display_currency_outside', $currency);
            $currency_position = $arm_payment_gateways->arm_currency_symbol_position($currency);
            $currencies = array_merge($arm_payment_gateways->currency['paypal'], $arm_payment_gateways->currency['stripe'], $arm_payment_gateways->currency['authorize_net'], $arm_payment_gateways->currency['2checkout']);
            $is_coupon_amount = false; $get_currency_wise_seperator = true;
            $arm_cycle_price = $arm_payment_gateways->arm_amount_set_separator($currency, $this->amount, $is_coupon_amount, $get_currency_wise_seperator);
            $general_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
            $arm_currency_decimal = isset($general_settings['arm_currency_decimal_digit']) ? $general_settings['arm_currency_decimal_digit'] : 2;
            $arm_plan_amount = '<span class="arm_module_plan_cycle_price">' . $arm_cycle_price . '</span>';
            if (isset($currencies[$currency])) {
                $currency = $currencies[$currency];
            } else {
                $currencies_all = $arm_payment_gateways->arm_get_all_currencies();
                $currency = isset($currencies_all[strtoupper($currency)]) ? $currencies_all[strtoupper($currency)] : '';
            }
            $planText = '';
            if ($this->is_paid()) {
                if ($this->is_lifetime()) {
                    if ($currency_position == 'prefix') {
                        $planText .= $currency . $arm_plan_amount;
                    } else {
                        $planText .= $arm_plan_amount . $currency;
                    }
                } else {
                    if ($this->payment_type == 'subscription') {
                        if ($showTrialInfo) {
                            if (!empty($this->recurring_data['trial'])) {
                                if ($this->recurring_data['trial']['amount'] > 0) {
                                    if ($currency_position == 'prefix') {
                                        $planText .= "{$currency}{$this->recurring_data['trial']['amount']}";
                                    } else {
                                        $planText .= "{$this->recurring_data['trial']['amount']}{$currency}";
                                    }
                                } else {
                                    $planText .= esc_html__('Free', 'ARMember');
                                }
                            }
                        }
                        if ($currency_position == 'prefix') {
                            $planText .= "{$currency}{$arm_plan_amount}";
                        } else {
                            $planText .= "{$arm_plan_amount}{$currency} ";
                        }
                    } elseif ($this->payment_type == 'one_time') {
                        if ($currency_position == 'prefix') {
                            $planText .= "{$currency}{$arm_plan_amount}";
                        } else {

                            $planText .= "{$arm_plan_amount}{$currency}";
                        }
                    }
                }
            } else {
                if ($currency_position == 'prefix') {
                    $planText = "{$currency}{$arm_plan_amount}";
                } else {
                    $planText = "{$arm_plan_amount}{$currency}";
                }
            }

            return $planText;
        }

        public function setup_plan_text($showTrialInfo = true) {
            global $arm_subscription_plans, $arm_payment_gateways;
            $currency = $arm_payment_gateways->arm_get_global_currency();
            $planText = '';
            if ($this->is_paid()) {
                if ($this->is_lifetime()) {
                    $planText .= $this->amount . " " . $currency . " " . esc_html__('For Lifetime', 'ARMember');
                } else {
                    if ($this->payment_type == 'subscription') {
                        if ($showTrialInfo) {
                            if (!empty($this->recurring_data['trial'])) {
                                if ($this->recurring_data['trial']['amount'] > 0) {
                                    $planText .= "{$this->recurring_data['trial']['amount']} {$currency}";
                                } else {
                                    $planText .= esc_html__('Free', 'ARMember');
                                }
                                $planText .= " " . esc_html__('for the first', 'ARMember') . " ";
                                $trialInterval = $this->recurring_data['trial']['interval'];
                                if ($this->recurring_data['trial']['period'] == 'Y') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('years', 'ARMember') : esc_html__('year', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'M') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('months', 'ARMember') : esc_html__('month', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'W') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('weeks', 'ARMember') : esc_html__('week', 'ARMember');
                                } elseif ($this->recurring_data['trial']['period'] == 'D') {
                                    $planText .= ($trialInterval > 1) ? "{$trialInterval} " . esc_html__('days', 'ARMember') : esc_html__('day', 'ARMember');
                                }
                                $planText .= ", " . esc_html__('Then', 'ARMember') . " ";
                            }
                        }
                        $typeArrayMany = array(
                            'D' => esc_html__("days", 'ARMember'),
                            'W' => esc_html__("weeks", 'ARMember'),
                            'M' => esc_html__("months", 'ARMember'),
                            'Y' => esc_html__("years", 'ARMember'),
                        );
                        $typeArray = array(
                            'D' => esc_html__("day", 'ARMember'),
                            'W' => esc_html__("week", 'ARMember'),
                            'M' => esc_html__("month", 'ARMember'),
                            'Y' => esc_html__("year", 'ARMember'),
                        );
                        $period = $this->recurring_data['period'];
                        $interval = $this->recurring_data['interval'];
                        $cycles = $this->recurring_data['rec_time'];
                        $recText = ($interval > 1) ? "{$interval} {$typeArrayMany[$period]}" : "{$typeArray[$period]}";
                        $planText .= "{$this->amount} {$currency} " . esc_html__('for each', 'ARMember') . " {$recText}";
                        if (!empty($cycles) && $cycles != '0' && is_numeric($cycles)) {
                            $planText .= ", " . esc_html__('for', 'ARMember') . " {$cycles} " . esc_html__('installments', 'ARMember');
                        }
                    } elseif ($this->payment_type == 'one_time') {
                        $period_options = $this->options['eopa'];
                        $eopaType = $period_options['type'];
                        $eopaTime = '';
                        switch ($eopaType) {
                            case 'D':
                                $num = (isset($period_options['days'])) ? $period_options['days'] : 1;
                                $eopaTime = $num . " " . esc_html__("day(s)", 'ARMember');
                                break;
                            case 'W':
                                $num = (isset($period_options['weeks'])) ? $period_options['weeks'] : 1;
                                $eopaTime = $num . " " . esc_html__("week(s)", 'ARMember');
                                break;
                            case 'M':
                                $num = (isset($period_options['months'])) ? $period_options['months'] : 1;
                                $eopaTime = $num . " " . esc_html__("month(s)", 'ARMember');
                                break;
                            case 'Y':
                                $num = (isset($period_options['years'])) ? $period_options['years'] : 1;
                                $eopaTime = $num . " " . esc_html__("year(s)", 'ARMember');
                                break;
                            default:
                                $num = (isset($period_options['days'])) ? $period_options['days'] : 1;
                                $eopaTime = $num . " " . esc_html__("day(s)", 'ARMember');
                                break;
                        }
                        $planText .= "{$this->amount} {$currency} " . esc_html__('as One Time payment for', 'ARMember') . " {$eopaTime}";
                    }
                }
            }
            return $planText;
        }

        /* return plan start date and trial start date */

        function arm_trial_and_plan_start_date($nowMysql = '', $payment_mode = '', $allow_trial = true, $payment_cycle = 0) {
            $return_array['arm_trial_start_date'] = '';
            $return_array['arm_expire_plan_trial'] = '';
            $return_array['subscription_start_date'] = '';
            if ($nowMysql === '') {
                $nowMysql = strtotime(current_time('mysql'));
            }
            $return_array['subscription_start_date'] = $nowMysql;
            $current_day = date('Y-m-d', $nowMysql);
            if ($this->has_trial_period() && $this->is_recurring() && $allow_trial) {
                $plan_options = $this->options;
                if (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) {
                    if ($payment_cycle === '') {
                        $payment_cycle = 0;
                    }
                    $opt_recurring = $plan_options['payment_cycles'][$payment_cycle];
                    $period_options['type'] = !empty($opt_recurring['billing_type']) ? $opt_recurring['billing_type'] : 'M';
                } else {
                    $period_options = $plan_options['recurring'];
                }

                $billing_start_day = $this->options['recurring']['manual_billing_start'];
                $return_array['arm_trial_start_date'] = $nowMysql;
                $trial_type = $this->options['trial']['type'];

                if ($payment_mode != 'manual_subscription') {
                    switch ($trial_type) {
                        case 'D':
                            $days = $this->options['trial']['days'];
                            $return_array['subscription_start_date'] = strtotime(date('Y-m-d', strtotime("$current_day + $days day")));
                            break;
                        case 'W':
                            $weeks = $this->options['trial']['weeks'];
                            $return_array['subscription_start_date'] = strtotime(date('Y-m-d', strtotime("$current_day + $weeks week")));
                            break;
                        case 'M':
                            $months = $this->options['trial']['months'];
                            $return_array['subscription_start_date'] = strtotime(date('Y-m-d', strtotime("$current_day + $months month")));
                            break;
                        case 'Y':
                            $years = $this->options['trial']['years'];
                            $return_array['subscription_start_date'] = strtotime(date('Y-m-d', strtotime("$current_day + $years year")));
                            break;
                        default:
                            break;
                    }
                    $expire_date = $return_array['subscription_start_date'];

                    $return_array['arm_expire_plan_trial'] = $expire_date;
                } else {
                    if ($billing_start_day == 'transaction_day') {
                        switch ($trial_type) {
                            case 'D':
                                $days = $this->options['trial']['days'];
                                $return_array['subscription_start_date'] = strtotime(date('Y-m-d', strtotime("$current_day+$days day")));
                                break;
                            case 'W':
                                $weeks = $this->options['trial']['weeks'];
                                $return_array['subscription_start_date'] = strtotime(date('Y-m-d', strtotime("$current_day+$weeks week")));
                                break;
                            case 'M':
                                $months = $this->options['trial']['months'];
                                $return_array['subscription_start_date'] = strtotime(date('Y-m-d', strtotime("$current_day+$months month")));
                                break;
                            case 'Y':
                                $years = $this->options['trial']['years'];
                                $return_array['subscription_start_date'] = strtotime(date('Y-m-d', strtotime("$current_day+$years year")));
                                break;
                            default:
                                break;
                        }
                    } else {
                        switch ($trial_type) {
                            case 'D':
                                $trial_days = $this->options['trial']['days'];
                                $trial_end_date = date('Y-m-d', strtotime("$current_day+$trial_days day"));
                                $trial_end_day = date('d', strtotime($trial_end_date));

                                /* If recurring type daily( Recurring Using Days ) than we will simply add trial days to current day */
                                if ($trial_end_day < $billing_start_day || $period_options['type'] == 'D') {
                                    $return_array['subscription_start_date'] = strtotime($trial_end_date);
                                } else {
                                    $return_array['subscription_start_date'] = strtotime(date('Y-m-' . $billing_start_day, strtotime("$current_day+$trial_days day")));
                                }

                                if ($return_array['subscription_start_date'] < $nowMysql) {
                                    $return_array['subscription_start_date'] = strtotime($trial_end_date);
                                }


                                break;
                            case 'W':
                                $trial_weeks = $this->options['trial']['weeks'];
                                $trial_end_date = date('Y-m-d', strtotime("$current_day+$trial_weeks week"));
                                $trial_end_day = date('d', strtotime($trial_end_date));
                                /* If recurring type daily( Recurring Using Days ) than we will simply add trial days to current day */
                                if ($trial_end_day < $billing_start_day || $period_options['type'] == 'D') {
                                    $return_array['subscription_start_date'] = strtotime($trial_end_date);
                                } else {
                                    $return_array['subscription_start_date'] = strtotime(date('Y-m-' . $billing_start_day, strtotime("$current_day+$trial_weeks week")));
                                }

                                if ($return_array['subscription_start_date'] < $nowMysql) {
                                    $return_array['subscription_start_date'] = strtotime($trial_end_date);
                                }

                                break;
                            case 'M':
                                $trial_months = $this->options['trial']['months'];
                                $trial_end_date = date('Y-m-d', strtotime("$current_day+$trial_months month"));
                                $trial_end_day = date('d', strtotime($trial_end_date));
                                /* If recurring type daily( Recurring Using Days ) than we will simply add trial days to current day */
                                if ($trial_end_day < $billing_start_day || $period_options['type'] == 'D') {
                                    $return_array['subscription_start_date'] = strtotime($trial_end_date);
                                } else {
                                    $return_array['subscription_start_date'] = strtotime(date('Y-m-' . $billing_start_day, strtotime("$current_day+$trial_months month")));
                                }

                                if ($return_array['subscription_start_date'] < $nowMysql) {
                                    $return_array['subscription_start_date'] = strtotime($trial_end_date);
                                }
                                break;
                            case 'Y':
                                $trial_years = $this->options['trial']['years'];
                                $trial_end_date = date('Y-m-d', strtotime("$current_day+$trial_years year"));
                                $trial_end_day = date('d', strtotime($trial_end_date));
                                /* If recurring type daily( Recurring Using Days ) than we will simply add trial days to current day */
                                if ($trial_end_day < $billing_start_day || $period_options['type'] == 'D') {
                                    $return_array['subscription_start_date'] = strtotime($trial_end_date);
                                } else {
                                    $return_array['subscription_start_date'] = strtotime(date('Y-m-' . $billing_start_day, strtotime("$current_day+$trial_years year")));
                                }

                                if ($return_array['subscription_start_date'] < $nowMysql) {
                                    $return_array['subscription_start_date'] = strtotime($trial_end_date);
                                }
                                break;
                            default:
                                break;
                        }
                    }
                    $expire_date = $return_array['subscription_start_date'];

                    $return_array['arm_expire_plan_trial'] = $expire_date;
                }
            } else {
                $return_array['arm_trial_start_date'] = '';
                $return_array['arm_expire_plan_trial'] = '';
                $return_array['subscription_start_date'] = $nowMysql;
            }
            return $return_array;
        }

    }

}