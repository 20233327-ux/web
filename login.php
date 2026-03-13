<?php
require_once __DIR__ . '/includes/auth.php';

if (!function_exists('e')) {
    function e(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$error = '';
$csrf  = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Yêu cầu không hợp lệ. Vui lòng thử lại.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if (empty($username) || empty($password)) {
            $error = 'Vui lòng nhập đầy đủ thông tin.';
        } else {
            $result = login($username, $password);
            if ($result['success']) {
                $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . '/index.php';
                unset($_SESSION['redirect_after_login']);
                if (in_array($result['role'], ['admin', 'editor', 'moderator'], true)) {
                    header('Location: ' . BASE_URL . '/admin/index.php');
                } else {
                    header('Location: ' . $redirect);
                }
                exit;
            } else {
                $error = $result['message'];
            }
        }
    }
}
$pageTitle = 'Đăng nhập';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - <?= SITE_NAME ?></title>
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
            max-width: 420px;
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
        .alert-success { background: #163124; color: #bde9d1; border: 1px solid #22553a; }
        .alert-warning { background: #3a3218; color: #fff0bf; border: 1px solid #6a5922; }
        .muted { color: #aab2bf; font-size: 14px; text-align: center; margin-top: 12px; }
        a { color: #ff6961; }
    </style>
</head>
<body>
<div class="card">
    <h1><?= SITE_NAME ?></h1>
    <div class="sub">Đăng nhập</div>
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($_GET['registered'])): ?>
        <div class="alert alert-success">Đăng ký thành công! Vui lòng đăng nhập.</div>
        <?php endif; ?>
        <?php if (!empty($_GET['error']) && $_GET['error'] === 'banned'): ?>
        <div class="alert alert-warning">Tài khoản của bạn đã bị khóa.</div>
        <?php endif; ?>
        <form method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <label for="username">Tên đăng nhập hoặc Email</label>
            <input id="username" type="text" name="username" placeholder="Nhập tên đăng nhập..." value="<?= e($_POST['username'] ?? '') ?>" required autofocus autocomplete="username">

            <label for="password">Mật khẩu</label>
            <input id="password" type="password" name="password" placeholder="Nhập mật khẩu..." required autocomplete="current-password">

            <button type="submit">Đăng nhập</button>
        </form>
        <p class="muted">Chưa có tài khoản? <a href="<?= BASE_URL ?>/register.php">Đăng ký ngay</a></p>
</div>
</body>
</html>
