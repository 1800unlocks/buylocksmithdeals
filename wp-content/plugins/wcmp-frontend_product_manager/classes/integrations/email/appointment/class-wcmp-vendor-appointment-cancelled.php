<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Appointment is cancelled
 *
 * An email sent to the vendor when an appointment is cancelled or not approved.
 *
 * @class   WC_Email_Vendor_Appointment_Cancelled
 * @extends WC_Email
 */
class WC_Email_Vendor_Appointment_Cancelled extends WC_Email {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id             = 'vendor_appointment_cancelled';
		$this->title          = __( 'Vendor Appointment Cancelled', 'woocommerce-appointments' );
		$this->description    = __( 'Appointment cancelled emails are sent when the status of an appointment goes to cancelled.', 'woocommerce-appointments' );
		$this->template_html  = 'appointment/emails/vendor-appointment-cancelled.php';
		$this->template_plain = 'appointment/emails/plain/vendor-appointment-cancelled.php';
		$this->placeholders   = array(
			'{site_title}'         => $this->get_blogname(),
			'{product_title}'      => '',
			'{appointment_number}' => '',
			'{appointment_start}'  => '',
			'{appointment_end}'    => '',
			'{order_date}'         => '',
			'{order_number}'       => '',
		);

		// Triggers for this email
		add_action( 'woocommerce_appointment_pending-confirmation_to_cancelled_notification', array( $this, 'trigger' ) );
		add_action( 'woocommerce_appointment_confirmed_to_cancelled_notification', array( $this, 'trigger' ) );
		add_action( 'woocommerce_appointment_paid_to_cancelled_notification', array( $this, 'trigger' ) );

		// Call parent constructor
		parent::__construct();

		// Other settings
		$this->template_base = WCMp_AFM_PLUGIN_DIR . 'views/products/';
	}

	/**
	 * Get email subject.
	 *
	 * @since  4.2.9
	 * @return string
	 */
	public function get_default_subject() {
		return __( '[{site_title}]: Appointment for "{product_title}" has been cancelled', 'woocommerce-appointments' );
	}

	/**
	 * Get email heading.
	 *
	 * @since  4.2.9
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Appointment #{appointment_number} Cancelled', 'woocommerce-appointments' );
	}

	/**
	 * trigger function.
	 *
	 * @access public
	 * @param string $appointment_id
	 * @return void
	 */
	public function trigger( $appointment_id ) {
		$this->setup_locale();

		if ( $appointment_id ) {
			$appointment = get_wc_appointment( $appointment_id );

			$appointment_order = $appointment->get_order_id();
            $suborder = get_wcmp_suborders($appointment_order);
            $vendor_id = get_post_meta($suborder[0]->get_id(), '_vendor_id', true);
            $vendor = get_wcmp_vendor($vendor_id);
            $this->recipient = $vendor->user_data->user_email;

			// Check if provided $appointment_id is indeed an $appointment.
			$this->object = wc_appointments_maybe_appointment_object( $appointment );

			$this->placeholders['{appointment_number}'] = $appointment_id;
			$this->placeholders['{appointment_start}']  = $this->object->get_start_date();
			$this->placeholders['{appointment_end}']    = $this->object->get_end_date();

			if ( $this->object->get_product() ) {
				$this->placeholders['{product_title}'] = $this->object->get_product_name();
			}

			if ( $this->object->get_order() ) {
				if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
					$order_date = $this->object->get_order()->order_date;
				} else {
					$order_date = $this->object->get_order()->get_date_created() ? $this->object->get_order()->get_date_created() : '';
				}
				$this->placeholders['{order_date}']   = wc_format_datetime( $order_date );
				$this->placeholders['{order_number}'] = $this->object->get_order()->get_order_number();
			} else {
				$this->placeholders['{order_date}']   = date_i18n( wc_date_format(), strtotime( $this->object->appointment_date ) );
				$this->placeholders['{order_number}'] = __( 'N/A', 'woocommerce-appointments' );
			}

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}
	}

	/**
	 * get_content_html function.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_html() {
		ob_start();
		wc_get_template(
			$this->template_html,
			array(
				'appointment'   => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => false,
				'email'         => $this,
			),
			'',
			$this->template_base
		);

		return ob_get_clean();
	}

	/**
	 * get_content_plain function.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template(
			$this->template_plain,
			array(
				'appointment'   => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
				'plain_text'    => true,
				'email'         => $this,
			),
			'',
			$this->template_base
		);

		return ob_get_clean();
	}

    /**
     * Initialise Settings Form Fields
     *
     * @access public
     * @return void
     */
    public function init_form_fields() {
    	$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-appointments' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'woocommerce-appointments' ),
				'default' => 'yes',
			),
			'recipient'  => array(
				'title'       => __( 'Recipient(s)', 'woocommerce-appointments' ),
				'type'        => 'text',
				/* translators: %s: WP admin email */
				'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to assigned staff and <code>%s</code>.', 'woocommerce-appointments' ), esc_attr( get_option( 'admin_email' ) ) ),
				'placeholder' => '',
				'default'     => '',
				'desc_tip'    => true,
			),
			'subject'    => array(
				'title'       => __( 'Subject', 'woocommerce-appointments' ),
				'type'        => 'text',
				/* translators: %s: list of placeholders */
				'description' => sprintf( __( 'Available placeholders: %s', 'woocommerce-appointments' ), '<code>{product_title}, {appointment_number}, {appointment_start}, {appointment_end}, {order_date}, {order_number}</code>' ),
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
				'desc_tip'    => true,
			),
			'heading'    => array(
				'title'       => __( 'Email Heading', 'woocommerce-appointments' ),
				'type'        => 'text',
				/* translators: %s: list of placeholders */
				'description' => sprintf( __( 'Available placeholders: %s', 'woocommerce-appointments' ), '<code>{product_title}, {appointment_number}, {appointment_start}, {appointment_end}, {order_date}, {order_number}</code>' ),
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
				'desc_tip'    => true,
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'woocommerce-appointments' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'woocommerce-appointments' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
    }
}
