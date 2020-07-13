<?php
/**
 * Customer job completion email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/customer-job-completion.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
//do_action( 'woocommerce_email_header', $email_heading, $email ); 

//global $email; 
// Get an instance of the WC_Order object
//$order = $email->object; 
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
		<title><?php echo get_bloginfo( 'name', 'display' ); ?></title>
	</head>
	<body <?php echo is_rtl() ? 'rightmargin' : 'leftmargin'; ?>="0" marginwidth="0" topmargin="0" marginheight="0" offset="0">
		<div id="wrapper" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
			<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
				<tr>
					<td align="center" valign="top">
						<div id="template_header_image">
                                                    <?php
                                                        
								if ( $img = get_option( 'woocommerce_email_header_image' ) ) {
									echo '<p style="margin-top:0;"><img src="' . esc_url( $img ) . '" alt="' . get_bloginfo( 'name', 'display' ) . '" /></p>';
								}
							?>
						</div>
						<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">
							<tr>
								<td align="center" valign="top">
									<!-- Header -->
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header">
										<tr>
											<td id="header_wrapper">
                                                                                            <?php 
                                                                                            if($email_heading == '{vendor_logo}'){ 
                                                                                                
                                                                                            $suborder_authorname=''; 
                                                                                            $vendor_business_name=''; 
                                                                                            $profile_image='';
                                                                                            $sub_orders = get_children( array('post_parent' => $order->get_id(), 'post_type' => 'shop_order' ) );
                                                                                            foreach($sub_orders as $sorder){
                                                                                                $suborder_id=$sorder->ID;
                                                                                                $suborder_authorid=$sorder->post_author;
                                                                                                $suborder_authorname=get_author_name( $suborder_authorid);
                                                                                                $vendor_business_name = get_user_meta($suborder_authorid, '_vendor_page_title', true);
                                                                                                $vendor_profile_image = get_user_meta($suborder_authorid, '_vendor_profile_image', true);
                                                                                                if (isset($vendor_profile_image) && $vendor_profile_image > 0){
                                                                                                    $profile_image = wp_get_attachment_url($vendor_profile_image);
                                                                                                }
                                                                                             }
                                                                                             if($profile_image == ''){
                                                                                             ?>
                                                                                             <h1><?php echo $suborder_authorname; ?></h1>
                                                                                            <?php
                                                                                             }
                                                                                             else{
                                                                                               ?>
                                                                                             <img src="<?php echo $profile_image; ?>">
                                                                                            <?php   
                                                                                             }
                                                                                            }
                                                                                            else{ ?>
                                                                                                 <h1><?php echo $email_heading; ?></h1>
                                                                                            <?php
                                                                                            }
                                                                                             ?>
                                                                                            
                                                                                            
                                                                                           
											</td>
										</tr>
									</table>
									<!-- End Header -->
								</td>
							</tr>
							<tr>
								<td align="center" valign="top">
									<!-- Body -->
									<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
										<tr>
											<td valign="top" id="body_content">
												<!-- Content -->
												<table border="0" cellpadding="20" cellspacing="0" width="100%">
													<tr>
														<td valign="top">
															<div id="body_content_inner">



<?php /* translators: %s: Customer first name */ ?>
<p><?php printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $order->get_billing_first_name() ) ); ?></p>
<?php /* translators: %s: Order number */ ?>
<?php // printf( esc_html__( 'How was your job by locksmith. &mdash; Please give your review on order #%s:', 'woocommerce' ), esc_html( $order->get_order_number() ) ); ?>

<p><?php printf( esc_html__( 'Thank you for trusting %s for all your security needs. We hoped you enjoyed the service. Please leave us feedback on our store.', 'woocommerce' ), esc_html( $vendor_business_name)); ?></p>

<?php
$text_align = is_rtl() ? 'right' : 'left';
?>

<h2>
	<?php
	if ( $sent_to_admin ) {
		$before = '<a class="link" href="' . esc_url( $order->get_edit_order_url() ) . '">';
		$after  = '</a>';
	} else {
		$before = '';
		$after  = '';
	}
	/* translators: %s: Order ID. */
	echo wp_kses_post( $before . sprintf( __( '[Order #%s]', 'woocommerce' ) . $after . ' (<time datetime="%s">%s</time>)', $order->get_order_number(), $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ) );
	?>
</h2>

<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		
		<tbody>
                    <tr>
                        <td style="text-align: center;">
                            <?php   
                                    $sub_orders = get_children( array('post_parent' => $order->get_id(), 'post_type' => 'shop_order' ) );
                                    $review_rating='';
                                    foreach($sub_orders as $sorder){
                                        $suborder_id=$sorder->ID;
                                        $suborder_authorid=$sorder->post_author;
                                        $code=$order->get_id()."&".$suborder_id."&".$suborder_authorid;
                                        $key='buylocksmithdeals';
                                        $string=BuyLockSmithDealsCustomizationAddon::code_encrypt($code, $key);
                                        $review_rating=home_url().'/review-rating?code='.$string;
                                    }
                                    ?>
									<a href="<?php echo $review_rating ?>" target="_blank">
									<button type="button" style="background: #92d050;display: inline-block;padding: 15px 50px;border: 2px solid #2f528f;border-radius: 10px;font-size: 16px;font-weight: 400;color: #fff;cursor: pointer;">Leave Us A Review</button>
									</a>
                        </td>
						
                    </tr>
		</tbody>
	</table>
</div>

<?php //do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); 

//do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );



/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
//do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
//do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );


/**
 * Show user-defined additonal content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
?>
<style>
    #completion_code{
        display:none;
    }    
</style>