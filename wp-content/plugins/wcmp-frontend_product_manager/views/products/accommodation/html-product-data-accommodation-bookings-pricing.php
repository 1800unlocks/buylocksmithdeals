<?php
/**
 * Rates product tab template
 *
 * Used by WCMp_AFM_Accommodation_Integration->accommodation_booking_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/accommodation/html-product-data-accommodation-bookings-pricing.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/accommodation
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
        <div class="form-group-row"> 
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_accommodation_booking_base_cost"><?php echo __( 'Standard room rate', 'woocommerce-accommodation-bookings' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="0" step=".01" id="_wc_accommodation_booking_base_cost" name="_wc_accommodation_booking_base_cost" value="<?php esc_attr_e( get_post_meta( $id, '_wc_booking_base_cost', true ) ); ?>" class="form-control">
                    <span class="form-text"><?php esc_html_e( 'Standard cost for booking the room.', 'woocommerce-accommodation-bookings' ); ?></span>
                </div>
            </div> 
            <?php do_action( 'afm_accommodation_bookings_after_booking_base_cost', $id ); ?>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_accommodation_booking_display_cost"><?php echo __( 'Display cost', 'woocommerce-accommodation-bookings' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="0" step=".01" id="_wc_accommodation_booking_display_cost" name="_wc_accommodation_booking_display_cost" value="<?php esc_attr_e( $bookable_product->get_display_cost( 'edit' ) ); ?>" class="form-control">
                    <span class="form-text"><?php esc_html_e( 'The cost is displayed to the user on the frontend. Leave blank to have it calculated for you. If a booking has varying costs, this will be prefixed with the word "from:".', 'woocommerce-accommodation-bookings' ); ?></span>
                </div>
            </div>
            <?php do_action( 'woocommerce_accommodation_bookings_after_display_cost', $id ); ?>
        </div>
        <div class="form-group-row"> 
            <div class="form-group">
                <div class="col-md-12">
                    <div class="booking_range_pricing">
                        <table class="table table-outer-border">
                            <thead>
                                <tr>
                                    <th class="sort" width="1%">&nbsp;</th>
                                    <th><?php esc_html_e( 'Range type', 'woocommerce-accommodation-bookings' ); ?></th>
                                    <th><?php esc_html_e( 'Starting', 'woocommerce-accommodation-bookings' ); ?></th>
                                    <th><?php esc_html_e( 'Ending', 'woocommerce-accommodation-bookings' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_attr_e( 'Includes this date/night.', 'woocommerce-accommodation-bookings' ); ?>">[?]</a></th>
                                    <th><?php esc_html_e( 'Cost', 'woocommerce-accommodation-bookings' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_attr_e( 'Cost for this time period.', 'woocommerce-accommodation-bookings' ); ?>">[?]</a></th>
                                    <th class="remove" width="1%">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody id="pricing_rows">
                                <?php
                                $values = $bookable_product->get_pricing( 'edit' );
                                if ( ! empty( $values ) && is_array( $values ) ) {
                                    foreach ( $values as $index => $pricing ) {
                                        include( 'html-accommodation-booking-range-pricing.php' );
                                    }
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="9">
                                        <a href="#" class="btn btn-default insert" data-row="<?php
                                        ob_start();
                                        include( 'html-accommodation-booking-range-pricing.php' );
                                        $html = ob_get_clean();
                                        echo esc_attr( $html );
                                        ?>"><?php esc_html_e( 'Add Range', 'woocommerce-accommodation-bookings' ); ?></a>
                                    </th>
                                </tr>
                            </tfoot> 
                        </table>
                    </div>  
                </div>
            </div>
        </div>
        <?php do_action( 'afm_accommodation_bookings_after_bookings_pricing', $id ); ?>
    </div>
</div>