//--------------------------------------------------------------------//
// Sinatra Core Demo Library script.
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
	var SinatraCoreDemoLibrary = {

		import_start_time : '',
		install_plugins   : [],
		installed_plugins : [],
		progress          : 0,
		import_steps      : [],
		completed_steps   : [],

		/**
		 * Start the engine.
		 *
		 * @since 1.0.0
		 */
		init: function() {

			// Document ready
			$(document).ready( SinatraCoreDemoLibrary.ready );

			// Window load
			$(window).on( 'load', SinatraCoreDemoLibrary.load );

			// Bind UI actions
			SinatraCoreDemoLibrary.bindUIActions();

			// Trigger event when Sinatra fully loaded
			$(document).trigger( 'sinatraCoreReady' );
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
		},

		/**
		 * Window load.
		 *
		 * @since 1.0.0
		 */
		load: function() {
			SinatraCoreDemoLibrary.populateTemplates( sinatraCoreDemoLibrary.templates );
		},

		/**
		 * Bind UI actions.
		 *
		 * @since 1.0.0
		*/
		bindUIActions: function() {

			// Demo preview screen.
			$document.on( 'click' , '.sinatra-demo .demo-screenshot, .sinatra-demo .preview', SinatraCoreDemoLibrary.preview );
			
			// Direct import button.
			$document.on( 'click' , '.sinatra-demo .import', function(e) {
				$(this).siblings( '.preview' ).click();
				$( '.wp-full-overlay-main iframe' ).on( 'load', function() {
					$( '.wp-full-overlay-header .sinatra-demo-import' ).trigger( 'click' );
				});
			} );

			// Close preview screen.
			$document.on( 'click', '.close-full-overlay', SinatraCoreDemoLibrary.closePreview );
			
			$document.keyup( function(e) {
				if ( 27 === e.keyCode ) {
					$( '.close-full-overlay' ).trigger( 'click' );
				}
			});

			// Next/Preview demo.
			$document.on( 'click', '.next-theme', SinatraCoreDemoLibrary.previewNext );
			$document.on( 'click', '.previous-theme', SinatraCoreDemoLibrary.previewPrevious );

			// Preview demo on a device screen.
			$document.on( 'click', '.devices button', SinatraCoreDemoLibrary.previewDevice );

			// Collapse sidebar.
			$document.on( 'click', '.collapse-sidebar', SinatraCoreDemoLibrary.collapseSidebar );

			// Collapse section.
			$document.on( 'click', '.si-demo-section-title .control-toggle label', SinatraCoreDemoLibrary.collapsePreviewSection );

			// Import demo.
			$document.on( 'click', '.sinatra-demo-import', SinatraCoreDemoLibrary.importDemo );

			$document.on( 'click', '#import_content, #import_media', SinatraCoreDemoLibrary.importOptionsContent );

			// Import steps triggers.
			$document.on( 'sinatra-core-import_started', SinatraCoreDemoLibrary.importStarted );
			$document.on( 'sinatra-core-install_plugins', SinatraCoreDemoLibrary.pluginsInstallActivate );
			$document.on( 'sinatra-core-import_customizer', SinatraCoreDemoLibrary.importCustomizer );
			$document.on( 'sinatra-core-import_content', SinatraCoreDemoLibrary.importContent );
			$document.on( 'sinatra-core-import_widgets', SinatraCoreDemoLibrary.importWidgets );
			$document.on( 'sinatra-core-import_options', SinatraCoreDemoLibrary.importOptions );
			$document.on( 'sinatra-core-import_wpforms', SinatraCoreDemoLibrary.importWPForms );
			$document.on( 'sinatra-core-import_completed', SinatraCoreDemoLibrary.importCompleted );

			// Filter template list.
			$document.on( 'click', '.demo-filters a', SinatraCoreDemoLibrary.filters );
			$document.on( 'input', '.demo-search input', SinatraCoreDemoLibrary.search );
		},

		//--------------------------------------------------------------------//
		// Functions
		//--------------------------------------------------------------------//

		/**
		 * Demo preview page.
		 *
		 * On click on image, more link & preview button.
		 */
		preview: function( event ) {

			event.preventDefault();

			var site_id = $(this).parents('.sinatra-demo').data('demo-id') || '';

			var self = $(this).parents( '.sinatra-demo' );
			self.addClass( 'sinatra-demo-previewed' );

			$html.addClass( 'sinatra-demo-preview-on' );

			SinatraCoreDemoLibrary.previewDemo( self );
		},

		/**
		 * Preview Demo website.
		 */
		previewDemo: function( anchor ) {

			var template = wp.template( 'sinatra-core-demo-preview' );

			var data = {
				id                 : anchor.data( 'demo-id' ),
				pro                : anchor.data( 'demo-pro' ),
				url                : anchor.data( 'demo-url' ),
				screenshot         : anchor.data( 'demo-screenshot' ),
				name               : anchor.data( 'demo-name' ),
				description        : anchor.data( 'demo-description' ),
				slug               : anchor.data( 'demo-slug' ),
				required_plugins   : anchor.data( 'required-plugins' ),
			};

			$( '.theme-install-overlay' ).remove();
			$( '.sinatra-section.demos' ).append( template( data ) );
			$( '.theme-install-overlay' ).css('display', 'block');

			SinatraCoreDemoLibrary.updateNextPrev();

			$( '.wp-full-overlay-main iframe' ).on( 'load', function() {
				$( '.sinatra-demo-preview .sinatra-demo-import' ).removeAttr( 'disabled' );
			});
		},

		/**
		 * Check Next Previous Buttons.
		 */
		updateNextPrev: function() {

			if ( $body.hasClass( 'importing' ) ) {
				$( '.next-theme, .previous-theme' ).addClass( 'disabled' );
				return;
			}

			var current = $( '.sinatra-demo-previewed' ).parent();
			var next    = current.nextAll( '.sinatra-column' ).length;
			var prev    = current.prevAll( '.sinatra-column' ).length;

			if ( 0 == next ) {
				$( '.next-theme' ).addClass( 'disabled' );
			} else if ( 0 != next ) {
				$( '.next-theme' ).removeClass( 'disabled' );
			}

			if ( 0 == prev ) {
				$( '.previous-theme' ).addClass( 'disabled' );
			} else if ( 0 != prev ) {
				$( '.previous-theme' ).removeClass( 'disabled' );
			}

			return;
		},

		/**
		 * Close demo preview screen.
		 */
		closePreview: function( event ) {

			event.preventDefault();

			// Import process is started?
			// And Closing the window? Then showing the warning confirm message.
			if ( $('body').hasClass( 'importing' ) && ! confirm( sinatraCoreDemoLibrary.strings.closeWindowWarning ) ) {
				return;
			}

			$( 'body' ).removeClass( 'importing' );
			$( '.previous-theme, .next-theme' ).removeClass( 'disabled' );
			$( '.theme-install-overlay' ).css( 'display', 'none' );
			$( '.theme-install-overlay' ).remove();
			$( '.sinatra-demo-previewed' ).removeClass( 'sinatra-demo-previewed' );
			$html.removeClass( 'sinatra-demo-preview-on' );
		},

		/**
		 * Preview previous demo.
		 */
		previewPrevious: function( event ) {
			
			event.preventDefault();

			var current = $( '.sinatra-demo-previewed' ).removeClass( 'sinatra-demo-previewed' ).parent();
			var prev    = current.prev( '.sinatra-column' ).find( '.sinatra-demo' ).addClass( 'sinatra-demo-previewed' );

			var site_id = $(this).parents( '.wp-full-overlay-header' ).data('demo-id') || '';

			SinatraCoreDemoLibrary.previewDemo( prev );
		},

		/**
		 * Preview next demo.
		 */
		previewNext: function( event ) {
			
			event.preventDefault();
			
			var current = $( '.sinatra-demo-previewed' ).removeClass( 'sinatra-demo-previewed' ).parent();
			var next    = current.next( '.sinatra-column' ).find( '.sinatra-demo' ).addClass( 'sinatra-demo-previewed' );

			var site_id = $(this).parents( '.wp-full-overlay-header' ).data( 'demo-id' ) || '';

			SinatraCoreDemoLibrary.previewDemo( next );
		},

		/**
		 * Preview on a device sized screen.
		 */
		previewDevice: function( event ) {

			var device = $( event.currentTarget ).data( 'device' );

			$( '.theme-install-overlay' )
				.removeClass( 'preview-desktop preview-tablet preview-mobile' )
				.addClass( 'preview-' + device )
				.data( 'current-preview-device', device );

			SinatraCoreDemoLibrary.previewDeviceButtons( device );
		},

		/**
		 * Toggle preview device buttons.
		 */
		previewDeviceButtons: function( device ) {
			
			var $devices = $( '.wp-full-overlay-footer .devices' );

			$devices.find( 'button' )
				.removeClass( 'active' )
				.attr( 'aria-pressed', false );

			$devices.find( 'button.preview-' + device )
				.addClass( 'active' )
				.attr( 'aria-pressed', true );
		},

		/**
		 * Collapse Sidebar.
		 */
		collapseSidebar: function() {

			event.preventDefault();

			var overlay = $( '.wp-full-overlay' );

			if ( overlay.hasClass( 'expanded' ) ) {
				overlay.removeClass( 'expanded' );
				overlay.addClass( 'collapsed' );
				return;
			}

			if ( overlay.hasClass( 'collapsed' ) ) {
				overlay.removeClass( 'collapsed' );
				overlay.addClass( 'expanded' );
				return;
			}
		},

		/**
		 * Collapse Section.
		 */
		collapsePreviewSection: function() {

			var section_content = $(this).closest( '.si-demo-section-title' ).next( '.si-demo-section-content' );

			if ( $(this).prev('input[type="checkbox"]').is(':checked') ) {
				section_content.removeClass( 'hidden' );
			} else {
				section_content.addClass( 'hidden' );
			}

		},

		/**
		 * Start Demo import.
		 */
		importDemo: function( event ) {

			event.preventDefault();

			if ( ! confirm( sinatraCoreDemoLibrary.strings.importDemoWarning ) ) {
				return;
			}	

			var date = new Date();

			SinatraCoreDemoLibrary.import_start_time = new Date();

			var disabled = $(this).attr( 'disabled' );

			if ( typeof disabled === 'undefined' || ! $(this).hasClass('disabled') ) {

				// Get list of plugins to install.
				SinatraCoreDemoLibrary.install_plugins = $( '.plugin-list input:checked' ).not( ':disabled' ).map( function(){
					return {
						slug:   $(this).data( 'slug' ),
						name:   $(this).next( '.si-label' ).html(),
						status: $(this).data( 'status' ),
					};
				}).get();

				// Get import options.
				SinatraCoreDemoLibrary.import_steps = $( '.si-demo-section-content.import-options input:checked' ).map( function(){
					var step = $(this).attr('id');

					if ( 'import_media' !== step ) {
						return $(this).attr('id');
					}
				}).get();

				if ( SinatraCoreDemoLibrary.install_plugins.length ) {
					SinatraCoreDemoLibrary.import_steps.unshift( 'install_plugins' );
				}

				if ( SinatraCoreDemoLibrary.import_steps.includes( 'import_content') ) {

					if ( $( '#install_plugin_wpforms-lite' ).is(':checked') ) {
						SinatraCoreDemoLibrary.import_steps.splice( SinatraCoreDemoLibrary.import_steps.indexOf( 'import_content'), 0, 'import_wpforms' );
					}
					SinatraCoreDemoLibrary.import_steps.push( 'import_options' );				
				}

				SinatraCoreDemoLibrary.import_steps.unshift( 'import_started' );
				SinatraCoreDemoLibrary.import_steps.push( 'import_completed' );

				// Start import.
				$body.addClass( 'importing' );

				SinatraCoreDemoLibrary.updateNextPrev();

				$( '.si-checkbox input' ).attr( 'disabled', 'disabled' );
				$( '.wp-full-overlay-header .sinatra-demo-import' ).text( sinatraCoreDemoLibrary.strings.importing );

				$document.trigger( 'sinatra-core-' + SinatraCoreDemoLibrary.import_steps[0] );
			}
		},

		/**
		 * Install and activate selected plugins.
		 */
		pluginsInstallActivate: function() {

			// Set up progress delta.
			SinatraCoreDemoLibrary.progress_delta = ( 50 / SinatraCoreDemoLibrary.import_steps.length ) / SinatraCoreDemoLibrary.install_plugins.length;

			// Start plugin installation.
			SinatraCoreDemoLibrary.pluginInstall( SinatraCoreDemoLibrary.install_plugins[0] );
		},

		/**
		 * Install specific plugin.
		 */
		pluginInstall: function( plugin ) {

			$( '.wp-full-overlay-footer .status' ).text( sinatraCoreDemoLibrary.strings.installingPlugin + plugin.name );

			if ( wp.updates.shouldRequestFilesystemCredentials && ! wp.updates.ajaxLocked ) {

				wp.updates.requestFilesystemCredentials( event );

				$( document ).on( 'credential-modal-cancel', function() {
					wp.a11y.speak( wp.updates.l10n.updateCancel, 'polite' );
				} );
			}

			if ( 'not_installed' === plugin.status ) {

				wp.updates.installPlugin( {
					slug: plugin.slug,
					success: function() {

						SinatraCoreDemoLibrary.updateProgressBar();

						$( '.wp-full-overlay-footer .status' ).text( sinatraCoreDemoLibrary.strings.installed );

						SinatraCoreDemoLibrary.pluginActivate( plugin );
					},
					error: function( response ) {
						console.log( response );
					}
				});

			} else if ( 'installed' === plugin.status ) {

				SinatraCoreDemoLibrary.updateProgressBar();

				$( '.wp-full-overlay-footer .status' ).text( sinatraCoreDemoLibrary.strings.installed );

				SinatraCoreDemoLibrary.pluginActivate( plugin );

			} else {

				SinatraCoreDemoLibrary.updateProgressBar();
				
				$( '.wp-full-overlay-footer .status' ).text( sinatraCoreDemoLibrary.strings.activated );

				SinatraCoreDemoLibrary.installed_plugins.push( SinatraCoreDemoLibrary.install_plugins.shift() );

				if ( SinatraCoreDemoLibrary.install_plugins.length ) {
					return SinatraCoreDemoLibrary.pluginInstall( SinatraCoreDemoLibrary.install_plugins[0] );
				} else {
					SinatraCoreDemoLibrary.importNextStep();
				}

			}
		},

		/**
		 * Activate specific plugin.
		 */
		pluginActivate: function( plugin ) {

			$( '.wp-full-overlay-footer .status' ).text( sinatraCoreDemoLibrary.strings.activatingPlugin + plugin.name );

			return $.ajax({
				url : sinatra_strings.ajaxurl,
				type : 'POST',
				dataType: 'json',
				data : {
					_ajax_nonce: sinatra_strings.wpnonce,
					action:      'sinatra_core_import_step',
					import_step: 'activate_plugin',
					plugin:      plugin,
					demo_id:     $( '.wp-full-overlay-header' ).data( 'demo-slug' ),
				},
			}).then( function(data) {

				SinatraCoreDemoLibrary.updateProgressBar();
				
				$( '.wp-full-overlay-footer .status' ).text( sinatraCoreDemoLibrary.strings.activated );

				SinatraCoreDemoLibrary.installed_plugins.push( SinatraCoreDemoLibrary.install_plugins.shift() );

				if ( SinatraCoreDemoLibrary.install_plugins.length ) {
					return SinatraCoreDemoLibrary.pluginInstall( SinatraCoreDemoLibrary.install_plugins[0] );
				} else {
					SinatraCoreDemoLibrary.importNextStep();
				}
			});
		},

		/**
		 * Import step AJAX.
		 */
		importStepAJAX: function( data ) {

			data._ajax_nonce = sinatra_strings.wpnonce;
			data.action      = 'sinatra_core_import_step';
			data.demo_id     = $( '.wp-full-overlay-header' ).data( 'demo-slug' );

			return $.ajax({
				url : sinatra_strings.ajaxurl,
				type : 'POST',
				dataType: 'json',
				data : data,
			}).done( function(response) {

				if ( response.success ) {
					SinatraCoreDemoLibrary.updateProgressBar();
					SinatraCoreDemoLibrary.importNextStep();
				} else {
					console.log( response );
				}
			}).fail(function(jqXHR, textStatus, errorThrown)  {
				console.log(jqXHR);
				console.log(textStatus);
			    console.log(errorThrown);
			});
		},

		/**
		 * Import Started.
		 */
		importStarted: function() {

			var data = {
				import_step: 'import_started',
			};

			return SinatraCoreDemoLibrary.importStepAJAX( data );
		},

		/**
		 * Import Customizer.
		 */
		importCustomizer: function() {

			$( '.wp-full-overlay-footer .status' ).text( sinatraCoreDemoLibrary.strings.importingCustomizer );

			var data = {
				'import_step' : 'import_customizer'
			};

			return SinatraCoreDemoLibrary.importStepAJAX( data );
		},

		/**
		 * Import Content.
		 */
		importContent: function() {

			$( '.wp-full-overlay-footer .status' ).text( sinatraCoreDemoLibrary.strings.importingContent );

			var data = {
				import_step: 'import_content',
				attachments: $( '#import_media' ).is(':checked') ? 1 : 0,
			};

			return SinatraCoreDemoLibrary.importStepAJAX( data );
		},

		/**
		 * Import Customizer.
		 */
		importOptions: function() {

			$( '.wp-full-overlay-footer .status' ).text( sinatraCoreDemoLibrary.strings.importingOptions );

			var data = {
				import_step: 'import_options',
			};

			return SinatraCoreDemoLibrary.importStepAJAX( data );
		},

		/**
		 * Import WPForms.
		 */
		importWPForms: function() {

			$( '.wp-full-overlay-footer .status' ).text( sinatraCoreDemoLibrary.strings.importingWPForms );

			var data = {
				import_step: 'import_wpforms',
			};

			return SinatraCoreDemoLibrary.importStepAJAX( data );
		},

		/**
		 * Import Customizer.
		 */
		importWidgets: function() {

			$( '.wp-full-overlay-footer .status' ).text( sinatraCoreDemoLibrary.strings.importingWidgets );

			var data = {
				import_step: 'import_widgets',
			};

			return SinatraCoreDemoLibrary.importStepAJAX( data );
		},

		/**
		 * Import Completed.
		 */
		importCompleted: function() {

			var data = {
				import_step: 'import_completed',
			};

			SinatraCoreDemoLibrary.importStepAJAX( data );

			$( '.wp-full-overlay-header .sinatra-demo-import' ).html( sinatraCoreDemoLibrary.strings.preview );
			$( '.sinatra-demo-preview .sinatra-demo-import' ).removeClass( 'sinatra-demo-import' ).attr( 'href', sinatraCoreDemoLibrary.homeurl );
			$( '.wp-full-overlay-footer .status' ).text( sinatraCoreDemoLibrary.strings.importCompleted );

			$body.removeClass( 'importing' );

			// Particle animation
			setTimeout(function() {
				$( '.wp-full-overlay-footer .si-btn' ).addClass('animate');
			}, 100);

			setTimeout(function() {
				$( '.wp-full-overlay-footer .si-btn' ).removeClass('animate');
			}, 3000);

			SinatraCoreDemoLibrary.updateNextPrev();
		},

		/**
		 * Trigger next import step.
		 */
		importNextStep: function() {

			// No further steps defined.
			if ( ! SinatraCoreDemoLibrary.import_steps.length ) {
				$document.trigger( 'sinatra-core-import_completed' );
				return;
			}

			// Track completed steps.
			SinatraCoreDemoLibrary.completed_steps.push( SinatraCoreDemoLibrary.import_steps.shift() );

			// Update progress bar.
			SinatraCoreDemoLibrary.updateProgressBar();

			// Trigger next step.
			$document.trigger( 'sinatra-core-' + SinatraCoreDemoLibrary.import_steps[0] );
		},

		/**
		 * Update progress bar.
		 */
		updateProgressBar: function() {

			var remaining = 100 - SinatraCoreDemoLibrary.progress;
			var delta     = ( remaining / SinatraCoreDemoLibrary.import_steps.length );

			if ( SinatraCoreDemoLibrary.install_plugins.length > 0 ) {
				delta = delta / 2;
				delta = delta / SinatraCoreDemoLibrary.install_plugins.length;
			}

			SinatraCoreDemoLibrary.progress += delta;

			$( '#si-progress-bar .si-progress-percentage' ).css( 'width', SinatraCoreDemoLibrary.progress + '%' );
		},

		/**
		 * Import options: content and media dependency.
		 */
		importOptionsContent: function() {

			var $checkbox = $(this);

			if ( 'import_content' === $checkbox.attr('id') ) {

				if ( ! $checkbox.is(':checked') ) {
					$( '#import_media' ).attr( 'disabled', 'disabled' ).removeAttr( 'checked' );
				} else {
					$( '#import_media' ).removeAttr( 'disabled', 'disabled' );
				}
			}

			if ( 'import_media' === $checkbox.attr('id') && $checkbox.is(':checked') ) {
				$( '#import_content' ).attr( 'checked', 'checked' );
			}
		},

		/**
		 * Populate templates.
		 */
		populateTemplates: function( templates ) {

			$( '.sinatra-section.demos' ).html('');

			if ( _.isEmpty( templates ) ) {
				$( '.sinatra-section.demos' ).html( '<div class="sinatra-column">' + sinatraCoreDemoLibrary.strings.noResultsFound + '</div>' );
				return;
			}

			var data;
			var demo_template = wp.template( 'sinatra-core-demo-item' );

			for ( var key in templates ) {
				
				data = {
					id               : key,
					pro              : templates[key].pro,
					url              : templates[key].url,
					screenshot       : templates[key].screenshot,
					name             : templates[key].name,
					description      : templates[key].description,
					slug             : templates[key].slug,
					required_plugins : templates[key].plugins
				};

				$( '.sinatra-section.demos' ).append( demo_template( data ) );
			}
		},

		/**
		 * Handle clicks on filter items.
		 */
		filters: function() {
			var $this = $(this);

			$this.closest('ul').find('li').removeClass('selected');
			$this.closest('li').addClass('selected');

			SinatraCoreDemoLibrary.filterDemoList();
		},

		/**
		 * Handle clicks on filter items.
		 */
		search: function() {
			var $this = $(this);
			var timer = 0;

			if ( timer ) {
				clearTimeout( timer );
			}

			var search_input = $this.val();

			if ( ! search_input || search_input && search_input.length >= 4 ) {	
				timer = setTimeout( function() { SinatraCoreDemoLibrary.filterDemoList() }, 300 );
			}
		},

		/**
		 * Reload filtered demo list.
		 */
		filterDemoList: function() {

			var filters = {
				'category' : $( '.demo-filters .demo-categories li.selected a' ).data( 'category' ),
				'builder'  : $( '.demo-filters .demo-builders li.selected a' ).data( 'builder' ),
				's'        : $( '.demo-search input' ).val(),
			};

			return $.ajax({
				url : sinatra_strings.ajaxurl,
				type : 'POST',
				dataType: 'json',
				data : {
					_ajax_nonce: sinatra_strings.wpnonce,
					action: 'sinatra-core-filter-demos',
					filters: filters
				},
			}).then( function(response) {

				if ( response.success ) {
					SinatraCoreDemoLibrary.populateTemplates( response.data );
				}
			});
		}

	}; // END var SinatraCoreDemoLibrary.

	SinatraCoreDemoLibrary.init();
	window.SinatraCoreDemoLibrary = SinatraCoreDemoLibrary;	

})( jQuery );
