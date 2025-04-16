<?php
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
if(!isset($_SESSION['user_id'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

// Check if appointment ID is provided in the URL
if(isset($_GET['appointment_id']) && !empty($_GET['appointment_id'])) {
    // Sanitize the input to prevent SQL injection
    $appointment_id = filter_var($_GET['appointment_id'], FILTER_SANITIZE_NUMBER_INT);

    try {
        // Retrieve customer information before deleting the appointment
        $stmt_get_customer_info = $pdo->prepare("SELECT customer_id, appointment_date, appointment_time, service_id FROM appointment WHERE appointment_id = ?");
        $stmt_get_customer_info->execute([$appointment_id]);
        $appointment_info = $stmt_get_customer_info->fetch(PDO::FETCH_ASSOC);

        if($appointment_info) {
            $customer_id = $appointment_info['customer_id'];
            $appointment_date = date("d/m/Y", strtotime($appointment_info['appointment_date']));
            $appointment_time = date("ga", strtotime($appointment_info['appointment_time']));
            $service_id = $appointment_info['service_id'];

            // Retrieve the service name
            $stmt_get_service_name = $pdo->prepare("SELECT service_name FROM service WHERE service_id = ?");
            $stmt_get_service_name->execute([$service_id]);
            $service_name = $stmt_get_service_name->fetchColumn();

            if($service_name) {
                // Prepare SQL query to delete the appointment
                $stmt_delete = $pdo->prepare("DELETE FROM appointment WHERE appointment_id = ?");
                $stmt_delete->execute([$appointment_id]);

                // Check if the appointment was successfully deleted
                if($stmt_delete->rowCount() > 0) {
                    // Send cancellation message to customer
                    $customerPhoneStmt = $pdo->prepare("SELECT phone_number FROM user WHERE user_id = ?");
                    $customerPhoneStmt->execute([$customer_id]);
                    $customerPhone = $customerPhoneStmt->fetchColumn();

                    if ($customerPhone) {
                        // Remove leading zero from phone number
                        $customerPhone = ltrim($customerPhone, '0');

                        // Add country code for Malaysia
                        $customerPhone = '60' . $customerPhone;

                        $message = "Dear Customer, your appointment for $service_name on $appointment_date at $appointment_time has been canceled.";

                        $send_error = sendWhatsAppMessage($customerPhone, $message);

                        if ($send_error) {
                            $_SESSION['error_message'] = 'Appointment canceled, but failed to send cancellation message to customer: ' . $send_error;
                        } else {
                            $_SESSION['success_message'] = 'Appointment canceled successfully! Cancellation message sent to customer.';
                        }
                    } else {
                        $_SESSION['error_message'] = 'Failed to send cancellation message. Customer phone number not found.';
                        // Log the error for further investigation
                        error_log('Customer phone number not found for user ID: ' . $customer_id);
                    }

                    // Redirect back to the appointment management page with a success message
                    header("Location: staff-appointment.php?success=Appointment canceled successfully.");
                    exit();
                } else {
                    // If deletion failed, redirect back to the appointment management page with an error message
                    header("Location: staff-appointment.php?error=Failed to cancel appointment.");
                    exit();
                }
            } else {
                // If service name not found, redirect back to the appointment management page with an error message
                header("Location: staff-appointment.php?error=Service not found.");
                exit();
            }
        } else {
            // If appointment information not found, redirect back to the appointment management page with an error message
            header("Location: staff-appointment.php?error=Appointment not found.");
            exit();
        }
    } catch (PDOException $e) {
        // Handle PDO exceptions
        echo "Error: " . $e->getMessage();
        exit();
    }
} else {
    // If appointment ID is not provided in the URL, redirect to the appointment management page
    header("Location: staff-appointment.php");
    exit();
}
?>