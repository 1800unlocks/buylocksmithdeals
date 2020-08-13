<?php
/**
 * Plugin Name: Advanced Frontend Manager
 * Plugin URI: https://wc-marketplace.com/
 * Description: Allow your vendors to manage their individual shops from the front end using the Advanced Frontend Manager.
 * Author: WC Marketplace
 * Author URI: https://wc-marketplace.com
 * Version: 3.2.1
 * Requires at least: 4.2
 * Tested up to: 5.4.2
 * WC requires at least: 3.0
 * WC tested up to: 4.3.0
 * Text Domain: wcmp-afm
 * Domain Path: /languages/
 *
 * @package  WCMp_AFM
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define WCMp_AFM_PLUGIN_FILE.
if ( ! defined( 'WCMp_AFM_PLUGIN_FILE' ) ) {
	define( 'WCMp_AFM_PLUGIN_FILE', __FILE__ );
}

// Include the main Advanced Frontend Manager class.
if ( ! class_exists( 'WCMp_AFM' ) ) {
	include_once plugin_dir_path( __FILE__ ) . 'config.php';
	include_once plugin_dir_path( __FILE__ ) . 'classes/class-wcmp-afm.php';
}

/**
 * Main instance of Advanced Frontend Manager.
 *
 * Returns the main instance of WCMp_AFM to prevent the need to use globals.
 *
 * @since  3.0.0
 * @return WCMp_AFM
 */

function afm() {
    return WCMp_AFM::instance();
}

// Global for backwards compatibility.
$GLOBALS['WCMp_Frontend_Product_Manager'] = afm();