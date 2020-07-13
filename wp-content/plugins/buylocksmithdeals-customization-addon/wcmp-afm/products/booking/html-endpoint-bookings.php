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
wp_dequeue_script( 'afm-bookings-js' );
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
                'empty_table'            => esc_js( __( 'No bookings found!', WCMp_AFM_TEXT_DOMAIN ) ),
                'processing'             => esc_js( __( 'Processing...', WCMp_AFM_TEXT_DOMAIN ) ),
                'info'                   => esc_js( __( 'Showing _START_ to _END_ of _TOTAL_ bookings', WCMp_AFM_TEXT_DOMAIN ) ),
                'info_empty'             => esc_js( __( 'Showing 0 to 0 of 0 bookings', WCMp_AFM_TEXT_DOMAIN ) ),
                'length_menu'            => esc_js( __( 'Number of rows _MENU_', WCMp_AFM_TEXT_DOMAIN ) ),
                'zero_records'           => esc_js( __( 'No matching bookings found', WCMp_AFM_TEXT_DOMAIN ) ),
                'next'                   => esc_js( __( 'Next', WCMp_AFM_TEXT_DOMAIN ) ),
                'previous'               => esc_js( __( 'Previous', WCMp_AFM_TEXT_DOMAIN ) ),
                'reload'                 => esc_js( __( 'Reload', WCMp_AFM_TEXT_DOMAIN ) ),
                'booking_filter_default' => esc_js( __( 'All Bookable Products', 'woocommerce-bookings' ) ),
                'booking_filter_options' => json_encode( $filters ),
            );
            wp_localize_script( 'afm-blsd-bookings-js', 'bookings_params', $bookings_params );
wp_enqueue_script( 'afm-blsd-bookings-js' );
do_action( 'before_wcmp_vendor_dashboard_bookings_table' );
?>
<div class="col-md-12">
    <div class="panel panel-default panel-pading mt-0">
        <?php
        $statuses = array_unique( array_merge( array( 'all' => __( 'All', WCMp_AFM_TEXT_DOMAIN ) ), get_wc_booking_statuses( 'user', true ), get_wc_booking_statuses( 'cancel', true ) ) );
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
                    <!-- <th><?php // _e( '# of Persons', 'woocommerce-bookings' ); ?></th> -->
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
