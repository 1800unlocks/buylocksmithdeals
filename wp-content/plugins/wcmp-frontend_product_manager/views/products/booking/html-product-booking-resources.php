<?php
/**
 * Booking Resources template
* Used by WCMp_AFM_Booking_Integration->booking_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-product-booking-resources.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      WC Marketplace
 * @package     WCMp_AFM/views/products/booking
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wcmp-metabox-wrapper woocommerce_booking_resource wc-metabox closed" rel="<?php esc_attr_e( absint( $resource->get_id() ) ); ?>">
    <div class="wcmp-metabox-title bookable-resource-title" data-toggle="collapse" data-target="#resource_<?php esc_attr_e( absint( $resource->get_id() ) ); ?>"  aria-expanded="false" aria-controls="collapseExample">
        <div class="resource-select-group">
            <span class="sortable-icon"></span>
            <strong>#<?php esc_html_e( $resource->get_id() ); ?> &mdash; <span class="resource_title"><?php esc_html_e( $resource->get_name() ); ?></span></strong>
            <input type="hidden" name="resource_id[]" value="<?php esc_attr_e( $resource->get_id() ); ?>" />
            <input type="hidden" name="resource_title[]" value="<?php esc_attr_e( '#' . $resource->get_id() . ' - ' . $resource->get_name() ); ?>" />
            <input type="hidden" class="resource_menu_order" name="resource_menu_order[]" value="<?php echo $loop; ?>" />
        </div>
        <div class="wcmp-metabox-action resource-action">
            <i class="wcmp-font ico-up-arrow-icon"></i>
            <?php if( current_vendor_can( 'manage_resources' ) ) : ?>
                <a href="<?php echo wcmp_get_vendor_dashboard_endpoint_url( 'resources', absint( $resource->get_id() ) ); ?>" target="_blank" class="edit_resource"><?php esc_html_e( 'Edit resource', 'woocommerce-bookings' ); ?> &rarr;</a>
            <?php endif; ?>
            <a href="#" class="remove_row delete remove_resource"><?php esc_html_e( 'Remove', 'woocommerce' ); ?></a>
        </div>
    </div>
    <div class="wcmp-metabox-content booking_resource_data collapse" id="resource_<?php esc_attr_e( absint( $resource->get_id() ) ); ?>">
        <table cellpadding="0" cellspacing="0" class="table">
            <tbody>
                <tr>
                    <td>
                        <label><?php esc_html_e( 'Base Cost', 'woocommerce-bookings' ); ?>:</label>
                        <input type="number" class="form-control" name="resource_cost[]" value="<?php
                        if ( ! empty( $resource_base_cost ) ) {
                            esc_attr_e( $resource_base_cost );
                        }
                        ?>" placeholder="0.00" step="0.01" />
                        <?php do_action( 'afm_bookings_after_resource_cost', $resource->get_id(), $id ); ?>
                    </td>
                    <td>
                        <label><?php esc_html_e( 'Block Cost', 'woocommerce-bookings' ); ?>:</label>
                        <input type="number" class="form-control" name="resource_block_cost[]" value="<?php
                        if ( ! empty( $resource_block_cost ) ) {
                            esc_attr_e( $resource_block_cost );
                        }
                        ?>" placeholder="0.00" step="0.01" />
                        <?php do_action( 'afm_bookings_after_resource_block_cost', $resource->get_id(), $id ); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>