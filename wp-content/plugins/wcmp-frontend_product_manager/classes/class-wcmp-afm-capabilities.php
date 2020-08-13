<?php

/**
 * WCMp_AFM_Capabilities setup
 *
 * @package  WCMp_AFM/classes
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Capabilities {

    /**
     * Primary class constructor.
     *
     * @since 3.0.0
     * @access public
     */
    public function __construct() {
        add_action( 'wcmp_init', array( $this, 'vendor_permitted_capabilities' ), 20 );

        //WooCommerce Bookings
        add_filter( 'vendor_booking_allowed_caps', array( $this, 'set_allowed_booking_caps' ) );
        add_filter( 'vendor_rentalpro_allowed_caps', array( $this, 'set_allowed_rentalpro_caps' ) );
        add_filter( 'vendor_geo-my-wp_allowed_caps', array( $this, 'set_allowed_gmw_caps' ) );
    }

    /**
     * Vendor role valid capability checker 
     * remove any invalid capability
     * 
     * @return void
     */
    public function vendor_permitted_capabilities() {
        $vendor = get_role( 'dc_vendor' );
        $capabilities = isset( $vendor->capabilities ) ? $vendor->capabilities : array();

        $remove_caps = $this->remove_cap_list();
        $is_changed = false;
        //remove any unauthorized capability from vendor role
        foreach ( $remove_caps as $cap ) {
            if ( array_key_exists( $cap, $capabilities ) ) {
                $vendor->remove_cap( $cap );
                $is_changed = true;
            }
        }
        foreach ( afm()->dependencies->allowed_vendor_caps as $plugin => $caps ) {
            $valid_caps = apply_filters( "vendor_{$plugin}_allowed_caps", $caps );
            $invalid_caps = array_diff( $caps, $valid_caps );
            foreach ( $invalid_caps as $cap ) {
                if ( array_key_exists( $cap, $capabilities ) ) {
                    $vendor->remove_cap( $cap );
                    $is_changed = true;
                }
            }
            foreach ( $valid_caps as $cap ) {
                if ( ! array_key_exists( $cap, $capabilities ) && ! in_array( $cap, $remove_caps ) ) {
                    $vendor->add_cap( $cap );
                    $is_changed = true;
                }
            }
        }
        if ( 1 || $is_changed ) {
            $vendor->add_cap( 'delete_posts' );
            flush_rewrite_rules();
        }

        do_action( 'afm_vendor_permitted_capabilities' );
    }

    private function remove_cap_list() {
        $diff = array(); //'add_bookable_resource'
        foreach ( afm()->dependencies->supported_vendor_caps as $key => $caps ) {
            if ( isset( afm()->dependencies->allowed_vendor_caps[$key] ) ) {
                $diff = array_merge( $diff, array_diff( $caps, afm()->dependencies->allowed_vendor_caps[$key] ) );
            } else {
                $diff = array_merge( $diff, $caps );
            }
        }
        return apply_filters( 'afm_vendor_remove_caps_list', $diff );
    }

    public function set_allowed_booking_caps( $caps ) {
        return $caps;
    }

    public function set_allowed_rentalpro_caps( $caps ) {
        if ( ! array_key_exists( 'rentalpro', afm()->integrations->get_classlist() ) || get_option( 'rnb_enable_rft_endpoint', true ) !== 'yes' ) {
            return array();
        }
        return $caps;
    }

    public function set_allowed_gmw_caps( $caps ) {
        if ( ! in_array( 'product', gmw_get_option( 'post_types_settings', 'post_types', array() ) ) ) {
            return array();
        }
        return $caps;
    }

}
