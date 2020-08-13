'use strict';
( function ( $ ) {
    var afmBookingController = function ( ) {
        var utility = null,
            persons = null,
            resources = null;
        return {
            init: function () {
                utility = this.defineUtilities();
                persons = this.personsController();
                persons.init();
                resources = this.resourcesController();
                resources.init();
                //Product type select change
                $( '#woocommerce-product-data' )
                    .on( 'afm-product-type-changed', this.resetProductOptions.bind( this ) )
                    .on( 'change', 'input#_wc_booking_has_persons, input#_wc_booking_has_resources', this.updateTabsDisplay.bind( this ) )
                    .on( 'change', 'select#_wc_booking_duration_type, input#_wc_booking_duration, select#_wc_booking_duration_unit', this.bookingDurationTypeChanged )
                    .on( 'change', 'input#_wc_booking_user_can_cancel', this.bookingCancelPreferenceChanged )
                    .on( 'change', '.wc_booking_pricing_type select, .wc_booking_availability_type select', this.pricingTypeChanged.bind( this ) )
                    .on( 'click', '.booking_range_pricing a.insert, .booking_availability a.insert', this.insertRangeCost.bind( this ) )
                    .on( 'click', '.booking_range_pricing a.delete, .booking_availability a.delete', this.deleteRangeCost.bind( this ) )
                    .on( 'change', '#booking_availability_product_data #_wc_booking_has_restricted_days', this.toggleDayRestrictions )
                    ;

                this.setupEnvironment();
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
                                callback( $elem );
                            }
                        };
                        $( $elem ).sortable( options );
                    },
                    // Date picker fields.
                    datePickerSelect: function ( datepicker ) {
                        var $td = $( datepicker ).closest( 'td' ),
                            $tr = $td.closest( 'tr' ),
                            option = $td.children().hasClass( 'bookings-datetime-select-from' ) ? 'minDate' : 'maxDate',
                            $otherDateField = $tr.find( '.date-picker' ).not( datepicker ),
                            date = $( datepicker ).datepicker( 'getDate' );
                        $( $otherDateField ).datepicker( 'option', option, date );
                        $( datepicker ).change();
                        return;
                    }
                };
            },
            resetProductOptions: function () {
                var type = $( 'select#product-type' ).val();

                if ( type !== 'booking' && type !== "accommodation-booking" ) {
                    $( '#_wc_booking_has_persons' ).prop( 'checked', false );
                    $( '#_wc_booking_has_resources' ).prop( 'checked', false );
                } else {
                    $( 'input#_downloadable' ).prop( 'checked', false );
                }
                this.updateTabsDisplay();
            },
            updateTabsDisplay: function ( event ) {
                var hasPerson = $( 'input#_wc_booking_has_persons:checked' ).length;
                var hasResources = $( 'input#_wc_booking_has_resources:checked' ).length;

                var showClasses = '.show_if_has_persons, .show_if_has_resources';

                $( showClasses ).hide();
                // Shows rules.
                if ( hasPerson ) {
                    $( '.show_if_has_persons' ).show();
                }
                if ( hasResources ) {
                    $( '.show_if_has_resources' ).show();
                }
                if ( typeof event !== 'undefined' ) { //trigger only if person or resource checkbox changed
                    $( '#product_data_tabs' ).trigger( 'tab-display-updated' );
                }
            },
            setupEnvironment: function ( ) {
                //on first time load reset tabs display
                this.resetProductOptions();

                $( '#woocommerce-product-data' ).find( 'select#_wc_booking_duration_type' ).change();
                $( '#woocommerce-product-data' ).find( 'input#_wc_booking_user_can_cancel' ).change();
                $( '#woocommerce-product-data' ).find( '.wc_booking_pricing_type select' ).change();
                $( '#woocommerce-product-data' ).find( '.wc_booking_availability_type select' ).change();
                $( '#booking_availability_product_data' ).find( '#_wc_booking_has_restricted_days' ).change();
            },
            bookingDurationTypeChanged: function () {
                var $wrap = $( this ).closest( '.form-group-row' );
                var rangePicker = false;
                if ( $( 'select#_wc_booking_duration_type' ).val() === 'customer' ) {
                    $wrap.find( '.show_if_customer' ).show();
                    if ( $( 'select#_wc_booking_duration_unit' ).val() === 'day' && $( 'input#_wc_booking_duration' ).val() == '1' ) {
                        rangePicker = true;
                    }
                } else {
                    $wrap.find( '.show_if_customer' ).hide();
                }
                if ( rangePicker ) {
                    $wrap.find( '.form-group.date-range-picker' ).show();
                } else {
                    $wrap.find( '.form-group.date-range-picker' ).hide();
                }
            },
            bookingCancelPreferenceChanged: function () {
                if ( this.checked ) {
                    $( this ).closest( '.form-group-row' ).find( '.booking-cancel-limit' ).show();
                } else {
                    $( this ).closest( '.form-group-row' ).find( '.booking-cancel-limit' ).hide();
                }
            },
            pricingTypeChanged: function ( e ) {
                var value = $( e.target ).val(),
                    $row = $( e.target ).closest( 'tr' );

                // cleanup
                $row.find( '.bookings-datetime-select-from, .bookings-datetime-select-to' ).children( 'div' ).hide();
                //row.find( '.repeating-label' ).hide();
                $row.find( '.bookings-datetime-select-from, .bookings-datetime-select-to' ).removeClass( 'bookings-datetime-select-both' );
                //row.find( '.bookings-datetime-select-from' ).removeClass( 'bookings-datetime-select-both' );
                $row.find( '.bookings-to-label-row .bookings-datetimerange-second-label' ).hide();

                if ( value === 'custom' ) {
                    $row.find( '.from_date, .to_date' ).show();
                } else if ( value === 'months' ) {
                    $row.find( '.from_month, .to_month' ).show();
                } else if ( value === 'weeks' ) {
                    $row.find( '.from_week, .to_week' ).show();
                } else if ( value === 'days' ) {
                    $row.find( '.from_day_of_week, .to_day_of_week' ).show();
                } else if ( value.match( "^time" ) ) {
                    $row.find( '.from_time, .to_time' ).show();
                    // Show the date range as well if "time range for custom dates" is selected
                    if ( 'time:range' === value ) {
                        $row.find( '.from_date, .to_date' ).show();
                        //$row.find( '.repeating-label' ).show();
                        $row.find( '.bookings-datetime-select-from, .bookings-datetime-select-to' ).addClass( 'bookings-datetime-select-both' );
                        //$row.find( '.bookings-datetime-select-from' ).addClass( 'bookings-datetime-select-both' );
                        $row.find( '.bookings-to-label-row .bookings-datetimerange-second-label' ).show();
                    }
                } else if ( value === 'persons' || value === 'duration' || value === 'blocks' ) {
                    $row.find( '.from, .to' ).show();
                }
                this.updateDisplay( $row );
            },
            updateDisplay: function ( $row ) {
                var $elem = $row.closest( 'tbody' );
                var items = 'tr';
                var handle = 'td.sort';
                utility.sortable( $elem, items, handle, this.updateRowIndices );

                $( '.date-picker', $elem ).datepicker( {
                    defaultDate: '',
                    dateFormat: 'm/d/yy',
                    numberOfMonths: 1,
                    showButtonPanel: true,
                    onSelect: function () {
                        utility.datePickerSelect( $( this ) );
                    }
                } ).on( 'change', function () {
                    if ( !$( this ).datepicker( 'getDate' ) ) {
                        var $td = $( this ).closest( 'td' ),
                            $tr = $td.closest( 'tr' ),
                            option = $td.children().hasClass( 'bookings-datetime-select-from' ) ? 'minDate' : 'maxDate',
                            $otherDateField = $tr.find( '.date-picker' ).not( this );
                        $( $otherDateField ).datepicker( 'option', option, null );
                    }
                    return false;
                } );
                $( '.date-picker', $elem ).each( function () {
                    utility.datePickerSelect( $( this ) );
                } );
            },
            updateRowIndices: function ( $tbody ) {
                $tbody.find( 'tr' ).each( function ( index, el ) {
                    $( '[name*="wc_booking"]', el ).each( function () {
                        var oldName = $( this ).attr( 'name' ),
                            newName = oldName.replace( /[\d+]/g, index );
                        if ( oldName !== newName ) {
                            $( this ).attr( 'name', newName );
                        }
                    } );
                } );
            },
            insertRangeCost: function ( e ) {
                var $table = $( e.target ).closest( 'table' );
                var newRowIndex = $table.find( 'tbody tr' ).length;
                var newRow = $( e.target ).data( 'row' );
                var $wrap = $( e.target ).closest( 'table' ).find( 'tbody' );
                newRow = newRow.replace( /bookings_cost_js_index_replace/ig, newRowIndex.toString() );
                $wrap.append( newRow );
                this.updateRowIndices( $wrap );
                $wrap.find( 'tr:last select:first' ).change();
                return false;
            },
            deleteRangeCost: function ( e ) {
                var $tbody = $( e.target ).closest( 'tbody' );
                $( e.target ).closest( 'tr' ).remove();
                this.updateRowIndices( $tbody );
                return false;
            },
            toggleDayRestrictions: function () {
                if ( this.checked ) {
                    $( this ).closest( '#booking_availability_product_data' ).find( '.wc_booking_restricted_days_field' ).show();
                } else {
                    $( this ).closest( '#booking_availability_product_data' ).find( '.wc_booking_restricted_days_field' ).hide();
                }
            },
            personsController: function () {
                return {
                    init: function () {
                        //Event Listners
                        $( '#booking_persons_product_data' )
                            .on( 'change', 'input.person_name', this.changePersonName )
                            .on( 'change', 'input#_wc_booking_has_person_types', this.togglePersonTypes )
                            .on( 'click', 'button.add_person', this.addPerson.bind( this ) )
                            .on( 'click', 'a.remove_person', this.removePerson.bind( this ) )
                            .on( 'click', '.expand_all', this.expandAllPersons )
                            .on( 'click', '.close_all', this.closeAllPersons )
                            ;
                        this.setupEnvironment();
                    },
                    setupEnvironment: function ( ) {
                        var $elem = $( '.booking-persons-wrapper' );
                        var items = '.wcmp-metabox-wrapper';
                        var handle = '.wcmp-metabox-title';
                        $( '#booking_persons_product_data input#_wc_booking_has_person_types' ).change();
                        utility.sortable( $elem, items, handle, this.updateRowIndices );
                    },
                    changePersonName: function () {
                        $( this ).closest( '.wcmp-metabox-wrapper' ).find( 'span.person_title' ).text( $( this ).val() );
                    },
                    togglePersonTypes: function () {
                        if ( this.checked ) {
                            $( this ).closest( '#booking_persons_product_data' ).find( '.has-person-types' ).show();
                        } else {
                            $( this ).closest( '#booking_persons_product_data' ).find( '.has-person-types' ).hide();
                        }
                    },
                    addPerson: function () {
                        var ref = this;
                        var loop = $( '#booking_persons_product_data .booking_person' ).length;
                        var $wrapper = $( '#booking_persons_product_data' );
                        var $persons = $wrapper.find( '.booking-persons-wrapper' );

                        var data = {
                            action: 'wcmp_afm_add_bookable_person',
                            post_id: wcmp_advance_product_params.product_id,
                            loop: loop,
                            security: wcmp_advance_product_params.add_person_nonce
                        };

                        $wrapper.block( {
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        } );

                        $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {
                            $persons.append( response );

                            ref.updateRowIndices();
                            //open the added attribute in expanded view
                            $persons.find( '.wcmp-metabox-wrapper' ).last().find( '.wcmp-metabox-title' ).click();

                            $wrapper.unblock();
                        } );
                        return false;
                    },
                    removePerson: function ( e ) {
                        if ( window.confirm( wcmp_advance_product_params.i18n_remove_person ) ) {
                            var ref = this;
                            var $person = $( e.target ).closest( '.wcmp-metabox-wrapper' );
                            var personId = $person.attr( 'rel' );
                            var $wrapper = $( '#booking_persons_product_data' );
                            if ( personId > 0 ) {
                                var data = {
                                    action: 'wcmp_afm_unlink_bookable_person',
                                    person_id: personId,
                                    security: wcmp_advance_product_params.unlink_person_nonce
                                };
                                $wrapper.block( {
                                    message: null,
                                    overlayCSS: {
                                        background: '#fff',
                                        opacity: 0.6
                                    }
                                } );
                                $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {
                                    //after successful unlinking remove the person
                                    $person.remove();
                                    ref.updateRowIndices();
                                    $wrapper.unblock();
                                } );
                            }
                        }
                        return false;
                    },
                    updateRowIndices: function ( ) {
                        $( '#booking_persons_product_data .booking-persons-wrapper .wcmp-metabox-wrapper' ).each( function ( index, el ) {
                            $( '.person_menu_order', el ).val( parseInt( $( el ).index( '.booking-persons-wrapper .wcmp-metabox-wrapper' ), 10 ) );
                        } );
                    },
                    expandAllPersons: function () {
                        $( this ).closest( '#booking_persons_product_data' ).find( '.wcmp-metabox-wrapper > .wcmp-metabox-content' ).collapse( 'show' );
                        return false;
                    },
                    closeAllPersons: function () {
                        $( this ).closest( '#booking_persons_product_data' ).find( '.wcmp-metabox-wrapper > .wcmp-metabox-content' ).collapse( 'hide' );
                        return false;
                    }
                }
            },
            resourcesController: function () {
                return {
                    init: function () {
                        //Event Listners
                        $( '#booking_resources_product_data' )
                            .on( 'click', 'button.add_resource', this.addResource.bind( this ) )
                            .on( 'click', 'a.remove_resource', this.removeResource.bind( this ) )
                            .on( 'click', '.expand_all', this.expandAllResources )
                            .on( 'click', '.close_all', this.closeAllResources )
                            ;
                        this.setupEnvironment();
                    },
                    setupEnvironment: function ( ) {
                        var $elem = $( '.bookable-resources-wrapper' );
                        var items = '.wcmp-metabox-wrapper';
                        var handle = '.wcmp-metabox-title';
                        utility.sortable( $elem, items, handle, this.updateRowIndices );
                    },
                    addResource: function () {
                        var ref = this;
                        var loop = $( '#booking_resources_product_data .booking_person' ).length;
                        var $wrapper = $( '#booking_resources_product_data' );
                        var $resources = $wrapper.find( '.bookable-resources-wrapper' );

                        var addResourceId = $wrapper.find( 'select.add_resource_id' ).val();
                        var addResourceName = '';

                        if ( !addResourceId ) {
                            addResourceName = prompt( wcmp_advance_product_params.i18n_new_resource_name );

                            if ( !addResourceName ) {
                                return false;
                            }
                        }

                        var data = {
                            action: 'wcmp_afm_add_bookable_resource',
                            post_id: wcmp_advance_product_params.product_id,
                            loop: loop,
                            add_resource_id: addResourceId,
                            add_resource_name: addResourceName,
                            security: wcmp_advance_product_params.add_resource_nonce
                        };

                        $wrapper.block( {
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        } );

                        $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {
                            if ( response.error ) {
                                alert( response.error );
                            } else {
                                if ( addResourceId ) {
                                    $( '.add_resource_id' ).find( 'option[value=' + addResourceId + ']' ).remove();
                                }
                                $resources.append( response.html );

                                ref.updateRowIndices();
                                //open the added attribute in expanded view
                                $resources.find( '.wcmp-metabox-wrapper' ).last().find( '.wcmp-metabox-title' ).click();
                            }
                            $wrapper.unblock();
                        } );
                        return false;
                    },
                    removeResource: function ( e ) {
                        if ( window.confirm( wcmp_advance_product_params.i18n_remove_resource ) ) {
                            var ref = this;
                            var $resource = $( e.target ).closest( '.wcmp-metabox-wrapper' );
                            var resourceId = $resource.find( 'input[name*=resource_id]' ).val();
                            var resourceTitle = $resource.find( 'input[name*=resource_title]' ).val();
                            var $wrapper = $( '#booking_resources_product_data' );
                            if ( resourceId > 0 ) {
                                var data = {
                                    action: 'wcmp_afm_remove_bookable_resource',
                                    post_id: wcmp_advance_product_params.product_id,
                                    resource_id: resourceId,
                                    security: wcmp_advance_product_params.remove_resource_nonce
                                };
                                $wrapper.block( {
                                    message: null,
                                    overlayCSS: {
                                        background: '#fff',
                                        opacity: 0.6
                                    }
                                } );
                                $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {
                                    $( 'select[name=add_resource_id]' ).append( $( '<option>', {
                                        value: resourceId,
                                        text: resourceTitle
                                    } ) );
                                    //after successful unlinking remove the person
                                    $resource.remove();
                                    ref.updateRowIndices();
                                    $wrapper.unblock();
                                } );
                            }
                        }
                        return false;
                    },
                    updateRowIndices: function ( ) {
                        $( '#booking_resources_product_data .bookable-resources-wrapper .wcmp-metabox-wrapper' ).each( function ( index, el ) {
                            $( '.resource_menu_order', el ).val( parseInt( index, 10 ) );
                        } );
                    },
                    expandAllResources: function () {
                        $( this ).closest( '#booking_resources_product_data' ).find( '.wcmp-metabox-wrapper > .wcmp-metabox-content' ).collapse( 'show' );
                        return false;
                    },
                    closeAllResources: function () {
                        $( this ).closest( '#booking_resources_product_data' ).find( '.wcmp-metabox-wrapper > .wcmp-metabox-content' ).collapse( 'hide' );
                        return false;
                    }
                }
            }
        };
    }();
    afmBookingController.init();
} )( jQuery );