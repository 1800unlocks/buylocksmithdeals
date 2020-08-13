<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="col-md-12 appointment-calendar-wrapper">
    <?php do_action( 'before_wcmp_afm_appointment_month_calendar_form' ); ?>

	<form method="get" id="mainform" enctype="multipart/form-data" class="wc_appointments_calendar_form">
		<?php do_action( 'wcmp_afm_appointment_month_calendar_form_start' ); ?>
		<div class="panel panel-default pannel-outer-heading mt-0">
            <div class="panel-body panel-content-padding form-horizontal">
				<input type="hidden" name="post_type" value="wc_appointment" />
				<input type="hidden" name="page" value="appointment_calendar" />
				<input type="hidden" name="calendar_month" value="<?php echo absint( $month ); ?>" />
				<input type="hidden" name="view" value="<?php echo esc_attr( $view ); ?>" />
				<input type="hidden" name="tab" value="calendar" />

				<div class="tablenav">
                    <div class="filters">
						<?php
						// Product filter.
						$product_name = '';
						$product_id   = '';

						if ( ! empty( $_REQUEST['filter_appointable_product'] ) ) { // phpcs:disable  WordPress.Security.NonceVerification.NoNonceVerification
							$product_id   = absint( $_REQUEST['filter_appointable_product'] ); // WPCS: input var ok, sanitization ok.
							$product      = get_wc_product_appointment( $product_id );
							$product_name = $product ? $product->get_title() : '';
						}
						?>
						<select id="appointable_product_id" name="filter_appointable_products" class="wc-product-search" style="width: 300px;" data-allow_clear="true" data-placeholder="<?php esc_html_e( 'Select an appointable product...', 'woocommerce-appointments' ); ?>"></select>
						</select>
					</div>
						<!-- <div class="views"> -->
					<?php if ( 'month' === $view ) : ?>
					<div class="date_selector">
						<a class="prev" href="<?php echo esc_url( add_query_arg(array('calendar_year'  => $year,'calendar_month' => $month - 1,)));?>">&larr;</a>
		
						<select name="calendar_month" class="wc-enhanced-select" style="width:160px">
							<?php for ( $i = 1; $i <= 12; $i ++ ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $month, $i ); ?>><?php echo esc_attr( ucfirst( date_i18n( 'M', strtotime( '2013-' . $i . '-01' ) ) ) ); ?></option>
							<?php endfor; ?>
						</select>
			
						<select name="calendar_year" class="wc-enhanced-select" style="width:160px">
							<?php $current_year = date( 'Y' ); ?>
							<?php for ( $i = ( $current_year - 1 ); $i <= ( $current_year + 5 ); $i ++ ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $year, $i ); ?>><?php echo esc_attr( $i ); ?></option>
							<?php endfor; ?>
						</select>
						<a class="next" href="
						<?php
						echo esc_url(
							add_query_arg(
								array(
									'calendar_year'  => $year,
									'calendar_month' => $month + 1,
								)
							)
						);
						?>
						">&rarr;</a>
					</div>
		<!-- </div> -->
					<?php endif; ?>
					<?php if ( 'week' === $view ) : ?>
						<div class="week_selector">
							<a class="prev" href="<?php echo esc_url( add_query_arg( 'calendar_day', $prev_week ) ); ?>">&larr;</a>
							<div class="week_picker">
								<input type="hidden" name="calendar_day" class="calendar_day" value="<?php echo esc_attr( $day_formatted ); ?>" />
								<input type="text" name="calendar_week" class="calendar_week date-picker" value="<?php echo esc_attr( $week_formatted ); ?>" placeholder="<?php echo esc_attr( wc_date_format() ); ?>" autocomplete="off" readonly="readonly" />
							</div>
							<a class="next" href="<?php echo esc_url( add_query_arg( 'calendar_day', $next_week ) ); ?>">&rarr;</a>
						</div>
					<?php endif; ?>
					<?php if ( in_array( $view, array( 'day', 'staff' ) ) ) : ?>
						<div class="date_selector">
							<a class="prev" href="<?php echo esc_url( add_query_arg( 'calendar_day', $prev_day ) ); ?>">&larr;</a>
							<div>
								<input type="text" name="calendar_day" class="calendar_day date-picker" value="<?php echo esc_attr( $day_formatted ); ?>" placeholder="<?php echo esc_attr( wc_date_format() ); ?>" autocomplete="off" />
							</div>
							<a class="next" href="<?php echo esc_url( add_query_arg( 'calendar_day', $next_day ) ); ?>">&rarr;</a>
						</div>
					<?php endif; ?>
					<div class="views">
						<a class="view-select <?php echo ( 'month' === $view ) ? 'current' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'view', 'month' ) ); ?>">
							<?php esc_html_e( 'Month', 'woocommerce-appointments' ); ?>
						</a>
						<a class="view-select <?php echo ( 'week' === $view ) ? 'current' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'view', 'week' ) ); ?>">
							<?php esc_html_e( 'Week', 'woocommerce-appointments' ); ?>
						</a>
						<a class="view-select <?php echo ( 'day' === $view ) ? 'current' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'view', 'day' ) ); ?>">
							<?php esc_html_e( 'Day', 'woocommerce-appointments' ); ?>
						</a>
					</div>
					<script type="text/javascript">
						jQuery(function() {
							jQuery(".tablenav select").change(function() {
								jQuery('#mainform').submit();
							});
						});
					</script>
				</div>

				<table class="wc_appointments_calendar widefat table table-bordered">
					<thead>
						<tr>
							<?php $start_of_week = absint( get_option( 'start_of_week', 1 ) ); ?>
							<?php for ( $ii = $start_of_week; $ii < $start_of_week + 7; $ii ++ ) : ?>
								<th><?php echo esc_attr( date_i18n( _x( 'l', 'date format', 'woocommerce-appointments' ), strtotime( "next sunday +{$ii} day" ) ) ); ?></th>
							<?php endfor; ?>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php
							$timestamp    = $start_time;
							$current_date = date( 'Y-m-d', current_time( 'timestamp' ) );
							$index        = 0;
							while ( $timestamp <= $end_time ) :
								$timestamp_date = date( 'Y-m-d', $timestamp );
								$is_today       = $timestamp_date === $current_date;
								?>
								<td width="14.285%" class="<?php
								if ( date( 'n', $timestamp ) != absint( $month ) ) {
									echo 'calendar-diff-month';
								}
								// }
								?>">
									<a href="<?php echo '?view=day&calendar_day=' . date( 'Y-m-d', $timestamp ); ?>">
										<?php echo esc_attr( date( 'd', $timestamp ) ); ?>
									</a>
									<div class="appointments">
		                                <ul>
		                                    <?php
		                                    $self->list_events(
												date( 'd', $timestamp ),
												date( 'm', $timestamp ),
												date( 'Y', $timestamp ),
												'by_month'
											);
		                                    ?>
		                                </ul>
		                            </div>
								</td>
								<?php
								$timestamp = strtotime( '+1 day', $timestamp );
								$index ++;

								if ( 0 === $index % 7 ) {
									echo '</tr><tr>';
								}
							endwhile;
							?>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</form>
</div>

