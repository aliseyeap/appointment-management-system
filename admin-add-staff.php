<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $staffName = $_POST['staffName'];
    $staffGender = $_POST['staffGender'];
    $staffEmail = $_POST['staffEmail'];
    $staffPhoneNumber = $_POST['staffPhoneNumber'];

    // Check if the email already exists in the database
    $stmt_check_email = $pdo->prepare("SELECT * FROM user WHERE email = ?");
    $stmt_check_email->execute([$staffEmail]);
    $existing_staff = $stmt_check_email->fetch();

    if ($existing_staff) {
        // If staff with the same email already exists, display an alert message
        echo "<script>alert('staff already exists!'); 
        window.location.href = 'admin-create-staff.php';</script>";
        exit(); // Stop script execution
    } else {
        // Insert new staff into the database
        $stmt = $pdo->prepare("INSERT INTO user (username, gender, email, phone_number, role, date_created, date_updated) VALUES (?, ?, ?, ?, 'staff', NOW(), NOW())");
        $stmt->execute([$staffName, $staffGender, $staffEmail, $staffPhoneNumber]);

        // Display alert message for successful insertion
        echo "<script>alert('Staff successfully added!'); 
        window.location.href = 'admin-staff.php';</script>";
        exit(); // Stop script execution
    }
} else {
    // Redirect to add staff page if accessed directly
    header("Location: admin-staff.php");
    exit(); // Stop script execution
}
?>
