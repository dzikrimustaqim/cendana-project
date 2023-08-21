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
 * Sinatra Core Customizer Import/Export.
 *
 * @since 1.0.0
 * @package Sinatra Core
 */
final class Sinatra_Customizer_Import_Export {

	/**
	 * Singleton instance of the class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	private static $instance;

	/**
	 * Main Sinatra_Customizer_Import_Export Instance.
	 *
	 * @since 1.0.0
	 * @return Sinatra_Customizer_Import_Export
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Sinatra_Customizer_Import_Export ) ) {
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
	}

	/**
	 * Import customizer options.
	 *
	 * @since  1.0.0
	 * @param  object $data customizer data from the demo.
	 */
	public static function import( $data ) {

		// Have valid data?
		// If no data or could not decode.
		if ( empty( $data ) || ! is_array( $data ) ) {
			return new WP_Error( esc_html__( 'Import data could not be read. Please try a different file.', 'sinatra-core' ) );
		}

		// Hook before import.
		do_action( 'sinatra_core_before_customizer_import' );

		$data = self::remap_urls( $data );
		$data = apply_filters( 'sinatra_core_customizer_import_data', $data );

		// Theme Mods.
		if ( isset( $data['theme_mod'] ) ) {
			foreach ( $data['theme_mod'] as $id => $value ) {
				set_theme_mod( $id, $value );
			}
		}

		// Options.
		if ( isset( $data['option'] ) ) {
			foreach ( $data['option'] as $id => $value ) {
				update_option( $id, $value );
			}
		}

		// Custom CSS.
		if ( isset( $data['custom_css'] ) ) {
			wp_update_custom_css_post( $data['custom_css'] );
		}

		// Hook after import.
		do_action( 'sinatra_core_after_customizer_import' );
	}

	/**
	 * Export customizer options.
	 *
	 * @since 1.0.0
	 */
	public static function export() {

		// Export data.
		$data = array();

		// Sinatra settings.
		$customizer = array_keys( sinatra()->options->get_defaults() );

		if ( ! empty( $customizer ) ) {
			foreach ( $customizer as $id ) {

				$id = str_replace( 'sinatra_', '', $id );

				$data['theme_mod'][ 'sinatra_' . $id ] = sinatra_option( $id );
			}
		}

		// Custom CSS.
		$custom_css = wp_get_custom_css();

		if ( ! empty( $custom_css ) ) {
			$data['custom_css'] = $custom_css;
		}

		$data = apply_filters( 'sinatra_customizer_export_data', $data );
		$data = wp_json_encode( $data );

		$filesize = strlen( $data );

		// Set the download headers.
		nocache_headers();
		header( 'Content-disposition: attachment; filename=customizer.json' );
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Expires: 0' );
		header( 'Content-Length: ' . $filesize );

		// Serialize the export data.
		echo $data; // phpcs:ignore

		// Start the download.
		die();
	}

	/**
	 * Remap URLs from Customizer options to use local URLs.
	 *
	 * @since  1.0.1
	 *
	 * @param  array $data Customizer data to modify.
	 * @return array       Modified customizer data.
	 */
	public static function remap_urls( $data ) {

		// Method refers to theme_mods or options.
		foreach ( $data as $method => $options ) {

			if ( is_array( $options ) ) {
				foreach ( $options as $id => $value ) {
					if ( isset( $value['background-image'] ) ) {
						$image = (object) sinatra_demo_importer()->sideload_image( $value['background-image'] );

						if ( ! is_wp_error( $image ) ) {
							if ( isset( $image->attachment_id ) && ! empty( $image->attachment_id ) ) {
								$data[ $method ][ $id ]['background-image-id'] = $image->attachment_id;
								$data[ $method ][ $id ]['background-image']    = $image->url;
							}
						}
					}
				}
			}
		}

		return $data;
	}
}
