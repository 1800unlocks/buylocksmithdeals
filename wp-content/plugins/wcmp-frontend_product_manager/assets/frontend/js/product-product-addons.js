'use strict';
var afmAddonController = ( function ( $ ) {
    var privateApi = {
        //bundledItems: null,
        addEventHandlers: function addEventHandlers( ) {
            $( '#product_addons_data' )
                .on( 'change', '.product-addons-wrapper select.product_addon_type', this.changeAddonType )
                .on( 'change', '.addon_name input', this.changeAddonName )
                .on( 'click', 'button.add_new_addon', this.addAddon )
                .on( 'click', 'a.remove-addon', this.removeAddon )
                .on( 'click', '.product_addon_type', this.stopToggle )
                .on( 'click', '.expand_all', this.expandAllAddons )
                .on( 'click', '.close_all', this.closeAllAddons )
                .on( 'click', 'a.add_addon_option', this.addAddonOption )
                .on( 'click', 'a.remove_addon_option', this.removeAddonOption )
                .on( 'click', 'button.export_addons', this.exportAddons )
                .on( 'click', 'button.import_addons', this.importAddons )
                ;
            
            this.setupEnvironment();
        },
        setupEnvironment: function setupEnvironment( ) {
            var $addonsTab = $( '#product_addons_data' ),
                $addonContainer = $( '.product-addons-wrapper', $addonsTab );
            publicApi.sortable( $addonContainer, '.wcmp-metabox-wrapper', '.wcmp-metabox-title span.sortable-icon', this.updateAddonIndex );
            $( '#product_addons_data textarea.export, #product_addons_data textarea.import' ).hide();
            $( 'select.product_addon_type', '#product_addons_data' ).change();
        },
        changeAddonType: function changeAddonType( ) {
            var type = $( this ).val( );
            var $productAddon = $( this ).closest( '.wcmp-metabox-wrapper' );
            if ( type === 'custom' || type === 'custom_price' || type === 'custom_textarea' || type === 'input_multiplier' || type === 'custom_letters_only' || type === 'custom_digits_only' || type === 'custom_letters_or_digits' ) {
                $productAddon.find( 'td.minmax_column, th.minmax_column' ).show( );
            } else {
                $productAddon.find( 'td.minmax_column, th.minmax_column' ).hide( );
            }

            if ( type == 'custom_price' ) {
                $productAddon.find( 'td.price_column, th.price_column' ).hide( );
            } else {
                $productAddon.find( 'td.price_column, th.price_column' ).show( );
            }
            var columnTitle = '';
            // Switch up the column title, based on the field type selected
            switch ( type ) {
                case 'custom_price':
                    columnTitle = wcmp_advance_product_params.i18n_minmax_price;
                    break;
                case 'input_multiplier':
                    columnTitle = wcmp_advance_product_params.i18n_minmax_multiplier;
                    break;
                case 'custom_textarea':
                case 'custom_letters_only':
                case 'custom_digits_only':
                case 'custom_letters_or_digits':
                case 'custom_email':
                case 'custom':
                    columnTitle = wcmp_advance_product_params.i18n_minmax_characters;
                    break;
                default:
                    columnTitle = wcmp_advance_product_params.i18n_minmax;
                    break;
            }
            $productAddon.find( 'th.minmax_column .column-title' ).text( columnTitle );
//            // Count the number of options.  If one (or less), disable the remove option buttons
//            var removeAddOnOptionButtons = $productAddon.find( 'a.remove_addon_option' );
//            if ( 2 > removeAddOnOptionButtons.length ) {
//                removeAddOnOptionButtons.attr( 'disabled', 'disabled' );
//            } else {
//                removeAddOnOptionButtons.removeAttr( 'disabled' );
//            }

        },
        changeAddonName: function changeAddonName( ) {
            $( this ).closest( '.woocommerce_product_addon' ).find( 'span.group_name' ).text( $( this ).val( ) || '' );
        },
        updateAddonIndex: function updateAddonIndex() {
            $( '.product-addons-wrapper .wcmp-metabox-wrapper' ).each( function ( index, el ) {
                $( '.product_addon_position', el ).val( parseInt( $( el ).index( '.product-addons-wrapper .wcmp-metabox-wrapper' ), 10 ) );
            } );
        },
        addAddon: function addAddon( ) {
            var $items = $( this ).closest( '#product_addons_data' ).find( '.product-addons-wrapper .wcmp-metabox-wrapper' ).get();
            var size = 0;
            if ( $items.length > 0 ) {
                //get the heighest attr id
                size = $items.reduce( function ( a, b ) {
                    var mIndex = parseInt( $( b ).find( '.wcmp-metabox-content' ).attr( 'id' ).replace( 'product_addon_', '' ), 10 );
                    return ( isNaN( mIndex ) || a > mIndex ) ? a : mIndex;
                }, 0 );
                //next item index
                ++size;
            }

            var $wrapper = $( this ).closest( '#product_addons_data' );
            var $addons = $wrapper.find( '.product-addons-wrapper' );
            var data = {
                action: 'wcmp_afm_add_product_addon',
                product_id: wcmp_advance_product_params.product_id,
                i: size,
                security: wcmp_advance_product_params.add_addon_nonce
            };

            $wrapper.block( {
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            } );

            $.post( wcmp_advance_product_params.ajax_url, data, function ( response ) {
                $addons.append( response );
                var $lastAddon = $addons.find( '.wcmp-metabox-wrapper' ).last( ),
                    $lastAddonOptions = $lastAddon.find( '.table-addon-options tbody tr' );
                if ( $lastAddonOptions.length === 1 ) {
                    $lastAddonOptions.find( '.remove_addon_option' ).addClass( 'disabled' );
                }
                $( 'select.product_addon_type', $lastAddon ).change();
                publicApi.sortable( $addons, '.wcmp-metabox-wrapper', '.wcmp-metabox-title span.sortable-icon', privateApi.updateAddonIndex );
                privateApi.updateAddonIndex();
                $lastAddon.find('.wcmp-metabox-title').click();
                $wrapper.unblock();
            } );

            return false;
        },
        removeAddon: function removeAddon() {
            if ( window.confirm( wcmp_advance_product_params.i18n_remove_addon ) ) {
                var $parent = $( this ).closest( '.wcmp-metabox-wrapper' );

                $parent.remove();
                privateApi.updateAddonIndex();
            }
            return false;
        },
        stopToggle: function stopToggle( e ) {
            e.stopPropagation();
        },
        expandAllAddons: function expandAllAddons() {
            $( '.product-addons-wrapper .wcmp-metabox-wrapper > .wcmp-metabox-content', '#product_addons_data' ).collapse( 'show' );
            return false;
        },
        closeAllAddons: function closeAllAddons() {
            $( '.product-addons-wrapper .wcmp-metabox-wrapper > .wcmp-metabox-content', '#product_addons_data' ).collapse( 'hide' );
            return false;
        },
        addAddonOption: function addAddonOption( ) {
            var $options = $( this ).closest( '.table-addon-options' ).find( 'tbody tr' );
            $options.find( '.remove_addon_option' ).removeClass( 'disabled' );

            $( this ).closest( '.table-addon-options' ).find( 'tbody' ).append( $( this ).data( 'row' ) );
            $( this ).closest( '.wcmp-metabox-wrapper' ).find( 'select.product_addon_type' ).change();
            return false;
        },
        removeAddonOption: function removeAddonOption() {
            var $options = $( this ).closest( 'table' ).find( 'tbody tr' );
            if ( $options.length > 1 && window.confirm( wcmp_advance_product_params.i18n_remove_addon_option ) ) {
                if ( $options.length === 2 ) {
                    $options.find( '.remove_addon_option' ).addClass( 'disabled' );
                }
                $( this ).closest( 'tr' ).remove();
            } else {
                window.alert( wcmp_advance_product_params.i18n_restrict_addon_remove );
            }
            return false;
        },
        exportAddons: function exportAddons() {
            $( '#product_addons_data textarea.import' ).hide();
            $( '#product_addons_data textarea.export' ).slideToggle( '500', function () {
                $( this ).select();
            } );

            return false;
        },
        importAddons: function importAddons() {
            $( '#product_addons_data textarea.export' ).hide();
            $( '#product_addons_data textarea.import' ).slideToggle( '500' );

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
            //privateApi.setupEnvironment( );
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
afmAddonController.init( );