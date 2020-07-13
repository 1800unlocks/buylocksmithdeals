<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// Get booking object.

if(isset($_POST['update_btn']) && $_POST['update_btn'] == 'Update'){
                $post_id=$_POST['bookingid'];
				$orderid=$_POST['orderid'];
                $customer_email=$_POST['_booking_customer_email'];
		$booking    = new WC_Booking( $post_id );
		$before_update_booking_date=date( 'F d, Y', $booking->get_start() ).' - '.date( 'F d, Y', $booking->get_end() );
		$before_update_booking_time=date( 'h:i a', $booking->get_start() ). ' - '.date( 'h:i a', $booking->get_end() );
		
		$after_update_booking_date=date('F d, Y', strtotime($_POST['booking_start_date'])).' - '.date('F d, Y',strtotime($_POST['booking_end_date']));
		$after_update_booking_time=date('h:i a', strtotime($_POST['booking_start_time'])).' - '.date('h:i a', strtotime($_POST['booking_end_time']));
		
		$product_id = wc_clean( $_POST['product_or_resource_id'] ) ?: $booking->get_product_id();
		$start_date = explode( '-', wc_clean( $_POST['booking_start_date'] ) );
		$end_date   = explode( '-', wc_clean( $_POST['booking_end_date'] ) );
		$start_time = explode( ':', wc_clean( $_POST['booking_start_time'] ) );
		$end_time   = explode( ':', wc_clean( $_POST['booking_end_time'] ) );
		$start      = mktime( $start_time[0], $start_time[1], 0, $start_date[1], $start_date[2], $start_date[0] );
		$end        = mktime( $end_time[0], $end_time[1], 0, $end_date[1], $end_date[2], $end_date[0] );
		
		
		if ( strstr( $product_id, '=>' ) ) {
			list( $product_id, $resource_id ) = explode( '=>', $product_id );
		} else {
			$resource_id = 0;
		}

		$person_counts     = $booking->get_person_counts( 'edit' );
		$product           = wc_get_product( $product_id );
		$booking_types_ids = array_keys( $booking->get_person_counts( 'edit' ) );
		$product_types_ids = $product ? array_keys( $product->get_person_types() ) : array();
		$booking_persons   = array();

		foreach ( array_unique( array_merge( $booking_types_ids, $product_types_ids ) ) as $person_id ) {
			$booking_persons[ $person_id ] = absint( $_POST[ '_booking_person_' . $person_id ] );
		}

		$booking->set_props( array(
			'all_day'       => isset( $_POST['_booking_all_day'] ),
			'customer_id'   => isset( $_POST['_booking_customer_id'] ) ? absint( $_POST['_booking_customer_id'] ) : '',
			'date_created'  => empty( $_POST['booking_date'] ) ? current_time( 'timestamp' ) : strtotime( $_POST['booking_date'] . ' ' . (int) $_POST['booking_date_hour'] . ':' . (int) $_POST['booking_date_minute'] . ':00' ),
			'end'           => $end,
			'order_id'      => isset( $_POST['_booking_order_id'] ) ? absint( $_POST['_booking_order_id'] ) : '',
			'parent_id'     => absint( $_POST['_booking_parent_id'] ),
			'person_counts' => $booking_persons,
			'product_id'    => absint( $product_id ),
			'resource_id'   => absint( $resource_id ),
			'start'         => $start,
			'status'        => wc_clean( $_POST['_booking_status'] ),
		) );

		do_action( 'woocommerce_admin_process_booking_object', $booking );

		$booking->save();

		do_action( 'woocommerce_booking_process_meta', $post_id );
                $order = wc_get_order( $orderid );
                $parent_order_id = $order->get_parent_id();
                $parent_order = wc_get_order( $parent_order_id );
                $template_html  = '/emails/customer-job-update.php';
                
                
                // load the mailer class
                $mailer = WC()->mailer();
                //format the email
                $recipient = $customer_email;
                $subject = __("Order Job updated #".$parent_order_id, 'theme_name');
                $attachments=[];
                $content = wc_get_template_html(
				$template_html,
				array(
					'order'              => $parent_order,
					'email_heading'      => '{vendor_logo}',
					'additional_content' => $before_update_booking_date.'||'.$before_update_booking_time.'||'.$after_update_booking_date.'||'.$after_update_booking_time,
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $mailer,
				)
			); 
                $headers = "Content-Type: text/html\r\n";
                //send the email through wordpress
                $mailer->send( $recipient, $subject, $content, $headers, $attachments );

		
                
                
                    
}


$booking_id=$_REQUEST['bookings_vendor'];
$booking = new WC_Booking( $booking_id );
$order_id=$_REQUEST['order_id'];
$order= wc_get_order($order_id);
$product_id        = $booking->get_product_id( 'edit' );
$resource_id       = $booking->get_resource_id( 'edit' );
$customer_id       = $booking->get_customer_id( 'edit' );
$product           = $booking->get_product( $product_id );
$customer          = $booking->get_customer();
$statuses          = array_unique( array_merge( get_wc_booking_statuses( null, true ), get_wc_booking_statuses( 'user', true ), get_wc_booking_statuses( 'cancel', true ) ) );
$bookable_products = array( '' => __( 'N/A', 'woocommerce-bookings' ) );
$bookable_products[ $product->get_id() ] = $product->get_name();
$resources = $product->get_resources();
foreach ( $resources as $resource ) {
        $bookable_products[ $product->get_id() . '=>' . $resource->get_id() ] = '&nbsp;&nbsp;&nbsp;' . $resource->get_name();
}
$attachments=[];
echo implode( ',', $attachments );
?>
<style>
    #booking_data .booking_data_column {
    width: 32%;
    padding: 0 2% 0 0;
   /* float: left; */
}
#booking_data {
    padding: 23px 24px;
    
}

.booking_data_column_container {
    display: flex;
    flex-wrap: wrap;
}
.booking_data_column_container>div {
    flex: 1 1 33%;
    max-width: 33%;
    padding-right: 20px;
}

#booking_data .booking_data_column label {
    display: block;
    font-size: 13px;
    font-weight: 600;
}

.booking_data_column h4 {
    font-weight: 700;
}
.booking_data_column input {
    padding: 3px 5px 4px;
    box-shadow: none;
    line-height: 20px;
    border: 1px solid #d3dbe2;
    border-radius: 4px;
    height: 34px;
    width: 100%;
}

input#booking_date {
    max-width: 120px;
    margin-bottom: 5px;
}
input#booking_date_hour, #booking_date_minute {
    max-width: 50px;
}
#booking_data .booking_data_column br {
    display: none;
}
.booking_data_column>h4 {
    font-weight: 700;
    margin-bottom: 20px;
    font-size: 16px;
    color: #000;
}

p.form-field.booking_start_time_field,
p.form-field.booking_start_date_field {
    width: 45%;
    float: left;
    margin-right: 10px;
}
p.form-field.booking_end_time_field,
p.form-field.booking_end_date_field {
    width: 45%;
    float: left;
}


.booking_data_column:after {
    content: '';
    display: table;
    clear: both;
}


@media (max-width:767px){
  .booking_data_column_container {
    display: block;
  }
  .booking_data_column_container>div {
    max-width: 100%;
    padding-right: 0;
    margin-bottom: 30px;
  }
}
</style>
<form name="post" action="" method="post" id="post">
<div class="panel-wrap woocommerce">
			<div id="booking_data" class="panel">
                            <input type="hidden" name="bookingid" id="bookingid" value="<?php echo $booking_id; ?>">
                            <input type="hidden" name="orderid" id="orderid" value="<?php echo $order_id; ?>">
				<h2>
				<?php
				/* translators: 1: booking id */
				printf( esc_html__( 'Booking #%s details', 'woocommerce-bookings' ), esc_html( $booking_id ) );
				?>
				</h2>
				<p class="booking_number">
				<?php
				if ( $order ) {
					/* translators: 1: href to order id */
					printf( ' ' . esc_html__( 'Linked to order %s.', 'woocommerce-bookings' ), '<a href="' .home_url() . '/dashboard/vendor-orders/'.$order_id.'">#' . esc_html( $order->get_order_number() ) . '</a>' );
				}
                                    
				?>
				</p>

				<div class="booking_data_column_container">
					<div class="booking_data_column">
						<h4><?php esc_html_e( 'General details', 'woocommerce-bookings' ); ?></h4>

						<p class="form-field form-field-wide">
							<label for="_booking_order_id"><?php esc_html_e( 'Order ID:', 'woocommerce-bookings' ); ?></label>
							<?php if ( version_compare( WC_VERSION, '3.0', '<' ) ) : ?>
								<input type="hidden" name="_booking_order_id" id="_booking_order_id" value="<?php echo esc_attr( $booking->get_order_id() ); ?>" data-selected="<?php echo esc_attr( $order ? $order->get_order_number() : '' ); ?>" data-placeholder="<?php esc_attr_e( 'N/A', 'woocommerce-bookings' ); ?>" data-allow_clear="true" />
							<?php else : ?>
								<select name="_booking_order_id" id="_booking_order_id" data-placeholder="<?php esc_attr_e( 'N/A', 'woocommerce-bookings' ); ?>" data-allow_clear="true">
									<?php if ( $booking->get_order_id() && $order ) : ?>
										<option selected="selected" value="<?php echo esc_attr( $booking->get_order_id() ); ?>"><?php echo esc_html( $order->get_order_number() . ' &ndash; ' . date_i18n( wc_date_format(), strtotime( is_callable( array( $order, 'get_date_created' ) ) ? $order->get_date_created() : $order->post_date ) ) ); ?></option>
									<?php endif; ?>
								</select>
							<?php endif; ?>
						</p>

						<p class="form-field form-field-wide"><label for="booking_date"><?php esc_html_e( 'Date created:', 'woocommerce-bookings' ); ?></label>
							<input type="text" class="date-picker-field" name="booking_date" id="booking_date" maxlength="10" value="<?php echo esc_attr( date_i18n( 'Y-m-d', $booking->get_date_created() ) ); ?>" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" /> @ <input type="number" class="hour" placeholder="<?php esc_attr_e( 'h', 'woocommerce-bookings' ); ?>" name="booking_date_hour" id="booking_date_hour" maxlength="2" size="2" value="<?php echo esc_attr( date_i18n( 'H', $booking->get_date_created() ) ); ?>" pattern="\-?\d+(\.\d{0,})?" />:<input type="number" class="minute" placeholder="<?php esc_attr_e( 'm', 'woocommerce-bookings' ); ?>" name="booking_date_minute" id="booking_date_minute" maxlength="2" size="2" value="<?php echo esc_attr( date_i18n( 'i', $booking->get_date_created() ) ); ?>" pattern="\-?\d+(\.\d{0,})?" />
						</p>
                                                <?php //print_r($booking); ?>
						<p class="form-field form-field-wide">
							<label for="_booking_status"><?php esc_attr_e( 'Booking status:', 'woocommerce-bookings' ); ?></label>
							
                                                        <select id="_booking_status" name="_booking_status" class="wc-enhanced-select">
							<?php
							foreach ( $statuses as $key => $value ) {
								echo '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $booking->get_status(), false ) . '>' . esc_html( $value ) . '</option>';
							}
							?>
							</select>
							<input type="hidden" name="post_status" value="<?php echo esc_attr( $booking->get_status() ); ?>">
						</p>

						<p class="form-field form-field-wide">
							<label for="_booking_customer_id"><?php esc_html_e( 'Customer:', 'woocommerce-bookings' ); ?></label>
							<?php
							$name = ! empty( $customer->name ) ? ' &ndash; ' . $customer->name : '';
							$guest_placeholder = __( 'Guest', 'woocommerce-bookings' );
							if ( 'Guest' === $name ) {
								/* translators: 1: guest name */
								$guest_placeholder = sprintf( _x( 'Guest (%s)', 'Admin booking guest placeholder', 'woocommerce-bookings' ), $name );
							}

							if ( $booking->get_customer_id() ) {
								$user            = get_user_by( 'id', $booking->get_customer_id() );
								$customer_string = sprintf(
									/* translators: 1: full name 2: user id 3: email */
									esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'woocommerce-bookings' ),
									$user ? trim( $user->first_name . ' ' . $user->last_name ) : $customer->name,
									$customer->user_id,
									$customer->email
								);
							} else {
								$customer_string = '';
							}
							?>
                                                        <input type="hidden" name="_booking_customer_email" id="_booking_customer_email" class="wc-customer-search" value="<?php echo esc_attr( $customer->email ); ?>" />
							
							<?php if ( version_compare( WC_VERSION, '3.0', '<' ) ) : ?>
								<input type="hidden" name="_booking_customer_id" id="_booking_customer_id" class="wc-customer-search" value="<?php echo esc_attr( $booking->get_customer_id() ); ?>" data-selected="<?php echo esc_attr( $customer_string ); ?>" data-placeholder="<?php echo esc_attr( $guest_placeholder ); ?>" data-allow_clear="true" />
								<?php else : ?>
								<select name="_booking_customer_id" id="_booking_customer_id" class="wc-customer-search" data-placeholder="<?php echo esc_attr( $guest_placeholder ); ?>" data-allow_clear="true">
									<?php if ( $booking->get_customer_id() ) : ?>
										<option selected="selected" value="<?php echo esc_attr( $booking->get_customer_id() ); ?>"><?php echo esc_attr( $customer_string ); ?></option>
									<?php endif; ?>
								</select>
							<?php endif; ?>
						</p>

						<?php do_action( 'woocommerce_admin_booking_data_after_booking_details', $post->ID ); ?>

					</div>
					<div class="booking_data_column">
						<h4><?php esc_html_e( 'Booking specification', 'woocommerce-bookings' ); ?></h4>

						<?php
						woocommerce_wp_select( array(
							'id'            => 'product_or_resource_id',
							'class'         => 'wc-enhanced-select',
							'wrapper_class' => 'form-field form-field-wide',
							'label'         => __( 'Booked product:', 'woocommerce-bookings' ),
							'options'       => $bookable_products,
							'value'         => $resource_id ? $product_id . '=>' . $resource_id : $product_id,
						) );

						woocommerce_wp_text_input( array(
							'id'            => '_booking_parent_id',
							'label'         => __( 'Parent booking ID:', 'woocommerce-bookings' ),
							'wrapper_class' => 'form-field form-field-wide',
							'placeholder'   => 'N/A',
							'class'         => '',
							'value'         => $booking->get_parent_id() ? $booking->get_parent_id() : '',
						) );

						$person_counts = $booking->get_person_counts();

						echo '<br class="clear" />';
						echo '<h4>' . esc_html__( 'Person(s)', 'woocommerce-bookings' ) . '</h4>';

						$person_types = $product ? $product->get_person_types() : array();

						if ( count( $person_counts ) > 0 || count( $person_types ) > 0 ) {
							$needs_update = false;

							foreach ( $person_counts as $person_id => $person_count ) {
								$person_type = null;

								try {
									$person_type = new WC_Product_Booking_Person_Type( $person_id );
								} catch ( Exception $e ) {
									// This person type was deleted from the database.
									unset( $person_counts[ $person_id ] );
									$needs_update = true;
								}

								if ( $person_type ) {
									woocommerce_wp_text_input( array(
										'id'            => '_booking_person_' . $person_id,
										'label'         => $person_type->get_name(),
										'type'          => 'number',
										'placeholder'   => '0',
										'value'         => $person_count,
										'wrapper_class' => 'booking-person',
									) );
								}
							}

							if ( $needs_update ) {
								$booking->set_person_counts( $person_counts );
								$booking->save();
							}

							$product_booking_diff = array_diff( array_keys( $person_types ), array_keys( $person_counts ) );

							foreach ( $product_booking_diff as $id ) {
								$person_type = $person_types[ $id ];
								woocommerce_wp_text_input( array(
									'id'            => '_booking_person_' . $person_type->get_id(),
									'label'         => $person_type->get_name(),
									'type'          => 'number',
									'placeholder'   => '0',
									'value'         => '0',
									'wrapper_class' => 'booking-person',
								) );
							}
						} else {
							$person_counts = $booking->get_person_counts();
							$person_type   = new WC_Product_Booking_Person_Type( 0 );

							woocommerce_wp_text_input( array(
								'id'            => '_booking_person_0',
								'label'         => $person_type->get_name(),
								'type'          => 'number',
								'placeholder'   => '0',
								'value'         => ! empty( $person_counts[0] ) ? $person_counts[0] : 0,
								'wrapper_class' => 'booking-person',
							) );
						}
						?>
					</div>
					<div class="booking_data_column">
						<h4><?php esc_html_e( 'Booking date &amp; time', 'woocommerce-bookings' ); ?></h4>
						<?php
							woocommerce_wp_text_input( array(
								'id'          => 'booking_start_date',
								'label'       => __( 'Start date:', 'woocommerce-bookings' ),
								'placeholder' => 'yyyy-mm-dd',
								'value'       => date( 'Y-m-d', $booking->get_start( 'edit' ) ),
								'class'       => 'date-picker-field',
							) );

							woocommerce_wp_text_input( array(
								'id'          => 'booking_end_date',
								'label'       => __( 'End date:', 'woocommerce-bookings' ),
								'placeholder' => 'yyyy-mm-dd',
								'value'       => date( 'Y-m-d', $booking->get_end( 'edit' ) ),
								'class'       => 'date-picker-field',
							) );

							/*woocommerce_wp_checkbox( array(
								'id'          => '_booking_all_day',
								'label'       => __( 'All day booking:', 'woocommerce-bookings' ),
								'description' => __( 'Check this box if the booking is for all day.', 'woocommerce-bookings' ),
								'value'       => $booking->get_all_day( 'edit' ) ? 'yes' : 'no',
							) ); */

							woocommerce_wp_text_input( array(
								'id'          => 'booking_start_time',
								'label'       => __( 'Start time:', 'woocommerce-bookings' ),
								'placeholder' => 'hh:mm',
								'value'       => date( 'H:i', $booking->get_start( 'edit' ) ),
								'type'        => 'time',
							) );

							woocommerce_wp_text_input( array(
								'id'          => 'booking_end_time',
								'label'       => __( 'End time:', 'woocommerce-bookings' ),
								'placeholder' => 'hh:mm',
								'value'       => date( 'H:i', $booking->get_end( 'edit' ) ),
								'type'        => 'time',
							) );

						if ( wc_should_convert_timezone( $booking ) ) {
							woocommerce_wp_text_input( array(
								'id'                => 'booking_start_time',
								'label'             => __( 'Start time (local timezone):', 'woocommerce-bookings' ),
								'placeholder'       => 'hh:mm',
								'value'             => date( 'H:i', $booking->get_start( 'edit', true ) ),
								'type'              => 'time',
								'custom_attributes' => array( 'disabled' => 'disabled' ),
							) );

							woocommerce_wp_text_input( array(
								'id'                => 'booking_end_time',
								'label'             => __( 'End time (local timezone):', 'woocommerce-bookings' ),
								'placeholder'       => 'hh:mm',
								'value'             => date( 'H:i', $booking->get_end( 'edit', true ) ),
								'type'              => 'time',
								'custom_attributes' => array( 'disabled' => 'disabled' ),
							) );
						}
						?>
					</div>
				</div>
			</div>
			<div class="clear"></div>
                        
		</div>
<input type="submit" class="btn btn-default update-btn" name="update_btn" id="update_btn" value="Update">
</form>
                    <?php
                    
                    function woocommerce_wp_select( $field ) {
	global $thepostid, $post;

	$thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
	$field     = wp_parse_args(
		$field, array(
			'class'             => 'select short',
			'style'             => '',
			'wrapper_class'     => '',
			'value'             => get_post_meta( $thepostid, $field['id'], true ),
			'name'              => $field['id'],
			'desc_tip'          => false,
			'custom_attributes' => array(),
		)
	);

	$wrapper_attributes = array(
		'class' => $field['wrapper_class'] . " form-field {$field['id']}_field",
	);

	$label_attributes = array(
		'for' => $field['id'],
	);

	$field_attributes          = (array) $field['custom_attributes'];
	$field_attributes['style'] = $field['style'];
	$field_attributes['id']    = $field['id'];
	$field_attributes['name']  = $field['name'];
	$field_attributes['class'] = $field['class'];

	$tooltip     = ! empty( $field['description'] ) && false !== $field['desc_tip'] ? $field['description'] : '';
	$description = ! empty( $field['description'] ) && false === $field['desc_tip'] ? $field['description'] : '';
	?>
	<p <?php echo wc_implode_html_attributes( $wrapper_attributes ); // WPCS: XSS ok. ?>>
		<label <?php echo wc_implode_html_attributes( $label_attributes ); // WPCS: XSS ok. ?>><?php echo wp_kses_post( $field['label'] ); ?></label>
		<?php if ( $tooltip ) : ?>
			<?php echo wc_help_tip( $tooltip ); // WPCS: XSS ok. ?>
		<?php endif; ?>
		<select <?php echo wc_implode_html_attributes( $field_attributes ); // WPCS: XSS ok. ?>>
			<?php
			foreach ( $field['options'] as $key => $value ) {
				echo '<option value="' . esc_attr( $key ) . '"' . wc_selected( $key, $field['value'] ) . '>' . esc_html( $value ) . '</option>';
			}
			?>
		</select>
		<?php if ( $description ) : ?>
			<span class="description"><?php echo wp_kses_post( $description ); ?></span>
		<?php endif; ?>
	</p>
	<?php
}

function woocommerce_wp_text_input( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
	$field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;
	$data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

	switch ( $data_type ) {
		case 'price':
			$field['class'] .= ' wc_input_price';
			$field['value']  = wc_format_localized_price( $field['value'] );
			break;
		case 'decimal':
			$field['class'] .= ' wc_input_decimal';
			$field['value']  = wc_format_localized_decimal( $field['value'] );
			break;
		case 'stock':
			$field['class'] .= ' wc_input_stock';
			$field['value']  = wc_stock_amount( $field['value'] );
			break;
		case 'url':
			$field['class'] .= ' wc_input_url';
			$field['value']  = esc_url( $field['value'] );
			break;

		default:
			break;
	}

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

		foreach ( $field['custom_attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
		<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

	if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
		echo wc_help_tip( $field['description'] );
	}

	echo '<input type="' . esc_attr( $field['type'] ) . '" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';

	if ( ! empty( $field['description'] ) && false === $field['desc_tip'] ) {
		echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
	}

	echo '</p>';
}

function woocommerce_wp_checkbox( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'checkbox';
	$field['style']         = isset( $field['style'] ) ? $field['style'] : '';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
	$field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : 'yes';
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
	$field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;

	// Custom attribute handling
	$custom_attributes = array();

	if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

		foreach ( $field['custom_attributes'] as $attribute => $value ) {
			$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
		}
	}

	echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
		<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';

	if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
		echo wc_help_tip( $field['description'] );
	}

	echo '<input type="checkbox" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['cbvalue'] ) . '" ' . checked( $field['value'], $field['cbvalue'], false ) . '  ' . implode( ' ', $custom_attributes ) . '/> ';

	if ( ! empty( $field['description'] ) && false === $field['desc_tip'] ) {
		echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
	}

	echo '</p>';
}

  wc_enqueue_js( "
			$( '#_booking_all_day' ).change( function () {
				if ( $( this ).is( ':checked' ) ) {
					$( '#booking_start_time, #booking_end_time' ).closest( 'p' ).hide();
				} else {
					$( '#booking_start_time, #booking_end_time' ).closest( 'p' ).show();
				}
			}).change();

			$( '.date-picker-field' ).datepicker({
				dateFormat: 'yy-mm-dd',
				firstDay: ". get_option( 'start_of_week' ) .",
				numberOfMonths: 1,
				showButtonPanel: true,
			});
		" );              