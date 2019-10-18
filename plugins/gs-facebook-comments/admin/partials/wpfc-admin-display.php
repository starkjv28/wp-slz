<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://profiles.wordpress.org/qsheeraz#content-plugins
 * @since      1.0.0
 *
 * @package    Wpfc
 * @subpackage Wpfc/admin/partials
 */
?>

	<form action='options.php' method='post'>
	<div class="woosocio_wrap">
	<div id="woosocio-services-block">
		<a href="https://genialsouls.com/product/wp-facebook-comments-pro/" target="_top">
		<img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/wpfcp_cs.jpg' ?>" alt="WP Facebook Comments Pro" width="700"></a>
		<?php
		settings_fields( 'wpfc_options' );
		do_settings_sections( 'wpfc_options' );
		submit_button();

		include_once( 'wpfc-admin-plugins.php' );

		echo '</div>';

		include_once( 'wpfc-admin-right-area.php' );
		?>
	</div>		
	</form>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
