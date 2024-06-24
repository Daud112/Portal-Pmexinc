<?php
/*
Template Name: Upload Patient Data File Form
*/

get_header();

echo '<div class="container">';
echo '<h2 class="my-4 fs-1">Upload Your Record</h2>';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
    // Get current user's info
    $current_user = wp_get_current_user();
    $name = $current_user->user_login;
    $email = $current_user->user_email;

    $record_file = $_FILES['record_file'];

    // Check if the file is uploaded
    if ($record_file['error'] !== UPLOAD_ERR_OK) {
        echo "<div class='d-block alert alert-danger' role='alert'>Error while uploading file.</div>";
        unset($record_file); // Clear file info
    } else {
        // Get the file extension
        $file_extension = strtolower(pathinfo($record_file['name'], PATHINFO_EXTENSION));

        // Check if the file extension is allowed
        $allowed_extensions = ['pdf', 'txt', 'docx', 'xlsx', 'png', 'jpeg'];
        if (!in_array($file_extension, $allowed_extensions)) {
            echo "<div class='d-block alert alert-danger' role='alert'>Invalid file type. Only .pdf, .txt, .docx, .xlsx, .png, and .jpeg files are allowed.</div>";
            unset($record_file); // Clear file info
        } else {
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
            $new_file_name = $name . '_' . $current_time . '.' . $file_extension;
            $target_file = $target_dir . $new_file_name;

            // Move the uploaded file to the new directory with the new name
            if (move_uploaded_file($record_file['tmp_name'], $target_file)) {
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
                wp_cache_flush();
                echo "<div class='alert alert-success' role='alert'>Your record successfully uploaded.</div>";
                unset($record_file); // Clear file info
                // wp_redirect(site_url() ."/upload-patients-csv");
                echo '<script>
                    setTimeout(function() {
                        window.location.href = "' . site_url('/upload-patients-csv') . '";
                    }, 50); // 1000 milliseconds = 0.5 seconds
                </script>';
            } else {
                echo "<div class='d-block alert alert-danger' role='alert'>Your record is not uploaded.</div>";
                unset($record_file); // Clear file info
            }
        }
    }
}

?>
    
    <form action="" method="post" enctype="multipart/form-data">
        <label class="my-2" for="record_file">Please upload a record file:</label>
        <div class="input-group my-2">
            <input class="form-control" type="file" id="record_file" name="record_file" accept=".pdf, .txt, .docx, .xlsx, .png, .jpeg" required>
        </div>
        <div id="fileHelp" class="form-text mb-2">Only upload file in these formats (<span class="fw-bold">.pdf, .txt, .docx, .xlsx, .png, .jpeg</span>).</div>
        <input type="submit" name="submit" value="Upload">
    </form>
</div>

<?php get_footer(); ?>