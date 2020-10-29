<?php
/**
 * Vendor dashboard Bookings->Booking single page template
 *
 * Used by WCMp_AFM_Bookings_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/appointment/html-endpoint-single-appointment.php.
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

$appointment_order = $appointment->get_order();
$order_id = is_callable( array( $appointment_order, 'get_id' ) ) ? $appointment_order->get_id() : $appointment_order->id;
$suborder = get_wcmp_suborders($order_id);
$order =$suborder[0];
$product_id = $appointment->get_product_id( 'edit' );
$customer_id = $appointment->get_customer_id( 'edit' );
$product = $appointment->get_product( $product_id );
$customer = $appointment->get_customer();
$statuses = array_unique( array_merge( get_wc_appointment_statuses( 'user', true ), get_wc_appointment_statuses( 'cancel', true ) ) );
?>
<div class="col-md-12">
    <div class="icon-header">
        <span><i class="wcmp-font ico-order-details-icon"></i></span>
        <h2><?php printf( __( 'Appointment #%s details', 'woocommerce-appointments' ), esc_html( $appointment->get_id() ) ); ?></h2>
        <h3>
            <?php
            $order_id = $order ? absint( ( is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id ) ) : '';
            if ( $order ) {
                /* translators: 1: href to order id */
                printf( ' ' . __( 'Linked to order %s.', 'woocommerce-appointments' ), '<a href="' . wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders' ), $order_id ) . '">#' . esc_html( $order->get_order_number() ) . '</a>' );
            }

            if ( $product && is_callable( array( $product, 'is_appointments_addon' ) ) && $product->is_appointments_addon() ) {
                /* translators: 1: appointments addon title */
                printf( ' ' . __( 'Appointment type: %s', 'woocommerce-appointments' ), $product->appointments_addon_title() );
            }
            ?>
        </h3>
    </div>
    <div class="row">
        <div class="col-md-8 appointment-details-wrapper">
            <div class="panel panel-default pannel-outer-heading mt-0">
                <div class="panel-heading">
                    <h3><?php esc_html_e( 'Appointment details', 'woocommerce-appointments' ); ?></h3>
                </div>
                <div class="panel-body panel-content-padding form-horizontal" id="appointment_details">
                    <table class="table vertical-th table-bordered appointment-customer-details">
                        <tr>
                            <th><?php esc_html_e( 'Appointment status:', 'wcmp-afm' ); ?></th>
                            <td><span class="status <?php esc_attr_e( $appointment->get_status() ); ?>"><?php esc_html_e( $appointment->get_status() ); ?></span></td>
                        </tr>
                        <?php if ( $order ) { ?>
                            <tr>
                                <th><?php esc_html_e( 'Order details:', 'wcmp-afm' ); ?></th>
                                <td><?php echo '<a href="' . wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders' ), $order_id ) . '">#' . $order->get_order_number() . '</a> &ndash; <span class="status ' . esc_attr( $order->get_status() ) . '">' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</span>'; ?></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <th><?php esc_html_e( 'Date created:', 'woocommerce-appointments' ); ?></th>
                            <td><?php esc_html_e( date_i18n( wc_date_format() . ' @' . wc_time_format(), $appointment->get_date_created() ) ); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Booked Product:', 'woocommerce-appointments' ); ?></th>
                            <td><?php esc_html_e( $product->get_title() ); ?></td>
                        </tr>
                        <?php
                        $format = $appointment->get_all_day( 'edit' ) ? 'Y-m-d' : 'Y-m-d H:i A';
                        ?>
                        <tr>
                            <th><?php esc_html_e( 'Start date:', 'woocommerce-appointments' ); ?></th>
                            <td><?php esc_html_e( date_i18n( wc_date_format() . ' @' . wc_time_format(), $appointment->get_start( 'edit' ) ) ); ?></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'End date:', 'woocommerce-appointments' ); ?></th>
                            <td><?php esc_html_e( date_i18n( wc_date_format() . ' @' . wc_time_format(), $appointment->get_end( 'edit' ) ) ); ?></td>
                        </tr>
                        <?php
                        if ( $appointment->get_all_day( 'edit' ) ) {
                            ?>
                            <tr>
                                <th><?php esc_html_e( 'All day appointment:', 'woocommerce-appointments' ); ?></th>
                                <td><?php esc_html_e( 'yes' ); ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                    <?php if ( current_vendor_can( 'update_appointment_details' ) ) : ?>
                        <form id="wcmp-afm-appointment-details" class="woocommerce form-horizontal" method="POST">
                            <div class="wcmp-action-container">
                                <select name="_appointment_status" class="form-control inline-select pull-left">
                                    <?php
                                    foreach ( $statuses as $key => $value ) {
                                        echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $appointment->get_status(), false ) . '>' . esc_html( $value ) . '</option>';
                                    }
                                    ?>
                                </select>
                                <input type="hidden" name="appointment_id" value="<?php esc_attr_e( $appointment->get_id() ); ?>"/>
                                <?php wp_nonce_field( 'appointment_details', 'appointment_details_nonce' ); ?>
                                <input type="submit" name="appointment_details" class="btn btn-default button-primary" value="<?php esc_attr_e( 'Update' ); ?>" />
                            </div>
                        </form>
                    <?php else : ?>
                        <div class="wcmp-action-container">
                            <a href="<?php echo esc_url( wcmp_get_vendor_dashboard_endpoint_url( 'appointments' ) ); ?>" class="btn btn-default"><?php esc_html_e( 'Back', 'wcmp-afm' ); ?></>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div> 
        <div class="col-md-4">
            <div class="panel panel-default pannel-outer-heading mt-0">
                <div class="panel-heading">
                    <h3><?php esc_html_e( 'Customer details', 'woocommerce-appointments' ); ?></h3>
                </div>
                <div class="panel-body panel-content-padding form-horizontal" id="customer_details">
                    <table class="table vertical-th table-bordered appointment-customer-details">
                        <?php
                        $appointment_customer_id = $appointment->get_customer_id();
                        $user = $appointment_customer_id ? get_user_by( 'id', $appointment_customer_id ) : false;

                        if ( $appointment_customer_id && $user ) {
                            ?>
                            <tr>
                                <th><?php esc_html_e( 'Name:', 'woocommerce-appointments' ); ?></th>
                                <td><?php echo esc_html( $user->last_name && $user->first_name ? $user->first_name . ' ' . $user->last_name : '&mdash;'  ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Email:', 'woocommerce-appointments' ); ?></th>
                                <td><?php echo make_clickable( sanitize_email( $user->user_email ) ); ?></td>
                            </tr>
                            <?php
                            $has_data = true;
                        }

                        $appointment_order_id = $order->get_id();
                        $order = $appointment_order_id ? wc_get_order( $appointment_order_id ) : false;

                        if ( $appointment_order_id && $order ) {
                            ?>
                            <tr>
                                <th><?php esc_html_e( 'Address:', 'woocommerce-appointments' ); ?></th>
                                <td><?php echo wp_kses( $order->get_formatted_billing_address() ? $order->get_formatted_billing_address() : __( 'No billing address set.', 'woocommerce-appointments' ), array( 'br' => array() ) ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Email:', 'woocommerce-appointments' ); ?></th>
                                <td><?php echo make_clickable( sanitize_email( is_callable( array( $order, 'get_billing_email' ) ) ? $order->get_billing_email() : $order->billing_email  ) ); ?></td>
                            </tr>
                            <tr>
                                <th><?php esc_html_e( 'Phone:', 'woocommerce-appointments' ); ?></th>
                                <td><?php echo esc_html( is_callable( array( $order, 'get_billing_phone' ) ) ? $order->get_billing_phone() : $order->billing_phone  ); ?></td>
                            </tr>
                            <tr class="view">
                                <th>&nbsp;</th>
                                <td><a class="button button-small" target="_blank" href="<?php echo esc_url( wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders' ), absint( $order->get_id() ) ) ); ?>"><?php echo esc_html( 'View Order', 'woocommerce-appointments' ); ?></a></td>
                            </tr>
                            <?php
                            $has_data = true;
                        }

                        if ( ! $has_data ) {
                            ?>
                            <tr>
                                <td colspan="2"><?php esc_html_e( 'N/A', 'woocommerce-appointments' ); ?></td>
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