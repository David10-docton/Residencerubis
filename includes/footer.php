  </main>

  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <h3 style="color:var(--gold);">Résidence Rubis</h3>
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
          <a href="mailto:<?= $site_email ?>"><?= $site_email ?></a>
          <a href="tel:<?= str_replace([' ', '(', ')', '+'], '', $site_phone) ?>"><?= $site_phone ?></a>
          <div class="footer-social">
            <a href="https://www.facebook.com/residencerubis" target="_blank" aria-label="Facebook">f</a>
            <a href="https://www.instagram.com/residencerubis66" target="_blank" aria-label="Instagram">📷</a>
            <a href="mailto:<?= $site_email ?>" aria-label="Email">✉️</a>
            <a href="tel:<?= str_replace([' ', '(', ')', '+'], '', $site_phone) ?>" aria-label="Téléphone">📞</a>
          </div>
        </div>
        <div>
          <h4>Trouvez-nous sur Google Map</h4>
          <div class="footer-map">
            📍 <?= $site_address ?>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        2022 - <?= $current_year ?> &copy; ResidenceRubis by SAV
      </div>
    </div>
  </footer>

  <div class="cart-float" onclick="window.location.href='nos-appartements.php'" title="Mes réservations">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
    <span class="cart-badge">0</span>
  </div>

  <script src="js/main.js"></script>
</body>
</html>
