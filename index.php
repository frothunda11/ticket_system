<?php
 require_once 'config.php';
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
 
    $ldap = ldap_connect($ldap_host);
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
 
    // Step 1: Bind with service account
    if (@ldap_bind($ldap, $service_user, $service_pass)) {
        // Step 2: Search for user DN
        $filter = "(sAMAccountName=$username)";
        $attributes = ["dn", "memberof"];
        $search = ldap_search($ldap, $ldap_base_dn, $filter, $attributes);
 
        if (!$search) {
            $error = "LDAP search failed: " . ldap_error($ldap);
        } else {
            $entries = ldap_get_entries($ldap, $search);
 
            if ($entries["count"] == 0) {
                $error = "User not found in Active Directory.";
            } else {
                $user_dn = $entries[0]["dn"];
 
                // Step 3: Bind as user to validate password
                if (@ldap_bind($ldap, $user_dn, $password)) {
                    // Step 4: Determine role based on group membership
                    $role = null;
                    if (isset($entries[0]["memberof"])) {
                        foreach ($entries[0]["memberof"] as $group_dn) {
                            if (!is_string($group_dn)) continue;
                            foreach ($group_role_map as $keyword => $mapped_role) {
                                if (stripos($group_dn, $keyword) !== false) {
                                    $role = $mapped_role;
                                    break 2;
                                }
                            }
                        }
                    }
 
                    if ($role) {
                        // Step 5: Check if user is registered in app
                        $stmt = $db->prepare("SELECT username FROM users WHERE username = ?");
                        $stmt->bind_param("s", $username);
                        $stmt->execute();
                        $result = $stmt->get_result();
 
                        if ($result->num_rows === 1) {
                            // Fetch facilities
                            $stmt = $db->prepare("SELECT facility_id FROM user_facility_map WHERE username = ?");
                            $stmt->bind_param("s", $username);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            $facilities = [];
                            while ($row = $res->fetch_assoc()) {
                                $facilities[] = $row['facility_id'];
                            }
 
                            // Start session
                            $_SESSION['username'] = $username;
                            $_SESSION['role'] = $role;
                            $_SESSION['facilities'] = $facilities;
 
                            header("Location: main.php");
                            exit;
                        } else {
                            $error = "User is not registered in the AEMR system.";
                        }
                    } else {
                        $error = "Access denied: not a member of an authorized AEMR group.";
                    }
                } else {
                    $ldap_error = ldap_error($ldap);
                    switch ($ldap_error) {
                        case "Invalid credentials":
                            $error = "Invalid username or password.";
                            break;
                        case "No such object":
                            $error = "User not found in AD.";
                            break;
                        case "Constraint violation":
                            $error = "Account is locked, expired, or needs a password reset.";
                            break;
                        default:
                            $error = "User bind failed: " . $ldap_error;
                            break;
                    }
                }
            }
        }
    } else {
        $error = "LDAP service account bind failed: " . ldap_error($ldap);
    }
}
?>
<!DOCTYPE html><!--  This site was created in Webflow. https://webflow.com  --><!--  Last Published: Fri May 23 2025 18:26:00 GMT+0000 (Coordinated Universal Time)  -->
<html data-wf-page="682e19ddb0ae83ddaa78f3fa" data-wf-site="682e19ddb0ae83ddaa78f38d">
<head>
  <meta charset="utf-8">
  <title>aemr</title>
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
    <div class="global-styles">
      <div class="style-overrides w-embed">
        <style>
/* Ensure all elements inherit the color from its parent */
a,
.w-input,
.w-select,
.w-tab-link,
.w-nav-link,
.w-nav-brand,
.w-dropdown-btn,
.w-dropdown-toggle,
.w-slider-arrow-left,
.w-slider-arrow-right,
.w-dropdown-link {
  color: inherit;
  text-decoration: inherit;
  font-size: inherit;
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
/* Remove default Webflow chevron from form select */
select{
  -webkit-appearance:none;
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
      <div class="color-schemes w-embed">
        <style>
/* Color Schemes Controls*/
<meta name="relume-color-schemes" content="false"/>
  .color-scheme-1 {
/*All sections should point to Color Scheme 1*/
  }
  .color-scheme-2 {
    --color-scheme-1--text: var(--color-scheme-2--text);
    --color-scheme-1--background: var(--color-scheme-2--background);
    --color-scheme-1--foreground: var(--color-scheme-2--foreground);
    --color-scheme-1--border: var(--color-scheme-2--border);
    --color-scheme-1--accent: var(--color-scheme-2--accent);
  }
  .color-scheme-3 {
    --color-scheme-1--text: var(--color-scheme-3--text);
    --color-scheme-1--background: var(--color-scheme-3--background);
    --color-scheme-1--foreground: var(--color-scheme-3--foreground);
    --color-scheme-1--border: var(--color-scheme-3--border);
    --color-scheme-1--accent: var(--color-scheme-3--accent);
  }
  .color-scheme-4 {
    --color-scheme-1--text: var(--color-scheme-4--text);
    --color-scheme-1--background: var(--color-scheme-4--background);
    --color-scheme-1--foreground: var(--color-scheme-4--foreground);
    --color-scheme-1--border: var(--color-scheme-4--border);
    --color-scheme-1--accent: var(--color-scheme-4--accent);
  }
  .color-scheme-5 {
    --color-scheme-1--text: var(--color-scheme-5--text);
    --color-scheme-1--background: var(--color-scheme-5--background);
    --color-scheme-1--foreground: var(--color-scheme-5--foreground);
    --color-scheme-1--border: var(--color-scheme-5--border);
    --color-scheme-1--accent: var(--color-scheme-5--accent);
  }
  .color-scheme-6 {
    --color-scheme-1--text: var(--color-scheme-6--text);
    --color-scheme-1--background: var(--color-scheme-6--background);
    --color-scheme-1--foreground: var(--color-scheme-6--foreground);
    --color-scheme-1--border: var(--color-scheme-6--border);
    --color-scheme-1--accent: var(--color-scheme-6--accent);
  }
  .color-scheme-7 {
    --color-scheme-1--text: var(--color-scheme-7--text);
    --color-scheme-1--background: var(--color-scheme-7--background);
    --color-scheme-1--foreground: var(--color-scheme-7--foreground);
    --color-scheme-1--border: var(--color-scheme-7--border);
    --color-scheme-1--accent: var(--color-scheme-7--accent);
  }
  .color-scheme-8 {
    --color-scheme-1--text: var(--color-scheme-8--text);
    --color-scheme-1--background: var(--color-scheme-8--background);
    --color-scheme-1--foreground: var(--color-scheme-8--foreground);
    --color-scheme-1--border: var(--color-scheme-8--border);
    --color-scheme-1--accent: var(--color-scheme-8--accent);
  }
  .color-scheme-9 {
    --color-scheme-1--text: var(--color-scheme-9--text);
    --color-scheme-1--background: var(--color-scheme-9--background);
    --color-scheme-1--foreground: var(--color-scheme-9--foreground);
    --color-scheme-1--border: var(--color-scheme-9--border);
    --color-scheme-1--accent: var(--color-scheme-9--accent);
  }
  .color-scheme-10 {
    --color-scheme-1--text: var(--color-scheme-10--text);
    --color-scheme-1--background: var(--color-scheme-10--background);
    --color-scheme-1--foreground: var(--color-scheme-10--foreground);
    --color-scheme-1--border: var(--color-scheme-10--border);
    --color-scheme-1--accent: var(--color-scheme-10--accent);
  }
/* Inherit slider dot colors */
.w-slider-dot {
  background-color: var(--color-scheme-1--text);
  opacity: 0.20;
}
.w-slider-dot.w-active {
  background-color: var(--color-scheme-1--text);
  opacity: 1;
}
/* Override .w-slider-nav-invert styles */
.w-slider-nav-invert .w-slider-dot {
  background-color: var(--color-scheme-1--text) !important;
  opacity: 0.20 !important;
}
.w-slider-nav-invert .w-slider-dot.w-active {
  background-color: var(--color-scheme-1--text) !important;
  opacity: 1 !important;
}
</style>
      </div>
    </div>
    <main class="main-wrapper">
      <section class="section_login1">
        <div class="padding-global">
          <div class="login_component">
            <div class="login_navbar">
              <a href="#" class="login_logo-link w-nav-brand"><img loading="lazy" src="images/Logo-500x500_Atlantis_28May20_Mesa-de-tr.png" alt=""></a>
            </div>
            <div class="max-width-small align-center">
              <div class="text-align-center">
                <h1 class="heading-style-h2">Login</h1>
                <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
              </div>
              <div class="spacer-medium"></div>
              <div class="login_form-block">
                <form class="login_form" method="POST">
                  <div class="form_field-wrapper">
                    <div class="form_field-label">Username*</div><input class="form_input" name="username" data-name="username" placeholder="" type="text"  required="">
                  </div>
                  <div class="form_field-wrapper">
                    <div class="form_field-label">Password*</div><input class="form_input" name="password" data-name="password" placeholder="" type="password"  required="">
                  </div>
                  <div class="form-button-wrapper">
                    <button class="button max-width-full" type="submit">Login</button>
                </div>
                </form>
                
                <div class="spacer-small"></div>
                <div class="text-align-center">
                  <a href="https://relume-library-components.webflow.io/forms-sections#">Olvido su contraseña?</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>
  <script src="https://d3e54v103j8qbb.cloudfront.net/js/jquery-3.5.1.min.dc5e7f18c8.js?site=682e19ddb0ae83ddaa78f38d" type="text/javascript" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
  <script src="js/webflow.js" type="text/javascript"></script>
</body>
</html>