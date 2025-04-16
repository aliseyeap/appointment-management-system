<?php
require('vendor/autoload.php');
require('fpdf/fpdf.php');

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

// Function to fetch staff availability data
function fetchStaffAvailabilityData() {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            sa.availability_id,
            u.username AS staff,
            s.service_name,
            sa.available_start_date,
            sa.available_end_date,
            sa.date_created,
            sa.date_updated
        FROM 
            staff_availability sa
        JOIN 
            user u ON sa.staff_id = u.user_id
        JOIN 
            service s ON sa.service_id = s.service_id
        ORDER BY
            sa.available_start_date,
            u.username
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch customer data
function fetchCustomerData() {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            username,
            email,
            gender,
            phone_number,
            date_created,
            date_updated
        FROM 
            user 
        WHERE 
            role = 'customer' 
        ORDER BY 
            username ASC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch appointment data
function fetchAppointmentData() {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            appointment_id,
            u1.username AS customer,
            s.service_name AS service,
            u2.username AS staff,
            appointment_date,
            appointment_time,
            a.date_created,
            a.date_updated
        FROM 
            appointment a
        INNER JOIN 
            user u1 ON a.customer_id = u1.user_id
        INNER JOIN 
            service s ON a.service_id = s.service_id
        INNER JOIN 
            user u2 ON a.staff_id = u2.user_id
        ORDER BY
            appointment_date,
            appointment_time,
            u1.username
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch user login logs data
function fetchLoginLogsData() {
  global $pdo;
  $stmt = $pdo->prepare("
      SELECT 
          ll.log_id,
          u.username AS user,
          u.role,
          ll.login_time
      FROM 
          login_logs ll
      INNER JOIN 
          user u ON ll.user_id = u.user_id
      ORDER BY
          ll.login_time
  ");
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Function to generate Staff Availability PDF report using FPDF
function generateStaffAvailabilityPDF() {
  // Fetch data from the database
  $staffAvailabilityData = fetchStaffAvailabilityData();

  // Create a new FPDF instance
  $pdf = new \FPDF('L');

  // Add a new page
  $pdf->AddPage();

  // Set font
  $pdf->SetFont('Arial', '', 24);

  // Title
  $pdf->Cell(0, 10, 'Staff Availability Report', 0, 1, 'C');

  // Line break
  $pdf->Ln(10);

  // Column headings
  $pdf->SetFont('Arial', 'B', 12);
  $pdf->Cell(60, 10, 'Staff', 1, 0, 'C');
  $pdf->Cell(55, 10, 'Service', 1, 0, 'C');
  $pdf->Cell(35, 10, 'Start Date', 1, 0, 'C');
  $pdf->Cell(35, 10, 'End Date', 1, 0, 'C');
  $pdf->Cell(45, 10, 'Created', 1, 0, 'C');
  $pdf->Cell(45, 10, 'Updated', 1, 0, 'C');
  $pdf->Ln();

  // Data
  $pdf->SetFont('Arial', '', 12);
  foreach ($staffAvailabilityData as $row) {
      $pdf->Cell(60, 10, $row['staff'], 1, 0, 'C');
      $pdf->Cell(55, 10, $row['service_name'], 1, 0, 'C');
      $pdf->Cell(35, 10, $row['available_start_date'], 1, 0, 'C');
      $pdf->Cell(35, 10, $row['available_end_date'], 1, 0, 'C');
      $pdf->Cell(45, 10, $row['date_created'], 1, 0, 'C');
      $pdf->Cell(45, 10, $row['date_updated'], 1, 0, 'C');
      $pdf->Ln();
  }

  // Output the PDF
  $pdf->Output('D', 'Staff_Availability_Report.pdf'); // Download the PDF
}

// Function to generate List of Customers PDF report using FPDF
function generateCustomerPDF() {
  // Fetch data from the database
  $customerData = fetchCustomerData();

  // Create a new FPDF instance
  $pdf = new \FPDF('L');

  // Add a new page
  $pdf->AddPage();

  // Set font
  $pdf->SetFont('Arial', '', 24);

  // Title
  $pdf->Cell(0, 10, 'List of Customers', 0, 1, 'C');

  // Line break
  $pdf->Ln(10);

  // Column headings
  $pdf->SetFont('Arial', 'B', 12);
  $pdf->Cell(60, 10, 'Name', 1, 0, 'C');
  $pdf->Cell(60, 10, 'Email', 1, 0, 'C');
  $pdf->Cell(30, 10, 'Gender', 1, 0, 'C');
  $pdf->Cell(35, 10, 'Phone Number', 1, 0, 'C');
  $pdf->Cell(45, 10, 'Date Created', 1, 0, 'C');
  $pdf->Cell(45, 10, 'Date Updated', 1, 0, 'C');
  $pdf->Ln();

  // Data
  $pdf->SetFont('Arial', '', 12);
  foreach ($customerData as $row) {
      $pdf->Cell(60, 10, $row['username'], 1, 0, 'C');
      $pdf->Cell(60, 10, $row['email'], 1, 0, 'C');
      $pdf->Cell(30, 10, $row['gender'], 1, 0, 'C');
      $pdf->Cell(35, 10, $row['phone_number'], 1, 0, 'C');
      $pdf->Cell(45, 10, $row['date_created'], 1, 0, 'C');
      $pdf->Cell(45, 10, $row['date_updated'], 1, 0, 'C');
      $pdf->Ln();
  }

  // Output the PDF
  $pdf->Output('D', 'Customer_List.pdf'); // Download the PDF
}

// Function to generate List of Appointments PDF report using FPDF
function generateAppointmentPDF() {
  // Fetch data from the database
  $appointmentData = fetchAppointmentData();

  // Create a new FPDF instance
  $pdf = new \FPDF('L');

  // Add a new page
  $pdf->AddPage();

  // Set font
  $pdf->SetFont('Arial', '', 24);

  // Title
  $pdf->Cell(0, 10, 'List of Appointments', 0, 1, 'C');

  // Line break
  $pdf->Ln(10);

  // Column headings
  $pdf->SetFont('Arial', 'B', 12);
  $pdf->Cell(40, 10, 'Customer', 1, 0, 'C');
  $pdf->Cell(50, 10, 'Service', 1, 0, 'C');
  $pdf->Cell(40, 10, 'Staff', 1, 0, 'C');
  $pdf->Cell(30, 10, 'Date', 1, 0, 'C');
  $pdf->Cell(25, 10, 'Time', 1, 0, 'C');
  $pdf->Cell(45, 10, 'Created', 1, 0, 'C');
  $pdf->Cell(45, 10, 'Updated', 1, 0, 'C');
  $pdf->Ln();

  // Data
  $pdf->SetFont('Arial', '', 12);
  foreach ($appointmentData as $row) {
      $pdf->Cell(40, 10, $row['customer'], 1, 0, 'C');
      $pdf->Cell(50, 10, $row['service'], 1, 0, 'C');
      $pdf->Cell(40, 10, $row['staff'], 1, 0, 'C');
      $pdf->Cell(30, 10, $row['appointment_date'], 1, 0, 'C');
      $pdf->Cell(25, 10, $row['appointment_time'], 1, 0, 'C');
      $pdf->Cell(45, 10, $row['date_created'], 1, 0, 'C');
      $pdf->Cell(45, 10, $row['date_updated'], 1, 0, 'C');
      $pdf->Ln();
  }

  // Output the PDF
  $pdf->Output('D', 'Appointment_List.pdf'); // Download the PDF
}

// Function to generate Login Logs PDF report using FPDF
function generateLoginLogsPDF() {
  // Fetch data from the database
  $loginLogsData = fetchLoginLogsData();

  // Create a new FPDF instance
  $pdf = new \FPDF('L');

  // Add a new page
  $pdf->AddPage();

  // Set font
  $pdf->SetFont('Arial', '', 24);

  // Title
  $pdf->Cell(0, 10, 'User Login Logs', 0, 1, 'C');

  // Line break
  $pdf->Ln(10);

  // Column headings
  $pdf->SetFont('Arial', 'B', 12);
  $pdf->Cell(90, 10, 'User', 1, 0, 'C');
  $pdf->Cell(60, 10, 'Role', 1, 0, 'C');
  $pdf->Cell(120, 10, 'Login Time', 1, 0, 'C');
  $pdf->Ln();

  // Data
  $pdf->SetFont('Arial', '', 12);
  foreach ($loginLogsData as $row) {
      $pdf->Cell(90, 10, $row['user'], 1, 0, 'C');
      $pdf->Cell(60, 10, $row['role'], 1, 0, 'C');
      $pdf->Cell(120, 10, $row['login_time'], 1, 0, 'C');
      $pdf->Ln();
  }

  // Output the PDF
  $pdf->Output('D', 'User_Login_Logs.pdf'); // Download the PDF
}

// Function to generate Staff Availability Excel report
function generateStaffAvailabilityExcel() {
  // Fetch data from the database
  $staffAvailabilityData = fetchStaffAvailabilityData();

  // Set headers for Excel file download
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment;filename="Staff_Availability_Report.xls"');
  header('Cache-Control: max-age=0');

  // Start Excel file
  echo "Staff\tService\tStart Date\tEnd Date\tDate Created\tDate Updated\n";

  // Output data
  foreach ($staffAvailabilityData as $row) {
      echo "{$row['staff']}\t{$row['service_name']}\t{$row['available_start_date']}\t{$row['available_end_date']}\t{$row['date_created']}\t{$row['date_updated']}\n";
  }

  // End Excel file
  exit();
}

// Function to generate List of Customers Excel report
function generateCustomerExcel() {
  // Fetch data from the database
  $customerData = fetchCustomerData();

  // Set headers for Excel file download
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment;filename="Customer_List.xls"');
  header('Cache-Control: max-age=0');

  // Start Excel file
  echo "Name\tEmail\tGender\tPhone Number\tDate Created\tDate Updated\n";

  // Output data
  foreach ($customerData as $row) {
      echo "{$row['username']}\t{$row['email']}\t{$row['gender']}\t{$row['phone_number']}\t{$row['date_created']}\t{$row['date_updated']}\n";
  }

  // End Excel file
  exit();
}

// Function to generate List of Appointments Excel report
function generateAppointmentExcel() {
  // Fetch data from the database
  $appointmentData = fetchAppointmentData();

  // Set headers for Excel file download
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment;filename="Appointment_List.xls"');
  header('Cache-Control: max-age=0');

  // Start Excel file
  echo "Customer\tService\tStaff\tDate\tTime\tDate Created\tDate Updated\n";

  // Output data
  foreach ($appointmentData as $row) {
      echo "{$row['customer']}\t{$row['service']}\t{$row['staff']}\t{$row['appointment_date']}\t{$row['appointment_time']}\t{$row['date_created']}\t{$row['date_updated']}\n";
  }

  // End Excel file
  exit();
}

// Function to generate Login Logs Excel report
function generateLoginLogsExcel() {
  // Fetch data from the database
  $loginLogsData = fetchLoginLogsData();

  // Set headers for Excel file download
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment;filename="User_Login_Logs.xls"');
  header('Cache-Control: max-age=0');

  // Start Excel file
  echo "User\tRole\tLogin Time\n";

  // Output data
  foreach ($loginLogsData as $row) {
      echo "{$row['user']}\t{$row['role']}\t{$row['login_time']}\n";
  }

  // End Excel file
  exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reportType = $_POST['report_type'];

    switch ($reportType) {
        case 'staff_availability_pdf':
            generateStaffAvailabilityPDF();
            break;
        case 'staff_availability_excel':
            generateStaffAvailabilityExcel();
            break;
        case 'customer_pdf':
            generateCustomerPDF();
            break;
        case 'customer_excel':
            generateCustomerExcel();
            break;
        case 'appointment_pdf':
            generateAppointmentPDF();
            break;
        case 'appointment_excel':
            generateAppointmentExcel();
            break;
        case 'login_logs_pdf':
            generateLoginLogsPDF();
            break;
        case 'login_logs_excel':
            generateLoginLogsExcel();
            break;
    }
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>

    <script>
        function generateReport(reportType) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'report_type';
            input.value = reportType;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    </script>

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
      <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin-report.php' ? 'active' : ''; ?>">
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
        <h3 class="content-title">Report Generation</h3>
      </div>

      <section class="report-section">
        <div class="report-box" id="staffReport">
          <h4>Staff Availability Report</h4>
          <div class="report-buttons">
                <button class="pdf-btn" onclick="generateReport('staff_availability_pdf')">Generate PDF</button>
                <button class="excel-btn" onclick="generateReport('staff_availability_excel')">Generate Excel</button>
          </div>
        </div>

        <div class="report-box" id="customerReport">
          <h4>List of Customer</h4>
          <div class="report-buttons">
                <button class="pdf-btn" onclick="generateReport('customer_pdf')">Generate PDF</button>
                <button class="excel-btn" onclick="generateReport('customer_excel')">Generate Excel</button>
          </div>
        </div>

        <div class="report-box" id="appointmentReport">
          <h4>List of Appointment</h4>
          <div class="report-buttons">
                <button class="pdf-btn" onclick="generateReport('appointment_pdf')">Generate PDF</button>
                <button class="excel-btn" onclick="generateReport('appointment_excel')">Generate Excel</button>
          </div>
        </div>

        <div class="report-box" id="loginReport">
          <h4>User Login Logs</h4>
          <div class="report-buttons">
                <button class="pdf-btn" onclick="generateReport('login_logs_pdf')">Generate PDF</button>
                <button class="excel-btn" onclick="generateReport('login_logs_excel')">Generate Excel</button>
          </div>
        </div>
      </section>
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
