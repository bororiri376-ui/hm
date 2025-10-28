<?php
  $pageTitle = 'Beranda';
  require __DIR__ . '/includes/header.php';

  function read_json($path) {
    $json = @file_get_contents($path);
    if ($json === false) return [];
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
  }

  $berita = read_json(__DIR__ . '/data/berita.json');
  $pengumuman = read_json(__DIR__ . '/data/pengumuman.json');
  $users = read_json(__DIR__ . '/data/users.json');
  $bph = read_json(__DIR__ . '/data/bph.json');
  $galeri = read_json(__DIR__ . '/data/galeri.json');
  $heroImg = '';
  if (is_array($galeri) && count($galeri) > 0 && !empty($galeri[0]['image'])) {
    $heroImg = $galeri[0]['image'];
  }
  $totalMahasiswa = 0;
  if (is_array($users)) {
    foreach ($users as $u) { if (($u['role'] ?? '') === 'student') { $totalMahasiswa++; } }
  }
  $totalAktif = $totalMahasiswa; // asumsi seluruh mahasiswa aktif; bisa diganti jika ada field status
  $totalBph = is_array($bph) ? count($bph) : 0;
?>

<section class="mb-4">
  <div class="position-relative rounded-3 overflow-hidden border">
    <div class="w-100 hero-bg" style="background: <?= $heroImg ? 'url(' . htmlspecialchars($heroImg) . ') center/cover no-repeat' : 'linear-gradient(135deg,#9ca3af,#d1d5db)' ?>;"></div>
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,.35);"></div>
    <div class="position-absolute top-50 start-50 translate-middle w-100 px-4 px-md-5 text-white">
      <h1 class="h3 mb-1">Selamat Datang di HIMASI</h1>
      <div class="text-white-50">Himpunan Mahasiswa Sistem Informasi</div>
    </div>
  </div>
</section>

<section class="mb-4">
  <div class="row g-2 g-md-3">
    <div class="col-4 col-md-4">
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
    <div class="col-4 col-md-4">
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
    <div class="col-4 col-md-4">
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
    <h2 class="h4 mb-0">Tujuan HIMASI</h2>
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
    <h2 class="h4 mb-0">Berita Terbaru</h2>
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
    <h2 class="h4 mb-0">Pengumuman</h2>
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

<?php require __DIR__ . '/includes/footer.php'; ?>
