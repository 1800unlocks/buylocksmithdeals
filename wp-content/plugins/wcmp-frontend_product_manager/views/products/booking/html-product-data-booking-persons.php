<?php
/**
 * Booking Persons product tab template
 *
 * Used by WCMp_AFM_Booking_Integration->booking_additional_tabs_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-product-data-booking-persons.php.
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

$person_types = $bookable_product->get_person_types( 'edit' );
?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_wc_booking_min_persons_group"><?php esc_html_e( 'Min persons', 'woocommerce-bookings' ); ?></label>
            <div class="col-md-6 col-sm-9">
                <input type="number" min="0" step="1" id="_wc_booking_min_persons_group" name="_wc_booking_min_persons_group" value="<?php esc_attr_e( $bookable_product->get_min_persons( 'edit' ) ); ?>" class="form-control">
                <span class="form-text"><?php esc_html_e( 'The minimum number of persons per booking.', 'woocommerce-bookings' ); ?></span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_wc_booking_max_persons_group"><?php esc_html_e( 'Max persons', 'woocommerce-bookings' ); ?></label>
            <div class="col-md-6 col-sm-9">
                <input type="number" min="0" step="1" id="_wc_booking_max_persons_group" name="_wc_booking_max_persons_group" value="<?php esc_attr_e( $bookable_product->get_max_persons( 'edit' ) ); ?>" class="form-control">
                <span class="form-text"><?php esc_html_e( 'The maximum number of persons per booking.', 'woocommerce-bookings' ); ?></span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_wc_booking_person_cost_multiplier"><?php esc_html_e( 'Multiply all costs by person count', 'woocommerce-bookings' ); ?></label>
            <div class="col-md-6 col-sm-9">
                <input type="checkbox" class="form-control" id="_wc_booking_person_cost_multiplier" name="_wc_booking_person_cost_multiplier" value="yes" <?php checked( $bookable_product->get_has_person_cost_multiplier( 'edit' ), true ); ?>>
                <span class="form-text"><?php esc_html_e( 'Enable this to multiply the entire cost of the booking (block and base costs) by the person count.', 'woocommerce-bookings' ); ?></span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_wc_booking_person_qty_multiplier"><?php esc_html_e( 'Count persons as bookings', 'woocommerce-bookings' ); ?></label>
            <div class="col-md-6 col-sm-9">
                <input type="checkbox" class="form-control" id="_wc_booking_person_qty_multiplier" name="_wc_booking_person_qty_multiplier" value="yes" <?php checked( $bookable_product->get_has_person_qty_multiplier( 'edit' ), true  ); ?>>
                <span class="form-text"><?php esc_html_e( 'Enable this to count each person as a booking until the max bookings per block (in availability) is reached.', 'woocommerce-bookings' ); ?></span>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_wc_booking_has_person_types"><?php esc_html_e( 'Enable person types', 'woocommerce-bookings' ); ?></label>
            <div class="col-md-6 col-sm-9">
                <input type="checkbox" class="form-control" id="_wc_booking_has_person_types" name="_wc_booking_has_person_types" value="yes" <?php checked( $bookable_product->get_has_person_types( 'edit' ), true ); ?>>
                <span class="form-text"><?php esc_html_e( 'Person types allow you to offer different booking costs for different types of individuals, for example, adults and children.', 'woocommerce-bookings' ); ?></span>
            </div>
        </div>
        <div class="has-person-types">
            <div class="row">
                <div class="col-md-12">
                    <h4 class="persons-types-headings pull-left margin-0"><?php esc_html_e( 'Person types', 'woocommerce-bookings' ); ?></h4>
                    <div class="toolbar pull-right">
                        <span class="expand-close">
                            <a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
                        </span>
                    </div>
                    <div class="clearfix"></div>
                    <hr>
                </div>
            </div>
            <div class="row" id="persons-types">
                <div class="col-md-12">
                    <?php if ( sizeof( $person_types ) === 0 ) : ?>
                        <div id="persons-message" class="inline notice woocommerce-message mt-15">
                            <?php echo wp_kses_post( __( 'Person types allow you to offer different booking costs for different types of individuals, for example, adults and children.', 'woocommerce-bookings' ) ); ?>
                        </div>
                    <?php endif; ?>
                    <div class="booking_persons wc-metaboxes booking-persons-wrapper">  
                        <?php
                        if ( $person_types ) {
                            $loop = 0;

                            foreach ( $person_types as $person_type ) {
                                include( 'html-product-booking-persons.php' );
                                $loop ++;
                            }
                        }
                        ?>
                    </div>

                </div>
            </div> 
            <div class="button-group">
                <button type="button" class="btn btn-default add_person button-primary"><?php esc_html_e( 'Add Person Type', 'woocommerce-bookings' ); ?></button>
                <div class="toolbar pull-right">
                    <span class="expand-close">
                        <a href="#" class="expand_all"><?php esc_html_e( 'Expand', 'woocommerce' ); ?></a> / <a href="#" class="close_all"><?php esc_html_e( 'Close', 'woocommerce' ); ?></a>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>