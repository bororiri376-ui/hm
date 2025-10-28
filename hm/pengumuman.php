<?php
  $pageTitle = 'Pengumuman';
  require __DIR__ . '/includes/header.php';

  function read_json($path) {
    $json = @file_get_contents($path);
    if ($json === false) return [];
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
  }

  $data = read_json(__DIR__ . '/data/pengumuman.json');
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<?php if ($id): $detail = null; foreach ($data as $p) { if ((int)$p['id'] === $id) { $detail = $p; break; } } ?>
  <?php if ($detail): ?>
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/hm/">Beranda</a></li>
        <li class="breadcrumb-item"><a href="/hm/pengumuman.php">Pengumuman</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
      </ol>
    </nav>
    <article class="mb-4">
      <h1 class="h3"><?= htmlspecialchars($detail['title']) ?></h1>
      <div class="text-muted small mb-3"><?= htmlspecialchars($detail['date']) ?></div>
      <p><?= nl2br(htmlspecialchars($detail['content'])) ?></p>
      <?php if (!empty($detail['link'])): ?>
        <a href="<?= htmlspecialchars($detail['link']) ?>" target="_blank" class="btn btn-primary">Kunjungi tautan</a>
      <?php endif; ?>
    </article>
    <a class="btn btn-outline-secondary" href="/hm/pengumuman.php">Kembali</a>
  <?php else: ?>
    <div class="alert alert-warning">Pengumuman tidak ditemukan.</div>
  <?php endif; ?>
<?php else: ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Pengumuman</h1>
  </div>
  <div class="list-group">
    <?php foreach ($data as $p): ?>
      <a class="list-group-item list-group-item-action" href="/hm/pengumuman.php?id=<?= (int)$p['id'] ?>">
        <div class="d-flex w-100 justify-content-between">
          <h3 class="h6 mb-1"><?= htmlspecialchars($p['title']) ?></h3>
          <small class="text-muted"><?= htmlspecialchars($p['date']) ?></small>
        </div>
        <p class="mb-1 small text-muted"><?= htmlspecialchars($p['excerpt']) ?></p>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
