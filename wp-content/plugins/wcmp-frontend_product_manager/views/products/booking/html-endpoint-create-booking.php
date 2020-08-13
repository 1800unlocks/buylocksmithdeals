<?php
/**
 * Vendor dashboard Bookings->Add Bookings menu template Part 1
 *
 * Used by WCMp_AFM_Create_Booking_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-endpoint-create-booking.php.
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
<div class="col-md-12 create-booking-wrapper">
    <?php do_action( 'before_wcmp_afm_create_booking_form' ); ?>
    <form id="wcmp-afm-create-booking" class="woocommerce form-horizontal" method="POST" data-nonce="<?php echo esc_attr( wp_create_nonce( 'find-booked-day-blocks' ) ); ?>">
        <?php do_action( 'wcmp_afm_create_booking_form_start' ); ?>
        <div class="panel panel-default pannel-outer-heading mt-0">
            <div class="panel-heading">
                <label><?php esc_html_e( 'You can create a new booking for a customer here. This form will create a booking for the user, and optionally an associated order. Created orders will be marked as pending payment.', 'woocommerce-bookings' ); ?></label>
            </div>
            <div class="panel-body panel-content-padding form-horizontal">
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3" for="customer_id"><?php esc_html_e( 'Customer', 'woocommerce-bookings' ); ?></label>
                    <div class=" col-md-6 col-sm-9">
                        <?php if ( version_compare( WC_VERSION, '3.0', '<' ) ) : ?>
                            <input type="hidden" name="customer_id" id="customer_id" class="wc-customer-search" data-placeholder="<?php esc_attr_e( 'Guest', 'woocommerce-bookings' ); ?>" data-allow_clear="true" />
                        <?php else : ?>
                            <select name="customer_id" id="customer_id" class="wc-customer-search form-control" data-placeholder="<?php esc_attr_e( 'Guest', 'woocommerce-bookings' ); ?>" data-allow_clear="true">
                            </select>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3" for="bookable_product_id"><?php esc_html_e( 'Bookable Product', 'woocommerce-bookings' ); ?></label>
                    <div class=" col-md-6 col-sm-9">
                        <select id="bookable_product_id" name="bookable_product_id" class="chosen_select form-control">
                            <option value=""><?php esc_html_e( 'Select a bookable product...', 'woocommerce-bookings' ); ?></option>
                            <?php foreach ( WCMp_AFM_Booking_Integration::get_vendor_bookable_products( 'publish' ) as $product ) : ?>
                                <option value="<?php echo $product->get_id(); ?>"><?php echo sprintf( '%s (#%s)', $product->get_name(), $product->get_id() ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3" for="create_order"><?php esc_html_e( 'Create Order', 'woocommerce-bookings' ); ?></label>
                    <div class=" col-md-6 col-sm-9 create-order-label-wrap">
                        <label>
                            <input type="radio" name="booking_order" value="new"/>
                            <?php esc_html_e( 'Create a new corresponding order for this new booking. Please note - the booking will not be active until the order is processed/completed.', 'woocommerce-bookings' ); ?>
                        </label>
                        <label>
                            <input type="radio" name="booking_order" value="existing"/>
                            <?php esc_html_e( 'Assign this booking to an existing order with this ID:', 'woocommerce-bookings' ); ?>
                            <?php if ( class_exists( 'WC_Seq_Order_Number_Pro' ) ) : ?>
                                <input type="text" name="booking_order_id" value="" class="text form-control inline-input" size="15" />
                            <?php else : ?>
                                <input type="number" name="booking_order_id" value="" class="text form-control inline-input" size="10" />
                            <?php endif; ?>
                        </label>
                        <label>
                            <input type="radio" name="booking_order" value=""checked="checked" />
                            <?php esc_html_e( 'Don\'t create an order for this booking.', 'woocommerce-bookings' ); ?>
                        </label>
                    </div>
                </div>
                <?php do_action( 'afm_bookings_after_create_booking_page' ); ?>
                <div class="row">
                    <label class="col-md-3"></label>
                    <div class="col-md-6">
                        <input type="submit" name="create_booking" class="btn btn-default button-primary" value="<?php esc_html_e( 'Next', 'woocommerce-bookings' ); ?>" />
                    </div>
                </div>
                <?php wp_nonce_field( 'create_booking_notification' ); ?>
            </div>
        </div>
        <?php do_action( 'wcmp_afm_create_booking_form_end' ); ?>
    </form>
    <?php do_action( 'after_wcmp_afm_create_booking_form' ); ?>
</div> 
