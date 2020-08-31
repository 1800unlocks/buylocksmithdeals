<?php
/**
 * WCMp_AFM_Create_Booking_Endpoint setup
 *
 * @package  WCMp_AFM/classes/endpoints
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Create_Booking_Endpoint {

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
        $step = 1;

        $current_vendor_id = afm()->vendor_id;

        if ( ! $current_vendor_id || ! apply_filters( 'vendor_can_create_booking', true, $current_vendor_id ) ) {
            ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <?php esc_html_e( 'You do not have permission to view this content. Please contact site administrator.', 'wcmp-afm' ); ?>
                </div>
            </div>
            <?php
            return;
        }

        if ( ! empty( $_POST['create_booking'] ) ) {
            $bookable_product_id = absint( $_POST['bookable_product_id'] );
            if ( $bookable_product_id ) {
                $transient_key = 'create_booking_' . $bookable_product_id . '_by' . $current_vendor_id;
                $booking_data = get_transient( $transient_key );
                delete_transient( $transient_key );
                if ( $booking_data ) {
                    $step ++;
                }
            }
        }
        $create_booking_params = array(
            'ajax_url'               => admin_url( 'admin-ajax.php' ),
            'search_customers_nonce' => wp_create_nonce( 'search-customers' ),
        );

        wp_localize_script( 'afm-create-booking-js', 'create_booking_params', $create_booking_params );
        wp_enqueue_script( 'afm-create-booking-js' );

        switch ( $step ) {
            case 1:
                afm()->template->get_template( 'products/booking/html-endpoint-create-booking.php' );
                break;
            case 2:
                // overriding this template may brake create booking.
                // it is adviced to do this with caution or better not to do it at all.
                afm()->template->get_template( 'products/booking/html-endpoint-create-booking-2.php', $booking_data );
                break;
        }
    }

    /**
     * Create order.
     *
     * @param  float $total
     * @param  int $customer_id
     * @return int
     */
    public function create_order( $total, $customer_id ) {
        if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
            $order = wc_create_order( array(
                'customer_id' => absint( $customer_id ),
                ) );
            $order_id = $order->id;
            $order->set_total( $total );
            update_post_meta( $order->id, '_created_via', 'bookings' );
        } else {
            $order = new WC_Order();
            $order->set_customer_id( $customer_id );
            $order->set_total( $total );
            $order->set_created_via( 'bookings' );
            $order_id = $order->save();
        }

        do_action( 'woocommerce_new_booking_order', $order_id );

        return $order_id;
    }

    /**
     * Output any errors
     */
    public function show_errors() {
        foreach ( $this->errors as $error ) {
            wc_add_notice( esc_html( $error ), 'error' );
        }
    }

}
