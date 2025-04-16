<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php';

// Start session
session_start();

// Function to send a WhatsApp message using Fonnte API
function sendWhatsAppMessage($phoneNumber, $message) {
    $apiToken = 'xNAVREMNuZm7MmU8ichz';

    $data = [
        'target' => $phoneNumber,
        'message' => $message,
        'countryCode' => '6', // Malaysia country code
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.fonnte.com/send");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: $apiToken",
        "Content-Type: application/x-www-form-urlencoded"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $errorMsg = curl_error($ch);
    } elseif ($httpCode != 200) {
        $errorMsg = "HTTP status code $httpCode";
    } else {
        $errorMsg = '';
    }

    curl_close($ch);

    return $errorMsg;
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve user ID from session
$user_id = $_SESSION['user_id'];

// Prepare SQL query to retrieve user information based on user ID
$stmt_user = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Check if user is admin
if ($user['role'] != 'admin') {
    // Handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $appointmentId = $_POST['appointment_id'];
    $customerId = $_POST['customer_id'];
    $serviceId = $_POST['service_id'];
    $appointmentDate = $_POST['appointmentDate'];
    $appointmentTime = date("H:i:s", strtotime($_POST['appointmentTime'])); // Convert to 24-hour format
    $staffId = $_POST['assignedTo'];
    $appointmentStatus = $_POST['status'];
    $appointmentDateTime = $appointmentDate . ' ' . $appointmentTime;
    $twoHourBefore = date("Y-m-d H:i:s", strtotime($appointmentDateTime . ' -2 hours -5 minutes'));
    $twoHourAfter = date("Y-m-d H:i:s", strtotime($appointmentDateTime . ' +2 hours +5 minutes'));

    try {
        // Check for conflicting appointments with the same staff
        $conflictStmt = $pdo->prepare("
            SELECT * FROM appointment
            WHERE staff_id = :staff_id
            AND appointment_id != :appointment_id
            AND (
                (appointment_date = :appointment_date AND appointment_time BETWEEN :two_hour_before_time AND :two_hour_after_time)
            )
        ");
        $conflictStmt->execute([
            ':staff_id' => $staffId,
            ':appointment_id' => $appointmentId,
            ':appointment_date' => $appointmentDate,
            ':two_hour_before_time' => date("H:i:s", strtotime($appointmentTime . ' -2 hours -5 minutes')),
            ':two_hour_after_time' => date("H:i:s", strtotime($appointmentTime . ' +2 hours +5 minutes'))
        ]);

        // Check for customer's other appointments within the time range
        $customerConflictStmt = $pdo->prepare("
            SELECT * FROM appointment
            WHERE customer_id = :customer_id
            AND appointment_id != :appointment_id
            AND (
                (appointment_date = :appointment_date AND appointment_time BETWEEN :two_hour_before_time AND :two_hour_after_time)
            )
        ");
        $customerConflictStmt->execute([
            ':customer_id' => $customerId,
            ':appointment_id' => $appointmentId,
            ':appointment_date' => $appointmentDate,
            ':two_hour_before_time' => date("H:i:s", strtotime($appointmentTime . ' -2 hours -5 minutes')),
            ':two_hour_after_time' => date("H:i:s", strtotime($appointmentTime . ' +2 hours +5 minutes'))
        ]);

        // Check for conflicts with customer's other appointments with different staff
        $customerStaffConflictStmt = $pdo->prepare("
            SELECT * FROM appointment
            WHERE customer_id = :customer_id
            AND staff_id != :staff_id
            AND appointment_date = :appointment_date
            AND appointment_time BETWEEN :two_hour_before_time AND :two_hour_after_time
        ");
        $customerStaffConflictStmt->execute([
            ':customer_id' => $customerId,
            ':staff_id' => $staffId,
            ':appointment_date' => $appointmentDate,
            ':two_hour_before_time' => date("H:i:s", strtotime($appointmentTime . ' -2 hours -5 minutes')),
            ':two_hour_after_time' => date("H:i:s", strtotime($appointmentTime . ' +2 hours +5 minutes'))
        ]);

        if ($conflictStmt->rowCount() > 0) {
            // Conflict found with the same staff
            echo "<script>alert('The selected time slot for the service and staff is not available. Please choose a different time.'); window.location.href='admin-view-appointment.php?appointment_id=$appointmentId';</script>";
        } elseif ($customerConflictStmt->rowCount() > 0) {
            // Conflict found with the customer's other appointments
            echo "<script>alert('The customer already has another appointment within 2 hours and 5 minutes of the selected time. Please choose a different time.'); window.location.href='admin-view-appointment.php?appointment_id=$appointmentId';</script>";
        } elseif ($customerStaffConflictStmt->rowCount() > 0) {
            // Conflict found with the customer's other appointments with different staff
            echo "<script>alert('The customer already has an appointment with a different staff within 2 hours and 5 minutes at the selected time. Please choose a different time.'); window.location.href='admin-view-appointment.php?appointment_id=$appointmentId';</script>";
        } else {
            // No conflicts, proceed to update the appointment
            $updateStmt = $pdo->prepare("
                UPDATE appointment
                SET staff_id = :staff_id,
                    appointment_date = :appointment_date,
                    appointment_time = :appointment_time,
                    appointment_status = :appointment_status,
                    date_updated = NOW()
                WHERE appointment_id = :appointment_id
            ");
            $updateStmt->execute([
                ':staff_id' => $staffId,
                ':appointment_date' => $appointmentDate,
                ':appointment_time' => $appointmentTime,
                ':appointment_status' => $appointmentStatus,
                ':appointment_id' => $appointmentId
            ]);

            echo "<script>alert('Appointment successfully updated!');</script>";
            
            // Retrieve the customer's phone number based on the customer ID
            $customerStmt = $pdo->prepare("SELECT phone_number FROM user WHERE user_id = ?");
            $customerStmt->execute([$customerId]);
            $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);

            if ($customer && isset($customer['phone_number'])) {
                $customerPhoneNumber = $customer['phone_number'];

                // Remove leading zero if present
                if (substr($customerPhoneNumber, 0, 1) === '0') {
                    $customerPhoneNumber = substr($customerPhoneNumber, 1);
                }
                $full_phone_number = "60" . $customerPhoneNumber;

                // Format the new date and time
                $formatted_date = date('d/m/Y', strtotime($appointmentDate));
                $formatted_time = date('g:iA', strtotime($appointmentTime));
                
                // Retrieve the service name
                $serviceStmt = $pdo->prepare("SELECT service_name FROM service WHERE service_id = ?");
                $serviceStmt->execute([$serviceId]);
                $service = $serviceStmt->fetch(PDO::FETCH_ASSOC);
                $serviceName = ($service && isset($service['service_name'])) ? $service['service_name'] : '';

                // Compose different WhatsApp messages based on status
                if ($appointmentStatus == 'Completed') {
                    $message = "Dear Customer, your appointment for $serviceName has been completed on $formatted_date at $formatted_time. Thank you for choosing us!";
                } elseif ($appointmentStatus == 'Coming Soon') {
                    $message = "Dear Customer, your appointment for $serviceName has been scheduled for $formatted_date at $formatted_time. Please be punctual.";
                } else {
                    // Default message for other statuses
                    $message = "Dear Customer, your appointment for $serviceName has been updated. New status: $appointmentStatus. Date: $formatted_date, Time: $formatted_time.";
                }
                
                // Call the function to send the WhatsApp message
                $send_error = sendWhatsAppMessage($full_phone_number, $message);

                if ($send_error) {
                    // Log the error or handle it as needed
                    echo "<script>alert('Appointment successfully updated, but failed to send WhatsApp message: $send_error'); window.location.href='admin-appointment.php';</script>";
                } else {
                    // Successfully sent the message
                    echo "<script>alert('Appointment successfully updated! WhatsApp message sent.'); window.location.href='admin-appointment.php';</script>";
                }
            } else {
                // Customer phone number not found or empty
                echo "<script>alert('Failed to send WhatsApp message. Customer phone number not found.'); window.location.href='admin-appointment.php';</script>";
            }
        }
    } catch (PDOException $e) {
        // Handle database errors
        echo "<script>alert('Failed to update appointment. Please try again later.'); window.location.href='admin-appointment.php';</script>";
    }
} else {
    // Invalid request method
    header("Location: admin-appointment.php");
    exit();
}
?>
