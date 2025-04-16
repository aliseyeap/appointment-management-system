<?php

// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php';

// Start session
session_start();

// Display success message
$_SESSION['success_message'] = "Service updated successfully!";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $service_id = $_POST['service_id'];
    $serviceName = $_POST['serviceName'];
    $serviceDescription = $_POST['serviceDescription'];
    $serviceDuration = $_POST['serviceDuration'];
    $servicePrice = $_POST['servicePrice'];

    // Check if a new image is uploaded
    if ($_FILES['serviceImage']['name']) {
        // Handle image upload
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($_FILES["serviceImage"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["serviceImage"]["tmp_name"]);
        if ($check === false) {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["serviceImage"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            // Attempt to move uploaded file
            if (move_uploaded_file($_FILES["serviceImage"]["tmp_name"], $targetFile)) {
                // Update service with new image
                $serviceImagePath = $targetFile;
                $stmt = $pdo->prepare("UPDATE service SET service_name = ?, service_description = ?, service_duration = ?, service_price = ?, service_image = ?, date_updated = NOW() WHERE service_id = ?");
                $stmt->execute([$serviceName, $serviceDescription, $serviceDuration, $servicePrice, $serviceImagePath, $service_id]);
                // Display success message
                echo "<script>alert('Service updated successfully!');</script>";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        // Update service without changing the image
        $stmt = $pdo->prepare("UPDATE service SET service_name = ?, service_description = ?, service_duration = ?, service_price = ?, date_updated = NOW() WHERE service_id = ?");
        $stmt->execute([$serviceName, $serviceDescription, $serviceDuration, $servicePrice, $service_id]);
        // Display success message
        echo "<script>alert('Service updated successfully!');</script>";
    }

    // Redirect to admin-service.php after successful update
    header("Location: admin-service.php");
    exit();
} else {
    // Redirect to the update service form if accessed directly without form submission
    header("Location: admin-service.php");
    exit();
}

?>
