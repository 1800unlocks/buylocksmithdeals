<?php
defined('ABSPATH') || exit;

/**
 * Main BuyLockSmithDealsCustomizationAdmin Class.
 *
 * @class BuyLockSmithDealsCustomizationAdmin
 */
final class BuyLockSmithDealsCustomizationAdmin {

    protected static $_instance = null;
    public $vendor_class_obj = null;

    /**
     * provide class instance
     * @return type
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Initialize class action and filters.
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Filters and Actions are bundled.
     * @return boolean
     */
    private function init_hooks() {
        add_action('admin_init', function(){
	$pinstance = WC_Bookings_Admin::get_instance();
	remove_action( 'woocommerce_product_data_panels', array($pinstance,'booking_panels'), 10 );
}, 20);
       add_action('admin_menu', array($this, 'theme_options_panel'), 1000);
        add_action('pre_get_posts', array($this, 'target_main_conditional_product_list'), 1000, 1);
        add_filter('wp_count_posts', array($this, 'posts_where_count'), 10, 3);
        add_action('woocommerce_update_product', array($this, 'mp_sync_on_product_save'), 1000, 2);
        add_action('admin_footer', array($this, 'admin_menu_css'));
        add_action('admin_head', array($this, 'admin_global_css'));
        //   add_action( 'post_updated', array($this,'delete_wcmp_spmv_product'), 100000, 3 );

        add_filter('manage_edit-product_columns', array($this, 'misha_brand_column'), 20);
        add_action('manage_posts_custom_column', array($this, 'misha_populate_brands'));
        add_action('admin_enqueue_scripts',  array($this, 'blsd_load_custom_wp_admin_style'));
        /*******25-10-2019******/
        add_filter( 'woocommerce_product_data_tabs', array($this,'blsd_add_my_custom_product_data_tab') , 99 , 1 );
        add_action( 'woocommerce_product_data_panels', array($this,'booking_panels') );
        add_action( 'woocommerce_product_data_panels', array($this,'blsd_add_custom_product_data_fields') );
        
        add_filter( 'gettext', array($this,'wpse6096_gettext'), 10, 2 );
    }
    
   
    
    public static function admin_global_css(){
        echo '<style>'
        . 'table.bar_chart tbody td span {
color: #8a4b75;
display: inline!important;
margin-left: 1px;
}'
                . '</style>';
        
    }
    public static function wpse6096_gettext( $translation, $original )
    {
        if ( 'Google+ Profile' == $original ) {
            return 'Google Maps';
        }
        return $translation;
    }
    public static function blsd_add_my_custom_product_data_tab( $product_data_tabs ) {
       // print_r($product_data_tabs);
        if(isset($product_data_tabs['bookings_pricing'])){
            
           // $product_data_tabs['bookings_pricing']['label']='Pricing';
            //$product_data_tabs['bookings_pricing']['target']='blsd_bookings_pricing';
            //print_r($product_data_tabs['bookings_pricing']);
        }
        $product_data_tabs['custom_settings'] = array(
            'label' => __( 'Custom Settings', 'my_text_domain' ),
            'target' => 'blsd_add_custom_product_data_fields',
        );
        return $product_data_tabs;
    }
    
    public static function booking_panels(){
        global $post, $bookable_product;

		if ( empty( $bookable_product ) || $bookable_product->get_id() !== $post->ID ) {
			$bookable_product = get_wc_product_booking( $post->ID );
		}

		$restricted_meta = $bookable_product->get_restricted_days();

		for ( $i = 0; $i < 7; $i++ ) {

			if ( $restricted_meta && in_array( $i, $restricted_meta ) ) {
				$restricted_days[ $i ] = $i;
			} else {
				$restricted_days[ $i ] = false;
			}
		}
                wp_enqueue_script( 'wc_bookings_admin_js' );
                
                include WC_BOOKINGS_ABSPATH.'includes/admin/views/html-booking-resources.php';
		//include WC_BOOKINGS_ABSPATH.'includes/admin/views/html-booking-availability.php';
		include BUYLOCKSMITH_DEALS_WC_BOOKINGS_TEMPLATE_PATH.'html-booking-availability.php';
		//include 'views/html-booking-pricing.php';
                include BUYLOCKSMITH_DEALS_WC_BOOKINGS_TEMPLATE_PATH.'html-booking-pricing.php';
		include WC_BOOKINGS_ABSPATH.'includes/admin/views/html-booking-persons.php';
    }
    public static function blsd_add_custom_product_data_fields(){
        global $post;
        $post_id=$post->ID;
            $show_unserviceable_cars = get_post_meta( $post_id, 'show_unserviceable_cars', true);
            $where_need_service = get_post_meta( $post_id, 'where_need_service', true );
            $where_car_located = get_post_meta( $post_id, 'where_car_located', true );
            $have_any_working_keys = get_post_meta( $post_id, 'have_any_working_keys', true );
            $have_any_working_keys_locks = get_post_meta( $post_id, 'have_any_working_keys_locks', true );
            $when_start_car =  get_post_meta( $post_id, 'when_start_car', true );
            $is_car_currently_locked = get_post_meta( $post_id, 'is_car_currently_locked', true );
            $will_owner_authorize_service=  get_post_meta( $post_id, 'will_owner_authorize_service', true );
            $need_key_to_work=  get_post_meta( $post_id, 'need_key_to_work', true );
            $want_more_than_one_key =  get_post_meta( $post_id, 'want_more_than_one_key', true );
            $cost_to_cut_additional_key =  get_post_meta( $post_id, 'cost_to_cut_additional_key', true );
            $cost_to_program_additional_key =  get_post_meta( $post_id, 'cost_to_program_additional_key', true );
            $car_key_look_like=  get_post_meta( $post_id, 'car_key_look_like', true );
            $edge_cut_price=  get_post_meta( $post_id, 'edge_cut_price', true );
            $high_security_price=  get_post_meta( $post_id, 'high_security_price', true );
            $tibbe_price=  get_post_meta( $post_id, 'tibbe_price', true );
            $vats_price=  get_post_meta( $post_id, 'vats_price', true );
            $ask_property_type =  get_post_meta( $post_id, 'ask_property_type', true );
            $where_property_located = get_post_meta( $post_id, 'where_property_located', true );
            $quantity_of_locks = get_post_meta( $post_id, 'quantity_of_locks', true );
            $type_of_installation = get_post_meta( $post_id, 'type_of_installation', true );
            $door_and_frame_type = get_post_meta( $post_id, 'door_and_frame_type', true );
            $who_supplies_deadbolts = get_post_meta( $post_id, 'who_supplies_deadbolts', true );
            $quantity_of_locks_install = get_post_meta( $post_id, 'quantity_of_locks_install', true );
            $default_my_location_price=get_post_meta( $post_id, 'default_my_location_price', true );
            $default_miles=  get_post_meta($post_id, 'default_miles', true);
            $car_programming_fee=  get_post_meta($post_id, 'car_programming_fee', true);
            $car_vats_programming_fee=  get_post_meta($post_id, 'car_vats_programming_fee', true);
            $cost_cut_standard_house_key=  get_post_meta($post_id, 'cost_cut_standard_house_key', true);
            $extra_permile_price= get_post_meta($post_id, 'extra_permile_price', true);
            $maximum_miles= get_post_meta($post_id, 'maximum_miles', true);
            $product_car_key = get_post_meta($post_id,'product_car_key',true);
            $product_lock_rekeying = get_post_meta($post_id,'product_lock_rekeying',true);
            $default_product_car_cat_id = get_post_meta($post_id,'default_product_car_cat_id',true);
            $default_product_lock_rekeying_cat_id = get_post_meta($post_id,'default_product_lock_rekeying_cat_id',true);
            $cylinders_included = get_post_meta($post_id, 'cylinders_included', true);
            $extra_per_cylinders_included_price = get_post_meta($post_id, 'extra_per_cylinders_included_price', true);
			$deadbolt_cylinders_included = get_post_meta($post_id, 'deadbolt_cylinders_included', true);
            $extra_per_deadbolt_cylinders_included_price = get_post_meta($post_id, 'extra_per_deadbolt_cylinders_included_price', true);
            $house_keys_included = get_post_meta($post_id, 'house_keys_included', true);
            
			$file_edge_cut=get_option('file_edge_cut');
            $file_high_security=get_option('file_high_security');
            $file_tibbe= get_option('file_tibbe');
            $file_vats= get_option('file_vats');
            $target_dir_img = WP_PLUGIN_URL  .'/'.BUYLOCKSMITH_DEALS_BASE_FOLDER_NAME . "/uploads/";
        ?>
       <div id="blsd_add_custom_product_data_fields" class="panel woocommerce_options_panel">
           <h3>Custom Settings</h3>
            <div class="single_setting_custom">
               <input type="checkbox" <?php if($show_unserviceable_cars == 'yes'){ echo 'checked'; } ?> name="show_unserviceable_cars" value="1" class="setting_ask"> Show car list
            </div>
            <div class="single_setting_custom">
                <input type="number" class="default" name="car_programming_fee" id="car_programming_fee" min="0" value="<?php echo $car_programming_fee; ?>"><span>Enter default Car programming Fee</span>
                <input type="number" class="default" name="car_vats_programming_fee" id="car_vats_programming_fee" min="0" value="<?php echo $car_vats_programming_fee; ?>"><span> Enter cost to do VATS programming of each additional car key (less cutting price)</span>
            </div>
            <div class="single_setting_custom">
               <input type="checkbox" <?php if($where_car_located == 'yes'){ echo 'checked'; } ?> name="where_car_located" value="1" class="setting_ask"> Where is the vehicle located?
            </div>
            <div class="single_setting_custom">
               <input type="checkbox" <?php if($where_need_service == 'yes'){ echo 'checked'; } ?> name="where_need_service" value="1" class="setting_ask"> Where do you need service?
               </div>
            <div class="single_setting_custom">
                <input type="number" class="default" name="default_my_location_price" id="default_my_location_price" min="0" value="<?php echo $default_my_location_price; ?>"><span>Enter default price for my location</span>
                <input type="number" class="default" name="default_miles" id="default_miles" min="0" value="<?php echo $default_miles; ?>"><span>Enter default miles for giving service</span>
               <input type="number" class="default" name="extra_permile_price" id="extra_permile_price" min="0" value="<?php echo $extra_permile_price; ?>"><span>Enter default Extra miles price for giving service</span>
               <input type="number" class="default" name="maximum_miles" id="maximum_miles" min="0" value="<?php echo $maximum_miles; ?>"><span>Enter default Maximum miles for giving service</span>
           </div>
           <div class="single_setting_custom">
               <input type="checkbox" <?php if($have_any_working_keys == 'yes'){ echo 'checked'; } ?> name="have_any_working_keys" value="1" class="setting_ask"> Do you have any working keys that start the car?
           </div>
           <div class="single_setting_custom">
               <input type="checkbox" <?php if($have_any_working_keys_locks == 'yes'){ echo 'checked'; } ?> name="have_any_working_keys_locks" value="1" class="setting_ask"> Do you have any working keys for the locks?
           </div>
           <div class="single_setting_custom ">
               <input type="checkbox" <?php if($when_start_car == 'yes'){ echo 'checked'; } ?> name="when_start_car" value="1" class="setting_ask"> When you start your car?
           </div>
           <div class="single_setting_custom ">
               <input type="checkbox" <?php if($is_car_currently_locked == 'yes'){ echo 'checked'; } ?> name="is_car_currently_locked" value="1" class="setting_ask"> Is the car currently locked?
           </div>
           <div class="single_setting_custom ">
               <input type="checkbox" <?php if($will_owner_authorize_service == 'yes'){ echo 'checked'; } ?> name="will_owner_authorize_service" value="1" class="setting_ask"> Will the owner be able to authorize service?
           </div>
           <div class="single_setting_custom ">
               <input type="checkbox" <?php if($need_key_to_work == 'yes'){ echo 'checked'; } ?> name="need_key_to_work" value="1" class="setting_ask"> Do you need a key to work?
           </div>
           <div class="single_setting_custom ">
               <input type="checkbox" <?php if($want_more_than_one_key == 'yes'){ echo 'checked'; } ?> name="want_more_than_one_key" value="1" class="setting_ask"> How many car keys do you want made?
           </div> 
           <div class="single_setting_custom ">
               <!--<input type="number" class="default" name="cost_to_cut_additional_key" id="cost_to_cut_additional_key" min="0" value="<?php echo $cost_to_cut_additional_key; ?>"><span>Enter default Cost to Cut Each Additional Key</span> -->
               <input type="number" class="default" name="cost_to_program_additional_key" id="cost_to_program_additional_key" min="0" value="<?php echo $cost_to_program_additional_key; ?>"><span>Enter cost to do On-Board program of each additional car key (less cutting price)</span>
           </div>
           <div class="single_setting_custom ">
               <input type="checkbox" <?php if($car_key_look_like == 'yes'){ echo 'checked'; } ?> name="car_key_look_like" value="1" class="setting_ask"> What does your car key look like?
           </div>
           <div class="single_setting_custom ">
                <input type="number" class="default" name="edge_cut_price" id="edge_cut_price" min="0" value="<?php echo $edge_cut_price; ?>"><p>Enter default Double-Sided Car Key Cutting Price</p>
                <?php if(!empty($file_edge_cut)){ ?>
                <div class="car_key_img"><img src="<?php echo $target_dir_img.$file_edge_cut; ?>" height="50" width="50"></div>
                <?php } ?>
                <input type="number" class="default" name="high_security_price" id="high_security_price" min="0" value="<?php echo $high_security_price; ?>"><p>Enter default High-Security Car Key Cutting Price</p>
                <?php if(!empty($file_high_security)){ ?>
                <div class="car_key_img"><img src="<?php echo $target_dir_img.$file_high_security; ?>" height="50" width="50"></div>
                <?php } ?>
                <input type="number" class="default" name="tibbe_price" id="tibbe_price" min="0" value="<?php echo $tibbe_price; ?>"><p>Enter default Tibee Car Key Cutting Price</p>
                <?php if(!empty($file_tibbe)){ ?>
                <div class="car_key_img"><img src="<?php echo $target_dir_img.$file_tibbe; ?>" height="50" width="50"></div>
                <?php } ?>
                <input type="number" class="default" name="vats_price" id="vats_price" min="0" value="<?php echo $vats_price; ?>"><p>Enter default Vats Car Key Cutting Price</p>
                <?php if(!empty($file_vats)){ ?>
                <div class="car_key_img"><img src="<?php echo $target_dir_img.$file_vats; ?>" height="50" width="50"></div>
                <?php } ?>
                </div>
           
           <div class="single_setting_custom ">
               <input type="checkbox" <?php if($product_car_key == 'yes'){ echo 'checked'; } ?> name="product_car_key" value="1" class="setting_ask"> Product FAQ?
           </div>
           <div class="single_setting_custom">
                <input type="number" class="default" name="default_product_car_cat_id" id="default_product_car_cat_id" min="0" value="<?php echo $default_product_car_cat_id; ?>"><span>Enter corresponding FAQ Category ID</span>
            </div>
           <hr>
           <div class="single_setting_custom ">
               <input type="checkbox" <?php if($ask_property_type == 'yes'){ echo 'checked'; } ?> name="ask_property_type" value="1" class="setting_ask"> Ask property type?
           </div>
            <div class="single_setting_custom ">
               <input type="checkbox" <?php if($where_property_located == 'yes'){ echo 'checked'; } ?> name="where_property_located" value="1" class="setting_ask"> Where is the property located?
            </div>
           <div class="single_setting_custom ">
               <input type="checkbox" <?php if($quantity_of_locks == 'yes'){ echo 'checked'; } ?> name="quantity_of_locks" value="1" class="setting_ask">Ask Quantity of locks to rekey?
            </div>
           <div class="single_setting_custom ">
                <input type="number" class="default" name="cylinders_included" id="cylinders_included" min="0" value="<?php echo $cylinders_included; ?>"><span>Enter default cylinders included on this deal. </span>
                <input type="number" class="default" name="extra_per_cylinders_included_price" id="extra_per_cylinders_included_price" min="0" value="<?php echo $extra_per_cylinders_included_price; ?>"><span>Enter default extra per cylinders price. </span>
                <input type="number" class="default" name="cost_cut_standard_house_key" id="cost_cut_standard_house_key" min="0" value="<?php echo $cost_cut_standard_house_key; ?>"><span>Enter cost to cut additional standard house keys</span>
           </div>
		   <div class="single_setting_custom ">
               <input type="checkbox" <?php if($house_keys_included == 'yes'){ echo 'checked'; } ?> name="house_keys_included" value="1" class="setting_ask">How many house keys are included with this deal?
            </div>
           
           <hr>
            <div class="single_setting_custom ">
               <input type="checkbox" <?php if($who_supplies_deadbolts == 'yes'){ echo 'checked'; } ?> name="who_supplies_deadbolts" value="1" class="setting_ask">Choose Who Is Supplying Deadbolts
            </div>
            <div class="single_setting_custom ">
               <input type="checkbox" <?php if($type_of_installation == 'yes'){ echo 'checked'; } ?> name="type_of_installation" value="1" class="setting_ask">Choose Type of Installation
            </div>
            <div class="single_setting_custom ">
               <input type="checkbox" <?php if($door_and_frame_type == 'yes'){ echo 'checked'; } ?> name="door_and_frame_type" value="1" class="setting_ask">Choose Door & Frame Type
            </div>
            <div class="single_setting_custom ">
               <input type="checkbox" <?php if($quantity_of_locks_install == 'yes'){ echo 'checked'; } ?> name="quantity_of_locks_install" value="1" class="setting_ask">Choose Quantity Of Locks To Install
            </div>
			<div class="single_setting_custom ">
                <input type="number" class="default" name="deadbolt_cylinders_included" id="deadbolt_cylinders_included" min="0" value="<?php echo $deadbolt_cylinders_included; ?>"><span>Enter default cylinders included on this deal. </span>
                <input type="number" class="default" name="extra_per_deadbolt_cylinders_included_price" id="extra_per_deadbolt_cylinders_included_price" min="0" value="<?php echo $extra_per_deadbolt_cylinders_included_price; ?>"><span>Enter default extra per cylinders price. </span>
            </div>
			
           <!-- <div class="single_setting_custom ">
               <input type="checkbox" <?php // if($product_lock_rekeying == 'yes'){ echo 'checked'; } ?> name="product_lock_rekeying" value="1" class="setting_ask"> Product Lock Rekeying FAQ?
           </div>
          <div class="single_setting_custom">
                <input type="number" class="default" name="default_product_lock_rekeying_cat_id" id="default_product_lock_rekeying_cat_id" min="0" value="<?php // echo $default_product_lock_rekeying_cat_id; ?>"><span>Enter default Lock Rekeying FAQ Category ID</span>
            </div> -->
           <style>
               .single_setting_custom p {
                    width: 27%;
                    display: inline-block;
                    vertical-align: middle;
                }

                .car_key_img {
                    display: -webkit-inline-box;
                    vertical-align: top;
                }
               
               .car-key-img label {
                    padding-left: 150px;
                }   
               .single_setting_custom{
                   padding:10px;
               }
               .setting_ask{
                   margin-right: 5px !important;
               }
               .disabled{
                   display:none;
               }
               
               .default{
                    width: 50% !important;
                    float: none !important;
                    margin: 5px !important;
                }
            </style>
       </div>
    <?php
    }
    /*
     * To hide admin menu.
     */

    public function admin_menu_css() {
        ?>
        <script>jQuery(document).ready(function () {
                jQuery('a[href="admin.php?page=vendors"]').parent('li').remove();
            });</script>

        <?php
    }
    public static function get_booking_resources() {
		$ids       = WC_Data_Store::load( 'product-booking-resource' )->get_bookable_product_resource_ids();
		$resources = array();

		foreach ( $ids as $id ) {
			$resources[] = new WC_Product_Booking_Resource( $id );
		}
		return $resources;
	}
    
    
    function blsd_load_custom_wp_admin_style() {
//    wp_register_style('custom_wp_admin_css', plugins_url() . '/' . NUJ_API__TEXT_DOMAIN . '/assets/css/nuj4-admin-style.css', false, '1.0.0');
//    wp_enqueue_style('custom_wp_admin_css');
//
//
//    wp_register_style('custom_wp_admin_css_chart', plugins_url() . '/' . NUJ_API__TEXT_DOMAIN . '/assets/additions/chart.js-2.8.0/package/dist/Chart.css', false);
//    wp_enqueue_style('custom_wp_admin_css_chart');
//
//
//
//
//    wp_register_script('custom_wp_admin_js_chart', plugins_url() . '/' . NUJ_API__TEXT_DOMAIN . '/assets/additions/chart.js-2.8.0/package/dist/Chart.js', false);
//    wp_enqueue_script('custom_wp_admin_js_chart');
//
//
//    wp_enqueue_style('custom_wp_adminselect2_css', plugins_url() . '/' . NUJ_API__TEXT_DOMAIN . '/assets/additions/select2/select2.min.css');
//    wp_register_script('custom_wp_adminselect2_js', plugins_url() . '/' . NUJ_API__TEXT_DOMAIN . '/assets/additions/select2/select2.min.js');
//    wp_enqueue_script('custom_wp_adminselect2_js');
//
//    wp_enqueue_style('custom_wp_colorpicker_css', plugins_url() . '/' . NUJ_API__TEXT_DOMAIN . '/assets/additions/colorpicker/css/colorpicker.css');
//    wp_register_script('custom_wp_colorpicker_js', plugins_url() . '/' . NUJ_API__TEXT_DOMAIN . '/assets/additions/colorpicker/js/colorpicker.js');
//    wp_enqueue_script('custom_wp_colorpicker_js');
        
        
        
        wp_enqueue_style( 'blsd-admin-style', BUYLOCKSMITH_DEALS_ASSETS_PATH . 'css/blsd-admin-style.css' );
}



    /*
     * to rendar the custom vendor page.
     */

    public function wcmp_vendors() {
        ?>  
        <div class="wrap">
            <?php
            //do_action( "settings_page_vendors_tab_init", 'vendors' );


            if (!class_exists('WCMp_Settings_WCMp_Vendors_overrided', false)) {
                include_once 'overrided/class-wcmp-settings-vendors-overrided.php';
            }

            $this->vendor_class_obj = new WCMp_Settings_WCMp_Vendors_overrided($tab);
            $this->vendor_class_obj->settings_page_init();
            ?>

        <?php do_action('dualcube_admin_footer'); ?>
        </div>
        <?php
    }

    /*
     * Registring new submenu of wcmp;
     */

    function theme_options_panel() {
        add_submenu_page('wcmp', __('Vendors', 'dc-woocommerce-multi-vendor'), __('Vendors', 'dc-woocommerce-multi-vendor'), 'manage_woocommerce', 'locksmith-vendors', array($this, 'wcmp_vendors'));
        add_submenu_page(NULL, __('Assign Products', 'dc-woocommerce-multi-vendor'), __('Assign Products', 'dc-woocommerce-multi-vendor'), 'manage_woocommerce', 'assign-product-to-vendors', array($this, 'assign_products_to_vendors'));
        
        add_submenu_page( 'edit.php?post_type=product', 'Products Settings', 'Products Settings', 'manage_woocommerce', 'product-settings-page', array($this, 'product_settings_page_callback') );
        add_submenu_page( 'edit.php?post_type=product', 'Vendors Products', 'Vendors Products', 'manage_woocommerce', 'vendor-product-setting', array($this, 'vendor_product_settings_page_callback') );


        $notification_count = $this->blsm_get_open_dispute_count();

        add_menu_page('Dispute', sprintf('Dispute <span class="awaiting-mod">%d</span>', $notification_count), '', 'blsm_dispute', array($this, 'blsm_dispute_list'));
        add_submenu_page('blsm_dispute', 'List', 'List', 'manage_options', 'blsm-dispute-list', array($this, 'blsm_dispute_list'));
        add_submenu_page('blsm_dispute', '', '', 'manage_options', 'blsm-dispute-detail', array($this, 'blsm_dispute_detail'));
        add_submenu_page('edit.php?post_type=product', '', '', 'manage_options', 'blsm_assign_product_multiple_vendor', array($this, 'blsm_assign_product_multiple_vendor'));
    }
    function product_settings_page_callback() {
        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/admin/pages/product-settings.php';
    }
	function vendor_product_settings_page_callback(){
		include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/admin/pages/vendor-product-settings.php';
	}
    /*
     * Including submenu Assign Products html.
     */

    public function assign_products_to_vendors() {
        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/admin/pages/assign-product.php';
    }

    /*
     * Return all products of current user of by vendor id.
     * * @return Array
     */

    public static function getProductList($user_id = 0,$search ='') {
        global $wpdb;
        if ($user_id == 0) {
            global $current_user;
            $user_id = $current_user->ID;
        }

       /* get_currentuserinfo();

        $args = array(
            'author' => $user_id,
            'orderby' => 'post_date',
            'order' => 'ASC',
            'post_type' => 'product',
            'posts_per_page' => -1
        );

        return $current_user_posts = get_posts($args); */
         $where='';
    if($search != ''){
        $where .=" AND (p.post_title LIKE '%$search%')";
    }
    $where .=" AND p.post_author =".$user_id." AND p.post_type='product'";
    
    
    $per_page = BuyLockSmithDealsCustomizationAddon::get_record_limit();

    $current_page = BuyLockSmithDealsCustomizationAddon::get_current_page();
    $offset = ($current_page - 1) * $per_page;
    
    $order_limit = " order by p.post_date ASC LIMIT $offset ,$per_page";
    
    $sql="Select * From {$wpdb->prefix}posts p WHERE 1=1 $where $order_limit";
    $data= $wpdb->get_results($sql);
    
    $sql_total="Select * From {$wpdb->prefix}posts p WHERE 1=1 $where";
    $data_total= $wpdb->get_results($sql_total);
    $total=count($data_total);
     return array(
            "data" =>$data,
            "total_items" => $total,
            "total_pages" => ceil($total / $per_page),
            "per_page" => $per_page,
        );
    }

    /*
     * To alter the query result
     * * @return WP Query Object 
     */

    function target_main_conditional_product_list($query) {
        global $pagenow;
        $allow_query = 1;
        if (isset($_REQUEST['page'])) {
            if ($_REQUEST['page'] == 'blsm_assign_product_multiple_vendor') {
                $allow_query = 0;
            }
        }

        if (is_admin() && isset($_REQUEST['post_type']) && !isset($_REQUEST['dc_vendor_shop']) && $pagenow == 'edit.php' && $allow_query) {

            $query_vars = $query->query_vars;

            if ($query_vars['post_type'] == 'product' && $_REQUEST['post_type'] == 'product') {

                $query->set('meta_query', array(
                    array(
                        'key' => '_vendor_product_parent',
                        'compare' => 'NOT EXISTS'
                    )
                ));
            }
        }
        return $query;
    }

    /*
     * To change the wordpress product page total page count for super admin.
     * * @return Object 
     */

    function posts_where_count($counts, $type, $perm) {
        global $wpdb;

        // We only want to modify the counts shown in admin and depending on $perm being 'readable' 
        if (!is_admin() || 'readable' !== $perm)
            return $counts;

        // Only modify the counts if the user is not allowed to edit the posts of others
        if ($type == 'product') {
            if (isset($_REQUEST['post_type']) && $_REQUEST['post_type'] == 'product' && !isset($_REQUEST['dc_vendor_shop'])) {
                $post_type_object = get_post_type_object($type);


                $query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND ID not in (select post_id from wp_postmeta where meta_key='_vendor_product_parent') GROUP BY post_status";
                $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
                $counts = array_fill_keys(get_post_stati(), 0);

                foreach ($results as $row) {
                    $counts[$row['post_status']] = $row['num_posts'];
                }
            }
        }
        return (object) $counts;
    }

    /*
     * Syncs all vendor products when super admin updted his product detail.
     * * @return Void
     */

    function mp_sync_on_product_save($post_id,$product) {
       
    $terms = get_the_terms($post_id, 'product_type');
   $name = '';
        if (isset($terms[0]->name)) {
            $name = $terms[0]->name;
        }
      
        $product_parent_id = $post_id;
        global $wpdb;

        $product = wc_get_product($post_id);
        $post=get_post($post_id);    
        $user_id = $post->post_author;
        $user_meta=get_userdata($user_id);

        $user_roles=$user_meta->roles;
        if(!in_array("dc_vendor", $user_roles)){
       
        ///////////////////////////////////////////////
        $show_unserviceable_cars = isset( $_POST['show_unserviceable_cars'] ) ? 'yes' : 'no';
        $where_need_service = isset( $_POST['where_need_service'] ) ? 'yes' : 'no';
        $where_car_located = isset( $_POST['where_car_located'] ) ? 'yes' : 'no';
        $have_any_working_keys = isset( $_POST['have_any_working_keys'] ) ? 'yes' : 'no';
        $have_any_working_keys_locks = isset( $_POST['have_any_working_keys_locks'] ) ? 'yes' : 'no';
        $when_start_car = isset( $_POST['when_start_car'] ) ? 'yes' : 'no';
        $is_car_currently_locked = isset( $_POST['is_car_currently_locked'] ) ? 'yes' : 'no';
        $will_owner_authorize_service = isset( $_POST['will_owner_authorize_service'] ) ? 'yes' : 'no';
        $need_key_to_work = isset( $_POST['need_key_to_work'] ) ? 'yes' : 'no';
        $want_more_than_one_key = isset( $_POST['want_more_than_one_key'] ) ? 'yes' : 'no';
        $car_key_look_like = isset( $_POST['car_key_look_like'] ) ? 'yes' : 'no';
        $ask_property_type = isset( $_POST['ask_property_type'] ) ? 'yes' : 'no';
        $where_property_located = isset( $_POST['where_property_located'] ) ? 'yes' : 'no';
        $quantity_of_locks = isset( $_POST['quantity_of_locks'] ) ? 'yes' : 'no';
        $house_keys_included = isset( $_POST['house_keys_included'] ) ? 'yes' : 'no';
        $type_of_installation = isset( $_POST['type_of_installation'] ) ? 'yes' : 'no';
        $door_and_frame_type = isset( $_POST['door_and_frame_type'] ) ? 'yes' : 'no';
        $who_supplies_deadbolts = isset( $_POST['who_supplies_deadbolts'] ) ? 'yes' : 'no';
        $quantity_of_locks_install = isset( $_POST['quantity_of_locks_install'] ) ? 'yes' : 'no';
        $product_car_key = isset( $_POST['product_car_key'] ) ? 'yes' : 'no';
        $product_lock_rekeying = isset( $_POST['product_lock_rekeying'] ) ? 'yes' : 'no';
        $default_my_location_price = isset( $_POST['default_my_location_price'] ) ? $_POST['default_my_location_price'] : 0;
       
        
        
        
        update_post_meta( $post_id, 'show_unserviceable_cars', $show_unserviceable_cars );
        update_post_meta( $post_id, 'where_need_service', $where_need_service );
        update_post_meta( $post_id, 'where_car_located', $where_car_located );
        update_post_meta( $post_id, 'have_any_working_keys', $have_any_working_keys );
        update_post_meta( $post_id, 'have_any_working_keys_locks', $have_any_working_keys_locks );
        update_post_meta( $post_id, 'when_start_car', $when_start_car );
        update_post_meta( $post_id, 'is_car_currently_locked', $is_car_currently_locked );
        update_post_meta( $post_id, 'will_owner_authorize_service', $will_owner_authorize_service );
        update_post_meta( $post_id, 'need_key_to_work', $need_key_to_work );
        update_post_meta( $post_id, 'want_more_than_one_key', $want_more_than_one_key );
        update_post_meta($post_id, 'cost_to_cut_additional_key', $_POST['cost_to_cut_additional_key']);
        update_post_meta($post_id, 'cost_to_program_additional_key', $_POST['cost_to_program_additional_key']);
        update_post_meta( $post_id, 'car_key_look_like', $car_key_look_like );
        update_post_meta($post_id, 'edge_cut_price', $_POST['edge_cut_price']);
        update_post_meta($post_id, 'high_security_price', $_POST['high_security_price']);
        update_post_meta($post_id, 'tibbe_price', $_POST['tibbe_price']);
        update_post_meta($post_id, 'vats_price', $_POST['vats_price']);
        update_post_meta( $post_id, 'ask_property_type', $ask_property_type );
        update_post_meta( $post_id, 'where_property_located', $where_property_located );
        update_post_meta( $post_id, 'quantity_of_locks', $quantity_of_locks );
        update_post_meta( $post_id, 'house_keys_included', $house_keys_included );
        update_post_meta( $post_id, 'type_of_installation', $type_of_installation );
        update_post_meta( $post_id, 'door_and_frame_type', $door_and_frame_type );
        update_post_meta( $post_id, 'who_supplies_deadbolts', $who_supplies_deadbolts );
        update_post_meta( $post_id, 'quantity_of_locks_install', $quantity_of_locks_install );
        update_post_meta( $post_id, 'default_my_location_price', $default_my_location_price );
        update_post_meta($post_id, 'default_miles', $_POST['default_miles']);
        update_post_meta($post_id, 'car_programming_fee', $_POST['car_programming_fee']);
        update_post_meta($post_id, 'car_vats_programming_fee', $_POST['car_vats_programming_fee']);
        update_post_meta($post_id, 'cost_cut_standard_house_key', $_POST['cost_cut_standard_house_key']);
        update_post_meta($post_id, 'extra_permile_price', $_POST['extra_permile_price']);
        update_post_meta($post_id, 'maximum_miles', $_POST['maximum_miles']);
        
       
        
        update_post_meta($post_id, 'product_car_key', $product_car_key);
        update_post_meta($post_id, 'product_lock_rekeying', $product_lock_rekeying);
        update_post_meta($post_id, 'default_product_car_cat_id', $_POST['default_product_car_cat_id']);
        update_post_meta($post_id, 'default_product_lock_rekeying_cat_id', $_POST['default_product_lock_rekeying_cat_id']);
        $cylinders_included=(isset($_POST['cylinders_included']) && !empty($_POST['cylinders_included'])) ? $_POST['cylinders_included']:0;
        $extra_per_cylinders_included_price=(isset($_POST['extra_per_cylinders_included_price']) && !empty($_POST['extra_per_cylinders_included_price'])) ? $_POST['extra_per_cylinders_included_price']:0;
            
        update_post_meta($post_id, 'cylinders_included',$cylinders_included);
        update_post_meta($post_id, 'extra_per_cylinders_included_price', $extra_per_cylinders_included_price);
		$deadbolt_cylinders_included=(isset($_POST['deadbolt_cylinders_included']) && !empty($_POST['deadbolt_cylinders_included'])) ? $_POST['deadbolt_cylinders_included']:0;
        $extra_per_deadbolt_cylinders_included_price=(isset($_POST['extra_per_deadbolt_cylinders_included_price']) && !empty($_POST['extra_per_deadbolt_cylinders_included_price'])) ? $_POST['extra_per_deadbolt_cylinders_included_price']:0;
         update_post_meta($post_id, 'deadbolt_cylinders_included',$deadbolt_cylinders_included);
		 update_post_meta($post_id, 'extra_per_deadbolt_cylinders_included_price',$extra_per_deadbolt_cylinders_included_price);
		 
	   update_post_meta($post_id, '_wc_booking_last_block_time', $_POST['_wc_booking_last_block_time']); 
        
        $service_title='Where do you need service?';
        $product_addons_service_charge=[
                    [
                        'name'=>$service_title,
                        'title_format'=>'label',
                        'description_enable'=>0,
                        'description'=>'',
                        'type'=>'multiple_choice',
                        'display'=>'select',
                        'position'=>0,
                        'required'=>0,
                        'restrictions' => 0,
                        'restrictions_type' => 'any_text',
                        'adjust_price' => 0,
                        'price_type' => 'flat_fee',
                        'price' => '',
                        'min' => 0,
                        'max'=> 0,
                        'options' => [
                            [
                                 'label' => "Customer's Location",
                                'price' => $default_my_location_price,
                                'image' => '',
                                'price_type' => 'flat_fee',
                            ],
                            [
                                'label' => "Locksmith's Office",
                                'price' => '',
                                'image' => '',
                                'price_type' => 'flat_fee',
                            ]
                        ],
                        'wc_booking_person_qty_multiplier'=>0,
                        'wc_booking_block_qty_multiplier'=>0,                    
                    ]
                ];
        
            update_post_meta( $post_id, '_product_addons', $product_addons_service_charge );
            if($where_need_service == 'no'){
            delete_post_meta( $post_id, '_product_addons', $product_addons_service_charge );
            }
        ////////////////////////////////////////////////////////////////////////////
        }
        
        $meta = get_post_meta($post_id);
       $post_main = get_post($post_id);
        unset($meta['_sale_price']);
        unset($meta['_regular_price']);
        unset($meta['_price']);
        unset($meta['_product_addons']);
        unset($meta['default_my_location_price']);
        unset($meta['default_miles']);
        unset($meta['car_programming_fee']);
        unset($meta['car_vats_programming_fee']);
        unset($meta['cost_cut_standard_house_key']);
        unset($meta['extra_permile_price']);
        unset($meta['maximum_miles']);
        unset($meta['cylinders_included']);
        unset($meta['deadbolt_cylinders_included']);
		unset($meta['extra_per_deadbolt_cylinders_included_price']);
		unset($meta['extra_per_cylinders_included_price']);
        unset($meta['edge_cut_price']);
        unset($meta['high_security_price']);
        unset($meta['tibbe_price']);
        unset($meta['vats_price']);
        unset($meta['cost_to_cut_additional_key']);
        unset($meta['cost_to_program_additional_key']);
         
        $all_vendor_post = BuyLockSmithDealsAssignProductToVendor::get_product_by_meta($post_id);
        //print_r($all_vendor_post); 
//echo $name;
//exit;
        foreach ($all_vendor_post->posts as $val) {
            $product_id = $val->ID;
            
            $duplicate_product = wc_get_product($product_id);
            $post = get_post($product_id);
            //Update vendor product
            $data_post = array(
                'ID' => $product_id,
                'post_title' => $post_main->post_title,
                'post_content' => $post_main->post_content,
                'post_excerpt' => $post_main->post_excerpt,
                'post_author' => $val->post_author
            );

            $table_name = $wpdb->prefix . 'posts';


            $wpdb->update($table_name, $data_post, ['ID' => $product_id]);
            
            foreach ($meta as $key => $value) {
                $is_exclude = 0;
                $wp_exclude = explode('wc_', $key);
                if (isset($wp_exclude[1])) {
                    $is_exclude = 1;
                } 
				if ($is_exclude == 0 && $key != '_tax_status' && $key != '_tax_class' && $key != '_sku' && $key != '_price' && $key != '_vendor_product_parent' && $key != '_edit_lock' && $key != 'total_sales' && $key != '_product_attributes' && 
                        $key !='_product_addons' && $key !='extra_permile_price' && $key !='default_miles' && $key !='default_my_location_price' && $key !='maximum_miles' && $key !='cylinders_included' && $key!='extra_per_cylinders_included_price' && $key !='edge_cut_price' && $key !='high_security_price' && $key !='tibbe_price' && $key !='vats_price' && $key!='cost_to_cut_additional_key' && $key != 'cost_to_program_additional_key' && $key !='car_programming_fee' && $key !='car_vats_programming_fee' && $key !='cost_cut_standard_house_key'
						&& $key !='deadbolt_cylinders_included' && $key != 'extra_per_deadbolt_cylinders_included_price') {
                    update_post_meta($product_id, $key, $value[0]);
                }
            }
            
           //Update terms
            BuyLockSmithDealsAssignProductToVendor::update_category($post_main->post_title, $product, $duplicate_product, $val->post_author, 'update');

            $post_id = $product_id;
            $vendor_id = $val->post_author;
            if (is_user_wcmp_vendor($vendor_id)) {
                $vendor_term_id = get_user_meta($vendor_id, '_vendor_term_id', true);
                $term = get_term($vendor_term_id, 'dc_vendor_shop');
                if ($term) {
                    wp_delete_object_term_relationships($post_id, 'dc_vendor_shop');
                    wp_set_post_terms($post_id, $term->slug, 'dc_vendor_shop', true);
                }

                $vendor = get_wcmp_vendor_by_term($vendor_term_id);
                if (!wp_is_post_revision($post_id)) {
                    //wp_update_post(array('ID' => $post_id, 'post_author' => $vendor->id));

                    $data_post = array(
                        'post_author' => $vendor->id
                    );

                    $table_name = $wpdb->prefix . 'posts';


                    $wpdb->update($table_name, $data_post, ['ID' => $post_id]);
                }
            }
        wp_set_object_terms( $product_id, $name, 'product_type', false );
            
                }
                
    }

    function blsm_dispute_list() {
        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/admin/pages/dispute/dispute-list.php';
    }

    function blsm_dispute_detail() {
        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/admin/pages/dispute/dispute-detail.php';
    }

    function blsm_get_open_dispute_count() {
        global $wpdb;
        $table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_table_name();

        $query = "SELECT count(id) as total_open_dispute from $table_name WHERE $table_name.status = 1";
        $results_dispute_data = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);

        return $results_dispute_data[0]['total_open_dispute'];
    }

    function misha_brand_column($columns_array) {

        if (isset($columns_array['taxonomy-dc_vendor_shop'])) {
            unset($columns_array['taxonomy-dc_vendor_shop']);
        }
        if (isset($columns_array['featured'])) {
            unset($columns_array['featured']);
        }
        // I want to display Brand column just after the product name column
        return array_slice($columns_array, 0, 9, true) + array('assign' => 'Action') + array_slice($columns_array, 9, NULL, true);
    }

    function misha_populate_brands($column_name) {

        if ($column_name == 'assign') {

            echo '<a href="' . home_url() . '/wp-admin/edit.php?post_type=product&page=blsm_assign_product_multiple_vendor&product_id=' . get_the_ID() . '" class="">Click to Assign</a>';
        }
    }

    function blsm_assign_product_multiple_vendor() {

        include_once BUYLOCKSMITH_DEALS_PLUGIN_DIR . '/admin/pages/product/assign-product-to-multiple-vendor.php';
    }

}
