<?php
  $pageTitle = 'Admin Dashboard';
  require __DIR__ . '/../includes/auth.php';
  require_role(['admin']);

  // Helpers
  function read_json_local($name, $default = []) { return read_json_assoc($name, $default); }
  function write_json_local($name, $data) { write_json_assoc($name, $data); }
  function next_id($items) { $max=0; foreach ($items as $it) { if (isset($it['id'])) $max=max($max,(int)$it['id']); } return $max+1; }
  function handle_upload($field) {
    if (!isset($_FILES[$field]) || !is_array($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) return '';
    $tmp = $_FILES[$field]['tmp_name'];
    $orig = $_FILES[$field]['name'] ?? 'file';
    $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (!in_array($ext, $allowed, true)) $ext = 'jpg';
    $uploadDirFs = __DIR__ . '/../assets/uploads/';
    if (!is_dir($uploadDirFs)) { @mkdir($uploadDirFs, 0777, true); }
    $fname = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
    $destFs = $uploadDirFs . $fname;
    if (@move_uploaded_file($tmp, $destFs)) {
      return '/hm/assets/uploads/' . $fname;
    }
    return '';
  }

  // Support multiple uploads; returns array of paths
  function handle_upload_many($field) {
    $paths = [];
    if (!isset($_FILES[$field])) return $paths;
    $f = $_FILES[$field];
    $allowed = ['jpg','jpeg','png','gif','webp'];
    $uploadDirFs = __DIR__ . '/../assets/uploads/';
    if (!is_dir($uploadDirFs)) { @mkdir($uploadDirFs, 0777, true); }
    // Single file fallback
    if (!is_array($f['name'])) {
      $p = handle_upload($field);
      if ($p) $paths[] = $p;
      return $paths;
    }
    $count = count($f['name']);
    for ($i=0; $i<$count; $i++) {
      if (($f['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
      $tmp = $f['tmp_name'][$i];
      $orig = $f['name'][$i] ?? 'file';
      $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
      if (!in_array($ext, $allowed, true)) $ext = 'jpg';
      $fname = 'img_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
      $destFs = $uploadDirFs . $fname;
      if (@move_uploaded_file($tmp, $destFs)) {
        $paths[] = '/hm/assets/uploads/' . $fname;
      }
    }
    return $paths;
  }

  $tab = $_GET['tab'] ?? 'dashboard';
  $msg = '';

  // Load all datasets
  $berita = berita_all();
  $pengumuman = pengumuman_all();
  $bph = bph_all();
  $himasi = himasi_all();
  $himasi_bagian = himasi_bagian_all();
  $galeri = galeri_all();
  $feedback = feedback_all();
  $candidates = election_candidates_all();
  $votes = election_votes_all();

  // Derive HIMASI members from users with role 'student' (read-only in admin UI)
  $students = array_values(array_filter((array)users_all(), function($u){ return is_array($u) && ($u['role'] ?? '') === 'student'; }));
  $himasiNames = array_map(function($m){ return mb_strtolower(trim($m['name'] ?? '')); }, (array)$himasi);
  $himasi_auto = [];
  foreach ($students as $u) {
    $nm = trim($u['name'] ?? '');
    if ($nm === '') { continue; }
    if (in_array(mb_strtolower($nm), $himasiNames, true)) { continue; }
    $himasi_auto[] = [
      'name' => $nm,
      'photo' => '',
      'contact' => '',
      'nim' => $u['nim'] ?? ''
    ];
  }

  // Handle actions
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Berita
    if ($action === 'add_berita') {
      $imgPath = handle_upload('image_file');
      berita_insert(
        trim($_POST['title'] ?? ''),
        trim($_POST['date'] ?? ''),
        trim($_POST['author'] ?? ''),
        trim($_POST['excerpt'] ?? ''),
        trim($_POST['content'] ?? ''),
        $imgPath
      );
      $msg = 'Berita ditambahkan.'; $tab='berita';
    }
    if ($action === 'delete_berita') {
      $id = (int)($_POST['id'] ?? 0);
      berita_delete($id);
      $msg = 'Berita dihapus.'; $tab='berita';
    }
    if ($action === 'update_berita') {
      $id = (int)($_POST['id'] ?? 0);
      $title = trim($_POST['title'] ?? '');
      $date = trim($_POST['date'] ?? '');
      $author = trim($_POST['author'] ?? '');
      $excerpt = trim($_POST['excerpt'] ?? '');
      $content = trim($_POST['content'] ?? '');
      $newImg = handle_upload('image_file');
      berita_update($id, $title, $date, $author, $excerpt, $content, $newImg ?: null);
      $msg = 'Berita diperbarui.'; $tab='berita';
    }

    // Pengumuman
    if ($action === 'add_pengumuman') {
      pengumuman_insert(
        trim($_POST['title'] ?? ''),
        trim($_POST['date'] ?? ''),
        trim($_POST['excerpt'] ?? ''),
        trim($_POST['content'] ?? ''),
        trim($_POST['link'] ?? '')
      );
      $msg = 'Kegiatan ditambahkan.'; $tab='pengumuman';
    }
    if ($action === 'delete_pengumuman') {
      $id = (int)($_POST['id'] ?? 0);
      pengumuman_delete($id);
      $msg = 'Kegiatan dihapus.'; $tab='pengumuman';
    }
    if ($action === 'update_pengumuman') {
      $id = (int)($_POST['id'] ?? 0);
      $title = trim($_POST['title'] ?? '');
      $date = trim($_POST['date'] ?? '');
      $excerpt = trim($_POST['excerpt'] ?? '');
      $content = trim($_POST['content'] ?? '');
      $link = trim($_POST['link'] ?? '');
      pengumuman_update($id, $title, $date, $excerpt, $content, $link);
      $msg = 'Kegiatan diperbarui.'; $tab='pengumuman';
    }

    // BPH
    if ($action === 'add_bph') {
      $photoPath = handle_upload('bph_photo');
      bph_add(
        trim($_POST['name'] ?? ''),
        trim($_POST['position'] ?? ''),
        $photoPath,
        trim($_POST['contact'] ?? '')
      );
      $msg = 'Anggota BPH ditambahkan.'; $tab='bph';
    }
    if ($action === 'delete_bph') {
      $name = $_POST['name'] ?? '';
      bph_delete_by_name($name);
      $msg = 'Anggota BPH dihapus.'; $tab='bph';
    }
    if ($action === 'update_bph') {
      $original = $_POST['original_name'] ?? '';
      $newPhoto = handle_upload('bph_photo');
      bph_update_by_original(
        $original,
        trim($_POST['name'] ?? ''),
        trim($_POST['position'] ?? ''),
        $newPhoto ?: null,
        trim($_POST['contact'] ?? '')
      );
      $msg = 'Anggota BPH diperbarui.'; $tab='bph';
    }

    // HIMASI
    if ($action === 'add_himasi') {
      $photoPath = handle_upload('himasi_photo');
      $name = trim($_POST['name'] ?? '');
      $bagian = trim($_POST['bagian'] ?? '');
      $bagianKey = mb_strtolower(trim($bagian));
      if (mb_strpos($bagianKey, 'ketua') === 0 && himasi_exists_ketua_any()) {
        $msg = 'Gagal: Posisi Ketua sudah terisi.'; $tab='himasi';
      } else {
        himasi_add(
          $name,
          $bagian
        );
        $msg = 'Anggota HIMASI ditambahkan.'; $tab='himasi';
      }
    }
    if ($action === 'delete_himasi') {
      $name = $_POST['name'] ?? '';
      himasi_delete_by_name($name);
      $msg = 'Anggota HIMASI dihapus.'; $tab='himasi';
    }
    if ($action === 'update_himasi') {
      $original = $_POST['original_name'] ?? '';
      $name = trim($_POST['name'] ?? '');
      $bagian = trim($_POST['bagian'] ?? '');
      $bagianKey = mb_strtolower(trim($bagian));
      $conflict = (mb_strpos($bagianKey, 'ketua') === 0) ? himasi_get_first_ketua() : null;
      if ($conflict && isset($conflict['name']) && mb_strtolower(trim($conflict['name'])) !== mb_strtolower(trim($original))) {
        $msg = 'Gagal: Posisi Ketua sudah terisi.'; $tab='himasi';
      } else {
        $newPhoto = handle_upload('himasi_photo');
        himasi_update_by_original(
          $original,
          $name,
          $bagian
        );
        $msg = 'Anggota HIMASI diperbarui.'; $tab='himasi';
      }
    }

    // HIMASI: Manage Bagian (sections)
    if ($action === 'add_himasi_bagian') {
      $name = trim($_POST['name'] ?? '');
      if ($name !== '') {
        himasi_bagian_insert_ignore($name);
      }
      $msg = 'Bagian HIMASI ditambahkan.'; $tab='himasi';
    }
    if ($action === 'delete_himasi_bagian') {
      $name = trim($_POST['name'] ?? '');
      if ($name !== '') {
        himasi_bagian_delete($name);
      }
      $msg = 'Bagian HIMASI dihapus.'; $tab='himasi';
    }

    // Galeri
    if ($action === 'add_galeri') {
      $paths = handle_upload_many('galeri_images');
      $cap = trim($_POST['caption'] ?? '');
      if (empty($paths)) {
        $msg = 'Tidak ada file diunggah atau format tidak didukung.'; $tab='galeri';
      } else {
        foreach ($paths as $p) { galeri_insert_ignore($p, $cap); }
        $msg = count($paths) > 1 ? 'Beberapa foto galeri ditambahkan.' : 'Item galeri ditambahkan.'; $tab='galeri';
      }
    }
    if ($action === 'delete_galeri') {
      $image = $_POST['image'] ?? '';
      galeri_delete_by_image($image);
      $msg = 'Item galeri dihapus.'; $tab='galeri';
    }
    if ($action === 'update_galeri') {
      $original = $_POST['original_image'] ?? '';
      $cap = trim($_POST['caption'] ?? '');
      $newImg = handle_upload('galeri_image');
      galeri_update_by_original($original, $cap, $newImg ?: null);
      $msg = 'Item galeri diperbarui.'; $tab='galeri';
    }

    // Feedback (Saran & Kritik)
    if ($action === 'delete_feedback') {
      $id = (int)($_POST['id'] ?? 0);
      feedback_delete($id);
      $msg = 'Feedback dihapus.'; $tab='feedback';
    }

    // Election: add pair
    if ($action === 'add_pair') {
      $ketuaPhoto = handle_upload('ketua_photo');
      $wakilPhoto = handle_upload('wakil_photo');
      election_add_pair_db(
        trim($_POST['ketua_name'] ?? ''),
        trim($_POST['ketua_nim'] ?? ''),
        $ketuaPhoto,
        trim($_POST['wakil_name'] ?? ''),
        trim($_POST['wakil_nim'] ?? ''),
        $wakilPhoto
      );
      $msg = 'Pasangan calon ditambahkan.'; $tab='election';
    }
    if ($action === 'delete_pair') {
      $id = (int)($_POST['id'] ?? 0);
      election_delete_pair_db($id);
      $msg = 'Pasangan calon dihapus.'; $tab='election';
    }

    // Election: update pair (edit)
    if ($action === 'update_pair') {
      $pid = (int)($_POST['id'] ?? 0);
      // Find existing pair and candidate IDs
      $pair = null;
      foreach (($candidates['pairs'] ?? []) as $p) { if ((int)$p['id'] === $pid) { $pair = $p; break; } }
      if ($pair) {
        $kid = (int)($pair['ketua_id'] ?? 0);
        $wid = (int)($pair['wakil_id'] ?? 0);

        // Current photos to preserve if no new upload
        $ketuaPhotoCurrent = '';
        $wakilPhotoCurrent = '';
        foreach (($candidates['ketua'] ?? []) as $kc) { if ((int)$kc['id'] === $kid) { $ketuaPhotoCurrent = $kc['photo'] ?? ''; break; } }
        foreach (($candidates['wakil'] ?? []) as $wc) { if ((int)$wc['id'] === $wid) { $wakilPhotoCurrent = $wc['photo'] ?? ''; break; } }

        $ketuaName = trim($_POST['ketua_name'] ?? '');
        $ketuaNim  = trim($_POST['ketua_nim'] ?? '');
        $wakilName = trim($_POST['wakil_name'] ?? '');
        $wakilNim  = trim($_POST['wakil_nim'] ?? '');

        $newKetuaPhoto = handle_upload('ketua_photo');
        $newWakilPhoto = handle_upload('wakil_photo');

        // Upsert ketua & wakil
        election_ketua_upsert($kid, $ketuaName, $ketuaNim, $newKetuaPhoto !== '' ? $newKetuaPhoto : $ketuaPhotoCurrent);
        election_wakil_upsert($wid, $wakilName, $wakilNim, $newWakilPhoto !== '' ? $newWakilPhoto : $wakilPhotoCurrent);
        // Update pair names
        election_pair_upsert($pid, $kid, $wid, $ketuaName, $wakilName);

        $msg = 'Pasangan calon diperbarui.'; $tab='election';
      } else {
        $msg = 'Gagal memperbarui: pasangan tidak ditemukan.'; $tab='election';
      }
    }

    // Reset votes (include pairs)
    if ($action === 'reset_votes') {
      election_votes_reset_db();
      $msg = 'Semua suara direset.'; $tab='election';
    }

    // Simple redirect to avoid resubmission
    header('Location: /hm/admin/?tab=' . urlencode($tab) . '&msg=' . urlencode($msg));
    exit;
  }

  if (isset($_GET['msg'])) { $msg = $_GET['msg']; }

  require __DIR__ . '/../includes/header_admin.php';
?>
<div class="row">
  <div class="col-lg-3 mb-3">
    <div class="card admin-sidebar">
      <div class="card-body py-2">
        <div class="sidebar-title small text-muted mb-2">Navigasi</div>
        <div class="list-group">
          <a href="?tab=berita" class="list-group-item list-group-item-action <?= $tab==='berita'?'active':'' ?>">📰 <span>Berita</span></a>
          <a href="?tab=pengumuman" class="list-group-item list-group-item-action <?= $tab==='pengumuman'?'active':'' ?>">📢 <span>Kegiatan</span></a>
          <a href="?tab=bph" class="list-group-item list-group-item-action <?= $tab==='bph'?'active':'' ?>">👥 <span>Anggota BPH</span></a>
          <a href="?tab=himasi" class="list-group-item list-group-item-action <?= $tab==='himasi'?'active':'' ?>">👤 <span>Anggota HIMASI</span></a>
          <a href="?tab=galeri" class="list-group-item list-group-item-action <?= $tab==='galeri'?'active':'' ?>">🖼️ <span>Galeri</span></a>
          <a href="?tab=feedback" class="list-group-item list-group-item-action <?= $tab==='feedback'?'active':'' ?>">💬 <span>Saran & Kritik</span></a>
          <a href="?tab=election" class="list-group-item list-group-item-action <?= $tab==='election'?'active':'' ?>">🗳️ <span>Panel Pemilihan</span></a>
          <a href="?tab=reports" class="list-group-item list-group-item-action <?= $tab==='reports'?'active':'' ?>">🖨️ <span>Cetak Laporan</span></a>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-9">
    <?php if ($msg): ?><div class="alert alert-success py-2 mb-3"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <?php if ($tab==='dashboard'): ?>
      <div class="p-4 p-lg-5 mb-4 rounded-4 text-white shadow-sm" style="background:linear-gradient(135deg,#6f42c1,#0d6efd);">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          <div class="mb-1">
            <div class="h4 mb-1">Halo, <?= htmlspecialchars($currentUser['name'] ?? 'Admin') ?></div>
            <div class="text-white-75 small">Kelola konten dan aktivitas HIMASI dengan cepat</div>
          </div>
        </div>
      </div>

      

      <div class="row g-3">
        <div class="col-12 col-lg-8">
          <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="h6 mb-0">Distribusi Konten</div>
              </div>
              <canvas id="contentDist" height="160" style="max-height:240px"></canvas>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-4">
          <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
              <div class="h6 mb-2">Ringkasan</div>
              <ul class="list-unstyled small mb-0">
                <li>📰 Berita: <strong><?= (int)count((array)$berita) ?></strong></li>
                <li>📢 Kegiatan: <strong><?= (int)count((array)$pengumuman) ?></strong></li>
                <li>🖼️ Galeri: <strong><?= (int)count((array)$galeri) ?></strong></li>
                <li>💬 Feedback: <strong><?= (int)count((array)$feedback) ?></strong></li>
                <li>👥 BPH: <strong><?= (int)count((array)$bph) ?></strong></li>
                <li>👤 HIMASI: <strong><?= (int)count((array)$himasi) ?></strong></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <script>
        (function(){
          var el = document.getElementById('contentDist');
          if (!el || !window.Chart) return;
          var dataCounts = [
            <?= (int)count((array)$berita) ?>,
            <?= (int)count((array)$pengumuman) ?>,
            <?= (int)count((array)$galeri) ?>,
            <?= (int)count((array)$feedback) ?>,
            <?= (int)count((array)$bph) ?>,
            <?= (int)count((array)$himasi) ?>
          ];
          var lg = window.matchMedia('(min-width: 992px)').matches;
          new Chart(el, {
            type: 'doughnut',
            data: {
              labels: ['Berita','Kegiatan','Galeri','Feedback','BPH','HIMASI'],
              datasets: [{
                data: dataCounts,
                backgroundColor: ['#0d6efd','#198754','#6f42c1','#ffc107','#0ea5e9','#ef4444'],
                borderWidth: 0
              }]
            },
            options: {
              responsive: true,
              maintainAspectRatio: true,
              aspectRatio: 2.2,
              layout: { padding: 6 },
              cutout: '62%',
              plugins: {
                legend: { position: lg ? 'right' : 'bottom' }
              }
            }
          });
        })();
      </script>
    <?php endif; ?>

    <?php if ($tab==='reports'): ?>
      <div class="card">
        <div class="card-body">
          <h2 class="h5 mb-3">Cetak Laporan</h2>
          <div class="row g-2">
            <div class="col-12 col-md-6 col-xl-4"><a class="btn btn-outline-primary w-100" target="_blank" href="/hm/admin/print.php?type=berita">📰 Cetak Berita</a></div>
            <div class="col-12 col-md-6 col-xl-4"><a class="btn btn-outline-primary w-100" target="_blank" href="/hm/admin/print.php?type=pengumuman">📢 Cetak Kegiatan</a></div>
            <div class="col-12 col-md-6 col-xl-4"><a class="btn btn-outline-primary w-100" target="_blank" href="/hm/admin/print.php?type=bph">👥 Cetak Anggota BPH</a></div>
            <div class="col-12 col-md-6 col-xl-4"><a class="btn btn-outline-primary w-100" target="_blank" href="/hm/admin/print.php?type=himasi">👤 Cetak Anggota HIMASI</a></div>
            <div class="col-12 col-md-6 col-xl-4"><a class="btn btn-outline-primary w-100" target="_blank" href="/hm/admin/print.php?type=galeri">🖼️ Cetak Galeri</a></div>
            <div class="col-12 col-md-6 col-xl-4"><a class="btn btn-outline-primary w-100" target="_blank" href="/hm/admin/print.php?type=feedback">💬 Cetak Feedback</a></div>
            <div class="col-12 col-md-6 col-xl-4"><a class="btn btn-outline-primary w-100" target="_blank" href="/hm/admin/print.php?type=election">🗳️ Cetak Data Pemilihan</a></div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($tab==='feedback'): ?>
      <div class="card mb-3"><div class="card-body">
        <h2 class="h5">Saran & Kritik</h2>
        <?php if (empty($feedback)): ?>
          <div class="text-muted small">Belum ada feedback.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead><tr><th style="width:80px;">ID</th><th>Nama</th><th>NIM</th><th>Pesan</th></tr></thead>
              <tbody>
                <?php foreach ($feedback as $f): ?>
                  <tr>
                    <td><?= (int)($f['id'] ?? 0) ?></td>
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

    <?php if ($tab==='berita'): ?>
      <div class="card mb-3"><div class="card-body">
        <h2 class="h5">Tambah Berita</h2>
        <form method="post" enctype="multipart/form-data" class="row g-2">
          <input type="hidden" name="action" value="add_berita">
          <div class="col-md-6"><input class="form-control" name="title" placeholder="Judul" required></div>
          <div class="col-md-3"><input class="form-control" type="date" name="date" required></div>
          <div class="col-md-3"><input class="form-control" name="author" placeholder="Author" required></div>
          <div class="col-12"><input class="form-control" type="file" name="image_file" accept="image/*"></div>
          <div class="col-12"><input class="form-control" name="excerpt" placeholder="Ringkasan"></div>
          <div class="col-12"><textarea class="form-control" name="content" rows="3" placeholder="Konten"></textarea></div>
          <div class="col-12"><button class="btn btn-primary">Simpan</button></div>
        </form>
      </div></div>
      <div class="card"><div class="card-body">
        <h2 class="h6">Daftar Berita</h2>
        <?php foreach ($berita as $b): ?>
          <div class="row g-2 align-items-center py-2 border-bottom small">
            <div class="col-md-4"><div class="form-control form-control-sm bg-light"><?= htmlspecialchars($b['title']) ?></div></div>
            <div class="col-md-2"><div class="form-control form-control-sm bg-light"><?= htmlspecialchars($b['date']) ?></div></div>
            <div class="col-md-2"><div class="form-control form-control-sm bg-light"><?= htmlspecialchars($b['author'] ?? '') ?></div></div>
            <div class="col-md-2 d-none d-md-block"><div class="form-control form-control-sm bg-light" title="Ringkasan" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">&nbsp;<?= htmlspecialchars($b['excerpt'] ?? '') ?></div></div>
            <div class="col-md-2 d-flex justify-content-end gap-2">
              <a class="btn btn-primary btn-sm" href="/hm/admin/berita_edit.php?id=<?= (int)$b['id'] ?>">Edit</a>
              <button form="del-berita-<?= (int)$b['id'] ?>" class="btn btn-outline-primary btn-sm">Hapus</button>
            </div>
          </div>
          <form id="del-berita-<?= (int)$b['id'] ?>" method="post" class="d-none">
            <input type="hidden" name="action" value="delete_berita">
            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
          </form>
        <?php endforeach; ?>
      </div></div>
    <?php endif; ?>

    <?php if ($tab==='pengumuman'): ?>
      <div class="card mb-3"><div class="card-body">
        <h2 class="h5">Tambah Kegiatan</h2>
        <form method="post" class="row g-2">
          <input type="hidden" name="action" value="add_pengumuman">
          <div class="col-md-6"><input class="form-control" name="title" placeholder="Judul" required></div>
          <div class="col-md-3"><input class="form-control" type="date" name="date" required></div>
          <div class="col-md-3"><input class="form-control" name="link" placeholder="Tautan opsional"></div>
          <div class="col-12"><input class="form-control" name="excerpt" placeholder="Ringkasan"></div>
          <div class="col-12"><textarea class="form-control" name="content" rows="3" placeholder="Konten"></textarea></div>
          <div class="col-12"><button class="btn btn-primary">Simpan</button></div>
        </form>
      </div></div>
      <div class="card"><div class="card-body">
        <h2 class="h6">Daftar Kegiatan</h2>
        <?php foreach ($pengumuman as $p): ?>
          <div class="row g-2 align-items-center py-2 border-bottom small">
            <div class="col-md-4"><div class="form-control form-control-sm bg-light"><?= htmlspecialchars($p['title']) ?></div></div>
            <div class="col-md-2"><div class="form-control form-control-sm bg-light"><?= htmlspecialchars($p['date']) ?></div></div>
            <div class="col-md-2 d-none d-md-block"><div class="form-control form-control-sm bg-light" title="Tautan" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">&nbsp;<?= htmlspecialchars($p['link'] ?? '') ?></div></div>
            <div class="col-md-2 d-none d-md-block"><div class="form-control form-control-sm bg-light" title="Ringkasan" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">&nbsp;<?= htmlspecialchars($p['excerpt'] ?? '') ?></div></div>
            <div class="col-md-2 d-flex justify-content-end gap-2">
              <a class="btn btn-primary btn-sm" href="/hm/admin/pengumuman_edit.php?id=<?= (int)$p['id'] ?>">Edit</a>
              <button form="del-peng-<?= (int)$p['id'] ?>" class="btn btn-outline-primary btn-sm">Hapus</button>
            </div>
          </div>
          <form id="del-peng-<?= (int)$p['id'] ?>" method="post" class="d-none">
            <input type="hidden" name="action" value="delete_pengumuman">
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
          </form>
        <?php endforeach; ?>
      </div></div>
    <?php endif; ?>

    <?php if ($tab==='bph'): ?>
      <div class="card mb-3"><div class="card-body">
        <h2 class="h5">Tambah Anggota</h2>
        <form method="post" enctype="multipart/form-data" class="row g-2">
          <input type="hidden" name="action" value="add_bph">
          <div class="col-md-6"><input class="form-control" name="name" placeholder="Nama" required></div>
          <div class="col-md-6">
            <select class="form-select" name="position" required>
              <option value="">Pilih Jabatan...</option>
              <option>Ketua</option>
              <option>Wakil</option>
              <option>Sekretaris 1</option>
              <option>Sekretaris 2</option>
              <option>Bendahara 1</option>
              <option>Bendahara 2</option>
              <option>Departemen Sains Dan Teknologi</option>
              <option>Departemen Humas</option>
              <option>Departemen Olahraga</option>
              <option>Departemen Kerohanian</option>
              <option>Departemen Kominfo</option>
              <option>Departemen Multimedia</option>
            </select>
          </div>
          <div class="col-md-6"><input class="form-control" type="file" name="bph_photo" accept="image/*"></div>
          <div class="col-md-6"><input class="form-control" name="contact" placeholder="Kontak/Email"></div>
          <div class="col-12"><button class="btn btn-primary">Simpan</button></div>
        </form>
      </div></div>
      <div class="card"><div class="card-body">
        <h2 class="h6">Daftar</h2>
        <?php foreach ($bph as $m): ?>
          <div class="row g-2 align-items-center py-2 border-bottom small">
            <div class="col-md-3"><div class="form-control form-control-sm bg-light"><?= htmlspecialchars($m['name']) ?></div></div>
            <div class="col-md-3"><div class="form-control form-control-sm bg-light"><?= htmlspecialchars($m['position']) ?></div></div>
            <div class="col-md-3"><div class="form-control form-control-sm bg-light"><?= htmlspecialchars($m['contact'] ?? '') ?></div></div>
            <div class="col-md-3 d-flex justify-content-end gap-2">
              <a class="btn btn-primary btn-sm" href="/hm/admin/bph_edit.php?name=<?= urlencode($m['name']) ?>">Edit</a>
              <button form="del-bph-<?= md5($m['name']) ?>" class="btn btn-outline-primary btn-sm">Hapus</button>
            </div>
          </div>
          <form id="del-bph-<?= md5($m['name']) ?>" method="post" class="d-none">
            <input type="hidden" name="action" value="delete_bph">
            <input type="hidden" name="name" value="<?= htmlspecialchars($m['name']) ?>">
          </form>
        <?php endforeach; ?>
      </div></div>
    <?php endif; ?>

    <?php if ($tab==='himasi'): ?>
      <div class="card mb-3"><div class="card-body">
        <h2 class="h5">Tambah Anggota HIMASI</h2>
        <form method="post" class="row g-2">
          <input type="hidden" name="action" value="add_himasi">
          <div class="col-md-6"><input class="form-control" name="name" placeholder="Nama" required></div>
          <div class="col-md-6">
            <select class="form-select" name="bagian">
              <option value="">Pilih Bagian...</option>
              <?php foreach (($himasi_bagian ?? []) as $bopt): ?>
                <option value="<?= htmlspecialchars($bopt) ?>"><?= htmlspecialchars($bopt) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12"><button class="btn btn-primary">Simpan</button></div>
        </form>
      </div></div>
      <div class="card"><div class="card-body">
        <h2 class="h6">Daftar Anggota HIMASI</h2>
        <?php foreach ($himasi as $m2): ?>
          <div class="row g-2 align-items-center py-2 border-bottom small">
            <div class="col-md-6"><div class="form-control form-control-sm bg-light"><?= htmlspecialchars($m2['name']) ?></div></div>
            <div class="col-md-4"><div class="form-control form-control-sm bg-light"><?= htmlspecialchars($m2['bagian'] ?? '') ?></div></div>
            <div class="col-md-2 d-flex justify-content-end gap-2">
              <a class="btn btn-primary btn-sm" href="/hm/admin/himasi_edit.php?name=<?= urlencode($m2['name']) ?>">Edit</a>
              <button form="del-himasi-<?= md5($m2['name']) ?>" class="btn btn-outline-primary btn-sm">Hapus</button>
            </div>
          </div>
          <form id="del-himasi-<?= md5($m2['name']) ?>" method="post" class="d-none">
            <input type="hidden" name="action" value="delete_himasi">
            <input type="hidden" name="name" value="<?= htmlspecialchars($m2['name']) ?>">
          </form>
        <?php endforeach; ?>
        <div class="mt-4 pt-3 border-top">
          <h3 class="h6">Kelola Bagian HIMASI</h3>
          <form method="post" class="row g-2 align-items-end">
            <input type="hidden" name="action" value="add_himasi_bagian">
            <div class="col-md-6"><input class="form-control" name="name" placeholder="Nama Bagian baru" required></div>
            <div class="col-md-3"><button class="btn btn-outline-primary">Tambah Bagian</button></div>
          </form>
          <div class="small text-muted mt-2 mb-2">Daftar Bagian</div>
          <?php foreach (($himasi_bagian ?? []) as $bx): ?>
            <form method="post" class="d-flex align-items-center gap-2 py-1 border-bottom">
              <input type="hidden" name="action" value="delete_himasi_bagian">
              <input type="hidden" name="name" value="<?= htmlspecialchars($bx) ?>">
              <div class="flex-grow-1 small"><?= htmlspecialchars($bx) ?></div>
              <button class="btn btn-outline-primary btn-sm">Hapus</button>
            </form>
          <?php endforeach; ?>
        </div>
        
      </div></div>
    <?php endif; ?>

    <?php if ($tab==='galeri'): ?>
      <div class="card mb-3"><div class="card-body">
        <h2 class="h5">Tambah Foto Galeri</h2>
        <form method="post" enctype="multipart/form-data" class="row g-2">
          <input type="hidden" name="action" value="add_galeri">
          <div class="col-md-8"><input class="form-control" type="file" name="galeri_images[]" accept="image/*" multiple required></div>
          <div class="col-md-4"><input class="form-control" name="caption" placeholder="Caption (opsional, diterapkan ke semua)"></div>
          <div class="col-12"><button class="btn btn-primary">Unggah</button></div>
        </form>
      </div></div>
      <div class="card"><div class="card-body">
        <h2 class="h6">Daftar Galeri</h2>
        <?php foreach ($galeri as $g): ?>
          <div class="row g-2 align-items-center py-2 border-bottom small">
            <div class="col-md-6"><div class="form-control form-control-sm bg-light" title="Caption" style="white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">&nbsp;<?= htmlspecialchars($g['caption'] ?? '') ?></div></div>
            <div class="col-md-2"><img src="<?= htmlspecialchars($g['image']) ?>" alt="" class="img-fluid rounded" style="max-height:40px; object-fit:cover;"></div>
            <div class="col-md-4 d-flex justify-content-end gap-2">
              <a class="btn btn-primary btn-sm" href="/hm/admin/galeri_edit.php?image=<?= urlencode($g['image']) ?>">Edit</a>
              <button form="del-gal-<?= md5($g['image']) ?>" class="btn btn-outline-primary btn-sm">Hapus</button>
            </div>
          </div>
          <form id="del-gal-<?= md5($g['image']) ?>" method="post" class="d-none">
            <input type="hidden" name="action" value="delete_galeri">
            <input type="hidden" name="image" value="<?= htmlspecialchars($g['image']) ?>">
          </form>
        <?php endforeach; ?>
      </div></div>
    <?php endif; ?>

    <?php if ($tab==='election'): ?>
      <div class="card mb-2"><div class="card-body">
        <h2 class="h6">Statistik Suara (Pasangan)</h2>
        <div class="row g-4">
          <div class="col-12">
            <canvas id="chartPairs" height="130"></canvas>
          </div>
        </div>
        <form method="post" class="mt-3">
          <input type="hidden" name="action" value="reset_votes">
          <button class="btn btn-outline-primary">Reset Semua Suara</button>
        </form>
      </div></div>

      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <script>
        (function(){
          const pairs = <?= json_encode(array_values($candidates['pairs'] ?? [])) ?>;
          const totalsPairs = <?= json_encode($votes['totals']['pairs'] ?? []) ?>;

          const labels = [];
          const data = [];
          pairs.forEach((p, idx) => {
            const nomor = idx + 1;
            labels.push('Calon ' + nomor + ' ( ' + (p.ketua_name || 'Ketua') + ' & ' + (p.wakil_name || 'Wakil') + ' )');
            const key = String(p.id);
            data.push(totalsPairs && totalsPairs[key] ? parseInt(totalsPairs[key], 10) : 0);
          });

          const ctx = document.getElementById('chartPairs');
          if (ctx) {
            new Chart(ctx, {
              type: 'bar',
              data: {
                labels: labels,
                datasets: [{
                  label: 'Jumlah Suara',
                  data: data,
                  backgroundColor: 'rgba(54, 162, 235, 0.7)',
                  borderWidth: 0,
                  maxBarThickness: 24,
                  barPercentage: 0.7,
                  categoryPercentage: 0.8
                }]
              },
              options: {
                responsive: true,
                maintainAspectRatio: true,
                devicePixelRatio: 1,
                animation: false,
                scales: {
                  y: { beginAtZero: true, precision: 0, ticks: { stepSize: 1, font: { size: 10 } } },
                  x: { ticks: { autoSkip: true, maxRotation: 0, font: { size: 10 } } }
                },
                plugins: { legend: { display: false } }
              }
            });
          }
        })();
      </script>

      <div class="card mt-3"><div class="card-body">
        <h2 class="h5">Kelola Pasangan Calon</h2>
        <form method="post" enctype="multipart/form-data" class="row g-2 align-items-end">
          <input type="hidden" name="action" value="add_pair">
          <div class="col-12 col-md-6">
            <div class="fw-semibold small mb-1">Calon Ketua</div>
            <input class="form-control mb-1" name="ketua_name" placeholder="Nama Ketua" required>
            <input class="form-control mb-1" name="ketua_nim" placeholder="NIM Ketua" required>
            <input class="form-control" type="file" name="ketua_photo" accept="image/*">
          </div>
          <div class="col-12 col-md-6">
            <div class="fw-semibold small mb-1">Calon Wakil</div>
            <input class="form-control mb-1" name="wakil_name" placeholder="Nama Wakil" required>
            <input class="form-control mb-1" name="wakil_nim" placeholder="NIM Wakil" required>
            <input class="form-control" type="file" name="wakil_photo" accept="image/*">
          </div>
          <div class="col-12 mt-2"><button class="btn btn-primary">Tambah Pasangan</button></div>
        </form>

        <div class="mt-3">
          <div class="fw-semibold mb-2">Daftar Pasangan</div>
          <?php $idx=1; foreach (($candidates['pairs'] ?? []) as $p): ?>
            <?php
              $kid = (int)($p['ketua_id'] ?? 0); $wid = (int)($p['wakil_id'] ?? 0);
              $ketua = null; foreach (($candidates['ketua'] ?? []) as $kc) { if ((int)$kc['id'] === $kid) { $ketua = $kc; break; } }
              $wakil = null; foreach (($candidates['wakil'] ?? []) as $wc) { if ((int)$wc['id'] === $wid) { $wakil = $wc; break; } }
              $collapseId = 'editPair' . (int)$p['id'];
            ?>
            <div class="py-2 border-bottom">
              <div class="d-flex align-items-center gap-2">
                <div class="small flex-grow-1">Calon <?= $idx ?>: <?= htmlspecialchars($p['ketua_name']) ?> &amp; <?= htmlspecialchars($p['wakil_name']) ?></div>
                <a class="btn btn-primary btn-sm" href="/hm/admin/pair_edit.php?id=<?= (int)$p['id'] ?>">Edit</a>
                <form method="post" class="d-inline">
                  <input type="hidden" name="action" value="delete_pair">
                  <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                  <button class="btn btn-outline-primary btn-sm" onclick="return confirm('Hapus pasangan ini?');">Hapus</button>
                </form>
              </div>
            </div>
          <?php $idx++; endforeach; ?>
        </div>
      </div></div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
