<?php

/**
 * WCMp Advanced Frontend Manager
 *
 * WooCommerce Product Add-Ons Support
 *
 * @author WC Marketplace
 * @package WCMp_AFM/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Product_Addons_Integration {

    protected $id = null;
    protected $product_object = null;
    protected $product_addons = null;
    protected $exclude_global = null;
    protected $tabs = array();
    protected $plugin = 'product-addons';

    public function __construct() {
        $this->tabs = $this->set_additional_tabs();

        add_filter( 'wcmp_advance_product_script_params', array( $this, 'add_localize_params' ) );

        // Product Add-ons Product Additional Tabs
        add_filter( 'wcmp_product_data_tabs', array( $this, 'additional_product_tabs' ) );
        add_action( 'wcmp_product_tabs_content', array( $this, 'additional_tabs_content' ) );

        // Addon Product Meta Data Save
        add_action( 'wcmp_process_product_object', array( $this, 'process_addon_data' ), 20 );
    }

    //this will be called from the main Integration class after WCMp_AFM_Add_Product_Endpoint class constructor executed
    public function set_props( $id ) {
        $this->id = $id;
        $this->product_object = wc_get_product( $this->id );
        $this->product_addons = array_filter( (array) $this->product_object->get_meta( '_product_addons' ) );
        $this->exclude_global = $this->product_object->get_meta( '_product_addons_exclude_global' );
    }

    protected function set_additional_tabs() {
        global $WCMp;
        $addon_tabs = array();

        $addon_tabs['product_addons'] = array(
            'label'    => __( 'Add-ons', 'woocommerce-product-addons' ),
            'target'   => 'product_addons_data',
            'class'    => array(),
            'priority' => '79',
        );
        return $addon_tabs;
    }

    public function add_localize_params( $params ) {
        $new_params = array(
            'add_addon_nonce'            => wp_create_nonce( 'add-addon' ),
            'i18n_empty_name'            => esc_js( __( 'All addon fields require a name.', 'woocommerce-product-addons' ) ),
            'i18n_minmax_price'          => esc_js( __( 'Min / max price', 'woocommerce-product-addons' ) ),
            'i18n_minmax_multiplier'     => esc_js( __( 'Min / max multiplier', 'woocommerce-product-addons' ) ),
            'i18n_minmax_characters'     => esc_js( __( 'Min / max characters', 'woocommerce-product-addons' ) ),
            'i18n_minmax'                => esc_js( __( 'Min / max', 'woocommerce-product-addons' ) ),
            'i18n_remove_addon'          => esc_js( __( 'Are you sure you want remove this add-on?', 'woocommerce-product-addons' ) ),
            'i18n_remove_addon_option'   => esc_js( __( 'Are you sure you want delete this option?', 'woocommerce-product-addons' ) ),
            'i18n_restrict_addon_remove' => esc_js( __( "Last option can not be deleted", WCMp_AFM_TEXT_DOMAIN ) ),
        );
        return array_merge( $params, $new_params );
    }

    public function additional_product_tabs( $product_tabs ) {
        return array_merge( $product_tabs, $this->tabs );
    }

    public function additional_tabs_content() {
        foreach ( $this->tabs as $key => $tab ) {
            afm()->template->get_template( 'products/product-addons/html-product-data-' . str_replace( '_', '-', $key ) . '.php', array( 'id' => $this->id, 'tab' => $tab['target'], 'self' => $this, 'product_object' => $this->product_object, 'product_addons' => $this->product_addons, 'exclude_global' => $this->exclude_global ) );
        }
        return;
    }

    public static function get_product_addon_type() {
        return apply_filters( 'wcmp_afm_product_addon_types', array(
            'custom_price'     => __( 'Additional custom price input', 'woocommerce-product-addons' ),
            'input_multiplier' => __( 'Additional price multiplier', 'woocommerce-product-addons' ),
            'checkbox'         => __( 'Checkboxes', 'woocommerce-product-addons' ),
            'custom_textarea'  => __( 'Custom input (textarea)', 'woocommerce-product-addons' ),
            'optgroup_section' => array(
                'label'   => __( 'Custom input (text)', 'woocommerce-product-addons' ),
                'options' => array(
                    'custom'                   => __( 'Any text', 'woocommerce-product-addons' ),
                    'custom_email'             => __( 'Email address', 'woocommerce-product-addons' ),
                    'custom_letters_only'      => __( 'Only letters', 'woocommerce-product-addons' ),
                    'custom_letters_or_digits' => __( 'Only letters and numbers', 'woocommerce-product-addons' ),
                    'custom_digits_only'       => __( 'Only numbers', 'woocommerce-product-addons' ),
                ),
            ),
            'file_upload'      => __( 'File upload', 'woocommerce-product-addons' ),
            'radiobutton'      => __( 'Radio buttons', 'woocommerce-product-addons' ),
            'select'           => __( 'Select box', 'woocommerce-product-addons' ),
            ) );
    }

    /**
     * Process meta box.
     *
     * @param int $post_id Post ID.
     */
    public function process_addon_data( $post_id ) {
        // Save addons as serialised array.
        $product_addons = $this->get_posted_product_addons();
        $product_addons_exclude_global = isset( $_POST['_product_addons_exclude_global'] ) ? 1 : 0;

        $product = wc_get_product( $post_id );
        $product->update_meta_data( '_product_addons', $product_addons );
        $product->update_meta_data( '_product_addons_exclude_global', $product_addons_exclude_global );
        $product->save();
    }

    /**
     * Put posted addon data into an array.
     *
     * @return array
     */
    protected function get_posted_product_addons() {
        $product_addons = array();

        if ( isset( $_POST['product_addon_name'] ) ) {
            $addon_name = $_POST['product_addon_name'];
            $addon_description = $_POST['product_addon_description'];
            $addon_type = $_POST['product_addon_type'];
            $addon_position = $_POST['product_addon_position'];
            $addon_required = isset( $_POST['product_addon_required'] ) ? $_POST['product_addon_required'] : array();

            $addon_option_label = $_POST['product_addon_option_label'];
            $addon_option_price = $_POST['product_addon_option_price'];

            $addon_option_min = $_POST['product_addon_option_min'];
            $addon_option_max = $_POST['product_addon_option_max'];

            for ( $i = 0; $i < sizeof( $addon_name ); $i ++ ) {

                if ( ! isset( $addon_name[$i] ) || ( '' == $addon_name[$i] ) ) {
                    continue;
                }

                $addon_options = array();
                $option_label = $addon_option_label[$i];
                $option_price = $addon_option_price[$i];
                $option_min = $addon_option_min[$i];
                $option_max = $addon_option_max[$i];

                for ( $ii = 0; $ii < sizeof( $option_label ); $ii ++ ) {
                    $label = sanitize_text_field( stripslashes( $option_label[$ii] ) );
                    $price = wc_format_decimal( sanitize_text_field( stripslashes( $option_price[$ii] ) ) );
                    $min = sanitize_text_field( stripslashes( $option_min[$ii] ) );
                    $max = sanitize_text_field( stripslashes( $option_max[$ii] ) );

                    $addon_options[] = array(
                        'label' => $label,
                        'price' => $price,
                        'min'   => $min,
                        'max'   => $max
                    );
                }

                if ( sizeof( $addon_options ) == 0 ) {
                    continue; // Needs options.
                }

                $data = array();
                $data['name'] = sanitize_text_field( stripslashes( $addon_name[$i] ) );
                $data['description'] = wp_kses_post( stripslashes( $addon_description[$i] ) );
                $data['type'] = sanitize_text_field( stripslashes( $addon_type[$i] ) );
                $data['position'] = absint( $addon_position[$i] );
                $data['options'] = $addon_options;
                $data['required'] = isset( $addon_required[$i] ) ? 1 : 0;

                // Add to array.
                $product_addons[] = apply_filters( 'wcmp_afm_product_addons_save_data', $data, $i );
            }
        }

        if ( ! empty( $_POST['import_product_addon'] ) ) {
            $import_addons = maybe_unserialize( maybe_unserialize( stripslashes( trim( $_POST['import_product_addon'] ) ) ) );

            if ( is_array( $import_addons ) && sizeof( $import_addons ) > 0 ) {
                $valid = true;

                foreach ( $import_addons as $addon ) {
                    if ( ! isset( $addon['name'] ) || ! $addon['name'] ) {
                        $valid = false;
                    }
                    if ( ! isset( $addon['description'] ) ) {
                        $valid = false;
                    }
                    if ( ! isset( $addon['type'] ) ) {
                        $valid = false;
                    }
                    if ( ! isset( $addon['position'] ) ) {
                        $valid = false;
                    }
                    if ( ! isset( $addon['options'] ) ) {
                        $valid = false;
                    }
                    if ( ! isset( $addon['required'] ) ) {
                        $valid = false;
                    }
                }

                if ( $valid ) {
                    $product_addons = array_merge( $product_addons, $import_addons );
                }
            }
        }

        uasort( $product_addons, array( $this, 'addons_cmp' ) );

        return $product_addons;
    }

    /**
     * Sort addons.
     *
     * @param  array $a First item to compare.
     * @param  array $b Second item to compare.
     * @return bool
     */
    protected function addons_cmp( $a, $b ) {
        if ( $a['position'] == $b['position'] ) {
            return 0;
        }

        return ( $a['position'] < $b['position'] ) ? -1 : 1;
    }

    /**
     * Generate a filterable default new addon option.
     *
     * @return array
     */
    public static function get_new_addon_option() {
        $new_addon_option = array(
            'label' => '',
            'price' => '',
            'min'   => '',
            'max'   => ''
        );

        return apply_filters( 'wcmp_afm_product_addons_new_addon_option', $new_addon_option );
    }

}
