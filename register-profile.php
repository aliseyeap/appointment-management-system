<?php
// Include the PDO connection
include 'config.php'; 

include 'session-termination.php';

// Check if user is logged in
if(isset($_SESSION['user_id'])) {
    // Retrieve user ID from session
    $user_id = $_SESSION['user_id'];

    // Prepare SQL query to retrieve user information based on user ID
    $stmt_user = $pdo->prepare("SELECT * FROM user WHERE user_id = ?");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

    // Fetch security questions from the database
    $stmt = $pdo->prepare("SELECT question_id, question_text FROM security_questions");
    $stmt->execute();
    $securityQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //Check form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve form data
        $updateUsername = $_POST['updateUsername'];
        $updateEmail = $_POST['updateEmail'];
        $updateSecurityQuestionText = $_POST['updateSecurityQuestion'];
        $updateGender = $_POST['updateGender'];
        $updatePhoneNumber = $_POST['updatePhoneNumber'];
        $updateSecurityAnswer = $_POST['updateSecurityAnswer'];
    
        // Find the ID of the selected security question
        $selectedSecurityQuestionID = null;
        foreach ($securityQuestions as $question) {
            if ($question['question_text'] === $updateSecurityQuestionText) {
                $selectedSecurityQuestionID = $question['question_id'];
                break;
            }
        }
    
        // Check if a new file was uploaded
        if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
            // Define the directory where the uploaded file will be stored
            $uploadDirectory = 'uploads/';
            // Generate a unique filename to avoid overwriting existing files
            $filename = uniqid() . '_' . basename($_FILES['profilePicture']['name']);
            // Move the uploaded file to the destination directory
            $destination = $uploadDirectory . $filename;
            if (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $destination)) {
                // File upload successful, update the profile picture path in the form data
                $_POST['profilePicturePath'] = $destination;
            } else {
                // File upload failed
                echo "Failed to move uploaded file.";
            }
        } else {
            // No new file uploaded, retain the existing profile picture path
            $_POST['profilePicturePath'] = $user['profile_picture'];
        }
    
        // Prepare and execute SQL statement to update user information including the profile picture path
        $stmt_update = $pdo->prepare("UPDATE user SET username = ?, email = ?, security_question_id = ?, gender = ?, phone_number = ?, security_answer = ?, profile_picture = ? WHERE user_id = ?");
        $stmt_update->execute([$updateUsername, $updateEmail, $selectedSecurityQuestionID, $updateGender, $updatePhoneNumber, $updateSecurityAnswer, $_POST['profilePicturePath'], $user_id]);
    
        // Set a session variable to indicate success
        $_SESSION['update_success'] = true;
        // Pass user ID to login.php in the query string
        header("Location: register-profile.php?user_id=" . $user_id);
        exit();
    }
} else {
    // Redirect to login page or handle unauthorized access
    header("Location: login.php");
    exit(); // Stop script execution
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>My Personal Information</title>
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico"/>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <!-- Personal Information Update Form Container -->
    <div class="update-form-container">
        <!-- Personal Information Update Form -->
        <div id="updateModal" class="update-form">
            <h2>My Personal Information</h2>
            <form action="#" method="post" enctype="multipart/form-data" onsubmit="onUpdateSuccess(); return validateUpdateForm()">
                <!-- Add profile picture upload -->
                <div class="profile-picture-container">
                    <div class="profile-picture">
                        <img id="profilePreview" src="<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : '../images/profile.jpg'; ?>" alt="Profile Picture">
                        <input type="file" name="profilePicture" id="profilePicture" accept="image/*">
                    </div>
                </div>
                
                <div class="input-group">
                    <div class="input-left">
                        <label for="updateUsername">Username:</label>
                        <input type="text" name="updateUsername" id="updateUsername" value="<?php echo $user['username']; ?>" required>
                        
                        <label for="updateEmail">Email:</label>
                        <input type="email" name="updateEmail" id="updateEmail" value="<?php echo $user['email']; ?>" required>

                        <label for="updateSecurityQuestion">Security Question:</label>
                        <select name="updateSecurityQuestion" id="updateSecurityQuestion" required>
                            <?php foreach ($securityQuestions as $question) : ?>
                                <option value="<?php echo $question['question_text']; ?>" <?php echo ($user['security_question_id'] === $question['question_id']) ? 'selected' : ''; ?>><?php echo $question['question_text']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-right">
                        <label for="updateGender">Gender:</label>
                        <select name="updateGender" id="updateGender" required>
                            <option value="male" <?php echo ($user['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo ($user['gender'] === 'female') ? 'selected' : ''; ?>>Female</option>
                            <option value="rather not say" <?php echo ($user['gender'] === 'rather not say') ? 'selected' : ''; ?>>Rather not say</option>
                        </select>

                        <label for="updatePhoneNumber">Phone Number:</label>
                        <input type="tel" name="updatePhoneNumber" id="updatePhoneNumber" value="<?php echo $user['phone_number']; ?>" pattern="[0-9]{10,11}" title="Phone number must be 10 or 11 digits long" required>
                              
                        <label for="updateSecurityAnswer">Security Answer:</label>
                        <input type="text" name="updateSecurityAnswer" id="updateSecurityAnswer" value="<?php echo $user['security_answer']; ?>" required>
                    </div>
                </div>

                <button type="submit">Update Information</button>
            </form>
        </div>
    </div>


    <script>
        // Validate phone number length on form submission
        function validateUpdateForm() {
            var phoneNumberInput = document.getElementById("updatePhoneNumber");
            var phoneNumber = phoneNumberInput.value;
            if (phoneNumber.length < 10 || phoneNumber.length > 11) {
                alert("Phone number must be 10 or 11 digits long");
                return false;
            }
            return true;
        }

        // Check if the update was successful and show alert/redirect
        <?php if (isset($_SESSION['update_success'])): ?>
            alert("Personal information updated successfully!");
            alert("Welcome to Elvira True Beauty Salon! Please login again your account for complete registration.");
            window.location.href = 'login.php?user_id=<?php echo $user_id; ?>';
            <?php unset($_SESSION['update_success']); ?>
        <?php endif; ?>
    </script>
</body>
</html>