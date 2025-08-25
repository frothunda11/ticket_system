<?php
require_once 'config.php';
require_once 'session_helper.php';
require_once 'db_connection.php';

// Prepare dropdowns (facility_options, assigned_to_options, related_ticket_options)
$facility_options = '';
$assigned_to_options = '';
$related_ticket_options = '';

// Fetch facilities (user-limited)
$facility_ids = $_SESSION['facilities'] ?? [];
if (!empty($facility_ids)) {
    $placeholders = implode(',', array_fill(0, count($facility_ids), '?'));
    $stmt = $db->prepare("SELECT id, name FROM facilities WHERE id IN ($placeholders) ORDER BY name");
    $stmt->bind_param(str_repeat('s', count($facility_ids)), ...$facility_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $facility_options .= '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['name']) . '</option>';
    }
    $stmt->close();
}

// Fetch users for assignment
$res = $db->query("SELECT username FROM users ORDER BY username");
while ($row = $res->fetch_assoc()) {
    $assigned_to_options .= '<option value="' . htmlspecialchars($row['username']) . '">' . htmlspecialchars($row['username']) . '</option>';
}
$res->free();

// Fetch tickets for related_ticket dropdown
$res = $db->query("SELECT id, title FROM tickets ORDER BY id DESC LIMIT 50");
while ($row = $res->fetch_assoc()) {
    $related_ticket_options .= '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['title']) . '</option>';
}
$res->free();

$error = '';
$success = '';

// Handle form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status_id = intval($_POST['status_id'] ?? 0);
    $priority_id = intval($_POST['priority_id'] ?? 0);
    $facility_id = trim($_POST['facility_id'] ?? '');
    $assigned_to = trim($_POST['assigned_to'] ?? '') ?: null;
    $related_ticket_id = trim($_POST['related_ticket'] ?? '') ?: null;
    $created_by = "shilario";

    // Validation
    if (!$title || !$status_id || !$priority_id || !$facility_id || !$created_by) {
    $error = 'Please fill in all required fields.';
} else {
    $stmt = $db->prepare("
        INSERT INTO tickets 
            (title, description, status_id, priority_id, created_by, assigned_to, facility_id, related_ticket_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "ssiissii",
        $title,
        $description,
        $status_id,
        $priority_id,
        $created_by,
        $assigned_to,
        $facility_id,
        $related_ticket_id
    );
    if ($stmt->execute()) {
        $success = "Ticket created successfully!";
        $ticket_id = $stmt->insert_id;

        // Handle file upload if present
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($_FILES['attachment']['tmp_name']);
        if (!empty($_FILES['attachment']['name'])) {
            $filename = basename($_FILES['attachment']['name']);
            $fileData = file_get_contents($_FILES['attachment']['tmp_name']);
            $uploaded_by = $_SESSION['username'] ?? $created_by;

           $stmt_attach = $db->prepare("
    INSERT INTO ticket_attachments (ticket_id, file_data, file_name, mime_type, comment_id, uploaded_by)
    VALUES (?, ?, ?, ?, NULL, ?)
");
$stmt_attach->bind_param("ibsss", $ticket_id, $null, $filename, $mime_type, $uploaded_by);
$stmt_attach->send_long_data(1, $fileData);
$stmt_attach->execute();
            if (!$stmt_attach->execute()) {
                $error .= " File upload failed.";
            }
            $stmt_attach->close();
        }
    } else {
        $error = "Error creating ticket: " . $stmt->error;
    }
    $stmt->close();
}
}


$db->close();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Create Ticket</title>
  <meta content="width=device-width, initial-scale=1" name="viewport">
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
                <h1 class="heading-style-h3">Create Ticket</h1>
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
              <div class="shell-layout_component">
                <div class="form-block">
                  <form method="POST" enctype="multipart/form-data" name="create_ticket" class="form">
                    <div class="form_2col">
                        <div class="form-field-wrapper">
                            <label for="title" class="field-label">Ticket Name</label>
                            <input type="text" class="form-input" name="title" required>
                        </div>
                        <div class="form-field-wrapper"><label for="priority_id" class="field-label">Priority</label>
                          <select name="priority_id" class="form-input" required>
                              <option value="">Choose one...</option>
                              <option value="1">Low</option>
                              <option value="2">Medium</option>
                              <option value="3">High</option>
                              <option value="4">Urgent</option>
                              
                          </select>
                        </div>
                    </div>
                    <div class="form_2col">
                        <div class="form-field-wrapper">
                        <label for="facility_id" class="field-label">Facility</label>
                        <select required name="facility_id" class="form-input">
                            <option value="">Escoge uno...</option>
                            <?= $facility_options ?>
                        </select>
                      </div>
                        <div class="form-field-wrapper"><label for="status_id" class="field-label">Priority</label>
                          <select name="status_id" class="form-input" required>
                              <option value="">Choose one...</option>
                              <option value="1">Open</option>
                              <option value="2">In Progress</option>
                              <option value="3">On Hold</option>
                              <option value="4">Resolved</option>
                              <option value="5">Closed</option>
                              
                          </select>
                        </div>
                    </div>
                    <div class="form_2col">
                      <div class="form-field-wrapper">
                        <label for="related_ticket" class="field-label">Related Ticket</label>
                        <select name="related_ticket" class="form-input">
                            <option value="">Escoge uno...</option>
                            <?= $related_ticket_options ?>
                        </select>
                      </div>
                      <div class="form-field-wrapper"><label for="assigned_to" class="field-label">Assigned To</label>
                        <select name="assigned_to" class="form-input" >
                            <option value="">Choose one...</option>
                            <?= $assigned_to_options ?>   
                        </select>
                      </div>
                    </div>
                    <div class="form_2col">
                      <div class="form-field-wrapper">
                        <label for="description" class="field-label">Description</label>
                        <textarea class="form-input-textarea" rows="3" name="description" type="text"></textarea>
                      </div>
                      <div class="form-field-wrapper">
                        <label for="attachment" class="field-label">Attachment (image/file)</label>
                        <input type="file" name="attachment" class="form-input-file" accept="image/*,.pdf">
                      </div>
                   </div>
                    <div>
                        <button class="button" type="submit">Save</button>
                    </div>
                  </form>
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