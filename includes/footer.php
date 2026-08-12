  </main>

  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <img src="<?= htmlspecialchars($logo_image) ?>" alt="<?= $site_name ?>" class="footer-logo-img footer-logo-large">
          <p>Appartements meublés de standing à Cotonou, Bénin. Vue sur mer, confort moderne et service exceptionnel pour vos séjours personnels et professionnels.</p>
        </div>
        <div>
          <h4>Raccourcis rapides</h4>
          <?php foreach ($nav_links as $link): ?>
            <a href="<?= $link['url'] ?>"><?= $link['label'] ?></a>
          <?php endforeach; ?>
        </div>
        <div>
          <h4>Contactez-nous</h4>
          <a href="mailto:<?= $site_email ?>"><i class="ph ph-fill ph-envelope" aria-hidden="true"></i> <?= $site_email ?></a>
          <a href="tel:<?= str_replace([' ', '(', ')', '+'], '', $site_phone) ?>"><i class="ph ph-fill ph-phone" aria-hidden="true"></i> <?= $site_phone ?></a>
          <div class="footer-social">
            <a href="https://www.facebook.com/residencerubis" target="_blank" aria-label="Facebook"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
            <a href="https://www.instagram.com/residencerubis66" target="_blank" aria-label="Instagram"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
            <a href="mailto:<?= $site_email ?>" aria-label="Email"><i class="ph ph-fill ph-envelope" aria-hidden="true"></i></a>
            <a href="tel:<?= str_replace([' ', '(', ')', '+'], '', $site_phone) ?>" aria-label="Téléphone"><i class="ph ph-fill ph-phone" aria-hidden="true"></i></a>
          </div>
        </div>
        <div class="footer-map-col">
          <h4>Trouvez-nous sur Google Map</h4>
          <div class="footer-map">
            <iframe src="https://maps.google.com/maps?q=6.35056,2.371313&amp;t=m&amp;z=16&amp;output=embed&amp;iwloc=near" width="100%" height="100%" style="border:0;" allowfullscreen loading="lazy" title="Résidence Rubis — Fidjrossè, Cotonou"></iframe>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <div class="footer-copy">2022 - <?= $current_year ?> &copy; ResidenceRubis by <img src="images/site-live/logo-agence-sav.png" alt="SAV" class="footer-sav-logo"></div>
      </div>
    </div>
  </footer>

  <div class="cart-float" onclick="window.location.href='nos-appartements.php'" title="Mes réservations">
    <i class="ph ph-fill ph-shopping-cart" aria-hidden="true"></i>
    <span class="cart-badge">0</span>
  </div>

  <script src="js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>

  <div class="login-modal-overlay" id="login-modal">
    <div class="login-modal">
      <button type="button" class="login-modal-close" id="loginModalClose" aria-label="Fermer">&times;</button>
      <div class="login-modal-header">
        <img src="<?= htmlspecialchars($logo_image) ?>" alt="<?= $site_name ?>" class="login-modal-logo">
        <h2>Bienvenue</h2>
        <p>Connectez-vous ou créez un compte</p>
      </div>
      <div class="login-tabs">
        <button type="button" class="login-tab active" data-tab="login">Se connecter</button>
        <button type="button" class="login-tab" data-tab="register">S'inscrire</button>
      </div>
      <form class="login-form" id="loginForm" style="display:block;">
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="votre@email.com" required>
        </div>
        <div class="form-group">
          <label>Mot de passe</label>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <div class="login-options">
          <label class="login-remember">
            <input type="checkbox" name="remember"> Se souvenir de moi
          </label>
          <a href="#" class="login-forgot">Mot de passe oublié</a>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Se connecter</button>
      </form>
      <form class="login-form" id="registerForm" style="display:none;">
        <div class="form-group">
          <label>Nom complet</label>
          <input type="text" name="name" placeholder="Votre nom" required>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="votre@email.com" required>
        </div>
        <div class="form-group">
          <label>Mot de passe</label>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <div class="form-group">
          <label>Confirmer le mot de passe</label>
          <input type="password" name="password_confirm" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full">Créer mon compte</button>
      </form>
    </div>
  </div>

  <script>
  (function(){
    var modal = document.getElementById('login-modal');
    var closeBtn = document.getElementById('loginModalClose');
    var loginBtn = document.getElementById('navLoginBtn');
    var tabs = document.querySelectorAll('.login-tab');
    var loginForm = document.getElementById('loginForm');
    var registerForm = document.getElementById('registerForm');

    if (loginBtn) {
      loginBtn.addEventListener('click', function(e) {
        e.preventDefault();
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
      });
    }

    if (closeBtn) {
      closeBtn.addEventListener('click', function() {
        modal.classList.remove('open');
        document.body.style.overflow = '';
      });
    }

    modal.addEventListener('click', function(e) {
      if (e.target === modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
      }
    });

    tabs.forEach(function(tab) {
      tab.addEventListener('click', function() {
        tabs.forEach(function(t) { t.classList.remove('active'); });
        tab.classList.add('active');
        if (tab.dataset.tab === 'login') {
          loginForm.style.display = 'block';
          registerForm.style.display = 'none';
        } else {
          loginForm.style.display = 'none';
          registerForm.style.display = 'block';
        }
      });
    });
  })();
  </script>
</body>
</html>
