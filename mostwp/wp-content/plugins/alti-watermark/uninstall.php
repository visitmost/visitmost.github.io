<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

class Alti_Watermark_Uninstall {


	private static function delete_files() {

		$plugin_name = 'alti-watermark';

		if( file_exists( self::get_uploads_dir() . '/' . $plugin_name . '.old.htaccess' ) ) {

			unlink( self::get_uploads_dir() . '/' . $plugin_name . '.old.htaccess' );
		}

		if( is_dir( WP_PLUGIN_DIR . '/' . $plugin_name . '-data' ) ) {
			@unlink( WP_PLUGIN_DIR . '/' . $plugin_name . '-data/watermark.png' );
			rmdir( WP_PLUGIN_DIR . '/' . $plugin_name . '-data' );
		}

		if( file_exists( self::get_uploads_dir() . '/' . $plugin_name . '.previous.htaccess' ) ) {
			rename(self::get_uploads_dir().'/'. $plugin_name . '.previous.htaccess', self::get_uploads_dir().'/'. '.htaccess');
		}	
	}

	private static function get_uploads_dir() {
		$uploads_dir = wp_upload_dir();
		return $uploads_dir['basedir'];
	}

	public static function run() {
		if( is_admin()) self::delete_files();
	}

}

Alti_Watermark_Uninstall::run();