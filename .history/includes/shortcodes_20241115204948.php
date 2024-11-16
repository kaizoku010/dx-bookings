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

    <script>
    jQuery(document).ready(function($) {
        $('#appointment-form').on('submit', function(e) {
            e.preventDefault(); // Prevent form from refreshing page
            
            let appointmentType = $('#appointment_type').val();
            let appointmentDate = $('#appointment_date').val();
            let appointmentTime = $('#appointment_time').val();
            let appointmentNotes = $('#appointment_notes').val();
            
            // AJAX request to add product to cart
            $.ajax({
                url: '/?wc-ajax=add_to_cart', // WooCommerce AJAX URL for adding to cart
                method: 'POST',
                data: {
                    product_id: getProductID(appointmentType), // Map appointment type to product ID
                    quantity: 1
                },
                success: function(response) {
                    // After successfully adding to cart, redirect to the cart page
                    window.location.href = '/cart'; // Or the appropriate cart URL
                },
                error: function() {
                    alert('Failed to schedule appointment. Please try again.');
                }
            });
        });

        // Helper function to map appointment type to product ID
        function getProductID(appointmentType) {
            let productMap = {
                standard: 123,  // Replace with your actual product IDs for each appointment type
                extended: 124,
                premium: 125
            };
            return productMap[appointmentType];
        }
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
        echo '<h3>Upcoming Paid Appointments</h3>';
        foreach ($appointments as $appointment) {
            echo '<p>' . esc_html($appointment->cab_date) . ' at ' . esc_html($appointment->cab_time) . '</p>';
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
shortcode('display_user_appointments', 'display_user_appointments');

?>