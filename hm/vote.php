<?php
  $pageTitle = 'Pemilihan Ketua & Wakil HIMASI';
  require __DIR__ . '/includes/auth.php';
  require_role(['student']);

  $user = auth_user();
  $candidates = election_candidates();
  $pairs = $candidates['pairs'] ?? [];
  $votes = election_votes();

  $message = '';
  $error = '';

  // Check if already voted
  $already = false;
  foreach ($votes['records'] as $r) {
    if ($r['nim'] === $user['nim']) { $already = true; break; }
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already) {
    $pairId = (int)($_POST['pair'] ?? 0);
    if ($pairId > 0 && !empty($pairs)) {
      $selected = null;
      foreach ($pairs as $p) { if ((int)$p['id'] === $pairId) { $selected = $p; break; } }
      if ($selected && (int)$selected['ketua_id'] && (int)$selected['wakil_id']) {
        $ketua = (int)$selected['ketua_id'];
        $wakil = (int)$selected['wakil_id'];
        $votes['records'][] = [ 'nim' => $user['nim'], 'ketua' => $ketua, 'wakil' => $wakil ];
        if (!isset($votes['totals']['pairs'][$pairId])) $votes['totals']['pairs'][$pairId] = 0;
        if (!isset($votes['totals']['ketua'][$ketua])) $votes['totals']['ketua'][$ketua] = 0;
        if (!isset($votes['totals']['wakil'][$wakil])) $votes['totals']['wakil'][$wakil] = 0;
        $votes['totals']['pairs'][$pairId] += 1;
        $votes['totals']['ketua'][$ketua] += 1;
        $votes['totals']['wakil'][$wakil] += 1;
        election_votes_save($votes);
        $message = 'Terima kasih, suara Anda telah direkam.';
        $already = true;
      } else {
        $error = 'Silakan pilih salah satu pasangan calon.';
      }
    } else {
      $error = 'Belum ada pasangan atau belum dipilih.';
    }
  }

  require __DIR__ . '/includes/header_student.php';
?>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-body">
        <h1 class="h4 mb-3">Pemilihan Ketua & Wakil HIMASI</h1>
        <div class="text-muted small mb-3">Login: <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['nim']) ?>)</div>
        <?php if ($message): ?><div class="alert alert-success py-2"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <?php if ($already): ?>
          <div class="alert alert-info">Anda sudah memberikan suara. Terima kasih.</div>
        <?php else: ?>
        <form method="post">
          <div class="mb-2 fw-semibold">Pilih 1 Pasangan Calon</div>
          <?php if (!empty($pairs)): ?>
            <div class="row g-2">
              <?php $n=1; foreach ($pairs as $p): ?>
                <?php
                  $ketua = null; foreach ($candidates['ketua'] as $c) { if ((int)$c['id'] === (int)$p['ketua_id']) { $ketua = $c; break; } }
                  $wakil = null; foreach ($candidates['wakil'] as $c) { if ((int)$c['id'] === (int)$p['wakil_id']) { $wakil = $c; break; } }
                ?>
                <div class="col-12">
                  <label class="card p-2 d-flex align-items-center gap-3">
                    <span class="badge bg-primary">Calon <?= $n ?></span>
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                      <div class="d-flex align-items-center gap-2">
                        <img src="<?= htmlspecialchars($ketua['photo'] ?? '') ?>" alt="" class="rounded" style="width:48px;height:48px;object-fit:cover;">
                        <div>
                          <div class="fw-semibold small mb-0">Ketua: <?= htmlspecialchars($ketua['name'] ?? '-') ?></div>
                          <div class="text-muted small">NIM: <?= htmlspecialchars($ketua['nim'] ?? '-') ?></div>
                        </div>
                      </div>
                      <div class="d-flex align-items-center gap-2">
                        <img src="<?= htmlspecialchars($wakil['photo'] ?? '') ?>" alt="" class="rounded" style="width:48px;height:48px;object-fit:cover;">
                        <div>
                          <div class="fw-semibold small mb-0">Wakil: <?= htmlspecialchars($wakil['name'] ?? '-') ?></div>
                          <div class="text-muted small">NIM: <?= htmlspecialchars($wakil['nim'] ?? '-') ?></div>
                        </div>
                      </div>
                    </div>
                    <input class="form-check-input ms-auto" type="radio" name="pair" value="<?= (int)$p['id'] ?>">
                  </label>
                </div>
                <?php $n++; endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary mt-2">Kirim Suara</button>
          <?php else: ?>
            <div class="alert alert-info">Belum ada pasangan calon yang tersedia. Silakan coba lagi nanti.</div>
          <?php endif; ?>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
