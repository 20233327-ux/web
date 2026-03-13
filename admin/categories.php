<?php
$adminTitle = 'Quản lý thể loại';
require_once __DIR__ . '/header.php';
requireCategoryManager();

$pdo  = getDB();
$csrf = generateCsrfToken();
$error = $success = '';

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_genre'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) { $error = 'CSRF invalid'; }
    else {
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) { $error = 'Vui lòng nhập tên thể loại.'; }
        else {
            $slug = slugify($name);
            try {
                $pdo->prepare('INSERT INTO genres (name, slug) VALUES (?,?)')->execute([$name, $slug]);
                $success = "Đã thêm thể loại: $name";
            } catch (PDOException $e) {
                $error = 'Slug đã tồn tại. Vui lòng chọn tên khác.';
            }
        }
    }
}
// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_genre'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) { $error = 'CSRF invalid'; }
    else {
        $gid  = filter_input(INPUT_POST, 'genre_id', FILTER_VALIDATE_INT);
        $name = trim($_POST['name'] ?? '');
        if ($gid && $name) {
            $slug = slugify($name);
            try {
                $pdo->prepare('UPDATE genres SET name=?,slug=? WHERE id=?')->execute([$name,$slug,$gid]);
                $success = 'Đã cập nhật thể loại.';
            } catch (PDOException $e) {
                $error = 'Slug đã tồn tại.';
            }
        }
    }
}
// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_genre'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) { $error = 'CSRF invalid'; }
    else {
        $gid = filter_input(INPUT_POST, 'genre_id', FILTER_VALIDATE_INT);
        if ($gid) {
            // Set movies.genre_id = NULL before deleting
            $pdo->prepare('UPDATE movies SET genre_id=NULL WHERE genre_id=?')->execute([$gid]);
            $pdo->prepare('DELETE FROM genres WHERE id=?')->execute([$gid]);
            $success = 'Đã xóa thể loại.';
        }
    }
}

$genres = $pdo->query('SELECT g.*, COUNT(m.id) movie_count FROM genres g LEFT JOIN movies m ON g.id=m.genre_id GROUP BY g.id ORDER BY g.name')->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-white mb-0"><i class="fas fa-tags me-2 text-warning"></i>Quản lý thể loại</h4>
</div>

<?php if ($error):   ?><div class="alert alert-danger   alert-auto"><?= sanitize($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success alert-auto"><?= sanitize($success) ?></div><?php endif; ?>

<div class="row g-4">
    <!-- Add genre form -->
    <div class="col-md-4">
        <div class="admin-card">
            <h6 class="text-warning mb-3"><i class="fas fa-plus me-2"></i>Thêm thể loại mới</h6>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="add_genre" value="1">
                <div class="mb-3">
                    <label class="form-label text-muted">Tên thể loại</label>
                    <input type="text" name="name" class="form-control admin-input" placeholder="vd: Hành động" required>
                </div>
                <button type="submit" class="btn btn-danger w-100">
                    <i class="fas fa-plus me-1"></i>Thêm thể loại
                </button>
            </form>
        </div>

        <!-- Add country -->
        <div class="admin-card mt-3">
            <h6 class="text-info mb-3"><i class="fas fa-globe me-2"></i>Thêm quốc gia</h6>
            <?php
            // Handle add country
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_country'])) {
                if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                    $cname = trim($_POST['country_name'] ?? '');
                    if ($cname) {
                        try {
                            $pdo->prepare('INSERT INTO countries (name,slug) VALUES (?,?)')->execute([$cname, slugify($cname)]);
                            echo '<div class="alert alert-success alert-auto small">Đã thêm: '.sanitize($cname).'</div>';
                        } catch(PDOException $e) { echo '<div class="alert alert-danger small">Đã tồn tại.</div>'; }
                    }
                }
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_country'])) {
                if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                    $cid = filter_input(INPUT_POST, 'country_id', FILTER_VALIDATE_INT);
                    if ($cid) {
                        $pdo->prepare('UPDATE movies SET country_id=NULL WHERE country_id=?')->execute([$cid]);
                        $pdo->prepare('DELETE FROM countries WHERE id=?')->execute([$cid]);
                    }
                }
            }
            $countries = $pdo->query('SELECT * FROM countries ORDER BY name')->fetchAll();
            ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <input type="hidden" name="add_country" value="1">
                <div class="input-group">
                    <input type="text" name="country_name" class="form-control admin-input" placeholder="Tên quốc gia" required>
                    <button class="btn btn-info btn-sm"><i class="fas fa-plus"></i></button>
                </div>
            </form>
            <ul class="list-unstyled mt-2">
                <?php foreach ($countries as $c): ?>
                <li class="d-flex justify-content-between align-items-center py-1 border-bottom border-secondary">
                    <small class="text-white"><?= sanitize($c['name']) ?></small>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="hidden" name="country_id" value="<?= $c['id'] ?>">
                        <input type="hidden" name="delete_country" value="1">
                        <button class="btn btn-xs btn-outline-danger p-1" style="font-size:.65rem"
                                onclick="return confirm('Xóa quốc gia <?= sanitize($c['name']) ?>?')">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Genres table -->
    <div class="col-md-8">
        <div class="admin-card p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead><tr><th>#</th><th>Tên thể loại</th><th>Slug</th><th>Số phim</th><th>Thao tác</th></tr></thead>
                    <tbody>
                        <?php foreach ($genres as $g): ?>
                        <tr id="genre-row-<?= $g['id'] ?>">
                            <td class="text-muted"><?= $g['id'] ?></td>
                            <td>
                                <span class="genre-name-<?= $g['id'] ?>"><?= sanitize($g['name']) ?></span>
                                <!-- Inline edit form (hidden) -->
                                <form method="POST" class="genre-edit-form-<?= $g['id'] ?>" style="display:none">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                    <input type="hidden" name="edit_genre" value="1">
                                    <input type="hidden" name="genre_id" value="<?= $g['id'] ?>">
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="name" class="form-control admin-input" value="<?= sanitize($g['name']) ?>" required>
                                        <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="cancelEdit(<?= $g['id'] ?>)"><i class="fas fa-times"></i></button>
                                    </div>
                                </form>
                            </td>
                            <td><small class="text-muted font-monospace"><?= sanitize($g['slug']) ?></small></td>
                            <td>
                                <a href="<?= BASE_URL ?>/genre.php?slug=<?= urlencode($g['slug']) ?>" class="text-info text-decoration-none">
                                    <?= number_format($g['movie_count']) ?> phim
                                </a>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-xs btn-outline-warning" style="font-size:.7rem;padding:2px 6px"
                                            onclick="startEdit(<?= $g['id'] ?>)" title="Sửa">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                        <input type="hidden" name="genre_id" value="<?= $g['id'] ?>">
                                        <input type="hidden" name="delete_genre" value="1">
                                        <button class="btn btn-xs btn-outline-danger" style="font-size:.7rem;padding:2px 6px"
                                                onclick="return confirm('Xóa thể loại &quot;<?= sanitize($g['name']) ?>&quot;?')" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$adminExtraScripts = <<<JS
<script>
function startEdit(id) {
    document.querySelector('.genre-name-'+id).style.display = 'none';
    document.querySelector('.genre-edit-form-'+id).style.display = 'block';
}
function cancelEdit(id) {
    document.querySelector('.genre-name-'+id).style.display = '';
    document.querySelector('.genre-edit-form-'+id).style.display = 'none';
}
</script>
JS;
include __DIR__ . '/footer.php';
?>
