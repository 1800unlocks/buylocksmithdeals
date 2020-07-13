<?php
/**
 * Vendor Review Comments Template
 *
 * Closing li is left out on purpose!.
 *
 * This template can be overridden by copying it to yourtheme/dc-product-vendor/review/rating.php.
 *
 * HOWEVER, on occasion WC Marketplace will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * 
 * @author  WC Marketplace
 * @package dc-woocommerce-multi-vendor/Templates
 * @version 3.3.5
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

global $WCMp;

$rating = round($rating_val_array['avg_rating']);
$count = intval($rating_val_array['total_rating']);
$review_text = $count > 1 ? __('Reviews', 'dc-woocommerce-multi-vendor') : __('Review', 'dc-woocommerce-multi-vendor');
?> 
<div style="clear:both; width:100%;"></div> 
<?php if ($count > 0) { ?>
    <span class="wcmp_total_rating_number"><?php echo __(sprintf(' %s / 5', ceil($rating))); ?></span>
<?php } ?>
<a href="#reviews">
<?php if ($count > 0) { ?>	
        <span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="star-rating" title="<?php echo sprintf(__('Rated %s out of 5', 'dc-woocommerce-multi-vendor'), $rating) ?>">
            <span style="width:<?php echo ( ceil(round($rating_val_array['avg_rating'])) / 5 ) * 100; ?>%"><strong itemprop="ratingValue"><?php echo $rating; ?></strong><?php _e('out of 5', 'dc-woocommerce-multi-vendor'); ?> </span>
        </span>
        <?php echo __(sprintf(' %s %s', $count, $review_text)); ?>

    <?php
} else {
    ?>
        <?php echo __(' No Review Yet ', 'dc-woocommerce-multi-vendor'); ?>
    <?php } ?>
</a>
<style>
.star-rating span:before {
    content: "\53\53\53\53\53";
}
</style>