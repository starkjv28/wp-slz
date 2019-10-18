<?php
/**
 * Social Snap import field.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
*/
class SocialSnap_Field_import {
	
	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct( $value ) {
		$this->field 		= $value['type'];
		$this->description	= $value['desc'];
		$this->name 		= $value['name'];
		$this->id 			= $value['id'];
	}

	/**
	 * HTML Output of the field
	 *
	 * @since 1.0.0
	 */
	public function render() {

		ob_start(); 
		?>
		<div id="<?php echo $this->id; ?>_wrapper" class="ss-field-wrapper ss-import-element ss-clearfix ss-field-spacing" id="<?php echo $this->id; ?>">

			<div class="ss-field-title">
				<?php echo $this->name; ?>

				<?php if ( $this->description ) { ?>
					<i class="ss-tooltip ss-question-mark" data-title="<?php echo $this->description; ?>"></i>
				<?php } ?>	
			</div>
			
			<div class="ss-field-element ss-clearfix">
				<input type="file" id="ss-upload-import-file" multiple="false" style="display: none;" name="ss-upload-import-file" />
				<a href="#" class="ss-button ss-small-button" id="ss-import-settings" data-nonce="<?php echo wp_create_nonce( 'socialsnap-settings' ); ?>" >
					<?php esc_html_e( 'Import Settings', 'socialsnap' ); ?>	
				</a>
				<span class="ss-file-name"></span>
				<span class="spinner"></span>
			</div>

		</div>
		<?php
		return ob_get_clean();
	}
}
