<?php
$page_title = "Contact";
$meta_desc = "Contactez la Résidence Rubis à Cotonou, Bénin. Téléphone, email, adresse. Disponible 24h/24.";
require_once 'includes/config.php';
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
            <div class="contact-card-icon">💬</div>
            <div>
              <h4>Téléphone</h4>
              <p><?= $site_phone ?></p>
            </div>
          </div>
          <div class="contact-card">
            <div class="contact-card-icon">✉️</div>
            <div>
              <h4>Email</h4>
              <p><?= $site_email ?></p>
            </div>
          </div>
          <div class="contact-card">
            <div class="contact-card-icon">📍</div>
            <div>
              <h4>Adresse</h4>
              <p><?= $site_address ?></p>
            </div>
          </div>
          <div class="contact-card">
            <div class="contact-card-icon">🕐</div>
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
          <form id="contactForm" class="booking-form">
            <div class="form-group">
              <label>Nom/Prénom</label>
              <input type="text" placeholder="Votre nom" required>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" placeholder="votre@email.com" required>
            </div>
            <div class="form-group">
              <label>Message</label>
              <textarea placeholder="Votre message..." rows="6"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="justify-content:center;">Envoyer</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
