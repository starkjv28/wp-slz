<?php
/**
 * Social Snap social share networks field.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
*/
class SocialSnap_Field_social_share_networks {


	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */	
	function __construct( $value ) {
		$this->field 		= $value['type'];
		$this->name 		= $value['name'];
		$this->id 			= $value['id'];
		$this->default 		= isset( $value['default'] )     ? $value['default'] : '';
		$this->value 		= isset( $value['value'] )       ? $value['value']   : '';
		$this->description 	= isset( $value['desc'] ) 		 ? $value['desc']    : '';
		$this->dependency 	= isset( $value['dependency'] )  ? $value['dependency'] : '';


		if ( ! wp_script_is( 'jquery-ui-sortable', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
		}

		add_action( 'socialsnap_live_preview', array( $this, 'add_network_popup' ) );
	}

	/**
	 * Popup to select a network
	 *
	 * @since 1.0.0
	 */
	public function add_network_popup() {

		$all_networks 	= socialsnap_get_social_share_networks();
		$mobile_only 	= socialsnap_get_mobile_only_social_share_networks();
		?>

		<div id="ss-ss-networks-popup" class="ss-add-networks-popup ss-hidden">
			<h4>
				<?php _e( 'Add Networks', 'socialsnap' ); ?>
				<a href="#" id="ss-close-share-networks-modal" class="ss-close-modal"><i class="ss ss-close"></i></a>
			</h4>

			<div class="ss-popup-content">
				<div class="ss-popup-networks ss-clearfix">

					<?php foreach ( $all_networks as $network => $name ) { ?>
						<div class="ss-popup-network">
							<a href="#" data-id="<?php echo $network; ?>" data-name="<?php echo $name; ?>" data-mobile-only="<?php echo in_array( $network, array_keys( $mobile_only ) ); ?>" class="ss-<?php echo $network; ?>-color"><i class="ss ss-<?php echo $network; ?>"></i><?php echo $name; ?><span><i class="ss ss-plus"></i></span></a>
						</div>
					<?php } ?>

				</div><!-- END .ss-popup-networks -->

				<?php if ( ! socialsnap()->pro ) { ?>
					<div class="ss-pro-notice">
						<?php
						_e( 'Unlock 30+ share networks, share counters for all networks, URL shortening, custom share images and descriptions and more awesome features!', 'socialsnap' ); ?> 
						<a href="<?php echo socialsnap_upgrade_link(); ?>" target="_blank"><?php _e( 'Upgrade Now.', 'socialsnap' ); ?></a>
					</div><!-- END .ss-pro-notice -->
				<?php } ?>
				
			</div><!-- END .ss-popup-content -->

		</div><!-- END #ss-ss-networks-popup -->
		<?php 
	}

	/**
	 * HTML output of the field
	 *
	 * @since 1.0.0
	 */
	public function render() { 

		$mobile_only 		= socialsnap_get_mobile_only_social_share_networks();
		$values 			= apply_filters( 'socialsnap_filter_social_share_networks', $this->value );
		$values['order'] 	= isset( $values['order'] ) ? $values['order'] : '';
		$order 				= explode( ';', trim( $values['order'] ) );
		
		ob_start(); ?>

		<div id="<?php echo $this->id; ?>_wrapper" class="ss-field-wrapper ss-clearfix"<?php echo SocialSnap_Fields::dependency_builder( $this->dependency ); ?>>

			<div class="ss-field-title">
				<?php echo $this->name; ?>

				<?php if ( $this->description ) { ?>
					<i class="ss-tooltip ss-question-mark" data-title="<?php echo $this->description; ?>"></i>
				<?php } ?>	
			</div>

			<div class="ss-field-element ss-share-networks ss-clearfix" id="<?php echo $this->id; ?>">

			<?php 
			if ( is_array( $order ) && count( $order ) == 1 && ! $order[0] ) {
				$order = array();
			}

			if ( is_array( $order ) && ! empty( $order ) ) {
				foreach ( $order as $network ) {

					if ( ! isset( $values[ $network ]['desktop_visibility'] ) ) {
						$values[ $network ]['desktop_visibility'] = false;
					}

					if ( ! isset( $values[ $network ]['mobile_visibility'] ) ) {
						$values[ $network ]['mobile_visibility'] = false;
					}

					if ( ! isset( $values[ $network ]['text'] ) ) {
						$values[ $network ]['text'] = ucfirst($network);
					}					
					?>
					<div class="ss-ss-network" data-id="<?php echo $network; ?>">
						<i class="ss ss-<?php echo $network; ?> ss-<?php echo $network; ?>-color"></i>
						<input type="text" class="ss-ss-name" name="<?php echo $this->id; ?>[<?php echo $network; ?>][text]" value="<?php echo $values[ $network ]['text']; ?>" placeholder="<?php _e( 'Enter network label', 'socialsnap' ); ?>" />

						<div class="ss-ss-actions">
							<a href="#" class="ss-ss-edit ss-tooltip" data-title="<?php _e( 'Change label', 'socialsnap' ); ?>"><i class="ss ss-edit"></i></a>
							
							<div class="ss-ss-mobile-visibility ss-tooltip" data-title="<?php _e('Device visibility', 'socialsnap'); ?>"><i class="ss ss-eye"></i>
								<ul class="ss-ss-visibility-dropdown">
									
									<?php 
									if ( ! in_array( $network, array_keys( $mobile_only ) ) ) { ?>
										<li><?php _e('Desktop', 'socialsnap'); ?>
											<span class="ss-small-toggle">
												<input type="checkbox" id="ss-ss-visibility-desktop-<?php echo $network; ?>" name="<?php echo $this->id; ?>[<?php echo $network; ?>][desktop_visibility]" <?php checked( $values[ $network ]['desktop_visibility'], 'on' ); ?>>
												<label for="ss-ss-visibility-desktop-<?php echo $network; ?>"></label>
											</span>
										</li>

										<li><?php _e('Mobile', 'socialsnap'); ?>
											<span class="ss-small-toggle">
												<input type="checkbox" id="ss-ss-visibility-mobile-<?php echo $network; ?>" name="<?php echo $this->id; ?>[<?php echo $network; ?>][mobile_visibility]" <?php checked( $values[ $network ]['mobile_visibility'], 'on' ); ?>>
												<label for="ss-ss-visibility-mobile-<?php echo $network; ?>"></label>
											</span>
										</li>
									<?php 
									} else {
										esc_html_e( 'Mobile only', 'socialsnap' ); 
									}
									?>
									
								</ul>
							</div><!-- END .ss-ss-mobile-visibility -->

							<a href="#" class="ss-ss-remove ss-tooltip" data-title="<?php _e( 'Remove', 'socialsnap' ); ?>"><i class="ss ss-close"></i></a>
						</div>
					</div><!-- END .ss-ss-network -->
				<?php
				}
			} 
			?>

			<input type="hidden" name="<?php echo $this->id; ?>[order]" value="<?php echo $values['order']; ?>" class="ss-social-share-order"/>
			</div>

			<a href="#" class="ss-button ss-secondary ss-small-button" id="ss-add-share-networks"><i class="ss ss-plus"></i><?php _e('Add Networks', 'socialsnap'); ?></a>

			<div class="ss-note">
				<em><?php _e( 'These networks apply to all locations.', 'socialsnap' ); ?></em>
			</div>
		</div>

		<?php
		return ob_get_clean();			
	}
}