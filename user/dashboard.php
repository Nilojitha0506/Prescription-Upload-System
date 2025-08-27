<?php
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: ../auth/login.php');
    exit;
}

$userName = '';
$stmt = $pdo->prepare('SELECT name FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
if ($row = $stmt->fetch()) {
    $userName = htmlspecialchars($row['name']);
}
?>

<!DOCTYPE html>
<html lang="en" class="bg-gradient-to-r from-cyan-100 via-blue-200 to-purple-100">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex flex-col min-h-screen font-sans text-gray-700">

  <!-- Navbar -->
  <header class="bg-white/80 backdrop-blur-md shadow-md sticky top-0 z-50 px-6 py-4 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
    <div class="flex items-center gap-4">
      <span class="text-gray-700 font-medium">Hello, <span class="text-blue-600"><?= $userName ?></span></span>
      <a href="../auth/logout.php" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition shadow-sm">Logout</a>
    </div>
  </header>

  <div class="flex flex-1 overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-white/90 backdrop-blur-md border-r border-gray-200 p-6 hidden md:block rounded-r-2xl shadow-lg">
      <div class="text-gray-500 font-semibold uppercase mb-4 tracking-wide">Menu</div>
      <ul class="space-y-4">
        <li>
          <a href="upload_prescription.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-cyan-100 hover:text-cyan-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Upload Prescription
          </a>
        </li>
        <li>
          <a href="view_quotations.php" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-indigo-100 hover:text-indigo-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><path d="M8 14s1.5-2 4-2 4 2 4 2"/>
            </svg>
            View Quotations
          </a>
        </li>
      </ul>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-auto">
      <h2 class="text-4xl font-bold mb-8 text-gray-800">Welcome, <?= $userName ?>!</h2>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Upload Prescription Card -->
        <a href="upload_prescription.php" class="group relative border border-gray-200 rounded-2xl p-10 bg-gradient-to-tr from-cyan-400 via-blue-400 to-indigo-500 shadow-lg hover:shadow-2xl transition transform hover:-translate-y-1 text-white">
          <div class="flex flex-col items-center gap-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span class="text-2xl font-semibold group-hover:text-yellow-200 transition-colors">Upload Prescription</span>
            <p class="text-center max-w-xs text-white/90">Upload your medical prescription to receive quotations from pharmacies nearby.</p>
          </div>
        </a>

        <!-- View Quotations Card -->
        <a href="view_quotations.php" class="group relative border border-gray-200 rounded-2xl p-10 bg-gradient-to-tr from-purple-400 via-pink-400 to-red-400 shadow-lg hover:shadow-2xl transition transform hover:-translate-y-1 text-white">
          <div class="flex flex-col items-center gap-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-16 h-16 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><path d="M8 14s1.5-2 4-2 4 2 4 2"/>
            </svg>
            <span class="text-2xl font-semibold group-hover:text-yellow-200 transition-colors">View Quotations</span>
            <p class="text-center max-w-xs text-white/90">Browse all quotations related to your prescriptions and orders, and make informed decisions.</p>
          </div>
        </a>
      </div>
    </main>

  </div>

  <!-- Footer -->
  <footer class="bg-white/80 backdrop-blur-md border-t border-gray-200 py-4 text-center text-gray-700 text-sm select-none">
    &copy; <?= date('Y') ?> MedPortal. All rights reserved.
  </footer>

</body>
</html>
