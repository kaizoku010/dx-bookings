<?php
$user_id = get_current_user_id();

// Fetch only paid appointments
global $wpdb;
$table_name = $wpdb->prefix . 'cab_appointments';
$appointments = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table_name WHERE cab_user_id = %d AND cab_status = %s ORDER BY cab_date DESC",
        $user_id,
        'paid'
    )
);

// Display appointments
if (!empty($appointments)) {
    echo '<h3>Your Paid Appointments</h3>';
    foreach ($appointments as $appointment) {
        echo '<div class="appointment-item">';
        echo '<p><strong>Date:</strong> ' . esc_html($appointment->cab_date) . '</p>';
        echo '<p><strong>Type:</strong> ' . esc_html(ucfirst($appointment->cab_appointment_type)) . '</p>';
        echo '<p><strong>Status:</strong> ' . esc_html(ucfirst($appointment->cab_status)) . '</p>';
        if (!empty($appointment->cab_document_url)) {
            echo '<p><a href="' . esc_url($appointment->cab_document_url) . '" class="btn btn-primary">Download Report</a></p>';
        }
        echo '</div>';
    }
} else {
    echo '<p>No paid appointments found.</p>';
}

// Display a success message after successful booking
if (isset($_GET['booking_success']) && $_GET['booking_success'] == '1') {
    echo '<p class="success-message">Your booking has been scheduled successfully and is now paid!</p>';
}
?>
