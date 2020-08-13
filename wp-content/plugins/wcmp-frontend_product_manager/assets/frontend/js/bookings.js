/* All bookings data table */
'use strict';
( function ( $ ) {
    var bookingsTable = $( '#bookings_table' ).DataTable( {
        ordering: true,
        searching: false,
        processing: true,
        serverSide: true,
        responsive: true,
        language: {
            emptyTable: bookings_params.empty_table,
            processing: bookings_params.processing,
            info: bookings_params.info,
            infoEmpty: bookings_params.info_empty,
            lengthMenu: bookings_params.length_menu,
            zeroRecords: bookings_params.zero_records,
            paginate: {
                next: bookings_params.next,
                previous: bookings_params.previous
            }
        },
        drawCallback: function ( settings ) {
            $( "#filter_bookings" ).detach();
            var filter_bookings_sel = $( '<select id="filter_bookings" name="filter_bookings" class="wcmp_filter_bookings form-control">' ).appendTo( "#bookings_table_length" );
            filter_bookings_sel.append( $( "<option>" ).attr( 'value', '' ).text( bookings_params.booking_filter_default ) );
            var options = JSON.parse( bookings_params.booking_filter_options );
            for ( var option in options ) {
                if ( options.hasOwnProperty( option ) ) {
                    filter_bookings_sel.append( $( "<option>" ).attr( 'value', option ).text( options[option] ) );
                }
            }
            if ( settings.oAjaxData.filter_bookings ) {
                filter_bookings_sel.val( settings.oAjaxData.filter_bookings );
            }
        },
        ajax: {
            url: bookings_params.ajax_url,
            data: function ( data ) {
                data.action = 'wcmp_vendor_booking_list';
                if ( $( '#filter_bookings' ).val() ) {
                    data.filter_bookings = $( '#filter_bookings' ).val();
                }
                if(bookings_params.post_status) {
                    data.post_status = bookings_params.post_status;
                }
            },
            type: "POST",
            error: function ( xhr, status, error ) {
                $( "#bookings_table tbody" ).append( '<tr class="odd"><td valign="top" colspan="4" class="dataTables_empty" style="text-align:center;">' + error + ' - <a href="javascript:window.location.reload();">' + bookings_params.reload + '</a></td></tr>' );
                $( "#bookings_table_processing" ).css( "display", "none" );
            }
        },
        createdRow: function ( row, data, index ) {
            $( row ).addClass( 'wc-booking' );
        },
        columns: [
            { data: "id" },
            { data: "booked-product", className: "name" },
            { data: "persons" },
            { data: "booked-by" },
            { data: "order" },
            { data: "start-date" },
            { data: "end-date" },
            { data: "actions", orderable: false }
        ]
    } );
    new $.fn.dataTable.FixedHeader( bookingsTable );
    $( document ).on( 'change', '#filter_bookings', function () {
        bookingsTable.draw();
    } );
} )( jQuery );
