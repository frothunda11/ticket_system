<?php
require_once 'config.php';
require_once 'session_helper.php';
require_once 'db_connection.php';

//update user facilities
function getUserFacilities($username, $db) {
    $facilities = [];
    $stmt = $db->prepare("
        SELECT facility_id 
        FROM user_facility_map 
        WHERE username = ?
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $facilities[] = $row['facility_id'];
    }
    $stmt->close();
    return $facilities;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username      = trim($_POST['Username'] ?? '');
    $facility_ids  = $_POST['Facilities'] ?? [];

    if (!empty($username) && !empty($facility_ids)) {
        try {
            if (isset($_POST['save'])) {
                // Insert user if doesn't exist
                $stmt1 = $db->prepare("INSERT IGNORE INTO users (username) VALUES (?)");
                $stmt1->bind_param("s", $username);
                $stmt1->execute();
                $stmt1->close();

                // Map user to selected facilities
                $stmt2 = $db->prepare("INSERT IGNORE INTO user_facility_map (username, facility_id) VALUES (?, ?)");
                foreach ($facility_ids as $fid) {
                    $fid = intval($fid);
                    $stmt2->bind_param("si", $username, $fid);
                    $stmt2->execute();
                }
                $stmt2->close();

                // ✅ Refresh session if this is the logged-in user
                if (isset($_SESSION['username']) && $_SESSION['username'] === $username) {
                    $_SESSION['facilities'] = getUserFacilities($username, $db);
                }

                $success = "User successfully mapped to selected facilities.";
            }

            if (isset($_POST['remove'])) {
                // Remove user-facility mappings
                $stmt3 = $db->prepare("DELETE FROM user_facility_map WHERE username = ? AND facility_id = ?");
                foreach ($facility_ids as $fid) {
                    $fid = intval($fid);
                    $stmt3->bind_param("si", $username, $fid);
                    $stmt3->execute();
                }
                $stmt3->close();

                // ✅ Refresh session if this is the logged-in user
                if (isset($_SESSION['username']) && $_SESSION['username'] === $username) {
                    $_SESSION['facilities'] = getUserFacilities($username, $db);
                }

                $success = "Selected facilities removed for user.";
            }

        } catch (mysqli_sql_exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in both Username and select at least one Facility.";
    }
}



//fetch facilities into array
$facility_options_array = [];
$query = $db->query("SELECT id, name FROM facilities ORDER BY name");
while ($row = $query->fetch_assoc()) {
    $facility_options_array[] = $row;
}

// Fetch facilities for dropdown
$facility_options = '';
$result = $db->query("SELECT id, name FROM facilities ORDER BY name");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = htmlspecialchars($row['id']);
        $name = htmlspecialchars($row['name']);
        $facility_options .= "<option value=\"$id\">$name</option>\n";
    }
} else {
    $facility_options = '<option disabled>No facilities found</option>';
}

// Fetch user-facility mappings with facility names to display table
if (isset($_GET['ajax']) && $_GET['ajax'] === 'user_mappings') {
    require 'db_connection.php'; // or skip if it's already included

    $username = trim($_GET['username'] ?? '');

    $column_labels = [
      'username' => 'Username',
      'facility_name' => 'Facility Name'
    ];

    $query = "
        SELECT 
            ufm.username,
            f.name AS facility_name
        FROM 
            user_facility_map ufm
        JOIN 
            facilities f ON ufm.facility_id = f.id
    ";

    if (!empty($username)) {
        $query .= " WHERE ufm.username = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $query .= " ORDER BY ufm.id DESC";
        $result = $db->query($query);
    }

    if ($result && $result->num_rows > 0) {
        echo '<table class="table_table">';
        echo '<thead class="table_head"><tr class="table_row">';
        echo '<th class="table_header">Username</th><th class="table_header">Facility Name</th>';
        echo '</tr></thead><tbody>';

        while ($row = $result->fetch_assoc()) {
            echo '<tr class="table_row">';
            echo '<td class="table_cell" data-label="Username">' . htmlspecialchars($row['username']) . '</td>';
            echo '<td class="table_cell" data-label="Facility Name">' . htmlspecialchars($row['facility_name']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No records found.</p>';
    }
    exit;
}

// ✅ Only run this if it's NOT an AJAX request
if (!isset($_GET['ajax'])) {
    $reports = [];

    $query = "
        SELECT 
            ufm.username,
            f.name AS facility_name
        FROM 
            user_facility_map ufm
        JOIN 
            facilities f ON ufm.facility_id = f.id
        ORDER BY 
            ufm.id DESC
    ";

    $result = $db->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $reports[] = $row;
        }
        $result->free();
    }
}



$db->close();
?>

<!DOCTYPE html><!--  This site was created in Webflow. https://webflow.com  --><!--  Last Published: Fri May 23 2025 18:26:00 GMT+0000 (Coordinated Universal Time)  -->
<html data-wf-page="6830b6838485e8a9e075884f" data-wf-site="682e19ddb0ae83ddaa78f38d">
<head>
  <meta charset="utf-8">
  <title>Users</title>
  <meta content="Cisterns" property="og:title">
  <meta content="Cisterns" property="twitter:title">
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <meta content="Webflow" name="generator">
  <link href="css/normalize.css" rel="stylesheet" type="text/css">
  <link href="css/webflow.css" rel="stylesheet" type="text/css">
  <link href="css/tables.css" rel="stylesheet" type="text/css">
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
    <div class="global-styles w-embed">
      <style>
/* Set color style to inherit */
.inherit-color * {
    color: inherit;
}
/* Focus state style for keyboard navigation for the focusable elements */
*[tabindex]:focus-visible,
  input[type="file"]:focus-visible {
   outline: 0.125rem solid #4d65ff;
   outline-offset: 0.125rem;
}
/* Get rid of top margin on first element in any rich text element */
.w-richtext > :not(div):first-child, .w-richtext > div:first-child > :first-child {
  margin-top: 0 !important;
}
/* Get rid of bottom margin on last element in any rich text element */
.w-richtext>:last-child, .w-richtext ol li:last-child, .w-richtext ul li:last-child {
	margin-bottom: 0 !important;
}
/* Prevent all click and hover interaction with an element */
.pointer-events-off {
	pointer-events: none;
}
/* Enables all click and hover interaction with an element */
.pointer-events-on {
  pointer-events: auto;
}
/* Create a class of .div-square which maintains a 1:1 dimension of a div */
.div-square::after {
	content: "";
	display: block;
	padding-bottom: 100%;
}
/* Make sure containers never lose their center alignment */
.container-medium,.container-small, .container-large {
	margin-right: auto !important;
  margin-left: auto !important;
}
/* 
Make the following elements inherit typography styles from the parent and not have hardcoded values. 
Important: You will not be able to style for example "All Links" in Designer with this CSS applied.
Uncomment this CSS to use it in the project. Leave this message for future hand-off.
*/
/*
a,
.w-input,
.w-select,
.w-tab-link,
.w-nav-link,
.w-dropdown-btn,
.w-dropdown-toggle,
.w-dropdown-link {
  color: inherit;
  text-decoration: inherit;
  font-size: inherit;
}
*/
/* Apply "..." after 3 lines of text */
.text-style-3lines {
	display: -webkit-box;
	overflow: hidden;
	-webkit-line-clamp: 3;
	-webkit-box-orient: vertical;
}
/* Apply "..." after 2 lines of text */
.text-style-2lines {
	display: -webkit-box;
	overflow: hidden;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical;
}
/* Adds inline flex display */
.display-inlineflex {
  display: inline-flex;
}
/* These classes are never overwritten */
.hide {
  display: none !important;
}
@media screen and (max-width: 991px) {
    .hide, .hide-tablet {
        display: none !important;
    }
}
  @media screen and (max-width: 767px) {
    .hide-mobile-landscape{
      display: none !important;
    }
}
  @media screen and (max-width: 479px) {
    .hide-mobile{
      display: none !important;
    }
}
.margin-0 {
  margin: 0rem !important;
}
.padding-0 {
  padding: 0rem !important;
}
.spacing-clean {
padding: 0rem !important;
margin: 0rem !important;
}
.margin-top {
  margin-right: 0rem !important;
  margin-bottom: 0rem !important;
  margin-left: 0rem !important;
}
.padding-top {
  padding-right: 0rem !important;
  padding-bottom: 0rem !important;
  padding-left: 0rem !important;
}
.margin-right {
  margin-top: 0rem !important;
  margin-bottom: 0rem !important;
  margin-left: 0rem !important;
}
.padding-right {
  padding-top: 0rem !important;
  padding-bottom: 0rem !important;
  padding-left: 0rem !important;
}
.margin-bottom {
  margin-top: 0rem !important;
  margin-right: 0rem !important;
  margin-left: 0rem !important;
}
.padding-bottom {
  padding-top: 0rem !important;
  padding-right: 0rem !important;
  padding-left: 0rem !important;
}
.margin-left {
  margin-top: 0rem !important;
  margin-right: 0rem !important;
  margin-bottom: 0rem !important;
}
.padding-left {
  padding-top: 0rem !important;
  padding-right: 0rem !important;
  padding-bottom: 0rem !important;
}
.margin-horizontal {
  margin-top: 0rem !important;
  margin-bottom: 0rem !important;
}
.padding-horizontal {
  padding-top: 0rem !important;
  padding-bottom: 0rem !important;
}
.margin-vertical {
  margin-right: 0rem !important;
  margin-left: 0rem !important;
}
.padding-vertical {
  padding-right: 0rem !important;
  padding-left: 0rem !important;
}
/* Apply "..." at 100% width */
.truncate-width { 
		width: 100%; 
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
}
/* Removes native scrollbar */
.no-scrollbar {
    -ms-overflow-style: none;
    overflow: -moz-scrollbars-none; 
}
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
</style>
    </div>
    <div class="shell_wrapper">
      <?php include 'sidebar_component.php'; ?>
      <main class="shell_main-wrapper">
        <header class="section_header">
          <div class="padding-global">
            <div class="container-large">
              <div class="padding-section-small">
                <h1 class="heading-style-h3">Add Users</h1>
                <?php if (!empty($success) || !empty($error)): ?>
                  <div id="msg" class="<?= !empty($success) ? 'success-message' : 'error-message' ?>">
                    <?= !empty($success) ? htmlspecialchars($success) : htmlspecialchars($error) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </header>
        <div class="section_shell-layout">
          <div class="padding-global">
            <div class="container-large">
              <div id="w-node-_27b25744-048c-b6db-6354-5ae213b547ae-e075884f" class="w-layout-grid shell-layout_component">
                <div class="form-block">
                  <form method="POST" name="Cisterns" class="form" >
                    <div class="form_2col">
                      <div class="form-field-wrapper">
                        <label for="Username" class="field-label">Username</label>
                        <input id="username-search" class="form-input" required maxlength="50" name="Username"  type="text">
                      </div>
                    </div>
                      <div>
                        <div class="form-field-wrapper">
                          <div class="checkbox-group">
                            <!-- Select All Checkbox -->
                          <label style="display: block; margin-bottom: 6px;">
                            <input type="checkbox" id="select-all-facilities"> SELECT ALL
                          </label>
                              <?php foreach ($facility_options_array as $facility): ?>
                                <label style="display: block; margin-bottom: 4px;">
                                  <input 
                                    type="checkbox" 
                                    name="Facilities[]" 
                                    class="facility-checkbox"
                                    value="<?= htmlspecialchars($facility['id']) ?>">
                                  <?= htmlspecialchars($facility['name']) ?>
                                </label>
                              <?php endforeach; ?>
                            </div>
                          </div>
                        </div>
                        <div class="button-group">
                          <button class="button" type="submit" name="save">Save</button>
                          <button class="button delete-button" type="submit" name="remove">Remove</button>
                        </div>
                      </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <div class="padding-global">
            <div class="container-large">
              <div class="spacer-medium"></div>
              <h2 class="heading-style-h5">All Users</h2>
              <div class="spacer-xsmall"></div>
              <?php
              //map user friendly names in table titles
              $reports = $reports ?? [];
              $column_labels = [
                  'username' => 'Username',
                  'facility_name' => 'Facility Name'
              ];
              ?>
              <div class="table_instance" id="user-mapping-table"> 
                <table class="table_table">
                  <thead class="table_head">
                    <tr class="table_row">
                      <?php if (!empty($reports)): ?>
                        <?php foreach (array_keys($reports[0]) as $column): ?>
                          <th class="table_header"><?= htmlspecialchars($column_labels[$column] ?? $column) ?></th>
                        <?php endforeach; ?>
                      <?php endif; ?>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($reports as $row): ?>
                      <tr class="table_row">
                        <?php foreach ($row as $key => $value): ?>
                          <td class="table_cell" data-label="<?= htmlspecialchars($column_labels[$key] ?? $key) ?>">
                            <?= htmlspecialchars($value) ?>
                          </td>
                        <?php endforeach; ?>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
              <div class="spacer-xlarge"></div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
  <script src="https://d3e54v103j8qbb.cloudfront.net/js/jquery-3.5.1.min.dc5e7f18c8.js?site=682e19ddb0ae83ddaa78f38d" type="text/javascript" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
  <script src="js/webflow.js" type="text/javascript"></script>
  <script>
  //fade out success or error message when saving data
  window.addEventListener('DOMContentLoaded', () => {
    const msg = document.getElementById('msg');
    if (msg) {
      // After 5 seconds, start fade out
      setTimeout(() => {
        msg.classList.add('fade-out');
      }, 5000);

      // Optionally, after fade out completes, remove element from DOM
      setTimeout(() => {
        if (msg.parentNode) {
          msg.parentNode.removeChild(msg);
        }
      }, 6000); // 1s after fade out starts
    }
  });
</script>
<script>
  //update table live with search input
document.getElementById('username-search').addEventListener('input', function () {
    const username = this.value;

    fetch('<?= basename($_SERVER["PHP_SELF"]) ?>?ajax=user_mappings&username=' + encodeURIComponent(username))
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            document.getElementById('user-mapping-table').innerHTML = html;
        })
        .catch(error => {
            console.error('Fetch error:', error);
        });
});
</script>

<script>
  //toggle all checkboxes
document.getElementById('select-all-facilities').addEventListener('change', function () {
  const checkboxes = document.querySelectorAll('.facility-checkbox');
  checkboxes.forEach(cb => cb.checked = this.checked);
});
</script>

</body>
</html>