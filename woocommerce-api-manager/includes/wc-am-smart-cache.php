<?php

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Smart Cache Class
 *
 * @since       2.0.12
 * @version     2.2.0
 *
 * @author      Kestrel
 * @author      Copyright (c) Kestrel [hey@kestrelwp.com]
 * @package     WooCommerce API Manager/Smart Cache
 */
class WC_AM_Smart_Cache {

	/**
	 * Transients to set on shutdown.
	 *
	 * @var array Array of transient keys.
	 */
	private array $set_transients = [];

	/**
	 * Transients to delete on shutdown.
	 *
	 * @var array Array of transient keys.
	 */
	private array $delete_transients = [];

	/**
	 * @var null
	 */
	private static $_instance = null;

	/**
	 * @static
	 * @return \WC_AM_Smart_Cache
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	private function __construct() {
		add_action( 'shutdown', [ $this, 'delete_transients_on_shutdown' ], 10 );
		add_action( 'shutdown', [ $this, 'set_transients_on_shutdown' ], 11 );
		add_action( 'wp', [ $this, 'prevent_caching' ] );
		add_action( 'post_updated', [ $this, 'delete_api_doc_page_cache' ], 10, 1 );
		add_action( 'save_post', [ $this, 'delete_api_doc_page_cache' ], 10, 1 );
		add_action( 'trashed_post', [ $this, 'delete_api_doc_page_cache' ], 10, 1 );
		add_action( 'deleted_post', [ $this, 'delete_api_doc_page_cache' ], 10, 1 );
	}

	/**
	 * Get prefix for use with wp_cache_set. Allows all cache in a group to be invalidated at once.
	 *
	 * @since 2.0.12
	 *
	 * @param string $group Group of cache to get.
	 *
	 * @return string
	 */
	public function get_cache_prefix( $group ) {
		// Get cache key - uses cache key wc_orders_cache_prefix to invalidate when needed.
		$prefix = wp_cache_get( 'wc_' . $group . '_cache_prefix', $group );

		if ( $prefix === false ) {
			$prefix = 1;
			wp_cache_set( 'wc_' . $group . '_cache_prefix', $prefix, $group );
		}

		return 'wc_cache_' . $prefix . '_';
	}

	/**
	 * Get transient version.
	 *
	 * When using transients with unpredictable names, e.g. those containing an md5
	 * hash in the name, we need a way to invalidate them all at once.
	 *
	 * When using default WP transients we're able to do this with a DB query to
	 * delete transients manually.
	 *
	 * With external cache however, this isn't possible. Instead, this function is used
	 * to append a unique string, based on time(), to each transient. When transients
	 * are invalidated, the transient version will increment and data will be regenerated.
	 *
	 * Raised in issue https://github.com/woocommerce/woocommerce/issues/5777.
	 * Adapted from ideas in http://tollmanz.com/invalidation-schemes/.
	 *
	 * @since 2.0.12
	 *
	 * @param string  $group   Name for the group of transients we need to invalidate.
	 * @param boolean $refresh true to force a new version.
	 *
	 * @return string transient version based on time(), 10 digits.
	 */
	public function get_transient_version( $group, $refresh = false ) {
		$transient_name  = $group . '-transient-version';
		$transient_value = get_transient( $transient_name );

		if ( $transient_value === false || $refresh === true ) {
			$transient_value = (string) time();

			set_transient( $transient_name, $transient_value );
		}

		return $transient_value;
	}

	/**
	 * Increment group cache prefix to invalidates cache.
	 *
	 * @since 2.0.12
	 *
	 * @param string $group Group of cache to clear.
	 */
	public function incr_cache_prefix( $group ) {
		wp_cache_incr( 'wc_' . $group . '_cache_prefix', 1, $group );
	}

	/**
	 * Transients that don't need to be set right away can be set on shutdown to avoid repetition.
	 *
	 * @since 2.2.0
	 */
	public function set_transients_on_shutdown() {
		if ( $this->set_transients ) {
			foreach ( $this->set_transients as $key => $data ) {
				$this->refresh_cache( $data );
			}

			$this->set_transients = [];
		}
	}

	/**
	 * Set constants to prevent caching by some plugins.
	 *
	 * @since 2.0.12
	 */
	public function set_nocache_constants() {
		WCAM()->maybe_define_constant( 'DONOTCACHEPAGE', true );
		WCAM()->maybe_define_constant( 'DONOTCACHEOBJECT', true );
		WCAM()->maybe_define_constant( 'DONOTCACHEDB', true );
	}

	/**
	 * Return the transient value, or sets a new value if expired or if the value does not exist.
	 *
	 * @since 2.2.0
	 *
	 * @param string $key        The transient key.
	 * @param mixed  $data       Could be a string, integer, array, or object, or anything.
	 * @param int    $expiration If set to zero (0), value will be autoloaded.
	 *
	 * @return mixed Returns false if transient is expired and/or if $data is empty.
	 */
	public function set_or_get_cache( $key, $data = '', $expiration = 0 ) {
		$transient = get_transient( $key );

		if ( $transient === false && ! WC_AM_FORMAT()->empty( $data ) ) {
			$set = set_transient( $key, $data, empty( $expiration ) ? (int) ( WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS ) : absint( $expiration ) );

			if ( $set ) {
				return get_transient( $key );
			}
		}

		return $transient;
	}

	/**
	 * Return the transient value, or sets a new value if expired or if the value does not exist, using a callback function/method.
	 *
	 * @since 2.2.0
	 *
	 * @param string       $key        The transient key.
	 * @param string|array $callback   name of function, or array of class - method that fetches the data
	 * @param array        $params     arguments passed to $callback
	 * @param int          $expiration If set to zero (0), value will be autoloaded.
	 *
	 * @return mixed
	 */
	public function set_or_get_cache_with_function( $key, $callback, $params = [], $expiration = 0 ) {
		$transient = get_transient( $key );

		if ( $transient === false ) {
			$data = call_user_func_array( $callback, $params );
			set_transient( $key, $data, empty( $expiration ) ? (int) ( WCAM()->get_db_cache_expires() * MINUTE_IN_SECONDS ) : absint( $expiration ) );

			$transient = get_transient( $key );
		}

		return $transient;
	}

	/**
	 * Add a transient to set on shutdown.
	 *
	 * @since 2.2.0
	 *
	 * @param string|array $keys Transient key or keys.
	 */
	public function queue_set_transient( $keys ) {
		$this->set_transients = array_unique( array_merge( is_array( $keys ) ? $keys : [ $keys ], $this->set_transients ), SORT_REGULAR );
	}

	/**
	 * Add a transient to delete on shutdown.
	 *
	 * @since 2.0.12
	 *
	 * @param string|array $keys Transient key or keys.
	 */
	public function queue_delete_transient( $keys ) {
		$this->delete_transients = array_unique( array_merge( is_array( $keys ) ? $keys : [ $keys ], $this->delete_transients ) );
	}

	/**
	 * Refresh cached API Resources by order_id.
	 *
	 * @since   2.2.4
	 *
	 * @param int $order_id
	 */
	public function refresh_cache_by_order_id( $order_id, $refresh = true ) {
		$order = WC_AM_ORDER_DATA_STORE()->get_order_object( $order_id );

		if ( is_object( $order ) ) {
			$user_id = WC_AM_ORDER_DATA_STORE()->get_customer_id( $order );

			if ( ! empty( $user_id ) ) {
				$this->delete_cache( [
					'admin_resources' => [
						'order_id' => $order_id,
						'user_id'  => $user_id,
					],
				], $refresh );
			}
		}
	}

	/**
	 * Refresh cached data. All other cache expires when time limit is reached.
	 *
	 * @since 2.2.0
	 *
	 * @param array $data
	 */
	public function refresh_cache( $data ) {
		/**
		 * Triggered by the API.
		 */
		if ( ! empty( $data['api_key'] ) && ! empty( $data['product_id'] ) ) {
			// Refresh Product specific Authenticated API cache.
			$this->delete_api_cache( $data );

			// Refresh Product specific database API Resource cache.
			if ( ! empty( $data['resources'] ) ) {
				foreach ( $data['resources'] as $resource ) {
					if ( $data['product_id'] == $resource->product_id ) {
						// $mac           = WC_AM_USER()->get_master_api_key( $resource->user_id );
						$sub_parent_id = ! empty( $resource->sub_parent_id ) ? $resource->sub_parent_id : $resource->order_id;

						// WC_AM_API_RESOURCE_DATA_STORE()->get_api_resources_for_master_api_key( $mac ); // Method not used yet.
						// WC_AM_API_RESOURCE_DATA_STORE()->get_api_resources_for_user_id( $resource->user_id ); // Only used by get_api_resources_for_master_api_key( $mac ).
						try {
							WC_AM_API_RESOURCE_DATA_STORE()->get_all_api_resources_for_order_id( $resource->order_id );
						} catch ( Exception $e ) {
							WC_AM_LOG()->log_error( PHP_EOL . esc_html__( 'Details from refresh_cache() method, get_all_api_resources_for_order_id() error.', 'woocommerce-api-manager' ) . PHP_EOL . $e, 'refresh_cache' );
						}

						try {
							WC_AM_API_RESOURCE_DATA_STORE()->get_all_api_resources_for_sub_parent_id( $sub_parent_id );
						} catch ( Exception $e ) {
							WC_AM_LOG()->log_error( PHP_EOL . esc_html__( 'Details from refresh_cache() method, get_all_api_resources_for_sub_parent_id() error.', 'woocommerce-api-manager' ) . PHP_EOL . $e, 'refresh_cache' );
						}

						try {
							WC_AM_API_RESOURCE_DATA_STORE()->get_all_api_non_wc_subscription_resources_for_order_id( $resource->order_id );
						} catch ( Exception $e ) {
							WC_AM_LOG()->log_error( PHP_EOL . esc_html__( 'Details from refresh_cache() method, get_all_api_non_wc_subscription_resources_for_order_id() error.', 'woocommerce-api-manager' ) . PHP_EOL . $e, 'refresh_cache' );
						}

						try {
							WC_AM_API_RESOURCE_DATA_STORE()->get_api_resources_for_user_id_sort_by_product_title( $resource->user_id );
						} catch ( Exception $e ) {
							WC_AM_LOG()->log_error( PHP_EOL . esc_html__( 'Details from refresh_cache() method, get_api_resources_for_user_id_sort_by_product_title() error.', 'woocommerce-api-manager' ) . PHP_EOL . $e, 'refresh_cache' );
						}
					}
				}
			}
			/**
			 * Triggered by the Order screen API Resource Activations metabox, My Account screen Activation delete button,
			 * or an order change such as a new order or updated order.
			 */
		} elseif ( ! empty( $data['admin_resources'] ) ) {
			// Refresh Product specific Authenticated API cache.
			if ( ! empty( $data['admin_resources']['api_key'] ) && ! empty( $data['admin_resources']['product_id'] ) ) {
				$this->delete_api_cache( [
					'api_key'    => $data['admin_resources']['api_key'],
					'product_id' => $data['admin_resources']['product_id'],
					'instance'   => ! empty( $data['admin_resources']['instance'] ) ? $data['admin_resources']['instance'] : '',
				] );
			}

			// $mac           = WC_AM_USER()->get_master_api_key( $data[ 'admin_resources' ][ 'user_id' ] );
			$sub_parent_id = ! empty( $data['admin_resources']['sub_parent_id'] ) ? $data['admin_resources']['sub_parent_id'] : $data['admin_resources']['order_id'];

			/**
			 * Refresh Order specific database API Resource cache for a specific Product.
			 */

			// WC_AM_API_RESOURCE_DATA_STORE()->get_api_resources_for_master_api_key( $mac ); // Method not used yet.
			// WC_AM_API_RESOURCE_DATA_STORE()->get_api_resources_for_user_id( $data[ 'admin_resources' ][ 'user_id' ] );
			try {
				WC_AM_API_RESOURCE_DATA_STORE()->get_all_api_resources_for_order_id( $data['admin_resources']['order_id'] );
			} catch ( Exception $e ) {
				WC_AM_LOG()->log_error( PHP_EOL . esc_html__( 'Details from refresh_cache() method, get_all_api_resources_for_order_id() error.', 'woocommerce-api-manager' ) . PHP_EOL . $e, 'refresh_cache' );
			}

			try {
				WC_AM_API_RESOURCE_DATA_STORE()->get_all_api_resources_for_sub_parent_id( $sub_parent_id );
			} catch ( Exception $e ) {
				WC_AM_LOG()->log_error( PHP_EOL . esc_html__( 'Details from refresh_cache() method, get_all_api_resources_for_sub_parent_id() error.', 'woocommerce-api-manager' ) . PHP_EOL . $e, 'refresh_cache' );
			}

			try {
				WC_AM_API_RESOURCE_DATA_STORE()->get_all_api_non_wc_subscription_resources_for_order_id( $data['admin_resources']['order_id'] );
			} catch ( Exception $e ) {
				WC_AM_LOG()->log_error( PHP_EOL . esc_html__( 'Details from refresh_cache() method, get_all_api_non_wc_subscription_resources_for_order_id() error.', 'woocommerce-api-manager' ) . PHP_EOL . $e, 'refresh_cache' );
			}

			try {
				WC_AM_API_RESOURCE_DATA_STORE()->get_api_resources_for_user_id_sort_by_product_title( $data['admin_resources']['user_id'] );
			} catch ( Exception $e ) {
				WC_AM_LOG()->log_error( PHP_EOL . esc_html__( 'Details from refresh_cache() method, get_api_resources_for_user_id_sort_by_product_title() error.', 'woocommerce-api-manager' ) . PHP_EOL . $e, 'refresh_cache' );
			}
		}
	}

	/**
	 * Delete cached data.
	 *
	 * @since   2.1.7
	 *
	 * @param array $data
	 * @param bool  $refresh
	 *
	 * @version 2.2.0
	 */
	public function delete_cache( $data, $refresh = false ) {
		/**
		 * Triggered by the API.
		 */
		if ( ! empty( $data['api_key'] ) && ! empty( $data['product_id'] ) ) {
			// Delete Product specific Authenticated API cache.
			$this->delete_api_cache( $data );

			// Delete Product specific database API Resource cache.
			if ( ! empty( $data['resources'] ) ) {
				foreach ( $data['resources'] as $resource ) {
					if ( $data['product_id'] == $resource->product_id ) {
						$mac           = WC_AM_USER()->get_master_api_key( $resource->user_id );
						$sub_parent_id = ! empty( $resource->sub_parent_id ) ? $resource->sub_parent_id : $resource->order_id;

						$transients_for_deletion = [
							'wc_am_get_ar_for_mac_' . $mac,
							'wc_am_get_ar_for_mac_ar_' . $mac,
							'wc_am_get_ar_for_mac_and_poak_' . $data['api_key'],
							'wc_am_get_ar_for_mac_and_poak_ar_' . $data['api_key'],
							'wc_am_get_api_resources_for_user_id_' . $resource->user_id,
							'wc_am_get_api_resources_for_user_id_ar_' . $resource->user_id,
							'wc_am_get_all_api_resources_for_order_id_' . $resource->order_id,
							'wc_am_get_all_api_resources_for_order_id_ar_' . $resource->order_id,
							'wc_am_get_sub_parent_order_id_for_related_order_' . $resource->order_id,
							'wc_am_get_all_api_resources_for_sub_parent_id_' . $sub_parent_id,
							'wc_am_get_all_api_resources_for_sub_parent_id_ar_' . $sub_parent_id,
							'wc_am_get_all_api_non_wc_sub_resources_for_order_id_' . $resource->order_id,
							'wc_am_get_all_api_non_wc_sub_resources_for_order_id_ar_' . $resource->order_id,
							'wc_am_get_api_resources_for_user_id_sort_by_product_title_' . $resource->user_id,
							'wc_am_get_api_resources_for_user_id_sort_by_product_title_ar_' . $resource->user_id,
						];

						$this->delete_transients( $transients_for_deletion );
						// $this->queue_delete_transient( $transients_for_deletion );
					}
				}

				if ( $refresh ) {
					try {
						$this->queue_set_transient( [ (string) random_int( 1, 99999 ) => $data ] );
					} catch ( Exception $e ) {
						WC_AM_LOG()->log_error( PHP_EOL . esc_html__( 'Details from delete_cache() method, random_int() error.', 'woocommerce-api-manager' ) . PHP_EOL . $e, 'delete_cache' );
					}
				}
			}
			/**
			 * Triggered by the Order screen API Resource Activations metabox, My Account screen Activation delete button,
			 * or an order change such as a new order or updated order.
			 */
		} elseif ( ! empty( $data['admin_resources'] ) ) { // From the Order screen API Resource Activations metabox, or My Account screen Activation, delete button.
			// Delete Product specific Authenticated API cache.
			if ( ! empty( $data['admin_resources']['api_key'] ) && ! empty( $data['admin_resources']['product_id'] ) ) {
				// If this is from WC_AM_Order()->delete_cache() then delete_api_cache() is skipped.
				$this->delete_api_cache( [
					'api_key'    => $data['admin_resources']['api_key'],
					'product_id' => $data['admin_resources']['product_id'],
					'instance'   => ! empty( $data['admin_resources']['instance'] ) ? $data['admin_resources']['instance'] : '',
				] );
			}

			$mac           = WC_AM_USER()->get_master_api_key( $data['admin_resources']['user_id'] );
			$sub_parent_id = ! empty( $data['admin_resources']['sub_parent_id'] ) ? $data['admin_resources']['sub_parent_id'] : $data['admin_resources']['order_id'];

			/**
			 * Delete Order specific database API Resource cache for a specific Product.
			 */
			$transient_order_keys = [
				'wc_am_get_ar_for_mac_' . $mac,
				'wc_am_get_ar_for_mac_ar_' . $mac,
				'wc_am_get_api_resources_for_user_id_' . $data['admin_resources']['user_id'],
				'wc_am_get_api_resources_for_user_id_ar_' . $data['admin_resources']['user_id'],
				'wc_am_get_all_api_resources_for_order_id_' . $data['admin_resources']['order_id'],
				'wc_am_get_all_api_resources_for_order_id_ar_' . $data['admin_resources']['order_id'],
				'wc_am_get_sub_parent_order_id_for_related_order_' . $data['admin_resources']['order_id'],
				'wc_am_get_all_api_resources_for_sub_parent_id_' . $sub_parent_id,
				'wc_am_get_all_api_resources_for_sub_parent_id_ar_' . $sub_parent_id,
				'wc_am_get_all_api_non_wc_sub_resources_for_order_id_' . $data['admin_resources']['order_id'],
				'wc_am_get_all_api_non_wc_sub_resources_for_order_id_ar_' . $data['admin_resources']['order_id'],
				'wc_am_get_api_resources_for_user_id_sort_by_product_title_' . $data['admin_resources']['user_id'],
				'wc_am_get_api_resources_for_user_id_sort_by_product_title_ar_' . $data['admin_resources']['user_id'],
			];

			$this->delete_transients( $transient_order_keys );
			// $this->queue_delete_transient( $transient_order_keys );

			if ( ! empty( $data['admin_resources']['api_key'] ) ) {
				$transient_order_keys = [
					'wc_am_get_ar_for_mac_and_poak_' . $data['admin_resources']['api_key'],
					'wc_am_get_ar_for_mac_and_poak_ar_' . $data['admin_resources']['api_key'],
				];

				$this->delete_transients( $transient_order_keys );
			}

			if ( $refresh ) {
				try {
					$this->queue_set_transient( [ (string) random_int( 1, 99999 ) => $data ] );
				} catch ( Exception $e ) {
					WC_AM_LOG()->log_error( PHP_EOL . esc_html__( 'Details from delete_cache() method, random_int() error.', 'woocommerce-api-manager' ) . PHP_EOL . $e, 'delete_cache' );
				}
			}
		}
	}

	/**
	 * Delete the Authenticated API Cache including related API Key activations cached.
	 *
	 * @since 2.4.9
	 *
	 * @param int $order_id
	 */
	public function delete_activation_api_cache_by_order_id( $order_id ) {
		$activation_resources = WC_AM_API_ACTIVATION_DATA_STORE()->get_activation_resources_by_order_id( $order_id );

		/**
		 * Delete Product specific Authenticated API cache.
		 *
		 * @since 2.4.9
		 */
		if ( ! WC_AM_FORMAT()->empty( $activation_resources ) ) {
			foreach ( $activation_resources as $activation_resource ) {
				$data = [
					'api_key'    => $activation_resource->api_key,
					'product_id' => $activation_resource->product_id,
					'instance'   => $activation_resource->instance,
				];

				$this->delete_api_cache( $data, false );
			}
		}
	}

	/**
	 * Delete product specific API cache.
	 *
	 * @since 2.4.9
	 *
	 * @param array $args
	 */
	private function delete_api_cache( $args = [] ) {
		$trans_hash_status = '';

		if ( ! empty( $args['instance'] ) ) {
			// Authenticated queries.
			$trans_hash_status          = md5( $args['api_key'] . $args['product_id'] . $args['instance'] );
			$trans_hash_info_and_update = md5( $args['api_key'] . $args['product_id'] );
		} else {
			// Unauthenticated query for information only.
			$trans_hash_info_and_update = md5( $args['product_id'] );
		}

		if ( ! empty( $args['instance'] ) && ! empty( $trans_hash_status ) ) {
			$trans_keys_status = [
				'wc_am_api_status_func_data_' . $trans_hash_status,
				'wc_am_api_status_func_top_level_data_' . $trans_hash_status,
			];

			$this->delete_transients( $trans_keys_status );
			// $this->queue_delete_transient( $trans_keys_status );
		}

		if ( ! empty( $trans_hash_info_and_update ) ) {
			$trans_keys_info_and_update = [
				'wc_am_api_information_func_response_active_' . $trans_hash_info_and_update,
				'wc_am_api_information_func_data_active_' . $trans_hash_info_and_update,
				'wc_am_api_information_func_top_level_data_active_' . $trans_hash_info_and_update,
				'wc_am_api_update_func_response_active_' . $trans_hash_info_and_update,
				'wc_am_api_update_func_data_active_' . $trans_hash_info_and_update,
				'wc_am_api_update_func_top_level_data_active_' . $trans_hash_info_and_update,
			];

			$this->delete_transients( $trans_keys_info_and_update );
			// $this->queue_delete_transient( $trans_keys_info_and_update );
		}

		// product_list() cache.
		if ( ! empty( $args['api_key'] ) && ! empty( $args['instance'] ) ) {
			$trans_hash_product_list = md5( $args['api_key'] . 'product_list' . $args['instance'] );

			$trans_keys_product_list = [
				'wc_am_api_product_list_func_data_' . $trans_hash_product_list,
				'wc_am_api_product_list_func_top_level_data_' . $trans_hash_product_list,
			];

			$this->delete_transients( $trans_keys_product_list );
		}
	}

	/**
	 * Delete cached page served by the API to display the more information tabs doc for plugins.
	 *
	 * @since 2.2.0
	 *
	 * @param int $post_id Post/Page ID that links to the Page ID, not the actual Page ID.
	 */
	public function delete_api_doc_page_cache( $post_id ) {
		$trans_keys = [
			'wc_am_doc_tab_api_description_' . $post_id,
			'wc_am_doc_tab_api_installation_' . $post_id,
			'wc_am_doc_tab_api_faq_' . $post_id,
			'wc_am_doc_tab_api_screenshots_' . $post_id,
			'wc_am_doc_tab_api_other_notes_' . $post_id,
			'wc_am_doc_tab_api_changelog_' . $post_id,
		];

		$this->delete_transients( $trans_keys );
		// $this->queue_delete_transient( $trans_keys );
	}

	/**
	 * Transients that don't need to be cleaned right away can be deleted on shutdown to avoid repetition.
	 *
	 * @since 2.0.12
	 */
	public function delete_transients_on_shutdown() {
		if ( ! empty( $this->delete_transients ) ) {
			foreach ( $this->delete_transients as $key ) {
				delete_transient( $key );
			}

			$this->delete_transients = [];
		}
	}

	/**
	 * Delete transients immediately.
	 *
	 * @since   2.4.1
	 * @updated 2.6.4
	 *
	 * @param array $array
	 */
	public function delete_transients( $array ) {
		$array = is_array( $array ) ? $array : [ $array ];

		if ( ! empty( $array ) ) {
			foreach ( $array as $key ) {
				delete_transient( $key );
			}
		}
	}

	/**
	 * Wrapper for nocache_headers which also disables page caching.
	 *
	 * @since 2.0.12
	 */
	public function nocache_headers() {
		$this->set_nocache_constants();
		nocache_headers();
	}

	/**
	 * Prevent caching on certain pages.
	 *
	 * @since 2.0.12
	 */
	public function prevent_caching() {
		// Prevent caching on the root/home URL where the API Manager listens for requests.
		if ( WC_AM_DISABLE_HOMEPAGE_CACHE && WC_AM_URL()->is_home() ) {
			$this->nocache_headers();
		}
	}
}
