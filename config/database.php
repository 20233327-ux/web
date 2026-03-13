<?php
// ============================================================
// Cấu hình Database & Hằng số hệ thống
// Chỉnh sửa thông tin kết nối phù hợp với máy chủ của bạn
// ============================================================

// -- Database --
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'phimweb');

// -- Site --
define('SITE_NAME', 'PhimWeb');
// Thay đổi BASE_URL khi deploy lên hosting
define('BASE_URL', '');   // Để trống nếu chạy từ thư mục gốc, hoặc '/baitapphp' nếu trong thư mục con
define('APP_ENV', 'development'); // development | production
define('FORCE_HTTPS', false);

// -- Upload --
define('UPLOAD_PATH',       __DIR__ . '/../uploads/');
define('VIDEO_UPLOAD_PATH', __DIR__ . '/../uploads/movies/');
define('THUMB_UPLOAD_PATH', __DIR__ . '/../uploads/thumbnails/');
define('MAX_VIDEO_SIZE',    2  * 1024 * 1024 * 1024);  // 2 GB
define('MAX_IMAGE_SIZE',    10 * 1024 * 1024);           // 10 MB

/**
 * Lấy kết nối PDO (singleton)
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT
                 . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('DB Connection failed: ' . $e->getMessage());
            http_response_code(500);
            die('<div style="font-family:sans-serif;text-align:center;padding:60px">
                <h2 style="color:#e50914">Lỗi kết nối cơ sở dữ liệu</h2>
                <p>Vui lòng kiểm tra thông tin kết nối trong <code>config/database.php</code></p>
                <p>Hoặc chạy <a href="setup.php">setup.php</a> để cài đặt lần đầu.</p>
            </div>');
        }
    }
    return $pdo;
}

function enforceSecurityHeaders(): void {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    if (APP_ENV === 'production') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
}

function enforceHttpsRedirect(): void {
    if (!FORCE_HTTPS) {
        return;
    }
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    if (!$isHttps) {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        header('Location: https://' . $host . $uri, true, 301);
        exit;
    }
}
