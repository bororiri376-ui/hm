<?php
  $pageTitle = 'Laporan Pemilihan';
  require __DIR__ . '/../includes/auth.php';
  require_role(['ketua']);

  $candidates = election_candidates();
  $votes = election_votes();

  function name_by_id($list, $id) {
    foreach ($list as $c) { if ((int)$c['id'] === (int)$id) return $c['name']; }
    return 'N/A';
  }

  require __DIR__ . '/../includes/header_ketua.php';
?>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card"><div class="card-body">
      <h2 class="h5">Rekap Suara - Ketua</h2>
      <?php if (!empty($votes['totals']['ketua'])): ?>
        <?php foreach ($votes['totals']['ketua'] as $id=>$count): ?>
          <div class="d-flex justify-content-between small py-1 border-bottom">
            <div>#<?= (int)$id ?> - <?= htmlspecialchars(name_by_id($candidates['ketua'], $id)) ?></div>
            <div><strong><?= (int)$count ?></strong></div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="text-muted small">Belum ada suara.</div>
      <?php endif; ?>
    </div></div>
  </div>
  <div class="col-md-6">
    <div class="card"><div class="card-body">
      <h2 class="h5">Rekap Suara - Wakil</h2>
      <?php if (!empty($votes['totals']['wakil'])): ?>
        <?php foreach ($votes['totals']['wakil'] as $id=>$count): ?>
          <div class="d-flex justify-content-between small py-1 border-bottom">
            <div>#<?= (int)$id ?> - <?= htmlspecialchars(name_by_id($candidates['wakil'], $id)) ?></div>
            <div><strong><?= (int)$count ?></strong></div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="text-muted small">Belum ada suara.</div>
      <?php endif; ?>
    </div></div>
  </div>
</div>

<div class="card mt-3"><div class="card-body">
  <h2 class="h6">Log Suara</h2>
  <?php if (!empty($votes['records'])): ?>
    <div class="table-responsive">
      <table class="table table-sm align-middle">
        <thead><tr><th>NIM</th><th>Ketua</th><th>Wakil</th></tr></thead>
        <tbody>
          <?php foreach ($votes['records'] as $r): ?>
            <tr>
              <td class="small"><?= htmlspecialchars($r['nim']) ?></td>
              <td class="small">#<?= (int)$r['ketua'] ?> - <?= htmlspecialchars(name_by_id($candidates['ketua'], $r['ketua'])) ?></td>
              <td class="small">#<?= (int)$r['wakil'] ?> - <?= htmlspecialchars(name_by_id($candidates['wakil'], $r['wakil'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="text-muted small">Belum ada log suara.</div>
  <?php endif; ?>
</div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
