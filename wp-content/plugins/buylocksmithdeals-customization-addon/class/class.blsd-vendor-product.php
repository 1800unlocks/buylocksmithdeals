<?php

/**
 * Main BuyLockSmithDealsAssignProductToVendor Class.
 *
 * @class BuyLockSmithDealsAssignProductToVendor
 * 
 */
class BuyLockSmithDealsAssignProductToVendor {

    /**
     * Initialize class action and filters.
     */
    public static function init() {
        add_action('admin_init', array(__CLASS__, 'include_vendor_page'));
        add_action('save_post', array(__CLASS__, 'update_post_detail_meta'), 10, 3);
        add_action('woocommerce_update_product', array(__CLASS__, 'blsd_mp_sync_on_product_save'), 1000, 1);
        
       //  self::product_duplicate(22, 3);
    }

    /*
     * Call function for page include for vendor page assign
     */

    public static function include_vendor_page() {
        if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'vendors' && isset($_REQUEST['buylock_smith_vendor_id']) && $_REQUEST['buylock_smith_vendor_id'] != '') {
//            include_once BUYLOCKSMITH_PLUGIN_PATH . '/includes/assign-vendor-product.php';
        }
    }

    public static function product_duplicate($product_id, $vendor_id) {
       
        global $wpdb;
        $product = new WC_Product($product_id);
        
        $terms = get_the_terms($product->get_id(), 'product_type');
       
        $post_product = get_post($product_id);
        $title_parent_post = $post_product->post_title;
        $slug =  $post_product->post_name;
        
        
       
       
        $name = '';
        if (isset($terms[0]->name)) {
            $name = $terms[0]->name;
        }
        
        if ($name == 'simple' || $name=='booking') {
            
            //Check product already added
            $final_product_id = 0;
            $response = self::get_product_by_meta($product_id, $vendor_id);
            
            if (count($response->posts) == 0) {
                //Start code for product clone
                global $WCMp;
                $product_id = $product_id;
                $parent_post = get_post($product_id);
                $product = wc_get_product($product_id);
                if (!function_exists('duplicate_post_plugin_activation')) {
                    include_once( WC_ABSPATH . 'includes/admin/class-wc-admin-duplicate-product.php' );
                }
                $duplicate_product_class = new WC_Admin_Duplicate_Product();
                $duplicate_product = $duplicate_product_class->product_duplicate($product);
                
                $response = array('status' => false);
                if ($duplicate_product && is_user_wcmp_vendor($vendor_id)) {
                    $title = str_replace(" Copy", "", $parent_post->post_title);
                    self::update_category($parent_post->post_title, $product, $duplicate_product, $vendor_id);
                }
                //End
                //Ste custom meta
                update_post_meta($duplicate_product->get_id(), '_vendor_product_parent', $product_id);
                 ///////////////////////05-11-2019/////////////////////////////
                $default_my_location_price=get_post_meta( $product_id, 'default_my_location_price', true );
                $default_miles=get_post_meta( $product_id, 'default_miles', true );
                $car_programming_fee=get_post_meta( $product_id, 'car_programming_fee', true );
                $car_vats_programming_fee=get_post_meta( $product_id, 'car_vats_programming_fee', true );
                $cost_cut_standard_house_key=get_post_meta( $product_id, 'cost_cut_standard_house_key', true );
                $extra_permile_price=get_post_meta( $product_id, 'extra_permile_price', true );
                $maximum_miles=get_post_meta( $product_id, 'maximum_miles', true );
                $product_addons=get_post_meta( $product_id, '_product_addons', true );
                $cylinders_included=get_post_meta( $product_id, 'cylinders_included', true );
                $extra_per_cylinders_included_price=get_post_meta( $product_id, 'extra_per_cylinders_included_price', true );
                $deadbolt_cylinders_included=get_post_meta( $product_id, 'deadbolt_cylinders_included', true );
				$extra_per_deadbolt_cylinders_included_price=get_post_meta( $product_id, 'extra_per_deadbolt_cylinders_included_price', true );
				$edge_cut_price=get_post_meta( $product_id, 'edge_cut_price', true );
                $high_security_price=get_post_meta( $product_id, 'high_security_price', true );
                $tibbe_price=get_post_meta( $product_id, 'tibbe_price', true );
                $vats_price=get_post_meta( $product_id, 'vats_price', true );
                $cost_to_cut_additional_key=get_post_meta( $product_id, 'cost_to_cut_additional_key', true );
                $cost_to_program_additional_key=get_post_meta( $product_id, 'cost_to_program_additional_key', true );
                $wc_booking_last_block_time=get_post_meta( $product_id, '_wc_booking_last_block_time', true );
                
                update_post_meta( $duplicate_product->get_id(), 'default_my_location_price', $default_my_location_price );
                update_post_meta($duplicate_product->get_id(), 'default_miles', $default_miles);
                update_post_meta($duplicate_product->get_id(), 'car_programming_fee', $car_programming_fee);
                update_post_meta($duplicate_product->get_id(), 'car_vats_programming_fee', $car_vats_programming_fee);
                update_post_meta($duplicate_product->get_id(), 'cost_cut_standard_house_key', $cost_cut_standard_house_key);
                update_post_meta($duplicate_product->get_id(), 'extra_permile_price', $extra_permile_price);
                update_post_meta($duplicate_product->get_id(), 'maximum_miles', $maximum_miles);
                update_post_meta($duplicate_product->get_id(), '_product_addons', $product_addons);
                update_post_meta($duplicate_product->get_id(), 'cylinders_included', $cylinders_included);
                update_post_meta($duplicate_product->get_id(), 'extra_per_cylinders_included_price', $extra_per_cylinders_included_price);
                update_post_meta($duplicate_product->get_id(), 'deadbolt_cylinders_included', $deadbolt_cylinders_included);
				update_post_meta($duplicate_product->get_id(), 'extra_per_deadbolt_cylinders_included_price', $extra_per_deadbolt_cylinders_included_price);
				update_post_meta($duplicate_product->get_id(), 'edge_cut_price', $edge_cut_price);
                update_post_meta($duplicate_product->get_id(), 'high_security_price', $high_security_price);
                update_post_meta($duplicate_product->get_id(), 'tibbe_price', $tibbe_price);
                update_post_meta($duplicate_product->get_id(), 'vats_price', $vats_price);
                update_post_meta($duplicate_product->get_id(), 'cost_to_cut_additional_key', $cost_to_cut_additional_key);
                update_post_meta($duplicate_product->get_id(), 'cost_to_program_additional_key', $cost_to_program_additional_key);
                update_post_meta($duplicate_product->get_id(), '_wc_booking_last_block_time', $wc_booking_last_block_time);
                /////////////////////////////////////////////////////////
                $final_product_id = $duplicate_product->get_id();

                update_post_meta($final_product_id, 'status_vendor', 'published');
                $my_post = array();
                $my_post['ID'] = $final_product_id;
                $my_post['post_status'] = 'publish';
                $my_post['post_title'] =$title_parent_post;
                $my_post['post_name'] =self::blsd_post_name_for_permalink($final_product_id, $slug);
                wp_update_post($my_post);


                $data_post = array(
                    'post_status' => 'publish',
                    'post_title' => $parent_post->post_title,
                );


                $table_name = $wpdb->prefix . 'posts';
                $wpdb->update($table_name, $data_post, ['ID' => $final_product_id]);
            } else {
                foreach ($response->posts as $post) {
                    $my_post = array();
                    $my_post['ID'] = $post->ID;
                    $my_post['post_status'] = 'publish';
                    $my_post['post_title'] =$title_parent_post;
                    $final_product_id = $post->ID;
                    // Update the post into the database
                    wp_update_post($my_post);
                }
            }
        } else {

            //Check product already added
            $final_product_id = 0;
            $response = self::get_product_by_meta($product_id, $vendor_id);

            if (count($response->posts) == 0) {
                //Start code for product clone
                global $WCMp;
                $product_id = $product_id;
                $parent_post = get_post($product_id);
                $product = wc_get_product($product_id);
                if (!function_exists('duplicate_post_plugin_activation')) {
                    include_once( WC_ABSPATH . 'includes/admin/class-wc-admin-duplicate-product.php' );
                }
                $duplicate_product_class = new WC_Admin_Duplicate_Product();
                $duplicate_product = $duplicate_product_class->product_duplicate($product);
                $response = array('status' => false);
                if ($duplicate_product && is_user_wcmp_vendor($vendor_id)) {
                    $title = str_replace(" Copy", "", $parent_post->post_title);
                    self::update_category($parent_post->post_title, $product, $duplicate_product, $vendor_id);
                }
                //End
                //Ste custom meta
                update_post_meta($duplicate_product->get_id(), '_vendor_product_parent', $product_id);
                $final_product_id = $duplicate_product->get_id();

                update_post_meta($final_product_id, 'status_vendor', 'published');
                $my_post = array();
                $my_post['ID'] = $final_product_id;
                $my_post['post_status'] = 'publish';
                $my_post['post_title'] =$title_parent_post;
                $my_post['post_name'] =self::blsd_post_name_for_permalink($final_product_id, $slug);
                wp_update_post($my_post);
                $data_post = array(
                    'post_status' => $parent_post->post_status
                );

                $table_name = $wpdb->prefix . 'posts';
                $wpdb->update($table_name, $data_post, ['ID' => $final_product_id]);





                $product = new WC_Product_Variable($final_product_id);
                $variations = $product->get_available_variations();
                if (count($variations)) {
                    $table_name = $wpdb->prefix . 'posts';
                    foreach ($variations as $variation) {

                        $variation_id = $variation['variation_id'];
                        $data_post = array('post_author' => $vendor_id);
                        // update_post_meta($variation_id, '_vendor_product_parent','published');      
                        $wpdb->update($table_name, $data_post, ['ID' => $variation_id]);
                    }
                }
            } else {
                foreach ($response->posts as $post) {
                    $my_post = array();
                    $my_post['ID'] = $post->ID;
                    $my_post['post_status'] = 'publish';
                    $my_post['post_title'] =$title_parent_post;
                    $final_product_id = $post->ID;
                    // Update the post into the database
                    wp_update_post($my_post);
                }
            }
        }

        $post_id = $final_product_id;
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
                
                
                
                $status_data = $response->posts[0];
                
                if($status_data->post_status!='publish' || !isset($status_data->post_status)){
                BuyLockSmithDealsCustomizationEmail::blsd_email_vendor_product_assign_status($post_id, 'Published', $vendor->id);
                }
            }
        }
    }

    public static function get_product_by_meta($product_id, $vendor_id = 0) {
        if (!empty($vendor_id)) {

            $args = array(
                'post_type' => 'product',
                'author' => $vendor_id,
                'meta_query' => array(
                    array(
                        'key' => '_vendor_product_parent',
                        'value' => $product_id,
                        'compare' => '=',
                    )
                )
            );
        } else {
            $args = array(
                'post_type' => 'product',
                'meta_query' => array(
                    array(
                        'key' => '_vendor_product_parent',
                        'value' => $product_id,
                        'compare' => '=',
                    )
                )
            );
        }
        return new WP_Query($args);
    }

    public static function get_product_by_meta_and_status($product_id, $vendor_id = 0) {
        if (!empty($vendor_id)) {

            $args = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'author' => $vendor_id,
                'meta_query' => array(
                    array(
                        'key' => '_vendor_product_parent',
                        'value' => $product_id,
                        'compare' => '=',
                    )
                )
            );
        } else {
            $args = array(
                'post_type' => 'product',
                'meta_query' => array(
                    array(
                        'key' => '_vendor_product_parent',
                        'value' => $product_id,
                        'compare' => '=',
                    )
                )
            );
        }
        return new WP_Query($args);
    }
   
    public static function blsd_mp_sync_on_product_save($post_id){
       $product_addons_service_charge=array();
            
            $label_my_location=$_POST['label_my_location'];
            $price_my_location=$_POST['price_my_location'];
            $label_locksmith=$_POST['label_locksmith'];
            $price_locksmith=$_POST['price_locksmith'];
            $service_title=$_POST['service_title'];
            $mobile_locksmith=isset($_POST['mobile_locksmith'])?'yes':'no';
            
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
                    'wc_booking_person_qty_multiplier'=>0,
                    'wc_booking_block_qty_multiplier'=>0,                    
                ]
            ]; 
            if($mobile_locksmith == 'no'){
                $product_addons_service_charge[0]['options']=[
                        [
                             'label' => "Customer's Location",
                                'price' => $_POST['price_my_location'],
                                'image' => '',
                                'price_type' => 'flat_fee',
                        ],
                        [
                            'label' => "Locksmith's Office",
                            'price' => '',
                            'image' => '',
                            'price_type' => 'flat_fee',
                        ]
                    ];
            }
            else if($mobile_locksmith == 'yes'){
                $product_addons_service_charge[0]['options']=[
                        [
							'label' => "Customer's Location",
							'price' => $_POST['price_my_location'],
							'image' => '',
							'price_type' => 'flat_fee',
                        ]
                    ];
            }
            /*************Service Call Pricing**************/
            update_post_meta($post_id, '_product_addons',$product_addons_service_charge);
            update_post_meta($post_id, 'default_miles', $_POST['default_miles']);
            update_post_meta($post_id, 'extra_permile_price', $_POST['extra_permile_price']);
            update_post_meta($post_id, 'maximum_miles', $_POST['maximum_miles']);
			/***********************************************/
			
			/**************IS MOBILE LOCKSMITH*****************/
            update_post_meta($post_id, 'mobile_locksmith', $mobile_locksmith);
            update_post_meta($post_id, 'mobile_locksmith_address', $_POST['mobile_locksmith_address']);
			/*************************************************/
			
			/****************IN SHOP PRICING******************/
            update_post_meta($post_id, '_wc_booking_in_shop_pricing_cost', $_POST['_wc_booking_in_shop_pricing_cost']);
            update_post_meta($post_id, '_wc_in_shop_key_duplication_price_cost', $_POST['_wc_in_shop_key_duplication_price_cost']);
			/*************************************************/
			
			/****************DISCOUNT ON DEAL******************/
            update_post_meta($post_id, 'discount_on_deal', $_POST['discount_on_deal']);
			/*************************************************/
			
			/****************Deadbolt installation*******************/
            update_post_meta($post_id, 'customer_fresh_install_deadbolt', $_POST['customer_fresh_install_deadbolt']);
            update_post_meta($post_id, 'customer_replaced_deadbolt', $_POST['customer_replaced_deadbolt']);
            update_post_meta($post_id, 'locksmith_fresh_install_deadbolt', $_POST['locksmith_fresh_install_deadbolt']);
            update_post_meta($post_id, 'locksmith_replaced_deadbolt', $_POST['locksmith_replaced_deadbolt']);
			
			update_post_meta($post_id, 'quantity_customer_fresh_install_deadbolt', $_POST['quantity_customer_fresh_install_deadbolt']);
            update_post_meta($post_id, 'quantity_customer_replaced_deadbolt', $_POST['quantity_customer_replaced_deadbolt']);
            update_post_meta($post_id, 'quantity_locksmith_fresh_install_deadbolt', $_POST['quantity_locksmith_fresh_install_deadbolt']);
            update_post_meta($post_id, 'quantity_locksmith_replaced_deadbolt', $_POST['quantity_locksmith_replaced_deadbolt']);
			
			/* $deadbolt_cylinders_included=(isset($_POST['deadbolt_cylinders_included']) && !empty($_POST['deadbolt_cylinders_included'])) ? $_POST['deadbolt_cylinders_included']:0;
            $extra_per_deadbolt_cylinders_included_price=(isset($_POST['extra_per_deadbolt_cylinders_included_price']) && !empty($_POST['extra_per_deadbolt_cylinders_included_price'])) ? $_POST['extra_per_deadbolt_cylinders_included_price']:0;
            update_post_meta($post_id, 'deadbolt_cylinders_included', $deadbolt_cylinders_included);
            update_post_meta($post_id, 'extra_per_deadbolt_cylinders_included_price', $extra_per_deadbolt_cylinders_included_price); */
			
            /*******************************************************/
			
			/***************Lock Rekeying****************/
			$house_keys_included_deal=(isset($_POST['house_keys_included_deal']) && !empty($_POST['house_keys_included_deal'])) ? $_POST['house_keys_included_deal']:0;
            $extra_per_house_keys_included_price=(isset($_POST['extra_per_house_keys_included_price']) && !empty($_POST['extra_per_house_keys_included_price'])) ? $_POST['extra_per_house_keys_included_price']:0;
            update_post_meta($post_id, 'house_keys_included_deal', $house_keys_included_deal);
            update_post_meta($post_id, 'extra_per_house_keys_included_price', $extra_per_house_keys_included_price);
			$cylinders_included=(isset($_POST['cylinders_included']) && !empty($_POST['cylinders_included'])) ? $_POST['cylinders_included']:0;
            $extra_per_cylinders_included_price=(isset($_POST['extra_per_cylinders_included_price']) && !empty($_POST['extra_per_cylinders_included_price'])) ? $_POST['extra_per_cylinders_included_price']:0;
            update_post_meta($post_id, 'cylinders_included', $cylinders_included);
            update_post_meta($post_id, 'extra_per_cylinders_included_price', $extra_per_cylinders_included_price);
			/*******************************************/
			
			/**************Home Lockout***************/
			update_post_meta($post_id, 'quantity_of_door_locks_unlocks', $_POST['quantity_of_door_locks_unlocks']);
			update_post_meta($post_id, 'price_of_additional_door_locks_unlocks', $_POST['price_of_additional_door_locks_unlocks']);
            /****************************************/
			
			/**************Car Lockout***************/
			update_post_meta($post_id, 'quantity_of_cars_unlocks', $_POST['quantity_of_cars_unlocks']);
			update_post_meta($post_id, 'price_of_additional_car_unlocks', $_POST['price_of_additional_car_unlocks']);
            /****************************************/
			
			/***********Unserviceable cars***********/
			$unserviceable_cars=[];
            if(!empty($_POST['unserviceable_cars'])){
                $unserviceable_cars=explode(',',$_POST['unserviceable_cars']);
            }
            update_post_meta($post_id, 'unserviceable_cars', $unserviceable_cars);
			/***************************************/
			
			
			/**************Car Key Programming***************/
			$works_on_vats_car=isset($_POST['works_on_vats_car'])?'yes':'no';
            update_post_meta($post_id, 'works_on_vats_car', $works_on_vats_car);
			
			update_post_meta($post_id, 'quantity_double_sided_car_key', $_POST['quantity_double_sided_car_key']);
			update_post_meta($post_id, 'quantity_high_security_car_key', $_POST['quantity_high_security_car_key']);
			update_post_meta($post_id, 'quantity_tibbe_car_key', $_POST['quantity_tibbe_car_key']);
			update_post_meta($post_id, 'quantity_car_key_programmed', $_POST['quantity_car_key_programmed']);
			update_post_meta($post_id, 'quantity_vats_car_key_cut', $_POST['quantity_vats_car_key_cut']);
			update_post_meta($post_id, 'quantity_vats_car_key_programmed', $_POST['quantity_vats_car_key_programmed']);
			
			update_post_meta($post_id, 'car_programming_fee', $_POST['car_programming_fee']);
            update_post_meta($post_id, 'car_vats_programming_fee', $_POST['car_vats_programming_fee']);
			update_post_meta($post_id, 'additional_double_sided_car_key_price', $_POST['additional_double_sided_car_key_price']);
			update_post_meta($post_id, 'additional_high_security_car_key_price', $_POST['additional_high_security_car_key_price']);
			update_post_meta($post_id, 'additional_tibbe_car_key_price', $_POST['additional_tibbe_car_key_price']);
			update_post_meta($post_id, 'additional_car_key_programmed_price', $_POST['additional_car_key_programmed_price']);
			update_post_meta($post_id, 'additional_vats_car_key_cut_price', $_POST['additional_vats_car_key_cut_price']);
			update_post_meta($post_id, 'additional_vats_car_key_programmed_price', $_POST['additional_vats_car_key_programmed_price']);


            update_post_meta($post_id, 'inshop_double_sided_car_key_price', $_POST['inshop_double_sided_car_key_price']);
            update_post_meta($post_id, 'inshop_high_security_car_key_price', $_POST['inshop_high_security_car_key_price']);
            update_post_meta($post_id, 'inshop_tibbe_car_key_price', $_POST['inshop_tibbe_car_key_price']);
            update_post_meta($post_id, 'inshop_car_key_programmed_price', $_POST['inshop_car_key_programmed_price']);
            update_post_meta($post_id, 'inshop_vats_car_key_cut_price', $_POST['inshop_vats_car_key_cut_price']);
            update_post_meta($post_id, 'inshop_vats_car_key_programmed_price', $_POST['inshop_vats_car_key_programmed_price']);
			
			/* update_post_meta($post_id, 'edge_cut_price', $_POST['edge_cut_price']);
            update_post_meta($post_id, 'high_security_price', $_POST['high_security_price']);
            update_post_meta($post_id, 'tibbe_price', $_POST['tibbe_price']);
            update_post_meta($post_id, 'vats_price', $_POST['vats_price']); */
			
            /****************************************/
				
            update_post_meta($post_id, '_wc_booking_last_block_time', $_POST['_wc_booking_last_block_time']);
    }
    public static function update_post_detail_meta($post_id, $post, $update) {
       if (isset($_POST) && isset($post->post_type) && $post->post_type = 'product') {
           
            $product = wc_get_product($post_id);
            $meta = get_post_meta($post_id);
            
            unset($meta['_sale_price']);
            unset($meta['_regular_price']);
            unset($meta['_price']);
           
            $all_vendor_post = self::get_product_by_meta($post_id);
            //  echo '<pre>';
            //echo $post_id;
         // print_r($product_addons_service_charge); exit;
           
            foreach ($all_vendor_post->posts as $val) {
                $product_id = $val->ID;  
                $duplicate_product = wc_get_product($product_id);
                //Update vendor product
                wp_update_post(array(
                    'ID' => $product_id,
                    'post_title' => $post->post_title,
                    'post_content' => $post->post_content,
                ));
                //Update vendor meta
                foreach ($meta as $key => $value) {
                    update_post_meta($product_id, $key, $value[0]);
                }
                
                
                //Update terms
                self::update_category($post->post_title, $product, $duplicate_product, $val->post_author, 'update');
            }
            
        }
    }

    public static function update_category($parent_title, $product, $duplicate_product, $vendor_id, $type = '') {
        global $WCMp;
        $parent_product = $product->get_id();
        $title = str_replace(" Copy", "", $parent_title);
        //wp_update_post(array('ID' => $duplicate_product->get_id(), 'post_author' => $vendor_id, 'post_title' => $title, 'post_status' => 'publish'));
        wp_set_object_terms($duplicate_product->get_id(), absint(get_current_vendor()->term_id), $WCMp->taxonomy->taxonomy_name);

        // Add GTIN, if exists
        $gtin_data = wp_get_post_terms($product->get_id(), $WCMp->taxonomy->wcmp_gtin_taxonomy);
        if ($type == "update") {
//            $handle=new WC_Product_Variable($product->get_id()); 
//            echo '<pre>';
//             $variations1=$handle->get_children();
//            print_r($variations1);die;
            //For Variations
            $terms_type = get_the_terms($product->get_id(), 'product_type', true)[0];
            wp_set_object_terms($duplicate_product->get_id(), array($terms_type->term_id), 'product_type');
            //Update category
            $terms = get_the_terms($product->get_id(), 'product_cat');
            $new_array_terms = array();
            foreach ($terms as $value_term) {
                $new_array_terms[] = $value_term->term_id;
            }

            wp_set_object_terms($duplicate_product->get_id(), $new_array_terms, 'product_cat');
        }
        if ($gtin_data) {
            echo $gtin_type = isset($gtin_data[0]->term_id) ? $gtin_data[0]->term_id : '';
            die;
            wp_set_object_terms($duplicate_product->get_id(), $gtin_type, $WCMp->taxonomy->wcmp_gtin_taxonomy, true);
        }
        $gtin_code = get_post_meta($product->get_id(), '_wcmp_gtin_code', true);
        if ($gtin_code)
            update_post_meta($duplicate_product->get_id(), '_wcmp_gtin_code', $gtin_code);

        $has_wcmp_spmv_map_id = get_post_meta($product->get_id(), '_wcmp_spmv_map_id', true);
        if ($has_wcmp_spmv_map_id) {
            $data = array('product_id' => $duplicate_product->get_id(), 'product_map_id' => $has_wcmp_spmv_map_id);
            update_post_meta($duplicate_product->get_id(), '_wcmp_spmv_map_id', $has_wcmp_spmv_map_id);
            wcmp_spmv_products_map($data, 'insert');
        } else {
            $data = array('product_id' => $duplicate_product->get_id());
            $map_id = wcmp_spmv_products_map($data, 'insert');

            if ($map_id) {
                update_post_meta($duplicate_product->get_id(), '_wcmp_spmv_map_id', $map_id);
                // Enroll in SPMV parent product too 
                $data = array('product_id' => $product->get_id(), 'product_map_id' => $map_id);
                wcmp_spmv_products_map($data, 'insert');
                update_post_meta($product->get_id(), '_wcmp_spmv_map_id', $map_id);
            }
          //  update_post_meta($product->get_id(), '_wcmp_spmv_product', true);
        }
       // update_post_meta($duplicate_product->get_id(), '_wcmp_spmv_product', true);
        $duplicate_product->save();
        do_action('wcmp_create_duplicate_product', $duplicate_product);
        
        
       
        
    }

    public static function product_unassign($array_unassign, $vendor_id) {
    
        $args = array(
            'post_type' => 'product',
            'post_author' => $vendor_id,
            'meta_query' => array(
                'relation' => 'AND', 
                array(
                    'key' => '_vendor_product_parent',
                    'value' => $array_unassign,
                    'compare' => 'IN',
                )
            )
        );

        $query = new WP_Query($args);
        

        $posts = $query->posts;
        if (count($posts) > 0) {
            foreach ($posts as $post) {
                if($post->post_author==$vendor_id){
                $my_post = array();
                $my_post['ID'] = $post->ID;
                $my_post['post_status'] = 'draft';
                wp_update_post($my_post);
                  if($status_data->post_status!='draft'){
                BuyLockSmithDealsCustomizationEmail::blsd_email_vendor_product_assign_status($post->ID, 'Draft', $vendor_id);
                }
                }
            }
        }
    }
    
    
public static function blsd_post_name_for_permalink($product_id, $slug){
    
        
     return    $slug.'-'.md5($product_id.$slug);
}
}

?>