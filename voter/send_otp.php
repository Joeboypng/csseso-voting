<?php
require_once '../config/security.php';
require_once '../config/db.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../mailer/PHPMailer.php';
require_once '../mailer/SMTP.php';
require_once '../mailer/Exception.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* 🔐 HARD BLOCK */
if (
    empty($_SESSION['otp_voter_id']) ||
    empty($_SESSION['otp_email'])
) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$voter_id = $_SESSION['otp_voter_id'];
$email    = $_SESSION['otp_email'];
$ip       = $_SERVER['REMOTE_ADDR'];

/* ⏱ RESEND COOLDOWN (60s) */
if (isset($_SESSION['otp_last_sent']) && time() - $_SESSION['otp_last_sent'] < 60) {
    die("⏳ Please wait 60 seconds before requesting another OTP.");
}

/* 🔐 GENERATE OTP */
$otp = random_int(100000, 999999);
$expiry = date('Y-m-d H:i:s', time() + 300);

/* 🧹 CLEAR OLD */
$conn->prepare("DELETE FROM voter_otps WHERE voter_id=?")->execute([$voter_id]);

/* 💾 SAVE OTP */
$conn->prepare(
    "INSERT INTO voter_otps (voter_id, otp, expires_at) VALUES (?, ?, ?)"
)->execute([$voter_id, $otp, $expiry]);

/* 📝 AUDIT LOG */
$conn->prepare(
    "INSERT INTO otp_audit_logs (voter_id, event, ip_address)
     VALUES (?, 'OTP_SENT', ?)"
)->execute([$voter_id, $ip]);

/* 📧 SEND EMAIL */
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'csseso.voting@gmail.com';
    $mail->Password = 'hudp jydg rsfm elhf';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('csseso.voting@gmail.com', 'CSSESO Voting');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Your CSSESO Voting OTP';

    $mail->Body = "
    <div style='font-family:Arial;background:#f4f6f8;padding:20px'>
        <div style='max-width:500px;margin:auto;background:#ffffff;padding:20px;border-radius:8px'>
            <h2 style='color:#0033cc'>CSSESO E-Voting</h2>
            <p>Your One-Time Password is:</p>
            <h1 style='letter-spacing:4px'>$otp</h1>
            <p>This OTP expires in <b>5 minutes</b>.</p>
            <hr>
            <small>If you did not request this, ignore this email.</small>
        </div>
    </div>";

    $mail->send();

    $_SESSION['otp_last_sent'] = time();
    header("Location: verify_otp.php");
    exit;

} catch (Exception $e) {
    die("❌ Failed to send OTP.");
}

