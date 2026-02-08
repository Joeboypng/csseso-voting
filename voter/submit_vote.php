<?php
require_once '../config/security.php';
require_once '../config/db.php';

/* ================= SESSION ================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['voter_logged_in'], $_SESSION['voter_id'])) {
    header("Location: login.php");
    exit;
}

$voter_id = (int) $_SESSION['voter_id'];
$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

/* ================= ELECTION ================= */

$stmt = $conn->prepare("
    SELECT id 
    FROM elections 
    WHERE title = ? 
    LIMIT 1
");
$stmt->execute(['CSSESO General Election']);
$election = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$election) {
    die("❌ Election not found.");
}

$election_id = (int) $election['id'];

/* ================= VOTER CHECK ================= */

$stmt = $conn->prepare("
    SELECT has_voted 
    FROM voters 
    WHERE id = ? 
    LIMIT 1
");
$stmt->execute([$voter_id]);
$voter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$voter) {
    die("❌ Invalid voter session.");
}

if ($voter['has_voted']) {
    die("❌ You have already voted.");
}

/* ================= BLOCK CHECK (voter_abuse) ================= */

$stmt = $conn->prepare("
    SELECT id 
    FROM voter_abuse 
    WHERE voter_id = ? 
    LIMIT 1
");
$stmt->execute([$voter_id]);

if ($stmt->fetch()) {
    die("❌ You are blocked due to suspicious activity. Contact EC on 0536764848.");
}

/* ================= IP RESTRICTION (PRODUCTION ONLY) ================= */

if (ENABLE_IP_RESTRICTION) {

    $stmt = $conn->prepare("
        SELECT id 
        FROM votes_ip 
        WHERE ip_address = ? 
          AND election_id = ? 
        LIMIT 1
    ");
    $stmt->execute([$ip, $election_id]);

    if ($stmt->fetch()) {

        if (ENABLE_ABUSE_LOCK) {
            $conn->prepare("
                INSERT INTO voter_abuse (voter_id, reason, ip_address)
                VALUES (?, ?, ?)
            ")->execute([
                $voter_id,
                'IP already used for voting',
                $ip
            ]);
        }

        die("❌ This device has already voted. Contact EC on 0536764848.");
    }
}

/* ================= TRANSACTION ================= */

$conn->beginTransaction();

try {

    /* SAVE VOTES */
    foreach ($_SESSION['votes'] ?? [] as $portfolio => $candidate_id) {

        $stmt = $conn->prepare("
            INSERT INTO votes 
            (voter_id, candidate_id, portfolio, election_id, ip_address)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $voter_id,
            (int)$candidate_id,
            $portfolio,
            $election_id,
            $ip
        ]);
    }

    /* SAVE SKIPPED */
    foreach ($_SESSION['skipped'] ?? [] as $portfolio => $v) {

        $conn->prepare("
            INSERT INTO skipped_votes 
            (voter_id, portfolio, election_id)
            VALUES (?, ?, ?)
        ")->execute([
            $voter_id,
            $portfolio,
            $election_id
        ]);
    }

    /* MARK IP USED (PRODUCTION ONLY) */
    if (ENABLE_IP_RESTRICTION) {
        $conn->prepare("
            INSERT INTO votes_ip (ip_address, voter_id, election_id)
            VALUES (?, ?, ?)
        ")->execute([
            $ip,
            $voter_id,
            $election_id
        ]);
    }

    /* LOCK VOTER */
    $conn->prepare("
        UPDATE voters 
        SET has_voted = 1 
        WHERE id = ?
    ")->execute([$voter_id]);

    $conn->commit();

    session_unset();
    session_destroy();

    echo "<h2>✅ Vote submitted successfully</h2>";
    echo "<p>Thank you for participating.</p>";

} catch (Exception $e) {

    $conn->rollBack();

    if (ENABLE_ABUSE_LOCK) {
        $conn->prepare("
            INSERT INTO voter_abuse (voter_id, reason, ip_address)
            VALUES (?, ?, ?)
        ")->execute([
            $voter_id,
            'Vote submission failure',
            $ip
        ]);
    }

    die("❌ Error submitting vote. Please contact EC.");
}




