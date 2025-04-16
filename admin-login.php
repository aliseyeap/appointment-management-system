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

    // Initialize $roleFilter variable
    $roleFilter = isset($_GET['role']) ? $_GET['role'] : 'all';

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
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Login Management</title>
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico"/>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>

    <style>
        .hidden {
            display: none;
        }
    </style>
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
      <li  class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin-login.php' ? 'active' : ''; ?>">
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

    <div class="content hidden">
      <div class="content-header">
        <h3 class="content-title">User Login Logs</h3>
      </div>

      <!--Login Logs-->
      <div class="login-logs-container">

        <!--Login Logs Filter-->
        <div class="login-logs-filter">
            <form id="role-filter-form" method="GET">
                <label for="role-filter">Filter by Role:</label>
                <select id="role-filter" name="role">
                    <option value="all" <?php echo $roleFilter === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    <option value="customer" <?php echo $roleFilter === 'customer' ? 'selected' : ''; ?>>Customer</option>
                    <option value="staff" <?php echo $roleFilter === 'staff' ? 'selected' : ''; ?>>Staff</option>
                </select>
            </form>
        </div>

        <!--List of Login Logs-->
        <table class="login-logs-table">
          <thead>
          <tr>
            <th style="width: 50px;">No</th>
            <th style="width: 200px;">Username</th>
            <th style="width: 100px;">Role</th>
            <th style="width: 150px;">Login Time</th>
          </tr>
          </thead>
          <tbody>
          <?php
            // Initialize counter variable
            $counter = 1;

            // Fetch user login logs from the database based on the selected role
            $roleFilter = isset($_GET['role']) ? $_GET['role'] : 'all';
            $roleCondition = $roleFilter !== 'all' ? "AND user.role = '$roleFilter'" : "";
            $stmt_logs = $pdo->query("SELECT login_logs.login_time, user.username, user.role FROM login_logs JOIN user ON login_logs.user_id = user.user_id WHERE 1 $roleCondition ORDER BY login_logs.login_time DESC");

            // Iterate over the fetched logs and display them in table rows
            while ($log = $stmt_logs->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>{$counter}</td>"; // Display the counter value in the "No" column
                echo "<td>{$log['username']}</td>";
                echo "<td>{$log['role']}</td>";
                echo "<td>" . date(' d M Y H:i:s', strtotime($log['login_time'])) . "</td>";
                echo "</tr>";

                // Increment the counter for the next row
                $counter++;
            }
          ?>
          </tbody>
      </table>           
    </div>
</section>
  

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

    //Filter by Role
    document.getElementById("role-filter").addEventListener("change", function() {
        document.getElementById("role-filter-form").submit();
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
  </script>
</body>
</html>

