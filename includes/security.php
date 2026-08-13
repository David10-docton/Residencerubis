<?php
/**
 * Helpers de sécurité — Résidence Rubis
 *
 * - Chargeur de variables d'environnement (fichier .env, hors Git)
 * - Protection CSRF (jeton par session, hash_equals)
 * - Honeypot anti-robots (champ caché « website »)
 * - Limitation de débit côté session (anti-spam formulaire)
 */

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/**
 * Charge un fichier .env simple (KEY=VALUE, une ligne par variable).
 * Les variables déjà définies dans l'environnement ne sont pas écrasées.
 */
function load_env_file($path) {
  static $loaded = [];
  $real = realpath($path);
  if (!$real || isset($loaded[$real]) || !is_file($real)) return;
  $loaded[$real] = true;

  $lines = file($real, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if ($lines === false) return;

  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    $pos = strpos($line, '=');
    if ($pos === false) continue;
    $key = trim(substr($line, 0, $pos));
    $value = trim(substr($line, $pos + 1));
    if ($key === '' || getenv($key) !== false) continue;
    putenv($key . '=' . $value);
    $_ENV[$key] = $value;
  }
}

/**
 * Jeton CSRF unique pour la session courante.
 */
function csrf_token() {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

/**
 * Champ caché CSRF à insérer dans chaque formulaire POST.
 */
function csrf_field() {
  return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Vérifie le jeton CSRF d'un POST. Retourne true si valide.
 */
function csrf_verify() {
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') return true;
  $sent = $_POST['csrf_token'] ?? '';
  return is_string($sent) && $sent !== '' && hash_equals(csrf_token(), $sent);
}

/**
 * True si le piège anti-robot (champ caché « website ») a été rempli.
 * Les robots remplissent tous les champs visibles du DOM : on les détecte ici.
 */
function honeypot_filled() {
  return trim($_POST['website'] ?? '') !== '';
}

/**
 * Anti-spam : refuse plus d'un envoi de formulaire par intervalle (défaut 3 s).
 * Retourne true si l'envoi est trop rapide.
 */
function submission_too_fast($min_seconds = 3) {
  $now = microtime(true);
  $last = $_SESSION['last_submission'] ?? 0;
  if ($now - $last < $min_seconds) return true;
  $_SESSION['last_submission'] = $now;
  return false;
}
