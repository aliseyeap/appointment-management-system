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
    $customerName = $_POST['customerName'];
    $customerGender = $_POST['customerGender'];
    $customerEmail = $_POST['customerEmail'];
    $customerPhoneNumber = $_POST['customerPhoneNumber'];

    // Check if the email already exists in the database
    $stmt_check_email = $pdo->prepare("SELECT * FROM user WHERE email = ?");
    $stmt_check_email->execute([$customerEmail]);
    $existing_customer = $stmt_check_email->fetch();

    if ($existing_customer) {
        // If customer with the same email already exists, display an alert message
        echo "<script>alert('Customer already exists!'); 
        window.location.href = 'staff-create-customer.php';</script>";
        exit(); // Stop script execution
    } else {
        // Insert new customer into the database
        $stmt = $pdo->prepare("INSERT INTO user (username, gender, email, phone_number, role, date_created, date_updated) VALUES (?, ?, ?, ?, 'customer', NOW(), NOW())");
        $stmt->execute([$customerName, $customerGender, $customerEmail, $customerPhoneNumber]);

        // Display alert message for successful insertion
        echo "<script>alert('Customer successfully added!'); 
        window.location.href = 'staff-customer.php';</script>";
        exit(); // Stop script execution
    }
} else {
    // Redirect to add customer page if accessed directly
    header("Location: staff-customer.php");
    exit(); // Stop script execution
}
?>
