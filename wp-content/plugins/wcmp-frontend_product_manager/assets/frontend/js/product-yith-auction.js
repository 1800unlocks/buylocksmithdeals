'use strict';
var afmYithAuctionController = ( function ( $ ) {
    var privateApi = {
        addEventHandlers: function addEventHandlers( ) {
            $( '#woocommerce-product-data' )
                .on( 'afm-product-type-changed', this.resetProductOptions )
                ;
            this.setupEnvironment();
        },
        setupEnvironment: function setupEnvironment( ) {
            var startDateTextBox = $( '#_yith_auction_for' );
            var endDateTextBox = $( '#_yith_auction_to' );

            $.timepicker.datetimeRange(
                startDateTextBox,
                endDateTextBox,
                {
                    minInterval: ( 1000 * 60 ), // 1min
                    dateFormat: 'yy-mm-dd',
                    timeFormat: 'HH:mm:ss',
                    start: { }, // start picker options
                    end: { } // end picker options
                }
            );
            
            this.resetProductOptions();
        },
        resetProductOptions: function resetProductOptions() {
            var type = $( 'select#product-type' ).val();

            if ( type === 'auction' ) {
                $( '#_regular_price' ).val( '' );
                $( '#_sale_price' ).val( '' );
            }
        }
    };
    /**
     * @TODO implement all the public API to product.js and access directly via global object
     * e.g. sortable function
     */
    var publicApi = {
        init: function ( ) {
            privateApi.addEventHandlers( );
        }
    };
    return publicApi;
} )( jQuery );
afmYithAuctionController.init( );