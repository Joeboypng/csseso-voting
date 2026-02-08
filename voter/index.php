<?php
require_once '../config/security.php';
require_once '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* RESET OTP SESSION */
unset($_SESSION['otp_voter_id'], $_SESSION['otp_email']);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $index_number = trim($_POST['index_number']);
    $email        = trim($_POST['email']);

    if ($index_number === '' || $email === '') {
        $error = "All fields are required.";
    } else {

        $stmt = $conn->prepare(
            "SELECT id, has_voted FROM voters WHERE index_number=? AND email=? LIMIT 1"
        );
        $stmt->execute([$index_number, $email]);
        $voter = $stmt->fetch();

        if (!$voter) {
            $error = "Voter not found or email mismatch.";
        } elseif ($voter['has_voted']) {
            $error = "You have already voted.";
        } else {
            $_SESSION['otp_voter_id'] = $voter['id'];
            $_SESSION['otp_email']    = $email;
            header("Location: send_otp.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Voter Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 600px;
    margin: auto;
    padding: 15px;
}

/* Buttons */
button, a {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}

/* Desktop improvement */
@media (min-width: 768px) {
    .container {
        max-width: 800px;
    }

    button, a {
        width: auto;
    }
}
</style>
</head>
<body id="body">
<div class="container">
<center style="margin-top:100px">
<div style="background:blue;padding:20px;width:320px;color:white">
<h2>Voter Login</h2>

<?php if ($error): ?>
<p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
<input name="index_number" placeholder="Student ID" required><br><br>
<input name="email" type="email" placeholder="Enter Student Email" required><br><br>
<button type="submit">Request OTP</button>
</form>
</div>
</center>
</div>
</body>
</html>

