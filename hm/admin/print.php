<?php
  require __DIR__ . '/../includes/auth.php';
  require_role(['admin']);
  require_once __DIR__ . '/../includes/db.php';

  $type = strtolower(trim($_GET['type'] ?? ''));
  $pageMap = [
    'berita' => 'Berita',
    'pengumuman' => 'Kegiatan',
    'bph' => 'Anggota BPH',
    'himasi' => 'Anggota HIMASI',
    'galeri' => 'Galeri',
    'feedback' => 'Saran & Kritik',
    'election' => 'Data Pemilihan',
  ];
  $title = $pageMap[$type] ?? 'Laporan';

  // Fetch data per type
  $data = [];
  $extra = [];
  switch ($type) {
    case 'berita':
      $data = berita_all();
      break;
    case 'pengumuman':
      $data = pengumuman_all();
      break;
    case 'bph':
      $data = bph_all();
      break;
    case 'himasi':
      $data = himasi_all();
      break;
    case 'galeri':
      $data = galeri_all();
      break;
    case 'feedback':
      $data = feedback_all();
      break;
    case 'election':
      $extra['candidates'] = election_candidates_all();
      $extra['votes'] = election_votes_all();
      break;
    default:
      $type = '';
  }
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cetak Laporan - <?= htmlspecialchars($title) ?></title>
  <style>
    :root { --ink:#111; --muted:#666; --border:#ddd; }
    body{ font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial; color:var(--ink); margin:24px; }
    h1{ font-size:20px; margin:0 0 12px; }
    .meta{ color:var(--muted); font-size:12px; margin-bottom:16px; }
    table{ width:100%; border-collapse:collapse; font-size:12px; }
    th,td{ border:1px solid var(--border); padding:8px; vertical-align:top; }
    th{ background:#f6f7f9; text-align:left; }
    .actions{ margin-bottom:16px; }
    .btn{ display:inline-block; padding:8px 12px; border:1px solid var(--border); background:#fff; border-radius:6px; text-decoration:none; color:#111; }
    .note{ color:var(--muted); font-size:11px; margin-top:8px; }
    @media print{
      .actions{ display:none; }
      body{ margin:0; }
      th,td{ padding:6px; }
    }
  </style>
</head>
<body>
  <div class="actions">
    <a class="btn" href="/hm/admin/?tab=reports">Kembali</a>
    <a class="btn" href="#" onclick="window.print(); return false;">Cetak</a>
  </div>
  <h1>Laporan <?= htmlspecialchars($title) ?></h1>
  <div class="meta">Tanggal cetak: <?= date('Y-m-d H:i') ?></div>

  <?php if ($type === 'berita'): ?>
    <table>
      <thead><tr><th>ID</th><th>Gambar</th><th>Judul</th><th>Tanggal</th><th>Author</th><th>Ringkasan</th></tr></thead>
      <tbody>
        <?php foreach (($data ?? []) as $r): ?>
          <tr>
            <td><?= (int)($r['id'] ?? 0) ?></td>
            <td><?php $img = trim((string)($r['image'] ?? '')); if ($img): ?><img src="<?= htmlspecialchars($img) ?>" alt="" style="max-height:60px; max-width:100px; object-fit:cover;" /><?php endif; ?></td>
            <td><?= htmlspecialchars($r['title'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['date'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['author'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['excerpt'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  <?php elseif ($type === 'pengumuman'): ?>
    <table>
      <thead><tr><th>ID</th><th>Judul</th><th>Tanggal</th><th>Link</th><th>Ringkasan</th></tr></thead>
      <tbody>
        <?php foreach (($data ?? []) as $r): ?>
          <tr>
            <td><?= (int)($r['id'] ?? 0) ?></td>
            <td><?= htmlspecialchars($r['title'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['date'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['link'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['excerpt'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  <?php elseif ($type === 'bph'): ?>
    <table>
      <thead><tr><th>Foto</th><th>Nama</th><th>Jabatan</th><th>Kontak</th></tr></thead>
      <tbody>
        <?php foreach (($data ?? []) as $r): ?>
          <tr>
            <td><?php $p = trim((string)($r['photo'] ?? '')); if ($p): ?><img src="<?= htmlspecialchars($p) ?>" alt="" style="max-height:60px; max-width:100px; object-fit:cover;" /><?php endif; ?></td>
            <td><?= htmlspecialchars($r['name'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['position'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['contact'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  <?php elseif ($type === 'himasi'): ?>
    <table>
      <thead><tr><th>Nama</th><th>Bagian</th></tr></thead>
      <tbody>
        <?php foreach (($data ?? []) as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['name'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['bagian'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  <?php elseif ($type === 'galeri'): ?>
    <table>
      <thead><tr><th>Gambar</th><th>URL</th><th>Caption</th></tr></thead>
      <tbody>
        <?php foreach (($data ?? []) as $r): ?>
          <tr>
            <td><?php $img = trim((string)($r['image'] ?? '')); if ($img): ?><img src="<?= htmlspecialchars($img) ?>" alt="" style="max-height:80px; max-width:120px; object-fit:cover;" /><?php endif; ?></td>
            <td><?= htmlspecialchars($r['image'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['caption'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  <?php elseif ($type === 'feedback'): ?>
    <table>
      <thead><tr><th>ID</th><th>Tanggal</th><th>Nama</th><th>NIM</th><th>Pesan</th></tr></thead>
      <tbody>
        <?php foreach (($data ?? []) as $r): ?>
          <tr>
            <td><?= (int)($r['id'] ?? 0) ?></td>
            <td><?= htmlspecialchars($r['date'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['name'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['nim'] ?? '') ?></td>
            <td><?= htmlspecialchars($r['message'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  <?php elseif ($type === 'election'): ?>
    <?php $c = $extra['candidates'] ?? []; $v = $extra['votes'] ?? []; ?>
    <?php 
      $pairs = array_values($c['pairs'] ?? []);
      usort($pairs, function($a,$b){ return (int)($a['id']??0) <=> (int)($b['id']??0); });
      $pairs = array_slice($pairs, 0, 2);
      $totals = $v['totals']['pairs'] ?? [];
    ?>
    <h2 style="font-size:16px; margin:16px 0 8px;">Rekap Suara (Calon 1 & Calon 2)</h2>
    <table>
      <thead><tr><th>Calon</th><th>Nama Pasangan</th><th>Total Suara</th></tr></thead>
      <tbody>
        <?php foreach ($pairs as $idx => $p): $pid=(int)($p['id'] ?? 0); $total=(int)($totals[(string)$pid] ?? 0); ?>
          <tr>
            <td><?= 'Calon ' . (int)($idx+1) ?></td>
            <td><?= htmlspecialchars(($p['ketua_name'] ?? '').' & '.($p['wakil_name'] ?? '')) ?></td>
            <td><?= $total ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  <?php else: ?>
    <div>Tipe laporan tidak dikenal.</div>
  <?php endif; ?>

  <div class="note">Dicetak dari Panel Admin HIMASI</div>
</body>
</html>
