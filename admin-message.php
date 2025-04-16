<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php'; 
include 'session-termination.php';

// Check if success message is set in session
if(isset($_SESSION['success'])) {
  // Display success message
  echo "<script>alert('" . $_SESSION['success'] . "');</script>";

  // Unset success message to avoid displaying it again on page reload
  unset($_SESSION['success']);
}

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

// Initialize $statusFilter variable
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Initialize $statusCondition variable
$statusCondition = "";

// Fetch messages from the database based on the selected status
switch ($statusFilter) {
    case 'all':
        $statusCondition = "WHERE message_status = 'New' OR message_status = 'Replied'";
        break;
    case 'new':
        $statusCondition = "WHERE message_status = 'New'";
        break;
    case 'replied':
        $statusCondition = "WHERE message_status = 'Replied'";
        break;
    default:
        $statusCondition = "";
        break;
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
            <h3 class="content-title">List of Messages</h3>
        </div>

        <!-- Message Logs -->
        <div class="message-logs-container">

          <!-- Message Logs Filter -->
          <div class="message-logs-filter">
              <form id="status-filter-form" method="GET">
                  <label for="status-filter">Filter by Status:</label>
                  <select id="status-filter" name="status">
                      <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All</option>
                      <option value="new" <?php echo $statusFilter === 'new' ? 'selected' : ''; ?>>New</option>
                      <option value="replied" <?php echo $statusFilter === 'replied' ? 'selected' : ''; ?>>Replied</option>
                  </select>
              </form>
          </div>

            <!-- List of Message Logs -->
            <table class="message-logs-table">
            <thead>
              <tr>
                  <th style="width: 50px;">No</th>
                  <th style="width: 150px;">Message ID</th>
                  <th style="width: 200px;">Sender Name</th>
                  <th style="width: 150px;">Sent Datetime</th>
                  <th style="width: 100px;">Message Status</th>
                  <th style="width: 150px;">Replied Date</th>
                  <th style="width: 100px;">Action</th>
              </tr>
            </thead>
              <tbody>
                <?php
                  // Initialize counter variable
                  $counter = 1;

                  // Fetch messages from the database based on the selected role
                  $stmt_messages = $pdo->prepare("SELECT * FROM message $statusCondition ORDER BY send_datetime DESC");
                  $stmt_messages->execute(); // Execute the prepared statement

                  // Iterate over the fetched messages and display them in table rows
                  while ($message = $stmt_messages->fetch(PDO::FETCH_ASSOC)) {
                      // Format message ID as MSG202400(message_id)
                      $message_id = "MSG202400" . $message['message_id'];

                      // Format datetime fields as desired format
                      $sent_datetime = date("d M Y H:i:s", strtotime($message['send_datetime']));
                      $replied_date = $message['reply_datetime'] ? date("d M Y H:i:s", strtotime($message['reply_datetime'])) : "-";

                      echo "<tr>";
                      echo "<td>{$counter}</td>"; // Display the counter value in the "No" column
                      echo "<td>{$message_id}</td>";
                      echo "<td>{$message['name']}</td>";
                      echo "<td>{$sent_datetime}</td>";

                      if ($message['message_status'] == 'New') {
                          echo "<td><div class='status new'>{$message['message_status']}</div></td>";
                      } elseif ($message['message_status'] == 'Replied') {
                          echo "<td><div class='status replied'>{$message['message_status']}</div></td>";
                      } else {
                          echo "<td><div class='status'>{$message['message_status']}</div></td>";
                      }

                      echo "<td>{$replied_date}</td>";
                      echo "<td><a class='message-details-btn' href='admin-view-message-details.php?message_id={$message['message_id']}' data-message-id='{$message['message_id']}'>Details</a></td>";
                      echo "</tr>";

                      // Increment the counter for the next row
                      $counter++;
                  }
                  ?>
              </tbody>
            </table>
        </div>
    </div>
    </section>
  

    <script>
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

        //Filter by Role
        document.getElementById("status-filter").addEventListener("change", function() {
            document.getElementById("status-filter-form").submit();
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
        
    </script>
</body>
</html>

