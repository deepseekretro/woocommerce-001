<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Order Data Store Class
 *
 * @since       2.0
 *
 * @author      Kestrel
 * @author      Copyright (c) Kestrel [hey@kestrelwp.com]
 * @package     WooCommerce API Manager/Order Data Store
 */
class WC_AM_Order_Data_Store {

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return null|\WC_AM_Order_Data_Store
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() { }

	/**
	 * Return the order object.
	 *
	 * @since 2.0
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 *
	 * @return bool|\WC_Order
	 */
	public function get_order_object( $order ) {
		return is_object( $order ) ? $order : wc_get_order( $order );
	}

	/**
	 * Get order metadata by key. If one key passed, and two or more identical keys exist,
	 * all values for those identical keys will be returned.
	 *
	 * @usage WC_AM_ORDER_DATA_STORE()->get_meta( $order_id, '_api_new_version' );
	 *
	 * @since 2.0
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 * @param string    $meta_key
	 * @param bool      $single
	 *
	 * @return bool|mixed A single value is returned.
	 */
	public function get_meta( $order, $meta_key = '', $single = true ) {
		$order = $this->get_order_object( $order );

		if ( is_object( $order ) ) {
			if ( $single ) {
				/**
				 * @usage returns a single value for a single key. A single value for the single order.
				 * echo WC_AM_ORDER_DATA_STORE()->get_meta( $order_id, '_api_new_version' );
				 */
				return $order->get_meta( $meta_key, $single );
			} else {
				/**
				 * @usage returns multiple values if there are multiple keys. One value for each order.
				 * $o = WC_AM_ORDER_DATA_STORE()->get_meta( $order_id, '_api_new_version', false );
				 * echo $o['_api_new_version'];
				 */
				return WC_AM_ARRAY()->flatten_meta_object( $order->get_meta( $meta_key, $single ) );
			}
		}

		return false;
	}

	/**
	 * Get all order metadata.
	 *
	 * @since 2.0
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 *
	 * @return array|bool|mixed If data exists an array is returned.
	 */
	public function get_meta_data( $order ) {
		$order = $this->get_order_object( $order );

		if ( is_object( $order ) ) {
			$args = [
				'id'     => $order->get_id(),
				'number' => $order->get_order_number(),
			];

			return array_merge( $args, $order->get_data(), [
				'meta_data' => WC_AM_ARRAY()->flatten_meta_object( $order->get_meta_data() ),
			] );
		}

		return false;
	}

	/**
	 * Return an array of items/products within this order.
	 *
	 * @since 2.0
	 *
	 * @param int          $order_id
	 * @param string|array $types Types of line items to get (array or string).
	 *
	 * @return \WC_Order_Item[]
	 */
	public function get_items( $order_id, $types = 'line_item' ) {
		$order = $this->get_order_object( $order_id );

		return is_object( $order ) ? $order->get_items( $types ) : [];
	}

	/**
	 * WooCommerce Order Item Meta API - Get term meta.
	 *
	 * @since 2.0
	 *
	 * @param int    $item_id
	 * @param string $key
	 * @param bool   $single
	 *
	 * @return array|mixed
	 * @throws \Exception
	 */
	public function get_order_item_meta( $item_id, $key = '', $single = true ) {
		if ( WC_AM_FORMAT()->empty( $key ) ) {
			return WC_AM_ARRAY()->flatten_array( wc_get_order_item_meta( $item_id, $key, $single ) );
		}

		return wc_get_order_item_meta( $item_id, $key, $single );
	}

	/**
	 * Get order ID by order item ID.
	 *
	 * @since 2.0
	 *
	 * @param int $item_id
	 *
	 * @return int
	 * @throws \Exception
	 */
	public function get_order_id_by_order_item_id( $item_id ) {
		return wc_get_order_id_by_order_item_id( $item_id );
	}

	/**
	 * Get the current order item ID(s).
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 *
	 * @return array|bool
	 */
	public function get_order_item_ids( $order_id ) {
		$order    = $this->get_order_object( $order_id );
		$item_ids = [];

		if ( is_object( $order ) ) {
			$items = $order->get_items();

			if ( WC_AM_FORMAT()->count( $items ) > 0 ) {
				foreach ( $items as $item_id => $data ) {
					$item_ids[] = $item_id;
				}
			}
		}

		return ! WC_AM_FORMAT()->empty( $item_ids ) ? $item_ids : false;
	}

	/**
	 * Get all order data, including metadata.
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 *
	 * @return array|bool
	 */
	public function get_order_data( $order ) {
		$order = $this->get_order_object( $order );

		if ( is_object( $order ) ) {
			return array_merge( [
				'id' => $order->get_id(),
			], $order->get_data(), [
				'number'         => $order->get_order_number(),
				'meta_data'      => WC_AM_ARRAY()->flatten_meta_object( $order->get_meta_data() ),
				'line_items'     => $order->get_items( 'line_item' ),
				'tax_lines'      => $order->get_items( 'tax' ),
				'shipping_lines' => $order->get_items( 'shipping' ),
				'fee_lines'      => $order->get_items( 'fee' ),
				'coupon_lines'   => $order->get_items( 'coupon' ),
			] );
		}

		return false;
	}

	/**
	 * Gets all order IDs as an array. Default order status is completed or processing, which means payment should have been completed.
	 *
	 * @since 2.0.8
	 *
	 * @return \stdClass|\WC_Order[]
	 */
	public function get_all_order_ids() {
		if ( ! WCAM()->get_grant_access_after_payment() ) {
			return wc_get_orders( [
				'limit'  => - 1,
				'status' => 'wc-completed',
				'type'   => 'shop_order',
				'return' => 'ids',
			] );
		} else {
			return wc_get_orders( [
				'limit'  => - 1,
				'status' => [ 'wc-completed', 'wc-processing' ],
				'type'   => 'shop_order',
				'return' => 'ids',
			] );
		}
	}

	/**
	 * Return the customer/user ID.
	 *
	 * @since   2.0
	 * @updated 2.5 For WooCommerce HPOS.
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 *
	 * @return bool|int|mixed
	 */
	public function get_customer_id( $order ) {
		$order = $this->get_order_object( $order );

		return is_object( $order ) && ! ( $order instanceof WC_Order_Refund ) ? $order->get_customer_id() : false;
	}

	/**
	 * Return the order key.
	 *
	 * @since   2.0
	 * @updated 2.5 For WooCommerce HPOS.
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 *
	 * @return bool|mixed|string
	 */
	public function get_order_key( $order ) {
		$order = $this->get_order_object( $order );

		return is_object( $order ) ? $order->get_order_key() : false;
	}

	/**
	 * Return the order number/order ID.
	 *
	 * @since 2.0
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 *
	 * @return bool|string
	 */
	public function get_order_number( $order ) {
		$order = $this->get_order_object( $order );

		if ( is_object( $order ) ) {
			// Pre 3.0 $order->id or $order->get_order_number()
			return $order->get_order_number();
		}

		return false;
	}

	/**
	 * Return the refunded quantity for an order item.
	 * Used for non-WooCommerce Subscription products since there is an Order item_id in the API Resource.
	 *
	 * @since 2.0
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 * @param int       $item_id
	 *
	 * @return int
	 */
	public function get_qty_refunded_for_item( $order, $item_id ) {
		$order = $this->get_order_object( $order );

		return is_object( $order ) ? $order->get_qty_refunded_for_item( $item_id ) : 0;
	}

	/**
	 * Return the refunded quantity for a product_id.
	 * Used for WooCommerce Subscription products since there is no Order item_id in the API Resource.
	 *
	 * @since 2.3.10
	 *
	 * @param int|object $order WC_Order or order ID.
	 * @param int        $product_id
	 *
	 * @return int
	 * @throws \Exception
	 */
	public function get_qty_refunded_for_product_id( $order, $product_id ) {
		$count   = 0;
		$refunds = [];
		$order   = $this->get_order_object( $order );

		if ( is_object( $order ) ) {
			$item_ids = $this->get_order_item_ids( $order );

			foreach ( $order->get_refunds() as $refund ) {
				foreach ( $item_ids as $item_id ) {
					foreach ( $refund->get_items( [ 'line_item' ] ) as $refunded_item ) {
						if ( absint( $refunded_item->get_meta( '_refunded_item_id' ) ) === $item_id ) {
							$parent_id                   = wc_get_order_item_meta( $refunded_item->get_id(), '_product_id' );
							$variation_id                = wc_get_order_item_meta( $refunded_item->get_id(), '_variation_id' );
							$refunds[ $order->get_id() ] = [
								'order_id'   => $order->get_id(),
								'item_id'    => $item_id,
								'product_id' => WC_AM_FORMAT()->empty( $variation_id ) ? $parent_id : $variation_id,
								'count'      => $count += $this->get_refund_quantity_for_item( $refund, $item_id ),
							];
						}
					}
				}
			}

			foreach ( $refunds as $refund ) {
				if ( $refund['product_id'] == $product_id ) {
					return $refund['count'];
				}
			}
		}

		return 0;
	}

	/**
	 * Get the refunded amount for a line item.
	 *
	 * @since 2.3.10
	 *
	 * @param object $refund    WC_Order_Refund
	 * @param int    $item_id   ID of the item we're checking.
	 * @param string $item_type Type of the item we're checking, if not a line_item.
	 *
	 * @return int
	 */
	private function get_refund_quantity_for_item( $refund, $item_id, $item_type = 'line_item' ) {
		$qty = 0;

		if ( is_object( $refund ) ) {
			foreach ( $refund->get_items( $item_type ) as $refunded_item ) {
				if ( absint( $refunded_item->get_meta( '_refunded_item_id' ) ) === $item_id ) {
					$qty += $refunded_item->get_quantity();
				}
			}
		}

		return $qty;
	}

	/**
	 * Builds a secure download URL.
	 *
	 * @since   2.0
	 * @updated 2.6.2 Replaced http_build_query() with add_query_arg().
	 *
	 * @param int    $user_id
	 * @param int    $order_id
	 * @param int    $product_id
	 * @param string $remote_url
	 *
	 * @return string
	 */
	public function get_secure_order_download_url( $user_id, $order_id, $product_id, $remote_url = '' ) {
		// Cost value is between 4 and 31
		$hash_data = WC_AM_HASH()->password_hash( $user_id, null, null, [ 'cost' => 4 ] );

		$url_args = [
			'user_id'          => $user_id,
			'am_download_file' => $product_id,
			'am_order'         => $order_id,
			'hname'            => $hash_data['hname'],
			'hkey'             => $hash_data['hkey'],
			'hexpires'         => $hash_data['hexpires'],
			'remote_url'       => ! WC_AM_FORMAT()->empty( $remote_url ) ? 'yes' : 'no',
		];

		return add_query_arg( $url_args, home_url( '/' ) );
	}

	/**
	 * Return the downloadable data.
	 *
	 * @since 2.0
	 *
	 * @param int $user_id
	 * @param int $order_id
	 * @param int $product_id
	 *
	 * @return array|bool|null|object
	 */
	public function get_order_downloadable_data( $user_id, $order_id, $product_id ) {
		global $wpdb;

		$sql = "
			SELECT *
			FROM {$wpdb->prefix}woocommerce_downloadable_product_permissions
			WHERE user_id = %s
			AND order_id = %s
			AND product_id = %s
		";

		$args = [
			$user_id,
			$order_id,
			$product_id,
		];

		// Returns an Object
		$result = $wpdb->get_row( $wpdb->prepare( $sql, $args ) ); // phpcs:ignore

		return ! WC_AM_FORMAT()->empty( $result ) ? $result : false;
	}

	/**
	 * Return all API resource order item rows matching the order_id.
	 *
	 * @since 2.0
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 *
	 * @return array|bool
	 */
	public function get_api_resource_items_for_order( $order ) {
		$order = $this->get_order_object( $order );

		if ( is_object( $order ) ) {
			global $wpdb;

			$sql = "
				SELECT *
				FROM {$wpdb->prefix}" . WC_AM_USER()->get_api_resource_table_name() . '
				WHERE order_id = %d
			';

			// Get the API resource order items for this product.
			$resources = $wpdb->get_results( $wpdb->prepare( $sql, $order->get_id() ) ); // phpcs:ignore
		}

		return ! WC_AM_FORMAT()->empty( $resources ) ? $resources : false;
	}

	/**
	 * Get order_item_id.
	 *
	 * @since 2.0
	 *
	 * @param int    $order_id
	 * @param string $type
	 *
	 * @return bool|int
	 */
	public function get_item_id( $order_id, $type ) {
		global $wpdb;

		// phpcs:disable
		$item_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT 		order_item_id
			FROM 		{$wpdb->prefix}woocommerce_order_items
			WHERE 		order_id = %d
			AND 		order_item_type = %s
		", absint( $order_id ), esc_attr( $type ) ) );
		// phpcs:enable

		return ! WC_AM_FORMAT()->empty( $item_id ) ? (int) $item_id : false;
	}

	/**
	 * Get order_item_ids.
	 *
	 * Output example:
	 * Array
	 * (
	 *  [0] = 625
	 *  [1] = 626
	 *  [2] = 627
	 *  [3] = 628
	 * )
	 *
	 * @since 1.0
	 *
	 * @param int    $order_id
	 * @param string $type
	 *
	 * @return array|bool
	 */
	public function get_item_ids( $order_id, $type ) {
		global $wpdb;

		$item_ids = $wpdb->get_results( $wpdb->prepare( "
			SELECT 		order_item_id
			FROM 		{$wpdb->prefix}woocommerce_order_items
			WHERE 		order_id = %d
			AND 		order_item_type = %s
			ORDER BY 	order_item_id DESC
		", absint( $order_id ), esc_attr( $type ) ), ARRAY_A );

		if ( ! WC_AM_FORMAT()->empty( $item_ids ) && is_array( $item_ids ) ) {
			foreach ( $item_ids as $order_item_id => $item ) {
				$order_items[] = $item['order_item_id'];
			}

			return ! WC_AM_FORMAT()->empty( $order_items ) ? $order_items : false;
		}

		return false;
	}

	/**
	 * Returns the current epoch/unix timestamp.
	 *
	 * @since   2.0
	 * @updated 2.6.5 Changed default $gmt to true to allow it to return time() offset by get_option( 'gmt_offset' ).
	 *
	 * @param int|bool $gmt Optional. Whether to use GMT timezone. Default true.
	 *
	 * @return int
	 */
	public function get_current_time_stamp( $gmt = true ) {
		return (int) current_time( 'timestamp', $gmt );
	}

	/**
	 * Return true if $time is older than the current time, and false if $time is zero meaning infinite.
	 *
	 * @since   2.0
	 * @updated 2.6.15 Add case for infinte subscriptions having a value of zero.
	 *
	 * @param int $time
	 *
	 * @return bool
	 */
	public function is_time_expired( $time ) {
		if ( is_numeric( $time ) ) {
			// Infinite API Manager subscription.
			if ( $time == 0 ) {
				return false;
			}

			// A value of zero would return true. Infinite API Manager subscriptions, which have a value of zero, would evaluate as expired.
			return $time < $this->get_current_time_stamp();
		}

		// If a non-numeric argument is sent, there is no way to evaluate it accurately.
		return false;
	}

	/**
	 * Returns a formatted array of order line item data from an order.
	 *
	 * @since 2.0
	 *
	 * @param int $order_id
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function get_line_item_data_from_order( $order_id ) {
		$values = [];
		$items  = [];
		$order  = $this->get_order_object( $order_id );

		if ( is_object( $order ) ) {
			$get_items = $order->get_items();

			if ( WC_AM_FORMAT()->count( $get_items ) > 0 ) {
				foreach ( $get_items as $item_id => $item ) {
					// Line item has been renewed on a new order, so this API Resource has expired.
					if ( ! WC_AM_FORMAT()->empty( wc_get_order_item_meta( $item_id, '_wc_am_is_expired_api_resource' ) ) ) {
						continue;
					}
					/**
					 * $item->get_id() is the order_item_id.
					 */
					$data              = $item->get_data();
					$parent_product_id = WC_AM_PRODUCT_DATA_STORE()->get_parent_product_id( $item->get_product_id() );
					$is_api            = WC_AM_PRODUCT_DATA_STORE()->is_api_product( ! WC_AM_FORMAT()->empty( $parent_product_id ) ? $parent_product_id : $data['variation_id'] );

					// Only store API resource data for API products that have an order status of completed.
					if ( $is_api ) {
						$variation_id  = ! WC_AM_FORMAT()->empty( $data['variation_id'] ) && WC_AM_PRODUCT_DATA_STORE()->has_valid_product_status( $data['variation_id'] ) ? $data['variation_id'] : 0;
						$product_id    = ! WC_AM_FORMAT()->empty( $variation_id ) ? $variation_id : $parent_product_id;
						$valid_product = WC_AM_PRODUCT_DATA_STORE()->has_valid_product_status( $product_id );

						// Skip WC Subscriptions.
						$is_wc_sub = WC_AM_SUBSCRIPTION()->is_wc_subscription( $parent_product_id );

						// Only store API resource data for API products that have an order status of completed.
						if ( $valid_product && ! $is_wc_sub ) {
							$item_qty             = ! WC_AM_FORMAT()->empty( $item->get_quantity() ) ? $item->get_quantity() : 0;
							$refund_qty           = $this->get_qty_refunded_for_item( $order_id, $item_id );
							$values['item_qty']   = $item_qty;
							$values['refund_qty'] = absint( $refund_qty );

							if ( $values['refund_qty'] >= $values['item_qty'] ) {
								continue;
							}

							// Add time remaing from old access expires if this is a renewal item.
							$old_access_expires = wc_get_order_item_meta( $item_id, '_wc_am_access_expires_time_to_add' );

							$values['user_id']            = $this->get_customer_id( $order );
							$values['order_item_id']      = ! WC_AM_FORMAT()->empty( $item_id ) ? (int) $item_id : 0;
							$values['variation_id']       = $variation_id;
							$values['parent_id']          = $parent_product_id;
							$values['product_id']         = $product_id;
							$values['access_expires']     = WC_AM_PRODUCT_DATA_STORE()->get_api_access_expires( $values['product_id'] );
							$values['old_access_expires'] = ( ! WC_AM_FORMAT()->empty( $old_access_expires ) ) ? $old_access_expires : 0;
							$api_product_activations      = WC_AM_PRODUCT_DATA_STORE()->get_api_activations( $values['product_id'] );
							$values['api_activations']    = ! WC_AM_FORMAT()->empty( $api_product_activations ) ? $api_product_activations : apply_filters( 'wc_api_manager_custom_default_api_activations', 1, $values['product_id'] );
							$product_object               = WC_AM_PRODUCT_DATA_STORE()->get_product_object( $values['product_id'] );
							$values['product_title']      = is_object( $product_object ) ? $product_object->get_title() : '';
							$values['activations_total']  = ( $values['api_activations'] * $item_qty ) + ( $refund_qty * $values['api_activations'] );

							if ( WC_AM_FORMAT()->empty( $values['api_activations'] ) ) {
								$values['api_activations']   = apply_filters( 'wc_api_manager_custom_default_api_activations', 1, $values['product_id'] );
								$values['activations_total'] = ( $values['api_activations'] * $item_qty ) + ( $refund_qty * $values['api_activations'] );
							}

							$items[] = $values;
						}
					}
				}

				return $items;
			}
		}

		return [];
	}

	/**
	 * Converted order time to an Epoch time stamp else get the current Epoch time stamp.
	 *
	 * @since   2.0.1
	 * @updated 2.3.13 Eliminate call to get_post_meta().
	 * @updated 2.5 Replace return strtotime( gmdate( 'Y-m-d H:i:s', $order->get_date_created()->getTimestamp() ) );
	 *
	 * @param int|object $order_id
	 *
	 * @return int
	 */
	public function get_order_time_to_epoch_time_stamp( $order_id ) {
		$order = $this->get_order_object( $order_id );

		if ( is_object( $order ) ) {
			return $order->get_date_created()->getTimestamp();
		}

		return $this->get_current_time_stamp();
	}

	/**
	 * Get Order Item ID using Meta Value.
	 *
	 * @since 2.1.3
	 *
	 * @param string $meta_value
	 *
	 * @return bool|int
	 */
	public function get_order_item_id_by_meta_value( $meta_value ) {
		global $wpdb;

		$order_item_id = $wpdb->get_var( $wpdb->prepare( "
			SELECT 		order_item_id
			FROM 		{$wpdb->prefix}woocommerce_order_itemmeta
			WHERE 		meta_value = %s
		", $meta_value ) );

		return ! WC_AM_FORMAT()->empty( $order_item_id ) ? (int) $order_item_id : false;
	}

	/**
	 * Get all Order Item IDs using Meta Value.
	 *
	 * @since   2.1.3
	 * @updated 2.5.5 Use DISTINCT to prevent duplicate order_item_ids.
	 *
	 * @param string $meta_value
	 *
	 * @return array|bool
	 */
	public function get_all_order_item_ids_by_meta_value( $meta_value ) {
		global $wpdb;

		$item_ids = $wpdb->get_results( $wpdb->prepare( "
			SELECT DISTINCT order_item_id
			FROM 		{$wpdb->prefix}woocommerce_order_itemmeta
			WHERE 		meta_value = %s
		", $meta_value ), ARRAY_A );

		return ! WC_AM_FORMAT()->empty( $item_ids ) ? $item_ids : false;
	}

	/**
	 * Get Order ID using Meta Value.
	 *
	 * @since 2.1.3
	 *
	 * @param string $meta_value
	 *
	 * @return bool|int
	 * @throws \Exception
	 */
	public function get_order_id_by_meta_value( $meta_value ) {
		$order_item_id = $this->get_order_item_id_by_meta_value( $meta_value );

		return ! WC_AM_FORMAT()->empty( $order_item_id ) ? $this->get_order_id_by_order_item_id( $order_item_id ) : false;
	}

	/**
	 * Get all Order IDs using Meta Value.
	 *
	 * @since   2.1.3
	 * @updated 2.5.5 Use array_unique() to prevent order_ids duplicates.
	 *
	 * @param string $meta_value
	 *
	 * @return array|bool
	 * @throws \Exception
	 */
	public function get_all_order_ids_by_meta_value( $meta_value ) {
		$order_ids      = [];
		$order_item_ids = $this->get_all_order_item_ids_by_meta_value( $meta_value );

		if ( ! WC_AM_FORMAT()->empty( $order_item_ids ) ) {
			foreach ( $order_item_ids as $key => $item_id ) {
				$order_ids[] = $this->get_order_id_by_order_item_id( $item_id );
			}
		}

		return ! WC_AM_FORMAT()->empty( $order_ids ) ? array_unique( $order_ids ) : false;
	}

	/**
	 * Delete order metadata.
	 *
	 * @since   2.0
	 * @updated 2.5 For WooCommerce HPOS.
	 *
	 * @param int|WC_Product $order
	 * @param string         $meta_key
	 */
	public function delete_meta( $order, $meta_key ) {
		$order = $this->get_order_object( $order );

		if ( is_object( $order ) ) {
			$order->delete_meta_data( $meta_key );
		}
	}

	/**
	 * Update order metadata.
	 *
	 * @since   2.0
	 * @updated 2.5 For WooCommerce HPOS.
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 * @param string    $meta_key
	 * @param mixed     $meta_value
	 */
	public function update_meta( $order, $meta_key, $meta_value ) {
		$order = $this->get_order_object( $order );

		if ( is_object( $order ) ) {
			$order->update_meta_data( $meta_key, $meta_value );
			$order->save_meta_data();
		}
	}

	/**
	 * Return true if the order status is completed.
	 *
	 * @since 2.0
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 *
	 * @return bool
	 */
	public function has_status_completed( $order ) {
		$order = $this->get_order_object( $order );

		return is_object( $order ) && $order->has_status( 'completed' );
	}

	/**
	 * Return true if the order status is processing.
	 *
	 * @since 2.0.20
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 *
	 * @return bool
	 */
	public function has_status_processing( $order ) {
		$order = $this->get_order_object( $order );

		return is_object( $order ) && $order->has_status( 'processing' );
	}

	/**
	 * Return true if the order contains an API product.
	 *
	 * @since   2.0
	 * @updated 2.5 For WooCommerce HPOS.
	 *
	 * @param int|mixed $order WC_Order or order ID.
	 *
	 * @return bool
	 */
	public function has_api_product( $order ) {
		$order = $this->get_order_object( $order );

		if ( is_object( $order ) ) {
			foreach ( $order->get_items() as $item_id => $item ) {
				$product = $item->get_product();

				if ( is_object( $product ) && $product->exists() && WC_AM_PRODUCT_DATA_STORE()->is_api_product( $item->get_product_id() ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if the order contains the Product ID.
	 *
	 * @since 2.1
	 *
	 * @param int|mixed $order      WC_Order or order ID.
	 * @param int       $product_id A Parent, or Varation, Product ID.
	 *
	 * @return bool
	 */
	public function has_product( $order, $product_id ) {
		$order = $this->get_order_object( $order );

		if ( is_object( $order ) ) {
			foreach ( $order->get_items() as $line_item ) {
				if ( $line_item['product_id'] == $product_id || $line_item['variation_id'] == $product_id ) {
					return true;
				}
			}
		}

		return false;
	}
}
