<?php
/**
 * Social Snap text field.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
*/
class SocialSnap_Field_text {
	
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
		$this->readonly 	= isset( $value['readonly'] ) 	 ? ' readonly'		 : '';
		$this->value_type 	= isset( $value['value_type'] )	 ? $value['value_type']	 : 'text';
		$this->placeholder 	= isset( $value['placeholder'] ) ? 'placeholder="' . $value['placeholder'] . '"' : '';
		$this->dependency 	= isset( $value['dependency'] )  ? $value['dependency'] : '';
		$this->pro 			= isset( $value['pro'] )		 ? $value['pro']		: false;
		$this->pro_info		= isset( $value['pro_info'] )	 ? $value['pro_info']	: '';
		$this->min 			= isset( $value['min'] )		 ? intval( $value['min'] )	: 0;
	}

	/**
	 * HTML output of the field
	 *
	 * @since 1.0.0
	 */
	public function render() { 

		$additional_params = '';

		if ( $this->value_type === 'number' ) {
			$additional_params = ' min="' . $this->min . '" step="1"';

			$this->value = $this->value ? $this->value : '0';
			$this->value = $this->value > $this->min ? $this->value : $this->min;
		}

		$is_pro = $this->pro && socialsnap()->pro || ! $this->pro;
		$is_pro_class = $is_pro ? '' : ' ss-pro-feature';
		ob_start();
		?>
		<div id="<?php echo $this->id; ?>_wrapper" class="ss-field-wrapper ss-field-spacing ss-clearfix<?php echo $is_pro_class; ?>"<?php echo SocialSnap_Fields::dependency_builder( $this->dependency ); ?>>

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
		
				<div class="ss-field-element ss-clearfix">
					<input type="<?php echo $this->value_type; ?>" <?php echo $this->placeholder; ?> name="<?php echo $this->id; ?>" id="<?php echo $this->id; ?>" value="<?php esc_attr_e( $this->value ); ?>" <?php echo $additional_params; echo $this->readonly; ?>/>
				</div>
			<?php } ?>
		</div>
		<?php
		return ob_get_clean();
	}
}