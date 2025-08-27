<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}

if (!isset($_GET['id'], $_GET['action'])) {
    die("Invalid request.");
}

$quotation_id = intval($_GET['id']);
$action = $_GET['action'] === 'accepted' ? 'accepted' : ($_GET['action'] === 'rejected' ? 'rejected' : null);

if (!$action) {
    die("Invalid action.");
}

$user_id = $_SESSION['user_id'];

// Ensure the quotation belongs to this user
$stmt = $pdo->prepare('
SELECT q.id 
FROM quotations q
JOIN prescriptions p ON q.prescription_id = p.id
WHERE q.id = ? AND p.user_id = ?
');
$stmt->execute([$quotation_id, $user_id]);
$quotation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quotation) {
    die("Quotation not found or access denied.");
}

// Update the quotation status
$stmt = $pdo->prepare('UPDATE quotations SET status = ? WHERE id = ?');
$stmt->execute([$action, $quotation_id]);

// Redirect back with a success flag
header("Location: view_quotations.php?status_updated=1");
exit;
