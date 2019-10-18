<?php
/**
 * Social Sharing. Class generates sharing buttons.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
*/
class SocialSnap_Social_Share {

	/**
	 * Social Networks array.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $networks;

	/**
	 * Display Positions of share bar.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $positions;

	/**
	 * Indicator to check if share buttons are placed on the page.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $is_displayed = false;

	/**
	 * Indicator to check if share counts are displayed.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $counts_displayed = false;

	/**
	 * Indicator to check if share all is displayed.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $share_all_popup_displayed = false;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( is_admin() ) {
			add_action( 'init', array( $this, 'init'), 20 );
			add_action( 'init', array( $this, 'display_positions' ), 20 );
			add_action( 'init', array( $this, 'redirect' ), 999 );
		} else {
			add_action( 'wp', array( $this, 'init'), 20 );
			add_action( 'wp', array( $this, 'display_positions' ), 20 );
		}

		// Register a shortcode for Social Share.
		add_shortcode( 'ss_social_share', array( $this, 'register_shortcodes' ) );

		// Add support for Block Editor.
		add_action( 'plugins_loaded', array( $this, 'block_editor_support' ) );

		// Refresh share count cache.
		add_action( 'wp_ajax_socialsnap_ss_cache_refresh', array( $this, 'socialsnap_refresh_share_count_cache' ) );

		// Add settings.
		add_filter( 'socialsnap_settings_config', array( $this, 'add_settings_config' ) );
	}

	/**
	 * Initialize class variables.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Social networks.
		$this->networks = apply_filters( 
			'socialsnap_filter_social_share_networks', 
			socialsnap_settings( 'ss_social_share_networks' ) 
		);

		// Share buttons positions.
		$this->positions = socialsnap_get_social_share_positions();

		// Crate marker that shares have expired and to fetch new.
		if ( ! is_admin() ) {
			add_action( 'wp_footer', array( $this, 'share_count_cache_expired' ), 99 );
		}

		// Facebook App authorized for share count.
		if ( isset( $_GET['ss_network_authorized'], $_GET['access_token'], $_GET['expires_in'] ) && 'facebook_shares' === $_GET['ss_network_authorized'] ) {

			if ( 'never' !== $_GET['expires_in'] ) {
				
				set_transient(
					'ss_facebook_token',
					array(
						'access_token' => sanitize_text_field( $_GET['access_token'] ),
						'expires_in'   => time() + intval( $_GET['expires_in'] )
					),
					intval( $_GET['expires_in'] ) + MONTH_IN_SECONDS
				);

			} else {

				set_transient(
					'ss_facebook_token',
					array(
						'access_token' => sanitize_text_field( $_GET['access_token'] ),
						'expires_in'   => 'never'
					)
				);
			}
			
		}
	}

	/**
	 * Redirect after authorization.
	 *
	 * @since 1.0.0
	 */
	public function redirect() {

		// Redirect to the same page without the authorization parameters.
		if ( isset( $_GET['ss_network_authorized'], $_GET['access_token'], $_GET['expires_in'] ) && 'facebook_shares' === $_GET['ss_network_authorized'] ) {

			wp_redirect( add_query_arg( array( 'page' => 'socialsnap-settings#ss_social_share_networks_display-ss' ), admin_url( 'admin.php' ) ) );
			exit;
		}
	}

	/**
	 * Display Social Sharing buttons on enabled positions in the settings panel.
	 *
	 * @since 1.0.0
	 */
	public function display_positions() {

		// No positions are enabled.
		if ( ! is_array( $this->positions ) || empty( $this->positions ) ) {
			return;
		}

		// Check AMP pages.
		if ( socialsnap_is_amp_page() ) {
			return;
		}

		add_filter( 'socialsnap_ss_button_data', array( $this, 'add_share_button_api_data' ), 10, 6 );
		add_filter( 'socialsnap_display_position_classes', array( $this, 'get_display_position_classes' ), 10, 3 );

		// All networks popup.
		add_action( 'wp_footer', array( $this, 'render_share_all_popup' ), 20 );
		add_action( 'wp_footer', array( $this, 'render_copy_popup' ), 20 );

		foreach ( $this->positions as $position ) {

			// Check if render method exists.
			if ( ! method_exists( $this, 'render_position_' . $position ) ) {
				continue;
			}

			// Hook into Admin Live Preview.
			add_action( 'preview_social_share_' . $position, array( $this, 'render_position_' . $position ) );
			
			// Move on if this position is not enabled.
			if ( ! socialsnap_settings( 'ss_ss_' . $position . '_enabled' ) ) {
				continue;
			}

			switch ( $position ) {
				case 'sidebar':
					add_action( 'wp_footer', array( $this, 'render_position_sidebar' ) );
					break;

				case 'inline_content' :

					if ( ! is_admin() ) {

						if ( is_singular() || socialsnap_settings( 'ss_ss_inline_content_full_content' ) ) {
							add_filter( 'the_content', array( $this, 'filter_content_inline_content_share' ), 1000 ); 
						} else {
							add_filter( 'the_excerpt', array( $this, 'filter_content_inline_content_share' ), 1000 );
						}
					}
					break;

				case 'on_media' :

					if ( ! is_admin() ) {
						add_filter( 'the_content', array( $this, 'filter_content_on_media_share' ), 30 ); 
						add_filter( 'post_thumbnail_html', array( $this, 'filter_content_on_media_share' ), 30 );
						add_filter( 'woocommerce_single_product_image_thumbnail_html', array( $this, 'filter_content_on_media_share' ), 30 ); 
					}
					break;

				default:
					# code...
					break;
			}
		}
	}

	/**
	 * Determines whether the location should be displayed on this page or not
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function check_display_on( $settings ) {
		
		if ( ! is_array( $settings ) || empty( $settings ) ) {
			return false;
		}

		if ( get_post_meta( get_the_ID(), 'ss_social_share_disable', true ) ) {
			return false;
		}

		foreach ( $settings as $key => $value ) {

			if ( ! $value ) {
				continue;
			}

			switch( $key ) {
				case 'home':
					if ( is_front_page() ) {
						return true;
					}
					break;

				case 'blog':
					if ( is_home() ) {
						return true;
					}
					break;

				case 'shop' :
					if ( function_exists( 'is_shop' ) && is_shop() ) {
						return true;
					}
					break;

				default:
					if ( post_type_exists( $key ) && is_singular( $key ) && ! socialsnap_is_homepage() )  {
						return true;
					}
					break;
			}
		}

		return false;
	}

	/**
	 * Determines whether the share popup html should be printed.
	 * Returns true if at least one All Networks button is enabled.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function check_display_share_popup() {

		$return = false;

		if ( is_array( $this->positions ) && ! empty( $this->positions ) ) {
			foreach ( $this->positions as $position ) {

				if ( in_array( $position, array( 'on_media' ) ) ) {
					continue;
				}

				$position_enabled 		= socialsnap_settings( 'ss_ss_' . $position . '_enabled' );
				$all_networks_enabled 	= socialsnap_settings( 'ss_ss_' . $position . '_all_networks' );
				$display 				= $this->check_display_on( socialsnap_settings( 'ss_ss_' . $position . '_post_types' ) );

				$return = $return || ( $position_enabled && $all_networks_enabled && $display );
			}
		}

		return apply_filters( 'socialsnap_display_share_popup', $return );
	}

	/**
	 * Render popup that displays share buttons for all networks
	 *
	 * @since 1.0.0
	 */
	public function render_share_all_popup() {

		// No need for this since no share buttons are displayed.
		if ( ! apply_filters( 'socialsnap_share_all_popup_displayed', $this->share_all_popup_displayed ) ) {
			return;
		}

		$all_networks 	= socialsnap_get_social_share_networks(); 
		$post_id 		= socialsnap_get_current_post_id();
		$post_title 	= socialsnap_get_current_page_title();
		$current_link 	= socialsnap_get_current_url();

		if ( 0 === $post_id ) {
			return;
		}
		
		ob_start();
		?>

		<div id="ss-all-networks-popup" class="ss-popup-overlay" data-nonce="<?php echo wp_create_nonce('socialsnap-ss-counts-refresh'); ?>">
			<div class="ss-popup">
				
				<div class="ss-popup-heading">
					<span><?php _e( 'Share via', 'socialsnap' ); ?></span>
					<a href="#" id="ss-close-share-networks-modal" class="ss-close-modal" rel="nofollow"><i class="ss ss-close"></i></a>
				</div><!-- END .ss-popup-heading -->

				<div class="ss-popup-content">
					<div class="ss-popup-networks ss-clearfix">
						<?php foreach ( $all_networks as $id => $name ) { 

							$permalink 			= socialsnap_get_shared_permalink( array( 'permalink' => $current_link, 'network' => $id ) );
							$permalink          = apply_filters( 'socialsnap_complete_shared_permalink', $permalink, $id );
							$share_url 			= socialsnap_get_share_url( $id, array( 'permalink' => $permalink, 'title' => $post_title, 'location' => 'share_all_popup' ) );

							$additional_data 	= apply_filters( 'socialsnap_ss_button_data', '', $share_url, $permalink, $id, $post_id, 'popup' );
							$icon_class 		= apply_filters( 'socialsnap_social_share_button_class', 'ss-' . $id . '-color', $id, $post_id );

							if ( 'pinterest' === $id ) {
								$additional_data = $additional_data . ' data-ss-ss-link="' . esc_url( $share_url ) . '"';
								$share_url       = '#';
							}
							?>
							
							<div class="ss-popup-network ss-popup-<?php echo esc_attr( $id ); ?>">
								<a href="#" data-ss-ss-link="<?php echo esc_url( $share_url ); ?>" data-id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $icon_class ); ?>" <?php echo $additional_data; ?> rel="nofollow">
									<i class="ss ss-<?php echo $id; ?>"></i>
									<span><?php echo $name; ?></span>
									<?php /*<span class="ss-popup-network-counter"><?php echo socialsnap_get_share_count( $id, array( 'url' => $permalink, 'post_id' => $post_id ) ); */ ?>
								</a>
							</div>

						<?php } ?>
					</div><!-- END .ss-popup-networks -->

					<?php socialsnap_signature(); ?>

				</div><!-- END .ss-popup-content -->
			</div><!-- END .ss-popup -->
		</div><!-- END #ss-all-networks-popup -->

		<?php 
		$output = ob_get_clean();

		echo apply_filters( 'socialsnap_share_all_popup', $output );
	}

	/**
	 * Render popup that displays the copy link.
	 *
	 * @since 1.1.6
	 */
	public function render_copy_popup() {

		$post_id = socialsnap_get_current_post_id();

		if ( 0 === $post_id ) {
			return;
		}

		$permalink = socialsnap_get_shared_permalink(
			array(
				'permalink' => socialsnap_get_current_url(),
				'network'   => 'copy'
			)
		);
		$permalink = apply_filters( 'socialsnap_complete_shared_permalink', $permalink, 'copy' );
		
		ob_start();
		?>

		<div id="ss-copy-popup" class="ss-popup-overlay">
			<div class="ss-popup">
				
				<div class="ss-popup-heading">
					<span><?php esc_html_e( 'Copy link', 'socialsnap' ); ?></span>
					<a href="#" id="ss-close-share-networks-modal" class="ss-close-modal" rel="nofollow"><i class="ss ss-close"></i></a>
				</div><!-- END .ss-popup-heading -->

				<div class="ss-popup-content">

					<div class="ss-copy-action">
						<input type="text" readonly="readonly" value="<?php echo esc_url( $permalink ); ?>" class="ss-copy-action-field" />
						<a href="#" class="ss-button" rel="nofollow"><?php _e( 'Copy', 'socialsnap' ); ?><span class="ss-share-network-tooltip"><?php _e( 'Copied', 'socialsnap' ); ?></span></a>
						<i class="ss ss-copy"></i>
					</div><!-- END .ss-copy-action -->

					<?php socialsnap_signature(); ?>

				</div><!-- END .ss-popup-content -->
			</div><!-- END .ss-popup -->
		</div><!-- END #ss-copy-popup -->

		<?php 
		$output = ob_get_clean();

		echo apply_filters( 'socialsnap_copy_popup', $output );
	}

	/**
	 * Display Social Sharing buttons on a floating sidebar.
	 *
	 * @since 1.0.0
	 */
	public function render_position_sidebar() {

		if ( ! socialsnap_settings( 'ss_ss_sidebar_enabled' ) && ! is_admin() ) {
			return;
		}

		if ( ! is_admin() && ! $this->check_display_on( socialsnap_settings( 'ss_ss_sidebar_post_types' ) ) ) {
			return;
		}

		// No networks selected in the settings panel.
		if ( ! is_array( $this->networks ) || empty( $this->networks ) ) {
			return;
		}

		$class  = array();
		$class  = apply_filters( 'socialsnap_display_position_classes', $class, 'sidebar' );
		$class  = implode( ' ', $class );

		$atts = apply_filters( 'socialsnap_floating_sidebar_atts', array() );
		$attributes = '';

		if ( ! empty( $atts ) ) {
			foreach ( $atts as $key => $value ) {
				$attributes .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
			}
		}

		ob_start();
		?>
		<div id="ss-floating-bar" class="<?php echo esc_attr( $class ); ?>"<?php echo $attributes; ?>>

			<?php 
			$this->render_share_count( 'sidebar' );
			$this->render_social_icons( 'sidebar' );
			$this->render_view_count( 'sidebar' ); 
			?>

			<span class="ss-hide-floating-bar">
				<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 370.814 370.814"><path d="M292.92 24.848L268.781 0 77.895 185.401l190.886 185.413 24.139-24.853-165.282-160.56"/></svg>
			</span>
			
		</div><!-- END #ss-floating-bar -->
		<?php
		$output = ob_get_clean();
		echo apply_filters( 'socialsnap_social_share_sidebar', $output );
	}

	/**
	 * Display Social Sharing buttons around content.
	 *
	 * @since 1.0.0
	 */
	public function render_position_inline_content( $options = array() ) {

		$defaults = array(
			'share_label'		=> socialsnap_settings( 'ss_ss_inline_content_share_label' ),
			'show_total_count'	=> socialsnap_settings( 'ss_ss_inline_content_total_count' )
		);

		$options = array_replace_recursive( $defaults, (array) $options );

		$class = array( 'ss-inline-share-wrapper' );
		$class = apply_filters( 'socialsnap_display_position_classes', $class, 'inline_content', $options );
		$class = implode( ' ', $class );

		ob_start();
		?>
		<div class="<?php echo $class; ?>">

			<?php // Echo share label
			if ( $options['share_label'] ) { ?>
				<p class="ss-social-share-label">
					<span><?php echo $options['share_label']; ?></span>
				</p>
			<?php } ?>
			
			<div class="ss-inline-share-content">
				
				<?php $this->render_share_count( 'inline_content', array( 'post_id' => get_the_ID(), 'options' => $options ) ); ?>
				<?php $this->render_social_icons( 'inline_content', array( 'post_id' => get_the_ID(), 'options' => $options ) ); ?>
			
			</div><!-- END .ss-inline-share-content -->
		</div><!-- END .ss-inline-share-wrapper -->
		<?php
		$output = ob_get_clean();
		echo apply_filters( 'socialsnap_social_share_inline_content', $output );
	}

	/**
	 * Filter content and insert inline social sharing buttons
	 *
	 * @since 1.0.0
	 */
	public function filter_content_inline_content_share( $content ) {

		if ( ! socialsnap_settings( 'ss_ss_inline_content_enabled' ) && ! is_admin() ) {
			return $content;
		}

		if ( ! is_admin() && ! $this->check_display_on( socialsnap_settings( 'ss_ss_inline_content_post_types' ) ) ) {
			return $content;
		}
		
		ob_start();
		$this->render_position_inline_content();
		$output = ob_get_clean();

		$inline_location = socialsnap_settings( 'ss_ss_inline_content_location' );

		if ( in_array( $inline_location, array( 'above', 'both' ) ) ) {
			$content = $output . $content;
		}

		if ( in_array( $inline_location, array( 'below', 'both' ) ) ) {
			$content = $content . $output;
		}

		return $content;
	}

	/**
	 * Filter content and insert on media sharing buttons
	 *
	 * @since 1.0.0
	 */
	public function filter_content_on_media_share( $content ) {

		if ( ! socialsnap_settings( 'ss_ss_on_media_enabled' ) ) {
			return $content;
		}

		if ( is_admin() ) {
			return $content;
		}

		if ( ! is_admin() && ! $this->check_display_on( socialsnap_settings( 'ss_ss_on_media_post_types' ) ) ) {
			return $content;
		}

		// Get all imgs from content
		preg_match_all( '/<img [^>]*>/s', $content, $images_array );

		if ( ! is_array( $images_array ) || empty( $images_array ) ) {
			return $content;
		}

		$images_array = array_unique( $images_array[0] );

		foreach ( $images_array as $image ) {

			if ( false !== strpos( $image, 'class="ngg_' ) ) {
				continue;
			}

			preg_match( '@src="([^"]+)"@' , $image , $image_src );

			// Check for image src
			if ( isset( $image_src[1] ) ) {
				$image_src = $image_src[1];
			} elseif( isset( $image_src[0] ) ) {
				$image_src = $image_src[0];
			} else {
				return $content;
			}

			// Get classes from img
			preg_match( '@class="([^"]+)"@' , $image , $image_classes );

			$image_class 		= 'ss-on-media-container';
			$image_wrap_class 	= 'ss-on-media-image-wrap';

			if ( isset( $image_classes[1] ) ) {
				$image_class 		.= ' ' . $image_classes[1];
				$image_wrap_class 	.= ' ' . $image_classes[1];
			}

			ob_start();
			$this->render_position_on_media( $image_src );
			$icons = ob_get_clean();

			if ( ! $icons ) {
				return $content;
			}

			$replacement = '<div class="' . $image_class . '"><span class="' . $image_wrap_class . '">' . $image . $icons . '</span></div>';
			$content     = str_replace( $image, $replacement, $content );
		}

		return $content;
	}

	/**
	 * Display Social Sharing buttons on a media element.
	 *
	 * @since 1.0.0
	 */
	public function render_position_on_media( $image_src ) {

		$class = array( 'ss-on-media-wrapper' );
		$class = apply_filters( 'socialsnap_display_position_classes', $class, 'on_media' );
		$class = implode( ' ', $class );

		$button_type = socialsnap_settings( 'ss_ss_on_media_type' );

		ob_start();
		?>
		<div class="<?php echo $class; ?>">

			<?php 
			if ( 'pin_it' === $button_type || is_admin() ) { 

				$share_url = is_admin() ? '#' : socialsnap_get_share_url( 'pinterest', array( 'image' => $image_src, 'location' => 'on_media' ) );

				if ( $share_url ) {
					?>
					<ul class="ss-social-icons-container ss-on-media-pinit">
						<li>
							<div data-ss-ss-link="<?php echo $share_url; ?>" class="ss-pinterest-color ss-pinit-button ss-ss-on-media-button">
								<span class="ss-on-media-content">
									<i class="ss ss-pinterest"></i><?php apply_filters( 'socialsnap_pinit_text', _e( 'Save', 'socialsnap' ) ); ?>
								</span>
							</div>
						</li>
					</ul>
					<?php 
				}
			}

			if ( 'share_buttons' === $button_type || is_admin() ) {
				$this->render_social_icons( 'on_media', array( 'image' => $image_src ) );
			}
			?>
		</div>
		<?php
		$output = ob_get_clean();
		echo apply_filters( 'socialsnap_social_share_on_media', $output );
	}

	/**
	 * Display Total share count
	 *
	 * @since 1.0.0
	 */
	protected function render_share_count( $location = null, $settings = array() ) {

		if ( is_null( $location ) ) {
			return;
		}

		$defaults = array(
			'post_id'	=> '',
			'options'	=> array(
				'show_total_count' 		=> socialsnap_settings( 'ss_ss_' . $location . '_total_count' ),
				'inline_total_style'	=> socialsnap_settings( 'ss_ss_inline_content_total_share_style' )
			),
		);

		$settings = array_replace_recursive( $defaults, $settings );

		$settings = apply_filters( 'socialsnap_social_share_display_args', $settings );
		
		if ( ! $settings['post_id'] ) {
			$settings['post_id'] = socialsnap_get_current_post_id();
		}

		if ( is_admin() ) { 
			$count = rand( 300, 700 );
		} else {
			$count = socialsnap_get_total_share_count( array( 'post_id' => $settings['post_id'] ) );
		} 

		$this->counts_displayed = $this->counts_displayed || $settings['options']['show_total_count'];

		$settings = apply_filters( 'socialsnap_total_share_count_settings', $settings, $location, $count );

		if ( ! $settings['options']['show_total_count'] && ! is_admin() ) {
			return;
		}

		$count = socialsnap_format_number( $count );

		if ( 'inline_content' === $location ) { ?>

			<!-- Total share counter -->
			<div class="ss-inline-counter">

			<?php do_action( 'socialsnap_before_total_share_counter', $location, $settings ); ?>

		<?php } ?>

		<span class="ss-total-counter ss-total-shares ss-share-<?php echo $location; ?>-total-shares" data-ss-ss-post-id="<?php echo $settings['post_id']; ?>">
			<span><?php echo $count ?></span>
			<span><?php echo _n( 'Share', 'Shares', $count, 'socialsnap' ); ?></span>
		</span>

		<?php if ( 'inline_content' === $location ) { ?>
			</div>
		<?php } 

		do_action( 'socialsnap_after_total_share_counter', $location, $settings );
	}

	/**
	 * Display View count
	 *
	 * @since 1.0.0
	 */
	protected function render_view_count( $location = null ) {
		echo apply_filters( 'socialsnap_view_count', '', $location );
	}

	/**
	 * Generate list of social sharing icons
	 *
	 * @since 1.0.0
	 */
	protected function render_social_icons( $location, $data = array() ) {

		// Location is required.
		if ( ! isset ( $location ) ) {
			return;
		}

		// Set the displayed indicator to true.
		$this->is_displayed = true;

		// Default settings
		$defaults = array(
			'post_id'		=> socialsnap_get_current_post_id(),
			'permalink'		=> socialsnap_get_current_url(),
			'image'			=> '',
			'options'		=> array(
				'networks'				=> array(),
				'share_count'			=> socialsnap_settings( 'ss_ss_' . $location . '_share_count' ),
				'tooltip'				=> socialsnap_settings( 'ss_ss_' . $location . '_label_tooltip' ),
				'all_networks'			=> socialsnap_settings( 'ss_ss_' . $location . '_all_networks' ),
				'inline_button_label'	=> socialsnap_settings( 'ss_ss_inline_content_button_label' ),
				'hover_animation'		=> socialsnap_settings( 'ss_ss_inline_content_hover_animation' ),
			)
		);

		// Filter data with the default alues
		$data = array_replace_recursive( $defaults, $data );

		// Allow modification of data
		$data = apply_filters( 'socialsnap_social_share_display_args', $data );

		// Set up the Post ID
		if ( ! $data['post_id'] ) {
			$data['post_id'] = socialsnap_get_current_post_id( $data['permalink'] );
		}

		$data['title'] = socialsnap_get_shared_title( array( 'post_id' => $data['post_id'], 'location' => $location ) );

		// No networks specified, take from settings
		if ( ! is_array( $data['options']['networks'] ) || empty( $data['options']['networks'] ) ) {
			$data['options']['networks'] = $this->networks;
		}

		// No networks selected in the settings panel.
		if ( ! is_array( $data['options']['networks'] ) || empty( $data['options']['networks'] ) ) {

			if ( is_admin() ) { 
				echo '<ul class="ss-social-icons-container"></ul>';	
			} 

			return;
		}

		// Mobile only social networks
		$mobile_only_networks = socialsnap_get_mobile_only_social_share_networks();
		$container_class	  = is_admin() ? ' ' . $location : '';
		?>

		<ul class="ss-social-icons-container<?php echo $container_class; ?>">

			<?php 
			// Loop through the networks
			foreach ( $data['options']['networks'] as $network => $network_settings ) { 

				if ( 'order' === $network ) {
					continue; 
				}

				if ( ! $data['permalink'] ) {
					$permalink = socialsnap_get_shared_permalink( array( 'network' => $network ) );
				} else {
					$permalink = $data['permalink'];
				}

				$permalink = apply_filters( 'socialsnap_complete_shared_permalink', $permalink, $network );

				$network_settings = wp_parse_args( $network_settings, array(
					'desktop_visibility'	=> ! in_array( $network, array_keys( $mobile_only_networks ) ),
					'text'					=> socialsnap_get_network_name( $network ),
				));
				
				$hide_class = array();

				if ( ! isset( $network_settings['desktop_visibility'] ) || ! $network_settings['desktop_visibility'] ) {
					$hide_class[] = 'ss-hide-on-desktop';
				}

				if ( ( ! isset( $network_settings['mobile_visibility'] ) || ! $network_settings['mobile_visibility'] ) && ! in_array( $network, array_keys( $mobile_only_networks ) ) ) {
					$hide_class[] = 'ss-hide-on-mobile';
				}

				$hide_class 		= implode( ' ', $hide_class );
				$share_url 			= is_admin() ? '#' : socialsnap_get_share_url( $network, array( 'image' => $data['image'], 'post_id' => $data['post_id'], 'permalink' => $permalink, 'title' => $data['title'], 'location' => $location ) );
				$additional_data 	= apply_filters( 'socialsnap_ss_button_data', '', $share_url, $permalink, $network, $data['post_id'], $location );

				// Share URL not valid, go to next network.
				if ( ! $share_url ) {
					continue;
				}
				?>
				<li class="<?php echo $hide_class; ?>">

					<?php if ( 'on_media' === $location ) { ?>

						<?php $icon_class = apply_filters( 'socialsnap_social_share_button_class', 'ss-ss-on-media-button ss-' . $network . '-color ss-on-media', $network, $data['post_id'] ); ?>

						<div data-ss-ss-link="<?php echo $share_url; ?>" class="<?php echo $icon_class; ?>" <?php echo $additional_data; ?>>
					<?php } else { ?>

						<?php $icon_class = apply_filters( 'socialsnap_social_share_button_class', 'ss-' . $network . '-color', $network, $data['post_id'] ); ?>

						<a href="#" data-ss-ss-link="<?php echo esc_url( $share_url ); ?>" class="<?php echo esc_attr( $icon_class ); ?>" rel="nofollow" <?php echo $additional_data; ?>>
					<?php } ?>

						<span class="ss-share-network-content">
							<i class="ss ss-<?php echo $network; ?>"></i>

							<?php 
							$share_count = socialsnap_get_share_count( $network, array( 'url' => $permalink, 'post_id' => $data['post_id'] ) );
							$share_count = apply_filters( 'socialsnap_filter_social_share_count', $share_count, $location );
							$share_count = is_admin() ? rand( 1, 200 ) : $share_count;

							if ( in_array( $location, array( 'inline_content' ) ) ) { 

								if ( socialsnap()->pro && ( is_admin() || 'ss-reveal-label' === $data['options']['hover_animation'] ) ) { ?>
									<span class="ss-reveal-label-wrap">
								<?php }

								$data['options']['share_count'] = in_array( $data['options']['inline_button_label'], array( 'count', 'both' ) );
							
								if ( in_array( $data['options']['inline_button_label'], array( 'label', 'both' ) ) || is_admin() ) { ?>
									<span class="ss-network-label"><?php echo $network_settings['text']; ?></span>
								<?php }
							}

							$this->counts_displayed = $this->counts_displayed || $data['options']['share_count'];

							if ( is_admin() || is_numeric( $share_count ) && $data['options']['share_count'] ) { ?>
								<span class="ss-network-count">
									<?php echo socialsnap_format_number( (int) $share_count ); ?>		
								</span>
							<?php }

							if ( socialsnap()->pro && 'ss-reveal-label' === $data['options']['hover_animation'] ) { ?>
								</span><!-- .ss-reveal-label-wrap -->
							<?php } ?>

						</span>

					<?php if ( 'on_media' === $location ) { ?>
						</div>
					<?php } else { ?>
						</a>
					<?php } ?>

					<?php if ( $data['options']['tooltip'] || is_admin() ) { ?>
						<span class="ss-share-network-tooltip"><?php echo $network_settings['text']; ?></span>
					<?php } ?>
				</li>
			<?php } ?>

			<?php 
				if ( $data['options']['all_networks'] || is_admin() ) {

					$this->share_all_popup_displayed = true;

					$no_label_class = '';

					if ( 'inline_content' == $location && ! socialsnap_settings( 'ss_ss_inline_content_all_networks_label' ) ) {
						$no_label_class = 'ss-without-all-networks-label ';
					}
				?>

				<li>
					<a href="#" class="<?php echo $no_label_class; ?>ss-share-all ss-shareall-color" rel="nofollow">
						<span class="ss-share-network-content">
							<i class="ss ss-plus"></i>
					
							<?php
							if ( 'inline_content' == $location ) { ?>
								<span class="ss-reveal-label-wrap">
									<?php
									if ( $label = socialsnap_settings( 'ss_ss_inline_content_all_networks_label' ) || is_admin() ) { ?>
										<span class="ss-network-label"><?php echo socialsnap_settings( 'ss_ss_inline_content_all_networks_label' ); ?></span>
									<?php } ?>
								</span>
							<?php } ?>
						</span>
					</a>

					<?php if ( $data['options']['tooltip'] || is_admin() ) { ?>
						<span class="ss-share-network-tooltip"><?php _e( 'More Networks', 'socialsnap' ); ?></span>
					<?php } ?>

				</li>
			<?php } ?>
		</ul>
		<?php
	}

	/**
	 * Create array of classes for specified button location
	 *
	 * @since 1.0.0
	 */
	public function get_display_position_classes( $class = array(), $location = null, $settings = array() ) {

		if ( is_null( $location ) ) {
			return $class;
		}

		// Default Settings
		$defaults = array(
			'position'				=> socialsnap_settings( 'ss_ss_' . $location . '_position' ),
			'button_size'			=> socialsnap_settings( 'ss_ss_' . $location . '_button_size' ),
			'button_spacing'		=> socialsnap_settings( 'ss_ss_' . $location . '_button_spacing' ),
			'button_shape'			=> socialsnap_settings( 'ss_ss_' . $location . '_button_shape' ),
			'hide_on_mobile'		=> socialsnap_settings( 'ss_ss_' . $location . '_hide_on_mobile' ),
			'on_media_visibility'	=> socialsnap_settings( 'ss_ss_on_media_hover' ),
			'inline_button_label'	=> socialsnap_settings( 'ss_ss_inline_content_button_label' ),
		);

		$settings = array_replace_recursive( $defaults, $settings );

		$location_class = str_replace( '_', '-', $location );

		$class[] = 'ss-' . $settings['position'] . '-'. $location_class;
		$class[] = 'ss-' . $settings['button_size'] . '-icons'; 
		
		if ( $settings['hide_on_mobile'] ) {
			$class[] = 'ss-hide-on-mobile';
		}

		if ( in_array( $location, array( 'sidebar', 'inline_content', 'on_media' ) ) && $settings['button_spacing'] ) {
			$class[] = 'ss-with-spacing';
		}

		if ( in_array( $location, array( 'sidebar', 'inline_content', 'on_media', 'hub' ) ) ) {
			$class[] = 'ss-' . $settings['button_shape'] . '-icons';
		}

		if ( 'on_media' === $location && $settings['on_media_visibility'] === 'always' ) {
			$class[] = 'ss-on-media-always-visible';
		}

		if ( 'inline_content' === $location ) {
			if ( 'none' === $settings['inline_button_label'] ) {
				$class[] = 'ss-without-labels';
			}

			if ( 'both' === $settings['inline_button_label'] ) {
				$class[] = 'ss-both-labels';
			}
		}

		return apply_filters( 'socialsnap_social_share_class', $class );
	}

	/**
	 * Mark button to use API for share count.
	 *
	 * @since 1.0.0
	 */
	public function add_share_button_api_data( $data, $url, $permalink, $network, $post_id, $location ) {
		
		$data .= ' data-ss-ss-network-id="' . $network . '"';
		$data .= ' data-ss-ss-post-id="' . $post_id . '"';
		$data .= ' data-ss-ss-location="' . $location . '"';
		$data .= ' data-ss-ss-permalink="' . $permalink . '"';

		if ( $network == 'heart' ) {
			$data .= ' data-ss-ss-type="like"';
		} else {
			$data .= ' data-ss-ss-type="share"';
		}
		
		$with_api = socialsnap_get_social_share_networks_with_api();	

		if ( in_array( $network, $with_api ) ) {
			$data .= ' data-has-api="true"';
		}

		return $data;
	}

	/**
	 * Javascript Indicator that share count cache has expired.
	 *
	 * @since 1.0.0
	 */
	public function share_count_cache_expired() {

		// Check AMP pages.
		if ( socialsnap_is_amp_page() ) {
			return;
		}

		// No share buttons here.
		if ( ! apply_filters( 'socialsnap_share_buttons_displayed', $this->is_displayed ) ) {
			return;
		}

		// No share counts here.
		if ( ! apply_filters( 'socialsnap_share_counts_displayed', $this->counts_displayed ) ) {
			return;
		}

		// Skip WooCommerce account page.
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			return;
		}

		$expired = socialsnap_share_count_expired( socialsnap_get_current_url(), socialsnap_get_current_post_id() );
		$post_id = socialsnap_get_current_post_id();

		// Only do for published pages.
		if ( $post_id > 0 && 'publish' !== get_post_status( $post_id ) ) {
			return;
		}

		?>
		<!-- Social Snap Share count cache indicator -->
		<script type="text/javascript">

			var SocialSnapURL 				= window.location.href;
			var SocialSnapShareCacheExpired = <?php echo intval( $expired ); ?>;
				
			if ( -1 !== SocialSnapURL.indexOf('?ss_cache_refresh') ) {

				SocialSnapShareCacheExpired = true;

			} else {

				var SocialSnapServerTimestamp 	= <?php echo time(); ?>;
				var SocialSnapBrowserTimestamp 	= Date.now();

				if ( ! SocialSnapBrowserTimestamp ) {
					SocialSnapBrowserTimestamp = new Date().getTime();
				}

				SocialSnapBrowserTimestamp = Math.floor( SocialSnapBrowserTimestamp / 1000 );

				SocialSnapShareCacheExpired = SocialSnapShareCacheExpired && ( SocialSnapBrowserTimestamp - SocialSnapServerTimestamp < 60 );
			}

		</script>
		<!-- Social Snap Share count cache indicator -->
		<?php
	}

	/**
	 * Register shortcode for click to tweet module
	 *
	 * @since 1.0.0
	 */
	public function register_shortcodes( $atts ) {

		$defaults = array(
			'networks'			=> '',
			'align'				=> socialsnap_settings( 'ss_ss_inline_content_position' ),
			'total'				=> socialsnap_settings( 'ss_ss_inline_content_total_count' ),
			'shape'				=> socialsnap_settings( 'ss_ss_inline_content_button_shape' ),
			'size'				=> socialsnap_settings( 'ss_ss_inline_content_button_size' ),
			'labels'			=> socialsnap_settings( 'ss_ss_inline_content_button_label' ),
			'spacing'			=> socialsnap_settings( 'ss_ss_inline_content_button_spacing' ),
			'hide_on_mobile'	=> socialsnap_settings( 'ss_ss_inline_content_hide_on_mobile' ),
			'all_networks'		=> socialsnap_settings( 'ss_ss_inline_content_all_networks' )
		);

		$defaults = apply_filters( 'socialsnap_social_share_shortcode_atts', $defaults );

		$atts = shortcode_atts( 
			$defaults, 
			$atts 
		);

		$networks_default 	= $this->networks;
		$networks_mobile	= socialsnap_get_mobile_only_social_share_networks();
		$allowed_networks	= array_keys( socialsnap_get_social_share_networks() );

		if ( '' === $atts['networks'] ) {
			$atts['networks'] 	= $networks_default;
		} else {

			$networks 			= explode( ';', strtolower( str_replace( ' ', '', $atts['networks'] ) ) );
			$atts['networks'] 	= array();

			if ( is_array( $networks ) && ! empty( $networks ) ) {
				foreach ( $networks as $network ) {

					if ( ! in_array( $network, $allowed_networks ) ) {
						continue;
					}
					
					if ( isset ( $networks_default[ $network ] ) ) {
						$atts['networks'][ $network ] = $networks_default[ $network ];
					} else {
						$atts['networks'][ $network ] = array(
							'text'					=> socialsnap_get_network_name( $network ),
							'desktop_visibility'	=> isset( $networks_mobile[ $network ] ) ? false : true,
							'mobile_visibility'		=> true,
						);
					}
				}
			}
		}

		$options = array(
			'networks'				=> $atts['networks'],
			'button_shape'			=> $atts['shape'],
			'button_size'			=> $atts['size'],
			'button_spacing'		=> $atts['spacing'],
			'position'				=> $atts['align'],
			'inline_button_label'	=> $atts['labels'],
			'hide_on_mobile'		=> $atts['hide_on_mobile'],
			'show_total_count'		=> $atts['total'],
			'all_networks'			=> $atts['all_networks'],
			'share_label'			=> false,
		);

		$options = apply_filters( 'socialsnap_social_share_shotcode_options', $options, $atts );

		ob_start();

		$this->render_position_inline_content( $options );

		return ob_get_clean();
	}

	/**
	 * Register Block for Social Share.
	 *
	 * @since 1.0.0
	 */
	public function block_editor_support() {

		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type( 'socialsnap/social-share', array(
			'render_callback' => array( $this, 'block_editor_social_share' ),
		) );
	}

	/**
	 * Social Share block editor support.
	 *
	 * @since 1.0.0
	 */
	public function block_editor_social_share( $attributes ) {
		ob_start();

		$defaults = array(
			'networks'			=> 'twitter;facebook;linkedin',
			'align'				=> 'left',
			'total'				=> false,
			'shape'				=> 'rounded',
			'size'				=> 'small',
			'labels'			=> 'label',
			'spacing'			=> true,
			'hide_on_mobile'	=> false,
			'all_networks'		=> true,
		);

		$defaults = apply_filters( 'socialsnap_social_share_block_editor_atts', $defaults );

		$attributes['networks'] = isset( $attributes['networks'] ) ? $attributes['networks'] : '';
		$attributes['networks'] = preg_replace( '/ |\t/', '', $attributes['networks'] );
		$attributes['networks'] = preg_replace( '/\n/', ';', $attributes['networks'] );
		$attributes['networks']	= strtolower( $attributes['networks'] );

		$attributes = wp_parse_args( $attributes, $defaults );
		
		$shortcode = '[ss_social_share';

		foreach ( $attributes as $key => $value ) {
			$shortcode .= ' ' . $key . '="' . $value . '"';
		}
		$shortcode .= ']';

		echo do_shortcode( $shortcode );

		return ob_get_clean();
	}

	/**
	 * Reset flags for share count cache.
	 *
	 * @since 1.0.0
	 */
	public function socialsnap_refresh_share_count_cache() {
		
		check_ajax_referer( 'socialsnap-admin' );

		if ( ! current_user_can( apply_filters( 'socialsnap_manage_cap', 'manage_options' ) ) ) {
			wp_send_json_error( array(
				'message' => __( 'Error. Access denied.', 'socialsnap' )
			) );
		}

		// Reset homepage flag.
		update_option( 'socialsnap_homepage_share_count_timestamp', false );

		// Reset individual post/page flag.
		global $wpdb;

		set_time_limit( 300 );

		$query = "
			SELECT postmeta.post_id, postmeta.meta_value
			FROM   $wpdb->postmeta postmeta
			WHERE  postmeta.meta_key = %s
		";

		$results = $wpdb->get_results( $wpdb->prepare( $query, 'socialsnap_share_count_timestamp' ) );

		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				update_post_meta( $row->post_id, 'socialsnap_share_count_timestamp', false );
			}
		}
	}

	/**
	 * Modify settings.
	 *
	 * @since 1.0.0
	 * @param array $settings Array of Social Snap settings.
	 * @return array          Modified array of Social Snap settings.
	 */
	public function add_settings_config( $settings ) {

		$facebook_token = get_transient( 'ss_facebook_token' );

		if ( false !== $facebook_token ) {

			$note = array(
				'ss_ss_facebook_authorize_note' => array(
					'id'     => 'ss_ss_facebook_authorize_note',
					'name'   => '<span class="error">' . esc_html__( 'Authorization expired. ', 'socialsnap' ) . '</span>',
					'desc'   => __( 'Please authorize again.', 'socialsnap' ),
					'type'   => 'note',
					'dependency'	=> array(
						'element'	=> 'ss_ss_facebook_count_provider',
						'value'		=> 'authorize',
					),
				),
			);

			if ( 'never' !== $facebook_token['expires_in'] && time() < intval( $facebook_token['expires_in'] ) ) {
				$note['ss_ss_facebook_authorize_note']['name'] = '<span class="ss-bitly-authorized"><i class="dashicons dashicons-yes"></i>' . __( 'Authorized. ', 'socialsnap' ) . '</span>';
				$note['ss_ss_facebook_authorize_note']['desc'] = __( 'Expires on ', 'socialsnap' ) . date( 'F j, Y', $facebook_token['expires_in'] ) . '.';

				unset( $settings['ss_social_sharing']['fields']['ss_social_share_networks_display']['fields']['ss_ss_facebook_authorize_app'] );
			} elseif ( 'never' === $facebook_token['expires_in'] ) {
				$note['ss_ss_facebook_authorize_note']['name'] = '<span class="ss-bitly-authorized"><i class="dashicons dashicons-yes"></i>' . __( 'Authorized. ', 'socialsnap' ) . '</span>';
				$note['ss_ss_facebook_authorize_note']['desc'] = __( 'Never expires.', 'socialsnap' );
				
				unset( $settings['ss_social_sharing']['fields']['ss_social_share_networks_display']['fields']['ss_ss_facebook_authorize_app'] );
			}
			
			// Insert into settings.
			$settings['ss_social_sharing']['fields']['ss_social_share_networks_display']['fields'] = socialsnap_array_insert(
				$settings['ss_social_sharing']['fields']['ss_social_share_networks_display']['fields'], 
				$note, 
				'ss_ss_facebook_count_provider',
				'after'
			);
		}

		return $settings;
	}
}
new SocialSnap_Social_Share;