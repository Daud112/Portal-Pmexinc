<?php

function astra_child_enqueue_styles() {
    // Enqueue parent theme styles
wp_enqueue_style('astra-style', get_template_directory_uri() . '/style.css');


    // Enqueue child theme styles
    wp_enqueue_style('astra-child-style', get_stylesheet_directory_uri() . '/style.css', array('astra-style'), wp_get_theme()->get('Version'));

    // Enqueue child theme scripts
    wp_enqueue_script('astra-child-scripts', get_stylesheet_directory_uri() . '/js/scripts.js', array('jquery'), wp_get_theme()->get('Version'), true);
}
add_action('wp_enqueue_scripts', 'astra_child_enqueue_styles');



// Enqueue custom scripts and styles
// function astra_child_enqueue_custom_styles() {
//     wp_enqueue_style('astra-child-custom-style', get_stylesheet_directory_uri() . '/custom-style.css');
//     wp_enqueue_script('astra-child-custom-script', get_stylesheet_directory_uri() . '/custom-script.js', array('jquery'), wp_get_theme()->get('Version'), true);
// }
// add_action('wp_enqueue_scripts', 'astra_child_enqueue_custom_styles');



// Function to create a table for storing CSV uploads
function create_csv_upload_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'csv_uploads';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        email text NOT NULL,
        file_url text NOT NULL,
        upload_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'create_csv_upload_table');

