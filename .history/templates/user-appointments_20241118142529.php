<?php
// Check if the user is logged in
if (!is_user_logged_in()) {
    echo '<p>Please log in to make a new appointments.</p>';
    return;
}

// Fetch user ID and retrieve user appointments
$user_id = get_current_user_id();
$appointments = dx_get_user_appointments($user_id);

// Display success message
if (isset($_GET['appointment_booked']) && $_GET['appointment_booked'] == 'true') {
    echo '<div class="success-message">Your appointment has been successfully booked!</div>';
}
?>

<div class="dx-appointments-container">
    <!-- Appointments List -->
    <div class="appointments-list">
        <h3 class="your-appointments-title">Your Appointments</h3>
        <?php if (!empty($appointments)) : ?>
            <?php foreach ($appointments as $appointment) : ?>
                <div class="appointment-item">
                    <p class="found-data-title"><strong>Date:</strong> <?php echo esc_html($appointment->cab_date); ?></p>
                    <p class="found-data-title"><strong>Type:</strong> <?php echo esc_html(ucfirst($appointment->cab_appointment_type)); ?></p>
                    <p class="found-data-title"><strong>Status:</strong> <?php echo esc_html(ucfirst($appointment->cab_status)); ?></p>
                    <?php if (!empty($appointment->cab_document_url)) : ?>
                        <p>
                            <a href="<?php echo esc_url($appointment->cab_document_url); ?>" class="btn btn-primary" id="download_report_btn" download onclick="handleDownload(event)">
                                Download Report
                            </a>
                            <div id="download-status" style="display: none;">Downloading... Please wait...</div>
                            <div id="download-failed" style="display: none; color: red;">Download failed. Please try again.</div>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No appointments found.</p>
        <?php endif; ?>
    </div>

    <!-- Button to Trigger Appointment Booking Form Modal -->
    <button id="openAppointmentForm" class="btn btn-primary">Book A New Appointment</button>

    <!-- Appointment Booking Form Modal -->
    <div id="appointmentFormModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" id="closeAppointmentForm">&times;</span>
            <h3 class="new-booking-title">Appointment Form</h3>
            <?php echo do_shortcode('[dx_appointment_form]'); ?>
        </div>
    </div>
</div>

<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background-color: #fff;
        margin: 10% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 60%;
        border-radius: 8px;
    }

    .close-modal {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close-modal:hover, .close-modal:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }

    .btn {
        background-color: #0073aa;
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 4px;
        cursor: pointer;
    }

    #download-status, #download-failed {
        margin-top: 10px;
    }

</style>

<!-- JavaScript for Modal -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var openBtn = document.getElementById('openAppointmentForm');
        var modal = document.getElementById('appointmentFormModal');
        var closeBtn = document.getElementById('closeAppointmentForm');

        // Show the modal
        openBtn.addEventListener('click', function () {
            modal.style.display = 'block';
        });

        // Hide the modal
        closeBtn.addEventListener('click', function () {
            modal.style.display = 'none';
        });

        // Hide the modal when clicking outside the modal content
        window.addEventListener('click', function (event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
</script>
