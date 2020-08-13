'use strict';
( function ( $ ) {
    var afmRentalController = function ( ) {
        var availability = null;
        var utility = null;
        return {
            init: function () {
                utility = this.defineUtilities();
                //inventory tab
                availability = this.availabilityController();
                availability.init();
                
                $( '#woocommerce-product-data' )
                    .on( 'afm-product-type-changed', this.resetProductOptions.bind( this ) );
                $( 'form#wcmp-afm-add-product' ).on( 'before_product_save', this.triggerFormSubmit );
            },
            defineUtilities: function () {
                return {
                    // Date picker fields.
                    datePickerSelect: function ( datepicker ) {
                        var option = $( datepicker ).parent().hasClass( 'from' ) ? 'minDate' : 'maxDate',
                            $otherDateField = 'minDate' === option ? $( datepicker ).parent().next().find( 'input' ) : $( datepicker ).parent().prev().find( 'input' ),
                            date = $( datepicker ).datepicker( 'getDate' );

                        $( $otherDateField ).datepicker( 'option', option, date );
                        $( datepicker ).change();
                        return;
                    }
                };
            },
            resetProductOptions: function ( event, type ) {
                if ( type === 'redq_rental' ) {
                    $( 'input#_downloadable' ).prop( 'checked', false );
                    $( 'input#_virtual' ).prop( 'checked', false );
                }
            },
            triggerFormSubmit:function() {
                availability.triggerValidation();
            },
            availabilityController: function () {
                return {
                    init: function () {
                        $( '#availability_product_data' )
                            .on( 'click', '.rental-availability-wrapper a.insert', this.addAvailabilityRow.bind( this ) )
                            .on( 'click', '.rental-availability-wrapper a.delete', this.removeAvailabilityRow.bind( this ) );
                        
                        this.setupEnvironent();
                    },
                    setupEnvironent: function () {
                        var pricingType = $( 'select#pricing_type' ).val();
                        var showClasses = '.show_if_general_pricing, .show_if_daily_pricing, .show_if_monthly_pricing, .show_if_days_range';
                        $( showClasses ).hide();
                        $( '.show_if_' + pricingType ).show();
                        
                        $( '#availability_product_data .rental-availability-wrapper input[type="text"]' ).datepicker( {
                            defaultDate: '',
                            dateFormat: 'yy-mm-dd',
                            numberOfMonths: 1,
                            showButtonPanel: true,
                            onSelect: function () {
                                utility.datePickerSelect( $( this ) );
                            }
                        } ).on( 'change', function () {
                            if ( !$( this ).datepicker( 'getDate' ) ) {
                                var option = $( this ).parent().hasClass( 'from' ) ? 'minDate' : 'maxDate',
                                    $otherDateField = 'minDate' === option ? $( this ).parent().next().find( 'input' ) : $( this ).parent().prev().find( 'input' );
                                $( $otherDateField ).datepicker( 'option', option, null );
                            }
                            return false;
                        } );
                        $( '#availability_product_data .rental-availability-wrapper input[type="text"]' ).each( function () {
                            utility.datePickerSelect( $( this ) );
                        } );
                    },
                    updateAvailabilityIndex: function ( $wrap ) {
                        $wrap.find( 'table tbody tr' ).each( function ( index, el ) {
                            $( '.form-control', el ).each( function () {
                                var name = $( this ).attr( 'name' );
                                var currentIndex = name.match( /^redq_rental_availability\[(\d+)\]/ )[1];
                                if( currentIndex ) {
                                    var modName = name.replace( 'redq_rental_availability[' + currentIndex + ']', 'redq_rental_availability[' + index + ']' );
                                    $( this ).attr( 'name', modName );
                                }
                            } );
                        } );
                    },
                    addAvailabilityRow: function ( e ) {
                        var ref = this;

                        var $items = $( e.target ).closest( '.rental-availability-wrapper' ).find( 'table tbody tr' ).get();
                        var size = $items.length;
                        var $wrapper = $( e.target ).closest( '.rental-availability-wrapper' );
                        var $availabilities = $wrapper.find( 'table tbody' );
                        var data = {
                            action: 'wcmp_afm_rental_free_add_availability',
                            i: size,
                            security: wcmp_advance_product_params.add_availability_nonce
                        };

                        $wrapper.block( {
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        } );

                        $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {
                            $availabilities.append( response );
                            ref.setupEnvironent();
                            ref.updateAvailabilityIndex( $wrapper );
                            $wrapper.unblock();
                        } );
                        return false;
                    },
                    removeAvailabilityRow: function ( e ) {
                        if ( window.confirm( wcmp_advance_product_params.remove_availability ) ) {
                            var $wrap = $( e.target ).closest( '.rental-availability-wrapper' );
                            $( e.target ).closest( 'tr' ).remove();
                            this.updateAvailabilityIndex( $wrap );
                        }
                        return false;
                    },
                    triggerValidation: function() {
                        var unchanged = true;
                        $( '#availability_product_data .rental-availability-wrapper table tbody tr' ).each( function() {
                            if( ! $( this ).find( 'td.from input' ).datepicker( 'getDate' ) || ! $( this ).find( 'td.to input' ).datepicker( 'getDate' ) ) {
                                unchanged = false;
                                $( this ).remove();
                            }    
                        });
                        if( ! unchanged ) {
                            this.updateAvailabilityIndex( $( '#availability_product_data .rental-availability-wrapper' ) );
                        }
                    }
                };
            }
        };
    }();
    afmRentalController.init();
} )( jQuery );