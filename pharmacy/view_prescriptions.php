<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy') {
    header('Location: ../auth/login.php');
    exit;
}

// Fetch all prescriptions with user info
$stmt = $pdo->query('
SELECT p.id, p.note, p.delivery_address, p.delivery_time_slot, p.created_at, u.name as user_name
FROM prescriptions p
JOIN users u ON p.user_id = u.id
ORDER BY p.created_at DESC
');
$prescriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>View Prescriptions</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #45A86F, #457AA8);
    background-size: 400% 400%;
    animation: gradientBG 10s ease infinite;
    min-height: 100vh;
    margin: 0;
    padding: 2rem;
    color: #1D1D1D;
}
@keyframes gradientBG {
    0% {background-position: 0% 50%;}
    50% {background-position: 100% 50%;}
    100% {background-position: 0% 50%;}
}
.card {
    background: #fff;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}
h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-align: center;
}
table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 0.75rem;
    overflow: hidden;
}
thead {
    background: #457AA8;
    color: #fff;
}
th, td {
    padding: 0.9rem 1rem;
    text-align: left;
    font-size: 0.95rem;
    vertical-align: middle;
}
tr:nth-child(even) { background: #F9F9F9; }
tr:hover { background: #5f6261ff; transition: 0.2s; }
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
a.back:hover {
    background: #45A86F;
    transform: scale(1.05);
}
a.send-btn {
    color: #fff;
    background: #F7971E;
    padding: 0.4rem 0.8rem;
    border-radius: 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: 0.3s;
}
a.send-btn:hover {
    background: #DD6B20;
}
.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
}
.status-pending { background: #facc15; color: #1f2937; }
.status-accepted { background: #22c55e; color: #fff; }
.status-rejected { background: #ef4444; color: #fff; }
.image-thumb {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 0.5rem;
    margin-right: 0.3rem;
    border: 1px solid #ccc;
    transition: transform 0.3s ease;
}
.image-thumb:hover {
    transform: scale(1.2);
}
</style>
</head>
<body>
  <a href="dashboard.php" class="back">← Back to Dashboard</a>
  <div class="card">
    <h2>Uploaded Prescriptions</h2>
    <table>
      <thead>
        <tr>
          <th>Prescription ID</th>
          <th>User</th>
          <th>Note</th>
          <th>Delivery Address</th>
          <th>Delivery Time Slot</th>
          <th>Images</th>
          <th>Quotation Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($prescriptions as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['id']) ?></td>
          <td><?= htmlspecialchars($p['user_name']) ?></td>
          <td><?= htmlspecialchars($p['note']) ?></td>
          <td><?= htmlspecialchars($p['delivery_address']) ?></td>
          <td><?= htmlspecialchars($p['delivery_time_slot']) ?></td>
          <td>
            <?php 
            $stmtImgs = $pdo->prepare('SELECT image_path FROM prescription_images WHERE prescription_id = ?');
            $stmtImgs->execute([$p['id']]);
            $images = $stmtImgs->fetchAll(PDO::FETCH_ASSOC);
            foreach ($images as $img): ?>
                <img src="../assets/uploads/<?= htmlspecialchars($img['image_path']) ?>" class="image-thumb" alt="Prescription Image" />
            <?php endforeach; ?>
          </td>
          <td>
            <?php 
            $stmtQuote = $pdo->prepare('SELECT status FROM quotations WHERE prescription_id = ? ORDER BY created_at DESC LIMIT 1');
            $stmtQuote->execute([$p['id']]);
            $quote = $stmtQuote->fetch(PDO::FETCH_ASSOC);
            $status = $quote['status'] ?? 'pending';
            $statusClass = $status === 'accepted' ? 'status-accepted' : ($status === 'rejected' ? 'status-rejected' : 'status-pending');
            ?>
            <span class="status-badge <?= $statusClass ?>"><?= ucfirst($status) ?></span>
          </td>
          <td>
            <a href="send_quotation.php?prescription_id=<?= $p['id'] ?>" class="send-btn">Send Quotation</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
