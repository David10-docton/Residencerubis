<?php
$page_title = "Nos Appartements";
$meta_desc = "Découvrez nos appartements meublés à Cotonou : ANAIS, LYS, OCCITANIE, LAURA, JASMAIN, HORTENSIA. T2 et T3 avec vue sur mer, climatisation, WiFi.";
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1>Nos Appartements</h1>
    <p>Vous êtes ici chez vous</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">À la Résidence Rubis</span>
      <h2>Choisissez votre <span>appartement</span></h2>
      <p>Les tarifs indiqués ne comprennent pas les frais d'électricité.</p>
    </div>
    <div class="apartments-grid">
      <?php foreach ($apartments as $a): ?>
      <div class="apartment-card animate-on-scroll">
        <div class="apartment-image" style="background-image:linear-gradient(rgba(0,0,0,0.05),rgba(0,0,0,0.3)),url('<?= $a['image'] ?>');">
          <span class="apartment-badge"><?= $a['type'] ?></span>
          <span class="apartment-price"><?= $a['price'] ?> XOF/nuit</span>
        </div>
        <div class="apartment-body">
          <h3><?= $a['name'] ?></h3>
          <p class="type"><?= $a['description'] ?></p>
          <div class="apartment-features">
            <?php foreach ($a['features'] as $feat): ?>
            <span><?= $feat ?></span>
            <?php endforeach; ?>
          </div>
          <a href="contact.php" class="btn btn-primary">Réserver maintenant</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="info-banner animate-on-scroll">
  <div class="container">
    <h2>Vous avez des questions ?</h2>
    <p>Notre équipe est à votre disposition pour vous aider à choisir l'appartement idéal</p>
    <div style="margin-top:20px;">
      <a href="tel:<?= str_replace([' ', '(', ')', '+'], '', $site_phone) ?>" class="btn btn-white"><?= $site_phone ?></a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
