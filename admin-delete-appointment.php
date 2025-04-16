<?php
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
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

// Check if appointment ID is provided in the URL
if (isset($_GET['appointment_id']) && !empty($_GET['appointment_id'])) {
    // Sanitize the input to prevent SQL injection
    $appointment_id = filter_var($_GET['appointment_id'], FILTER_SANITIZE_NUMBER_INT);

    try {
        // Prepare SQL query to retrieve appointment details including customer information
        $stmt_appointment = $pdo->prepare("SELECT * FROM appointment WHERE appointment_id = ?");
        $stmt_appointment->execute([$appointment_id]);
        $appointment = $stmt_appointment->fetch(PDO::FETCH_ASSOC);

        // Check if the appointment exists
        if ($appointment) {
            // Retrieve customer ID
            $customer_id = $appointment['customer_id'];

            // Retrieve service name
            $service_id = $appointment['service_id'];
            $stmt_service = $pdo->prepare("SELECT service_name FROM service WHERE service_id = ?");
            $stmt_service->execute([$service_id]);
            $service = $stmt_service->fetch(PDO::FETCH_ASSOC);
            $service_name = ($service && isset($service['service_name'])) ? $service['service_name'] : '';

            // Prepare SQL query to retrieve customer's phone number based on customer ID
            $stmt_customer = $pdo->prepare("SELECT phone_number FROM user WHERE user_id = ?");
            $stmt_customer->execute([$customer_id]);
            $customer = $stmt_customer->fetch(PDO::FETCH_ASSOC);

            // Check if customer's phone number was retrieved successfully
            if ($customer && isset($customer['phone_number'])) {
                $customer_phone = $customer['phone_number'];

                // Delete the appointment from the database
                $stmt_delete = $pdo->prepare("DELETE FROM appointment WHERE appointment_id = ?");
                $stmt_delete->execute([$appointment_id]);

                // Check if the appointment was successfully deleted
                if ($stmt_delete->rowCount() > 0) {
                    // Compose cancellation message
                    $appointment_date = date('d/m/Y', strtotime($appointment['appointment_date']));
                    $appointment_time = date('g:iA', strtotime($appointment['appointment_time']));
                    $message = "Dear Customer, your appointment for $service_name scheduled on $appointment_date at $appointment_time has been canceled.";

                    // Send cancellation message to customer
                    $send_error = sendWhatsAppMessage($customer_phone, $message);

                    // Check if message was sent successfully
                    if ($send_error) {
                        // If message sending failed, display error message
                        echo '<script>alert("Failed to send cancellation message: '.$send_error.'");</script>';
                    } else {
                        // If message sending succeeded, display success message
                        echo '<script>alert("Appointment canceled successfully. Cancellation message sent to customer.");</script>';
                    }
                } else {
                    // If deletion failed, display error message
                    echo '<script>alert("Failed to cancel appointment.");</script>';
                }

                // Redirect back to the appointment management page after a delay
                echo '<script>window.setTimeout(function(){ window.location.href = "admin-appointment.php"; }, 500);</script>';
                exit();
            } else {
                // If customer's phone number is not found or empty, display error message
                echo '<script>alert("Failed to retrieve customer phone number.");</script>';
            }
        } else {
            // If appointment does not exist, display error message
            echo '<script>alert("Appointment not found.");</script>';
        }
    } catch (PDOException $e) {
        // Handle PDO exceptions
        echo "Error: " . $e->getMessage();
        exit();
    }
} else {
    // If appointment ID is not provided in the URL, redirect to the appointment management page
    header("Location: admin-appointment.php");
    exit();
}
?>