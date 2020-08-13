<?php
/**
 * WCMp Advanced Frontend Manager
 *
 * Affiliate Wordpress Support
 *
 * @author WC Marketplace
 * @package Affiliate_WP/classes/integrations
 * @since    3.0.0
 */
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Affiliate_WP_Integration {

    protected $id = null;
    protected $product = null;
    protected $plugin = 'affiliate-wp';

    public function __construct() {

      /***********************  Fetch the integration(plugin) list of affiliate in admin backend  *************************************/
      $wc_marketplace_integration = get_option( 'affwp_settings', true );

      /******************** (Affiliates->settings->integration) WC Marketplace checkbox on admin end( IN affiliate settings ) ************************/
      add_filter( 'affwp_integrations',array($this, 'wcmp_on_list_intregration_page') );

      if( is_array( $wc_marketplace_integration ) && array_key_exists( 'wc-marketplace' , $wc_marketplace_integration['integrations'] )):


        /******************** (WCMp->settings->capabilities) Give setting on admin end for capabilities to vendor *************************/
        add_filter( "settings_capabilities_product_tab_options", array( $this, 'wp_affiliate_capabilities' ) );
        
        /******************** (WCMp->settings->Payment) Give setting on admin end for capabilities to vendor *************************/
        add_filter( "settings_payment_tab_options", array( $this,'wp_affiliate_payement'));


        /**********************************  Save affiliate for admin capability section **************************************/

        add_filter( "settings_capabilities_product_tab_new_input", array( $this, 'save_affiliate_capabilities_setting' ), 99 , 2 );

        /**********************************  Save affiliate for admin payment section **************************************/
        add_filter("settings_payment_tab_new_input", array( $this, 'save_affiliate_payment_setting' ), 10 , 2);

        /************************** Enqueue javascript in endpoint page on vendor dashboard  ******************************************/
        add_action( 'afm_enqueue_dashboard_scripts', array( $this, 'affiliate_endpoint_scripts' ), 99, 4 );


        /******************** Set additional product tabs for affiliate product type *****************************/
        $this->tabs = $this->set_additional_tabs();

        /***** Get all the endpoint which is set set_supported_integrations() in this function. for refference :class-wcmp-afm-dependencies.php ********/
        $this->affiliate_endpoints = $this->all_affiliate_endpoints();

        /************************ Create affiliate tab on product for frontend   ***************************/
        add_filter( 'wcmp_product_data_tabs',array($this, 'affiliate_product_support' ));
        /******************  AffiliateWP product tab content ***************************************/
        add_action( 'wcmp_product_tabs_content',array($this, 'affiliate_product_tab_addition') , 10 , 3 );


        /********************** Save affiliates tab data on product page  *******************************/
        add_action( 'wcmp_process_product_object',array($this, 'save_affiliate_product_data'), 10 , 2 );


        /********************** Save coupon data on coupon page  *******************************/
        add_action( 'wcmp_afm_after_general_coupon_data',array($this, 'affiliate_coupon_support'), 10 , 2 );

        /********************** Save variation product data for affiliates tab data on product page  *******************************/
        add_action( 'woocommerce_save_product_variation',array($this, 'affiliate_product_variations'), 10 , 2 );

        /******************** Assign vendor on admin backend affiliate page *****************************/
        add_action( 'affwp_edit_affiliate_end',array($this, 'assign_vendor_to_backend_affiliate_page') );
        add_action( 'affwp_new_affiliate_end', array($this, 'assign_vendor_to_backend_affiliate_page') );
        
        /*************************** Create Endpoint For vendor ashboard pages ************************/
        add_filter('wcmp_vendor_dashboard_nav', array($this, 'add_tab_to_vendor_dashboard_affiliate'));


        /***************** Add assign-affiliate and manage-affiliate endpoint to dashboard   ********************************/
        add_filter('wcmp_endpoints_query_vars', array($this,'add_wcmp_endpoints_query_vars_affiliate'));
        
        /************************ Call all the endpoint template in dynamic order  **************************/
        $this->call_endpoint_contents();

        /************************  Include select2 librery for admin ***********************************/
        add_action( 'admin_enqueue_scripts',array($this, 'enqueue_select2_jquery' ));

        /**************** Display pending affiliate on to do list page  ************************************/
        add_action('after_wcmp_to_do_list',array($this, 'diaply_pending_affiliate_on_to_to_list'));


        /********************* Commisssion distribute to admin or vendor ***************************/
        add_filter('wcmp_commission_total_amount',array($this, 'affiliate_vendor_commission_distribute' ), 99 , 2 );

        /************************* Display affiliate amount on commission pange and vendor dashboard order page     **********************************/
        add_action('wcmp_admin_commission_order_totals_after_shipping',array($this, 'display_affiliate_amount' ));
        add_action('wcmp_vendor_order_totals_after_shipping',array($this, 'display_affiliate_amount' ));

        /************************* Display affiliate amount on transaction details page     **********************************/
        add_filter('wcmp_transaction_item_totals', array($this, 'display_amount_on_transaction_details' ), 10 , 2);

        /***************** Affiliate Referral column on bendor dashboard ***********************************/
        add_filter( 'wcmp_datatable_order_list_row_data',array($this, 'order_refference_affiliate' ), 10 , 2 );

        add_filter('wcmp_datatable_order_list_table_headers',array($this, 'order_refference_affiliate_header' ), 10 , 2);

        // add product variation fileds for affiliate
        add_action( 'wcmp_afm_product_after_variable_attributes', array( $this , 'wcmp_afm_product_affiliate_variation' ) , 10 , 3);

      endif;
    }


    /************************ Marge affiliate tabs with others product type ***************************/
    public function affiliate_product_support( $product_tabs ) {
        return array_merge( $product_tabs, $this->tabs );
    }

    /******************** Set additional product tabs for affiliate product type *****************************/
    protected function set_additional_tabs() {
        global $WCMp;
        $affiliate_tabs = array();

        $affiliate_tabs['AffiliateWP'] = array(
                'label'    => __( 'AffiliateWP', 'wcmp-afm' ),
                'target'   => 'affiliate_product_data',
                'class'    => array( 'hide_if_grouped' ),
                'priority' => 10,
            );
        return $affiliate_tabs;
    }

    /**
     * Return all the `Affiliate` endpoints added to vendor dashboard
     * 
     * @return array endpoints 
     */
    /***** Get all the endpoint which is set set_supported_integrations() in this function. for refference :class-wcmp-afm-dependencies.php ********/
    private function all_affiliate_endpoints() {
        $affiliate_payment_option = get_option( 'wcmp_capabilities_product_settings_name', true );
        $get_allowed_endpoint_affiliate = afm()->dependencies->get_allowed_endpoints( $this->plugin );
        if( array_key_exists( 'affiliate_cap_vendor', $affiliate_payment_option ) ){
          unset( $get_allowed_endpoint_affiliate['assign-affiliate']['menu']['cap'] );
        }

        return apply_filters( "wcmp_afm_{$this->plugin}_endpoint_list", $get_allowed_endpoint_affiliate );
    }

    /***************** Add assign-affiliate and manage-affiliate endpoint to dashboard   ********************************/
    public function add_wcmp_endpoints_query_vars_affiliate( $endpoints ) {

        return afm()->dependencies->plugin_endpoints_query_vars( $endpoints, $this->affiliate_endpoints );
    }

    /*************************** Create Endpoint For vendor dashboard pages( Endpoint name: Affiliate Management ) ************************/
    public function add_tab_to_vendor_dashboard_affiliate( $navs ) {
        $parent_menu = array(
            'label'      => __( 'Affiliate Management', 'wcmp-afm' ),
            'capability' => 'wcmp_vendor_dashboard_menu_booking_capability',
            'position'   => 70,
            'nav_icon'   => 'wcmp-afm-font ico-wp_affiliate_icon',
            'plugin'     => $this->plugin,
        );
        return afm()->dependencies->plugin_dashboard_navs( $navs, $this->affiliate_endpoints, $parent_menu );
    }


    /************************ Call all the endpoint template in dynamic order  **************************/
    public function call_endpoint_contents() {
        //add endpoint content
        foreach ( $this->affiliate_endpoints as $key => $endpoint ) {
            $cap = ! empty( $endpoint['vendor_can'] ) ? $endpoint['vendor_can'] : '';
            if ( $cap && current_vendor_can( $cap ) ) {
                add_action( 'wcmp_vendor_dashboard_' . $key . '_endpoint', array( $this, 'affiliate_endpoints_callback' ) );
            }
        }
    }

    public function affiliate_endpoints_callback() {
        $endpoint_name = str_replace( array( 'wcmp_vendor_dashboard_', '_endpoint' ), '', current_filter() );
        afm()->endpoints->load_class( $endpoint_name );
        $classname = 'WCMp_AFM_' . ucwords( str_replace( '-', '_', $endpoint_name ), '_' ) . '_Endpoint';
        $endpoint_class = new $classname;
        $endpoint_class->output();
    }

    /***************************************** Affiliate product type **********************************************/

    public function set_props( $id ) {
        $this->id = $id;
        //after setting id get the WC product object
        $this->product = wc_get_product( $this->id );
    }

    /******************  AffiliateWP product tab content ***************************************/
    public function affiliate_product_tab_addition( $self, $product_object, $post ){
        afm()->template->get_template( 'products/affiliate/html-product-data-affiliate.php', array( 'self' => $self, 'product_object' => $product_object, 'post' => $post ) );
    }
    

    /********************** Save affiliates tab data on product page  *******************************/
    public function save_affiliate_product_data( $product, $post_data ){
        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
            update_post_meta( $product->get_id() , '_affwp_woocommerce_product_rate_type', isset($post_data['_affwp_woocommerce_product_rate_type']) ? $post_data['_affwp_woocommerce_product_rate_type'] : '' );
            update_post_meta( $product->get_id() , '_affwp_woocommerce_product_rate', isset( $post_data['_affwp_woocommerce_product_rate'] ) ? sanitize_text_field($post_data['_affwp_woocommerce_product_rate']) : '' );
            update_post_meta( $product->get_id() , '_affwp_woocommerce_referrals_disabled', isset( $post_data['_affwp_woocommerce_referrals_disabled']) ? 1 : '' );
        }
    }


    /********** Coupon data *********/
    public function affiliate_coupon_support( $post_ID, $coupon ){
      $user_name    = '';
      $user_id      = '';
      $affiliate_id = get_post_meta( $post_ID, 'affwp_discount_affiliate', true );
      if( $affiliate_id ) {
        $user_id      = affwp_get_affiliate_user_id( $affiliate_id );
        $user         = get_userdata( $user_id );
        $user_name    = $user ? $user->user_login : '';
      }
      ?>
      <div class="form-group">
        <label class="control-label col-sm-3 col-md-3" for="user_name"><?php esc_html_e( 'Affiliate Discount?', 'affiliate-wp' ); ?></label>
        <div class="col-md-6 col-sm-9">
          <input id="user_name" name="user_name" value="<?php echo esc_attr( $user_name ); ?>" type="text" class="form-control">
        </div>
      </div>
      <?php
    }

    /******************** (Affiliates->settings->integration) WC Marketplace checkbox on admin end( IN affiliate settings ) ************************/
    public function wcmp_on_list_intregration_page( $intregration ){
      $intregration['wc-marketplace'] = 'WC Marketplace';
      return $intregration;
    }

    /********************** Save variation product data for affiliates tab data on product page  *******************************/
    public function affiliate_product_variations( $variation_id, $i ){
      update_post_meta( $variation_id , '_affwp_woocommerce_product_rate_type', isset($_POST['_affwp_woocommerce_variation_rate_types'][$variation_id]) ? $_POST['_affwp_woocommerce_variation_rate_types'][$variation_id] : '' );
      
      update_post_meta( $variation_id , '_affwp_woocommerce_referrals_disabled', isset($_POST['_affwp_woocommerce_variation_referrals_disabled'][$variation_id]) ? 1 : '' );

      update_post_meta( $variation_id , '_affwp_woocommerce_product_rate', isset($_POST['_affwp_woocommerce_variation_rates'][$variation_id]) ? $_POST['_affwp_woocommerce_variation_rates'][$variation_id] : '' );
    }



    /******************** Assign vendor on admin backend affiliate page *****************************/
    public function assign_vendor_to_backend_affiliate_page(){
      $affiliate_vendor  = get_wcmp_vendors(); 
      if( $_GET['action'] == 'edit_affiliate' ){
        $affiliate = affwp_get_affiliate( absint( $_GET['affiliate_id'] ) );
        $vendor_assign = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'affiliate_assign_vendor', true );
      } else {
        $vendor_assign = array();
      }
      ?>
      <tr class="form-row" id="affwp-welcome-email-row">

        <th scope="row">
        <label for="welcome_email1"><?php _e( 'Assign Vendor', 'wcmp-afm' ); ?></label>
        </th>
        <td>
          <label class="description1">
              <select multiple = "multiple" data-placeholder = "<?php esc_attr_e( 'Select Vendor', 'wcmp-afm' ); ?>" class = "multiselect form-control vendor-affiliate" name = "affiliate_assign_vendor[]">
                    <?php
                    foreach ( $affiliate_vendor as $term_id => $term_name ) {
                        echo '<option value="' . $term_name->id . '" '.
                        selected( in_array( $term_name->id, $vendor_assign ), true, false )
                        .' >' . $term_name->user_data->data->user_email . '</option>';
                    }
                    ?>
                </select>
            <?php _e( 'Assign vendor to this perticular affiliate', 'affiliate-wp' ); ?>
          </label>
        </td>
      </tr>
      <?php
    }

    
    


    /******************** (WCMp->settings->capabilities) Give setting on admin end for capabilities to vendor *************************/
    function wp_affiliate_capabilities( $data ){
      $data['sections']['affiliate_area'] = array( "title"  => __( 'Affiliate', 'wcmp-afm' ), // Section one
        "fields" => apply_filters( "wcmp_vendor_affiliate_capability_options", array(
            "affiliate_cap_vendor"      => array( 'title' => __( 'Enable Request Affiliate', 'wcmp-afm' ), 'type' => 'checkbox', 'id' => 'affiliate_cap_vendor', 'label_for' => 'affiliate_cap_vendor', 'name' => 'affiliate_cap_vendor', 'value' => 'Enable' ), // Checkbox
            )
        )
      );
      return $data;
    }

    /******************** (WCMp->settings->Payment) Give setting on admin end for capabilities to vendor *************************/
    function wp_affiliate_payement( $settings_tab_options ){
        $settings_tab_options['sections']['what_to_pay_section']['fields']['wcmp_affiliate_payment_cap']  = array('title' => __('Who Will Bear the Affiliate charge', 'wcmp-afm'), 'type' => 'radio', 'id' => 'wcmp_affiliate_payment_cap', 'label_for' => 'wcmp_affiliate_payment_cap', 'name' => 'wcmp_affiliate_payment_cap','dfvalue' => 'vendor', 'options' => array('admin' => __('Admin', 'wcmp-afm'), 'vendor' => __('Vendor ', 'wcmp-afm'))
      );                                                                                                
      return $settings_tab_options;
    }


    /**********************************  Save affiliate for admin capability section **************************************/
    function save_affiliate_capabilities_setting( $new_input, $input ){
      if ( isset( $input['affiliate_cap_vendor'] ) ) {
          $new_input['affiliate_cap_vendor'] = sanitize_text_field( $input['affiliate_cap_vendor'] );
        }
      return $new_input;
    }

    /**********************************  Save affiliate for admin payment section **************************************/
    function save_affiliate_payment_setting( $new_input, $input ){
      if(isset($input['wcmp_affiliate_payment_cap'])){
        $new_input['wcmp_affiliate_payment_cap'] = sanitize_text_field($input['wcmp_affiliate_payment_cap']);
      }
      return $new_input;
    }



    /**********************************  Save affiliate **************************************/
    function save_affiliate_setting( $new_input, $input ){
      if (isset($input['affiliate_capabilities_to_vendor'])) {
        $new_input['affiliate_capabilities_to_vendor'] = sanitize_text_field($input['affiliate_capabilities_to_vendor']);
      }
      if (isset($input['commission_shared_affiliate'])) {
        $new_input['commission_shared_affiliate'] = sanitize_text_field($input['commission_shared_affiliate']);
      }
      return $new_input;
    }

    /************************** Enqueue javascript in endpoint page on vendor dashboard  ******************************************/
    function affiliate_endpoint_scripts( $endpoint, $frontend_script_path, $lib_path, $suffix ){
      global $WCMp;
      switch ( $endpoint ) {
        case 'manage-affiliate':
        wp_register_script( 'afm-manage-affiliate-js', $frontend_script_path . 'manage-affiliate.js', array( 'jquery' ), afm()->version, true );
        wp_enqueue_script( 'afm-manage-affiliate-js' );

        wp_localize_script('afm-manage-affiliate-js', 'afm_manage_affiliate_js', array(
            'remove_affiliate' => __( 'You have successfully removed from this affiliate', 'wcmp-afm' ),
            'ajax_url' => admin_url('admin-ajax.php'),
        ));

        break;
        case 'assign-affiliate':
        wp_register_script( 'afm-assign-affiliate-js', $frontend_script_path . 'assign-affiliate.js', array( 'jquery' ), afm()->version, true );
        wp_enqueue_script( 'afm-assign-affiliate-js' );

        wp_localize_script('afm-assign-affiliate-js', 'afm_assign_affiliate_js', array(
            'email_empty' => __( 'Email fields is empty', 'wcmp-afm' ),
            'already_assign' => __( 'You have already assign with this affiliate' , 'wcmp-afm' ),
            'success_apply' => __( 'You have succesfully applied for affiliate' , 'wcmp-afm' ),
            'ajax_url' => admin_url('admin-ajax.php'),
        ));


        break;
      }

    }

    /************************  Include select2 librery for admin ***********************************/
    public function enqueue_select2_jquery() {
        wp_register_style( 'select2css', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.css', false, '1.0', 'all' );
        wp_register_script( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.js', array( 'jquery' ), '1.0', true );
        wp_enqueue_style( 'select2css' );
        wp_enqueue_script( 'select2' );
        
        /************************  To do list js ****************************************/
        wp_register_script( 'afm-affiliate-to-do-list-js', WCMp_AFM_PLUGIN_URL . 'assets/admin/js/affiliate-to-do-list.js', array( 'jquery' ), afm()->version, true );
        wp_enqueue_script( 'afm-affiliate-to-do-list-js' );
    }


    /**************** Display pending affiliate on to do list page  ************************************/
    public function diaply_pending_affiliate_on_to_to_list(){

      global $wpdb;
      $results = $wpdb->get_results( "SELECT affiliate_id FROM {$wpdb->prefix}affiliate_wp_affiliates" );
      foreach ($results as $key => $value) {
        if( affwp_get_affiliate_status( $value->affiliate_id )  == 'pending' ){
          $pending_affiliate[] = $value->affiliate_id;
        }
      }
      if( !empty( $pending_affiliate ) ):
      ?>
        <table class="form-table" id="to_do_list">
           <tbody>
              <tr>
                 <?php
                    $table_headers = apply_filters('wcmp_affiliate_request_vendor_table_headers', array(
                      'affiliate_id' => __('Affiliate ID', 'wcmp-afm'),
                      'status' => __('Status', 'wcmp-afm'),
                      'accept' => __('Accept', 'wcmp-afm')
                      ));
                    if ($table_headers) :
                      foreach ($table_headers as $key => $label) {
                        ?>
                 <th><?php echo $label; ?> </th>
                 <?php
                    }
                    endif;
                    ?>
              </tr>
              <?php
              foreach ($pending_affiliate as $affiliate_key => $affiliate_value) {
              ?>
              <tr>
                <?php
                if ($table_headers) :
                  foreach ($table_headers as $key => $label) {
                    switch ($key) {
                      case 'affiliate_id':
                        ?>
                        <td class="vendor column-coupon"><a href="admin.php?page=affiliate-wp-affiliates&action=edit_affiliate&affiliate_id=<?php echo $affiliate_value; ?>&amp;wp_http_referer=%2Fwordpress%2Fdc_vendor%2Fwp-admin%2Fusers.php%3Frole%3Ddc_vendor" target="_blank"><?php echo $affiliate_value; ?></a></td>
                        <?php break;
                      case 'status':
                        ?>
                        <td class="commission column-coupon"><?php echo affwp_get_affiliate_status($affiliate_value); ?></td>
                        <?php break;
                      case 'accept':
                        ?>
                        <td class="edit"><input type="button" data-affiliate = <?php echo $affiliate_value; ?> class="vendor_affiliate_edit_button" value="Edit" /> </a> </td>
                        <?php break;
                      default:
                      break;
                    }
                  }
                endif;
                ?>   
              </tr>
              <?php } ?>
           </tbody>
        </table>

      <?php endif;

    }


   
    /********************* Commisssion distribute to admin or vendor ***************************/
    public function affiliate_vendor_commission_distribute( $commission_total, $commission_id ) {
      $backend_setting_wcmp = get_option( 'wcmp_payment_settings_name', true );
      if( array_key_exists( 'wcmp_affiliate_payment_cap', $backend_setting_wcmp ) && $backend_setting_wcmp['wcmp_affiliate_payment_cap'] == 'vendor' ) {

        $suborder_id = get_post_meta( $commission_id, '_commission_order_id', true );
        $parent_order_id = wp_get_post_parent_id( $suborder_id );
        $refference = affiliate_wp()->referrals->get_by( 'reference', $parent_order_id, 'woocommerce' );
        $referral  = affwp_get_referral( $refference->referral_id );
        $referral_products = array();
        foreach ($referral->products as $key => $value) {
          $referral_products[$value['id']] = $value['referral_amount'];
        }

        $suborder_products = array();  
        $sub_order = wc_get_order( $suborder_id ); 
        foreach ($sub_order->get_items() as $item_id => $item_data) {

          $product = $item_data->get_product();
          $product_id = $product->get_id(); 
          $suborder_products[] = $product_id;
        }

        $affiliate_charge = 0;
        foreach ($referral_products as $key => $value) {
          if( in_array($key, $suborder_products) ){
            $affiliate_charge += $value;
          }
        }

        $commission_total = $commission_total - $affiliate_charge;
        return $commission_total;
      } else {
        return $commission_total;        
      }
    }

    /************************* Display affiliate amount on commission pange and vendor dashboard order page     **********************************/
    public function display_affiliate_amount( $order_id ){
      $order = wc_get_order( $order_id );
      $refference = affiliate_wp()->referrals->get_by( 'reference', wp_get_post_parent_id($order_id), 'woocommerce' );
      if( $refference ):
        $referral  = affwp_get_referral( $refference->referral_id );
        $referral_products = array();
        foreach ($referral->products as $key => $value) {
          $referral_products[$value['id']] = $value['referral_amount'];
        }

        $suborder_products = array();  
        $sub_order = wc_get_order( $order_id ); 
        foreach ($sub_order->get_items() as $item_id => $item_data) {

          $product = $item_data->get_product();
          $product_id = $product->get_id(); 
          $suborder_products[] = $product_id;
        }

        $affiliate_charge = 0;
        foreach ($referral_products as $key => $value) {
          if( in_array($key, $suborder_products) ){
            $affiliate_charge += $value;
          }
        }

        if ($affiliate_charge) : ?>
          <tr>
              <td class="label"><?php esc_html_e('Affiliate:', 'dc-woocommerce-multi-vendor'); ?></td>
              <td width="1%"></td>
              <td class="total">
                  <?php 
                  echo "-";
                      echo wc_price($affiliate_charge, array('currency' => $order->get_currency())); // WPCS: XSS ok.
                  ?>
              </td>
          </tr>
        <?php endif;
      endif;
    }
    
    /************************* Display affiliate amount on transaction details page     **********************************/
    public function display_amount_on_transaction_details( $item_totals, $transaction_id ){
      
      $commission_id = get_post_meta( $transaction_id, 'commission_detail', true );
      $suborder_id = get_post_meta( $commission_id[0], '_commission_order_id', true );
      $refference = affiliate_wp()->referrals->get_by( 'reference', wp_get_post_parent_id( $suborder_id ), 'woocommerce' );
      if( $refference ):
        $referral  = affwp_get_referral( $refference->referral_id );
        $referral_products = array();
        foreach ($referral->products as $key => $value) {
          $referral_products[$value['id']] = $value['referral_amount'];
        }

        $suborder_products = array();  
        $sub_order = wc_get_order( $suborder_id ); 
        foreach ($sub_order->get_items() as $item_id => $item_data) {

          $product = $item_data->get_product();
          $product_id = $product->get_id(); 
          $suborder_products[] = $product_id;
        }

        $affiliate_charge = 0;
        foreach ($referral_products as $key => $value) {
          if( in_array($key, $suborder_products) ){
            $affiliate_charge += $value;
          }
        }

        if ($affiliate_charge) {
          $item_totals['affiliate'] = array('label' => __('Affiliate', 'dc-woocommerce-multi-vendor'), 'value' => wc_price($affiliate_charge));
        }
      endif;
      return $item_totals;
    }

    /************************ Affiliate Referral column on bendor dashboard ***********************************/
    public function order_refference_affiliate( $column, $order ){
      $parent_order_id = wp_get_post_parent_id( $order->get_id() );
      $refference = affiliate_wp()->referrals->get_by( 'reference', $parent_order_id, 'woocommerce' );
      if( $refference ){
        $column['refference'] = $refference->referral_id;
      } else {
        $column['refference'] = __( 'No Affiliate Added', 'dc-woocommerce-multi-vendor' );
      }
      return $column;
    }

    public function order_refference_affiliate_header( $header, $user_id ){
      $header['refference'] = array('label' => __( 'Affiliate Referral', 'dc-woocommerce-multi-vendor' ));
      return $header;
    }

    public function wcmp_afm_product_affiliate_variation( $loop, $variation_data, $variation ){
        ?>
        <!--*************************** Add product variations for affiliates **************************-->
        <?php
            $rate     = get_post_meta( $variation->ID, '_affwp_woocommerce_product_rate', true );
            $rate_type = get_post_meta( $variation->ID, '_affwp_woocommerce_product_rate_type', true );
            $disabled  = get_post_meta( $variation->ID, '_affwp_woocommerce_referrals_disabled', true );
        ?>
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label col-md-12" for="_affwp_woocommerce_variation_rate_types[<?php echo $variation->ID; ?>]"><?php echo __( 'Referral Rate Type', 'affiliate-wp' ); ?></label>
                <select name="_affwp_woocommerce_variation_rate_types[<?php echo $variation->ID; ?>]" id="_affwp_woocommerce_variation_rate_types[<?php echo $variation->ID; ?>]">
                    <option value=""><?php _e( 'Site Default', 'affiliate-wp' ); ?></option>
                    <?php foreach( affwp_get_affiliate_rate_types() as $key => $type ) : ?>
                        <option value="<?php echo esc_attr( $key ); ?>"<?php selected( $rate_type, $key ); ?>><?php echo esc_html( $type ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label for="_affwp_woocommerce_variation_rates[<?php echo $variation->ID; ?>]"><?php echo __( 'Referral Rate', 'affiliate-wp' ); ?></label>
                <input type="text" name="_affwp_woocommerce_variation_rates[<?php echo $variation->ID; ?>]" value="<?php echo esc_attr( $rate ); ?>" class="wc_input_price" id="_affwp_woocommerce_variation_rates[<?php echo $variation->ID; ?>]" placeholder="<?php esc_attr_e( 'Referral rate (optional)', 'affiliate-wp' ); ?>" />
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label for="_affwp_woocommerce_variation_referrals_disabled[<?php echo $variation->ID; ?>]">
                    <input type="checkbox" class="checkbox" name="_affwp_woocommerce_variation_referrals_disabled[<?php echo $variation->ID; ?>]" id="_affwp_woocommerce_variation_referrals_disabled[<?php echo $variation->ID; ?>]" <?php checked( $disabled, true ); ?> /> <?php _e( 'Disable referrals for this product variation', 'affiliate-wp' ); ?>
                </label>
            </div>
        </div>
        <?php
    }

}

