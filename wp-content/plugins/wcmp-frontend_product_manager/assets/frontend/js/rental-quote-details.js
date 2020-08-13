'use strict';
( function ( $ ) {
    var quoteDetails = {
        init: function () {
            $( 'form[name="quote-request-details"]' )
                .on( 'click', '.add-quote-reply', this.addNewComment )
                .on( 'click', '.wcmp-save-quote', this.updateQuote );

            $( '.quote-details-single' )
                .on( 'click', '.notice-wrapper button.notice-dismiss', this.dismissNotice );
        },
        addNewComment: function () {
            var $wrapper = $( 'form[name="quote-request-details"]' );
            var data = {
                action: 'wcmp_afm_rental_quote_reply',
                security: quote_details_params.add_message_nonce,
                message: $( '#add-quote-message' ).val().trim(),
                quote_id: quote_details_params.quote_id
            };

            $wrapper.block( {
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            } );
            var $messages = $wrapper.find( 'ul.rental-quote-message' );
            $.post( quote_details_params.ajax_url, data, function ( response ) {
                $messages.prepend( response );
                $wrapper.unblock();
            } );
            return false;
        },
        updateQuote: function () {
            var $wrapper = $( 'form[name="quote-request-details"]' );
            var data = {
                action: 'wcmp_afm_rental_update_quote',
                security: quote_details_params.update_quote_nonce,
                data: $( 'ul.quote_actions' ).find( 'input, select' ).serialize(),
                quote_id: quote_details_params.quote_id
            };

            $wrapper.block( {
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            } );
            $.post( quote_details_params.ajax_url, data, function ( response ) {
                $( '.woocommerce-error, .woocommerce-message' ).remove();
                $( '.quote-details-single .notice-wrapper' ).append( '<div class="woocommerce-message">' + response.message + '<button type="button" class="notice-dismiss"></button></div>' );
                $wrapper.unblock();
            } );
            return false;
        },
        dismissNotice: function ( ) {
            $( this ).parent().remove();
        }
    };
    quoteDetails.init();
} )( jQuery );
