<?php
require_once '../config.php';

// Session is already started in config.php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy') {
    header('Location: ../auth/login.php');
    exit;
}

if (!isset($_GET['prescription_id'])) {
    die("Prescription ID is missing.");
}

$prescription_id = intval($_GET['prescription_id']);

// Fetch prescription details along with user email
$stmt = $pdo->prepare('
    SELECT p.id, p.note, p.delivery_address, p.delivery_time_slot, u.name AS user_name, u.email AS user_email
    FROM prescriptions p
    JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
');
$stmt->execute([$prescription_id]);
$prescription = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$prescription) {
    die("Prescription not found.");
}

// Fetch prescription images
$stmtImgs = $pdo->prepare('SELECT image_path FROM prescription_images WHERE prescription_id = ?');
$stmtImgs->execute([$prescription_id]);
$images = $stmtImgs->fetchAll(PDO::FETCH_ASSOC);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add item
    if (isset($_POST['add_item'])) {
        $drug_name = trim($_POST['drug_name']);
        $quantity = intval($_POST['quantity']);
        $price = floatval($_POST['price']);
        $amount = $quantity * $price;

        if (!isset($_SESSION['quotation_items'])) {
            $_SESSION['quotation_items'] = [];
        }

        $_SESSION['quotation_items'][] = [
            'drug_name' => $drug_name,
            'quantity' => $quantity,
            'price' => $price,
            'amount' => $amount
        ];

        header("Location: send_quotation.php?prescription_id=$prescription_id");
        exit;
    }

    // Send quotation
    if (isset($_POST['send_quotation'])) {
        $pharmacy_id = $_SESSION['user_id'];
        $items = $_SESSION['quotation_items'] ?? [];

        if (empty($items)) {
            $error = "Please add at least one item.";
        } else {
            $total_amount = array_sum(array_column($items, 'amount'));

            try {
                // Insert quotation
                $stmt = $pdo->prepare('
                    INSERT INTO quotations (prescription_id, pharmacy_id, total_amount, status, created_at)
                    VALUES (?, ?, ?, "pending", NOW())
                ');
                $stmt->execute([$prescription_id, $pharmacy_id, $total_amount]);
                $quotation_id = $pdo->lastInsertId();

                // Insert items
                $stmtItem = $pdo->prepare('INSERT INTO quotation_items (quotation_id, drug_name, quantity, amount) VALUES (?, ?, ?, ?)');
                foreach ($items as $it) {
                    $stmtItem->execute([$quotation_id, $it['drug_name'], $it['quantity'], $it['amount']]);
                }

                // Send email to the prescription owner (user)
                $to = $prescription['user_email'];
                $subject = "New Quotation Received";
                $message = "Hello " . $prescription['user_name'] . ",\n\n";
                $message .= "You have received a new quotation for your prescription (ID: $prescription_id).\n";
                $message .= "Total Amount: $" . number_format($total_amount, 2) . "\n";
                $message .= "\nPlease login to your account to view and accept or reject the quotation.";
                $headers = "From: no-reply@yourdomain.com";

                @mail($to, $subject, $message, $headers);

                // Clear session items
                unset($_SESSION['quotation_items']);

                header("Location: ../user/view_quotations.php?new_quotation=1");
                exit;

            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Load existing items in session
$items = $_SESSION['quotation_items'] ?? [];
$total_amount = array_sum(array_column($items, 'amount'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Send Quotation</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
.image-large { width: 100%; border-radius: 0.5rem; object-fit: contain; }
.image-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; margin-top: 0.5rem; }
a.back {
    display: inline-block;
    margin-bottom: 1rem;
    font-weight: 600;
    text-decoration: none;
    color: #fff;
    background: #457AA8;
    padding: 0.6rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s;
}
a.back:hover { background: #45A86F; transform: scale(1.05); }
</style>
</head>
<body class="bg-gradient-to-r from-cyan-50 via-blue-50 to-purple-50 min-h-screen p-6">

<a href="view_prescriptions.php" class="back">← Back to Dashboard</a>

<div class="max-w-6xl mx-auto bg-white p-8 rounded-2xl shadow-lg grid md:grid-cols-2 gap-6">

    <!-- Left: Prescription Images -->
    <div>
        <?php if (!empty($images)): ?>
            <img src="../assets/uploads/<?= htmlspecialchars($images[0]['image_path']) ?>" class="w-full max-h-80 object-contain rounded mb-4" alt="Prescription Image">
            <?php if (count($images) > 1): ?>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <?php for ($i = 1; $i < count($images); $i++): ?>
                        <img src="../assets/uploads/<?= htmlspecialchars($images[$i]['image_path']) ?>" class="w-full h-24 object-cover rounded" alt="Prescription Image">
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p>No prescription images available.</p>
        <?php endif; ?>
    </div>

    <!-- Right: Quotation Table & Form -->
    <div>
        <h2 class="text-2xl font-bold mb-4">Quotation for <?=htmlspecialchars($prescription['user_name'])?></h2>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-800 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <table class="w-full mb-4 border">
            <thead class="bg-blue-500 text-white">
                <tr>
                    <th class="p-2">Drug</th>
                    <th class="p-2">Qty</th>
                    <th class="p-2">Price</th>
                    <th class="p-2">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr class="text-center border-b">
                        <td class="p-2"><?= htmlspecialchars($it['drug_name']) ?></td>
                        <td class="p-2"><?= $it['quantity'] ?></td>
                        <td class="p-2">$<?= number_format($it['price'],2) ?></td>
                        <td class="p-2">$<?= number_format($it['amount'],2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="font-bold text-right mb-4">Total: $<?= number_format($total_amount,2) ?></p>

        <!-- Add Item Form -->
        <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <input type="text" name="drug_name" placeholder="Drug" required class="p-2 border rounded-lg focus:ring-2 focus:ring-indigo-400">
            <input type="number" name="quantity" placeholder="Qty" min="1" required class="p-2 border rounded-lg focus:ring-2 focus:ring-indigo-400">
            <input type="number" name="price" placeholder="Price" step="0.01" min="0.01" required class="p-2 border rounded-lg focus:ring-2 focus:ring-indigo-400">
            <button type="submit" name="add_item" class="bg-green-500 hover:bg-green-600 text-white rounded-lg px-4 py-2 font-semibold transition">
                Add
            </button>
        </form>

        <!-- Send Quotation -->
        <form method="POST">
            <button type="submit" name="send_quotation" class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-3 rounded-xl text-lg font-semibold shadow hover:scale-105 transition">
                Send Quotation
            </button>
        </form>

    </div>
</div>
</body>
</html>
