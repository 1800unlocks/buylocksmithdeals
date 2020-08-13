<?php
/**
 * Accommodation Booking Range Pricing template
 * Not overridable
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/accommodation
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;

$intervals = array();

$intervals['months'] = array(
	'1' => __( 'January', 'woocommerce-accommodation-bookings' ),
	'2' => __( 'February', 'woocommerce-accommodation-bookings' ),
	'3' => __( 'March', 'woocommerce-accommodation-bookings' ),
	'4' => __( 'April', 'woocommerce-accommodation-bookings' ),
	'5' => __( 'May', 'woocommerce-accommodation-bookings' ),
	'6' => __( 'June', 'woocommerce-accommodation-bookings' ),
	'7' => __( 'July', 'woocommerce-accommodation-bookings' ),
	'8' => __( 'August', 'woocommerce-accommodation-bookings' ),
	'9' => __( 'September', 'woocommerce-accommodation-bookings' ),
	'10' => __( 'October', 'woocommerce-accommodation-bookings' ),
	'11' => __( 'November', 'woocommerce-accommodation-bookings' ),
	'12' => __( 'December', 'woocommerce-accommodation-bookings' ),
);

$intervals['days'] = array(
	'1' => __( 'Monday', 'woocommerce-accommodation-bookings' ),
	'2' => __( 'Tuesday', 'woocommerce-accommodation-bookings' ),
	'3' => __( 'Wednesday', 'woocommerce-accommodation-bookings' ),
	'4' => __( 'Thursday', 'woocommerce-accommodation-bookings' ),
	'5' => __( 'Friday', 'woocommerce-accommodation-bookings' ),
	'6' => __( 'Saturday', 'woocommerce-accommodation-bookings' ),
	'7' => __( 'Sunday', 'woocommerce-accommodation-bookings' ),
);

for ( $i = 1; $i <= 52; $i ++ ) {
	/* translators: 1: week number */
	$intervals['weeks'][ $i ] = sprintf( __( 'Week %s', 'woocommerce-accommodation-bookings' ), $i );
}

if ( ! isset( $pricing['type'] ) ) {
	$pricing['type'] = 'custom';
}
?>
<tr>
	<td class="sort"><span class="sortable-icon"></span></td>
	<td>
		<div class="select wc_booking_pricing_type">
			<select name="wc_accommodation_booking_pricing_type[]" class="form-control">
				<option value="custom" <?php selected( $pricing['type'], 'custom' ); ?>><?php _e( 'Range of certain nights', 'woocommerce-accommodation-bookings' ); ?></option>
				<option value="months" <?php selected( $pricing['type'], 'months' ); ?>><?php _e( 'Range of months', 'woocommerce-accommodation-bookings' ); ?></option>
				<option value="weeks" <?php selected( $pricing['type'], 'weeks' ); ?>><?php _e( 'Range of weeks', 'woocommerce-accommodation-bookings' ); ?></option>
				<option value="days" <?php selected( $pricing['type'], 'days' ); ?>><?php _e( 'Range of nights during the week', 'woocommerce-accommodation-bookings' ); ?></option>
			</select>
		</div>
	</td>
	<td style="border-right:0;">
	<div class="bookings-datetime-select-from">
		<div class="select from_day_of_week">
			<select name="wc_accommodation_booking_pricing_from_day_of_week[]" class="form-control">
				<?php foreach ( $intervals['days'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $pricing['from'] ) && $pricing['from'] == $key, true ); ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select from_month">
			<select name="wc_accommodation_booking_pricing_from_month[]" class="form-control">
				<?php foreach ( $intervals['months'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $pricing['from'] ) && $pricing['from'] == $key, true ); ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select from_week">
			<select name="wc_accommodation_booking_pricing_from_week[]" class="form-control">
				<?php foreach ( $intervals['weeks'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $pricing['from'] ) && $pricing['from'] == $key, true ); ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="from_date">
			<?php
			$from_date = '';
			if ( 'custom' === $pricing['type'] && ! empty( $pricing['from'] ) ) {
				$from_date = $pricing['from'];
			}
			?>
			<span class="date-inp-wrap">
				<input type="text" class="date-picker form-control" name="wc_accommodation_booking_pricing_from_date[]" value="<?php echo esc_attr( $from_date ); ?>" />
			</span>
		</div>
	</div>
	</td>
	<td>
	<div class="bookings-datetime-select-to">
		<div class="select to_day_of_week">
			<select name="wc_accommodation_booking_pricing_to_day_of_week[]" class="form-control">
				<?php foreach ( $intervals['days'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $pricing['to'] ) && $pricing['to'] == $key, true ); ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select to_month">
			<select name="wc_accommodation_booking_pricing_to_month[]" class="form-control">
				<?php foreach ( $intervals['months'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $pricing['to'] ) && $pricing['to'] == $key, true ); ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="select to_week">
			<select name="wc_accommodation_booking_pricing_to_week[]" class="form-control">
				<?php foreach ( $intervals['weeks'] as $key => $label ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( isset( $pricing['to'] ) && $pricing['to'] == $key, true ); ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="to_date">
			<?php
			$to_date = '';
			if ( 'custom' === $pricing['type'] && ! empty( $pricing['to'] ) ) {
				$to_date = $pricing['to'];
			}
			?>
			<span class="date-inp-wrap">
				<input type="text" class="date-picker form-control" name="wc_accommodation_booking_pricing_to_date[]" value="<?php echo esc_attr( $to_date ); ?>" />
			</span>
		</div>
	</div>
	</td>
	<td>
		<input type="number" step="0.01" name="wc_accommodation_booking_pricing_block_cost[]" value="<?php
		if ( ! empty( $pricing['override_block'] ) ) {
			echo $pricing['override_block'];
		}
		?>" placeholder="0" class="form-control"/>
	<?php do_action( 'afm_accommodation_bookings_after_booking_pricing_override_block_cost', $pricing, $id ); ?>
	</td>
	<td class="remove"><a href="#" class="delete" title="<?php esc_html_e( 'Delete', 'woocommerce' ); ?>"><i class="wcmp-font ico-delete-icon"></i></a></td>
</tr>