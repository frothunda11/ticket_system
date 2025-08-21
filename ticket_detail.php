<?php
require_once 'config.php';
require_once 'session_helper.php';
require_once 'db_connection.php';

$ticket_id = intval($_GET['id'] ?? 0);

$stmt = $db->prepare("
  SELECT t.id, t.title, t.description, t.created_at,
         ts.name AS status_name, tp.name AS priority_name,
         f.name AS facility_name, t.assigned_to, t.related_ticket_id
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
$db->close();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Ticket Details</title>
  <!-- Add your CSS links here -->
  <link href="css/normalize.css" rel="stylesheet" type="text/css">
  <link href="css/webflow.css" rel="stylesheet" type="text/css">
  <link href="css/tables.css" rel="stylesheet" type="text/css">
  <link href="css/aemr.webflow.css" rel="stylesheet" type="text/css">
</head>
<body>
  <?php include 'sidebar_component.php'; ?>
  <main class="shell_main-wrapper">
    <div class="padding-global">
      <div class="container-large">
        <h1>Ticket Details</h1>
        <?php if ($ticket): ?>
          <table class="table_table">
            <tr><th>ID</th><td><?= htmlspecialchars($ticket['id']) ?></td></tr>
            <tr><th>Title</th><td><?= htmlspecialchars($ticket['title']) ?></td></tr>
            <tr><th>Facility</th><td><?= htmlspecialchars($ticket['facility_name']) ?></td></tr>
            <tr><th>Status</th><td><?= htmlspecialchars($ticket['status_name']) ?></td></tr>
            <tr><th>Priority</th><td><?= htmlspecialchars($ticket['priority_name']) ?></td></tr>
            <tr><th>Assigned To</th><td><?= htmlspecialchars($ticket['assigned_to']) ?></td></tr>
            <tr><th>Related Ticket</th><td><?= htmlspecialchars($ticket['related_ticket_id']) ?></td></tr>
            <tr><th>Created At</th><td><?= htmlspecialchars($ticket['created_at']) ?></td></tr>
            <tr><th>Description</th><td><?= nl2br(htmlspecialchars($ticket['description'])) ?></td></tr>
          </table>
          <a href="view_tickets.php" class="button">Back to Tickets</a>
        <?php else: ?>
          <p>Ticket not found.</p>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>
</html>