<?php
/*
Template Name: View Patient's Data
*/

get_header();

// Handle form submission and get selected username
$current_user = wp_get_current_user();
$selected_username = '';

if (current_user_can('administrator')) {
    $selected_username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
} else {
    $selected_username = $current_user->user_login;
}

// Get all users if the current user is an admin
if (current_user_can('administrator')) {
    $args = array(
        'role' => 'Doctor'
    );
    $users = get_users($args);
} else {
    $users = array($current_user);
}

// Get the selected user's data from wp_csv_uploads table
global $wpdb;
$user_uploads = [];
if ($selected_username) {
    $table_name = $wpdb->prefix . 'csv_uploads';
    $user_uploads = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE name = %s",
        $selected_username
    ));
}
?>

<div class="container">
    <?php if (current_user_can('administrator')) : ?>
        <h1 class="my-4 fs-1">Doctor's CSV Uploads</h1>
    <?php else : ?>
        <h1 class="my-4 fs-1">Your Uploads</h1>
    <?php endif;?>
    <?php 
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['DeleteFile'] == 'deletefile') {
            // Sanitize and retrieve posted data
            $fileUrl = isset($_POST['fileUrl']) ? $_POST['fileUrl'] : '';
            $uploadsId = isset($_POST['uploadsId']) ? $_POST['uploadsId'] : '';
        
            // Check if $fileUrl and $uploadsId are not empty
            if (!empty($fileUrl) && !empty($uploadsId)) {
                // Convert file URL to server path
                $file_path = str_replace(site_url(), ABSPATH, $fileUrl);
        
                // Check if the file exists before attempting deletion
                if (file_exists($file_path)) {
                    // Attempt to delete the file
                    if (unlink($file_path)) {
                        // File deletion successful, now delete from database
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'csv_uploads';
        
                        // Use $wpdb->delete to remove the row from the database table
                        $wpdb->delete($table_name, array('id' => $uploadsId));
        
                        // Send success response
                        echo "<div class='alert alert-success' role='alert'>File deleted successfully.</div>";
                        // Redirect to view-patients-data after 5 seconds
                        echo '<script>
                            console.log("TESTESRESDASDD");
                            setTimeout(function() {
                                window.location.href = "' . site_url('/view-patients-data') . '";
                            }, 50); // 1000 milliseconds = 5 seconds
                        </script>';
                    } else {
                        // File deletion failed
                        echo "<div class='d-block alert alert-danger' role='alert'>Failed to delete file.</div>";
                    }
                } else {
                    // File does not exist
                    echo "<div class='d-block alert alert-danger' role='alert'>File does not exist.</div>";
                }
            } 
        }
    ?>
    <?php if (current_user_can('administrator')) : ?>
        <form method="post">
            <label for="username">Select Doctor</label>
            <br>
            <select name="username" id="username" class="user-select">
                <option value="">Select a doctor</option>
                <?php foreach ($users as $user) : ?>
                    <option value="<?php echo esc_attr($user->user_login); ?>" <?php selected($selected_username, $user->user_login); ?>>
                        <?php echo esc_html($user->display_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="select-submit-btn">Show Uploads</button>
        </form>
    <?php endif; ?>

    <?php if ($selected_username && $user_uploads) : ?>
        <?php if (current_user_can('administrator')) : ?>
            <div class="row">
                <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                    <p> <span class="fw-bold">Name:</span> <?php echo($current_user->display_name); ?></p>
                </div>
                <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                    <p><span class="fw-bold"> Email:</span> <?php echo($current_user->user_email); ?></p>
                </div>
            </div>
        <?php endif; ?>
        <div class="fs-4 my-2">Records</div>

        <!-- User file uploads for preview and show cards below -->
        <div class="row">
            <?php foreach ($user_uploads as $upload) : ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 rounded-1">
                        <?php
                            // Display image preview
                            $file_extension = strtolower(pathinfo(site_url().$upload->file_url, PATHINFO_EXTENSION));
                            if (in_array($file_extension, ['png', 'jpeg'])) {
                        ?>
                            <div class="w-100 card-height d-flex justify-content-center">
                                <img src="<?php echo esc_url(site_url().$upload->file_url); ?>" class="card-img-top file-preview p-3 w-100 h-50 my-auto" data-url="<?php echo esc_url(site_url().$upload->file_url); ?>" alt="<?php echo esc_attr(site_url().$upload->file_url); ?>">
                            </div>
                        <?php } elseif($file_extension == 'xlsx') { ?>
                            <div class="w-100 card-height d-flex justify-content-center">
                                <img src="<?php echo esc_url(site_url().'/wp-content/themes/astra-child/public/images/xlsx.png') ?>" class="card-img-top file-preview p-2 w-25 h-50 my-auto"alt="Excel file">
                            </div>
                        <?php } elseif($file_extension == 'docx') { ?>
                            <div class="w-100 card-height d-flex justify-content-center">
                                <img src="<?php echo esc_url(site_url().'/wp-content/themes/astra-child/public/images/docx.png') ?>" class="card-img-top file-preview p-2 w-25 h-50 my-auto"alt="Excel file">
                            </div>
                        <?php } elseif($file_extension == 'txt') { ?>
                            <div class="w-100 card-height d-flex justify-content-center">
                                <img src="<?php echo esc_url(site_url().'/wp-content/themes/astra-child/public/images/txt.png') ?>" class="card-img-top file-preview p-2 w-25 h-50 my-auto"alt="Excel file">
                            </div>
                        <?php } elseif($file_extension == 'pdf') { ?>
                            <div class="card-img-top card-height text-center file-preview" data-url="<?php echo esc_url(site_url().$upload->file_url); ?>">
                                <iframe src="<?php echo esc_url(site_url().$upload->file_url) ?>" class="w-100 h-100 border-0 overflow-hidden"></iframe>
                            </div>
                        <?php } else { ?>
                            <div class="w-100 card-height">
                                <img src="<?php echo esc_url(site_url().'/wp-content/themes/astra-child/public/images/goole-docs.png') ?>" class="card-img-top file-preview p-2 w-25 h-50 my-auto"alt="Excel file">
                            </div>
                        <?php } ?>
                        <div class="card-body">
                            <p class="card-text">Uploaded on: <?php echo date('F j, Y', strtotime($upload->upload_date)); ?></p>
                            </div>
                            <div class="card-footer border-0 bg-white">
                                
                                <a href="#" class="btn btn-primary open-file-popup" data-url="<?php echo esc_url(site_url().$upload->file_url); ?>">View</a>
                                <!-- <a type="button" href="<?php site_url().$upload->file_url ?>" class="btn btn-info file-download-link text-white" download>Download</a> -->
                                <form action="" method="post" class="d-inline">
                                    <input name="DeleteFile" value="deletefile" hidden/>
                                    <input name="fileUrl" value="<?php echo site_url().$upload->file_url ?>" hidden/>
                                    <input name="uploadsId" value="<?php echo $upload->id ?>" hidden/>
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($selected_username) : ?>
        <p>Sorry! No records found.</p>
    <?php endif; ?>
</div>

<!-- Popup Modal -->
<div  class="modal w-100" id="file-popup-modal" style="display:none;">
    <div class="modal-dialog modal-xl w-100 h-100 py-5">
        <div class="modal-content w-100 h-100 overflow-auto rounded-2 p-2">
            <!-- <span class="close-popup">&times;</span> -->
            <div class="modal-body w-100 h-100">
                <div id="file-preview-container" class="w-100 h-100 my-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-popup" data-bs-dismiss="modal">Close</button>
                <a type="button" href="" class="btn btn-primary file-download-link" download>Download</a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Add your custom CSS here */
    #file-popup-modal {
        position: fixed;
        background: rgb(0 0 0 / 96%);
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .card-height{
        height: 200px;
    }
</style>

<script>
    jQuery(document).ready(function($) {
        $('.open-file-popup').on('click', function(e) {
            e.preventDefault();
            var fileUrl = $(this).data('url');
            $('#file-preview-container').html('<iframe src="' + fileUrl + '" style="width: 100%; height: 100%; border: none;"></iframe>');
            $('a.file-download-link').attr('href', fileUrl);
            $('#file-popup-modal').show();
        });

        $('.close-popup').on('click', function() {
            $('#file-popup-modal').hide();
        });

        $(document).on('click', function(e) {
            if ($(e.target).is('#file-popup-modal')) {
                $('#file-popup-modal').hide();
            }
        });
});
</script>

<?php get_footer(); ?>
