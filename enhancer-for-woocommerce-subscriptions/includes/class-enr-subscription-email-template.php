<?php

defined( 'ABSPATH' ) || exit;

/**
 * Subscription Email Template handler.
 * 
 * @class ENR_Subscription_Email_Template
 * @package Class
 */
class ENR_Subscription_Email_Template {

	/**
	 * Core data for the email template.
	 *
	 * @var array
	 */
	protected static $data = array();

	/**
	 * Default data for the email template.
	 *
	 * @var array
	 */
	protected static $default_data = array(
		'name'                      => '',
		'wc_email_id'               => '',
		'email_mapping_key'         => '',
		'email_subject'             => '',
		'email_heading'             => '',
		'email_content'             => '',
		'email_product_filter'      => 'all-products',
		'email_included_products'   => array(),
		'email_included_categories' => array(),
	);

	/**
	 * Post instance of the email template.
	 *
	 * @var WP_Post[]
	 */
	protected static $post = array();

	const POST_TYPE = 'enr_email_template';

	/**
	 * Gets a default prop.
	 *
	 * @param string $prop Name of prop to get.
	 * @return mixed
	 */
	public static function get_default_prop( $prop ) {
		return isset( self::$default_data[ $prop ] ) ? self::$default_data[ $prop ] : null;
	}

	/**
	 * Get the cached email template post instance.
	 * 
	 * @param int $template_id
	 * @return false|WP_Post
	 */
	public static function maybe_get_post( $template_id ) {
		if ( ! $template_id || ! is_numeric( $template_id ) ) {
			return false;
		}

		if ( ! isset( self::$post[ $template_id ] ) ) {
			$post = get_post( $template_id );

			if ( ! $post || self::POST_TYPE !== $post->post_type ) {
				return false;
			}

			self::$post[ $template_id ] = $post;
		}

		return self::$post[ $template_id ];
	}

	/**
	 * Check whether the template exists?
	 * 
	 * @param int $template_id
	 * @return bool
	 */
	public static function exists( $template_id ) {
		$post = self::maybe_get_post( $template_id );
		return is_a( $post, 'WP_Post' );
	}

	/**
	 * Gets a prop.
	 * Gets the value from either cached data or from db itself.
	 *
	 * @param int $template_id
	 * @param string $prop Name of prop to get.
	 * @param bool $default
	 * @return mixed
	 */
	public static function get_prop( $template_id, $prop, $default = true ) {
		if ( isset( self::$data[ $template_id ][ $prop ] ) ) {
			return self::$data[ $template_id ][ $prop ];
		}

		if ( is_null( self::get_default_prop( $prop ) ) ) {
			return null;
		}

		if ( ! self::exists( $template_id ) ) {
			return $default ? self::get_default_prop( $prop ) : null;
		}

		if ( 'name' === $prop ) {
			self::$data[ $template_id ][ $prop ] = self::$post[ $template_id ]->post_title;
		} else if ( 'email_mapping_key' === $prop ) {
			self::$data[ $template_id ][ $prop ] = self::$post[ $template_id ]->post_excerpt;
		} else if ( 'email_content' === $prop ) {
			self::$data[ $template_id ][ $prop ] = self::$post[ $template_id ]->post_content;
		} else {
			$metadata = get_post_meta( $template_id );

			if ( isset( $metadata[ "_$prop" ][ 0 ] ) ) {
				self::$data[ $template_id ][ $prop ] = maybe_unserialize( $metadata[ "_$prop" ][ 0 ] );
			}
		}

		return isset( self::$data[ $template_id ][ $prop ] ) ? self::$data[ $template_id ][ $prop ] : ( $default ? self::get_default_prop( $prop ) : '' );
	}
}
