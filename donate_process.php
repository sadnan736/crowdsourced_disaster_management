<?php
session_start();

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.html");
    exit();
}

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // If not set, default to 0
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

if (isset($_POST['source']) && $_POST['source'] == 'other_hubs') {
    $total_needed_med = $_POST['med'] ?? 0;
    $total_needed_liq = $_POST['liq'] ?? 0;
    $total_needed_dry = $_POST['dry'] ?? 0;

    $sql_other_hubs = "
        SELECT * FROM resources WHERE hub_id != '{$report['hub_id']}' AND hub_id IN (
            SELECT hub_id FROM hub WHERE division = '{$report['division']}'
        )
    ";

    $other_hubs_result = $conn->query($sql_other_hubs);

    while ($other_hub = $other_hubs_result->fetch_assoc()) {
        if ($total_needed_med > 0) {
            $med_taken = min($total_needed_med, $other_hub['medicide_unit']);
            $total_needed_med -= $med_taken;
            $other_hub['medicide_unit'] -= $med_taken;
        }

        if ($total_needed_liq > 0) {
            $liq_taken = min($total_needed_liq, $other_hub['liquid_unit']);
            $total_needed_liq -= $liq_taken;
            $other_hub['liquid_unit'] -= $liq_taken;
        }

        if ($total_needed_dry > 0) {
            $dry_taken = min($total_needed_dry, $other_hub['dry_food_unit']);
            $total_needed_dry -= $dry_taken;
            $other_hub['dry_food_unit'] -= $dry_taken;
        }

        $sql_update_other_hub = "
            UPDATE resources
            SET medicide_unit = {$other_hub['medicide_unit']},
                liquid_unit = {$other_hub['liquid_unit']},
                dry_food_unit = {$other_hub['dry_food_unit']}
            WHERE hub_id = '{$other_hub['hub_id']}'
        ";

        $conn->query($sql_update_other_hub);

        if ($total_needed_med <= 0 && $total_needed_liq <= 0 && $total_needed_dry <= 0) {
            break;
        }
    }

    if ($total_needed_med <= 0 && $total_needed_liq <= 0 && $total_needed_dry <= 0) {
        echo "Resources successfully sourced and distributed!";
    } else {
        echo "Failed to source enough resources from other hubs.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribute Resources</title>
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

    <?php if (isset($insufficient_resources) && $insufficient_resources): ?>
        <h4>Not enough supplies in your hub. Do you want to source from other hubs?</h4>
        <form method="POST">
            <input type="hidden" name="source" value="other_hubs">
            <button type="submit">Yes</button>
        </form>
        <form method="POST">
            <input type="hidden" name="source" value="no">
            <button type="submit">No</button>
        </form>
    <?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>
