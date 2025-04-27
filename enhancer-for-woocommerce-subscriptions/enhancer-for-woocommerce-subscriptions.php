<?php

/**
 * Plugin Name: Enhancer for WooCommerce Subscriptions
 * Description: Additional features for WooCommerce Subscriptions such as price updation for existing users, separate shipping cycle, cancel delay, auto-renewal reminder, etc.
 * Version: 4.7.0
 * Author: Flintop
 * Author URI: https://woocommerce.com/vendor/flintop/
 * Text Domain: enhancer-for-woocommerce-subscriptions
 * Domain Path: /languages
 * Woo: 5834751:b0f115cc74f785a3e38e8aa056cebc4f
 * Tested up to: 6.7.1
 * WC tested up to: 9.5.1
 * WC requires at least: 3.5.0
 * WCS tested up to: 6.9.1
 * WCS requires at least: 3.0.14
 * Requires Plugins: woocommerce,woocommerce-subscriptions
 * Copyright: © 2024 Flintop
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Woo: 5834751:b0f115cc74f785a3e38e8aa056cebc4f

 */
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ENR_FILE' ) ) {
	define( 'ENR_FILE', __FILE__ );
}


// Include the main Enhancer class.
if ( ! class_exists( 'WC_Subscriptions_Enhancer', false ) ) {
	include_once dirname( ENR_FILE ) . '/includes/class-wc-subscriptions-enhancer.php';
}

/**
 * Add HPOS support.
 */
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

/**
 * Main instance of WC_Subscriptions_Enhancer.
 * Returns the main instance of WC_Subscriptions_Enhancer.
 *
 * @return WC_Subscriptions_Enhancer
 */
function _enr() {
	return WC_Subscriptions_Enhancer::instance();
}

/**
 * Run Enhancer for WooCommerce Subscriptions
 */
_enr();
