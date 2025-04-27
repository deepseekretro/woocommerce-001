<?php

/**
 * Subscription Shipping Frequency Notification Email.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/customer-shipping-frequency-notification.php.
 */
defined( 'ABSPATH' ) || exit;

$shipping_cycle_string = _enr_get_shipping_frequency_string( array(
	'is_synced'      => _enr_is_shipping_frequency_synced( $subscription ),
	'interval'       => $subscription->get_meta( ENR_PREFIX . 'shipping_period_interval' ),
	'period'         => $subscription->get_meta( ENR_PREFIX . 'shipping_period' ),
	'sync_date_day'  => $subscription->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_day' ),
	'sync_date_week' => $subscription->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_week' ),
		) );

echo esc_html( $email_heading ) . "\n\n";

/* translators: 1: subscription number 2: shipping cycle */
printf( esc_html__( 'This is to inform you that your Subscription #%1$s will be %2$s from your next renewal onward..', 'enhancer-for-woocommerce-subscriptions' ), esc_html( $subscription->get_order_number() ), wp_kses_post( $shipping_cycle_string ) );

echo "\n\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";

/**
 * Add email order details.
 * 
 * @since 1.0
 */
do_action( 'woocommerce_subscriptions_email_order_details', $subscription, $sent_to_admin, $plain_text, $email );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/**
 * Add email order meta.
 * 
 * @since 1.0
 */
do_action( 'woocommerce_email_order_meta', $subscription, $sent_to_admin, $plain_text, $email );

/**
 * Add email customer details.
 * 
 * @since 1.0
 */
do_action( 'woocommerce_email_customer_details', $subscription, $sent_to_admin, $plain_text, $email );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";
}

/**
 * Get email footer text.
 * 
 * @since 1.0
 */
echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
