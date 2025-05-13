<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crowdsource_disaster_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $_POST['name'];
$number = $_POST['number'];
$disaster_type = $_POST['disaster_type'];
$division = $_POST['division'];

$name = $conn->real_escape_string($name);
$number = $conn->real_escape_string($number);
$disaster_type = $conn->real_escape_string($disaster_type);
$division = $conn->real_escape_string($division);

$sql_hub = "SELECT hub_id FROM hub WHERE division = ? LIMIT 1";
$stmt_hub = $conn->prepare($sql_hub);
$stmt_hub->bind_param("s", $division);
$stmt_hub->execute();
$result_hub = $stmt_hub->get_result();

if ($result_hub->num_rows > 0) {
    $hub = $result_hub->fetch_assoc();
    $hub_id = $hub['hub_id'];
} else {
    echo "No hub found for the given division.";
    exit();
}

$sql_insert = "INSERT INTO reports (name, number, division, hub_id, disaster_type) 
               VALUES (?, ?, ?, ?, ?)";

$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("sssss", $name, $number, $division, $hub_id, $disaster_type);

if ($stmt_insert->execute()) {
    echo "Report submitted successfully.";
} else {
    echo "Error: " . $stmt_insert->error;
}

$stmt_insert->close();
$conn->close();
?>
