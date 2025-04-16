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
    // Retrieve customer ID from the form
    $customer_id = $_POST['customer_id'];

    // Retrieve form data
    $customerName = $_POST['customerName'];
    $customerGender = $_POST['customerGender'];
    $customerEmail = $_POST['customerEmail'];
    $customerPhoneNumber = $_POST['customerPhoneNumber'];

    // Prepare SQL statement to update customer information
    $stmt = $pdo->prepare("UPDATE user SET username = ?, gender = ?, email = ?, phone_number = ? WHERE user_id = ?");
    $stmt->execute([$customerName, $customerGender, $customerEmail, $customerPhoneNumber, $customer_id]);

    // Redirect to customer list page after update
    header("Location: staff-customer.php?update=success&name=".urlencode($customerName));
    exit(); // Stop script execution
}

// If form is not submitted or invalid request method, redirect to staff-customer.php
header("Location: staff-customer.php");
exit(); // Stop script execution
?>
