<?php
/**
 * WCMp Advanced Frontend Manager
 *
 * RnB - WooCommerce Rental & Bookings System Support
 *
 * @author WC Marketplace
 * @package WCMp_AFM/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Rentalpro_Integration {

    protected $id = null;
    protected $tabs = array();
    protected $plugin = 'rentalpro';
    protected $rental_endpoints = array();
    //pair of field name and meta values
    public $form_fields = array();

    public function __construct() {
        global $WCMp;

        $this->tabs = $this->set_additional_tabs();
        $this->rental_endpoints = $this->all_rental_endpoints();

        //filter for adding wcmp endpoint query vars
        add_filter( 'wcmp_endpoints_query_vars', array( $this, 'rental_endpoints_query_vars' ) );
        add_filter( 'wcmp_vendor_dashboard_nav', array( $this, 'rental_dashboard_navs' ) );
        //add endpoint content
        add_action( 'wcmp_vendor_dashboard_rental-calendar_endpoint', array( $this, 'rental_calendar_endpoint' ) );
        if ( current_vendor_can( 'manage_rental_quotes' ) ) {
            add_action( 'wcmp_vendor_dashboard_request-quote_endpoint', array( $this, 'request_quote_endpoint' ) );
            add_action( 'wcmp_vendor_dashboard_quote-details_endpoint', array( $this, 'quote_details_endpoint' ) );
        }
        //dequeue scripts on rental endpoints
        add_action( 'afm_endpoints_dequeue_wcmp_scripts', array( $this, 'dequeue_wcmp_scripts' ), 10, 2 );
        //enqueue required scripts for endpoints added
        add_action( 'afm_enqueue_dashboard_scripts', array( $this, 'rental_endpoint_scripts' ), 10, 4 );

        // Rental Product Additional Tabs
        add_filter( 'wcmp_product_data_tabs', array( $this, 'redq_rental_additional_tabs' ) );
        add_action( 'wcmp_product_tabs_content', array( $this, 'redq_rental_additional_tabs_content' ) );
        // Rental Product add Additional Metabox 
        add_filter( 'afm_exclude_handled_taxonomies', array( $this, 'exclude_redq_rental_taxonomy' ) );
        add_action( 'after_wcmp_afm_product_tags_metabox_panel', array( $this, 'redq_rental_additional_taxonomy_content' ) );

        add_filter( 'wcmp_advance_product_script_params', array( $this, 'add_localize_params' ) );
        // Rental Product Meta Data Save
        add_action( 'wcmp_process_product_meta_redq_rental', array( &$this, 'save_redq_rental_meta' ), 10, 2 );
    }

    //this will be called from the main Integration class after WCMp_AFM_Add_Product_Endpoint class constructor executed
    public function set_props( $id ) {
        $this->id = $id;

        //after setting id populate all fields and meta values
        $this->form_fields = $this->set_form_fields();
    }

    protected function set_additional_tabs() {
        return array(
            'rental_inventory'  => array(
                'label'    => __( 'Inventory', 'redq-rental' ),
                'target'   => 'rental_inventory_product_data',
                'class'    => array( 'show_if_redq_rental' ),
                'priority' => '80',
            ),
            'price_calculation' => array(
                'label'    => __( 'Price Calculation', 'redq-rental' ),
                'target'   => 'price_calculation_product_data',
                'class'    => array( 'show_if_redq_rental' ),
                'priority' => '90',
                'fields'   => array(
                    'pricing_type'         => '',
                    'hourly_price'         => '',
                    'general_price'        => '',
                    'redq_daily_pricing'   => array(),
                    'redq_monthly_pricing' => array(),
                    'redq_day_ranges_cost' => array(),
                ),
            ),
            'price_discount'    => array(
                'label'    => __( 'Price Discount', 'redq-rental' ),
                'target'   => 'price_discount_product_data',
                'class'    => array( 'show_if_redq_rental' ),
                'priority' => '100',
                'fields'   => array(
                    'redq_price_discount_cost' => '',
                ),
            ),
            'redq_settings'     => array(
                'label'      => __( 'Settings', 'redq-rental' ),
                'target'     => 'redq_settings_product_data',
                'class'      => array( 'show_if_redq_rental' ),
                'priority'   => '110',
                'fields'     => array(
                    'rnb_settings_for_display'             => '',
                    // labels tab
                    'rnb_settings_for_labels'              => '',
                    'redq_show_pricing_flipbox_text'       => '',
                    'redq_flip_pricing_plan_text'          => '',
                    'redq_pickup_location_heading_title'   => '',
                    'redq_pickup_loc_placeholder'          => '',
                    'redq_dropoff_location_heading_title'  => '',
                    'redq_return_loc_placeholder'          => '',
                    'redq_pickup_date_heading_title'       => '',
                    'redq_pickup_date_placeholder'         => '',
                    'redq_pickup_time_placeholder'         => '',
                    'redq_dropoff_date_heading_title'      => '',
                    'redq_dropoff_date_placeholder'        => '',
                    'redq_dropoff_time_placeholder'        => '',
                    'redq_rnb_cat_heading'                 => '',
                    'redq_resources_heading_title'         => '',
                    'redq_adults_heading_title'            => '',
                    'redq_adults_placeholder'              => '',
                    'redq_childs_heading_title'            => '',
                    'redq_childs_placeholder'              => '',
                    'redq_security_deposite_heading_title' => '',
                    'redq_discount_text_title'             => '',
                    'redq_instance_pay_text_title'         => '',
                    'redq_total_cost_text_title'           => '',
                    'redq_book_now_button_text'            => '',
                    'redq_rfq_button_text'                 => '',
                    // conditions tab
                    'rnb_settings_for_conditions'          => '',
                    'redq_block_general_dates'             => '',
                    'redq_calendar_date_format'            => '',
                    'redq_max_time_late'                   => '',
                    'redq_max_rental_days'                 => '',
                    'redq_min_rental_days'                 => '',
                    'redq_rental_starting_block_dates'     => '',
                    'redq_rental_post_booking_block_dates' => '',
                    'redq_time_interval'                   => '',
                    'redq_allowed_times'                   => '',
                    'redq_rental_off_days'                 => array(),
                    // validation tab
                    'rnb_settings_for_validations'         => '',
                    'redq_rental_fri_min_time'             => '00:00',
                    'redq_rental_fri_max_time'             => '24:00',
                    'redq_rental_sat_min_time'             => '00:00',
                    'redq_rental_sat_max_time'             => '24:00',
                    'redq_rental_sun_min_time'             => '00:00',
                    'redq_rental_sun_max_time'             => '24:00',
                    'redq_rental_mon_min_time'             => '00:00',
                    'redq_rental_mon_max_time'             => '24:00',
                    'redq_rental_thu_min_time'             => '00:00', //wrong spelling for tuesday RNB
                    'redq_rental_thu_max_time'             => '24:00', //wrong spelling for tuesday RNB
                    'redq_rental_wed_min_time'             => '00:00',
                    'redq_rental_wed_max_time'             => '24:00',
                    'redq_rental_thur_min_time'            => '00:00',
                    'redq_rental_thur_max_time'            => '24:00',
                ),
                'checkboxes' => array(
                    //display tab
                    'redq_rental_local_show_pickup_date'                     => 'open',
                    'redq_rental_local_show_pickup_time'                     => 'open',
                    'redq_rental_local_show_dropoff_date'                    => 'open',
                    'redq_rental_local_show_dropoff_time'                    => 'open',
                    'redq_rental_local_show_pricing_flip_box'                => 'open',
                    'redq_rental_local_show_price_discount_on_days'          => 'open',
                    'redq_rental_local_show_price_instance_payment'          => 'open',
                    'redq_rental_local_show_request_quote'                   => 'closed',
                    'redq_rental_local_show_book_now'                        => 'open',
                    //Conditions tab
                    'redq_rental_local_enable_single_day_time_based_booking' => 'open',
                    //Validation tab
                    'redq_rental_local_required_pickup_location'             => 'closed',
                    'redq_rental_local_required_return_location'             => 'closed',
                    'redq_rental_local_required_person'                      => 'closed',
                    'redq_rental_required_local_pickup_time'                 => 'closed',
                    'redq_rental_required_local_return_time'                 => 'closed',
                ),
            )
        );
    }

    public function set_form_fields() {
        $temp = array();
        $current_vendor_id = afm()->vendor_id;
        if ( $current_vendor_id ) {
            if ( false === ( $temp = get_transient( 'rental_form_fields_' . $current_vendor_id . '_' . $this->id . '_transient' ) ) ) {
                foreach ( $this->tabs as $key => $tab ) {
                    $temp[$key] = array();
                    $fields = array();
                    if ( ! empty( $tab['fields'] ) && is_array( $tab['fields'] ) ) {
                        $fields = array_merge( $fields, $tab['fields'] );
                    }
                    if ( ! empty( $tab['checkboxes'] ) && is_array( $tab['checkboxes'] ) ) {
                        $fields = array_merge( $fields, $tab['checkboxes'] );
                    }

                    $sub_arr = array();
                    foreach ( $fields as $field => $default ) {
                        $sub_arr[$field] = get_post_meta( $this->id, $field, true );
                        if ( empty( $sub_arr[$field] ) && $default !== '' ) {
                            $sub_arr[$field] = $default;
                        }
                    }
                    $temp[$key] = array_merge( $temp[$key], $sub_arr );
                }
                set_transient( 'rental_form_fields_' . $current_vendor_id . '_' . $this->id . '_transient', $temp, 5 * MINUTE_IN_SECONDS );
            }
        }
        return (array) $temp;
    }

    public function is_quote_menu_enabled() {
        $quote_menu = get_option( 'rnb_enable_rft_endpoint', true );
        if ( $quote_menu === 'yes' )
            return true;

        return false;
    }

    /**
     * Return all the `RedQ Rental` endpoints added to vendor dashboard
     * 
     * @return array endpoints 
     */
    private function all_rental_endpoints() {
        return apply_filters( "wcmp_afm_{$this->plugin}_endpoint_list", afm()->dependencies->get_allowed_endpoints( $this->plugin ) );
    }

    public function rental_endpoints_query_vars( $endpoints ) {
        return afm()->dependencies->plugin_endpoints_query_vars( $endpoints, $this->rental_endpoints );
    }

    public function rental_dashboard_navs( $navs ) {
        $parent_menu = array(
            'label'      => __( 'Rentals', 'wcmp-afm' ),
            'capability' => 'wcmp_vendor_dashboard_menu_vendor_rentals_capability',
            'position'   => 31,
            'nav_icon'   => 'wcmp-font ico-rental-icon',
            'plugin'     => $this->plugin,
        );
        return afm()->dependencies->plugin_dashboard_navs( $navs, $this->rental_endpoints, $parent_menu );
    }

    public function rental_calendar_endpoint() {
        afm()->endpoints->load_class( 'rental-calendar' );
        $rental_calendar = new WCMp_AFM_Rental_Calendar_Endpoint();
        $rental_calendar->output();
    }

    public function request_quote_endpoint() {
        afm()->endpoints->load_class( 'request-quote' );
        $request_quote = new WCMp_AFM_Request_Quote_Endpoint();
        $request_quote->output();
    }

    public function quote_details_endpoint() {
        afm()->endpoints->load_class( 'quote-details' );
        $quote_details = new WCMp_AFM_Quote_Details_Endpoint();
        $quote_details->output();
    }

    public function dequeue_wcmp_scripts( $flag, $endpoint ) {
        if ( $endpoint === 'rental-calendar' || $endpoint === 'request-quote' || $endpoint === 'quote-details' ) {
            return true;
        }
        return $flag;
    }

    public function rental_endpoint_scripts( $endpoint, $frontend_script_path, $lib_path, $suffix ) {
        global $WCMp;
        switch ( $endpoint ) {
            case 'rental-calendar':
                afm()->library->load_fullcalendar_lib();
                wp_register_script( 'afm-rental-calendar-js', $frontend_script_path . 'rental-calendar.js', array( 'afm-qtip2-js', 'afm-fullcalendar-js' ), afm()->version, true );
                break;
            case 'request-quote':
                if ( $this->is_quote_menu_enabled() ) {
                    $WCMp->library->load_dataTable_lib();
                    wp_register_script( 'afm-rental-quotes-js', $frontend_script_path . 'rental-quotes.js', array( 'wcmp-datatable-script', 'wcmp-datatable-bs-script' ), afm()->version, true );
                }
                break;
            case 'quote-details':
                if ( $this->is_quote_menu_enabled() ) {
                    wp_register_script( 'afm-quote-details-js', $frontend_script_path . 'rental-quote-details.js', array( 'jquery' ), afm()->version, true );
                }
                break;
        }
    }

    public function add_localize_params( $params ) {
        $new_params = array(
            'add_inventory_item_nonce' => wp_create_nonce( 'add-inventory-item' ),
            'add_availability_nonce'   => wp_create_nonce( 'add-inventory-availability' ),
            'add_days_range_nonce'     => wp_create_nonce( 'add-days-range' ),
            'add_price_discount_nonce' => wp_create_nonce( 'add-price-discount' ),
            'remove_inventory_item'    => esc_js( __( 'Remove this inventory item?', 'wcmp-afm' ) ),
            'remove_availability'      => esc_js( __( 'Remove this availability range?', 'wcmp-afm' ) ),
            'remove_days_range'        => esc_js( __( 'Remove this days range?', 'wcmp-afm' ) ),
            'remove_price_discount'    => esc_js( __( 'Remove this discount?', 'wcmp-afm' ) ),
            'currency_symbol'          => get_woocommerce_currency_symbol(),
        );
        return array_merge( $params, $new_params );
    }

    public function redq_rental_additional_tabs( $product_tabs ) {
        return array_merge( $product_tabs, $this->tabs );
    }

    public function redq_rental_additional_tabs_content() {
        foreach ( $this->tabs as $key => $tab ) {
            afm()->template->get_template( 'products/rental/html-product-data-' . str_replace( '_', '-', $key ) . '.php', array( 'id' => $this->id, 'tab' => $tab['target'], 'fields' => $this->form_fields[$key] ) );
        }
        return;
    }

    public function exclude_redq_rental_taxonomy( $exclude_list ) {
        return array_merge( $exclude_list, array( 'car_company' ) );
    }

    /**
     * Add Rental specific taxonomy metabox 
     * @param type $product_id
     */
    public function redq_rental_additional_taxonomy_content( $product_id ) {
        ob_start();
        $product_terms = afm_get_product_terms_HTML( 'car_company', $product_id );
        if ( $product_terms ) {
            ?>
            <div class="panel panel-default pannel-outer-heading car_company_widget_wrap show_if_redq_rental">
                <div class="panel-heading">
                    <h3 class="pull-left"><?php esc_html_e( 'Car Company', 'redq-rental' ); ?></h3>
                </div>
                <div class="panel-body panel-content-padding form-group-wrapper"> 
                    <?php
                    echo $product_terms;
                    ?>
                </div>
            </div>
            <?php
        }
    }

    public function save_redq_rental_meta( $product_id, $data ) {
        $current_vendor_id = afm()->vendor_id;
        if ( $current_vendor_id && isset( $data['product-type'] ) && $data['product-type'] == 'redq_rental' ) {
            delete_transient( 'rental_form_fields_' . $current_vendor_id . '_' . $product_id . '_transient' );

            // save all data
            $redq_booking_data = array();

            foreach ( $this->tabs as $key => $tab ) {
                if ( isset( $tab['fields'] ) && is_array( $tab['fields'] ) ) {
                    foreach ( $tab['fields'] as $meta_name => $dfvalue ) {
                        if ( isset( $data[$meta_name] ) ) {
                            update_post_meta( $product_id, $meta_name, $data[$meta_name] );
                        } elseif ( $dfvalue !== '' ) {
                            update_post_meta( $product_id, $meta_name, $dfvalue );
                        }
                    }
                }
                if ( isset( $tab['checkboxes'] ) && is_array( $tab['checkboxes'] ) ) {
                    foreach ( $tab['checkboxes'] as $meta_name => $dfvalue ) {
                        if ( isset( $data[$meta_name] ) ) {
                            update_post_meta( $product_id, $meta_name, $data[$meta_name] );
                        } elseif ( $dfvalue !== '' ) {
                            update_post_meta( $product_id, $meta_name, 'closed' );
                        }
                    }
                }
            }

            //update woocommerce product price
            $pricing_type = $data['pricing_type'];
            switch ( $pricing_type ) {
                case "general_pricing":
                    if ( isset( $data["general_price"] ) ) {
                        update_post_meta( $product_id, '_price', $data["general_price"] );
                    }
                    break;
                case "daily_pricing":
                    if ( isset( $data["redq_daily_pricing"] ) ) {
                        $daily_pricing = $data["redq_daily_pricing"];
                        $today = date( 'N' );
                        switch ( $today ) {
                            case '7':
                                update_post_meta( $product_id, '_price', $daily_pricing['sunday'] );
                                break;
                            case '1':
                                update_post_meta( $product_id, '_price', $daily_pricing['monday'] );
                                break;
                            case '2':
                                update_post_meta( $product_id, '_price', $daily_pricing['tuesday'] );
                                break;
                            case '3':
                                update_post_meta( $product_id, '_price', $daily_pricing['wednesday'] );
                                break;
                            case '4':
                                update_post_meta( $product_id, '_price', $daily_pricing['thursday'] );
                                break;
                            case '5':
                                update_post_meta( $product_id, '_price', $daily_pricing['friday'] );
                                break;
                            case '6':
                                update_post_meta( $product_id, '_price', $daily_pricing['saturday'] );
                                break;
                            default:
                                update_post_meta( $product_id, '_price', 'Daily price not set' );
                                break;
                        }
                    }
                    break;
                case "monthly_pricing":
                    if ( isset( $data["redq_monthly_pricing"] ) ) {
                        $monthly_pricing = $data["redq_monthly_pricing"];
                        $current_month = date( 'm' );
                        switch ( $current_month ) {
                            case '1':
                                update_post_meta( $product_id, '_price', $monthly_pricing['january'] );
                                break;
                            case '2':
                                update_post_meta( $product_id, '_price', $monthly_pricing['february'] );
                                break;
                            case '3':
                                update_post_meta( $product_id, '_price', $monthly_pricing['march'] );
                                break;
                            case '4':
                                update_post_meta( $product_id, '_price', $monthly_pricing['april'] );
                                break;
                            case '5':
                                update_post_meta( $product_id, '_price', $monthly_pricing['may'] );
                                break;
                            case '6':
                                update_post_meta( $product_id, '_price', $monthly_pricing['june'] );
                                break;
                            case '7':
                                update_post_meta( $product_id, '_price', $monthly_pricing['july'] );
                                break;
                            case '8':
                                update_post_meta( $product_id, '_price', $monthly_pricing['august'] );
                                break;
                            case '9':
                                update_post_meta( $product_id, '_price', $monthly_pricing['september'] );
                                break;
                            case '10':
                                update_post_meta( $product_id, '_price', $monthly_pricing['october'] );
                                break;
                            case '11':
                                update_post_meta( $product_id, '_price', $monthly_pricing['november'] );
                                break;
                            case '12':
                                update_post_meta( $product_id, '_price', $monthly_pricing['december'] );
                                break;
                            default:
                                update_post_meta( $product_id, '_price', 'Daily price not set' );
                                break;
                        }
                    }
                    break;
                case "days_range":
                    if ( isset( $data["redq_day_ranges_cost"] ) ) {
                        update_post_meta( $product_id, '_price', $data["redq_day_ranges_cost"][0]['range_cost'] );
                    }
                    break;
            }
            //update date format
            if ( $data['redq_calendar_date_format'] === 'd/m/Y' ) {
                update_post_meta( $product_id, 'redq_choose_european_date_format', 'yes' );
            } else {
                update_post_meta( $product_id, 'redq_choose_european_date_format', 'no' );
            }

            $post_data_unique_names = isset( $data['redq_inventory'] ) ? wp_list_pluck( $data['redq_inventory'], 'products_unique_name' ) : null;
            if ( empty( $post_data_unique_names ) ) {
                unset( $data['redq_inventory'] );
                $data['redq_inventory'][0] = array( 'products_unique_name' => get_the_title( $product_id ) );
            }
            //Previous inventory data
            $inventory_child_ids = get_post_meta( $product_id, 'inventory_child_posts', true );
            $inventory_products_unique_names = get_post_meta( $product_id, 'redq_inventory_products_quique_models', true );

            //Form data
            $resource_identifier = array();
            $current_inventory_child_ids = array();
            $current_inventory_products_unique_names = array();

            // Inventory Availability
            $rental_availability_block_dates = array();
            $intialize_block_dates_and_times = array();

            $intialize_rental_availability = array();
            $intialize_rental_availability['block_dates'] = array();
            $intialize_rental_availability['block_times'] = array();
            $intialize_rental_availability['only_block_dates'] = array();

            foreach ( $data['redq_inventory'] as $rental_inventory_child ) {
                if ( ! empty( $rental_inventory_child['inventory_child_id'] ) ) {
                    $inventory_id = $rental_inventory_child['inventory_child_id'];
                    $new_inventory_child = false;
                    //This is for dummy imported data
                    $checkinven = get_post_meta( $product_id, 'redq_block_dates_and_times', true );
                    if ( ! is_array( $checkinven ) ) {
                        $intialize_block_dates_and_times[$inventory_id] = $intialize_rental_availability;
                        update_post_meta( $product_id, 'redq_block_dates_and_times', $intialize_block_dates_and_times );
                    }
                } else {
                    $inventory_id = '';
                    $new_inventory_child = true;
                }

                $defaults = array(
                    'ID'                    => $inventory_id,
                    'post_author'           => $current_vendor_id,
                    'post_content'          => $rental_inventory_child['products_unique_name'],
                    'post_content_filtered' => '',
                    'post_title'            => $rental_inventory_child['products_unique_name'],
                    'post_excerpt'          => '',
                    'post_status'           => 'publish',
                    'post_type'             => 'inventory',
                    'comment_status'        => '',
                    'ping_status'           => '',
                    'post_password'         => '',
                    'to_ping'               => '',
                    'pinged'                => '',
                    'post_parent'           => $product_id,
                    'menu_order'            => 0,
                    'guid'                  => '',
                    'import_id'             => 0,
                    'context'               => '',
                );
                $inventory_id = wp_insert_post( $defaults );

                if ( in_array( 'sitepress-multilingual-cms/sitepress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && function_exists( 'icl_object_id' ) ) {
                    global $sitepress;
                    $trid = $sitepress->get_element_trid( $inventory_id, 'post_inventory' );
                    $sitepress->set_element_language_details( $inventory_id, 'post_inventory', $trid, ICL_LANGUAGE_CODE );
                }

                $resource_identifier[$inventory_id]['title'] = $rental_inventory_child['products_unique_name'];
                $resource_identifier[$inventory_id]['inventory_id'] = $inventory_id;

                array_push( $current_inventory_child_ids, $inventory_id );
                array_push( $current_inventory_products_unique_names, $rental_inventory_child['products_unique_name'] );

                // 1. Set terms for rnb categories taxonomy
                $rnb_taxonomies = array( 'rnb_categories', 'pickup_location', 'dropoff_location', 'resource', 'person', 'deposite', 'attributes', 'features' );
                foreach ( $rnb_taxonomies as $taxonomy ) {
                    if ( ! empty( $rental_inventory_child[$taxonomy] ) )
                        wp_set_object_terms( $inventory_id, $rental_inventory_child[$taxonomy], $taxonomy );
                    else
                        wp_set_object_terms( $inventory_id, '', $taxonomy );
                }

                // 2. Rental Availability
                if ( isset( $rental_inventory_child['redq_rental_availability'] ) ) {
                    $redq_rental_availability = array();
                    foreach ( $rental_inventory_child['redq_rental_availability'] as $key => $inventory_avail ) {
                        $redq_rental_availability[$key] = array_merge( $inventory_avail, array( 'post_id' => $inventory_id ) );
                    }
                    update_post_meta( $inventory_id, 'redq_rental_availability', $redq_rental_availability );
                    $intialize_rental_availability['block_dates'] = $redq_rental_availability;
                }

                $rental_availability_block_dates[$inventory_id] = $intialize_rental_availability;
            }
            update_post_meta( $product_id, 'inventory_child_posts', $current_inventory_child_ids );
            update_post_meta( $product_id, 'redq_inventory_products_quique_models', $current_inventory_products_unique_names );
            update_post_meta( $product_id, 'resource_identifier', $resource_identifier );

            update_post_meta( $product_id, 'redq_block_dates_and_times', $rental_availability_block_dates );

            // Delete unassociated Inventory Child ID's
            $invalid_inventory_posts = array_diff( $inventory_child_ids, $current_inventory_child_ids );
            if ( ! empty( $invalid_inventory_posts ) ) {
                foreach ( $invalid_inventory_posts as $post_id ) {
                    wp_delete_post( $post_id );
                }
            }
        }
    }

}
