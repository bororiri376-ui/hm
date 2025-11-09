<?php
  $pageTitle = 'Berita';
  require __DIR__ . '/includes/header.php';

  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<?php if ($id):
  $stmt = db()->prepare("SELECT id, title, DATE_FORMAT(date, '%Y-%m-%d') AS date, author, excerpt, content, image FROM berita WHERE id=?");
  $stmt->execute([$id]);
  $detail = $stmt->fetch();
?>
  <?php if ($detail): ?>
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/hm/">Beranda</a></li>
        <li class="breadcrumb-item"><a href="/hm/berita.php">Berita</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
      </ol>
    </nav>
    <article class="mb-4 berita-detail">
      <div class="row g-4 align-items-start">
        <div class="col-md-5 col-lg-4">
          <img src="<?= htmlspecialchars($detail['image']) ?>" class="img-fluid rounded shadow-sm berita-img" alt="">
        </div>
        <div class="col-md-7 col-lg-8">
          <h1 class="display-5 fw-semibold mb-2 berita-title"><?= htmlspecialchars($detail['title']) ?></h1>
          <div class="text-muted small mb-3"><?= htmlspecialchars($detail['date']) ?> • <?= htmlspecialchars($detail['author']) ?></div>
          <div class="berita-content">
            <p><?= nl2br(htmlspecialchars($detail['content'])) ?></p>
          </div>
        </div>
      </div>
    </article>
    <a class="btn btn-outline-secondary" href="/hm/berita.php">Kembali</a>
  <?php else: ?>
    <div class="alert alert-warning">Berita tidak ditemukan.</div>
  <?php endif; ?>
<?php else: ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Berita</h1>
  </div>
  <?php $berita = berita_all(); ?>
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
