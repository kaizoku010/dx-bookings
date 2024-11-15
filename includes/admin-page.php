<?php
function dx_bookings_admin_menu() {
    add_menu_page('DX Bookings', 'DX Bookings', 'manage_options', 'dx-bookings', 'dx_bookings_admin_page');
}
add_action('admin_menu', 'dx_bookings_admin_menu');

function dx_bookings_admin_page() {
    ?>
    <div class="wrap">
        <h1>DX Bookings Admin Page</h1>
        <p>Manage your booking settings here.</p>
        <!-- Admin options form -->
    </div>
    <?php
}
?>
