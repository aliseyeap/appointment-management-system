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
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Role Management</title>
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
      <li  class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin-role.php' ? 'active' : ''; ?>">
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
            <h3 class="content-title">List of All Users</h3>
        </div>
        
        <!-- User List -->
        <div class="user-list-container">
            <!-- List of Users -->
            <table class="user-list-table">
                <thead>
                    <tr>
                      <th style="width: 50px;">No</th>
                      <th style="width: 150px;">User ID</th>
                      <th style="width: 200px;">Username</th>
                      <th style="width: 100px;">Gender</th>
                      <th style="width: 200px;">Email</th>
                      <th style="width: 150px;">Phone Number</th>
                      <th style="width: 150px;">Date Registered</th>
                      <th style="width: 100px;">Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                      // Initialize counter variable
                      $counter = 1;

                      // Fetch users from the database excluding those with the role 'admin'
                      $stmt_users = $pdo->query("SELECT * FROM user WHERE role != 'admin'");

                      // Iterate over the fetched users and display them in table rows
                      while ($user = $stmt_users->fetch(PDO::FETCH_ASSOC)) {
                          // Format date registered as desired format
                          $date_registered = date("d M Y H:i:s", strtotime($user['date_created']));
                          
                          // Concatenate "US9527" with user ID
                          $user_id_display = "US9527" . $user['user_id'];
                    ?>
                    <tr>
                      <td><?php echo $counter; ?></td>
                      <td><?php echo $user_id_display; ?></td>
                      <td><?php echo $user['username']; ?></td>
                      <td style="text-transform: capitalize;"><?php echo $user['gender']; ?></td>
                      <td style="text-transform: lowercase;"><?php echo $user['email']; ?></td>
                      <td><?php echo $user['phone_number']; ?></td>
                      <td><?php echo $date_registered; ?></td>
                      <td>
                          <form action="admin-update-role.php" method="post">
                              <select name="role" class="role-dropdown" data-user-id="<?php echo $user['user_id']; ?>" onchange="this.form.submit()">
                                  <option value="customer" <?php echo $user['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                                  <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                              </select>
                              <input type="hidden" name="userId" value="<?php echo $user['user_id']; ?>">
                          </form>
                      </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
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

      
        // For close tab
        let arrow = document.querySelectorAll(".arrow");
        for (var i = 0; i < arrow.length; i++) {
            arrow[i].addEventListener("click", (e) => {
                let arrowParent = e.target.parentElement.parentElement; //selecting main parent of arrow
                arrowParent.classList.toggle("showMenu");
            });
        }
        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".bx-menu");
        console.log(sidebarBtn);
        sidebarBtn.addEventListener("click", () => {
            sidebar.classList.toggle("close");
        });

        // Logout confirmation
        document.addEventListener("DOMContentLoaded", function () {
            // Select the logout link
            const logoutLink = document.querySelector('.logout-link');

            // Add click event listener to the logout link
            logoutLink.addEventListener('click', function (event) {
                // Prevent the default behavior of the link
                event.preventDefault();

                // Show confirmation dialog
                const confirmLogout = confirm('Are you sure you want to logout?');

                // If user confirms logout, redirect to logout page
                if (confirmLogout) {
                    window.location.href = 'logout.php';
                }
            });

            // Add click event listener to the details buttons
            const detailsButtons = document.querySelectorAll('.message-details-btn');
            detailsButtons.forEach(button => {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    const messageID = this.getAttribute('data-message-id');
                    // Redirect to details page with the message ID
                    window.location.href = `admin-view-message-details.php?message_id=${messageID}`;
                });
            });
        });

        // Add event listener to the role dropdown menus
        document.addEventListener("DOMContentLoaded", function() {
            const roleDropdowns = document.querySelectorAll('.role-dropdown');
            roleDropdowns.forEach(function(dropdown) {
                dropdown.addEventListener('change', function() {
                    // Submit the form when dropdown selection changes
                    this.closest('form').submit();
                });
            });
        });

        // Check if the success parameter is present in the URL
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');

        if (success) {
            // Display an alert message
            alert('User role updated successfully.');
        }
    </script>
</body>
</html>