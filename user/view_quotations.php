<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle status update from query parameters
$success = '';
if (isset($_GET['id'], $_GET['action'])) {
    $quotation_id = intval($_GET['id']);
    $action = $_GET['action'] === 'accepted' ? 'accepted' : ($_GET['action'] === 'rejected' ? 'rejected' : '');
    if ($action) {
        $stmtUpdate = $pdo->prepare('UPDATE quotations q
                                     JOIN prescriptions p ON q.prescription_id = p.id
                                     SET q.status = ?
                                     WHERE q.id = ? AND p.user_id = ?');
        $stmtUpdate->execute([$action, $quotation_id, $user_id]);
        if ($stmtUpdate->rowCount()) {
            $success = "Quotation has been " . ucfirst($action) . ".";
        }
    }
}

// Fetch quotations for this user
$stmt = $pdo->prepare('
SELECT q.id, q.total_amount, q.status, q.created_at, p.note, q.pharmacy_id
FROM quotations q
JOIN prescriptions p ON q.prescription_id = p.id
WHERE p.user_id = ?
ORDER BY q.created_at DESC
');
$stmt->execute([$user_id]);
$quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>View Quotations</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-cyan-50 via-blue-50 to-purple-50 min-h-screen p-6">

<a href="dashboard.php" class="inline-block mb-6 px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg shadow hover:bg-blue-700 hover:shadow-lg transition">
    ← Back to Dashboard
</a>


<h2 class="text-3xl font-semibold mb-6">Your Quotations</h2>

<?php if ($success): ?>
    <div class="bg-green-100 p-4 mb-4 rounded text-green-800 border border-green-200"><?= $success ?></div>
<?php endif; ?>

<?php if (empty($quotations)): ?>
    <p class="text-gray-600">No quotations available yet.</p>
<?php else: ?>
    <table class="min-w-full bg-white rounded-2xl shadow overflow-hidden">
        <thead class="bg-blue-100 text-gray-700">
            <tr>
                <th class="py-3 px-4 text-left">Prescription Note</th>
                <th class="py-3 px-4 text-left">Total Amount</th>
                <th class="py-3 px-4 text-left">Status</th>
                <th class="py-3 px-4 text-left">Created At</th>
                <th class="py-3 px-4 text-left">Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($quotations as $q): ?>
            <tr class="border-t hover:bg-gray-50 transition">
                <td class="py-3 px-4"><?= htmlspecialchars($q['note']) ?></td>
                <td class="py-3 px-4 font-medium">$<?= number_format($q['total_amount'], 2) ?></td>
                <td class="py-3 px-4 capitalize font-semibold"><?= htmlspecialchars($q['status']) ?></td>
                <td class="py-3 px-4"><?= htmlspecialchars($q['created_at']) ?></td>
                <td class="py-3 px-4 flex gap-2">
                    <?php if ($q['status'] === 'pending'): ?>
                        <a href="?id=<?= $q['id'] ?>&action=accepted" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition">Accept</a>
                        <a href="?id=<?= $q['id'] ?>&action=rejected" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition">Reject</a>
                    <?php else: ?>
                        <span class="text-gray-500 italic">No Action</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
