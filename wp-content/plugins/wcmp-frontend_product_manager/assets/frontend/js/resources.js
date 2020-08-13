/* All resources data table */
'use strict';
( function ( $ ) {
    var resources = $( '#resources_table' ).DataTable( {
        ordering: true,
        searching: true,
        processing: true,
        serverSide: true,
        language: {
            emptyTable: resources_params.empty_table,
            processing: resources_params.processing,
            info: resources_params.info,
            infoEmpty: resources_params.info_empty,
            lengthMenu: resources_params.length_menu,
            zeroRecords: resources_params.zero_records,
            paginate: {
                next: resources_params.next,
                previous: resources_params.previous
            }
        },
        ajax: {
            url: resources_params.ajax_url,
            data: {
				action: 'wcmp_vendor_resources_list'
			},
            type: "POST",
            error: function ( xhr, status, error ) {
                $( "#resources_table tbody" ).append( '<tr class="odd"><td valign="top" colspan="4" class="dataTables_empty" style="text-align:center;">' + error + ' - <a href="javascript:window.location.reload();">'+resources_params.reload+'</a></td></tr>' );
                    $( "#resources_table_processing" ).css( "display", "none" );
            }
        },
        createdRow: function ( row, data, index ) {
            $( row ).addClass( 'wc-booking-resource' );
        },
        columns: [
            { data: "title", className: "name" },
            { data: "date" }
        ]
    } );
} )( jQuery );
