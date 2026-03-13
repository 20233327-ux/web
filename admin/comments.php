<?php
$adminTitle = 'Quản lý bình luận';
require_once __DIR__ . '/header.php';
requireCommentManager();

$csrf    = generateCsrfToken();
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

// Handle hide/show and delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) { die('Invalid CSRF'); }
    $cid = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
    if ($cid) {
        if (isset($_POST['toggle_comment'])) {
            getDB()->prepare("UPDATE comments SET status=IF(status='active','hidden','active') WHERE id=?")->execute([$cid]);
        }
        if (isset($_POST['delete_comment'])) {
            getDB()->prepare("DELETE FROM comments WHERE id=?")->execute([$cid]);
        }
    }
    header('Location: comments.php?page=' . $page);
    exit;
}

$total    = countAllComments();
$comments = getAllComments($perPage, $offset);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white mb-0"><i class="fas fa-comments me-2 text-success"></i>Quản lý bình luận</h4>
    <span class="text-muted small">Tổng: <?= number_format($total) ?> bình luận</span>
</div>

<div class="admin-card p-0">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr><th>#</th><th>Người dùng</th><th>Phim</th><th>Nội dung</th><th>Trạng thái</th><th>Thời gian</th><th>Thao tác</th></tr>
            </thead>
            <tbody>
                <?php if ($comments): ?>
                <?php foreach ($comments as $c): ?>
                <tr>
                    <td class="text-muted"><?= $c['id'] ?></td>
                    <td><small class="text-white"><?= sanitize($c['username']) ?></small></td>
                    <td>
                        <a href="<?= BASE_URL ?>/movie.php?id=<?= $c['movie_id'] ?>" target="_blank" class="text-info text-decoration-none small">
                            <?= sanitize(mb_substr($c['movie_title'],0,30)) ?>…
                        </a>
                    </td>
                    <td><small class="text-muted"><?= sanitize(mb_substr($c['content'],0,100)) ?><?= mb_strlen($c['content'])>100?'…':'' ?></small></td>
                    <td>
                        <span class="badge <?= $c['status']==='active'?'bg-success':'bg-secondary' ?>">
                            <?= $c['status']==='active'?'Hiện':'Ẩn' ?>
                        </span>
                    </td>
                    <td><small class="text-muted"><?= timeAgo($c['created_at']) ?></small></td>
                    <td>
                        <div class="d-flex gap-1">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="toggle_comment" value="1">
                                <button class="btn btn-xs btn-outline-warning" style="font-size:.7rem;padding:2px 6px"
                                        title="<?= $c['status']==='active'?'Ẩn':'Hiện' ?> bình luận">
                                    <i class="fas fa-<?= $c['status']==='active'?'eye-slash':'eye' ?>"></i>
                                </button>
                            </form>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="delete_comment" value="1">
                                <button class="btn btn-xs btn-outline-danger" style="font-size:.7rem;padding:2px 6px"
                                        onclick="return confirm('Xóa bình luận này?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Chưa có bình luận nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3"><?= renderPagination($total, $perPage, $page, '?') ?></div>

<?php include __DIR__ . '/footer.php'; ?>
