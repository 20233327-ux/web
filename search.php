<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$q       = trim($_GET['q'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset  = ($page - 1) * $perPage;

$movies  = $q ? searchMovies($q, $perPage, $offset) : [];
$total   = $q ? countSearch($q) : 0;

$pageTitle = $q ? 'Tìm kiếm: ' . $q : 'Tìm kiếm phim';
include __DIR__ . '/includes/header.php';
?>
<main>
<div class="container py-4">
    <!-- Search bar -->
    <div class="row justify-content-center mb-4">
        <div class="col-md-8">
            <form action="" method="GET">
                <div class="input-group input-group-lg">
                    <input type="search" name="q" class="form-control bg-dark border-secondary text-white"
                           placeholder="Nhập tên phim, đạo diễn, diễn viên..." value="<?= sanitize($q) ?>" autofocus>
                    <button class="btn btn-danger px-4" type="submit">
                        <i class="fas fa-search me-2"></i>Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($q): ?>
    <div class="mb-3">
        <h5 class="text-white">
            <?php if ($total > 0): ?>
                Tìm thấy <span class="text-danger"><?= number_format($total) ?></span> kết quả cho
                "<span class="text-warning"><?= sanitize($q) ?></span>"
            <?php else: ?>
                Không tìm thấy kết quả cho "<span class="text-warning"><?= sanitize($q) ?></span>"
            <?php endif; ?>
        </h5>
    </div>

    <?php if ($movies): ?>
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3 mb-4">
        <?php foreach ($movies as $m): ?>
        <div class="col"><?php include __DIR__ . '/includes/movie_card.php'; ?></div>
        <?php endforeach; ?>
    </div>
    <?= renderPagination($total, $perPage, $page, '?q=' . urlencode($q)) ?>
    <?php elseif ($q): ?>
    <div class="text-center py-5">
        <i class="fas fa-search fa-3x text-muted mb-3"></i>
        <p class="text-muted">Không tìm thấy phim nào. Thử từ khóa khác!</p>
    </div>
    <?php endif; ?>
    <?php else: ?>
    <!-- No query - show latest movies -->
    <?php $latest = getLatestMovies(12); ?>
    <h5 class="text-white mb-3">Phim mới nhất</h5>
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
        <?php foreach ($latest as $m): ?>
        <div class="col"><?php include __DIR__ . '/includes/movie_card.php'; ?></div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
