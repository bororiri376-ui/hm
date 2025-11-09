<?php
  $pageTitle = 'Login';
  require __DIR__ . '/includes/auth.php';

  $error = '';
  $success = '';
  $tab = ($_GET['tab'] ?? 'login') === 'daftar' ? 'daftar' : 'login';
  // Load HIMASI sections for registration form (from DB)
  $himasi_bagian = himasi_bagian_all();
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'login';
    if ($mode === 'register') {
      $name = trim($_POST['name'] ?? '');
      $nim = trim($_POST['nim'] ?? '');
      $password = trim($_POST['password'] ?? '');
      $bagian = trim($_POST['bagian'] ?? '');
      if (!$name || !$nim || !$password) {
        $error = 'Semua field wajib diisi.';
        $tab = 'daftar';
      } else {
        $existing = user_db_get($nim);
        if ($existing) {
          $error = 'NIM sudah terdaftar.';
          $tab = 'daftar';
        } else {
          // Rule: posisi yang diawali 'Ketua' hanya boleh 1 orang PER BAGIAN (contoh: 'Ketua Divisi Agama' unik)
          $bagianKey = mb_strtolower(trim($bagian));
          if (mb_strpos($bagianKey, 'ketua') === 0 && himasi_exists_in_bagian($bagian)) {
            $error = 'Bagian Ketua tersebut sudah ditempati. Silakan pilih bagian lain.';
            $tab = 'daftar';
          } else {
            user_db_upsert($nim, $name, $password, 'student');
            // Also insert into HIMASI list so it appears in Admin immediately
            himasi_add($name, $bagian);
            if (auth_login($nim, $password)) {
              header('Location: /hm/student/');
              exit;
            }
            $success = 'Pendaftaran berhasil. Silakan login.';
            $tab = 'login';
          }
        }
      }
    } else {
      $nim = trim($_POST['nim'] ?? '');
      $password = trim($_POST['password'] ?? '');
      if ($nim && $password && auth_login($nim, $password)) {
        $u = auth_user();
        if ($u && isset($u['role'])) {
          if ($u['role'] === 'admin') { header('Location: /hm/admin/'); exit; }
          if ($u['role'] === 'ketua') { header('Location: /hm/ketua/'); exit; }
          if ($u['role'] === 'student') { header('Location: /hm/student/'); exit; }
        }
        header('Location: /hm/');
        exit;
      } else {
        $error = 'NIM atau password salah.';
        $tab = 'login';
      }
    }
  }

  require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card">
      <div class="card-body">
        
        <?php if ($error): ?><div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success py-2 mb-3"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <?php if ($tab==='daftar'): ?>
          <form method="post" class="row g-2">
            <input type="hidden" name="mode" value="register">
            <div class="col-12">
              <label class="form-label">Nama Lengkap</label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="col-12">
              <label class="form-label">NIM</label>
              <input type="text" class="form-control" name="nim" required>
            </div>
            <div class="col-12">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" name="password" required>
            </div>
            <div class="col-12">
              <label class="form-label">Bagian HIMASI</label>
              <select class="form-select" name="bagian">
                <option value="">Pilih Bagian...</option>
                <?php foreach (($himasi_bagian ?? []) as $bopt): ?>
                  <?php 
                    $isKetua = (mb_strpos(mb_strtolower(trim((string)$bopt)), 'ketua') === 0);
                    $occupied = $isKetua ? himasi_exists_in_bagian($bopt) : false;
                  ?>
                  <option value="<?= htmlspecialchars($bopt) ?>" <?= $occupied ? 'disabled' : '' ?>>
                    <?= htmlspecialchars($bopt) ?><?= $occupied ? ' (sudah ditempati)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <button class="btn btn-primary w-100" type="submit">Daftar</button>
            </div>
          </form>
          <div class="text-muted small mt-3">Sudah punya akun? <a href="/hm/login.php?tab=login">Login</a></div>
        <?php else: ?>
          <form method="post">
            <input type="hidden" name="mode" value="login">
            <div class="mb-3">
              <label class="form-label">NIM</label>
              <input type="text" class="form-control" name="nim" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" class="form-control" name="password" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Masuk</button>
          </form>
          <div class="text-muted small mt-3">Belum punya akun? <a href="/hm/login.php?tab=daftar">Daftar</a></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  </div>

<?php require __DIR__ . '/includes/footer.php'; ?>
