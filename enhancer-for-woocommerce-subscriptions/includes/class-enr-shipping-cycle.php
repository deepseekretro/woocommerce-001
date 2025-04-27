<?php

/**
 * Shipping Cycle Product and Order handler.
 *
 * @class ENR_Shipping_Cycle
 * @package Class
 */
class ENR_Shipping_Cycle {

	/**
	 * Get an array of meta.
	 * 
	 * @var array 
	 */
	protected static $meta = array(
		'_enr_enable_seperate_shipping_cycle'    => 'no',
		'_enr_shipping_period_interval'          => '',
		'_enr_shipping_period'                   => '',
		'_enr_shipping_frequency_sync_date_day'  => '0',
		'_enr_shipping_frequency_sync_date_week' => '0',
	);

	/**
	 * Get an array of weekdays.
	 * 
	 * @var array 
	 */
	protected static $weekdays = array(
		1 => 'Monday',
		2 => 'Tuesday',
		3 => 'Wednesday',
		4 => 'Thursday',
		5 => 'Friday',
		6 => 'Saturday',
		7 => 'Sunday',
	);

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_filter( 'wcs_new_order_types', __CLASS__ . '::register_our_order_types' );
		add_filter( 'wcs_additional_related_order_relation_types', __CLASS__ . '::register_our_order_relational_types' );

		add_filter( 'woocommerce_subscription_price_string_details', __CLASS__ . '::prepare_frequency_args_for_subscription', 50, 2 );
		add_filter( 'woocommerce_subscriptions_product_price_string', __CLASS__ . '::get_product_frequency_string', 50, 2 );
		add_filter( 'woocommerce_subscription_price_string', __CLASS__ . '::get_subscription_frequency_string', 50, 2 );
		add_action( 'wcs_subscription_schedule_after_billing_schedule', __CLASS__ . '::show_frequency_string' );

		add_filter( 'enr_get_subscription_meta', __CLASS__ . '::get_subscription_meta' );
		add_action( 'woocommerce_scheduled_subscription_payment', __CLASS__ . '::maybe_enable_for_old_subscription', -2 );
		add_action( 'woocommerce_scheduled_subscription_payment', __CLASS__ . '::shipping_done', 2 );
		add_filter( 'wcs_admin_subscription_related_orders_to_display', __CLASS__ . '::output_shipping_fulfilment_orders', 10, 3 );
		add_action( 'manage_shop_order_posts_custom_column', __CLASS__ . '::show_relationship', 10 );
	}

	/*
	  |--------------------------------------------------------------------------
	  | Shipping Fulfilment Order Types Registration Methods
	  |--------------------------------------------------------------------------
	 */

	/**
	 * Register our order types.
	 * 
	 * @param array $order_types
	 * @return array
	 */
	public static function register_our_order_types( $order_types ) {
		$order_types[] = 'enr_shipping_fulfilment_order';
		return $order_types;
	}

	/**
	 * Register our order relational types.
	 * 
	 * @param array $order_types
	 * @return array
	 */
	public static function register_our_order_relational_types( $order_types ) {
		$order_types[] = 'enr_shipping_fulfilment';
		return $order_types;
	}

	/*
	  |--------------------------------------------------------------------------
	  | Shipping Price String Methods
	  |--------------------------------------------------------------------------
	 */

	/**
	 * Prepare the frequency string to display.
	 * 
	 * @param array $args
	 * @return string
	 */
	public static function prepare_frequency_string( $args ) {
		$args = wp_parse_args( $args, array(
			'is_synced'      => false,
			'interval'       => '',
			'period'         => '',
			'sync_date_day'  => '0',
			'sync_date_week' => '0',
				) );

		// translators: 1: period
		$subscription_string = sprintf( __( 'Delivered every %s', 'enhancer-for-woocommerce-subscriptions' ), wcs_get_subscription_period_strings( $args[ 'interval' ], $args[ 'period' ] ) );

		if ( $args[ 'is_synced' ] ) {
			switch ( $args[ 'period' ] ) {
				case 'week':
					$payment_day_of_week = WC_Subscriptions_Synchroniser::get_weekday( $args[ 'sync_date_week' ] );
					if ( 1 === $args[ 'interval' ] ) {
						// translators: 1: day of the week (e.g. "Delivered every Wednesday").
						$subscription_string = sprintf( __( 'Delivered every %s', 'enhancer-for-woocommerce-subscriptions' ), $payment_day_of_week );
					} else {
						// translators: 1: period 2: day of the week (e.g. "Delivered every 2nd week on Wednesday").
						$subscription_string = sprintf( __( 'Delivered every %1$s on %2$s', 'enhancer-for-woocommerce-subscriptions' ), wcs_get_subscription_period_strings( $args[ 'interval' ], $args[ 'period' ] ), $payment_day_of_week );
					}
					break;
				case 'month':
					if ( 1 === $args[ 'interval' ] ) {
						if ( $args[ 'sync_date_day' ] > 27 ) {
							$subscription_string = __( 'Delivered on the last day of each month', 'enhancer-for-woocommerce-subscriptions' );
						} else {
							// translators: 1: day of the month (e.g. "23rd") (e.g. "Delivered every 23rd of each month").
							$subscription_string = sprintf( __( 'Delivered on the %s of each month', 'enhancer-for-woocommerce-subscriptions' ), _enr_append_numeral_suffix( $args[ 'sync_date_day' ] ) );
						}
					} elseif ( $args[ 'sync_date_day' ] > 27 ) {
						// translators: 1: interval (e.g. "3rd") (e.g. "Delivered on the last day of every 3rd month").
						$subscription_string = sprintf( __( 'Delivered on the last day of every %s month', 'enhancer-for-woocommerce-subscriptions' ), _enr_append_numeral_suffix( $args[ 'interval' ] ) );
					} else {
						// translators: 1: <date> day of every, 2: <interval> month (e.g. "Delivered on the 23rd day of every 2nd month").
						$subscription_string = sprintf( __( 'Delivered on the %1$s day of every %2$s month', 'enhancer-for-woocommerce-subscriptions' ), _enr_append_numeral_suffix( $args[ 'sync_date_day' ] ), _enr_append_numeral_suffix( $args[ 'interval' ] ) );
					}
					break;
			}
		}

		$shipping_string = '<span class="subscription-enr-shipping-cycle-details">';
		$shipping_string .= $subscription_string;
		$shipping_string .= '</span>';
		return $shipping_string;
	}

	/**
	 * Prepare the array of shipping data required to display the frequency string by subscription.
	 * 
	 * @param array $args
	 * @param WC_Subscription $subscription
	 * @return array
	 */
	public static function prepare_frequency_args_for_subscription( $args, $subscription ) {
		if ( self::shipping_cycle_enabled( $subscription ) ) {
			$args[ 'enr_shipping_cycle_enabled' ]            = true;
			$args[ 'enr_shipping_synced' ]                   = self::is_frequency_synced( $subscription );
			$args[ 'enr_shipping_period_interval' ]          = $subscription->get_meta( ENR_PREFIX . 'shipping_period_interval' );
			$args[ 'enr_shipping_period' ]                   = $subscription->get_meta( ENR_PREFIX . 'shipping_period' );
			$args[ 'enr_shipping_frequency_sync_date_day' ]  = $subscription->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_day' );
			$args[ 'enr_shipping_frequency_sync_date_week' ] = $subscription->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_week' );
		}

		return $args;
	}

	/**
	 * Get the frequency string for the shipping cycle to display in shop/product page.
	 * 
	 * @param string $subscription_string
	 * @param WC_Product $product
	 * @return string
	 */
	public static function get_product_frequency_string( $subscription_string, $product ) {
		if ( self::shipping_cycle_enabled( $product ) ) {
			$subscription_string .= '</br>';
			$subscription_string .= self::prepare_frequency_string( array(
						'is_synced'      => self::is_frequency_synced( $product ),
						'interval'       => $product->get_meta( ENR_PREFIX . 'shipping_period_interval' ),
						'period'         => $product->get_meta( ENR_PREFIX . 'shipping_period' ),
						'sync_date_day'  => $product->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_day' ),
						'sync_date_week' => $product->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_week' ),
					) );
		}

		return $subscription_string;
	}

	/**
	 * Get the frequency string for the shipping cycle to display in cart/admin screen/my account page.
	 * 
	 * @param string $subscription_string
	 * @param array $subscription_details
	 * @return string
	 */
	public static function get_subscription_frequency_string( $subscription_string, $subscription_details ) {
		if ( isset( $subscription_details[ 'enr_shipping_cycle_enabled' ] ) ) {
			$subscription_string .= '&nbsp;-&nbsp;';
			$subscription_string .= self::prepare_frequency_string( array(
						'is_synced'      => $subscription_details[ 'enr_shipping_synced' ],
						'interval'       => $subscription_details[ 'enr_shipping_period_interval' ],
						'period'         => $subscription_details[ 'enr_shipping_period' ],
						'sync_date_day'  => $subscription_details[ 'enr_shipping_frequency_sync_date_day' ],
						'sync_date_week' => $subscription_details[ 'enr_shipping_frequency_sync_date_week' ],
					) );
		}

		return $subscription_string;
	}

	/**
	 * Show the frequency string in edit subscription screen. 
	 * 
	 * @param WC_Subscription $subscription
	 */
	public static function show_frequency_string( $subscription ) {
		if ( self::shipping_cycle_enabled( $subscription ) ) {
			$subscription_string = '</br>';
			$subscription_string .= self::prepare_frequency_string( array(
						'is_synced'      => self::is_frequency_synced( $subscription ),
						'interval'       => $subscription->get_meta( ENR_PREFIX . 'shipping_period_interval' ),
						'period'         => $subscription->get_meta( ENR_PREFIX . 'shipping_period' ),
						'sync_date_day'  => $subscription->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_day' ),
						'sync_date_week' => $subscription->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_week' ),
					) );

			echo wp_kses_post( $subscription_string );
		}
	}

	/*
	  |--------------------------------------------------------------------------
	  | Helper Methods
	  |--------------------------------------------------------------------------
	 */

	/**
	 * Check if the shipping cycle is enabled ?
	 * 
	 * @param mixed $object WC_Product|WC_Cart|WC_Subscription instance.
	 * @return bool 
	 */
	public static function shipping_cycle_enabled( $object ) {
		$enabled = false;

		if ( is_a( $object, 'WC_Product' ) ) {
			if ( 'yes' !== $object->get_meta( ENR_PREFIX . 'enable_seperate_shipping_cycle' ) ) {
				return false;
			}

			if ( ! self::is_valid_shipping_period_and_interval( WC_Subscriptions_Product::get_period( $object ), WC_Subscriptions_Product::get_interval( $object ), $object->get_meta( ENR_PREFIX . 'shipping_period' ), $object->get_meta( ENR_PREFIX . 'shipping_period_interval' ) ) ) {
				return false;
			}

			if ( $object->is_virtual() ) {
				return false;
			}

			$enabled = true;
		} else if ( is_a( $object, 'WC_Cart' ) ) {
			if ( 'yes' !== wcs_cart_pluck( $object, ENR_PREFIX . 'enable_seperate_shipping_cycle' ) ) {
				return false;
			}

			if ( ! self::is_valid_shipping_period_and_interval( wcs_cart_pluck( $object, 'subscription_period' ), wcs_cart_pluck( $object, 'subscription_period_interval' ), wcs_cart_pluck( $object, ENR_PREFIX . 'shipping_period' ), wcs_cart_pluck( $object, ENR_PREFIX . 'shipping_period_interval' ) ) ) {
				return false;
			}

			if ( ! empty( $object->cart_contents ) ) {
				foreach ( $object->cart_contents as $cart_item ) {
					if ( is_object( $cart_item[ 'data' ] ) && $cart_item[ 'data' ]->is_virtual() ) {
						return false;
					}
				}
			}

			$enabled = true;
		} else if ( is_a( $object, 'WC_Subscription' ) ) {
			if ( 'yes' !== $object->get_meta( ENR_PREFIX . 'enable_seperate_shipping_cycle' ) ) {
				return false;
			}

			if ( ! self::is_valid_shipping_period_and_interval( $object->get_billing_period(), $object->get_billing_interval(), $object->get_meta( ENR_PREFIX . 'shipping_period' ), $object->get_meta( ENR_PREFIX . 'shipping_period_interval' ) ) ) {
				return false;
			}

			foreach ( $object->get_items( 'line_item' ) as $item ) {
				$product = $item->get_product();

				if ( $product && $product->is_virtual() ) {
					return false;
				}
			}

			// Prevent from Trial subscription 
			if ( $object->get_time( 'trial_end' ) > time() ) {
				return false;
			}

			$enabled = true;
		}

		/**
		 * Is shipping cycle enabled on object?
		 * 
		 * @param bool $enabled
		 * @param mixed $object WC_Product|WC_Cart|WC_Subscription instance.
		 * @since 1.0
		 */
		return ( bool ) apply_filters( 'enr_shipping_cycle_enabled', $enabled, $object );
	}

	/**
	 * Check if the shipping cycle can be scheduled?
	 * 
	 * @param WC_Subscription $subscription
	 * @return bool
	 */
	public static function can_be_scheduled( $subscription ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription );
		if ( ! $subscription ) {
			return false;
		}

		/**
		 * Can shipping cycle be scheduled?
		 * 
		 * @param bool $enabled
		 * @param WC_Subscription $subscription 
		 * @since 1.0
		 */
		return ( bool ) apply_filters( 'enr_shipping_cycle_can_be_scheduled', self::shipping_cycle_enabled( $subscription ), $subscription );
	}

	/**
	 * Validate shipping period and interval based upon billing period and interval.
	 * 
	 * @param string $billing_period Subscription billing period
	 * @param int $billing_interval Subscription billing interval
	 * @param string $shipping_period Shipping period
	 * @param int $shipping_interval Shipping interval
	 * @return bool
	 */
	public static function is_valid_shipping_period_and_interval( $billing_period, $billing_interval, $shipping_period, $shipping_interval ) {
		$valid = false;

		if ( ! empty( $shipping_interval ) && is_numeric( $shipping_interval ) && $shipping_interval > 0 ) {
			$interval_ranges = self::get_shipping_interval_options( $billing_period, $billing_interval, $shipping_period );
			$valid           = $shipping_interval <= max( $interval_ranges ) ? true : false;
		}

		return $valid;
	}

	/**
	 * Check if a given subscription's shipping frequency is synced to a certain day.
	 *
	 * @param WC_Subscription|WC_Product $object
	 * @return bool
	 */
	public static function is_frequency_synced( $object ) {
		$period = $object->get_meta( ENR_PREFIX . 'shipping_period' );

		if ( 'day' === $period ) {
			return false;
		}

		if ( 'week' === $period ) {
			$sync_date = $object->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_week' );
		} else {
			$sync_date = $object->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_day' );
		}

		if ( is_numeric( $sync_date ) && $sync_date > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Return the array of shipping interval options.
	 * 
	 * @param string $billing_period Subscription billing period
	 * @param int $billing_interval Subscription billing interval
	 * @param string $shipping_period Shipping period
	 * @return array
	 */
	public static function get_shipping_interval_options( $billing_period, $billing_interval, $shipping_period ) {
		$prorated_length = 0;

		switch ( $shipping_period ) {
			case 'day':
				switch ( $billing_period ) {
					case 'day':
						$prorated_length = 1 * $billing_interval;
						break;
					case 'week':
						$prorated_length = 7 * $billing_interval;
						break;
					case 'month':
						$prorated_length = 28 * $billing_interval;
						break;
					case 'year':
						$prorated_length = 365 * $billing_interval;
						break;
				}
				break;
			case 'week':
				switch ( $billing_period ) {
					case 'week':
						$prorated_length = 1 * $billing_interval;
						break;
					case 'month':
						$prorated_length = 4 * $billing_interval;
						break;
					case 'year':
						$prorated_length = 52 * $billing_interval;
						break;
				}
				break;
			case 'month':
				switch ( $billing_period ) {
					case 'month':
						$prorated_length = 1 * $billing_interval;
						break;
					case 'year':
						$prorated_length = 12 * $billing_interval;
						break;
				}
				break;
			case 'year':
				switch ( $billing_period ) {
					case 'year':
						$prorated_length = 1 * $billing_interval;
						break;
				}
				break;
		}

		return range( 0, max( 0, $prorated_length - 1 ) );
	}

	/**
	 * Retrieve the shipping dates to schedule before the given timestamp.
	 * 
	 * @param int $timestamp
	 * @return array
	 */
	public static function get_shipping_dates( $timestamp, $subscription ) {
		$_timestamp      = wcs_date_to_time( gmdate( 'Y-m-d', absint( $timestamp ) ) );
		$period          = $subscription->get_meta( ENR_PREFIX . 'shipping_period' );
		$period_interval = absint( $subscription->get_meta( ENR_PREFIX . 'shipping_period_interval' ) );
		$shipping_dates  = array();

		if ( $_timestamp > 0 && $period_interval > 0 ) {
			$saved_fulfilment_dates = $subscription->get_meta( ENR_PREFIX . 'shipping_fulfilment_dates', true );

			if ( ! empty( $saved_fulfilment_dates ) ) {
				$next_shipping_time = current( $saved_fulfilment_dates );
			} else if ( self::is_frequency_synced( $subscription ) ) {
				if ( 'week' === $period ) {
					$sync_week          = $subscription->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_week' );
					$next_shipping_time = strtotime( self::$weekdays[ $sync_week ] );
				} else {
					$sync_day           = $subscription->get_meta( ENR_PREFIX . 'shipping_frequency_sync_date_day' );
					$sync_month         = gmdate( 'm' );
					$sync_year          = gmdate( 'Y' );
					$next_shipping_time = strtotime( "{$sync_year}-{$sync_month}-{$sync_day}" );
				}
			} else {
				$next_shipping_time = _enr_get_time( 'timestamp', array( 'time' => wcs_add_time( $period_interval, $period, time() ) ) );
			}

			while ( $next_shipping_time < $_timestamp ) {
				$shipping_dates[]   = $next_shipping_time;
				$next_shipping_time = _enr_get_time( 'timestamp', array( 'time' => wcs_add_time( $period_interval, $period, $next_shipping_time ) ) );
			}
		}

		/**
		 * Get the shipping fulfilment dates.
		 * 
		 * @param array $shipping_dates
		 * @param WC_Subscription $subscription 
		 * @param int $timestamp 
		 * @since 1.0
		 */
		return apply_filters( 'enr_get_shipping_fulfilment_dates', $shipping_dates, $subscription, $timestamp );
	}

	/**
	 * Prepare and save the shipping fulfilment dates once for every billing cycle.
	 * And schedule the shipping fulfilment orders.
	 * 
	 * @param WC_Subscription $subscription
	 * @param int $timestamp
	 * @param array $action_args
	 */
	public static function schedule_shipping_fulfilment_orders( $subscription, $timestamp, $action_args ) {
		$shipping_dates = self::get_shipping_dates( $timestamp, $subscription );

		if ( ! empty( $shipping_dates ) ) {
			foreach ( $shipping_dates as $shippment_time ) {
				if ( time() <= $shippment_time ) {
					as_schedule_single_action( $shippment_time, 'enr_woocommerce_scheduled_subscription_shipping_fulfilment_order', $action_args );
				}
			}
		}

		$subscription->update_meta_data( ENR_PREFIX . 'shipping_fulfilment_dates', $shipping_dates );
		$subscription->save_meta_data();
	}

	/**
	 * Get shipping fulfilment meta paired with default value.
	 * 
	 * @param array $meta
	 * @return array
	 */
	public static function get_subscription_meta( $meta ) {
		$meta = array_merge( $meta, self::$meta );
		return $meta;
	}

	/**
	 * Check whether the shipping cycle is enabled for old subscription and save the shipping in renewal order.
	 * 
	 * @param int $subscription_id
	 */
	public static function maybe_enable_for_old_subscription( $subscription_id ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription_id );
		if ( ! $subscription ) {
			return;
		}

		if ( self::shipping_cycle_enabled( $subscription ) ) {
			return;
		}

		$items   = $subscription->get_items( 'line_item' );
		$enabled = false;

		if ( ! empty( $items ) ) {
			foreach ( $items as $item ) {
				$product = $item->get_product();

				if ( $product && $product->is_type( array( 'subscription', 'subscription_variation' ) ) && self::shipping_cycle_enabled( $product ) && 'yes' === $product->get_meta( ENR_PREFIX . 'enable_seperate_shipping_cycle_for_old_subscriptions' ) ) {
					$enabled = true;
					break;
				}
			}
		}

		if ( $enabled ) {
			foreach ( self::$meta as $key => $default_value ) {
				$subscription->update_meta_data( $key, WC_Subscriptions_Product::get_meta_data( $product, $key, $default_value ) );
				$subscription->save_meta_data();
			}

			if ( self::shipping_cycle_enabled( $subscription ) ) {
				/**
				 * Trigger shipping fulfilment enabled for old orders.
				 * 
				 * @param WC_Subscription $subscription 
				 * @since 1.0
				 */
				do_action( 'enr_wc_subscriptions_shipping_fulfilment_enabled_for_old_orders', $subscription );
			}
		}
	}

	/**
	 * Save the shipping in renewal order.
	 * 
	 * @param int $subscription_id
	 */
	public static function shipping_done( $subscription_id ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription_id );
		if ( ! $subscription ) {
			return;
		}

		$renewal_order = $subscription->get_last_order( 'all', 'renewal' );
		if ( $renewal_order ) {
			$renewal_order->update_meta_data( ENR_PREFIX . 'shipping_fulfilment_dates', $subscription->get_meta( ENR_PREFIX . 'shipping_fulfilment_dates', true ) );
			$renewal_order->update_meta_data( ENR_PREFIX . 'shipping_fulfilment_orders', $subscription->get_meta( ENR_PREFIX . 'shipping_fulfilment_orders', true ) );
			$renewal_order->save_meta_data();
		}

		$subscription->delete_meta_data( ENR_PREFIX . 'shipping_fulfilment_dates' );
		$subscription->delete_meta_data( ENR_PREFIX . 'shipping_fulfilment_orders' );
		$subscription->save_meta_data();
	}

	/**
	 * Displays the shipping fulfilment orders in the Related Orders meta box.
	 * 
	 * @param array $orders_to_display
	 * @param WC_Subscription[] $subscriptions
	 * @param WC_Order $order
	 * @return array
	 */
	public static function output_shipping_fulfilment_orders( $orders_to_display, $subscriptions, $order ) {
		$orders_by_type = array();

		if ( ! wcs_is_subscription( $order->get_id() ) && wcs_order_contains_renewal( $order->get_id() ) ) {
			$shipping_fulfilment_orders = $order->get_meta( ENR_PREFIX . 'shipping_fulfilment_orders', true );

			if ( ! empty( $shipping_fulfilment_orders ) ) {
				$orders_by_type[ 'shipping_fulfilment' ] = $shipping_fulfilment_orders;
			}
		} else {
			foreach ( $subscriptions as $subscription ) {
				$orders_by_type[ 'shipping_fulfilment' ] = $subscription->get_related_orders( 'ids', 'enr_shipping_fulfilment' );
			}
		}

		foreach ( $orders_by_type as $order_type => $orders ) {
			foreach ( $orders as $order_id ) {
				$order = wc_get_order( $order_id );

				switch ( $order_type ) {
					case 'shipping_fulfilment':
						$relation = _x( 'Shipping Fulfilment Order', 'relation to order', 'enhancer-for-woocommerce-subscriptions' );
						break;
					default:
						$relation = _x( 'Unknown Order Type', 'relation to order', 'enhancer-for-woocommerce-subscriptions' );
						break;
				}

				if ( $order && ! $order->has_status( 'trash' ) ) {
					$order->update_meta_data( '_relationship', $relation );
					$orders_to_display[] = $order;
				}
			}
		}

		return $orders_to_display;
	}

	/**
	 * Add column content to the WooCommerce -> Orders admin screen to indicate whether an
	 * order is a shipping of a subscription or a regular order.
	 *
	 * @param string $column The string of the current column
	 */
	public static function show_relationship( $column ) {
		global $post;

		if ( 'subscription_relationship' === $column ) {
			if ( _enr_order_contains_shipping_fulfilment( $post->ID ) ) {
				echo '<img class="_enr_shipping_fulfilment_order_relationship" src="' . esc_attr( ENR_URL ) . '/assets/images/ship.png" title="' . esc_attr__( 'Shipping Fulfilment Order', 'enhancer-for-woocommerce-subscriptions' ) . '">';
			}
		}
	}
}
