<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Admin Subscription Email Templates handler.
 * 
 * @class ENR_Admin_List_Table_Email_Templates
 * @package Class
 */
class ENR_Admin_List_Table_Email_Templates extends WC_Admin_List_Table {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $list_table_type = 'enr_email_template' ;

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
			'cb'            => $columns[ 'cb' ],
			'template_name' => __( 'Template Name', 'enhancer-for-woocommerce-subscriptions' ),
			'wc_email_id'   => __( 'For Email ID', 'enhancer-for-woocommerce-subscriptions' ),
			'date'          => __( 'Created Date', 'enhancer-for-woocommerce-subscriptions' ),
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
			'template_name' => 'post_title',
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
			$query_vars[ 'orderby' ] = 'ID' ;
		}

		if ( empty( $query_vars[ 'order' ] ) ) {
			$query_vars[ 'order' ] = 'DESC' ;
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
		switch ( $column ) {
			case 'template_name':
				printf( '<a href="%1$s">%2$s</a>', esc_url( get_admin_url( null, 'post.php?post=' . $post_id . '&action=edit' ) ), wp_kses_post( ENR_Subscription_Email_Template::get_prop( $post_id, 'name' ) ) ) ;
				break ;
			case 'wc_email_id':
				$selected_email_id = ENR_Subscription_Email_Template::get_prop( $post_id, 'wc_email_id' ) ;

				foreach ( ENR_Emails::get_emails() as $email ) {
					if ( $selected_email_id === $email->id ) {
						echo '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=email&section=' . strtolower( get_class( $email ) ) ) ) . '" target="_blank">' . esc_html( $email->get_title() ) . '</a>' ;
					}
				}
				break ;
		}
	}
}

new ENR_Admin_List_Table_Email_Templates() ;
