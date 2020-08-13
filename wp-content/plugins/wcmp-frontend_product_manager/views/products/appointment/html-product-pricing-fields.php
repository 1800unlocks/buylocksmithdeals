<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$intervals = array();

$intervals['months'] = array(
	'1'  => __( 'January', 'woocommerce-appointments' ),
	'2'  => __( 'February', 'woocommerce-appointments' ),
	'3'  => __( 'March', 'woocommerce-appointments' ),
	'4'  => __( 'April', 'woocommerce-appointments' ),
	'5'  => __( 'May', 'woocommerce-appointments' ),
	'6'  => __( 'June', 'woocommerce-appointments' ),
	'7'  => __( 'July', 'woocommerce-appointments' ),
	'8'  => __( 'August', 'woocommerce-appointments' ),
	'9'  => __( 'September', 'woocommerce-appointments' ),
	'10' => __( 'October', 'woocommerce-appointments' ),
	'11' => __( 'November', 'woocommerce-appointments' ),
	'12' => __( 'December', 'woocommerce-appointments' ),
);

$intervals['days'] = array(
	'1' => __( 'Monday', 'woocommerce-appointments' ),
	'2' => __( 'Tuesday', 'woocommerce-appointments' ),
	'3' => __( 'Wednesday', 'woocommerce-appointments' ),
	'4' => __( 'Thursday', 'woocommerce-appointments' ),
	'5' => __( 'Friday', 'woocommerce-appointments' ),
	'6' => __( 'Saturday', 'woocommerce-appointments' ),
	'7' => __( 'Sunday', 'woocommerce-appointments' ),
);

for ( $i = 1; $i <= 53; $i ++ ) {
	/* translators: 1: week number */
	$intervals['weeks'][ $i ] = sprintf( __( 'Week %s', 'woocommerce-appointments' ), $i );
}

if ( ! isset( $pricing['type'] ) ) {
	$pricing['type'] = 'custom';
}

if ( ! isset( $pricing['modifier'] ) ) {
	$pricing['modifier'] = '';
}
if ( ! isset( $pricing['base_modifier'] ) ) {
	$pricing['base_modifier'] = '';
}
if ( ! isset( $pricing['base_cost'] ) ) {
	$pricing['base_cost'] = '';
}
?>
<tr>
	<td class="sort"><span class="sortable-icon"></span></td>
	<td class="range_type">
		<div class="select wc_appointment_pricing_type">
			<select name="wc_appointment_pricing_type[]">
				<option value="custom" <?php selected( $pricing['type'], 'custom' ); ?>><?php esc_html_e( 'Date range', 'woocommerce-appointments' ); ?></option>
				<option value="months" <?php selected( $pricing['type'], 'months' ); ?>><?php esc_html_e( 'Range of months', 'woocommerce-appointments' ); ?></option>
				<option value="weeks" <?php selected( $pricing['type'], 'weeks' ); ?>><?php esc_html_e( 'Range of weeks', 'woocommerce-appointments' ); ?></option>
				<option value="days" <?php selected( $pricing['type'], 'days' ); ?>><?php esc_html_e( 'Range of days', 'woocommerce-appointments' ); ?></option>
				<option value="quant" <?php selected( $pricing['type'], 'quant' ); ?>><?php esc_html_e( 'Quantity count', 'woocommerce-appointments' ); ?></option>
				<option value="slots" <?php selected( $pricing['type'], 'slots' ); ?>><?php esc_html_e( 'Duration', 'woocommerce-appointments' ); ?></option>
				<optgroup label="<?php esc_html_e( 'Time Ranges', 'woocommerce-appointments' ); ?>">
					<option value="time" <?php selected( $pricing['type'], 'time' ); ?>><?php esc_html_e( 'Recurring Time (all week)', 'woocommerce-appointments' ); ?></option>
					<option value="time:range" <?php selected( $pricing['type'], 'time:range' ); ?>><?php esc_html_e( 'Recurring Time (date range)', 'woocommerce-appointments' ); ?></option>
					<?php foreach ( $intervals['days'] as $key => $label ) : ?>
						<option value="time:<?php echo esc_html( $key ); ?>" <?php selected( $pricing['type'], 'time:' . $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</optgroup>
			</select>
		</div>
	</td>
	<td style="border-right:0;">
		<div class="appointments-datetime-select-from">
			<div class="select from_day_of_week">
				<select name="wc_appointment_pricing_from_day_of_week[]" class="form-control">
					<?php foreach ( $intervals['days'] as $key => $label ) : ?>
						<option value="<?php echo $key; ?>" <?php selected( isset( $pricing['from'] ) && $pricing['from'] == $key, true ); ?>><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="select from_month">
				<select name="wc_appointment_pricing_from_month[]" class="form-control">
					<?php foreach ( $intervals['months'] as $key => $label ) : ?>
						<option value="<?php echo $key; ?>" <?php selected( isset( $pricing['from'] ) && $pricing['from'] == $key, true ); ?>><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="select from_week">
				<select name="wc_appointment_pricing_from_week[]" class="form-control">
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
				} elseif ( 'time:range' === $pricing['type'] && ! empty( $pricing['from_date'] ) ) {
					$from_date = $pricing['from_date'];
				}
				?>
				<span class="date-inp-wrap">
					<input type="text" class="date-picker form-control" name="wc_appointment_pricing_from_date[]" value="<?php echo esc_attr( $from_date ); ?>"/>
				</span>
			</div>

			<div class="from_time">
				<input type="time" class="time-picker form-control" name="wc_appointment_pricing_to_time[]" value="<?php
				if ( strrpos( $pricing['type'], 'time' ) === 0 && ! empty( $pricing['to'] ) ) {
					echo 'value="' . esc_html( $pricing['to'] ) . '"';
				}
				?>" placeholder="HH:MM" />
			</div>

			<div class="from">
				<input type="number" step="1" name="wc_appointment_pricing_to[]" value="<?php
				if ( ! empty( $pricing['to'] ) && is_numeric( $pricing['to'] ) ) {
					echo 'value="' . esc_html( $pricing['to'] ) . '"';
				}
				?>" class="form-control" />
			</div>
		</div>
	</td>
	<td style="border-right:0;" class="range_to">
		<div class='appointments-datetime-select-to'>
			<div class="select to_day_of_week">
				<select name="wc_appointment_pricing_to_day_of_week[]" class="form-control">
					<?php foreach ( $intervals['days'] as $key => $label ) : ?>
						<option value="<?php echo esc_html( $key ); ?>" <?php selected( isset( $pricing['to'] ) && $pricing['to'] == $key, true ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="select to_month">
				<select name="wc_appointment_pricing_to_month[]" class="form-control">
					<?php foreach ( $intervals['months'] as $key => $label ) : ?>
						<option value="<?php echo esc_html( $key ); ?>" <?php selected( isset( $pricing['to'] ) && $pricing['to'] == $key, true ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="select to_week">
				<select name="wc_appointment_pricing_to_week[]" class="form-control">
					<?php foreach ( $intervals['weeks'] as $key => $label ) : ?>
						<option value="<?php echo esc_html( $key ); ?>" <?php selected( isset( $pricing['to'] ) && $pricing['to'] == $key, true ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="to_date">
				<?php
				$to_date = '';
				if ( 'custom' === $pricing['type'] && ! empty( $pricing['to'] ) ) {
					$to_date = $pricing['to'];
				} elseif ( 'time:range' === $pricing['type'] && ! empty( $pricing['to_date'] ) ) {
					$to_date = $pricing['to_date'];
				}
				?>
				<span class="date-inp-wrap">
					<input type="text" class="date-picker form-control" name="wc_appointment_pricing_to_date[]" value="<?php echo esc_attr( $to_date ); ?>"/>
				</span>
			</div>

			<div class="to_time">
			<input type="time" class="time-picker form-control" name="wc_appointment_pricing_to_time[]" value="<?php
				if ( strrpos( $pricing['type'], 'time' ) === 0 && ! empty( $pricing['to'] ) ) {
					echo 'value="' . esc_html( $pricing['to'] ) . '"';
				}
				?>" placeholder="HH:MM" />
			</div>

			<div class="to">
				<input type="number" step="1" name="wc_appointment_pricing_to[]" value="<?php
				if ( ! empty( $pricing['to'] ) && is_numeric( $pricing['to'] ) ) {
					echo 'value="' . esc_html( $pricing['to'] ) . '"';
				}
				?>" class="form-control" />
			</div>

		</div>
	</td>

	<td>
		<div class="select">
			<select name="wc_appointment_pricing_base_cost_modifier[]" class="form-control">
				<option <?php selected( $pricing['base_modifier'], '' ); ?> value="">+</option>
				<option <?php selected( $pricing['base_modifier'], 'minus' ); ?> value="minus">-</option>
				<option <?php selected( $pricing['base_modifier'], 'times' ); ?> value="times">&times;</option>
				<option <?php selected( $pricing['base_modifier'], 'divide' ); ?> value="divide">&divide;</option>
				<option <?php selected( $pricing['base_modifier'], 'equals' ); ?> value="equals">=</option>
			</select>
		</div>
			<input
				type="number"
				step="0.00001"
				name="wc_appointment_pricing_base_cost[]"
				class="form-control"
				<?php
				if ( ! empty( $pricing['base_cost'] ) ) {
					echo 'value="' . esc_html( $pricing['base_cost'] ) . '"';
				}
				?>
				placeholder="0"
			/>
			<?php do_action( 'wcmp_afm_appointments_after_appointment_pricing_base_cost', $pricing, get_the_ID() ); ?>
	</td>
	<td>
		<div class="select">
			<select name="wc_appointment_pricing_cost_modifier[]" class="form-control">
				<option <?php selected( $pricing['modifier'], '' ); ?> value="">+</option>
				<option <?php selected( $pricing['modifier'], 'minus' ); ?> value="minus">-</option>
				<option <?php selected( $pricing['modifier'], 'equals' ); ?> value="equals">=</option>
				<!--
				<option <?php selected( $pricing['modifier'], 'times' ); ?> value="times">&times;</option>
				<option <?php selected( $pricing['modifier'], 'divide' ); ?> value="divide">&divide;</option>
				-->
			</select>
		</div>
		<input
			type="number"
			step="0.00001"
			name="wc_appointment_pricing_cost[]"
			class="form-control"
			<?php
			if ( ! empty( $pricing['cost'] ) ) {
				echo 'value="' . esc_html( $pricing['cost'] ) . '"';
			}
			?>
			placeholder="0"
		/>
			<?php do_action( 'wcmp_afm_appointments_after_appointment_pricing_cost', $pricing, get_the_ID() ); ?>
	</td>
	<td class="remove remove_grid_row remove_rule"><a href="#" class="delete" title="<?php esc_html_e( 'Delete', 'woocommerce' ); ?>"><i class="wcmp-font ico-delete-icon"></i></a></td>
</tr>




