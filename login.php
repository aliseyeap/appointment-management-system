<?php
// Include the database connection
include 'config.php';

// Start session
session_start();

// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Initialize login attempts counter and timestamp
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}
if (!isset($_SESSION['last_attempt_time'])) {
    $_SESSION['last_attempt_time'] = time();
}

// Function to verify the password
function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the cooldown period has passed
    $current_time = time();
    $elapsed_time = $current_time - $_SESSION['last_attempt_time'];
    if ($_SESSION['login_attempts'] >= 3 && $elapsed_time < 300) {
        echo "<script>alert('Too many failed login attempts. Please wait 5 minutes before trying again.');</script>";
    } else {
        if ($_SESSION['login_attempts'] >= 3 && $elapsed_time >= 300) {
            $_SESSION['login_attempts'] = 0;
        }
        
        // Check if the email exists in the database
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if (verifyPassword($password, $user['password'])) {
                $_SESSION['login_attempts'] = 0;

                if ($user['is_verified'] == 1) {
                    $_SESSION['user_id'] = $user['user_id'];
                    header("Location: phone_number.php");
                    exit();
                } else {
                    echo "<script>alert('Your account is not verified. Please check your email for the verification link.');</script>";
                }
            } else {
                $_SESSION['login_attempts'] += 1;
                $_SESSION['last_attempt_time'] = time();
                echo "<script>alert('Invalid login credentials. Please check your email and password and try again.');</script>";
            }
        } else {
            $_SESSION['login_attempts'] += 1;
            $_SESSION['last_attempt_time'] = time();
            echo "<script>alert('Invalid login credentials. Please check your email and password and try again.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Welcome Back</title>
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
                <h2>Welcome Back</h2>
                <h6>Not registred yet?</h6>
                <a href="register.php" class="toggle">Sign Up</a>
              </div>

              <div class="actual-form">
                <div class="input-wrap">
                  <input name="email" type="email" class="input-field" autocomplete="off" required/>
                  <label>Email</label>
                </div>

                <div class="input-wrap">
                  <input name="password" type="password" minlength="4" class="input-field" autocomplete="off" required/>
                  <label>Password</label>
                </div>

                <input type="submit" id="signInBtn" value="Sign In" class="sign-btn" <?php echo ($_SESSION['login_attempts'] >= 3 && $elapsed_time < 300) ? 'disabled' : ''; ?> />

                <p class="text"> Forgotten your password? <a href="forget-password.php">Get help Signing in</a> 
                </p>
              </div>
            </form>

            <!--Sign Up Form-->
            <form action="" autocomplete="off" class="sign-up-form" method="post">
              <div class="logo">
                <h4>Elvira True Beauty</h4>
              </div>

              <div class="heading">
                <h2>Get Started</h2>
                <h6>Already have an account?</h6>
                <a href="#" class="toggle">Sign In</a>
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
                  <a href="#">Terms of Services</a> and
                  <a href="#">Privacy Policy</a>
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

        images.forEach((img) => img.classList.remove("show"));
        images[index - 1].classList.add("show");

        bullets.forEach((bull) => bull.classList.remove("active"));
        this.classList.add("active");
    }

    bullets.forEach((bullet) => {
        bullet.addEventListener("click", moveSlider);
    });

    // Auto play carousel
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

    const signInBtn = document.getElementById("signInBtn");
    let loginAttempts = <?php echo $_SESSION['login_attempts']; ?>;
    let lastAttemptTime = <?php echo $_SESSION['last_attempt_time']; ?> * 1000;
    let currentTime = new Date().getTime();
    let elapsed = currentTime - lastAttemptTime;

    if (loginAttempts >= 3 && elapsed < 300000) {
        signInBtn.disabled = true;
        let remainingTime = 300000 - elapsed;
        let countdown = Math.floor(remainingTime / 1000);
        
        const countdownInterval = setInterval(() => {
            countdown--;
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                signInBtn.disabled = false;
            }
        }, 1000);
        
        alert('Too many failed login attempts. Please wait 5 minutes before trying again.');
    }
  </script>
</body>
</html>