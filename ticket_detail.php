<?php
require_once 'config.php';
require_once 'session_helper.php';
require_once 'db_connection.php';

$success = "";
$error = "";

$ticket_id = intval($_GET['id'] ?? 0);
$ticket = null;

if (isset($_GET['success'])) $success = $_GET['success'];
if (isset($_GET['error'])) $error = $_GET['error'];

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
        if ($db->connect_error) {
            $error = "DB error: " . $db->connect_error;
        } else {
            $stmt = $db->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $ticket_id, $user_id, $comment_text);
            if ($stmt->execute()) {
                $success = "Comment added successfully!";
            } else {
                $error = "Failed to add comment: " . $stmt->error;
            }
            $stmt->close();
            $db->close();
        }
        // Optionally: redirect to clear POST and avoid resubmission
        if ($success) {
            header("Location: ticket_detail.php?id=$ticket_id&success=" . urlencode($success));
            exit;
        } elseif ($error) {
            header("Location: ticket_detail.php?id=$ticket_id&error=" . urlencode($error));
            exit;
        }
    } else {
        $error = "Comment cannot be empty.";
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

// Fetch status and priority options
$status_options = [];
$priority_options = [];
$res = $db->query("SELECT id, name FROM ticket_statuses ORDER BY id ASC");
while ($row = $res->fetch_assoc()) $status_options[] = $row;
$res = $db->query("SELECT id, name FROM ticket_priorities ORDER BY id ASC");
while ($row = $res->fetch_assoc()) $priority_options[] = $row;

// Handle update form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ticket'])) {
    $new_status = intval($_POST['status_id'] ?? 0);
    $new_priority = intval($_POST['priority_id'] ?? 0);

    if ($ticket_id > 0 && $new_status > 0 && $new_priority > 0) {
        $stmt = $db->prepare("UPDATE tickets SET status_id = ?, priority_id = ? WHERE id = ?");
        $stmt->bind_param("iii", $new_status, $new_priority, $ticket_id);
        if ($stmt->execute()) {
            $success = "Ticket updated successfully!";
        } else {
            $error = "Failed to update ticket: " . $stmt->error;
        }
        $stmt->close();
        // Optionally: redirect to clear POST and avoid resubmission
        if ($success) {
            header("Location: ticket_detail.php?id=$ticket_id&success=" . urlencode($success));
            exit;
        } elseif ($error) {
            header("Location: ticket_detail.php?id=$ticket_id&error=" . urlencode($error));
            exit;
        }
    } else {
        $error = "Please select status and priority.";
    }
}

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
                  <h1 class="heading-style-h3">Ticket #<?= htmlspecialchars($_GET['id'] ?? '') ?></h1>
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
              <div class="ticket_component">
                <form method="post">
                  <div class="ticket_info">
                      <div class="ticket_heading-row">
                        <div class="text-weight-semibold"><?= htmlspecialchars($ticket['title'] ?? '') ?></div>
                        <div class="text-size-small"><?= htmlspecialchars($ticket['description'] ?? '') ?></div>
                      </div>
                      <div class="ticket_info-wrap">
                        <div class="ticket_info-row">
                          <div>Status:</div>
                          <div>
                            <select name="status_id" class="select-small">
                              <?php foreach ($status_options as $opt): ?>
                                <option value="<?= $opt['id'] ?>" <?= $ticket['status_id'] == $opt['id'] ? 'selected' : '' ?>>
                                  <?= htmlspecialchars($opt['name']) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                        </div>
                        <div class="ticket_info-row">
                          <div>Priority:</div>
                          <div>
                            <select name="priority_id" class="select-small">
                              <?php foreach ($priority_options as $opt): ?>
                                <option value="<?= $opt['id'] ?>" <?= $ticket['priority_id'] == $opt['id'] ? 'selected' : '' ?>>
                                  <?= htmlspecialchars($opt['name']) ?>
                                </option>
                              <?php endforeach; ?>
                            </select>
                          </div>
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
                      <div class="button-group align-center">
                        <button type="submit" name="update_ticket" class="button is-xsmall w-button">Save</button>
                      </div>
                  </div>
                </form>
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