<?php
require_once 'config.php';
require_once 'session_helper.php';

$username = $_SESSION['username'];
$role = $_SESSION['role'];
$facilities = $_SESSION['facilities'];
?>

<?php

// Connect to MySQL
$db = new mysqli("localhost", "root", "", "aemr");
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $technician_name = trim($_POST['tech_name'] ?? '');
    $facility_id = 50; // Hardcoded facility_id

    if (!empty($technician_name)) {
        $stmt = $db->prepare("INSERT INTO reports (technician_name, facility_id) VALUES (?, ?)");
        $stmt->bind_param("si", $technician_name, $facility_id);
        if ($stmt->execute()) {
            $success = "Technician name added to reports with facility_id 50!";
        } else {
            $error = "Database error: " . $stmt->error;
        }
    } else {
        $error = "Please enter a technician name.";
    }
}

$db->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>AEMR Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <style>
        .card { border: 1px solid #ccc; border-radius: 8px; padding: 15px; margin-bottom: 20px; box-shadow: 0 0 10px #eee; }
        .card h3 { margin: 0 0 10px 0; }
        .badge { display: inline-block; background: #007bff; color: white; padding: 5px 10px; border-radius: 12px; font-size: 12px; margin-left: 5px; }
    </style>
</head>
<body>
<?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
<!--
<h2>Welcome, <?= htmlspecialchars($username) ?>!</h2>
<p>Your role: <strong><?= htmlspecialchars($role) ?></strong></p>

<div class="card">
    <h3>Authorized Facilities</h3>
    <ul>
        <?php foreach ($facilities as $fid): ?>
            <li>Facility ID: <strong><?= htmlspecialchars($fid) ?></strong></li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="card">
    <h3>Available Actions</h3>
    <ul>
        <?php if ($role === 'admin'): ?>
            <li>✔ View/Edit/Add Reports</li>
            <li>✔ Manage Users and Facility Access</li>
            <li>✔ Full System Access</li>
        <?php elseif ($role === 'editor'): ?>
            <li>✔ View Reports</li>
            <li>✔ Edit and Submit New Reports</li>
        <?php else: ?>
            <li>✔ View Reports Only</li>
        <?php endif; ?>
    </ul>
</div>

</body>

        -->
<body>
  <div class="page-wrapper">
    <div class="global-styles w-embed">

    </div>
    <div class="shell2_wrapper">
      <?php include 'sidebar_component.php'; ?>
      <main class="shell2_main-wrapper">
        <header class="section_header46">
          <div class="padding-global">
            <div class="container-large">
              <div class="padding-section-small">
                <div class="max-width-large">
                  <h1 class="heading-style-h3">Welcome <?php echo $username; ?></h1>
                </div>
              </div>
            </div>
          </div>
        </header>
        
      </main>
    </div>
  </div>
  <script src="https://d3e54v103j8qbb.cloudfront.net/js/jquery-3.5.1.min.dc5e7f18c8.js?site=682e19ddb0ae83ddaa78f38d" type="text/javascript" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
  <script src="js/webflow.js" type="text/javascript"></script>
</body>

</html>
