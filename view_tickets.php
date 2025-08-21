<?php
require_once 'config.php';
require_once 'session_helper.php';
require_once 'db_connection.php';

$username = $_SESSION['username'];
$facilities = $_SESSION['facilities'] ?? [];

// HTMX search/sort inputs
$search = trim($_GET['search'] ?? '');


$tickets = [];

// Defensive: only run query if there are facilities
if (!empty($facilities)) {
    $placeholders = implode(',', array_fill(0, count($facilities), '?'));
    $whereClauses = [
        "t.created_by = ?",
        "t.facility_id IN ($placeholders)"
    ];
    $params = [$username];
    $types = 's' . str_repeat('s', count($facilities));
    $params = array_merge($params, $facilities);

    if ($search) {
    $whereClauses[] = "(
        t.id LIKE ? OR
        t.title LIKE ? OR
        t.description LIKE ? OR
        f.name LIKE ? OR
        ts.name LIKE ? OR
        tp.name LIKE ? OR
        t.created_at LIKE ?
    )";
    $params = array_merge($params, array_fill(0, 7, "%$search%"));
    $types .= str_repeat('s', 7);
}
    $where = implode(' AND ', $whereClauses);

    $sql = "SELECT t.id, t.title, t.description, 
                   ts.name AS status_name, 
                   tp.name AS priority_name, 
                   t.facility_id, f.name AS facility_name, t.created_at
            FROM tickets t
            JOIN facilities f ON t.facility_id = f.id
            JOIN ticket_statuses ts ON t.status_id = ts.id
            JOIN ticket_priorities tp ON t.priority_id = tp.id
            WHERE $where
            ORDER BY t.created_at DESC";

    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $tickets[] = $row;
    }

    $stmt->close();
}

$db->close();

// HTMX partial response (table body only)
$is_htmx = isset($_SERVER['HTTP_HX_REQUEST']);
if ($is_htmx) {
    foreach ($tickets as $t) {
        echo "<tr class='table_row'>
            <td class='table_cell'>" . htmlspecialchars($t['id']) . "</td>
            <td class='table_cell'>" . htmlspecialchars($t['title']) . "</td>
            <td class='table_cell'>" . htmlspecialchars($t['facility_name']) . "</td>
            <td class='table_cell'>" . htmlspecialchars($t['status_name']) . "</td>
            <td class='table_cell'>" . htmlspecialchars($t['priority_name']) . "</td>
            <td class='table_cell'>" . htmlspecialchars($t['created_at']) . "</td>
            <td class='table_cell'>" . htmlspecialchars($t['description']) . "</td>
        </tr>";
    }
    if (empty($tickets)) {
        echo "<tr><td colspan='7'></td></tr>";
    }
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>View Tickets</title>
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <link href="css/normalize.css" rel="stylesheet" type="text/css">
  <link href="css/webflow.css" rel="stylesheet" type="text/css">
  <link href="css/tables.css" rel="stylesheet" type="text/css">
  <link href="css/aemr.webflow.css" rel="stylesheet" type="text/css">
  <script type="text/javascript">!function(o,c){var n=c.documentElement,t=" w-mod-";n.className+=t+"js",("ontouchstart"in o||o.DocumentTouch&&c instanceof DocumentTouch)&&(n.className+=t+"touch")}(window,document);</script>
  <link href="images/favicon.png" rel="shortcut icon" type="image/x-icon">
  <link href="images/webclip.png" rel="apple-touch-icon"><!--  Keep this css code to improve the font quality -->
  <script src="https://unpkg.com/htmx.org@1.9.2"></script>
  <style>
  * {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
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
                <h1 class="heading-style-h3">My Tickets</h1>
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
                <div class="button-group" style="display: flex; gap: 8px;">
                <input type="text" name="search" id="ticket-search" placeholder="search..." class="form-input-small"
                    hx-get="view_tickets.php"
                    hx-target="#tickets-table-body"
                    hx-trigger="keyup changed delay:300ms"
                    hx-params="sort,search"
                >
            </div>
                <div class="spacer-small"> </div>

                <table class="table_table">
                  <thead class="table_head">
                    <tr class="table_row">
                      <th class="table_header">ID</th>
                      <th class="table_header">Title</th>
                      <th class="table_header">Facility</th>
                      <th class="table_header">Status</th>
                      <th class="table_header">Priority</th>
                      <th class="table_header">Created At</th>
                      <th class="table_header">Description</th>
                    </tr>
                  </thead>
                  <tbody id="tickets-table-body">
                    <?php foreach ($tickets as $t): ?>
                      <tr class="table_row">
                        <td class="table_cell">
                          <a href="ticket_detail.php?id=<?= htmlspecialchars($t['id']) ?>" style="color: #0074d9; text-decoration: underline;">
                            <?= htmlspecialchars($t['id']) ?>
                          </a>
                        </td>
                        <td class="table_cell"><?= htmlspecialchars($t['title']) ?></td>
                        <td class="table_cell"><?= htmlspecialchars($t['facility_name']) ?></td>
                        <td class="table_cell"><?= htmlspecialchars($t['status_name']) ?></td>
                        <td class="table_cell"><?= htmlspecialchars($t['priority_name']) ?></td>
                        <td class="table_cell"><?= htmlspecialchars($t['created_at']) ?></td>
                        <td class="table_cell"><?= htmlspecialchars($t['description']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                    <?php if (empty($tickets)): ?>
                      <tr><td colspan="7">No tickets found.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>

              
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

</body>
</html>