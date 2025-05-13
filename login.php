<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crowdsource_disaster_management";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $user_password = $_POST['password'];

    // Check if the credentials are 'admin'
    if ($user_id == 'admin' && $user_password == 'admin') {
        header("Location: http://localhost/crowdsource_disaster_management/admin.html");
        exit();
    } else {
        // Query for volunteer login
        $sql = "SELECT * FROM volunteers WHERE volt_id = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $user_id, $user_password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['volunteer_id'] = $user_id;
            header("Location: http://localhost/crowdsource_disaster_management/volunteer_home.php");
            exit();
        } else {
            header("Location: http://localhost/crowdsource_disaster_management/Login.html?error=Incorrect%20ID%20or%20Password");
            exit();
        }
        $stmt->close();
    }
}

$conn->close();
?>
