<?php

/**
 * WCMp Advanced Frontend Manager
 *
 * WooCommerce Simple Auction Support
 *
 * @author WC Marketplace
 * @package WCMp_AFM/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Simple_Auction_Integration {

    protected $id = null;
    protected $tabs = array();
    protected $auctionable_product = null;
    protected $plugin = 'simple-auction';
    protected $auction_endpoints = array();
    //pair of field name and meta values
    public $form_fields = array();

    public function __construct() {
        $this->tabs = $this->set_additional_tabs();
        $this->auction_endpoints = $this->all_auction_endpoints(); //print_r( $this->auction_endpoints ); $vendor_role = get_role( 'dc_vendor' ); $capabilities = isset( $vendor_role->capabilities ) ? $vendor_role->capabilities : array(); print_r( $capabilities );
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

        add_action( 'wcmp_product_type_options', array( $this, 'auction_additional_product_type_options' ) );

        add_filter( 'general_tab_tax_section', array( $this, 'include_auction_type' ) );
        add_filter( 'inventory_tab_stock_status_section_invisibility', array( $this, 'include_auction_type' ) );

        add_filter( 'inventory_tab_manage_stock_class_list', array( $this, 'include_hide_auction_class' ) );
        add_filter( 'inventory_tab_stock_fields_class_list', array( $this, 'include_hide_auction_class' ) );

        add_action( 'wcmp_afm_after_product_excerpt_metabox_panel', array( $this, 'auction_after_product_excerpt_content' ) );
        // Auction Product Meta Data Save
        // below WC version 3.0
        add_action( 'wcmp_process_product_meta_auction', array( $this, 'save_auction_meta' ), 10, 2 );

        // Remove simple auction pre_get_posts modifications to exclude auctions from product query
        add_filter( 'wcmp_init', array( $this, 'include_auctions_woocommerce_product_query', 99 ) );
    }

    //this will be called from the main Integration class after WCMp_AFM_Add_Product_Endpoint class constructor executed
    public function set_props( $id ) {
        $this->id = absint( $id );

        //after setting id get the auctionable product
        $this->auctionable_product = wc_get_product( $this->id );
        //populate all fields and meta values
        $this->form_fields = $this->set_form_fields();
    }

    /**
     * Return all the `Simple Auction` endpoints added to vendor dashboard
     * 
     * @return array endpoints 
     */
    private function all_auction_endpoints() {
        return apply_filters( "wcmp_afm_{$this->plugin}_endpoint_list", afm()->dependencies->get_allowed_endpoints( $this->plugin ) );
    }

    public function set_form_fields() {
        $temp = array();
        $current_vendor_id = afm()->vendor_id;
        if ( $current_vendor_id ) {
            foreach ( $this->tabs as $key => $tab ) {
                $temp[$key] = array();
                if ( ! empty( $tab['fields'] ) && is_array( $tab['fields'] ) ) {
                    $fields = $tab['fields'];
                    $sub_arr = array();
                    foreach ( $fields as $field => $default ) {
                        $field_name = substr( $field, 1 );
                        $sub_arr[$field_name] = get_post_meta( $this->id, $field, true );
                        if ( empty( $sub_arr[$field_name] ) && $default !== '' ) {
                            $sub_arr[$field_name] = $default;
                        }
                    }
                    $temp[$key] = array_merge( $temp[$key], $sub_arr );
                }
            }
        }
        return (array) $temp;
    }

    protected function set_additional_tabs() {
        $auction_tabs = array();

        $auction_tabs['simple_auction'] = array(
            'p_type'   => 'auction',
            'label'    => __( 'Auction', 'wc_simple_auctions' ),
            'target'   => 'simple_auction_product_data',
            'class'    => array( 'show_if_auction' ),
            'priority' => '77',
            'fields'   => array(
                '_auction_item_condition'    => '',
                '_auction_type'              => '',
                '_auction_proxy'             => 'no',
                '_auction_sealed'            => 'no',
                '_auction_start_price'       => '',
                '_auction_bid_increment'     => '',
                '_auction_reserved_price'    => '',
                '_regular_price'             => '',
                '_auction_dates_from'        => '',
                '_auction_dates_to'          => '',
                '_relist_auction_dates_from' => '',
                '_relist_auction_dates_to'   => '',
            ),
        );
        $auction_tabs['automatic_relist'] = array(
            'p_type'   => 'auction',
            'label'    => __( 'Automatic relist', WCMp_AFM_TEXT_DOMAIN ),
            'target'   => 'automatic_relist_auction_product_data',
            'class'    => array( 'show_if_auction' ),
            'priority' => '78',
            'fields'   => array(
                '_auction_automatic_relist'     => 'no',
                '_auction_relist_fail_time'     => '',
                '_auction_relist_not_paid_time' => '',
                '_auction_relist_duration'      => '',
            ),
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
        $endpoint_name = 'simple-' . str_replace( array( 'wcmp_vendor_dashboard_', '_endpoint' ), '', current_filter() );
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
                if ( current_vendor_can( 'manage_simple_auctions' ) ) {
                    $WCMp->library->load_dataTable_lib();
                    wp_register_script( 'afm-auctions-js', $frontend_script_path . 'simple-auctions.js', array( 'jquery', 'wcmp-datatable-script', 'wcmp-datatable-bs-script' ), afm()->version, true );
                }
                break;
        }
    }

    public function add_localize_params( $params ) {
        $new_params = array(
            'SA_nonce'            => wp_create_nonce( 'SAajax-nonce' ),
            'i18n_delete_bid'     => esc_js( __( 'Are you sure you want to delete the customer\'s bid?', WCMp_AFM_TEXT_DOMAIN ) ),
            'i18n_remove_reserve' => esc_js( __( 'Are you sure you want to remove reserve price?', WCMp_AFM_TEXT_DOMAIN ) ),
        );
        return array_merge( $params, $new_params );
    }

    public function auction_additional_product_type_options( $options ) {
        $options['virtual']['wrapper_class'] .= ' show_if_auction';
        $options['downloadable']['wrapper_class'] .= ' show_if_auction';
        return $options;
    }

    public function auction_additional_tabs( $product_tabs ) {
        if ( isset( $product_tabs['inventory']['class'] ) ) {
            $product_tabs['inventory']['class'][] = 'show_if_auction';
        }
        return array_merge( $product_tabs, $this->tabs );
    }

    public function auction_additional_tabs_content() {
        foreach ( $this->tabs as $key => $tab ) {
            afm()->template->get_template( 'products/simple-auction/html-product-data-' . str_replace( '_', '-', $key ) . '.php', array( 'id' => $this->id, 'tab' => $tab['target'], 'self' => $this, 'product' => $this->auctionable_product, 'fields' => $this->form_fields[$key] ) );
        }
        return;
    }

    public function include_auction_type( $types ) {
        $types[] = 'auction';
        return $types;
    }

    /**
     * 
     * @param string $classes space separated class list
     * @return string
     */
    public function include_hide_auction_class( $classes ) {
        return $classes . " hide_if_auction";
    }

    public function auction_after_product_excerpt_content() {
        if ( wcmp_is_allowed_product_type( 'auction' ) && WC_Product_Factory::get_product_type( $this->id ) == 'auction' ) {
            afm()->template->get_template( 'products/simple-auction/html-product-bid-info.php', array( 'id' => $this->id, 'self' => $this, 'product' => $this->auctionable_product ) );
        }
    }

    public function save_auction_meta( $post_id, $data ) {
        if ( 'auction' !== sanitize_title( stripslashes( $data['product-type'] ) ) ) {
            return;
        }

        update_post_meta( $post_id, '_manage_stock', 'yes' );
        update_post_meta( $post_id, '_stock', '1' );
        update_post_meta( $post_id, '_backorders', 'no' );
        update_post_meta( $post_id, '_sold_individually', 'yes' );

        if ( isset( $_POST['_auction_item_condition'] ) ) {
            update_post_meta( $post_id, '_auction_item_condition', stripslashes( $_POST['_auction_item_condition'] ) );
        }

        if ( isset( $_POST['_auction_type'] ) ) {
            update_post_meta( $post_id, '_auction_type', stripslashes( $_POST['_auction_type'] ) );
        }

        if ( isset( $_POST['_auction_proxy'] ) ) {
            update_post_meta( $post_id, '_auction_proxy', stripslashes( $_POST['_auction_proxy'] ) );
        } else {
            update_post_meta( $post_id, '_auction_proxy', '0' );
        }

        if ( isset( $_POST['_auction_sealed'] ) && ! isset( $_POST['_auction_proxy'] ) ) {
            update_post_meta( $post_id, '_auction_sealed', stripslashes( $_POST['_auction_sealed'] ) );
        } else {
            update_post_meta( $post_id, '_auction_sealed', 'no' );
        }

        if ( isset( $_POST['_auction_start_price'] ) ) {
            update_post_meta( $post_id, '_auction_start_price', wc_format_decimal( wc_clean( $_POST['_auction_start_price'] ) ) );
        }

        if ( isset( $_POST['_auction_bid_increment'] ) ) {
            update_post_meta( $post_id, '_auction_bid_increment', wc_format_decimal( wc_clean( $_POST['_auction_bid_increment'] ) ) );
        }

        if ( isset( $_POST['_auction_reserved_price'] ) ) {
            update_post_meta( $post_id, '_auction_reserved_price', wc_format_decimal( wc_clean( $_POST['_auction_reserved_price'] ) ) );
        }

        if ( isset( $_POST['_regular_price'] ) ) {
            update_post_meta( $post_id, '_regular_price', wc_format_decimal( wc_clean( $_POST['_regular_price'] ) ) );
            update_post_meta( $post_id, '_price', wc_format_decimal( wc_clean( $_POST['_regular_price'] ) ) );
        }

        if ( isset( $_POST['_auction_dates_from'] ) ) {
            update_post_meta( $post_id, '_auction_dates_from', stripslashes( $_POST['_auction_dates_from'] ) );
        }

        if ( isset( $_POST['_auction_dates_to'] ) ) {
            update_post_meta( $post_id, '_auction_dates_to', stripslashes( $_POST['_auction_dates_to'] ) );
        }

        if ( isset( $_POST['_relist_auction_dates_from'] ) && isset( $_POST['_relist_auction_dates_to'] ) && ! empty( $_POST['_relist_auction_dates_from'] ) && ! empty( $_POST['_relist_auction_dates_to'] ) ) {
            $this->do_relist( $post_id, $_POST['_relist_auction_dates_from'], $_POST['_relist_auction_dates_to'] );
        }

        if ( isset( $_POST['_auction_automatic_relist'] ) ) {
            update_post_meta( $post_id, '_auction_automatic_relist', stripslashes( $_POST['_auction_automatic_relist'] ) );
        } else {
            update_post_meta( $post_id, '_auction_automatic_relist', 'no' );
        }

        if ( isset( $_POST['_auction_relist_fail_time'] ) ) {
            update_post_meta( $post_id, '_auction_relist_fail_time', stripslashes( $_POST['_auction_relist_fail_time'] ) );
        }

        if ( isset( $_POST['_auction_relist_not_paid_time'] ) ) {
            update_post_meta( $post_id, '_auction_relist_not_paid_time', stripslashes( $_POST['_auction_relist_not_paid_time'] ) );
        }

        if ( isset( $_POST['_auction_relist_duration'] ) ) {
            update_post_meta( $post_id, '_auction_relist_duration', stripslashes( $_POST['_auction_relist_duration'] ) );
        }

        $auction_bid_count = get_post_meta( $post_id, '_auction_bid_count', true );
        if ( empty( $auction_bid_count ) ) {
            update_post_meta( $post_id, '_auction_bid_count', '0' );
        }
    }

    public function do_relist( $post_id, $relist_from, $relist_to ) {

        global $wpdb;

        update_post_meta( $post_id, '_auction_dates_from', stripslashes( $relist_from ) );
        update_post_meta( $post_id, '_auction_dates_to', stripslashes( $relist_to ) );
        update_post_meta( $post_id, '_auction_relisted', current_time( 'mysql' ) );
        update_post_meta( $post_id, '_manage_stock', 'yes' );
        update_post_meta( $post_id, '_stock', '1' );
        update_post_meta( $post_id, '_stock_status', 'instock' );
        update_post_meta( $post_id, '_backorders', 'no' );
        update_post_meta( $post_id, '_sold_individually', 'yes' );
        delete_post_meta( $post_id, '_auction_closed' );
        delete_post_meta( $post_id, '_auction_started' );
        delete_post_meta( $post_id, '_auction_fail_reason' );
        delete_post_meta( $post_id, '_auction_current_bid' );
        delete_post_meta( $post_id, '_auction_current_bider' );
        delete_post_meta( $post_id, '_auction_max_bid' );
        delete_post_meta( $post_id, '_auction_max_current_bider' );
        delete_post_meta( $post_id, '_stop_mails' );
        delete_post_meta( $post_id, '_stop_mails' );
        delete_post_meta( $post_id, '_auction_bid_count' );
        delete_post_meta( $post_id, '_auction_sent_closing_soon' );
        delete_post_meta( $post_id, '_auction_sent_closing_soon2' );
        delete_post_meta( $post_id, '_auction_fail_email_sent' );
        delete_post_meta( $post_id, '_Reserve_fail_email_sent' );
        delete_post_meta( $post_id, '_auction_win_email_sent' );
        delete_post_meta( $post_id, '_auction_finished_email_sent' );

        $order_id = get_post_meta( $post_id, '_order_id', true );
        // check if the custom field has a value
        if ( ! empty( $order_id ) ) {
            $order = wc_get_order( $order_id );
            $order->update_status( 'failed', __( 'Failed because off relisting', 'wc_simple_auctions' ) );
            delete_post_meta( $post_id, '_order_id' );
        }

        $wpdb->delete( $wpdb->usermeta, array( 'meta_key' => 'wsa_my_auctions', 'meta_value' => $post_id ), array( '%s', '%s' ) );

        do_action( 'wcmp_afm_simple_auction_do_relist', $post_id, $relist_from, $relist_to );
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
            'active'   => __( 'Active', WCMp_AFM_TEXT_DOMAIN ),
            'finished' => __( 'Finished', WCMp_AFM_TEXT_DOMAIN ),
            'fail'     => __( 'Fail', WCMp_AFM_TEXT_DOMAIN ),
            'sold'     => __( 'Sold', WCMp_AFM_TEXT_DOMAIN ),
            'payed'    => __( 'Paid', WCMp_AFM_TEXT_DOMAIN ),
        );
        return apply_filters( 'wcmp_afm_simple_auction_page_filters', $output );
    }

    public static function filter_by_auction_status( $auction_type ) {

        switch ( $auction_type ) {
            case 'active' :
                return array(
                    array(
                        'key'     => '_auction_closed',
                        'compare' => 'NOT EXISTS'
                    ) );
            //no break needed as we are returning the array directly
            case 'finished' :
                return array(
                    array(
                        'key'     => '_auction_closed',
                        'value'   => array( '1', '2', '3', '4' ),
                        'compare' => 'IN',
                    ) );
            case 'fail' :
                return array(
                    array(
                        'key'   => '_auction_closed',
                        'value' => '1',
                    )
                );
            case 'sold' :
                return array(
                    'relation' => 'AND',
                    array(
                        'key'   => '_auction_closed',
                        'value' => '2',
                    ),
                    array(
                        'key'     => '_auction_payed',
                        'compare' => 'NOT EXISTS',
                    )
                );
            case 'payed' :
                return array(
                    array(
                        'key'   => '_auction_payed',
                        'value' => '1',
                    )
                );
            default: return array();
        }
    }

    /**
     * Return auction status html
     * 
     * @param integer $product_id 
     * @return string Auction Status
     */
    public static function get_auction_status_html( $product_id ) {

        if ( empty( $product_id ) || ! current_vendor_can( 'manage_simple_auctions' ) ) {
            return;
        }
        ob_start();

        $product = wc_get_product( $product_id );

        $auction_relisted = $product->get_auction_relisted();
        if ( ! empty( $auction_relisted ) ) {
            echo '<p>' . esc_html__( 'Auction has been relisted on:', 'wc_simple_auctions' ) . ' ' . $auction_relisted . '</p>';
        }

        if ( $product->is_closed() === TRUE && $product->is_started() === TRUE ) {
            echo '<p>' . esc_html__( 'Auction has finished', 'wc_simple_auctions' ) . '</p>';
            if ( $product->get_auction_fail_reason() == '1' ) {
                echo "<p>" . esc_html__( 'Auction failed because there were no bids', 'wc_simple_auctions' ) . "</p>";
            } elseif ( $product->get_auction_fail_reason() == '2' ) {
                echo "<p class='reservefail'>" . esc_html__( 'Auction failed because item did not make it to reserve price', 'wc_simple_auctions' ) . "</p>";
            }
            if ( $product->get_auction_closed() == '3' ) {
                echo '<p>' . esc_html__( 'Product sold for buy now price', 'wc_simple_auctions' ) . ': <span>' . wc_price( $product->get_regular_price() ) . '</span></p>';
            } elseif ( $product->get_auction_current_bider() ) {
                $auction_order_id = $product->get_order_id();
                $order_url = $auction_order_id ? wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders' ), $auction_order_id ) : '';
                if ( $product->get_auction_payed() ) {
                    echo '<p>' . esc_html__( 'Order has been paid, order ID is', 'wc_simple_auctions' ) . ': <span><a href="' . $order_url . '">#' . $auction_order_id . '</a></span></p>';
                } elseif ( $auction_order_id ) {
                    $order = wc_get_order( $auction_order_id );
                    if ( $order ) {
                        $order_status = $order->get_status() ? $order->get_status() : __( 'unknown', 'wc_simple_auctions' );
                        echo '<p>' . esc_html__( 'Order has been made, order status is', 'wc_simple_auctions' ) . ': <a href="' . $order_url . '">' . $order_status . '</a><span></p>';
                    }
                }
            }
        }
        if ( $product->is_closed() === FALSE && $product->is_started() === TRUE ) {
            if ( $product->get_auction_proxy() ) {
                echo '<p>' . esc_html__( 'This is proxy auction', 'wc_simple_auctions' ) . '</p>';
            }
        }
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
    
    public function include_auctions_woocommerce_product_query() {
        if ( afm()->is_vendor_dashboard_page() && class_exists( 'WooCommerce_simple_auction' ) ) {
            //this will ensure to get auctions in product query within vendor dashboard
            global $woocommerce_auctions;
            remove_filter( 'pre_get_posts', array( $woocommerce_auctions, 'auction_arhive_pre_get_posts' ) );
        }
    }
}