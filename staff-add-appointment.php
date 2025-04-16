<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php';

// Start session
session_start();

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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customerId = $_POST['customerName'];
    $serviceId = $_POST['service'];
    $appointmentDate = $_POST['appointmentDate'];
    $appointmentTime = $_POST['appointmentTime'];
    $status = $_POST['status'];
    $staffId = $_SESSION['user_id'];
    $appointmentDateTime = $appointmentDate . ' ' . date("H:i:s", strtotime($appointmentTime));
    $twoHourBefore = date("Y-m-d H:i:s", strtotime($appointmentDateTime . ' -2 hours'));
    $twoHourAfter = date("Y-m-d H:i:s", strtotime($appointmentDateTime . ' +2 hours'));

    try {
        // Check for conflicting appointments with the same staff
        $conflictStmt = $pdo->prepare("
            SELECT * FROM appointment
            WHERE staff_id = :staff_id
            AND appointment_date = :appointment_date
            AND (
                (appointment_time BETWEEN :two_hour_before AND :two_hour_after)
                OR (:appointment_time BETWEEN appointment_time AND DATE_ADD(appointment_time, INTERVAL 2 HOUR))
            )
        ");
        $conflictStmt->execute([
            ':staff_id' => $staffId,
            ':appointment_date' => $appointmentDate,
            ':two_hour_before' => date("H:i:s", strtotime($twoHourBefore)),
            ':two_hour_after' => date("H:i:s", strtotime($twoHourAfter)),
            ':appointment_time' => date("H:i:s", strtotime($appointmentTime))
        ]);

        // Check for customer's other appointments within the time range
        $customerConflictStmt = $pdo->prepare("
            SELECT * FROM appointment
            WHERE customer_id = :customer_id
            AND appointment_date = :appointment_date
            AND (
                (appointment_time BETWEEN :two_hour_before AND :two_hour_after)
                OR (:appointment_time BETWEEN appointment_time AND DATE_ADD(appointment_time, INTERVAL 2 HOUR))
            )
        ");
        $customerConflictStmt->execute([
            ':customer_id' => $customerId,
            ':appointment_date' => $appointmentDate,
            ':two_hour_before' => date("H:i:s", strtotime($twoHourBefore)),
            ':two_hour_after' => date("H:i:s", strtotime($twoHourAfter)),
            ':appointment_time' => date("H:i:s", strtotime($appointmentTime))
        ]);

        if ($conflictStmt->rowCount() > 0) {
            // Conflict found with the same staff
            $_SESSION['error_message'] = 'The selected time slot for the service and staff is not available. Please choose a different time.';
            header("Location: staff-create-appointment.php");
            exit();
        } elseif ($customerConflictStmt->rowCount() > 0) {
            // Conflict found with the customer's other appointments
            $_SESSION['error_message'] = 'The customer already has another appointment within 2 hours of the selected time. Please choose a different time.';
            header("Location: staff-create-appointment.php");
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
                ':appointment_time' => date("H:i:s", strtotime($appointmentTime)),
                ':appointment_status' => $status
            ]);

            $_SESSION['success_message'] = 'Appointment successfully made!';

            // Retrieve service name
            $serviceStmt = $pdo->prepare("SELECT service_name FROM service WHERE service_id = ?");
            $serviceStmt->execute([$serviceId]);
            $service = $serviceStmt->fetch(PDO::FETCH_ASSOC);
            $serviceName = $service['service_name'];

            // Format date and time
            $formattedDate = date("d/m/Y", strtotime($appointmentDate));
            $formattedTime = date("g:ia", strtotime($appointmentTime));

            // Send confirmation message to customer
            $customerPhoneStmt = $pdo->prepare("SELECT phone_number FROM user WHERE user_id = ?");
            $customerPhoneStmt->execute([$customerId]);
            $customerPhone = $customerPhoneStmt->fetchColumn();

            if ($customerPhone) {
                // Remove leading zero from phone number
                $customerPhone = ltrim($customerPhone, '0');

                // Add country code for Malaysia
                $customerPhone = '60' . $customerPhone;

                $message = "Dear Customer, your appointment for $serviceName has been scheduled on $formattedDate at $formattedTime. Please be punctual.";

                $send_error = sendWhatsAppMessage($customerPhone, $message);

                if ($send_error) {
                    $_SESSION['error_message'] = 'Appointment successfully made, but failed to send confirmation message: ' . $send_error;
                } else {
                    $_SESSION['success_message'] = 'Appointment successfully made! Confirmation message sent to customer.';
                }
            } else {
                $_SESSION['error_message'] = 'Failed to send confirmation message. Customer phone number not found.';
            }

            header("Location: staff-appointment.php");
            exit();
        }

    } catch (PDOException $e) {
        // Handle database errors
        $_SESSION['error_message'] = 'Failed to make appointment. Please choose another time.';
        header("Location: staff-create-appointment.php");
        exit();
    }
} else {
    // Invalid request method
    header("Location: staff-create-appointment.php");
    exit();
}
?>
