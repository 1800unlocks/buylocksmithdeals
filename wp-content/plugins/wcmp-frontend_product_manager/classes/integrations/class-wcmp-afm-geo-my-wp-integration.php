<?php
/**
 * WCMp Advanced Frontend Manager
 *
 * Geo My Wordpress Support
 *
 * @author WC Marketplace
 * @package WCMp_AFM/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Geo_My_Wp_Integration {

    protected $id = null;
    protected $product = null;
    protected $plugin = 'geo-my-wp';

    public function __construct() {
        if ( ! class_exists( 'GMW_Location_Form' ) || ! current_vendor_can('gmw_product_geotagging_enabled') ) {
            return;
        }
        /**
         * add `GMW Form` media button in tinymce editor
         */
        if ( ! class_exists( 'GMW_Admin' ) ) {
            require_once( GMW_PATH . '/includes/admin/class-gmw-admin.php' );
        }
        add_action( 'media_buttons', array( 'GMW_Admin', 'add_form_button' ), 25 );
        add_action( 'wp_footer', array( 'GMW_Admin', 'form_insert_popup' ) );
        
        add_action( 'wcmp_afm_after_product_excerpt_metabox_panel', array( $this, 'gmw_location_panel' ) );
        
        add_filter( 'wcmp_afm_enqueue_geo-my-wp_style', array( $this, 'gmw_enqueue_leaflet_style' ) );
        add_filter( 'gmw_location_form_default_location', array( $this, 'gmw_default_location' ), 10, 2 );
    }

    //this will be called from the main Integration class after WCMp_AFM_Add_Product_Endpoint class constructor executed
    public function set_props( $id ) {
        $this->id = $id;

        //after setting id get the WC product object
        $this->product = wc_get_product( $this->id );
    }
    
    public function gmw_location_panel() {
        afm()->template->get_template( 'products/gmw/html-gwm-location.php', array( 'id' => $this->id, 'self' => $this, 'product' => $this->product ) );
    }

    private function build_req_param( $key, $req_type, $args ) {
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?';
        $apikey = '&key=' . $key;
        if ( $req_type == 'geocode' && ! empty( $args['lat'] ) && ! empty( $args['lng'] ) ) {
            return $url . 'latlng=' . $args['lat'] . ',' . $args['lng'] . $apikey;
        } elseif ( $req_type == 'reverse_geocode' && ! empty( $args['address'] ) ) {
            return $url . 'address=' . urlencode( $args['address'] ) . $apikey;
        }
        return '';
    }

    private function get_geocoder_address( $key, $req_type, $args ) {
        $request_url = $this->build_req_param( $key, $req_type, $args );
        if ( ! $request_url ) {
            return false;
        }

        $geocoder_response = wp_remote_get( $request_url );
        if ( is_array( $geocoder_response ) ) {
            $result = json_decode( $geocoder_response['body'] ); // use the content
            $address = array(
                'street_number'     => '',
                'street_name'       => '',
                'street'            => '',
                'premise'           => '',
                'neighborhood'      => '',
                'city'              => '',
                'county'            => '',
                'region_name'       => '',
                'region_code'       => '',
                'country_name'      => '',
                'country_code'      => '',
                'postcode'          => '',
                'address'           => '',
                'formatted_address' => '',
            );

            if ( isset( $result->results[0]->geometry->location ) ) {
                $address['latitude'] = $result->results[0]->geometry->location->lat;
                $address['longitude'] = $result->results[0]->geometry->location->lng;
            }
            
            if ( isset( $result->results[0]->formatted_address ) ) {
                $address['address'] = $result->results[0]->formatted_address;
                $address['formatted_address'] = $result->results[0]->formatted_address;
            }

            if ( isset( $result->results[0]->address_components ) ) {
                $address_components = $result->results[0]->address_components;
                foreach ( $address_components as $component ) {
                    $type = implode( ",", $component->types );
                    if ( $type == 'street_number' && ! empty( $component->long_name ) ) {
                        $address['street_number'] = $component->long_name;
                    } elseif ( $type == 'route' && ! empty( $component->long_name ) ) {
                        $address['street_name'] = $component->long_name;
                        $address['street'] = ! empty( $address['street_number'] ) ? $address['street_number'] . ' ' . $component->long_name : $component->long_name;
                    } elseif ( $type == 'subpremise' && ! empty( $component->long_name ) ) {
                        $address['premise'] = $component->long_name;
                    } elseif ( $type == 'neighborhood,political' && ! empty( $component->long_name ) ) {
                        $address['neighborhood'] = $component->long_name;
                    } elseif ( $type == 'locality,political' && ! empty( $component->long_name ) ) {
                        $address['city'] = $component->long_name;
                    } elseif ( $type == 'administrative_area_level_2,political' && ! empty( $component->long_name ) ) {
                        $address['county'] = $component->long_name;
                    } elseif ( $type == 'administrative_area_level_1,political' ) {
                        $address['region_name'] = $component->long_name;
                        $address['region_code'] = $component->short_name;
                    } elseif ( $type == 'country,political' ) {
                        $address['country_name'] = $component->long_name;
                        $address['country_code'] = $component->short_name;
                    } elseif ( $type == 'postal_code' && ! empty( $component->long_name ) ) {
                        $address['postcode'] = $component->long_name;
                    }
                }
            }
            return $address;
        }
        return array();
    }

    private function get_address_fields( $gapi_key, $vendor_id ) {
        $fields = array(
            '_vendor_address_1',
            '_vendor_address_2',
            '_vendor_city',
            '_vendor_state',
            '_vendor_country',
            '_vendor_postcode',
        );
        $address_fields = array();
        foreach ( $fields as $field ) {
            $address_fields[] = trim( get_user_meta( $vendor_id, $field, true ), ',' );
        }
        $address_fields = implode( ',', array_filter( $address_fields ) );

        return $this->get_geocoder_address( $gapi_key, 'reverse_geocode', array( 'address' => $address_fields ) );
    }
    
    public function gmw_enqueue_leaflet_style( $handle ) {
        if(GMW()->maps_provider === 'leaflet') {
            return true;
        }
        return false;
    }
    
    public function gmw_default_location( $saved_location, $args ) {
        if ( empty( $saved_location ) ) {
            $vendor_id = afm()->vendor_id;
            $vendor_lat = get_user_meta( $vendor_id, '_store_lat', true );
            $vendor_lng = get_user_meta( $vendor_id, '_store_lng', true );
            $gapi_key = ! empty( get_wcmp_vendor_settings( 'google_api_key' ) ) ? get_wcmp_vendor_settings( 'google_api_key' ) : gmw_get_option( 'api_providers', 'google_maps_server_api_key', '' );

            if ( empty( $gapi_key ) ) {
                return $saved_location;
            }

            if ( ! empty( $vendor_lat ) && ! empty( $vendor_lng ) ) {
                $address = $this->get_geocoder_address( $gapi_key, 'geocode', array( 'lat' => $vendor_lat, 'lng' => $vendor_lng ) );
            } else {
                $address = $this->get_address_fields( $gapi_key, $vendor_id );
            }

            if ( empty( $address ) ) {
                return $saved_location;
            }

            $saved_location = array_merge( array( 'ID' => 0 ), $address );
        }
        return $saved_location;
    }
}
