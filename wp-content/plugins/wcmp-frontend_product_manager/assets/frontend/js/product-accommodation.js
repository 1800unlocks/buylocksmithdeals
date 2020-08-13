'use strict';
var afmAccommodationController = ( function ( $ ) {
    var privateApi = {
        setupEnvironment: function ( ) {
            this.resetProductOptions();
            $( '#woocommerce-product-data' ).find( 'input#_wc_accommodation_booking_user_can_cancel' ).change();
            $( '#accommodation_availability_product_data' ).find( '#_wc_accommodation_booking_has_restricted_days' ).change();
        },
        resetProductOptions: function () {
            var type = $( 'select#product-type' ).val();
            if ( type === "accommodation-booking" ) {
                $( '#_virtual' ).prop( 'checked', false );
            }
        },
        accommodationBookingCancelPreferenceChanged: function () {
            if ( this.checked ) {
                $( this ).closest( '.form-group-row' ).find( '.accommodation-booking-cancel-limit' ).show();
            } else {
                $( this ).closest( '.form-group-row' ).find( '.accommodation-booking-cancel-limit' ).hide();
            }
        },
        toggleDayRestrictions: function () {
            if ( this.checked ) {
                $( this ).closest( '#accommodation_availability_product_data' ).find( '.wc_booking_restricted_days_field' ).show();
            } else {
                $( this ).closest( '#accommodation_availability_product_data' ).find( '.wc_booking_restricted_days_field' ).hide();
            }
        },
    };
    var publicApi = {
        init: function () {
            $( '#woocommerce-product-data' )
                .on( 'afm-product-type-changed', privateApi.resetProductOptions )
                .on( 'change', 'input#_wc_accommodation_booking_user_can_cancel', privateApi.accommodationBookingCancelPreferenceChanged )
                .on( 'change', '#accommodation_availability_product_data #_wc_accommodation_booking_has_restricted_days', privateApi.toggleDayRestrictions )
                ;
            privateApi.setupEnvironment();
        }
    };
    return publicApi;
} )( jQuery );
afmAccommodationController.init();