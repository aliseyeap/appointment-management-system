<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php'; 

// Start session
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

// Check if form is submitted
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve staff ID from the form
    $staff_id = $_POST['staff_id'];

    // Retrieve form data
    $staffName = $_POST['staffName'];
    $staffGender = $_POST['staffGender'];
    $staffEmail = $_POST['staffEmail'];
    $staffPhoneNumber = $_POST['staffPhoneNumber'];

    // Prepare SQL statement to update staff information
    $stmt = $pdo->prepare("UPDATE user SET username = ?, gender = ?, email = ?, phone_number = ? WHERE user_id = ?");
    $stmt->execute([$staffName, $staffGender, $staffEmail, $staffPhoneNumber, $staff_id]);

    // Redirect to staff list page after update
    header("Location: admin-staff.php?update=success&name=".urlencode($staffName));
    exit(); // Stop script execution
}

// If form is not submitted or invalid request method, redirect to admin-staff.php
header("Location: admin-staff.php");
exit(); // Stop script execution
?>
