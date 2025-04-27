<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Trial Ending Reminder Email.
 *
 * An email will be sent to the customers(subscribers) before their subscription trial is going to end.
 *
 * @class ENR_Email_Customer_Trial_Ending_Reminder
 * @extends ENR_Abstract_Email
 */
class ENR_Email_Customer_Trial_Ending_Reminder extends ENR_Abstract_Email {

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
		'{trial_end_date}'        => '',
		'{trial_end_details}'     => '',
		'{subscription_details}'  => '',
		'{customer_addresses}'    => '',
	);

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = ENR_PREFIX . 'customer_trial_ending_reminder';
		$this->customer_email = true;
		$this->title          = __( 'Trial Ending Reminder', 'enhancer-for-woocommerce-subscriptions' );
		$this->description    = __( 'Trial Ending Reminder emails are sent to the customers(subscribers) before their subscription trial is going to end.', 'enhancer-for-woocommerce-subscriptions' );
		$this->heading        = __( 'Your subscription trial going to end', 'enhancer-for-woocommerce-subscriptions' );
		$this->subject        = __( 'Your {blogname} subscription trial going to end', 'enhancer-for-woocommerce-subscriptions' );
		$this->template_html  = 'emails/customer-trial-ending-reminder.php';
		$this->template_plain = 'emails/plain/customer-trial-ending-reminder.php';

		add_action( 'enr_wc_subscriptions_remind_before_trial_end_notification', array( $this, 'trigger' ), 10, 2 );

		// Call parent constructor
		parent::__construct();
	}

	/**
	 * Get default content to show as main email content.
	 *
	 * @return string
	 */
	public function get_default_content() {
		return __( "Your subscription trial is going to end soon. Here's the details of your subscription. {trial_end_details} {subscription_details} {customer_addresses}", 'enhancer-for-woocommerce-subscriptions' );
	}

	/**
	 * Collect multiple content placeholders.
	 */
	protected function collect_multiple_content_placeholders() {
		parent::collect_multiple_content_placeholders();

		ob_start();
		ENR_Emails::subscription_trial_end_details( $this->object, false, 'plain' === $this->get_email_type(), $this );
		$this->multiple_content_placeholders[ '{trial_end_details}' ] = ob_get_clean();
		$this->multiple_content_placeholders[ '{trial_end_date}' ]    = $this->object->get_time( 'trial_end' ) > 0 ? date_i18n( wc_date_format(), $this->object->get_time( 'trial_end', 'site' ) ) : '';
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

return new ENR_Email_Customer_Trial_Ending_Reminder();
