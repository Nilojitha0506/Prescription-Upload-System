<?php
require_once '../config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!$email || !$password) {
        $errors[] = "Please enter both email and password.";
    } else {
        $stmt = $pdo->prepare('SELECT id, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header('Location: ../index.php');
            exit;
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Login</title>
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

  .login-container {
    background: #CFCCAE; /* form background */
    padding: 2.5rem;
    border-radius: 1.5rem;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
    text-align: center;
    transition: transform 0.3s ease;
  }

  .login-container:hover {
    transform: translateY(-5px);
  }

  h2 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    color: #1D1D1D; /* dark text for readability */
  }

  .alert {
    padding: 0.8rem;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    margin-bottom: 1rem;
    text-align: left;
  }

  .alert-success { background: #DCFCE7; color: #166534; border: 1px solid #86EFAC; }
  .alert-error { background: #FEE2E2; color: #991B1B; border: 1px solid #FCA5A5; }

  label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.3rem;
    text-align: left;
    color: #1D1D1D; /* visible on light form */
  }

  input {
    width: 100%;
    padding: 0.9rem;
    border: 1px solid #A39E80;
    border-radius: 0.75rem;
    margin-bottom: 1.2rem;
    font-size: 1rem;
    outline: none;
    transition: all 0.3s ease;
    background: #FFFFFF;
    color: #1D1D1D;
  }

  input:focus {
    border-color: #457AA8;
    box-shadow: 0 0 0 3px rgba(69,122,168,0.3);
  }

  button {
    width: 100%;
    background: linear-gradient(135deg, #45A86F, #457AA8);
    color: #fff;
    font-weight: 600;
    padding: 0.9rem;
    border-radius: 0.75rem;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.3s ease;
  }

  button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(0,0,0,0.25);
  }

  p {
    margin-top: 1.5rem;
    font-size: 0.95rem;
    color: #1D1D1D;
  }

  p a {
    color: #457AA8;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
  }

  p a:hover {
    color: #45A86F;
  }
</style>
</head>
<body>
<div class="login-container">
  <h2>Login</h2>
  <?php if (!empty($_GET['registered'])) : ?>
    <div class="alert alert-success">Registration successful! Please login.</div>
  <?php endif; ?>
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
    <label>Email</label>
    <input type="email" name="email" required placeholder="Enter your email" value="<?=htmlspecialchars($email ?? '')?>" />
    <label>Password</label>
    <input type="password" name="password" required placeholder="Enter your password" />
    <button type="submit">Login</button>
  </form>
  <p>Don't have an account? <a href="register.php">Register here</a></p>
</div>
</body>
</html>
