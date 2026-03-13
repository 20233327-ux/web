<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireMovieManager();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { header('Location: movies.php'); exit; }

$movie = getMovieByIdAdmin($id);
if (!$movie) { header('Location: movies.php'); exit; }

// Delete associated files
if ($movie['video_type'] === 'file' && $movie['video_path']) {
    safeDeleteFile(VIDEO_UPLOAD_PATH . $movie['video_path']);
}
foreach (getEpisodesByMovie($id, false) as $ep) {
    if (($ep['video_type'] ?? '') === 'file' && !empty($ep['video_path'])) {
        safeDeleteFile(VIDEO_UPLOAD_PATH . $ep['video_path']);
    }
}
if ($movie['thumbnail'] && !str_starts_with($movie['thumbnail'], 'http')) {
    safeDeleteFile(THUMB_UPLOAD_PATH . $movie['thumbnail']);
}

// Delete from DB (cascades to ratings, comments, watch_history)
getDB()->prepare('DELETE FROM movies WHERE id=?')->execute([$id]);

header('Location: movies.php?deleted=1');
exit;
