<?php
require_once '../config/db.php';
require_once '../includes/security.php';
admin_guard();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet">
</head>
<body style="font-family: montserrat; font-weight: 700;">
<center><h2 style="font-size: 50px; margin-bottom: 5px; text-align: center; background: linear-gradient(135deg, #0a1cff, #1226ff); color: white;">Admin Dashboard</h2></center>
<center><h3 style="margin-top: 0px; opacity: 0.9;">Department of Cybersecurity and Software Engineering</h3></center><br>

<center>
<div style="height: 300px; width: 300px;">
<ul>
    <li><a href="elections.php"><button class="elect" style="height: 50px; width: 200px; border-radius: 5px 5px 5px 5px; background-color: white; font-size: 20px">Manage Elections</button></a></li><br>
    <li><a href="candidates.php"><button class="candi" style="height: 50px; width: 200px; border-radius: 5px 5px 5px 5px; background-color: white; font-size: 20px">Manage Candidates</button></a></li><br>
    <li><a href="voters.php"><button class="voter" style="height: 50px; width: 200px; border-radius: 5px 5px 5px 5px; background-color: white; font-size: 20px">Eligible Voters</button></a></li><br>
    <li><a href="logout.php"><button class="logout" style="height: 50px; width: 200px; border-radius: 5px 5px 5px 5px; background-color: white; font-size: 20px">Logout</button></a></li>
</ul>
</div>
</center>
</body>
</html>
