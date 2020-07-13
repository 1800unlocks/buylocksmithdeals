<?php

defined('ABSPATH') || exit;
if (!class_exists('BLSDIncWnW')) {

    class BLSDIncWnW {

        protected static $_instance = null;

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
            include_once 'class-blsd-category-settings.php';
            include_once 'class-blsd-regional-product-listing.php';
            $this->init_hooks();
        }

        /**
         * Filters and Actions are bundled.
         * @return boolean
         */
        private function init_hooks() {
            
        }

        public static function blsd_get_all_categories() {
            global $wpdb;
            $args = array(
                'taxonomy' => "product_cat",
                'hide_empty' => 0,
            );
            $product_categories = get_terms($args);
            $current_vendor = get_current_user_id();
            $categoryFinal = [];
            if (count($product_categories) > 0) {

                foreach ($product_categories as $category) {
                    
                    if($category->name == 'Uncategorized'){
                        continue;
                    }
                    
                    $args = array(
                        'post_status' => array('publish'),
                        'author' => $current_vendor,
                        'posts_per_page' => -1,
                        'post_type' => 'product',
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
    ),
                        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => array($category->slug),
                'operator' => 'IN',
            )
         )
                    );

                    $postsFound = get_posts($args);
                   if(count($postsFound)>0){
                       $allowCategory = 0;
                      foreach($postsFound as $postdata){
                          if($postdata->post_status=='publish'){
                            $allowCategory = 1;  
                          }
                      }
                      if($allowCategory){
                       $categoryFinal[]=$category;
                      }
                      
                   }
                 
                }
            }
            return $categoryFinal;
        }
        
        
        public static function blsd_get_deals_of_vendor() {
            global $wpdb;
            $current_vendor = get_current_user_id();
            
                    $args = array(
                        'post_status' => 'publish',
                        'author' => $current_vendor,
                        'posts_per_page' => -1,
                        'post_type' => 'product',
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

                    $postsFound = get_posts($args);
         // print_r($postsFound); exit;
           
          
            return $postsFound;
        }

    }

    /* class end */
} /* if end */

function run_blsd_inc_wnw_func() {
    BLSDIncWnW::instance();
}

run_blsd_inc_wnw_func();
