<?php
// Include the database connection
include 'config.php';

// Function to validate the password
function validatePassword($password) {
    // Password must contain at least 8 characters, one uppercase, one lowercase, one digit, and one symbol
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{8,}$/';
    return preg_match($pattern, $password);
}

// Function to hash the password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Function to get the current password hash from the database
function getCurrentPasswordHash($email) {
    global $pdo;
    $sql = "SELECT password FROM user WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    return $stmt->fetchColumn();
}

// Function to update the user's password in the database
function updatePassword($email, $password) {
    global $pdo;
    $hashedPassword = hashPassword($password);
    $sql = "UPDATE user SET password = :password WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':password' => $hashedPassword,
        ':email' => $email
    ]);
}

// Function to delete the password reset token
function deletePasswordResetToken($token) {
    global $pdo;
    $sql = "DELETE FROM password_reset WHERE token = :token";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':token' => $token]);
}

// Check if the token is provided in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token exists in the database
    $stmt = $pdo->prepare("SELECT * FROM password_reset WHERE token = ?");
    $stmt->execute([$token]);
    $resetRequest = $stmt->fetch();

    if ($resetRequest) {
        // Token exists, check if the form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];
            $email = $resetRequest['email'];

            // Validate the password
            if (!validatePassword($password)) {
                echo "<script>alert('Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one digit, and one special character.');</script>";
            } elseif ($password !== $confirmPassword) {
                echo "<script>alert('The confirmed password does not match the new password.');</script>";
            } else {
                // Get the current password hash
                $currentPasswordHash = getCurrentPasswordHash($email);

                // Check if the new password is different from the current password
                if (password_verify($password, $currentPasswordHash)) {
                    echo "<script>alert('The new password must be different from the current password.');</script>";
                } else {
                    // Update the password in the database
                    updatePassword($email, $password);

                    // Delete the password reset token
                    deletePasswordResetToken($token);

                    // Display success message and redirect to login page
                    echo "<script>alert('Your password has been reset successfully. You can now log in with your new password.'); window.location.href='login.php';</script>";
                }
            }
        }
    } else {
        echo "<script>alert('Invalid or expired token.'); window.location.href='forget-password.php';</script>";
    }
} else {
    echo "<script>alert('No token provided.'); window.location.href='forget-password.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Elvira True Beauty | Reset Password</title>
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
                <h2>Reset Password</h2>
                <h6>Create a new password</h6>
                </div>

                <div class="actual-form">
                    <div class="input-wrap">
                        <label style="height: 120%;" for="password">New Password:</label>
                        <input name="password" type="password" class="input-field" autocomplete="off" id="password" required>  
                    </div>
                    <div class="input-wrap">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input name="confirm_password" type="password" class="input-field" autocomplete="off" id="confirm_password" required>  
                    </div>
                    <button type="submit" class="sign-btn">Reset Password</button>
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

    toggle_btn.forEach((btn) => {
    btn.addEventListener("click", () => {
        main.classList.toggle("sign-up-mode");
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

