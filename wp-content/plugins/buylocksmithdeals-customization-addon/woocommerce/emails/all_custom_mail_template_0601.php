<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
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
                                                
                                               
                                                                
                                                                $suborder_authorid=$user_id;
                                                                $suborder_authorname=get_author_name( $suborder_authorid);
                                                                $vendor_profile_image = get_user_meta($suborder_authorid, '_vendor_profile_image', true);
                                                                if (isset($vendor_profile_image) && $vendor_profile_image > 0){
                                                                    $profile_image = wp_get_attachment_url($vendor_profile_image);
                                                                }
                                                    
                                                         if($profile_image == ''){
                                                             $website_logo=BUYLOCKSMITH_DEALS_ASSETS_PATH.'img/site_logo.png';
                                                         ?>
                                                    <img width="120px" src="<?php echo $website_logo; ?>">
                                                        <?php
                                                         }
                                                         else{
                                                           ?>
                                                         <img src="<?php echo $profile_image; ?>">
                                                        <?php   
                                                         }
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
                                <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body" >
                                    <tr>
                                        <td valign="top" id="body_content">
                                            <!-- Content -->
                                            <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                                <tr>
                                                    <td valign="top">
                                                        <div id="body_content_inner">
                                                            <p style="margin: 0 0 16px;font-size: 16px;font-weight: 600;color: #474646;"><?php printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html($name) ); ?></p>
                                                            <!--p><?php echo $mail_heading; ?></p-->
                                                            <?php
                                                            $text_align = is_rtl() ? 'right' : 'left';
                                                            ?>
                                                            <h2>
                                                                <?php 
                                                                    echo date('F d, Y');
                                                                ?>
                                                            </h2>

                                                        <div style="margin-bottom: 40px;">
                                                                <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" >
                                                                        <tbody>
                                                                            <?php echo $body; ?>
                                                                        </tbody>
                                                                </table>
                                                        </div>
<?php
/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
?>
