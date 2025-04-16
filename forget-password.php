<?php
// Include the database connection and other required files
include 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Function to generate a random token
function generateToken() {
    return bin2hex(random_bytes(32));
}

// Function to insert a password reset token into the database
function insertPasswordResetToken($email, $token) {
    global $pdo;
    $sql = "INSERT INTO password_reset (email, token, created_at)
            VALUES (:email, :token, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':email' => $email,
        ':token' => $token
    ]);
}

// Function to send password reset email
function sendPasswordResetEmail($email, $token) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = 'elviratruebeauty@gmail.com'; // SMTP username
        $mail->Password = 'lldumisyvoelvboc'; // SMTP password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('elviratruebeauty@gmail.com', 'Elvira True Beauty');
        $mail->addAddress($email);

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Elvira True Beauty Salon Password';
        $mail->Body = "We received a request to reset your password for your Elvira True Beauty Salon account.<br><br>"
                    . "If you requested this password reset, please click the link below to create a new, secure password:<br><br>"
                    . "<a href='http://localhost:3000/reset-password.php?token=$token'>Reset Password</a><br><br>"
                    . "This link will expire in 24 hours for your security.<br><br>"
                    . "If you did not request a password reset, please disregard this email.<br><br>"
                    . "For your account security, we recommend creating a strong password that includes a combination of uppercase and lowercase letters, numbers, and symbols.<br><br>"
                    . "If you continue to have trouble resetting your password, please reply to this email or contact us at +601120508847 or elviratruebeauty@gmail.com.<br><br>"
                    . "We're here to help!<br><br>"
                    . "Sincerely,<br><br>"
                    . "The Elvira True Beauty Salon Team";

        $mail->send();
        return true; // Return true if email is sent successfully
    } catch (Exception $e) {
        // Handle errors
        return false; // Return false if email sending fails
    }
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $email = $_POST['email'];

    // Check if the email exists in the database
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // Email exists, generate token and store in database
        $token = generateToken();
        insertPasswordResetToken($email, $token);

        // Send password reset email
        if (sendPasswordResetEmail($email, $token)) {
            // Email sent successfully
            echo "<script>alert('An email with instructions to reset your password has been sent to your email address.');</script>";
        } else {
            // Email sending failed
            echo "<script>alert('Email could not be sent. Please try again later.');</script>";
        }
    } else {
        // Email does not exist
        echo "<script>alert('This email address is not registered.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Forget Password</title>
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
            <form action="#" autocomplete="off" class="sign-in-form" method="post">
                <div class="logo">
                <h4>Elvira True Beauty</h4>
                </div>

                <div class="heading">
                <h2>Forget Your Password?</h2>
                <h6>Reset Your Password Now!</h6>
                </div>

                <div class="actual-form">
                    <div class="input-wrap">
                        <label for="email">Enter your email address:</label>
                        <input name="email" type="email" class="input-field" autocomplete="off" id="email" name="email" required>  
                    </div>
                    <button type="submit" class="sign-btn">Submit</button>
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
    const inputs = document.querySelectorAll(".input-field");

    inputs.forEach((inp) => {
    inp.addEventListener("focus", () => {
        inp.classList.add("active");
    });
    inp.addEventListener("blur", () => {
        if (inp.value != "") return;
        inp.classList.remove("active");
    });
    });

    const bullets = document.querySelectorAll(".bullets span");
    const images = document.querySelectorAll(".image");

    function moveSlider() {
    let index = this.dataset.value;

    let currentImage = document.querySelector(`.img-${index}`);
    images.forEach((img) => img.classList.remove("show"));
    currentImage.classList.add("show");

    const textSlider = document.querySelector(".text-group");
    textSlider.style.transform = `translateY(${-(index - 1) * 2.2}rem)`;

    bullets.forEach((bull) => bull.classList.remove("active"));
    this.classList.add("active");
    }

    bullets.forEach((bullet) => {
    bullet.addEventListener("click", moveSlider);
    });

    // Auto play carousel
    let currentSlideIndex = 0;
    const slides = document.querySelectorAll('.image');
    const totalSlides = slides.length;

    function showSlide(index) {
        // Hide all slides
        slides.forEach(slide => {
            slide.classList.remove('show');
        });

        // Show the slide at the specified index
        slides[index].classList.add('show');
    }

    function showNextSlide() {
        currentSlideIndex = (currentSlideIndex + 1) % totalSlides;
        showSlide(currentSlideIndex);
    }

    // Automatically switch to the next slide every 5 seconds
    const interval = setInterval(showNextSlide, 5000);

    document.addEventListener("DOMContentLoaded", function() {
        // Toggle the sign-up mode after the page loads
        main.classList.toggle("sign-up-mode");
        // Shift from sign-up to sign-in form
        setTimeout(() => {
        main.classList.toggle("sign-up-mode");
        }, 1000); // Change the delay as needed
    });
</script>

</main>
</body>
</html>
