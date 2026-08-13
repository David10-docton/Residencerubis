<?php
session_start();

// Identifiants admin. En production, définissez ADMIN_USER et
// ADMIN_PASS_HASH (hash bcrypt) dans le fichier .env à la racine —
// jamais dans un fichier versionné.
require_once __DIR__ . '/../includes/security.php';
load_env_file(__DIR__ . '/../.env');

define('ADMIN_USER', getenv('ADMIN_USER') ?: 'admin');
define('ADMIN_PASS_HASH', getenv('ADMIN_PASS_HASH') ?: '$2y$10$BkNmkODhaIyFRCNMXZdexu2lCsaZgq/u.s/z1gerZdYKC/LgwxKBS');

function admin_is_logged_in() {
  return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function admin_require_login() {
  if (!admin_is_logged_in()) {
    header('Location: login.php');
    exit;
  }
}
