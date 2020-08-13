<?php

/**
 * WCMp Advanced Frontend Manager
 *
 * WooCommerce Bookings Support
 *
 * @author WC Marketplace
 * @package WCMp_AFM/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Booking_Integration {

    protected $id = null;
    protected $tabs = array();
    protected $bookable_product = null;
    protected $restricted_meta = array();
    protected $restricted_days = array();
    protected $plugin = 'booking';
    protected $booking_endpoints = array();

    public function __construct() {
        $this->tabs = $this->set_additional_tabs();
        $this->booking_endpoints = $this->all_booking_endpoints();

        //filter for adding wcmp endpoint query vars
        add_filter( 'wcmp_endpoints_query_vars', array( $this, 'booking_endpoints_query_vars' ) );
        add_filter( 'wcmp_vendor_dashboard_nav', array( $this, 'booking_dashboard_navs' ) );

        $this->call_endpoint_contents();
        add_action( 'wcmp_product_type_options', array( $this, 'booking_additional_product_type_options' ) );

        //dequeue scripts on booking endpoints
        add_action( 'afm_endpoints_dequeue_wcmp_scripts', array( $this, 'dequeue_wcmp_scripts' ), 10, 2 );
        //enqueue required scripts for endpoints added
        add_filter( 'wcmp_advance_product_script_params', array( $this, 'add_localize_params' ) );
        add_action( 'afm_enqueue_dashboard_scripts', array( $this, 'booking_endpoint_scripts' ), 10, 4 );

        // Bookable Product Additional Tabs
        add_filter( 'wcmp_product_data_tabs', array( $this, 'booking_additional_tabs' ) );
        add_action( 'wcmp_product_tabs_content', array( $this, 'booking_additional_tabs_content' ) );

        add_action( 'wcmp_afm_after_general_product_data', array( $this, 'booking_general_product_tab_content' ) );
        add_filter( 'general_tab_tax_section', array( $this, 'allow_booking_type' ) );
        // Bookable Product Meta Data Save
        // below WC version 3.0
        add_action( 'wcmp_process_product_meta_booking', array( $this, 'save_booking_meta' ), 10, 2 );
        //above WC version 3.0 (set props before product save)
        add_action( 'wcmp_process_product_object', array( $this, 'set_booking_props' ), 20 );

        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            add_action( 'template_redirect', array( $this, 'form_submited' ), 90 );
        }
    }

    //this will be called from the main Integration class after WCMp_AFM_Add_Product_Endpoint class constructor executed
    public function set_props( $id ) {
        $this->id = $id;

        //after setting id get the bookable product and restricted days
        $this->bookable_product = new WC_Product_Booking( $this->id );
        $this->restricted_meta = $this->bookable_product->get_restricted_days();
        for ( $i = 0; $i < 7; $i ++ ) {
            if ( $this->restricted_meta && in_array( $i, $this->restricted_meta ) ) {
                $this->restricted_days[$i] = $i;
            } else {
                $this->restricted_days[$i] = false;
            }
        }
    }

    /**
     * Return all the `WC Bookings` endpoints added to vendor dashboard
     * 
     * @return array endpoints 
     */
    private function all_booking_endpoints() {
        return apply_filters( "wcmp_afm_{$this->plugin}_endpoint_list", afm()->dependencies->get_allowed_endpoints( $this->plugin ) );
    }

    protected function set_additional_tabs() {
        global $WCMp;
        $booking_tabs = array();

        $booking_tabs['booking_pricing'] = array(
            'p_type'   => 'booking',
            'label'    => __( 'Costs', 'woocommerce-bookings' ),
            'target'   => 'booking_pricing_product_data',
            'class'    => array( 'show_if_booking' ),
            'priority' => '12',
        );
        $booking_tabs['booking_availability'] = array(
            'p_type'   => 'booking',
            'label'    => __( 'Availability', 'woocommerce-bookings' ),
            'target'   => 'booking_availability_product_data',
            'class'    => array( 'show_if_booking' ),
            'priority' => '22',
        );
        if ( $WCMp->vendor_caps->vendor_can( 'wc_booking_has_persons' ) ) {
            $booking_tabs['booking_persons'] = array(
                'p_type'   => 'booking',
                'label'    => __( 'Persons', 'woocommerce-bookings' ),
                'target'   => 'booking_persons_product_data',
                'class'    => array( 'show_if_has_persons' ),
                'priority' => '42',
            );
        }
        if ( $WCMp->vendor_caps->vendor_can( 'wc_booking_has_resources' ) ) {
            $booking_tabs['booking_resources'] = array(
                'p_type'   => 'booking',
                'label'    => __( 'Resources', 'woocommerce-bookings' ),
                'target'   => 'booking_resources_product_data',
                'class'    => array( 'show_if_has_resources' ),
                'priority' => '52',
            );
        }
        return $booking_tabs;
    }

    public function booking_endpoints_query_vars( $endpoints ) {
        return afm()->dependencies->plugin_endpoints_query_vars( $endpoints, $this->booking_endpoints );
    }

    public function booking_dashboard_navs( $navs ) {
        $parent_menu = array(
            'label'      => __( 'Bookings', 'woocommerce-bookings' ),
            'capability' => 'wcmp_vendor_dashboard_menu_booking_capability',
            'position'   => 32,
            'nav_icon'   => 'wcmp-font ico-booking-icon',
            'plugin'     => $this->plugin,
        );
        return afm()->dependencies->plugin_dashboard_navs( $navs, $this->booking_endpoints, $parent_menu );
    }

    public function call_endpoint_contents() {
        //add endpoint content
        foreach ( $this->booking_endpoints as $key => $endpoint ) {
            $cap = ! empty( $endpoint['vendor_can'] ) ? $endpoint['vendor_can'] : '';
            if ( $cap && current_vendor_can( $cap ) ) {
                add_action( 'wcmp_vendor_dashboard_' . $key . '_endpoint', array( $this, 'booking_endpoints_callback' ) );
            }
        }
    }

    public function booking_endpoints_callback() {
        $endpoint_name = str_replace( array( 'wcmp_vendor_dashboard_', '_endpoint' ), '', current_filter() );
        afm()->endpoints->load_class( $endpoint_name );
        $classname = 'WCMp_AFM_' . ucwords( str_replace( '-', '_', $endpoint_name ), '_' ) . '_Endpoint';
        $endpoint_class = new $classname;
        $endpoint_class->output();
    }

    /**
     * The required return type. One of OBJECT, ARRAY_A, or ARRAY_N,    
     */

    /**
     * Get current vendor booking products.
     *
     * @param string post status
     * @param string One of OBJECT, or ARRAY_N
     * @return array of object or numeric array
     */
    public static function get_vendor_bookable_products( $post_status = 'any', $output = OBJECT ) {
        global $WCMp;
        $vendor_id = afm()->vendor_id;
        $bookable_products = array();
        if ( $vendor_id ) {
            $vendor = get_wcmp_vendor( $vendor_id );
            if ( $vendor ) {
                $args = apply_filters( 'get_vendor_booking_products_args', array(
                    'post_status' => $post_status,
                    'tax_query'   => array(
                        array(
                            'taxonomy' => $WCMp->taxonomy->taxonomy_name,
                            'field'    => 'term_id',
                            'terms'    => absint( $vendor->term_id )
                        ),
                        array(
                            'taxonomy' => 'product_type',
                            'field'    => 'slug',
                            'terms'    => 'booking',
                        )
                    )
                    ) );

                $vendor_products = $vendor->get_products( $args );

                foreach ( $vendor_products as $vendor_product ) {
                    $product_type = WC_Product_Factory::get_product_type( $vendor_product->ID );
                    if ( in_array( $product_type, apply_filters( 'get_vendor_booking_product_types', array( 'booking' ) ) ) ) {
                        $bookable_products[] = ( $output == OBJECT ) ? new WC_Product_Booking( $vendor_product->ID ) : $vendor_product->ID;
                    }
                }
            }
        }
        return $bookable_products;
    }

    /**
     * Get current vendor booking orders.
     *
     * @return array of bookings object
     */
    public static function get_vendor_booking_array( $args = null ) {
        global $wpdb;
        $bookings_object = array();
        $products = self::get_vendor_bookable_products( 'any', ARRAY_N );
        if ( ! empty( $products ) ) {
//            $query = "SELECT ID FROM {$wpdb->posts} as posts
//                    INNER JOIN {$wpdb->postmeta} AS postmeta ON posts.ID = postmeta.post_id
//                    WHERE 1=1
//                    AND posts.post_type IN ( 'wc_booking' )
//                    AND posts.post_status NOT IN ('was-in-cart', 'in-cart')
//                    AND postmeta.meta_key = '_booking_product_id' AND postmeta.meta_value in (" . implode( ',', $products ) . ")";
//            $vendor_bookings = $wpdb->get_results( $query );

            $defaults = array(
                'post_status'      => array( 'pending-confirmation', 'pending', 'complete', 'paid', 'confirmed', 'unpaid', 'cancelled' ),
                'post_type'        => 'wc_booking',
                'posts_per_page'   => -1,
                'orderby'          => 'menu_order',
                'order'            => 'asc',
                'suppress_filters' => true,
                'meta_query'       => array(
                    array(
                        'key'     => '_booking_product_id',
                        'value'   => join( ', ', $products ),
                        'compare' => 'IN',
                    )
                )
            );
            $r = wp_parse_args( $args, $defaults );
            $vendor_bookings = get_posts( $r );

            foreach ( $vendor_bookings as $vendor_booking ) {
                $bookings_object[] = get_post( $vendor_booking->ID );
            }
        }
        return $bookings_object;
    }

    /**
     * Return all bookings for a product in a given range
     * @param integer $start_date
     * @param integer $end_date
     * @param int  $product_or_resource_id
     * @param bool $check_in_cart
     *
     * @return array
     */
    public static function get_bookings_in_date_range( $start_date, $end_date, $product_or_resource_id = 0, $check_in_cart = true ) {
        global $WCMp;
        $vendor_id = afm()->vendor_id;
        if ( $vendor_id ) {
            //Append vendor id to separate out each vendor transient
            $transient_name = 'book_dr_' . md5( http_build_query( array( $vendor_id, $start_date, $end_date, $product_or_resource_id, $check_in_cart, WC_Cache_Helper::get_transient_version( 'bookings' ) ) ) );
            $booking_ids = get_transient( $transient_name );

            if ( false === $booking_ids ) {
                $booking_ids = self::get_bookings_in_date_range_query( $start_date, $end_date, $product_or_resource_id, $check_in_cart );
                set_transient( $transient_name, $booking_ids, DAY_IN_SECONDS * 30 );
            }
            return array_map( 'get_wc_booking', wp_parse_id_list( $booking_ids ) );
        }
        return array();
    }

    /**
     * Return all bookings for a product in a given range - the query part (no cache)
     * @param  integer $start_date
     * @param  integer$end_date
     * @param  int $product_or_resource_id
     * @param  bool $check_in_cart
     * @return array of booking ids
     */
    public static function get_bookings_in_date_range_query( $start_date, $end_date, $product_or_resource_id, $check_in_cart ) {
        $args = array(
            'status'       => get_wc_booking_statuses(),
            'object_id'    => $product_or_resource_id,
            'object_type'  => 'product_or_resource',
            'date_between' => array(
                'start' => $start_date,
                'end'   => $end_date,
            ),
        );

        if ( ! $check_in_cart ) {
            $args['status'] = array_diff( $args['status'], array( 'in-cart' ) );
        }

        if ( $product_or_resource_id ) {
            if ( get_post_type( $product_or_resource_id ) === 'bookable_resource' ) {
                $args['resource_id'] = absint( $product_or_resource_id );
            } else {
                $args['product_id'] = absint( $product_or_resource_id );
            }
        }
        return array_intersect( WC_Booking_Data_Store::get_booking_ids_by( $args ), wp_list_pluck( self::get_vendor_booking_array(), 'ID' ) );
    }

    /**
     * Filters products for narrowing search.
     */
    public static function product_filters() {
        $filters = array();
        $products = self::get_vendor_bookable_products();
        foreach ( $products as $product ) {
            $filters[$product->get_id()] = $product->get_name();

            $resources = $product->get_resources();

            foreach ( $resources as $resource ) {
                $filters[$resource->get_id()] = '&nbsp;&nbsp;&nbsp;' . $resource->get_name();
            }
        }

        return $filters;
    }

    /**
     * Filters resources for narrowing search.
     */
    public static function resources_filters() {
        $filters = array();
        $resources = self::get_booking_resources();

        foreach ( $resources as $resource ) {
            $filters[$resource->get_id()] = $resource->get_name();
        }

        return $filters;
    }

    /**
     * Get all booking product resources for current vendor.
     *
     * @return array
     */
    public static function get_bookable_product_resource_ids( $args = null ) {
        $current_vendor_id = afm()->vendor_id;
        if ( $current_vendor_id && current_vendor_can( 'manage_resources' ) ) {
            $resources_author = array();
            if ( current_vendor_can( 'share_admin_resources' ) ) {
                $resources_author = wp_list_pluck( get_users( array( 'role' => 'Administrator' ) ), 'ID' );
            }
            $resources_author[] = $current_vendor_id;

            $defaults = array(
                'post_status'      => 'publish',
                'post_type'        => 'bookable_resource',
                'posts_per_page'   => -1,
                'orderby'          => 'menu_order',
                'order'            => 'asc',
                'suppress_filters' => true,
                'fields'           => 'ids',
                'author__in'       => $resources_author,
            );
            $r = wp_parse_args( $args, $defaults );
            $ids = get_posts( apply_filters( 'afm_get_booking_resources_args', $r ) );

            return wp_parse_id_list( $ids );
        }
        return array();
    }

    /**
     * Get booking product resources.
     *
     * @return array
     */
    public static function get_booking_resources( $args = null ) {
        $ids = self::get_bookable_product_resource_ids( $args );
        $resources = array();

        foreach ( $ids as $id ) {
            $resources[] = new WC_Product_Booking_Resource( $id );
        }
        return $resources;
    }

    public function dequeue_wcmp_scripts( $flag, $endpoint ) {
        if ( $endpoint === 'bookings' || $endpoint === 'resources' || $endpoint === 'add-resource' || $endpoint === 'create-booking' || $endpoint === 'booking-calendar' || $endpoint === 'booking-notification' ) {
            return true;
        }
        return $flag;
    }

    public function booking_endpoint_scripts( $endpoint, $frontend_script_path, $lib_path, $suffix ) {
        global $WCMp;
        switch ( $endpoint ) {
            case 'bookings':
                if ( current_vendor_can( 'manage_bookings' ) ) {
                    $WCMp->library->load_dataTable_lib();
                    wp_register_script( 'afm-bookings-js', $frontend_script_path . 'bookings.js', array( 'jquery', 'wcmp-datatable-script', 'wcmp-datatable-bs-script' ), afm()->version, true );
                }
                break;
            case 'resources':
                if ( current_vendor_can( 'manage_resources' ) ) {
                    /**
                     * script for resources datatable
                     */
                    $WCMp->library->load_dataTable_lib();
                    wp_register_script( 'afm-resources-js', $frontend_script_path . 'resources.js', array( 'jquery', 'wcmp-datatable-script', 'wcmp-datatable-bs-script' ), afm()->version, true );

                    /**
                     * script for resource details page
                     */
                    wp_register_script( 'afm-add-resource-js', $frontend_script_path . 'add-resource.js', array( 'jquery', 'jquery-ui-sortable' ), afm()->version, true );
                }
                break;
            case 'create-booking':
                if ( current_vendor_can( 'create_booking' ) ) {
                    wp_enqueue_style( 'select2' );
                    wp_enqueue_style( 'wc-bookings-styles', WC_BOOKINGS_PLUGIN_URL . '/assets/css/frontend.css', null, WC_BOOKINGS_VERSION );
                    wp_enqueue_script( 'select2' );
                    wp_enqueue_script( 'afm-create-booking-js', $frontend_script_path . 'create-booking.js', array( 'jquery', 'select2' ), afm()->version, true );
                    break;
                }

            case 'booking-calendar':
                if ( current_vendor_can( 'booking_calendar' ) ) {

                    wp_enqueue_script( 'jquery-tiptip', WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js', array( 'jquery' ), WC_VERSION, true );
                    break;
                }
        }
    }

    public function add_localize_params( $params ) {
        $new_params = array(
            'add_person_nonce'       => wp_create_nonce( 'add-person' ),
            'unlink_person_nonce'    => wp_create_nonce( 'unlink-person' ),
            'add_resource_nonce'     => wp_create_nonce( 'add-resource' ),
            'remove_resource_nonce'  => wp_create_nonce( 'remove-resource' ),
            'i18n_remove_person'     => esc_js( __( 'Are you sure you want to remove this person type?', 'woocommerce-bookings' ) ),
            'i18n_new_resource_name' => esc_js( __( 'Enter a name for the new resource', 'woocommerce-bookings' ) ),
            'i18n_remove_resource'   => esc_js( __( 'Are you sure you want to remove this resource?', 'woocommerce-bookings' ) ),
        );
        return array_merge( $params, $new_params );
    }

    public function booking_additional_product_type_options( $options ) {
        global $WCMp;

        $options['virtual']['wrapper_class'] .= ' show_if_booking';
        if ( $WCMp->vendor_caps->vendor_can( 'wc_booking_has_persons' ) ) {
            $options['wc_booking_has_persons'] = array(
                'id'            => '_wc_booking_has_persons',
                'wrapper_class' => 'show_if_booking',
                'label'         => __( 'Has persons', 'woocommerce-bookings' ),
                'description'   => __( 'Enable this if this bookable product can be booked by a customer defined number of persons.', 'woocommerce-bookings' ),
                'default'       => 'no',
            );
        }
        if ( $WCMp->vendor_caps->vendor_can( 'wc_booking_has_resources' ) ) {
            $options['wc_booking_has_resources'] = array(
                'id'            => '_wc_booking_has_resources',
                'wrapper_class' => 'show_if_booking',
                'label'         => __( 'Has resources', 'woocommerce-bookings' ),
                'description'   => __( 'Enable this if this bookable product has multiple bookable resources, for example room types or instructors.', 'woocommerce-bookings' ),
                'default'       => 'no',
            );
        }
        return $options;
    }

    public function booking_additional_tabs( $product_tabs ) {
        return array_merge( $product_tabs, $this->tabs );
    }

    public function booking_additional_tabs_content() {
        foreach ( $this->tabs as $key => $tab ) {
            afm()->template->get_template( 'products/booking/html-product-data-' . str_replace( '_', '-', $key ) . '.php', array( 'id' => $this->id, 'tab' => $tab['target'], 'self' => $this, 'bookable_product' => $this->bookable_product, 'restricted_days' => $this->restricted_days ) );
        }
        return;
    }

    public function booking_general_product_tab_content() {
        if ( wcmp_is_allowed_product_type( 'booking' ) ) {
            afm()->template->get_template( 'products/booking/html-product-data-general.php', array( 'id' => $this->id, 'tab' => 'general', 'self' => $this, 'bookable_product' => $this->bookable_product, 'restricted_days' => $this->restricted_days ) );
        }
    }

    public function allow_booking_type( $allowed_types ) {
        $allowed_types[] = 'booking';
        return $allowed_types;
    }

    /**
     * Set data in 3.0.x
     *
     * @version  1.10.7
     * @param    WC_Product $product
     */
    public function set_booking_props( $product ) {
        // Only set props if the product is a bookable product.
        if ( ! is_a( $product, 'WC_Product_Booking' ) ) {
            return;
        }

        $resources = $this->get_posted_resources( $product );
        $product->set_props( array(
            'apply_adjacent_buffer'      => isset( $_POST['_wc_booking_apply_adjacent_buffer'] ),
            'availability'               => $this->get_posted_availability(),
            'block_cost'                 => isset( $_POST['_wc_booking_block_cost'] ) ? wc_clean( $_POST['_wc_booking_block_cost'] ) : null,
            'buffer_period'              => isset( $_POST['_wc_booking_buffer_period'] ) ? wc_clean( $_POST['_wc_booking_buffer_period'] ) : null,
            'calendar_display_mode'      => isset( $_POST['_wc_booking_calendar_display_mode'] ) ? wc_clean( $_POST['_wc_booking_calendar_display_mode'] ) : null,
            'cancel_limit_unit'          => isset( $_POST['_wc_booking_cancel_limit_unit'] ) ? wc_clean( $_POST['_wc_booking_cancel_limit_unit'] ) : null,
            'cancel_limit'               => isset( $_POST['_wc_booking_cancel_limit'] ) ? wc_clean( $_POST['_wc_booking_cancel_limit'] ) : null,
            'check_start_block_only'     => 'start' === $_POST['_wc_booking_check_availability_against'],
            'cost'                       => isset( $_POST['_wc_booking_cost'] ) ? wc_clean( $_POST['_wc_booking_cost'] ) : null,
            'default_date_availability'  => isset( $_POST['_wc_booking_default_date_availability'] ) ? wc_clean( $_POST['_wc_booking_default_date_availability'] ) : null,
            'display_cost'               => isset( $_POST['_wc_display_cost'] ) ? wc_clean( $_POST['_wc_display_cost'] ) : null,
            'duration_type'              => isset( $_POST['_wc_booking_duration_type'] ) ? wc_clean( $_POST['_wc_booking_duration_type'] ) : null,
            'duration_unit'              => isset( $_POST['_wc_booking_duration_unit'] ) ? wc_clean( $_POST['_wc_booking_duration_unit'] ) : null,
            'duration'                   => isset( $_POST['_wc_booking_duration'] ) ? wc_clean( $_POST['_wc_booking_duration'] ) : null,
            'enable_range_picker'        => isset( $_POST['_wc_booking_enable_range_picker'] ),
            'first_block_time'           => isset( $_POST['_wc_booking_first_block_time'] ) ? wc_clean( $_POST['_wc_booking_first_block_time'] ) : null,
            'has_person_cost_multiplier' => isset( $_POST['_wc_booking_person_cost_multiplier'] ),
            'has_person_qty_multiplier'  => isset( $_POST['_wc_booking_person_qty_multiplier'] ),
            'has_person_types'           => isset( $_POST['_wc_booking_has_person_types'] ),
            'has_persons'                => isset( $_POST['_wc_booking_has_persons'] ),
            'has_resources'              => isset( $_POST['_wc_booking_has_resources'] ),
            'has_restricted_days'        => isset( $_POST['_wc_booking_has_restricted_days'] ),
            'max_date_unit'              => isset( $_POST['_wc_booking_max_date_unit'] ) ? wc_clean( $_POST['_wc_booking_max_date_unit'] ) : null,
            'max_date_value'             => isset( $_POST['_wc_booking_max_date'] ) ? wc_clean( $_POST['_wc_booking_max_date'] ) : null,
            'max_duration'               => isset( $_POST['_wc_booking_max_duration'] ) ? wc_clean( $_POST['_wc_booking_max_duration'] ) : null,
            'max_persons'                => isset( $_POST['_wc_booking_max_persons_group'] ) ? wc_clean( $_POST['_wc_booking_max_persons_group'] ) : null,
            'min_date_unit'              => isset( $_POST['_wc_booking_min_date_unit'] ) ? wc_clean( $_POST['_wc_booking_min_date_unit'] ) : null,
            'min_date_value'             => isset( $_POST['_wc_booking_min_date'] ) ? wc_clean( $_POST['_wc_booking_min_date'] ) : null,
            'min_duration'               => isset( $_POST['_wc_booking_min_duration'] ) ? wc_clean( $_POST['_wc_booking_min_duration'] ) : null,
            'min_persons'                => isset( $_POST['_wc_booking_min_persons_group'] ) ? wc_clean( $_POST['_wc_booking_min_persons_group'] ) : null,
            'person_types'               => $this->get_posted_person_types( $product ),
            'pricing'                    => $this->get_posted_pricing(),
            'qty'                        => isset( $_POST['_wc_booking_qty'] ) ? wc_clean( $_POST['_wc_booking_qty'] ) : null,
            'requires_confirmation'      => isset( $_POST['_wc_booking_requires_confirmation'] ),
            'resource_label'             => isset( $_POST['_wc_booking_resource_label'] ) ? wc_clean( $_POST['_wc_booking_resource_label'] ) : null,
            'resource_base_costs'        => wp_list_pluck( $resources, 'base_cost' ),
            'resource_block_costs'       => wp_list_pluck( $resources, 'block_cost' ),
            'resource_ids'               => array_keys( $resources ),
            'resources_assignment'       => isset( $_POST['_wc_booking_resources_assignment'] ) ? wc_clean( $_POST['_wc_booking_resources_assignment'] ) : null,
            'restricted_days'            => isset( $_POST['_wc_booking_restricted_days'] ) ? wc_clean( $_POST['_wc_booking_restricted_days'] ) : '',
            'user_can_cancel'            => isset( $_POST['_wc_booking_user_can_cancel'] ),
        ) );
    }

    public function save_booking_meta( $product_id, $data ) {
        if ( version_compare( WC_VERSION, '3.0', '>=' ) || 'booking' !== sanitize_title( stripslashes( $data['product-type'] ) ) ) {
            return;
        }
        $product = new WC_Product_Booking( $product_id );
        $this->set_booking_props( $product );
        $product->save();
    }

    /**
     * Get posted availability fields and format.
     *
     * @return array
     */
    private function get_posted_availability() {
        $availability = array();
        $row_size = isset( $_POST['wc_booking_availability_type'] ) ? sizeof( $_POST['wc_booking_availability_type'] ) : 0;
        for ( $i = 0; $i < $row_size; $i ++ ) {
            $availability[$i]['type'] = wc_clean( $_POST['wc_booking_availability_type'][$i] );
            $availability[$i]['bookable'] = wc_clean( $_POST['wc_booking_availability_bookable'][$i] );
            $availability[$i]['priority'] = intval( $_POST['wc_booking_availability_priority'][$i] );

            switch ( $availability[$i]['type'] ) {
                case 'custom':
                    $availability[$i]['from'] = wc_clean( $_POST['wc_booking_availability_from_date'][$i] );
                    $availability[$i]['to'] = wc_clean( $_POST['wc_booking_availability_to_date'][$i] );
                    break;
                case 'months':
                    $availability[$i]['from'] = wc_clean( $_POST['wc_booking_availability_from_month'][$i] );
                    $availability[$i]['to'] = wc_clean( $_POST['wc_booking_availability_to_month'][$i] );
                    break;
                case 'weeks':
                    $availability[$i]['from'] = wc_clean( $_POST['wc_booking_availability_from_week'][$i] );
                    $availability[$i]['to'] = wc_clean( $_POST['wc_booking_availability_to_week'][$i] );
                    break;
                case 'days':
                    $availability[$i]['from'] = wc_clean( $_POST['wc_booking_availability_from_day_of_week'][$i] );
                    $availability[$i]['to'] = wc_clean( $_POST['wc_booking_availability_to_day_of_week'][$i] );
                    break;
                case 'time':
                case 'time:1':
                case 'time:2':
                case 'time:3':
                case 'time:4':
                case 'time:5':
                case 'time:6':
                case 'time:7':
                    $availability[$i]['from'] = wc_booking_sanitize_time( $_POST['wc_booking_availability_from_time'][$i] );
                    $availability[$i]['to'] = wc_booking_sanitize_time( $_POST['wc_booking_availability_to_time'][$i] );
                    break;
                case 'time:range':
                    $availability[$i]['from'] = wc_booking_sanitize_time( $_POST['wc_booking_availability_from_time'][$i] );
                    $availability[$i]['to'] = wc_booking_sanitize_time( $_POST['wc_booking_availability_to_time'][$i] );

                    $availability[$i]['from_date'] = wc_clean( $_POST['wc_booking_availability_from_date'][$i] );
                    $availability[$i]['to_date'] = wc_clean( $_POST['wc_booking_availability_to_date'][$i] );
                    break;
            }
        }
        return $availability;
    }

    /**
     * Get posted pricing fields and format.
     *
     * @return array
     */
    private function get_posted_pricing() {
        $pricing = array();
        $row_size = isset( $_POST['wc_booking_pricing_type'] ) ? sizeof( $_POST['wc_booking_pricing_type'] ) : 0;
        for ( $i = 0; $i < $row_size; $i ++ ) {
            $pricing[$i]['type'] = wc_clean( $_POST['wc_booking_pricing_type'][$i] );
            $pricing[$i]['cost'] = wc_clean( $_POST['wc_booking_pricing_cost'][$i] );
            $pricing[$i]['modifier'] = wc_clean( $_POST['wc_booking_pricing_cost_modifier'][$i] );
            $pricing[$i]['base_cost'] = wc_clean( $_POST['wc_booking_pricing_base_cost'][$i] );
            $pricing[$i]['base_modifier'] = wc_clean( $_POST['wc_booking_pricing_base_cost_modifier'][$i] );

            switch ( $pricing[$i]['type'] ) {
                case 'custom':
                    $pricing[$i]['from'] = wc_clean( $_POST['wc_booking_pricing_from_date'][$i] );
                    $pricing[$i]['to'] = wc_clean( $_POST['wc_booking_pricing_to_date'][$i] );
                    break;
                case 'months':
                    $pricing[$i]['from'] = wc_clean( $_POST['wc_booking_pricing_from_month'][$i] );
                    $pricing[$i]['to'] = wc_clean( $_POST['wc_booking_pricing_to_month'][$i] );
                    break;
                case 'weeks':
                    $pricing[$i]['from'] = wc_clean( $_POST['wc_booking_pricing_from_week'][$i] );
                    $pricing[$i]['to'] = wc_clean( $_POST['wc_booking_pricing_to_week'][$i] );
                    break;
                case 'days':
                    $pricing[$i]['from'] = wc_clean( $_POST['wc_booking_pricing_from_day_of_week'][$i] );
                    $pricing[$i]['to'] = wc_clean( $_POST['wc_booking_pricing_to_day_of_week'][$i] );
                    break;
                case 'time':
                case 'time:1':
                case 'time:2':
                case 'time:3':
                case 'time:4':
                case 'time:5':
                case 'time:6':
                case 'time:7':
                    $pricing[$i]['from'] = wc_booking_sanitize_time( $_POST['wc_booking_pricing_from_time'][$i] );
                    $pricing[$i]['to'] = wc_booking_sanitize_time( $_POST['wc_booking_pricing_to_time'][$i] );
                    break;
                case 'time:range':
                    $pricing[$i]['from'] = wc_booking_sanitize_time( $_POST['wc_booking_pricing_from_time'][$i] );
                    $pricing[$i]['to'] = wc_booking_sanitize_time( $_POST['wc_booking_pricing_to_time'][$i] );

                    $pricing[$i]['from_date'] = wc_clean( $_POST['wc_booking_pricing_from_date'][$i] );
                    $pricing[$i]['to_date'] = wc_clean( $_POST['wc_booking_pricing_to_date'][$i] );
                    break;
                default:
                    $pricing[$i]['from'] = wc_clean( $_POST['wc_booking_pricing_from'][$i] );
                    $pricing[$i]['to'] = wc_clean( $_POST['wc_booking_pricing_to'][$i] );
                    break;
            }
        }
        return $pricing;
    }

    /**
     * Get posted person types.
     *
     * @return array
     */
    private function get_posted_person_types( $product ) {
        $person_types = array();

        if ( isset( $_POST['person_id'] ) && isset( $_POST['_wc_booking_has_persons'] ) ) {
            $person_ids = $_POST['person_id'];
            $person_menu_order = $_POST['person_menu_order'];
            $person_name = $_POST['person_name'];
            $person_cost = $_POST['person_cost'];
            $person_block_cost = $_POST['person_block_cost'];
            $person_description = $_POST['person_description'];
            $person_min = $_POST['person_min'];
            $person_max = $_POST['person_max'];
            $max_loop = max( array_keys( $_POST['person_id'] ) );

            for ( $i = 0; $i <= $max_loop; $i ++ ) {
                if ( ! isset( $person_ids[$i] ) ) {
                    continue;
                }
                $person_id = absint( $person_ids[$i] );
                $person_type = new WC_Product_Booking_Person_Type( $person_id );
                $person_type->set_props( array(
                    'name'        => wc_clean( stripslashes( $person_name[$i] ) ),
                    'description' => wc_clean( stripslashes( $person_description[$i] ) ),
                    'sort_order'  => absint( $person_menu_order[$i] ),
                    'cost'        => wc_clean( $person_cost[$i] ),
                    'block_cost'  => wc_clean( $person_block_cost[$i] ),
                    'min'         => wc_clean( $person_min[$i] ),
                    'max'         => wc_clean( $person_max[$i] ),
                    'parent_id'   => $product->get_id(),
                ) );
                $person_types[] = $person_type;
            }
        }
        return $person_types;
    }

    /**
     * Get posted resources. Resources are global, but booking products store information about the relationship.
     *
     * @return array
     */
    private function get_posted_resources( $product ) {
        $resources = array();

        if ( isset( $_POST['resource_id'] ) && isset( $_POST['_wc_booking_has_resources'] ) ) {
            $resource_ids = $_POST['resource_id'];
            $resource_menu_order = $_POST['resource_menu_order'];
            $resource_base_cost = $_POST['resource_cost'];
            $resource_block_cost = $_POST['resource_block_cost'];
            $max_loop = max( array_keys( $_POST['resource_id'] ) );
            $resource_base_costs = array();
            $resource_block_costs = array();

            foreach ( $resource_menu_order as $key => $value ) {
                $resources[absint( $resource_ids[$key] )] = array(
                    'base_cost'  => wc_clean( $resource_base_cost[$key] ),
                    'block_cost' => wc_clean( $resource_block_cost[$key] ),
                );
            }
        }

        return $resources;
    }

    public function form_submited() {
        global $WCMp;
        $current_endpoint_key = $WCMp->endpoints->get_current_endpoint();
        $current_vendor_id = afm()->vendor_id;
        if ( $current_vendor_id && ! empty( $_POST ) ) {
            switch ( $current_endpoint_key ) {
                case 'booking-notification':
                    if ( current_vendor_can( 'send_booking_notification' ) && isset( $_POST['send_booking_notification_nonce'] ) && wp_verify_nonce( $_POST['send_booking_notification_nonce'], 'send_booking_notification' ) ) {
                        $notification_product_id = absint( $_POST['notification_product_id'] );
                        $notification_subject = wc_clean( stripslashes( $_POST['notification_subject'] ) );
                        $notification_message = wp_kses_post( stripslashes( $_POST['notification_message'] ) );

                        try {
                            if ( ! $notification_product_id ) {
                                throw new Exception( __( 'Please choose a product', 'woocommerce-bookings' ) );
                            }

                            if ( ! $notification_message ) {
                                throw new Exception( __( 'Please enter a message', 'woocommerce-bookings' ) );
                            }

                            if ( ! $notification_subject ) {
                                throw new Exception( __( 'Please enter a subject', 'woocommerce-bookings' ) );
                            }

                            $bookings = WC_Bookings_Controller::get_bookings_for_product( $notification_product_id );
                            $mailer = WC()->mailer();
                            $notification = $mailer->emails['WC_Email_Booking_Notification'];

                            foreach ( $bookings as $booking ) {
                                $attachments = array();

                                // Add .ics file
                                if ( isset( $_POST['notification_ics'] ) ) {
                                    $generate = new WC_Bookings_ICS_Exporter;
                                    $attachments[] = $generate->get_booking_ics( $booking );
                                }

                                $notification->reset_tags();
                                $notification->trigger( $booking->get_id(), $notification_subject, $notification_message, $attachments );
                            }

                            wc_add_notice( __( 'Notification sent successfully', 'woocommerce-bookings' ) );
                        } catch ( Exception $e ) {
                            wc_add_notice( $e->getMessage(), 'error' );
                        }
                    }
                    break;
                case 'resources':
                    if ( current_vendor_can( 'add_bookable_resource' ) && isset( $_POST['add_resource'] ) && isset( $_POST['bookable_resource_details_nonce'] ) && wp_verify_nonce( $_POST['bookable_resource_details_nonce'], 'bookable_resource_details' ) ) {
                        $vendor_resource_ids = WCMp_AFM_Booking_Integration::get_bookable_product_resource_ids();
                        $is_updated = true;
                        if ( isset( $_POST['resource_id'] ) && $_POST['resource_id'] ) {
                            $resource_id = absint( $_POST['resource_id'] );
                            if ( ! in_array( $resource_id, $vendor_resource_ids ) ) {
                                $resource_id = 0;
                                wp_die( __( 'Invalid resource.', WCMp_AFM_TEXT_DOMAIN ) );
                            }
                        } else {
                            $is_updated = false;
                            // Create post object if not updated
                            $resource_args = array(
                                'post_title'  => wp_strip_all_tags( $_POST['post_title'] ),
                                'post_status' => 'publish',
                                'post_type'   => 'bookable_resource',
                                'post_author' => $current_vendor_id,
                            );
                            $resource_id = wp_insert_post( $resource_args );
                        }
                        if ( ! is_wp_error( $resource_id ) ) {
                            $resource = new WC_Product_Booking_Resource( $resource_id );
                            $resource->set_props( array(
                                'qty'          => wc_clean( $_POST['_wc_booking_qty'] ),
                                'availability' => afm_woo()->get_posted_availability(),
                            ) );
                            $resource->save();
                        } else {
                            wc_add_notice( $resource_id->get_error_message(), 'error' );
                        }
                        if ( ! $is_updated ) {
                            wc_add_notice( __( 'Resource published successfully', WCMp_AFM_TEXT_DOMAIN ), 'success' );
                            wp_redirect( wcmp_get_vendor_dashboard_endpoint_url( 'resources', $resource_id ) );
                            exit;
                        }
                        wc_add_notice( __( 'Resource updated successfully', WCMp_AFM_TEXT_DOMAIN ), 'error' );
                    } else {
                        wp_die( -1 );
                    }
                    break;
                case 'bookings' :
                    if ( current_vendor_can( 'update_booking_details' ) && ! empty( $_POST['booking_id'] ) && isset( $_POST['booking_details_nonce'] ) && wp_verify_nonce( $_POST['booking_details_nonce'], 'booking_details' ) ) {
                        $booking_id = absint( $_POST['booking_id'] );
                        $vendor_bookings = WCMp_AFM_Booking_Integration::get_vendor_booking_array();
                        $vendor_bookings_id = wp_list_pluck( $vendor_bookings, 'ID' );
                        if ( in_array( $booking_id, $vendor_bookings_id ) ) {
                            $booking = new WC_Booking( $_POST['booking_id'] );
                            $booking->set_props( array(
                                'status' => wc_clean( $_POST['_booking_status'] ),
                            ) );
                            $booking->save();
                            wc_add_notice( __( 'Booking status updated successfully', WCMp_AFM_TEXT_DOMAIN ), 'success' );
                        } else {
                            wc_add_notice( __( 'Update failed! Invalid booking, ', WCMp_AFM_TEXT_DOMAIN ), 'success' );
                        }
                    } else {
                        wp_die( -1 );
                    }
                    break;
                case 'create-booking':
                    if ( apply_filters( 'vendor_can_create_booking', true, $current_vendor_id ) ) {
                        $errors = array();
                        $new_booking_id = null;
                        try {
                            if ( ! ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'create_booking_notification' ) ) ) {
                                throw new Exception( __( 'Error - please try again', 'woocommerce-bookings' ) );
                            }

                            if ( ! empty( $_POST['create_booking'] ) ) {
                                $customer_id = isset( $_POST['customer_id'] ) ? absint( $_POST['customer_id'] ) : 0;
                                $bookable_product_id = absint( $_POST['bookable_product_id'] );
                                $booking_order = wc_clean( $_POST['booking_order'] );

                                if ( ! $bookable_product_id ) {
                                    throw new Exception( __( 'Please choose a bookable product', 'woocommerce-bookings' ) );
                                }

                                if ( 'existing' === $booking_order ) {

                                    if ( class_exists( 'WC_Seq_Order_Number_Pro' ) ) {
                                        $order_id = WC_Seq_Order_Number_Pro::find_order_by_order_number( wc_clean( $_POST['booking_order_id'] ) );
                                    } else {
                                        $order_id = absint( $_POST['booking_order_id'] );
                                    }

                                    $booking_order = $order_id;

                                    if ( ! $booking_order || get_post_type( $booking_order ) !== 'shop_order' ) {
                                        throw new Exception( __( 'Invalid order ID provided', 'woocommerce-bookings' ) );
                                    }
                                }

                                $product = wc_get_product( $bookable_product_id );
                                $booking_form = new WC_Booking_Form( $product );
                                $transient = array(
                                    'booking_form'        => $booking_form,
                                    'customer_id'         => $customer_id,
                                    'bookable_product_id' => $bookable_product_id,
                                    'booking_order'       => $booking_order,
                                );
                                set_transient( 'create_booking_' . $bookable_product_id . '_by' . $current_vendor_id, $transient, MINUTE_IN_SECONDS );
                            } elseif ( ! empty( $_POST['create_booking_2'] ) ) {
                                $customer_id = absint( $_POST['customer_id'] );
                                $bookable_product_id = absint( $_POST['bookable_product_id'] );
                                $booking_order = wc_clean( $_POST['booking_order'] );
                                $product = wc_get_product( $bookable_product_id );
                                $booking_form = new WC_Booking_Form( $product );
                                $booking_data = $booking_form->get_posted_data( $_POST );
                                $cost = $booking_form->calculate_booking_cost( $_POST );
                                $booking_cost = $cost && ! is_wp_error( $cost ) ? number_format( $cost, 2, '.', '' ) : 0;
                                $create_order = false;
                                $order_id = 0;
                                $item_id = 0;

                                if ( 'yes' === get_option( 'woocommerce_prices_include_tax' ) ) {
                                    $base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class() );
                                    $base_taxes = WC_Tax::calc_tax( $booking_cost, $base_tax_rates, true );
                                    $booking_cost = round( $booking_cost - array_sum( $base_taxes ), absint( get_option( 'woocommerce_price_num_decimals' ) ) );
                                }

                                $props = array(
                                    'customer_id'   => $customer_id,
                                    'product_id'    => is_callable( array( $product, 'get_id' ) ) ? $product->get_id() : $product->id,
                                    'resource_id'   => isset( $booking_data['_resource_id'] ) ? $booking_data['_resource_id'] : '',
                                    'person_counts' => $booking_data['_persons'],
                                    'cost'          => $booking_cost,
                                    'start'         => $booking_data['_start_date'],
                                    'end'           => $booking_data['_end_date'],
                                    'all_day'       => $booking_data['_all_day'] ? true : false,
                                );

                                if ( 'new' === $booking_order ) {
                                    $create_order = true;
                                    $order_id = $this->create_order( $booking_cost, $customer_id );

                                    if ( ! $order_id ) {
                                        throw new Exception( __( 'Error: Could not create order', 'woocommerce-bookings' ) );
                                    }
                                } elseif ( $booking_order > 0 ) {
                                    $order_id = absint( $booking_order );

                                    if ( ! $order_id || get_post_type( $order_id ) !== 'shop_order' ) {
                                        throw new Exception( __( 'Invalid order ID provided', 'woocommerce-bookings' ) );
                                    }

                                    $order = new WC_Order( $order_id );

                                    if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
                                        update_post_meta( $order_id, '_order_total', $order->get_total() + $booking_cost );
                                    } else {
                                        $order->set_total( $order->get_total( 'edit' ) + $booking_cost );
                                        $order->save();
                                    }

                                    do_action( 'woocommerce_bookings_create_booking_page_add_order_item', $order_id );
                                }

                                if ( $order_id ) {
                                    $item_id = wc_add_order_item( $order_id, array(
                                        'order_item_name' => $product->get_title(),
                                        'order_item_type' => 'line_item',
                                        ) );

                                    if ( ! $item_id ) {
                                        throw new Exception( __( 'Error: Could not create item', 'woocommerce-bookings' ) );
                                    }

                                    if ( ! empty( $customer_id ) ) {
                                        $order = wc_get_order( $order_id );
                                        $keys = array(
                                            'first_name',
                                            'last_name',
                                            'company',
                                            'address_1',
                                            'address_2',
                                            'city',
                                            'state',
                                            'postcode',
                                            'country',
                                        );
                                        $types = array( 'shipping', 'billing' );

                                        foreach ( $types as $type ) {
                                            $address = array();

                                            foreach ( $keys as $key ) {
                                                $address[$key] = (string) get_user_meta( $customer_id, $type . '_' . $key, true );
                                            }
                                            $order->set_address( $address, $type );
                                        }
                                    }

                                    // Add line item meta
                                    wc_add_order_item_meta( $item_id, '_qty', 1 );
                                    wc_add_order_item_meta( $item_id, '_tax_class', $product->get_tax_class() );
                                    wc_add_order_item_meta( $item_id, '_product_id', $product->get_id() );
                                    wc_add_order_item_meta( $item_id, '_variation_id', '' );
                                    wc_add_order_item_meta( $item_id, '_line_subtotal', $booking_cost );
                                    wc_add_order_item_meta( $item_id, '_line_total', $booking_cost );
                                    wc_add_order_item_meta( $item_id, '_line_tax', 0 );
                                    wc_add_order_item_meta( $item_id, '_line_subtotal_tax', 0 );

                                    do_action( 'woocommerce_bookings_create_booking_page_add_order_item', $order_id );
                                }
                                // Calculate the order totals with taxes.
                                $order = wc_get_order( $order_id );
                                if ( is_a( $order, 'WC_Order' ) ) {
                                    $order->calculate_totals( afm_is_enabled_vendor_tax() );
                                }

                                // Create the booking itself
                                $new_booking = new WC_Booking( $props );
                                $new_booking->set_order_id( $order_id );
                                $new_booking->set_order_item_id( $item_id );
                                $new_booking->set_status( $create_order ? 'unpaid' : 'confirmed'  );
                                $new_booking->save();
                                $new_booking_id = $new_booking->get_id();
                                wc_clear_notices();
                            }
                        } catch ( Exception $e ) {
                            $errors[] = $e->getMessage();
                        }

                        foreach ( $errors as $error ) {
                            wc_add_notice( $error, 'error' );
                        }
                        if ( $new_booking_id ) {
                            wp_redirect( wcmp_get_vendor_dashboard_endpoint_url( 'bookings', $new_booking_id ) );
                            exit;
                        }
                    }
                    break;
            }
        }
    }

}
