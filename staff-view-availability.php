<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php'; 

include 'session-termination.php';

// Check if user is logged in
if(isset($_SESSION['user_id'])) {
    // Retrieve user ID from session
    $user_id = $_SESSION['user_id'];

    // Prepare SQL query to retrieve user information based on user ID
    $stmt_user = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    
    // Check if user is admin
    if($user['role'] == 'staff') {
        // Retrieve admin name
        $staff_name = $user['username'];
    }

} else {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

// Retrieve the availability ID from the query parameter
$availability_id = $_GET['availability_id'] ?? null;

// If no availability ID is provided, redirect to the availability management page
if (!$availability_id) {
    header("Location: staff-availability.php");
    exit();
}

// Prepare SQL query to retrieve the availability data based on the availability ID
$stmt_availability = $pdo->prepare("SELECT * FROM staff_availability WHERE availability_id = ?");
$stmt_availability->execute([$availability_id]);
$availability = $stmt_availability->fetch(PDO::FETCH_ASSOC);

// If no availability data is found, redirect to the availability management page
if (!$availability) {
    header("Location: staff-availability.php");
    exit();
}

// Retrieve the matching service details based on service_id from the staff_availability table
$service_id = $availability['service_id'];
$stmt_service = $pdo->prepare("SELECT * FROM service WHERE service_id = ?");
$stmt_service->execute([$service_id]);
$service = $stmt_service->fetch(PDO::FETCH_ASSOC);

// If no service data is found, redirect to the availability management page
if (!$service) {
    header("Location: staff-availability.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Availability Management</title>
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico"/>
    <link rel="stylesheet" href="css/staff-style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
  </head>
<body>
<div class="sidebar close">
    <div class="logo-details">
      <img src="favicon/favicon.ico" alt="logo" style="padding-left: 16px; padding-top: 14px;">
      <span class="logo_name">Elvira True Beauty</span>
    </div>
    <ul class="nav-links">
    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'staff-dashboard.php' ? 'active' : ''; ?>">
        <a href="staff-dashboard.php">
          <i class='bx bx-grid-alt' ></i>
          <span class="link_name">Dashboard</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="#">Dashboard</a></li>
        </ul>
      </li>
      <li>
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
        <h3 class="content-title">Update Availability</h3>
      </div>

    <!-- Availability Form Container -->
    <div class="staff-new-ava-form-container">
      <form class="staff-new-ava-form" action="staff-update-availability.php" method="post">
        <div class="staff-new-ava-datetime-container">
          <div class="staff-new-ava-date-container">
            <label for="fromDate">Available From:</label>
            <input type="text" name="fromDate" id="fromDate" placeholder="yyyy-mm-dd" style="color: grey;" class="staff-new-ava-input" required value="<?php echo $availability['available_start_date']; ?>">
          </div>
          <div class="staff-new-ava-date-container">
            <label for="untilDate">Until Date:</label>
            <input type="text" name="untilDate" id="untilDate" placeholder="yyyy-mm-dd" style="color: grey;" class="staff-new-ava-input" required value="<?php echo $availability['available_end_date']; ?>">
          </div>
        </div>
        <br>

        <div class="form-group">
          <label>Available Service:</label>
          <input type="text" id="serviceName" name="serviceName" value="<?php echo $service['service_name']; ?>" disabled>
          <br>
          <!-- Display service description -->
          <label>Service Description:</label>
          <textarea id="serviceDescription" name="serviceDescription" disabled><?php echo $service['service_description']; ?></textarea>
          
          
          <br>
          <!-- Display service duration -->
          <label>Service Duration (in Hours):</label>
          <input type="text" id="serviceDuration" name="serviceDuration" value="<?php echo $service['service_duration']; ?>" disabled>
        </div>
        <input type="hidden" name="availability_id" value="<?php echo $availability_id; ?>">
        
        <div class="staff-new-ava-btn-container">
          <!-- Form submission button -->
          <button class="staff-new-ava-btn-submit btn btn-flat btn-primary"><i class='bx bx-check'></i> Update</button>
            <!-- Cancel button (this can be a link to go back or any other action) -->
            <a href="staff-availability.php" class="staff-new-app-btn-cancel btn btn-flat btn-danger">Cancel</a>
        </div>
    </form>
    </div>
</div>

<script>
  // For toggling the sidebar
  document.querySelectorAll(".arrow").forEach(function (arrow) {
        arrow.addEventListener("click", function (event) {
            const arrowParent = event.target.closest(".menu-item");
            arrowParent.classList.toggle("showMenu");
        });
    });

    const sidebar = document.querySelector(".sidebar");
    const sidebarBtn = document.querySelector(".bx-menu");

    // Toggle the sidebar when the sidebar button is clicked
    sidebarBtn.addEventListener("click", function () {
        sidebar.classList.toggle("close");
    });

    // Handle logout confirmation
    document.addEventListener("DOMContentLoaded", function () {
        const logoutLink = document.querySelector('.logout-link');

        logoutLink.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default link behavior

            const confirmLogout = confirm('Are you sure you want to logout?');

            if (confirmLogout) {
                window.location.href = 'logout.php';
            }
        });
    });

    $(document).ready(function() {
        // Initialize date pickers with restrictions
        $("#fromDate, #untilDate").datepicker({
            dateFormat: "yy-mm-dd",
            minDate: 0, // Disable past dates
            beforeShowDay: function(date) {
                const day = date.getDay(); // Get day of the week (0 = Sunday, 1 = Monday, etc.)
                // Disable Wednesdays (3 is Wednesday)
                return [day !== 3];
            }
        });
    });
</script>
</body>
</html>