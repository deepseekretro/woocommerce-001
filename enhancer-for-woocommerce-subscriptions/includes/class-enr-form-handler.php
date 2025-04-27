<?php

/**
 * Handle frontend forms.
 */
defined( 'ABSPATH' ) || exit ;

/**
 * ENR_Form_Handler class.
 */
class ENR_Form_Handler {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'wp_loaded', array( __CLASS__, 'add_to_cart_action' ) ) ;
	}

	/**
	 * Read posted data upon add to cart action.
	 */
	public static function add_to_cart_action() {
		if ( ! isset( $_REQUEST[ 'add-to-cart' ] ) || ! is_numeric( wc_clean( wp_unslash( $_REQUEST[ 'add-to-cart' ] ) ) ) ) {
			return ;
		}

		wc_nocache_headers() ;

		$product_id     = absint( wp_unslash( $_REQUEST[ 'add-to-cart' ] ) ) ;
		$adding_to_cart = wc_get_product( $product_id ) ;

		if ( $adding_to_cart && 'variable' === $adding_to_cart->get_type() ) {
			$variation_id   = empty( $_REQUEST[ 'variation_id' ] ) ? 0 : absint( wp_unslash( $_REQUEST[ 'variation_id' ] ) ) ;
			$adding_to_cart = wc_get_product( $variation_id ) ;
		}

		if ( $adding_to_cart ) {
			ENR_Product_Level_Subscribe_Now::instance()->read_posted_data( $_REQUEST, $adding_to_cart ) ;
		}
	}
}

ENR_Form_Handler::init() ;
