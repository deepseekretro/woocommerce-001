<?php

defined( 'ABSPATH' ) || exit;

/**
 * Admin Post Types handler.
 * 
 * @class ENR_Abstract_Admin_Post_Types
 * @package Class
 */
class ENR_Abstract_Admin_Post_Types {

	/**
	 * Is meta boxes saved once?
	 *
	 * @var bool
	 */
	private static $saved_meta_boxes = false;

	/**
	 * Meta box error messages.
	 *
	 * @var array
	 */
	protected static $meta_box_errors = array();

	/**
	 * ENR_Abstract_Admin_Post_Types constructor.
	 */
	public function __construct() {
		$this->load_list_types();

		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 50 );
		add_action( 'add_meta_boxes', array( $this, 'remove_meta_boxes' ), 50 );
		add_action( 'save_post', array( $this, 'save_meta_boxes' ), 1, 2 );

		add_action( 'enr_process_enr_subsc_plan_posted_meta', 'ENR_Meta_Box_Subscription_Plan_Data::save', 10, 3 );
		add_action( 'enr_process_enr_email_template_posted_meta', 'ENR_Meta_Box_Subscription_Email_Template_Data::save', 10, 3 );

		add_filter( 'post_updated_messages', __CLASS__ . '::post_updated_messages' );
		add_filter( 'bulk_post_updated_messages', __CLASS__ . '::bulk_post_updated_messages', 10, 2 );

		// Error handling
		add_action( 'admin_notices', array( $this, 'output_errors' ) );
		add_action( 'shutdown', array( $this, 'save_errors' ) );
	}

	/**
	 * Load list types.
	 */
	protected function load_list_types() {
		if ( ! class_exists( 'WC_Admin_List_Table', false ) ) {
			include_once WC_ABSPATH . 'includes/admin/list-tables/abstract-class-wc-admin-list-table.php';
		}

		include_once 'list-tables/class-enr-admin-list-table-subscription-plans.php';
		include_once 'list-tables/class-enr-admin-list-table-subscription-email-templates.php';
	}

	/**
	 * Change title boxes in admin.
	 *
	 * @param string  $text Text to shown.
	 * @param WP_Post $post Current post object.
	 * @return string
	 */
	public function enter_title_here( $text, $post ) {
		switch ( $post->post_type ) {
			case 'enr_subsc_plan':
				$text = esc_html__( 'Subscription Plan name', 'enhancer-for-woocommerce-subscriptions' );
				break;
			case 'enr_email_template':
				$text = esc_html__( 'Subscription email template name', 'enhancer-for-woocommerce-subscriptions' );
				break;
		}

		return $text;
	}

	/**
	 * Add Metaboxes.
	 */
	public function add_meta_boxes() {
		add_meta_box( '_enr_subscription_plan_data', ' ', 'ENR_Meta_Box_Subscription_Plan_Data::output', 'enr_subsc_plan', 'normal', 'high' );
		add_meta_box( '_enr_email_template_data', ' ', 'ENR_Meta_Box_Subscription_Email_Template_Data::output', 'enr_email_template', 'normal', 'high' );
	}

	/**
	 * Remove Metaboxes.
	 */
	public function remove_meta_boxes() {
		remove_meta_box( 'commentsdiv', 'enr_subsc_plan', 'normal' );
		remove_meta_box( 'commentsdiv', 'enr_email_template', 'normal' );
	}

	/**
	 * Add an error message.
	 *
	 * @param string $text Error to add.
	 */
	public static function add_error( $text ) {
		self::$meta_box_errors[] = $text;
	}

	/**
	 * Save errors to an option.
	 */
	public function save_errors() {
		update_option( 'enr_meta_box_errors', self::$meta_box_errors );
	}

	/**
	 * Show any stored error messages.
	 */
	public function output_errors() {
		$errors = array_filter( ( array ) get_option( 'enr_meta_box_errors' ) );

		if ( ! empty( $errors ) ) {
			echo '<div id="woocommerce_errors" class="error notice is-dismissible">';
			foreach ( $errors as $error ) {
				echo '<p>' . wp_kses_post( $error ) . '</p>';
			}
			echo '</div>';

			// Clear.
			delete_option( 'enr_meta_box_errors' );
		}
	}

	/**
	 * Check if we're saving, the trigger an action based on the post type.
	 *
	 * @param  int    $post_id Post ID.
	 * @param  object $post Post object.
	 */
	public function save_meta_boxes( $post_id, $post ) {
		$post_id = absint( $post_id );

		// $post_id and $post are required
		if ( empty( $post_id ) || empty( $post ) || self::$saved_meta_boxes ) {
			return;
		}

		// Dont' save meta boxes for revisions or autosaves.
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce.
		if ( empty( $_POST[ 'enr_save_meta_nonce' ] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST[ 'enr_save_meta_nonce' ] ) ), 'enr_save_data' ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		$posted = $_POST;
		if ( empty( $posted[ 'post_ID' ] ) || absint( $posted[ 'post_ID' ] ) !== $post_id ) {
			return;
		}

		// Check user has permission to edit.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		self::$saved_meta_boxes = true;

		if ( in_array( $post->post_type, array( 'enr_subsc_plan', 'enr_email_template' ), true ) ) {
			/**
			 * Process our post types save.
			 * 
			 * @param int $post_id
			 * @param WP_Post $post
			 * @param array $posted
			 * @since 1.0
			 */
			do_action( "enr_process_{$post->post_type}_posted_meta", $post_id, $post, $posted );
		}
	}

	/**
	 * Change messages when a post type is updated.
	 *
	 * @param  array $messages Array of messages.
	 * @return array
	 */
	public static function post_updated_messages( $messages ) {
		$messages[ 'enr_email_template' ] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Email template updated.', 'enhancer-for-woocommerce-subscriptions' ),
			4 => __( 'Email template updated.', 'enhancer-for-woocommerce-subscriptions' ),
			6 => __( 'Email template updated.', 'enhancer-for-woocommerce-subscriptions' ),
			7 => __( 'Email template saved.', 'enhancer-for-woocommerce-subscriptions' ),
			8 => __( 'Email template submitted.', 'enhancer-for-woocommerce-subscriptions' ),
		);

		return $messages;
	}

	/**
	 * Specify custom bulk actions messages for different post types.
	 *
	 * @param  array $bulk_messages Array of messages.
	 * @param  array $bulk_counts Array of how many objects were updated.
	 * @return array
	 */
	public static function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {
		$bulk_messages[ 'enr_email_template' ] = array(
			/* translators: %s: email template count */
			'updated'   => _n( '%s email template updated.', '%s email templates updated.', $bulk_counts[ 'updated' ], 'enhancer-for-woocommerce-subscriptions' ),
			/* translators: %s: email template count */
			'locked'    => _n( '%s email template not updated, somebody is editing it.', '%s email templates not updated, somebody is editing them.', $bulk_counts[ 'locked' ], 'enhancer-for-woocommerce-subscriptions' ),
			/* translators: %s: email template count */
			'deleted'   => _n( '%s email template permanently deleted.', '%s email templates permanently deleted.', $bulk_counts[ 'deleted' ], 'enhancer-for-woocommerce-subscriptions' ),
			/* translators: %s: email template count */
			'trashed'   => _n( '%s email template moved to the Trash.', '%s email templates moved to the Trash.', $bulk_counts[ 'trashed' ], 'enhancer-for-woocommerce-subscriptions' ),
			/* translators: %s: email template count */
			'untrashed' => _n( '%s email template restored from the Trash.', '%s email templates restored from the Trash.', $bulk_counts[ 'untrashed' ], 'enhancer-for-woocommerce-subscriptions' ),
		);

		return $bulk_messages;
	}
}

new ENR_Abstract_Admin_Post_Types();
