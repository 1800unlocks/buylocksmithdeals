<?php
defined( 'ABSPATH' ) || exit;

class WCMp_AFM_Manage_affiliate_Endpoint {

    public function output() {
        global $wpdb;
        $results = $wpdb->get_results( "SELECT affiliate_id FROM {$wpdb->prefix}affiliate_wp_affiliates" );
        $current_user = get_current_user_id();
        if (!empty($results)) {
            echo '<div class="col-md-12"><div class="wcmp_tab ui-tabs ui-widget ui-widget-content ui-corner-all staff-detail-wrap"><div class="wcmp_table_holder"><table class="table table-bordered">
            <thead><tr>

                <th>' . __('Name', "wcmp-afm") . '</th>
                <th>' . __('Affiliate ID', "wcmp-afm") . '</th>
                <th>' . __('Paid Earnings', "wcmp-afm") . '</th>
                <th>' . __('Unpaid Earnings', "wcmp-afm") . '</th>
                <th>' . __('Rate', "wcmp-afm") . '</th>
                <th>' . __('Paid Referrals', "wcmp-afm") . '</th>
                <th>' . __('Unpaid Referrals', "wcmp-afm") . '</th>
                <th>' . __('Visit', "wcmp-afm") . '</th>
                <th>' . __('Status', "wcmp-afm") . '</th>

                <th>' . __('User ID', "wcmp-afm") . '</th>
                <th>' . __('Action', "wcmp-afm") . '</th>
            </tr></thead><tbody>';

            foreach ($results as $user => $affiliate_ids ) {

                $affiliate_details = affwp_get_affiliate( $affiliate_ids );
                $vendor_meta_key = affwp_get_affiliate_meta( $affiliate_ids->affiliate_id, 'affiliate_assign_vendor', true );
                if( is_array( $vendor_meta_key ) ){
                    if( in_array( $current_user , $vendor_meta_key ) ) {

                        echo 
                        '<tr>
                        <td>' . affwp_get_affiliate_username($affiliate_ids->affiliate_id) . ' </td>
                        <td>' . $affiliate_ids->affiliate_id . ' </td>
                        <td>' . affwp_get_affiliate_earnings( $affiliate_ids->affiliate_id ) . ' </td>
                        <td>' . affwp_get_affiliate_unpaid_earnings( $affiliate_ids->affiliate_id ) . ' </td>
                        <td>' . affwp_get_affiliate_rate( $affiliate_ids->affiliate_id ) . ' </td>
                        <td>' . affwp_get_affiliate_referral_count( $affiliate_ids->affiliate_id ) . ' </td>
                        <td>' . affwp_get_affiliate_referral_count( $affiliate_ids->affiliate_id ) . ' </td>
                        <td>' . affwp_get_affiliate_visit_count( $affiliate_ids->affiliate_id ) . ' </td>
                        <td>' . affwp_get_affiliate_status( $affiliate_ids->affiliate_id ) . ' </td>

                        <td>' . $affiliate_details->user_id . '</td><td>';
                        ?>
                        <button type="button" name="affiliate_data_id" data-affiliatedelete="<?php echo $affiliate_ids->affiliate_id; ?>" data-metakeyaffiliate="<?php print_r( $vendor_meta_key ); ?>"  id="<?php echo $affiliate_ids->affiliate_id; ?>" class="vendor_affiliate_delete_button">DELETE</button>
                        <?php
                    }
                }   
            }

            echo '</tbody></table></div></div></div>';
        } else {
            ?>
            <div><h4>&nbsp;&nbsp;&nbsp;&nbsp;
                <?php _e("You did not assign with any affiliate yet!!!", 'wcmp-afm'); ?>
            </h4></div>
            <?php
        }

    }
}
?>
<style>
    .staff-detail-wrap{
        overflow: auto;
    }
</style>
