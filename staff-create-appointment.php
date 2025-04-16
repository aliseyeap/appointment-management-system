<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php'; 

include 'session-termination.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

// Retrieve user ID from session
$user_id = $_SESSION['user_id'];

// Prepare SQL query to retrieve user information based on user ID
$stmt_user = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Check if user is staff
if($user['role'] != 'staff') {
    // Handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

// Retrieve staff name
$staff_name = $user['username'];

// Query to fetch customers (users with role 'customer')
$stmt_customers = $pdo->query("SELECT user_id, username FROM user WHERE role = 'customer'");
$customers = $stmt_customers->fetchAll(PDO::FETCH_ASSOC);

// Query to fetch all services
$stmt_services = $pdo->query("SELECT service_id, service_name FROM service");
$services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

// Query to fetch the staff's availability date range
$stmt_staff_availability = $pdo->prepare("
    SELECT sa.service_id, DATE_FORMAT(sa.available_start_date, '%Y-%m-%d') AS available_start_date, DATE_FORMAT(sa.available_end_date, '%Y-%m-%d') AS available_end_date
    FROM staff_availability sa
    WHERE sa.staff_id = ?
");
$stmt_staff_availability->execute([$user_id]);
$staff_availability = $stmt_staff_availability->fetchAll(PDO::FETCH_ASSOC);

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

<!-- Pass PHP data to JavaScript -->
<script>
    const staffAvailability = <?php echo json_encode($staff_availability); ?>;
</script>
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
    <div class="home-content" style="margin-top: -25px;">
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
        <h3 class="content-title">Create New Appointment</h3>
      </div>

      <!-- Appointment Form Container -->
      <div class ="staff-new-app-form-container">
        <form class="staff-new-app-form" action="staff-add-appointment.php" method="post">
        <div class="staff-new-app-datetime-container">
        <div class="staff-new-app-row">
          <div class="staff-new-app-date-container">
            <label for="customerName">Customer Name:</label>
            <select id="customerName" name="customerName" class="staff-new-app-select" required>
              <option value="" selected disabled>Please select customer</option>
              <?php foreach ($customers as $customer): ?>
                <option value="<?php echo $customer['user_id']; ?>"><?php echo $customer['username']; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="staff-new-app-row">
          <div class="staff-new-app-date-container">
            <label for="service">Service:</label>
            <select id="service" name="service" class="staff-new-app-select" required>
              <option value="">Select Service</option>
              <?php foreach ($services as $service): ?>
                <option value="<?php echo $service['service_id']; ?>"><?php echo htmlspecialchars($service['service_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="staff-new-app-row">
          <div class="staff-new-app-date-container">
            <label for="appointmentDate">Select Date:</label>
            <input type="text" name="appointmentDate" id="appointmentDate" class="staff-new-app-input" placeholder="yyyy-mm-dd" required>
          </div>
          <div class="staff-new-app-date-container">
            <label for="appointmentTime">Select Time:</label>
            <input type="text" name="appointmentTime" id="appointmentTime" class="staff-new-app-input" placeholder="hh:mm" required>
          </div>
        </div>
      </div>

      <div class="staff-new-app-row">
        <div class="staff-new-app-date-container">
          <label for="status">Status:</label>
          <select id="status" name="status" class="staff-new-app-select" required>
            <option value="" selected disabled>Please select status</option>
            <option value="Completed">Completed</option>
            <option value="Coming Soon">Coming Soon</option>
          </select>
          </div>
      </div>

          <div class="staff-new-app-btn-container" style="width: calc(100%);">
            <!-- Form submission button -->
            <button class="staff-new-app-btn-submit btn btn-flat btn-primary" type="submit"><i class='bx bx-plus'></i> Add Appointment</button>
            <!-- Cancel button (this can be a link to go back or any other action) -->
            <a href="staff-appointment.php" class="staff-new-app-btn-cancel btn btn-flat btn-danger">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </section>

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
    // Populate the service dropdown based on the staff's availability
    $('#service').change(function() {
        const selectedServiceId = $(this).val();
        const filteredAvailability = staffAvailability.filter(item => item.service_id == selectedServiceId);

        // If the service is available for the staff, set the datepicker's minDate and maxDate
        if (filteredAvailability.length > 0) {
            const minDate = new Date(filteredAvailability[0].available_start_date);
            const maxDate = new Date(filteredAvailability[0].available_end_date);

            $('#appointmentDate').datepicker('option', 'minDate', minDate);
            $('#appointmentDate').datepicker('option', 'maxDate', maxDate);
            $('#appointmentDate').datepicker('refresh');
        }
    });

    // Initialize datepicker and timepicker
    $('#appointmentDate').datepicker({
        dateFormat: 'yy-mm-dd',
        beforeShowDay: function(date) {
            const day = date.getDay();
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Set time to midnight to compare only date parts

            // Disable Wednesdays and past dates
            if (day === 3 || date < today) {
                return [false];
            }
            return [true];
        }
    });

    $('#appointmentTime').timepicker({
        timeFormat: 'h:i A',
        step: 15,
        minTime: '11:00 AM',
        maxTime: '6:00 PM'
    });
  });
</script>

</body>
</html>
<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php'; 

include 'session-termination.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

// Retrieve user ID from session
$user_id = $_SESSION['user_id'];

// Prepare SQL query to retrieve user information based on user ID
$stmt_user = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Check if user is staff
if($user['role'] != 'staff') {
    // Handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

// Retrieve staff name
$staff_name = $user['username'];

// Query to fetch customers (users with role 'customer')
$stmt_customers = $pdo->query("SELECT user_id, username FROM user WHERE role = 'customer'");
$customers = $stmt_customers->fetchAll(PDO::FETCH_ASSOC);

// Query to fetch all services
$stmt_services = $pdo->query("SELECT service_id, service_name FROM service");
$services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

// Query to fetch the staff's availability date range
$stmt_staff_availability = $pdo->prepare("
    SELECT sa.service_id, DATE_FORMAT(sa.available_start_date, '%Y-%m-%d') AS available_start_date, DATE_FORMAT(sa.available_end_date, '%Y-%m-%d') AS available_end_date
    FROM staff_availability sa
    WHERE sa.staff_id = ?
");
$stmt_staff_availability->execute([$user_id]);
$staff_availability = $stmt_staff_availability->fetchAll(PDO::FETCH_ASSOC);

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

<!-- Pass PHP data to JavaScript -->
<script>
    const staffAvailability = <?php echo json_encode($staff_availability); ?>;
</script>
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
    <div class="home-content" style="margin-top: -25px;">
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
        <h3 class="content-title">Create New Appointment</h3>
      </div>

      <!-- Appointment Form Container -->
      <div class ="staff-new-app-form-container">
        <form class="staff-new-app-form" action="staff-add-appointment.php" method="post">
        <div class="staff-new-app-datetime-container">
        <div class="staff-new-app-row">
          <div class="staff-new-app-date-container">
            <label for="customerName">Customer Name:</label>
            <select id="customerName" name="customerName" class="staff-new-app-select" required>
              <option value="" selected disabled>Please select customer</option>
              <?php foreach ($customers as $customer): ?>
                <option value="<?php echo $customer['user_id']; ?>"><?php echo $customer['username']; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="staff-new-app-row">
          <div class="staff-new-app-date-container">
            <label for="service">Service:</label>
            <select id="service" name="service" class="staff-new-app-select" required>
              <option value="">Select Service</option>
              <?php foreach ($services as $service): ?>
                <option value="<?php echo $service['service_id']; ?>"><?php echo htmlspecialchars($service['service_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="staff-new-app-row">
          <div class="staff-new-app-date-container">
            <label for="appointmentDate">Select Date:</label>
            <input type="text" name="appointmentDate" id="appointmentDate" class="staff-new-app-input" placeholder="yyyy-mm-dd" required>
          </div>
          <div class="staff-new-app-date-container">
            <label for="appointmentTime">Select Time:</label>
            <input type="text" name="appointmentTime" id="appointmentTime" class="staff-new-app-input" placeholder="hh:mm" required>
          </div>
        </div>
      </div>

      <div class="staff-new-app-row">
        <div class="staff-new-app-date-container">
          <label for="status">Status:</label>
          <select id="status" name="status" class="staff-new-app-select" required>
            <option value="" selected disabled>Please select status</option>
            <option value="Completed">Completed</option>
            <option value="Coming Soon">Coming Soon</option>
          </select>
          </div>
      </div>

          <div class="staff-new-app-btn-container" style="width: calc(100%);">
            <!-- Form submission button -->
            <button class="staff-new-app-btn-submit btn btn-flat btn-primary" type="submit"><i class='bx bx-plus'></i> Add Appointment</button>
            <!-- Cancel button (this can be a link to go back or any other action) -->
            <a href="staff-appointment.php" class="staff-new-app-btn-cancel btn btn-flat btn-danger">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </section>

<script>
  // For toggling the sidebar
  document.querySelectorAll(".arrow").forEach(arrow => {
      arrow.addEventListener("click", function(event) {
          const arrowParent = event.target.closest(".menu-item"); // Select the main parent of the arrow
          arrowParent.classList.toggle("showMenu");
      });
  });

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
    // Populate the service dropdown based on the staff's availability
    $('#service').change(function() {
        const selectedServiceId = $(this).val();
        const filteredAvailability = staffAvailability.filter(item => item.service_id == selectedServiceId);

        // If the service is available for the staff, set the datepicker's minDate and maxDate
        if (filteredAvailability.length > 0) {
            const minDate = new Date(filteredAvailability[0].available_start_date);
            const maxDate = new Date(filteredAvailability[0].available_end_date);

            $('#appointmentDate').datepicker('option', 'minDate', minDate);
            $('#appointmentDate').datepicker('option', 'maxDate', maxDate);
            $('#appointmentDate').datepicker('refresh');
        }
    });

    // Initialize datepicker and timepicker
    $('#appointmentDate').datepicker({
        dateFormat: 'yy-mm-dd',
        beforeShowDay: function(date) {
            const day = date.getDay();
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Set time to midnight to compare only date parts

            // Disable Wednesdays and past dates
            if (day === 3 || date < today) {
                return [false];
            }
            return [true];
        }
    });

    $('#appointmentTime').timepicker({
        timeFormat: 'h:i A',
        step: 15,
        minTime: '11:00 AM',
        maxTime: '6:00 PM'
    });
  });
</script>

</body>
</html>
