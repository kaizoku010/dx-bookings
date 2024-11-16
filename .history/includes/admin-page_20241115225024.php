<?php

function dx_bookings_admin_menu() {
    add_menu_page('DX Bookings', 'DX Bookings', 'manage_options', 'dx-bookings', 'dx_bookings_admin_page');
}
add_action('admin_menu', 'dx_bookings_admin_menu');

function dx_bookings_admin_page() {
    // Check if a delete action has been triggered
    if (isset($_GET['delete_appointment'])) {
        $appointment_id = intval($_GET['delete_appointment']);
        dx_bookings_delete_appointment($appointment_id);
    }

    // Fetch all appointments from the database
    global $wpdb;
    $table_name = $wpdb->prefix . 'cab_appointments';
    $appointments = $wpdb->get_results("SELECT * FROM $table_name");
    dx_bookings_admin_add_appointment();

    ?>
    <div class="wrap">
        <h1>DX Bookings Admin Page</h1>
        <p>Manage all appointments here.</p>
        
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <th scope="col" class="manage-column">ID</th>
                    <th scope="col" class="manage-column">Appointment Type</th>
                    <th scope="col" class="manage-column">Date</th>
                    <th scope="col" class="manage-column">Status</th>
                    <th scope="col" class="manage-column">Price</th>
                    <th scope="col" class="manage-column">Notes</th>
                    <th scope="col" class="manage-column">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($appointments)) : ?>
                    <?php foreach ($appointments as $appointment) : ?>
                        <tr>
                            <td><?php echo esc_html($appointment->cab_id); ?></td>
                            <td><?php echo esc_html($appointment->cab_appointment_type); ?></td>
                            <td><?php echo esc_html($appointment->cab_date); ?></td>
                            <td><?php echo esc_html($appointment->cab_status); ?></td>
                            <td><?php echo esc_html($appointment->cab_price); ?></td>
                            <td><?php echo esc_html($appointment->cab_notes); ?></td>
                            <td>
                                <!-- Delete appointment link -->
                                <a href="<?php echo admin_url('admin.php?page=dx-bookings&delete_appointment=' . $appointment->cab_id); ?>" class="button" onclick="return confirm('Are you sure you want to delete this appointment?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7">No appointments found.</td>
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
    $wpdb->delete($table_name, ['cab_id' => $appointment_id]);

    // Redirect back to the admin page to avoid re-posting on refresh
    wp_redirect(admin_url('admin.php?page=dx-bookings'));
    exit;
}

?>
