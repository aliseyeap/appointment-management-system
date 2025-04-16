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

  // Fetch staff data from the database
  $stmt = $pdo->query("SELECT * FROM user WHERE role = 'staff'");
  $staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Check if update success message is set
  if(isset($_GET['update']) && $_GET['update'] == 'success') {
    // Display JavaScript alert message
    echo "<script>alert('Staff ".$_GET['name']." has been successfully updated!');</script>";
  }

  // Check for status parameter and set message
  $status = isset($_GET['status']) ? $_GET['status'] : '';
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Staff Management</title>
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico"/>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

  <?php if ($status == 'success'): ?>
      <script>
          alert("Staff has been deleted successfully.");
      </script>
  <?php elseif ($status == 'error'): ?>
      <script>
          alert("An error occurred while deleting the staff.");
      </script>
  <?php endif; ?>

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
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin-staff.php' ? 'active' : ''; ?>">
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
        <h3 class="content-title">List of Staff</h3>
        <div class="content-tools">
          <a href="admin-create-staff.php" class="btn btn-flat btn-primary"><i class='bx bx-plus'></i> Create New</a>
        </div>
      </div>

      <!-- Staff List Container -->
      <div class="staff-list-container">
        <table class="staff-list-table">
          <thead>
            <tr>
              <th>No</th>
              <th>User ID</th>
              <th>Staff Name</th>
              <th>Gender</th>
              <th>Email</th>
              <th>Phone Number</th>
              <th>Date Registered</th>
              <th>Date Updated</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Assuming $staffList contains the data fetched from the user table where role is staff
            foreach ($staffList as $key => $staff):
                ?>
                <tr>
                  <td><?php echo $key + 1; ?></td>
                  <td><?php echo "US9527" . $staff['user_id']; ?></td>
                  <td><?php echo $staff['username']; ?></td>
                  <td><?php echo ucfirst($staff['gender']); ?></td>
                  <td><?php echo $staff['email']; ?></td>
                  <td><?php echo $staff['phone_number']; ?></td>
                  <td><?php echo date("d M Y H:i:s", strtotime($staff['date_created'])); ?></td>
                  <td><?php echo date("d M Y H:i:s", strtotime($staff['date_updated'])); ?></td>

                  <td>
                    <!-- Update button container -->
                    <div class="button-container">
                        <a href="admin-view-staff.php?staff_id=<?php echo $staff['user_id']; ?>" class="admin-update-staff-btn">Update</a>
                    </div>
                    
                    <!-- Delete button container -->
                    <div class="button-container">
                        <a href="#" class="admin-delete-staff-btn" onclick="confirmDelete('<?php echo $staff['username']; ?>', <?php echo $staff['user_id']; ?>)">Delete</a>
                    </div>
                    </td>
                </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
    </div>

    <script>
        //For close tab
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

        function confirmDelete(username, userId) {
            if (confirm("Are you sure you want to delete " + username + "?")) {
                // If user confirms, redirect to delete-staff.php with user ID as parameter
                window.location.href = "admin-delete-staff.php?userId=" + userId;
            }
        }

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
        });
    </script>
</body>
</html>