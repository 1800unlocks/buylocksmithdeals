<?php
/**
 * WCMp_AFM_Appointments_Endpoint setup
 *
 * @package  WCMp_AFM/classes/endpoints
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Appointments_Endpoint {
 /**
     * Stores errors.
     *
     * @var array
     */
    private $errors = array();

    /**
     * Output the form.
     *
     * @version  3.0.0
     */

     public function output() {
        global $wp;

        $current_vendor_id = afm()->vendor_id;
        $appointment_id = absint( $wp->query_vars['appointments'] );
        $statuses = get_post_stati();
        unset( $statuses['trash']);
        $appointments = array( 'post_status' => $statuses, 'post_type' => 'wc_appointment' );
        $appointment_orders = get_posts( $appointments );
        $vendor_appointments = WCMp_AFM_Appointment_Integration::get_vendor_appointment_array();
        $vendor_appointments_id = wp_list_pluck( $vendor_appointments, 'ID' );
        if ( ! $current_vendor_id || ! apply_filters( 'vendor_list_appointment', true, $current_vendor_id ) ) {
            ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <?php esc_html_e( 'You do not have permission to view this content. Please contact site administrator.', 'wcmp-afm' ); ?>
                </div>
            </div>
            <?php
            return;
        }
        if( !$appointment_orders ) {
            afm()->template->get_template( 'products/appointment/html-endpoint-appointments.php' );
        } elseif ( $appointment_id ) {
            $appointment = new WC_Appointment( $appointment_id );
            afm()->template->get_template( 'products/appointment/html-endpoint-single-appointment.php', array( 'appointment' => $appointment ) );
        } else {

        $filters = array();
        $appointable_products = WCMp_AFM_Appointment_Integration::get_vendor_appointable_products( 'publish' );
        foreach ( $appointable_products as $product ) {
            $filters[$product->get_id()] = $product->get_name();
        }
        $appointments_params = array(
            'ajax_url'               => admin_url( 'admin-ajax.php' ),
            'post_status'            => ! empty( $_GET['post_status'] ) ? wc_clean( $_GET['post_status'] ) : '',
            'empty_table'            => esc_js( __( 'No appointments found!', 'wcmp-afm' ) ),
            'processing'             => esc_js( __( 'Processing...', 'wcmp-afm' ) ),
            'info'                   => esc_js( __( 'Showing _START_ to _END_ of _TOTAL_ appointments', 'wcmp-afm' ) ),
            'info_empty'             => esc_js( __( 'Showing 0 to 0 of 0 appointments', 'wcmp-afm' ) ),
            'length_menu'            => esc_js( __( 'Number of rows _MENU_', 'wcmp-afm' ) ),
            'zero_records'           => esc_js( __( 'No matching appointments found', 'wcmp-afm' ) ),
            'next'                   => esc_js( __( 'Next', 'wcmp-afm' ) ),
            'previous'               => esc_js( __( 'Previous', 'wcmp-afm' ) ),
            'reload'                 => esc_js( __( 'Reload', 'wcmp-afm' ) ),
            'booking_filter_default' => esc_js( __( 'All appointmentable Products', 'woocommerce-appointments' ) ),
            'booking_filter_options' => json_encode( $filters ),
        );
        wp_localize_script( 'afm-appointments-js', 'appointments_params', $appointments_params );
        wp_enqueue_script( 'afm-appointments-js' );

        afm()->template->get_template( 'products/appointment/html-endpoint-appointments1.php' );
    }
    }
    
}
