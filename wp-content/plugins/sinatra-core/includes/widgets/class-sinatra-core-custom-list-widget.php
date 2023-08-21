<?php
/**
 * Sinatra Core Widget: Custom List.
 *
 * @package Sinatra Core
 * @author  Sinatra Team <hello@sinatrawp.com>
 * @since   1.0.0
 */
class Sinatra_Core_Custom_List_Widget extends WP_Widget {

	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $defaults;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {

		// Widget defaults.
		$this->defaults = array(
			'title'   => '',
			'content' => '',
			'items'   => array(),
		);

		// Widget Slug.
		$widget_slug = 'sinatra-core-custom-list-widget';

		// Widget basics.
		$widget_ops = array(
			'classname'   => $widget_slug,
			'description' => _x( 'A list of items with optional icon and separator.', 'Widget', 'sinatra' ),
		);

		// Widget controls.
		$control_ops = array(
			'id_base'      => $widget_slug,
		);

		// load widget
		parent::__construct( $widget_slug, _x( '[Sinatra] Custom List', 'Widget', 'sinatra' ), $widget_ops, $control_ops );

		// Hook into dynamic styles.
		add_filter( 'sinatra_dynamic_styles', array( $this, 'dynamic_styles' ) );
	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @since 1.0.0
	 * @param array $args An array of standard parameters for widgets in this theme.
	 * @param array $instance An array of settings for this widget instance.
	 */
	function widget( $args, $instance ) {

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo wp_kses_post( $args['before_widget'] );

		do_action( 'sinatra_before_custom_list_widget', $instance );

		// Title.
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		if ( ! empty( $instance['content'] ) ) {
			$instance['content'] = apply_filters( 'sinatra_dynamic_strings', $instance['content'] );
			echo '<div class="si-custom-list-widget-desc">' . wp_kses_post( wpautop( $instance['content'], true ) ) . '</div>';
		}

		if ( ! empty( $instance['items'] ) ) {

			echo '<div class="si-custom-list-widget-items">';

			foreach ( $instance['items'] as $entry ) {

				$separator_class = $entry['separator'] ? 'si-clw-sep ' : '';

				echo '<div class="' . $separator_class . 'si-custom-list-widget-item">';

				if ( $entry['icon'] ) {

					$entry['icon'] = $this->process_icon( $entry['icon'] );

					if ( false !== strpos( $entry['icon'], '<svg' ) ) {
						echo wp_kses( $entry['icon'], sinatra_get_allowed_html_tags( 'svg' ) );
					} else {
						echo '<i class="si-widget-icon ' . esc_attr( $entry['icon'] ) . '" aria-hidden="true"></i>';
					}
				}

				if ( $entry['description'] ) {
					echo '<span class="si-entry">' . wp_kses_post( nl2br( $entry['description'] ) ) . '</span>';
				}

				echo '</div>';
			}

			echo '</div>';
		}

		do_action( 'sinatra_after_custom_list_widget', $instance );

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Deals with the settings when they are saved by the admin. Here is
	 * where any validation should be dealt with.
	 *
	 * @since 1.0.0
	 * @param array $new_instance An array of new settings as submitted by the admin.
	 * @param array $old_instance An array of the previous settings.
	 * @return array The validated and (if necessary) amended settings
	 */
	function update( $new_instance, $old_instance ) {

		$instance            = array();
		$instance['title']   = wp_strip_all_tags( $new_instance['title'] );
		$instance['content'] = isset( $new_instance['content'] ) ? wp_kses_post( $new_instance['content'] ) : '';
		$instance['items']   = array();

		if ( isset( $new_instance['items'] ) ) {
			foreach ( $new_instance['items'] as $entry ) {

				// Sanitize entry values.
				$new_entry = array(
					'icon'        => '',
					'description' => isset( $entry['description'] ) ? wp_kses_post( trim( $entry['description'] ) ) : '',
					'separator'   => isset( $entry['separator'] ) && $entry['separator'] ? true : false,
				);

				if ( isset( $entry['icon'] ) ) {
					$new_entry['icon'] = wp_kses( $this->process_icon( $entry['icon'] ), sinatra_get_allowed_html_tags( 'svg' ) );
				}


				if ( ! empty( $new_entry['icon'] ) || ! empty( $new_entry['description'] ) || true === $new_entry['separator'] ) {
					$instance['items'][] = $new_entry;
				}
			}
		}

		return $instance;
	}

	/**
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 *
	 * @since 1.0.0
	 * @param array $instance An array of the current settings for this widget.
	 * @return void
	 */
	function form( $instance ) {

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$empty    = empty( $instance['items'] ) ? ' empty' : '';
		?>

		<div class="si-repeatable-widget si-custom-list-widget si-widget">
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>">
					<?php _ex( 'Title:', 'Widget', 'sinatra' ); ?>
				</label>
				<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat"/>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'content' ); ?>">
					<?php _ex( 'Text Before:', 'Widget', 'sinatra' ); ?>
				</label>
				
				<textarea class="widefat" id="<?php echo $this->get_field_id( 'content' ); ?>" name="<?php echo $this->get_field_name( 'content' ); ?>" rows="3"><?php echo $instance['content']; ?></textarea>

				<em class="description si-description">
					<?php
					echo wp_kses_post(
						sprintf(
							_x( 'HTML tags and %1$sdynamic strings%2$s allowed.', 'Widget', 'sinatra' ),
							'<a href="https://sinatrawp.com/docs/sinatra-dynamic-strings/" rel="nofollow noreferrer" target="_blank">',
							'</a>'
						)
					);
					?>
				</em>
			</p>

			<div class="si-repeatable-container<?php echo esc_attr( $empty ); ?>">

				<?php
				if ( ! empty( $instance['items'] ) ) {
					foreach ( $instance['items'] as $index => $entry ) {
						?>
						<div class="si-repeatable-item">
							
							<!-- Repeatable title -->
							<div class="si-repeatable-item-title">
								<?php
								_ex( 'List Item', 'Widget', 'sinatra' );

								if ( ! empty( $entry['description'] ) ) {
									echo ': <span class="in-widget-title">' . esc_html( wp_trim_words( $entry['description'], 2, '...' ) ) . '</span>';
								}
								?>

								<div class="si-repeatable-indicator">
									<span class="accordion-section-title" aria-hidden="true"></span>
								</div>
							</div>
							
							<!-- Repeatable content -->
							<div class="si-repeatable-item-content">
								
								<p>
									<label for="<?php echo $this->get_field_id( 'items' ) . '-' . $index . '-icon'; ?>">
										<?php _ex( 'Icon', 'Widget', 'sinatra' ); ?>
									</label>
									
									<textarea class="widefat" id="<?php echo $this->get_field_id( 'icon' ) . '-' . $index . '-icon'; ?>" name="<?php echo $this->get_field_name( 'items' ); ?>[<?php echo $index; ?>][icon]" rows="3"><?php echo $entry['icon']; ?></textarea>
									<em class="description si-description">
										<?php echo wp_kses_post( _x( 'Enter icon SVG code.', 'Widget', 'sinatra' ) ); ?>
									</em>
								</p>
								
								<p>
									<label for="<?php echo $this->get_field_id( 'items' ) . '-' . $index . '-description'; ?>">
										<?php _ex( 'Item Description', 'Widget', 'sinatra' ); ?>
									</label>
									<textarea class="widefat" id="<?php echo $this->get_field_id( 'items' ) . '-' . $index . '-description'; ?>" name="<?php echo $this->get_field_name( 'items' ); ?>[<?php echo $index; ?>][description]" rows="3"><?php echo $entry['description']; ?></textarea>
									<em class="description si-description">
										<?php
										echo wp_kses_post(
											sprintf(
												_x( 'HTML tags and %1$sdynamic strings%2$s allowed.', 'Widget', 'sinatra' ),
												'<a href="https://sinatrawp.com/docs/sinatra-dynamic-strings/" rel="nofollow noreferrer" target="_blank">',
												'</a>'
											)
										);
										?>
									</em>
								</p>

								<p>
									<input type="checkbox" id="<?php echo $this->get_field_name( 'items' ); ?>[<?php echo $index; ?>][separator]" name="<?php echo $this->get_field_name( 'items' ); ?>[<?php echo $index; ?>][separator]" <?php checked( true, $entry['separator'] ); ?>/>
									<label for="<?php echo $this->get_field_name( 'items' ); ?>[<?php echo $index; ?>][separator]"><?php _ex( 'Add bottom separator', 'Widget', 'sinatra' ); ?></label>
								</p>

								<!-- Remove -->
								<button type="button" class="remove-repeatable-item button-link button-link-delete"><?php _ex( 'Remove', 'Widget', 'sinatra' ); ?></button>
							</div>

						</div>
						<?php
					}
				}
				?>

				<div class="si-svg-icon si-hide-if-not-empty">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="8"></line></svg>
				</div>

				<h5 class="si-hide-if-not-empty">
					<?php _ex( 'No Items Found', 'Widget', 'sinatra' ); ?>
				</h5>

				<p class="si-hide-if-not-empty">
					<?php _ex( 'Please add new items to see more options', 'Widget', 'sinatra' ); ?>
				</p>

				<div class="si-repeatable-footer">
					<a href="#" class="button secondary add-new-item" data-index="<?php echo intval( count( $instance['items'] ) ); ?>" data-widget-name="<?php echo $this->get_field_name( 'items' ); ?>" data-widget-id="<?php echo $this->get_field_id( 'items' ); ?>"><?php esc_html_e( 'Add New', 'sinatra' ); ?></a>
				</div>
			</div>
			<!-- END .si-repeatable-container -->

			<?php
			if ( function_exists( 'sinatra_help_link' ) ) {
				sinatra_help_link(
					array(
						'link' => 'https://sinatrawp.com/docs/custom-list-widget/',
					)
				);
			}
			?>

		</div>
		<!-- END .si-custom-list-widget -->

		<?php
	}

	/**
	 * Hook into Sinatra dynamic styles.
	 *
	 * @param  string $css Generated CSS code.
	 * @return string Modified CSS code.
	 */
	function dynamic_styles( $css ) {
		$css .= '.sinatra-core-custom-list-widget .si-icon, .sinatra-core-custom-list-widget svg {
			fill: ' . sinatra_option( 'accent_color' ) . ';
			color: ' . sinatra_option( 'accent_color' ) . ';
		}';

		return $css;
	}

	function process_icon( $icon ) {

		// Icon is not an SVG.
		if ( false === strpos( $icon, '<svg' ) ) {

			if ( version_compare( SINATRA_THEME_VERSION, '1.2.0', '>=' ) ) {

				$_icon = trim( str_replace( 'si-icon', '', $icon ) );
				$_icon = trim( str_replace( 'si-', '', $_icon ) );

				$svg_icon = sinatra()->icons->get_svg( $_icon );

				if ( $svg_icon ) {
					$icon = $svg_icon;
				} elseif ( file_exists( SINATRA_CORE_PLUGIN_DIR . '/assets/svg/' . $_icon . '.svg'  ) ) {
					$icon = file_get_contents( SINATRA_CORE_PLUGIN_DIR . '/assets/svg/' . $_icon . '.svg' );
				}
			}
		}

		return $icon;
	}
}
