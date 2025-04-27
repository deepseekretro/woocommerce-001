<?php

defined( 'ABSPATH' ) || exit;

/**
 * Subscription Email Template Data.
 * 
 * @class ENR_Meta_Box_Subscription_Email_Template_Data
 * @package Class
 */
class ENR_Meta_Box_Subscription_Email_Template_Data {

	/**
	 * Available WC_Email::id.
	 * 
	 * @var array 
	 */
	protected static $available_wc_email_ids = array(
		'_enr_customer_subscription_price_updated',
		'_enr_customer_auto_renewal_reminder',
		'_enr_customer_manual_renewal_reminder',
		'_enr_customer_trial_ending_reminder',
		'_enr_customer_expiry_reminder',
	);

	/**
	 * Available WC emails.
	 * 
	 * @var WC_Email[]
	 */
	protected static $available_wc_emails = array();

	/**
	 * Get the default WC_Email::id before save.
	 * 
	 * @var string 
	 */
	protected static $default_wc_email_id = '_enr_customer_subscription_price_updated';

	/**
	 * Return an array of available WC emails.
	 * 
	 * @return array
	 */
	public static function get_available_wc_emails() {
		if ( ! empty( self::$available_wc_emails ) ) {
			return self::$available_wc_emails;
		}

		foreach ( ENR_Emails::get_emails() as $wc_email ) {
			if ( in_array( $wc_email->id, self::$available_wc_email_ids ) ) {
				self::$available_wc_emails[ $wc_email->id ] = $wc_email;
			}
		}

		return self::$available_wc_emails;
	}

	/**
	 * Get an array of placeholders.
	 * 
	 * @param string $for_wc_email_id
	 * @return array
	 */
	public static function get_placeholders( $for_wc_email_id = '' ) {
		$placeholders = array();

		foreach ( self::get_available_wc_emails() as $wc_email_id => $wc_email ) {
			if ( empty( $wc_email->multiple_content_placeholders ) ) {
				continue;
			}

			foreach ( $wc_email->multiple_content_placeholders as $placeholder_key => $placeholder_value ) {
				switch ( $placeholder_key ) {
					case '{customer_name}':
						$placeholders[ $wc_email_id ][ $placeholder_key ] = __( 'To display customer name.', 'enhancer-for-woocommerce-subscriptions' );
						break;
					case '{customer_first_name}':
						$placeholders[ $wc_email_id ][ $placeholder_key ] = __( 'To display customer first name.', 'enhancer-for-woocommerce-subscriptions' );
						break;
					case '{customer_last_name}':
						$placeholders[ $wc_email_id ][ $placeholder_key ] = __( 'To display customer last name.', 'enhancer-for-woocommerce-subscriptions' );
						break;
					case '{view_subscription_url}':
						$placeholders[ $wc_email_id ][ $placeholder_key ] = __( 'To display subscription url.', 'enhancer-for-woocommerce-subscriptions' );
						break;
					case '{renewal_amount}':
						$placeholders[ $wc_email_id ][ $placeholder_key ] = __( 'To display renewal amount.', 'enhancer-for-woocommerce-subscriptions' );
						break;
					case '{trial_end_date}':
						$placeholders[ $wc_email_id ][ $placeholder_key ] = __( 'To display trial end date.', 'enhancer-for-woocommerce-subscriptions' );
						break;
					case '{next_payment_date}':
						$placeholders[ $wc_email_id ][ $placeholder_key ] = __( 'To display next payment date.', 'enhancer-for-woocommerce-subscriptions' );
						break;
					case '{end_date}':
						$placeholders[ $wc_email_id ][ $placeholder_key ] = __( 'To display subscription end date.', 'enhancer-for-woocommerce-subscriptions' );
						break;
					case '{subscription_price_changed_details}':
						$placeholders[ $wc_email_id ][ $placeholder_key ] = __( 'To display subscription price changed details.', 'enhancer-for-woocommerce-subscriptions' );
						break;
					case '{subscription_details}':
						$placeholders[ $wc_email_id ][ $placeholder_key ] = __( 'To display subscription details.', 'enhancer-for-woocommerce-subscriptions' );
						break;
					case '{trial_end_details}':
						$placeholders[ $wc_email_id ][ $placeholder_key ] = __( 'To display trial end details.', 'enhancer-for-woocommerce-subscriptions' );
						break;
					case '{subscription_end_details}':
						$placeholders[ $wc_email_id ][ $placeholder_key ] = __( 'To display subscription end details.', 'enhancer-for-woocommerce-subscriptions' );
						break;
					case '{customer_addresses}':
						$placeholders[ $wc_email_id ][ $placeholder_key ] = __( 'To display customer addresses.', 'enhancer-for-woocommerce-subscriptions' );
						break;
				}
			}
		}

		if ( ! empty( $for_wc_email_id ) && isset( $placeholders[ $for_wc_email_id ] ) ) {
			return $placeholders[ $for_wc_email_id ];
		}

		return $placeholders;
	}

	/**
	 * Get an array of default data.
	 * 
	 * @param string $for_wc_email_id
	 * @return array
	 */
	public static function get_default_data( $for_wc_email_id = '' ) {
		$data = array();

		foreach ( self::get_available_wc_emails() as $wc_email_id => $wc_email ) {
			$data[ $wc_email_id ][ 'placeholder' ][ 'email_subject' ] = $wc_email->get_default_subject();
			/* translators: %s: email subject */
			$data[ $wc_email_id ][ 'description' ][ 'email_subject' ] = sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'enhancer-for-woocommerce-subscriptions' ), $wc_email->get_default_subject() );

			$data[ $wc_email_id ][ 'placeholder' ][ 'email_heading' ] = $wc_email->get_default_heading();
			/* translators: %s: email heading */
			$data[ $wc_email_id ][ 'description' ][ 'email_heading' ] = sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'enhancer-for-woocommerce-subscriptions' ), $wc_email->get_default_heading() );

			$data[ $wc_email_id ][ 'email_content' ] = $wc_email->get_default_content();

			switch ( $wc_email_id ) {
				case '_enr_customer_trial_ending_reminder':
					$data[ $wc_email_id ][ 'description' ][ 'email_mapping_key' ] = __( 'day(s) before trial end date', 'enhancer-for-woocommerce-subscriptions' );
					break;
				case '_enr_customer_expiry_reminder':
					$data[ $wc_email_id ][ 'description' ][ 'email_mapping_key' ] = __( 'day(s) before subscription expiry date', 'enhancer-for-woocommerce-subscriptions' );
					break;
				default:
					$data[ $wc_email_id ][ 'description' ][ 'email_mapping_key' ] = __( 'day(s) before subscription due date', 'enhancer-for-woocommerce-subscriptions' );
					break;
			}
		}

		if ( ! empty( $for_wc_email_id ) && isset( $data[ $for_wc_email_id ] ) ) {
			return $data[ $for_wc_email_id ];
		}

		return $data;
	}

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post $post
	 */
	public static function output( $post ) {
		global $post;

		$selected_wc_email_id       = ENR_Subscription_Email_Template::get_prop( $post->ID, 'wc_email_id' );
		$selected_email_mapping_key = ENR_Subscription_Email_Template::get_prop( $post->ID, 'email_mapping_key' );
		$selected_email_subject     = ENR_Subscription_Email_Template::get_prop( $post->ID, 'email_subject' );
		$selected_email_heading     = ENR_Subscription_Email_Template::get_prop( $post->ID, 'email_heading' );
		$selected_email_content     = ENR_Subscription_Email_Template::get_prop( $post->ID, 'email_content' );

		$available_wc_emails    = self::get_available_wc_emails();
		$email_placeholders     = self::get_placeholders( $selected_wc_email_id ? $selected_wc_email_id : self::$default_wc_email_id );
		$default_data           = self::get_default_data( $selected_wc_email_id ? $selected_wc_email_id : self::$default_wc_email_id );
		$selected_email_content = ! empty( $selected_email_content ) ? $selected_email_content : $default_data[ 'email_content' ];

		$product_terms                      = ENR_Admin::get_product_term_options( array( 'taxonomy' => 'product_cat' ) );
		$selected_email_product_filter      = ENR_Subscription_Email_Template::get_prop( $post->ID, 'email_product_filter' );
		$selected_email_included_products   = ENR_Subscription_Email_Template::get_prop( $post->ID, 'email_included_products' );
		$selected_email_included_categories = ENR_Subscription_Email_Template::get_prop( $post->ID, 'email_included_categories' );

		include 'views/html-subscription-email-template-data.php';
	}

	/**
	 * Save meta box data.
	 *
	 * @param int $post_id
	 * @param WP_Post $post
	 */
	public static function save( $post_id, $post, $posted ) {
		update_post_meta( $post_id, '_wc_email_id', wc_clean( wp_unslash( $posted[ '_wc_email_id' ] ) ) );
		update_post_meta( $post_id, '_email_product_filter', wc_clean( wp_unslash( $posted[ '_email_product_filter' ] ) ) );
		update_post_meta( $post_id, '_email_included_products', isset( $posted[ '_email_included_products' ] ) ? array_values( array_filter( ( array ) wp_unslash( $posted[ '_email_included_products' ] ) ) ) : array()  );
		update_post_meta( $post_id, '_email_included_categories', isset( $posted[ '_email_included_categories' ] ) ? array_values( array_filter( ( array ) wp_unslash( $posted[ '_email_included_categories' ] ) ) ) : array()  );
		update_post_meta( $post_id, '_email_subject', wc_clean( wp_unslash( $posted[ '_email_subject' ] ) ) );
		update_post_meta( $post_id, '_email_heading', wc_clean( wp_unslash( $posted[ '_email_heading' ] ) ) );

		$posted_post_data = array(
			'post_excerpt' => sanitize_title( wp_unslash( $posted[ '_email_mapping_key' ] ) ),
			'post_content' => wp_unslash( $posted[ '_email_content' ] ),
		);

		if ( doing_action( 'save_post' ) ) {
			$GLOBALS[ 'wpdb' ]->update( $GLOBALS[ 'wpdb' ]->posts, $posted_post_data, array( 'ID' => $post_id ) );
			clean_post_cache( $post_id );
		} else {
			wp_update_post( array_merge( array( 'ID' => $post_id ), $posted_post_data ) );
		}
	}
}
