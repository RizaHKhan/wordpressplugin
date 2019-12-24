<?php 
/*
* Plugin Name: 2JSON
* Description: Converting adRotates SQL tables to JSON
* Version: 1.0.0
* Author: Riza Khan
* Author: khanriza.com
* Author URI: khanriza.com
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    die;
}

require_once(plugin_dir_path(__FILE__) . '/includes/2JSON-scripts.php');

function register_menu_page() {
    add_menu_page('2JSON', '2JSON', 'manage_options', 'adminmenu', 'tojson_callback', 'dashicons-paperclip');
}

add_action('admin_menu', 'register_menu_page');

function tojson_callback() {
    require_once dirname(__FILE__) . '/2JSON-admin.php';
}

