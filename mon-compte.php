<?php
$page_title = "Mon compte";
$meta_desc = "Connectez-vous ou créez votre compte client à la Résidence Rubis, Cotonou Bénin.";
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/security.php';

$account_error = '';
$account_success = '';

// Message flash (après redirection) : évite la re-soumission du formulaire
if (!empty($_SESSION['account_flash'])) {
  $account_success = $_SESSION['account_flash'];
  unset($_SESSION['account_flash']);
}

// --- Déconnexion ---
if (($_GET['action'] ?? '') === 'logout') {
  if (!empty($_SESSION['client_id'])) {
    if (isset($_COOKIE['client_remember'])) {
      db_user_set_remember_token($_SESSION['client_id'], null);
      setcookie('client_remember', '', time() - 3600, '/');
    }
  }
  unset($_SESSION['client_id'], $_SESSION['client_name'], $_SESSION['client_email']);
  header('Location: mon-compte.php');
  exit;
}

// --- Connexion automatique via le cookie « se souvenir de moi » ---
if (empty($_SESSION['client_id']) && !empty($_COOKIE['client_remember'])) {
  $user = db_user_find_by_token($_COOKIE['client_remember']);
  if ($user) {
    $_SESSION['client_id'] = (int)$user['id'];
    $_SESSION['client_name'] = $user['name'];
    $_SESSION['client_email'] = $user['email'];
  } else {
    setcookie('client_remember', '', time() - 3600, '/');
  }
}

// --- Inscription ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_submit'])) {
  if (honeypot_filled()) {
    // Robot détecté : on feint le succès sans rien créer.
    $_SESSION['account_flash'] = 'Votre compte a bien été créé. Bienvenue !';
    header('Location: mon-compte.php');
    exit;
  } elseif (!csrf_verify()) {
    $account_error = 'Session expirée : rechargez la page et réessayez.';
  } elseif (submission_too_fast()) {
    $account_error = 'Veuillez patienter quelques secondes avant de renvoyer le formulaire.';
  } else {
    $name = trim($_POST['name'] ?? '');
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($name === '' || $email === '' || $password === '' || $password_confirm === '') {
      $account_error = 'Veuillez remplir tous les champs.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $account_error = 'Adresse email invalide.';
    } elseif (mb_strlen($password) < 8) {
      $account_error = 'Le mot de passe doit contenir au moins 8 caractères.';
    } elseif ($password !== $password_confirm) {
      $account_error = 'Les deux mots de passe ne correspondent pas.';
    } elseif (db_user_find_by_email($email)) {
      $account_error = 'Un compte existe déjà avec cette adresse email. Vous pouvez vous connecter.';
    } else {
      $hash = password_hash($password, PASSWORD_BCRYPT);
      if (db_user_create($name, $email, $hash)) {
        session_regenerate_id(true);
        $user = db_user_find_by_email($email);
        $_SESSION['client_id'] = (int)$user['id'];
        $_SESSION['client_name'] = $user['name'];
        $_SESSION['client_email'] = $user['email'];
        $_SESSION['account_flash'] = 'Bienvenue ' . $user['name'] . ' ! Votre compte a été créé.';
        header('Location: mon-compte.php');
        exit;
      } else {
        $account_error = 'Une erreur est survenue lors de la création du compte. Veuillez réessayer.';
      }
    }
  }
}

// --- Connexion ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
  if (honeypot_filled()) {
    // Robot détecté : on feint le succès sans se connecter.
    $_SESSION['account_flash'] = 'Connexion réussie. Bienvenue !';
    header('Location: mon-compte.php');
    exit;
  } elseif (!csrf_verify()) {
    $account_error = 'Session expirée : rechargez la page et réessayez.';
  } elseif (submission_too_fast()) {
    $account_error = 'Veuillez patienter quelques secondes avant de renvoyer le formulaire.';
  } else {
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']);

    $user = $email !== '' ? db_user_find_by_email($email) : null;
    if ($user && password_verify($password, $user['password_hash'])) {
      session_regenerate_id(true);
      $_SESSION['client_id'] = (int)$user['id'];
      $_SESSION['client_name'] = $user['name'];
      $_SESSION['client_email'] = $user['email'];

      if ($remember) {
        $token = bin2hex(random_bytes(32));
        db_user_set_remember_token($user['id'], $token);
        setcookie('client_remember', $token, [
          'expires' => time() + 60 * 60 * 24 * 30,
          'path' => '/',
          'httponly' => true,
          'samesite' => 'Lax',
        ]);
      }
      $_SESSION['account_flash'] = 'Bon retour, ' . $user['name'] . ' !';
      header('Location: mon-compte.php');
      exit;
    } else {
      $account_error = 'Identifiants incorrects.';
    }
  }
}

require_once 'includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1>Mon compte</h1>
    <p>Connectez-vous ou créez votre compte pour préparer votre séjour</p>
  </div>
</section>

<section class="section account-section">
  <div class="container">
    <div class="account-wrap animate-on-scroll">

      <?php if (!empty($account_success)): ?>
        <div class="booking-alert booking-success account-alert"><?= $account_success ?></div>
      <?php elseif (!empty($account_error)): ?>
        <div class="booking-alert booking-error account-alert"><?= htmlspecialchars($account_error) ?></div>
      <?php endif; ?>

      <?php if (!empty($_SESSION['client_id'])): ?>
        <!-- ===== Vue connecté ===== -->
        <div class="account-card account-welcome">
          <div class="account-avatar"><i class="ph ph-fill ph-user" aria-hidden="true"></i></div>
          <span class="section-tag">Bonjour</span>
          <h2><?= htmlspecialchars($_SESSION['client_name']) ?></h2>
          <p class="account-email"><?= htmlspecialchars($_SESSION['client_email']) ?></p>
          <p class="account-msg">Vous êtes connecté à votre espace client. Préparez votre prochain séjour à la Résidence Rubis.</p>
          <div class="account-actions">
            <a href="nos-appartements.php" class="btn btn-primary"><i class="ph ph-fill ph-calendar-check" aria-hidden="true"></i> Réserver un séjour</a>
            <a href="mon-compte.php?action=logout" class="btn btn-secondary"><i class="ph ph-fill ph-sign-out" aria-hidden="true"></i> Se déconnecter</a>
          </div>
        </div>
      <?php else: ?>
        <!-- ===== Connexion / Inscription ===== -->
        <div class="account-card">
          <div class="account-tabs" role="tablist" aria-label="Connexion ou inscription">
            <button type="button" class="account-tab active" id="tabLogin" data-tab="login" role="tab" aria-selected="true">Se connecter</button>
            <button type="button" class="account-tab" id="tabRegister" data-tab="register" role="tab" aria-selected="false">S'inscrire</button>
          </div>

          <!-- Connexion -->
          <form method="POST" action="mon-compte.php" class="account-form" id="formLogin">
            <input type="hidden" name="login_submit" value="1">
            <?= csrf_field() ?>
            <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="hp-field">
            <div class="form-group">
              <label for="login-email">Adresse e-mail</label>
              <input type="email" name="email" id="login-email" placeholder="votre@email.com" required autofocus>
            </div>
            <div class="form-group">
              <label for="login-password">Mot de passe</label>
              <input type="password" name="password" id="login-password" placeholder="••••••••" required>
            </div>
            <label class="account-remember">
              <input type="checkbox" name="remember" value="1"> Se souvenir de moi
            </label>
            <button type="submit" class="btn btn-primary account-submit"><i class="ph ph-fill ph-sign-in" aria-hidden="true"></i> Se connecter</button>
            <p class="account-switch">Pas encore de compte ? <button type="button" class="account-link" data-to="register">Créer un compte</button></p>
          </form>

          <!-- Inscription -->
          <form method="POST" action="mon-compte.php" class="account-form" id="formRegister" style="display:none;">
            <input type="hidden" name="register_submit" value="1">
            <?= csrf_field() ?>
            <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="hp-field">
            <div class="form-group">
              <label for="reg-name">Nom complet</label>
              <input type="text" name="name" id="reg-name" placeholder="Votre nom" required>
            </div>
            <div class="form-group">
              <label for="reg-email">Adresse e-mail</label>
              <input type="email" name="email" id="reg-email" placeholder="votre@email.com" required>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="reg-password">Mot de passe</label>
                <input type="password" name="password" id="reg-password" placeholder="8 caractères min." required>
              </div>
              <div class="form-group">
                <label for="reg-password-confirm">Confirmer</label>
                <input type="password" name="password_confirm" id="reg-password-confirm" placeholder="••••••••" required>
              </div>
            </div>
            <button type="submit" class="btn btn-primary account-submit"><i class="ph ph-fill ph-user-plus" aria-hidden="true"></i> Créer mon compte</button>
            <p class="account-switch">Déjà inscrit ? <button type="button" class="account-link" data-to="login">Se connecter</button></p>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<script>
(function () {
  var tabs = document.querySelectorAll('.account-tab');
  var formLogin = document.getElementById('formLogin');
  var formRegister = document.getElementById('formRegister');
  var links = document.querySelectorAll('.account-link');

  function show(tab) {
    tabs.forEach(function (t) {
      t.classList.toggle('active', t.dataset.tab === tab);
      t.setAttribute('aria-selected', t.dataset.tab === tab ? 'true' : 'false');
    });
    formLogin.style.display = tab === 'login' ? 'flex' : 'none';
    formRegister.style.display = tab === 'register' ? 'flex' : 'none';
  }

  tabs.forEach(function (t) {
    t.addEventListener('click', function () { show(t.dataset.tab); });
  });
  links.forEach(function (l) {
    l.addEventListener('click', function () { show(l.dataset.to); });
  });
})();
</script>

<?php require_once 'includes/footer.php'; ?>
