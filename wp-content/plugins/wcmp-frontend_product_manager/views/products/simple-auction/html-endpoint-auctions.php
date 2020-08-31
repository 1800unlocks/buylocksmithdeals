<?php

/**
 * Product Manager->Auctions endpoint content
 *
 * Used by WCMp_AFM_Simple_Auctions_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/simple-auction/html-endpoint-auctions.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/simple-auction
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;

global $WCMp;
do_action( 'before_wcmp_vendor_dashboard_simple_auctions_table' );
?>
<div class="col-md-12">
    <div class="panel panel-default panel-pading">
        <table id="auctions_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?php _e( 'Name', 'wcmp-afm' ); ?></th>
                    <th><?php _e( 'Start Date', 'wcmp-afm' ); ?></th>
                    <th><?php _e( 'End Date', 'wcmp-afm' ); ?></th>
                    <th><?php _e( 'Auction status', 'wcmp-afm' ); ?></th>
                    <th><?php _e( 'Max bid', 'wcmp-afm' ); ?></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<?php
do_action( 'after_wcmp_vendor_dashboard_simple_auctions_table' );
