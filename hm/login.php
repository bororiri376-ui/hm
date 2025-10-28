<?php
  $pageTitle = 'Login';
  require __DIR__ . '/includes/auth.php';

  $error = '';
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($nim && $password && auth_login($nim, $password)) {
      $u = auth_user();
      if ($u && isset($u['role'])) {
        if ($u['role'] === 'admin') {
          header('Location: /hm/admin/');
          exit;
        }
        if ($u['role'] === 'ketua') {
          header('Location: /hm/ketua/report.php');
          exit;
        }
        if ($u['role'] === 'student') {
          header('Location: /hm/vote.php');
          exit;
        }
      }
      header('Location: /hm/');
      exit;
    } else {
      $error = 'NIM atau password salah.';
    }
  }

  require __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card">
      <div class="card-body">
        <h1 class="h4 mb-3">Login</h1>
        <?php if ($error): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post">
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
        <div class="text-muted small mt-3">Peran: mahasiswa (student), admin, ketua.</div>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
