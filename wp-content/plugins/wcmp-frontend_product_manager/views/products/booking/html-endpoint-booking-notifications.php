<?php
/**
 * Vendor dashboard Bookings->Send Notification menu template
 *
 * Used by WCMp_AFM_Booking_Notification_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/booking/html-endpoint-booking-notifications.php.
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
<div class="col-md-12 booking-notification-wrapper">
    <?php do_action( 'before_wcmp_afm_booking_notification_form' ); ?>
    <form id="wcmp-afm-booking-notification" class="woocommerce form-horizontal" method="POST">
        <?php do_action( 'wcmp_afm_booking_notification_form_start' ); ?>
        <div class="panel panel-default pannel-outer-heading mt-0">
            <div class="panel-heading">
                <label><?php _e( sprintf( __( 'You may send an email notification to all customers who have a %1$sfuture%2$s booking for a particular product. This will use the default template specified under %3$sWooCommerce > Settings > Emails%4$s.', 'woocommerce-bookings' ), '<strong>', '</strong>', '<span>', '</span>' ) ); ?></label>
            </div>
            <div class="panel-body panel-content-padding form-horizontal">
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3" for="notification_product_id"><?php esc_html_e( 'Booking Product', 'woocommerce-bookings' ); ?></label>
                    <div class=" col-md-6 col-sm-9">
                        <select id="notification_product_id" name="notification_product_id" class="form-control">
							<option value=""><?php esc_html_e( 'Select a booking product...', 'woocommerce-bookings' ); ?></option>
							<?php foreach ( $booking_products as $product ) : ?>
								<option value="<?php echo $product->get_id(); ?>"><?php echo $product->get_title(); ?></option>
							<?php endforeach; ?>
						</select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3" for="notification_subject"><?php esc_html_e( 'Subject', 'woocommerce-bookings' ); ?></label>
                    <div class=" col-md-6 col-sm-9">
                        <input type="text" placeholder="<?php esc_attr_e( 'Email subject', 'woocommerce-bookings' ); ?>" name="notification_subject" id="notification_subject" class="form-control" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3" for="notification_message"><?php esc_html_e( 'Message', 'woocommerce-bookings' ); ?></label>
                    <div class=" col-md-6 col-sm-9">
                        <textarea id="notification_message" name="notification_message" class="form-control code" placeholder="<?php esc_attr_e( 'The message you wish to send', 'woocommerce-bookings' ); ?>"></textarea>
						<span class="description form-text"><?php esc_html_e( 'The following tags can be inserted in your message/subject and will be replaced dynamically' , 'woocommerce-bookings' ); ?>: <code>{booking_id} {product_title} {order_date} {order_number} {customer_name} {customer_first_name} {customer_last_name}</code></span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-md-3 col-sm-3" for="notification_ics"><?php esc_html_e( 'Attachment', 'woocommerce-bookings' ); ?></label>
                    <div class=" col-md-6 col-sm-9">
                        <label><input type="checkbox" name="notification_ics" id="notification_ics" class="form-control mt-0"/> <?php _e( 'Attach <code>.ics</code> file', 'woocommerce-bookings' ); ?></label>
                    </div>
                </div>
                <?php do_action( 'afm_bookings_after_booking_notification_page' ); ?>
                <div class="row">
                    <label class="col-md-3"></label>
                    <div class="col-md-6">
                        <input type="submit" name="send" class="btn btn-default button-primary" value="<?php esc_attr_e( 'Send Notification', 'woocommerce-bookings' ); ?>" />
						<?php wp_nonce_field( 'send_booking_notification', 'send_booking_notification_nonce' ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php do_action( 'wcmp_afm_booking_notification_form_end' ); ?>
    </form>
    <?php do_action( 'after_wcmp_afm_booking_notification_form' ); ?>
</div> 
