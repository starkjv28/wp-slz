<?php
/**
 * Social Snap toggle field.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
*/
class SocialSnap_Field_toggle {
	
	/**
	 * Primary class constructor.
	 *
	 * @since 4.0
	 */
	function __construct( $value ) {
		$this->field 		= $value['type'];
		$this->name 		= $value['name'];
		$this->id 			= $value['id'];
		$this->pro 			= isset( $value['pro'] )		? $value['pro']			: false;
		$this->pro_info		= isset( $value['pro_info'] )	? $value['pro_info']	: false;
		$this->default 		= isset( $value['default'] ) 	? $value['default'] 	: '';
		$this->value 		= isset( $value['value'] ) 		? $value['value'] 		: '';
		$this->description 	= isset( $value['desc'] ) 		? $value['desc'] 		: '';
		$this->dependency  	= isset( $value['dependency'] ) ? $value['dependency'] 	: '';
	}

	/**
	 * HTML Output of the field
	 *
	 * @since 4.0
	 */
	public function render() {

		if ( $this->value || 1 === $this->value || true === $this->value ) {
			$this->value = 'on';
		}

		$is_pro = $this->pro && socialsnap()->pro || ! $this->pro;
		$is_pro_class = $is_pro ? '' : ' ss-pro-feature';
		ob_start();
		?>
		<div id="<?php echo $this->id; ?>_wrapper" class="ss-field-wrapper ss-field-spacing ss-toggle-element ss-clearfix<?php echo $is_pro_class; ?>"<?php echo SocialSnap_Fields::dependency_builder( $this->dependency ); ?>>

			<div class="ss-field-title">
				
				<span><?php echo $this->name; ?></span>

				<?php if ( $this->description ) { ?>
					<i class="ss-tooltip ss-question-mark" data-title="<?php echo $this->description; ?>"></i>
				<?php } ?>	

				<?php if ( ! $is_pro ) { 
					socialsnap_settings_upgrade_button( $this->pro_info );
				} ?>
			</div>

			<?php if ( $is_pro ) { ?>
	
				<span class="ss-small-toggle">
					<input type="checkbox" id="<?php echo $this->id; ?>" name="<?php echo $this->id; ?>" <?php checked( 'on', $this->value, true ); ?> />
					<label for="<?php echo $this->id; ?>"></label>
				</span>
			<?php } ?>

		</div>

		<?php
		return ob_get_clean();
	}
}