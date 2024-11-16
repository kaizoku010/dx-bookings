<?php
/*
Plugin Name: DX Bookings
Description: Allows users to book appointments, pay through WooCommerce, and access reports.
Version: 5.0   
Author: Muneza
Text Domain: dx-bookings
*/

// Include required files
include_once plugin_dir_path(__FILE__) . 'includes/db.php';
include_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
include_once plugin_dir_path(__FILE__) . 'includes/helpers.php';
include_once plugin_dir_path(__FILE__) . 'includes/admin-page.php';
include_once plugin_dir_path(__FILE__) . 'includes/display-appointments.php';

// Add DX Appointments tab to WooCommerce Account menu
add_filter('woocommerce_account_menu_items', 'dx_bookings_add_tab_to_account_menu');
function dx_bookings_add_tab_to_account_menu($items) {
    // Add the DX Appointments tab after Orders
    $new_items = array_slice($items, 0, 2, true) + 
                 ['dx-appointments' => __('DX Appointments', 'dx-bookings')] + 
                 array_slice($items, 2, null, true);
    return $new_items;
}

// Register WooCommerce endpoint for DX Appointments
add_action('init', 'dx_bookings_add_endpoint');
function dx_bookings_add_endpoint() {
    // Register the endpoint
    add_rewrite_endpoint('dx-appointments', EP_ROOT | EP_PAGES);
}

// Add query vars for endpoint
add_filter('query_vars', 'dx_bookings_query_vars');
function dx_bookings_query_vars($vars) {
    $vars[] = 'dx-appointments';
    return $vars;
}

// Content for DX Appointments tab
add_action('woocommerce_account_dx-appointments_endpoint', 'dx_bookings_tab_content');
function dx_bookings_tab_content() {
    // Include the user appointments template
    include plugin_dir_path(__FILE__) . 'templates/user-appointments.php';

    // Display the booking form
    echo do_shortcode('[dx_appointment_form]');
}

// Enqueue styles and scripts
function dx_bookings_enqueue_assets() {
    wp_enqueue_style('dx-bookings-style', plugin_dir_url(__FILE__) . 'assets/styles.css');
    wp_enqueue_script('dx-bookings-script', plugin_dir_url(__FILE__) . 'assets/scripts.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'dx_bookings_enqueue_assets');

// Flush rewrite rules on activation
register_activation_hook(__FILE__, 'dx_bookings_flush_rewrite_rules');
function dx_bookings_flush_rewrite_rules() {
    dx_bookings_add_endpoint();
    flush_rewrite_rules();
}


// function display_user_appointments() {
//     $user_id = get_current_user_id();
//     $appointments = get_user_appointments($user_id);  // Fetch only paid appointments

//     ob_start();
//     if (!empty($appointments)) {
//         echo '<h3>Your Paid Appointments</h3>';
//         foreach ($appointments as $appointment) {
//             echo '<p>Appointment Date: ' . esc_html($appointment->cab_date) . '</p>';
//             echo '<p>Time: ' . esc_html($appointment->cab_time) . '</p>';
//             echo '<p>Status: ' . esc_html($appointment->cab_status) . '</p>';
//             echo '<p>Notes: ' . esc_html($appointment->cab_notes) . '</p>';
//             if (!empty($appointment->cab_document_url)) {
//                 echo '<a href="' . esc_url($appointment->cab_document_url) . '">Download Report</a>';
//             }
//         }
//     } else {
//         echo '<p>No paid appointments found.</p>';
//     }

//     return ob_get_clean();
// }
// add_shortcode('display_user_appointments', 'display_user_appointments');


?>