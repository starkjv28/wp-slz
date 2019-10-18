<?php
/**
 * Social Snap: Social Followers Widget.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
 */
class SocialSnap_Social_Followers_Widget extends WP_Widget {

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
			'title' 			=> '',
			'networks'			=> '',
			'total_followers'	=> socialsnap_settings( 'ss_sf_total_followers' ),
			'button_followers'	=> socialsnap_settings( 'ss_sf_button_followers' ),
			'size'				=> socialsnap_settings( 'ss_sf_button_size' ),
			'spacing'			=> socialsnap_settings( 'ss_sf_button_spacing' ),
			'columns'			=> socialsnap_settings( 'ss_sf_button_columns' ),
			'vertical'			=> socialsnap_settings( 'ss_sf_button_vertical' ),
			'labels'			=> socialsnap_settings( 'ss_sf_button_labels' ),
			'scheme'			=> socialsnap_settings( 'ss_sf_button_scheme' ),
		);

		// Widget Slug.
		$widget_slug = 'socialsnap-social-followers-widget';

		// Widget basics.
		$widget_ops = array(
			'classname'   => $widget_slug,
			'description' => _x( 'Displays social follow links with follower counts.', 'Widget', 'socialsnap' ),
		);

		// Widget controls.
		$control_ops = array(
			'id_base' => $widget_slug,
		);

		// load widget
		parent::__construct( $widget_slug, _x( 'Social Snap: Social Followers', 'Widget', 'socialsnap' ), $widget_ops, $control_ops );

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

		echo $args['before_widget'];

		// Title
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		// Build social follow shortcode.
		$shortcode 	= '[ss_social_follow';

		$instance['networks'] = isset( $instance['networks'] ) ? $instance['networks'] : '';
		$instance['networks'] = preg_replace( '/ |\t/', '', $instance['networks'] );
		$instance['networks'] = preg_replace( '/\n/', ';', $instance['networks'] );
		$instance['networks'] = strtolower( $instance['networks'] );

		if ( '' != $instance['networks'] ) {
			$shortcode .= ' networks="' . $instance['networks'] . '"';
		}

		$shortcode .= ' total_followers="' . $instance['total_followers'] . '"';
		$shortcode .= ' button_followers="' . $instance['button_followers'] . '"';
		$shortcode .= ' size="' . $instance['size'] . '"';
		$shortcode .= ' spacing="' . $instance['spacing'] . '"';
		$shortcode .= ' columns="' . $instance['columns'] . '"';
		$shortcode .= ' vertical="' . $instance['vertical'] . '"';
		$shortcode .= ' scheme="' . $instance['scheme'] . '"';
		$shortcode .= ' labels="' . $instance['labels'] . '"';
		$shortcode .= ']';

		echo do_shortcode( $shortcode );

		echo $args['after_widget'];
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

		$new_instance['title'] 				= wp_strip_all_tags( $new_instance['title'] );

		$new_instance['networks']			= isset( $new_instance['networks'] ) 			? sanitize_text_field( $new_instance['networks'] ) : '';
		$new_instance['total_followers']	= isset( $new_instance['total_followers'] ) 	? '1' : false;
		$new_instance['button_followers']	= isset( $new_instance['button_followers'] ) 	? '1' : false;
		$new_instance['spacing']			= isset( $new_instance['spacing'] ) 			? '1' : false;
		$new_instance['vertical']			= isset( $new_instance['vertical'] ) 			? '1' : false;
		$new_instance['labels']				= isset( $new_instance['labels'] ) 				? '1' : false;
		$new_instance['size']				= isset( $new_instance['size'] ) 				? $new_instance['size'] 	: socialsnap_settings( 'ss_sf_button_size' );
		$new_instance['columns']			= isset( $new_instance['columns'] ) 			? $new_instance['columns'] 	: socialsnap_settings( 'ss_sf_button_columns' );
		$new_instance['scheme']				= isset( $new_instance['scheme'] ) 				? $new_instance['scheme'] 	: socialsnap_settings( 'ss_sf_button_scheme' );

		return $new_instance;
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
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">
				<?php _ex( 'Title:', 'Widget', 'socialsnap' ); ?>
			</label>
			<input type="text"
					id="<?php echo $this->get_field_id( 'title' ); ?>"
					name="<?php echo $this->get_field_name( 'title' ); ?>"
					value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat"/>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'networks' ); ?>">
				<?php _ex( 'Networks:', 'Widget', 'socialsnap' ); ?>
			</label>
			<input type="text"
					id="<?php echo $this->get_field_id( 'networks' ); ?>"
					name="<?php echo $this->get_field_name( 'networks' ); ?>"
					value="<?php echo esc_attr( $instance['networks'] ); ?>" class="widefat"/>
			<em>
				<?php _e( 'Semicolon separated list of networks.', 'socialsnap' ); ?><br/>
				<?php _e( 'Leave empty to use all configured networks.', 'socialsnap' ); ?><br/>
				<?php echo wp_kses( __( 'You can find the list of available networks <a href="https://socialsnap.com/help/features/available-social-networks/" target="_blank">here</a>.', 'socialsnap' ), socialsnap_get_allowed_html_tags() ); ?><br/>
				<?php _e( 'Example: facebook;twitter;instagram', 'socialsnap' ); ?>
			</em>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'size' ); ?>">
				<?php _ex( 'Button Size:', 'Widget', 'socialsnap' ); ?>
			</label>

			<select id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>" class="widefat">
				<option value="small" <?php selected( $instance['size'], 'small', true ); ?>><?php _ex( 'Small', 'Widget', 'socialsnap' ); ?></option>
				<option value="regular" <?php selected( $instance['size'], 'regular', true ); ?>><?php _ex( 'Regular', 'Widget', 'socialsnap' ); ?></option>
				<option value="large" <?php selected( $instance['size'], 'large', true ); ?>><?php _ex( 'Large', 'Widget', 'socialsnap' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'scheme' ); ?>">
				<?php _ex( 'Color Scheme:', 'Widget', 'socialsnap' ); ?>
			</label>

			<select id="<?php echo $this->get_field_id( 'scheme' ); ?>" name="<?php echo $this->get_field_name( 'scheme' ); ?>" class="widefat">
				<option value="network" <?php selected( $instance['scheme'], 'network', true ); ?>><?php _ex( 'Network', 'Widget', 'socialsnap' ); ?></option>
				<option value="light" <?php selected( $instance['scheme'], 'light', true ); ?>><?php _ex( 'Light', 'Widget', 'socialsnap' ); ?></option>
				<option value="dark" <?php selected( $instance['scheme'], 'dark', true ); ?>><?php _ex( 'Dark', 'Widget', 'socialsnap' ); ?></option>
			</select>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'columns' ); ?>">
				<?php _ex( 'Button Columns:', 'Widget', 'socialsnap' ); ?>
			</label>

			<select id="<?php echo $this->get_field_id( 'columns' ); ?>" name="<?php echo $this->get_field_name( 'columns' ); ?>" class="widefat">
				<option value="1" <?php selected( $instance['columns'], '1', true ); ?>><?php _ex( '1 Column', 'Widget', 'socialsnap' ); ?></option>
				<option value="2" <?php selected( $instance['columns'], '2', true ); ?>><?php _ex( '2 Column', 'Widget', 'socialsnap' ); ?></option>
				<option value="3" <?php selected( $instance['columns'], '3', true ); ?>><?php _ex( '3 Column', 'Widget', 'socialsnap' ); ?></option>
				<option value="4" <?php selected( $instance['columns'], '4', true ); ?>><?php _ex( '4 Column', 'Widget', 'socialsnap' ); ?></option>
				<option value="5" <?php selected( $instance['columns'], '5', true ); ?>><?php _ex( '5 Column', 'Widget', 'socialsnap' ); ?></option>
			</select>
		</p>

		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'spacing' ); ?>"
					name="<?php echo $this->get_field_name( 'spacing' ); ?>" <?php checked( '1', $instance['spacing'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'spacing' ); ?>"><?php _ex( 'Button Spacing', 'Widget', 'socialsnap' ); ?></label>
			<br/>

			<input type="checkbox" id="<?php echo $this->get_field_id( 'vertical' ); ?>"
					name="<?php echo $this->get_field_name( 'vertical' ); ?>" <?php checked( '1', $instance['vertical'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'vertical' ); ?>"><?php _ex( 'Vertical Layout', 'Widget', 'socialsnap' ); ?></label>
			<br/>

			<input type="checkbox" id="<?php echo $this->get_field_id( 'total_followers' ); ?>"
					name="<?php echo $this->get_field_name( 'total_followers' ); ?>" <?php checked( '1', $instance['total_followers'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'total_followers' ); ?>"><?php _ex( 'Total Followers', 'Widget', 'socialsnap' ); ?></label>
			<br/>

			<input type="checkbox" id="<?php echo $this->get_field_id( 'button_followers' ); ?>"
					name="<?php echo $this->get_field_name( 'button_followers' ); ?>" <?php checked( '1', $instance['button_followers'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'button_followers' ); ?>"><?php _ex( 'Network Followers', 'Widget', 'socialsnap' ); ?></label>
			<br/>

			<input type="checkbox" id="<?php echo $this->get_field_id( 'labels' ); ?>"
					name="<?php echo $this->get_field_name( 'labels' ); ?>" <?php checked( '1', $instance['labels'] ); ?>>
			<label for="<?php echo $this->get_field_id( 'labels' ); ?>"><?php _ex( 'Network Labels', 'Widget', 'socialsnap' ); ?></label>
			<br/>
		</p>
		<?php
	}
}

/**
 * Register Social Snap Social Followers widget.
 */
function socialsnap_register_social_followers_widget() {
	register_widget( 'SocialSnap_Social_Followers_Widget' );
}

add_action( 'widgets_init', 'socialsnap_register_social_followers_widget' );