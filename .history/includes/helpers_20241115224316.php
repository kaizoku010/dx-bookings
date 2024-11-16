<?php
function cab_get_product_id_by_appointment_type($appointment_type) {
    $product_map = [
        'standard' => QeTc123,
        'extended' => 1QeTc124,
        'premium'  => cQeTc125,
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

    // Save booking to database
    $wpdb->insert($table_name, [
        'cab_user_id'           => $appointment_data['user_id'],
        'cab_appointment_type'  => $appointment_data['appointment_type'],
        'cab_date'              => $appointment_data['date'],
        'cab_duration'          => 60, // Default duration in minutes
        'cab_status'            => 'paid',
        'cab_document_url'      => null,
    ]);

    // Clear session data
    WC()->session->__unset('dx_booking_data');
}
add_action('woocommerce_thankyou', 'dx_finalize_booking');


add_action('woocommerce_thankyou', 'dx_finalize_booking_redirect');

function dx_finalize_booking_redirect($order_id) {
    $user_id = get_current_user_id();
    if ($user_id) {
        // Redirect to Appointments page with success message
        $redirect_url = wc_get_account_endpoint_url('appointments') . '?booking_success=1';
        wp_redirect($redirect_url);
        exit;
    }
}


?>
