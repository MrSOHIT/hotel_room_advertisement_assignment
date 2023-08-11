<!DOCTYPE html>
<html>
<head>
    <title>Room Availability</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
        <style>
        .availability-container {
    width: 80%;
    margin: 0 auto;
    padding: 20px;
}

.availability-entry {
    border: 1px solid #ccc;
    padding: 10px;
    margin-bottom: 10px;
    background-color: #f7f7f7;
}

.availability-entry p {
    margin: 5px 0;
}

.available {
    color: green;
}

.booked {
    color: red;
}

.book-button, .waiting-button {
    display: inline-block;
    padding: 5px 10px;
    background-color: #333;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
    margin-top: 5px;
}

.waiting-button {
    background-color: #ff6600;
}

    </style>
</head>
<body>
    <div class="availability-container">
        <?php
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $room_id = $_GET['room_id'];
        $year = 2022;
        $month = 8;
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $db_host = 'localhost';
        $db_user = 'root';
        $db_pass = '';
        $db_name = 'hotel_booking';

        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        for ($day = 1; $day <= $days_in_month; $day++) {
            $date_to_check = sprintf('%04d-%02d-%02d', $year, $month, $day);

            $query = "SELECT COUNT(*) AS occupied_rooms FROM bookings WHERE room_id = $room_id AND check_in_date = '$date_to_check'";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $occupied_rooms = $row['occupied_rooms'];

                echo "<div class='availability-entry'>";
                echo "<p>Date: $date_to_check</p>";
                if ($occupied_rooms < 2) {
                    echo "<p class='available'>Room is available</p>";
                    echo "<a class='book-button' href='booking.php?room_id=$room_id&check_in_date=$date_to_check'>Book Now</a>";
                } else {
                    echo "<p class='booked'>All rooms are booked</p>";
                    echo "<a class='waiting-button' href='booking.php?room_id=$room_id&check_in_date=$date_to_check'>Join Waiting List</a>";
                }
                echo "</div>";
            } else {
                echo "<p>Availability information not found for $date_to_check.</p>";
            }
        }

        $conn->close();
        ?>
    </div>
</body>
</html>