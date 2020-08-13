<?php

/**
 * WCMp Advanced Frontend Manager
 *
 * Toolset Support
 *
 * @author WC Marketplace
 * @package WCMp_AFM/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Toolset_Integration {

    protected $id = null;
    protected $product = null;
    protected $field_groups = null;
    protected $plugin = 'toolset';

    public function __construct() {
        if ( ! function_exists( 'wpcf_admin_post_get_post_groups_fields' ) ) {
            require_once( WPCF_EMBEDDED_ABSPATH . '/includes/fields-post.php' );
        }

        //load additional libs required for toolset type @screen ADD PRODUCT
        add_action( 'afm_add_product_load_script_lib', array( $this, 'load_required_script_lib' ), 1, 3 );

        add_action( 'wcmp_afm_after_product_excerpt_metabox_panel', array( $this, 'toolset_fields_panel' ), 11 );

        // Toolset Product Meta Data Save
        add_action( 'wcmp_process_product_object', array( $this, 'toolset_fields_save' ), 20 );
    }

    //this will be called from the main Integration class after WCMp_AFM_Add_Product_Endpoint class constructor executed
    public function set_props( $id ) {
        $this->id = $id;

        //after setting id get the WC product object
        $this->product = wc_get_product( $this->id );
        $product_post = get_post( $this->id );
        $this->field_groups = wpcf_admin_post_get_post_groups_fields( $product_post );
    }

    public function load_required_script_lib( $frontend_script_path, $lib_path, $suffix ) {
        global $WCMp;
        $WCMp->library->load_colorpicker_lib();
        $WCMp->library->load_frontend_upload_lib();
    }

    public function toolset_fields_panel() {
        if ( ! empty( $this->field_groups ) && ! empty( $this->id ) ) {
            foreach ( $this->field_groups as $field_group_index => $field_group ) {
                //If Access plugin activated
                if ( function_exists( 'wpcf_access_register_caps' ) ) {
                    //If user can't view own profile fields
                    if ( ! current_user_can( 'view_fields_in_edit_page_' . $field_group['slug'] ) ) {
                        continue;
                    }
                    //If user can modify current group in own profile
                    if ( ! current_user_can( 'modify_fields_in_edit_page_' . $field_group['slug'] ) ) {
                        continue;
                    }
                }
                if ( isset( $group['__show_meta_box'] ) && $group['__show_meta_box'] == false ) {
                    continue;
                }
                $field_group_load = Types_Field_Group_Post_Factory::load( $field_group['slug'] );
                if ( null === $field_group_load ) {
                    continue;
                }
                // WooCommerce Filter Views discard
                if ( $field_group['slug'] == 'woocommerce-views-filter-fields' ) {
                    continue;
                }
                if ( ! empty( $field_group['fields'] ) ) {
                    afm()->template->get_template( 'products/toolset/html-toolset-fields.php', array( 'id' => $this->id, 'self' => $this, 'product' => $this->product, 'field_group' => $field_group ) );
                }
            }
        }
    }

    public function toolset_fields_save( $product ) {
        $post_id = $product->get_id();
        if ( isset( $_POST['wpcf'] ) ) {
            foreach ( $_POST['wpcf'] as $toolset_types_filed_key => $toolset_types_filed_value ) {
                update_post_meta( $post_id, $toolset_types_filed_key, $toolset_types_filed_value );
            }
        }
    }

}
