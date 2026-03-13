<?php
$adminTitle = 'Dashboard';
require_once __DIR__ . '/header.php';

$totalMovies   = countAllMovies();
$totalUsers    = countAllUsers();
$totalComments = countAllComments();
$totalViews    = getTotalViews();
$latestMovies  = getAdminMovies(5, 0);
$latestUsers   = getAllUsers(5, 0);
?>
<h4 class="text-white mb-4"><i class="fas fa-tachometer-alt me-2 text-danger"></i>Dashboard</h4>

<!-- Stats cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-danger"><i class="fas fa-film"></i></div>
            <div class="stat-number"><?= number_format($totalMovies) ?></div>
            <div class="stat-label">Tổng phim</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-info"><i class="fas fa-users"></i></div>
            <div class="stat-number"><?= number_format($totalUsers) ?></div>
            <div class="stat-label">Người dùng</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning"><i class="fas fa-eye"></i></div>
            <div class="stat-number"><?= formatViews($totalViews) ?></div>
            <div class="stat-label">Tổng lượt xem</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-success"><i class="fas fa-comments"></i></div>
            <div class="stat-number"><?= number_format($totalComments) ?></div>
            <div class="stat-label">Bình luận</div>
        </div>
    </div>
</div>

<!-- Quick actions -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="admin-card">
            <h6 class="text-white mb-3">Thao tác nhanh</h6>
            <div class="d-flex flex-wrap gap-2">
                <?php if (canManageMovies()): ?>
                <a href="<?= BASE_URL ?>/admin/add_movie.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-plus me-1"></i>Thêm phim mới
                </a>
                <a href="<?= BASE_URL ?>/admin/movies.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-film me-1"></i>Quản lý phim
                </a>
                <?php endif; ?>
                <?php if (canManageCategories()): ?>
                <a href="<?= BASE_URL ?>/admin/categories.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-tags me-1"></i>Quản lý thể loại
                </a>
                <?php endif; ?>
                <?php if (canManageUsers()): ?>
                <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-users me-1"></i>Quản lý người dùng
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Latest movies -->
    <div class="col-md-7">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-white mb-0">Phim gần đây</h6>
                <a href="<?= BASE_URL ?>/admin/movies.php" class="btn btn-sm btn-outline-danger">Xem tất cả</a>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover table-sm">
                    <thead><tr><th>Tên phim</th><th>Thể loại</th><th>Lượt xem</th><th>Trạng thái</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($latestMovies as $m): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= getThumbnailUrl($m['thumbnail']) ?>" class="rounded" style="width:35px;height:25px;object-fit:cover">
                                    <span class="small"><?= sanitize(mb_substr($m['title'],0,30)) ?><?= mb_strlen($m['title'])>30?'…':'' ?></span>
                                </div>
                            </td>
                            <td><small class="text-muted"><?= sanitize($m['genre_name'] ?? '-') ?></small></td>
                            <td><small><?= formatViews($m['views']) ?></small></td>
                            <td>
                                <span class="badge <?= $m['status']==='active'?'bg-success':'bg-secondary' ?>">
                                    <?= $m['status']==='active'?'Hoạt động':'Ẩn' ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/edit_movie.php?id=<?= $m['id'] ?>" class="btn btn-xs btn-outline-warning" style="font-size:.7rem;padding:2px 8px">Sửa</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Latest users -->
    <div class="col-md-5">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-white mb-0">Người dùng mới</h6>
                <a href="<?= BASE_URL ?>/admin/users.php" class="btn btn-sm btn-outline-danger">Xem tất cả</a>
            </div>
            <ul class="list-unstyled">
                <?php foreach ($latestUsers as $u): ?>
                <li class="d-flex align-items-center gap-3 mb-3">
                    <div class="avatar-circle flex-shrink-0">
                        <?= strtoupper(substr($u['username'],0,1)) ?>
                    </div>
                    <div class="flex-grow-1">
                        <div class="text-white small fw-semibold"><?= sanitize($u['username']) ?></div>
                        <div class="text-muted" style="font-size:.72rem"><?= sanitize($u['email']) ?></div>
                    </div>
                    <span class="badge <?= $u['role']==='admin'?'bg-danger':'bg-secondary' ?> small">
                        <?= $u['role'] ?>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
