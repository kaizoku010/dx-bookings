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

        <!-- Call the function to display the prices -->
        <?php display_appointment_prices(); ?>

        <button type="submit" id="schedule-appointment-btn">Schedule Appointment</button>
    </form>

    <script>
function getProductID(appointmentType) {
    const productMap = {
        standard:4650, // ID for Standard Appointment
        extended:4146, // ID for Extended Appointment
        premium: 4651,  // ID for Premium Appointment
    };
    return productMap[appointmentType] || null;
}

jQuery(document).ready(function($) {
    $('#appointment-form').on('submit', function(e) {
        e.preventDefault();

        $('#progress-bar').show();
        let appointmentType = $('#appointment_type').val();
        let appointmentDate = $('#appointment_date').val();
        let appointmentTime = $('#appointment_time').val();
        let appointmentNotes = $('#appointment_notes').val();

        let progress = 0;
        let interval = setInterval(function() {
            progress += 10;
            $('#progress-bar div').css('width', progress + '%');
            if (progress >= 100) {
                clearInterval(interval);
            }
        }, 500);

        $.ajax({
            url: '/?wc-ajax=add_to_cart',
            method: 'POST',
            data: {
                product_id: getProductID(appointmentType),
                quantity: 1
            },
            success: function(response) {
                window.location.href = '/cart';
            },
            error: function() {
                alert('Failed to schedule appointment. Please try again.');
            },
            complete: function() {
                $('#progress-bar').hide();
            }
        });
    });
});
</script>

    <?php
    return ob_get_clean();
}


add_shortcode('dx_appointment_form', 'render_dx_appointment_form');


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