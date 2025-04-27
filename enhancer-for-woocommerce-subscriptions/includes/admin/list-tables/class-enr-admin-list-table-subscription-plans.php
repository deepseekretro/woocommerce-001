<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Admin Subscription plan handler.
 * 
 * @class ENR_Admin_List_Table_Subscription_Plans
 * @package Class
 */
class ENR_Admin_List_Table_Subscription_Plans extends WC_Admin_List_Table {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $list_table_type = 'enr_subsc_plan' ;

	/**
	 * To not show blank slate.
	 *
	 * @param string $which String which tablenav is being shown.
	 */
	public function maybe_render_blank_state( $which ) {
	}

	/**
	 * Define which columns to show on this screen.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function define_columns( $columns ) {
		$columns = array(
			'cb'   => $columns[ 'cb' ],
			'name' => __( 'Plan Name', 'enhancer-for-woocommerce-subscriptions' ),
			'type' => __( 'Plan Type', 'enhancer-for-woocommerce-subscriptions' ),
			'date' => __( 'Created Date', 'enhancer-for-woocommerce-subscriptions' ),
				) ;

		return $columns ;
	}

	/**
	 * Define which columns are sortable.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function define_sortable_columns( $columns ) {
		$custom = array(
			'name' => 'post_title',
			'type' => 'type',
				) ;

		return wp_parse_args( $custom, $columns ) ;
	}

	/**
	 * Define bulk actions.
	 *
	 * @param array $actions Existing actions.
	 * @return array
	 */
	public function define_bulk_actions( $actions ) {
		unset( $actions[ 'edit' ] ) ;
		return $actions ;
	}

	/**
	 * Get row actions to show in the list table.
	 *
	 * @param array   $actions Array of actions.
	 * @param WP_Post $post Current post object.
	 * @return array
	 */
	public function get_row_actions( $actions, $post ) {
		unset( $actions[ 'inline hide-if-no-js' ] ) ;
		return $actions ;
	}

	/**
	 * Handle any custom filters.
	 *
	 * @param array $query_vars Query vars.
	 * @return array
	 */
	public function query_filters( $query_vars ) {
		//Sorting
		if ( empty( $query_vars[ 'orderby' ] ) ) {
			$query_vars[ 'orderby' ] = 'menu_order' ;
		}

		if ( empty( $query_vars[ 'order' ] ) ) {
			$query_vars[ 'order' ] = 'ASC' ;
		}

		if ( ! empty( $query_vars[ 'orderby' ] ) ) {
			switch ( $query_vars[ 'orderby' ] ) {
				case 'type':
					$query_vars[ 'meta_key' ] = "_{$query_vars[ 'orderby' ]}" ;
					$query_vars[ 'orderby' ]  = 'meta_value' ;
					break ;
			}
		}

		return $query_vars ;
	}

	/**
	 * Render individual columns.
	 *
	 * @param string $column Column ID to render.
	 * @param int    $post_id Post ID.
	 */
	public function render_columns( $column, $post_id ) {
		$type = ENR_Subscription_Plan::get_type( $post_id ) ;

		switch ( $column ) {
			case 'name':
				printf( '<a href="%1$s">%2$s</a>', esc_url( get_admin_url( null, 'post.php?post=' . $post_id . '&action=edit' ) ), wp_kses_post( ENR_Subscription_Plan::get_prop( $post_id, $type, 'name' ) ) ) ;
				break ;
			case 'type':
				$available_types = _enr_get_subscription_plan_types() ;
				echo isset( $available_types[ $type ] ) ? wp_kses_post( $available_types[ $type ] ) : '' ;
				break ;
		}
	}
}

new ENR_Admin_List_Table_Subscription_Plans() ;
