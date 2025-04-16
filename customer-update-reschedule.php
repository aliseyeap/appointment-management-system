<?php
session_start();

// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

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

// Get the appointment ID, new date, and new time from the POST data
$appointment_id = $_POST['appointment_id'] ?? null;
$new_date = $_POST['rescheduleDate'] ?? null;
$new_time = $_POST['rescheduleTime'] ?? null;

// Initialize the alert message
$alert_message = "";

// Check if all required data is available
if ($appointment_id && $new_date && $new_time) {
    // Format the time with leading zero for the hour (if necessary)
    $new_time_formatted = date('H:i', strtotime($new_time));

    // Calculate the service duration (2 hours and 5 minutes)
    $service_duration = '+2 hours 5 minutes';
    $service_end_time = date('H:i', strtotime($service_duration, strtotime($new_time_formatted)));

    // Get the staff ID, customer ID, and service ID associated with the appointment
    $appointment_stmt = $pdo->prepare("SELECT staff_id, customer_id, service_id FROM appointment WHERE appointment_id = ?");
    $appointment_stmt->execute([$appointment_id]);
    $appointment = $appointment_stmt->fetch(PDO::FETCH_ASSOC);

    if ($appointment) {
        $staff_id = $appointment['staff_id'];
        $customer_id = $appointment['customer_id'];
        $service_id = $appointment['service_id'];

        // Retrieve the service name
        $service_stmt = $pdo->prepare("SELECT service_name FROM service WHERE service_id = ?");
        $service_stmt->execute([$service_id]);
        $service = $service_stmt->fetch(PDO::FETCH_ASSOC);
        $service_name = $service ? $service['service_name'] : 'Service';

        // Check for conflicting appointments with the same staff
        $conflict_stmt = $pdo->prepare("
            SELECT * FROM appointment
            WHERE staff_id = ? 
            AND appointment_id != ? 
            AND appointment_date = ? 
            AND (
                (appointment_time BETWEEN ? AND ?) 
                OR (appointment_time < ? AND ? < DATE_ADD(appointment_time, INTERVAL 2 HOUR))
                OR (DATE_ADD(appointment_time, INTERVAL 2 HOUR) BETWEEN ? AND ?)
            )
        ");
        $conflict_stmt->execute([$staff_id, $appointment_id, $new_date, $new_time_formatted, $service_end_time, $new_time_formatted, $service_end_time, $new_time_formatted, $service_end_time]);

        if ($conflict_stmt->rowCount() > 0) {
            // Conflict found with the same staff
            $alert_message = "There is a conflicting appointment with the same staff. Please choose a different time.";
        } else {
            // Check for conflicting appointments for the customer
            $customer_conflict_stmt = $pdo->prepare("
                SELECT * FROM appointment
                WHERE customer_id = ? 
                AND appointment_id != ? 
                AND appointment_date = ? 
                AND (
                    (appointment_time BETWEEN ? AND ?) 
                    OR (appointment_time < ? AND ? < DATE_ADD(appointment_time, INTERVAL 2 HOUR))
                    OR (DATE_ADD(appointment_time, INTERVAL 2 HOUR) BETWEEN ? AND ?)
                )
            ");
            $customer_conflict_stmt->execute([$customer_id, $appointment_id, $new_date, $new_time_formatted, $service_end_time, $new_time_formatted, $service_end_time, $new_time_formatted, $service_end_time]);

            if ($customer_conflict_stmt->rowCount() > 0) {
                // Conflict found with the customer's other appointments
                $alert_message = "There is a conflicting appointment for the customer. Please choose a different time.";
            } else {
                // Check for conflicting appointments for the customer with different staff
                $customer_staff_conflict_stmt = $pdo->prepare("
                    SELECT * FROM appointment
                    WHERE customer_id = ? 
                    AND staff_id != ? 
                    AND appointment_date = ? 
                    AND (
                        (appointment_time BETWEEN ? AND ?) 
                        OR (appointment_time < ? AND ? < DATE_ADD(appointment_time, INTERVAL 2 HOUR))
                        OR (DATE_ADD(appointment_time, INTERVAL 2 HOUR) BETWEEN ? AND ?)
                    )
                ");
                $customer_staff_conflict_stmt->execute([$customer_id, $staff_id, $new_date, $new_time_formatted, $service_end_time, $new_time_formatted, $service_end_time, $new_time_formatted, $service_end_time]);

                if ($customer_staff_conflict_stmt->rowCount() > 0) {
                    // Conflict found with the customer's other appointments with different staff
                    $alert_message = "There is a conflicting appointment for the customer with a different staff. Please choose a different time.";
                } else {
                    // No conflicts found, proceed with updating the appointment
                    $update_stmt = $pdo->prepare("UPDATE appointment SET appointment_date = ?, appointment_time = ? WHERE appointment_id = ?");
                    $update_stmt->execute([$new_date, $new_time_formatted, $appointment_id]);

                    if ($update_stmt->rowCount() > 0) {
                        // Appointment updated successfully
                        $alert_message = "Appointment rescheduled successfully!";

                        // Retrieve the phone number of the current user
                        $user_id = $_SESSION['user_id'];
                        $user_stmt = $pdo->prepare("SELECT phone_number FROM user WHERE user_id = ?");
                        $user_stmt->execute([$user_id]);
                        $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

                        if ($user) {
                            $phone_number = $user['phone_number'];
                            // Remove leading zero if present
                            if (substr($phone_number, 0, 1) === '0') {
                                $phone_number = substr($phone_number, 1);
                            }
                            $full_phone_number = "60" . $phone_number;

                            // Format the new date and time
                            $formatted_date = date('d/m/Y', strtotime($new_date));
                            $formatted_time = date('g:iA', strtotime($new_time));

                            // Prepare the WhatsApp message
                            $message = "Dear customer, your appointment for $service_name has been successfully rescheduled to $formatted_date at $formatted_time. Please be punctual.";

                            // Send the WhatsApp message using the Fonnte API
                            $send_error = sendWhatsAppMessage($full_phone_number, $message);

                            if ($send_error) {
                                // Log the error or handle it as needed
                                $alert_message = "Appointment rescheduled successfully, but failed to send WhatsApp message: $send_error";
                            } else {
                                // Successfully sent the message
                                $alert_message = "Appointment rescheduled successfully! WhatsApp message sent.";
                            }
                        }
                    } else {
                        // Failed to update the appointment
                        $alert_message = "Failed to reschedule the appointment. Please try again.";
                    }
                }
            }
        }
    } else {
        // Appointment not found
        $alert_message = "Appointment not found. Please try again.";
    }
} else {
    // Missing required data
    $alert_message = "Missing required data. Please try again.";
}

// Prepare the JavaScript code to display the alert
$js_code = "<script>alert('$alert_message'); window.location.href = 'customer-appointment.php';</script>";

// Output the JavaScript code
echo $js_code;
?>
