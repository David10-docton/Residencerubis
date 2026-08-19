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
          <div class="footer-map" data-map-lazy="https://maps.google.com/maps?q=R%C3%A9sidence%20rubis&amp;t=m&amp;z=16&amp;output=embed&amp;iwloc=near" title="Résidence Rubis — Fidjrossè, Cotonou">
            <div class="footer-map-placeholder">
              <i class="ph ph-fill ph-map-pin" aria-hidden="true"></i>
              <span>Carte Google Map</span>
              <button type="button" class="btn btn-sm btn-outline" onclick="loadMap(this.parentElement.parentElement)">Charger la carte</button>
            </div>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <div class="footer-copy">2022 - <?= $current_year ?> &copy; ResidenceRubis by <img src="images/site-live/logo-agence-sav.png" alt="SAV" class="footer-sav-logo"></div>
      </div>
    </div>
  </footer>

  <!-- ===== Panier Réservation(s) : bouton flottant + tiroir latéral ===== -->
  <button type="button" class="res-float" id="resFloat" aria-label="Voir mes réservations" aria-expanded="false">
    <i class="ph ph-fill ph-shopping-cart-simple" aria-hidden="true"></i>
<span class="res-float-badge" id="resBadge" hidden>0</span>
  </button>

  <div class="res-overlay" id="resOverlay" hidden></div>

  <aside class="res-drawer" id="resDrawer" role="dialog" aria-modal="true" aria-label="Vos réservations" aria-hidden="true">
    <div class="res-drawer-head">
      <div class="res-drawer-title">
        <i class="ph ph-fill ph-shopping-cart-simple" aria-hidden="true"></i>
        <span>Réservation(s)</span>
      </div>
      <button type="button" class="res-drawer-close" id="resClose" aria-label="Fermer"><i class="ph ph-fill ph-x" aria-hidden="true"></i></button>
    </div>

    <div class="res-drawer-body">
      <div class="res-drawer-empty" id="resEmpty">
        <span class="res-drawer-empty-icon"><i class="ph ph-fill ph-basket" aria-hidden="true"></i></span>
        <p>Vous n'avez aucune réservation enregistrée pour le moment.</p>
        <a href="nos-appartements.php" class="btn btn-primary">Effectuer une réservation maintenant</a>
        <button type="button" class="res-drawer-continue" id="resContinue">Continuer à réserver</button>
      </div>
      <ul class="res-drawer-list" id="resList"></ul>
    </div>

    <div class="res-drawer-foot" id="resFoot" hidden>
      <input type="hidden" name="csrf_token" value="<?= function_exists('csrf_token') ? csrf_token() : '' ?>">
      <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="hp-field">
      <div class="res-drawer-total">
        <span>Total estimé</span>
        <strong id="resTotal">0 F</strong>
      </div>
      <label class="res-drawer-email-label" for="resEmail">Votre email</label>
      <input type="email" id="resEmail" placeholder="votre@email.com" autocomplete="email">
      <div class="res-drawer-alert" id="resAlert" role="status" hidden></div>
      <button type="button" class="res-drawer-submit" id="resSubmit"><i class="ph ph-fill ph-paper-plane-right" aria-hidden="true"></i> Envoyer ma demande</button>
    </div>
  </aside>

  <!-- ===== Fullscreen Search Overlay ===== -->
  <div class="search-overlay" id="searchOverlay" role="dialog" aria-modal="true" aria-label="Recherche" aria-hidden="true">
    <div class="search-overlay-bg" id="searchOverlayBg"></div>
    <div class="search-overlay-content">
      <button type="button" class="search-overlay-close" id="searchOverlayClose" aria-label="Fermer la recherche">
        <i class="ph ph-fill ph-x" aria-hidden="true"></i>
      </button>
      <div class="search-overlay-inner">
        <span class="search-overlay-label"><i class="ph ph-fill ph-magnifying-glass" aria-hidden="true"></i> Rechercher</span>
        <form action="nos-appartements.php" method="GET" role="search" class="search-overlay-form">
          <input type="search" name="q" class="search-overlay-input" placeholder="Rechercher un appartement, un service..." aria-label="Rechercher" autofocus>
          <button type="submit" class="search-overlay-submit"><i class="ph ph-fill ph-arrow-right" aria-hidden="true"></i></button>
        </form>
        <div class="search-overlay-hints">
          <a href="nos-appartements.php">Appartements</a>
          <a href="nos-services.php">Services</a>
          <a href="contact.php">Contact</a>
          <a href="blog.php">Blog</a>
        </div>
      </div>
    </div>
  </div>

  <script src="js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
  <script>
  /* Lazy-load Google Map : ne charge l'iframe que quand l'utilisateur
     clique sur le bouton ou que la zone entre dans le viewport. */
  (function(){
    var mapDiv=document.querySelector('.footer-map[data-map-lazy]');
    if(!mapDiv)return;
    function loadMap(){
      if(mapDiv.dataset.loaded)return;
      mapDiv.dataset.loaded='1';
      var src=mapDiv.dataset.mapLazy;
      mapDiv.innerHTML='<iframe src="'+src+'" width="100%" height="100%" style="border:0;" allowfullscreen loading="lazy" title="Résidence Rubis — Carte"></iframe>';
    }
    window.loadMap=loadMap;
    if('IntersectionObserver'in window){
      new IntersectionObserver(function(entries){
        if(entries[0].isIntersecting){loadMap();this.disconnect();}
      },{rootMargin:'300px 0px'}).observe(mapDiv);
    }
  })();
  </script>

</body>
</html>
