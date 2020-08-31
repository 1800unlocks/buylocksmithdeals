<?php
/**
 * WCMp_AFM_Endpoints setup
 *
 * @package  WCMp_AFM/classes
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Endpoint {

    /**
     * Primary class constructor.
     *
     * @since 3.0.0
     * @access public
     */
    public function __construct() {
        global $WCMp;

        add_filter( 'wcmp_product_list_bulk_actions', array( $this, 'add_bulk_edit_action' ) );
        // Products list add modal for bulk action and quick edit
        add_action( 'after_wcmp_vendor_dashboard_product_list_table', array( $this, 'add_modal_html' ) );

        // Product variable tab content
        add_action( 'wcmp_after_attribute_product_tabs_content', array( $this, 'wcmp_product_variable_tab_content' ), 10, 3 );
        add_action( 'template_redirect', array( $this, 'wcmp_duplicate_product_action' ), 90 );

        //Action for fetching the template of frontend import
        add_action( 'wcmp_vendor_dashboard_product-import_endpoint', array( $this, 'wcmp_vendor_dashboard_product_import_endpoint' ) );
        //Action for fetching the template of frontend export
        add_action( 'wcmp_vendor_dashboard_product-export_endpoint', array( $this, 'wcmp_vendor_dashboard_product_export_endpoint' ) );

    }

    public function add_bulk_edit_action( $action ) {
        if ( ! apply_filters( 'vendor_can_bulk_edit', true ) || ( isset( $_GET['post_status'] ) && $_GET['post_status'] === 'trash' ) ) {
            return $action;
        }
        $edit_action = array( 'edit' => __( 'Edit' ) );
        return array_merge( $edit_action, $action );
    }

    public function add_modal_html() {
        ob_start();
        if ( apply_filters( 'vendor_can_bulk_edit', true ) ) :
            ?>
            <!-- Modal -->
            <div class="modal fade" id="edit_action_modal" tabindex="-1" role="dialog" aria-labelledby="edit_action_modal" aria-hidden="true">
                <div class="modal-dialog modal-fluid modal-notify modal-success modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="modal_title"><?php _e( 'Product data', 'woocommerce' ); ?></h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="modal_body">
                            <?php
                            afm()->template->get_template( 'bulk-actions/html-bulk-edit-product.php' );
                            ?>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-default" id="do_bulk_update">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ( apply_filters( 'vendor_can_quick_edit', true ) ) : ?>
            <div class="modal fade" id="quickedit_action_modal" tabindex="-1" role="dialog" aria-labelledby="quickedit_action_modal" aria-hidden="true">
                <div class="modal-dialog modal-fluid modal-notify modal-success" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="quickedit_modal_title"><?php _e( 'Product data', 'woocommerce' ); ?></h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="quickedit_modal_body">
                            <?php
                            //$shipping_class = get_terms( 'product_shipping_class', array('hide_empty' => false) );
                            //afm()->template->get_template( 'bulk-actions/html-bulk-edit-product.php', array( 'shipping_class' => $shipping_class ) );
                            ?>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-default" id="do_quickedit">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php
        ob_end_flush();
    }

    public function load_class( $class_name = '' ) {
        if ( '' != $class_name && '' != WCMp_AFM_PLUGIN_TOKEN ) {
            require_once ('endpoints/class-' . esc_attr( WCMp_AFM_PLUGIN_TOKEN ) . '-' . esc_attr( $class_name ) . '-endpoint.php');
        }
    }

    /**
     * Duplicate owned product
     * @global type $WCMp
     * @return none|false false only if vendor is not allowed to duplicate products
     */
    public function wcmp_duplicate_product_action() {
        global $WCMp;

        $current_endpoint_key = $WCMp->endpoints->get_current_endpoint();
        // retrive the actual endpoint name in case admin changes that from settings
        $current_endpoint = get_wcmp_vendor_settings( 'wcmp_' . str_replace( '-', '_', $current_endpoint_key ) . '_endpoint', 'vendor', 'general', $current_endpoint_key );
        $products_endpoint = get_wcmp_vendor_settings( 'wcmp_products_endpoint', 'vendor', 'general', 'products' );
        if ( $current_endpoint !== $products_endpoint ) {
            return;
        }

        if ( empty( $_REQUEST['product_id'] ) || empty( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'afm-duplicate-product' ) ) {
            return;
        }

        $src_id = absint( wc_clean( $_REQUEST['product_id'] ) );
        $src_product = wc_get_product( $src_id );
        if ( ! $src_product ) {
            /* translators: %s: product id */
            wp_die( sprintf( __( 'Product creation failed, could not find original product: %s', 'woocommerce' ), $src_id ) );
        } elseif ( $src_product->get_status() === 'trash' ) {
            return;
        }

        $vendor_id = afm()->vendor_id;
        $vendor = get_wcmp_vendor( $vendor_id );
        if ( ! ( $vendor && is_current_vendor_product( $src_id ) ) ) {
            wp_die( __( 'You are not authorized to perform this action!', 'wcmp-afm' ) );
        }

        if ( apply_filters( 'wcmp_vendor_can_duplicate_owned_product', true, $vendor, $src_product ) ) {
            if ( ! class_exists( 'WC_Admin_Duplicate_Product', false ) ) {
                include_once( WC_ABSPATH . 'includes/admin/class-wc-admin-duplicate-product.php' );
            }
            $duplicate_instance = new WC_Admin_Duplicate_Product();
            $duplicate = $duplicate_instance->product_duplicate( $src_product );

            if ( $duplicate ) {
                // Hook rename to match other woocommerce_product_* hooks, and to move away from depending on a response from the wp_posts table.
                do_action( 'woocommerce_product_duplicate', $duplicate, $src_product );

                wp_set_object_terms( $duplicate->get_id(), absint( $vendor->term_id ), $WCMp->taxonomy->taxonomy_name );
                $redirect_url = wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_edit_product_endpoint', 'vendor', 'general', 'edit-product' ), $duplicate->get_id() );
                $duplicate_product_redirect_url = apply_filters( 'wcmp_afm_after_product_duplicate_redirect_url', $redirect_url, $src_product );
                wp_redirect( $redirect_url );
                exit;
            }
        }
        return false;
    }

    /**
     * fetches the template of frontend import
     */
    public function wcmp_vendor_dashboard_product_import_endpoint() {
        $this->load_class( 'import-product' );
        $import_product = new WCMp_AFM_Import_Product_Endpoint();
        $import_product->output();
    }

    /**
     * fetches the template of frontend export
     */
    public function wcmp_vendor_dashboard_product_export_endpoint() {
        $this->load_class( 'export-product' );
        $export_product = new WCMp_AFM_Export_Product_Endpoint();
        $export_product->output();
    }
    
    /**
     * Variable product variation tab content
     */
    public function wcmp_product_variable_tab_content( $self, $product_object, $post ) {
        if ( wcmp_is_allowed_product_type( 'variable' ) ) {
            afm()->template->get_template( 'products/woocommerce/html-product-data-variations.php', array( 'self' => $self, 'product_object' => $product_object, 'post' => $post ) );
        }
    }

}
