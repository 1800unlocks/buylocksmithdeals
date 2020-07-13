<?php

class BLSDRestApi {

    public static $restApiBase = 'blsd';

    public static function init() {

        add_action('rest_api_init', array(__CLASS__, 'register_rest_route'));
    }

    public static function register_rest_route() {
        register_rest_route(self::$restApiBase, '/get_preview', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'get_preview_html'),
        ));
        register_rest_route(self::$restApiBase, '/get_products', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'get_products_html'),
        ));
    }

    public static function get_products_html(WP_REST_Request $request) {
        global $wpdb;
        $request_params = self::parse_request_params($request);
        
        $html = '';
        if (isset($request_params['api_details'])) {
            $api_details = trim($request_params['api_details']);
            $unique_id = trim($request_params['id']);
            $layout = isset($request_params['layout'])?$request_params['layout']:1;
            $sort = isset($request_params['sort'])?$request_params['sort']:'title';
            $records = isset($request_params['records'])?$request_params['records']:10;
            $see_more_deals = isset($request_params['see_more_deals'])?$request_params['see_more_deals']:0;
            $vendor_url = isset($request_params['vendor_url'])?$request_params['vendor_url']:'';
            $category = isset($request_params['category'])?$request_params['category']:'';
            $deal = isset($request_params['deal'])?$request_params['deal']:'';
            
            
             $table_name  = BuyLockSmithDealsCustomizationAddon::blsd_api_credentials_table_name();
   
      $query = "SELECT vendor_id FROM $table_name where api_key='$api_details'";
                $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
            
        if(count($results)){ 
            $table_name= BuyLockSmithDealsCustomizationAddon::blsd_deals_custom_design_name();
            $sql = "SELECT * from $table_name WHERE unique_id='$unique_id'";
            $result_array = $wpdb->get_row($sql,ARRAY_A);
            $style_params=[];
            if(!empty($result_array)){
                $style_params= unserialize($result_array['style_parameter']);
            }
            $vendor_id = $results[0]['vendor_id'];
            
            if($sort=='title'){$sort = 'post_title';}
            else if($sort=='price'){$sort = 'price';}
            else if($sort=='date'){$sort = 'post_date';}
            else{
               $sort = 'post_title';
            }
            
            if($sort!='price'){
            $args = array('post_type' => 'product',
                'author' => $vendor_id,
                    'posts_per_page' => $records,
                    'orderby' => $sort,
                    'order' => 'ASC',
                'meta_query'     => array(
        'relation' => 'AND',
        array(
            'key'     => '_vendor_product_parent',
            'compare' => 'EXISTS',
        ),
        array(
            'key'     => 'status_vendor',
            'compare' => '!=',
            'value' => 'draft',
        ),
    )
          
                );
            }else{
              $args = array('post_type' => 'product',
                  'author' => $vendor_id,
                    'posts_per_page' => $records,
                    'orderby' => 'meta_value',
                    //"meta_key" => '_regular_price',
                    //"meta_key" => '_wc_booking_cost',
                    'order' => 'ASC',
                      'meta_query'     => array(
        'relation' => 'AND',
        array(
            'key'     => '_vendor_product_parent',
            'compare' => 'EXISTS',
        ),
        array(
            'key'     => 'status_vendor',
            'compare' => '!=',
            'value' => 'draft',
        ),
    )
          
                );  
                
                
            }
            
            if($deal!=''){
                $args['post__in'] = array($deal);
            }
            
              if($category!=''){
                $args['tax_query'] =  array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => array($category),
                'operator' => 'IN',
            )
         );
            }
          
            $products = get_posts($args);
       
            //  $html =$products;
//            echo 'Last query: '.var_export($wpdb->last_query, TRUE);
//            echo count($products);
//            print_r($products); exit;
            if (!empty($products)) {
                
               if(!empty($style_params)){
                    $btn_colors=(isset($style_params['btn_colors']) && !empty($style_params['btn_colors']))?$style_params['btn_colors']:'#000';
                    $btn_text_color=(isset($style_params['btn_text_color']) && !empty($style_params['btn_text_color']))?$style_params['btn_text_color']:'#fff';
                    $label_text_color=(isset($style_params['text_color']) && !empty($style_params['text_color']))?$style_params['text_color']:'#000';
                    $border_color=(isset($style_params['border_color']) && !empty($style_params['border_color']))?$style_params['border_color']:'#efefef';
                    $price_color=(isset($style_params['price_color']) && !empty($style_params['price_color']))?$style_params['price_color']:'#000';
                
                    $html .= '<style>.blsd_column {display: flex;flex-wrap: wrap;}
                            .blsd_column2 .single-product {flex: 1 1 45%;max-width: 45%;}
                            .blsd_column3 .single-product {flex: 1 1 29%;max-width: 29%;}
                            .blsd_column .single-product img {margin: 0 auto;}
                            .product-right-part {text-align: center;}
                            .blsd_column .single-product{width: 100%;margin: 15px;padding: 0 0 15px 0;box-shadow: 0 0 3px '.$border_color.';}
                            .blsd_column .single-product .product-right-part label {font-size: 18px;margin: 5px 0 !important;display: block;font-weight: 600; color:'.$label_text_color.'}
                            .blsd_column .single-product .product-right-part .product-price {font-size: 16px;margin: 5px 0; color:'.$price_color.'}
                            .blsd_column .single-product .product-right-part .product-add-cart a.button-primary {background: '.$btn_colors.';color: '.$btn_text_color.';padding: 4px 10px;text-decoration: none;}
                            .blsd_column .single-product img {width: 100%;}
                            .see-more-deals { text-align: right; margin-right: 26px; }
                            .see-more-deals button#see_more_deals { background: '.$btn_colors.'; color: '.$btn_text_color.'; }
                            </style>';
                }
                else{
                $html .= '<style>.blsd_column {display: flex;flex-wrap: wrap;}
                            .blsd_column2 .single-product {flex: 1 1 45%;max-width: 45%;}
                            .blsd_column3 .single-product {flex: 1 1 29%;max-width: 29%;}
                            .blsd_column .single-product img {margin: 0 auto;}
                            .product-right-part {text-align: center;}
                            .blsd_column .single-product{width: 100%;margin: 15px;padding: 0 0 15px 0;box-shadow: 0 0 3px #efefef;}
                            .blsd_column .single-product .product-right-part label {font-size: 18px;margin: 5px 0 !important;display: block;font-weight: 600;}
                            .blsd_column .single-product .product-right-part .product-price {font-size: 16px;margin: 5px 0;}
                            .blsd_column .single-product .product-right-part .product-add-cart a.button-primary {background: #000;color: #fff;padding: 4px 10px;text-decoration: none;}
                            .blsd_column .single-product img {width: 100%;}
                            .see-more-deals { text-align: right; margin-right: 26px; }
                            .see-more-deals button#see_more_deals { background: #000; color: #fff; }
                            </style>';
                }
                            $layout_column = '';
                            $layout_column = $request_params['layout']; 
                            if($layout_column==''){
                                $layout_column = 3;
                            }

                $html .= '<div class="main-product-div blsd_column blsd_column'.$layout_column.'">';
                foreach ($products as $product) {
                  
                    $handle = new WC_Product_Variable($product->ID);
                    if (count($handle->get_attributes()) == 0) {//Condition for skip variation products
                        $images='';
                        $image = wp_get_attachment_image_src(get_post_thumbnail_id($product->ID), 'thumbnail');
                        $images = $image[0];
                        if($images==''){
                            $images = wc_placeholder_img_src('thumbnail'); 
                        }
                        $currency = get_woocommerce_currency_symbol();
                        $price = get_post_meta($product->ID, '_price', true);
                        $html .= '<div class="single-product ">';
                        $html .= '<div class="product-left-part">'
                                . '<div class="product-image"><img src="' . $images . '" data-id="' . $product->ID . '"></div>'
                                . '</div>';
                        $html .= '<div class="product-right-part">'
                                . '<div class="product-title"><label>' . $product->post_title . '</label></div>';
                        $terms = get_the_terms($product->ID, 'product_type');
   $name = '';
        if (isset($terms[0]->name)) {
            $name = $terms[0]->name;
        }
        
        if($name=='booking'){
          $price =    (int)get_post_meta($product->ID,'_wc_booking_cost',true); 
        if($price!=''){
         $price =    $price+((int)get_post_meta($product->ID,'_wc_booking_block_cost',true));
        }else{
            $price =    (int)get_post_meta($product->ID,'_wc_booking_block_cost',true);
        }
        
        }
         if($price==''){
            $price = 0;
        }
                        $html .= '<div class="product-price">' . $currency . ' ' . $price . '</div>';
                        $html .= '<div class="product-add-cart"><a href="' . get_permalink($product->ID) . '" class="button-primary" target="_blank">Buy Now</a></div>'
                                . '</div>';
                        $html .= '</div>';
                    }
                }
                
                $html .= '</div>';
                if($see_more_deals == 1){
                    $html .='<div class="see-more-deals"><a href="'.$vendor_url.'" target="_blank"><button name="see_more_deals" id="see_more_deals">See More Deals</button></a></div>';
                }
            }
        }
            // $html = '<h2>Welcome wnw HEllO</h2><label>Test</label>';
            $res_data = array('html' => $html);
            // Create the response object
            $response = new WP_REST_Response($res_data);
            $response->set_status(200);
            return $response;
        }
    }

    public static function get_preview_html(WP_REST_Request $request) {
        global $wpdb;
        $request_params = self::parse_request_params($request);
        if (isset($request_params['api_details'])) {
            $args = array('post_type' => 'product', 'posts_per_page' => -1);
            $products = get_posts($args);
            //  $html =$products;
            $html = '';
            if (!empty($products)) {
                $html .= '<div class="main-product-div">';
                foreach ($products as $product) {
                    $handle = new WC_Product_Variable($product->ID);
                    if (count($handle->get_attributes()) == 0) {//Condition for skip variation products
                        $image = wp_get_attachment_image_src(get_post_thumbnail_id($product->ID), 'thumbnail');
                        $currency = get_woocommerce_currency_symbol();
                        $price = get_post_meta($product->ID, '_price', true);
                        $html .= '<div class="single-product">';
                        $html .= '<div class="product-left-part">'
                                . '<div class="product-image"><img src="' . $image[0] . '" data-id="' . $product->ID . '"></div>'
                                . '</div>';
                        $html .= '<div class="product-right-part">'
                                . '<div class="product-title"><label>' . $product->post_title . '</label></div>';

                        $html .= '<div class="product-price">' . $currency . ' ' . $price . '</div>';
                        $html .= '<div class="product-add-cart"><a href="' . get_site_url() . '/?add-to-cart=' . $product->ID . '" class="button-primary" target="_blank">Buy Now</a></div>'
                                . '</div>';
                        $html .= '</div>';
                    }
                }
                $html .= '</div>';
            }

            // $html = '<h2>Welcome wnw HEllO</h2><label>Test</label>';
            $res_data = array('html' => $html);
            // Create the response object
            $response = new WP_REST_Response($res_data);
            $response->set_status(200);
            return $response;
        }
    }

    public static function parse_request_params($request) {
        global $wpdb;
        $request_body = $request->get_body();
        $params = array();
        parse_str($request_body, $params);
        $params['api_details'] = $params['api_details'];
        return $params;
        /* print_r($request_body); exit; */
    }

}
