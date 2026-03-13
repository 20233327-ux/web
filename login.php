<?php
require_once __DIR__ . '/includes/auth.php';

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-page">
<div class="auth-bg"></div>
<div class="auth-container">
    <div class="auth-card">
        <div class="text-center mb-4">
            <a href="<?= BASE_URL ?>/index.php" class="brand-logo text-decoration-none d-block mb-2" style="font-size:2rem">🎬 <?= SITE_NAME ?></a>
            <h4 class="text-white">Đăng nhập</h4>
        </div>
        <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="fas fa-exclamation-circle"></i> <?= sanitize($error) ?>
        </div>
        <?php endif; ?>
        <?php if (!empty($_GET['registered'])): ?>
        <div class="alert alert-success d-flex align-items-center gap-2">
            <i class="fas fa-check-circle"></i> Đăng ký thành công! Vui lòng đăng nhập.
        </div>
        <?php endif; ?>
        <?php if (!empty($_GET['error']) && $_GET['error'] === 'banned'): ?>
        <div class="alert alert-warning">Tài khoản của bạn đã bị khóa.</div>
        <?php endif; ?>
        <form method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div class="mb-3">
                <label class="form-label text-muted">Tên đăng nhập hoặc Email</label>
                <input type="text" name="username" class="form-control auth-input auth-input-plain" placeholder="Nhập tên đăng nhập..."
                       value="<?= sanitize($_POST['username'] ?? '') ?>" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted">Mật khẩu</label>
                <input type="password" name="password" class="form-control auth-input auth-input-plain" placeholder="Nhập mật khẩu..." required id="passwordField">
            </div>
            <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">
                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
            </button>
        </form>
        <hr class="border-secondary my-3">
        <p class="text-center text-muted mb-0">
            Chưa có tài khoản? <a href="<?= BASE_URL ?>/register.php" class="text-danger">Đăng ký ngay</a>
        </p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
