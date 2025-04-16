<?php
// Ensure session is started before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection and other required files
include 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Function to generate a random verification code
function generateVerificationCode() {
    return substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"), 0, 10);
}

// Function to validate the password
function validatePassword($password) {
    // Password must contain at least 8 characters, one uppercase, one lowercase, one digit, and one symbol
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{8,}$/';
    return preg_match($pattern, $password);
}

// Function to hash and encrypt the password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Function to insert a new user into the database
function insertUser($email, $password, $role, $verificationCode) {
    global $pdo;
    $hashedPassword = hashPassword($password);
    $sql = "INSERT INTO user (email, password, role, is_verified, verification_code, date_created, date_updated)
            VALUES (:email, :password, :role, 0, :verification_code, NOW(), NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':email' => $email,
        ':password' => $hashedPassword,
        ':role' => $role,
        ':verification_code' => $verificationCode
    ]);
    return $pdo->lastInsertId();
}

// Function to send verification email
function sendVerificationEmail($email, $verificationCode) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@example.com'; // SMTP username
        $mail->Password = 'your_email_password'; // SMTP password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('your_email@example.com', 'Elvira True Beauty');
        $mail->addAddress($email);

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Elvira True Beauty Salon! Verify your Account & Get Started!';
        $mail->Body = "Thank you for joining the Elvira True Beauty Salon family! We're thrilled to welcome you and can't wait to help you achieve your beauty goals.<br><br>"
                    . "To unlock all the benefits of your account, including booking appointments online, managing your profile, and receiving exclusive offers, please verify your email address.<br><br>"
                    . "Verifying your account is quick and easy! Simply click the link below:<br><br>"
                    . "<a href='http://localhost:3000/verify.php?code=$verificationCode'>Verify Your Email</a><br><br>"
                    . "Here's what you can look forward to after verification:<br><br>"
                    . "<ul>"
                    . "<li><b>Effortless Booking:</b> Schedule your next appointment online anytime, anywhere.</li><br>"
                    . "<li><b>Personalized Profile:</b> Manage your contact information, booking preferences, and appointment history.</li><br>"
                    . "<li><b>Exclusive Offers:</b> Be the first to know about special promotions, discounts, and new services.</li><br>"
                    . "<li><b>Beauty Inspiration:</b> Get expert tips, hair and makeup trends, and exclusive behind-the-scenes peeks.</li><br><br>"
                    . "</ul>"
                    . "If you have any trouble verifying your account, please don't hesitate to reply to this email or contact us at +601120508847 or elviratruebeauty@gmail.com.<br><br>"
                    . "Welcome aboard,";

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
    $password = $_POST['password'];
    $reconfirmedPassword = $_POST['reconfirmed_password'];

    // Check if the email already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        // Email already exists
        echo "<script>alert('This email is already registered. Please use a different email.');</script>";
    } else {
        // Check if the password meets the requirements
        if (!validatePassword($password)) {
            // Password does not meet requirements
            echo "<script>alert('Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one digit, and one special character.');</script>";
        } else {
            // Check if the password and reconfirmed password match
            if ($password !== $reconfirmedPassword) {
                // Passwords do not match
                echo "<script>alert('The reconfirmed password does not match the password. Please make sure they are the same.');</script>";
            } else {
                // Password meets requirements, generate verification code
                $verificationCode = generateVerificationCode();

                // Insert the user into the database with role as 'customer' and store verification code
                $userId = insertUser($email, $password, 'customer', $verificationCode);

                // Send verification email
                if (sendVerificationEmail($email, $verificationCode)) {
                    // Email sent successfully
                    echo "<script>alert('Registration successful. Check your email to verify your account.');</script>";
                } else {
                    // Email sending failed
                    echo "<script>alert('Email could not be sent. Please try again later.');</script>";
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Get Started</title>
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
            <form action="#" autocomplete="off" class="sign-in-form">
              <div class="logo">
                <h4>Elvira True Beauty</h4>
              </div>

              <div class="heading">
                <h2>Welcome Back</h2>
                <h6>Not registred yet?</h6>
                <a href="#" class="toggle">Sign Up</a>
              </div>

              <div class="actual-form">
                <div class="input-wrap">
                  <input type="email" class="input-field" autocomplete="off" required/>
                  <label>Email</label>
                </div>

                <div class="input-wrap">
                  <input type="password" minlength="4" class="input-field" autocomplete="off" required/>
                  <label>Password</label>
                </div>

                <input type="submit" value="Sign In" class="sign-btn" />

                <p class="text"> Forgotten your password? <a href="#">Get help Signing in</a> 
                </p>
              </div>
            </form>

            <!--Sign Up Form-->
            <form action="register.php" autocomplete="off" class="sign-up-form" method="post">
                <div class="logo">
                    <h4>Elvira True Beauty</h4>
                </div>

                <div class="heading">
                    <h2>Get Started</h2>
                    <h6>Already have an account?</h6>
                    <a href="login.php" class="toggle">Sign In</a>
                </div>

                <div class="actual-form">
                    <div class="input-wrap">
                        <input name="email" type="email" class="input-field" autocomplete="off" required/>
                        <label>Email</label>
                    </div>

                    <div class="input-wrap">
                        <input name="password" type="password" minlength="8" class="input-field" autocomplete="off" required/>
                        <label>Password</label>
                    </div>

                    <div class="input-wrap">
                        <input name="reconfirmed_password" type="password" minlength="8" class="input-field" autocomplete="off" required/>
                        <label>Reconfirmed Password</label>
                    </div>

                    <input type="submit" value="Sign Up" class="sign-btn" />

                    <p class="text">By signing up, I agree to the
                        <a href="terms-of-service.php">Terms of Services</a> and
                        <a href="privacy-policy.php">Privacy Policy</a>
                    </p>
                </div>
            </form>
          </div>

          <div class="carousel">
            <div class="images-wrapper">
              <img src="images/register (1).jpg" class="image img-1 show" alt="" />
              <img src="images/register (2).jpg" class="image img-2" alt="" />
              <img src="images/register (3).jpg" class="image img-3" alt="" />
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

    <!-- Javascript file -->

    <script>
    const inputs = document.querySelectorAll(".input-field");
    const toggle_btn = document.querySelectorAll(".toggle");
    const main = document.querySelector("main");
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

    toggle_btn.forEach((btn) => {
        btn.addEventListener("click", () => {
            main.classList.toggle("sign-up-mode");
        });
    });

    function moveSlider() {
        let index = this.dataset.value;

        let currentImage = document.querySelector(`.img-${index}`);
        images.forEach((img) => img.classList.remove("show"));
        currentImage.classList.add("show");

        // Update active bullet
        bullets.forEach((bull) => bull.classList.remove("active"));
        this.classList.add("active");
    }

    bullets.forEach((bullet) => {
        bullet.addEventListener("click", moveSlider);
    });

    let currentSlideIndex = 0;
    const totalSlides = images.length;

    function showSlide(index) {
        images.forEach(slide => slide.classList.remove('show'));
        images[index].classList.add('show');

        // Update active bullet
        bullets.forEach((bull) => bull.classList.remove("active"));
        bullets[index].classList.add("active");
    }

    function showNextSlide() {
        currentSlideIndex = (currentSlideIndex + 1) % totalSlides;
        showSlide(currentSlideIndex);
    }

    const interval = setInterval(showNextSlide, 5000);

    window.onload = function() {
        setTimeout(() => {
            main.classList.add("sign-up-mode");
        }, 1000);
    };
</script>

  </body>
</html>