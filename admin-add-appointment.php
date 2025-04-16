<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php';

// Function to send a WhatsApp message using Fonnte API
function sendWhatsAppMessage($phoneNumber, $message) {
    $apiToken = 'your_fontee_api_token_here';

    $data = [
        'target' => $phoneNumber,
        'message' => $message,
        'countryCode' => '60', // Malaysia country code
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

// Start session
session_start();

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
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customerId = $_POST['customerName'];
    $serviceId = $_POST['service'];
    $appointmentDate = $_POST['appointmentDate'];
    $appointmentTime = date("H:i:s", strtotime($_POST['appointmentTime'])); // Convert to 24-hour format
    $staffId = $_POST['assignedTo'];
    $appointmentStatus = $_POST['status'];
    $appointmentDateTime = $appointmentDate . ' ' . $appointmentTime;
    $twoHourBefore = date("Y-m-d H:i:s", strtotime($appointmentDateTime . ' -2 hours'));
    $twoHourAfter = date("Y-m-d H:i:s", strtotime($appointmentDateTime . ' +2 hours'));

    try {
        // Check for conflicting appointments with the same staff
        $conflictStmt = $pdo->prepare("
            SELECT * FROM appointment
            WHERE staff_id = :staff_id
            AND (
                (appointment_date = :appointment_date AND appointment_time BETWEEN :two_hour_before_time AND :two_hour_after_time)
            )
        ");
        $conflictStmt->execute([
            ':staff_id' => $staffId,
            ':appointment_date' => $appointmentDate,
            ':two_hour_before_time' => date("H:i:s", strtotime($appointmentTime . ' -2 hours')),
            ':two_hour_after_time' => date("H:i:s", strtotime($appointmentTime . ' +2 hours'))
        ]);

        // Check for customer's other appointments within the time range
        $customerConflictStmt = $pdo->prepare("
            SELECT * FROM appointment
            WHERE customer_id = :customer_id
            AND (
                (appointment_date = :appointment_date AND appointment_time BETWEEN :two_hour_before_time AND :two_hour_after_time)
            )
        ");
        $customerConflictStmt->execute([
            ':customer_id' => $customerId,
            ':appointment_date' => $appointmentDate,
            ':two_hour_before_time' => date("H:i:s", strtotime($appointmentTime . ' -2 hours')),
            ':two_hour_after_time' => date("H:i:s", strtotime($appointmentTime . ' +2 hours'))
        ]);

        if ($conflictStmt->rowCount() > 0) {
            // Conflict found with the same staff
            $_SESSION['error_message'] = "The selected time slot for the service and staff is not available. Please choose a different time.";
            header("Location: admin-create-appointment.php");
            exit();
        } elseif ($customerConflictStmt->rowCount() > 0) {
            // Conflict found with the customer's other appointments
            $_SESSION['error_message'] = "The customer already has another appointment within 2 hours of the selected time. Please choose a different time.";
            header("Location: admin-create-appointment.php");
            exit();
        } else {
            // No conflicts, proceed to insert the appointment
            $insertStmt = $pdo->prepare("
                INSERT INTO appointment (customer_id, service_id, staff_id, appointment_date, appointment_time, appointment_status, date_created)
                VALUES (:customer_id, :service_id, :staff_id, :appointment_date, :appointment_time, :appointment_status, NOW())
            ");
            $insertStmt->execute([
                ':customer_id' => $customerId,
                ':service_id' => $serviceId,
                ':staff_id' => $staffId,
                ':appointment_date' => $appointmentDate,
                ':appointment_time' => $appointmentTime,
                ':appointment_status' => $appointmentStatus
            ]);

            $_SESSION['success_message'] = "Appointment successfully made!";

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

                // Compose the WhatsApp message
                $message = "Dear Customer, your appointment for $serviceName has been scheduled on $formatted_date at $formatted_time. Please be punctual.";

                // Call the function to send the WhatsApp message
                $send_error = sendWhatsAppMessage($full_phone_number, $message);

                if ($send_error) {
                    // Log the error or handle it as needed
                    $_SESSION['error_message'] = "Appointment scheduled successfully, but failed to send WhatsApp message: $send_error";
                } else {
                    // Successfully sent the message
                    $_SESSION['success_message'] = "Appointment scheduled successfully! WhatsApp message sent.";
                }
            } else {
                // Customer phone number not found or empty
                $_SESSION['error_message'] = "Failed to send WhatsApp message. Customer phone number not found.";
            }
            header("Location: admin-appointment.php");
            exit();
        }
    } catch (PDOException $e) {
        // Handle database errors
        $_SESSION['error_message'] = "Failed to make appointment. Please try again later.";
        header("Location: admin-appointment.php");
        exit();
    }
} else {
    // Invalid request method
    header("Location: admin-appointment.php");
    exit();
}
?>
