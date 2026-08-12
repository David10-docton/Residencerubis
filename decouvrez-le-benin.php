<?php
$page_title = "Découvrez Le Bénin";
$meta_desc = "Découvrez le Bénin : culture, gastronomie, lieux touristiques, monuments historiques comme le Monument Amazone, la Place Goho, Bio Guéra, les Tata Somba et la Porte du Non-Retour.";
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
        <div class="about-image" data-bg="<?= htmlspecialchars($benin_image) ?>" data-gradient="linear-gradient(rgba(122,92,74,0.08),rgba(122,92,74,0.15))" style="height:380px;"></div>
      </div>
      <div class="animate-on-scroll">
        <span class="section-tag">Bienvenue</span>
        <h2>Explorez le <span>Bénin</span></h2>
        <p>Le Bénin, berceau du vaudou et pays de la renaissance africaine, vous accueille avec sa chaleur légendaire. De Cotonou à Ouidah, en passant par Abomey et la porte du non-retour, chaque coin du pays raconte une histoire.</p>
        <p>Terres de rois et de guerriers, les monuments du Bénin — du Monument Amazone aux Tata Somba — témoignent de la grandeur des civilisations dahoméennes et de la résistance à la colonisation. Séjourner à la Résidence Rubis, c'est choisir le point de départ idéal pour explorer les richesses culturelles, historiques et naturelles du Bénin.</p>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">Monuments & histoire</span>
      <h2>Des <span>monuments</span> chargés d'histoire</h2>
      <p>Chaque monument raconte une page de l'histoire du Bénin. Faites défiler les photos pour découvrir ces lieux emblématiques.</p>
    </div>

    <?php foreach ($benin_monuments as $i => $m): ?>
    <div class="monument-card animate-on-scroll <?= $i % 2 === 1 ? 'reverse' : '' ?>">
      <div class="monument-media">
        <div class="slider">
          <?php foreach ($m['images'] as $j => $img): ?>
            <?php if ($j === 0): ?>
              <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($m['name']) ?> — photo <?= $j + 1 ?>" class="slider-img active" loading="lazy" decoding="async">
            <?php else: ?>
              <img data-src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($m['name']) ?> — photo <?= $j + 1 ?>" class="slider-img" loading="lazy" decoding="async">
            <?php endif; ?>
          <?php endforeach; ?>
          <button type="button" class="slider-arrow slider-prev" aria-label="Image précédente"><i class="ph ph-fill ph-caret-left" aria-hidden="true"></i></button>
          <button type="button" class="slider-arrow slider-next" aria-label="Image suivante"><i class="ph ph-fill ph-caret-right" aria-hidden="true"></i></button>
          <div class="slider-dots"></div>
        </div>
      </div>
      <div class="monument-text">
        <span class="section-tag">Monument & histoire</span>
        <h3><?= htmlspecialchars($m['name']) ?></h3>
        <p class="monument-location"><i class="ph ph-fill ph-map-pin" aria-hidden="true"></i> <?= htmlspecialchars($m['location']) ?></p>
        <p class="monument-desc"><?= htmlspecialchars($m['description']) ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">À visiter</span>
      <h2>Lieux <span>incontournables</span></h2>
      <p>Les merveilles du Bénin à découvrir pendant votre séjour.</p>
    </div>
    <div class="benin-grid">
      <?php foreach ($benin_destinations as $d): ?>
      <div class="benin-card animate-on-scroll">
        <div class="benin-card-image" data-bg="<?= htmlspecialchars($d['image']) ?>" data-gradient="linear-gradient(rgba(0,0,0,0.05),rgba(0,0,0,0.45))"></div>
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
      <a href="contact.php" class="btn btn-white"><i class="ph ph-fill ph-envelope" aria-hidden="true"></i> Contactez-nous</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
