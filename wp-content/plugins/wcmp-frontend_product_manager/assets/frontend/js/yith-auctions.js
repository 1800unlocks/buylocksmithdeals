/* All auctions data table */
'use strict';
( function ( $ ) {
    var auctionsTable = $( '#auctions_table' ).DataTable( {
        ordering: true,
        searching: true,
        processing: true,
        serverSide: true,
        responsive: true,
        language: {
            emptyTable: auctions_params.empty_table,
            processing: auctions_params.processing,
            info: auctions_params.info,
            infoEmpty: auctions_params.info_empty,
            lengthMenu: auctions_params.length_menu,
            zeroRecords: auctions_params.zero_records,
            paginate: {
                next: auctions_params.next,
                previous: auctions_params.previous
            }
        },
        drawCallback: function ( settings ) {
            $( "#filter_auctions" ).detach();
            var filter_auctions_sel = $( '<select id="filter_auctions" name="filter_auctions" class="wcmp_filter_auctions form-control">' ).appendTo( "#auctions_table_length" );
            filter_auctions_sel.append( $( "<option>" ).attr( 'value', '' ).text( auctions_params.auction_filter_default ) );
            var options = JSON.parse( auctions_params.auction_filter_options );
            for ( var option in options ) {
                if ( options.hasOwnProperty( option ) ) {
                    filter_auctions_sel.append( $( "<option>" ).attr( 'value', option ).text( options[option] ) );
                }
            }
            if ( settings.oAjaxData.filter_auctions ) {
                filter_auctions_sel.val( settings.oAjaxData.filter_auctions );
            }
        },
        ajax: {
            url: auctions_params.ajax_url,
            data: function ( data ) {
                data.action = 'wcmp_vendor_auction_list';
                if ( $( '#filter_auctions' ).val() ) {
                    data.filter_auctions = $( '#filter_auctions' ).val();
                }
            },
            type: "POST",
            error: function ( xhr, status, error ) {
                $( "#auctions_table tbody" ).append( '<tr class="odd"><td valign="top" colspan="4" class="dataTables_empty" style="text-align:center;">' + error + ' - <a href="javascript:window.location.reload();">' + auctions_params.reload + '</a></td></tr>' );
                $( "#auctions_table_processing" ).css( "display", "none" );
            }
        },
        createdRow: function ( row, data, index ) {
            $( row ).addClass( 'yith-auction' );
        },
        columns: [
            { data: "name", className: "name" },
            { data: "start-date" },
            { data: "end-date" },
            { data: "auction-status" },
            { data: "max-bidder", orderable: false },
        ]
    } );
    new $.fn.dataTable.FixedHeader( auctionsTable );
    $( document ).on( 'change', '#filter_auctions', function () {
        auctionsTable.draw();
    } );
} )( jQuery );
