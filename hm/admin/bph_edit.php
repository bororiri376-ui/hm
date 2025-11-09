<?php
  $pageTitle = 'Edit Anggota BPH';
  require __DIR__ . '/../includes/auth.php';
  require_role(['admin']);
  require_once __DIR__ . '/../includes/db.php';

  $name = isset($_GET['name']) ? (string)$_GET['name'] : '';
  $detail = null;
  if ($name !== '') {
    $stmt = db()->prepare("SELECT name, position, contact, photo FROM bph WHERE name=? LIMIT 1");
    $stmt->execute([$name]);
    $detail = $stmt->fetch();
  }

  require __DIR__ . '/../includes/header_admin.php';
?>
<div class="row">
  <div class="col-lg-10 mx-auto">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h5 mb-0">Edit Anggota BPH</h1>
      <a class="btn btn-outline-primary btn-sm" href="/hm/admin/?tab=bph">Kembali</a>
    </div>

    <?php if (!$detail): ?>
      <div class="alert alert-warning">Data anggota tidak ditemukan.</div>
    <?php else: ?>
      <div class="card">
        <div class="card-body">
          <form method="post" action="/hm/admin/index.php" enctype="multipart/form-data" class="row g-2">
            <input type="hidden" name="action" value="update_bph">
            <input type="hidden" name="original_name" value="<?= htmlspecialchars($detail['name']) ?>">
            <div class="col-md-6"><input class="form-control" name="name" value="<?= htmlspecialchars($detail['name']) ?>" placeholder="Nama" required></div>
            <div class="col-md-6">
              <select class="form-select" name="position" required>
                <?php $pos = (string)($detail['position'] ?? ''); ?>
                <option value="">Pilih Jabatan...</option>
                <option <?= $pos==='Ketua'?'selected':'' ?>>Ketua</option>
                <option <?= $pos==='Wakil'?'selected':'' ?>>Wakil</option>
                <option <?= $pos==='Sekretaris 1'?'selected':'' ?>>Sekretaris 1</option>
                <option <?= $pos==='Sekretaris 2'?'selected':'' ?>>Sekretaris 2</option>
                <option <?= $pos==='Bendahara 1'?'selected':'' ?>>Bendahara 1</option>
                <option <?= $pos==='Bendahara 2'?'selected':'' ?>>Bendahara 2</option>
                <option <?= $pos==='Departemen Sains Dan Teknologi'?'selected':'' ?>>Departemen Sains Dan Teknologi</option>
                <option <?= $pos==='Departemen Humas'?'selected':'' ?>>Departemen Humas</option>
                <option <?= $pos==='Departemen Olahraga'?'selected':'' ?>>Departemen Olahraga</option>
                <option <?= $pos==='Departemen Kerohanian'?'selected':'' ?>>Departemen Kerohanian</option>
                <option <?= $pos==='Departemen Kominfo'?'selected':'' ?>>Departemen Kominfo</option>
                <option <?= $pos==='Departemen Multimedia'?'selected':'' ?>>Departemen Multimedia</option>
              </select>
            </div>
            <div class="col-md-6"><input class="form-control" name="contact" value="<?= htmlspecialchars($detail['contact'] ?? '') ?>" placeholder="Kontak/Email"></div>
            <div class="col-md-6">
              <label class="form-label small text-muted">Foto (opsional untuk ganti)</label>
              <input class="form-control" type="file" name="bph_photo" accept="image/*">
            </div>
            <?php if (!empty($detail['photo'])): ?>
              <div class="col-12">
                <div class="small text-muted mb-1">Foto saat ini:</div>
                <img src="<?= htmlspecialchars($detail['photo']) ?>" alt="" class="img-fluid rounded" style="max-height:160px; object-fit:cover;">
              </div>
            <?php endif; ?>
            <div class="col-12 d-flex gap-2 mt-2">
              <button class="btn btn-primary">Simpan Perubahan</button>
              <a class="btn btn-outline-primary" href="/hm/admin/?tab=bph">Batal</a>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
