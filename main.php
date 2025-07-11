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

$sql = "
SELECT 
  f.id AS facility_id,
  f.name AS facility_name,
  ROUND(AVG(DISTINCT c.water_level), 0) AS water_level,
  MAX(g1.diesel_level_percent) AS diesel_level_1,
  MAX(g2.diesel_level_percent) AS diesel_level_2,
  fuel.level AS fuel_level,
  r.power_source,
  r.water_source
FROM (
    SELECT r1.*
    FROM reports r1
    INNER JOIN (
        SELECT facility_id, MAX(id) AS max_id
        FROM reports
        WHERE facility_id IN ($placeholders)
        GROUP BY facility_id
    ) latest
    ON r1.facility_id = latest.facility_id
    AND r1.id = latest.max_id
) r
JOIN facilities f ON r.facility_id = f.id
LEFT JOIN cistern_logs c ON c.report_id = r.id

LEFT JOIN (
    SELECT gl.report_id, gl.diesel_level_percent
    FROM generator_logs gl
    JOIN (
        SELECT report_id, MIN(generator_id) AS generator_id
        FROM generator_logs
        GROUP BY report_id
    ) min_gen ON gl.report_id = min_gen.report_id AND gl.generator_id = min_gen.generator_id
) g1 ON g1.report_id = r.id

LEFT JOIN (
    SELECT gl.report_id, gl.diesel_level_percent
    FROM generator_logs gl
    JOIN (
        SELECT report_id, MAX(generator_id) AS generator_id
        FROM generator_logs
        GROUP BY report_id
    ) max_gen ON gl.report_id = max_gen.report_id AND gl.generator_id = max_gen.generator_id
) g2 ON g2.report_id = r.id

LEFT JOIN (
    SELECT rep_id, MAX(id) as max_id
    FROM fuel_reserve
    GROUP BY rep_id
) fr ON fr.rep_id = r.id
LEFT JOIN fuel_reserve fuel ON fuel.id = fr.max_id
WHERE c.water_level IS NOT NULL
GROUP BY f.name, fuel.level, r.power_source, r.water_source
ORDER BY f.name ASC
";

$stmt = $db->prepare($sql);
$stmt->bind_param(str_repeat('i', count($facilities)), ...$facilities);
$stmt->execute();
$result = $stmt->get_result();

$facilityCharts = [];

while ($row = $result->fetch_assoc()) {
    $facilityCharts[] = [
        'facility_id'     => $row['facility_id'],
        'facility'        => $row['facility_name'],
        'water_level'     => (float) $row['water_level'],
        'diesel_1_level'  => isset($row['diesel_level_1']) ? (float) $row['diesel_level_1'] : 0,
        'diesel_2_level'  => isset($row['diesel_level_2']) ? (float) $row['diesel_level_2'] : 0,
        'fuel_level'      => isset($row['fuel_level']) ? (float) $row['fuel_level'] : 0,
        'power_source'    => $row['power_source'],
        'water_source'    => $row['water_source'],
    ];
}



$db->close();
?>

<!DOCTYPE html><!--  This site was created in Webflow. https://webflow.com  --><!--  Last Published: Fri May 23 2025 18:26:00 GMT+0000 (Coordinated Universal Time)  -->
<html data-wf-page="68309de13312e42fb5f708c9" data-wf-site="682e19ddb0ae83ddaa78f38d">
<head>
  <meta charset="utf-8">
  <title>Home</title>
  <meta content="Main" property="og:title">
  <meta content="Main" property="twitter:title">
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <meta content="Webflow" name="generator">
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
                <div class="max-width-large">
                  <h1 class="heading-style-h3">Dashboard</h1>
                </div>
              </div>
            </div>
          </div>
        </header>
        <div class="section_shell-layout">
          <div class="padding-global">
            <div class="container-large">
              <div id="w-node-_27b25744-048c-b6db-6354-5ae213b547ae-b5f708c9" class="w-layout-grid shell-layout_component">
                <div id="chartsContainer"></div>
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
  <script src="https://d3e54v103j8qbb.cloudfront.net/js/jquery-3.5.1.min.dc5e7f18c8.js?site=682e19ddb0ae83ddaa78f38d" type="text/javascript" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
  <script src="js/webflow.js" type="text/javascript"></script>
 <script>
Chart.register({
  id: 'customColoredTitle',
  beforeDraw(chart) {
    const {ctx, chartArea: {top, left, right}, config: {data}} = chart;
    if (!data.meta) return;

    const powerSource = data.meta.power_source;
    const waterSource = data.meta.water_source;

    // Conditional colors using if logic
    let powerColor = '#000'; // default black
    if (powerSource === 'AEE') powerColor = '#228B22';      // light green
    else if (powerSource === 'Gen 1') powerColor = '#D2042D'; // light red
    else if (powerSource === 'Gen 2') powerColor = '#D2042D'; // light red

    let waterColor = '#000'; // default black
    if (waterSource === 'AAA') waterColor = '#0096FF';      // light blue
    else if (waterSource === 'Cistern') waterColor = '#D2042D';   // light red

    ctx.save();
    ctx.font = 'bold 15px Calibri';
    ctx.textAlign = 'center';

    

    // Title line 1: Power Source
    ctx.fillStyle = '#000';
    ctx.fillText('Power Source -', (left + right) / 2 - 40, top - 25);
    ctx.fillStyle = powerColor;
    ctx.fillText(powerSource, (left + right) / 2 + 40, top - 25);

    // Title line 2: Water Source
    ctx.fillStyle = '#000';
    ctx.fillText('Water Source -', (left + right) / 2 - 40, top - 5);
    ctx.fillStyle = waterColor;
    ctx.fillText(waterSource, (left + right) / 2 + 40, top - 5);

    ctx.restore();
  }
});


  //chart.js
const facilityCharts = <?= json_encode($facilityCharts) ?>;

facilityCharts.forEach((data, index) => {
  const chartId = `facilityChart${index}`;
  
  const chartWrapper = document.createElement('div');
  chartWrapper.className = 'chart-box';
  chartWrapper.innerHTML = `
    <p>${data.facility}: ${data.facility_id}</p>
    <canvas id="${chartId}" width="400" height="300"></canvas>
  `;
  document.getElementById('chartsContainer').appendChild(chartWrapper);

  new Chart(document.getElementById(chartId), {
    type: 'bar',
    data: {
      labels: ['Water Level', 'Gen #1 Diesel', 'Gen #2 Diesel', 'Fuel Reserve'],
      datasets: [{
        label: `${data.facility} (%)`,
        data: [
          data.water_level,
          data.diesel_1_level,
          data.diesel_2_level,
          data.fuel_level
        ],
        backgroundColor: ['#36A2EB', '#FF9F40', '#FF6384', '#4BC0C0']
      }],
  meta: {
    power_source: data.power_source,
    water_source: data.water_source
  }

    },
    options: {
      responsive: true,
      layout: {
        padding: {
          top: 40 // make space for 2-line custom title
        }
      },
      plugins: {
        title: {
          display: false // turn off built-in title
        },
            legend: {
              display: false
            }
          },
      scales: {
        y: {
          beginAtZero: true,
          max: 100
        }
      }
    }
  });
});
</script>
</script>


</body>
</html>