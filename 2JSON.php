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

class ToJson {

    public function register() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
        add_action('admin_menu', array($this, 'menu_script'));
    }

    public function enqueue() {
        wp_enqueue_style('2JSON_style', plugins_url('/css/style.css', __FILE__));
    }

    function menu_script() {
        add_menu_page('2JSON', '2JSON', 'manage_options', 'tojson_plugin', array($this, 'admin_index'), 'dashicons-paperclip', 110);
    }

    function admin_index() {
        require_once plugin_dir_path(__FILE__) . '/template/admin_page.php';
    }
}

if(class_exists('ToJson')) {
    $toJsonPlugin = new ToJson;
    $toJsonPlugin->register();
}
