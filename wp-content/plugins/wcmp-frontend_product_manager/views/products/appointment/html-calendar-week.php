<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="col-md-12 appointment-calendar-wrapper">
    <?php do_action( 'before_wcmp_afm_appointment_week_calendar_form' ); ?>

	<form method="get" id="mainform" enctype="multipart/form-data" class="wc_appointments_calendar_form week_view">
		<input type="hidden" name="post_type" value="wc_appointment" />
		<input type="hidden" name="page" value="appointment_calendar" />
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
		<div class="calendar_days">
			<div class="calendar_presentation">
				<div class="header_labels">
					<label class="empty_label"></label>
					<label class="allday_label"><?php esc_html_e( 'All Day', 'woocommerce-appointments' ); ?></label>
				</div>
				<div class="header_days">
					<?php 
					$current_timestamp = current_time( 'timestamp' );
					$index = 0;
					?>
					<div class="header_wrapper">
						<?php
						$start_of_week = $week_start;
						while ( $start_of_week <= $week_end ) {
							$current_on_cal = date( 'Y-m-d', $start_of_week ) === date( 'Y-m-d', $current_timestamp );
							$current_class  = $current_on_cal ? ' current' : '';
							$past_on_cal    = date( 'Y-m-d', $start_of_week ) < date( 'Y-m-d', $current_timestamp );
							$current_class .= $past_on_cal ? ' past' : '';
							echo "<div class='header_column$current_class' data-time='" . date( 'Y-m-d', $start_of_week ) . "'>";
								echo '<div class="header_label"><a href="' . admin_url( 'edit.php?post_type=wc_appointment&page=appointment_calendar&view=day&tab=calendar&calendar_day=' . date( 'Y-m-d', $start_of_week ) ) . '" title="' . date( wc_date_format(), $start_of_week ) . '">' . date( 'D', $start_of_week ) . ' <span class="daynum">' . date( 'j', $start_of_week ) . '</span></a></div>';
								echo '<div class="header_allday">';
									echo '<div class="events allday">';
										$self->list_events(
											date( 'd', $start_of_week ),
											date( 'm', $start_of_week ),
											date( 'Y', $start_of_week ),
											'all_day'
										);
									echo '</div>';
								echo '</div>';
							echo '</div>';

							$start_of_week = strtotime( '+1 day', $start_of_week );
							$index ++;
						}
						?>
					</div>
				</div>
			</div>
            <ul class="hours">
                <?php for ( $i = 0; $i < 24; $i ++ ) : ?>
                    <li><label>
                            <?php
                            if ( 0 != $i && 24 != $i ) {
                                echo date_i18n( wc_time_format(), strtotime( "midnight +{$i} hour" ) );
                            }
                            ?>
                        </label></li>
                <?php endfor; ?>
            </ul>
            <ul class="appointments weeks">
                <div class="body_days">
				<?php 
				$current_timestamp = current_time( 'timestamp' );
				$index = 0;
				?>
				<div class="body_wrapper">
					<?php
					$start_of_week = $week_start;
					while ( $start_of_week <= $week_end ) {
						$current_on_cal = date( 'Y-m-d', $start_of_week ) === date( 'Y-m-d', $current_timestamp );
						$current_class  = $current_on_cal ? ' current' : '';
						$past_on_cal    = date( 'Y-m-d', $start_of_week ) < date( 'Y-m-d', $current_timestamp );
						$current_class .= $past_on_cal ? ' past' : '';
						echo "<div class='body_column$current_class' data-time='" . date( 'Y-m-d', $start_of_week ) . "'>";
							echo '<div class="events bytime">';
								$self->list_events(
									date( 'd', $start_of_week ),
									date( 'm', $start_of_week ),
									date( 'Y', $start_of_week ),
									'by_time'
								);
							echo '</div>';
						echo '</div>';

						$start_of_week = strtotime( '+1 day', $start_of_week );
						$index ++;
					}
					?>
				</div>
			</div>
            </ul>
        </div>
	</form>
</div>