<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$slug    = $_GET['slug'] ?? '';
$genre   = $slug ? getGenreBySlug($slug) : null;
if (!$genre) { header('Location: ' . BASE_URL . '/index.php'); exit; }

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 18;
$offset  = ($page - 1) * $perPage;
$sort    = in_array($_GET['sort'] ?? '', ['views','rating','year','title']) ? $_GET['sort'] : 'created_at';

$movies = getMovies($perPage, $offset, (int)$genre['id'], $sort);
$total  = countMovies((int)$genre['id']);

$pageTitle = 'Thể loại: ' . $genre['name'];
include __DIR__ . '/includes/header.php';
?>
<main>
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active"><?= sanitize($genre['name']) ?></li>
                </ol>
            </nav>
            <h2 class="text-white mb-0">
                <i class="fas fa-film text-danger me-2"></i><?= sanitize($genre['name']) ?>
                <small class="text-muted fs-6 ms-2">(<?= number_format($total) ?> phim)</small>
            </h2>
        </div>
        <!-- Sort -->
        <form method="GET" class="d-flex gap-2 align-items-center">
            <input type="hidden" name="slug" value="<?= sanitize($slug) ?>">
            <select name="sort" class="form-select form-select-sm bg-dark text-white border-secondary" onchange="this.form.submit()">
                <option value="created_at" <?= $sort==='created_at'?'selected':'' ?>>Mới nhất</option>
                <option value="views"      <?= $sort==='views'?'selected':'' ?>>Xem nhiều nhất</option>
                <option value="rating"     <?= $sort==='rating'?'selected':'' ?>>Đánh giá cao nhất</option>
                <option value="year"       <?= $sort==='year'?'selected':'' ?>>Mới nhất (năm)</option>
            </select>
        </form>
    </div>

    <?php if ($movies): ?>
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3 mb-4">
        <?php foreach ($movies as $m): ?>
        <div class="col"><?php include __DIR__ . '/includes/movie_card.php'; ?></div>
        <?php endforeach; ?>
    </div>
    <?= renderPagination($total, $perPage, $page, '?slug=' . urlencode($slug) . '&sort=' . $sort) ?>
    <?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-film fa-3x text-muted mb-3"></i>
        <p class="text-muted">Chưa có phim nào trong thể loại này.</p>
    </div>
    <?php endif; ?>
</div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
