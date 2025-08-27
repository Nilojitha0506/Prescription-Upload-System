<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Delete user if delete param set
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$del_id]);
    header("Location: manage_users.php");
    exit;
}

// Fetch all users
$stmt = $pdo->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Users</title>
<style>
body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #45A86F, #457AA8, #CFCCAE);
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
  border-radius: 1rem;
  padding: 2rem;
  max-width: 1000px;
  margin: auto;
  box-shadow: 0 15px 40px rgba(0,0,0,0.25);
  animation: fadeIn 0.6s ease-in-out;
}
@keyframes fadeIn {
  from {opacity: 0; transform: translateY(20px);}
  to {opacity: 1; transform: translateY(0);}
}

h2 {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
  text-align: center;
}

table {
  width: 100%;
  border-collapse: collapse;
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


a.delete-btn {
  color: #fff;
  background: #E63946;
  padding: 0.4rem 0.8rem;
  border-radius: 0.5rem;
  font-size: 0.85rem;
  font-weight: 600;
  text-decoration: none;
  transition: 0.3s;
}
a.delete-btn:hover {
  background: #C53030;
}
</style>
<script>
function confirmDelete(name) {
    return confirm(`Are you sure you want to delete user: ${name}?`);
}
</script>
</head>
<body>
  <a href="dashboard.php" class="back">← Back to Dashboard</a>
  <div class="card">
    <h2>Manage Users</h2>
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Created At</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><?=htmlspecialchars($u['name'])?></td>
          <td><?=htmlspecialchars($u['email'])?></td>
          <td class="capitalize"><?=htmlspecialchars($u['role'])?></td>
          <td><?=htmlspecialchars($u['created_at'])?></td>
          <td>
            <a href="manage_users.php?delete=<?= $u['id'] ?>" 
               onclick="return confirmDelete('<?= htmlspecialchars($u['name']) ?>');" 
               class="delete-btn">Delete</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
