<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Auto Renewal Reminder Email.
 *
 * An email will be sent to the customer before the subscription price is to be charged automatically from the customer account.
 *
 * @class ENR_Email_Customer_Auto_Renewal_Reminder
 * @extends ENR_Abstract_Email
 */
class ENR_Email_Customer_Auto_Renewal_Reminder extends ENR_Abstract_Email {

	/**
	 * Email supports.
	 *
	 * @var array Supports
	 */
	public $supports = array( 'multiple_content' );

	/**
	 * Strings to find/replace in multiple content supported email.
	 *
	 * @var array Supports
	 */
	public $multiple_content_placeholders = array(
		'{view_subscription_url}' => '',
		'{next_payment_date}'     => '',
		'{renewal_amount}'        => '',
		'{subscription_details}'  => '',
		'{customer_addresses}'    => '',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = ENR_PREFIX . 'customer_auto_renewal_reminder';
		$this->customer_email = true;
		$this->title          = __( 'Auto Renewal Reminder', 'enhancer-for-woocommerce-subscriptions' );
		$this->description    = __( 'Auto renewal reminder emails are sent to the customers(subscribers) when their automatically renewing subscriptions are due for renewal.', 'enhancer-for-woocommerce-subscriptions' );
		$this->heading        = __( 'Your subscription is going to auto renew', 'enhancer-for-woocommerce-subscriptions' );
		$this->subject        = __( 'Your {blogname} subscription is going to auto renew', 'enhancer-for-woocommerce-subscriptions' );
		$this->template_html  = 'emails/customer-auto-renewal-reminder.php';
		$this->template_plain = 'emails/plain/customer-auto-renewal-reminder.php';

		add_action( 'enr_wc_subscriptions_remind_before_auto_renewal_notification', array( $this, 'trigger' ), 10, 2 );

		// Call parent constructor
		parent::__construct();
	}

	/**
	 * Get default content to show as main email content.
	 *
	 * @return string
	 */
	public function get_default_content() {
		return __( 'Your Subscription {view_subscription_url} is due for renewal on {next_payment_date}. Please make sure that you have sufficient funds in your account. {customer_addresses}', 'enhancer-for-woocommerce-subscriptions' );
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param WC_Subscription|false $subscription Subscription object.
	 * @param int|null $day_to_remind
	 */
	public function trigger( $subscription, $day_to_remind = null ) {
		$subscription = _enr_maybe_get_subscription_instance( $subscription );

		if ( $subscription ) {
			$this->object            = $subscription;
			$this->recipient         = $this->object->get_billing_email();
			$this->email_template_id = is_numeric( $day_to_remind ) ? _enr_get_mapped_email_template_id( $this->id, $day_to_remind, $this->object ) : null;
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->wpml_switch_language();
		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}
}

return new ENR_Email_Customer_Auto_Renewal_Reminder();
