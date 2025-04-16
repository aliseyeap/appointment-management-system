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

// Check if message ID is provided in the URL
if(isset($_GET['message_id'])) {
    // Retrieve message ID from the URL
    $message_id = $_GET['message_id'];

    // Fetch message details from the database based on the provided message ID
    $stmt_message = $pdo->prepare("SELECT * FROM message WHERE message_id = ?");
    $stmt_message->execute([$message_id]);
    $message = $stmt_message->fetch(PDO::FETCH_ASSOC);

    // Check if message exists
    if(!$message) {
        // Handle case when message does not exist
        echo "Message not found!";
        exit(); // Stop script execution
    }
} else {
    // Handle case when message ID is not provided in the URL
    echo "Message ID not provided!";
    exit(); // Stop script execution
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Message Management</title>
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico"/>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
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
      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin-message.php' ? 'active' : ''; ?>">
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
            <h3 class="content-title">Details of Message</h3>
        </div>

        <!-- Message Details -->
        <div class="message-details-container">
            <form action="admin-reply-message.php" method="post">
                <div class="message-details-row">
                    <label for="sender_name">Sender's Name:</label>
                    <input type="text" id="sender_name" name="sender_name" value="<?php echo $message['name']; ?>" readonly>
                </div>
                <div class="message-details-row">
                    <label for="sender_email">Sender's Email:</label>
                    <input type="email" id="sender_email" name="sender_email" value="<?php echo $message['email']; ?>" readonly>
                </div>
                <div class="message-details-row">
                    <label for="message_text">Message Text:</label>
                    <textarea id="message_text" name="message_text" rows="4" readonly><?php echo $message['message_text']; ?></textarea>
                </div>
                <div class="message-details-row">
                    <label for="reply_text">Reply Text:</label>
                    <textarea id="reply_text" name="reply_text" rows="4" required><?php echo isset($message['reply_text']) ? $message['reply_text'] : ''; ?></textarea>
                </div>
                <div class="message-details-row">
                    <div class="message-btn-container">
                        <button class="message-reply-btn btn-flat btn-primary"><i class='bx bx-check'></i> Reply</button>
                        <a href="admin-message.php" class="message-cancel-btn btn btn-flat btn-danger">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </section>
  
    <script>
        // For close tab
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

