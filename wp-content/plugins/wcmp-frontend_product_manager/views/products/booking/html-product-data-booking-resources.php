<?php
/**
 * Booking Resources product tab template
 *
 * Used by WCMp_AFM_Booking_Integration->booking_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-product-data-booking-resources.php.
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

$all_resources = WCMp_AFM_Booking_Integration::get_booking_resources();
$product_resources = $bookable_product->get_resource_ids( 'edit' );
$resource_base_costs = $bookable_product->get_resource_base_costs( 'edit' );
$resource_block_costs = $bookable_product->get_resource_block_costs( 'edit' );
?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_wc_booking_resource_label"><?php esc_html_e( 'Label', 'woocommerce-bookings' ); ?></label>
            <div class="col-md-6 col-sm-9">
                <input type="text" id="_wc_booking_resource_label" name="_wc_booking_resource_label" value="<?php esc_attr_e( $bookable_product->get_resource_label( 'edit' ) ); ?>" placeholder="<?php esc_attr_e( 'Type', 'woocommerce-bookings' ); ?>" class="form-control">
                <span class="form-text"><?php esc_html_e( 'The label shown on the frontend if the resource is customer defined.', 'woocommerce-bookings' ); ?></span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_wc_booking_resources_assignment"><?php esc_html_e( 'Resources are...', 'woocommerce-bookings' ); ?></label>
            <div class="col-md-6 col-sm-9">
                <select name="_wc_booking_resources_assignment" id="_wc_booking_resources_assignment" class="form-control regular-select">
                    <option value="customer" <?php selected( $bookable_product->get_resources_assignment( 'edit' ), 'customer' ); ?>><?php esc_html_e( 'Customer selected', 'woocommerce-bookings' ); ?></option>
                    <option value="automatic" <?php selected( $bookable_product->get_resources_assignment( 'edit' ), 'automatic' ); ?>><?php esc_html_e( 'Automatically assigned', 'woocommerce-bookings' ); ?></option>
                </select>
                <span class="form-text"><?php esc_html_e( 'Customer selected resources allow customers to choose one from the booking form.', 'woocommerce-bookings' ); ?></span>
            </div>
        </div>
        <div class="has-resource">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="bookable-resource-headings pull-left margin-0"><?php esc_html_e( 'Resources', 'woocommerce-bookings' ); ?></h4>
                    <div class="toolbar pull-right">
                        <span class="expand-close">
                            <a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
                        </span>
                    </div>
                    <div class="clearfix"></div>
                    <hr>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <?php if ( sizeof( $product_resources ) === 0 ) : ?>
                        <div id="resources-message" class="inline notice woocommerce-info mt-15">
                            <?php echo wp_kses_post( __( 'Resources are used if you have multiple bookable items, e.g. room types, instructors or ticket types. Availability for resources is global across all bookable products.', 'woocommerce-bookings' ) ); ?>
                        </div>
                    <?php endif; ?>
                    <div class="woocommerce_bookable_resources wc-metaboxes bookable-resources-wrapper">  
                        <?php
                        if ( $product_resources ) {
                            $loop = 0;

                            foreach ( $product_resources as $resource_id ) {
                                $resource = new WC_Product_Booking_Resource( $resource_id );
                                $resource_base_cost = isset( $resource_base_costs[$resource_id] ) ? $resource_base_costs[$resource_id] : '';
                                $resource_block_cost = isset( $resource_block_costs[$resource_id] ) ? $resource_block_costs[$resource_id] : '';

                                include( 'html-product-booking-resources.php' );
                                $loop ++;
                            }
                        }
                        ?>
                    </div>

                </div>
            </div> 
            <div class="button-group">
                <?php if ( current_vendor_can( 'manage_resources' ) ) : ?>
                    <a href="<?php echo esc_url( wcmp_get_vendor_dashboard_endpoint_url( 'resources' ) ); ?>"><?php esc_html_e( 'Manage Resource', 'woocommerce-bookings' ); ?></a>
                <?php endif; ?>
                <?php if ( current_vendor_can( 'manage_resources' ) || sizeof( $all_resources ) > 0 ) : ?>
                    <div class="pull-right">
                        <select name="add_resource_id" class="add_resource_id form-control inline-select">
                            <?php if ( current_vendor_can( 'add_bookable_resource' ) ) : ?>
                                <option value=""><?php esc_html_e( 'New resource', 'woocommerce-bookings' ); ?></option>
                            <?php endif; ?>
                            <?php
                            if ( $all_resources ) {
                                foreach ( $all_resources as $resource ) {
                                    if ( in_array( $resource->ID, $product_resources ) ) {
                                        continue; // ignore resources that's already on the product
                                    }
                                    echo '<option value="' . esc_attr( $resource->ID ) . '">#' . absint( $resource->ID ) . ' - ' . esc_html( $resource->post_title ) . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <button type="button" class="btn btn-default add_resource button-primary"><?php esc_html_e( 'Add/link Resource', 'woocommerce-bookings' ); ?></button>
                    </div>
                <?php endif; ?> 
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>