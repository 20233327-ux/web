<?php
require_once __DIR__ . '/../config/database.php';

// ── Sanitize ────────────────────────────────────────────────
function sanitize(string $s): string {
    return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8');
}

// ── Slug / URL ───────────────────────────────────────────────
function slugify(string $text): string {
    $vn = ['à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
           'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ','ì','í','ị','ỉ','ĩ',
           'ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ',
           'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ','ỳ','ý','ỵ','ỷ','ỹ','đ',
           'À','Á','Ạ','Ả','Ã','Â','Ầ','Ấ','Ậ','Ẩ','Ẫ','Ă','Ằ','Ắ','Ặ','Ẳ','Ẵ',
           'È','É','Ẹ','Ẻ','Ẽ','Ê','Ề','Ế','Ệ','Ể','Ễ','Ì','Í','Ị','Ỉ','Ĩ',
           'Ò','Ó','Ọ','Ỏ','Õ','Ô','Ồ','Ố','Ộ','Ổ','Ỗ','Ơ','Ờ','Ớ','Ợ','Ở','Ỡ',
           'Ù','Ú','Ụ','Ủ','Ũ','Ư','Ừ','Ứ','Ự','Ử','Ữ','Ỳ','Ý','Ỵ','Ỷ','Ỹ','Đ'];
    $en = ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
           'e','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i',
           'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
           'u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y','d',
           'A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A',
           'E','E','E','E','E','E','E','E','E','E','E','I','I','I','I','I',
           'O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O',
           'U','U','U','U','U','U','U','U','U','U','U','Y','Y','Y','Y','Y','D'];
    $text = str_replace($vn, $en, $text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function uniqueSlug(string $title, ?int $excludeId = null): string {
    $pdo  = getDB();
    $slug = $base = slugify($title);
    $i    = 1;
    while (true) {
        $q = 'SELECT id FROM movies WHERE slug=?';
        $p = [$slug];
        if ($excludeId !== null) { $q .= ' AND id!=?'; $p[] = $excludeId; }
        $stmt = $pdo->prepare($q); $stmt->execute($p);
        if (!$stmt->fetch()) break;
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

// ── Genres / Countries ───────────────────────────────────────
function getGenres(): array {
    return getDB()->query('SELECT * FROM genres ORDER BY name')->fetchAll();
}
function getCountries(): array {
    return getDB()->query('SELECT * FROM countries ORDER BY name')->fetchAll();
}
function getGenreBySlug(string $slug): ?array {
    $stmt = getDB()->prepare('SELECT * FROM genres WHERE slug=?');
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

// ── Movies ───────────────────────────────────────────────────
function getMovieById(int $id): ?array {
    $stmt = getDB()->prepare('SELECT m.*,g.name genre_name,g.slug genre_slug,c.name country_name
        FROM movies m LEFT JOIN genres g ON m.genre_id=g.id LEFT JOIN countries c ON m.country_id=c.id
        WHERE m.id=? AND m.status="active"');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getEpisodesByMovie(int $movieId, bool $onlyActive = true): array {
    $where = $onlyActive ? ' AND status="active"' : '';
    $stmt = getDB()->prepare('SELECT * FROM movie_episodes WHERE movie_id=?' . $where . ' ORDER BY episode_number ASC, id ASC');
    $stmt->execute([$movieId]);
    return $stmt->fetchAll();
}

function getEpisodeById(int $episodeId): ?array {
    $stmt = getDB()->prepare('SELECT * FROM movie_episodes WHERE id=? AND status="active"');
    $stmt->execute([$episodeId]);
    return $stmt->fetch() ?: null;
}

function getEpisodeByIdAdmin(int $episodeId): ?array {
    $stmt = getDB()->prepare('SELECT * FROM movie_episodes WHERE id=?');
    $stmt->execute([$episodeId]);
    return $stmt->fetch() ?: null;
}
function getMovieByIdAdmin(int $id): ?array {
    $stmt = getDB()->prepare('SELECT m.*,g.name genre_name,c.name country_name
        FROM movies m LEFT JOIN genres g ON m.genre_id=g.id LEFT JOIN countries c ON m.country_id=c.id
        WHERE m.id=?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
function getMovieBySlug(string $slug): ?array {
    $stmt = getDB()->prepare('SELECT m.*,g.name genre_name,g.slug genre_slug,c.name country_name
        FROM movies m LEFT JOIN genres g ON m.genre_id=g.id LEFT JOIN countries c ON m.country_id=c.id
        WHERE m.slug=? AND m.status="active"');
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function getMovies(int $limit = 12, int $offset = 0, ?int $genreId = null, string $sort = 'created_at'): array {
    $allowed = ['created_at','views','rating','year','title'];
    $col  = in_array($sort, $allowed) ? $sort : 'created_at';
    $where = 'WHERE m.status="active"';
    $params = [];
    if ($genreId !== null) { $where .= ' AND m.genre_id=?'; $params[] = $genreId; }
    $params[] = $limit; $params[] = $offset;
    $stmt = getDB()->prepare("SELECT m.*,g.name genre_name FROM movies m LEFT JOIN genres g ON m.genre_id=g.id $where ORDER BY m.$col DESC LIMIT ? OFFSET ?");
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function countMovies(?int $genreId = null): int {
    if ($genreId !== null) {
        $stmt = getDB()->prepare('SELECT COUNT(*) FROM movies WHERE status="active" AND genre_id=?');
        $stmt->execute([$genreId]);
    } else {
        $stmt = getDB()->query('SELECT COUNT(*) FROM movies WHERE status="active"');
    }
    return (int)$stmt->fetchColumn();
}

function getFeaturedMovies(int $limit = 5): array {
    $stmt = getDB()->prepare('SELECT m.*,g.name genre_name FROM movies m LEFT JOIN genres g ON m.genre_id=g.id WHERE m.status="active" AND m.featured=1 ORDER BY m.created_at DESC LIMIT ?');
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getLatestMovies(int $limit = 12): array {
    $stmt = getDB()->prepare('SELECT m.*,g.name genre_name FROM movies m LEFT JOIN genres g ON m.genre_id=g.id WHERE m.status="active" ORDER BY m.created_at DESC LIMIT ?');
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getTopRatedMovies(int $limit = 6): array {
    $stmt = getDB()->prepare('SELECT m.*,g.name genre_name FROM movies m LEFT JOIN genres g ON m.genre_id=g.id WHERE m.status="active" AND m.rating_count>0 ORDER BY m.rating DESC,m.views DESC LIMIT ?');
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getMostViewedMovies(int $limit = 6): array {
    $stmt = getDB()->prepare('SELECT m.*,g.name genre_name FROM movies m LEFT JOIN genres g ON m.genre_id=g.id WHERE m.status="active" ORDER BY m.views DESC LIMIT ?');
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function searchMovies(string $q, int $limit = 12, int $offset = 0): array {
    $like = '%' . $q . '%';
    $stmt = getDB()->prepare('SELECT m.*,g.name genre_name FROM movies m LEFT JOIN genres g ON m.genre_id=g.id WHERE m.status="active" AND (m.title LIKE ? OR m.original_title LIKE ? OR m.director LIKE ? OR m.cast_members LIKE ?) ORDER BY m.views DESC LIMIT ? OFFSET ?');
    $stmt->execute([$like,$like,$like,$like,$limit,$offset]);
    return $stmt->fetchAll();
}
function countSearch(string $q): int {
    $like = '%' . $q . '%';
    $stmt = getDB()->prepare('SELECT COUNT(*) FROM movies WHERE status="active" AND (title LIKE ? OR original_title LIKE ? OR director LIKE ? OR cast_members LIKE ?)');
    $stmt->execute([$like,$like,$like,$like]);
    return (int)$stmt->fetchColumn();
}

function getRelatedMovies(int $movieId, ?int $genreId, int $limit = 6): array {
    $params = [];
    $where  = 'm.id != ? AND m.status = "active"';
    $params[] = $movieId;
    if ($genreId) { $where .= ' AND m.genre_id = ?'; $params[] = $genreId; }
    $params[] = $limit;
    $stmt = getDB()->prepare("SELECT m.*,g.name genre_name FROM movies m LEFT JOIN genres g ON m.genre_id=g.id WHERE $where ORDER BY m.views DESC LIMIT ?");
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function incrementViews(int $id): void {
    getDB()->prepare('UPDATE movies SET views=views+1 WHERE id=?')->execute([$id]);
}

// ── Watch history ────────────────────────────────────────────
function addToHistory(int $userId, int $movieId): void {
    getDB()->prepare('INSERT INTO watch_history (user_id,movie_id) VALUES (?,?) ON DUPLICATE KEY UPDATE watched_at=CURRENT_TIMESTAMP')->execute([$userId,$movieId]);
}
function getWatchHistory(int $userId, int $limit = 20): array {
    $stmt = getDB()->prepare('SELECT m.*,wh.watched_at,g.name genre_name FROM watch_history wh JOIN movies m ON wh.movie_id=m.id LEFT JOIN genres g ON m.genre_id=g.id WHERE wh.user_id=? AND m.status="active" ORDER BY wh.watched_at DESC LIMIT ?');
    $stmt->execute([$userId,$limit]);
    return $stmt->fetchAll();
}

// ── Ratings ──────────────────────────────────────────────────
function getUserRating(int $userId, int $movieId): ?int {
    $stmt = getDB()->prepare('SELECT rating FROM ratings WHERE user_id=? AND movie_id=?');
    $stmt->execute([$userId,$movieId]);
    $r = $stmt->fetch();
    return $r ? (int)$r['rating'] : null;
}
function rateMovie(int $userId, int $movieId, int $rating): bool {
    if ($rating < 1 || $rating > 10) return false;
    $pdo = getDB();
    $pdo->prepare('INSERT INTO ratings (user_id,movie_id,rating) VALUES (?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating)')->execute([$userId,$movieId,$rating]);
    $pdo->prepare('UPDATE movies SET rating=(SELECT AVG(rating) FROM ratings WHERE movie_id=?), rating_count=(SELECT COUNT(*) FROM ratings WHERE movie_id=?) WHERE id=?')->execute([$movieId,$movieId,$movieId]);
    return true;
}

// ── Comments ─────────────────────────────────────────────────
function getComments(int $movieId, int $limit = 30): array {
    $stmt = getDB()->prepare('SELECT c.*,u.username,u.avatar FROM comments c JOIN users u ON c.user_id=u.id WHERE c.movie_id=? AND c.status="active" ORDER BY c.created_at DESC LIMIT ?');
    $stmt->execute([$movieId,$limit]);
    return $stmt->fetchAll();
}
function addComment(int $userId, int $movieId, string $content): bool {
    $content = trim($content);
    if (empty($content) || mb_strlen($content) > 1000) return false;
    return getDB()->prepare('INSERT INTO comments (user_id,movie_id,content) VALUES (?,?,?)')->execute([$userId,$movieId,$content]);
}

// ── Helpers ──────────────────────────────────────────────────
function formatViews(int $v): string {
    if ($v >= 1000000) return round($v/1000000,1).'M';
    if ($v >= 1000)    return round($v/1000,1).'K';
    return (string)$v;
}
function timeAgo(string $dt): string {
    $diff = (new DateTime())->diff(new DateTime($dt));
    if ($diff->y > 0) return $diff->y.' năm trước';
    if ($diff->m > 0) return $diff->m.' tháng trước';
    if ($diff->d > 0) return $diff->d.' ngày trước';
    if ($diff->h > 0) return $diff->h.' giờ trước';
    if ($diff->i > 0) return $diff->i.' phút trước';
    return 'Vừa xong';
}
function getThumbnailUrl(?string $thumb): string {
    if (empty($thumb)) return BASE_URL.'/assets/images/no-thumb.svg';
    if (str_starts_with($thumb,'http://') || str_starts_with($thumb,'https://')) return $thumb;
    return BASE_URL.'/uploads/thumbnails/'.$thumb;
}
function getYoutubeId(string $url): ?string {
    preg_match('/(?:youtube(?:-nocookie)?\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m);
    return $m[1] ?? null;
}
function isYoutubeUrl(string $url): bool {
    return (bool)preg_match('/(?:youtube\.com|youtu\.be)/i', $url);
}

function isSafeHttpUrl(string $url): bool {
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    $parts = parse_url($url);
    if (!$parts || empty($parts['scheme']) || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
        return false;
    }
    return true;
}

function sanitizeEmbedCode(string $embedCode): string {
    $embedCode = trim($embedCode);
    if ($embedCode === '') {
        return '';
    }
    if (!preg_match('/<iframe[^>]*src=["\']([^"\']+)["\'][^>]*><\/iframe>/i', $embedCode, $m)) {
        return '';
    }
    $src = $m[1];
    if (!isSafeHttpUrl($src)) {
        return '';
    }
    $host = strtolower(parse_url($src, PHP_URL_HOST) ?? '');
    $allowedHosts = [
        'www.youtube.com',
        'youtube.com',
        'player.vimeo.com',
        'www.dailymotion.com',
        'www.ok.ru',
    ];
    if (!in_array($host, $allowedHosts, true)) {
        return '';
    }
    return '<iframe src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" allowfullscreen loading="lazy" referrerpolicy="no-referrer"></iframe>';
}

function resolveVideoSource(string $videoType, string $videoUrl, string $embedCode, array $fileInput, string $existingPath = ''): array {
    $videoType = in_array($videoType, ['file', 'url', 'youtube', 'embed'], true) ? $videoType : 'url';
    $videoPath = $existingPath;
    $error = '';

    if ($videoType === 'file') {
        if (!empty($fileInput['name'])) {
            $uploaded = uploadVideo($fileInput, VIDEO_UPLOAD_PATH);
            if (!$uploaded) {
                $error = 'File video không hợp lệ hoặc vượt quá dung lượng cho phép.';
            } else {
                $videoPath = $uploaded;
            }
        } elseif ($existingPath === '') {
            $error = 'Bạn chưa chọn file video.';
        }
    }

    if ($videoType === 'url') {
        if (!isSafeHttpUrl($videoUrl)) {
            $error = 'URL video không hợp lệ.';
        } else {
            $videoPath = trim($videoUrl);
        }
    }

    if ($videoType === 'youtube') {
        if (!isSafeHttpUrl($videoUrl) || !isYoutubeUrl($videoUrl)) {
            $error = 'URL YouTube không hợp lệ.';
        } else {
            $videoPath = trim($videoUrl);
        }
    }

    if ($videoType === 'embed') {
        $safe = sanitizeEmbedCode($embedCode);
        if ($safe === '') {
            $error = 'Embed code không hợp lệ hoặc domain chưa được cho phép.';
        } else {
            $videoPath = $safe;
        }
    }

    return [$videoType, $videoPath, $error];
}

function resolveEpisodeVideoSource(string $videoType, string $videoUrl, array $fileInput, string $existingPath = ''): array {
    $videoType = in_array($videoType, ['file', 'url', 'youtube'], true) ? $videoType : 'url';
    $videoPath = $existingPath;
    $error = '';

    if ($videoType === 'file') {
        if (!empty($fileInput['name'])) {
            $uploaded = uploadVideo($fileInput, VIDEO_UPLOAD_PATH);
            if (!$uploaded) {
                $error = 'File video tập không hợp lệ.';
            } else {
                $videoPath = $uploaded;
            }
        } elseif ($existingPath === '') {
            $error = 'Bạn chưa chọn file video cho tập.';
        }
    }

    if ($videoType === 'url') {
        if (!isSafeHttpUrl($videoUrl)) {
            $error = 'URL video tập không hợp lệ.';
        } else {
            $videoPath = trim($videoUrl);
        }
    }

    if ($videoType === 'youtube') {
        if (!isSafeHttpUrl($videoUrl) || !isYoutubeUrl($videoUrl)) {
            $error = 'URL YouTube tập không hợp lệ.';
        } else {
            $videoPath = trim($videoUrl);
        }
    }

    return [$videoType, $videoPath, $error];
}

function renderPagination(int $total, int $perPage, int $current, string $baseUrl): string {
    $pages = (int)ceil($total / $perPage);
    if ($pages <= 1) return '';
    $sep = str_contains($baseUrl, '?') ? '&' : '?';
    $html = '<nav><ul class="pagination justify-content-center flex-wrap">';
    if ($current > 1) $html .= '<li class="page-item"><a class="page-link" href="'.$baseUrl.$sep.'page='.($current-1).'">&laquo;</a></li>';
    $start = max(1, $current-2); $end = min($pages, $current+2);
    if ($start > 1) $html .= '<li class="page-item"><a class="page-link" href="'.$baseUrl.$sep.'page=1">1</a></li>'.($start>2?'<li class="page-item disabled"><span class="page-link">…</span></li>':'');
    for ($i=$start; $i<=$end; $i++) {
        $a = $i===$current ? ' active' : '';
        $html .= '<li class="page-item'.$a.'"><a class="page-link" href="'.$baseUrl.$sep.'page='.$i.'">'.$i.'</a></li>';
    }
    if ($end < $pages) $html .= ($end<$pages-1?'<li class="page-item disabled"><span class="page-link">…</span></li>':'').'<li class="page-item"><a class="page-link" href="'.$baseUrl.$sep.'page='.$pages.'">'.$pages.'</a></li>';
    if ($current < $pages) $html .= '<li class="page-item"><a class="page-link" href="'.$baseUrl.$sep.'page='.($current+1).'">&raquo;</a></li>';
    return $html.'</ul></nav>';
}

// ── File upload ──────────────────────────────────────────────
function uploadImage(array $file, string $destDir): string|false {
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed, true) || $file['size'] > MAX_IMAGE_SIZE) return false;
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $name = bin2hex(random_bytes(16)).'.'.$ext;
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    return move_uploaded_file($file['tmp_name'], $destDir.$name) ? $name : false;
}

function uploadVideo(array $file, string $destDir): string|false {
    $allowed = ['video/mp4','video/webm','video/ogg','video/mpeg','video/quicktime'];
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) return false;
    if ($file['size'] > MAX_VIDEO_SIZE) return false;
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $name = bin2hex(random_bytes(16)).'.'.$ext;
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    return move_uploaded_file($file['tmp_name'], $destDir.$name) ? $name : false;
}

function safeDeleteFile(string $path): void {
    if (file_exists($path) && is_file($path)) unlink($path);
}

// ── Admin helpers ─────────────────────────────────────────────
function countAllMovies(): int {
    return (int)getDB()->query('SELECT COUNT(*) FROM movies')->fetchColumn();
}
function countAllUsers(): int {
    return (int)getDB()->query('SELECT COUNT(*) FROM users WHERE role="user"')->fetchColumn();
}
function countAllComments(): int {
    return (int)getDB()->query('SELECT COUNT(*) FROM comments')->fetchColumn();
}
function getTotalViews(): int {
    return (int)(getDB()->query('SELECT SUM(views) FROM movies')->fetchColumn() ?: 0);
}
function getAdminMovies(int $limit = 20, int $offset = 0, string $search = ''): array {
    if ($search) {
        $like = '%'.$search.'%';
        $stmt = getDB()->prepare('SELECT m.*,g.name genre_name FROM movies m LEFT JOIN genres g ON m.genre_id=g.id WHERE m.title LIKE ? OR m.original_title LIKE ? ORDER BY m.created_at DESC LIMIT ? OFFSET ?');
        $stmt->execute([$like,$like,$limit,$offset]);
    } else {
        $stmt = getDB()->prepare('SELECT m.*,g.name genre_name FROM movies m LEFT JOIN genres g ON m.genre_id=g.id ORDER BY m.created_at DESC LIMIT ? OFFSET ?');
        $stmt->execute([$limit,$offset]);
    }
    return $stmt->fetchAll();
}
function countAdminMovies(string $search = ''): int {
    if ($search) {
        $like = '%'.$search.'%';
        $stmt = getDB()->prepare('SELECT COUNT(*) FROM movies WHERE title LIKE ? OR original_title LIKE ?');
        $stmt->execute([$like,$like]);
    } else {
        $stmt = getDB()->query('SELECT COUNT(*) FROM movies');
    }
    return (int)$stmt->fetchColumn();
}
function getAllUsers(int $limit = 20, int $offset = 0, string $search = ''): array {
    if ($search) {
        $like = '%'.$search.'%';
        $stmt = getDB()->prepare('SELECT * FROM users WHERE username LIKE ? OR email LIKE ? OR full_name LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?');
        $stmt->execute([$like,$like,$like,$limit,$offset]);
    } else {
        $stmt = getDB()->prepare('SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?');
        $stmt->execute([$limit,$offset]);
    }
    return $stmt->fetchAll();
}
function countAllUsersTotal(string $search = ''): int {
    if ($search) {
        $like = '%'.$search.'%';
        $stmt = getDB()->prepare('SELECT COUNT(*) FROM users WHERE username LIKE ? OR email LIKE ? OR full_name LIKE ?');
        $stmt->execute([$like,$like,$like]);
    } else {
        $stmt = getDB()->query('SELECT COUNT(*) FROM users');
    }
    return (int)$stmt->fetchColumn();
}
function getAllComments(int $limit = 20, int $offset = 0): array {
    $stmt = getDB()->prepare('SELECT c.*,u.username,m.title movie_title,m.id movie_id FROM comments c JOIN users u ON c.user_id=u.id JOIN movies m ON c.movie_id=m.id ORDER BY c.created_at DESC LIMIT ? OFFSET ?');
    $stmt->execute([$limit,$offset]);
    return $stmt->fetchAll();
}
