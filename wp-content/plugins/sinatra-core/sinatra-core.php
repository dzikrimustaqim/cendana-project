<?php
/**
 * Plugin Name: Sinatra Core
 * Plugin URI:  https://sinatrawp.com
 * Description: Additional features for Sinatra WordPress Theme.
 * Author:      Sinatra
 * Author URI:  https://sinatrawp.com
 * Version:     1.0.4
 * Text Domain: sinatra-core
 * Domain Path: languages
 *
 * Sinatra Core is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Sinatra Core is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Social Snap. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    Sinatra Core
 * @author     Sinatra Team <hello@sinatrawp.com>
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Sinatra
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't allow multiple versions to be active.
if ( ! class_exists( 'Sinatra_Core' ) ) {

	/**
	 * Main Sinatra Core class.
	 *
	 * @since 1.0.0
	 * @package Sinatra Core
	 */
	final class Sinatra_Core {

		/**
		 * Singleton instance of the class.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		private static $instance;

		/**
		 * Plugin version for enqueueing, etc.
		 *
		 * @since 1.0.0
		 * @var sting
		 */
		public $version = '1.0.4';

		/**
		 * Main Sinatra Core Instance.
		 *
		 * Insures that only one instance of Sinatra Core exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0.0
		 * @return Sinatra_Core
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Sinatra_Core ) ) {

				self::$instance = new Sinatra_Core();
				self::$instance->constants();
				self::$instance->load_textdomain();
				self::$instance->includes();
				self::$instance->objects();

				add_action( 'plugins_loaded', array( self::$instance, 'objects' ), 10 );
			}

			return self::$instance;
		}

		/**
		 * Setup plugin constants.
		 *
		 * @since 1.0.0
		 */
		private function constants() {

			// Plugin version.
			if ( ! defined( 'SINATRA_CORE_VERSION' ) ) {
				define( 'SINATRA_CORE_VERSION', $this->version );
			}

			// Plugin Folder Path.
			if ( ! defined( 'SINATRA_CORE_PLUGIN_DIR' ) ) {
				define( 'SINATRA_CORE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL.
			if ( ! defined( 'SINATRA_CORE_PLUGIN_URL' ) ) {
				define( 'SINATRA_CORE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File.
			if ( ! defined( 'SINATRA_CORE_PLUGIN_FILE' ) ) {
				define( 'SINATRA_CORE_PLUGIN_FILE', __FILE__ );
			}
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @since 1.0.0
		 */
		public function load_textdomain() {

			load_plugin_textdomain( 'sinatra-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Include files.
		 *
		 * @since 1.0.0
		 */
		private function includes() {

			// Global includes.
			require_once SINATRA_CORE_PLUGIN_DIR . 'includes/widgets/widgets.php';

			require_once SINATRA_CORE_PLUGIN_DIR . 'includes/admin/class-sinatra-core-admin.php';

			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				require_once SINATRA_CORE_PLUGIN_DIR . 'includes/cli/class-sinatra-core-cli.php';
			}
		}

		/**
		 * Setup objects to be used throughout the plugin.
		 *
		 * @since 1.0.0
		 */
		public function objects() {

			// Hook now that all of the Sinatra Core stuff is loaded.
			do_action( 'sinatra_core_loaded' );
		}
	}

	/**
	 * The function which returns the one Sinatra_Core instance.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php $sinatra_core = sinatra_core(); ?>
	 *
	 * @since 1.0.0
	 * @return object
	 */
	function sinatra_core() {
		return Sinatra_Core::instance();
	}

	$theme = wp_get_theme();

	if ( 'Sinatra' === $theme->name || 'sinatra' === $theme->template ) {
		sinatra_core();
	} else {
		add_action( 'admin_notices', 'sinatra_core_theme_notice' );
	}

	/**
	 * Display notice.
	 *
	 * @since 1.0.0
	 */
	function sinatra_core_theme_notice() {
		echo '<div class="notice notice-warning"><p>' . __( 'Please activate Sinatra Theme before activating Sinatra Core.', 'sinatra-core' ) . '</p></div>';
	}
}
