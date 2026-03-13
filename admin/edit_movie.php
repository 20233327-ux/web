<?php
$adminTitle = 'Sửa phim';
require_once __DIR__ . '/header.php';
requireMovieManager();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: movies.php'); exit; }

$movie     = getMovieByIdAdmin($id);
if (!$movie) { die('<div class="alert alert-danger">Phim không tồn tại.</div>'); }

$genres    = getGenres();
$countries = getCountries();
$error     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $origTitle   = trim($_POST['original_title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $genreId     = (int)($_POST['genre_id'] ?? 0) ?: null;
    $countryId   = (int)($_POST['country_id'] ?? 0) ?: null;
    $year        = (int)($_POST['year'] ?? 0) ?: null;
    $director    = trim($_POST['director'] ?? '');
    $cast        = trim($_POST['cast_members'] ?? '');
    $duration    = trim($_POST['duration'] ?? '');
    $quality     = trim($_POST['quality'] ?? 'HD');
    $language    = trim($_POST['language'] ?? 'Vietsub');
    $trailerUrl  = trim($_POST['trailer_url'] ?? '');
    $videoType   = $_POST['video_type'] ?? 'url';
    $videoUrl    = trim($_POST['video_url'] ?? '');
    $videoUrlYt  = trim($_POST['video_url_youtube'] ?? '');
    $embedCode   = trim($_POST['embed_code'] ?? '');
    $thumbUrl    = trim($_POST['thumb_url'] ?? '');
    $featured    = isset($_POST['featured']) ? 1 : 0;
    $status      = $_POST['status'] ?? 'active';

    if (empty($title)) {
        $error = 'Vui lòng nhập tên phim.';
    } else {
        $slug      = uniqueSlug($title, $id);
        $videoPath = $movie['video_path'];
        $thumbnail = $movie['thumbnail'];

        $pickedUrl = $videoType === 'youtube' ? $videoUrlYt : $videoUrl;
        [$videoType, $videoPath, $videoError] = resolveVideoSource($videoType, $pickedUrl, $embedCode, $_FILES['video_file'] ?? [], $videoPath);
        if ($videoError !== '') {
            $error = $videoError;
        }
        if (!$error && $videoType === 'file' && !empty($_FILES['video_file']['name']) && $movie['video_type'] === 'file' && !empty($movie['video_path']) && $movie['video_path'] !== $videoPath) {
            safeDeleteFile(VIDEO_UPLOAD_PATH . $movie['video_path']);
        }

        // Handle thumbnail
        if (!empty($_FILES['thumb_file']['name'])) {
            $uploaded = uploadImage($_FILES['thumb_file'], THUMB_UPLOAD_PATH);
            if (!$uploaded) { $error = 'File ảnh không hợp lệ.'; }
            else {
                // Delete old thumbnail file
                if ($thumbnail && !str_starts_with($thumbnail, 'http')) {
                    safeDeleteFile(THUMB_UPLOAD_PATH . $thumbnail);
                }
                $thumbnail = $uploaded;
            }
        } elseif (!empty($thumbUrl) && isSafeHttpUrl($thumbUrl)) {
            $thumbnail = $thumbUrl;
        }

        if (!$error) {
            getDB()->prepare('UPDATE movies SET title=?,slug=?,original_title=?,description=?,genre_id=?,country_id=?,year=?,director=?,cast_members=?,thumbnail=?,trailer_url=?,video_type=?,video_path=?,duration=?,quality=?,language=?,featured=?,status=? WHERE id=?')
                ->execute([$title,$slug,$origTitle,$description,$genreId,$countryId,$year,$director,$cast,$thumbnail,$trailerUrl,$videoType,$videoPath,$duration,$quality,$language,$featured,$status,$id]);
            header('Location: movies.php?saved=1');
            exit;
        }

        // Update $movie for re-display
        $movie = array_merge($movie, compact('title','origTitle','description','genreId','year','director','cast','duration','quality','language','trailerUrl','videoType','featured','status'));
        $movie['video_path'] = $videoPath;
        $movie['thumbnail']  = $thumbnail;
    }
}

$currentVideoUrl = '';
if (in_array($movie['video_type'], ['url','youtube'])) $currentVideoUrl = $movie['video_path'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white mb-0"><i class="fas fa-edit me-2 text-warning"></i>Sửa phim: <?= sanitize($movie['title']) ?></h4>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/watch.php?id=<?= $id ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-play me-1"></i>Xem phim</a>
        <a href="<?= BASE_URL ?>/admin/episodes.php?movie_id=<?= $id ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-list-ol me-1"></i>Tap phim</a>
        <a href="movies.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
    </div>
</div>

<?php if ($error): ?>
<div class="alert alert-danger"><?= sanitize($error) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="admin-card">
                <h6 class="text-warning mb-3">Thông tin cơ bản</h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted">Tên phim <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control admin-input" value="<?= sanitize($movie['title']) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted">Tên gốc</label>
                        <input type="text" name="original_title" class="form-control admin-input" value="<?= sanitize($movie['original_title'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted">Mô tả</label>
                        <textarea name="description" class="form-control admin-input" rows="5"><?= sanitize($movie['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Thể loại</label>
                        <select name="genre_id" class="form-select admin-input">
                            <option value="">-- Chọn --</option>
                            <?php foreach ($genres as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= (int)$movie['genre_id'] === (int)$g['id'] ? 'selected' : '' ?>><?= sanitize($g['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Quốc gia</label>
                        <select name="country_id" class="form-select admin-input">
                            <option value="">-- Chọn --</option>
                            <?php foreach ($countries as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (int)$movie['country_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3"><label class="form-label text-muted">Năm</label>
                        <input type="number" name="year" class="form-control admin-input" min="1900" max="2030" value="<?= $movie['year'] ?? '' ?>">
                    </div>
                    <div class="col-md-3"><label class="form-label text-muted">Thời lượng</label>
                        <input type="text" name="duration" class="form-control admin-input" value="<?= sanitize($movie['duration'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted">Chất lượng</label>
                        <select name="quality" class="form-select admin-input">
                            <?php foreach (['CAM','TS','DVDRip','HD','FHD','4K'] as $q): ?>
                            <option value="<?= $q ?>" <?= ($movie['quality'] ?? 'HD') === $q ? 'selected' : '' ?>><?= $q ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted">Ngôn ngữ</label>
                        <select name="language" class="form-select admin-input">
                            <?php foreach (['Vietsub','Thuyết minh','Lồng tiếng','Gốc'] as $lang): ?>
                            <option value="<?= $lang ?>" <?= ($movie['language'] ?? '') === $lang ? 'selected' : '' ?>><?= $lang ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Đạo diễn</label>
                        <input type="text" name="director" class="form-control admin-input" value="<?= sanitize($movie['director'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Diễn viên</label>
                        <input type="text" name="cast_members" class="form-control admin-input" value="<?= sanitize($movie['cast_members'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted">URL Trailer</label>
                        <input type="url" name="trailer_url" class="form-control admin-input" value="<?= sanitize($movie['trailer_url'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="admin-card mt-4">
                <h6 class="text-warning mb-3"><i class="fas fa-video me-2"></i>Nguồn video</h6>
                <p class="text-muted small mb-2">Loại hiện tại: <strong class="text-white"><?= $movie['video_type'] ?></strong></p>
                <div class="mb-3">
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach (['file'=>'📁 Tải file lên','url'=>'🔗 Link trực tiếp','youtube'=>'▶️ YouTube','embed'=>'</> Embed code'] as $vt => $vl): ?>
                        <input type="radio" class="btn-check" name="video_type" id="vt_<?= $vt ?>" value="<?= $vt ?>" <?= $movie['video_type'] === $vt ? 'checked' : '' ?>>
                        <label class="btn btn-sm btn-outline-secondary" for="vt_<?= $vt ?>"><?= $vl ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div id="vp_file" class="video-panel" style="display:none">
                    <?php if ($movie['video_type'] === 'file' && $movie['video_path']): ?>
                    <p class="text-success small mb-2">✅ File hiện tại: <?= sanitize(basename($movie['video_path'])) ?></p>
                    <?php endif; ?>
                    <input type="file" name="video_file" class="form-control admin-input" accept="video/mp4,video/webm,video/ogg">
                    <small class="text-muted">Để trống nếu không muốn đổi file video.</small>
                </div>
                <div id="vp_url" class="video-panel">
                    <label class="form-label text-muted">URL video</label>
                    <input type="url" name="video_url" class="form-control admin-input" value="<?= in_array($movie['video_type'],['url','youtube']) ? sanitize($movie['video_path'] ?? '') : '' ?>">
                </div>
                <div id="vp_youtube" class="video-panel" style="display:none">
                    <label class="form-label text-muted">URL YouTube</label>
                    <input type="url" name="video_url_youtube" class="form-control admin-input" value="<?= $movie['video_type']==='youtube' ? sanitize($movie['video_path'] ?? '') : '' ?>">
                </div>
                <div id="vp_embed" class="video-panel" style="display:none">
                    <label class="form-label text-muted">Embed code</label>
                    <textarea name="embed_code" class="form-control admin-input font-monospace" rows="4"><?= $movie['video_type']==='embed' ? htmlspecialchars($movie['video_path'] ?? '') : '' ?></textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="admin-card">
                <h6 class="text-warning mb-3">Ảnh bìa</h6>
                <div class="mb-3 text-center">
                    <img id="thumbPreview" src="<?= getThumbnailUrl($movie['thumbnail']) ?>" class="rounded w-100" style="max-height:200px;object-fit:cover">
                </div>
                <input type="file" name="thumb_file" id="thumbFile" class="form-control admin-input mb-2" accept="image/*">
                <input type="url" name="thumb_url" id="thumbUrl" class="form-control admin-input" placeholder="Hoặc dán URL ảnh mới" value="">
            </div>
            <div class="admin-card mt-3">
                <h6 class="text-warning mb-3">Cài đặt</h6>
                <div class="mb-3">
                    <label class="form-label text-muted">Trạng thái</label>
                    <select name="status" class="form-select admin-input">
                        <option value="active"   <?= $movie['status']==='active'?'selected':'' ?>>✅ Hiện</option>
                        <option value="inactive" <?= $movie['status']==='inactive'?'selected':'' ?>>🔒 Ẩn</option>
                    </select>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="featured" id="featured" class="form-check-input" <?= $movie['featured']?'checked':'' ?>>
                    <label class="form-check-label text-muted" for="featured">
                        <i class="fas fa-star text-warning me-1"></i>Phim nổi bật
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-warning w-100 mt-3 py-2 fw-bold text-dark">
                <i class="fas fa-save me-2"></i>Lưu thay đổi
            </button>
            <a href="movies.php" class="btn btn-outline-secondary w-100 mt-2">Hủy</a>
        </div>
    </div>
</form>

<?php
$adminExtraScripts = <<<JS
<script>
function showVideoPanel(type) {
    document.querySelectorAll('.video-panel').forEach(p => p.style.display = 'none');
    const panel = document.getElementById('vp_' + type);
    if (panel) panel.style.display = 'block';
}
document.querySelectorAll('input[name="video_type"]').forEach(r => {
    r.addEventListener('change', () => showVideoPanel(r.value));
});
showVideoPanel(document.querySelector('input[name="video_type"]:checked')?.value || 'url');

document.getElementById('thumbFile').addEventListener('change', function() {
    if (this.files[0]) document.getElementById('thumbPreview').src = URL.createObjectURL(this.files[0]);
});
document.getElementById('thumbUrl').addEventListener('input', function() {
    if (this.value) document.getElementById('thumbPreview').src = this.value;
});
</script>
JS;
include __DIR__ . '/footer.php';
?>
