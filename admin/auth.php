<?php
// Démarrer la session seulement si elle n'est pas déjà active.
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../includes/security.php';

// Lire directement le fichier .env pour les identifiants admin,
// car les variables d'environnement système peuvent écraser
// les valeurs du .env (load_env_file ne les surcharge pas).
function _auth_read_env($path, $key, $default = '') {
  if (!is_file($path)) return $default;
  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if (!$lines) return $default;
  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    $pos = strpos($line, '=');
    if ($pos === false) continue;
    $k = trim(substr($line, 0, $pos));
    if ($k === $key) return trim(substr($line, $pos + 1));
  }
  return $default;
}

$_auth_env = __DIR__ . '/../.env';
define('ADMIN_USER', _auth_read_env($_auth_env, 'ADMIN_USER', 'savplus'));
define('ADMIN_PASS', _auth_read_env($_auth_env, 'ADMIN_PASS', 's@vplus'));
define('ADMIN_PASS_HASH', _auth_read_env($_auth_env, 'ADMIN_PASS_HASH', '') ?: password_hash(ADMIN_PASS, PASSWORD_BCRYPT));

function admin_is_logged_in() {
  return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function admin_require_login() {
  if (!admin_is_logged_in()) {
    header('Location: login.php');
    exit;
  }
}
