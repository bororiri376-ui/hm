<?php
  $pageTitle = isset($pageTitle) ? $pageTitle . ' | HIMASI' : 'HIMASI';
  require_once __DIR__ . '/auth.php';
  $currentUser = auth_user();
  $logoSrc = '';
  if (file_exists(__DIR__ . '/../assets/img/logo.webp')) { $logoSrc = '/hm/assets/img/logo.webp'; }
  elseif (file_exists(__DIR__ . '/../assets/img/HIMASI.png')) { $logoSrc = '/hm/assets/img/HIMASI.png'; }
  elseif (file_exists(__DIR__ . '/../assets/img/logo.png')) { $logoSrc = '/hm/assets/img/logo.png'; }
  elseif (file_exists(__DIR__ . '/../assets/img/HIMASI.jpg')) { $logoSrc = '/hm/assets/img/HIMASI.jpg'; }
  elseif (file_exists(__DIR__ . '/../assets/img/logo.jpg')) { $logoSrc = '/hm/assets/img/logo.jpg'; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="Website resmi HIMASI - Himpunan Mahasiswa Sistem Informasi">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/hm/assets/css/style.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand fw-bold d-flex align-items-center" href="/hm/">
        <?php if ($logoSrc): ?>
          <img src="<?= htmlspecialchars($logoSrc) ?>" alt="Logo" class="brand-logo">
        <?php endif; ?>
        <span>HIMASI</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="/hm/berita.php">Berita</a></li>
          <li class="nav-item"><a class="nav-link" href="/hm/pengumuman.php">Pengumuman</a></li>
          <li class="nav-item"><a class="nav-link" href="/hm/bph.php">Anggota BPH</a></li>
          <li class="nav-item"><a class="nav-link" href="/hm/galeri.php">Galeri</a></li>
          <?php if ($currentUser): ?>
            <li class="nav-item"><a class="nav-link" href="/hm/logout.php">Logout (<?= htmlspecialchars($currentUser['name']) ?>)</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/hm/login.php">Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>
  <main class="py-4">
    <div class="container">
