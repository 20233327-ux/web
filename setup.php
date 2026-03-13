<?php
/**
 * setup.php - Trình cài đặt PhimWeb
 * Chạy file này một lần để khởi tạo CSDL và tài khoản admin.
 * XÓA hoặc đổi tên file này sau khi cài đặt xong!
 */
session_start();

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Bước 2: Xử lý form cài đặt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 1) {
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbPort = trim($_POST['db_port'] ?? '3306');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = $_POST['db_pass'] ?? '';
    $dbName = trim($_POST['db_name'] ?? 'phimweb');
    $adminUser = trim($_POST['admin_user'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPass = $_POST['admin_pass'] ?? '';
    $siteName = trim($_POST['site_name'] ?? 'PhimWeb');

    if (empty($dbUser) || empty($dbName) || empty($adminUser) || empty($adminEmail) || empty($adminPass)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
    } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email admin không hợp lệ.';
    } elseif (strlen($adminPass) < 6) {
        $error = 'Mật khẩu admin phải ít nhất 6 ký tự.';
    } else {
        try {
            $pdo = new PDO(
                "mysql:host=$dbHost;port=$dbPort;charset=utf8mb4",
                $dbUser, $dbPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Tạo database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbName`");

            // Chạy SQL schema
            $sql = file_get_contents(__DIR__ . '/database.sql');
            // Bỏ phần CREATE DATABASE và USE (đã xử lý ở trên)
            $sql = preg_replace('/CREATE DATABASE.*?;/si', '', $sql);
            $sql = preg_replace('/USE\s+`[^`]+`\s*;/si', '', $sql);
            // Bỏ các INSERT INTO users mẫu
            $sql = preg_replace('/INSERT INTO `users`.*?;/si', '', $sql);

            // Tách và thực thi từng câu lệnh SQL
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $stmt) {
                if (!empty($stmt)) {
                    $pdo->exec($stmt);
                }
            }

            // Tạo tài khoản admin
            $hashedPass = password_hash($adminPass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, "admin") ON DUPLICATE KEY UPDATE password=VALUES(password)');
            $stmt->execute([$adminUser, $adminEmail, $hashedPass, 'Quản trị viên']);

            // Cập nhật config (ghi thông tin vào file config)
            $configContent = file_get_contents(__DIR__ . '/config/database.php');
            $configContent = preg_replace("/define\('DB_HOST',\s*'[^']*'\)/", "define('DB_HOST', '$dbHost')", $configContent);
            $configContent = preg_replace("/define\('DB_PORT',\s*'[^']*'\)/", "define('DB_PORT', '$dbPort')", $configContent);
            $configContent = preg_replace("/define\('DB_USER',\s*'[^']*'\)/", "define('DB_USER', '$dbUser')", $configContent);
            $configContent = preg_replace("/define\('DB_PASS',\s*'[^']*'\)/", "define('DB_PASS', '$dbPass')", $configContent);
            $configContent = preg_replace("/define\('DB_NAME',\s*'[^']*'\)/", "define('DB_NAME', '$dbName')", $configContent);
            $configContent = preg_replace("/define\('SITE_NAME',\s*'[^']*'\)/", "define('SITE_NAME', '$siteName')", $configContent);
            file_put_contents(__DIR__ . '/config/database.php', $configContent);

            // Tạo thư mục uploads
            foreach (['uploads/movies', 'uploads/thumbnails'] as $dir) {
                if (!is_dir(__DIR__ . '/' . $dir)) {
                    mkdir(__DIR__ . '/' . $dir, 0755, true);
                }
            }

            $success = "Cài đặt thành công! Tài khoản admin: <strong>$adminUser</strong>";
            header('Location: setup.php?step=2&admin=' . urlencode($adminUser));
            exit;
        } catch (PDOException $e) {
            $error = 'Lỗi kết nối/CSDL: ' . htmlspecialchars($e->getMessage());
        } catch (Exception $e) {
            $error = 'Lỗi: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài đặt PhimWeb</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #141414; color: #fff; font-family: sans-serif; }
        .setup-card { background: #1e1e1e; border-radius: 12px; padding: 40px; max-width: 580px; margin: 60px auto; }
        .logo { color: #e50914; font-size: 2rem; font-weight: 900; letter-spacing: -1px; }
        .form-control, .form-select { background: #333; border: 1px solid #444; color: #fff; }
        .form-control:focus, .form-select:focus { background: #3a3a3a; border-color: #e50914; color: #fff; box-shadow: 0 0 0 .2rem rgba(229,9,20,.25); }
        .btn-setup { background: #e50914; border: none; padding: 12px; font-size: 1.1rem; font-weight: 700; }
        .btn-setup:hover { background: #c1000f; }
        label { color: #ccc; font-size: .9rem; }
    </style>
</head>
<body>
<div class="setup-card">
    <div class="text-center mb-4">
        <div class="logo">🎬 PhimWeb</div>
        <h4 class="mt-2 text-white">Trình cài đặt</h4>
    </div>

    <?php if ($step === 2): ?>
        <div class="alert alert-success text-center">
            <h5>✅ Cài đặt thành công!</h5>
            <p>Tài khoản admin: <strong><?= htmlspecialchars($_GET['admin'] ?? '') ?></strong></p>
            <hr>
            <p class="mb-2"><strong>⚠️ Quan trọng:</strong> Hãy xóa hoặc đổi tên file <code>setup.php</code> để bảo mật!</p>
            <a href="index.php" class="btn btn-danger mt-2">Đến trang chủ</a>
        </div>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <h6 class="text-warning mb-3">1. Cấu hình Database</h6>
            <div class="row g-2 mb-3">
                <div class="col-8">
                    <label>Host DB <span class="text-danger">*</span></label>
                    <input type="text" name="db_host" class="form-control" value="localhost" required>
                </div>
                <div class="col-4">
                    <label>Port</label>
                    <input type="text" name="db_port" class="form-control" value="3306">
                </div>
                <div class="col-6">
                    <label>User DB <span class="text-danger">*</span></label>
                    <input type="text" name="db_user" class="form-control" value="root" required>
                </div>
                <div class="col-6">
                    <label>Mật khẩu DB</label>
                    <input type="password" name="db_pass" class="form-control">
                </div>
                <div class="col-12">
                    <label>Tên Database <span class="text-danger">*</span></label>
                    <input type="text" name="db_name" class="form-control" value="phimweb" required>
                </div>
            </div>
            <h6 class="text-warning mb-3">2. Tài khoản Admin</h6>
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label>Tên đăng nhập <span class="text-danger">*</span></label>
                    <input type="text" name="admin_user" class="form-control" value="admin" required>
                </div>
                <div class="col-6">
                    <label>Mật khẩu <span class="text-danger">*</span></label>
                    <input type="password" name="admin_pass" class="form-control" placeholder="Tối thiểu 6 ký tự" required>
                </div>
                <div class="col-12">
                    <label>Email Admin <span class="text-danger">*</span></label>
                    <input type="email" name="admin_email" class="form-control" required>
                </div>
            </div>
            <h6 class="text-warning mb-3">3. Cài đặt trang web</h6>
            <div class="mb-4">
                <label>Tên website</label>
                <input type="text" name="site_name" class="form-control" value="PhimWeb">
            </div>
            <button type="submit" class="btn btn-setup text-white w-100">🚀 Cài đặt ngay</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
