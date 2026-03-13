<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$episodeId = filter_input(INPUT_GET, 'episode', FILTER_VALIDATE_INT);
if (!$id) { header('Location: ' . BASE_URL . '/index.php'); exit; }

$movie = getMovieById($id);
if (!$movie) { http_response_code(404); die('Phim không tồn tại.'); }

// Increment views & save history
incrementViews($id);
$currentUser = getCurrentUser();
if ($currentUser) addToHistory($currentUser['id'], $id);

$pageTitle = 'Xem: ' . $movie['title'];
include __DIR__ . '/includes/header.php';

// Determine video source
$videoType = $movie['video_type'];
$videoPath = $movie['video_path'];
$activeEpisode = null;
if ($episodeId) {
    $activeEpisode = getEpisodeById($episodeId);
    if ($activeEpisode && (int)$activeEpisode['movie_id'] === $id) {
        $videoType = $activeEpisode['video_type'];
        $videoPath = $activeEpisode['video_path'];
    }
}
$ytId      = null;
if ($videoType === 'youtube' && $videoPath) {
    $ytId = getYoutubeId($videoPath);
}
$safeEmbed = $videoType === 'embed' ? sanitizeEmbedCode((string)$videoPath) : '';
?>
<main>
<div class="container-fluid p-0 watch-page">
    <!-- Video player section -->
    <div class="video-wrapper bg-black">
        <div class="container">
            <div class="ratio ratio-16x9">
                <?php if ($videoType === 'youtube' && $ytId): ?>
                <!-- YouTube embed -->
                <iframe
                    src="https://www.youtube.com/embed/<?= htmlspecialchars($ytId) ?>?autoplay=1&rel=0"
                    allowfullscreen
                    allow="autoplay; encrypted-media"
                    class="w-100 h-100"></iframe>

                <?php elseif ($videoType === 'embed' && $safeEmbed): ?>
                <!-- Custom embed code -->
                <?= $safeEmbed ?>

                <?php elseif ($videoType === 'file' && $videoPath): ?>
                <!-- Uploaded file streamed via PHP -->
                <video id="player" playsinline controls crossorigin>
                    <source src="<?= BASE_URL ?>/stream.php?id=<?= $id ?><?= $activeEpisode ? '&episode=' . (int)$activeEpisode['id'] : '' ?>" type="video/mp4">
                    Trình duyệt của bạn không hỗ trợ video HTML5.
                </video>

                <?php elseif ($videoType === 'url' && $videoPath): ?>
                <!-- Direct URL -->
                <video id="player" playsinline controls crossorigin>
                    <source src="<?= sanitize($videoPath) ?>">
                    Trình duyệt của bạn không hỗ trợ video HTML5.
                </video>

                <?php else: ?>
                <div class="d-flex align-items-center justify-content-center bg-dark h-100">
                    <div class="text-center text-muted p-4">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                        <p>Phim chưa có nguồn video. Vui lòng thử lại sau.</p>
                        <a href="<?= BASE_URL ?>/movie.php?id=<?= $id ?>" class="btn btn-outline-light btn-sm">Quay lại</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Movie info bar -->
    <div class="container py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div>
                <h1 class="h4 text-white mb-1"><?= sanitize($movie['title']) ?></h1>
                <?php if ($activeEpisode): ?>
                <p class="text-warning small mb-0">Đang xem tập <?= (int)$activeEpisode['episode_number'] ?><?= !empty($activeEpisode['title']) ? ' - ' . sanitize($activeEpisode['title']) : '' ?></p>
                <?php endif; ?>
                <?php if ($movie['original_title']): ?>
                <p class="text-muted small mb-0"><?= sanitize($movie['original_title']) ?></p>
                <?php endif; ?>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <?php if ($movie['quality']): ?><span class="badge bg-warning text-dark"><?= sanitize($movie['quality']) ?></span><?php endif; ?>
                    <?php if ($movie['language']): ?><span class="badge bg-info text-dark"><?= sanitize($movie['language']) ?></span><?php endif; ?>
                    <?php if ($movie['year']): ?><span class="badge bg-secondary"><?= $movie['year'] ?></span><?php endif; ?>
                    <?php if ($movie['duration']): ?><span class="badge bg-secondary"><i class="fas fa-clock me-1"></i><?= sanitize($movie['duration']) ?></span><?php endif; ?>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/movie.php?id=<?= $id ?>" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-info-circle me-1"></i>Chi tiết
                </a>
            </div>
        </div>

        <?php if ($movie['description']): ?>
        <div class="mt-3 text-muted small watch-desc">
            <?= nl2br(sanitize(mb_substr($movie['description'], 0, 300))) ?>
            <?php if (mb_strlen($movie['description']) > 300): ?>...<a href="<?= BASE_URL ?>/movie.php?id=<?= $id ?>" class="text-danger">Xem thêm</a><?php endif; ?>
        </div>
        <?php endif; ?>

        <?php $episodeList = getEpisodesByMovie($id, true); if ($episodeList): ?>
        <div class="mt-3 d-flex flex-wrap gap-2">
            <?php foreach ($episodeList as $ep): ?>
            <a class="btn btn-sm <?= ($activeEpisode && (int)$activeEpisode['id'] === (int)$ep['id']) ? 'btn-danger' : 'btn-outline-light' ?>"
               href="<?= BASE_URL ?>/watch.php?id=<?= $id ?>&episode=<?= (int)$ep['id'] ?>">
               Tap <?= (int)$ep['episode_number'] ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</main>

<?php
$extraScripts = '
<script>
// Init Plyr for HTML5 video types
const playerEl = document.getElementById("player");
if (playerEl) {
    const player = new Plyr("#player", {
        controls: ["play-large","play","progress","current-time","duration","mute","volume","captions","settings","pip","airplay","fullscreen"],
        settings: ["quality","speed","loop"],
        speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] }
    });
}
</script>';
include __DIR__ . '/includes/footer.php';
?>
