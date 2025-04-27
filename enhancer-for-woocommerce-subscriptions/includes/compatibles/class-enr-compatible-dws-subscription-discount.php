<?php

defined( 'ABSPATH' ) || exit;

use MeowCrew\SubscriptionsDiscounts\Entity\DiscountedOrderItem;

/**
 * Compatibility with Discounts for WooCommerce Subscriptions
 */
class ENR_Compatible_DWS_Subscription_Discount {

	/**
	 * Init ENR_Compatible_DWS_Subscription_Discount
	 */
	public function init() {
		add_filter( 'enr_subscription_item_new_recurring_price', array( $this, 'set_item_new_recurring_price' ), 10, 3 );
	}

	/**
	 * Check is plugin active?
	 * 
	 * @since 4.5.0
	 * @return bool
	 */
	public function is_active() {
		return class_exists( 'MeowCrew\SubscriptionsDiscounts\SubscriptionsDiscountsPlugin' );
	}

	/**
	 * Set new subscription's item recurring price.
	 * 
	 * @since 4.5.0
	 * @param float $recurring_price Recurring Price.
	 * @param WC_Order_Item_Product Subscription Item.
	 * @param WC_Subscription $subscription Subscription Object.
	 * @return float
	 */
	public function set_item_new_recurring_price( $recurring_price, $item, $subscription ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription );
		if ( ! $subscription ) {
			return $recurring_price;
		}

		$discounted_item = new DiscountedOrderItem( $item, $subscription );
		if ( ! $discounted_item->isDiscountedItem() ) {
			return $recurring_price;
		}

		$discounts = $discounted_item->getDiscounts( false );
		if ( ! is_array( $discounts ) || empty( $discounts ) ) {
			return $recurring_price;
		}

		$applied_discount = $discounted_item->getAppliedDiscount();
		if ( empty( $applied_discount ) ) {
			return $recurring_price;
		}

		if ( ! array_key_exists( $applied_discount, $discounts ) ) {
			return $recurring_price;
		}

		$discount_amount = $discounts[ $applied_discount ];

		if ( $discounted_item->getDiscountsType() === 'percentage' ) {
			$product = $item->get_product();

			if ( ! $product ) {
				return $recurring_price;
			}

			$recurring_price = $product->get_price() - ( ( $product->get_price() / 100 ) * $discount_amount );
		} else {
			$recurring_price = $discount_amount;
		}

		return wc_get_price_excluding_tax( $product, array( 'price' => ( float ) $recurring_price ) );
	}
}
