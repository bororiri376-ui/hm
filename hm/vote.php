<?php
  $pageTitle = 'Pemilihan Ketua & Wakil HIMASI';
  require __DIR__ . '/includes/auth.php';
  require_role(['student']);

  $user = auth_user();
  $candidates = election_candidates_all();
  $pairs = $candidates['pairs'] ?? [];
  $votes = election_votes_all();

  $message = '';
  $error = '';

  // Check if already voted
  $already = false;
  foreach ($votes['records'] as $r) {
    if (($r['voter_nim'] ?? '') === ($user['nim'] ?? '')) { $already = true; break; }
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already) {
    $pairId = (int)($_POST['pair'] ?? 0);
    if ($pairId > 0 && !empty($pairs)) {
      $selected = null;
      foreach ($pairs as $p) { if ((int)$p['id'] === $pairId) { $selected = $p; break; } }
      if ($selected && (int)$selected['ketua_id'] && (int)$selected['wakil_id']) {
        $ketua = (int)$selected['ketua_id'];
        $wakil = (int)$selected['wakil_id'];
        // Insert vote records (pairs, ketua, wakil)
        $now = date('Y-m-d H:i:s');
        votes_record_insert($user['nim'], 'pairs', $pairId, $now);
        votes_record_insert($user['nim'], 'ketua', $ketua, $now);
        votes_record_insert($user['nim'], 'wakil', $wakil, $now);

        // Helper to get current total then upsert incremented value
        $getTotal = function($type,$id){
          $stmt = db()->prepare("SELECT total FROM votes_totals WHERE choice_type=? AND choice_id=?");
          $stmt->execute([$type,$id]);
          $row = $stmt->fetch();
          return (int)($row['total'] ?? 0);
        };
        $pairsTotal = $getTotal('pairs',$pairId) + 1;
        $ketuaTotal = $getTotal('ketua',$ketua) + 1;
        $wakilTotal = $getTotal('wakil',$wakil) + 1;
        votes_total_upsert('pairs',$pairId,$pairsTotal);
        votes_total_upsert('ketua',$ketua,$ketuaTotal);
        votes_total_upsert('wakil',$wakil,$wakilTotal);

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

<style>
 .option-card { cursor: pointer; transition: box-shadow .2s ease, transform .05s ease, border-color .2s ease; }
 .option-card:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.1); }
 .option-card:active { transform: scale(.998); }
 .option-card input[type=radio] { margin-left: auto; transform: scale(1.3); }
 .option-card:has(input:checked) { border: 2px solid #0d6efd; }
 .candidate-photo { width: 120px; height: 120px; object-fit: cover; border-radius: .5rem; background: #f8f9fa; }
 @media (min-width: 768px) { .candidate-photo { width: 140px; height: 140px; } }
 .role-title { font-weight: 600; }
 .nim-text { font-size: .875rem; color: #6c757d; }
 .pair-badge { min-width: 80px; }
</style>

<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="p-3 p-md-4 mb-3 bg-primary text-white rounded-3">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between">
        <div>
          <div class="h4 mb-1 mb-md-0">Selamat datang, <?= htmlspecialchars($user['name'] ?? 'Mahasiswa') ?></div>
          <div class="small text-white-50">NIM: <?= htmlspecialchars($user['nim'] ?? '') ?></div>
        </div>
        <div class="mt-2 mt-md-0 small text-white-75">Area Mahasiswa</div>
      </div>
    </div>
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
            <div class="row g-3">
              <?php $n=1; foreach ($pairs as $p): ?>
                <?php
                  $ketua = null; foreach ($candidates['ketua'] as $c) { if ((int)$c['id'] === (int)$p['ketua_id']) { $ketua = $c; break; } }
                  $wakil = null; foreach ($candidates['wakil'] as $c) { if ((int)$c['id'] === (int)$p['wakil_id']) { $wakil = $c; break; } }
                ?>
                <div class="col-12 col-md-6">
                  <label class="option-card card p-3 h-100">
                    <div class="d-flex align-items-center gap-2">
                      <span class="badge bg-primary pair-badge">Calon <?= $n ?></span>
                      <div class="ms-auto d-flex align-items-center gap-2">
                        <span class="small text-muted">Pilih</span>
                        <input class="form-check-input" type="radio" name="pair" value="<?= (int)$p['id'] ?>">
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-3">
                      <img src="<?= htmlspecialchars($ketua['photo'] ?? '') ?>" alt="Foto Ketua" class="candidate-photo">
                      <div>
                        <div class="role-title mb-1">Ketua</div>
                        <div class="fw-semibold"><?= htmlspecialchars($ketua['name'] ?? '-') ?></div>
                        <div class="nim-text">NIM: <?= htmlspecialchars($ketua['nim'] ?? '-') ?></div>
                      </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-3">
                      <img src="<?= htmlspecialchars($wakil['photo'] ?? '') ?>" alt="Foto Wakil" class="candidate-photo">
                      <div>
                        <div class="role-title mb-1">Wakil</div>
                        <div class="fw-semibold"><?= htmlspecialchars($wakil['name'] ?? '-') ?></div>
                        <div class="nim-text">NIM: <?= htmlspecialchars($wakil['nim'] ?? '-') ?></div>
                      </div>
                    </div>
                  </label>
                </div>
                <?php $n++; endforeach; ?>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Kirim Suara</button>
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
