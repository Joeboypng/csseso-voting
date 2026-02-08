<?php
require_once '../config/db.php';
session_start();

$abuses = $conn->query("
    SELECT va.*, v.index_number
    FROM voter_abuse va
    JOIN voters v ON v.id = va.voter_id
    ORDER BY va.created_at DESC
")->fetchAll();
?>

<h2>Voter Abuse Logs</h2>

<table border="1">
<tr>
<th>Student ID</th>
<th>Reason</th>
<th>IP</th>
<th>Action</th>
</tr>

<?php foreach ($abuses as $a): ?>
<tr>
<td><?= $a['index_number'] ?></td>
<td><?= $a['reason'] ?></td>
<td><?= $a['ip_address'] ?></td>
<td>
<a href="unlock.php?voter=<?= $a['voter_id'] ?>&ip=<?= $a['ip_address'] ?>">
Unlock
</a>
</td>
</tr>
<?php endforeach; ?>
</table>
