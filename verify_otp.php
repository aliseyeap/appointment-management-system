<?php
session_start();

// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include the database connection
include 'config.php';

// Function to verify the OTP
function verifyOTP($inputOTP, $userId, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM otp WHERE user_id = ? AND otp = ? AND otp_expiry >= NOW()");
    $stmt->execute([$userId, $inputOTP]);
    return $stmt->fetch();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $inputOTP = $_POST['otp'];
    $captcha = strtoupper($_POST['captcha']); // Convert the user input to uppercase for case-insensitive comparison
    $storedCaptcha = strtoupper($_SESSION['captcha_code']); // Convert the stored CAPTCHA code to uppercase
    $userId = $_SESSION['user_id'];

    // Verify the CAPTCHA
    if ($captcha === $storedCaptcha) { // Compare as strings
        // Verify the OTP
        $otpVerification = verifyOTP($inputOTP, $userId, $pdo);
        if ($otpVerification) {
            // OTP is correct, proceed with login
            // Fetch user details
            $stmt = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            // Log user login
            $sql = "INSERT INTO login_logs (user_id) VALUES (?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);

            // Check user role and redirect accordingly
            switch ($user['role']) {
                case 'customer':
                    header("Location: customer_homepage.php");
                    exit();
                case 'staff':
                    header("Location: staff-dashboard.php");
                    exit();
                case 'admin':
                    header("Location: admin-dashboard.php");
                    exit();
                default:
                    echo "<script>alert('Invalid user role.');</script>";
                    break;
            }
        } else {
            echo "<script>alert('Invalid OTP. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Invalid CAPTCHA. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Two Factor Authentication</title>
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico"/>
    <link rel="stylesheet" href="css/public_style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <main>
        <div class="box">
            <div class="inner-box">
                <div class="forms-wrap">
                    <form action="verify_otp.php" autocomplete="off" class="sign-in-form" method="post">
                        <div class="logo">
                            <h4>Elvira True Beauty</h4>
                        </div>
                        <div class="heading">
                            <h2>Two-Factor Authentication</h2>
                            <h6>Enter the received OTP and solve the CAPTCHA. </h6>
                        </div>
                        <div class="actual-form">
                            <div class="input-wrap">
                                <input name="otp" type="text" class="input-field" autocomplete="off" required/>
                                <label>OTP</label>
                            </div>

                            <!-- Display CAPTCHA image -->
                            <img src="captcha.php" alt="CAPTCHA Image">
                            <br>
                            <br>

                            <div class="input-wrap">
                                <input name="captcha" type="text" class="input-field" autocomplete="off" required/>
                                <label>CAPTCHA</label>
                            </div>
                            <input type="submit" value="Verify" class="sign-btn" />

                            <p class="text">Does not received an OTP or OTP expired?
                                <a href="phone_number.php">Generate OTPs</a>
                            </p>
                        </div>
                    </form>
                </div>
                <div class="carousel">
                    <div class="images-wrapper">
                        <img src="images/login1.jpg" class="image img-1 show" alt="" />
                        <img src="images/login2.jpg" class="image img-2" alt="" />
                        <img src="images/login3.jpg" class="image img-3" alt="" />
                    </div>

                    <div class="text-slider">
                        <div class="bullets">
                            <span class="active" data-value="1"></span>
                            <span data-value="2"></span>
                            <span data-value="3"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const inputs = document.querySelectorAll(".input-field");
        const bullets = document.querySelectorAll(".bullets span");
        const images = document.querySelectorAll(".image");

        inputs.forEach((inp) => {
            inp.addEventListener("focus", () => {
                inp.classList.add("active");
            });
            inp.addEventListener("blur", () => {
                if (inp.value != "") return;
                inp.classList.remove("active");
            });
        });

        function moveSlider() {
            let index = this.dataset.value;

            images.forEach((img) => img.classList.remove("show"));
            images[index - 1].classList.add("show");

            bullets.forEach((bull) => bull.classList.remove("active"));
            this.classList.add("active");
        }

        bullets.forEach((bullet) => {
            bullet.addEventListener("click", moveSlider);
        });

        let currentSlideIndex = 0;
        const totalSlides = images.length;

        function showNextSlide() {
            images[currentSlideIndex].classList.remove('show');
            currentSlideIndex = (currentSlideIndex + 1) % totalSlides;
            images[currentSlideIndex].classList.add('show');
            bullets[currentSlideIndex].classList.add('active');
            bullets.forEach((bullet, index) => {
                if (index !== currentSlideIndex) {
                    bullet.classList.remove('active');
                }
            });
        }

        const interval = setInterval(showNextSlide, 5000);
    });
</script>
</body>
</html>