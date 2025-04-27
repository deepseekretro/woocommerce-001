<?php

/**
 * Our Template Hooks
 *
 * Action/filter hooks used for Our functions/templates.
 */
defined( 'ABSPATH' ) || exit ;

/**
 * My Account.
 */
add_filter( 'wcs_view_subscription_actions', '_enr_account_cancel_option_to_subscriber', 99, 2 ) ;
add_filter( 'woocommerce_subscriptions_switch_link', '_enr_account_switch_option_to_subscriber', 99, 4 ) ;
add_filter( 'wcs_subscription_details_table_before_dates', '_enr_account_shipping_details' ) ;
