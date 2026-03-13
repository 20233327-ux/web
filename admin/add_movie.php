<?php
$adminTitle = 'Thêm phim mới';
require_once __DIR__ . '/header.php';
requireMovieManager();

$genres    = getGenres();
$countries = getCountries();
$error     = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $origTitle    = trim($_POST['original_title'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $genreId      = (int)($_POST['genre_id'] ?? 0) ?: null;
    $countryId    = (int)($_POST['country_id'] ?? 0) ?: null;
    $year         = (int)($_POST['year'] ?? 0) ?: null;
    $director     = trim($_POST['director'] ?? '');
    $cast         = trim($_POST['cast_members'] ?? '');
    $duration     = trim($_POST['duration'] ?? '');
    $quality      = trim($_POST['quality'] ?? 'HD');
    $language     = trim($_POST['language'] ?? 'Vietsub');
    $trailerUrl   = trim($_POST['trailer_url'] ?? '');
    $videoType    = $_POST['video_type'] ?? 'url';
    $videoUrl     = trim($_POST['video_url'] ?? '');
    $videoUrlYt   = trim($_POST['video_url_youtube'] ?? '');
    $embedCode    = trim($_POST['embed_code'] ?? '');
    $thumbUrl     = trim($_POST['thumb_url'] ?? '');
    $featured     = isset($_POST['featured']) ? 1 : 0;
    $status       = $_POST['status'] ?? 'active';

    if (empty($title)) {
        $error = 'Vui lòng nhập tên phim.';
    } else {
        $slug      = uniqueSlug($title);
        $videoPath = '';
        $thumbnail = null;

        $pickedUrl = $videoType === 'youtube' ? $videoUrlYt : $videoUrl;
        [$videoType, $videoPath, $videoError] = resolveVideoSource($videoType, $pickedUrl, $embedCode, $_FILES['video_file'] ?? [], '');
        if ($videoError !== '') {
            $error = $videoError;
        }

        // Handle thumbnail
        if (!empty($_FILES['thumb_file']['name'])) {
            $uploaded = uploadImage($_FILES['thumb_file'], THUMB_UPLOAD_PATH);
            if (!$uploaded) { $error = 'File ảnh không hợp lệ (jpg, png, gif, webp, tối đa 10MB).'; }
            else { $thumbnail = $uploaded; }
        } elseif (!empty($thumbUrl) && isSafeHttpUrl($thumbUrl)) {
            $thumbnail = $thumbUrl;
        }

        if (!$error) {
            getDB()->prepare('INSERT INTO movies (title,slug,original_title,description,genre_id,country_id,year,director,cast_members,thumbnail,trailer_url,video_type,video_path,duration,quality,language,featured,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$title,$slug,$origTitle,$description,$genreId,$countryId,$year,$director,$cast,$thumbnail,$trailerUrl,$videoType,$videoPath,$duration,$quality,$language,$featured,$status]);
            header('Location: movies.php?saved=1');
            exit;
        }
    }
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white mb-0"><i class="fas fa-plus-circle me-2 text-danger"></i>Thêm phim mới</h4>
    <a href="movies.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger"><?= sanitize($error) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="row g-4">
        <!-- Left column -->
        <div class="col-lg-8">
            <div class="admin-card">
                <h6 class="text-warning mb-3">Thông tin cơ bản</h6>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label text-muted">Tên phim <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control admin-input" value="<?= sanitize($_POST['title'] ?? '') ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted">Tên gốc (tiếng Anh/nước ngoài)</label>
                        <input type="text" name="original_title" class="form-control admin-input" value="<?= sanitize($_POST['original_title'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted">Mô tả / Nội dung phim</label>
                        <textarea name="description" class="form-control admin-input" rows="5"><?= sanitize($_POST['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Thể loại</label>
                        <select name="genre_id" class="form-select admin-input">
                            <option value="">-- Chọn thể loại --</option>
                            <?php foreach ($genres as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= (int)($_POST['genre_id'] ?? 0) === (int)$g['id'] ? 'selected' : '' ?>>
                                <?= sanitize($g['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Quốc gia</label>
                        <select name="country_id" class="form-select admin-input">
                            <option value="">-- Chọn quốc gia --</option>
                            <?php foreach ($countries as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (int)($_POST['country_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
                                <?= sanitize($c['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted">Năm sản xuất</label>
                        <input type="number" name="year" class="form-control admin-input" min="1900" max="2030" value="<?= sanitize($_POST['year'] ?? date('Y')) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted">Thời lượng</label>
                        <input type="text" name="duration" class="form-control admin-input" placeholder="vd: 120 phút" value="<?= sanitize($_POST['duration'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted">Chất lượng</label>
                        <select name="quality" class="form-select admin-input">
                            <?php foreach (['CAM','TS','DVDRip','HD','FHD','4K'] as $q): ?>
                            <option value="<?= $q ?>" <?= ($_POST['quality'] ?? 'HD') === $q ? 'selected' : '' ?>><?= $q ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted">Ngôn ngữ</label>
                        <select name="language" class="form-select admin-input">
                            <?php foreach (['Vietsub','Thuyết minh','Lồng tiếng','Gốc'] as $lang): ?>
                            <option value="<?= $lang ?>" <?= ($_POST['language'] ?? 'Vietsub') === $lang ? 'selected' : '' ?>><?= $lang ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Đạo diễn</label>
                        <input type="text" name="director" class="form-control admin-input" value="<?= sanitize($_POST['director'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Diễn viên</label>
                        <input type="text" name="cast_members" class="form-control admin-input" placeholder="Ngăn cách bằng dấu phẩy" value="<?= sanitize($_POST['cast_members'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label text-muted">URL Trailer (YouTube)</label>
                        <input type="url" name="trailer_url" class="form-control admin-input" placeholder="https://www.youtube.com/watch?v=..." value="<?= sanitize($_POST['trailer_url'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Video Source -->
            <div class="admin-card mt-4">
                <h6 class="text-warning mb-3"><i class="fas fa-video me-2"></i>Nguồn video</h6>
                <div class="mb-3">
                    <div class="d-flex flex-wrap gap-2" id="videoTypeTabs">
                        <?php foreach (['file'=>'📁 Tải file lên','url'=>'🔗 Link trực tiếp','youtube'=>'▶️ YouTube','embed'=>'</> Embed code'] as $vt => $vl): ?>
                        <input type="radio" class="btn-check" name="video_type" id="vt_<?= $vt ?>" value="<?= $vt ?>" <?= ($_POST['video_type'] ?? 'url') === $vt ? 'checked' : '' ?>>
                        <label class="btn btn-sm btn-outline-secondary video-type-btn" for="vt_<?= $vt ?>"><?= $vl ?></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div id="vp_file" class="video-panel" style="display:none">
                    <label class="form-label text-muted">Chọn file MP4 (tối đa 2GB)</label>
                    <input type="file" name="video_file" class="form-control admin-input" accept="video/mp4,video/webm,video/ogg">
                    <small class="text-muted">Hỗ trợ: MP4, WebM, OGG. File sẽ được lưu trên máy chủ.</small>
                </div>
                <div id="vp_url" class="video-panel">
                    <label class="form-label text-muted">URL video trực tiếp</label>
                    <input type="url" name="video_url" id="video_url" class="form-control admin-input" placeholder="https://example.com/video.mp4" value="<?= sanitize($_POST['video_url'] ?? '') ?>">
                    <small class="text-muted">Link trực tiếp đến file .mp4, .webm, hoặc URL stream.</small>
                </div>
                <div id="vp_youtube" class="video-panel" style="display:none">
                    <label class="form-label text-muted">URL YouTube</label>
                    <input type="url" name="video_url_youtube" class="form-control admin-input" id="yt_url" placeholder="https://www.youtube.com/watch?v=..." value="<?= sanitize($_POST['video_url_youtube'] ?? '') ?>">
                    <small class="text-muted">URL đầy đủ của YouTube video.</small>
                </div>
                <div id="vp_embed" class="video-panel" style="display:none">
                    <label class="form-label text-muted">Embed code</label>
                    <textarea name="embed_code" class="form-control admin-input font-monospace" rows="4" placeholder='&lt;iframe src="..." ...&gt;&lt;/iframe&gt;'><?= sanitize($_POST['embed_code'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Right column -->
        <div class="col-lg-4">
            <div class="admin-card">
                <h6 class="text-warning mb-3">Ảnh bìa / Thumbnail</h6>
                <div id="thumbPreviewWrap" class="mb-3 text-center">
                    <img id="thumbPreview" src="<?= BASE_URL ?>/assets/images/no-thumb.svg" class="rounded w-100" style="max-height:200px;object-fit:cover">
                </div>
                <label class="form-label text-muted">Tải ảnh lên</label>
                <input type="file" name="thumb_file" id="thumbFile" class="form-control admin-input mb-2" accept="image/*">
                <label class="form-label text-muted">Hoặc dán URL ảnh</label>
                <input type="url" name="thumb_url" id="thumbUrl" class="form-control admin-input" placeholder="https://..." value="<?= sanitize($_POST['thumb_url'] ?? '') ?>">
            </div>

            <div class="admin-card mt-3">
                <h6 class="text-warning mb-3">Cài đặt hiển thị</h6>
                <div class="mb-3">
                    <label class="form-label text-muted">Trạng thái</label>
                    <select name="status" class="form-select admin-input">
                        <option value="active" <?= ($_POST['status'] ?? 'active')==='active'?'selected':'' ?>>✅ Hiện (Active)</option>
                        <option value="inactive" <?= ($_POST['status'] ?? '')==='inactive'?'selected':'' ?>>🔒 Ẩn (Inactive)</option>
                    </select>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="featured" id="featured" class="form-check-input" <?= isset($_POST['featured'])?'checked':'' ?>>
                    <label class="form-check-label text-muted" for="featured">
                        <i class="fas fa-star text-warning me-1"></i>Phim nổi bật (hiển thị ở banner)
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-danger w-100 mt-3 py-2 fw-bold">
                <i class="fas fa-save me-2"></i>Lưu phim
            </button>
            <a href="movies.php" class="btn btn-outline-secondary w-100 mt-2">Hủy</a>
        </div>
    </div>
</form>

<?php
$adminExtraScripts = <<<JS
<script>
// Video type tabs
function showVideoPanel(type) {
    document.querySelectorAll('.video-panel').forEach(p => p.style.display = 'none');
    const panel = document.getElementById('vp_' + type);
    if (panel) panel.style.display = 'block';
}
document.querySelectorAll('input[name="video_type"]').forEach(r => {
    r.addEventListener('change', () => showVideoPanel(r.value));
});
showVideoPanel(document.querySelector('input[name="video_type"]:checked')?.value || 'url');

// Thumbnail preview
document.getElementById('thumbFile').addEventListener('change', function() {
    if (this.files[0]) {
        document.getElementById('thumbPreview').src = URL.createObjectURL(this.files[0]);
    }
});
document.getElementById('thumbUrl').addEventListener('input', function() {
    if (this.value) document.getElementById('thumbPreview').src = this.value;
});
</script>
JS;
include __DIR__ . '/footer.php';
?>
