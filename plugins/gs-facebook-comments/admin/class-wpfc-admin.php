<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://profiles.wordpress.org/qsheeraz#content-plugins
 * @since      1.0.0
 *
 * @package    Wpfc
 * @subpackage Wpfc/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpfc
 * @subpackage Wpfc/admin
 * @author     Qamar Sheeraz <qsheeraz@yahoo.com>
 */
class Wpfc_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpfc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpfc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( "wpfc-admin.css", plugin_dir_url( __FILE__ ) . 'css/wpfc-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpfc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpfc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( "wpfc-admin.js", plugin_dir_url( __FILE__ ) . 'js/wpfc-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */

	public function wpfc_admin_menu() {

	    /*
	     * Add a settings page for this plugin to the Settings menu.
	     *
	     * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
	     *
	     *        Administration Menus: http://codex.wordpress.org/Administration_Menus
	     *
	     */

		add_menu_page( 'Facebook Comments', 'Facebook Comments', 'manage_options', 'wpfc', '', 'dashicons-facebook', 54 );
		$page_options  = add_submenu_page( 'wpfc', 'Options', 'Options', 'manage_options', $this->plugin_name, array( $this, 'display_plugin_setup_page' ) );

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */

	public function display_plugin_setup_page() {
	    include_once( 'partials/wpfc-admin-display.php' );
	}

	/**
	 * admin init function.
	 *
	 * @access public
	 * @return void
	 */		
	public function wpfc_admin_init() {
		register_setting( 'wpfc_options', 'wpfc_settings' );
	
		add_settings_section(
			'wpfc_fb_appid_section', 
			__( 'WP Facebook Comments Settings!', 'wpfc' ), 
			array($this, 'wpfc_fb_appid_section_callback'), 
			'wpfc_options'
		);

		add_settings_section(
			'wpfc_options_section', 
			'',
			array($this, 'wpfc_settings_section_callback'), 
			'wpfc_options'
		);

		add_settings_section(
			'wpfc_posts_options_section', 
			'', 
			array($this, 'wpfc_posts_section_callback'), 
			'wpfc_options'
		);

		add_settings_field( 
			'wpfc_text_comments_count', 
			__( 'Facebook App ID', 'wpfc' ), 
			array($this, 'wpfc_text_fb_appid'), 
			'wpfc_options', 
			'wpfc_fb_appid_section' 
		);

		add_settings_field( 
			'wpfc_text_comments_count', 
			__( 'Number of comments to show!', 'wpfc' ), 
			array($this, 'wpfc_text_comments_count'), 
			'wpfc_options', 
			'wpfc_options_section' 
		);

		add_settings_field( 
			'wpfc_text_data_width', 
			__( 'Width of the comments box!', 'wpfc' ), 
			array($this, 'wpfc_text_data_width'), 
			'wpfc_options', 
			'wpfc_options_section' 
		);

		add_settings_field( 
			'wpfc_option_data_colorscheme', 
			__( 'Color scheme!', 'wpfc' ), 
			array($this, 'wpfc_option_data_colorscheme'), 
			'wpfc_options', 
			'wpfc_options_section' 
		);

		add_settings_field( 
			'wpfc_option_data_orderby', 
			__( 'Order by!', 'wpfc' ), 
			array($this, 'wpfc_option_data_orderby'), 
			'wpfc_options', 
			'wpfc_options_section' 
		);

		add_settings_field( 
			'wpfc_option_data_locale', 
			__( 'Language', 'wpfc' ), 
			array($this, 'wpfc_option_data_locale'), 
			'wpfc_options', 
			'wpfc_options_section' 
		);

		add_settings_field( 
			'wpfc_checkbox_post_types', 
			__( 'Select post types to show Facebook comments!', 'wpfc' ),
			array($this, 'wpfc_checkbox_post_types'), 
			'wpfc_options', 
			'wpfc_posts_options_section' 
		);
    }	

	/**
	 * wpfc_text_fb_appid function.
	 *
	 * @access public
	 * @return void
	 */		
	function wpfc_text_fb_appid(  ) { 
		$options = get_option( 'wpfc_settings' );
		if ( !isset ( $options['wpfc_text_fb_appid'] ) )
			$options['wpfc_text_fb_appid'] = '';
		?>
		<input type='text' size='27' name='wpfc_settings[wpfc_text_fb_appid]' 
        	   value = <?php echo sanitize_text_field($options['wpfc_text_fb_appid']) ?> >
		<span class='description'>
			<?php _e( 'If you want to moderate the comments, we recommend you specify a ', 'wpfc' ) ?>
			<a href="<?php echo esc_url('https://developers.facebook.com/apps')?>" target="_new">Facebook App</a>
			<?php _e( ' ID. Please check our video tutorial about ', 'wpfc' ) ?>
			<a href="<?php echo esc_url('https://www.youtube.com/watch?v=tNZqddIUUtU')?>" target="_new"><?php _e('How to create Facebook App v2.11', 'wpfc') ?></a>
		</span></br>
		<span class='description'>
			<a href="<?php echo esc_url('https://developers.facebook.com/tools/comments/').$options['wpfc_text_fb_appid']; ?>" target="_new">
				<?php _e('Click here for App Moderation Tools!', 'wpfc') ?>
			</a>
		</span>
		<?php
	}

	/**
	 * wpfc_text_comments_count function.
	 *
	 * @access public
	 * @return void
	 */		
	function wpfc_text_comments_count(  ) { 
		$options = get_option( 'wpfc_settings' );
		if ( !isset ( $options['wpfc_text_comments_count'] ) or !intval( $options['wpfc_text_comments_count'] ) )
			$options['wpfc_text_comments_count'] = 10;
		?>
		<input type='number' min='1' max='9999' name='wpfc_settings[wpfc_text_comments_count]' 
         value = <?php echo $options['wpfc_text_comments_count'] ?> >
		<span class='description'><?php _e( 'The number of comments to show. The minimum value is 1', 'wpfc' ) ?></span>
		<?php
	}
	
	/**
	 * wpfc_text_data_width function.
	 *
	 * @access public
	 * @return void
	 */		
	function wpfc_text_data_width(  ) { 
		$options = get_option( 'wpfc_settings' );
		if ( !isset ( $options['wpfc_text_data_width'] ) )
			$options['wpfc_text_data_width'] = '';
		?>
		<input type='number' min='320' max='1500' name='wpfc_settings[wpfc_text_data_width]' 
         value = <?php echo $options['wpfc_text_data_width'] ?> >
		<span class='description'>px. <?php _e( 'Leave blank for full width.', 'wpfc' ) ?></span>
		<?php
	
	}

	/**
	 * wpfc_option_data_colorscheme function.
	 *
	 * @access public
	 * @return void
	 */		
	function wpfc_option_data_colorscheme(  ) { 
		$options = get_option( 'wpfc_settings' );
		if ( !isset ( $options['wpfc_option_data_colorscheme'] ) )
			$options['wpfc_option_data_colorscheme'] = 'light';
		?>
		<input type="radio" class="ios8-switch" id="wpfc_light" name='wpfc_settings[wpfc_option_data_colorscheme]' value='light' 
				<?php echo ($options['wpfc_option_data_colorscheme'] == 'light') ? 'checked' : '' ?> > 
				<label for="wpfc_light"><b><?php echo __( 'Light', 'wpfc' ) ?></b><br/></label>
		<span class='description'><?php //_e( 'The color scheme used by the comments box. Can be "light" or "dark".', 'wpfc' ) ?></span>
		<input type="radio" class="ios8-switch" id="wpfc_dark" name='wpfc_settings[wpfc_option_data_colorscheme]' value='dark'
        		<?php echo ($options['wpfc_option_data_colorscheme'] == 'dark') ? 'checked' : '' ?> > 
		        <label for="wpfc_dark"><b><?php echo __( 'Dark', 'wpfc' ) ?></b></label>
		<?php
	}

	/**
	 * wpfc_option_data_orderby function.
	 *
	 * @access public
	 * @return void
	 */		
	function wpfc_option_data_orderby(  ) { 
		$options = get_option( 'wpfc_settings' );
		if ( !isset ( $options['wpfc_option_data_orderby'] ) )
			$options['wpfc_option_data_orderby'] = 'social';
		?>
		<input type="radio" class="ios8-switch" id="wpfc_social" name='wpfc_settings[wpfc_option_data_orderby]' value='social' 
				<?php echo ($options['wpfc_option_data_orderby'] == 'social') ? 'checked' : '' ?> > 
				<label for="wpfc_social"><b><?php echo __( 'Top', 'wpfc' ) ?></b></label>
		<span class='description'><?php _e( 'Highest quality comments.', 'wpfc' ) ?></span><br/>
		<input type="radio" class="ios8-switch" id="wpfc_time" name='wpfc_settings[wpfc_option_data_orderby]' value='time'
        		<?php echo ($options['wpfc_option_data_orderby'] == 'time') ? 'checked' : '' ?> > 
		        <label for="wpfc_time"><b><?php echo __( 'Time', 'wpfc' ) ?></b></label>
		<span class='description'><?php _e( 'Oldest comments at the top.', 'wpfc' ) ?></span><br/>
		<input type="radio" class="ios8-switch" id="wpfc_reverse_time" name='wpfc_settings[wpfc_option_data_orderby]' value='reverse_time'
        		<?php echo ($options['wpfc_option_data_orderby'] == 'reverse_time') ? 'checked' : '' ?> > 
		        <label for="wpfc_reverse_time"><b><?php echo __( 'Reverse Time', 'wpfc' ) ?></b></label>
		<span class='description'><?php _e( 'Newest comments at the top.', 'wpfc' ) ?></span>
		<?php
	}

	/**
	 * wpfc_option_data_locale function.
	 *
	 * @access public
	 * @return void
	 */		
	function wpfc_option_data_locale(  ) { 
		$options = get_option( 'wpfc_settings' );
		if ( !isset ( $options['wpfc_option_data_locale'] ) )
			$options['wpfc_option_data_locale'] = 'vi_VN';
		?>
		<select id="country" class="selectpicker" data-width="fit" name='wpfc_settings[wpfc_option_data_locale]'>
    		<option value="en_US" data-content='<span class="flag-icon flag-icon-us"></span> English'>English</option>
  			<option value="es_ES" data-content='<span class="flag-icon flag-icon-mx"></span> Español'>Español</option>
  			<option value="fr_FR" data-content='<span class="flag-icon flag-icon-mx"></span> Français'>Français</option>
  			<option value="de_DE" data-content='<span class="flag-icon flag-icon-mx"></span> Deutsch'>Deutsch</option>
  			<option value="it_IT" data-content='<span class="flag-icon flag-icon-mx"></span> Italiano'>Italiano</option>
			<option value="vi_VN" data-content='<span class="flag-icon flag-icon-mx"></span> Vietnamese'>Tiếng Việt</option>
		
		</select>
		<span class='description'><?php _e( 'Select language.', 'wpfc' ) ?></span>

		<script type="text/javascript"><!--
			document.getElementById("country").value = "<?php echo $options['wpfc_option_data_locale']; ?>";
		//-->
        </script>

		<?php
	}

	/**
	 * wpfc_checkbox_post_types function.
	 *
	 * @access public
	 * @return void
	 */		
	function wpfc_checkbox_post_types(  ) { 
		$options = get_option( 'wpfc_settings' );
		if ( !isset ( $options['wpfc_checkbox_post_types'] ) )
			$options['wpfc_checkbox_post_types'] = array();
		
		foreach ( get_post_types( '', 'names' ) as $post_type ) {
			if ( !in_array($post_type, array('page', 'attachment') )){
				?>
				<input type='checkbox'
					   class="ios8-switch"
					   name='wpfc_settings[wpfc_checkbox_post_types][<?php echo $post_type ?>]' 
					   id = '<?php echo $post_type ?>'
					   <?php checked( isset($options['wpfc_checkbox_post_types'][$post_type]) ); ?> 
					   value='<?php echo $post_type ?> '> 
				<label for="<?php echo $post_type ?>"><b><?php echo ucwords($post_type) ?></b></label><br />
				<?php
			}
		}
	}

	/**
	 * wpfc_fb_appid_section_callback function.
	 *
	 * @access public
	 * @return void
	 */		
	function wpfc_fb_appid_section_callback(  ) { 
	
		echo '<h3 class="ws-table-title">' . __( 'Facebook Comments Moderation?', 'wpfc' ) . '</h3>';

	}

	/**
	 * wpfc_posts_section_callback function.
	 *
	 * @access public
	 * @return void
	 */		
	function wpfc_posts_section_callback(  ) { 
	
		echo '<h3 class="ws-table-title">' . __( 'Post Types for Comments!', 'wpfc' ) . '</h3>';

	    echo '<div class="woosocio-service-entry" style="font-size:18px; color:#0cca2b">';
			echo '<strong>' . __( 'Please use shortcode ', 'wpfc' ) . '</strong>';
			echo '<code>[gs-fb-comments]</code>';
			echo '<strong>' . __( ' for pages and manual comments!', 'wpfc' ) . '</strong>';
		echo '</div>';
	}	

	/**
	 * wpfc_settings_section_callback function.
	 *
	 * @access public
	 * @return void
	 */		
	function wpfc_settings_section_callback(  ) { 
	
		echo '<h3 class="ws-table-title">' . __( 'Comments Box!', 'wpfc' ) . '</h3>';

	}
}