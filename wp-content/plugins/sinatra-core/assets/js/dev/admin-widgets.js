//--------------------------------------------------------------------//
// Sinatra Core Admin Widgets script.
//--------------------------------------------------------------------//
;(function( $ ) {
	"use strict";

	/**
	 * Common element caching.
	 */
	var $body     = $( 'body' );
	var $document = $( document );
	var $wrapper  = $( '#page' );
	var $html     = $( 'html' );
	var $this;

	/**
	 * Holds most important methods that bootstrap the whole theme.
	 * 
	 * @type {Object}
	 */
	var SinatraCoreAdminWidgets = {

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			// Document ready
			$(document).ready( SinatraCoreAdminWidgets.ready );

			// Window load
			$(window).on( 'load', SinatraCoreAdminWidgets.load );

			// Bind UI actions
			SinatraCoreAdminWidgets.bindUIActions();

			// Trigger event when Sinatra fully loaded
			$(document).trigger( 'sinatraCoreWidgetsReady' );
		},

		//--------------------------------------------------------------------//
		// Events
		//--------------------------------------------------------------------//

		/**
		 * Document ready.
		 *
		 * @since 1.0.0
		 */
		ready: function() {

			SinatraCoreAdminWidgets.initRepeatableSortable();
		},

		/**
		 * Window load.
		 *
		 * @since 1.0.0
		 */
		load: function() {
		},

		/**
		 * Bind UI actions.
		 *
		 * @since 1.0.0
		*/
		bindUIActions: function() {

			var $this,
				index = 0,
				template,
				$widget;

			$(document).on( 'click', '.si-repeatable-widget .add-new-item', function(e){
				e.preventDefault();

				$this    = $(this);
				index    = parseInt( $this.attr('data-index') );
				template = wp.template( 'sinatra-core-repeatable-item' );

				var data = {
					index: index,
					name: $this.attr('data-widget-name'),
					id: $this.attr('data-widget-id'),
				};

				index++;

				$this.attr( 'data-index', index );
				$( template( data ) ).insertBefore( $this.closest('.si-repeatable-footer') );
				$this.closest('.widget-inside').trigger('change');

				update_widget_repeatable_class( $this );
			});

			$(document).on( 'click', '.si-repeatable-widget .remove-repeatable-item', function(e){
				e.preventDefault();

				$this   = $(this);
				$widget = $this.closest('.si-repeatable-container');

				$this.closest('.widget-inside').trigger('change');
				$this.closest('.si-repeatable-item').remove();
				
				update_widget_repeatable_class( $widget );
			});

			$(document).on( 'click', '.si-repeatable-widget .si-repeatable-item-title', function(){
				$(this).closest('.si-repeatable-item').toggleClass('open');
			});

			var update_widget_repeatable_class = function( $target ) {

				var $widget = $target.closest('.si-repeatable-container');

				if ( $widget.find('.si-repeatable-item').length ) {
					$widget.removeClass('empty');
				} else {
					$widget.addClass('empty');
				}
			};

			// Updated widget event.
			$(document).on( 'widget-updated widget-added', function( e, widget ){
				if ( widget.find('.si-repeatable-container').length ) {
					SinatraCoreAdminWidgets.initRepeatableSortable();
				}
			});
		},

		//--------------------------------------------------------------------//
		// Functions
		//--------------------------------------------------------------------//

		initRepeatableSortable: function() {

			$('.si-repeatable-container').sortable({
				handle: '.si-repeatable-item-title',
				accent: '.si-repeatable-item',
				containment: 'parent',
				tolerance: 'pointer',
				change: function( event, ui ){
					$(this).closest('.widget-inside').trigger('change');
				},
			});


		},

	}; // END var SinatraCoreAdminWidgets.

	SinatraCoreAdminWidgets.init();
	window.SinatraCoreAdminWidgets = SinatraCoreAdminWidgets;	

})( jQuery );
