<?php
/**
 * Social Snap note field.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
*/
class SocialSnap_Field_note {
	
	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */	
	function __construct( $value ) {
		$this->field 		= $value['type'];
		$this->name 		= $value['name'];
		$this->id 			= $value['id'];
		$this->description 	= isset( $value['desc'] ) 		 ? $value['desc']    : '';
		$this->dependency 	= isset( $value['dependency'] )  ? $value['dependency'] : '';
	}

	/**
	 * HTML output of the field
	 *
	 * @since 1.0.0
	 */
	public function render() { 
		ob_start();
		?>
		<div id="<?php echo $this->id; ?>_wrapper" class="ss-field-wrapper ss-field-spacing ss-clearfix">
			<div class="ss-note"<?php echo SocialSnap_Fields::dependency_builder( $this->dependency ); ?>>
				<strong><?php echo $this->name; ?></strong>
				<em><?php echo $this->description; ?></em>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}