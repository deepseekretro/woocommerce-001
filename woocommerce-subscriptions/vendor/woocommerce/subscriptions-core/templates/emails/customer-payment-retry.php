<?php
/**
 * Customer payment retry email
 *
 * @package WooCommerce_Subscriptions/Templates/Emails
 * @version 7.3.0 - Updated for WC core email improvements.
 */
defined( 'ABSPATH' ) || exit;

$email_improvements_enabled = wcs_is_wc_feature_enabled( 'email_improvements' );

do_action( 'woocommerce_email_header', $email_heading, $email );

echo $email_improvements_enabled ? '<div class="email-introduction">' : '';

/* translators: %s: Customer first name */ ?>
<p><?php printf( esc_html__( 'Hi %s,', 'woocommerce-subscriptions' ), esc_html( $order->get_billing_first_name() ) ); ?></p>
<?php /* translators: %s: lowercase human time diff in the form returned by wcs_get_human_time_diff(), e.g. 'in 12 hours' */ ?>
<p><?php printf( esc_html_x( 'The automatic payment to renew your subscription has failed. We will retry the payment %s.', 'In customer renewal invoice email', 'woocommerce-subscriptions' ), esc_html( wcs_get_human_time_diff( $retry->get_time() ) ) ); ?></p>

<?php /* translators: %1$s %2$s: link markup to checkout payment url, note: no full stop due to url at the end */ ?>
<p><?php echo wp_kses( sprintf( _x( 'To reactivate the subscription now, you can also log in and pay for the renewal from your account page: %1$sPay Now &raquo;%2$s', 'In customer renewal invoice email', 'woocommerce-subscriptions' ), '<a href="' . esc_url( $order->get_checkout_payment_url() ) . '">', '</a>' ), array( 'a' => array( 'href' => true ) ) ); ?></p>

<?php echo $email_improvements_enabled ? '</div>' : ''; ?>

<?php
do_action( 'woocommerce_subscriptions_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo $email_improvements_enabled ? '<div class="email-additional-content">' : '';
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
	echo $email_improvements_enabled ? '</div>' : '';
}

do_action( 'woocommerce_email_footer', $email );
