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

// Fetch staff availability data from the database for the current logged-in user with service name
$stmt = $pdo->prepare("SELECT a.*, s.service_name FROM staff_availability a JOIN service s ON a.service_id = s.service_id WHERE a.staff_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$availabilityList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if update success message is set
if (isset($_GET['update']) && $_GET['update'] == 'success') {
  // Display JavaScript alert message
  echo "<script>alert('Availability update successful!');</script>";
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
    <style>
        .hidden {
            display: none;
        }
    </style>
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
      <li>
        <a href="staff-appointment.php">
          <i class='bx bx-calendar-check'></i>
          <span class="link_name">Appointment Management</span>
        </a>
        <ul class="sub-menu blank">
          <li><a class="link_name" href="#">Appointment Management</a></li>
        </ul>
      </li>
      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'staff-availability.php' ? 'active' : ''; ?>">
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

    <div class="content hidden">
      <div class="content-header">
        <h3 class="content-title">List of Availability</h3>
        <div class="content-tools">
            <a href="staff-create-availability.php" class="btn btn-flat btn-primary"><i class='bx bx-plus'></i> Create New</a>
        </div>
      </div>

    <!-- Availability List Container -->
    <div class="availability-list-container">
      <table class="availability-list-table">
        <thead>
          <tr>
            <th>No</th>
            <th>Service Name</th>
            <th>From Date</th>
            <th>Until Date</th>
            <th>Date Created</th>
            <th>Date Updated</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // Display each availability entry
          foreach ($availabilityList as $key => $availability) {
              echo '<tr>';
              echo '<td>' . ($key + 1) . '</td>';
              echo '<td>' . $availability['service_name'] . '</td>';
              echo '<td>' . date("d M Y", strtotime($availability['available_start_date'])) . '</td>';
              echo '<td>' . date("d M Y", strtotime($availability['available_end_date'])) . '</td>';
              echo '<td>' . date("d M Y H:i:s", strtotime($availability['date_created'])) . '</td>';
              echo '<td>' . date("d M Y H:i:s", strtotime($availability['date_updated'])) . '</td>';              
              echo '<td>';
              echo '<div class="button-container">';
              echo '<a href="staff-view-availability.php?availability_id=' . $availability['availability_id'] . '" class="staff-update-availability-btn">Update</a>';
              echo '</div>';
              echo '<div class="button-container">';
              echo '<a href="#" class="staff-delete-availability-btn" onclick="confirmDelete(' . $availability['availability_id'] . ')">Delete</a>';
              echo '</div>';
              echo '</td>';
              echo '</tr>';
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    // Function to handle the security answer response from the popup window
    function handleSecurityAnswer(answer) {
            if (answer === 'match') {
                alert('Security answer matched. Access granted.');
                // Reveal the hidden content
                var content = document.querySelector('.content');
                content.classList.remove('hidden');
                // Add code here to reveal the hidden content or perform further actions
            } else {
                alert('Security answer did not match. Access denied.');
            }
        }

    // Function to toggle the visibility of the content
    function toggleContentVisibility() {
        var content = document.querySelector('.content');
        content.classList.toggle('hidden');
    }

    // Function to show the pop-up window
    function showSecurityQuestionsPopup() {
        // Get the user ID
        var userID = <?php echo isset($user_id) ? $user_id : 'null'; ?>;
        
        // Create a new window with the security questions form, passing the user ID as a query parameter
        var securityQuestionsPopup = window.open('security_questions_popup.php?user_id=' + userID, '_blank', 'width=600,height=400');
        
        // Focus the new window
        if (window.focus) {
            securityQuestionsPopup.focus();
        }
    }
    
    // Call the function to show the pop-up window when the page loads
    window.onload = function() {
        showSecurityQuestionsPopup();
    };

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

    function confirmDelete(availabilityId) {
    const confirmed = confirm('Are you sure you want to delete this availability?');
    if (confirmed) {
        window.location.href = 'staff-delete-availability.php?availability_id=' + availabilityId;
    }
}
  </script>
</body>
</html>