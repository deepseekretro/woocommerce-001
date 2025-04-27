<?php

/**
 * Subscriptions Management Class 
 *
 * @class ENR_Subscriptions_Manager
 * @package Class
 */
class ENR_Subscriptions_Manager {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'woocommerce_checkout_create_subscription', __CLASS__ . '::update_subscription_meta', 10, 4 );
		add_action( 'woocommerce_checkout_create_order_line_item', __CLASS__ . '::update_subscription_item_meta', 10, 4 );
		add_action( 'woocommerce_subscriptions_switched_item', __CLASS__ . '::subscriptions_switched', 10, 2 );
		add_filter( 'woocommerce_hidden_order_itemmeta', __CLASS__ . '::hidden_subscription_itemmeta' );

		add_action( 'woocommerce_scheduled_subscription_payment', __CLASS__ . '::maybe_enable_for_old_subscription', -2 );

		add_action( 'enr_woocommerce_scheduled_subscription_trial_end_reminder', __CLASS__ . '::remind_before_trial_end', 10, 2 );
		add_action( 'enr_woocommerce_scheduled_subscription_auto_renewal_reminder', __CLASS__ . '::remind_before_renewal', 10, 2 );
		add_action( 'enr_woocommerce_scheduled_subscription_manual_renewal_reminder', __CLASS__ . '::remind_before_renewal', 10, 2 );
		add_action( 'enr_woocommerce_scheduled_subscription_expiration_reminder', __CLASS__ . '::remind_before_expiry', 10, 2 );
		add_action( 'enr_woocommerce_scheduled_subscription_shipping_fulfilment_order', __CLASS__ . '::create_shipping_fulfilment_order' );

		ENR_Action_Scheduler::init();
		ENR_Shipping_Cycle::init();
		ENR_Subscriptions_Price_Update::init();
	}

	/*
	  |--------------------------------------------------------------------------
	  | Helper Methods
	  |--------------------------------------------------------------------------
	 */

	/**
	 * Return an array of subscription meta.
	 */
	public static function get_subscription_meta() {
		/**
		 * Get the subscription meta.
		 * 
		 * @since 1.0
		 */
		return apply_filters( 'enr_get_subscription_meta', array(
			'_enr_allow_cancelling_to'         => 'use-storewide',
			'_enr_allow_cancelling_after'      => '0',
			'_enr_allow_cancelling_after_due'  => '0',
			'_enr_allow_cancelling_before_due' => '0',
			'_enr_subscribed_plan'             => '0',
			'_enr_exclude_reminder_emails'     => array(),
				) );
	}

	/**
	 * Update subscription meta while creating subscription.
	 * 
	 * @param WC_Subscription $subscription
	 * @param array $posted_data
	 * @param WC_Order $order
	 * @param WC_Cart $cart
	 */
	public static function update_subscription_meta( $subscription, $posted_data, $order, $cart ) {
		$meta = self::get_subscription_meta();
		foreach ( $meta as $key => $default_value ) {
			$subscription->update_meta_data( $key, wcs_cart_pluck( $cart, $key, $default_value ) );
		}
	}

	/**
	 * Update subscription item meta after subscription item created.
	 * 
	 * @param WC_Order_Item $item
	 * @param string $cart_item_key
	 * @param array $cart_item
	 * @param WC_Subscription $subscription
	 */
	public static function update_subscription_item_meta( $item, $cart_item_key, $cart_item, $subscription ) {
		if ( ! wcs_is_subscription( $subscription ) ) {
			return;
		}

		$meta = self::get_subscription_meta();
		foreach ( $meta as $key => $default_value ) {
			$item->update_meta_data( $key, WC_Subscriptions_Product::get_meta_data( $cart_item[ 'data' ], $key, $default_value ) );
		}
	}

	/**
	 * Update subscription meta after subscription is switched.
	 * 
	 * @param WC_Subscription $subscription
	 * @param WC_Order_Item $new_order_item
	 */
	public static function subscriptions_switched( $subscription, $new_order_item ) {
		$meta = self::get_subscription_meta();
		foreach ( $meta as $key => $default_value ) {
			$subscription->update_meta_data( $key, $new_order_item->get_meta( $key ) );
			$subscription->save_meta_data();
		}
	}

	/**
	 * Hide subscription meta.
	 * 
	 * @return array
	 */
	public static function hidden_subscription_itemmeta( $hidden ) {
		$meta   = array_keys( self::get_subscription_meta() );
		$hidden = array_merge( $meta, $hidden );
		return $hidden;
	}

	/**
	 * Maybe update product meta for old subscription and update it once.
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

				if ( $product && ( $product->is_type( array( 'subscription', 'subscription_variation' ) ) || 'yes' === $product->get_meta( '_enr_allow_subscribe_now' ) ) ) {
					$overriden = true;
					break;
				}
			}
		}

		if ( $overriden ) {
			$subscription->update_meta_data( '_enr_exclude_reminder_emails', WC_Subscriptions_Product::get_meta_data( $product, '_enr_exclude_reminder_emails', array() ) );
			$subscription->save_meta_data();
		}
	}

	/*
	  |--------------------------------------------------------------------------
	  | Action Scheduler Callback Methods
	  |--------------------------------------------------------------------------
	 */

	/**
	 * Remind users before the subscription trial is ending.
	 * 
	 * @param int $subscription_id The ID of a 'shop_subscription' post
	 * @param int|null $day_to_remind
	 */
	public static function remind_before_trial_end( $subscription_id, $day_to_remind = null ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription_id );

		if ( ! $subscription || ! $subscription->has_status( 'active' ) ) {
			return;
		}

		/**
		 * Remind before the trial end.
		 * 
		 * @param WC_Subscription $subscription 
		 * @param int $day_to_remind 
		 * @since 1.0
		 */
		do_action( 'enr_wc_subscriptions_remind_before_trial_end', $subscription, $day_to_remind );
	}

	/**
	 * Remind users before the subscription is going to renew automatically/manually.
	 * 
	 * @param int $subscription_id The ID of a 'shop_subscription' post
	 * @param int|null $day_to_remind
	 */
	public static function remind_before_renewal( $subscription_id, $day_to_remind = null ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription_id );

		if ( ! $subscription || ! $subscription->has_status( 'active' ) ) {
			return;
		}

		if ( $subscription->is_manual() ) {
			/**
			 * Remind before the manual renewal payment.
			 * 
			 * @param WC_Subscription $subscription 
			 * @param int $day_to_remind 
			 * @since 1.0
			 */
			do_action( 'enr_wc_subscriptions_remind_before_manual_renewal', $subscription, $day_to_remind );
		} else {
			/**
			 * Remind before the auto renewal payment.
			 * 
			 * @param WC_Subscription $subscription 
			 * @param int $day_to_remind 
			 * @since 1.0
			 */
			do_action( 'enr_wc_subscriptions_remind_before_auto_renewal', $subscription, $day_to_remind );
		}
	}

	/**
	 * Remind users before the subscription gets expired.
	 * 
	 * @param int $subscription_id The ID of a 'shop_subscription' post
	 * @param int|null $day_to_remind
	 */
	public static function remind_before_expiry( $subscription_id, $day_to_remind = null ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription_id );

		if ( ! $subscription || ! $subscription->has_status( array( 'active', 'pending-cancel' ) ) ) {
			return;
		}

		/**
		 * Remind before expiry.
		 * 
		 * @param WC_Subscription $subscription 
		 * @param int $day_to_remind 
		 * @since 1.0
		 */
		do_action( 'enr_wc_subscriptions_remind_before_expiry', $subscription, $day_to_remind );
	}

	/**
	 * Create the shipping fulfilment order.
	 * 
	 * @param int $subscription_id The ID of a 'shop_subscription' post
	 */
	public static function create_shipping_fulfilment_order( $subscription_id ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription_id );
		if ( ! $subscription ) {
			return;
		}

		$shipping_fulfilment_order = wcs_create_order_from_subscription( $subscription, 'enr_shipping_fulfilment_order' );

		if ( is_wp_error( $shipping_fulfilment_order ) ) {
			/**
			 * Trigger when it is failed to create the shipping fulfilment order.
			 * 
			 * @param WC_Order $shipping_fulfilment_order 
			 * @param WC_Subscription $subscription 
			 * @since 1.0
			 */
			do_action( 'enr_wc_subscriptions_failed_to_create_shipping_fulfilment_order', $shipping_fulfilment_order, $subscription );
			return;
		}

		// Update as shipping fulfilment order.
		$shipping_fulfilment_order->update_meta_data( ENR_PREFIX . 'shipping_fulfilment_order', 'yes' );

		$shipping_fulfilment_orders       = $subscription->get_meta( ENR_PREFIX . 'shipping_fulfilment_orders' );
		$shipping_fulfilment_orders       = is_array( $shipping_fulfilment_orders ) ? $shipping_fulfilment_orders : array();
		$shipping_fulfilment_orders[]     = $shipping_fulfilment_order->get_id();
		$shipping_fulfilment_orders_count = count( $shipping_fulfilment_orders );

		foreach ( array( 'shipping', 'fee', 'coupon' ) as $item_type ) {
			$shipping_fulfilment_order->remove_order_items( $item_type );
		}

		foreach ( $shipping_fulfilment_order->get_items( 'line_item' ) as $item ) {
			if ( isset( $item[ 'product_id' ] ) ) {
				$item->set_total( 0 );
				$item->set_subtotal( 0 );
				$item->save();
			}
		}

		// Make sure to calculate the total for the order since we are saving the line total/subtotal alone zero not calculating it while creating the order.
		$shipping_fulfilment_order->calculate_totals();

		// Add relation to the order.
		WCS_Related_Order_Store::instance()->add_relation( $shipping_fulfilment_order, $subscription, 'enr_shipping_fulfilment' );

		/* translators: 1: shipping fulfilment orders count */
		$shipping_fulfilment_order->update_status( 'processing', sprintf( __( '%s shipping fulfilment order for the subscription.', 'enhancer-for-woocommerce-subscriptions' ), _enr_get_number_suffix( $shipping_fulfilment_orders_count ) ) );
		/* translators: 1: shipping fulfilment orders count 2: shipping fulfilment order admin url 3: shipping fulfilment order ID */
		$subscription->add_order_note( sprintf( __( '%1$s shipping fulfilment order <a href="%2$s">#%3$s</a>', 'enhancer-for-woocommerce-subscriptions' ), _enr_get_number_suffix( $shipping_fulfilment_orders_count ), esc_url( wcs_get_edit_post_link( $shipping_fulfilment_order->get_id() ) ), $shipping_fulfilment_order->get_id() ) );

		// Update the order as shipping fulfilment.
		$subscription->update_meta_data( ENR_PREFIX . 'shipping_fulfilment_orders', $shipping_fulfilment_orders );
		$subscription->save_meta_data();

		/**
		 * Trigger after shipping fulfilment order has been created.
		 * 
		 * @param WC_Subscription $subscription 
		 * @param WC_Order $shipping_fulfilment_order 
		 * @param int $shipping_fulfilment_orders_count 
		 * @since 1.0
		 */
		do_action( 'enr_wc_subscriptions_shipping_fulfilment_order_created', $subscription, $shipping_fulfilment_order, $shipping_fulfilment_orders_count );
	}
}

ENR_Subscriptions_Manager::init();
