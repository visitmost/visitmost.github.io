<?php 
error_reporting(0);

class Alti_Watermark_Public {

	private $plugin_name;
	private $version;
	private $image_requested;

	// constructor
	public function __construct( $plugin_name, $version, $image_requested ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->image_requested = $image_requested;

	}

	/**
	 * get image request
	 * @return value return image requested or false
	 */
	private function get_image_requested() {
		if( !empty($this->image_requested) && strtolower(substr($this->image_requested, -3)) == 'jpg' ) {
			return $this->image_requested;
		}
		else {
			return false;
		}
	}

	/**
	 * check image requested sent from the user through the htaccess
	 * @return file image
	 */
	public function check_image_requested() {

		if( $this->get_image_requested() !== false ) {


			preg_match('/(.*)' . $this->plugin_name . '(.*)/i', dirname( __FILE__ ), $watermark_folder);
			$request_uri_clean = explode('?', $_SERVER['REQUEST_URI'], 2); // remove query strings at the end of uri.
			$relative_path_image_requested = self::get_relative_path($_SERVER['SCRIPT_NAME'], $request_uri_clean[0]);
			$watermark_file  = imagecreatefrompng( $watermark_folder[1] . $this->plugin_name . '-data/watermark.png' );
			$image_requested = imagecreatefromjpeg( $relative_path_image_requested );

			// place watermark
		    imagefilledrectangle(
		      $image_requested, 
		      0, 
		      (imagesy($image_requested))-(imagesy($watermark_file)), 
		      imagesx($image_requested), 
		      imagesy($image_requested), 
		      imagecolorallocatealpha($image_requested, 0, 0, 0, 127) 
		    );

		    // push watermark inside image requested
		    imagecopy(
		      $image_requested, 
		      $watermark_file, 
		      (imagesx($image_requested)-(imagesx($watermark_file))), 
		      (imagesy($image_requested))-(imagesy($watermark_file)), 
		      0, 
		      0, 
		      imagesx($watermark_file), 
		      imagesy($watermark_file)
		    );

		    header('Last-Modified: '.gmdate('D, d M Y H:i:s T', filemtime ( $relative_path_image_requested )));
		    header('Content-Type: image/jpeg');

		    imagejpeg($image_requested, NULL, 85);
		    imagedestroy($watermark_file);
		    imagedestroy($image_requested);

		} 
		else {
			header('HTTP/1.1 503 Service Temporarily Unavailable');
			header('Status: 503 Service Temporarily Unavailable');
			header('Retry-After: 300');//300 seconds
			echo 'Plugin Error. Administrator, please deactivate it.';
		}

	}

	/**
	 * Get relative path
	 * http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
	 */
	function get_relative_path($from, $to)
	{
	    // some compatibility fixes for Windows paths
	    $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
	    $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
	    $from = str_replace('\\', '/', $from);
	    $to   = str_replace('\\', '/', $to);

	    $from     = explode('/', $from);
	    $to       = explode('/', $to);
	    $relPath  = $to;

	    foreach($from as $depth => $dir) {
	        // find first non-matching dir
	        if($dir === $to[$depth]) {
	            // ignore this directory
	            array_shift($relPath);
	        } else {
	            // get number of remaining dirs to $from
	            $remaining = count($from) - $depth;
	            if($remaining > 1) {
	                // add traversals up to first matching dir
	                $padLength = (count($relPath) + $remaining - 1) * -1;
	                $relPath = array_pad($relPath, $padLength, '..');
	                break;
	            } else {
	                $relPath[0] = './' . $relPath[0];
	            }
	        }
	    }
	    return implode('/', $relPath);
	}

}