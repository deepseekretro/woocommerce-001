<?php
if (!class_exists('ARM_members')) {

    class ARM_members {

        function __construct() {
            global $wpdb, $ARMember, $arm_slugs, $arm_pay_per_post_feature;
            add_action('wp_ajax_arm_member_ajax_action', array($this, 'arm_member_ajax_action'));
            add_action('wp_ajax_arm_member_bulk_action', array($this, 'arm_member_bulk_action'));
            add_action('wp_ajax_arm_members_hide_column', array($this, 'arm_members_hide_column'));
            add_action('wp_ajax_arm_filter_members_list', array($this, 'arm_filter_members_list'));
            add_action('wp_ajax_arm_change_user_status', array($this, 'arm_change_user_status'), 10, 1);
            add_action('wp_ajax_arm_get_user_all_pan_details_for_grid', array($this, 'arm_get_user_all_plan_details_for_grid'));
            add_action('wp_ajax_get_user_all_plan_details', array($this, 'arm_get_user_all_plan_details'));
            add_action('wp_ajax_arm_manage_plan_get_cycle', array($this, 'arm_manage_plan_get_cycle'));
            add_action('wp_ajax_get_user_plan_failed_payment_details', array($this, 'arm_get_user_plan_failed_payment_details'));
            add_action('wp_ajax_arm_resend_verification_email', array($this, 'arm_resend_verification_email_func'));
            add_action('arm_handle_import_export', array($this, 'arm_handle_import_export'));
            add_action('wp_ajax_arm_handle_import_user', array($this, 'arm_handle_import_user'));
            add_action('wp_ajax_arm_handle_import_user_meta', array($this, 'arm_handle_import_user_meta'));
            add_action('wp_ajax_arm_add_import_user', array($this, 'arm_add_import_user'));
            add_action('wp_ajax_arm_download_sample_csv', array($this, 'arm_download_sample_csv'));
            /* Member Iterations */
            //add_action('user_register', array($this, 'arm_user_register_hook_func'));
            //add_action('profile_update', array($this, 'arm_profile_update_hook_func'), 20, 2);
            //add_action('delete_user', array($this, 'arm_before_delete_user_action'), 10, 2);
            //add_action('deleted_user', array($this, 'arm_after_deleted_user_action'), 10, 2);
            /* Filter User Columns For Search */
            add_filter('user_search_columns', array($this, 'arm_user_search_columns'), 10, 3);
            /* Action for progressbar data for import user from csv or xml file */
            add_action('wp_ajax_arm_import_member_progress', array($this, 'arm_import_member_progress'));
            add_action('wp_ajax_arm_get_member_details', array($this, 'arm_get_member_grid_data'));

            /* Action for multisite, when user assign to site from admin menu */
            add_action('add_user_to_blog', array($this, 'arm_assign_user_to_blog'), 10, 3);
            //add_action('wp_ajax_arm_login_history_pagination', array($this, 'arm_login_history_pagination'));

            add_action('wp_ajax_arm_user_login_history_paging_action', array($this, 'arm_user_login_history_paging_action'));

            add_action('wp_ajax_arm_all_user_login_history_paging_action', array($this, 'arm_all_user_login_history_paging_action'));
            add_action('wp_ajax_arm_login_history_search_action', array($this, 'arm_all_user_login_history_paging_action'));
            /* Action for adding user to ARMember with plan */
            add_action('arm_add_user_to_armember', array($this, 'arm_add_user_to_armember_func'), 10, 3);

            //add_action('user_register', array($this, 'arm_add_capabilities_to_new_user'));

            //add_action('set_user_role', array($this,'arm_add_capabilities_to_change_user_role'), 10, 3);

            add_action('wp_ajax_arm_failed_attempt_login_history_paging_action', array($this, 'arm_failed_attempt_login_history_paging_action'));

            add_action('wp_ajax_arm_user_plan_action', array($this, 'arm_user_plan_action'));
            add_action('wp_ajax_get_arm_member_list', array($this, 'get_arm_member_list_func'));

            add_action('wp_ajax_arm_member_view_detail', array($this, 'arm_member_view_detail_func'));

            add_action('wp_ajax_arm_member_view_paid_plan_detail', array($this, 'arm_member_view_paid_plan_detail'));

            add_filter('arm_gateway_cancel_subscription_data', array($this, 'arm_gateway_cancel_subscription_data'), 10, 7);

            add_action('arm_cancel_subscription_payment_log_entry', array($this, 'arm_cancel_subscription_payment_log'), 10, 7);

            add_action('wp_ajax_arm_save_debug_logs', array($this, 'arm_save_debug_logs_settings'));

            add_action('wp_ajax_arm_clear_debug_logs_data', array($this, 'arm_clear_debug_logs_data'));

            add_action('arm_after_add_new_user', array($this, 'arm_update_entries_data_after_user_add'), 10, 2);

            add_action('wp_ajax_arm_get_bookingpress', array( $this, 'arm_get_bookingpress_plugin_func'));

            add_action('wp_ajax_arm_get_arforms', array( $this, 'arm_get_arforms_plugin_func'));
                
            add_action('wp_ajax_arm_get_arprice', array( $this, 'arm_get_arprice_plugin_func'));

            add_filter('arm_members_grid_columns',array($this,'arm_members_grid_columns_func'));

            add_filter('arm_pro_get_grid_exlcuded_colvis',array($this,'arm_pro_get_grid_exlcuded_colvis_func'),10,2);

            add_filter('arm_pro_get_grid_arm_colvis',array($this,'arm_pro_get_grid_arm_colvis_func'),10,2);
            
            add_filter('arm_pro_get_grid_sortable_columns',array($this,'arm_pro_get_grid_sortable_columns_func'),10,2);

            add_filter('arm_pro_get_default_grid_sort_columns',array($this,'arm_pro_get_default_grid_sort_columns_func'),10,1);

            add_filter('arm_pro_bulk_actions_filter_data',array($this,'arm_pro_bulk_actions_filter_data_func'),10,1);

            add_filter('grid_column_paid_with_arm_pro',array($this,'grid_column_paid_with_func'));

            add_filter('arm_member_grid_meta_fields_filter',array($this,'arm_member_grid_meta_fields_filter_func'),10,2);

            add_filter('arm_member_grid_membership_plans_fields_filter',array($this,'arm_member_grid_membership_plans_fields_filter_func'),10,3);

            add_filter('arm_admin_right_box_panel_section',array($this,'arm_admin_right_box_panel_section_func'),10,2);

            add_filter('arm_admin_right_box_panel_btn_section',array($this,'arm_admin_right_box_panel_btn_section_func'),10,2);
            
            add_filter('arm_admin_view_member_get_social_profile_data',array($this,'arm_admin_view_member_get_social_profile_data_func'),10,2);

            add_filter('arm_view_members_memberships_details',array($this,'arm_view_members_memberships_details_func'),10,3);

            add_filter('arm_get_member_forms_filter',array($this,'arm_get_member_forms_filter_func'));

            add_filter('arm_pro_modify_plan_ids_externally',array($this,'arm_pro_modify_plan_ids_externally_func'),10,2);

            add_filter('arm_pro_modify_future_plan_ids_externally',array($this,'arm_pro_modify_future_plan_ids_externally_func'),10,2);

            add_filter('arm_manage_members_plan_list_filters',array($this,'arm_manage_members_plan_list_filters_func'),10,4);
            
            add_filter('arm_pro_paid_conditional',array($this,'arm_pro_paid_conditional_func'),10,2);

            add_filter('arm_pro_paid_finite_plan_renewal_section',array($this,'arm_pro_paid_finite_plan_renewal_section_func'),10,2);

            add_filter('arm_multiple_membership_plan_renewal',array($this,'arm_multiple_membership_plan_renewal_func'),10,4);

            add_filter('arm_add_edit_member_additional_data',array($this,'arm_add_edit_member_additional_data_func'),10,4);

            add_filter('arm_paid_post_plan_lists',array($this,'arm_paid_post_plan_lists_func'),10,2);
                
        }

        function arm_paid_post_plan_lists_func($paidPlansLists, $planIDs){

            global $arm_subscription_plans,$is_multiple_membership_feature,$arm_pay_per_post_feature,$arm_global_settings;

            $all_subscription_plans = $arm_subscription_plans->arm_get_plans_data();
            $paidPlansLists = '<li data-label="' . addslashes( esc_attr__('Select Paid Post', 'ARMember')) . '" data-value="">' . addslashes( esc_html__('Select Paid Post', 'ARMember') ) . '</li>';
            if (!empty($all_subscription_plans)) {
                foreach ($all_subscription_plans as $p) {
                    if($p['arm_subscription_plan_post_id'] != 0 && (!in_array($p['arm_subscription_plan_id'], $planIDs)))
                    {
                        $p_id = $p['arm_subscription_plan_id'];
                        if ($is_multiple_membership_feature->isMultipleMembershipFeature) {
                            //if (in_array($p_id, $plan_to_show)) {
                            $paidPlansLists .= '<li data-label="' . esc_attr($p['arm_subscription_plan_name']) . '" data-value="' . $p_id . '">' . esc_attr($p['arm_subscription_plan_name']) . '</li>';
                            //}
                        } else {
                            $paidPlansLists .= '<li data-label="' . esc_attr($p['arm_subscription_plan_name']) . '" data-value="' . $p_id . '">' . esc_attr($p['arm_subscription_plan_name']) . '</li>';
                        }
                    }
                }
            }
            return $paidPlansLists;
        }

        function arm_add_edit_member_additional_data_func($arm_add_edit_member_additional_data,$planID,$planIDs,$user_id){
            global $arm_subscription_plans,$is_multiple_membership_feature,$arm_pay_per_post_feature,$arm_global_settings;
            $all_subscription_plans = $arm_subscription_plans->arm_get_plans_data();

            $arm_common_date_format = $arm_global_settings->arm_check_common_date_format(get_option('date_format'));

            $paidPlansLists = '';
            $paidPlansLists = $this->arm_paid_post_plan_lists_func($paidPlansLists,$planIDs);

            if($arm_pay_per_post_feature->isPayPerPostFeature==true)
            {
                $arm_add_edit_member_additional_data = '<input type="hidden" id="arm_total_user_posts" value="1">
                <tr><td colspan="2"><div class="arm_solid_divider"></div><div class="page_sub_title">'. esc_html__('Paid Post', 'ARMember').'</div></td></tr>

                <tr>
                    <td colspan="2">
                        <div class="arm-note-message --warning">
                            <p>'. esc_html__('Important Note:', 'ARMember').'</p>
                            <span>'. esc_html__('All the actions like add new post, renew cycle, extend days, delete post will be applied only after save button is clicked at the bottom of this page.', 'ARMember').'</span>
                        </div>                                
                    </td>
                </tr>

                <tr class="form-field">
                    <th>
                        <label for="arm_user_plan">'. esc_html__('Add New Paid Post', 'ARMember').'</label>
                    </th>
                    <td class="arm_position_relative">';
                        //if ($is_multiple_membership_feature->isMultipleMembershipFeature) {

                            $arm_add_edit_member_additional_data .= '<ul class="arm_user_plan_ul2" id="arm_user_plan_ul2">
                                <li class="arm_user_plan_li_1 arm_margin_bottom_20">
                                    <div class="arm_user_plns_box">
                                        <input type="hidden" class="arm_user_plan_change_input arm_mm_user_post_change_input_get_cycle" name="arm_user_plan2[]" id="arm_user_post_1" value="" data-arm-plan-count="0"/>

                                        <dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_margin_right_5">
                                            <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                            <dd><ul data-id="arm_user_post_1">'. $paidPlansLists .'</ul></dd>
                                        </dl>

                                        <img src="'. MEMBERSHIP_IMAGES_URL . '/add_plan.png"  id="arm_add_new_user_plan_link2" title="'. esc_html__('Add New Post', 'ARMember').'" onmouseover="this.src = \''. MEMBERSHIP_IMAGES_URL.'/add_plan_hover.png\';" onmouseout="this.src = \''. MEMBERSHIP_IMAGES_URL.'/add_plan.png\';" class="arm_helptip_icon tipso_style arm_add_plan_icon">
                                        <img src="'. MEMBERSHIP_IMAGES_URL . '/remove_plan.png"  id="arm_remove_user_plan2" title="'. esc_html__('Remove Post', 'ARMember').'" onmouseover="this.src = \''. MEMBERSHIP_IMAGES_URL.'/remove_plan_hover.png\';" onmouseout="this.src = \''. MEMBERSHIP_IMAGES_URL.'/remove_plan.png\';" class="arm_helptip_icon tipso_style arm_add_plan_icon">

                                        <div class="arm_selected_plan_cycle_0 arm_margin_top_20" style=" display: none;">
                                        </div>

                                        <div class="arm_subscription_start_date_wrapper">
                                            <span>'. esc_html__('Post Start Date', 'ARMember').'</span> 
                                            <input type="text" value="'. date($arm_common_date_format, strtotime(date('Y-m-d'))).'" data-date_format="'. esc_attr($arm_common_date_format).'"  name="arm_subscription_start_date2[]" class="arm_member_form_input arm_user_plan_date_picker" />
                                        </div>
                                    </div>
                                </li>

                            </ul>
                            <input type="hidden" id="arm_total_user_paid_posts" value="1"/>
                    </td>
                </tr>';
                if($arm_pay_per_post_feature->isPayPerPostFeature){
                    $arm_add_edit_member_additional_data .='<tr><td colspan="2">';
                        
                    $member_paid_post_plans = $arm_pay_per_post_feature->arm_get_paid_post_plans_paging($user_id, 1, 5);
                    $arm_add_edit_member_additional_data .= $member_paid_post_plans;
                    $arm_add_edit_member_additional_data .='</td></tr>';
                }
            }
            return $arm_add_edit_member_additional_data;
        }

        function arm_multiple_membership_plan_renewal_func( $arm_multiple_membership_plan_renewal, $pID, $planData, $count_plans){
            global $is_multiple_membership_feature;
            if ($is_multiple_membership_feature->isMultipleMembershipFeature) {
                
                $arm_multiple_membership_plan_renewal = '<input type="hidden" name="arm_user_plan[]" value="'. esc_attr($pID).'"/>

                <input type="hidden" name="arm_subscription_start_date[]" value="'. date('m/d/Y', $planData['arm_start_plan']).'"/>
                <div class="arm_position_relative arm_float_left">
                    <a class="arm_remove_user_plan_div armhelptip tipso_style" href="javascript:void(0)" title="'. esc_html__('Remove Plan', 'ARMember').'" onclick="showConfirmBoxCallback(\'delete_user_plan_'. esc_attr($pID).'\');"></a>
                    <div class="arm_confirm_box arm_member_edit_confirm_box" id="arm_confirm_box_delete_user_plan_'. esc_attr($pID).'" style="top:25px; right: -20px; ">
                        <div class="arm_confirm_box_body">
                            <div class="arm_confirm_box_arrow arm_float_right"></div>
                            <div class="arm_confirm_box_text arm_padding_top_15" >
                                '. esc_html__('Are you sure you want to remove this plan?', 'ARMember').'
                            </div>
                            <div class="arm_confirm_box_btn_container">
                                <button type="button" class="arm_confirm_box_btn armemailaddbtn arm_remove_user_plan_div_box arm_margin_right_5"  data-index="'. esc_attr($count_plans).'" >'. esc_html__('Ok', 'ARMember').'</button>
                                <button type="button" class="arm_confirm_box_btn armcancel" onclick="hideConfirmBoxCallback();">'. esc_html__('Cancel', 'ARMember').'</button>
                            </div>
                        </div>
                    </div>
                </div>';
            }
            return $arm_multiple_membership_plan_renewal;
        }

        function arm_pro_paid_finite_plan_renewal_section_func($arm_renewal_button_section,$pID){
            $arm_renewal_button_section = '<div class="arm_position_relative arm_float_left">
            <a href="javascript:void(0)" class="arm_user_renew_next_cycle_action_btn" id="arm_skip_next_cycle" onclick="showConfirmBoxCallback(\'renew_next_cycle_'. esc_attr($pID).'\');">'. esc_html__('Renew', 'ARMember').'</a>
            <div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_renew_next_cycle arm_width_280" id="arm_confirm_box_renew_next_cycle_'. esc_attr($pID).'" style="top:25px; right:45px; ">
                <div class="arm_confirm_box_body">
                    <div class="arm_confirm_box_arrow" style="float: right"></div>
                    <div class="arm_confirm_box_text arm_padding_top_15">
                        <input type="hidden" id="arm_skip_next_renewal_'. esc_attr($pID).'" name="arm_skip_next_renewal_'. esc_attr($pID).'" value="0" class="arm_skip_next_renewal"/>
                        '. esc_html__('Are you sure you want to renew plan?', 'ARMember').'
                    </div>
                    <div class="arm_confirm_box_btn_container">
                        <button type="button" class="arm_confirm_box_btn armemailaddbtn arm_margin_right_5" onclick="RenewNextCycleOkCallback('. esc_attr($pID).')" >'. esc_html__('Ok', 'ARMember').'</button>
                        <button type="button" class="arm_confirm_box_btn armcancel arm_user_renew_next_cycle_cancel_btn" onclick="hideUserRenewNextCycleBoxCallback('. esc_attr($pID).');">'. esc_html__('Cancel', 'ARMember').'</button>
                    </div>
                </div>
            </div>';
            return $arm_renewal_button_section;
        }

        function arm_pro_paid_conditional_func($arm_paid_condition,$planData){
            $arm_paid_condition = (!empty($planData) && !empty($planData['arm_current_plan_detail']) && empty($planData['arm_current_plan_detail']['arm_subscription_plan_post_id']) );
            return $arm_paid_condition;
        }

        function arm_manage_members_plan_list_filters_func($arm_membership_plan_list_filter,$planIDs,$plansLists,$planData){
            global $arm_global_settings,$is_multiple_membership_feature,$arm_subscription_plans;
            $arm_common_date_format = $arm_global_settings->arm_check_common_date_format(get_option('date_format'));
            $plan_start_date = ( isset( $planData['arm_start_plan'] ) && ! empty( $planData['arm_start_plan'] ) ) ? date( $arm_common_date_format, $planData['arm_start_plan'] ) : date( $arm_common_date_format );
            $arm_membership_plan_list_filter ='<th>
                <label for="arm_user_plan">';
                    if ($is_multiple_membership_feature->isMultipleMembershipFeature) {
                        $arm_membership_plan_list_filter .= esc_html__('Add New Membership Plan', 'ARMember');
                    } else {
                        $arm_membership_plan_list_filter .= esc_html__('Membership Plan', 'ARMember');
                    }
                    $arm_membership_plan_list_filter .= '</label>
            </th>
            <td class="arm_position_relative">';
                if ($is_multiple_membership_feature->isMultipleMembershipFeature) {

                    $arm_membership_plan_list_filter .= '<ul class="arm_user_plan_ul" id="arm_user_plan_ul">
                        <li class="arm_user_plan_li_0 arm_margin_bottom_20">
                            <div class="arm_user_plns_box">
                                <input type="hidden" class="arm_user_plan_change_input arm_mm_user_plan_change_input_get_cycle" name="arm_user_plan[]" id="arm_user_plan_0" value="" data-arm-plan-count="0"/>

                                <dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_margin_right_5">
                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd><ul data-id="arm_user_plan_0">'. $plansLists.'</ul></dd>
                                </dl>
                                <img src="'. MEMBERSHIP_IMAGES_URL . '/add_plan.png"  id="arm_add_new_user_plan_link" title="'. esc_html__('Add New Plan', 'ARMember').'" onmouseover="this.src = \''. MEMBERSHIP_IMAGES_URL.'/add_plan_hover.png\';" onmouseout="this.src = \''. MEMBERSHIP_IMAGES_URL .'/add_plan.png\';" class="arm_helptip_icon tipso_style arm_add_plan_icon">
                                <img src="'. MEMBERSHIP_IMAGES_URL . '/remove_plan.png"  id="arm_remove_user_plan" title="'. esc_html__('Remove Plan', 'ARMember').'" onmouseover="this.src = \''. MEMBERSHIP_IMAGES_URL.'/remove_plan_hover.png\';" onmouseout="this.src = \''. MEMBERSHIP_IMAGES_URL.'/remove_plan.png\';" class="arm_helptip_icon tipso_style arm_add_plan_icon">

                                <div class="arm_selected_plan_cycle_0 arm_margin_top_10" style="display: none;">
                                </div>

                                <div class="arm_subscription_start_date_wrapper">
                                    <span>'. esc_html__('Plan Start Date', 'ARMember').'  </span> 
                                    <input type="text" value="'. date($arm_common_date_format, strtotime(date('Y-m-d'))).'" data-date_format="'. esc_attr($arm_common_date_format).'"  name="arm_subscription_start_date[]" class="arm_member_form_input arm_user_plan_date_picker" />
                                </div>
                            </div>
                        </li>

                    </ul>
                    <input type="hidden" id="arm_total_user_plans" value="1"/>';

                } else {

                    $arm_membership_plan_list_filter .= '<span class="arm_user_plan_text">';
                    if(!empty($planIDs))
                    {
                        $arm_plan_first = array_key_first($planIDs);
                        $plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($planIDs[$arm_plan_first]);
                        $arm_membership_plan_list_filter .= (!empty($plan_name)) ? esc_html($plan_name) : '-';
                        $plan_id = ($planIDs[$arm_plan_first] > 0) ? $planIDs[$arm_plan_first] : '';
                    }
                    else
                    {
                        $arm_membership_plan_list_filter .= '-';
                        $plan_id = '';
                    }
                    $arm_membership_plan_list_filter .='</span>
                    <a href="javascript:void(0)" class="arm_user_plan_change_action_btn" onclick="showUserPlanChangeBoxCallback(\'plan_change\');">'. esc_html__('Change Plan', 'ARMember').'</a>
                    <div class="arm_confirm_box arm_member_edit_confirm_box arm_confirm_box_plan_change arm_width_280" id="arm_confirm_box_plan_change" >
                        <div class="arm_confirm_box_body">
                            <div class="arm_confirm_box_arrow"></div>
                            <div class="arm_confirm_box_text arm_text_align_left arm_padding_top_15">
                                <input type="hidden" id="arm_user_plan" class="arm_user_plan_change_input arm_user_plan_change_input_get_cycle" name="arm_user_plan" data-old="'. esc_attr($plan_id).'" value="'. esc_attr($plan_id).'" data-manage-plan-grid="2"/>
                                <span class="arm_add_plan_filter_label">'. esc_html__('Select New Plan', 'ARMember') .'</span>
                                <dl class="arm_selectbox column_level_dd arm_width_230">
                                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                    <dd><ul data-id="arm_user_plan">'. $plansLists.'</ul></dd>
                                </dl>
                                <div class="arm_selected_plan_cycle"></div>';

                                if(in_array($plan_id, $planIDs)){
                                    $display = 'none';
                                }
                                else{
                                    $display = 'inline-block';
                                }
                 

                                $arm_membership_plan_list_filter .='<div style="display: '. esc_attr($display).'; position: relative;" class="arm_plan_start_date_box arm_margin_top_10">
                                    <span class="arm_add_plan_filter_label">'. esc_html__('Plan Start Date', 'ARMember').'  </span> 
                                    <input type="text" value="'. date($arm_common_date_format, strtotime($plan_start_date)).'" data-date_format="'. esc_attr($arm_common_date_format).'" name="arm_subscription_start_date" class="arm_member_form_input arm_user_plan_date_picker arm_width_232 arm_min_width_232" />
                                </div>
                            </div>
                            <div class="arm_confirm_box_btn_container">
                                <button type="button" class="arm_confirm_box_btn armemailaddbtn arm_user_plan_change_btn arm_margin_right_5" >'. esc_html__('Ok', 'ARMember').'</button>
                                <button type="button" class="arm_confirm_box_btn armcancel arm_user_plan_change_cancel_btn" onclick="hideUserPlanChangeBoxCallback();">'. esc_html__('Cancel', 'ARMember').'</button>
                            </div>
                        </div>
                    </div>';
                }
            $arm_membership_plan_list_filter .='</td>';
            return $arm_membership_plan_list_filter;
        }
        function arm_pro_modify_future_plan_ids_externally_func($futurePlanIDs, $user_id){
            global $arm_pay_per_post_feature;
            if( !empty( $futurePlanIDs ) ){
                foreach( $futurePlanIDs as $f_plan_key => $f_plan_id ){
                    $paid_post_id = $arm_pay_per_post_feature->arm_get_post_from_plan_id( $f_plan_id );
                    if( !empty( $paid_post_id[0]['arm_subscription_plan_id'] && !empty( $paid_post_id[0]['arm_subscription_plan_post_id'] ) ) ){
                        unset( $futurePlanIDs[$f_plan_key] );
                    }
                }
            }
            return $futurePlanIDs;
        }

        function arm_pro_modify_plan_ids_externally_func($planIDs, $user_id){
            $postIDs = get_user_meta($user_id, 'arm_user_post_ids', true);
            $postIDs = !empty($postIDs) ? $postIDs : array();
            foreach($planIDs as $plan_key => $planVal)
            {
                if(!empty($postIDs[$planVal]))
                {
                    unset($planIDs[$plan_key]);
                }
            }

            return $planIDs;
        }

        function arm_get_member_forms_filter_func($armform)
        {
            $armform = new ARM_Form();
            return $armform;
        }

        function arm_view_members_memberships_details_func($arm_member_plans_details,$user_id,$plan_id_name_array){
            global $arm_pay_per_post_feature,$arm_subscription_plans,$arm_transaction;
            $is_paid_post = 1;
            if($arm_pay_per_post_feature->isPayPerPostFeature){
                $paid_post_membership_history = $arm_subscription_plans->arm_get_user_membership_history( $user_id, 1, 5, $plan_id_name_array,$is_paid_post);
                if(!empty($paid_post_membership_history)){
                    $arm_member_plans_details .= '<div class="arm_view_member_sub_title">'. esc_html__('Paid Post History','ARMember').'</div>
                    <div class="arm_view_member_sub_content arm_membership_history_list armPageContainer">'. $paid_post_membership_history.'
                    </div>
                    <div class="armclear"></div>';
                }
                
                $user_logs = $arm_transaction->arm_get_user_transactions_with_pagging($user_id, 1, 5, $plan_id_name_array,$is_paid_post);
                if(!empty($user_logs)){
                    $arm_member_plans_details .= '<div class="arm_view_member_sub_title">'. esc_html__('Paid Post Payment History','ARMember').'</div>
                        <div class="arm_view_member_sub_content arm_membership_history_list armPageContainer">'. $user_logs.'
                    </div>
                    <div class="armclear"></div>';
                }
            }

            $arm_member_plans_details .= '<div class="armclear"></div>';
            $login_history = $this->arm_get_user_login_history($user_id, 1, 10);
            if(isset($login_history) && !empty($login_history)):
                $arm_member_plans_details .= '<div class="arm_view_member_sub_title">'. esc_html__('Login History','ARMember').'</div>
                <div class="arm_view_member_sub_content arm_membership_history_list armPageContainer">
                    '. $login_history.'
                </div>
                <div class="armclear"></div>';
            endif;
            $arm_member_plans_details .= '<div class="armclear"></div>';
            return $arm_member_plans_details;
        }

        function arm_admin_view_member_get_social_profile_data_func($arm_social_profiles_field_data, $user_id){
            global $arm_social_feature,$arm_member_forms;
            if ($arm_social_feature->isSocialFeature) {
                $socialProfileFields = $arm_member_forms->arm_social_profile_field_types();
                if (!empty($socialProfileFields)) {
                    foreach ($socialProfileFields as $spfKey => $spfLabel) {
                        $spfMetaKey = 'arm_social_field_'.$spfKey;
                        $spfMetaValue = get_user_meta($user_id, $spfMetaKey, true);                                        
                        if( !empty( $spfMetaValue ) ) {    
                            $field_meta_val =  (!empty($spfMetaValue)) ? esc_html($spfMetaValue) : '--';                                    
                            $arm_social_profiles_field_data .= '<tr class="form-field">
                            <th class="arm-form-table-label">'. esc_html($spfLabel).':</th>
                            <td class="arm-form-table-content">'. $field_meta_val .'</td>
                        </tr>';
                        }
                    }
                }
            }

            $coupon_codes = "";
            $user_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
            if(!empty($user_plan_ids)) {
            foreach ($user_plan_ids as $key => $plan_ids) {
                    $coupon_lbl = get_user_meta($user_id, 'arm_used_invite_coupon_'.$plan_ids, true);
                    if(!empty($coupon_lbl))
                    {
                        $coupon_codes .= $coupon_lbl.",";
                    }
                }	
            }

            if(!empty($coupon_codes)) {   
                $arm_social_profiles_field_data .= '<tr class="form-field">
                    <th class="arm-form-table-label">'. esc_html__("Used Coupon as Invitation", "ARMember").':</th>
                    <td class="arm-form-table-content">'. trim($coupon_codes, ",") .'</td>
                </tr>';

            }
            return $arm_social_profiles_field_data;
        }

        function arm_admin_right_box_panel_section_func($arm_admin_view_member_additional_data,$user_id)
        {
            global $arm_global_settings,$arm_social_feature,$arm_members_badges;
            if ($arm_social_feature->isSocialFeature) {
                $user_achievements_detail = $arm_members_badges->arm_get_user_achievements_detail($user_id);
                $global_settings = $arm_global_settings->global_settings;
                $badge_width = !empty($global_settings['badge_width']) ? $global_settings['badge_width'] : 30;
                $badge_height = !empty($global_settings['badge_height']) ? $global_settings['badge_height'] : 30;
                $badge_css = "width:".$badge_width."px; height:".$badge_height."px;";
                if (!empty($user_achievements_detail)) {                   
                    $arm_admin_view_member_additional_data .= '<div class="arm_member_detail_badges">';
                    foreach($user_achievements_detail as $user_achieve) {
                        $arm_admin_view_member_additional_data .= '<span class="arm-user-badge armhelptip_front" title="'. esc_html($user_achieve['badge_title']).'"><img src="'. strstr($user_achieve['badge_icon'],"//").'" style="'. esc_html($badge_css).'" /></span>';
                    }
                    $arm_admin_view_member_additional_data .= '</div>';
                }
            } 
            return $arm_admin_view_member_additional_data;
        }

        function arm_admin_right_box_panel_btn_section_func($arm_admin_view_member_additional_btn_data,$user_id)
        {
            global $arm_social_feature;
            if($arm_social_feature->isSocialFeature) {
                $arm_admin_view_member_additional_btn_data = '<button type="button" class="armemailaddbtn arm_view_membership_card_btn arm_cancel_btn" data-id="'. esc_html( $user_id ).'">'. esc_html__('View Membership Card', 'ARMember').'</button>';
        	}
            return $arm_admin_view_member_additional_btn_data;
        }

        function arm_member_grid_membership_plans_fields_filter_func($arm_others_field_filters,$all_plans,$filter_member_status){
            global $arm_pay_per_post_feature,$armPrimaryStatus,$is_multiple_membership_feature;
            $arm_others_field_filters ='<div class="arm_filter_status_box arm_datatable_filter_item">                        
                <input type="hidden" id="arm_status_filter" class="arm_status_filter" value="'. esc_attr($filter_member_status).'" />
                <dl class="arm_selectbox arm_width_190">
                    <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                    <dd>
                        <ul data-id="arm_status_filter" data-placeholder="'. esc_html__('Select Status', 'ARMember').'">
                            <li data-label="'. esc_html__('Select Status', 'ARMember').'" data-value="0">'. esc_html__('Select Status', 'ARMember').'</li>';
                            foreach ($armPrimaryStatus as $key => $value) {
                                $arm_others_field_filters .='<li data-label="'. esc_attr($value).' '. esc_html__('User', 'ARMember').'" data-value="'. esc_attr($key) .'">'. esc_html($value) .' '. esc_html__('User', 'ARMember').'</li>';
                            }                           
                            if($arm_pay_per_post_feature->isPayPerPostFeature)
                            {
                                $suspended_plan_user_txt = esc_html__('Suspended Plan/Post User', 'ARMember');
                                $active_plan_user_txt = esc_html__('Active Plan/Post User', 'ARMember');
                            }
                            else{
                                $suspended_plan_user_txt = esc_html__('Suspended Plan User', 'ARMember');
                                $active_plan_user_txt = esc_html__('Active Plan User', 'ARMember');
                            }
                            $arm_others_field_filters .='<li data-label="'. esc_attr($suspended_plan_user_txt).'" data-value="5">'. esc_html($suspended_plan_user_txt).'</li>
                            <li data-label="'. esc_attr($active_plan_user_txt).'" data-value="6">'. esc_attr($active_plan_user_txt).'</li>

                        </ul>
                    </dd>
                </dl>
            </div>';


            if($is_multiple_membership_feature->isMultipleMembershipFeature) {
                $arm_others_field_filters .='<div class="arm_datatable_filter_item arm_filter_membership_type_label">                            
                    <input type="hidden" id="arm_filter_membership_type" class="arm_filter_membership_type" value="0" />
                    <dl class="arm_selectbox arm_width_190">
                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                        <dd>
                            <ul data-id="arm_filter_membership_type" data-placeholder="'. esc_html__('All Members', 'ARMember').'">
                                <li data-label="'. esc_html__('All Members', 'ARMember').'" data-value="0">'. esc_html__('All Members', 'ARMember').'</li>
                                <li data-label="'. esc_html__('Single Membership', 'ARMember').'" data-value="1">'. esc_html__('Single Membership', 'ARMember').'</li>
                                <li data-label="'. esc_html__('Multiple Membership', 'ARMember').'" data-value="2">'. esc_html__('Multiple Membership', 'ARMember').'</li>
                            </ul>
                        </dd>
                    </dl>
                </div>';
            }
            return $arm_others_field_filters;
        }

        function arm_member_grid_meta_fields_filter_func($arm_meta_field_filters,$user_meta_keys){
            $arm_formfields = $user_meta_keys;
            if (!empty($arm_formfields)) {
                $arm_meta_field_filters = '<div class="arm_filter_status_box arm_datatable_filter_item">                            
                    <input type="hidden" id="arm_meta_field_filter" class="arm_meta_field_filter" value="0" />
                    <dl class="arm_selectbox arm_width_190">
                        <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                        <dd>
                            <ul data-id="arm_meta_field_filter" data-placeholder="'. esc_html__('Select field', 'ARMember').'">
                                <li data-label="'. esc_html__('Select field', 'ARMember').'" data-value="0">'. esc_html__('Select field', 'ARMember').'</li>';
                            foreach ($arm_formfields as $field_meta_key => $field_meta_value) { 
                                $field_options = maybe_unserialize($field_meta_value);
                                $field_options = apply_filters('arm_change_field_options', $field_options);
                                $exclude_field_keys = array('user_pass','repeat_pass','arm_user_plan', 'arm_last_login_ip', 'arm_last_login_date', 'roles', 'section','repeat_pass', 'repeat_email', 'social_fields', 'avatar', 'profile_cover');
                                $field_meta_key = isset($field_options['meta_key']) ? $field_options['meta_key'] : $field_options['id'];
                                $field_meta_label = isset($field_options['label']) ? $field_options['label'] : '';
                                $field_type = isset($field_options['type']) ? $field_options['type'] : array();
                                if (!in_array($field_meta_key, $exclude_field_keys) && !in_array($field_type, array('section', 'roles', 'html', 'hidden', 'submit', 'repeat_pass', 'repeat_email'))) {
                                    
                                    $arm_meta_field_filters .= '<li data-label="'. esc_attr($field_meta_label) .'" data-value="'. esc_attr($field_meta_key) .'">'. esc_html($field_meta_label) .'</li>'; 
                                    }
                                }
                            $arm_meta_field_filters .= '</ul>
                        </dd>
                    </dl>
                </div>';
            }    
            return $arm_meta_field_filters;
        }

        function arm_pro_bulk_actions_filter_data_func($filter_data){
            global $armPrimaryStatus,$is_multiple_membership_feature;
            $filter_data = '<ol>'. esc_html__('Change Status To','ARMember').'</ol>';
            foreach($armPrimaryStatus as $armPrimaryStatus_key => $armPrimaryStatus_value)
            { 
                $filter_data .= '<li data-label="'. esc_attr($armPrimaryStatus_value).' '. esc_attr__('User', 'ARMember').'" data-value="arm_user_status-'. esc_html($armPrimaryStatus_key) .'">'. esc_html($armPrimaryStatus_value) .' '. esc_html__('User', 'ARMember').'</li>';
            }

            if(!$is_multiple_membership_feature->isMultipleMembershipFeature)
            {
                $filter_data .= '<ol>'. esc_html__('Change Plan To', 'ARMember').'</ol>';
            }
            else {
                $filter_data .= '<ol>'. esc_html__('Add Plan To', 'ARMember').'</ol>';
            }
            return $filter_data;
        }

        function grid_column_paid_with_func($grid_column_paid_with)
        {
            global $is_multiple_membership_feature,$arm_pay_per_post_feature;
            if($is_multiple_membership_feature->isMultipleMembershipFeature){
                $grid_column_paid_with = false;
            }
            return $grid_column_paid_with;
        }

        function arm_pro_get_grid_arm_colvis_func($arm_colvis,$total_grid_column)
        {
            global $is_multiple_membership_feature,$arm_pay_per_post_feature;
            if($is_multiple_membership_feature->isMultipleMembershipFeature){
                $arm_colvis = ' 1, '.$total_grid_column;
            }
            return $arm_colvis;
        }

        function arm_pro_get_grid_exlcuded_colvis_func($arm_exclude_colvis,$total_grid_column)
        {
            global $is_multiple_membership_feature,$arm_pay_per_post_feature;
            if($is_multiple_membership_feature->isMultipleMembershipFeature){
                $arm_exclude_colvis = '0 , 1';
            }
            return $arm_exclude_colvis;
        }

        function arm_pro_get_grid_sortable_columns_func($grid_clmn,$total_grid_column){
            global $is_multiple_membership_feature,$arm_pay_per_post_feature;
            $grid_clmn = '';
            if($is_multiple_membership_feature->isMultipleMembershipFeature){
                $grid_column_paid_with = false;
                $arm_colvis = ' 1, '.$total_grid_column;
                $arm_exclude_colvis='0 , 1';
                for( $i=0; $i < $total_grid_column; $i++ ) {
                    //if( $i == 3 || $i == 4 || $i ==5 || $i ==7 || $i == 8 || $i == 9 ) {
                    if( $i>=3 &&  $i<=12 ) {
                        if($arm_pay_per_post_feature->isPayPerPostFeature && $i!=6 && $i!=7){
                            continue;
                        }
                        else if($arm_pay_per_post_feature->isPayPerPostFeature && $i==7)
                        {
                            //need to skip i == 7 when the pay per post is enabled.
                        }
                        else if( $i == 3 || $i == 4 || $i ==5 || $i == 7 || $i == 8 || $i == 9 || $i == 10  || $i == 11) {
                            continue;
                        }
                    }
                    $grid_clmn .= $i . ",";
                }
            }
            else{
               
                for( $i=0; $i < $total_grid_column; $i++ ) {
                    if( $i>=2 &&  $i<=13 ) {
                        if($arm_pay_per_post_feature->isPayPerPostFeature && $i!=5 && $i!=6 && $i!=7  && $i!=9 ){
                            continue;
                        }
                        else if( $i == 2 || $i == 3 || $i == 4 || $i == 8 || $i == 9 || $i == 10 || $i == 11 || $i == 12 ) {
                            continue;
                        }
                    }
                    $grid_clmn .= $i . ",";
                }
            }
            return $grid_clmn;
        }

        function arm_pro_get_default_grid_sort_columns_func($sort_clmn)
        {
            global $is_multiple_membership_feature,$arm_pay_per_post_feature;
            if($is_multiple_membership_feature->isMultipleMembershipFeature){
                $sort_clmn = 3;
            }
            return $sort_clmn;
            
        }

        function arm_members_grid_columns_func($grid_columns){
            global $arm_pay_per_post_feature, $is_multiple_membership_feature;
            $grid_columns = array(
                'avatar' => esc_html__('Avatar', 'ARMember'),
                'ID' => esc_html__('User ID', 'ARMember'),
                'user_login' => esc_html__('Username', 'ARMember'),
                'user_email' => esc_html__('Email Address', 'ARMember'),
                'arm_user_plan_ids' => esc_html__('Member Plan', 'ARMember'),
            );
            if($arm_pay_per_post_feature->isPayPerPostFeature)
            {
                $grid_columns['arm_user_paid_plans'] = esc_html__('Paid Post(s)', 'ARMember');
            }
            $grid_columns['arm_primary_status'] = esc_html__('Status', 'ARMember');
            if(!$is_multiple_membership_feature->isMultipleMembershipFeature){
                $grid_columns['roles'] = esc_html__('User Role', 'ARMember');
            }
            $grid_columns['first_name'] = esc_html__('First Name', 'ARMember');
            $grid_columns['last_name'] = esc_html__('Last Name', 'ARMember');
            $grid_columns['display_name'] = esc_html__('Display Name', 'ARMember');
            $grid_columns['user_registered'] = esc_html__('Joined Date', 'ARMember');
            return $grid_columns;
        }
        function arm_get_bookingpress_plugin_func() {
			global $wpdb, $ARMember, $arm_capabilities_global;
			$response = array(
				'type' => 'error',
				'msg'  => esc_html__( 'Sorry, Something went wrong. Please try again.', 'ARMember' ),
			);

			$ARMember->arm_check_user_cap( $arm_capabilities_global['arm_growth_plugins'], '1',1); //phpcs:ignore --Reason:Verifying nonce

			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_get_bookingpress' ) { //phpcs:ignore
				$arm_bookingpress_install_activate = 1; 
				if ( ! file_exists( WP_PLUGIN_DIR . '/bookingpress-appointment-booking/bookingpress-appointment-booking.php' ) ) {
        
					if ( ! function_exists( 'plugins_api' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
					}
					$response = plugins_api(
						'plugin_information',
						array(
							'slug'   => 'bookingpress-appointment-booking',
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
						
						if ( ! empty( $source ) ) {
							if ( $upgrader->install( $source ) === true ) {
								activate_plugin( 'bookingpress-appointment-booking/bookingpress-appointment-booking.php' );
								$arm_bookingpress_install_activate = 1; 
							}
						}
					} else {
						$source_url = 'https://bookingpressplugin.com/bpa_misc/bkp_lite_plugin_install_api.php';
						$get_custom_response = wp_remote_get( $source_url, array( 'method' => 'GET') );
						if(!is_wp_error($get_custom_response)) {
							$get_custom_response_body = json_decode(wp_remote_retrieve_body($get_custom_response));
							if(is_object($get_custom_response_body) && !empty($get_custom_response_body))
							{
								if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
									require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
								}
								$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
								$source   = !empty( $get_custom_response_body->download_link ) ? $get_custom_response_body->download_link : '';
								
								if ( ! empty( $source ) ) {
									if ( $upgrader->install( $source ) === true ) {
										activate_plugin( 'bookingpress-appointment-booking/bookingpress-appointment-booking.php' );
										$arm_bookingpress_install_activate = 1;
									}
								}
							}
						}
					}
				}
			
				if ( ! empty( $arm_bookingpress_install_activate ) && $arm_bookingpress_install_activate == 1 ) {
					$response = array(
						'type' => 'success',
						'msg'  => esc_html__('BookingPress Successfully installed.', 'ARMember' ),
					);
				}
			}
			
			echo json_encode( $response );
			die();
			
		}
		function arm_get_arforms_plugin_func() {
			global $wpdb, $ARMember, $arm_capabilities_global;
			$response = array(
				'type' => 'error',
				'msg'  => esc_html__( 'Sorry, Something went wrong. Please try again.', 'ARMember' ),
			);

			$ARMember->arm_check_user_cap( $arm_capabilities_global['arm_growth_plugins'], '1',1); //phpcs:ignore --Reason:Verifying nonce

			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_get_arforms' ) { //phpcs:ignore
				$arm_arforms_install_activate = 1; 
				if ( ! file_exists( WP_PLUGIN_DIR . '/arforms-form-builder/arforms-form-builder.php' ) ) {
        
					if ( ! function_exists( 'plugins_api' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
					}
					$response = plugins_api(
						'plugin_information',
						array(
							'slug'   => 'arforms-form-builder',
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
						
						if ( ! empty( $source ) ) {
							if ( $upgrader->install( $source ) === true ) {
								activate_plugin( 'arforms-form-builder/arforms-form-builder.php' );
								$arm_arforms_install_activate = 1; 
							}
						}
					} else {
						$source_url = 'https://www.arformsplugin.com/arf_misc/arforms-form-builder/arforms-form-builder-latest.zip';
						$get_custom_response = wp_remote_get( $source_url, array( 'method' => 'GET') );
						if(!is_wp_error($get_custom_response)) {
							$get_custom_response_body = json_decode(wp_remote_retrieve_body($get_custom_response));
							if(is_object($get_custom_response_body) && !empty($get_custom_response_body))
							{
								if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
									require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
								}
								$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
								$source   = !empty( $get_custom_response_body->download_link ) ? $get_custom_response_body->download_link : '';
								
								if ( ! empty( $source ) ) {
									if ( $upgrader->install( $source ) === true ) {
										activate_plugin( 'arforms-form-builder/arforms-form-builder.php' );
										$arm_arforms_install_activate = 1;
									}
								}
							}
						}
					}
				}
			
				if ( ! empty( $arm_arforms_install_activate ) && $arm_arforms_install_activate == 1 ) {
					$response = array(
						'type' => 'success',
						'msg'  => esc_html__('ARForms Successfully installed.', 'ARMember' ),
					);
				}
			}
			
			echo json_encode( $response );
			die();
			
		}
		function arm_get_arprice_plugin_func() {
			global $wpdb, $ARMember, $arm_capabilities_global;
			$response = array(
				'type' => 'error',
				'msg'  => esc_html__( 'Sorry, Something went wrong. Please try again.', 'ARMember' ),
			);

			$ARMember->arm_check_user_cap( $arm_capabilities_global['arm_growth_plugins'], '1',1); //phpcs:ignore --Reason:Verifying nonce

			if ( isset( $_POST['action'] ) && $_POST['action'] == 'arm_get_arprice' ) { //phpcs:ignore
				$arm_arprice_install_activate = 1; 
				if ( ! file_exists( WP_PLUGIN_DIR . '/arprice-responsive-pricing-table/arprice-responsive-pricing-table.php' ) ) {
        
					if ( ! function_exists( 'plugins_api' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
					}
					$response = plugins_api(
						'plugin_information',
						array(
							'slug'   => 'arprice-responsive-pricing-table',
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
						
						if ( ! empty( $source ) ) {
							if ( $upgrader->install( $source ) === true ) {
								activate_plugin( 'arprice-responsive-pricing-table/arprice-responsive-pricing-table.php' );
								$arm_arprice_install_activate = 1; 
							}
						}
					} else {
						$source_url = 'https://www.arpriceplugin.com/arp_misc/arprice-pricing-table/arprice-pricing-table-latest.zip';
						$get_custom_response = wp_remote_get( $source_url, array( 'method' => 'GET') );
						if(!is_wp_error($get_custom_response)) {
							$get_custom_response_body = json_decode(wp_remote_retrieve_body($get_custom_response));
							if(is_object($get_custom_response_body) && !empty($get_custom_response_body))
							{
								if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
									require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
								}
								$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
								$source   = !empty( $get_custom_response_body->download_link ) ? $get_custom_response_body->download_link : '';
								
								if ( ! empty( $source ) ) {
									if ( $upgrader->install( $source ) === true ) {
										activate_plugin( 'arprice-responsive-pricing-table/arprice-responsive-pricing-table.php' );
										$arm_arprice_install_activate = 1;
									}
								}
							}
						}
					}
				}
				
				if ( ! empty( $arm_arprice_install_activate ) && $arm_arprice_install_activate == 1 ) {
					$response = array(
						'type' => 'success',
						'msg'  => esc_html__('ARPrice Successfully installed.', 'ARMember' ),
					);
				}
			}
			
			echo json_encode( $response );
			die();
			
		}

        function arm_update_entries_data_after_user_add($user_id, $posted_data){
            global $wpdb, $ARMember, $arm_payment_gateways;
            if(!empty($user_id) && !empty($posted_data) && is_array($posted_data)){
                $arm_entry_id = !empty($posted_data['arm_entry_id']) ? $posted_data['arm_entry_id'] : 0;
                if(!empty($arm_entry_id)){
                    $updt_qur_flag = 0;
                    $entry_data = $arm_payment_gateways->arm_get_entry_data_by_id($arm_entry_id);
                    $entry_values = !empty($entry_data['arm_entry_value']) ? maybe_unserialize($entry_data['arm_entry_value']) : array();
                    if(!empty($entry_values) && isset($entry_values['user_pass'])){
                        unset($entry_values['user_pass']);
                        $updt_qur_flag = 1;
                    }
                    if(!empty($entry_values) && isset($entry_values['repeat_pass'])){
                        unset($entry_values['repeat_pass']);
                        $updt_qur_flag = 1;
                    }

                    if(!empty($updt_qur_flag))
                    {
                        $arm_updated_entry_values = maybe_serialize($entry_values);
                        $wpdb->update($ARMember->tbl_arm_entries, array('arm_user_id' => $user_id, 'arm_entry_value' => $arm_updated_entry_values), array('arm_entry_id' => $arm_entry_id));
                    }
                }
            }
        }

        function arm_clear_debug_logs_data()
        {
            if(!empty($_POST) && !empty($_POST['arm_clear_debug_log_item']))//phpcs:ignore
            {
                global $wpdb, $ARMember, $arm_capabilities_global;

                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_general_settings'], '1',1);

                $arm_clear_debug_log_item = sanitize_text_field( $_POST['arm_clear_debug_log_item'] );//phpcs:ignore

                $arm_general_debug_log_selector_arr = array('cron','email');
                $arm_general_debug_log_selector_arr = apply_filters('arm_debug_general_log_selector_external', $arm_general_debug_log_selector_arr);

                if($arm_clear_debug_log_item=='optins' || in_array( $arm_clear_debug_log_item, $arm_general_debug_log_selector_arr ) )
                {

                    $arm_clear_debu_log_where_qur = "";
                    if($arm_clear_debug_log_item=='optins')
                    {
                        foreach($arm_general_debug_log_selector_arr as $arm_general_debug_log_selector_val)
                        {
                            if( empty( $arm_clear_debu_log_where_qur ) )
                            {
                                $arm_clear_debu_log_where_qur = $wpdb->prepare(" `arm_general_log_event`!=%s ",$arm_general_debug_log_selector_val);
                            }
                            else
                            {
                                $arm_clear_debu_log_where_qur .= $wpdb->prepare(" AND `arm_general_log_event`!=%s ",$arm_general_debug_log_selector_val);
                            }
                        }

                    }
                    else {
                        $arm_clear_debu_log_where_qur = $wpdb->prepare(" arm_general_log_event=%s ",$arm_clear_debug_log_item);
                    }
                    
                    $tbl_arm_debug_general_log = $ARMember->tbl_arm_debug_general_log;

                    //If data exists into general debug log table then delete from that table.
                    $wpdb->query( $wpdb->prepare( "DELETE FROM {$tbl_arm_debug_general_log} WHERE {$arm_clear_debu_log_where_qur} " ) );//phpcs:ignore --Reason $tbl_arm_debug_general_log is a table name
                }
                else 
                {
                    $tbl_arm_debug_payment_log = $ARMember->tbl_arm_debug_payment_log;

                    //If data exists into payment debug log table then delete from that table.
                    $arm_payment_log_gateway_where_qur = $wpdb->prepare(" arm_payment_log_gateway=%s ",$arm_clear_debug_log_item);
                    $wpdb->query( $wpdb->prepare( "DELETE FROM {$tbl_arm_debug_payment_log} WHERE {$arm_payment_log_gateway_where_qur} " ) );//phpcs:ignore --Reason $tbl_arm_debug_payment_log is a table name 
                }

                $response = array('type' => 'success', 'msg' => esc_html__('Debug Logs cleared successfully', 'ARMember'));
                echo json_encode($response);
                die();
            }
        }

        function arm_save_debug_logs_settings()
        {
            global $wpdb, $ARMember, $arm_payment_gateways, $arm_email_settings, $arm_capabilities_global;
            if(!empty($_POST))//phpcs:ignore
            {
                /*
                * Update payment gateway settings for debug log
                */
                    $ARMember->arm_check_user_cap( $arm_capabilities_global['arm_manage_general_settings'], '1',1); //phpcs:ignore --Reason:Verifying nonce

                    $arm_payment_gateways = get_option('arm_payment_gateway_settings');
                    $posted_data = array_map( array( $ARMember, 'arm_recursive_sanitize_data'), $_POST ); //phpcs:ignore
                    $arm_posted_payment_gateway_data = !empty($posted_data['payment_gateway_settings']) ? $posted_data['payment_gateway_settings'] : array();
                    
                    foreach($arm_payment_gateways as $arm_payment_gateway_key => $arm_payment_gateway_val)
                    {
                        if(!empty($arm_posted_payment_gateway_data[$arm_payment_gateway_key]['debug_log']))
                        {
                            $arm_payment_gateways[$arm_payment_gateway_key]['payment_debug_logs'] = 1;
                        }
                        else
                        {
                            $arm_payment_gateways[$arm_payment_gateway_key]['payment_debug_logs'] = 0;    
                        }
                    }

                    $arm_payment_gateways = arm_array_map($arm_payment_gateways);
                    update_option('arm_payment_gateway_settings', $arm_payment_gateways);

                /*
                * Update Integrations settings for debug log
                */
                $arm_get_integrations = apply_filters('arm_add_integration_debug_log_section',array());
                if (!empty($arm_get_integrations)) {
                    $arm_integrations = array();
                    $arm_posted_integration_data = !empty($posted_data['arm_integration_settings']) ? $posted_data['arm_integration_settings'] : array();

                    $arm_integrations = arm_array_map($arm_posted_integration_data);
                    update_option('arm_integration_settings', $arm_integrations);
                }
                    
                /*
                * Update optins settings for debug log
                */
                if($arm_email_settings->isOptInsFeature)
                {
                    $arm_optins_debug_log = !empty($posted_data['arm_optins_debug_log']) ? 1 : 0;
                    update_option('arm_optins_debug_log', $arm_optins_debug_log);
                }

                /*
                * Update cron log option
                */
                $arm_is_cron_log_enabled = !empty($posted_data['arm_cron_debug_log']) ? 1 : 0;
                update_option('arm_cron_debug_log', $arm_is_cron_log_enabled);


                /*
                * Update email log option                
                */

                $arm_is_email_log_enabled = !empty($posted_data['arm_email_debug_log']) ? 1 : 0;
                update_option('arm_email_debug_log', $arm_is_email_log_enabled);

                $response = array('type' => 'success', 'msg' => esc_html__('Debug Settings Saved Successfully', 'ARMember'));
                echo json_encode($response);
                die();
            }
        }

        function arm_user_plan_action($post_data=array(),$arm_return_data=false) {
            global $wpdb, $ARMember, $arm_member_forms, $arm_manage_communication, $is_multiple_membership_feature, $arm_subscription_plans, $arm_members_class, $arm_global_settings, $arm_capabilities_global, $arm_pay_per_post_feature, $arm_subscription_cancel_msg;
            if(empty($post_data)){
                $post_data = $_POST;//phpcs:ignore
            }
            
            $response = array('type' => 'error', 'msg' => esc_html__("Sorry, Something went wrong. Please try again.", 'ARMember'));
            
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1);

            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
            if ($post_data['arm_action'] == 'add') {
                $user_ID = isset($post_data['user_id']) ? intval($post_data['user_id']) : 0;

                do_action('arm_modify_content_on_plan_change', $post_data, $user_ID);

                if (!empty($user_ID)) {
                    if (!isset($post_data['arm_user_plan'])) {
                        $post_data['arm_user_plan'] = 0;
                    } else {
                        if (is_array($post_data['arm_user_plan'])) {
                            foreach ($post_data['arm_user_plan'] as $key => $mpid) {
                                if (empty($mpid)) {
                                    unset($post_data['arm_user_plan'][$key]);
                                } else {
                                    $post_data['arm_subscription_start_' . $mpid] = isset($post_data['arm_subscription_start_date'][$key]) ? $post_data['arm_subscription_start_date'][$key] : '';
                                }
                            }
                            unset($post_data['arm_subscription_start_date']);
                            $post_data['arm_user_plan'] = array_values($post_data['arm_user_plan']);
                        }
                    }
                    unset($post_data['arm_action']);
                    $post_data['action'] = 'update_member';

                    $old_plan_ids = get_user_meta($user_ID, 'arm_user_plan_ids', true);
                    $old_plan_ids = !empty($old_plan_ids) ? $old_plan_ids : array();
                    $old_plan_id = isset($old_plan_ids[0]) ? $old_plan_ids[0] : 0;
                    if (!empty($old_plan_ids)) {
                        foreach ($old_plan_ids as $plan_id) {
                            $field_name = "arm_subscription_expiry_date_" . $plan_id . "_" . $user_ID;
                            if (isset($post_data[$field_name])) {
                                unset($post_data[$field_name]);
                            }
                        }
                    }
                    unset($post_data['user_id']);

                    $arm_old_suscribed_plans = "";


                    $is_paid_post = (!empty($_POST['arm_paid_post_request']) && ($_POST['arm_paid_post_request'] == 1)) ? 1 : 0; //phpcs:ignore
                    $is_gift_plan = (!empty($_POST['arm_gift_plan_request']) && ($_POST['arm_gift_plan_request'] == 1)) ? 1 : 0; //phpcs:ignore

                    if($is_gift_plan || $is_paid_post || $is_multiple_membership_feature->isMultipleMembershipFeature)
                    {
                        if(array_key_exists('arm_user_plan', $_POST))//phpcs:ignore
                        {
                            //Get Old Data Of Subscribed Plans
                            $arm_old_suscribed_plans = get_user_meta($user_ID, 'arm_user_plan_ids', true);

                            $arm_new_subscribed_data = array();
                            array_push($arm_new_subscribed_data, $post_data['arm_user_plan'][0]);
                            if(!empty($arm_old_suscribed_plans))
                            {
                                foreach($arm_old_suscribed_plans as $value)
                                {
                                    if(!in_array($value, $arm_new_subscribed_data))
                                    {
                                        array_push($arm_new_subscribed_data, $value);
                                    }
                                }
                            }


                            $post_data['arm_user_plan'] = $arm_new_subscribed_data;
                        }
                    }
                    $admin_save_flag = 1;
                    do_action('arm_member_update_meta', $user_ID, $post_data, $admin_save_flag);

                    if (isset($post_data['arm_user_plan']) && !empty($post_data['arm_user_plan'])) {
                        if ((is_array($post_data['arm_user_plan']) && $is_multiple_membership_feature->isMultipleMembershipFeature) || ($is_paid_post) || ($is_gift_plan)) {
                            $old_plan_ids = array_intersect($post_data['arm_user_plan'], $old_plan_ids);
                            foreach ($post_data['arm_user_plan'] as $plan_id) {
                                if (!in_array($plan_id, $old_plan_ids)) {
                                    $arm_manage_communication->membership_communication_mail('on_new_subscription', $user_ID, $plan_id);
                                    do_action('arm_after_user_plan_change_by_admin', $user_ID, $plan_id);
                                }
                            }
                        } else {
                            if ($old_plan_id != 0 && $old_plan_id != '') {
                                if ($old_plan_id != $post_data['arm_user_plan']) {
                                    $arm_manage_communication->membership_communication_mail('on_change_subscription_by_admin', $user_ID, $post_data['arm_user_plan']);
                                }
                            } else {
                                $arm_manage_communication->membership_communication_mail('on_new_subscription', $user_ID, $post_data['arm_user_plan']);
                            }
                            do_action('arm_after_user_plan_change_by_admin', $user_ID, $post_data['arm_user_plan']);
                        }
                    }
                    
                    $popup_plan_content = "";
                    if($is_paid_post)
                    {
                        $popup_plan_content = $arm_pay_per_post_feature->arm_get_paid_post_modal_plans($user_ID, 1, 5);
                        $response = array('type' => 'success', 'msg' => esc_html__("Paid Post added successfully.", 'ARMember'), 'content' => $popup_plan_content);
                    }
                    else
                    {
                        $popup_plan_content = $this->arm_get_user_all_plan_details($user_ID, true, $is_paid_post, $is_gift_plan);
                        $response = array('type' => 'success', 'msg' => esc_html__("Plan added successfully.", 'ARMember'), 'content' => $popup_plan_content);
                    }

                    $response = apply_filters('arm_modify_admin_plan_add_response', $response, $user_ID, $popup_plan_content, $post_data);
                }
            } else if ($post_data['arm_action'] == 'delete') {
                $user_ID = intval($post_data['user_id']);
                $user = get_userdata($user_ID);
                $plan_id = intval($post_data['plan_id']);

                $planData = get_user_meta($user_ID, 'arm_user_plan_' . $plan_id, true);
                $userPlanDatameta = !empty($planData) ? $planData : array();
                $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                $plan_detail = $planData['arm_current_plan_detail'];
                $planData['arm_cencelled_plan'] = 'yes';
                update_user_meta($user_ID, 'arm_user_plan_' . $plan_id, $planData);
                update_user_meta($user_ID, 'arm_user_old_plan_id', array($plan_id));

                $is_paid_post = (!empty($_POST['arm_paid_post_request']) && ($_POST['arm_paid_post_request'] == 1)) ? 1 : 0; //phpcs:ignore
                $is_gift_plan = (!empty($_POST['arm_gift_plan_request']) && ($_POST['arm_gift_plan_request'] == 1)) ? 1 : 0; //phpcs:ignore
                if($is_paid_post)
                {
                    //Update Post IDs Meta
                    $arm_user_post_ids = get_user_meta($user_ID, 'arm_user_post_ids', true);
                    unset($arm_user_post_ids[$plan_id]);
                    update_user_meta($user_ID, 'arm_user_post_ids', $arm_user_post_ids);
                }

                if (!empty($plan_detail)) {
                    $planObj = new ARM_Plan(0);
                    $planObj->init((object) $plan_detail);
                } else {
                    $planObj = new ARM_Plan($plan_id);
                }
                if ($planObj->exists() && $planObj->is_recurring()) {
                    do_action('arm_cancel_subscription_gateway_action', $user_ID, $plan_id);
                }

                if(!empty($arm_subscription_cancel_msg))
                {
                    $common_messages = isset($arm_global_settings->common_message) ? $arm_global_settings->common_message : array();
                    $arm_subscription_error = isset($common_messages['arm_payment_gateway_subscription_failed_error_msg']) ? $common_messages['arm_payment_gateway_subscription_failed_error_msg'] : esc_html__("Membership plan couldn't cancel due to not canceled subscription from payment gateway.", 'ARMember');
                    $response = array('type' => 'error', 'msg' => $arm_subscription_error, 'content' => '');
                }
                else
                {
                    $arm_subscription_plans->arm_add_membership_history($user_ID, $plan_id, 'cancel_subscription', array(), 'admin');
                    do_action('arm_cancel_subscription', $user_ID, $plan_id);
                    $arm_subscription_plans->arm_clear_user_plan_detail($user_ID, $plan_id, $is_paid_post);

                    $cancel_plan_action = isset($planObj->options['cancel_plan_action']) ? $planObj->options['cancel_plan_action'] : 'immediate';

                    $user_future_plans = get_user_meta($user_ID, 'arm_user_future_plan_ids', true);
                    $user_future_plans = !empty($user_future_plans) ? $user_future_plans : array();

                    if (!empty($user_future_plans)) {
                        if (in_array($plan_id, $user_future_plans)) {
                            unset($user_future_plans[array_search($plan_id, $user_future_plans)]);
                            update_user_meta($user_ID, 'arm_user_future_plan_ids', array_values($user_future_plans));
                        }
                    }


                    $popup_plan_content = "";
                    if($is_paid_post)
                    {
                        $popup_plan_content = $arm_pay_per_post_feature->arm_get_paid_post_modal_plans($user_ID, 1, 5);
                        $response = array('type' => 'success', 'msg' => esc_html__("Paid Post deleted successfully.", 'ARMember'), 'content' => $popup_plan_content);
                    }
                    else
                    {
                        $popup_plan_content = $this->arm_get_user_all_plan_details($user_ID, true, $is_paid_post, $is_gift_plan);
                        $response = array('type' => 'success', 'msg' => esc_html__("Plan deleted successfully.", 'ARMember'), 'content' => $popup_plan_content);
                    }

                    $response = apply_filters('arm_modify_admin_plan_delete_response', $response, $user_ID, $popup_plan_content, $post_data);
                }

            } else if ($post_data['arm_action'] == 'status') {
                $user_ID = intval($post_data['user_id']);
                $user = get_userdata($user_ID);
                $plan_id = intval($post_data['plan_id']);

                $user_suspended_plans = get_user_meta($user_ID, 'arm_user_suspended_plan_ids', true);
                $user_suspended_plans = !empty($user_suspended_plans) ? $user_suspended_plans : array();

                if (!empty($user_suspended_plans)) {
                    if (in_array($plan_id, $user_suspended_plans)) {
                        unset($user_suspended_plans[array_search($plan_id, $user_suspended_plans)]);
                        update_user_meta($user_ID, 'arm_user_suspended_plan_ids', array_values($user_suspended_plans));

                        //update user meta for the keep record for admin has removed suspended plan.
                        update_user_meta($user_ID, 'arm_admin_user_remove_suspended_plan_'.$plan_id, current_time('mysql'));

                        $userPlanDatameta = get_user_meta($user_ID, 'arm_user_plan_' . $plan_id, true);
                        $planDataCheck = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                        if(!empty($planDataCheck) &&  !empty($planDataCheck['arm_payment_mode']) && $planDataCheck['arm_payment_mode'] != 'manual_subscription' )
                        {
                            $payment_cycle = isset($planDataCheck['arm_payment_cycle']) ? $planDataCheck['arm_payment_cycle'] : 0;
                            $completed_recurrence = $planDataCheck['arm_completed_recurring'];
                            $completed_recurrence++;
                            $planDataCheck['arm_completed_recurring'] = $completed_recurrence;
                            update_user_meta($user_ID, 'arm_user_plan_' . $plan_id, $planDataCheck); //necessary to update this meta.

                            $arm_next_payment_date = $arm_members_class->arm_get_next_due_date($user_ID, $plan_id, false, $payment_cycle);
                            $planDataCheck['arm_next_due_payment'] = $arm_next_payment_date;
                            
                            update_user_meta($user_ID, 'arm_user_plan_' . $plan_id, $planDataCheck);
                        }
                        
                        //Hook for plan activation by admin
                        do_action('arm_after_user_plan_change_by_admin', $user_ID, $plan_id);
                    }
                }


                $is_paid_post = (!empty($_POST['arm_paid_post_request']) && ($_POST['arm_paid_post_request'] == 1)) ? 1 : 0; //phpcs:ignore

                $popup_plan_content = "";
                if($is_paid_post)
                {
                    $popup_plan_content = $arm_pay_per_post_feature->arm_get_paid_post_modal_plans($user_ID, 1, 5);
                    $response = array('type' => 'success', 'msg' => esc_html__("Paid Post status changed successfully.", 'ARMember'), 'content' => $popup_plan_content);
                }
                else
                {
                    $is_gift_plan = (!empty($_POST['arm_gift_plan_request']) && ($_POST['arm_gift_plan_request'] == 1)) ? 1 : 0;//phpcs:ignore
                    $popup_plan_content = $this->arm_get_user_all_plan_details($user_ID, true, $is_paid_post, $is_gift_plan);
                    $response = array('type' => 'success', 'msg' => esc_html__("Plan status changed successfully.", 'ARMember'), 'content' => $popup_plan_content);
                }

                $response = apply_filters('arm_modify_admin_plan_add_response', $response, $user_ID, $popup_plan_content, $post_data);
                
            } else if ($post_data['arm_action'] == 'edit') {
                $is_paid_post = (!empty($_POST['arm_paid_post_request']) && ($_POST['arm_paid_post_request'] == 1)) ? 1 : 0; //phpcs:ignore
                
                $user_ID = intval($post_data['user_id']);

                do_action('arm_plan_change_check_group_membership', $post_data, $user_ID);

                $arm_changed_expiry_date_plan = get_user_meta($user_ID, 'arm_changed_expiry_date_plans', true);
                $arm_changed_expiry_date_plan = !empty($arm_changed_expiry_date_plan) ? $arm_changed_expiry_date_plan : array();
                if (isset($post_data['expiry_date']) && !empty($post_data['expiry_date'])) {
                    $user_plan_data = get_user_meta($user_ID, 'arm_user_plan_' . $post_data['plan_id'], true);

                    if ($user_plan_data['arm_expire_plan'] != strtotime($post_data['expiry_date'])) {
                        if (!in_array($post_data['plan_id'], $arm_changed_expiry_date_plan)) {
                            $arm_changed_expiry_date_plan[] = intval($post_data['plan_id']);
                        }
                    }
                    update_user_meta($user_ID, 'arm_changed_expiry_date_plans', $arm_changed_expiry_date_plan);
                    $user_plan_data['arm_expire_plan'] = strtotime(sanitize_text_field($post_data['expiry_date']));
                    update_user_meta($user_ID, 'arm_user_plan_' . $post_data['plan_id'], $user_plan_data);


                    $popup_plan_content = "";
                    if($is_paid_post)
                    {
                        $popup_plan_content = $arm_pay_per_post_feature->arm_get_paid_post_modal_plans($user_ID, 1, 5);
                    }
                    else
                    {
                        $is_gift_plan = (!empty($_POST['arm_gift_plan_request']) && ($_POST['arm_gift_plan_request'] == 1)) ? 1 : 0;//phpcs:ignore
                        $popup_plan_content = $this->arm_get_user_all_plan_details($user_ID, true, $is_paid_post, $is_gift_plan);
                    }
                    $popup_plan_content = apply_filters('arm_modify_admin_edit_popup_plan_content', $popup_plan_content, $user_ID, $post_data);
                    $response = array('type' => 'success', 'msg' => esc_html__("Expiry date updated successfully.", 'ARMember'), 'content' => $popup_plan_content);
                }
            }

            if (isset($response['type']) && $response['type'] == 'success' && $user_ID > 0) 
            {
                $userPlanIDs = get_user_meta($user_ID, 'arm_user_plan_ids', true);

        		if(!empty($userPlanIDs))
        		{
        			$userPostIDs = get_user_meta($user_ID, 'arm_user_post_ids', true);
        	                foreach($userPlanIDs as $arm_plan_key => $arm_plan_val)
        	                {
        	                    if(isset($userPostIDs[$arm_plan_val]) && in_array($userPostIDs[$arm_plan_val], $userPostIDs))
        	                    {
        	                        unset($userPlanIDs[$arm_plan_key]);
        	                    }
        	                }
				$userPlanIDs = apply_filters('arm_modify_plan_ids_externally',$userPlanIDs,$user_ID);
        		}
                $arm_all_user_plans = $userPlanIDs;
                $arm_future_user_plans = get_user_meta($user_ID, 'arm_user_future_plan_ids', true);
                
                if (!empty($arm_future_user_plans) && is_array($arm_future_user_plans) && is_array($userPlanIDs)) {
                    $arm_all_user_plans = array_merge($userPlanIDs, $arm_future_user_plans);
                }
                $arm_user_plans = '';
                $plan_names = array();
                $subscription_effective_from = array();
                if (!empty($arm_all_user_plans) && is_array($arm_all_user_plans)) {
                    foreach ($arm_all_user_plans as $userPlanID) {
                        $plan_data = get_user_meta($user_ID, 'arm_user_plan_' . $userPlanID, true);

                        $userPlanDatameta = !empty($plan_data) ? $plan_data : array();
                        $plan_data = shortcode_atts($defaultPlanData, $userPlanDatameta);
                        $subscription_effective_from_date = $plan_data['arm_subscr_effective'];
                        $change_plan_to = $plan_data['arm_change_plan_to'];

                        $plan_names[$userPlanID] = $arm_subscription_plans->arm_get_plan_name_by_id($userPlanID);
                        $subscription_effective_from[] = array('arm_subscr_effective' => $subscription_effective_from_date, 'arm_change_plan_to' => $change_plan_to);
                    }
                }

                if ($arm_pay_per_post_feature->isPayPerPostFeature) {
                    $response['paid_post'] = '1';
                }
                else
                {
                    $response['paid_post'] = '0';
                }

                if ($is_multiple_membership_feature->isMultipleMembershipFeature) {
                    $response['multiple_membership'] = '1';

                    $arm_user_plans = '<div class="arm_min_width_120">';
                    $arm_user_plans .= "<a href='javascript:void(0)'  id='arm_show_user_more_plans_" . esc_attr($user_ID) . "' class='arm_show_user_more_plans' data-id='" . esc_attr($user_ID) . "'>";

                    if (!empty($arm_all_user_plans) && is_array($arm_all_user_plans)) {
                        foreach ($arm_all_user_plans as $plan_id) {
                            $plan_color_id = ($plan_id > 10) ? intval($plan_id / 10) : $plan_id;
                            $plan_color_id = ($plan_color_id > 10) ? intval($plan_color_id / 10) : $plan_color_id;
                            $arm_user_plans .= "<span class='armhelptip arm_user_plan_circle arm_user_plan_" . $plan_color_id . "' title='" . stripslashes_deep($plan_names[$plan_id]) . "' >";
                            $plan_name = str_replace('-', '', stripslashes_deep($plan_names[$plan_id]));
                            $words = explode(" ", $plan_name);
                            $plan_name = '';
                            foreach ($words as $w) {
                                $w = preg_replace('/[^A-Za-z0-9\-]/', '', $w);
                                $plan_name .= mb_substr($w, 0, 1, 'utf-8');
                            }
                            $plan_name = strtoupper($plan_name);
                            $arm_user_plans .= substr($plan_name, 0, 2);
                            $arm_user_plans .= "</span>";
                        }
                    }
                    $arm_user_plans .= "</a></div>";
                    $response['membership_plan'] = $arm_user_plans;
                } else {
                    $response['multiple_membership'] = '0';
                    $auser = new WP_User($user_ID);
                    $u_role = array_shift($auser->roles);
                    $user_roles = get_editable_roles();
                    if (!empty($user_roles[$u_role]['name'])) {
                        $arm_user_role = $user_roles[$u_role]['name'];
                    } else {
                        $arm_user_role = '-';
                    }
                    $response['user_role'] = $arm_user_role;

                    $memberTypeText = $arm_members_class->arm_get_member_type_text($user_ID);
                    $response['membership_type'] = $memberTypeText;

                    $plan_name = (!empty($plan_names)) ? implode(',', $plan_names) : '-';
                    $response['membership_plan'] = '<span class="arm_user_plan_' . esc_attr($user_ID) . '">' . esc_html($plan_name) . '</span>';

                    if (!empty($subscription_effective_from)) {
                        foreach ($subscription_effective_from as $subscription_effective) {
                            $subscr_effective = $subscription_effective['arm_subscr_effective'];
                            $change_plan = $subscription_effective['arm_change_plan_to'];
                            $change_plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($change_plan);
                            if (!empty($change_plan) && $subscr_effective > strtotime($nowDate)) {
                                $response['membership_plan'] .= '<div>' . esc_html($change_plan_name) . '<br/> (' . esc_html__('Effective from', 'ARMember') . ' ' . date_i18n($date_format, $subscr_effective) . ')</div>';
                            }
                        }
                    }
                }

                if (isset($post_data['arm_action']) && $post_data['arm_action'] == 'delete') {
                    do_action('arm_after_cancel_subscription', $user_ID, $planObj, $cancel_plan_action, $planData);
                }
            }
	    
            if(!empty($arm_return_data)){
                return $response;
            }
            echo json_encode($response);
            exit;
        }

        function arm_get_user_plan_failed_payment_details() {
            global $ARMember, $wpdb, $arm_global_settings, $arm_payment_gateways,$ARMember,$arm_capabilities_global;
            if (isset($_POST['user_id']) && !empty($_POST['user_id']) && isset($_POST['plan_id']) && !empty($_POST['plan_id'])) {//phpcs:ignore
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1); //phpcs:ignore --Reason:Verifying nonce
                $user_id = intval( $_POST['user_id'] );//phpcs:ignore
                $plan_id = intval( $_POST['plan_id'] );//phpcs:ignore
                $plan_name = sanitize_text_field( $_POST['plan_name'] );//phpcs:ignore
                $global_currency = $arm_payment_gateways->arm_get_global_currency();
                $start = sanitize_text_field( $_POST['start'] );//phpcs:ignore
                $date_format = $arm_global_settings->arm_get_wp_date_format();
                if (!empty($start)) {
                    $start = date('Y-m-d H:i:s', $start);
                }
                $arm_failed_transaction = $wpdb->get_results($wpdb->prepare("SELECT `arm_payment_date`, `arm_amount`, `arm_currency`, `arm_payment_gateway`, `arm_payment_mode` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_user_id`=%d AND `arm_plan_id`=%d AND `arm_payment_date`>=%s AND `arm_transaction_status` =%s", $user_id, $plan_id, $start, 'failed')); //phpcs:ignore --Reason $ARMember->tbl_arm_payment_log is a table name

                $return = '';
                $return .= '<table class="form-table arm_failed_login_history_table">';
                $return .= '<tr>';
                $return .= '<td>' . esc_html__('Payment Date', 'ARMember') . '</td>';
                $return .= '<td>' . esc_html__('Amount', 'ARMember') . '</td>';
                $return .= '<td>' . esc_html__('Payment Mode', 'ARMember') . '</td>';
                $return .= '<td>' . esc_html__('Payment Gateway', 'ARMember') . '</td>';
                $return .= '</tr>';

                if (!empty($arm_failed_transaction)) {
                    foreach ($arm_failed_transaction as $arm_failed_transaction_data) {


                        $return .= '<tr class="arm_failed_login_history_data">';
                        $return .= '<td>' . date_i18n($date_format, strtotime($arm_failed_transaction_data->arm_payment_date)) . '</td>';
                        $return .= '<td>' . $arm_payment_gateways->arm_amount_set_separator($arm_failed_transaction_data->arm_currency, $arm_failed_transaction_data->arm_amount) . ' ' . strtoupper($arm_failed_transaction_data->arm_currency) . '</td>';

                        if ($arm_failed_transaction_data->arm_payment_gateway == '') {
                            $payment_gateway = esc_html__('Manual', 'ARMember');
                        } else {
                            $payment_gateway = $arm_payment_gateways->arm_gateway_name_by_key($arm_failed_transaction_data->arm_payment_gateway);
                        }

                        if ($arm_failed_transaction_data->arm_payment_mode == 'manual_subscription') {
                            $arm_payment_mode = esc_html__('Manual', 'ARMember');
                        } else {
                            $arm_payment_mode = esc_html__('Auto Debit', 'ARMember');
                        }
                        $return .= '<td>' . $arm_payment_mode . '</td>';
                        $return .= '<td>' . $payment_gateway . '</td>';
                        $return .= '</tr>';
                    }
                }

                $return .= '<table>';

                echo $return . '^|^' . $plan_name; //phpcs:ignore
                die;
            }
        }

        function arm_get_user_all_plan_details($user_id = 0, $is_ajax = false, $is_paid_post = false, $is_gift_plan = false) {

            global $arm_global_settings, $ARMember, $arm_capabilities_global, $arm_pay_per_post_feature;

            $arm_common_date_format = $arm_global_settings->arm_check_common_date_format(get_option('date_format'));
            
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1);//phpcs:ignore --Reason:Verifying nonce
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $user_name = '';
            if (isset($_POST['user_id']) && $_POST['user_id'] != '') {//phpcs:ignore
                $user_id = intval($_POST['user_id']);//phpcs:ignore
                $arm_user_info = get_userdata($user_id);
                $user_name = $arm_user_info->user_login;
                $u_roles = $arm_user_info->roles;
            }
            global $arm_global_settings, $arm_subscription_plans, $is_multiple_membership_feature;
            $return = '';
            if (!empty($user_id)) {
                $planIDs = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $planIDs = !empty($planIDs) ? $planIDs : array();

                $user_future_plan_ids = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
                $user_future_plan_ids = !empty($user_future_plan_ids) ? $user_future_plan_ids : array();

                $futurePlanIDs = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
                $futurePlanIDs = !empty($futurePlanIDs) ? $futurePlanIDs : array();

                $plansLists = "";

                if($is_paid_post)
                {
                    $all_subscription_plans = $arm_subscription_plans->arm_get_plans_data();

                    $all_plan_ids = array();
                    if (!empty($all_subscription_plans)) {
                        foreach ($all_subscription_plans as $p) {
                            if($p['arm_subscription_plan_post_id'] != 0)
                            {
                                $all_plan_ids[] = $p['arm_subscription_plan_id'];
                            }
                        }
                    }


                    $plansLists = '<li data-label="' . esc_attr__('Select Post', 'ARMember') . '" data-value="">' . esc_html__('Select Post', 'ARMember') . '</li>';
                    if (!empty($all_subscription_plans)) {
                        foreach ($all_subscription_plans as $p) {
                            if($p['arm_subscription_plan_post_id'] != 0 && (!in_array($p['arm_subscription_plan_id'], $planIDs)))
                            {
                                $p_id = $p['arm_subscription_plan_id'];
                                $plansLists .= '<li data-label="' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '" data-value="' . $p_id . '">' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '</li>';
                            }
                        }
                    }
                }
                else
                {
                    $all_active_plans = $arm_subscription_plans->arm_get_all_active_subscription_plans();

                    $all_plan_ids = array();
                    if (!empty($all_active_plans)) {
                        foreach ($all_active_plans as $p) {
                            $all_plan_ids[] = $p['arm_subscription_plan_id'];
                        }
                    }
                    $plan_to_show = array_diff($all_plan_ids, $planIDs);
                    $plan_to_show = array_diff($plan_to_show, $futurePlanIDs);



                    $plansLists = '<li data-label="' . esc_attr__('Select Plan', 'ARMember') . '" data-value="">' . esc_html__('Select Plan', 'ARMember') . '</li>';
                    if (!empty($all_active_plans)) {
                        foreach ($all_active_plans as $p) {
                            $p_id = $p['arm_subscription_plan_id'];
                            if (in_array($p_id, $plan_to_show)) {
                                $plansLists .= '<li data-label="' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '" data-value="' . $p_id . '">' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '</li>';
                            }
                        }
                    }
                }

                if ($is_multiple_membership_feature->isMultipleMembershipFeature || $is_paid_post) {
                    $return .= ($is_paid_post) ? '<div class="arm_add_new_item_box arm_add_new_plan"><a id="arm_add_plan_to_user" class="greensavebtn arm_save_btn" href="javascript:void(0)" ><img align="absmiddle" src="' . MEMBERSHIP_IMAGES_URL . '/add_new_icon.png"><span> ' . esc_html__('Add Post', 'ARMember') . '</span></a></div>' : '<div class="arm_add_new_item_box arm_add_new_plan"><a id="arm_add_plan_to_user" class="greensavebtn arm_save_btn" href="javascript:void(0)" ><img align="absmiddle" src="' . MEMBERSHIP_IMAGES_URL . '/add_new_icon.png"><span> ' . esc_html__('Add Plan', 'ARMember') . '</span></a></div>';
                } else {
                    $return .= ($is_paid_post) ? '<div class="arm_add_new_item_box arm_add_new_plan"><a id="arm_change_plan_to_user" class="greensavebtn arm_save_btn" href="javascript:void(0)" ><img align="absmiddle" src="' . MEMBERSHIP_IMAGES_URL . '/add_new_icon.png"><span> ' . esc_html__('Change Post', 'ARMember') . '</span></a></div>' : '<div class="arm_add_new_item_box arm_add_new_plan"><a id="arm_change_plan_to_user" class="greensavebtn arm_save_btn" href="javascript:void(0)" ><img align="absmiddle" src="' . MEMBERSHIP_IMAGES_URL . '/add_new_icon.png"><span> ' . esc_html__('Change Plan', 'ARMember') . '</span></a></div>';
                }



                $return .= '<div class="popup_content_text arm_add_plan arm_text_align_center" style="display:none;">';
                $return .= '<div class="arm_edit_plan_wrapper arm_margin_top_10 arm_position_relative" >';
                $return .= ($is_paid_post) ? '<span class="arm_edit_plan_lbl">' . esc_html__('Select Post', 'ARMember') . '*</span> ' : '<span class="arm_edit_plan_lbl">' . esc_html__('Select Plan', 'ARMember') . '*</span> ';
                $return .= '<div class="arm_edit_field arm_max_width_500">';
                if ($is_multiple_membership_feature->isMultipleMembershipFeature || $is_paid_post) {
                    $return .= '<input type="hidden" class="arm_user_plan_change_input arm_user_plan_change_input_get_cycle" name="arm_user_plan[]" id="arm_user_plan" value="" data-manage-plan-grid="1"/>';
                } else {
                    $return .= '<input type="hidden" class="arm_user_plan_change_input arm_user_plan_change_input_get_cycle" name="arm_user_plan" id="arm_user_plan" value="" data-manage-plan-grid="1"/>';
                }
                $return .= '<dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_float_left" >';
                $return .= '<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
                $return .= '<dd><ul data-id="arm_user_plan">' . $plansLists . '</ul></dd>';
                $return .= '</dl>';
                $return .= ($is_paid_post) ? '<br/><span class="arm_error_select_plan error arm_invalid arm_text_align_left" style="display:none; ">' . esc_html__('Please select Post.', 'ARMember') . '</span>' : '<br/><span class="arm_error_select_plan error arm_invalid arm_text_align_left" style="display:none; ">' . esc_html__('Please select Plan.', 'ARMember') . '</span>';
                $return .= '</div>';
                $return .= '</div>';

                $return .= '<div class="arm_selected_plan_cycle arm_margin_top_10 arm_position_relative">';
                $return .= '</div>';

                $return .= '<div class="arm_margin_top_10 arm_position_relative" >';
                $return .= ($is_paid_post) ? '<span class="arm_edit_plan_lbl">' . esc_html__('Post Start Date', 'ARMember') . '</span>' : '<span class="arm_edit_plan_lbl">' . esc_html__('Plan Start Date', 'ARMember') . '</span>';
                $return .= '<div class="arm_edit_field">';
                if ($is_multiple_membership_feature->isMultipleMembershipFeature || $is_paid_post) {
                    $return .= '<input type="text" value="' . date($arm_common_date_format, strtotime(date('Y-m-d'))) . '"  data-date_format="'.$arm_common_date_format.'" name="arm_subscription_start_date[]" class="arm_datepicker arm_member_form_input arm_user_add_plan_date_picker arm_width_500 arm_min_width_500"  />';
                } else {
                    $return .= '<input type="text" value="' . date($arm_common_date_format, strtotime(date('Y-m-d'))) . '"  data-date_format="'.$arm_common_date_format.'" name="arm_subscription_start_date" class="arm_datepicker arm_member_form_input arm_user_add_plan_date_picker arm_width_500 arm_min_width_500"  />';
                }
                $return .= '</div>';
                $return .= '</div>';

                $return .= '<div  class="arm_position_relative arm_margin_top_10">';
                $return .= '<span class="arm_edit_plan_lbl">&nbsp;</span>';
                $return .= '<div class="arm_edit_field">';

                $arm_btn_save_class = ($is_paid_post) ? 'arm_member_add_paid_plan_save_btn' : 'arm_member_add_plan_save_btn';

                $return .= '<button class="'.$arm_btn_save_class.' arm_save_btn">' . esc_html__('Save', 'ARMember') . '</button>';

                if ($is_multiple_membership_feature->isMultipleMembershipFeature || $is_paid_post) {
                    $return .= '<button class="arm_add_plan_cancel_btn arm_cancel_btn" type="button">' . esc_html__('Close', 'ARMember') . '</button>';
                } else {
                    $return .= '<button class="arm_add_plan_cancel_single_btn arm_cancel_btn" type="button">' . esc_html__('Close', 'ARMember') . '</button>';
                }


                $return .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/arm_loader.gif" class="arm_loader_img_user_add_plan arm_submit_btn_loader" style="display:none;" width="24" height="24" />';
                $return .= '</div>';
                $return .= '</div>';

                $return .= '</div>';


                $user_plans = $planIDs;

                if ((!empty($u_roles) && $is_multiple_membership_feature->isMultipleMembershipFeature) || ($is_paid_post)) {
                    foreach ($u_roles as $ur) {
                        $return .= '<input type="hidden" name="roles[]" value="' . esc_attr($ur) . '"/>';
                    }
                }

                $return .= '<div class="arm_loading_grid arm_plan_loading_grid" style="display: none;"><img src="' . MEMBERSHIP_IMAGES_URL . '/loader.gif" alt="Loading.."></div>';
                $return .= '<table class="arm_user_edit_plan_table arm_text_align_center" cellspacing="1" style="width:calc(100% - 40px); border-left: 1px solid #eaeaea; margin: 20px; border-right: 1px solid #eaeaea;">';

                $return .= '<tr class="arm_user_plan_row arm_user_plan_head odd">';
                $return .= ($is_paid_post) ? '<th class="arm_edit_plan_name">' . esc_html__('Post Name', 'ARMember') . '</th>' : '<th class="arm_edit_plan_name">' . esc_html__('Membership Plan', 'ARMember') . '</th>';
                $return .= ($is_paid_post) ? '<th class="arm_edit_plan_type">' . esc_html__('Post Type', 'ARMember') . '</th>' : '<th class="arm_edit_plan_type">' . esc_html__('Plan Type', 'ARMember') . '</th>';
                $return .= '<th class="arm_edit_plan_start">' . esc_html__('Starts On', 'ARMember') . '</th>';
                $return .= '<th class="arm_edit_plan_expire">' . esc_html__('Expires On', 'ARMember') . '</th>';
                $return .= '<th class="arm_edit_plan_cycle_date">' . esc_html__('Cycle Date', 'ARMember') . '</th>';

                $return .= '<th class="arm_edit_plan_action">' . esc_html__('Remove', 'ARMember') . '</th>';
                $return .= '</tr>';

                if (!empty($user_future_plan_ids)) {

                    $all_user_plans = array_merge($user_plans, $user_future_plan_ids);
                } else {
                    $all_user_plans = $user_plans;
                }
                
                $all_user_plans = apply_filters('arm_modify_plan_ids_externally', $all_user_plans, $user_id);

                if (!empty($all_user_plans)) {

                    $count_plan = 0;
                    foreach ($all_user_plans as $uplans) {
                        $count_plan++;
                        $planData = get_user_meta($user_id, 'arm_user_plan_' . $uplans, true);

                        $arm_plan_condition = "";

                        if($arm_pay_per_post_feature->isPayPerPostFeature)
                        {
                            if($is_paid_post)
                            {
                                $arm_plan_condition = (!empty($planData) && (isset($planData['arm_current_plan_detail']) && !empty($planData['arm_current_plan_detail']) && ($planData['arm_current_plan_detail']['arm_subscription_plan_post_id'] != 0)));
                            }
                            else
                            {
                                $arm_plan_condition = (!empty($planData) && (isset($planData['arm_current_plan_detail']) && !empty($planData['arm_current_plan_detail']) && ($planData['arm_current_plan_detail']['arm_subscription_plan_post_id'] == 0)));
                                if($arm_plan_condition && $is_gift_plan)
                                {
                                    $arm_plan_condition = (!empty($planData) && (isset($planData['arm_current_plan_detail']) && !empty($planData['arm_current_plan_detail']) && ($planData['arm_current_plan_detail']['arm_subscription_plan_gift_status'] == 0)));
                                }
                            }
                        }
                        else
                        {
                            $arm_plan_condition = !empty($planData);

                            if($is_gift_plan)
                            {
                                $arm_plan_condition = (!empty($planData) && (isset($planData['arm_current_plan_detail']) && !empty($planData['arm_current_plan_detail']) && ($planData['arm_current_plan_detail']['arm_subscription_plan_gift_status'] == 0)));
                            }
                        }

                        if($arm_plan_condition)
                        {
                        
                            $planDetail = $planData['arm_current_plan_detail'];


                            $payment_cycle = $planData['arm_payment_cycle'];
                            $plan_start_date = (isset($planData['arm_start_plan']) && !empty($planData['arm_start_plan'])) ? date('m/d/Y', $planData['arm_start_plan']) : date('m/d/Y');
                            if (!empty($planDetail)) {
                                $planObj = new ARM_Plan(0);
                                $planObj->init((object) $planDetail);
                            } else {
                                $planObj = new ARM_Plan($uplans);
                            }




                            $plan_name = isset($planDetail['arm_subscription_plan_name']) ? $planDetail['arm_subscription_plan_name'] : '';
                            $recurring_profile = $planObj->new_user_plan_text(false, $payment_cycle);

                            $arm_plan_is_suspended = '';
                            $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                            $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
                            if (!empty($suspended_plan_ids)) {
                                if (in_array($uplans, $suspended_plan_ids)) {
                                    $arm_plan_is_suspended = '<div class="arm_manage_plan_status_div arm_position_relative">';
                                    $arm_plan_is_suspended .= '<span style="color: #ec4444;">(' . esc_html__('Suspended', 'ARMember') . ')</span>';
                                    $arm_plan_is_suspended .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/grid_edit_hover_trns.png"  title="' . esc_attr__('Activate Plan', 'ARMember') . '" class="armhelptip tipso_style" width="26" data-plan_id="' . esc_attr($uplans) . '" data-user_id="' . esc_attr($user_id) . '" onclick="showConfirmBoxCallback_plan(\'status_' . $uplans . '\');" style="margin: -5px 0; position: absolute; "/>';

                                    $arm_plan_is_suspended .= "<div class='arm_confirm_box arm_confirm_box_status_".esc_attr($uplans)."' id='arm_confirm_box_plan_status_".esc_attr($uplans)."' style='right: -5px;'>";
                                    $arm_plan_is_suspended .= "<div class='arm_confirm_box_body'>";
                                    $arm_plan_is_suspended .= "<div class='arm_confirm_box_arrow'></div>";
                                    $arm_plan_is_suspended .= "<div class='arm_confirm_box_text'>" . esc_html__("Are you sure you want to activate", 'ARMember') . ' ' . esc_html($plan_name) . ' ' . esc_html__("plan for this user?", 'ARMember') . "</div>";
                                    $arm_plan_is_suspended .= "<div class='arm_confirm_box_btn_container'>";
                                    $arm_plan_is_suspended .= "<button type='button' class='arm_confirm_box_btn armok arm_plan_status_change' data-item_id='".esc_attr($uplans)."'>" . esc_html__('Activate', 'ARMember') . "</button>";
                                    $arm_plan_is_suspended .= "<button type='button' class='arm_confirm_box_btn armcancel' onclick='hideConfirmBoxCallback();'>" . esc_html__('Cancel', 'ARMember') . "</button>";
                                    $arm_plan_is_suspended .= "</div>";
                                    $arm_plan_is_suspended .= "</div>";
                                    $arm_plan_is_suspended .= "</div></div>";
                                }
                            }
                            $arm_next_due_date = (isset($planData['arm_next_due_payment']) && !empty($planData['arm_next_due_payment']) ) ? date_i18n($date_format, $planData['arm_next_due_payment']) : '-';

                            if ($planObj->is_recurring()) {
                                $recurring_plan_options = $planObj->prepare_recurring_data($payment_cycle);
                                $recurring_time = $recurring_plan_options['rec_time'];
                                $completed = $planData['arm_completed_recurring'];
                                if ($recurring_time == 'infinite' || empty($planData['arm_expire_plan'])) {
                                    $remaining_occurence = esc_html__('Never Expires', 'ARMember');
                                } else {
                                    $remaining_occurence = $recurring_time - $completed;
                                }

                                if (!empty($planData['arm_expire_plan'])) {
                                    if ($remaining_occurence == 0) {
                                        $arm_next_due_date = esc_html__('No cycles due', 'ARMember');
                                    } else {
                                        $arm_next_due_date .= "<br/>( " . $remaining_occurence . esc_html__(' cycles due', 'ARMember') . " )";
                                    }
                                }
                            }



                            $expiry_date = (isset($planData['arm_expire_plan']) && !empty($planData['arm_expire_plan'])) ? $planData['arm_expire_plan'] : '';

                            $arm_edit_plan = '';

                            $arm_delete_plan = $future_plan_label = '';
                            if ($is_multiple_membership_feature->isMultipleMembershipFeature || ($is_paid_post)) {

                                if (in_array($uplans, $user_future_plan_ids)) {
                                    $arm_delete_plan .= '<input type="hidden" name="arm_user_future_plan[]" value="' . esc_attr($uplans) . '"/>';
                                } else {
                                    $arm_delete_plan .= '<input type="hidden" name="arm_subscription_start_date[]" value="' . esc_attr($plan_start_date) . '"/>';
                                    $arm_delete_plan .= '<input type="hidden" name="arm_user_plan[]" value="' . esc_attr($uplans) . '"/>';
                                }
                            }

                            if (in_array($uplans, $user_future_plan_ids)) {
                                $future_plan_label = '<br/><span class="arm_future_plan_label">('. esc_html__('Future Membership','ARMember') . ')</span>';
                            }

                            $arm_delete_plan .= '<div class="arm_position_relative">';
                            $arm_delete_plan .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/grid_delete_icon_trans.png"  title="' . esc_attr__('Delete Plan', 'ARMember') . '" class="arm_edit_plan_action_button armhelptip tipso_style" id="arm_member_delete_plan" data-plan_id="' . esc_attr($uplans) . '" data-user_id="' . esc_attr($user_id) . '" onclick="showConfirmBoxCallback_plan(' . $uplans . ');"/>';


                            $confirmBoxStyle = 'right: -5px;';

                            $confirmBox = "<div class='arm_confirm_box arm_confirm_box_".esc_attr($uplans)."' id='arm_confirm_box_plan_".esc_attr($uplans)."' style='".$confirmBoxStyle."'>";
                            $confirmBox .= "<div class='arm_confirm_box_body'>";
                            $confirmBox .= "<div class='arm_confirm_box_arrow'></div>";
                            $confirmBox .= "<div class='arm_confirm_box_text'>" . esc_html__("Are you sure you want to delete this plan from user?", 'ARMember') . "</div>";
                            $confirmBox .= "<div class='arm_confirm_box_btn_container'>";

                            $arm_member_delete_btn_class = ($is_paid_post) ? 'arm_member_paid_plan_delete_btn' : 'arm_member_plan_delete_btn' ;

                            $confirmBox .= "<button type='button' class='arm_confirm_box_btn armok ".esc_attr($arm_member_delete_btn_class)."' data-item_id='".esc_attr($uplans)."'>" . esc_html__('Delete', 'ARMember') . "</button>";
                            
                            $confirmBox .= "<button type='button' class='arm_confirm_box_btn armcancel' onclick='hideConfirmBoxCallback();'>" . esc_html__('Cancel', 'ARMember') . "</button>";
                            $confirmBox .= "</div>";
                            $confirmBox .= "</div>";
                            $confirmBox .= "</div>";
                            $confirmBox .= "</div>";

                            $arm_delete_plan .= $confirmBox;

                            $arm_edit_plan_text_box = '';
                            if ($expiry_date != '') {
                                $arm_edit_plan_text_box = '<input value="' . esc_attr( date('m/d/Y', $expiry_date) ) . '" name="arm_subscription_expiry_date_' . esc_attr($uplans) . '_' . esc_attr($user_id) . '" id="arm_subscription_expiry_date_' . esc_attr($uplans) . '_' . esc_attr($user_id) . '" class="arm_datepicker arm_expire_date arm_edit_plan_expire_date arm_width_100 arm_min_width_100"  aria-invalid="false" type="text">';
                                $arm_edit_plan .= "<a class='arm_member_edit_plan' >"
                                        . "<img src='" . MEMBERSHIP_IMAGES_URL . "/grid_edit_hover_trns.png' style='position: absolute; margin: -4px 0 0 5px; cursor: pointer;' width='26' title='" . esc_attr__('Change Expiry Date', 'ARMember') . "' class='armhelptip tipso_style'/>"
                                        . "</a>";
                                $arm_edit_plan .= "<img src='" . MEMBERSHIP_IMAGES_URL . "/arm_save_icon.png' style='display:none;' width='14' height='16' title='" . esc_attr__('Save Expiry Date', 'ARMember') . "' class='arm_edit_plan_action_button arm_member_save_plan armhelptip tipso_style arm_vertical_align_middle' data-plan_id='" . esc_attr($uplans) . "' data-user_id='" . esc_attr($user_id) . "' />&nbsp;";
                                $arm_edit_plan .= "<img src='" . MEMBERSHIP_IMAGES_URL . "/cancel_date_icon.png' style='display:none;' width='14' height='16' title='" . esc_attr__('Cancel', 'ARMember') . "' class='arm_edit_plan_action_button arm_member_cancel_save_plan armhelptip tipso_style' data-plan_id='" . esc_attr($uplans) . "' data-user_id='" . esc_attr($user_id) . "' data-plan-expire-date='" . date('m/d/Y', $expiry_date) . "' />&nbsp;";
                                $arm_edit_plan .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/arm_loader.gif" class="arm_edit_user_plan_loader arm_vertical_align_middle arm_margin_left_10" style="display:none;" width="17" height="18" />';
                            }

                            $additional_plan_label = '';
                            $additional_plan_label = apply_filters('arm_content_after_membership_plan_name_external', $additional_plan_label, $user_id, $uplans, $planData);

                            $expire_date = ($expiry_date != '') ? date_i18n($date_format, $expiry_date) : esc_html__('Never Expires', 'ARMember');
                            $row_class = ($count_plan % 2 == 0) ? 'odd' : 'even';
                            $return .= '<tr class="arm_user_plan_row ' . esc_attr($row_class) . '">';
                            $return .= '<td class="arm_edit_plan_name" >' . esc_html($plan_name) . ' ' . $future_plan_label. ' ' . $arm_plan_is_suspended . ' ' . $additional_plan_label . '</td>';
                            $return .= '<td class="arm_edit_plan_type" >' . $recurring_profile;

                            $return .= '</td>';
                            $return .= '<td class="arm_edit_plan_start" >' . date_i18n($date_format, $planData['arm_start_plan']);

                            $nowTime = strtotime(current_time('mysql'));

                            if (!empty($planData['arm_is_trial_plan'])) {
                                if ($planData['arm_trial_start'] < $planData['arm_start_plan'] && $nowTime < $planData['arm_trial_end']) {
                                    $return .= "<br/><span style='color: green;'>(" . esc_html__('trial active', 'ARMember') . ")</span>";
                                }
                            }

                            $return .= '</td>';


                            $return .= '<td class="arm_edit_plan_expiry" >'
                                    . '<span id="arm_expiry_date_lbl">' . esc_attr($expire_date) . '</span>'
                                    . '<span id="arm_expiry_date_input" style="display:none;">' . $arm_edit_plan_text_box . '</span>'
                                    . $arm_edit_plan
                                    . '</td>';
                            $return .= '<td class="arm_edit_plan_cycle_date" >' . $arm_next_due_date;


                            if ($planObj->is_recurring() && $planData['arm_payment_mode'] == 'auto_debit_subscription') {
                                $return .= '<br/>(' . esc_html__('Auto Debit', 'ARMember') . ')';
                            }
                            $return .= '</td>';

                            $additional_plan_actions = '';
                            $additional_plan_actions = apply_filters('arm_content_after_membership_action_btn_external', $additional_plan_actions, $user_id, $uplans, $planData,$planObj);
                            
                            $return .= '<td class="arm_edit_plan_action">' . $arm_delete_plan . $additional_plan_actions . '</td>';
                            $return .= '</tr>';
                        }
                    }
                } else {
                    $return .= ($is_paid_post) ? '<tr class="arm_user_edit_plan_table" ><td colspan="6" class="arm_text_align_center" >'
                            . esc_html__("This user don't have any post.", 'ARMember')
                            . '</td></tr>' : '<tr class="arm_user_edit_plan_table" ><td colspan="6" class="arm_text_align_center">'
                            . esc_html__("This user don't have any plans.", 'ARMember')
                            . '</td></tr>';
                }

                $return .= '</table>';

                $bulk_member_change_plan_popup_content = '<span class="arm_confirm_text">' . esc_html__("Are you sure you want to remove this plan from this user??", 'ARMember') . '</span>';
                $bulk_member_change_plan_popup_content .= '<input type="hidden" value="false" id="bulk_change_plan_flag"/>';
                $bulk_member_change_plan_popup_arg = array(
                    'id' => 'change_plan_bulk_message',
                    'class' => 'change_plan_bulk_message',
                    'title' => esc_html__('Change Plan', 'ARMember'),
                    'content' => $bulk_member_change_plan_popup_content,
                    'button_id' => 'arm_bulk_member_change_plan_ok_btn',
                    'button_onclick' => "apply_member_bulk_action('bulk_change_plan_flag');",
                );

                $return .= $arm_global_settings->arm_get_bpopup_html($bulk_member_change_plan_popup_arg);
            }
            if ($is_ajax) {
                return $return . '^|^' . $user_name;
            } else {
                echo $return . '^|^' . $user_name; //phpcs:ignore
                die;
            }
        }

        function arm_manage_plan_get_cycle() {
            global $ARMember, $wpdb, $arm_global_settings, $arm_payment_gateways, $is_multiple_membership_feature, $arm_capabilities_global;
            $type = 'failed';
            $content = '';
            if (isset($_POST['action']) && !empty($_POST['action']) && isset($_POST['plan_id']) && !empty($_POST['plan_id'])) {//phpcs:ignore
                $plan = new ARM_Plan(intval($_POST['plan_id']));//phpcs:ignore

                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1); //phpcs:ignore --Reason:Verifying nonce

                if (!$plan->is_lifetime() && $plan->is_recurring()) {
                    $type = 'success';
                    $arm_dropdown_width = '';
                    $arm_dropdown_style = '';
                    $arm_plan_cycle_dropdown = '';
                    $plansCycleLists = '<li data-label="' . esc_attr__('Select Payment Cycle', 'ARMember') . '" data-value="">' . esc_html__('Select Payment Cycle', 'ARMember') . '</li>';


                    $plan_options['payment_cycles'] = (isset($plan->options['payment_cycles']) && !empty($plan->options['payment_cycles'])) ? $plan->options['payment_cycles'] : array();

                    if (empty($plan_options['payment_cycles'])) {
                        $plan_amount = !empty($plan_data['arm_subscription_plan_amount']) ? $plan_data['arm_subscription_plan_amount'] : 0;
                        $recurring_time = isset($plan_options['recurring']['time']) ? $plan_options['recurring']['time'] : 'infinite';
                        $recurring_type = isset($plan_options['recurring']['type']) ? $plan_options['recurring']['type'] : 'D';
                        switch ($recurring_type) {
                            case 'D':
                                $billing_cycle = isset($plan_options['recurring']['days']) ? $plan_options['recurring']['days'] : '1';
                                break;
                            case 'M':
                                $billing_cycle = isset($plan_options['recurring']['months']) ? $plan_options['recurring']['months'] : '1';
                                break;
                            case 'Y':
                                $billing_cycle = isset($plan_options['recurring']['years']) ? $plan_options['recurring']['years'] : '1';
                                break;
                            default:
                                $billing_cycle = '1';
                                break;
                        }
                        $plan_options['payment_cycles'] = array(array(
                                'cycle_key' => 'arm0',
                                'cycle_label' => $plan->plan_text(false, false),
                                'cycle_amount' => $plan_amount,
                                'billing_cycle' => $billing_cycle,
                                'billing_type' => $recurring_type,
                                'recurring_time' => $recurring_time,
                                'payment_cycle_order' => 1,
                        ));
                    }
                    if (is_array($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) {
                        foreach ($plan_options['payment_cycles'] as $cycle_key => $p) {
                            $plansCycleLists .= '<li data-label="' . stripslashes(esc_attr($p['cycle_label'])) . '" data-value="' . $cycle_key . '">' . stripslashes(esc_attr($p['cycle_label'])) . '</li>';
                        }
                    }

                    $arm_user_plan_cycle_data_id = "arm_user_plan_cycle_input";
                    $arm_user_plan_cycle_name = "arm_selected_payment_cycle";
                    if (isset($_POST['arm_manage_plan_grid']) && ($_POST['arm_manage_plan_grid'] == 0 || $_POST['arm_manage_plan_grid'] == 2)) {//phpcs:ignore
                        $arm_dropdown_width = 'style="width: 230px;"';
                        $arm_dropdown_style = '';
                        if($_POST['arm_manage_plan_grid'] == 0)//phpcs:ignore
                        {
                            $arm_dropdown_style = 'margin-top:10px;';
                        }
                        $arm_dropdown_style = 'style="float: left;'.$arm_dropdown_style.'"';
                        if ($is_multiple_membership_feature->isMultipleMembershipFeature || $plan->isPaidPost || $plan->isGiftPlan) {
                            $arm_user_plan_cycle_data_id = "arm_user_plan_cycle_input_" . intval($_POST['plan_id']);//phpcs:ignore
                            $arm_user_plan_cycle_name = "arm_selected_payment_cycle[arm_plan_cycle_" . intval( $_POST['plan_id']) . "]";//phpcs:ignore
                            $arm_dropdown_width = '';
                        }
                    }

                    $arm_plan_cycle_dropdown = '<input type="hidden" class="' . esc_attr($arm_user_plan_cycle_data_id) . '" name="' . esc_attr($arm_user_plan_cycle_name) . '" id="' . esc_attr($arm_user_plan_cycle_data_id) . '" value=""/>';
                    $arm_plan_cycle_dropdown .= '<dl class="arm_selectbox column_level_dd arm_member_form_dropdown arm_width_500_manage_plan_detail" ' . $arm_dropdown_style . '>';
                    $arm_plan_cycle_dropdown .= '<dt ' . $arm_dropdown_width . '><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
                    $arm_plan_cycle_dropdown .= '<dd><ul data-id="' . esc_attr($arm_user_plan_cycle_data_id) . '">' . $plansCycleLists . '</ul></dd>';
                    $arm_plan_cycle_dropdown .= '</dl>';
                    $arm_plan_cycle_dropdown .= '<br/><span class="arm_error_select_plan_cycle error arm_invalid arm_text_align_left" style="display:none; ">' . esc_html__('Please select payment cycle.', 'ARMember') . '</span>';


                    if (isset($_POST['arm_manage_plan_grid']) && $_POST['arm_manage_plan_grid'] == 1) {//phpcs:ignore
                        $content .= '<span class="arm_edit_plan_lbl">' . esc_html__('Choose Payment Cycle', 'ARMember') . '*</span> ';
                        $content .= '<div class="arm_edit_field">';
                        $content .= $arm_plan_cycle_dropdown;
                        $content .= '</div>';
                    } else if (isset($_POST['arm_manage_plan_grid']) && $_POST['arm_manage_plan_grid'] == '2') {//phpcs:ignore
                        $content .= '<span class="arm_add_plan_filter_label arm_choose_payment_cycle_label">'.esc_html__('Choose Payment Cycle', 'ARMember').'</span>';
                        $content .= $arm_plan_cycle_dropdown;
                    }
                    else {
                        $content .= $arm_plan_cycle_dropdown;
                    }
                }
            }

            $content = apply_filters('arm_add_membership_plan_option', $content, intval($_POST['plan_id']));//phpcs:ignore
            $type = (!empty($content)) ? 'success' : 'failed';

            echo json_encode(array('type' => $type, 'content' => $content));
            exit;
        }

        function arm_get_user_all_plan_details_for_grid() {
            global $arm_global_settings, $arm_payment_gateways, $ARMember, $arm_capabilities_global, $arm_pay_per_post_feature;
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $user_id = intval($_POST['user_id']);//phpcs:ignore
            $return = '';
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1'); //phpcs:ignore --Reason:Verifying nonce
            if (!empty($user_id)) {

                $user_plans = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $user_plans = apply_filters('arm_modify_plan_ids_externally', $user_plans, $user_id);

                $user_future_plans = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
                $return .= '<div class="arm_child_row_div"><table class="arm_user_child_row_table arm_text_align_center" cellspacing="1" >';
                $return .= '<tr class="arm_child_user_row">';
                $return .= '<th class="arm_width_180">' . esc_html__('Membership Plan', 'ARMember') . '</th>';
                $return .= '<th>' . esc_html__('Plan Type', 'ARMember') . '</th>';
                $return .= '<th>' . esc_html__('Starts On', 'ARMember') . '</th>';

                $return .= '<th>' . esc_html__('Expires On', 'ARMember') . '</th>';
                $return .= '<th>' . esc_html__('Cycle Date', 'ARMember') . '</th>';

                $return .= '<th>' . esc_html__('Plan Role', 'ARMember') . '</th>';
                $return .= '<th>' . esc_html__('Paid With', 'ARMember') . '</th>';
                $return .= '</tr>';

                if (!empty($user_future_plans)) {
                    $arm_user_plans = array_merge($user_plans, $user_future_plans);
                } else {
                    $arm_user_plans = $user_plans;
                }


                if (!empty($arm_user_plans)) {

                    foreach ($arm_user_plans as $uplans) {
                        $planData = get_user_meta($user_id, 'arm_user_plan_' . $uplans, true);
                        $planDetail = $planData['arm_current_plan_detail'];
                        $payment_cycle = $planData['arm_payment_cycle'];

                        $arm_plan_condition = "";

                        if($arm_pay_per_post_feature->isPayPerPostFeature)
                        {
                            $arm_plan_condition = (!empty($planData) && (isset($planData['arm_current_plan_detail']) && !empty($planDetail) && empty($planDetail['arm_subscription_plan_post_id'])));
                        }
                        else
                        {
                            $arm_plan_condition = !empty($planData);
                        }

                        if($arm_plan_condition)
                        {
                            if (!empty($planDetail)) {
                                $planObj = new ARM_Plan(0);
                                $planObj->init((object) $planDetail);
                            } else {
                                $planObj = new ARM_Plan($uplans);
                            }

                            $planRecurringData = $planObj->prepare_recurring_data($payment_cycle);

                            $recurring_profile = $planObj->new_user_plan_text(false, $payment_cycle);


                            $payment_mode = '';
                            if ($planData['arm_payment_mode'] == 'auto_debit_subscription') {
                                $payment_mode = "<br/>(" . esc_html__('Auto Debit', 'ARMember') . ")";
                            }

                            $arm_plan_is_suspended = '';
                            $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                            $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
                            if (!empty($suspended_plan_ids)) {
                                if (in_array($uplans, $suspended_plan_ids)) {
                                    $arm_plan_is_suspended = '<br/><span style="color: #ec4444;">(' . esc_html__('Suspended', 'ARMember') . ')</span>';
                                }
                            }

                            $arm_is_cancelled = (!empty($planData['arm_cencelled_plan']) && $planData['arm_cencelled_plan'] == "yes") ? '<span style="color: red;">( '.esc_html__('Cancelled', 'ARMember').' )</span>' : '';

                            $plan_name = $planDetail['arm_subscription_plan_name'] . " " . $arm_plan_is_suspended." ".$arm_is_cancelled;
                            $plan_role = $planDetail['arm_subscription_plan_role'];
                            $start_date = (isset($planData['arm_start_plan']) && !empty($planData['arm_start_plan'])) ? date_i18n($date_format, $planData['arm_start_plan']) : '-';
                            $expiry_date = (isset($planData['arm_expire_plan']) && !empty($planData['arm_expire_plan'])) ? date_i18n($date_format, $planData['arm_expire_plan']) : esc_html__('Never Expires', 'ARMember');
                            $renew_date = (isset($planData['arm_next_due_payment']) && !empty($planData['arm_next_due_payment'])) ? date_i18n($date_format, $planData['arm_next_due_payment']) : '-';
                            $paidwith = (isset($planData['arm_user_gateway']) && !empty($planData['arm_user_gateway'])) ? $arm_payment_gateways->arm_gateway_name_by_key($planData['arm_user_gateway']) : '-';
                            $arm_membership_cycle = isset($planRecurringData['cycle_label']) ? $planRecurringData['cycle_label'] : '-';
                            $total_payments = isset($planRecurringData['rec_time']) ? $planRecurringData['rec_time'] : 0;

                            $arm_trial_start = $planData['arm_trial_start'];

                            $arm_trial_active = '';
                            if (!empty($arm_trial_start) && !empty($planData['arm_start_plan'])) {
                                if ($arm_trial_start < $planData['arm_start_plan']) {
                                    $arm_trial_active = "<br/><span style='color: green;'>( " . esc_html__('trial active', 'ARMember') . " ) </span>";
                                }
                            }

                            $arm_installments_text = '';
                            $done_payments = $planData['arm_completed_recurring'];
                            if ($total_payments > 0 && $done_payments >= 0) {
                                $arm_installments = (int)$total_payments - $done_payments;
                                if (!empty($planData['arm_expire_plan'])) {

                                    if ($arm_installments == 0) {
                                        $renew_date = '';
                                        $arm_installments_text = esc_html__('No cycles due', 'ARMember');
                                    } else {
                                        $arm_installments_text = "<br/>( " . $arm_installments . " " . esc_html__('cycles due', 'ARMember') . ")";
                                    }
                                }
                            }

                            $arm_plan_is_suspended = '';
                            $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                            $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
                            if (!empty($suspended_plan_ids)) {
                                if (in_array($uplans, $suspended_plan_ids)) {
                                    $arm_plan_is_suspended = '<span style="color: #ec4444;">(' . esc_html__('Suspended', 'ARMember') . ')</span>';
                                }
                            }

                            $return .= '<tr class="arm_child_user_row">';
                            $return .= '<td>' . esc_html($plan_name) . '</td>';
                            $return .= '<td>' . esc_html($recurring_profile);

                            $return .= '</td>';
                            $return .= '<td>' . esc_html($start_date) . esc_html($arm_trial_active) . '</td>';

                            $return .= '<td>' . esc_html($expiry_date) . '</td>';
                            $return .= '<td>' . esc_html($renew_date) . esc_html($arm_installments_text) . esc_html($payment_mode) . '</td>';
                            $return .= '<td>' . esc_html(ucfirst($plan_role)) . '</td>';
                            $return .= '<td>' . esc_html(ucfirst($paidwith)) . '</td>';
                            $return .= '</tr>';
                        }
                    }
                }
                $return .= '</table></div>';
            }
            echo $return; //phpcs:ignore
            die;
        }

        function arm_add_capabilities_to_new_user($user_id) {
            global $ARMember;
            if ($user_id == '') {
                return;
            }
            if (user_can($user_id, 'administrator')) {
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

        function arm_add_capabilities_to_change_user_role($user_id, $role, $old_roles) {
            global $ARMember;
            if ($user_id == '') {
                return;
            }
            if ($role=='administrator' && $user_id) {
                $armroles = $ARMember->arm_capabilities();
                $userObj = new WP_User($user_id);
                foreach ($armroles as $armrole => $armroledescription) {
                    if (!user_can($user_id, $armrole)) {
                        $userObj->add_cap($armrole);
                    }
                }
                unset($armrole);
                unset($armroles);
                unset($armroledescription);
            }
        }

        /**
         * Filter User Columns For Search In WP User Query
         */
        function arm_add_user_to_armember_func($user_id = 0, $blog_id = 0, $plan_id = 0) {
            $this->arm_add_update_member_profile($user_id, $blog_id);
            do_action('arm_apply_plan_to_member', $plan_id, $user_id);
        }

        function arm_get_user_login_history($user_id = 0, $current_page = 1, $perPage = 10) {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $arm_all_block_settings = $arm_global_settings->block_settings;
            if (isset($arm_all_block_settings['track_login_history']) && $arm_all_block_settings['track_login_history'] != 1)
                return;

            $historyHtml = '';
            if (!empty($user_id) && $user_id != 0) {

                $perPage = (!empty($perPage) && is_numeric($perPage)) ? $perPage : 10;
                $offset = 0;

                if (is_multisite()) {
                    $wp_date_time_format = get_option('date_format') . " " . get_option('time_format');
                } else {
                    $wp_date_time_format = get_site_option('date_format') . " " . get_site_option('time_format');
                }
                if (!empty($current_page) && $current_page > 1) {
                    $offset = ($current_page - 1) * $perPage;
                }
                $historyLimit = (!empty($perPage)) ? " LIMIT $offset, $perPage " : "";
                $totalRecord = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(`arm_history_id`) FROM `" . $ARMember->tbl_arm_login_history . "` WHERE `arm_user_id`=%d",$user_id)); //phpcs:ignore --Reason $ARMember->tbl_arm_login_history is a table name
                $historyRecords = $wpdb->get_results( $wpdb->prepare("SELECT `arm_logged_in_ip`, `arm_user_current_status`, `arm_logged_in_date`, `arm_logout_date`, `arm_history_browser`, `arm_login_country` FROM `" . $ARMember->tbl_arm_login_history . "` WHERE `arm_user_id`=%d ORDER BY `arm_history_id` DESC {$historyLimit}",$user_id), ARRAY_A); //phpcs:ignore --Reason $ARMember->tbl_arm_login_history is a table name

                $historyHtml .= '<div class="arm_loginhistory_wrapper" data-user_id="' . esc_attr($user_id) . '">';
                $historyHtml .= '<table class="form-table arm_member_last_subscriptions_table" width="100%">';
                $historyHtml .= '<tr>';

                $historyHtml .= '<td>' . esc_html__('Logged In Date', 'ARMember') . '</td>';
                $historyHtml .= '<td>' . esc_html__('Logged In IP', 'ARMember') . '</td>';
                $historyHtml .= '<td>' . esc_html__('Browser Name', 'ARMember') . '</td>';
                $historyHtml .= '<td>' . esc_html__('Country Name', 'ARMember') . '</td>';
                $historyHtml .= '<td>' . esc_html__('Logged Out Date', 'ARMember') . '</td>';
                $historyHtml .= '</tr>';
                if (!empty($historyRecords)) {
                    $i = 0;
                    foreach ($historyRecords as $mh) {

                        $logout_date = date_create($mh['arm_logout_date']);
                        $login_date = date_create($mh['arm_logged_in_date']);
                        if (isset($mh['arm_user_current_status']) && $mh['arm_user_current_status'] == 1 && $mh['arm_logout_date'] == "0000-00-00 00:00:00") {
                            $arm_logged_out_date = esc_html__('Currently Logged In', 'ARMember');
                        } else {
                            if ($mh['arm_user_current_status'] == 0 && $mh['arm_logout_date'] == "0000-00-00 00:00:00") {
                                $arm_logged_out_date = "-";
                            } else {
                                $arm_logged_out_date = date_i18n($wp_date_time_format, strtotime($mh['arm_logout_date']));
                            }
                        }
                        $i++;
                        //$arm_login_date = date_i18n(date_format($login_date, $wp_date_time_format));
                        $arm_login_date = date_i18n($wp_date_time_format, strtotime($mh['arm_logged_in_date']));
                        $historyHtml .= '<tr class="arm_member_last_subscriptions_data">';

                        $historyHtml .= '<td>' . $arm_login_date . '</td>';
                        $historyHtml .= '<td>' . $mh['arm_logged_in_ip'] . '</td>';
                        $historyHtml .= '<td>' . $mh['arm_history_browser'] . '</td>';
                        $historyHtml .= '<td>' . $mh['arm_login_country'] . '</td>';
                        $historyHtml .= '<td>' . $arm_logged_out_date . '</td>';
                        $historyHtml .= '</tr>';
                    }
                } else {
                    $historyHtml .= '<tr class="arm_member_last_subscriptions_data">';
                    $historyHtml .= '<td colspan="5"  class="arm_text_align_center">' . esc_html__('No Login History Found.', 'ARMember') . '</td>';
                    $historyHtml .= '</tr>';
                }
            }

            $historyHtml .= '</table>';
            $historyHtml .= '<div class="arm_membership_history_pagination_block">';
            $historyPaging = $arm_global_settings->arm_get_paging_links($current_page, $totalRecord, $perPage, '');
            $historyHtml .= '<div class="arm_membership_history_paging_container">' . $historyPaging . '</div>';
            $historyHtml .= '</div>';
            $historyHtml .= '</div>';

            return $historyHtml;
        }

        function arm_get_all_user_login_history($current_page = 1, $perPage = 10, $arm_log_history_search_user = '') {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $arm_all_block_settings = $arm_global_settings->block_settings;
            $user_table = $wpdb->users;
            $arm_log_history_search_user = !empty($arm_log_history_search_user) ? $arm_log_history_search_user : '';
            $historyHtml = '';


            $perPage = (!empty($perPage) && is_numeric($perPage)) ? $perPage : 10;
            $offset = 0;
            if (is_multisite()) {
                $wp_date_time_format = get_option('date_format') . " " . get_option('time_format');
            } else {
                $wp_date_time_format = get_site_option('date_format') . " " . get_site_option('time_format');
            }
            if (!empty($current_page) && $current_page > 1) {
                $offset = ($current_page - 1) * $perPage;
            }
            $history_where = "";
            if(!empty($arm_log_history_search_user))
            {
               $history_where .= $wpdb->prepare(' AND u.user_login LIKE %s ','%'.$arm_log_history_search_user.'%');
            }
            $historyLimit = (!empty($perPage)) ? " LIMIT $offset, $perPage " : "";
            
            $historyRecords1 = "SELECT u.user_login, l.arm_user_current_status, l.arm_user_id,l.arm_logged_in_ip, l.arm_logged_in_date, l.arm_logout_date, l.arm_history_browser, l.arm_login_country FROM `{$user_table}` u INNER JOIN `" . $ARMember->tbl_arm_login_history . "` l ON u.ID = l.arm_user_id where 1 = 1  $history_where ORDER BY l.arm_history_id DESC ";
            
            $historyRecords2 = $historyRecords1." {$historyLimit}";

            $historyRecords =  $wpdb->get_results($historyRecords2, ARRAY_A); //phpcs:ignore --Reason $historyRecords2 is a query variable

            $totalRecord = $wpdb->get_results($historyRecords1);//phpcs:ignore --Reason $historyRecords1 is a query variable
            $totalRecord = count($totalRecord);

            $historyHtml .= '<div class="arm_all_loginhistory_main_wrapper">';
            $historyHtml .= '<div class="arm_all_loginhistory_filter_wrapper arm_datatable_searchbox">';
            $historyHtml .= '<table class="form-table arm_member_last_subscriptions_table arm_member_login_history_filter_table" width="100%">';
            $historyHtml .= '<tr>';
            $historyHtml .= '<td>';
            $historyHtml .= '<label class="arm_log_history_search_lbl_user"><input type="text" placeholder="'.esc_attr__('Search by username', 'ARMember'). '" id="arm_log_history_search_user" name="arm_log_history_search_user" value="'.esc_attr($arm_log_history_search_user).'" tabindex="-1" ></label>';
            $historyHtml .= '<div>
                            <button id="arm_login_history_search_btn" class="armemailaddbtn arm_login_history_search_btn" type="button">'. esc_html__('Apply', 'ARMember').'</button>
                            </div>';
            $historyHtml .= '</td>';
            $historyHtml .= '</tr>';
            $historyHtml .= '</table>';
            $historyHtml .= '</div>';
            $historyHtml .= '<div class="arm_all_loginhistory_wrapper">';
            $historyHtml .= '<table class="form-table arm_member_last_subscriptions_table" width="100%">';
            $historyHtml .= '<tr>';
            $historyHtml .= '<td>' . esc_html__('Username', 'ARMember') . '</td>';
            $historyHtml .= '<td>' . esc_html__('Logged In Date', 'ARMember') . '</td>';
            $historyHtml .= '<td>' . esc_html__('Logged In IP', 'ARMember') . '</td>';
            $historyHtml .= '<td>' . esc_html__('Browser Name', 'ARMember') . '</td>';
            $historyHtml .= '<td>' . esc_html__('Country', 'ARMember') . '</td>';
            $historyHtml .= '<td>' . esc_html__('Logged Out Date', 'ARMember') . '</td>';
            $historyHtml .= '</tr>';
            if (!empty($historyRecords)) {
                $i = 0;
                foreach ($historyRecords as $mh) {
                    $i++;
                    $logout_date = date_create($mh['arm_logout_date']);
                    $login_date = date_create($mh['arm_logged_in_date']);
                    if (isset($mh['arm_user_current_status']) && $mh['arm_user_current_status'] == 1 && $mh['arm_logout_date'] == "0000-00-00 00:00:00") {
                        $arm_logged_out_date = esc_html__('Currently Logged In', 'ARMember');
                    } else {
                        if ($mh['arm_user_current_status'] == 0 && $mh['arm_logout_date'] == "0000-00-00 00:00:00") {
                            $arm_logged_out_date = "-";
                        } else {
                            $arm_logged_out_date = date_i18n($wp_date_time_format, strtotime($mh['arm_logout_date']));
                        }
                    }
                    $historyHtml .= '<tr class="arm_member_last_subscriptions_data all_user_login_history_tr">';
                    $historyHtml .= '<td>' . $mh['user_login'] . '</td>';
                    $historyHtml .= '<td>' . date_i18n($wp_date_time_format, strtotime($mh['arm_logged_in_date'])) . '</td>';
                    $historyHtml .= '<td>' . $mh['arm_logged_in_ip'] . '</td>';
                    $historyHtml .= '<td>' . $mh['arm_history_browser'] . '</td>';
                    $historyHtml .= '<td>' . $mh['arm_login_country'] . '</td>';
                    $historyHtml .= '<td>' . $arm_logged_out_date . '</td>';
                    $historyHtml .= '</tr>';
                }
            } else {
                $historyHtml .= '<tr class="arm_member_last_subscriptions_data">';
                $historyHtml .= '<td colspan="6" class="arm_text_align_center">' . esc_html__('No Login History Found.', 'ARMember') . '</td>';
                $historyHtml .= '</tr>';
            }

            $historyHtml .= '</table>';
            $historyHtml .= '<div class="arm_membership_history_pagination_block">';
            $historyPaging = $arm_global_settings->arm_get_paging_links($current_page, $totalRecord, $perPage, '');
            $historyHtml .= '<div class="arm_membership_history_paging_container">' . $historyPaging . '</div>';
            $historyHtml .= '</div>';
            $historyHtml .= '</div>';
            $historyHtml .= '</div>';


            return $historyHtml;
        }

        function arm_user_login_history_paging_action() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways,$arm_capabilities_global;
            if (isset($_POST['action']) && $_POST['action'] == 'arm_user_login_history_paging_action') {//phpcs:ignore
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1); //phpcs:ignore --Reason:Verifying nonce
                $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;//phpcs:ignore
                $current_page = isset($_POST['page']) ? intval($_POST['page']) : 1;//phpcs:ignore
                $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;//phpcs:ignore
                echo $this->arm_get_user_login_history($user_id, $current_page, $per_page); //phpcs:ignore
            }
            exit;
        }

        function arm_all_user_login_history_paging_action() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways,$arm_capabilities_global;
            if (isset($_POST['action']) && ($_POST['action'] == 'arm_all_user_login_history_paging_action' || $_POST['action'] == 'arm_login_history_search_action')) {//phpcs:ignore

                $current_page = isset($_POST['page']) ? intval($_POST['page']) : 1;//phpcs:ignore
                $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;//phpcs:ignore
                $arm_log_history_search_user = isset($_POST['arm_log_history_search_user']) ? sanitize_text_field($_POST['arm_log_history_search_user']) : '';//phpcs:ignore
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_block_settings'], '1');
                echo $this->arm_get_all_user_login_history($current_page, $per_page, $arm_log_history_search_user); //phpcs:ignore
            }
            exit;
        }

        function arm_login_history_pagination() {
            global $ARMember, $wpdb, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_block_settings'], '1'); //phpcs:ignore
            $table_name = $ARMember->tbl_arm_login_history;
            $content = '';
            $page_num = intval($_POST['page']);//phpcs:ignore

            $limit = intval($_POST['limit']);//phpcs:ignore
            $start_from = ($page_num - 1) * $limit;
            $user_id = intval($_POST['user_id']);//phpcs:ignore
            $get_login_history = $wpdb->get_results( $wpdb->prepare("SELECT * FROM `{$table_name}` WHERE `arm_user_id` = %d ORDER BY `arm_history_id` ASC limit {$start_from},{$limit}",$user_id)); //phpcs:ignore --Reason $table_name is a table name
            if (!empty($get_login_history)) {

                foreach ($get_login_history as $key => $login_history) {
                    $arm_logout_date = ($login_history->arm_logout_date == '0000-00-00 00:00:00') ? esc_html__('User is currently logged in', 'ARMember') : $login_history->arm_logout_date;
                    $arm_login_duration = ($login_history->arm_login_duration == '00:00:00') ? '-' : $login_history->arm_login_duration;
                    $class = (($key + 1) % 2 == 0) ? 'even' : 'odd';

                    $content .= '<tr class="' . $class . '" >';
                    $content .= '<td align="center">' . (($limit * $page_num) - ($limit - ($key + 1))) . '</td>';
                    $content .= '<td align="center">' . ($login_history->arm_logged_in_date) . '</td>';
                    $content .= '<td align="center">' . ($login_history->arm_logged_in_ip) . '</td>';
                    $content .= '<td align="center">' . ($login_history->arm_history_browser) . '</td>';
                    $content .= '<td align="center">' . $arm_logout_date . '</td>';
                    $content .= '<td align="center">' . $arm_login_duration . '</td>';
                    $content .= '</tr>';
                }
            }
            echo $content; //phpcs:ignore
            exit;
        }

        function arm_user_search_columns($search_columns, $search, $WPUserQuery) {
            $search_columns[] = 'display_name';
            return $search_columns;
        }

        function arm_before_delete_user_action($id, $reassign = 1) {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_global_settings, $arm_subscription_plans;
            $plan_ids = get_user_meta($id, 'arm_user_plan_ids', true);

            do_action('arm_delete_users_external', $id);
            
            if (!empty($plan_ids) && is_array($plan_ids)) {
                foreach ($plan_ids as $plan_id) {
                    if (!empty($plan_id) && $plan_id != 0) {
                        $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                        $userPlanDatameta = get_user_meta($id, 'arm_user_plan_' . $plan_id, true);
                        $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                        $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);



                        $plan_detail = $planData['arm_current_plan_detail'];
                        if (!empty($plan_detail)) {
                            $planObj = new ARM_Plan(0);
                            $planObj->init((object) $plan_detail);
                        } else {
                            $planObj = new ARM_Plan($plan_id);
                        }
                        if ($planObj->exists() && $planObj->is_recurring()) {
                            do_action('arm_cancel_subscription_gateway_action', $id, $plan_id);
                        }
                        else {
                            do_action('arm_cancel_except_recurring_subscription_action', $id, $plan_id);
                        }
                    }
                }
                delete_user_meta($id, 'arm_user_suspended_plan_ids', true);
                delete_user_meta($id, 'arm_changed_expiry_date_plans', true);
            }
        }

        function arm_after_deleted_user_action($id, $reassign = 1) {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_global_settings;

            /* delete user login-logout history starts */
            $delete_login_history = $wpdb->query( $wpdb->prepare("DELETE FROM `$ARMember->tbl_arm_login_history` where arm_user_id = %d" , $id)); //phpcs:ignore --Reason $ARMember->tbl_arm_login_history is a table name
            /* delete user login-logout history ends */

            /* delete user activity history starts */
            $delete_user_activity = $wpdb->query( $wpdb->prepare("DELETE FROM `$ARMember->tbl_arm_activity` where arm_user_id = %d" , $id));//phpcs:ignore --Reason $ARMember->tbl_arm_activity is a table name
            /* delete user activity history ends */

            /* delete user arm members table starts */
            $delete_user_members = $wpdb->query( $wpdb->prepare("DELETE FROM `$ARMember->tbl_arm_members` where arm_user_id = %d" , $id));//phpcs:ignore --Reason $ARMember->tbl_arm_members is a table name
            /* delete user arm members table ends */

            /* delete members entries table starts */
            $delete_user_entries = $wpdb->query( $wpdb->prepare("DELETE FROM `$ARMember->tbl_arm_entries` where arm_user_id = %d" , $id));//phpcs:ignore --Reason $ARMember->tbl_arm_entries is a table name
            /* delete members entries table ends */

            /* delete members fail attempts table starts */
            $delete_user_fail_attempts = $wpdb->query( $wpdb->prepare("DELETE FROM `$ARMember->tbl_arm_fail_attempts` where arm_user_id = %d" , $id));//phpcs:ignore --Reason $ARMember->tbl_arm_fail_attempts is a table name
            /* delete members fail attempts table ends */

            /* delete members lockdown table starts */
            $delete_user_lockdown = $wpdb->query($wpdb->prepare("DELETE FROM `$ARMember->tbl_arm_lockdown` where arm_user_id = %d" , $id));//phpcs:ignore --Reason $ARMember->tbl_arm_lockdown is a table name
            /* delete members lockdown table ends */

            /* update member id payment log table starts */
            $update_user_payment_log = $wpdb->query( $wpdb->prepare("UPDATE `$ARMember->tbl_arm_payment_log` SET arm_user_id='0', arm_payer_email='', arm_first_name='', arm_last_name='', arm_bank_name='', arm_account_name='', arm_additional_info='' where arm_user_id = %d" , $id)); //phpcs:ignore --Reason $ARMember->tbl_arm_payment_log is a table name
            /* update member id payment log table ends */
        }

        function arm_get_all_members($type = 0, $only_total = 0, $recent_data = 0,$inactive_array=array()) {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;

            $user_table = $wpdb->users;
            $usermeta_table = $wpdb->usermeta;
            $capability_column = $wpdb->get_blog_prefix($GLOBALS['blog_id']) . 'capabilities';

            $super_admin_ids = array();
            if (is_multisite()) {
                $super_admin = get_super_admins();
                if (!empty($super_admin)) {
                    foreach ($super_admin as $skey => $sadmin) {
                        if ($sadmin != '') {
                            $user_obj = get_user_by('login', $sadmin);
                            if ($user_obj->ID != '') {
                                $super_admin_ids[] = $user_obj->ID;
                            }
                        }
                    }
                }
            }

            $user_where = " WHERE 1=1";
            if (!empty($super_admin_ids)) {
				$super_admin_placeholders = ' AND u.ID NOT IN (';
				$super_admin_placeholders .= rtrim( str_repeat( '%s,', count( $super_admin_ids ) ), ',' );
				$super_admin_placeholders .= ')';

				array_unshift( $super_admin_ids, $super_admin_placeholders );

				// $user_where .= ' AND u.ID NOT IN (' . implode( ',', $super_admin_ids ) . ')';
				$user_where .= call_user_func_array(array( $wpdb, 'prepare' ), $super_admin_ids );
            }

            $user_where .= " AND um.meta_key = '{$capability_column}' AND um.meta_value NOT LIKE '%administrator%' "; //phpcs:ignore
            $user_join = "";

            if(!empty($inactive_array)){
                $admin_placeholders = 'AND arm1.arm_primary_status IN  (';
				$admin_placeholders .= rtrim( str_repeat( '%s,', count( $inactive_array ) ), ',' );
				$admin_placeholders .= ')';
				// $admin_users       = implode( ',', $admin_users );

				array_unshift( $inactive_array, $admin_placeholders );

                // $user_where .= " AND arm1.arm_primary_status IN (" . implode(',', $inactive_array) . ") ";
					
				$user_where .= call_user_func_array(array( $wpdb, 'prepare' ), $inactive_array );
                
                $user_join = " INNER JOIN {$ARMember->tbl_arm_members} arm1 ON u.ID = arm1.arm_user_id";
            }else{

                if (!empty($type) && in_array($type, array(1, 2, 3))) {
                    $user_join = " INNER JOIN {$ARMember->tbl_arm_members} arm1 ON u.ID = arm1.arm_user_id";
                    $user_where .= $wpdb->prepare(" AND arm1.arm_primary_status=%s ",$type);
                }
            }
            $user_fields = "u.ID,u.user_registered,u.user_login";
            $user_group_by = " GROUP BY u.ID ";
            $user_order_by = " ORDER BY u.user_registered DESC";
            if ($only_total > 0) {
                $user_fields = " COUNT(*) as total ";
                $user_group_by = "";
                $user_order_by = "";
            }

            if($recent_data == 1) {
                $before_week = strtotime('-6 days', strtotime(current_time('mysql')));
                $before_week = date('Y-m-d 00:00:00', $before_week);
                $current_date = date('Y-m-d 23:59:00', strtotime(current_time('mysql')));
                $user_where .= $wpdb->prepare(" AND (u.user_registered >= %s AND u.user_registered <= %s) ",$before_week,$current_date);
            }

            $user_query = "SELECT {$user_fields} FROM `{$user_table}` u LEFT JOIN `{$usermeta_table}` um ON u.ID = um.user_id {$user_join} {$user_where} {$user_group_by} {$user_order_by} "; 
            $users_details = $wpdb->get_results($user_query);//phpcs:ignore --Reason: $user_query is query

            if ($only_total > 0) {
                $all_members = $users_details[0]->total;
            } else {
                $all_members = $users_details;
            }

            return $all_members;
        }

        function arm_get_all_members_with_administrators($type = 0, $only_total = 0) {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;

            $user_table = $wpdb->users;
            $usermeta_table = $wpdb->usermeta;
            $capability_column = $wpdb->get_blog_prefix($GLOBALS['blog_id']) . 'capabilities';



            $user_where = " WHERE 1=1";


            $user_join = "";
            if (!empty($type) && in_array($type, array(1, 2, 3))) {
                $user_join = " INNER JOIN {$ARMember->tbl_arm_members} arm1 ON u.ID = arm1.arm_user_id";
                $user_where .= $wpdb->prepare(" AND arm1.arm_primary_status=%d ",$type);
            }

            $user_fields = "u.ID,u.user_registered,u.user_login";
            $user_group_by = " GROUP BY u.ID ";
            $user_order_by = " ORDER BY u.user_registered DESC";
            if ($only_total > 0) {
                $user_fields = " COUNT(*) as total ";
                $user_group_by = "";
                $user_order_by = "";
            }

            $user_query = "SELECT {$user_fields} FROM `{$user_table}` u LEFT JOIN `{$usermeta_table}` um ON u.ID = um.user_id {$user_join} {$user_where} {$user_group_by} {$user_order_by} ";
            $users_details = $wpdb->get_results($user_query); //phpcs:ignore --Reason $user_query is a query
            if ($only_total > 0) {
                $all_members = $users_details[0]->total;
            } else {
                $all_members = $users_details;
            }

            return $all_members;
        }

        function arm_get_all_members_without_administrator($type = 0, $only_total = 0, $recent_data = 0,$inactive_type = array()) {
            global $wp, $wpdb, $arm_errors, $ARMember, $armPrimaryStatus, $arm_members_class, $arm_member_forms, $arm_global_settings;
            $all_members = $this->arm_get_all_members($type, $only_total, $recent_data,$inactive_type);
            
            if ($only_total == 0) {
                return $all_members;
            } else {
                return $all_members;
            }
        }

        function arm_get_member_detail($user_id = 0) {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_member_forms, $arm_global_settings;
            if (!empty($user_id) && $user_id != 0) {
                $user_info = get_user_by('id', $user_id);
                $user_meta_info = $this->arm_get_user_metas($user_id);
                if (!empty($user_meta_info)) {
                    $user_info->user_meta = $user_meta_info;
                }
                return $user_info;
            }
            return false;
        }

        function arm_get_user_metas($user_id = 0) {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_member_forms, $arm_global_settings, $arm_subscription_plans;
            if (!empty($user_id) && $user_id != 0) {
                $user_meta_info = get_user_meta($user_id);
                if (!empty($user_meta_info)) {
                    foreach ($user_meta_info as $key => $val) {
                        if ($key == "country") {
                            $user_meta_info[$key] = get_user_meta($user_id, "country", true);
                        } else {
                            $user_meta_info[$key] = maybe_unserialize($val[0]);
                        }
                    }
                }
                return $user_meta_info;
            }
            return false;
        }

        function arm_member_ajax_action() {
            global $wpdb, $ARMember, $arm_members_class, $arm_global_settings, $arm_case_types, $arm_capabilities_global;
            if (!isset($_POST)) { //phpcs:ignore
                return;
            }
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1); //phpcs:ignore --Reason:Verifying nonce
            $action = sanitize_text_field( $_POST['act'] );//phpcs:ignore
            $id = intval($_POST['id']);//phpcs:ignore
            if ($action == 'delete') {
                if (empty($id)) {
                    $errors[] = esc_html__('Invalid action.', 'ARMember');
                } else {
                    if (!current_user_can('arm_manage_members')) {
                        if (MEMBERSHIP_DEBUG_LOG == true) {
                            $arm_case_types['shortcode']['protected'] = true;
                            $arm_case_types['shortcode']['type'] = 'delete_user';
                            $arm_case_types['shortcode']['message'] = esc_html__('Current user doesn\'t have permission to delete users', 'ARMember');
                            $ARMember->arm_debug_response_log('arm_member_ajax_action', $arm_case_types, $_POST, $wpdb->last_query, false); //phpcs:ignore
                        }
                        $errors[] = esc_html__('Sorry, You do not have permission to perform this action', 'ARMember');
                    } else {
                        if (file_exists(ABSPATH . 'wp-admin/includes/user.php')) {
                            require_once(ABSPATH . 'wp-admin/includes/user.php');
                        }

                        //do_action('arm_delete_users_external', $id);

                        if (is_multisite()) {
                            $res_var = remove_user_from_blog($id, $GLOBALS['blog_id']);
                            $blog_id = $GLOBALS['blog_id'];
                            $meta_key = "arm_site_" . $blog_id . "_deleted";
                            $meta_value = true;
                            update_user_meta($id, $meta_key, $meta_value);
                        } else {
                            $res_var = wp_delete_user($id, 1);
                            /* delete user login-logout history starts */
                            $delete_login_history = $wpdb->query( $wpdb->prepare("DELETE FROM `$ARMember->tbl_arm_login_history` where arm_user_id = %d" , $id)); //phpcs:ignore --Reason $ARMember->tbl_arm_login_history is a table name
                            /* delete user login-logout history ends */
                        }
                        if ($res_var) {
                            $message = esc_html__('Record is deleted successfully.', 'ARMember');
                        }
                    }
                }
            }
            $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            echo json_encode($return_array);
            exit;
        }

        function arm_member_bulk_action() {
            global $wpdb, $ARMember, $arm_members_class, $arm_global_settings, $arm_subscription_plans, $arm_case_types, $arm_capabilities_global;
            if (!isset($_POST)) { //phpcs:ignore
                return;
            }

            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1); //phpcs:ignore --Reason:Verifying nonce

            $bulkaction = $arm_global_settings->get_param('action1');
            $ids = $arm_global_settings->get_param('item-action', '');
            if (empty($ids)) {
                $errors[] = esc_html__('Please select one or more records.', 'ARMember');
            } else {
                if ($bulkaction == '' || $bulkaction == '-1') {
                    $errors[] = esc_html__('Please select valid action.', 'ARMember');
                } else {
                    if (!is_array($ids)) {
                        $ids = explode(',', $ids);
                    }
                    if ($bulkaction == 'delete_member') {
                        if (!current_user_can('arm_manage_members')) {
                            if (MEMBERSHIP_DEBUG_LOG == true) {
                                $arm_case_types['shortcode']['protected'] = true;
                                $arm_case_types['shortcode']['type'] = 'delete_user_bulk_action';
                                $arm_case_types['shortcode']['message'] = esc_html__('Current user doesn\'t have permission to delete users', 'ARMember');
                                $ARMember->arm_debug_response_log('arm_member_bulk_action', $arm_case_types, $_POST, $wpdb->last_query, false); //phpcs:ignore
                            }
                            $errors[] = esc_html__('Sorry, You do not have permission to perform this action', 'ARMember');
                        } else {
                            if (is_array($ids)) {
                                if (file_exists(ABSPATH . 'wp-admin/includes/user.php')) {
                                    require_once(ABSPATH . 'wp-admin/includes/user.php');
                                }
                                foreach ($ids as $id) {
                                    if (is_multisite()) {
                                        $res_var = remove_user_from_blog($id, $GLOBALS['blog_id']);
                                        $blog_id = $GLOBALS['blog_id'];
                                        $meta_key = "arm_site_" . $blog_id . "_deleted";
                                        $meta_value = true;
                                        update_user_meta($id, $meta_key, $meta_value);
                                        if (MEMBERSHIP_DEBUG_LOG == true) {
                                            $arm_case_types['shortcode']['protected'] = true;
                                            $arm_case_types['shortcode']['type'] = 'user_removed';
                                            $arm_case_types['shortcode']['message'] = esc_html__('User is removed from current blog', 'ARMember');
                                            $ARMember->arm_debug_response_log('arm_member_bulk_action', $arm_case_types, $_POST, $wpdb->last_query, false); //phpcs:ignore
                                        }
                                    } else {
                                        $res_var = wp_delete_user($id, 1);
                                        $delete_login_history = $wpdb->query( $wpdb->prepare("DELETE FROM `$ARMember->tbl_arm_login_history` where arm_user_id = %d" , $id) ); //phpcs:ignore --Reason $ARMember->tbl_arm_login_history is a table name
                                    }
                                }
                                $message = esc_html__('Member(s) has been deleted successfully.', 'ARMember');
                            }
                        }
                    } elseif($bulkaction == 'arm_user_status-1' || $bulkaction == 'arm_user_status-2' || $bulkaction == 'arm_user_status-3' || $bulkaction == 'arm_user_status-4') {
                        if (!current_user_can('arm_manage_members')) {
                            if (MEMBERSHIP_DEBUG_LOG == true) {
                                $arm_case_types['shortcode']['protected'] = true;
                                $arm_case_types['shortcode']['type'] = 'delete_user_bulk_action';
                                $arm_case_types['shortcode']['message'] = esc_html__('Current user doesn\'t have permission to delete users', 'ARMember');
                                $ARMember->arm_debug_response_log('arm_member_bulk_action', $arm_case_types, $_POST, $wpdb->last_query, false);  //phpcs:ignore
                            }
                            $errors[] = esc_html__('Sorry, You do not have permission to perform this action', 'ARMember');
                        } else {
                            if (is_array($ids)) {
                                if ($bulkaction == 'arm_user_status-1') {
                                    $bulkaction = '1';
                                } else if ($bulkaction == 'arm_user_status-2') {
                                    $bulkaction = '2';
				} else if ($bulkaction == 'arm_user_status-3') {
                                    $bulkaction = '3';
                                } else if ($bulkaction == 'arm_user_status-4') {
                                    $bulkaction = '4';
                                }
                                foreach ($ids as $id) {
                                    $post = array(
                                        'user_id' => $id,
                                        'bulkaction' => $bulkaction
                                    );
                                    $this->arm_change_user_status($post);
                                }
				$message = esc_html__('Member(s) status has been changed successfully.', 'ARMember');
                            }
                        }
                    } else {
                        if (is_array($ids) && is_numeric($bulkaction)) {
                            $plan = new ARM_Plan($bulkaction);
                            if ($plan->exists() && $plan->is_active()) {
                                foreach ($ids as $id) {
                                    do_action('arm_before_update_user_subscription', $id, $bulkaction);
                                    $this->arm_manual_update_user_data($id, $bulkaction);
                                    $arm_subscription_plans->arm_update_user_subscription($id, $bulkaction, 'admin', false);
                                }
                                $message = esc_html__('Member(s) plan has been changed successfully.', 'ARMember');
                            } else {
                                $errors[] = esc_html__('Selected plan is invalid.', 'ARMember');
                            }
                        }
                    }
                }
            }
            $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            if(!empty($message))
            {
                $ARMember->arm_set_message('success', $message);
            }
            echo json_encode($return_array);
            exit;
        }

        function arm_validate_username($user_login, $invalid_username = '') {
            global $wpdb, $ARMember, $arm_members_class, $arm_global_settings;
            $sanitized_user_login = sanitize_user($user_login);
            $err = "";
            // Check the username
            if ($sanitized_user_login == '') {
                $err = esc_html__('Please enter a username.', 'ARMember');
            } elseif (!validate_username($user_login)) {
                if ($invalid_username == '') {
                    $err_msg = esc_html__('This username is invalid because it uses illegal characters. Please enter a valid username.', 'ARMember');
                } else {
                    $err_msg = $invalid_username;
                }
                $err = (!empty($err_msg)) ? $err_msg : esc_html__('This username is invalid because it uses illegal characters. Please enter a valid username.', 'ARMember');
            } elseif (username_exists($sanitized_user_login)) {
                $err_msg = $arm_global_settings->common_message['arm_username_exist'];
                $err = (!empty($err_msg)) ? $err_msg : esc_html__('This username is already registered, please choose another one.', 'ARMember');
            }
            return $err;
        }

        function arm_validate_email($user_email, $invalid_email = '') {
            global $wpdb, $ARMember, $arm_members_class, $arm_global_settings;
            $err = "";
            // Check the username
            if ('' == $user_email) {
                $err = esc_html__("Please type your e-mail address.", 'ARMember');
            } elseif (!is_email($user_email)) {
                if ($invalid_email == '') {
                    $err_msg = esc_html__('Please enter valid email address.', 'ARMember');
                } else {
                    $err_msg = $invalid_email;
                }
                $err = (!empty($err_msg)) ? $err_msg : esc_html__('Please enter valid email address.', 'ARMember');
            } elseif (email_exists($user_email)) {
                $err_msg = $arm_global_settings->common_message['arm_email_exist'];
                $err = (!empty($err_msg)) ? $err_msg : esc_html__("This email is already registered, please choose another one.", 'ARMember');
            }
            return $err;
        }

        function arm_user_register_hook_func($user_id) {
            global $wp, $wpdb, $current_user, $ARMember, $arm_global_settings, $arm_members_badges;
            $this->arm_add_update_member_profile($user_id);
        }

        function arm_profile_update_hook_func($user_id, $old_user_data) {
            global $wp, $wpdb, $current_user, $ARMember, $arm_global_settings, $arm_members_badges;
            /* is_admin() is not giving right result here please make sure with isAdmin Condition */
            if (is_admin() && !isset($_POST['isAdmin'])) { //phpcs:ignore
                if (is_plugin_active('bbpress/bbpress.php')) {
                    if (isset($_POST['bbp-forums-role']) && $_POST['bbp-forums-role'] != '') { //phpcs:ignore
                        $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'roles', sanitize_text_field($_POST['bbp-forums-role'])); //phpcs:ignore
                    }
                }
                if (isset($_POST['role'])) { //phpcs:ignore
                    $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'roles', sanitize_text_field($_POST['role'])); //phpcs:ignore
                }
            }
            $this->arm_add_update_member_profile($user_id);
        }

        /* Add member to plugin table when assign user to site from network site menu */

        function arm_assign_user_to_blog($user_id, $role, $blog_id) {
            if (!is_multisite()) {
                return;
            }
            global $wp, $wpdb, $current_user, $ARMember, $arm_global_settings;
            /* Check if user is already deleted from current blog */
            $deleted_user = get_user_meta($user_id, "arm_site_" . $blog_id . "_deleted", true);
            if ($deleted_user == 1) {
                delete_user_meta($user_id, "arm_site_" . $blog_id . "_deleted");
            }
            $this->arm_add_update_member_profile($user_id, $blog_id);
        }

        function arm_add_update_member_profile($user_id, $blog_id = 0) {
            global $wp, $wpdb, $current_user, $ARMember, $arm_global_settings;
            if (!empty($user_id) && $user_id != 0) {
                $arm_member_table = $ARMember->tbl_arm_members;
                if (is_multisite() && $blog_id > 0) {
                    $arm_member_table = $wpdb->get_blog_prefix($blog_id) . 'arm_members';
                }
                $member = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE `ID`=%d",$user_id), ARRAY_A); //phpcs:ignore  --Reason $wpdb->users is a table name
                /* Add WP Members into Plugin's Member Table */
                $args = array(
                    'arm_user_id' => $user_id,
                    'arm_user_login' => $member['user_login'],
                    'arm_user_nicename' => $member['user_nicename'],
                    'arm_user_email' => $member['user_email'],
                    'arm_user_url' => $member['user_url'],
                    'arm_user_registered' => $member['user_registered'],
                    'arm_user_activation_key' => $member['user_activation_key'],
                    'arm_user_status' => $member['user_status'],
                    'arm_display_name' => $member['display_name'],
                );
                $old_record = $wpdb->get_var( $wpdb->prepare("SELECT `arm_member_id` FROM `" . $arm_member_table . "` WHERE `arm_user_id`=%d",$user_id)); //phpcs:ignore --Reason $arm_member_table is a table name
                if ($old_record != null) {
                    $wpdb->update($arm_member_table, $args, array('arm_user_id' => $user_id));
                } else {
                    $wpdb->insert($arm_member_table, $args);
                }
            }
            return;
        }

        public function arm_activate_member($user_id = 0) {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_case_types;
            if (!empty($user_id) && $user_id != 0) {
                do_action('arm_before_activate_member', $user_id);
                arm_set_member_status($user_id, 1);
                return true;
            }
            if (MEMBERSHIP_DEBUG_LOG == true) {
                $arm_case_types['shortcode']['protected'] = true;
                $arm_case_types['shortcode']['type'] = 'member_activation';
                $arm_case_types['shortcode']['message'] = esc_html__('Member couldn\'t be activate', 'ARMember');
                $ARMember->arm_debug_response_log('arm_activate_member', $arm_case_types, $arm_errors, $wpdb->last_query, false);
            }
            return false;
        }

        public function arm_deactivate_member($user_id = 0) {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_case_types;
            if (!empty($user_id) && $user_id != 0) {
                $this->arm_add_member_activation_key($user_id);
                return true;
            }
            if (MEMBERSHIP_DEBUG_LOG == true) {
                $arm_case_types['shortcode']['protected'] = true;
                $arm_case_types['shortcode']['type'] = 'member_activation';
                $arm_case_types['shortcode']['message'] = esc_html__('Member couldn\'t be deactivate', 'ARMember');
                $ARMember->arm_debug_response_log('arm_deactivate_member', $arm_case_types, $arm_errors, $wpdb->last_query, false);
            }
            return false;
        }

        //Insert Activation Key.
        public function arm_add_member_activation_key($user_id) {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
            if (!empty($user_id) && $user_id != 0) {
                //Generate activation key
                $activation_key = wp_generate_password(10);
                //Add key to the user meta
                update_user_meta($user_id, 'arm_user_activation_key', $activation_key);
            }
        }

        //Validate User Activation Key
        public function arm_verify_user_activation($user_email, $key) {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_global_settings;
            if (!isset($user_email) || empty($user_email)) {
                $err_msg = $arm_global_settings->common_message['arm_user_not_exist'];
                $err_msg = (!empty($err_msg)) ? $err_msg : esc_html__('User does not exist.', 'ARMember');
                $arm_errors->add('empty_username', $err_msg);
                return $arm_errors;
            }
            //Get user data.
            $user_data = get_user_by('email', $user_email);
            $activation_key = get_user_meta($user_data->ID, 'arm_user_activation_key', true);
            if (!empty($user_data) && (empty($activation_key) || $activation_key == '')) {
                $err_msg = $arm_global_settings->common_message['arm_already_active_account'];
                $message = (!empty($err_msg)) ? $err_msg : esc_html__('Your account has been activated.', 'ARMember');
                $arm_errors->add('empty_username', $message, 'message');
            } else if ($activation_key == $key) {
                /* Update Activation Status */
                arm_set_member_status($user_data->ID, 1);
                /* Send New User Notification Mail */
                armMemberSignUpCompleteMail($user_data);
                /* Send Account Verify Notification Mail */
                armMemberAccountVerifyMail($user_data);
                /* Activation Success Message */
                $message = (!empty($arm_global_settings->common_message['arm_already_active_account'])) ? $arm_global_settings->common_message['arm_already_active_account'] : esc_html__('Your account has been activated, please login to view your profile.', 'ARMember');
                $arm_errors->add('empty_username', $message, 'message');
            } else {
                $err_msg = (!empty($arm_global_settings->common_message['arm_expire_activation_link'])) ? $arm_global_settings->common_message['arm_expire_activation_link'] : esc_html__('Activation link is expired or invalid.', 'ARMember');
                $arm_errors->add('empty_username', $err_msg);
            }
            return $arm_errors;
        }

        /**
         * Verify User Before Login.
         */
        public function arm_user_register_verification($user, $user_login, $password) {
            global $wp, $wpdb, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans;
            $activation_key = '';
            //Check For Activation Key.
            if (isset($_GET['arm-key']) && !empty($_GET['arm-key'])) {
                $chk_key = stripslashes_deep(sanitize_text_field($_GET['arm-key']));
                $user_email = stripslashes_deep(sanitize_email($_GET['email'])); //phpcs:ignore
                return $this->arm_verify_user_activation($user_email, $chk_key);
            }
            //Check if blank form submited.
            if (empty($user_login) || empty($password)) {
                // figure out which one
                if (empty($user_login)) {
                    $arm_errors->add('empty_username', esc_html__('The username field is empty.', 'ARMember'));
                }
                if (empty($password)) {
                    $arm_errors->add('empty_password', esc_html__('The password field is empty.', 'ARMember'));
                }
                // remove the ability to authenticate
                remove_action('authenticate', 'wp_authenticate_username_password', 20);
                // return appropriate error
                return $arm_errors;
            }
            $user_info = get_user_by('login', $user_login);
            if ($user_info == false) {
                /* Allow User to login with Email Address */
                $user_info = get_user_by('email', $user_login);
                $user_login = ($user_info == false) ? $user_login : $user_info->user_login;
            
                $err_msg = $arm_global_settings->common_message['arm_user_not_exist'];
                $err_msg = (!empty($err_msg)) ? $err_msg : esc_html__('User does not exist.', 'ARMember');
                $arm_errors->add('invalid_username', $err_msg);
                // remove the ability to authenticate
                remove_action('authenticate', 'wp_authenticate_username_password', 20);
                return $arm_errors;
            } else {
                //Allow Super Admin be Logged-In without checking any conditions.
                if (is_super_admin($user_info->ID)) {
                    return $user;
                    exit;
                }
                /* ----------------------/.Begin User's Subscription Expire Process./---------------------- */
                //Check if User's plan is expired or not
                $plan_ids = get_user_meta($user_info->ID, 'arm_user_plan_ids', true);
                if (!empty($plan_ids) && is_array($plan_ids)) {
                    foreach ($plan_ids as $plan_id) {
                        if (!empty($plan_id) && $plan_id != 0) {
                            $now_time = strtotime(current_time('mysql'));

                            $plaData = get_user_meta($user_info->ID, 'arm_user_plan_' . $plan_id, true);
                            $expire_time = !empty($plaData['arm_expire_plan']) ? $plaData['arm_expire_plan'] : '' ;
                            if (!empty($expire_time) && $now_time >= $expire_time) {
                                $arm_subscription_plans->arm_user_plan_status_action(array('plan_id' => $plan_id, 'user_id' => $user_info->ID, 'action' => 'eot'));
                            }
                        }
                    }
                }
                /* ----------------------/.End User's Subscription Expire Process./---------------------- */
                $activation_key = get_user_meta($user_info->ID, 'arm_user_activation_key', true);
            }
            $user_register_verification = $arm_global_settings->arm_get_single_global_settings('user_register_verification', 'auto');
            if (empty($activation_key) || in_array($user_register_verification, array('auto', 'email', 'manual'))) {

                $user_status = apply_filters('arm_check_member_status_before_login', TRUE, $user_info->ID); //Check Member Status Before Login.
                if ($user_status == TRUE) {

                    return $user;
                    exit;
                } else {

                    if ($user_status == FALSE) {
                        $err_msg = $arm_global_settings->common_message['arm_not_authorized_login'];
                        $err_msg = (!empty($err_msg)) ? $err_msg : esc_html__('You are not authorized to login.', 'ARMember');
                        $arm_errors->add('access_denied', $err_msg);
                    } else {
                        $arm_errors = $user_status;
                    }
                    remove_action('authenticate', 'wp_authenticate_username_password', 20);
                    return $arm_errors;
                    exit;
                }
            }
        }

        function arm_members_hide_column() {
            global $ARMember, $arm_capabilities_global;

            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1); //phpcs:ignore --Reason:Verifying nonce

            $column_list = isset($_POST['column_list']) ? sanitize_text_field($_POST['column_list']) : ''; //phpcs:ignore
            $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : '0'; //phpcs:ignore
            if ($column_list != "") {
                $user_id = get_current_user_id();
                $members_column_list = explode(',', $column_list);
                $members_show_hide_serialize = maybe_serialize($members_column_list);
                //update_option('arm_members_hide_show_columns', $members_show_hide_serialize);
                $prev_value = maybe_unserialize(get_user_meta($user_id, 'arm_members_hide_show_columns_' . $form_id, true));
                update_user_meta($user_id, 'arm_members_hide_show_columns_' . $form_id, $members_show_hide_serialize);
            }
            die();
        }

        function arm_filter_members_list() {
            global $ARMember, $arm_capabilities_global;

            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1); //phpcs:ignore --Reason:Verifying nonce.
            if (file_exists(MEMBERSHIP_VIEWS_DIR . '/arm_members_list_records.php')) {
                include( MEMBERSHIP_VIEWS_DIR . '/arm_members_list_records.php');
            }
            die();
        }

        function arm_handle_import_export($request) {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_capabilities_global;
            if (isset($request['arm_action']) && !empty($request['arm_action'])) {

                $ARMember->arm_check_user_cap( $arm_capabilities_global['arm_import_export'], '1',1); //phpcs:ignore --Reason:Verifying nonce

                switch ($request['arm_action']) {
                    case 'user_export_csv':
                    case 'user_export_xls':
                    case 'user_export_xml':
                        self::arm_user_export_handle($request);
                        break;
                    case 'settings_export':
                        self::arm_settings_export_handle($request);
                        break;
                    case 'settings_import':
                        self::arm_settings_import_handle($request);
                        break;
                    case 'download_sample':
                        self::arm_download_sample_csv($request);
                        break;
                    default:
                        break;
                }
            }
        }

        function arm_get_user_import_default_fields() {
            global $wp, $wpdb, $ARMember;
            $userdata_fields = array(
                'userdata' => array(
                    'ID' => 'ID', 'id' => 'ID',
                    'user_login' => 'user_login', 'username' => 'user_login', 'login' => 'user_login',
                    'user_pass' => 'user_pass', 'password' => 'user_pass',
                    'user_email' => 'user_email', 'email' => 'user_email',
                    'user_url' => 'user_url', 'website' => 'user_url', 'url' => 'user_url',
                    'user_nicename' => 'user_nicename', 'nicename' => 'user_nicename',
                    'display_name' => 'display_name', 'name' => 'display_name',
                    'user_registered' => 'user_registered', 'registered' => 'user_registered', 'joined' => 'user_registered',
                    'role' => 'role', 'user_role' => 'role',
                    'first_name' => 'first_name', 'firstname' => 'first_name',
                    'last_name' => 'last_name', 'lastname' => 'last_name',
                    'nickname' => 'nickname',
                    'description' => 'description', 'biographical_info' => 'description',
                    'rich_editing' => 'rich_editing',
                    'show_admin_bar_front' => 'show_admin_bar_front',
                    'admin_color' => 'admin_color',
                    'use_ssl' => 'use_ssl',
                    'comment_shortcuts' => 'comment_shortcuts'
                ),
                'usermeta' => array(
                    'subscription_plan' => 'arm_user_plan_ids', 'plan' => 'arm_user_plan_ids',
                    'status' => 'status', 'member_status' => 'status', 'user_status' => 'status',
                    /* import time manually start plan */
                    'arm_subscription_start_date' => 'arm_subscription_start_date'
                )
            );
            $userdata_fields = apply_filters('arm_user_import_default_fields', $userdata_fields);
            return $userdata_fields;
        }

        function arm_handle_import_user_meta() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_case_types, $arm_member_forms, $arm_capabilities_global;
            $ARMember->arm_session_start();

            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_import_export'], '1',1); //phpcs:ignore --Reason:Verifying nonce

            set_time_limit(0);
            $file_data_array = $errors = array();
            $request = $_POST;//phpcs:ignore
            $_SESSION['imported_users'] = 0;
            $action = sanitize_text_field($request['arm_action']);
            $up_file = sanitize_text_field($request['import_user']);
            if (isset($up_file)) {
                $up_file_ext = strtolower(pathinfo($up_file, PATHINFO_EXTENSION));
                if (in_array($up_file_ext, array('csv', 'xls', 'xlsx', 'xml'))) {
                    if ($up_file_ext == 'xml') {

                        if(file_exists(ABSPATH . 'wp-admin/includes/file.php')){
                            require_once(ABSPATH . 'wp-admin/includes/file.php');
                        }
        
                        WP_Filesystem();
                        global $wp_filesystem;
                        $arm_loader_url = MEMBERSHIP_UPLOAD_DIR . '/' . basename($up_file);
                        $fileContent = $wp_filesystem->get_contents($arm_loader_url);

                        $xmlData = armXML_to_Array($fileContent);
                        if (isset($xmlData['members']['member']) && !empty($xmlData['members']['member'])) {
                            $file_data_array = $xmlData['members']['member'];
                        } else {
                            if (MEMBERSHIP_DEBUG_LOG == true) {
                                $arm_case_types['shortcode']['protected'] = true;
                                $arm_case_types['shortcode']['type'] = 'import_user_xml';
                                $arm_case_types['shortcode']['message'] = esc_html__('Error during file upload', 'ARMember');
                                $ARMember->arm_debug_response_log('arm_handle_import_user', $arm_case_types, $xmlData, $wpdb->last_query, false);
                            }
                            $errors[] = esc_html__('Error during file upload.', 'ARMember');
                        }
                    } else {
                        //Read CSV, XLS Files
                        if (file_exists(MEMBERSHIP_LIBRARY_DIR . '/class-readcsv.php')) {
                            require_once(MEMBERSHIP_LIBRARY_DIR . '/class-readcsv.php');
                        }
                        $csv_reader = new ReadCSV(MEMBERSHIP_UPLOAD_DIR . '/' . basename($up_file));
                        if ($csv_reader->is_file == TRUE) {
                            $file_data_array = $csv_reader->get_data();
                        } else {
                            if (MEMBERSHIP_DEBUG_LOG == true) {
                                $arm_case_types['shortcode']['protected'] = true;
                                $arm_case_types['shortcode']['type'] = 'import_user_CSV';
                                $arm_case_types['shortcode']['message'] = esc_html__('Error during file upload', 'ARMember');
                                $ARMember->arm_debug_response_log('arm_handle_import_user', $arm_case_types, $csv_reader, $wpdb->last_query, false);
                            }
                            $errors[] = esc_html__('Error during file upload.', 'ARMember');
                        }
                    }

                    $allready_exists = array('username', 'email', 'website', 'joined', 'user_nicename', 'display_name', 'user_pass', 'biographical_info');
                    $allready_exists_meta = $arm_member_forms->arm_get_db_form_fields(true);
                    $select_user_meta = array();
                    foreach ($allready_exists_meta as $exist_meta) {
                        array_push($select_user_meta, $exist_meta['id']);
                        array_push($select_user_meta, $exist_meta['label']);
                        array_push($select_user_meta, $exist_meta['meta_key']);
                    }
                    $exists_user_meta = array_merge_recursive($allready_exists, $select_user_meta);
                    $dbProfileFields = $arm_member_forms->arm_get_db_form_fields();
                    if (!empty($file_data_array[0])):
                        ?><label class = "account_detail_radio arm_account_detail_options">
                            <input type="checkbox" class="arm_icheckbox arm_import_all_user_meta" name="arm_import_all_user_meta" id="arm_import_all_user_meta" />
                            <label for="arm_import_all_user_meta"><?php esc_html_e('Select All Meta', 'ARMember'); ?></label>
                            <div class="arm_list_sortable_icon"></div>
                        </label><?php
                        foreach ($file_data_array[0] as $key => $title):
                            $title = '';
                            switch ($key):
                                case 'id':
                                    $title = esc_html__('User ID', 'ARMember');
                                    break;
                                case 'username':
                                    $title = esc_html__('Username', 'ARMember');
                                    break;
                                case 'email':
                                    $title = esc_html__('Email Address', 'ARMember');
                                    break;
                                case 'first_name':
                                    $title = esc_html__('First Name', 'ARMember');
                                    break;
                                case 'last_name':
                                    $title = esc_html__('Last Name', 'ARMember');
                                    break;
                                case 'nickname':
                                    $title = esc_html__('Nick Name', 'ARMember');
                                    break;
                                case 'display_name':
                                    $title = esc_html__('Display Name', 'ARMember');
                                    break;
                                case 'biographical_info':
                                    $title = esc_html__('Info', 'ARMember');
                                    break;
                                case 'website':
                                    $title = esc_html__('Website', 'ARMember');
                                    break;
                                case 'joined':
                                    $title = esc_html__('Joined Date', 'ARMember');
                                    break;
                                case 'arm_subscription_start_date':
                                    $title = esc_html__('Subscription Start Date', 'ARMember');
                                    break;
                                default:
                                    if (!in_array($key, array('role', 'status', 'subscription_plan'))) {
                                        $title = $key;
                                        if (!empty($dbProfileFields['default'])) {
                                            foreach ($dbProfileFields['default'] as $fieldMetaKey => $fieldOpt) {
                                                if (empty($fieldMetaKey) || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme','arm_captcha'))) {
                                                    continue;
                                                }
                                                if ($fieldMetaKey == $key) {
                                                    $title = $fieldOpt['label'];
                                                }
                                            }
                                        }

                                        if (!empty($dbProfileFields['other'])) {

                                            foreach ($dbProfileFields['other'] as $fieldMetaKey => $fieldOpt) {
                                                if (empty($fieldMetaKey) || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme','arm_captcha'))) {
                                                    continue;
                                                }
                                                if ($fieldMetaKey == $key) {
                                                    $title = $fieldOpt['label'];
                                                }
                                            }
                                        }
                                    }
                                    break;
                            endswitch;

                            if ($key == 'id' || $title == ''):
                                continue;
                            endif;
                            $checkedDefault = " checked='checked' disabled='disabled' ";
                            if (!in_array($key, array('username', 'email'))) {
                                $checkedDefault = "";
                            }
                            $user_meta = (in_array($key, $exists_user_meta) || in_array(str_replace(' ', '_', $key), $exists_user_meta)) ? esc_html__('Existing', 'ARMember') : esc_html__('New', 'ARMember');
                            ?>
                            <label class = "account_detail_radio arm_account_detail_options">
                                <input type = "checkbox" value = "<?php echo esc_attr($key); ?>" class = "arm_icheckbox arm_import_user_meta" name = "import_user_meta[<?php echo esc_attr($key); ?>]" id = "arm_profile_field_input_<?php echo esc_attr($key); ?>" <?php echo esc_html($checkedDefault); ?> />
                                <label for="arm_profile_field_input_<?php echo esc_attr($key); ?>"><?php echo esc_html($title); ?></label>
                                <div class="arm_list_sortable_icon"></div>
                                <span class="arm_user_meta_<?php echo esc_attr($user_meta); ?> arm_user_meta_existing_meta_txt" style="color: gray;font-size: 11px; font-style: italic; text-align: center; width: 100%; margin: 0 0 0 34px;"><?php echo '(' . esc_html($user_meta) . esc_html__(' Meta', 'ARMember') . ')'; ?> </span>
                            </label>
                            <?php
                        endforeach;
                    endif;
                }
            }
            exit;
        }

        function arm_handle_import_user() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_case_types, $arm_member_forms, $arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_import_export'], '1',1); //phpcs:ignore --Reason: Verifying nonce

            set_time_limit(0);

            $file_data_array = $user_ids = $u_errors = $errors = array();
            $request = $_POST;//phpcs:ignore
            $action = sanitize_text_field($request['arm_action']);
            $up_file = sanitize_text_field($request['import_user']);
            $dbProfileFields = $arm_member_forms->arm_get_db_form_fields();

            $grid_columns = array();
            $arm_grid_columns = explode(',', $request['arm_user_metas_to_import']);
            foreach ($arm_grid_columns as $key => $val) {
                switch ($val):
                    case 'id':
                        $grid_columns[$val] = esc_html__('User ID', 'ARMember');
                        break;
                    case 'username':
                        $grid_columns[$val] = esc_html__('Username', 'ARMember');
                        break;
                    case 'email':
                        $grid_columns[$val] = esc_html__('Email Address', 'ARMember');
                        break;
                    case 'first_name':
                        $grid_columns[$val] = esc_html__('First Name', 'ARMember');
                        break;
                    case 'last_name':
                        $grid_columns[$val] = esc_html__('Last Name', 'ARMember');
                        break;
                    case 'nickname':
                        $grid_columns[$val] = esc_html__('Nick Name', 'ARMember');
                        break;
                    case 'display_name':
                        $grid_columns[$val] = esc_html__('Display Name', 'ARMember');
                        break;
                    case 'biographical_info':
                        $grid_columns[$val] = esc_html__('Info', 'ARMember');
                        break;
                    case 'website':
                        $grid_columns[$val] = esc_html__('Website', 'ARMember');
                        break;
                    case 'joined':
                        $grid_columns[$val] = esc_html__('Joined Date', 'ARMember');
                        break;
                    case 'arm_subscription_start_date':
                        $grid_columns[$val] = esc_html__('Subscription Start Date', 'ARMember');
                        break;
                    default:
                        if (!in_array($val, array('role', 'status', 'subscription_plan'))) {
                            $grid_columns[$val] = $val;
                            if (!empty($dbProfileFields['default'])) {
                                foreach ($dbProfileFields['default'] as $fieldMetaKey => $fieldOpt) {
                                    if (empty($fieldMetaKey) || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme'))) {
                                        continue;
                                    }
                                    if ($fieldMetaKey == $val) {
                                        $grid_columns[$val] = $fieldOpt['label'];
                                    }
                                }
                            }

                            if (!empty($dbProfileFields['other'])) {

                                foreach ($dbProfileFields['other'] as $fieldMetaKey => $fieldOpt) {
                                    if (empty($fieldMetaKey) || in_array($fieldOpt['type'], array('hidden', 'html', 'section', 'rememberme'))) {
                                        continue;
                                    }
                                    if ($fieldMetaKey == $val) {
                                        $grid_columns[$val] = $fieldOpt['label'];
                                    }
                                }
                            }
                        }
                        break;
                endswitch;
            }

            $up_plan_id = !empty($request['plan_id']) ? intval($request['plan_id']) : 0;
            $users_data = array();
            if (isset($up_file)) {
                $up_file_ext = pathinfo($up_file, PATHINFO_EXTENSION);
                if (in_array($up_file_ext, array('csv', 'xls', 'xlsx', 'xml'))) {
                    if ($up_file_ext == 'xml') {

                        if(file_exists(ABSPATH . 'wp-admin/includes/file.php')){
                            require_once(ABSPATH . 'wp-admin/includes/file.php');
                        }
        
                        WP_Filesystem();
                        global $wp_filesystem;
                        $arm_loader_url = MEMBERSHIP_UPLOAD_DIR . '/' . basename($up_file);
                        $fileContent = $wp_filesystem->get_contents($arm_loader_url);

                        $xmlData = armXML_to_Array($fileContent);
                        if (isset($xmlData['members']['member']) && !empty($xmlData['members']['member'])) {
                            $file_data_array = $xmlData['members']['member'];
                        } else {
                            if (MEMBERSHIP_DEBUG_LOG == true) {
                                $arm_case_types['shortcode']['protected'] = true;
                                $arm_case_types['shortcode']['type'] = 'import_user_xml';
                                $arm_case_types['shortcode']['message'] = esc_html__('Error during file upload', 'ARMember');
                                $ARMember->arm_debug_response_log('arm_handle_import_user', $arm_case_types, $xmlData, $wpdb->last_query, false);
                            }
                            $errors[] = esc_html__('Error during file upload.', 'ARMember');
                        }
                    } else {
                        //Read CSV, XLS Files
                        if (file_exists(MEMBERSHIP_LIBRARY_DIR . '/class-readcsv.php')) {
                            require_once(MEMBERSHIP_LIBRARY_DIR . '/class-readcsv.php');
                        }
                        $csv_reader = new ReadCSV(MEMBERSHIP_UPLOAD_DIR . '/' . basename($up_file));
                        if ($csv_reader->is_file == TRUE) {
                            $file_data_array = $csv_reader->get_data();
                        } else {
                            if (MEMBERSHIP_DEBUG_LOG == true) {
                                $arm_case_types['shortcode']['protected'] = true;
                                $arm_case_types['shortcode']['type'] = 'import_user_CSV';
                                $arm_case_types['shortcode']['message'] = esc_html__('Error during file upload', 'ARMember');
                                $ARMember->arm_debug_response_log('arm_handle_import_user', $arm_case_types, $csv_reader, $wpdb->last_query, false);
                            }
                            $errors[] = esc_html__('Error during file upload.', 'ARMember');
                        }
                    }
                    $users_array = array();
                    $arm_uniqe_user = array();
                    if (!empty($file_data_array)) {
                        $is_password_column = 0;
                        $count_row = 0;
                        foreach ($file_data_array as $fdaVal) {
                            if (isset($fdaVal['user_pass'])) {
                                $is_password_column = 1;
                            }
                            $fdaVal['username'] = isset($fdaVal['username']) ? $fdaVal['username'] : '';
                            $fdaVal['email'] = isset($fdaVal['email']) ? $fdaVal['email'] : '';
                            if (!empty($arm_uniqe_user) && ( in_array($fdaVal['username'], $arm_uniqe_user) || in_array($fdaVal['email'], $arm_uniqe_user) )) {
                                continue;
                            }
                            array_push($arm_uniqe_user, $fdaVal['username']);
                            array_push($arm_uniqe_user, $fdaVal['email']);
                            if (isset($fdaVal['username']) && !empty($fdaVal['username'])) {
                                //$users_array[] = $fdaVal;
                                foreach ($grid_columns as $key => $val) {
                                    $users_array[$count_row][$key] = htmlspecialchars(utf8_encode($fdaVal[$key]), ENT_NOQUOTES);
                                    //$users_array[$count_row][$key] = htmlspecialchars($fdaVal[$key], ENT_NOQUOTES);
                                }
                                $count_row++;
                            }
                        }
                    }
                    unset($arm_uniqe_user);
                    if (!empty($users_array))
                    {
                ?>
                        <div class="">
                            <span class="arm_info_text"><?php esc_html_e(" Note that importing user's data will", 'ARMember'); ?><strong> <?php esc_html_e('Skip', 'ARMember'); ?> </strong><?php esc_html_e("existing user(s), if any duplicate user found.", 'ARMember'); ?>
                                <br/>
                                ( <?php esc_html_e('Cosidering duplicate', 'ARMember'); ?> <strong><?php esc_html_e('Username', 'ARMember'); ?> </strong><?php esc_html_e('and', 'ARMember'); ?><strong> <?php esc_html_e('Email', 'ARMember'); ?></strong> )
                            </span>
                            <table width="100%" cellspacing="0">
                                <tr>
                                    <th class="center cb-select-all-th arm_max_width_60 arm_text_align_center" ><input id="cb-select-all-1" type="checkbox" class="chkstanard arm_all_import_user_chks"></th>
                <?php
                                    if (!empty($grid_columns)):
                                        foreach ($grid_columns as $key => $title):
                                            if ($key == 'id'):
                                                continue;
                                            endif;
                ?>
                                            <th data-key="<?php echo esc_attr($key); ?>" class="arm_grid_th_<?php echo esc_attr($key); ?>" style="min-width: 100px;"><?php echo esc_html($title); ?></th>
                <?php
                                        endforeach;
                                    endif;
                ?>
                                </tr>
                <?php
                                foreach ($users_array as $value) {
                ?>
                                    <tr>
                                        <td>
                <?php
                                        /* Check User's `username` or `email` If user exist AND if `Update User` Set to true */
                                        if (isset($value['username'])) {
                                            $user = get_user_by('login', $value['username']);
                                        }
                                        if (!$user && isset($value['email'])) {
                                            $user = get_user_by('email', $value['email']);
                                        }
                                        $user_disable = '';
                                        if ($user || empty($value['email']) || !is_email($value['email'])) {
                                            $user_disable = 'disabled=disabled';
                                        } else {
                                            $users_data[$value['username']] = $value;
                                        }
                ?>
                                            <input id="cb-item-action-<?php echo esc_attr($value['username']); ?>" <?php echo esc_attr($user_disable); ?> class="chkstanard arm_import_user_chks" type="checkbox" value="<?php echo esc_attr($value['username']); ?>" name="item-action[]">
                                        </td>
                <?php
                                        foreach ($grid_columns as $key => $val) {
                                            echo isset($value[$key]) ? (!empty($value[$key])) ? '<td>' . utf8_encode($value[$key]) . '</td>' : '<td>-</td>' : ''; //phpcs:ignore
                                            //echo isset($value[$key]) ? (!empty($value[$key])) ? '<td>' . $value[$key] . '</td>' : '<td>-</td>' : '';
                                        }
                ?>
                                    </tr>									
                <?php
                                }
                ?>
                            </table>
                            <input type="hidden" id="arm_import_file_url" name="file_url" value="<?php echo esc_url($up_file); ?>" />
                            <input type="hidden" id="arm_import_plan_id" name="plan_id" value="<?php echo intval( $up_plan_id ); ?>" />
                            <input type="hidden" id="is_arm_password_column" name="is_arm_password_column" value="<?php echo esc_attr($is_password_column); ?>"/>
                            <?php 
                                $arm_add_other_input_outside = "";
                                echo apply_filters('arm_add_other_input_for_import_outside', $arm_add_other_input_outside, $request); //phpcs:ignore
                            ?>
                            <textarea id="arm_import_users_data" name="users_data" style="display:none;"><?php echo json_encode($users_data); ?></textarea>
                        </div>
                <?php
                    }
                }
            }
            exit;
        }

        function arm_add_import_user() {
            global $wpdb, $ARMember, $arm_members_class, $arm_global_settings, $arm_subscription_plans, $arm_case_types, $arm_members_badges, $arm_member_forms, $arm_email_settings, $arm_capabilities_global;

            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_import_export'], '1',1); //phpcs:ignore --Reason:Verifying nonce
            if (!isset($_POST)) { //phpcs:ignore
                return;
            }

            $ARMember->arm_session_start();
            $arm_global_settings->arm_set_ini_for_importing_users();
            $message = '';
            $file_data_array = $user_ids = $u_errors = $errors = array();
            $ip_address = $ARMember->arm_get_ip_address();
            $user_default_fields = self::arm_get_user_import_default_fields();
            $send_notification = isset($_REQUEST['send_email']) ? sanitize_text_field( $_REQUEST['send_email'] ) : 'false';
            
            $password_type = isset($_REQUEST['password_type']) ? sanitize_text_field($_REQUEST['password_type']) : "hashed";
            $user_password_type = isset($_REQUEST['generate_password_type']) ? sanitize_text_field($_REQUEST['generate_password_type']) : false; //phpcs:ignore
            $new_password = isset($_REQUEST['fixed_password']) ? $_REQUEST['fixed_password'] : ''; //phpcs:ignore

            $postedFormData = json_decode(stripslashes_deep($_POST['filtered_form']), true);//phpcs:ignore

            $posted_user_data = htmlspecialchars($postedFormData['users_data'], ENT_NOQUOTES);

            $file_data_array = json_decode($posted_user_data, true);

            if (json_last_error() != JSON_ERROR_NONE) {
                $file_data_array = maybe_unserialize($posted_user_data);
            }

            $plan_id = isset($postedFormData['plan_id']) ? $postedFormData['plan_id'] : 0;
            $ids = isset($postedFormData['item-action']) ? $postedFormData['item-action'] : array();
            $mail_count = 0;
            $imp_count = 0;
            if( empty( $_REQUEST['arm_import_export_continue_flag'] ) )
            {
                $_SESSION['imported_users'] = 0;
            }
            
            if (empty($ids)) {
                $errors[] = esc_html__('Please select one or more records.', 'ARMember');
            } else {
                if (!is_array($ids)) {
                    $ids = explode(',', $ids);
                }
                if (is_array($ids)) {
                    if (!empty($file_data_array)) {
                        $users_data = array();
                        foreach ($file_data_array as $k1 => $val1) {
                            if (!in_array($k1, $ids)) {
                                continue;
                            }
                            foreach ($val1 as $k2 => $val2) {
                                if (in_array($k2, array_keys($user_default_fields['userdata']))) {
                                    if ($user_default_fields['userdata'][$k2] == 'role') {
                                        
                                    }
                                    if ($user_default_fields['userdata'][$k2] == 'user_registered') {
                                        if (empty($val2)) {
                                            $val2 = date("Y-m-d H:i:s");
                                        }
                                        $val2 = date("Y-m-d H:i:s", strtotime($val2));
                                    }
                                    unset($file_data_array[$k1][$k2]);
                                    if (!empty($val2)) {
                                        $users_data[$k1]['userdata'][$user_default_fields['userdata'][$k2]] = $val2; /* Set Matched Key Value */
                                    }
                                } elseif (in_array($k2, array_keys($user_default_fields['usermeta']))) {
                                    unset($file_data_array[$k1][$k2]); /* Remove Old Key From Array */
                                    if (in_array($user_default_fields['usermeta'][$k2], array('arm_user_plan_ids', 'status'))) {
                                        unset($users_data[$k1]['usermeta'][$k2]);
                                    } else {
                                        $users_data[$k1]['usermeta'][$user_default_fields['usermeta'][$k2]] = $val2; /* Set Matched Key Value */
                                    }
                                } else {
                                    $users_data[$k1]['usermeta'][$k2] = $val2;
                                }
                            }
                        }



                        if (!empty($users_data)) {
                            $allready_exists = array('username', 'email', 'website', 'joined', 'user_nicename', 'display_name', 'user_pass', 'biographical_info');
                            $allready_exists_meta = $arm_member_forms->arm_get_db_form_fields(true);
                            $select_user_meta = array();
                            foreach ($allready_exists_meta as $exist_meta) {
                                array_push($select_user_meta, $exist_meta['id']);
                                array_push($select_user_meta, $exist_meta['label']);
                                array_push($select_user_meta, $exist_meta['meta_key']);
                            }
                            $exists_user_meta = array_merge_recursive($allready_exists, $select_user_meta);
                            if (count($users_data) > 50) {

                                $chunked_user_data = array_chunk($users_data, 50, false);

                                $total_chunked_data = count($chunked_user_data);

                                $change_password_page_id = isset($arm_global_settings->global_settings['change_password_page_id']) ? $arm_global_settings->global_settings['change_password_page_id'] : 0;
                                $arm_change_password_page_url = $arm_global_settings->arm_get_permalink('', $change_password_page_id);
                                $arm_change_password_page_url = apply_filters('arm_modify_redirection_page_external', $arm_change_password_page_url,0,$change_password_page_id);
                                $temp_detail = $arm_email_settings->arm_get_email_template($arm_email_settings->templates->forgot_passowrd_user);

                                for ($ch_data = 0; $ch_data < $total_chunked_data; $ch_data++) {
                                    $chunked_data = null;
                                    $chunked_data = $chunked_user_data[$ch_data];
                                    foreach ($chunked_data as $rkey => $udata) {
                                        $user_main_data = $udata['userdata'];
                                        $user_meta_data = isset($udata['usermeta']) ? $udata['usermeta'] : array();
                                        /* Get User If `ID` is available */
                                        if (isset($user_main_data['ID'])) {
                                            unset($user_main_data['ID']);
                                        }
                                        /* Check User's `username` or `email` If user exist AND if `Update User` Set to true */
                                        if (isset($user_main_data['user_login'])) {
                                            $user = get_user_by('login', $user_main_data['user_login']);
                                        }
                                        if (!$user && isset($user_main_data['user_email'])) {
                                            $user = get_user_by('email', $user_main_data['user_email']);
                                        }
                                        /* Skip existing users */
                                        if ($user) {
                                            continue;
                                        }

                                        if (!empty($user_main_data['user_email'])) {
                                            $update = FALSE;
                                            if ($user) {
                                                $user_main_data['ID'] = $user->ID;
                                                $update = TRUE;
                                            }
                                            /* Set Password For new users */
                                            //$user_main_data['user_pass'] = wp_generate_password(8, false);   
                                            // $user_main_data['user_pass'] = 'adminconnect';
                                            $generate_from_csv = 0;
                                            if ($user_password_type == 'generate_dynamic') {
                                                $user_main_data['user_pass'] = wp_generate_password(8, false);
                                            } else if ($user_password_type == 'generate_fixed') {
                                                $user_main_data['user_pass'] = $new_password;
                                            } else if ($user_password_type == 'generate_from_csv') {
                                                $generate_from_csv = 1;
                                            }

                                            $plaintext_pass = $user_main_data['user_pass'];
                                            $user_role = (!empty($user_main_data['role'])) ? $user_main_data['role'] : '';
                                            unset($user_main_data['role']);

                                            if (isset($user_main_data['nickname'])) {
                                                $user_main_data['user_nicename'] = $user_main_data['nickname'];
                                            }
                                            if (isset($user_main_data['joined'])) {
                                                $user_main_data['user_registered'] = $user_main_data['joined'];
                                            }


                                            if ($generate_from_csv == 0) {
                                                if ($update) {
                                                    $user_id = wp_update_user($user_main_data);
                                                } else {
                                                    //                                        $user_main_data['user_registered'] = date("Y-m-d H:i:s");
                                                    $user_id = wp_insert_user($user_main_data);
                                                    $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'defaultbadge');
                                                }
                                            } else {
                                                if ($password_type == 'plain') {
                                                    if ($update) {
                                                        $user_id = wp_update_user($user_main_data);
                                                    } else {
                                                        // $user_main_data['user_registered'] = date("Y-m-d H:i:s");
                                                        $user_id = wp_insert_user($user_main_data);
                                                        $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'defaultbadge');
                                                    }
                                                } else {
                                                    global $wpdb;
                                                    if ($update) {
                                                        $user_id = wp_update_user($user_main_data);
                                                        $wpdb->query( $wpdb->prepare("UPDATE " . $wpdb->users . " set `user_pass`=%s where `ID`= %d",$user_main_data['user_pass'] , $user_id)); //phpcs:ignore --Reason $wpdb->users is a table name
                                                    } else {
                                                        $user_id = wp_insert_user($user_main_data);
                                                        $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'defaultbadge');
                                                        $wpdb->query( $wpdb->prepare("UPDATE " . $wpdb->users . " set `user_pass`=%s where `ID`=%d",$user_main_data['user_pass'] , $user_id)); //phpcs:ignore --Reason $wpdb->users is a table name
                                                    }
                                                }
                                            }



                                            /* Is there an error o_O? */
                                            if (is_wp_error($user_id)) {
                                                $u_errors[$rkey] = $user_id;
                                            } else {
                                                /* If no error, let's update the user meta too! */
                                                if (!empty($user_meta_data)) {
                                                    foreach ($user_meta_data as $metakey => $metavalue) {
                                                        if ($metakey != 'arm_subscription_start_date') 
                                                        {
                                                            if (!in_array($metakey, $exists_user_meta)) 
                                                            {
                                                                $fields = array('label' => $metakey);
                                                                $metakey = str_replace(' ', '_', $metakey);
                                                                $arm_member_forms->arm_db_add_preset_form_field($fields, $metakey);
                                                            }
                                                            else if(!empty($allready_exists_meta) && is_array($allready_exists_meta)) 
                                                            {
                                                                if(!empty($allready_exists_meta[$metakey]) && !empty($allready_exists_meta[$metakey]['type']) && $allready_exists_meta[$metakey]['type'] == 'checkbox')
                                                                {
                                                                    $metavalue = explode(',',$metavalue);
                                                                }
                                                            }
                                                            $metavalue = maybe_unserialize($metavalue);
                                                            update_user_meta($user_id, $metakey, $metavalue);
                                                        }
                                                    }
                                                }
                                                update_user_meta($user_id, 'arm_last_login_date', date('Y-m-d H:i:s'));
                                                /* add user to plan */

                                                if(empty($planObj))
                                                {
                                                    $planObj = new ARM_Plan($plan_id);
                                                }

                                                $posted_data = array(
                                                    'arm_user_plan' => $plan_id,
                                                    'payment_gateway' => 'manual',
                                                    'arm_selected_payment_mode' => 'manual_subscription',
                                                    'arm_primary_status' => 1,
                                                    'arm_secondary_status' => 0,
                                                    'arm_subscription_start_date' => isset($user_meta_data['arm_subscription_start_date']) ? $user_meta_data['arm_subscription_start_date'] : '',
                                                    'arm_user_import' => true,
                                                        // 'action' => 'add_member'
                                                );
                                                $admin_save_flag = 1;
                                                do_action('arm_member_update_meta', $user_id, $posted_data, $admin_save_flag);
                                                do_action('arm_action_outside_after_assign_import_user_plan', $user_id, $plan_id, $postedFormData);
                                                if (!$planObj->is_free()) {
                                                    $this->arm_manual_update_user_data($user_id, $plan_id, $posted_data);
                                                }


                                                /* Some plugins may need to do things after one user has been imported. Who know? */
                                                if ($send_notification == 'true') {
                                                    $message = '';
                                                    $user = new WP_User($user_id);
                                                    armMemberSignUpCompleteMail($user, $plaintext_pass);
                                                    if ($mail_count == 100) {
                                                        sleep(10);
                                                        $mail_count = 0;
                                                    }

                                                   
                                                    if (isset($user_main_data['user_email']) && $user_main_data['user_email'] != '') {

                                                        if (function_exists('get_password_reset_key')) {
                                                            $user_data = get_user_by('email', trim($user_main_data['user_email']));
                                                            $key = get_password_reset_key($user_data);

                                                        } else {
                                                            
                                                            do_action('retreive_password', $user_main_data['user_login']);  /* Misspelled and deprecated */
                                                            do_action('retrieve_password', $user_main_data['user_login']);

                                                            /* Generate something random for a key... */
                                                            $key = wp_generate_password(20, false);
                                                            do_action('retrieve_password_key', $user_main_data['user_login'], $key);
                                                            global $wp_hasher;
                                                            /* Now insert the new md5 key into the db */
                                                            if (empty($wp_hasher)) {
                                                                require_once ABSPATH . WPINC . '/class-phpass.php';
                                                                $wp_hasher = new PasswordHash(8, true);
                                                            }
                                                            $hashed = $wp_hasher->HashPassword($key);
                                                            $key_saved = $wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_main_data['user_login']));
                                                            
                                                        }
                                                        update_user_meta($user_id, 'arm_reset_password_key', $key);
                                                       
                                                        if ($change_password_page_id == 0) {
                                                            $rp_link = network_site_url("wp-login.php?action=armrp&key=" . rawurlencode($key) . "&login=" . rawurlencode($user_main_data['user_login']), 'login');
                                                        } else {

                                                           

                                                            $arm_change_password_page_url = $arm_global_settings->add_query_arg('action', 'armrp', $arm_change_password_page_url);
                                                            $arm_change_password_page_url = $arm_global_settings->add_query_arg('key', rawurlencode($key), $arm_change_password_page_url);
                                                            $arm_change_password_page_url = $arm_global_settings->add_query_arg('login', rawurlencode($user_main_data['user_login']), $arm_change_password_page_url);

                                                            $rp_link = $arm_change_password_page_url;
                                                            $rp_link = apply_filters('arm_modify_redirection_page_external', $rp_link,0,$change_password_page_id);
                                                        }


                                                       
                                                        if ($temp_detail->arm_template_status == '1') {
                                                            $temp_detail = apply_filters('arm_get_modify_mail_template_object_for_send_mail', $temp_detail,$user_id);

                                                            $title = $arm_global_settings->arm_filter_email_with_user_detail($temp_detail->arm_template_subject, $user_id, 0);

                                                            $message = $arm_global_settings->arm_filter_email_with_user_detail($temp_detail->arm_template_content, $user_id, 0, 0, $key);

                                                            $message = str_replace('{ARM_RESET_PASSWORD_LINK}', '<a href="' . $rp_link . '">' . $rp_link . '</a>', $message);
                                                            $message = str_replace('{VAR1}', '<a href="' . $rp_link . '">' . $rp_link . '</a>', $message);
                                                        } else {
                                                            $title = $blogname . ' ' . esc_html__('Password Reset', 'ARMember');
                                                            $message = esc_html__('Someone requested that the password be reset for the following account:', 'ARMember') . "\r\n\r\n";
                                                            $message .= network_home_url('/') . "\r\n\r\n";
                                                            $message .= esc_html__('Username', 'ARMember') . ": " . $user_login . "\r\n\r\n";
                                                            $message .= esc_html__('If this was a mistake, just ignore this email and nothing will happen.', 'ARMember') . "\r\n\r\n";
                                                            $message .= esc_html__('To reset your password, visit the following address:', 'ARMember') . " " . $rp_link . "\r\n\r\n";
                                                        }


                                                        $title = apply_filters('retrieve_password_title', $title, $user_data->ID);
                                                        $message = apply_filters('retrieve_password_message', $message, $key, $user_data->user_login, $user_data);
                                                        $send_mail = $arm_global_settings->arm_wp_mail('', $user_main_data['user_email'], $title, $message);

                                                       
                                                        // $user_send_mail = $arm_global_settings->arm_wp_mail('', $user_main_data['user_email'], $subject, $message);
                                                    }
                                                }
                                                do_action('arm_after_user_import', $user_id);
                                                $user_ids[] = $user_id;
                                                if (is_multisite()) {
                                                    add_user_to_blog($GLOBALS['blog_id'], $user_id, 'ARMember');
                                                }
                                                $_SESSION['imported_users']++;
                                                @session_write_close();
                                                $ARMember->arm_session_start(true);
                                                $mail_count++;
                                                $imp_count++;
                                            }
                                        }
                                    }
                                }
                            } else {
                                $change_password_page_id = isset($arm_global_settings->global_settings['change_password_page_id']) ? $arm_global_settings->global_settings['change_password_page_id'] : 0;
                                $arm_change_password_page_url = $arm_global_settings->arm_get_permalink('', $change_password_page_id);
                                $arm_change_password_page_url = apply_filters('arm_modify_redirection_page_external', $arm_change_password_page_url,0,$change_password_page_id);
                                $temp_detail = $arm_email_settings->arm_get_email_template($arm_email_settings->templates->forgot_passowrd_user);
                                foreach ($users_data as $rkey => $udata) {
                                    $user_main_data = $udata['userdata'];
                                    $user_meta_data = isset($udata['usermeta']) ? $udata['usermeta'] : array();
                                    /* Get User If `ID` is available */
                                    if (isset($user_main_data['ID'])) {
                                        unset($user_main_data['ID']);
                                    }
                                    /* Check User's `username` or `email` If user exist AND if `Update User` Set to true */
                                    if (isset($user_main_data['user_login'])) {
                                        $user = get_user_by('login', $user_main_data['user_login']);
                                    }
                                    if (!$user && isset($user_main_data['user_email'])) {
                                        $user = get_user_by('email', $user_main_data['user_email']);
                                    }
                                    /* Skip existing users */
                                    if ($user) {
                                        continue;
                                    }

                                    if (!empty($user_main_data['user_email'])) {
                                        $update = FALSE;
                                        if ($user) {
                                            $user_main_data['ID'] = $user->ID;
                                            $update = TRUE;
                                        }
                                        
                                        $generate_from_csv = 0;
                                        if ($user_password_type == 'generate_dynamic') {
                                            $user_main_data['user_pass'] = wp_generate_password(8, false);
                                        } else if ($user_password_type == 'generate_fixed') {
                                            $user_main_data['user_pass'] = $new_password;
                                        } else if ($user_password_type == 'generate_from_csv') {
                                            $generate_from_csv = 1;
                                        }

                                        $plaintext_pass = $user_main_data['user_pass'];
                                        $user_role = (!empty($user_main_data['role'])) ? $user_main_data['role'] : '';
                                        unset($user_main_data['role']);

                                        if (isset($user_main_data['nickname'])) {
                                            $user_main_data['user_nicename'] = $user_main_data['nickname'];
                                        }
                                        if (isset($user_main_data['joined'])) {
                                            $user_main_data['user_registered'] = $user_main_data['joined'];
                                        }


                                        if ($generate_from_csv == 0) {
                                            if ($update) {
                                                $user_id = wp_update_user($user_main_data);
                                            } else {
                                                //                                        $user_main_data['user_registered'] = date("Y-m-d H:i:s");
                                                $user_id = wp_insert_user($user_main_data);
                                                $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'defaultbadge');
                                            }
                                        } else {
                                            if ($password_type == 'plain') {
                                                if ($update) {
                                                    $user_id = wp_update_user($user_main_data);
                                                } else {
                                                    // $user_main_data['user_registered'] = date("Y-m-d H:i:s");
                                                    $user_id = wp_insert_user($user_main_data);
                                                    $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'defaultbadge');
                                                }
                                            } else {
                                                global $wpdb;
                                                if ($update) {
                                                    $user_id = wp_update_user($user_main_data);
                                                    $wpdb->query( $wpdb->prepare("UPDATE " . $wpdb->users . " set `user_pass`='" . $user_main_data['user_pass'] . "' where `ID`=%d" , $user_id)); //phpcs:ignore --Reason $wpdb->users is  a table name
                                                } else {
                                                    $user_id = wp_insert_user($user_main_data);
                                                    $arm_members_badges->arm_add_user_achieve_by_type($user_id, 0, 'defaultbadge');
                                                    $wpdb->query( $wpdb->prepare("UPDATE " . $wpdb->users . " set `user_pass`='" . $user_main_data['user_pass'] . "' where `ID`=%d" , $user_id) ); //phpcs:ignore --Reason $wpdb->users is a table name
                                                }
                                            }
                                        }



                                        /* Is there an error o_O? */
                                        if (is_wp_error($user_id)) {
                                            $u_errors[$rkey] = $user_id;
                                        } else {
                                            /* If no error, let's update the user meta too! */
                                            if (!empty($user_meta_data)) {
                                                foreach ($user_meta_data as $metakey => $metavalue) {
                                                    if ($metakey != 'arm_subscription_start_date') {
                                                        if (!in_array($metakey, $exists_user_meta)) {
                                                            $fields = array('label' => $metakey);
                                                            $metakey = str_replace(' ', '_', $metakey);
                                                            $arm_member_forms->arm_db_add_preset_form_field($fields, $metakey);
                                                        }
                                                        else if(!empty($allready_exists_meta) && is_array($allready_exists_meta)) 
                                                        {
                                                            if(!empty($allready_exists_meta[$metakey]) && !empty($allready_exists_meta[$metakey]['type']) && $allready_exists_meta[$metakey]['type'] == 'checkbox')
                                                            {
                                                                $metavalue = explode(',',$metavalue);
                                                            }
                                                        }
                                                        $metavalue = maybe_unserialize($metavalue);
                                                        update_user_meta($user_id, $metakey, $metavalue);
                                                    }
                                                }
                                            }
                                            update_user_meta($user_id, 'arm_last_login_date', date('Y-m-d H:i:s'));
                                            /* add user to plan */
                                            if(empty($planObj))
                                            {
                                                $planObj = new ARM_Plan($plan_id);
                                            }

                                            $posted_data = array(
                                                'arm_user_plan' => $plan_id,
                                                'payment_gateway' => 'manual',
                                                'arm_selected_payment_mode' => 'manual_subscription',
                                                'arm_primary_status' => 1,
                                                'arm_secondary_status' => 0,
                                                'arm_subscription_start_date' => isset($user_meta_data['arm_subscription_start_date']) ? $user_meta_data['arm_subscription_start_date'] : '',
                                                'arm_user_import' => true,
                                                    // 'action' => 'add_member'
                                            );
                                            
                                            
                                            $admin_save_flag = 1;
                                            do_action('arm_member_update_meta', $user_id, $posted_data, $admin_save_flag);
                                            do_action('arm_action_outside_after_assign_import_user_plan', $user_id, $plan_id, $postedFormData);
                                            if (!$planObj->is_free()) {
                                                $this->arm_manual_update_user_data($user_id, $plan_id, $posted_data);
                                            }
                                            /* Some plugins may need to do things after one user has been imported. Who know? */
                                            if ($send_notification == 'true') {
                                                $message = '';
                                                $user = new WP_User($user_id);
                                                armMemberSignUpCompleteMail($user, $plaintext_pass);
                                                if ($mail_count == 100) {
                                                    sleep(10);
                                                    $mail_count = 0;
                                                }
                                                
                                                if (isset($user_main_data['user_email'])) {

                                                   

                                                    if (isset($user_main_data['user_email']) && $user_main_data['user_email'] != '') {

                                                        if (function_exists('get_password_reset_key')) {
                                                            $user_data = get_user_by('email', trim($user_main_data['user_email']));
                                                            $key = get_password_reset_key($user_data);

                                                        } else {
                                                            
                                                            do_action('retreive_password', $user_main_data['user_login']);  /* Misspelled and deprecated */
                                                            do_action('retrieve_password', $user_main_data['user_login']);

                                                            /* Generate something random for a key... */
                                                            $key = wp_generate_password(20, false);
                                                            do_action('retrieve_password_key', $user_main_data['user_login'], $key);
                                                            global $wp_hasher;
                                                            /* Now insert the new md5 key into the db */
                                                            if (empty($wp_hasher)) {
                                                                require_once ABSPATH . WPINC . '/class-phpass.php';
                                                                $wp_hasher = new PasswordHash(8, true);
                                                            }
                                                            $hashed = $wp_hasher->HashPassword($key);
                                                            $key_saved = $wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_main_data['user_login']));
                                                            
                                                        }
                                                        update_user_meta($user_id, 'arm_reset_password_key', $key);
                                                        
                                                        if ($change_password_page_id == 0) {
                                                            $rp_link = network_site_url("wp-login.php?action=armrp&key=" . rawurlencode($key) . "&login=" . rawurlencode($user_main_data['user_login']), 'login');
                                                        } else {
                                                            $arm_change_password_page_url = $arm_global_settings->add_query_arg('action', 'armrp', $arm_change_password_page_url);
                                                            $arm_change_password_page_url = $arm_global_settings->add_query_arg('key', rawurlencode($key), $arm_change_password_page_url);
                                                            $arm_change_password_page_url = $arm_global_settings->add_query_arg('login', rawurlencode($user_main_data['user_login']), $arm_change_password_page_url);

                                                            $rp_link = $arm_change_password_page_url;
                                                            $rp_link = apply_filters('arm_modify_redirection_page_external', $rp_link,0,$change_password_page_id);
                                                        }

                                                        if ($temp_detail->arm_template_status == '1') {
                                                            $temp_detail = apply_filters('arm_get_modify_mail_template_object_for_send_mail', $temp_detail,$user_id);
                                                            
                                                            $title = $arm_global_settings->arm_filter_email_with_user_detail($temp_detail->arm_template_subject, $user_id, 0);

                                                            $message = $arm_global_settings->arm_filter_email_with_user_detail($temp_detail->arm_template_content, $user_id, 0, 0, $key);

                                                            $message = str_replace('{ARM_RESET_PASSWORD_LINK}', '<a href="' . $rp_link . '">' . $rp_link . '</a>', $message);
                                                            $message = str_replace('{VAR1}', '<a href="' . $rp_link . '">' . $rp_link . '</a>', $message);
                                                        } else {
                                                            $title = $blogname . ' ' . esc_html__('Password Reset', 'ARMember');
                                                            $message = esc_html__('Someone requested that the password be reset for the following account:', 'ARMember') . "\r\n\r\n";
                                                            $message .= network_home_url('/') . "\r\n\r\n";
                                                            $message .= esc_html__('Username', 'ARMember') . ": " . $user_login . "\r\n\r\n";
                                                            $message .= esc_html__('If this was a mistake, just ignore this email and nothing will happen.', 'ARMember') . "\r\n\r\n";
                                                            $message .= esc_html__('To reset your password, visit the following address:', 'ARMember') . " " . $rp_link . "\r\n\r\n";
                                                        }

                                                        $title = apply_filters('retrieve_password_title', $title, $user_data->ID);
                                                        $message = apply_filters('retrieve_password_message', $message, $key, $user_data->user_login, $user_data);
                                                        $send_mail = $arm_global_settings->arm_wp_mail('', $user_main_data['user_email'], $title, $message);
                                                    }
                                                }
                                            }
                                            do_action('arm_after_user_import', $user_id);
                                            $user_ids[] = $user_id;
                                            if (is_multisite()) {
                                                add_user_to_blog($GLOBALS['blog_id'], $user_id, 'ARMember');
                                            }
                                            $_SESSION['imported_users'] ++;
                                            $wpdb->flush();
                                            @session_write_close();
                                            $ARMember->arm_session_start(true);
                                            $mail_count++;
                                            $imp_count++;
                                        }
                                    }
                                }

                            }

                            if (!empty($planObj)) {
                                if(!$planObj->is_free() && $planObj->type!='paid_infinite')
                                {
                                    do_action('arm_handle_expire_subscription');
                                }
                            }

                        } else {
                            $errors[] = esc_html__('No user was imported, please check the file.', 'ARMember');
                        }
                    }
                }
            }
            /* One more thing to do after all imports? */
            do_action('arm_after_all_users_import', $user_ids, $errors);
            if (!empty($user_ids)) {
                $message = esc_html__('User(s) has been imported successfully', 'ARMember');
                $ARMember->arm_set_message('success', $message);
                if (!empty($postedFormData['file_url'])) {

                    $arm_up_file_name = basename($postedFormData['file_url']);
                    $file_path = MEMBERSHIP_UPLOAD_DIR . '/' . $arm_up_file_name;

                    $file_name_arm = substr($arm_up_file_name, 0,3);

                    $checkext = explode(".", $arm_up_file_name);
                    $ext = strtolower( $checkext[count($checkext) - 1] );

                    if(!empty($ext) && ($ext=='csv' || $ext=='xml') && file_exists($file_path) && $file_name_arm=='arm' ) {
                        unlink($file_path);
                    }
                }
            }
            if (!empty($u_errors)) {
                $errors[] = esc_html__('Error during user import.', 'ARMember');
            }
            if (empty($user_ids) && empty($errors) && empty($u_errors)) {
                $errors[] = esc_html__('No user was imported.', 'ARMember');
            }
            if (MEMBERSHIP_DEBUG_LOG == true) {
                $arm_case_types['shortcode']['protected'] = true;
                $arm_case_types['shortcode']['type'] = 'after_import_users';
                $arm_case_types['shortcode']['message'] = esc_html__('Log after users are imported using xml or csv file.', 'ARMember');
                $ARMember->arm_debug_response_log('arm_add_import_user', $arm_case_types, $csv_reader, $wpdb->last_query, false);
            }
            $return_array = $arm_global_settings->handle_return_messages(@$errors, @$message);
            echo json_encode($return_array);
            exit;
        }
        /*
        function arm_user_import_handle($request) {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_case_types, $arm_members_badges, $arm_capabilities_global;

            $ARMember->arm_check_user_cap( $arm_capabilities_global['arm_import_export'], '1' );

            $file_data_array = $user_ids = $u_errors = $errors = array();
            $action = $request['arm_action'];
            //			$update_users = ($request['update_users']) ? TRUE : FALSE;
            $up_file = $_FILES['import_user']; //phpcs:ignore
            if (isset($up_file) && $up_file['error'] == UPLOAD_ERR_OK && is_uploaded_file($up_file['tmp_name'])) {
                $up_file_name = $up_file['name'];
                $up_file_ext = pathinfo($up_file_name, PATHINFO_EXTENSION);
                $tmp_name = $up_file['tmp_name'];
                if (in_array($up_file_ext, array('csv', 'xls', 'xlsx', 'xml'))) {
                    $user_default_fields = self::arm_get_user_import_default_fields();
                    if ($up_file_ext == 'xml') {

                        if(file_exists(ABSPATH . 'wp-admin/includes/file.php')){
                            require_once(ABSPATH . 'wp-admin/includes/file.php');
                        }
        
                        WP_Filesystem();
                        global $wp_filesystem;
                        $fileContent = $wp_filesystem->get_contents($tmp_name);

                        $xmlData = armXML_to_Array($fileContent);
                        if (isset($xmlData['members']['member']) && !empty($xmlData['members']['member'])) {
                            $file_data_array = $xmlData['members']['member'];
                        } else {
                            if (MEMBERSHIP_DEBUG_LOG == true) {
                                $arm_case_types['shortcode']['protected'] = true;
                                $arm_case_types['shortcode']['type'] = 'import_user_xml';
                                $arm_case_types['shortcode']['message'] = esc_html__('Error during file upload', 'ARMember');
                                $ARMember->arm_debug_response_log('arm_user_import_handle', $arm_case_types, $xmlData, $wpdb->last_query, false);
                            }
                            $errors[] = esc_html__('Error during file upload.', 'ARMember');
                        }
                    } else {
                        //Read CSV, XLS Files
                        if (file_exists(MEMBERSHIP_LIBRARY_DIR . '/class-readcsv.php')) {
                            require_once(MEMBERSHIP_LIBRARY_DIR . '/class-readcsv.php');
                        }
                        $csv_reader = new ReadCSV($tmp_name);
                        if ($csv_reader->is_file == TRUE) {
                            $file_data_array = $csv_reader->get_data();
                        } else {
                            if (MEMBERSHIP_DEBUG_LOG == true) {
                                $arm_case_types['shortcode']['protected'] = true;
                                $arm_case_types['shortcode']['type'] = 'import_user_csv';
                                $arm_case_types['shortcode']['message'] = esc_html__('Error during file upload', 'ARMember');
                                $ARMember->arm_debug_response_log('arm_user_import_handle', $arm_case_types, $csv_reader, $wpdb->last_query, false);
                            }
                            $errors[] = esc_html__('Error during file upload.', 'ARMember');
                        }
                    }
                    if (!empty($file_data_array)) {
                        $users_data = array();
                        foreach ($file_data_array as $k1 => $val1) {
                            foreach ($val1 as $k2 => $val2) {
                                if (in_array($k2, array_keys($user_default_fields['userdata']))) {
                                    if ($user_default_fields['userdata'][$k2] == 'role') {
                                        $val2 = ''; // Remove Role to add user into site default role 
                                    }
                                    if ($user_default_fields['userdata'][$k2] == 'user_registered') {
                                        if (empty($val2)) {
                                            $val2 = date("Y-m-d H:i:s");
                                        }
                                        $val2 = date("Y-m-d H:i:s", strtotime($val2));
                                    }
                                    unset($file_data_array[$k1][$k2]); // Remove Old Key From Array
                                    if (!empty($val2)) {
                                        $users_data[$k1]['userdata'][$user_default_fields['userdata'][$k2]] = $val2; // Set Matched Key Value
                                    }
                                } elseif (in_array($k2, array_keys($user_default_fields['usermeta']))) {
                                    unset($file_data_array[$k1][$k2]); // Remove Old Key From Array
                                    if (in_array($user_default_fields['usermeta'][$k2], array('arm_user_plan', 'status'))) {
                                        unset($users_data[$k1]['usermeta'][$k2]);
                                    } else {
                                        $users_data[$k1]['usermeta'][$user_default_fields['usermeta'][$k2]] = $val2; // Set Matched Key Value
                                    }
                                } else {
                                    $users_data[$k1]['usermeta'][$k2] = $val2;
                                }
                            }
                        }

                        $users_data = apply_filters('arm_filter_users_before_import', $users_data);
                        // Insert Or Update User Details.
                        if (!empty($users_data)) {
                            foreach ($users_data as $rkey => $udata) {
                                $user_main_data = $udata['userdata'];
                                $user_meta_data = isset($udata['usermeta']) ? $udata['usermeta'] : array();
                                // Get User If `ID` is available 
                                if (isset($user_main_data['ID'])) {
                                    // $user = get_user_by('ID', $user_main_data['ID']); 
                                    unset($user_main_data['ID']);
                                }
                                // Check User's `username` or `email` If user exist AND if `Update User` Set to true 
                                if (isset($user_main_data['user_login'])) {
                                    $user = get_user_by('login', $user_main_data['user_login']);
                                }
                                if (!$user && isset($user_main_data['user_email'])) {
                                    $user = get_user_by('email', $user_main_data['user_email']);
                                }
                                // Skip existing users 
                                if ($user) {
                                    continue;
                                }
                                $update = FALSE;
                                if ($user) {
                                    $user_main_data['ID'] = $user->ID;
                                    $update = TRUE;
                                }
                                // Set Password For new users 
                                if (!$update && empty($user_main_data['user_pass'])) {
                                    $user_main_data['user_pass'] = wp_generate_password(8, false);
                                }
                                $user_role = (!empty($user_main_data['role'])) ? $user_main_data['role'] : '';
                                unset($user_main_data['role']);

                                if ($update) {
                                    $user_id = wp_update_user($user_main_data);
                                } else {
                                    $user_id = wp_insert_user($user_main_data);
                                }
                                // Is there an error o_O? 
                                if (is_wp_error($user_id)) {
                                    $u_errors[$rkey] = $user_id;
                                } else {
                                    if ($update && user_can($user_id, 'administrator')) {
                                        
                                    } else {
                                        $added_user = new WP_User($user_id);
                                        $blog_role = get_option('default_role');
                                        if (!empty($user_role)) {
                                            $role_obj = get_role($user_role);
                                            if (!empty($role_obj)) {
                                                $added_user->set_role($user_role);
                                                $blog_role = $user_role;
                                            }
                                        }
                                        // User to current blog. 
                                        if (function_exists('add_user_to_blog')) {
                                            $blog_id = get_current_blog_id();
                                            add_user_to_blog($blog_id, $user_id, $blog_role);
                                        }
                                    }
                                    // If no error, let's update the user meta too! 
                                    if (!empty($user_meta_data)) {
                                        foreach ($user_meta_data as $metakey => $metavalue) {
                                            $metavalue = maybe_unserialize($metavalue);
                                            update_user_meta($user_id, $metakey, $metavalue);
                                        }
                                    }
                                    // If we created a new user, maybe set password nag and send new user notification? 
                                    if (!$update) {
                                        if ($password_nag)
                                            update_user_option($user_id, 'default_password_nag', true, true);
                                        if ($new_user_notification)
                                            arm_new_user_notification($user_id, $user_main_data['user_pass']);
                                    }
                                    // Some plugins may need to do things after one user has been imported. Who know? 
                                    do_action('arm_after_user_import', $user_id);
                                    $user_ids[] = $user_id;
                                }
                            }
                        } else {
                            $errors[] = esc_html__('No user was imported, please check the file.', 'ARMember');
                        }
                    } else {
                        $errors[] = esc_html__('Cannot extract data from uploaded file or no file was uploaded.', 'ARMember');
                    }
                } else {
                    $errors[] = esc_html__('Invalid file uploaded.', 'ARMember');
                }
            } else {
                $errors[] = esc_html__('Error during file upload.', 'ARMember');
            }
            // One more thing to do after all imports?
            do_action('arm_after_all_users_import', $user_ids, $errors);
            //Print Import Process Messages.
            if (!empty($user_ids)) {
                $msg[] = esc_html__('User(s) has been imported successfully', 'ARMember');
                self::arm_user_import_export_messages('', $msg);
            }
            if (!empty($u_errors)) {
                $errors[] = esc_html__('Error during user import.', 'ARMember');
            }
            if (empty($user_ids) && empty($errors) && empty($u_errors)) {
                $errors[] = esc_html__('No user was imported.', 'ARMember');
            }
            if (!empty($errors)) {
                self::arm_user_import_export_messages($errors);
            }
            //Unset Uploaded File.
            unset($_FILES);
        }
        */
        function arm_user_export_handle($request) {
            global $wp, $wpdb, $ARMember, $armPrimaryStatus, $arm_global_settings, $arm_subscription_plans, $arm_case_types,$is_multiple_membership_feature, $arm_pay_per_post_feature, $arm_capabilities_global;

            $ARMember->arm_check_user_cap( $arm_capabilities_global['arm_import_export'], '1',1); //phpcs:ignore --Reason:Verifying nonce

            $action = $request['arm_action'];
            if (isset($action) && in_array($action, array('user_export_csv', 'user_export_xls', 'user_export_xml'))) {
                $join = '';
                $where = "WHERE 1=1 ";
                $subscription_plan = (isset($request['subscription_plan'])) ? $request['subscription_plan'] : '';
                $primary_status = $request['primary_status'];
                $start_date = $request['start_date'];
                $end_date = $request['end_date'];
                if (!empty($start_date) && strtotime($start_date) > current_time('timestamp')) {
                    $err = esc_html__('There is no any Member(s) found', 'ARMember');
                    self::arm_user_import_export_messages($err);
                } else {
                    $user_table = $wpdb->users;
                    $usermeta_table = $wpdb->usermeta;
                    $capability_column = $wpdb->get_blog_prefix($GLOBALS['blog_id']) . 'capabilities';

                    $super_admin_ids = array();
                    if (is_multisite()) {
                        $super_admin = get_super_admins();
                        if (!empty($super_admin)) {
                            foreach ($super_admin as $skey => $sadmin) {
                                if ($sadmin != '') {
                                    $user_obj = get_user_by('login', $sadmin);
                                    if ($user_obj->ID != '') {
                                        $super_admin_ids[] = $user_obj->ID;
                                    }
                                }
                            }
                        }
                    }

                    $user_where = " WHERE 1=1";
                    $admin_where = " WHERE 1=1 ";
                    if (!empty($super_admin_ids)) {
                        $super_admin_placeholders = ' AND u.ID IN (';
                        $super_admin_placeholders .= rtrim( str_repeat( '%s,', count( $super_admin_ids ) ), ',' );
                        $super_admin_placeholders .= ')';
                        array_unshift( $super_admin_ids, $super_admin_placeholders );

                        $admin_where .= call_user_func_array(array( $wpdb, 'prepare' ), $super_admin_ids );
                        //$admin_where .= " AND u.ID IN (" . implode(',', $super_admin_ids) . ")";
                    }

                    $operator = " AND ";
                    if (!empty($super_admin_ids)) {
                        $operator = " OR ";
                    }
                    $admin_where .= $operator;
                    $admin_where .= $wpdb->prepare(" um.meta_key = %s AND um.meta_value LIKE %s ",$capability_column,'%administrator%');
                    $admin_user_query = " SELECT u.ID FROM `{$user_table}` u LEFT JOIN `{$usermeta_table}` um ON u.ID = um.user_id {$admin_where} ";

                    $admin_users = $wpdb->get_results($admin_user_query); //phpcs:ignore --Reason $admin_user_query is a query name
                    $admin_user_ids = array();

                    if (!empty($admin_users)) {
                        foreach ($admin_users as $key => $admin_user) {
                            array_push($admin_user_ids, $admin_user->ID);
                        }
                    }

                    if (!empty($admin_user_ids)){
                        //$where .= " AND U.ID NOT IN (" . implode(',', $admin_user_ids) . ") ";
                        $admin_placeholders = 'AND U.ID NOT IN (';
                        $admin_placeholders .= rtrim( str_repeat( '%s,', count( $admin_user_ids ) ), ',' );
                        $admin_placeholders .= ')';
                        // $admin_users       = implode( ',', $admin_users );
                        
                        array_unshift( $admin_user_ids, $admin_placeholders );
        
                        $where .= call_user_func_array(array( $wpdb, 'prepare' ), $admin_user_ids );
                    }


                    if (!empty($start_date)) {
                        $start_datetime = date('Y-m-d 00:00:00', strtotime($start_date));
                        if (!empty($end_date)) {
                            $end_datetime = date('Y-m-d 23:59:59', strtotime($end_date));
                            if (strtotime($start_date) > strtotime($end_datetime)) {
                                $end_datetime = date('Y-m-d 00:00:00', strtotime($start_date));
                                $start_datetime = date('Y-m-d 23:59:59', strtotime($end_date));
                            }
                            $where .= $wpdb->prepare(" AND (`user_registered` BETWEEN %s AND %s) ",$start_datetime,$end_datetime);
                        } else {
                            $where .= $wpdb->prepare(" AND (`user_registered` > %s) ",$start_datetime);
                        }
                    } else {
                        if (!empty($end_date)) {
                            $end_datetime = date('Y-m-d 23:59:59', strtotime($end_date));
                            $where .= $wpdb->prepare(" AND (`user_registered` < %s) ",$end_datetime);
                        }
                    }
                    if (!empty($primary_status)) {
                        $where .= $wpdb->prepare(" AND (U.ID IN (SELECT AM.arm_user_id FROM `" . $ARMember->tbl_arm_members . "` AS AM WHERE AM.arm_primary_status=%s))",$primary_status); //phpcs:ignore --Reason $ARMember->tbl_arm_members is a table name
                    }
                    $user_sql = "SELECT U.ID FROM `" . $wpdb->users . "` U $join $where ORDER BY U.ID ASC";
                    $users = $wpdb->get_results($user_sql); //phpcs:ignore --Reason  $user_sql is a predefined query


                    if (!empty($subscription_plan) && is_array($subscription_plan)) {
                        if (!empty($users)) {
                            foreach ($users as $key => $u) {
                                $user_id = $u->ID;
                                $planIds = get_user_meta($user_id, 'arm_user_plan_ids', true);

                                if (!empty($planIds) && is_array($planIds)) 
                                {
                                    if($arm_pay_per_post_feature->isPayPerPostFeature)
                                    {
                                        $postIDs = get_user_meta($user_id, 'arm_user_post_ids', true);
                                        foreach($planIds as $armPlanKey => $armPlanVal)
                                        {
                                            if(!empty($postIDs[$armPlanVal]))
                                            {
                                                unset($planIds[$armPlanKey]);
                                            }
                                        }
                                    }

                                    $plan_intersect_array = array_intersect($planIds, $subscription_plan);
                                    if (empty($plan_intersect_array)) {
                                        unset($users[$key]);
                                    }
                                } else {
                                    unset($users[$key]);
                                }
                            }
                        }
                    }



                    if (!empty($users)) {
                        $users_data = array();
                        foreach ($users as $key => $u) {
                            $user_id = $u->ID;
                            if (is_user_member_of_blog($user_id)) {
                                $user_info = get_userdata($user_id);
                                $roles = '';
                                $arm_user_plan = array();
                                $arm_subscription_start_date = "";
                                $u_roles = array();
                                $plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                if (!empty($user_info->roles) && is_array($user_info->roles)) {
                                    //$u_roles = array_shift($user_info->roles);
                                    $u_roles = implode(', ', $user_info->roles);
                                    $roles = $u_roles;
                                }


                                if($arm_pay_per_post_feature->isPayPerPostFeature)
                                {
                                    $postIDs = get_user_meta($user_id, 'arm_user_post_ids', true);
                                    foreach($plan_ids as $armPlanKey => $armPlanVal)
                                    {
                                        if(!empty($postIDs[$armPlanVal]))
                                        {
                                            unset($plan_ids[$armPlanKey]);
                                        }
                                    }
                                }
                                
                                if (!empty($plan_ids) && is_array($plan_ids)) {
                                    foreach ($plan_ids as $plan_id) {
                                        if (!empty($plan_id)) {
                                            $arm_user_plan[] = $arm_subscription_plans->arm_get_plan_name_by_id($plan_id);
                                            if(!$is_multiple_membership_feature->isMultipleMembershipFeature)
                                            {
                                                $arm_current_plan_detail = get_user_meta($user_id, 'arm_user_plan_'.$plan_id, true);
                                                if(!empty($arm_current_plan_detail['arm_start_plan']))
                                                {
                                                    $arm_subscription_start_date = date('Y-m-d',$arm_current_plan_detail['arm_start_plan']);
                                                }
                                            }
                                        }
                                    }
                                }

                                $status = arm_get_member_status($user_id);
                                $statusText = $armPrimaryStatus[$status];
                                $users_data[$user_id] = array(
                                    'id' => $user_id,
                                    'username' => $user_info->user_login,
                                    'email' => $user_info->user_email,
                                    'status' => $statusText,
                                    'role' => $roles,
                                    'subscription_plan' => implode(",", $arm_user_plan),
                                    'joined' => $user_info->user_registered,
                                );
                                if(!$is_multiple_membership_feature->isMultipleMembershipFeature)
                                {
                                    $users_data[$user_id]['arm_subscription_start_date'] = $arm_subscription_start_date;
                                }
                                if (isset($request['arm_user_metas_to_export']) && $request['arm_user_metas_to_export'] != '') {
                                    $user_meta = explode(',', $request['arm_user_metas_to_export']);

                                    if (in_array('first_name', $user_meta)) {
                                        $users_data[$user_id]['first_name'] = $user_info->first_name;
                                    }
                                    if (in_array('last_name', $user_meta)) {
                                        $users_data[$user_id]['last_name'] = $user_info->last_name;
                                    }
                                    if (in_array('nickname', $user_meta)) {
                                        $users_data[$user_id]['nickname'] = get_user_meta($user_id, 'nickname', true);
                                    }
                                    if (in_array('display_name', $user_meta)) {
                                        $users_data[$user_id]['display_name'] = $user_info->display_name;
                                    }
                                    if (in_array('description', $user_meta)) {
                                        $users_data[$user_id]['biographical_info'] = get_user_meta($user_id, 'description', true);
                                    }
                                    if (in_array('user_url', $user_meta)) {
                                        $users_data[$user_id]['website'] = $user_info->user_url;
                                    }
                                    if (in_array('user_pass', $user_meta)) {
                                        $users_data[$user_id]['user_pass'] = $user_info->user_pass;
                                    }

                                    $exclude_meta = array('user_login', 'user_email', 'user_url', 'description');
                                    foreach ($user_meta as $key => $meta) {
                                        if (!array_key_exists($meta, $users_data[$user_id]) && !in_array($meta, $exclude_meta)) {
                                            $meta_value = get_user_meta($user_id, $meta, true);
                                            if (is_array($meta_value)) {
                                                $metaValues = '';
                                                foreach ($meta_value as $_meta_value) {
                                                    if ($_meta_value != '') {
                                                        $metaValues .= $_meta_value . ',';
                                                    }
                                                }
                                                $meta_value = rtrim($metaValues, ',');
                                            }
                                            $users_data[$user_id][$meta] = $meta_value;
                                        }
                                    }
                                }
                            }
                        }
                        $users_data = apply_filters('arm_filter_users_before_export', $users_data, $request);

                        switch ($action) {
                            case 'user_export_csv':
                                self::arm_export_to_csv($users_data);
                                break;
                            case 'user_export_xls':
                                self::arm_export_to_xls($users_data);
                                break;
                            case 'user_export_xml':
                                self::arm_export_to_xml($users_data);
                                break;
                            default:
                                break;
                        }
                    } else {
                        if (MEMBERSHIP_DEBUG_LOG == true) {
                            $arm_case_types['shortcode']['protected'] = true;
                            $arm_case_types['shortcode']['type'] = 'export_user';
                            $arm_case_types['shortcode']['message'] = esc_html__('No any Member(s) fount', 'ARMember');
                            $ARMember->arm_debug_response_log('arm_user_export_handle', $arm_case_types, $csv_reader, $wpdb->last_query, false);
                        }
                        $err = esc_html__('There is no any Member(s) found', 'ARMember');
                        self::arm_user_import_export_messages($err);
                    }
                }
            }
        }

        function arm_download_sample_csv() {
            global $wp, $wpdb, $ARMember, $arm_global_settings,$ARMember,$arm_capabilities_global;
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_import_export'], '1',1); //phpcs:ignore --Reason:Verifying nonce
            $sample_data[1] = array(
                "id" => 1,
                "username" => "reputeinfosystems",
                "email" => "reputeinfosystems@example.com",
                "first_name" => "Repute",
                "last_name" => "InfoSystems",
                "nickname" => "reputeinfo",
                "display_name" => "Repute InfoSystems",
                "joined" => "2024-08-20 00:00:00",
                "biographical_info" => " ",
                "website" => " ",
            );
            self::arm_export_to_csv($sample_data, 'ARMember-sample-export-members.csv');
            exit;
        }

        function arm_export_to_csv($array, $output_file_name = '', $delimiter = ',') {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            if (count($array) == 0) {
                return null;
            }
            if (empty($output_file_name)) {
                $output_file_name = "ARMember-export-members.csv";
            }
            ob_clean();
            ob_start();
            //Set Headers
            $this->download_send_headers($output_file_name);
            //Open File For Write Data
            $df = fopen("php://output", 'w');
            fputcsv($df, array_keys(reset($array)));
            foreach ($array as $row) {
                fputcsv($df, $row);
            }
            fclose($df);
            exit;
        }

        function arm_export_to_xls($array, $output_file_name = '') {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            if (count($array) == 0) {
                return null;
            }
            if (empty($output_file_name)) {
                $output_file_name = "ARMember-export-members.xls";
            }
            ob_clean();
            ob_start();
            //Set Headers
            $this->download_send_headers($output_file_name);
            header("Content-type: application/vnd.ms-excel;");
            $flag = false;
            foreach ($array as $row) {
                if (!$flag) {
                    // display field/column names as first row
                    echo implode("\t", array_keys($row)) . "\r\n"; //phpcs:ignore
                    $flag = true;
                }
                echo implode("\t", array_values($row)) . "\r\n"; //phpcs:ignore
            }
            exit;
        }

        function arm_export_to_xml($array, $output_file_name = '') {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            if (count($array) == 0) {
                return null;
            }
            if (empty($output_file_name)) {
                $output_file_name = "ARMember-export-members.xml";
            }
            ob_clean();
            ob_start();
            //Set Headers
            $this->download_send_headers($output_file_name);
            header('Content-type: text/xml');
            $xmlContent = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
            $xmlContent .= "<members>\n";
            foreach ($array as $row) {
                if (is_array($row)) {
                    $xmlContent .= "<member>\n";
                    foreach ($row as $key => $value) {
                        $xmlContent .= "<{$key}>";
                        $xmlContent .= "{$value}";
                        $xmlContent .= "</{$key}>\n";
                    }
                    $xmlContent .= "</member>\n";
                }
            }
            $xmlContent .= "</members>";
            echo $xmlContent; //phpcs:ignore
            exit;
        }

        function download_send_headers($filename) {
            // disable caching
            $now = gmdate("D, d M Y H:i:s");
            header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
            header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
            header("Last-Modified: {$now} GMT");
            // force download  
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
            // disposition / encoding on response body
            header("Content-Disposition: attachment;filename={$filename}");
            header("Content-Transfer-Encoding: binary");
        }

        function arm_settings_import_handle($request) {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_email_settings, $arm_member_forms, $arm_capabilities_global;

            $ARMember->arm_check_user_cap( $arm_capabilities_global['arm_import_export'], '1',1); //phpcs:ignore --Reason:Verifying nonce

            set_time_limit(0);
            $action = $request['arm_action'];
            if ($action == 'settings_import') {
                $encoded_data = $request['settings_import_text'];
                $all_settings = maybe_unserialize(base64_decode($encoded_data));
                if (!empty($all_settings) && is_array($all_settings)) {

                    $arm_default_global_settings  = $arm_global_settings->arm_default_global_settings();
                    $all_settings = shortcode_atts( $all_settings, $arm_default_global_settings );
                    $all_settings = array_map( array( $ARMember, 'arm_recursive_sanitize_data_extend_only_kses'), $all_settings ); //phpcs:ignore

                    /* For Global Settings */
                    if (isset($all_settings['global_options']) && !empty($all_settings['global_options'])) {
                        $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                        $all_settings['global_options']['restrict_site_access'] = $all_global_settings['general_settings']['restrict_site_access'];
                        
                        $all_settings['global_options'] = apply_filters('arm_general_settings_before_import_additional_settings', $all_settings['global_options']);
                        
                        $all_global_settings['general_settings'] = $all_settings['global_options'];
                        /* Update new General Options */
                        update_option('arm_global_settings', $all_global_settings);
                    }
                    if (isset($all_settings['email_options']) && !empty($all_settings['email_options'])) {
                        $old_email_settings = $arm_email_settings->arm_get_all_email_settings();
                        $old_email_tools = (isset($old_email_settings['arm_email_tools'])) ? $old_email_settings['arm_email_tools'] : array();
                        $arm_mail_authentication = isset($all_settings['email_options']['arm_mail_authentication']) ? intval( $all_settings['email_options']['arm_mail_authentication'] ) : 1;
                        $email_settings = array(
                            'arm_email_from_name' => sanitize_text_field( $all_settings['email_options']['arm_email_from_name'] ),
                            'arm_email_from_email' => sanitize_email( $all_settings['email_options']['arm_email_from_email'] ),
                            'arm_email_server' => sanitize_text_field( $all_settings['email_options']['arm_email_server'] ),
                            'arm_mail_server' => sanitize_text_field( $all_settings['email_options']['arm_mail_server'] ),
                            'arm_mail_port' => sanitize_text_field( $all_settings['email_options']['arm_mail_port'] ),
                            'arm_mail_login_name' => sanitize_text_field( $all_settings['email_options']['arm_mail_login_name'] ),
                            'arm_mail_password' => $all_settings['email_options']['arm_mail_password'], //phpcs:ignore
                            'arm_smtp_enc' => sanitize_text_field( $all_settings['email_options']['arm_smtp_enc'] ),
                            'arm_email_tools' => $old_email_tools,
                            'arm_mail_authentication' => $arm_mail_authentication,
                        );
                        update_option('arm_email_settings', $email_settings);
                    }
                    /* For Block Settings. */
                    if (isset($all_settings['block_options']) && !empty($all_settings['block_options'])) {
                        $new_block_optioins = $all_settings['block_options'];
                        $old_block_settings = $arm_global_settings->arm_get_parsed_block_settings();
                        /* Merge imported settings with old settings */
                        $all_block_settings = array_merge_recursive($old_block_settings, $new_block_optioins);
                        $all_block_settings = $ARMember->arm_array_unique($all_block_settings);
                        /* Set new messages */

                        $all_block_settings['failed_login_lockdown']          = intval( $new_block_optioins['failed_login_lockdown'] );
                        $all_block_settings['remained_login_attempts']        = intval( $new_block_optioins['remained_login_attempts'] );
                        $all_block_settings['max_login_retries']              = intval( $new_block_optioins['max_login_retries'] );
                        $all_block_settings['temporary_lockdown_duration']    = intval( $new_block_optioins['temporary_lockdown_duration'] );
                        $all_block_settings['permanent_login_retries']        = intval( $new_block_optioins['permanent_login_retries'] );
                        $all_block_settings['permanent_lockdown_duration']    = intval( $new_block_optioins['permanent_lockdown_duration'] );
                        $all_block_settings['arm_block_ips_msg']              = !empty($new_block_optioins['arm_block_ips_msg']) ? sanitize_text_field( $new_block_optioins['arm_block_ips_msg'] ) : '';
                        $all_block_settings['arm_block_usernames_msg']        = sanitize_text_field( $new_block_optioins['arm_block_usernames_msg'] );
                        $all_block_settings['arm_block_emails_msg']           = sanitize_text_field( $new_block_optioins['arm_block_emails_msg'] );
                        
                        $all_block_settings['arm_block_urls_option']          = !empty($new_block_optioins['arm_block_urls_option']) ? $new_block_optioins['arm_block_urls_option'] : '' ;
                        $all_block_settings['arm_block_urls_option_message']  = !empty($new_block_optioins['arm_block_urls_option_message']) ? $new_block_optioins['arm_block_urls_option_message'] : '';
                        $all_block_settings['arm_block_urls_option_redirect'] = !empty($new_block_optioins['arm_block_urls_option_redirect']) ? $new_block_optioins['arm_block_urls_option_redirect'] : '';

                        if (isset($all_block_settings['arm_block_ips'])) {
                            $all_block_settings['arm_block_ips'] = is_array($all_block_settings['arm_block_ips']) ? implode(PHP_EOL, array_filter(array_map('trim', $all_block_settings['arm_block_ips']))) : '';
                        }
                        if (isset($all_block_settings['arm_block_usernames'])) {
                            $all_block_settings['arm_block_usernames'] = is_array($all_block_settings['arm_block_usernames']) ? implode(PHP_EOL, array_filter(array_map('trim', $all_block_settings['arm_block_usernames']))) : '';
                        }
                        if (isset($all_block_settings['arm_block_emails'])) {
                            $all_block_settings['arm_block_emails'] = is_array($all_block_settings['arm_block_emails']) ? implode(PHP_EOL, array_filter(array_map('trim', $all_block_settings['arm_block_emails']))) : '';
                        }
                        if (isset($all_block_settings['arm_block_urls'])) {
                            $all_block_settings['arm_block_urls'] = is_array($all_block_settings['arm_block_urls']) ? implode(PHP_EOL, array_filter(array_map('trim', $all_block_settings['arm_block_urls']))) : '';
                        }
                        $all_block_settings['arm_conditionally_block_urls'] = isset($new_block_optioins['arm_conditionally_block_urls']) ? $new_block_optioins['arm_conditionally_block_urls'] : 0;

                        if (isset($all_block_settings['arm_conditionally_block_urls_options']) && is_array($all_block_settings['arm_conditionally_block_urls_options'])) {
                            $conditionally_block_urls_options = array();
                            $condition_count = 0;
                            foreach ($all_block_settings['arm_conditionally_block_urls_options'] as $condition) {
                                if (isset($condition['arm_block_urls']) && $condition['plan_id'] != '') {
                                    $conditionally_block_urls_options[$condition_count]['plan_id'] = $condition['plan_id'];
                                    //$conditionally_block_urls_options[$condition_count]['arm_block_urls'] = $condition['arm_block_urls'];
                                    $arm_block_url = implode(PHP_EOL, array_filter(array_map('trim', explode(PHP_EOL, $condition['arm_block_urls']))));
                                    $conditionally_block_urls_options[$condition_count]['arm_block_urls'] = $arm_block_url;
                                    $condition_count++;
                                }
                            }
                            $all_block_settings['arm_conditionally_block_urls_options'] = $conditionally_block_urls_options;
                        }
                        $all_block_settings = apply_filters('arm_block_settings_before_import_additional_settings', $all_block_settings);

                        /* Update New Block Options */
                        update_option('arm_block_settings', $all_block_settings);
                    }
                    /* For Common Messages */
                    if (isset($all_settings['common_messages']) && !empty($all_settings['common_messages'])) {
                        $all_common_messages = $all_settings['common_messages'];
                        $all_common_messages = apply_filters('arm_common_messages_before_import_additional_settings', $all_common_messages);

                        update_option('arm_common_message_settings', $all_common_messages);
                    }
                    //Print Success Message.
                    $msg[] = esc_html__('Setting(s) has been imported successfully', 'ARMember');
                    self::arm_user_import_export_messages('', $msg);
                    return;
                }
            }
            $errors[] = esc_html__('This is not a valid import file data.', 'ARMember');
            self::arm_user_import_export_messages($errors);
        }

        function arm_settings_export_handle($request) {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_email_settings, $arm_member_forms, $arm_capabilities_global;

            $ARMember->arm_check_user_cap( $arm_capabilities_global['arm_import_export'], '1',1); //phpcs:ignore --Reason:Verifying nonce

            $action = $request['arm_action'];
            $all_settings = array();
            if ($action == 'settings_export') {
                if (!isset($request['global_options']) && !isset($request['block_options']) && !isset($request['common_messages'])) {
                    $errors[] = esc_html__('Please select one or more setting.', 'ARMember');
                    self::arm_user_import_export_messages($errors);
                }
                if (isset($request['global_options'])) {
                    $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
                    $arm_email_settings_data = $arm_email_settings->arm_get_all_email_settings();
                    if (!empty($all_global_settings['general_settings'])) {
                        $all_global_settings['general_settings'] = apply_filters('arm_general_settings_additional_export_data_externally', $all_global_settings['general_settings']);
                        $all_settings['global_options'] = $all_global_settings['general_settings'];
                    }
                    if (!empty($arm_email_settings_data)) {
                        $arm_email_settings_data['arm_email_tools'] = array();
                        $all_settings['email_options'] = $arm_email_settings_data;
                    }
                }
                if (isset($request['block_options'])) {
                    $block_options = $arm_global_settings->arm_get_parsed_block_settings();
                    $block_options = apply_filters('arm_block_settings_additional_export_data_externally', $block_options);
                    
                    if (!empty($block_options)) {
                        $all_settings['block_options'] = $block_options;
                    }
                }
                if (isset($request['common_messages'])) {
                    $common_messages = $arm_global_settings->arm_get_all_common_message_settings();
                    $common_messages = apply_filters('arm_common_messages_additional_export_data_externally', $common_messages);
                    if (!empty($common_messages)) {
                        $all_settings['common_messages'] = $common_messages;
                    }
                }
                if (!empty($all_settings)) {
                    //Encode All Settings Array
                    $encode_all_settings = base64_encode(maybe_serialize($all_settings));
                    $file_name = 'ARMember-export-settings.txt';
                    ob_clean();
                    ob_start();
                    header("Content-Type: plain/text");
                    header('Content-Disposition: attachment; filename="' . $file_name . '"');
                    header("Pragma: no-cache");
                    print($encode_all_settings); //phpcs:ignore
                    exit;
                }
            }
        }

        function arm_user_import_export_messages($errors = '', $messages = '') {
            if (!empty($messages)) {
                if (!is_array($messages)) {
                    $msgs[] = $messages;
                } else {
                    $msgs = $messages;
                }
                foreach ($msgs as $msg) {
                    ?>
                    <div class="arm_message arm_success_message arm_import_export_msg">
                        <div class="arm_message_text"><?php echo esc_html($msg); ?></div>
                        <script type="text/javascript">
                                                jQuery(window).on("load", function(){armToast('<?php echo $msg; //phpcs:ignore ?>', 'success'); });</script>
                    </div>
                    <?php
                }
            }
            if (!empty($errors)) {
                if (!is_array($errors)) {
                    $errs[] = $errors;
                } else {
                    $errs = $errors;
                }
                foreach ($errs as $msg) {
                    ?><script type="text/javascript">jQuery(window).on("load", function(){armToast('<?php echo $msg; //phpcs:ignore?>', 'error'); });</script><?php
                }
            }
        }

        function arm_chartPlanMembers($all_plans = array()) {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans;
            $plans_info = $wpdb->get_results( $wpdb->prepare("SELECT `arm_subscription_plan_id` as id, `arm_subscription_plan_name` as name FROM `" . $ARMember->tbl_arm_subscription_plans . "` WHERE `arm_subscription_plan_is_delete`=%d AND `arm_subscription_plan_post_id`=%d",0,0)); //phpcs:ignore --Reason $ARMember->tbl_arm_subscription_plans is a table name

            if (!empty($plans_info)) {
                $plan_name = $plan_users = "[";
                $plan_name  .= "' ', ";
                $plan_users .= "0, ";
                foreach ($plans_info as $plan) {
                    $user_arg = array(
                        'meta_key'     => 'arm_user_plan_ids',
                        'meta_value'   => '',
                        'meta_compare' => '!=',
                        'role__not_in' => array('administrator'),
                        'date_query'   => array(
                            'after'    => '1 month ago',
                        )
                    );
                    $users = get_users($user_arg);
                    $total_users = 0;
                    if (!empty($users)) {
                        foreach ($users as $user) {
                            $plan_ids = get_user_meta($user->ID, 'arm_user_plan_ids', true);
                            if (!empty($plan_ids) && is_array($plan_ids)) {
                                if (in_array($plan->id, $plan_ids)) {
                                    $total_users++;
                                }
                            }
                        }
                    }

                    if ($total_users > 0) {
                        $plan_name  .= "'".$plan->name."', ";
                        $plan_users .= "{$total_users}, ";
                    }
                }
                $plan_name  .= "]";
                $plan_users .= "]";
                if (!empty($plan_name) && !empty($plan_users)) { ?>
                    <div id="arm_chart_wrapper_plan_members" class="arm_chart_wrapper_plan_members arm_chart_wrapper"></div>
                    <script type="text/javascript">
                        jQuery(document).ready(function ($) {
                            var plan_users = <?php echo $plan_users; //phpcs:ignore ?>;
                            var plan_names = <?php echo $plan_name; //phpcs:ignore ?>;
                            jQuery('#arm_chart_wrapper_plan_members').highcharts({
                                chart: {type: 'areaspline'},
                                title: {text: "<?php echo esc_html__('Recent Members By Plans', 'ARMember');?>"},
                                credits : {
                                    enabled : false
                                },
                                xAxis: {
                                    categories: plan_names,
                                    crosshair: true,
				    labels: {rotation: - 60},
                                    min : 0.5
                                },
                                yAxis: {
                                    min: 0,
                                    allowDecimals: false,
                                    title: {text: ''}
                                },
                                legend: {enabled: false},
                                plotOptions: {
                                    areaspline: {
                                        fillOpacity: 0.05,
                                        dataLabels: {enabled: false, format: '{point.y}'},
                                        lineColor: '#005aee',
                                    }
                                },
                                tooltip: {
                                    formatter: function() {
                                        var tooltip = "";
                                        var index = this.point.index;
                                        var name  = plan_names[index];
                                        if (index == 0) {
                                            name = '0';
                                        }
                                        tooltip   = '<span style="font-size:12px">' + name + ':</span>';
                                        tooltip   += '<div style="color:' + this.series.color + '">(</div><b>' + this.y + '</b><div style="color:' + this.series.color + '">)</div>';
                                        return tooltip;
                                    }
                                },
                                colors: ['#766ed2;', '#fbc32b', '#fc6458', '#a7db1b', '#20d381', '#005aee', '#4da4fe'],
                                series: [{
                                    name: "Membership",
                                    color: 'rgb(0,90,238)',
                                    colorByPoint: true,
                                    lineWidth: 2,
                                    data: plan_users,
                                }],
                            });
                        });
                    </script>
                    <?php
                }
            }
        }

        function arm_chartRecentMembers() {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans;
            $user_table = $wpdb->users;
            $usermeta_table = $wpdb->usermeta;
            $capability_column = $wpdb->get_blog_prefix($GLOBALS['blog_id']) . 'capabilities';

            $super_admin_ids = array();
            if (is_multisite()) {
                $super_admin = get_super_admins();
                if (!empty($super_admin)) {
                    foreach ($super_admin as $skey => $sadmin) {
                        if ($sadmin != '') {
                            $user_obj = get_user_by('login', $sadmin);
                            if ($user_obj->ID != '') {
                                $super_admin_ids[] = $user_obj->ID;
                            }
                        }
                    }
                }
            }

            $user_where = " WHERE 1=1";
            if (!empty($super_admin_ids)) {
                //$user_where .= " AND u.ID NOT IN (" . implode(',', $super_admin_ids) . ")";
                $admin_placeholders = ' AND u.ID NOT IN  (';
                $admin_placeholders .= rtrim( str_repeat( '%s,', count( $super_admin_ids ) ), ',' );
                $admin_placeholders .= ')';
                array_unshift( $super_admin_ids, $admin_placeholders );
                $user_where .= call_user_func_array(array( $wpdb, 'prepare' ), $super_admin_ids );
            }
           
            $user_where .= " AND (um.meta_key = '{$capability_column}' AND um.meta_value NOT LIKE '%administrator%')"; //phpcs:ignore
            $operator = " AND ";
            $user_where .= $operator;
            $user_where .= " u.user_registered >= DATE_SUB(DATE(NOW()), INTERVAL 1 MONTH)";
            $users_details = $wpdb->get_results("SELECT u.ID,u.user_registered FROM `{$user_table}` u LEFT JOIN `{$usermeta_table}` um ON u.ID = um.user_id {$user_where} GROUP BY u.ID ORDER BY u.user_registered ASC"); //phpcs:ignore --Reason $usertable na d $usermeta_table are table names

            $day_records = array();
            foreach ($users_details as $users_det) {
                $users_registered = date('d-M', strtotime($users_det->user_registered));
                $day_records[$users_registered][] = $users_det;
            }

            if (!empty($day_records)) {
                for ($i = 0; $i <=31; $i++) {
                    $date = date('d-M', strtotime("-{$i} days"));;
                    $keys[$date] = $date;
                }
                $keys = array_reverse($keys);
                $disCnt = 0;
                $day_var = $val_var = $custom_key = "[";
                foreach ($keys as $day) {
                    $custom_key .= "'{$day}', ";
                    if (!array_key_exists($day, $day_records)) {
                        if ($disCnt == 0) {
                            $disCnt++;
                            $day_var .= "'{$day}', ";
                            $val_var .= '0, ';
                        } else {
                            $disCnt = 0;
                            $day_var .= "' ', ";
                            $val_var .= '0, ';
                        }
                    } else {
                        $total_users = count($day_records[$day]);
                        if ($disCnt == 0) {
                            $disCnt++;
                            $day_var .= "'{$day}', ";
                            $val_var .= $total_users. ', ';
                        } else {
                            $disCnt = 0;
                            $day_var .= "' ', ";
                            $val_var .= $total_users. ', ';
                        }
                    }
                }
                $day_var .= "]";
                $val_var .= ']';
                $custom_key .= ']';
                unset($disCnt); ?>
                <div id="arm_chart_wrapper_recent_members" class="arm_chart_wrapper_recent_members arm_chart_wrapper"></div>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        var line1 = <?php echo $val_var;//phpcs:ignore ?>;
                        var line2 = <?php echo $custom_key;//phpcs:ignore ?>;
                        jQuery('#arm_chart_wrapper_recent_members').highcharts({
                            chart: {type: 'areaspline'},
                            title: {text: "<?php echo esc_html__('Recent Members', 'ARMember');?>"},
                            xAxis: {
                                categories: <?php echo $day_var;//phpcs:ignore ?>,
                                crosshair: true
                            },
                            credits : {
                                enabled : false
                            },
                            yAxis: {
                                min: 0,
                                allowDecimals: false,
                                title: {text: ''}
                            },
                            legend: {enabled: false},
                            plotOptions: {
                                areaspline: {
                                    fillOpacity: 0.05,
                                    dataLabels: {enabled: false, format: '{point.y}'},
                                    lineColor: '#005aee',
                                }
                            },
                            tooltip: {
                                formatter: function() {
                                    var tooltip = "";
                                    var index = this.point.index;
                                    var name  = line2[index];
                                    tooltip   = '<span style="font-size:12px"></span>';
                                    tooltip   += '<div style="color:' + this.series.color + '">' + name + ': <b>' + this.y + '</b> <?php esc_html_e("Members", 'ARMember'); ?></div>';
                                    return tooltip;
                                }
                            },
                            colors: ['#766ed2;', '#fbc32b', '#fc6458', '#a7db1b', '#20d381', '#005aee', '#4da4fe'],
                            series: [{
                                name: "Members",
                                color: 'rgb(0,90,238)',
                                colorByPoint: true,
                                lineWidth: 2,
                                data: line1,
                            }],
                        });
                    });
                </script>
                <?php
            }
        }

        function armGetMemberStatusText_print($primary_status,$secondary_status) {
            global $armPrimaryStatus, $armSecondaryStatus;
            
            	if ($primary_status == '1') {
                    $statusClass = 'active';
                    $memberStatusText = $armPrimaryStatus[1];
                } elseif ($primary_status == '3') {
                    $statusClass = 'pending';
                    $memberStatusText = $armPrimaryStatus[3];
                } elseif ($primary_status == '4') {
                    $statusClass = 'inactive banned';
                    //$secondaryStatusClass = 'banned';
                    $memberStatusText = $armPrimaryStatus[4];
                } else {
                    $memberStatusText = $armPrimaryStatus[2];
                    $statusClass = 'inactive';
                    $memberSecondaryStatusText = $armSecondaryStatus[$secondary_status];
                    if (isset($armSecondaryStatus[$secondary_status]) && !empty($armSecondaryStatus[$secondary_status])) {
                        switch ($secondary_status) {
                            case '0':
                                $secondaryStatusClass = "banned";
                                break;
                            case '1':
                            case '4':
                            case '6':
                                $secondaryStatusClass = "cancelled";
                                break;
                            case '2':
                            case '3':
                                $secondaryStatusClass = "expired";
                                break;
                            case '5':
                                $secondaryStatusClass = "failed";
                                break;
                            default:
                                $secondaryStatusClass = "cancelled";
                                break;
                        }
                        $statusClass .= " " . $secondaryStatusClass;
                        $memberStatusText .= ' <span class="' . $secondaryStatusClass . '"> (' . $memberSecondaryStatusText . ')</span>';
                    }
                }
            
            return '<span class="arm_item_status_text ' . $statusClass . '">' . $memberStatusText . '</span>';
        }

        function armGetMemberStatusText($user_id = 0, $default_status = '1') {
            global $armPrimaryStatus, $armSecondaryStatus;
            $memberStatusText = $armPrimaryStatus[$default_status];
            if (in_array($default_status, array(2, 4))) {
                $statusClass = 'inactive';
            } else {
                $statusClass = 'active';
            }
            if (!empty($user_id) && $user_id != 0) {
                //$primary_status = $default_status;

                $user_all_status = arm_get_all_member_status($user_id);

                $primary_status = $user_all_status['arm_primary_status'];
                $secondary_status = $user_all_status['arm_secondary_status'];
                if ($primary_status == '1') {
                    $statusClass = 'active';
                    $memberStatusText = $armPrimaryStatus[1];
                } elseif ($primary_status == '3') {
                    $statusClass = 'pending';
                    $memberStatusText = $armPrimaryStatus[3];
                } elseif ($primary_status == '4') {
                    $statusClass = 'inactive banned';
                    //$secondaryStatusClass = 'banned';
                    $memberStatusText = $armPrimaryStatus[4];
                } else {
                    $memberStatusText = $armPrimaryStatus[2];
                    $statusClass = 'inactive';
                    $memberSecondaryStatusText = $armSecondaryStatus[$secondary_status];
                    if (isset($armSecondaryStatus[$secondary_status]) && !empty($armSecondaryStatus[$secondary_status])) {
                        switch ($secondary_status) {
                            case '0':
                                $secondaryStatusClass = "banned";
                                break;
                            case '1':
                            case '4':
                            case '6':
                                $secondaryStatusClass = "cancelled";
                                break;
                            case '2':
                            case '3':
                                $secondaryStatusClass = "expired";
                                break;
                            case '5':
                                $secondaryStatusClass = "failed";
                                break;
                            default:
                                $secondaryStatusClass = "cancelled";
                                break;
                        }
                        $statusClass .= " " . $secondaryStatusClass;
                        $memberStatusText .= ' <span class="' . esc_attr($secondaryStatusClass) . '"> (' . esc_html($memberSecondaryStatusText) . ')</span>';
                    }
                }
            }
            return '<span class="arm_item_status_text ' . esc_attr($statusClass) . '">' . $memberStatusText . '</span>';
        }

        function armGetMemberStatusTextForAdmin($user_id = 0, $default_status = '1', $secondary_status='') {
            global $armPrimaryStatus, $armSecondaryStatus;
            $memberStatusText = $armPrimaryStatus[$default_status];
            if ($default_status == '2') {
                $statusClass = 'inactive';
            } else {
                $statusClass = 'active';
            }
            if (!empty($user_id) && $user_id != 0) {
                $primary_status = $default_status;
                //$primary_status = arm_get_member_status($user_id);

                if ($primary_status == '1') {
                    $statusClass = 'active';
                    $memberStatusText = $armPrimaryStatus[1];
                } elseif ($primary_status == '3') {
                    $statusClass = 'pending';
                    $memberStatusText = $armPrimaryStatus[3];
                } else {
                    $memberStatusText = $armPrimaryStatus[2];
                    $statusClass = 'inactive';
                    if (isset($armSecondaryStatus[$secondary_status]) && !empty($armSecondaryStatus[$secondary_status])) {
                        $memberSecondaryStatusText = $armSecondaryStatus[$secondary_status];
                        switch ($secondary_status) {
                            case '0':
                                $secondaryStatusClass = "banned";
                                break;
                            case '1':
                            case '4':
                            case '6':
                                $secondaryStatusClass = "cancelled";
                                break;
                            case '2':
                            case '3':
                                $secondaryStatusClass = "expired";
                                break;
                            case '5':
                                $secondaryStatusClass = "failed";
                                break;
                            default:
                                $secondaryStatusClass = "cancelled";
                                break;
                        }
                        $statusClass .= " " . $secondaryStatusClass;
                        $memberStatusText .= ' <span class="' . esc_attr($secondaryStatusClass) . '"> (' . esc_html($memberSecondaryStatusText) . ')</span>';
                    }
                }
            }
            return '<span class="arm_item_status_text ' . esc_attr($statusClass) . '">' . $memberStatusText . '</span>';
        }

        function arm_change_user_status($user_data_action = array()) {
            global $wpdb, $arm_email_settings, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_members_class, $arm_subscription_plans, $arm_manage_communication, $arm_slugs, $arm_payment_gateways, $is_multiple_membership_feature, $arm_capabilities_global,$arm_pay_per_post_feature;

            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1);//phpcs:ignore --Reason:Verifying nonce

            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : '';//phpcs:ignore
            if(empty($user_id))
            {
                $user_id = isset($user_data_action['user_id']) ? $user_data_action['user_id'] : '';//phpcs:ignore
            }
                $new_status = isset($_POST['new_status']) ? intval($_POST['new_status']) : '';//phpcs:ignore
            if(empty($new_status))
            {
                $new_status = isset($user_data_action['bulkaction']) ? $user_data_action['bulkaction'] : '';//phpcs:ignore
            }

            $nowDate = current_time('mysql');
            $send_user_notification = isset($_POST['send_user_notification']) ? intval($_POST['send_user_notification']) : '';//phpcs:ignore
            $all_plans = $arm_subscription_plans->arm_get_all_subscription_plans();
            $plansLists = '<li data-label="' . esc_attr__('Select Plan', 'ARMember') . '" data-value="">' . esc_html__('Select Plan', 'ARMember') . '</li>';
            if (!empty($all_plans)) {
                foreach ($all_plans as $p) {
                    $p_id = $p['arm_subscription_plan_id'];
                    if ($p['arm_subscription_plan_status'] == '1') {
                        $plansLists .= '<li data-label="' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '" data-value="' . esc_attr($p_id) . '">' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '</li>';
                    }
                }
            }
            $response = array('type' => 'error', 'msg' => esc_html__('Sorry, Something went wrong. Please try again.', 'ARMember'));
            $is_changed = false;
            if (!empty($user_id) && $user_id != 0) {
                if ($new_status == '1') {
                    arm_set_member_status($user_id, 1);


                    if (!empty($send_user_notification) && $send_user_notification == 1) {
                        $user_data = get_user_by('ID', $user_id);
                        $arm_global_settings->arm_mailer($arm_email_settings->templates->on_menual_activation, $user_id);
                    }
                } else if ($new_status == '2') {
                    arm_set_member_status($user_id, 2, 0);
                } else if ($new_status == '3') {
                    arm_set_member_status($user_id, 3, 0);
                } else if ($new_status == '4') {
                    arm_set_member_status($user_id, 4);
                    $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                    $stop_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                    $stop_future_plan_ids = get_user_meta($user_id, 'arm_user_future_plan_ids', true);

                    if (!empty($stop_future_plan_ids) && is_array($stop_future_plan_ids)) {
                        foreach ($stop_future_plan_ids as $stop_future_plan_id) {
                            $arm_subscription_plans->arm_add_membership_history($user_id, $stop_future_plan_id, 'cancel_subscription', array(), 'terminate');
                            delete_user_meta($user_id, 'arm_user_plan_' . $stop_future_plan_id);
                        }
                        delete_user_meta($user_id, 'arm_user_future_plan_ids');
                    }

                    if (!empty($stop_plan_ids) && is_array($stop_plan_ids)) {
                        foreach ($stop_plan_ids as $stop_plan_id) {
                            $old_plan = new ARM_Plan($stop_plan_id);
                            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $stop_plan_id, true);
                            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                            $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                            $plan_detail = $planData['arm_current_plan_detail'];
                            $planData['arm_cencelled_plan'] = 'yes';
                            update_user_meta($user_id, 'arm_user_plan_' . $stop_plan_id, $planData);

                            if (!empty($plan_detail)) {
                                $planObj = new ARM_Plan(0);
                                $planObj->init((object) $plan_detail);
                            } else {
                                $planObj = new ARM_Plan($stop_plan_id);
                            }
                            if ($planObj->exists() && $planObj->is_recurring()) {
                                do_action('arm_cancel_subscription_gateway_action', $user_id, $stop_plan_id);
                            }
                            $arm_subscription_plans->arm_add_membership_history($user_id, $stop_plan_id, 'cancel_subscription', array(), 'terminate');
                            do_action('arm_cancel_subscription', $user_id, $stop_plan_id);
                            $arm_subscription_plans->arm_clear_user_plan_detail($user_id, $stop_plan_id);
                        }
                    }

                    $sessions = WP_Session_Tokens::get_instance($user_id);
                    $sessions->destroy_all();
                }
                $arm_status = $arm_members_class->armGetMemberStatusText($user_id);

                $userID = $user_id;
                $primary_status = arm_get_member_status($userID);

                $auser = new WP_User($user_id);
                $u_role = array_shift($auser->roles);
                $user_roles = get_editable_roles();
                if (!empty($user_roles[$u_role]['name'])) {
                    $arm_user_role = $user_roles[$u_role]['name'];
                } else {
                    $arm_user_role = '-';
                }
                $userPlanIDS = get_user_meta($userID, 'arm_user_plan_ids', true);

                if($arm_pay_per_post_feature->isPayPerPostFeature)
                {
                    $postIDs = get_user_meta($user_id, 'arm_user_post_ids', true);
                    if(!empty($postIDs))
                    {
                        foreach($userPlanIDS as $arm_plan_keys => $arm_plan_vals)
                        {
                            if(!empty($postIDs[$arm_plan_vals]))
                            {
                                unset($userPlanIDS[$arm_plan_keys]);
                            }
                        }
                    }
                }
                
                $arm_paid_withs = array();
                $effective_from_plans = array();
                if (!empty($userPlanIDS) && is_array($userPlanIDS)) {
                    foreach ($userPlanIDS as $userPlanID) {
                        $planData = get_user_meta($userID, 'arm_user_plan_' . $userPlanID, true);
                        $using_gateway = $planData['arm_user_gateway'];
                        $subscription_effective = $planData['arm_subscr_effective'];
                        $change_plan_to = $planData['arm_change_plan_to'];
                        if (!empty($using_gateway)) {
                            $arm_paid_withs[] = $arm_payment_gateways->arm_gateway_name_by_key($using_gateway);
                        }
                        if (!empty($subscription_effective)) {
                            $effective_from_plans[] = array('subscription_effective_from' => $subscription_effective, 'change_plan_to' => $change_plan_to);
                        }
                    }
                }

                if (!empty($arm_paid_withs)) {
                    $arm_paid_with = implode(",", $arm_paid_withs);
                } else {
                    $arm_paid_with = "-";
                }

                $gridAction = "<div class='arm_grid_action_btn_container'>";
                if ((get_current_user_id() != $userID) && !is_super_admin($userID)) {
                    if ($primary_status == '3') {
                        $activation_key = get_user_meta($userID, 'arm_user_activation_key', true);


                        if (!empty($activation_key) && $activation_key != '') {
                            $gridAction .= "<a href='javascript:void(0)' onclick='showResendVerifyBoxCallback(".esc_attr($userID).");'><img src='" . MEMBERSHIP_IMAGES_URL . "/resend_mail_icon.png' class='armhelptip' title='" . esc_attr__('Resend Verification Email', 'ARMember') . "' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/resend_mail_icon_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/resend_mail_icon.png';\" /></a>";
                            $gridAction .= "<div class='arm_confirm_box arm_resend_verify_box arm_resend_verify_box_".esc_attr($userID)."' id='arm_resend_verify_box_".esc_attr($userID)."'>";
                            $gridAction .= "<div class='arm_confirm_box_body'>";
                            $gridAction .= "<div class='arm_confirm_box_arrow'></div>";
                            $gridAction .= "<div class='arm_confirm_box_text'>";
                            $gridAction .= esc_html__('Are you sure you want to resend verification email?', 'ARMember');
                            $gridAction .= "</div>";
                            $gridAction .= "<div class='arm_confirm_box_btn_container'>";
                            $gridAction .= "<button type='button' class='arm_confirm_box_btn armemailaddbtn arm_resend_verify_email_ok_btn' data-item_id='".esc_attr($userID)."'>" . esc_html__('Ok', 'ARMember') . "</button>";
                            $gridAction .= "<button type='button' class='arm_confirm_box_btn armcancel' onclick='hideConfirmBoxCallback();'>" . esc_html__('Cancel', 'ARMember') . "</button>";
                            $gridAction .= "</div>";
                            $gridAction .= "</div>";
                            $gridAction .= "</div>";
                        }
                    }
                }
                $view_link = admin_url('admin.php?page=' . $arm_slugs->manage_members . '&action=view_member&id=' . $userID);
                $gridAction .= "<a class='arm_openpreview arm_openpreview_popup armhelptip' href='javascript:void(0)' data-id='" . esc_attr($userID) . "' title='" . esc_attr__('View Detail', 'ARMember') . "'><img src='" . MEMBERSHIP_IMAGES_URL . "/grid_preview.png' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_preview_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_preview.png';\" /></a>";
                if (current_user_can('arm_manage_members')) {
                    $edit_link = admin_url('admin.php?page=' . $arm_slugs->manage_members . '&action=edit_member&id=' . $userID);
                    $gridAction .= "<a href='" . esc_url($edit_link) . "' class='armhelptip' title='" . esc_attr__('Edit Member', 'ARMember') . "' ><img src='" . MEMBERSHIP_IMAGES_URL . "/grid_edit.png' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_edit_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_edit.png';\" /></a>";
                }
                if ((get_current_user_id() != $userID) && !is_super_admin($userID)) {
                    $gridAction .= "<a href='javascript:void(0)' onclick='showChangeStatusBoxCallback(".esc_attr($userID).");'><img src='" . MEMBERSHIP_IMAGES_URL . "/change_status_icon.png' class='armhelptip' title='" . esc_attr__('Change Status', 'ARMember') . "' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/change_status_icon_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/change_status_icon.png';\" /></a>";
                    $gridAction .= "<div class='arm_confirm_box arm_change_status_box arm_change_status_box_".esc_attr($userID)."' id='arm_change_status_box_".esc_attr($userID)."'>";
                    $gridAction .= "<div class='arm_confirm_box_body'>";
                    $gridAction .= "<div class='arm_confirm_box_arrow'></div>";
                    $gridAction .= "<div class='arm_confirm_box_text'>";
                    if ($primary_status == '1') {
                        $gridAction .= "<input type='hidden' id='arm_new_assigned_status_".esc_attr($userID)."' data-id='".esc_attr($userID)."' value=''>";
                        $gridAction .= "<dl class='arm_selectbox column_level_dd arm_member_form_dropdown arm_margin_top_10'>";
                        $gridAction .= "<dt><span> " . esc_html__('Select Status', 'ARMember') . " </span><input type='text' style='display:none;' value='' class='arm_autocomplete'/><i class='armfa armfa-caret-down armfa-lg'></i></dt>";
                        $gridAction .= "<dd><ul data-id='arm_new_assigned_status_".esc_attr($userID)."'>";
                        $gridAction .= '<li data-label="' . esc_attr__('Select Status', 'ARMember') . '" data-value="">' . esc_html__('Select Status', 'ARMember') . '</li>';
                        if ($primary_status != 1) {
                            $gridAction .= '<li data-label="' . esc_attr__('Activate', 'ARMember') . '" data-value="1">' . esc_html__('Activate', 'ARMember') . '</li>';
                        }
                        if (!in_array($primary_status, array(2, 4))) {
                            $gridAction .= '<li data-label="' . esc_attr__('Inactivate', 'ARMember') . '" data-value="2">' . esc_html__('Inactivate', 'ARMember') . '</li>';
                        }
                        if ($primary_status != 4) {
                            $gridAction .= '<li data-label="' . esc_attr__('Terminate', 'ARMember') . '" data-value="4">' . esc_html__('Terminate', 'ARMember') . '</li>';
                        }$gridAction .= "</ul></dd>";
                        $gridAction .= "</dl>";
                    } else {
                        //  $gridAction .= esc_html__('Are you sure you want to active this member?', 'ARMember');

                        $gridAction .= "<input type='hidden' id='arm_new_assigned_status_".esc_attr($userID)."' data-id='".esc_attr($userID)."' value='' class='arm_new_assigned_status' data-status='".esc_attr($primary_status)."'>";
                        $gridAction .= "<dl class='arm_selectbox column_level_dd arm_member_form_dropdown arm_margin_top_10' >";
                        $gridAction .= "<dt><span> " . esc_html__('Select Status', 'ARMember') . " </span><input type='text' style='display:none;' value='' class='arm_autocomplete'/><i class='armfa armfa-caret-down armfa-lg'></i></dt>";
                        $gridAction .= "<dd><ul data-id='arm_new_assigned_status_".esc_attr($userID)."'>";
                        $gridAction .= '<li data-label="' . esc_attr__('Select Status', 'ARMember') . '" data-value="">' . esc_html__('Select Status', 'ARMember') . '</li>';
                        if ($primary_status != 1) {
                            $gridAction .= '<li data-label="' . esc_attr__('Activate', 'ARMember') . '" data-value="1">' . esc_html__('Activate', 'ARMember') . '</li>';
                        }
                        if (!in_array($primary_status, array(2, 4))) {
                            $gridAction .= '<li data-label="' . esc_attr__('Inactivate', 'ARMember') . '" data-value="2">' . esc_html__('Inactivate', 'ARMember') . '</li>';
                        }
                        if ($primary_status != 4) {
                            $gridAction .= '<li data-label="' . esc_attr__('Terminate', 'ARMember') . '" data-value="4">' . esc_html__('Terminate', 'ARMember') . '</li>';
                        }
                        $gridAction .= "</ul></dd>";
                        $gridAction .= "</dl>";

                        if ($primary_status == '3') {
                            $gridAction .= "<label style='display: none;' class='arm_notify_user_via_email arm_margin_top_10'>";
                            $gridAction .= "<input type='checkbox' class='arm_icheckbox' id='arm_user_activate_check_".esc_attr($userID)."' value='1' checked='checked'>&nbsp;";
                            $gridAction .= esc_html__('Notify user via email', 'ARMember');
                            $gridAction .= "</label>";
                        }
                    }
                    $gridAction .= "</div>";
                    $gridAction .= "<div class='arm_confirm_box_btn_container'>";
                    $gridAction .= "<button type='button' class='arm_confirm_box_btn armemailaddbtn arm_change_user_status_ok_btn' data-item_id='".esc_attr($userID)."' data-status='".esc_attr($primary_status)."'>" . esc_html__('Ok', 'ARMember') . "</button>";
                    $gridAction .= "<button type='button' class='arm_confirm_box_btn armcancel' onclick='hideConfirmBoxCallback();'>" . esc_html__('Cancel', 'ARMember') . "</button>";
                    $gridAction .= "</div>";
                    $gridAction .= "</div>";
                    $gridAction .= "</div>";
                }

                $gridAction .= "<a href='javascript:void(0)' onclick='arm_member_manage_plan(".esc_attr($userID).");' id='arm_manage_plan_" . esc_attr($userID) . "'><img src='" . MEMBERSHIP_IMAGES_URL . "/grid_manage_plan_icon.png' class='armhelptip' title='" . esc_attr__('Manage Plans', 'ARMember') . "' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_manage_plan_icon_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_manage_plan_icon.png';\" /></a>";

                if (current_user_can('arm_manage_members') && (get_current_user_id() != $userID)) {
                    if (is_multisite() && is_super_admin($userID)) {
                        /* Hide delete button for Super Admins */
                    } else {
                        $gridAction .= "<a href='javascript:void(0)' onclick='showConfirmBoxCallback(".esc_attr($userID).");'><img src='" . MEMBERSHIP_IMAGES_URL . "/grid_delete.png' class='armhelptip' title='" . esc_attr__('Delete', 'ARMember') . "' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_delete_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_delete.png';\" /></a>";
                        $gridAction .= $arm_global_settings->arm_get_confirm_box($userID, esc_html__("Are you sure you want to delete this member?", 'ARMember'), 'arm_member_delete_btn');
                    }
                }
                $gridAction .= "</div>";

                $memberTypeText = $arm_members_class->arm_get_member_type_text($userID);


                $arm_all_user_plans = $userPlanIDS;
                $arm_future_user_plans = get_user_meta($userID, 'arm_user_future_plan_ids', true);
                
                if (!empty($arm_future_user_plans)) {
                    $arm_all_user_plans = array_merge($userPlanIDS, $arm_future_user_plans);
                }
                
                $plan_names = array();
                $subscription_effective_from = array();
                if (!empty($arm_all_user_plans) && is_array($arm_all_user_plans)) {
                    foreach ($arm_all_user_plans as $userPlanID) {
                        $plan_data = get_user_meta($userID, 'arm_user_plan_' . $userPlanID, true);
                        $subscription_effective_from_date = $plan_data['arm_subscr_effective'];
                        $change_plan_to = $plan_data['arm_change_plan_to'];

                        $plan_names[$userPlanID] = $arm_subscription_plans->arm_get_plan_name_by_id($userPlanID);
                        $subscription_effective_from[] = array('arm_subscr_effective' => $subscription_effective_from_date, 'arm_change_plan_to' => $change_plan_to);
                    }
                }

                $memberPlanText = '';
                if ($is_multiple_membership_feature->isMultipleMembershipFeature) {
                    $multiple_membership = 1;
                    $arm_user_plans = '<div class="arm_min_width_120">';
                    $arm_user_plans .= "<a href='javascript:void(0)'  id='arm_show_user_more_plans_" . esc_attr($userID) . "' class='arm_show_user_more_plans' data-id='" . esc_attr($userID) . "'>";
                    if (!empty($arm_all_user_plans) && is_array($arm_all_user_plans)) {

                        foreach ($arm_all_user_plans as $plan_id) {
                            $plan_color_id = ($plan_id > 10) ? intval($plan_id / 10) : $plan_id;
                            $plan_color_id = ($plan_color_id > 10) ? intval($plan_color_id / 10) : $plan_color_id;
                            $arm_user_plans .= "<span class='armhelptip arm_user_plan_circle arm_user_plan_" . esc_attr($plan_color_id) . "' title='" . stripslashes_deep( esc_attr($plan_names[$plan_id]) ) . "' >";
                            $plan_name = str_replace('-', '', stripslashes_deep($plan_names[$plan_id]));
                            $words = explode(" ", $plan_name);
                            $plan_name = '';
                            foreach ($words as $w) {
                                $w = preg_replace('/[^A-Za-z0-9\-]/', '', $w);
                                $plan_name .= mb_substr($w, 0, 1, 'utf-8');
                            }
                            $plan_name = strtoupper($plan_name);
                            $arm_user_plans .= substr($plan_name, 0, 2);
                            $arm_user_plans .= "</span>";
                        }
                    }
                    $arm_user_plans .= "</a></div>";
                    $memberPlanText = $arm_user_plans;
                } else {
                    $multiple_membership = 0;
                    $plan_name = (!empty($plan_names)) ? implode(',', $plan_names) : '-';
                    $memberPlanText = '<span class="arm_user_plan_' . esc_attr($userID) . '">' . esc_html($plan_name) . '</span>';

                    if (!empty($subscription_effective_from)) {
                        foreach ($subscription_effective_from as $subscription_effective) {
                            $subscr_effective = $subscription_effective['arm_subscr_effective'];
                            $change_plan = $subscription_effective['arm_change_plan_to'];
                            $change_plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($change_plan);
                            if (!empty($change_plan) && $subscr_effective > strtotime($nowDate)) {
                                $memberPlanText .= '<div>' . $change_plan_name . '<br/> (' . esc_html__('Effective from', 'ARMember') . ' ' . date_i18n($date_format, $subscr_effective) . ')</div>';
                            }
                        }
                    }
                }
                $is_changed = true;
                $response = array('type' => 'success', 'msg' => esc_html__('User status has been changed successfully.', 'ARMember'), 'status' => $arm_status, 'grid_action' => $gridAction, 'user_role' => $arm_user_role, 'paid_with' => $arm_paid_with, 'membership_type' => $memberTypeText, 'membership_plan' => $memberPlanText, 'multiple_membership' => $multiple_membership,'paid_post'=>$arm_pay_per_post_feature->isPayPerPostFeature);
            }
            if (empty($user_data_action)) {
                echo json_encode($response);
                die();
            } else {
                return $is_changed;
            }
        }

        function arm_resend_verification_email_func($user_id = 0) {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_capabilities_global;
            $response = array('type' => 'error', 'msg' => esc_html__('Sorry, Something went wrong. Please try again.', 'ARMember'));
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1);//phpcs:ignore --Reason:Verifying nonce
            if (isset($_POST['action']) && $_POST['action'] == 'arm_resend_verification_email') { //phpcs:ignore
                $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0; //phpcs:ignore
            }
            if (!empty($user_id) && $user_id != 0) {
                $user = new WP_User($user_id);
                $activation_key = get_user_meta($user->ID, 'arm_user_activation_key', true);
                if ($user->exists() && !empty($activation_key)) {
                    $rve = armEmailVerificationMail($user);
                    if ($rve) {
                        $response = array('type' => 'success', 'msg' => esc_html__('User verification email has been sent successfully.', 'ARMember'));
                    }
                }
            }
            if (isset($_POST['action']) && $_POST['action'] == 'arm_resend_verification_email') { //phpcs:ignore
                echo json_encode($response);
                die();
            }
            return $response;
        }

        function arm_get_next_due_date_by_start_date($user_id,$plan_id,$planStart,$payment_cycle,$num_rec)
        {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans;
            $memberTypeText = '';
            $planID = $plan_id;

            $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $planID, true);
            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
            $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);
            $plan_detail = $planData['arm_current_plan_detail'];
            $expire_time = '';
            if (!empty($plan_detail)) {
                $planObj = new ARM_Plan(0);
                $planObj->init((object) $plan_detail);
            } else {
                $planObj = new ARM_Plan($planID);
            }
            $plan_options = $planObj->options;
            if (!$planObj->is_free()) {
                if ($planObj->is_recurring()) {
                    if (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) {
                        if ($payment_cycle == '') {
                            $payment_cycle = 0;
                        }
                        $arm_user_payment_cycle = $plan_options['payment_cycles'][$payment_cycle];
                        $planRecurringOpts = array();
                        $planRecurringOpts['type'] = !empty($arm_user_payment_cycle['billing_type']) ? $arm_user_payment_cycle['billing_type'] : 'M';
                        $billing_cycle = !empty($arm_user_payment_cycle['billing_cycle']) ? $arm_user_payment_cycle['billing_cycle'] : '1';
                        $intervalDate = '';
                        switch ($planRecurringOpts['type']) {
                            case 'D':
                                $intervalDate = "+$num_rec day";
                                break;
                            case 'M':
                                $intervalDate = "+$num_rec month";
                                break;
                            case 'Y':
                                $intervalDate = "+$num_rec year";
                                break;
                            default:
                            $intervalDate = "+$num_rec day";
                                break;
                        }
                    }
                    $expire_time = strtotime(date('Y-m-d', strtotime($intervalDate, intval($planStart))));
                }
            }
            return $expire_time;
        }

        function arm_get_next_due_date($user_id = 0, $plan_id = 0, $allow_trial = true, $payment_cycle = 0, $planStart = '') {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans;
            $memberTypeText = '';
            $planID = $plan_id;



            $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
            $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $planID, true);
            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
            $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);

            $plan_detail = $planData['arm_current_plan_detail'];
            $expire_time = '';
            if (!empty($plan_detail)) {
                $planObj = new ARM_Plan(0);
                $planObj->init((object) $plan_detail);
            } else {
                $planObj = new ARM_Plan($planID);
            }
            if (!empty($user_id) && $user_id != 0 && !empty($planID) && $planObj->exists()) {

                $planStart = !empty($planStart) ? $planStart : $planData['arm_start_plan'];

                $planExpire = $planData['arm_expire_plan'];
                $paymentMode = $planData['arm_payment_mode'];
                $planType = esc_html__('Free', 'ARMember');
                $planExpireText = '';
                if (!$planObj->is_free()) {
                    if ($planObj->is_recurring()) {

                        $plan_options = $planObj->options;
                        if (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) {
                            if ($payment_cycle == '') {
                                $payment_cycle = 0;
                            }
                            $arm_user_payment_cycle = $plan_options['payment_cycles'][$payment_cycle];
                            $planRecurringOpts = array();
                            $planRecurringOpts['type'] = !empty($arm_user_payment_cycle['billing_type']) ? $arm_user_payment_cycle['billing_type'] : 'M';
                            $billing_cycle = !empty($arm_user_payment_cycle['billing_cycle']) ? $arm_user_payment_cycle['billing_cycle'] : '1';
                            switch ($planRecurringOpts['type']) {
                                case 'D':
                                    $planRecurringOpts['days'] = $billing_cycle;
                                    break;
                                case 'M':
                                    $planRecurringOpts['months'] = $billing_cycle;
                                    break;
                                case 'Y':
                                    $planRecurringOpts['years'] = $billing_cycle;
                                    break;
                                default:
                                    $planRecurringOpts['days'] = $billing_cycle;
                                    break;
                            }
                            $planRecurringOpts['time'] = (!empty($arm_user_payment_cycle['recurring_time'])) ? $arm_user_payment_cycle['recurring_time'] : 'infinite';
                        } else {
                            $planRecurringOpts = isset($planObj->options['recurring']) ? $planObj->options['recurring'] : array();
                        }



                        $planType = esc_html__('Subscription', 'ARMember');
                        $planTrialOpts = isset($planObj->options['trial']) ? $planObj->options['trial'] : array();
                        if (!empty($planRecurringOpts)) {
                            $period = !empty($planRecurringOpts['type']) ? $planRecurringOpts['type'] : 'M';
                            $start_type = $planObj->options['recurring']['manual_billing_start'];
                            $total_payments = $planRecurringOpts['time'];
                            $done_payments = $planData['arm_completed_recurring'];
                            $current_day = date('Y-m-d', intval($planStart));
                            $billing_type = $period;
                            /* if plan has trial and first time plan start day will be the next due date o_0 */
                            if (($done_payments === '' || $done_payments === 0) && $planObj->has_trial_period() && $allow_trial == true) {
                                $intervalDate = date('Y-m-d', $planStart);
                            } else {
                                $done_payments = ($done_payments != '' && $done_payments != 0) ? $done_payments : 1;
                                if ($start_type == 'transaction_day' || $paymentMode=='auto_debit_subscription') {
                                    $billing_type = $period;
                                    if ($billing_type == 'D') {
                                        $days = $planRecurringOpts['days'];
                                        $days = $done_payments * $days;
                                        $intervalDate = "+$days day";
                                    } else if ($billing_type == 'M') {
                                        $months = $planRecurringOpts['months'];
                                        $months = $done_payments * $months;
                                        $intervalDate = "+$months month";
                                    } else if ($billing_type == 'Y') {
                                        $years = $planRecurringOpts['years'];
                                        $years = $done_payments * $years;
                                        $intervalDate = "+$years year";
                                    }
                                } else {
                                    $billing_type = $period;
                                    if ($billing_type == 'D') {
                                        $days = $planRecurringOpts['days'];
                                        $days = $done_payments * $days;
                                        $intervalDate = "+$days day";
                                    } else {
                                        if (date('d', strtotime($current_day)) < $start_type) {
                                            if ($billing_type == 'M') {
                                                $months = $planRecurringOpts['months'];
                                                $months = $done_payments * $months;
                                                if ($months > 0) {
                                                    $tmonths = ($months >= 1) ? $months : $months - 1;
                                                } else {
                                                    $tmonths = $months;
                                                }
                                                $intervalDate = date('Y-m-' . $start_type, strtotime("$current_day+$tmonths month"));
                                            } else if ($billing_type == 'Y') {
                                                $years = $planRecurringOpts['years'];
                                                $years = $done_payments * $years;
                                                if ($years > 0) {
                                                    $tyears = ($years >= 1) ? $years : $years - 1;
                                                } else {
                                                    $tyears = $years;
                                                }
                                                $intervalDate = date('Y-m-' . $start_type, strtotime("$current_day+$tyears year"));
                                            }
                                        } else if (date('d', strtotime($current_day)) >= $start_type) {
                                            if ($billing_type == 'M') {
                                                $months = $planRecurringOpts['months'];
                                                $months = $done_payments * $months;
                                                $intervalDate = date('Y-m-d', strtotime(date('Y-m-' . $start_type, strtotime("$current_day+$months month"))));
                                            } else if ($billing_type == 'Y') {
                                                $years = $planRecurringOpts['years'];
                                                $years = $done_payments * $years;
                                                $intervalDate = date('Y-m-d', strtotime(date('Y-m-' . $start_type, strtotime("$current_day+$years year"))));
                                            }
                                        }
                                    }
                                }
                            }




                            $expire_time = strtotime(date('Y-m-d', strtotime($intervalDate, intval($planStart))));
                        }
                    } /* End `ELSE - ($planObj->is_recurring())` */
                    //}/* End `ELSE - ($planObj->is_lifetime())` */
                }/* End `(!$planObj->is_free())` */


                $memberTypeText .= $expire_time;
            }
            return $memberTypeText;
        }

        function arm_get_next_due_date_old($user_id = 0, $plan_id = 0, $allow_trial = true, $payment_cycle = 0, $planStart = '') {
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans;
            $memberTypeText = '';
            $planID = $plan_id;




            $plan_detail = get_user_meta($user_id, 'arm_current_plan_detail', true);

            $expire_time = '';
            if (!empty($plan_detail)) {
                $planObj = new ARM_Plan(0);
                $planObj->init((object) $plan_detail);
            } else {
                $planObj = new ARM_Plan($planID);
            }
            if (!empty($user_id) && $user_id != 0 && !empty($planID) && $planObj->exists()) {

                $planStart = get_user_meta($user_id, 'arm_start_plan_' . $planID, true);
                $planExpire = get_user_meta($user_id, 'arm_expire_plan_' . $planID, true);
                $paymentMode = get_user_meta($user_id, 'arm_selected_payment_mode', true);


                $planType = esc_html__('Free', 'ARMember');
                $planExpireText = '';
                if (!$planObj->is_free()) {
                    if ($planObj->is_recurring()) {

                        $plan_options = $planObj->options;
                        if (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) {
                            if ($payment_cycle == '') {
                                $payment_cycle = 0;
                            }
                            $arm_user_payment_cycle = $plan_options['payment_cycles'][$payment_cycle];
                            $planRecurringOpts = array();
                            $planRecurringOpts['type'] = !empty($arm_user_payment_cycle['billing_type']) ? $arm_user_payment_cycle['billing_type'] : 'M';
                            $billing_cycle = !empty($arm_user_payment_cycle['billing_cycle']) ? $arm_user_payment_cycle['billing_cycle'] : '1';
                            switch ($planRecurringOpts['type']) {
                                case 'D':
                                    $planRecurringOpts['days'] = $billing_cycle;
                                    break;
                                case 'M':
                                    $planRecurringOpts['months'] = $billing_cycle;
                                    break;
                                case 'Y':
                                    $planRecurringOpts['years'] = $billing_cycle;
                                    break;
                                default:
                                    $planRecurringOpts['days'] = $billing_cycle;
                                    break;
                            }
                            $planRecurringOpts['time'] = (!empty($arm_user_payment_cycle['recurring_time'])) ? $arm_user_payment_cycle['recurring_time'] : 'infinite';
                        } else {
                            $planRecurringOpts = isset($planObj->options['recurring']) ? $planObj->options['recurring'] : array();
                        }



                        $planType = esc_html__('Subscription', 'ARMember');
                        $planTrialOpts = isset($planObj->options['trial']) ? $planObj->options['trial'] : array();
                        if (!empty($planRecurringOpts)) {
                            $period = !empty($planRecurringOpts['type']) ? $planRecurringOpts['type'] : 'M';
                            $start_type = $planObj->options['recurring']['manual_billing_start'];
                            $total_payments = $planRecurringOpts['time'];
                            $done_payments = get_user_meta($user_id, 'arm_completed_recurring_' . $planID, true);
                            $current_day = date('Y-m-d', $planStart);
                            $billing_type = $period;
                            /* if plan has trial and first time plan start day will be the next due date o_0 */
                            if (($done_payments === '' || $done_payments === 0) && $planObj->has_trial_period() && $allow_trial == true) {
                                $intervalDate = date('Y-m-d', $planStart);
                            } else {
                                $done_payments = ($done_payments != '' && $done_payments != 0) ? $done_payments : 1;
                                if ($start_type == 'transaction_day' || $paymentMode=='auto_debit_subscription') {
                                    $billing_type = $period;
                                    if ($billing_type == 'D') {
                                        $days = $planRecurringOpts['days'];
                                        $days = $done_payments * $days;
                                        $intervalDate = "+$days day";
                                    } else if ($billing_type == 'M') {
                                        $months = $planRecurringOpts['months'];
                                        $months = $done_payments * $months;
                                        $intervalDate = "+$months month";
                                    } else if ($billing_type == 'Y') {
                                        $years = $planRecurringOpts['years'];
                                        $years = $done_payments * $years;
                                        $intervalDate = "+$years year";
                                    }
                                } else {
                                    $billing_type = $period;
                                    if ($billing_type == 'D') {
                                        $days = $planRecurringOpts['days'];
                                        $days = $done_payments * $days;
                                        $intervalDate = "+$days day";
                                    } else {
                                        if (date('d', strtotime($current_day)) < $start_type) {
                                            if ($billing_type == 'M') {
                                                $months = $planRecurringOpts['months'];
                                                $months = $done_payments * $months;
                                                if ($months > 0) {
                                                    $tmonths = ($months >= 1) ? $months : $months - 1;
                                                } else {
                                                    $tmonths = $months;
                                                }
                                                $intervalDate = date('Y-m-' . $start_type, strtotime("$current_day+$tmonths month"));
                                            } else if ($billing_type == 'Y') {
                                                $years = $planRecurringOpts['years'];
                                                $years = $done_payments * $years;
                                                if ($years > 0) {
                                                    $tyears = ($years >= 1) ? $years : $years - 1;
                                                } else {
                                                    $tyears = $years;
                                                }
                                                $intervalDate = date('Y-m-' . $start_type, strtotime("$current_day+$tyears year"));
                                            }
                                        } else if (date('d', strtotime($current_day)) >= $start_type) {
                                            if ($billing_type == 'M') {
                                                $months = $planRecurringOpts['months'];
                                                $months = $done_payments * $months;
                                                $intervalDate = date('Y-m-d', strtotime(date('Y-m-' . $start_type, strtotime("$current_day+$months month"))));
                                            } else if ($billing_type == 'Y') {
                                                $years = $planRecurringOpts['years'];
                                                $years = $done_payments * $years;
                                                $intervalDate = date('Y-m-d', strtotime(date('Y-m-' . $start_type, strtotime("$current_day+$years year"))));
                                            }
                                        }
                                    }
                                }
                            }


                            $expire_time = strtotime(date('Y-m-d', strtotime($intervalDate, $planStart)));
                        }
                    } /* End `ELSE - ($planObj->is_recurring())` */
                    //}/* End `ELSE - ($planObj->is_lifetime())` */
                }/* End `(!$planObj->is_free())` */


                $memberTypeText .= $expire_time;
            }
            return $memberTypeText;
        }

        function arm_get_start_date_for_auto_debit_plan($plan_id = 0, $trial = true, $payment_cycle = 0, $plan_action = '', $user_id = 0) {
            $planObj = new ARM_Plan($plan_id);

            $plan_options = $planObj->options;
            if (isset($plan_options['payment_cycles']) && !empty($plan_options['payment_cycles'])) {
                $arm_user_payment_cycle = $plan_options['payment_cycles'][$payment_cycle];
                $planRecurringOpts = array();
                $planRecurringOpts['type'] = !empty($arm_user_payment_cycle['billing_type']) ? $arm_user_payment_cycle['billing_type'] : 'M';
                $billing_cycle = !empty($arm_user_payment_cycle['billing_cycle']) ? $arm_user_payment_cycle['billing_cycle'] : '1';
                switch ($planRecurringOpts['type']) {
                    case 'D':
                        $planRecurringOpts['days'] = $billing_cycle;
                        break;
                    case 'M':
                        $planRecurringOpts['months'] = $billing_cycle;
                        break;
                    case 'Y':
                        $planRecurringOpts['years'] = $billing_cycle;
                        break;
                    default:
                        $planRecurringOpts['days'] = $billing_cycle;
                        break;
                }
                $planRecurringOpts['time'] = (!empty($arm_user_payment_cycle['recurring_time'])) ? $arm_user_payment_cycle['recurring_time'] : 'infinite';
            } else {
                $planRecurringOpts = isset($planObj->options['recurring']) ? $planObj->options['recurring'] : array();
            }



            $planTrialOpts = isset($planObj->options['trial']) ? $planObj->options['trial'] : array();
            $startDate = strtotime(date('Y-m-d'));
            if (!empty($planRecurringOpts)) {
                $period = !empty($planRecurringOpts['type']) ? $planRecurringOpts['type'] : 'M';

                $total_payments = $planRecurringOpts['time'];
                $current_day = strtotime(date('Y-m-d'));
                if (!empty($user_id)) {
                    if ($plan_action == 'renew_subscription') {
                        $user_plan_data = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                        $user_plan_data = !empty($user_plan_data) ? $user_plan_data : array();
                        $plan_expiry_date = isset($user_plan_data['arm_expire_plan']) && !empty($user_plan_data['arm_expire_plan']) ? $user_plan_data['arm_expire_plan'] : strtotime(date('Y-m-d'));
                        $current_day = $plan_expiry_date;
                    } else {
                        $current_day = strtotime(date('Y-m-d'));
                    }
                }




                if ($planObj->has_trial_period() && !empty($planTrialOpts) && $trial) {
                    $trial_type = $planTrialOpts['type'];
                    switch ($trial_type) {
                        case 'D':
                            $days = $planTrialOpts['days'];
                            $intervalDate = "+$days day";
                            break;
                        case 'M':
                            $months = $planTrialOpts['months'];
                            $intervalDate = "+$months month";
                            break;
                        case 'Y':
                            $years = $planTrialOpts['years'];
                            $intervalDate = "+$years year";
                            break;
                        default:
                            break;
                    }
                } else {
                    $billing_type = $period;
                    switch ($billing_type) {
                        case 'D':
                            $days = $planRecurringOpts['days'];
                            $intervalDate = "+$days day";
                            break;
                        case 'M':
                            $months = $planRecurringOpts['months'];
                            $intervalDate = "+$months month";
                            break;
                        case 'Y':
                            $years = $planRecurringOpts['years'];
                            $intervalDate = "+$years year";
                            break;
                        default:
                            break;
                    }
                }
                $startDate = strtotime(date('Y-m-d', strtotime($intervalDate, $current_day)));
            }
            return $startDate;
        }

        function arm_get_member_type_text($user_id = 0) {

            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_global_settings, $arm_pay_per_post_feature;
            $memberTypeText = '';
            $planIDs = get_user_meta($user_id, 'arm_user_plan_ids', true);

            if($arm_pay_per_post_feature->isPayPerPostFeature)
            {
                $postIDs = get_user_meta($user_id, 'arm_user_post_ids', true);
                if(!empty($postIDs))
                {
                    foreach($planIDs as $arm_plan_keys => $arm_plan_vals)
                    {
                        if(!empty($postIDs[$arm_plan_vals]))
                        {
                            unset($planIDs[$arm_plan_keys]);
                        }
                    }
                }
            }

            $planIDs = apply_filters('arm_modify_plan_ids_externally', $planIDs, $user_id);
    
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            if (!empty($planIDs) && is_array($planIDs)) {
                $morePlans = '<ul>';
                $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                foreach ($planIDs as $planID) {

                    $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $planID, true);
                    $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                    $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                    $plan_detail = $planData['arm_current_plan_detail'];
                    $payment_cycle = $planData['arm_payment_cycle'];
                    if (!empty($plan_detail)) {
                        $planObj = new ARM_Plan(0);
                        $planObj->init((object) $plan_detail);
                    } else {
                        $planObj = new ARM_Plan($planID);
                    }
                    if (!empty($user_id) && $user_id != 0 && !empty($planID) && $planObj->exists() && empty($plan_detail['arm_subscription_plan_post_id']) ) {
                        $userPlanCurrencymeta  = "";
                        $userPlanCurrencymeta = apply_filters('arm_get_member_currency_outside', $userPlanCurrencymeta, $user_id, $planID);
                        $planStart = $planData['arm_start_plan'];
                        $planExpire = $planData['arm_expire_plan'];
                        $paymentMode = $planData['arm_payment_mode'];
                        $planType = esc_html__('Free', 'ARMember');
                        $payment_mode_text = '';


                        $planExpireText = '';
                        if (!$planObj->is_free()) {
                            if ($planObj->is_lifetime()) {
                                $planType = esc_html__('Life Time', 'ARMember');
                            } else {
                                if ($planObj->is_recurring()) {
                                    $planType = esc_html__('Subscription', 'ARMember');
                                    $plan_options = $planObj->options;
                                    $planRecurringData = $planObj->prepare_recurring_data($payment_cycle);
                                    $arm_membership_cycle = $planObj->new_user_plan_text(false, $payment_cycle, false, $userPlanCurrencymeta);
                                    $arm_installments_text = '';

                                    if ($paymentMode == 'auto_debit_subscription') {
                                        $payment_mode_text = "<span>(" . esc_html__('Automatic', 'ARMember') . ")</span>";
                                    }
                                    $planTrialOpts = isset($planObj->options['trial']) ? $planObj->options['trial'] : array();
                                    if (!empty($planRecurringData)) {
                                        $total_payments = !empty($planRecurringData['rec_time']) ? $planRecurringData['rec_time'] : 0;
                                        $done_payments = !empty($planData['arm_completed_recurring']) ? $planData['arm_completed_recurring'] : 0;

                                        if (isset($planRecurringData['rec_time']) && isset($planData['arm_completed_recurring']) && $total_payments!='infinite' ) {
                                            if (!empty($planData['arm_expire_plan'])) {
                                                if ($total_payments - $done_payments > 0) {

                                                    $arm_installments_text = ($total_payments - $done_payments) . ' / ' . $total_payments . ' ' . esc_html__('cycles due', 'ARMember');
                                                } else {
                                                    $arm_installments_text = esc_html__('No cycles due', 'ARMember');
                                                }
                                            }
                                        }
                                    }
                                    if ($arm_membership_cycle != '') {
                                        $planExpireText .= "<span class='arm_user_plan_type arm_plan_cycle'> " . esc_html($arm_membership_cycle) . " </span>";
                                    }

                                    $planExpireText .= '<span class="arm_user_plan_expire_text" style="margin-bottom: 3px;">';
                                    if ($done_payments < $total_payments || $total_payments == 'infinite') {
                                        $planExpireText .= esc_html__('Renewal On', 'ARMember');
                                        $expire_time = $planData['arm_next_due_payment'];
                                        $planExpireText .= '<span>(' . esc_html( date_i18n($date_format, $expire_time) ) . ')</span>';
                                    } else if ($done_payments >= $total_payments) {
                                        $planExpireText .= esc_html__('Expires On', 'ARMember');
                                        $expire_time = $planData['arm_expire_plan'];
                                        $planExpireText .= '<span>(' . esc_html(date_i18n($date_format, $expire_time)) . ')</span>';
                                    }

                                    $planExpireText .= '</span>';

                                    if ($arm_installments_text != '') {
                                        $planExpireText .= "<span class='arm_user_plan_type arm_user_installments' style='margin-bottom: 3px;'>" . esc_html($arm_installments_text) . "</span>";
                                    }
                                    $planExpireText .= $payment_mode_text;
                                } else {
                                    $planType = esc_html__('One Time', 'ARMember');
                                    $planExpireText .= '<span class="arm_user_plan_expire_text">';
                                    $planExpireText .= esc_html__('Expires On', 'ARMember');
                                    $planExpireText .= '<span>(' . esc_html(date_i18n($date_format, $planExpire) ) . ')</span>';
                                    $planExpireText .= '</span>';
                                }/* End `ELSE - ($planObj->is_recurring())` */
                            }/* End `ELSE - ($planObj->is_lifetime())` */
                        }/* End `(!$planObj->is_free())` */

                        $morePlans .= '<span class="arm_user_plan_type_text">' . esc_html($planType) . '</span>';
                        $morePlans .= $planExpireText;
                        $morePlans .= '</li>';
                    }
                }
                $morePlans .= '</ul>';

                $memberTypeText .= $morePlans;
            }
            return $memberTypeText;
        }

        function arm_import_member_progress() {
            global $ARMember,$arm_capabilities_global;
            $ARMember->arm_session_start();
            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_import_export'], '1',1); //phpcs:ignore --Reason:Verifying nonce
            $total_members = isset($_REQUEST['total_members']) ? (int) $_REQUEST['total_members'] : 0;
            $imported_users = isset($_SESSION['imported_users']) ? (int) $_SESSION['imported_users'] : 0;
            $response = array();
            $response['total_members'] = $total_members;
            $response['currently_imported'] = $imported_users;
            if ($response['total_members'] == 0) {
                $response['error'] = true;
                $response['continue'] = false;
            } else {
                if ($response['currently_imported'] > 0) {
                    if ($response['currently_imported'] == $response['total_members']) {
                        $percentage = 100;
                        $response['continue'] = false;
                        unset($_SESSION['imported_users']);
                    } else {
                        $percentage = (100 * $response['currently_imported']) / $response['total_members'];
                        $percentage = round($percentage);
                        $response['continue'] = true;
                    }
                    $response['percentage'] = $percentage;
                } else {
                    $response['percentage'] = 0;
                    $response['continue'] = true;
                }
                $response['error'] = false;
            }
            @session_write_close();
            $ARMember->arm_session_start(true);
            echo json_encode(stripslashes_deep($response));
            die();
        }

        function arm_get_member_grid_data() {
            global $wpdb, $ARMember, $arm_slugs, $arm_members_class, $arm_member_forms, $arm_global_settings, $arm_subscription_plans, $arm_payment_gateways, $is_multiple_membership_feature, $arm_capabilities_global, $arm_pay_per_post_feature;

            $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1);//phpcs:ignore --Reason:Verifying nonce
            $date_format = $arm_global_settings->arm_get_wp_date_format();
            $user_roles = get_editable_roles();
            $nowDate = current_time('mysql');
            $all_plans = $arm_subscription_plans->arm_get_all_subscription_plans();
            if(!empty($_POST['data'])) //phpcs:ignore
            {
                $_REQUEST = $_POST = json_decode(stripslashes_deep($_REQUEST['data']),true);  //phpcs:ignore
            }

            
            $grid_columns = array(
                'avatar' => esc_html__('Avatar', 'ARMember'),
                'ID' => esc_html__('User ID', 'ARMember'),
                'user_login' => esc_html__('Username', 'ARMember'),
                'user_email' => esc_html__('Email Address', 'ARMember'),
                'arm_user_plan_ids' => esc_html__('Member Plan', 'ARMember'),
            );
            if($arm_pay_per_post_feature->isPayPerPostFeature)
            {
                
                $grid_columns['arm_user_paid_plans'] = esc_html__('Paid Post(s)', 'ARMember');
                    
            }
            $grid_columns['arm_primary_status'] = esc_html__('Status', 'ARMember');
            if(!$is_multiple_membership_feature->isMultipleMembershipFeature)
            {
                $grid_columns['roles'] = esc_html__('User Role', 'ARMember');
            }
            $grid_columns['first_name'] = esc_html__('First Name', 'ARMember');
            $grid_columns['last_name'] = esc_html__('Last Name', 'ARMember');
            $grid_columns['display_name'] = esc_html__('Display Name', 'ARMember');
            $grid_columns['user_registered'] = esc_html__('Joined Date', 'ARMember');

            $plansLists = '<li data-label="' . esc_attr__('Select Plan', 'ARMember') . '" data-value="">' . esc_html__('Select Plan', 'ARMember') . '</li>';
            if (!empty($all_plans)) {
                foreach ($all_plans as $p) {
                    $p_id = $p['arm_subscription_plan_id'];
                    if ($p['arm_subscription_plan_status'] == '1') {
                        $plansLists .= '<li data-label="' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '" data-value="' . esc_attr($p_id) . '">' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '</li>';
                    }
                }
            }

            $displayed_grid_columns = $grid_columns;
            $filter_plan_id = (!empty($_REQUEST['filter_plan_id']) && $_REQUEST['filter_plan_id'] != '0') ? sanitize_text_field($_REQUEST['filter_plan_id']) : '';
            $payment_mode_id = (!empty($_REQUEST['filter_mode_id']) && $_REQUEST['filter_mode_id'] != '') ? sanitize_text_field($_REQUEST['filter_mode_id']) : '';
            $filter_status_id = (!empty($_REQUEST['filter_status_id']) && $_REQUEST['filter_status_id'] != 0) ? sanitize_text_field($_REQUEST['filter_status_id']) : '';
            
            $filter_meta_field_key = (!empty($_REQUEST['filter_meta_field_key']) && $_REQUEST['filter_meta_field_key'] != '0') ? sanitize_text_field($_REQUEST['filter_meta_field_key']) : '';

            $user_meta_keys = $arm_member_forms->arm_get_db_form_fields(true);
            if (!empty($user_meta_keys)) {
                $exclude_keys = array('user_pass', 'repeat_pass', 'rememberme', 'remember_me', 'section', 'html','arm_captcha');
                $exclude_keys = array_merge($exclude_keys, array_keys($grid_columns));
                foreach ($user_meta_keys as $umkey => $val) {
                    if (!in_array($umkey, $exclude_keys)) {
                        $grid_columns[$umkey] = $val['label'];
                    }
                }
            }
            if(!$is_multiple_membership_feature->isMultipleMembershipFeature)
            {
                $grid_columns['paid_with'] = esc_html__('Paid With', 'ARMember');
            }
            $grid_columns['action_btn'] = '';
            $user_args = array(
                'orderby' => 'ID',
                'order' => 'DESC',
            );

            $data_columns = array();
            $n = 0;
            foreach ($grid_columns as $key => $value) {
                $data_columns[$n]['data'] = $key;
                $n++;
            }
            unset($n);

            $user_offset = isset($_REQUEST['iDisplayStart']) ? intval($_REQUEST['iDisplayStart']) : 0;
            $user_number = isset($_REQUEST['iDisplayLength']) ? intval($_REQUEST['iDisplayLength']) : 10;

            $super_admin_ids = array();
            if (is_multisite()) {
                $super_admin = get_super_admins();
                if (!empty($super_admin)) {
                    foreach ($super_admin as $skey => $sadmin) {
                        if ($sadmin != '') {
                            $user_obj = get_user_by('login', $sadmin);
                            if ($user_obj->ID != '') {
                                $super_admin_ids[] = $user_obj->ID;
                            }
                        }
                    }
                }
            }
            $user_where = " WHERE 1=1";
            if (!empty($super_admin_ids)) {
                $users_admin_placeholders = ' AND u.ID IN (';
                $users_admin_placeholders .= rtrim( str_repeat( '%s,', count( $super_admin_ids ) ), ',' );
                $users_admin_placeholders .= ')';

                array_unshift( $super_admin_ids, $users_admin_placeholders );

                $user_where .= call_user_func_array(array( $wpdb, 'prepare' ), $super_admin_ids );
                //$user_where .= ' AND u.ID IN (' . implode( ',', $super_admin_ids ) . ')';
            }
            $user_table = $wpdb->users;
            $usermeta_table = $wpdb->usermeta;
            $arm_user_table = $ARMember->tbl_arm_members;
            $capability_column = $wpdb->get_blog_prefix($GLOBALS['blog_id']) . 'capabilities';
            $operator = " AND ";
            if (!empty($super_admin_ids)) {
                $operator = " OR ";
            }
	    $user_where .= $operator;
            $user_where .= $wpdb->prepare(" um.meta_key = %s AND um.meta_value LIKE %s ",$capability_column,'%administrator%');

            $sSearch = isset($_REQUEST['sSearch']) ? trim(sanitize_text_field($_REQUEST['sSearch'])) : '';
            $filter_plan_left_join_qur = "";
            if(!empty($sSearch) && !empty($filter_plan_id))
            {
                $filter_plan_left_join_qur = " LEFT JOIN `{$usermeta_table}` ump ON ump.user_id = u.ID ";
            }

            $sel_administrator = "SELECT u.ID FROM `{$user_table}` u LEFT JOIN `{$usermeta_table}` um ON um.user_id = u.ID $filter_plan_left_join_qur $user_where GROUP BY u.ID";
            $row = $wpdb->get_results($sel_administrator); //phpcs:ignore --Reason $sel_administrator is a table name
            $admin_users = array();
            if (!empty($row)) {
                foreach ($row as $key => $admin) {
                    array_push($admin_users, $admin->ID);
                }
            }
            $admin_user_where = ' WHERE 1=1 ';
            //$admin_user_where .= " AND u.ID NOT IN({$admin_users}) ";
            $admin_users = $exclude_admins      = array_unique( $admin_users );
            $user_args['exclude'] = $admin_users; //phpcs:ignore
            if(!empty($admin_users))
            {
                $admin_placeholders = ' AND u.ID NOT IN (';
            $admin_placeholders .= rtrim( str_repeat( '%s,', count( $admin_users ) ), ',' );
            $admin_placeholders .= ')';
            array_unshift( $admin_users, $admin_placeholders );
            $admin_user_where .= call_user_func_array(array( $wpdb, 'prepare' ), $admin_users );
            }
            $admin_user_join = "";
            if (is_multisite()) {
                $admin_user_join = " LEFT JOIN `{$usermeta_table}` um ON u.ID = um.user_id ";
                $admin_user_where .= $wpdb->prepare(" AND um.meta_key = %s ",$capability_column);
            }
            $exclude_admin = "SELECT COUNT(*) as total_users FROM `{$user_table}` u {$admin_user_join} {$admin_user_where} ";
            $excluded_admin = $wpdb->get_results($exclude_admin); //phpcs:ignore --Reason $exclude_admin is a predefined query
            $user_args['exclude'] = $admin_users;
            $total_before_filter = (isset($excluded_admin[0]->total_users) && $excluded_admin[0]->total_users != '') ? $excluded_admin[0]->total_users : 0; //phpcs:ignore
            $filterPlanArr = array();
            $meta_query_args = array();
            $mq = 0;
            if (!empty($filter_plan_id)) {
                $filterPlanArr = explode(',', $filter_plan_id);
                if (!empty($filterPlanArr) && !in_array('0', $filterPlanArr) && !in_array('no_plan', $filterPlanArr)) {
                    
                }
            }

            $sOrder = "";
            $sorting_ord = isset($_REQUEST['sSortDir_0']) ? sanitize_text_field($_REQUEST['sSortDir_0']) : 'desc';
            $sorting_ord = strtolower($sorting_ord);
            $sorting_col = (isset($_REQUEST['iSortCol_0']) && $_REQUEST['iSortCol_0'] > 0) ? intval($_REQUEST['iSortCol_0']) : 2;
            if ( ( isset($_REQUEST['iSortCol_0']) && $_REQUEST['iSortCol_0'] == 0 ) || ( 'asc'!=$sorting_ord && 'desc'!=$sorting_ord ) ) {
                $sorting_ord = 'desc';
            }

            $arm_multiple_membership_list_show = (!empty($_REQUEST['arm_multiple_membership_list_show']) && $_REQUEST['arm_multiple_membership_list_show'] != '0') ? sanitize_text_field( $_REQUEST['arm_multiple_membership_list_show'] ) : '';

            if(intval($sorting_col) == 13) {
                $orderby = "user_registered";
            }
            else {
            	if($is_multiple_membership_feature->isMultipleMembershipFeature){ 
                    if(intval($sorting_col)<=6)
                    {
                        $orderby = $data_columns[(intval($sorting_col) - 2 )]['data'];
                    }
                    if(intval($sorting_col)>6 && intval($sorting_col)<=8)
                    {
               	        $orderby = $data_columns[(intval($sorting_col) - 1 )]['data'];
                    }
                    if(intval($sorting_col) >8 && intval($sorting_col)>=9)
                    {
                        $orderby = $data_columns[(intval($sorting_col) )]['data'];
                    }
                }
                else
                {
                    $orderby = $data_columns[(intval($sorting_col) - 1)]['data'];
                }
               	
            }
            $org_orderby = "";
            if(in_array($orderby, array("first_name", "last_name"))) {
                $org_orderby = $orderby;
            }
            $user_args['orderby'] = $orderby;
            $user_args['order'] = $sorting_ord;
            $ordered_by_query = false;
            $user_table_columns = array("ID", "user_login", "user_email", "user_url", "user_registered", "display_name", "arm_primary_status");
            if (in_array($orderby, $user_table_columns)) {
                $ordered_by_query = true;
            }
            else {
                $orderby = 'um.meta_value';
                $ordered_by_query = true;
            }

            // GET ALL PLANS IDS AND NAMES
            $plan_query = $wpdb->prepare("SELECT arm_subscription_plan_id as plan_id,`arm_subscription_plan_name` as plan_name FROM " . $ARMember->tbl_arm_subscription_plans . " WHERE `arm_subscription_plan_is_delete`=%d ORDER BY arm_subscription_plan_id",'0'); //phpcs:ignore --Reason $ARMember->tbl_arm_subscription_plans is a table name
            $plan_result = $wpdb->get_results($plan_query);//phpcs:ignore --Reason $plan_query is a predefined query
            $plan_total = !empty($plan_result) ? count($plan_result) : 0;

            $filter_plan_search = "";
            $filter_ids = array();
            $filter_payment_mode_search = "";
            $arm_multiple_plan_id_condition = "";
            $join_arm_user_table = "";
            if (!empty($filter_plan_id)) {

                $arm_meta_search_alias_val = "armp";
                $join_arm_user_table .= " LEFT JOIN `{$arm_user_table}` armp ON armp.arm_user_id = u.ID ";

                $filter_ids = explode(',', $filter_plan_id);
                $filter_new_ids = implode("','", $filter_ids);
                if($is_multiple_membership_feature->isMultipleMembershipFeature || !empty( $arm_pay_per_post_feature->isPayPerPostFeature ) )
                {
                    if($plan_total>1)
                    {
                        $arm_multiple_plan_id_condition .= " OR ";
                        for($plan_i=1; $plan_i<=$plan_total; $plan_i++)
                        {
                            $arm_multiple_plan_id_condition .= " ({$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%i:".$plan_i.";i:" . implode("%' OR {$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%i:".$plan_i.";i:", $filter_ids) . ";%') ";
                            if($plan_i!=$plan_total)
                            {
                                $arm_multiple_plan_id_condition .= " OR ";
                            }
                        }
                    }
                    //$arm_multiple_plan_id_condition = " OR ({$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%i:1;i:" . implode("%' OR {$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%i:1;i:", $filter_ids) . ";%') OR ({$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%i:2;i:" . implode("%' OR {$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%i:2;i:", $filter_ids) . ";%') OR ({$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%i:3;i:" . implode("%' OR {$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%i:3;i:", $filter_ids) . ";%') OR ({$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%i:4;i:" . implode("%' OR {$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%i:4;i:", $filter_ids) . ";%') OR ({$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%i:5;i:" . implode("%' OR {$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%i:5;i:", $filter_ids) . ";%')";
                }
                $arm_plan_id_condition = " ( ({$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%\"" . implode("\"%' OR {$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%\"", $filter_ids) . "\"%' ) OR (({$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%i:0;i:" . implode("%' OR {$arm_meta_search_alias_val}.arm_user_plan_ids LIKE '%i:0;i:", $filter_ids) . ";%') {$arm_multiple_plan_id_condition} ) ) ";
                $filter_plan_search = " AND ({$arm_plan_id_condition})";
            }
            $search_params = '';
            if ($sSearch != '') 
            {
                $arm_search_user_meta_key_where = '';

                if(!empty($filter_meta_field_key)){

                    $arm_user_keys = array('user_login', 'user_email', 'display_name', 'user_url');
                    if(in_array($filter_meta_field_key, $arm_user_keys)){
                        $arm_search_user_meta_key_where .= " (u.".$filter_meta_field_key." LIKE '%{$sSearch}%') ";
                    }else{
                        $arm_search_user_meta_key_where .= $wpdb->prepare(" (um.meta_key = %s AND um.meta_value LIKE %s)  ",$filter_meta_field_key,'%'.$sSearch.'%');
                    }
                    $search_params = " AND ($arm_search_user_meta_key_where )";
                }else{
                    foreach ($user_meta_keys as $arm_um_key => $arm_um_val) 
                    {
                        $arm_user_keys = array('user_login', 'user_email', 'display_name', 'user_url');
                        if(!in_array($arm_um_key, $arm_user_keys))
                        {
                            $arm_search_user_meta_key_where .= " (um.meta_key = '".$arm_um_key."' AND um.meta_value LIKE '%{$sSearch}%') OR ";
                        }   
                    }
				$search_params = $wpdb->prepare(" AND ( u.user_login LIKE %s OR u.user_email LIKE %s OR u.display_name LIKE %s OR (um.meta_key = %s AND um.meta_value LIKE %s) OR (um.meta_key = %s AND um.meta_value LIKE %s) OR (um.meta_key = %s AND um.meta_value LIKE %s) )",'%'.$sSearch.'%','%'.$sSearch.'%','%'.$sSearch.'%','first_name','%'.$sSearch.'%','last_name','%'.$sSearch.'%',$capability_column,'%'.$sSearch.'%');
                }    
                
                
            }
            $admin_placeholders = 'u.ID NOT IN (';
            $admin_placeholders .= rtrim( str_repeat( '%s,', count( $exclude_admins ) ), ',' );
            $admin_placeholders .= ')';
            array_unshift( $exclude_admins, $admin_placeholders );
            $search_where = "";
            if ($filter_plan_search == '' && $search_params == '' && $filter_payment_mode_search == '') {
                $exclude_admins = call_user_func_array(array( $wpdb, 'prepare' ), $exclude_admins );
                $search_where = " WHERE ".$exclude_admins;
            } else {
                $exclude_admins = call_user_func_array(array( $wpdb, 'prepare' ), $exclude_admins );
                $search_where = " WHERE ".$exclude_admins." ".$filter_plan_search." ".$filter_payment_mode_search." ".$search_params;
            }

            if (is_multisite()) {
                if ($sSearch == '' && $filter_plan_search == '' && $filter_payment_mode_search == '') {
                    $search_where .= $wpdb->prepare(" AND um.meta_key = %s",$capability_column);
                } else {
                    $search_where .= $wpdb->prepare("AND um.user_id IN (SELECT `user_id` FROM `".$usermeta_table."` WHERE 1=1 AND `meta_key` = %s)",$capability_column); //phpcs:ignore --Reason $usermeta_table is a table name
                }
            }

            if ($orderby == 'arm_primary_status') {
                $join_arm_user_table .= " LEFT JOIN `{$arm_user_table}` armu ON armu.arm_user_id = u.ID ";
            }

            $join_on = "um.user_id = u.ID";
            if($org_orderby != "") {
                $join_on = "(um.user_id = u.ID AND um.meta_key = '{$org_orderby}')";
            }
            else {
                $join_on = "um.user_id = u.ID";
            }

            $join_arm_usermeta_table = "";
            $join_for_status = "";
            if($filter_status_id != '') {
                    
                $is_suspended = false;

                $search_where .= " AND (";

                if(strpos($filter_status_id, "5") !== false) {
                    $is_suspended = true;
                    $filter_status_id = substr($filter_status_id, 0, -2);
                    $join_for_status = " LEFT JOIN `{$usermeta_table}` um1 ON um1.user_id = u.ID ";
                    $search_where .= $wpdb->prepare(" (armu.arm_user_suspended_plan_ids != %s AND armu.arm_user_suspended_plan_ids != %s)",'a:0:{}','');
                }
                if(strpos($filter_status_id, "6") !== false) {

                    $is_suspended = false;
                    $filter_status_id = substr($filter_status_id, 0, -2);
                    $search_where .= $wpdb->prepare("( (armu.arm_user_plan_ids != %s AND armu.arm_user_plan_ids IS NOT NULL AND armu.arm_user_plan_ids !=%s) AND (armu.arm_user_suspended_plan_ids = %s OR armu.arm_user_suspended_plan_ids = %s or armu.arm_user_suspended_plan_ids IS NULL) ) ",'a:0:{}','','','a:0:{}');
                }

                if($filter_status_id != '')
                {
                    $search_where .= "(armu.arm_primary_status IN ({$filter_status_id}))";
                }
                
                $search_where .= " ) ";

                if ($orderby != 'arm_primary_status') {
                    $join_arm_user_table .= " LEFT JOIN `{$arm_user_table}` armu ON armu.arm_user_id = u.ID ";
                }
                    
            }
         
            $search_query1 = "SELECT u.ID FROM `{$user_table}` u LEFT JOIN `{$usermeta_table}` um ON {$join_on} {$join_for_status} {$filter_plan_left_join_qur} {$join_arm_user_table} {$join_arm_usermeta_table} {$search_where}";

            $mycounterquery = "SELECT count(DISTINCT(u.ID)) as total_users FROM `{$user_table}` u LEFT JOIN `{$usermeta_table}` um ON {$join_on} {$join_for_status} {$filter_plan_left_join_qur} {$join_arm_user_table} {$join_arm_usermeta_table} {$search_where}";

            if($search_params == '' && (!empty($orderby) && $orderby == 'arm_primary_status'))
            {
                $search_query1 = "SELECT u.ID FROM `{$user_table}` u {$join_for_status} {$filter_plan_left_join_qur} {$join_arm_user_table} {$join_arm_usermeta_table} {$search_where}";

                $mycounterquery = "SELECT count(DISTINCT(u.ID)) as total_users FROM `{$user_table}` u {$join_for_status} {$filter_plan_left_join_qur} {$join_arm_user_table} {$join_arm_usermeta_table} {$search_where}";
            }
            $search_query = $search_query1." GROUP BY u.ID";

            $tmp_user_query1 = $wpdb->get_results($search_query); //phpcs:ignore --Reason $search_query is a table name
            
            
            $tmp_user_query = $wpdb->get_row($mycounterquery);//phpcs:ignore --Reason $search_query is a predefined query
            $total_after_filter = (!empty($tmp_user_query->total_users)) ? $tmp_user_query->total_users : 0;

            $after_filter_args = $user_args;
            $user_args['offset'] = intval($user_offset);
            $user_args['number'] = intval($user_number);
            $order_by_qry = "";
            if ($ordered_by_query) {
                if($orderby == "arm_primary_status") 
                {
                    $orderby = "armu.".$orderby;
                }
                $order_by_qry = " ORDER BY " . $orderby . " " . $sorting_ord;
            }
            if (!empty($arm_multiple_membership_list_show)) 
            {
                $arm_multiple_membership_user_ids = '';
                foreach ($tmp_user_query1 as $gusers) {
                    $plan_ids = get_user_meta($gusers->ID, 'arm_user_plan_ids', true);
                    $post_ids = get_user_meta($gusers->ID, 'arm_user_post_ids', true);

                    if (!empty($plan_ids) && is_array($plan_ids)) 
                    {
                        $arm_plan_ids_count = count($plan_ids);

                        if($arm_pay_per_post_feature->isPayPerPostFeature)
                        {
                            $arm_plan_counter = 0;
                            foreach($plan_ids as $key => $value)
                            {
                                if(is_array( $post_ids ) && !array_key_exists($value, $post_ids))
                                {
                                    $arm_plan_counter++;
                                }
                            }

                            if($arm_plan_counter > 1 && $arm_multiple_membership_list_show=='2')
                            {
                                $arm_multiple_membership_user_ids .= $gusers->ID.',';    
                            }
                            else if($arm_plan_counter == 1 && $arm_multiple_membership_list_show=='1')
                            {
                                $arm_multiple_membership_user_ids .= $gusers->ID.',';    
                            }
                        }
                        else
                        {
                            if($arm_plan_ids_count > 1 && $arm_multiple_membership_list_show=='2'){
                                $arm_multiple_membership_user_ids .= $gusers->ID.',';
                            }
                            else if($arm_plan_ids_count==1 && $arm_multiple_membership_list_show=='1')
                            {
                                $arm_multiple_membership_user_ids .= $gusers->ID.',';
                            }
                        }
                    }
                }

                if(!empty($arm_multiple_membership_user_ids))
                {
                    $arm_multiple_membership_user_ids = rtrim($arm_multiple_membership_user_ids,',');
                }
                else
                {
                    $arm_multiple_membership_user_ids = 0;
                }
                $mm_user_id = explode(',',$arm_multiple_membership_user_ids);
                $mm_user_id_admin_placeholders = 'AND u.ID IN (';
				$mm_user_id_admin_placeholders .= rtrim( str_repeat( '%s,', count( $mm_user_id ) ), ',' );
				$mm_user_id_admin_placeholders .= ')';

				array_unshift( $mm_user_id, $mm_user_id_admin_placeholders );

				// $user_where .= ' AND u.ID NOT IN (' . implode( ',', $super_admin_ids ) . ')';
				$user_where = call_user_func_array(array( $wpdb, 'prepare' ), $mm_user_id );


                // $search_query = $search_query1." AND u.ID IN($arm_multiple_membership_user_ids) GROUP BY u.ID";
                $search_query = $search_query1." ".$user_where." GROUP BY u.ID";
                    
                $tmp_query = $search_query." {$order_by_qry} ";
                $form_result = $wpdb->get_results($tmp_query); //phpcs:ignore --Reason $tmp_query is a query name
                $total_after_filter = (!empty($form_result)) ? count($form_result) : 0;

                $tmp_query = $search_query." {$order_by_qry} LIMIT {$user_offset},{$user_number}";
                $form_result = $wpdb->get_results($tmp_query);//phpcs:ignore --Reason $tmp_query is a query name
            }
            else
            {
                $tmp_query = $search_query . "{$order_by_qry} LIMIT {$user_offset},{$user_number}";
                $form_result = $wpdb->get_results($tmp_query);//phpcs:ignore --Reason $tmp_query is a query name
                
                $total_after_filter = (!empty($tmp_user_query1)) ? count($tmp_user_query1) : 0;
		

            }
            
            $plan_array = array();
            foreach ($plan_result as $key => $plan)
            {
                $plan_array[$plan->plan_id] = $plan->plan_name;
            }
            
            if (!empty($form_result)) {
                if (!empty($payment_mode_ids)) {
                    if (!empty($filter_ids)) {
                        foreach ($form_result as $key => $gusers) {
                            $plan_ids = get_user_meta($gusers->ID, 'arm_user_plan_ids', true);
                            if (!empty($plan_ids) && is_array($plan_ids)) {
                                $user_array = array_intersect($plan_ids, $filter_ids);
                                if (empty($user_array)) {
                                    unset($form_result[$key]);
                                } else {
                                    $user_payment_mode = array();
                                    foreach ($plan_ids as $pid) {
                                        $planData = get_user_meta($gusers->ID, 'arm_user_plan_' . $pid, true);
                                        if (!empty($planData)) {
                                            $user_payment_mode[] = isset($planData['arm_payment_mode']) ? $planData['arm_payment_mode'] : '';
                                        }
                                    }
                                    $user_payment_mode_intersect = array_intersect($payment_mode_ids, $user_payment_mode);
                                    if (empty($user_payment_mode_intersect)) {
                                        unset($form_result[$key]);
                                    }
                                }
                            } else {
                                unset($form_result[$key]);
                            }
                        }
                    } else {
                        foreach ($form_result as $key => $gusers) {
                            $plan_ids = get_user_meta($gusers->ID, 'arm_user_plan_ids', true);
                            if (!empty($plan_ids) && is_array($plan_ids)) {
                                $user_payment_mode = array();
                                foreach ($plan_ids as $pid) {
                                    $planData = get_user_meta($gusers->ID, 'arm_user_plan_' . $pid, true);
                                    if (!empty($planData)) {
                                        $user_payment_mode[] = isset($planData['arm_payment_mode']) ? $planData['arm_payment_mode'] : '';
                                    }
                                }
                                $user_payment_mode_intersect = array_intersect($payment_mode_ids, $user_payment_mode);
                                if (empty($user_payment_mode_intersect)) {
                                    unset($form_result[$key]);
                                }
                            } else {
                                unset($form_result[$key]);
                            }
                        }
                    }
                } else {
                    if (!empty($filter_ids)) {
                        foreach ($form_result as $key => $gusers) {
                            $plan_ids = get_user_meta($gusers->ID, 'arm_user_plan_ids', true);
                            if (!empty($plan_ids) && is_array($plan_ids)) {
                                $user_array = array_intersect($plan_ids, $filter_ids);
                                if (empty($user_array)) {
                                    unset($form_result[$key]);
                                }
                            } else {
                                unset($form_result[$key]);
                            }
                        }
                    }
                }
            }
            
            $grid_data = array();
            $ai = 0;
            foreach ($form_result as $gusers) {
                $auser = new WP_User($gusers->ID);
                $userID = $auser->ID;
                $userPlanID = get_user_meta($userID, 'arm_user_plan_ids', true);
                $userFormID = get_user_meta($userID, 'arm_form_id', true);
                
                $user_all_status = arm_get_all_member_status($userID);

                $primary_status = $user_all_status['arm_primary_status'];
                $secondary_status = $user_all_status['arm_secondary_status'];
				
                if (in_array('no_plan', $filterPlanArr) && !empty($userPlanID)) {
                    continue;
                }

                if (user_can($userID, 'administrator')) {
                    //continue;
                }

                $userPlanIDs = get_user_meta($userID, 'arm_user_plan_ids', true);
                $userPlanIDs = (isset($userPlanIDs) && !empty($userPlanIDs)) ? $userPlanIDs : array();

                $arm_all_user_plans = $userPlanIDs;
                $arm_future_user_plans = get_user_meta($userID, 'arm_user_future_plan_ids', true);
                if (!empty($arm_future_user_plans)) {
                    $arm_all_user_plans = array_merge($userPlanIDs, $arm_future_user_plans);
                }

                if($arm_pay_per_post_feature->isPayPerPostFeature)
                {
                    $postIDs = get_user_meta($userID, 'arm_user_post_ids', true);
                    if(!empty($postIDs))
                    {
                        foreach($arm_all_user_plans as $arm_plan_keys => $arm_plan_vals)
                        {
                            if(!empty($postIDs[$arm_plan_vals]))
                            {
                                unset($arm_all_user_plans[$arm_plan_keys]);
                            }
                        }
                    }
                }

                $arm_all_user_plans = apply_filters('arm_modify_plan_ids_externally', $arm_all_user_plans, $userID);

                $userSuspendedPlanIDs = get_user_meta($userID, 'arm_user_suspended_plan_ids', true);
                $userSuspendedPlanIDs = (isset($userSuspendedPlanIDs) && !empty($userSuspendedPlanIDs)) ? $userSuspendedPlanIDs : array();

                /* if ($is_multiple_membership_feature->isMultipleMembershipFeature) {
                    if (!empty($arm_all_user_plans)) {
                        $grid_data[$ai][0] = "<div class='arm_show_user_more_plans' id='arm_show_user_more_plans_" . esc_attr($userID) . "' data-id='" . esc_attr($userID) . "'></div>";
                    } else {
                        $grid_data[$ai][0] = "";
                    }
                } */
                $grid_data[$ai][0] = "";
                $edit_link = admin_url('admin.php?page=' . $arm_slugs->manage_members . '&action=edit_member&id=' . $userID);
                $view_link = admin_url('admin.php?page=' . $arm_slugs->manage_members . '&action=view_member&id=' . $userID);
                if ((get_current_user_id() != $userID)) {
                        $grid_data[$ai][0] = "<input id=\"cb-item-action-".esc_attr($userID)."\" class=\"chkstanard\" type=\"checkbox\" value=\"".esc_attr($userID)."\" name=\"item-action[]\">";
                } else {
                        $grid_data[$ai][0] = "<input id=\"cb-item-action-".esc_attr($userID)."\" class=\"chkstanard\" type=\"checkbox\" disabled=\"disabled\">";
                }

                if (!empty($grid_columns)) {

                    $n = 1;               
                    foreach($grid_columns as $key => $title) {
                        switch ($key) {
                            case 'ID':
                                $grid_data[$ai][$n] = $userID;
                                break;
                            case 'user_login':
                                $grid_data[$ai][$n] = $auser->user_login;
                                break;
                            case 'user_email':
                                $grid_data[$ai][$n] = '<a class="arm_openpreview_popup" href="javascript:void(0)" data-id="' . esc_attr($userID) . '">' . stripslashes($auser->user_email) . '</a>';
                                break;
                            case 'display_name':
                                $grid_data[$ai][$n] = $auser->display_name;
                                break;
                            case 'first_name':
                            case 'last_name':
                                $grid_data[$ai][$n] = get_user_meta($userID, $key, true);
                                break;
                            case 'roles':
                                if (!empty($auser->roles)) {
                                    $role_name = array();
                                    if (is_array($auser->roles)) {
                                        foreach ($auser->roles as $role) {
                                            if (isset($user_roles[$role])) {
                                                $role_name[] = $user_roles[$role]['name'];
                                            }
                                        }
                                    } else {
                                        $u_role = array_shift($auser->roles);
                                        if (isset($user_roles[$u_role])) {
                                            $role_name[] = $user_roles[$u_role]['name'];
                                        }
                                    }
                                }
                                reset($auser->roles);
                                if (!empty($role_name)) {
                                    $grid_data[$ai][$n] = implode(', ', $role_name);
                                } else {
                                    $grid_data[$ai][$n] = '-';
                                }
                                break;
                            /* case 'arm_member_type':
                                $memberTypeText = $arm_members_class->arm_get_member_type_text($userID);
                                $grid_data[$ai][$n] = $memberTypeText;
                                break; */
                            case 'arm_user_plan_ids':
                                $plan_names = array();
                                $subscription_effective_from = array();
                                $arm_user_plans = '';
                                if (!empty($arm_all_user_plans) && is_array($arm_all_user_plans)) {
                                    $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                                    foreach ($arm_all_user_plans as $userPlanID) {
                                        $userPlanDatameta = get_user_meta($userID, 'arm_user_plan_' . $userPlanID, true);

                                        $arm_paid_plan_condition = "";
                                        if($arm_pay_per_post_feature->isPayPerPostFeature)
                                        {
                                            $arm_paid_plan_condition = (!empty($userPlanDatameta) && empty($userPlanDatameta['arm_current_plan_detail']['arm_subscription_plan_post_id']) );

                                            if(!empty($userPlanDatameta['arm_current_plan_detail']['arm_subscription_plan_post_id']) && $userPlanDatameta['arm_current_plan_detail']['arm_subscription_plan_post_id'] != 0)
                                            {
                                                //Code for delete user plan id which is associated with paid post.
                                                if (($key = array_search($userPlanID, $arm_all_user_plans)) !== false)
                                                {
                                                    unset($arm_all_user_plans[$key]);
                                                }
                                            }
                                        }
                                        else
                                        {
                                            $arm_paid_plan_condition = (!empty($userPlanDatameta));
                                        }

                                        if($arm_paid_plan_condition)
                                        {
                                            $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                                            $plan_data = shortcode_atts($defaultPlanData, $userPlanDatameta);

                                            //$plan_data = get_user_meta($userID, 'arm_user_plan_'.$userPlanID, true);
                                            $subscription_effective_from_date = $plan_data['arm_subscr_effective'];
                                            $change_plan_to = $plan_data['arm_change_plan_to'];

                                            //$plan_names[$userPlanID] = $arm_subscription_plans->arm_get_plan_name_by_id($userPlanID);
                                            $plan_names[$userPlanID] = isset($plan_array[$userPlanID]) ? $plan_array[$userPlanID] : '';
                                            if($plan_data['arm_cencelled_plan'] == "yes" && !$is_multiple_membership_feature->isMultipleMembershipFeature)
                                            {
                                                $plan_names[$userPlanID] .= " <span style='color: red;'>( ".esc_html__('Cancelled', 'ARMember')." )</span>";
                                            }
                                            $subscription_effective_from[] = array('arm_subscr_effective' => $subscription_effective_from_date, 'arm_change_plan_to' => $change_plan_to);
                                        }
                                    }
                                }

                                //if(count($userPlanIDs) > 1){
                                if ($is_multiple_membership_feature->isMultipleMembershipFeature) {
                                    $arm_user_plans = '<div class="arm_min_width_120">';
                                    $arm_user_plans .= "<a href='javascript:void(0)'  id='arm_show_user_more_plans_" . $userID . "' data-id='" . $userID . "'>";

                                    if (!empty($arm_all_user_plans) && is_array($arm_all_user_plans) ) {
                                        foreach ($arm_all_user_plans as $plan_id) {
                                            $plan_color_id = ($plan_id > 10) ? intval($plan_id / 10) : $plan_id;
                                            $plan_color_id = ($plan_color_id > 10) ? intval($plan_color_id / 10) : $plan_color_id;
                                            if( !empty( $plan_names[$plan_id] ) ) {
                                                $arm_plan_title = $plan_names[$plan_id];

                                                if($plan_data['arm_cencelled_plan'] == "yes" && $is_multiple_membership_feature->isMultipleMembershipFeature)
                                                {
                                                    $arm_plan_title .= '<span style="color: red;">('.esc_html__('Cancelled', 'ARMember').' )</span>';
                                                }

                                                $arm_user_plans .= "<span class='armhelptip arm_user_plan_circle arm_user_plan_" . $plan_color_id . "' title='" . stripslashes_deep($arm_plan_title) . "' >";
                                                $plan_name = str_replace('-', '', stripslashes_deep($plan_names[$plan_id]));
                                                $words = explode(" ", $plan_name);
                                                $plan_name = '';
                                                foreach ($words as $w) {
                                                    $w = preg_replace('/[^A-Za-z0-9\-]/', '', $w);
                                                    $plan_name .= mb_substr($w, 0, 1, 'utf-8');
                                                }
                                                $plan_name = strtoupper($plan_name);
                                                $arm_user_plans .= substr($plan_name, 0, 2);
                                                $arm_user_plans .= "</span>";
                                            }
                                        }
                                    }
                                    $arm_user_plans .= "</a></div>";
                                    $grid_data[$ai][$n] = $arm_user_plans;
                                } else {
                                    $plan_name = (!empty($plan_names)) ? implode(',', $plan_names) : '';
                                    $grid_data[$ai][$n] = '<span class="arm_user_plan_' . $userID . '">' . stripslashes_deep($plan_name) . '</span>';
                                    if(!empty($arm_all_user_plans) && is_array($arm_all_user_plans)) {
                                        foreach($arm_all_user_plans as $arm_all_user_plan)
                                        {
                                            if (in_array($arm_all_user_plan, $userSuspendedPlanIDs)) {
                                                $grid_data[$ai][$n] .= '<br/><span style="color: red;">(' . esc_html__('Suspended', 'ARMember') . ')</span>';
                                            }
                                        }
                                    }

                                    if (!empty($subscription_effective_from)) {
                                        foreach ($subscription_effective_from as $subscription_effective) {
                                            $subscr_effective = $subscription_effective['arm_subscr_effective'];
                                            $change_plan = $subscription_effective['arm_change_plan_to'];
                                            //$change_plan_name = $arm_subscription_plans->arm_get_plan_name_by_id($change_plan);
                                            
                                            $change_plan_name = isset($plan_array[$change_plan]) ? $plan_array[$change_plan] : array();
                                            
                                            
                                            if (!empty($change_plan) && $subscr_effective > strtotime($nowDate)) {
                                                $grid_data[$ai][$n] .= '<div>' . esc_html($change_plan_name) . '<br/> (' . esc_html__('Effective from', 'ARMember') . ' ' . date_i18n($date_format, $subscr_effective) . ')</div>';
                                            }
                                        }
                                    }
                                }

                                break;
                            case 'arm_user_paid_plans':
                                    if($arm_pay_per_post_feature->isPayPerPostFeature)
                                    {
                                        $arm_paid_post_counter = 0;
                                        $arm_user_post_ids = get_user_meta($userID, 'arm_user_post_ids', true);
                    					if(empty($arm_user_post_ids) )
                    					{
                    						$arm_user_post_ids = array();
                    					}
                                        $arm_user_plan_ids = get_user_meta($userID, 'arm_user_plan_ids', true);
                    					if(empty($arm_user_plan_ids) )
                    					{
                    						$arm_user_plan_ids = array();
                    					}
                    					if(!empty( $arm_user_post_ids ))
                    					{
	                                        foreach($arm_user_plan_ids as $arm_user_plan_id_val)
	                                        {
	                                            if(array_key_exists($arm_user_plan_id_val, $arm_user_post_ids))
	                                            {
	                                                $arm_paid_post_counter++;
	                                            }
	                                        } 
					}                                       
					$grid_data[$ai][$n] = '<a class="arm_open_paid_plan_popup" href="javascript:void(0)" data-id="' . esc_attr($userID) . '">' . esc_html($arm_paid_post_counter) . '</a>';
                                    }
                                    break;
                            case 'arm_primary_status':
                                //$grid_data[$ai][$n] = $arm_members_class->armGetMemberStatusText($userID);
                                $grid_data[$ai][$n] = $arm_members_class->armGetMemberStatusText_print($primary_status,$secondary_status);
                                break;
                            case 'user_registered':
                                $grid_data[$ai][$n] = date_i18n($date_format, strtotime($auser->$key));
                                break;
                            case 'avatar':
                                $user_avatar = get_user_meta($userID, $key, true);
                                $grid_data[$ai][$n] = get_avatar($userID, 43);
                                break;
                            case 'user_url':
                                $grid_data[$ai][$n] = $auser->user_url;
                                break;
                            case 'paid_with':
                                $arm_paid_withs = array();
                                if (!empty($userPlanIDs) && is_array($userPlanIDs)) {
                                    foreach ($userPlanIDs as $userPlanID) {
                                        $planData = get_user_meta($userID, 'arm_user_plan_' . $userPlanID, true);
                                        if( empty($planData['arm_current_plan_detail']['arm_subscription_plan_post_id']) )
                                        {
                                            $using_gateway = isset($planData['arm_user_gateway']) ? $planData['arm_user_gateway'] : '' ;
                                            if (!empty($using_gateway)) {
                                                $arm_paid_withs[] = $arm_payment_gateways->arm_gateway_name_by_key($using_gateway);
                                            }
                                        }
                                    }
                                }

                                if (!empty($arm_paid_withs)) {
                                    $arm_paid_with = implode(",", $arm_paid_withs);
                                } else {
                                    $arm_paid_with = "-";
                                }
                                $grid_data[$ai][$n] = $arm_paid_with;
                                break;
                            case 'action_btn':
                                $gridAction = "<div class='arm_grid_action_btn_container'>";
                                if ((get_current_user_id() != $userID) && !is_super_admin($userID)) {
                                    if ($primary_status == '3') {
                                        $activation_key = get_user_meta($userID, 'arm_user_activation_key', true);

                                        if (!empty($activation_key)) {
                                            $gridAction .= "<a href='javascript:void(0)' onclick='showResendVerifyBoxCallback(".esc_attr($userID).");'><img src='" . MEMBERSHIP_IMAGES_URL . "/resend_mail_icon.png' class='armhelptip' title='" . esc_attr__('Resend Verification Email', 'ARMember') . "' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/resend_mail_icon_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/resend_mail_icon.png';\" /></a>";
                                            $gridAction .= "<div class='arm_confirm_box arm_resend_verify_box arm_resend_verify_box_".esc_attr($userID)."' id='arm_resend_verify_box_".esc_attr($userID)."'>";
                                            $gridAction .= "<div class='arm_confirm_box_body'>";
                                            $gridAction .= "<div class='arm_confirm_box_arrow'></div>";
                                            $gridAction .= "<div class='arm_confirm_box_text'>";
                                            $gridAction .= esc_html__('Are you sure you want to resend verification email?', 'ARMember');
                                            $gridAction .= "</div>";
                                            $gridAction .= "<div class='arm_confirm_box_btn_container'>";
                                            $gridAction .= "<button type='button' class='arm_confirm_box_btn armemailaddbtn arm_resend_verify_email_ok_btn' data-item_id='".esc_attr($userID)."'>" . esc_html__('Ok', 'ARMember') . "</button>";
                                            $gridAction .= "<button type='button' class='arm_confirm_box_btn armcancel' onclick='hideConfirmBoxCallback();'>" . esc_html__('Cancel', 'ARMember') . "</button>";
                                            $gridAction .= "</div>";
                                            $gridAction .= "</div>";
                                            $gridAction .= "</div>";
                                        }
                                    }
                                }
                                $gridAction .= "<a class='arm_openpreview arm_openpreview_popup armhelptip' href='javascript:void(0)' data-id='" . esc_attr($userID) . "' title='" . esc_attr__('View Detail', 'ARMember') . "'><img src='" . MEMBERSHIP_IMAGES_URL . "/grid_preview.png' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_preview_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_preview.png';\" /></a>";
                                if (current_user_can('arm_manage_members')) {
                                    $edit_link = admin_url('admin.php?page=' . $arm_slugs->manage_members . '&action=edit_member&id=' . $userID);
                                    $gridAction .= "<a href='" . esc_url($edit_link) . "' class='armhelptip' title='" . esc_attr__('Edit Member', 'ARMember') . "' ><img src='" . MEMBERSHIP_IMAGES_URL . "/grid_edit.png' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_edit_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_edit.png';\" /></a>";
                                }
                                if ((get_current_user_id() != $userID) && !is_super_admin($userID)) {
                                    $gridAction .= "<a href='javascript:void(0)' onclick='showChangeStatusBoxCallback(".esc_attr($userID).");'><img src='" . MEMBERSHIP_IMAGES_URL . "/change_status_icon.png' class='armhelptip' title='" . esc_attr__('Change Status', 'ARMember') . "' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/change_status_icon_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/change_status_icon.png';\" /></a>";
                                    $gridAction .= "<div class='arm_confirm_box arm_change_status_box arm_change_status_box_".esc_attr($userID)."' id='arm_change_status_box_".esc_attr($userID)."' >";
                                    $gridAction .= "<div class='arm_confirm_box_body'>";
                                    $gridAction .= "<div class='arm_confirm_box_arrow'></div>";
                                    $gridAction .= "<div class='arm_confirm_box_text'>";
                                    if ($primary_status == '1') {

                                        $gridAction .= "<input type='hidden' id='arm_new_assigned_status_".esc_attr($userID)."' data-id='".esc_attr($userID)."' value=''>";
                                        $gridAction .= "<dl class='arm_selectbox column_level_dd arm_member_form_dropdown arm_margin_top_10' >";
                                        $gridAction .= "<dt><span> " . esc_html__('Select Status', 'ARMember') . " </span><input type='text' style='display:none;' value='' class='arm_autocomplete'/><i class='armfa armfa-caret-down armfa-lg'></i></dt>";
                                        $gridAction .= "<dd><ul data-id='arm_new_assigned_status_".esc_attr($userID)."'>";
                                        $gridAction .= '<li data-label="' . esc_attr__('Select Status', 'ARMember') . '" data-value="">' . esc_html__('Select Status', 'ARMember') . '</li>';
                                        if ($primary_status != 1) {
                                            $gridAction .= '<li data-label="' . esc_attr__('Activate', 'ARMember') . '" data-value="1">' . esc_html__('Activate', 'ARMember') . '</li>';
                                        }
                                        if (!in_array($primary_status, array(2, 4))) {
                                            $gridAction .= '<li data-label="' . esc_attr__('Inactivate', 'ARMember') . '" data-value="2">' . esc_html__('Inactivate', 'ARMember') . '</li>';
                                        }
                                        if ($primary_status != 4) {
                                            $gridAction .= '<li data-label="' . esc_attr__('Terminate', 'ARMember') . '" data-value="4">' . esc_html__('Terminate', 'ARMember') . '</li>';
                                        }$gridAction .= "</ul></dd>";
                                        $gridAction .= "</dl>";
                                    } else {

                                        // $gridAction .= esc_html__('Are you sure you want to active this member?', 'ARMember');
                                        $gridAction .= "<input type='hidden' id='arm_new_assigned_status_".esc_attr($userID)."' data-id='".esc_attr($userID)."' value='' class='arm_new_assigned_status' data-status='".esc_attr($primary_status)."'>";
                                        $gridAction .= "<dl class='arm_selectbox column_level_dd arm_member_form_dropdown arm_margin_top_10' >";
                                        $gridAction .= "<dt><span> " . esc_html__('Select Status', 'ARMember') . " </span><input type='text' style='display:none;' value='' class='arm_autocomplete'/><i class='armfa armfa-caret-down armfa-lg'></i></dt>";
                                        $gridAction .= "<dd><ul data-id='arm_new_assigned_status_".esc_attr($userID)."'>";

                                        $gridAction .= '<li data-label="' . esc_attr__('Select Status', 'ARMember') . '" data-value="">' . esc_html__('Select Status', 'ARMember') . '</li>';

                                        if ($primary_status != 1) {
                                            $gridAction .= '<li data-label="' . esc_attr__('Activate', 'ARMember') . '" data-value="1">' . esc_html__('Activate', 'ARMember') . '</li>';
                                        }
                                        if (!in_array($primary_status, array(2, 4))) {
                                            $gridAction .= '<li data-label="' . esc_attr__('Inactivate', 'ARMember') . '" data-value="2">' . esc_html__('Inactivate', 'ARMember') . '</li>';
                                        }
                                        if ($primary_status != 4) {
                                            $gridAction .= '<li data-label="' . esc_attr__('Terminate', 'ARMember') . '" data-value="4">' . esc_html__('Terminate', 'ARMember') . '</li>';
                                        }
                                        $gridAction .= "</ul></dd>";
                                        $gridAction .= "</dl>";
                                        if ($primary_status == '3') {
                                            $gridAction .= "<label style='display: none;' class='arm_notify_user_via_email arm_margin_top_10'>";
                                            $gridAction .= "<input type='checkbox' class='arm_icheckbox' id='arm_user_activate_check_".esc_attr($userID)."' value='1' checked='checked'>&nbsp;";
                                            $gridAction .= esc_html__('Notify user via email', 'ARMember');
                                            $gridAction .= "</label>";
                                        }
                                    }
                                    $gridAction .= "</div>";
                                    $gridAction .= "<div class='arm_confirm_box_btn_container'>";
                                    $gridAction .= "<button type='button' class='arm_confirm_box_btn armemailaddbtn arm_change_user_status_ok_btn' data-item_id='".esc_attr($userID)."' data-status='".esc_attr($primary_status)."'>" . esc_html__('Ok', 'ARMember') . "</button>";
                                    $gridAction .= "<button type='button' class='arm_confirm_box_btn armcancel' onclick='hideConfirmBoxCallback();'>" . esc_html__('Cancel', 'ARMember') . "</button>";
                                    $gridAction .= "</div>";
                                    $gridAction .= "</div>";
                                    $gridAction .= "</div>";
                                }

                                $gridAction .= "<a href='javascript:void(0)' onclick='arm_member_manage_plan(".esc_attr($userID).");' id='arm_manage_plan_" . esc_attr($userID) . "'><img src='" . MEMBERSHIP_IMAGES_URL . "/grid_manage_plan_icon.png' class='armhelptip' title='" . esc_attr__('Manage Plans', 'ARMember') . "' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_manage_plan_icon_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_manage_plan_icon.png';\" /></a>";

                                if (current_user_can('arm_manage_members') && (get_current_user_id() != $userID)) {
                                    if (is_multisite() && is_super_admin($userID)) {
                                        /* Hide delete button for Super Admins */
                                    } else {
                                        $gridAction .= "<a href='javascript:void(0)' onclick='showConfirmBoxCallback({$userID});'><img src='" . MEMBERSHIP_IMAGES_URL . "/grid_delete.png' class='armhelptip' title='" . esc_attr__('Delete', 'ARMember') . "' onmouseover=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_delete_hover.png';\" onmouseout=\"this.src='" . MEMBERSHIP_IMAGES_URL . "/grid_delete.png';\" /></a>";
                                        $gridAction .= $arm_global_settings->arm_get_confirm_box($userID, esc_html__("Are you sure you want to delete this member?", 'ARMember'), 'arm_member_delete_btn');
                                    }
                                }
                                $gridAction .= "</div>";
                                $grid_data[$ai][$n] = $gridAction;
                                break;
                            case 'profile_cover':
                                $grid_data[$ai][$n] = '';
                                $user_meta_detail = get_user_meta($userID, $key, true);
                                if($user_meta_detail !='')
                                {
                                    // $grid_data[$ai][$n] = $user_meta_detail;
                                    $upload_dir = wp_upload_dir();
                                    $upload_dirname = $upload_dir['basedir'];
                                    $exp_val = explode("/", $user_meta_detail);
                                    $filename = $exp_val[count($exp_val) - 1];
                                    
                                    if (file_exists($upload_dirname . "/armember/" . $filename)) {
                                        $file_extension = explode('.', $filename);
                                        $file_ext = $file_extension[count($file_extension) - 1];
                                        if (in_array($file_ext, array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tif', 'tiff'))) {
                                            $grid_data[$ai][$n] = '<img src="' . $user_meta_detail . '" width="100px" height="auto">';
                                        } else if (in_array($file_ext, array('pdf', 'exe'))) {
                                            $grid_data[$ai][$n] = '<img src="' . MEMBERSHIP_IMAGES_URL . '/document.png" >';
                                        } else if (in_array($file_ext, array('zip'))) {
                                            $grid_data[$ai][$n] = '<img src="' . MEMBERSHIP_IMAGES_URL . '/archive.png" >';
                                        } else {
                                            $grid_data[$ai][$n] = '<img src="' . MEMBERSHIP_IMAGES_URL . '/text.png" >';
                                        }
                                    }
                                }
                                break;
                            default:
                                $user_meta_detail = get_user_meta($userID, $key, true);
                                                            
                                $arm_form_id = get_user_meta($userID, 'arm_form_id', true);
                                $grid_data[$ai][$n] = '';

                                $data = isset($user_meta_keys[$key]) ? $user_meta_keys[$key] : '';

                                /* though we have again query for $data if $data is null than not display value */
                                if ($data != '') {
                                    $arm_form_field_option = maybe_unserialize($data);
                                    $arm_form_field_type = $arm_form_field_option['type'];
                                    if ($arm_form_field_type == 'file') {
                                        if($user_meta_detail != '')
                                        {
                                            $files_urls = explode(',',$user_meta_detail);
                                            if($files_urls > 0)
                                            {
                                                $grid_data[$ai][$n] = '';
                                                $upload_dir = wp_upload_dir();
                                                $upload_dirname = $upload_dir['basedir'];
                                                foreach($files_urls as $file_url)
                                                {
                                                    if ($file_url != '') {
                                                        $exp_val = explode("/", $file_url);
                                                        $filename = $exp_val[count($exp_val) - 1];
                                                        
                                                        if (file_exists($upload_dirname . "/armember/" . $filename)) {
                                                            $file_extension = explode('.', $filename);
                                                            $file_ext = $file_extension[count($file_extension) - 1];
                                                            if (in_array($file_ext, array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tif', 'tiff'))) {
                                                                $grid_data[$ai][$n] .= '<img src="' . $file_url . '" width="100px" height="auto">';
                                                            } else if (in_array($file_ext, array('pdf', 'exe'))) {
                                                                $grid_data[$ai][$n] .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/document.png" >';
                                                            } else if (in_array($file_ext, array('zip'))) {
                                                                $grid_data[$ai][$n] .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/archive.png" >';
                                                            } else {
                                                                $grid_data[$ai][$n] .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/text.png" >';
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    } else if ($arm_form_field_type == 'textarea') {
                                        $str = explode("\n", wordwrap($user_meta_detail, 70));
                                        $user_meta_detail = $str[0] . '...';
                                        $grid_data[$ai][$n] = $user_meta_detail;
                                    } else if (in_array($arm_form_field_type, array('radio', 'checkbox', 'select'))) {
                                        $main_array = array();
                                        $options = $arm_form_field_option['options'];
                                        $value_array = array();
                                        foreach ($options as $arm_key => $arm_val) {
                                            if (strpos($arm_val, ":") != false) {
                                                $exp_val = explode(":", $arm_val);
                                                $exp_val1 = $exp_val[1];
                                                $value_array[$exp_val[0]] = $exp_val[1];
                                            } else {
                                                $value_array[$arm_val] = $arm_val;
                                            }
                                        }
                                        $user_meta_detail = $ARMember->arm_array_trim($user_meta_detail);
                                        if (!empty($value_array)) {
                                            if (is_array($user_meta_detail)) {
                                                foreach ($user_meta_detail as $u) {
                                                    foreach ($value_array as $arm_key => $arm_val) {
                                                        if ($u == $arm_val) {
                                                            array_push($main_array, $arm_key);
                                                        }
                                                    }
                                                }
                                                $user_meta_detail = @implode(', ', $main_array);
                                                $grid_data[$ai][$n] = $user_meta_detail;
                                            } else {
                                                $exp_val = array();
                                                /*if (strpos($user_meta_detail, ",") != false) {
                                                    $exp_val = explode(",", $user_meta_detail);
                                                }*/
                                                if (!empty($exp_val)) {
                                                    foreach ($exp_val as $u) {
                                                        if (in_array($u, $value_array)) {
                                                            array_push($main_array, array_search($u, $value_array));
                                                        }
                                                    }
                                                    $user_meta_detail = @implode(', ', $main_array);
                                                    $grid_data[$ai][$n] = $user_meta_detail;
                                                } else {
                                                    if (in_array($user_meta_detail, $value_array)) {
                                                        $grid_data[$ai][$n] = array_search($user_meta_detail, $value_array);
                                                    } else {
                                                        $grid_data[$ai][$n] = $user_meta_detail;
                                                    }
                                                }
                                            }
                                        } else {
                                            if (is_array($user_meta_detail)) {
                                                $user_meta_detail = $ARMember->arm_array_trim($user_meta_detail);
                                                $user_meta_detail = @implode(', ', $user_meta_detail);
                                                $grid_data[$ai][$n] = $user_meta_detail;
                                            } else {
                                                $grid_data[$ai][$n] = $user_meta_detail;
                                            }
                                        }
                                    } else {
                                        if (is_array($user_meta_detail)) {
                                            $user_meta_detail = $ARMember->arm_array_trim($user_meta_detail);
                                            $user_meta_detail = @implode(', ', $user_meta_detail);
                                            $grid_data[$ai][$n] = $user_meta_detail;
                                        } else {
                                            $grid_data[$ai][$n] = $user_meta_detail;
                                        }
                                    }
                                }
                                break;
                        }
                        $n++;
                    }
                }
                $ai++;
            }

            $sEcho = isset($_REQUEST['sEcho']) ? intval($_REQUEST['sEcho']) : intval(10);
            $response = array(
                'sColumns' => implode(',', $grid_columns),
                'sEcho' => $sEcho,
                'iTotalRecords' => $total_before_filter, // Before Filtered Records
                'iTotalDisplayRecords' => $total_after_filter, // After Filter Records
                'aaData' => $grid_data,
            );
            echo json_encode($response);
            die();
        }

        function arm_new_plan_assigned_by_system($new_plan_id, $old_plan_id, $user_id, $is_plan_assigned = 0) {
            global $arm_subscription_plans, $arm_payment_gateways;
            $new_plan = new ARM_Plan($new_plan_id);
            if ($new_plan->is_recurring()) {
                $payment_mode = 'manual_subscription';

                $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, true);
                $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                $newPlanData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                $newPlanData['arm_payment_mode'] = 'manual_subscription';

                update_user_meta($user_id, 'arm_user_plan_' . $new_plan_id, $newPlanData);
            }
            $start_date=''; 
            $entry_id='';
            $arm_last_payment_status = 'success';
            $arm_subscription_plans->arm_update_user_subscription($user_id, $new_plan_id, 'system', false,  $arm_last_payment_status, $start_date, $entry_id, $is_plan_assigned);
            //delete_user_meta($user_id, 'arm_using_gateway_' . $old_plan_id);
            if (!($new_plan->is_free())) {
                $payment_mode = '';
                $new_plan_amount = 0;
                $currency = $arm_payment_gateways->arm_get_global_currency();
                $currency = !empty($currency) ? $currency : 'USD';
                $user_info = get_user_by('id', $user_id);
                $extraParam = array();
                $extraParam['plan_amount'] = $new_plan_amount;
                $extraParam['manual_by'] = 'Paid By system';
                $return_array = array();
                $return_array['arm_plan_id'] = $new_plan_id;
                $return_array['arm_payment_gateway'] = '';
                $return_array['arm_user_id'] = $user_id;
                $return_array['arm_first_name']= $user_info->first_name;
                $return_array['arm_last_name']=$user_info->last_name;
                $return_array['arm_payment_type'] = $new_plan->payment_type;
                $return_array['arm_token'] = '-';
                $return_array['payment_gateway'] = 'manual';
                $return_array['arm_payer_email'] = '';
                $return_array['arm_receiver_email'] = '';
                $return_array['arm_transaction_id'] = '-';
                $return_array['arm_transaction_payment_type'] = $new_plan->payment_type;
                $return_array['arm_transaction_status'] = 'completed';
                $return_array['arm_payment_mode'] = $payment_mode;
                $return_array['arm_payment_date'] = current_time( 'mysql' );
                $return_array['arm_amount'] = $new_plan_amount;
                $return_array['arm_currency'] = $currency;
                $return_array['arm_extra_vars'] = maybe_serialize($extraParam);
                $return_array['arm_is_trial'] = 0;
                $return_array['arm_created_date'] = current_time('mysql');
                $payment_log_id = $arm_payment_gateways->arm_save_payment_log($return_array);
            }
        }

        function arm_manual_update_user_data($user_id = 0, $plan_id = 0, $posted_data = array(), $plan_cycle = 0) {

            global $arm_payment_gateways, $ARMember, $arm_members_class, $arm_subscription_plans, $arm_global_settings, $arm_membership_setup, $arm_pay_per_post_feature;
            $general_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
            $arm_currency_decimal = isset($general_settings['arm_currency_decimal_digit']) ? $general_settings['arm_currency_decimal_digit'] : 0;

            $is_paid_post = (isset($posted_data['arm_paid_post_request']) && !empty($posted_data['arm_paid_post_request']) && ($posted_data['arm_paid_post_request'] == (bool)"1")) ? 1 : 0 ;
            $is_arm_gift_plan = (isset($posted_data['arm_gift_request']) && !empty($posted_data['arm_gift_request']) && ($posted_data['arm_gift_request'] == (bool)"1")) ? 1 : 0 ;


            // $plan_id = $posted_data['arm_user_plan'];
            // $planData = get_user_meta($user_id, 'arm_user_plan_'.$plan_id, true); 


            $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
            $planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
            $userPlanDatameta = !empty($planData) ? $planData : array();
            $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);


            $payment_mode = isset($posted_data['arm_selected_payment_mode']) ? $posted_data['arm_selected_payment_mode'] : 'manual_subscription';
            $payment_gateway = isset($posted_data['payment_gateway']) ? $posted_data['payment_gateway'] : 'manual';


            $start_time = $planData['arm_start_plan'];

            if ($start_time == '') {
                $start_time = strtotime(current_time('mysql'));
            }
            $current_time = strtotime(current_time('mysql'));
            //$plan = new ARM_Plan($plan_id);

            if ($start_time > $current_time) {
                $current_time = $start_time;
            }

            $planDetail = $planData['arm_current_plan_detail'];
            if (!empty($planDetail)) {
                $plan = new ARM_Plan(0);
                $plan->init((object) $planDetail);
            } else {
                $plan = new ARM_Plan($plan_id);
            }

            $total_occurence = isset($plan->options['recurring']['time']) ? $plan->options['recurring']['time'] : '';
            if ($total_occurence == 'infinite') {
                $total_occurence_actual = 1;
                $planData['arm_expire_plan'] = "";
            } else {
                $total_occurence_actual = $total_occurence;
            }

            $currency = $arm_payment_gateways->arm_get_global_currency();
            $currency = !empty($currency) ? $currency : 'USD';

            /*to check that tax is enable or not*/
            $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
            $general_settings = isset($all_global_settings['general_settings']) ? $all_global_settings['general_settings'] : array();
            $enable_tax = isset($general_settings['enable_tax']) ? $general_settings['enable_tax'] : 0;

            $tax_display_type = !empty($general_settings['arm_tax_include_exclude_flag']) ? $general_settings['arm_tax_include_exclude_flag'] : 0;


            $total_cycle_performed = 0;
            if ($plan->is_recurring()) {

                $is_trail_added = 0;
                while ($total_occurence_actual > 0) {

                    if ($start_time <= $current_time) {

                        $total_cycle_performed++;
                        $next_recurring_date = $arm_members_class->arm_get_next_due_date($user_id, $plan_id, false, $plan_cycle, $start_time);
                        $arm_plan_amount = 0;
                        $arm_extra_vars = array('manual_by'=>esc_html__('Paid By admin', 'ARMember'));
                        $plan_cycle_data = $plan->prepare_recurring_data($plan_cycle);
                        /*rpt_log : changes for trial period amount while recurring transaction done by admin.*/
                        $old_plan_ids = get_user_meta($user_id, 'arm_user_old_plan_id', true);
                        $old_plan = (isset($old_plan_ids) && !empty($old_plan_ids)) ? $old_plan_ids : array();

                        $arm_is_trial = '0';
                                                
                        if ($plan->has_trial_period()) {
                            if ( !empty($old_plan) && !in_array($plan_id, $old_plan) ) {
                                $total_cycle_performed = 1;
                            } else if( isset($posted_data['arm_plan_ids']) && $posted_data['arm_plan_ids'] != '' && $posted_data['arm_plan_ids'] != $plan_id ) {
                                $total_cycle_performed = 1;
                            } else {
                                if( isset($plan_cycle_data['trial']) && !empty($plan_cycle_data['trial']) && empty($is_trail_added)) {
                                    $is_trail_added = 1;
                                    $arm_plan_amount = isset($plan_cycle_data['trial']['amount']) ? $plan_cycle_data['trial']['amount'] : 0;
                                    $arm_is_trial = '1';
                                    $arm_extra_vars['trial'] = $plan_cycle_data['trial'];
                                    $arm_extra_vars['arm_is_trial'] = $arm_is_trial;
                                    $arm_extra_vars['paid_amount'] = sprintf("%.2f", $arm_plan_amount);
                                    $arm_extra_vars['plan_amount'] = $plan_cycle_data['amount'];
                                    $plan_start_date = empty($planData['arm_start_plan']) ? current_time('mysql') : date('Y-m-d H:i:s', $planData['arm_start_plan']);
                                    
                                    $start_date = "";
                                    
                                    if ( "D" == $plan->recurring_data['trial']['period'] ) {

                                        $start_date = date('Y-m-d H:i:s', strtotime($plan_start_date . " + ".$plan->recurring_data['trial']['interval']." days"));
                                    } else if ( "M" == $plan->recurring_data['trial']['period'] ) {

                                        $start_date = date('Y-m-d H:i:s', strtotime($plan_start_date . " + ".$plan->recurring_data['trial']['interval']." months"));
                                    } else if ( "Y" == $plan->recurring_data['trial']['period'] ) {
                                        
                                        $start_date = date('Y-m-d H:i:s', strtotime($plan_start_date . " + ".$plan->recurring_data['trial']['interval']." years"));
                                    }
                                    
                                    $start_date = strtotime($start_date);

                                    $planData['arm_is_trial_plan'] = $arm_is_trial;
                                    $planData['arm_trial_start'] = strtotime($plan_start_date);
                                    $planData['arm_start_plan'] = $start_date;
                                    $planData['arm_trial_end'] = $start_date;
                                    $total_cycle_performed = 0;
                                    $next_recurring_date = $start_date;

                                } else {
                                    $arm_plan_amount = $plan_cycle_data['amount'];
                                }    
                                
                            }    
                        } else{
                            $total_cycle_performed = 1;
                            $arm_plan_amount = $plan_cycle_data['amount'];
                        }
                        $return_array = array();
                        $plan_cycle_data_amount = str_replace(",", "", $plan_cycle_data['amount']);

                        
                        /*applying tax if paid by admin*/
                        if(1 == $enable_tax) {
                            $tax_amount = 0;
                            if(isset($plan_cycle_data['trial']['amount'])) {
                                $plan_cycle_data_amount = $plan_cycle_data['trial']['amount'];
                            }

                            $tax_type = $general_settings['tax_type'];
                            $country_tax_field = isset($general_settings['country_tax_field']) ? $general_settings['country_tax_field'] : '';

                            
                    
                            if('common_tax' == $tax_type) {
                                $tax_percentage = $general_settings['tax_amount'];                            

                            } else if('country_tax' == $tax_type) {
                                $country_tax_arr = (!empty($general_settings['arm_tax_country_name'])) ? maybe_unserialize($general_settings['arm_tax_country_name']) : array();
                                $country_tax_val_arr = (!empty($general_settings['arm_country_tax_val'])) ? maybe_unserialize($general_settings['arm_country_tax_val']) : array();
                                $country_default_tax = (!empty($general_settings['arm_country_tax_default_val'])) ? $general_settings['arm_country_tax_default_val'] : 0;
                                
                                if(!empty($posted_data) && isset($posted_data[$country_tax_field]) && in_array($posted_data[$country_tax_field], $country_tax_arr)) {
                                    $opt_index = array_search($posted_data[$country_tax_field], $country_tax_arr);
                                    $tax_percentage = $country_tax_val_arr[$opt_index];
                                } else {
                                    $tax_percentage = $country_default_tax;
                                }

                            }

                            $plan_cycle_data_amount = str_replace(",", "", $plan_cycle_data_amount);

                            if($tax_percentage > 0){

                                if( empty($tax_display_type))
                                {
                                    $tax_amount = ($plan_cycle_data_amount * $tax_percentage) / 100;
                                    $tax_amount = number_format((float)$tax_amount, $arm_currency_decimal, '.', '');
                                    $arm_extra_vars['plan_amount'] = number_format((float)$plan_cycle_data_amount,$arm_currency_decimal, '.', '');
                                    $plan_cycle_data_amount = $plan_cycle_data_amount+$tax_amount;
                                }
                                else
                                {
                                    $tax_amount = $plan_cycle_data_amount - ( $plan_cycle_data_amount /  ( ( $tax_percentage /100 )+ 1) ) ;
                                }
                                $arm_extra_vars['paid_amount'] = number_format((float)$plan_cycle_data_amount,$arm_currency_decimal, '.', '');
                            }
                            $arm_extra_vars['tax_amount'] = number_format((float)$tax_amount,$arm_currency_decimal, '.', '');
                            $arm_extra_vars['tax_percentage'] = number_format((float)$tax_percentage,$arm_currency_decimal, '.', '');
                            
                        }
                        $user_info = get_user_by('id', $user_id);
                
                        $return_array['arm_user_id'] = $user_id;
                        $return_array['arm_first_name']= $user_info->first_name;
                        $return_array['arm_last_name']=$user_info->last_name;
                        $return_array['arm_plan_id'] = $plan->ID;
                        $return_array['arm_payment_gateway'] = 'manual';
                        $return_array['arm_payment_type'] = $plan->payment_type;
                        $return_array['arm_token'] = '-';
                        $return_array['arm_payer_email'] = '';
                        $return_array['arm_receiver_email'] = '';
                        $return_array['arm_transaction_id'] = '-';
                        $return_array['arm_transaction_payment_type'] = $plan->payment_type;
                        $return_array['arm_transaction_status'] = 'completed';
                        $return_array['arm_payment_mode'] = 'manual_subscription';
                        $return_array['arm_payment_date'] = date('Y-m-d H:i:s', $start_time);
                        $return_array['arm_amount'] = number_format((float)$plan_cycle_data_amount,$arm_currency_decimal, '.', '');
                        $return_array['arm_currency'] = $currency;
                        $return_array['arm_coupon_code'] = '';
                        $return_array['arm_extra_vars'] = maybe_serialize($arm_extra_vars);

                        if($is_paid_post)
                        {
                            $is_post_plan = $arm_pay_per_post_feature->arm_get_post_from_plan_id($plan->ID);

                            if(!empty($is_post_plan) && !empty($is_post_plan[0]['arm_subscription_plan_post_id']))
                            {
                                $return_array['arm_is_post_payment'] = 1;

                                //Count `arm_user_plan` array and get last element from array
                                $arm_user_post_id = end($posted_data['arm_user_plan']);
                                $return_array['arm_paid_post_id'] = $arm_user_post_id;
                            }
                        }

                        $return_array = apply_filters('arm_modify_return_data_for_manual_update_user_data', $return_array, $plan->ID);

                        $return_array['arm_created_date'] = date('Y-m-d H:i:s', $start_time);
                        $payment_log_id = $arm_payment_gateways->arm_save_payment_log($return_array);

                        if (!isset($next_recurring_date) || $next_recurring_date == '') {
                            break;
                        }

                        $start_time = $next_recurring_date;
                    } else {
                        break;
                    }

                    if ($total_occurence == 'infinite') {
                        $total_occurence_actual++;
                    } else {
                        $total_occurence_actual--;
                    }
                }

                $planData['arm_completed_recurring'] = $total_cycle_performed;
                $planData['arm_next_due_payment'] = $start_time;
                if( !isset($planData['arm_payment_cycle']) )
                {
            		$planData['arm_payment_cycle'] = 0;
                }
                update_user_meta($user_id, 'arm_user_plan_' . $plan_id, $planData);
            } else if ($plan->is_lifetime() || $plan->type == 'paid_finite') {
                $plan_cycle_data_amount = str_replace(",", "", $plan->amount);

                $arm_extra_vars = array();
                $arm_extra_vars['manual_by'] = esc_html__('Paid By admin', 'ARMember');

                /*applying tax if paid by admin*/
                $tax_amount = 0;
                if(1 == $enable_tax) {
                    $tax_type = isset($general_settings['tax_type']) ? $general_settings['tax_type'] : '';
                    $country_tax_field = isset($general_settings['country_tax_field']) ? $general_settings['country_tax_field'] : '';
                    
                    if('common_tax' == $tax_type) {
                        $tax_percentage = $general_settings['tax_amount'];                            

                    } else if('country_tax' == $tax_type) {
                        $country_tax_arr = (!empty($general_settings['arm_tax_country_name'])) ? maybe_unserialize($general_settings['arm_tax_country_name']) : array();
                        $country_tax_val_arr = (!empty($general_settings['arm_country_tax_val'])) ? maybe_unserialize($general_settings['arm_country_tax_val']) : array();
                        $country_default_tax = (!empty($general_settings['arm_country_tax_default_val'])) ? $general_settings['arm_country_tax_default_val'] : 0;
                        
                        if(!empty($posted_data) && isset($posted_data[$country_tax_field]) && in_array($posted_data[$country_tax_field], $country_tax_arr)) {
                            $opt_index = array_search($posted_data[$country_tax_field], $country_tax_arr);
                            $tax_percentage = $country_tax_val_arr[$opt_index];
                        } else {
                            $tax_percentage = $country_default_tax;
                        }
                    }
                    
                    if($tax_percentage > 0){

                        if( empty($tax_display_type))
                        {
                            $tax_amount = ($plan_cycle_data_amount * $tax_percentage) / 100;
                            $tax_amount = number_format((float)$tax_amount, $arm_currency_decimal, '.', '');
                            $arm_extra_vars['plan_amount'] = number_format((float)$plan_cycle_data_amount,$arm_currency_decimal, '.', '');
                            $plan_cycle_data_amount = $plan_cycle_data_amount+$tax_amount;
                        }
                        else
                        {
                            $tax_amount = $plan_cycle_data_amount - ($plan_cycle_data_amount / ( ( $tax_percentage /100 )+ 1)) ;
                        }
                        $arm_extra_vars['paid_amount'] = number_format((float)$plan_cycle_data_amount,$arm_currency_decimal, '.', '');
                    }
                    $arm_extra_vars['tax_amount'] = number_format((float)$tax_amount, $arm_currency_decimal, '.', '');
                    $arm_extra_vars['tax_percentage'] = $tax_percentage;
                    
                }

                $return_array = array();
                $user_info = get_user_by('id', $user_id);
                $return_array['arm_user_id'] = $user_id;
                $return_array['arm_first_name']= $user_info->first_name;
                $return_array['arm_last_name']=$user_info->last_name;
                $return_array['arm_plan_id'] = $plan->ID;
                $return_array['arm_payment_gateway'] = 'manual';
                $return_array['arm_payment_type'] = $plan->payment_type;
                $return_array['arm_token'] = '-';
                $return_array['arm_payer_email'] = '';
                $return_array['arm_receiver_email'] = '';
                $return_array['arm_transaction_id'] = '-';
                $return_array['arm_transaction_payment_type'] = $plan->payment_type;
                $return_array['arm_transaction_status'] = 'completed';
                $return_array['arm_payment_mode'] = '';
                $return_array['arm_payment_date'] = date('Y-m-d H:i:s', $start_time);
                $return_array['arm_amount'] = number_format((float)$plan_cycle_data_amount,$arm_currency_decimal, '.', '');
                $return_array['arm_currency'] = $currency;
                $return_array['arm_coupon_code'] = '';

                if($is_paid_post)
                {
                    $is_post_plan = $arm_pay_per_post_feature->arm_get_post_from_plan_id($plan->ID);

                    if(!empty($is_post_plan) && !empty($is_post_plan[0]['arm_subscription_plan_post_id']))
                    {
                        $return_array['arm_is_post_payment'] = 1;

                        //Count `arm_user_plan` array and get last element from array
                        $arm_user_post_id = end($posted_data['arm_user_plan']);
                        $return_array['arm_paid_post_id'] = $arm_user_post_id;
                    }
                }

                $return_array = apply_filters('arm_modify_return_data_for_manual_update_user_data', $return_array, $plan->ID);
                
                $return_array['arm_extra_vars'] = maybe_serialize($arm_extra_vars);
                $return_array['arm_created_date'] = date('Y-m-d H:i:s', $start_time);
                
                $payment_log_id = $arm_payment_gateways->arm_save_payment_log($return_array);
            }

            if(!empty($arm_pay_per_post_feature->isPayPerPostFeature))
            {
                $arm_pay_per_post_feature->arm_assign_paid_post_to_user($user_id,$plan->ID);
            }
        }

        function arm_add_manual_user_payment($user_id = 0, $plan_id = 0, $member_data=array()) {
            global $arm_payment_gateways, $arm_global_settings;

            $currency = $arm_payment_gateways->arm_get_global_currency();
            $currency = !empty($currency) ? $currency : 'USD';
            $general_settings = isset($arm_global_settings->global_settings) ? $arm_global_settings->global_settings : array();
            $arm_currency_decimal = isset($general_settings['arm_currency_decimal_digit']) ? $general_settings['arm_currency_decimal_digit'] : 0;
            $plan_amount = $tax_amount = $tax_percentage = $plan_cycle_data_amount = 0;

            $tax_display_type = !empty($general_settings['arm_tax_include_exclude_flag']) ? $general_settings['arm_tax_include_exclude_flag'] : 0;
            
            if($user_id > 0 && $plan_id > 0) {
                $user_plan_detail = get_user_meta($user_id, 'arm_user_plan_'.$plan_id);
                $user_plan_detail = !empty($user_plan_detail) ? maybe_unserialize($user_plan_detail) : array();
                if(!empty($user_plan_detail)) {
                    foreach ($user_plan_detail as $key => $user_plan) {
                        $plan_amount = $user_plan['arm_current_plan_detail']['arm_subscription_plan_amount'];
                    }            
                }
            }

            $all_global_settings = $arm_global_settings->arm_get_all_global_settings();
            $general_settings = isset($all_global_settings['general_settings']) ? $all_global_settings['general_settings'] : array();
            $enable_tax = isset($general_settings['enable_tax']) ? $general_settings['enable_tax'] : 0;

            $arm_extra_vars = array();
            $arm_extra_vars['manual_by'] = esc_html__('Paid By admin', 'ARMember');
            
            $plan_cycle_data_amount = $plan_amount;
            if(1 == $enable_tax) {
                $tax_percentage = 0;
                $tax_type = isset($general_settings['tax_type']) ? $general_settings['tax_type'] : '';
                $country_tax_field = isset($general_settings['country_tax_field']) ? $general_settings['country_tax_field'] : '';
                
                
                if('common_tax' == $tax_type) {
                    $tax_percentage = $general_settings['tax_amount'];                            

                } else if('country_tax' == $tax_type) {
                    $country_tax_arr = (!empty($general_settings['arm_tax_country_name'])) ? maybe_unserialize($general_settings['arm_tax_country_name']) : array();
                    $country_tax_val_arr = (!empty($general_settings['arm_country_tax_val'])) ? maybe_unserialize($general_settings['arm_country_tax_val']) : array();
                    $country_default_tax = (!empty($general_settings['arm_country_tax_default_val'])) ? $general_settings['arm_country_tax_default_val'] : 0;
                    
                    $member_data_country = isset($member_data['country']) ? $member_data['country'] : 0;
                    if(!empty($member_data_country) && in_array($member_data_country, $country_tax_arr)) {
                        $opt_index = array_search($member_data_country, $country_tax_arr);
                        $tax_percentage = $country_tax_val_arr[$opt_index];
                    } else {
                        $tax_percentage = $country_default_tax;
                    }
                }

                if($tax_percentage > 0){
                    $tax_amount = ($plan_amount * $tax_percentage) / 100;
                    $tax_amount = number_format((float)$tax_amount, $arm_currency_decimal, '.', '');
                    $arm_extra_vars['plan_amount'] = number_format((float)$plan_amount,$arm_currency_decimal);
                    $plan_cycle_data_amount = $plan_amount + $tax_amount;
                    $arm_extra_vars['paid_amount'] = number_format((float)$plan_cycle_data_amount,$arm_currency_decimal);
                }
                if($tax_percentage > 0){

                    if( empty($tax_display_type))
                    {
                        $tax_amount = ($plan_amount * $tax_percentage) / 100;
                        $tax_amount = number_format((float)$tax_amount, $arm_currency_decimal, '.', '');
                        $arm_extra_vars['plan_amount'] = number_format((float)$plan_cycle_data_amount,$arm_currency_decimal, '.', '');
                        $plan_cycle_data_amount = $plan_amount + $tax_amount;
                    }
                    else
                    {
                        $tax_amount = $plan_amount - ($plan_amount / ( ( $tax_percentage /100 ) + 1)) ;
                    }
                    $arm_extra_vars['paid_amount'] = number_format((float)$plan_amount,$arm_currency_decimal, '.', '');
                }
                $arm_extra_vars['tax_amount'] = number_format((float)$tax_amount,$arm_currency_decimal);
                $arm_extra_vars['tax_percentage'] =  number_format((float)$tax_percentage,$arm_currency_decimal);
            }
            $payment_type = 'subscription';
            $transaction_status = 'completed';
            $payment_mode = 'manual_subscription';
            if(isset($member_data['payment_type']) && "manual" == $member_data['payment_type']){
                    $payment_type = 'one_time';
                $transaction_status = 'success';
                $payment_mode = '';
            }

            //$planData = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
            $return_array = array();
            $return_array['arm_user_id'] = $user_id;
            $return_array['arm_plan_id'] = $plan_id;
            $return_array['arm_first_name'] = isset($member_data['first_name']) ? $member_data['first_name'] : '';
            $return_array['arm_last_name'] = isset($member_data['last_name']) ? $member_data['last_name'] : '';
            $return_array['arm_payment_gateway'] = 'manual';
            $return_array['arm_payment_type'] = $payment_type;
            $return_array['arm_token'] = '-';
            $return_array['arm_payer_email'] = '';
            $return_array['arm_receiver_email'] = '';
            $return_array['arm_transaction_id'] = '-';
            $return_array['arm_transaction_payment_type'] = $payment_type;
            $return_array['arm_transaction_status'] = $transaction_status;
            $return_array['arm_payment_mode'] = 'manual_subscription';
            $return_array['arm_payment_date'] = current_time( 'mysql' );
            $return_array['arm_amount'] = $plan_cycle_data_amount;
            $return_array['arm_currency'] = $currency;
            $return_array['arm_coupon_code'] = '';
            $return_array['arm_extra_vars'] = maybe_serialize($arm_extra_vars);
            $return_array['arm_created_date'] = current_time('mysql');
	        $payment_log_id = $arm_payment_gateways->arm_save_payment_log($return_array);
        }

        

        function arm_get_failed_login_users() {
            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $user_table = $wpdb->users;
            $historyRecords = $wpdb->get_results("SELECT u.ID, u.user_login, l.arm_user_id FROM `{$user_table}` u RIGHT JOIN `" . $ARMember->tbl_arm_fail_attempts . "` l ON u.ID = l.arm_user_id group by u.ID ORDER BY u.ID DESC", ARRAY_A);//phpcs:ignore --Reason $user_table and $ARMember->tbl_arm_fail_attempts are table names. No need to prepare there is no where clause in query
            if (!empty($historyRecords)) {
                return $historyRecords;
            }
        }

        function arm_get_failed_login_attempts_history($current_page = 1, $perPage = 10) {

            global $wp, $wpdb, $ARMember, $arm_global_settings;
            $user_table = $wpdb->users;

            $historyHtml = '';

            $perPage = (!empty($perPage) && is_numeric($perPage)) ? $perPage : 10;
            $offset = 0;

            $wp_date_time_format = $arm_global_settings->arm_get_wp_date_time_format();
            if (!empty($current_page) && $current_page > 1) {
                $offset = ($current_page - 1) * $perPage;
            }
            $historyLimit = (!empty($perPage)) ? " LIMIT $offset, $perPage " : "";

            $totalRecord = $wpdb->get_var("SELECT COUNT(`arm_fail_attempts_ip`) FROM `" . $ARMember->tbl_arm_fail_attempts . "`"); //phpcs:ignore --Reason $ARMember->tbl_arm_fail_attempts is a table name. No need to Prepare bcz no WHERE Clause in Query

            $historyRecords = $wpdb->get_results("SELECT u.user_login, l.arm_user_id, l.arm_fail_attempts_ip, l.arm_fail_attempts_datetime FROM `{$user_table}` u RIGHT JOIN `" . $ARMember->tbl_arm_fail_attempts . "` l ON u.ID = l.arm_user_id ORDER BY l.arm_fail_attempts_datetime DESC {$historyLimit}", ARRAY_A); //phpcs:ignore --Reason $ARMember->tbl_arm_fail_attempts is a table name. No need to Prepare bcz no WHERE Clause in Query

            $historyHtml .= '<div class="arm_failed_attempt_loginhistory_wrapper">';
            $historyHtml .= '<table class="form-table arm_failed_login_history_table arm_margin_0" width="100%" >';
            $historyHtml .= '<tr>';
            $historyHtml .= '<td>' . esc_html__('Username', 'ARMember') . '</td>';
            $historyHtml .= '<td>' . esc_html__('Logged In Date', 'ARMember') . '</td>';
            $historyHtml .= '<td>' . esc_html__('Logged In IP', 'ARMember') . '</td>';
            $historyHtml .= '</tr>';
            if (!empty($historyRecords)) {
                $i = 0;
                foreach ($historyRecords as $mh) {
                    $i++;
                    $arm_failed_attempt_user_login = ($mh['user_login'] != '') ? $mh['user_login'] : '-';
                    $arm_failed_attempt_login_date = date_create($mh['arm_fail_attempts_datetime']);

                    $historyHtml .= '<tr class="arm_failed_login_history_data all_user_login_history_tr">';
                    $historyHtml .= '<td>' . esc_html($arm_failed_attempt_user_login) . '</td>';
                    $historyHtml .= '<td>' . esc_html(date_i18n($wp_date_time_format, strtotime($mh['arm_fail_attempts_datetime']))). '</td>';
                    $historyHtml .= '<td>' . $mh['arm_fail_attempts_ip'] . '</td>';
                    $historyHtml .= '</tr>';
                }
            } else {
                $historyHtml .= '<tr class="arm_failed_login_history_data">';
                $historyHtml .= '<td colspan="6" class="arm_text_align_center">' . esc_html__('No Failed Attempt Login History Found.', 'ARMember') . '</td>';
                $historyHtml .= '</tr>';
            }

            $historyHtml .= '</table>';
            $historyHtml .= '<div class="arm_failed_attempt_loginhistory_pagination_block">';
            $historyPaging = $arm_global_settings->arm_get_paging_links($current_page, $totalRecord, $perPage, '');
            $historyHtml .= '<div class="arm_failed_attempt_loginhistory_paging_container">' . $historyPaging . '</div>'; //phpcs:ignore
            $historyHtml .= '</div>';
            $historyHtml .= '</div>';

            return $historyHtml;
        }

        function arm_failed_attempt_login_history_paging_action() {
            global $wp, $wpdb, $ARMember, $arm_global_settings, $arm_payment_gateways, $arm_capabilities_global;
            if (isset($_POST['action']) && $_POST['action'] == 'arm_failed_attempt_login_history_paging_action') { //phpcs:ignore

                $current_page = isset($_POST['page']) ? intval( $_POST['page'] ) : 1; //phpcs:ignore 
                $per_page = isset($_POST['per_page']) ? intval( $_POST['per_page'] ) : 10; //phpcs:ignore 
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_block_settings'], '1',1); //phpcs:ignore --Reason:Verifying nonce
                echo $this->arm_get_failed_login_attempts_history($current_page, $per_page); //phpcs:ignore
            }
            exit;
        }
        function get_arm_member_list_func(){
            if(isset($_REQUEST['action']) && $_REQUEST['action']=='get_arm_member_list') {
                $text = sanitize_text_field($_REQUEST['txt']); //phpcs:ignore
                $type = !empty($_REQUEST['type'])? intval($_REQUEST['type']):0;
                
                $arm_display_admin_user=!empty($_REQUEST['arm_display_admin_user']) ? intval($_REQUEST['arm_display_admin_user']) : 0;

                global $wp, $wpdb, $arm_errors, $ARMember, $arm_members_class, $arm_member_forms, $arm_global_settings;
                $ARMember->arm_check_user_cap('',0,1);
                $user_table = $wpdb->users;
                $usermeta_table = $wpdb->usermeta;
                $capability_column = $wpdb->get_blog_prefix($GLOBALS['blog_id']) . 'capabilities';
                if($arm_display_admin_user==1){
                    $super_admin_ids = array();
                    if (is_multisite()) {
                        $super_admin = get_super_admins();
                        if (!empty($super_admin)) {
                            foreach ($super_admin as $skey => $sadmin) {
                                if ($sadmin != '') {
                                    $user_obj = get_user_by('login', $sadmin);
                                    if ($user_obj->ID != '') {
                                        $super_admin_ids[] = $user_obj->ID;
                                    }
                                }
                            }
                        }
                    }
                }    
                $user_where = " WHERE ";
                $user_where .= " (user_login LIKE '".$text."%' OR `user_email` LIKE '".$text."%')";
                if($arm_display_admin_user==1){
                    if (!empty($super_admin_ids)) {
                        $super_admin_placeholders = 'AND u.ID NOT IN (';
                        $super_admin_placeholders .= rtrim( str_repeat( '%s,', count( $super_admin_ids ) ), ',' );
                        $super_admin_placeholders .= ')';

                        array_unshift( $super_admin_ids, $super_admin_placeholders );

                        // $user_where .= ' AND u.ID NOT IN (' . implode( ',', $super_admin_ids ) . ')';
                        $user_where .= call_user_func_array(array( $wpdb, 'prepare' ), $super_admin_ids );
                    }
                }    
                
                $admin_user_where = $wpdb->prepare(" um.meta_key = %s AND um.meta_value LIKE %s ",$capability_column,"%administrator%");
				$row         = $wpdb->get_results( "SELECT u.ID FROM `".$user_table."` u LEFT JOIN `".$usermeta_table."` um ON um.user_id = u.ID WHERE ".$admin_user_where." GROUP BY u.ID" );//phpcs:ignore --Reason $user_table and $usermeta_table are  table name
				$admin_users = array();
				if ( ! empty( $row ) ) {
					foreach ( $row as $key => $admin ) {
						array_push( $admin_users, $admin->ID );
					}
				}
				$admin_users       = array_unique( $admin_users );
				// $admin_users       = implode( ',', $admin_users );
				$admin_placeholders = ' AND u.ID NOT IN (';
				$admin_placeholders .= rtrim( str_repeat( '%s,', count( $admin_users ) ), ',' );
				$admin_placeholders .= ')';	
				// $admin_users       = implode( ',', $admin_users );

				array_unshift( $admin_users, $admin_placeholders );
				
				$user_where .= call_user_func_array(array( $wpdb, 'prepare' ), $admin_users );
                $user_join = "";
                if (!empty($type) && in_array($type, array(1, 2, 3))) {
                    $user_join = " INNER JOIN {$ARMember->tbl_arm_members} arm1 ON u.ID = arm1.arm_user_id";
                    $user_where .= $wpdb->prepare(" AND arm1.arm_primary_status=%s ",$type);
                }

                $user_fields = "u.ID,u.user_email,u.user_registered,u.user_login";
                $user_group_by = " GROUP BY u.ID ";
                $user_order_by = " ORDER BY u.user_registered DESC limit 0,10";
                
                $user_query = "SELECT {$user_fields} FROM `{$user_table}` u LEFT JOIN `{$usermeta_table}` um ON u.ID = um.user_id {$user_join} {$user_where} {$user_group_by} {$user_order_by} ";
                $users_details = $wpdb->get_results($user_query); //phpcs:ignore --Reason $user_query is a prepared Query

                $all_members = $users_details;
                
                $user_list_html = "";
                $drData = array();
                if(!empty($all_members)) {
                    foreach ( $all_members as $user ) {
                        
                        $user_list_html .= '<li data-id="'.esc_attr($user->ID).'">' . $user->user_login . '</li>';
                        $drData[] = array(
                                    'id' => $user->ID,
                                    'value' => $user->user_login,
                                    'label' => $user->user_login . ' ('.$user->user_email.')',
                                );
                    }
                }
                $response = array('status' => 'success', 'data' => $drData);
                echo json_encode($response);
                die;
            }    
        }


        function arm_member_view_paid_plan_detail()
        {
            $user_id = intval($_REQUEST['member_id']); //phpcs:ignore
            if (!empty($user_id) && $user_id != 0) {
        ?>
                <div class="arm_member_paid_post_popup popup_wrapper arm_import_user_list_detail_popup_wrapper">
                    <form method="GET" id="arm_member_manage_plan_user_form" class="arm_admin_form">
                    <div class="popup_wrapper_inner">
                        <div class="popup_header">
                            <span class="popup_close_btn arm_popup_close_btn arm_member_view_detail_close_btn"></span>
                            <span class="add_rule_content"><?php esc_html_e('Manage Paid Post','ARMember' );?> <span class="arm_manage_plans_username" id="arm_manage_plans_username"></span></span>
                            <input type="hidden" id="arm_delete_paid_post_plan" value="<?php echo esc_attr($user_id); ?>" />
                            <input type="hidden" id="arm_add_paid_post_plan" value="<?php echo esc_attr($user_id); ?>" />
                        </div>
                        <div class="popup_content_text arm_member_view_detail_popup_text arm_member_manage_post_detail_popup_text arm_text_align_center arm_padding_0" style="height: auto;">
                            <?php
                                global $arm_global_settings, $ARMember, $arm_capabilities_global, $arm_pay_per_post_feature;

                                $arm_common_date_format = $arm_global_settings->arm_check_common_date_format(get_option('date_format'));
                                
                                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1);
                                $date_format = $arm_global_settings->arm_get_wp_date_format();
                                $user_name = '';
                                $arm_user_info = get_userdata($user_id);
                                $user_name = $arm_user_info->user_login;
                                $u_roles = $arm_user_info->roles;
                                global $arm_global_settings, $arm_subscription_plans, $is_multiple_membership_feature;
                                $return = '';
                                if (!empty($user_id)) {

                                    /*$all_subscription_plans = $arm_subscription_plans->arm_get_plans_data();

                                    $planIDs = get_user_meta($user_id, 'arm_user_plan_ids', true);
                                    $planIDs = !empty($planIDs) ? $planIDs : array();

                                    $postIDs = get_user_meta($user_id, 'arm_user_post_ids', true);
                                    $postIDs = !empty($postIDs) ? $postIDs : array();

                                    foreach($planIDs as $plan_key => $plan_value)
                                    {
                                        if(!array_key_exists($plan_value, $postID))
                                        {
                                            unset($plan_key);
                                        }
                                    }


                                    $user_future_plan_ids = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
                                    $user_future_plan_ids = !empty($user_future_plan_ids) ? $user_future_plan_ids : array();

                                    $futurePlanIDs = get_user_meta($user_id, 'arm_user_future_plan_ids', true);
                                    $futurePlanIDs = !empty($futurePlanIDs) ? $futurePlanIDs : array();

                                    $all_plan_ids = array();
                                    if (!empty($all_subscription_plans)) {
                                        foreach ($all_subscription_plans as $p) {
                                            if($p['arm_subscription_plan_post_id'] != 0)
                                            {
                                                $all_plan_ids[] = $p['arm_subscription_plan_id'];
                                            }
                                        }
                                    }


                                    $plansLists = '<li data-label="' . esc_html__('Select Post', 'ARMember') . '" data-value="">' . esc_html__('Select Post', 'ARMember') . '</li>';
                                    if (!empty($all_subscription_plans)) {
                                        foreach ($all_subscription_plans as $p) {
                                            if($p['arm_subscription_plan_post_id'] != 0 && (!in_array($p['arm_subscription_plan_id'], $planIDs)))
                                            {
                                                $p_id = $p['arm_subscription_plan_id'];
                                                $plansLists .= '<li data-label="' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '" data-value="' . $p_id . '">' . stripslashes(esc_attr($p['arm_subscription_plan_name'])) . '</li>';
                                            }
                                        }
                                    }

                                    
                                    $return .= '<div class="arm_add_new_item_box arm_add_new_plan"><a id="arm_add_plan_to_user" class="greensavebtn arm_save_btn" href="javascript:void(0)" ><img align="absmiddle" src="' . MEMBERSHIP_IMAGES_URL . '/add_new_icon.png"><span> ' . esc_html__('Add Post', 'ARMember') . '</span></a></div>';


                                    $return .= '<div class="popup_content_text arm_add_plan" style="text-align:center; display:none;">';
                                    $return .= '<div class="arm_edit_plan_wrapper" style="position: relative; margin-top: 10px;">';
                                    $return .= '<span class="arm_edit_plan_lbl">' . esc_html__('Select Post', 'ARMember') . '*</span> ';
                                    $return .= '<div class="arm_edit_field">';
                                    
                                        $return .= '<input type="hidden" class="arm_user_plan_change_input arm_user_plan_change_input_get_cycle" name="arm_user_plan[]" id="arm_user_plan" value="" data-manage-plan-grid="1"/>';
                                    
                                    $return .= '<dl class="arm_selectbox column_level_dd arm_member_form_dropdown" style="float: left;">';
                                    $return .= '<dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>';
                                    $return .= '<dd><ul data-id="arm_user_plan">' . $plansLists . '</ul></dd>';
                                    $return .= '</dl>';
                                    $return .= '<br/><span class="arm_error_select_plan error arm_invalid" style="display:none; text-align:left;">' . esc_html__('Please select Post.', 'ARMember') . '</span>';
                                    $return .= '</div>';
                                    $return .= '</div>';

                                    $return .= '<div class="arm_selected_plan_cycle" style="position: relative; margin-top: 3.8rem;">';
                                    $return .= '</div>';

                                    $return .= '<div  style="position: relative; margin-top: 10px;">';
                                    $return .= '<span class="arm_edit_plan_lbl">' . esc_html__('Post Start Date', 'ARMember') . '</span>';
                                    $return .= '<div class="arm_edit_field" style="position: relative;">';
                                    
                                    $return .= '<input type="text" value="' . date($arm_common_date_format, strtotime(date('Y-m-d'))) . '"  data-date_format="'.$arm_common_date_format.'" name="arm_subscription_start_date[]" class="arm_datepicker arm_member_form_input arm_user_add_plan_date_picker"  style="width: 500px; min-width: 500px;"/>';
                                    
                                    $return .= '</div>';
                                    $return .= '</div>';

                                    $return .= '<div  style="position: relative; margin-top: 10px;">';
                                    $return .= '<span class="arm_edit_plan_lbl">&nbsp;</span>';
                                    $return .= '<div class="arm_edit_field">';
                                    $return .= '<button class="arm_member_add_paid_plan_save_btn arm_save_btn">' . esc_html__('Save', 'ARMember') . '</button>';

                                    
                                    $return .= '<button class="arm_add_plan_cancel_btn arm_cancel_btn" type="button">' . esc_html__('Close', 'ARMember') . '</button>';


                                    $return .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/arm_loader.gif" class="arm_loader_img_user_add_plan" style="position:relative;top:8px;display:none;" width="24" height="24" />';
                                    $return .= '</div>';
                                    $return .= '</div>';

                                    $return .= '</div>';

                                    $user_plans = $planIDs;

                                    if (!empty($u_roles)) {
                                        foreach ($u_roles as $ur) {
                                            $return .= '<input type="hidden" name="roles[]" value="' . $ur . '"/>';
                                        }
                                    }

                                    $return .= '<div class="arm_loading_grid arm_plan_loading_grid" style="display: none;"><img src="' . MEMBERSHIP_IMAGES_URL . '/loader.gif" alt="Loading.."></div>';
                                    $return .= '<table class="arm_user_edit_plan_table" cellspacing="1" style="text-align: center; width:95%;     border-left: 1px solid #eaeaea; margin: 20px; border-right: 1px solid #eaeaea;">';

                                    $return .= '<tr class="arm_user_plan_row arm_user_plan_head odd">';
                                    $return .= '<th class="arm_edit_plan_name">' . esc_html__('Post Name', 'ARMember') . '</th>';
                                    $return .= '<th class="arm_edit_plan_type">' . esc_html__('Post Type', 'ARMember') . '</th>';
                                    $return .= '<th class="arm_edit_plan_start">' . esc_html__('Starts On', 'ARMember') . '</th>';
                                    $return .= '<th class="arm_edit_plan_expire">' . esc_html__('Expires On', 'ARMember') . '</th>';
                                    $return .= '<th class="arm_edit_plan_cycle_date">' . esc_html__('Cycle Date', 'ARMember') . '</th>';

                                    $return .= '<th class="arm_edit_plan_action">' . esc_html__('Remove', 'ARMember') . '</th>';
                                    $return .= '</tr>';

                                    if (!empty($user_future_plan_ids)) {

                                        $all_user_plans = array_merge($user_plans, $user_future_plan_ids);
                                    } else {
                                        $all_user_plans = $user_plans;
                                    }

                                    if (!empty($all_user_plans) && !empty($postIDs)) {
                                        $count_plan = 0;
                                        foreach ($all_user_plans as $uplans) {
                                            $count_plan++;
                                            $planData = get_user_meta($user_id, 'arm_user_plan_' . $uplans, true);

                                            $arm_paid_plan_condition = "";

                                            if (!empty($planData) && (isset($planData['arm_current_plan_detail']) && !empty($planData['arm_current_plan_detail']) && ($planData['arm_current_plan_detail']['arm_subscription_plan_post_id'] != 0)))
                                            {
                                                $planDetail = $planData['arm_current_plan_detail'];


                                                $payment_cycle = $planData['arm_payment_cycle'];
                                                $plan_start_date = (isset($planData['arm_start_plan']) && !empty($planData['arm_start_plan'])) ? date('m/d/Y', $planData['arm_start_plan']) : date('m/d/Y');
                                                if (!empty($planDetail)) {
                                                    $planObj = new ARM_Plan(0);
                                                    $planObj->init((object) $planDetail);
                                                } else {
                                                    $planObj = new ARM_Plan($uplans);
                                                }




                                                $plan_name = isset($planDetail['arm_subscription_plan_name']) ? $planDetail['arm_subscription_plan_name'] : '';
                                                $recurring_profile = $planObj->new_user_plan_text(false, $payment_cycle);

                                                $arm_plan_is_suspended = '';
                                                $suspended_plan_ids = get_user_meta($user_id, 'arm_user_suspended_plan_ids', true);
                                                $suspended_plan_ids = (isset($suspended_plan_ids) && !empty($suspended_plan_ids)) ? $suspended_plan_ids : array();
                                                if (!empty($suspended_plan_ids)) {
                                                    if (in_array($uplans, $suspended_plan_ids)) {
                                                        $arm_plan_is_suspended = '<div class="arm_manage_plan_status_div" style="position: relative; width:55%;">';
                                                        $arm_plan_is_suspended .= '<span style="color: #ec4444;">(' . esc_html__('Suspended', 'ARMember') . ')</span>';
                                                        $arm_plan_is_suspended .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/grid_edit_hover_trns.png"  title="' . esc_attr__('Activate Plan', 'ARMember') . '" class="armhelptip tipso_style" width="26" data-plan_id="' . $uplans . '" data-user_id="' . $user_id . '" onclick="showConfirmBoxCallback_plan(\'status_' . $uplans . '\');" style="margin: -5px 0; position: absolute; "/>';

                                                        $arm_plan_is_suspended .= "<div class='arm_confirm_box arm_confirm_box_status_{$uplans}' id='arm_confirm_box_plan_status_{$uplans}' style='right: -5px;'>";
                                                        $arm_plan_is_suspended .= "<div class='arm_confirm_box_body'>";
                                                        $arm_plan_is_suspended .= "<div class='arm_confirm_box_arrow'></div>";
                                                        $arm_plan_is_suspended .= "<div class='arm_confirm_box_text'>" . esc_html__("Are you sure you want to activate " . $plan_name . " plan for this user?", 'ARMember') . "</div>";
                                                        $arm_plan_is_suspended .= "<div class='arm_confirm_box_btn_container'>";
                                                        $arm_plan_is_suspended .= "<button type='button' class='arm_confirm_box_btn armok arm_plan_status_change' data-item_id='{$uplans}'>" . esc_html__('Activate', 'ARMember') . "</button>";
                                                        $arm_plan_is_suspended .= "<button type='button' class='arm_confirm_box_btn armcancel' onclick='hideConfirmBoxCallback();'>" . esc_html__('Cancel', 'ARMember') . "</button>";
                                                        $arm_plan_is_suspended .= "</div>";
                                                        $arm_plan_is_suspended .= "</div>";
                                                        $arm_plan_is_suspended .= "</div></div>";
                                                    }
                                                }
                                                $arm_next_due_date = (isset($planData['arm_next_due_payment']) && !empty($planData['arm_next_due_payment']) ) ? date_i18n($date_format, $planData['arm_next_due_payment']) : '-';

                                                if ($planObj->is_recurring()) {
                                                    $recurring_plan_options = $planObj->prepare_recurring_data($payment_cycle);
                                                    $recurring_time = $recurring_plan_options['rec_time'];
                                                    $completed = $planData['arm_completed_recurring'];
                                                    if ($recurring_time == 'infinite' || empty($planData['arm_expire_plan'])) {
                                                        $remaining_occurence = esc_html__('Never Expires', 'ARMember');
                                                    } else {
                                                        $remaining_occurence = $recurring_time - $completed;
                                                    }

                                                    if (!empty($planData['arm_expire_plan'])) {
                                                        if ($remaining_occurence == 0) {
                                                            $arm_next_due_date = esc_html__('No cycles due', 'ARMember');
                                                        } else {
                                                            $arm_next_due_date .= "<br/>( " . $remaining_occurence . esc_html__(' cycles due', 'ARMember') . " )";
                                                        }
                                                    }
                                                }



                                                $expiry_date = (isset($planData['arm_expire_plan']) && !empty($planData['arm_expire_plan'])) ? $planData['arm_expire_plan'] : '';

                                                $arm_edit_plan = '';

                                                $arm_delete_plan = '';
                                                if ($is_multiple_membership_feature->isMultipleMembershipFeature) {

                                                    if (in_array($uplans, $user_future_plan_ids)) {
                                                        $arm_delete_plan .= '<input type="hidden" name="arm_user_future_plan[]" value="' . $uplans . '"/>';
                                                    } else {
                                                        $arm_delete_plan .= '<input type="hidden" name="arm_subscription_start_date[]" value="' . $plan_start_date . '"/>';
                                                        $arm_delete_plan .= '<input type="hidden" name="arm_user_plan[]" value="' . $uplans . '"/>';
                                                    }
                                                }

                                                $arm_delete_plan .= '<div style="position:relative;">';
                                                $arm_delete_plan .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/grid_delete_icon_trans.png"  title="' . esc_attr__('Delete Plan', 'ARMember') . '" class="arm_edit_plan_action_button armhelptip tipso_style" id="arm_member_delete_plan" data-plan_id="' . $uplans . '" data-user_id="' . $user_id . '" onclick="showConfirmBoxCallback_plan(' . $uplans . ');"/>';

                                                $confirmBox = "<div class='arm_confirm_box arm_confirm_box_{$uplans}' id='arm_confirm_box_plan_{$uplans}' style='right: -5px;'>";
                                                $confirmBox .= "<div class='arm_confirm_box_body'>";
                                                $confirmBox .= "<div class='arm_confirm_box_arrow'></div>";
                                                $confirmBox .= "<div class='arm_confirm_box_text'>" . esc_html__("Are you sure you want to delete this plan from user?", 'ARMember') . "</div>";
                                                $confirmBox .= "<div class='arm_confirm_box_btn_container'>";
                                                $confirmBox .= "<button type='button' class='arm_confirm_box_btn armok arm_member_paid_plan_delete_btn' data-item_id='{$uplans}'>" . esc_html__('Delete', 'ARMember') . "</button>";
                                                $confirmBox .= "<button type='button' class='arm_confirm_box_btn armcancel' onclick='hideConfirmBoxCallback();'>" . esc_html__('Cancel', 'ARMember') . "</button>";
                                                $confirmBox .= "</div>";
                                                $confirmBox .= "</div>";
                                                $confirmBox .= "</div>";
                                                $confirmBox .= "</div>";

                                                $arm_delete_plan .= $confirmBox;

                                                $arm_edit_plan_text_box = '';
                                                if ($expiry_date != '') {
                                                    $arm_edit_plan_text_box = '<input value="' . date('m/d/Y', $expiry_date) . '" name="arm_subscription_expiry_date_' . $uplans . '_' . $user_id . '" id="arm_subscription_expiry_date_' . $uplans . '_' . $user_id . '" class="arm_datepicker arm_expire_date arm_edit_plan_expire_date" style="min-width:100px; width:100px" aria-invalid="false" type="text">';
                                                    $arm_edit_plan .= "<a class='arm_member_edit_plan' >"
                                                            . "<img src='" . MEMBERSHIP_IMAGES_URL . "/grid_edit_hover_trns.png' style='position: absolute; margin: -4px 0 0 5px; cursor: pointer;' width='26' title='" . esc_attr__('Change Expiry Date', 'ARMember') . "' class='armhelptip tipso_style'/>"
                                                            . "</a>";
                                                    $arm_edit_plan .= "<img src='" . MEMBERSHIP_IMAGES_URL . "/arm_save_icon.png' style='vertical-align: middle;display:none;' width='14' height='16' title='" . esc_html__('Save Expiry Date', 'ARMember') . "' class='arm_edit_plan_action_button arm_member_save_plan armhelptip tipso_style' data-plan_id='" . $uplans . "' data-user_id='" . $user_id . "' />&nbsp;";
                                                    $arm_edit_plan .= "<img src='" . MEMBERSHIP_IMAGES_URL . "/cancel_date_icon.png' style='display:none;' width='14' height='16' title='" . esc_html__('Cancel', 'ARMember') . "' class='arm_edit_plan_action_button arm_member_cancel_save_plan armhelptip tipso_style' data-plan_id='" . $uplans . "' data-user_id='" . $user_id . "' data-plan-expire-date='" . date('m/d/Y', $expiry_date) . "' />&nbsp;";
                                                    $arm_edit_plan .= '<img src="' . MEMBERSHIP_IMAGES_URL . '/arm_loader.gif" class="arm_edit_user_plan_loader" style="    vertical-align: middle;display:none;margin-left: 10px;" width="17" height="18" />';
                                                }

                                                $arm_paid_post_data = get_post($postIDs[$uplans]);
                                                $plan_name = $arm_paid_post_data->post_title;

                                                $expire_date = ($expiry_date != '') ? date_i18n($date_format, $expiry_date) : esc_html__('Never Expires', 'ARMember');
                                                $row_class = ($count_plan % 2 == 0) ? 'odd' : 'even';
                                                $return .= '<tr class="arm_user_plan_row ' . $row_class . '">';
                                                $return .= '<td class="arm_edit_plan_name">' . stripslashes_deep($plan_name) . ' ' . $arm_plan_is_suspended . '</td>';
                                                $return .= '<td class="arm_edit_plan_type" >' . $recurring_profile;

                                                $return .= '</td>';
                                                $return .= '<td class="arm_edit_plan_start" >' . date_i18n($date_format, $planData['arm_start_plan']);

                                                if (!empty($planData['arm_trial_start'])) {
                                                    if ($planData['arm_trial_start'] < $planData['arm_start_plan']) {
                                                        $return .= "<br/><span style='color: green;'>(" . esc_html__('trial active', 'ARMember') . ")</span>";
                                                    }
                                                }

                                                $return .= '</td>';


                                                $return .= '<td class="arm_edit_plan_expiry" >'
                                                        . '<span id="arm_expiry_date_lbl">' . $expire_date . '</span>'
                                                        . '<span id="arm_expiry_date_input" style="display:none;">' . $arm_edit_plan_text_box . '</span>'
                                                        . $arm_edit_plan
                                                        . '</td>';
                                                $return .= '<td class="arm_edit_plan_cycle_date" >' . $arm_next_due_date;


                                                if ($planObj->is_recurring() && $planData['arm_payment_mode'] == 'auto_debit_subscription') {
                                                    $return .= '<br/>(' . esc_html__('Auto Debit', 'ARMember') . ')';
                                                }
                                                $return .= '</td>';
                                                $return .= '<td class="arm_edit_plan_action">' . $arm_delete_plan . '</td>';
                                                $return .= '</tr>';



                                            }
                                        }

                                        

                        
                                    } else {
                                        $return .= '<tr class="arm_user_edit_plan_table" ><td colspan="6" style="text-align:center">'
                                                . esc_html__("This user don't have any paid post.", 'ARMember')
                                                . '</td></tr>';
                                    }*/

                                    $return .= '<tr>';
                                    $return .= '<td colspan="2">';
                                    $member_paid_post_plans = $arm_pay_per_post_feature->arm_get_paid_post_modal_plans($user_id, 1, 5);
                                    $return .= $member_paid_post_plans;
                                    $return .= '</td></tr>';

                                    $return .= '</table>';
                                    

                                    $bulk_member_change_plan_popup_content = '<span class="arm_confirm_text">' . esc_html__("Are you sure you want to remove this plan from this user??", 'ARMember') . '</span>';
                                    $bulk_member_change_plan_popup_content .= '<input type="hidden" value="false" id="bulk_change_plan_flag"/>';
                                    $bulk_member_change_plan_popup_arg = array(
                                        'id' => 'change_plan_bulk_message',
                                        'class' => 'change_plan_bulk_message',
                                        'title' => esc_html__('Change Plan', 'ARMember'),
                                        'content' => $bulk_member_change_plan_popup_content,
                                        'button_id' => 'arm_bulk_member_change_plan_ok_btn',
                                        'button_onclick' => "apply_member_bulk_action('bulk_change_plan_flag');",
                                    );
                                    $return .= $arm_global_settings->arm_get_bpopup_html($bulk_member_change_plan_popup_arg);
                                }
                                echo $return. '^|^' . $user_name; //phpcs:ignore
                                die();
                            ?>
                        </div>
                    </div>
                    </form>
                </div>
        <?php
            }
            die;
        }

        function arm_member_view_detail_func() {

            $member_id = !empty($_REQUEST['member_id']) ? intval($_REQUEST['member_id']) : '';
            if (!empty($member_id) && $member_id != 0) {
                global $arm_slugs, $ARMember, $arm_capabilities_global;
                $view_type = (!empty($_REQUEST['view_type']) && $_REQUEST['view_type'] == 'popup') ? sanitize_text_field($_REQUEST['view_type']) : "";
                $link_param = "";
                if($view_type == 'popup') {
                    $link_param = "&view_type=popup";
                }
		
                $ARMember->arm_check_user_cap($arm_capabilities_global['arm_manage_members'], '1',1);
		
                $view_link = admin_url('admin.php?page=' . $arm_slugs->manage_members . '&action=view_member&id=' . $member_id.$link_param);
                //$view_link = MEMBERSHIP_VIEWS_URL."/arm_view_member.php?member_id=".$member_id;
        ?>
                <div class="arm_member_view_detail_popup popup_wrapper arm_member_view_detail_popup_wrapper">
                    <div class="popup_wrapper_inner" style="overflow: hidden;">
                        <div class="popup_header">
                            <span class="popup_close_btn arm_popup_close_btn arm_member_view_detail_close_btn"></span>
                            <span class="add_rule_content"><?php esc_html_e('Member Details','ARMember' );?></span>
                        </div>
                        <div class="popup_content_text arm_member_view_detail_popup_text arm_padding_0" id="arm_member_view_detail_popup_text" >
                            <iframe src="<?php echo esc_url($view_link); //phpcs:ignore?>" id="arm_member_view_iframe"></iframe>
                        </div>
                    </div>
                </div>
        <?php
            }
            die;
        }

        function arm_gateway_cancel_subscription_data($arm_cancel_data = array(), $user_id = 0, $plan_id = 0, $arm_payment_gateway = "", $arm_subscription_id_field_name = "", $arm_transaction_id_field_name = "", $arm_customer_id_field_name = ""){
            global $wpdb, $ARMember, $arm_global_settings, $arm_subscription_plans, $arm_member_forms, $arm_payment_gateways, $arm_manage_communication, $arm_subscription_cancel_msg;

            if(!empty($user_id) && !empty($plan_id) && !empty($arm_payment_gateway)){
                $all_payment_gateways = $arm_payment_gateways->arm_get_active_payment_gateways();
                if(!empty($all_payment_gateways[$arm_payment_gateway])){
                    $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                    $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $plan_id, true);
                    $planData = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                    //$planData = shortcode_atts($defaultPlanData, $userPlanDatameta);

                    $user_payment_gateway = !empty($planData['arm_user_gateway']) ? $planData['arm_user_gateway'] : '';

                    if(strtolower($user_payment_gateway) == $arm_payment_gateway){
                        $user_payment_gateway_data = !empty($planData['arm_'.$arm_payment_gateway]) ? $planData['arm_'.$arm_payment_gateway] : array();
                        $arm_payment_mode = $planData['arm_payment_mode'];
                        $arm_plan_details = $planData['arm_current_plan_detail'];

                        if(!empty($arm_plan_details)){
                            $plan = new ARM_Plan(0);
                            $plan->init((object) $arm_plan_details);
                        }else{
                            $plan = new ARM_Plan($plan_id);
                        }

                        $arm_payment_cycle = $planData['arm_payment_cycle'];
                        $recurring_data = $plan->prepare_recurring_data($arm_payment_cycle);
                        $amount = isset($recurring_data['amount']) ? $recurring_data['amount'] : 0;

                        $arm_customer_id = $arm_subscr_id = $arm_transaction_id = "";

                        if(!empty($arm_customer_id_field_name)){
                            $arm_customer_id = isset($user_payment_gateway_data[$arm_customer_id_field_name]) ? $user_payment_gateway_data[$arm_customer_id_field_name] : '';
                        }

                        if(!empty($arm_subscription_id_field_name)){
                            $arm_subscr_id = isset($user_payment_gateway_data[$arm_subscription_id_field_name]) ? $user_payment_gateway_data[$arm_subscription_id_field_name] : '';
                            if(empty($arm_subscr_id))
                            {
                                 $arm_subscription_id_field_name_old = str_replace('arm_', '', $arm_subscription_id_field_name);
                                 $arm_subscr_id = isset($user_payment_gateway_data[$arm_subscription_id_field_name_old]) ? $user_payment_gateway_data[$arm_subscription_id_field_name_old] : '';
                            }
                        }

                        if(!empty($arm_transaction_id_field_name)){
                            $arm_transaction_id = isset($user_payment_gateway_data[$arm_transaction_id_field_name]) ? $user_payment_gateway_data[$arm_transaction_id_field_name] : '';
                            if(empty($arm_transaction_id))
                            {
                                 $arm_transaction_id_field_name_old = str_replace('arm_', '', $arm_transaction_id_field_name);
                                 $arm_transaction_id = isset($user_payment_gateway_data[$arm_transaction_id_field_name_old]) ? $user_payment_gateway_data[$arm_transaction_id_field_name_old] : '';
                            }
                        }

                        $arm_payment_gateway_options = $all_payment_gateways[$arm_payment_gateway];

                        $arm_payment_log_table = $ARMember->tbl_arm_payment_log;
                        $arm_transaction_payment_log_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM `{$arm_payment_log_table}` WHERE arm_user_id = %d AND arm_plan_id = %d AND arm_payment_type = %s AND arm_payment_gateway = %s AND arm_token != %s AND arm_transaction_id != '' ORDER BY arm_created_date DESC LIMIT 0,1", $user_id, $plan_id, 'subscription', $arm_payment_gateway, '')); //phpcs:ignore --Reason $arm_payment_log_table is a table name

                        if(empty($arm_subscr_id))
                        {
                            $arm_subscr_id = !empty($arm_transaction_payment_log_data->arm_token) ? $arm_transaction_payment_log_data->arm_token : '';
                        }

                        $arm_cancel_data = array(
                            'user_id' => $user_id,
                            'plan_id' => $plan_id,
                            'arm_cancel_amount' => $amount,
                            'arm_plan_data' => $planData,
                            'payment_gateway_options' => $arm_payment_gateway_options,
                            'arm_payment_mode' => $arm_payment_mode,
                            'arm_subscr_id' => $arm_subscr_id,
                            'arm_customer_id' => $arm_customer_id,
                            'arm_transaction_id' => $arm_transaction_id,
                            'arm_payment_log_data' => $arm_transaction_payment_log_data
                        );
                    }
                }
            }

            return $arm_cancel_data;
        }


        function arm_cancel_subscription_payment_log($user_id = 0, $plan_id = 0, $arm_payment_gateway = "", $arm_subscription_id = "", $arm_transaction_id = "", $arm_customer_id = "", $payment_mode = "manual_subscription", $arm_cancel_amount = 0, $arm_payer_email = ""){

            global $wpdb, $ARMember, $arm_payment_gateways, $arm_manage_communication;

            if(!empty($user_id) && !empty($plan_id)){

                //Check plan cancel entry already exist or not.
                $armCancelLogData = $wpdb->get_row( $wpdb->prepare("SELECT `arm_log_id` FROM `" . $ARMember->tbl_arm_payment_log . "` WHERE `arm_token`= %s AND arm_transaction_status = %s AND arm_user_id = %d AND arm_plan_id = %d ORDER BY `arm_log_id` DESC",$arm_subscription_id,'canceled',$user_id,$plan_id) ); //phpcs:ignore --Reason $ARMember->tbl_arm_payment_log is a table name

                if(empty($armCancelLogData))
                {
                    $user_detail = get_userdata($user_id);
                    $payer_email = !empty($arm_payer_email) ? $arm_payer_email : $user_detail->user_email;

                    $arm_manage_communication->arm_user_plan_status_action_mail(array('plan_id' => $plan_id, 'user_id' => $user_id, 'action' => 'on_cancel_subscription'));

                    $payment_data = array(
                        'arm_user_id' => $user_id,
                        'arm_first_name'=> $user_detail->first_name,
                        'arm_last_name'=> $user_detail->last_name,
                        'arm_plan_id' => $plan_id,
                        'arm_payment_gateway' => $arm_payment_gateway,
                        'arm_payment_type' => 'subscription',
                        'arm_token' => $arm_subscription_id,
                        'arm_payer_email' => $payer_email,
                        'arm_receiver_email' => '',
                        'arm_transaction_id' => $arm_transaction_id,
                        'arm_transaction_payment_type' => 'subscription',
                        'arm_payment_mode' => $payment_mode,
                        'arm_transaction_status' => 'canceled',
                        'arm_payment_date' => current_time('mysql'),
                        'arm_amount' => $arm_cancel_amount,
                        'arm_coupon_code' => '',
                        'arm_is_trial' => '0',
                        'arm_created_date' => current_time('mysql')
                    );
                    $payment_log_id = $arm_payment_gateways->arm_save_payment_log($payment_data);
                }
            }
        }

    }

}
global $arm_members_class;
$arm_members_class = new ARM_members();

if (!function_exists('arm_set_member_status')) {

    /**
     * Set Member Status
     * @param int $user_id Member's ID
     * @param int $primary_status `Active->1, Inactive->2, Pending->3`
     * @param int $secondary_status `Admin->0, Account Closed->1, Suspended->2, Expired->3, User Cancelled->4, Payment Failed->5, Cancelled->6`
     * 
     */
    function arm_set_member_status($user_id, $primary_status = 1, $secondary_status = 0) {


        global $wp, $wpdb, $ARMember;
        $primary_status = (!empty($primary_status)) ? $primary_status : 1;
        $secondary_status = (!empty($secondary_status)) ? $secondary_status : 0;
        if (!empty($user_id) && $user_id != 0) {
            if ($primary_status == 3) {
                $secondary_status = 0;
            }
            $updateStatusArgs = array(
                'arm_primary_status' => $primary_status,
                'arm_secondary_status' => $secondary_status,
            );
            $wpdb->update($ARMember->tbl_arm_members, $updateStatusArgs, array('arm_user_id' => $user_id));
            if ($primary_status == 1) {
                delete_user_meta($user_id, 'arm_user_activation_key');
            }
            update_user_meta($user_id, 'arm_primary_status', $primary_status);
            update_user_meta($user_id, 'arm_secondary_status', $secondary_status);
        }
        return;
    }

}
if (!function_exists('arm_get_member_status')) {

    function arm_get_member_status($user_id, $type = "primary") {
        global $wp, $wpdb, $ARMember;
        $memberStatus = false;
        $selectedColumn = 'arm_primary_status';
        if ($type == 'secondary') {
            $selectedColumn = 'arm_secondary_status';
        }
        if (!empty($user_id) && $user_id != 0) {

           

             $statuses = $wpdb->get_row( $wpdb->prepare("SELECT `$selectedColumn` FROM `" . $ARMember->tbl_arm_members . "` WHERE `arm_user_id`=%d ",$user_id)); //phpcs:ignore --Reason $ARMember->tbl_arm_members is a table name

            if ($statuses != null) {
                if ($type == 'secondary' && isset($statuses->arm_secondary_status)) {
                    $memberStatus = $statuses->arm_secondary_status;
                } else {
                    $memberStatus = $statuses->arm_primary_status;
                }
            }
        }
        return $memberStatus;
    }

}

if (!function_exists('arm_get_all_member_status')) {

    function arm_get_all_member_status($user_id) {
        global $wp, $wpdb, $ARMember;
        $memberStatus = array();

        if (!empty($user_id) && $user_id != 0) {
            $statuses = $wpdb->get_row( $wpdb->prepare("SELECT `arm_primary_status`, `arm_secondary_status` FROM `" . $ARMember->tbl_arm_members . "` WHERE `arm_user_id`=%d ",$user_id)); //phpcs:ignore --Reason $ARMember->tbl_arm_members is a table name
            if ($statuses != null) {
                $memberStatus['arm_primary_status'] = $statuses->arm_primary_status;
                $memberStatus['arm_secondary_status'] = $statuses->arm_secondary_status;
            }
        }
        return $memberStatus;
    }

}

if (!function_exists('arm_is_member_active')) {

    function arm_is_member_active($user_id) {
        global $wp, $wpdb, $ARMember;
        $memberStatus = arm_get_member_status($user_id);
        if ($memberStatus == '1') {
            return true;
        }
        return false;
    }

}
