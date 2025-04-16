<?php

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

// Check if service_id is provided in the request
if(isset($_GET['service_id'])) {
    // Retrieve the service ID from the request
    $serviceId = $_GET['service_id'];

    // Prepare and execute the SQL statement to delete the service from the database
    $stmt = $pdo->prepare("DELETE FROM service WHERE service_id = ?");
    $stmt->execute([$serviceId]);

    // Redirect to admin-service.php after successful deletion
    header("Location: admin-service.php");
    exit();
} else {
    // If service_id is not provided, redirect to admin-service.php
    header("Location: admin-service.php");
    exit();
}

?>
