<?php
$page_title = "À propos";
$meta_desc = "Découvrez la Résidence Rubis à Cotonou : appartements meublés de standing avec vue sur mer, équipe professionnelle et services haut de gamme.";
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<section class="about-hero" style="background-image:url('<?= htmlspecialchars($about_image) ?>')">
  <div class="container">
    <span class="section-tag"><i class="ph ph-fill ph-gem" aria-hidden="true"></i> À propos de la Résidence Rubis</span>
    <h1>Vous pouvez compter sur nous pour un cadre de <span>détente parfait</span></h1>
    <p>Une résidence d'exception au cœur de Cotonou, à deux pas de la mer et du quartier des ambassades.</p>
  </div>
</section>

<section class="section section-about">
  <div class="container">
    <div class="about-story animate-on-scroll">
      <div class="about-media-wrap">
        <div class="about-story-media">
          <img src="images/site-live/about/residence.jpg" alt="La Résidence Rubis à Cotonou" loading="lazy" decoding="async">
        </div>
        <div class="about-badge">
          <span class="about-badge-num">100%</span>
          <span>Confort &amp;<br>satisfaction client</span>
        </div>
      </div>
      <div class="about-story-content">
        <span class="section-tag">Notre histoire</span>
        <h2>Votre chez-vous au cœur de <span>Cotonou</span></h2>
        <p>Implantés au cœur de Cotonou, non loin du quartier des ambassades, et très proche de la mer, nos appartements vous offrent la liberté de profiter d'un grand espace lors de vos séjours, qu'ils soient :</p>
        <div class="about-feature-box">
          <div class="about-feature">
            <i class="ph ph-fill ph-gem" aria-hidden="true"></i>
            <div><strong>Séjours personnels</strong><span>Charme du neuf, calme et convivialité</span></div>
          </div>
          <div class="about-feature">
            <i class="ph ph-fill ph-briefcase" aria-hidden="true"></i>
            <div><strong>Séjours professionnels</strong><span>Confort, espace et productivité</span></div>
          </div>
        </div>
        <a href="nos-appartements.php" class="btn btn-primary">Découvrir nos appartements <i class="ph ph-fill ph-arrow-right" aria-hidden="true"></i></a>
      </div>
    </div>

    <div class="about-story about-story-reverse animate-on-scroll">
      <div class="about-story-content">
        <span class="section-tag">Le confort avant tout</span>
        <h2>Tout est pensé pour votre <span>bien-être</span></h2>
        <p>Tous nos appartements disposent d'un grand séjour, un espace de nuit, une salle de bain indépendante, une cuisine fonctionnelle, le tout avec une terrasse : vue sur la mer. Wi-Fi gratuit.</p>
        <div class="about-info">
          <div class="about-info-item">
            <i class="ph ph-fill ph-waves" aria-hidden="true"></i>
            <span>Terrasse avec vue sur la mer</span>
          </div>
          <div class="about-info-item">
            <i class="ph ph-fill ph-wifi-high" aria-hidden="true"></i>
            <span>Wi-Fi gratuit</span>
          </div>
          <div class="about-info-item">
            <i class="ph ph-fill ph-bath" aria-hidden="true"></i>
            <span>Salle de bain indépendante</span>
          </div>
          <div class="about-info-item">
            <i class="ph ph-fill ph-fork-knife" aria-hidden="true"></i>
            <span>Cuisine fonctionnelle</span>
          </div>
        </div>
        <p class="about-note">Nous sommes dans un appartement résidentiel, confortable, spacieux et chaleureux mais non-fumeur. Calme, sérénité, convivialité... Optez pour le confort de notre résidence et la qualité de nos prestations. Laissez-vous séduire par l'alliance du charme du neuf et la modernité de nos équipements.</p>
      </div>
      <div class="about-story-media">
        <img src="images/site-live/about/residence1.jpg" alt="La Résidence Rubis — notre établissement" loading="lazy" decoding="async">
      </div>
    </div>

    <div class="about-stats animate-on-scroll">
      <div class="stat">
        <div class="stat-icon"><i class="ph ph-fill ph-building" aria-hidden="true"></i></div>
        <div class="stat-number"><?= count($apartments) ?></div>
        <div class="stat-label">Appartements de standing</div>
      </div>
      <div class="stat">
        <div class="stat-icon"><i class="ph ph-fill ph-clock" aria-hidden="true"></i></div>
        <div class="stat-number">24/24</div>
        <div class="stat-label">Disponibilité &amp; sécurité</div>
      </div>
      <div class="stat">
        <div class="stat-icon"><i class="ph ph-fill ph-star" aria-hidden="true"></i></div>
        <div class="stat-number">5★</div>
        <div class="stat-label">Service client</div>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">Notre équipe</span>
      <h2>Les visages derrière la <span>Résidence Rubis</span></h2>
      <p>Une équipe passionnée dédiée à votre confort et votre satisfaction.</p>
    </div>
    <div class="team-grid">
      <?php foreach ($team as $m): ?>
      <div class="team-card animate-on-scroll">
        <div class="team-avatar">
          <?php if (!empty($m['image'])): ?>
            <img src="<?= htmlspecialchars($m['image']) ?>" alt="<?= htmlspecialchars($m['name']) ?>" loading="lazy" decoding="async">
          <?php elseif ($m['icon'] === 'cleaning'): ?>
            <i class="ph ph-fill ph-spray-bottle" aria-hidden="true"></i>
          <?php elseif ($m['icon'] === 'security'): ?>
            <i class="ph ph-fill ph-shield-check" aria-hidden="true"></i>
          <?php endif; ?>
        </div>
        <h4><?= $m['name'] ?></h4>
        <span class="team-role-chip"><?= $m['role'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="info-banner animate-on-scroll">
  <div class="container">
    <h2>Prêt à réserver votre séjour ?</h2>
    <p>Contactez-nous dès maintenant pour une expérience inoubliable</p>
    <div style="margin-top:20px;">
      <a href="nos-appartements.php" class="btn btn-white">Voir les appartements <i class="ph ph-fill ph-arrow-right" aria-hidden="true"></i></a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
