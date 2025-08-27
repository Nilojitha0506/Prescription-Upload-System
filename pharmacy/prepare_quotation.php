<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy') {
    header('Location: ../auth/login.php');
    exit;
}

if (!isset($_GET['prescription_id'])) {
    header('Location: view_prescriptions.php');
    exit;
}

$prescription_id = (int)$_GET['prescription_id'];

// Fetch prescription and user info
$stmt = $pdo->prepare('
SELECT p.*, u.name as user_name, u.email as user_email
FROM prescriptions p
JOIN users u ON p.user_id = u.id
WHERE p.id = ?
');
$stmt->execute([$prescription_id]);
$prescription = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prescription) {
    die('Prescription not found.');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $drugs = $_POST['drug_name'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $amounts = $_POST['amount'] ?? [];

    if (empty($drugs) || empty($quantities) || empty($amounts)) {
        $errors[] = "Please fill quotation details properly.";
    } else {
        $total = 0;
        for ($i = 0; $i < count($drugs); $i++) {
            if (empty(trim($drugs[$i])) || intval($quantities[$i]) <= 0 || floatval($amounts[$i]) < 0) {
                $errors[] = "All drug details must be valid.";
                break;
            }
            $total += intval($quantities[$i]) * floatval($amounts[$i]);
        }
    }

    if (empty($errors)) {
        // Insert quotation
        $stmt = $pdo->prepare('INSERT INTO quotations (prescription_id, pharmacy_id, total_amount) VALUES (?, ?, ?)');
        $stmt->execute([$prescription_id, $_SESSION['user_id'], $total]);
        $quotation_id = $pdo->lastInsertId();

        // Insert quotation items
        $stmtItems = $pdo->prepare('INSERT INTO quotation_items (quotation_id, drug_name, quantity, amount) VALUES (?, ?, ?, ?)');
        for ($i = 0; $i < count($drugs); $i++) {
            $stmtItems->execute([
                $quotation_id,
                trim($drugs[$i]),
                intval($quantities[$i]),
                floatval($amounts[$i])
            ]);
        }

        header('Location: send_quotation.php?id='.$quotation_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Prepare Quotation</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet" />
<script>
function addRow() {
    const table = document.getElementById('quotation-items');
    const rowCount = table.rows.length;
    if (rowCount >= 10) return; // max 10 rows
    const row = table.insertRow(rowCount);
    row.innerHTML = `
        <td><input type="text" name="drug_name[]" required class="border p-1 w-full" /></td>
        <td><input type="number" name="quantity[]" min="1" required class="border p-1 w-full" /></td>
        <td><input type="number" name="amount[]" step="0.01" min="0" required class="border p-1 w-full" /></td>
        <td><button type="button" onclick="removeRow(this)" class="text-red-600 font-bold">X</button></td>
    `;
}
function removeRow(btn) {
    const row = btn.parentNode.parentNode;
    row.parentNode.removeChild(row);
}
window.onload = function() {
    addRow();
};
</script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
<a href="view_prescriptions.php" class="text-blue-700 hover:underline mb-6 inline-block">← Back to Prescriptions</a>

<h2 class="text-2xl font-semibold mb-4">Prepare Quotation for Prescription #<?= $prescription_id ?></h2>

<p><strong>User:</strong> <?=htmlspecialchars($prescription['user_name'])?></p>
<p><strong>Note:</strong> <?=htmlspecialchars($prescription['note'])?></p>

<?php if (!empty($errors)): ?>
    <div class="bg-red-200 p-3 mb-4 rounded text-red-800">
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?=htmlspecialchars($e)?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="POST">
    <table id="quotation-items" class="mb-4 table-auto border-collapse w-full">
        <thead>
            <tr>
                <th class="border p-2 text-left">Drug</th>
                <th class="border p-2 text-left">Quantity</th>
                <th class="border p-2 text-left">Amount</th>
                <th class="border p-2">Remove</th>
            </tr>
        </thead>
        <tbody>
            <!-- Dynamic rows go here -->
        </tbody>
    </table>
    <button type="button" onclick="addRow()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mb-4">Add Drug</button>
    <br />
    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">Send Quotation</button>
</form>
</body>
</html>
