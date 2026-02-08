<?php
require_once '../includes/security.php';
require_once '../config/db.php';
admin_guard();

/* =========================
   ADD VOTER
========================= */
if (isset($_POST['add_voter'])) {

    $index = trim($_POST['index_number']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    if ($index && ($phone || $email)) {

        // CHECK FOR DUPLICATES
        $check = $conn->prepare(
            "SELECT id FROM voters
             WHERE index_number = ?
                OR phone = ?
                OR email = ?"
        );

        $check->execute([
            $index,
            $phone ?: '',
            $email ?: ''
        ]);

        if ($check->rowCount() > 0) {
            $_SESSION['error'] = "Voter already exists";
        } else {

            $stmt = $conn->prepare(
                "INSERT INTO voters (index_number, phone, email)
                 VALUES (?, ?, ?)"
            );

            $stmt->execute([
                $index,
                $phone ?: null,
                $email ?: null
            ]);

            $_SESSION['success'] = "Voter added successfully";
        }
    } else {
        $_SESSION['error'] = "Index number and at least one contact is required";
    }

    header("Location: voters.php");
    exit;
}

/* =========================
   DELETE VOTER
========================= */
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM voters WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = "Voter deleted successfully";
    header("Location: voters.php");
    exit;
}

/* =========================
   FETCH VOTER FOR EDIT
========================= */
$editVoter = null;

if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];

    $stmt = $conn->prepare("SELECT * FROM voters WHERE id = ?");
    $stmt->execute([$id]);
    $editVoter = $stmt->fetch();
}

/* =========================
   UPDATE VOTER
========================= */
if (isset($_POST['update_voter'])) {

    $id = (int) $_POST['id'];
    $index = trim($_POST['index_number']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);

    if ($index && ($phone || $email)) {

        // CHECK FOR DUPLICATES (EXCEPT CURRENT VOTER)
        $check = $conn->prepare(
            "SELECT id FROM voters
             WHERE (index_number = ? OR phone = ? OR email = ?)
             AND id != ?"
        );

        $check->execute([
            $index,
            $phone ?: '',
            $email ?: '',
            $id
        ]);

        if ($check->rowCount() > 0) {
            $_SESSION['error'] = "Another voter already uses these details";
        } else {

            $stmt = $conn->prepare(
                "UPDATE voters
                 SET index_number = ?, phone = ?, email = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $index,
                $phone ?: null,
                $email ?: null,
                $id
            ]);

            $_SESSION['success'] = "Voter updated successfully";
        }
    } else {
        $_SESSION['error'] = "Index number and at least one contact is required";
    }

    header("Location: voters.php");
    exit;
}

/* =========================
   FETCH ALL VOTERS
========================= */
$stmt = $conn->prepare(
    "SELECT * FROM voters ORDER BY id DESC"
);
$stmt->execute();
$voters = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Eligible Voters</title>
</head>

<body style="background:#0a2a66; color:white; font-family:Arial;">

<h2 style="font-size:30px;">Manage Eligible Voters</h2>

<!-- ================= MESSAGES ================= -->
<?php if (!empty($_SESSION['error'])): ?>
    <p style="color:red; font-size:18px;">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </p>
<?php endif; ?>

<?php if (!empty($_SESSION['success'])): ?>
    <p style="color:lightgreen; font-size:18px;">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </p>
<?php endif; ?>

<!-- ================= FORM ================= -->
<form method="POST" style="margin-bottom:20px;">

    <?php if ($editVoter): ?>
        <input type="hidden" name="id" value="<?= $editVoter['id'] ?>">
    <?php endif; ?>

    <input type="text"
           name="index_number"
           placeholder="Index Number"
           value="<?= $editVoter['index_number'] ?? '' ?>"
           required
           style="height:30px;width:200px;border-radius:6px;">

    <input type="text"
           name="phone"
           placeholder="Phone (optional)"
           value="<?= $editVoter['phone'] ?? '' ?>"
           style="height:30px;width:200px;border-radius:6px;">

    <input type="email"
           name="email"
           placeholder="Email (optional)"
           value="<?= $editVoter['email'] ?? '' ?>"
           style="height:30px;width:200px;border-radius:6px;">

    <?php if ($editVoter): ?>
        <button type="submit" name="update_voter"
                style="height:32px;width:150px;border-radius:6px;">
            Update Voter
        </button>
        <a href="voters.php" style="color:white;margin-left:10px;">Cancel</a>
    <?php else: ?>
        <button type="submit" name="add_voter"
                style="height:30px;width:150px;border-radius:6px;
                       background:black;color:white;">
            Add Voter
        </button>
    <?php endif; ?>

</form>

<!-- ================= TABLE ================= -->
<table border="1" cellpadding="6"
       style="border-collapse:collapse;color:white;font-size:18px;">

<tr>
    <th>ID</th>
    <th>Index Number</th>
    <th>Phone</th>
    <th>Email</th>
    <th>Action</th>
</tr>

<?php foreach ($voters as $v): ?>
<tr>
    <td><?= $v['id'] ?></td>
    <td><?= htmlspecialchars($v['index_number']) ?></td>
    <td><?= htmlspecialchars($v['phone']) ?></td>
    <td><?= htmlspecialchars($v['email']) ?></td>
    <td>
        <a href="?edit=<?= $v['id'] ?>"><button>Edit</button></a> |
        <a href="?delete=<?= $v['id'] ?>"
           onclick="return confirm('Delete this voter?')">
           <button>Delete</button>
        </a>
    </td>
</tr>
<?php endforeach; ?>

</table>

<br>
<a href="dashboard.php">
    <button style="height:30px;width:150px;border-radius:6px;
                   background:black;color:white;">
        ← Back to Dashboard
    </button>
</a>

</body>
</html>
