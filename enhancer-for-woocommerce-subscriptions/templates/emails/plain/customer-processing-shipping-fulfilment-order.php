<?php

/**
 * Processing Shipping Fulfillment Order Email.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/customer-processing-shipping-fulfilment-order.php.
 */
defined( 'ABSPATH' ) || exit;

echo esc_html( $email_heading ) . "\n\n";

/* translators: %s: Subscription number */
printf( esc_html__( 'Just to let you know &mdash; your shipping fulfillment order for Subscription #%s is created and it is now being processed.', 'enhancer-for-woocommerce-subscriptions' ), esc_html( $subscription->get_order_number() ) );

echo "\n\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";

/**
 * Add email order details.
 * 
 * @since 1.0
 */
do_action( 'woocommerce_subscriptions_email_order_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/**
 * Add email order meta.
 * 
 * @since 1.0
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

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
