<?php
session_start();

// Check if volunteer is logged in
if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crowdsource_disaster_management";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$volunteer_id = $_SESSION['volunteer_id'];

$sql = "SELECT * FROM volunteers WHERE volt_id = '$volunteer_id'";
$result = $conn->query($sql);
$volunteer = $result->fetch_assoc();

$team_id = $volunteer['team_id'];
$sql_team = "SELECT * FROM teams WHERE team_id = '$team_id'";
$team_result = $conn->query($sql_team);
$team = $team_result->fetch_assoc();

if ($team['report_id'] == NULL) {
    echo "Your team is not assigned any report yet.";
    exit();
}

$report_id = $_GET['report_id'];

$sql_report = "SELECT * FROM reports WHERE report_id = '$report_id'";
$report_result = $conn->query($sql_report);
$report = $report_result->fetch_assoc();

$sql_resources = "SELECT * FROM resources WHERE hub_id = '{$report['hub_id']}'";
$resources_result = $conn->query($sql_resources);
$resources = $resources_result->fetch_assoc();

if (!$resources) {
    echo "No resources available for this hub.";
    exit();
}

$insufficient_resources = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $med = $_POST['med'] ?? 0;
    $liq = $_POST['liq'] ?? 0;
    $dry = $_POST['dry'] ?? 0;

    if ($resources['medicide_unit'] >= $med && $resources['liquid_unit'] >= $liq && $resources['dry_food_unit'] >= $dry) {
        $sql_update_resources = "
            UPDATE resources 
            SET medicide_unit = medicide_unit - '$med',
                liquid_unit = liquid_unit - '$liq',
                dry_food_unit = dry_food_unit - '$dry'
            WHERE hub_id = '{$report['hub_id']}'
        ";

        if ($conn->query($sql_update_resources) === TRUE) {
            echo "Resources successfully distributed!";
        } else {
            echo "Error distributing resources: " . $conn->error;
        }
    } else {
        $insufficient_resources = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribute Resources</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="dis.css">
</head>
</head>
<body>
    <h3>Distribute Resources for Report: <?php echo $report['name']; ?></h3>
    <p><strong>Disaster Type:</strong> <?php echo $report['disaster_type']; ?></p>
    <p><strong>Division:</strong> <?php echo $report['division']; ?></p>

    <form method="POST">
        <label for="med">Medicine Unit:</label>
        <input type="number" name="med" value="0" min="0" required>

        <label for="liq">Liquid Unit:</label>
        <input type="number" name="liq" value="0" min="0" required>

        <label for="dry">Dry Food Unit:</label>
        <input type="number" name="dry" value="0" min="0" required>

        <button type="submit">Distribute</button>
    </form>

    <?php if ($insufficient_resources): ?>
        <h4>Not enough supplies in your hub.</h4>
    <?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>
