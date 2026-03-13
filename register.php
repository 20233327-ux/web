<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error   = '';
$success = '';
$csrf    = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Yêu cầu không hợp lệ.';
    } else {
        $username  = trim($_POST['username'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $fullName  = trim($_POST['full_name'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm'] ?? '';

        if ($password !== $confirm) {
            $error = 'Mật khẩu xác nhận không khớp.';
        } else {
            $result = register($username, $email, $password, $fullName);
            if ($result['success']) {
                header('Location: ' . BASE_URL . '/login.php?registered=1');
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - <?= SITE_NAME ?></title>
        <style>
      :root { color-scheme: dark; }
      * { box-sizing: border-box; }
      body {
          margin: 0;
          min-height: 100vh;
          font-family: Arial, sans-serif;
          background: #101114;
          color: #fff;
          display: grid;
          place-items: center;
          padding: 20px;
      }
      .card {
          width: 100%;
          max-width: 460px;
          background: #16181d;
          border: 1px solid #2a2f39;
          border-radius: 12px;
          padding: 24px;
      }
      h1 { margin: 0 0 16px; font-size: 28px; color: #ff3b30; text-align: center; }
      .sub { text-align: center; color: #cfd3dc; margin-bottom: 18px; }
      label { display: block; margin: 10px 0 6px; color: #d7dbe4; font-size: 14px; }
      input {
          width: 100%;
          display: block;
          padding: 12px;
          border-radius: 8px;
          border: 1px solid #3a4250;
          background: #0f1218;
          color: #fff;
          outline: none;
      }
      input:focus { border-color: #ff3b30; box-shadow: 0 0 0 3px rgba(255,59,48,.2); }
      button {
          width: 100%;
          margin-top: 14px;
          border: 0;
          border-radius: 8px;
          padding: 12px;
          font-weight: 700;
          background: #ff3b30;
          color: #fff;
          cursor: pointer;
      }
      .alert {
          margin-bottom: 12px;
          padding: 10px;
          border-radius: 8px;
          font-size: 14px;
      }
      .alert-danger { background: #3b1c1c; color: #ffd2d2; border: 1px solid #5e2f2f; }
      .muted { color: #aab2bf; font-size: 14px; text-align: center; margin-top: 12px; }
      a { color: #ff6961; }
        </style>
</head>
<body>
<div class="card">
    <h1><?= SITE_NAME ?></h1>
    <div class="sub">Tạo tài khoản</div>
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= sanitize($error) ?></div>
        <?php endif; ?>
        <form method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <label for="username">Tên đăng nhập *</label>
            <input id="username" type="text" name="username" placeholder="Chỉ chữ cái, số, dấu _" value="<?= sanitize($_POST['username'] ?? '') ?>" required autocomplete="username">

            <label for="full_name">Họ và tên</label>
            <input id="full_name" type="text" name="full_name" placeholder="Không bắt buộc" value="<?= sanitize($_POST['full_name'] ?? '') ?>" autocomplete="name">

            <label for="email">Email *</label>
            <input id="email" type="email" name="email" placeholder="example@email.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required autocomplete="email">

            <label for="password">Mật khẩu *</label>
            <input id="password" type="password" name="password" placeholder="Tối thiểu 6 ký tự" required autocomplete="new-password">

            <label for="confirm">Xác nhận mật khẩu *</label>
            <input id="confirm" type="password" name="confirm" placeholder="Nhập lại mật khẩu" required autocomplete="new-password">

            <button type="submit">Đăng ký</button>
        </form>
        <p class="muted">Đã có tài khoản? <a href="<?= BASE_URL ?>/login.php">Đăng nhập</a></p>
</div>
</body>
</html>
