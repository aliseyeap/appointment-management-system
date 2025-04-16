<?php

include 'session-termination.php';

// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page
    header("Location: login.php");
    exit; 
}

// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php'; 

// Function to check if it's the user's first login
function isFirstLogin($userId, $pdo) {
    $sql = "SELECT * FROM login_logs WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->rowCount() == 1; // If there's only one row, it's the first login
}

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    if (isFirstLogin($userId, $pdo)) {
        // Redirect the user to view their profile
        header("Location: register-profile.php");
        exit();
    }
}

// Fetch services from the database
$stmt = $pdo->query("SELECT * FROM service LIMIT 6"); // Adjust the query as needed
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Welcome To Customer Homepage</title>
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

    <!-- Slide -->
    <script>
        // Call the showSlides function when the page loads
        document.addEventListener("DOMContentLoaded", function() {
            showSlides(slideIndex);
        });
    </script>

    <div class="slideshow-container">
        <div class="slides">
            <img src="images/slide1.jpg" alt="Slide 1">
            <img src="images/slide2.jpg" alt="Slide 2">
            <img src="images/slide3.jpg" alt="Slide 3">
            <img src="images/slide4.jpg" alt="Slide 4">
        </div>
        
        <!-- Navigation arrows -->
        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
        <a class="next" onclick="plusSlides(1)">&#10095;</a>
    </div>
    
    <!-- Dots to represent slides -->
    <div style="text-align:center">
        <span class="dot" onclick="currentSlide(1)"></span>
        <span class="dot" onclick="currentSlide(2)"></span>
        <span class="dot" onclick="currentSlide(3)"></span>
        <span class="dot" onclick="currentSlide(4)"></span>
    </div>

    <!-- Service -->
    <section class="service-provide">
        <h2>Services We Provide</h2>
        <div class="service-boxes">
            <?php foreach ($services as $service): ?>
                <div class="service-box">
                    <img src="<?php echo $service['service_image']; ?>" alt="<?php echo $service['service_name']; ?>">
                    <h3><?php echo $service['service_name']; ?></h3>
                    <p><?php echo $service['service_description']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="more-services-btn-container">
            <a href="service.php" class="more-services-btn">More Services</a>
        </div>
    </section>

    <div class="space"></div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section about">
                <h2>About Us</h2>
                <p>Welcome to Elvira True Beauty Salon, where we are dedicated to enhancing your natural beauty and promoting overall well-being. Our salon offers a range of luxurious services tailored to pamper and rejuvenate you from head to toe.</p>
            </div>
            <div class="footer-section contact">
                <h2>Contact Us</h2>
                <br><p>Email: elviratruebeauty@gmail.com<br><br>Phone: (+60) 017-584 2889</p>
            </div>
            <div class="footer-section social">
                <h2>Follow Us</h2>
                <div class="social-buttons">
                    <a href="https://www.facebook.com/profile.php?id=100063520605070" class="social-buttons__button social-button social-button--facebook" aria-label="Facebook">
                      <span class="social-button__inner">
                        <i class="fab fa-facebook-f"></i>
                      </span>
                    </a>
                    <a href="http://www.wasap.my/60175842889" class="social-buttons__button social-button social-button--whatsapp" aria-label="Whatsapp">
                      <span class="social-button__inner">
                        <i class="fab fa-whatsapp"></i>
                      </span>
                    </a>
                    <a href="https://www.instagram.com/elvira_true_beauty/" target="_blank" class="social-buttons__button social-button social-button--instagram" aria-label="InstaGram">
                      <span class="social-button__inner">
                        <i class="fab fa-instagram"></i>
                      </span>
                    </a>
                  </div>
            </div>
        </div>
    </footer>
    <div class="space"></div>
    <div class="footer-bottom">
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
