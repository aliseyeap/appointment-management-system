<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php';
include 'session-termination.php';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // Retrieve user ID from session
    $user_id = $_SESSION['user_id'];

    // Prepare SQL query to retrieve user information based on user ID
    $stmt_user = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    
    // Check if user is admin
    if ($user['role'] == 'admin') {
        // Retrieve admin name
        $admin_name = $user['username'];

        // Prepare SQL query to retrieve all appointments
        $stmt_appointments = $pdo->prepare("SELECT a.*, s.service_name, c.username AS customer_name, u.username AS staff_name
                                            FROM appointment a
                                            LEFT JOIN service s ON a.service_id = s.service_id
                                            LEFT JOIN user c ON a.customer_id = c.user_id
                                            LEFT JOIN user u ON a.staff_id = u.user_id
                                            ORDER BY a.appointment_date ASC, a.appointment_time ASC");
        $stmt_appointments->execute();
        $appointments = $stmt_appointments->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Redirect to login page or handle unauthorized access
        header("Location: login.php");
        exit(); // Stop script execution
    }
} else {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

// Function to convert 24-hour time format to specific AM/PM format
function convertToCustomFormat($time24) {
    $hour = intval(substr($time24, 0, 2));
    $minute = substr($time24, 3, 2);
    $meridiem = $hour >= 12 ? 'PM' : 'AM';
    $hour = $hour % 12;
    if ($hour == 0) {
        $hour = 12;
    }
    return sprintf('%02d:%s %s', $hour, $minute, $meridiem);
}

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
        <h3 class="content-title">List of Appointments</h3>
        <div class="content-tools">
          <a href="admin-create-appointment.php" class="btn btn-flat btn-primary"><i class='bx bx-plus'></i> Create New</a>
        </div>
      </div>

      <!-- Appointment List Container -->
      <div class="appointment-list-container">
        <table class="appointment-list">
          <thead>
            <tr>
              <th>No</th>
              <th>Date Created</th>
              <th>Appointment Date</th>
              <th>Appointment Time</th>
              <th>Service</th>
              <th>Customers</th>
              <th>Staff</th> 
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($appointments as $key => $appointment): ?>
              <tr>
                <td><?php echo $key + 1; ?></td>
                <td><?php echo date('d M Y H:i:s', strtotime($appointment['date_created'])); ?></td>
                <td><?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?></td>
                <td><?php echo convertToCustomFormat($appointment['appointment_time']); ?></td>
                <td><?php echo $appointment['service_name']; ?></td>
                <td><?php echo $appointment['customer_name']; ?></td>
                <td><?php echo $appointment['staff_name']; ?></td>
                <td><?php echo $appointment['appointment_status']; ?></td>
                <!-- Update and Cancel buttons -->
                <td>
                    <?php if ($appointment['appointment_status'] === 'Completed'): ?>
                        <span class="completed-status">Completed</span>
                    <?php else: ?>
                        <a href="admin-view-appointment.php?appointment_id=<?php echo $appointment['appointment_id']; ?>" class="admin-update-appointment-btn">Update</a>
                        <a href="#" class="admin-delete-appointment-btn" onclick="confirmDelete(<?php echo $appointment['appointment_id']; ?>)">Cancel</a>
                    <?php endif; ?>
                </td>
                <style>
                  .completed-status {
                      color: gray;
                      font-style: italic;
                    }
                </style>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <script>
    // For closing tab
    let arrow = document.querySelectorAll(".arrow");
    for (let i = 0; i < arrow.length; i++) {
      arrow[i].addEventListener("click", (e) => {
        let arrowParent = e.target.parentElement.parentElement; // selecting main parent of arrow
        arrowParent.classList.toggle("showMenu");
      });
    }

    let sidebar = document.querySelector(".sidebar");
    let sidebarBtn = document.querySelector(".bx-menu");
    sidebarBtn.addEventListener("click", () => {
      sidebar.classList.toggle("close");
    });

    // Logout confirmation
    document.addEventListener("DOMContentLoaded", function() {
      const logoutLink = document.querySelector('.logout-link');
      logoutLink.addEventListener('click', function(event) {
        event.preventDefault();
        const confirmLogout = confirm('Are you sure you want to logout?');
        if (confirmLogout) {
          window.location.href = 'logout.php';
        }
      });
    });

    // Function to handle appointment cancellation
    function confirmDelete(appointmentId) {
      const confirmed = confirm('Are you sure you want to cancel this appointment?');
      if (confirmed) {
        window.location.href = 'admin-delete-appointment.php?appointment_id=' + appointmentId;
      }
    }

    // Check if update was successful
    if (<?php echo isset($_GET['updateSuccess']) ? json_encode($_GET['updateSuccess']) : 'false'; ?>) {
      alert('Appointment updated successfully.');
    }

    // Check if there was an error during update
    if (<?php echo isset($_GET['updateError']) ? json_encode($_GET['updateError']) : 'false'; ?>) {
      alert('Failed to update appointment. The selected date and time conflict with an existing appointment.');
    }
  </script>
</body>
</html>
