<?php
function cab_get_product_id_by_appointment_type($appointment_type) {
    $product_map = [
        'standard' => 4147,
        'extended' => 4146,
        'premium'  => 4140,
    ];
    return $product_map[$appointment_type] ?? null;
}

function dx_handle_booking_submission() {
    if (isset($_POST['submit_booking']) && is_user_logged_in()) {
        global $wpdb;

        $user_id = get_current_user_id();
        $appointment_type = sanitize_text_field($_POST['appointment_type']);
        $appointment_date = sanitize_text_field($_POST['appointment_date']);
        $appointment_time = sanitize_text_field($_POST['appointment_time']);
        $appointment_notes = sanitize_textarea_field($_POST['appointment_notes']);

        // Combine date and time for the full appointment date
        $appointment_datetime = $appointment_date . ' ' . $appointment_time;

        // Get the product ID for the selected appointment type
        $product_id = cab_get_product_id_by_appointment_type($appointment_type);

        if (!$product_id) {
            wp_die('Invalid appointment type. Please try again.');
        }

        // Temporarily save booking details in session
        $appointment_data = [
            'user_id'          => $user_id,
            'appointment_type' => $appointment_type,
            'date'             => $appointment_datetime,
            'notes'            => $appointment_notes,
            'status'           => 'pending_payment',
        ];

        // Save the appointment data to the WooCommerce session
        WC()->session->set('dx_booking_data', $appointment_data);

        // Add the product to the cart
        WC()->cart->add_to_cart($product_id);

        // Redirect to the checkout page
        wp_redirect(wc_get_checkout_url());
        exit;
    }
}

add_action('init', 'dx_handle_booking_submission');


function get_user_appointments($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'appointments';
    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d ORDER BY date DESC", $user_id);
    return $wpdb->get_results($query);
}



function dx_finalize_booking($order_id) {
    if (!$order_id) {
        return;
    }

    $order = wc_get_order($order_id);

    // Get appointment data from session
    $appointment_data = WC()->session->get('dx_booking_data');

    if (!$appointment_data) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'cab_appointments';

    // Get the product ID and price
    $product_id = cab_get_product_id_by_appointment_type($appointment_data['appointment_type']);
    $product = wc_get_product($product_id);
    $price = $product ? $product->get_price() : 0;

    // Save booking to database, including price
    $wpdb->insert($table_name, [
        'cab_user_id'           => $appointment_data['user_id'],
        'cab_appointment_type'  => $appointment_data['appointment_type'],
        'cab_date'              => $appointment_data['date'],
        'cab_duration'          => 60, // Default duration in minutes
        'cab_status'            => 'paid',
        'cab_price'             => $price,  // Save the price
        'cab_notes'             => $appointment_data['notes'],
        'cab_document_url'      => null,
    ]);

    // Clear session data
    WC()->session->__unset('dx_booking_data');
}
add_action('woocommerce_thankyou', 'dx_finalize_booking');

function dx_finalize_booking_redirect($order_id) {
    $user_id = get_current_user_id();
    if ($user_id) {
        // Redirect to Appointments page with success message
        $redirect_url = wc_get_account_endpoint_url('appointments') . '?booking_success=1';
        wp_redirect($redirect_url);
        exit;
    }
}
add_action('woocommerce_thankyou', 'dx_finalize_booking_redirect');

// In includes/helpers.php or another appropriate file
function display_appointment_prices() {
    // Get product IDs (update with actual product ID)
    $product_ids = [
        'standard' => 4147,  // Replace with actual product ID for standard appointment
        'extended' => 4146,  // Replace with actual product ID for extended appointment
        'premium'  => 4140,  // Replace with actual product ID for premium appointment
    ];

    // Retrieve the product prices
    $prices = [];
    foreach ($product_ids as $key => $product_id) {
        $product = wc_get_product($product_id);
        if ($product) {
            $prices[$key] = $product->get_price();
        }
    }

    // Display prices (you can adjust how and where you display them)
    echo '<p>Standard Appointment Price: $' . esc_html($prices['standard']) . '</p>';
    echo '<p>Extended Appointment Price: $' . esc_html($prices['extended']) . '</p>';
    echo '<p>Premium Appointment Price: $' . esc_html($prices['premium']) . '</p>';
}

function dx_bookings_admin_add_appointment() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dx_booking_submit'])) {
        $appointment_type = sanitize_text_field($_POST['appointment_type']);
        $appointment_date = sanitize_text_field($_POST['appointment_date']);
        $appointment_price = floatval($_POST['appointment_price']);
        $appointment_notes = sanitize_textarea_field($_POST['appointment_notes']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'cab_appointments';

        // Insert the new appointment into the database
        $wpdb->insert(
            $table_name,
            [
                'cab_appointment_type' => $appointment_type,
                'cab_date'             => $appointment_date,
                'cab_price'            => $appointment_price,
                'cab_notes'            => $appointment_notes,
                'cab_status'           => 'pending',
            ]
        );

        echo '<div class="updated"><p>Appointment added successfully!</p></div>';
    }
    ?>
    <h2>Add New Appointment</h2>
    <form method="POST">
        <label for="appointment_type">Appointment Type:</label>
        <select name="appointment_type" id="appointment_type" required>
            <option value="standard">Standard</option>
            <option value="extended">Extended</option>
            <option value="premium">Premium</option>
        </select><br>

        <label for="appointment_date">Appointment Date:</label>
        <input type="date" name="appointment_date" id="appointment_date" required><br>

        <label for="appointment_price">Price ($):</label>
        <input type="number" step="0.01" name="appointment_price" id="appointment_price" required><br>

        <label for="appointment_notes">Notes:</label>
        <textarea name="appointment_notes" id="appointment_notes"></textarea><br>

        <button type="submit" name="dx_booking_submit" class="button button-primary">Add Appointment</button>
    </form>
    <?php
}


?>