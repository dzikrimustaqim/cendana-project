<?php
/**
 * Sinatra Core Import CLI Command class file.
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
 * Allows import via CLI.
 *
 * @version 1.0.0
 * @package Sinatra Core
 */
class Sinatra_Core_CLI_Import {

	/**
	 * Registers the import command.
	 *
	 * @since 1.0.0
	 */
	public static function register_commands() {

		// Import demo command.
		WP_CLI::add_command(
			'sinatra import demo',
			array( 'Sinatra_Core_CLI_Import', 'import' ),
			array(
				'shortdesc' => __( 'Imports demo content', 'sinatra-core' ),
				'synopsis'  => array(
					array(
						'type'        => 'positional',
						'name'        => 'demo-slug',
						'description' => 'Sanitized title of the demo to import.',
						'optional'    => false,
						'repeating'   => false,
					),
				),
			)
		);

		// Import demo command.
		WP_CLI::add_command(
			'sinatra import page',
			array( 'Sinatra_Core_CLI_Import', 'import_page' ),
			array(
				'shortdesc' => __( 'Imports a demo page', 'sinatra-core' ),
				'synopsis'  => array(
					array(
						'type'        => 'associative',
						'name'        => 'xml-file',
						'description' => 'Sanitized title of the page to import.',
						'optional'    => false,
						'repeating'   => false,
					),
				),
			)
		);
	}

	/**
	 * Runs Import command.
	 *
	 * @since 1.0.0
	 */
	public static function import( $args, $assoc_args ) {

		$demo_slug = sanitize_title( $args[0] );

		// Load Demo Library class.
		if ( ! function_exists( 'sinatra_demo_library' ) ) {

			$class = SINATRA_CORE_PLUGIN_DIR . 'includes/admin/demo-library/class-sinatra-demo-library.php';

			if ( file_exists( $class ) ) {
				require_once $class;
			} else {
				WP_CLI::error( 'missing file' );
				return;
			}
		}

		$demos = sinatra_demo_library()->get_templates();

		// Check if demo exists.
		if ( ! array_key_exists( $demo_slug, $demos ) ) {

			WP_CLI::error_multi_line(
				array(
					/* translators: %s Demo Slug */
					sprintf( __( '%s demo slug is not valid.', 'sinatra-core' ), $demo_slug ),
				)
			);

			WP_CLI\Utils\format_items( 'table', $demos, array( 'name', 'slug', 'url' ) );
			return;
		}

		$demo = $demos[ $demo_slug ];

		// Install & Activate required plugins.
		if ( isset( $demo['plugins'] ) && ! empty( $demo['plugins'] ) ) {
			foreach ( $demo['plugins'] as $plugin ) {
				WP_CLI::runcommand( 'plugin install ' . $plugin['slug'] );
				WP_CLI::runcommand( 'plugin activate ' . $plugin['slug'] );
			}
		}

		// Configure import paths.
		$response = sinatra_demo_importer()->configure_paths( $demo_slug );

		if ( is_wp_error( $response ) ) {
			WP_CLI::error( $response->get_error_message() );
			return;
		}

		foreach ( self::get_import_steps( $demo ) as $step ) {

			/* translators: %s import step */
			WP_CLI::log( sprintf( __( 'Importing %s...', 'sinatra-core' ), $step ) );

			$args = array();

			if ( 'content' === $step ) {
				$args = array(
					// should we import attachments?
					true,
				);
			}

			// Call import step function.
			$response = call_user_func_array( array( sinatra_demo_importer(), 'import_' . $step ), $args );

			if ( is_wp_error( $response ) ) {
				WP_CLI::warning( $response->get_error_message() );
			}
		}

		WP_CLI::log( sprintf( __( 'Demo import complete.', 'sinatra-core' ), $step ) );
	}

	/**
	 * Import steps array.
	 *
	 * @since 1.0.0
	 *
	 * @param array $demo Array of demo details.
	 */
	public static function get_import_steps( $demo = '' ) {

		$steps = array(
			'customizer',
			'wpforms',
			'content',
			'widgets',
			'options',
		);

		return $steps;
	}

	/**
	 * Import a demo page.
	 *
	 * @since 1.0.2
	 */
	public static function import_page( $args, $assoc_args ) {

		$xml_file = isset( $assoc_args['xml-file'] ) ? $assoc_args['xml-file'] : false;

		if ( ! $xml_file ) {
			WP_CLI::error( '--xml-file="<path>" required' );
			return;
		}

		$attachments = isset( $assoc_args['attachments'] ) ? boolval( $assoc_args['attachments'] ) : true;

		// Load Demo Library class.
		if ( ! function_exists( 'sinatra_demo_importer' ) ) {

			$class = SINATRA_CORE_PLUGIN_DIR . 'includes/admin/demo-library/class-sinatra-demo-importer.php';

			if ( file_exists( $class ) ) {
				require_once $class;
			} else {
				WP_CLI::error( 'missing file' );
				return;
			}
		}

		if ( ! is_file( $xml_file ) ) {

			// Get upload dir.
			$upload_dir = wp_upload_dir();

			// Check upload folder permission.
			if ( ! wp_is_writable( trailingslashit( $upload_dir['basedir'] ) ) ) {
				WP_CLI::error( __( 'Upload folder not writable.', 'sinatra-core' ) );
				return;
			}

			$to = trailingslashit( $upload_dir['basedir'] ) . 'sinatra/pages/' . basename( $xml_file );

			$xml_file = sinatra_demo_importer()->download( $xml_file, $to );

			if ( is_wp_error( $xml_file ) ) {
				WP_CLI::error( $xml_file->get_error_message() );
				return;
			}
		}

		$import = sinatra_demo_importer()->process_import_content( $xml_file, true );

		if ( is_wp_error( $import ) ) {
			WP_CLI::error( $import->get_error_message() );
			return;
		}

		WP_CLI::success( __( 'Page imported.', 'sinatra-core' ) );
	}
}
