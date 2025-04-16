<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include the database connection
include 'config.php';

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

// Get the appointment details from the POST data
$service_id = $_POST['selectedService'] ?? null;
$staff_id = $_POST['selectedStaff'] ?? null;
$appointment_date = $_POST['appointmentDate'] ?? null;
$appointment_time = date("H:i:s", strtotime($_POST['appointmentTime'])); // Convert to 24-hour format
$customer_id = $_SESSION['user_id']; // Assuming customer ID is stored in session
$appointment_status = 'Coming Soon';

// Check if all required data is available
if ($service_id && $staff_id && $appointment_date && $appointment_time) {
    // Calculate time range for conflict checking
    $twoHourBefore = date("Y-m-d H:i:s", strtotime($appointment_date . ' ' . $appointment_time . ' -2 hours'));
    $twoHourAfter = date("Y-m-d H:i:s", strtotime($appointment_date . ' ' . $appointment_time . ' +2 hours'));

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
            ':staff_id' => $staff_id,
            ':appointment_date' => $appointment_date,
            ':two_hour_before_time' => date("H:i:s", strtotime($appointment_time . ' -2 hours')),
            ':two_hour_after_time' => date("H:i:s", strtotime($appointment_time . ' +2 hours'))
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
            ':customer_id' => $customer_id,
            ':appointment_date' => $appointment_date,
            ':two_hour_before_time' => date("H:i:s", strtotime($appointment_time . ' -2 hours')),
            ':two_hour_after_time' => date("H:i:s", strtotime($appointment_time . ' +2 hours'))
        ]);

        if ($conflictStmt->rowCount() > 0) {
            // Conflict found with the same staff
            $alert_message = "The selected time slot for the service and staff is not available. Please choose a different time.";
        } elseif ($customerConflictStmt->rowCount() > 0) {
            // Conflict found with the customer's other appointments
            $alert_message = "You already have another appointment within 2 hours of the selected time. Please choose a different time.";
        } else {
            // No conflicts, proceed to insert the appointment
            $stmt = $pdo->prepare("
                INSERT INTO appointment (customer_id, staff_id, service_id, appointment_date, appointment_time, appointment_status)
                VALUES (:customer_id, :staff_id, :service_id, :appointment_date, :appointment_time, :appointment_status)
            ");
            $stmt->execute([
                ':customer_id' => $customer_id,
                ':staff_id' => $staff_id,
                ':service_id' => $service_id,
                ':appointment_date' => $appointment_date,
                ':appointment_time' => $appointment_time,
                ':appointment_status' => $appointment_status
            ]);

            if ($stmt->rowCount() > 0) {
                // Set success message if appointment was successfully made
                $alert_message = "Appointment made successfully!";
                
                // Retrieve the phone number of the customer
                $user_stmt = $pdo->prepare("SELECT phone_number FROM user WHERE user_id = ?");
                $user_stmt->execute([$customer_id]);
                $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $phone_number = $user['phone_number'];
                    // Remove leading zero if present
                    if (substr($phone_number, 0, 1) === '0') {
                        $phone_number = substr($phone_number, 1);
                    }
                    $full_phone_number = "60" . $phone_number;

                    // Format the new date and time
                    $formatted_date = date('d/m/Y', strtotime($appointment_date));
                    $formatted_time = date('g:iA', strtotime($appointment_time));

                    // Retrieve the service name
                    $service_stmt = $pdo->prepare("SELECT service_name FROM service WHERE service_id = ?");
                    $service_stmt->execute([$service_id]);
                    $service = $service_stmt->fetch(PDO::FETCH_ASSOC);
                    $service_name = $service ? $service['service_name'] : 'Service';

                    // Prepare the WhatsApp message
                    $message = "Dear customer, your appointment for $service_name has been successfully scheduled for $formatted_date at $formatted_time. Please be punctual.";

                    // Send the WhatsApp message using the Fonnte API
                    $send_error = sendWhatsAppMessage($full_phone_number, $message);

                    if ($send_error) {
                        // Log the error or handle it as needed
                        $alert_message = "Appointment made successfully, but failed to send WhatsApp message: $send_error";
                    } else {
                        // Successfully sent the message
                        $alert_message = "Appointment made successfully! WhatsApp message sent.";
                    }
                }
            } else {
                // Set error message if appointment failed to be made
                $alert_message = "Failed to make the appointment. Please try again.";
            }
        }
    } catch (PDOException $e) {
        // Handle database errors
        $alert_message = "Failed to make appointment. Please try again later.";
    }
} else {
    // Set error message if any required data is missing
    $alert_message = "Please fill out all required fields.";
}

// Prepare the JavaScript code to display the alert
$js_code = "<script>alert('$alert_message'); window.location.href = 'customer-appointment.php';</script>";

// Output the JavaScript code
echo $js_code;
?>
