<?php

/**
 * WCMp_AFM_Frontend setup
 *
 * @package  WCMp_AFM/classes
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Frontend {

    public function __construct() {
        //dequeue scripts on add product page
        add_action( 'wp_print_scripts', array( &$this, 'vendor_dashboard_dequeue_script' ), 100 );
        //enqueue scripts
        add_action( 'wcmp_frontend_enqueue_scripts', array( &$this, 'vendor_dashboard_scripts' ) );
        //enqueue styles
        add_action( 'wcmp_frontend_enqueue_scripts', array( &$this, 'vendor_dashboard_styles' ) );

        add_filter( 'wcmp_disable_other_product_type', '__return_false' );
        add_filter( 'wcmp_vendor_dashboard_header_right_panel_nav', array( &$this, 'remove_dashboard_header_right_panel_nav' ) );
        add_filter( 'wcmp_create_vendor_dashboard_breadcrumbs', array( &$this, 'change_label_to_reflect_product_edit_action' ), 10, 3 );
    }

    public function vendor_dashboard_dequeue_script() {
        if ( ! afm()->is_vendor_dashboard_page() ) {
            return;
        }
        global $WCMp, $wp_scripts;
        //remove scripts enqued from theme
        $scripts_to_keep = apply_filters( 'wcmp_theme_scripts_to_keep', array() );
        foreach ( $wp_scripts->queue as $handle ) {
            if ( ! empty( $scripts_to_keep ) && in_array( $handle, $scripts_to_keep ) )
                continue;
            $src = $wp_scripts->registered[$handle]->src;
            if ( strpos( $src, 'wp-content/themes' ) !== false ) {
                wp_dequeue_script( $handle );
            }
        }
        $endpoint = $WCMp->endpoints->get_current_endpoint();
        if ( $endpoint === 'edit-product' || apply_filters( 'afm_endpoints_dequeue_wcmp_scripts', false, $endpoint ) ) {
            wp_dequeue_script( 'wcmp_frontend_vdashboard_js' );
            wp_dequeue_script( 'vendor_order_by_product_js' );
            wp_dequeue_script( 'wcmp_seller_review_rating_js' );
            wp_dequeue_script( 'wcmp_customer_qna_js' );
        }
    }

    public function vendor_dashboard_scripts() {
        global $WCMp;

        $frontend_script_path = WCMp_AFM_PLUGIN_URL . 'assets/frontend/js/';
        $frontend_script_path = str_replace( array( 'http:', 'https:' ), '', $frontend_script_path );
        $lib_path = WCMp_AFM_PLUGIN_URL . 'lib/js/';
        $lib_path = str_replace( array( 'http:', 'https:' ), '', $lib_path );
//        $pluginURL = str_replace( array( 'http:', 'https:' ), '', WCMp_AFM_PLUGIN_URL );
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        $endpoint = $WCMp->endpoints->get_current_endpoint();
        if ( $endpoint === 'edit-product' ) {
            do_action( 'afm_add_product_load_script_lib', $frontend_script_path, $lib_path, $suffix );

            do_action( 'afm_after_add_product_scripts_registered', $frontend_script_path, array( 'wcmp-advance-product' ) );
        } elseif ( $endpoint === 'add-coupon' ) {
            do_action( 'afm_add_coupon_load_script_lib', $frontend_script_path, $lib_path, $suffix );
            $WCMp->library->load_select2_lib();
            $WCMp->library->load_datepicker_lib();
            wp_enqueue_script( 'selectWoo' );
            wp_register_script( 'wcmp-afm-add-coupon', $frontend_script_path . 'coupon.js', array( 'jquery', 'select2_js', 'jquery-ui-datepicker', 'selectWoo' ), WCMp_AFM_VERSION );

            do_action( 'afm_after_add_coupon_scripts_registered', $frontend_script_path, array( 'wcmp-afm-add-coupon' ) );
        } elseif ( $endpoint === 'product-import' ) {
            wp_register_script( 'dc-product-import', $frontend_script_path . 'import.js', array( 'jquery' ) );
        } elseif ( $endpoint === 'product-export' ) {
            if ( class_exists( 'woocommerce' ) ) {
                wp_enqueue_style( 'select2' );
                wp_enqueue_script( 'select2' );
            }
            wp_enqueue_script( 'dc-product-export', $frontend_script_path . 'export.js', array( 'jquery' ) );
            wp_localize_script( 'dc-product-export', 'wc_product_export_params', array(
                'export_nonce' => wp_create_nonce( 'wc-product-export' ), 'ajax_url'     => WC()->ajax_url()
            ) );
        } elseif ( $endpoint === 'products' ) { //@bulk
            $WCMp->library->load_select2_lib();
            wp_enqueue_script( 'afm-bulk-delete', $frontend_script_path . 'bulk.js', array( 'jquery', 'select2_js' ) );
            wp_localize_script( 'afm-bulk-delete', 'products_params', array(
                'ajax_url'          => WC()->ajax_url(),
                'bulk_edit_nonce'   => wp_create_nonce( 'afm-bulk-edit' ),
                'i18n_bulk_edit'    => esc_js( __( 'Bulk Edit' ) ),
                'i18n_no_selection' => esc_js( __( 'No products selected!', 'wcmp-afm' ) ),
            ) );
        }
        do_action( 'afm_enqueue_dashboard_scripts', $endpoint, $frontend_script_path, $lib_path, $suffix );
    }

    public function vendor_dashboard_styles() {
        global $WCMp;

        $frontend_style_path = WCMp_AFM_PLUGIN_URL . 'assets/frontend/css/';
        $frontend_style_path = str_replace( array( 'http:', 'https:' ), '', $frontend_style_path );
        $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        $endpoint = $WCMp->endpoints->get_current_endpoint();
        if ( $endpoint === 'edit-product' ) {
            wp_enqueue_style( 'wcmp-afm-add-product', $frontend_style_path . 'product_manager.css', array(), WCMp_AFM_VERSION );
        } elseif ( $endpoint === 'product-import' || $endpoint === 'product-export' ) {
            if ( class_exists( 'woocommerce' ) ) {
                wp_enqueue_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
                // Add RTL support for admin styles
                // wp_style_add_data( 'woocommerce_admin_styles', 'rtl', 'replace' );
            }
        }

        wp_enqueue_style( 'wcmp-afm-dashboard', $frontend_style_path . 'dashboard.css', array(), WCMp_AFM_VERSION );

        do_action( 'after_add_product_style_enqueued', $frontend_style_path );
    }

    /**
     * AFM feature
     * Remove wp-admin link under user-profile section (vendor dashboard)
     *
     * @access public
     * @return void
     */
    function remove_dashboard_header_right_panel_nav( $nav ) {
        if ( afm()->is_vendor_dashboard_page() ) {
            $general_settings = get_option( 'wcmp_general_settings_name' );
            if ( isset( $general_settings['is_backend_diabled'] ) && isset( $nav['wp-admin'] ) ) {
                unset( $nav['wp-admin'] );
            }
            return $nav;
        }
    }

    public function change_label_to_reflect_product_edit_action( $breadcrumbs, $menu ) {
        global $wp, $WCMp;

        $current_endpoint_key = $WCMp->endpoints->get_current_endpoint();
        // retrive the actual endpoint name in case admn changes that from settings
        $current_endpoint = get_wcmp_vendor_settings( 'wcmp_' . str_replace( '-', '_', $current_endpoint_key ) . '_endpoint', 'vendor', 'general', $current_endpoint_key );
        // retrive add-product endpoint name in case admn changes that from settings
        $add_product_endpoint = get_wcmp_vendor_settings( 'wcmp_edit_product_endpoint', 'vendor', 'general', 'edit-product' );

        $product_id = isset( $wp->query_vars[$add_product_endpoint] ) ? $wp->query_vars[$add_product_endpoint] : '';

        if ( $product_id && wc_get_product( $product_id ) && $current_endpoint === $add_product_endpoint ) {
            return str_replace( __( 'Add Product', 'dc-woocommerce-multi-vendor' ), __( 'Edit Product', 'wcmp-afm' ), $breadcrumbs );
        }
        return $breadcrumbs;
    }

}
