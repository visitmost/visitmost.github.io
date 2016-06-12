<?php 
/**
 * Independant files called outside the Wordpress loop. Used as a bridge from htaccess to jpg
 */
require_once '../class-alti-watermark-public.php';

$plugin = new Alti_Watermark_Public('alti-watermark', '0.3', $_GET['imageRequested']);
echo $plugin->check_image_requested();