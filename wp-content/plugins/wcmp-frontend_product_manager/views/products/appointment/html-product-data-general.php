<?php
/**
 * Booking General product tab template
 *
 * Used by WCMp_AFM_Booking_Integration->booking_general_product_tab_content()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/appointment/html-product-data-general.php.
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

$duration = $appointable_product->get_duration( 'edit' );
$duration_unit = $appointable_product->get_duration_unit( 'edit' );
if ( '' == $duration_unit ) {
	$duration_unit = 'hour';
}

$interval_s = $appointable_product->get_interval( 'edit' );
if ( '' == $interval_s ) {
	$interval = $duration;
} else {
	$interval = max( absint( $interval_s ), 1 );
}
$interval_unit = $appointable_product->get_interval_unit( 'edit' );
if ( '' == $interval_unit ) {
	$interval_unit = $duration_unit;
} elseif ( 'day' == $interval_unit ) {
	$interval_unit = 'hour';
}

$padding_duration      = absint( $appointable_product->get_padding_duration( 'edit' ) );
$padding_duration_unit = $appointable_product->get_padding_duration_unit( 'edit' );
if ( '' == $padding_duration_unit ) {
	$padding_duration_unit = 'minute';
}
$min_date      = absint( $appointable_product->get_min_date( 'edit' ) );
$min_date_unit = $appointable_product->get_min_date_unit( 'edit' );
if ( '' == $min_date_unit ) {
	$min_date_unit = 'month';
}

$max_date = $appointable_product->get_max_date( 'edit' );
if ( '' == $max_date ) {
	$max_date = 12;
}
$max_date      = max( absint( $max_date ), 1 );
$max_date_unit = $appointable_product->get_max_date_unit( 'edit' );
if ( '' == $max_date_unit ) {
	$max_date_unit = 'month';
}

$cancel_limit      = max( absint( $appointable_product->get_cancel_limit( 'edit' ) ), 1 );
$cancel_limit_unit = $appointable_product->get_cancel_limit_unit( 'edit' );
if ( '' == $cancel_limit_unit ) {
	$cancel_limit_unit = 'day';
}

$cal_color_val = $appointable_product->get_cal_color( 'edit' );
if ( '' == $cal_color_val ) {
	$cal_color_val = '#0073aa'; // default color
}
?>

<div class = "form-group-row show_if_appointment">
	<div class = "form-group">
		<label class="control-label col-sm-3 col-md-3" for="_wc_appointment_has_price_label"><?php esc_html_e( 'Label instead of price?', 'woocommerce-appointments' ); ?></label>
        <div class="col-md-6 col-sm-9">
            <input type="checkbox" class="form-control" name="_wc_appointment_has_price_label" id="_wc_appointment_has_price_label" value="yes" <?php checked( $appointable_product->get_has_price_label( 'edit' ), true ); ?>>
            <span class="form-text"><?php esc_html_e( 'Check this box if the appointment should display text label instead of fixed price amount.', 'woocommerce-appointments' ); ?></span>
        </div>
    </div>
    <div class="form-group _wc_appointment_price_label_field" >
        <label class="control-label col-sm-3 col-md-3" for="_wc_appointment_price_label"><?php esc_html_e( 'Price Label', 'woocommerce-appointments' ); ?></label>
        <div class="col-md-6 col-sm-9">
            <input type="text" class="form-control" name="_wc_appointment_price_label" id="_wc_appointment_price_label" placeholder="<?php esc_html_e( 'Price Varies', 'woocommerce-appointments' ); ?>" value="<?php esc_attr_e( $appointable_product->get_price_label( 'edit' ) ); ?>">
            <span class="form-text"><?php esc_html_e( 'Show this label instead of fixed price amount.', 'woocommerce-appointments' ); ?></span>
        </div>
    </div>  
    <div class = "form-group">
		<label class="control-label col-sm-3 col-md-3" for="_wc_appointment_has_pricing"><?php esc_html_e( 'Custom pricing rules?', 'woocommerce-appointments' ); ?></label>
        <div class="col-md-6 col-sm-9">
            <input type="checkbox" class="form-control" name="_wc_appointment_has_pricing" id="_wc_appointment_has_pricing" value="yes" <?php checked( $appointable_product->get_has_pricing( 'edit' ), true ); ?>>
            <span class="form-text"><?php esc_html_e( 'Check this box if the appointment has custom pricing rules.', 'woocommerce-appointments' ); ?></span>
        </div>
    </div>
    <?php do_action( 'afm_appointments_after_display_cost', get_the_ID() ); ?>
    <!-- <div class = "form-group"> -->
		<div class="form-group-row"> 
            <div class="form-group">
                <div class="col-md-12">
                    <div id="appointments_pricing" class="appointment_range_pricing">
                        <table class="table table-outer-border">
                            <thead>
							<thead>
									<tr>
										<th class="sort" width="1%">&nbsp;</th>
										<th class="range_type"><?php esc_html_e( 'Type', 'woocommerce-appointments' ); ?></th>
										<th class="range_name"><?php esc_html_e( 'Range', 'woocommerce-appointments' ); ?></th>
										<th class="range_name2"></th>
										<th class="range_cost"><?php esc_html_e( 'Base cost', 'woocommerce-appointments' ); ?><?php echo wc_help_tip( esc_html__( 'Applied to the appointment as a whole. Must be inside range rules to be applied.', 'woocommerce-appointments' ) ); // WPCS: XSS ok. ?></th>
										<th class="range_cost"><?php esc_html_e( 'Slot cost', 'woocommerce-appointments' ); ?><?php echo wc_help_tip( esc_html__( 'Applied to each appointment slot separately. When appointment lasts for 2 days or more, this cost applies to each day in range separately.', 'woocommerce-appointments' ) ); // WPCS: XSS ok. ?></th>
										<th class="remove" width="1%">&nbsp;</th>
									</tr>
								</thead>
								<tfoot>
									<tr>
										<th colspan="7">
											<a
												href="#"
												class="btn btn-default add_grid_row"
												<?php
												ob_start();
												require 'html-product-pricing-fields.php';
												$html = ob_get_clean();
												echo 'data-row="' . esc_attr( $html ) . '"';
												?>
											>
												<?php esc_html_e( 'Add Rule', 'woocommerce-appointments' ); ?>
											</a>
											<span class="description"><?php esc_html_e( 'All matching rules will be applied to the appointment.', 'woocommerce-appointments' ); ?></span>
										</th>
									</tr>
								</tfoot>
								<tbody id="pricing_rows">
									<?php
									$values = $appointable_product->get_pricing( 'edit' );
									if ( ! empty( $values ) && is_array( $values ) ) {
										foreach ( $values as $pricing ) {
											require 'html-product-pricing-fields.php';
											do_action( 'woocommerce_appointments_pricing_fields', $pricing );
										}
									}
									?>
								</tbody>
                        </table>
                    </div>  
                </div>
            </div>
        </div>
        <?php do_action( 'afm_appointments_after_appointments_pricing', get_the_ID() ); ?>

    <!-- </div> -->
	<div class = "form-group">
		<label class ="control-label col-sm-3 col-md-3" for="_wc_appointment_duration"><?php esc_html_e( 'Duration', 'woocommerce-appointments' ); ?></label>
		<div class="col-md-6 col-sm-9">
			<input class="col-md-3" type="number" name="_wc_appointment_duration" id="_wc_appointment_duration" value="<?php echo esc_html( $duration ); ?>" step="1" min="1" style="margin-right: 7px; width: 4em;">
			<select class="col-md-3" name="_wc_appointment_duration_unit" id="_wc_appointment_duration_unit" class="short" style="width: auto; margin-right: 7px;">
				<option value="minute" <?php selected( $duration_unit, 'minute' ); ?>><?php esc_html_e( 'Minute(s)', 'woocommerce-appointments' ); ?></option>
				<option value="hour" <?php selected( $duration_unit, 'hour' ); ?>><?php esc_html_e( 'Hour(s)', 'woocommerce-appointments' ); ?></option>
				<option value="day" <?php selected( $duration_unit, 'day' ); ?>><?php esc_html_e( 'Day(s)', 'woocommerce-appointments' ); ?></option>
				<option value="month" <?php selected( $duration_unit, 'month' ); ?>><?php esc_html_e( 'Month(s)', 'woocommerce-appointments' ); ?></option>
			</select>
			<?php echo wc_help_tip( esc_html__( 'How long do you plan this appointment to last?', 'woocommerce-appointments' ) ); // WPCS: XSS ok. ?>
		</div>
	</div>
	<div class = "form-group _wc_appointment_interval_duration_wrap">
		<label class ="control-label col-sm-3 col-md-3" for="_wc_appointment_interval"><?php esc_html_e( 'Interval', 'woocommerce-appointments' ); ?></label>
		<div class="col-md-6 col-sm-9">
			<input class="col-md-3" type="number" name="_wc_appointment_interval" id="_wc_appointment_interval" value="<?php echo esc_html( $interval ); ?>" step="1" min="1" style="margin-right: 7px; width: 4em;">
			<select class="col-md-3" name="_wc_appointment_interval_unit" id="_wc_appointment_interval_unit" class="short" style="width: auto; margin-right: 7px;">
				<option value="minute" <?php selected( $interval_unit, 'minute' ); ?>><?php esc_html_e( 'Minute(s)', 'woocommerce-appointments' ); ?></option>
				<option value="hour" <?php selected( $interval_unit, 'hour' ); ?>><?php esc_html_e( 'Hour(s)', 'woocommerce-appointments' ); ?></option>
			</select>
			<?php echo wc_help_tip( esc_html__( 'Select intervals when each appointment slot is available for scheduling.', 'woocommerce-appointments' ) ); // WPCS: XSS ok. ?>
		</div>
	</div>
	<div class = "form-group _wc_appointment_padding_duration_wrap">
		<label class ="control-label col-sm-3 col-md-3" for="_wc_appointment_padding_duration"><?php esc_html_e( 'Padding Time', 'woocommerce-appointments' ); ?></label>
		<div class="col-md-6 col-sm-9">
			<input class="col-md-3" type="number" name="_wc_appointment_padding_duration" id="_wc_appointment_padding_duration" value="<?php echo esc_html( $padding_duration ); ?>" step="1" min="0" style="margin-right: 7px; width: 4em;">
			<select class="col-md-3" name="_wc_appointment_padding_duration_unit" id="_wc_appointment_padding_duration_unit" class="short" style="width: auto; margin-right: 7px;">
				<option value="minute" <?php selected( $padding_duration_unit, 'minute' ); ?>><?php esc_html_e( 'Minute(s)', 'woocommerce-appointments' ); ?></option>
				<option value="hour" <?php selected( $padding_duration_unit, 'hour' ); ?>><?php esc_html_e( 'Hour(s)', 'woocommerce-appointments' ); ?></option>
				<option value="day" <?php selected( $padding_duration_unit, 'day' ); ?>><?php esc_html_e( 'Day(s)', 'woocommerce-appointments' ); ?></option>
				<option value="month" <?php selected( $padding_duration_unit, 'month' ); ?>><?php esc_html_e( 'Month(s)', 'woocommerce-appointments' ); ?></option>
			</select>
			<?php echo wc_help_tip( esc_html__( 'Specify the padding time you need between appointments.', 'woocommerce-appointments' ) ); // WPCS: XSS ok. ?>
		</div>
	</div>
	<div class = "form-group">
		<label class ="control-label col-sm-3 col-md-3" for="_wc_appointment_min_date"><?php esc_html_e( 'Lead Time', 'woocommerce-appointments' ); ?></label>
		<div class="col-md-6 col-sm-9">
			<input class="col-md-3" type="number" name="_wc_appointment_min_date" id="_wc_appointment_min_date" value="<?php echo esc_html( $min_date ); ?>" step="1" min="0" style="margin-right: 7px; width: 4em;">
			<select class="col-md-3" name="_wc_appointment_min_date_unit" id="_wc_appointment_min_date_unit" class="short" style="margin-right: 7px; width: auto;">
				<option value="hour" <?php selected( $min_date_unit, 'hour' ); ?>><?php esc_html_e( 'Hour(s)', 'woocommerce-appointments' ); ?></option>
				<option value="day" <?php selected( $min_date_unit, 'day' ); ?>><?php esc_html_e( 'Day(s)', 'woocommerce-appointments' ); ?></option>
				<option value="week" <?php selected( $min_date_unit, 'week' ); ?>><?php esc_html_e( 'Week(s)', 'woocommerce-appointments' ); ?></option>
				<option value="month" <?php selected( $min_date_unit, 'month' ); ?>><?php esc_html_e( 'Month(s)', 'woocommerce-appointments' ); ?></option>
			</select> <?php echo wc_help_tip( esc_html__( 'How much in advance do you need before a client schedules an appointment?', 'woocommerce-appointments' ) ); // WPCS: XSS ok. ?>
		</div>
	</div>
	<div class = "form-group">
		<label class ="control-label col-sm-3 col-md-3" for="_wc_appointment_max_date"><?php esc_html_e( 'Scheduling Window', 'woocommerce-appointments' ); ?></label>
		<div class="col-md-6 col-sm-9">
			<input class="col-md-3" type="number" name="_wc_appointment_max_date" id="_wc_appointment_max_date" value="<?php echo esc_html( $max_date ); ?>" step="1" min="1" style="margin-right: 7px; width: 4em;">
			<select class="col-md-3" name="_wc_appointment_max_date_unit" id="_wc_appointment_max_date_unit" class="short" style="margin-right: 7px; width: auto;">
				<option value="hour" <?php selected( $max_date_unit, 'hour' ); ?>><?php esc_html_e( 'Hour(s)', 'woocommerce-appointments' ); ?></option>
				<option value="day" <?php selected( $max_date_unit, 'day' ); ?>><?php esc_html_e( 'Day(s)', 'woocommerce-appointments' ); ?></option>
				<option value="week" <?php selected( $max_date_unit, 'week' ); ?>><?php esc_html_e( 'Week(s)', 'woocommerce-appointments' ); ?></option>
				<option value="month" <?php selected( $max_date_unit, 'month' ); ?>><?php esc_html_e( 'Month(s)', 'woocommerce-appointments' ); ?></option>
			</select>
			<?php echo wc_help_tip( esc_html__( 'How far in advance are customers allowed to schedule an appointment?', 'woocommerce-appointments' ) ); // WPCS: XSS ok. ?>
		</div>
	</div>
	<div class = "form-group">
		<label class="control-label col-sm-3 col-md-3" for="_wc_appointment_requires_confirmation"><?php esc_html_e( 'Requires confirmation?', 'woocommerce-appointments' ); ?></label>
        <div class="col-md-6 col-sm-9">
            <input type="checkbox" class="form-control" name="_wc_appointment_requires_confirmation" id="_wc_appointment_requires_confirmation" value="yes" <?php checked( $appointable_product->get_requires_confirmation( 'edit' ), true ); ?>>
            <span class="form-text"><?php esc_html_e( 'Check this box if appointment requires confirmation. Payment will not be taken during checkout.', 'woocommerce-appointments' ); ?></span>
        </div>
    </div>
    <div class = "form-group">
		<label class="control-label col-sm-3 col-md-3" for="_wc_appointment_user_can_cancel"><?php esc_html_e( 'Can be cancelled?', 'woocommerce-appointments' ); ?></label>
        <div class="col-md-6 col-sm-9">
            <input type="checkbox" class="form-control" name="_wc_appointment_user_can_cancel" id="_wc_appointment_user_can_cancel" value="yes" <?php checked( $appointable_product->get_user_can_cancel( 'edit' ), true ); ?>>
            <span class="form-text"><?php esc_html_e( 'Check this box if appointment can be cancelled by the customer. A refund will not be sent automatically.', 'woocommerce-appointments' ); ?></span>
        </div>
    </div>
    <div class = "form-group appointment-cancel-limit">
    	<label class ="control-label col-sm-3 col-md-3" for="_wc_appointment_cancel_limit"><?php esc_html_e( 'Cancelled at least', 'woocommerce-appointments' ); ?></label>
    	<div class="col-md-6 col-sm-9">
			<input class="col-md-3" type="number" name="_wc_appointment_cancel_limit" id="_wc_appointment_cancel_limit" value="<?php echo esc_html( $cancel_limit ); ?>" step="1" min="1" style="margin-right: 7px; width: 4em;">
			<select class="col-md-3" name="_wc_appointment_cancel_limit_unit" id="_wc_appointment_cancel_limit_unit" class="short" style="width: auto; margin-right: 7px;">
				<option value="month" <?php selected( $cancel_limit_unit, 'month' ); ?>><?php esc_html_e( 'Month(s)', 'woocommerce-appointments' ); ?></option>
				<option value="day" <?php selected( $cancel_limit_unit, 'day' ); ?>><?php esc_html_e( 'Day(s)', 'woocommerce-appointments' ); ?></option>
				<option value="hour" <?php selected( $cancel_limit_unit, 'hour' ); ?>><?php esc_html_e( 'Hour(s)', 'woocommerce-appointments' ); ?></option>
				<option value="minute" <?php selected( $cancel_limit_unit, 'minute' ); ?>><?php esc_html_e( 'Minute(s)', 'woocommerce-appointments' ); ?></option>
			</select>
			<span class="col-md-6"><?php esc_html_e( 'before the start date.', 'woocommerce-appointments' ); ?></span>
			<script type="text/javascript">
				jQuery( '._tax_status_field' ).closest( '.show_if_simple' ).addClass( 'show_if_appointment' );
				jQuery( 'select#_wc_appointment_duration_unit, select#_wc_appointment_duration_type, input#_wc_appointment_duration' ).change(function(){
					if ( [ 'day', 'month' ].includes( jQuery('select#_wc_appointment_duration_unit').val() ) && '1' == jQuery('input#_wc_appointment_duration').val() && 'customer' === jQuery('select#_wc_appointment_duration_type').val() ) {
						jQuery('p._wc_appointment_enable_range_picker_field').show();
					} else {
						jQuery('p._wc_appointment_enable_range_picker_field').hide();
					}
				});
				jQuery( '#_wc_appointment_duration_unit' ).change();
			</script>
		</div>
    </div>
    <div class = "form-group">
		<label class="control-label col-sm-3 col-md-3" for="_wc_appointment_customer_timezones"><?php esc_html_e( 'Customer timezones?', 'woocommerce-appointments' ); ?></label>
        <div class="col-md-6 col-sm-9">
            <input type="checkbox" class="form-control" name="_wc_appointment_customer_timezones" id="_wc_appointment_customer_timezones" value="yes" <?php checked( $appointable_product->get_customer_timezones( 'edit' ), true ); ?>>
            <span class="form-text"><?php esc_html_e( 'Check this box if can be converted to customer\'s timezone.', 'woocommerce-appointments' ); ?></span>
        </div>
	</div>
	<?php do_action('wcmp-afm-addition-content'); ?> 
</div>