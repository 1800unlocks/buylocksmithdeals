<?php
/**
 * WCMp Advanced Frontend Manager
 *
 * Shipping Per Product Support
 *
 * @author WC Marketplace
 * @package WCMp_AFM/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Per_Product_Shipping_Integration {

    protected $id = null;
    protected $product = null;
    protected $plugin = 'per-product-shipping';

    public function __construct() {
        add_action( 'wcmp_afm_product_options_shipping', array( $this, 'per_product_shipping_content' ) );
        add_action( 'wcmp_afm_variation_options', array( $this, 'add_to_variation_options' ), 10, 3 );
        add_action( 'wcmp_afm_product_after_variable_attributes', array( $this, 'variation_shipping_content' ), 10, 3 );
        add_filter( 'wcmp_advance_product_script_params', array( $this, 'add_localize_params' ) );
        // Addon Product Meta Data Save
        add_action( 'wcmp_process_product_object', array( $this, 'pps_save' ), 20 );
    }

    //this will be called from the main Integration class after WCMp_AFM_Add_Product_Endpoint class constructor executed
    public function set_props( $id ) {
        $this->id = $id;

        //after setting id get the WC product object
        $this->product = wc_get_product( $this->id );
    }

    public function per_product_shipping_content() {
        ob_start();
        $pps_enabled = get_post_meta( $this->id, '_per_product_shipping', true );
        ?>
        <div class="form-group">
            <label class="control-label col-sm-3 col-md-3" for="_per_product_shipping"><?php esc_html_e( 'Per-product shipping', 'woocommerce-shipping-per-product' ); ?></label>
            <div class="col-md-6 col-sm-9">
                <input class="form-control" type="checkbox" id="_per_product_shipping" name="_per_product_shipping" value="yes"<?php checked( wc_bool_to_string( $pps_enabled ), 'yes' ); ?>>
                <span class="form-text"><?php esc_html_e( 'Enable per-product shipping cost', 'woocommerce-shipping-per-product' ); ?></span>
            </div>
        </div>
    <?php
        ob_end_flush();
        afm()->template->get_template( 'products/per-product-shipping/html-product-data-shipping.php', array( 'post_id' => $this->id ) );
    }

    public function add_to_variation_options( $loop, $variation_data, $variation ) {
        ob_start();
        ?>
        <div class="form-group">
            <label for="_per_variation_shipping[<?php echo esc_attr( $variation->ID ); ?>]"><input type="checkbox" class="form-control enable_per_product_shipping" id="_per_variation_shipping[<?php echo esc_attr( $variation->ID ); ?>]" name="_per_variation_shipping[<?php echo esc_attr( $variation->ID ); ?>]" <?php checked( get_post_meta( $variation->ID, '_per_product_shipping', true ), 'yes' ); ?> /> <?php esc_html_e( 'Per-variation shipping', 'woocommerce-shipping-per-product' ); ?></label>
        </div>
        <?php
        ob_end_flush();
    }
    
    public function variation_shipping_content( $loop, $variation_data, $variation ) {
        afm()->template->get_template( 'products/per-product-shipping/html-product-data-shipping.php', array( 'post_id' => $variation->ID ) );
    }

    public function add_localize_params( $params ) {
        $new_params = array(
            //'add_person_nonce'       => wp_create_nonce( 'add-person' ),
            'i18n_product_id'   => __( 'Product ID', 'woocommerce-shipping-per-product' ),
            'i18n_country_code' => __( 'Country Code', 'woocommerce-shipping-per-product' ),
            'i18n_state'        => __( 'State/County Code', 'woocommerce-shipping-per-product' ),
            'i18n_postcode'     => __( 'Zip/Postal Code', 'woocommerce-shipping-per-product' ),
            'i18n_cost'         => __( 'Cost', 'woocommerce-shipping-per-product' ),
            'i18n_item_cost'    => __( 'Item Cost', 'woocommerce-shipping-per-product' ),
        );
        return array_merge( $params, $new_params );
    }
    
    public function pps_save( $product ) {
        $post_id = $product->get_id();
		// Enabled or Disabled.
		if ( ! empty( $_POST['_per_product_shipping'] ) ) {
			update_post_meta( $post_id, '_per_product_shipping', 'yes' );
			update_post_meta( $post_id, '_per_product_shipping_add_to_all', ! empty( $_POST['_per_product_shipping_add_to_all'][ $post_id ] ) ? 'yes' : 'no' );
		} else {
			delete_post_meta( $post_id, '_per_product_shipping' );
			delete_post_meta( $post_id, '_per_product_shipping_add_to_all' );
		}

		$countries  = ! empty( $_POST['per_product_country'][ $post_id ] ) ? $_POST['per_product_country'][ $post_id ] : array();
		$states     = ! empty( $_POST['per_product_state'][ $post_id ] ) ? $_POST['per_product_state'][ $post_id ] : array();
		$postcodes  = ! empty( $_POST['per_product_postcode'][ $post_id ] ) ? $_POST['per_product_postcode'][ $post_id ] : array();
		$costs      = ! empty( $_POST['per_product_cost'][ $post_id ] ) ? $_POST['per_product_cost'][ $post_id ] : array();
		$item_costs = ! empty( $_POST['per_product_item_cost'][ $post_id ] ) ? $_POST['per_product_item_cost'][ $post_id ] : array();
		if ( ! empty( $countries ) ) {
			$data = compact( 'countries', 'states', 'postcodes', 'costs', 'item_costs' );
			$this->save_product_rules( $post_id, $data );
		}
	}
    private function save_product_rules( $product_id, $data ) {
		global $wpdb;

		$data = wp_parse_args(
			$data,
			array(
				'countries'  => array(),
				'states'     => array(),
				'postcodes'  => array(),
				'costs'      => array(),
				'item_costs' => array(),
			)
		);

		$countries  = $data['countries'];
		$states     = $data['states'];
		$postcodes  = $data['postcodes'];
		$costs      = $data['costs'];
		$item_costs = $data['item_costs'];
		$rule_order = 0;

		foreach ( $countries as $key => $value ) {
			if ( 'new' === $key ) {
				foreach ( $value as $new_key => $new_value ) {
					$has_column_with_value = (
						! empty( $countries[ $key ][ $new_key ] )
						|| ! empty( $states[ $key ][ $new_key ] )
						|| ! empty( $postcodes[ $key ][ $new_key ] )
						|| ! empty( $costs[ $key ][ $new_key ] )
						|| ! empty( $item_costs[ $key ][ $new_key ] )
					);

					if ( $has_column_with_value ) {
						$wpdb->insert(
							$wpdb->prefix . 'woocommerce_per_product_shipping_rules',
							array(
								'rule_country'   => esc_attr( $this->replace_aseterisk( $countries[ $key ][ $new_key ] ) ),
								'rule_state'     => esc_attr( $this->replace_aseterisk( $states[ $key ][ $new_key ] ) ),
								'rule_postcode'  => esc_attr( $this->replace_aseterisk( $postcodes[ $key ][ $new_key ] ) ),
								'rule_cost'      => esc_attr( $costs[ $key ][ $new_key ] ),
								'rule_item_cost' => esc_attr( $item_costs[ $key ][ $new_key ] ),
								'rule_order'     => $rule_order++,
								'product_id'     => absint( $product_id ),
							)
						);
					}
				}
			} else {
				$has_column_with_value = (
					! empty( $countries[ $key ] )
					|| ! empty( $states[ $key ] )
					|| ! empty( $postcodes[ $key ] )
					|| ! empty( $costs[ $key ] )
					|| ! empty( $item_costs[ $key ] )
				);

				if ( $has_column_with_value ) {
					$wpdb->update(
						$wpdb->prefix . 'woocommerce_per_product_shipping_rules',
						array(
							'rule_country'   => esc_attr( $this->replace_aseterisk( $countries[ $key ] ) ),
							'rule_state'     => esc_attr( $this->replace_aseterisk( $states[ $key ] ) ),
							'rule_postcode'  => esc_attr( $this->replace_aseterisk( $postcodes[ $key ] ) ),
							'rule_cost'      => esc_attr( $costs[ $key ] ),
							'rule_item_cost' => esc_attr( $item_costs[ $key ] ),
							'rule_order'     => $rule_order++,
						),
						array(
							'product_id' => absint( $product_id ),
							'rule_id'    => absint( $key ),
						)
					);
				} else {
					$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}woocommerce_per_product_shipping_rules WHERE product_id = %d AND rule_id = %s;", absint( $product_id ), absint( $key ) ) );
				}
			}
		}
	}
    public function replace_aseterisk( $rule ) {
		if ( ! empty( $rule ) && '*' === $rule ) {
			return '';
		}

		return $rule;
	}

}
