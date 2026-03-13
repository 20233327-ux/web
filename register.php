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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
        <link href="<?= BASE_URL ?>/assets/css/style.css?v=20260314-2" rel="stylesheet">
        <style>
            /* Fallback: keep register inputs visible/editable even with stale CSS cache */
            .auth-card input[type="text"],
            .auth-card input[type="email"],
            .auth-card input[type="password"] {
                display: block !important;
                width: 100% !important;
                opacity: 1 !important;
                visibility: visible !important;
                pointer-events: auto !important;
                background: #1a1a1a !important;
                border: 2px solid #555 !important;
                color: #fff !important;
                min-height: 46px !important;
                padding: 12px 14px !important;
            }
        </style>
</head>
<body class="auth-page">
<div class="auth-bg"></div>
<div class="auth-container">
    <div class="auth-card">
        <div class="text-center mb-4">
            <a href="<?= BASE_URL ?>/index.php" class="brand-logo text-decoration-none d-block mb-2" style="font-size:2rem">🎬 <?= SITE_NAME ?></a>
            <h4 class="text-white">Tạo tài khoản</h4>
        </div>
        <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= sanitize($error) ?></div>
        <?php endif; ?>
        <form method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div class="mb-3">
                <label class="form-label text-muted">Tên đăng nhập <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control auth-input auth-input-plain" placeholder="Chỉ chữ cái, số, dấu _" value="<?= sanitize($_POST['username'] ?? '') ?>" required autocomplete="username">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Họ và tên</label>
                <input type="text" name="full_name" class="form-control auth-input auth-input-plain" placeholder="Không bắt buộc" value="<?= sanitize($_POST['full_name'] ?? '') ?>" autocomplete="name">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control auth-input auth-input-plain" placeholder="example@email.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required autocomplete="email">
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Mật khẩu <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control auth-input auth-input-plain" placeholder="Tối thiểu 6 ký tự" required id="pw1" autocomplete="new-password">
            </div>
            <div class="mb-4">
                <label class="form-label text-muted">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                <input type="password" name="confirm" class="form-control auth-input auth-input-plain" placeholder="Nhập lại mật khẩu" required id="pw2" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-danger w-100 py-2 fw-bold">
                <i class="fas fa-user-plus me-2"></i>Đăng ký
            </button>
        </form>
        <hr class="border-secondary my-3">
        <p class="text-center text-muted mb-0">
            Đã có tài khoản? <a href="<?= BASE_URL ?>/login.php" class="text-danger">Đăng nhập</a>
        </p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
