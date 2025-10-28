<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function base_path($path = '') {
  return __DIR__ . '/../' . ltrim($path, '/');
}

function read_json_assoc($file, $default = []) {
  $p = base_path('data/' . $file);
  if (!file_exists($p)) { return $default; }
  $raw = @file_get_contents($p);
  if ($raw === false) { return $default; }
  $data = json_decode($raw, true);
  return is_array($data) ? $data : $default;
}

function write_json_assoc($file, $data) {
  $p = base_path('data/' . $file);
  @file_put_contents($p, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function users_all() {
  return read_json_assoc('users.json', []);
}

function users_save($users) {
  write_json_assoc('users.json', $users);
}

function auth_login($nim, $password) {
  $users = users_all();
  foreach ($users as $u) {
    if (isset($u['nim']) && $u['nim'] === $nim) {
      // Plaintext password check for demo. Replace with password_verify for production.
      if (isset($u['password']) && $u['password'] === $password) {
        $_SESSION['user'] = [
          'nim' => $u['nim'],
          'name' => $u['name'],
          'role' => $u['role']
        ];
        return true;
      }
    }
  }
  return false;
}

function auth_logout() {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }
  session_destroy();
}

function auth_user() {
  return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function auth_check() {
  return auth_user() !== null;
}

function auth_is_role($role) {
  $u = auth_user();
  return $u && isset($u['role']) && $u['role'] === $role;
}

function auth_any_role($roles) {
  $u = auth_user();
  if (!$u) return false;
  return in_array($u['role'], (array)$roles, true);
}

function require_login() {
  if (!auth_check()) {
    header('Location: /hm/login.php');
    exit;
  }
}

function require_role($roles) {
  require_login();
  if (!auth_any_role((array)$roles)) {
    http_response_code(403);
    echo '<div style="padding:2rem;font-family:sans-serif">Akses ditolak.</div>';
    exit;
  }
}

// Election helpers
function election_candidates() {
  $data = read_json_assoc('election_candidates.json', [
    'ketua' => [],
    'wakil' => [],
    'pairs' => []
  ]);
  if (!isset($data['pairs']) || !is_array($data['pairs'])) { $data['pairs'] = []; }
  if (!isset($data['ketua']) || !is_array($data['ketua'])) { $data['ketua'] = []; }
  if (!isset($data['wakil']) || !is_array($data['wakil'])) { $data['wakil'] = []; }
  return $data;
}

function election_candidates_save($data) {
  write_json_assoc('election_candidates.json', $data);
}

function election_votes() {
  $data = read_json_assoc('votes.json', [
    'records' => [],
    'totals' => [ 'ketua' => [], 'wakil' => [], 'pairs' => [] ]
  ]);
  if (!isset($data['records']) || !is_array($data['records'])) { $data['records'] = []; }
  if (!isset($data['totals']) || !is_array($data['totals'])) { $data['totals'] = []; }
  if (!isset($data['totals']['ketua']) || !is_array($data['totals']['ketua'])) { $data['totals']['ketua'] = []; }
  if (!isset($data['totals']['wakil']) || !is_array($data['totals']['wakil'])) { $data['totals']['wakil'] = []; }
  if (!isset($data['totals']['pairs']) || !is_array($data['totals']['pairs'])) { $data['totals']['pairs'] = []; }
  return $data;
}

function election_votes_save($data) {
  write_json_assoc('votes.json', $data);
}
