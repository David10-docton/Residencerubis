<?php
require_once 'auth.php';

// Invalider le jeton CSRF avant de détruire la session
unset($_SESSION['csrf_token']);
session_regenerate_id(true);
session_destroy();

// Supprimer le cookie de session côté client
if (ini_get('session.use_cookies')) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params['path'], $params['domain'],
    $params['secure'], $params['httponly']
  );
}

header('Location: login.php');
exit;
