<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$currentUser = getCurrentUser();
$history     = getWatchHistory($currentUser['id'], 20);
$error       = '';
$success     = '';
$csrf        = generateCsrfToken();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Yêu cầu không hợp lệ.';
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        $pdo = getDB();
        // Check email uniqueness
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email=? AND id!=?');
        $stmt->execute([$email, $currentUser['id']]);
        if ($stmt->fetch()) {
            $error = 'Email đã được sử dụng bởi tài khoản khác.';
        } elseif (!empty($password) && $password !== $confirm) {
            $error = 'Mật khẩu xác nhận không khớp.';
        } elseif (!empty($password) && strlen($password) < 6) {
            $error = 'Mật khẩu phải ít nhất 6 ký tự.';
        } else {
            if (!empty($password)) {
                $pdo->prepare('UPDATE users SET full_name=?,email=?,password=? WHERE id=?')
                    ->execute([$fullName, $email, password_hash($password, PASSWORD_BCRYPT), $currentUser['id']]);
            } else {
                $pdo->prepare('UPDATE users SET full_name=?,email=? WHERE id=?')
                    ->execute([$fullName, $email, $currentUser['id']]);
            }
            $success = 'Cập nhật thông tin thành công!';
            // Refresh current user cache
            header('Location: profile.php?success=1');
            exit;
        }
    }
}

$pageTitle = 'Hồ sơ cá nhân';
include __DIR__ . '/includes/header.php';
?>
<main>
<div class="container py-4">
    <div class="row g-4">
        <!-- Profile form -->
        <div class="col-lg-4">
            <div class="card bg-dark border-secondary">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="avatar-circle-lg mx-auto mb-3">
                            <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                        </div>
                        <h5 class="text-white mb-1"><?= sanitize($currentUser['username']) ?></h5>
                        <?php
                        $roleLabel = [
                            'admin' => 'Quan tri vien',
                            'editor' => 'Bien tap vien',
                            'moderator' => 'Dieu hanh vien',
                            'user' => 'Thanh vien',
                        ][$currentUser['role']] ?? 'Thanh vien';
                        ?>
                        <span class="badge <?= $currentUser['role']==='admin'?'bg-danger':'bg-secondary' ?>"><?= $roleLabel ?></span>
                        <p class="text-muted small mt-2">
                            <i class="fas fa-calendar me-1"></i>Tham gia từ <?= date('d/m/Y', strtotime($currentUser['created_at'] ?? '')) ?>
                        </p>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success py-2 small">✅ Cập nhật thành công!</div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><?= sanitize($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Họ và tên</label>
                            <input type="text" name="full_name" class="form-control form-control-sm bg-dark text-white border-secondary"
                                   value="<?= sanitize($currentUser['full_name'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small">Email</label>
                            <input type="email" name="email" class="form-control form-control-sm bg-dark text-white border-secondary"
                                   value="<?= sanitize($currentUser['email']) ?>" required>
                        </div>
                        <hr class="border-secondary">
                        <p class="text-muted small">Đổi mật khẩu (bỏ trống nếu không đổi)</p>
                        <div class="mb-3">
                            <input type="password" name="new_password" class="form-control form-control-sm bg-dark text-white border-secondary"
                                   placeholder="Mật khẩu mới (tối thiểu 6 ký tự)">
                        </div>
                        <div class="mb-3">
                            <input type="password" name="confirm_password" class="form-control form-control-sm bg-dark text-white border-secondary"
                                   placeholder="Xác nhận mật khẩu mới">
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm w-100">
                            <i class="fas fa-save me-1"></i>Lưu thay đổi
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Watch history -->
        <div class="col-lg-8">
            <h5 class="text-white border-start border-danger border-3 ps-3 mb-3">
                <i class="fas fa-history me-2"></i>Lịch sử xem
            </h5>
            <?php if ($history): ?>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-3">
                <?php foreach ($history as $m): ?>
                <div class="col"><?php include __DIR__ . '/includes/movie_card.php'; ?></div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                <p class="text-muted">Bạn chưa xem phim nào. <a href="<?= BASE_URL ?>/index.php" class="text-danger">Khám phá phim ngay!</a></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
