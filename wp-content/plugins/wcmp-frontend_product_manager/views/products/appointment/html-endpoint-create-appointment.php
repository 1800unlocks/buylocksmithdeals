<?php
/**
 * Booking General product tab template
 *
 * Used by WCMp_AFM_Create_Appointment_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/appointment/html-endpoint-create-appointment.php.
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
?>


<div class="col-md-12 create-booking-wrapper">
    <?php do_action( 'before_wcmp_afm_create_booking_form' ); ?>
    <form id="wcmp-afm-create-appointment" class="woocommerce form-horizontal" method="POST" data-nonce="<?php echo esc_attr( wp_create_nonce( 'find-appointed-day-blocks' ) ); ?>">
        <?php do_action( 'wcmp_afm_create_appointment_form_start' ); ?>
        <div class="panel panel-default pannel-outer-heading mt-0">
            <div class="panel-heading">
                <label><?php esc_html_e( 'You can add a new appointment for a customer here. Created orders will be marked as pending payment.', 'woocommerce-appointments' ); ?></label>
            </div>
            <div class="panel-body panel-content-padding form-horizontal">
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3" for="customer_id"><?php esc_html_e( 'Customer', 'woocommerce-appointments' ); ?></label>
                    <div class=" col-md-6 col-sm-9">
                        <?php if ( version_compare( WC_VERSION, '3.0', '<' ) ) : ?>
                            <input type="hidden" name="customer_id" id="customer_id" class="wc-customer-search" data-placeholder="<?php esc_attr_e( 'Guest', 'woocommerce-appointments' ); ?>" data-allow_clear="true" />
                        <?php else : ?>
                            <select name="customer_id" id="customer_id" class="wc-customer-search form-control" data-placeholder="<?php esc_attr_e( 'Guest', 'woocommerce-appointments' ); ?>" data-allow_clear="true">
                            </select>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3" for="bookable_product_id"><?php esc_html_e( 'Product', 'woocommerce-appointments' ); ?></label>
                    <div class=" col-md-6 col-sm-9">
                        <select id="appointable_product_id" name="appointable_product_id" class="wc-product-search" style="width: 300px;" data-allow_clear="true" data-placeholder="<?php esc_html_e( 'Select an appointable product...', 'woocommerce-appointments' ); ?>"></select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3" for="create_order"><?php esc_html_e( 'Order', 'woocommerce-appointments' ); ?></label>

                    <div class=" col-md-6 col-sm-9 create-order-label-wrap">
                        <label>
                           <input type="radio" name="appointment_order" value="new" class="checkbox appointment-order-selector" checked="checked" />
								<?php esc_html_e( 'Create a new order', 'woocommerce-appointments' ); ?>
								<?php echo wc_help_tip( esc_html__( 'Please note - appointment won\'t be active until order is processed/completed.', 'woocommerce-appointments' ) ); ?>
                        </label>
                        <label>
								<input type="radio" name="appointment_order" value="existing" class="checkbox appointment-order-selector" />
								<?php esc_html_e( 'Assign to an existing order', 'woocommerce-appointments' ); ?>
						</label>
                        <div class="appointment-order-label-select">
                            <select name="appointment_order_id" id="appointment_order_id" class="wc-customer-search form-control" data-placeholder="<?php esc_html_e( 'N/A', 'woocommerce-appointments' ); ?>" data-allow_clear="true">
                        </div>
                    </div>
                </div>
                <?php do_action( 'afm_appointments_after_create_appointment_page' ); ?>
                <div class="form-group">
                        <input type="submit" name="create_appointment" class="btn btn-default button-primary" value="<?php esc_html_e( 'Next', 'woocommerce-appointments' ); ?>" />
                        <?php wp_nonce_field( 'add_appointment_notification' ); ?>
                </div>
            </div>
        </div>
        <?php do_action( 'wcmp_afm_create_appointment_form_end' ); ?>
    </form>
    <?php do_action( 'after_wcmp_afm_create_appointment_form' ); ?>
</div> 


