<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Post Types
 * 
 * Registers post types
 * 
 * @class ENR_Post_Types
 * @package Class
 */
class ENR_Post_Types {

	/**
	 * Init ENR_Post_Types.
	 */
	public static function init() {
		add_action( 'init', __CLASS__ . '::register_post_types' ) ;
	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( ! post_type_exists( 'enr_subsc_plan' ) ) {
			register_post_type( 'enr_subsc_plan', array(
				'labels'              => array(
					'name'               => __( 'Subscription Plans', 'enhancer-for-woocommerce-subscriptions' ),
					'singular_name'      => _x( 'Subscription Plan', 'enr_subsc_plan post type singular name', 'enhancer-for-woocommerce-subscriptions' ),
					'menu_name'          => _x( 'Subscription Plans', 'Admin menu name', 'enhancer-for-woocommerce-subscriptions' ),
					'add_new'            => __( 'Add subscription plan', 'enhancer-for-woocommerce-subscriptions' ),
					'add_new_item'       => __( 'Add new subscription plan', 'enhancer-for-woocommerce-subscriptions' ),
					'new_item'           => __( 'New subscription plan', 'enhancer-for-woocommerce-subscriptions' ),
					'edit_item'          => __( 'Edit subscription plan', 'enhancer-for-woocommerce-subscriptions' ),
					'view_item'          => __( 'View subscription plan', 'enhancer-for-woocommerce-subscriptions' ),
					'search_items'       => __( 'Search subscription plans', 'enhancer-for-woocommerce-subscriptions' ),
					'not_found'          => __( 'No subscription plan found.', 'enhancer-for-woocommerce-subscriptions' ),
					'not_found_in_trash' => __( 'No subscription plan found in Trash.', 'enhancer-for-woocommerce-subscriptions' ),
				),
				'description'         => __( 'This is where store subscription plans are stored.', 'enhancer-for-woocommerce-subscriptions' ),
				'public'              => false,
				'show_ui'             => true,
				'capability_type'     => 'post',
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_in_menu'        => false,
				'show_in_admin_bar'   => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'has_archive'         => false,
				'supports'            => array( 'title' ),
				'map_meta_cap'        => true,
				'capabilities'        => array(
					'delete_posts' => true,
				),
			) ) ;
		}

		if ( ! post_type_exists( 'enr_email_template' ) ) {
			register_post_type( 'enr_email_template', array(
				'labels'              => array(
					'name'               => __( 'Subscription Email Templates', 'enhancer-for-woocommerce-subscriptions' ),
					'singular_name'      => _x( 'Subscription Email Template', 'enr_email_template post type singular name', 'enhancer-for-woocommerce-subscriptions' ),
					'menu_name'          => _x( 'Subscription Email Templates', 'Admin menu name', 'enhancer-for-woocommerce-subscriptions' ),
					'add_new'            => __( 'Add subscription email template', 'enhancer-for-woocommerce-subscriptions' ),
					'add_new_item'       => __( 'Add new subscription email template', 'enhancer-for-woocommerce-subscriptions' ),
					'new_item'           => __( 'New subscription email template', 'enhancer-for-woocommerce-subscriptions' ),
					'edit_item'          => __( 'Edit subscription email template', 'enhancer-for-woocommerce-subscriptions' ),
					'view_item'          => __( 'View subscription email template', 'enhancer-for-woocommerce-subscriptions' ),
					'search_items'       => __( 'Search subscription email templates', 'enhancer-for-woocommerce-subscriptions' ),
					'not_found'          => __( 'No subscription email template found.', 'enhancer-for-woocommerce-subscriptions' ),
					'not_found_in_trash' => __( 'No subscription email template found in Trash.', 'enhancer-for-woocommerce-subscriptions' ),
				),
				'description'         => __( 'This is where store subscription email templates are stored.', 'enhancer-for-woocommerce-subscriptions' ),
				'public'              => false,
				'show_ui'             => true,
				'capability_type'     => 'post',
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_in_menu'        => false,
				'show_in_admin_bar'   => false,
				'show_in_nav_menus'   => false,
				'rewrite'             => false,
				'query_var'           => false,
				'has_archive'         => false,
				'supports'            => array( 'title' ),
				'map_meta_cap'        => true,
				'capabilities'        => array(
					'delete_posts' => true,
				),
			) ) ;
		}
	}
}

ENR_Post_Types::init() ;
