'use strict';
var afmYithAuctionProController = ( function ( $ ) {
    var privateApi = {
        addEventHandlers: function addEventHandlers( ) {
            $( '#woocommerce-product-data' )
                .on( 'afm-product-type-changed', this.resetProductOptions )
                ;
            $( '#yith_auction_product_data' )
                .on( 'click', '#reschedule_button', this.rescheduleAuction )
                ;
            $( '#wcmp-afm-add-product' )
                .on( 'click', '.auction_status_wrapper a#yith-wcact-send-winner-email', this.sendWinnerEmail )
                .on( 'click', '.auction_bids_wrapper a.yith-wcact-delete-bid', this.deleteBid )
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
            $( '#yith_reschedule_notice_admin' ).hide();
            
            this.resetProductOptions();
        },
        resetProductOptions: function resetProductOptions() {
            var type = $( 'select#product-type' ).val();

            if ( type === 'auction' ) {
                $( '#_regular_price' ).val( '' );
                $( '#_sale_price' ).val( '' );
            }
        },
        rescheduleAuction: function rescheduleAuction() {
            var $wrapper = $( '#yith_auction_product_data' );

            var data = {
                action: 'wcmp_afm_yith_reschedule_auction',
                id: wcmp_advance_product_params.product_id,
                security: wcmp_advance_product_params.reschedule_auction_nonce
            };

            $wrapper.block( {
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            } );

            $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {
                $( '#reschedule_button' ).hide();
                $( '#yith_reschedule_notice_admin' ).show();
                $( '#_stock_status' ).val( 'instock' );

                $wrapper.unblock();
            } );
            return false;
        },
        sendWinnerEmail: function sendWinnerEmail() {
            var $wrapper = $( '.auction_status_wrapper' );

            $wrapper.block( {
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            } );

            var data = {
                action: 'wcmp_afm_yith_auction_resend_winner_email',
                id: wcmp_advance_product_params.product_id,
                security: wcmp_advance_product_params.resend_winner_email_nonce
            };

            $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {
                $( '.auction_status_wrapper' ).empty();
                $( '.auction_status_wrapper' ).html( response['resend_winner_email'] );

                $wrapper.unblock();
            } );
            
            return false;
        },
        deleteBid: function deleteBid() {
            var $wrapper = $( '.auction_bids_wrapper' ),
                $currentBid = $( this ).closest( 'tr' );

            if ( window.confirm( wcmp_advance_product_params.i18n_delete_bid ) ) {

                $wrapper.block( {
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                } );

                var data = {
                    action: 'yith_wcact_delete_customer_bid',
                    user_id: $( this ).data( 'userId' ),
                    product_id: $( this ).data( 'productId' ),
                    date: $( this ).data( 'dateTime' ),
                };

                $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {
                    $currentBid.remove();
                    $wrapper.unblock();
                } );
            }
            return false;
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
afmYithAuctionProController.init( );