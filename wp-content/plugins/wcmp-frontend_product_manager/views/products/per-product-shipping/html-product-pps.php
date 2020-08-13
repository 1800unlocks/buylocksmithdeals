<?php

/**
 * Add Downloadable file template
 * Used by html-product-data-shipping.php template
 * Not overridable
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/per-product-shipping
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
$rule_id = empty($rule->rule_id)? "[new][]" : "[".$rule->rule_id."]";
?>
<tr>
	<td class="sort"><span class="sortable-icon"></span></td>
	<td class="country"><input type="text" class="form-control" value="<?php esc_attr_e( $rule->rule_country ); ?>" placeholder="*" name="per_product_country[<?php echo $post_id; ?>]<?php echo $rule_id; ?>" /></td>
	<td class="state"><input type="text" class="form-control" value="<?php esc_attr_e( $rule->rule_state ); ?>" placeholder="*" name="per_product_state[<?php echo $post_id; ?>]<?php echo $rule_id; ?>" /></td>
	<td class="postcode"><input type="text" class="form-control" value="<?php esc_attr_e( $rule->rule_postcode ); ?>" placeholder="*" name="per_product_postcode[<?php echo $post_id; ?>]<?php echo $rule_id; ?>" /></td>
	<td class="cost"><input type="text" class="form-control" value="<?php esc_attr_e( $rule->rule_cost ); ?>" placeholder="0.00" name="per_product_cost[<?php echo $post_id; ?>]<?php echo $rule_id; ?>" /></td>
	<td class="item_cost"><input type="text" class="form-control" value="<?php esc_attr_e( $rule->rule_item_cost ); ?>" placeholder="0.00" name="per_product_item_cost[<?php echo $post_id; ?>]<?php echo $rule_id; ?>" /></td>
	<td><a href="#" class="delete" title="<?php esc_html_e( 'Delete', 'woocommerce' ); ?>"><i class="wcmp-font ico-delete-icon"></i></a></td>
</tr>