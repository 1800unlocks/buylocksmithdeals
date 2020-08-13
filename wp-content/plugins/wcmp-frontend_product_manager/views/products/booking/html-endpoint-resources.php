<?php
/**
 * Vendor dashboard Bookings->Resources menu template
 *
 * Used by WCMp_AFM_Resources_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-endpoint-resources.php.
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

do_action( 'before_wcmp_vendor_dashboard_resources_table' );
?>
<div class="col-md-12">
    <div class="panel panel-default panel-pading mt-0">
        <table id="resources_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th class="main"><?php esc_html_e( 'Title', 'woocommerce-bookings' ); ?></th>
                    <th><?php esc_html_e( 'Date', 'woocommerce-bookings' ); ?></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <?php if ( current_vendor_can( 'add_bookable_resource' ) ) : ?>
            <div class="wcmp-action-container">
                <a href="<?php echo wcmp_get_vendor_dashboard_endpoint_url( 'resources', 'draft-resource' ); ?>" class="btn btn-default"><?php esc_html_e( 'Add Resource', 'woocommerce-bookings' ); ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
do_action( 'after_wcmp_vendor_dashboard_quote_list_table' );
