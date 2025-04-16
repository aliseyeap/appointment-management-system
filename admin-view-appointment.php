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

// Check if user is admin
if($user['role'] != 'admin') {
    // Handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

// Retrieve admin name
$admin_name = $user['username'];

// Fetch appointment details based on appointment ID from URL parameter
if(isset($_GET['appointment_id'])) {
    $appointment_id = $_GET['appointment_id'];
    
    // Prepare SQL query to fetch appointment details
    $stmt_appointment = $pdo->prepare("SELECT * FROM appointment WHERE appointment_id = ?");
    $stmt_appointment->execute([$appointment_id]);
    $appointment = $stmt_appointment->fetch(PDO::FETCH_ASSOC);
    
    // Check if appointment exists
    if(!$appointment) {
        // Handle non-existent appointment ID
        echo "Appointment not found.";
        exit(); // Stop script execution
    }
    
    // Fetch customer details based on customer ID from appointment details
    $customer_id = $appointment['customer_id'];
    $stmt_customer = $pdo->prepare("SELECT username FROM user WHERE user_id = ?");
    $stmt_customer->execute([$customer_id]);
    $customer = $stmt_customer->fetch(PDO::FETCH_ASSOC);
    
    // Fetch service details based on service ID from appointment details
    $service_id = $appointment['service_id'];
    $stmt_service = $pdo->prepare("SELECT service_name FROM service WHERE service_id = ?");
    $stmt_service->execute([$service_id]);
    $service = $stmt_service->fetch(PDO::FETCH_ASSOC);
    
    // Fetch staff availability for the selected service
    $stmt_staff_availability = $pdo->prepare("
    SELECT sa.staff_id, u.username AS staff_name, sa.available_start_date, sa.available_end_date
    FROM staff_availability sa
    JOIN user u ON u.user_id = sa.staff_id
    WHERE sa.service_id = ?
    ");
    $stmt_staff_availability->execute([$service_id]);
    $staff_availability = $stmt_staff_availability->fetchAll(PDO::FETCH_ASSOC);

    // Encode staff availability data into JSON format
    $staff_availability_json = json_encode($staff_availability);

    // Fetch staff assigned to the appointment
    $assigned_staff_id = $appointment['staff_id'];

    // Fetch staff details based on assigned staff ID
    $stmt_staff = $pdo->prepare("SELECT user_id, username FROM user WHERE user_id = ?");
    $stmt_staff->execute([$assigned_staff_id]);
    $assigned_staff = $stmt_staff->fetch(PDO::FETCH_ASSOC);

} else {
    // Handle missing appointment ID
    echo "Appointment ID not provided.";
    exit(); // Stop script execution
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Elvira True Beauty | Appointment Management</title>
  <link rel="icon" type="image/x-icon" href="favicon/favicon.ico"/>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
  <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.js"></script>
</head>
<body>
<div class="sidebar close">
    <div class="logo-details">
      <img src="favicon/favicon.ico" alt="logo">
      <span class="logo_name">Elvira True Beauty</span>
    </div>
    <ul class="nav-links">
    <li>
        <a href="admin-dashboard.php">
          <i class='bx bx-grid-alt' ></i>
          <span class="link_name">Dashboard</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="admin-dashboard.php">Dashboard</a></li>
        </ul>
      </li>
      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin-appointment.php' ? 'active' : ''; ?>">
        <a href="admin-appointment.php">
          <i class='bx bx-calendar-check'></i>
          <span class="link_name">Appointment Management</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="admin-appointment.php">Appointment Management</a></li>
        </ul>
      </li>
      <li>
        <a href="admin-customer.php">
          <i class='bx bxs-user-detail' ></i>
          <span class="link_name">Customer Management</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="admin-customer.php">Customer Management</a></li>
        </ul>
      </li>
      <li>
        <a href="admin-login.php">
          <i class='bx bx-copy-alt' ></i>
          <span class="link_name">Login Management</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="admin-login.php">Login Management</a></li>
        </ul>
      </li>
      <li>
        <a href="admin-message.php">
          <i class='bx bx-chat' ></i>
          <span class="link_name">Message Management</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="admin-message.php">Message Management</a></li>
        </ul>
      </li>
      <li>
        <a href="admin-staff.php">
          <i class='bx bx-briefcase-alt-2'></i>
          <span class="link_name">Staff Management</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="admin-staff.php">Staff Management</a></li>
        </ul>
      </li>
      <li>
        <a href="admin-service.php">
          <i class='bx bx-spa'></i>
          <span class="link_name">Service Management</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="admin-service.php">Service Management</a></li>
        </ul>
      </li>
      <li>
        <a href="admin-report.php">
          <i class='bx bx-printer bx-flip-vertical' ></i>
          <span class="link_name">Report Management</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="admin-report.php">Report Management</a></li>
        </ul>
      </li>
      <li>
        <a href="admin-role.php">
          <i class='bx bx-check-shield' ></i>
          <span class="link_name">Role Management</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="admin-role.php">Role Management</a></li>
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
      <i class="bx bx-user"></i> <?php echo isset($admin_name) ? $admin_name : ''; ?> 
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

  <!-- Appointment Update Form Container -->
  <div class="admin-update-app-form-container">
      <form class="admin-update-app-form" action="admin-update-appointment.php" method="post">
          <input type="hidden" name="appointment_id" value="<?php echo $appointment_id; ?>">
          <div class="form-group">
              <label for="customerName">Customer Name:</label>
              <!-- Display the customer name -->
              <input type="text" id="customerName" name="customerName" class="admin-update-app-input" value="<?php echo htmlspecialchars($customer['username']); ?>" readonly>
              <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">
          </div>

          <div class="form-group">
              <label for="service">Service:</label>
              <!-- Display the service name -->
              <input type="text" id="service" name="service" class="admin-update-app-input" value="<?php echo htmlspecialchars($service['service_name']); ?>" readonly>
              <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
          </div>

          <div class="form-group">
              <label for="assignedTo">Assign To Staff:</label>
              <!-- Select dropdown for staff -->
              <select id="assignedTo" name="assignedTo" class="admin-update-app-select" required>
                  <?php foreach ($staff_availability as $staff): ?>
                      <?php if ($staff['staff_id'] == $assigned_staff_id): ?>
                          <!-- If this staff is currently assigned to the appointment, preselect it -->
                          <option value="<?php echo $staff['staff_id']; ?>" selected><?php echo htmlspecialchars($staff['staff_name']); ?></option>
                      <?php else: ?>
                          <!-- Otherwise, display as an option -->
                          <option value="<?php echo $staff['staff_id']; ?>"><?php echo htmlspecialchars($staff['staff_name']); ?></option>
                      <?php endif; ?>
                  <?php endforeach; ?>
              </select>
          </div>

          <div class="admin-update-app-datetime-container">
              <div class="admin-update-app-date-container">
                  <label for="appointmentDate">Select Date:</label>
                  <!-- Date input field -->
                  <input type="text" name="appointmentDate" id="appointmentDate" class="admin-update-app-input" value="<?php echo htmlspecialchars($appointment['appointment_date']); ?>" required>
              </div>
              <div class="admin-update-app-time-container">
                  <label for="appointmentTime">Select Time:</label>
                  <!-- Time input field -->
                  <input type="text" name="appointmentTime" id="appointmentTime" class="admin-update-app-input" value="<?php echo htmlspecialchars($appointment['appointment_time']); ?>" required>
              </div>
          </div>

          <div class="form-group">
              <label for="status">Status:</label>
              <!-- Select dropdown for status -->
              <select id="status" name="status" class="admin-update-app-select" required>
                  <option value="Completed" <?php if ($appointment['appointment_status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                  <option value="Coming Soon" <?php if ($appointment['appointment_status'] == 'Coming Soon') echo 'selected'; ?>>Coming Soon</option>
              </select>
          </div>

          <div class="admin-update-app-btn-container" style="width: calc(100%);">
              <!-- Form submission button -->
              <button class="admin-update-app-btn-submit btn btn-flat btn-primary" type="submit"><i class='bx bx-check'></i> Update</button>
              <!-- Cancel button (this can be a link to go back or any other action) -->
              <a href="admin-appointment.php" class="admin-update-app-btn-cancel btn btn-flat btn-danger">Cancel</a>
          </div>
      </form>
  </div>
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

    $(document).ready(function (){

    });

    $(function() {
      // Retrieve staff availability from PHP and parse it as JSON
      var staffAvailability = <?php echo $staff_availability_json; ?>;

      // Initialize datepicker
      $("#appointmentDate").datepicker({
          dateFormat: 'yy-mm-dd', // Display date in yyyy-mm-dd format
          minDate: 0, // Restrict to today or future dates
          beforeShowDay: function(date) {
              // Function to highlight available dates
              var dateString = jQuery.datepicker.formatDate('yy-mm-dd', date); // Format the date as yyyy-mm-dd

              // Block Wednesdays
              if (date.getDay() === 3) {
                  return [false, '', 'Unavailable on Wednesdays'];
              }

              for (var i = 0; i < staffAvailability.length; i++) {
                  // Loop through staff availability
                  if (dateString >= staffAvailability[i].available_start_date && dateString <= staffAvailability[i].available_end_date) {
                      // If date is within staff availability range, return true to highlight it
                      return [true, 'highlight'];
                  }
              }
              // If date is not within any staff availability range, return false to disable it
              return [false];
          }
      });

      // Set default date to the appointment date
      $("#appointmentDate").datepicker("setDate", "<?php echo $appointment['appointment_date']; ?>");
  });

  // Timepicker for selecting appointment time
  $(function() {
      $('#appointmentTime').timepicker({
          timeFormat: 'h:i A', // Display time in 12-hour format
          step: 15, // Interval of 15 minutes
          minTime: '11:00am', // Minimum time (11:00 AM)
          maxTime: '6:00pm', // Maximum time (6:00 PM)
          startTime: '11:00', // Start time for the time picker
          dynamic: false,
          dropdown: true,
          scrollbar: true
      });

      // Set default time to the appointment time
      $("#appointmentTime").val("<?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?>");
  });
</script>
</script>


</body>
</html>