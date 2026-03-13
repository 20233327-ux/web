<?php
$adminTitle = 'Quản lý người dùng';
require_once __DIR__ . '/header.php';
requireUserManager();

$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;
$csrf    = generateCsrfToken();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) { die('Invalid CSRF'); }
    $uid = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if (isset($_POST['toggle_status']) && $uid) {
        $pdo = getDB();
        $pdo->prepare("UPDATE users SET status=IF(status='active','banned','active') WHERE id=? AND role!='admin'")->execute([$uid]);
    }
    if (isset($_POST['change_role']) && $uid && canChangeRoles()) {
        $allowedRoles = ['user', 'editor', 'moderator', 'admin'];
        $newRole = $_POST['new_role'] ?? 'user';
        if (!in_array($newRole, $allowedRoles, true)) {
            $newRole = 'user';
        }
        if ($uid !== (int)$adminUser['id']) {
            getDB()->prepare("UPDATE users SET role=? WHERE id=?")->execute([$newRole, $uid]);
        }
    }
    if (isset($_POST['delete_user']) && $uid && canDeleteUsers()) {
        if ($uid !== (int)$adminUser['id']) {
            getDB()->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
        }
    }
    header('Location: users.php?search=' . urlencode($search));
    exit;
}

$users = getAllUsers($perPage, $offset, $search);
$total = countAllUsersTotal($search);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white mb-0"><i class="fas fa-users me-2 text-info"></i>Quản lý người dùng</h4>
</div>

<form method="GET" class="mb-3 d-flex gap-2">
    <input type="search" name="search" class="form-control bg-dark text-white border-secondary" style="max-width:300px"
           placeholder="Tìm username, email..." value="<?= sanitize($search) ?>">
    <button class="btn btn-outline-secondary btn-sm">Tìm</button>
    <?php if ($search): ?><a href="users.php" class="btn btn-sm btn-outline-danger">Xóa lọc</a><?php endif; ?>
</form>

<div class="admin-card p-0">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th><th>Người dùng</th><th>Email</th><th>Vai trò</th>
                    <th>Trạng thái</th><th>Ngày đăng ký</th><th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="text-muted"><?= $u['id'] ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-circle flex-shrink-0"><?= strtoupper(substr($u['username'],0,1)) ?></div>
                            <div>
                                <div class="text-white small fw-semibold"><?= sanitize($u['username']) ?></div>
                                <?php if ($u['full_name']): ?><div class="text-muted" style="font-size:.72rem"><?= sanitize($u['full_name']) ?></div><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><small class="text-muted"><?= sanitize($u['email']) ?></small></td>
                    <td>
                        <span class="badge <?= $u['role']==='admin'?'bg-danger':'bg-secondary' ?>">
                            <?= sanitize(ucfirst($u['role'])) ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge <?= $u['status']==='active'?'bg-success':'bg-warning text-dark' ?>">
                            <?= $u['status']==='active'?'Hoạt động':'Bị khóa' ?>
                        </span>
                    </td>
                    <td><small class="text-muted"><?= date('d/m/Y', strtotime($u['created_at'])) ?></small></td>
                    <td>
                        <?php if ((int)$u['id'] !== (int)$adminUser['id']): ?>
                        <div class="d-flex gap-1 flex-wrap">
                            <!-- Toggle status -->
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="toggle_status" value="1">
                                <button class="btn btn-xs <?= $u['status']==='active'?'btn-outline-warning':'btn-outline-success' ?>" style="font-size:.7rem;padding:2px 6px"
                                        title="<?= $u['status']==='active'?'Khóa tài khoản':'Mở khóa' ?>">
                                    <i class="fas fa-<?= $u['status']==='active'?'ban':'check' ?>"></i>
                                </button>
                            </form>
                            <!-- Toggle role -->
                            <?php if (canChangeRoles()): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="change_role" value="1">
                                <select name="new_role" class="form-select form-select-sm bg-dark text-white border-secondary" style="font-size:.72rem;height:24px;width:96px;display:inline-block">
                                    <?php foreach (['user','editor','moderator','admin'] as $roleOpt): ?>
                                    <option value="<?= $roleOpt ?>" <?= $u['role']===$roleOpt?'selected':'' ?>><?= ucfirst($roleOpt) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-xs btn-outline-info" style="font-size:.7rem;padding:2px 6px"
                                        title="Đổi vai trò"
                                        onclick="return confirm('Đổi vai trò của <?= sanitize($u['username']) ?>?')">
                                    <i class="fas fa-user-cog"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <!-- Delete -->
                            <?php if (canDeleteUsers()): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="delete_user" value="1">
                                <button class="btn btn-xs btn-outline-danger" style="font-size:.7rem;padding:2px 6px"
                                        onclick="return confirm('Xóa tài khoản <?= sanitize($u['username']) ?>? Không thể hoàn tác!')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <small class="text-muted">Tài khoản của bạn</small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">
    <?= renderPagination($total, $perPage, $page, '?search='.urlencode($search)) ?>
</div>
<p class="text-muted small mt-2">Tổng: <?= number_format($total) ?> tài khoản</p>

<?php include __DIR__ . '/footer.php'; ?>
