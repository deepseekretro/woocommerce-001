<?php

/**
 * Trial Ending Reminder Email.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/customer-trial-ending-reminder.php.
 */
defined( 'ABSPATH' ) || exit;

echo esc_html( $email_heading ) . "\n\n";

esc_html_e( 'Your subscription trial is going to end soon. Here\'s the details of your subscription.', 'enhancer-for-woocommerce-subscriptions' );

echo "\n\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";

/**
 * Add email order details.
 * 
 * @since 1.0
 */
do_action( 'woocommerce_subscriptions_email_order_details', $subscription, $sent_to_admin, $plain_text, $email );

echo "\n----------\n\n";

$end_time = $subscription->get_time( 'trial_end', 'site' );

if ( ! empty( $end_time ) ) {
	// translators: placeholder is localised date string
	echo sprintf( esc_html__( 'Trial End Date: %s', 'enhancer-for-woocommerce-subscriptions' ), esc_html( date_i18n( wc_date_format(), $end_time ) ) ) . "\n";
}

/**
 * Add email order meta.
 * 
 * @since 1.0
 */
do_action( 'woocommerce_email_order_meta', $subscription, $sent_to_admin, $plain_text, $email );

// translators: view subscription url
echo "\n" . sprintf( esc_html_x( 'View Subscription: %s', 'in plain emails for subscription information', 'enhancer-for-woocommerce-subscriptions' ), esc_url( $subscription->get_view_order_url() ) ) . "\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

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
