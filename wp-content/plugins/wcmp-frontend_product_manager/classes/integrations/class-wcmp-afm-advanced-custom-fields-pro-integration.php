<?php

/**
 * WCMp Advanced Frontend Manager
 *
 * Advanced Custom Fields
 *
 * @author WC Marketplace
 * @package WCMp_AFM/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Advanced_Custom_Fields_Pro_Integration {
	
	protected $id = null;
	protected $product = null;
	protected $field_groups = null;
    protected $plugin = 'advanced-custom-fields-pro';

    public function __construct() {
        /*************************  product and coupon template for acf    ***************************************/
    	add_action( 'wcmp_afm_after_product_excerpt_metabox_panel', array($this, 'Advance_custom_fields_panel_for_vendor' ) );
		
        add_action( 'wcmp_afm_add_coupon_form_end', array($this, 'Advance_custom_fields_panel_for_vendor' ) );
    	/****************************** Save Coupon and product field data *********************************************************************************/  
		add_action( 'wcmp_process_product_object', array($this, 'advance_custom_fields_save'), 20 );
        add_action( 'wcmp_afm_before_coupon_post_update', array($this, 'advance_custom_fields_save') );

    }

    public function set_props( $id ) {
        $this->id = $id;
        //after setting id get the WC product object
        $this->product = wc_get_product( $this->id );
        $this->post_type = get_post_type( $this->id );
        $this->field_groups = acf_get_field_groups(array(
        	'post_id' => $this->id, 
        	'post_type' => $this->post_type
        	));

    }
    /*************************  product  and coupon template for acf    ******************************************/
    public function Advance_custom_fields_panel_for_vendor(){
    	if ( ! empty( $this->field_groups ) && ! empty( $this->id ) ) {
    		foreach ( $this->field_groups as $field_group_index => $field_group ) {
    			if ( ! empty( acf_get_fields( $field_group['ID'] ) ) ) {
                    afm()->template->get_template( 'products/acf/html-acf-fields.php', array( 'id' => $this->id, 'self' => $this, 'product' => $this->product, 'field_group' => $field_group ) );
                }
    		}
    	}
    }

    /****************************** Save Coupon and product field data *********************************************************************************/  
    public function advance_custom_fields_save(){
        if( isset( $_POST['acf'] ) ){
            foreach ($_POST['acf'] as $key => $value) {
                $google_map_address = json_decode( wp_unslash($value), true );
                $get_field_key = get_field_object($key, $_POST['post_ID'] );
                update_post_meta( $_POST['post_ID'], $get_field_key['_name'] , $value );
                if( $google_map_address ){
                    update_post_meta( $_POST['post_ID'], $get_field_key['_name'] , $google_map_address);
                }
            }
        }
    }

}
