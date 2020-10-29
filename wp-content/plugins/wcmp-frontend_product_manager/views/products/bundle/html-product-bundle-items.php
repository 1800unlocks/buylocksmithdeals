<?php
/**
 * Bundled Products tab products list template
*
 * This template can be overridden by copying it to yourtheme/wcmp-afm/products/bundle/html-product-bundled-items.php.
 *
 * HOWEVER, on occasion AFM will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      WC Marketplace
 * @package     WCMp_AFM/views/products/bundle
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;
?>
<div class="wcmp-metabox-wrapper wc-bundled-item wc-metabox closed <?php echo $toggle; ?>" rel="<?php echo $loop; ?>">
    <div class="wcmp-metabox-title item-title" data-toggle="collapse" data-target="#bundle_item_<?php echo esc_attr( $loop ); ?>"  aria-expanded="false" aria-controls="collapseExample">
        <div class="bundle-select-group">
            <span class="sortable-icon"></span>
            <strong class="item_name"><?php esc_html_e( $title ); ?></strong>
            <?php
            echo '' !== $item_availability ? '<span class="item-availability">' . $item_availability . '</span>' : '';
            ?>
        </div>
        <div class="wcmp-metabox-action item-action">
            <i class="wcmp-font ico-up-arrow-icon"></i>
            <a href="#" class="remove_row delete remove-item"><?php esc_html_e( 'Remove', 'woocommerce' ); ?></a>
        </div>
    </div>

    <div class="wcmp-metabox-content bundled_item_data wc-metabox-content collapse mt-15" id="bundle_item_<?php echo esc_attr( $loop ); ?>">
        <input type="hidden" name="bundle_data[<?php echo $loop; ?>][menu_order]" class="item_menu_order" value="<?php echo $loop; ?>" /><?php
        if ( false !== $item_id ) {
            ?><input type="hidden" name="bundle_data[<?php echo $loop; ?>][item_id]" class="item_id" value="<?php echo $item_id; ?>" /><?php
        }
        ?><input type="hidden" name="bundle_data[<?php echo $loop; ?>][product_id]" class="product_id" value="<?php echo $product_id; ?>" />
        <ul class="nav nav-tabs" role="tablist"><?php
            /* -------------------------------- */
            /*  Tab menu items.               */
            /* -------------------------------- */

            $tab_loop = 0;

            foreach ( $tabs as $tab_values ) {
                $tab_id = $tab_values['id'];
                ?><li class="nav-item<?php echo $tab_loop === 0 ? " active" : ""; ?>"><a href="#bundle_item_tab_<?php esc_attr_e( $tab_id . "_" . $loop ); ?>" class="nav-link <?php echo $tab_loop === 0 ? 'current' : ''; ?>" aria-controls="bundle_item_tab_<?php esc_attr_e( $tab_id . "_" . $loop ); ?>" role="tab" data-toggle="tab" aria-expanded="false"><?php
                echo $tab_values['title'];
                ?></a></li><?php
                $tab_loop ++;
            }
            ?></ul>
        <div class="tab-content">
            <?php
            /* -------------------------------- */
            /*  Tab contents.                 */
            /* -------------------------------- */

            $tab_loop = 0;

            foreach ( $tabs as $tab_values ) {

                $tab_id = $tab_values['id'];
                ?>
                <div id="bundle_item_tab_<?php esc_attr_e( $tab_id . "_" . $loop ); ?>" role="tabpanel" class="tab-pane fade <?php echo $tab_id;?><?php echo $tab_loop === 0 ? ' active in' : ''; ?>">
                    <?php
                    include( "html-product-bundle-{$tab_id}-tab.php" );
                    ?></div><?php
                $tab_loop ++;
            }
            ?>
        </div>
    </div>
</div>