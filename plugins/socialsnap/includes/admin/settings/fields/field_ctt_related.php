<?php
/**
 * Social Snap Click To tweet related accounts.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
*/
class SocialSnap_Field_ctt_related {
	
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

		if ( ! is_array( $this->value ) ) {
			$this->value = array();
		}

		if ( ! isset( $this->value[0] ) ) {
			$this->value[0] = array( 'username' => '', 'desc' => '' );
		}

		if ( ! isset( $this->value[1] ) ) {
			$this->value[1] = array( 'username' => '', 'desc' => '' );
		}

		ob_start();
		?>
		<div class="ss-field-wrapper ss-field-spacing ss-clearfix"<?php echo SocialSnap_Fields::dependency_builder( $this->dependency ); ?>>

			<div class="ss-field-title">
				<?php echo $this->name; ?>

				<?php if ( $this->description ) { ?>
					<i class="ss-tooltip ss-question-mark" data-title="<?php echo $this->description; ?>"></i>
				<?php } ?>	
			</div>

			<div class="ss-field-element ss-twitter-related-fields ss-clearfix">
				
				<input type="text" placeholder="@username_1" name="<?php echo $this->id; ?>[0][username]" id="<?php echo $this->id; ?>_username_0" value="<?php esc_attr_e( $this->value[0]['username'] ); ?>"/>

				<textarea placeholder="Optional. Shortly describe how @username_1 is related to your Twitter account." name="<?php echo $this->id; ?>[0][desc]" id="<?php echo $this->id; ?>_desc_0" rows="2"><?php echo $this->value[0]['desc']; ?></textarea>

				<input type="text" placeholder="@username_2" name="<?php echo $this->id; ?>[1][username]" id="<?php echo $this->id; ?>_username_1" value="<?php esc_attr_e( $this->value[1]['username'] ); ?>"/>

				<textarea placeholder="Optional. Shortly describe how @username_2 is related to your Twitter account." name="<?php echo $this->id; ?>[1][desc]" id="<?php echo $this->id; ?>_desc_1" rows="2"><?php echo $this->value[1]['desc']; ?></textarea>

			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}