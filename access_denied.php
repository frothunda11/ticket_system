<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Access Denied</title>
    <style>
        body { font-family: Arial; text-align: center; padding: 50px; }
        .box {
            border: 1px solid #ccc;
            padding: 30px;
            display: inline-block;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        a {
            color: #007BFF;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>🚫 Access Denied</h1>
        <p>You don’t have permission to access this page.</p>
        <p><a href="index.php">Return to Login</a></p>
    </div>
</body>
</html>