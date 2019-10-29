<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://profiles.wordpress.org/qsheeraz#content-plugins
 * @since      1.0.0
 *
 * @package    Wpfc
 * @subpackage Wpfc/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wpfc
 * @subpackage Wpfc/public
 * @author     Qamar Sheeraz <qsheeraz@yahoo.com>
 */
class Wpfc_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wpfc-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wpfc-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * add meta property function.
	 *
	 * @access public
	 * @return void
	 */
	public function wpfc_add_meta_property() {
		$options = get_option( 'wpfc_settings' );
		if ( isset ( $options['wpfc_text_fb_appid'] ) and $options['wpfc_text_fb_appid'] != '' )
			echo '<meta property="fb:app_id" content="'.sanitize_text_field($options['wpfc_text_fb_appid']).'" />';
	}

	/**
	 * Facebook_js_sdk function.
	 *
	 * @access public
	 * @return void
	 */
	public function wpfc_add_facebook_js_sdk() {
		
		$options = get_option( 'wpfc_settings' );
		if ( !isset ( $options['wpfc_option_data_locale'] ) )
			$options['wpfc_option_data_locale'] = 'vi_VN';

		if ( !isset ( $options['wpfc_text_fb_appid'] ) or  $options['wpfc_text_fb_appid'] == '' )
			$fb_appid = '';
		else
			$fb_appid = '&appId=' . $options['wpfc_text_fb_appid'];

	  	?>
	  	<div id="fb-root"></div>
	  	<script async defer>
			(function(d, s, id) {
			  var js, fjs = d.getElementsByTagName(s)[0];
			  if (d.getElementById(id)) {return;}
			  js = d.createElement(s); js.id = id;
			  js.src = 'https://connect.facebook.net/<?php echo sanitize_text_field($options['wpfc_option_data_locale']) ?>/sdk.js#xfbml=1&version=v2.10<?php echo $fb_appid; ?>';
			  fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));//-->
	  	</script>
	  	<?php
	}

	/**
	 * show Facebook comments function.
	 *
	 * @access public
	 * @return $content
	 */
	public function wpfc_show_facebook_comments($content) {
		$post_id = get_the_ID();
		$options = get_option( 'wpfc_settings' );

		if( isset($options['wpfc_checkbox_post_types'][get_post_type( $post_id )]) != '' ){
			
			if ( !isset ( $options['wpfc_text_comments_count'] ) or !intval( $options['wpfc_text_comments_count'] ) )
				$options['wpfc_text_comments_count'] = 10;
			$data_numposts = (int)$options['wpfc_text_comments_count'];

			if ( !isset ( $options['wpfc_text_data_width'] ) or $options['wpfc_text_data_width'] == '' )
				$data_width = $options['wpfc_text_data_width'] = '100%';
			else
				$data_width = (int)$options['wpfc_text_data_width'] . 'px';

			if ( !isset ( $options['wpfc_option_data_colorscheme'] ) )
				$options['wpfc_option_data_colorscheme'] = 'light';
			$data_colorscheme = $options['wpfc_option_data_colorscheme'];

			if ( !isset ( $options['wpfc_option_data_orderby'] ) )
				$options['wpfc_option_data_orderby'] = 'social';
			$data_orderby = $options['wpfc_option_data_orderby'];

			if ( !isset ( $options['wpfc_text_fb_appid'] ) or  $options['wpfc_text_fb_appid'] == '' )
				$fb_appid = '';
			else
				$fb_appid = '&appId=' . $options['wpfc_text_fb_appid'];

			$socio_link = get_permalink( $post_id );
			/* iframe old			
			 <style>
			.fb_iframe_widget_fluid_desktop iframe {
			    width: '.$data_width.' !important;
			}
		  </style>
			*/
		    $comments_div = '
			  <div 
			  	class = "fb-comments" 
			  	data-href = "' . esc_url($socio_link) . '"
			  	data-numposts = "' . (int)$data_numposts . '"
				data-colorscheme = "' . sanitize_text_field($data_colorscheme) . '"
				data-order-by = "' . sanitize_text_field($data_orderby) . '"
				data-mobile=true>
			  </div>
		  <style>
			.fb_iframe_widget_fluid_desktop iframe {
			    width: 100% !important;
			}
		  </style>
		  ';

			return $content.=$comments_div;
		}
		else 
			return $content;
	}

	/**
	 * show shortcode comments function.
	 *
	 * @access public
	 * @return $content
	 */
	public function wpfc_show_facebook_comments_shortcode($content) {
		$post_id = get_the_ID();
		$options = get_option( 'wpfc_settings' );

		if ( !isset ( $options['wpfc_text_comments_count'] ) or !intval( $options['wpfc_text_comments_count'] ) )
			$options['wpfc_text_comments_count'] = 10;
		$data_numposts = (int)$options['wpfc_text_comments_count'];

		if ( !isset ( $options['wpfc_text_data_width'] ) or $options['wpfc_text_data_width'] == '' )
			$data_width = $options['wpfc_text_data_width'] = '100%';
		else
			$data_width = (int)$options['wpfc_text_data_width'] . 'px';

		if ( !isset ( $options['wpfc_option_data_colorscheme'] ) )
			$options['wpfc_option_data_colorscheme'] = 'light';
		$data_colorscheme = $options['wpfc_option_data_colorscheme'];

		if ( !isset ( $options['wpfc_option_data_orderby'] ) )
			$options['wpfc_option_data_orderby'] = 'social';
		$data_orderby = $options['wpfc_option_data_orderby'];

		if ( !isset ( $options['wpfc_text_fb_appid'] ) or  $options['wpfc_text_fb_appid'] == '' )
			$fb_appid = '';
		else
			$fb_appid = '&appId=' . $options['wpfc_text_fb_appid'];

		$socio_link = get_permalink( $post_id );
		
	    $comments_div = '
		  <div 
		  	class = "fb-comments" 
		  	data-href = "' . esc_url($socio_link). '"
		  	data-numposts = "' . (int)$data_numposts . '"
			data-colorscheme = "' . sanitize_text_field($data_colorscheme) . '"
			data-order-by = "' . sanitize_text_field($data_orderby) . '"
			data-mobile=true>
		  </div>
		  <style>
			.fb_iframe_widget_fluid_desktop iframe {
			    width: 100% !important;
			}
		  </style>
		  ';

		return $content.=$comments_div;
	}
}



