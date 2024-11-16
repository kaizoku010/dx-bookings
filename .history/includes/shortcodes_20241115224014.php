<?php


function render_dx_appointment_form() {
    ob_start();
    ?>
    <form id="appointment-form" method="POST">
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

        <button type="submit" id="schedule-appointment-btn">Schedule Appointment</button>
    </form>

    <div id="progress-bar-container" style="display: none;">
        <div id="progress-bar" style="width: 0%; background-color: green; height: 5px;"></div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('#appointment-form').on('submit', function(e) {
            e.preventDefault(); // Prevent form from refreshing page
            
            // Show progress bar
            $('#progress-bar-container').show();
            $('#progress-bar').css('width', '10%');

            let appointmentType = $('#appointment_type').val();
            let appointmentDate = $('#appointment_date').val();
            let appointmentTime = $('#appointment_time').val();
            let appointmentNotes = $('#appointment_notes').val();
            
            // AJAX request to handle the booking
            $.ajax({
                url: '/?wc-ajax=dx_booking_submission', // Custom WooCommerce AJAX endpoint
                method: 'POST',
                data: {
                    appointment_type: appointmentType,
                    appointment_date: appointmentDate,
                    appointment_time: appointmentTime,
                    appointment_notes: appointmentNotes,
                },
                success: function(response) {
                    // Increase progress bar width as the request is successful
                    $('#progress-bar').css('width', '50%');
                    
                    // Redirect to cart or confirmation page after success
                    window.location.href = '/cart';  // Or use the appropriate URL for checkout

                    $('#progress-bar').css('width', '100%');
                },
                error: function() {
                    alert('Failed to schedule appointment. Please try again.');
                    $('#progress-bar').css('width', '0%');
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}


function display_user_appointments() {
    $user_id = get_current_user_id();
    $appointments = get_user_appointments($user_id);  // Fetch only paid appointments

    ob_start();
    if (!empty($appointments)) {
        echo '<h3>Your Paid Appointments</h3>';
        foreach ($appointments as $appointment) {
            echo '<p>Appointment Date: ' . esc_html($appointment->cab_date) . '</p>';
            echo '<p>Time: ' . esc_html($appointment->cab_time) . '</p>';
            echo '<p>Status: ' . esc_html($appointment->cab_status) . '</p>';
            echo '<p>Notes: ' . esc_html($appointment->cab_notes) . '</p>';
            if (!empty($appointment->cab_document_url)) {
                echo '<a href="' . esc_url($appointment->cab_document_url) . '">Download Report</a>';
            }
        }
    } else {
        echo '<p>No paid appointments found.</p>';
    }

    return ob_get_clean();
}
add_shortcode('display_user_appointments', 'display_user_appointments');

?>