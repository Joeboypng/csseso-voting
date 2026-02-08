<?php
require_once '../config/db.php';

$logs = $conn->query("
    SELECT o.*, v.index_number
    FROM otp_audit_logs o
    JOIN voters v ON v.id = o.voter_id
    ORDER BY o.created_at DESC
")->fetchAll();
?>

<h2>OTP Audit Logs</h2>
<table border="1">
<tr>
<th>Student ID</th>
<th>Event</th>
<th>IP</th>
<th>Date</th>
</tr>

<?php foreach ($logs as $l): ?>
<tr>
<td><?= $l['index_number'] ?></td>
<td><?= $l['event'] ?></td>
<td><?= $l['ip_address'] ?></td>
<td><?= $l['created_at'] ?></td>
</tr>
<?php endforeach; ?>
</table>
