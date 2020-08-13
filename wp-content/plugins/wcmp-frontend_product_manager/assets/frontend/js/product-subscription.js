'use strict';
( function ( $ ) {
  var afmSubscriptionsController = function ( ) {
    var utility = null;
    var persons = null;
    return {
      init: function () {
        utility = this.defineUtilities();

        $( '#woocommerce-product-data #_sale_price' ).after( '<span id="sale-price-period" style="display: none;"></span>' );

        //Product type select change
        $( '#woocommerce-product-data' )
          .on( 'afm-product-type-changed', this.resetProductOptions.bind( this ) );

        $( '#product_attributes_data' ).on( 'attribute_added', this.triggerVariableSubscriptionAttribute );
        $( '#variable_product_options' ).on( 'woocommerce_variations_added', this.triggerVariableSubscriptionVariation.bind( this ) );
        $( '#woocommerce-product-data' ).on( 'woocommerce_variations_loaded', this.triggerVariableSubscriptionVariation.bind( this ) );

        this.setupEnvironment();

        // Update subscription ranges when subscription period or interval is changed
        $( '#woocommerce-product-data' ).on( 'change', '[name^="_subscription_period"], [name^="_subscription_period_interval"], [name^="variable_subscription_period"], [name^="variable_subscription_period_interval"]', this.changeProductOptions.bind( this ) );
        $( '#woocommerce-product-data' ).on( 'change', '[name^="_subscription_trial_length"], [name^="_subscription_payment_sync_date"], [name^="_subscription_payment_sync_date_day"]', this.disableEnableOneTimeShipping.bind() );
        $( '#woocommerce-product-data' ).on( 'propertychange keyup input paste change', '[name^="_subscription_trial_length"] , [name^="variable_subscription_trial_length"]', this.setTrialPeriods.bind() );

        // WC 2.4+ variation bulk edit handling
        $( 'select.variation_actions' ).on( 'variable_subscription_sign_up_fee_ajax_data variable_subscription_period_interval_ajax_data variable_subscription_period_ajax_data variable_subscription_trial_period_ajax_data variable_subscription_trial_length_ajax_data variable_subscription_length_ajax_data', this.enableVariationBulkEdit.bind( this ) );
      },
      enableVariationBulkEdit: function ( event, data ) {
        var bulk_action = event.type.replace( /_ajax_data/g, '' );
        var value = this.getVariationBulkEditValue( bulk_action );

        if ( 'variable_subscription_trial_length' == bulk_action ) {
          // After variations have their trial length bulk updated in the backend, flag the One Time Shipping field as needing to be updated
          $( '#_subscription_one_time_shipping' ).addClass( 'wcs_ots_needs_update' );
        }

        if ( value != null ) {
          data.value = value;
        }
        return data;
      },
      defineUtilities: function () {
        return {
          clean: function ( selector ) {
            //sanitize invalid selectors
            return $( selector ).length;
          }
        };
      },
      triggerVariableSubscriptionAttribute: function ( ) { 
        var type = $( 'select#product-type' ).val();
        if ( type === 'variable' || type === 'variable-subscription' ) {
            $( '#woocommerce-product-data' ).find( '.show_if_variable-subscription' ).show();
        } else {
            $( '#woocommerce-product-data' ).find( '.show_if_variable-subscription' ).hide();
        }
      },
      triggerVariableSubscriptionVariation: function ( ) {
        var type = $( 'select#product-type' ).val();
        if ( type === 'variable' ) {
          $( '.hide_if_variable' ).hide();
        } else if ( type === 'variable-subscription' ) {
          $( '#woocommerce-product-data' ).find( '.variable_pricing' ).children( ':first' ).addClass( 'hide_if_variable-subscription' );
          $( '.hide_if_variable-subscription' ).hide();
          $( 'input.variable_manage_stock' ).change();
        }
      },
      setupEnvironment: function ( ) {
        //$('#woocommerce-product-data .show_if_simple').addClass('show_if_subscription');
        $( '#woocommerce-product-data' ).find( '.show_if_variable' ).addClass( 'show_if_variable-subscription' );
        $( '#woocommerce-product-data' ).find( '.hide_if_variable' ).addClass( 'hide_if_variable-subscription' );
        $( '#_regular_price', '#general_product_data' ).closest( '.form-group' ).addClass( 'hide_if_subscription' );

        //on first time load reset tabs display
        this.resetProductOptions();
      },
      resetProductOptions: function () {
        var type = $( 'select#product-type' ).val();
        var $variation_actions = $( 'select.variation_actions' );
        var $delete_all = $variation_actions.find( 'option[value="delete_all"], option[value="delete_all_no_subscriptions"]' );

        if ( type === 'subscription' ) {
          this.setSalePeriod();
          $( 'input#_manage_stock' ).change();
        } else if ( type === 'variable-subscription' ) {
          // In order for WooCommerce not to show the stock_status_field on variable subscriptions, make sure it has the hide if variable subscription class.
          $( '.stock_status_field' ).addClass( 'hide_if_variable-subscription' );
          $( '.show_if_variable-subscription' ).show();
          $( '.hide_if_variable-subscription' ).hide();
          this.setSalePeriod();
          $( 'input#_manage_stock' ).change();
        }

        // Alter Variation delete label
        if ( 'variable-subscription' === type && 'delete_all' === $delete_all.val() ) {
          $delete_all.data( 'wcs_original_wc_label', $delete_all.text() )
            .attr( 'value', 'delete_all_no_subscriptions' )
            .text( wcmp_advance_product_params.bulkDeleteOptionLabel );
        } else if ( 'variable-subscription' !== type && 'delete_all_no_subscriptions' === $delete_all.val() ) {
          $delete_all.text( $delete_all.data( 'wcs_original_wc_label' ) )
            .attr( 'value', 'delete_all' );
        }

        this.updateTabsDisplay();
        this.setSubscriptionLengths();
        this.disableEnableOneTimeShipping();
        this.setTrialPeriods();
      },
      updateTabsDisplay: function () {
        var type = $( 'select#product-type' ).val();
        if ( type == 'subscription' ) {
          $( '#sale-price-period' ).show();

          if ( 'day' == $( '#_subscription_period' ).val() ) {
            $( '.subscription_sync' ).hide();
          }
        } else {
          $( '#sale-price-period' ).hide();
          $( '#_regular_price', '#general_product_data' ).closest( '.form-group' ).show();
        }
      },
      setSalePeriod: function () {
        $( '#sale-price-period' ).fadeOut( 80, function () {
          $( '#sale-price-period' ).text( $( '#_subscription_period_interval option:selected' ).text() + ' ' + $( '#_subscription_period option:selected' ).text() );
          $( '#sale-price-period' ).fadeIn( 180 );
        } );
      },
      setSubscriptionLengths: function () {
        $( '[name^="_subscription_length"], [name^="variable_subscription_length"]' ).each( function () {
          var $lengthElement = $( this ),
            selectedLength = $lengthElement.val(),
            hasSelectedLength = false,
            matches = $lengthElement.attr( 'name' ).match( /\[(.*?)\]/ ),
            periodSelector,
            billingInterval,
            interval;

          if ( matches ) { // Variation
            periodSelector = '[name="variable_subscription_period[' + matches[1] + ']"]';
            billingInterval = parseInt( $( '[name="variable_subscription_period_interval[' + matches[1] + ']"]' ).val() );
          } else {
            periodSelector = '#_subscription_period';
            billingInterval = parseInt( $( '#_subscription_period_interval' ).val() );
          }

          $lengthElement.empty();

          $.each( wcmp_advance_product_params.subscriptionLengths[ $( periodSelector ).val() ], function ( length, description ) {
            if ( parseInt( length ) == 0 || 0 == ( parseInt( length ) % billingInterval ) ) {
              $lengthElement.append( $( '<option></option>' ).attr( 'value', length ).text( description ) );
            }
          } );

          $lengthElement.children( 'option' ).each( function () {
            if ( this.value == selectedLength ) {
              hasSelectedLength = true;
              return false;
            }
          } );

          if ( hasSelectedLength ) {
            $lengthElement.val( selectedLength );
          } else {
            $lengthElement.val( 0 );
          }

        } );
      },
      showHideSyncOptions: function () {
        if ( $( '#_subscription_payment_sync_date' ).length > 0 || $( '.wc_input_subscription_payment_sync' ).length > 0 ) {
          $( '.subscription_sync, .variable_subscription_sync' ).each( function () { // loop through all sync field groups
            var $syncWeekMonthContainer = $( this ).find( '.subscription_sync_week_month' ),
              $syncWeekMonthSelect = $syncWeekMonthContainer.find( 'select' ),
              $syncAnnualContainer = $( this ).find( '.subscription_sync_annual' ),
              $varSubField = $( this ).find( '[name^="variable_subscription_payment_sync_date"]' ),
              $slideSwitch = false, // stop the general sync field group sliding down if editing a variable subscription
              $subscriptionPeriodElement,
              billingPeriod;

            if ( $varSubField.length > 0 ) { // Variation
              var matches = $varSubField.attr( 'name' ).match( /\[(.*?)\]/ );
              $subscriptionPeriodElement = $( '[name="variable_subscription_period[' + matches[1] + ']"]' );
              if ( $( 'select#product-type' ).val() == 'variable-subscription' ) {
                $slideSwitch = true;
              }
            } else {
              $subscriptionPeriodElement = $( '#_subscription_period' );
              if ( $( 'select#product-type' ).val() == 'subscription' ) {
                $slideSwitch = true;
              }
            }

            billingPeriod = $subscriptionPeriodElement.val();

            if ( 'day' == billingPeriod ) {
              $( this ).slideUp( 400 );
            } else {
              if ( $slideSwitch ) {
                $( this ).slideDown( 400 );
                if ( 'year' == billingPeriod ) {
                  // Make sure the year sync fields are visible
                  $syncAnnualContainer.slideDown( 400 );
                  // And the week/month field is hidden
                  $syncWeekMonthContainer.slideUp( 400 );
                } else {
                  // Make sure the year sync fields are hidden
                  $syncAnnualContainer.slideUp( 400 );
                  // And the week/month field is visible
                  $syncWeekMonthContainer.slideDown( 400 );
                }
              }
            }
          } );
        }
      },
      setSyncOptions: function ( el ) {

        var periodField = $( el );

        if ( typeof periodField != 'undefined' ) {

          if ( $( 'select#product-type' ).val() == 'variable-subscription' ) {
            var $container = periodField.closest( '.woocommerce_variable_attributes' ).find( '.variable_subscription_sync' );
          } else {
            var $container = periodField.closest( '#general_product_data' ).find( '.subscription_sync' )
          }

          var $syncWeekMonthContainer = $container.find( '.subscription_sync_week_month' ),
            $syncWeekMonthSelect = $syncWeekMonthContainer.find( 'select' ),
            $syncAnnualContainer = $container.find( '.subscription_sync_annual' ),
            $varSubField = $container.find( '[name^="variable_subscription_payment_sync_date"]' ),
            $subscriptionPeriodElement,
            billingPeriod;

          if ( $varSubField.length > 0 ) { // Variation
            var matches = $varSubField.attr( 'name' ).match( /\[(.*?)\]/ );
            $subscriptionPeriodElement = $( '[name="variable_subscription_period[' + matches[1] + ']"]' );
          } else {
            $subscriptionPeriodElement = $( '#_subscription_period' );
          }

          billingPeriod = $subscriptionPeriodElement.val();

          if ( 'day' == billingPeriod ) {
            $syncWeekMonthSelect.val( 0 );
            $syncAnnualContainer.find( 'input[type="number"]' ).val( 0 );
          } else {
            if ( 'year' == billingPeriod ) {
              // Make sure the year sync fields are reset
              $syncAnnualContainer.find( 'input[type="number"]' ).val( 0 );
              // And the week/month field has no option selected
              $syncWeekMonthSelect.val( 0 );
            } else {
              // Make sure the year sync value is 0
              $syncAnnualContainer.find( 'input[type="number"]' ).val( 0 );
              // And the week/month field has the appropriate options
              $syncWeekMonthSelect.empty();

              $.each( wcmp_advance_product_params.syncOptions[billingPeriod], function ( key, description ) {
                $syncWeekMonthSelect.append( $( '<option></option>' ).attr( 'value', key ).text( description ) );
              } );
            }
          }
        }
      },
      disableEnableOneTimeShipping: function () {
        var is_synced_or_has_trial = false;

        if ( 'variable-subscription' == $( 'select#product-type' ).val() ) {
          var variations = $( '.woocommerce_variations .woocommerce_variation' ),
            variations_checked = { },
            number_of_pages = $( '.woocommerce_variations' ).attr( 'data-total_pages' );

          $( variations ).each( function () {
            var period_field = $( this ).find( '.wc_input_subscription_period' ),
              variation_index = $( period_field ).attr( 'name' ).match( /\[(.*?)\]/ ),
              variation_id = $( '[name="variable_post_id[' + variation_index[1] + ']"]' ).val(),
              period = period_field.val(),
              trial = $( this ).find( '.wc_input_subscription_trial_length' ).val(),
              sync_date = 0;

            if ( 0 != trial ) {
              is_synced_or_has_trial = true;

              // break
              return false;
            }

            if ( $( this ).find( '.variable_subscription_sync' ).length ) {
              if ( 'month' == period || 'week' == period ) {
                sync_date = $( '[name="variable_subscription_payment_sync_date[' + variation_index[1] + ']"]' ).val();
              } else if ( 'year' == period ) {
                sync_date = $( '[name="variable_subscription_payment_sync_date_day[' + variation_index[1] + ']"]' ).val();
              }

              if ( 0 != sync_date ) {
                is_synced_or_has_trial = true;

                // break
                return false;
              }
            }

            variations_checked[ variation_index[1] ] = variation_id;
          } );

          // if we haven't found a variation synced or with a trial at this point check the backend for other product variations
          if ( ( number_of_pages > 1 || 0 == variations.size() ) && false == is_synced_or_has_trial ) {

            var data = {
              action: 'wcs_product_has_trial_or_is_synced',
              product_id: wcmp_advance_product_params.product_id,
              variations_checked: variations_checked,
              nonce: wcmp_advance_product_params.oneTimeShippingCheckNonce,
            };

            $.ajax( {
              url: wcmp_advance_product_params.ajax_url,
              data: data,
              type: 'POST',
              success: function ( response ) {
                $( '#_subscription_one_time_shipping' ).prop( 'disabled', response.is_synced_or_has_trial );
                // trigger an event now we have determined the one time shipping availability, in case we need to update the backend
                $( '#_subscription_one_time_shipping' ).trigger( 'subscription_one_time_shipping_updated', [ response.is_synced_or_has_trial ] );
              }
            } );
          } else {
            // trigger an event now we have determined the one time shipping availability, in case we need to update the backend
            $( '#_subscription_one_time_shipping' ).trigger( 'subscription_one_time_shipping_updated', [ is_synced_or_has_trial ] );
          }
        } else {
          var trial = $( '#general_product_data #_subscription_trial_length' ).val();

          if ( 0 != trial ) {
            is_synced_or_has_trial = true;
          }

          if ( $( '.subscription_sync' ).length && false == is_synced_or_has_trial ) {
            var period = $( '#_subscription_period' ).val(),
              sync_date = 0;

            if ( 'month' == period || 'week' == period ) {
              sync_date = $( '#_subscription_payment_sync_date' ).val();
            } else if ( 'year' == period ) {
              sync_date = $( '#_subscription_payment_sync_date_day' ).val();
            }

            if ( 0 != sync_date ) {
              is_synced_or_has_trial = true;
            }
          }
        }

        $( '#_subscription_one_time_shipping' ).prop( 'disabled', is_synced_or_has_trial );
      },
      setTrialPeriods: function () {
        $( '[name^="_subscription_trial_length"], [name^="variable_subscription_trial_length"]' ).each( function () {
          var $trialLengthElement = $( this ),
            trialLength = $trialLengthElement.val(),
            matches = $trialLengthElement.attr( 'name' ).match( /\[(.*?)\]/ ),
            $trialPeriodElement,
            selectedTrialPeriod,
            periodStrings;

          if ( matches ) { // Variation
            $trialPeriodElement = $( '[name="variable_subscription_trial_period[' + matches[1] + ']"]' );
          } else {
            $trialPeriodElement = $( '#_subscription_trial_period' );
          }

          selectedTrialPeriod = $trialPeriodElement.val();

          $trialPeriodElement.empty();

          if ( parseInt( trialLength ) == 1 ) {
            periodStrings = wcmp_advance_product_params.trialPeriodSingular;
          } else {
            periodStrings = wcmp_advance_product_params.trialPeriodPlurals;
          }

          $.each( periodStrings, function ( key, description ) {
            $trialPeriodElement.append( $( '<option></option>' ).attr( 'value', key ).text( description ) );
          } );

          $trialPeriodElement.val( selectedTrialPeriod );
        } );
      },
      getVariationBulkEditValue: function ( variation_action ) {
        var value;

        switch ( variation_action ) {
          case 'variable_subscription_period':
          case 'variable_subscription_trial_period':
            value = prompt( wcmp_advance_product_params.bulkEditPeriodMessage );
            break;
          case 'variable_subscription_period_interval':
            value = prompt( wcmp_advance_product_params.bulkEditIntervalhMessage );
            break;
          case 'variable_subscription_trial_length':
          case 'variable_subscription_length':
            value = prompt( wcmp_advance_product_params.bulkEditLengthMessage );
            break;
          case 'variable_subscription_sign_up_fee':
            value = prompt( wcmp_advance_product_params.i18n_enter_a_value );
            value = accounting.unformat( value, wcmp_advance_product_params.mon_decimal_point );
            break;
        }

        return value;
      },
      changeProductOptions: function ( e ) {
        this.setSubscriptionLengths();
        this.showHideSyncOptions();
        this.setSyncOptions( e.target );
        this.setSalePeriod();
        this.disableEnableOneTimeShipping();
      }
    };
  }();
  afmSubscriptionsController.init();

  /**
   * Prevents removal of variations in use by a subscription.
   */
  var wcs_prevent_variation_removal = {
    init: function () {
      if ( 0 === $( '#woocommerce-product-data' ).length ) {
        return;
      }

      $( 'body' ).on( 'woocommerce-product-type-change', this.product_type_change );
      $( '#variable_product_options' ).on( 'reload', this.product_type_change );
      $( 'select.variation_actions' ).on( 'delete_all_no_subscriptions_ajax_data', this.bulk_action_data );
      this.product_type_change();
    },

    product_type_change: function () {
      var product_type = $( '#product-type' ).val();
      var $variation_actions = $( 'select.variation_actions' );
      var $delete_all = $variation_actions.find( 'option[value="delete_all"], option[value="delete_all_no_subscriptions"]' );

      if ( 'variable-subscription' === product_type && 'delete_all' === $delete_all.val() ) {
        $delete_all.data( 'wcs_original_wc_label', $delete_all.text() )
          .attr( 'value', 'delete_all_no_subscriptions' )
          .text( wcmp_advance_product_params.bulkDeleteOptionLabel );
      } else if ( 'variable-subscription' !== product_type && 'delete_all_no_subscriptions' === $delete_all.val() ) {
        $delete_all.text( $delete_all.data( 'wcs_original_wc_label' ) )
          .attr( 'value', 'delete_all' );
      }
    },

    bulk_action_data: function ( event, data ) {
      if ( window.confirm( wcmp_advance_product_params.i18n_delete_all_variations ) ) {
        if ( window.confirm( wcmp_advance_product_params.i18n_last_warning ) ) {
          data.allowed = true;

          // do_variation_action() in woocommerce/assets/js/admin/meta-boxes-product-variation.js doesn't
          // allow us to do anything after the AJAX request, so we need to listen to all AJAX requests for a
          // little while to update the quantity and refresh the variation list.
          $( document ).bind( 'ajaxComplete', wcs_prevent_variation_removal.update_qty_after_removal );
        }
      }

      return data;
    },

    update_qty_after_removal: function ( event, jqXHR, ajaxOptions ) {
      var $variations = $( '#variable_product_options .woocommerce_variations' );
      var removed;

      // Not our bulk edit request. Ignore.
      if ( -1 === ajaxOptions.data.indexOf( 'action=woocommerce_bulk_edit_variations' ) || -1 === ajaxOptions.data.indexOf( 'bulk_action=delete_all_no_subscriptions' ) ) {
        return;
      }

      // Unbind so this doesn't get called every time an AJAX request is performed.
      $( document ).unbind( 'ajaxComplete', wcs_prevent_variation_removal.update_qty_after_removal );

      // Update variation quantity.
      removed = ( 'OK' === jqXHR.statusText ) ? parseInt( jqXHR.responseText, 10 ) : 0;
      $variations.attr( 'data-total', Math.max( 0, parseInt( $variations.attr( 'data-total' ), 10 ) - removed ) );
      $( '#variable_product_options' ).trigger( 'reload' );
    },
  };
  wcs_prevent_variation_removal.init();
} )( jQuery );
