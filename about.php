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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | About Us</title>
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
    <div class="space"></div>

    <!--About Us-->
    <div class="row">
      <div class="col-md-6">
        <div class="about-container" style="background-image: linear-gradient(180deg, #F8E8EE, #F2BED1)">
            <div class="content">
                <div class="text">
                <h2>About Us</h2>
                    <p style="text-align: center;">Unveiling a sanctuary dedicated solely to enhancing your natural beauty, Elvira True Beauty Salon offers a curated selection of specialized services. Dive into deep relaxation with a customized massage, expertly tailored to your needs. Our talented makeup artists will transform your features, accentuating your unique beauty. For a radiant and revitalized complexion, explore our rejuvenating skin treatments and facial spa experiences. Unlike salons offering a broader spectrum of services, Elvira True Beauty Salon focuses on these core areas, allowing our team to become specialists. This dedication ensures you receive the most personalized and exceptional experience possible, leaving you feeling confident and ready to embrace the world with renewed radiance.</p>
                </div>
                <div class="image">
                    <img src="images/beauty.jpg" alt="Image">
                </div>
            </div>
        </div>
      </div>
    </div>

    <!--Vision and Mission-->
    <div class="row">
        <div class="col-md-6">
            <div class="about-container" style="background-image: linear-gradient(180deg, #F2BED1, #D0A2F7)">
                <h2>Vision and Mission</h2>
                <div class="vision-mission-content">
                    <div class="vision">
                        <div class="circle"><i class="fas fa-eye"></i></div>
                        <h3>Vision</h3>
                        <p>To be the premier sanctuary for holistic beauty, fostering inner and outer well-being through expert massage, makeup artistry, skin treatments, and facial spa experiences.</p>
                    </div>
                    <div class="mission">
                        <div class="circle"><i class="fas fa-bullseye"></i></div>
                        <h3>Mission</h3>
                        <p>To cultivate a haven of personalized and specialized care, empowering individuals to unlock their natural beauty and inner well-being through expert massage, makeup artistry, skin treatments, and facial spa experiences.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--Product Used-->
    <div class="row">
      <div class="col-md-6">
        <div class="about-container" style="background-image: linear-gradient(180deg, #D0A2F7, #F1EAFF)">
            <h2>Products Used for Service</h2>
            <p style="margin: 20px 30px; text-align: center;">At Elvira True Beauty Salon, we are dedicated to providing you with the most exceptional experience possible. That's why we've partnered with Artistry, a globally recognized brand renowned for its innovative and science-backed approach to beauty.</p>
            <hr>
            <div class="product-content">
                <div class="product-text">
                <h3>Artistry: Where Nature Meets Science</h3>
                    <p style="text-align: center;">Artistry products are formulated using a unique philosophy that combines the power of nature with cutting-edge scientific research. Nutrilite™-grown botanicals, meticulously chosen and refined, form the foundation of these products. Artistry's renowned skin science then amplifies their effectiveness, ensuring you receive visible results.</p>
                </div>
                <div class="product-image">
                    <img src="images/about-product1.jpg" alt="Image">
                </div>
            </div>
            <hr>
            <div class="product-content">
                <div class="product-image">
                    <img src="images/about-product2.jpg" alt="Image">
                </div>
                <div class="product-text">
                <h3>Artistry Products Tailored to Your Needs</h3>
                    <p style="text-align: center;">Our team of experienced professionals is dedicated to understanding your unique skin and beauty goals. We will work with you to select the perfect Artistry products to address your specific needs. Whether you desire a radiant complexion, a youthful glow, or a touch of flawless makeup artistry, Artistry offers a comprehensive range of solutions.</p>
                </div>
            </div>
            <hr>
            <div class="product-content">
                <div class="product-text">
                <h3>Artistry: Where Makeup Meets Skincare</h3>
                    <p style="text-align: center;">Artistry makeup goes beyond color cosmetics. Infused with Nutrilite™-grown botanicals and cutting-edge technology, Artistry makeup nourishes your skin while enhancing your natural features. Imagine flawless coverage that feels weightless, vibrant colors that stay true all day, and formulas that care for your skin with every application.</p>
                </div>
                <div class="product-image">
                    <img src="images/about-product3.jpg" alt="Image">
                </div>
            </div>
            <hr>
            <div class="product-content">
                <div class="product-image">
                    <img src="images/about-product4.jpg" alt="Image">
                </div>
                <div class="product-text">
                <h3>Beyond Makeup: A Holistic Approach to Beauty</h3>
                    <p style="text-align: center;">Our team of experts will curate an Artistry makeup routine tailored to your unique features and preferences.  Combined with our specialized services like facials and massages, you'll experience a complete transformation that celebrates your natural beauty.</p>
                </div>
            </div>
            <hr>
            <div class="product-content">
                <div class="product-text">
                <h3>Experience the Artistry Difference</h3>
                    <p style="text-align: center;">At Elvira True Beauty Salon, we believe Artistry products are the perfect complement to our specialized services. By combining the expertise of our team with the power of Artistry science, we can help you achieve your desired results and unlock your natural radiance.</p>
                </div>
                <div class="product-image">
                    <img src="images/about-product5.jpg" alt="Image">
                </div>
            </div>
            <hr>
                <div class="video-container">
                    <h3>Discover the Artistry advantage and embark on a transformative journey to beauty at Elvira True Beauty Salon.</h3><br>
                    <div class="video-wrapper">
                        <iframe width="560" height="315" src="https://www.youtube.com/embed/RYHhOFFf8Bo?si=KbiCgAEWE6782sky" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen autoplay></iframe>
                    </div>
                </div>
            </div>
      </div>
    </div>
  </div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<div class="footer-bottom" style="background-color: #F1EAFF; color:purple;">
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
