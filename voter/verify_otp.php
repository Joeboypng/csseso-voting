<?php
require_once '../config/security.php';
require_once '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['otp_voter_id'])) {
    header("Location: login.php");
    exit;
}

$voter_id = $_SESSION['otp_voter_id'];
$ip = $_SERVER['REMOTE_ADDR'];
$error = "";

$stmt = $conn->prepare(
    "SELECT * FROM voter_otps WHERE voter_id=? ORDER BY id DESC LIMIT 1"
);
$stmt->execute([$voter_id]);
$otpRow = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $entered = trim($_POST['otp']);

    if (!$otpRow || strtotime($otpRow['expires_at']) < time()) {
        $error = "OTP expired.";
    } elseif ($otpRow['attempts'] >= 3) {

        $conn->prepare(
            "UPDATE voters SET has_voted = 1 WHERE id=?"
        )->execute([$voter_id]);

        $error = "Account locked due to abuse.";

    } elseif ($entered !== (string)$otpRow['otp']) {

        $conn->prepare(
            "UPDATE voter_otps SET attempts = attempts + 1 WHERE id=?"
        )->execute([$otpRow['id']]);

        $conn->prepare(
            "INSERT INTO otp_audit_logs (voter_id,event,ip_address)
             VALUES (?, 'OTP_FAILED', ?)"
        )->execute([$voter_id, $ip]);

        $error = "Invalid OTP.";
    } else {

        $conn->prepare("DELETE FROM voter_otps WHERE id=?")->execute([$otpRow['id']]);

        $_SESSION['voter_logged_in'] = true;
        $_SESSION['voter_id'] = $voter_id;
        session_regenerate_id(true);

        unset($_SESSION['otp_voter_id']);
        header("Location: vote.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP</title>
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
<body style="background:black">
<div class="container">
<center style="margin-top:100px">
<div style="background:blue;padding:20px;width:300px;color:white">
<h2>Verify OTP</h2>

<?php if ($error): ?>
<p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
<input name="otp" placeholder="Enter OTP" required><br><br>
<button type="submit">Verify</button>
</form>

<br>
<a href="send_otp.php" style="color:white">Resend OTP</a>
</div>
</center>
</div>
</body>
</html>


