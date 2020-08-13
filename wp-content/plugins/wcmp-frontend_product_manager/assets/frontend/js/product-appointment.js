/* global wcmp_advance_product_params, alert, confirm */
jQuery( document ).ready( function( $ ) {
	'use strict';

	var wc_appointments_writepanel = {
		init: function() {
 			$( '#appointments_availability, #appointments_pricing' ).on( 'change', '.wc_appointment_availability_type select, .wc_appointment_availability_type input, .wc_appointment_pricing_type select', this.wc_appointments_table_grid );
 			$( '#appointments_availability, #appointments_pricing, #appointments_products' ).on( 'focus', 'select, input, button', this.wc_appointments_table_grid_focus );
 			$( 'body' ).on( 'row_added', this.wc_appointments_row_added );
 			$( '#_wc_appointment_user_can_cancel' ).on( 'change', this.wc_appointments_user_cancel );
 			if ( $( '#_wc_appointment_user_can_cancel' ).is( ':checked' ) ) {
				$( '.form-group.appointment-cancel-limit' ).show();
			} else {
				$( '.form-group.appointment-cancel-limit' ).hide();
			}
			$( '#_wc_appointment_qty' ).on( 'change', this.wc_appointments_qty );
			$( '#_wc_appointment_qty_min' ).on( 'change', this.wc_appointments_qty_min );
			$( '#_wc_appointment_qty_max' ).on( 'change', this.wc_appointments_qty_max );
 			// Rule change magic.
 			$( 'body' ).on( 'change', '.appointments-datetime-select-from :input, .appointments-datetime-select-to :input', this.wc_appointments_rule_range_change );
 			$( '#_wc_appointment_has_price_label' ).on( 'change', this.wc_appointments_price_label );
 			if ( $( '#_wc_appointment_has_price_label' ).is( ':checked' ) ) {
				$( '.form-group._wc_appointment_price_label_field' ).show();
			} else {
				$( '.form-group._wc_appointment_price_label_field' ).hide();
			}
			$( '#_wc_appointment_has_pricing' ).on( 'change', this.wc_appointments_pricing );
			if ( $( '#_wc_appointment_has_pricing' ).is( ':checked' ) ) {
				$( '#appointments_pricing' ).show();
			} else {
				$( '#appointments_pricing' ).hide();
			}
 			$( '#_wc_appointment_duration_unit' ).on( 'change', this.wc_appointment_duration_unit );
 			$( '#_wc_appointment_has_restricted_days' ).on( 'change', this.wc_appointment_restricted_days );
 			if ( $( '#_wc_appointment_has_restricted_days' ).is( ':checked' ) ) {
				$( '.appointment-day-restriction' ).show();
			} else {
				$( '.appointment-day-restriction' ).hide();
			}
			$( '.add_grid_row' ).on( 'click', this.wc_appointments_table_grid_add_row );
 		 	$( 'body' ).on( 'click', '.remove_grid_row', this.wc_appointments_table_grid_remove_row );

 			wc_appointments_writepanel.wc_appointments_trigger_change_events();
 			wc_appointments_writepanel.wc_appointments_sortable_rows();
 			wc_appointments_writepanel.wc_appointments_pickers();
 		},
		wc_appointments_rule_range_change: function() {
			var input_this            = $( this );
			var input_this_val        = input_this.val();
			var input_this_val_int    = parseFloat( input_this_val.replace( /-/g, '' ).replace( /:/g, '.' ), 10 );
			var input_this_class      = input_this.attr( 'class' );
			var range_from_or_to      = input_this.closest( '.range_from' ).length ? 'from' : 'to';
			var range_oposite         = 'from' === range_from_or_to ? 'to' : 'from';
			var range_type            = input_this.closest( 'tr' ).find( '.range_type select' ).val();
			var input_other_container = input_this.closest( 'tr' ).find( '.appointments-datetime-select-' + range_oposite + '' );
			console.log(input_other_container);
			var input_other           = input_other_container.find( '[class^="' + input_this_class + '"]' );
			var input_other_val       = input_other.val();
			console.log(input_other_val);
			var input_other_val_int   = parseFloat( input_other_val.replace( /-/g, '' ).replace( /:/g, '.' ), 10 );

			// Set up from and to variables.
			var range_from         = 'from' === range_from_or_to ? input_this : input_other;
			var range_from_val_int = 'from' === range_from_or_to ? input_this_val_int : input_other_val_int;
			var range_to           = 'to' === range_from_or_to ? input_this : input_other;
			var range_to_val_int   = 'to' === range_from_or_to ? input_this_val_int : input_other_val_int;

			console.log( input_this );
			console.log( input_other_container );

			if ( !input_other_val_int ) {
				input_other.val( input_this_val );
				wc_appointments_writepanel.wc_appointments_input_animate( input_this, input_other );
			} else if ( 'custom:daterange' === range_type || 'time:range' === range_type ) {
				var range_from_date         = input_this.closest( 'tr' ).find( '.from_date input' );
				var range_from_date_val     = range_from_date.val();
				var range_from_date_int     = parseFloat( range_from_date_val.replace( /-/g, '' ) );
				var range_from_time         = input_this.closest( 'tr' ).find( '.from_time input' );
				var range_from_time_val     = range_from_time.val();
				var range_from_time_int     = parseFloat( range_from_time_val.replace( /:/g, '.' ) );
				var range_to_date           = input_this.closest( 'tr' ).find( '.to_date input' );
				var range_to_date_val       = range_to_date.val();
				var range_to_date_int       = parseFloat( range_to_date_val.replace( /-/g, '' ) );
				var range_to_time           = input_this.closest( 'tr' ).find( '.to_time input' );
				var range_to_time_val       = range_to_time.val();
				var range_to_time_int       = parseFloat( range_to_time_val.replace( /:/g, '.' ) );
				var range_oposite_from_time = 'to' === range_from_or_to ? range_to_time : range_from_time;
				var range_oposite_to_time   = 'to' === range_from_or_to ? range_from_time : range_to_time;
				var range_oposite_from_date = 'to' === range_from_or_to ? range_to_date : range_from_date;
				var range_oposite_to_date   = 'to' === range_from_or_to ? range_from_date : range_to_date;

				if ( input_this.hasClass( 'time-picker' ) ) {
					if ( 'time:range' === range_type && range_from_time_int > range_to_time_int ) {
						wc_appointments_writepanel.wc_appointments_input_animate( range_oposite_from_time, range_oposite_to_time, true );
					} else if ( range_from_date_int >= range_to_date_int && range_from_time_int > range_to_time_int ) {
						wc_appointments_writepanel.wc_appointments_input_animate( range_oposite_from_time, range_oposite_to_time, true );
					} else if ( range_from_date_int > range_to_date_int ) {
						wc_appointments_writepanel.wc_appointments_input_animate( range_oposite_from_time, range_oposite_to_date, true );
					} else {
						wc_appointments_writepanel.wc_appointments_input_animate( range_oposite_from_time, range_oposite_to_time );
					}
				} else if ( input_this.hasClass( 'date-picker' ) ) {
					if ( 'time:range' === range_type && range_from_time_int > range_to_time_int ) {
						wc_appointments_writepanel.wc_appointments_input_animate( range_oposite_from_date, range_oposite_to_time, true );
					} else if ( range_from_date_int > range_to_date_int ) {
						wc_appointments_writepanel.wc_appointments_input_animate( range_oposite_from_date, range_oposite_to_date, true );
					} else if ( range_from_time_int > range_to_time_int && range_from_date_int === range_to_date_int ) {
						wc_appointments_writepanel.wc_appointments_input_animate( range_oposite_from_date, range_oposite_to_time, true );
					} else {
						wc_appointments_writepanel.wc_appointments_input_animate( range_oposite_from_date );
					}
				}
			} else if ( range_from_val_int > range_to_val_int ) {
				if ( 'from' === range_from_or_to ) {
					range_to.val( input_this_val );
					wc_appointments_writepanel.wc_appointments_input_animate( input_this, range_to );
				} else {
					wc_appointments_writepanel.wc_appointments_input_animate( input_this, range_from, true );
				}
			} else if ( range_from_val_int <= range_to_val_int ) {
				wc_appointments_writepanel.wc_appointments_input_animate( input_this );
			} else {
				wc_appointments_writepanel.wc_appointments_input_animate( input_this );
			}

			return false;
		},
		wc_appointments_input_animate: function( selected, other, error ) {
			// Reset.
			selected.parents( 'tr' ).find( 'select, input' ).stop().css( {
				outlineWidth: '0'
			} );

			if ( other ) {
				other.stop().css( {
					outlineWidth: '0'
				} );

				// Update other.
				if ( error ) {
					other.stop().css( {
						outlineOffset: '-1px',
						outlineStyle: 'solid',
						outlineColor: 'red',
						outlineWidth: '1px'
					} );
				} else {
					other.stop().css( {
						outlineOffset: '-1px',
						outlineStyle: 'solid',
						outlineColor: 'black',
						outlineWidth: '1px'
					} ).animate( {
						outlineWidth: '0'
					}, 500, 'linear' );
				}
			}
		},
		wc_appointments_pickers: function() {
			// Date picker.
			$( '.date-picker' ).datepicker( {
				dateFormat: 'dd-mm-yy',
				numberOfMonths: 1,
				showOtherMonths: true,
				changeMonth: true,
				showButtonPanel: true,
				showOn: 'button',
				firstDay: wcmp_advance_product_params.firstday,
				buttonText: '<span class="dashicons dashicons-calendar-alt"></span>'
			} );

			return false;
		},
		wc_appointments_table_grid: function() {
			var value = $( this ).val();
			var tr    = $( this ).closest( 'tr' );
			var row   = $( tr );

			row.find( '.from_date, .from_day_of_week, .from_month, .from_week, .from_time, .from' ).hide();
			row.find( '.to_date, .to_day_of_week, .to_month, .to_week, .to_time, .to, .on_date' ).hide();
			row.find( '.repeating-label' ).hide();
			row.find( '.appointments-datetime-select-to' ).removeClass( 'appointments-datetime-select-both' );
			row.find( '.appointments-datetime-select-from' ).removeClass( 'appointments-datetime-select-both' );
			row.find( '.rrule' ).hide();

			if ( 'custom' === value ) {
				row.find( '.from_date, .to_date' ).show();
			}
			if ( 'custom:daterange' === value ) {
				row.find( '.from_time, .to_time' ).show();
				row.find( '.from_date, .to_date' ).show();
				row.find( '.appointments-datetime-select-to' ).addClass( 'appointments-datetime-select-both' );
				row.find( '.appointments-datetime-select-from' ).addClass( 'appointments-datetime-select-both' );
			}
			if ( 'months' === value ) {
				row.find( '.from_month, .to_month' ).show();
			}
			if ( 'weeks' === value ) {
				row.find( '.from_week, .to_week' ).show();
			}
			if ( 'days' === value ) {
				row.find( '.from_day_of_week, .to_day_of_week' ).show();
			}
			if ( value.match( '^time' ) ) {
				row.find( '.from_time, .to_time' ).show();
				//* Show the date range as well if "time range for custom dates" is selected
				if ( 'time:range' === value ) {
					row.find( '.from_date, .to_date' ).show();
					row.find( '.repeating-label' ).show();
					row.find( '.appointments-datetime-select-to' ).addClass( 'appointments-datetime-select-both' );
					row.find( '.appointments-datetime-select-from' ).addClass( 'appointments-datetime-select-both' );
				}
			}
			if ( 'duration' === value || 'slots' === value || 'quant' === value ) {
				row.find( '.from, .to' ).show();
			}
			if ( 'rrule' === value ) {
				row.find( '.rrule' ).show();
			}

			return false;
		},
		wc_appointments_table_grid_focus: function( e ) {
			var $this_body  = $( 'body' );
			var $this_table = $( this ).closest( 'table, tbody' );
			var $this_row   = $( this ).closest( 'tr' );

			//console.log( e );

			if ( 'focus' === e.type || 'focusin' === e.type || ( 'click' === e.type && $( this ).is( ':focus' ) ) ) {
				$( 'tr', $this_table ).removeClass( 'current' ).removeClass( 'last_selected' );
				$this_row.addClass( 'current' ).addClass( 'last_selected' );
				$this_body.addClass( 'row_highlighted' );
			}

			return false;
		},
		wc_appointments_sortable_rows: function() {
			$( '#pricing_rows, #availability_rows' ).sortable( {
				items: 'tr',
				cursor: 'move',
				axis: 'y',
				handle: '.sort',
				scrollSensitivity: 40,
				forcePlaceholderSize: true,
				helper: 'clone',
				opacity: 0.65,
				placeholder: {
					element: function() {
						return $( '<tr class="wc-metabox-sortable-placeholder"><td colspan=99>&nbsp;</td></tr>' )[0];
					},
					update: function() {}
				},
				start: function( event, ui ) {
					ui.item.css( 'background-color', '#f6f6f6' );
				},
				stop: function( event, ui ) {
					ui.item.removeAttr( 'style' );
					ui.item.show();
				}
			} );

			return false;
		},
		wc_appointments_row_added: function() {
			$( '.wc_appointment_availability_type select, .wc_appointment_pricing_type select' ).change();
			$( '.date-picker' ).datepicker( {
				dateFormat: 'dd-mm-yy',
				numberOfMonths: 1,
				showOtherMonths: true,
				changeMonth: true,
				showButtonPanel: true,
				showOn: 'button',
				firstDay: wcmp_advance_product_params.firstday,
				buttonText: '<span class="dashicons dashicons-calendar-alt"></span>'
			} );

			return false;
		},
		wc_appointments_trigger_change_events: function() {
			$( '.wc_appointment_availability_type select, .wc_appointment_availability_type input, .wc_appointment_pricing_type select, #_wc_appointment_user_can_cancel, #_wc_appointment_has_price_label, #_wc_appointment_has_pricing, #_wc_appointment_duration_unit, #_stock, #_wc_appointment_qty, #_wc_appointment_qty_min, #_wc_appointment_qty_max, #_wc_appointment_has_restricted_days' ).change();

			return false;
		},
		wc_appointments_user_cancel: function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '.form-group.appointment-cancel-limit' ).show();
			} else {
				$( '.form-group.appointment-cancel-limit' ).hide();
			}

			return false;
		},
		wc_appointments_qty: function() {
			var qty_this	= parseInt( $( this ).val(), 10 );
			var qty_min		= parseInt( $( '#_wc_appointment_qty_min' ).val(), 10 );
			var qty_max		= parseInt( $( '#_wc_appointment_qty_max' ).val(), 10 );

			if ( 1 < qty_this ) {
				$( '.form-group._wc_appointment_customer_qty_wrap' ).show();
				$( '#_wc_appointment_qty_min' ).prop( 'max', qty_this );
				$( '#_wc_appointment_qty_max' ).prop( 'max', qty_this );
			} else {
				$( '.form-group._wc_appointment_customer_qty_wrap' ).hide();
			}

			// min.
			if ( qty_this < qty_min ) {
				$( '#_wc_appointment_qty_min' ).val( qty_this );
			}

			// max.
			if ( qty_this < qty_max ) {
				$( '#_wc_appointment_qty_max' ).val( qty_this );
			}

			return false;
		},
		wc_appointments_qty_min: function() {
			var qty_this	= parseInt( $( this ).val(), 10 );
			var qty_max		= parseInt( $( '#_wc_appointment_qty_max' ).val(), 10 );

			if ( qty_this > qty_max ) {
				$( '#_wc_appointment_qty_max' ).val( qty_this );
			}

			return false;
		},
		wc_appointments_qty_max: function() {
			var qty_this	= parseInt( $( this ).val(), 10 );
			var qty_min		= parseInt( $( '#_wc_appointment_qty_min' ).val(), 10 );

			if ( qty_this < qty_min ) {
				$( '#_wc_appointment_qty_min' ).val( qty_this );
			}

			return false;
		},

		wc_appointments_variable_enable: function() {
			var product_type = $( 'select#product-type' ).val();
			$( '#_virtual' ).prop('checked', false);
			if ( 'appointment' === product_type ) {
				$( '#_virtual' ).prop('checked', true);
			} 

			return false;
		},
// 		
		wc_appointments_price_label: function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '.form-group._wc_appointment_price_label_field' ).show();
			} else {
				$( '.form-group._wc_appointment_price_label_field' ).hide();
			}

			return false;
		},
		wc_appointments_pricing: function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '#appointments_pricing' ).show();
			} else {
				$( '#appointments_pricing' ).hide();
			}

			return false;
		},
		wc_appointment_duration_unit: function() {
			switch ( $( this ).val() ) {
				case 'month':
					$( '.form-field._wc_appointment_interval_duration_wrap' ).hide();
					$( '.form-field._wc_appointment_padding_duration_wrap' ).hide();
					$( '.form-field._wc_appointment_customer_timezones_field' ).hide();
					break;
				case 'day':
					$( '.form-field._wc_appointment_interval_duration_wrap' ).hide();
					$( '.form-field._wc_appointment_padding_duration_wrap' ).show();
					$( '.form-field._wc_appointment_customer_timezones_field' ).hide();
					$( '#_wc_appointment_padding_duration_unit option[value="minute"]' ).hide();
					$( '#_wc_appointment_padding_duration_unit option[value="hour"]' ).hide();
					$( '#_wc_appointment_padding_duration_unit option[value="day"]' ).show();
					$( '#_wc_appointment_padding_duration_unit' ).val( 'day' );
					break;
				default: // all other.
					$( '.form-field._wc_appointment_interval_duration_wrap' ).show();
					$( '.form-field._wc_appointment_padding_duration_wrap' ).show();
					$( '.form-field._wc_appointment_customer_timezones_field' ).show();
					$( '#_wc_appointment_padding_duration_unit option[value="minute"]' ).show();
					$( '#_wc_appointment_padding_duration_unit option[value="hour"]' ).show();
					$( '#_wc_appointment_padding_duration_unit option[value="day"]' ).hide();
					break;
			}

			return false;
		},
		wc_appointment_restricted_days: function() {
			if ( $( this ).is( ':checked' ) ) {
				$( '.appointment-day-restriction' ).show();
			} else {
				$( '.appointment-day-restriction' ).hide();
			}

			return false;
		},
		wc_appointments_table_grid_add_row: function( e ) {
			var newRowIndex = $( e.target ).closest( 'table' ).find( '#pricing_rows tr' ).length;
			var newRow = $( this ).data( 'row' );
			newRow = newRow.replace( /appointments_cost_js_index_replace/ig, newRowIndex.toString() );
			// Clear out IDs.
			newRow = newRow.replace( /wc_appointment_availability_id.+/, 'wc_appointment_availability_id[]" value="" />' );
			newRow = newRow.replace( /wc_appointment_availability_kind_id.+/, 'wc_appointment_availability_kind_id[]" value="" />' );
			newRow = newRow.replace( /wc_appointment_availability_event_id.+/, 'wc_appointment_availability_event_id[]" value="" />' );
			newRow = newRow.replace( /data-id=.+/, 'data-id="">' );

			// Clear out title.
			newRow = newRow.replace( /wc_appointment_availability_title.+/, 'wc_appointment_availability_title[]" value="" />' );

			// Clear out priority.
			newRow = newRow.replace( /wc_appointment_availability_priority.+/, 'wc_appointment_availability_priority[]" value="10" placeholder="10" />' );

			$( e.target ).closest( 'table' ).find( 'tbody' ).append( newRow );
			$( 'body' ).trigger( 'row_added' );

			return false;
		},
		wc_appointments_table_grid_remove_row: function( e ) {
			var row = $( e.target ).closest( 'tr' );
			var id  = row.data( 'id' );

			// Get current deleted list.
			var deleted = $( '.wc-appointment-availability-deleted' ).val();

			// Separator.
			var separator = ( deleted ? ', ' : '' );

			// Add to deleted list.
			var deleted_ids = deleted + separator + id;

			// Add deleted id to input field.
			$( '.wc-appointment-availability-deleted' ).val( deleted_ids );

			row.remove();

			return false;
		},
	};

	wc_appointments_writepanel.init();
} );
