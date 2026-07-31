<?php
$page_title = "Découvrez Le Bénin";
$meta_desc = "Découvrez le Bénin : culture, gastronomie, lieux touristiques, plages, et conseils pour votre séjour à Cotonou.";
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1>Découvrez Le Bénin</h1>
    <p>Un pays d'accueil, de culture et de découvertes</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="about-grid">
      <div>
        <div class="about-image" style="background-image:linear-gradient(rgba(122,92,74,0.08),rgba(122,92,74,0.15)),url('https://residencerubis.com/wp-content/uploads/2023/07/Residencerubis-HORTENCIA-8.jpg');height:380px;"></div>
      </div>
      <div class="animate-on-scroll">
        <span class="section-tag">Bienvenue</span>
        <h2>Explorez le <span>Bénin</span></h2>
        <p>Le Bénin, berceau du vaudou et pays de la renaissance africaine, vous accueille avec sa chaleur légendaire. De Cotonou à Ouidah, en passant par Abomey et la porte du non-retour, chaque coin du pays raconte une histoire.</p>
        <p>Séjourner à la Résidence Rubis, c'est choisir le point de départ idéal pour explorer les richesses culturelles, historiques et naturelles du Bénin.</p>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">À visiter</span>
      <h2>Lieux <span>incontournables</span></h2>
      <p>Les merveilles du Bénin à découvrir pendant votre séjour.</p>
    </div>
    <div class="benin-grid">
      <?php foreach ($benin_destinations as $d): ?>
      <div class="benin-card animate-on-scroll">
        <div class="benin-card-body">
          <h3><?= $d['name'] ?></h3>
          <p><?= $d['desc'] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="info-banner animate-on-scroll">
  <div class="container">
    <h2>Prêt à vivre l'expérience béninoise ?</h2>
    <p>Réservez votre séjour à la Résidence Rubis et partez à la découverte</p>
    <div style="margin-top:20px;">
      <a href="contact.php" class="btn btn-white">Contactez-nous</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
