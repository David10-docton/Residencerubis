<?php
$page_title = "Accueil";
$meta_desc = "Résidence Rubis à Cotonou, Bénin. Appartements meublés de standing avec vue sur mer, wifi gratuit, climatisation. Réservation en ligne.";
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/security.php';

$booking_success = '';
$booking_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_submit'])) {
  if (honeypot_filled()) {
    // Robot détecté : on feint le succès sans rien enregistrer.
    $booking_success = 'Merci ! Votre demande de réservation a bien été enregistrée. Nous vous recontacterons rapidement.';
  } elseif (!csrf_verify()) {
    $booking_error = 'Session expirée : rechargez la page et réessayez.';
  } elseif (submission_too_fast()) {
    $booking_error = 'Veuillez patienter quelques secondes avant de renvoyer le formulaire.';
  } else {
    $check_in = trim($_POST['check_in'] ?? '');
    $check_out = trim($_POST['check_out'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $apartment = trim($_POST['apartment'] ?? '');

    if ($check_in === '' || $check_out === '' || $email === '' || $apartment === '') {
      $booking_error = 'Veuillez remplir tous les champs du formulaire.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $booking_error = 'Adresse email invalide.';
    } elseif ($check_out <= $check_in) {
      $booking_error = 'La date de départ doit être postérieure à la date d\'arrivée.';
    } else {
      $ok = db_save_booking($apartment, $check_in, $check_out, $email);

      if ($ok) {
        $subject = 'Nouvelle demande de réservation - Résidence Rubis';
        $body = "Nouvelle demande de réservation :\n\n"
              . "Appartement : $apartment\n"
              . "Arrivée : $check_in\n"
              . "Départ : $check_out\n"
              . "Email : $email\n";
        @mail($site_email, $subject, $body, 'From: ' . $site_email);
        $booking_success = 'Merci ! Votre demande de réservation a bien été enregistrée. Nous vous recontacterons rapidement.';
      } else {
        $booking_error = 'Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer ou nous contacter directement.';
      }
    }
  }
}

require_once 'includes/header.php';
?>

<section class="hero hero-slideshow">
  <div class="hero-slides">
    <div class="hero-slide active" style="background-image:url('images/site-live/hero/hero-1.jpg')"></div>
    <div class="hero-slide" style="background-image:url('images/site-live/hero/hero-2.jpg')"></div>
    <div class="hero-slide" style="background-image:url('images/site-live/hero/hero-3.jpg')"></div>
  </div>
  <div class="hero-overlay"></div>
  <div class="container">
    <div class="hero-content">
      <div class="hero-text animate animate-d1">
        <span class="section-tag">Bienvenue à la Résidence Rubis</span>
        <h1>Vous êtes <span>ici</span> chez vous.</h1>
        <p>Implantés au cœur de Cotonou, non loin du quartier des ambassades et très proche de la mer, nos appartements vous offrent la liberté de profiter d'un grand espace lors de vos séjours personnels ou professionnels.</p>
        <div class="hero-buttons">
          <a href="nos-appartements.php" class="btn btn-primary">Voir nos appartements <i class="ph ph-fill ph-arrow-right" aria-hidden="true"></i></a>
          <a href="contact.php" class="btn btn-secondary"><i class="ph ph-fill ph-envelope" aria-hidden="true"></i> Nous contacter</a>
        </div>
        <div class="hero-pills">
          <span class="hero-pill"><i class="ph ph-fill ph-wifi-high" aria-hidden="true"></i> Wi-Fi gratuit</span>
          <span class="hero-pill"><i class="ph ph-fill ph-snowflake" aria-hidden="true"></i> Climatisation</span>
          <span class="hero-pill"><i class="ph ph-fill ph-waves" aria-hidden="true"></i> Vue sur mer</span>
          <span class="hero-pill"><i class="ph ph-fill ph-shield-check" aria-hidden="true"></i> Sécurité 24/24</span>
        </div>
      </div>
      <div class="hero-booking animate animate-d2">
        <h3>Réservez votre séjour</h3>
        <?php if ($booking_success): ?>
          <div class="booking-alert booking-success"><?= htmlspecialchars($booking_success) ?></div>
        <?php elseif ($booking_error): ?>
          <div class="booking-alert booking-error"><?= htmlspecialchars($booking_error) ?></div>
        <?php endif; ?>
        <form class="booking-form" method="POST" action="index.php">
          <input type="hidden" name="booking_submit" value="1">
          <?= csrf_field() ?>
          <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;height:0;width:0;">
          <div class="form-row">
            <div class="form-group">
              <label>Arrivée</label>
              <input type="date" name="check_in" required>
            </div>
            <div class="form-group">
              <label>Départ</label>
              <input type="date" name="check_out" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" placeholder="votre@email.com" required>
            </div>
            <div class="form-group">
              <label>Appartement</label>
              <select name="apartment" required>
                <option value="">Choisissez...</option>
                <?php foreach ($apartments as $a): ?>
                  <option value="<?= htmlspecialchars($a['name']) ?>"><?= $a['name'] ?> - <?= $a['type'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <button type="submit" class="btn btn-primary"><i class="ph ph-fill ph-calendar-check" aria-hidden="true"></i> Réserver maintenant</button>
        </form>
      </div>
    </div>
  </div>
  <button type="button" class="hero-scroll-hint" aria-label="Défiler vers le contenu">
    <span class="mouse" aria-hidden="true"></span>
    <span>Défiler</span>
  </button>
</section>

<section class="info-banner animate-on-scroll">
  <div class="container">
    <h2><i class="ph ph-fill ph-clock" aria-hidden="true"></i> Horaires de la réception</h2>
    <p>Notre équipe vous accueille du lundi au samedi</p>
    <div class="hours"><?= $site_hours ?><br>Dimanche & jours fériés : fermé</div>
  </div>
</section>

<section class="section section-atouts">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">Nos atouts</span>
      <h2>À la Résidence Rubis nous avons tout ce dont vous avez <span>besoin</span>.</h2>
      <p>Pour satisfaire nos clients, nous mettons à leur disposition dans la résidence :</p>
    </div>

    <div class="atouts-layout">
      <div class="atouts-video animate-on-scroll">
        <div class="video-card">
          <span class="video-card-badge"><i class="ph ph-fill ph-play" aria-hidden="true"></i> Visite virtuelle</span>
          <div class="video-wrapper">
            <iframe src="https://www.youtube.com/embed/oZC7FrW6NgY" title="Visite virtuelle Résidence Rubis" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
          </div>
          <div class="video-card-caption">
            <h3>Visitez notre <span>résidence</span></h3>
            <p>Un aperçu de nos appartements et de leur confort exceptionnel.</p>
          </div>
        </div>
      </div>

      <div class="atouts-grid">
        <div class="atout-card animate-on-scroll">
          <span class="atout-num">01</span>
          <span class="atout-icon"><i class="ph ph-fill ph-waves" aria-hidden="true"></i></span>
          <span class="atout-text">Emplacement pratique à Cotonou avec vue sur la mer</span>
        </div>
        <div class="atout-card animate-on-scroll">
          <span class="atout-num">02</span>
          <span class="atout-icon"><i class="ph ph-fill ph-wifi-high" aria-hidden="true"></i></span>
          <span class="atout-text">Wi-Fi haut débit disponible gratuitement</span>
        </div>
        <div class="atout-card animate-on-scroll">
          <span class="atout-num">03</span>
          <span class="atout-icon"><i class="ph ph-fill ph-snowflake" aria-hidden="true"></i></span>
          <span class="atout-text">Appartements climatisés, confort de haut standing</span>
        </div>
        <div class="atout-card animate-on-scroll">
          <span class="atout-num">04</span>
          <span class="atout-icon"><i class="ph ph-fill ph-shield-check" aria-hidden="true"></i></span>
          <span class="atout-text">Emplacement sécurisé pour la protection de vos véhicules</span>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">Sélection</span>
      <h2>Nos <span>Appartements</span></h2>
      <p>Location en courte et longue durée. Logements spacieux, climatisés, avec vue sur mer et tout le confort moderne.</p>
    </div>
    <div class="apartments-grid">
      <?php foreach (array_slice($apartments, 0, 6) as $a): ?>
      <div class="apartment-card animate-on-scroll">
        <div class="apartment-image" data-bg="<?= htmlspecialchars($a['image']) ?>" data-gradient="linear-gradient(rgba(0,0,0,0.05),rgba(0,0,0,0.3))">
          <span class="apartment-badge"><?= $a['type'] ?></span>
          <span class="apartment-price"><?= $a['price'] ?> F CFA/nuit</span>
        </div>
        <div class="apartment-body">
          <h3><?= $a['name'] ?></h3>
          <p class="type"><?= $a['description'] ?></p>
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
    <div style="text-align:center;margin-top:36px;">
      <a href="nos-appartements.php" class="btn btn-gold">Voir tous nos appartements <i class="ph ph-fill ph-arrow-right" aria-hidden="true"></i></a>
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
        <div class="stars"><i class="ph ph-fill ph-star" aria-hidden="true"></i><i class="ph ph-fill ph-star" aria-hidden="true"></i><i class="ph ph-fill ph-star" aria-hidden="true"></i><i class="ph ph-fill ph-star" aria-hidden="true"></i><i class="ph ph-fill ph-star" aria-hidden="true"></i></div>
        <p>"<?= $t['text'] ?>"</p>
        <div class="testimonial-author">
          <div class="testimonial-avatar">
            <?php if (!empty($t['image'])): ?>
              <img src="<?= htmlspecialchars($t['image']) ?>" alt="<?= htmlspecialchars($t['author']) ?>" loading="lazy" decoding="async">
            <?php else: ?>
              <?= $t['initial'] ?>
            <?php endif; ?>
          </div>
          <div><strong><?= $t['author'] ?></strong><span>Client</span></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section-reglement">
  <div class="container">
    <div class="reglement-card animate-on-scroll">
      <h2>Nos règlements</h2>
      <div class="reglement-divider">
        <span class="reglement-divider-line"></span>
        <i class="ph ph-fill ph-storefront" aria-hidden="true"></i>
        <span class="reglement-divider-line"></span>
      </div>
      <div class="reglement-content">
        <div class="reglement-item">
          <i class="ph ph-fill ph-arrow-right" aria-hidden="true"></i>
          <p><strong>RESPECT DU VOISINAGE ET DES PARTIES COMMUNES.</strong> Pensez à prévenir vos voisins par une petite affiche lorsque vous risquez de faire plus de bruit qu'habitude.</p>
        </div>
        <div class="reglement-item">
          <i class="ph ph-fill ph-arrow-right" aria-hidden="true"></i>
          <p>Sachez que les bruits de comportement peuvent être sanctionnés dès lors qu'ils troublent de manière anormale la tranquillité du voisinage, de jour comme de nuit. Merci de respecter la tranquillité de l'immeuble.</p>
        </div>
        <div class="reglement-item">
          <i class="ph ph-fill ph-arrow-right" aria-hidden="true"></i>
          <p>Les bruits et comportements concernés sont notamment ceux provoqués, de jour comme de nuit, par un individu (locataire, propriétaire ou occupant) : cris, bruits de pas, talons, pétards, feux d'artifice, travaux de bricolage, etc., ainsi que ceux provoqués par des animaux.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
