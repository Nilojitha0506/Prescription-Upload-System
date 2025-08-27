<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $contact_no = trim($_POST['contact_no']);
    $dob = $_POST['dob'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Fix for role selection
    $allowed_roles = ['user', 'pharmacy', 'admin'];
    $role = in_array($_POST['role'], $allowed_roles) ? $_POST['role'] : 'user';

    $errors = [];

    if (!$name || !$email || !$password || !$confirm_password) {
        $errors[] = "Please fill in all required fields.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, address, contact_no, dob, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $address, $contact_no, $dob, $hashed_password, $role]);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Register</title>
<style>
  body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #45A86F, #457AA8);
    background-size: 400% 400%;
    animation: gradientBG 8s ease infinite;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
  }

  @keyframes gradientBG {
    0% {background-position: 0% 50%;}
    50% {background-position: 100% 50%;}
    100% {background-position: 0% 50%;}
  }

  .register-box {
    background: #CFCCAE;
    padding: 2rem;
    border-radius: 1.25rem;
    max-width: 480px;
    width: 100%;
    box-shadow: 0 12px 35px rgba(0,0,0,0.25);
    animation: fadeIn 0.6s ease-in-out;
  }

  @keyframes fadeIn {
    from {opacity: 0; transform: translateY(20px);}
    to {opacity: 1; transform: translateY(0);}
  }

  h2 {
    text-align: center;
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: #1D1D1D;
  }

  .alert {
    padding: 0.8rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    font-size: 0.9rem;
  }

  .alert-error { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }

  label {
    font-weight: 600;
    display: block;
    margin-bottom: 0.3rem;
    color: #1D1D1D;
  }

  input, textarea, select {
    width: 100%;
    padding: 0.9rem;
    border-radius: 0.75rem;
    border: 1px solid #A39E80;
    margin-bottom: 1rem;
    font-size: 1rem;
    background: #fff;
    color: #1D1D1D;
    transition: all 0.3s ease;
  }

  input:focus, textarea:focus, select:focus {
    border-color: #457AA8;
    box-shadow: 0 0 0 3px rgba(69,122,168,0.25);
  }

  button {
    width: 100%;
    background: linear-gradient(135deg, #45A86F, #457AA8);
    color: #fff;
    font-weight: 600;
    padding: 0.9rem;
    border: none;
    border-radius: 0.75rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
  }

  button:hover {
    transform: scale(1.03);
    box-shadow: 0 8px 18px rgba(0,0,0,0.25);
  }

  p {
    margin-top: 1rem;
    text-align: center;
    color: #1D1D1D;
  }

  p a {
    color: #457AA8;
    font-weight: 600;
    text-decoration: none;
  }

  p a:hover {
    color: #45A86F;
  }
</style>
</head>
<body>
  <div class="register-box">
    <h2>Create Account</h2>
    <?php if (!empty($errors)) : ?>
      <div class="alert alert-error">
        <ul>
          <?php foreach ($errors as $error) : ?>
            <li><?=htmlspecialchars($error)?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <form method="POST" action="">
      <label>Name</label>
      <input type="text" name="name" required value="<?=htmlspecialchars($name ?? '')?>" />

      <label>Email</label>
      <input type="email" name="email" required value="<?=htmlspecialchars($email ?? '')?>" />

      <label>Address</label>
      <textarea name="address"><?=htmlspecialchars($address ?? '')?></textarea>

      <label>Contact No</label>
      <input type="text" name="contact_no" value="<?=htmlspecialchars($contact_no ?? '')?>" />

      <label>Date of Birth</label>
      <input type="date" name="dob" value="<?=htmlspecialchars($dob ?? '')?>" />

      <label>Password</label>
      <input type="password" name="password" required />

      <label>Confirm Password</label>
      <input type="password" name="confirm_password" required />

      <label>Role</label>
      <select name="role">
        <option value="user" <?= (isset($role) && $role === 'user') ? 'selected' : '' ?>>User</option>
        <option value="pharmacy" <?= (isset($role) && $role === 'pharmacy') ? 'selected' : '' ?>>Pharmacy</option>
		<option value="admin" <?= (isset($role) && $role === 'admin') ? 'selected' : '' ?>>Admin</option>
      </select>

      <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
  </div>
</body>
</html>
