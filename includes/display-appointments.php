<?php
function dx_get_user_appointments($user_id) {
   global $wpdb;
    $table_name = $wpdb->prefix . 'cab_appointments';

    $query = $wpdb->prepare("
        SELECT * 
        FROM $table_name 
        WHERE cab_user_id = %d AND cab_status = 'paid' 
        ORDER BY cab_date DESC
    ", $user_id);

    return $wpdb->get_results($query);
}
?>