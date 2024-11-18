<?php
/*
Plugin Name: DX Bookings
Description: Allows users to book appointments, pay through WooCommerce, and access reports.
Version: 81.1.1   
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

    // // Display the booking form
    // echo do_shortcode('[dx_appointment_form]');
}

add_action('woocommerce_add_to_cart', 'save_appointment_notes_to_session', 10, 6);
function save_appointment_notes_to_session($cart_item_key, $product_id, $quantity, $variation_id, $variations, $cart_item_data) {
    if (isset($_POST['appointment_notes'])) {
        // Store appointment notes in the WooCommerce session
        WC()->session->set('dx_booking_data', [
            'notes' => sanitize_textarea_field($_POST['appointment_notes'])
        ]);
    }
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





add_action('woocommerce_order_status_completed', 'sync_order_to_appointments');
function sync_order_to_appointments($order_id) {

    $order = wc_get_order($order_id);
    $user_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    $user_email = $order->get_billing_email();
    $user_phone = $order->get_billing_phone();
    $appointment_data = WC()->session->get('dx_booking_data');
    $appointment_notes = isset($appointment_data['notes']) ? $appointment_data['notes'] : '';


    if (!$order) {
        error_log("Order not found: $order_id");
        return;
    }

    $items = $order->get_items();

    foreach ($items as $item) {
        $product_id = $item->get_product_id();

        // Map product ID to appointment type
        $appointment_type = array_search($product_id, [
            'free' => 4650,
            'premium' => 4651,
        ]);

        if ($appointment_type) {
            global $wpdb;

            // Use the zdN prefix dynamically
            $table_name = $wpdb->base_prefix . 'cab_appointments';

            // Insert data into the table
            $result = $wpdb->insert($table_name, [
                'cab_user_id' => $order->get_user_id(),
                'cab_appointment_type' => $appointment_type,
                'cab_date' => current_time('mysql'), // Use actual data if available
                'cab_status'=> 'paid',
                'cab_price' => $item->get_total(),
                'cab_notes' => $appointment_notes,
                'cab_user_name' => $user_name,
                'cab_user_email' => $user_email,
                'cab_user_phone' => $user_phone,
            ]);

            // Log success or errors for debugging
            if ($result === false) {
                error_log("Database error: " . $wpdb->last_error);
            } else {
                error_log("Appointment inserted for product ID: $product_id in table $table_name");
            }
        } else {
            error_log("No appointment type found for product ID: $product_id");
        }
    }
}

// function dx_bookings_admin_scripts() {
//     wp_enqueue_media(); // Enqueue WordPress media uploader scripts

//     ?>
//     <script type="text/javascript">
//         jQuery(document).ready(function($){
//             $('#upload_document_button').click(function(e) {
//                 e.preventDefault();

//                 var mediaUploader = wp.media({
//                     title: 'Select Document',
//                     button: {
//                         text: 'Use this file'
//                     },
//                     multiple: false
//                 });

//                 mediaUploader.open();

//                 mediaUploader.on('select', function() {
//                     var attachment = mediaUploader.state().get('selection').first().toJSON();
//                     $('#appointment_document_url').val(attachment.url); // Store the URL in the text field
//                 });
//             });
//         });
//     </script>
//     <?php
// }
// add_action('admin_enqueue_scripts', 'dx_bookings_admin_scripts');


?>