<?php
/**
 * Variable subscription pricing fields template
 * Used by WCMp_AFM_Subscription_Integration->woocommerce_variable_subscription_pricing_fields()
 * Not overridable
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/subscription
 * @version     3.0.0
 */

defined( 'ABSPATH' ) || exit;

if ( wcmp_is_allowed_product_type( 'variable-subscription' ) ) :
?>
<div class="row form-group-row pricing variable_subscription_trial show_if_variable-subscription variable_subscription_trial_sign_up hide_if_variable"> 
	<div class="col-md-6">
		<div class="form-group">
			<label class="control-label col-md-12" for="variable_subscription_sign_up_fee[<?php echo esc_attr( $loop ); ?>]"><?php printf( esc_html__( 'Sign-up fee (%s)', 'woocommerce-subscriptions' ), esc_html( get_woocommerce_currency_symbol() ) ); ?></label>
			<div class="col-sm-12">
				<input type="text" name="variable_subscription_sign_up_fee[<?php echo esc_attr( $loop ); ?>]" class="wc_input_price wc_input_subscription_intial_price form-control" placeholder="<?php echo esc_attr_x( 'e.g. 9.90', 'example price', 'woocommerce-subscriptions' ); ?>" value="<?php echo esc_attr( wc_format_localized_price( WC_Subscriptions_Product::get_sign_up_fee( $variation_product ) ) ); ?>" />
				<label for="_subscription_period_interval" class="wcs_hidden_label"><?php esc_html_e( 'Subscription interval', 'woocommerce-subscriptions' ); ?></label>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label class="control-label col-md-12" for="variable_subscription_trial_length[<?php echo esc_attr( $loop ); ?>]">
				<?php esc_html_e( 'Free trial', 'woocommerce-subscriptions' ); ?>  
				<span class="img_tip" data-desc="<?php esc_attr_e( sprintf( _x( 'An optional period of time to wait before charging the first recurring payment. Any sign up fee will still be charged at the outset of the subscription. %s', 'Trial period dropdown\'s description in pricing fields', 'woocommerce-subscriptions' ), WC_Subscriptions_Admin::get_trial_period_validation_message() ) ); ?>"></span>
			</label>
			<div class="col-sm-6">
				<input type="text" name="variable_subscription_trial_length[<?php echo esc_attr( $loop ); ?>]" class="wc_input_subscription_trial_length form-control" value="<?php echo esc_attr( WC_Subscriptions_Product::get_trial_length( $variation_product ) ); ?>" /> 
			</div>
			<div class="col-sm-6"> 
				<label for="variable_subscription_period[<?php echo esc_attr( $loop ); ?>]" class="wcs_hidden_label"><?php esc_html_e( 'Subscription Trial Period', 'woocommerce-subscriptions' ); ?>
				</label>
				<select name="variable_subscription_trial_period[<?php echo esc_attr( $loop ); ?>]" class="wc_input_subscription_trial_period form-control" >
					<?php foreach ( wcs_get_available_time_periods() as $key => $value ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, WC_Subscriptions_Product::get_trial_period( $variation_product ) ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
			</div> 
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label class="control-label col-md-12" for="variable_subscription_price[<?php echo esc_attr( $loop ); ?>]">
			<?php
				// translators: placeholder is a currency symbol / code
				printf( esc_html__( 'Subscription price (%s)', 'woocommerce-subscriptions' ), esc_html( get_woocommerce_currency_symbol() ) );
				?>
			</label>
			<div class="col-sm-4">
				<input type="text" name="variable_subscription_price[<?php echo esc_attr( $loop ); ?>]" class="wc_input_price wc_input_subscription_price form-control" placeholder="<?php echo esc_attr_x( 'e.g. 9.90', 'example price', 'woocommerce-subscriptions' ); ?>" value="<?php echo esc_attr( wc_format_localized_price( WC_Subscriptions_Product::get_regular_price( $variation_product ) ) ); ?>" />
			</div>
			<div class="col-sm-4">
				<label class="wcs_hidden_label" for="variable_subscription_period_interval[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Billing interval:', 'woocommerce-subscriptions' ); ?></label>
				<select name="variable_subscription_period_interval[<?php echo esc_attr( $loop ); ?>]" class="wc_input_subscription_period_interval form-control">
					<?php foreach ( wcs_get_subscription_period_interval_strings() as $key => $value ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, WC_Subscriptions_Product::get_interval( $variation_product ) ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="col-sm-4">
				<label class="wcs_hidden_label" for="variable_subscription_period[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Billing Period:', 'woocommerce-subscriptions' ); ?></label>
				<select name="variable_subscription_period[<?php echo esc_attr( $loop ); ?>]" class="wc_input_subscription_period form-control">
					<?php foreach ( wcs_get_subscription_period_strings() as $key => $value ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $billing_period ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="form-group">
			<label class="control-label col-md-12" for="variable_subscription_length[<?php echo esc_attr( $loop ); ?>]"><?php esc_html_e( 'Expire after', 'woocommerce-subscriptions' ); ?>
				<span class="img_tip" data-desc="<?php esc_attr_e( sprintf( _x( 'Automatically expire the subscription after this length of time. This length is in addition to any free trial or amount of time provided before a synchronised first renewal date.', 'Subscription Length dropdown\'s description in pricing fields', 'woocommerce-subscriptions' ) ) ); ?>"></span> 
			</label>
			<div class="col-sm-12">
				<select name="variable_subscription_length[<?php echo esc_attr( $loop ); ?>]" class="wc_input_subscription_length form-control">
					<?php foreach ( wcs_get_subscription_ranges( $billing_period ) as $key => $value ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, WC_Subscriptions_Product::get_length( $variation_product ) ); ?>> <?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>
</div>
<?php endif;
