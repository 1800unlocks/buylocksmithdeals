<?php

/**
 * WCMp Advanced Frontend Manager
 *
 * YITH WooCommerce Auctions (FREE) Support
 *
 * @author WC Marketplace
 * @package WCMp_AFM/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Yith_Auction_Integration {

    protected $id = null;
    protected $tabs = array();
    protected $auctionable_product = null;
    protected $plugin = 'yith-auction';

    public function __construct() {
        $this->tabs = $this->set_additional_tabs();
        
        //load additional libs required for auction product type @screen ADD PRODUCT
        add_action( 'afm_add_product_load_script_lib', array( $this, 'load_required_script_lib' ), 1, 3 );
        
        add_filter( 'wcmp_product_data_tabs', array( $this, 'auction_additional_tabs' ) );
        add_action( 'wcmp_product_tabs_content', array( $this, 'auction_additional_tabs_content' ) );
        
        // Auction Product Meta Data Save
        // below WC version 3.0
        add_action( 'wcmp_process_product_meta_auction', array( $this, 'save_auction_meta' ), 10, 2 );
    }

    //this will be called from the main Integration class after WCMp_AFM_Add_Product_Endpoint class constructor executed
    public function set_props( $id ) {
        $this->id = $id;

        //after setting id get the auctionable product
        $this->auctionable_product = wc_get_product( $this->id );
    }

    protected function set_additional_tabs() {
        global $WCMp;
        $auction_tabs = array();

        $auction_tabs['yith_auction'] = array(
            'p_type'   => 'auction',
            'label'    => __( 'Auction', 'yith-auctions-for-woocommerce' ),
            'target'   => 'yith_auction_product_data',
            'class'    => array( 'show_if_auction' ),
            'priority' => '77',
        );
        return $auction_tabs;
    }
    
    public function load_required_script_lib( $frontend_script_path, $lib_path, $suffix ) {
        wp_enqueue_style( 'wcmp-afm-auction-css', $lib_path . 'datetimepicker/timepicker.min.css', '1.6.3' );
        wp_enqueue_script( 'wcmp-afm-auction-js', $lib_path . 'datetimepicker/timepicker.min.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '1.6.3', true );
    }

    public function auction_additional_tabs( $product_tabs ) {
        if ( isset( $product_tabs['inventory']['class'] ) ) {
            $product_tabs['inventory']['class'][] = 'show_if_auction';
        }
        return array_merge( $product_tabs, $this->tabs );
    }

    public function auction_additional_tabs_content() {
        foreach ( $this->tabs as $key => $tab ) {
            afm()->template->get_template( 'products/yith-auction/html-product-data-' . str_replace( '_', '-', $key ) . '.php', array( 'id' => $this->id, 'tab' => $tab['target'], 'self' => $this, 'auctionable_product' => $this->auctionable_product ) );
        }
        return;
    }

    public function save_auction_meta( $product_id, $data ) {
        if ( 'auction' !== sanitize_title( stripslashes( $data['product-type'] ) ) ) {
            return;
        }

        $auction_product = wc_get_product( $product_id );
        
        if ( isset( $_POST['_yith_auction_for'] ) ) {
            $my_date = $_POST['_yith_auction_for'];
            $gmt_date = get_gmt_from_date( $my_date );
            yit_save_prop( $auction_product, '_yith_auction_for', strtotime( $gmt_date ), true );
        }
        if ( isset( $_POST['_yith_auction_to'] ) ) {
            $my_date = $_POST['_yith_auction_to'];
            $gmt_date = get_gmt_from_date( $my_date );
            yit_save_prop( $auction_product, '_yith_auction_to', strtotime( $gmt_date ), true );
        }
        // Prevent issue with stock managing
        yit_save_prop( $auction_product, 'manage_stock', function_exists( 'wc_string_to_bool' ) ? wc_string_to_bool( 'yes' ) : 'yes' );
        yit_update_product_stock( $auction_product, 1, 'set' );

        //Prevent issues with orderby in shop loop
        $bids = YITH_Auctions()->bids;
        $exist_auctions = $bids->get_max_bid( $post_id );
        if ( ! $exist_auctions ) {
            yit_save_prop( $auction_product, '_yith_auction_start_price', 0 );
            yit_save_prop( $auction_product, '_price', 0 );
        }
    }

}
