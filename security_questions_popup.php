<?php
// Include your PDO connection here
include 'config.php';

// Check if the user ID is provided in the query parameters
if (isset($_GET['user_id'])) {
    $userID = $_GET['user_id'];
    
    // Fetch the user's security question from the database
    $stmt = $pdo->prepare("SELECT security_question_id FROM user WHERE user_id = ?");
    $stmt->execute([$userID]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If the user's security question exists
    if ($user) {
        // Fetch all security questions from the database
        $stmt = $pdo->prepare("SELECT question_id, question_text FROM security_questions");
        $stmt->execute();
        $securityQuestions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch the user's security question text
        $userQuestionID = $user['security_question_id'];
        $stmtUserQuestion = $pdo->prepare("SELECT question_text FROM security_questions WHERE question_id = ?");
        $stmtUserQuestion->execute([$userQuestionID]);
        $userQuestion = $stmtUserQuestion->fetch(PDO::FETCH_ASSOC);
    } else {
        // Redirect or handle error (user not found or security question not set)
        exit("User not found or security question not set.");
    }
} else {
    // Redirect or handle error (user ID not provided)
    exit("User ID not provided.");
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the submitted security question ID and answer
    $questionID = $_POST['question'];
    $answer = $_POST['answer'];

    // Fetch the correct security answer from the database
    $stmt = $pdo->prepare("SELECT security_answer, security_question_id FROM user WHERE user_id = ?");
    $stmt->execute([$userID]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the submitted answer matches the correct answer
    $result = 'not match';
    if ($user && $user['security_answer'] === $answer && $user['security_question_id'] == $questionID) {
        // If the answers match, set result to 'match'
        $result = 'match';
    }

    // Output JavaScript to send the result to the main window and close the popup
    echo "<script>
        window.opener.handleSecurityAnswer('$result');
        window.close();
    </script>";
    exit; // Terminate script execution after outputting the JavaScript
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Security Questions</title>
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Titillium+Web&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>

    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Titillium Web', sans-serif;
            font-size: 16px;
            background-color: honeydew; /* Light pastel peach background color */
        }

        form {
            background-color: #FFFCF9; 
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: teal; 
        }

        form select,
        form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid teal; 
            border-radius: 4px;
            background-color: snow; 
            color: teal;
        }

        form button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: teal; 
            color: snow;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        form button:hover {
            background-color: #A3C4BC; /* Slightly lighter pastel color for the button on hover */
            color: teal; /* Pastel teal color for the text on hover */
            border: 1px solid #A3C4BC; /* Pastel teal border color on hover */
        }
    </style>
</head>
<body>
    <!-- Your HTML content for the security question form goes here -->
    <form id="securityQuestionForm" method="POST" action="">
        <label for="question">Select a Security Question:</label>
        <select id="question" name="question" required>
            <option value="">Select a question...</option>
            <?php foreach ($securityQuestions as $question): ?>
                <option value="<?php echo $question['question_id']; ?>"><?php echo $question['question_text']; ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        <label for="answer">Answer:</label>
        <input style="width: 94%;" type="text" id="answer" name="answer" required>
        <br>
        <button type="submit">Submit</button>
    </form>

    <!-- JavaScript to set the hidden input field with the selected question ID -->
    <script>
        document.getElementById("question").addEventListener("change", function() {
            var questionID = this.value;
            document.getElementById("question_id").value = questionID;
        });
    </script>
</body>
</html>
