<?php

/**
 * WCMp Frontend Manager plugin core
 *
 * Product Import Support
 *
 * @author 	WC Marketplace
 * @package  WCMp_AFM/classes
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_Admin_Importers extends WC_Admin_Importers {

    public function __construct() {
        parent::__construct();
        add_filter( 'woocommerce_product_importer_parsed_data', array( &$this, 'wcmp_check_import_permission' ), 10, 2 );
        add_filter( 'woocommerce_product_import_pre_insert_product_object', array( &$this, 'wcmp_import_pre_insert_product_object' ), 10, 2 );
        add_action( 'wp_head', array( &$this, 'wcmp_change_view_products_link' ) );
    }

    /**
	 * Return true if WooCommerce imports are allowed for current user, false otherwise.
	 *
	 * @return bool Whether current user can perform imports.
	 */
	protected function import_allowed() {
		global $WCMp;
		return current_user_can( 'edit_products' ) && $WCMp->vendor_caps->vendor_can( 'vendor_import_capability' );
	}
	
	function wcmp_check_import_permission($parsed_data, $data_obj) {
		global $WCMp;

		if(is_user_wcmp_vendor(get_current_user_id())){
			// Checks whether vendor can publish products or not
			if(!$WCMp->vendor_caps->vendor_can('is_published_product')) {
                $parsed_data['published'] = -1;
            }
            
            // Checks whether vendor can upload images or not
            if(!$WCMp->vendor_caps->vendor_can('is_upload_files')) {
                $parsed_data['raw_image_id'] = '';
                $parsed_data['raw_gallery_image_ids'] = array();
            }
        }
        return $parsed_data;
	}
	
	public function wcmp_import_pre_insert_product_object($object, $data) {
        global $WCMp;
        if(is_user_wcmp_vendor(get_current_user_id())){
            if(!$WCMp->vendor_caps->vendor_can('is_published_product')){
            	if('variation' === $object->get_type()) $object->set_status('publish');
            	else $object->set_status('draft');
            }
        }
        return $object;
    }
	
	function wcmp_change_view_products_link() {
		global $wp_query;
		
		$view_products_link = wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_products_endpoint', 'vendor', 'general', 'products'));
		$product_import_endpoint = get_wcmp_vendor_settings('wcmp_product_import_endpoint', 'vendor', 'general', 'product-import');
		
		if(isset( $wp_query->query_vars[$product_import_endpoint] )) {
			?>
			<script type="text/javascript">
				var view_products_link = '<?php echo $view_products_link;?>';
					jQuery( document ).ready(function($) {
					console.log( "document loaded" );
					$('.wc-actions .button-primary').attr('href', view_products_link);
				})
			</script>
			<?php
        }
	}
}
