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

// Check if customer ID is provided via GET parameter
if(isset($_GET['customer_id'])) {
    // Retrieve customer ID from GET parameter
    $customer_id = $_GET['customer_id'];

    // Prepare SQL query to retrieve customer information based on customer ID
    $stmt_customer = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
    $stmt_customer->execute([$customer_id]);
    $customer = $stmt_customer->fetch(PDO::FETCH_ASSOC);

    // Check if customer data exists
    if($customer) {
        // Assign customer data to variables for pre-filling form fields
        $customerName = $customer['username'];
        $customerGender = $customer['gender'];
        $customerEmail = $customer['email'];
        $customerPhoneNumber = $customer['phone_number'];
    } else {
        // Handle case where customer data does not exist
        // For example, redirect to a page with an error message
        header("Location: login.php");
        exit();
    }
} else {
    // Handle case where customer ID is not provided
    // For example, redirect to a page with an error message
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Customer Management</title>
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
        <li  class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin-customer.php' ? 'active' : ''; ?>">
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
        <h3 class="content-title">Update Customer</h3>
      </div>

    <!-- Customer Form Container -->
    <div class="customer-form-container">
      <form id="customerForm" class="customer-form" action="admin-update-customer.php" method="post">
        <div class="form-group">
            <label for="customerName">Customer Name</label>
            <input type="text" id="customerName" name="customerName" class="customer-input" placeholder="Please enter the name of the customer" value="<?php echo isset($customerName) ? $customerName : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="customerGender">Gender</label>
            <select id="customerGender" name="customerGender" class="customer-select" required>
                <option value="" disabled selected>Please select gender</option>
                <option value="male" <?php echo ($customerGender == 'male') ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo ($customerGender == 'female') ? 'selected' : ''; ?>>Female</option>
                <option value="rather not say" <?php echo ($customerGender == 'rather not say') ? 'selected' : ''; ?>>Rather Not Say</option>
            </select>
        </div>

        <div class="form-group">
            <label for="customerEmail">Email</label>
            <input type="email" id="customerEmail" name="customerEmail" class="customer-input" placeholder="Please enter the email of the customer" value="<?php echo isset($customerEmail) ? $customerEmail : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="customerPhoneNumber">Phone Number</label>
            <input type="text" id="customerPhoneNumber" name="customerPhoneNumber" class="customer-input" placeholder="Please enter the phone number of the customer" value="<?php echo isset($customerPhoneNumber) ? $customerPhoneNumber : ''; ?>" required>
        </div>

        <div class="admin-new-app-btn-container" style="width: calc(100%); ">
            <!-- Form submission button -->
            <button type="submit" class="admin-new-app-btn-submit btn btn-flat btn-primary"><i class='bx bx-check'></i> Update</button>
            <!-- Cancel button -->
            <a href="admin-customer.php" class="admin-new-app-btn-cancel btn btn-flat btn-danger">Cancel</a>
        </div>

        <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>">

      </form>
  </div>
</div>
</section>

<script>
  //For close tab
  let arrow = document.querySelectorAll(".arrow");
  for (var i = 0; i < arrow.length; i++) {
    arrow[i].addEventListener("click", (e) => {
        let arrowParent = e.target.parentElement.parentElement; 
        arrowParent.classList.toggle("showMenu");
    });
  }
  let sidebar = document.querySelector(".sidebar");
  let sidebarBtn = document.querySelector(".bx-menu");
  console.log(sidebarBtn);
  sidebarBtn.addEventListener("click", () => {
      sidebar.classList.toggle("close");
  });

  // Handle form submission
  document.querySelector(".customer-form-btn-submit").addEventListener("click", function(event) {
      event.preventDefault(); 

      var form = document.getElementById("customerForm");
      form.action = "admin-customer.php"; 
      form.method = "post";
      form.submit();

      alert("Customer added successfully!");
      window.location.href = "admin-customer.php"; 
  });

  // Handle cancel button click
  document.querySelector(".customer-form-btn-cancel").addEventListener("click", function() {
      // Redirect to admin-customer.php
      window.location.href = "admin-customer.php";
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
        });
</script>
</body>
</html>