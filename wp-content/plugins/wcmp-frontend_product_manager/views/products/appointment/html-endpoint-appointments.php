<?php
/**
 * Vendor dashboard Bookings->All Bookings menu template
 *
 * Used by WCMp_AFM_Bookings_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/appointment/html-endpoint-appointments.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/booking
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;

echo '<div class="woocommerce-BlankState">';

echo '<h2 class="woocommerce-BlankState-message">' . esc_html__( 'Ready to start accepting appointments?', 'woocommerce-appointments' ) . '</h2>';

echo '<div class="woocommerce-BlankState-buttons">';

// echo '<a class="woocommerce-BlankState-cta button-primary button" href="' . esc_url(wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_aa_appointment_endpoint', 'vendor', 'general', 'create-appointment'))) . '">' . esc_html__( 'Add New Appointment', 'woocommerce-appointments' ) . '</a>';
echo '<a class="woocommerce-BlankState-cta button" href="' . esc_url(wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_edit_product_endpoint', 'vendor', 'general', 'add-product'))) . '">' . esc_html__( 'Add Appointable Product', 'woocommerce-appointments' ) . '</a>';

echo '</div>';

echo '</div>';