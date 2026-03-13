<?php
$adminTitle = 'Quản lý tập phim';
require_once __DIR__ . '/header.php';
requireMovieManager();

$movieId = filter_input(INPUT_GET, 'movie_id', FILTER_VALIDATE_INT);
if (!$movieId) {
    header('Location: movies.php');
    exit;
}

$movie = getMovieByIdAdmin($movieId);
if (!$movie) {
    header('Location: movies.php');
    exit;
}

$csrf = generateCsrfToken();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Yeu cau khong hop le.';
    } else {
        if (isset($_POST['add_episode'])) {
            $episodeNumber = max(1, (int)($_POST['episode_number'] ?? 1));
            $title = trim($_POST['title'] ?? '');
            $duration = trim($_POST['duration'] ?? '');
            $status = ($_POST['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
            $videoType = $_POST['video_type'] ?? 'url';
            $videoUrl = trim($_POST['video_url'] ?? '');
            $existing = '';
            [$videoType, $videoPath, $videoError] = resolveEpisodeVideoSource($videoType, $videoUrl, $_FILES['video_file'] ?? [], $existing);
            if ($videoError !== '') {
                $error = $videoError;
            } else {
                try {
                    getDB()->prepare('INSERT INTO movie_episodes (movie_id, episode_number, title, video_type, video_path, duration, status) VALUES (?, ?, ?, ?, ?, ?, ?)')
                        ->execute([$movieId, $episodeNumber, $title, $videoType, $videoPath, $duration, $status]);
                    header('Location: episodes.php?movie_id=' . $movieId . '&saved=1');
                    exit;
                } catch (PDOException $e) {
                    $error = 'So tap da ton tai cho phim nay.';
                }
            }
        }

        if (isset($_POST['delete_episode'])) {
            $episodeId = filter_input(INPUT_POST, 'episode_id', FILTER_VALIDATE_INT);
            if ($episodeId) {
                $episode = getEpisodeByIdAdmin($episodeId);
                if ($episode && (int)$episode['movie_id'] === $movieId) {
                    if (($episode['video_type'] ?? '') === 'file' && !empty($episode['video_path'])) {
                        safeDeleteFile(VIDEO_UPLOAD_PATH . $episode['video_path']);
                    }
                    getDB()->prepare('DELETE FROM movie_episodes WHERE id=?')->execute([$episodeId]);
                }
            }
            header('Location: episodes.php?movie_id=' . $movieId . '&deleted=1');
            exit;
        }

        if (isset($_POST['toggle_status'])) {
            $episodeId = filter_input(INPUT_POST, 'episode_id', FILTER_VALIDATE_INT);
            if ($episodeId) {
                getDB()->prepare("UPDATE movie_episodes SET status=IF(status='active','inactive','active') WHERE id=? AND movie_id=?")
                    ->execute([$episodeId, $movieId]);
            }
            header('Location: episodes.php?movie_id=' . $movieId);
            exit;
        }
    }
}

$episodes = getEpisodesByMovie($movieId, false);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white mb-0"><i class="fas fa-list-ol me-2 text-danger"></i>Tap phim: <?= sanitize($movie['title']) ?></h4>
    <a href="edit_movie.php?id=<?= $movieId ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Quay lai phim</a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger"><?= sanitize($error) ?></div>
<?php endif; ?>
<?php if (isset($_GET['saved'])): ?>
<div class="alert alert-success alert-auto">Da them tap phim.</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<div class="alert alert-success alert-auto">Da xoa tap phim.</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="admin-card">
            <h6 class="text-warning mb-3">Them tap moi</h6>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="add_episode" value="1">
                <div class="mb-3">
                    <label class="form-label text-muted">So tap</label>
                    <input type="number" name="episode_number" class="form-control admin-input" min="1" value="<?= max(1, count($episodes) + 1) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Tieu de tap</label>
                    <input type="text" name="title" class="form-control admin-input" placeholder="VD: Tap 1 - Khoi dau">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Thoi luong</label>
                    <input type="text" name="duration" class="form-control admin-input" placeholder="VD: 45 phut">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Loai nguon</label>
                    <select name="video_type" id="episodeVideoType" class="form-select admin-input">
                        <option value="url">URL truc tiep</option>
                        <option value="youtube">YouTube</option>
                        <option value="file">Tai file len</option>
                    </select>
                </div>
                <div class="mb-3" id="episodeUrlWrap">
                    <label class="form-label text-muted">URL video</label>
                    <input type="url" name="video_url" class="form-control admin-input" placeholder="https://..." >
                </div>
                <div class="mb-3" id="episodeFileWrap" style="display:none">
                    <label class="form-label text-muted">File video</label>
                    <input type="file" name="video_file" class="form-control admin-input" accept="video/mp4,video/webm,video/ogg">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Trang thai</label>
                    <select name="status" class="form-select admin-input">
                        <option value="active">Hien</option>
                        <option value="inactive">An</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-danger w-100"><i class="fas fa-plus me-1"></i>Them tap</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="admin-card p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr><th>Tap</th><th>Tieu de</th><th>Nguon</th><th>Trang thai</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php if ($episodes): ?>
                        <?php foreach ($episodes as $ep): ?>
                        <tr>
                            <td class="text-white fw-semibold">Tap <?= (int)$ep['episode_number'] ?></td>
                            <td><small class="text-muted"><?= sanitize($ep['title'] ?: ('Tap ' . $ep['episode_number'])) ?></small></td>
                            <td><small><?= sanitize($ep['video_type']) ?></small></td>
                            <td>
                                <span class="badge <?= $ep['status']==='active'?'bg-success':'bg-secondary' ?>"><?= $ep['status']==='active'?'Hien':'An' ?></span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= BASE_URL ?>/watch.php?id=<?= $movieId ?>&episode=<?= $ep['id'] ?>" target="_blank" class="btn btn-xs btn-outline-info" style="font-size:.7rem;padding:2px 6px"><i class="fas fa-play"></i></a>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                        <input type="hidden" name="episode_id" value="<?= $ep['id'] ?>">
                                        <input type="hidden" name="toggle_status" value="1">
                                        <button class="btn btn-xs btn-outline-warning" style="font-size:.7rem;padding:2px 6px"><i class="fas fa-eye"></i></button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                        <input type="hidden" name="episode_id" value="<?= $ep['id'] ?>">
                                        <input type="hidden" name="delete_episode" value="1">
                                        <button class="btn btn-xs btn-outline-danger" style="font-size:.7rem;padding:2px 6px" onclick="return confirm('Xoa tap nay?')"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Chua co tap nao.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$adminExtraScripts = '<script>
const typeEl = document.getElementById("episodeVideoType");
function refreshEpisodeTypeUI() {
  const isFile = typeEl.value === "file";
  document.getElementById("episodeFileWrap").style.display = isFile ? "block" : "none";
  document.getElementById("episodeUrlWrap").style.display = isFile ? "none" : "block";
}
typeEl.addEventListener("change", refreshEpisodeTypeUI);
refreshEpisodeTypeUI();
</script>';
include __DIR__ . '/footer.php';
?>
