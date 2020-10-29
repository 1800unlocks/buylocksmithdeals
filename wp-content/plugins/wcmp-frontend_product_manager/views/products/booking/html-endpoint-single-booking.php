<?php
/**
 * Vendor dashboard Bookings->Booking single page template
 *
 * Used by WCMp_AFM_Bookings_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-endpoint-single-booking.php.
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

$bookable_order = $booking->get_order();
$order_id = is_callable( array( $bookable_order, 'get_id' ) ) ? $bookable_order->get_id() : $bookable_order->id;
$suborder = get_wcmp_suborders($order_id);
$order =$suborder[0];
$product_id = $booking->get_product_id( 'edit' );
$resource_id = $booking->get_resource_id( 'edit' );
$customer_id = $booking->get_customer_id( 'edit' );
$product = $booking->get_product( $product_id );
$resource = new WC_Product_Booking_Resource( $resource_id );
$customer = $booking->get_customer();
$statuses = array_unique( array_merge( get_wc_booking_statuses( 'user', true ), get_wc_booking_statuses( 'cancel', true ) ) );
?>
<div class="col-md-12">
    <div class="icon-header">
        <span><i class="wcmp-font ico-order-details-icon"></i></span>
        <h2><?php printf( __( 'Booking #%s details', 'woocommerce-bookings' ), esc_html( $order->get_id() ) ); ?></h2>
        <h3>
            <?php
            $order_id = $order ? absint( ( is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id ) ) : '';
            if ( $order ) {
                /* translators: 1: href to order id */
                printf( ' ' . __( 'Linked to order %s.', 'woocommerce-bookings' ), '<a href="' . wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders' ), $order->get_id() ) . '">#' . esc_html( $order->get_order_number() ) . '</a>' );
            }

            if ( $product && is_callable( array( $product, 'is_bookings_addon' ) ) && $product->is_bookings_addon() ) {
                /* translators: 1: bookings addon title */
                printf( ' ' . __( 'Booking type: %s', 'woocommerce-bookings' ), $product->bookings_addon_title() );
            }
            ?>
        </h3>
    </div>
    <div class="row">
        <div class="col-md-8 booking-details-wrapper">
            <div class="panel panel-default pannel-outer-heading mt-0">
                <div class="panel-heading">
                    <h3><?php esc_html_e( 'Booking details', 'woocommerce-bookings' ); ?></h3>
                </div>
                <div class="panel-body panel-content-padding form-horizontal" id="booking_details">
                    <table class="table vertical-th table-bordered booking-customer-details">
                        <tr>
                            <th><?php esc_html_e( 'Booking status:', 'wcmp-afm' ); ?></th>
                            <td><span class="status <?php esc_attr_e( $booking->get_status() ); ?>"><?php esc_html_e( $booking->get_status() ); ?></span></td>
                        </tr>
                        <?php if ( $order ) { ?>
                            <tr>
                                <th><?php esc_html_e( 'Order details:', 'wcmp-afm' ); ?></th>
                                <td><?php echo '<a href="' . wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders' ), $order_id ) . '">#' . $order->get_order_number() . '</a> &ndash; <span class="status ' . esc_attr( $order->get_status() ) . '">' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</span>'; ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <th><?php esc_html_e( 'Date created:', 'woocommerce-bookings' ); ?></th>
                            <td><?php esc_html_e( date_i18n( wc_date_format() . ' @' . wc_time_format(), $booking->get_date_created() ) ); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Booked Product:', 'woocommerce-bookings' ); ?></th>
                            <td><?php esc_html_e( $product->get_title() ); ?></td>
                        </tr>
                        <?php
                        if ( $resource_id ) {
                            ?>
                            <tr>
                                <th><?php esc_html_e( 'Resource:', 'wcmp-afm' ); ?></th>
                                <td><?php esc_html_e( $resource->post_title ); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        <?php
                        $person_counts = $booking->get_person_counts();
                        $person_types = $product ? $product->get_person_types() : array();

                        if ( count( $person_counts ) > 0 ) {
                            $persons_html = "";
                            foreach ( $person_counts as $person_id => $person_count ) {
                                $person_type = null;

                                try {
                                    $person_type = new WC_Product_Booking_Person_Type( $person_id );
                                } catch ( Exception $e ) {
                                    // This person type was deleted from the database.
                                    unset( $person_counts[$person_id] );
                                    continue;
                                }

                                if ( $person_type ) {
                                    $persons_html .= sprintf( "%s (%d), ", $person_type->get_name(), $person_count );
                                }
                            }

                            if ( $persons_html ) {
                                ?>
                                <tr>
                                    <th><?php esc_html_e( 'Person(s):', 'woocommerce-bookings' ); ?></th>
                                    <td><?php esc_html_e( trim( $persons_html, ', ' ) ); ?></td>
                                </tr>
                                <?php
                            }
                        }
                        $format = $booking->get_all_day( 'edit' ) ? 'Y-m-d' : 'Y-m-d H:i A';
                        ?>
                        <tr>
                            <th><?php esc_html_e( 'Start date:', 'woocommerce-bookings' ); ?></th>
                            <td><?php esc_html_e( date_i18n( wc_date_format() . ' @' . wc_time_format(), $booking->get_start( 'edit' ) ) ); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'End date:', 'woocommerce-bookings' ); ?></th>
                            <td><?php esc_html_e( date_i18n( wc_date_format() . ' @' . wc_time_format(), $booking->get_end( 'edit' ) ) ); ?></td>
                        </tr>
                        <?php
                        if ( $booking->get_all_day( 'edit' ) ) {
                            ?>
                            <tr>
                                <th><?php esc_html_e( 'All day booking:', 'woocommerce-bookings' ); ?></th>
                                <td><?php esc_html_e( 'yes' ); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                    <?php if ( current_vendor_can( 'update_booking_details' ) ) : ?>
                        <form id="wcmp-afm-booking-details" class="woocommerce form-horizontal" method="POST">
                            <div class="wcmp-action-container">
                                <select name="_booking_status" class="form-control inline-select pull-left">
                                    <?php
                                    foreach ( $statuses as $key => $value ) {
                                        echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $booking->get_status(), false ) . '>' . esc_html( $value ) . '</option>';
                                    }
                                    ?>
                                </select>
                                <input type="hidden" name="booking_id" value="<?php esc_attr_e( $booking->get_id() ); ?>"/>
                                <?php wp_nonce_field( 'booking_details', 'booking_details_nonce' ); ?>
                                <input type="submit" name="booking_details" class="btn btn-default button-primary" value="<?php esc_attr_e( 'Update' ); ?>" />
                            </div>
                        </form>
                    <?php else : ?>
                        <div class="wcmp-action-container">
                            <a href="<?php echo esc_url( wcmp_get_vendor_dashboard_endpoint_url( 'bookings' ) ); ?>" class="btn btn-default"><?php esc_html_e( 'Back', 'wcmp-afm' ); ?></>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div> 
        <div class="col-md-4">
            <div class="panel panel-default pannel-outer-heading mt-0">
                <div class="panel-heading">
                    <h3><?php esc_html_e( 'Customer details', 'woocommerce-bookings' ); ?></h3>
                </div>
                <div class="panel-body panel-content-padding form-horizontal" id="customer_details">
                    <table class="table vertical-th table-bordered booking-customer-details">
                        <?php
                        $booking_customer_id = $booking->get_customer_id();
                        $user = $booking_customer_id ? get_user_by( 'id', $booking_customer_id ) : false;

                        if ( $booking_customer_id && $user ) {
                            ?>
                            <tr>
                                <th><?php esc_html_e( 'Name:', 'woocommerce-bookings' ); ?></th>
                                <td><?php echo esc_html( $user->last_name && $user->first_name ? $user->first_name . ' ' . $user->last_name : '&mdash;'  ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Email:', 'woocommerce-bookings' ); ?></th>
                                <td><?php echo make_clickable( sanitize_email( $user->user_email ) ); ?></td>
                            </tr>
                            <?php
                            $has_data = true;
                        }

                        $booking_order_id = $booking->get_order_id();
                        $order = $booking_order_id ? wc_get_order( $booking_order_id ) : false;

                        if ( $booking_order_id && $order ) {
                            ?>
                            <tr>
                                <th><?php esc_html_e( 'Address:', 'woocommerce-bookings' ); ?></th>
                                <td><?php echo wp_kses( $order->get_formatted_billing_address() ? $order->get_formatted_billing_address() : __( 'No billing address set.', 'woocommerce-bookings' ), array( 'br' => array() ) ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Email:', 'woocommerce-bookings' ); ?></th>
                                <td><?php echo make_clickable( sanitize_email( is_callable( array( $order, 'get_billing_email' ) ) ? $order->get_billing_email() : $order->billing_email  ) ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Phone:', 'woocommerce-bookings' ); ?></th>
                                <td><?php echo esc_html( is_callable( array( $order, 'get_billing_phone' ) ) ? $order->get_billing_phone() : $order->billing_phone  ); ?></td>
                            </tr>
                            <tr class="view">
                                <th>&nbsp;</th>
                                <td><a class="button button-small" target="_blank" href="<?php echo esc_url( wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders' ), absint( $booking->get_order_id() ) ) ); ?>"><?php echo esc_html( 'View Order', 'woocommerce-bookings' ); ?></a></td>
                            </tr>
                            <?php
                            $has_data = true;
                        }

                        if ( ! $has_data ) {
                            ?>
                            <tr>
                                <td colspan="2"><?php esc_html_e( 'N/A', 'woocommerce-bookings' ); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>