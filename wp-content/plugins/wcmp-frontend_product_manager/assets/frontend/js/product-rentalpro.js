'use strict';
( function ( $ ) {
    var afmRentalProController = (function ( ) {
        var inventory = null;
        var availability = null;
        var priceCalculation = null;
        var priceDiscount = null;
        var settings = null;
        var utility = null;
        return {
            init: function () {
                utility = this.defineUtilities();
                //inventory tab
                inventory = this.inventoryController();
                inventory.init();
                availability = this.availabilityController();
                availability.init();
                //price calculation tab
                priceCalculation = this.priceCalculationController();
                priceCalculation.init();
                //price discount tab
                priceDiscount = this.priceDiscountController();
                priceDiscount.init();
                //settings tab
                settings = this.settingsController();
                settings.init();
                
                $( '#woocommerce-product-data' )
                    .on( 'afm-product-type-changed', this.resetProductOptions.bind( this ) );
                
                $( 'form#wcmp-afm-add-product' ).on( 'before_product_save', this.triggerFormSubmit );
            },
            defineUtilities: function () {
                return {
                    clean: function ( selector ) {
                        //sanitize invalid selectors
                        return $( selector ).length;
                    },
                    sortable: function ( $elem, items, handle, callback ) {
                        if ( !this.clean( $elem ) || !this.clean( items ) || !this.clean( handle ) ) {
                            return;
                        }
                        var placeholder = items.substr( 1 ) + '-sortable-placeholder';
                        var options = {
                            items: items,
                            cursor: 'move',
                            axis: 'y',
                            handle: handle,
                            scrollSensitivity: 40,
                            forcePlaceholderSize: true,
                            helper: 'clone',
                            opacity: 0.65,
                            placeholder: placeholder,
                            start: function ( event, ui ) {
                                ui.item.css( 'background-color', '#f6f6f6' );
                            },
                            stop: function ( event, ui ) {
                                ui.item.removeAttr( 'style' );
                                callback();
                            }
                        };
                        $( $elem ).sortable( options );
                    },
                    // Date picker fields.
                    datePickerSelect: function ( datepicker ) {
                        var option = $( datepicker ).closest('td').hasClass( 'from' ) ? 'minDate' : 'maxDate',
                            $otherDateField = 'minDate' === option ? $( datepicker ).closest('td').next('td').find( 'input' ) : $( datepicker ).closest('td').prev('td').find( 'input' ),
                            date = $( datepicker ).datepicker( 'getDate' );

                        $( $otherDateField ).datepicker( 'option', option, date );
                        $( datepicker ).change();
                        return;
                    },
                    multiInputSelect: function ( $wrapper ) {
                        $( $wrapper ).find( 'select.multiselect' ).select2( {
                            placeholder: "Choose ..."
                        } );
                    }
                }
            },
            resetProductOptions: function ( event, type ) {
                if ( type === 'redq_rental' ) {
                    $( 'input#_downloadable' ).prop( 'checked', false );
                    $( 'input#_virtual' ).prop( 'checked', false );
                }
            },
            triggerFormSubmit:function() {
                availability.triggerValidation();
                priceCalculation.triggerValidation();
                priceDiscount.triggerValidation();
            },
            inventoryController: function () {
                return {
                    init: function () {
                        $( '#rental_inventory_product_data' )
                            .on( 'blur', '.products_unique_name', this.updateSummary )
                            .on( 'click', 'button.add_inventory_item_action', this.addInventoryItem.bind(this) )
                            .on( 'click', 'a.remove-inventory-item', this.removeInventoryItem.bind(this) )
                            .on( 'click', '.expand_all', this.expandAllRows )
                            .on( 'click', '.close_all', this.closeAllRows );
                        utility.multiInputSelect( $( '#rental_inventory_product_data' ) );
                    },
                    updateSummary: function( ) {
                        $( this ).closest( '.redq_inventory' ).find( '.inventory-title .inventory_group .summary' ).text($( this ).val());
                    },
                    updateInventoryIndex: function ( ) {
                        $( '#rental_inventory_product_data .redq_inventory' ).each( function ( index, el ) {
                            $( '.form-control', el ).each( function () {
                                var name = $( this ).attr( 'name' );
                                var currentIndex = name.match( /^redq_inventory\[(\d+)\]/ )[1];
                                if( currentIndex ) {
                                    var modName = name.replace( 'redq_inventory[' + currentIndex + ']', 'redq_inventory[' + index + ']' );
                                    $( this ).attr( 'name', modName );
                                }
                            } );
                        } );
                    },
                    addInventoryItem: function ( e ) {
                        var ref = this;
                        
                        var $items = $( '.rental-inventory-wrapper .redq_inventory' ).get();
                        var size = 0;
                        if ( $items.length > 0 ) {
                            //get the heighest attr id
                            var size = $items.reduce( function ( a, b ) {
                                var mIndex = parseInt( $( b ).find( '.redq_inventory_data' ).attr( 'id' ).replace( 'redq_inventory_', '' ), 10 );
                                return ( isNaN( mIndex ) || a > mIndex ) ? a : mIndex;
                            }, 0 );
                            //next item index
                            ++size;
                        }
                        var $wrapper = $( '.rental-inventory-panel' );
                        var $resources = $wrapper.find( '.rental-inventory-wrapper' );
                        var data = {
                            action: 'wcmp_afm_rental_add_inventory_item',
                            i: size,
                            security: wcmp_advance_product_params.add_inventory_item_nonce
                        };

                        $wrapper.block( {
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        } );

                        $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {
                            $resources.append( response );
                            ref.updateInventoryIndex();
                            $( ".rental-inventory-wrapper .inventory-taxonomy" ).select2( {
                                placeholder: "Choose ..."
                            } );
                            $resources.find( '.redq_inventory' ).last().find( '.inventory-title' ).click();
                            $wrapper.unblock();
                        } );
                        return false;
                    },
                    removeInventoryItem: function ( e ) {
                        if ( window.confirm( wcmp_advance_product_params.remove_inventory_item ) ) {
                            $( e.target ).closest( '.redq_inventory' ).remove();
                            this.updateInventoryIndex();
                        }
                        return false;
                    },
                    expandAllRows: function ( ) {
                        $( this ).closest( '.rental-inventory-panel' ).find( '.redq_inventory > .redq_inventory_data' ).collapse( 'show' );
                        return false;
                    },
                    closeAllRows: function ( ) {
                        $( this ).closest( '.rental-inventory-panel' ).find( '.redq_inventory > .redq_inventory_data' ).collapse( 'hide' );
                        return false;
                    }
                };
            },
            availabilityController: function () {
                return {
                    init: function () {
                        $( '.rental-inventory-panel' )
                            .on( 'click', '.redq_inventory_data .resource_availabilities a.insert', this.addAvailabilityRow.bind( this ) )
                            .on( 'click', '.redq_inventory_data .resource_availabilities a.delete', this.removeAvailabilityRow.bind( this ) );
                        this.setupEnvironent();
                    },
                    setupEnvironent: function () {
                        $( '.rental-inventory-panel' ).find( '.resource_availabilities input[type="text"]' ).datepicker( {
                            defaultDate: '',
                            dateFormat: 'yy-mm-dd',
                            numberOfMonths: 1,
                            showButtonPanel: true,
                            onSelect: function () {
                                utility.datePickerSelect( $( this ) );
                            }
                        } ).on( 'change', function () {
                            if ( !$( this ).datepicker( 'getDate' ) ) {
                                var option = $( this ).closest('td').hasClass( 'from' ) ? 'minDate' : 'maxDate',
                                    $otherDateField = 'minDate' === option ? $( this ).closest('td').next('td').find( 'input' ) : $( this ).closest('td').prev('td').find( 'input' );
                                $( $otherDateField ).datepicker( 'option', option, null );
                            }
                            return false;
                        } );
                        $( '.rental-inventory-panel' ).find( '.resource_availabilities input[type="text"]' ).each( function () {
                            utility.datePickerSelect( $( this ) );
                        } );
                    },
                    updateAvailabilityIndex: function ( $wrap ) {
                        $wrap.find( 'table tbody tr' ).each( function ( index, el ) {
                            $( '.form-control', el ).each( function () {
                                var name = $( this ).attr( 'name' );
                                var currentIndex = name.match( /\[redq_rental_availability\]\[(\d+)\]/ )[1];
                                if( currentIndex ) {
                                    var modName = name.replace( '[redq_rental_availability][' + currentIndex + ']', '[redq_rental_availability][' + index + ']' );
                                    $( this ).attr( 'name', modName );
                                }
                            } );
                        } );
                    },
                    addAvailabilityRow: function ( e ) {
                        var ref = this;

                        var $items = $( e.target ).closest( '.resource_availabilities' ).find( 'table tbody tr' ).get();
                        var size = $items.length;
                        var parentSize = $( e.target ).closest( '.redq_inventory' ).index( '.rental-inventory-wrapper .redq_inventory' );
                        var $wrapper = $( e.target ).closest( '.resource_availabilities' );
                        var $availabilities = $wrapper.find( 'table tbody' );
                        var data = {
                            action: 'wcmp_afm_rental_add_availability',
                            i: parentSize,
                            j: size,
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
                            var $wrap = $( e.target ).closest( '.resource_availabilities' );
                            $( e.target ).closest( 'tr' ).remove();
                            this.updateAvailabilityIndex( $wrap );
                        }
                        return false;
                    },
                    triggerValidation: function() {
                        var ref = this;
                        $('#rental_inventory_product_data .redq_inventory').each( function() {
                            var unchanged = true;
                            $( this ).find( '.resource_availabilities table tbody tr' ).each( function() {
                                if( ! $( this ).find( 'td.from input' ).datepicker( 'getDate' ) || ! $( this ).find( 'td.to input' ).datepicker( 'getDate' ) ) {
                                    unchanged = false;
                                    $( this ).remove();
                                }    
                            });
                            if( ! unchanged ) {
                                ref.updateAvailabilityIndex( $( this ).find( '.resource_availabilities' ) );
                            }
                        });
                    }
                };
            },
            priceCalculationController: function () {
                return {
                    init: function () {
                        $( '#price_calculation_product_data' )
                            .on( 'change', 'select#pricing_type', this.pricingTypeChanged )
                            .on( 'click', 'button.add_days_range_action', this.addDaysRange.bind( this ) )
                            .on( 'click', 'a.remove-day-range', this.removeDaysRange.bind( this ) )
                            .on( 'blur', '.redq_day_range_data input:not([type=hidden])', this.updateSummary )
                            .on( 'click', '.expand_all', this.expandAllRanges )
                            .on( 'click', '.close_all', this.closeAllRanges )
                            ;
                        this.setupEnvironment();
                    },
                    setupEnvironment: function ( ) {
                        var $elem = $( '.days-range-wrapper.sortable' );
                        var items = '.redq_days_range';
                        var handle = '.days-range-title';

                        $( 'select#pricing_type' ).change();
                        utility.sortable( $elem, items, handle, this.updateRowIndices );
                    },
                    pricingTypeChanged: function ( ) {
                        var pricingType = $( 'select#pricing_type' ).val();
                        // Show all with rules.
                        var showClasses = '.show_if_general_pricing, .show_if_daily_pricing, .show_if_monthly_pricing, .show_if_days_range';
                        $( showClasses ).hide();
                        $( '.show_if_' + pricingType ).show();
                    },
                    updateRowIndices: function ( ) {
                        $( '#price_calculation_product_data .redq_days_range' ).each( function ( index, el ) {
                            $( '.form-control', el ).each( function () {
                                var name = $( this ).attr( 'name' );
                                var currentIndex = name.match( /^redq_day_ranges_cost\[(\d+)\]/ )[1];
                                if( currentIndex ) {
                                    var modName = name.replace( 'redq_day_ranges_cost[' + currentIndex + ']', 'redq_day_ranges_cost[' + index + ']' );
                                    $( this ).attr( 'name', modName );
                                }
                            } );
                        } );
                    },
                    addDaysRange: function ( ) {
                        var ref = this;
                        var $items = $( '.days-range-wrapper .redq_days_range' ).get();
                        var size = 0;
                        if ( $items.length > 0 ) {
                            //get the heighest attr id
                            var size = $items.reduce( function ( a, b ) {
                                var mIndex = parseInt( $( b ).find( '.redq_day_range_data' ).attr( 'id' ).replace( 'days_range_', '' ), 10 );
                                return ( isNaN( mIndex ) || a > mIndex ) ? a : mIndex;
                            }, 0 );
                            //next item index
                            ++size;
                        }
                        var $wrapper = $( '.days-range-panel' );
                        var $dayRanges = $wrapper.find( '.days-range-wrapper' );
                        var data = {
                            action: 'wcmp_afm_rental_add_days_range',
                            i: size,
                            security: wcmp_advance_product_params.add_days_range_nonce
                        };

                        $wrapper.block( {
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        } );

                        $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {
                            $dayRanges.append( response );
                            ref.updateRowIndices();
                            $dayRanges.find( '.redq_days_range' ).last().find( '.days-range-title' ).click();
                            $wrapper.unblock();
                        } );
                        return false;
                    },
                    removeDaysRange: function ( e ) {
                        if ( window.confirm( wcmp_advance_product_params.remove_days_range ) ) {
                            $( e.target ).closest( '.redq_days_range' ).remove();
                            this.updateRowIndices();
                        }
                        return false;
                    },
                    updateSummary: function ( e ) {
                        var elem = /\[\d+\]\[(\w+)\]$/.exec( e.target.name )[1];
                        var $summary = $( this ).closest( '.redq_days_range' ).find( '.days-range-title .summary' );
                        var val = $( this ).val();
                        switch ( elem ) {
                            case 'min_days':
                                $summary.find( '.min' ).text( val );
                                break;
                            case 'max_days':
                                $summary.find( '.max' ).text( val );
                                break;
                            case 'range_cost':
                                $summary.find( '.price' ).text( val );
                                break;
                        }
                    },
                    expandAllRanges: function ( ) {
                        $( this ).closest( '.days-range-panel' ).find( '.redq_days_range > .redq_day_range_data' ).collapse( 'show' );
                        return false;
                    },
                    closeAllRanges: function ( ) {
                        $( this ).closest( '.days-range-panel' ).find( '.redq_days_range > .redq_day_range_data' ).collapse( 'hide' );
                        return false;
                    },
                    triggerValidation: function() {
                        var unchanged = true;
                        $('#price_calculation_product_data .days-range-panel .redq_days_range').each( function() {
                            if( ! $( this ).find( 'input[name$="[min_days]"]' ).val() || ! $( this ).find( 'input[name$="[max_days]"]' ).val() || ! $( this ).find( 'input[name$="[range_cost]"]' ).val() ) {
                                unchanged = false;
                                $( this ).remove();
                            }
                        });
                        if( ! unchanged ) {
                            this.updateRowIndices();
                        }
                    }
                };
            },
            priceDiscountController: function () {
                return {
                    init: function () {
                        $( '#price_discount_product_data' )
                            .on( 'click', 'button.add_price_discount_action', this.addPriceDiscount.bind( this ) )
                            .on( 'click', 'a.remove-price-discount', this.removePriceDiscount.bind( this ) )
                            .on( 'blur', '.redq_price_discount_data input:not([type=hidden])', this.updateSummary )
                            .on( 'change', '.redq_price_discount_data select', this.updateSummary )
                            .on( 'click', '.expand_all', this.expandAllRows )
                            .on( 'click', '.close_all', this.closeAllRows )
                            ;
                        this.setupEnvironment();
                    },
                    setupEnvironment: function ( ) {
                        var $elem = $( '.price-discount-wrapper.sortable' );
                        var items = '.redq_price_discount';
                        var handle = '.price-discount-title';
                        utility.sortable( $elem, items, handle, this.updateRowIndices );
                    },
                    updateRowIndices: function ( ) {
                        $( '#price_discount_product_data .redq_price_discount' ).each( function ( index, el ) {
                            $( '.form-control', el ).each( function () {
                                var name = $( this ).attr( 'name' );
                                var currentIndex = name.match( /^redq_price_discount_cost\[(\d+)\]/ )[1];
                                if( currentIndex ) {
                                    var modName = name.replace( 'redq_price_discount_cost[' + currentIndex + ']', 'redq_price_discount_cost[' + index + ']' );
                                    $( this ).attr( 'name', modName );
                                }
                            } );
                        } );
                    },
                    addPriceDiscount: function ( ) {
                        var ref = this;
                        var $items = $( '.price-discount-wrapper .redq_price_discount' ).get();
                        var size = 0;
                        if ( $items.length > 0 ) {
                            //get the heighest attr id
                            var size = $items.reduce( function ( a, b ) {
                                var mIndex = parseInt( $( b ).find( '.redq_price_discount_data' ).attr( 'id' ).replace( 'price_discount_', '' ), 10 );
                                return ( isNaN( mIndex ) || a > mIndex ) ? a : mIndex;
                            }, 0 );
                            //next item index
                            ++size;
                        }
                        var $wrapper = $( '.price-discount-panel' );
                        var $discounts = $wrapper.find( '.price-discount-wrapper' );
                        var data = {
                            action: 'wcmp_afm_rental_add_price_discount',
                            i: size,
                            security: wcmp_advance_product_params.add_price_discount_nonce
                        };

                        $wrapper.block( {
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        } );

                        $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {
                            $discounts.append( response );
                            ref.updateRowIndices();
                            $discounts.find( '.redq_price_discount' ).last().find( '.price-discount-title' ).click();
                            $wrapper.unblock();
                        } );
                        return false;
                    },
                    removePriceDiscount: function ( e ) {
                        if ( window.confirm( wcmp_advance_product_params.remove_price_discount ) ) {
                            $( e.target ).closest( '.redq_price_discount' ).remove();
                            this.updateRowIndices();
                        }
                        return false;
                    },
                    updateSummary: function ( e ) {
                        var elem = /\[\d+\]\[(\w+)\]$/.exec( e.target.name )[1];
                        var $summary = $( this ).closest( '.redq_price_discount' ).find( '.price-discount-title .summary' );
                        var val = $( this ).val();
                        switch ( elem ) {
                            case 'min_days':
                                $summary.find( '.min' ).text( val );
                                break;
                            case 'max_days':
                                $summary.find( '.max' ).text( val );
                                break;
                            case 'discount_amount':
                                $summary.find( '.discount' ).text( val );
                                break;
                            case 'discount_type':
                                var symbol = '%';
                                if ( val === 'fixed' ) {
                                    symbol = wcmp_advance_product_params.currency_symbol;
                                }
                                $summary.find( '.symbol' ).text( symbol );
                                break;
                        }
                    },
                    expandAllRows: function ( ) {
                        $( this ).closest( '.price-discount-panel' ).find( '.redq_price_discount > .redq_price_discount_data' ).collapse( 'show' );
                        return false;
                    },
                    closeAllRows: function ( ) {
                        $( this ).closest( '.price-discount-panel' ).find( '.redq_price_discount > .redq_price_discount_data' ).collapse( 'hide' );
                        return false;
                    },
                    triggerValidation: function() {
                        var unchanged = true;
                        $('#price_discount_product_data .price-discount-panel .redq_price_discount').each( function() {
                            if( ! $( this ).find( 'input[name$="[min_days]"]' ).val() || ! $( this ).find( 'input[name$="[max_days]"]' ).val() || ! $( this ).find( 'input[name$="[discount_amount]"]' ).val() ) {
                                unchanged = false;
                                $( this ).remove();
                            }
                        });
                        if( ! unchanged ) {
                            this.updateRowIndices();
                        }
                    }
                };
            },
            settingsController: function () {
                return {
                    init: function () {
                        $( '#redq_settings_product_data' )
                            .on( 'change', 'select.redq_settings_preference', this.preferenceChanged )
                            .on( 'settings_parent_tab_is_active', this.resetSubTabs )
                            ;

                        $( '#redq_settings_product_data select.redq_settings_preference' ).each( function () {
                            $( this ).change();
                        } );

                        $( 'a[data-toggle="tab"]' ).on( 'shown.bs.tab', function ( e ) {
                            if ( $( e.target ).attr( 'href' ) === '#redq_settings_product_data' ) {
                                $( '#redq_settings_product_data' ).trigger( 'settings_parent_tab_is_active' );
                            }
                        } );
                        utility.multiInputSelect( $( '#redq_settings_product_data' ) );
                    },
                    resetSubTabs: function ( ) {
                        $( 'ul#rental_settings_nav_tabs li:first' ).find( 'a' ).tab( 'show' );
                    },
                    preferenceChanged: function ( ) {
                        var preference = $( this ).val();
                        var $wrapper = $( this ).closest( '.tab-pane' );
                        // Show all with rules.
                        var showClasses = '.show_if_local';
                        $( $wrapper ).find( showClasses ).hide();
                        $( $wrapper ).find( '.show_if_' + preference ).show();
                    }
                };
            }
        };
    })();
    afmRentalProController.init();
} )( jQuery );

