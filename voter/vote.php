<?php
require_once '../config/security.php';
require_once '../config/db.php';

/* SAFE SESSION START */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* SECURITY */
if (!isset($_SESSION['voter_logged_in'], $_SESSION['voter_id'])) {
    header("Location: login.php");
    exit;
}

$voter_id = (int) $_SESSION['voter_id'];

/* GET ELECTION */
$stmt = $conn->prepare(
    "SELECT id, status 
     FROM elections 
     WHERE title = ? 
     LIMIT 1"
);
$stmt->execute(['CSSESO General Election']);
$election = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$election || $election['status'] !== 'open') {
    die("❌ Elections are closed. Contact EC on 0536764848.");
}

$election_id = (int) $election['id'];

/* CHECK IF ALREADY VOTED */
$stmt = $conn->prepare(
    "SELECT has_voted 
     FROM voters 
     WHERE id = ? 
     LIMIT 1"
);
$stmt->execute([$voter_id]);

if ($stmt->fetchColumn()) {
    die("❌ You have already voted.");
}

/* FETCH PORTFOLIOS */
$stmt = $conn->prepare(
    "SELECT DISTINCT portfolio 
     FROM candidates 
     WHERE election_id = ? 
     ORDER BY portfolio"
);
$stmt->execute([$election_id]);
$portfolios = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!$portfolios) {
    die("⚠️ No candidates available.");
}

$totalSteps = count($portfolios);
$step = isset($_GET['step']) ? (int) $_GET['step'] : 0;

/* STOP OVERFLOW */
if ($step >= $totalSteps) {
    header("Location: submit_vote.php");
    exit;
}

$portfolio = $portfolios[$step];
$error = "";

/* HANDLE POST */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['skip'])) {
        $_SESSION['skipped'][$portfolio] = true;
        unset($_SESSION['votes'][$portfolio]);
        header("Location: vote.php?step=" . ($step + 1));
        exit;
    }

    if (!isset($_POST['candidate_id'])) {
        $error = "⚠️ Please select a candidate before voting or click Skip.";
    } else {
        $_SESSION['votes'][$portfolio] = (int) $_POST['candidate_id'];
        unset($_SESSION['skipped'][$portfolio]);

        if ($step + 1 < $totalSteps) {
            header("Location: vote.php?step=" . ($step + 1));
        } else {
            header("Location: submit_vote.php");
        }
        exit;
    }
}

/* FETCH CANDIDATES */
$stmt = $conn->prepare(
    "SELECT id, full_name, photo 
     FROM candidates
     WHERE portfolio = ? 
       AND election_id = ?"
);
$stmt->execute([$portfolio, $election_id]);
$candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Vote</title>
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

img {
    max-width: 100%;
    height: auto;
}

label {
    display: block;
    margin-bottom: 15px;
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
<body>

<div class="container">
<h2><?= htmlspecialchars($portfolio) ?></h2>

<?php if ($error): ?>
<p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">

<?php foreach ($candidates as $c): ?>
    <label style="border:1px solid #ddd;padding:10px;border-radius:8px;">
        <img src="../uploads/candidates/<?= htmlspecialchars($c['photo']) ?>" width="90"><br>
        <input type="radio" name="candidate_id" value="<?= $c['id'] ?>">
        <?= htmlspecialchars($c['full_name']) ?>
    </label><br><br>
<?php endforeach; ?>

<?php if ($step > 0): ?>
<a href="vote.php?step=<?= $step - 1 ?>"
   style="display:inline-block;text-decoration:none;padding:7px 15px;border-radius:6px;background:blue;color:white;">⬅ Back</a>
<?php endif; ?>

<br><br>

<?php if ($step + 1 < $totalSteps): ?>
<button style="height:30px;width:150px;border-radius:6px;background:black;color:white;" type="submit">Vote ➡</button>
<button style="height:30px;width:150px;border-radius:6px;background:black;color:white;" type="submit" name="skip">Skip ⏭</button>
<?php else: ?>
<button style="height:30px;width:150px;border-radius:6px;background:black;color:white;" type="submit">✅ Submit Vote</button>
<button style="height:30px;width:150px;border-radius:6px;background:black;color:white;" type="submit" name="skip">Skip & Submit Vote</button>
<?php endif; ?>

</form>
</div>
</body>
</html>




