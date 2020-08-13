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

class WCMp_AFM_Appointment_Integration {

    protected $id = null;
    protected $tabs = array();
    protected $appointment_product = null;
    protected $restricted_meta = array();
    protected $restricted_days = array();
    protected $plugin = 'appointment';
    protected $appointment_endpoints = array();

    public function __construct() {
        $this->tabs = $this->set_additional_tabs();
        $this->appointment_endpoints = $this->all_appointment_endpoints();

        add_filter( 'wcmp_endpoints_query_vars', array( $this, 'appointment_endpoints_query_vars' ) );
        add_filter( 'wcmp_vendor_dashboard_nav', array( $this, 'appointment_dashboard_navs' ) );

        $this->call_endpoint_contents();

        add_action( 'wcmp_product_type_options', array( $this, 'appointment_additional_product_type_options' ) );

        add_filter( 'wcmp_product_data_tabs', array( $this, 'appointment_additional_tabs' ) );
        add_action( 'wcmp_product_tabs_content', array( $this, 'appointment_additional_tabs_content' ) );

        add_action( 'afm_enqueue_dashboard_scripts', array( $this, 'appointment_endpoint_scripts' ), 10, 4 );

        add_action('wcmp_vendor_dash_before_order_itemmeta', array( $this, 'appointment_order_item_details' ));

        add_filter( 'woocommerce_email_classes', array( $this, 'afm_appointment_emails' ) );
        add_filter( 'woocommerce_email_attachments', array( $this, 'vendor_attach_ics_file' ), 10, 3 );
        add_action( 'woocommerce_vendor_new_appointment_notification', array( $this, 'send_appointment_notification_to_vendor' ) );

        add_filter( 'general_tab_pricing_section', array( $this, 'include_appointment_type' ) );
        add_filter( 'general_tab_tax_section', array( $this, 'include_appointment_type' ) );
        add_action( 'wcmp_afm_after_general_product_data', array( $this, 'appointment_after_general_product_data' ) );
        add_filter( 'inventory_tab_stock_status_section_invisibility', array( $this, 'include_appointment_type' ) );
        add_action( 'wcmp_afm_product_options_sku', array( $this, 'appointment_after_inventory_product_data' ) );

        add_action( 'wcmp_process_product_meta_appointment', array( $this, 'save_appointment_meta' ), 10, 2 );
        //above WC version 3.0 (set props before product save)
        add_action( 'wcmp_process_product_object', array( $this, 'set_appointment_props' ), 20 );

        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            add_action( 'template_redirect', array( $this, 'form_submited' ), 90 );
        }
    }

    //this will be called from the main Integration class after WCMp_AFM_Add_Product_Endpoint class constructor executed
    public function set_props( $id ) {
        $this->id = $id;
        $this->appointment_product = new WC_Product_Appointment( $this->id );
        //after setting id populate all fields and meta values
        $this->restricted_meta = $this->appointment_product->get_restricted_days();
        for ( $i = 0; $i < 7; $i ++ ) {
            if ( $this->restricted_meta && in_array( $i, $this->restricted_meta ) ) {
                $this->restricted_days[$i] = $i;
            } else {
                $this->restricted_days[$i] = false;
            }
        }
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

    private function set_additional_tabs() {
        $appointment_tabs = array();

        $appointment_tabs['appointment-availability'] = array(
            'p_type'   => 'appointment',
            'label'    => __( 'Availability', 'woocommerce-appointments' ),
            'target'   => 'appointment_availability_product_data',
            'class'    => array( 'show_if_appointment' ),
            'priority' => '22',
        );
        return $appointment_tabs;
    }

    private function all_appointment_endpoints() {
        return apply_filters( "wcmp_afm_{$this->plugin}_endpoint_list", afm()->dependencies->get_allowed_endpoints( $this->plugin ) );
    }

    public function appointment_endpoints_query_vars( $endpoints ) {
        return afm()->dependencies->plugin_endpoints_query_vars( $endpoints, $this->appointment_endpoints);
    }

    public function appointment_dashboard_navs( $navs ) {
        $parent_menu = array(
            'label'      => __( 'Appointments', 'woocommerce-appointments' ),
            'capability' => 'wcmp_vendor_dashboard_menu_appointment_capability',
            'position'   => 31,
            'nav_icon'   => 'wcmp-font ico-appointment_icon',
            'plugin'     => $this->plugin,
        );
        return afm()->dependencies->plugin_dashboard_navs( $navs, $this->appointment_endpoints, $parent_menu );
    }

    public function call_endpoint_contents() {
        //add endpoint content
        foreach ( $this->appointment_endpoints as $key => $endpoint ) {
            $cap = ! empty( $endpoint['vendor_can'] ) ? $endpoint['vendor_can'] : '';
            if ( $cap && current_vendor_can( $cap ) ) {
                add_action( 'wcmp_vendor_dashboard_' . $key . '_endpoint', array( $this, 'appointment_endpoints_callback' ) );
            }
        }
    }

    public function appointment_endpoints_callback() {
        $endpoint_name = str_replace( array( 'wcmp_vendor_dashboard_', '_endpoint' ), '', current_filter() );
        afm()->endpoints->load_class( $endpoint_name );
        $classname = 'WCMp_AFM_' . ucwords( str_replace( '-', '_', $endpoint_name ), '_' ) . '_Endpoint';
        $endpoint_class = new $classname;
        $endpoint_class->output();
    }

    public function appointment_additional_product_type_options( $options ) {
        $options['virtual']['wrapper_class'] .= ' show_if_appointment';
        $options['downloadable']['wrapper_class'] .= ' show_if_appointment';
        return $options;
    }

    public function appointment_additional_tabs( $product_tabs ) {
        if ( isset( $product_tabs['inventory']['class'] ) ) {
            $product_tabs['inventory']['class'][] = 'show_if_appointment';
        }
        return array_merge( $product_tabs, $this->tabs );
    }

    public function appointment_additional_tabs_content() {
        foreach ( $this->tabs as $key => $tab ) {
            afm()->template->get_template( 'products/appointment/html-product-data-' . str_replace( '_', '-', $key ) . '.php', array( 'id' => $this->id, 'tab' => $tab['target'], 'self' => $this, 'appointable_product' => $this->appointment_product, 'restricted_days' => $this->restricted_days ) );
        }
        return;
    }

    public function include_appointment_type( $types ) {
        $types[] = 'appointment';
        return $types;
    }

    public function appointment_after_general_product_data() {
        if ( wcmp_is_allowed_product_type( 'appointment' ) ) {
            afm()->template->get_template( 'products/appointment/html-product-data-general.php', array( 'id' => $this->id, 'tab' => 'general', 'self' => $this, 'appointable_product' => $this->appointment_product ) );
        }
    }

    public function appointment_after_inventory_product_data() {
        if ( wcmp_is_allowed_product_type( 'appointment' ) ) {
            afm()->template->get_template( 'products/appointment/html-product-data-inventory.php', array( 'id' => $this->id, 'tab' => 'inventory', 'self' => $this, 'appointable_product' => $this->appointment_product ) );
        }
    }

    public static function get_events_in_date_range( $start_date, $end_date, $product_id = 0, $check_in_cart = true, $filters = array() ) {
        $appointments = WC_Appointment_Data_Store::get_appointments_in_date_range( $start_date, $end_date, $product_id, $check_in_cart, $filters, true );
        $min_date     = date( 'Y-m-d', $start_date );
        $max_date     = date( 'Y-m-d', $end_date );

        // Filter only for events synced from Google Calendar.
        $availability_filters = array(
            array(
                'key'     => 'kind',
                'compare' => '=',
                'value'   => 'availability#global',
            ),
            array(
                'key'     => 'event_id',
                'compare' => '!=',
                'value'   => '',
            ),
        );

        $global_availabilities = WC_Data_Store::load( 'appointments-availability' )->get_all( $availability_filters, $min_date, $max_date );

        return array_merge( $appointments, $global_availabilities );



        // $args = array(
        //     'status'       => get_wc_appointment_statuses(),
        //     'object_id'    => $product_id,
        //     'object_type'  => 'product',
        //     'date_between' => array(
        //         'start' => $start_date,
        //         'end'   => $end_date,
        //     ),
        // );

        // if ( ! $check_in_cart ) {
        //     $args['status'] = array_diff( $args['status'], array( 'in-cart' ) );
        // }

        // if ( $product_id ) {
        //         $args['product_id'] = absint( $product_id );
        // }
        // return array_intersect( WC_Appointment_Data_Store::get_appointment_ids_by( $args ), wp_list_pluck( self::get_vendor_appointment_array(), 'ID' ) );
    }

    public function appointment_endpoint_scripts( $endpoint, $frontend_script_path, $lib_path, $suffix ) {
        global $WCMp;

                if ( current_vendor_can( 'manage_appointments' ) ) {
                    wp_enqueue_style( 'select2' );
                    //wp_enqueue_style( 'wc-appointments-styles', WC_BOOKINGS_PLUGIN_URL . '/assets/css/frontend.css', null, WC_BOOKINGS_VERSION );
                    wp_enqueue_script( 'select2' );
                    $create_appointment_params = array(
                        'ajax_url'               => admin_url( 'admin-ajax.php' ),
                        'search_customers_nonce' => wp_create_nonce( 'search-customers' ),
                    );
                    wp_localize_script( 'afm-create-appointment-js', 'create_appointment_params', $create_appointment_params );
                    wp_enqueue_script( 'afm-create-appointment-js' );
                    wp_enqueue_script( 'afm-create-appointment-js', $frontend_script_path . 'create-appointment.js', array( 'jquery', 'select2' ), afm()->version, true );
                    $WCMp->library->load_dataTable_lib();
                    wp_register_script( 'afm-appointments-js', $frontend_script_path . 'appointments.js', array( 'jquery', 'wcmp-datatable-script', 'wcmp-datatable-bs-script' ), afm()->version, true );
        }
    }

    public function appointment_order_item_details() {
        
    }

    public function afm_appointment_emails($emails ) {
        if ( ! isset( $emails['WC_Email_Vendor_New_Appointment'] ) ) {
            include('email/appointment/class-wcmp-vendor-new-appointment.php');
            $emails['WC_Email_Vendor_New_Appointment'] = new WC_Email_Vendor_New_Appointment();
        }
        if ( ! isset( $emails['WC_Email_Vendor_Appointment_Cancelled'] ) ) {
            include('email/appointment/class-wcmp-vendor-appointment-cancelled.php');
            $emails['WC_Email_Vendor_Appointment_Cancelled'] = new WC_Email_Vendor_Appointment_Cancelled();
        }
        return $emails;
    }

    public function vendor_attach_ics_file( $attachments, $email_id, $object ) {
        $available = apply_filters(
            'wcmp_afm_appointments_emails_ics',
            array(
                'appointment_confirmed',
                'appointment_reminder',
                'vendor_new_appointment',
                'customer_processing_order',
                'customer_completed_order',
            )
        );

        #error_log( var_export( $email_id, true ) );
        #error_log( var_export( $object, true ) );

        if ( in_array( $email_id, $available ) ) {
            $generate = new WC_Appointments_ICS_Exporter();

            // Email object is for WC_Order.
            if ( is_a( $object, 'WC_Order' ) ) {
                $appointment_ids = WC_Appointment_Data_Store::get_appointment_ids_from_order_id( $object->get_id() );

                // Order contains appointments.
                if ( $appointment_ids ) {
                    foreach ( $appointment_ids as $appointment_id ) {
                        $appointment   = get_wc_appointment( $appointment_id );
                        $attachments[] = $generate->get_appointment_ics( $appointment );
                    }
                }
            // Email object is for single WC_Appointment.
            } elseif ( is_a( $object, 'WC_Appointment' ) ) {
                $attachments[] = $generate->get_appointment_ics( $object );
            }
        }

        return $attachments;
    }

    public function send_appointment_notification_to_vendor( $appointment_id ) {
        $notification = WC()->mailer()->emails['WC_Email_Vendor_New_Appointment'];
        $notification->trigger( $appointment_id );
    }

    public function set_appointment_props( $product ) {
        // Only set props if the product is a appointable product.
        if ( ! is_wc_appointment_product( $product ) ) {
            return;
        }

        $availability = $this->save_product_availability( $product );
        $z=$product->set_props(
            array(
                'has_price_label'         => isset( $_POST['_wc_appointment_has_price_label'] ),
                'price_label'             => wc_clean( $_POST['_wc_appointment_price_label'] ),
                'has_pricing'             => isset( $_POST['_wc_appointment_has_pricing'] ),
                'pricing'                 => $this->get_posted_pricing(),
                'qty'                     => wc_clean( $_POST['_wc_appointment_qty'] ),
                'qty_min'                 => wc_clean( $_POST['_wc_appointment_qty_min'] ),
                'qty_max'                 => wc_clean( $_POST['_wc_appointment_qty_max'] ),
                'duration_unit'           => wc_clean( $_POST['_wc_appointment_duration_unit'] ),
                'duration'                => wc_clean( $_POST['_wc_appointment_duration'] ),
                'interval_unit'           => wc_clean( $_POST['_wc_appointment_interval_unit'] ),
                'interval'                => wc_clean( $_POST['_wc_appointment_interval'] ),
                'padding_duration_unit'   => wc_clean( $_POST['_wc_appointment_padding_duration_unit'] ),
                'padding_duration'        => wc_clean( $_POST['_wc_appointment_padding_duration'] ),
                'min_date_unit'           => wc_clean( $_POST['_wc_appointment_min_date_unit'] ),
                'min_date'                => wc_clean( $_POST['_wc_appointment_min_date'] ),
                'max_date_unit'           => wc_clean( $_POST['_wc_appointment_max_date_unit'] ),
                'max_date'                => wc_clean( $_POST['_wc_appointment_max_date'] ),
                'user_can_cancel'         => isset( $_POST['_wc_appointment_user_can_cancel'] ),
                'cancel_limit_unit'       => wc_clean( $_POST['_wc_appointment_cancel_limit_unit'] ),
                'cancel_limit'            => wc_clean( $_POST['_wc_appointment_cancel_limit'] ),
                'requires_confirmation'   => isset( $_POST['_wc_appointment_requires_confirmation'] ),
                'customer_timezones'      => isset( $_POST['_wc_appointment_customer_timezones'] ),
                'availability_span'       => wc_clean( $_POST['_wc_appointment_availability_span'] ),
                'availability_autoselect' => isset( $_POST['_wc_appointment_availability_autoselect'] ),
                'has_restricted_days'     => isset( $_POST['_wc_appointment_has_restricted_days'] ),
                'restricted_days'         => isset( $_POST['_wc_appointment_restricted_days'] ) ? wc_clean( $_POST['_wc_appointment_restricted_days'] ) : '',
                'appointments_version'    => WC_APPOINTMENTS_VERSION,
                'appointments_db_version' => WC_APPOINTMENTS_DB_VERSION,
            )
        );
    }

    public function save_appointment_meta( $post_id ) {
        if ( version_compare( WC_VERSION, '3.0', '>=' ) || 'appointment' !== sanitize_title( stripslashes( $_POST['product-type'] ) ) ) {
            return;
        }
        $product =  new WC_Product_Appointment( $post_id );
        $this->set_appointment_props( $product );
        $product->save();
    }

    private function save_product_availability( $product ) {
        // Delete.
        if ( ! empty( $_POST['wc_appointment_availability_deleted'] ) ) {
            $deleted_ids = array_filter( explode( ',', wc_clean( wp_unslash( $_POST['wc_appointment_availability_deleted'] ) ) ) );

            foreach ( $deleted_ids as $delete_id ) {
                $availability_object = get_wc_appointments_availability( $delete_id );
                $availability_object->delete();
            }
        }

        // Save.
        $types    = isset( $_POST['wc_appointment_availability_type'] ) ? wc_clean( wp_unslash( $_POST['wc_appointment_availability_type'] ) ) : array();
        $row_size = count( $types );

        for ( $i = 0; $i < $row_size; $i ++ ) {
            if ( isset( $_POST['wc_appointment_availability_id'][ $i ] ) ) {
                $current_id = intval( $_POST['wc_appointment_availability_id'][ $i ] );
            } else {
                $current_id = 0;
            }

            $availability = get_wc_appointments_availability( $current_id );
            $availability->set_ordering( $i );
            $availability->set_range_type( $types[ $i ] );
            $availability->set_kind( 'availability#product' );
            $availability->set_kind_id( $product->get_id() );

            if ( isset( $_POST['wc_appointment_availability_appointable'][ $i ] ) ) {
                $availability->set_appointable( wc_clean( wp_unslash( $_POST['wc_appointment_availability_appointable'][ $i ] ) ) );
            }

            if ( isset( $_POST['wc_appointment_availability_title'][ $i ] ) ) {
                $availability->set_title( sanitize_text_field( wp_unslash( $_POST['wc_appointment_availability_title'][ $i ] ) ) );
            }

            if ( isset( $_POST['wc_appointment_availability_qty'][ $i ] ) ) {
                $availability->set_qty( intval( $_POST['wc_appointment_availability_qty'][ $i ] ) );
            }

            if ( isset( $_POST['wc_appointment_availability_priority'][ $i ] ) ) {
                $availability->set_priority( intval( $_POST['wc_appointment_availability_priority'][ $i ] ) );
            }

            switch ( $availability->get_range_type() ) {
                case 'custom':
                    if ( isset( $_POST['wc_appointment_availability_from_date'][ $i ] ) && isset( $_POST['wc_appointment_availability_to_date'][ $i ] ) ) {
                        $availability->set_from_range( wc_clean( wp_unslash( $_POST['wc_appointment_availability_from_date'][ $i ] ) ) );
                        $availability->set_to_range( wc_clean( wp_unslash( $_POST['wc_appointment_availability_to_date'][ $i ] ) ) );
                    }
                    break;
                case 'months':
                    if ( isset( $_POST['wc_appointment_availability_from_month'][ $i ] ) && isset( $_POST['wc_appointment_availability_to_month'][ $i ] ) ) {
                        $availability->set_from_range( wc_clean( wp_unslash( $_POST['wc_appointment_availability_from_month'][ $i ] ) ) );
                        $availability->set_to_range( wc_clean( wp_unslash( $_POST['wc_appointment_availability_to_month'][ $i ] ) ) );
                    }
                    break;
                case 'weeks':
                    if ( isset( $_POST['wc_appointment_availability_from_week'][ $i ] ) && isset( $_POST['wc_appointment_availability_to_week'][ $i ] ) ) {
                        $availability->set_from_range( wc_clean( wp_unslash( $_POST['wc_appointment_availability_from_week'][ $i ] ) ) );
                        $availability->set_to_range( wc_clean( wp_unslash( $_POST['wc_appointment_availability_to_week'][ $i ] ) ) );
                    }
                    break;
                case 'days':
                    if ( isset( $_POST['wc_appointment_availability_from_day_of_week'][ $i ] ) && isset( $_POST['wc_appointment_availability_to_day_of_week'][ $i ] ) ) {
                        $availability->set_from_range( wc_clean( wp_unslash( $_POST['wc_appointment_availability_from_day_of_week'][ $i ] ) ) );
                        $availability->set_to_range( wc_clean( wp_unslash( $_POST['wc_appointment_availability_to_day_of_week'][ $i ] ) ) );
                    }
                    break;
                case 'rrule':
                    // Do nothing rrules are read only for now.
                    break;
                case 'time':
                case 'time:1':
                case 'time:2':
                case 'time:3':
                case 'time:4':
                case 'time:5':
                case 'time:6':
                case 'time:7':
                    if ( isset( $_POST['wc_appointment_availability_from_time'][ $i ] ) && isset( $_POST['wc_appointment_availability_to_time'][ $i ] ) ) {
                        $availability->set_from_range( wc_appointment_sanitize_time( wp_unslash( $_POST['wc_appointment_availability_from_time'][ $i ] ) ) );
                        $availability->set_to_range( wc_appointment_sanitize_time( wp_unslash( $_POST['wc_appointment_availability_to_time'][ $i ] ) ) );
                    }
                    break;
                case 'time:range':
                case 'custom:daterange':
                    if ( isset( $_POST['wc_appointment_availability_from_time'][ $i ] ) && isset( $_POST['wc_appointment_availability_to_time'][ $i ] ) ) {
                        $availability->set_from_range( wc_appointment_sanitize_time( wp_unslash( $_POST['wc_appointment_availability_from_time'][ $i ] ) ) );
                        $availability->set_to_range( wc_appointment_sanitize_time( wp_unslash( $_POST['wc_appointment_availability_to_time'][ $i ] ) ) );
                    }
                    if ( isset( $_POST['wc_appointment_availability_from_date'][ $i ] ) && isset( $_POST['wc_appointment_availability_to_date'][ $i ] ) ) {
                        $availability->set_from_date( wc_clean( wp_unslash( $_POST['wc_appointment_availability_from_date'][ $i ] ) ) );
                        $availability->set_to_date( wc_clean( wp_unslash( $_POST['wc_appointment_availability_to_date'][ $i ] ) ) );
                    }
                    break;
            }

            $availability->save();
        }
    }

    private function get_posted_pricing() {
        $pricing  = array();
        $row_size = isset( $_POST['wc_appointment_pricing_type'] ) ? sizeof( $_POST['wc_appointment_pricing_type'] ) : 0;
        for ( $i = 0; $i < $row_size; $i ++ ) {
            $pricing[ $i ]['type']          = wc_clean( $_POST['wc_appointment_pricing_type'][ $i ] );
            $pricing[ $i ]['cost']          = wc_clean( $_POST['wc_appointment_pricing_cost'][ $i ] );
            $pricing[ $i ]['modifier']      = wc_clean( $_POST['wc_appointment_pricing_cost_modifier'][ $i ] );
            $pricing[ $i ]['base_cost']     = wc_clean( $_POST['wc_appointment_pricing_base_cost'][ $i ] );
            $pricing[ $i ]['base_modifier'] = wc_clean( $_POST['wc_appointment_pricing_base_cost_modifier'][ $i ] );

            switch ( $pricing[ $i ]['type'] ) {
                case 'custom':
                    $pricing[ $i ]['from'] = wc_clean( $_POST['wc_appointment_pricing_from_date'][ $i ] );
                    $pricing[ $i ]['to']   = wc_clean( $_POST['wc_appointment_pricing_to_date'][ $i ] );
                    break;
                case 'months':
                    $pricing[ $i ]['from'] = wc_clean( $_POST['wc_appointment_pricing_from_month'][ $i ] );
                    $pricing[ $i ]['to']   = wc_clean( $_POST['wc_appointment_pricing_to_month'][ $i ] );
                    break;
                case 'weeks':
                    $pricing[ $i ]['from'] = wc_clean( $_POST['wc_appointment_pricing_from_week'][ $i ] );
                    $pricing[ $i ]['to']   = wc_clean( $_POST['wc_appointment_pricing_to_week'][ $i ] );
                    break;
                case 'days':
                    $pricing[ $i ]['from'] = wc_clean( $_POST['wc_appointment_pricing_from_day_of_week'][ $i ] );
                    $pricing[ $i ]['to']   = wc_clean( $_POST['wc_appointment_pricing_to_day_of_week'][ $i ] );
                    break;
                case 'time':
                case 'time:1':
                case 'time:2':
                case 'time:3':
                case 'time:4':
                case 'time:5':
                case 'time:6':
                case 'time:7':
                    $pricing[ $i ]['from'] = wc_appointment_sanitize_time( $_POST['wc_appointment_pricing_from_time'][ $i ] );
                    $pricing[ $i ]['to']   = wc_appointment_sanitize_time( $_POST['wc_appointment_pricing_to_time'][ $i ] );
                    break;
                case 'time:range':
                    $pricing[ $i ]['from'] = wc_appointment_sanitize_time( $_POST['wc_appointment_pricing_from_time'][ $i ] );
                    $pricing[ $i ]['to']   = wc_appointment_sanitize_time( $_POST['wc_appointment_pricing_to_time'][ $i ] );

                    $pricing[ $i ]['from_date'] = wc_clean( $_POST['wc_appointment_pricing_from_date'][ $i ] );
                    $pricing[ $i ]['to_date']   = wc_clean( $_POST['wc_appointment_pricing_to_date'][ $i ] );
                    break;
                default:
                    $pricing[ $i ]['from'] = wc_clean( $_POST['wc_appointment_pricing_from'][ $i ] );
                    $pricing[ $i ]['to']   = wc_clean( $_POST['wc_appointment_pricing_to'][ $i ] );
                    break;
            }
        }
        return $pricing;
    }

    public static function get_vendor_appointable_products( $post_status = 'any', $output = OBJECT ) {
        global $WCMp;
        $vendor_id = afm()->vendor_id;
        $appointment_products = array();
        if ( $vendor_id ) {
            $vendor = get_wcmp_vendor( $vendor_id );
            if ( $vendor ) {
                $args = apply_filters( 'get_vendor_appointment_products_args', array(
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
                            'terms'    => 'appointment',
                        )
                    )
                    ) );

                $vendor_products = $vendor->get_products( $args );

                foreach ( $vendor_products as $vendor_product ) {
                    $product_type = WC_Product_Factory::get_product_type( $vendor_product->ID );
                    if ( in_array( $product_type, apply_filters( 'get_vendor_appointment_product_types', array( 'appointment' ) ) ) ) {
                        $appointment_products[] = ( $output == OBJECT ) ? new WC_Product_Appointment( $vendor_product->ID ) : $vendor_product->ID;
                    }
                }
            }
        }
        return $appointment_products;
    }

    public static function get_vendor_appointment_array( $args = null ) {
        global $wpdb;
        $appointments_object = array();
        $products = self::get_vendor_appointable_products( 'any', ARRAY_N );
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
                'post_type'        => 'wc_appointment',
                'posts_per_page'   => -1,
                'orderby'          => 'menu_order',
                'order'            => 'asc',
                'suppress_filters' => true,
                'meta_query'       => array(
                    array(
                        'key'     => '_appointment_product_id',
                        'value'   => join( ', ', $products ),
                        'compare' => 'IN',
                    )
                )
            );
            $r = wp_parse_args( $args, $defaults );
            $vendor_appointments = get_posts( $r );
            foreach ( $vendor_appointments as $vendor_appointment ) {
                $appointments_object[] = get_post( $vendor_appointment->ID );
            }
        }
        return $appointments_object;
    }

  public function form_submited() {
    global $WCMp;
    $current_endpoint_key = $WCMp->endpoints->get_current_endpoint();
    $current_vendor_id = afm()->vendor_id;
    if ( $current_vendor_id && ! empty( $_POST ) ) {
        if( $current_endpoint_key == 'appointments' ) {
            if ( current_vendor_can( 'update_appointment_details' ) && ! empty( $_POST['appointment_id'] ) && isset( $_POST['appointment_details_nonce'] ) && wp_verify_nonce( $_POST['appointment_details_nonce'], 'appointment_details' ) ) {
                $appointment_id = absint( $_POST['appointment_id'] );
                $vendor_appointments = WCMp_AFM_Appointment_Integration::get_vendor_appointment_array();
                $vendor_appointments_id = wp_list_pluck( $vendor_appointments, 'ID' );
                if ( in_array( $appointment_id, $vendor_appointments_id ) ) {
                    $appointment = new WC_Appointment( $_POST['appointment_id'] );
                    $appointment->set_props( array(
                        'status' => wc_clean( $_POST['_appointment_status'] ),
                    ) );
                    $appointment->save();
                    wc_add_notice( __( 'Appointment status updated successfully', WCMp_AFM_TEXT_DOMAIN ), 'success' );
                } else {
                    wc_add_notice( __( 'Update failed! Invalid appointment, ', WCMp_AFM_TEXT_DOMAIN ), 'success' );
                }
            } else {
                wp_die( -1 );
            }
        }
    }
        
}

}
