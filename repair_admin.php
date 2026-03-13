<?php
require_once __DIR__ . '/config/database.php';

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$message = '';
$error = '';
$connectedDb = '';

try {
    $pdo = getDB();
    $connectedDb = (string)$pdo->query('SELECT DATABASE()')->fetchColumn();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? 'admin');
        $email = trim($_POST['email'] ?? 'admin@phimweb.vn');
        $password = (string)($_POST['password'] ?? 'Admin@123');

        if ($username === '' || $email === '' || $password === '') {
            throw new RuntimeException('Vui lòng nhập đầy đủ thông tin.');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE LOWER(username)=LOWER(?) OR LOWER(email)=LOWER(?) LIMIT 1');
        $stmt->execute([$username, $email]);
        $existing = $stmt->fetch();

        if ($existing) {
            $upd = $pdo->prepare('UPDATE users SET username=?, email=?, password=?, role="admin", status="active" WHERE id=?');
            $upd->execute([$username, $email, $hash, (int)$existing['id']]);
            $message = 'Da cap nhat tai khoan admin thanh cong.';
        } else {
            $ins = $pdo->prepare('INSERT INTO users (username,email,password,full_name,role,status) VALUES (?,?,?,?,"admin","active")');
            $ins->execute([$username, $email, $hash, 'Quan tri vien']);
            $message = 'Da tao tai khoan admin moi thanh cong.';
        }

        $pdo->commit();
    }
} catch (Throwable $ex) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error = $ex->getMessage();
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Repair Admin</title>
  <style>
    body { font-family: Arial, sans-serif; background:#111; color:#fff; margin:0; padding:24px; }
    .box { max-width:560px; margin:0 auto; background:#1b1b1b; border:1px solid #333; border-radius:10px; padding:20px; }
    label { display:block; margin:10px 0 6px; color:#ddd; }
    input { width:100%; padding:10px; border-radius:8px; border:1px solid #444; background:#0f0f0f; color:#fff; }
    button { margin-top:14px; width:100%; padding:11px; border:0; border-radius:8px; background:#e50914; color:#fff; font-weight:700; cursor:pointer; }
    .ok { background:#173126; border:1px solid #24573f; padding:10px; border-radius:8px; margin-bottom:12px; }
    .err { background:#3a1f1f; border:1px solid #5a2d2d; padding:10px; border-radius:8px; margin-bottom:12px; }
    .muted { color:#b7b7b7; font-size:13px; }
    a { color:#ff7b74; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Repair tai khoan admin</h2>
    <p class="muted">Database dang ket noi: <strong><?= e($connectedDb) ?></strong></p>

    <?php if ($message !== ''): ?>
      <div class="ok"><?= e($message) ?></div>
      <p>Dang nhap ngay: <a href="login.php">login.php</a></p>
      <p class="muted">Nen xoa file repair_admin.php sau khi sua xong de an toan.</p>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
      <div class="err"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post">
      <label for="username">Username admin</label>
      <input id="username" name="username" value="admin" required>

      <label for="email">Email admin</label>
      <input id="email" name="email" type="email" value="admin@phimweb.vn" required>

      <label for="password">Mat khau admin</label>
      <input id="password" name="password" type="text" value="Admin@123" required>

      <button type="submit">Sua/Tao tai khoan admin</button>
    </form>
  </div>
</body>
</html>
