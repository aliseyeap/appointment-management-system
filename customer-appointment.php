<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php';

include 'session-termination.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

// Fetch appointments for the logged-in user with staff and service details
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT a.*, u.username AS staff_name, s.service_name 
                       FROM appointment a
                       LEFT JOIN user u ON a.staff_id = u.user_id
                       LEFT JOIN service s ON a.service_id = s.service_id
                       WHERE a.customer_id = ?
                       ORDER BY a.appointment_date, a.appointment_time");
$stmt->execute([$user_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Contact Us</title>
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico"/>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <script src="js/javascript.js"></script>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="logo">
            <h1>Elvira True Beauty</h1>
        </div>
        <ul class="nav">
            <li><a href="customer_homepage.php">Home</a></li>
            <li><a href="service.php">Services</a></li>
            <li><a href="about.php">About Us</a></li>
            <li><a href="contact.php">Contact Us</a></li>
        </ul>
        <a href="customer-create-appointment.php" class="appointment-btn" onclick="toggleAppointmentForm()">Make an Appointment</a>
        
        <div class="cta-buttons">
            <div class="my-dropdown" id="profileDropdown" onmouseover="showDropdown()" onmouseout="hideDropdown()">
                <button class="my-account-btn">My Account</button>
                <div class="my-dropdown-content">
                    <a href="customer-appointment.php" class="my-appointment-btn">My Appointment</a>
                    <a href="view-profile.php">View Profile</a>
                    <a href="change-pass.php">Change Password</a>
                    <a href="#" onclick="logout()">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- My Appointment Table -->
    <section class="my-appointment-table">
        <h2>My Appointments</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Date Created</th>
                    <th>Appointment Date</th>
                    <th>Appointment Time</th>
                    <th>Service</th>
                    <th>Staff</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($appointments as $key => $appointment): ?>
                <?php
                // Check if the appointment is within 24 hours
                $appointment_datetime = strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
                $current_datetime = time();
                $time_diff = $appointment_datetime - $current_datetime;
                $within_24_hours = $time_diff <= 86400;
                ?>
                <tr id="appointment_<?php echo $appointment['appointment_id']; ?>">
                    <td><?php echo $key + 1; ?></td>
                    <td><?php echo date('d F Y H:i:s', strtotime($appointment['date_created'])); ?></td>
                    <td><?php echo date('d F Y', strtotime($appointment['appointment_date'])); ?></td>
                    <td class="appointment-time"><?php echo $appointment['appointment_time']; ?></td>
                    <td><?php echo $appointment['service_name']; ?></td>
                    <td><?php echo $appointment['staff_name']; ?></td>
                    <td id="status_<?php echo $appointment['appointment_id']; ?>"><?php echo $appointment['appointment_status']; ?></td>
                    <td>
                        <?php if ($appointment['appointment_status'] === 'Coming Soon'): ?>
                            <button class="reschedule-btn <?php if ($within_24_hours) echo 'disabled-reschedule-btn'; ?>" 
                                    onclick="rescheduleAppointment(<?php echo $appointment['appointment_id']; ?>)" 
                                    <?php if ($within_24_hours) echo 'disabled'; ?>>
                                Reschedule
                            </button>
                            <button class="cancel-btn <?php if ($within_24_hours) echo 'disabled-cancel-btn'; ?>" 
                                    onclick="cancelAppointment(<?php echo $appointment['appointment_id']; ?>)" 
                                    <?php if ($within_24_hours) echo 'disabled'; ?>>
                                Cancel
                            </button>
                        <?php elseif ($appointment['appointment_status'] === 'Completed'): ?>
                            <button class="disabled-btn" disabled>Completed</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </section>

    <script>
        // Function to convert time from 24-hour format to 12-hour AM/PM format
        function convertTo12HourFormat(time24) {
            var hour = parseInt(time24.substring(0, 2));
            var minute = time24.substring(3, 5);
            var meridiem = hour >= 12 ? 'pm' : 'am'; // Determine AM/PM
            hour = hour % 12 || 12; // Convert hour to 12-hour format
            return hour + ':' + minute + ' ' + meridiem;
        }

        // Iterate through appointment time cells and convert time format
        document.addEventListener('DOMContentLoaded', function() {
            var timeCells = document.querySelectorAll('.appointment-time');
            timeCells.forEach(function(cell) {
                var time24 = cell.textContent.trim();
                cell.textContent = convertTo12HourFormat(time24);
            });
        });

        function cancelAppointment(appointmentId) {
        if (confirm('Are you sure you want to cancel this appointment?')) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'customer-cancel-appointment.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        var row = document.getElementById('appointment_' + appointmentId);
                        row.parentNode.removeChild(row);
                        alert('Appointment successfully canceled.');
                    } else {
                        alert('Failed to cancel the appointment: ' + response.message);
                    }
                }
            };
            xhr.send('appointment_id=' + appointmentId);
        }
    }

    function rescheduleAppointment(appointmentId) {
        window.location.href = 'customer-reschedule-form.php?appointment_id=' + appointmentId;
    }

    function showDropdown() {
        document.getElementById("profileDropdown").classList.add("show");
    }

    function hideDropdown() {
        document.getElementById("profileDropdown").classList.remove("show");
    }

    function logout() {
        var confirmLogout = confirm("Are you sure you want to logout?");
        if (confirmLogout) {
            window.location.href = "logout.php";
        }
    }
    </script>

</body>
</html>
