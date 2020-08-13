/* All subscriptions data table */
'use strict';
( function ( $ ) {
    var subscriptionsTable = $( '#subscriptions_table' ).DataTable( {
        ordering: true,
        searching: false,
        processing: true,
        serverSide: true,
        responsive: true,
        language: {
            emptyTable: subscriptions_params.empty_table,
            processing: subscriptions_params.processing,
            info: subscriptions_params.info,
            infoEmpty: subscriptions_params.info_empty,
            lengthMenu: subscriptions_params.length_menu,
            zeroRecords: subscriptions_params.zero_records,
            paginate: {
                next: subscriptions_params.next,
                previous: subscriptions_params.previous
            }
        },
        ajax: {
            url: subscriptions_params.ajax_url,
            data: function ( data ) {
                data.action = 'wcmp_vendor_subscription_list';
                if ( $( '#filter_subscriptions' ).val() ) {
                    data.filter_subscriptions = $( '#filter_subscriptions' ).val();
                }
                if(subscriptions_params.post_status) {
                    data.post_status = subscriptions_params.post_status;
                }
            },
            type: "POST",
            error: function ( xhr, status, error ) {
                $( "#subscriptions_table tbody" ).append( '<tr class="odd"><td valign="top" colspan="4" class="dataTables_empty" style="text-align:center;">' + error + ' - <a href="javascript:window.location.reload();">' + subscriptions_params.reload + '</a></td></tr>' );
                $( "#subscriptions_table_processing" ).css( "display", "none" );
            }
        },
        createdRow: function ( row, data, index ) {
            $( row ).addClass( 'wc-subscription' );
        },
        columns: [
            { data: "order_title"},
            { data: "status", orderable: false },
            { data: "order_items", orderable: false },
            { data: "recurring_total", orderable: false },
            { data: "start_date" },
            { data: "trial_end_date", orderable: false },
            { data: "next_payment_date", orderable: false },
            { data: "last_payment_date", orderable: false },
            { data: "end_date", orderable: false  },
        ]
    } );
    new $.fn.dataTable.FixedHeader( subscriptionsTable );
    $( document ).on( 'change', '#filter_subscriptions', function () {
        subscriptionsTable.draw();
    } );
} )( jQuery );
