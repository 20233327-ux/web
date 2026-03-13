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
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
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
                <div class="input-group">
                    <span class="input-group-text auth-input-icon"><i class="fas fa-user"></i></span>
                    <input type="text" name="username" class="form-control auth-input" placeholder="Chỉ chữ cái, số, dấu _" value="<?= sanitize($_POST['username'] ?? '') ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Họ và tên</label>
                <div class="input-group">
                    <span class="input-group-text auth-input-icon"><i class="fas fa-id-card"></i></span>
                    <input type="text" name="full_name" class="form-control auth-input" placeholder="Không bắt buộc" value="<?= sanitize($_POST['full_name'] ?? '') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Email <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text auth-input-icon"><i class="fas fa-envelope"></i></span>
                    <input type="email" name="email" class="form-control auth-input" placeholder="example@email.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted">Mật khẩu <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text auth-input-icon"><i class="fas fa-lock"></i></span>
                    <input type="password" name="password" class="form-control auth-input" placeholder="Tối thiểu 6 ký tự" required id="pw1">
                    <button type="button" class="input-group-text auth-input-icon" onclick="toggle('pw1','eye1')">
                        <i class="fas fa-eye" id="eye1"></i>
                    </button>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label text-muted">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text auth-input-icon"><i class="fas fa-lock"></i></span>
                    <input type="password" name="confirm" class="form-control auth-input" placeholder="Nhập lại mật khẩu" required id="pw2">
                    <button type="button" class="input-group-text auth-input-icon" onclick="toggle('pw2','eye2')">
                        <i class="fas fa-eye" id="eye2"></i>
                    </button>
                </div>
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
<script>
function toggle(id, eyeId) {
    const f = document.getElementById(id), e = document.getElementById(eyeId);
    f.type = f.type === 'password' ? 'text' : 'password';
    e.className = f.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
</body>
</html>
