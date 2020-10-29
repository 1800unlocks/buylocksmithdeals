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
        // enqueue woocommerce product addons js
        add_action( 'afm_enqueue_dashboard_scripts', array( $this, 'wp_enqueue_scripts_product_addon' ), 999, 4);

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
        $this->product_addons = wc_get_product( $this->id ) ? array_filter( (array) $this->product_object->get_meta( '_product_addons' ) ) : '';
        $this->exclude_global = wc_get_product( $this->id ) ? $this->product_object->get_meta( '_product_addons_exclude_global' ) : '';
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
            'i18n_restrict_addon_remove' => esc_js( __( "Last option can not be deleted", 'wcmp-afm' ) ),
        );
        return array_merge( $params, $new_params );
    }

    public function wp_enqueue_scripts_product_addon( $endpoint, $frontend_script_path, $lib_path, $suffix ){
        if( is_vendor_dashboard() ){
            switch ( $endpoint ) {
                case 'edit-product':
                wp_enqueue_style( 'woocommerce_product_addons_css', WC_PRODUCT_ADDONS_PLUGIN_URL . '/assets/css/admin.css', array(), WC_PRODUCT_ADDONS_VERSION );

                $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

                wp_register_script( 'woocommerce_product_addons', WC_PRODUCT_ADDONS_PLUGIN_URL.'/assets/js/admin' . $suffix . '.js', array( 'jquery' ), WC_PRODUCT_ADDONS_VERSION, true );

                $params = array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => array(
                        'get_addon_options' => wp_create_nonce( 'wc-pao-get-addon-options' ),
                        'get_addon_field'   => wp_create_nonce( 'wc-pao-get-addon-field' ),
                        ),
                    'i18n'     => array(
                        'required_fields'       => __( 'All fields must have a title and/or option name. Please review the settings highlighted in red border.', 'woocommerce-product-addons' ),
                        'limit_price_range'         => __( 'Limit price range', 'woocommerce-product-addons' ),
                        'limit_quantity_range'      => __( 'Limit quantity range', 'woocommerce-product-addons' ),
                        'limit_character_length'    => __( 'Limit character length', 'woocommerce-product-addons' ),
                        'restrictions'              => __( 'Restrictions', 'woocommerce-product-addons' ),
                        'confirm_remove_addon'      => __( 'Are you sure you want remove this add-on field?', 'woocommerce-product-addons' ),
                        'confirm_remove_option'     => __( 'Are you sure you want delete this option?', 'woocommerce-product-addons' ),
                        'add_image_swatch'          => __( 'Add Image Swatch', 'woocommerce-product-addons' ),
                        'add_image'                 => __( 'Add Image', 'woocommerce-product-addons' ),
                        ),
                    );

                wp_localize_script( 'woocommerce_product_addons', 'wc_pao_params', apply_filters( 'wc_pao_params', $params ) );

                wp_enqueue_script( 'woocommerce_product_addons' );
                break;
            }
        }
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
            $addon_name               = $_POST['product_addon_name'];
            $addon_title_format       = $_POST['product_addon_title_format'];
            $addon_description_enable = isset( $_POST['product_addon_description_enable'] ) ? $_POST['product_addon_description_enable'] : array();
            $addon_description        = $_POST['product_addon_description'];
            $addon_type               = $_POST['product_addon_type'];
            $addon_display            = $_POST['product_addon_display'];
            $addon_position           = $_POST['product_addon_position'];
            $addon_required           = isset( $_POST['product_addon_required'] ) ? $_POST['product_addon_required'] : array();
            $addon_option_label       = $_POST['product_addon_option_label'];
            $addon_option_price       = $_POST['product_addon_option_price'];
            $addon_option_price_type  = $_POST['product_addon_option_price_type'];
            $addon_option_image       = $_POST['product_addon_option_image'];
            $addon_restrictions       = isset( $_POST['product_addon_restrictions'] ) ? $_POST['product_addon_restrictions'] : array();
            $addon_restrictions_type  = $_POST['product_addon_restrictions_type'];
            $addon_adjust_price       = isset( $_POST['product_addon_adjust_price'] ) ? $_POST['product_addon_adjust_price'] : array();
            $addon_price_type         = $_POST['product_addon_price_type'];
            $addon_price              = $_POST['product_addon_price'];
            $addon_min                = $_POST['product_addon_min'];
            $addon_max                = $_POST['product_addon_max'];

            for ( $i = 0; $i < count( $addon_name ); $i++ ) {
                if ( ! isset( $addon_name[ $i ] ) || ( '' == $addon_name[ $i ] ) ) {
                    continue;
                }

                $addon_options = array();

                if ( isset( $addon_option_label[ $i ] ) ) {
                    $option_label      = $addon_option_label[ $i ];
                    $option_price      = $addon_option_price[ $i ];
                    $option_price_type = $addon_option_price_type[ $i ];
                    $option_image      = $addon_option_image[ $i ];

                    for ( $ii = 0; $ii < count( $option_label ); $ii++ ) {
                        $label      = sanitize_text_field( stripslashes( $option_label[ $ii ] ) );
                        $price      = wc_format_decimal( sanitize_text_field( stripslashes( $option_price[ $ii ] ) ) );
                        $image      = sanitize_text_field( stripslashes( $option_image[ $ii ] ) );
                        $price_type = sanitize_text_field( stripslashes( $option_price_type[ $ii ] ) );

                        $addon_options[] = array(
                            'label'      => $label,
                            'price'      => $price,
                            'image'      => $image,
                            'price_type' => $price_type,
                        );
                    }
                }

                $data                       = array();
                $data['name']               = sanitize_text_field( stripslashes( $addon_name[ $i ] ) );
                $data['title_format']       = sanitize_text_field( stripslashes( $addon_title_format[ $i ] ) );
                $data['description_enable'] = isset( $addon_description_enable[ $i ] ) ? 1 : 0;
                $data['description']        = wp_kses_post( stripslashes( $addon_description[ $i ] ) );
                $data['type']               = sanitize_text_field( stripslashes( $addon_type[ $i ] ) );
                $data['display']            = sanitize_text_field( stripslashes( $addon_display[ $i ] ) );
                $data['position']           = absint( $addon_position[ $i ] );
                $data['required']           = isset( $addon_required[ $i ] ) ? 1 : 0;
                $data['restrictions']       = isset( $addon_restrictions[ $i ] ) ? 1 : 0;
                $data['restrictions_type']  = sanitize_text_field( stripslashes( $addon_restrictions_type[ $i ] ) );
                $data['adjust_price']       = isset( $addon_adjust_price[ $i ] ) ? 1 : 0;
                $data['price_type']         = sanitize_text_field( stripslashes( $addon_price_type[ $i ] ) );
                $data['price']              = wc_format_decimal( sanitize_text_field( stripslashes( $addon_price[ $i ] ) ) );
                $data['min']                = (float) sanitize_text_field( stripslashes( $addon_min[ $i ] ) );
                $data['max']                = (float) sanitize_text_field( stripslashes( $addon_max[ $i ] ) );

                if ( ! empty( $addon_options ) ) {
                    $data['options'] = $addon_options;
                }

                // Always use quantity based price type for custom price.
                if ( 'custom_price' === $data['type'] ) {
                    $data['price_type'] = 'quantity_based';
                }

                // Add to array.
                $product_addons[] = apply_filters( 'woocommerce_product_addons_save_data', $data, $i );
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

    public static function convert_type_name( $type = '' ) {
        switch ( $type ) {
            case 'checkboxes':
                $name = __( 'Checkbox', 'woocommerce-product-addons' );
                break;
            case 'custom_price':
                $name = __( 'Price', 'woocommerce-product-addons' );
                break;
            case 'input_multiplier':
                $name = __( 'Quantity', 'woocommerce-product-addons' );
                break;
            case 'custom_text':
                $name = __( 'Short Text', 'woocommerce-product-addons' );
                break;
            case 'custom_textarea':
                $name = __( 'Long Text', 'woocommerce-product-addons' );
                break;
            case 'file_upload':
                $name = __( 'File Upload', 'woocommerce-product-addons' );
                break;
            case 'select':
                $name = __( 'Dropdown', 'woocommerce-product-addons' );
                break;
            case 'multiple_choice':
            default:
                $name = __( 'Multiple Choice', 'woocommerce-product-addons' );
                break;
        }

        return $name;
    }

}
