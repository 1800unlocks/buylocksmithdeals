<?php

/**
 * Single subscription template
 *
 * Used by WCMp_AFM_Subscriptions_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/subscription/html-endpoint-single-subscription.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/subscription
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="col-md-12">
    <div class="icon-header">
        <span><i class="wcmp-font ico-order-details-icon"></i></span>
        <h2><?php printf( esc_html_x( 'Subscription #%s details', 'edit subscription header', 'woocommerce-subscriptions' ), esc_html( $subscription->get_order_number() ) ); ?></h2>
        <h3>
            <?php 
            $parent_order = $subscription->get_parent();
			if ( $parent_order ) {
				$parent_order_status = $parent_order->get_status();
				$parent_order_link = sprintf( esc_html__( '#%1$s', 'woocommerce-subscriptions' ), esc_html( $parent_order->get_order_number() ) );
				if($parent_order_status == 'completed' || $parent_order_status == 'processing') {
					$parent_order_link = '<a href="' . wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders' ), $parent_order->get_order_number() ) . '">' . $parent_order_link . '</a>';
				}
				echo esc_html__( 'Parent order:', 'woocommerce-subscriptions' ) . $parent_order_link;
			}
			echo " " . esc_html__( 'Status', 'woocommerce-subscriptions' ) . " : " .  wcs_get_subscription_status_name( $subscription->get_status() );
            ?>
        </h3>
    </div>
    <div class="row">
        <div class="col-md-8 subscription-details-wrapper">
            <div class="panel panel-default pannel-outer-heading">
                <div class="panel-heading">
                    <h3><?php esc_html_e( 'Customer Information', 'wcmp-afm' ); ?></h3>
                </div>
                <div class="panel-body panel-content-padding form-horizontal" id="subscription_details">
                    <table class="table deep subscription-customer-details">
                        <tr>
                            <th><?php esc_html_e( 'Name:', 'wcmp-afm' ); ?></th>
                            <td><span class="customer_name"><?php esc_html_e( trim( $subscription->get_billing_first_name() . ' ' . $subscription->get_billing_last_name() ) ); ?></span></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Email address:', 'wcmp-afm' ); ?></th>
                            <td><span class="customer_email"><?php esc_html_e( $subscription->get_billing_email() ); ?></span></td>
                        </tr>
                        <tr>
                            <th><?php esc_html_e( 'Phone:', 'wcmp-afm' ); ?></th>
                            <td><span class="customer_phone"><?php esc_html_e( $subscription->get_billing_phone() ); ?></span></td>
                        </tr>
                    </table>
                </div>
                
				<div class="panel panel-default pannel-outer-heading">
					<div class="panel-heading">
						<h3><?php esc_html_e( 'Customer Address', 'wcmp-afm' ); ?></h3>
					</div>
					<div class="panel-body panel-content-padding form-horizontal" id="subscription_details">
						<div class="col-md-4">
							 <div class="panel-heading">
								<h3><?php esc_html_e( 'Billing Address', 'woocommerce-subscriptions' ); ?></h3>
							</div>
							<?php
								if ( $subscription->get_formatted_billing_address() ) {
									echo '<p>' . wp_kses( $subscription->get_formatted_billing_address(), array( 'br' => array() ) ) . '</p>';
								} else {
									echo '<p class="none_set">' . esc_html__( 'No billing address set.', 'woocommerce-subscriptions' ) . '</p>';
								}
							?>
						</div>
						<div class="col-md-4">
							<div class="panel-heading">
								<h3><?php esc_html_e( 'Shipping Address', 'woocommerce-subscriptions' ); ?></h3>
							</div>
							<?php
								if ( $subscription->get_formatted_shipping_address() ) {
									echo '<p>' . wp_kses( $subscription->get_formatted_shipping_address(), array( 'br' => array() ) ) . '</p>';
								} else {
									echo '<p class="none_set">' . esc_html__( 'No shipping address set.', 'woocommerce-subscriptions' ) . '</p>';
								}
							?>
						</div>
					</div>
				</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default pannel-outer-heading">
                <div class="panel-heading">
                    <h3><?php esc_html_e( 'Schedule', 'woocommerce-subscriptions' ); ?></h3>
                </div>
                <div class="panel-body panel-content-padding form-horizontal" id="customer_details">
                    <table class="table deep subscription-customer-details">
						<tr class="view">
							<th><?php esc_html_e( 'Payment:', 'woocommerce-subscriptions' ); ?></th>
							<td><?php echo esc_html( wcs_get_subscription_period_interval_strings( $subscription->get_billing_interval() ) ) . ' ' . esc_html( wcs_get_subscription_period_strings( 1, $subscription->get_billing_period() ) ); ?></td>
						</tr>
						<tr class="view">
							<th><?php esc_html_e( 'Start Date:', 'wcmp-afm' ); ?></th>
							<td><?php echo esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'date_created', 'site' ) ) ); ?></td>
						</tr>
						<tr class="view">
							<th><?php esc_html_e( 'Trial End:', 'wcmp-afm' ); ?></th>
							<td><?php $date_type = wcs_normalise_date_type_key( 'trial_end_date' ); echo $subscription->get_date_to_display( $date_type ); ?></td>
						</tr>
						<tr class="view">
							<th><?php esc_html_e( 'Next Payment:', 'wcmp-afm' ); ?></th>
							<td><?php $date_type = wcs_normalise_date_type_key( 'next_payment_date' ); echo $subscription->get_date_to_display( $date_type ); ?></td>
						</tr>
						<tr class="view">
							<th><?php esc_html_e( 'End Date:', 'wcmp-afm' ); ?></th>
							<td><?php $end_date = ( 0 < $subscription->get_time( 'end' ) ) ? date_i18n( wc_date_format(), $subscription->get_time( 'end', 'site' ) ) : _x( 'When Cancelled', 'Used as end date for an indefinite subscription', 'woocommerce-subscriptions' );
								echo esc_html( $end_date ); ?></td>
						</tr>
						<tr class="view">
							<th><?php esc_html_e( 'Price:', 'wcmp-afm' ); ?></th>
							<td><?php echo $subscription->get_formatted_order_total(); ?></td>
						</tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-8 subscription-details-wrapper">
        </div> 
    </div>
</div>
