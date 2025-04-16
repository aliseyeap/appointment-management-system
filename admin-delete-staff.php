<?php
// Include the PDO connection
include 'config.php'; 

// Check if user ID parameter is set
if(isset($_GET['userId'])) {
    // Retrieve user ID from GET parameter
    $userId = $_GET['userId'];

    // Begin a transaction
    $pdo->beginTransaction();

    try {
        // Delete related records in the login_logs table
        $stmt_logs = $pdo->prepare("DELETE FROM login_logs WHERE user_id = ?");
        $stmt_logs->execute([$userId]);

        // Delete related records in the appointment table
        $stmt_appointments = $pdo->prepare("DELETE FROM appointment WHERE staff_id = ?");
        $stmt_appointments->execute([$userId]);

        // Delete related records in the staff_availability table
        $stmt_availability = $pdo->prepare("DELETE FROM staff_availability WHERE staff_id = ?");
        $stmt_availability->execute([$userId]);

        // Delete the user from the user table
        $stmt_user = $pdo->prepare("DELETE FROM user WHERE user_id = ?");
        $stmt_user->execute([$userId]);

        // Commit the transaction
        $pdo->commit();

        // Redirect back to the staff management page with success message
        header("Location: admin-staff.php?status=success");
        exit(); 
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $pdo->rollBack();
        // Redirect back to the staff management page with error message
        header("Location: admin-staff.php?status=error");
        exit(); 
    }
}
?>
