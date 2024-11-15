<?php
function dx_get_user_appointments($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'dx_appointments';
    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d ORDER BY date DESC", $user_id);
    return $wpdb->get_results($query);
}
?>