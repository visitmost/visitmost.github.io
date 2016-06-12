<?php 
/**
 * Plugin Name:       Watermark
 * Plugin URI:        http://www.alticreation.com/en/alti-watermark/
 * Description:       Add a watermark on all your pictures even the one which are already uploaded. You can setup this plugin through the Media category in the side menu.
 * Version:           0.3.1
 * Author:            Alexis Blondin
 * Author URI:        http://www.alticreation.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       alti-watermark
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function activate_alti_watermark() {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-alti-watermark-activator.php';
	$activation = new Alti_Watermark_Activator();
	$activation->run();

}
function deactivate_alti_watermark() {

	require_once plugin_dir_path( __FILE__ ) . 'includes/class-alti-watermark-deactivator.php';
	$deactivation = new Alti_Watermark_Deactivator();
	$deactivation->run();

}

register_activation_hook( __FILE__, 'activate_alti_watermark' );
register_deactivation_hook( __FILE__, 'deactivate_alti_watermark' );

require plugin_dir_path( __FILE__ ) . 'includes/class-alti-watermark.php';

$plugin = new Alti_Watermark();
$plugin->run();
