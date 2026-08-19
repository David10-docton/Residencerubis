<?php
$page_title = "Connexion";
$meta_desc = "Connectez-vous à votre espace Résidence Rubis, Cotonou Bénin.";
require_once 'includes/config.php';
require_once 'includes/security.php';

$login_error = '';

// --- Déconnexion ---
if (($_GET['action'] ?? '') === 'logout') {
  session_regenerate_id(true);
  unset($_SESSION['admin_logged_in'], $_SESSION['admin_user'], $_SESSION['csrf_token']);
  header('Location: mon-compte.php');
  exit;
}

// --- Si déjà connecté, rediriger vers le panneau admin ---
if (!empty($_SESSION['admin_logged_in'])) {
  header('Location: admin/index.php');
  exit;
}

// --- Connexion ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
  if (honeypot_filled()) {
    // Robot détecté : on feint le succès sans rien enregistrer.
    $login_error = 'Identifiants incorrects.';
  } elseif (!csrf_verify()) {
    $login_error = 'Session expirée : rechargez la page et réessayez.';
  } elseif (submission_too_fast(2)) {
    $login_error = 'Trop de tentatives : veuillez patienter avant de réessayer.';
  } else {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Requérir les identifiants depuis admin/auth.php (plus de credentials en dur ici)
    require_once __DIR__ . '/admin/auth.php';
    $user_ok = hash_equals(ADMIN_USER, $username);
    $pass_ok = hash_equals(ADMIN_PASS, $password) || password_verify($password, ADMIN_PASS_HASH);
    if ($user_ok && $pass_ok) {
      session_regenerate_id(true);
      $_SESSION['admin_logged_in'] = true;
      $_SESSION['admin_user'] = $username;
      header('Location: admin/index.php');
      exit;
    } else {
      $login_error = 'Identifiants incorrects.';
    }
  }
}

require_once 'includes/header.php';
?>

<section class="login-hero">
  <div class="login-hero-overlay"></div>
  <div class="login-hero-content">
    <div class="login-card animate-on-scroll">
      <a href="index.php" class="login-logo">
        <img src="<?= htmlspecialchars($logo_image) ?>" alt="<?= $site_name ?>">
      </a>

      <?php if (!empty($login_error)): ?>
        <div class="booking-alert booking-error login-alert"><?= htmlspecialchars($login_error) ?></div>
      <?php endif; ?>

      <form method="POST" action="mon-compte.php" class="login-form">
        <input type="hidden" name="login_submit" value="1">
        <?= csrf_field() ?>
        <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="hp-field">
        <div class="form-group">
          <label for="login-username"><i class="ph ph-fill ph-user" aria-hidden="true"></i> Identifiant</label>
          <input type="text" name="username" id="login-username" placeholder="Saisissez votre identifiant" required autofocus>
        </div>
        <div class="form-group">
          <label for="login-password"><i class="ph ph-fill ph-lock-simple" aria-hidden="true"></i> Mot de passe</label>
          <div class="password-wrap">
            <input type="password" name="password" id="login-password" placeholder="••••••••" required>
            <button type="button" class="password-toggle" onclick="var p=this.previousElementSibling; p.type=p.type==='password'?'text':'password'; this.querySelector('i').classList.toggle('ph-eye'); this.querySelector('i').classList.toggle('ph-eye-slash');" aria-label="Afficher le mot de passe"><i class="ph ph-fill ph-eye" aria-hidden="true"></i></button>
          </div>
        </div>
        <button type="submit" class="btn btn-primary login-submit">
          <i class="ph ph-fill ph-sign-in" aria-hidden="true"></i> Se connecter
        </button>
      </form>

      <a href="index.php" class="login-back"><i class="ph ph-fill ph-arrow-left" aria-hidden="true"></i> Retour au site</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
