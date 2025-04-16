<?php
// Include the PDO connection
include 'config.php'; 

// Check if the form is submitted and the required fields are present
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['userId']) && isset($_POST['role'])) {
    // Sanitize and validate input data
    $userId = $_POST['userId'];
    $role = $_POST['role'];

    // Prepare SQL statement to update the user role
    $stmt_update_role = $pdo->prepare("UPDATE user SET role = :role WHERE user_id = :userId");

    // Bind parameters and execute the query
    $stmt_update_role->bindParam(':role', $role);
    $stmt_update_role->bindParam(':userId', $userId);

    if ($stmt_update_role->execute()) {
        // Redirect back to the previous page with a success message
        header("Location: admin-role.php?success=1");
        exit(); // Stop script execution
    } else {
        // Handle the case where the update fails
        echo "Error updating user role.";
    }
} else {
    // Handle the case where the form data is not complete
    echo "Invalid request.";
}
?>
