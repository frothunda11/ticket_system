<?php
require_once 'config.php';
require_once 'session_helper.php';
require 'secrets.php'; //adding password for email


$success = '';
$error = '';

// PHPMailer
require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $type = htmlspecialchars(trim($_POST['type'] ?? ''));
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $username = $_SESSION['username'] ?? '';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.office365.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'steven.hilario@atlantishgi.com'; // your Outlook address
        $mail->Password = $OUTLOOK_SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('steven.hilario@atlantishgi.com', 'AEMR Feedback');
        $mail->addAddress('steven.hilario@atlantishgi.com');
        $mail->addAddress('efrain.gonzalez@atlantishgi.com');
        if (!empty($email)) $mail->addReplyTo($email);

        $mail->isHTML(false);
        $mail->Subject = "[Starter Feedback] $type";
        $mail->Body =
            "Feedback Type: $type\n" .
            "User Email: $email\n" .
            "Username: $username\n\n" .
            "Description:\n$description";

        $mail->send();
        $success = "Feedback sent successfully!";
    } catch (Exception $e) {
        $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}



?>

<!DOCTYPE html><!--  This site was created in Webflow. https://webflow.com  --><!--  Last Published: Fri May 23 2025 18:26:00 GMT+0000 (Coordinated Universal Time)  -->
<html data-wf-page="6830b6838485e8a9e075884f" data-wf-site="682e19ddb0ae83ddaa78f38d">
<head>
  <meta charset="utf-8">
  <title>Feedback</title>
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
                <h1 class="heading-style-h3">Feedback and error submission</h1>
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
                  <form method="POST" name="feedback" class="form" >
                    <div class="form_2col">
                      <div class="form-field-wrapper">
                      <label for="email" class="field-label">Your Email</label>
                      <input class="form-input" name="email" type="email" require></div>
                        <div class="form-field-wrapper"><label for="Type" class="field-label">Type</label>
                          <select require name="type" class="form-input">
                              <option value="">Choose one...</option>
                              <option value="Suggestion">Suggestion/Feature Request</option>
                              <option value="Bug/Error">Bug/Error</option>
                              <option value="Other">Other</option>
                          </select>
                        </div>
                    </div>
                    <div class="form_2col">
                      <div class="form-field-wrapper">
                        <label for="description" class="field-label">Description</label>
                        <textarea require class="form-input" maxlength="300" name="description" ></textarea>
                      </div>
                    </div>
                    <div>
                    <button id="sendFeedbackBtn" class="button" type="submit">Send feedback</button>
                </div>
                  </form>
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
<script>
  //adding spinner to button on sending email from feedback
document.addEventListener('DOMContentLoaded', function () {
  var form = document.querySelector('form[name="feedback"]');
  var btn = document.getElementById('sendFeedbackBtn');
  if (form && btn) {
    form.addEventListener('submit', function () {
      btn.disabled = true;
      btn.innerHTML = '<span class="button-spinner"></span>Sending...';
    });
  }
});
</script>
</body>
</html>