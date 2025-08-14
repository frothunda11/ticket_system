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
                            $error = "User is not registered in the system.";
                        }
                    } else {
                        $error = "Access denied: not a member of an authorized group.";
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
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Ticket System</title>
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
    <div class="global-styles">
    </div>
    <main class="main-wrapper">
      <section class="section_login1">
        <div class="padding-global">
          <div class="login_component">
            <div class="login_navbar">
              <a href="#" class="login_logo-link w-nav-brand"><img loading="lazy" src="images/logo.png" alt=""></a>
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
                  <a href="#">Olvido su contraseña?</a>
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