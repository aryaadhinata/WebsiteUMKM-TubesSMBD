<?php
require_once 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Akun admin default statis untuk keperluan lokal offline Anda
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = 'Username atau Password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        body { background-color: #D9D9D9; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; border-top: 5px solid #400101; }
        .login-card h2 { color: #400101; text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: 600; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; }
        .btn-submit { background-color: #400101; color: #fff; border: none; width: 100%; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 15px; margin-top: 10px; }
        .btn-submit:hover { background-color: #667302; }
        .error-msg { background: #FFEBEE; color: #C62828; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; text-align: center; }
        .back-link { display: block; text-align: center; margin-top: 15px; font-size: 13px; color: #666; text-decoration: none; }
    </style>
</head>
<body>

    <div class="login-card">
        <h2>Admin Authentication</h2>
        <?php if($error): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Masukkan username">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Masukkan password">
            </div>
            <button type="submit" class="btn-submit">Sign In</button>
        </form>
        <a href="index.php" class="back-link">&larr; Kembali ke Beranda Utama</a>
    </div>

</body>
</html>