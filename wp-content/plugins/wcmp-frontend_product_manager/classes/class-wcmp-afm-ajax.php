<?php
/**
 * WCMp_AFM_Ajax class
 *
 * @package  WCMp_AFM/classes
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Ajax {

    public function __construct() {
        //Filter for adding new actions in vendor Product list under product name
        //@defined via WCMp_Ajax class
        add_filter( 'wcmp_vendor_product_list_row_actions_column', array( $this, 'add_duplicate_product_action' ), 10, 2 );
        //ajax call to update products in bulk
        add_action( 'wp_ajax_wcmp_afm_bulk_product_edit', array( $this, 'bulk_update_products' ) );
        //ajax call to get the product attributes
        add_action( 'wp_ajax_wcmp_afm_add_product_attribute', array( $this, 'add_product_attribute_callback' ) );
        add_action( 'wp_ajax_wcmp_afm_save_attributes', array( $this, 'save_attributes_callback' ) );

        add_action( 'wp_ajax_wcmp_afm_load_variations', array( $this, 'load_variations_callback' ) );
        add_action( 'wp_ajax_wcmp_afm_add_variation', array( $this, 'add_variation_callback' ) );

        //rental pro
        add_action( 'wp_ajax_wcmp_afm_rental_add_inventory_item', array( $this, 'add_inventory_item_callback' ) );
        add_action( 'wp_ajax_wcmp_afm_rental_add_availability', array( $this, 'add_availability_callback' ) );
        add_action( 'wp_ajax_wcmp_afm_rental_add_days_range', array( $this, 'add_days_range_callback' ) );
        add_action( 'wp_ajax_wcmp_afm_rental_add_price_discount', array( $this, 'add_price_discount_callback' ) );
        //rental quotes
        add_action( 'wp_ajax_wcmp_vendor_rental_quotes_list', array( $this, 'rental_quotes_list' ) );
        add_action( 'wp_ajax_wcmp_afm_rental_quote_reply', array( $this, 'rental_quote_reply' ) );
        add_action( 'wp_ajax_wcmp_afm_rental_update_quote', array( $this, 'rental_update_quote' ) );

        //rental
        add_action( 'wp_ajax_wcmp_afm_rental_free_add_availability', array( $this, 'add_rental_availability_callback' ) );

        //appointment
        add_action( 'wp_ajax_wcmp_vendor_appointment_list', array( $this, 'appointment_list' ) );
        add_action( 'wp_ajax_wcmp_afm_json_search_appointable_products', array( $this, 'json_search_appointable_products' ) );

        //bookings
        add_action( 'wp_ajax_wcmp_vendor_booking_list', array( $this, 'booking_list' ) );
        add_action( 'wp_ajax_wcmp_vendor_resources_list', array( $this, 'resources_list' ) );
        add_action( 'wp_ajax_wcmp_afm_add_bookable_person', array( $this, 'add_bookable_person' ) );
        add_action( 'wp_ajax_wcmp_afm_unlink_bookable_person', array( $this, 'unlink_bookable_person' ) );
        add_action( 'wp_ajax_wcmp_afm_add_bookable_resource', array( $this, 'add_bookable_resource' ) );
        add_action( 'wp_ajax_wcmp_afm_remove_bookable_resource', array( $this, 'remove_bookable_resource' ) );
        add_action( 'wp_ajax_wcmp_afm_json_search_customers', array( $this, 'json_search_customers' ) );

        //bundle
        add_action( 'wp_ajax_wcmp_afm_json_search_bundle_items', array( $this, 'json_search_valid_bundle_items' ) );
        add_action( 'wp_ajax_wcmp_afm_add_product_to_bundle', array( $this, 'add_product_to_bundle' ) );

        //product addons
        add_action( 'wp_ajax_wcmp_afm_add_product_addon', array( $this, 'add_product_addon' ) );

        //subscription
        add_action( 'wp_ajax_wcmp_vendor_subscription_list', array( $this, 'subscription_list' ) );

        //yith auction premium
        add_action( 'wp_ajax_wcmp_afm_yith_reschedule_auction', array( $this, 'reschedule_auction' ) );
        add_action( 'wp_ajax_wcmp_afm_yith_auction_resend_winner_email', array( $this, 'resend_winner_email' ) );
        add_action( 'wp_ajax_wcmp_vendor_auction_list', array( $this, 'auction_list' ) );

        //simple auction
        add_action( 'wp_ajax_wcmp_afm_simple_auction_delete_bid', array( $this, 'simple_auction_delete_bid' ) );
        add_action( 'wp_ajax_wcmp_vendor_simple_auction_list', array( $this, 'simple_auction_list' ) );

         //Wp affiliates
        /********************* WCMp vendor dashboard assign-affiliate endpoint response *****************************/
        add_action('wp_ajax_request_affiliate_vendor_action',array($this, 'request_affiliate_vendor_action_calback'));
        /******************** Request admin to active the status  **********************************/
        add_action( 'wp_ajax_request_affiliate_status_changed',array($this, 'request_affiliate_status_changed') );
        /****************** Vendor can delete the affiliate **************************/
        add_action( 'wp_ajax_request_affiliate_delete_vendor',array($this, 'request_affiliate_delete_vendor') );
    }

    /**
     * Show the "Duplicate" link in vendor products list.
     * @param array $actions Array of actions.
     * @param Object $product WooCommerce product object
     * @return array 
     */
    public function add_duplicate_product_action( $actions, $product ) {
        if ( 'product' !== $product->post_type || $product->get_status() === 'trash' ) {
            return $actions;
        }
        $duplicate = array(
            'duplicate' => '<a title="Duplicate" href="' . wp_nonce_url( add_query_arg( array( 'product_id' => $product->get_id() ), wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_products_endpoint', 'vendor', 'general', 'products' ) ) ), 'afm-duplicate-product' ) . '" aria-label="' . esc_attr__( 'Make a duplicate from this product', 'woocommerce' )
            . '" rel="permalink">' . __( '<i class="wcmp-font ico-duplicate-icon"></i>', 'woocommerce' ) . '</a>'
        );
        return array_merge( $actions, $duplicate );
    }

    /**
     * Bulk product update from vendor dashboard
     * @return status 
     */
    public function bulk_update_products() {
        if ( ! current_vendor_can( 'edit_product' ) || ! current_vendor_can( 'edit_published_products' ) || ! apply_filters( 'vendor_can_bulk_edit_products', true ) ) {
            wp_die( -1 );
        }
        check_ajax_referer( 'afm-bulk-edit', 'security' );
        $product_ids = isset( $_POST['product_ids'] ) ? json_decode( stripslashes( $_POST['product_ids'] ) ) : array();
        if ( empty( $product_ids ) ) {
            wp_die( -1 );
        }
        $form_data = array();
        parse_str( $_POST['form_data'], $form_data );
        $update_counter = 0;
        foreach ( $product_ids as $product_id ) {
            if( !is_current_vendor_product( $product_id ) ) {
                continue;
            }
            $product = wc_get_product( absint( $product_id ) );
            if ( ! $product ) {
                wp_die( -1 );
            }
            
            $update_counter++;
            
            $data_store = $product->get_data_store();
            $old_regular_price = $product->get_regular_price();
            $old_sale_price = $product->get_sale_price();
            $data = wp_unslash( $form_data ); // WPCS: input var ok, CSRF ok.

            if ( afm_is_allowed_vendor_shipping() ) { // WPCS: input var ok, sanitization ok.
                if ( ! empty( $data['change_weight'] ) && isset( $data['_weight'] ) ) { // WPCS: input var ok, sanitization ok.
                    $product->set_weight( wc_clean( wp_unslash( $data['_weight'] ) ) ); // WPCS: input var ok, sanitization ok.
                }
                if ( ! empty( $data['change_dimensions'] ) ) {
                    if ( isset( $data['_length'] ) ) { // WPCS: input var ok, sanitization ok.
                        $product->set_length( wc_clean( wp_unslash( $data['_length'] ) ) ); // WPCS: input var ok, sanitization ok.
                    }
                    if ( isset( $data['_width'] ) ) { // WPCS: input var ok, sanitization ok.
                        $product->set_width( wc_clean( wp_unslash( $data['_width'] ) ) ); // WPCS: input var ok, sanitization ok.
                    }
                    if ( isset( $data['_height'] ) ) { // WPCS: input var ok, sanitization ok.
                        $product->set_height( wc_clean( wp_unslash( $data['_height'] ) ) ); // WPCS: input var ok, sanitization ok.
                    }
                }
                if ( ! empty( $data['_shipping_class'] ) ) { // WPCS: input var ok, sanitization ok.
                    if ( '_no_shipping_class' === $data['_shipping_class'] ) { // WPCS: input var ok, sanitization ok.
                        $product->set_shipping_class_id( 0 );
                    } else {
                        $shipping_class_id = $data_store->get_shipping_class_id_by_slug( wc_clean( $data['_shipping_class'] ) ); // WPCS: input var ok, sanitization ok.
                        $product->set_shipping_class_id( $shipping_class_id );
                    }
                }
            }

            if ( afm_is_enabled_vendor_tax() ) {
                if ( ! empty( $data['_tax_status'] ) ) { // WPCS: input var ok, sanitization ok.
                    $product->set_tax_status( wc_clean( $data['_tax_status'] ) ); // WPCS: input var ok, sanitization ok.
                }

                if ( ! empty( $data['_tax_class'] ) ) { // WPCS: input var ok, sanitization ok.
                    $tax_class = wc_clean( wp_unslash( $data['_tax_class'] ) ); // WPCS: input var ok, sanitization ok.
                    if ( 'standard' === $tax_class ) {
                        $tax_class = '';
                    }
                    $product->set_tax_class( $tax_class );
                }
            }

            if ( afm_is_allowed_vendor_feature_product() ) {
                if ( ! empty( $data['_visibility'] ) ) { // WPCS: input var ok, sanitization ok.
                    $product->set_catalog_visibility( wc_clean( $data['_visibility'] ) ); // WPCS: input var ok, sanitization ok.
                }

                if ( ! empty( $data['_featured'] ) ) { // WPCS: input var ok, sanitization ok.
                    $product->set_featured( wp_unslash( $data['_featured'] ) ); // WPCS: input var ok, sanitization ok.
                }
            }

            if ( afm_is_allowed_vendor_manage_stock() && ! empty( $data['_sold_individually'] ) ) { // WPCS: input var ok, sanitization ok.
                if ( 'yes' === $data['_sold_individually'] ) { // WPCS: input var ok, sanitization ok.
                    $product->set_sold_individually( 'yes' );
                } else {
                    $product->set_sold_individually( '' );
                }
            }

            // Handle price - remove dates and set to lowest.
            $change_price_product_types = apply_filters( 'woocommerce_bulk_edit_save_price_product_types', array( 'simple', 'external' ) );
            $can_product_type_change_price = false;
            foreach ( $change_price_product_types as $product_type ) {
                if ( $product->is_type( $product_type ) ) {
                    $can_product_type_change_price = true;
                    break;
                }
            }

            if ( $can_product_type_change_price ) {
                $price_changed = false;

                if ( ! empty( $data['change_regular_price'] ) && isset( $data['_regular_price'] ) ) { // WPCS: input var ok, sanitization ok.
                    $change_regular_price = absint( $data['change_regular_price'] ); // WPCS: input var ok, sanitization ok.
                    $raw_regular_price = wc_clean( wp_unslash( $data['_regular_price'] ) ); // WPCS: input var ok, sanitization ok.
                    $is_percentage = (bool) strstr( $raw_regular_price, '%' );
                    $regular_price = wc_format_decimal( $raw_regular_price );

                    switch ( $change_regular_price ) {
                        case 1:
                            $new_price = $regular_price;
                            break;
                        case 2:
                            if ( $is_percentage ) {
                                $percent = $regular_price / 100;
                                $new_price = $old_regular_price + ( round( $old_regular_price * $percent, wc_get_price_decimals() ) );
                            } else {
                                $new_price = $old_regular_price + $regular_price;
                            }
                            break;
                        case 3:
                            if ( $is_percentage ) {
                                $percent = $regular_price / 100;
                                $new_price = max( 0, $old_regular_price - ( round( $old_regular_price * $percent, wc_get_price_decimals() ) ) );
                            } else {
                                $new_price = max( 0, $old_regular_price - $regular_price );
                            }
                            break;

                        default:
                            break;
                    }

                    if ( isset( $new_price ) && $new_price !== $old_regular_price ) {
                        $price_changed = true;
                        $new_price = round( $new_price, wc_get_price_decimals() );
                        $product->set_regular_price( $new_price );
                    }
                }

                if ( ! empty( $data['change_sale_price'] ) && isset( $data['_sale_price'] ) ) { // WPCS: input var ok, sanitization ok.
                    $change_sale_price = absint( $data['change_sale_price'] ); // WPCS: input var ok, sanitization ok.
                    $raw_sale_price = wc_clean( wp_unslash( $data['_sale_price'] ) ); // WPCS: input var ok, sanitization ok.
                    $is_percentage = (bool) strstr( $raw_sale_price, '%' );
                    $sale_price = wc_format_decimal( $raw_sale_price );

                    switch ( $change_sale_price ) {
                        case 1:
                            $new_price = $sale_price;
                            break;
                        case 2:
                            if ( $is_percentage ) {
                                $percent = $sale_price / 100;
                                $new_price = $old_sale_price + ( $old_sale_price * $percent );
                            } else {
                                $new_price = $old_sale_price + $sale_price;
                            }
                            break;
                        case 3:
                            if ( $is_percentage ) {
                                $percent = $sale_price / 100;
                                $new_price = max( 0, $old_sale_price - ( $old_sale_price * $percent ) );
                            } else {
                                $new_price = max( 0, $old_sale_price - $sale_price );
                            }
                            break;
                        case 4:
                            if ( $is_percentage ) {
                                $percent = $sale_price / 100;
                                $new_price = max( 0, $product->regular_price - ( $product->regular_price * $percent ) );
                            } else {
                                $new_price = max( 0, $product->regular_price - $sale_price );
                            }
                            break;

                        default:
                            break;
                    }

                    if ( isset( $new_price ) && $new_price !== $old_sale_price ) {
                        $price_changed = true;
                        $new_price = ! empty( $new_price ) || '0' === $new_price ? round( $new_price, wc_get_price_decimals() ) : '';
                        $product->set_sale_price( $new_price );
                    }
                }

                if ( $price_changed ) {
                    $product->set_date_on_sale_to( '' );
                    $product->set_date_on_sale_from( '' );

                    if ( $product->get_regular_price() < $product->get_sale_price() ) {
                        $product->set_sale_price( '' );
                    }
                }
            }

            // Handle Stock Data.
            if ( afm_is_allowed_vendor_manage_stock() ) {
                $was_managing_stock = $product->get_manage_stock() ? 'yes' : 'no';
                $stock_status = $product->get_stock_status();
                $backorders = $product->get_backorders();
                $backorders = ! empty( $data['_backorders'] ) ? wc_clean( $data['_backorders'] ) : $backorders; // WPCS: input var ok, sanitization ok.
                $stock_status = ! empty( $data['_stock_status'] ) ? wc_clean( $data['_stock_status'] ) : $stock_status; // WPCS: input var ok, sanitization ok.

                if ( ! empty( $data['_manage_stock'] ) ) { // WPCS: input var ok, sanitization ok.
                    $manage_stock = 'yes' === wc_clean( $data['_manage_stock'] ) && 'grouped' !== $product->get_type() ? 'yes' : 'no'; // WPCS: input var ok, sanitization ok.
                } else {
                    $manage_stock = $was_managing_stock;
                }

                $stock_amount = 'yes' === $manage_stock && ! empty( $data['change_stock'] ) && isset( $data['_stock'] ) ? wc_stock_amount( $data['_stock'] ) : $product->get_stock_quantity(); // WPCS: input var ok, sanitization ok.

                $product->set_manage_stock( $manage_stock );
                $product->set_backorders( $backorders );

                $product->set_stock_quantity( $stock_amount );

                // Apply product type constraints to stock status.
                if ( $product->is_type( 'external' ) ) {
                    // External products are always in stock.
                    $product->set_stock_status( 'instock' );
                } elseif ( $product->is_type( 'variable' ) && ! $product->get_manage_stock() ) {
                    // Stock status is determined by children.
                    foreach ( $product->get_children() as $child_id ) {
                        $child = wc_get_product( $child_id );
                        if ( ! $product->get_manage_stock() ) {
                            $child->set_stock_status( $stock_status );
                            $child->save();
                        }
                    }
                    $product = WC_Product_Variable::sync( $product, false );
                } else {
                    $product->set_stock_status( $stock_status );
                }
            }

            // set product custom terms
            $custom_terms = isset( $data['tax_input'] ) ? $data['tax_input'] : array();
            // Set Product Custom Terms
            if ( ! empty( $custom_terms ) ) {
                foreach ( $custom_terms as $term => $value ) {
                    $custom_term = isset( $data['tax_input'][$term] ) ? array_filter( array_map( 'intval', (array) $data['tax_input'][$term] ) ) : array();
                    wp_set_object_terms( absint( $product_id ), $custom_term, $term );
                }
            }
            // change product status from publish to pending, if vendor does not have publish product capability
            $prev_status = $product->get_status( 'edit' );
            if ( $prev_status === 'publish' && ! current_user_can( 'publish_products' ) ) {
                $product->set_status( 'pending' );
            }
            $product->save();

            do_action( 'wcmp_afm_product_bulk_edit_save', $product );
        }
        $status = array( 'status' => true, 'message' => sprintf( _n( '%s Product updated successfully', '%s Products updated successfully', $update_counter, 'wcmp-afm' ) ), number_format_i18n( $update_counter ) );
        wp_send_json( $status );
    }

    /**
     * Add an attribute row.
     */
    public function add_product_attribute_callback() {
        ob_start();

        check_ajax_referer( 'add-attribute', 'security' );

        if ( ! current_user_can( 'edit_products' ) || ( ! apply_filters( 'vendor_can_add_custom_attribute', true ) && empty( sanitize_text_field( $_POST['taxonomy'] ) ) ) ) {
            wp_die( -1 );
        }

        $i = absint( $_POST['i'] );
        $metabox_class = array();
        $attribute = new WC_Product_Attribute();

        $attribute->set_id( wc_attribute_taxonomy_id_by_name( sanitize_text_field( $_POST['taxonomy'] ) ) );
        $attribute->set_name( sanitize_text_field( $_POST['taxonomy'] ) );
        $attribute->set_visible( apply_filters( 'woocommerce_attribute_default_visibility', 1 ) );
        $attribute->set_variation( apply_filters( 'woocommerce_attribute_default_is_variation', 0 ) );

        if ( $attribute->is_taxonomy() ) {
            $metabox_class[] = 'taxonomy';
            $metabox_class[] = $attribute->get_name();
        }

        include( WCMp_AFM_PLUGIN_DIR . 'views/products/woocommerce/html-product-attribute.php' );
        wp_die();
    }

    /**
     * Save attributes
     */
    public function save_attributes_callback() {
        check_ajax_referer( 'save-attributes', 'security' );

        if ( ! current_user_can( 'edit_products' ) ) {
            wp_die( -1 );
        }

        parse_str( $_POST['data'], $data );

        $attr_data = isset( $data['wc_attributes'] ) ? $data['wc_attributes'] : array();

        $attributes = afm_woo()->prepare_attributes( $attr_data );
        $product_id = absint( $_POST['post_id'] );
        $product_type = ! empty( $_POST['product_type'] ) ? wc_clean( $_POST['product_type'] ) : 'simple';
        $classname = WC_Product_Factory::get_product_classname( $product_id, $product_type );
        $product = new $classname( $product_id );

        $product->set_attributes( $attributes );
        $product->save();
        wp_die();
    }

    /**
     * Load variations via AJAX.
     */
    public function load_variations_callback() {
        ob_start();

        check_ajax_referer( 'load-variations', 'security' );

        if ( ! current_user_can( 'edit_products' ) || empty( $_POST['product_id'] ) ) {
            wp_die( -1 );
        }

        // Set $post global so its available, like within the admin screens
        global $post;

        $loop = 0;
        $product_id = absint( $_POST['product_id'] );
        $post = get_post( $product_id );
        $product_object = wc_get_product( $product_id );
        $per_page = ! empty( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 10;
        $page = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $variations = wc_get_products( array(
            'status'  => array( 'private', 'publish' ),
            'type'    => 'variation',
            'parent'  => $product_id,
            'limit'   => $per_page,
            'page'    => $page,
            'orderby' => array(
                'menu_order' => 'ASC',
                'ID'         => 'DESC',
            ),
            'return'  => 'objects',
            ) );
        if ( $variations ) {
            foreach ( $variations as $variation_object ) {
                $variation_id = $variation_object->get_id();
                $variation = get_post( $variation_id );
                $variation_data = array_merge( array_map( 'maybe_unserialize', get_post_custom( $variation_id ) ), wc_get_product_variation_attributes( $variation_id ) ); // kept for BW compatibility.
                afm()->template->get_template( 'products/woocommerce/html-product-variations.php', array( 'variation_object' => $variation_object, 'variation_id' => $variation_id, 'variation_data' => $variation_data, 'variation' => $variation, 'product_object' => $product_object ) );
                $loop ++;
            }
        }
        wp_die();
    }

    /**
     * Add variation via ajax function.
     */
    public function add_variation_callback() {
        check_ajax_referer( 'add-variation', 'security' );

        if ( ! current_user_can( 'edit_products' ) ) {
            wp_die( -1 );
        }

        global $post; // Set $post global so its available, like within the admin screens.

        $product_id = intval( $_POST['post_id'] );
        $post = get_post( $product_id );
        $loop = intval( $_POST['loop'] );
        $product_object = wc_get_product( $product_id );
        $classname = WC_Product_Factory::get_product_classname( $product_id, 'variable' );
        // if the saved product type is not variation, it will return a variation class object
        $variable_product_object = new $classname( $product_id );
        $variation_object = new WC_Product_Variation();
        $variation_object->set_parent_id( $product_id );
        $variation_object->set_attributes( array_fill_keys( array_map( 'sanitize_title', array_keys( $variable_product_object->get_variation_attributes() ) ), '' ) );
        $variation_id = $variation_object->save();
        $variation = get_post( $variation_id );
        $variation_data = array_merge( array_map( 'maybe_unserialize', get_post_custom( $variation_id ) ), wc_get_product_variation_attributes( $variation_id ) ); // kept for BW compatibility.
        
        afm()->template->get_template( 'products/woocommerce/html-product-variations.php', array( 'variation_object' => $variation_object, 'variation_id' => $variation_id, 'variation_data' => $variation_data, 'variation' => $variation, 'product_object' => $product_object ) );
        
        wp_die();
    }

    public function add_inventory_item_callback() {
        ob_start();

        check_ajax_referer( 'add-inventory-item', 'security' );

        if ( ! current_user_can( 'edit_products' ) || ! wcmp_is_allowed_product_type( 'redq_rental' ) ) {
            wp_die( -1 );
        }

        $i = absint( $_POST['i'] );
        $rnb_taxonomies = array( 'rnb_categories', 'pickup_location', 'dropoff_location', 'resource', 'person', 'deposite', 'attributes', 'features' );
        afm()->template->get_template( 'products/rental/html-product-rental-inventory.php', array( 'i' => $i, 'rnb_taxonomies' => $rnb_taxonomies ) );
        wp_die();
    }

    public function add_availability_callback() {
        ob_start();

        check_ajax_referer( 'add-inventory-availability', 'security' );

        if ( ! current_user_can( 'edit_products' ) || ! wcmp_is_allowed_product_type( 'redq_rental' ) ) {
            wp_die( -1 );
        }

        $i = absint( $_POST['i'] );
        $j = absint( $_POST['j'] );
        afm()->template->get_template( 'products/rental/html-product-rental-availability.php', array( 'i' => $i, 'j' => $j ) );
        wp_die();
    }

    public function add_rental_availability_callback() {
        ob_start();

        check_ajax_referer( 'add-own-availability', 'security' );

        if ( ! current_user_can( 'edit_products' ) || ! wcmp_is_allowed_product_type( 'redq_rental' ) ) {
            wp_die( -1 );
        }

        $i = absint( $_POST['i'] );
        afm()->template->get_template( 'products/rental/html-product-rental-own-avaiablity.php', array( 'i' => $i ) );
        wp_die();
    }

    public function appointment_list() {
        ob_start();

        $current_vendor_id = afm()->vendor_id;
        if ( ! $current_vendor_id || ! current_vendor_can( 'manage_appointments' ) ) {
            wp_die( -1 );
        }
        $requestData = $_REQUEST;
        //$enable_ordering = apply_filters( 'wcmp_vendor_dashboard_appointment_list_table_orderable_columns', array( 'id', 'booked-product', 'start-date', 'end-date' ) );

        $args = array();

        if ( isset( $requestData['post_status'] ) && $requestData['post_status'] != '' ) {
            $args['post_status'] = $requestData['post_status'];
        }

        if ( isset( $requestData['filter_appointments'] ) && $requestData['filter_appointments'] != '' ) {
            $args['meta_query'] = array(
                array(
                    'key'   => '_appointment_product_id',
                    'value' => absint( $requestData['filter_appointments'] ),
                ),
            );
        }
        // filter/ordering data
        if ( ! empty( $requestData['search']['value'] ) ) {
            $args['s'] = $requestData['search']['value'];
        }
        if ( isset( $requestData['order'][0]['column'] ) ) {
            $args['orderby'] = $enable_ordering[$requestData['order'][0]['column']];
            $args['order'] = $requestData['order'][0]['dir'];
        }
        $args['offset'] = $requestData['start'];
        $args['posts_per_page'] = $requestData['length'];

        $data = array();
        $vendor_appointments = WCMp_AFM_Appointment_Integration::get_vendor_appointment_array( $args );

        //wp_send_json($vendor_appointments);
        if ( $vendor_appointments ) {
            foreach ( $vendor_appointments as $vendor_appointment ) {
                $row = array();
                $appointment = new WC_Appointment( $vendor_appointment->ID );
                $product_id = $appointment->get_product_id();
                $product = $appointment->get_product();
                $product_url = wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_edit_product_endpoint', 'vendor', 'general', 'edit-product' ), $product_id );

                $appointment_order = $appointment->get_order();
                $order_id = is_callable( array( $appointment_order, 'get_id' ) ) ? $appointment_order->get_id() : $appointment_order->id;
                $suborder = get_wcmp_suborders($order_id);
                $order = $suborder[0];
                $order_url = wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders' ), $order->ID );
                $customer = $order ? $appointment->get_customer( $order ) : $appointment->get_customer();
                $buyer    = $customer->full_name;
                $row['appointment'] = '';
                $row['appointment'] .= sprintf( '<a href="%s">' . __( $buyer, 'woocommerce-appointments' ) . '</a>', esc_url( wcmp_get_vendor_dashboard_endpoint_url( 'appointments', $vendor_appointment->ID ) ) ) .'-'. $appointment->get_status() .'<br>';

                if ( $order ) {
                    $row['appointment'] .= '<a href="' . esc_url( $order_url ) . '">order #' . $order->get_order_number() . '</a> - ' . esc_html( wc_get_order_status_name( $order->get_status() ) );
                } else {
                    $row['appointment'] .= '-';
                }

                $row['when'] =  $appointment->get_start_date() .'<br>'. $appointment->get_duration();

                $row['product'] = "<a href='" . esc_url( $product_url ) . "'>" . esc_html( $product->get_title()) .'<br> <div class="view"><small class="times">×</small> '. $product->get_qty() . "</a>";

                $row['actions'] = sprintf( '<a href="%s">' . __( 'View', 'woocommerce-bookings' ) . '</a>', esc_url( wcmp_get_vendor_dashboard_endpoint_url( 'appointments', $vendor_appointment->ID ) ) );

                $data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval( $requestData['draw'] ), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( count( $vendor_appointments ) ), // total number of records
            "recordsFiltered" => intval( count( $vendor_appointments ) ), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );
        wp_send_json( $json_data );
    }

    public function json_search_appointable_products() {
        ob_start();
        check_ajax_referer( 'search-customers', 'security' );
        if ( ! current_vendor_can( 'manage_appointments' ) ) {
            wp_die( -1 );
        }

        if ( ! empty( $_GET['limit'] ) ) {
            $limit = absint( $_GET['limit'] );
        } else {
            $limit = absint( apply_filters( 'wcmp_afm_json_search_limit', 30 ) );
        }

        $include_ids = ! empty( $_GET['include'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['include'] ) ) : array();
        $exclude_ids = ! empty( $_GET['exclude'] ) ? array_map( 'absint', (array) wp_unslash( $_GET['exclude'] ) ) : array();

        $term       = isset( $_GET['term'] ) ? (string) wc_clean( wp_unslash( $_GET['term'] ) ) : '';
        $data_store = WC_Data_Store::load( 'product' );
        $ids        = $data_store->search_products( $term, '', true, false, $limit );

        $product_objects = array_filter( array_map( 'wc_get_product', $ids ), 'wc_products_array_filter_readable' );
        $products        = array();
        $current_user_id = get_current_user_id();

        foreach ( $product_objects as $product_object ) {
            //print_r($product_object);die;
            // if(get_wcmp_vendor_orders($product_object->id)) {
            //     print_r('vendor');die;
            // } else {
            //     print_r("admin");die;
            // }
            if ( ! $product_object->is_type( 'appointment' ) ) {
                continue;
            }
            $staff_ids        = $product_object->get_staff_ids();
            $personal_product = '';
            if ( $product_object->has_staff() && in_array( $current_user_id, (array) $staff_ids ) ) {
                $personal_product = ' - <strong>' . esc_html( 'assigned to you', 'woocommerce-appointments' ) . '</strong>';
            }
            $products[ $product_object->get_id() ] = rawurldecode( $product_object->get_formatted_name() ) . $personal_product;
        }

        wp_send_json( $products );
    }

    public function add_days_range_callback() {
        ob_start();

        check_ajax_referer( 'add-days-range', 'security' );

        if ( ! current_user_can( 'edit_products' ) || ! wcmp_is_allowed_product_type( 'redq_rental' ) ) {
            wp_die( -1 );
        }

        $i = absint( $_POST['i'] );

        afm()->template->get_template( 'products/rental/html-product-price-calculation.php', array( 'i' => $i ) );
        wp_die();
    }

    public function add_price_discount_callback() {
        ob_start();

        check_ajax_referer( 'add-price-discount', 'security' );

        if ( ! current_user_can( 'edit_products' ) || ! wcmp_is_allowed_product_type( 'redq_rental' ) ) {
            wp_die( -1 );
        }

        $i = absint( $_POST['i'] );

        afm()->template->get_template( 'products/rental/html-product-price-discount.php', array( 'i' => $i ) );
        wp_die();
    }

    public function booking_list() {
        //@TODO filter by bookable product, search by boookable product, sort by allowed columns
        ob_start();

        $current_vendor_id = afm()->vendor_id;
        if ( ! $current_vendor_id || ! current_vendor_can( 'manage_bookings' ) ) {
            wp_die( -1 );
        }
        $requestData = $_REQUEST;
        $enable_ordering = apply_filters( 'wcmp_vendor_dashboard_booking_list_table_orderable_columns', array( 'id', 'booked-product', 'start-date', 'end-date' ) );

        $args = array();

        if ( isset( $requestData['post_status'] ) && $requestData['post_status'] != '' ) {
            $args['post_status'] = $requestData['post_status'];
        }

        if ( isset( $requestData['filter_bookings'] ) && $requestData['filter_bookings'] != '' ) {
            $args['meta_query'] = array(
                array(
                    'key'   => get_post_type( $requestData['filter_bookings'] ) === 'bookable_resource' ? '_booking_resource_id' : '_booking_product_id',
                    'value' => absint( $requestData['filter_bookings'] ),
                ),
            );
        }
        // filter/ordering data
        if ( ! empty( $requestData['search']['value'] ) ) {
            $args['s'] = $requestData['search']['value'];
        }
        if ( isset( $requestData['order'][0]['column'] ) ) {
            $args['orderby'] = $enable_ordering[$requestData['order'][0]['column']];
            $args['order'] = $requestData['order'][0]['dir'];
        }
        $args['offset'] = $requestData['start'];
        $args['posts_per_page'] = $requestData['length'];

        $data = array();
        $vendor_bookings = WCMp_AFM_Booking_Integration::get_vendor_booking_array( $args );
        //wp_send_json($vendor_bookings);
        if ( $vendor_bookings ) {
            foreach ( $vendor_bookings as $vendor_booking ) {
                $row = array();
                $booking = new WC_Booking( $vendor_booking->ID );
                $product_id = $booking->get_product_id();
                $product = $booking->get_product();
                $product_url = wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_edit_product_endpoint', 'vendor', 'general', 'edit-product' ), $product_id );
                //datatable fields
                //booking ID column
                $row['id'] = sprintf( '<a href="%s">' . __( 'Booking #%d', 'woocommerce-bookings' ) . '</a>', esc_url( wcmp_get_vendor_dashboard_endpoint_url( 'bookings', $vendor_booking->ID ) ), esc_html__( $vendor_booking->ID ) );
                //product column
                $resource = $booking->get_resource();
                if ( $product ) {
                    $row['booked-product'] = "<a href='" . esc_url( $product_url ) . "'>" . esc_html( $product->get_title() ) . "</a>";
                    if ( $resource ) {
                        $row['booked-product'] .= ' (<a href="#">' . esc_html( $resource->get_name() ) . '</a>)';
                    }
                } else {
                    $row['booked-product'] = '-';
                }
                //persons column
                if ( ! is_object( $product ) || ! $product->has_persons() ) {
                    $row['persons'] = esc_html__( 'N/A', 'woocommerce-bookings' );
                } else {
                    $row['persons'] = esc_html( array_sum( $booking->get_person_counts() ) );
                }
                //customer column
                $customer = $booking->get_customer();
                $customer_name = esc_html( $customer->name ?: '-' );
                if ( $customer->email ) {
                    $customer_name = '<a href="mailto:' . esc_attr( $customer->email ) . '">' . $customer_name . '</a>';
                }
                $row['booked-by'] = $customer_name;
                //order column
                $bookable_order = $booking->get_order();
                $order_id = is_callable( array( $bookable_order, 'get_id' ) ) ? $bookable_order->get_id() : $bookable_order->id;
                $suborder = get_wcmp_suborders($order_id);
                $order = $suborder[0];
                $order_url = wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders' ), $order->ID );
                if ( $order ) {
                    $row['order'] = '<a href="' . esc_url( $order_url ) . '">#' . $order->get_order_number() . '</a> - ' . esc_html( wc_get_order_status_name( $order->get_status() ) );
                } else {
                    $row['order'] = '-';
                }
                $row['start-date'] = wcmp_date( $booking->get_start_date() );
                $row['end-date'] = wcmp_date( $booking->get_end_date() );
                $row['actions'] = sprintf( '<a href="%s">' . __( 'View', 'woocommerce-bookings' ) . '</a>', esc_url( wcmp_get_vendor_dashboard_endpoint_url( 'bookings', $vendor_booking->ID ) ) );

                $data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval( $requestData['draw'] ), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( count( $vendor_bookings ) ), // total number of records
            "recordsFiltered" => intval( count( $vendor_bookings ) ), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );
        wp_send_json( $json_data );
    }

    public function resources_list() {
        ob_start();

        $current_vendor_id = afm()->vendor_id;
        if ( ! $current_vendor_id || ! current_vendor_can( 'manage_resources' ) ) {
            wp_die( -1 );
        }
        $requestData = $_REQUEST;
        $enable_ordering = apply_filters( 'wcmp_vendor_dashboard_resources_list_table_orderable_columns', array( 'title', 'date' ) );
        $args = array();
        // filter/ordering data
        if ( ! empty( $requestData['search']['value'] ) ) {
            $args['s'] = $requestData['search']['value'];
        }
        if ( isset( $requestData['order'][0]['column'] ) ) {
            $args['orderby'] = $enable_ordering[$requestData['order'][0]['column']];
            $args['order'] = $requestData['order'][0]['dir'];
        }
        $args['offset'] = $requestData['start'];
        $args['posts_per_page'] = $requestData['length'];

        $data = array();
        $vendor_booking_resources = WCMp_AFM_Booking_Integration::get_booking_resources( $args );

        if ( $vendor_booking_resources ) {
            foreach ( $vendor_booking_resources as $booking_resource ) {
                $row = array();
                $resource_url = wcmp_get_vendor_dashboard_endpoint_url( 'resources', $booking_resource->ID );
                $action_label = __( 'View' );
                if ( current_vendor_can( 'add_bookable_resource' ) ) {
                    $action_label = __( 'Edit' );
                }
                $actions = array(
                    'id'     => sprintf( __( 'ID: %d', 'dc-woocommerce-multi-vendor' ), $booking_resource->ID ),
                    'action' => '<a href="' . esc_url( $resource_url ) . '">' . $action_label . '</a>',
                );
                $actions = apply_filters( 'wcmp_vendor_booking_resource_list_row_actions', $actions, $booking_resource );
                $row_actions = array();
                foreach ( $actions as $key => $action ) {
                    $row_actions[] = '<span class="' . esc_attr( $key ) . '">' . $action . '</span>';
                }
                $action_html = '<div class="row-actions">' . implode( ' <span class="divider">|</span> ', $row_actions ) . '</div>';
                //datatable fields
                //resource title column
                $row['title'] = sprintf( "%s%s%s%s", '<a href="' . $resource_url . '">', esc_html( $booking_resource->post_title ), '</a>', $action_html );
                $row['date'] = wcmp_date( get_the_date( "", $booking_resource->ID ) );

                $data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval( $requestData['draw'] ), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( count( $vendor_booking_resources ) ), // total number of records
            "recordsFiltered" => intval( count( $vendor_booking_resources ) ), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );
        wp_send_json( $json_data );
    }

    public function add_bookable_person() {
        check_ajax_referer( 'add-person', 'security' );

        $post_id = intval( $_POST['post_id'] );
        $loop = intval( $_POST['loop'] );

        $person_type = new WC_Product_Booking_Person_Type();
        $person_type->set_parent_id( $post_id );
        $person_type->set_sort_order( $loop );
        $person_type_id = $person_type->save();

        if ( $person_type_id ) {
            afm()->template->get_template( 'products/booking/html-product-booking-persons.php', array( 'post_id' => $post_id, 'loop' => $loop, 'person_type' => $person_type, 'person_type_id' => $person_type_id ) );
        }
        die();
    }

    /**
     * Remove person type.
     */
    public function unlink_bookable_person() {
        check_ajax_referer( 'unlink-person', 'security' );

        $person_type_id = intval( $_POST['person_id'] );
        $person_type = new WC_Product_Booking_Person_Type( $person_type_id );
        $person_type->set_parent_id( 0 );
        $person_type->save();
        wp_die();
    }

    /**
     * Add resource link to product.
     */
    public function add_bookable_resource() {
        check_ajax_referer( 'add-resource', 'security' );

        $id = intval( $_POST['post_id'] );
        $loop = intval( $_POST['loop'] );
        $add_resource_id = intval( $_POST['add_resource_id'] );
        $add_resource_name = wc_clean( $_POST['add_resource_name'] );

        if ( ! $add_resource_id ) {
            $resource = new WC_Product_Booking_Resource();
            $resource->set_name( $add_resource_name );
            $add_resource_id = $resource->save();
        } else {
            $resource = new WC_Product_Booking_Resource( $add_resource_id );
        }

        if ( $add_resource_id ) {
            $product = new WC_Product_Booking( $id );
            $resource_ids = $product->get_resource_ids();

            if ( in_array( $add_resource_name, $resource_ids ) ) {
                wp_send_json( array( 'error' => __( 'The resource has already been linked to this product', 'woocommerce-bookings' ) ) );
            }

            $resource_ids[] = $add_resource_id;
            $product->set_resource_ids( $resource_ids );
            $product->save();

            ob_start();
            afm()->template->get_template( 'products/booking/html-product-booking-resources.php', array( 'id' => $id, 'loop' => $loop, 'resource' => $resource ) );
            wp_send_json( array( 'html' => ob_get_clean() ) );
        }

        wp_send_json( array( 'error' => __( 'Unable to add resource', 'woocommerce-bookings' ) ) );
    }

    /**
     * Remove resource link from product.
     */
    public function remove_bookable_resource() {
        check_ajax_referer( 'remove-resource', 'security' );

        $post_id = absint( $_POST['post_id'] );
        $resource_id = absint( $_POST['resource_id'] );
        $product = new WC_Product_Booking( $post_id );
        $resource_ids = $product->get_resource_ids();
        $resource_ids = array_diff( $resource_ids, array( $resource_id ) );
        $product->set_resource_ids( $resource_ids );
        $product->save();
        wp_die();
    }

    /**
     * Search for customers and return json.
     */
    public function json_search_customers() {
        ob_start();
        check_ajax_referer( 'search-customers', 'security' );
        if ( ! current_vendor_can( 'create_booking' ) ) {
            wp_die( -1 );
        }

        $term = wc_clean( wp_unslash( $_GET['term'] ) );
        $exclude = array();
        $limit = '';

        if ( empty( $term ) ) {
            wp_die();
        }

        $ids = array();
        // Search by ID.
        if ( is_numeric( $term ) ) {
            $customer = new WC_Customer( intval( $term ) );

            // Customer does not exists.
            if ( 0 !== $customer->get_id() ) {
                $ids = array( $customer->get_id() );
            }
        }

        // Usernames can be numeric so we first check that no users was found by ID before searching for numeric username, this prevents performance issues with ID lookups.
        if ( empty( $ids ) ) {
            $data_store = WC_Data_Store::load( 'customer' );

            // If search is smaller than 3 characters, limit result set to avoid
            // too many rows being returned.
            if ( 3 > strlen( $term ) ) {
                $limit = 20;
            }
            $ids = $data_store->search_customers( $term, $limit );
        }

        $found_customers = array();

        if ( ! empty( $_GET['exclude'] ) ) {
            $ids = array_diff( $ids, (array) $_GET['exclude'] );
        }

        foreach ( $ids as $id ) {
            $customer = new WC_Customer( $id );
            /* translators: 1: user display name 2: user ID 3: user email */
            $found_customers[$id] = sprintf(
                esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'woocommerce' ), $customer->get_first_name() . ' ' . $customer->get_last_name(), $customer->get_id(), $customer->get_email()
            );
        }

        wp_send_json( apply_filters( 'wcmp_afm_json_search_found_customers', $found_customers ) );
    }

    public function json_search_valid_bundle_items() {
        check_ajax_referer( 'search-products', 'security' );

        $term = wc_clean( empty( $term ) ? wp_unslash( $_GET['term'] ) : $term );
        if ( empty( $term ) ) {
            wp_die();
        }

        $vendor_id = afm()->vendor_id;
        if ( ! $vendor_id ) {
            wp_die();
        }

        $data_store = WC_Data_Store::load( 'product' );
        $ids = $data_store->search_products( $term, '', false );
        if ( empty( $ids ) ) {
            wp_send_json( $ids );
        }

        if ( ! empty( $_GET['exclude'] ) ) {
            $ids = array_diff( $ids, (array) $_GET['exclude'] );
        }

        global $WCMp;
        $vendor = get_wcmp_vendor( $vendor_id );
        $args = array(
            'post_status' => 'publish',
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
                    'terms'    => array( 'simple', 'variable', 'subscription', 'variable-subscription' ),
                )
            )
        );
        $current_vendor_products = $vendor->get_products( $args );
        $p_ids = wp_list_pluck( $current_vendor_products, 'ID' );
        $ids = array_intersect( $ids, wp_parse_id_list( $p_ids ) );

        if ( ! empty( $_GET['limit'] ) ) {
            $ids = array_slice( $ids, 0, absint( $_GET['limit'] ) );
        }

        $product_objects = array_filter( array_map( 'wc_get_product', $ids ), 'wc_products_array_filter_readable' );
        $products = array();

        foreach ( $product_objects as $product_object ) {
            $products[$product_object->get_id()] = rawurldecode( $product_object->get_formatted_name() );
        }

        wp_send_json( apply_filters( 'wcmp_afm_json_search_found_bundle_items', $products ) );
    }

    public function add_product_to_bundle() {

        check_ajax_referer( 'wc_bundles_add_bundled_product', 'security' );

        $loop = intval( $_POST['id'] );
        // bundled product id
        $post_id = intval( $_POST['post_id'] );
        // product to be added to the bundle
        $product_id = intval( $_POST['product_id'] );
        $item_id = false;
        $toggle = 'open';
        $tabs = WC_PB_Meta_Box_Product_Data::get_bundled_product_tabs();
        $product = wc_get_product( $product_id );
        $title = $product->get_title();
        $sku = $product->get_sku();
        $title = WC_PB_Helpers::format_product_title( $title, $sku, '', true );
        $title = sprintf( _x( '#%1$s: %2$s', 'bundled product admin title', 'woocommerce-product-bundles' ), $product_id, $title );

        $item_data = array();
        $item_availability = '';

        $response = array(
            'markup'  => '',
            'message' => ''
        );

        if ( $product ) {

            if ( in_array( $product->get_type(), array( 'simple', 'variable', 'subscription', 'variable-subscription' ) ) ) {

                if ( ! $product->is_in_stock() ) {
                    $item_availability = '<mark class="outofstock">' . __( 'Out of stock', 'woocommerce' ) . '</mark>';
                }

                ob_start();

                afm()->template->get_template( 'products/bundle/html-product-bundle-items.php', array( 'loop' => $loop, 'product_id' => $product_id, 'item_id' => $item_id, 'tabs' => $tabs, 'title' => $title, 'item_availability' => $item_availability ) );
                $response['markup'] = ob_get_clean();
            } else {
                $response['message'] = __( 'The selected product cannot be bundled. Please select a simple product, a variable product, or a simple/variable subscription.', 'woocommerce-product-bundles' );
            }
        } else {
            $response['message'] = __( 'The selected product is invalid.', 'woocommerce-product-bundles' );
        }

        wp_send_json( $response );
    }

    /**
     * Add an product addon row.
     */
    public function add_product_addon() {
        ob_start();

        check_ajax_referer( 'add-addon', 'security' );

        if ( ! current_user_can( 'edit_products' ) ) {
            wp_die( -1 );
        }

        $loop = absint( $_POST['i'] );

        if ( version_compare( WC_VERSION, '3.0', '>=' ) && defined( 'WC_PRODUCT_ADDONS_VERSION' ) && version_compare( WC_PRODUCT_ADDONS_VERSION, '3.0.4', '>=' ) ) {
            $options = WC_Product_Addons_Admin::get_new_addon_option();
        } else {
            $options = Product_Addon_Admin::get_new_addon_option();
        }
        
        $addon = array(
            'name'        => '',
            'description' => '',
            'required'    => '',
            'type'        => 'checkbox',
            'options'     => array(
                $options,
            ),
        );
        $product_id = absint( $_POST['product_id'] );
        $product_object = wc_get_product( $product_id );
        $product_addons = array_filter( (array) $product_object->get_meta( '_product_addons' ) );
        $exclude_global = $product_object->get_meta( '_product_addons_exclude_global' );
        $product_addon_type = WCMp_AFM_Product_Addons_Integration::get_product_addon_type();

        afm()->template->get_template( 'products/product-addons/html-product-addons.php', array( 'loop' => $loop, 'product_addons' => $product_addons, 'addon' => $addon, 'product_addon_type' => $product_addon_type, 'product_object' => $product_object ) );
        wp_die();
    }

    public function reschedule_auction() {

        check_ajax_referer( 'reschedule-auction', 'security' );

        if ( ! current_user_can( 'edit_published_products' ) || get_wcmp_vendor_settings( 'is_edit_delete_published_product', 'capabilities', 'product' ) !== 'Enable' || empty( wc_clean( $_POST['id'] ) ) ) {
            wp_die( -1 );
        }

        $id = absint( wc_clean( $_POST['id'] ) );
        $product = wc_get_product( $id );
        $product->set_stock_status( 'instock' );

        $bids = YITH_Auctions()->bids;
        $bids->reshedule_auction( $product->get_id() );

        if ( $product->is_closed_for_buy_now() ) {
            yit_save_prop( $product, '_yith_auction_closed_buy_now', 0 );
        }
        yit_delete_prop( $product, '_yith_is_in_overtime', false );

        yit_delete_prop( $product, '_yith_auction_paid_order', false );

        /* Product has a watchlist */
        if ( $product->get_watchlist() ) {
            yit_delete_prop( $product, 'yith_wcact_auction_watchlist', false );
        }

        yit_delete_prop( $product, 'yith_wcact_send_winner_email', false );
        yit_delete_prop( $product, 'yith_wcact_send_admin_winner_email', false );
        yit_delete_prop( $product, 'yith_wcact_send_admin_not_reached_reserve_price', false );
        yit_delete_prop( $product, 'yith_wcact_send_admin_without_any_bids', false );

        //delete winner email user prop (since v2.0.1)
        yit_delete_prop( $product, 'yith_wcact_winner_email_is_send', false );
        yit_delete_prop( $product, 'yith_wcact_winner_email_send_custoner', false );
        yit_delete_prop( $product, 'yith_wcact_winner_email_is_not_send', false );

        wp_die();
    }

    public function resend_winner_email() {

        check_ajax_referer( 'resend-winner-email', 'security' );

        if ( ! current_vendor_can( 'yith_send_winner_email' ) || empty( wc_clean( $_POST['id'] ) ) || WC_Product_Factory::get_product_type( $_POST['id'] ) !== 'auction' ) {
            wp_die( -1 );
        }

        $id = absint( wc_clean( $_POST['id'] ) );
        $product = wc_get_product( $id );

        $instance = YITH_Auctions()->bids;
        $max_bidder = $instance->get_max_bid( $id );

        $user = get_user_by( 'id', $max_bidder->user_id );
        yit_delete_prop( $product, 'yith_wcact_send_winner_email', false );

        WC()->mailer();

        do_action( 'yith_wcact_auction_winner', $product, $user );

        $args = array(
            'id'                  => $id,
            'auctionable_product' => $product,
        );

        ob_start();

        afm()->template->get_template( 'products/yith-auction/html-auction-status.php', $args );
        $templates['resend_winner_email'] = ob_get_clean();

        wp_send_json( $templates );
        wp_die();
    }

    public function auction_list() {
        ob_start();

        $current_vendor_id = afm()->vendor_id;
        if ( ! $current_vendor_id || ! current_vendor_can( 'manage_auctions' ) ) {
            wp_die( -1 );
        }
        $requestData = $_REQUEST;
        $enable_ordering = apply_filters( 'wcmp_vendor_dashboard_auction_list_table_orderable_columns', array( 'name', 'start-date', 'end-date', 'auction-status' ) );

        $args = array();

        if ( isset( $requestData['filter_auctions'] ) && $requestData['filter_auctions'] != '' ) {
            $args['meta_query'] = WCMp_AFM_Yith_Auctionpro_Integration::filter_by_auction_status( wc_clean( $requestData['filter_auctions'] ) );
        }
        // filter/ordering data
        if ( ! empty( $requestData['search']['value'] ) ) {
            $args['s'] = $requestData['search']['value'];
        }
        if ( isset( $requestData['order'][0]['column'] ) ) {
            $args['orderby'] = $enable_ordering[$requestData['order'][0]['column']];
            $args['order'] = $requestData['order'][0]['dir'];
        }
        $args['offset'] = $requestData['start'];
        $args['posts_per_page'] = $requestData['length'];

        $data = array();
        $vendor_auctions = WCMp_AFM_Yith_Auctionpro_Integration::get_vendor_auctionable_products( 'publish', $args );
        $instance = YITH_Auctions()->bids;
        if ( $vendor_auctions ) {
            foreach ( $vendor_auctions as $auction_product ) {
                $row = array();
                $product_id = $auction_product->get_id();
                $product_url = wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_edit_product_endpoint', 'vendor', 'general', 'edit-product' ), $product_id );

                $actions = array(
                    'id'     => sprintf( __( 'ID: %d', 'dc-woocommerce-multi-vendor' ), $product_id ),
                    'action' => '<a href="' . esc_url( $product_url ) . '">' . __( 'Edit' ) . '</a>',
                );

                if ( ! current_user_can( 'edit_published_products' ) || get_wcmp_vendor_settings( 'is_edit_delete_published_product', 'capabilities', 'product' ) !== 'Enable' ) {
                    unset( $actions['action'] );
                }

                $actions = apply_filters( 'wcmp_vendor_yith_auction_list_row_actions', $actions, $auction_product );
                $row_actions = array();
                foreach ( $actions as $key => $action ) {
                    $row_actions[] = '<span class="' . esc_attr( $key ) . '">' . $action . '</span>';
                }
                $action_html = '<div class="row-actions">' . implode( ' <span class="divider">|</span> ', $row_actions ) . '</div>';
                //datatable fields
                //booking ID column
                $row['name'] = sprintf( "%s%s%s%s", '<a href="' . esc_url( $product_url ) . '">', esc_html( $auction_product->get_name() ), '</a>', $action_html );

                $dateinic = yit_get_prop( $auction_product, '_yith_auction_for', true );
                if ( $dateinic ) {
                    $row['start-date'] = date( wc_date_format() . ' ' . wc_time_format(), $dateinic );
                } else {
                    $row['start-date'] = '';
                }

                $dateclose = yit_get_prop( $auction_product, '_yith_auction_to', true );
                if ( $dateclose ) {
                    $row['end-date'] = date( wc_date_format() . ' ' . wc_time_format(), $dateclose );
                } else {
                    $row['end-date'] = '';
                }

                $type = $auction_product->get_auction_status();
                if ( $type === 'non-started' ) {
                    $row['auction-status'] = '<span class="yith-wcact-auction-status yith-auction-non-start">' . esc_html__( 'Not Started', 'yith-auctions-for-woocommerce' ) . '</span>';
                } elseif ( $type === 'started' ) {
                    $row['auction-status'] = '<span class="yith-wcact-auction-status yith-auction-started">' . esc_html__( 'Started', 'yith-auctions-for-woocommerce' ) . '</span>';
                } elseif ( $type === 'finished' ) {
                    $row['auction-status'] = '<span class="yith-wcact-auction-status yith-auction-finished">' . esc_html__( 'Finished', 'yith-auctions-for-woocommerce' ) . '</span>';
                }

                $max_bidder = $instance->get_max_bid( $product_id );
                if ( $max_bidder ) {
                    $user = get_user_by( 'id', $max_bidder->user_id );
                    $username = $user->data->user_nicename;

                    $row['max-bidder'] = esc_html__( 'Max bidder:', 'yith-auctions-for-woocommerce' ) . ' <span>' . $username . '</span>';
                } else {
                    $row['max-bidder'] = esc_html__( 'Max bidder:', 'yith-auctions-for-woocommerce' ) . ' <span>' . esc_html__( 'There is no bid for this product', 'yith-auctions-for-woocommerce' ) . '</span>';
                }

                $data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval( $requestData['draw'] ), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( count( $vendor_auctions ) ), // total number of records
            "recordsFiltered" => intval( count( $vendor_auctions ) ), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );
        wp_send_json( $json_data );
    }

    /**
     * Simple Auction delete bid
     *
     * Function for deleting bid in vendor dashboard
     *
     * @access public
     * @param  array
     * @return string
     *
     */
    function simple_auction_delete_bid() {
        check_ajax_referer( 'SAajax-nonce', 'SA_nonce' );

        $current_vendor_id = afm()->vendor_id;

        if ( ! $current_vendor_id || ! current_user_can( 'edit_product', $_POST["postid"] ) || ! current_vendor_can( 'simple_auction_delete_bid' ) ) {
            wp_die( -1 );
        }

        global $wpdb;
        if ( $_POST["postid"] && $_POST["logid"] ) {
            $product_data = wc_get_product( $_POST["postid"] );
            $log = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "simple_auction_log WHERE id=%d", $_POST["logid"] ) );
            if ( ! is_null( $log ) ) {
                if ( $product_data->get_auction_type() == 'normal' ) {
                    if ( ($log->bid == $product_data->get_auction_current_bid()) && ($log->userid == $product_data->get_auction_current_bider()) ) {

                        if ( $product_data->get_auction_relisted() ) {
                            $time = 'AND `date` > \'' . $product_data->get_auction_relisted() . '\'';
                        } else {
                            $time = ' ';
                        }

                        $newbid = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "simple_auction_log WHERE auction_id =%d " . $time . " ORDER BY  `date` desc, `bid` desc LIMIT 1, 1 ", $_POST["postid"] ) );
                        if ( ! is_null( $newbid ) ) {
                            update_post_meta( $_POST["postid"], '_auction_current_bid', $newbid->bid );
                            update_post_meta( $_POST["postid"], '_auction_current_bider', $newbid->userid );
                            delete_post_meta( $_POST["postid"], '_auction_max_bid' );
                            delete_post_meta( $_POST["postid"], '_auction_max_current_bider' );
                            $new_max_bider_id = $newbid->userid;
                        } else {
                            delete_post_meta( $_POST["postid"], '_auction_current_bid' );
                            delete_post_meta( $_POST["postid"], '_auction_current_bider' );
                            delete_post_meta( $_POST["postid"], '_auction_max_bid' );
                            delete_post_meta( $_POST["postid"], '_auction_max_current_bider' );
                            $new_max_bider_id = false;
                        }
                        $wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "simple_auction_log WHERE id= %d", $_POST["logid"] ) );
                        update_post_meta( $_POST["postid"], '_auction_bid_count', intval( $product_data->get_auction_bid_count() - 1 ) );
                        $return['action'] = 'deleted';
                        do_action( 'woocommerce_simple_auction_delete_bid', array( 'product_id' => $_POST["postid"], 'delete_user_id' => $log->userid, 'new_max_bider_id ' => $new_max_bider_id ) );
                    } else {
                        $wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "simple_auction_log WHERE id= %d", $_POST["logid"] ) );
                        update_post_meta( $_POST["postid"], '_auction_bid_count', intval( $product_data->get_auction_bid_count() - 1 ) );
                        $wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "simple_auction_log WHERE id= %d", $_POST["logid"] ) );
                        do_action( 'woocommerce_simple_auction_delete_bid', array( 'product_id' => $_POST["postid"], 'delete_user_id' => $log->userid ) );
                        $return['action'] = 'deleted';
                    }
                } elseif ( $product_data->get_auction_type() == 'reverse' ) {
                    if ( ($log->bid == $product_data->get_auction_current_bid()) && ($log->userid == $product_data->get_auction_current_bider()) ) {

                        if ( $product_data->get_auction_relisted() ) {
                            $time = 'AND `date` > \'' . $product_data->get_auction_relisted() . '\'';
                        } else {
                            $time = ' ';
                        }

                        $newbid = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "simple_auction_log WHERE auction_id =%d  " . $time . "  ORDER BY  `date` desc , `bid`  asc LIMIT 1, 1 ", $_POST["postid"] ) );
                        if ( ! is_null( $newbid ) ) {
                            update_post_meta( $_POST["postid"], '_auction_current_bid', $newbid->bid );
                            update_post_meta( $_POST["postid"], '_auction_current_bider', $newbid->userid );
                            delete_post_meta( $_POST["postid"], '_auction_max_bid' );
                            delete_post_meta( $_POST["postid"], '_auction_max_current_bider' );
                            $new_max_bider_id = $newbid->userid;
                        } else {
                            delete_post_meta( $_POST["postid"], '_auction_current_bid' );
                            delete_post_meta( $_POST["postid"], '_auction_current_bider' );
                            $new_max_bider_id = false;
                        }
                        $wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "simple_auction_log WHERE id= %d", $_POST["logid"] ) );
                        update_post_meta( $_POST["postid"], '_auction_bid_count', intval( $product_data->get_auction_bid_count() - 1 ) );
                        $return['action'] = 'deleted';
                        do_action( 'woocommerce_simple_auction_delete_bid', array( 'product_id' => $_POST["postid"], 'delete_user_id' => $log->userid, 'new_max_bider_id ' => $new_max_bider_id ) );
                    } else {
                        $wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "simple_auction_log  WHERE id= %d", $_POST["logid"] ) );
                        update_post_meta( $_POST["postid"], '_auction_bid_count', intval( $product_data->get_auction_bid_count() - 1 ) );
                        do_action( 'woocommerce_simple_auction_delete_bid', array( 'product_id' => $_POST["postid"], 'delete_user_id' => $log->userid ) );
                        $return['action'] = 'deleted';
                    }
                }
                $product = wc_get_product( $_POST["postid"] );
                if ( $product ) {
                    $return['auction_current_bid'] = wc_price( $product->get_curent_bid() );
                    if ( $product->get_auction_current_bider() ) {
                        $return['auction_current_bider'] = '<a href="' . get_edit_user_link( $product->get_auction_current_bider() ) . '">' . get_userdata( $product->get_auction_current_bider() )->display_name . '</a>';
                    }
                }

                if ( isset( $return ) ) {
                    wp_send_json( $return );
                }

                wp_die();
            }
        }
        $return['action'] = 'failed';
        if ( isset( $return ) ) {
            wp_send_json( $return );
        }

        wp_die();
    }

    /**
     * Load Simple Auction Products
     * 
     */
    public function simple_auction_list() {
        ob_start();

        $current_vendor_id = afm()->vendor_id;
        if ( ! $current_vendor_id || ! current_vendor_can( 'manage_simple_auctions' ) ) {
            wp_die( -1 );
        }
        $requestData = $_REQUEST;
        $enable_ordering = apply_filters( 'wcmp_vendor_dashboard_auction_list_table_orderable_columns', array( 'name' ) );

        $args = array();

        if ( isset( $requestData['filter_auctions'] ) && $requestData['filter_auctions'] != '' ) {
            $args['meta_query'] = WCMp_AFM_Simple_Auction_Integration::filter_by_auction_status( wc_clean( $requestData['filter_auctions'] ) );
        }
        // filter/ordering data
        if ( ! empty( $requestData['search']['value'] ) ) {
            $args['s'] = $requestData['search']['value'];
        }
        if ( isset( $requestData['order'][0]['column'] ) ) {
            $args['orderby'] = $enable_ordering[$requestData['order'][0]['column']];
            $args['order'] = $requestData['order'][0]['dir'];
        }
        $args['offset'] = $requestData['start'];
        $args['posts_per_page'] = $requestData['length'];

        $data = array();
        $vendor_auctions = WCMp_AFM_Simple_Auction_Integration::get_vendor_auctionable_products( 'publish', $args );
        if ( $vendor_auctions ) {
            foreach ( $vendor_auctions as $auction_product ) {
                $row = array();
                $product_id = $auction_product->get_id();
                $product_url = wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_edit_product_endpoint', 'vendor', 'general', 'edit-product' ), $product_id );

                $actions = array(
                    'id'     => sprintf( __( 'ID: %d', 'dc-woocommerce-multi-vendor' ), $product_id ),
                    'action' => '<a href="' . esc_url( $product_url ) . '">' . __( 'Edit' ) . '</a>',
                );

                if ( ! current_user_can( 'edit_published_products' ) || get_wcmp_vendor_settings( 'is_edit_delete_published_product', 'capabilities', 'product' ) !== 'Enable' ) {
                    unset( $actions['action'] );
                }

                $actions = apply_filters( 'wcmp_vendor_simple_auction_list_row_actions', $actions, $auction_product );
                $row_actions = array();
                foreach ( $actions as $key => $action ) {
                    $row_actions[] = '<span class="' . esc_attr( $key ) . '">' . $action . '</span>';
                }
                $action_html = '<div class="row-actions">' . implode( ' <span class="divider">|</span> ', $row_actions ) . '</div>';
                //datatable fields
                //booking ID column
                $row['name'] = sprintf( "%s%s%s%s", '<a href="' . esc_url( $product_url ) . '">', esc_html( $auction_product->get_name() ), '</a>', $action_html );

                $dateinic = get_post_meta( $product_id, '_auction_dates_from', true );
                if ( $dateinic ) {
                    $row['start-date'] = date( wc_date_format() . ' ' . wc_time_format(), strtotime( $dateinic ) );
                } else {
                    $row['start-date'] = '';
                }

                $dateclose = get_post_meta( $product_id, '_auction_dates_to', true );
                if ( $dateclose ) {
                    $row['end-date'] = date( wc_date_format() . ' ' . wc_time_format(), strtotime( $dateclose ) );
                } else {
                    $row['end-date'] = '';
                }

                $row['auction-status'] = WCMp_AFM_Simple_Auction_Integration::get_auction_status_html( $product_id );

                $max_bid = "";
                if ( $auction_product->is_closed() === TRUE && $auction_product->is_started() === TRUE && $auction_product->get_auction_closed() !== 3 && $auction_product->get_auction_current_bider() ) {
                    $max_bid = sprintf( "%s %s %s %s", __( 'Maximum bid is', 'wc_simple_auctions' ), $auction_product->get_curent_bid(), __( 'by', 'wc_simple_auctions' ), get_userdata( $auction_product->get_auction_current_bider() )->display_name );
                } elseif ( $auction_product->is_closed() === FALSE && $auction_product->is_started() === TRUE && $auction_product->get_auction_proxy() && $auction_product->get_auction_max_bid() && $auction_product->get_auction_max_current_bider() ) {
                    $max_bid = sprintf( "%s %s %s %s", __( 'Maximum bid is', 'wc_simple_auctions' ), $auction_product->get_auction_max_bid(), __( 'by', 'wc_simple_auctions' ), get_userdata( $auction_product->get_auction_max_current_bider() )->display_name );
                }

                $row['max-bid'] = $max_bid;

                $data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval( $requestData['draw'] ), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( count( $vendor_auctions ) ), // total number of records
            "recordsFiltered" => intval( count( $vendor_auctions ) ), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );
        wp_send_json( $json_data );
    }

    public function rental_quotes_list() {
        ob_start();

        $current_vendor_id = afm()->vendor_id;
        $vendor = get_wcmp_vendor( $current_vendor_id );
        if ( ! $current_vendor_id || ! $vendor || ! apply_filters( 'vendor_can_access_request_quote', true, $current_vendor_id ) ) {
            wp_die( -1 );
        }

        $vendor_products = $vendor->get_products(array('post_status' => 'any'));

        $requestData = $_REQUEST;
        $data = array();
        if ( ! empty( $vendor_products ) ) {
            $vendor_product_ids = wp_list_pluck( $vendor_products, 'ID' );
            $args = array(
                'posts_per_page'   => -1,
                'orderby'          => 'date',
                'order'            => 'DESC',
                'meta_key'         => '_product_id',
                'post_type'        => 'request_quote',
                'post_status'      => 'any',
                'meta_query'       => array(
                    array(
                        'key'     => '_product_id',
                        'value'   => $vendor_product_ids,
                        'compare' => 'IN',
                    ),
                ),
                'suppress_filters' => true
            );
            $vendor_total_rental_quotes = get_posts( $args );
            $args['offset'] = $requestData['start'];
            $args['posts_per_page'] = $requestData['length'];
            $vendor_rental_quotes = get_posts( $args );

            if ( $vendor_rental_quotes ) {
                foreach ( $vendor_rental_quotes as $quote_single ) {
                    $quote_single_meta = json_decode( get_post_meta( $quote_single->ID, 'order_quote_meta', true ), true );
                    $forms = array();

                    foreach ( $quote_single_meta as $key => $meta ) {
                        if ( array_key_exists( 'forms', $meta ) ) {
                            $forms = $meta['forms'];
                        }
                    }

                    $row = array();
                    $row['quote'] = "<a href='" . esc_url( wcmp_get_vendor_dashboard_endpoint_url( 'quote-details', $quote_single->ID ) ) . "'><strong>#" . esc_html__( $quote_single->ID ) . "</strong></a> " . esc_html__( 'by', 'wcmp-afm' ) . " " . $forms['quote_first_name'] . " " . $forms['quote_last_name'];
                    $row['status'] = ucfirst( substr( get_post_status( $quote_single->ID ), 6 ) );

                    $product_id = get_post_meta( $quote_single->ID, 'add-to-cart', true );
                    $product_title = get_the_title( $product_id );
                    $product_url = wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_edit_product_endpoint', 'vendor', 'general', 'edit-product' ), $product_id );

                    $row['product'] = "<a href='" . esc_url( $product_url ) . "'>" . esc_html__( $product_title ) . "</a>";

                    $user_email = isset( $forms['quote_email'] ) ? $forms['quote_email'] : "";
                    $row['email'] = "<a href='mailto:" . $user_email . "'>$user_email</a>";
                    $row['date'] = date_i18n( get_option( 'date_format' ), strtotime( $quote_single->post_date ) );

                    $data[] = $row;
                }
            }
        }


        $json_data = array(
            "draw"            => intval( $requestData['draw'] ), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( count( $vendor_total_rental_quotes ) ), // total number of records
            "recordsFiltered" => intval( count( $vendor_total_rental_quotes ) ), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );
        wp_send_json( $json_data );
    }

    public function rental_quote_reply() {
        ob_start();

        check_ajax_referer( 'add-message', 'security' );

        $current_vendor_id = afm()->vendor_id;
        $vendor = get_wcmp_vendor( $current_vendor_id );
        if ( empty( $_POST['quote_id'] ) || ! $current_vendor_id || ! $vendor || ! wcmp_is_allowed_product_type( 'redq_rental' ) || ! apply_filters( 'is_vendor_can_add_quote_message', true, $current_vendor_id ) || ! apply_filters( 'vendor_can_access_request_quote', true, $current_vendor_id ) ) {
            wp_die( -1 );
        }

        $from_name = $vendor->user_data->user_nicename;
        $from_email = $vendor->user_data->user_email;

        $to_author_id = get_post_field( 'post_author', $_POST['quote_id'] );
        $to_email = get_the_author_meta( 'user_email', $to_author_id );

        if ( ! empty( $_POST['message'] ) ) {
            $time = current_time( 'mysql' );

            if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
                //check ip from share internet
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
                //to check ip is pass from proxy
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
            $ip = apply_filters( 'redq_rental_get_ip', $ip );

            $data = array(
                'comment_post_ID'      => $_POST['quote_id'],
                'comment_author'       => $vendor->user_data->user_nicename,
                'comment_author_email' => $vendor->user_data->user_email,
                'comment_author_url'   => $vendor->user_data->user_url,
                'comment_content'      => $_POST['message'],
                'comment_type'         => 'quote_message',
                'comment_parent'       => 0,
                'user_id'              => $current_vendor_id,
                'comment_author_IP'    => $ip,
                'comment_agent'        => $_SERVER['HTTP_USER_AGENT'],
                'comment_date'         => $time,
                'comment_approved'     => 1,
            );

            $comment_id = wp_insert_comment( $data );

            if ( $comment_id ) {
                $comment = get_comment( $comment_id );

                $subject = "New reply for your quote request";
                $reply_message = $_POST['message'];
                $data_object = array(
                    'reply_message' => $reply_message,
                    'quote_id'      => $_POST['quote_id'],
                );

                // Send the mail to the customer
                $email = new RnB_Email();
                $email->owner_reply_message( $to_email, $subject, $from_email, $from_name, $data_object );

                $list_class = 'message-list';
                $content_class = 'quote-message-content';
                if ( $comment->user_id === get_post_field( 'post_author', $_POST['quote_id'] ) ) {
                    $list_class .= ' customer';
                    $content_class .= ' customer';
                }
                ?>
                <li class="<?php echo $list_class ?>">
                    <div class="<?php echo $content_class ?>">
                        <?php echo wpautop( wptexturize( wp_kses_post( $comment->comment_content ) ) ); ?>
                    </div>
                    <p class="meta"><?php printf( __( 'added on %1$s at %2$s', 'wcmp-afm' ), date_i18n( wc_date_format(), strtotime( $comment->comment_date ) ), date_i18n( wc_time_format(), strtotime( $comment->comment_date ) ) ); ?>
                        <?php printf( ' ' . __( 'by %s', 'wcmp-afm' ), $comment->comment_author ); ?>
                    </p>
                </li><?php
            }
        } else {
            wp_die( __( 'Invalid product status.', 'wcmp-afm' ) );
        }
        wp_die();
    }

    public function rental_update_quote() {
        ob_start();

        check_ajax_referer( 'update-quote', 'security' );

        $current_vendor_id = afm()->vendor_id;
        $vendor = get_current_vendor();
        if ( empty( $_POST['quote_id'] ) || ! $current_vendor_id || ! $vendor || ! wcmp_is_allowed_product_type( 'redq_rental' ) || ! apply_filters( 'is_vendor_can_add_quote_message', true, $current_vendor_id ) || ! apply_filters( 'vendor_can_access_request_quote', true, $current_vendor_id ) ) {
            wp_die( -1 );
        }
        $quote_id = absint( $_POST['quote_id'] );
        $quote_post = get_post( $quote_id );

        $data = array();
        parse_str( $_POST['data'], $data );

        $from_name = $vendor->user_data->user_nicename;
        $from_email = $vendor->user_data->user_email;

        $to_author_id = get_post_field( 'post_author', $quote_id );
        $to_email = get_the_author_meta( 'user_email', $to_author_id );

        if ( isset( $data['post_status'] ) && $data['post_status'] !== $quote_post->post_status ) {
            $update_quote = array(
                'ID'          => $quote_id,
                'post_status' => $data['post_status'],
            );
            wp_update_post( $update_quote );

            $subject = ( $quote_post->post_status === 'quote-accepted' ) ? "Congratulations! Your quote request has been accepted" : "Your quote request status has been updated";
            $data_object = array(
                'quote_id' => $quote_id,
            );

            // Send the mail to the customer
            $email = new RnB_Email();
            $email->quote_accepted_notify_customer( $to_email, $subject, $from_email, $from_name, $data_object );
        }
        if ( isset( $data['quote_price'] ) ) {
            update_post_meta( $quote_id, '_quote_price', $data['quote_price'] );
        }
        wp_send_json( array( 'message' => __( 'Quote successfully updated and emailed to the customer', 'wcmp-afm' ) ) );
        wp_die();
    }

    public function subscription_list() {
        //@TODO filter by subscription product, search by subscription product, sort by allowed columns
        ob_start();

        $current_vendor_id = afm()->vendor_id;
        if ( ! $current_vendor_id || ! current_vendor_can( 'manage_subscriptions' ) ) {
            wp_die( -1 );
        }
        $requestData = $_REQUEST;
        $enable_ordering = apply_filters( 'wcmp_vendor_dashboard_subscription_list_table_orderable_columns', array( 0 => 'order_title', 4 => 'start_date', 8 => 'end_date' ) );

        $args = array();

        if ( isset( $requestData['post_status'] ) && $requestData['post_status'] != '' ) {
            $args['post_status'] = $requestData['post_status'];
        }

        // filter/ordering data
        if ( ! empty( $requestData['search']['value'] ) ) {
            $args['s'] = $requestData['search']['value'];
        }
        if ( isset( $requestData['order'][0]['column'] ) ) {
            $args['orderby'] = $enable_ordering[$requestData['order'][0]['column']];
            $args['order'] = $requestData['order'][0]['dir'];
        }
        $args['offset'] = $requestData['start'];
        $args['posts_per_page'] = $requestData['length'];

        $data = array();
        $vendor_subscriptions = WCMp_AFM_Subscription_Integration::get_vendor_subscription_array( $args );
        //wp_send_json($vendor_subscriptions);
        if ( $vendor_subscriptions ) {
            foreach ( $vendor_subscriptions as $vendor_subscription ) {
                $row = array();
                $the_subscription = new WC_Subscription( $vendor_subscription );

                //datatable fields
                // For column status
                $row['status'] = sprintf( '<mark class="%s tips" data-tip="%s">%s</mark>', sanitize_title( $the_subscription->get_status() ), wcs_get_subscription_status_name( $the_subscription->get_status() ), wcs_get_subscription_status_name( $the_subscription->get_status() ) );

                // For column order_title
                $customer_tip = '';
                $column_content = '';

                // This is to stop PHP from complaining
                $username = '';

                if ( $the_subscription->get_user_id() && ( false !== ( $user_info = get_userdata( $the_subscription->get_user_id() ) ) ) ) {
                    if ( $the_subscription->get_billing_first_name() || $the_subscription->get_billing_last_name() ) {
                        $username .= esc_html( ucfirst( $the_subscription->get_billing_first_name() ) . ' ' . ucfirst( $the_subscription->get_billing_last_name() ) );
                    } elseif ( $user_info->first_name || $user_info->last_name ) {
                        $username .= esc_html( ucfirst( $user_info->first_name ) . ' ' . ucfirst( $user_info->last_name ) );
                    } else {
                        $username .= esc_html( ucfirst( $user_info->display_name ) );
                    }
                } elseif ( $the_subscription->get_billing_first_name() || $the_subscription->get_billing_last_name() ) {
                    $username = trim( $the_subscription->get_billing_first_name() . ' ' . $the_subscription->get_billing_last_name() );
                }
                // translators: $1: is opening link, $2: is subscription order number, $3: is closing link tag, $4: is user's name
                $column_content .= sprintf( _x( '%1$s#%2$s%3$s for %4$s', 'Subscription title on admin table. (e.g.: #211 for John Doe)', 'woocommerce-subscriptions' ), '<a href="' . esc_url( wcmp_get_vendor_dashboard_endpoint_url( 'subscriptions', $the_subscription->get_order_number() ) ) . '">', '<strong>' . esc_attr( $the_subscription->get_order_number() ) . '</strong>', '</a>', $username );

                $row['order_title'] = $column_content;

                // For column order_items
                $column_content = '';
                // Display either the item name or item count with a collapsed list of items
                $subscription_items = $the_subscription->get_items();
                switch ( count( $subscription_items ) ) {
                    case 0 :
                        $column_content .= '&ndash;';
                        break;
                    case 1 :
                        foreach ( $subscription_items as $item ) {
                            $column_content .= self::get_item_display( $item, $the_subscription );
                        }
                        break;
                    default :
                        $column_content .= '<a href="#" class="show_order_items">' . esc_html( apply_filters( 'woocommerce_admin_order_item_count', sprintf( _n( '%d item', '%d items', $the_subscription->get_item_count(), 'woocommerce-subscriptions' ), $the_subscription->get_item_count() ), $the_subscription ) ) . '</a>';
                        $column_content .= '<table class="order_items" cellspacing="0">';

                        foreach ( $subscription_items as $item ) {
                            $column_content .= self::get_item_display( $item, $the_subscription, 'row' );
                        }

                        $column_content .= '</table>';
                        break;
                }

                $row['order_items'] = $column_content;

                // For column recurring_total
                $column_content = esc_html( strip_tags( $the_subscription->get_formatted_order_total() ) );

                // translators: placeholder is the display name of a payment gateway a subscription was paid by
                $column_content .= '<small class="meta">' . esc_html( sprintf( __( 'Via %s', 'woocommerce-subscriptions' ), $the_subscription->get_payment_method_to_display() ) ) . '</small>';
                $row['recurring_total'] = $column_content;

                // For column start_date
                $row['start_date'] = self::get_date_column_content( $the_subscription, 'start_date' );

                // For column trial_end_date
                $row['trial_end_date'] = self::get_date_column_content( $the_subscription, 'trial_end_date' );

                // For column next_payment_date
                $row['next_payment_date'] = self::get_date_column_content( $the_subscription, 'next_payment_date' );

                // For column last_payment_date
                $row['last_payment_date'] = self::get_date_column_content( $the_subscription, 'last_payment_date' );

                // For column end_date
                $row['end_date'] = self::get_date_column_content( $the_subscription, 'end_date' );

                //$row['actions'] = sprintf( '<a href="%s">' . __( 'View', 'woocommerce-bookings' ) . '</a>', esc_url( wcmp_get_vendor_dashboard_endpoint_url( 'bookings', $vendor_booking->ID ) ) );

                $data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval( $requestData['draw'] ), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( count( $vendor_subscriptions ) ), // total number of records
            "recordsFiltered" => intval( count( $vendor_subscriptions ) ), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );
        wp_send_json( $json_data );
    }

    /**
     * Get the HTML for an order item to display on the Subscription list table.
     *
     * @param array $actions
     * @param object $post
     * @return array
     */
    protected static function get_item_display( $item, $the_subscription ) {

        $_product = apply_filters( 'woocommerce_order_item_product', $the_subscription->get_product_from_item( $item ), $item );
        $item_meta_html = self::get_item_meta_html( $item, $_product );

        $item_html = '<div class="order-item">';
        $item_html .= wp_kses( self::get_item_name_html( $item, $_product ), array( 'a' => array( 'href' => array() ) ) );

        if ( $item_meta_html ) {
            $item_html .= wcs_help_tip( $item_meta_html );
        }

        $item_html .= '</div>';

        return $item_html;
    }

    /**
     * Get the HTML for order item meta to display on the Subscription list table.
     *
     * @param WC_Order_Item $item
     * @param WC_Product $product
     * @return string
     */
    protected static function get_item_name_html( $item, $_product, $include_quantity = 'include_quantity' ) {

        $item_quantity = absint( $item['qty'] );

        $item_name = '';

        if ( wc_product_sku_enabled() && $_product && $_product->get_sku() ) {
            $item_name .= $_product->get_sku() . ' - ';
        }

        $item_name .= apply_filters( 'woocommerce_order_item_name', $item['name'], $item, false );
        $item_name = esc_html( $item_name );

        if ( 'include_quantity' === $include_quantity && $item_quantity > 1 ) {
            $item_name = sprintf( '%s &times; %s', absint( $item_quantity ), $item_name );
        }

        if ( $_product ) {
            $product_url = wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_edit_product_endpoint', 'vendor', 'general', 'edit-product' ), ( $_product->is_type( 'variation' ) ) ? wcs_get_objects_property( $_product, 'parent_id' ) : $_product->get_id() );

            $item_name = sprintf( '<a href="%s">%s</a>', $product_url, $item_name );
        }

        return $item_name;
    }

    /**
     * Get the HTML for order item meta to display on the Subscription list table.
     *
     * @param WC_Order_Item $item
     * @param WC_Product $product
     * @return string
     */
    protected static function get_item_meta_html( $item, $_product ) {

        if ( WC_Subscriptions::is_woocommerce_pre( '3.0' ) ) {
            $item_meta = wcs_get_order_item_meta( $item, $_product );
            $item_meta_html = $item_meta->display( true, true );
        } else {
            $item_meta_html = wc_display_item_meta( $item, array(
                'before'    => '',
                'after'     => '',
                'separator' => '',
                'echo'      => false,
                ) );
        }

        return $item_meta_html;
    }

    /**
     * Return the content for a date column on the Edit Subscription screen
     *
     * @param WC_Subscription $subscription
     * @param string $column
     * @return string
     * @since 2.3.0
     */
    public static function get_date_column_content( $subscription, $column ) {

        $date_type_map = array( 'start_date' => 'date_created', 'last_payment_date' => 'last_order_date_created' );
        $date_type = array_key_exists( $column, $date_type_map ) ? $date_type_map[$column] : $column;

        if ( 0 == $subscription->get_time( $date_type, 'gmt' ) ) {
            $column_content = '-';
        } else {
            $column_content = sprintf( '<time class="%s" title="%s">%s</time>', esc_attr( $column ), esc_attr( date( __( 'Y/m/d g:i:s A', 'woocommerce-subscriptions' ), $subscription->get_time( $date_type, 'site' ) ) ), esc_html( $subscription->get_date_to_display( $date_type ) ) );

            if ( 'next_payment_date' == $column && $subscription->payment_method_supports( 'gateway_scheduled_payments' ) && ! $subscription->is_manual() && $subscription->has_status( 'active' ) ) {
                $column_content .= '<div class="woocommerce-help-tip" data-tip="' . esc_attr__( 'This date should be treated as an estimate only. The payment gateway for this subscription controls when payments are processed.', 'woocommerce-subscriptions' ) . '"></div>';
            }
        }

        return $column_content;
    }

    /********************* WCMp vendor dashboard assign-affiliate endpoint response *****************************/
    public function request_affiliate_vendor_action_calback(){

      $current_user_id = array();
      $error_msg = array();
      $request_affiliate_email = $_POST['request_affiliate'];
      $user_details = get_user_by( 'email', $request_affiliate_email );
      if( $user_details ){
      

          $affiliate = affiliate_wp()->affiliates->get_by( 'user_id', $user_details->ID );

          $current_vendor = get_current_vendor();
          $current_user_id[] = $current_vendor->id;

          if( $affiliate ){

            $affiliate_assign_vendor = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'affiliate_assign_vendor', true );
            if( in_array(get_current_vendor_id(), $affiliate_assign_vendor) ){
                $error_msg['error'] = 'You are already assign with this affilliate';
            } else {

                $vendor_assign_on_affiliate = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'affiliate_assign_vendor', true );

                $vendor_selected_affiliate = in_array( $current_vendor->id , $vendor_assign_on_affiliate )? $vendor_assign_on_affiliate : ( isset( $vendor_assign_on_affiliate )? array_merge( $vendor_assign_on_affiliate, $current_user_id ) : $current_user_id );
                affwp_update_affiliate_meta( $affiliate->affiliate_id, 'affiliate_assign_vendor', $vendor_selected_affiliate );
            }
            
          } else {
              $new_affiliate_id = affwp_add_affiliate( array( 'user_id' => $user_details->data->ID, 'status' => 'pending' ) );
              affwp_add_affiliate_meta( $new_affiliate_id, 'affiliate_assign_vendor', $current_user_id );
          }
      } else {
        $error_msg['no_user'] = 'This is not an user';
      }
      wp_send_json( $error_msg );
      die;
    }


    /******************** Request admin to active the status  **********************************/
    public function request_affiliate_status_changed(){
      affwp_set_affiliate_status( $_POST['data_affiliate'], array( 'status' => 'active' ) );
      affwp_update_affiliate( array( 'affiliate_id' => $_POST['data_affiliate'], 'status' => 'active' ) );
    }

    /****************** Vendor can delete the affiliate **************************/
    public function request_affiliate_delete_vendor(){
     $affiliate_assign_vendor = affwp_get_affiliate_meta( $_POST['data_affiliates'], 'affiliate_assign_vendor', true );
        $new_assign = array();
        foreach ($affiliate_assign_vendor as $key => $value) {
          if( get_current_vendor_id() == $value  ){
            unset( $key );
          } else {
            $new_assign[] = $value;
          }
        } 
        affwp_update_affiliate_meta( $_POST['data_affiliates'] , 'affiliate_assign_vendor', $new_assign );
    }

}
