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

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve availability ID from the form
    $availability_id = $_POST['availability_id'];

    // Retrieve form data
    $available_start_date = $_POST['fromDate'];
    $available_end_date = $_POST['untilDate'];

    // Prepare SQL statement to update availability information
    $stmt = $pdo->prepare("UPDATE staff_availability SET available_start_date = ?, available_end_date = ? WHERE availability_id = ?");
    $stmt->execute([$available_start_date, $available_end_date, $availability_id]);

    // Redirect to the availability management page after update
    header("Location: staff-availability.php?update=success&id=" . $availability_id);
    exit(); // Stop script execution
}

// If form is not submitted or invalid request method, redirect to staff-availability.php
header("Location: staff-availability.php");
exit(); // Stop script execution
?>
