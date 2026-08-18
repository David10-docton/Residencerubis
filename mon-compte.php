<?php
$page_title = "Connexion";
$meta_desc = "Connectez-vous à votre espace Résidence Rubis, Cotonou Bénin.";
require_once 'includes/config.php';
require_once 'includes/security.php';

$login_error = '';

// --- Déconnexion ---
if (($_GET['action'] ?? '') === 'logout') {
  unset($_SESSION['admin_logged_in'], $_SESSION['admin_user']);
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
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_user'] = 'savplus';
    header('Location: admin/index.php');
    exit;
  } elseif (!csrf_verify()) {
    $login_error = 'Session expirée : rechargez la page et réessayez.';
  } elseif (submission_too_fast()) {
    $login_error = 'Veuillez patienter quelques secondes avant de renvoyer le formulaire.';
  } else {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === 'savplus' && $password === 's@vplus') {
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
          <input type="password" name="password" id="login-password" placeholder="••••••••" required>
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
