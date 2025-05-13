<?php
session_start();

// Check if volunteer is logged in
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

$sql_members = "SELECT name, phone_number FROM volunteers WHERE team_id = '$team_id'";
$members_result = $conn->query($sql_members);

$division = $volunteer['hub_id'];
$sql_reports_in_area = "SELECT * FROM reports WHERE hub_id = '$division' AND team_assigned IS NULL";
$reports_in_area_result = $conn->query($sql_reports_in_area);

$sql_reports_outside_area = "SELECT * FROM reports WHERE hub_id != '$division' AND team_assigned IS NULL";
$reports_outside_area_result = $conn->query($sql_reports_outside_area);

$sql_team_report = "SELECT report_id FROM teams WHERE team_id = '$team_id' AND report_id IS NOT NULL";
$team_report_result = $conn->query($sql_team_report);
$team_report = $team_report_result->fetch_assoc();

if ($team_report) {
    $assigned_report_id = $team_report['report_id'];
    $sql_report_details = "SELECT * FROM reports WHERE report_id = '$assigned_report_id'";
    $report_details_result = $conn->query($sql_report_details);
    $assigned_report = $report_details_result->fetch_assoc();
}

if (isset($_POST['contribute'])) {
    $report_id = $_POST['report_id'];
    $sql_assign_report = "UPDATE reports SET team_assigned = '$team_id' WHERE report_id = '$report_id'";
    $conn->query($sql_assign_report);
    $sql_update_team_report = "UPDATE teams SET report_id = '$report_id' WHERE team_id = '$team_id'";
    $conn->query($sql_update_team_report);
    header("Location: volunteer_home.php");
}

if (isset($_POST['end_work'])) {
    $report_id = $_POST['report_id'];

    $sql_update_team_report = "UPDATE teams SET report_id = NULL WHERE report_id = '$report_id'";
    if ($conn->query($sql_update_team_report) === TRUE) {
        $sql_delete_report = "DELETE FROM reports WHERE report_id = '$report_id'";
        if ($conn->query($sql_delete_report) === TRUE) {
            header("Location: volunteer_home.php");
        } else {
            echo "Error deleting report: " . $conn->error;
        }
    } else {
        echo "Error removing report reference from teams table: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="dis.css">
    <style>
        .ongoing-reports {
            display: flex;
            flex-direction: column;
            margin: 20px;
            justify-content: flex-start;
            align-items: center;
            height: auto;
        }

        .report {
            margin: 10px 0;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .report form {
            margin-top: 10px;
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .ongoing-report-assigned {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar sidebarGo">
            <i class="fa fa-close" id="close"></i>
            <nav>
                <ul>
                    <li><a href="http://localhost/crowdsource_disaster_management/index.html">Home</a></li>
                    <li><a href="http://localhost/crowdsource_disaster_management/services.html">Services</a></li>
                    <li><a href="http://localhost/crowdsource_disaster_management/hub.html">Hub Details</a></li>
                    <li><a href="http://localhost/crowdsource_disaster_management/about.html">About</a></li>
                    <li><a href="http://localhost/crowdsource_disaster_management/contact.html">Contact</a></li>
                    <li><a href="http://localhost/crowdsource_disaster_management/donate.html">Donate Here</a></li>
                </ul>
            </nav>
        </div>

        <div class="main">
            <i class="fa fa-bars" id="menu"></i>

            <div class="info">
                <div class="pic"></div>
                <div class="devinfo">
                    <h2>Welcome, <?php echo $volunteer['name']; ?></h2>
                    <p><strong>Phone Number:</strong> <?php echo $volunteer['phone_number']; ?></p>
                    <h3>Your Team: <?php echo $team['team_name']; ?></h3>
                    <h4>Team Members:</h4>
                    <ul>
                        <?php while($member = $members_result->fetch_assoc()): ?>
                            <li><?php echo $member['name']; ?> - <?php echo $member['phone_number']; ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>

            <div class="ongoing-reports">
                <h3>Ongoing Reports from Your Area</h3>
                <?php while($report = $reports_in_area_result->fetch_assoc()): ?>
                    <div class="report">
                        <p><strong>Report:</strong> <?php echo $report['name']; ?></p>
                        <?php if (!$team_report): ?>
                            <form method="POST">
                                <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                <button type="submit" name="contribute">Contribute Now</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="ongoing-reports">
                <h3>Ongoing Reports Outside Your Area</h3>
                <?php while($report = $reports_outside_area_result->fetch_assoc()): ?>
                    <div class="report">
                        <p><strong>Report:</strong> <?php echo $report['name']; ?></p>
                        <?php if (!$team_report): ?>
                            <form method="POST">
                                <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                <button type="submit" name="contribute">Contribute Now</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($team_report): ?>
                <div class="ongoing-report-assigned">
                    <h3>Your Assigned Report: <?php echo $assigned_report['name']; ?></h3>
                    <form method="POST">
                        <input type="hidden" name="report_id" value="<?php echo $assigned_report['report_id']; ?>">
                        <button type="submit" name="end_work">End Volunteer Work</button>
                    </form>
                    <form action="distribute_res.php" method="GET">
                        <input type="hidden" name="report_id" value="<?php echo $assigned_report['report_id']; ?>">
                        <button type="submit">Distribute Resources</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="dis.js"></script>
</body>
</html>
<?php
$conn->close();
?>
