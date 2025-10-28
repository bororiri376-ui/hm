<?php
  $pageTitle = 'Berita';
  require __DIR__ . '/includes/header.php';

  function read_json($path) {
    $json = @file_get_contents($path);
    if ($json === false) return [];
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
  }
  
  $berita = read_json(__DIR__ . '/data/berita.json');
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<?php if ($id): $detail = null; foreach ($berita as $b) { if ((int)$b['id'] === $id) { $detail = $b; break; } } ?>
  <?php if ($detail): ?>
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/hm/">Beranda</a></li>
        <li class="breadcrumb-item"><a href="/hm/berita.php">Berita</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
      </ol>
    </nav>
    <article class="mb-4">
      <img src="<?= htmlspecialchars($detail['image']) ?>" class="img-fluid rounded mb-3" alt="">
      <h1 class="h3"><?= htmlspecialchars($detail['title']) ?></h1>
      <div class="text-muted small mb-3"><?= htmlspecialchars($detail['date']) ?> • <?= htmlspecialchars($detail['author']) ?></div>
      <p><?= nl2br(htmlspecialchars($detail['content'])) ?></p>
    </article>
    <a class="btn btn-outline-secondary" href="/hm/berita.php">Kembali</a>
  <?php else: ?>
    <div class="alert alert-warning">Berita tidak ditemukan.</div>
  <?php endif; ?>
<?php else: ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Berita</h1>
  </div>
  <div class="row g-3">
    <?php foreach ($berita as $b): ?>
      <div class="col-md-4">
        <div class="card h-100">
          <img src="<?= htmlspecialchars($b['image']) ?>" class="card-img-top" alt="">
          <div class="card-body">
            <h3 class="h6 card-title mb-1"><?= htmlspecialchars($b['title']) ?></h3>
            <div class="text-muted small mb-2"><?= htmlspecialchars($b['date']) ?> • <?= htmlspecialchars($b['author']) ?></div>
            <p class="card-text small"><?= htmlspecialchars($b['excerpt']) ?></p>
            <a class="stretched-link" href="/hm/berita.php?id=<?= (int)$b['id'] ?>"></a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
