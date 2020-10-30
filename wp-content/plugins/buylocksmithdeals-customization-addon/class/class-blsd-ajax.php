<?php

defined('ABSPATH') || exit;

/**
 * Main BuyLockSmithDealsCustomizationAdmin Class.
 *
 * @class BuyLockSmithDealsCustomizationAdmin
 */
final class BuyLockSmithDealsCustomizationAjax {

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
        $this->init_hooks();
    }

    /**
     * Filters and Actions are bundled.
     * @return boolean
     */
    private function init_hooks() {
        add_action('wp_ajax_wcmp_vendor_product_list_wnw', array($this, 'wcmp_vendor_product_list_wnw'));
        add_action('wp_ajax_wcmp_datatable_get_vendor_orders_wnw', array($this, 'wcmp_datatable_get_vendor_orders_wnw'));
        add_action('wp_ajax_wcmp_datatable_get_vendor_dispute_list_wnw', array($this, 'wcmp_datatable_get_vendor_dispute_list_wnw'));

        add_action("wp_ajax_get_stats_top_selling", array($this, "get_stats_top_selling"));
         add_action("wp_ajax_blsd_slug_duplicate_status", array($this, "blsd_slug_duplicate_status"));
         add_action("wp_ajax_blsd_get_deals_by_category", array($this, "blsd_get_deals_by_category"));
         add_action("wp_ajax_blsd_get_car_model_year", array($this, "blsd_get_car_model_year"));
         add_action("wp_ajax_nopriv_blsd_get_car_model_year", array($this, "blsd_get_car_model_year"));
        add_action( 'wp_ajax_product_remove', array($this, "blsd_ajax_product_remove")  );
        add_action( 'wp_ajax_nopriv_product_remove', array($this, "blsd_ajax_product_remove") );
        add_action( 'wp_ajax_blsd_location_same_service', array($this, "blsd_location_same_service") );
        add_action( 'wp_ajax_nopriv_blsd_location_same_service', array($this, "blsd_location_same_service") );
        add_action( 'wp_ajax_blsd_update_vendor_order_status', array($this, "blsd_update_vendor_order_status") );
        add_action( 'wp_ajax_nopriv_blsd_update_vendor_order_status', array($this, "blsd_update_vendor_order_status") );
        add_action( 'wp_ajax_blsd_check_cart_vendor_product', array($this, "blsd_check_cart_vendor_product") );
        add_action( 'wp_ajax_nopriv_blsd_check_cart_vendor_product', array($this, "blsd_check_cart_vendor_product") );
        add_action( 'wp_ajax_calculate_product_tax', array($this, "blsd_calculate_product_tax") );
        add_action( 'wp_ajax_nopriv_calculate_product_tax', array($this, "blsd_calculate_product_tax") );
        add_action( 'wp_ajax_blsd_update_vendor_rating', array($this, "blsd_update_vendor_rating") );
        add_action( 'wp_ajax_nopriv_blsd_update_vendor_rating', array($this, "blsd_update_vendor_rating") );
        add_action( 'wp_ajax_wc_bookings_get_blocks_blsd', array( $this, 'blsd_get_time_blocks_for_date' ) );
        add_action( 'wp_ajax_nopriv_wc_bookings_get_blocks_blsd', array( $this, 'blsd_get_time_blocks_for_date' ) );
        add_action( 'wp_ajax_blsd_get_phone_country_prefix', array( $this, 'blsd_get_phone_country_prefix' ) );
        add_action( 'wp_ajax_blsd_get_car_fee', array( $this, 'blsd_get_car_fee' ) );
        add_action( 'wp_ajax_nopriv_blsd_get_car_fee', array( $this, 'blsd_get_car_fee' ) );
        add_action( 'wp_ajax_blsd_get_vendor_products', array( $this, 'blsd_get_vendor_products' ) );
        add_action( 'wp_ajax_blsd_vendor_products_delete', array( $this, 'blsd_vendor_products_delete' ) );
		add_action( 'wp_ajax_blsd_wcmp_vendor_booking_list', array( $this, 'blsd_booking_list' ) );
    }
	
	function blsd_booking_list(){
		 //@TODO filter by bookable product, search by boookable product, sort by allowed columns
        ob_start();

        $current_vendor_id = afm()->vendor_id;
        if ( ! $current_vendor_id || ! current_vendor_can( 'manage_bookings' ) ) {
            wp_die( -1 );
        }
        $requestData = $_REQUEST;
        $enable_ordering = apply_filters( 'wcmp_vendor_dashboard_booking_list_table_orderable_columns', array( 'id', 'booked-product', 'start-date', 'end-date' ) );

        $args = array();

        if ( isset( $requestData['post_status'] ) && $requestData['post_status'] != '' ) {
            $args['post_status'] = $requestData['post_status'];
        }

        if ( isset( $requestData['filter_bookings'] ) && $requestData['filter_bookings'] != '' ) {
            $args['meta_query'] = array(
                array(
                    'key'   => get_post_type( $requestData['filter_bookings'] ) === 'bookable_resource' ? '_booking_resource_id' : '_booking_product_id',
                    'value' => absint( $requestData['filter_bookings'] ),
                ),
            );
        }
        // filter/ordering data
        if ( ! empty( $requestData['search']['value'] ) ) {
            $args['s'] = $requestData['search']['value'];
        }
        if ( isset( $requestData['order'][0]['column'] ) ) {
            $args['orderby'] = $enable_ordering[$requestData['order'][0]['column']];
            $args['order'] = $requestData['order'][0]['dir'];
        }
        $args['offset'] = $requestData['start'];
        $args['posts_per_page'] = $requestData['length'];

        $data = array();
        $vendor_bookings = WCMp_AFM_Booking_Integration::get_vendor_booking_array( $args );
        //wp_send_json($vendor_bookings);
        if ( $vendor_bookings ) {
            foreach ( $vendor_bookings as $vendor_booking ) {
                $row = array();
                $booking = new WC_Booking( $vendor_booking->ID );
                $product_id = $booking->get_product_id();
                $product = $booking->get_product();
                $product_url = wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_edit_product_endpoint', 'vendor', 'general', 'edit-product' ), $product_id );
                //datatable fields
                //booking ID column
                $row['id'] = sprintf( '<a href="%s">' . __( 'Booking #%d', 'woocommerce-bookings' ) . '</a>', esc_url( wcmp_get_vendor_dashboard_endpoint_url( 'bookings', $vendor_booking->ID ) ), esc_html__( $vendor_booking->ID ) );
                //product column
                $resource = $booking->get_resource();
                if ( $product ) {
                    $row['booked-product'] = "<a href='" . esc_url( $product_url ) . "'>" . esc_html( $product->get_title() ) . "</a>";
                    if ( $resource ) {
                        $row['booked-product'] .= ' (<a href="#">' . esc_html( $resource->get_name() ) . '</a>)';
                    }
                } else {
                    $row['booked-product'] = '-';
                }
                //persons column
               /*  if ( ! is_object( $product ) || ! $product->has_persons() ) {
                    $row['persons'] = esc_html__( 'N/A', 'woocommerce-bookings' );
                } else {
                    $row['persons'] = esc_html( array_sum( $booking->get_person_counts() ) );
                } */
                //customer column
                $customer = $booking->get_customer();
                $customer_name = esc_html( $customer->name ?: '-' );
                if ( $customer->email ) {
                    $customer_name = '<a href="mailto:' . esc_attr( $customer->email ) . '">' . $customer_name . '</a>';
                }
                $row['booked-by'] = $customer_name;
                //order column
                $order = $booking->get_order();
                $order_id = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
                $order_url = wcmp_get_vendor_dashboard_endpoint_url( get_wcmp_vendor_settings( 'wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders' ), $order_id );
                if ( $order ) {
                    $row['order'] = '<a href="' . esc_url( $order_url ) . '">#' . $order->get_order_number() . '</a> - ' . esc_html( wc_get_order_status_name( $order->get_status() ) );
                } else {
                    $row['order'] = '-';
                }
                $row['start-date'] = wcmp_date( $booking->get_start_date() );
                $row['end-date'] = wcmp_date( $booking->get_end_date() );
                $row['actions'] = sprintf( '<a href="%s">' . __( 'View', 'woocommerce-bookings' ) . '</a>', esc_url( wcmp_get_vendor_dashboard_endpoint_url( 'bookings', $vendor_booking->ID ) ) );

                $data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval( $requestData['draw'] ), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
            "recordsTotal"    => intval( count( $vendor_bookings ) ), // total number of records
            "recordsFiltered" => intval( count( $vendor_bookings ) ), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data"            => $data   // total data array
        );
        wp_send_json( $json_data );
	}
	 function blsd_vendor_products_delete(){
		$vendor_id=$_POST['vendor_id'];
		$products_ids=$_POST['products_ids'];
		$current_url=$_POST['current_url'];
		$posts_per_page=$_POST['posts_per_page'];
		if(!empty($products_ids)){
			foreach($products_ids as $product_id){
				wp_delete_post( $product_id );
			}
		}
		$paged=1;
		$row=self::get_vendor_product($vendor_id,$current_url,$posts_per_page,$paged);
		echo $row;
		exit;
		
	}
	function blsd_get_vendor_products(){
		$vendor_id=$_POST['vendor_id'];
		$current_url=$_POST['current_url'];
		$posts_per_page=$_POST['posts_per_page'];
		$paged=$_POST['paged'];
		$row=self::get_vendor_product($vendor_id,$current_url,$posts_per_page,$paged);
		echo $row;
		exit;
	}
	function get_vendor_product($vendor_id,$current_url,$posts_per_page,$paged){ 
		$all_products=[];
		$total_products=[];
		$offset = ($paged - 1) * $posts_per_page;
		 $row = '<table id="vendor_product_table">';
		if(!empty($vendor_id)){
			$args = array(
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'DESC',
                'post_type' => 'product',
                'author' => $vendor_id,
                );
		$total_products= wc_get_products($args);
		$args1 = array(
                'posts_per_page' => $posts_per_page,
                'offset' => $offset,                
                'orderby' => 'date',
                'order' => 'DESC',                
                'post_type' => 'product',                
                'author' => $vendor_id,
                );
		$all_products= wc_get_products($args1);
		
		
		}
		
		$total_products= BuyLockSmithDealsCustomizationAddon::get_product_details($total_products);
		$all_product=count($total_products);
		$total_pages = ceil($all_product / $posts_per_page);
		$products_array= BuyLockSmithDealsCustomizationAddon::get_product_details($all_products);
		$row .= '<thead>';
		$row .= '<th></th>';
		$row .= '<th>Name</th>';
		$row .= '<th>SKU</th>';
		$row .= '<th>Price</th>';
		$row .= '<th>Vendor Status</th>';
		$row .= '<th>Admin Status</th>';
		$row .= '</thead>';
		if (!empty($products_array)) {
		foreach ($products_array as $key => $product_single) {
			$product_id=$product_single['ID'];
			if ($product_single['status'] == 'publish') {
				$status = __('Published', 'dc-woocommerce-multi-vendor');
			} elseif ($product_single['status'] == 'pending') {
				$status = __('Pending', 'dc-woocommerce-multi-vendor');
			} elseif ($product_single['status'] == 'draft') {
				$status = __('Draft', 'dc-woocommerce-multi-vendor');
			} elseif ($product_single['status'] == 'private') {
				$status = __('Private', 'dc-woocommerce-multi-vendor');
			} elseif ($product_single['status'] == 'trash') {
				$status = __('Trash', 'dc-woocommerce-multi-vendor');
			} else {
				$status = ucfirst($product_single['status']);
			}
			$status_vendor = get_post_meta($product_id, 'status_vendor', true);
			if ($status_vendor == 'publish') {
				$status_vendor = 'Published';
			}
			$row .=  '<tr>';
			$row .=  '<td><input type="checkbox" class="select_' . $product_single['status'] . '" name="selected_products[]" value="' . $product_id . '" data-title="' . $product_single['name'] . '" data-sku="' . $product_single['sku'] . '"/></td>';
			$row .=  '<td>' . $product_single['name'] . '</td>';
			$row .=  '<td>' . $product_single['sku'] . '</td>';
			$row .=  '<td>' . get_woocommerce_currency_symbol().$product_single['price'] . '</td>';
			$row .=  '<td>' . ucfirst($status_vendor) . '</td>';
			$row .=  '<td>' . $status . '</td>';
			$row .=  '</tr>';
		   } 
		}
		else{
			$row .=  '<tr>';
			$row .=  '<td colspan="6">No products Found</td>';
			$row .=  '</tr>';
		}
		
		$row .='</table>'; 
	
		$row .= BuyLockSmithDealsCustomizationAddon::render_pagination_ajax($current_url, $total_pages,'',$paged); 
		return $row;
	} 
    function blsd_get_phone_country_prefix(){
         global $wpdb;
         $phone_country = $_POST['phone_country'];
         $table_name=BuyLockSmithDealsCustomizationAddon::blsd_phone_country_table_name();
         $query = "SELECT phonecode FROM $table_name WHERE iso='$phone_country'";
         $results = $wpdb->get_row($query, ARRAY_A);
         echo $results['phonecode'];
         exit;
    }
    function blsd_get_time_blocks_for_date(){
        
        // clean posted data
		$posted = array();
		parse_str( $_POST['form'], $posted );
		if ( empty( $posted['add-to-cart'] ) ) {
			return false;
		}

		// Product Checking
		$booking_id   = $posted['add-to-cart'];
		$product      = get_wc_product_booking( wc_get_product( $booking_id ) );
		if ( ! $product ) {
			return false;
		}

		// Check selected date.
		if ( ! empty( $posted['wc_bookings_field_start_date_year'] ) && ! empty( $posted['wc_bookings_field_start_date_month'] ) && ! empty( $posted['wc_bookings_field_start_date_day'] ) ) {
			$year      = max( date( 'Y' ), absint( $posted['wc_bookings_field_start_date_year'] ) );
			$month     = absint( $posted['wc_bookings_field_start_date_month'] );
			$day       = absint( $posted['wc_bookings_field_start_date_day'] );
			$timestamp = strtotime( "{$year}-{$month}-{$day}" );
		}
		if ( empty( $timestamp ) ) {
			die( '<li>' . esc_html__( 'Please enter a valid date.', 'woocommerce-bookings' ) . '</li>' );
		}

		if ( ! empty( $posted['wc_bookings_field_duration'] ) ) {
			$interval = (int) $posted['wc_bookings_field_duration'] * $product->get_duration();
		} else {
			$interval = $product->get_duration();
		}

		$base_interval = $product->get_duration();

		if ( 'hour' === $product->get_duration_unit() ) {
			$interval      = $interval * 60;
			$base_interval = $base_interval * 60;
		}

		$first_block_time     = $product->get_first_block_time();
		$from                 = strtotime( $first_block_time ? $first_block_time : 'midnight', $timestamp );
		$standard_from        = $from;

		// Get an extra day before/after so front-end can get enough blocks to fill out 24 hours in client time.
		if ( isset( $posted['get_prev_day'] ) ) {
			$from = strtotime( '- 1 day', $from );
		}
                $productid=$product->get_id();
                $wc_booking_last_block_time=get_post_meta($productid,'_wc_booking_last_block_time',true);
                if(!empty($wc_booking_last_block_time)){
                    $to =strtotime( $wc_booking_last_block_time, $timestamp );
                }
                else{
		$to = strtotime( '+ 1 day', $standard_from ) + $interval;
                    if ( isset( $posted['get_next_day'] ) ) {
                            $to = strtotime( '+ 1 day', $to );
                    }

		// cap the upper range
		$to                   = strtotime( 'midnight', $to ) - 1; 
                }
                

		$resource_id_to_check = ( ! empty( $posted['wc_bookings_field_resource'] ) ? $posted['wc_bookings_field_resource'] : 0 );
		$resource             = $product->get_resource( absint( $resource_id_to_check ) );
		$resources            = $product->get_resources();

		if ( $resource_id_to_check && $resource ) {
			$resource_id_to_check = $resource->ID;
		} elseif ( $product->has_resources() && $resources && count( $resources ) === 1 ) {
			$resource_id_to_check = current( $resources )->ID;
		} else {
			$resource_id_to_check = 0;
		}

		$booking_form = new WC_Booking_Form( $product );
		$blocks       = $product->get_blocks_in_range( $from, $to, array( $interval, $base_interval ), $resource_id_to_check );
		$block_html   = $booking_form->get_time_slots_html( $blocks, array( $interval, $base_interval ), $resource_id_to_check, $from, $to );

		if ( empty( $block_html ) ) {
			$block_html .= '<li>' . __( 'No blocks available.', 'woocommerce-bookings' ) . '</li>';
		}

		die( $block_html ); // phpcs:ignore WordPress.Security.EscapeOutput
    }
    function blsd_update_vendor_rating(){
        
        global $WCMp, $wpdb;
        $review = $_POST['comment'];
        $rating = isset($_POST['rating']) ? $_POST['rating'] : false;
        $comment_parent = isset($_POST['comment_parent']) ? $_POST['comment_parent'] : 0;
        $vendor_id = $_POST['vendor_id'];
        $customer_id = $_POST['customer_id'];
        $order_id = $_POST['order_id'];
        $current_user = get_user_by('ID', $customer_id);
		$comment_approve_by_settings = get_option('comment_moderation') ? 0 : 1;
        $vendor_user = get_user_by('ID', $vendor_id);
            $time = current_time('mysql');
            if ($current_user->ID > 0) {
                $data = array(
                    'comment_post_ID' => BuyLockSmithDealsCustomizationAddon::blsd_wcmp_vendor_dashboard_page_id(),
                    'comment_author' => $current_user->display_name,
                    'comment_author_email' => $current_user->user_email,
                    'comment_author_url' => $current_user->user_url,
                    'comment_content' => $review,
                    'comment_type' => 'wcmp_vendor_rating',
                    'comment_parent' => $comment_parent,
                    'user_id' => $current_user->ID,
                    'comment_author_IP' => $_SERVER['REMOTE_ADDR'],
                    'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'comment_date' => $time,
                    'comment_approved' => $comment_approve_by_settings,
                );
                
                $comment_id = wp_insert_comment($data);
                
                update_comment_meta($comment_id, '_rating_on_order', $order_id);
                if (!is_wp_error($comment_id)) {
                    // delete transient
                    if (get_transient('wcmp_dashboard_reviews_for_vendor_' . $vendor_id)) {
                        delete_transient('wcmp_dashboard_reviews_for_vendor_' . $vendor_id);
                    }
                    // mark as replied
                    if ($comment_parent != 0 && $vendor_id) {
                        update_comment_meta($comment_parent, '_mark_as_replied', 1);
                    }
                    if ($rating && !empty($rating)) {
                        update_comment_meta($comment_id, 'vendor_rating', $rating);
                    }
                    $is_updated = update_comment_meta($comment_id, 'vendor_rating_id', $vendor_id);
                    /**********************************/
                    $mailer = WC()->mailer();
                    if($rating>3){
                    //mail send to user with social links
                    $order = wc_get_order( $order_id );
                    $parent_order_id = $order->get_parent_id();
                    $parent_order = wc_get_order( $parent_order_id );
                    $template_html  = '/emails/customer-recommend_vendor_socialmedia.php';
                    //$recipient = $current_user->user_email;
                    $recipient = get_post_meta($order_id,'_billing_email',true);
                    $subject = __("You loved us! Please recommend us to others", 'theme_name');
                    $attachments=[];
                    $content = wc_get_template_html(
				$template_html,
				array(
					'order'              => $parent_order,
					'email_heading'      => '{vendor_logo}',
					'additional_content' => '',
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $mailer,
				)
			); 
                        $headers = "Content-Type: text/html\r\n";

                        //send custom mail
                        do_action( 'custom_review_email', $order_id);
                        
                        //send the email through wordpress
                        // $mailer->send( $recipient, $subject, $content, $headers, $attachments );
                    }
                    else{
                        // mail send to admin for poor rating
                        $super_admin_list = BuyLockSmithDealsCustomizationAddon::get_all_admin_list_global();
                        foreach ($super_admin_list as $super_admin) {
                            $recipient = $super_admin->user_email;
                            $vendor_name = $vendor_user->display_name;
                            $customer_name = $current_user->display_name;
                            $subject = __("Poor Locksmith Rating #".$vendor_name, 'theme_name');
                            $attachments=[];
                            $name = $super_admin->display_name;
                            $message = 'Hello ' . $name . ',';
                            $message .= '</br></br>';
                            $message .= 'Locksmith "'.$vendor_name.'" Rating is low given by customer "'.$customer_name.'"';
                            $message .= '</br></br>';
                            $message .= 'Rating: ' . $rating;
                            $message .= '</br></br>';
                            $message .= '</br>';
                            $message .= 'Please see Locksmith.';
                            $headers = "MIME-Version: 1.0" . "\r\n";
                            $headers .= "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
                            //send the email through wordpress
                            wp_mail($recipient, $subject, $message, $headers);
                            
                        }
                        
                    }   
                    
                    if ($is_updated) {
                        echo 1;
                    }
                }
            }
        
        die;
    }
    function blsd_calculate_product_tax(){
        $amount=$_POST['amount'];
        $latitude=$_POST['latitude'];
        $longitude=$_POST['longitude'];
        $format=BuyLockSmithDealsCustomizationAddon::get_address_from_coordinates_global($latitude,$longitude);
        $format_address=json_decode($format);
        $country='';
        $state='';
        $total_amount=0;
        foreach($format_address->address_components as $add){
                
                if($add->types[0] == 'country'){
                    $country =$add->short_name;
                }
                if($add->types[0] == 'administrative_area_level_1'){
                    $state =$add->short_name;
                }
        }
        $rate='';
        $all_tax_rates = [];
        $tax_classes = WC_Tax::get_tax_classes(); // Retrieve all tax classes.
        if ( !in_array( '', $tax_classes ) ) { // Make sure "Standard rate" (empty class name) is present.
            array_unshift( $tax_classes, '' );
        }
        foreach ( $tax_classes as $tax_class ) { // For each tax class, get all rates.
            $taxes = WC_Tax::get_rates_for_tax_class( $tax_class );
            $all_tax_rates = array_merge( $all_tax_rates, $taxes );
        }
        foreach($all_tax_rates as $tax){
            if($country == $tax->tax_rate_country && $state == $tax->tax_rate_state){
                $rate=$tax->tax_rate;
            }
        }
        if($rate == ''){
            $total_amount=$amount;
        }
        else{
            $tax_amount=($amount*$rate)/100;
            $total_amount=$amount+$tax_amount;
        }
        
        echo json_encode(['rate'=>$rate,'total_amount'=>$total_amount]);
        exit;
    }
    function blsd_check_cart_vendor_product(){
        global $woocommerce;
        $items = $woocommerce->cart->get_cart();
        $post_id=$_POST['post_id'];
        $current_post_authorid = get_post_field( 'post_author', $post_id );
        $count=0;
        foreach ( $items as $cart_item ) {
            $product_id=$cart_item['product_id'];
            $post_authorid = get_post_field( 'post_author', $product_id );
            if($post_authorid != $current_post_authorid){
             $count++;   
            }
            $count++;
        }
        if($count == 0){
            echo 'success';
        }
        else{
           echo 'failed'; 
        }
        exit;
    }
    function blsd_update_vendor_order_status(){
        global $WCMp; 
        $order_id=$_POST['order_id'];
         $code=$_POST['code'];
         
         $order_token= get_post_meta($order_id,'unique_token',true);
        if($order_token == $code){ 
            $selected_status ='wc-completed';
            $order = wc_get_order( $order_id );
            $vendor_id = get_post_meta($order_id,'_vendor_id',true);
            $vendor = get_wcmp_vendor($vendor_id);
			update_post_meta($order_id,'_order_completed_by_code','yes');
		   
					   /**********Mail send on job completion************/
			$parent_order_id = $order->get_parent_id();
			$parent_order = wc_get_order( $parent_order_id );
			$template_html  = '/emails/customer-job-completion.php';
		  
			$customer_id=get_post_meta($order_id,'_customer_user',true);
			$customer = get_user_by('ID', $customer_id);
		  
			// load the mailer class
			$mailer = WC()->mailer();
			//format the email
			
			// $recipient = $customer->data->user_email;
			$recipient = get_post_meta($order_id,'_billing_email',true);
		   
		
			$subject = __("Job Completion #".$parent_order_id, 'theme_name');
		   
			$attachments=[];
			$content = wc_get_template_html(
						$template_html,
						array(
							'order'              => $parent_order,
							'email_heading'      => '{vendor_logo}',
							'additional_content' => '',
							'sent_to_admin'      => false,
							'plain_text'         => false,
							'email'              => $mailer,
						)
					); 
		  
			$headers = "Content-Type: text/html\r\n";
		   
			//send the email through wordpress
			// $mailer->send( $recipient, $subject, $content, $headers, $attachments );
			/*********************/
			
			 if( $order ) {
				//   echo $order_id;
					$order = new WC_Order($order_id);
				  $order->update_status($selected_status);
                  do_action('woocommerce_order_status_completed',$order_id);
				  
				}
			
			echo 'success';
        }else{
            echo 'failed';
        }
       exit;
    }

    public function get_wcmp_transaction_notice_wnw($transaction_id) {
        $transaction = get_post($transaction_id);
        $notice = array();
        switch ($transaction->post_status) {
            case 'wcmp_processing':
                $notice = array('type' => 'success', 'message' => __('Your withdrawal request has been sent to the admin and your commission will be disbursed shortly!', 'dc-woocommerce-multi-vendor'));
                break;
            case 'wcmp_completed':
                $notice = array('type' => 'success', 'message' => __('Congrats! You have successfully received your commission amount.', 'dc-woocommerce-multi-vendor'));
                break;
            case 'wcmp_canceled':
                $notice = array('type' => 'error', 'message' => __('Oops something went wrong! Your commission withdrawal request was declined!', 'dc-woocommerce-multi-vendor'));
                break;
            default :
                break;
        }
        return apply_filters('wcmp_get_transaction_status_notice', $notice, $transaction);
    }
    public function blsd_location_same_service(){
        global $woocommerce;
        $items = $woocommerce->cart->get_cart();
        $address=[];
        $popup=[];
        $location_coordinates=[];
        foreach ( $items as $cart_item ) {
            //print_r($cart_item);
            $address[] = $cart_item['my_location_coordinates'][0];
            //$location_coordinates[] = $cart_item['my_location_coordinates'][0];
           // $location_address[]=$cart_item['my_location_address'][0];
        }
       
        //$address=BuyLockSmithDealsCustomizationAddon::get_address_selected($location_coordinates,$location_address);
       // print_r($popup);
        $latitude=$address[0]['latitude'];
        $longitude=$address[0]['longitude'];
        $format=BuyLockSmithDealsCustomizationAddon::get_address_from_coordinates_global($latitude,$longitude);
        $format_address=json_decode($format);
        $address1='';
        $address2='';
        $city='';
        $country='';
        $post_code='';
        $state='';
        $billing_add=[];
        foreach($format_address->address_components as $add){
                if($add->types[0] == 'street_number'){
                    $address1 .=$add->short_name.', ';
                }
                if($add->types[0] == 'route'){
                    $address1 .=$add->short_name;
                }
                if($add->types[0] == 'locality'){
                    $city .=$add->short_name;
                }
                if($add->types[0] == 'country'){
                    $country .=$add->short_name;
                }
                if($add->types[0] == 'postal_code'){
                    $post_code .=$add->short_name;
                }
                if($add->types[0] == 'administrative_area_level_1'){
                    $state .=$add->short_name;
                }
        }
        $billing_add['billing_country']=$country;
        $billing_add['billing_address_1']=$address1;
        $billing_add['billing_address_2']=$address2;
        $billing_add['billing_city']=$city;
        $billing_add['billing_postcode']=$post_code;
        $billing_add['billing_state']=$state;
        $fields = WC()->checkout()->get_checkout_fields( 'billing' );
        $array=[];
        foreach ( $fields as $key => $field ) {
            if(array_key_exists($key,$billing_add)){
                
            $array[$key]=$billing_add[$key];
            }
        }
        $status=$_POST['status'];
        echo json_encode($array);
        exit;
        
    }
    public function blsd_ajax_product_remove()
    {
        ob_start();
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item)
        {
            if($cart_item['product_id'] == $_POST['product_id'] && $cart_item_key == $_POST['cart_item_key'] )
            {
                WC()->cart->remove_cart_item($cart_item_key);
            }
        }
        WC()->cart->calculate_totals();
        WC()->cart->maybe_set_cart_cookies();
        woocommerce_order_review();
        $woocommerce_order_review = ob_get_clean();
    }
    
    public function blsd_get_car_model_year(){
        $maker=$_POST['maker'];
        $model=isset($_POST['model'])?$_POST['model']:'';
        $post_id=$_POST['post_id'];
        $vendor_unserviceable_cars= get_post_meta($post_id,'unserviceable_cars',true);
        if($model !=''){
          $where_field="maker = '$maker' AND model= '$model'";  
          $field='year';
        }
        else{
          $where_field="maker = '$maker'";
           $field='model';
        }
        $all_cars= BuyLockSmithDealsCustomizationAddon::get_all_cars_frontend($vendor_unserviceable_cars,$field,$where_field,$post_id);
        
        if($model !=''){
            $option='<option value="">Year</option>';
            foreach($all_cars as $cars){
              $option .="<option value='".$cars['year']."'>".$cars['year']."</option>";  
            }
        }
        else{
            $option='<option value="">Model</option>';
            foreach($all_cars as $cars){
              $option .="<option value='".$cars['model']."'>".$cars['model']."</option>";  
            }
        }
        
        echo $option;
        exit;
    }
    
    public function blsd_get_car_fee(){
        global $wpdb;
        $maker=$_POST['maker'];
        $model=$_POST['model'];
        $year=$_POST['year'];
        $post_id=$_POST['post_id'];
        $table_name = BuyLockSmithDealsCustomizationAddon::blsd_y_m_model_table_name();
        $query = "SELECT * from $table_name where maker='$maker' and model='$model' and year=$year"; 
        $result =$wpdb->get_row($query, ARRAY_A);
        $data=['programming'=>$result['programming'],'type'=>$result['type'],'message'=>$result['message']];
        echo json_encode($data);
        exit;
        
    }
    
    
    /*
     * Altering the wcmp product list in vendor dashboard.
     * * @return JSON
     */

    public function wcmp_vendor_product_list_wnw() {
        global $WCMp;
        if (is_user_logged_in() && is_user_wcmp_vendor(get_current_user_id())) {
            $vendor = get_current_vendor();
            $enable_ordering = apply_filters('wcmp_vendor_dashboard_product_list_table_orderable_columns', array('name', 'date'));
            $products_table_headers = array(
                'select_product' => '',
                'image' => '',
                'name' => __('Product', 'dc-woocommerce-multi-vendor'),
                'price' => __('Price', 'dc-woocommerce-multi-vendor'),
                'stock' => __('Stock', 'dc-woocommerce-multi-vendor'),
                'categories' => __('Categories', 'dc-woocommerce-multi-vendor'),
                'date' => __('Date', 'dc-woocommerce-multi-vendor'),
                'status_vendor' => __('Status', 'dc-woocommerce-multi-vendor'),
                'status' => __('Admin Status', 'dc-woocommerce-multi-vendor'),
                'actions' => __('Actions', 'dc-woocommerce-multi-vendor'),
            );
            $products_table_headers = apply_filters('wcmp_vendor_dashboard_product_list_table_headers', $products_table_headers);
            // storing columns keys for ordering
            $columns = array();
            foreach ($products_table_headers as $key => $value) {
                $columns[] = $key;
            }

            $requestData = $_REQUEST;
            $filterActionData = array();
            parse_str($requestData['products_filter_action'], $filterActionData);
            do_action('before_wcmp_products_list_query_bind', $filterActionData, $requestData);
            $notices = array();
            // Do bulk handle
            if (isset($requestData['bulk_action']) && $requestData['bulk_action'] != '' && isset($filterActionData['selected_products']) && is_array($filterActionData['selected_products'])) {
                if ($requestData['bulk_action'] === 'trash') {
                    // Trash products
                    foreach ($filterActionData['selected_products'] as $id) {
                        wp_trash_post($id);
                    }
                    $notices[] = array(
                        'message' => ((count($filterActionData['selected_products']) > 1) ? sprintf(__('%s products', 'dc-woocommerce-multi-vendor'), count($filterActionData['selected_products'])) : sprintf(__('%s product', 'dc-woocommerce-multi-vendor'), count($filterActionData['selected_products']))) . ' ' . __('moved to the Trash.', 'dc-woocommerce-multi-vendor'),
                        'type' => 'success'
                    );
                } elseif ($requestData['bulk_action'] === 'untrash') {
                    // Untrash products
                    foreach ($filterActionData['selected_products'] as $id) {
                        wp_untrash_post($id);
                    }
                    $notices[] = array(
                        'message' => ((count($filterActionData['selected_products']) > 1) ? sprintf(__('%s products', 'dc-woocommerce-multi-vendor'), count($filterActionData['selected_products'])) : sprintf(__('%s product', 'dc-woocommerce-multi-vendor'), count($filterActionData['selected_products']))) . ' ' . __('restored from the Trash.', 'dc-woocommerce-multi-vendor'),
                        'type' => 'success'
                    );
                } elseif ($requestData['bulk_action'] === 'delete') {
                    if (current_user_can('delete_published_products')) {
                        // delete products
                        foreach ($filterActionData['selected_products'] as $id) {
                            wp_delete_post($id);
                        }
                        $notices[] = array(
                            'message' => ((count($filterActionData['selected_products']) > 1) ? sprintf(__('%s products', 'dc-woocommerce-multi-vendor'), count($filterActionData['selected_products'])) : sprintf(__('%s product', 'dc-woocommerce-multi-vendor'), count($filterActionData['selected_products']))) . ' ' . __('deleted from the Trash.', 'dc-woocommerce-multi-vendor'),
                            'type' => 'success'
                        );
                    } else {
                        $notices[] = array(
                            'message' => __('Sorry! You do not have this permission.', 'dc-woocommerce-multi-vendor'),
                            'type' => 'error'
                        );
                    }
                } else {
                    do_action('wcmp_products_list_do_handle_bulk_actions', $vendor->get_products(), $filterActionData['bulk_actions'], $filterActionData['selected_products'], $filterActionData, $requestData);
                }
            }
            $df_post_status = apply_filters('wcmp_vendor_dashboard_default_product_list_statues', array('publish', 'pending', 'draft'), $requestData, $vendor);
            if (isset($requestData['post_status']) && $requestData['post_status'] != 'all') {
                $df_post_status = $requestData['post_status'];
            }
            $args = apply_filters('wcmp_get_vendor_product_list_query_args', array(
                'posts_per_page' => -1,
                'offset' => 0,
                'category' => '',
                'category_name' => '',
                'orderby' => 'date',
                'order' => 'DESC',
                'include' => '',
                'exclude' => '',
                'meta_key' => '',
                'meta_value' => '',
                'post_type' => 'product',
                'post_mime_type' => '',
                'post_parent' => '',
                'author' => get_current_vendor_id(),
                'post_status' => $df_post_status,
                'suppress_filters' => true
                    ), $vendor, $requestData);
            $tax_query = array();
            if (isset($filterActionData['product_cat']) && $filterActionData['product_cat'] != '') {
                $tax_query[] = array('taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $filterActionData['product_cat']);
            }
            if (isset($filterActionData['product_type']) && $filterActionData['product_type'] != '') {
                if ('downloadable' === $filterActionData['product_type']) {
                    $args['meta_value'] = 'yes';
                    $query_vars['meta_key'] = '_downloadable';
                } elseif ('virtual' === $filterActionData['product_types']) {
                    $query_vars['meta_value'] = 'yes';
                    $query_vars['meta_key'] = '_virtual';
                } else {
                    $tax_query[] = array('taxonomy' => 'product_type', 'field' => 'slug', 'terms' => $filterActionData['product_type']);
                }
            }
            if ($tax_query):
                $args['tax_query'] = $tax_query;
            endif;

            $total_products_array = $vendor->get_products(apply_filters('wcmp_products_list_total_products_query_args', $args, $filterActionData, $requestData));
            // filter/ordering data
            if (!empty($requestData['search_keyword'])) {
                $args['s'] = $requestData['search_keyword'];
            }
            if (isset($columns[$requestData['order'][0]['column']]) && in_array($columns[$requestData['order'][0]['column']], $enable_ordering)) {
                $args['orderby'] = $columns[$requestData['order'][0]['column']];
                $args['order'] = $requestData['order'][0]['dir'];
            }
            if (isset($requestData['post_status']) && $requestData['post_status'] != 'all') {
                $args['post_status'] = $requestData['post_status'];
            }
            $args['offset'] = $requestData['start'];
            $args['posts_per_page'] = $requestData['length'];

            $args = apply_filters('wcmp_datatable_product_list_query_args', $args, $filterActionData, $requestData);

            $data = array();
            $products_array = $vendor->get_products($args);
            if (!empty($products_array)) {
                foreach ($products_array as $product_single) {
                    $row = array();
                    $product = wc_get_product($product_single->ID);
                    $edit_product_link = '';
                    if ((current_vendor_can('edit_published_products') && get_wcmp_vendor_settings('is_edit_delete_published_product', 'capabilities', 'product') == 'Enable') || in_array($product->get_status(), apply_filters('wcmp_enable_edit_product_options_for_statuses', array('draft', 'pending')))) {
                        $edit_product_link = esc_url(wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_edit_product_endpoint', 'vendor', 'general', 'edit-product'), $product->get_id()));
                    }
                    if (!current_vendor_can('edit_product') && in_array($product->get_status(), apply_filters('wcmp_enable_edit_product_options_for_statuses', array('draft', 'pending'))))
                        $edit_product_link = '';
                    $edit_product_link = apply_filters('wcmp_vendor_product_list_wnw_product_edit_link', $edit_product_link, $product);
                    // Get actions
                    $onclick = "return confirm('" . __('Are you sure want to delete this product?', 'dc-woocommerce-multi-vendor') . "')";
                    $view_title = __('View', 'dc-woocommerce-multi-vendor');
                    if (in_array($product->get_status(), array('draft', 'pending'))) {
                        $view_title = __('Preview', 'dc-woocommerce-multi-vendor');
                    }
                    $actions = array(
                        'id' => sprintf(__('ID: %d', 'dc-woocommerce-multi-vendor'), $product->get_id()),
                    );
                    // Add GTIN if have
                    if (get_wcmp_vendor_settings('is_gtin_enable', 'general') == 'Enable') {
                        $gtin_terms = wp_get_post_terms($product->get_id(), $WCMp->taxonomy->wcmp_gtin_taxonomy);
                        $gtin_label = '';
                        if ($gtin_terms && isset($gtin_terms[0])) {
                            $gtin_label = $gtin_terms[0]->name;
                        }
                        $gtin_code = get_post_meta($product->get_id(), '_wcmp_gtin_code', true);

                        if ($gtin_code) {
                            $actions['gtin'] = ( $gtin_label ) ? $gtin_label . ': ' . $gtin_code : __('GTIN', 'dc-woocommerce-multi-vendor') . ': ' . $gtin_code;
                        }
                    }

                    $actions_col = array(
                        'view' => '<a href="' . esc_url($product->get_permalink()) . '" target="_blank" title="' . $view_title . '"><i class="wcmp-font ico-eye-icon"></i></a>',
                        'edit' => '<a href="' . esc_url($edit_product_link) . '" title="' . __('Edit', 'dc-woocommerce-multi-vendor') . '"><i class="wcmp-font ico-edit-pencil-icon"></i></a>',
                        'restore' => '<a href="' . esc_url(wp_nonce_url(add_query_arg(array('product_id' => $product->get_id()), wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_products_endpoint', 'vendor', 'general', 'products'))), 'wcmp_untrash_product')) . '" title="' . __('Restore from the Trash', 'dc-woocommerce-multi-vendor') . '"><i class="wcmp-font ico-reply-icon"></i></a>',
                            //'trash' => '<a class="productDelete" href="' . esc_url(wp_nonce_url(add_query_arg(array('product_id' => $product->get_id()), wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_products_endpoint', 'vendor', 'general', 'products'))), 'wcmp_trash_product')) . '" title="' . __('Move to the Trash', 'dc-woocommerce-multi-vendor') . '"><i class="wcmp-font ico-delete-icon"></i></a>',
                            //'delete' => '<a class="productDelete" href="' . esc_url(wp_nonce_url(add_query_arg(array('product_id' => $product->get_id()), wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_products_endpoint', 'vendor', 'general', 'products'))), 'wcmp_delete_product')) . '" onclick="' . $onclick . '" title="' . __('Delete Permanently', 'dc-woocommerce-multi-vendor') . '"><i class="wcmp-font ico-delete-icon"></i></a>',
                    );
                    if ($product->get_status() == 'trash') {
                        $edit_product_link = '';
                        unset($actions_col['edit']);
                        unset($actions_col['trash']);
                        unset($actions_col['view']);
                    } else {
                        unset($actions_col['restore']);
                        unset($actions_col['delete']);
                    }

                    if (!current_vendor_can('edit_published_products') && get_wcmp_vendor_settings('is_edit_delete_published_product', 'capabilities', 'product') != 'Enable' && !in_array($product->get_status(), apply_filters('wcmp_enable_edit_product_options_for_statuses', array('draft', 'pending')))) {
                        unset($actions_col['edit']);
                        if ($product->get_status() != 'trash')
                            unset($actions_col['delete']);
                    }elseif (!current_vendor_can('edit_product') && in_array($product->get_status(), apply_filters('wcmp_enable_edit_product_options_for_statuses', array('draft', 'pending')))) {
                        unset($actions_col['edit']);
                    }

                    $actions = apply_filters('wcmp_vendor_product_list_wnw_row_actions', $actions, $product);
                    $actions_col = apply_filters('wcmp_vendor_product_list_wnw_row_actions_column', $actions_col, $product);
                    $row_actions = array();
                    foreach ($actions as $action => $link) {
                        $row_actions[] = '<span class="' . esc_attr($action) . '">' . $link . '</span>';
                    }
                    $row_actions_col = array();
                    foreach ($actions_col as $action => $link) {
                        $row_actions_col[] = '<span class="' . esc_attr($action) . '">' . $link . '</span>';
                    }
                    $action_html = '<div class="row-actions">' . implode(' <span class="divider">|</span> ', $row_actions) . '</div>';
                    $actions_col_html = '<div class="col-actions">' . implode(' <span class="divider">|</span> ', $row_actions_col) . '</div>';
                    // is in stock
                    if ($product->is_in_stock()) {
                        $stock_html = '<span class="label label-success instock">' . __('In stock', 'dc-woocommerce-multi-vendor');
                        if ($product->managing_stock()) {
                            $stock_html .= ' (' . wc_stock_amount($product->get_stock_quantity()) . ')';
                        }
                        $stock_html .= '</span>';
                    } else {
                        $stock_html = '<span class="label label-danger outofstock">' . __('Out of stock', 'dc-woocommerce-multi-vendor') . '</span>';
                    }
                    // product cat
                    $product_cats = '';
                    $termlist = array();
                    $terms = get_the_terms($product->get_id(), 'product_cat');
                    if (!$terms) {
                        $product_cats = '<span class="na">&ndash;</span>';
                    } else {
                        $terms = apply_filters('wcmp_vendor_product_list_wnw_row_product_categories', $terms, $product);
                        foreach ($terms as $term) {
                            $termlist[] = $term->name;
                        }
                    }
                    if ($termlist) {
                        $product_cats = implode(' | ', $termlist);
                    }
                    $date = '&ndash;';
                    if ($product->get_status() == 'publish') {
                        $status = __('Published', 'dc-woocommerce-multi-vendor');
                        $date = wcmp_date($product->get_date_created('edit'));
                    } elseif ($product->get_status() == 'pending') {
                        $status = __('Pending', 'dc-woocommerce-multi-vendor');
                    } elseif ($product->get_status() == 'draft') {
                        $status = __('Draft', 'dc-woocommerce-multi-vendor');
                    } elseif ($product->get_status() == 'private') {
                        $status = __('Private', 'dc-woocommerce-multi-vendor');
                    } elseif ($product->get_status() == 'trash') {
                        $status = __('Trash', 'dc-woocommerce-multi-vendor');
                    } else {
                        $status = ucfirst($product->get_status());
                    }

                    $status_vendor = get_post_meta($product->get_id(), 'status_vendor', true);
                    if ($status_vendor == 'publish') {
                        $status_vendor = 'Published';
                    }

                    $row ['select_product'] = '<input type="checkbox" class="select_' . $product->get_status() . '" name="selected_products[' . $product->get_id() . ']" value="' . $product->get_id() . '" data-title="' . $product->get_title() . '" data-sku="' . $product->get_sku() . '"/>';
                    $row ['image'] = '<td>' . $product->get_image(apply_filters('wcmp_vendor_product_list_wnw_image_size', array(40, 40))) . '</td>';
                    $row ['name'] = '<td><a href="' . esc_url($edit_product_link) . '">' . $product->get_title() . '</a>' . $action_html . '</td>';
                    $row ['price'] = '<td>' . $product->get_price_html() . '</td>';
                    $row ['stock'] = '<td>' . $stock_html . '</td>';
                    $row ['categories'] = '<td>' . $product_cats . '</td>';
                    $row ['date'] = '<td>' . $date . '</td>';
                    $row ['status_vendor'] = '<td>' . ucfirst($status_vendor) . '</td>';
                    $row ['status'] = '<td>' . $status . '</td>';
                    $row ['actions'] = '<td>' . $actions_col_html . '</td>';
                    $data[] = apply_filters('wcmp_vendor_dashboard_product_list_table_row_data', $row, $product, $filterActionData, $requestData);
                }
            }

            $json_data = apply_filters('wcmp_datatable_product_list_result_data', array(
                "draw" => intval($requestData['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
                "recordsTotal" => intval(count($total_products_array)), // total number of records
                "recordsFiltered" => intval(count($total_products_array)), // total number of records after searching, if there is no searching then totalFiltered = totalData
                "data" => $data, // total data array
                "notices" => $notices   // set messages or motices
                    ), $filterActionData, $requestData);
            wp_send_json($json_data);
            die;
        }
    }

    public function wcmp_datatable_get_vendor_orders_wnw() {
        global $wpdb, $WCMp;
        $requestData = $_REQUEST;
        $start_date = date('Y-m-d G:i:s', $_POST['start_date']);
        $end_date = date('Y-m-d G:i:s', $_POST['end_date']);
        $vendor = get_current_vendor();

        $args = array(
            'author' => $vendor->id,
            'date_query' => array(
                array(
                    'after' => $start_date,
                    'before' => $end_date,
                    'inclusive' => true,
                ),
            )
        );
        $vendor_all_orders = apply_filters('wcmp_datatable_get_vendor_all_orders', wcmp_get_orders($args), $requestData, $_POST);

        if (isset($requestData['order_status']) && $requestData['order_status'] != 'all' && $requestData['order_status'] != '') {
            foreach ($vendor_all_orders as $key => $id) {
                if (get_post_status($id) != $requestData['order_status']) {
                    unset($vendor_all_orders[$key]);
                }
            }
        }
        $vendor_orders = array_slice($vendor_all_orders, $requestData['start'], $requestData['length']);
        $data = array();

        foreach ($vendor_orders as $order_id) {
            $order = wc_get_order($order_id);
            $vendor_order = wcmp_get_order($order_id);
            if ($order) {
                if (in_array($order->get_status(), array('draft', 'trash')))
                    continue;
                $actions = array();
                $is_shipped = (array) get_post_meta($order->get_id(), 'dc_pv_shipped', true);
                if (!in_array($vendor->id, $is_shipped)) {
                    $mark_ship_title = __('Mark as shipped', 'dc-woocommerce-multi-vendor');
                } else {
                    $mark_ship_title = __('Shipped', 'dc-woocommerce-multi-vendor');
                }
                $actions['view'] = array(
                    'url' => esc_url(wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders'), $order->get_id())),
                    'icon' => 'ico-eye-icon action-icon',
                    'title' => __('View', 'dc-woocommerce-multi-vendor'),
                );
                if (apply_filters('can_wcmp_vendor_export_orders_csv', true, get_current_vendor_id())) :
                    $actions['wcmp_vendor_csv_download_per_order'] = array(
                        'url' => admin_url('admin-ajax.php?action=wcmp_vendor_csv_download_per_order&order_id=' . $order->get_id() . '&nonce=' . wp_create_nonce('wcmp_vendor_csv_download_per_order')),
                        'icon' => 'ico-download-icon action-icon',
                        'title' => __('Download', 'dc-woocommerce-multi-vendor'),
                    );
                endif;
                if ($vendor->is_shipping_enable()) {
                    $vendor_shipping_method = get_wcmp_vendor_order_shipping_method($order->get_id(), $vendor->id);
                    // hide shipping for local pickup
                    if ($vendor_shipping_method && !in_array($vendor_shipping_method->get_method_id(), apply_filters('hide_shipping_icon_for_vendor_order_on_methods', array('local_pickup')))) {
                        $actions['mark_ship'] = array(
                            'url' => '#',
                            'title' => $mark_ship_title,
                            'icon' => 'ico-shippingnew-icon action-icon'
                        );
                    }
                }
                $current_user_id = get_current_user_id();
                $table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_table_name();
                $query = "SELECT id FROM $table_name WHERE order_id = $order_id";
                $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);

                if (count($results) < 1) {
                    $actions['dispute'] = array(
                        'url' => esc_url(wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-dispute-list'), $order->get_id()) . '?add=' . $order_id),
                        'icon' => 'ico-failed-status-icon action-icon make_curcle_icon',
                        'title' => __('Create Dispute', 'dc-woocommerce-multi-vendor'),
                    );
                } else if ($results[0]['id']) {
                    $id_dispute = $results[0]['id'];
                    $actions['dispute'] = array(
                        'url' => esc_url(wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-dispute-list')) . '?view=' . $id_dispute),
                        'icon' => 'ico-failed-status-icon action-icon make_icon_red make_curcle_icon',
                        'title' => __('Show Dispute', 'dc-woocommerce-multi-vendor'),
                    );
                }
                $actions = apply_filters('wcmp_my_account_my_orders_actions', $actions, $order->get_id());
                $action_html = '';
                foreach ($actions as $key => $action) {
                    if ($key == 'mark_ship' && !in_array($vendor->id, $is_shipped)) {
                        $action_html .= '<a href="javascript:void(0)" title="' . $mark_ship_title . '" onclick="wcmpMarkeAsShip(this,' . $order->get_id() . ')"><i class="wcmp-font ' . $action['icon'] . '"></i></a> ';
                    } else if ($key == 'mark_ship') {
                        $action_html .= '<i title="' . $mark_ship_title . '" class="wcmp-font ' . $action['icon'] . '"></i> ';
                    } else {
                        $action_html .= '<a href="' . $action['url'] . '" title="' . $action['title'] . '"><i class="wcmp-font ' . $action['icon'] . '"></i></a> ';
                    }
                }
                $data[] = apply_filters('wcmp_datatable_order_list_row_data', array(
                    'select_order' => '<input type="checkbox" class="select_' . $order->get_status() . '" name="selected_orders[' . $order->get_id() . ']" value="' . $order->get_id() . '" />',
                    'order_id' => $order->get_id(),
                    'order_date' => wcmp_date($order->get_date_created()),
                    'vendor_earning' => ($vendor_order->get_commission_total()) ? $vendor_order->get_commission_total() : '-',
                    'order_status' => esc_html(wc_get_order_status_name($order->get_status())), //ucfirst($order->get_status()),
                    'action' => apply_filters('wcmp_vendor_orders_row_action_html', $action_html, $actions)
                        ), $order);
            }
        }
        $json_data = array(
            "draw" => intval($requestData['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
            "recordsTotal" => intval(count($vendor_all_orders)), // total number of records
            "recordsFiltered" => intval(count($vendor_all_orders)), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data   // total data array
        );
        wp_send_json($json_data);
    }

    public function wcmp_datatable_get_vendor_dispute_list_wnw() {
        global $wpdb, $WCMp;
        $requestData = $_REQUEST;
        $vendor = get_current_vendor();




        // print_r($_POST);
        $limit = $_POST['length'];
        $start_date = date('Y-m-d H:i:d', strtotime($_POST['start_date'] . ' 00:00:00'));
        $end_date = date('Y-m-d H:i:d', strtotime($_POST['end_date'] . ' 23:59:59'));

        $search = $_POST['search']['value'];
        $where = '';
        $table_name_status = BuyLockSmithDealsCustomizationAddon::blsd_status_table_name();
        $table_name = BuyLockSmithDealsCustomizationAddon::blsd_dispute_table_name();
        $table_name_message_table = BuyLockSmithDealsCustomizationAddon::blsd_dispute_message_table_name();
        if ($search != '') {
            $where = " and ($table_name.id Like '%$search%' OR "
                    . " (select count(id) from  $table_name_message_table where $table_name_message_table.dispute_id=$table_name.id and title Like '%$search%' limit 1)"
                    . " OR $table_name_status.name Like '%$search%'  )  ";
        }

        $vendor_id = $vendor->id;


        if (isset($requestData['dispute_status']) && $requestData['dispute_status'] != 'all' && $requestData['dispute_status'] != '') {
            $dispute_status = $requestData['dispute_status'];
            $where .= " and $table_name.status = $dispute_status ";
        }


        $query = "SELECT $table_name.*,$table_name_status.name as status_name "
                . ", (select title from  $table_name_message_table where $table_name_message_table.dispute_id=$table_name.id limit 1) as title FROM $table_name"
                . " inner join $table_name_status on $table_name_status.id=$table_name.status "
                . " WHERE (user_id=$vendor_id or who_opose_user_id=$vendor_id) $where  and $table_name.created_at BETWEEN '$start_date' AND '$end_date'";
        $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);

        $query = $query . " Limit $limit";
        //   $vendor_all_orders = apply_filters('wcmp_datatable_get_vendor_all_orders', wcmp_get_orders($args), $requestData, $_POST);
        $vendor_all_orders = count($results);


        //  $vendor_orders = array_slice($vendor_all_orders, $requestData['start'], $requestData['length']);
        $vendor_disputes = $results;
        $data = array();

        foreach ($vendor_disputes as $order_id) {



            $actions = array();


            $actions['view'] = array(
                'url' => esc_url(wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders'), $order_id['id'])),
                'icon' => 'ico-eye-icon action-icon',
                'title' => __('View', 'dc-woocommerce-multi-vendor'),
            );

            if (apply_filters('can_wcmp_vendor_export_orders_csv', true, get_current_vendor_id())) :
                $actions['wcmp_vendor_csv_download_per_order'] = array(
                    'url' => admin_url('admin-ajax.php?action=wcmp_vendor_csv_download_per_order&order_id=' . $order_id['id'] . '&nonce=' . wp_create_nonce('wcmp_vendor_csv_download_per_order')),
                    'icon' => 'ico-download-icon action-icon',
                    'title' => __('Download', 'dc-woocommerce-multi-vendor'),
                );
            endif;


            $actions = apply_filters('wcmp_my_account_my_orders_actions', $actions, $order_id['id']);
            $action_html = '';

            $data[] = apply_filters('wcmp_datatable_order_list_row_data', array(
                'select_order' => '<input type="checkbox" class="select_' . $order_id['id'] . '" name="selected_orders[' . $order_id['id'] . ']" value="' . $order_id['id'] . '" />',
                'dispute_id' => $order_id['id'],
                'title' => $order_id['title'],
                'status' => $order_id['status_name'],
                'created_at' => date('F d, Y H:i:s', strtotime($order_id['created_at'])), //ucfirst($order->get_status()),
                'action' => '<a href="' . esc_url(wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-dispute-list')) . '?view=' . $order_id['id']) . '"><i class="wcmp-font ico-eye-icon action-icon"></i></a>'
                    ), $order);
        }
        $json_data = array(
            "draw" => intval($requestData['draw']), // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
            "recordsTotal" => intval(count($vendor_all_orders)), // total number of records
            "recordsFiltered" => intval(count($vendor_all_orders)), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data   // total data array
        );
        wp_send_json($json_data);
    }

    function get_stats_top_selling() {
        $selectedlistLength = $_REQUEST['selectedlistLength'];
        $product_final = $products = $this->get_top_selling_deals($selectedlistLength);
         if (count($products)) {
            $this->aasort($products, "total_sales");
            $products = array_reverse($products);
            $products = array_slice($products, 0, $selectedlistLength);
        }
        
       $category_list =  $this->get_top_selling_category($product_final);
          if (count($category_list)) {
            $this->aasort($category_list, "total_sales");
            
            $category_list = array_reverse($category_list);
            $category_list = array_slice($category_list, 0, $selectedlistLength);
        }
        
        $resultFinal = ['products' => $products, 'category' => $category_list];
        echo json_encode($resultFinal);
        wp_die();
    }

    function get_top_selling_deals($selectedlistLength) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'posts';
        $totalDealsIds = BuyLockSmithDealsCustomizationAdminReport::totalDealsIds();
        $products = [];
        foreach ($totalDealsIds as $ids) {
            $query = "SELECT $table_name.post_title, $table_name.ID,  (SELECT meta_value from wp_postmeta WHERE (meta_key='total_sales' and post_id=wp_posts.ID)) as total_sales"
                    . " FROM $table_name"
                    . " WHERE ID in (SELECT post_id from wp_postmeta WHERE (meta_key='_vendor_product_parent' and meta_value='$ids'))";
            $results = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);

            $total_sales = 0;
            $title = '';
            foreach ($results as $result) {
                $title = $result['post_title'];
                $total_sales = $total_sales + $result['total_sales'];
            }
            $products[] = ['title' => $title, 'total_sales' => $total_sales, 'ID' => $ids];
        }
       
        return $products;
    }

    function aasort(&$array, $key) {
        $sorter = array();
        $ret = array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii] = $va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii] = $array[$ii];
        }
        $array = $ret;
    }
    
    
    function get_top_selling_category($product_final){
        if(count($product_final)){
       $product_categories = $this->blsd_get_product_cat();
       
     //  print_r($product_categories);
       $final_category_order_count_array = [];
      //  print_r($product_final); exit;
        foreach($product_final as $product){
            $terms = wp_get_post_terms( $product['ID'], 'product_cat');
            
            if(count($product_categories)){
                foreach($product_categories as $categories){
                    if(count($terms)>0){
                        foreach($terms as $term){
                            
                           
                    if($categories->term_id==$term->term_id){
                        if(isset($final_category_order_count_array[$categories->term_id])){
                         $final_category_order_count_array[$categories->term_id] = $final_category_order_count_array[$categories->term_id]+ $product['total_sales'];  
                        }else{
                        $final_category_order_count_array[$categories->term_id] = $product['total_sales'];
                        }
                    }
                        }
                    }
                }
            }
            
        }
        $array_with_details = [];
        if (count($final_category_order_count_array)) {
            
            foreach($final_category_order_count_array as $keycat=> $category_detail){
                
                $term = get_term_by('term_id',$keycat, 'product_cat');
                
                $name = $term->name;
                $array_with_details[]=['term_id'=>$keycat, 'name'=>$name, 'total_sales'=>$category_detail];
            }
            
      
          
        }
        
        
        return $array_with_details;
        }else{
            return [];
        }
        
    }
    
    
    function blsd_get_product_cat(){
        $orderby = 'name';
$order = 'asc';
$hide_empty = false ;
$cat_args = array(
    'orderby'    => $orderby,
    'order'      => $order,
    'hide_empty' => $hide_empty,
);
 
return $product_categories = get_terms( 'product_cat', $cat_args );
    }
    
    
    function blsd_slug_duplicate_status(){
       $status = true;
       if(isset($_REQUEST['slug'])){
           if($_REQUEST['slug']!=''){
               $slug = $_REQUEST['slug'];
               global $wpdb;
               $table_name = $wpdb->prefix.'usermeta';
               $user_id = get_current_user_id();
               $query = "Select * from $table_name where user_id<>$user_id and meta_key='_vendor_page_slug' and meta_value='$slug'";
               
                   $results_total = $users = $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
                   if(count($results_total)){
                       $status  = false;
                   }
               
           }
           
       }
         echo json_encode(['status'=>$status]);
        wp_die();
    }
    
    
     function blsd_get_deals_by_category(){
          $current_vendor = get_current_user_id();
      if(isset($_REQUEST['category'])){
          $category = $_REQUEST['category'];
          $termQuery = '';
          if($_REQUEST['category']!=''){
              
             $termQuery =  array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => array($category),
                'operator' => 'IN',
            )
         );
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
                        'tax_query' => $termQuery
                    );

                    $postsFound = get_posts($args);
      }
         echo json_encode($postsFound);
        wp_die();
    }
    
}
