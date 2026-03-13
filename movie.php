<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: ' . BASE_URL . '/index.php'); exit; }

$movie = getMovieById($id);
if (!$movie) { http_response_code(404); include '404.php'; exit; }

$currentUser   = getCurrentUser();
$comments      = getComments($id);
$episodes      = getEpisodesByMovie($id, true);
$related       = getRelatedMovies($id, $movie['genre_id'] ? (int)$movie['genre_id'] : null, 6);
$userRating    = $currentUser ? getUserRating($currentUser['id'], $id) : null;
$csrfToken     = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rate'])) {
    header('Content-Type: application/json');
    if (!$currentUser) { echo json_encode(['error' => 'Cần đăng nhập']); exit; }
    $rating = filter_input(INPUT_POST, 'rate', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10]]);
    if ($rating && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        rateMovie($currentUser['id'], $id, $rating);
        $info = getMovieById($id);
        echo json_encode(['success' => true, 'rating' => $info['rating'], 'count' => $info['rating_count']]);
    } else {
        echo json_encode(['error' => 'Điểm không hợp lệ']);
    }
    exit;
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['rate'])) {
    requireLogin();
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $commentError = 'Yêu cầu không hợp lệ.';
    } elseif (!empty($_POST['comment_content'])) {
        addComment($currentUser['id'], $id, $_POST['comment_content']);
        header('Location: ' . BASE_URL . '/movie.php?id=' . $id . '#comments');
        exit;
    }
}

$pageTitle = $movie['title'];
$pageDesc  = mb_substr(strip_tags($movie['description'] ?? ''), 0, 160);
include __DIR__ . '/includes/header.php';
?>
<main>
<div class="container py-4">
    <div class="row g-4">
        <!-- Left: movie info -->
        <div class="col-lg-8">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Trang chủ</a></li>
                    <?php if ($movie['genre_name']): ?>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/genre.php?slug=<?= urlencode($movie['genre_slug']) ?>"><?= sanitize($movie['genre_name']) ?></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active"><?= sanitize($movie['title']) ?></li>
                </ol>
            </nav>

            <div class="d-flex flex-md-row flex-column gap-4 mb-4">
                <!-- Thumbnail -->
                <div class="movie-detail-thumb flex-shrink-0">
                    <img src="<?= getThumbnailUrl($movie['thumbnail']) ?>" alt="<?= sanitize($movie['title']) ?>" class="rounded shadow w-100">
                    <a href="<?= BASE_URL ?>/watch.php?id=<?= $id ?><?= $episodes ? '&episode=' . (int)$episodes[0]['id'] : '' ?>" class="btn btn-danger w-100 mt-3 py-2 fw-bold">
                        <i class="fas fa-play me-2"></i>Xem phim ngay
                    </a>
                </div>
                <!-- Detail -->
                <div class="flex-grow-1">
                    <h1 class="h3 text-white mb-1"><?= sanitize($movie['title']) ?></h1>
                    <?php if ($movie['original_title']): ?>
                    <p class="text-muted mb-2"><em><?= sanitize($movie['original_title']) ?></em></p>
                    <?php endif; ?>

                    <!-- Badges -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php if ($movie['quality']): ?><span class="badge bg-warning text-dark"><?= sanitize($movie['quality']) ?></span><?php endif; ?>
                        <?php if ($movie['language']): ?><span class="badge bg-info text-dark"><?= sanitize($movie['language']) ?></span><?php endif; ?>
                        <?php if ($movie['genre_name']): ?><a href="<?= BASE_URL ?>/genre.php?slug=<?= urlencode($movie['genre_slug']) ?>" class="badge bg-secondary text-decoration-none"><?= sanitize($movie['genre_name']) ?></a><?php endif; ?>
                    </div>

                    <!-- Movie info table -->
                    <table class="table table-sm table-borderless text-sm movie-info-table">
                        <tbody>
                            <?php if ($movie['year']): ?>
                            <tr><td class="text-muted w-25">Năm:</td><td><?= $movie['year'] ?></td></tr>
                            <?php endif; ?>
                            <?php if ($movie['duration']): ?>
                            <tr><td class="text-muted">Thời lượng:</td><td><?= sanitize($movie['duration']) ?></td></tr>
                            <?php endif; ?>
                            <?php if ($movie['director']): ?>
                            <tr><td class="text-muted">Đạo diễn:</td><td><?= sanitize($movie['director']) ?></td></tr>
                            <?php endif; ?>
                            <?php if ($movie['cast_members']): ?>
                            <tr><td class="text-muted">Diễn viên:</td><td><?= sanitize($movie['cast_members']) ?></td></tr>
                            <?php endif; ?>
                            <?php if ($movie['country_name']): ?>
                            <tr><td class="text-muted">Quốc gia:</td><td><?= sanitize($movie['country_name']) ?></td></tr>
                            <?php endif; ?>
                            <tr>
                                <td class="text-muted">Lượt xem:</td>
                                <td><?= formatViews($movie['views']) ?></td>
                            </tr>
                            <?php if ($movie['rating'] > 0): ?>
                            <tr>
                                <td class="text-muted">Điểm TB:</td>
                                <td>
                                    <span class="text-warning fw-bold"><?= number_format($movie['rating'],1) ?>/10</span>
                                    <small class="text-muted ms-1">(<?= number_format($movie['rating_count']) ?> đánh giá)</small>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Rating -->
                    <div class="rating-section mt-3">
                        <p class="text-muted mb-2 small">Đánh giá của bạn:</p>
                        <?php if ($currentUser): ?>
                        <div class="star-rating" data-movie="<?= $id ?>" data-csrf="<?= $csrfToken ?>" data-user-rating="<?= $userRating ?? 0 ?>">
                            <?php for ($s = 1; $s <= 10; $s++): ?>
                            <button class="star-btn <?= ($userRating && $s <= $userRating) ? 'active' : '' ?>"
                                    data-value="<?= $s ?>" title="<?= $s ?>/10">
                                <i class="fas fa-star"></i>
                            </button>
                            <?php endfor; ?>
                        </div>
                        <small class="text-muted" id="ratingMsg"><?= $userRating ? 'Bạn đã đánh giá: '.$userRating.'/10' : 'Chưa đánh giá' ?></small>
                        <?php else: ?>
                        <p class="small text-muted"><a href="<?= BASE_URL ?>/login.php">Đăng nhập</a> để đánh giá phim.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <?php if ($movie['description']): ?>
            <div class="movie-description mb-4">
                <h5 class="text-white border-start border-danger border-3 ps-3 mb-3">Nội dung phim</h5>
                <div class="text-muted description-text" id="descText">
                    <?= nl2br(sanitize($movie['description'])) ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($episodes): ?>
            <div class="mb-4">
                <h5 class="text-white border-start border-danger border-3 ps-3 mb-3">Danh sách tập</h5>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($episodes as $ep): ?>
                    <a href="<?= BASE_URL ?>/watch.php?id=<?= $id ?>&episode=<?= (int)$ep['id'] ?>" class="btn btn-sm btn-outline-light">
                        Tập <?= (int)$ep['episode_number'] ?><?= !empty($ep['title']) ? ' - ' . sanitize($ep['title']) : '' ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Trailer -->
            <?php if ($movie['trailer_url']): ?>
            <div class="mb-4">
                <h5 class="text-white border-start border-danger border-3 ps-3 mb-3">Trailer</h5>
                <?php $ytId = getYoutubeId($movie['trailer_url']); ?>
                <?php if ($ytId): ?>
                <div class="ratio ratio-16x9 rounded overflow-hidden">
                    <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($ytId) ?>" allowfullscreen></iframe>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Comments -->
            <div id="comments" class="mb-4">
                <h5 class="text-white border-start border-danger border-3 ps-3 mb-3">Bình luận (<?= count($comments) ?>)</h5>
                <?php if ($currentUser): ?>
                <form method="POST" class="mb-4">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <textarea name="comment_content" class="form-control bg-dark text-white border-secondary mb-2"
                              rows="3" placeholder="Viết bình luận của bạn..." maxlength="1000"></textarea>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-paper-plane me-1"></i>Gửi bình luận
                    </button>
                </form>
                <?php else: ?>
                <p class="text-muted mb-4"><a href="<?= BASE_URL ?>/login.php">Đăng nhập</a> để bình luận.</p>
                <?php endif; ?>
                <?php if ($comments): ?>
                <div class="comments-list">
                    <?php foreach ($comments as $c): ?>
                    <div class="comment-item d-flex gap-3 mb-3">
                        <div class="comment-avatar flex-shrink-0">
                            <div class="avatar-circle"><?= strtoupper(substr($c['username'], 0, 1)) ?></div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="text-white fw-semibold small"><?= sanitize($c['username']) ?></span>
                                <span class="text-muted" style="font-size:.75rem"><?= timeAgo($c['created_at']) ?></span>
                            </div>
                            <p class="text-muted mb-0 small"><?= nl2br(sanitize($c['content'])) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">Chưa có bình luận nào. Hãy là người đầu tiên!</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: related movies -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top:80px">
                <h5 class="text-white border-start border-danger border-3 ps-3 mb-3">Phim liên quan</h5>
                <?php foreach ($related as $r): ?>
                <div class="d-flex gap-3 mb-3 related-movie-item">
                    <a href="<?= BASE_URL ?>/movie.php?id=<?= $r['id'] ?>" class="flex-shrink-0">
                        <img src="<?= getThumbnailUrl($r['thumbnail']) ?>" alt="<?= sanitize($r['title']) ?>"
                             class="rounded" style="width:80px;height:55px;object-fit:cover">
                    </a>
                    <div class="flex-grow-1">
                        <a href="<?= BASE_URL ?>/movie.php?id=<?= $r['id'] ?>" class="text-white text-decoration-none small fw-semibold d-block line-clamp-2">
                            <?= sanitize($r['title']) ?>
                        </a>
                        <div class="text-muted" style="font-size:.75rem">
                            <?= $r['year'] ?? '' ?>
                            <?php if ($r['rating'] > 0): ?>
                            &nbsp;<i class="fas fa-star text-warning"></i> <?= number_format($r['rating'],1) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
</main>

<?php
$extraScripts = '<script>
// Rating
document.querySelectorAll(".star-btn").forEach(btn => {
    btn.addEventListener("click", async function() {
        const val = this.dataset.value;
        const container = this.closest(".star-rating");
        const csrf = container.dataset.csrf;
        const movieId = container.dataset.movie;
        const resp = await fetch(location.href, {
            method: "POST",
            headers: {"Content-Type": "application/x-www-form-urlencoded"},
            body: "rate="+val+"&csrf_token="+csrf
        });
        const data = await resp.json();
        if (data.success) {
            container.querySelectorAll(".star-btn").forEach((b,i) => {
                b.classList.toggle("active", i < val);
            });
            document.getElementById("ratingMsg").textContent = "Bạn đã đánh giá: "+val+"/10";
        }
    });
    btn.addEventListener("mouseover", function() {
        const val = this.dataset.value;
        const container = this.closest(".star-rating");
        container.querySelectorAll(".star-btn").forEach((b,i) => b.classList.toggle("hover", i < val));
    });
    btn.addEventListener("mouseout", function() {
        document.querySelectorAll(".star-btn").forEach(b => b.classList.remove("hover"));
    });
});
</script>';
include __DIR__ . '/includes/footer.php';
?>
