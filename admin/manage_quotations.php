<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Delete quotation if delete param set
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM quotations WHERE id = ?')->execute([$del_id]);
    header("Location: manage_quotations.php");
    exit;
}

$stmt = $pdo->query('
SELECT q.id, q.total_amount, q.status, q.created_at, u.name as pharmacy_name, p.note
FROM quotations q
JOIN users u ON q.pharmacy_id = u.id
JOIN prescriptions p ON q.prescription_id = p.id
ORDER BY q.created_at DESC
');

$quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Manage Quotations</title>
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
    background: #CFCCAE;
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    max-width: 1000px;
    margin: 0 auto;
    animation: fadeIn 0.6s ease-in-out;
  }

  @keyframes fadeIn {
    from {opacity: 0; transform: translateY(20px);}
    to {opacity: 1; transform: translateY(0);}
  }

  h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-align: center;
    color: #1D1D1D;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 0.75rem;
    overflow: hidden;
    background: #fff;
  }

  thead {
    background: #457AA8;
    color: #fff;
  }

  th, td {
    padding: 0.9rem 1rem;
    text-align: left;
    font-size: 0.95rem;
  }

  tr:nth-child(even) { background: #F9F9F9; }
  tr:hover { background: #EEF6F1; transition: 0.2s; }

  .back {
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

  .back:hover {
    background: #45A86F;
    transform: scale(1.05);
  }

  .delete-btn {
    color: #fff;
    background: #E63946;
    padding: 0.4rem 0.8rem;
    border-radius: 0.5rem;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: 0.3s;
  }

  .delete-btn:hover {
    background: #C53030;
  }
</style>
</head>
<body>
  <div class="card">
    <a href="dashboard.php" class="back">← Back to Dashboard</a>
    <h2>Manage Quotations</h2>
    <table>
      <thead>
        <tr>
          <th>Pharmacy</th>
          <th>Prescription Note</th>
          <th>Total Amount</th>
          <th>Status</th>
          <th>Created At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($quotations as $q): ?>
        <tr>
          <td><?=htmlspecialchars($q['pharmacy_name'])?></td>
          <td><?=htmlspecialchars($q['note'])?></td>
          <td>$<?=number_format($q['total_amount'], 2)?></td>
          <td class="capitalize"><?=htmlspecialchars($q['status'])?></td>
          <td><?=htmlspecialchars($q['created_at'])?></td>
          <td>
            <a href="manage_quotations.php?delete=<?= $q['id'] ?>" 
               onclick="return confirm('Are you sure to delete this quotation?');" 
               class="delete-btn">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
