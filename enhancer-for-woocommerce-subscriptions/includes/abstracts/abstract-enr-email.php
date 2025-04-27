<?php

/**
 * Email Class.
 * 
 * @abstract ENR_Abstract_Email
 * @extends WC_Email
 */
abstract class ENR_Abstract_Email extends WC_Email {

	/**
	 * Email supports.
	 *
	 * @var array Supports
	 */
	public $supports = array();

	/**
	 * Email template ID.
	 *
	 * @var int
	 */
	public $email_template_id;

	/**
	 * Strings to find/replace in multiple content supported email.
	 *
	 * @var array
	 */
	public $multiple_content_placeholders = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->template_base                 = _enr()->template_path();
		$this->multiple_content_placeholders = array_merge(
				array(
					'{customer_name}'       => '',
					'{customer_first_name}' => '',
					'{customer_last_name}'  => '',
				),
				$this->multiple_content_placeholders
		);

		// Call WC_Email constuctor
		parent::__construct();
	}

	/**
	 * Check email supports the given type.
	 *
	 * @param string $type
	 * @return bool
	 */
	public function supports( $type ) {
		return in_array( $type, $this->supports );
	}

	/**
	 * Collect multiple content placeholders.
	 */
	protected function collect_multiple_content_placeholders() {
		$user = new WP_User( $this->object->get_user_id() );

		$this->multiple_content_placeholders[ '{customer_name}' ]         = ucfirst( $user->display_name );
		$this->multiple_content_placeholders[ '{customer_first_name}' ]   = ucfirst( $user->first_name );
		$this->multiple_content_placeholders[ '{customer_last_name}' ]    = ucfirst( $user->last_name );
		$this->multiple_content_placeholders[ '{view_subscription_url}' ] = '<a href="' . esc_url( $this->object->get_view_order_url() ) . '">#' . esc_html( $this->object->get_order_number() ) . '</a>';
		$this->multiple_content_placeholders[ '{renewal_amount}' ]        = $this->object->get_formatted_order_total();
		$this->multiple_content_placeholders[ '{next_payment_date}' ]     = $this->object->get_time( 'next_payment' ) > 0 ? date_i18n( wc_date_format(), $this->object->get_time( 'next_payment', 'site' ) ) : '';

		ob_start();
		WC_Subscriptions_Email::order_details( $this->object, false, 'plain' === $this->get_email_type(), $this );
		$this->multiple_content_placeholders[ '{subscription_details}' ] = ob_get_clean();

		ob_start();
		wc_get_template( 'emails/email-addresses.php', array( 'order' => $this->object, 'sent_to_admin' => false ) );
		$this->multiple_content_placeholders[ '{customer_addresses}' ] = ob_get_clean();
	}

	/**
	 * Default content to show below main email content.
	 *
	 * @return string
	 */
	public function get_default_additional_content() {
		return __( 'Thanks for shopping with us.', 'enhancer-for-woocommerce-subscriptions' );
	}

	/**
	 * Get default content to show as main email content.
	 *
	 * @return string
	 */
	public function get_default_content() {
		return '';
	}

	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_subject() {
		$subject = $this->get_option( 'subject', $this->get_default_subject() );

		if ( $this->supports( 'multiple_content' ) && ENR_Subscription_Email_Template::exists( $this->email_template_id ) ) {
			$new_subject = ENR_Subscription_Email_Template::get_prop( $this->email_template_id, 'email_subject' );

			if ( ! empty( $new_subject ) ) {
				$subject = $new_subject;
			}
		}

		/**
		 * Get the email subject.
		 * 
		 * @param string $subject
		 * @param object $object
		 * @param WC_Email $this
		 * @since 1.0
		 */
		return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->format_string( $subject ), $this->object, $this );
	}

	/**
	 * Get email heading.
	 *
	 * @return string
	 */
	public function get_heading() {
		$heading = $this->get_option( 'heading', $this->get_default_heading() );

		if ( $this->supports( 'multiple_content' ) && ENR_Subscription_Email_Template::exists( $this->email_template_id ) ) {
			$new_heading = ENR_Subscription_Email_Template::get_prop( $this->email_template_id, 'email_heading' );

			if ( ! empty( $new_heading ) ) {
				$heading = $new_heading;
			}
		}

		/**
		 * Get the email heading.
		 * 
		 * @param string $subject
		 * @param object $object
		 * @param WC_Email $this
		 * @since 1.0
		 */
		return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $heading ), $this->object, $this );
	}

	/**
	 * Get email content.
	 *
	 * @return string
	 */
	public function get_content() {
		$email_content = parent::get_content();

		if ( $this->supports( 'multiple_content' ) && ENR_Subscription_Email_Template::exists( $this->email_template_id ) ) {
			$new_content = ENR_Subscription_Email_Template::get_prop( $this->email_template_id, 'email_content' );

			if ( empty( $new_content ) ) {
				return $email_content;
			}

			$this->collect_multiple_content_placeholders();

			ob_start();
			wc_get_template( 'emails/email-header.php', array( 'email_heading' => $this->get_heading() ) );
			$email_header = ob_get_clean();

			ob_start();
			wc_get_template( 'emails/email-footer.php' );
			$email_footer = ob_get_clean();

			if ( 'plain' === $this->get_email_type() ) {
				$email_content = wp_strip_all_tags( $email_header );
				$email_content .= str_replace( array_keys( $this->multiple_content_placeholders ), array_values( $this->multiple_content_placeholders ), wp_strip_all_tags( $new_content ) );
				$email_content .= wp_strip_all_tags( $email_footer );
				$email_content = wordwrap( $email_content, 70 );
			} else {
				$email_content = $email_header;
				$email_content .= str_replace( array_keys( $this->multiple_content_placeholders ), array_values( $this->multiple_content_placeholders ), $new_content );
				$email_content .= $email_footer;
			}
		}

		return $email_content;
	}

	/**
	 * Get content html.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'subscription'       => $this->object,
			'email_heading'      => $this->get_heading(),
			'additional_content' => is_callable( array( $this, 'get_additional_content' ) ) ? $this->get_additional_content() : '',
			'sent_to_admin'      => false,
			'plain_text'         => false,
			'email'              => $this,
				), '', $this->template_base );
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'subscription'       => $this->object,
			'email_heading'      => $this->get_heading(),
			'additional_content' => is_callable( array( $this, 'get_additional_content' ) ) ? $this->get_additional_content() : '',
			'sent_to_admin'      => false,
			'plain_text'         => true,
			'email'              => $this,
				), '', $this->template_base );
	}

	/**
	 * Initialize settings form fields.
	 */
	public function init_form_fields() {
		/* translators: %s: list of placeholders */
		$placeholder_text  = sprintf( __( 'Available placeholders: %s', 'enhancer-for-woocommerce-subscriptions' ), '<code>' . esc_html( implode( '</code>, <code>', array_keys( $this->placeholders ) ) ) . '</code>' );
		$this->form_fields = array(
			'enabled'            => array(
				'title'   => __( 'Enable/Disable', 'enhancer-for-woocommerce-subscriptions' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'enhancer-for-woocommerce-subscriptions' ),
				'default' => 'yes',
			),
			'subject'            => array(
				'title'       => __( 'Subject', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'text',
				/* translators: %s: email subject */
				'description' => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'enhancer-for-woocommerce-subscriptions' ), $this->subject ),
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'heading'            => array(
				'title'       => __( 'Email Heading', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'text',
				/* translators: %s: email heading */
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'enhancer-for-woocommerce-subscriptions' ), $this->heading ),
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'additional_content' => array(
				'title'       => __( 'Additional content', 'enhancer-for-woocommerce-subscriptions' ),
				'description' => __( 'Text to appear below the main email content.', 'enhancer-for-woocommerce-subscriptions' ) . ' ' . $placeholder_text,
				'css'         => 'width:400px; height: 75px;',
				'placeholder' => __( 'N/A', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'textarea',
				'default'     => $this->get_default_additional_content(),
				'desc_tip'    => true,
			),
			'email_type'         => array(
				'title'       => __( 'Email type', 'enhancer-for-woocommerce-subscriptions' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'enhancer-for-woocommerce-subscriptions' ),
				'default'     => 'html',
				'class'       => 'email_type',
				'options'     => array(
					'plain'     => _x( 'Plain text', 'email type', 'enhancer-for-woocommerce-subscriptions' ),
					'html'      => _x( 'HTML', 'email type', 'enhancer-for-woocommerce-subscriptions' ),
					'multipart' => _x( 'Multipart', 'email type', 'enhancer-for-woocommerce-subscriptions' ),
				),
			),
		);
	}

	/**
	 * WPML compatibility.
	 * 
	 * @since 3.9.0
	 */
	protected function wpml_switch_language() {
		/**
		 * WPML switch language.
		 * 
		 * @since 3.9.0
		 */
		do_action( 'wpml_switch_language', $this->object->get_meta('wpml_language') );
	}
}
