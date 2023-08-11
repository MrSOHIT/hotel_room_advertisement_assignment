<!DOCTYPE html>
<html>

<head>
    <title>Room Booking</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #ffffff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: center;
        }
        .error {
            color: red;
        }
    </style>
    <script>
        function validateForm() {
            var customerName = document.forms["bookingForm"]["customer_name"].value;
            var email = document.forms["bookingForm"]["email"].value;
            var phone = document.forms["bookingForm"]["phone"].value;
            var numPersons = document.forms["bookingForm"]["num_persons"].value;

            if (!customerName.match(/^[a-zA-Z]+\s[a-zA-Z]+$/)) {
                alert("Invalid customer name format. Please provide first and last name.");
                return false;
            }
            if (!email.includes("@")) {
                alert("Invalid email format.");
                return false;
            }
            if (!phone.match(/^04\d{8}$/)) {
                alert("Invalid phone number format. Phone number should start with 04 and have ten digits.");
                return false;
            }
            
            // Check the maximum number of persons based on room type
            var roomType = document.getElementById("room_id").value;
            var maxPersons = 0;
            if (roomType == 1 || roomType == 2) { // Standard Twin or Executive Twin
                maxPersons = 2;
            } else if (roomType == 3 || roomType == 4 || roomType == 5) { // Superior Suite, Deluxe Suite, or Executive Suite
                maxPersons = 3;
            } else if (roomType == 6) { // Presidential Suite
                maxPersons = 5;
            }
            if (numPersons > maxPersons) {
                alert("Maximum number of persons allowed for this room type is " + maxPersons);
                return false;
            }
        }
    </script>
</head>

<body>
    <?php
    if (!isset($_POST['submit'])) {
        $room_id = $_GET['room_id'];
        $check_in_date = $_GET['check_in_date'];
        ?>

<form method="post" action="" onsubmit="return validateForm();" name="bookingForm">
        <input type="hidden" name="room_id" value="<?= $room_id ?>">
        <input type="hidden" name="check_in_date" value="<?= $check_in_date ?>">
        
        <label for="customer_name">Customer Name:</label>
        <input type="text" name="customer_name" required>
        <span id="error_customer_name" class="error"></span>
        
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        <span id="error_email" class="error"></span>
        
        <label for="phone">Phone:</label>
        <input type="tel" name="phone" required>
        <span id="error_phone" class="error"></span>

        <label for="check_in_date">Check-in Date:</label>
        <input type="date" name="check_in_date" required>
        <span id="error_check_in_date" class="error"></span>
        
        <label for="num_persons">Number of Persons:</label>
        <input type="number" name="num_persons" required>
        <span id="error_num_persons" class="error"></span>

        <input type="submit" name="submit" value="Submit">
    </form>
        <?php
    } else {
        $db_host = 'localhost';
        $db_user = 'root';
        $db_pass = '';
        $db_name = 'hotel_booking';

        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $customer_name = $_POST['customer_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $check_in_date = $_POST['check_in_date'];
        $room_id = $_POST['room_id'];
        $num_persons = $_POST['num_persons'];

        if (preg_match('/^[a-zA-Z]+\s[a-zA-Z]+$/', $customer_name) &&
            filter_var($email, FILTER_VALIDATE_EMAIL) &&
            preg_match('/^04\d{8}$/', $phone) &&
            $num_persons <= 2) {

            $query = "SELECT available_rooms FROM availability WHERE room_id = ? AND date = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("is", $room_id, $check_in_date);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $available_rooms = $row['available_rooms'];

                if ($available_rooms > 0) {
                    // Update availability table and insert booking information
                    $update_query = "UPDATE availability SET available_rooms = available_rooms - 1 WHERE room_id = ? AND date = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("is", $room_id, $check_in_date);
                    $stmt->execute();

                    $insert_query = "INSERT INTO bookings (room_id, customer_name, email, phone, check_in_date, num_persons) 
                                     VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_query);
                    $stmt->bind_param("issssi", $room_id, $customer_name, $email, $phone, $check_in_date, $num_persons);

                    if ($stmt->execute()) {
                        echo "Your room has been booked successfully!<br>";
                        echo "Name: $customer_name<br>";
                        echo "Email: $email<br>";
                        echo "Phone: $phone<br>";
                        echo "Check-in Date: $check_in_date<br>";
                        echo "Room Type: $room_id<br>";
                        echo "Number of Persons: $num_persons<br>";
                    } else {
                        echo "Error: " . $stmt->error;
                    }
                } else {
                    echo "Sorry! There is no available room!";
                }
            } else {
                echo "Room availability information not found.";
            }

            $stmt->close();
        } else {
            echo "Invalid input. Please check your form entries.";
        }

        $conn->close();
    }
    ?>
</body>
</html>