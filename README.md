# AEMR (Asset and Emergency Management Reports)

A lightweight PHP-MySQL-based internal web application for managing facility reports, water and fuel reserves, generator logs, and technician activity across multiple locations.

---

## 📦 Project Structure

```
aemr_v1/
## user-facing Pages
├── index.php                   # Login
├── main.php                    # Home/Dashboard
├── report.php                  # Submit report
├── view-report.php             # View reports, shows latest report from each facility. Download report.
├── cistern_maint.php           # Submit maintenance. currently disable for all users
├── cisterns.php                # Add cisterns
├── generators.php              # Add generators
├── events.php                  # Add Events
├── users.php                   # User management + mapping to facilities

## Background Pages
├── access_denied.php           # redirect users if the page is not authorized
├── config.php                  # Handles session setup, database and LDAP connections, timezone, error reporting, and role-based access control.
├── db_connection.php           # Connects to the MySQL database
├── download_event_reports.php  # Handles the download of events to csv
├── logout.php                  # log out users
├── session_helper.php          # Manages session timeout, enforces user login, and optionally restricts access based on user roles.  
├── assets/                     # CSS/JS files
├── README.md                   # Project documentation (this file)

---

## 🛠️ Installation Instructions

1. Clone the repository or copy the project folder.
2. Install XAMPP and start Apache/MySQL.
3. Import sql file/database into phpMyAdmin.
4. Configure DB credentials in `includes/db.php`.
5. Open in browser: http://localhost/aemr_v1


## 🧑‍💻 User Roles & Permissions

* `admin`: Full access (view, edit, delete, manage users and facilities)
* `editor`: Can create and submit reports
* `viewer`: Can only view reports
Role access is enforced at page level (in config.php)


## 📋 Features

* Report submission (weekly/event)
* Logging cistern water levels, generator diesel levels, and working hours
* Mapping users to specific facilities
* Fuel reserve tracking
* Facility status tracking (power/water source)
* Auto-detects number of cisterns/generators
* Chart.js visualizations of historical data
* Role-based access control

---

## 🔐 Environment Modes

Use a flag like `$isProduction = true;` (in config.php) to turn off error messages.


---

## 🚀 Deployment Tips

* Host on a local LAN server (XAMPP).
* Keep DB backups regularly.
* Use Git (private) to version code.

---

## 🛡️ Security Checklist

* [x] Input validation
* [x] Role-based access
* [x] Use `htmlspecialchars()` when displaying dynamic data

---

## Mysql database users
Apache server user: aemrapp
password: @3mrAtlant1$ 
Aemr transfer switch sensor: aemrxsensor
password: @3mrAtlant1$

## 📞 Support & Maintenance

* Contact: \ Steven Hilario steven.hilario@atlantishgi.com  frontend dev
            Efrain Gonzalez efrain.gonzalez@atlantishgi.com backend dev
* Update process: Make changes on a staging copy, test, then push to production.

---

## Pushing updates to live site

- Make updates on local copy and active version.
- Test new features locally.
- Push updates in github.
- Copy files to production environment and clear cache if needed.

---

Feel free to edit and customize this `README.md` as your app evolves!
