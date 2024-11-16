<?php
function cab_create_appointments_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'cab_appointments'; // Namespaced table name

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
            cab_id mediumint(9) NOT NULL AUTO_INCREMENT,
            cab_user_id mediumint(9) NOT NULL,
            cab_appointment_type varchar(50) NOT NULL,
            cab_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            cab_duration int NOT NULL,
            cab_status varchar(50) DEFAULT 'unpaid' NOT NULL,
            cab_document_url text DEFAULT NULL,
            PRIMARY KEY  (cab_id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
register_activation_hook(__FILE__, 'cab_create_appointments_table');

function cab_run_upgrade_routine() {
    global $wpdb;

    $old_table_name = $wpdb->prefix . 'appointments';
    $new_table_name = $wpdb->prefix . 'cab_appointments';

    if ($wpdb->get_var("SHOW TABLES LIKE '$old_table_name'") == $old_table_name) {
        $wpdb->query("ALTER TABLE $old_table_name RENAME TO $new_table_name");
    }

    $wpdb->query("ALTER TABLE $new_table_name CHANGE user_id cab_user_id BIGINT(20)");
    $wpdb->query("ALTER TABLE $new_table_name CHANGE appointment_type cab_appointment_type VARCHAR(50)");
    $wpdb->query("ALTER TABLE $new_table_name CHANGE status cab_status VARCHAR(50)");
    $wpdb->query("ALTER TABLE $new_table_name CHANGE document_url cab_document_url TEXT");
    $wpdb->query("ALTER TABLE $new_table_name CHANGE date cab_date DATETIME");

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    cab_create_appointments_table();
}
?>
