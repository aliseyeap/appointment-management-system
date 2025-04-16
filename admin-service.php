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
  if($user['role'] == 'admin') {
      // Retrieve admin name
      $admin_name = $user['username'];
  }

} else {
  // Redirect to login page or handle unauthorized access
  header("Location: login.php");
  exit(); // Stop script execution
}

// Retrieve service data from the database
$stmt_services = $pdo->query("SELECT * FROM service");
$serviceList = $stmt_services->fetchAll(PDO::FETCH_ASSOC);

// Check if success message is set in session
if (isset($_SESSION['success_message'])) {
  // Display the success message
  echo "<script>alert('" . $_SESSION['success_message'] . "');</script>";
  // Unset the session variable to clear the message
  unset($_SESSION['success_message']);
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Service Management</title>
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico"/>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
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
      <li>
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
      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin-service.php' ? 'active' : ''; ?>">
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
        <h3 class="content-title">List of Service</h3>
        <div class="content-tools">
          <a href="admin-create-service.php" class="btn btn-flat btn-primary"><i class='bx bx-plus'></i> Create New</a>
        </div>
      </div>

      <!-- Service List Container -->
      <div class="service-list-container">
          <table class="service-list-table">
            <thead>
              <tr>
                  <th style="width: 50px;">No</th>
                  <th style="width: 100px;">Service ID</th>
                  <th style="width: 150px;">Service Image</th>
                  <th style="width: 150px;">Service Name</th>
                  <th style="width: 400px;">Service Description</th>
                  <th style="width: 100px;">Price (RM)</th>
                  <th style="width: 100px;">Duration</th>
                  <th style="width: 150px;">Date Created</th>
                  <th style="width: 150px;">Date Updated</th>
                  <th style="width: 150px;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($serviceList as $key => $service): ?>
                    <td><?php echo $key + 1; ?></td>
                    <td>SER6783<?php echo $service['service_id']; ?></td>
                    <td><img src="<?php echo $service['service_image']; ?>" alt="<?php echo $service['service_name']; ?>" width="150"></td>
                    <td><?php echo ucwords($service['service_name']); ?></td>
                    <td><?php echo $service['service_description']; ?></td>
                    <td><?php echo $service['service_price']; ?></td>
                    <td>
                      <?php 
                        // Convert decimal hours to hours and minutes
                        $duration_hours = floor($service['service_duration']); // Get the whole number part (hours)
                        $duration_minutes = ($service['service_duration'] - $duration_hours) * 60; // Get the decimal part (minutes)
                        
                        // Check if duration is less than 1 hour
                        if ($duration_hours == 0) {
                            echo $duration_minutes . ' minutes'; // Display only minutes
                        } else {
                            echo $duration_hours . ' hours ' . $duration_minutes . ' minutes'; // Display hours and minutes
                        }
                      ?>
                    </td>
                    <td><?php echo date("d M Y H:i:s", strtotime($service['date_created'])); ?></td>
                    <td><?php echo date("d M Y H:i:s", strtotime($service['date_updated'])); ?></td>

                    <td>
                    <!-- Update button container -->
                    <div class="button-container">
                        <a href="admin-view-service.php?service_id=<?php echo $service['service_id']; ?>" class="admin-update-service-btn">Update</a>
                    </div>
                    
                    <!-- Delete button container -->
                    <div class="button-container">
                        <a href="#" class="admin-delete-service-btn" onclick="confirmDelete('<?php echo $service['service_name']; ?>', <?php echo $service['service_id']; ?>)">Delete</a>
                    </div>
                    </td>
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
        let arrowParent = e.target.parentElement.parentElement;//selecting main parent of arrow
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

    function confirmDelete(service_name, service_id) {
            if (confirm("Are you sure you want to delete " + service_name + "?")) {
                // If user confirms, redirect to delete-service.php with service id as parameter
                window.location.href = "admin-delete-service.php?service_id=" + service_id;
            }
        }
  </script>
</body>
</html>