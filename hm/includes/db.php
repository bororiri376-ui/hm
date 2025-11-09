<?php
$__db = null;
function db() {
  global $__db;
  if ($__db) return $__db;
  $host = 'localhost';
  $db   = 'hm';
  $user = 'root';
  $pass = '';
  $charset = 'utf8mb4';
  $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
  $opts = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
  ];
  try {
    $__db = new PDO($dsn, $user, $pass, $opts);
  } catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Unknown database') !== false || $e->getCode() === 1049) {
      $dsnNoDb = "mysql:host=$host;charset=$charset";
      $tmp = new PDO($dsnNoDb, $user, $pass, $opts);
      $tmp->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
      $tmp = null;
      $__db = new PDO($dsn, $user, $pass, $opts);
    } else {
      throw $e;
    }
  }
  // Ensure tables exist (berita, pengumuman)
  $__db->exec("CREATE TABLE IF NOT EXISTS users (
    nim BIGINT UNSIGNED PRIMARY KEY,
    name VARCHAR(70) NOT NULL,
    password VARCHAR(6) NOT NULL,
    role ENUM('admin','ketua','student') NOT NULL DEFAULT 'student',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  $__db->exec("CREATE TABLE IF NOT EXISTS berita (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(60) NOT NULL,
    date DATE NOT NULL,
    author VARCHAR(20) DEFAULT NULL,
    excerpt TEXT DEFAULT NULL,
    content TEXT DEFAULT NULL,
    image VARCHAR(9) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  $__db->exec("CREATE TABLE IF NOT EXISTS pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(60) NOT NULL,
    date DATE NOT NULL,
    excerpt TEXT DEFAULT NULL,
    content TEXT DEFAULT NULL,
    link VARCHAR(100) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  // Other datasets
  $__db->exec("CREATE TABLE IF NOT EXISTS bph (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(50) NOT NULL,
    photo VARCHAR(9) DEFAULT NULL,
    contact VARCHAR(100) DEFAULT NULL,
    UNIQUE KEY unq_bph_name_position (name, position)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  $__db->exec("CREATE TABLE IF NOT EXISTS himasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    bagian VARCHAR(255) DEFAULT NULL,
    photo VARCHAR(512) DEFAULT NULL,
    contact VARCHAR(255) DEFAULT NULL,
    UNIQUE KEY unq_himasi_name (name)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  $__db->exec("CREATE TABLE IF NOT EXISTS himasi_bagian (
    name VARCHAR(255) PRIMARY KEY
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  $__db->exec("CREATE TABLE IF NOT EXISTS galeri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image VARCHAR(512) NOT NULL,
    caption TEXT DEFAULT NULL,
    UNIQUE KEY unq_galeri_image (image)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  $__db->exec("CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATETIME NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    nim VARCHAR(50) DEFAULT NULL,
    message TEXT NOT NULL,
    UNIQUE KEY unq_feedback_nim_msg_date (nim, message(191), date)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  // Election tables (preserve IDs from JSON)
  $__db->exec("CREATE TABLE IF NOT EXISTS election_ketua (
    id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    nim VARCHAR(50) DEFAULT NULL,
    photo VARCHAR(512) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  $__db->exec("CREATE TABLE IF NOT EXISTS election_wakil (
    id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    nim VARCHAR(50) DEFAULT NULL,
    photo VARCHAR(512) DEFAULT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  $__db->exec("CREATE TABLE IF NOT EXISTS election_pairs (
    id INT PRIMARY KEY,
    ketua_id INT NOT NULL,
    wakil_id INT NOT NULL,
    ketua_name VARCHAR(255) DEFAULT NULL,
    wakil_name VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_pairs_ketua FOREIGN KEY (ketua_id) REFERENCES election_ketua(id) ON DELETE CASCADE,
    CONSTRAINT fk_pairs_wakil FOREIGN KEY (wakil_id) REFERENCES election_wakil(id) ON DELETE CASCADE
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  $__db->exec("CREATE TABLE IF NOT EXISTS votes_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voter_nim VARCHAR(50) DEFAULT NULL,
    choice_type ENUM('ketua','wakil','pairs') NOT NULL,
    choice_id INT NOT NULL,
    created_at DATETIME NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  $__db->exec("CREATE TABLE IF NOT EXISTS votes_totals (
    choice_type ENUM('ketua','wakil','pairs') NOT NULL,
    choice_id INT NOT NULL,
    total INT NOT NULL DEFAULT 0,
    PRIMARY KEY (choice_type, choice_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
  return $__db;
}

function berita_all() {
  $stmt = db()->query("SELECT id, title, DATE_FORMAT(date, '%Y-%m-%d') AS date, author, excerpt, content, image FROM berita ORDER BY date DESC, id DESC");
  return $stmt->fetchAll();
}
function berita_insert($title,$date,$author,$excerpt,$content,$image) {
  $stmt = db()->prepare("INSERT INTO berita (title,date,author,excerpt,content,image) VALUES (?,?,?,?,?,?)");
  $stmt->execute([$title,$date,$author,$excerpt,$content,$image]);
}
function berita_update($id,$title,$date,$author,$excerpt,$content,$imageOrNull) {
  if ($imageOrNull) {
    $stmt = db()->prepare("UPDATE berita SET title=?, date=?, author=?, excerpt=?, content=?, image=? WHERE id=?");
    $stmt->execute([$title,$date,$author,$excerpt,$content,$imageOrNull,$id]);
  } else {
    $stmt = db()->prepare("UPDATE berita SET title=?, date=?, author=?, excerpt=?, content=? WHERE id=?");
    $stmt->execute([$title,$date,$author,$excerpt,$content,$id]);
  }
}
function berita_delete($id) {
  $stmt = db()->prepare("DELETE FROM berita WHERE id=?");
  $stmt->execute([$id]);
}

function pengumuman_all() {
  $stmt = db()->query("SELECT id, title, DATE_FORMAT(date, '%Y-%m-%d') AS date, excerpt, content, link FROM pengumuman ORDER BY date DESC, id DESC");
  return $stmt->fetchAll();
}
function pengumuman_insert($title,$date,$excerpt,$content,$link) {
  $stmt = db()->prepare("INSERT INTO pengumuman (title,date,excerpt,content,link) VALUES (?,?,?,?,?)");
  $stmt->execute([$title,$date,$excerpt,$content,$link]);
}
function pengumuman_update($id,$title,$date,$excerpt,$content,$link) {
  $stmt = db()->prepare("UPDATE pengumuman SET title=?, date=?, excerpt=?, content=?, link=? WHERE id=?");
  $stmt->execute([$title,$date,$excerpt,$content,$link,$id]);
}
function pengumuman_delete($id) {
  $stmt = db()->prepare("DELETE FROM pengumuman WHERE id=?");
  $stmt->execute([$id]);
}

// Minimal helpers for migration
function bph_insert_ignore($name,$position,$photo,$contact){
  $stmt = db()->prepare("INSERT IGNORE INTO bph (name,position,photo,contact) VALUES (?,?,?,?)");
  $stmt->execute([$name,$position,$photo,$contact]);
  return $stmt->rowCount() > 0;
}
function himasi_insert_ignore($name,$bagian){
  $stmt = db()->prepare("INSERT IGNORE INTO himasi (name,bagian) VALUES (?,?)");
  $stmt->execute([$name,$bagian]);
}
function himasi_bagian_insert_ignore($name){
  $stmt = db()->prepare("INSERT IGNORE INTO himasi_bagian (name) VALUES (?)");
  $stmt->execute([$name]);
}
function galeri_insert_ignore($image,$caption){
  $stmt = db()->prepare("INSERT IGNORE INTO galeri (image,caption) VALUES (?,?)");
  $stmt->execute([$image,$caption]);
}
function feedback_insert_ignore($date,$name,$nim,$message){
  $stmt = db()->prepare("INSERT IGNORE INTO feedback (date,name,nim,message) VALUES (?,?,?,?)");
  $stmt->execute([$date,$name,$nim,$message]);
}
function election_ketua_upsert($id,$name,$nim,$photo){
  $stmt = db()->prepare("INSERT INTO election_ketua (id,name,nim,photo) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name), nim=VALUES(nim), photo=VALUES(photo)");
  $stmt->execute([$id,$name,$nim,$photo]);
}
function election_wakil_upsert($id,$name,$nim,$photo){
  $stmt = db()->prepare("INSERT INTO election_wakil (id,name,nim,photo) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name), nim=VALUES(nim), photo=VALUES(photo)");
  $stmt->execute([$id,$name,$nim,$photo]);
}
function election_pair_upsert($id,$ketua_id,$wakil_id,$ketua_name,$wakil_name){
  $stmt = db()->prepare("INSERT INTO election_pairs (id,ketua_id,wakil_id,ketua_name,wakil_name) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE ketua_id=VALUES(ketua_id), wakil_id=VALUES(wakil_id), ketua_name=VALUES(ketua_name), wakil_name=VALUES(wakil_name)");
  $stmt->execute([$id,$ketua_id,$wakil_id,$ketua_name,$wakil_name]);
}
function votes_record_insert($voter_nim,$choice_type,$choice_id,$created_at){
  $stmt = db()->prepare("INSERT INTO votes_records (voter_nim,choice_type,choice_id,created_at) VALUES (?,?,?,?)");
  $stmt->execute([$voter_nim,$choice_type,$choice_id,$created_at]);
}
function votes_total_upsert($choice_type,$choice_id,$total){
  $stmt = db()->prepare("INSERT INTO votes_totals (choice_type,choice_id,total) VALUES (?,?,?) ON DUPLICATE KEY UPDATE total=VALUES(total)");
  $stmt->execute([$choice_type,$choice_id,$total]);
}

// Users runtime
function user_db_all(){
  $stmt = db()->query("SELECT nim, name, password, role FROM users ORDER BY name ASC");
  return $stmt->fetchAll();
}
function user_db_get($nim){
  $key = trim((string)$nim);
  $stmt = db()->prepare("SELECT nim, name, password, role FROM users WHERE nim = ? LIMIT 1");
  $stmt->execute([$key]);
  return $stmt->fetch();
}
function user_db_upsert($nim,$name,$password,$role){
  $stmt = db()->prepare("INSERT INTO users (nim,name,password,role) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name), password=VALUES(password), role=VALUES(role)");
  $stmt->execute([$nim,$name,$password,$role]);
}
function user_db_insert_if_not_exists($nim,$name,$password,$role){
  $stmt = db()->prepare("INSERT IGNORE INTO users (nim,name,password,role) VALUES (?,?,?,?)");
  $stmt->execute([$nim,$name,$password,$role]);
}

// BPH runtime
function bph_all(){
  $stmt = db()->query("SELECT name, position, photo, contact FROM bph ORDER BY 
    (FIELD(position,
      'Ketua',
      'Wakil',
      'Sekretaris 1',
      'Sekretaris 2',
      'Bendahara 1',
      'Bendahara 2',
      'Departemen Sains Dan Teknologi',
      'Departemen Humas',
      'Departemen Olahraga',
      'Departemen Kerohanian',
      'Departemen Kominfo',
      'Departemen Multimedia'
    ) = 0),
    FIELD(position,
      'Ketua',
      'Wakil',
      'Sekretaris 1',
      'Sekretaris 2',
      'Bendahara 1',
      'Bendahara 2',
      'Departemen Sains Dan Teknologi',
      'Departemen Humas',
      'Departemen Olahraga',
      'Departemen Kerohanian',
      'Departemen Kominfo',
      'Departemen Multimedia'
    ),
    name ASC");
  return $stmt->fetchAll();
}
function bph_add($name,$position,$photo,$contact){ return bph_insert_ignore($name,$position,$photo,$contact); }
function bph_update_by_original($original_name,$name,$position,$photoOrNull,$contact){
  if ($photoOrNull) {
    $stmt = db()->prepare("UPDATE bph SET name=?, position=?, contact=?, photo=? WHERE name=?");
    $stmt->execute([$name,$position,$contact,$photoOrNull,$original_name]);
  } else {
    $stmt = db()->prepare("UPDATE bph SET name=?, position=?, contact=? WHERE name=?");
    $stmt->execute([$name,$position,$contact,$original_name]);
  }
}
function bph_delete_by_name($name){
  $stmt = db()->prepare("DELETE FROM bph WHERE name=?");
  $stmt->execute([$name]);
}

// HIMASI runtime
function himasi_all(){
  $stmt = db()->query("SELECT name, bagian FROM himasi ORDER BY name ASC");
  return $stmt->fetchAll();
}
function himasi_add($name,$bagian){ himasi_insert_ignore($name,$bagian); }
function himasi_update_by_original($original_name,$name,$bagian){
  $stmt = db()->prepare("UPDATE himasi SET name=?, bagian=? WHERE name=?");
  $stmt->execute([$name,$bagian,$original_name]);
}
function himasi_delete_by_name($name){
  // Delete from HIMASI first
  $stmt = db()->prepare("DELETE FROM himasi WHERE name=?");
  $stmt->execute([$name]);
  // Also delete corresponding user (student) with the same name to keep data in sync
  $stmt2 = db()->prepare("DELETE FROM users WHERE LOWER(TRIM(name)) = LOWER(TRIM(?)) AND role='student'");
  $stmt2->execute([$name]);
}
function himasi_bagian_all(){
  $stmt = db()->query("SELECT name FROM himasi_bagian ORDER BY name ASC");
  return array_map(fn($r)=>$r['name'],$stmt->fetchAll());
}
function himasi_bagian_delete($name){
  $stmt = db()->prepare("DELETE FROM himasi_bagian WHERE name=?");
  $stmt->execute([$name]);
}

// Check if a bagian already has at least one member (case-insensitive)
function himasi_exists_in_bagian($bagian){
  $stmt = db()->prepare("SELECT COUNT(*) AS c FROM himasi WHERE LOWER(TRIM(bagian)) = LOWER(TRIM(?))");
  $stmt->execute([$bagian]);
  $row = $stmt->fetch();
  return (int)($row['c'] ?? 0) > 0;
}

// Get first member by bagian (case-insensitive)
function himasi_get_by_bagian($bagian){
  $stmt = db()->prepare("SELECT name, bagian FROM himasi WHERE LOWER(TRIM(bagian)) = LOWER(TRIM(?)) LIMIT 1");
  $stmt->execute([$bagian]);
  return $stmt->fetch();
}

// Ketua guard helpers
function himasi_exists_ketua_any(){
  $stmt = db()->query("SELECT COUNT(*) AS c FROM himasi WHERE LOWER(TRIM(bagian)) LIKE 'ketua%'");
  $row = $stmt->fetch();
  return (int)($row['c'] ?? 0) > 0;
}
function himasi_get_first_ketua(){
  $stmt = db()->query("SELECT name, bagian FROM himasi WHERE LOWER(TRIM(bagian)) LIKE 'ketua%' LIMIT 1");
  return $stmt->fetch();
}

// Galeri runtime
function galeri_all(){
  $stmt = db()->query("SELECT image, caption FROM galeri ORDER BY id DESC");
  return $stmt->fetchAll();
}
function galeri_update_by_original($original_image,$caption,$newImageOrNull){
  if ($newImageOrNull) {
    $stmt = db()->prepare("UPDATE galeri SET image=?, caption=? WHERE image=?");
    $stmt->execute([$newImageOrNull,$caption,$original_image]);
  } else {
    $stmt = db()->prepare("UPDATE galeri SET caption=? WHERE image=?");
    $stmt->execute([$caption,$original_image]);
  }
}
function galeri_delete_by_image($image){
  $stmt = db()->prepare("DELETE FROM galeri WHERE image=?");
  $stmt->execute([$image]);
}

// Feedback runtime
function feedback_all(){
  $stmt = db()->query("SELECT id, DATE_FORMAT(date, '%Y-%m-%d %H:%i') AS date, name, nim, message FROM feedback ORDER BY id DESC");
  return $stmt->fetchAll();
}
function feedback_add($date,$name,$nim,$message){ feedback_insert_ignore($date,$name,$nim,$message); }
function feedback_delete($id){
  $stmt = db()->prepare("DELETE FROM feedback WHERE id=?");
  $stmt->execute([$id]);
}

// Election runtime
function election_next_id($table){
  $stmt = db()->query("SELECT COALESCE(MAX(id),0)+1 AS next_id FROM `".$table."`");
  return (int)($stmt->fetch()['next_id'] ?? 1);
}
function election_candidates_all(){
  $ketua = db()->query("SELECT id, name, nim, photo FROM election_ketua ORDER BY id ASC")->fetchAll();
  $wakil = db()->query("SELECT id, name, nim, photo FROM election_wakil ORDER BY id ASC")->fetchAll();
  $pairs = db()->query("SELECT id, ketua_id, wakil_id, ketua_name, wakil_name FROM election_pairs ORDER BY id ASC")->fetchAll();
  return ['ketua'=>$ketua,'wakil'=>$wakil,'pairs'=>$pairs];
}
function election_add_pair_db($ketua_name,$ketua_nim,$ketua_photo,$wakil_name,$wakil_nim,$wakil_photo){
  $kid = election_next_id('election_ketua');
  $wid = election_next_id('election_wakil');
  $pid = election_next_id('election_pairs');
  election_ketua_upsert($kid, $ketua_name, $ketua_nim, $ketua_photo);
  election_wakil_upsert($wid, $wakil_name, $wakil_nim, $wakil_photo);
  election_pair_upsert($pid, $kid, $wid, $ketua_name, $wakil_name);
}
function election_delete_pair_db($pair_id){
  $stmt = db()->prepare("SELECT ketua_id, wakil_id FROM election_pairs WHERE id=?");
  $stmt->execute([$pair_id]);
  $pair = $stmt->fetch();
  db()->prepare("DELETE FROM election_pairs WHERE id=?")->execute([$pair_id]);
  if ($pair) {
    db()->prepare("DELETE FROM election_ketua WHERE id=?")->execute([(int)$pair['ketua_id']]);
    db()->prepare("DELETE FROM election_wakil WHERE id=?")->execute([(int)$pair['wakil_id']]);
  }
}
function election_votes_all(){
  // totals keyed by id as array
  $totals = ['ketua'=>[], 'wakil'=>[], 'pairs'=>[]];
  $rows = db()->query("SELECT choice_type, choice_id, total FROM votes_totals")->fetchAll();
  foreach ($rows as $r) { $totals[$r['choice_type']][(string)$r['choice_id']] = (int)$r['total']; }
  $records = db()->query("SELECT voter_nim, choice_type, choice_id, DATE_FORMAT(created_at,'%Y-%m-%d %H:%i:%s') AS created_at FROM votes_records ORDER BY id ASC")->fetchAll();
  return ['records'=>$records, 'totals'=>$totals];
}
function election_votes_reset_db(){
  db()->exec("TRUNCATE TABLE votes_records");
  db()->exec("TRUNCATE TABLE votes_totals");
}
