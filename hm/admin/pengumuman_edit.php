<?php
  $pageTitle = 'Edit Kegiatan';
  require __DIR__ . '/../includes/auth.php';
  require_role(['admin']);
  require_once __DIR__ . '/../includes/db.php';

  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $detail = null;
  if ($id) {
    $stmt = db()->prepare("SELECT id, title, DATE_FORMAT(date, '%Y-%m-%d') AS date, excerpt, content, link FROM pengumuman WHERE id=?");
    $stmt->execute([$id]);
    $detail = $stmt->fetch();
  }

  require __DIR__ . '/../includes/header_admin.php';
?>
<div class="row">
  <div class="col-lg-10 mx-auto">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h5 mb-0">Edit Kegiatan</h1>
      <a class="btn btn-outline-primary btn-sm" href="/hm/admin/?tab=pengumuman">Kembali</a>
    </div>

    <?php if (!$detail): ?>
      <div class="alert alert-warning">Data kegiatan tidak ditemukan.</div>
    <?php else: ?>
      <div class="card">
        <div class="card-body">
          <form method="post" action="/hm/admin/index.php" class="row g-2">
            <input type="hidden" name="action" value="update_pengumuman">
            <input type="hidden" name="id" value="<?= (int)$detail['id'] ?>">
            <div class="col-md-6"><input class="form-control" name="title" value="<?= htmlspecialchars($detail['title']) ?>" placeholder="Judul" required></div>
            <div class="col-md-3"><input class="form-control" type="date" name="date" value="<?= htmlspecialchars($detail['date']) ?>" required></div>
            <div class="col-md-3"><input class="form-control" name="link" value="<?= htmlspecialchars($detail['link'] ?? '') ?>" placeholder="Tautan (opsional)"></div>
            <div class="col-12"><input class="form-control" name="excerpt" value="<?= htmlspecialchars($detail['excerpt'] ?? '') ?>" placeholder="Ringkasan"></div>
            <div class="col-12"><textarea class="form-control" name="content" rows="4" placeholder="Konten"><?= htmlspecialchars($detail['content'] ?? '') ?></textarea></div>
            <div class="col-12 d-flex gap-2 mt-2">
              <button class="btn btn-primary">Simpan Perubahan</button>
              <a class="btn btn-outline-primary" href="/hm/admin/?tab=pengumuman">Batal</a>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
