<?php
// Check if the user is logged in
if (!is_user_logged_in()) {
    echo '<p>Please log in to view your appointments.</p>';
    return;
}

// Fetch user ID and retrieve user appointments
$user_id = get_current_user_id();
$appointments = dx_get_user_appointments($user_id);

// Display success message
if (isset($_GET['appointment_booked']) && $_GET['appointment_booked'] == 'true') {
    echo '<div class="success-message">Your appointment has been successfully booked!</div>';
}
?>

<div class="dx-appointments-container">
    <!-- Appointments List -->
    <div class="appointments-list">
        <h3>Your Appointments</h3>
        <?php if (!empty($appointments)) : ?>
            <?php foreach ($appointments as $appointment) : ?>
                <div class="appointment-item">
                    <p><strong>Date:</strong> <?php echo esc_html($appointment->cab_date); ?></p>
                    <p><strong>Type:</strong> <?php echo esc_html(ucfirst($appointment->cab_appointment_type)); ?></p>
                    <p><strong>Status:</strong> <?php echo esc_html(ucfirst($appointment->cab_status)); ?></p>
                    <?php if (!empty($appointment->cab_document_url)) : ?>
                        <p>
                            <a href="<?php echo esc_url($appointment->cab_document_url); ?>" class="btn btn-primary">
                                Download Report
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No appointments found.</p>
        <?php endif; ?>
    </div>

    <!-- Appointment Booking Form -->
    <div class="appointments-form">
        <h3 class="new-booking-title">Book a New Appointment</h3>
        <?php echo do_shortcode('[dx_appointment_form]'); ?>
    </div>
</div>
