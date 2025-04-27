<?php
/**
 * Trial Ending Reminder Email.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-trial-ending-reminder.php.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Add email header.
 * 
 * @since 1.0
 */
do_action( 'woocommerce_email_header', $email_heading, $email );
?>
<p><?php esc_html_e( 'Your subscription trial is going to end soon. Here\'s the details of your subscription.', 'enhancer-for-woocommerce-subscriptions' ); ?></p>
<?php
/**
 * Add email trial end details.
 * 
 * @since 1.0
 */
do_action( 'enr_wc_subscriptions_email_subscription_trial_end_details', $subscription, $sent_to_admin, $plain_text, $email );

/**
 * Add email order details.
 * 
 * @since 1.0
 */
do_action( 'woocommerce_subscriptions_email_order_details', $subscription, $sent_to_admin, $plain_text, $email );

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
