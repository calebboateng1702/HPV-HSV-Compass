<?php
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';
require __DIR__ . '/../includes/media.php';
require_admin();
$pdo = get_db();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM content WHERE id = ?');
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) { http_response_code(404); die('Content not found.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $formAction = $_POST['form_action'] ?? 'save_text';

    if ($formAction === 'save_text') {
        $title = trim($_POST['title'] ?? '');
        $body = $_POST['body'] ?? '';
        $upd = $pdo->prepare('UPDATE content SET title = ?, body = ? WHERE id = ?');
        $upd->execute([$title, $body, $id]);
        $_SESSION['flash_success'] = 'Content updated.';
    } elseif ($formAction === 'upload_media') {
        $caption = trim($_POST['caption'] ?? '');
        $result = handle_media_upload($id, $_FILES['media_file'] ?? [], $caption);
        if ($result['ok']) {
            $_SESSION['flash_success'] = ucfirst($result['type']) . ' uploaded. Click "Insert" below to add it to the lesson.';
        } else {
            $_SESSION['flash_error'] = $result['error'];
        }
    } elseif ($formAction === 'insert_media') {
        $mediaId = (int) ($_POST['media_id'] ?? 0);
        $mStmt = $pdo->prepare('SELECT * FROM content_media WHERE id = ? AND content_id = ?');
        $mStmt->execute([$mediaId, $id]);
        $media = $mStmt->fetch();
        if ($media) {
            $newBody = rtrim($item['body']) . "\n" . media_embed_html($media);
            $pdo->prepare('UPDATE content SET body = ? WHERE id = ?')->execute([$newBody, $id]);
            $_SESSION['flash_success'] = 'Media inserted into the lesson content.';
        }
    } elseif ($formAction === 'delete_media') {
        delete_media((int) ($_POST['media_id'] ?? 0));
        $_SESSION['flash_success'] = 'Media deleted.';
    }

    header('Location: ' . BASE_URL . '/admin/content_edit.php?id=' . $id);
    exit;
}

// Re-fetch in case it just changed
$stmt->execute([$id]);
$item = $stmt->fetch();

$mediaStmt = $pdo->prepare('SELECT * FROM content_media WHERE content_id = ? ORDER BY created_at DESC');
$mediaStmt->execute([$id]);
$mediaItems = $mediaStmt->fetchAll();

$pageTitle = 'Edit Content';
require __DIR__ . '/_admin_header.php';
?>

<a href="<?= BASE_URL ?>/admin/content.php" style="color:var(--teal); font-weight:700; font-size:0.85rem;"><i class="fas fa-arrow-left"></i> Back to content list</a>

<div class="mini-card mt-2">
  <div class="section-heading"><i class="fas fa-pen"></i><h2 style="font-size:1.2rem;"><?= strtoupper(e($item['topic'])) ?> — <?= e($item['section']) ?></h2></div>
  <form method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="form_action" value="save_text">
    <div class="input-group">
      <label>Section title</label>
      <input type="text" name="title" class="form-plain" style="width:100%; padding:0.85rem 1rem; border:1px solid rgba(44,125,160,0.22); border-radius:1rem;" value="<?= e($item['title']) ?>">
    </div>
    <div class="input-group">
      <label>Body (HTML allowed — lists, paragraphs, images, videos)</label>
      <textarea name="body" rows="14" style="width:100%; padding:1rem; border:1px solid rgba(44,125,160,0.22); border-radius:1rem; font-family: 'Courier New', monospace; font-size:0.85rem;"><?= e($item['body']) ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Save changes</button>
  </form>
</div>

<div class="mini-card mt-2">
  <div class="section-heading" style="margin-bottom:1rem;"><i class="fas fa-photo-film"></i><h2 style="font-size:1.1rem;">Add an image or video</h2></div>
  <p class="section-subtext">Upload here, then click "Insert" on the item below to add it to the lesson content (it's appended to the end — you can then reorder or move it within the Body text above using the <code>&lt;figure class="lesson-media"&gt;</code> block).</p>
  <form method="POST" enctype="multipart/form-data" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
    <?= csrf_field() ?>
    <input type="hidden" name="form_action" value="upload_media">
    <div class="input-group" style="margin-bottom:0; flex:1; min-width:200px;">
      <label>File (image or video)</label>
      <input type="file" name="media_file" accept="image/jpeg,image/png,image/gif,image/webp,video/mp4,video/webm,video/quicktime" required style="width:100%; padding:0.6rem; border:1px solid rgba(44,125,160,0.22); border-radius:0.8rem;">
    </div>
    <div class="input-group" style="margin-bottom:0; flex:1; min-width:200px;">
      <label>Caption (optional)</label>
      <input type="text" name="caption" placeholder="e.g. Figure 1: HPV vaccine schedule" class="form-plain" style="width:100%; padding:0.85rem 1rem; border:1px solid rgba(44,125,160,0.22); border-radius:1rem;">
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
  </form>
  <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.8rem;">Images up to 5MB (JPG/PNG/GIF/WEBP). Videos up to 75MB (MP4/WEBM/MOV). If large video uploads fail, your host's <code>upload_max_filesize</code> and <code>post_max_size</code> in php.ini may need increasing.</p>
</div>

<?php if (!empty($mediaItems)): ?>
<div class="mini-card mt-2">
  <div class="section-heading" style="margin-bottom:1rem;"><i class="fas fa-images"></i><h2 style="font-size:1.1rem;">Uploaded media for this section</h2></div>
  <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:1rem;">
    <?php foreach ($mediaItems as $m): ?>
      <div style="border:1px solid rgba(44,125,160,0.18); border-radius:1rem; overflow:hidden; background:white;">
        <?php if ($m['media_type'] === 'image'): ?>
          <img src="<?= BASE_URL ?>/<?= e($m['file_path']) ?>" alt="" style="width:100%; height:120px; object-fit:cover;">
        <?php else: ?>
          <video src="<?= BASE_URL ?>/<?= e($m['file_path']) ?>" style="width:100%; height:120px; object-fit:cover;" muted></video>
        <?php endif; ?>
        <div style="padding:0.7rem;">
          <div style="font-size:0.78rem; color:var(--text-muted); margin-bottom:0.6rem;"><i class="fas <?= $m['media_type'] === 'video' ? 'fa-video' : 'fa-image' ?>"></i> <?= e($m['caption'] ?: 'No caption') ?></div>
          <div style="display:flex; gap:6px;">
            <form method="POST" style="flex:1;">
              <?= csrf_field() ?>
              <input type="hidden" name="form_action" value="insert_media">
              <input type="hidden" name="media_id" value="<?= $m['id'] ?>">
              <button type="submit" class="btn btn-outline" style="width:100%; padding:0.4rem; font-size:0.75rem;"><i class="fas fa-plus"></i> Insert</button>
            </form>
            <form method="POST" onsubmit="return confirm('Delete this media file?');">
              <?= csrf_field() ?>
              <input type="hidden" name="form_action" value="delete_media">
              <input type="hidden" name="media_id" value="<?= $m['id'] ?>">
              <button type="submit" class="btn btn-danger" style="padding:0.4rem 0.6rem; font-size:0.75rem;"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="mini-card mt-2">
  <div class="section-heading"><i class="fas fa-eye"></i><h2 style="font-size:1rem;">Live preview</h2></div>
  <div style="border:1px dashed rgba(44,125,160,0.3); border-radius:1rem; padding:1.2rem;"><?= $item['body'] ?></div>
</div>

<?php require __DIR__ . '/_admin_footer.php'; ?>
