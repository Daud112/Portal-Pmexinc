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
$user_uploads = array();
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
        <h1 class="my-4 fs-1"><?php echo("Doctor's CSV Uploads"); ?></h1>
    <?php else : ?>
        <h1 class="my-4 fs-1"><?php echo('CSV Uploads'); ?></h1>
    <?php endif;?>
    <?php if (current_user_can('administrator')) : ?>
        <form method="post">
            <label for="username"><?php echo('Select Doctor'); ?></label>
            <br>
            <select name="username" id="username" class="user-select">
                <option value=""><?php echo('Select a doctor'); ?></option>
                <?php foreach ($users as $user) : ?>
                    <option value="<?php echo esc_attr($user->user_login); ?>" <?php selected($selected_username, $user->user_login); ?>>
                        <?php echo esc_html($user->display_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="select-submit-btn"><?php echo('Show Uploads'); ?></button>
        </form>
    <?php endif; ?>

    <?php if ($selected_username && $user_uploads) : ?>

        <div class="fs-4"><?php echo('User Details'); ?></div>

        <div class="row">
            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                <p> <?php echo('Name'); ?>: <?php echo($current_user->display_name); ?></p>
            </div>
            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                <!-- <?php $selected_user = get_user_by('login', $selected_username); ?> -->
                <p><?php echo('Email'); ?>: <?php echo($current_user->user_email); ?></p>
            </div>
        </div>

        <div class="fs-4 my-2"><?php echo('CSV Files Data'); ?></div>
        <?php foreach ($user_uploads as $upload) : ?>
            <div class="row">
                <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                    <?php echo esc_html(basename($upload->file_url)); ?>
                </div>
                <div class="col-12 col-sm-12 col-md-6 col-lg-6 text-start text-sm-start text-md-end d-flex flex-column ">
                    <div>
                        <a href="<?php echo esc_url(site_url() .$upload->file_url); ?>" target="_blank" class="text-decoration-none btn btn-primary my-1">
                            Download
                        </a>
                    </div>
                    <div>                    
                        Created At: <?php echo esc_html($upload->upload_date); ?>
                    </div>
                </div>
            </div>
        </br>
            <?php
            // Convert the URL to a local file path
            $file_path = esc_url(site_url() .  $upload->file_url); 

            if (($handle = fopen($file_path, "r")) !== FALSE) {
                echo '<div class="table-responsive">';
                echo '<table class="table table-hover border-1">';
                $header = fgetcsv($handle);
                if ($header) {
                    echo '<thead>';
                    echo '<tr>';
                    foreach ($header as $column) {
                        echo '<th>' . esc_html($column) . '</th>';
                    }
                    echo '</tr>';
                    echo '</thead>';
                }
                echo '<tbody>';
                while (($data = fgetcsv($handle)) !== FALSE) {
                    echo '<tr>';
                    foreach ($data as $cell) {
                        echo '<td>' . esc_html($cell) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
                echo '</div>';
                fclose($handle);
            } else {
                echo '<p>' . __('Unable to open file.') . '</p>';
            }
            ?>
        <?php endforeach; ?>

    <?php elseif ($selected_username) : ?>
        <p><?php echo('No CSV uploads found for this user.'); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
