<?php
  $pageTitle = 'Dashboard Ketua';
  require __DIR__ . '/../includes/auth.php';
  require_role(['ketua']);

  // Stats
  $totKetua = (int)db()->query("SELECT COUNT(*) AS c FROM election_ketua")->fetch()['c'];
  $totWakil = (int)db()->query("SELECT COUNT(*) AS c FROM election_wakil")->fetch()['c'];
  $totPairs = (int)db()->query("SELECT COUNT(*) AS c FROM election_pairs")->fetch()['c'];
  $totVotes = (int)db()->query("SELECT SUM(total) AS s FROM votes_totals WHERE choice_type='pairs'")->fetch()['s'];
  if (!$totVotes) { $totVotes = 0; }

  $tab = $_GET['tab'] ?? 'dashboard';
  $msg = '';
  // Load datasets for read-only views
  $berita = berita_all();
  $pengumuman = pengumuman_all();
  $bph = bph_all();
  $himasi = himasi_all();
  $galeri = galeri_all();
  $feedback = feedback_all();
  // Election pairs and vote totals for recap
  $cands = election_candidates_all();
  $pairs = $cands['pairs'] ?? [];
  $votesAll = election_votes_all();
  $pairTotals = $votesAll['totals']['pairs'] ?? [];

  require __DIR__ . '/../includes/header_ketua.php';
?>
<?php $currentUser = auth_user(); ?>
<div class="alert alert-info d-flex align-items-center justify-content-between py-3 mb-3">
  <div class="h4 mb-0">Selamat datang, <?= htmlspecialchars($currentUser['name'] ?? 'Ketua') ?>.</div>
  <span class="text-muted small">Panel Ketua HIMASI</span>
</div>

<?php if ($tab==='dashboard'): ?>
  <!-- Grid of boxes for sections -->
  <div class="row g-2 g-sm-3">
    <div class="col-6 col-md-4 col-lg-3">
      <a class="text-decoration-none" href="/hm/ketua/report.php"><div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div class="small text-muted">Calon</div>
          <div class="fs-4 fw-bold"><?= (int)$totPairs ?></div>
        </div>
        <div class="display-6">🗳️</div>
      </div></div></a>
    </div>
    <div class="col-6 col-md-4 col-lg-3">
      <a class="text-decoration-none" href="?tab=berita"><div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div class="small text-muted">Berita</div>
          <div class="fs-4 fw-bold"><?= count((array)$berita) ?></div>
        </div>
        <div class="display-6">📰</div>
      </div></div></a>
    </div>
    <div class="col-6 col-md-4 col-lg-3">
      <a class="text-decoration-none" href="?tab=pengumuman"><div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div class="small text-muted">Kegiatan</div>
          <div class="fs-4 fw-bold"><?= count((array)$pengumuman) ?></div>
        </div>
        <div class="display-6">📢</div>
      </div></div></a>
    </div>
    <div class="col-6 col-md-4 col-lg-3">
      <a class="text-decoration-none" href="?tab=bph"><div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div class="small text-muted">Anggota BPH</div>
          <div class="fs-4 fw-bold"><?= count((array)$bph) ?></div>
        </div>
        <div class="display-6">👥</div>
      </div></div></a>
    </div>
    <div class="col-6 col-md-4 col-lg-3">
      <a class="text-decoration-none" href="?tab=himasi"><div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div class="small text-muted">Anggota HIMASI</div>
          <div class="fs-4 fw-bold"><?= count((array)$himasi) ?></div>
        </div>
        <div class="display-6">👤</div>
      </div></div></a>
    </div>
    <div class="col-6 col-md-4 col-lg-3">
      <a class="text-decoration-none" href="?tab=galeri"><div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div class="small text-muted">Galeri</div>
          <div class="fs-4 fw-bold"><?= count((array)$galeri) ?></div>
        </div>
        <div class="display-6">🖼️</div>
      </div></div></a>
    </div>
    <div class="col-6 col-md-4 col-lg-3">
      <a class="text-decoration-none" href="?tab=feedback"><div class="card h-100"><div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div class="small text-muted">Saran & Kritik</div>
          <div class="fs-4 fw-bold"><?= count((array)$feedback) ?></div>
        </div>
        <div class="display-6">💬</div>
      </div></div></a>
    </div>
  </div>
<?php endif; ?>

<?php if ($tab==='berita'): ?>
      <style>
        .news-thumb { width: 100%; height: 160px; object-fit: cover; background: #f1f3f5; }
        @media (min-width: 576px) { .news-thumb { height: 180px; } }
      </style>
      <div class="card"><div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h2 class="h6 mb-0">Daftar Berita</h2>
        </div>
        <?php if (empty($berita)): ?>
          <div class="text-muted small">Belum ada berita.</div>
        <?php else: ?>
          <div class="row g-2 g-sm-3">
            <?php foreach ($berita as $b): ?>
              <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">
                  <?php if (!empty($b['image'])): ?>
                    <img src="<?= htmlspecialchars($b['image']) ?>" alt="" class="news-thumb">
                  <?php endif; ?>
                  <div class="card-body">
                    <div class="fw-semibold mb-1" style="min-height:2.25rem; line-height:1.1;">
                      <?= htmlspecialchars($b['title']) ?>
                    </div>
                    <div class="text-muted small mb-2">
                      <?= htmlspecialchars($b['date']) ?><?= ($b['author']??'') ? ' • '.htmlspecialchars($b['author']) : '' ?>
                    </div>
                    <div class="small text-secondary" style="min-height:3.6rem;">
                      <?= htmlspecialchars($b['excerpt'] ?? '') ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div></div>
<?php endif; ?>

<?php if ($tab==='pengumuman'): ?>
      <div class="card"><div class="card-body">
        <h2 class="h6">Daftar Kegiatan</h2>
        <?php if (empty($pengumuman)): ?>
          <div class="text-muted small">Belum ada kegiatan.</div>
        <?php else: ?>
          <?php foreach ($pengumuman as $p): ?>
            <div class="py-2 border-bottom small">
              <div class="fw-semibold"><?= htmlspecialchars($p['title']) ?></div>
              <div class="text-muted"><?= htmlspecialchars($p['date']) ?><?= ($p['link']??'')? ' • '.htmlspecialchars($p['link']):'' ?></div>
              <div><?= htmlspecialchars($p['excerpt'] ?? '') ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div></div>
<?php endif; ?>

<?php if ($tab==='bph'): ?>
      <div class="card"><div class="card-body">
        <h2 class="h6">Anggota BPH</h2>
        <?php if (empty($bph)): ?>
          <div class="text-muted small">Belum ada data.</div>
        <?php else: ?>
          <?php foreach ($bph as $m): ?>
            <div class="row g-2 align-items-center py-2 border-bottom small">
              <div class="col-md-3 fw-semibold"><?= htmlspecialchars($m['name']) ?></div>
              <div class="col-md-3 text-muted"><?= htmlspecialchars($m['position']) ?></div>
              <div class="col-md-4"><?= htmlspecialchars($m['contact'] ?? '') ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div></div>
<?php endif; ?>

<?php if ($tab==='himasi'): ?>
      <div class="card"><div class="card-body">
        <h2 class="h6">Anggota HIMASI</h2>
        <?php if (empty($himasi)): ?>
          <div class="text-muted small">Belum ada data.</div>
        <?php else: ?>
          <?php foreach ($himasi as $m2): ?>
            <div class="row g-2 align-items-center py-2 border-bottom small">
              <div class="col-md-4 fw-semibold"><?= htmlspecialchars($m2['name']) ?></div>
              <div class="col-md-3 text-muted"><?= htmlspecialchars($m2['bagian'] ?? '') ?></div>
              <div class="col-md-5"><?= htmlspecialchars($m2['contact'] ?? '') ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div></div>
<?php endif; ?>

<?php if ($tab==='galeri'): ?>
      <div class="card"><div class="card-body">
        <h2 class="h6">Galeri</h2>
        <?php if (empty($galeri)): ?>
          <div class="text-muted small">Belum ada foto.</div>
        <?php else: ?>
          <div class="row g-2">
            <?php foreach ($galeri as $g): ?>
              <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100">
                  <?php if (!empty($g['image'])): ?>
                    <img src="<?= htmlspecialchars($g['image']) ?>" class="gallery-img" alt="">
                  <?php endif; ?>
                  <div class="card-body small"><?= htmlspecialchars($g['caption'] ?? '') ?></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div></div>
<?php endif; ?>

<?php if ($tab==='feedback'): ?>
      <div class="card"><div class="card-body">
        <h2 class="h6">Saran & Kritik</h2>
        <?php if (empty($feedback)): ?>
          <div class="text-muted small">Belum ada feedback.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead><tr><th style="width:80px;">ID</th><th>Tanggal</th><th>Nama</th><th>NIM</th><th>Pesan</th></tr></thead>
              <tbody>
                <?php foreach ($feedback as $f): ?>
                  <tr>
                    <td><?= (int)($f['id'] ?? 0) ?></td>
                    <td class="small text-muted"><?= htmlspecialchars($f['date'] ?? '') ?></td>
                    <td class="small"><?= htmlspecialchars($f['name'] ?? '') ?></td>
                    <td class="small"><?= htmlspecialchars($f['nim'] ?? '') ?></td>
                    <td class="small" style="max-width:420px; white-space:pre-wrap;"><?= nl2br(htmlspecialchars($f['message'] ?? '')) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div></div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
