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
class SocialSnap_Field_social_follow_networks {

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

		$this->value 				= apply_filters( 'socialsnap_filter_social_follow_networks', $this->value );
		$this->follow_networks 		= socialsnap_get_social_follow_networks();
		$this->follow_networks_api 	= socialsnap_social_follow_networks_with_api();
		$this->authorized_networks  = apply_filters( 
			'socialsnap_filter_social_follow_networks', 
			get_option( 'socialsnap_authorized_networks' )
		);

		// Make sure that all networks are going to be listed, required when upgraded to PRO.
		$values_keys = array_keys( $this->value );

		foreach ( $this->follow_networks as $id => $name ) {

			if ( ! in_array( $id, $values_keys ) ) {
				
				$this->value[ $id ] = array(
					'profile'	=> array(
						'id'		=> '',
						'username'	=> '',
						'url'		=> '',
					),
					'label'		=> sprintf( __( 'Follow us on %1$s', 'socialsnap' ), socialsnap_get_network_name( $id ) ),
					'manual_followers'	=> '',
				);
			}
		}

		unset( $this->value['order'] );
		$this->value['order'] = implode( ';', array_keys( $this->value ) );

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

		$network_order = explode( ';', trim( $this->value['order'] ) );
		?>

		<div id="ss-sf-networks-popup" class="ss-add-networks-popup ss-hidden">

			<h4>
				<?php _e( 'Setup', 'socialsnap' ); ?>
				<span class="ss-sf-setup-title"></span>
				<a href="#" id="ss-close-follow-networks-modal" class="ss-close-modal"><i class="ss ss-close"></i></a>
			</h4>

			<div class="ss-popup-content">

				<?php foreach ( $network_order as $network_id ) {

					$network_name = socialsnap_get_network_name( $network_id );

					$network_settings = array(
						'access_token'			=> '',
						'access_token_secret'	=> '',
						'profile'				=> '',
						'network_key_index'		=> '',
						'manual_followers'		=> '',
						'label'					=> sprintf( __( 'Follow on %1$s', 'socialsnap' ), $network_name ),
						'authorized'			=> false,
					);

					$network_settings 	= isset( $this->authorized_networks[ $network_id ] ) ? wp_parse_args( $this->authorized_networks[ $network_id ], $network_settings ) : $network_settings; 
					$disconnect_url		= add_query_arg( array( 'network_disconnect' => $network_id, 'page' => 'socialsnap-settings#ss_social_follow_networks_display-ss' ), admin_url( 'admin.php' ) );

					$readonly_field = '';
					?>

					<div class="ss-sf-network-settings ss-sf-network-settings-<?php echo $network_id; ?> ss-hidden" data-network="<?php echo $network_id; ?>">

						<?php if ( in_array( $network_id, $this->follow_networks_api ) ) { ?>
						<p>
							<label>
								<strong><?php printf( __( 'Connect %1$s Account', 'socialsnap' ), $network_name ); ?></strong>
								<?php 
									_e( 'Authorize Social Snap to automatically obtain followers count from your account. The count is updated daily.' ); 
								?>
							</label><br/>

							<?php if ( $network_settings['access_token'] && $network_settings[ 'access_token_secret'] ) { ?>

								<span class="ss-authenticated-user">
									<i class="dashicons dashicons-yes ss-authenticated-badge"></i> 

									<?php if ( isset( $network_settings['profile']['username'], $network_settings['profile']['url'] ) && $network_id !== 'facebook' && $network_id !== 'linkedin' ) { 

										printf( __( 'Connected as %1$s.', 'socialsnap' ), $network_settings['profile']['username'] ); 
										$readonly_field = ' readonly'; ?>

									<?php } elseif ( $network_id == 'linkedin' || $network_id == 'facebook' ) {

										$readonly_field = ' readonly';

										$this->value[ $network_id ]['accounts'] = isset( $this->value[ $network_id ]['accounts'] ) ? $this->value[ $network_id ]['accounts'] : '';

										if ( is_array( $network_settings['accounts'] ) && ! empty( $network_settings['accounts'] ) ) { 
											_e( 'Connect as:', 'socialsnap' );

											?>

											<select name="<?php echo $this->id; ?>[<?php echo $network_id; ?>][accounts]" id="<?php echo $this->id; ?>_<?php echo $network_id; ?>_accounts">
												<?php foreach ( $network_settings['accounts'] as $account ) { ?>
													
													<option value="<?php echo $account['id']; ?>" <?php selected( $account['id'], $this->value[ $network_id ]['accounts'], true ); ?> data-slug="<?php echo $account['slug']; ?>"><?php echo $account['name']; ?></option>

												<?php } ?>
											</select>

										<?php } ?>

									<?php } ?>

									<a href="<?php echo $disconnect_url; ?>" data-network="<?php echo $network_id; ?>" class="ss-disconnect-authenticated-user"><?php _e( 'Disconnect?', 'socialsnap' ); ?></a>
									<span class="spinner ss-ntm"></span>
									
								</span><!-- END .ss-authenticated-user -->

							<?php } else {

								$url = add_query_arg(
									array(
										'network'		=> $network_id,
										'client_url'	=> rawurlencode( add_query_arg( array( 'page' => 'socialsnap-settings#ss_social_follow_networks_display-ss' ), admin_url( 'admin.php' ) ) )
									), 'https://socialsnap.com/wp-json/api/v1/authorize' );
								?>

								<a href="<?php echo $url; ?>" data-network="<?php echo $network_id; ?>" class="ss-follow-authorize ss-button"><?php _e( 'Authorize', 'socialsnap' ); ?></a>
								<span class="spinner"></span>

							<?php } ?>
						</p>
						<?php } ?>

						<p>

							<!-- Profile ID / Username -->
							<label for="<?php echo $this->id; ?>_<?php echo $network_id; ?>_profile_username">
								<strong><?php _e( 'Username', 'socialsnap' ); ?></strong>
								<?php _e( 'Enter your profile ID/username (without @ prefix).', 'socialsnap' ); ?>
							</label><br/>

							<input type="text" name="<?php echo $this->id; ?>[<?php echo $network_id; ?>][profile][username]" id="<?php echo $this->id; ?>_<?php echo $network_id; ?>_profile_username" value="<?php echo ( isset( $this->value[ $network_id ]['profile']['username'] ) ? $this->value[ $network_id ]['profile']['username'] : '' ); ?>"<?php echo $readonly_field; ?> class="ss-follow-username-profile"/>

							<?php if ( $readonly_field && $readonly_field !== '' ) { ?>
								<small><?php _e( 'Username obtained automatically.' ); ?></small>
							<?php } ?>

						</p>

						<p>

							<!-- Profile URL -->
							<label for="<?php echo $this->id; ?>_<?php echo $network_id; ?>_profile_url">
								<strong><?php _e( 'Profile URL', 'socialsnap' ); ?></strong>
								<?php _e( 'Enter your profile URL (including http://).', 'socialsnap' ); ?>
							</label><br/>

							<input type="text" name="<?php echo $this->id; ?>[<?php echo $network_id; ?>][profile][url]" id="<?php echo $this->id; ?>_<?php echo $network_id; ?>_profile_url" placeholder="<?php _e('http://') ?>" value="<?php echo ( isset( $this->value[ $network_id ]['profile']['url'] ) ? $this->value[ $network_id ]['profile']['url'] : '' ); ?>"<?php echo $readonly_field; ?> class="ss-follow-username-profile"/>

							<?php if ( $readonly_field && $readonly_field !== '' ) { ?>
								<small><?php _e( 'Profile URL obtained automatically.' ); ?></small>
							<?php } ?>
						</p>

						<p>
							<label for="<?php echo $this->id; ?>_<?php echo $network_id; ?>_label">
								<strong><?php _e( 'Button Label', 'socialsnap' ); ?></strong>
								<?php _e( 'Specify the label that will be displayed on follow buttons.', 'socialsnap' ); ?>
							</label><br/>

							<input type="text" name="<?php echo $this->id; ?>[<?php echo $network_id; ?>][label]" id="<?php echo $this->id; ?>_<?php echo $network_id; ?>_label" value="<?php echo $this->value[ $network_id ]['label']; ?>"/>
						</p>

						<?php if ( 'pinterest' != $network_id ) { ?>
							<p>
								<label for="<?php echo $this->id; ?>_<?php echo $network_id; ?>_manual_followers">
									<strong><?php _e( 'Number of Followers', 'socialsnap' ); ?></strong>
									<?php _e( 'Manually enter your number of followers as a backup method for followers count.', 'socialsnap' ); ?>
								</label>
								<input type="number" name="<?php echo $this->id; ?>[<?php echo $network_id; ?>][manual_followers]" id="<?php echo $this->id; ?>_<?php echo $network_id; ?>_manual_followers" value="<?php echo $this->value[ $network_id ]['manual_followers']; ?>"/>
							</p>
						<?php } ?>
					</div>
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

		$network_order = explode( ';', trim( $this->value['order'] ) );

		ob_start(); ?>

		<div id="<?php echo $this->id; ?>_wrapper" class="ss-field-wrapper ss-clearfix"<?php echo SocialSnap_Fields::dependency_builder( $this->dependency ); ?>>

			<div class="ss-field-title">
				<?php echo $this->name; ?>

				<?php if ( $this->description ) { ?>
					<i class="ss-tooltip ss-question-mark" data-title="<?php echo $this->description; ?>"></i>
				<?php } ?>	
			</div><!-- END .ss-field-title -->

			<div class="ss-field-element ss-follow-networks ss-clearfix" id="<?php echo $this->id; ?>">
				<?php
				foreach ( $network_order as $network_id ) { 

					$network_name = socialsnap_get_network_name( $network_id );

					if ( ! isset( $this->value[ $network_id ]['label'] ) ) {
						$this->value[ $network_id ]['label'] = '';
					}

					if ( ! isset( $this->value[ $network_id ]['manual_followers'] ) ) {
						$this->value[ $network_id ]['manual_followers'] = '';
					}

					$network_settings = array(
						'access_token'			=> '',
						'access_token_secret'	=> '',
						'profile'				=> '',
						'network_key_index'		=> '',
						'authorized'			=> false,
					);

					$network_settings = isset( $this->authorized_networks[ $network_id ] ) ? wp_parse_args( $this->authorized_networks[ $network_id ], $network_settings ) : $network_settings; ?>

					<div class="ss-follow-network" data-id="<?php echo $network_id; ?>" data-name="<?php echo $network_name; ?>">

						<i class="ss ss-<?php echo $network_id; ?>-color ss-<?php echo $network_id; ?>"></i>
						
						<?php if ( isset( $this->value[ $network_id ]['profile']['username'] ) && $this->value[ $network_id ]['profile']['username'] ) { ?>
							<span class="ss-follow-network-name ss-follow-network-account"><?php echo $this->value[ $network_id ]['profile']['username']; ?></span>
						<?php } else { ?>
							<span class="ss-follow-network-name"><?php echo $network_name; ?></span>
						<?php } ?>

						<span class="ss-followers-badge">
							<?php if ( $this->has_automatic_followers( $network_id, $network_name ) ) { ?>
								<i class="dashicons dashicons-yes ss-authenticated-badge ss-tooltip" data-title="<?php _e( 'Followers obtained automatically', 'socialsnap' ); ?>"></i>
							<?php } ?>
						</span>

						<span class="ss-follow-network-buttons"><a href="#" class="ss-configure-follow-network"><i class="ss ss-edit"></i> <?php _e( 'Setup', 'socialsnap' ); ?></a></span>
					</div>
				<?php }
				?>
			</div><!-- END .ss-follow-networks -->

			<input type="hidden" name="<?php echo $this->id; ?>[order]" value="<?php echo $this->value['order']; ?>" class="ss-social-follow-order"/>

		</div><!-- END .ss-field-wrapper -->

		<?php
		return ob_get_clean();			
	}

	
	/**
	 * Check if network supports automatic follower count
	 * 
	 * @param  string  $network      Network ID
	 * @param  string  $network_name Network Name
	 * @return boolean               
	 * @since 1.0.0
	 */
	private function has_automatic_followers( $network, $network_name ) {

		$network_settings = array(
			'access_token'			=> '',
			'access_token_secret'	=> '',
			'profile'				=> '',
			'network_key_index'		=> '',
			'authorized'			=> false,
		);

		$network_settings = isset( $this->authorized_networks[ $network ] ) ? wp_parse_args( $this->authorized_networks[ $network ], $network_settings ) : $network_settings;

		if ( in_array( $network, $this->follow_networks_api ) ) {
			return $network_settings['authorized'];
		}

		if ( 'pinterest' == $network && isset( $this->value['pinterest']['profile']['username'] ) && $this->value['pinterest']['profile']['username'] ) {
			return true;
		}

		return false;
	}
}