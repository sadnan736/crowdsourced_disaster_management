<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crowdsource_disaster_management";
$conn = "" ;

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT hub_id, division, district FROM hub";
$result = $conn->query($sql);

$hubs = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $hubs[] = $row;
    }
}

$conn->close();

echo json_encode($hubs);
?>
