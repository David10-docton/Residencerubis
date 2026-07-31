<?php
$page_title = "À propos";
$meta_desc = "Découvrez la Résidence Rubis à Cotonou : appartements meublés de standing avec vue sur mer, équipe professionnelle et services haut de gamme.";
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1>À propos de nous</h1>
    <p>Une résidence d'exception au cœur de Cotonou</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="about-grid">
      <div class="about-image animate-on-scroll" style="background-image:linear-gradient(rgba(122,92,74,0.08),rgba(122,92,74,0.15)),url('https://residencerubis.com/wp-content/uploads/2023/07/Residencerubis-HORTENCIA-8.jpg');">
      </div>
      <div class="about-text animate-on-scroll">
        <span class="section-tag">Notre histoire</span>
        <h2>Vous pouvez compter sur nous pour un cadre de <span>détente parfait</span></h2>
        <p>Implantés au cœur de Cotonou, non loin du quartier des ambassades, et très proche de la mer, nos appartements vous offrent la liberté de profiter d'un grand espace lors de vos séjours personnels ou professionnels.</p>
        <p>Tous nos appartements disposent d'un grand séjour, un espace de nuit, une salle de bain indépendante, une cuisine fonctionnelle, le tout avec une terrasse : vue sur la mer. Wi-Fi gratuit.</p>
        <p>Nous sommes dans un appartement résidentiel, confortable, spacieux et chaleureux mais non-fumeur. Calme, sérénité, convivialité... Optez pour le confort de notre résidence et la qualité de nos prestations.</p>
        <div class="about-stats">
          <div class="stat">
            <div class="stat-number"><?= count($apartments) ?></div>
            <div class="stat-label">Appartements</div>
          </div>
          <div class="stat">
            <div class="stat-number">24/7</div>
            <div class="stat-label">Disponibilité</div>
          </div>
          <div class="stat">
            <div class="stat-number">5★</div>
            <div class="stat-label">Service client</div>
          </div>
        </div>
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
        <div class="team-avatar"><?= $m['emoji'] ?></div>
        <h4><?= $m['name'] ?></h4>
        <p><?= $m['role'] ?></p>
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
      <a href="nos-appartements.php" class="btn btn-white">Voir les appartements</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
