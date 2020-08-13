<?php

/**
 * WCMp_AFM_Integrations setup
 *
 * @package  WCMp_AFM/classes
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Integrations {
    
    protected $product_id = '';
    /**
     * Third party integrations supported by AFM
     * 
     * @var ARRAY_A 
     */
    protected $supported_integrations;

    /**
     * Available integrations based on activated plugins that corresponds to AFM supported integrations
     * 
     * @var ARRAY_A 
     */
    protected $available_integrations;

    /**
     * Admin Allowed integrations corresponding to available integrations
     * 
     * @var ARRAY_A 
     */
    protected $allowed_integrations;

    /**
     * List of all the supported vendor caps
     * 
     * @var ARRAY_A 
     */
    protected $supported_vendor_caps = array();

    /**
     * List of all the allowed vendor caps that gets added to `dc_vendor` Role
     * 
     * @var ARRAY_A 
     */
    protected $allowed_vendor_caps = array();

    /**
     * List of all the allowed integration JavaScripts, that are going to load on add product page
     * 
     * @var ARRAY_A 
     */
    protected $required_scripts = array();

    /**
     * List of all the allowed integration classes
     * 
     * @var ARRAY_A 
     */
    protected $class_list = array();

    //public $endpoints = array();

    public function __construct() {
        $this->supported_integrations = afm()->dependencies->supported_integrations;
        $this->available_integrations = afm()->dependencies->available_integrations;
        $this->allowed_integrations = afm()->dependencies->allowed_integrations;
        $this->supported_vendor_caps = afm()->dependencies->supported_vendor_caps;
        $this->allowed_vendor_caps = afm()->dependencies->allowed_vendor_caps;

        $this->class_list = $this->load_required_classes();

        //enqueue required styles after add product main css enqueued
        add_action( 'after_add_product_style_enqueued', array( $this, 'enqueue_required_styles' ) );
        //register all the required scripts after add product main js registered
        add_action( 'afm_after_add_product_scripts_registered', array( $this, 'register_required_scripts' ), 10, 2 );
        
        add_action( 'wcmp_frontend_enqueue_scripts', array( $this, 'wcmp_frontend_enqueue_scripts' ) );

        //set properties of allowed integration classes after add product endpoint load
        add_action( 'after_wcmp_edit_product_endpoint_load', array( $this, 'set_class_properties' ), 10, 3 );
        //set properties of allowed integration classes after add coupon endpoint load
        add_action( 'wcmp_afm_after_add_coupon_endpoint_load', array( $this, 'set_class_properties' ), 10, 3 );

        
        //enqueue all the required scripts
        add_action( 'wcmp_edit_product_template_load', array( $this, 'enqueue_required_scripts' ), 11 );

        //adding third party product types to backend settings
        add_filter( 'wcmp_vendor_product_types', array( $this, 'settings_additional_product_types' ) );
        add_filter( 'wcmp_vendor_product_type_options', array( $this, 'settings_additional_product_type_options' ) );
        //save third party product types to backend settings
        add_filter( 'settings_capabilities_product_tab_new_input', array( $this, 'save_product_types_setting' ), 10, 2 );
        //adding third party product types to frontend
        add_filter( 'wcmp_product_type_selector', array( $this, 'afm_set_default_product_types' ), 10 );
        add_filter( 'wcmp_product_type_selector', array( $this, 'afm_set_product_types' ), 99 );
        //check support for virtual and downloadable
        add_filter( 'wcmp_product_type_options', array( $this, 'afm_set_product_type_options' ), 20 );

        add_filter( 'wcmp_get_available_product_types', array( $this, 'afm_set_addl_product_types' ) );
        //Allow percentage coupon creation
        add_filter( 'wcmp_multi_vendor_coupon_types', array( $this, 'allow_percentage_coupon' ) );
        
        add_action( 'after_wcmp_edit_product_endpoint_load', array( $this, 'after_wcmp_edit_product_endpoint_load' ), 20, 1 );
    }

    /**
     * get active class list on demand
     */
    public function get_classlist() {
        return $this->class_list;
    }

    public function is_active_class( $class ) {
        return array_key_exists( $class, $this->class_list );
    }

    public function enqueue_required_styles( $path ) {
        foreach ( $this->allowed_integrations as $plugin => $integrations ) {
            if ( ! empty( $this->supported_integrations[$plugin]['style'] ) && apply_filters( 'wcmp_afm_enqueue_' . $plugin . '_style', true, $this->supported_integrations[$plugin]['style'] ) ) {
                $handle = $this->supported_integrations[$plugin]['style'];
                wp_enqueue_style( 'afm-' . $handle . '-style', $path . $handle . '.css' );
            }
        }
    }

    /**
     * Register required JS for each integration classes where 'noscript' is not defined
     * @page- ADD PRODUCT PAGE
     * 
     * @param type $script_path
     * @param type $dependencies
     */
    public function register_required_scripts( $script_path, $dependencies ) {
        foreach ( $this->class_list as $key => $instance ) {
            if ( isset( $this->supported_integrations[$key]['noscript'] ) ) {
                continue;
            }
            $handle = 'afm-add-product-' . $key;
            $filename = 'product-' . $key;
            $this->required_scripts[] = $handle;
            wp_register_script( $handle, $script_path . $filename . '.js', $dependencies, WCMp_AFM_VERSION );
        }
    }

    public function enqueue_required_scripts() {
        foreach ( $this->required_scripts as $handle ) {
            wp_enqueue_script( $handle );
        }
    }
    
    public function wcmp_frontend_enqueue_scripts(){
        $frontend_script_path = WCMp_AFM_PLUGIN_URL . 'assets/frontend/js/';
        $frontend_script_path = str_replace( array( 'http:', 'https:' ), '', $frontend_script_path );
        if( is_vendor_dashboard() ){
            wp_register_script( 'wcmp-afm-add-product', $frontend_script_path . 'product.js', array( 'wcmp-advance-product' ), WCMp_AFM_VERSION );
            wp_enqueue_script( 'wcmp-afm-add-product' );
        }
    }

    //include necessary integration classes
    public function load_required_classes() {
        //load dependency plugins first
        //$classes = $this->load_dependent_classes();
        $classes = array();
        $allowed_integrations = $this->allowed_integrations;
        foreach ( $allowed_integrations as $plugin => $integrations ) {
            if ( ! empty( $this->supported_integrations[$plugin]['dependencies'] ) ) {
                $dependencies = $this->supported_integrations[$plugin]['dependencies'];
                foreach ( $dependencies as $dependency ) {
                    $dependency_class = WCMp_AFM_CLASS_PREFIX . ucwords( str_replace( "-", "_", $dependency ), "_" ) . '_Integration';
                    if ( ! class_exists( $dependency_class ) && isset( $this->available_integrations[$dependency] ) ) {
                        $this->load_class( $dependency );
                        $classes[$dependency] = new $dependency_class( );
                    }
                }
            }
            $class = WCMp_AFM_CLASS_PREFIX . ucwords( str_replace( "-", "_", $plugin ), "_" ) . '_Integration';
            if ( ! class_exists( $class ) ) {
                $this->load_class( $plugin );
                $classes[$plugin] = new $class( );
            }
        }
        return $classes;
    }

//    private function load_dependent_classes() {
//        $list = array_column( $this->supported_integrations, 'dependencies' );
//        $classes = array();
//        if ( ! empty( $list ) ) {
//            $dependency_list = array_unique( call_user_func_array( 'array_merge', $list ) );
//
//            foreach ( $dependency_list as $dependency ) {
//                $class = WCMp_AFM_CLASS_PREFIX . ucwords( str_replace( "-", "_", $dependency ), "_" ) . '_Integration';
//                if ( ! class_exists( $class ) && isset( $this->available_integrations[$dependency] ) && ! isset( $this->allowed_integrations[$dependency] ) ) {
//                    $this->load_class( $dependency );
//                    $classes[$dependency] = new $class( );
//                }
//            }
//        }
//        return $classes;
//    }

    public function set_class_properties( $id, $product, $post ) {
        foreach ( $this->class_list as $key => $instance ) {
            $instance->set_props( $id, $product, $post );
        }
    }

    public function load_class( $class_name = '' ) {
        if ( '' != $class_name && '' != WCMp_AFM_PLUGIN_TOKEN ) {
            require_once ('integrations/class-' . esc_attr( WCMp_AFM_PLUGIN_TOKEN ) . '-' . esc_attr( $class_name ) . '-integration.php');
        }
    }
    
    public function after_wcmp_edit_product_endpoint_load( $product_id ){ 
        $this->product_id = $product_id;
        add_action( 'after_wcmp_afm_product_tags_metabox_panel', array( $this, 'add_taxonomy_metaboxes' ) );
    }
    
    public function add_taxonomy_metaboxes() {
        ob_start();
        $product_taxonomies = get_object_taxonomies( 'product', 'objects' );
        if ( ! empty( $product_taxonomies ) ) {
            foreach ( $product_taxonomies as $product_taxonomy ) {
                if ( ! in_array( $product_taxonomy->name, apply_filters( 'afm_exclude_handled_taxonomies', array( 'product_cat', 'product_tag' ) ) ) ) {
                    if ( $product_taxonomy->public && $product_taxonomy->show_ui && $product_taxonomy->meta_box_cb ) {
                        afm()->template->get_template( 'products/metabox/html-taxonomy-metabox.php', array( 'product_id' => $this->product_id, 'product_taxonomy' => $product_taxonomy ) );
                    }
                }
            }
        }
    }

    //Add product type checkbox under Capabilities tab - backend
    public function settings_additional_product_types( $product_types ) {
        global $WCMp;
        foreach ( $this->available_integrations as $plugin => $integrations ) {
            if ( $integrations && is_array( $integrations ) ) {
                foreach ( $integrations as $p_type => $details ) {
                    $product_types[$p_type] = array(
                        'title'     => $details['label'],
                        'type'      => 'checkbox',
                        'id'        => $p_type,
                        'label_for' => $p_type,
                        'name'      => $p_type,
                        'value'     => 'Enable' ); // Checkbox
                }
            }
        }
        return $product_types;
    }

    public function settings_additional_product_type_options( $options ) {
        global $WCMp;
        foreach ( $this->available_integrations as $plugin => $integrations ) {
            if ( $integrations && is_array( $integrations ) ) {
                foreach ( $integrations as $p_type => $details ) {
                    if ( ! empty( $details['options'] ) ) {
                        foreach ( $details['options'] as $name => $label ) {
                            $options[$name] = array(
                                'title'     => $label,
                                'type'      => 'checkbox',
                                'id'        => $name,
                                'label_for' => $name,
                                'name'      => $name,
                                'value'     => 'Enable' ); // Checkbox
                        }
                    }
                }
            }
        }
        return $options;
    }

    public function save_product_types_setting( $new_input, $input ) {
        $vendor_role = get_role( 'dc_vendor' );
        $capabilities = array_keys( $vendor_role->capabilities );
        $is_changed = false;
        foreach ( $this->available_integrations as $plugin => $integrations ) {
            $p_type_enabled = false;
            if ( $integrations && is_array( $integrations ) ) {
                foreach ( $integrations as $p_type => $details ) {
                    if ( isset( $input[$p_type] ) ) {
                        $p_type_enabled = true;
                        $new_input[$p_type] = wc_clean( $input[$p_type] );
                    }
                    if ( ! empty( $details['options'] ) && is_array( $details['options'] ) ) {
                        foreach ( $details['options'] as $name => $label ) {
                            if ( isset( $input[$name] ) ) {
                                $new_input[$name] = wc_clean( $input[$name] );
                            }
                        }
                    }
                }
            }
            if ( ! empty( $this->allowed_vendor_caps[$plugin] ) ) {
                if ( $p_type_enabled ) {
                    $allowed_caps = apply_filters( "vendor_{$plugin}_allowed_caps", $this->allowed_vendor_caps[$plugin] );
                    foreach ( $allowed_caps as $cap ) {
                        if ( ! array_key_exists( $cap, $capabilities ) ) {
                            $is_changed = true;
                            $vendor_role->add_cap( $cap );
                        }
                    }
                } else {
                    foreach ( $this->allowed_vendor_caps[$plugin] as $cap ) {
                        if ( array_key_exists( $cap, $capabilities ) ) {
                            $is_changed = true;
                            $vendor_role->remove_cap( $cap );
                        }
                    }
                }
            }
        }
        if ( $is_changed ) {
            flush_rewrite_rules();
        }
        return $new_input;
    }

    public function afm_set_product_types( $product_types ) {
        global $WCMp;

        $allowed_types = array();
        foreach ( $product_types as $type => $val ) {
            if ( $WCMp->vendor_caps->vendor_can( $type ) ) {
                $allowed_types[$type] = $val;
            }
        }
        foreach ( $this->allowed_integrations as $plugin => $integrations ) {
            if ( $integrations && is_array( $integrations ) ) {
                foreach ( $integrations as $p_type => $details ) {
                    if ( $WCMp->vendor_caps->vendor_can( $p_type ) ) {
                        $allowed_types[$p_type] = $details['label'];
                    }
                }
            }
        }
        return $allowed_types;
    }

    public function afm_set_product_type_options( $option ) {
        global $WCMp;
        foreach ( $option as $key => $val ) {
            if ( ! $WCMp->vendor_caps->vendor_can( $key ) ) {
                unset( $option[$key] );
            }
        }
        return $option;
    }

    public function afm_set_addl_product_types( $available_product_types ) {
        global $WCMp;
        $allowed_types = array();
        foreach ( $this->allowed_integrations as $plugin => $integrations ) {
            if ( $integrations && is_array( $integrations ) ) {
                foreach ( $integrations as $p_type => $details ) {
                    if ( $WCMp->vendor_caps->vendor_can( $p_type ) ) {
                        $allowed_types[$p_type] = $details['label'];
                    }
                }
            }
        }
        if ( ! empty( $allowed_types ) ) {
            return array_merge( $available_product_types, $allowed_types );
        }
        return $available_product_types;
    }
    
    public function allow_percentage_coupon( $to_unset ) {
        if ( ( $key = array_search( 'percent', $to_unset ) ) !== false ) {
            unset( $to_unset[$key] );
        }
        return apply_filters( 'wcmp_afm_disallowed_coupon_types', $to_unset );
    }
    
    public function afm_set_default_product_types( $product_types ){
        $afm_types = apply_filters( 'afm_set_default_product_types', array(
            'grouped'  => __( 'Grouped product', 'woocommerce' ),
            'external' => __( 'External/Affiliate product', 'woocommerce' ),
            'variable' => __( 'Variable product', 'woocommerce' ),
        ));
        return array_merge( $product_types, $afm_types );
    }

}
