<?php
/**
 * Booking General product tab template
 *
  * Used by WCMp_AFM_Create_Appointment_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/appointment/html-endpoint-create-appointment-2.php.
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
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
// if ( $product->get_qty() > 1 && $product->get_qty_max() > 1 ) {
// 	woocommerce_quantity_input(
// 		array(
// 			'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_qty_min(), $product ),
// 			'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_qty_max(), $product ),
// 			'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( $_POST['quantity'] ) : 1,
// 		)
// 	);
// }
?>
<div class="col-md-12 create-appointment-part-two-wrapper">
    <?php do_action( 'before_wcmp_afm_create_booking_part_two_form' ); ?>
    <form class="wc-appointments-appointment-form-wrap cart" method="POST"">
        <?php do_action( 'wcmp_afm_create_appointment_part_two_form_start' ); ?>
        <div class="panel panel-default pannel-outer-heading">
            <div class="panel-body panel-content-padding form-horizontal">
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3"><?php esc_html_e( 'Appointment Data', 'woocommerce-appointments' ); ?></label>
                    <div class="wc-appointments-appointment-hook wc-appointments-appointment-hook-before"><?php do_action( 'woocommerce_before_appointment_form_output', 'before', $appointment_form->product ); ?></div>
					<?php $appointment_form->output(); ?>
					<div class="wc-appointments-appointment-hook wc-appointments-appointment-hook-after"><?php do_action( 'woocommerce_after_appointment_form_output', 'after', $appointment_form->product ); ?></div>
					<div class="wc-appointments-appointment-cost" style="display:none"></div>
                </div>
                <div class="row">
                    <label class="col-md-3"></label>
                    <div class="col-md-6">
                        <input type="submit" name="add_appointment_2" class="btn btn-default button-primary" value="<?php esc_html_e( 'Add New Appointment', 'woocommerce-appointments' ); ?>" />
                        <input type="hidden" name="customer_id" value="<?php echo esc_attr( $customer_id ); ?>" />
						<input type="hidden" name="appointable_product_id" value="<?php echo esc_attr( $appointable_product_id ); ?>" />
						<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $appointable_product_id ); ?>" />
						<input type="hidden" name="appointment_order" value="<?php echo esc_attr( $appointment_order ); ?>" />
						<?php wp_nonce_field( 'add_appointment_notification' ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php do_action( 'wcmp_afm_create_booking_part_two_form_end' ); ?>
    </form>
    <?php do_action( 'after_wcmp_afm_create_booking_part_two_form' ); ?>
</div> 
