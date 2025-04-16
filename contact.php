<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php'; 

include 'session-termination.php';

// Check if user is logged in
if(isset($_SESSION['user_id'])) {
    // Get user ID from session
    $userId = $_SESSION['user_id'];

    // Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $messageText = isset($_POST['message']) ? $_POST['message'] : '';
    $sendDatetime = date('Y-m-d H:i:s');

    // Set the default message status
    $messageStatus = 'New';

    // Insert message into database
    $sql = "INSERT INTO message (user_id, name, email, message_text, send_datetime, message_status) 
            VALUES (:userId, :name, :email, :messageText, :sendDatetime, :messageStatus)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['userId' => $userId, 'name' => $name, 'email' => $email, 'messageText' => $messageText, 'sendDatetime' => $sendDatetime, 'messageStatus' => $messageStatus]);
    }
} else {
    // Redirect user to login page if not logged in
    header("Location: login.php");
    exit();
}
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

    <!-- Get In Touch Container -->
    <div class="get-in-touch-container">
        <img src="images/contact-us.gif" alt="Contact Us">    
        <h2 class="text-center">Get In Touch</h2>
    </div> 

    <!-- Contact Form-->
    <div class="contact-form-container">
        <div class="contact-form-image">
            <img src="images/message-us.png" alt="Message Us">
            <p>We love hearing from you! Drop us a message and let's talk beauty.</p>    
        </div>
        <div class="contact-form">
            <h2>Send Us a Message</h2>
            <form id="messageForm" action="contact.php" method="POST">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                <label for="message">Message:</label>
                <textarea id="message" name="message" rows="4" cols="50" required></textarea>
                <button type="submit" onclick="sendMessage()">Send</button>
            </form>
        </div>
    </div>

    <div class="contact-container">
        <!-- Google Maps -->
        <div class="google-maps-container">
            <div class="google-maps">
                <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15866.929871984195!2d100.5113569!3d6.1665699!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x304b51fde990d927%3A0xcd44b9433dfcbbcc!2sElvira%20true%20beauty%20salon!5e0!3m2!1sen!2smy!4v1709837529496!5m2!1sen!2smy" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="contact-info">
            <h2>Contact Us</h2>
            <p><i class="fas fa-map-marker-alt"></i> A13, Tingkat 1, Taman Bandar Baru II, 06400 Pokok Sena, Kedah</p>
            <p><i class="fas fa-phone"></i> (+60) 017-584 2889</p>
            <p><i class="far fa-envelope"></i> elviratruebeauty@gmail.com</p>

            <!-- Operating Hours -->
            <h2>Operating Hours</h2>
            <p>Sunday - Tuesday: 11:00 AM - 6:00 PM</p>
            <p>Wednesday: Closed</p>
            <p>Thursday - Saturday: 11:00 AM - 6:00 PM</p>

            <!-- Follow Us -->
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

        function sendMessage() {
            // Display the alert message
            alert('Thanks for your message! We\'ll get back to you soon.');
        }
    </script>

</body>
</html>