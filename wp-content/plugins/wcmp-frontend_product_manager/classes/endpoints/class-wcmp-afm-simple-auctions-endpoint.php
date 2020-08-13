<?php
/**
 * WCMp_AFM_Simple_Auctions_Endpoint setup
 *
 * @package  WCMp_AFM/classes/endpoints
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Simple_Auctions_Endpoint {

    public function output() {
        $current_vendor_id = afm()->vendor_id;
        
        if ( ! $current_vendor_id || ! current_vendor_can( 'manage_simple_auctions' ) ) {
            ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <?php esc_html_e( 'You do not have permission to view this content. Please contact site administrator.', WCMp_AFM_TEXT_DOMAIN ); ?>
                </div>
            </div>
            <?php
            return;
        }
        
        $filters = array();
        $auctionable_products = WCMp_AFM_Simple_Auction_Integration::get_vendor_auctionable_products( 'publish' );
        
        $auction_params = array(
            'ajax_url'               => admin_url( 'admin-ajax.php' ),
            'empty_table'            => esc_js( __( 'No auctions found!', WCMp_AFM_TEXT_DOMAIN ) ),
            'processing'             => esc_js( __( 'Processing...', WCMp_AFM_TEXT_DOMAIN ) ),
            'info'                   => esc_js( __( 'Showing _START_ to _END_ of _TOTAL_ auctions', WCMp_AFM_TEXT_DOMAIN ) ),
            'info_empty'             => esc_js( __( 'Showing 0 to 0 of 0 auctions', WCMp_AFM_TEXT_DOMAIN ) ),
            'length_menu'            => esc_js( __( 'Number of rows _MENU_', WCMp_AFM_TEXT_DOMAIN ) ),
            'zero_records'           => esc_js( __( 'No matching auctions found', WCMp_AFM_TEXT_DOMAIN ) ),
            'next'                   => esc_js( __( 'Next', WCMp_AFM_TEXT_DOMAIN ) ),
            'previous'               => esc_js( __( 'Previous', WCMp_AFM_TEXT_DOMAIN ) ),
            'reload'                 => esc_js( __( 'Reload', WCMp_AFM_TEXT_DOMAIN ) ),
            'auction_filter_default' => esc_js( __( 'Auction filter By', WCMp_AFM_TEXT_DOMAIN ) ),
            'auction_filter_options' => json_encode( WCMp_AFM_Simple_Auction_Integration::auction_status_filter_options() ),
        );
        wp_localize_script( 'afm-auctions-js', 'auctions_params', $auction_params );
        wp_enqueue_script( 'afm-auctions-js' );

        afm()->template->get_template( 'products/simple-auction/html-endpoint-auctions.php' );
    }

}
