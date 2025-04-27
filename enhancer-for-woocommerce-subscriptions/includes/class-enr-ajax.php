<?php

defined( 'ABSPATH' ) || exit;

/**
 * Handle Enhancer for Woocommerce Subscriptions Ajax Event.
 * 
 * @class ENR_Ajax
 * @package Class
 */
class ENR_Ajax {

	/**
	 * Init ENR_Ajax.
	 */
	public static function init() {
		//Get Ajax Events.
		$prefix      = ENR_PREFIX;
		$ajax_events = array(
			'post_ordering'                 => false,
			'subscribe_now'                 => true,
			'json_search_subscription_plan' => false,
			'collect_preview_email_inputs'  => false,
			'preview_email'                 => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( "wp_ajax_{$prefix}{$ajax_event}", __CLASS__ . "::{$ajax_event}" );

			if ( $nopriv ) {
				add_action( "wp_ajax_nopriv_{$prefix}{$ajax_event}", __CLASS__ . "::{$ajax_event}" );
			}
		}
	}

	/**
	 * Ajax request handling for post ordering.
	 */
	public static function post_ordering() {
		global $wpdb;

		$posted = $_REQUEST;
		if ( ! isset( $posted[ 'id' ] ) ) {
			wp_die( -1 );
		}

		$sorting_id  = absint( $posted[ 'id' ] );
		$post_type   = get_post_type( $sorting_id );
		$previd      = absint( isset( $posted[ 'previd' ] ) ? $posted[ 'previd' ] : 0 );
		$nextid      = absint( isset( $posted[ 'nextid' ] ) ? $posted[ 'nextid' ] : 0 );
		$menu_orders = wp_list_pluck( $wpdb->get_results( $wpdb->prepare( "SELECT ID, menu_order FROM {$wpdb->posts} WHERE post_type=%s ORDER BY menu_order ASC, post_title ASC", esc_sql( $post_type ) ) ), 'menu_order', 'ID' );
		$index       = 0;

		foreach ( $menu_orders as $id => $menu_order ) {
			$id = absint( $id );

			if ( $sorting_id === $id ) {
				continue;
			}
			if ( $nextid === $id ) {
				$index++;
			}
			$index++;
			$menu_orders[ $id ] = $index;
			$wpdb->update( $wpdb->posts, array( 'menu_order' => $index ), array( 'ID' => $id ) );
		}

		if ( isset( $menu_orders[ $previd ] ) ) {
			$menu_orders[ $sorting_id ] = $menu_orders[ $previd ] + 1;
		} elseif ( isset( $menu_orders[ $nextid ] ) ) {
			$menu_orders[ $sorting_id ] = $menu_orders[ $nextid ] - 1;
		} else {
			$menu_orders[ $sorting_id ] = 0;
		}

		$wpdb->update( $wpdb->posts, array( 'menu_order' => $menu_orders[ $sorting_id ] ), array( 'ID' => $sorting_id ) );
		wp_send_json( $menu_orders );
	}

	/**
	 * Subscribe now handle.
	 */
	public static function subscribe_now() {
		check_ajax_referer( 'enr-subscribe-now-handle', 'security' );

		$posted   = $_POST;
		$raw_data = wp_parse_args( wp_unslash( $posted[ 'data' ] ) );

		if ( ! empty( $raw_data[ 'enr_subscribe_now_type_nonce' ] ) ) {
			$subscribe_now_class_name = null;
			if ( wp_verify_nonce( sanitize_key( wp_unslash( $raw_data[ 'enr_subscribe_now_type_nonce' ] ) ), 'cart_level' ) ) {
				$subscribe_now_class_name = 'ENR_Cart_Level_Subscribe_Now';
			} else if ( wp_verify_nonce( sanitize_key( wp_unslash( $raw_data[ 'enr_subscribe_now_type_nonce' ] ) ), 'product_level' ) ) {
				$subscribe_now_class_name = 'ENR_Product_Level_Subscribe_Now';
			}

			if ( class_exists( $subscribe_now_class_name ) ) {
				$subscribe_now = new $subscribe_now_class_name();
				$subscribe_now->read_posted_data( $raw_data, $raw_data[ 'enr_subscribed_key' ] );

				if ( is_callable( array( $subscribe_now, 'maybe_force_subscribe' ) ) ) {
					add_filter( 'enr_product_level_subscribe_now_form_args', array( $subscribe_now, 'maybe_force_subscribe' ) );
				}

				wp_send_json_success( array(
					'refresh'                 => 'userdefined' === ENR_Subscription_Plan::get_type( $subscribe_now->get_prop( 'subscribed_plan', null, $raw_data[ 'enr_subscribed_key' ] ) ) ? true : false,
					'subscribe_wrapper_class' => ".enr-{$subscribe_now->get_type()}-subscribe-now-wrapper",
					'html'                    => $subscribe_now->get_subscribe_form( false, false, $raw_data[ 'enr_subscribed_key' ] ),
				) );
			}
		}

		wp_die();
	}

	/**
	 * Search for subscription plan.
	 */
	public static function json_search_subscription_plan() {
		ob_start();

		$requested = $_GET;
		$term      = ( string ) wc_clean( stripslashes( isset( $requested[ 'term' ] ) ? $requested[ 'term' ] : '' ) );
		$exclude   = array();

		if ( isset( $requested[ 'exclude' ] ) && ! empty( $requested[ 'exclude' ] ) ) {
			$exclude = array_map( 'intval', explode( ',', $requested[ 'exclude' ] ) );
		}

		$args = array(
			'post_type'   => 'enr_subsc_plan',
			'post_status' => 'publish',
			'order'       => 'ASC',
			'orderby'     => 'parent title',
			's'           => $term,
			'exclude'     => $exclude,
		);

		if ( is_numeric( $term ) ) {
			unset( $args[ 's' ] );
			$args[ 'post__in' ] = array( ( int ) $term );
		}

		$posts       = get_posts( $args );
		$found_plans = array();

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$found_plans[ $post->ID ] = sprintf( '(#%s) %s', $post->ID, $post->post_title );
			}
		}

		wp_send_json( $found_plans );
	}

	/**
	 * Collect the preview email inputs.
	 */
	public static function collect_preview_email_inputs() {
		check_ajax_referer( 'enr-collect-preview-email-inputs', 'security' );

		if ( ! isset( $_GET[ 'email_id' ] ) ) {
			wp_die();
		}

		$email_id = sanitize_title( wp_unslash( $_GET[ 'email_id' ] ) );

		ob_start();
		include 'admin/views/html-add-preview-email-inputs.php';
		$email_inputs = ob_get_clean();

		wp_send_json_success( array(
			'email_id'     => $email_id,
			'email_inputs' => $email_inputs,
		) );
	}

	/**
	 * Preview email.
	 */
	public static function preview_email() {
		check_ajax_referer( 'enr-preview-email', 'security' );

		try {
			if ( ! isset( $_GET[ 'data' ] ) ) {
				throw new Exception( __( 'Invalid inputs', 'enhancer-for-woocommerce-subscriptions' ) );
			}

			$requested        = $_GET;
			$raw_data         = wp_parse_args( wp_unslash( $requested[ 'data' ] ) );
			$email_id         = sanitize_title( wp_unslash( $raw_data[ 'email_id' ] ) );
			$email_input_args = ( array ) $raw_data[ 'input_args' ];
			$emails           = WC()->mailer()->get_emails();

			foreach ( $emails as $email ) {
				if ( $email_id === $email->id ) {
					foreach ( $email_input_args as $input_arg => $input_value ) {
						$email->{$input_arg} = $input_value;
					}

					if ( 'order' === $email->object_type ) {
						$email->object = wc_get_order( $email->object );

						if ( ! $email->object ) {
							throw new Exception( __( 'Invalid Order Number', 'enhancer-for-woocommerce-subscriptions' ) );
						}
					} else if ( 'subscription' === $email->object_type ) {
						$email->object = wcs_get_subscription( $email->object );

						if ( ! $email->object ) {
							throw new Exception( __( 'Invalid Subscription Number', 'enhancer-for-woocommerce-subscriptions' ) );
						}
					}

					if ( isset( $email->order ) ) {
						$email->order = wc_get_order( $email->order );

						if ( ! $email->order ) {
							throw new Exception( __( 'Invalid Order Number', 'enhancer-for-woocommerce-subscriptions' ) );
						}
					}

					if ( isset( $email->subscriptions ) ) {
						$email->subscriptions = wcs_get_subscriptions_for_switch_order( $email->object );
					}

					if ( isset( $email->retry ) ) {
						$email->retry = WCS_Retry_Manager::store()->get_last_retry_for_order( $email->object->get_id() );
					}

					if ( isset( $email->from_price ) || isset( $email->to_price ) ) {
						$from_price = is_numeric( $email->from_price ) ? $email->from_price : 0;
						$to_price   = is_numeric( $email->to_price ) ? $email->to_price : 0;

						$email->price_changed_items   = array();
						$email->price_changed_items[] = array(
							'from'        => $from_price,
							'to'          => $to_price,
							'from_string' => wcs_price_string( array(
								'recurring_amount'    => $from_price,
								'subscription_period' => 'month',
							) ),
							'to_string'   => wcs_price_string( array(
								'recurring_amount'    => $to_price,
								'subscription_period' => 'month',
							) ),
						);
					}

					/**
					 * Trigger before previewing the email.
					 * 
					 * @since 1.0
					 */
					do_action( 'enr_wc_subscriptions_before_preview_email', $email );

					wp_send_json_success( array(
						'email_id'      => $email->id,
						'email_title'   => $email->get_title(),
						'email_content' => $email->style_inline( $email->get_content() ),
					) );
				}
			}
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => esc_html( $e->getMessage() ) ) );
		}
	}
}

ENR_Ajax::init();
