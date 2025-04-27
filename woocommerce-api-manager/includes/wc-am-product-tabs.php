<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce API Manager Product Manager Admin Class
 *
 * @since       3.3.0
 *
 * @author      Kestrel
 * @author      Copyright (c) Kestrel [hey@kestrelwp.com]
 * @package     WooCommerce API Manager/Product Tabs
 */

/**
 * WC_AM_Product_Tabs class.
 */
class WC_AM_Product_Tabs {

	public function __construct() {
		add_filter( 'woocommerce_product_tabs', [ $this, 'api_manager_document_tabs' ] );
	}

	/**
	 * Build the array of tabs and their callback function.
	 *
	 * @since 1.0
	 *
	 * @param array $tabs
	 *
	 * @return mixed
	 */
	public function api_manager_document_tabs( $tabs ) {
		$docs = [
			[
				'tab'      => 'api_product_documentation',
				'title'    => __( 'Documentation', 'woocommerce-api-manager-product-tabs' ),
				'priority' => 51,
				'callback' => 'api_product_documentation',
				'option'   => true,
			],
			[
				'tab'      => 'api_description',
				'title'    => __( 'API Description', 'woocommerce-api-manager-product-tabs' ),
				'priority' => 52,
				'callback' => 'api_description',
				'option'   => 'woocommerce_api_manager_description',
			],
			[
				'tab'      => 'api_changelog',
				'title'    => __( 'Changelog', 'woocommerce-api-manager-product-tabs' ),
				'priority' => 53,
				'callback' => 'api_changelog',
				'option'   => true,
			],
			[
				'tab'      => 'api_installation',
				'title'    => __( 'Installation', 'woocommerce-api-manager-product-tabs' ),
				'priority' => 54,
				'callback' => 'api_installation',
				'option'   => 'woocommerce_api_manager_installation',
			],
			[
				'tab'      => 'api_faq',
				'title'    => __( 'FAQ', 'woocommerce-api-manager-product-tabs' ),
				'priority' => 55,
				'callback' => 'api_faq',
				'option'   => 'woocommerce_api_manager_faq',
			],
			[
				'tab'      => 'api_screenshots',
				'title'    => __( 'Screenshots', 'woocommerce-api-manager-product-tabs' ),
				'priority' => 56,
				'callback' => 'api_screenshots',
				'option'   => 'woocommerce_api_manager_screenshots',
			],
			[
				'tab'      => 'api_other_notes',
				'title'    => __( 'Other Notes', 'woocommerce-api-manager-product-tabs' ),
				'priority' => 57,
				'callback' => 'api_other_notes',
				'option'   => 'woocommerce_api_manager_other_notes',
			],
		];

		foreach ( $docs as $k => $v ) {
			if ( $v['option'] === true || get_option( $v['option'] ) == 'yes' ) {
				$tabs[ $v['tab'] ] = [
					'title'    => $v['title'],
					'priority' => $v['priority'],
					'callback' => [ $this, $v['callback'] ],
				];
			}
		}

		// Default data in addition to changelog doc.
		$release_notes = [
			'tab'      => 'api_release_notes',
			'title'    => __( 'Release Notes', 'woocommerce-api-manager-product-tabs' ),
			'priority' => 50,
			'callback' => 'api_release_notes',
		];

		$tabs[ $release_notes['tab'] ] = [
			'title'    => $release_notes['title'],
			'priority' => $release_notes['priority'],
			'callback' => [ $this, $release_notes['callback'] ],
		];

		return $tabs;
	}

	/**
	 * Renders tab content.
	 *
	 * @since 3.3.0
	 */
	public function api_product_documentation() {
		global $product;

		$post_id = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $product->get_id(), '_api_product_documentation' ) );

		if ( ! empty( $post_id ) ) {
			printf( esc_html__( '%1$sDocumentation%2$s', 'woocommerce-api-manager-product-tabs' ), '<h2>', '</h2>' );
			echo wp_kses_post( $this->get_page_content( get_post( $post_id ) ) );
		} else {
			echo '<style>#tab-title-api_product_documentation{display: none;}</style>';
		}
	}

	/**
	 * Renders tab content.
	 *
	 * @since 3.3.0
	 */
	public function api_description() {
		global $product;

		$post_id = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $product->get_id(), '_api_description' ) );

		if ( ! empty( $post_id ) ) {
			printf( esc_html__( '%1$sAPI Description%2$s', 'woocommerce-api-manager-product-tabs' ), '<h2>', '</h2>' );
			echo wp_kses_post( $this->get_page_content( get_post( $post_id ) ) );
		} else {
			echo '<style>#tab-title-api_description{display: none;}</style>';
		}
	}

	/**
	 * Renders tab content.
	 *
	 * @since 3.3.0
	 */
	public function api_changelog() {
		global $product;

		$post_id = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $product->get_id(), '_api_changelog' ) );

		if ( ! empty( $post_id ) ) {
			printf( esc_html__( '%1$sChangelog%2$s', 'woocommerce-api-manager-product-tabs' ), '<h2>', '</h2>' );
			echo wp_kses_post( $this->get_page_content( get_post( $post_id ) ) );
		} else {
			echo '<style>#tab-title-api_changelog{display: none;}</style>';
		}
	}

	/**
	 * Renders tab content.
	 *
	 * @since 3.3.0
	 */
	public function api_installation() {
		global $product;

		$post_id = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $product->get_id(), 'api_installation' ) );

		if ( ! empty( $post_id ) ) {
			printf( esc_html__( '%1$sInstallation%2$s', 'woocommerce-api-manager-product-tabs' ), '<h2>', '</h2>' );
			echo wp_kses_post( $this->get_page_content( get_post( $post_id ) ) );
		} else {
			echo '<style>#tab-title-api_installation{display: none;}</style>';
		}
	}

	/**
	 * Renders tab content.
	 *
	 * @since 3.3.0
	 */
	public function api_faq() {
		global $product;

		$post_id = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $product->get_id(), 'api_faq' ) );

		if ( ! empty( $post_id ) ) {
			printf( esc_html__( '%1$sFAQ%2$s', 'woocommerce-api-manager-product-tabs' ), '<h2>', '</h2>' );
			echo wp_kses_post( $this->get_page_content( get_post( $post_id ) ) );
		} else {
			echo '<style>#tab-title-api_faq{display: none;}</style>';
		}
	}

	/**
	 * Renders tab content.
	 *
	 * @since 3.3.0
	 */
	public function api_screenshots() {
		global $product;

		$post_id = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $product->get_id(), 'api_screenshots' ) );

		if ( ! empty( $post_id ) ) {
			printf( esc_html__( '%1$sScreenshots%2$s', 'woocommerce-api-manager-product-tabs' ), '<h2>', '</h2>' );
			echo wp_kses_post( $this->get_page_content( get_post( $post_id ) ) );
		} else {
			echo '<style>#tab-title-api_screenshots{display: none;}</style>';
		}
	}

	/**
	 * Renders tab content.
	 *
	 * @since 3.3.0
	 */
	public function api_other_notes() {
		global $product;

		$post_id = absint( WC_AM_PRODUCT_DATA_STORE()->get_meta( $product->get_id(), 'api_other_notes' ) );

		if ( ! empty( $post_id ) ) {
			printf( esc_html__( '%1$sOther Notes%2$s', 'woocommerce-api-manager-product-tabs' ), '<h2>', '</h2>' );
			echo wp_kses_post( $this->get_page_content( get_post( $post_id ) ) );
		} else {
			echo '<style>#tab-title-api_other_notes{display: none;}</style>';
		}
	}

	/**
	 * Renders tab content.
	 *
	 * @since 3.3.0
	 */
	public function api_release_notes() {
		global $product;

		$post_id = $product->get_id();

		if ( ! empty( $post_id ) ) {
			printf( __( '%1$sRelease Notes%2$s', 'woocommerce-api-manager-product-tabs' ), '<h2>', '</h2>' ); // phpcs:ignore
			printf( __( '%s' ), '<tr class="woocommerce-product-attributes-item woocommerce-product-attributes-item--attribute_release_notes">' ); // phpcs:ignore
			printf( __( '%s', 'woocommerce-api-manager-product-tabs' ), '<ul>' ); // phpcs:ignore
			printf( __( '%1$s%2$s%3$s%4$s', 'woocommerce-api-manager-product-tabs' ), '<li>', __( 'Version: ', 'woocommerce-api-manager-product-tabs' ), ! empty( $this->get_meta_data( $post_id, '_api_new_version' ) ) ? $this->get_meta_data( $post_id, '_api_new_version' ) : '', '</li>' ); // phpcs:ignore
			printf( __( '%1$s%2$s%3$s%4$s', 'woocommerce-api-manager-product-tabs' ), '<li>', __( 'Requires WordPress Version: ', 'woocommerce-api-manager-product-tabs' ), ! empty( $this->get_meta_data( $post_id, '_api_version_required' ) ) ? $this->get_meta_data( $post_id, '_api_version_required' ) . __( ' or higher', 'woocommerce-api-manager-product-tabs' ) : '', '</li>' ); // phpcs:ignore
			printf( __( '%1$s%2$s%3$s%4$s', 'woocommerce-api-manager-product-tabs' ), '<li>', __( 'WordPress Version Compatible Up To: ', 'woocommerce-api-manager-product-tabs' ), ! empty( $this->get_meta_data( $post_id, '_api_tested_up_to' ) ) ? $this->get_meta_data( $post_id, '_api_tested_up_to' ) : '', '</li>' ); // phpcs:ignore
			printf( __( '%1$s%2$s%3$s%4$s', 'woocommerce-api-manager-product-tabs' ), '<li>', __( 'Requires PHP Version: ', 'woocommerce-api-manager-product-tabs' ), ! empty( $this->get_meta_data( $post_id, '_api_requires_php' ) ) ? $this->get_meta_data( $post_id, '_api_requires_php' ) . __( ' or higher', 'woocommerce-api-manager-product-tabs' ) : '', '</li>' ); // phpcs:ignore
			printf( __( '%1$s%2$s%3$s%4$s', 'woocommerce-api-manager-product-tabs' ), '<li>', __( 'Last Updated: ', 'woocommerce-api-manager-product-tabs' ), ! empty( $this->get_meta_data( $post_id, '_api_last_updated' ) ) ? $this->get_meta_data( $post_id, '_api_last_updated' ) : '', '</li>' ); // phpcs:ignore
			printf( __( '%s', 'woocommerce-api-manager-product-tabs' ), '</ul>' ); // phpcs:ignore
			printf( __( '%s' ), '</tr>' ); // phpcs:ignore
		} else {
			echo '<style>#tab-title-api_release_notes{display: none;}</style>';
		}
	}

	/**
	 * Returns the meta data for the post_id.
	 *
	 * @since 3.3.0
	 *
	 * @param int $key
	 * @param int $post_id
	 *
	 * @return bool|mixed
	 */
	private function get_meta_data( $post_id, $key ) {
		return WC_AM_PRODUCT_DATA_STORE()->get_meta( $post_id, $key );
	}

	/**
	 * Returns page content if it exists.
	 *
	 * @since 3.3.0
	 *
	 * @param $page_obj
	 *
	 * @return string
	 */
	private function get_page_content( $page_obj ) {
		if ( isset( $page_obj ) && is_object( $page_obj ) ) {
			if ( ! empty( $page_obj->post_content ) ) {
				return wp_kses_post( $page_obj->post_content );
			} else {
				return '';
			}
		}

		return '';
	}
}
