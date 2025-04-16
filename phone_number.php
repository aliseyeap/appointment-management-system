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

// Function to generate a random OTP
function generateOTP() {
    return rand(100000, 999999); // Generate a 6-digit random number
}

// Function to send the OTP via WhatsApp using Fonnte API
function sendOTP($phoneNumber, $otp) {
    $apiToken = 'your_fontee_api_token_here';
    $message = "Your OTP for login is: $otp. This code will expire in 5 minutes. For your security, please do not share this code with anyone. ";

    $data = [
        'target' => $phoneNumber,
        'message' => $message,
        'countryCode' => '60', // Replace with your country code if necessary
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.fonnte.com/send");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: $apiToken",
        "Content-Type: application/x-www-form-urlencoded"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $phoneNumber = $_POST['phone_number'];
    $userId = $_SESSION['user_id'];

    // Generate and send OTP
    $otp = generateOTP();
    sendOTP($phoneNumber, $otp);

    // Store OTP in the database with an expiry time of 5 minutes
    $otpExpiry = date("Y-m-d H:i:s", strtotime('+5 minutes'));
    $stmt = $pdo->prepare("INSERT INTO otp (user_id, otp, otp_expiry) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $otp, $otpExpiry]);

    // Redirect to OTP verification page
    header("Location: verify_otp.php");
    exit();
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
                    <form action="phone_number.php" autocomplete="off" class="sign-in-form" method="post">
                        <div class="logo">
                            <h4>Elvira True Beauty</h4>
                        </div>

                        <div class="heading">
                            <h2>Generate OTP</h2>
                            <br>
                            <h6>Please enter your phone number to receive an OTP. The OTP will be sent to your WhatsApp.</h6>
                        </div>
                        <div class="actual-form">
                            <div class="input-wrap">
                                <input name="phone_number" type="text" class="input-field" autocomplete="off" required/>
                                <label for="phone_num">Phone Number (with country code)</label>
                            </div>
                            <input type="submit" value="Send OTP" class="sign-btn" />
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
</main>
</body>
</html>
