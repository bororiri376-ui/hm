<?php
  $pageTitle = 'Galeri';
  require __DIR__ . '/includes/header.php';

  $items = galeri_all();
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Galeri</h1>
</div>
<div class="row g-3">
  <?php foreach ($items as $g): ?>
    <div class="col-6 col-md-3">
      <div class="card h-100">
        <img src="<?= htmlspecialchars($g['image']) ?>" class="gallery-img card-img-top" alt="">
        <div class="card-body py-2">
          <div class="small text-muted"><?= htmlspecialchars($g['caption']) ?></div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
