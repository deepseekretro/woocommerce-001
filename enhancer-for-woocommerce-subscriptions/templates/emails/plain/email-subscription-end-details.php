<?php

/**
 * Subscription end details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/email-subscription-end-details.php.
 */
defined( 'ABSPATH' ) || exit ;

echo "\n\n" . esc_html__( 'Subscription information', 'enhancer-for-woocommerce-subscriptions' ) . "\n\n" ;

// translators: placeholder is subscription's number
echo sprintf( wp_kses_post( _x( 'Subscription: %s', 'in plain emails for subscription information', 'enhancer-for-woocommerce-subscriptions' ) ), esc_html( $subscription->get_order_number() ) ) . "\n" ;
// translators: placeholder is either view url for the subscription
echo sprintf( wp_kses_post( _x( 'View subscription: %s', 'in plain emails for subscription information', 'enhancer-for-woocommerce-subscriptions' ) ), esc_url( $subscription->get_view_order_url() ) ) . "\n" ;
// translators: placeholder is the formatted order total for the subscription
printf( wp_kses_post( _x( 'Price: %s', 'in plain emails for subscription information', 'enhancer-for-woocommerce-subscriptions' ) ), wp_kses_post( $subscription->get_formatted_order_total() ) ) ;
// translators: placeholder is localised end date"
echo sprintf( wp_kses_post( _x( 'End date: %s', 'in plain emails for subscription information', 'enhancer-for-woocommerce-subscriptions' ) ), esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'end', 'site' ) ) ) ) . "\n" ;

echo "\n\n" ;
