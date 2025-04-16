<?php
// Include the PDO connection
include 'config.php';

// Start session
session_start();

// Check if user is logged in
if(isset($_SESSION['user_id'])) {
    // Retrieve user ID from session
    $user_id = $_SESSION['user_id'];

    // Prepare SQL query to retrieve events based on staff ID
    $stmt_events = $pdo->prepare("SELECT appointment_id, customer_id, service_id, appointment_date, appointment_time FROM appointment WHERE staff_id = ?");
    $stmt_events->execute([$user_id]);
    $events = $stmt_events->fetchAll(PDO::FETCH_ASSOC);

    // Return events in JSON format
    echo json_encode($events);
} else {
    // If user is not logged in, return empty JSON array
    echo json_encode([]);
}
?>
