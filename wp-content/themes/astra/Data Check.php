<?php
/* Template Name: Display CSV Data */

get_header();

if (is_user_logged_in()) {
    global $wpdb;

    // Example: Retrieve the latest submitted CSV file path from CF7DB
    $results = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}cf7dbplugin_submits WHERE form_name = 'your-form-name' ORDER BY submitted_on DESC LIMIT 1");

    if ($results) {
        $file_url = wp_upload_dir()['basedir'] . '/' . $results[0]->file; // Adjust path based on your file storage

        // Read CSV data
        if (($handle = fopen($file_url, "r")) !== FALSE) {
            echo '<div class="wp-table-builder">';
            echo do_shortcode('[wptb id=215]'); // Replace '1' with your actual WP Table Builder table ID
            echo '</div>';
            fclose($handle);
        } else {
            echo 'Failed to open file: ' . $file_url;
        }
    } else {
        echo 'No data found in the database.';
    }
} else {
    echo 'Please log in to view the data.';
}

get_footer();
?>
