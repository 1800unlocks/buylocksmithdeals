'use strict';
var afmBundleController = ( function ( $ ) {
    var privateApi = {
        bundledItems: null,
        addEventHandlers: function addEventHandlers( ) {
            $( '#woocommerce-product-data' )
                .on( 'afm-product-type-changed', this.resetProductOptions )
                // .on( 'change', 'input#_wc_accommodation_booking_user_can_cancel', privateApi.accommodationBookingCancelPreferenceChanged )
                // .on( 'change', '#accommodation_availability_product_data #_wc_accommodation_booking_has_restricted_days', privateApi.toggleDayRestrictions )
                ;
            $( '#bundled_products_data' )
                .on( 'change', 'select#_wc_pb_group_mode', this.groupModeChanged )
                ;
        },
        setupEnvironment: function setupEnvironment( ) {
            this.resetProductOptions( );
            this.bundledItems = this.bundleItemsController( );
            this.bundledItems.init( );
        },
        resetProductOptions: function resetProductOptions( ) {
            var type = $( 'select#product-type' ).val( );
            if ( type === "bundle" ) {
                //$( '.show_if_external' ).hide( );
                //$( '.show_if_bundle' ).show( );
                $( 'input#_manage_stock' ).change( );
                $( 'select#_wc_pb_group_mode' ).change( );
            }
        },
        groupModeChanged: function groupModeChanged( ) {
            var $edit_in_cart = $( '.bundle_edit_in_cart' );
            if ( ~( wcmp_advance_product_params.group_modes_with_parent ).indexOf( $( this ).val( ) ) ) {
                $edit_in_cart.show( );
            } else {
                $edit_in_cart.hide( );
            }
        },
        bundleItemsController: function bundleItemsController( ) {
            var ref = this,
                $bundleTab = $( '#bundled_products_data' ),
                $itemContainer = $( '.bundle_product_items', $bundleTab );
            var returnObj = {
                init: function init( ) {
                    $bundleTab
                        .on( 'change', 'select#bundled_product', this.addBundleItem )
                        .on( 'click', 'a.remove_row', this.removeAttribute )
                        .on( 'click', 'a.expand_all', this.expandAllProducts )
                        .on( 'click', 'a.close_all', this.closeAllProducts )
                        ;
                    $itemContainer
                        .on( 'change', '.wc-bundled-item .config .priced_individually input', this.pricedIndividuallyInputChanged )
                        .on( 'change', '.wc-bundled-item .advanced .override_title input', this.overrideTitleInputChanged )
                        .on( 'change', '.wc-bundled-item .advanced .override_description input', this.overrideDescriptionInputChanged )
                        ;
                    $itemContainer.find( '.wc-bundled-item' ).each( function ( ) {
                        returnObj.setupBundleItem( $( this ) );
                    } );
                    publicApi.sortable( $itemContainer, '.wc-bundled-item', '.item-title span.sortable-icon', this.updateRowIndices );
                },
                setupBundleItem: function setupBundleItem( $item ) {
                    if ( !publicApi.clean( $item ) ) {
                        return;
                    }
                    $item.find( '.config .priced_individually input' ).change( );
                    $item.find( '.advanced .override_title input' ).change( );
                    $item.find( '.advanced .override_description input' ).change( );
                },
                updateRowIndices: function updateRowIndices( ) {
                    var $items = $( '.wc-bundled-item', $itemContainer );
                    $items.each( function ( index, el ) {
                        $( '.item_menu_order', el ).val( index );
                    } );
                },
                addBundleItem: function addBundleItem( ) {
                    var bundledProductId = this.value || false;
                    if ( !bundledProductId ) {
                        return false;
                    }
                    $( this ).val( [ ] ).change( );
                    var $items = $( '.wc-bundled-item', $itemContainer );
                    var data = {
                        action: 'wcmp_afm_add_product_to_bundle',
                        post_id: wcmp_advance_product_params.product_id,
                        id: $items.length,
                        product_id: bundledProductId,
                        security: wcmp_advance_product_params.add_bundled_product_nonce
                    };
                    $bundleTab.block( {
                        message: null,
                        overlayCSS: {
                            background: '#fff',
                            opacity: 0.6
                        }
                    } );
                    $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {

                        if ( '' !== response.markup ) {

                            $itemContainer.append( response.markup );
                            var $lastItem = $itemContainer.find( '.wc-bundled-item' ).last( );
                            publicApi.sortable( $itemContainer, '.wc-bundled-item', '.item-title span.sortable-icon', returnObj.updateRowIndices );
                            returnObj.setupBundleItem( $lastItem );
                            afmLibrary.qtip();
                            //open the added product in expanded view
                            $lastItem.find( '.item-title' ).click( );
                            //$bundled_products_panel.trigger( 'wc-bundles-added-bundled-product' );
                        } else if ( response.message !== '' ) {
                            window.alert( response.message );
                        }

                        $bundleTab.unblock( );
                    } );
                },
                removeAttribute: function removeAttribute( ) {
                    if ( window.confirm( wcmp_advance_product_params.i18n_remove_bundled_product ) ) {
                        var $item = $( this ).closest( '.wc-bundled-item' );
                        $item.remove( );
                        returnObj.updateRowIndices( );
                    }
                    return false;
                },
                expandAllProducts: function expandAllProducts( ) {
                    $itemContainer.find( '.wcmp-metabox-wrapper > .wcmp-metabox-content' ).collapse( 'show' );
                    return false;
                },
                closeAllProducts: function closeAllProducts( ) {
                    $itemContainer.find( '.wcmp-metabox-wrapper > .wcmp-metabox-content' ).collapse( 'hide' );
                    return false;
                },
                pricedIndividuallyInputChanged: function pricedIndividuallyInputChanged( ) {
                    if ( this.checked ) {
                        $( this ).closest( '.priced_individually' ).siblings( '.discount' ).show( );
                        $( this ).closest( '.tab-content' ).find( '.advanced .price_visibility' ).show( );
                    } else {
                        $( this ).closest( '.priced_individually' ).siblings( '.discount' ).hide( );
                        $( this ).closest( '.tab-content' ).find( '.advanced .price_visibility' ).hide( );
                    }
                },
                overrideTitleInputChanged: function overrideTitleInputChanged( ) {
                    if ( this.checked ) {
                        $( this ).closest( '.override_title' ).find( 'textarea' ).show( );
                    } else {
                        $( this ).closest( '.override_title' ).find( 'textarea' ).hide( );
                    }
                },
                overrideDescriptionInputChanged: function overrideDescriptionInputChanged( ) {
                    if ( this.checked ) {
                        $( this ).closest( '.override_description' ).find( 'textarea' ).show( );
                    } else {
                        $( this ).closest( '.override_description' ).find( 'textarea' ).hide( );
                    }
                }
            };
            return returnObj;
        }
    };
    /**
     * @TODO implement all the public API to product.js and access directly via global object
     * e.g. sortable function
     */
    var publicApi = {
        init: function ( ) {
            privateApi.addEventHandlers( );
            privateApi.setupEnvironment( );
        },
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
                    callback( );
                }
            };
            $( $elem ).sortable( options );
        }
    };
    return publicApi;
} )( jQuery );
afmBundleController.init( );