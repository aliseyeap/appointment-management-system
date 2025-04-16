<?php
session_start();

// Set the timezone to Malaysia Time
date_default_timezone_set('Asia/Kuala_Lumpur');

// Generate a random CAPTCHA code
$captcha_code = '';
$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
for ($i = 0; $i < 6; $i++) {
    $captcha_code .= $characters[rand(0, strlen($characters) - 1)];
}

// Store the CAPTCHA code in session
$_SESSION['captcha_code'] = $captcha_code;

// Create the CAPTCHA image
$captcha_image = imagecreate(120, 40);
$background_color = imagecolorallocate($captcha_image, 245, 245, 245); 
$text_color = imagecolorallocate($captcha_image, 0, 0, 0);

// Add the CAPTCHA code to the image
imagestring($captcha_image, 5, 10, 10, $captcha_code, $text_color);

// Set the content type to PNG and output the image
header('Content-Type: image/png');
imagepng($captcha_image);
imagedestroy($captcha_image);
?>
