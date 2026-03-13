    </div><!-- /.container or .main-content -->
</main>

<!-- Footer -->
<footer class="site-footer mt-5 pt-5 pb-3">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="brand-logo mb-3">🎬 <?= SITE_NAME ?></h5>
                <p class="text-muted small">Xem phim online chất lượng cao, miễn phí. Cập nhật phim mới nhất từ khắp nơi trên thế giới.</p>
            </div>
            <div class="col-md-4">
                <h6 class="text-white mb-3">Thể loại phim</h6>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (array_slice($genres ?? [], 0, 8) as $g): ?>
                    <a href="<?= BASE_URL ?>/genre.php?slug=<?= urlencode($g['slug']) ?>" class="badge bg-secondary text-decoration-none">
                        <?= sanitize($g['name']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-4">
                <h6 class="text-white mb-3">Liên kết nhanh</h6>
                <ul class="list-unstyled small">
                    <li><a href="<?= BASE_URL ?>/index.php" class="footer-link"><i class="fas fa-home me-2"></i>Trang chủ</a></li>
                    <li><a href="<?= BASE_URL ?>/search.php" class="footer-link"><i class="fas fa-search me-2"></i>Tìm kiếm</a></li>
                    <?php if (isset($currentUser) && $currentUser): ?>
                    <li><a href="<?= BASE_URL ?>/profile.php" class="footer-link"><i class="fas fa-user me-2"></i>Hồ sơ</a></li>
                    <?php else: ?>
                    <li><a href="<?= BASE_URL ?>/login.php" class="footer-link"><i class="fas fa-sign-in-alt me-2"></i>Đăng nhập</a></li>
                    <li><a href="<?= BASE_URL ?>/register.php" class="footer-link"><i class="fas fa-user-plus me-2"></i>Đăng ký</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <hr class="border-secondary mt-4">
        <p class="text-center text-muted small mb-0">
            &copy; <?= date('Y') ?> <?= SITE_NAME ?>. Thiết kế bởi PHP &amp; Bootstrap 5.
        </p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Plyr JS -->
<script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
<!-- Custom JS -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
