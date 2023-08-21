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
 * Sinatra Demo Exporter Class.
 *
 * @since 1.0.0
 * @package Sinatra Core
 */
final class Sinatra_Demo_Exporter {

	/**
	 * Singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Demo ID.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $demo_id;

	/**
	 * Main Sinatra Demo Exporter Instance.
	 *
	 * @since 1.0.0
	 * @return Sinatra_Demo_Exporter
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Sinatra_Demo_Exporter ) ) {
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

		// Add export listeners.
		add_action( 'init', array( $this, 'export' ) );
	}


	/**
	 * Export.
	 *
	 * @since 1.0.0
	 */
	public function export() {

		// Check if user has permission for this.
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}

		// Export Customizer.
		if ( isset( $_REQUEST['sinatra-core-customizer-export'] ) ) { // phpcs:ignore

			if ( ! class_exists( 'Sinatra_Customizer_Import_Export' ) ) {

				$class_customizer_import = plugin_dir_path( __FILE__ ) . 'importers/class-customizer-import-export.php';

				if ( file_exists( $class_customizer_import ) ) {
					require_once $class_customizer_import;

					Sinatra_Customizer_Import_Export::export();
				}
			}
		}

		// Export Widgets.
		if ( isset( $_REQUEST['sinatra-core-widgets-export'] ) ) { // phpcs:ignore

			if ( ! class_exists( 'Sinatra_Widgets_Import_Export' ) ) {

				$class_widgets_import = plugin_dir_path( __FILE__ ) . 'importers/class-widgets-import-export.php';

				if ( file_exists( $class_widgets_import ) ) {
					require_once $class_widgets_import;

					Sinatra_Widgets_Import_Export::export();
				}
			}
		}

		// Export Options.
		if ( isset( $_REQUEST['sinatra-core-options-export'] ) ) { // phpcs:ignore

			if ( ! class_exists( 'Sinatra_Options_Import_Export' ) ) {

				$class_options_import = plugin_dir_path( __FILE__ ) . 'importers/class-options-import-export.php';

				if ( file_exists( $class_options_import ) ) {
					require_once $class_options_import;

					Sinatra_Options_Import_Export::export();
				}
			}
		}
	}
}

/**
 * The function which returns the one Sinatra_Demo_Exporter instance.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $sinatra_demo_exporter = sinatra_demo_exporter(); ?>
 *
 * @since 1.0.0
 * @return object
 */
function sinatra_demo_exporter() {
	return Sinatra_Demo_Exporter::instance();
}

sinatra_demo_exporter();
