<?php
  $pageTitle = 'Edit Pasangan Calon';
  require __DIR__ . '/../includes/auth.php';
  require_role(['admin']);
  require_once __DIR__ . '/../includes/db.php';

  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  $pair = null; $ketua = null; $wakil = null;
  if ($id) {
    $stmt = db()->prepare("SELECT id, ketua_id, wakil_id, ketua_name, wakil_name FROM election_pairs WHERE id=?");
    $stmt->execute([$id]);
    $pair = $stmt->fetch();
    if ($pair) {
      $kid = (int)($pair['ketua_id'] ?? 0);
      $wid = (int)($pair['wakil_id'] ?? 0);
      $s1 = db()->prepare("SELECT id, name, nim, photo FROM election_ketua WHERE id=?");
      $s1->execute([$kid]);
      $ketua = $s1->fetch();
      $s2 = db()->prepare("SELECT id, name, nim, photo FROM election_wakil WHERE id=?");
      $s2->execute([$wid]);
      $wakil = $s2->fetch();
    }
  }

  require __DIR__ . '/../includes/header_admin.php';
?>
<div class="row">
  <div class="col-lg-10 mx-auto">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h5 mb-0">Edit Pasangan Calon</h1>
      <a class="btn btn-outline-primary btn-sm" href="/hm/admin/?tab=election">Kembali</a>
    </div>

    <?php if (!$pair): ?>
      <div class="alert alert-warning">Data pasangan tidak ditemukan.</div>
    <?php else: ?>
      <div class="card">
        <div class="card-body">
          <form method="post" action="/hm/admin/index.php" enctype="multipart/form-data" class="row g-3">
            <input type="hidden" name="action" value="update_pair">
            <input type="hidden" name="id" value="<?= (int)$pair['id'] ?>">

            <div class="col-12 col-md-6">
              <div class="card h-100"><div class="card-body">
                <div class="fw-semibold small mb-2">Calon Ketua</div>
                <div class="row g-2 align-items-center mb-2">
                  <div class="col-auto">
                    <?php $kp = $ketua['photo'] ?? ''; ?>
                    <?php if (!empty($kp)): ?>
                      <img src="<?= htmlspecialchars($kp) ?>" alt="Foto Ketua" style="width:96px;height:96px;object-fit:cover;border-radius:.25rem;background:#f8f9fa;">
                    <?php else: ?>
                      <div class="bg-light rounded" style="width:96px;height:96px;"></div>
                    <?php endif; ?>
                  </div>
                  <div class="col">
                    <div class="small text-muted">Preview</div>
                  </div>
                </div>
                <input class="form-control mb-2" name="ketua_name" placeholder="Nama Ketua" value="<?= htmlspecialchars($ketua['name'] ?? $pair['ketua_name'] ?? '') ?>" required>
                <input class="form-control mb-2" name="ketua_nim" placeholder="NIM Ketua" value="<?= htmlspecialchars($ketua['nim'] ?? '') ?>" required>
                <label class="form-label small text-muted">Ganti Foto (opsional)</label>
                <input class="form-control" type="file" name="ketua_photo" accept="image/*">
              </div></div>
            </div>

            <div class="col-12 col-md-6">
              <div class="card h-100"><div class="card-body">
                <div class="fw-semibold small mb-2">Calon Wakil</div>
                <div class="row g-2 align-items-center mb-2">
                  <div class="col-auto">
                    <?php $wp = $wakil['photo'] ?? ''; ?>
                    <?php if (!empty($wp)): ?>
                      <img src="<?= htmlspecialchars($wp) ?>" alt="Foto Wakil" style="width:96px;height:96px;object-fit:cover;border-radius:.25rem;background:#f8f9fa;">
                    <?php else: ?>
                      <div class="bg-light rounded" style="width:96px;height:96px;"></div>
                    <?php endif; ?>
                  </div>
                  <div class="col">
                    <div class="small text-muted">Preview</div>
                  </div>
                </div>
                <input class="form-control mb-2" name="wakil_name" placeholder="Nama Wakil" value="<?= htmlspecialchars($wakil['name'] ?? $pair['wakil_name'] ?? '') ?>" required>
                <input class="form-control mb-2" name="wakil_nim" placeholder="NIM Wakil" value="<?= htmlspecialchars($wakil['nim'] ?? '') ?>" required>
                <label class="form-label small text-muted">Ganti Foto (opsional)</label>
                <input class="form-control" type="file" name="wakil_photo" accept="image/*">
              </div></div>
            </div>

            <div class="col-12 d-flex gap-2 mt-2">
              <button class="btn btn-primary">Simpan Perubahan</button>
              <a class="btn btn-outline-primary" href="/hm/admin/?tab=election">Batal</a>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
