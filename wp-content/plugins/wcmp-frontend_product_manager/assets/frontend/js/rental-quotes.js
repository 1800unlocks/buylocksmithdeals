'use strict';
( function ( $ ) {
    var rentalQuotes;
    rentalQuotes = $( '#rental_quotes_table' ).DataTable( {
        ordering: false,
        searching: false,
        processing: true,
        serverSide: true,
        language: {
            emptyTable: rental_quote_params.empty_table,
            processing: rental_quote_params.processing,
            info: rental_quote_params.info,
            infoEmpty: rental_quote_params.info_empty,
            lengthMenu: rental_quote_params.length_menu,
            zeroRecords: rental_quote_params.zero_records,
            paginate: {
                next: rental_quote_params.next,
                previous: rental_quote_params.previous
            }
        },
        ajax: {
            url: rental_quote_params.ajax_url,
            data: {
				action: 'wcmp_vendor_rental_quotes_list'
			},
            type: "POST",
            error: function ( xhr, status, error ) {
                $( "#rental_quotes_table tbody" ).append( '<tr class="odd"><td valign="top" colspan="4" class="dataTables_empty" style="text-align:center;">' + error + ' - <a href="javascript:window.location.reload();">'+rental_quote_params.reload+'</a></td></tr>' );
                    $( "#rental_quotes_table_processing" ).css( "display", "none" );
            }
        },
        createdRow: function ( row, data, index ) {
            $( row ).addClass( 'rental-quote' );
        },
        columns: [
            { data: "quote", className: "name" },
            { data: "status" },
            { data: "product" },
            { data: "email" },
            { data: "date" },
        ]
    } );
} )( jQuery );