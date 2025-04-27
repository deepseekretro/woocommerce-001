<?php
/**
 * Subscription Price Updated Email.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-subscription-price-updated.php.
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
	/* translators: 1: Subscription number */
	printf( esc_html__( 'The subscription price for your subscription %s has been updated. You have to pay the updated price for the future renewals. Here\'s the details of your subscription.', 'enhancer-for-woocommerce-subscriptions' ), '<a href="' . esc_url( $subscription->get_view_order_url() ) . '">#' . esc_html( $subscription->get_order_number() ) . '</a>' );
	?>
</p>
<?php
/**
 * Add email subscription price changed details.
 * 
 * @since 1.0
 */
do_action( 'enr_wc_subscriptions_email_subscription_price_changed_details', $subscription, $price_changed_items, $sent_to_admin, $plain_text, $email );

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
