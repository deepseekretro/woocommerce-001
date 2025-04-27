<?php
defined( 'ABSPATH' ) || exit;

/**
 * Emails class.
 * 
 * @class ENR_Emails
 * @package Class
 */
class ENR_Emails {

	/**
	 * Email notification classes
	 *
	 * @var WC_Email[]
	 */
	protected static $emails = array();

	/**
	 * Available email notification classes to load
	 * 
	 * @var WC_Email::id => WC_Email class
	 */
	protected static $email_classes = array(
		'_enr_customer_processing_shipping_fulfilment_order' => 'ENR_Email_Customer_Processing_Shipping_Fulfilment_Order',
		'_enr_customer_shipping_frequency_notification'      => 'ENR_Email_Customer_Shipping_Frequency_Notification',
		'_enr_customer_subscription_price_updated'           => 'ENR_Email_Customer_Subscription_Price_Updated',
		'_enr_customer_trial_ending_reminder'                => 'ENR_Email_Customer_Trial_Ending_Reminder',
		'_enr_customer_auto_renewal_reminder'                => 'ENR_Email_Customer_Auto_Renewal_Reminder',
		'_enr_customer_manual_renewal_reminder'              => 'ENR_Email_Customer_Manual_Renewal_Reminder',
		'_enr_customer_expiry_reminder'                      => 'ENR_Email_Customer_Expiry_Reminder',
	);

	/**
	 * Init the email class hooks in all emails that can be sent.
	 */
	public static function init() {
		add_filter( 'woocommerce_email_classes', __CLASS__ . '::add_email_classes' );
		add_filter( 'woocommerce_email_actions', __CLASS__ . '::add_email_actions' );
		add_filter( 'woocommerce_email_setting_columns', __CLASS__ . '::add_email_preview_column' );
		add_filter( 'woocommerce_email_setting_column_enr_preview', __CLASS__ . '::add_email_preview_row' );
		add_action( 'admin_footer', __CLASS__ . '::email_inputs_preview_template' );
		add_action( 'admin_footer', __CLASS__ . '::email_preview_template' );
		add_filter( 'woocommerce_email_enabled_new_order', __CLASS__ . '::prevent_sending_shipping_order_wc_email', 99, 2 );
		add_filter( 'woocommerce_email_enabled_customer_completed_order', __CLASS__ . '::prevent_sending_shipping_order_wc_email', 99, 2 );
		add_filter( 'woocommerce_email_enabled_customer_processing_order', __CLASS__ . '::prevent_sending_shipping_order_wc_email', 99, 2 );

		add_action( 'enr_wc_subscriptions_email_subscription_price_changed_details', __CLASS__ . '::subscription_price_changed_details', 10, 5 );
		add_action( 'enr_wc_subscriptions_email_subscription_trial_end_details', __CLASS__ . '::subscription_trial_end_details', 10, 4 );
		add_action( 'enr_wc_subscriptions_email_subscription_end_details', __CLASS__ . '::subscription_end_details', 10, 4 );
	}

	/**
	 * Return the emails
	 *
	 * @return WC_Email[]
	 */
	public static function get_emails() {
		WC()->mailer();
		return self::$emails;
	}

	/**
	 * Load our email classes.
	 * 
	 * @param array $emails
	 */
	public static function add_email_classes( $emails ) {
		if ( ! empty( self::$emails ) ) {
			return $emails + self::$emails;
		}

		include_once 'abstracts/abstract-enr-email.php';

		// Include email classes.
		foreach ( self::$email_classes as $id => $class ) {
			$file_name = 'class-' . strtolower( str_replace( '_', '-', $class ) );
			$path      = ENR_DIR . "includes/emails/{$file_name}.php";

			if ( is_readable( $path ) ) {
				self::$emails[ $class ] = include $path;
			}
		}

		return $emails + self::$emails;
	}

	/**
	 * Hook in all our emails to notify.
	 */
	public static function add_email_actions( $email_actions ) {
		if ( is_callable( array( 'WCS_Staging', 'is_duplicate_site' ) ) && WCS_Staging::is_duplicate_site() && ! defined( 'WCS_FORCE_EMAIL' ) ) {
			return $email_actions;
		}

		$email_actions[] = 'enr_wc_subscriptions_shipping_fulfilment_order_created';
		$email_actions[] = 'enr_wc_subscriptions_shipping_fulfilment_enabled_for_old_orders';
		$email_actions[] = 'enr_wc_subscriptions_remind_subscription_price_changed_before_renewal';
		$email_actions[] = 'enr_wc_subscriptions_remind_before_trial_end';
		$email_actions[] = 'enr_wc_subscriptions_remind_before_auto_renewal';
		$email_actions[] = 'enr_wc_subscriptions_remind_before_manual_renewal';
		$email_actions[] = 'enr_wc_subscriptions_remind_before_expiry';
		return $email_actions;
	}

	/**
	 * Add column for preview.
	 * 
	 * @param array $columns
	 * @return array
	 */
	public static function add_email_preview_column( $columns ) {
		$position = 4;
		$columns  = array_slice( $columns, 0, $position ) + array( 'enr_preview' => __( 'Preview Subscription Emails', 'enhancer-for-woocommerce-subscriptions' ) ) + array_slice( $columns, $position, count( $columns ) - 1 );
		return $columns;
	}

	/**
	 * Add row for preview.
	 * 
	 * @param WC_Email $email
	 */
	public static function add_email_preview_row( $email ) {
		$our_emails  = array_keys( self::$email_classes );
		$core_emails = array(
			'customer_completed_renewal_order',
			'customer_processing_renewal_order',
			'customer_on_hold_renewal_order',
			'customer_completed_switch_order',
			'customer_renewal_invoice',
			'customer_payment_retry',
			'new_renewal_order',
			'new_switch_order',
			'payment_retry',
			'suspended_subscription',
			'cancelled_subscription',
			'expired_subscription',
		);

		/**
		 * Get the subscription emails to preview.
		 * 
		 * @param array $emails
		 * @since 1.0
		 */
		$preview_emails = ( array ) apply_filters( 'enr_wc_subscriptions_preview_emails', array_merge( $core_emails, $our_emails ) );
		if ( in_array( $email->id, $preview_emails ) ) {
			echo '<td class="wc-email-settings-table-enr_preview">
		<a class="button enr-email-preview" href="#" data-email-id="' . esc_attr( $email->id ) . '" title="' . esc_attr__( 'Preview', 'enhancer-for-woocommerce-subscriptions' ) . '"><span class="dashicons dashicons-visibility"></span></a>
            </td>';
		} else {
			echo '<td/>';
		}
	}

	/**
	 * Template for email inputs preview.
	 */
	public static function email_inputs_preview_template() {
		?>
		<script type="text/template" id="tmpl-enr-modal-preview-email-inputs">
		<?php include 'admin/views/html-preview-email-inputs.php'; ?>
		</script>
		<?php
	}

	/**
	 * Template for email preview.
	 */
	public static function email_preview_template() {
		?>
		<script type="text/template" id="tmpl-enr-modal-preview-email">
		<?php include 'admin/views/html-preview-email.php'; ?>
		</script>
		<?php
	}

	/**
	 * Check if we need to send WC emails for shipping orders ?
	 */
	public static function prevent_sending_shipping_order_wc_email( $bool, $order ) {
		$disabled_wc_emails_for_shipping_orders = get_option( ENR_PREFIX . 'disabled_wc_emails_for_shipping_orders', array() );
		if ( empty( $disabled_wc_emails_for_shipping_orders ) ) {
			return $bool;
		}

		$order = wc_get_order( $order );
		if ( ! $order ) {
			return $bool;
		}

		if ( 'shop_order' !== WC_Data_Store::load( 'order' )->get_order_type( $order->get_id() ) || ! _enr_order_contains_shipping_fulfilment( $order ) ) {
			return $bool;
		}

		if ( 'woocommerce_email_enabled_new_order' === current_filter() && in_array( 'new', $disabled_wc_emails_for_shipping_orders ) ) {
			$bool = false;
		} else if ( $order->has_status( $disabled_wc_emails_for_shipping_orders ) ) {
			$bool = false;
		}

		return $bool;
	}

	/**
	 * Show the subscription price changed details table.
	 *
	 * @param WC_Order $subscription  Subscription instance.
	 * @param array $price_changed_items
	 * @param bool     $sent_to_admin If should sent to admin.
	 * @param bool     $plain_text    If is plain text email.
	 * @param string   $email         Email address.
	 */
	public static function subscription_price_changed_details( $subscription, $price_changed_items, $sent_to_admin, $plain_text, $email ) {
		if ( $plain_text ) {
			_enr_get_template(
					'emails/plain/email-subscription-price-changed-details.php',
					array(
						'subscription'        => $subscription,
						'price_changed_items' => $price_changed_items,
						'sent_to_admin'       => $sent_to_admin,
						'plain_text'          => $plain_text,
						'email'               => $email,
					)
			);
		} else {
			_enr_get_template(
					'emails/email-subscription-price-changed-details.php',
					array(
						'subscription'        => $subscription,
						'price_changed_items' => $price_changed_items,
						'sent_to_admin'       => $sent_to_admin,
						'plain_text'          => $plain_text,
						'email'               => $email,
					)
			);
		}
	}

	/**
	 * Show the subscription trial end details table.
	 *
	 * @param WC_Order $subscription  Subscription instance.
	 * @param bool     $sent_to_admin If should sent to admin.
	 * @param bool     $plain_text    If is plain text email.
	 * @param string   $email         Email address.
	 */
	public static function subscription_trial_end_details( $subscription, $sent_to_admin, $plain_text, $email ) {
		if ( $plain_text ) {
			_enr_get_template(
					'emails/plain/email-subscription-trial-end-details.php',
					array(
						'subscription'  => $subscription,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
			);
		} else {
			_enr_get_template(
					'emails/email-subscription-trial-end-details.php',
					array(
						'subscription'  => $subscription,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
			);
		}
	}

	/**
	 * Show the subscription end details table.
	 *
	 * @param WC_Order $subscription  Subscription instance.
	 * @param bool     $sent_to_admin If should sent to admin.
	 * @param bool     $plain_text    If is plain text email.
	 * @param string   $email         Email address.
	 */
	public static function subscription_end_details( $subscription, $sent_to_admin, $plain_text, $email ) {
		if ( $plain_text ) {
			_enr_get_template(
					'emails/plain/email-subscription-end-details.php',
					array(
						'subscription'  => $subscription,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
			);
		} else {
			_enr_get_template(
					'emails/email-subscription-end-details.php',
					array(
						'subscription'  => $subscription,
						'sent_to_admin' => $sent_to_admin,
						'plain_text'    => $plain_text,
						'email'         => $email,
					)
			);
		}
	}
}

ENR_Emails::init();
