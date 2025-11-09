<?php
  $pageTitle = 'Beranda';
  require __DIR__ . '/includes/header.php';
  require_once __DIR__ . '/includes/db.php';

  // Stats from SQL
  $totalMahasiswa = (int)db()->query("SELECT COUNT(*) AS c FROM users WHERE role='student'")->fetch()['c'];
  $totalAktif = $totalMahasiswa; // placeholder; adjust if ada field status aktif
  $totalBph = (int)db()->query("SELECT COUNT(*) AS c FROM bph")->fetch()['c'];

  // Hero image from latest galeri
  $rowHero = db()->query("SELECT image FROM galeri ORDER BY id DESC LIMIT 1")->fetch();
  $heroImg = $rowHero['image'] ?? '';

  // Latest items
  $berita = db()->query("SELECT id, title, author, excerpt, image, DATE_FORMAT(date,'%Y-%m-%d') AS date FROM berita ORDER BY date DESC, id DESC LIMIT 3")->fetchAll();
  $pengumuman = db()->query("SELECT id, title, excerpt, DATE_FORMAT(date,'%Y-%m-%d') AS date FROM pengumuman ORDER BY date DESC, id DESC LIMIT 3")->fetchAll();

  // Ketua & Wakil HIMASI (current)
  $stmtKW = db()->query("SELECT name, position, photo, contact FROM bph WHERE position IN ('Ketua','Wakil') ORDER BY FIELD(position,'Ketua','Wakil'), name ASC");
  $ketuaWakil = $stmtKW ? $stmtKW->fetchAll() : [];
?>

<section class="mb-4">
  <div class="position-relative rounded-3 overflow-hidden border">
    <div class="w-100 hero-bg" style="background: <?= $heroImg ? 'url(' . htmlspecialchars($heroImg) . ') center/cover no-repeat' : 'linear-gradient(135deg,#9ca3af,#d1d5db)' ?>;"></div>
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: linear-gradient(to bottom, rgba(0,0,0,.35), rgba(0,0,0,.45));"></div>
    <div class="position-absolute top-50 start-50 translate-middle w-100 px-4 px-md-5 text-white">
      <h1 class="display-4 fw-bold mb-2 hero-title">Selamat Datang di HIMASI</h1>
      <div class="lead text-white-75 mb-0 hero-subtitle">Himpunan Mahasiswa Sistem Informasi</div>
    </div>
  </div>
  
</section>

 

<section class="mb-4">
  <div class="row g-2 g-sm-3 g-md-3">
    <div class="col-12 col-sm-6 col-md-4">
      <div class="card stat-card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="stat-title"><span class="stat-icon">👥</span>Anggota HIMASI</div>
            <div class="stat-num"><?= (int)$totalMahasiswa ?></div>
          </div>
          <span class="badge bg-primary stat-badge">Mahasiswa</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
      <div class="card stat-card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="stat-title"><span class="stat-icon">✅</span>Anggota Aktif</div>
            <div class="stat-num"><?= (int)$totalAktif ?></div>
          </div>
          <span class="badge bg-success stat-badge">Aktif</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-md-4">
      <div class="card stat-card h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="stat-title"><span class="stat-icon">🏛️</span>Badan Pengurus Harian</div>
            <div class="stat-num"><?= (int)$totalBph ?></div>
          </div>
          <span class="badge bg-info text-dark stat-badge">BPH</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="mb-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0 section-title">Tujuan HIMASI</h2>
  </div>
  <div class="row g-3">
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="fw-semibold mb-1">Pengembangan Akademik</div>
          <div class="text-muted small">Mendukung peningkatan kompetensi dan prestasi akademik mahasiswa Sistem Informasi melalui kegiatan ilmiah dan pelatihan.</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="fw-semibold mb-1">Organisasi & Kepemimpinan</div>
          <div class="text-muted small">Mewadahi pengembangan soft skills, kepemimpinan, dan kolaborasi melalui program kerja dan kepanitiaan.</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="fw-semibold mb-1">Kontribusi & Pengabdian</div>
          <div class="text-muted small">Memberi dampak bagi kampus dan masyarakat lewat kegiatan sosial, seminar, serta proyek berbasis teknologi.</div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="mb-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0 section-title">Berita Terbaru</h2>
    <a class="btn btn-sm btn-outline-primary" href="/hm/berita.php">Lihat semua</a>
  </div>
  <div class="row g-3">
    <?php foreach (array_slice($berita, 0, 3) as $b): ?>
      <div class="col-md-4">
        <div class="card h-100">
          <div class="ratio ratio-16x9">
            <img src="<?= htmlspecialchars($b['image']) ?>" class="w-100 h-100" style="object-fit:cover;" alt="">
          </div>
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
</section>

<section class="mb-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0 section-title">Pengumuman</h2>
    <a class="btn btn-sm btn-outline-primary" href="/hm/pengumuman.php">Lihat semua</a>
  </div>
  <div class="list-group">
    <?php foreach (array_slice($pengumuman, 0, 3) as $p): ?>
      <a class="list-group-item list-group-item-action" href="/hm/pengumuman.php?id=<?= (int)$p['id'] ?>">
        <div class="d-flex w-100 justify-content-between">
          <h3 class="h6 mb-1"><?= htmlspecialchars($p['title']) ?></h3>
          <small class="text-muted"><?= htmlspecialchars($p['date']) ?></small>
        </div>
        <p class="mb-1 small text-muted"><?= htmlspecialchars($p['excerpt']) ?></p>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php if (!empty($ketuaWakil)): ?>
<section class="mb-4">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h2 class="h4 mb-0 section-title">Ketua & Wakil HIMASI</h2>
    <a class="btn btn-sm btn-outline-primary" href="/hm/bph.php">Lihat BPH</a>
  </div>
  <div class="row g-3 mb-2">
    <?php foreach ($ketuaWakil as $m): ?>
      <div class="col-12 col-md-6">
        <div class="card h-100 text-center">
          <img src="<?= htmlspecialchars($m['photo']) ?>" class="card-img-top" alt="" style="width:220px!important; height:220px!important; object-fit:cover; display:block; margin:16px auto 0; border-radius:50%; box-shadow:0 10px 24px rgba(0,0,0,.15);">
          <div class="card-body">
            <div class="fw-semibold" style="font-size:1.05rem;"><?= htmlspecialchars($m['name']) ?></div>
            <div class="small text-muted" style="margin-top:4px;"><?= htmlspecialchars($m['position']) ?></div>
          </div>
          <?php if (!empty($m['contact'])): ?>
          <div class="card-footer bg-white">
            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($m['contact']) ?>">Kontak</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
