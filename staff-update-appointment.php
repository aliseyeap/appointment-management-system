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
    $appointmentId = $_POST['appointment_id'];
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
            AND appointment_id != :appointment_id
        ");
        $conflictStmt->execute([
            ':staff_id' => $staffId,
            ':appointment_date' => $appointmentDate,
            ':two_hour_before' => date("H:i:s", strtotime($twoHourBefore)),
            ':two_hour_after' => date("H:i:s", strtotime($twoHourAfter)),
            ':appointment_time' => date("H:i:s", strtotime($appointmentTime)),
            ':appointment_id' => $appointmentId
        ]);

        // Check for customer's other appointments within the time range
        $customerConflictStmt = $pdo->prepare("
            SELECT * FROM appointment
            WHERE customer_id = (
                SELECT customer_id FROM appointment WHERE appointment_id = :appointment_id
            )
            AND appointment_date = :appointment_date
            AND (
                (appointment_time BETWEEN :two_hour_before AND :two_hour_after)
                OR (:appointment_time BETWEEN appointment_time AND DATE_ADD(appointment_time, INTERVAL 2 HOUR))
            )
            AND appointment_id != :appointment_id
        ");
        $customerConflictStmt->execute([
            ':appointment_id' => $appointmentId,
            ':appointment_date' => $appointmentDate,
            ':two_hour_before' => date("H:i:s", strtotime($twoHourBefore)),
            ':two_hour_after' => date("H:i:s", strtotime($twoHourAfter)),
            ':appointment_time' => date("H:i:s", strtotime($appointmentTime))
        ]);

        if ($conflictStmt->rowCount() > 0) {
            // Conflict found with the same staff
            $_SESSION['error_message'] = 'The selected time slot for the service and staff is not available. Please choose a different time.';
            header("Location: staff-view-appointment.php?appointment_id=$appointmentId");
            exit();
        } elseif ($customerConflictStmt->rowCount() > 0) {
            // Conflict found with the customer's other appointments
            $_SESSION['error_message'] = 'The customer already has another appointment within 2 hours of the selected time. Please choose a different time.';
                header("Location: staff-view-appointment.php?appointment_id=$appointmentId");
                exit();
        } else {
            // No conflicts, proceed to update the appointment
            $updateStmt = $pdo->prepare("
                UPDATE appointment 
                SET appointment_date = :appointment_date, 
                    appointment_time = :appointment_time, 
                    appointment_status = :appointment_status
                WHERE appointment_id = :appointment_id
            ");
            $updateStmt->execute([
                ':appointment_date' => $appointmentDate,
                ':appointment_time' => date("H:i:s", strtotime($appointmentTime)),
                ':appointment_status' => $status,
                ':appointment_id' => $appointmentId
            ]);

            // Retrieve appointment details
            $appointmentDetailsStmt = $pdo->prepare("
                SELECT s.service_name, a.appointment_date, a.appointment_time
                FROM appointment a
                INNER JOIN service s ON a.service_id = s.service_id
                WHERE a.appointment_id = :appointment_id
            ");
            $appointmentDetailsStmt->execute([':appointment_id' => $appointmentId]);
            $appointmentDetails = $appointmentDetailsStmt->fetch(PDO::FETCH_ASSOC);

            if ($appointmentDetails) {
                $serviceName = $appointmentDetails['service_name'];
                $appointmentDate = date("d/m/Y", strtotime($appointmentDetails['appointment_date']));
                $appointmentTime = date("h:iA", strtotime($appointmentDetails['appointment_time']));

                // Retrieve customer's phone number
                $customerPhoneStmt = $pdo->prepare("SELECT phone_number FROM user WHERE user_id = (SELECT customer_id FROM appointment WHERE appointment_id = :appointment_id)");
                $customerPhoneStmt->execute([':appointment_id' => $appointmentId]);
                $customerPhoneNumber = $customerPhoneStmt->fetchColumn();

                // Send message to customer
                $message = "Dear Customer, your appointment for $serviceName has been rescheduled to $appointmentDate at $appointmentTime. Please be punctual.";
                $error = sendWhatsAppMessage($customerPhoneNumber, $message);

                if (!empty($error)) {
                    $_SESSION['error_message'] = 'Failed to send notification to the customer.';
                }
            } else {
                $_SESSION['error_message'] = 'Failed to retrieve appointment details.';
            }

            $_SESSION['success_message'] = 'Appointment successfully updated! WhatsApp message sent.';
            header("Location: staff-appointment.php");
            exit();
            }
        }
        catch (PDOException $e) {
            // Handle database errors
            $_SESSION['error_message'] = 'Failed to update appointment. Please choose another time.';
            header("Location: staff-view-appointment.php?appointment_id=$appointmentId");
            exit();
        }
    } else {
        // Invalid request method
        header("Location: staff-appointment.php");
        exit();
    }
?>
