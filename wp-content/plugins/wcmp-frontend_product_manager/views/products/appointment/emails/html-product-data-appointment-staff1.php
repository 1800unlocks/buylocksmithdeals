<?php
/**
 * Booking General product tab template
 *
 * Used by WCMp_AFM_Booking_Integration->booking_general_product_tab_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/appointment/html-product-data-appointment-staff.php.
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

global $post, $wpdb;

$all_staff        = WCMp_AFM_Appointment_Integration::get_appointment_staff();
$product_staff    = $appointable_product->get_staff_ids( 'edit' );
$staff_base_costs = $appointable_product->get_staff_base_costs( 'edit' );
$staff_qtys       = $appointable_product->get_staff_qtys( 'edit' );
$loop             = 0;

?>
<div role="tabpanel" class="tab-pane fade" id="<?php esc_attr_e( $tab ); ?>">
    <div class="row-padding">
		<div id="appointments_staff" class="woocommerce_options_panel panel wc-metaboxes-wrapper">
			<div class="options_group" id="staff_options">
				<div class="form-group _wc_appointment_staff_label_field">
			        <label class="control-label col-sm-3 col-md-3" for="_wc_appointment_staff_label"><?php esc_html_e( 'Label', 'woocommerce-appointments' ); ?></label>
			        <div class="col-md-6 col-sm-9">
			            <input type="text" class="form-control" name="_wc_appointment_staff_label" id="_wc_appointment_staff_label" placeholder="<?php esc_html_e( 'Providers', 'woocommerce-appointments' ); ?>" value="<?php esc_attr_e( $appointable_product->get_staff_label( 'edit' ) ); ?>">
			            <span class="form-text"><?php esc_html_e( 'The label shown on the frontend if the staff is customer defined.', 'woocommerce-appointments' ); ?></span>
			        </div>
	   			</div>
	   			<div class="form-group">
	                <label class="control-label col-sm-3 col-md-3" for="_wc_appointment_staff_assignment">
	                    <?php esc_html_e( 'Selection', 'woocommerce-appointments' ); ?>        
	                </label>
	                <div class="col-md-6 col-sm-9">
	                    <select name="_wc_appointment_staff_assignment" id="_wc_appointment_staff_assignment" class="form-control">
	                        <option value="available" <?php selected( $appointable_product->get_staff_assignment( 'edit' ), 'available' ); ?>><?php esc_html_e( 'Customer selected', 'woocommerce-appointments' ); ?></option>
	                        <option value="non-available" <?php selected( $appointable_product->get_staff_assignment( 'edit' ), 'non-available' ); ?>><?php esc_html_e( 'Automatically assigned', 'woocommerce-appointments' ); ?></option>
	                        <option value="non-available-all" <?php selected( $appointable_product->get_staff_assignment( 'edit' ), 'non-available-all' ); ?>><?php esc_html_e( 'Automatically assigned (all staff together)', 'woocommerce-appointments' ); ?></option>
	                    </select>
	                    <span class="form-text"><?php esc_html_e( 'Customer selected staff allow customers to choose one from the appointment form.', 'woocommerce-appointments' ); ?></span>
	                </div>
	            </div>
	            <div class = "form-group _wc_appointment_staff_nopref_field">
					<label class="control-label col-sm-3 col-md-3" for="_wc_appointment_staff_nopref"><?php esc_html_e( 'No Preference?', 'woocommerce-appointments' ); ?></label>
			        <div class="col-md-6 col-sm-9">
			            <input type="checkbox" class="form-control" name="_wc_appointment_staff_nopref" id="_wc_appointment_staff_nopref" value="yes" <?php checked( $appointable_product->get_staff_nopref( 'edit' ), true ); ?>>
			            <span class="form-text"><?php esc_html_e( 'Check this box if you want to show No preference option.', 'woocommerce-appointments' ); ?></span>
			        </div>
		    	</div>
			</div>
		</div>
	</div>
</div>
