<?php
// Include the database connection
include 'config.php';

// Check if verification code is provided
if (isset($_GET['code'])) {
    $verificationCode = $_GET['code'];

    // Check if the verification code exists in the database
    $stmt = $pdo->prepare("SELECT * FROM user WHERE verification_code = ? AND is_verified = 0");
    $stmt->execute([$verificationCode]);
    $user = $stmt->fetch();

    if ($user) {
        // Update the user to set is_verified to 1
        $stmt = $pdo->prepare("UPDATE user SET is_verified = 1, verification_code = NULL WHERE verification_code = ?");
        $stmt->execute([$verificationCode]);

        // Success message and redirection to login page
        echo "<script>
            alert('Your email has been verified. You can now log in.');
            window.location.href = 'login.php';
        </script>";
    } else {
        // Invalid or expired verification code
        echo "<script>
            alert('Invalid or expired verification code.');
            window.location.href = 'login.php';
        </script>";
    }
} else {
    // No verification code provided
    echo "<script>
        alert('No verification code provided.');
        window.location.href = 'login.php';
    </script>";
}
?>
