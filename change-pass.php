<?php
// Include the PDO connection
include 'config.php'; 

include 'session-termination.php';

// Check if user is logged in
if(isset($_SESSION['user_id'])) {
    // Retrieve user ID from session
    $user_id = $_SESSION['user_id'];

    // Check if form is submitted
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve form data
        $oldPassword = $_POST['oldPassword'];
        $newPassword = $_POST['newPassword'];
        $confirmNewPassword = $_POST['confirmNewPassword'];

        // Retrieve the current password hash from the database
        $stmt = $pdo->prepare("SELECT password FROM user WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentPasswordHash = $user['password'];

        // Verify if the old password matches the current password
        if(password_verify($oldPassword, $currentPasswordHash)) {
            // Check if the new password is different from the old password
            if($oldPassword != $newPassword) {
                // Check if the new password matches the confirmed new password
                if($newPassword === $confirmNewPassword) {
                    // Validate password complexity
                    if(preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $newPassword)) {
                        // Hash the new password
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                        // Update the password in the database
                        $stmt_update = $pdo->prepare("UPDATE user SET password = ? WHERE user_id = ?");
                        $stmt_update->execute([$hashedPassword, $user_id]);

                        // Display success message and call the JavaScript function
                        echo "<script>alert('Password changed successfully!'); onChangePasswordSuccess(); </script>";

                    } else {
                        // Password complexity requirements not met
                        echo "<script>alert('Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one digit, and one special character.');</script>";
                    }
                } else {
                    // New password and confirmed new password do not match
                    echo "<script>alert('New password and confirmed new password do not match.');</script>";
                }
            } else {
                // New password is the same as the old password
                echo "<script>alert('New password must be different from the old password.');</script>";
            }
        } else {
            // Old password does not match the current password
            echo "<script>alert('Old password is incorrect.');</script>";
        }
    }
} else {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Change Password</title>
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico"/>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>

    <style>
        .hidden {
            display: none;
        }
    </style>

</head>
<body>
    <!-- Change Password Form Container -->
    <div class="change-password-form-container hidden">
        <!-- Change Password Form -->
        <div id="changePasswordModal" class="change-password-form">
            <span class="close-btn" onclick="closeChangePasswordForm()">&times;</span>
            <h2>Change Password</h2>
            <form action="change-pass.php" method="post" onsubmit="return validateChangePasswordForm()">
                <label for="oldPassword">Old Password:</label>
                <input type="password" name="oldPassword" id="oldPassword" required>

                <label for="newPassword">New Password:</label>
                <input type="password" name="newPassword" id="newPassword" required>

                <label for="confirmNewPassword">Confirm New Password:</label>
                <input type="password" name="confirmNewPassword" id="confirmNewPassword" required>

                <button type="submit">Change Password</button>
            </form>
        </div>
    </div>

    <script>

        // Function to handle the security answer response from the popup window
        function handleSecurityAnswer(answer) {
                if (answer === 'match') {
                    alert('Security answer matched. Access granted.');
                    // Reveal the hidden content
                    var content = document.querySelector('.change-password-form-container');
                    content.classList.remove('hidden');
                    // Add code here to reveal the hidden content or perform further actions
                } else {
                    alert('Security answer did not match. Access denied.');
                }
            }

        // Function to toggle the visibility of the content
        function toggleContentVisibility() {
            var content = document.querySelector('.content');
            content.classList.toggle('hidden');
        }

        // Function to show the pop-up window
        function showSecurityQuestionsPopup() {
            // Get the user ID
            var userID = <?php echo isset($user_id) ? $user_id : 'null'; ?>;
            
            // Create a new window with the security questions form, passing the user ID as a query parameter
            var securityQuestionsPopup = window.open('security_questions_popup.php?user_id=' + userID, '_blank', 'width=600,height=400');
            
            // Focus the new window
            if (window.focus) {
                securityQuestionsPopup.focus();
            }
        }

        // Call the function to show the pop-up window when the page loads
        window.onload = function() {
            showSecurityQuestionsPopup();
        };

        function closeChangePasswordForm() {
            // Go back to the previous page
            window.history.back();
        }

        // Function to handle form submission
        function onChangePasswordSuccess() {
            // Show alert message
            alert("Password changed successfully!");

            // Redirect to customer_homepage.php
            window.location.href = "customer_homepage.php";
        }

        // Function to validate the change password form
        function validateChangePasswordForm() {
            var newPassword = document.getElementById("newPassword").value;
            var confirmNewPassword = document.getElementById("confirmNewPassword").value;

            // Check if the new password and confirmed new password match
            if (newPassword !== confirmNewPassword) {
                alert("New password and confirmed new password do not match.");
                return false;
            }

            // Check if the new password meets the complexity requirements
            if (! /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/.test(newPassword)) {
                alert("Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one digit, and one special character.");
                return false;
            }

            return true;
        }
    </script>
</body>
</html>