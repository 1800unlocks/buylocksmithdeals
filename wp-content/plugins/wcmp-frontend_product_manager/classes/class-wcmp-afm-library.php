<?php

/**
 * WCMp_AFM_Library setup
 *
 * @package  WCMp_AFM/classes
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Library {

    public $lib_path;
    public $lib_url;
    public $php_lib_path;
    public $php_lib_url;
    public $js_lib_path;
    public $js_lib_url;

    public function __construct() {

        $this->lib_path = WCMp_AFM_PLUGIN_DIR . 'lib/';

        $this->lib_url = WCMp_AFM_PLUGIN_URL . 'lib/';

        $this->php_lib_path = $this->lib_path . 'php/';

        $this->php_lib_url = $this->lib_url . 'php/';

        $this->js_lib_path = $this->lib_path . 'js/';

        $this->js_lib_url = $this->lib_url . 'js/';
    }

    /**
     * PHP WP fields Library
     */
    // public function load_wp_fields() {
    //     if ( ! class_exists( 'DC_WP_Fields' ) )
    //         require_once ($this->php_lib_path . 'class-dc-wp-fields.php');
    //     $DC_WP_Fields = new DC_WP_Fields();
    //     return $DC_WP_Fields;
    // }

    /**
     * Jquery qTip library
     */
    public function load_qtip_lib() {
        global $WCMp_Afm;
        wp_enqueue_script( 'qtip_js', $this->js_lib_url . 'qtip/qtip.js', array( 'jquery' ), afm()->version, true );
        wp_enqueue_style( 'qtip_css', $this->js_lib_url . 'qtip/qtip.css', array(), afm()->version );
    }

    /**
     * WP Media library
     */
    public function load_upload_lib() {
        global $WCMp_Afm;
        wp_enqueue_media();
        wp_enqueue_script( 'upload_js', $this->js_lib_url . 'upload/media-upload.js', array( 'jquery' ), afm()->version, true );
        wp_enqueue_style( 'upload_css', $this->js_lib_url . 'upload/media-upload.css', array(), afm()->version );
    }

    /**
     * WP ColorPicker library
     */
    public function load_colorpicker_lib() {
        global $WCMp_Afm;
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'colorpicker_init', $this->js_lib_url . 'colorpicker/colorpicker.js', array( 'jquery', 'wp-color-picker' ), afm()->version, true );
        wp_enqueue_style( 'wp-color-picker' );
    }

    /**
     * WP DatePicker library
     */
    public function load_datepicker_lib() {
        global $WCMp_Afm;
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_style( 'jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
    }

    /**
     * fullcalendar library
     */
    public function load_fullcalendar_lib() {
        global $WCMp;
        wp_enqueue_style( 'afm-fullcalendar-css', $this->js_lib_url . 'fullcalendar/fullcalendar.css', array(), afm()->version );
        wp_enqueue_style( 'afm-qtip2-css', '//cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.css', array(), afm()->version );
        wp_enqueue_style( 'afm-magnific-popup-css', $this->js_lib_url . 'fullcalendar/magnific-popup.css', array(), afm()->version );

        wp_enqueue_script( 'afm-moment-js', $this->js_lib_url . 'fullcalendar/moment.js', array( 'jquery' ), afm()->version, true );
        wp_enqueue_script( 'afm-qtip2-js', '//cdn.jsdelivr.net/qtip2/3.0.3/jquery.qtip.min.js', array( 'jquery', 'afm-moment-js' ), afm()->version, true );
        wp_enqueue_script( 'afm-fullcalendar-js', $this->js_lib_url . 'fullcalendar/fullcalendar.min.js', array( 'jquery' ), afm()->version, true );
        wp_enqueue_script( 'afm-locale-all-js', $this->js_lib_url . 'fullcalendar/locale-all.js', array( 'jquery' ), afm()->version, true );
        wp_enqueue_script( 'afm-magnific-popup-js', $this->js_lib_url . 'fullcalendar/jquery.magnific-popup.min.js', array( 'jquery' ), afm()->version, true );
    }

}
