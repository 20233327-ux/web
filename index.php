<?php
$pageTitle = 'Trang chủ';
require_once __DIR__ . '/includes/header.php';

$featured  = getFeaturedMovies(5);
$latest    = getLatestMovies(12);
$topRated  = getTopRatedMovies(6);
$mostViewed= getMostViewedMovies(6);
?>
<main>
<!-- Hero / Banner -->
<?php if ($featured): ?>
<section class="hero-section">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
            <?php foreach ($featured as $i => $m): ?>
            <div class="carousel-item <?= $i===0?'active':'' ?>">
                <div class="hero-bg" style="background-image:url('<?= getThumbnailUrl($m['thumbnail']) ?>')"></div>
                <div class="hero-overlay"></div>
                <div class="container hero-content">
                    <div class="hero-meta">
                        <?php if ($m['genre_name']): ?><span class="badge bg-danger mb-2"><?= sanitize($m['genre_name']) ?></span><?php endif; ?>
                        <?php if ($m['quality']): ?><span class="badge bg-warning text-dark mb-2 ms-1"><?= sanitize($m['quality']) ?></span><?php endif; ?>
                    </div>
                    <h1 class="hero-title"><?= sanitize($m['title']) ?></h1>
                    <?php if ($m['original_title']): ?><p class="hero-orig"><?= sanitize($m['original_title']) ?></p><?php endif; ?>
                    <p class="hero-desc"><?= sanitize(mb_substr($m['description'] ?? '', 0, 200)) ?>...</p>
                    <div class="hero-info d-flex flex-wrap gap-3 mb-4">
                        <?php if ($m['year']): ?><span><i class="fas fa-calendar me-1"></i><?= $m['year'] ?></span><?php endif; ?>
                        <?php if ($m['duration']): ?><span><i class="fas fa-clock me-1"></i><?= sanitize($m['duration']) ?></span><?php endif; ?>
                        <?php if ($m['rating'] > 0): ?><span><i class="fas fa-star text-warning me-1"></i><?= number_format($m['rating'],1) ?>/10</span><?php endif; ?>
                        <span><i class="fas fa-eye me-1"></i><?= formatViews($m['views']) ?> lượt xem</span>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= BASE_URL ?>/watch.php?id=<?= $m['id'] ?>" class="btn btn-danger btn-lg px-4">
                            <i class="fas fa-play me-2"></i>Xem ngay
                        </a>
                        <a href="<?= BASE_URL ?>/movie.php?id=<?= $m['id'] ?>" class="btn btn-outline-light btn-lg px-4">
                            <i class="fas fa-info-circle me-2"></i>Chi tiết
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($featured) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
        <div class="carousel-indicators">
            <?php foreach ($featured as $i => $m): ?>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" <?= $i===0?'class="active"':'' ?>></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<div class="container py-4">
    <!-- Phim mới nhất -->
    <section class="mb-5">
        <div class="section-header d-flex justify-content-between align-items-center mb-3">
            <h2 class="section-title"><i class="fas fa-fire text-danger me-2"></i>Phim mới nhất</h2>
            <a href="<?= BASE_URL ?>/search.php" class="btn btn-sm btn-outline-secondary">Xem tất cả</a>
        </div>
        <?php if ($latest): ?>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
            <?php foreach ($latest as $m): ?>
            <div class="col">
                <?php include __DIR__ . '/includes/movie_card.php'; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-muted text-center py-4">Chưa có phim nào. <a href="<?= BASE_URL ?>/admin/add_movie.php">Thêm phim ngay</a>.</p>
        <?php endif; ?>
    </section>

    <!-- Top đánh giá cao -->
    <?php if ($topRated): ?>
    <section class="mb-5">
        <div class="section-header d-flex justify-content-between align-items-center mb-3">
            <h2 class="section-title"><i class="fas fa-star text-warning me-2"></i>Đánh giá cao nhất</h2>
        </div>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
            <?php foreach ($topRated as $m): ?>
            <div class="col"><?php include __DIR__ . '/includes/movie_card.php'; ?></div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Xem nhiều nhất -->
    <?php if ($mostViewed): ?>
    <section class="mb-5">
        <div class="section-header d-flex justify-content-between align-items-center mb-3">
            <h2 class="section-title"><i class="fas fa-eye text-info me-2"></i>Xem nhiều nhất</h2>
        </div>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
            <?php foreach ($mostViewed as $m): ?>
            <div class="col"><?php include __DIR__ . '/includes/movie_card.php'; ?></div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
