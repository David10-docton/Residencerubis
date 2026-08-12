<?php
function db_connect() {
  static $conn = null;
  if ($conn instanceof mysqli) return $conn;

  // Identifiants MySQL. En local (WAMP) : valeurs par défaut ci-dessous.
  // En production (InfinityFree) : surchargez-les via des variables
  // d'environnement (DB_HOST, DB_USER, DB_PASS, DB_NAME).
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
