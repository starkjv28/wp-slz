<?php
/**
 * Welcome page class.
 *
 * This page is shown when the plugin is activated.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
*/
class SocialSnap_Welcome extends SocialSnap_Admin_Page {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Call parent constructor
		parent::__construct();

		$this->page_slug = 'welcome';
		$this->title 	 = __( 'Welcome to Social Snap!', 'socialsnap' );

		// Actions
		add_action( 'admin_menu', 		array( $this, 'register_pages' ), 12 );
		add_action( 'admin_init', 		array( $this, 'redirect'  ), 9999 );
		add_action( 'admin_notices', 	array( $this, 'display_notices' ) );

		// Add AJAX handler for subscribe
		add_action( 'wp_ajax_socialsnap_subscribe', array( $this, 'subscribe' ) );
	}

	/**
	 * Register the pages to be used for the Welcome screen (and tabs).
	 *
	 * These pages will be removed from the Dashboard menu, so they will
	 * not actually show.
	 *
	 * @since 1.0.0
	 */
	public function register_pages() {

		// Getting started - shows after installation
		add_submenu_page(
			'socialsnap-settings',
			__( 'Welcome to Social Snap', 'socialsnap' ),
			__( 'About', 'socialsnap' ),
			apply_filters( 'socialsnap_welcome_cap', 'manage_options' ),
			'about-socialsnap',
			array( $this, 'render' )
		);
	}

	/**
	 * Welcome screen redirect.
	 *
	 * This function checks if a new install or update has just occured. If so,
	 * then we redirect the user to the appropriate page.
	 *
	 * @since 1.0.0
	 */
	public function redirect() {

		// Check if we should consider redirection.
		if ( ! get_transient( 'socialsnap_activation_redirect' ) ) {
			return;
		}

		// If we are redirecting, clear the transient so it only happens once.
		delete_transient( 'socialsnap_activation_redirect' );

		// Check option to disable welcome redirect.
		if ( get_option( 'socialsnap_activation_redirect', false ) ) {
			return;
		}

		// Only do this for single site installs.
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		// Initial install.
		wp_safe_redirect( admin_url( 'admin.php?page=about-socialsnap' ) );
		exit;
	}

	/**
	 * Build the output for the plugin welcome page.
	 *
	 * @since 1.0.0
	 */
	public function render() { ?>

		<div class="ss-welcome-wrapper">

			<div class="ss-welcome-logo">
				<img src="<?php echo SOCIALSNAP_PLUGIN_URL . '/assets/images/ss-logo-dark.svg'; ?>" />
			</div>

			<div class="ss-welcome-about ss-welcome-section ss-clearfix">

				<p><?php _e( 'Welcome to <strong>Social Snap</strong> — Start driving more traffic and increase engagement by leveraging the power of social media.', 'socialsnap' ); ?></p>

				<div class="ss-actions-wrapper ss-clearfix">
					<a href="<?php echo admin_url('admin.php?page=socialsnap-settings'); ?>" class="ss-button ss-button-primary ss-button-large"><?php _e('Customize Social Snap', 'socialsnap'); ?></a>

					<a href="https://socialsnap.com/docs/?utm_source=WordPress&utm_medium=link&utm_campaign=liteplugin" class="ss-button ss-button-secondary" target="_blank"><?php _e( 'Read Guide', 'socialsnap' ); ?></a>
				</div><!-- END .ss-actions-wrapper -->

			</div><!-- END .ss-welcome-about -->

			<!-- <div class="ss-welcome-video ss-welcome-section">
				<div class="ss-video-wrapper">
					
					<a href="#" class="ss-video-image-placeholder">
						<i class="ss-play">
							<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 314.068 314.068"><path d="M293.002 78.53C249.646 3.435 153.618-22.296 78.529 21.068 3.434 64.418-22.298 160.442 21.066 235.534c43.35 75.095 139.375 100.83 214.465 57.47 75.096-43.365 100.84-139.384 57.471-214.474zm-73.168 187.271c-60.067 34.692-136.894 14.106-171.576-45.973C13.568 159.761 34.161 82.935 94.23 48.26c60.071-34.69 136.894-14.106 171.578 45.971 34.685 60.076 14.098 136.886-45.974 171.57zm-6.279-115.149l-82.214-47.949c-7.492-4.374-13.535-.877-13.493 7.789l.421 95.174c.038 8.664 6.155 12.191 13.669 7.851l81.585-47.103c7.506-4.332 7.522-11.388.032-15.762z"/></svg>
						</i>

						<span>How to customize Social Snap</span>
					</a>
						
					<iframe width="700" height="394" src="" allowfullscreen></iframe>

				</div>
			</div> -->

			<div class="ss-welcome-features ss-welcome-section">

				<h3>
					<?php socialsnap()->pro ? _e( 'Social Snap Features', 'socialsnap' ) : _e( 'Social Snap Lite Features', 'socialsnap' ); ?>
				</h3>

				<p><?php _e( 'Social Snap is the best social media plugin that gives you everything you need to increase shares and drive more traffic to your website.', 'socialsnap' ); ?></p>

				<div class="ss-row ss-clearfix">
					<div class="ss-col-6">
						<div class="ss-feature-icon">
							<img src="<?php echo SOCIALSNAP_PLUGIN_URL . 'assets/images/features/share.svg' ?>"/>
						</div>
						<h5><?php _e( 'Social Share Buttons', 'socialsnap' ); ?></h5>
						<p><?php _e( 'Add social sharing buttons without slowing down your website.', 'socialsnap' ); ?></p>
					</div>

					<div class="ss-col-6">
						<div class="ss-feature-icon">
							<img src="<?php echo SOCIALSNAP_PLUGIN_URL . 'assets/images/features/counters.svg' ?>"/>
						</div>
						<h5><?php _e( 'Share Counters', 'socialsnap' ); ?></h5>
						<p><?php _e( 'Display social media share counters and encourage users to share.', 'socialsnap' ); ?></p>
					</div>
				</div><!-- END .ss-row -->

				<div class="ss-row ss-clearfix">
					<div class="ss-col-6">
						<div class="ss-feature-icon">
							<img src="<?php echo SOCIALSNAP_PLUGIN_URL . 'assets/images/features/floating.svg' ?>"/>
						</div>
						<h5><?php _e( 'Floating Share Buttons', 'socialsnap' ); ?></h5>
						<p><?php _e( 'Stunning share buttons that are accessible throughout the entire page.', 'socialsnap' ); ?></p>
					</div>

					<div class="ss-col-6">
						<div class="ss-feature-icon">
							<img src="<?php echo SOCIALSNAP_PLUGIN_URL . 'assets/images/features/media.svg' ?>"/>
						</div>
						<h5><?php _e( 'On Media Share Buttons', 'socialsnap' ); ?></h5>
						<p><?php _e( 'Display share buttons on media and leverage the power of Pinterest.', 'socialsnap' ); ?></p>
					</div>
				</div><!-- END .ss-row -->

				<div class="ss-row ss-clearfix">
					<div class="ss-col-6">
						<div class="ss-feature-icon">
							<img src="<?php echo SOCIALSNAP_PLUGIN_URL . 'assets/images/features/responsive.svg' ?>"/>
						</div>
						<h5><?php _e( 'Fully Responsive', 'socialsnap' ); ?></h5>
						<p><?php _e( 'Social Snap is fully responsive and looks great on all devices.', 'socialsnap' ); ?></p>
					</div>

					<div class="ss-col-6">
						<div class="ss-feature-icon">
							<img src="<?php echo SOCIALSNAP_PLUGIN_URL . 'assets/images/features/gutenberg.svg' ?>"/>
						</div>
						<h5><?php _e( 'Gutenberg Block &amp; Shortcode', 'socialsnap' ); ?></h5>
						<p><?php _e( 'Insert social media buttons into your content in a few simple clicks.', 'socialsnap' ); ?></p>
					</div>
				</div><!-- END .ss-row -->

				<div class="ss-row ss-clearfix">
					<div class="ss-col-6">
						<div class="ss-feature-icon">
							<img src="<?php echo SOCIALSNAP_PLUGIN_URL . 'assets/images/features/svg.svg' ?>"/>
						</div>
						<h5><?php _e( 'Scalable Vector Icons (SVG)', 'socialsnap' ); ?></h5>
						<p><?php _e( 'Scalable vector icons will look crisp on all devices and load faster.', 'socialsnap' ); ?></p>
					</div>

					<div class="ss-col-6">
						<div class="ss-feature-icon">
							<img src="<?php echo SOCIALSNAP_PLUGIN_URL . 'assets/images/features/fast.svg' ?>"/>
						</div>
						<h5><?php _e( 'Lightning Fast / Async Load', 'socialsnap' ); ?></h5>
						<p><?php _e( 'Advanced caching and async loading makes everything run smooth.', 'socialsnap' ); ?></p>
					</div>
				</div><!-- END .ss-row -->

				<div class="ss-row ss-clearfix">
					<div class="ss-col-6">
						<div class="ss-feature-icon">
							<img src="<?php echo SOCIALSNAP_PLUGIN_URL . 'assets/images/features/ctt.svg' ?>"/>
						</div>
						<h5><?php _e( 'Click to Tweet Boxes', 'socialsnap' ); ?></h5>
						<p><?php _e( 'An effective tool for increasing your site engagement and getting more shares on Twitter. ', 'socialsnap' ); ?></p>
					</div>

					<div class="ss-col-6">
						<div class="ss-feature-icon">
							<img src="<?php echo SOCIALSNAP_PLUGIN_URL . 'assets/images/features/followers.svg' ?>"/>
						</div>
						<h5><?php _e( 'Social Follow Buttons', 'socialsnap' ); ?></h5>
						<p><?php _e( 'Get more followers and subscribers with beautifully designed follow buttons and counters.', 'socialsnap' ); ?></p>
					</div>
				</div><!-- END .ss-row -->

				<?php $button_text = socialsnap()->pro ? __( 'See All Features', 'socialsnap' ) : __( 'See Premium Features', 'socialsnap' ); ?>
				<div class="ss-actions-wrapper ss-clearfix">
					<a href="<?php echo socialsnap_upgrade_link(); ?>" class="ss-button ss-button-secondary" target="_blank"><?php echo $button_text; ?></a>
				</div><!-- END .ss-actions-wrapper -->


			</div><!-- END .ss-welcome-section -->


			<?php if ( ! socialsnap()->pro ) { ?>

			<div class="ss-welcome-upgrade ss-welcome-section ss-clearfix">
				<div class="ss-row ss-clearfix">
					
					<div class="ss-col-8">
						<h3 class="ss-premium"><?php _e( 'Go Premium', 'socialsnap' ); ?></h3>

						<div class="ss-row ss-clearfix">
							
							<ul class="ss-col-6 ss-check-list">
								<li><?php _e( '30+ Share Providers', 'socialsnap' ); ?></li>
								<li><?php _e( 'More Share Positions', 'socialsnap' ); ?></li>
								<li><?php _e( 'Share Counters', 'socialsnap' ); ?></li>
								<li><?php _e( 'URL Shortening', 'socialsnap' ); ?></li>
								<li><?php _e( 'Analytics', 'socialsnap' ); ?></li>
							</ul>


							<ul class="ss-col-6 ss-check-list">
								<li><?php _e( 'Top Posts Widget', 'socialsnap' ); ?></li>
								<li><?php _e( 'Social Login', 'socialsnap' ); ?></li>
								<li><?php _e( 'Boost Old Posts', 'socialsnap' ); ?></li>
								<li><?php _e( 'Social Auto Poster', 'socialsnap' ); ?></li>
								<li><?php _e( 'Social Meta Tags', 'socialsnap' ); ?></li>
							</ul>


						</div><!-- END .ss-row -->
					</div><!-- END .ss-col-8 -->

					<div class="ss-col-4">
						
						<h3 class="ss-premium"><?php _e( 'Starting at', 'socialsnap' ); ?></h3>

						<div class="ss-upgrade-price">
							<span class="ss-amount">39</span>
							<span class="ss-per-year">per year</span>
						</div>

						<a href="<?php echo socialsnap_upgrade_link(); ?>" target="_blank" class="ss-button ss-upgrade-button"><?php _e( 'Upgrade Now', 'socialsnap' ); ?></a>
					</div>

				</div><!-- END .ss-row -->
			</div><!-- END .ss-welcome-upgrade -->

			<?php } ?>


			<?php
			$margin_top = '';
			if ( socialsnap()->pro ) {
				$margin_top = ' style="margin-top: 25px;"';
			} ?>

			<div class="ss-welcome-subscribe ss-welcome-section">
				<div class="ss-subscribe-content">
					<h2><?php _e( 'Stay in the loop!', 'socialsnap' ); ?></h2>

					<h4 style="max-width:340px;"><?php _e( 'Sign up to receive emails for the latest Social Snap updates, features, and news.', 'socialsnap' ); ?></h4>

					<div class="ss-subscribe-form">
						<input type="email" placeholder="Enter your email address" value="<?php echo get_option( 'admin_email' ); ?>" />
						<button type="submit" data-nonce="<?php echo wp_create_nonce( 'socialsnap-subscribe' ); ?>" class="ss-subscribe-action"><?php _e( 'Subscribe', 'socialsnap' ); ?></button>

						<span class="spinner"></span>
					</div>

					<div class="ss-subscribe-response">
						
					</div><!-- END .ss-subscribe-response -->

				</div>
			</div><!-- END .ss-welcome-subscribe -->

		</div><!-- END .ss-welcome-wrapper -->

		<ul class="ss-welcome-follow">
			<li>
				<a href="https://www.facebook.com/socialsnaphq/" target="_blank"><i class="ss ss-facebook ss-facebook-color"></i></a>
			</li>
			
			<li>
				<a href="https://twitter.com/socialsnaphq" target="_blank"><i class="ss ss-twitter ss-twitter-color"></i></a>
			</li>
			
			<li>
				<a href="https://socialsnap.com/" target="_blank"><i class="dashicons dashicons-ssbrand ss-socialsnap-color"></i></a>
			</li>
		</ul>

		<?php
	}


	/**
	 * Handles subscribe AJAX request.
	 *
	 * @since 1.0.0
	 */
	public function subscribe() {

		check_ajax_referer( 'socialsnap-subscribe', 'security' );

		if ( ! isset( $_POST['email'] ) ) {
			wp_send_json_error();
		}

		// Sanitize email
		$email 	= sanitize_email( $_POST['email'] );
		
		$url 	= add_query_arg( 
			array(
				'email'	=> $email
			), 
			'https://socialsnap.com/wp-json/api/v1/subscribe' 
		);

		$args = array(
			'user-agent' => 'SocialSnap/' . SOCIALSNAP_VERSION . '; ' . esc_url( home_url() ),
		);

		// Send request to socialsnap.com
		$request = wp_remote_get( $url, $args );

		if ( is_wp_error( $request ) ) {
			wp_send_json_error();
		}

		if ( 200 !== wp_remote_retrieve_response_code( $request ) ) {
			wp_send_json_error();
		}

		$response = json_decode( wp_remote_retrieve_body( $request ), true );

		if ( ! isset( $response['success'] ) ) {
			wp_send_json_error();
		}

		if ( false == $response['success'] ) {
			wp_send_json_error( array( 
				'message' => isset( $response['message'] ) ? esc_html( $response['message'] ) : __( 'Error. Please try again.', 'socialsnap' )
			) );
		}

		wp_send_json_success( array( 
			'message' => isset( $response['message'] ) ? esc_html( $response['message'] ) : __( 'Success.', 'socialsnap' )
		) );
	}

	/**
	 * Outputs the Social Snap admin notices.
	 *
	 * @since 1.0.5
	 */
	public function display_notices() {	

		if ( socialsnap()->pro ) {

			// Notices.
		}
	}	
}
new SocialSnap_Welcome();