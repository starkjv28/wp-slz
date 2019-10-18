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
class SocialSnap_Field_editor_upload {
	
	/**
	 * Primary class constructor.
	 *
	 * @since 4.0
	 */
	function __construct( $value ) {
		$this->field 		= $value['type'];
        $this->name 		= $value['name'];
        $this->id 			= $value['id'];
        $this->default 		= isset( $value['default'] ) 		? $value['default']		 : '';
        $this->value 		= isset( $value['value'] ) 			? $value['value']		 : '';
        $this->description 	= isset( $value['desc'] ) 			? $value['desc']		 : '';
        $this->dependency 	= isset( $value['dependency'] ) 	? $value['dependency']	 : '';
        $this->extradesc 	= isset( $value['extradesc'] ) 		? $value['extradesc']	 : '';
        $this->allowed_type	= isset( $value['allowed_type'] ) 	? $value['allowed_type'] : array( 'image' );

        $this->button_caption = esc_html__( 'Upload Image', 'socialsnap' );

        if ( isset( $this->allowed_type ) && ! in_array( 'image', $this->allowed_type ) ) {
        	$this->button_caption = esc_html__( 'Upload Video', 'socialsnap' );
        }
	}

	/**
	 * HTML Output of the field
	 *
	 * @since 4.0
	 */
	public function render() {

		$class 	= ! $this->value ? ' hidden' : '';
		$id		= is_array( $this->value ) && isset( $this->value['id'] ) ? $this->value['id'] : $this->value;
		$edit	= '#';

		$mime_type = '';

		// We have an upload.
		if ( $id ) {

			$edit 		= admin_url( 'post.php?post='. $id .'&action=edit' );

			// Determine the type of the uploaded file.
			$mime_type 	= get_post_mime_type( $id );
			$mime_type 	= explode( '/', $mime_type );
			if ( is_array( $mime_type ) && ! empty( $mime_type ) ) {
				$mime_type = $mime_type[0];
			}
		}

		ob_start();
		?>
		<div id="<?php echo $this->id; ?>_wrapper" class="ss-field-wrapper ss-field-upload ss-upload-element ss-clearfix"<?php echo SocialSnap_Fields::dependency_builder( $this->dependency ); ?>>

			<div class="ss-left-section">
				<label for="<?php echo $this->id; ?>"><strong><?php echo $this->name; ?></strong>

					<?php if ( $this->description ) { ?>
					<span class="ss-desc"><?php echo $this->description; ?></span>
					<?php } ?>

				</label>
			</div>

			<div class="ss-right-section">
				<input id="<?php echo $this->id; ?>_img_id" type="hidden" name="<?php echo $this->id; ?>" value="<?php echo esc_attr( $id ); ?>">

				<a class="ss-upload-button ss-button" id="<?php echo $this->id; ?>_button" href="#" data-title="<?php esc_html_e( 'Choose or upload a file', 'socialsnap' ); ?>" data-button="<?php esc_html_e( 'Use this file', 'socialsnap' ); ?>" data-type="<?php echo implode( $this->allowed_type, ',' ); ?>">
					<?php echo $this->button_caption; ?>	
				</a>

				<div class="wp-clearfix"></div>
				
				<div id="<?php echo $this->id; ?>-preview" class="show-upload-image mime-type-<?php echo $mime_type; ?>">

					<?php

					if ( $id && false !== strpos( $mime_type, 'image' ) ) {

						$src  = wp_get_attachment_image_src ( $id, 'medium' );
						$full = wp_get_attachment_image_src ( $id, 'full' );

						echo '<img src="' . $src[0] . '" alt="' . $this->name . '"/>';

					} elseif ( $id && false !== strpos( $mime_type, 'video' ) ) {
						
						$src = get_attached_file( $id );

						echo '<span class="ss-video-name">' . basename( $src ) . '</span>';
					}
					
					?>
						

					<div class="ss-image-tools">
						
						<a href="#" class="ss-remove-image<?php echo $class; ?>" id="<?php echo $this->id; ?>_remove"><i class="ss ss-close"></i></a>

						<a href="<?php echo $edit; ?>" id="<?php echo $this->id; ?>_edit" target="_blank"><i class="dashicons dashicons-edit"></i></a>
					
						<span class="ss-image-dimension" id="<?php echo $this->id; ?>_dimension">
							<?php 
							if ( isset( $mime_type ) && false !== strpos( $mime_type, 'image' ) && isset( $full ) && isset( $full[1] ) && isset( $full[2] ) ) {
								echo $full[1] . 'px x ' . $full[2] . 'px';
							}
							?>
						</span>

					</div>
				</div>

				<?php if ( $this->extradesc ) { ?>
					<div class="ss-extra-desc"><?php echo $this->extradesc; ?></div>
				<?php } ?>

			</div>

		</div>

		<?php
		return ob_get_clean();
	}
}