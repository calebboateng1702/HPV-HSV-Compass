<?php
/**
 * Handles image/video uploads for the content CMS.
 * Validates real file type (not just the extension), enforces size limits,
 * stores under /uploads/content/ with a random filename, and records the
 * upload in content_media.
 */

const MEDIA_ALLOWED_IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
const MEDIA_ALLOWED_VIDEO_TYPES = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov'];
const MEDIA_MAX_IMAGE_BYTES = 5 * 1024 * 1024;   // 5MB
const MEDIA_MAX_VIDEO_BYTES = 75 * 1024 * 1024;  // 75MB

/**
 * @return array{ok: bool, error?: string, id?: int}
 */
function handle_media_upload(int $contentId, array $file, ?string $caption): array {
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => 'Please choose a file to upload.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Upload failed (error code ' . $file['error'] . '). If this is a video, your server may need a higher upload_max_filesize/post_max_size in php.ini.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (isset(MEDIA_ALLOWED_IMAGE_TYPES[$mime])) {
        $type = 'image';
        $ext = MEDIA_ALLOWED_IMAGE_TYPES[$mime];
        $maxBytes = MEDIA_MAX_IMAGE_BYTES;
    } elseif (isset(MEDIA_ALLOWED_VIDEO_TYPES[$mime])) {
        $type = 'video';
        $ext = MEDIA_ALLOWED_VIDEO_TYPES[$mime];
        $maxBytes = MEDIA_MAX_VIDEO_BYTES;
    } else {
        return ['ok' => false, 'error' => "Unsupported file type ({$mime}). Allowed: JPG, PNG, GIF, WEBP images or MP4, WEBM, MOV videos."];
    }

    if ($file['size'] > $maxBytes) {
        $limitMb = round($maxBytes / 1024 / 1024);
        return ['ok' => false, 'error' => "File is too large. Limit for {$type}s is {$limitMb}MB."];
    }

    $uploadDir = __DIR__ . '/../uploads/content/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $filename = bin2hex(random_bytes(12)) . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['ok' => false, 'error' => 'Could not save the uploaded file. Check that /uploads/content/ is writable.'];
    }

    $relativePath = 'uploads/content/' . $filename;
    $stmt = get_db()->prepare('INSERT INTO content_media (content_id, media_type, file_path, caption) VALUES (?, ?, ?, ?)');
    $stmt->execute([$contentId, $type, $relativePath, $caption ?: null]);

    return ['ok' => true, 'id' => (int) get_db()->lastInsertId(), 'type' => $type, 'path' => $relativePath];
}

/** Builds the <img>/<video> HTML snippet used when a media item is inserted into a lesson body. */
function media_embed_html(array $media): string {
    $src = BASE_URL . '/' . e($media['file_path']);
    $caption = $media['caption'] ? '<figcaption>' . e($media['caption']) . '</figcaption>' : '';
    if ($media['media_type'] === 'video') {
        return "<figure class=\"lesson-media\"><video controls preload=\"metadata\"><source src=\"{$src}\"></video>{$caption}</figure>";
    }
    $alt = $media['caption'] ? e($media['caption']) : 'Illustration';
    return "<figure class=\"lesson-media\"><img src=\"{$src}\" alt=\"{$alt}\" loading=\"lazy\">{$caption}</figure>";
}

function delete_media(int $mediaId): bool {
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM content_media WHERE id = ?');
    $stmt->execute([$mediaId]);
    $media = $stmt->fetch();
    if (!$media) return false;

    $fullPath = __DIR__ . '/../' . $media['file_path'];
    if (is_file($fullPath)) @unlink($fullPath);

    $del = $pdo->prepare('DELETE FROM content_media WHERE id = ?');
    return $del->execute([$mediaId]);
}
