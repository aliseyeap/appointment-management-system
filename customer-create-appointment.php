<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

include 'config.php';

include 'session-termination.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all services from the database
$services_stmt = $pdo->query("SELECT * FROM service");
$services = $services_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all staff with their available services and availability dates
$staff_stmt = $pdo->query("SELECT u.user_id, u.username, sa.service_id, sa.available_start_date, sa.available_end_date
                           FROM user u
                           JOIN staff_availability sa ON u.user_id = sa.staff_id
                           WHERE u.role = 'staff'");
$staff = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Make An Appointment</title>
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
            <h2>Make An Appointment</h2>
            <form action="customer-add-appointment.php" method="post" onsubmit="return validateAppointmentForm()">
                <!-- Display Service -->
                <div class="appointment-info">
                    <label for="selectedService">Service:</label>
                    <select id="selectedService" name="selectedService" required>
                        <option value="">Select Service</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?php echo $service['service_id']; ?>"><?php echo htmlspecialchars($service['service_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Display Staff -->
                <div class="appointment-info">
                    <label for="selectedStaff">Staff:</label>
                    <select id="selectedStaff" name="selectedStaff" required>
                        <option value="">Select Staff</option>
                    </select>
                </div>

                <!-- Date and Time Container -->
                <div class="datetime-container">
                    <div class="date-container">
                        <label for="appointmentDate">Appointment Date:</label>
                        <input type="text" name="appointmentDate" id="appointmentDate" placeholder="dd-mm-yyyy" required>
                    </div>
                    <div class="time-container">
                        <label for="appointmentTime">Appointment Time:</label>
                        <input type="text" name="appointmentTime" id="appointmentTime" placeholder="hh:mm" required>
                    </div>
                </div>

                <button type="submit">Make Appointment</button>
            </form>
        </div>
    </div>

    <script>
        // Function to close the reschedule form
        function closeRescheduleForm() {
            window.history.back();
        }

        $(document).ready(function() {
            // Datepicker initialization
            $('#appointmentDate').datepicker({
                dateFormat: 'yy-mm-dd',
                minDate: 0, // Minimum date is today
                beforeShowDay: function(date) {
                    return [date.getDay() !== 3]; // Disable Wednesdays
                }
            });

            // Timepicker initialization
            $('#appointmentTime').timepicker({
                timeFormat: 'h:i A', // 12-hour format with AM/PM
                step: 15, // 15-minute intervals
                minTime: '11:00 AM',
                maxTime: '6:00 PM'
            });

            // Staff data from PHP to JavaScript
            const staffData = <?php echo json_encode($staff); ?>;

            // Update staff options based on selected service
            $('#selectedService').change(function() {
                const selectedService = $(this).val();
                const staffSelect = $('#selectedStaff');
                staffSelect.empty();
                staffSelect.append('<option value="">Select Staff</option>');

                staffData.forEach(function(staff) {
                    if (staff.service_id == selectedService) {
                        staffSelect.append('<option value="' + staff.user_id + '" data-start-date="' + staff.available_start_date + '" data-end-date="' + staff.available_end_date + '">' + staff.username + '</option>');
                    }
                });
            });

            // Update datepicker based on selected staff
            $('#selectedStaff').change(function() {
                const selectedStaff = $(this).find(':selected');
                const startDate = new Date(Date.parse(selectedStaff.data('start-date')));
                const endDate = new Date(Date.parse(selectedStaff.data('end-date')));
                const currentDate = new Date();

                // Ensure minDate is the later of the startDate or currentDate
                const minDate = startDate > currentDate ? startDate : currentDate;
                
                $('#appointmentDate').datepicker('option', 'minDate', minDate);
                $('#appointmentDate').datepicker('option', 'maxDate', endDate);
            });
        });
    </script>
</body>
</html>

