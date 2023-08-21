<?php
/**
 * Sinatra Core Admin class. Sinatra related pages in WP Dashboard.
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
 * Sinatra Core Admin Class.
 *
 * @since 1.0.0
 * @package Sinatra Core
 */
final class Sinatra_Core_Admin {

	/**
	 * Singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Main Sinatra Core Admin Instance.
	 *
	 * @since 1.0.0
	 * @return Sinatra_Core_Admin
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Sinatra_Core_Admin ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since  1.0.0
	 */
	public function __construct() {

		if ( ! is_admin() ) {
			return;
		}

		// Init Sinatra Core admin.
		add_action( 'after_setup_theme', array( $this, 'init_admin' ), 99 );

		// Fetch recommended plugins remotely.
		add_filter( 'sinatra_recommended_plugins', array( $this, 'recommended_plugins' ) );

		// Sinatra Core Admin loaded.
		do_action( 'sinatra_core_admin_loaded' );
	}

	/**
	 * Include files.
	 *
	 * @since 1.0.0
	 */
	private function includes() {

		// Demo Library.
		require_once SINATRA_CORE_PLUGIN_DIR . 'includes/admin/demo-library/class-sinatra-demo-library.php';
	}

	/**
	 * Admin init.
	 *
	 * @since 1.0.0
	 */
	public function init_admin() {

		if ( ! defined( 'SINATRA_THEME_VERSION' ) ) {
			add_action( 'admin_notices', array( $this, 'theme_required_notice' ) );
			return;
		}

		// Add Sinatra admin page.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 100 );
		add_action( 'admin_menu', array( $this, 'add_changelog_menu' ), 999 );

		// Change about page navigation.
		add_filter( 'sinatra_dashboard_navigation_items', array( $this, 'update_navigation_items' ) );

		// Add changelog section.
		add_action( 'sinatra_after_changelog', array( $this, 'changelog' ) );

		// Enqueue scripts & styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		$this->includes();
	}

	/**
	 * Add main menu.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu() {

		// Remove from Appearance.
		remove_submenu_page( 'themes.php', 'sinatra-dashboard' );
		remove_submenu_page( null, 'sinatra-plugins' );

		// Add a new menu item.
		add_menu_page(
			esc_html__( 'Sinatra', 'sinatra-core' ),
			'Sinatra', // This menu cannot be translated because it's used for the $hook prefix.
			apply_filters( 'sinatra_manage_cap', 'edit_theme_options' ), // phpcs:ignore
			'sinatra-dashboard',
			array( sinatra_dashboard(), 'render_dashboard' ),
			'dashicons-si-brand',
			apply_filters( 'sinatra_menu_position', '999.2' ) // phpcs:ignore
		);

		// About page.
		add_submenu_page(
			'sinatra-dashboard',
			esc_html__( 'About', 'sinatra-core' ),
			'About',
			apply_filters( 'sinatra_manage_cap', 'edit_theme_options' ), // phpcs:ignore
			'sinatra-dashboard',
			array( sinatra_dashboard(), 'render_dashboard' )
		);

		// Install Plugins page.
		add_submenu_page(
			'sinatra-dashboard',
			esc_html__( 'Plugins', 'sinatra-core' ),
			'Plugins',
			apply_filters( 'sinatra_manage_cap', 'edit_theme_options' ), // phpcs:ignore
			'sinatra-plugins',
			array( sinatra_dashboard(), 'render_plugins' )
		);
	}

	/**
	 * Add changelog menu.
	 *
	 * @since 1.0.0
	 */
	public function add_changelog_menu() {

		remove_submenu_page( null, 'sinatra-changelog' );

		// Changelog page.
		add_submenu_page(
			'sinatra-dashboard',
			esc_html__( 'Changelog', 'sinatra-core' ),
			'Changelog',
			apply_filters( 'sinatra_manage_cap', 'edit_theme_options' ), // phpcs:ignore
			'sinatra-changelog',
			array( sinatra_dashboard(), 'render_changelog' )
		);
	}

	/**
	 * Add menu items to Sinatra Dashboard navigation.
	 *
	 * @param array $items Array of navigation items.
	 * @since 1.0.0
	 */
	public function update_navigation_items( $items ) {

		$items['dashboard']['url'] = admin_url( 'admin.php?page=sinatra-dashboard' );
		$items['plugins']['url']   = admin_url( 'admin.php?page=sinatra-plugins' );
		$items['changelog']['url'] = admin_url( 'admin.php?page=sinatra-changelog' );

		return $items;
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since 1.0.0
	 */
	public function enqueue() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style(
			'sinatra-core-dashicon',
			SINATRA_CORE_PLUGIN_URL . 'assets/css/admin-dashicon' . $suffix . '.css',
			null,
			SINATRA_CORE_VERSION
		);
	}

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @since 1.0.0
	 */
	public function changelog() {

		$changelog = SINATRA_CORE_PLUGIN_DIR . '/changelog.txt';

		if ( ! file_exists( $changelog ) ) {
			$changelog = esc_html__( 'Changelog file not found.', 'sinatra-core' );
		} elseif ( ! is_readable( $changelog ) ) {
			$changelog = esc_html__( 'Changelog file not readable.', 'sinatra-core' );
		} else {
			global $wp_filesystem;

			// Check if the the global filesystem isn't setup yet.
			if ( is_null( $wp_filesystem ) ) {
				WP_Filesystem();
			}

			$changelog = $wp_filesystem->get_contents( $changelog );
		}

		?>
		<div class="sinatra-section-title sinatra-core-changelog">
			<h2 class="sinatra-section-title">
				<span><?php esc_html_e( 'Sinatra Core Plugin Changelog', 'sinatra-core' ); ?></span>
				<span class="changelog-version"><?php echo esc_html( sprintf( 'v%1$s', SINATRA_CORE_VERSION ) ); ?></span>
			</h2>

		</div><!-- END .sinatra-section-title -->

		<div class="sinatra-section sinatra-columns">

			<div class="sinatra-column column-12">
				<div class="sinatra-box sinatra-changelog">
					<pre><?php echo esc_html( $changelog ); ?></pre>
				</div>
			</div>
		</div><!-- END .sinatra-columns -->
		<?php
	}

	/**
	 * Display notice.
	 *
	 * @since 1.0.0
	 */
	public function theme_required_notice() {

		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Sinatra Theme needs to be installed and activated in order to use Sinatra Core plugin.', 'sinatra-core' ) . ' <a href="' . esc_url( admin_url( 'themes.php' ) ) . '"><strong>' . esc_html__( 'Install & Activate', 'sinatra-core' ) . '</strong></a>.</p></div>';
	}

	/**
	 * Fetch plugins config array from remote server.
	 *
	 * @since 1.0.0
	 * @param array $plugins Array of recommended plugins.
	 */
	public function recommended_plugins( $plugins ) {

		$remote = get_site_transient( 'sinatra_check_plugin_update' );

		if ( false === $remote ) {

			$response = wp_remote_get(
				'https://sinatrawp.com/wp-json/api/v1/plugins',
				array(
					'user-agent' => 'Sinatra/' . SINATRA_THEME_VERSION . ';',
					'timeout'    => 60,
				)
			);

			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				set_site_transient( 'sinatra_check_plugin_update', 'error', 60 * 60 * 6 );
				return;
			}

			$body    = wp_remote_retrieve_body( $response );
			$plugins = json_decode( $body, true );

			set_site_transient( 'sinatra_check_plugin_update', $plugins, 60 * 60 * 24 * 3 );
		} elseif ( 'error' === $remote ) {
			return $plugins;
		} else {
			$plugins = $remote;
		}

		return $plugins;
	}
}

/**
 * The function which returns the one Sinatra_Core_Admin instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $sinatra_core_admin = sinatra_core_admin(); ?>
 *
 * @since 1.0.0
 * @return object
 */
function sinatra_core_admin() {
	return Sinatra_Core_Admin::instance();
}

sinatra_core_admin();
