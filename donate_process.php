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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $nid = $_POST['nid'] ?? '';
    $division = $_POST['division'] ?? '';
    $med = $_POST['med'] ?? 0;
    $liq = $_POST['liq'] ?? 0;
    $dry = $_POST['dry'] ?? 0;

    if (empty($name) || empty($division) || $med <= 0 && $liq <= 0 && $dry <= 0) {
        echo "Please fill in all the necessary fields!";
        exit();
    }

    $sql_hub = "SELECT hub_id FROM hub WHERE division = '$division' LIMIT 1";
    $hub_result = $conn->query($sql_hub);

    if ($hub_result->num_rows > 0) {
        $hub = $hub_result->fetch_assoc();
        $hub_id = $hub['hub_id'];

        $sql_resources = "SELECT * FROM resources WHERE hub_id = '$hub_id'";
        $resources_result = $conn->query($sql_resources);

        if ($resources_result->num_rows > 0) {
            $resources = $resources_result->fetch_assoc();

            $new_medicide_unit = $resources['medicide_unit'] + $med;
            $new_liquid_unit = $resources['liquid_unit'] + $liq;
            $new_dry_food_unit = $resources['dry_food_unit'] + $dry;

            $sql_update_resources = "
                UPDATE resources 
                SET medicide_unit = '$new_medicide_unit',
                    liquid_unit = '$new_liquid_unit',
                    dry_food_unit = '$new_dry_food_unit'
                WHERE hub_id = '$hub_id'
            ";

            if ($conn->query($sql_update_resources) === TRUE) {
                echo "Donation successfully processed!";
            } else {
                echo "Error updating resources: " . $conn->error;
            }
        } else {
            $sql_insert_resources = "
                INSERT INTO resources (hub_id, medicide_unit, liquid_unit, dry_food_unit)
                VALUES ('$hub_id', '$med', '$liq', '$dry')
            ";

            if ($conn->query($sql_insert_resources) === TRUE) {
                echo "Donation successfully processed!";
            } else {
                echo "Error inserting resources: " . $conn->error;
            }
        }
    } else {
        echo "Hub not found for the given division.";
    }
}

$conn->close();
?>
