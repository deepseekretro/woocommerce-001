<?php

use Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CartItemSchema;

/**
 * Store API integration class.
 *
 * @class ENR_Store_API
 * @package Class
 */
class ENR_Store_API {

	/**
	 * Plugin identifier, unique to each plugin.
	 *
	 * @var string
	 */
	const IDENTIFIER = 'enhancer-for-woocommerce-subscriptions';

	/**
	 * Bootstraps the class and hooks required data.
	 */
	public static function init() {
		self::extend_store();
		add_filter( 'woocommerce_get_item_data', __CLASS__ . '::get_item_data', 10, 2 );
	}

	/**
	 * Register cart data handler.
	 */
	public static function extend_store() {
		if ( ! function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
			return;
		}

		woocommerce_store_api_register_endpoint_data(
				array(
					'endpoint'        => CartItemSchema::IDENTIFIER,
					'namespace'       => self::IDENTIFIER,
					'data_callback'   => array( __CLASS__, 'extend_cart_item_data' ),
					'schema_callback' => array( __CLASS__, 'extend_cart_item_schema' ),
					'schema_type'     => ARRAY_A,
				)
		);

		woocommerce_store_api_register_endpoint_data(
				array(
					'endpoint'        => CartSchema::IDENTIFIER,
					'namespace'       => self::IDENTIFIER,
					'data_callback'   => array( __CLASS__, 'extend_cart_data' ),
					'schema_callback' => array( __CLASS__, 'extend_cart_schema' ),
					'schema_type'     => ARRAY_A,
				)
		);

		woocommerce_store_api_register_update_callback(
				array(
					'namespace' => self::IDENTIFIER,
					'callback'  => array( __CLASS__, 'handle_update_endpoint' ),
				)
		);
	}

	/**
	 * Gets extension data to cart route responses.
	 *
	 * @return array
	 */
	public static function get_cart_data( $cart_item = array() ) {
		$cart_data = array(
			'subscribe_label'         => null,
			'subscribed_key'          => null,
			'is_available'            => false,
			'is_subscribed'           => false,
			'force_subscribe'         => false,
			'chosen_plan'             => null,
			'chosen_interval'         => null,
			'chosen_period'           => null,
			'chosen_length'           => null,
			'subscribed_plan_type'    => null,
			'subscribed_price_string' => null,
			'subscribe_type'          => null,
			'default_plan'            => null,
			'available_plans'         => array(),
			'period_to_subscribe'     => array(),
			'interval_to_subscribe'   => array(),
			'length_to_subscribe'     => array(),
			'min_length'              => array(),
			'max_length'              => array(),
		);

		if ( ENR_Cart_Level_Subscribe_Now::instance()->is_available( WC()->cart ) ) {
			$cart_data                   = ENR_Cart_Level_Subscribe_Now::instance()->get_subscribe_form_args();
			$cart_data[ 'is_available' ] = true;
			$raw_plans                   = $cart_data[ 'available_plans' ];
			$raw_intervals               = $cart_data[ 'interval_to_subscribe' ];
			$raw_periods                 = $cart_data[ 'period_to_subscribe' ];
			$raw_lengths                 = $cart_data[ 'length_to_subscribe' ];

			$plans = array();
			if ( ! empty( $raw_plans ) ) {
				foreach ( $raw_plans as $plan_id ) {
					$plans[] = array(
						'id'    => $plan_id,
						'title' => wp_kses_post( get_the_title( $plan_id ) ),
					);
				}
			}

			$intervals = array();
			foreach ( wcs_get_subscription_period_interval_strings() as $value => $label ) {
				if ( isset( $raw_intervals[ 'min' ][ $cart_data[ 'chosen_period' ] ], $raw_intervals[ 'max' ][ $cart_data[ 'chosen_period' ] ] ) && $value >= $raw_intervals[ 'min' ][ $cart_data[ 'chosen_period' ] ] && $value <= $raw_intervals[ 'max' ][ $cart_data[ 'chosen_period' ] ] ) {
					$intervals[] = array(
						'key'   => $value,
						'title' => $label,
					);
				}
			}

			$periods = array();
			foreach ( wcs_get_subscription_period_strings() as $value => $label ) {
				if ( in_array( $value, ( array ) $raw_periods ) ) {
					$periods[] = array(
						'key'   => $value,
						'title' => $label,
					);
				}
			}

			$lengths = array();
			if ( isset( $raw_lengths[ 'min' ][ $cart_data[ 'chosen_period' ] ], $raw_lengths[ 'max' ][ $cart_data[ 'chosen_period' ] ] ) ) {
				$add_never_expire = true;

				foreach ( _enr_get_subscription_length_ranges( $cart_data[ 'chosen_period' ], $cart_data[ 'chosen_interval' ] ) as $value => $label ) {
					if ( $value >= $raw_lengths[ 'min' ][ $cart_data[ 'chosen_period' ] ] ) {
						if ( '0' === $raw_lengths[ 'max' ][ $cart_data[ 'chosen_period' ] ] ) {
							$add_never_expire = true;
							$lengths[]        = array(
								'key'   => $value,
								'title' => $label,
							);
						} else if ( $value <= $raw_lengths[ 'max' ][ $cart_data[ 'chosen_period' ] ] ) {
							$add_never_expire = false;
							$lengths[]        = array(
								'key'   => $value,
								'title' => $label,
							);
						}
					}
				}

				if ( $add_never_expire ) {
					$lengths[] = array(
						'key'   => 0,
						'title' => esc_html__( 'Never expire', 'enhancer-for-woocommerce-subscriptions' ),
					);
				}
			}

			$cart_data[ 'available_plans' ]       = $plans;
			$cart_data[ 'interval_to_subscribe' ] = $intervals;
			$cart_data[ 'period_to_subscribe' ]   = $periods;
			$cart_data[ 'length_to_subscribe' ]   = $lengths;
			$cart_data[ 'min_length' ]            = current( $lengths );
			$cart_data[ 'max_length' ]            = end( $lengths );
		} elseif ( ! empty( $cart_item[ 'data' ] ) && ENR_Product_Level_Subscribe_Now::instance()->is_available( $cart_item[ 'data' ] ) ) {
			$cart_data                   = ENR_Product_Level_Subscribe_Now::instance()->get_subscribe_form_args( $cart_item[ 'data' ] );
			$cart_data[ 'is_available' ] = true;
		}

		return $cart_data;
	}

	/**
	 * Adds meta data so it can be displayed in the Cart.
	 */
	public static function get_item_data( $other_data, $cart_item ) {
		$product = $cart_item[ 'data' ];

		if ( ! WC_Subscriptions_Product::is_subscription( $product ) ) {
			return $other_data;
		}

		if ( ENR_Shipping_Cycle::shipping_cycle_enabled( $product ) ) {
			$shipping_frequency_string = ENR_Shipping_Cycle::prepare_frequency_string( array(
						'is_synced'      => ENR_Shipping_Cycle::is_frequency_synced( $product ),
						'interval'       => $product->get_meta( ENR_PREFIX . 'shipping_period_interval' ),
						'period'         => $product->get_meta( ENR_PREFIX . 'shipping_period' ),
						'sync_date_day'  => $product->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_day' ),
						'sync_date_week' => $product->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_week' ),
					) );

			$other_data[] = array(
				'name'                                     => '',
				'value'                                    => $shipping_frequency_string,
				'hidden'                                   => true,
				'__experimental_woocommerce_blocks_hidden' => false,
			);
		}

		return $other_data;
	}

	/**
	 * Register product data into cart/items endpoint.
	 *
	 * @param  array $cart_item Current cart item data.
	 * @return array $item_data Registered deposits product data.
	 */
	public static function extend_cart_item_data( $cart_item ) {
		$item_data = self::get_cart_data( $cart_item );
		return $item_data;
	}

	/**
	 * Register product schema into cart/items endpoint.
	 *
	 * @return array Registered schema.
	 */
	public static function extend_cart_item_schema() {
		$schema = self::extend_cart_schema();
		return $schema;
	}

	/**
	 * Adds extension data to cart route responses.
	 *
	 * @return array
	 */
	public static function extend_cart_data() {
		$cart_data = self::get_cart_data();
		return $cart_data;
	}

	/**
	 * Register schema into cart endpoint.
	 *
	 * @return  array  Registered schema.
	 */
	public static function extend_cart_schema() {
		return array(
			'is_available'            => array(
				'description' => __( 'Check if cart/product level is enabled.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'subscribe_label'         => array(
				'description' => __( 'Subscribe label.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => array( 'string', 'null' ),
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'subscribed_key'          => array(
				'description' => __( 'Subscribed key.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => array( 'string', 'null' ),
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'subscribe_type'          => array(
				'description' => __( 'Subscribe type.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => array( 'string', 'null' ),
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'subscribed_price_string' => array(
				'description' => __( 'Subscribed price string.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => array( 'string', 'null' ),
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'chosen_interval'         => array(
				'description' => __( 'Chosen subscription interval.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => array( 'integer', 'null' ),
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'chosen_period'           => array(
				'description' => __( 'Chosen subscription period.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => array( 'string', 'null' ),
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'chosen_length'           => array(
				'description' => __( 'Chosen subscription length.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => array( 'integer', 'null' ),
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'subscribed_plan_type'    => array(
				'description' => __( 'Subscribed plan type.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => array( 'string', 'null' ),
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'is_subscribed'           => array(
				'description' => __( 'Check if cart level is subscribed.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'force_subscribe'         => array(
				'description' => __( 'Check if cart level subscription is forced.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'available_plans'         => array(
				'description' => __( 'Available cart level subscription plans.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'default_plan'            => array(
				'description' => __( 'Cart level default subscription plan.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'chosen_plan'             => array(
				'description' => __( 'Cart level chosen subscription plan.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => array( 'integer', 'null' ),
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'min_length'              => array(
				'description' => __( 'Minimum subscription length.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'max_length'              => array(
				'description' => __( 'Maximum subscription length.', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
		);
	}

	/**
	 * Handle our actions through StoreAPI.
	 *
	 * @param array $args
	 */
	public static function handle_update_endpoint( $args ) {
		switch ( $args[ 'action' ] ) {
			case 'subscribe_now':
				if ( isset( $args[ 'value' ][ 'subscribed' ] ) ) {
					$posted = array(
						'enr_subscribed'                 => $args[ 'value' ][ 'subscribed' ],
						'enr_subscribed_plan'            => $args[ 'value' ][ 'subscribed_plan' ],
						'enr_subscribed_period_interval' => $args[ 'value' ][ 'subscribed_interval' ],
						'enr_subscribed_period'          => $args[ 'value' ][ 'subscribed_period' ],
						'enr_subscribed_length'          => $args[ 'value' ][ 'subscribed_length' ],
					);
				} else {
					$posted = array();
				}

				ENR_Cart_Level_Subscribe_Now::instance()->read_posted_data( $posted );
				break;
		}
	}
}
