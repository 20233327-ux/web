<?php
/**
 * stream.php - Phát video với hỗ trợ HTTP Range (cho phép tua, seek)
 * Dùng cho video được tải lên (video_type = 'file')
 */
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$episodeId = filter_input(INPUT_GET, 'episode', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('Bad request'); }

$movie = getMovieById($id);
$videoPath = '';
if ($episodeId) {
    $ep = getEpisodeById($episodeId);
    if (!$ep || (int)$ep['movie_id'] !== $id || ($ep['video_type'] ?? '') !== 'file') {
        http_response_code(404); exit('Episode not found');
    }
    $videoPath = $ep['video_path'];
} else {
    if (!$movie || $movie['video_type'] !== 'file' || empty($movie['video_path'])) {
        http_response_code(404); exit('Video not found');
    }
    $videoPath = $movie['video_path'];
}

$file = VIDEO_UPLOAD_PATH . basename($videoPath);
if (!is_file($file)) {
    http_response_code(404); exit('File not found');
}

// Determine MIME type
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file);
if (!in_array($mimeType, ['video/mp4','video/webm','video/ogg','video/mpeg'], true)) {
    http_response_code(415); exit('Unsupported media type');
}

$fileSize = filesize($file);
$start    = 0;
$end      = $fileSize - 1;
$length   = $fileSize;

if (isset($_SERVER['HTTP_RANGE'])) {
    $range = $_SERVER['HTTP_RANGE'];
    if (!preg_match('/bytes=(\d*)-(\d*)/i', $range, $matches)) {
        http_response_code(416);
        header('Content-Range: bytes */' . $fileSize);
        exit;
    }
    $start = $matches[1] !== '' ? (int)$matches[1] : 0;
    $end   = $matches[2] !== '' ? (int)$matches[2] : $fileSize - 1;

    if ($start > $end || $start >= $fileSize || $end >= $fileSize) {
        http_response_code(416);
        header('Content-Range: bytes */' . $fileSize);
        exit;
    }
    $length = $end - $start + 1;
    http_response_code(206);
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
} else {
    http_response_code(200);
}

// Security headers
header('Content-Security-Policy: default-src \'none\'');
header('X-Content-Type-Options: nosniff');
header('Accept-Ranges: bytes');
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $length);
header('Cache-Control: public, max-age=3600');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($file)) . ' GMT');

// Etag for caching
$etag = '"' . md5($file . $fileSize) . '"';
header('ETag: ' . $etag);
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag && $start === 0) {
    http_response_code(304); exit;
}

// Stream file in chunks
$fp      = fopen($file, 'rb');
fseek($fp, $start);
$bufSize = 1024 * 256; // 256 KB chunks
$sent    = 0;

while (!feof($fp) && $sent < $length) {
    $read   = min($bufSize, $length - $sent);
    $chunk  = fread($fp, $read);
    echo $chunk;
    $sent  += strlen($chunk);
    ob_flush();
    flush();
    if (connection_aborted()) break;
}
fclose($fp);
