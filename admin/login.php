<?php
require_once 'auth.php';
require_once __DIR__ . '/../includes/security.php';

if (admin_is_logged_in()) {
  header('Location: index.php');
  exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!csrf_verify()) {
    $error = 'Session expirée : rechargez la page et réessayez.';
  } elseif (submission_too_fast(2)) {
    $error = 'Trop de tentatives : veuillez patienter quelques secondes.';
  } else {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === ADMIN_USER && password_verify($password, ADMIN_PASS_HASH)) {
      session_regenerate_id(true);
      $_SESSION['admin_logged_in'] = true;
      $_SESSION['admin_user'] = $username;
      header('Location: index.php');
      exit;
    } else {
      $error = 'Identifiants incorrects.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion Admin - Résidence Rubis</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../vendor/phosphor/style.css">
  <link rel="stylesheet" href="admin.css?v=7">
</head>
<body class="login-body">
  <div class="login-card">
    <div class="login-logo"><i class="ph ph-fill ph-diamond" aria-hidden="true"></i></div>
    <h1>Résidence Rubis</h1>
    <p class="login-sub">Espace administrateur</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="login-form">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Nom d'utilisateur</label>
        <input type="text" name="username" placeholder="admin" required autofocus>
      </div>
      <div class="form-group">
        <label>Mot de passe</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn">Se connecter</button>
    </form>

    <a href="../index.php" class="back-link"><i class="ph ph-fill ph-arrow-left" aria-hidden="true"></i> Retour au site</a>
  </div>
</body>
</html>
