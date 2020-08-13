<?php

/**
 * WCMp Frontend Manager plugin core
 *
 * Product Export Support
 *
 * @author 	WC Marketplace
 * @package  WCMp_AFM/classes
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_Admin_Exporters extends WC_Admin_Exporters {

    public function __construct() {
        parent::__construct();
        add_action( 'wp', array( $this, 'download_export_file' ) );
        add_filter( "woocommerce_product_export_product_query_args", array( $this, "set_vendor_products_only" ) );
    }

    /**
	 * Return true if WooCommerce imports are allowed for current user, false otherwise.
	 *
	 * @return bool Whether current user can perform imports.
	 */
	 protected function export_allowed() {
	 	global $WCMp;
		return current_user_can( 'edit_products' ) && $WCMp->vendor_caps->vendor_can( 'vendor_export_capability' );
	}
	
	/**
	 * AJAX callback for doing the actual export to the CSV file.
	 */
	public function do_ajax_product_export() {
		check_ajax_referer( 'wc-product-export', 'security' );

		if ( ! $this->export_allowed() ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient privileges to export products.', 'woocommerce' ) ) );
		}

		include_once WC_ABSPATH . 'includes/export/class-wc-product-csv-exporter.php';

		$step     = isset( $_POST['step'] ) ? absint( $_POST['step'] ) : 1; // WPCS: input var ok, sanitization ok.
		$exporter = new WC_Product_CSV_Exporter();

		if ( ! empty( $_POST['columns'] ) ) { // WPCS: input var ok.
			$exporter->set_column_names( wp_unslash( $_POST['columns'] ) ); // WPCS: input var ok, sanitization ok.
		}

		if ( ! empty( $_POST['selected_columns'] ) ) { // WPCS: input var ok.
			$exporter->set_columns_to_export( wp_unslash( $_POST['selected_columns'] ) ); // WPCS: input var ok, sanitization ok.
		}

		if ( ! empty( $_POST['export_meta'] ) ) { // WPCS: input var ok.
			$exporter->enable_meta_export( true );
		}

		if ( ! empty( $_POST['export_types'] ) ) { // WPCS: input var ok.
			$exporter->set_product_types_to_export( wp_unslash( $_POST['export_types'] ) ); // WPCS: input var ok, sanitization ok.
		}

		if ( ! empty( $_POST['filename'] ) ) { // WPCS: input var ok.
			$exporter->set_filename( wp_unslash( $_POST['filename'] ) ); // WPCS: input var ok, sanitization ok.
		}

		$exporter->set_page( $step );
		$exporter->generate_file();

		$query_args = apply_filters(
			'woocommerce_export_get_ajax_query_args',
			array(
				'nonce'    => wp_create_nonce( 'product-csv' ),
				'action'   => 'download_product_csv',
				'filename' => $exporter->get_filename(),
			)
		);

		if ( 100 === $exporter->get_percent_complete() ) {
			wp_send_json_success(
				array(
					'step'       => 'done',
					'percentage' => 100,
					'url'        => add_query_arg( $query_args, wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_product_export_endpoint', 'vendor', 'general', 'product-export')) ),
				)
			);
		} else {
			wp_send_json_success(
				array(
					'step'       => ++$step,
					'percentage' => $exporter->get_percent_complete(),
					'columns'    => $exporter->get_column_names(),
				)
			);
		}
	}
	
	function set_vendor_products_only( $args ) {
		$current_user_id = get_current_user_id();
		if( is_user_wcmp_vendor($current_user_id) ) {
			$args['author'] = $current_user_id;
		}
		
		return $args;
	}
}
