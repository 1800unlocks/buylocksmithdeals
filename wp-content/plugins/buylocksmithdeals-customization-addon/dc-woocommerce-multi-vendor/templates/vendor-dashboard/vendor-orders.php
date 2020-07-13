<?php
/**
 * The template for displaying vendor orders
 *
 * Override this template by copying it to yourtheme/dc-product-vendor/vendor-dashboard/vendor-orders.php
 *
 * @author 		WC Marketplace
 * @package 	WCMp/Templates
 * @version   2.2.0
 */
if (!defined('ABSPATH')) {
    // Exit if accessed directly
    exit;
}
global  $WCMp;
if(isset($_REQUEST['bookings_vendor']) && !empty($_REQUEST['bookings_vendor'])){
    //echo 'aaaaaaaaa'; exit;
    include_once 'vendor-bookings.php';
    
}
else{
  
$orders_list_table_headers = apply_filters('wcmp_datatable_order_list_table_headers', array(
    'select_order'  => array('label' => '', 'class' => 'text-center'),
    'order_id'      => array('label' => __( 'Order ID', 'dc-woocommerce-multi-vendor' )),
    'order_date'    => array('label' => __( 'Date', 'dc-woocommerce-multi-vendor' )),
    'vendor_earning'=> array('label' => __( 'Earnings', 'dc-woocommerce-multi-vendor' )),
    'order_status'  => array('label' => __( 'Status', 'dc-woocommerce-multi-vendor' )),
    'action'        => array('label' => __( 'Action', 'dc-woocommerce-multi-vendor' )),
), get_current_user_id());
?>
<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-body">
            <form name="wcmp_vendor_dashboard_orders" method="POST" class="form-inline">
                <div class="form-group">
                    <span class="date-inp-wrap">
                        <input type="text" name="wcmp_start_date_order" class="pickdate gap1 wcmp_start_date_order form-control" placeholder="<?php _e('from', 'dc-woocommerce-multi-vendor'); ?>" value="<?php echo isset($_POST['wcmp_start_date_order']) ? $_POST['wcmp_start_date_order'] : date('Y-m-01'); ?>" />
                    </span> 
                    <!-- <span class="between">&dash;</span> -->
                </div>
                <div class="form-group">
                    <span class="date-inp-wrap">
                        <input type="text" name="wcmp_end_date_order" class="pickdate wcmp_end_date_order form-control" placeholder="<?php _e('to', 'dc-woocommerce-multi-vendor'); ?>" value="<?php echo isset($_POST['wcmp_end_date_order']) ? $_POST['wcmp_end_date_order'] : date('Y-m-d'); ?>" />
                    </span>
                </div>
                <button class="wcmp_black_btn btn btn-default" type="submit" name="wcmp_order_submit"><?php _e('Show', 'dc-woocommerce-multi-vendor'); ?></button>
            </form>
            <form method="post" name="wcmp_vendor_dashboard_completed_stat_export">
                <table class="table table-striped table-bordered" id="wcmp-vendor-orders" style="width:100%;">
                    <thead>
                        <tr>
                        <?php 
                            if($orders_list_table_headers) :
                                foreach ($orders_list_table_headers as $key => $header) {
                                    if($key == 'select_order'){ ?>
                            <th class="<?php if(isset($header['class'])) echo $header['class']; ?>"><input type="checkbox" class="select_all_all" onchange="toggleAllCheckBox(this, 'wcmp-vendor-orders');" /></th>
                                <?php }else{ ?>
                            <th class="<?php if(isset($header['class'])) echo $header['class']; ?>"><?php if(isset($header['label'])) echo $header['label']; ?></th>         
                                <?php }
                                }
                            endif;
                        ?>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            <?php if(apply_filters('can_wcmp_vendor_export_orders_csv', true, get_current_vendor_id())) : ?>
            <div class="wcmp-action-container">
                <input class="btn btn-default" type="submit" name="wcmp_download_vendor_order_csv" value="<?php _e('Download CSV', 'dc-woocommerce-multi-vendor') ?>" />
            </div>
            <?php endif; ?>
            <?php if (isset($_POST['wcmp_start_date_order'])) : ?>
                <input type="hidden" name="wcmp_start_date_order" value="<?php echo $_POST['wcmp_start_date_order']; ?>" />
            <?php endif; ?>
            <?php if (isset($_POST['wcmp_end_date_order'])) : ?>
                <input type="hidden" name="wcmp_end_date_order" value="<?php echo $_POST['wcmp_end_date_order']; ?>" />
            <?php endif; ?>    
            </form>
        </div>
    </div>

    <!-- Modal -->
    <div id="marke-as-ship-modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <form method="post">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><?php _e('Shipment Tracking Details', 'dc-woocommerce-multi-vendor'); ?></h4>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="tracking_url"><?php _e('Enter Tracking Url', 'dc-woocommerce-multi-vendor'); ?> *</label>
                            <input type="url" class="form-control" id="email" name="tracking_url" required="">
                        </div>
                        <div class="form-group">
                            <label for="tracking_id"><?php _e('Enter Tracking ID', 'dc-woocommerce-multi-vendor'); ?> *</label>
                            <input type="text" class="form-control" id="pwd" name="tracking_id" required="">
                        </div>
                    </div>
                    <input type="hidden" name="order_id" id="wcmp-marke-ship-order-id" />
                    <?php if (isset($_POST['wcmp_start_date_order'])) : ?>
                        <input type="hidden" name="wcmp_start_date_order" value="<?php echo $_POST['wcmp_start_date_order']; ?>" />
                    <?php endif; ?>
                    <?php if (isset($_POST['wcmp_end_date_order'])) : ?>
                        <input type="hidden" name="wcmp_end_date_order" value="<?php echo $_POST['wcmp_end_date_order']; ?>" />
                    <?php endif; ?>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" name="wcmp-submit-mark-as-ship"><?php _e('Submit', 'dc-woocommerce-multi-vendor'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var orders_table;
        var statuses = [];
        var columns = [];
        <?php if($orders_list_table_headers) {
     foreach ($orders_list_table_headers as $key => $header) { ?>
        obj = {};
        obj['data'] = '<?php echo esc_js($key); ?>';
        obj['className'] = '<?php if(isset($header['class'])) echo esc_js($header['class']); ?>';
        columns.push(obj);
     <?php }
        }
        $filter_by_status = apply_filters( 'wcmp_vendor_dashboard_order_filter_status_arr', array_merge( 
            array( 'all' => __('All', 'dc-woocommerce-multi-vendor') ), 
            wc_get_order_statuses()
        ) );
        foreach ($filter_by_status as $key => $label) { ?>
            obj = {};
            obj['key'] = "<?php echo trim($key); ?>";
            obj['label'] = "<?php echo addslashes($label); ?>";
            statuses.push(obj);
        <?php } ?>
        orders_table = $('#wcmp-vendor-orders').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: false,
            responsive: true,
            drawCallback: function (settings) {
                $( "#filter_by_order_status" ).detach();
                var order_status_sel = $('<select id="filter_by_order_status" class="wcmp-filter-dtdd wcmp_filter_order_status form-control">').appendTo("#wcmp-vendor-orders_length");
                $(statuses).each(function () {
                    order_status_sel.append($("<option>").attr('value', this.key).text(this.label));
                });
                if(settings.oAjaxData.order_status){
                    order_status_sel.val(settings.oAjaxData.order_status);
                }
            },
            language: {
                emptyTable: "<?php echo trim(__('No orders found!', 'dc-woocommerce-multi-vendor')); ?>",
                processing: "<?php echo trim(__('Processing...', 'dc-woocommerce-multi-vendor')); ?>",
                info: "<?php echo trim(__('Showing _START_ to _END_ of _TOTAL_ orders', 'dc-woocommerce-multi-vendor')); ?>",
                infoEmpty: "<?php echo trim(__('Showing 0 to 0 of 0 orders', 'dc-woocommerce-multi-vendor')); ?>",
                lengthMenu: "<?php echo trim(__('Number of rows _MENU_', 'dc-woocommerce-multi-vendor')); ?>",
                zeroRecords: "<?php echo trim(__('No matching orders found', 'dc-woocommerce-multi-vendor')); ?>",
                paginate: {
                    next: "<?php echo trim(__('Next', 'dc-woocommerce-multi-vendor')); ?>",
                    previous: "<?php echo trim(__('Previous', 'dc-woocommerce-multi-vendor')); ?>"
                }
            },
            ajax: {
                url: '<?php echo add_query_arg( 'action', 'wcmp_datatable_get_vendor_orders_wnw', $WCMp->ajax_url() ); ?>',
                type: "post",
                data: function (data) {
                    data.start_date = '<?php echo $start_date; ?>';
                    data.end_date = '<?php echo $end_date; ?>';
                    data.order_status = $('#filter_by_order_status').val();
                },
                error: function(xhr, status, error) {
                    $("#wcmp-vendor-orders tbody").append('<tr class="odd"><td valign="top" colspan="6" class="dataTables_empty" style="text-align:center;">'+error+' - <a href="javascript:window.location.reload();"><?php _e('Reload', 'dc-woocommerce-multi-vendor'); ?></a></td></tr>');
                    $("#wcmp-vendor-orders_processing").css("display","none");
                }
            },
            columns: columns
        });
        new $.fn.dataTable.FixedHeader( orders_table );
        $(document).on('change', '#filter_by_order_status', function () {
            orders_table.ajax.reload();
        });
    });

    function wcmpMarkeAsShip(self, order_id) {
        jQuery('#wcmp-marke-ship-order-id').val(order_id);
        jQuery('#marke-as-ship-modal').modal('show');
    }
</script>
<?php } ?>