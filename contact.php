<?php
$page_title = "Contact";
$meta_desc = "Contactez la Résidence Rubis à Cotonou, Bénin. Téléphone, email, adresse. Disponible 24h/24.";
require_once 'includes/config.php';
require_once 'includes/db.php';

$contact_success = '';
$contact_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
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
        <div class="hero-booking">
          <h3 style="text-transform:uppercase;letter-spacing:0.5px;">Écrivez Nous</h3>
          <?php if ($contact_success): ?>
            <div class="booking-alert booking-success"><?= $contact_success ?></div>
          <?php elseif ($contact_error): ?>
            <div class="booking-alert booking-error"><?= htmlspecialchars($contact_error) ?></div>
          <?php endif; ?>
          <form id="contactForm" class="booking-form" method="POST" action="contact.php">
            <input type="hidden" name="contact_submit" value="1">
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
      <iframe src="https://maps.google.com/maps?q=6.35056,2.371313&amp;t=m&amp;z=16&amp;output=embed&amp;iwloc=near" width="100%" height="420" style="border:0;" allowfullscreen loading="lazy" title="Résidence Rubis — Fidjrossè, Cotonou"></iframe>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
