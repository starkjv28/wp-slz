<?php
/**
 * Social Snap info box field.
 *
 * @package    Social Snap
 * @author     Social Snap
 * @since      1.0.0
 * @license    GPL-3.0+
 * @copyright  Copyright (c) 2018, Social Snap LLC
*/
class SocialSnap_Field_info {
	
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
		<div id="<?php echo $this->id; ?>_wrapper" class="ss-field-wrapper ss-clearfix"<?php echo SocialSnap_Fields::dependency_builder( $this->dependency ); ?>>

			<div class="ss-info-field">
				<h4><?php echo $this->name; ?></h4>
				<?php echo $this->description; ?>
				<br/>
				<a href="<?php echo socialsnap_upgrade_link(); ?>" class="ss-button ss-small-button ss-upgrade-button" target="_blank"><?php _e( 'Upgrade Now', 'socialsnap' ); ?></a>
				<i class="dashicons-ssbrand"></i>
			</div><!-- END .ss-info-field -->

		</div>
		<?php
		return ob_get_clean();
	}
}