<?php

/**
 * WCMp_AFM_Export_Product_Endpoint setup
 *
 * @package  WCMp_AFM/classes/endpoints
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Export_Product_Endpoint {

    public function __construct() {
    }

    public function output() {
    	include_once( WC_ABSPATH . 'includes/export/class-wc-product-csv-exporter.php' );
        $user_id = get_current_user_id();
        $vendor = get_wcmp_vendor($user_id);
        $user = new WP_User($user_id);
        if ($vendor && $user->has_cap('edit_products')) {

            //$WCMP_Product_Import_Export_Bundle->template->get_template('product-export.php');
            echo '<div class="col-md-12">';
            include_once( WC_ABSPATH . 'includes/export/class-wc-product-csv-exporter.php' );
            include_once( WC_ABSPATH . 'includes/admin/views/html-admin-page-product-export.php' );
            echo '</div>';
        }
    }
}
