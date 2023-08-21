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
final class Sinatra_Demo_Library {

	/**
	 * Singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Version.
	 *
	 * @since 1.0.0
	 * @var sting
	 */
	public $version = '1.0.0';

	/**
	 * Demo templates.
	 *
	 * @since 1.0.0
	 * @var sting
	 */
	public $templates = false;

	/**
	 * Main Sinatra Demo Library Instance.
	 *
	 * @since 1.0.0
	 * @return Sinatra_Demo_Library
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Sinatra_Demo_Library ) ) {
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

		$this->version = defined( SINATRA_CORE_VERSION ) ? SINATRA_CORE_VERSION : $this->version;

		$this->includes();
		$this->hooks();

		do_action( 'sinatra_demo_library_loaded' );
	}

	/**
	 * Include files.
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		require_once plugin_dir_path( __FILE__ ) . 'class-sinatra-demo-library-page.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-sinatra-demo-importer.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-sinatra-demo-exporter.php';
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
		add_action( 'admin_init', array( $this, 'refresh_templates' ) );
		add_action( 'wp_ajax_sinatra-core-filter-demos', array( $this, 'filter_templates' ) );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $hook Current hook name.
	 * @return void
	 */
	public function admin_enqueue( $hook = '' ) {

		if ( 'sinatra_page_sinatra-demo-library' !== $hook ) {
			return;
		}

		wp_enqueue_script(
			'sinatra-demo-library',
			plugin_dir_url( __FILE__ ) . 'assets/js/demo-library.min.js',
			array( 'jquery', 'wp-util', 'updates' ),
			$this->version,
			true
		);

		$localized = array(
			'strings'   => array(
				'closeWindowWarning'  => __( 'Warning! Demo import process is not complete. Don\'t close the window until import process is complete. Do you still want to leave the window?', 'sinatra-core' ),
				'importDemoWarning'   => __( 'Demo import process will start now. Please do not close the window until import process is complete.', 'sinatra-core' ),
				'importing'           => __( 'Importing...', 'sinatra-core' ),
				'installingPlugin'    => __( 'Installing plugin', 'sinatra-core' ) . ' ',
				'installed'           => __( 'Plugin installed!', 'sinatra-core' ),
				'activatingPlugin'    => __( 'Activating plugin', 'sinatra-core' ) . ' ',
				'activated'           => __( 'Plugin activated! ', 'sinatra-core' ),
				'importCompleted'     => __( 'All Done! Visit Site', 'sinatra-core' ),
				'importingCustomizer' => __( 'Importing Customizer...', 'sinatra-core' ),
				'importingContent'    => __( 'Importing Content...', 'sinatra-core' ),
				'importingWPForms'    => __( 'Importing WPForms...', 'sinatra-core' ),
				'importingOptions'    => __( 'Importing Options...', 'sinatra-core' ),
				'importingWidgets'    => __( 'Importing Widgets...', 'sinatra-core' ),
				'preview'             => __( 'Preview', 'sinatra-core' ),
				'preparing'           => __( 'Preparing Data...', 'sinatra-core' ),
				'noResultsFound'      => __( 'No results found', 'sinatra-core' ),
			),
			'homeurl'   => home_url( '/' ),
			'templates' => $this->get_templates(),
		);

		$localized = apply_filters( 'sinatra_core_demo_library_localized', $localized );

		wp_localize_script(
			'sinatra-demo-library',
			'sinatraCoreDemoLibrary',
			$localized
		);

		wp_enqueue_style(
			'sinatra-core-admin',
			plugin_dir_url( __FILE__ ) . 'assets/css/demo-library.min.css',
			$this->version,
			true
		);
	}

	/**
	 * Get templates.
	 *
	 * @since  1.0.0
	 *
	 * @return array Array of demo templates.
	 */
	public function get_templates() {

		// Check if we have stored templates.
		if ( false === $this->templates ) {
			$this->templates = get_transient( 'sinatra_core_demo_templates' );
		}

		// No stored templates, get from remote.
		if ( false === $this->templates ) {

			$response = wp_remote_get(
				'https://sinatrawp.com/wp-json/api/v1/demos',
				array(
					'user-agent' => 'Sinatra/' . SINATRA_THEME_VERSION . ';',
					'timeout'    => 60,
				)
			);

			if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
				$this->templates = (array) json_decode( stripcslashes( wp_remote_retrieve_body( $response ) ), true );
			}

			if ( is_array( $this->templates ) && ! empty( $this->templates ) ) {
				foreach ( $this->templates as $id => $template ) {

					// Skip demos that require a newer version of Sinatra Core.
					if ( defined( 'SINATRA_CORE_VERSION' ) && isset( $template['sinatra-core-version'] ) && version_compare( SINATRA_CORE_VERSION, $template['sinatra-core-version'] ) < 0 ) {
						unset( $this->templates[ $id ] );
						continue;
					}

					// Skip demos that require a newer version of Sinatra Theme.
					if ( defined( 'SINATRA_THEME_VERSION' ) && isset( $template['sinatra-theme-version'] ) && version_compare( SINATRA_THEME_VERSION, $template['sinatra-theme-version'] ) < 0 ) {
						unset( $this->templates[ $id ] );
						continue;
					}
				}
			}

			set_transient( 'sinatra_core_demo_templates', $this->templates, 60 * 60 * 24 );
		}

		if ( is_array( $this->templates ) && ! empty( $this->templates ) ) {
			foreach ( $this->templates as $id => $template ) {
				$this->templates[ $id ]['plugins'] = $this->required_plugins( $template );
			}
		}

		return $this->templates;
	}

	/**
	 * Refresh demo templates.
	 *
	 * @since 1.0.0
	 */
	public function refresh_templates() {

		// Security check.
		if ( ! isset( $_GET['sinatra_core_nonce'] ) || ! wp_verify_nonce( $_GET['sinatra_core_nonce'], 'refresh_templates' ) ) {
			return;
		}

		delete_transient( 'sinatra_core_demo_templates' );

		wp_safe_redirect( admin_url( 'admin.php?page=sinatra-demo-library' ) );
		die;
	}

	/**
	 * Filter demo templates.
	 *
	 * @since 1.0.0
	 */
	public function filter_templates() {

		// Nonce check.
		check_ajax_referer( 'sinatra_nonce' );

		// Permission check.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( esc_html__( 'You do not have permission to import a demo.', 'sinatra-core' ), 'import_error' );
		}

		$templates = $this->get_templates();

		if ( ! isset( $_POST['filters'] ) ) {
			wp_send_json_success( $templates );
		}

		$filters = array(
			'category' => isset( $_POST['filters']['category'] ) ? sanitize_text_field( wp_unslash( $_POST['filters']['category'] ) ) : '',
			'builder'  => isset( $_POST['filters']['builder'] ) ? sanitize_text_field( wp_unslash( $_POST['filters']['builder'] ) ) : '',
			's'        => isset( $_POST['filters']['s'] ) ? sanitize_text_field( wp_unslash( $_POST['filters']['s'] ) ) : '',
		);

		if ( ! empty( $templates ) && is_array( $templates ) ) {
			foreach ( $templates as $id => $template ) {

				// Check template category.
				if ( ! empty( $filters['category'] ) && ! array_key_exists( $filters['category'], $template['categories'] ) ) {
					unset( $templates[ $id ] );
					continue;
				}

				// Check template builder.
				if ( ! empty( $filters['builder'] ) && $filters['builder'] !== $template['page-builder'] ) {
					unset( $templates[ $id ] );
					continue;
				}

				// Check search filter.
				if ( ! empty( $filters['s'] ) && false === strpos( strtolower( $template['name'] ), strtolower( $filters['s'] ) ) ) {
					unset( $templates[ $id ] );
					continue;
				}
			}
		}

		wp_send_json_success( $templates );
	}

	/**
	 * Get required plugins.
	 *
	 * @since  1.0.0
	 * @param  array $template Template details.
	 * @return array Array of demo templates.
	 */
	public function required_plugins( $template ) {

		if ( ! isset( $template['plugins'] ) ) {
			return;
		}

		if ( ! function_exists( 'sinatra_plugin_utilities' ) ) {
			return $template['plugins'];
		}

		$plugins = array();

		foreach ( $template['plugins'] as $plugin ) {

			if ( sinatra_plugin_utilities()->is_activated( $plugin['slug'] ) ) {
				$plugin['status'] = 'active';
			} elseif ( sinatra_plugin_utilities()->is_installed( $plugin['slug'] ) ) {
				$plugin['status'] = 'installed';
			} else {
				$plugin['status'] = 'not_installed';
			}

			$plugins[] = $plugin;
		}

		return $plugins;
	}
}

/**
 * The function which returns the one Sinatra_Demo_Library instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $sinatra_demo_library = sinatra_demo_library(); ?>
 *
 * @since 1.0.0
 * @return object
 */
function sinatra_demo_library() {
	return Sinatra_Demo_Library::instance();
}

sinatra_demo_library();
