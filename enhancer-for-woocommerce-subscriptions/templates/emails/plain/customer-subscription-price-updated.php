<?php

/**
 * Subscription Price Updated Email.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/customer-subscription-price-updated.php.
 */
defined( 'ABSPATH' ) || exit;

echo esc_html( $email_heading ) . "\n\n";

/* translators: 1: Subscription number */
printf( esc_html__( 'The subscription price for your subscription #%s has been updated. You have to pay the updated price for the future renewals. Here\'s the details of your subscription.', 'enhancer-for-woocommerce-subscriptions' ), esc_html( $subscription->get_order_number() ) );

echo "\n\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";

foreach ( $price_changed_items as $changed ) {
	// translators: placeholder is localised new price string
	printf( esc_html__( 'New Price: %s', 'enhancer-for-woocommerce-subscriptions' ), wp_kses_post( $changed[ 'to_string' ] ) ) . "\n";
	// translators: placeholder is localised old price string
	printf( esc_html__( 'Old Price: %s', 'enhancer-for-woocommerce-subscriptions' ), wp_kses_post( $changed[ 'from_string' ] ) ) . "\n";
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
