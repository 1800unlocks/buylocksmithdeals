'use strict';
var afmSimpleAuctionController = ( function ( $ ) {
    var privateApi = {
        addEventHandlers: function addEventHandlers( ) {
            $( '#woocommerce-product-data' )
                .on( 'change', '#_regular_price', this.syncBuyNowPrice )
                ;
            $( '#simple_auction_product_data' )
                .on( 'change', '#_auction_proxy', this.toggleProxyBiddingSetting )
                .on( 'change', '#_auction_sealed', this.toggleSealedBidSetting )
                .on( 'click', '#relistauction', this.toggleRelistDateFields )
                ;

            $( '.auction-details-wrapper' )
                .on( 'click', '.auction-table .action a', this.deleteBid )
                .on( 'click', '.reservefail a.removereserve', this.removeReserve )
                ;
            this.setupEnvironment();
        },
        setupEnvironment: function setupEnvironment( ) {
            $( '#_auction_proxy' ).change();
            if ( $( '#_auction_sealed' ).length ) {
                $( '#_auction_sealed' ).change();
            }

            var startAuctionInput = $( '#_auction_dates_from' );
            var endAuctionInput = $( '#_auction_dates_to' );

            $.timepicker.datetimeRange(
                startAuctionInput,
                endAuctionInput,
                {
                    minInterval: ( 1000 * 60 ), // 1min
                    dateFormat: 'yy-mm-dd',
                    timeFormat: 'HH:mm',
                    start: { }, // start picker options
                    end: { } // end picker options
                }
            );
            if ( $( '.relist_auction_dates_fields' ).length > 0 ) {
                $( '.relist_auction_dates_fields' ).hide(); //start with hidden initially
                var startRelistAuctionInput = $( '#_relist_auction_dates_from' );
                var endRelistAuctionInput = $( '#_relist_auction_dates_to' );

                $.timepicker.datetimeRange(
                    startRelistAuctionInput,
                    endRelistAuctionInput,
                    {
                        minInterval: ( 1000 * 60 ), // 1min
                        dateFormat: 'yy-mm-dd',
                        timeFormat: 'HH:mm',
                        start: { }, // start picker options
                        end: { } // end picker options
                    }
                );
            }
        },
        syncBuyNowPrice: function syncBuyNowPrice() {
            $('#simple_auction_product_data #_sa_regular_price').val( this.value );
        },
        toggleProxyBiddingSetting: function toggleProxyBiddingSetting() {
            if ( $( '#_auction_sealed' ).length ) {
                var $sealedWrap = $( '#_auction_sealed' ).closest( '.form-group' );
                if ( this.checked ) {
                    $sealedWrap.slideUp( 'fast' );
                    $( '#_auction_sealed' ).prop( 'checked', false );

                } else {
                    $sealedWrap.slideDown( 'fast' );
                }
            }
            return false;
        },
        toggleSealedBidSetting: function toggleSealedBidSetting() {
            var $proxyWrap = $( '#_auction_proxy' ).closest( '.form-group' );
            if ( this.checked ) {
                $proxyWrap.slideUp( 'fast' );
                $( '#_auction_proxy' ).prop( 'checked', false );

            } else {
                $proxyWrap.slideDown( 'fast' );
            }
            return false;
        },
        toggleRelistDateFields: function toggleRelistDateFields() {
            $( '.relist_auction_dates_fields' ).slideToggle( 'fast' );
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
                    action: 'wcmp_afm_simple_auction_delete_bid',
                    logid: $( this ).data( 'id' ),
                    postid: $( this ).data( 'postid' ),
                    SA_nonce: wcmp_advance_product_params.SA_nonce,
                };

                $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {
                    if ( typeof response === "object" ) {
                        if ( response.hasOwnProperty( 'action' ) && response.action === 'deleted' ) {
                            $currentBid.remove();
                        }

                        if ( response.hasOwnProperty( 'auction_current_bid' ) && response.auction_current_bid ) {
                            $wrapper.find( 'span.higestbid' ).html( response.auction_current_bid );
                        }

                        if ( response.hasOwnProperty( 'auction_current_bider' ) && response.auction_current_bider ) {
                            $wrapper.find( 'span.higestbider' ).html( response.auction_current_bider );
                        }
                    }
                    $wrapper.unblock();
                } );
            }
            return false;
        },
        removeReserve: function removeReserve() {
            var $wrapper = $( '.reservefail' ),
                $current = $( this );

            if ( window.confirm( wcmp_advance_product_params.i18n_remove_reserve ) ) {

                $wrapper.block( {
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6
                    }
                } );

                var data = {
                    action: 'remove_reserve_price',
                    postid: $( this ).data( 'postid' ),
                    SA_nonce: wcmp_advance_product_params.SA_nonce,
                };

                $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {
                    if ( typeof response === "object" ) {
                        if ( response.hasOwnProperty( 'error' ) && response.error ) {
                            $current.after( response.error );
                        } else if ( response.hasOwnProperty( 'succes' ) && response.succes ) {
                            $( '#_auction_reserved_price' ).val( '' );
                            $wrapper.html( response.succes );
                        }
                    }
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
afmSimpleAuctionController.init( );