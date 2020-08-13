<?php
/**
 * WCMp Advanced Frontend Manager
 *
 * WooCommerce Product Bundles Support
 *
 * @author WC Marketplace
 * @package WCMp_AFM/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Product_Bundle_Integration {

    protected $id = null;
    protected $tabs = array();
    protected $bundled_product = null;
    protected $plugin = 'product-bundle';

    public function __construct() {
        $this->tabs = $this->set_additional_tabs();

        add_action( 'wcmp_product_type_options', array( $this, 'product_bundle_additional_product_type_options' ) );

        // Bundled Product Additional Tabs
        add_filter( 'wcmp_product_data_tabs', array( $this, 'product_bundle_additional_tabs' ) );
        add_action( 'wcmp_product_tabs_content', array( $this, 'product_bundle_additional_tabs_content' ) );
        // Adds a tooltip to the Manage Stock option.
        add_action( 'wcmp_afm_product_options_stock_description', array( $this, 'product_bundle_stock_note' ) );

        // add 'bundle' to allowed product type list for showing pricing, tax (if enabled), inventory contents
        add_filter( 'general_tab_pricing_section', array( $this, 'allow_bundle_type' ) );
        add_filter( 'general_tab_tax_section', array( $this, 'allow_bundle_type' ) );
        add_filter( 'inventory_tab_manage_stock_section', array( $this, 'allow_bundle_type' ) );

        add_action( 'wcmp_afm_after_inventory_section_ends', array( $this, 'sold_individually_option' ) );
        add_action( 'wcmp_afm_product_options_advanced', array( $this, 'form_location_option' ) );

        add_filter( 'wcmp_advance_product_script_params', array( $this, 'add_localize_params' ) );

        add_action( 'wcmp_process_product_object', array( $this, 'process_bundle_data' ), 20 );
    }

    //this will be called from the main Integration class after WCMp_AFM_Add_Product_Endpoint class constructor executed
    public function set_props( $id ) {
        $this->id = $id;

        //after setting id get the bundled product
        $this->bundled_product = new WC_Product_Bundle( $this->id );
    }

    protected function set_additional_tabs() {
        global $WCMp;
        $bundle_tabs = array();

        $bundle_tabs['bundled_products'] = array(
            'p_type'   => 'bundle',
            'label'    => __( 'Bundled Products', 'woocommerce-product-bundles' ),
            'target'   => 'bundled_products_data',
            'class'    => array( 'show_if_bundle' ),
            'priority' => '44',
        );
        return $bundle_tabs;
    }

    public function product_bundle_additional_product_type_options( $options ) {
        $options['downloadable']['wrapper_class'] .= ' show_if_bundle';
        $options['virtual']['wrapper_class'] .= ' show_if_bundle';
        return $options;
    }

    public function product_bundle_additional_tabs( $product_tabs ) {
        if ( isset( $product_tabs['inventory']['class'] ) ) {
            $product_tabs['inventory']['class'][] = 'show_if_bundle';
        }
        return array_merge( $product_tabs, $this->tabs );
    }

    public function product_bundle_additional_tabs_content() {
        foreach ( $this->tabs as $key => $tab ) {
            afm()->template->get_template( 'products/bundle/html-product-data-' . str_replace( '_', '-', $key ) . '.php', array( 'id' => $this->id, 'tab' => $tab['target'], 'self' => $this, 'bundled_product' => $this->bundled_product ) );
        }
        return;
    }

    public function product_bundle_stock_note() {
        ob_start();
        ?>
        <span class="bundle_stock_msg img_tip show_if_bundle" data-desc="<?php esc_attr_e( 'By default, the sale of a product within a bundle has the same effect on its stock as an individual sale. There are no separate inventory settings for bundled items. However, managing stock at bundle level can be very useful for allocating bundle stock quota, or for keeping track of bundled item sales.', 'woocommerce-product-bundles' ); ?>"></span>
        <?php
        ob_end_flush();
    }

    public function allow_bundle_type( $allowed_types ) {
        $allowed_types[] = 'bundle';
        return $allowed_types;
    }

    public function sold_individually_option() {

        $sold_individually = $this->bundled_product->get_sold_individually( 'edit' );
        $sold_individually_context = $this->bundled_product->get_sold_individually_context( 'edit' );

        $value = 'no';

        if ( $sold_individually ) {
            if ( ! in_array( $sold_individually_context, array( 'configuration', 'product' ) ) ) {
                $value = 'product';
            } else {
                $value = $sold_individually_context;
            }
        }
        ob_start();
        ?>
        <div class="form-group-row show_if_bundle">
            <div class="form-group">
                <label class="control-label col-sm-3 col-md-3" for="_wc_pb_sold_individually">
                    <?php esc_html_e( 'Sold individually', 'woocommerce' ); ?>
                    <span class="img_tip" data-desc="<?php esc_attr_e( 'Allow only one of this bundle to be bought in a single order. Choose the <strong>Matching configurations only</strong> option to only prevent <strong>identically configured</strong> bundles from being purchased together.', 'woocommerce' ); ?>"></span>
                </label>
                <div class="col-md-6 col-sm-9">
                    <select id="_wc_pb_sold_individually" name="_wc_pb_sold_individually" class="form-control">
                        <option value="no" <?php selected( $value, 'no' ); ?>><?php esc_html_e( 'No', 'woocommerce-product-bundles' ); ?></option>
                        <option value="product" <?php selected( $value, 'product' ); ?>><?php esc_html_e( 'Yes', 'woocommerce-product-bundles' ); ?></option>
                        <option value="configuration" <?php selected( $value, 'configuration' ); ?>><?php esc_html_e( 'Matching configurations only', 'woocommerce-product-bundles' ); ?></option>
                    </select>
                    <span class="form-text"></span>
                </div>
            </div>
        </div>
        <?php
        ob_end_flush();
    }

    public function form_location_option() {
        $options = WC_Product_Bundle::get_add_to_cart_form_location_options();
        $help_tip = '';
        $loop = 0;
        foreach ( $options as $option_key => $option ) {
            $help_tip .= '<strong>' . $option['title'] . '</strong> &ndash; ' . $option['description'];
            if ( $loop < sizeof( $options ) - 1 ) {
                $help_tip .= '</br></br>';
            }
            $loop ++;
        }
        ob_start();
        ?>
        <div class="form-group show_if_bundle">
            <label class="control-label col-sm-3 col-md-3" for="_wc_pb_add_to_cart_form_location">
                <?php esc_html_e( 'Form location', 'woocommerce' ); ?>
                <span class="img_tip" data-desc="<?php _e( $help_tip, 'woocommerce-product-bundles' ); ?>"></span>
            </label>
            <div class="col-md-6 col-sm-9">
                <select id="_wc_pb_sold_individually" name="_wc_pb_add_to_cart_form_location" class="form-control">
                    <?php
                    $from_location_options = array_combine( array_keys( $options ), wp_list_pluck( $options, 'title' ) );
                    $sel_value = $this->bundled_product->get_add_to_cart_form_location( 'edit' );
                    foreach ( $from_location_options as $key => $val ) {
                        ?>
                        <option value="<?php echo $key; ?>" <?php selected( $sel_value, $key ); ?>><?php esc_html_e( $val ); ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php
        ob_end_flush();
    }

    public function add_localize_params( $params ) {
        // Find group modes with a parent item.
        $group_mode_options = WC_Product_Bundle::get_group_mode_options();
        $group_modes_with_parent = array();

        foreach ( $group_mode_options as $group_mode_key => $group_mode_title ) {
            if ( WC_Product_Bundle::group_mode_has( $group_mode_key, 'parent_item' ) || WC_Product_Bundle::group_mode_has( $group_mode_key, 'faked_parent_item' ) ) {
                $group_modes_with_parent[] = $group_mode_key;
            }
        }

        $new_params = array(
            'add_bundled_product_nonce'   => wp_create_nonce( 'wc_bundles_add_bundled_product' ),
            'i18n_remove_bundled_product' => esc_js( __( 'Are you sure you want to remove this product?', WCMp_AFM_TEXT_DOMAIN ) ),
            'group_modes_with_parent'     => $group_modes_with_parent,
            'is_wc_version_gte_3_2'       => WC_PB_Core_Compatibility::is_wc_version_gte( '3.2' ) ? 'yes' : 'no'
        );
        return array_merge( $params, $new_params );
    }

    /**
     * Get current vendor booking products.
     *
     * @param string post status
     * @param string One of OBJECT, or ARRAY_N
     * @return array of object or numeric array
     */
    public static function get_vendor_bundled_products( $post_status = 'any', $output = OBJECT ) {
        global $WCMp;
        $vendor_id = afm()->vendor_id;
        $bundled_products = array();
        if ( $vendor_id ) {
            $vendor = get_wcmp_vendor( $vendor_id );
            if ( $vendor ) {
                $args = array(
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
                            'terms'    => 'bundle',
                            'operator' => 'IN',
                        )
                    )
                );
                $vendor_products = $vendor->get_products( $args );

                foreach ( $vendor_products as $vendor_product ) {
                    $product_type = WC_Product_Factory::get_product_type( $vendor_product->ID );
                    if ( $product_type === 'bundle' ) {
                        $bookable_products[] = ( $output == OBJECT ) ? new WC_Product_Booking( $vendor_product->ID ) : $vendor_product->ID;
                    }
                }
            }
        }
        return $bookable_products;
    }

    /**
     * Handles getting bundled product tabs - @see bundled_product_vendor_html.
     *
     * @return array
     */
    public function get_bundled_product_tabs() {

        /**
         * 'wcmp_afm_bundled_product_vendor_html_tabs' filter.
         * Use this to add bundled product admin settings tabs
         *
         * @param  array  $tab_data
         */
        return apply_filters( 'wcmp_afm_bundled_product_vendor_html_tabs', array(
            array(
                'id'    => 'config',
                'title' => __( 'Basic Settings', 'woocommerce-product-bundles' ),
            ),
            array(
                'id'    => 'advanced',
                'title' => __( 'Advanced Settings', 'woocommerce-product-bundles' ),
            )
            ) );
    }

    /**
     * Process, verify and save bundle type product data.
     *
     * @param  WC_Product  $product
     * @return void
     */
    public function process_bundle_data( $product ) {

        if ( $product->is_type( 'bundle' ) ) {

            $props = array(
                'layout'                    => 'default',
                'group_mode'                => 'parent',
                'editable_in_cart'          => false,
                'sold_individually'         => false,
                'sold_individually_context' => 'product'
            );

            /*
             * Layout.
             */

            if ( ! empty( $_POST['_wc_pb_layout_style'] ) ) {
                $props['layout'] = wc_clean( $_POST['_wc_pb_layout_style'] );
            }

            /*
             * Group mode option.
             */

            $group_mode_pre = $product->get_group_mode( 'edit' );

            if ( ! empty( $_POST['_wc_pb_group_mode'] ) ) {
                $props['group_mode'] = wc_clean( $_POST['_wc_pb_group_mode'] );
            }

            /*
             * Cart editing option.
             */

            if ( ! empty( $_POST['_wc_pb_edit_in_cart'] ) ) {
                $props['editable_in_cart'] = true;
            }

            /*
             * Extended "Sold Individually" option.
             */

            if ( ! empty( $_POST['_wc_pb_sold_individually'] ) ) {

                $sold_individually_context = wc_clean( $_POST['_wc_pb_sold_individually'] );

                if ( in_array( $sold_individually_context, array( 'product', 'configuration' ) ) ) {
                    $props['sold_individually'] = true;
                    $props['sold_individually_context'] = $sold_individually_context;
                }
            }

            /*
             * "Form location" option.
             */

            if ( ! empty( $_POST['_wc_pb_add_to_cart_form_location'] ) ) {

                $form_location = wc_clean( $_POST['_wc_pb_add_to_cart_form_location'] );

                if ( in_array( $form_location, array_keys( WC_Product_Bundle::get_add_to_cart_form_location_options() ) ) ) {
                    $props['add_to_cart_form_location'] = $form_location;
                }
            }

            if ( ! defined( 'WC_PB_UPDATING' ) ) {

                $posted_bundle_data = isset( $_POST['bundle_data'] ) ? $_POST['bundle_data'] : false;
                $processed_bundle_data = $this->process_posted_bundle_data( $posted_bundle_data, $product->get_id() );

                if ( empty( $processed_bundle_data ) ) {
                    wc_add_notice( __( 'Please add at least one product to the bundle before publishing. To add products, click on the <strong>Bundled Products</strong> tab.', 'woocommerce-product-bundles' ), 'error' );
                    $props['bundled_data_items'] = array();
                } else {

                    foreach ( $processed_bundle_data as $key => $data ) {
                        $processed_bundle_data[$key] = array(
                            'bundled_item_id' => $data['item_id'],
                            'bundle_id'       => $product->get_id(),
                            'product_id'      => $data['product_id'],
                            'menu_order'      => $data['menu_order'],
                            'meta_data'       => array_diff_key( $data, array( 'item_id' => 1, 'product_id' => 1, 'menu_order' => 1 ) )
                        );
                    }

                    $props['bundled_data_items'] = $processed_bundle_data;
                }

                $product->set( $props );
            } else {
                wc_add_notice( __( 'Your changes have not been saved &ndash; please wait for the <strong>WooCommerce Product Bundles Data Update</strong> routine to complete before creating new bundles or making changes to existing ones.', 'woocommerce-product-bundles' ), 'error' );
            }

            /*
             * Show invalid group mode selection notice.
             */

            if ( false === $product->validate_group_mode() ) {

                $product->set_group_mode( $group_mode_pre );

                $group_mode_options = WC_Product_Bundle::get_group_mode_options( true );
                $group_modes_without_parent = array();

                foreach ( $group_mode_options as $group_mode_key => $group_mode_title ) {
                    if ( false === WC_Product_Bundle::group_mode_has( $group_mode_key, 'parent_item' ) ) {
                        $group_modes_without_parent[] = '<strong>' . $group_mode_title . '</strong>';
                    }
                }

                $group_modes_without_parent_msg = sprintf( _n( '%s is only applicable to <a href="https://docs.woocommerce.com/document/bundles/bundles-configuration/#shipping" target="_blank">unassembled</a> bundles.', '%s are only applicable to <a href="https://docs.woocommerce.com/document/bundles/bundles-configuration/#shipping" target="_blank">unassembled</a> bundles.', sizeof( $group_modes_without_parent ), 'woocommerce-product-bundles' ), WC_PB_Helpers::format_list_of_items( $group_modes_without_parent ) );
                
                wc_add_notice( sprintf( __( 'Invalid <strong>Group mode</strong> selected. %s To make this bundle an <strong>unassembled</strong> one: <ol><li>Under <strong>Product Data</strong>, enable the <strong>Virtual</strong> option.</li><li>Go to the <strong>General</strong> tab and ensure that <strong>Regular Price</strong> and <strong>Sale Price</strong> are empty.</li><li>Go to the <strong>Bundled Products</strong> tab and enable <strong>Shipped Individually</strong> under the <strong>Basic Settings</strong> of each bundled item.</li></ol>', 'woocommerce-product-bundles' ), $group_modes_without_parent_msg ), 'error' );
            }
        }
    }

    /**
     * Sort by menu order callback.
     *
     * @param  array  $a
     * @param  array  $b
     * @return int
     */
    public static function menu_order_sort( $a, $b ) {
        if ( isset( $a['menu_order'] ) && isset( $b['menu_order'] ) ) {
            return $a['menu_order'] - $b['menu_order'];
        } else {
            return isset( $a['menu_order'] ) ? 1 : -1;
        }
    }

    /**
     * Process posted bundled item data.
     *
     * @param  array  $posted_bundle_data
     * @param  mixed  $post_id
     * @return mixed
     */
    public function process_posted_bundle_data( $posted_bundle_data, $post_id ) {

        $bundle_data = array();

        if ( ! empty( $posted_bundle_data ) ) {

            $sold_individually_notices = array();
            $times = array();
            $loop = 0;

            // Sort posted data by menu order.
            usort( $posted_bundle_data, array( __CLASS__, 'menu_order_sort' ) );

            foreach ( $posted_bundle_data as $data ) {

                $product_id = isset( $data['product_id'] ) ? absint( $data['product_id'] ) : false;
                $item_id = isset( $data['item_id'] ) ? absint( $data['item_id'] ) : false;

                $product = wc_get_product( $product_id );

                if ( ! $product ) {
                    continue;
                }

                $product_type = $product->get_type();
                $product_title = $product->get_title();
                $is_sub = in_array( $product_type, array( 'subscription', 'variable-subscription' ) );

                if ( in_array( $product_type, array( 'simple', 'variable', 'subscription', 'variable-subscription' ) ) && ( $post_id != $product_id ) && ! isset( $sold_individually_notices[$product_id] ) ) {

                    // Bundling subscription products requires Subs v2.0+.
                    if ( $is_sub ) {
                        if ( ! class_exists( 'WC_Subscriptions' ) || version_compare( WC_Subscriptions::$version, '2.0.0', '<' ) ) {
                            wc_add_notice( sprintf( __( '<strong>%s</strong> was not saved. WooCommerce Subscriptions version 2.0 or higher is required in order to bundle Subscription products.', 'woocommerce-product-bundles' ), $product_title ), 'error' );
                            continue;
                        }
                    }

                    // Only allow bundling multiple instances of non-sold-individually items.
                    if ( ! isset( $times[$product_id] ) ) {
                        $times[$product_id] = 1;
                    } else {
                        if ( $product->is_sold_individually() ) {
                            wc_add_notice( sprintf( __( '<strong>%s</strong> is sold individually and cannot be bundled more than once.', 'woocommerce-product-bundles' ), $product_title ), 'error' );
                            // Make sure we only display the notice once for every id.
                            $sold_individually_notices[$product_id] = 'yes';
                            continue;
                        }
                        $times[$product_id] += 1;
                    }

                    // Now start processing the posted data.
                    $loop ++;

                    $item_data = array();
                    $item_title = $product_title;

                    $item_data['product_id'] = $product_id;
                    $item_data['item_id'] = $item_id;

                    // Save thumbnail preferences first.
                    if ( isset( $data['hide_thumbnail'] ) ) {
                        $item_data['hide_thumbnail'] = 'yes';
                    } else {
                        $item_data['hide_thumbnail'] = 'no';
                    }

                    // Save title preferences.
                    if ( isset( $data['override_title'] ) ) {
                        $item_data['override_title'] = 'yes';
                        $item_data['title'] = isset( $data['title'] ) ? stripslashes( $data['title'] ) : '';
                    } else {
                        $item_data['override_title'] = 'no';
                    }

                    // Save description preferences.
                    if ( isset( $data['override_description'] ) ) {
                        $item_data['override_description'] = 'yes';
                        $item_data['description'] = isset( $data['description'] ) ? wp_kses_post( stripslashes( $data['description'] ) ) : '';
                    } else {
                        $item_data['override_description'] = 'no';
                    }

                    // Save optional.
                    if ( isset( $data['optional'] ) ) {
                        $item_data['optional'] = 'yes';
                    } else {
                        $item_data['optional'] = 'no';
                    }

                    // Save item pricing scheme.
                    if ( isset( $data['priced_individually'] ) ) {
                        $item_data['priced_individually'] = 'yes';
                    } else {
                        $item_data['priced_individually'] = 'no';
                    }

                    // Save item shipping scheme.
                    if ( isset( $data['shipped_individually'] ) || $product->is_virtual() ) {
                        $item_data['shipped_individually'] = 'yes';
                    } else {
                        $item_data['shipped_individually'] = 'no';
                    }

                    // Save quantity data.
                    if ( isset( $data['quantity_min'] ) ) {

                        if ( is_numeric( $data['quantity_min'] ) ) {

                            $quantity = absint( $data['quantity_min'] );

                            if ( $quantity >= 0 && $data['quantity_min'] - $quantity == 0 ) {

                                if ( $quantity !== 1 && $product->is_sold_individually() ) {
                                    wc_add_notice( sprintf( __( 'Item <strong>#%1$s: %2$s</strong> is sold individually &ndash; its minimum quantity cannot be higher than 1.', 'woocommerce-product-bundles' ), $loop, $item_title ), 'error' );
                                    $item_data['quantity_min'] = 1;
                                } else {
                                    $item_data['quantity_min'] = $quantity;
                                }
                            } else {
                                wc_add_notice( sprintf( __( 'The minimum quantity of item <strong>#%1$s: %2$s</strong> was not valid and has been reset. Please enter a non-negative integer value.', 'woocommerce-product-bundles' ), $loop, $item_title ), 'error' );
                                $item_data['quantity_min'] = 1;
                            }
                        }
                    } else {
                        $item_data['quantity_min'] = 1;
                    }

                    $quantity_min = $item_data['quantity_min'];

                    // Save max quantity data.
                    if ( isset( $data['quantity_max'] ) && ( is_numeric( $data['quantity_max'] ) || '' === $data['quantity_max'] ) ) {

                        $quantity = '' !== $data['quantity_max'] ? absint( $data['quantity_max'] ) : '';

                        if ( '' === $quantity || ( $quantity > 0 && $quantity >= $quantity_min && $data['quantity_max'] - $quantity == 0 ) ) {

                            if ( $quantity !== 1 && $product->is_sold_individually() ) {
                                wc_add_notice( sprintf( __( 'Item <strong>#%1$s: %2$s</strong> is sold individually &ndash; its maximum quantity cannot be higher than 1.', 'woocommerce-product-bundles' ), $loop, $item_title ), 'error' );
                                $item_data['quantity_max'] = 1;
                            } else {
                                $item_data['quantity_max'] = $quantity;
                            }
                        } else {
                            wc_add_notice( sprintf( __( 'The maximum quantity of item <strong>#%1$s: %2$s</strong> was not valid and has been reset. Please enter a positive integer value, at least as high as the minimum quantity. Otherwise, leave the field empty for an unlimited maximum quantity.', 'woocommerce-product-bundles' ), $loop, $item_title ), 'error' );
                            $item_data['quantity_max'] = $quantity_min;
                        }
                    } else {
                        $item_data['quantity_max'] = max( $quantity_min, 1 );
                    }

                    // Save sale price data.
                    if ( isset( $data['discount'] ) ) {

                        if ( 'yes' === $item_data['priced_individually'] && is_numeric( $data['discount'] ) ) {

                            $discount = wc_format_decimal( $data['discount'] );

                            if ( $discount < 0 || $discount > 100 ) {
                                wc_add_notice( sprintf( __( 'The discount value of item <strong>#%1$s: %2$s</strong> was not valid and has been reset. Please enter a positive number between 0-100.', 'woocommerce-product-bundles' ), $loop, $item_title ), 'error' );
                                $item_data['discount'] = '';
                            } else {
                                $item_data['discount'] = $discount;
                            }
                        } else {
                            $item_data['discount'] = '';
                        }
                    } else {
                        $item_data['discount'] = '';
                    }

                    // Save data related to variable items.
                    if ( in_array( $product_type, array( 'variable', 'variable-subscription' ) ) ) {

                        $allowed_variations = array();

                        // Save variation filtering options.
                        if ( isset( $data['override_variations'] ) ) {

                            if ( isset( $data['allowed_variations'] ) ) {

                                if ( is_array( $data['allowed_variations'] ) ) {
                                    $allowed_variations = array_map( 'intval', $data['allowed_variations'] );
                                } else {
                                    $allowed_variations = array_filter( array_map( 'intval', explode( ',', $data['allowed_variations'] ) ) );
                                }

                                if ( count( $allowed_variations ) > 0 ) {

                                    $item_data['override_variations'] = 'yes';

                                    $item_data['allowed_variations'] = $allowed_variations;

                                    if ( isset( $data['hide_filtered_variations'] ) ) {
                                        $item_data['hide_filtered_variations'] = 'yes';
                                    } else {
                                        $item_data['hide_filtered_variations'] = 'no';
                                    }
                                }
                            } else {
                                $item_data['override_variations'] = 'no';
                                wc_add_notice( sprintf( __( 'Please activate at least one variation of item <strong>#%1$s: %2$s</strong>.', 'woocommerce-product-bundles' ), $loop, $item_title ), 'error' );
                            }
                        } else {
                            $item_data['override_variations'] = 'no';
                        }

                        // Save defaults.
                        if ( isset( $data['override_default_variation_attributes'] ) ) {

                            if ( isset( $data['default_variation_attributes'] ) ) {

                                // If filters are set, check that the selections are valid.
                                if ( isset( $data['override_variations'] ) && ! empty( $allowed_variations ) ) {

                                    // The array to store all valid attribute options of the iterated product.
                                    $filtered_attributes = array();

                                    // Populate array with valid attributes.
                                    foreach ( $allowed_variations as $variation ) {

                                        $variation_data = array();

                                        // Get variation attributes.
                                        $variation_data = wc_get_product_variation_attributes( $variation );

                                        foreach ( $variation_data as $name => $value ) {

                                            $attribute_name = substr( $name, strlen( 'attribute_' ) );
                                            $attribute_value = $value;

                                            // Populate array.
                                            if ( ! isset( $filtered_attributes[$attribute_name] ) ) {
                                                $filtered_attributes[$attribute_name][] = $attribute_value;
                                            } elseif ( ! in_array( $attribute_value, $filtered_attributes[$attribute_name] ) ) {
                                                $filtered_attributes[$attribute_name][] = $attribute_value;
                                            }
                                        }
                                    }

                                    // Check validity.
                                    foreach ( $data['default_variation_attributes'] as $name => $value ) {

                                        if ( '' === $value ) {
                                            continue;
                                        }

                                        if ( ! in_array( stripslashes( $value ), $filtered_attributes[$name] ) && ! in_array( '', $filtered_attributes[$name] ) ) {
                                            // Set option to "Any".
                                            $data['default_variation_attributes'][$name] = '';
                                            // Show an error.
                                            wc_add_notice( sprintf( __( 'The attribute defaults of item <strong>#%1$s: %2$s</strong> are inconsistent with the set of active variations and have been reset.', 'woocommerce-product-bundles' ), $loop, $item_title ), 'error' );
                                            continue;
                                        }
                                    }
                                }

                                // Save.
                                foreach ( $data['default_variation_attributes'] as $name => $value ) {
                                    $item_data['default_variation_attributes'][$name] = stripslashes( $value );
                                }

                                $item_data['override_default_variation_attributes'] = 'yes';
                            }
                        } else {
                            $item_data['override_default_variation_attributes'] = 'no';
                        }
                    }

                    // Save item visibility preferences.
                    $visibility = array(
                        'product' => isset( $data['single_product_visibility'] ) ? 'visible' : 'hidden',
                        'cart'    => isset( $data['cart_visibility'] ) ? 'visible' : 'hidden',
                        'order'   => isset( $data['order_visibility'] ) ? 'visible' : 'hidden'
                    );

                    if ( 'hidden' === $visibility['product'] ) {

                        if ( in_array( $product_type, array( 'variable', 'variable-subscription' ) ) ) {

                            if ( 'yes' === $item_data['override_default_variation_attributes'] ) {

                                if ( ! empty( $data['default_variation_attributes'] ) ) {

                                    foreach ( $data['default_variation_attributes'] as $default_name => $default_value ) {
                                        if ( ! $default_value ) {
                                            $visibility['product'] = 'visible';
                                            wc_add_notice( sprintf( __( 'To hide item <strong>#%1$s: %2$s</strong> from the single-product template, please define defaults for its variation attributes.', 'woocommerce-product-bundles' ), $loop, $item_title ), 'error' );
                                            break;
                                        }
                                    }
                                } else {
                                    $visibility['product'] = 'visible';
                                }
                            } else {
                                wc_add_notice( sprintf( __( 'To hide item <strong>#%1$s: %2$s</strong> from the single-product template, please define defaults for its variation attributes.', 'woocommerce-product-bundles' ), $loop, $item_title ), 'error' );
                                $visibility['product'] = 'visible';
                            }
                        }
                    }

                    $item_data['single_product_visibility'] = $visibility['product'];
                    $item_data['cart_visibility'] = $visibility['cart'];
                    $item_data['order_visibility'] = $visibility['order'];

                    // Save price visibility preferences.

                    $item_data['single_product_price_visibility'] = isset( $data['single_product_price_visibility'] ) ? 'visible' : 'hidden';
                    $item_data['cart_price_visibility'] = isset( $data['cart_price_visibility'] ) ? 'visible' : 'hidden';
                    $item_data['order_price_visibility'] = isset( $data['order_price_visibility'] ) ? 'visible' : 'hidden';

                    // Save position data.
                    $item_data['menu_order'] = absint( $data['menu_order'] );

                    /**
                     * Filter processed data before saving/updating WC_Bundled_Item_Data objects.
                     *
                     * @param  array  $item_data
                     * @param  array  $data
                     * @param  mixed  $item_id
                     * @param  mixed  $post_id
                     */
                    $bundle_data[] = apply_filters( 'wcmp_afm_bundles_process_bundled_item_data', $item_data, $data, $item_id, $post_id );
                }
            }
        }

        return $bundle_data;
    }

}
