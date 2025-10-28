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

  $tab = $_GET['tab'] ?? 'berita';
  $msg = '';

  // Load all datasets
  $berita = read_json_local('berita.json', []);
  $pengumuman = read_json_local('pengumuman.json', []);
  $bph = read_json_local('bph.json', []);
  $galeri = read_json_local('galeri.json', []);
  $candidates = election_candidates();
  $votes = election_votes();

  // Handle actions
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Berita
    if ($action === 'add_berita') {
      $imgPath = handle_upload('image_file');
      $berita[] = [
        'id' => next_id($berita),
        'title' => trim($_POST['title'] ?? ''),
        'date' => trim($_POST['date'] ?? ''),
        'author' => trim($_POST['author'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'content' => trim($_POST['content'] ?? ''),
        'image' => $imgPath
      ];
      write_json_local('berita.json', $berita);
      $msg = 'Berita ditambahkan.'; $tab='berita';
    }
    if ($action === 'delete_berita') {
      $id = (int)($_POST['id'] ?? 0);
      $berita = array_values(array_filter($berita, fn($b) => (int)$b['id'] !== $id));
      write_json_local('berita.json', $berita);
      $msg = 'Berita dihapus.'; $tab='berita';
    }
    if ($action === 'update_berita') {
      $id = (int)($_POST['id'] ?? 0);
      foreach ($berita as &$b) {
        if ((int)$b['id'] === $id) {
          $b['title'] = trim($_POST['title'] ?? $b['title']);
          $b['date'] = trim($_POST['date'] ?? $b['date']);
          $b['author'] = trim($_POST['author'] ?? $b['author']);
          $b['excerpt'] = trim($_POST['excerpt'] ?? ($b['excerpt'] ?? ''));
          $b['content'] = trim($_POST['content'] ?? ($b['content'] ?? ''));
          $newImg = handle_upload('image_file');
          if ($newImg) { $b['image'] = $newImg; }
          break;
        }
      }
      unset($b);
      write_json_local('berita.json', $berita);
      $msg = 'Berita diperbarui.'; $tab='berita';
    }

    // Pengumuman
    if ($action === 'add_pengumuman') {
      $pengumuman[] = [
        'id' => next_id($pengumuman),
        'title' => trim($_POST['title'] ?? ''),
        'date' => trim($_POST['date'] ?? ''),
        'excerpt' => trim($_POST['excerpt'] ?? ''),
        'content' => trim($_POST['content'] ?? ''),
        'link' => trim($_POST['link'] ?? '')
      ];
      write_json_local('pengumuman.json', $pengumuman);
      $msg = 'Pengumuman ditambahkan.'; $tab='pengumuman';
    }
    if ($action === 'delete_pengumuman') {
      $id = (int)($_POST['id'] ?? 0);
      $pengumuman = array_values(array_filter($pengumuman, fn($p) => (int)$p['id'] !== $id));
      write_json_local('pengumuman.json', $pengumuman);
      $msg = 'Pengumuman dihapus.'; $tab='pengumuman';
    }
    if ($action === 'update_pengumuman') {
      $id = (int)($_POST['id'] ?? 0);
      foreach ($pengumuman as &$p) {
        if ((int)$p['id'] === $id) {
          $p['title'] = trim($_POST['title'] ?? $p['title']);
          $p['date'] = trim($_POST['date'] ?? $p['date']);
          $p['excerpt'] = trim($_POST['excerpt'] ?? ($p['excerpt'] ?? ''));
          $p['content'] = trim($_POST['content'] ?? ($p['content'] ?? ''));
          $p['link'] = trim($_POST['link'] ?? ($p['link'] ?? ''));
          break;
        }
      }
      unset($p);
      write_json_local('pengumuman.json', $pengumuman);
      $msg = 'Pengumuman diperbarui.'; $tab='pengumuman';
    }

    // BPH
    if ($action === 'add_bph') {
      $photoPath = handle_upload('bph_photo');
      $bph[] = [
        'name' => trim($_POST['name'] ?? ''),
        'position' => trim($_POST['position'] ?? ''),
        'photo' => $photoPath,
        'contact' => trim($_POST['contact'] ?? '')
      ];
      write_json_local('bph.json', $bph);
      $msg = 'Anggota BPH ditambahkan.'; $tab='bph';
    }
    if ($action === 'delete_bph') {
      $name = $_POST['name'] ?? '';
      $bph = array_values(array_filter($bph, fn($m) => $m['name'] !== $name));
      write_json_local('bph.json', $bph);
      $msg = 'Anggota BPH dihapus.'; $tab='bph';
    }
    if ($action === 'update_bph') {
      $original = $_POST['original_name'] ?? '';
      foreach ($bph as &$m) {
        if ($m['name'] === $original) {
          $m['name'] = trim($_POST['name'] ?? $m['name']);
          $m['position'] = trim($_POST['position'] ?? $m['position']);
          $m['contact'] = trim($_POST['contact'] ?? ($m['contact'] ?? ''));
          $newPhoto = handle_upload('bph_photo');
          if ($newPhoto) { $m['photo'] = $newPhoto; }
          break;
        }
      }
      unset($m);
      write_json_local('bph.json', $bph);
      $msg = 'Anggota BPH diperbarui.'; $tab='bph';
    }

    // Galeri
    if ($action === 'add_galeri') {
      $imgPath = handle_upload('galeri_image');
      $galeri[] = [ 'image' => $imgPath, 'caption' => trim($_POST['caption'] ?? '') ];
      write_json_local('galeri.json', $galeri);
      $msg = 'Item galeri ditambahkan.'; $tab='galeri';
    }
    if ($action === 'delete_galeri') {
      $image = $_POST['image'] ?? '';
      $galeri = array_values(array_filter($galeri, fn($g) => $g['image'] !== $image));
      write_json_local('galeri.json', $galeri);
      $msg = 'Item galeri dihapus.'; $tab='galeri';
    }
    if ($action === 'update_galeri') {
      $original = $_POST['original_image'] ?? '';
      foreach ($galeri as &$g) {
        if ($g['image'] === $original) {
          $g['caption'] = trim($_POST['caption'] ?? ($g['caption'] ?? ''));
          $newImg = handle_upload('galeri_image');
          if ($newImg) { $g['image'] = $newImg; }
          break;
        }
      }
      unset($g);
      write_json_local('galeri.json', $galeri);
      $msg = 'Item galeri diperbarui.'; $tab='galeri';
    }

    // Election: add pair
    if ($action === 'add_pair') {
      $ketuaPhoto = handle_upload('ketua_photo');
      $wakilPhoto = handle_upload('wakil_photo');
      $ketua = [
        'id' => next_id($candidates['ketua']),
        'name' => trim($_POST['ketua_name'] ?? ''),
        'nim' => trim($_POST['ketua_nim'] ?? ''),
        'photo' => $ketuaPhoto
      ];
      $wakil = [
        'id' => next_id($candidates['wakil']),
        'name' => trim($_POST['wakil_name'] ?? ''),
        'nim' => trim($_POST['wakil_nim'] ?? ''),
        'photo' => $wakilPhoto
      ];
      $candidates['ketua'][] = $ketua;
      $candidates['wakil'][] = $wakil;
      $pairs = $candidates['pairs'] ?? [];
      $pairs[] = [
        'id' => next_id($pairs),
        'ketua_id' => (int)$ketua['id'],
        'wakil_id' => (int)$wakil['id'],
        'ketua_name' => $ketua['name'],
        'wakil_name' => $wakil['name']
      ];
      $candidates['pairs'] = $pairs;
      election_candidates_save($candidates);
      $msg = 'Pasangan calon ditambahkan.'; $tab='election';
    }
    if ($action === 'delete_pair') {
      $id = (int)($_POST['id'] ?? 0);
      // find pair
      $pair = null;
      foreach (($candidates['pairs'] ?? []) as $p) { if ((int)$p['id'] === $id) { $pair = $p; break; } }
      // remove pair
      $candidates['pairs'] = array_values(array_filter(($candidates['pairs'] ?? []), fn($p) => (int)$p['id'] !== $id));
      // also remove corresponding ketua/wakil entries
      if ($pair) {
        $candidates['ketua'] = array_values(array_filter(($candidates['ketua'] ?? []), fn($c) => (int)$c['id'] !== (int)$pair['ketua_id']));
        $candidates['wakil'] = array_values(array_filter(($candidates['wakil'] ?? []), fn($c) => (int)$c['id'] !== (int)$pair['wakil_id']));
      }
      election_candidates_save($candidates);
      $msg = 'Pasangan calon dihapus.'; $tab='election';
    }

    // Reset votes (include pairs)
    if ($action === 'reset_votes') {
      $votes = [ 'records' => [], 'totals' => [ 'ketua' => [], 'wakil' => [], 'pairs' => [] ] ];
      election_votes_save($votes);
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
    <div class="list-group">
      <a href="?tab=berita" class="list-group-item list-group-item-action <?= $tab==='berita'?'active':'' ?>">Berita</a>
      <a href="?tab=pengumuman" class="list-group-item list-group-item-action <?= $tab==='pengumuman'?'active':'' ?>">Pengumuman</a>
      <a href="?tab=bph" class="list-group-item list-group-item-action <?= $tab==='bph'?'active':'' ?>">Anggota BPH</a>
      <a href="?tab=galeri" class="list-group-item list-group-item-action <?= $tab==='galeri'?'active':'' ?>">Galeri</a>
      <a href="?tab=election" class="list-group-item list-group-item-action <?= $tab==='election'?'active':'' ?>">Pemilihan</a>
    </div>
  </div>
  <div class="col-lg-9">
    <?php if ($msg): ?><div class="alert alert-success py-2 mb-3"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

    <?php if ($tab==='berita'): ?>
      <div class="card mb-3"><div class="card-body">
        <h2 class="h5">Tambah Berita</h2>
        <form method="post" enctype="multipart/form-data" class="row g-2">
          <input type="hidden" name="action" value="add_berita">
          <div class="col-md-6"><input class="form-control" name="title" placeholder="Judul" required></div>
          <div class="col-md-3"><input class="form-control" name="date" placeholder="YYYY-MM-DD" required></div>
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
          <form method="post" enctype="multipart/form-data" class="row g-2 align-items-end py-2 border-bottom">
            <input type="hidden" name="action" value="update_berita">
            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <div class="col-md-4"><input class="form-control form-control-sm" name="title" value="<?= htmlspecialchars($b['title']) ?>" placeholder="Judul"></div>
            <div class="col-md-2"><input class="form-control form-control-sm" name="date" value="<?= htmlspecialchars($b['date']) ?>" placeholder="YYYY-MM-DD"></div>
            <div class="col-md-2"><input class="form-control form-control-sm" name="author" value="<?= htmlspecialchars($b['author'] ?? '') ?>" placeholder="Author"></div>
            <div class="col-md-4"><input class="form-control form-control-sm" name="excerpt" value="<?= htmlspecialchars($b['excerpt'] ?? '') ?>" placeholder="Ringkasan"></div>
            <div class="col-12"><textarea class="form-control form-control-sm" name="content" rows="2" placeholder="Konten"><?= htmlspecialchars($b['content'] ?? '') ?></textarea></div>
            <div class="col-md-6"><input class="form-control form-control-sm" type="file" name="image_file" accept="image/*"></div>
            <div class="col-md-6 d-flex justify-content-end gap-2">
              <button class="btn btn-primary btn-sm">Simpan</button>
              <button form="del-berita-<?= (int)$b['id'] ?>" class="btn btn-outline-primary btn-sm">Hapus</button>
            </div>
          </form>
          <form id="del-berita-<?= (int)$b['id'] ?>" method="post" class="d-none">
            <input type="hidden" name="action" value="delete_berita">
            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
          </form>
        <?php endforeach; ?>
      </div></div>
    <?php endif; ?>

    <?php if ($tab==='pengumuman'): ?>
      <div class="card mb-3"><div class="card-body">
        <h2 class="h5">Tambah Pengumuman</h2>
        <form method="post" class="row g-2">
          <input type="hidden" name="action" value="add_pengumuman">
          <div class="col-md-6"><input class="form-control" name="title" placeholder="Judul" required></div>
          <div class="col-md-3"><input class="form-control" name="date" placeholder="YYYY-MM-DD" required></div>
          <div class="col-md-3"><input class="form-control" name="link" placeholder="Tautan opsional"></div>
          <div class="col-12"><input class="form-control" name="excerpt" placeholder="Ringkasan"></div>
          <div class="col-12"><textarea class="form-control" name="content" rows="3" placeholder="Konten"></textarea></div>
          <div class="col-12"><button class="btn btn-primary">Simpan</button></div>
        </form>
      </div></div>
      <div class="card"><div class="card-body">
        <h2 class="h6">Daftar Pengumuman</h2>
        <?php foreach ($pengumuman as $p): ?>
          <form method="post" class="row g-2 align-items-end py-2 border-bottom">
            <input type="hidden" name="action" value="update_pengumuman">
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <div class="col-md-4"><input class="form-control form-control-sm" name="title" value="<?= htmlspecialchars($p['title']) ?>" placeholder="Judul"></div>
            <div class="col-md-2"><input class="form-control form-control-sm" name="date" value="<?= htmlspecialchars($p['date']) ?>" placeholder="YYYY-MM-DD"></div>
            <div class="col-md-2"><input class="form-control form-control-sm" name="link" value="<?= htmlspecialchars($p['link'] ?? '') ?>" placeholder="Tautan"></div>
            <div class="col-md-4"><input class="form-control form-control-sm" name="excerpt" value="<?= htmlspecialchars($p['excerpt'] ?? '') ?>" placeholder="Ringkasan"></div>
            <div class="col-12"><textarea class="form-control form-control-sm" name="content" rows="2" placeholder="Konten"><?= htmlspecialchars($p['content'] ?? '') ?></textarea></div>
            <div class="col-12 d-flex justify-content-end gap-2">
              <button class="btn btn-primary btn-sm">Simpan</button>
              <button form="del-peng-<?= (int)$p['id'] ?>" class="btn btn-outline-primary btn-sm">Hapus</button>
            </div>
          </form>
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
          <div class="col-md-6"><input class="form-control" name="position" placeholder="Jabatan" required></div>
          <div class="col-md-6"><input class="form-control" type="file" name="bph_photo" accept="image/*"></div>
          <div class="col-md-6"><input class="form-control" name="contact" placeholder="Kontak/Email"></div>
          <div class="col-12"><button class="btn btn-primary">Simpan</button></div>
        </form>
      </div></div>
      <div class="card"><div class="card-body">
        <h2 class="h6">Daftar</h2>
        <?php foreach ($bph as $m): ?>
          <form method="post" enctype="multipart/form-data" class="row g-2 align-items-end py-2 border-bottom">
            <input type="hidden" name="action" value="update_bph">
            <input type="hidden" name="original_name" value="<?= htmlspecialchars($m['name']) ?>">
            <div class="col-md-3"><input class="form-control form-control-sm" name="name" value="<?= htmlspecialchars($m['name']) ?>" placeholder="Nama"></div>
            <div class="col-md-3"><input class="form-control form-control-sm" name="position" value="<?= htmlspecialchars($m['position']) ?>" placeholder="Jabatan"></div>
            <div class="col-md-3"><input class="form-control form-control-sm" name="contact" value="<?= htmlspecialchars($m['contact'] ?? '') ?>" placeholder="Kontak/Email"></div>
            <div class="col-md-3"><input class="form-control form-control-sm" type="file" name="bph_photo" accept="image/*"></div>
            <div class="col-12 d-flex justify-content-end gap-2">
              <button class="btn btn-primary btn-sm">Simpan</button>
              <button form="del-bph-<?= md5($m['name']) ?>" class="btn btn-outline-primary btn-sm">Hapus</button>
            </div>
          </form>
          <form id="del-bph-<?= md5($m['name']) ?>" method="post" class="d-none">
            <input type="hidden" name="action" value="delete_bph">
            <input type="hidden" name="name" value="<?= htmlspecialchars($m['name']) ?>">
          </form>
        <?php endforeach; ?>
      </div></div>
    <?php endif; ?>

    <?php if ($tab==='galeri'): ?>
      <div class="card mb-3"><div class="card-body">
        <h2 class="h5">Tambah Item Galeri</h2>
        <form method="post" enctype="multipart/form-data" class="row g-2">
          <input type="hidden" name="action" value="add_galeri">
          <div class="col-md-8"><input class="form-control" type="file" name="galeri_image" accept="image/*" required></div>
          <div class="col-md-4"><input class="form-control" name="caption" placeholder="Caption"></div>
          <div class="col-12"><button class="btn btn-primary">Simpan</button></div>
        </form>
      </div></div>
      <div class="card"><div class="card-body">
        <h2 class="h6">Daftar Galeri</h2>
        <?php foreach ($galeri as $g): ?>
          <form method="post" enctype="multipart/form-data" class="row g-2 align-items-end py-2 border-bottom">
            <input type="hidden" name="action" value="update_galeri">
            <input type="hidden" name="original_image" value="<?= htmlspecialchars($g['image']) ?>">
            <div class="col-md-6"><input class="form-control form-control-sm" name="caption" value="<?= htmlspecialchars($g['caption'] ?? '') ?>" placeholder="Caption"></div>
            <div class="col-md-4"><input class="form-control form-control-sm" type="file" name="galeri_image" accept="image/*"></div>
            <div class="col-md-2 d-flex justify-content-end gap-2">
              <button class="btn btn-primary btn-sm">Simpan</button>
              <button form="del-gal-<?= md5($g['image']) ?>" class="btn btn-outline-primary btn-sm">Hapus</button>
            </div>
          </form>
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
            <form method="post" class="d-flex align-items-center gap-2 py-1 border-bottom">
              <input type="hidden" name="action" value="delete_pair">
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <div class="small flex-grow-1">Calon <?= $idx ?>: <?= htmlspecialchars($p['ketua_name']) ?> &amp; <?= htmlspecialchars($p['wakil_name']) ?></div>
              <button class="btn btn-outline-primary btn-sm">Hapus</button>
            </form>
          <?php $idx++; endforeach; ?>
        </div>
      </div></div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
