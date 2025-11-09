<?php
  $pageTitle = 'Pengumuman';
  require __DIR__ . '/includes/header.php';

  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
?>

<?php if ($id):
  $stmt = db()->prepare("SELECT id, title, DATE_FORMAT(date, '%Y-%m-%d') AS date, excerpt, content, link FROM pengumuman WHERE id=?");
  $stmt->execute([$id]);
  $detail = $stmt->fetch();
?>
  <?php if ($detail): ?>
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/hm/">Beranda</a></li>
        <li class="breadcrumb-item"><a href="/hm/pengumuman.php">Pengumuman</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail</li>
      </ol>
    </nav>

    <div class="p-3 p-md-4 mb-3 rounded-4" style="background:linear-gradient(135deg, rgba(13,110,253,.08), rgba(102,16,242,.08));">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
        <div>
          <h1 class="h4 mb-1">
            <?= htmlspecialchars($detail['title']) ?>
          </h1>
          <span class="badge rounded-pill text-bg-light border text-muted">
            📅 <?= htmlspecialchars($detail['date']) ?>
          </span>
        </div>
        <?php if (!empty($detail['link'])): ?>
          <a href="<?= htmlspecialchars($detail['link']) ?>" target="_blank" class="btn btn-primary">
            Kunjungi tautan
          </a>
        <?php endif; ?>
      </div>
    </div>

    <article class="card border-0 shadow-sm mb-4">
      <div class="card-body">
        <div class="lead" style="white-space:pre-wrap;"><?= nl2br(htmlspecialchars($detail['content'])) ?></div>
      </div>
    </article>

    <a class="btn btn-outline-primary" href="/hm/pengumuman.php">Kembali</a>
  <?php else: ?>
    <div class="alert alert-warning">Pengumuman tidak ditemukan.</div>
  <?php endif; ?>
<?php else: ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Pengumuman</h1>
  </div>
  <?php $data = pengumuman_all(); ?>
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
