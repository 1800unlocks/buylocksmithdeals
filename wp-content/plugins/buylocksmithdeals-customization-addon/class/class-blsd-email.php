<?php

defined('ABSPATH') || exit;

/**
 * Main BuyLockSmithDealsCustomizationAddon Class.
 *
 * @class BuyLockSmithDealsCustomizationAddon
 */
class BuyLockSmithDealsCustomizationEmail {

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

    public function init_hooks() {
        // add_action('pre_get_posts', array($this, 'target_main_conditional_product_list'), 100, 1);
        // add_filter( 'woocommerce_related_products', array($this, 'woocommerce_related_products_function'),100,3 );
    }

    public static function blsd_email_vendor_product_assign_status($product_id, $status, $vendor_id) {

        $product = get_post($product_id);
        $vendor = get_user_by('ID', $vendor_id);

        if (isset($vendor->user_email) && isset($product->ID)) {

            $to = $vendor->user_email;
            $first_name = get_user_meta($vendor_id, 'first_name', true);
            $last_name = get_user_meta($vendor_id, 'last_name', true);
            $name = $first_name . ' ' . $last_name;
            if (trim($name) == '') {
                $name = $vendor->user_nicename;
            }
            $post_title = $product->post_title;
            $subject = 'Product ' . $status . ' ' . ' by admin';
           // $message = 'Hello ' . $name . ',';
           // $message .= '</br></br>';
            $message .= $post_title . ' ' . $status . ' ' . ' by admin.';
            $message .= '</br>';
            $message .= 'Please visit in your account.';


            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
            //   $headers .= "From: MyPlugin <" . $this->settings['from_address'] . ">" . "\r\n";

            $body='';
            $body .='<tr><td>';
            $body .='<p>Message : '.$message.'</p>';
            $body .='</br></br>';
            $body .='<p>Product detail : '.$post_title.'</p>';
            
            $body .='</td></tr>';
            
             /*************************************/
                   
                    $mailer = WC()->mailer();
                    $template_html  = '/emails/all_custom_mail_template.php';
                    $recipient = $to;
                    $subject = __($subject);
                    $attachments=[];
                     $content = wc_get_template_html(
				$template_html,
				array(
					'user_id'              => $vendor_id,
					'email_heading'      => '{vendor_logo}',
					'additional_content' => '',
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $mailer,
                                        'name'               => $name,
                                        'mail_heading'       => '',
                                        'body'               => $body,
				)
			); 
                        $headers = "Content-Type: text/html\r\n";
                        //send the email through wordpress
                        $mailer->send( $recipient, $subject, $content, $headers, $attachments );
            
            
//            wp_mail($to, $subject, $message, $headers);
        }
    }

    public static function blsd_email_on_submit_dispute($order, $customer_id, $title, $message_dispute, $username, $phone_number, $email, $order_number) {

        $order_id = $order->get_order_number();
        $order_post = get_post($order_id);
        $parent_id = wp_get_post_parent_id($order_post);
        $product_table = '<table style="width:100%;border-collapse: collapse;" border="1">
        <tr>
          <th>Order ID</th>
          <th>Parent Order ID</th>
          <th>Product</th>
        </tr>';
        $order_items = $order->get_items();
        foreach ($order->get_items() as $item_id => $item_data) {
            // Get an instance of corresponding the WC_Product object
            $product = $item_data->get_product();
              if($product!=''){
                $product_name = $product->get_name(); // Get the product name
            }else{
                global $wpdb;
                $table_name = $wpdb->prefix.'woocommerce_order_items';
                 $query = "SELECT order_item_name FROM $table_name WHERE order_item_id=$item_id";
                    $results_order_item = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
                   if(count($results_order_item)){
                       $product_name = $results_order_item[0]['order_item_name'];
                   }
            }

            $item_quantity = $item_data->get_quantity(); // Get the item quantity

            $item_total = $item_data->get_total(); // Get the item line total

            $product_table .= "<tr><td>$order_id</td>";
            $product_table .= "<td>$parent_id</td>";
            $product_table .= "<td> $product_name X $item_quantity = ". number_format((float)$item_total, 2, '.', ''). " </td></tr>";
        }
            $product_table .= '
        </table>';


        $product = get_post($product_id);
        $customer = get_user_by('ID', $customer_id);
        
        if (isset($customer->user_email) && isset($product->ID)) {
            $to = $customer->user_email;
            $first_name = get_user_meta($customer_id, 'first_name', true);
            $last_name = get_user_meta($customer_id, 'last_name', true);
            $name = $first_name . ' ' . $last_name;
            if (trim($name) == '') {
                $name = $customer->user_nicename;
            }
            $post_title = $product->post_title;
            $subject = 'New Dispute on #' . $order_id;
          //  $message = 'Hello ' . $name . ',';
           /*  $message  = '';
            $message .= 'Dispute details are as below.'.'<br>';
            $message .= '</br></br>';
			$message .= 'Name: ' . $username.'<br>';
			$message .= '</br></br>';
            $message .= 'Phone Number: ' . $phone_number.'<br>';
			$message .= '</br></br>';
            $message .= 'Email: ' . $email.'<br>';
			$message .= '</br></br>';
            $message .= 'Order Number: ' . $order_number.'<br>';
			$message .= '</br></br>';
            $message .= 'Dispute Title: ' . $title.'<br>';
            $message .= '</br></br>';
            $message .= 'Dispute Message: ' . $message_dispute.'<br>';
            $message .= '</br></br>';
            $message .= 'Product detail: ';
            $message .= '</br></br>';
            $message .= $product_table;
            $message .= '</br></br>';

            $message .= '</br>';
            $message .= 'Please visit in your account.'; */
            
            $body='';
            $body .='<tr><td>';
            $body .='<span>Name:'.$username.'</span>'.'<br>';
            $body .='<span>Phone Number:'.$phone_number.'</span>'.'<br>';
            $body .='<span>Email:'.$email.'</span>'.'<br>';
            $body .='<span>Order Number:'.$order_number.'</span>'.'<br>';
            $body .='<span>Dispute Title:'.$title.'</span>'.'<br>';
            $body .='<span>Dispute Message:'.$message_dispute.'</span>'.'<br>';
            $body .='</br></br>';
            $body .='Product detail:';
            $body .=$product_table;
            
            $body .='</td></tr>';
            $body .='Please visit in your account.';
            
             /*************************************/
                    $parent_order_id = $order->get_parent_id();
                    $parent_order = wc_get_order( $parent_order_id );
                    $mailer = WC()->mailer();
                    $template_html  = '/emails/all_custom_mail_template.php';
                    $recipient = $customer->user_email;
                    $subject = __('New Dispute on #' . $order_id, 'theme_name');
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
					'name'               => $name,
					'mail_heading'       => $mail_heading,
					'body'               => $body,
				)
			); 
                        $headers = "Content-Type: text/html\r\n";

                        //send custom email blsd
                        do_action( 'vendor_dispute_email', $parent_order_id );

                        //send the email through wordpress
                        // $mailer->send( $recipient, $subject, $content, $headers, $attachments );
        
            /*************************************/
            
            


            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
            //   $headers .= "From: MyPlugin <" . $this->settings['from_address'] . ">" . "\r\n";

           // wp_mail($to, $subject, $message, $headers);
        }


          
        $super_admin_list = self::get_all_admin_list();

        foreach ($super_admin_list as $super_admin) {

            $to = $super_admin->user_email;
            $admin_id = $super_admin->ID;
            $name = $super_admin->display_name;
            $post_title = $product->post_title;
            $subject = 'New Dispute on  #' . $order_id;
          //  $message = 'Hello ' . $name . ',';
            $body='';
            $body .='<tr><td>';
            $body .='<span>Name:'.$username.'</span>'.'<br>';
            $body .='<span>Phone Number:'.$phone_number.'</span>'.'<br>';
            $body .='<span>Email:'.$email.'</span>'.'<br>';
            $body .='<span>Order Number:'.$order_number.'</span>'.'<br>';
            $body .='<span>Dispute Title:'.$title.'</span>'.'<br>';
            $body .='<span>Dispute Message:'.$message_dispute.'</span>'.'<br>';
            $body .='</br></br>';
            $body .='Product detail:';
            $body .=$product_table;
            
            $body .='</td></tr>';
            //$body .='Please visit in your account.';

            
              /****************************/
              
                    $mailer = WC()->mailer();
                    $template_html  = '/emails/all_custom_mail_template.php';
                    $recipient = $to;
                    $subject = __($subject);
                    $attachments=[];
                     $content = wc_get_template_html(
				$template_html,
				array(
					'user_id'              => $admin_id,
					'email_heading'      => '{vendor_logo}',
					'additional_content' => '',
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $mailer,
					'name'               => $name,
					'mail_heading'       => '',
					'body'               => $body,
				)
			); 
			$headers = "Content-Type: text/html\r\n";

            //send custom email blsd
            do_action( 'vendor_dispute_email', $parent_order_id, $admin_id);

			//send the email through wordpress
			// $mailer->send( $recipient, $subject, $content, $headers, $attachments );
            
            /************************************/
          //  wp_mail($to, $subject, $message, $headers);
        }
    }

    public static function blsd_email_on_new_message_dispute($order, $customer_id, $message_dispute, $lastid_dispute_id) {

        $order_id = $order->get_order_number();
        $order_post = get_post($order_id);
        $parent_id = wp_get_post_parent_id($order_post);
        $product_table = '<table style="width: 100%;border-collapse: collapse;" border="1">
 <tr>
   <th>Order ID</th>
   <th>Parent Order ID</th>
   <th>Product</th>
 </tr>';
        $order_items = $order->get_items();
        foreach ($order->get_items() as $item_id => $item_data) {
            // Get an instance of corresponding the WC_Product object
            $product = $item_data->get_product();
            
            if($product!=''){
                $product_name = $product->get_name(); // Get the product name
            }else{
                global $wpdb;
                $table_name = $wpdb->prefix.'woocommerce_order_items';
                 $query = "SELECT order_item_name FROM $table_name WHERE order_item_id=$item_id";
                    $results_order_item = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
                   if(count($results_order_item)){
                       $product_name = $results_order_item[0]['order_item_name'];
                   }
            }
            
            
            
            $item_quantity = $item_data->get_quantity(); // Get the item quantity

            $item_total = $item_data->get_total(); // Get the item line total

            $product_table .= "<tr><td>$order_id</td>";
            $product_table .= "<td>$parent_id</td>";
            $product_table .= "<td> $product_name X $item_quantity = $item_total </td></tr>";
        }
        $product_table .= '
</table>';


        $product = get_post($product_id);
        $customer = get_user_by('ID', $customer_id);

        if (isset($customer->user_email) && isset($product->ID)) {

            $to = $customer->user_email;
            $first_name = get_user_meta($customer_id, 'first_name', true);
            $last_name = get_user_meta($customer_id, 'last_name', true);
            $name = $first_name . ' ' . $last_name;
            if (trim($name) == '') {
                $name = $customer->user_nicename;
            }
            $post_title = $product->post_title;
            $subject = 'New Message on Dispute #' . $lastid_dispute_id;
          //  $message = 'Hello ' . $name . ',';
            $message .= '</br></br>';
            $message .= 'Dispute details are as below.'.'<br>';
            $message .= '</br></br>';
            // $message .='Dispute Title: '.$title;
            //$message .='</br></br>';
            $message .= 'Dispute Message: ' . $message_dispute.'<br>';
            $message .= '</br></br>';
            $message .= 'Product detail: ';
            $message .= '</br></br>';
            $message .= $product_table;
            $message .= '</br></br>';

            $message .= '</br>';
            $message .= 'Please visit in your account.';


            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
            //   $headers .= "From: MyPlugin <" . $this->settings['from_address'] . ">" . "\r\n";

              /****************************/
              
                    $mailer = WC()->mailer();
                    $template_html  = '/emails/all_custom_mail_template.php';
                    $recipient = $to;
                    $subject = __($subject);
                    $attachments=[];
                     $content = wc_get_template_html(
				$template_html,
				array(
					'user_id'              => $vendor_id,
					'email_heading'      => '{vendor_logo}',
					'additional_content' => '',
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $mailer,
                                        'name'               => $name,
                                        'mail_heading'       => '',
                                        'body'               => $message,
				)
			); 
                        $headers = "Content-Type: text/html\r\n";
                        //send the email through wordpress
                        $mailer->send( $recipient, $subject, $content, $headers, $attachments );
            
            /************************************/
            
           // wp_mail($to, $subject, $message, $headers);
        }



        $super_admin_list = self::get_all_admin_list();

        foreach ($super_admin_list as $super_admin) {

            $to = $super_admin->user_email;
            $name = $super_admin->display_name;
            $post_title = $product->post_title;
            $subject = 'New Message on Dispute #' . $lastid_dispute_id;
            $message = 'Hello ' . $name . ',';
            $message .= '</br></br>';
            $message .= 'Dispute details are as below.'.'<br>';
            $message .= '</br></br>';
            //$message .='Dispute Title: '.$title;
            //$message .='</br></br>';
            $message .= 'Dispute Message: ' . $message_dispute.'<br>';
            $message .= '</br></br>';
            $message .= 'Product detail: ';
            $message .= '</br></br>';
            $message .= $product_table;
            $message .= '</br></br>';

            $message .= '</br>';
            $message .= 'Please visit in your account.';
            
               /****************************/
              
                    $mailer = WC()->mailer();
                    $template_html  = '/emails/all_custom_mail_template.php';
                    $recipient = $to;
                    $subject = __($subject);
                    $attachments=[];
                     $content = wc_get_template_html(
				$template_html,
				array(
					'user_id'              => $vendor_id,
					'email_heading'      => '{vendor_logo}',
					'additional_content' => '',
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $mailer,
                                        'name'               => $name,
                                        'mail_heading'       => '',
                                        'body'               => $message,
				)
			); 
                        $headers = "Content-Type: text/html\r\n";
                        //send the email through wordpress
                        $mailer->send( $recipient, $subject, $content, $headers, $attachments );
            
            /************************************/
            
         //   wp_mail($to, $subject, $message, $headers);
        }
    }
    public static function blsd_email_on_admin_action_for_dispute($order, $who_opose_user_id,$user_id, $message_dispute, $lastid_dispute_id, $status, $who_won_user_id) {

        $order_id = $order->get_order_number();
        $order_post = get_post($order_id);
        $parent_id = wp_get_post_parent_id($order_post);
        $product_table = '<table style="width:100%;border-collapse: collapse;" border="1">
 <tr>
   <th>Order ID</th>
   <th>Parent Order ID</th>
   <th>Product</th>
 </tr>';
        $order_items = $order->get_items();
        foreach ($order->get_items() as $item_id => $item_data) {
            // Get an instance of corresponding the WC_Product object
            $product = $item_data->get_product();
              if($product!=''){
                $product_name = $product->get_name(); // Get the product name
            }else{
                global $wpdb;
                $table_name = $wpdb->prefix.'woocommerce_order_items';
                 $query = "SELECT order_item_name FROM $table_name WHERE order_item_id=$item_id";
                    $results_order_item = (array) $wpdb->get_results($wpdb->prepare($query, $type), ARRAY_A);
                   if(count($results_order_item)){
                       $product_name = $results_order_item[0]['order_item_name'];
                   }
            }

            $item_quantity = $item_data->get_quantity(); // Get the item quantity

            $item_total = $item_data->get_total(); // Get the item line total

            $product_table .= "<tr><td>$order_id</td>";
            $product_table .= "<td>$parent_id</td>";
            $product_table .= "<td> $product_name X $item_quantity = $item_total </td></tr>";
        }
        $product_table .= '
</table>';


        $product = get_post($product_id);
       
        $customer = get_user_by('ID', $who_opose_user_id);
  
        if (isset($customer->user_email)) {
          
            $to = $customer->user_email;
            $first_name = get_user_meta($who_opose_user_id, 'first_name', true);
            $last_name = get_user_meta($who_opose_user_id, 'last_name', true);
            $name = $first_name . ' ' . $last_name;
            if (trim($name) == '') {
                $name = $customer->user_nicename;
            }
            $post_title = $product->post_title;
            $subject = 'Admin update on Dispute #' . $lastid_dispute_id;
            //$message = 'Hello ' . $name . ',';
            $message .= '</br></br>';
            $message .= 'Dispute details are as below.'.'<br>';
            $message .= '</br></br>';
            $message .='Status: '.BuyLockSmithDealsCustomizationAddon::blsd_get_status_name_by_id($status).'<br>';
            $message .='</br></br>';
            if($who_won_user_id!=0){
                if($who_won_user_id==$who_opose_user_id){
          $inFavor = 'You';
            }else{
             $first_name = get_user_meta($user_id, 'first_name', true);
            $last_name = get_user_meta($user_id, 'last_name', true);
            $name = $first_name . ' ' . $last_name;
            if (trim($name) == '') {
                $use_winner = get_user_by('ID', $user_id);
                $name = $use_winner->user_nicename;
            }  
            $inFavor = $name;
            }
              $message .='Dispute in Favour of : '.$inFavor;
            $message .='</br></br>';
            }
            $message .= 'Dispute Message: ' . $message_dispute.'<br>';
            $message .= '</br></br>';
            $message .= 'Product detail: ';
            $message .= '</br></br>';
            $message .= $product_table;
            $message .= '</br></br>';

            $message .= '</br>';
            $message .= 'Please visit in your account.';


            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
            //   $headers .= "From: MyPlugin <" . $this->settings['from_address'] . ">" . "\r\n";

              /****************************/
              
                    $mailer = WC()->mailer();
                    $template_html  = '/emails/all_custom_mail_template.php';
                    $recipient = $to;
                    $subject = __($subject);
                    $attachments=[];
                     $content = wc_get_template_html(
				$template_html,
				array(
					'user_id'              => $user_id,
					'email_heading'      => '{vendor_logo}',
					'additional_content' => '',
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $mailer,
                                        'name'               => $name,
                                        'mail_heading'       => '',
                                        'body'               => $message,
				)
			); 
                        $headers = "Content-Type: text/html\r\n";
                        //send the email through wordpress
                        $mailer->send( $recipient, $subject, $content, $headers, $attachments );
            
            /************************************/
                
          //  wp_mail($to, $subject, $message, $headers);
            
            /////***/////
            
            
            $customer_user = get_user_by('ID', $user_id);
            $to_user = $customer_user->user_email;
            $first_name = get_user_meta($user_id, 'first_name', true);
            $last_name = get_user_meta($user_id, 'last_name', true);
            $name = $first_name . ' ' . $last_name;
            if (trim($name) == '') {
                $name = $customer_user->user_nicename;
            }
            $post_title = $product->post_title;
            $subject = 'Admin update on Dispute #' . $lastid_dispute_id;
            $message = '';
            $message .= '</br></br>';
            $message .= 'Dispute details are as below.'.'<br>';
            $message .= '</br></br>';
            $message .='Status: '.BuyLockSmithDealsCustomizationAddon::blsd_get_status_name_by_id($status).'<br>';
            $message .='</br></br>';
            if($who_won_user_id!=0){
                if($who_won_user_id==$user_id){
                    $inFavor = 'You';
            }else{
             $first_name = get_user_meta($who_opose_user_id, 'first_name', true);
            $last_name = get_user_meta($who_opose_user_id, 'last_name', true);
            $name = $first_name . ' ' . $last_name;
            if (trim($name) == '') {
                $use_winner = get_user_by('ID', $who_opose_user_id);
                $name = $use_winner->user_nicename;
            }  
            $inFavor = $name;
            }
              $message .='Dispute in Favour of : '.$inFavor;
            $message .='</br></br>';
            }
            $message .= 'Dispute Message: ' . $message_dispute.'<br>';
            $message .= '</br></br>';
            $message .= 'Product detail: ';
            $message .= '</br></br>';
            $message .= $product_table;
            $message .= '</br></br>';

            $message .= '</br>';
            $message .= 'Please visit in your account.';


            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
            //   $headers .= "From: MyPlugin <" . $this->settings['from_address'] . ">" . "\r\n";

                  /****************************/
              
                    $mailer = WC()->mailer();
                    $template_html  = '/emails/all_custom_mail_template.php';
                    $recipient = $to;
                    $subject = __($subject);
                    $attachments=[];
                     $content = wc_get_template_html(
				$template_html,
				array(
					'user_id'              => $user_id,
					'email_heading'      => '{vendor_logo}',
					'additional_content' => '',
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $mailer,
                                        'name'               => $name,
                                        'mail_heading'       => '',
                                        'body'               => $message,
				)
			); 
                        $headers = "Content-Type: text/html\r\n";
                        //send the email through wordpress
                        $mailer->send( $recipient, $subject, $content, $headers, $attachments );
            
            /************************************/
          // wp_mail($to_user, $subject, $message, $headers);
          
        }
       
    }

    public static function get_all_admin_list() {
        $args = array(
            'role' => 'administrator',
            'orderby' => 'user_nicename',
            'order' => 'ASC',
        );
        return $administrator = get_users($args);
    }

    public static function blsd_email_send_vendor_social_links_for_review($social_links, $customer_id) {


        $customer = get_user_by('ID', $customer_id);

        if (isset($customer->user_email)) {

            $to = $customer->user_email;
            $first_name = get_user_meta($customer_id, 'first_name', true);
            $last_name = get_user_meta($customer_id, 'last_name', true);
            $name = $first_name . ' ' . $last_name;
            if (trim($name) == '') {
                $name = $customer->user_nicename;
            }
            $message = '';
            $post_title = $product->post_title;
            $subject = 'Review recently purchased on vendor social sites.';
         //   $message .= 'Hello ' . $name . ',';
         //   $message .= '</br></br>';
            $message .= 'Please see below the list of social links below and start review the vendor.';
            $message .= '</br></br>';
            foreach ($social_links as $key => $links) {
                if ($links != '') {
                    $message .= '<br>'.'<a href="' . $links . '">' . ucfirst($key) . '</a></br>';
                    $message .= '</br>';
                }
            }



            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=" . get_bloginfo('charset') . "" . "\r\n";
            //   $headers .= "From: MyPlugin <" . $this->settings['from_address'] . ">" . "\r\n";
                
            /****************************/
              
                    $mailer = WC()->mailer();
                    $template_html  = '/emails/all_custom_mail_template.php';
                    $recipient = $to;
                    $subject = __($subject);
                    $attachments=[];
                     $content = wc_get_template_html(
				$template_html,
				array(
					'user_id'              => $vendor_id,
					'email_heading'      => '{vendor_logo}',
					'additional_content' => '',
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $mailer,
                                        'name'               => $name,
                                        'mail_heading'       => '',
                                        'body'               => $message,
				)
			); 
                        $headers = "Content-Type: text/html\r\n";
                        //send the email through wordpress
                        $mailer->send( $recipient, $subject, $content, $headers, $attachments );
            
            /************************************/
            
           // wp_mail($to, $subject, $message, $headers);
        }
    }

}

?>