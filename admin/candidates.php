<?php
require_once '../includes/security.php';
require_once '../config/db.php';
admin_guard();

/* GET CSSESO ELECTION ID */
$stmt = $conn->prepare(
    "SELECT id FROM elections WHERE title = ? LIMIT 1"
);
$stmt->execute(['CSSESO General Election']);
$election = $stmt->fetch();

if (!$election) {
    die("CSSESO General Election not found. Please add it to the elections table.");
}

$election_id = $election['id'];


// ADD CANDIDATE
if (isset($_POST['add_candidate'])) {

    $name = trim($_POST['full_name']);
    $portfolio = $_POST['portfolio'];

    // IMAGE UPLOAD
    $photoName = $_FILES['photo']['name'];
    $tmpName   = $_FILES['photo']['tmp_name'];

    $ext = pathinfo($photoName, PATHINFO_EXTENSION);
    $newName = uniqid('cand_', true) . "." . $ext;
    $uploadPath = "../uploads/candidates/" . $newName;

    if ($name && $portfolio && move_uploaded_file($tmpName, $uploadPath)) {

        $stmt = $conn->prepare(
            "INSERT INTO candidates (full_name, portfolio, photo, election_id)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            $name,
            $portfolio,
            $newName,
            $election_id
        ]);
    }
}


// DELETE CANDIDATE
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("SELECT photo FROM candidates WHERE id = ?");
    $stmt->execute([$id]);
    $photo = $stmt->fetchColumn();

    if ($photo && file_exists("../uploads/candidates/$photo")) {
        unlink("../uploads/candidates/$photo");
    }

    $stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
    $stmt->execute([$id]);
}


// FETCH CANDIDATES
$stmt = $conn->prepare(
    "SELECT * FROM candidates WHERE election_id = ?"
);
$stmt->execute([$election_id]);
$candidates = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Candidates</title>
</head>
<body style="background:#0F2D5E;color:white;">

<h2>Manage Candidates – CSSESO General Election</h2>

<form method="POST" enctype="multipart/form-data">

    <input style="height:30px;width:200px;border-radius:6px;"
           type="text" name="full_name"
           placeholder="Candidate Name" required>

    <select style="height:30px;width:200px;border-radius:6px;"
            name="portfolio" required>
        <option value="">-- Select Portfolio --</option>
        <option>President</option>
        <option>Vice President</option>
        <option>General Secretary</option>
        <option>Financial Secretary</option>
        <option>Organizing Secretary</option>
        <option>Women Commissioner</option>
    </select>

    <input style="height:30px;width:200px;border-radius:6px;"
           type="file" name="photo" accept="image/*" required>

    <button style="height:30px;width:150px;border-radius:6px;
                   background:black;color:white;"
            type="submit" name="add_candidate">
        Add Candidate
    </button>

</form>

<hr><br>

<h3>Existing Candidates</h3>

<table border="1" cellpadding="5" style="border-collapse:collapse;">
<tr style="font-size:18px;">
    <th>Photo</th>
    <th>Name</th>
    <th>Portfolio</th>
    <th>Action</th>
</tr>

<?php foreach ($candidates as $c): ?>
<tr>
    <td>
        <img src="../uploads/candidates/<?= htmlspecialchars($c['photo']) ?>" width="60">
    </td>
    <td><?= htmlspecialchars($c['full_name']) ?></td>
    <td><?= htmlspecialchars($c['portfolio']) ?></td>
    <td>
        <a style="color:red;"
           href="?delete=<?= $c['id'] ?>"
           onclick="return confirm('Delete candidate?')">
            Delete
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
