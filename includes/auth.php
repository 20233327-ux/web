<?php
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', $isHttps ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}
require_once __DIR__ . '/../config/database.php';

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

function hasRole(array $roles): bool {
    return isLoggedIn() && in_array($_SESSION['role'] ?? '', $roles, true);
}

function canAccessAdminPanel(): bool {
    return hasRole(['admin', 'editor', 'moderator']);
}

function canManageMovies(): bool {
    return hasRole(['admin', 'editor']);
}

function canManageCategories(): bool {
    return hasRole(['admin', 'editor']);
}

function canManageUsers(): bool {
    return hasRole(['admin', 'moderator']);
}

function canManageComments(): bool {
    return hasRole(['admin', 'moderator']);
}

function canChangeRoles(): bool {
    return hasRole(['admin']);
}

function canDeleteUsers(): bool {
    return hasRole(['admin']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . (isLoggedIn() ? '/index.php' : '/login.php'));
        exit;
    }
}

function requireAdminPanelAccess(): void {
    if (!canAccessAdminPanel()) {
        header('Location: ' . BASE_URL . (isLoggedIn() ? '/index.php' : '/login.php'));
        exit;
    }
}

function requireMovieManager(): void {
    if (!canManageMovies()) {
        header('Location: ' . BASE_URL . '/admin/index.php');
        exit;
    }
}

function requireCategoryManager(): void {
    if (!canManageCategories()) {
        header('Location: ' . BASE_URL . '/admin/index.php');
        exit;
    }
}

function requireUserManager(): void {
    if (!canManageUsers()) {
        header('Location: ' . BASE_URL . '/admin/index.php');
        exit;
    }
}

function requireCommentManager(): void {
    if (!canManageComments()) {
        header('Location: ' . BASE_URL . '/admin/index.php');
        exit;
    }
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    static $user = null;
    if ($user === null) {
        $stmt = getDB()->prepare('SELECT id,username,email,full_name,role,avatar,status FROM users WHERE id=?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
        if ($user && $user['status'] === 'banned') {
            logout();
            header('Location: ' . BASE_URL . '/login.php?error=banned');
            exit;
        }
    }
    return $user;
}

function login(string $usernameOrEmail, string $password): array {
    $usernameOrEmail = trim($usernameOrEmail);
    $stmt = getDB()->prepare('SELECT id,username,email,password,role,status FROM users WHERE LOWER(username)=LOWER(?) OR LOWER(email)=LOWER(?)');
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng.'];
    }
    if ($user['status'] === 'banned') {
        return ['success' => false, 'message' => 'Tài khoản của bạn đã bị khóa.'];
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];
    return ['success' => true, 'role' => $user['role']];
}

function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function register(string $username, string $email, string $password, string $fullName = ''): array {
    $username = trim($username);
    $email = trim($email);
    $fullName = trim($fullName);
    if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
        return ['success' => false, 'message' => 'Tên đăng nhập 3-50 ký tự, chỉ chứa chữ cái, số và dấu _'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email không hợp lệ.'];
    }
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Mật khẩu phải ít nhất 6 ký tự.'];
    }
    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT id FROM users WHERE LOWER(username)=LOWER(?) OR LOWER(email)=LOWER(?)');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Tên đăng nhập hoặc email đã được sử dụng.'];
    }
    $pdo->prepare('INSERT INTO users (username,email,password,full_name) VALUES (?,?,?,?)')
        ->execute([$username, $email, password_hash($password, PASSWORD_BCRYPT), $fullName]);
    return ['success' => true];
}

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
