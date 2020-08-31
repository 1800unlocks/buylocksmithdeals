<?php
/**
 * WCMp Advanced Frontend Manager
 *
 * Booking and Rental System (Woocommerce) Support
 *
 * @author WC Marketplace
 * @package WCMp_AFM/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Rental_Integration {

    protected $id = null;
    protected $tabs = array();
    //pair of field name and meta values
    public $form_fields = array();

    public function __construct() {
        global $WCMp;

        $this->tabs = $this->set_additional_tabs();

        // Rental Product Additional Tabs
        add_filter( 'wcmp_product_data_tabs', array( $this, 'redq_rental_additional_tabs' ) );
        add_action( 'wcmp_product_tabs_content', array( $this, 'redq_rental_additional_tabs_content' ) );
        
        
        add_filter( 'rental_pricing_types' , array( $this, 'remove_unnecessary_pricing_types' ) );
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
            'availability' => array(
                'label'    => __( 'Availability', 'redq-rental' ),
                'target'   => 'availability_product_data',
                'class'    => array( 'show_if_redq_rental' ),
                'priority' => '9',
                'fields'   => array(
                    'redq_rental_availability' => array(),
                ),
            ),
            'price_calculation' => array(
                'label'    => __( 'Price Calculation', 'redq-rental' ),
                'target'   => 'price_calculation_product_data',
                'class'    => array( 'show_if_redq_rental' ),
                'priority' => '31',
                'fields'   => array(
                    'pricing_type'         => '',
                    'hourly_price'         => '',
                    'general_price'        => '',
                ),
            ),
        );
    }

    public function set_form_fields() {
        $temp = array();
        foreach ( $this->tabs as $key => $tab ) {
            $sub_arr = array();
            foreach ( $tab['fields'] as $field => $default ) {
                $sub_arr[$field] = get_post_meta( $this->id, $field, true );
                if ( empty( $sub_arr[$field] ) && $default !== '' ) {
                    $sub_arr[$field] = $default;
                }
            }
            $temp[$key] = $sub_arr;
        }
        return (array) $temp;
    }

    public function add_localize_params( $params ) {
        $new_params = array(
            'add_availability_nonce'   => wp_create_nonce( 'add-own-availability' ),
            'remove_availability'      => esc_js( __( 'Remove this availability range?', 'wcmp-afm' ) ),
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
    
    public function remove_unnecessary_pricing_types( $pricing_types ) {
        unset( $pricing_types );
        return array(
            'general_pricing' => __( 'General Pricing', 'redq-rental' ),
        );
    }
    
    public function save_redq_rental_meta( $product_id, $data ) {
        if ( isset( $data['product-type'] ) && $data['product-type'] == 'redq_rental' ) {

            // save all data
            $redq_booking_data = array();

            foreach ( $this->tabs as $key => $tab ) {
                if ( isset( $tab['fields'] ) && is_array( $tab['fields'] ) ) {
                    foreach ( $tab['fields'] as $meta_name => $dfvalue ) {
                        if ( isset( $data[$meta_name] ) ) {
                            $redq_booking_data[$meta_name] = $data[$meta_name];
                            update_post_meta( $product_id, $meta_name, $data[$meta_name] );
                        } elseif ( $dfvalue !== '' ) {
                            update_post_meta( $product_id, $meta_name, $dfvalue );
                        }
                    }
                }
            }
            update_post_meta( $product_id, 'redq_all_data', $redq_booking_data );
        }
    }

}