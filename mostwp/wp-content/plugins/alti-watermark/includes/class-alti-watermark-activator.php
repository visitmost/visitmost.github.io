<?php 
/**
 * fired on activation
 */
class Alti_Watermark_Activator extends Alti_Watermark {

	/**
	* Generate an empty watermark png file if watermark png file, this function is only available in activator class
	* @return file create image
	*/
	public function generate_empty_watermark() {

		if( self::check_watermark_folder() !== false && !file_exists( WP_PLUGIN_DIR . '/' . $this->plugin_name . '-data' . '/watermark.png' )) {
			$empty_watermark = imagecreatetruecolor(1, 1);
			imagesavealpha($empty_watermark, true);
			$transparent = imagecolorallocatealpha($empty_watermark, 0, 0, 0, 127);
			imagefill($empty_watermark, 0, 0, $transparent);
			imagepng( $empty_watermark, WP_PLUGIN_DIR . '/' . $this->plugin_name . '-data' . '/watermark.png' );
			imagedestroy($empty_watermark);
		}

	}

	/**
	 * check validity of folder for watermark and create it
	 * @return bool
	 */
	public function check_watermark_folder() {
		
		// check if folder-data exists
		if( !file_exists( WP_PLUGIN_DIR . '/' . $this->plugin_name . '-data' ) ) {
			if( !mkdir( WP_PLUGIN_DIR . '/' . $this->plugin_name . '-data', 0755, true ) ) {
				return 'false';
			}

		} else { return 'true'; }

		// check if is writable folder-data
		if( !is_writable( WP_PLUGIN_DIR . '/' . $this->plugin_name . '-data' )) {
			if ( !chmod( WP_PLUGIN_DIR . '/' . $this->plugin_name . '-data', 0755 )) {
				return 'false';
			}
		} else { return 'true'; }

	}

	/**
	 * Reactivate an eventual previous htaccess
	 */
	public function reactivate_previous_htaccess() {

		if( file_exists( self::get_uploads_dir() . '/' . $this->plugin_name . '.old.htaccess' ) && !file_exists( self::get_uploads_dir() . '/' . '.htaccess' )) {
			rename(self::get_uploads_dir() . '/' . $this->plugin_name . '.old.htaccess', self::get_uploads_dir() . '/' . '.htaccess');
		}

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
	 * run activation
	 */
	public function run() {

		$this->generate_empty_watermark();
		$this->reactivate_previous_htaccess();

	}

}