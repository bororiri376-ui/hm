<?php
  $pageTitle = 'Kritik dan Saran';
  require __DIR__ . '/../includes/auth.php';
  require_role(['student']);
  $user = auth_user();

  $error = '';
  $success = '';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    if ($message === '') {
      $error = 'Pesan wajib diisi.';
    } else {
      $date = date('Y-m-d H:i:s');
      feedback_add($date, ($user['name'] ?? ''), ($user['nim'] ?? ''), $message);
      $success = 'Terima kasih, saran/kritik Anda telah terkirim.';
    }
  }

  require __DIR__ . '/../includes/header_student.php';
?>

<div class="row justify-content-center">
  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-body">
        <h1 class="h5 mb-3">Kritik dan Saran</h1>
        <?php if ($error): ?><div class="alert alert-danger py-2 mb-3"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success py-2 mb-3"><?= htmlspecialchars($success) ?></div><?php endif; ?>
        <form method="post" class="row g-2">
          <div class="col-12">
            <label class="form-label">Pesan</label>
            <textarea class="form-control" name="message" rows="5" placeholder="Tulis saran atau kritik Anda..." required></textarea>
          </div>
          <div class="col-12">
            <button class="btn btn-primary" type="submit">Kirim</button>
            <a class="btn btn-outline-secondary ms-2" href="/hm/student/">Kembali ke Dashboard</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
