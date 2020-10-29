<?php

/**
 * WCMp_AFM_Booking_Notification_Endpoint setup
 *
 * @package  WCMp_AFM/classes/endpoints
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Booking_Notification_Endpoint {

    /**
     * Output the form.
     *
     * @version  3.0.0
     */
    public function output() {
        $current_vendor_id = afm()->vendor_id;

        if ( ! $current_vendor_id || ! apply_filters( 'vendor_can_send_booking_notification', true, $current_vendor_id ) ) {
            ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <?php esc_html_e( 'You do not have permission to view this content. Please contact site administrator.', 'wcmp-afm' ); ?>
                </div>
            </div>
            <?php
            return;
        }

        $booking_products = WCMp_AFM_Booking_Integration::get_vendor_bookable_products( 'publish' );

        afm()->template->get_template( 'products/booking/html-endpoint-booking-notifications.php', array( 'booking_products' => $booking_products ) );
    }
}
