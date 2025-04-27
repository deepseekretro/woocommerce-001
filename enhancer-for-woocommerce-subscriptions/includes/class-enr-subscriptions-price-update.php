<?php

defined( 'ABSPATH' ) || exit;

/**
 * Subscriptions price update handler.
 * 
 * @class ENR_Subscriptions_Price_Update
 * @package Class
 */
class ENR_Subscriptions_Price_Update {

	/**
	 * Get an array of meta.
	 * 
	 * @var array 
	 */
	protected static $meta = array(
		'_enr_allow_price_update_for_old_subscriptions' => 'use-storewide',
		'_enr_subscription_price_for_old_subscriptions' => 'old-price',
		'_enr_notify_subscription_price_update_before'  => '',
	);

	/**
	 * Get the price changed items for the subscription.
	 * 
	 * @var array 
	 */
	protected static $price_changed_items = array();

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'enr_woocommerce_scheduled_subscription_price_changed_reminder', __CLASS__ . '::remind_price_changed_before_renewal', 10, 2 );
		add_action( 'woocommerce_before_template_part', __CLASS__ . '::remind_price_changed_before_paying_early_renewal_via_modal', 10, 4 );

		add_filter( 'enr_get_subscription_meta', __CLASS__ . '::get_subscription_meta' );
		add_filter( 'woocommerce_order_item_product', __CLASS__ . '::restore_product_price_from_order_item', 10, 2 );
		add_action( 'woocommerce_before_product_object_save', __CLASS__ . '::destroy_subscription_cache' );
		add_action( 'woocommerce_scheduled_subscription_payment', __CLASS__ . '::maybe_enable_for_old_subscription', -2 );
		add_action( 'woocommerce_scheduled_subscription_payment', __CLASS__ . '::maybe_update', -1 );
		add_action( 'wcs_before_early_renewal_setup_cart_subscription', __CLASS__ . '::maybe_update', 0 );
		add_action( 'template_redirect', __CLASS__ . '::maybe_update_upon_resubscribe', 50 );
		add_action( 'wp_loaded', __CLASS__ . '::maybe_update_upon_early_renewal_payment_via_modal', 15 );
	}

	/**
	 * Can we apply new price to the subscription?
	 * 
	 * @param mixed $subscription
	 * @return bool
	 */
	public static function can_apply_new_price( $subscription ) {
		if ( 'override-storewide' === $subscription->get_meta( ENR_PREFIX . 'allow_price_update_for_old_subscriptions' ) ) {
			$can = 'new-price' === $subscription->get_meta( ENR_PREFIX . 'subscription_price_for_old_subscriptions' );
		} else {
			$can = 'new-price' === get_option( ENR_PREFIX . 'apply_old_subscription_price_as', 'old-price' );
		}

		return $can;
	}

	/**
	 * Get the number of days to remind before.
	 * 
	 * @param mixed $subscription
	 * @return int
	 */
	public static function get_days_to_remind_before( $subscription ) {
		$days_to_remind_before = 0;

		if ( 'override-storewide' === $subscription->get_meta( ENR_PREFIX . 'allow_price_update_for_old_subscriptions' ) ) {
			$days_to_remind_before = absint( $subscription->get_meta( ENR_PREFIX . 'notify_subscription_price_update_before' ) );
		} else {
			$days_to_remind_before = absint( get_option( ENR_PREFIX . 'notify_subscription_price_update_before' ) );
		}

		return $days_to_remind_before;
	}

	/**
	 * Check whether the subscription needs price update?
	 * 
	 * @param mixed $subscription
	 * @return bool
	 */
	public static function needs_update( $subscription ) {
		if ( ! $subscription || ! self::can_apply_new_price( $subscription ) ) {
			return false;
		}

		$subscription = _enr_maybe_get_subscription_instance( $subscription );
		if ( ! $subscription ) {
			return false;
		}

		$amount_is_editable = $subscription->is_manual() || $subscription->payment_method_supports( 'subscription_amount_changes' );
		if ( ! $amount_is_editable ) {
			return false;
		}

		$price_changed_items = self::get_price_changed_items( $subscription );
		if ( empty( $price_changed_items ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Return the array of price changed items for the subscription.
	 * 
	 * @param mixed $subscription
	 * @return array
	 */
	public static function get_price_changed_items( $subscription ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription );
		if ( ! $subscription ) {
			return array();
		}

		if ( ! empty( self::$price_changed_items[ $subscription->get_id() ] ) ) {
			return self::$price_changed_items[ $subscription->get_id() ];
		}

		self::$price_changed_items[ $subscription->get_id() ] = array();

		$price_args = array(
			'currency'                    => $subscription->get_currency(),
			'subscription_period'         => $subscription->get_billing_period(),
			'subscription_interval'       => $subscription->get_billing_interval(),
			'display_excluding_tax_label' => false,
		);

		/**
		 * Do you want prices include tax?
		 * 
		 * @since 4.5.2
		 */
		$prices_include_tax = apply_filters( 'enr_subscription_prices_include_tax', false, $subscription );
		$parent_order       = wc_get_order( $subscription->get_parent_id() );

		foreach ( $subscription->get_items( 'line_item' ) as $item ) {
			$product = $item->get_product();
			if ( ! $product ) {
				continue;
			}

			/**
			 * Do you want to bypass non base location prices?
			 * 
			 * @since 1.0
			 */
			if ( false === apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ) {
				add_filter( 'woocommerce_apply_base_tax_for_local_pickup', '__return_false' );

				$product_price          = floatval( wc_get_price_excluding_tax( $product, array( 'qty' => 1, 'order' => $parent_order ) ) );
				$product_price__inc_tax = floatval( wc_get_price_including_tax( $product, array( 'qty' => 1, 'order' => $parent_order ) ) );
			} else {
				$product_price          = floatval( wc_get_price_excluding_tax( $product, array( 'qty' => 1 ) ) );
				$product_price__inc_tax = floatval( wc_get_price_including_tax( $product, array( 'qty' => 1 ) ) );
			}

			$recurring_price_before_discount          = floatval( $subscription->get_item_subtotal( $item, false, false ) );
			$recurring_price_before_discount__inc_tax = floatval( $subscription->get_item_subtotal( $item, true, false ) );

			$recurring_price          = floatval( $subscription->get_item_total( $item, false, false ) );
			$recurring_price__inc_tax = floatval( $subscription->get_item_total( $item, true, false ) );

			$discount          = max( 0, ( $recurring_price_before_discount - $recurring_price ) );
			$discount__inc_tax = max( 0, ( $recurring_price_before_discount__inc_tax - $recurring_price__inc_tax ) );

			$new_recurring_price          = max( 0, ( $product_price - $discount ) );
			$new_recurring_price__inc_tax = max( 0, ( $product_price__inc_tax - $discount__inc_tax ) );

			/**
			 * Get the item new recurring price.
			 * 
			 * @since 4.5.0
			 */
			$new_recurring_price = apply_filters( 'enr_subscription_item_new_recurring_price', $new_recurring_price, $item, $subscription );

			if ( "$new_recurring_price" !== "$recurring_price" ) {
				self::$price_changed_items[ $subscription->get_id() ][ $item->get_id() ] = array(
					'from' => $recurring_price,
					'to'   => $new_recurring_price,
					'item' => $item,
				);

				$price_args[ 'recurring_amount' ]                                                         = $prices_include_tax ? $recurring_price__inc_tax : $recurring_price;
				self::$price_changed_items[ $subscription->get_id() ][ $item->get_id() ][ 'from_string' ] = wcs_price_string( $price_args );

				$price_args[ 'recurring_amount' ]                                                       = $prices_include_tax ? $new_recurring_price__inc_tax : $new_recurring_price;
				self::$price_changed_items[ $subscription->get_id() ][ $item->get_id() ][ 'to_string' ] = wcs_price_string( $price_args );
			}
		}

		/**
		 * Get the subscription price changed items.
		 * 
		 * @param array $price_changed_items
		 * @param WC_Subscription $subscription 
		 * @since 1.0
		 */
		self::$price_changed_items[ $subscription->get_id() ] = ( array ) apply_filters( 'enr_get_subscription_price_changed_items', self::$price_changed_items[ $subscription->get_id() ], $subscription );
		return self::$price_changed_items[ $subscription->get_id() ];
	}

	/**
	 * Remind customers about the subscription price changed before the renewal.
	 * 
	 * @param int $subscription_id The ID of a 'shop_subscription' post
	 * @param int|null $day_to_remind
	 */
	public static function remind_price_changed_before_renewal( $subscription_id, $day_to_remind = null ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription_id );
		if ( ! $subscription ) {
			return;
		}

		if ( ! self::needs_update( $subscription ) ) {
			return;
		}

		/**
		 * Remind the subscription price before the renewal payment.
		 * 
		 * @param WC_Subscription $subscription 
		 * @param array $price_changed_items
		 * @param int $day_to_remind
		 * @since 1.0
		 */
		do_action( 'enr_wc_subscriptions_remind_subscription_price_changed_before_renewal', $subscription, self::get_price_changed_items( $subscription ), $day_to_remind );

		self::maybe_update( $subscription );
	}

	/**
	 * Get price update meta paired with default value.
	 * 
	 * @param array $meta
	 * @return array
	 */
	public static function get_subscription_meta( $meta ) {
		$meta = array_merge( $meta, self::$meta );
		return $meta;
	}

	/**
	 * Attempts to restore the subscription price of a product instantiated using an order item as reference.
	 *
	 * @param  WC_Product  $product
	 * @param  array       $order_item
	 * @return WC_Product
	 */
	public static function restore_product_price_from_order_item( $product, $order_item ) {
		$subscribed_plan_id = $order_item->get_meta( '_enr_subscribed_plan', true );
		$product            = _enr_maybe_get_product_instance( $product );

		if ( ! $product || ! ENR_Subscription_Plan::exists( $subscribed_plan_id ) || $subscribed_plan_id === $product->get_meta( '_enr_current_subscription_plan', true ) ) {
			return $product;
		}

		$plan_type          = ENR_Subscription_Plan::get_type( $subscribed_plan_id );
		$plan_price_percent = ENR_Subscription_Plan::get_prop( $subscribed_plan_id, $plan_type, 'subscription_price' );

		if ( $plan_price_percent > 0 ) {
			$subscription_price = ( floatval( $product->get_price() ) * $plan_price_percent ) / 100;

			$product->set_price( $subscription_price );
			$product->update_meta_data( '_enr_current_subscription_plan', $subscribed_plan_id );
		}

		return $product;
	}

	/**
	 * Destroy the product object cache before data saves in to the database.
	 * 
	 * @param WC_Product $product
	 */
	public static function destroy_subscription_cache( $product ) {
		$product->delete_meta_data( '_enr_current_subscription_plan' );
	}

	/**
	 * Remind customers about the subscription price changed before processing the early renewal payment via modal.
	 * 
	 * @param string $template_name
	 * @param string $template_path
	 * @param string $located
	 * @param array $args
	 */
	public static function remind_price_changed_before_paying_early_renewal_via_modal( $template_name, $template_path, $located, $args ) {
		if ( 'html-early-renewal-modal-content.php' !== $template_name || empty( $args[ 'subscription' ] ) ) {
			return;
		}

		$subscription = _enr_maybe_get_subscription_instance( $args[ 'subscription' ] );
		if ( ! $subscription ) {
			return;
		}

		if ( ! self::needs_update( $args[ 'subscription' ] ) ) {
			return;
		}

		$price_changed_item = current( self::get_price_changed_items( $args[ 'subscription' ] ) );

		/* translators: %s new subscription product price */
		printf( wp_kses_post( __( '<p>The product price of this subscription has been updated to <strong>%s</strong>. You have to pay the Total amount based on the updated product price now and for the future renewals of this subscription.</p>', 'enhancer-for-woocommerce-subscriptions' ) ), wp_kses_post( $price_changed_item[ 'to_string' ] ) );
	}

	/**
	 * Check whether the subscription price update is enabled for old subscription and update it once.
	 * 
	 * @param int $subscription_id
	 */
	public static function maybe_enable_for_old_subscription( $subscription_id ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription_id );
		if ( ! $subscription ) {
			return;
		}

		$items     = $subscription->get_items( 'line_item' );
		$overriden = false;

		if ( ! empty( $items ) ) {
			foreach ( $items as $item ) {
				$product = $item->get_product();

				if ( $product && $product->is_type( array( 'subscription', 'subscription_variation' ) ) && 'override-storewide' === $product->get_meta( ENR_PREFIX . 'allow_price_update_for_old_subscriptions' ) ) {
					$overriden = true;
					break;
				}
			}
		}

		if ( $overriden ) {
			foreach ( self::$meta as $key => $default_value ) {
				$subscription->update_meta_data( $key, WC_Subscriptions_Product::get_meta_data( $product, $key, $default_value ) );
				$subscription->save_meta_data();
			}
		}
	}

	/**
	 * May be update to new subscription price if the price is changed in the subscription product.
	 * 
	 * @param mixed $subscription
	 */
	public static function maybe_update( $subscription ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription );
		if ( ! $subscription ) {
			return;
		}

		if ( ! self::needs_update( $subscription ) ) {
			return;
		}

		$price_changed_items = self::get_price_changed_items( $subscription );
		foreach ( $price_changed_items as $item_id => $changed ) {
			if ( $changed[ 'item' ]->get_subtotal() !== $changed[ 'item' ]->get_total() ) {
				$discount = wc_format_decimal( $changed[ 'item' ]->get_subtotal() - $changed[ 'item' ]->get_total(), '' );
			} else {
				$discount = 0;
			}

			$subtotal = $changed[ 'to' ] * $changed[ 'item' ]->get_quantity();
			$subtotal += $discount;

			$changed[ 'item' ]->set_subtotal( $subtotal );
			$changed[ 'item' ]->set_total( max( 0, ( $subtotal - $discount ) ) );
			$changed[ 'item' ]->save();
		}

		$subscription->calculate_totals();
	}

	/**
	 * May be update to new subscription price for resubscribe if the price is changed in the subscription product.
	 */
	public static function maybe_update_upon_resubscribe() {
		if ( isset( $_GET[ 'resubscribe' ], $_GET[ '_wpnonce' ] ) ) {
			$subscription_id = absint( wp_unslash( $_GET[ 'resubscribe' ] ) );

			if ( ! wp_verify_nonce( wc_clean( wp_unslash( $_GET[ '_wpnonce' ] ) ), $subscription_id ) ) {
				return;
			}

			self::maybe_update( $subscription_id );
		}
	}

	/**
	 * May be update to new subscription price for early renewal payment(via modal) if the price is changed in the subscription product.
	 */
	public static function maybe_update_upon_early_renewal_payment_via_modal() {
		if ( isset( $_GET[ 'process_early_renewal' ], $_GET[ 'subscription_id' ], $_GET[ 'wcs_nonce' ] ) ) {
			$subscription_id = absint( wp_unslash( $_GET[ 'subscription_id' ] ) );

			if ( ! wp_verify_nonce( wc_clean( wp_unslash( $_GET[ 'wcs_nonce' ] ) ), 'wcs-renew-early-modal-' . $subscription_id ) ) {
				return;
			}

			self::maybe_update( $subscription_id );
		}
	}
}
