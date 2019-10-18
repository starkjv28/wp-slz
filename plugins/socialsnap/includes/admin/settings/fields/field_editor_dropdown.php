<?php
/**
 * Social Snap dropdown editor field.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
*/
class SocialSnap_Field_editor_dropdown {
	
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
	}

	/**
	 * HTML output of the field
	 *
	 * @since 1.0.0
	 */
	public function render() { 

		$options = $this->options;
		$current = '';

		ob_start();
		?>

		<div id="<?php echo $this->id; ?>_wrapper" class="ss-field-wrapper ss-field-dropdown ss-clearfix"<?php echo SocialSnap_Fields::dependency_builder( $this->dependency ); ?>>

			<div class="ss-left-section">
				<label for="<?php echo $this->id; ?>"><strong><?php echo $this->name; ?></strong>

					<?php if ( $this->description ) { ?>
					<span class="ss-desc"><?php echo $this->description; ?></span>
					<?php } ?>

				</label>
			</div>

			<div class="ss-right-section">

				<?php ?>

				<?php if ( is_array( $this->options ) && ! empty( $this->options ) ) { ?> 
					<select name="<?php echo $this->id; ?>" id="<?php echo $this->id; ?>">
						
						<?php 
						foreach ( $this->options as $key => $value ) { 
							echo '<option value="'. $key .'"'. selected( $key, $this->value, false ) .'>'. $value .'</option>';
						}
						?>

					</select>
				<?php } ?>
			</div>

		</div>
		
		<?php
		return ob_get_clean();
	}
}