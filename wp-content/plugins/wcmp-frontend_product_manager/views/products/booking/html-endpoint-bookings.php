<?php
/**
 * Vendor dashboard Bookings->All Bookings menu template
 *
 * Used by WCMp_AFM_Bookings_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-endpoint-bookings.php.
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

global $WCMp;
do_action( 'before_wcmp_vendor_dashboard_bookings_table' );
?>
<div class="col-md-12">
    <div class="panel panel-default panel-pading mt-0">
        <?php
        $statuses = array_unique( array_merge( array( 'all' => __( 'All', 'wcmp-afm' ) ), get_wc_booking_statuses( 'user', true ), get_wc_booking_statuses( 'cancel', true ) ) );
        $current_status = ! empty( $_GET['post_status'] ) ? wc_clean( $_GET['post_status'] ) : 'all';
        echo '<ul class="booking_status by_status nav nav-pills category-filter-nav">';
        foreach ( $statuses as $key => $label ) {
            if ( $key == 'all' ) {
                $count_pros = count( WCMp_AFM_Booking_Integration::get_vendor_booking_array( array( 'post_status' => array( 'complete', 'paid', 'confirmed', 'pending-confirmation', 'unpaid', 'cancelled' ) ) ) );
            } else {
                $count_pros = count( WCMp_AFM_Booking_Integration::get_vendor_booking_array( array( 'post_status' => $key ) ) );
            }
            if ( $count_pros ) {
                echo '<li><a href="' . add_query_arg( array( 'post_status' => sanitize_title( $key ) ), wcmp_get_vendor_dashboard_endpoint_url( 'bookings' ) ) . '" class="' . ( $current_status == $key ? 'current' : '' ) . '">' . $label . ' ( ' . $count_pros . ' ) </a></li>';
            }
        }
        echo '</ul><br/>';
        ?>
        <table id="bookings_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?php _e( 'ID', 'woocommerce-bookings' ); ?></th>
                    <th><?php _e( 'Booked Product', 'woocommerce-bookings' ); ?></th>
                    <th><?php _e( '# of Persons', 'woocommerce-bookings' ); ?></th>
                    <th><?php _e( 'Booked By', 'woocommerce-bookings' ); ?></th>
                    <th><?php _e( 'Order', 'woocommerce-bookings' ); ?></th>
                    <th><?php _e( 'Start Date', 'woocommerce-bookings' ); ?></th>
                    <th><?php _e( 'End Date', 'woocommerce-bookings' ); ?></th>
                    <th><?php _e( 'Actions', 'woocommerce-bookings' ); ?></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <?php if ( current_vendor_can( 'add_bookings' ) ) : ?>
            <div class="wcmp-action-container">
                <a href="<?php echo wcmp_get_vendor_dashboard_endpoint_url( 'create-booking' ); ?>" class="btn btn-default"><?php esc_html_e( 'Add Booking', 'woocommerce-bookings' ); ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
do_action( 'after_wcmp_vendor_dashboard_bookings_table' );
