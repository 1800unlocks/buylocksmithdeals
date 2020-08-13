<?php
/**
 * Booking Cost product tab template
 *
 * Used by WCMp_AFM_Booking_Integration->booking_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-product-data-booking-pricing.php.
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
?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
        <div class="form-group-row"> 
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_booking_cost"><?php echo __( 'Base cost', 'woocommerce-bookings' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="0" step=".01" id="_wc_booking_cost" name="_wc_booking_cost" value="<?php esc_attr_e( $bookable_product->get_cost( 'edit' ) ); ?>" class="form-control">
                </div>
            </div> 
            <?php do_action( 'afm_bookings_after_booking_base_cost', $id ); ?>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_booking_block_cost"><?php echo __( 'Block cost', 'woocommerce-bookings' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="0" step=".01" id="_wc_booking_block_cost" name="_wc_booking_block_cost" value="<?php esc_attr_e( $bookable_product->get_block_cost( 'edit' ) ); ?>" class="form-control">
                </div>
            </div>
            <?php do_action( 'afm_bookings_after_booking_block_cost', $id ); ?>
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_display_cost"><?php echo __( 'Display cost', 'woocommerce-bookings' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
                <div class="col-md-6 col-sm-9">
                    <input type="number" min="0" step=".01" id="_wc_display_cost" name="_wc_display_cost" value="<?php esc_attr_e( $bookable_product->get_display_cost( 'edit' ) ); ?>" class="form-control">
                </div>
            </div>
            <?php do_action( 'afm_bookings_after_display_cost', $id ); ?>
        </div>
        <div class="form-group-row"> 
            <div class="form-group">
                <div class="col-md-12">
                    <div class="booking_range_pricing">
                        <table class="table table-outer-border">
                            <thead>
                                <tr>
                                    <th class="sort" width="1%">&nbsp;</th>
                                    <th><?php esc_html_e( 'Range type', 'woocommerce-bookings' ); ?></th>
                                    <th><?php esc_html_e( 'Range', 'woocommerce-bookings' ); ?></th>
                                    <th></th>
                                    <th></th>
                                    <th><?php esc_html_e( 'Base cost', 'woocommerce-bookings' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_attr_e( 'Enter a cost for this rule. Applied to the booking as a whole.', 'woocommerce-bookings' ); ?>">[?]</a></th>
                                    <th><?php esc_html_e( 'Block cost', 'woocommerce-bookings' ); ?>&nbsp;<a class="tips" data-tip="<?php esc_attr_e( 'Enter a cost for this rule. Applied to each booking block.', 'woocommerce-bookings' ); ?>">[?]</a></th>
                                    <th class="remove" width="1%">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody id="pricing_rows">
                                <?php
                                $values = $bookable_product->get_pricing( 'edit' );
                                if ( ! empty( $values ) && is_array( $values ) ) {
                                    foreach ( $values as $index => $pricing ) {
                                        include( 'html-booking-range-pricing.php' );

                                        /**
                                         * Fired just after pricing fields are rendered.
                                         *
                                         * @since 1.7.4
                                         *
                                         * @param array $pricing {
                                         * The pricing details for bookings
                                         *
                                         * @type string $type          The booking range type
                                         * @type string $from          The start value for the range
                                         * @type string $to            The end value for the range
                                         * @type string $modifier      The arithmetic modifier for block cost
                                         * @type string $cost          The booking block cost
                                         * @type string $base_modifier The arithmetic modifier for base cost
                                         * @type string $base_cost     The base cost
                                         * }
                                         */
                                        do_action( 'afm_bookings_pricing_fields', $pricing );
                                    }
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="9">
                                        <a href="#" class="btn btn-default insert" data-row="<?php
                                        ob_start();
                                        include( 'html-booking-range-pricing.php' );
                                        $html = ob_get_clean();
                                        echo esc_attr( $html );
                                        ?>"><?php esc_html_e( 'Add Range', 'woocommerce-bookings' ); ?></a>
                                        <span class="description"><?php esc_html_e( 'All matching rules will be applied to the booking.', 'woocommerce-bookings' ); ?></span>
                                    </th>
                                </tr>
                            </tfoot> 
                        </table>
                    </div>  
                </div>
            </div>
        </div>
        <?php do_action( 'afm_bookings_after_bookings_pricing', $id ); ?>
    </div>
</div>