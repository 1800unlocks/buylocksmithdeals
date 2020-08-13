<?php

/**
 * WCMp_AFM_Import_Product_Endpoint setup
 *
 * @package  WCMp_AFM/classes/endpoints
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Import_Product_Endpoint {

    public function __construct() {
    	
    }

    public function output() {
    	global $WCMp_Frontend_Product_Manager;
    	
    	$user_id = get_current_user_id();
        $vendor = get_wcmp_vendor($user_id);
        $user = new WP_User($user_id);
        if ($vendor && $user->has_cap('edit_products')) {
            include_once( WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php' );
            //loads import controller
			afm()->load_class('import-controller');
			$this->import_controller = new WCMP_Product_Import_Controller();
            $this->import_controller->dispatch();
        }
    }

}
