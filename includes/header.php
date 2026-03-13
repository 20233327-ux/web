<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

enforceHttpsRedirect();
enforceSecurityHeaders();

$currentUser = getCurrentUser();
$genres      = getGenres();
$searchQuery = sanitize($_GET['q'] ?? '');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' - ' . SITE_NAME : SITE_NAME ?></title>
    <meta name="description" content="<?= isset($pageDesc) ? sanitize($pageDesc) : 'Xem phim online miễn phí tại ' . SITE_NAME ?>">
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/favicon.svg" type="image/svg+xml">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Plyr (video player) -->
    <link href="https://cdn.plyr.io/3.7.8/plyr.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
    <div class="container-fluid px-3 px-md-4">
        <a class="navbar-brand fw-black" href="<?= BASE_URL ?>/index.php">
            <span class="brand-logo">🎬 <?= SITE_NAME ?></span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <!-- Search form -->
            <form class="d-flex mx-auto my-2 my-lg-0 nav-search" action="<?= BASE_URL ?>/search.php" method="GET">
                <div class="input-group">
                    <input type="search" name="q" class="form-control search-input" placeholder="Tìm kiếm phim..." value="<?= $searchQuery ?>">
                    <button class="btn btn-danger" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <!-- Nav links -->
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="<?= BASE_URL ?>/index.php"><i class="fas fa-home me-1"></i>Trang chủ</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-film me-1"></i>Thể loại
                    </a>
                    <ul class="dropdown-menu dropdown-dark">
                        <?php foreach ($genres as $g): ?>
                        <li>
                            <a class="dropdown-item" href="<?= BASE_URL ?>/genre.php?slug=<?= urlencode($g['slug']) ?>">
                                <?= sanitize($g['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php if ($currentUser): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/profile.php">
                            <i class="fas fa-user-circle me-1"></i><?= sanitize($currentUser['username']) ?>
                        </a>
                    </li>
                    <?php if (canAccessAdminPanel()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="<?= BASE_URL ?>/admin/index.php">
                            <i class="fas fa-cog me-1"></i>Admin
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Đăng xuất
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/login.php"><i class="fas fa-sign-in-alt me-1"></i>Đăng nhập</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-danger btn-sm px-3" href="<?= BASE_URL ?>/register.php">Đăng ký</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div style="padding-top:70px"></div>
