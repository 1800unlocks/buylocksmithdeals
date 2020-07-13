<?php
/**
 * The template for displaying demo plugin content.
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/emails/approved-vendor-account.php
 *
 * @author 		WC Marketplace
 * @package 	dc-product-vendor/Templates
 * @version   0.0.1
 */
 
global $WCMp;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
?>
<?php do_action( 'woocommerce_email_header', $email_heading, $email ); ?>
<p><?php printf( __( "Congratulations! Your vendor application on %s has been approved!", 'dc-woocommerce-multi-vendor' ), get_option( 'blogname' ) ); ?></p>
<p>
	<?php _e( "Application status: Approved",  'dc-woocommerce-multi-vendor' ); ?><br/>
	<?php printf( __( "Applicant Username: %s",  'dc-woocommerce-multi-vendor' ), $user_login ); ?>
</p>
<p><?php _e('You have been cleared for landing! Congratulations and welcome aboard!', 'dc-woocommerce-multi-vendor') ?> <p>
<p>Go Live Steps: </p>
<p>1) Log into your dashboard and follow the store set-up wizard.</p>
<p>2) Set up your account with Stripe. This is how you will get paid. Stripe will send money to your bank account.</p>
<p>3) Fill out your storefront profile with your logo and business contact details.</p>
<p>4) Your store will not have any deals in it yet. The admin will have to assign the deals to your store. Once your storefront details are uploaded, please contact us to review the store. We'll help you build it for the best customer experience.</p>
<p>5) Then we can assign any and all deals to your store.</p>
<p>6) Customize the pricing, pictures, and booking times for your deals.</p>
<p>7) Lastly, embed your store deals into your website for maximum exposure. </p>
<p><?php printf( __( "Log into your dashboard here: %s",  'dc-woocommerce-multi-vendor' ), home_url().'/dashboard/' ); ?>  </p>

<?php do_action( 'wcmp_email_footer' );?>