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
    
    // Check if user is staff
    if($user['role'] == 'staff') {
        // Retrieve staff name
        $staff_name = $user['username'];

        // Prepare SQL query to retrieve appointments for the logged-in staff, sorted by appointment date
        $stmt_appointments = $pdo->prepare("SELECT a.*, s.service_name, c.username AS customer_name
                                            FROM appointment a
                                            LEFT JOIN service s ON a.service_id = s.service_id
                                            LEFT JOIN user c ON a.customer_id = c.user_id
                                            WHERE a.staff_id = ?
                                            ORDER BY a.appointment_date ASC, a.appointment_time ASC");
        $stmt_appointments->execute([$user_id]);
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

// Check for success message
if(isset($_GET['success'])) {
  echo "<script>alert('" . $_GET['success'] . "')</script>";
}

// Check for error message
if(isset($_GET['error'])) {
  echo "<script>alert('" . $_GET['error'] . "')</script>";
}

// Function to convert 24-hour time format to specific AM/PM format
function convertToCustomFormat($time24) {
  $time12 = date("g:i A", strtotime($time24));
  return $time12;
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Appointment Management</title>
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico"/>
    <link rel="stylesheet" href="css/staff-style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

<script>
    // Check if there is a success message and display it
    <?php if (isset($_SESSION['success_message'])): ?>
        alert("<?php echo $_SESSION['success_message']; ?>");
        <?php unset($_SESSION['success_message']); ?> // Clear the success message from session
    <?php endif; ?>
</script>

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
        <h3 class="content-title">List of Appointment</h3>
        <div class="content-tools">
          <a href="staff-create-appointment.php" class="btn btn-flat btn-primary"><i class='bx bx-plus'></i> Create New</a>
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
              <th>Customer</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($appointments as $key => $appointment): ?>
              <tr>
                <td><?php echo $key + 1; ?></td>
                <td><?php echo date('d M Y H:i:s', strtotime($appointment['date_created'])); ?></td> <!-- Modify this line -->
                <td><?php echo date('d F Y', strtotime($appointment['appointment_date'])); ?></td> <!-- Modify this line -->
                <td><?php echo convertToCustomFormat($appointment['appointment_time']); ?></td>
                <td><?php echo $appointment['service_name']; ?></td>
                <td><?php echo $appointment['customer_name']; ?></td>
                <td><?php echo $appointment['appointment_status']; ?></td>
                <!-- Update and Cancel buttons -->
                <td>
                  <?php if ($appointment['appointment_status'] === 'Completed'): ?>
                    <span class="completed-status">Completed</span>
                  <?php else: ?>
                    <a href="staff-view-appointment.php?appointment_id=<?php echo $appointment['appointment_id']; ?>" class="staff-update-appointment-btn">Update</a>
                    <a href="#" class="staff-delete-appointment-btn" onclick="confirmDelete(<?php echo $appointment['appointment_id']; ?>)">Cancel</a>
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
    //For close tab
    let arrow = document.querySelectorAll(".arrow");
    for (var i = 0; i < arrow.length; i++) {
      arrow[i].addEventListener("click", (e)=>{
        let arrowParent = e.target.parentElement.parentElement; // selecting main parent of arrow
        arrowParent.classList.toggle("showMenu");
      });
    }
    let sidebar = document.querySelector(".sidebar");
    let sidebarBtn = document.querySelector(".bx-menu");
    console.log(sidebarBtn);
    sidebarBtn.addEventListener("click", ()=>{
      sidebar.classList.toggle("close");
    });

    // Logout confirmation
    document.addEventListener("DOMContentLoaded", function() {
      // Select the logout link
      const logoutLink = document.querySelector('.logout-link');
      
      // Add click event listener to the logout link
      logoutLink.addEventListener('click', function(event) {
        // Prevent the default behavior of the link
        event.preventDefault();

        // Show confirmation dialog
        const confirmLogout = confirm('Are you sure you want to logout?');
        
        // If user confirms logout, redirect to logout page
        if (confirmLogout) {
          window.location.href = 'logout.php';
        }
      });
    });

    // Function to handle appointment cancellation
    function confirmDelete(appointmentId) {
      const confirmed = confirm('Are you sure you want to cancel this appointment?');
      if (confirmed) {
          window.location.href = 'staff-delete-appointment.php?appointment_id=' + appointmentId;
      }
    }
  </script>

</body>
</html>
