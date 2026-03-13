<?php
$adminTitle = 'Quản lý phim';
require_once __DIR__ . '/header.php';
requireMovieManager();

$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;
$offset  = ($page - 1) * $perPage;

$movies = getAdminMovies($perPage, $offset, $search);
$total  = countAdminMovies($search);

// Handle quick status toggle (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    header('Content-Type: application/json');
    $mid = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
    if ($mid) {
        $pdo  = getDB();
        $stmt = $pdo->prepare("UPDATE movies SET status = IF(status='active','inactive','active') WHERE id=?");
        $stmt->execute([$mid]);
        $new = $pdo->query("SELECT status FROM movies WHERE id=$mid")->fetchColumn();
        echo json_encode(['status' => $new]);
    }
    exit;
}
// Toggle featured
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_featured'])) {
    header('Content-Type: application/json');
    $mid = filter_input(INPUT_POST, 'movie_id', FILTER_VALIDATE_INT);
    if ($mid) {
        $pdo = getDB();
        $pdo->prepare("UPDATE movies SET featured = IF(featured=1,0,1) WHERE id=?")->execute([$mid]);
        $new = $pdo->query("SELECT featured FROM movies WHERE id=$mid")->fetchColumn();
        echo json_encode(['featured' => (bool)$new]);
    }
    exit;
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white mb-0"><i class="fas fa-film me-2 text-danger"></i>Quản lý phim</h4>
    <a href="<?= BASE_URL ?>/admin/add_movie.php" class="btn btn-danger btn-sm">
        <i class="fas fa-plus me-1"></i>Thêm phim
    </a>
</div>

<?php if (isset($_GET['deleted'])): ?>
<div class="alert alert-success alert-auto">✅ Đã xóa phim thành công.</div>
<?php endif; ?>
<?php if (isset($_GET['saved'])): ?>
<div class="alert alert-success alert-auto">✅ Đã lưu phim thành công.</div>
<?php endif; ?>

<!-- Search -->
<form method="GET" class="mb-3 d-flex gap-2">
    <input type="search" name="search" class="form-control bg-dark text-white border-secondary" style="max-width:320px"
           placeholder="Tìm tên phim..." value="<?= sanitize($search) ?>">
    <button class="btn btn-outline-secondary btn-sm">Tìm</button>
    <?php if ($search): ?><a href="movies.php" class="btn btn-sm btn-outline-danger">Xóa lọc</a><?php endif; ?>
</form>

<div class="admin-card p-0">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0" id="moviesTable">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th>Tên phim</th>
                    <th>Thể loại</th>
                    <th>Năm</th>
                    <th>Loại video</th>
                    <th>Lượt xem</th>
                    <th>Nổi bật</th>
                    <th>Trạng thái</th>
                    <th style="width:160px">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($movies): ?>
                <?php foreach ($movies as $m): ?>
                <tr id="row-<?= $m['id'] ?>">
                    <td class="text-muted"><?= $m['id'] ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= getThumbnailUrl($m['thumbnail']) ?>" class="rounded flex-shrink-0"
                                 style="width:45px;height:32px;object-fit:cover">
                            <div>
                                <div class="text-white small fw-semibold"><?= sanitize(mb_substr($m['title'],0,40)) ?><?= mb_strlen($m['title'])>40?'…':'' ?></div>
                                <?php if ($m['quality']): ?><span class="badge bg-warning text-dark" style="font-size:.65rem"><?= sanitize($m['quality']) ?></span><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><small class="text-muted"><?= sanitize($m['genre_name'] ?? '-') ?></small></td>
                    <td><small><?= $m['year'] ?? '-' ?></small></td>
                    <td>
                        <?php $icons = ['file'=>'<i class="fas fa-upload text-success"></i> File','url'=>'<i class="fas fa-link text-info"></i> URL','youtube'=>'<i class="fab fa-youtube text-danger"></i> YouTube','embed'=>'<i class="fas fa-code text-warning"></i> Embed']; ?>
                        <small><?= $icons[$m['video_type']] ?? $m['video_type'] ?></small>
                    </td>
                    <td><small><?= formatViews($m['views']) ?></small></td>
                    <td>
                        <button class="btn btn-sm border-0 p-0 featured-btn" data-id="<?= $m['id'] ?>"
                                title="<?= $m['featured']?'Bỏ nổi bật':'Đặt nổi bật' ?>">
                            <i class="fas fa-star <?= $m['featured']?'text-warning':'text-secondary' ?>" id="star-<?= $m['id'] ?>"></i>
                        </button>
                    </td>
                    <td>
                        <button class="btn btn-sm border-0 p-0 status-btn" data-id="<?= $m['id'] ?>">
                            <span class="badge <?= $m['status']==='active'?'bg-success':'bg-secondary' ?>" id="status-<?= $m['id'] ?>">
                                <?= $m['status']==='active'?'Hiện':'Ẩn' ?>
                            </span>
                        </button>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?= BASE_URL ?>/watch.php?id=<?= $m['id'] ?>" target="_blank" class="btn btn-xs btn-outline-info" title="Xem" style="font-size:.7rem;padding:2px 6px"><i class="fas fa-play"></i></a>
                            <a href="<?= BASE_URL ?>/admin/episodes.php?movie_id=<?= $m['id'] ?>" class="btn btn-xs btn-outline-primary" title="Tập phim" style="font-size:.7rem;padding:2px 6px"><i class="fas fa-list-ol"></i></a>
                            <a href="<?= BASE_URL ?>/admin/edit_movie.php?id=<?= $m['id'] ?>" class="btn btn-xs btn-outline-warning" title="Sửa" style="font-size:.7rem;padding:2px 6px"><i class="fas fa-edit"></i></a>
                            <a href="<?= BASE_URL ?>/admin/delete_movie.php?id=<?= $m['id'] ?>" class="btn btn-xs btn-outline-danger delete-btn" title="Xóa" style="font-size:.7rem;padding:2px 6px" data-title="<?= sanitize($m['title']) ?>"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="9" class="text-center text-muted py-4">Chưa có phim nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="mt-3">
    <?= renderPagination($total, $perPage, $page, '?search='.urlencode($search)) ?>
</div>
<p class="text-muted small mt-2">Tổng: <?= number_format($total) ?> phim</p>

<?php
$adminExtraScripts = <<<JS
<script>
// Toggle status
document.querySelectorAll('.status-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        const resp = await fetch('movies.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'toggle_status=1&movie_id='+id});
        const data = await resp.json();
        const badge = document.getElementById('status-'+id);
        if (data.status === 'active') { badge.className='badge bg-success'; badge.textContent='Hiện'; }
        else { badge.className='badge bg-secondary'; badge.textContent='Ẩn'; }
    });
});
// Toggle featured
document.querySelectorAll('.featured-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        const resp = await fetch('movies.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'toggle_featured=1&movie_id='+id});
        const data = await resp.json();
        const star = document.getElementById('star-'+id);
        star.className = data.featured ? 'fas fa-star text-warning' : 'fas fa-star text-secondary';
    });
});
// Confirm delete
document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if(!confirm('Xóa phim "' + this.dataset.title + '"? Thao tác này không thể hoàn tác!')) e.preventDefault();
    });
});
</script>
JS;
include __DIR__ . '/footer.php';
?>
