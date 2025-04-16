<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

include 'config.php';

include 'session-termination.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$appointment_id = $_GET['appointment_id'] ?? null;

if ($appointment_id) {
    $stmt = $pdo->prepare("SELECT a.*, u.username AS staff_name, s.service_name 
                           FROM appointment a
                           LEFT JOIN user u ON a.staff_id = u.user_id
                           LEFT JOIN service s ON a.service_id = s.service_id
                           WHERE a.appointment_id = ?");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($appointment) {
        $staff_id = $appointment['staff_id'];
        $service_id = $appointment['service_id'];
        
        // Fetch staff availability for the service
        $availability_stmt = $pdo->prepare("SELECT available_start_date, available_end_date 
                                            FROM staff_availability 
                                            WHERE staff_id = ? AND service_id = ?");
        $availability_stmt->execute([$staff_id, $service_id]);
        $availability = $availability_stmt->fetch(PDO::FETCH_ASSOC);
        
        $available_start_date = $availability['available_start_date'];
        $available_end_date = $availability['available_end_date'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Reschedule Appointment</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.js"></script>
    <link rel="icon" type="image/x-icon" href="favicon/favicon.ico"/>
    <link rel="stylesheet" href="css/home.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Dancing+Script&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-...." crossorigin="anonymous" />
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>

    <div class="reschedule-form-container">

        <div id="rescheduleModal" class="reschedule-form">
            <span class="close-btn" onclick="closeRescheduleForm()">&times;</span>
            <h2>Reschedule Appointment</h2>
            <?php if ($appointment): ?>
                <form action="customer-update-reschedule.php" method="post" onsubmit="return validateRescheduleForm()">
                    <!-- Hidden input for appointment ID -->
                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">

                    <!-- Display Service -->
                    <div class="appointment-info">
                        <label for="selectedService">Service:</label>
                        <input type="text" id="selectedService" name="selectedService" value="<?php echo htmlspecialchars($appointment['service_name']); ?>" disabled>
                    </div>

                    <!-- Display Staff -->
                    <div class="appointment-info">
                        <label for="selectedStaff">Staff:</label>
                        <input type="text" id="selectedStaff" name="selectedStaff" value="<?php echo htmlspecialchars($appointment['staff_name']); ?>" disabled>
                    </div>

                    <!-- Date and Time Container -->
                    <div class="datetime-container">
                        <div class="date-container">
                            <label for="rescheduleDate">Appointment Date:</label>
                            <input type="text" name="rescheduleDate" id="rescheduleDate" value="<?php echo htmlspecialchars($appointment['appointment_date']); ?>" required>
                        </div>
                        <div class="time-container">
                            <label for="rescheduleTime">Appointment Time:</label>
                            <input type="text" name="rescheduleTime" id="rescheduleTime" value="<?php echo htmlspecialchars($appointment['appointment_time']); ?>" required>
                        </div>
                    </div>

                    <button type="submit">Reschedule Appointment</button>
                </form>
            <?php else: ?>
                <p>Invalid appointment ID.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Function to close the reschedule form
        function closeRescheduleForm() {
            window.history.back();
        }

        $(document).ready(function() {
            var availableStartDay = <?php echo json_encode($available_start_date); ?>;
            var availableEndDay = <?php echo json_encode($available_end_date); ?>;

            // Convert PHP date strings to JavaScript Date objects
            var startDate = new Date(Date.parse(availableStartDay));
            var endDate = new Date(Date.parse(availableEndDay));
            
            // Get the current date
            var currentDate = new Date();
            
            // Ensure minDate is the later of the startDate or currentDate
            var minDate = startDate > currentDate ? startDate : currentDate;
            
            // Subtract one day from the start date to include the day before
            minDate.setDate(minDate.getDate() - 1);

            // Set the date picker with proper minDate and maxDate
            $('#rescheduleDate').datepicker({
                dateFormat: 'yy-mm-dd',
                minDate: minDate,
                maxDate: endDate,
                beforeShowDay: function(date) {
                    var day = date.getDay(); // Get the day of the week (0 - Sunday, 1 - Monday, ..., 6 - Saturday)
                    
                    // Disable Wednesdays
                    if (day === 3) {
                        return [false];
                    }

                    // Check if date is within the available range
                    if (date >= minDate && date <= endDate) {
                        return [true];
                    }
                    return [false];
                }
            });

            $('#rescheduleTime').timepicker({
                timeFormat: 'h:i A', // 12-hour format with AM/PM
                step: 15, // 15-minute intervals
                minTime: '11:00 AM',
                maxTime: '6:00 PM'
            });
        });
    </script>


</body>
</html>
