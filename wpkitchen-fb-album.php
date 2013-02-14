<?php 
/*
Plugin Name: wpKitchen FB Album
Plugin URI:
Description: Post photos to albums on Facebook using graph API
Version: 1.0
Author: wpKitchen, David V. Krukov
Author URI:
Text Domain: wpkitchen
License: GPLv2 or later
*/

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

if(!class_exists('WP_Kitchen')):

if(!defined('WPK_ROOT_DIR')){
	define('WPK_ROOT_DIR',dirname(__FILE__).'/src/');
}

global $wpk_facebook;

// Require main plugin class
require_once WPK_ROOT_DIR.'wpkitchen.php';

WP_Kitchen::init();

endif;