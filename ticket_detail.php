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

$username = htmlspecialchars($username);
$role = htmlspecialchars($role);
$facilities = array_map('htmlspecialchars', $facilities);


switch (strtolower($role)) {
    case 'admin':
        $permissions = "View, edit, and add reports; manage users and facility access; full system access.";
        break;
    case 'editor':
        $permissions = "View reports; edit and submit new reports.";
        break;
    default:
        $permissions = "View reports only.";
        break;
}

// List facilities as a comma-separated string
$facilityList = implode(', ', $facilities);

//get facility names
$facilityNames = [];

if (!empty($facilityList)) {
    // Convert comma-separated string to array
    $facilityIds = explode(',', $facilityList);

    // Sanitize and prepare placeholders
    $placeholders = implode(',', array_fill(0, count($facilityIds), '?'));

    $stmt = $db->prepare("SELECT name FROM facilities WHERE id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($facilityIds)), ...$facilityIds);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $facilityNames[] = $row['name'];
    }

    $stmt->close();
}


//sql for charts
$placeholders = implode(',', array_fill(0, count($facilities), '?'));


$db->close();
?>

<!DOCTYPE html><!--  This site was created in Webflow. https://webflow.com  --><!--  Last Published: Fri May 23 2025 18:26:00 GMT+0000 (Coordinated Universal Time)  -->
<html>
<head>
  <meta charset="utf-8">
  <title>Ticket Detail</title>
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <link href="css/normalize.css" rel="stylesheet" type="text/css">
  <link href="css/webflow.css" rel="stylesheet" type="text/css">
  <link href="css/aemr.webflow.css" rel="stylesheet" type="text/css">
  <script type="text/javascript">!function(o,c){var n=c.documentElement,t=" w-mod-";n.className+=t+"js",("ontouchstart"in o||o.DocumentTouch&&c instanceof DocumentTouch)&&(n.className+=t+"touch")}(window,document);</script>
  <link href="images/favicon.png" rel="shortcut icon" type="image/x-icon">
  <link href="images/webclip.png" rel="apple-touch-icon"><!--  Keep this css code to improve the font quality -->
  <style>
  * {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  -o-font-smoothing: antialiased;
}
</style>
</head>
<body>
  <div class="page-wrapper">
    <div class="shell_wrapper">
      <?php include 'sidebar_component.php'; ?>
      <main class="shell_main-wrapper">
        <header class="section_header">
          <div class="padding-global">
            <div class="container-large">
              <div class="padding-section-small">
                <div class="max-width-large">
                  <h1 class="heading-style-h3">Ticket #</h1>
                </div>
              </div>
            </div>
          </div>
        </header>
        <div class="section_shell-layout">
          <div class="padding-global">
            <div class="container-large">
              <div id="w-node-_27b25744-048c-b6db-6354-5ae213b547ae-b5f708c9" class="w-layout-grid shell-layout_component">
                
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
  <script src="https://d3e54v103j8qbb.cloudfront.net/js/jquery-3.5.1.min.dc5e7f18c8.js?site=682e19ddb0ae83ddaa78f38d" type="text/javascript" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
  <script src="js/webflow.js" type="text/javascript"></script>



</body>
</html>