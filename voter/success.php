<?php

if (!isset($_SESSION['voter_logged_in'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Successful</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="background:#0a2a66; color:white; text-align:center;">

<h2>Login Successful ✅</h2>
<p>You may now proceed to vote.</p>

<a href="vote.php">
    <button style="height:35px; width:200px;">Go to Voting Page</button>
</a>

</body>
</html>
