<?php
  $pageTitle = 'Edit Anggota HIMASI';
  require __DIR__ . '/../includes/auth.php';
  require_role(['admin']);
  require_once __DIR__ . '/../includes/db.php';

  $name = isset($_GET['name']) ? (string)$_GET['name'] : '';
  $detail = null;
  $bagianOpts = himasi_bagian_all();
  if ($name !== '') {
    $stmt = db()->prepare("SELECT name, bagian FROM himasi WHERE name=? LIMIT 1");
    $stmt->execute([$name]);
    $detail = $stmt->fetch();
  }

  require __DIR__ . '/../includes/header_admin.php';
?>
<div class="row">
  <div class="col-lg-10 mx-auto">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h5 mb-0">Edit Anggota HIMASI</h1>
      <a class="btn btn-outline-primary btn-sm" href="/hm/admin/?tab=himasi">Kembali</a>
    </div>

    <?php if (!$detail): ?>
      <div class="alert alert-warning">Data anggota tidak ditemukan.</div>
    <?php else: ?>
      <div class="card">
        <div class="card-body">
          <form method="post" action="/hm/admin/index.php" class="row g-2">
            <input type="hidden" name="action" value="update_himasi">
            <input type="hidden" name="original_name" value="<?= htmlspecialchars($detail['name']) ?>">
            <div class="col-md-6"><input class="form-control" name="name" value="<?= htmlspecialchars($detail['name']) ?>" placeholder="Nama" required></div>
            <div class="col-md-6">
              <select class="form-select" name="bagian">
                <option value="">(Bagian)</option>
                <?php foreach (($bagianOpts ?? []) as $opt): ?>
                  <option value="<?= htmlspecialchars($opt) ?>" <?= (isset($detail['bagian']) && mb_strtolower($detail['bagian'])===mb_strtolower($opt))?'selected':'' ?>><?= htmlspecialchars($opt) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 d-flex gap-2 mt-2">
              <button class="btn btn-primary">Simpan Perubahan</button>
              <a class="btn btn-outline-primary" href="/hm/admin/?tab=himasi">Batal</a>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
