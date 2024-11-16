<?php
   global $wpdb;
   $table_name = $wpdb->prefix . 'cab_appointments';  // Ensure the correct table is used

   // Modify the query to get only paid appointments
   $query = $wpdb->prepare("
       SELECT * 
       FROM $table_name 
       WHERE cab_user_id = %d AND cab_status = 'paid' 
       ORDER BY cab_date DESC
   ", $user_id);

   return $wpdb->get_results($query);

?>