<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';

$results = [];

function add_result(&$results, $key, $count){ $results[] = [$key, $count]; }

// Determine source directory for JSON files
$srcParam = isset($_GET['src']) ? trim($_GET['src']) : '';
$srcBase = $srcParam !== '' ? base_path($srcParam) : base_path('data');
if (!is_dir($srcBase)) { $srcBase = base_path('data'); }

function read_json_from($baseDir, $file, $default = []){
  $p = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
  if (!file_exists($p)) return $default;
  $raw = @file_get_contents($p);
  if ($raw === false) return $default;
  $data = json_decode($raw, true);
  return is_array($data) ? $data : $default;
}

// Berita
$berita = read_json_from($srcBase, 'berita.json', []);
$cnt = 0;
foreach ((array)$berita as $b) {
  $title = trim($b['title'] ?? '');
  $date = trim($b['date'] ?? '');
  $exists = db()->prepare("SELECT COUNT(*) FROM berita WHERE title=? AND date=?");
  $exists->execute([$title,$date]);
  if ($exists->fetchColumn() == 0) {
    berita_insert($title,$date,($b['author'] ?? ''),($b['excerpt'] ?? ''),($b['content'] ?? ''),($b['image'] ?? ''));
    $cnt++;
  }
}
add_result($results,'berita',$cnt);

// Pengumuman
$pengumuman = read_json_from($srcBase, 'pengumuman.json', []);
$cnt = 0;
foreach ((array)$pengumuman as $p) {
  $title = trim($p['title'] ?? '');
  $date = trim($p['date'] ?? '');
  $exists = db()->prepare("SELECT COUNT(*) FROM pengumuman WHERE title=? AND date=?");
  $exists->execute([$title,$date]);
  if ($exists->fetchColumn() == 0) {
    pengumuman_insert($title,$date,($p['excerpt'] ?? ''),($p['content'] ?? ''),($p['link'] ?? ''));
    $cnt++;
  }
}
add_result($results,'pengumuman',$cnt);

// Users
$users = read_json_from($srcBase, 'users.json', []);
$cnt = 0;
foreach ((array)$users as $u) {
  $nim = $u['nim'] ?? '';
  $name = $u['name'] ?? '';
  $password = $u['password'] ?? '';
  $role = $u['role'] ?? 'student';
  if ($nim && $name && $password) { user_db_insert_if_not_exists($nim,$name,$password,$role); $cnt++; }
}
// Seed default admin if none exists
$hasAdmin = db()->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
if ((int)$hasAdmin === 0) {
  // nim 'admin', pass 'admin' (plaintext for demo)
  user_db_insert_if_not_exists('admin','Administrator','admin','admin');
  $cnt++;
}
// Seed default ketua if none exists
$hasKetua = db()->query("SELECT COUNT(*) FROM users WHERE role='ketua'")->fetchColumn();
if ((int)$hasKetua === 0) {
  // nim 'ketua', pass 'ketua' (plaintext for demo)
  user_db_insert_if_not_exists('ketua','Ketua','ketua','ketua');
  $cnt++;
}
add_result($results,'users',$cnt);

// BPH
$bph = read_json_from($srcBase, 'bph.json', []);
$cnt = 0;
foreach ((array)$bph as $m) {
  bph_insert_ignore(($m['name'] ?? ''),($m['position'] ?? ''),($m['photo'] ?? ''),($m['contact'] ?? ''));
  $cnt++;
}
add_result($results,'bph',$cnt);

// HIMASI
$himasi = read_json_from($srcBase, 'himasi.json', []);
$cnt = 0;
foreach ((array)$himasi as $m) {
  himasi_insert_ignore(($m['name'] ?? ''),($m['bagian'] ?? ''),($m['photo'] ?? ''),($m['contact'] ?? ''));
  $cnt++;
}
add_result($results,'himasi',$cnt);

// HIMASI Bagian
$bagian = read_json_from($srcBase, 'himasi_bagian.json', []);
$cnt = 0;
foreach ((array)$bagian as $n) { if ($n !== '') { himasi_bagian_insert_ignore($n); $cnt++; } }
add_result($results,'himasi_bagian',$cnt);

// Galeri
$galeri = read_json_from($srcBase, 'galeri.json', []);
$cnt = 0;
foreach ((array)$galeri as $g) {
  galeri_insert_ignore(($g['image'] ?? ''),($g['caption'] ?? ''));
  $cnt++;
}
add_result($results,'galeri',$cnt);

// Feedback
$feedback = read_json_from($srcBase, 'feedback.json', []);
$cnt = 0;
foreach ((array)$feedback as $f) {
  $date = trim($f['date'] ?? '');
  if (strlen($date) == 16) { $date .= ':00'; }
  feedback_insert_ignore($date, ($f['name'] ?? ''), ($f['nim'] ?? ''), ($f['message'] ?? ''));
  $cnt++;
}
add_result($results,'feedback',$cnt);

// Election
$e = read_json_from($srcBase, 'election_candidates.json', ['ketua'=>[], 'wakil'=>[], 'pairs'=>[]]);
$cntKetua=0; $cntWakil=0; $cntPairs=0;
foreach ((array)($e['ketua'] ?? []) as $c){ election_ketua_upsert((int)($c['id'] ?? 0), ($c['name'] ?? ''), ($c['nim'] ?? ''), ($c['photo'] ?? '')); $cntKetua++; }
foreach ((array)($e['wakil'] ?? []) as $c){ election_wakil_upsert((int)($c['id'] ?? 0), ($c['name'] ?? ''), ($c['nim'] ?? ''), ($c['photo'] ?? '')); $cntWakil++; }
foreach ((array)($e['pairs'] ?? []) as $p){ election_pair_upsert((int)($p['id'] ?? 0),(int)($p['ketua_id'] ?? 0),(int)($p['wakil_id'] ?? 0),($p['ketua_name'] ?? ''),($p['wakil_name'] ?? '')); $cntPairs++; }
add_result($results,'election_ketua',$cntKetua);
add_result($results,'election_wakil',$cntWakil);
add_result($results,'election_pairs',$cntPairs);

// Votes
$v = read_json_from($srcBase, 'votes.json', ['records'=>[], 'totals'=>['ketua'=>[], 'wakil'=>[], 'pairs'=>[]]]);
$cntRec=0; $cntTot=0;
foreach ((array)($v['records'] ?? []) as $r) {
  $exists = db()->prepare("SELECT COUNT(*) FROM votes_records WHERE voter_nim<=>? AND choice_type=? AND choice_id=? AND created_at=?");
  $exists->execute([($r['voter_nim'] ?? null), ($r['choice_type'] ?? 'ketua'), (int)($r['choice_id'] ?? 0), ($r['created_at'] ?? date('Y-m-d H:i:s'))]);
  if ($exists->fetchColumn() == 0) {
    votes_record_insert(($r['voter_nim'] ?? null), ($r['choice_type'] ?? 'ketua'), (int)($r['choice_id'] ?? 0), ($r['created_at'] ?? date('Y-m-d H:i:s')));
    $cntRec++;
  }
}
foreach (['ketua','wakil','pairs'] as $t) {
  foreach ((array)($v['totals'][$t] ?? []) as $choice_id => $total) {
    votes_total_upsert($t, (int)$choice_id, (int)$total);
    $cntTot++;
  }
}
add_result($results,'votes_records',$cntRec);
add_result($results,'votes_totals',$cntTot);

header('Content-Type: text/html; charset=utf-8');
echo '<!doctype html><meta charset="utf-8"><title>Migrasi Data</title><div style="padding:20px;font-family:system-ui,Segoe UI,Arial">';
echo '<h1 style="font-size:18px;margin:0 0 12px">Migrasi Data</h1>';
echo '<div class="small" style="margin-bottom:8px">Sumber JSON: '.htmlspecialchars($srcBase).'</div>';
echo '<ul style="line-height:1.6">';
foreach ($results as $r) { echo '<li><strong>'.htmlspecialchars($r[0]).'</strong>: '.(int)$r[1].' item diproses</li>'; }
echo '</ul>';
echo '<a href="/hm/admin/" style="display:inline-block;margin-top:12px">Kembali ke Admin</a>';
echo '</div>';
