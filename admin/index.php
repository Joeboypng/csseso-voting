<?php
session_start();
require_once '../includes/security.php';
require_once '../config/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid login details";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
</head>
<body style="background-color: black;">
<center style="margin-top: 100px;">
<div style="border: 3px solid cyan; box-shadow: 0 0 35px cyan; height: 350px; width: 300px; background-color: black;">
    <h2 style="color: white; margin-top: 20px;">Admin Login</h2>
    <h3 style="color: white; margin-bottom: 40px;">This page is for admins only</h3>

    <?php if ($error): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input style="height:30px;width:200px;border-radius:6px;"
               type="text" name="username" placeholder="Username" required><br><br>

        <input style="height:30px;width:200px;border-radius:6px;"
               type="password" name="password" placeholder="Password" required><br><br>

        <button style="height:30px;width:100px;border-radius:6px;
                       background:#0F2D5E;color:white;font-size:15px;"
                type="submit">
            Login
        </button>
    </form>
</div>
</center>
</body>
</html>
