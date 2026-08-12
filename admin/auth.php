<?php
session_start();

define('ADMIN_USER', 'admin');
define('ADMIN_PASS_HASH', '$2y$10$BkNmkODhaIyFRCNMXZdexu2lCsaZgq/u.s/z1gerZdYKC/LgwxKBS');

function admin_is_logged_in() {
  return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function admin_require_login() {
  if (!admin_is_logged_in()) {
    header('Location: login.php');
    exit;
  }
}
