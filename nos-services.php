<?php
$page_title = "Nos services";
$meta_desc = "Découvrez tous les services de la Résidence Rubis à Cotonou : Wi-Fi gratuit, ménage, parking, location de voiture, transfert aéroport.";
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1>Nos services</h1>
    <p>Des prestations sur mesure pour votre confort</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">Services</span>
      <h2>Tout ce dont vous avez <span>besoin</span></h2>
      <p>Des services gratuits inclus dans votre séjour et des prestations payantes à la carte.</p>
    </div>

    <div class="services-two-col animate-on-scroll">
      <div class="services-free-col">
        <h3><i class="ph ph-fill ph-clock" style="color:var(--gold);"></i> Services gratuits</h3>
        <?php foreach ($free_services as $s): ?>
        <div class="service-item">
          <div class="service-item-icon"><?= $s['icon'] ?></div>
          <div class="service-item-name">
            <?= $s['name'] ?>
            <?php if (isset($s['hint'])): ?>
              <span class="hint">(<?= $s['hint'] ?>)</span>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="services-paid-col">
        <h3><i class="ph ph-fill ph-coins" style="color:var(--gold);"></i> Services payants</h3>
        <?php foreach ($paid_services as $s): ?>
        <div class="service-item">
          <div class="service-item-icon"><?= $s['icon'] ?></div>
          <div class="service-item-name"><?= $s['name'] ?></div>
          <div class="service-item-price"><?= $s['price'] ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">Appartements</span>
      <h2>Nos <span>appartements</span></h2>
      <p>Des logements spacieux avec vue sur mer, climatisation et tout le confort moderne.</p>
    </div>
    <div class="apartments-grid">
      <?php foreach ($apartments as $a): ?>
      <div class="apartment-card animate-on-scroll">
        <div class="apartment-image" data-bg="<?= htmlspecialchars($a['image']) ?>" data-gradient="linear-gradient(rgba(0,0,0,0.05),rgba(0,0,0,0.3))">
          <span class="apartment-badge"><?= $a['type'] ?></span>
          <span class="apartment-price"><?= $a['price'] ?> F CFA/nuit</span>
        </div>
        <div class="apartment-body">
          <h3><?= $a['name'] ?></h3>
          <div class="apartment-features">
            <?php foreach (array_slice($a['features'], 0, 3) as $feat): ?>
            <span><?= $feat ?></span>
            <?php endforeach; ?>
          </div>
          <p class="apartment-rental"><i class="ph ph-fill ph-calendar-check" aria-hidden="true"></i> Courte &amp; longue durée</p>
          <a href="produit.php?appartement=<?= urlencode(mb_strtolower($a['name'])) ?>" class="btn btn-primary">Voir plus <i class="ph ph-fill ph-arrow-right" aria-hidden="true"></i></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="car-rental-wrap animate-on-scroll">
      <div class="car-rental-card">
        <div class="car-rental-body">
          <img src="images/voiture1.png" alt="Location de voiture" class="car-rental-img" loading="lazy" decoding="async">
          <h3>LOCATION DE VOITURE</h3>
          <p class="car-rental-desc">Explorez Cotonou et le Bénin en toute liberté</p>
          <div class="car-rental-price"><?= htmlspecialchars($car_rental_price) ?></div>
          <a href="contact.php" class="btn btn-gold">Voir plus <i class="ph ph-fill ph-arrow-right" aria-hidden="true"></i></a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
