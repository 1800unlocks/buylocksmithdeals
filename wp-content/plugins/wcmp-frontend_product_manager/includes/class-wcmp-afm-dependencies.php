<?php

/**
 * WC Dependency Checker
 *
 */
class WCMp_AFM_Dependencies {

    /**
     * Activated plugin list
     * 
     * @var ARRAY_A 
     */
    protected $wp_plugins;

    /**
     * AFM prerequisite plugins
     * 
     * @var ARRAY_N | ARRAY_A based on single site or multisite
     */
    protected $required_plugins;

    /**
     * Activation fail reason
     * 
     * @var STRING 
     */
    protected $failed_check;

    /**
     * Third party integrations supported by AFM
     * 
     * @var ARRAY_A 
     */
    protected $supported_integrations;

    /**
     * Available integrations based on activated plugins that corresponds to AFM supported integrations
     * 
     * @var ARRAY_A 
     */
    protected $available_integrations;

    /**
     * Admin Allowed integrations corresponding to available integrations
     * 
     * @var ARRAY_A 
     */
    protected $allowed_integrations;

//    /**
//     * Endpoints that corresponds to the allowed integrations
//     * 
//     * @var ARRAY_A 
//     */
//    protected $allowed_endpoints;

    /**
     * List of all the supported vendor caps
     * 
     * @var ARRAY_A 
     */
    protected $supported_vendor_caps = array();

    /**
     * List of all the allowed vendor caps that gets added to `dc_vendor` Role
     * 
     * @var ARRAY_A 
     */
    protected $allowed_vendor_caps = array();

    public function __construct() {
        $this->wp_plugins = (array) get_option( 'active_plugins', array() );
        if ( is_multisite() ) {
            $this->wp_plugins = array_merge( $this->wp_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
        }
        $this->required_plugins = $this->set_required_plugins();
        $this->supported_integrations = $this->set_supported_integrations();
        $this->available_integrations = $this->set_available_integrations();
        $this->allowed_integrations = $this->set_allowed_integrations();

        $this->supported_vendor_caps = $this->set_supported_caps();
        $this->allowed_vendor_caps = $this->set_allowed_caps();
    }

    /**
     * Auto-load in-accessible properties on demand.
     *
     * @param mixed $key Key name.
     * @return mixed
     */
    public function __get( $key ) {
        if ( in_array( $key, array( 'supported_integrations', 'available_integrations', 'allowed_integrations', 'supported_vendor_caps', 'allowed_vendor_caps' ), true ) ) {
            return $this->$key;
        }
    }

    public function check_prerequisites() {
        foreach ( $this->required_plugins as $plugin => $path ) {
            if ( ! $this->is_active( $path ) ) {
                $this->failed_check = $plugin;
                add_action( 'admin_notices', array( $this, 'prerequisite_fail_notice' ) );
                return false;
            }
        }
        return true;
    }

    public function wcmp_version_check() {
        global $WCMp;
        if ( ! version_compare( $WCMp->version, '3.3.0', '>=' ) ) {
            $this->failed_check = 'version';
            add_action( 'admin_notices', array( $this, 'prerequisite_fail_notice' ) );
            return false;
        }
        return true;
    }

    public function php_version_check() {
        if ( version_compare( PHP_VERSION, '5.5.16', '<' ) ) {
            $this->failed_check = 'phpversion';
            add_action( 'admin_notices', array( $this, 'prerequisite_fail_notice' ) );
            return false;
        }
        return true;
    }

    public function can_plugin_activate() {
        return $this->check_prerequisites() && $this->wcmp_version_check() && $this->php_version_check();
    }

    function prerequisite_fail_notice() {
        if ( $this->failed_check ) {
            if ( $this->failed_check === 'woocommerce' ) {
                ?>
                <div id="message" class="error">
                    <p><?php printf( __( '%sAdvanced Frontend Manager is inactive.%s The %sWooCommerce plugin%s must be active for the Advanced Frontend Manager to work. Please %sinstall & activate WooCommerce%s', 'wcmp-afm' ), '<strong>', '</strong>', '<a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . admin_url( 'plugin-install.php?tab=search&s=woocommerce' ) . '">', '&nbsp;&raquo;</a>' ); ?></p>
                </div>
                <?php
            } elseif ( $this->failed_check === 'wcmp' ) {
                ?>
                <div id="message" class="error">
                    <p><?php printf( __( '%sAdvanced Frontend Manager is inactive.%s The %sWC Marketplace%s must be active for the Advanced Frontend Manager to work. Please %sinstall, activate & make sure WC Marketplace%s is updated', 'wcmp-afm' ), '<strong>', '</strong>', '<a target="_blank" href="https://wordpress.org/plugins/dc-woocommerce-multi-vendor/">', '</a>', '<a href="' . admin_url( 'plugin-install.php?tab=search&s=wc+marketplace' ) . '">', '&nbsp;&raquo;</a>' ); ?></p>
                </div>
                <?php
            } elseif ( $this->failed_check === 'version' ) {
                ?>
                <div id="message" class="error">
                    <p><?php printf( __( 'Warning! This version of Frontend Manager is not compatible with older version of WC Marketplace. Please update to WCMp 3.3 or later.', 'wcmp-afm' ) ); ?></p>
                </div>
                <?php
            } elseif ( $this->failed_check === 'phpversion' ) {
                ?>
                <div id="message" class="error">
                    <p><?php printf( __( '%sAdvanced Frontend Manager%s requires PHP 5.5.16 or greater. We recommend upgrading to PHP 5.5.16 or greater.', 'wcmp-afm' ), '<strong>', '</strong>' ); ?></p>
                </div>
                <?php
            }
        }
    }

    /**
     * Set plugin prerequisites
     * All these plugins needs to be activated before activating AFM
     * 
     * @return ARRAY_A
     */
    private function set_required_plugins() {
        return array(
            'woocommerce' => 'woocommerce/woocommerce.php',
            'wcmp'        => 'dc-woocommerce-multi-vendor/dc_product_vendor.php',
        );
    }

    /**
     * List of all the supported third party plugins
     * 
     * @return ARRAY_A
     */
    private function set_supported_integrations() {
        return apply_filters( 'afm_supported_integrations', array(
            'rental'               => array(
                'src'    => 'booking-and-rental-system-woocommerce/redq-rental-and-bookings.php',
                'p_type' => array(
                    'redq_rental' => array(
                        'label' => __( 'Rental Product', 'redq-rental' ),
                    ),
                ),
            ),
            'rentalpro'            => array(
                'src'       => 'woocommerce-rental-and-booking/redq-rental-and-bookings.php',
                'p_type'    => array(
                    'redq_rental' => array(
                        'label' => __( 'Rental Product', 'redq-rental' ),
                    ),
                ),
                'endpoints' => array(
                    'request-quote'   => array(
                        'label'      => __( 'Quote', 'wcmp-afm' ),
                        'vendor_can' => array( 'manage_rental_quotes' ),
                        'menu'       => array(
                            'cap'      => 'vendor_can_access_request_quote',
                            'position' => 15,
                            'icon'     => 'wcmp-font ico-quote',
                        ),
                    ),
                    'quote-details'   => array(
                        'label'      => __( 'Quote Request', 'wcmp-afm' ),
                        'vendor_can' => array( 'manage_rental_quotes' ),
                    ),
                    'rental-calendar' => array(
                        'label'      => __( 'Rental Calendar', 'wcmp-afm' ),
                        'vendor_can' => array( 'view_rental_calendar' ),
                        'menu'       => array(
                            'cap'      => 'vendor_can_access_rental_calendar',
                            'position' => 20,
                            'icon'     => 'wcmp-font ico-rental-calender',
                        ),
                    ),
                ),
            // 'style'     => 'rental',
            ),
            'booking'              => array(
                'src'             => 'woocommerce-bookings/woocommerce-bookings.php',
                'p_type'          => array(
                    'booking' => array(
                        'label'   => __( 'Bookable product', 'woocommerce-bookings' ),
                        'options' => array(
                            'wc_booking_has_persons'   => __( 'Has persons', 'woocommerce-bookings' ),
                            'wc_booking_has_resources' => __( 'Has resources', 'woocommerce-bookings' ),
                        ),
                    ),
                ),
                'endpoints'       => array(
                    'bookings'             => array(
                        'label'      => __( 'All Bookings', 'woocommerce-bookings' ),
                        'vendor_can' => array( 'manage_bookings' ),
                        'menu'       => array(
                            'cap'      => 'vendor_can_manage_bookings',
                            'position' => 10,
                            'icon'     => 'ico-booking-icon',
                        ),
                    ),
                    'resources'            => array(
                        'label'      => __( 'Resources', 'woocommerce-bookings' ),
                        'vendor_can' => array( 'manage_resources' ),
                        'menu'       => array(
                            'cap'      => 'vendor_can_manage_resources',
                            'position' => 20,
                            'icon'     => 'ico-resources',
                        ),
                    ),
                    'create-booking'       => array(
                        'label'      => __( 'Add Booking', 'woocommerce-bookings' ),
                        'vendor_can' => array( 'create_booking' ),
                        'menu'       => array(
                            'cap'      => 'vendor_can_add_booking',
                            'position' => 30,
                            'icon'     => 'ico-add-booking',
                        ),
                    ),
                    'booking-calendar'     => array(
                        'label'      => __( 'Calendar', 'woocommerce-bookings' ),
                        'vendor_can' => array( 'booking_calendar' ),
                        'menu'       => array(
                            'cap'      => 'vendor_can_view_booking_calendar',
                            'position' => 40,
                            'icon'     => 'ico-calendar-icon',
                        ),
                    ),
                    'booking-notification' => array(
                        'label'      => __( 'Send Notification', 'woocommerce-bookings' ),
                        'vendor_can' => array( 'send_booking_notification' ),
                        'menu'       => array(
                            'cap'      => 'vendor_can_send_booking_notification',
                            'position' => 50,
                            'icon'     => 'ico-notification',
                        ),
                    ),
                ),
                /**
                 * any additional caps apart from endpoints caps
                 */
                'additional_caps' => array( 'update_booking_details', 'share_admin_resources', 'add_bookable_resource' ),
            ),
            'accommodation'        => array(
                'src'          => 'woocommerce-accommodation-bookings/woocommerce-accommodation-bookings.php',
                'dependencies' => array( 'booking' ),
                'p_type'       => array(
                    'accommodation-booking' => array(
                        'label'   => __( 'Accommodation product', 'woocommerce-accommodation-bookings' ),
                        'options' => array(
                            'wc_booking_has_persons'   => __( 'Has persons', 'woocommerce-bookings' ),
                            'wc_booking_has_resources' => __( 'Has resources', 'woocommerce-bookings' ),
                        ),
                    ),
                ),
            ),
            'product-bundle'       => array(
                'src'    => 'woocommerce-product-bundles/woocommerce-product-bundles.php',
                'p_type' => array(
                    'bundle' => array(
                        'label' => __( 'Product bundle', 'woocommerce-product-bundles' ),
                    ),
                ),
            ),
            'subscription'         => array(
                'src'       => 'woocommerce-subscriptions/woocommerce-subscriptions.php',
                'p_type'    => array(
                    'subscription'          => array(
                        'label' => __( 'Simple subscription', 'woocommerce-subscriptions' ),
                    ),
                    'variable-subscription' => array(
                        'label' => __( 'Variable subscription', 'woocommerce-subscriptions' ),
                    ),
                ),
                'endpoints' => array(
                    'subscription' => array(
                        'label'      => __( 'All Subscriptions', 'woocommerce-bookings' ),
                        'vendor_can' => array( 'manage_subscriptions' ),
                        'menu'       => array(
                            'cap'      => 'vendor_can_manage_subscriptions',
                            'position' => 10,
                            'icon'     => 'ico-subscriptions',
                        ),
                    ),
                ),
            ),
            'product-addons'       => array(
                'src' => 'woocommerce-product-addons/woocommerce-product-addons.php',
            ),
            'yith-auction'         => array(
                'src'    => 'yith-auctions-for-woocommerce/init.php',
                'p_type' => array(
                    'auction' => array(
                        'label' => __( 'Auction', 'yith-auctions-for-woocommerce' ),
                    ),
                ),
            ),
            'yith-auctionpro'      => array(
                'src'             => 'yith-woocommerce-auctions-premium/init.php',
                'p_type'          => array(
                    'auction' => array(
                        'label' => __( 'Auction', 'yith-auctions-for-woocommerce' ),
                    ),
                ),
                //'noscript' => true,
                'endpoints'       => array(
                    'auctions' => array(
                        'label'      => __( 'Auctions', 'yith-auctions-for-woocommerce' ),
                        'vendor_can' => array( 'manage_auctions' ),
                        'menu'       => array(
                            'cap'      => 'vendor_can_manage_auctions',
                            'position' => 36,
                            'icon'     => 'ico-quote',
                        ),
                    ),
                ),
                'additional_caps' => array( 'yith_send_winner_email' ),
            ),
            'simple-auction'       => array(
                'src'             => 'woocommerce-simple-auctions/woocommerce-simple-auctions.php',
                'p_type'          => array(
                    'auction' => array(
                        'label' => __( 'Auction', 'wc_simple_auctions' ),
                    ),
                ),
                'endpoints'       => array(
                    'auctions' => array(
                        'label'      => __( 'Auctions', 'wcmp-afm' ),
                        'vendor_can' => array( 'manage_simple_auctions' ),
                        'menu'       => array(
                            'cap'      => 'vendor_can_manage_auctions',
                            'position' => 37,
                            'icon'     => 'ico-quote',
                        ),
                    ),
                ),
                'additional_caps' => array( 'simple_auction_send_winner_email', 'simple_auction_delete_bid', 'simple_auction_remove_reserve_price' ),
            ),
            'geo-my-wp'            => array(
                'src'             => 'geo-my-wp/geo-my-wp.php',
                'additional_caps' => array( 'gmw_product_geotagging_enabled', 'gmw_vendor_can_geotag' ),
                'style'     => 'leaflet', //enqueue only when map provider is leaflet
            ),
            'toolset'              => array(
                'src' => 'types/wpcf.php',
                //'noscript' => true,
            ),
            'advanced-custom-fields'              => array(
                'src' => 'advanced-custom-fields/acf.php',
                'noscript' => true,
            ), 
            'advanced-custom-fields-pro'              => array(
                'src' => 'advanced-custom-fields-pro/acf.php',
                'noscript' => true,
            ),
            'per-product-shipping' => array(
                'src'             => 'woocommerce-shipping-per-product/woocommerce-shipping-per-product.php',
                'additional_caps' => array( 'pps_standalone_method_enabled' ),
            ),
            'appointment'              => array(
                'src'             => 'woocommerce-appointments/woocommerce-appointments.php',
                'p_type'          => array(
                    'appointment' => array(
                        'label'   => __( 'Appointment product', 'woocommerce-appointments' ),
                    ),
                ),
                'endpoints'       => array(
                    'appointments'             => array(
                        'label'      => __( 'All Appointments', 'woocommerce-appointments' ),
                        'vendor_can' => array( 'manage_appointments' ),
                        'menu'       => array(
                            'cap'      => 'vendor_can_manage_appointments',
                            'position' => 17,
                            'icon'     => 'ico-appointment_icon',
                        ),
                    ),
                    'appointment-calendar'     => array(
                        'label'      => __( 'Calendar', 'woocommerce-appointments' ),
                        'vendor_can' => array( 'appointment_calendar' ),
                        'menu'       => array(
                            'cap'      => 'vendor_can_access_appointment_calendar',
                            'position' => 40,
                            'icon'     => 'ico-calendar-icon',
                        ),
                    ),
                ),
                /**
                 * any additional caps apart from endpoints caps
                 */
                'additional_caps' => array( 'update_appointment_details', 'share_admin_resources', 'add_appointment_resource' ),
            ),
            'affiliate-wp'              => array(
                'src' => 'affiliate-wp/affiliate-wp.php',
                'endpoints'       => array(
                    'assign-affiliate' => array(
                        'label'      => __( 'Request Affiliate', 'wcmp-afm' ),
                        'vendor_can' => array( 'assign_simple_affiliate_request' ),
                        'menu'       => array(
                            'cap'      => 'vendor_can_assign_affiliate_request',
                            'position' => 10,
                            'icon'     => 'ico-quote',
                        ),
                    ),
                    'manage-affiliate' => array(
                        'label'      => __( 'Manage Affiliate', 'wcmp-afm' ),
                        'vendor_can' => array( 'manage_simple_affiliate' ),
                        'menu'       => array(
                            'cap'      => 'vendor_can_manage_affiliate',
                            'position' => 20,
                            'icon'     => 'ico-quote',
                        ),
                    ),
                ),
                'additional_caps' => array( 'simple_affiliate_send_winner_email', 'simple_affiliate_delete_bid', 'simple_affiliate_remove_reserve_price' ),
            )
            
        ) );
    }

    /**
     * List of all the activated third party plugins within the supported list of plugins
     * 
     * @return ARRAY_A
     */
    private function set_available_integrations() {
        $available_integrations = array();
        foreach ( $this->supported_integrations as $key => $details ) {
            if ( ! empty( $details['src'] ) && $this->is_active( $details['src'] ) ) {
                if ( isset( $details['p_type'] ) && is_array( $details['p_type'] ) ) {
                    foreach ( $details['p_type'] as $p_type => $info ) {
                        if ( ! empty( $p_type ) ) {
                            $available_integrations[$key][$p_type] = $info;
                        }
                    }
                } else {
                    $available_integrations[$key] = false;
                }
            }
        }
        return $available_integrations;
    }

    /**
     * List of all the allowed third party plugins within the available list of plugins
     * 
     * @return ARRAY_A
     */
    private function set_allowed_integrations() {
        $vendor_caps = (array) get_option( 'wcmp_capabilities_product_settings_name', array() );
        $allowed_integrations = array();
        foreach ( $this->available_integrations as $plugin => $integrations ) {
            $plugin_details = $this->supported_integrations[$plugin];
            if ( ! empty( $plugin_details['dependencies'] ) && is_array( $plugin_details['dependencies'] ) ) {
                foreach ( $plugin_details['dependencies'] as $dependency ) {
                    if ( ! isset( $this->available_integrations[$dependency] ) ) {
                        continue 2;
                    }
                }
            }
            if ( $integrations && is_array( $integrations ) ) {
                foreach ( $integrations as $integration => $details ) {
                    if ( array_key_exists( $integration, $vendor_caps ) ) {
                        $allowed_integrations[$plugin][$integration] = $details;
                    }
                }
            } else {
                $allowed_integrations[$plugin] = false;
            }
        }
        return $allowed_integrations;
    }

    /**
     * List of all the allowed endpoints corresponds to the allowed integrations
     * 
     * @param string 
     * @return ARRAY_A
     */
    public function get_allowed_endpoints( $plugin ) {
        $allowed_endpoints = array();
        if ( isset( $this->allowed_integrations[$plugin] ) && ! empty( $this->supported_integrations[$plugin]['endpoints'] ) ) {
            foreach ( $this->supported_integrations[$plugin]['endpoints'] as $endpoint => $details ) {
                if ( ! empty( $details['vendor_can'] ) && $this->vendor_can( $details['vendor_can'] ) ) {
                    $allowed_endpoints[$endpoint] = $details;
                }
            }
        }
        return $allowed_endpoints;
    }

    /**
     * Check if a plugin is activated
     * @param string $path
     * @return BOOLEAN
     */
    private function is_active( $path ) {
        return ( in_array( $path, $this->wp_plugins ) || array_key_exists( $path, $this->wp_plugins ) ) ? true : false;
    }

    /**
     * List of all the caps associated with supported plugins
     * 
     * @return ARRAY_A
     */
    private function set_supported_caps() {
        $supported_caps = array();
        foreach ( $this->supported_integrations as $plugin => $integrations ) {
            $plugin_caps = array();
            if ( ! empty( $integrations['endpoints'] ) && is_array( $integrations['endpoints'] ) ) {
                foreach ( $integrations['endpoints'] as $endpoint => $details ) {
                    if ( ! empty( $details['vendor_can'] ) ) {
                        $plugin_caps = array_merge( $plugin_caps, $details['vendor_can'] );
                    }
                }
            }
            if ( ! empty( $integrations['additional_caps'] ) ) {
                $plugin_caps = array_merge( $plugin_caps, $integrations['additional_caps'] );
            }
            if ( ! empty( $plugin_caps ) ) {
                $supported_caps[$plugin] = $plugin_caps;
            }
        }
        return $supported_caps;
    }

    /**
     * List of all the caps associated with activated plugins
     * 
     * @return ARRAY_A
     */
    private function set_allowed_caps() {
        $allowed_caps = array();
        foreach ( $this->allowed_integrations as $plugin => $p_types ) {
            if ( ! empty( $this->supported_vendor_caps[$plugin] ) ) {
                $allowed_caps[$plugin] = $this->supported_vendor_caps[$plugin];
            }
        }
        return $allowed_caps;
    }

    public function register_endpoints() {
        $endpoints = array();
        $mask = $this->get_afm_endpoints_mask();
        foreach ( $this->allowed_integrations as $plugin => $integrations ) {
            if ( ! empty( $this->supported_integrations[$plugin]['endpoints'] ) ) {
                foreach ( $this->supported_integrations[$plugin]['endpoints'] as $endpoint => $details ) {
                    if ( ! empty( $details['vendor_can'] ) && $this->vendor_can( $details['vendor_can'] ) ) {
                        add_rewrite_endpoint( $endpoint, $mask );
                    }
                }
            }
        }
        do_action( 'wcmp_afm_vendor_dashboard_endpoint' );
    }

    /**
     * Endpoint mask describing the places the endpoint should be added.
     *
     * @since 2.6.2
     * @return int
     */
    protected function get_afm_endpoints_mask() {
        if ( 'page' === get_option( 'show_on_front' ) ) {
            $page_on_front = get_option( 'page_on_front' );
            if ( $page_on_front == wcmp_vendor_dashboard_page_id() ) {
                return EP_ROOT | EP_PAGES;
            }
        }
        return EP_PAGES;
    }

    public function plugin_endpoints_query_vars( $endpoints, $plugin_endpoints ) {
        if ( ! empty( $plugin_endpoints ) ) {
            foreach ( $plugin_endpoints as $key => $endpoint ) {
                if ( ! empty( $endpoint['vendor_can'] ) && ! empty( $endpoint['label'] ) && current_vendor_can( $endpoint['vendor_can'] ) ) {
                    $endpoints[$key] = array(
                        'label'    => $endpoint['label'],
                        'endpoint' => $key,
                    );
                }
            }
        }
        return $endpoints;
    }

    public function plugin_dashboard_navs( $navs, $plugin_endpoints, $parent_menu = null ) {
        $current_vendor_id = afm()->vendor_id;
        if ( $current_vendor_id ) {
            $submenu = array();
            foreach ( $plugin_endpoints as $key => $endpoint ) {
                $cap = ! empty( $endpoint['vendor_can'] ) ? $endpoint['vendor_can'] : '';
                $label = ! empty( $endpoint['label'] ) ? $endpoint['label'] : '';

                if ( $cap && current_vendor_can( $cap ) && $label && isset( $endpoint['menu'] ) ) {
                    $menu_cap = ! empty( $endpoint['menu']['cap'] ) ? $endpoint['menu']['cap'] : '';
                    $position = ! empty( $endpoint['menu']['position'] ) ? absint( $endpoint['menu']['position'] ) : 0;
                    $icon = ! empty( $endpoint['menu']['icon'] ) ? " " . $endpoint['menu']['icon'] : '';
                    if ( $menu_cap ) {
                        $submenu[$key] = array(
                            'label'       => $label,
                            'url'         => wcmp_get_vendor_dashboard_endpoint_url( $key ),
                            'capability'  => apply_filters( $menu_cap, true ),
                            'position'    => $position,
                            'link_target' => '_self',
                            'nav_icon'    => 'wcmp-font' . $icon,
                        );
                    }
                }
            }
            if ( ! empty( $submenu ) && ! empty( $parent_menu ) ) {
                if ( is_array( $parent_menu ) ) {
                    $navs[$parent_menu['plugin']] = array(
                        'label'       => $parent_menu['label'],
                        'url'         => '#',
                        'capability'  => apply_filters( $parent_menu['capability'], true ),
                        'position'    => $parent_menu['position'],
                        'submenu'     => $submenu,
                        'link_target' => '_self',
                        'nav_icon'    => $parent_menu['nav_icon'],
                    );
                } elseif ( is_string( $parent_menu ) ) {
                    $navs[$parent_menu]['submenu'] = array_merge( $navs[$parent_menu]['submenu'], $submenu );
                }
            }
        }
        return $navs;
    }

    /**
     * Check if vendor has a certain capability 
     * @param string | ARRAY_N $capability 
     * @return boolean TRUE only if all passed capabilities are true for dc_vendor role
     */
    private function vendor_can( $capability ) {
        if ( empty( $capability ) ) {
            return false;
        }
        $vendor_role = get_role( 'dc_vendor' );
        $capabilities = isset( $vendor_role->capabilities ) ? $vendor_role->capabilities : array();

        if ( is_array( $capability ) ) {
            foreach ( $capability as $cap ) {
                if ( ! array_key_exists( $cap, $capabilities ) || ! $capabilities[$cap] ) {
                    return false;
                }
            }
            return true;
        }
        return array_key_exists( $capability, $capabilities ) && $capabilities[$capability];
    }

}
