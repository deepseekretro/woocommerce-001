<?php
/**
 * Subscription Product Variation Enhancer Options.
 */
defined( 'ABSPATH' ) || exit;

$product_internal_meta_keys = array(
	'enable_seperate_shipping_cycle',
	'enable_seperate_shipping_cycle_for_old_subscriptions',
	'shipping_period_interval',
	'shipping_period',
	'shipping_frequency_sync_date_day',
	'shipping_frequency_sync_date_week',
	'allow_cancelling_to',
	'allow_cancelling_after',
	'allow_cancelling_after_due',
	'allow_cancelling_before_due',
	'allow_price_update_for_old_subscriptions',
	'subscription_price_for_old_subscriptions',
	'notify_subscription_price_update_before',
	'exclude_reminder_emails',
);

$meta_values = array();
foreach ( $product_internal_meta_keys as $internal_meta_key ) {
	$meta_values[ $internal_meta_key ] = get_post_meta( $variation->ID, ENR_PREFIX . $internal_meta_key, true );
}

extract( $meta_values );
?>
<div class="show_if_variable-subscription _enr_subscription_fields_wrapper">
	<p class="form-row form-row-first form-field show_if_variable-subscription _enr_enable_seperate_shipping_cycle_field">
		<label for="_enr_enable_seperate_shipping_cycle[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Separate shipping cycle', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		<input type="checkbox" class="checkbox _enr_enable_seperate_shipping_cycle" name="_enr_enable_seperate_shipping_cycle[<?php echo esc_attr( $loop ); ?>]" value="yes" <?php checked( 'yes', $enable_seperate_shipping_cycle ); ?>/>
		<span class="woocommerce-help-tip" data-tip="<?php esc_html_e( 'Enabling this option creates separate shipping fulfilment orders for the subscription', 'enhancer-for-woocommerce-subscriptions' ); ?>"></span>
	</p>
	<p class="form-row form-row-first form-field show_if_variable-subscription _enr_enable_seperate_shipping_cycle_for_old_subscriptions_field">
		<label for="_enr_enable_seperate_shipping_cycle_for_old_subscriptions[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Separate shipping cycle for old subscriptions', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		<input type="checkbox" class="checkbox _enr_enable_seperate_shipping_cycle_for_old_subscriptions" name="_enr_enable_seperate_shipping_cycle_for_old_subscriptions[<?php echo esc_attr( $loop ); ?>]" value="yes" <?php checked( 'yes', $enable_seperate_shipping_cycle_for_old_subscriptions ); ?>/>
		<span class="woocommerce-help-tip" data-tip="<?php esc_html_e( 'Enabling this option creates separate shipping fulfillment orders for previously placed subscriptions from the next renewal onward', 'enhancer-for-woocommerce-subscriptions' ); ?>"></span>
	</p>    
	<p class="form-row form-row-first form-field show_if_variable-subscription _enr_shipping_frequency_field">
		<label for="_enr_shipping_frequency[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Shipping Frequency Every', 'enhancer-for-woocommerce-subscriptions' ); ?>
			<span class="woocommerce-help-tip" data-tip="<?php esc_html_e( 'Choose the interval to create the shipping fulfilment order', 'enhancer-for-woocommerce-subscriptions' ); ?>"></span>
		</label>
		<span class="wrap">
			<label for="_enr_shipping_period_interval[<?php echo esc_attr( $loop ); ?>]" class="wcs_hidden_label"><?php esc_html_e( 'Shipping interval', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
			<input type="number" class="wc_input_price short _enr_shipping_period_interval" name="_enr_shipping_period_interval[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $shipping_period_interval ); ?>" min="0"/>

			<label for="_enr_shipping_period[<?php echo esc_attr( $loop ); ?>]" class="wcs_hidden_label"><?php esc_html_e( 'Shipping period', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
			<select class="_enr_shipping_period" name="_enr_shipping_period[<?php echo esc_attr( $loop ); ?>]">
				<?php foreach ( wcs_get_subscription_period_strings() as $key => $value ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $shipping_period ); ?>><?php echo esc_html( $value ); ?></option>
				<?php endforeach; ?>
			</select>
		</span>
	</p>
	<p class="form-row form-row-first form-field show_if_variable-subscription _enr_shipping_frequency_sync_date_field">
		<label for="_enr_shipping_frequency_sync_date[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Synchronise shipping frequency', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		<span class="wrap">
			<select class="_enr_shipping_frequency_sync_date_day" name="_enr_shipping_frequency_sync_date_day[<?php echo esc_attr( $loop ); ?>]">
				<?php foreach ( WC_Subscriptions_Synchroniser::get_billing_period_ranges( 'month' ) as $value => $label ) { ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $shipping_frequency_sync_date_day, true ); ?>><?php echo esc_html( $label ); ?></option>                    
				<?php } ?>
			</select>
			<select class="_enr_shipping_frequency_sync_date_week" name="_enr_shipping_frequency_sync_date_week[<?php echo esc_attr( $loop ); ?>]">
				<?php foreach ( WC_Subscriptions_Synchroniser::get_billing_period_ranges( 'week' ) as $value => $label ) { ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $shipping_frequency_sync_date_week, true ); ?>><?php echo esc_html( $label ); ?></option>                    
				<?php } ?>
			</select>            
		</span>
	</p>
	<?php if ( 'yes' === get_option( ENR_PREFIX . 'allow_cancelling', 'yes' ) ) { ?>
		<p class="form-row form-row-first form-field show_if_variable-subscription _enr_allow_cancelling_to_field">
			<label for="_enr_allow_cancelling_to[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Allow cancelling', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
			<select class="select short _enr_allow_cancelling_to" name="_enr_allow_cancelling_to[<?php echo esc_attr( $loop ); ?>]">
				<option value="use-storewide" <?php selected( 'use-storewide', $allow_cancelling_to, true ); ?>><?php esc_html_e( 'Inherit storewide settings', 'enhancer-for-woocommerce-subscriptions' ); ?></option>
				<option value="override-storewide" <?php selected( 'override-storewide', $allow_cancelling_to, true ); ?>><?php esc_html_e( 'Override storewide settings', 'enhancer-for-woocommerce-subscriptions' ); ?></option>
			</select>
		</p>
		<p class="form-row form-row-first form-field show_if_variable-subscription _enr_allow_cancelling_after_field">
			<label for="_enr_allow_cancelling_after[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Allow cancelling after', 'enhancer-for-woocommerce-subscriptions' ); ?>
				<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Set 0 to allow subscribers to cancel immediately. If empty, customers will not be allowed to cancel.', 'enhancer-for-woocommerce-subscriptions' ); ?>"></span>
			</label>
			<input type="number" class="short wc_input_price" name="_enr_allow_cancelling_after[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( metadata_exists( 'post', $variation->ID, ENR_PREFIX . 'allow_cancelling_after' ) ? $allow_cancelling_after : '0'  ); ?>">
			<span class="description"><?php esc_html_e( 'day(s) from the subscription start date.', 'enhancer-for-woocommerce-subscriptions' ); ?></span>
		</p>
		<p class="form-row form-row-first form-field show_if_variable-subscription _enr_allow_cancelling_after_due_field">
			<label for="_enr_allow_cancelling_after_due[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Allow cancelling after each renewal', 'enhancer-for-woocommerce-subscriptions' ); ?>
				<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Set 0 to allow subscribers to cancel immediately. If empty, customers will not be allowed to cancel.', 'enhancer-for-woocommerce-subscriptions' ); ?>"></span>
			</label>
			<input type="number" class="short wc_input_price" name="_enr_allow_cancelling_after_due[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( metadata_exists( 'post', $variation->ID, ENR_PREFIX . 'allow_cancelling_after_due' ) ? $allow_cancelling_after_due : '0'  ); ?>">
			<span class="description"><?php esc_html_e( 'day(s) from the subscription renewal date.', 'enhancer-for-woocommerce-subscriptions' ); ?></span>
		</p>
		<p class="form-row form-row-first form-field show_if_variable-subscription _enr_allow_cancelling_before_due_field">
			<label for="_enr_allow_cancelling_before_due[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Prevent cancelling', 'enhancer-for-woocommerce-subscriptions' ); ?>
				<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'If left empty or set 0, subscribers will not be prevented from cancelling their subscriptions until the renewal date.', 'enhancer-for-woocommerce-subscriptions' ); ?>"></span>
			</label>
			<input type="number" class="short wc_input_price" name="_enr_allow_cancelling_before_due[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( metadata_exists( 'post', $variation->ID, ENR_PREFIX . 'allow_cancelling_before_due' ) ? $allow_cancelling_before_due : '0'  ); ?>">
			<span class="description"><?php esc_html_e( 'day(s) before the subscription renewal date.', 'enhancer-for-woocommerce-subscriptions' ); ?></span>
		</p>
	<?php } ?>
	<p class="form-row form-row-first form-field show_if_variable-subscription _enr_allow_price_update_for_old_subscriptions_field">
		<label for="_enr_allow_price_update_for_old_subscriptions[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Subscription price update for old subscriptions', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		<select class="select short _enr_allow_price_update_for_old_subscriptions" name="_enr_allow_price_update_for_old_subscriptions[<?php echo esc_attr( $loop ); ?>]">
			<option value="use-storewide" <?php selected( 'use-storewide', $allow_price_update_for_old_subscriptions, true ); ?>><?php esc_html_e( 'Inherit storewide settings', 'enhancer-for-woocommerce-subscriptions' ); ?></option>
			<option value="override-storewide" <?php selected( 'override-storewide', $allow_price_update_for_old_subscriptions, true ); ?>><?php esc_html_e( 'Override storewide settings', 'enhancer-for-woocommerce-subscriptions' ); ?></option>
		</select>
	</p>
	<p class="form-row form-row-first form-field show_if_variable-subscription _enr_subscription_price_for_old_subscriptions_field">
		<label for="_enr_subscription_price_for_old_subscriptions[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Subscription price for old subscriptions', 'enhancer-for-woocommerce-subscriptions' ); ?>
			<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'If the subscription price for products are updated and if you want to update new price for the old subscriptions which are renewed hereafter, then select "New price" option. The customers will be notified by email regarding the subscription price update. Note: If the subscription is placed using Auto Renewal, then new price will be updated only if the payment gateway supports "amount change" subscription feature.', 'enhancer-for-woocommerce-subscriptions' ); ?>"></span>
		</label>
		<select class="select short _enr_subscription_price_for_old_subscriptions" name="_enr_subscription_price_for_old_subscriptions[<?php echo esc_attr( $loop ); ?>]">
			<option value="old-price" <?php selected( 'old-price', $subscription_price_for_old_subscriptions, true ); ?>><?php esc_html_e( 'Old price', 'enhancer-for-woocommerce-subscriptions' ); ?></option>
			<option value="new-price" <?php selected( 'new-price', $subscription_price_for_old_subscriptions, true ); ?>><?php esc_html_e( 'New price', 'enhancer-for-woocommerce-subscriptions' ); ?></option>
		</select>
	</p>
	<p class="form-row form-row-first form-field show_if_variable-subscription _enr_notify_subscription_price_update_before_field">
		<label for="_enr_notify_subscription_price_update_before"><?php esc_html_e( 'Notify Subscription Price Update for Old Subscriptions', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		<input type="number" class="short wc_input_price" name="_enr_notify_subscription_price_update_before[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $notify_subscription_price_update_before ); ?>">
		<span class="description"><?php /* translators: 1: multiple email templates create/edit url */ echo wp_kses_post( sprintf( __( 'day(s) before the subscription due date. <br><br>You will be able to create multiple email templates to send different email content. To create a new email template/edit an existing one click <a class="button-primary" target="_blank" href="%s">Add/Edit subscription email template</a>', 'enhancer-for-woocommerce-subscriptions' ), esc_url( admin_url( 'edit.php?post_type=enr_email_template' ) ) ) ); ?></span>
	</p>

	<p class="form-row form-row-first form-field show_if_variable-subscription _enr_exclude_reminder_emails_field">
		<label for="_enr_exclude_reminder_emails[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Disable reminder emails', 'enhancer-for-woocommerce-subscriptions' ); ?>
			<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'The selected reminder emails will not be sent for the customers.', 'enhancer-for-woocommerce-subscriptions' ); ?>"></span>
		</label>
		<select class="wc-enhanced-select" name="_enr_exclude_reminder_emails[<?php echo esc_attr( $loop ); ?>][]" multiple="multiple">
			<?php foreach ( _enr_get_reminder_email_options() as $key => $val ) { ?>
				<option value="<?php echo esc_attr( $key ); ?>"
				<?php
				if ( is_array( $exclude_reminder_emails ) ) {
					selected( in_array( ( string ) $key, $exclude_reminder_emails, true ), true );
				} else {
					selected( $exclude_reminder_emails, ( string ) $key );
				}
				?>
						>
							<?php echo esc_html( $val ); ?>
				</option>
			<?php } ?>
		</select>
	</p>
</div>
