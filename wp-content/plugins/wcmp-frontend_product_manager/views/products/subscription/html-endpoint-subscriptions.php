<?php

/**
 * Vendor subscriptions template
 *
 * Used by WCMp_AFM_Subscriptions_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/subscription/html-endpoint-subscriptions.php.
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

global $WCMp;
do_action( 'before_wcmp_vendor_dashboard_subscriptions_table' );
?>
<div class="col-md-12">
    <div class="panel panel-default panel-pading">
        <?php
        $statuses = array_unique( array_merge( array( 'all' => __( 'All', WCMp_AFM_TEXT_DOMAIN) ), wcs_get_subscription_statuses( ) ) );
        $current_status = ! empty( $_GET['post_status'] ) ? wc_clean( $_GET['post_status'] ) : 'all';
        echo '<ul class="subscription_status by_status nav nav-pills">';
        foreach ( $statuses as $key => $label ) {
			$count_pros = count( WCMp_AFM_Subscription_Integration::get_vendor_subscription_array( array( 'post_status' => $key ) ) );
            if ( $count_pros ) {
                echo '<li><a href="' . add_query_arg( array( 'post_status' => sanitize_title( $key ) ), wcmp_get_vendor_dashboard_endpoint_url( 'subscriptions' ) ) . '" class="' . ( $current_status == $key ? 'current' : '' ) . '">' . $label . ' ( ' . $count_pros . ' ) </a></li>';
            }
        }
        echo '</ul><br/>';
        ?>
        <table id="subscriptions_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?php _e( 'Subscription', 'woocommerce-subscriptions' ); ?></th>
                    <th><?php _e( 'Status', 'woocommerce-subscriptions' ); ?></th>
                    <th><?php _e( 'Items', 'woocommerce-subscriptions' ); ?></th>
                    <th><?php _e( 'Total', 'woocommerce-subscriptions' ); ?></th>
                    <th><?php _e( 'Start Date', 'woocommerce-subscriptions' ); ?></th>
                    <th><?php _e( 'Trial End', 'woocommerce-subscriptions' ); ?></th>
                    <th><?php _e( 'Next Payment', 'woocommerce-subscriptions' ); ?></th>
                    <th><?php _e( 'Last Order Date', 'woocommerce-subscriptions' ); ?></th>
                    <th><?php _e( 'End Date', 'woocommerce-subscriptions' ); ?></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<?php
do_action( 'after_wcmp_vendor_dashboard_subscriptions_table' );
