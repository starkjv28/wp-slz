<?php
/**
 * Statistics page class.
 *
 * This page displays Social Snap Statistics
 *
 * @package    SocialSnap
 * @author     SocialSnap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
*/
class SocialSnap_Statistics extends SocialSnap_Admin_Page {

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Call parent constructor
		parent::__construct();

		$this->page_slug = 'statistics';
		$this->title 	 = __( 'Statistics', 'socialsnap' );

		// Actions
		add_action( 'admin_menu', array( $this, 'register_pages' ), 11 );
	}

	/**
	 * Register the pages to be used for the Settings screen.
	 *
	 * @since 1.0.0
	 */
	public function register_pages() {

		// Add 'Addons' submenu page
		add_submenu_page(
			'socialsnap-settings',
			__( 'Social Snap Statistics', 'socialsnap' ),
			__( 'Statistics', 'socialsnap' ),
			apply_filters( 'socialsnap_statistics_cap', 'manage_options' ),
			'socialsnap-statistics',
			array( $this, 'render' )
		);
	}

	/**
	 * Build the output for the plugin addons page.
	 *
	 * @since 1.0.0
	 */
	public function render() { ?>
			
		<div id="ss-statistics" class="ss-page-wrapper ss-clearfix">

			<?php if ( ! class_exists( 'SocialSnap_Pro' ) ) { ?>
				<div class="ss-statistics-placeholder">
					<img src="<?php echo SOCIALSNAP_PLUGIN_URL; ?>/assets/images/statistics.jpg"/>

					<div class="ss-upgrade-popup">
						<h3><?php _e( 'Your Website Social Stats in One Place', 'socialsnap' ); ?></h3>
						<p><?php _e( 'Upgrade Social Snap today and get access to advanced social network statistics to track how your website performs on social networks.', 'socialsnap' ); ?></p>

						<div class="ss-row ss-clearfix">
													
							<ul class="ss-col-6 ss-check-list">
								<li><?php _e( 'Track Shares, Views and Likes', 'socialsnap' ); ?></li>
								<li><?php _e( 'Share Stats for All Networks', 'socialsnap' ); ?></li>
								<li><?php _e( 'Filter Share Stats by Location', 'socialsnap' ); ?></li>
								<li><?php _e( 'Filter Share Stats by Networks', 'socialsnap' ); ?></li>
							</ul>


							<ul class="ss-col-6 ss-check-list">
								<li><?php _e( 'Filter by Date', 'socialsnap' ); ?></li>
								<li><?php _e( 'Custom Post Types Supported', 'socialsnap' ); ?></li>
								<li><?php _e( 'Track Click to Tweet Engagement', 'socialsnap' ); ?></li>
								<li><?php _e( 'Top Performing Posts', 'socialsnap' ); ?></li>
							</ul>

						</div>

						<div class="ss-upgrade-button">
							<a href="<?php echo socialsnap_upgrade_link(); ?>" target="_blank" class="ss-button"><?php _e( 'Upgrade Social Snap Now', 'socialsnap' ); ?></a>
						</div>
					</div>
				</div><!-- END .ss-upgrade-popup -->
			<?php } ?>

 			<?php do_action( 'socialsnap_statistics_output' ); ?> 

		</div><!-- END #ss-statistics -->
		<?php
	}
}
new SocialSnap_Statistics();