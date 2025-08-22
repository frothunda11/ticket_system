<?php
require_once 'config.php';
require_once 'session_helper.php';
require_once 'db_connection.php';

$ticket_id = intval($_GET['id'] ?? 0);
$ticket = null;

if ($ticket_id > 0) {
    $stmt = $db->prepare("
    SELECT t.id, t.title, t.description, t.status_id, ts.name AS status_name, 
           t.priority_id, tp.name AS priority_name, t.created_by, t.assigned_to, 
           t.facility_id, f.name AS facility_name, t.related_ticket_id, t.created_at, t.updated_at
    FROM tickets t
    JOIN facilities f ON t.facility_id = f.id
    JOIN ticket_statuses ts ON t.status_id = ts.id
    JOIN ticket_priorities tp ON t.priority_id = tp.id
    WHERE t.id = ?
");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();
$ticket = $result->fetch_assoc();
$stmt->close();
}

//comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment_text = trim($_POST['comment']);
    $user_id = $_SESSION['username'];
    if (!empty($comment_text) && $ticket_id > 0 && !empty($user_id)) {
        // Open new DB connection
        $db = new mysqli('localhost', 'root', '', 'ticket_system');
        if ($db->connect_error) die("DB error: " . $db->connect_error);

        $stmt = $db->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $ticket_id, $user_id, $comment_text);
        $stmt->execute();
        $stmt->close();
        $db->close();
        // Optionally: redirect to clear POST and avoid resubmission
        header("Location: ticket_detail.php?id=$ticket_id");
        exit;
    }
}

// Fetch comments for the ticket
$comments = [];

$stmt = $db->prepare("SELECT user_id, comment, created_at FROM ticket_comments WHERE ticket_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $comments[] = $row;
}
$stmt->close();


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
                  <h1 class="heading-style-h3">Ticket #<?= htmlspecialchars($_GET['id'] ?? '') ?></h1>
                </div>
              </div>
            </div>
          </div>
        </header>
        <div class="section_shell-layout">
          <div class="padding-global">
            <div class="container-large">
              <div class="ticket_component">
                <div class="ticket_info">
                    <div class="ticket_heading-row">
                      <div class="text-weight-semibold"><?= htmlspecialchars($ticket['title'] ?? '') ?></div>
                      <div class="text-size-small"><?= htmlspecialchars($ticket['description'] ?? '') ?></div>
                    </div>
                    <div class="ticket_info-wrap">
                      <div class="ticket_info-row">
                          <div>Status:</div>
                          <div class="text-color-light-grey"><?= htmlspecialchars($ticket['status_name'] ?? $ticket['status_id']) ?></div>
                      </div>
                      <div class="ticket_info-row">
                          <div>Priority:</div>
                          <div class="text-color-light-grey"><?= htmlspecialchars($ticket['priority_name'] ?? $ticket['priority_id']) ?></div>
                      </div>
                      <div class="ticket_info-row">
                          <div>Created by:</div>
                          <div class="text-color-light-grey"><?= htmlspecialchars($ticket['created_by'] ?? '') ?></div>
                      </div>
                      <div class="ticket_info-row">
                          <div>Assigned to:</div>
                          <div class="text-color-light-grey"><?= htmlspecialchars($ticket['assigned_to'] ?? 'None') ?></div>
                      </div>
                      <div class="ticket_info-row">
                          <div>Facility:</div>
                          <div class="text-color-light-grey"><?= htmlspecialchars($ticket['facility_name'] ?? $ticket['facility_id']) ?></div>
                      </div>
                      <div class="ticket_info-row">
                          <div>Related Ticket:</div>
                          <div class="text-color-light-grey">#<?= htmlspecialchars($ticket['related_ticket_id'] ?? 'None') ?></div>
                      </div>
                      <div class="ticket_info-row">
                          <div>Created At:</div>
                          <div class="text-color-light-grey"><?= htmlspecialchars($ticket['created_at'] ?? '') ?></div>
                      </div>
                      <div class="ticket_info-row">
                          <div>Updated At:</div>
                          <div class="text-color-light-grey"><?= htmlspecialchars($ticket['updated_at'] ?? '') ?></div>
                      </div>
                    </div>
                    <div class="button-group align-center"><a href="#" class="button is-xsmall w-button">Save</a></div>
                </div>
                <div class="ticket_comments">
                    <div>
                      <form name="comment_form" method="post" class="comment-form">
                        <label for="comment">Comment</label>
                        <textarea placeholder="Type Message" maxlength="5000" name="comment" class="comment-textarea"></textarea>
                        <input type="submit" class="button is-xsmall" value="Submit"></form>
                    </div>
                    <div class="ticket_comment-wrap">
                    <div>Ticket comments</div>
                    <div class="ticket_comment-row-wrap">
                      <?php foreach ($comments as $c): ?>
                        <div class="ticket_comment-row">
                          <div class="ticket_comment-row-heading">
                            <div class="ticket_username"><?= htmlspecialchars($c['user_id']) ?></div>
                            <div><?= htmlspecialchars(date("m/d/Y, h:i A", strtotime($c['created_at']))) ?></div>
                          </div>
                          <p class="ticket_comment-text"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                        </div>
                      <?php endforeach; ?>
                      <?php if (empty($comments)): ?>
                        <div>No comments yet.</div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
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