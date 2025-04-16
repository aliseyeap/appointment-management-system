<?php

// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include the PDO connection
include 'config.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}

// Check if form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $serviceName = $_POST['serviceName'];
    $serviceDescription = $_POST['serviceDescription'];
    $serviceDuration = $_POST['serviceDuration'];
    $servicePrice = $_POST['servicePrice'];

    // Handle image upload
    $targetDir = "uploads/"; // Directory where uploaded images will be stored
    $originalFileName = basename($_FILES["serviceImage"]["name"]);
    $extension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
    $newFileName = uniqid() . '_' . time() . '.' . $extension; // Generate unique filename
    $targetFile = $targetDir . $newFileName;
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

// Check if file already exists (unlikely due to unique filename generation, but still check)
if (file_exists($targetFile)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}

    // Check if image file is a actual image or fake image
    $check = getimagesize($_FILES["serviceImage"]["tmp_name"]);
    if($check === false) {
        echo "File is not an image.";
        $uploadOk = 0;
    }

    // Check if file already exists
    if (file_exists($targetFile)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
    }

    // Check file size
    if ($_FILES["serviceImage"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["serviceImage"]["tmp_name"], $targetFile)) {
            // File uploaded successfully, now check if service name already exists
            $stmt_check = $pdo->prepare("SELECT * FROM service WHERE service_name = ?");
            $stmt_check->execute([$serviceName]);
            $existingService = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($existingService) {
                // Service name already exists, alert the user and ask for a different name
                echo "<script>alert('Service name already exists. Please choose a different name.');</script>";
            } else {
                // Service name does not exist, insert data into database
                $serviceImagePath = $targetFile;

                // Prepare and execute the SQL statement to insert data into the service table
                $stmt = $pdo->prepare("INSERT INTO service (service_name, service_description, service_duration, service_price, service_image, date_created, date_updated) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->execute([$serviceName, $serviceDescription, $serviceDuration, $servicePrice, $serviceImagePath]);

                // Redirect to admin-service.php after successful insertion
                echo "<script>alert('Service added successfully!');</script>";
                echo "<script>window.location.href = 'admin-service.php';</script>";
                exit();
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}
?>
