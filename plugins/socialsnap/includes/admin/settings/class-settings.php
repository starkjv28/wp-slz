<?php
/**
 * Social Snap settings page.
 *
 * This class contains all Social Snap settings.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
*/
class SocialSnap_Settings extends SocialSnap_Admin_Page {

	/**
	 * Social Snap settings array.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	var $settings;


	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Call parent constructor
		parent::__construct();

		$this->page_slug = 'settings';

		// Add the page in admin menu
		add_action( 'admin_menu', array( $this, 'register_pages' ), 10 );

		// Redirect to download exported file
		add_action( 'init', array ( $this, 'init' ), 10 );

		// Add the settings menu item to the Plugins table.
		add_filter( 'plugin_action_links_' . plugin_basename( SOCIALSNAP_PLUGIN_FILE ), array( $this, 'settings_link' ) );

		$this->add_ajax_requests();
	}

	/**
	 * Register the pages to be used for the Settings screen.
	 *
	 * @since 1.0.0
	 */
	public function register_pages() {

		// Default Social Snap top level menu item
		add_menu_page(
			__( 'Social Snap', 'socialsnap' ),
			__( 'Social Snap', 'socialsnap' ),
			apply_filters( 'socialsnap_manage_cap', 'manage_options' ),
			'socialsnap-settings',
			array( $this, 'render' ),
			'dashicons-ssbrand',
			apply_filters( 'socialsnap_menu_position', '999.1' )
		);

		// Add Settings submenu page
		add_submenu_page(
			'socialsnap-settings',
			__( 'Social Snap Settings', 'socialsnap' ),
			__( 'Settings', 'socialsnap' ),
			apply_filters( 'socialsnap_welcome_cap', 'manage_options' ),
			'socialsnap-settings',
			array( $this, 'render' )
		);
	}

	/**
	 * Determing if the user is viewing the settings page.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Initialize settings
		$this->initialize_settings();

		// Check what page we are on.
		$page = isset( $_GET['page'] ) ? $_GET['page'] : '';

		// Only load if we are actually on the settings page.
		if ( 'socialsnap-settings' === $page ) {

			add_action( 'socialsnap_settings_init', array( $this, 'export_settings' 	) );
			add_action( 'socialsnap_live_preview', 	array( $this, 'preview_settings' 	) );
			
			// Hook for add-ons
			do_action( 'socialsnap_settings_init' );
		}
	}

	/**
	 * Add Ajax Requests for the Settings panel
	 *
	 * @since 1.0.0
	 */
	public function add_ajax_requests() {

    	add_action( 'wp_ajax_socialsnap_settings_save', array(
			$this,
			'save_settings'
		) );

		add_action( 'wp_ajax_socialsnap_settings_restore', array(
			$this,
			'restore_settings'
		) );

		add_action( 'wp_ajax_socialsnap_settings_import', array(
			$this,
			'import_settings'
		) );

	}

	/**
	 * Initialize the settings array to default values if the settings do not exist in the database
	 *
	 * @since 1.0.0
	 * @param array $settings
	 */
	private function initialize_settings() {

		$this->settings = require_once SOCIALSNAP_PLUGIN_DIR . 'includes/admin/settings/config-settings.php';

		// Stop if settings are not defined
		if ( ! is_array( $this->settings ) ) {
			return;
		}

		// Stop if settings are stored in the database
		if ( is_array( get_option( SOCIALSNAP_SETTINGS ) ) ) {
			return;
		}

		$default_settings = $this->get_default_settings( new ArrayIterator( $this->settings ) );
		$default_settings = apply_filters( 'socialsnap_default_settings', $default_settings );

		// Update the settings in WP Database
		$updated = update_option( SOCIALSNAP_SETTINGS, $default_settings );

		// If updated, set the Social Snap settings variable to default settings.
		if ( $updated ) {
			socialsnap()->settings = get_option( SOCIALSNAP_SETTINGS );
		}

		return $updated;
	}


	/**
	 * Generate array of settings with default values.
	 *
	 * @since 1.0.0
	 * @param ArrayIterator $iterator
	 */
	public function get_default_settings( $iterator ) {

		$result = array();

		while( $iterator->valid() ) {

			$current = $iterator->current();

			if ( isset( $current['type'] ) ) {

				// If current setting is a group or subgroup, go through it's fields.
				if ( $current['type'] == 'group' || $current['type'] == 'subgroup' ) {
					$result = array_merge( $result, $this->get_default_settings( new ArrayIterator( $current['fields'] ) ) );
				} else {
					$result[ $current['id'] ] = isset( $current['default'] ) ? $current['default'] : '';
				}
				
			}

			$iterator->next();
		}

		return $result;
	}

	/**
	 * Save the settings
	 *
	 * @since 1.0.0
	 */
	public function save_settings() {

		// Run a security check
		check_ajax_referer( 'socialsnap-settings', 'nonce' );

		if ( ! current_user_can( apply_filters( 'socialsnap_manage_cap', 'manage_options' ) ) ) {
			wp_send_json_error( array(
				'message' => __( 'Error. Access denied.', 'socialsnap' )
			) );
		}
		
		// Get form data
		$data = isset( $_POST['data'] ) ? $_POST['data'] : null;

		// Parse form data
		parse_str( $data, $data );

		// Sanitize form data
		if ( is_array( $data ) ) {
			$data = array_map('stripslashes_deep', $data );
			array_walk_recursive( $data, function( &$value ) {
    			$value = sanitize_text_field( $value );
			});
		} else {
			$data = stripslashes( $data );
			$data = sanitize_text_field( $data );
		}

		if ( ! empty( $data ) ) {

			// Try to update the options in the database, 
			if ( update_option( SOCIALSNAP_SETTINGS, $data ) ) {
				wp_send_json_success( array(
					'message' => __( 'Saved', 'socialsnap' )
				) );
			} else {
				wp_send_json_success( array(
					'message' => __( 'No changes were made', 'socialsnap' )
				) );
			}
		} else {
			wp_send_json_error( array(
				'message' => __( 'Error. Please reload and try again.', 'socialsnap' )
			) );
		}
	}

	/**
	 * Restore the settings
	 *
	 * @since 1.0.0
	 */
	public function restore_settings() {

		// Run a security check
		check_ajax_referer( 'socialsnap-settings', 'nonce' );

		if ( ! current_user_can( apply_filters( 'socialsnap_manage_cap', 'manage_options' ) ) ) {
			wp_send_json_error( array(
				'message' => __( 'Error. Access denied.', 'socialsnap' )
			) );
		}

		// Delete option
		delete_option( SOCIALSNAP_SETTINGS );

		// Initialize with default values
		// $this->initialize_settings();

		// Return
		wp_send_json_success( array(
			'message' => esc_html__( 'Settings restored. Reloading...', 'socialsnap' )
		) );
	}

	/**
	 * Import the settings
	 *
	 * @since 1.0.0
	 */
	public function import_settings() {
		
		// Run a security check
		check_ajax_referer( 'socialsnap-settings', 'nonce' );

		if ( ! current_user_can( apply_filters( 'socialsnap_manage_cap', 'manage_options' ) ) ) {
			wp_send_json_error( array(
				'message' => __( 'Error. Access denied.', 'socialsnap' )
			) );
		}

		if ( empty( $_FILES['file']['tmp_name'] ) ) {
			wp_send_json_error( array(
				'code' 		=> 'no-file-uploaded', 
				'message'  	=> esc_html__( 'No file uploaded', 'socialsnap' )
			) );
		}

		$ext = strtolower( pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION ) );

		if ( 'json' !== $ext ) {
			wp_send_json_error( array(
				'code' 		=> 'wrong-format',
				'message'	=> esc_html__( 'Please upload a valid .json form export file', 'socialsnap' )
			) );
		}

		$import_data = json_decode( file_get_contents( $_FILES['file']['tmp_name'] ), true );

		if ( ! empty( $import_data ) && is_array( $import_data ) ) {
			if ( update_option( SOCIALSNAP_SETTINGS, $import_data ) ) {
				wp_send_json_success( array( 
					'code'		=> 'success',
					'message'	=> esc_html__( 'Settings imported successfully. Reloading...', 'socialsnap' )
				) );
			} else {
				wp_send_json_error( array(
					'code'		=> 'already-saved',
					'message'	=> esc_html__( 'Imported settings are identical as current settings.', 'socialsnap' )
				) );	
			}
		}

		wp_send_json_error( array(
			'code'		=> 'could-not-complete',
			'message'	=> esc_html__( 'Import could not be finished', 'socialsnap' )
		) );

	}

	/**
	 * Export the settings
	 *
	 * @since 1.0.0
	 */
	public function export_settings() {

		if ( ! isset( $_GET['ss_export_settings'] ) || ! current_user_can( apply_filters( 'socialsnap_manage_cap', 'manage_options' ) ) ) {
			return;
		}

		ignore_user_abort( true );
		nocache_headers();

		header('Content-disposition: attachment; filename=socialsnap-export-' . date( 'm-d-Y' ) . '.json');
		header('Content-type: text/html');
		header("Expires: 0" );

		echo wp_json_encode( get_option( SOCIALSNAP_SETTINGS ) );
		exit;
	}

	/**
	 * Render save button for the settings panel.
	 *
	 * @since 1.0.0
	 */
	public function render_save_action() { ?>

		<div class="ss-save-button" data-nonce="<?php echo wp_create_nonce( 'socialsnap-settings' ); ?>" data-save-text="<?php _e( 'Save Changes', 'socialsnap' ); ?>" data-saved-text="<?php _e( 'Saved', 'socialsnap' ); ?>">
			<span class="spinner"></span>
			<span class="ss-save-button-label"><?php _e( 'Saved', 'socialsnap' ); ?></span>
		</div><!-- END .ss-save-button -->
	<?php }
	
	/**
	 * Load assets for the Settings page.
	 *
	 * @since 1.0.0
	 */
	public function load_assets( $hook ) {

		// Do not load if we are not Settings page
		if ( $hook != 'toplevel_page_socialsnap-' . $this->page_slug ) {
			return;
		}

		wp_enqueue_style(
			'socialsnap-admin-settings',
			SOCIALSNAP_PLUGIN_URL . 'assets/css/admin-settings.css',
			null,
			SOCIALSNAP_VERSION
		);

		wp_enqueue_script(
			'socialsnap-settings-js',
			SOCIALSNAP_PLUGIN_URL . 'assets/js/admin-settings.js',
			array( 'jquery' ),
			SOCIALSNAP_VERSION,
			true
		);

		// Localize variables to be used in plugin JavaScript files.
		$strings = array(
			'ajaxurl' 		=> admin_url( 'admin-ajax.php' ),
			'wait_text' 	=> __( 'Please wait&hellip;', 'socialsnap' ),
			'nonce'			=> wp_create_nonce( 'socialsnap-admin' ),
		);

		wp_localize_script(
			'socialsnap-settings-js',
			'socialsnap_admin',
			$strings
		);

		// Enqueue WordPress media upload
		wp_enqueue_media();

		// Load common assets from parent class.
		parent::load_assets( $hook );
	}

	/**
	 * Add Settings page and Go Pro to plugin action links in the Plugins table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links  Default plugin action links.
	 * @return array $links Amended plugin action links.
	 */
	public function settings_link( $links ) {

		$settings_link = '<a href="' . admin_url( 'admin.php?page=socialsnap-settings' ) . '">' . __( 'Settings', 'socialsnap' ) . '</a>';

		array_unshift( $links, $settings_link );

		$links['go_pro'] = '<a href="' . admin_url(  'admin.php?page=socialsnap-addons') . '">' . __( 'Addons', 'socialsnap' ) . '</a>';

		return $links;
	}

	/**
	 * Returns one random line for the bottom of the settings screen.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links  Default plugin action links.
	 * @return array $links Amended plugin action links.
	 */
	private function get_random_fact() {

		$facts = array(
			__( 'If you like <strong>Social Snap</strong> please leave us a <a href="https://wordpress.org/plugins/socialsnap/">★★★★★</a> rating to help us spread the word. Thank you!', 'socialsnap' ),
			__( 'Bring your old posts to life by automatically posting them on your social media.', 'socialsnap' ),
			__( 'Completely automate posting on social media whenever you publish a new article.', 'socialsnap' ),
			__( 'Allow your users to log into your website through their favorite social networks.', 'socialsnap' ),
			__( 'Unlock 30+ share providers, share counters for all providers, URL shortening and more awesome features!', 'socialsnap' ),
		);

		$facts = apply_filters( 'socialsnap_facts', $facts );

		return $facts[ array_rand( $facts ) ];
	}

	/**
	 * Render settings preview screen
	 *
	 * @since 1.0.0
	 */
	public function preview_settings() {

		$preview_social_share = socialsnap_get_social_share_positions();

		if ( is_array( $preview_social_share ) && ! empty( $preview_social_share ) ) {
			foreach ( $preview_social_share as $element ) {
				?>
				<div class="ss-preview-screen ss-preview-social_share_<?php echo $element; ?>">
					
					<?php if ( 'on_media' === $element ) { ?>

						<div class="ss-image-preview ss-on-media-container ss-bottom-left-position ss-with-overlay">
							<span class="ss-on-media-image-wrap">
								<?php do_action( 'preview_social_share_' . $element ); ?>
								<img src="<?php echo SOCIALSNAP_PLUGIN_URL . 'assets/images/image-placeholder.jpg'; ?>" />
							</span>
						</div><!-- END .ss-image-preview -->

					<?php } elseif ( 'inline_content' === $element ) { ?>

						<div class="ss-live-preview-placeholder">

							<div class="ss-live-preview-inline-content-mask-wrapper">

								<div class="ss-live-preview-mask ss-title-type"></div>
								<div class="ss-live-preview-mask ss-subtitle-type"></div>

								<div class="ss-ss-inline-content-before">
									<?php do_action( 'preview_social_share_' . $element ); ?>
								</div>

								<?php for ( $i = 1; $i <= 6; $i++ ) { ?>
									<div class="ss-live-preview-mask" style="width: <?php echo rand(50, 100); ?>%;"></div>
								<?php } ?>

								<div class="ss-ss-inline-content-after">
									<?php do_action( 'preview_social_share_' . $element ); ?>
								</div>

							</div>

						</div><!-- END .ss-live-preview-placeholder -->

					<?php } else {
						do_action( 'preview_social_share_' . $element ); 
					} ?>

				</div>
				<?php
			}
		}	
	}


	/**
	 * Render field
	 *
	 * @since 1.0.0
	 */
	private function render_field( $field, $level = 0 ) {

		$ul_class 	= '';
		$help_class = ' ss-without-desc';

		if ( isset ( $field['desc'] ) ) {
			$help_class = '';
		}

		if ( 0 === $level ) {
			$ul_class = 'ss-parent-menu';
		}

		?>
		<ul id="<?php echo $field['id']; ?>" class="<?php echo $ul_class; ?>">
			
			<div class="ss-customize-info<?php echo $help_class; ?>">
				<a href="#"></a>
				<p>
					<?php if ( isset( $field['parent_name'] ) ) { ?>
						<span class="ss-customize-desc"><?php echo $field['parent_name']; ?></span>
					<?php } ?>
					<?php echo $field['name']; ?>
				</p>

				<?php if ( isset ( $field['desc'] ) ) { ?>
					<i class="ss-help-button ss-question-mark"></i>
				<?php } ?>
			</div>

			<?php if ( isset ( $field['desc'] ) ) { ?>
				<div class="ss-help-description">
					<p><?php echo $field['desc']; ?></p>
				</div><!-- END .ss-help-description -->
			<?php } 

			if ( isset( $field['fields'] ) ) {
				
				if ( isset( $field['type'] ) && 'group' == $field['type'] ) {
					if ( is_array( $field['fields'] ) && ! empty( $field['fields'] ) ) {
						foreach ( $field['fields'] as $sub_id => $sub_settings ) { ?>

							<li>

							<?php $require_upgrade = socialsnap_settings_require_upgrade( $sub_settings ); ?>

							<?php if ( $require_upgrade ) { ?>
								<span class="ss-pro-feature">
												
									<?php if ( isset( $sub_settings['icon'] ) ) { ?>
										<i class="ss <?php echo $sub_settings['icon']; ?>"></i>
									<?php } ?>

									<span>
										<?php echo $sub_settings['name']; ?>
									</span>
									
									<?php
									$upgrade = '';
												
									if ( isset( $field['pro_info'] ) ) {
										$upgrade = $field['pro_info'];
									} elseif ( isset( $field['desc'] ) ) {
										$upgrade = $field['desc'];
									}
									
									socialsnap_settings_upgrade_button( $upgrade );
									?>
								</span>
							<?php } else { ?>
								<a href="#<?php echo $sub_id; ?>">
									<?php if ( isset( $sub_settings['icon'] ) ) { ?>
										<i class="ss <?php echo $sub_settings['icon']; ?>"></i>
									<?php } ?>
									<?php echo $sub_settings['name']; ?>
								</a>
							<?php } ?>

							</li>

							<?php 
						}
					}
				} else {
					?>
					<div class="ss-fields-wrapper">
						<?php 
						if ( is_array( $field['fields'] ) && ! empty( $field['fields'] ) ) {
							foreach ( $field['fields'] as $sub_id => $sub_settings ) {
								$sub_settings['value'] = SocialSnap_Fields::saved_value( SOCIALSNAP_SETTINGS, $sub_settings );
								echo SocialSnap_Fields::build_field( $sub_settings );
							}
						}
						?>
					</div><!-- END .ss-fields-wrapper -->
					<?php
				}
			}
			?>
		</ul>
		
		<?php 
		if ( isset( $field['type'] ) && 'group' == $field['type'] ) {
			if ( is_array( $field['fields'] ) && ! empty( $field['fields'] ) ) {
				$level++;
				foreach ( $field['fields'] as $sub_id => $sub_settings ) {
					$this->render_field( $sub_settings, $level );
				}
			}
		}
	}

	/**
	 * Build the output for the plugin settings page.
	 *
	 * @since 1.0.0
	 */
	public function render() {

		do_action( 'socialsnap_admin_settings_before', $this->settings );
		?>

		<div id="ss-settings-wrapper" class="ss-saved ss-clearfix">

			<form method="post" action="#" enctype="multipart/form-data" id="ss-settings-form">

				<div id="ss-left-panel">
					<ul id="ss-main-settings" class="ss-grandparent-menu">
						
						<div class="ss-customize-info">
							<p><?php _e( 'Settings', 'socialsnap' ); ?></p>
						</div>

						<?php 
						if ( is_array( $this->settings ) && ! empty( $this->settings ) ) {
							foreach ( $this->settings as $id => $field ) {
								?>
								<li>
									<?php $require_upgrade = socialsnap_settings_require_upgrade( $field ); ?>
									<?php if ( $require_upgrade ) { ?>
											<span class="ss-pro-feature">
												
												<?php if ( isset( $field['icon'] ) ) { ?>
													<i class="ss <?php echo $field['icon']; ?>"></i>
												<?php } ?>

												<span>
													<?php echo $field['name']; ?>
												</span>

												<?php
												$upgrade = '';
												
												if ( isset( $field['pro_info'] ) ) {
													$upgrade = $field['pro_info'];
												} elseif ( isset( $field['desc'] ) ) {
													$upgrade = $field['desc'];
												}

												socialsnap_settings_upgrade_button( $upgrade );
												?>
											</span>
									<?php } else { ?>
											<a href="#<?php echo $id; ?>">
												<?php if ( isset( $field['icon'] ) ) { ?>
													<i class="ss <?php echo $field['icon']; ?>"></i>
												<?php } ?>
												<?php echo $field['name']; ?>
											</a>
									<?php } ?>
								</li>
								<?php 
							}
						} ?>
					</ul>

					<?php 
					if ( is_array( $this->settings ) && ! empty( $this->settings ) ) {
						foreach ( $this->settings as $id => $field ) {
							$this->render_field( $field );
						}
					} ?>

					<?php $this->render_save_action(); ?>

				</div><!-- END #ss-left-panel -->

				<div id="ss-right-panel" class="ss-no-previews">

					<div class="ss-live-preview-placeholder">
						<span class="ss-live-preview-title"><?php _e( 'Live Preview', 'socialsnap' ); ?></span>

						<div class="ss-live-preview-mask-wrapper">

							<div class="ss-live-preview-mask ss-title-type"></div>
							<div class="ss-live-preview-mask ss-subtitle-type"></div>
							<?php for ( $i = 1; $i <= 10; $i++ ) { ?>
								<div class="ss-live-preview-mask" style="width: <?php echo rand(50, 100); ?>%;"></div>
							<?php } ?>

						</div>

					</div><!-- END .ss-live-preview-placeholder -->
					
					<?php do_action( 'socialsnap_live_preview' ); ?>

					<div class="ss-bottom-bar ss-clearfix">
						<p><?php echo $this->get_random_fact(); ?></p>

						<?php if ( ! socialsnap()->pro ) { ?>
							<a href="<?php echo socialsnap_upgrade_link(); ?>" target="_blank" class="ss-upgrade ss-button"><i class="dashicons-ssbrand"></i><?php _e( 'Upgrade Now', 'socialsnap' ); ?></a>
						<?php } else { ?>
							<a href="https://socialsnap.com/docs/" target="_blank" class="ss-upgrade ss-button"><i class="dashicons-ssbrand"></i><?php _e( 'Visit Help Center', 'socialsnap' ); ?></a>
						<?php } ?>

					</div><!-- END .ss-bottom-bar -->

				</div><!-- END #ss-right-panel -->

			</form>

		</div><!-- END #ss-settings-wrapper -->
		<?php
	}
}

new SocialSnap_Settings();