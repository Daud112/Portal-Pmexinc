<?php
/*
Template Name: Upload Patient Data File Form
*/

get_header();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    // Get current user's info
    $current_user = wp_get_current_user();
    $name = $current_user->user_login;
    $email = $current_user->user_email;

    $csv_file = $_FILES['csv_file'];

    // Handle file upload
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    // Define the upload directory
    $upload_dir = wp_upload_dir();
    $target_dir = $upload_dir['basedir'] . '/patient_data/';

    // Create the patient_data directory if it doesn't exist
    if (!file_exists($target_dir)) {
        wp_mkdir_p($target_dir);
    }

    // Define the new file name with username and current datetime
    $current_time = current_time('Ymd_His');
    $file_extension = pathinfo($csv_file['name'], PATHINFO_EXTENSION);
    $new_file_name = $name . '_' . $current_time . '.' . $file_extension;
    $target_file = $target_dir . $new_file_name;

    // Move the uploaded file to the new directory with the new name
    if (move_uploaded_file($csv_file['tmp_name'], $target_file)) {
        $file_url = $upload_dir['baseurl'];
        $relative_url = '/wp-content/uploads/patient_data/' . $new_file_name;

        // Save file URL and user info to the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'csv_uploads';
        $wpdb->insert($table_name, array(
            'name' => $name,
            'email' => $email,
            'file_url' => $relative_url,
            'upload_date' => current_time('mysql')
        ));

        echo "<p>Patient data file has successfully uploaded.</p> </br>";
        // echo "<p>File URL: <a href='" . esc_url(site_url() . $relative_url) . "'>" . esc_url(site_url().$relative_url) . "</a></p> </br></br>";
    } else {
        echo "<p>Sorry!! You uploaded wrong patient data file.</p> </br></br>";

    }
}

?>

<div class="container">
    <br><br>
    <h2>Upload CSV File</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <br><br>
        <label for="csv_file">CSV File:</label>
        <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
        <br><br>
        <input type="submit" name="submit" value="Upload">
    </form>
</div>

<?php get_footer(); ?>
