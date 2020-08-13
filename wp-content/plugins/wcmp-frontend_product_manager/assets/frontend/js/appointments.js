/* All bookings data table */
'use strict';
( function ( $ ) {
    var appointmentsTable = $( '#appointments_table' ).DataTable( {
        ordering: true,
        searching: false,
        processing: true,
        serverSide: true,
        responsive: true,
        language: {
            emptyTable: appointments_params.empty_table,
            processing: appointments_params.processing,
            info: appointments_params.info,
            infoEmpty: appointments_params.info_empty,
            lengthMenu: appointments_params.length_menu,
            zeroRecords: appointments_params.zero_records,
            paginate: {
                next: appointments_params.next,
                previous: appointments_params.previous
            }
        },
        drawCallback: function ( settings ) {
            $( "#filter_appointments" ).detach();
            var filter_appointments_sel = $( '<select id="filter_appointments" name="filter_appointments" class="wcmp_filter_appointments form-control">' ).appendTo( "#appointments_table_length" );
            filter_appointments_sel.append( $( "<option>" ).attr( 'value', '' ).text( appointments_params.booking_filter_default ) );
            var options = JSON.parse( appointments_params.booking_filter_options );
            for ( var option in options ) {
                if ( options.hasOwnProperty( option ) ) {
                    filter_appointments_sel.append( $( "<option>" ).attr( 'value', option ).text( options[option] ) );
                }
            }
            if ( settings.oAjaxData.filter_appointments ) {
                filter_appointments_sel.val( settings.oAjaxData.filter_appointments );
            }
        },
        ajax: {
            url: appointments_params.ajax_url,
            data: function ( data ) {
                data.action = 'wcmp_vendor_appointment_list';
                if ( $( '#filter_appointments' ).val() ) {
                    data.filter_appointments = $( '#filter_appointments' ).val();
                }
                if(appointments_params.post_status) {
                    data.post_status = appointments_params.post_status;
                }
            },
            type: "POST",
            error: function ( xhr, status, error ) {
                $( "#appointments_table tbody" ).append( '<tr class="odd"><td valign="top" colspan="4" class="dataTables_empty" style="text-align:center;">' + error + ' - <a href="javascript:window.location.reload();">' + appointments_params.reload + '</a></td></tr>' );
                $( "#appointments_table_processing" ).css( "display", "none" );
            }
        },
        createdRow: function ( row, data, index ) {
            $( row ).addClass( 'wc-appointment' );
        },
        columns: [
            { data: "appointment" },
            { data: "when", className: "name" },
            { data: "product" },
            { data: "actions" },
        ]
    } );
    new $.fn.dataTable.FixedHeader( appointmentsTable );
    $( document ).on( 'change', '#filter_appointments', function () {
        appointmentsTable.draw();
    } );
} )( jQuery );
