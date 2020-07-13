<?php

/**
 * Add Downloadable file template
 * Used by html-product-data-general.php template
 * Not overridable
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/woocommerce
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<tr>
	<td class="sort"><span class="sortable-icon"></span></td>
	<td class="file_name">
		<input type="text" class="input_text form-control" placeholder="<?php esc_attr_e( 'File name', 'woocommerce' ); ?>" name="_wc_file_names[]" value="<?php echo esc_attr( $file['name'] ); ?>" />
		<input type="hidden" name="_wc_file_hashes[]" value="<?php echo esc_attr( $key ); ?>" />
	</td>
	<td class="file_url"><input type="text" class="input_text form-control" placeholder="<?php esc_attr_e( 'http://', 'woocommerce' ); ?>" name="_wc_file_urls[]" value="<?php echo esc_attr( $file['file'] ); ?>" /></td>
	<td class="file_url_choose"><a href="#" class="button upload_file_button" data-choose="<?php esc_attr_e( 'Choose file', 'woocommerce' ); ?>" data-update="<?php esc_attr_e( 'Insert file URL', 'woocommerce' ); ?>" title="<?php echo esc_html__( 'Choose file', 'woocommerce' ); ?>"><i class="wcmp-font ico-upload-image-icon"></i></a></td>
	<td><a href="#" class="delete" title="<?php esc_html_e( 'Delete', 'woocommerce' ); ?>"><i class="wcmp-font ico-delete-icon"></i></a></td>
</tr>