<?php
/**
 * WCMp_AFM_Rental_Calendar_Endpoint setup
 *
 * @package  WCMp_AFM/classes/endpoints
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Rental_Calendar_Endpoint {

    public function output() {
        global $WCMp;
        $current_vendor_id = afm()->vendor_id;
        $vendor = get_wcmp_vendor( $current_vendor_id );
        if ( ! $current_vendor_id || ! $vendor || ! apply_filters( 'vendor_can_access_rental_calendar', true, $current_vendor_id ) ) {
            ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <?php esc_html_e( 'You do not have permission to view this content. Please contact site administrator.', 'wcmp-afm' ); ?>
                </div>
            </div>
            <?php
            return;
        }
        
        $orders = $vendor->get_orders();

        $fullcalendar = array();

        if ( isset( $orders ) && ! empty( $orders ) ) {
            foreach ( $orders as $order_id ) {

                $order = wc_get_order( $order_id );
                if ( ! $order )
                    continue;

                foreach ( $order->get_items() as $item ) {

                    $item_meta_array = $item['item_meta_array'];

                    $order_item_id = $item->get_id();
                    $product_id = $item->get_product_id();
                    $_product = wc_get_product( $product_id );
                    if ( $_product->get_type() !== 'redq_rental' )
                        continue;

                    $order_item_details = $item->get_formatted_meta_data( '' );

                    $fullcalendar[$order_item_id]['post_status'] = $order->get_status();
                    $fullcalendar[$order_item_id]['title'] = esc_html( get_the_title( $product_id ) );
                    
                    $fullcalendar[$order_item_id]['link'] = esc_url( wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_edit_product_endpoint', 'vendor', 'general', 'edit-product' ), $product_id ) );
                    $fullcalendar[$order_item_id]['id'] = $order_id;

                    $fullcalendar[$order_item_id]['description'] = '<table cellspacing="0" class="redq-rental-display-meta"><tbody><tr><th>Order ID:</th><td>#' . $order_id . '</td></tr>';
                    
                    foreach ( $order_item_details as $order_item_key => $order_item_value ) {

                        if ( $order_item_value->key !== 'pickup_hidden_datetime' && $order_item_value->key !== 'return_hidden_datetime' && $order_item_value->key !== 'return_hidden_days' ) {
                            $fullcalendar[$order_item_id]['description'] .= '<tr><th>' . $order_item_value->key . '</th><td>' . $order_item_value->value . '</td></tr>';
                        }

                        if ( $order_item_value->key === 'pickup_hidden_datetime' ) {
                            $pickup_datetime = explode( '|', $order_item_value->value );
                            $fullcalendar[$order_item_id]['start'] = $pickup_datetime[0];
                            $fullcalendar[$order_item_id]['start_time'] = isset( $pickup_datetime[1] ) ? $pickup_datetime[1] : '';
                        }

                        if ( $order_item_value->key === 'return_hidden_datetime' ) {
                            $return_datetime = explode( '|', $order_item_value->value );
                            $fullcalendar[$order_item_id]['return_date'] = $return_datetime[0];
                            $fullcalendar[$order_item_id]['return_time'] = isset( $return_datetime[1] ) ? $return_datetime[1] : '';
                        }

                        if ( $order_item_value->key === 'return_hidden_days' ) {
                            $start_day = $fullcalendar[$order_item_id]['start'];
                            $end_day = new DateTime( $start_day . ' + ' . $order_item_value->value . ' day' );
                            $fullcalendar[$order_item_id]['end'] = $end_day->format( 'Y-m-d' );
                        }

                        $fullcalendar[$order_item_id]['url'] = esc_url( wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders' ), $order->get_id() ) );
                    }

                    $order_total = $order->get_formatted_order_total();

                    $fullcalendar[$order_item_id]['description'] .= '<tr><th>' . esc_html__( 'Order Total', 'wcmp-afm' ) . '</th><td>' . $order_total . '</td>';
                    $fullcalendar[$order_item_id]['description'] .= '</tbody></table>';
                }
            }
        }

        $calendar_data = array();

        if ( ! empty( $fullcalendar ) ) {

            foreach ( $fullcalendar as $key => $value ) {
                if ( array_key_exists( 'start', $value ) && array_key_exists( 'end', $value ) ) {
                    $calendar_data[$key] = $value;
                }

                if ( array_key_exists( 'start', $value ) && ! array_key_exists( 'end', $value ) ) {
                    $start_info = isset( $value['start_time'] ) && ! empty( $value['start_time'] ) ? $value['start'] . 'T' . $value['start_time'] : $value['start'];
                    $return_info = isset( $value['return_time'] ) && ! empty( $value['return_time'] ) ? $value['start'] . 'T' . $value['return_time'] : $value['start'];

                    $value['start'] = $start_info;
                    $value['end'] = $return_info;
                    $calendar_data[$key] = $value;
                }

                if ( array_key_exists( 'end', $value ) && ! array_key_exists( 'start', $value ) ) {
                    $start_info = isset( $value['start_time'] ) && ! empty( $value['start_time'] ) ? $value['end'] . 'T' . $value['start_time'] : $value['end'];
                    $return_info = isset( $value['return_time'] ) && ! empty( $value['return_time'] ) ? $value['end'] . 'T' . $value['return_time'] : $value['end'];

                    $value['start'] = $start_info;
                    $value['end'] = $return_info;
                    $calendar_data[$key] = $value;
                }
            }

        }

        wp_localize_script( 'afm-rental-calendar-js', 'rental_calendar_params', $calendar_data );
        wp_enqueue_script( 'afm-rental-calendar-js');
        ob_start();
        ?>
        <div class="wrap">
            <div id="redq-rental-calendar"></div>
        </div>

        <div id="eventContent" class="popup-modal white-popup-block mfp-hide">
            <div class="white-popup">
                <h2><a id="eventProduct" href=""></a></h2>
                <strong><?php esc_html_e( 'Start:', 'wcmp-afm' ) ?></strong> <span id="startTime"></span><br>
                <strong><?php esc_html_e( 'End:', 'wcmp-afm' ) ?></strong> <span id="endTime"></span><br><br>
                <div id="eventInfo"></div>
                <p><strong><a id="eventLink" href=""><?php esc_html_e( 'View Order', 'wcmp-afm' ) ?></a></strong></p>
            </div>
        </div><?php
    }

}
