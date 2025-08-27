<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Fetch admin name
$stmt = $pdo->prepare('SELECT name FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
$adminName = $admin ? $admin['name'] : 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #45A86F, #457AA8, #CFCCAE, #F7D794);
    background-size: 400% 400%;
    animation: gradientBG 10s ease infinite;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
  }

  @keyframes gradientBG {
    0% {background-position: 0% 50%;}
    50% {background-position: 100% 50%;}
    100% {background-position: 0% 50%;}
  }

  .dashboard-container {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(12px);
    border-radius: 1.5rem;
    padding: 3rem;
    text-align: center;
    max-width: 900px;
    width: 90%;
    box-shadow: 0 15px 40px rgba(0,0,0,0.25);
  }

  h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1D1D1D;
    margin-bottom: 2rem;
  }

  .cards {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 2rem;
  }

  .card {
    background: rgba(255,255,255,0.3);
    backdrop-filter: blur(8px);
    padding: 2rem;
    width: 220px;
    border-radius: 1rem;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-decoration: none;
  }

  .card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.35);
  }

  .card h2 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 1rem;
    color: #1D1D1D;
  }

  .card p {
    margin-top: 0.5rem;
    font-size: 0.9rem;
    color: #1D1D1D;
  }

  .logout-btn {
    display: inline-block;
    margin-top: 2rem;
    padding: 0.7rem 1.5rem;
    background: #ef4444;
    color: #fff;
    font-weight: 600;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
  }

  .logout-btn:hover {
    background: #f87171;
    transform: scale(1.05);
  }
</style>
</head>
<body>

<div class="dashboard-container">
  <h1>Welcome, Admin <?= htmlspecialchars($adminName) ?>!</h1>
  
  <div class="cards">
    <a href="manage_users.php" class="card">
      <img src="https://img.icons8.com/ios-filled/50/000000/user-group-man-man.png" class="mx-auto" alt="Users">
      <h2>Manage Users</h2>
      <p>View, add, edit, or remove users easily.</p>
    </a>
    <a href="manage_prescriptions.php" class="card">
      <img src="https://img.icons8.com/ios-filled/50/000000/pill.png" class="mx-auto" alt="Prescriptions">
      <h2>Manage Prescriptions</h2>
      <p>Check all prescriptions uploaded by users.</p>
    </a>
    <a href="manage_quotations.php" class="card">
      <img src="https://img.icons8.com/ios-filled/50/000000/price-tag.png" class="mx-auto" alt="Quotations">
      <h2>Manage Quotations</h2>
      <p>Approve or delete pharmacy quotations.</p>
    </a>
  </div>

  <a href="../auth/logout.php" class="logout-btn">Logout</a>
</div>

</body>
</html>
