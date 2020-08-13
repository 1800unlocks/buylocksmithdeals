<?php
/**
 * Booking General product tab template
 *
 * Used by WCMp_AFM_Booking_Integration->booking_general_product_tab_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/appointment/html-product-data-appointment-availability.php.
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
		<div id="appointments_availability" class="panel woocommerce_options_panel wc-metaboxes-wrapper">
			<div class="form-group">
		        <label class="control-label col-sm-3 col-md-3" for="_wc_appointment_availability_span">
		            <?php esc_html_e( 'Availability Check', 'woocommerce-appointments' ); ?>        
		        </label>
		        <div class="col-md-6 col-sm-9">
		            <select name="_wc_appointment_availability_span" id="_wc_appointment_availability_span" class="form-control">
		                <option value="available" <?php selected( $appointable_product->get_availability_span( 'edit' ), 'available' ); ?>><?php esc_html_e( 'All slots in availability range', 'woocommerce-appointments' ); ?></option>
		                <option value="non-available" <?php selected( $appointable_product->get_availability_span( 'edit' ), 'non-available' ); ?>><?php esc_html_e( 'The starting slot only', 'woocommerce-appointments' ); ?></option>
		            </select>
		            <span class="form-text"><?php esc_html_e( 'By default availability per each slot in range is checked. You can also check availability for starting slot only.', 'woocommerce-appointments' ); ?></span>
		    	</div>
		    </div>
		    <div class = "form-group">
				<label class="control-label col-sm-3 col-md-3" for="_wc_appointment_availability_autoselect"><?php esc_html_e( 'Auto-select?', 'woocommerce-appointments' ); ?></label>
		        <div class="col-md-6 col-sm-9">
		            <input type="checkbox" class="form-control" name="_wc_appointment_availability_autoselect" id="_wc_appointment_availability_autoselect" value="yes" <?php checked( $appointable_product->get_availability_autoselect( 'edit' ), true ); ?>>
		            <span class="form-text"><?php esc_html_e( 'Check this box if you want to auto-select first available day and/or time.', 'woocommerce-appointments' ); ?></span>
		        </div>
			</div>
			<div class = "form-group">
				<label class="control-label col-sm-3 col-md-3" for="_wc_appointment_has_restricted_days"><?php esc_html_e( 'Restrict start days?', 'woocommerce-appointments' ); ?></label>
		        <div class="col-md-6 col-sm-9">
		            <input type="checkbox" class="form-control" name="_wc_appointment_has_restricted_days" id="_wc_appointment_has_restricted_days" value="yes" <?php checked( $appointable_product->has_restricted_days( 'edit' ), true ); ?>>
		            <span class="form-text"><?php esc_html_e( 'Restrict appointments so that they can only start on certain days of the week. Does not affect availability.', 'woocommerce-appointments' ); ?></span>
		        </div>
			</div>
			<div class = "form-group">
				<div class="appointment-day-restriction">
					<table class="widefat">
						<tbody>
							<tr>
								<td>&nbsp;</td>
								<?php
								$start_of_week = absint( get_option( 'start_of_week', 1 ) );
								for ( $i = $start_of_week; $i < $start_of_week + 7; $i ++ ) {
									$day_time   = strtotime( "next sunday +{$i} day" );
									$day_number = date_i18n( _x( 'w', 'date format', 'woocommerce-appointments' ), $day_time ); #day of week number (zero to six)
									$day_name   = date_i18n( _x( 'l', 'date format', 'woocommerce-appointments' ), $day_time ); #day of week name (Mon to Sun)
									?>
									<td class="col-md-2">
										<label class="checkbox" for="_wc_appointment_restricted_days[<?php echo esc_html( $day_number ); ?>]"><?php echo esc_html( $day_name ); ?>&nbsp;</label>
										<input type="checkbox" class="checkbox" name="_wc_appointment_restricted_days[<?php echo esc_html( $day_number ); ?>]" id="_wc_appointment_restricted_days[<?php echo esc_html( $day_number ); ?>]" value="<?php echo esc_html( $day_number ); ?>" <?php checked( $restricted_days[ $day_number ], $day_number ); ?>>
									</td>
								<?php
								}
								?>
								<td>&nbsp;</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="options_group">
				<div class="toolbar">
					<h3><?php esc_html_e( 'Custom Availability', 'woocommerce-appointments' ); ?></h3>
				</div>
			<?php
			$product_availabilities = WC_Data_Store::load( 'appointments-availability' )->get_all(
				array(
					array(
						'key'     => 'kind',
						'compare' => '=',
						'value'   => 'availability#product',
					),
					array(
						'key'     => 'kind_id',
						'compare' => '=',
						'value'   => $appointable_product->get_id(),
					),
				)
			);
			$show_title             = false;
			?>
			<div class="form-group-row"> 
            <div class="form-group">
                <div class="col-md-12">
					<div class="table_grid availability_table_grid">
						<table class="table table-outer-border">
							<thead>
								<tr>
									<th class="sort" width="1%">&nbsp;</th>
									<th class="range_type"><?php esc_html_e( 'Type', 'woocommerce-appointments' ); ?></th>
									<th class="range_name"><?php esc_html_e( 'Range', 'woocommerce-appointments' ); ?></th>
									<th class="range_name2"></th>
									<th class="range_capacity"><?php esc_html_e( 'Quantity', 'woocommerce-appointments' ); ?><?php echo wc_help_tip( esc_html__( 'The maximum number of appointments per slot. Overrides product quantity.', 'woocommerce-appointments' ) ); // WPCS: XSS ok. ?></th>
									<th class="range_priority"><?php esc_html_e( 'Priority', 'woocommerce-appointments' ); ?><?php echo wc_help_tip( esc_html__( 'Rules with lower priority numbers will override rules with a higher priority (e.g. 9 overrides 10 ). By using priority numbers you can execute rules in different orders for all three levels: Global, Product and Staff rules.', 'woocommerce-appointments' ) ); // WPCS: XSS ok. ?></th>
									<th class="range_appointable"><?php esc_html_e( 'Available', 'woocommerce-appointments' ); ?><?php echo wc_help_tip( esc_html__( 'If not available, users won\'t be able to choose slots in this range for their appointment.', 'woocommerce-appointments' ) ); // WPCS: XSS ok. ?></th>
									<?php do_action( 'woocommerce_appointments_extra_availability_fields_header' ); ?>
									<th class="remove" width="1%">&nbsp;</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<th colspan="9">
										<a
											href="#"
											class="btn btn-default add_grid_row"
											<?php
											ob_start();
											require 'html-product-availability-field.php';
											$html = ob_get_clean();
											echo 'data-row="' . esc_attr( $html ) . '"';
											?>
										>
											<?php esc_html_e( 'Add Rule', 'woocommerce-appointments' ); ?>
										</a>
										<span class="description"><?php esc_html_e( get_wc_appointment_rules_explanation() ); ?></span>
									</th>
								</tr>
							</tfoot>
							<tbody id="availability_rows">
								<?php
								if ( ! empty( $product_availabilities ) && is_array( $product_availabilities ) ) {
									foreach ( $product_availabilities as $availability ) {
										if ( $availability->has_past() ) {
											continue;
										}
										require 'html-product-availability-field.php';
									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
			<input type="hidden" name="wc_appointment_availability_deleted" value="" class="wc-appointment-availability-deleted" />
		</div>
	</div>
</div>
