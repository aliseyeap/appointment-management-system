<?php
// Include the PDO connection
include 'config.php';

// Start session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit;
}

// Check if the request contains the availability_id parameter
if (!isset($_GET['availability_id'])) {
    // Redirect back to the availability management page if the parameter is missing
    header("Location: staff-availability.php");
    exit;
}

// Retrieve the availability_id from the request
$availability_id = $_GET['availability_id'];

try {
    // Prepare the DELETE SQL query to remove the availability entry based on its ID
    $stmt = $pdo->prepare("DELETE FROM staff_availability WHERE availability_id = ?");
    
    // Execute the query with the provided availability ID
    $stmt->execute([$availability_id]);

    // Display success message and redirect back to the availability management page
    echo "<script>alert('Availability successfully deleted.');</script>";
    echo "<script>window.location.href = 'staff-availability.php';</script>";
} catch (PDOException $e) {
    // Display error message and redirect back to the availability management page
    echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    echo "<script>window.location.href = 'staff-availability.php';</script>";
}
?>
