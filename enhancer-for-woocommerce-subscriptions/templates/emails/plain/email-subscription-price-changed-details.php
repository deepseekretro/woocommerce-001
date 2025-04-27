<?php

/**
 * Subscription price changed details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/email-subscription-price-changed-details.php.
 */
defined( 'ABSPATH' ) || exit ;

echo "\n\n" . esc_html__( 'Subscription information', 'enhancer-for-woocommerce-subscriptions' ) . "\n\n" ;

// translators: placeholder is subscription's number
echo sprintf( wp_kses_post( _x( 'Subscription: %s', 'in plain emails for subscription information', 'enhancer-for-woocommerce-subscriptions' ) ), esc_html( $subscription->get_order_number() ) ) . "\n" ;
// translators: placeholder is either view url for the subscription
echo sprintf( wp_kses_post( _x( 'View subscription: %s', 'in plain emails for subscription information', 'enhancer-for-woocommerce-subscriptions' ) ), esc_url( $subscription->get_view_order_url() ) ) . "\n" ;

foreach ( $price_changed_items as $changed ) {
	// translators: placeholder is the new price for the subscription
	printf( wp_kses_post( _x( 'New Price: %s', 'in plain emails for subscription information', 'enhancer-for-woocommerce-subscriptions' ) ), wp_kses_post( $changed[ 'to_string' ] ) ) ;
	// translators: placeholder is the old price for the subscription
	printf( wp_kses_post( _x( 'Old Price: %s', 'in plain emails for subscription information', 'enhancer-for-woocommerce-subscriptions' ) ), wp_kses_post( $changed[ 'from_string' ] ) ) ;
}

echo "\n\n" ;
