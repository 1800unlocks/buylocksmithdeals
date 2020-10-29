<?php
/**
 * WCMp_AFM_Bookings_Endpoint setup
 *
 * @package  WCMp_AFM/classes/endpoints
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Bookings_Endpoint {

    public function output() {
        global $wp;

        $current_vendor_id = afm()->vendor_id;
        $booking_id = absint( $wp->query_vars['bookings'] );
        $vendor_bookings = WCMp_AFM_Booking_Integration::get_vendor_booking_array();
        $vendor_bookings_id = wp_list_pluck( $vendor_bookings, 'ID' );
        if ( ! $current_vendor_id || ! current_vendor_can( 'manage_bookings' ) || ( $booking_id && ! in_array( $booking_id, $vendor_bookings_id ) ) ) {
            ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <?php esc_html_e( 'You do not have permission to view this content. Please contact site administrator.', 'wcmp-afm' ); ?>
                </div>
            </div>
            <?php
            return;
        }

        if ( $booking_id ) {
            $booking = new WC_Booking( $booking_id );
            afm()->template->get_template( 'products/booking/html-endpoint-single-booking.php', array( 'booking' => $booking ) );
        } else {
            $filters = array();
            $bookable_products = WCMp_AFM_Booking_Integration::get_vendor_bookable_products( 'publish' );
            foreach ( $bookable_products as $product ) {
                $filters[$product->get_id()] = $product->get_name();

                $resources = $product->get_resources();

                foreach ( $resources as $resource ) {
                    $filters[$resource->get_id()] = '&nbsp;&nbsp;&nbsp;' . $resource->get_name();
                }
            }
            $bookings_params = array(
                'ajax_url'               => admin_url( 'admin-ajax.php' ),
                'post_status'            => ! empty( $_GET['post_status'] ) ? wc_clean( $_GET['post_status'] ) : '',
                'empty_table'            => esc_js( __( 'No bookings found!', 'wcmp-afm' ) ),
                'processing'             => esc_js( __( 'Processing...', 'wcmp-afm' ) ),
                'info'                   => esc_js( __( 'Showing _START_ to _END_ of _TOTAL_ bookings', 'wcmp-afm' ) ),
                'info_empty'             => esc_js( __( 'Showing 0 to 0 of 0 bookings', 'wcmp-afm' ) ),
                'length_menu'            => esc_js( __( 'Number of rows _MENU_', 'wcmp-afm' ) ),
                'zero_records'           => esc_js( __( 'No matching bookings found', 'wcmp-afm' ) ),
                'next'                   => esc_js( __( 'Next', 'wcmp-afm' ) ),
                'previous'               => esc_js( __( 'Previous', 'wcmp-afm' ) ),
                'reload'                 => esc_js( __( 'Reload', 'wcmp-afm' ) ),
                'booking_filter_default' => esc_js( __( 'All Bookable Products', 'woocommerce-bookings' ) ),
                'booking_filter_options' => json_encode( $filters ),
            );
            wp_localize_script( 'afm-bookings-js', 'bookings_params', $bookings_params );
            wp_enqueue_script( 'afm-bookings-js' );

            afm()->template->get_template( 'products/booking/html-endpoint-bookings.php' );
        }
    }
}
