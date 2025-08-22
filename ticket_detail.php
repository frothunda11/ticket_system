<?php
require_once 'config.php';
require_once 'session_helper.php';

$ticket_id = intval($_GET['id'] ?? 0);
$ticket = null;

if ($ticket_id > 0) {
    $stmt = $db->prepare("
        SELECT id, description, status_id, priority_id, created_by, assigned_to, facility_id, related_ticket_id, created_at, updated_at
        FROM tickets
        WHERE id = ?
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
              <?php if ($ticket): ?>
                <div class="ticket-detail"><span class="ticket-label">Description:</span> <span class="ticket-value"><?= nl2br(htmlspecialchars($ticket['description'])) ?></span></div>
                <div class="ticket-detail"><span class="ticket-label">Status ID:</span> <span class="ticket-value"><?= htmlspecialchars($ticket['status_id']) ?></span></div>
                <div class="ticket-detail"><span class="ticket-label">Priority ID:</span> <span class="ticket-value"><?= htmlspecialchars($ticket['priority_id']) ?></span></div>
                <div class="ticket-detail"><span class="ticket-label">Created By:</span> <span class="ticket-value"><?= htmlspecialchars($ticket['created_by']) ?></span></div>
                <div class="ticket-detail"><span class="ticket-label">Assigned To:<span class="ticket-value"><?= htmlspecialchars($ticket['assigned_to'] ?? 'None') ?></span></div>
                <div class="ticket-detail"><span class="ticket-label">Facility ID:</span> <span class="ticket-value"><?= htmlspecialchars($ticket['facility_id']) ?></span></div>
                <div class="ticket-detail"><span class="ticket-label">Related Ticket ID:</span> <span class="ticket-value"><?= htmlspecialchars($ticket['related_ticket_id'] ?? 'None') ?></span></div>
                <div class="ticket-detail"><span class="ticket-label">Created At:</span> <span class="ticket-value"><?= htmlspecialchars($ticket['created_at']) ?></span></div>
                <div class="ticket-detail"><span class="ticket-label">Updated At:</span> <span class="ticket-value"><?= htmlspecialchars($ticket['updated_at']) ?></span></div>
                <div class="spacer-small"></div>
                <a href="view_tickets.php" class="button">Back to Tickets</a>
              <?php else: ?>
                <p>Ticket not found.</p>
              <?php endif; ?>

              <div class="ticket_component">
                <div class="ticket_info">
                    <div class="ticket_heading-row">
                      <div class="text-weight-semibold">Issue with internet</div>
                      <div class="text-size-small">I am unable to connect to the internet</div>
                    </div>
                    <div class="ticket_info-wrap">
                      <div class="ticket_info-row">
                          <div>Status:</div>
                          <div>[Open]</div>
                      </div>
                      <div class="ticket_info-row">
                          <div>Priority:</div>
                          <div>[Urgent]</div>
                      </div>
                      <div class="ticket_info-row">
                          <div>Created by:</div>
                          <div>[shilario]</div>
                      </div>
                      <div class="ticket_info-row">
                          <div>Assigned to:</div>
                          <div>[jloredo]</div>
                      </div>
                      <div class="ticket_info-row">
                          <div>Facility:</div>
                          <div>[Mayaguez]</div>
                      </div>
                      <div class="ticket_info-row">
                          <div>Related Ticket:</div>
                          <div>[#14]</div>
                      </div>
                      <div class="ticket_info-row">
                          <div>Created At:</div>
                          <div>[08/21/2025]</div>
                      </div>
                      <div class="ticket_info-row">
                          <div>Updated At:</div>
                          <div>[08/22/2025]</div>
                      </div>
                    </div>
                    <div class="button-group align-center"><a href="#" class="button is-xsmall w-button">Update/Save</a></div>
                </div>
                <div class="ticket_comments">
                    <div>
                      <form id="email-form" name="email-form" data-name="Email Form" method="get" aria-label="Email Form">
                        <label for="email">Comment</label>
                        <textarea placeholder="Example Text" maxlength="5000" id="field" name="field" data-name="Field" class="w-input"></textarea>
                        <input type="submit" class="button is-xsmall" value="Submit"></form>
                    </div>
                    <div class="ticket_comment-wrap">
                      <div>Ticket comments</div>
                      <div class="ticket_comment-row-wrap">
                          <div class="ticket_comment-row">
                            <div class="ticket_comment-row-heading">
                                <div>shilario</div>
                                <div>08/23/2025, 4:30 PM</div>
                            </div>
                            <p class="ticket_comment-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros elementum tristique. Duis cursus, mi quis viverra ornare, eros dolor interdum nulla, ut commodo diam libero vitae erat. Aenean faucibus nibh et justo cursus id rutrum lorem imperdiet. Nunc ut sem vitae risus tristique posuere.</p>
                          </div>
                          <div class="ticket_comment-row">
                            <div class="ticket_comment-row-heading">
                                <div>jloredo</div>
                                <div>08/23/2025, 3:33 PM</div>
                            </div>
                            <p class="ticket_comment-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse varius enim in eros elementum tristique. Duis cursus, mi quis viverra ornare, eros dolor interdum nulla, ut commodo diam libero vitae erat. Aenean faucibus nibh et justo cursus id rutrum lorem imperdiet. Nunc ut sem vitae risus tristique posuere.</p>
                          </div>
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