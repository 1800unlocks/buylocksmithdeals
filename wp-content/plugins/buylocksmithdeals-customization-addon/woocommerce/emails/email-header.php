<?php
/**
 * Email Header
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-header.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 2.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $email; 
// Get an instance of the WC_Order object
$order = $email->object; 
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
                                                                                            $profile_image='';
                                                                                            $sub_orders = get_children( array('post_parent' => $order->get_id(), 'post_type' => 'shop_order' ) );
                                                                                            if(!empty($sub_orders)){
																								foreach($sub_orders as $sorder){
                                                                                                $suborder_id=$sorder->ID;
                                                                                                $suborder_authorid=$sorder->post_author;
                                                                                                $suborder_authorname=get_author_name( $suborder_authorid);
                                                                                                $vendor_profile_image = get_user_meta($suborder_authorid, '_vendor_profile_image', true);
                                                                                                if (isset($vendor_profile_image) && $vendor_profile_image > 0){
                                                                                                    $profile_image = wp_get_attachment_url($vendor_profile_image);
                                                                                                }
                                                                                             }
																							}
																							else{
																								$suborder_authorid = get_post_field( 'post_author', $order->get_id() );
																								$suborder_authorname=get_author_name( $suborder_authorid);
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
