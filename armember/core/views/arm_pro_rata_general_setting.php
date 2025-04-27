<?php

global $arm_global_settings,$arm_member_forms,$wp,$wpdb,$ARMember;
$all_global_settings = $arm_global_settings->arm_get_all_global_settings();
$is_permalink = $arm_global_settings->is_permalink();
$general_settings = $all_global_settings['general_settings'];
$arm_pro_ration_feature = get_option('arm_is_pro_ration_feature', 0);
    if($arm_pro_ration_feature == 1) {
        $arm_enable_reset_billing = isset($general_settings['arm_enable_reset_billing']) ? $general_settings['arm_enable_reset_billing'] : 0;
        $is_reset_billing_enabled = ($arm_enable_reset_billing == '1') ? 'checked' : '';
        ?>

        <div class="arm_solid_divider"></div>
        <div class="page_sub_title" id="ARMProRataConfig"><?php esc_html_e('Pro-Rata Configuration', 'ARMember')?></div>
            <table class="form-table">
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php esc_html_e('Pro-Rata Method', 'ARMember')?>
                    <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php echo sprintf( addslashes(esc_html__('Pro-Rata Cost-based calculation is a type of pseudo-pro-rata where the value of an upgrade or downgrade is calculated based on the cost difference between the current and new mebership plan and Time-based Pro-Rata calculation is the calculated amount of the remaining duration for the current mebership plan which will adjust the cost of the new mebership plan. For more information, please refer %s documentation %s', 'ARMember')),"<a target='_blank' href='https://www.armemberplugin.com/documents/pro-rata/'>", "</a>" ); //phpcs:ignore ?>"></i>
                    </th>
                    <td class="arm-form-table-content"><?php 
                        $arm_pro_ration_method = isset($general_settings['arm_pro_ration_method']) ? $general_settings['arm_pro_ration_method'] : 'cost_base';?>
                        <input type="hidden" id="arm_pro_ration_method" name="arm_general_settings[arm_pro_ration_method]" value="<?php echo $arm_pro_ration_method ?>" />
                        <dl class="arm_selectbox column_level_dd">
                                <dt><span></span><input type="text" style="display:none;" value="" class="arm_autocomplete"/><i class="armfa armfa-caret-down armfa-lg"></i></dt>
                                <dd>
                                    <ul class="arm_pro_ration_method" data-id="arm_pro_ration_method">
                                        <li data-label="<?php esc_html_e('Cost-Based Calculation', 'ARMember')?>" data-value="cost_base"><?php esc_html_e('Cost-Based Calculation', 'ARMember')?></li>
                                        <li data-label="<?php esc_html_e('Time-Based Calculation', 'ARMember') ?>" data-value="time_base"><?php esc_html_e('Time-Based Calculation', 'ARMember') ?></li>
                                    </ul>
                            </dd>
                        </dl>
                    </td>
                </tr>
                <tr class="form-field">
                    <th class="arm-form-table-label"><?php esc_html_e('Reset Billing Period','ARMember') ?></th>
                    <td class="arm-form-table-content">
                        <div class="armswitch arm_global_setting_switch">
                            <input type="checkbox" id="arm_enable_reset_billing" <?php echo $is_reset_billing_enabled ?> value="1" class="armswitch_input" name="arm_general_settings[arm_enable_reset_billing]"/>
                            <label for="arm_enable_reset_billing" class="armswitch_label"></label>
                        </div>
                        <label for="arm_enable_reset_billing" class="arm_global_setting_switch_label"><?php esc_html_e('Enable reset billing period on plan purchase with Pro-Ration','ARMember') ?>
                        <i class="arm_helptip_icon armfa armfa-question-circle" title="<?php esc_html_e('When this setting is enabled then subscription membership plan cycle will be reset and starts from the date of purchasing new membership plan.', 'ARMember')?>"></i> </label>
                    </td>
                </tr>                              
            </table>
    <?php
    }
    ?>