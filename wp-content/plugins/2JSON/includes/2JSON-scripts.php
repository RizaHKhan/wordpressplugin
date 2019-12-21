<?php 
// Add scripts
    function tojson_add_scripts() {
        // Add main css
        wp_enqueue_style('tojson-main-style', plugins_url() . '/2JSON/css/style.css');
        // Add JS
        wp_enqueue_script('tojson-main-script', plugins_url() . '/2JSON/js/main.js');
    }

    add_action('wp_enqueue_scripts', 'tojson_add_scripts' );