<?php
http_response_code(404);
$page_title = "Page introuvable";
$meta_desc = "Cette page n'existe pas ou a été déplacée. Retournez à l'accueil de la Résidence Rubis.";
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1>404 — Page introuvable</h1>
    <p>Oups ! Cette page n'existe pas ou a été déplacée.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="text-align:center;max-width:620px;">
    <div style="font-family:var(--font-display);font-size:7rem;font-weight:800;line-height:1;background:linear-gradient(135deg,var(--primary),var(--gold));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:18px;">404</div>
    <h2 style="font-family:var(--font-display);font-size:1.8rem;margin-bottom:10px;">Vous êtes ici <span style="background:linear-gradient(135deg,var(--primary),var(--primary-light));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;">chez vous</span>, mais pas sur cette page.</h2>
    <p style="color:var(--text-light);margin-bottom:28px;">La page que vous recherchez n'existe plus ou n'a jamais existé. Pas de panique, voici quelques liens utiles :</p>
    <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
      <a href="index.php" class="btn btn-primary"><i class="ph ph-fill ph-house" aria-hidden="true"></i> Retour à l'accueil</a>
      <a href="nos-appartements.php" class="btn btn-secondary">Nos appartements</a>
      <a href="contact.php" class="btn btn-gold">Nous contacter</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
