<?php
  $pageTitle = 'Edit Berita';
  require __DIR__ . '/../includes/auth.php';
  require_role(['admin']);
  require_once __DIR__ . '/../includes/db.php';

  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $detail = null;
  if ($id) {
    $stmt = db()->prepare("SELECT id, title, DATE_FORMAT(date, '%Y-%m-%d') AS date, author, excerpt, content, image FROM berita WHERE id=?");
    $stmt->execute([$id]);
    $detail = $stmt->fetch();
  }

  require __DIR__ . '/../includes/header_admin.php';
?>
<div class="row">
  <div class="col-lg-10 mx-auto">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h5 mb-0">Edit Berita</h1>
      <a class="btn btn-outline-primary btn-sm" href="/hm/admin/?tab=berita">Kembali</a>
    </div>

    <?php if (!$detail): ?>
      <div class="alert alert-warning">Data berita tidak ditemukan.</div>
    <?php else: ?>
      <div class="card">
        <div class="card-body">
          <form method="post" action="/hm/admin/index.php" enctype="multipart/form-data" class="row g-2">
            <input type="hidden" name="action" value="update_berita">
            <input type="hidden" name="id" value="<?= (int)$detail['id'] ?>">
            <div class="col-md-6"><input class="form-control" name="title" value="<?= htmlspecialchars($detail['title']) ?>" placeholder="Judul" required></div>
            <div class="col-md-3"><input class="form-control" type="date" name="date" value="<?= htmlspecialchars($detail['date']) ?>" required></div>
            <div class="col-md-3"><input class="form-control" name="author" value="<?= htmlspecialchars($detail['author'] ?? '') ?>" placeholder="Author" required></div>
            <div class="col-12"><input class="form-control" name="excerpt" value="<?= htmlspecialchars($detail['excerpt'] ?? '') ?>" placeholder="Ringkasan"></div>
            <div class="col-12"><textarea class="form-control" name="content" rows="4" placeholder="Konten"><?= htmlspecialchars($detail['content'] ?? '') ?></textarea></div>
            <div class="col-md-6">
              <label class="form-label small text-muted">Gambar (opsional untuk ganti)</label>
              <input class="form-control" type="file" name="image_file" accept="image/*">
            </div>
            <?php if (!empty($detail['image'])): ?>
              <div class="col-md-6">
                <div class="small text-muted mb-1">Gambar saat ini:</div>
                <img src="<?= htmlspecialchars($detail['image']) ?>" alt="" class="img-fluid rounded" style="max-height:160px; object-fit:cover;">
              </div>
            <?php endif; ?>
            <div class="col-12 d-flex gap-2 mt-2">
              <button class="btn btn-primary">Simpan Perubahan</button>
              <a class="btn btn-outline-primary" href="/hm/admin/?tab=berita">Batal</a>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
