<?php
/**
 * Sinatra Demo Library. Install a copy of a Sinatra demo to your website.
 *
 * @package Sinatra Core
 * @author  Sinatra Team <hello@sinatrawp.com>
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sinatra Demo Library Class.
 *
 * @since 1.0.0
 * @package Sinatra Core
 */
final class Sinatra_Demo_Library_Page {

	/**
	 * Singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Main Sinatra Demo Library Page Instance.
	 *
	 * @since 1.0.0
	 * @return Sinatra_Demo_Library_Page
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Sinatra_Demo_Library_Page ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 100 );
		add_action( 'admin_print_footer_scripts-sinatra_page_sinatra-demo-library', array( $this, 'print_templates' ) );
		add_filter( 'sinatra_admin_page_tabs', array( $this, 'add_admin_page_tabs' ) );
		add_filter( 'sinatra_dashboard_navigation_items', array( $this, 'update_navigation_items' ) );

		do_action( 'sinatra_demo_library_page_loaded' );
	}

	/**
	 * Add to menu.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu() {

		// Demo Library page.
		add_submenu_page(
			'sinatra-dashboard',
			esc_html__( 'Demo Library', 'sinatra-core' ),
			'Demo Library',
			apply_filters( 'sinatra_manage_cap', 'edit_theme_options' ),
			'sinatra-demo-library',
			array( $this, 'render_demo_library' )
		);
	}

	/**
	 * Render Demo Library content.
	 *
	 * @since 1.0.0
	 */
	public function render_demo_library() {

		sinatra_dashboard()->render_navigation();

		?>
		<div class="si-container">

			<div class="sinatra-section-title">
				<h2 class="sinatra-section-title"><?php esc_html_e( 'Demo Library', 'sinatra-core' ); ?></h2>

				<div class="demo-search">
					<input type="search" placeholder="<?php esc_html_e( 'Filter&hellip;', 'sinatra-core' ); ?>" id="sinatra-search-demos" />
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
				</div>

				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=sinatra-demo-library' ), 'refresh_templates', 'sinatra_core_nonce' ) ); ?>" class="si-btn secondary"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg><?php esc_html_e( 'Refresh', 'sinatra-core' ); ?></a>
			</div><!-- END .sinatra-section-title -->

			<div class="demo-filters">

				<ul class="demo-categories">
					<li class="selected" data-category=""><a href="#"><?php esc_html_e( 'All', 'sinatra-core' ); ?></a></li>
					<li><a href="#" data-category="blog"><?php esc_html_e( 'Blog', 'sinatra-core' ); ?></a></li>
					<li><a href="#" data-category="shop"><?php esc_html_e( 'Shop', 'sinatra-core' ); ?></a></li>
				</ul>

				<ul class="demo-builders">
					<li class="selected"><a href="#" data-builder="block-editor"><?php esc_html_e( 'Gutenberg', 'sinatra-core' ); ?></a></li>
				</ul>
			</div>

			<div class="sinatra-section sinatra-columns demos">
			</div><!-- END .demos -->

			<p class="demo-notice">
				<?php esc_html_e( 'New demos coming soon', 'sinatra-core' ); ?>
			</p>

		</div>
		<?php
	}

	/**
	 * Print the JavaScript templates used to render demo library page.
	 *
	 * Templates are imported into the JS use wp.template.
	 *
	 * @since 1.0.0
	 */
	public function print_templates() {
		?>
		<script type="text/template" id="tmpl-sinatra-core-template">
		</script>

		<script type="text/template" id="tmpl-sinatra-core-demo-item">
			<div class="sinatra-column">
				<div class="sinatra-demo"
					data-demo-id="{{data.slug}}"
					data-demo-pro="{{data.pro}}"
					data-demo-url="{{{data.url}}}"
					data-demo-description="{{{data.description}}}"
					data-demo-screenshot="{{{data.screenshot}}}"
					data-demo-name="{{{data.name}}}"
					data-demo-slug="{{{data.slug}}}"
					data-required-plugins="{{JSON.stringify( data.required_plugins )}}"
					>

					<div class="demo-screenshot">
						<img src="{{{data.screenshot}}}" />
						<span class="text-overlay">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
							<span>Preview Demo</span>
						</span>

						<# if ( ! _.isEmpty( data.categories ) ) { #>
							<div class="demo-cat-list">
								<# _.each( data.categories, function( category ) { #>
									<span>{{{category}}}</span>
								<# } ); #>
							</div>
						<# } #>
					</div>

					<div class="demo-meta">
						<div class="demo-name">
							<span class="name">{{{data.name}}}</span>
						</div>
						<div class="demo-actions">
							<a class="si-btn primary btn-small import" href="#" aria-label="<?php esc_attr_e( 'Import', 'sinatra-core' ); ?> {{data.name}}"><?php esc_html_e( 'Import Demo', 'sinatra-core' ); ?></a>
							<a class="si-btn secondary btn-small preview" href="#" aria-label="<?php esc_attr_e( 'Preview', 'sinatra-core' ); ?> {{data.name}}"><?php esc_html_e( 'Preview', 'sinatra-core' ); ?></a>
						</div>
					</div>
				</div>
			</div>
		</script>

		<script type="text/template" id="tmpl-sinatra-core-demo-preview">

			<div class="sinatra-demo-preview theme-install-overlay wp-full-overlay expanded">

				<div class="wp-full-overlay-sidebar">

					<div class="wp-full-overlay-header"
							data-demo-id="{{{data.id}}}"
							data-demo-pro="{{{data.pro}}}"
							data-demo-url="{{{data.url}}}"
							data-demo-name="{{{data.name}}}"
							data-demo-description="{{{data.description}}}"
							data-demo-slug="{{{data.slug}}}"
							data-demo-screenshot="{{{data.screenshot}}}"
							data-content="{{{data.content}}}"
							data-required-plugins="{{data.required_plugins}}">

						<button class="close-full-overlay"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'sinatra-core' ); ?></span></button>
						<button class="previous-theme"><span class="screen-reader-text"><?php esc_html_e( 'Previous', 'sinatra-core' ); ?></span></button>
						<button class="next-theme"><span class="screen-reader-text"><?php esc_html_e( 'Next', 'sinatra-core' ); ?></span></button>
						<span class="spinner"></span>
						<a class="si-btn primary hide-if-no-customize sinatra-demo-import" href="#" disabled="disabled">
							<?php esc_html_e( 'Import Demo', 'sinatra-core' ); ?>
						</a>

					</div>
					<div class="wp-full-overlay-sidebar-content">
						<div class="install-theme-info">

							<# if ( data.pro ) { #>
								<span class="site-type {{{data.pro}}}">Pro</span>
							<# } #>

							<div class="si-demo-name">
								<span><?php esc_html_e( 'You are previewing', 'sinatra-core' ); ?></span>
								<h3>{{{data.name}}}</h3>
							</div>

							<# if ( data.screenshot ) { #>
								<div class="theme-screenshot-wrap">
									<img class="theme-screenshot" src="{{{data.screenshot}}}" alt="">
								</div>
							<# } #>

							<div class="theme-description">
								{{{data.description}}}
							</div>

							<div class="si-demo-section">

								<div class="si-demo-section-title">
									<span class="control-heading"><?php esc_html_e( 'Import Options', 'sinatra-core' ); ?></span>
									<span class="control-toggle">
										<input type="checkbox" id="options_toggle" name="options_toggle" aria-hidden="true">
										<label for="options_toggle" aria-hidden="true"></label>
									</span>
								</div>

								<div class="si-demo-section-content import-options">
									<p>
										<label class="si-checkbox">
											<input type="checkbox" checked name="import_customizer" id="import_customizer" />
											<span class="si-label"><?php esc_html_e( 'Import Customizer Settings', 'sinatra-core' ); ?></span>
										</label>
									</p>

									<p>
										<label class="si-checkbox">
											<input type="checkbox" checked name="import_content" id="import_content" />
											<span class="si-label"><?php esc_html_e( 'Import Content', 'sinatra-core' ); ?></span>
											<span class="si-tooltip" data-tooltip="<?php esc_html_e( 'Import pages, posts and menus from this demo.', 'sinatra-core' ); ?>"><span class="dashicons dashicons-editor-help"></span>
										</label>
									</p>

									<p>
										<label class="si-checkbox">
											<input type="checkbox" checked name="import_media" id="import_media" />
											<span class="si-label"><?php esc_html_e( 'Import Media', 'sinatra-core' ); ?></span>
										</label>
									</p>

									<p>
										<label class="si-checkbox">
											<input type="checkbox" checked name="import_widgets" id="import_widgets" />
											<span class="si-label"><?php esc_html_e( 'Import Widgets', 'sinatra-core' ); ?></span>
										</label>
									</p>

								</div>
							</div>

							<# if ( ! _.isEmpty( data.required_plugins ) ) { #>

								<div class="si-demo-section">
									<div class="si-demo-section-title">
										<span class="control-heading"><?php esc_html_e( 'Plugins Used in This Demo', 'sinatra-core' ); ?> ({{{ _.size( data.required_plugins )}}})</span>
										<span class="control-toggle">
											<input type="checkbox" id="install_plugins_toggle" name="install_plugins_toggle" aria-hidden="true">
											<label for="install_plugins_toggle" aria-hidden="true"></label>
										</span>
									</div>

									<div class="si-demo-section-content plugin-list">

										<# _.each( data.required_plugins, function( plugin ) { #>

											<p>
												<label class="si-checkbox plugin-{{plugin.status}}">
													<input type="checkbox" name="install_plugin_{{plugin.slug}}" id="install_plugin_{{plugin.slug}}" data-slug="{{plugin.slug}}" checked="checked" data-status="{{plugin.status}}" <# if ( 'active' === plugin.status ) { #> disabled="disabled" <# } #>/>
													<span class="si-label">{{{plugin.name}}}</span>

													<# if ( 'active' === plugin.status ) { #>
														<em><i class="dashicons dashicons-yes"></i><?php esc_html_e( 'Already installed', 'sinatra-core' ); ?></em>
													<# } #>
												</label>
											</p>

										<# } ) #>

										<em class="theme-description"><?php esc_html_e( 'These plugins will be auto-installed for you.', 'sinatra-core' ); ?></em>
									</div>
								</div>

							<# } #>

						</div>
					</div>

					<div class="wp-full-overlay-footer">
						<div class="footer-import-button-wrap">
							<a class="si-btn primary large-button hide-if-no-customize sinatra-demo-import" href="#" disabled="disabled">
								<span class="spinner si-spinner"></span>
								<span class="status"><?php esc_html_e( 'Import Demo', 'sinatra-core' ); ?></span>
								<span class="percent"></span>
							</a>
							<div id="si-progress-bar">
								<div class="si-progress-percentage"></div>
							</div>
						</div>
						<button type="button" class="collapse-sidebar button" aria-expanded="true"
								aria-label="<?php esc_html_e( 'Collapse Sidebar', 'sinatra-core' ); ?>">
							<span class="collapse-sidebar-arrow"></span>
							<span class="collapse-sidebar-label"><?php esc_html_e( 'Collapse', 'sinatra-core' ); ?></span>
						</button>

						<div class="devices-wrapper">
							<div class="devices">
								<button type="button" class="preview-desktop active" aria-pressed="true" data-device="desktop">
									<span class="screen-reader-text"><?php esc_html_e( 'Enter desktop preview mode', 'sinatra-core' ); ?></span>
								</button>
								<button type="button" class="preview-tablet" aria-pressed="false" data-device="tablet">
									<span class="screen-reader-text"><?php esc_html_e( 'Enter tablet preview mode', 'sinatra-core' ); ?></span>
								</button>
								<button type="button" class="preview-mobile" aria-pressed="false" data-device="mobile">
									<span class="screen-reader-text"><?php esc_html_e( 'Enter mobile preview mode', 'sinatra-core' ); ?></span>
								</button>
							</div>
						</div>

					</div>
				</div>
				<div class="wp-full-overlay-main">
					<iframe src="{{{data.url}}}" title="<?php esc_attr_e( 'Preview', 'sinatra-core' ); ?>"></iframe>
				</div>
			</div>
		</script>
		<?php
	}

	/**
	 * Add tabs to Sinatra Dashboard page.
	 *
	 * @since 1.0.0
	 * @param array $items Array of navigation items.
	 */
	public function update_navigation_items( $items ) {

		$demo = array(
			'demo-library' => array(
				'id'    => 'demo-library',
				'name'  => esc_html__( 'Demo Library', 'sinatra-core' ),
				'icon'  => '',
				'url'   => admin_url( 'admin.php?page=sinatra-demo-library' ),
			),
		);

		$items = sinatra_array_insert( $items, $demo, 'changelog', 'before' );

		return $items;
	}
}

/**
 * The function which returns the one Sinatra_Demo_Library_Page instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $sinatra_demo_library_page = sinatra_demo_library_page(); ?>
 *
 * @since 1.0.0
 * @return object
 */
function sinatra_demo_library_page() {
	return Sinatra_Demo_Library_Page::instance();
}

sinatra_demo_library_page();
