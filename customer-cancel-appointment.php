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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'unauthorized']);
    exit();
}

// Get the appointment ID from the request
$appointment_id = isset($_POST['appointment_id']) ? $_POST['appointment_id'] : '';

if ($appointment_id) {
    // Retrieve the customer's phone number, appointment details, and service name
    $appointmentStmt = $pdo->prepare("
        SELECT a.customer_id, a.appointment_date, a.appointment_time, s.service_name
        FROM appointment a
        INNER JOIN service s ON a.service_id = s.service_id
        WHERE a.appointment_id = ?
    ");
    $appointmentStmt->execute([$appointment_id]);
    $appointmentData = $appointmentStmt->fetch(PDO::FETCH_ASSOC);

    if ($appointmentData) {
        $customer_id = $appointmentData['customer_id'];
        $service_name = $appointmentData['service_name'];

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

            // Format the appointment date and time
            $formatted_date = date('d/m/Y', strtotime($appointmentData['appointment_date']));
            $formatted_time = date('g:iA', strtotime($appointmentData['appointment_time']));

            // Prepare the WhatsApp message
            $message = "Dear customer, your appointment for $service_name scheduled for $formatted_date at $formatted_time has been canceled.";

            // Send the WhatsApp message using the Fonnte API
            $send_error = sendWhatsAppMessage($full_phone_number, $message);

            if ($send_error) {
                // Log the error or handle it as needed
                echo json_encode(['status' => 'failed', 'error' => $send_error]);
            } else {
                // Successfully sent the message, now delete the appointment
                $deleteStmt = $pdo->prepare("DELETE FROM appointment WHERE appointment_id = ?");
                $deleteStmt->execute([$appointment_id]);
                echo json_encode(['status' => 'success']);
            }
        } else {
            echo json_encode(['status' => 'failed', 'error' => 'Failed to retrieve user data']);
        }
    } else {
        echo json_encode(['status' => 'failed', 'error' => 'Appointment not found']);
    }
} else {
    echo json_encode(['status' => 'invalid']);
}
?>
