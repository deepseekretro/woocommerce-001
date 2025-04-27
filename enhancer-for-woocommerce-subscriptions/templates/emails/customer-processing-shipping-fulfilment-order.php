<?php
/**
 * Processing Shipping Fulfillment Order Email.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-processing-shipping-fulfilment-order.php.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Add email header.
 * 
 * @since 1.0
 */
do_action( 'woocommerce_email_header', $email_heading, $email );
?>
<p>
	<?php
	/* translators: %s: Subscription number */
	printf( esc_html__( 'Just to let you know &mdash; your shipping fulfillment order for Subscription #%s is created and it is now being processed.', 'enhancer-for-woocommerce-subscriptions' ), esc_html( $subscription->get_order_number() ) );
	?>
</p>

<?php
/**
 * Add email order details.
 * 
 * @since 1.0
 */
do_action( 'woocommerce_subscriptions_email_order_details', $order, $sent_to_admin, $plain_text, $email );

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

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/**
 * Add email footer.
 * 
 * @since 1.0
 */
do_action( 'woocommerce_email_footer', $email );
