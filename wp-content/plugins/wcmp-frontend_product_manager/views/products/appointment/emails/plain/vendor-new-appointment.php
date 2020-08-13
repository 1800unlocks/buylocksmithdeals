<?php
/**
 * PLAIN Admin new appointment email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/vendor-new-appointment.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @version     4.2.9.5
 * @since       3.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo '= ' . $email_heading . " =\n\n";

$appointment       = $appointment ? $appointment : get_wc_appointment( 0 );
$appointment_order = $appointment->get_order();

if ( wc_appointment_order_requires_confirmation( $appointment_order ) && $appointment->has_status( array( 'pending-confirmation' ) ) ) {
	/* translators: 1: billing first and last name */
	$opening_paragraph = __( 'A appointment has been made by %s and is awaiting your approval. The details of this appointment are as follows:', 'woocommerce-appointments' );
} else {
	/* translators: 1: billing first and last name */
	$opening_paragraph = __( 'A new appointment has been made by %s. The details of this appointment are as follows:', 'woocommerce-appointments' );
}

if ( $appointment_order ) {
	if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
		$first_name = $appointment_order->billing_first_name;
		$last_name  = $appointment_order->billing_last_name;
	} else {
		$first_name = $appointment_order->get_billing_first_name();
		$last_name  = $appointment_order->get_billing_last_name();
	}
}

if ( $appointment_order && ! empty( $first_name ) && ! empty( $last_name ) ) {
	echo sprintf( $opening_paragraph, $first_name . ' ' . $last_name ) . "\n\n"; // WPCS: XSS ok.
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: 1: appointment product title */
echo sprintf( __( 'Scheduled Product: %s', 'woocommerce-appointments' ), $appointment->get_product_name() ) . "\n";
/* translators: 1: appointment ID */
echo sprintf( __( 'Appointment ID: %s', 'woocommerce-appointments' ), $appointment->get_id() ) . "\n";
/* translators: 1: appointment start date */
echo sprintf( __( 'Appointment Date: %s', 'woocommerce-appointments' ), $appointment->get_start_date() ) . "\n";
/* translators: 1: appointment duration */
echo sprintf( __( 'Appointment Duration: %s', 'woocommerce-appointments' ), $appointment->get_duration() ) . "\n";

$staff = $appointment->get_staff_members( true );
if ( $appointment->has_staff() && $staff ) {
	/* translators: 1: appointment staff names */
	echo sprintf( __( 'Appointment Providers: %s', 'woocommerce-appointments' ), $staff ) . "\n";
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

if ( wc_appointment_order_requires_confirmation( $appointment_order ) && $appointment->has_status( array( 'pending-confirmation' ) ) ) {
	echo __( 'This appointment is awaiting your approval. Please check it and inform the customer if the date is available or not.', 'woocommerce-appointments' ) . "\n\n";
}

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
