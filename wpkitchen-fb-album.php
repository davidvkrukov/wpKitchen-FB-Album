<?php 
/*
Plugin Name: wpKitchen FB Album
Plugin URI:
Description: Post photos to albums on Facebook using graph API
Version: 1.0
Author: wpKitchen
Author URI:
Text Domain: wpkitchen
Domain Path: /lang/
License: GPL2
*/

// Exit if accessed directly
if(!defined('ABSPATH')) exit;

if(!class_exists('WP_Kitchen')):

if(!defined('WPK_ROOT_DIR')){
	define('WPK_ROOT_DIR',dirname(__FILE__).'/src/');
}

// Load timthumb library and check for cache directory
require_once dirname(__FILE__).'/lib/timthumb.php';
if(!file_exists(FILE_CACHE_DIRECTORY)){
	try{
		mkdir(FILE_CACHE_DIRECTORY,0777);
	}catch(Exception $e){
		echo $e->getMessage();
	}
}

global $wpk_facebook;

// Require main plugin class
require_once WPK_ROOT_DIR.'wpkitchen.php';

WP_Kitchen::init();

endif;