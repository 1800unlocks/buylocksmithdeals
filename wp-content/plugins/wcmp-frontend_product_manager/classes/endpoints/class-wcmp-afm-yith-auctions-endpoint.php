<?php
/**
 * WCMp_AFM_Yith_Auctions_Endpoint setup
 *
 * @package  WCMp_AFM/classes/endpoints
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Yith_Auctions_Endpoint {

    public function output() {
        $current_vendor_id = afm()->vendor_id;

        if ( ! $current_vendor_id || ! current_vendor_can( 'manage_auctions' ) ) {
            ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <?php esc_html_e( 'You do not have permission to view this content. Please contact site administrator.', 'wcmp-afm' ); ?>
                </div>
            </div>
            <?php
            return;
        }
        
        $filters = array();
        $auctionable_products = WCMp_AFM_Yith_Auctionpro_Integration::get_vendor_auctionable_products( 'publish' );
        $instance = YITH_Auctions()->bids;

        $auction_params = array(
            'ajax_url'               => admin_url( 'admin-ajax.php' ),
            'empty_table'            => esc_js( __( 'No auctions found!', 'wcmp-afm' ) ),
            'processing'             => esc_js( __( 'Processing...', 'wcmp-afm' ) ),
            'info'                   => esc_js( __( 'Showing _START_ to _END_ of _TOTAL_ auctions', 'wcmp-afm' ) ),
            'info_empty'             => esc_js( __( 'Showing 0 to 0 of 0 auctions', 'wcmp-afm' ) ),
            'length_menu'            => esc_js( __( 'Number of rows _MENU_', 'wcmp-afm' ) ),
            'zero_records'           => esc_js( __( 'No matching auctions found', 'wcmp-afm' ) ),
            'next'                   => esc_js( __( 'Next', 'wcmp-afm' ) ),
            'previous'               => esc_js( __( 'Previous', 'wcmp-afm' ) ),
            'reload'                 => esc_js( __( 'Reload', 'wcmp-afm' ) ),
            'auction_filter_default' => esc_js( __( 'Show all auction statuses', 'yith-auctions-for-woocommerce' ) ),
            'auction_filter_options' => json_encode( WCMp_AFM_Yith_Auctionpro_Integration::auction_status_filter_options() ),
        );
        wp_localize_script( 'afm-auctions-js', 'auctions_params', $auction_params );
        wp_enqueue_script( 'afm-auctions-js' );

        afm()->template->get_template( 'products/yith-auction/html-endpoint-auctions.php' );
    }

}
