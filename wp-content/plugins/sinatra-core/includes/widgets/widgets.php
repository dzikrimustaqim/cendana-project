<?php
/**
 * Sinatra Core - Register new widgets.
 *
 * @package     Sinatra Core
 * @author      Sinatra Team <hello@sinatrawp.com>
 * @since       1.0.0
 */

/**
 * Do not allow direct script access.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return list of available widgets.
 *
 * @since 1.0.0
 */
function sinatra_core_get_widgets() {

	$widgets = array(
		'sinatra-core-custom-list-widget'  => 'Sinatra_Core_Custom_List_Widget',
		'sinatra-core-social-links-widget' => 'Sinatra_Core_Social_Links_Widget',
		'sinatra-core-posts-list-widget'   => 'Sinatra_Core_Posts_List_Widget',
	);

	return apply_filters( 'sinatra_core_widgets', $widgets );
}

/**
 * Register widgets.
 *
 * @since 1.0.0
 */
function sinatra_core_register_widgets() {

	// Get available widgets.
	$widgets = sinatra_core_get_widgets();

	if ( empty( $widgets ) ) {
		return;
	}

	// Path to widgets folder.
	$path = SINATRA_CORE_PLUGIN_DIR . 'includes/widgets';

	// Register widgets.
	foreach ( $widgets as $key => $value ) {

		// Include class and register widget.
		$widget_path = $path . '/class-' . $key . '.php';

		if ( file_exists( $widget_path ) ) {
			require_once $widget_path;
			register_widget( $value );
		}
	}
}
add_action( 'widgets_init', 'sinatra_core_register_widgets' );

/**
 * Enqueue admin styles.
 *
 * @since 1.0.0
 */
function sinatra_core_widgets_enqueue( $page ) {

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	wp_enqueue_style(
		'sinatra-admin-widgets-css',
		SINATRA_CORE_PLUGIN_URL . 'assets/css/admin-widgets' . $suffix . '.css',
		SINATRA_CORE_VERSION,
		true
	);

	wp_enqueue_script(
		'sinatra-admin-widgets-js',
		SINATRA_CORE_PLUGIN_URL . 'assets/js/admin-widgets.min.js',
		array( 'jquery' ),
		SINATRA_CORE_VERSION,
		true
	);
}
add_action( 'admin_print_footer_scripts-widgets.php', 'sinatra_core_widgets_enqueue' );

/**
 * Enqueue front styles.
 *
 * @since 1.0.0
 */
function sinatra_core_enqueue_widget_assets() {

	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

	$widgets = sinatra_core_get_widgets();

	if ( is_array( $widgets ) ) {
		foreach ( $widgets as $id_slug => $class ) {
			if ( is_active_widget( false, false, $id_slug, true ) ) {

				wp_enqueue_style(
					'sinatra-core-widget-styles',
					SINATRA_CORE_PLUGIN_URL . 'assets/css/widgets' . $suffix . '.css',
					false,
					SINATRA_CORE_VERSION,
					'all'
				);
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'sinatra_core_enqueue_widget_assets' );



/**
 * Print repeatable template.
 *
 * @since  1.0.0
 * @return void
 */
function sinatra_core_print_widget_templates() {
	?>
	<script type="text/template" id="tmpl-sinatra-core-repeatable-item">
		<div class="si-repeatable-item open">

			<div class="si-repeatable-item-title">
				<?php echo esc_attr_x( 'New Item', 'Widget', 'sinatra-core' ); ?>

				<div class="si-repeatable-indicator">
					<span class="accordion-section-title" aria-hidden="true"></span>
				</div>

			</div>

			<div class="si-repeatable-item-content">

				<p>
					<label for="{{data.id}}-{{data.index}}-icon">
						<?php echo esc_attr_x( 'Icon', 'Widget', 'sinatra-core' ); ?>
					</label>

					<textarea class="widefat" id="{{data.id}}-{{data.index}}-icon" name="{{data.name}}[{{data.index}}][icon]" rows="3"></textarea>
				</p>
				
				<p>
					<label for="{{data.id}}-{{data.index}}-description">
						<?php echo esc_attr_x( 'Item Description', 'Widget', 'sinatra-core' ); ?>
					</label>
					<textarea class="widefat" id="{{data.id}}-{{data.index}}-description" name="{{data.name}}[{{data.index}}][description]" rows="3"></textarea>
					<em class="description si-description">
						<?php
						echo wp_kses_post(
							sprintf(
								_x( 'HTML tags and %1$sdynamic strings%2$s allowed.', 'Widget', 'sinatra-core' ),
								'<a href="https://sinatrawp.com/docs/sinatra-dynamic-strings/" rel="nofollow noreferrer" target="_blank">',
								'</a>'
							)
						);
						?>
					</em>
				</p>

				<p>
					<input type="checkbox" id="{{data.name}}[{{data.index}}][separator]" name="{{data.name}}[{{data.index}}][separator]" />
					<label for="{{data.name}}[{{data.index}}][separator]"><?php _ex( 'Add bottom separator', 'Widget', 'sinatra-core' ); ?></label>
				</p>

				<button type="button" class="remove-repeatable-item button-link button-link-delete"><?php _ex( 'Remove', 'Widget', 'sinatra-core' ); ?></button>
			</div>
		</div>
	</script>
	<?php
}
add_action( 'admin_print_footer_scripts-widgets.php', 'sinatra_core_print_widget_templates' );
