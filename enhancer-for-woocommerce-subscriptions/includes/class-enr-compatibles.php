<?php

defined( 'ABSPATH' ) || exit;

/**
 * Enhancer for WooCommerce Subscriptions Compatibles handler.
 * 
 * @class ENR_Compatibles
 */
class ENR_Compatibles {

	/**
	 * Cached compatible plugins.
	 *
	 * @var array
	 */
	protected static $plugins = array();

	/**
	 * Get the compatible plugins to be registered.
	 *
	 * @since 4.5.0
	 * @return Array
	 */
	protected static function get_plugins() {
		/**
		 * Get the compatible plugins.
		 * 
		 * @since 4.5.0
		 * @param array $plugins Plugins.
		 */
		return apply_filters( 'enr_get_compatible_plugins', array(
			'dws-subscription-discount' => 'ENR_Compatible_DWS_Subscription_Discount',
				) );
	}

	/**
	 * Load the compatible plugin classes.
	 * 
	 * @since 4.5.0
	 */
	public static function load() {
		foreach ( self::get_plugins() as $plugin_key => $compatible_class ) {
			self::$plugins[ $plugin_key ] = new $compatible_class();

			if ( self::$plugins[ $plugin_key ]->is_active() ) {
				self::$plugins[ $plugin_key ]->init();
			}
		}
	}
}
