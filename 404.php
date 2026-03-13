<?php
http_response_code(404);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = '404 - Không tìm thấy trang';
include __DIR__ . '/includes/header.php';
?>
<main>
<div class="container text-center py-5" style="min-height:60vh;display:flex;flex-direction:column;align-items:center;justify-content:center">
    <div style="font-size:6rem">🎬</div>
    <h1 class="display-4 text-danger fw-bold">404</h1>
    <h3 class="text-white mb-3">Trang không tồn tại</h3>
    <p class="text-muted mb-4">Trang bạn tìm kiếm có thể đã bị xóa hoặc đường dẫn không đúng.</p>
    <a href="<?= BASE_URL ?>/index.php" class="btn btn-danger px-5"><i class="fas fa-home me-2"></i>Về trang chủ</a>
</div>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
