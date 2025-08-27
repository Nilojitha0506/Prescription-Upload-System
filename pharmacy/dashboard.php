<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy') {
    header('Location: ../auth/login.php');
    exit;
}

// Fetch pharmacy name
$pharmacyName = '';
$stmt = $pdo->prepare('SELECT name FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
if ($row = $stmt->fetch()) {
    $pharmacyName = htmlspecialchars($row['name']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pharmacy Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(120deg, #f6f6f6ff, #436dc8ff);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
  }

  .container {
    display: flex;
    flex-wrap: wrap;
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(15px);
    border-radius: 2rem;
    box-shadow: 0 20px 50px rgba(0,0,0,0.25);
    max-width: 1100px;
    overflow: hidden;
  }

  .left-panel {
    flex: 1 1 300px;
    background: rgba(255,255,255,0.1);
    padding: 3rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-width: 280px;
  }

  .left-panel h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #090909ff;
  }

  .left-panel p {
    color: #090909ff;
    font-size: 1rem;
    text-align: center;
  }

  .logout-btn {
    margin-top: 2rem;
    padding: 0.7rem 1.5rem;
    border-radius: 0.75rem;
    background: #EF4444;
    color: #fff;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
  }

  .logout-btn:hover {
    transform: scale(1.05);
    background: #F87171;
  }

  .right-panel {
    flex: 2 1 600px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 2rem;
    padding: 3rem;
  }

  .card {
    position: relative;
    padding: 2rem;
    border-radius: 1rem;
    color: #fff;
    text-align: center;
    cursor: pointer;
    clip-path: polygon(0 0, 100% 0, 100% 85%, 50% 100%, 0 85%);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }

  .card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.35);
  }

  .card img {
    width: 50px;
    height: 50px;
    margin-bottom: 1rem;
  }

  .card h2 {
    font-weight: 700;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
  }

  .card p {
    font-size: 0.9rem;
  }

  /* Different card backgrounds */
  .card-prescriptions { background: #34D399; }
  .card-profile { background: #3B82F6; }
  .card-quotation { background: #8B5CF6; }
</style>
</head>
<body>
<div class="container">
  <!-- Left panel -->
  <div class="left-panel">
    <h1>Welcome, <?= $pharmacyName ?>!</h1>
    <p>Manage your prescriptions, profile, and send quotations efficiently.</p>
    <a href="../auth/logout.php" class="logout-btn">Logout</a>
  </div>

  <!-- Right panel -->
  <div class="right-panel">
    <a href="view_prescriptions.php" class="card card-prescriptions">
      <img src="https://img.icons8.com/ios-filled/50/ffffff/pill.png" alt="Prescriptions">
      <h2>View Prescriptions</h2>
      <p>Check all prescriptions submitted by users.</p>
    </a>

    <a href="profile.php" class="card card-profile">
      <img src="https://img.icons8.com/ios-filled/50/ffffff/user.png" alt="Profile">
      <h2>My Profile</h2>
      <p>Update your pharmacy details or contact info.</p>
    </a>
  </div>
</div>
</body>
</html>
