<?php
require_once '../config.php';

// Only allow logged-in pharmacy users
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pharmacy') {
    header('Location: ../auth/login.php');
    exit;
}

// Fetch user info
$stmt = $pdo->prepare('SELECT id, name, email, contact_no, address, dob FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle AJAX update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (isset($_POST['action'])) {
        // Update details
        if ($_POST['action'] === 'update_details') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $contact_no = trim($_POST['contact_no']);
            $address = trim($_POST['address']);
            $dob = trim($_POST['dob']);

            $update = $pdo->prepare("UPDATE users SET name=?, email=?, contact_no=?, address=?, dob=? WHERE id=?");
            $update->execute([$name, $email, $contact_no, $address, $dob, $_SESSION['user_id']]);
            echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
            exit;
        }

        // Change password
        if ($_POST['action'] === 'change_password') {
            $current = $_POST['current_password'];
            $new = $_POST['new_password'];
            $confirm = $_POST['confirm_password'];

            $stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
            $stmt->execute([$_SESSION['user_id']]);
            $row = $stmt->fetch();

            if (!password_verify($current, $row['password'])) {
                echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
                exit;
            }
            if ($new !== $confirm) {
                echo json_encode(['status' => 'error', 'message' => 'New password and confirm password do not match']);
                exit;
            }

            $hash = password_hash($new, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
            $update->execute([$hash, $_SESSION['user_id']]);
            echo json_encode(['status' => 'success', 'message' => 'Password changed successfully']);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pharmacy Profile</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gradient-to-r from-[#45A86F] to-[#457AA8] min-h-screen flex items-center justify-center p-4">

<div class="bg-white rounded-3xl shadow-2xl w-full max-w-3xl p-8 relative">
  <!-- Profile Picture & Welcome -->
  <div class="flex flex-col items-center">
    <div class="bg-green-600 text-white w-24 h-24 flex items-center justify-center rounded-full text-3xl font-bold mb-4">
      <?= strtoupper(substr($user['name'],0,1)) ?>
    </div>
    <h2 class="text-2xl font-bold mb-1">Welcome, <?= htmlspecialchars($user['name']) ?></h2>
    <p class="text-gray-600 mb-6">Manage your profile details below</p>
  </div>

  <!-- Profile Details -->
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <span class="font-semibold">Name:</span>
      <div class="flex items-center space-x-2">
        <span id="name-display"><?= htmlspecialchars($user['name']) ?></span>
        <button class="edit-btn bg-blue-600 text-white px-2 py-1 rounded text-sm" data-field="name">Edit</button>
        <input type="text" id="name-input" class="hidden border-b border-blue-500 px-1 py-0.5" />
      </div>
    </div>

    <div class="flex items-center justify-between">
      <span class="font-semibold">Email:</span>
      <div class="flex items-center space-x-2">
        <span id="email-display"><?= htmlspecialchars($user['email']) ?></span>
        <button class="edit-btn bg-blue-600 text-white px-2 py-1 rounded text-sm" data-field="email">Edit</button>
        <input type="email" id="email-input" class="hidden border-b border-blue-500 px-1 py-0.5" />
      </div>
    </div>

    <div class="flex items-center justify-between">
      <span class="font-semibold">Contact No:</span>
      <div class="flex items-center space-x-2">
        <span id="contact_no-display"><?= htmlspecialchars($user['contact_no']) ?></span>
        <button class="edit-btn bg-blue-600 text-white px-2 py-1 rounded text-sm" data-field="contact_no">Edit</button>
        <input type="text" id="contact_no-input" class="hidden border-b border-blue-500 px-1 py-0.5" />
      </div>
    </div>

    <div class="flex items-center justify-between">
      <span class="font-semibold">Address:</span>
      <div class="flex items-center space-x-2">
        <span id="address-display"><?= htmlspecialchars($user['address']) ?></span>
        <button class="edit-btn bg-blue-600 text-white px-2 py-1 rounded text-sm" data-field="address">Edit</button>
        <input type="text" id="address-input" class="hidden border-b border-blue-500 px-1 py-0.5" />
      </div>
    </div>

    <div class="flex items-center justify-between">
      <span class="font-semibold">Date of Birth:</span>
      <div class="flex items-center space-x-2">
        <span id="dob-display"><?= htmlspecialchars($user['dob']) ?></span>
        <button class="edit-btn bg-blue-600 text-white px-2 py-1 rounded text-sm" data-field="dob">Edit</button>
        <input type="date" id="dob-input" class="hidden border-b border-blue-500 px-1 py-0.5" />
      </div>
    </div>

    <div class="text-center mt-4">
      <button id="save-details" class="bg-green-600 text-white px-6 py-2 rounded">Save Changes</button>
    </div>
  </div>

  <!-- Change Password -->
  <div class="mt-8 border-t pt-4">
    <button id="toggle-password" class="bg-purple-600 text-white px-4 py-2 rounded mb-4">Change Password</button>
    <div id="password-section" class="hidden space-y-3">
      <input type="password" id="current-password" placeholder="Current Password" class="w-full border rounded px-2 py-1" />
      <input type="password" id="new-password" placeholder="New Password" class="w-full border rounded px-2 py-1" />
      <input type="password" id="confirm-password" placeholder="Confirm New Password" class="w-full border rounded px-2 py-1" />
      <button id="save-password" class="bg-purple-600 text-white px-6 py-2 rounded w-full">Save Password</button>
    </div>
  </div>

  <div class="text-center mt-6">
    <a href="dashboard.php" class="bg-blue-600 text-white px-6 py-2 rounded">← Back to Dashboard</a>
  </div>
</div>

<script>
// Toggle edit inputs
$('.edit-btn').click(function(){
  let field = $(this).data('field');
  $(`#${field}-input`).val($(`#${field}-display`).text()).removeClass('hidden').focus();
  $(`#${field}-display`).addClass('hidden');
});

// Save details via AJAX
$('#save-details').click(function(){
  let data = {
    action: 'update_details',
    name: $('#name-input').val() || $('#name-display').text(),
    email: $('#email-input').val() || $('#email-display').text(),
    contact_no: $('#contact_no-input').val() || $('#contact_no-display').text(),
    address: $('#address-input').val() || $('#address-display').text(),
    dob: $('#dob-input').val() || $('#dob-display').text()
  };

  $.post('profile.php', data, function(res){
    if(res.status === 'success'){
      $('#name-display').text(data.name).removeClass('hidden');
      $('#email-display').text(data.email).removeClass('hidden');
      $('#contact_no-display').text(data.contact_no).removeClass('hidden');
      $('#address-display').text(data.address).removeClass('hidden');
      $('#dob-display').text(data.dob).removeClass('hidden');
      $('#name-input,#email-input,#contact_no-input,#address-input,#dob-input').addClass('hidden');
      alert(res.message);
    } else {
      alert('Error updating details');
    }
  }, 'json');
});

// Toggle password section
$('#toggle-password').click(function(){
  $('#password-section').toggleClass('hidden');
});

// Save password via AJAX
$('#save-password').click(function(){
  let current = $('#current-password').val();
  let newp = $('#new-password').val();
  let confirmp = $('#confirm-password').val();

  $.post('profile.php', {
    action: 'change_password',
    current_password: current,
    new_password: newp,
    confirm_password: confirmp
  }, function(res){
    alert(res.message);
    if(res.status === 'success'){
      $('#current-password,#new-password,#confirm-password').val('');
      $('#password-section').addClass('hidden');
    }
  }, 'json');
});
</script>
</body>
</html>
