<?php
/**
 * WCMp Advanced Frontend Manager
 *
 * Simple Subscription (Woocommerce) Support
 *
 * @author WC Marketplace
 * @package WCMp_AFM/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Subscription_Integration {
    protected $id = null;
    protected $subscription_product = null;
    protected $plugin = 'subscription';
    protected $subscription_endpoints = array();

    public function __construct() {
        global $WCMp;
        
        $this->subscription_endpoints = $this->all_subscription_endpoints();
        
        //filter for adding wcmp endpoint query vars
        add_filter( 'wcmp_endpoints_query_vars', array( $this, 'subscription_endpoints_query_vars' ) );
        add_filter( 'wcmp_vendor_dashboard_nav', array( $this, 'subscription_dashboard_navs' ) );
        
        $this->call_endpoint_contents();

        add_action( 'wcmp_product_type_options', array( $this, 'woocommerce_subscription_product_type_options' ) );

        // Subscription Product Additional Tabs
        add_filter( 'wcmp_product_data_tabs', array( $this, 'woocommerce_subscription_update_tabs' ) );

        // Subscription Product update General Tab
        add_action( 'wcmp_afm_before_general_product_data', array( &$this, 'woocommerce_subscription_general_tabs_content' ) );
        
        // Subscription Product update Shipping Tab
        add_action( 'wcmp_afm_product_options_shipping', array( &$this, 'woocommerce_subscription_shipping_tabs_content' ) );
        
        // Subscription Product update Advanced Tab
        add_action( 'wcmp_afm_product_options_advanced', array( &$this, 'woocommerce_subscription_advanced_tabs_content' ) );
        
        // Variable Subscription Product pricing fileds added
        add_action( 'wcmp_afm_after_variation_sku', array( &$this, 'woocommerce_variable_subscription_pricing_fields' ), 10, 3 );
        add_action( 'wcmp_afm_product_after_variable_attributes', array( &$this, 'woocommerce_variable_subscription_synchronise_section' ), 10, 3 );
        add_action( 'woocommerce_variable_product_bulk_edit_actions', array( &$this, 'variable_subscription_bulk_edit_actions' ), 10 );

        add_action( 'woocommerce_cart_shipping_packages', array( &$this, 'woocommerce_subscription_recurring_calculation' ),99, 1 );
        
        // add 'subscription' to allowed product type list for showing tax (if enabled), inventory contents
        add_filter( 'general_tab_pricing_section', array( $this, 'allow_subscription_product_types' ) );
        add_filter( 'general_tab_tax_section' , array( $this, 'allow_both_product_types' ) );
        add_filter( 'inventory_tab_manage_stock_section', array( $this, 'allow_both_product_types' ) );
        add_filter( 'inventory_tab_sold_individually_section', array( $this, 'allow_both_product_types' ) );
        add_filter( 'attribute_tab_enable_variation_checkbox', array( $this, 'allow_variable_subscription_product_types' ) );
        
        add_filter( 'wcmp_advance_product_script_params', array( $this, 'add_localize_params' ) );
        add_action( 'afm_enqueue_dashboard_scripts', array( $this, 'subscription_endpoint_scripts' ), 10, 4 );
        
        // Subscription Product Meta Data Save
        add_action( 'wcmp_process_product_meta_subscription', array( &$this, 'save_subscription_meta' ), 10, 2 );
        add_action( 'wcmp_process_product_meta_variable-subscription', array( &$this, 'save_variable_subscription_meta' ), 10, 2 );
    }

    /**
     * Return all the `WC Subscriptions` endpoints added to vendor dashboard
     * 
     * @return array endpoints 
     */
    private function all_subscription_endpoints() {
        return apply_filters( "wcmp_afm_{$this->plugin}_endpoint_list", afm()->dependencies->get_allowed_endpoints( $this->plugin ) );
    }
    
    public function subscription_endpoints_query_vars( $endpoints ) {
        return afm()->dependencies->plugin_endpoints_query_vars( $endpoints, $this->subscription_endpoints );
    }

    public function subscription_dashboard_navs( $navs ) {
        $parent_menu = array(
            'label'       => __( 'Subscriptions', 'woocommerce-subscriptions' ),
            'capability'  => 'wcmp_vendor_dashboard_menu_subscription_capability',
            'position'    => 32,
            'nav_icon'    => 'wcmp-font ico-subscriptions',
            'plugin'      => $this->plugin,
        );
        return afm()->dependencies->plugin_dashboard_navs( $navs, $this->subscription_endpoints, $parent_menu );
    }
    
    public function call_endpoint_contents() {
        //add endpoint content
        foreach ( $this->subscription_endpoints as $key => $endpoint ) {
            $cap = ! empty( $endpoint['vendor_can'] ) ? $endpoint['vendor_can'] : '';
            if ( $cap && current_vendor_can( $cap ) ) {
                add_action( 'wcmp_vendor_dashboard_' . $key . '_endpoint', array( $this, 'subscription_endpoints_callback' ) );
            }
        }
    }
    
    public function subscription_endpoints_callback() {
        $endpoint_name = str_replace( array( 'wcmp_vendor_dashboard_', '_endpoint' ), '', current_filter() );
        afm()->endpoints->load_class( $endpoint_name );
        $classname = 'WCMp_AFM_' . ucwords( str_replace( '-', '_', $endpoint_name ), '_' ) . '_Endpoint';
        $endpoint_class = new $classname;
        $endpoint_class->output();
    }
    
    //this will be called from the main Integration class after WCMp_AFM_Add_Product_Endpoint class constructor executed
    public function set_props( $id ) {
        $this->id = $id;
        
        //after setting id get the subscription product
        $this->subscription_product = new WC_Product_Subscription( $this->id );
    }

    public function add_localize_params( $params ) {
		$billing_period_strings = self::get_billing_period_ranges();

		$new_params = array(
			'trialPeriodSingular' => wcs_get_available_time_periods(),
			'trialPeriodPlurals' => wcs_get_available_time_periods( 'plural' ),
			'subscriptionLengths' => wcs_get_subscription_ranges(),
			'oneTimeShippingCheckNonce' => wp_create_nonce( 'one_time_shipping' ),
			'mon_decimal_point' => wc_get_price_decimal_separator(),
			'syncOptions' => array(
								'week'  => $billing_period_strings['week'],
								'month' => $billing_period_strings['month'],
							),
			'bulkEditPeriodMessage' => esc_js(__( 'Enter the new period, either day, week, month or year:', 'woocommerce-subscriptions' ) ),
			'bulkEditLengthMessage' => esc_js(__( 'Enter a new length (e.g. 5):', 'woocommerce-subscriptions' ) ),
			'bulkEditIntervalhMessage' => esc_js(__( 'Enter a new interval as a single number (e.g. to charge every 2nd month, enter 2):', 'woocommerce-subscriptions' ) ),
			'bulkDeleteOptionLabel' => esc_js(__( 'Delete all variations without a subscription', 'woocommerce-subscriptions' ) ),
			'i18n_delete_all_variations' => esc_js( __( 'Are you sure you want to delete all variations? This cannot be undone.', 'woocommerce' ) ),
			'i18n_last_warning' => esc_js( __( 'Last warning, are you sure?', 'woocommerce' ) ),
		);

        return array_merge( $params, $new_params );
    }
    
     public function subscription_endpoint_scripts( $endpoint, $frontend_script_path, $lib_path, $suffix ) {
        global $WCMp;
        switch ( $endpoint ) {
            case 'subscriptions':
                if ( current_vendor_can( 'manage_subscriptions' ) ) {
                    $WCMp->library->load_dataTable_lib();
                    wp_register_script( 'afm-subscriptions-js', $frontend_script_path . 'subscriptions.js', array( 'jquery', 'wcmp-datatable-script', 'wcmp-datatable-bs-script' ), afm()->version, true );
                }
                break;
        }
    }
    
    public function woocommerce_subscription_product_type_options( $options ) {
        global $WCMp;
        
        if ( $WCMp->vendor_caps->vendor_can( 'subscription' ) ) {
            $options['virtual']['wrapper_class'] .= ' show_if_subscription';
            $options['downloadable']['wrapper_class'] .= ' show_if_subscription';
        }
        return $options;
    }
    
    public function woocommerce_subscription_update_tabs( $product_tabs ) {
    	global $WCMp;
    	
    	if ( isset( $product_tabs['inventory']['class'] ) ) {
    		if ( $WCMp->vendor_caps->vendor_can( 'subscription' ) ) {
    			$product_tabs['inventory']['class'][] = 'show_if_subscription';
    		}
    		if ( $WCMp->vendor_caps->vendor_can( 'variable-subscription' ) ) {
    			$product_tabs['inventory']['class'][] = 'show_if_variable-subscription';
    		}
       }
       return $product_tabs;
    }
    
    /**
	 * Output the subscription specific pricing fields on the "Add Product" page.
	 */
	public static function woocommerce_variable_subscription_pricing_fields( $loop, $variation_data, $variation ) {

		// When called via Ajax
		/*if ( ! function_exists( 'woocommerce_wp_text_input' ) ) {
			require_once( WC()->plugin_path() . '/admin/post-types/writepanels/writepanels-init.php' );
		}*/

		$variation_product = wc_get_product( $variation );
		$billing_period    = WC_Subscriptions_Product::get_period( $variation_product );

		if ( empty( $billing_period ) ) {
			$billing_period = 'month';
		}

		include( WCMp_AFM_PLUGIN_DIR . 'views/products/subscription/html-variation-price.php' );

		wp_nonce_field( 'wcs_subscription_variations', '_wcsnonce_save_variations', false );

		do_action( 'wcmp_afm_variable_subscription_pricing', $loop, $variation_data, $variation );
	}
	
	public static function woocommerce_variable_subscription_synchronise_section( $loop, $variation_data, $variation ) {
		$sync_payments_enabled = get_option("woocommerce_subscriptions_sync_payments");
		if ( $sync_payments_enabled == 'yes' ) {

			// Set month as the default billing period
			$subscription_period = WC_Subscriptions_Product::get_period( $variation );

			if ( empty( $subscription_period ) ) {
				$subscription_period = 'month';
			}

			$display_week_month_select = ( ! in_array( $subscription_period, array( 'month', 'week' ) ) ) ? 'display: none;' : '';
			$display_annual_select     = ( 'year' != $subscription_period ) ? 'display: none;' : '';

			$payment_day = self::get_products_payment_day( $variation );

			// An annual sync date is already set in the form: array( 'day' => 'nn', 'month' => 'nn' ), create a MySQL string from those values (year and time are irrelvent as they are ignored)
			if ( is_array( $payment_day ) ) {
				$payment_month = $payment_day['month'];
				$payment_day   = $payment_day['day'];
			} else {
				$payment_month = gmdate( 'm' );
			}

			include( WCMp_AFM_PLUGIN_DIR . 'views/products/subscription/html-variation-synchronisation.php' );
		}
	}
	
	public function variable_subscription_bulk_edit_actions() {

		if ( WC_Subscriptions_Product::is_subscription( $this->id ) ) : ?>
			<optgroup label="<?php esc_attr_e( 'Subscription pricing', 'woocommerce-subscriptions' ); ?>">
				<option value="variable_subscription_sign_up_fee"><?php esc_html_e( 'Subscription sign-up fee', 'woocommerce-subscriptions' ); ?></option>
				<option value="variable_subscription_period_interval"><?php esc_html_e( 'Subscription billing interval', 'woocommerce-subscriptions' ); ?></option>
				<option value="variable_subscription_period"><?php esc_html_e( 'Subscription period', 'woocommerce-subscriptions' ); ?></option>
				<option value="variable_subscription_length"><?php esc_html_e( 'Expire after', 'woocommerce-subscriptions' ); ?></option>
				<option value="variable_subscription_trial_length"><?php esc_html_e( 'Free trial length', 'woocommerce-subscriptions' ); ?></option>
				<option value="variable_subscription_trial_period"><?php esc_html_e( 'Free trial period', 'woocommerce-subscriptions' ); ?></option>
			</optgroup>
		<?php endif;
	}

    public function woocommerce_subscription_recurring_calculation( $packages ) {
        $new = array();
        if( WC()->cart->recurring_carts ) {
            foreach(WC()->cart->recurring_carts as $cart) {
                foreach($cart->cart_contents as $key => $s) {
                    $product_vendors = get_wcmp_product_vendors($s['product_id']);
                }
                foreach( WC()->session->get('shipping_for_package_'.$product_vendors->id)['rates'] as $id =>$rate) {
                    if(  WC()->session->get('chosen_shipping_methods')[$product_vendors->id] == $id ) 
                        $shipping_cost = $rate->cost;
                }
                $total = $cart->get_subtotal() + $shipping_cost;
                $cart->set_total($total);
            }
            $new[$product_vendors->id] = $packages[$product_vendors->id];
            return $new;
        }
        return $packages;
    }
    
    public function woocommerce_subscription_general_tabs_content() {
		afm()->template->get_template( 'products/subscription/html-product-data-general.php', array( 'id' => $this->id, 'subscription_product' => $this->subscription_product ) );
        return;
    }
    
    public function woocommerce_subscription_shipping_tabs_content() {
		afm()->template->get_template( 'products/subscription/html-product-data-shipping.php', array( 'id' => $this->id, 'subscription_product' => $this->subscription_product ) );
        return;
    }
    
    public function woocommerce_subscription_advanced_tabs_content() {
		afm()->template->get_template( 'products/subscription/html-product-data-advanced.php', array( 'id' => $this->id, 'subscription_product' => $this->subscription_product ) );
        return;
    }
    
    public function allow_subscription_product_types( $product_types ) {
    	global $WCMp;
    	
		if ( $WCMp->vendor_caps->vendor_can( 'subscription' ) ) {
			$product_types[] = 'subscription';
		}

		return $product_types;
    }
    
    public function allow_variable_subscription_product_types( $product_types ) {
    	global $WCMp;
    	
		if ( $WCMp->vendor_caps->vendor_can( 'variable-subscription' ) ) {
			$product_types[] = 'variable-subscription';
		}

		return $product_types;
    }
    
    public function allow_both_product_types( $product_types ) {
    	global $WCMp;
    	
		if ( $WCMp->vendor_caps->vendor_can( 'subscription' ) ) {
			$product_types[] = 'subscription';
		}

		if ( $WCMp->vendor_caps->vendor_can( 'variable-subscription' ) ) {
			$product_types[] = 'variable-subscription';
		}
		return $product_types;
    }
    
    public function save_subscription_meta( $product_id, $data ) {
        if ( isset( $data['product-type'] ) && $data['product-type'] == 'subscription' ) {
        	if ( isset( $data['_subscription_period'] ) && $data['_subscription_period'] == 'year' ) {
        		$data['_subscription_payment_sync_date'] = array(
        													'day' => isset( $data['_subscription_payment_sync_date_day'] ) ? $data['_subscription_payment_sync_date_day'] : 0,
        													'month' => isset( $data['_subscription_payment_sync_date_month'] ) ? $data['_subscription_payment_sync_date_month'] : '01',
														);
			}
        	// save all data
            foreach ( $data as $key => $value ) {
                if ( substr( $key, 0, 14 ) === "_subscription_" ) {
                	update_post_meta( $product_id, $key, $value );
                }
            }
        }
    }
    
    public function save_variable_subscription_meta( $product_id, $data ) {
    	if ( isset( $data['product-type'] ) && $data['product-type'] == 'variable-subscription' ) {
        	// save all data
            foreach ( $data as $key => $value ) {
				if ( $key == "_subscription_limit" || $key == "_subscription_one_time_shipping") {
					update_post_meta( $product_id, $key, $value );
				}
			}
        }
    }

    public static function get_products_payment_day( $sync_payments_enabled, $product ) {

		if ( $sync_payments_enabled == 'yes' ) {
			$payment_date = WC_Subscriptions_Product::get_meta_data( $product, 'subscription_payment_sync_date', 0 );	
		} else {
			$payment_date = 0;
		}

		return apply_filters( 'woocommerce_subscriptions_product_sync_date', $payment_date, $product );
	}
	
	/**
	 * Return an i18n'ified associative array of all possible subscription periods.
	 *
	 * @since 1.5
	 */
	public static function get_billing_period_ranges( $billing_period = '' ) {
		global $wp_locale;

		$billing_period_ranges = array();
		
		if ( empty( $billing_period_ranges ) ) {

			foreach ( array( 'week', 'month', 'year' ) as $key ) {
				$billing_period_ranges[ $key ][0] = __( 'Do not synchronise', 'woocommerce-subscriptions' );
			}

			// Week
			$weekdays = array_merge( $wp_locale->weekday, array( $wp_locale->weekday[0] ) );
			unset( $weekdays[0] );
			foreach ( $weekdays as $i => $weekly_billing_period ) {
				// translators: placeholder is a day of the week
				$billing_period_ranges['week'][ $i ] = sprintf( __( '%s each week', 'woocommerce-subscriptions' ), $weekly_billing_period );
			}

			// Month
			foreach ( range( 1, 27 ) as $i ) {
				// translators: placeholder is a number of day with language specific suffix applied (e.g. "1st", "3rd", "5th", etc...)
				$billing_period_ranges['month'][ $i ] = sprintf( __( '%s day of the month', 'woocommerce-subscriptions' ), WC_Subscriptions::append_numeral_suffix( $i ) );
			}
			$billing_period_ranges['month'][28] = __( 'Last day of the month', 'woocommerce-subscriptions' );

			$billing_period_ranges = apply_filters( 'woocommerce_subscription_billing_period_ranges', $billing_period_ranges );
		}

		if ( empty( $billing_period ) ) {
			return $billing_period_ranges;
		} elseif ( isset( $billing_period_ranges[ $billing_period ] ) ) {
			return $billing_period_ranges[ $billing_period ];
		} else {
			return array();
		}
	}
	
	/**
	 * Returns either a string or array of strings describing the allowable trial period range
	 * for a subscription.
	 *
	 * @since 1.0
	 */
	public static function get_trial_period_validation_message( $form = 'combined' ) {

		$subscription_ranges = wcs_get_subscription_ranges();

		if ( 'combined' == $form ) {
			// translators: number of 1$: days, 2$: weeks, 3$: months, 4$: years
			$error_message = sprintf( __( 'The trial period can not exceed: %1s, %2s, %3s or %4s.', 'woocommerce-subscriptions' ), array_pop( $subscription_ranges['day'] ), array_pop( $subscription_ranges['week'] ), array_pop( $subscription_ranges['month'] ), array_pop( $subscription_ranges['year'] ) );
		} else {
			$error_message = array();
			foreach ( wcs_get_available_time_periods() as $period => $string ) {
				// translators: placeholder is a time period (e.g. "4 weeks")
				$error_message[ $period ] = sprintf( __( 'The trial period can not exceed %s.', 'woocommerce-subscriptions' ), array_pop( $subscription_ranges[ $period ] ) );
			}
		}

		return apply_filters( 'woocommerce_subscriptions_trial_period_validation_message', $error_message );
	}
	
	
/**
     * Get current vendor subscription products.
     *
     * @param string post status
     * @param string One of OBJECT, or ARRAY_N
     * @return array of object or numeric array
     */
    public static function get_vendor_subscription_products( $post_status = 'any', $output = OBJECT ) {
        global $WCMp, $wpdb;
        $vendor_id = afm()->vendor_id;
        $subscription_products = array();
        if ( $vendor_id ) {
            $vendor = get_wcmp_vendor( $vendor_id );
            if ( $vendor ) {
                $args = array(
                    'post_status' => $post_status,
                    'tax_query'   => array(
                        'relation' => 'AND',
                        array(
                            'taxonomy' => $WCMp->taxonomy->taxonomy_name,
                            'field'    => 'id',
                            'terms'    => absint( $vendor->term_id )
                        ),
                        array(
                            'taxonomy' => 'product_type',
                            'field'    => 'slug',
                            'terms'    => array( 'subscription', 'variable-subscription' ),
                            'operator' => 'IN',
                        )
                    )
                );
                $vendor_products = $vendor->get_products( $args );
        
                foreach ( $vendor_products as $vendor_product ) {
                    $product_type = WC_Product_Factory::get_product_type( $vendor_product->ID );
                    if ( $product_type === 'subscription' || $product_type === 'variable-subscription') {
                        $subscription_products[] = ( $output == OBJECT ) ? new WC_Product_Subscription( $vendor_product->ID ) : $vendor_product->ID;
                    }
                }
            }
        }
        return $subscription_products;
    }
    
	/**
     * Get current vendor subscription orders.
     *
     * @return array of subscriptions IDS
     */
    public static function get_vendor_subscription_array( $args = null ) {
        global $wpdb;
        $subscriptions_object = array();
        $products = self::get_vendor_subscription_products( 'any', ARRAY_N );
        if ( ! empty( $products ) ) {
        	return self::get_subscriptions_for_product_by_status( $products, 'ids', $args);
        }
        return $subscriptions_object;
    }
    
    
	/**
	 * Get subscriptions that contain a certain product, specified by ID.
	 *
	 * @param  int | array $product_ids Either the post ID of a product or variation or an array of product or variation IDs
	 * @param  string $fields The fields to return, either "ids" to receive only post ID's for the match subscriptions, or "subscription" to receive WC_Subscription objects
	 * @return array
	 * @since  2.0
	 */
	public static function get_subscriptions_for_product_by_status( $product_ids, $fields = 'ids', $args = array() ) {
		global $wpdb;
	
		// If we have an array of IDs, convert them to a comma separated list and sanatise them to make sure they're all integers
		if ( is_array( $product_ids ) ) {
			$ids_for_query = implode( "', '", array_map( 'absint', array_unique( array_filter( $product_ids ) ) ) );
		} else {
			$ids_for_query = absint( $product_ids );
		}
		if(isset($args['post_status']) && $args['post_status'] != 'all') $post_status_string = " AND posts.post_status = '" . $args['post_status'] . "'";
		else $post_status_string = '';
		
		$post_orderby_string = '';
		if(isset($args['orderby'])) {
			if($args['orderby'] == 'order_title') $post_orderby_string = " ORDER BY posts.ID " . $args['order'];
			if($args['orderby'] == 'start_date') $post_orderby_string = " ORDER BY posts.post_date " . $args['order'];
		} 
		
		$subscription_ids = $wpdb->get_col( "
			SELECT DISTINCT order_items.order_id FROM {$wpdb->prefix}woocommerce_order_items as order_items
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS itemmeta ON order_items.order_item_id = itemmeta.order_item_id
				LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
			WHERE posts.post_type = 'shop_subscription'" . $post_status_string . 
				" AND itemmeta.meta_value IN ( '" . $ids_for_query . "' )
				AND itemmeta.meta_key   IN ( '_variation_id', '_product_id' )" . $post_orderby_string
		);
	
		$subscriptions = array();
	
		foreach ( $subscription_ids as $post_id ) {
			$subscriptions[ $post_id ] = ( 'ids' !== $fields ) ? wcs_get_subscription( $post_id ) : $post_id;
		}
	
		return apply_filters( 'woocommerce_subscriptions_for_product', $subscriptions, $product_ids, $fields );
	}
}
