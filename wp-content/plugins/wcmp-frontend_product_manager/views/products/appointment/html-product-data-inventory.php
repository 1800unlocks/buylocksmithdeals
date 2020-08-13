<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// In one of future versions, move to:
# $stock_quantity = max( absint( $appointable_product->get_stock_quantity( 'edit' ) ), 1 );

$stock_quantity = max( absint( $appointable_product->get_qty( 'edit' ) ), 1 );
$capacity_min   = max( absint( $appointable_product->get_qty_min( 'edit' ) ), 1 );
$capacity_max   = max( absint( $appointable_product->get_qty_max( 'edit' ) ), 1 );
$capacity_max   =  $appointable_product->get_qty_max( 'edit' );
?>

<div class="form-group-row show_if_appointment">
	<div class="form-group">
		<label class="control-label col-sm-3 col-md-3" for="_wc_appointment_qty"><?php esc_html_e( 'Quantity', 'woocommerce-appointments' ); ?></label>
		<div class="col-md-6 col-sm-9">
			<input type="number" class="form-control" name="_wc_appointment_qty" id="_wc_appointment_qty" placeholder="<?php esc_html_e( 'Price Varies', 'woocommerce-appointments' ); ?>" value="<?php esc_attr_e( $stock_quantity ); ?>" step="1" min="1">
			<span class="form-text"><?php esc_html_e( 'The maximum number of appointments per slot.', 'woocommerce-appointments' ); ?></span>
		</div>
	</div>
	<div class="form-group _wc_appointment_customer_qty_wrap">
		<label class="control-label col-sm-3 col-md-3" for="_wc_appointment_qty_min"><?php esc_html_e( 'Min order', 'woocommerce-appointments' ); ?></label>
		<div class="col-md-6 col-sm-9">
			<input type="number" class="form-control" name="_wc_appointment_qty_min" id="_wc_appointment_qty_min" placeholder="<?php esc_html_e( 'Price Varies', 'woocommerce-appointments' ); ?>" value="<?php esc_attr_e( $capacity_min ); ?>" step="1" min="1" max="<?php esc_attr_e( $stock_quantity ); ?>">
			<span class="form-text"><?php esc_html_e( 'The minimum number of appointments per order.', 'woocommerce-appointments' ); ?></span>
		</div>
	</div>
	<div class="form-group _wc_appointment_customer_qty_wrap">
		<label class="control-label col-sm-3 col-md-3" for="_wc_appointment_qty_max"><?php esc_html_e( 'Max order', 'woocommerce-appointments' ); ?></label>
		<div class="col-md-6 col-sm-9">
			<input type="number" class="form-control" name="_wc_appointment_qty_max" id="_wc_appointment_qty_max" placeholder="<?php esc_html_e( 'Price Varies', 'woocommerce-appointments' ); ?>" value="<?php esc_attr_e( $capacity_max ); ?>" step="1" min="1" max="<?php esc_attr_e( $stock_quantity ); ?>">
			<span class="form-text"><?php esc_html_e( 'The maximum number of appointments per order.', 'woocommerce-appointments' ); ?></span>
		</div>
	</div>
</div>
