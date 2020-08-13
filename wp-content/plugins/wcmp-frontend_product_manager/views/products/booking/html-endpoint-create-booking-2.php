<?php
/**
 * Vendor dashboard Bookings->Add Bookings menu template Part 2
 *
 * Used by WCMp_AFM_Create_Booking_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-endpoint-create-booking-2.php.
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
<div class="col-md-12 create-booking-part-two-wrapper">
    <?php do_action( 'before_wcmp_afm_create_booking_part_two_form' ); ?>
    <form id="wcmp-afm-create-booking-part-two" class="woocommerce form-horizontal" method="POST" data-nonce="<?php echo esc_attr( wp_create_nonce( 'find-booked-day-blocks' ) ); ?>">
        <?php do_action( 'wcmp_afm_create_booking_part_two_form_start' ); ?>
        <div class="panel panel-default pannel-outer-heading">
            <div class="panel-body panel-content-padding form-horizontal">
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3"><?php esc_html_e( 'Booking Data', 'woocommerce-bookings' ); ?></label>
                    <div class="wc-bookings-booking-form col-md-6 col-sm-9">
                        <?php $booking_form->output(); ?>
                        <div class="wc-bookings-booking-cost" style="display:none"></div>
                    </div>
                </div>
                <div class="row">
                    <label class="col-md-3"></label>
                    <div class="col-md-6">
                        <input type="submit" name="create_booking_2" class="btn btn-default button-primary" value="<?php esc_html_e( 'Add Booking', 'woocommerce-bookings' ); ?>" />
                        <input type="hidden" name="customer_id" value="<?php echo esc_attr( $customer_id ); ?>" />
                        <input type="hidden" name="bookable_product_id" value="<?php echo esc_attr( $bookable_product_id ); ?>" />
                        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $bookable_product_id ); ?>" />
                        <input type="hidden" name="booking_order" value="<?php echo esc_attr( $booking_order ); ?>" />
                        <?php wp_nonce_field( 'create_booking_notification' ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php do_action( 'wcmp_afm_create_booking_part_two_form_end' ); ?>
    </form>
    <?php do_action( 'after_wcmp_afm_create_booking_part_two_form' ); ?>
</div> 
