<?php

/**
 * Days range price plans template
 * Not overridable
 *
 * @author 		WC Marketplace
 * @package 	WCMp_AFM/views/products/rental
 * @version     3.0.0
 */
defined( 'ABSPATH' ) || exit;

$min_days = ! empty( $day_range['min_days'] ) ? absint( $day_range['min_days'] ) : '';
$max_days = ! empty( $day_range['max_days'] ) ? absint( $day_range['max_days'] ) : '';
$range_cost = ! empty( $day_range['range_cost'] ) ? absint( $day_range['range_cost'] ) : '';
$cost_applicable = ! empty( $day_range['cost_applicable'] ) ? wc_clean( $day_range['cost_applicable'] ) : '';
?>
<div class="wcmp-metabox-wrapper redq_days_range" rel="<?php echo esc_attr( $i ); ?>">
    <div class="wcmp-metabox-title days-range-title" data-toggle="collapse" data-target="#days_range_<?php echo esc_attr( $i ); ?>"  aria-expanded="false" aria-controls="collapseExample">
        <div class="days_range_group">
            <strong class="summary"><?php _e( sprintf( __( ' Days ( <span class="min">%d</span> - <span class="max">%d</span> ) -  Cost : <span class="price">%s</span>%s', WCMp_AFM_TEXT_DOMAIN ), $min_days, $max_days, $range_cost, get_woocommerce_currency_symbol() ) ); ?></strong>
        </div>
        <div class="wcmp-metabox-action days_range_action">
            <i class="wcmp-font ico-up-arrow-icon"></i>
            <a href="#" class="remove_row delete remove-day-range"><?php esc_html_e( 'Remove', 'woocommerce' ); ?></a>
        </div>
    </div>
    <div class="wcmp-metabox-content redq_day_range_data collapse" id="days_range_<?php echo esc_attr( $i ); ?>">
        <table cellpadding="0" cellspacing="0" class="table wcmp-metabox-table">
            <tbody>
                <tr>
                    <th><label><?php esc_html_e( 'Min Days', 'redq-rental' ); ?></label></th>
                    <td><input type="number" min="0" step="1" name="redq_day_ranges_cost[<?php esc_attr_e( $i ); ?>][min_days]" class="form-control" placeholder="<?php esc_attr_e( 'Days', 'redq-rental' ) ?>" value="<?php esc_attr_e( $min_days ); ?>" /></td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e( 'Max Days', 'redq-rental' ); ?></label></th>
                    <td><input type="number" min="0" step="1" name="redq_day_ranges_cost[<?php esc_attr_e( $i ); ?>][max_days]" class="form-control" placeholder="<?php esc_attr_e( 'Days', 'redq-rental' ) ?>" value="<?php esc_attr_e( $max_days ); ?>" /></td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e( sprintf( __( 'Days Range Cost ( %s )', 'redq-rental' ), get_woocommerce_currency_symbol() ) ); ?></label></th>
                    <td><input type="text" name="redq_day_ranges_cost[<?php esc_attr_e( $i ); ?>][range_cost]" class="form-control" placeholder="<?php esc_attr_e( 'Cost', 'redq-rental' ) ?>" value="<?php esc_attr_e( $range_cost ); ?>" /></td>
                </tr>
                <tr>
                    <th><label><?php esc_html_e( 'Applicable', 'redq-rental' ); ?></label></th>
                    <td>
                        <select name="redq_day_ranges_cost[<?php esc_attr_e( $i ); ?>][cost_applicable]" class="form-control regular-select">
                            <option value=''><?php esc_html_e( 'Select Type', 'redq-rental' ); ?></option>			
                            <option value='per_day'<?php selected( $cost_applicable, 'per_day' ) ?>><?php esc_html_e( 'Per Day', 'redq-rental' ); ?></option>				
                            <option value='fixed'<?php selected( $cost_applicable, 'fixed' ) ?>><?php esc_html_e( 'Fixed', 'redq-rental' ); ?></option>			
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>