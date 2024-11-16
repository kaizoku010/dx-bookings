<?php


// Shortcode to display the appointment booking form
function render_dx_appointment_form() {
    ob_start();
    ?>
    <form id="appointment-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <label for="appointment_type">Type:</label>
        <select name="appointment_type" id="appointment_type" required>
            <option value="standard">Standard</option>
            <option value="extended">Extended</option>
            <option value="premium">Premium</option>
        </select>

        <label for="appointment_date">Date:</label>
        <input type="date" name="appointment_date" id="appointment_date" required>

        <label for="appointment_time">Time:</label>
        <input type="time" name="appointment_time" id="appointment_time" required>

        <label for="appointment_notes">Notes:</label>
        <textarea name="appointment_notes" id="appointment_notes"></textarea>

        <button type="submit">Schedule Appointment</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('dx_appointment_form', 'render_dx_appointment_form');



// Shortcode to display the user's appointments
function display_user_appointments() {
    $user_id = get_current_user_id();
    $appointments = get_user_appointments($user_id);

    ob_start();
    echo '<h3>Upcoming Appointments</h3>';
    foreach ($appointments as $appointment) {
        if ($appointment->date >= current_time('Y-m-d')) {
            echo '<p>' . esc_html($appointment->date) . ' at ' . esc_html($appointment->time) . '</p>';
            echo '<p>Notes: ' . esc_html($appointment->notes) . '</p>';
            if (!empty($appointment->document_url)) {
                echo '<a href="' . esc_url($appointment->document_url) . '">Download Report</a>';
            }
        }
    }

    echo '<h3>Past Appointments</h3>';
    foreach ($appointments as $appointment) {
        if ($appointment->date < current_time('Y-m-d')) {
            echo '<p>' . esc_html($appointment->date) . ' at ' . esc_html($appointment->time) . '</p>';
            echo '<p>Notes: ' . esc_html($appointment->notes) . '</p>';
            if (!empty($appointment->document_url)) {
                echo '<a href="' . esc_url($appointment->document_url) . '">Download Report</a>';
            }
        }
    }

    return ob_get_clean();
}
add_shortcode('display_user_appointments', 'display_user_appointments');

?>