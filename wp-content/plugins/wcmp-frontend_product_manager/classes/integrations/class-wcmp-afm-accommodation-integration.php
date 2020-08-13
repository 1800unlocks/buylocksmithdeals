<?php

/**
 * WCMp Advanced Frontend Manager
 *
 * WooCommerce Accommodation Booking Support
 *
 * @author WC Marketplace
 * @package WCMp_AFM/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Accommodation_Integration {

    protected $id = null;
    protected $tabs = array();
    protected $bookable_product = null;
    protected $restricted_meta = array();
    protected $restricted_days = array();
    protected $plugin = 'accommodation';

    //protected $accommodation_endpoints = array();

    public function __construct() {
        $this->tabs = $this->set_additional_tabs();

        add_action( 'wcmp_product_type_options', array( $this, 'accommodation_additional_product_type_options' ) );

        // Accommodation Product Additional Tabs
        add_filter( 'wcmp_product_data_tabs', array( $this, 'accommodation_booking_additional_tabs' ) );
        add_action( 'wcmp_product_tabs_content', array( $this, 'accommodation_booking_additional_tabs_content' ) );

        add_filter( 'wcmp_afm_product_data_tabs_filter', array( $this, 'accommodation_booking_requisite_tabs' ), 10, 3 );

        add_action( 'wcmp_afm_after_general_product_data', array( $this, 'accommodation_booking_general_product_tab_content' ) );
        // Accommodation Booking Product Meta Data Save
        // below WC version 3.0
        add_action( 'wcmp_process_product_meta_accommodation-booking', array( $this, 'save_accommodation_booking_meta' ), 10, 2 );

        add_filter( 'get_vendor_booking_products_args', array( $this, 'add_accommodation_to_booking_products_args' ) );
        add_filter( 'get_vendor_booking_product_types', array( $this, 'add_accommodation_to_booking_product_type' ) );
    }

    //this will be called from the main Integration class after WCMp_AFM_Add_Product_Endpoint class constructor executed
    public function set_props( $id ) {
        $this->id = $id;

        //after setting id get the bookable product and restricted days
        $this->bookable_product = new WC_Product_Booking( $this->id );

        /**
         * Day restrictions added to Bookings 1.10.7
         * @todo  Remove version compare ~Aug 2018
         */
        if ( version_compare( WC_BOOKINGS_VERSION, '1.10.7', '>=' ) ) {

            $this->restricted_meta = $this->bookable_product->get_restricted_days();
            for ( $i = 0; $i < 7; $i ++ ) {
                if ( $this->restricted_meta && in_array( $i, $this->restricted_meta ) ) {
                    $this->restricted_days[$i] = $i;
                } else {
                    $this->restricted_days[$i] = false;
                }
            }
        }
    }

    protected function set_additional_tabs() {
        global $WCMp;
        $accommodation_tabs = array();

        $accommodation_tabs['accommodation_bookings_availability'] = array(
            'p_type'   => 'accommodation-booking',
            'label'    => __( 'Availability', 'woocommerce-accommodation-bookings' ),
            'target'   => 'accommodation_availability_product_data',
            'class'    => array( 'show_if_accommodation-booking' ),
            'priority' => '73',
        );
        $accommodation_tabs['accommodation_bookings_pricing'] = array(
            'p_type'   => 'accommodation-booking',
            'label'    => __( 'Rates', 'woocommerce-accommodation-bookings' ),
            'target'   => 'accommodation_bookings_pricing_product_data',
            'class'    => array( 'show_if_accommodation-booking' ),
            'priority' => '74',
        );
        return $accommodation_tabs;
    }

    public function accommodation_additional_product_type_options( $options ) {
        global $WCMp;

        $options['virtual']['wrapper_class'] .= ' hide_if_accommodation-booking';
        if ( $WCMp->vendor_caps->vendor_can( 'wc_booking_has_persons' ) && isset( $options['wc_booking_has_persons']['wrapper_class'] ) ) {
            $options['wc_booking_has_persons']['wrapper_class'] .= ' show_if_accommodation-booking';
        }
        if ( $WCMp->vendor_caps->vendor_can( 'wc_booking_has_resources' ) && isset( $options['wc_booking_has_resources']['wrapper_class'] ) ) {
            $options['wc_booking_has_resources']['wrapper_class'] .= ' show_if_accommodation-booking';
        }
        return $options;
    }

    public function accommodation_booking_additional_tabs( $product_tabs ) {
        if ( isset( $product_tabs['shipping']['class'] ) ) {
            $product_tabs['shipping']['class'][] = 'hide_if_accommodation-booking';
        }
        return array_merge( $product_tabs, $this->tabs );
    }

    public function accommodation_booking_additional_tabs_content() {
        foreach ( $this->tabs as $key => $tab ) {
            afm()->template->get_template( 'products/accommodation/html-product-data-' . str_replace( '_', '-', $key ) . '.php', array( 'id' => $this->id, 'tab' => $tab['target'], 'self' => $this, 'bookable_product' => $this->bookable_product, 'restricted_days' => $this->restricted_days ) );
        }
        return;
    }

    public function accommodation_booking_requisite_tabs( $status, $key, $tab ) {
        if ( $key === 'booking_persons' || $key === 'booking_resources' ) {
            return true;
        }
        return $status;
    }

    public function accommodation_booking_general_product_tab_content() {
        if ( wcmp_is_allowed_product_type( 'accommodation-booking' ) ) {
            afm()->template->get_template( 'products/accommodation/html-product-data-general.php', array( 'id' => $this->id, 'tab' => 'general', 'self' => $this, 'bookable_product' => $this->bookable_product, 'restricted_days' => $this->restricted_days ) );
        }
    }

    /**
     * Get posted availability fields and format.
     *
     * @return array
     */
    private function get_posted_availability() {
        $availability = array();
        $row_size = isset( $_POST['wc_accommodation_booking_availability_type'] ) ? sizeof( $_POST['wc_accommodation_booking_availability_type'] ) : 0;
        for ( $i = 0; $i < $row_size; $i ++ ) {
            $availability[$i]['type'] = wc_clean( $_POST['wc_accommodation_booking_availability_type'][$i] );
            $availability[$i]['bookable'] = wc_clean( $_POST['wc_accommodation_booking_availability_bookable'][$i] );
            $availability[$i]['priority'] = intval( $_POST['wc_accommodation_booking_availability_priority'][$i] );

            switch ( $availability[$i]['type'] ) {
                case 'custom':
                    $availability[$i]['from'] = wc_clean( $_POST['wc_accommodation_booking_availability_from_date'][$i] );
                    $availability[$i]['to'] = wc_clean( $_POST['wc_accommodation_booking_availability_to_date'][$i] );
                    break;
                case 'months':
                    $availability[$i]['from'] = wc_clean( $_POST['wc_accommodation_booking_availability_from_month'][$i] );
                    $availability[$i]['to'] = wc_clean( $_POST['wc_accommodation_booking_availability_to_month'][$i] );
                    break;
                case 'weeks':
                    $availability[$i]['from'] = wc_clean( $_POST['wc_accommodation_booking_availability_from_week'][$i] );
                    $availability[$i]['to'] = wc_clean( $_POST['wc_accommodation_booking_availability_to_week'][$i] );
                    break;
                case 'days':
                    $availability[$i]['from'] = wc_clean( $_POST['wc_accommodation_booking_availability_from_day_of_week'][$i] );
                    $availability[$i]['to'] = wc_clean( $_POST['wc_accommodation_booking_availability_to_day_of_week'][$i] );
                    break;
            }
        }
        return $availability;
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
     * Get posted pricing fields and format.
     *
     * @return array
     */
    private function get_posted_pricing() {
        $pricing = array();
        $row_size = isset( $_POST['wc_accommodation_booking_pricing_type'] ) ? sizeof( $_POST['wc_accommodation_booking_pricing_type'] ) : 0;
        for ( $i = 0; $i < $row_size; $i ++ ) {
            $pricing[$i]['type'] = wc_clean( $_POST['wc_accommodation_booking_pricing_type'][$i] );
            $pricing[$i]['cost'] = 0;
            $pricing[$i]['modifier'] = 'plus';
            $pricing[$i]['base_cost'] = 0;
            $pricing[$i]['base_modifier'] = 'plus';
            $pricing[$i]['override_block'] = wc_clean( $_POST['wc_accommodation_booking_pricing_block_cost'][$i] );

            switch ( $pricing[$i]['type'] ) {
                case 'custom':
                    $pricing[$i]['from'] = wc_clean( $_POST['wc_accommodation_booking_pricing_from_date'][$i] );
                    $pricing[$i]['to'] = wc_clean( $_POST['wc_accommodation_booking_pricing_to_date'][$i] );
                    break;
                case 'months':
                    $pricing[$i]['from'] = wc_clean( $_POST['wc_accommodation_booking_pricing_from_month'][$i] );
                    $pricing[$i]['to'] = wc_clean( $_POST['wc_accommodation_booking_pricing_to_month'][$i] );
                    break;
                case 'weeks':
                    $pricing[$i]['from'] = wc_clean( $_POST['wc_accommodation_booking_pricing_from_week'][$i] );
                    $pricing[$i]['to'] = wc_clean( $_POST['wc_accommodation_booking_pricing_to_week'][$i] );
                    break;
                case 'days':
                    $pricing[$i]['from'] = wc_clean( $_POST['wc_accommodation_booking_pricing_from_day_of_week'][$i] );
                    $pricing[$i]['to'] = wc_clean( $_POST['wc_accommodation_booking_pricing_to_day_of_week'][$i] );
                    break;
            }
        }
        return $pricing;
    }

    /**
     * Set data in 3.0.x
     *
     * @version  1.10.7
     * @param    WC_Product $product
     */
    public function set_accommodation_booking_props( $product ) {
        // Only set props if the product is a bookable product.
        if ( ! is_a( $product, 'WC_Product_Accommodation_Booking' ) ) {
            return;
        }

        $resources = $this->get_posted_resources( $product );
        $product->set_props( array(
            'availability'               => $this->get_posted_availability(),
            'has_persons'                => isset( $_POST['_wc_booking_has_persons'] ),
            'has_person_qty_multiplier'  => isset( $_POST['_wc_booking_person_qty_multiplier'] ),
            'has_person_cost_multiplier' => isset( $_POST['_wc_booking_person_cost_multiplier'] ),
            'min_persons'                => isset( $_POST['_wc_booking_min_persons_group'] ) ? wc_clean( $_POST['_wc_booking_min_persons_group'] ) : null,
            'max_persons'                => isset( $_POST['_wc_booking_max_persons_group'] ) ? wc_clean( $_POST['_wc_booking_max_persons_group'] ) : null,
            'has_person_types'           => isset( $_POST['_wc_booking_has_person_types'] ),
            'has_resources'              => isset( $_POST['_wc_booking_has_resources'] ),
            'resources_assignment'       => isset( $_POST['_wc_booking_resources_assignment'] ) ? wc_clean( $_POST['_wc_booking_resources_assignment'] ) : null,
            'resource_label'             => isset( $_POST['_wc_booking_resource_label'] ) ? wc_clean( $_POST['_wc_booking_resource_label'] ) : null,
            'calendar_display_mode'      => isset( $_POST['_wc_accommodation_booking_calendar_display_mode'] ) ? wc_clean( $_POST['_wc_accommodation_booking_calendar_display_mode'] ) : null,
            'requires_confirmation'      => isset( $_POST['_wc_accommodation_booking_requires_confirmation'] ),
            'user_can_cancel'            => isset( $_POST['_wc_accommodation_booking_user_can_cancel'] ),
            'cancel_limit'               => isset( $_POST['_wc_accommodation_booking_cancel_limit'] ) ? wc_clean( $_POST['_wc_accommodation_booking_cancel_limit'] ) : null,
            'cancel_limit_unit'          => isset( $_POST['_wc_accommodation_booking_cancel_limit_unit'] ) ? wc_clean( $_POST['_wc_accommodation_booking_cancel_limit_unit'] ) : null,
            'max_date_value'             => isset( $_POST['_wc_accommodation_booking_max_date'] ) ? wc_clean( $_POST['_wc_accommodation_booking_max_date'] ) : null,
            'max_date_unit'              => isset( $_POST['_wc_accommodation_booking_max_date_unit'] ) ? wc_clean( $_POST['_wc_accommodation_booking_max_date_unit'] ) : null,
            'min_date_value'             => isset( $_POST['_wc_accommodation_booking_min_date'] ) ? wc_clean( $_POST['_wc_accommodation_booking_min_date'] ) : null,
            'min_date_unit'              => isset( $_POST['_wc_accommodation_booking_min_date_unit'] ) ? wc_clean( $_POST['_wc_accommodation_booking_min_date_unit'] ) : null,
            'qty'                        => isset( $_POST['_wc_accommodation_booking_qty'] ) ? wc_clean( $_POST['_wc_accommodation_booking_qty'] ) : null,
            'block_cost'                 => isset( $_POST['_wc_accommodation_booking_base_cost'] ) ? wc_clean( $_POST['_wc_accommodation_booking_base_cost'] ) : null, // value float
            'display_cost'               => isset( $_POST['_wc_accommodation_booking_display_cost'] ) ? wc_clean( $_POST['_wc_accommodation_booking_display_cost'] ) : null,
            'min_duration'               => isset( $_POST['_wc_accommodation_booking_min_duration'] ) ? wc_clean( $_POST['_wc_accommodation_booking_min_duration'] ) : null,
            'max_duration'               => isset( $_POST['_wc_accommodation_booking_max_duration'] ) ? wc_clean( $_POST['_wc_accommodation_booking_max_duration'] ) : null,
            'has_restricted_days'        => isset( $_POST['_wc_accommodation_booking_has_restricted_days'] ),
            'restricted_days'            => isset( $_POST['_wc_accommodation_booking_restricted_days'] ) ? wc_clean( $_POST['_wc_accommodation_booking_restricted_days'] ) : '',
            'resource_base_costs'        => wp_list_pluck( $resources, 'base_cost' ),
            'resource_block_costs'       => wp_list_pluck( $resources, 'block_cost' ),
            'person_types'               => $this->get_posted_person_types( $product ),
            'pricing'                    => $this->get_posted_pricing(),
            'cost'                       => '',
            'regular_price'              => '',
            '_sale_price'                => '',
            '_manage_stock'              => 'no',
        ) );
        update_post_meta( $product->get_id(), '_wc_booking_base_cost', wc_clean( $_POST['_wc_accommodation_booking_base_cost'] ) );
    }

    public function save_accommodation_booking_meta( $product_id, $data ) {
        global $wpdb;
        if ( 'accommodation-booking' !== sanitize_title( stripslashes( $data['product-type'] ) ) ) {
            return;
        }
        $product = new WC_Product_Accommodation_Booking( $product_id );
        $this->set_accommodation_booking_props( $product );
        $product->save();
        update_post_meta( $product_id, '_price', $product->get_base_cost() );
    }

    public function add_accommodation_to_booking_products_args( $args ) {
        foreach ( $args['tax_query'] as $index => $filter ) {
            if ( 'product_type' !== $filter['taxonomy'] ) {
                continue;
            }

            $terms = isset( $args['tax_query'][$index]['terms'] ) ? $args['tax_query'][$index]['terms'] : array( 'booking' );
            if ( ! is_array( $terms ) ) {
                $terms = array( $terms );
            }
            $terms[] = 'accommodation-booking';

            $args['tax_query'][$index]['terms'] = $terms;
        }

        return $args;
    }

    public function add_accommodation_to_booking_product_type( $product_type ) {
        return array_merge( $product_type, array( 'accommodation-booking' ) );
    }

}
