<?php
// Include the PDO connection
include 'config.php'; 

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit;
}

// Retrieve user ID from session
$user_id = $_SESSION['user_id'];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $from_date = $_POST['fromDate'];
    $until_date = $_POST['untilDate'];
    $services = isset($_POST['services']) ? $_POST['services'] : [];

    // Validate input data
    if (empty($from_date) || empty($until_date) || empty($services)) {
        echo "<script>alert('All fields are required.');</script>";
        echo "<script>window.history.back();</script>";
        exit;
    }

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Insert each service availability into the database
        foreach ($services as $service_name) {
            // Retrieve the service_id based on the service name
            $stmt = $pdo->prepare("SELECT service_id FROM service WHERE service_name = ?");
            $stmt->execute([$service_name]);
            $service_id = $stmt->fetchColumn();

            // Insert the availability data into the staff_availability table
            $stmt = $pdo->prepare("INSERT INTO staff_availability (staff_id, service_id, available_start_date, available_end_date, date_created, date_updated) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$user_id, $service_id, $from_date, $until_date]);
        }

        // Commit the transaction
        $pdo->commit();

        // Display success message and redirect back to the availability page
        echo "<script>alert('Availability successfully created.');</script>";
        echo "<script>window.location.href = 'staff-availability.php';</script>";
    } catch (PDOException $e) {
        // Rollback the transaction in case of an error
        $pdo->rollBack();

        // Display error message and redirect back to the availability form
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        echo "<script>window.history.back();</script>";
    }
} else {
    // Redirect to the availability page if the request method is not POST
    header("Location: staff-availability.php");
    exit;
}
?>
