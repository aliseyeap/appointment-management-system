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

        // Retrieve counts for dashlets
        // Total Services Provided
        $stmt_service_provided = $pdo->query("SELECT COUNT(*) FROM service");
        $service_provided_count = $stmt_service_provided->fetchColumn();

        // Total Customers
        $stmt_total_customers = $pdo->query("SELECT COUNT(*) FROM user WHERE role = 'customer'");
        $total_customers_count = $stmt_total_customers->fetchColumn();

        // Total Appointments
        $stmt_total_appointments = $pdo->query("SELECT COUNT(*) FROM appointment");
        $total_appointments_count = $stmt_total_appointments->fetchColumn();

        // Completed Appointments
        $stmt_completed_appointments = $pdo->query("SELECT COUNT(*) FROM appointment WHERE appointment_status = 'Completed'");
        $completed_appointments_count = $stmt_completed_appointments->fetchColumn();

        // Pending Appointments
        $stmt_pending_appointments = $pdo->query("SELECT COUNT(*) FROM appointment WHERE appointment_status = 'Coming Soon'");
        $pending_appointments_count = $stmt_pending_appointments->fetchColumn();

        // Fetch events from the database
        $stmt_events = $pdo->query("SELECT appointment_id, customer_id, service_id, staff_id, appointment_date, appointment_time FROM appointment");
        $events = $stmt_events->fetchAll(PDO::FETCH_ASSOC);

        $calendar_events = []; // Initialize an empty array to store calendar events

        // Define colors for different staff
        $staff_colors = [];
        $available_colors = ['#EE4E4E', '#FEAE6F', '#F9E897', '#028391', '#B5C0D0', '#FF33A1', '#8E7AB5', '#EE99C2']; 
        $color_index = 0;

        foreach ($events as $event) {
            // Fetch customer name from the user table based on customer_id
            $stmt_customer = $pdo->prepare("SELECT username FROM user WHERE user_id = ? AND role = 'customer'");
            $stmt_customer->execute([$event['customer_id']]);
            $customer = $stmt_customer->fetch(PDO::FETCH_ASSOC);

            // Fetch staff name from the user table based on staff_id
            $stmt_staff = $pdo->prepare("SELECT username FROM user WHERE user_id = ? AND role = 'staff'");
            $stmt_staff->execute([$event['staff_id']]);
            $staff = $stmt_staff->fetch(PDO::FETCH_ASSOC);

            // Fetch service name from the service table based on service_id
            $stmt_service = $pdo->prepare("SELECT service_name FROM service WHERE service_id = ?");
            $stmt_service->execute([$event['service_id']]);
            $service = $stmt_service->fetch(PDO::FETCH_ASSOC);

            // Construct the title for the event
            $title = "Appointment with " . $customer['username'] . " for " . $service['service_name'] . " by " . $staff['username'];

            // Construct the start time of the event
            $start = date('Y-m-d H:i:s', strtotime($event['appointment_date'] . ' ' . $event['appointment_time']));

            // Construct the end time of the event (2 hours after the start time)
            $end_time = strtotime('+2 hours', strtotime($event['appointment_time']));
            $end = date('Y-m-d H:i:s', strtotime($event['appointment_date'] . ' ' . date('H:i:s', $end_time)));

            // Assign color to staff if not already assigned
            if (!isset($staff_colors[$event['staff_id']])) {
                $staff_colors[$event['staff_id']] = $available_colors[$color_index % count($available_colors)];
                $color_index++;
            }

            // Add the event object to the calendar events array
            $calendar_events[] = [
                'title' => $title,
                'start' => $start,
                'end' => $end,
                'color' => $staff_colors[$event['staff_id']] // Assigning color based on staff
            ];
        }
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
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Dashboard</title>
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
    <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php' ? 'active' : ''; ?>">
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
        <h3 class="content-title">Dashboard</h3>
      </div>
    
      <!-- Dashlet Container -->
    <div class="dashlet-container">

    <!-- Service Provided Dashlet -->
    <div class="dashlet">
      <h4>Service Provided</h4>
      <div class="circle-hollow">
        <p><?php echo $service_provided_count; ?></p>
      </div>
    </div>

    <!-- Total Customers Dashlet -->
    <div class="dashlet">
      <h4>Total of Customers</h4>
      <div class="circle-hollow">
        <p><?php echo $total_customers_count; ?></p>
      </div>
    </div>
    
    <!-- Total Appointments Dashlet -->
    <div class="dashlet">
      <h4>Total of Appointments</h4>
      <div class="circle-hollow">
        <p><?php echo $total_appointments_count; ?></p>
      </div>
    </div>

    <!-- Completed Appointments Dashlet -->
    <div class="dashlet">
      <h4>Completed Appointments</h4>
      <div class="circle-hollow">
        <p><?php echo $completed_appointments_count; ?></p>
      </div>
    </div>

    <!-- Pending Appointments Dashlet -->
    <div class="dashlet">
      <h4>Pending Appointments</h4>
      <div class="circle-hollow">
        <p><?php echo $pending_appointments_count; ?></p>
      </div>
    </div>
  </div>

  <div class="content-header">
      <h3 class="content-title">Schedule</h3>
  </div>

  <!-- Calendar Container -->
  <div id="calendar"></div>
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

    $(document).ready(function () {
        $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            events: <?php echo json_encode($calendar_events); ?>
        });
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
