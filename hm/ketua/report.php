<?php
  $pageTitle = 'Laporan Pemilihan';
  require __DIR__ . '/../includes/auth.php';
  require_role(['ketua']);

  $candidates = election_candidates_all();
  $votes = election_votes_all();
  $pairs = $candidates['pairs'] ?? [];
  $pairTotals = $votes['totals']['pairs'] ?? [];

  function name_by_id($list, $id) {
    foreach ($list as $c) { if ((int)$c['id'] === (int)$id) return $c['name']; }
    return 'N/A';
  }

  function photo_by_id($list, $id) {
    foreach ($list as $c) { if ((int)$c['id'] === (int)$id) return ($c['photo'] ?? ''); }
    return '';
  }

  require __DIR__ . '/../includes/header_ketua.php';
?>
<style>
  @media print {
    nav, .navbar, .no-print { display: none !important; }
    body { background: #fff !important; }
    .card { box-shadow: none !important; }
    .container { max-width: 100% !important; }
  }
  .candidate-photo-print { width: 64px; height: 64px; object-fit: cover; border-radius: .5rem; background: #f8f9fa; }
  @media (min-width: 576px) { .candidate-photo-print { width: 80px; height: 80px; } }
</style>

<div class="d-flex justify-content-between align-items-center mb-3 no-print">
  <div class="alert alert-info py-2 mb-0"><strong>Laporan Pemilihan</strong></div>
  <div class="d-flex gap-2">
    <button class="btn btn-primary btn-sm" onclick="window.print()">Cetak Laporan</button>
  </div>
</div>

<div class="card"><div class="card-body">
  <h2 class="h5 mb-2">Rekap Suara</h2>
  <?php if (empty($pairs)): ?>
    <div class="text-muted small">Belum ada calon.</div>
  <?php else: ?>
    <div class="row g-2 g-sm-3">
      <?php $displayPairs = array_slice(array_values($pairs), 0, 3); foreach ($displayPairs as $idx => $p): $pid=(int)$p['id']; $v=(int)($pairTotals[(string)$pid] ?? 0); ?>
        <?php $ketuaPhoto = photo_by_id($candidates['ketua'] ?? [], $p['ketua_id'] ?? 0); $wakilPhoto = photo_by_id($candidates['wakil'] ?? [], $p['wakil_id'] ?? 0); ?>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card h-100">
            <div class="card-body">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                  <div class="small text-muted mb-1">Calon <?= (int)($idx+1) ?></div>
                  <div class="fw-semibold"><?= htmlspecialchars($p['ketua_name'] ?? '') ?> &amp; <?= htmlspecialchars($p['wakil_name'] ?? '') ?></div>
                </div>
                <div class="badge bg-success align-self-start"><?= $v ?> suara</div>
              </div>
              <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2 flex-fill">
                  <img src="<?= htmlspecialchars($ketuaPhoto) ?>" alt="Ketua" class="candidate-photo-print">
                  <div>
                    <div class="small text-muted">Ketua</div>
                    <div class="fw-semibold small"><?= htmlspecialchars($p['ketua_name'] ?? '') ?></div>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-fill">
                  <img src="<?= htmlspecialchars($wakilPhoto) ?>" alt="Wakil" class="candidate-photo-print">
                  <div>
                    <div class="small text-muted">Wakil</div>
                    <div class="fw-semibold small"><?= htmlspecialchars($p['wakil_name'] ?? '') ?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div></div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
