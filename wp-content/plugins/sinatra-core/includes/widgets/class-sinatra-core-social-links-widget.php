<?php
/**
 * Sinatra Core: Social Links widget.
 *
 * @package     Sinatra Core
 * @author      Sinatra Team <hello@sinatrawp.com>
 * @since       1.0.0
 */
class Sinatra_Core_Social_Links_Widget extends WP_Widget {

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
	public function __construct() {

		// Widget defaults.
		$this->defaults = array(
			'title'    => '',
			'nav_menu' => '',
			'style'    => '',
			'align'    => '',
			'size'     => 'si-standard',
		);

		// Widget Slug.
		$widget_slug = 'sinatra-core-social-links-widget';

		// Widget basics.
		$widget_ops = array(
			'classname'   => $widget_slug,
			'description' => _x( 'Displays a list of social icon links.', 'Widget', 'sinatra-core' ),
		);

		// Widget controls.
		$control_ops = array(
			'id_base' => $widget_slug,
		);

		// Load widget.
		parent::__construct( $widget_slug, _x( '[Sinatra] Social Links', 'Widget', 'sinatra-core' ), $widget_ops, $control_ops );

	}

	/**
	 * Outputs the HTML for this widget.
	 *
	 * @since 1.0.0
	 * @param array $args An array of standard parameters for widgets in this theme.
	 * @param array $instance An array of settings for this widget instance.
	 */
	public function widget( $args, $instance ) {

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		echo wp_kses_post( $args['before_widget'] );

		do_action( 'sinatra_before_social_links_widget', $instance );

		// Title.
		if ( ! empty( $instance['title'] ) ) {
			echo wp_kses_post( $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'] );
		}

		// Widget content goes here.
		$nav_menu = ! empty( $instance['nav_menu'] ) ? wp_get_nav_menu_object( $instance['nav_menu'] ) : false;

		if ( $nav_menu && is_nav_menu( $nav_menu ) ) {

			$nav_menu_args = array(
				'menu'  => $nav_menu,
				'style' => $instance['style'],
				'align' => $instance['align'],
				'size'  => $instance['size'],
			);

			$nav_menu_args = apply_filters( 'sinatra_social_links_widget_nav_menu_args', $nav_menu_args, $nav_menu, $args, $instance );

			sinatra_social_links( $nav_menu_args );
		}

		do_action( 'sinatra_after_social_links_widget', $instance );

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
	public function update( $new_instance, $old_instance ) {

		$instance = array();

		$instance['title']    = ! empty( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['nav_menu'] = ! empty( $new_instance['nav_menu'] ) ? sanitize_text_field( $new_instance['nav_menu'] ) : '';
		$instance['style']    = ! empty( $new_instance['style'] ) ? sanitize_text_field( $new_instance['style'] ) : '';
		$instance['align']    = ! empty( $new_instance['align'] ) ? sanitize_text_field( $new_instance['align'] ) : '';
		$instance['size']     = ! empty( $new_instance['size'] ) ? sanitize_text_field( $new_instance['size'] ) : '';

		return $instance;
	}

	/**
	 * Displays the form for this widget on the Widgets page of the WP Admin area.
	 *
	 * @since 1.0.0
	 * @param array $instance An array of the current settings for this widget.
	 * @return void
	 */
	public function form( $instance ) {

		global $wp_customize;

		// Merge with defaults.
		$instance = wp_parse_args( (array) $instance, $this->defaults );

		$menus = wp_get_nav_menus();

		?>
		<div class="si-social-links-widget si-widget">
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
					<?php echo esc_html_x( 'Title:', 'Widget', 'sinatra-core' ); ?>
				</label>
				<input type="text"
						id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
						value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat"/>
			</p>

			<p>	
				<label for="<?php echo $this->get_field_id( 'nav_menu' ); ?>">
					<?php _ex( 'Menu:', 'Widget', 'sinatra-core' ); ?>
				</label>

				<select id="<?php echo $this->get_field_id( 'nav_menu' ); ?>" name="<?php echo $this->get_field_name( 'nav_menu' ); ?>">
					<option value="0"><?php _ex( '&mdash; Select &mdash;', 'Widget', 'sinatra-core' ); ?></option>
					
					<?php if ( ! empty( $menus ) ) { ?>
						<?php foreach ( $menus as $menu ) { ?>
						<option value="<?php echo esc_attr( $menu->slug ); ?>" <?php selected( $instance['nav_menu'], $menu->slug ); ?>>
							<?php echo esc_html( $menu->name ); ?>
						</option>
						<?php } ?>
					<?php } ?>
				</select>
			</p>

			<p>	
				<label for="<?php echo $this->get_field_id( 'style' ); ?>">
					<?php _ex( 'Style:', 'Widget', 'sinatra-core' ); ?>
				</label>

				<select id="<?php echo $this->get_field_id( 'style' ); ?>" name="<?php echo $this->get_field_name( 'style' ); ?>">
					<option value="minimal" <?php selected( $instance['style'], 'minimal' ); ?>><?php _ex( 'Minimal', 'Widget', 'sinatra-core' ); ?></option>
					<option value="rounded" <?php selected( $instance['style'], 'rounded' ); ?>><?php _ex( 'Rounded', 'Widget', 'sinatra-core' ); ?></option>
				</select>
			</p>

			<p>	
				<label for="<?php echo $this->get_field_id( 'style' ); ?>">
					<?php _ex( 'Align:', 'Widget', 'sinatra-core' ); ?>
				</label>

				<select id="<?php echo $this->get_field_id( 'align' ); ?>" name="<?php echo $this->get_field_name( 'align' ); ?>">
					<option value="si-flex-justify-start" <?php selected( $instance['align'], 'si-flex-justify-start' ); ?>><?php _ex( 'Left', 'Widget', 'sinatra-core' ); ?></option>
					<option value="si-flex-justify-center" <?php selected( $instance['align'], 'si-flex-justify-center' ); ?>><?php _ex( 'Center', 'Widget', 'sinatra-core' ); ?></option>
					<option value="si-flex-justify-end" <?php selected( $instance['align'], 'si-flex-justify-end' ); ?>><?php _ex( 'Right', 'Widget', 'sinatra-core' ); ?></option>
				</select>
			</p>

			<p>	
				<label for="<?php echo $this->get_field_id( 'size' ); ?>">
					<?php _ex( 'Size:', 'Widget', 'sinatra-core' ); ?>
				</label>

				<select id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>">
					<option value="si-small" <?php selected( $instance['size'], 'si-small' ); ?>><?php _ex( 'Small', 'Widget', 'sinatra-core' ); ?></option>
					<option value="si-standard" <?php selected( $instance['size'], 'si-standard' ); ?>><?php _ex( 'Standard', 'Widget', 'sinatra-core' ); ?></option>
					<option value="si-large" <?php selected( $instance['size'], 'si-large' ); ?>><?php _ex( 'Large', 'Widget', 'sinatra-core' ); ?></option>
					<option value="si-xlarge" <?php selected( $instance['size'], 'si-xlarge' ); ?>><?php _ex( 'Extra Large', 'Widget', 'sinatra-core' ); ?></option>
				</select>
			</p>

			<?php
			if ( function_exists( 'sinatra_help_link' ) ) {
				sinatra_help_link( array( 'link' => 'https://sinatrawp.com/docs/social-links-widget/' ) );
			}
			?>

		</div>
		<?php
	}
}
