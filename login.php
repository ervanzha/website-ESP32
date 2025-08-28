<?php
session_start();

// GANTI dengan data database kamu
$host = 'localhost';
$dbuser = 'u683623856_esp_user';
$dbpass = 'Esp_pass321';
$dbname = 'u683623856_esp_data';

$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user && hash('sha256', $password) === $user['password']) {
        $_SESSION['user'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
    mysqli_free_result($result);
}
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <style>
    body {
      background-color: #000;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      font-family: Arial, sans-serif;
      margin: 0;
    }
    .login-box {
      background-color: #121212;
      border: 1px solid #333;
      padding: 40px;
      border-radius: 8px;
      width: 300px;
      text-align: center;
      box-shadow: 0 0 10px rgba(255,255,255,0.1);
    }
    .login-box h1 {
      font-family: 'Segoe UI', cursive;
      font-size: 36px;
      color: white;
      margin-bottom: 30px;
    }
    .login-box input {
      width: 100%;
      padding: 12px 15px;
      margin-bottom: 15px;
      border: none;
      background-color: #262626;
      border-radius: 5px;
      color: white;
    }
    .login-box input[type="submit"] {
      background-color: #0095f6;
      font-weight: bold;
      cursor: pointer;
    }
    .login-box input[type="submit"]:hover {
      background-color: #007ed6;
    }
    .error {
      color: red;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <form class="login-box" method="POST">
    <h1>Login</h1>

    <?php if (!empty($error)): ?>
      <div class='error'><?= $error ?></div>
    <?php endif; ?>

    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="submit" value="Log in">
  </form>
</body>
</html>
