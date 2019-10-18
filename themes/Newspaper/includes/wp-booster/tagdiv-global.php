<?php

/**
 * Theme globals class
 * Here we store the global state of the theme. All globals are here
 */
class tagdiv_global {

	/**
	 * theme plugins
	 * 'PLUGIN_CONSTANT' => 'hash'
	 * @var array
	 */
	private static $td_plugins = array(
		'TD_COMPOSER'       => array( 'version' => '5a862b9d7c39671de80dd6dee389818b',         'class' => 'tdc_version_check' ),
		'TD_CLOUD_LIBRARY'  => array( 'version' => '489325fca4f12cbec6ded350cf173551',    'class' => 'tdb_version_check' ),
		'TD_SOCIAL_COUNTER' => array( 'version' => '8d47bba1fbef1da8fc31d99bbf7c4a46',   'class' => 'td_social_counter_plugin' ),
		'TD_NEWSLETTER'     => array( 'version' => '01c4aa984e303865067d66682a88d43f',       'class' => 'td_newsletter_version_check' ),
		'TD_MOBILE_PLUGIN'  => array( 'version' => 'abd0ff88846fca08146daa534b42cd3a',    'class' => 'td_mobile_theme' ),
		'AMP'               => array( 'version' => '___amp___',                 'class' => 'AMP_Autoloader' )
	);


	/**
	 * Get the $td_plugins hashes array
	 * @return array
	 */
	static function get_td_plugins() {
		return self::$td_plugins;
	}

	/**
	 * set below with either http or https string
	 * @var string
	 */
    static $http_or_https = 'http';

	/**
	 * Determines if SSL is used and sets the $http_or_https global
	 */
    static function set_http_or_https() {
	    if ( is_ssl() ) {
		    self::$http_or_https = 'https';
	    }
    }

	/**
	 * the plugins that are installable via the theme > plugins panel & tgma
	 * @var array
	 */
    static $theme_plugins_list = array();

	/**
	 * the plugins that are just for information proposes
	 * @var array
	 */
	static $theme_plugins_for_info_list = array();


    /**
     * the js files that are used in wp-admin
     * @var array
     *
     * @todo check what js files are needed for wp admin
     */
    static $js_files_for_wp_admin = array (
        'td_wp_admin'     => '/includes/wp-booster/wp-admin/js/td_wp_admin.js',
        'td_edit_page'    => '/includes/wp-booster/wp-admin/js/td_edit_page.js',
        'td_page_options' => '/includes/wp-booster/wp-admin/js/td_page_options.js',
        'td_tooltip'      => '/includes/wp-booster/wp-admin/js/tooltip.js',
	    'td_confirm'      => '/includes/wp-booster/wp-admin/js/tdConfirm.js',
    );

}

/**
 * set http or https
 */
tagdiv_global::set_http_or_https();


