<?php
  $pageTitle = 'Edit Galeri';
  require __DIR__ . '/../includes/auth.php';
  require_role(['admin']);
  require_once __DIR__ . '/../includes/db.php';

  $image = isset($_GET['image']) ? (string)$_GET['image'] : '';
  $detail = null;
  if ($image !== '') {
    $stmt = db()->prepare("SELECT image, caption FROM galeri WHERE image=? LIMIT 1");
    $stmt->execute([$image]);
    $detail = $stmt->fetch();
  }

  require __DIR__ . '/../includes/header_admin.php';
?>
<div class="row">
  <div class="col-lg-10 mx-auto">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h5 mb-0">Edit Item Galeri</h1>
      <a class="btn btn-outline-primary btn-sm" href="/hm/admin/?tab=galeri">Kembali</a>
    </div>

    <?php if (!$detail): ?>
      <div class="alert alert-warning">Item galeri tidak ditemukan.</div>
    <?php else: ?>
      <div class="card">
        <div class="card-body">
          <form method="post" action="/hm/admin/index.php" enctype="multipart/form-data" class="row g-2">
            <input type="hidden" name="action" value="update_galeri">
            <input type="hidden" name="original_image" value="<?= htmlspecialchars($detail['image']) ?>">
            <div class="col-md-8"><input class="form-control" name="caption" value="<?= htmlspecialchars($detail['caption'] ?? '') ?>" placeholder="Caption"></div>
            <div class="col-md-4">
              <label class="form-label small text-muted">Ganti gambar (opsional)</label>
              <input class="form-control" type="file" name="galeri_image" accept="image/*">
            </div>
            <div class="col-12">
              <div class="small text-muted mb-1">Gambar saat ini:</div>
              <img src="<?= htmlspecialchars($detail['image']) ?>" alt="" class="img-fluid rounded" style="max-height:220px; object-fit:cover;">
            </div>
            <div class="col-12 d-flex gap-2 mt-2">
              <button class="btn btn-primary">Simpan Perubahan</button>
              <a class="btn btn-outline-primary" href="/hm/admin/?tab=galeri">Batal</a>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
