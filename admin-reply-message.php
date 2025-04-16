<?php
// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php';
include 'session-termination.php';

// Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Check if user is logged in
if(isset($_SESSION['user_id'])) {
    // Retrieve user ID from session
    $user_id = $_SESSION['user_id'];

    // Prepare SQL query to retrieve user information based on user ID
    $stmt_user = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    // Check if user is admin
    if($user['role'] == 'admin') {
        // Retrieve admin name
        $admin_name = $user['username'];

        // Check if all necessary POST data is provided
        if(isset($_POST['sender_name'], $_POST['sender_email'], $_POST['message_text'], $_POST['reply_text'])) {
            // Sanitize input data
            $sender_name = htmlspecialchars($_POST['sender_name']);
            $sender_email = htmlspecialchars($_POST['sender_email']);
            $message_text = htmlspecialchars($_POST['message_text']);
            $reply_text = htmlspecialchars($_POST['reply_text']);

            // Update the message in the database
            $stmt_update = $pdo->prepare("UPDATE message SET message_status = 'Replied', reply_text = ?, reply_datetime = ? WHERE name = ? AND email = ? AND message_text = ?");
            $reply_datetime = date("Y-m-d H:i:s");
            $stmt_update->execute([$reply_text, $reply_datetime, $sender_name, $sender_email, $message_text]);

            // Send an email to the customer using PHPMailer
            $mail = new PHPMailer(true);
            try {
                //Server settings
                $mail->isSMTP();                                          
                $mail->Host       = 'smtp.gmail.com';                    
                $mail->SMTPAuth   = true;                                  
                $mail->Username   = 'elviratruebeauty@gmail.com';              
                $mail->Password   = 'lldumisyvoelvboc';                       
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;                                    

                //Recipients
                $mail->setFrom('elviratruebeauty@gmail.com', 'Elvira True Beauty');
                $mail->addAddress($sender_email, $sender_name);             

                // Content
                $mail->isHTML(true);                                        // Set email format to HTML
                $mail->Subject = 'Re: Your Question to ELvira True Beauty Salon';
                $mail->Body    = "
                <html>
                <head>
                    <title>Re: Your Question to Elvira True Beauty Salon</title>
                </head>
                <body>
                    <h2>We're happy to hear back from you!</h2>
                    <p>This email follows up on your recent inquiry. Our team has reviewed your message and is delighted to provide the following response:</p>
                    <p><strong>Your Question:</strong> \"$message_text\"</p>
                    <p><strong>Our Reply:</strong> \"$reply_text\"</p>
                    <p>We hope this information addresses your query. If you have any further questions or require additional clarification, please don't hesitate to reply to this email.</p>
                    <p>Thank you for choosing Elvira True Beauty Salon!</p>
                    <p>Sincerely,<br>The Elvira True Beauty Salon Team</p>
                </body>
                </html>";

                $mail->send();
                // Set success message in session
                $_SESSION['success'] = "Message replied successfully and email sent.";
            } catch (Exception $e) {
                // Set error message in session if email failed
                $_SESSION['error'] = "Message replied but failed to send email. Mailer Error: {$mail->ErrorInfo}";
            }

            // Redirect back to the message management page
            header("Location: admin-message.php");
            exit();
        } else {
            // If data is missing, set error message in session and redirect
            $_SESSION['error'] = "Please provide all necessary data.";
            header("Location: admin-message.php");
            exit();
        }
    } else {
        // If user is not admin, set error message in session and redirect
        $_SESSION['error'] = "Unauthorized access.";
        header("Location: admin-message.php");
        exit();
    }
} else {
    // If user is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
?>
