<?php 
/**
 * Custom Email
 *
 * An email sent to the admin when an order status is changed to Pending Payment.
 * 
 * @class       Custom_Email
 * @extends     WC_Email
 *
 */
class Blsd_Customer_Review_Email extends WC_Email {
    
    function __construct() {
        
        // Add email ID, title, description, heading, subject
        $this->id                   = 'blsd_customer_review_email';
        $this->title                = __( 'Blsd Customer Review Email', 'custom-blsd-review-email' );
        $this->description          = __( 'This email is received when an order status is changed to Pending.', 'custom-blsd-review-email' );
        
        $this->heading              = __( 'Blsd Customer Review Email', 'custom-blsd-review-email' );
        $this->subject              = __( '[Buy Locksmith Deals] Review recently purchased products', 'custom-blsd-review-email' );
        
        // email template path
        $this->template_html    = 'emails/custom-blsd-review-email.php';
        $this->template_plain   = 'emails/plain/custom-blsd-review-email.php';
        
        // Triggers for this email
        add_action( 'custom_review_email_notification', array( $this, 'trigger' ) );
        
        // Call parent constructor
        parent::__construct();
        
        // Other settings
        $this->template_base = CUSTOM_TEMPLATE_PATH;

        // default the email recipient to the admin's email address
        $this->recipient     = $this->get_option( 'recipient', get_option( 'admin_email' ) );
        // $this->recipient     = 'ravi.webnware@gmail.com';
        
    }
    
    public function queue_notification( $order_id ) {
        
        $order = new WC_order( $order_id );
        $items = $order->get_items();
        // foreach item in the order
        foreach ( $items as $item_key => $item_value ) {
            // add an event for the item email, pass the item ID so other details can be collected as needed
            wp_schedule_single_event( time(), 'custom_item_email', array( 'item_id' => $item_key ) );
        }
    }
    
    // This function collects the data and sends the email
    function trigger( $order , $is_admin = '' ) {
        global $WCMp,$wpdb;
        $send_email = true;
        $this->object = wc_get_order( $order );
        $order = $this->object;

		if ( version_compare( '3.0.0', WC()->version, '>' ) ) {
			$order_email = $this->object->billing_email;
		} else {
			$order_email = $this->object->get_billing_email();
		}

        if($is_admin != ''){
            $this->recipient = $is_admin;
        }else{
            $this->recipient = $order_email;
        }
    
        // if no recipient is set, do not send the email
        if ( ! $this->get_recipient() ) {
            return;
        }
        // send the email
        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

        
    }
    
    // Create an object with the data to be passed to the templates
    public static function create_object( $item_id ) {
    
        global $wpdb;
    
        $item_object = new stdClass();
        
        // order ID
        $query_order_id = "SELECT order_id FROM `". $wpdb->prefix."woocommerce_order_items`
                            WHERE order_item_id = %d";
        $get_order_id = $wpdb->get_results( $wpdb->prepare( $query_order_id, $item_id ) );
    
        $order_id = 0;
        if ( isset( $get_order_id ) && is_array( $get_order_id ) && count( $get_order_id ) > 0 ) {
            $order_id = $get_order_id[0]->order_id;
        } 
        $item_object->order_id = $order_id;
    
        $order = new WC_order( $order_id );
    
        // order date
        $post_data = get_post( $order_id );
        $item_object->order_date = $post_data->post_date;
    
        // product ID
        $item_object->product_id = wc_get_order_item_meta( $item_id, '_product_id' );
    
        // product name
        $_product = wc_get_product( $item_object->product_id );
        $item_object->product_title = $_product->get_title();    

        // qty
        $item_object->qty = wc_get_order_item_meta( $item_id, '_qty' );
        
        // total
        $item_object->total = wc_price( wc_get_order_item_meta( $item_id, '_line_total' ) );

        // email adress
        $item_object->billing_email = ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) < 0 ) ? $order->billing_email : $order->get_billing_email();
    
        // customer ID
        $item_object->customer_id = ( version_compare( WOOCOMMERCE_VERSION, "3.0.0" ) < 0 ) ? $order->user_id : $order->get_user_id();
    
        return $item_object;
    
    }
    
    // return the html content
    function get_content_html() {
        ob_start();
        wc_get_template( $this->template_html, array(
            'order'         => $this->object,
			'email_heading' => $this->get_heading()
        ), '', $this->template_base );
        return ob_get_clean();
    }

    // return the plain content
    function get_content_plain() {
        ob_start();
        wc_get_template( $this->template_plain, array(
            'order'         => $this->object,
			'email_heading' => $this->get_heading()
            ), '', $this->template_base );
        return ob_get_clean();
    }
    
    // return the subject
    /*function get_subject() {
        
        $order = new WC_order( $this->object );
        return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->format_string( $this->subject ), $this->object );
        
    }
    
    // return the email heading
    public function get_heading() {
        
        $order = new WC_order( $this->object );
        return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $this->heading ), $this->object );
        
    }*/
    
    // form fields that are displayed in WooCommerce->Settings->Emails
    function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' 		=> __( 'Enable/Disable', 'custom-blsd-review-email' ),
                'type' 			=> 'checkbox',
                'label' 		=> __( 'Enable this email notification', 'custom-blsd-review-email' ),
                'default' 		=> 'yes'
            ),
            'recipient' => array(
                'title'         => __( 'Recipient', 'custom-blsd-review-email' ),
                'type'          => 'text',
                'description'   => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s', 'custom-blsd-review-email' ), get_option( 'admin_email' ) ),
                'default'       => get_option( 'admin_email' )
            ),
            'subject' => array(
                'title' 		=> __( 'Subject', 'custom-blsd-review-email' ),
                'type' 			=> 'text',
                'description' 	=> sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'custom-blsd-review-email' ), $this->subject ),
                'placeholder' 	=> '',
                'default' 		=> ''
            ),
            'heading' => array(
                'title' 		=> __( 'Email Heading', 'custom-blsd-review-email' ),
                'type' 			=> 'text',
                'description' 	=> sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'custom-blsd-review-email' ), $this->heading ),
                'placeholder' 	=> '',
                'default' 		=> ''
            ),
            'email_type' => array(
                'title' 		=> __( 'Email type', 'custom-blsd-review-email' ),
                'type' 			=> 'select',
                'description' 	=> __( 'Choose which format of email to send.', 'custom-blsd-review-email' ),
                'default' 		=> 'html',
                'class'			=> 'email_type',
                'options'		=> array(
                    'plain'		 	=> __( 'Plain text', 'custom-blsd-review-email' ),
                    'html' 			=> __( 'HTML', 'custom-blsd-review-email' ),
                    'multipart' 	=> __( 'Multipart', 'custom-blsd-review-email' ),
                )
            )
        );
    }
    
}
return new Blsd_Customer_Review_Email();
?>