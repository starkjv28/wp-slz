<?php
/**
 * Social Snap title field.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
*/
class SocialSnap_Field_title {
	
	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */	
	function __construct( $value ) {
		$this->field 		= $value['type'];
		$this->name 		= $value['name'];
		$this->id 			= $value['id'];
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
		<div id="<?php echo $this->id; ?>_wrapper" class="ss-settings-title"<?php echo SocialSnap_Fields::dependency_builder( $this->dependency ); ?>>
			<?php echo $this->name; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}