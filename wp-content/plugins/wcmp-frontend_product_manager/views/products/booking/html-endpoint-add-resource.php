<?php
/**
 * Vendor dashboard Bookings->Resources->Add Resource template
 *
 * Used by WCMp_AFM_Resources_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-endpoint-add-resource.php.
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

$title = $resource ? $resource->post_title : '';
$qty = $resource ? max( $resource->get_qty( 'edit' ), 1 ) : 1;
$is_update = $resource ? true : false;
?>
<div class="col-md-12 add-resource-wrapper">
    <?php do_action( 'before_wcmp_afm_add_resource_form' ); ?>
    <form id="wcmp-afm-add-resource" class="woocommerce form-horizontal" method="POST">
        <?php do_action( 'wcmp_afm_add_resource_form_start' ); ?>
        <div class="panel panel-default pannel-outer-heading">
            <div class="panel-heading">
                <label><?php esc_html_e( 'Resource details', 'woocommerce-bookings' ); ?></label>
            </div>
            <div class="panel-body panel-content-padding form-horizontal" id="bookings_availability">
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3" for="post_title"><strong><?php esc_html_e( 'Resource Title', 'wcmp-afm' ); ?></strong></label>
                    <div class=" col-md-6 col-sm-9">
                        <input type="text" name="post_title" id="post_title" value="<?php esc_attr_e( $title ); ?>" placeholder="<?php esc_html_e( 'Enter title here' ); ?>" class="form-control" size="30" />
                        <input type="hidden" name="resource_id" id="resource_id" value="<?php esc_attr_e( $id ); ?>" />
                    </div>
                </div>
                <hr/>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3" for="_wc_booking_qty"><?php esc_html_e( 'Available Quantity', 'woocommerce-bookings' ); ?></label>
                    <div class="col-md-6 col-sm-9">
                        <input type="number" min="" step="1" name="_wc_booking_qty" id="_wc_booking_qty" value="<?php esc_attr_e( $qty ); ?>" class="form-control"/>
                        <span class="form-text"><?php esc_html_e( 'The quantity of this resource available at any given time.', 'woocommerce-bookings' ); ?></span>
                    </div>
                </div>
                <div class="form-group-row"> 
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="booking_availability">
                                <table class="table table-outer-border">
                                    <thead>
                                        <tr>
                                            <th class="sort" width="1%">&nbsp;</th>
                                            <th><?php esc_html_e( 'Range type', 'woocommerce-bookings' ); ?></th>
                                            <th><?php esc_html_e( 'Range', 'woocommerce-bookings' ); ?></th>
                                            <th></th>
                                            <th></th>
                                            <th><?php esc_html_e( 'Bookable', 'woocommerce-bookings' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_attr_e( 'If not bookable, users won\'t be able to choose this block for their booking.', 'woocommerce-bookings' ); ?>">[?]</a></th>
                                            <th><?php esc_html_e( 'Priority', 'woocommerce-bookings' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_attr_e( get_wc_booking_priority_explanation() ); ?>">[?]</a></th>
                                            <th class="remove" width="1%">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th colspan="9">
                                                <a href="#" class="btn btn-default insert" data-row="<?php
                                                ob_start();
                                                include( 'html-booking-availability.php' );
                                                $html = ob_get_clean();
                                                echo esc_attr( $html );
                                                ?>"><?php esc_html_e( 'Add Range', 'woocommerce-bookings' ); ?></a>
                                                <span class="description"><?php esc_html_e( get_wc_booking_rules_explanation() ); ?></span>
                                            </th>
                                        </tr>
                                    </tfoot>
                                    <tbody id="availability_rows">
                                        <?php
                                        $values = $resource ? $resource->get_availability( 'edit' ) : null;
                                        if ( ! empty( $values ) && is_array( $values ) ) {
                                            foreach ( $values as $availability ) {
                                                include( 'html-booking-availability.php' );
                                            }
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>  
                        </div>
                    </div>
                </div>
                <?php do_action( 'afm_bookings_after_add_resource_page' ); ?>
                <?php if ( current_vendor_can( 'add_bookable_resource' ) ) : ?>
                    <?php
                    $action_text = $is_update ? __( 'Update' ) : __( 'Publish' );
                    ?>
                    <div class="wcmp-action-container">
                        <input type="submit" name="add_resource" class="btn btn-default button-primary" value="<?php esc_attr_e( $action_text ); ?>" />
                    </div>
                <?php else : ?>
                    <div class="wcmp-action-container">
                        <a href="<?php echo esc_url( wcmp_get_vendor_dashboard_endpoint_url( 'resources' ) ); ?>" class="btn btn-default"><?php esc_html_e( 'Back', 'wcmp-afm' ); ?></>
                    </div>
                <?php endif; ?>
                <?php wp_nonce_field( 'bookable_resource_details', 'bookable_resource_details_nonce' ); ?>
            </div>
        </div>
        <?php do_action( 'wcmp_afm_add_resource_form_end' ); ?>
    </form>
    <?php do_action( 'after_wcmp_afm_add_resource_form' ); ?>
</div> 