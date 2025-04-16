<?php
// Start the session
session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Ensure the session cookie is deleted
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

// Prevent caching of the page
header("Cache-Control: no-cache, no-store, must-revalidate");

// Redirect to the login page after logout
header("Location: login.php");

// Display a logout success message using JavaScript
echo '<script>alert("Successfully logged out.");</script>';

// Disable the back button in the browser's history
echo '<script>window.history.forward();</script>';

// Ensure any further output is not sent to the browser
exit;
?>
