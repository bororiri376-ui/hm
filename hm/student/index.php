<?php
  $pageTitle = 'Dashboard Mahasiswa';
  require __DIR__ . '/../includes/auth.php';
  require_role(['student']);
  $user = auth_user();

  // Ambil statistik dari database
  $totalMahasiswa = (int)db()->query("SELECT COUNT(*) AS c FROM users WHERE role='student'")->fetch()['c'];
  $beritaCount = (int)db()->query("SELECT COUNT(*) AS c FROM berita")->fetch()['c'];
  $pengumumanCount = (int)db()->query("SELECT COUNT(*) AS c FROM pengumuman")->fetch()['c'];
  

  require __DIR__ . '/../includes/header_student.php';
?>

<div class="row justify-content-center">
  <div class="col-12 col-lg-10">
    <div class="p-3 p-md-4 mb-4 text-white rounded-4 shadow-sm" style="background:linear-gradient(135deg,#6f42c1,#0d6efd);">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-2">
        <div>
          <div class="h4 h3-md mb-1">Selamat datang, <?= htmlspecialchars($user['name'] ?? 'Mahasiswa') ?></div>
          <div class="text-white-75 small">Semoga harimu produktif 🎓</div>
        </div>
      </div>
    </div>

    <div class="row g-2 g-sm-3 mb-4">
      <div class="col-12 col-sm-6 col-md-4">
        <div class="card stat-card h-100 border-0 shadow-sm" style="background:linear-gradient(135deg, rgba(13,110,253,.08), rgba(13,110,253,.02));"><div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="stat-title"><span class="stat-icon">👥</span>Total Anggota</div>
            <div class="stat-num"><?= (int)$totalMahasiswa ?></div>
          </div>
          <span class="badge bg-primary stat-badge">Mahasiswa</span>
        </div></div>
      </div>
      <div class="col-12 col-sm-6 col-md-4">
        <div class="card stat-card h-100 border-0 shadow-sm" style="background:linear-gradient(135deg, rgba(25,135,84,.08), rgba(25,135,84,.02));"><div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="stat-title"><span class="stat-icon">📰</span>Berita Terbaru</div>
            <div class="stat-num"><?= (int)$beritaCount ?></div>
          </div>
          <span class="badge bg-secondary stat-badge">Info</span>
        </div></div>
      </div>
      <div class="col-12 col-sm-6 col-md-4">
        <div class="card stat-card h-100 border-0 shadow-sm" style="background:linear-gradient(135deg, rgba(111,66,193,.08), rgba(111,66,193,.02));"><div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <div class="stat-title"><span class="stat-icon">📢</span>Kegiatan</div>
            <div class="stat-num"><?= (int)$pengumumanCount ?></div>
          </div>
          <span class="badge bg-info text-dark stat-badge">Aktif</span>
        </div></div>
      </div>
    </div>

    

    <!-- Kritik & Saran dipindahkan ke halaman khusus -->

    <!-- Kegiatan & Berita dihapus dari dashboard untuk merapikan tampilan -->

    <!-- Galeri dihapus dari dashboard -->

  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
