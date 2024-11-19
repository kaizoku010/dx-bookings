<?php

function dx_bookings_admin_menu() {
    add_menu_page('DX Bookings', 'DX Bookings', 'manage_options', 'dx-bookings', 'dx_bookings_admin_page');
}
add_action('admin_menu', 'dx_bookings_admin_menu');

function dx_bookings_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cab_appointments';

    // Handle delete appointment request
    if (isset($_GET['delete_appointment'])) {
        $appointment_id = intval($_GET['delete_appointment']);
        dx_bookings_delete_appointment($appointment_id);
    }

    // Handle document upload via PHP
    if (isset($_POST['upload_document']) && isset($_POST['appointment_id']) && isset($_FILES['uploaded_document'])) {
        $appointment_id = intval($_POST['appointment_id']);
        $uploaded_file = $_FILES['uploaded_document'];

        // Validate file upload
        if ($uploaded_file['error'] === UPLOAD_ERR_OK) {
            $upload_result = wp_handle_upload($uploaded_file, ['test_form' => false]);
            
            if (isset($upload_result['file'])) {
                // Prepare file for attachment
                $attachment = [
                    'post_mime_type' => $upload_result['type'],
                    'post_title' => sanitize_file_name($uploaded_file['name']),
                    'post_content' => '',
                    'post_status' => 'inherit',
                ];
                
                $attachment_id = wp_insert_attachment($attachment, $upload_result['file']);
                require_once ABSPATH . 'wp-admin/includes/image.php';
                wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload_result['file']));

                // Save the attachment URL to the database
                $file_url = wp_get_attachment_url($attachment_id);
                $wpdb->update(
                    $table_name,
                    ['cab_document_url' => $file_url],
                    ['id' => $appointment_id]
                );
                echo '<div class="updated"><p>Document uploaded successfully!</p></div>';
            } else {
                echo '<div class="error"><p>Error uploading file. Please try again.</p></div>';
            }
        } else {
            echo '<div class="error"><p>File upload error. Please try again.</p></div>';
        }
    }

    // Fetch all appointments from the database
    $appointments = $wpdb->get_results("SELECT * FROM $table_name ORDER BY cab_date DESC");

    ?>
    <div class="wrap">
        <h1>DX Bookings Admin Page Version: ~85.1.1</h1>
        <p>Plugin by muneza dixon@2024</p>
        <p>Manage all appointments here.</p>

        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Client Name</th>
                    <th>Client Email</th>
                    <th>Mobile Number</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    <?php if (!empty($appointments)) : ?>
        <?php foreach ($appointments as $appointment) : ?>
            <tr>
                <td><?php echo esc_html($appointment->cab_user_id); ?></td>
                <td><?php echo esc_html($appointment->cab_appointment_type); ?></td>
                <td><?php echo esc_html($appointment->cab_date); ?></td>
                <td><?php echo esc_html($appointment->cab_status); ?></td>
                <td><?php echo esc_html($appointment->cab_user_name); ?></td>
                <td><?php echo esc_html($appointment->cab_user_email); ?></td>
                <td><?php echo esc_html($appointment->cab_user_phone); ?></td>
                <td><?php echo esc_html($appointment->cab_price); ?></td>
                <td class="document_upload_div">
                    <?php if (!empty($appointment->cab_document_url)) : ?>
                        <a href="<?php echo esc_url($appointment->cab_document_url); ?>" target="_blank">View Document</a>
                    <?php else : ?>
                        No document uploaded
                    <?php endif; ?>
                </td>
                <td>
                    <!-- Document Upload Form -->
                    <form class="dx-appointment-form" method="POST" enctype="multipart/form-data">
                        <input title=" "  class="dx-file-upload" type="file" name="uploaded_document" accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                        <input type="hidden" name="appointment_id" value="<?php echo esc_attr($appointment->id); ?>">
                        <button type="submit" name="upload_document" class="button" id="upload_document_button">Save</button>
                    </form>

                    <!-- Delete Appointment -->
                    <a href="<?php echo admin_url('admin.php?page=dx-bookings&delete_appointment=' . $appointment->id); ?>" class="button" id="delete-appointment-btn" onclick="return confirm('Are you sure you want to delete this appointment?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else : ?>
        <tr>
            <td colspan="11">No appointments found.</td>
        </tr>
    <?php endif; ?>
</tbody>

        </table>
    </div>
    <?php
}

// Function to delete an appointment
function dx_bookings_delete_appointment($appointment_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cab_appointments';
    $result = $wpdb->delete($table_name, ['id' => $appointment_id]);

    if ($result === false) {
        error_log('Failed to delete appointment: ' . $wpdb->last_error);
    }

    // Redirect back to the admin page to avoid re-posting on page refresh
    wp_redirect(admin_url('admin.php?page=dx-bookings'));
    exit;
}
?>
