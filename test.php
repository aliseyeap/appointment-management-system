<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php'; 

include 'session-termination.php';

// Prepare SQL query to retrieve user information based on user ID
$stmt_user = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Retrieve staff name
$staff_name = $user['username'];

// Check if appointment ID is provided in the URL
if(isset($_GET['appointment_id']) && !empty($_GET['appointment_id'])) {
    // Sanitize the input to prevent SQL injection
    $appointment_id = filter_var($_GET['appointment_id'], FILTER_SANITIZE_NUMBER_INT);

    // Prepare SQL query to fetch appointment details
    $stmt_appointment = $pdo->prepare("SELECT a.appointment_id, u.username AS customer_name, s.service_name, a.appointment_date, a.appointment_time, a.appointment_status FROM appointment a INNER JOIN user u ON a.customer_id = u.user_id INNER JOIN service s ON a.service_id = s.service_id WHERE a.appointment_id = ?");
    $stmt_appointment->execute([$appointment_id]);
    $appointment = $stmt_appointment->fetch(PDO::FETCH_ASSOC);

    // Check if appointment exists
    if(!$appointment) {
        // If appointment does not exist, redirect to the appointment management page with an error message
        header("Location: staff-appointment.php?error=Appointment not found.");
        exit();
    }
} else {
    // If appointment ID is not provided in the URL, redirect to the appointment management page
    header("Location: staff-appointment.php");
    exit();
}

// Query to fetch customers (users with role 'customer')
$stmt_customers = $pdo->query("SELECT user_id, username FROM user WHERE role = 'customer'");
$customers = $stmt_customers->fetchAll(PDO::FETCH_ASSOC);

// Query to fetch services available for the logged-in staff
$stmt_services = $pdo->prepare("
    SELECT s.service_id, s.service_name
    FROM service s
    JOIN staff_availability sa ON s.service_id = sa.service_id
    WHERE sa.staff_id = ?
");
$stmt_services->execute([$user_id]);
$services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

// Fetch staff availability date range
$stmt_availability = $pdo->prepare("
    SELECT MIN(available_start_date) AS available_start_date, MAX(available_end_date) AS available_end_date
    FROM staff_availability
    WHERE staff_id = ?
");
$stmt_availability->execute([$user_id]);
$availability = $stmt_availability->fetch(PDO::FETCH_ASSOC);
$available_start_date = $availability['available_start_date'];
$available_end_date = $availability['available_end_date'];

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
    echo "<script>alert('$error_message');</script>";
  }
  if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
    echo "<script>alert('$success_message');</script>";
  }
  
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Update Appointment</title>
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico"/>
    <link rel="stylesheet" href="css/staff-style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.js"></script>
</head>
<body>
<div class="sidebar close">
    <div class="logo-details">
      <img src="favicon/favicon.ico" alt="logo" style="padding-left: 16px; padding-top: 14px;">
      <span class="logo_name">Elvira True Beauty</span>
    </div>
    <ul class="nav-links">
      <li>
        <a href="staff-dashboard.php">
          <i class='bx bx-grid-alt' ></i>
          <span class="link_name">Dashboard</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="#">Dashboard</a></li>
        </ul>
      </li>
      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'staff-appointment.php' ? 'active' : ''; ?>">
        <a href="staff-appointment.php">
          <i class='bx bx-calendar-check'></i>
          <span class="link_name">Appointment Management</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="#">Appointment Management</a></li>
        </ul>
      </li>
      <li>
        <a href="staff-availability.php">
          <i class='bx bx-check-circle'></i>
          <span class="link_name">Availability Management</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="#">Availability Management</a></li>
        </ul>
      </li>
      <li>
        <a href="staff-customer.php">
          <i class='bx bxs-user-detail' ></i>
          <span class="link_name">Customer Management</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="staff-customer.php">Customer Management</a></li>
        </ul>
      </li>
    </div>
  </li>
</ul>
  </div>
  <section class="home-section">
    <div class="home-content">
      <i class='bx bx-menu' ></i>
      <span class="text">Appointment Management System</span>

      <!--Account Management-->
      <div class="user-dropdown">
        <button class="user-dropdown-btn">
        <i class="bx bx-user"></i> <?php echo isset($staff_name) ? $staff_name : ''; ?> 
        </button>
        <div class="user-dropdown-content">
        <a href="view-profile.php">View Profile</a>
            <a href="change-pass.php">Change Password</a>
            <a class="link_name logout-link" href="#">Logout</a>
        </div>
      </div>
    </div>

    <div class="content">
        <div class="content-header">
            <h3 class="content-title">Update Appointment</h3>
        </div>

        <!-- Appointment Form Container -->
        <div class="staff-new-app-form-container">
            <form class="staff-new-app-form" action="staff-update-appointment.php" method="post">
                <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                <div class="staff-new-app-datetime-container">
                    <div class="staff-new-app-row">
                        <div class="staff-new-app-date-container">
                            <label for="customer">Customer:</label>
                            <input type="text" name="customer" id="customer" value="<?php echo htmlspecialchars($appointment['customer_name']); ?>" readonly>
                            <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
                        </div>
                    </div>
                    <div class="staff-new-app-row">
                        <div class="staff-new-app-date-container">
                            <label for="service">Service:</label>
                            <input type="text" name="service" id="service" value="<?php echo htmlspecialchars($appointment['service_name']); ?>" readonly>
                            <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                        </div>
                    </div>
                    <div class="staff-new-app-row">
                        <div class="staff-new-app-date-container">
                            <label for="appointmentDate">Appointment Date:</label>
                            <input type="text" name="appointmentDate" id="appointmentDate" value="<?php echo htmlspecialchars($appointment['appointment_date']); ?>" required>
                        </div>
                        <div class="staff-new-app-date-container">
                            <label for="appointmentTime">Appointment Time:</label>
                            <input type="text" name="appointmentTime" id="appointmentTime" value="<?php echo htmlspecialchars($appointment['appointment_time']); ?>" required>
                        </div>
                    </div>
                    <div class="staff-new-app-row">
                        <div class="staff-new-app-date-container">
                            <label for="status">Status:</label>
                            <select id="status" name="status" required>
                                <option value="Coming Soon" <?php if($appointment['appointment_status'] == 'Coming Soon') echo 'selected'; ?>>Coming Soon</option>
                                <option value="Completed" <?php if($appointment['appointment_status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                            </select>
                        </div>
                    </div>
                </div>
                <br>

                <div class="staff-new-app-btn-container">
                    <!-- Form submission button -->
                    <button class="staff-new-app-btn-submit btn btn-flat btn-primary" type="submit"><i class='bx bx-plus'></i> Update Appointment</button>
                    <!-- Cancel button (this can be a link to go back or any other action) -->
                    <a href="staff-appointment.php" class="staff-new-app-btn-cancel btn btn-flat btn-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // For toggling the sidebar
        document.querySelectorAll(".arrow").forEach(arrow => {
            arrow.addEventListener("click", function(event) {
                const arrowParent = event.target.closest(".menu-item"); // Select the main parent of the arrow
                arrowParent.classList.toggle("showMenu");
            });
        });

        const sidebar = document.querySelector(".sidebar");
        const sidebarBtn = document.querySelector(".bx-menu");

        // Toggle the sidebar when the sidebar button is clicked
        sidebarBtn.addEventListener("click", function() {
            sidebar.classList.toggle("close");
        });

        // Handle logout confirmation
        document.addEventListener("DOMContentLoaded", function() {
            const logoutLink = document.querySelector('.logout-link');
            
            // Add click event listener to the logout link
            logoutLink.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent the default link behavior

                // Show confirmation dialog
                const confirmLogout = confirm('Are you sure you want to logout?');
                
                // If the user confirms logout, redirect to the logout page
                if (confirmLogout) {
                    window.location.href = 'logout.php';
                }
            });
        });

        $(document).ready(function() {
            // Function to convert 12-hour time to 24-hour time
            function convertTo24Hour(time) {
                var hours = Number(time.match(/^(\d+)/)[1]);
                var minutes = Number(time.match(/:(\d+)/)[1]);
                var AMPM = time.match(/\s(.*)$/)[1];

                if (AMPM === "PM" && hours < 12) hours = hours + 12;
                if (AMPM === "AM" && hours == 12) hours = hours - 12;

                var sHours = hours.toString();
                var sMinutes = minutes.toString();

                if (hours < 10) sHours = "0" + sHours;
                if (minutes < 10) sMinutes = "0" + sMinutes;

                return sHours + ":" + sMinutes;
            }

            // Datepicker initialization with beforeShowDay function to disable Wednesdays
            $('#appointmentDate').datepicker({
                dateFormat: 'yy-mm-dd',
                minDate: '<?php echo $available_start_date; ?>',
                maxDate: '<?php echo $available_end_date; ?>',
                beforeShowDay: function(date) {
                    return [date.getDay() !== 3]; // Disable Wednesdays (where 0 is Sunday, 1 is Monday, etc.)
                }
            });

            // Timepicker initialization
            $('#appointmentTime').timepicker({
                timeFormat: 'h:i A', // 12-hour format with AM/PM
                step: 15,            // 15-minute intervals
                minTime: '11:00 AM',
                maxTime: '6:00 PM'
            });

            // Form submission handler
            $('.staff-new-app-form').submit(function() {
                // Convert the selected time to 24-hour format
                var selectedTime = $('#appointmentTime').val();
                var time24Hour = convertTo24Hour(selectedTime);

                // Update the input field with the converted time
                $('#appointmentTime').val(time24Hour);
            });
        });

    </script>
</body>
</html>