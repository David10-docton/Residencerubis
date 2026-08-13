<?php
$page_title = "Réservation";
$meta_desc = "Réservez votre appartement meublé à Cotonou, Bénin. Tarifs, types, surfaces et formulaire de réservation.";
require_once 'includes/config.php';
require_once 'includes/security.php';

$slug = mb_strtolower(trim($_GET['appartement'] ?? ''));

$apartment = null;
foreach ($apartments as $a) {
  if (mb_strtolower($a['name']) === $slug) { $apartment = $a; break; }
}
if (!$apartment) {
  foreach ($apartments as $a) {
    if (stripos(mb_strtolower($a['name']), $slug) !== false) { $apartment = $a; break; }
  }
}
if (!$apartment) $apartment = $apartments[0];
$slug = mb_strtolower($apartment['name']);

$gallery = glob($apartment['gallery'] . '/*.jpg');
if (empty($gallery)) $gallery = glob($apartment['gallery'] . '/*.jpeg');
if (empty($gallery)) $gallery = glob($apartment['gallery'] . '/*.png');

$booking_success = '';
$booking_error = '';
if (($_POST['booking_submit'] ?? '') === '1') {
  if (honeypot_filled()) {
    // Robot détecté : on feint le succès sans rien enregistrer.
    $booking_success = 'Merci ! Votre demande de réservation a bien été enregistrée. Nous vous recontacterons rapidement.';
  } elseif (!csrf_verify()) {
    $booking_error = 'Session expirée : rechargez la page et réessayez.';
  } elseif (submission_too_fast()) {
    $booking_error = 'Veuillez patienter quelques secondes avant de renvoyer le formulaire.';
  } else {
    $debut_jour  = (int)($_POST['debut_jour']  ?? 0);
    $debut_mois  = (int)($_POST['debut_mois']  ?? 0);
    $debut_annee = (int)($_POST['debut_annee'] ?? 0);
    $fin_jour    = (int)($_POST['fin_jour']    ?? 0);
    $fin_mois    = (int)($_POST['fin_mois']    ?? 0);
    $fin_annee   = (int)($_POST['fin_annee']   ?? 0);
    $email       = trim($_POST['email'] ?? '');

    $dates_valides = $debut_jour && $debut_mois && $debut_annee && $fin_jour && $fin_mois && $fin_annee
      && $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)
      && checkdate($debut_mois, $debut_jour, $debut_annee)
      && checkdate($fin_mois, $fin_jour, $fin_annee);

    if ($dates_valides) {
      $check_in = sprintf('%04d-%02d-%02d', $debut_annee, $debut_mois, $debut_jour);
      $check_out = sprintf('%04d-%02d-%02d', $fin_annee, $fin_mois, $fin_jour);

      if ($check_out <= $check_in) {
        $booking_error = 'La date de départ doit être postérieure à la date d\'arrivée.';
      } else {
        $saved = (file_exists(__DIR__ . '/includes/db.php') && function_exists('db_save_booking'))
          ? db_save_booking($apartment['name'], $check_in, $check_out, $email)
          : false;
        $subject = "Réservation Résidence Rubis - " . $apartment['name'];
        $nights = max(1, (int)((strtotime($check_out) - strtotime($check_in)) / 86400));
        $price_n = (int)preg_replace('/\D/', '', $apartment['price']);
        $total_f = $nights * $price_n;
        $body = "Nouvelle demande de réservation\n"
          . "Appartement : {$apartment['name']}\n"
          . "Arrivée : $check_in\n"
          . "Départ : $check_out\n"
          . "Nombre de nuits : $nights\n"
          . "Tarif / nuit : {$apartment['price']} F CFA\n"
          . "Total estimé : " . number_format($total_f, 0, ',', ' ') . " F CFA (électricité non comprise)\n"
          . "Email : $email\n";
        @mail($site_email, $subject, $body, 'From: ' . $email);
        if ($saved !== false) {
          $booking_success = 'Merci ! Votre demande de réservation a bien été enregistrée. Nous vous recontacterons rapidement.';
        } else {
          $booking_error = 'Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer ou nous contacter directement.';
        }
      }
    } else {
      $booking_error = 'Veuillez remplir tous les champs correctement (dates et email).';
    }
  }
}

$annees = range((int)date('Y'), (int)date('Y') + 3);
$mois_labels = [1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'];

require_once 'includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1><?= htmlspecialchars($apartment['name']) ?></h1>
    <p>Réservez votre séjour</p>
  </div>
</section>

<section class="produit">
  <div class="container produit-grid">
    <div class="produit-gallery animate-on-scroll">
      <?php if (!empty($gallery)): ?>
      <div class="produit-main">
        <img src="<?= htmlspecialchars($gallery[0]) ?>" alt="<?= htmlspecialchars($apartment['name']) ?>" loading="eager" decoding="async">
        <button type="button" class="produit-zoom" aria-label="Agrandir l'image"><i class="ph ph-fill ph-magnifying-glass-plus" aria-hidden="true"></i></button>
      </div>
      <?php if (count($gallery) > 1): ?>
      <div class="produit-thumbs">
        <?php foreach (array_slice($gallery, 1) as $i => $img): ?>
        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($apartment['name']) ?> - photo <?= $i + 2 ?>" class="produit-thumb<?= $i === 0 ? ' active' : '' ?>" loading="lazy" decoding="async">
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>

      <!-- Lightbox : agrandissement des photos -->
      <div class="lightbox" id="lightbox" aria-hidden="true" role="dialog" aria-label="Galerie de photos">
        <button type="button" class="lightbox-close" aria-label="Fermer la galerie"><i class="ph ph-fill ph-x" aria-hidden="true"></i></button>
        <button type="button" class="lightbox-arrow lightbox-prev" aria-label="Photo précédente"><i class="ph ph-fill ph-caret-left" aria-hidden="true"></i></button>
        <figure class="lightbox-figure">
          <img src="" alt="">
          <figcaption class="lightbox-caption"></figcaption>
        </figure>
        <button type="button" class="lightbox-arrow lightbox-next" aria-label="Photo suivante"><i class="ph ph-fill ph-caret-right" aria-hidden="true"></i></button>
      </div>
    </div>
    <div class="produit-info animate-on-scroll">
      <h2 class="produit-title"><?= htmlspecialchars($apartment['name']) ?></h2>
      <div class="produit-specs">
        <p><strong><span class="spec-label">TARIFS :</span> <?= htmlspecialchars($apartment['price']) ?> F CFA (<?= htmlspecialchars($apartment['price_eur']) ?>€) nuitée</strong></p>
        <p><strong><span class="spec-label">LOCATION :</span> <?= htmlspecialchars($rental_type) ?></strong></p>
        <p><strong><span class="spec-label">TYPES APPARTEMENTS :</span> <?= htmlspecialchars($apartment['type']) ?> ( <?= htmlspecialchars($apartment['rooms']) ?> )</strong></p>
        <p><strong><span class="spec-label">SURFACES :</span> <?= htmlspecialchars($apartment['surface']) ?></strong></p>
      </div>
      <p class="produit-note">Tarif par nuitée. <?= htmlspecialchars($electricity_note) ?></p>

      <?php if ($booking_success): ?>
        <div class="booking-alert booking-success"><?= htmlspecialchars($booking_success) ?></div>
      <?php elseif ($booking_error): ?>
        <div class="booking-alert booking-error"><?= htmlspecialchars($booking_error) ?></div>
      <?php endif; ?>

      <form class="produit-booking" method="POST" action="produit.php?appartement=<?= urlencode($slug) ?>"
            data-apartment="<?= htmlspecialchars($apartment['name']) ?>"
            data-image="<?= htmlspecialchars($gallery[0] ?? $apartment['image']) ?>"
            data-price="<?= (int)preg_replace('/\D/', '', $apartment['price']) ?>"
            data-price-label="<?= htmlspecialchars($apartment['price']) ?> F"
            data-price-eur="<?= (int)$apartment['price_eur'] ?>">
        <input type="hidden" name="booking_submit" value="1">
        <?= csrf_field() ?>
        <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;height:0;width:0;">
        <input type="hidden" name="apartment" value="<?= htmlspecialchars($apartment['name']) ?>">
        <div class="produit-date-row">
          <label>Début</label>
          <div class="produit-date-fields">
            <select name="debut_jour" required aria-label="Jour de début">
              <option value="" disabled selected>JJ</option>
              <?php for ($j = 1; $j <= 31; $j++): ?><option value="<?= $j ?>"><?= sprintf('%02d', $j) ?></option><?php endfor; ?>
            </select>
            <select name="debut_mois" required aria-label="Mois de début">
              <option value="" disabled selected>MM</option>
              <?php foreach ($mois_labels as $num => $label): ?><option value="<?= $num ?>"><?= $label ?></option><?php endforeach; ?>
            </select>
            <select name="debut_annee" required aria-label="Année de début">
              <option value="" disabled selected>AAAA</option>
              <?php foreach ($annees as $y): ?><option value="<?= $y ?>"><?= $y ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="produit-date-row">
          <label>Fin</label>
          <div class="produit-date-fields">
            <select name="fin_jour" required aria-label="Jour de fin">
              <option value="" disabled selected>JJ</option>
              <?php for ($j = 1; $j <= 31; $j++): ?><option value="<?= $j ?>"><?= sprintf('%02d', $j) ?></option><?php endfor; ?>
            </select>
            <select name="fin_mois" required aria-label="Mois de fin">
              <option value="" disabled selected>MM</option>
              <?php foreach ($mois_labels as $num => $label): ?><option value="<?= $num ?>"><?= $label ?></option><?php endforeach; ?>
            </select>
            <select name="fin_annee" required aria-label="Année de fin">
              <option value="" disabled selected>AAAA</option>
              <?php foreach ($annees as $y): ?><option value="<?= $y ?>"><?= $y ?></option><?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="produit-price-calc" id="produitPriceCalc" aria-live="polite">
          <div class="produit-price-calc-head">
            <i class="ph ph-fill ph-calculator" aria-hidden="true"></i>
            <span>Estimation du séjour</span>
          </div>
          <div class="produit-price-calc-detail">
            <span id="priceCalcDetail"></span>
            <span class="produit-price-calc-currency" id="priceCalcEur"></span>
          </div>
          <div class="produit-price-calc-total">
            <span>Total</span>
            <strong id="priceCalcTotal"></strong>
          </div>

        </div>
        <div class="produit-email-row">
          <label>Email</label>
          <input type="email" name="email" placeholder="votre@email.com" required>
        </div>
        <button type="submit" class="produit-reserver"><i class="ph ph-fill ph-calendar-check" aria-hidden="true"></i> Réserver</button>
      </form>

    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">Sélection</span>
      <h2>D'autres <span>appartements</span></h2>
    </div>
    <div class="apartments-grid">
      <?php foreach (array_slice($apartments, 0, 3) as $other): ?>
      <div class="apartment-card animate-on-scroll">
        <div class="apartment-image" data-bg="<?= htmlspecialchars($other['image']) ?>" data-gradient="linear-gradient(rgba(0,0,0,0.05),rgba(0,0,0,0.3))">
          <span class="apartment-badge"><?= $other['type'] ?></span>
          <span class="apartment-price"><?= $other['price'] ?> F CFA/nuit</span>
        </div>
        <div class="apartment-body">
          <h3><?= $other['name'] ?></h3>
          <p class="type"><?= $other['description'] ?></p>
          <div class="apartment-features">
            <?php foreach (array_slice($other['features'], 0, 3) as $feat): ?>
            <span><?= $feat ?></span>
            <?php endforeach; ?>
          </div>
          <p class="apartment-rental"><i class="ph ph-fill ph-calendar-check" aria-hidden="true"></i> Courte &amp; longue durée</p>
          <a href="produit.php?appartement=<?= urlencode(mb_strtolower($other['name'])) ?>" class="btn btn-primary">Voir plus <i class="ph ph-fill ph-arrow-right" aria-hidden="true"></i></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
