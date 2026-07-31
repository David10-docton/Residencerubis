<?php
$page_title = "Accueil";
$meta_desc = "Résidence Rubis à Cotonou, Bénin. Appartements meublés de standing avec vue sur mer, wifi gratuit, climatisation. Réservation en ligne.";
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<section class="hero">
  <div class="container">
    <div class="hero-content">
      <div class="hero-text animate animate-d1">
        <span class="section-tag">Bienvenue à la Résidence Rubis</span>
        <h1>Vous êtes <span>ici</span> chez vous.</h1>
        <p>Implantés au cœur de Cotonou, non loin du quartier des ambassades et très proche de la mer, nos appartements vous offrent la liberté de profiter d'un grand espace lors de vos séjours personnels ou professionnels.</p>
        <div class="hero-buttons">
          <a href="nos-appartements.php" class="btn btn-primary">Voir nos appartements</a>
          <a href="contact.php" class="btn btn-secondary">Nous contacter</a>
        </div>
      </div>
      <div class="hero-booking animate animate-d2">
        <h3>Réservez votre séjour</h3>
        <form class="booking-form">
          <div class="form-row">
            <div class="form-group">
              <label>Arrivée</label>
              <input type="date" required>
            </div>
            <div class="form-group">
              <label>Départ</label>
              <input type="date" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Email</label>
              <input type="email" placeholder="votre@email.com" required>
            </div>
            <div class="form-group">
              <label>Appartement</label>
              <select required>
                <option value="">Choisissez...</option>
                <?php foreach ($apartments as $a): ?>
                  <option><?= $a['name'] ?> - <?= $a['type'] ?></option>
                <?php endforeach; ?>
                <option>Location de voiture</option>
              </select>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Réserver maintenant</button>
        </form>
      </div>
    </div>
  </div>
</section>

<section class="info-banner animate-on-scroll">
  <div class="container">
    <h2>Horaires de la réception</h2>
    <p>Notre équipe vous accueille du lundi au samedi</p>
    <div class="hours"><?= $site_hours ?><br>Dimanche & jours fériés : fermé</div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">Nos atouts</span>
      <h2>Tout ce dont vous avez <span>besoin</span></h2>
      <p>Pour satisfaire nos clients, nous mettons à leur disposition dans la résidence :</p>
    </div>
    <div class="features-grid">
      <?php foreach ($features_home as $f): ?>
      <div class="feature-card animate-on-scroll">
        <div class="feature-icon"><?= $f['icon'] ?></div>
        <h4><?= $f['title'] ?></h4>
        <p><?= $f['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">Sélection</span>
      <h2>Nos <span>Appartements</span></h2>
      <p>Des logements spacieux, climatisés, avec vue sur mer et tout le confort moderne.</p>
    </div>
    <div class="apartments-grid">
      <?php foreach (array_slice($apartments, 0, 6) as $a): ?>
      <div class="apartment-card animate-on-scroll">
        <div class="apartment-image" style="background-image:linear-gradient(rgba(0,0,0,0.05),rgba(0,0,0,0.3)),url('<?= $a['image'] ?>');">
          <span class="apartment-badge"><?= $a['type'] ?></span>
          <span class="apartment-price"><?= $a['price'] ?> XOF/nuit</span>
        </div>
        <div class="apartment-body">
          <h3><?= $a['name'] ?></h3>
          <p class="type"><?= $a['description'] ?></p>
          <div class="apartment-features">
            <?php foreach (array_slice($a['features'], 0, 3) as $feat): ?>
            <span><?= $feat ?></span>
            <?php endforeach; ?>
          </div>
          <a href="nos-appartements.php" class="btn btn-primary">Voir plus</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:36px;">
      <a href="nos-appartements.php" class="btn btn-gold">Voir tous nos appartements</a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">Témoignages</span>
      <h2>Nos clients en <span>parlent</span></h2>
    </div>
    <div class="testimonials-grid">
      <?php foreach ($testimonials as $t): ?>
      <div class="testimonial-card animate-on-scroll">
        <div class="stars">★★★★★</div>
        <p>"<?= $t['text'] ?>"</p>
        <div class="testimonial-author">
          <div class="testimonial-avatar"><?= $t['initial'] ?></div>
          <div><strong><?= $t['author'] ?></strong><span>Client</span></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
