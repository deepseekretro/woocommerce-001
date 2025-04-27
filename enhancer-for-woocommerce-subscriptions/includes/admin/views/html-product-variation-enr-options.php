<?php
/**
 * Product Variation Enhancer Options.
 */
defined( 'ABSPATH' ) || exit;

$product_internal_meta_keys = array(
	'allow_subscribe_now',
	'subscription_plans',
	'exclude_reminder_emails',
);

$meta_values = array();
foreach ( $product_internal_meta_keys as $internal_meta_key ) {
	$meta_values[ $internal_meta_key ] = get_post_meta( $variation->ID, ENR_PREFIX . $internal_meta_key, true );
}

extract( $meta_values );
?>
<div class="show_if_variable _enr_allow_subscribe_now_fields">
	<?php
	woocommerce_wp_checkbox(
			array(
				'id'            => "_enr_allow_subscribe_now{$loop}",
				'name'          => "_enr_allow_subscribe_now[{$loop}]",
				'class'         => '_enr_allow_subscribe_now',
				'value'         => $allow_subscribe_now,
				'wrapper_class' => 'form-row form-row-first',
				'label'         => __( 'Allow product level subscription', 'enhancer-for-woocommerce-subscriptions' ),
				'description'   => __( 'Enabling this option will give option for the customer to subscribe this product', 'enhancer-for-woocommerce-subscriptions' ),
				'desc_tip'      => true,
			)
	);
	?>
	<p class="form-field show_if_variable _enr_subscription_plans_field form-row form-row-first">
		<label for="_enr_select_subscription_plans[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Select subscription plans', 'enhancer-for-woocommerce-subscriptions' ); ?></label>
		<?php
		ENR_Admin::search_field( array(
			'class'       => 'wc-product-search',
			'id'          => "_enr_subscription_plans{$loop}",
			'name'        => "_enr_subscription_plans[{$loop}]",
			'action'      => '_enr_json_search_subscription_plan',
			'type'        => 'subscription_plan',
			'placeholder' => __( 'Search for a subscription plan&hellip;', 'enhancer-for-woocommerce-subscriptions' ),
			'options'     => $subscription_plans,
		) );
		?>
		<span class="woocommerce-help-tip" data-tip="<?php esc_html_e( 'Customer can choose the subscription plans from this list', 'enhancer-for-woocommerce-subscriptions' ); ?>"></span>
		<a class="button-primary" target="_blank" href="<?php echo esc_url( admin_url( 'edit.php?post_type=enr_subsc_plan' ) ); ?>"><?php esc_html_e( 'Add/Edit subscription plan', 'enhancer-for-woocommerce-subscriptions' ); ?></a>
	</p>

	<p class="form-field show_if_variable _enr_subscribe_now_exclude_reminder_emails_field form-row form-row-first">
		<label for="_enr_subscribe_now_exclude_reminder_emails[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Disable reminder emails', 'enhancer-for-woocommerce-subscriptions' ); ?>
			<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'The selected reminder emails will not be sent for the customers.', 'enhancer-for-woocommerce-subscriptions' ); ?>"></span>
		</label>
		<select class="wc-enhanced-select" name="_enr_subscribe_now_exclude_reminder_emails[<?php echo esc_attr( $loop ); ?>][]" multiple="multiple">
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
