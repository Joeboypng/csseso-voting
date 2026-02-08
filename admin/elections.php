<?php
require_once '../includes/security.php';
require_once '../config/db.php';
admin_guard();

/* GET ELECTION */
$election = $conn->query(
    "SELECT * FROM elections WHERE title='CSSESO General Election' LIMIT 1"
)->fetch();

if (!$election) {
    die("Election not found.");
}

$election_id = $election['id'];

/* OPEN / CLOSE ELECTION */
if (isset($_POST['toggle'])) {
    $newStatus = $election['status'] === 'open' ? 'closed' : 'open';
    $stmt = $conn->prepare("UPDATE elections SET status=? WHERE id=?");
    $stmt->execute([$newStatus, $election_id]);
    header("Location: elections.php");
    exit;
}

/* STATS */
$totalVoters = $conn->query(
    "SELECT COUNT(*) FROM voters"
)->fetchColumn();

/* voters who either voted OR skipped at least one portfolio */
$voted = $conn->query(
    "SELECT COUNT(DISTINCT voter_id) 
     FROM (
        SELECT voter_id FROM votes
        UNION
        SELECT voter_id FROM skipped_votes
     ) x"
)->fetchColumn();

$totalSkipped = $conn->query(
    "SELECT COUNT(*) FROM skipped_votes WHERE election_id=$election_id"
)->fetchColumn();

/* SKIPPED PER PORTFOLIO */
$skippedPerPortfolio = $conn->query(
    "SELECT portfolio, COUNT(*) total
     FROM skipped_votes
     WHERE election_id=$election_id
     GROUP BY portfolio
     ORDER BY portfolio"
)->fetchAll();

/* RESULTS */
$results = $conn->query(
    "SELECT c.portfolio, c.full_name, c.photo, COUNT(v.id) total
     FROM candidates c
     LEFT JOIN votes v ON v.candidate_id = c.id
     WHERE c.election_id = $election_id
     GROUP BY c.id
     ORDER BY c.portfolio, total DESC"
)->fetchAll();
?>

<center>
<h2 style="font-size:50px;background:linear-gradient(135deg,#0a1cff,#1226ff);color:white;">
🗳 CSSESO Election Dashboard
</h2>
</center>

<form method="POST">
    <button name="toggle"
        style="height:35px;width:180px;border-radius:6px;background:black;color:white;">
        <?= $election['status']=='open' ? '🔒 Close Election' : '🔓 Open Election' ?>
    </button>
</form>

<p><b>Status:</b> <?= strtoupper($election['status']) ?></p>
<p><b>Voted / Registered:</b> <?= $voted ?> / <?= $totalVoters ?></p>
<p><b>Total Skipped Actions:</b> <?= $totalSkipped ?></p>

<hr>

<h3>Skipped per Portfolio</h3>
<table border="1" cellpadding="8">
<tr>
    <th>Portfolio</th>
    <th>Skipped</th>
</tr>
<?php foreach ($skippedPerPortfolio as $s): ?>
<tr>
    <td><?= htmlspecialchars($s['portfolio']) ?></td>
    <td><b><?= $s['total'] ?></b></td>
</tr>
<?php endforeach; ?>
</table>

<hr>

<h2>RESULTS BY PORTFOLIO</h2>

<?php
$current = '';
foreach ($results as $r):
    if ($current !== $r['portfolio']):
        if ($current !== '') echo "<hr>";
        $current = $r['portfolio'];
        echo "<h3>".htmlspecialchars($current)."</h3>";
    endif;
?>
<div style="display:flex;align-items:center;margin:8px 0">
    <img src="../uploads/candidates/<?= htmlspecialchars($r['photo']) ?>" width="50">
    <span style="margin-left:10px">
        <?= htmlspecialchars($r['full_name']) ?> — <b><?= $r['total'] ?></b> votes
    </span>
</div>
<?php endforeach; ?>

<br>
<button onclick="window.print()"
    style="height:35px;width:180px;border-radius:6px;background:blue;color:white;">
🖨 Print Election Slip
</button>

<a href="dashboard.php"
   style="display:inline-block;padding:8px 14px;border-radius:6px;background:black;color:white;text-decoration:none;">
← Back to Dashboard
</a>
