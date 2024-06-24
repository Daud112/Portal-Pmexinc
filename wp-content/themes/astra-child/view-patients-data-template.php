<?php
/*
Template Name: View Patient's Data
*/

get_header();

// Handle form submission and get selected username
$current_user = wp_get_current_user();
$selected_username = '';

if (current_user_can('administrator')) {
    $selected_username = isset($_GET['username']) ? sanitize_text_field($_GET['username']) : '';
} else {
    $selected_username = $current_user->user_login;
}

// Initialize variables for date filtering
$filter_type = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$custom_start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
$custom_end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';

// Construct the WHERE clause based on filter type and custom dates
$where_clause = '';
switch ($filter_type) {
    case 'today':
        $where_clause = "DATE(upload_date) = CURDATE()";
        break;
    case 'this_week':
        $where_clause = "YEARWEEK(upload_date, 1) = YEARWEEK(CURDATE(), 1)";
        break;
    case 'this_month':
        $where_clause = "MONTH(upload_date) = MONTH(CURDATE()) AND YEAR(upload_date) = YEAR(CURDATE())";
        break;
    case 'custom':
        if (!empty($custom_start_date) && !empty($custom_end_date)) {
            $where_clause = $wpdb->prepare("upload_date BETWEEN %s AND %s", $custom_start_date, $custom_end_date);
        }
        break;
    default:
        // No filter applied (all uploads)
        break;
}

// Get the selected user's data from wp_csv_uploads table based on the filter
global $wpdb;
$user_uploads = [];
if ($selected_username) {
    $table_name = $wpdb->prefix . 'csv_uploads';
    
    // Construct the SQL query with the WHERE clause
    $query = "SELECT * FROM $table_name WHERE name = %s";

    if (!empty($where_clause)) {
        $query .= " AND $where_clause";
    }
    $query .= " ORDER BY upload_date DESC";

    // Execute the query
    $user_uploads = $wpdb->get_results($wpdb->prepare($query, $selected_username));
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

?>

<div class="container">
    <?php if (current_user_can('administrator')) : ?>
        <h1 class="my-4 fs-1">Doctor's Uploads</h1>
    <?php else : ?>
        <h1 class="my-4 fs-1">Your Uploads</h1>
    <?php endif;?>

    <!-- Filter form -->
    <form method="GET" action="" name="filterForm" id="filterForm" class="my-2">
        <?php if (current_user_can('administrator')) : ?>
            <div class="w-100 mb-3 d-flex justify-content-center">
                <select name="username" id="username" class="user-select">
                        <option value="">Select a doctor</option>
                    <?php foreach ($users as $user) : ?>
                    <option value="<?php echo esc_attr($user->user_login); ?>" <?php selected($selected_username, $user->user_login); ?>>
                        <?php echo esc_html($user->display_name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="w-100 mb-3 d-flex justify-content-center">
            <!-- <label for="filter" class="fs-6 me-2">Filter by:</label> -->
            <div class="form-check form-check-inline me-1">
                <input class="btn-check" type="radio" name="filter" id="filter-all" value="all" <?php checked($filter_type, 'all'); ?>>
                <label class="btn btn-outline-success text-nowrap" autocomplete="off" for="filter-all">All</label>
            </div>
            <div class="form-check form-check-inline mx-1">
                <input class="btn-check" type="radio" name="filter" id="filter-today" value="today" <?php checked($filter_type, 'today'); ?>>
                <label class="btn btn-outline-success text-nowrap" autocomplete="off" for="filter-today">Today</label>
            </div>
            <div class="form-check form-check-inline mx-1">
                <input class="btn-check" type="radio" name="filter" id="filter-this-week" value="this_week" <?php checked($filter_type, 'this_week'); ?>>
                <label class="btn btn-outline-success text-nowrap" autocomplete="off" for="filter-this-week">This Week</label>
            </div>
            <div class="form-check form-check-inline mx-1">
                <input class="btn-check" type="radio" name="filter" id="filter-this-month" value="this_month" <?php checked($filter_type, 'this_month'); ?>>
                <label class="btn btn-outline-success text-nowrap" autocomplete="off" for="filter-this-month">This Month</label>
            </div>
            <div class="form-check form-check-inline ms-1">
                <input class="btn-check" type="radio" name="filter" id="filter-custom" value="custom" <?php checked($filter_type, 'custom'); ?>>
                <label class="btn btn-outline-success text-nowrap" autocomplete="off" for="filter-custom">Custom Dates</label>
            </div>
        </div>
        <div class="row mb-3 <?php echo ($filter_type=="custom") ? 'd-block' : 'd-none'; ?>" id="custom-date-range">
            <div class="col-12">
                <input type="date" class="form-control h-50 my-2 w-50 mx-auto" id="start_date" name="start_date" value="<?php echo esc_attr($custom_start_date); ?>">
            </div>
            <div class="col-12 my-2 text-center">to</div>
            <div class="col-12">
                <input type="date" class="form-control h-50 my-2 w-50 mx-auto" id="end_date" name="end_date" value="<?php echo esc_attr($custom_end_date); ?>">
            </div>
        </div>
        <div class="row d-flex justify-content-center">
            <button type="submit" class="btn btn-success mt-2 mb-3 w-25 d-block">Apply Filter</button>
        </div>
        <div class="row d-flex justify-content-center">
            <button type="reset" class="btn btn-secondary mb-3 w-25 reset-filter d-block">Clear Filter</button>
        </div>
    </form>
    
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


    <?php if ($selected_username && $user_uploads) : ?>
        <?php if (current_user_can('administrator')) : ?>
            <div class="row my-2">
                <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                    <div class="d-flex justify-content-center fs-6"> <p class="fw-bold">Name: </p> <?php  echo($current_user->display_name); ?></div>
                </div>
                <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                    <div class="d-flex justify-content-center fs-6"><p class="fw-bold"> Email: </p> <?php echo($current_user->user_email); ?></div>
                </div>
            </div>
            <div class="fs-4 my-2">Records</div>
        <?php endif; ?>

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
                            <p class="card-text">Uploaded at: </br> <?php echo date('j-M-Y g:i A', strtotime($upload->upload_date)); ?></p>
                        </div>
                            <div class="card-footer border-0 bg-white text-center">
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle px-5" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="dropdownMenuButton1">
                                        <li>
                                            <a href="#" class="btn btn-primary open-file-popup dropdown-item fw-medium px-5" data-url="<?php echo esc_url(site_url().$upload->file_url); ?>">View</a>
                                        </li>
                                        <li>
                                            <form action="" method="post" class="d-inline">
                                                <input name="DeleteFile" value="deletefile" hidden/>
                                                <input name="fileUrl" value="<?php echo site_url().$upload->file_url ?>" hidden/>
                                                <input name="uploadsId" value="<?php echo $upload->id ?>" hidden/>
                                                <button type="submit" class="btn btn-danger dropdown-item fw-medium px-5">Delete</button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a type="button" href="<?php echo esc_url(site_url().$upload->file_url); ?>" class="btn btn-secondary dropdown-item fw-medium px-5" download>Download</a>
                                        </li>
                                    </ul>
                                </div>                                
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
    jQuery(document, window).ready(function($) {
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

        var rad = document.filterForm.filter;
        var prev = null;
        var current_select = "";
        const customDateRange = document.getElementById('custom-date-range');

        for (var i = 0; i < rad.length; i++) {
            rad[i].addEventListener('change', function() {
                (prev) ? console.log(prev.value): null;
                if (this !== prev) {
                    prev = this;
                }
                current_select = this.value;
                if (current_select == 'custom') {
                    customDateRange.classList.add('d-block');
                    customDateRange.classList.remove('d-none');
                } else {
                    customDateRange.classList.add('d-none');
                    customDateRange.classList.remove('d-block');
                }
            });
        }

        $('.reset-filter').on('click', function() {
            var base_url = window.location.origin + '/' + window.location.pathname.split ('/') [1] + '/';
            window.location.replace(base_url + '/view-patients-data');
        });
});
</script>

<?php get_footer(); ?>
