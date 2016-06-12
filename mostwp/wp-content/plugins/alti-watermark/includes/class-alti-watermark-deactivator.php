<?php 
/**
 * Fired on deactivation of plugin
 */
class Alti_Watermark_Deactivator extends Alti_Watermark {

	/**
	 * rename htaccess to old.htaccess and restore a previous htaccess to htaccess
	 */
	public function rename_htaccess() {
		if(@file_exists(self::get_uploads_dir().'/'.'.htaccess'))  rename(self::get_uploads_dir().'/'.'.htaccess', self::get_uploads_dir().'/'. $this->plugin_name . '.old.htaccess');
		if(@file_exists(self::get_uploads_dir().'/'. $this->plugin_name .'.previous.htaccess'))  rename(self::get_uploads_dir().'/'. $this->plugin_name . '.previous.htaccess', self::get_uploads_dir().'/'. '.htaccess');
	}

	/**
	 * get uploads dir
	 * @return string return path
	 */
	public function get_uploads_dir() {
		$uploads_dir = wp_upload_dir();
		return $uploads_dir['basedir'];
	}

	/**
	 * run deactivation
	 */
	public function run() {
		self::rename_htaccess();
	}

}