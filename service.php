<?php

include 'session-termination.php';
// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page
    header("Location: login.php");
    exit; // Stop further execution
}

// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php'; 

// Fetch services from the database
$stmt = $pdo->query("SELECT * FROM service"); // Adjust the query as needed
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <div class="container">

    <!-- Service Container -->
    <div class="service-provided-container">
        <img src="images/background.jpg" alt="Service">    
        <h2 class="text-center">Service We Provided</h2>
    </div> 

    <div class="space"></div>

    <div class="row">
        <?php 
        $index = 0; // Initialize index counter
        foreach ($services as $service):
            $index++; // Increment index counter
            $flexDirection = $index % 2 === 0 ? 'row-reverse' : 'row'; // Alternate flex direction
            $backgroundColors = array('#E6A4B4, #F3D7CA, #FFF8E3', '#FFF8E3, #F3D7CA, #E6A4B4');
            $background = $backgroundColors[$index % 2];

            // Calculate duration in hours and minutes
            $durationHours = floor($service['service_duration']);
            $durationMinutes = ($service['service_duration'] - $durationHours) * 60;
        ?>
            <div class="col-md-6">
                <div class="service-container" style="background-image: linear-gradient(270deg, <?php echo $background; ?>)">
                    <div class="service-content" style="flex-direction: <?php echo $flexDirection; ?>">
                        <div class="service-text">
                            <h3><?php echo $service['service_name']; ?></h3>
                            <p style="text-align: center;"><?php echo $service['service_description']; ?></p>
                            <h2 style="text-align: center;">Duration: <?php echo $durationHours . ' hours ' . $durationMinutes . ' minutes'; ?></h2>
                            <h2 style="text-align: center;">Price: RM<?php echo $service['service_price']; ?> per pax</h2>
                            <div style="text-align: center;">
                                <a href="customer-create-appointment.php" class="book-now-btn">Book Now</a>
                            </div>
                        </div>
                        <div class="service-image">
                            <img src="<?php echo $service['service_image']; ?>" alt="<?php echo $service['service_name']; ?>">
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    </div>

    <div class="footer-bottom" style="background-color: #FFE4E1; color:darkblue;">
        &copy; 2023 Elvira True Beauty | Alise Yeap Rou Xin (AI210338)
    </div>

    <script>
        function showDropdown() {
                document.getElementById("profileDropdown").classList.add("show");
            }

        function hideDropdown() {
            document.getElementById("profileDropdown").classList.remove("show");
        }
        

        function logout() {
            var confirmLogout = confirm("Are you sure you want to logout?");
            if (confirmLogout) {
                // Redirect to logout.php after confirmation
                window.location.href = "logout.php";
            }
        }
    </script>

</body>
</html>
