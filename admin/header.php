<?php
// Admin shared header / auth check
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdminPanelAccess();
enforceHttpsRedirect();
enforceSecurityHeaders();
$adminUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($adminTitle) ? sanitize($adminTitle) . ' - Admin' : 'Admin Panel' ?> | <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body class="admin-body">
<!-- Sidebar -->
<div class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">
            <span style="color:#e50914;font-size:1.4rem;font-weight:900">🎬 <?= SITE_NAME ?></span>
        </a>
        <small class="d-block text-muted" style="font-size:.7rem">Admin Panel</small>
    </div>
    <nav class="sidebar-nav mt-3">
        <?php $cp = basename($_SERVER['PHP_SELF']); ?>
        <a href="<?= BASE_URL ?>/admin/index.php"       class="sidebar-link <?= $cp==='index.php'?'active':'' ?>"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
        <?php if (canManageMovies()): ?>
        <a href="<?= BASE_URL ?>/admin/movies.php"      class="sidebar-link <?= $cp==='movies.php'?'active':'' ?>"><i class="fas fa-film"></i>Quản lý phim</a>
        <a href="<?= BASE_URL ?>/admin/add_movie.php"   class="sidebar-link <?= $cp==='add_movie.php'?'active':'' ?>"><i class="fas fa-plus-circle"></i>Thêm phim</a>
        <?php endif; ?>
        <?php if (canManageCategories()): ?>
        <a href="<?= BASE_URL ?>/admin/categories.php"  class="sidebar-link <?= $cp==='categories.php'?'active':'' ?>"><i class="fas fa-tags"></i>Thể loại</a>
        <?php endif; ?>
        <?php if (canManageUsers()): ?>
        <a href="<?= BASE_URL ?>/admin/users.php"       class="sidebar-link <?= $cp==='users.php'?'active':'' ?>"><i class="fas fa-users"></i>Người dùng</a>
        <?php endif; ?>
        <?php if (canManageComments()): ?>
        <a href="<?= BASE_URL ?>/admin/comments.php"    class="sidebar-link <?= $cp==='comments.php'?'active':'' ?>"><i class="fas fa-comments"></i>Bình luận</a>
        <?php endif; ?>
        <hr class="border-secondary my-2">
        <a href="<?= BASE_URL ?>/index.php"  class="sidebar-link"><i class="fas fa-globe"></i>Xem trang web</a>
        <a href="<?= BASE_URL ?>/logout.php" class="sidebar-link text-danger"><i class="fas fa-sign-out-alt"></i>Đăng xuất</a>
    </nav>
</div>
<!-- Main content wrapper -->
<div class="admin-main" id="adminMain">
    <!-- Top bar -->
    <div class="admin-topbar d-flex justify-content-between align-items-center px-4 py-2">
        <button class="btn btn-sm btn-outline-secondary" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small">Xin chào, <strong class="text-white"><?= sanitize($adminUser['username']) ?></strong></span>
            <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-outline-danger">
                <i class="fas fa-sign-out-alt me-1"></i>Đăng xuất
            </a>
        </div>
    </div>
    <div class="admin-content px-4 py-3">
