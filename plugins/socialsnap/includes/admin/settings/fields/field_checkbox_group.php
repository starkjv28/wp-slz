<?php
/**
 * Social Snap checkbox group field.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
*/
class SocialSnap_Field_checkbox_group {
	
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
		$this->options 	 	= isset( $value['options'] ) 	 ? $value['options'] : '';
		$this->dependency 	= isset( $value['dependency'] )  ? $value['dependency'] : '';
		$this->source 		= isset( $value['source'] )		 ? $value['source']	: '';
	}

	/**
	 * HTML output of the field
	 *
	 * @since 1.0.0
	 */
	public function render() { 

		$values = $this->value;

		// Check if we need to generate options from wp
		if ( is_array( $this->source ) && ! empty( $this->source ) ) {
			foreach ( $this->source as $src ) {
				
				switch ( $src ) {
					case 'post_type':
						$ss_pt = socialsnap_get_post_types();
						
						if ( is_array( $ss_pt ) && ! empty( $ss_pt ) ) {
							foreach ( $ss_pt as $key => $value) {
								$this->options[ $key ] = array(
									'title' => $value,
								);
							}
						}

						// Add Shop option.
						if ( class_exists( 'WooCommerce' ) ) {
							$this->options[ 'shop' ] = array( 
								'title' => __( 'Shop', 'socialsnap' ),
							);
						}

						break;
					
					default:
						break;
				}
			}
		}

		ob_start();
		?>
		<div id="<?php echo $this->id; ?>_wrapper" class="ss-field-wrapper ss-field-spacing ss-clearfix"<?php echo SocialSnap_Fields::dependency_builder( $this->dependency ); ?>>

			<div class="ss-field-title">
				<?php echo $this->name; ?>

				<?php if ( $this->description ) { ?>
					<i class="ss-tooltip ss-question-mark" data-title="<?php echo $this->description; ?>"></i>
				<?php } ?>
			</div>

			<div class="ss-field-element ss-clearfix">
				<?php if ( is_array( $this->options ) && ! empty( $this->options ) ) { ?>

					<div class="ss-checkbox-group">
					<?php
					foreach ( $this->options as $id => $settings ) {

						if ( ! isset ( $values[ $id ] ) ) {
							$values[ $id ] = false;
						}

						if ( 1 == $values[ $id ] || true == $values[ $id ] ) {
							$values[ $id ] = 'on';
						}
						?>
							<span class="ss-checkbox">
								<input type="checkbox" id="ss-sl-<?php echo $this->id; ?>-<?php echo $id; ?>" name="<?php echo $this->id; ?>[<?php echo $id; ?>]" <?php checked( 'on', $values[ $id ], true ); ?>/>
								<label for="ss-sl-<?php echo $this->id; ?>-<?php echo $id; ?>"><?php echo $settings['title']; ?></label>

								<?php if ( isset( $settings['desc'] ) ) { ?>
									<span class="ss-additional-info ss-tooltip" data-title="<?php echo $settings['desc']; ?>">?</span>
								<?php } ?>

							</span>
						<?php 
					} ?>
					</div>

				<?php } ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}