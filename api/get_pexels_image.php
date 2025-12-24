<?php
header('Content-Type: application/json');
require_once __DIR__.'/../includes/pexels.php';

$query = isset($_GET['query']) ? $_GET['query'] : 'leather craftsmanship';
$type = isset($_GET['type']) ? $_GET['type'] : 'photo'; // 'photo' or 'video'
$orientation = isset($_GET['orientation']) ? $_GET['orientation'] : 'landscape';

if ($type === 'video') {
    $url = 'https://api.pexels.com/videos/search?query=' . urlencode($query) . '&orientation=' . urlencode($orientation) . '&per_page=1&page=1';
    $data = pexels_get($url);
    if (!$data || empty($data['videos'])) {
        echo json_encode(['error'=>'Failed to fetch video from Pexels.']);
        exit;
    }
    $video = $data['videos'][0];
    $src = '';
    // Find the best quality mp4 link
    foreach($video['video_files'] as $file) {
        if ($file['file_type'] === 'video/mp4' && (strpos($file['link'], 'external') !== false)) {
            $src = $file['link'];
            break;
        }
    }
    if (empty($src)) {
        echo json_encode(['error'=>'No suitable video file found.']);
        exit;
    }

    $target_dir = __DIR__ . '/../assets/videos/';
    $target_filename = $video['id'] . '.mp4';
    $target_path = $target_dir . $target_filename;

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0775, true);
    }

    if (download_to($src, $target_path)) {
        echo json_encode([
            'id' => $video['id'],
            'local_path' => 'assets/videos/' . $target_filename,
            'original_url' => $src
        ]);
    } else {
        echo json_encode(['error'=>'Failed to download and save video.']);
    }

} else { // It's a photo
    $url = 'https://api.pexels.com/v1/search?query=' . urlencode($query) . '&orientation=' . urlencode($orientation) . '&per_page=1&page=1';
    $data = pexels_get($url);

    if (!$data || empty($data['photos'])) {
        echo json_encode(['error'=>'Failed to fetch image from Pexels.']);
        exit;
    }

    $photo = $data['photos'][0];
    $src = $photo['src']['large2x'] ?? ($photo['src']['large'] ?? $photo['src']['original']);
    $target_dir = __DIR__ . '/../assets/images/pexels/';
    $target_filename = 'about-us-' . $photo['id'] . '.jpg';
    $target_path = $target_dir . $target_filename;

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0775, true);
    }

    if (download_to($src, $target_path)) {
        echo json_encode([
            'id' => $photo['id'],
            'local_path' => 'assets/images/pexels/' . $target_filename,
            'photographer' => $photo['photographer'] ?? null,
            'photographer_url' => $photo['photographer_url'] ?? null,
            'original_url' => $src
        ]);
    } else {
        echo json_encode(['error'=>'Failed to download and save image.']);
    }
}
