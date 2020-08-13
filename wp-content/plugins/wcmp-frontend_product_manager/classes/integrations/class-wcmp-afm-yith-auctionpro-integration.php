<?php

/**
 * WCMp Advanced Frontend Manager
 *
 * YITH WooCommerce Auctions Support
 *
 * @author WC Marketplace
 * @package WCMp_AFM/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Yith_Auctionpro_Integration {

    protected $id = null;
    protected $tabs = array();
    protected $auctionable_product = null;
    protected $plugin = 'yith-auctionpro';
    protected $auction_endpoints = array();

    public function __construct() {
        $this->tabs = $this->set_additional_tabs();
        $this->auction_endpoints = $this->all_auction_endpoints();
        //filter for adding wcmp endpoint query vars
        add_filter( 'wcmp_endpoints_query_vars', array( $this, 'auction_endpoints_query_vars' ) );
        add_filter( 'wcmp_vendor_dashboard_nav', array( $this, 'auction_dashboard_navs' ) );
        $this->call_endpoint_contents();
        
        //dequeue scripts on auction endpoints
        add_action( 'afm_endpoints_dequeue_wcmp_scripts', array( $this, 'dequeue_wcmp_scripts' ), 10, 2 );
        //enqueue required scripts for endpoints added
        add_action( 'afm_enqueue_dashboard_scripts', array( $this, 'auction_endpoint_scripts' ), 10, 4 );
        
        //load additional libs required for auction product type @screen ADD PRODUCT
        add_action( 'afm_add_product_load_script_lib', array( $this, 'load_required_script_lib' ), 1, 3 );
        add_filter( 'wcmp_advance_product_script_params', array( $this, 'add_localize_params' ) );
        
        // Auctionable Product Additional Tabs
        add_filter( 'wcmp_product_data_tabs', array( $this, 'auction_additional_tabs' ) );
        add_action( 'wcmp_product_tabs_content', array( $this, 'auction_additional_tabs_content' ) );

        add_action( 'wcmp_afm_before_yith_auction_data', array( $this, 'auction_before_auction_tab_content' ) );
        add_action( 'wcmp_afm_after_yith_auction_data', array( $this, 'auction_after_auction_tab_content' ) );
        add_action( 'wcmp_afm_after_product_excerpt_metabox_panel', array( $this, 'auction_after_product_excerpt_content' ) );
        // Auction Product Meta Data Save
        // below WC version 3.0
        add_action( 'wcmp_process_product_meta_auction', array( $this, 'save_auction_meta' ), 10, 2 );
    }

    //this will be called from the main Integration class after WCMp_AFM_Add_Product_Endpoint class constructor executed
    public function set_props( $id ) {
        $this->id = absint( $id );

        //after setting id get the auctionable product
        $this->auctionable_product = wc_get_product( $this->id );
    }

    /**
     * Return all the `YITH Auction` endpoints added to vendor dashboard
     * 
     * @return array endpoints 
     */
    private function all_auction_endpoints() {
        return apply_filters( "wcmp_afm_{$this->plugin}_endpoint_list", afm()->dependencies->get_allowed_endpoints( $this->plugin ) );
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

    public function auction_endpoints_query_vars( $endpoints ) {
        return afm()->dependencies->plugin_endpoints_query_vars( $endpoints, $this->auction_endpoints );
    }

    public function auction_dashboard_navs( $navs ) {
        //make it a submenu under product manager menu
        return afm()->dependencies->plugin_dashboard_navs( $navs, $this->auction_endpoints, 'vendor-products' );
    }

    public function call_endpoint_contents() {
        //add endpoint content
        foreach ( $this->auction_endpoints as $key => $endpoint ) {
            $cap = ! empty( $endpoint['vendor_can'] ) ? $endpoint['vendor_can'] : '';
            if ( $cap && current_vendor_can( $cap ) ) {
                add_action( 'wcmp_vendor_dashboard_' . $key . '_endpoint', array( $this, 'auction_endpoints_callback' ) );
            }
        }
    }

    public function auction_endpoints_callback() {
        $endpoint_name = 'yith-' . str_replace( array( 'wcmp_vendor_dashboard_', '_endpoint' ), '', current_filter() );
        afm()->endpoints->load_class( $endpoint_name );
        $classname = 'WCMp_AFM_' . ucwords( str_replace( '-', '_', $endpoint_name ), '_' ) . '_Endpoint';
        $endpoint_class = new $classname;
        $endpoint_class->output();
    }

    /**
     * load datetimepicker library required for auction tab
     * @screen ADD PRODUCT
     */
    public function load_required_script_lib( $frontend_script_path, $lib_path, $suffix ) {
        wp_enqueue_style( 'wcmp-afm-auction-css', $lib_path . 'datetimepicker/timepicker.min.css', '1.6.3' );
        wp_enqueue_script( 'wcmp-afm-auction-js', $lib_path . 'datetimepicker/timepicker.min.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), '1.6.3', true );
    }

    public function dequeue_wcmp_scripts( $flag, $endpoint ) {
        if ( $endpoint === 'auctions' ) {
            return true;
        }
        return $flag;
    }

    public function auction_endpoint_scripts( $endpoint, $frontend_script_path, $lib_path, $suffix ) {
        global $WCMp;
        switch ( $endpoint ) {
            case 'auctions':
                if ( current_vendor_can( 'manage_auctions' ) ) {
                    $WCMp->library->load_dataTable_lib();
                    wp_register_script( 'afm-auctions-js', $frontend_script_path . 'yith-auctions.js', array( 'jquery', 'wcmp-datatable-script', 'wcmp-datatable-bs-script' ), afm()->version, true );
                }
                break;
        }
    }

    public function add_localize_params( $params ) {
        $new_params = array(
            'reschedule_auction_nonce'  => wp_create_nonce( 'reschedule-auction' ),
            'resend_winner_email_nonce' => wp_create_nonce( 'resend-winner-email' ),
            'i18n_delete_bid'           => esc_js( __( 'Are you sure you want to delete the customer\'s bid?', 'yith-auctions-for-woocommerce' ) ),
        );
        return array_merge( $params, $new_params );
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

    public function auction_before_auction_tab_content() {
        foreach ( $this->tabs as $key => $tab ) {
            afm()->template->get_template( 'products/yith-auction/html-product-before-auction-data.php', array( 'id' => $this->id, 'tab' => $tab['target'], 'self' => $this, 'auctionable_product' => $this->auctionable_product ) );
        }
        return;
    }

    public function auction_after_auction_tab_content() {
        foreach ( $this->tabs as $key => $tab ) {
            afm()->template->get_template( 'products/yith-auction/html-product-after-auction-data.php', array( 'id' => $this->id, 'tab' => $tab['target'], 'self' => $this, 'auctionable_product' => $this->auctionable_product ) );
        }
        return;
    }

    public function auction_after_product_excerpt_content() {
        if ( wcmp_is_allowed_product_type( 'auction' ) && WC_Product_Factory::get_product_type( $this->id ) == 'auction' ) {
            afm()->template->get_template( 'products/yith-auction/html-product-bid-info.php', array( 'id' => $this->id, 'self' => $this, 'auctionable_product' => $this->auctionable_product ) );
        }
    }

    public function save_auction_meta( $product_id, $data ) {
        if ( 'auction' !== sanitize_title( stripslashes( $data['product-type'] ) ) ) {
            return;
        }

        $auction_product = wc_get_product( $product_id );

        if ( isset( $_POST['_yith_auction_for'] ) ) {
            $my_date = $_POST['_yith_auction_for'];
            $gmt_date = get_gmt_from_date( $my_date );
            yit_save_prop( $auction_product, '_yith_auction_for', strtotime( $gmt_date ) );
        }
        if ( isset( $_POST['_yith_auction_to'] ) ) {
            $my_date = $_POST['_yith_auction_to'];
            $gmt_date = get_gmt_from_date( $my_date );
            yit_save_prop( $auction_product, '_yith_auction_to', strtotime( $gmt_date ) );
        }
        // Prevent issue with stock managing
        yit_save_prop( $auction_product, 'manage_stock', function_exists( 'wc_string_to_bool' ) ? wc_string_to_bool( 'yes' ) : 'yes'  );
        yit_update_product_stock( $auction_product, 1, 'set' );

        //Prevent issues with orderby in shop loop
        $bids = YITH_Auctions()->bids;
        $exist_auctions = $bids->get_max_bid( $product_id );
        if ( ! $exist_auctions ) {
            yit_save_prop( $auction_product, '_yith_auction_start_price', 0 );
            yit_save_prop( $auction_product, '_price', 0 );
        }
    }

    /**
     * Compatibility get product tax class options
     *
     * @since    3.0.0
     */
    public function get_product_tax_class_options() {

        if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
            return wc_get_product_tax_class_options();
        } else {
            $tax_classes = WC_Tax::get_tax_classes();
            $tax_class_options = array();
            $tax_class_options[''] = __( 'Standard', 'woocommerce' );

            if ( ! empty( $tax_classes ) ) {
                foreach ( $tax_classes as $class ) {
                    $tax_class_options[sanitize_title( $class )] = $class;
                }
            }
            return $tax_class_options;
        }
    }

    /**
     * Get current vendor auction products.
     *
     * @param string post status
     * @param array query arguments
     * @param string One of OBJECT, or ARRAY_N
     * @return array of object or numeric array
     */
    public static function get_vendor_auctionable_products( $post_status = 'any', $args = array(), $output = OBJECT ) {
        global $WCMp;
        $vendor_id = afm()->vendor_id;
        $auctionable_products = array();
        if ( $vendor_id ) {
            $vendor = get_wcmp_vendor( $vendor_id );
            if ( $vendor ) {
                $defaults = array(
                    'post_status' => $post_status,
                    'tax_query'   => array(
                        'relation' => 'AND',
                        array(
                            'taxonomy' => $WCMp->taxonomy->taxonomy_name,
                            'field'    => 'term_id',
                            'terms'    => absint( $vendor->term_id )
                        ),
                        array(
                            'taxonomy' => 'product_type',
                            'field'    => 'slug',
                            'terms'    => 'auction',
                            'operator' => 'IN',
                        )
                    )
                );
                $r = wp_parse_args( $args, $defaults );
                $vendor_products = $vendor->get_products( $r );

                foreach ( $vendor_products as $vendor_product ) {
                    $product_type = WC_Product_Factory::get_product_type( $vendor_product->ID );
                    if ( $product_type === 'auction' ) {
                        $auctionable_products[] = ( $output == OBJECT ) ? wc_get_product( $vendor_product->ID ) : $vendor_product->ID;
                    }
                }
            }
        }
        return $auctionable_products;
    }

    /**
     * Auction list page filter options
     * 
     * @return string HTML
     */
    public static function auction_status_filter_options() {
        $output = array(
            'non-started' => __( 'Not Started', 'yith-auctions-for-woocommerce' ),
            'started'     => __( 'Started', 'yith-auctions-for-woocommerce' ),
            'finished'    => __( 'Finished', 'yith-auctions-for-woocommerce' ),
        );
        return apply_filters( 'wcmp_afm_yith_auction_page_filters', $output );
    }

    public static function filter_by_auction_status( $auction_type ) {

        switch ( $auction_type ) {
            case 'non-started' :
                return array(
                    array(
                        'key'     => '_yith_auction_for',
                        'value'   => strtotime( 'now' ),
                        'compare' => '>'
                    ) );
            //no break needed as we are returning the array directly
            case 'started' :
                return array(
                    'relation' => 'AND',
                    array(
                        'key'     => '_yith_auction_for',
                        'value'   => strtotime( 'now' ),
                        'compare' => '<'
                    ),
                    array(
                        'key'     => '_yith_auction_to',
                        'value'   => strtotime( 'now' ),
                        'compare' => '>'
                    )
                );
            case 'finished' :
                return array(
                    array(
                        'key'     => '_yith_auction_to',
                        'value'   => strtotime( 'now' ),
                        'compare' => '<'
                    )
                );
            default: return array();
        }
    }
}
