<?php
/**
 * Vendor dashboard Rentals->Quote list content
 *
 * Used by WCMp_AFM_Request_Quote_Endpoint->output()
 *
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/rental/html-endpoint-request-quote.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/rental
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;

global $WCMp;
do_action('before_wcmp_vendor_dashboard_quote_list_table');
?>
<div class="col-md-12">
    <div class="panel panel-default panel-pading">
        <table id="rental_quotes_table" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th><?php _e('Quote', 'wcmp-afm'); ?></th>
                    <th><?php  _e('Status', 'wcmp-afm'); ?></th>
                    <th><?php _e('Product', 'wcmp-afm'); ?></th>
                    <th><?php _e('Email', 'wcmp-afm'); ?></th>
                    <th><?php _e('Date', 'wcmp-afm'); ?></th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<?php do_action('after_wcmp_vendor_dashboard_quote_list_table');
