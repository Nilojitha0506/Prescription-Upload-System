<?php
require_once 'config.php';

// Basic routing example: redirects users based on role or guest
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

$role = $_SESSION['role'] ?? 'user';

switch ($role) {
    case 'admin':
        header('Location: admin/dashboard.php');
        break;
    case 'pharmacy':
        header('Location: pharmacy/dashboard.php');
        break;
    case 'user':
    default:
        header('Location: user/dashboard.php');
        break;
}
exit;
?>
