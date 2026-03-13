<div class="movie-card h-100">
    <a href="<?= BASE_URL ?>/movie.php?id=<?= $m['id'] ?>" class="card-thumb-link">
        <div class="card-thumb">
            <img src="<?= getThumbnailUrl($m['thumbnail']) ?>"
                 alt="<?= sanitize($m['title']) ?>"
                 loading="lazy" class="thumb-img">
            <div class="card-overlay">
                <a href="<?= BASE_URL ?>/watch.php?id=<?= $m['id'] ?>" class="play-btn">
                    <i class="fas fa-play"></i>
                </a>
            </div>
            <?php if (!empty($m['quality'])): ?>
            <span class="quality-badge"><?= sanitize($m['quality']) ?></span>
            <?php endif; ?>
            <?php if (!empty($m['language'])): ?>
            <span class="lang-badge"><?= sanitize($m['language']) ?></span>
            <?php endif; ?>
        </div>
    </a>
    <div class="card-body-custom">
        <a href="<?= BASE_URL ?>/movie.php?id=<?= $m['id'] ?>" class="movie-title-link">
            <h6 class="movie-card-title" title="<?= sanitize($m['title']) ?>"><?= sanitize($m['title']) ?></h6>
        </a>
        <div class="card-meta d-flex justify-content-between align-items-center">
            <small class="text-muted"><?= $m['year'] ?? '' ?></small>
            <div class="d-flex align-items-center gap-2">
                <?php if ($m['rating'] > 0): ?>
                <small class="text-warning"><i class="fas fa-star"></i> <?= number_format($m['rating'],1) ?></small>
                <?php endif; ?>
                <small class="text-muted"><i class="fas fa-eye"></i> <?= formatViews($m['views']) ?></small>
            </div>
        </div>
    </div>
</div>
