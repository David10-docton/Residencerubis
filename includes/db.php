<?php
require_once __DIR__ . '/security.php';

/**
 * Charge le fichier .env (racine du projet) s'il existe :
 * permet de ne jamais versionner les identifiants de connexion.
 */
load_env_file(__DIR__ . '/../.env');

function db_connect() {
  static $conn = null;
  if ($conn instanceof mysqli) return $conn;

  // Identifiants MySQL. En local (WAMP) : valeurs par défaut ci-dessous.
  // En production (InfinityFree) : surchargez-les via le fichier .env
  // (DB_HOST, DB_USER, DB_PASS, DB_NAME) ou des variables d'environnement.
  $host = getenv('DB_HOST') ?: 'localhost';
  $user = getenv('DB_USER') ?: 'root';
  $pass = getenv('DB_PASS') ?: '';
  $db   = getenv('DB_NAME') ?: 'residencerubis';

  $conn = @new mysqli($host, $user, $pass);
  if ($conn->connect_error) {
    $conn = null;
    return null;
  }
  $conn->set_charset('utf8mb4');

  @$conn->query("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

  if (!$conn->select_db($db)) {
    $conn = null;
    return null;
  }

  $conn->query("CREATE TABLE IF NOT EXISTS site_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(50) NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    image_url TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_item (section, item_name)
  )");

  $conn->query("CREATE TABLE IF NOT EXISTS site_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(50) NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    price VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_item (section, item_name)
  )");

  $conn->query("CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    apartment VARCHAR(100) NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");

  $conn->query("CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");

  // NB : email en VARCHAR(190) — limite d'index InnoDB utf8mb4 (max 1000 octets).
  $conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    remember_token VARCHAR(64) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");

  return $conn;
}

function db_get_image($section, $item_name) {
  $conn = db_connect();
  if (!$conn) return null;
  $stmt = $conn->prepare("SELECT image_url FROM site_images WHERE section = ? AND item_name = ?");
  $stmt->bind_param('ss', $section, $item_name);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) return $row['image_url'];
  return null;
}

function db_set_image($section, $item_name, $image_url) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("INSERT INTO site_images (section, item_name, image_url) VALUES (?, ?, ?)
                          ON DUPLICATE KEY UPDATE image_url = ?");
  $stmt->bind_param('ssss', $section, $item_name, $image_url, $image_url);
  return $stmt->execute();
}

function db_delete_image($section, $item_name) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("DELETE FROM site_images WHERE section = ? AND item_name = ?");
  $stmt->bind_param('ss', $section, $item_name);
  return $stmt->execute();
}

function db_image_path_exists($url) {
  if ($url === '') return false;
  if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) return true;
  $path = strtok($url, '?');
  if (is_file($path)) return true;
  return is_file(__DIR__ . '/../' . $path);
}

function db_get_all_images() {
  $conn = db_connect();
  if (!$conn) return [];
  $res = $conn->query("SELECT section, item_name, image_url, updated_at FROM site_images");
  $rows = [];
  while ($row = $res->fetch_assoc()) $rows[] = $row;
  return $rows;
}

function db_get_price($section, $item_name) {
  $conn = db_connect();
  if (!$conn) return null;
  $stmt = $conn->prepare("SELECT price FROM site_prices WHERE section = ? AND item_name = ?");
  $stmt->bind_param('ss', $section, $item_name);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) return $row['price'];
  return null;
}

function db_set_price($section, $item_name, $price) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("INSERT INTO site_prices (section, item_name, price) VALUES (?, ?, ?)
                          ON DUPLICATE KEY UPDATE price = ?");
  $stmt->bind_param('ssss', $section, $item_name, $price, $price);
  return $stmt->execute();
}

function db_delete_price($section, $item_name) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("DELETE FROM site_prices WHERE section = ? AND item_name = ?");
  $stmt->bind_param('ss', $section, $item_name);
  return $stmt->execute();
}

function db_get_all_prices() {
  $conn = db_connect();
  if (!$conn) return [];
  $res = $conn->query("SELECT section, item_name, price, updated_at FROM site_prices");
  $rows = [];
  while ($row = $res->fetch_assoc()) $rows[] = $row;
  return $rows;
}

function db_save_booking($apartment, $check_in, $check_out, $email) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("INSERT INTO bookings (apartment, check_in, check_out, email) VALUES (?, ?, ?, ?)");
  $stmt->bind_param('ssss', $apartment, $check_in, $check_out, $email);
  return $stmt->execute();
}

function db_save_contact_message($name, $email, $message) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
  $stmt->bind_param('sss', $name, $email, $message);
  return $stmt->execute();
}

function db_get_bookings() {
  $conn = db_connect();
  if (!$conn) return [];
  $res = $conn->query("SELECT apartment, check_in, check_out, email, created_at FROM bookings ORDER BY created_at DESC");
  $rows = [];
  while ($row = $res->fetch_assoc()) $rows[] = $row;
  return $rows;
}

function db_get_contact_messages() {
  $conn = db_connect();
  if (!$conn) return [];
  $res = $conn->query("SELECT name, email, message, created_at FROM contact_messages ORDER BY created_at DESC");
  $rows = [];
  while ($row = $res->fetch_assoc()) $rows[] = $row;
  return $rows;
}

/* ============================================================
 * Comptes clients (connexion / inscription)
 * ============================================================ */

function db_user_find_by_email($email) {
  $conn = db_connect();
  if (!$conn) return null;
  $stmt = $conn->prepare("SELECT id, name, email, password_hash, remember_token, created_at FROM users WHERE email = ?");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $res = $stmt->get_result();
  return $res->fetch_assoc();
}

function db_user_create($name, $email, $password_hash) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
  $stmt->bind_param('sss', $name, $email, $password_hash);
  return $stmt->execute();
}

function db_user_set_remember_token($user_id, $token) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
  $stmt->bind_param('si', $token, $user_id);
  return $stmt->execute();
}

function db_user_find_by_token($token) {
  $conn = db_connect();
  if (!$conn) return null;
  $stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE remember_token = ?");
  $stmt->bind_param('s', $token);
  $stmt->execute();
  $res = $stmt->get_result();
  return $res->fetch_assoc();
}

/* ============================================================
 * Cache des surcharges (images + prix)
 *
 * Les surcharges sont stockées dans un fichier JSON afin d'éviter
 * ~25 requêtes MySQL à chaque chargement de page. Le fichier est
 * invalidé par l'admin (actions.php) à chaque modification.
 * ============================================================ */

function db_cache_file() {
  return __DIR__ . '/cache/overrides.json';
}

/**
 * Lit le cache s'il existe et qu'il est assez récent (TTL par défaut : 10 min).
 */
function db_cache_read($ttl = 600) {
  $file = db_cache_file();
  if (!is_file($file) || (time() - filemtime($file)) > $ttl) return null;
  $data = json_decode((string)file_get_contents($file), true);
  if (!is_array($data) || !isset($data['images'], $data['prices'])) return null;
  return $data;
}

/**
 * Écrit (ou rafraîchit) le cache des surcharges. Échec silencieux :
 * le site continue de fonctionner même si le fichier n'est pas inscriptible.
 */
function db_cache_write($data) {
  $file = db_cache_file();
  $dir = dirname($file);
  if (!is_dir($dir)) {
    if (!@mkdir($dir, 0755, true)) return false;
  }
  return @file_put_contents($file, json_encode($data)) !== false;
}

/**
 * Invalide le cache (appelé par l'admin après chaque modification).
 */
function db_cache_invalidate() {
  $file = db_cache_file();
  if (is_file($file)) @unlink($file);
}

/**
 * Retourne les surcharges ['images' => [clé => url], 'prices' => [clé => prix]]
 * via le cache si possible, sinon par une requête à la base (puis mise en cache).
 * Retourne null si la base est injoignable : le site utilisera les valeurs par défaut.
 */
function db_get_overrides() {
  $cached = db_cache_read();
  if ($cached !== null) return $cached;

  $conn = db_connect();
  if (!$conn) {
    // Base indisponible : on retombe sur un cache périmé s'il existe.
    $file = db_cache_file();
    if (is_file($file)) {
      $data = json_decode((string)file_get_contents($file), true);
      if (is_array($data)) return $data;
    }
    return null;
  }

  $images = [];
  foreach (db_get_all_images() as $row) {
    $images[$row['section'] . '::' . $row['item_name']] = $row['image_url'];
  }

  $prices = [];
  foreach (db_get_all_prices() as $row) {
    $prices[$row['section'] . '::' . $row['item_name']] = $row['price'];
  }

  $data = ['images' => $images, 'prices' => $prices];
  db_cache_write($data);
  return $data;
}
