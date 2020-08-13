<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="col-md-12 appointment-calendar-wrapper">
    <?php do_action( 'before_wcmp_afm_appointment_day_calendar_form' ); ?>

	<form method="get" id="mainform" enctype="multipart/form-data" class="wc_appointments_calendar_form">
		<?php do_action( 'wcmp_afm_appointment_day_calendar_form_start' ); ?>
        <div class="panel panel-default pannel-outer-heading">
            <div class="panel-body panel-content-padding form-horizontal">
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
					</div>
					<?php if ( in_array( $view, array( 'day') ) ) : ?>
						<div class="date_selector">
							<a class="prev" href="<?php echo esc_url( add_query_arg( 'calendar_day', $prev_day ) ); ?>">&larr;</a>
							<div>
                            	<span class="date-inp-wrap"><input type="text" name="calendar_day" class="calendar_day form-control" placeholder="yyyy-mm-dd" value="<?php echo esc_attr( $day ); ?>" /></span>
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
				

				<div class="calendar_days">
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
                    <ul class="appointments days">
                        <?php $self->list_events(date( 'd', strtotime( $day ) ), date( 'm', strtotime( $day ) ), date( 'Y', strtotime( $day ) ), 'by_time'
											); ?>
                    </ul>
                </div>


			</div>
		</div>
	</form>
</div>
