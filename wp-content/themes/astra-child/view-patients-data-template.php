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
$users = current_user_can('administrator') ? get_users() : array($current_user);

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

<div class="wrap w-100">
    <h1><?php _e('User CSV Uploads', 'your-text-domain'); ?></h1>

    <?php if (current_user_can('administrator')) : ?>
        <form method="post">
            <label for="username"><?php _e('Select User', 'your-text-domain'); ?></label>
            <br>
            <select name="username" id="username" class="user-select">
                <option value=""><?php _e('Select a user', 'your-text-domain'); ?></option>
                <?php foreach ($users as $user) : ?>
                    <option value="<?php echo esc_attr($user->user_login); ?>" <?php selected($selected_username, $user->user_login); ?>>
                        <?php echo esc_html($user->display_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="select-submit-btn"><?php _e('Show Uploads', 'your-text-domain'); ?></button>
        </form>
    <?php endif; ?>

    <?php if ($selected_username && $user_uploads) : ?>
        <br>
        <h2><?php _e('User Details:', 'your-text-domain'); ?></h2>
        <?php $selected_user = get_user_by('login', $selected_username); ?>
        <p><?php _e('Name', 'your-text-domain'); ?>: <?php echo esc_html($selected_user->display_name); ?></p>
        <p><?php _e('Email', 'your-text-domain'); ?>: <?php echo esc_html($selected_user->user_email); ?></p>

        <h2><?php _e('Uploaded CSV Files', 'your-text-domain'); ?></h2>
        <ul>
            <?php foreach ($user_uploads as $upload) : ?>
                <li>
                    <a href="<?php echo esc_url($upload->file_url); ?>" target="_blank">
                        <?php echo esc_html(basename($upload->file_url)); ?>
                    </a>
                    (<?php echo esc_html($upload->upload_date); ?>)
                </li>
            <?php endforeach; ?>
        </ul>

        <h2><?php _e('CSV Files Data', 'your-text-domain'); ?></h2>
        <?php foreach ($user_uploads as $upload) : ?>
            <h3><?php echo esc_html(basename($upload->file_url)); ?></h3>
        </br>
            <?php
            // Convert the URL to a local file path
            $file_path = esc_url(site_url() .  $upload->file_url); 

            if (($handle = fopen($file_path, "r")) !== FALSE) {
                echo '<table border="1" cellpadding="5" cellspacing="0">';
                $header = fgetcsv($handle);
                if ($header) {
                    echo '<tr>';
                    foreach ($header as $column) {
                        echo '<th>' . esc_html($column) . '</th>';
                    }
                    echo '</tr>';
                }
                while (($data = fgetcsv($handle)) !== FALSE) {
                    echo '<tr>';
                    foreach ($data as $cell) {
                        echo '<td>' . esc_html($cell) . '</td>';
                    }
                    echo '</tr>';
                }
                echo '</table>';
                fclose($handle);
            } else {
                echo '<p>' . __('Unable to open file.', 'your-text-domain') . '</p>';
            }
            ?>
        <?php endforeach; ?>

    <?php elseif ($selected_username) : ?>
        <p><?php _e('No CSV uploads found for this user.', 'your-text-domain'); ?></p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
