<?php
  $pageTitle = 'Anggota BPH';
  require __DIR__ . '/includes/header.php';

  function read_json($path) {
    $json = @file_get_contents($path);
    if ($json === false) return [];
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
  }

  $anggota = read_json(__DIR__ . '/data/bph.json');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Anggota BPH</h1>
</div>
<div class="row g-3">
  <?php foreach ($anggota as $m): ?>
    <div class="col-6 col-md-3">
      <div class="card h-100 text-center">
        <img src="<?= htmlspecialchars($m['photo']) ?>" class="card-img-top" alt="">
        <div class="card-body">
          <div class="small text-primary fw-semibold"><?= htmlspecialchars($m['position']) ?></div>
          <div class="fw-semibold"><?= htmlspecialchars($m['name']) ?></div>
        </div>
        <div class="card-footer bg-white">
          <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($m['contact']) ?>">Kontak</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
