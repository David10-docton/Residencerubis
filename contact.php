<?php
$page_title = "Contact";
$meta_desc = "Contactez la Résidence Rubis à Cotonou, Bénin. Téléphone, email, adresse. Disponible 24h/24.";
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/security.php';

$contact_success = '';
$contact_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
  if (honeypot_filled()) {
    // Robot détecté : on feint le succès sans rien enregistrer.
    $contact_success = 'Merci ! Votre message a bien été envoyé. Nous vous répondrons rapidement.';
  } elseif (!csrf_verify()) {
    $contact_error = 'Session expirée : rechargez la page et réessayez.';
  } elseif (submission_too_fast()) {
    $contact_error = 'Veuillez patienter quelques secondes avant de renvoyer le formulaire.';
  } else {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
      $contact_error = 'Veuillez remplir tous les champs du formulaire.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $contact_error = 'Adresse email invalide.';
    } else {
      $ok = db_save_contact_message($name, $email, $message);

      if ($ok) {
        $subject = 'Nouveau message depuis le site - Résidence Rubis';
        $body = "Nouveau message depuis le site :\n\n"
              . "Nom : $name\n"
              . "Email : $email\n"
              . "Message :\n$message\n";
        @mail($site_email, $subject, $body, 'From: ' . $site_email);
        $contact_success = 'Merci ' . htmlspecialchars($name) . ' ! Votre message a bien été envoyé. Nous vous répondrons rapidement.';
      } else {
        $contact_error = 'Une erreur est survenue lors de l\'envoi. Veuillez réessayer.';
      }
    }
  }
}

// Demande groupée depuis le tiroir « Réservation(s) » (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['panier_submit'])) {
  $wants_json = strpos(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false;
  $json = ['ok' => false, 'message' => 'Une erreur est survenue.'];

  if (honeypot_filled()) {
    // Robot détecté : on feint le succès sans rien enregistrer.
    $json = ['ok' => true, 'message' => 'Votre demande a bien été envoyée. Nous vous recontacterons rapidement.'];
  } elseif (!csrf_verify()) {
    $json['message'] = 'Session expirée : rechargez la page et réessayez.';
  } elseif (submission_too_fast()) {
    $json['message'] = 'Veuillez patienter quelques secondes avant de renvoyer le formulaire.';
  } else {
    $email = trim($_POST['email'] ?? '');
    $items = json_decode((string)($_POST['items'] ?? ''), true);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $json['message'] = 'Veuillez saisir une adresse email valide.';
    } elseif (!is_array($items) || count($items) === 0) {
      $json['message'] = 'Votre sélection est vide.';
    } else {
      $lines = [];
      $total = 0;
      foreach (array_slice($items, 0, 20) as $it) {
        if (!is_array($it)) continue;
        $name   = trim((string)($it['name'] ?? ''));
        $in     = trim((string)($it['checkIn'] ?? ''));
        $out    = trim((string)($it['checkOut'] ?? ''));
        $price  = (int)($it['price'] ?? 0);
        // Les nuits sont recalculées côté serveur à partir des dates (robustesse).
        $inT  = strtotime($in);
        $outT = strtotime($out);
        $nights = ($inT !== false && $outT !== false && $outT > $inT) ? (int)round(($outT - $inT) / 86400) : 0;
        if ($name === '' || $in === '' || $out === '' || $nights <= 0) continue;
        $sub = $nights * $price;
        $total += $sub;
        $lines[] = "- $name : du $in au $out ($nights nuit(s)) — " . number_format($sub, 0, ',', ' ') . " F";
      }
      if (empty($lines)) {
        $json['message'] = 'Votre sélection est vide.';
      } else {
        $msg = "Demande de réservation groupée depuis le site :\n\n"
          . implode("\n", $lines) . "\n\n"
          . 'Total estimé : ' . number_format($total, 0, ',', ' ') . " F CFA (électricité non comprise)\n"
          . "Email client : $email\n";
        if (db_save_contact_message('Réservation groupée', $email, $msg)) {
          @mail($site_email, 'Réservations groupées - Résidence Rubis', $msg, 'From: ' . $site_email);
          $json = ['ok' => true, 'message' => 'Votre demande a bien été envoyée. Nous vous recontacterons rapidement.'];
        } else {
          $json['message'] = 'Une erreur est survenue lors de l\'envoi. Veuillez réessayer.';
        }
      }
    }
  }

  if ($wants_json) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($json);
    exit;
  }
  header('Location: contact.php?sent=' . (!empty($json['ok']) ? '1' : '0'));
  exit;
}

require_once 'includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1>Contacts</h1>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="contact-grid">
      <div class="animate-on-scroll">
        <div class="contact-cards">
          <div class="contact-card">
            <div class="contact-card-icon"><i class="ph ph-fill ph-phone" aria-hidden="true"></i></div>
            <div>
              <h4>Téléphone</h4>
              <p><?= $site_phone ?></p>
            </div>
          </div>
          <div class="contact-card">
            <div class="contact-card-icon"><i class="ph ph-fill ph-envelope" aria-hidden="true"></i></div>
            <div>
              <h4>Email</h4>
              <p><?= $site_email ?></p>
            </div>
          </div>
          <div class="contact-card">
            <div class="contact-card-icon"><i class="ph ph-fill ph-map-pin" aria-hidden="true"></i></div>
            <div>
              <h4>Adresse</h4>
              <p><?= $site_address ?></p>
            </div>
          </div>
          <div class="contact-card">
            <div class="contact-card-icon"><i class="ph ph-fill ph-clock" aria-hidden="true"></i></div>
            <div>
              <h4>Disponible</h4>
              <p>Tous les jours, 24h/24h</p>
            </div>
          </div>
        </div>
      </div>

      <div class="animate-on-scroll">
        <div class="hero-booking contact-form-card">
          <h3>Écrivez-nous</h3>
          <?php if ($contact_success): ?>
            <div class="booking-alert booking-success"><?= $contact_success ?></div>
          <?php elseif ($contact_error): ?>
            <div class="booking-alert booking-error"><?= htmlspecialchars($contact_error) ?></div>
          <?php endif; ?>
          <form id="contactForm" class="booking-form" method="POST" action="contact.php">
            <input type="hidden" name="contact_submit" value="1">
            <?= csrf_field() ?>
            <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;opacity:0;height:0;width:0;">
            <div class="form-group">
              <label>Nom/Prénom</label>
              <input type="text" name="name" placeholder="Votre nom" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" name="email" placeholder="votre@email.com" required>
            </div>
            <div class="form-group">
              <label>Message</label>
              <textarea name="message" placeholder="Votre message..." rows="6" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="justify-content:center;"><i class="ph ph-fill ph-paper-plane-right" aria-hidden="true"></i> Envoyer</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt" style="padding-top:60px;padding-bottom:60px;">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">Localisation</span>
      <h2>Trouvez-nous sur <span>Google Map</span></h2>
      <p>Résidence rubis, Cotonou, Bénin - à proximité du quartier des ambassades et de la mer.</p>
    </div>
    <div class="contact-map animate-on-scroll">
      <iframe src="https://maps.google.com/maps?q=R%C3%A9sidence%20rubis&amp;t=m&amp;z=16&amp;output=embed&amp;iwloc=near" width="100%" height="420" style="border:0;" allowfullscreen loading="lazy" title="Résidence Rubis — Fidjrossè, Cotonou"></iframe>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
