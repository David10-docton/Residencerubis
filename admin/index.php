<?php
require_once 'auth.php';
admin_require_login();

// Empêcher le navigateur de mettre en cache la page admin
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/email.php';

$action_msg = '';
$action_type = '';
$whatsapp_link = '';

if (isset($_SESSION['admin_msg'])) {
  $action_msg = $_SESSION['admin_msg'];
  $action_type = $_SESSION['admin_type'] ?? 'success';
  unset($_SESSION['admin_msg'], $_SESSION['admin_type']);
}
if (isset($_SESSION['admin_whatsapp_link'])) {
  $whatsapp_link = $_SESSION['admin_whatsapp_link'];
  unset($_SESSION['admin_whatsapp_link']);
}

$tab = $_GET['tab'] ?? 'photos';
if (!in_array($tab, ['photos', 'prices', 'requests', 'blog'], true)) $tab = 'photos';

$bookings = [];
$messages = [];
$blog_posts = [];
if ($tab === 'requests') {
  $bookings = db_get_bookings();
  $messages = db_get_contact_messages();
}
if ($tab === 'blog') {
  db_blog_seed();
  $blog_posts = db_blog_get_all();
}

$overrides = [];
foreach (db_get_all_images() as $row) {
  $overrides[$row['section'] . '::' . $row['item_name']] = $row['image_url'];
}

$price_overrides = [];
foreach (db_get_all_prices() as $row) {
  $price_overrides[$row['section'] . '::' . $row['item_name']] = $row['price'];
}

function add_photo_field(&$photo_fields, $section, $name, $label, $default, $overrides) {
  $key = $section . '::' . $name;
  $raw = isset($overrides[$key]) ? $overrides[$key] : $default;
  // Depuis admin/, tout chemin relatif racine est préfixé avec ../
  $current = $raw;
  if ($raw !== '' && strpos($raw, 'http://') !== 0 && strpos($raw, 'https://') !== 0) {
    $current = '../' . ltrim($raw, '/');
  }
  $photo_fields[$key] = [
    'section' => $section,
    'name' => $name,
    'label' => $label,
    'default' => $default,
    'current' => $current,
    'custom' => isset($overrides[$key]),
  ];
}

function build_price_row($section, $item, $label, $default, $unit, $overrides) {
  $key = $section . '::' . $item;
  return [
    'section' => $section,
    'item' => $item,
    'label' => $label,
    'default' => $default,
    'current' => isset($overrides[$key]) ? $overrides[$key] : $default,
    'custom' => isset($overrides[$key]),
    'unit' => $unit,
  ];
}

$photo_fields = [];

add_photo_field($photo_fields, 'logo', 'logo', 'Logo du site', $logo_image, $overrides);

$apartment_keys = [];
foreach ($apartments as $a) {
  $key = 'apartment::' . $a['name'];
  $apartment_keys[] = $key;
  add_photo_field($photo_fields, 'apartment', $a['name'], $a['name'] . ' (' . $a['type'] . ')', $a['image'], $overrides);
}

add_photo_field($photo_fields, 'page', 'about', 'Image de la page « À propos »', $about_image, $overrides);
add_photo_field($photo_fields, 'page', 'benin', 'Image de la page « Découvrez le Bénin » (en-tête)', $benin_image, $overrides);

$benin_fields = ['page::benin'];
foreach ($benin_monuments as $m) {
  foreach ($m['images'] as $i => $img) {
    $key = 'monument::' . $m['key'] . '-' . ($i + 1);
    $benin_fields[] = $key;
    add_photo_field($photo_fields, 'monument', $m['key'] . '-' . ($i + 1), $m['name'] . ' — photo ' . ($i + 1), $img, $overrides);
  }
}

foreach ($testimonials as $t) {
  if (empty($t['image'])) continue;
  add_photo_field($photo_fields, 'testimonial', $t['author'], 'Témoignage – ' . $t['author'], $t['image'], $overrides);
}

$team_keys = [];
foreach ($team as $m) {
  if (empty($m['image'])) continue;
  $key = 'team::' . $m['name'];
  $team_keys[] = $key;
  add_photo_field($photo_fields, 'team', $m['name'], 'Équipe – ' . $m['name'], $m['image'], $overrides);
}

$page_sections = [
  ['id' => 'global', 'title' => 'Logo du site', 'pages' => 'En-tête & pied de page (toutes les pages)', 'fields' => ['logo::logo']],
  ['id' => 'accueil', 'title' => 'Accueil', 'pages' => 'index.php', 'fields' => ['testimonial::Boris DJIMADJA']],
  ['id' => 'apropos', 'title' => 'À propos', 'pages' => 'a-propos.php', 'fields' => ['page::about']],
  ['id' => 'equipe', 'title' => 'Notre équipe', 'pages' => 'a-propos.php', 'fields' => $team_keys],
  ['id' => 'appartements', 'title' => 'Nos appartements & Nos services', 'pages' => 'nos-appartements.php · nos-services.php', 'fields' => $apartment_keys],
  ['id' => 'benin', 'title' => 'Découvrez le Bénin', 'pages' => 'decouvrez-le-benin.php', 'fields' => $benin_fields,
   'note' => 'Sur cette page, les photos de chaque monument défilent automatiquement. La première photo (« en-tête ») est celle du haut de page ; remplacez chacune des photos des monuments ci-dessous pour changer le diaporama.'],
];

$apartment_prices = [];
foreach ($apartments as $a) {
  $apartment_prices[] = build_price_row('apartment', $a['name'], $a['name'] . ' (' . $a['type'] . ')', $a['price'], 'XOF/nuit', $price_overrides);
}

$service_prices = [];
foreach ($paid_services as $s) {
  $service_prices[] = build_price_row('service', $s['name'], $s['name'], $s['price'], '', $price_overrides);
}

$car_prices = [
  build_price_row('car_rental', 'location', 'Location de voiture', $car_rental_price, '', $price_overrides),
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administration - Résidence Rubis</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../vendor/phosphor/style.css">
  <link rel="stylesheet" href="admin.css?v=<?= filemtime(__DIR__ . '/admin.css') ?>">
</head>
<body>
  <nav class="admin-nav">
    <div class="admin-nav-inner">
      <div class="admin-nav-brand">
        <a href="../index.php" target="_blank" class="admin-nav-logo">
          <img src="<?= htmlspecialchars('../' . ltrim($logo_image, '/')) ?>" alt="<?= htmlspecialchars($site_name) ?>">
        </a>
        <span><?= htmlspecialchars($site_name) ?></span> <small>Admin</small>
      </div>
      <div class="admin-nav-links">
        <a href="index.php" class="<?= $tab === 'photos' ? 'active' : '' ?>"><i class="ph ph-fill ph-camera" aria-hidden="true"></i> Photos</a>
        <a href="index.php?tab=prices" class="<?= $tab === 'prices' ? 'active' : '' ?>"><i class="ph ph-fill ph-money" aria-hidden="true"></i> Prix</a>
        <a href="index.php?tab=requests" class="<?= $tab === 'requests' ? 'active' : '' ?>"><i class="ph ph-fill ph-tray" aria-hidden="true"></i> Demandes</a>
        <a href="index.php?tab=blog" class="<?= $tab === 'blog' ? 'active' : '' ?>"><i class="ph ph-fill ph-notebook" aria-hidden="true"></i> Blog</a>
        <a href="../index.php" target="_blank">Voir le site <i class="ph ph-fill ph-arrow-square-out" aria-hidden="true"></i></a>
        <a href="logout.php" class="logout">Déconnexion</a>
      </div>
    </div>
  </nav>

  <main class="admin-main">
    <div class="admin-header">
      <div>
        <h1><?= $tab === 'prices' ? 'Gestion des prix' : ($tab === 'requests' ? 'Demandes reçues' : ($tab === 'blog' ? 'Gestion du blog' : 'Gestion des photos')) ?></h1>
        <p><?= $tab === 'prices'
          ? 'Modifiez les tarifs des appartements, des services payants et de la location de voiture.'
          : ($tab === 'requests'
            ? 'Suivez les demandes de réservation et les messages reçus depuis le site.'
            : ($tab === 'blog'
              ? 'Créez, modifiez et publiez des articles pour le blog de la Résidence Rubis.'
              : 'Chaque page du site est modifiable : remplacez ou réinitialisez les photos qui y sont affichées.')) ?></p>
      </div>
    </div>

    <?php if ($action_msg): ?>
      <div class="alert alert-<?= htmlspecialchars($action_type) ?>"><?= htmlspecialchars($action_msg) ?></div>
    <?php endif; ?>
    <?php if ($whatsapp_link): ?>
      <div style="margin-top:8px;">
        <a href="<?= htmlspecialchars($whatsapp_link) ?>" target="_blank" rel="noopener" style="display:inline-block;padding:10px 20px;background:#25D366;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;">
          <i class="ph ph-fill ph-whatsapp-logo" style="margin-right:6px;"></i> Envoyer la notification WhatsApp au client
        </a>
      </div>
    <?php endif; ?>

    <?php if ($tab === 'prices'): ?>

      <section class="admin-section">
        <div class="admin-section-head">
          <div>
            <h2><i class="ph ph-fill ph-building" aria-hidden="true"></i> Appartements</h2>
            <p>Prix affiché par nuit</p>
          </div>
        </div>
        <div class="price-list">
          <?php foreach ($apartment_prices as $row): ?>
          <div class="price-row">
            <div class="price-label"><?= htmlspecialchars($row['label']) ?></div>
            <form method="POST" action="actions.php" class="price-form">
              <input type="hidden" name="action" value="price_update">
              <?= csrf_field() ?>
              <input type="hidden" name="tab" value="prices">
              <input type="hidden" name="section" value="<?= htmlspecialchars($row['section']) ?>">
              <input type="hidden" name="item" value="<?= htmlspecialchars($row['item']) ?>">
              <input type="text" name="price" value="<?= htmlspecialchars($row['current']) ?>" placeholder="<?= htmlspecialchars($row['default']) ?>">
              <span class="price-unit"><?= htmlspecialchars($row['unit']) ?></span>
              <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
            </form>
            <?php if ($row['custom']): ?>
              <form method="POST" action="actions.php">
                <input type="hidden" name="action" value="price_reset">
                <?= csrf_field() ?>
                <input type="hidden" name="tab" value="prices">
                <input type="hidden" name="section" value="<?= htmlspecialchars($row['section']) ?>">
                <input type="hidden" name="item" value="<?= htmlspecialchars($row['item']) ?>">
                <button type="submit" class="btn btn-danger btn-sm">Réinitialiser</button>
              </form>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="admin-section">
        <div class="admin-section-head">
          <div>
            <h2><i class="ph ph-fill ph-shopping-bag" aria-hidden="true"></i> Services payants</h2>
            <p>Tarifs affichés sur la page « Nos services »</p>
          </div>
        </div>
        <div class="price-list">
          <?php foreach ($service_prices as $row): ?>
          <div class="price-row">
            <div class="price-label"><?= htmlspecialchars($row['label']) ?></div>
            <form method="POST" action="actions.php" class="price-form">
              <input type="hidden" name="action" value="price_update">
              <?= csrf_field() ?>
              <input type="hidden" name="tab" value="prices">
              <input type="hidden" name="section" value="<?= htmlspecialchars($row['section']) ?>">
              <input type="hidden" name="item" value="<?= htmlspecialchars($row['item']) ?>">
              <input type="text" name="price" value="<?= htmlspecialchars($row['current']) ?>" placeholder="<?= htmlspecialchars($row['default']) ?>">
              <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
            </form>
            <?php if ($row['custom']): ?>
              <form method="POST" action="actions.php">
                <input type="hidden" name="action" value="price_reset">
                <?= csrf_field() ?>
                <input type="hidden" name="tab" value="prices">
                <input type="hidden" name="section" value="<?= htmlspecialchars($row['section']) ?>">
                <input type="hidden" name="item" value="<?= htmlspecialchars($row['item']) ?>">
                <button type="submit" class="btn btn-danger btn-sm">Réinitialiser</button>
              </form>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="admin-section">
        <div class="admin-section-head">
          <div>
            <h2><i class="ph ph-fill ph-car" aria-hidden="true"></i> Location de voiture</h2>
            <p>Prix affiché sur la page « Nos services »</p>
          </div>
        </div>
        <div class="price-list">
          <?php foreach ($car_prices as $row): ?>
          <div class="price-row">
            <div class="price-label"><?= htmlspecialchars($row['label']) ?></div>
            <form method="POST" action="actions.php" class="price-form">
              <input type="hidden" name="action" value="price_update">
              <?= csrf_field() ?>
              <input type="hidden" name="tab" value="prices">
              <input type="hidden" name="section" value="<?= htmlspecialchars($row['section']) ?>">
              <input type="hidden" name="item" value="<?= htmlspecialchars($row['item']) ?>">
              <input type="text" name="price" value="<?= htmlspecialchars($row['current']) ?>" placeholder="<?= htmlspecialchars($row['default']) ?>">
              <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
            </form>
            <?php if ($row['custom']): ?>
              <form method="POST" action="actions.php">
                <input type="hidden" name="action" value="price_reset">
                <?= csrf_field() ?>
                <input type="hidden" name="tab" value="prices">
                <input type="hidden" name="section" value="<?= htmlspecialchars($row['section']) ?>">
                <input type="hidden" name="item" value="<?= htmlspecialchars($row['item']) ?>">
                <button type="submit" class="btn btn-danger btn-sm">Réinitialiser</button>
              </form>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      </section>

    <?php elseif ($tab === 'requests'): ?>

      <?php
        $sc = ['pending' => 0, 'confirmed' => 0, 'cancelled' => 0, 'completed' => 0];
        foreach ($bookings as $b) { $s = $b['status'] ?? 'pending'; if (isset($sc[$s])) $sc[$s]++; else $sc['pending']++; }
        $total_bookings = count($bookings);
        $status_labels = ['pending' => 'En attente', 'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', 'completed' => 'Terminée'];
        $status_icons = ['pending' => 'ph-clock', 'confirmed' => 'ph-check-circle', 'cancelled' => 'ph-x-circle', 'completed' => 'ph-flag-banner'];
      ?>

      <!-- Cartes de statistiques -->
      <div class="booking-stats">
        <div class="stat-card">
          <div class="stat-card-icon all"><i class="ph ph-fill ph-squares-four"></i></div>
          <div class="stat-card-info"><span class="stat-card-label">Total</span><span class="stat-card-value"><?= $total_bookings ?></span></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon pending"><i class="ph ph-fill ph-clock"></i></div>
          <div class="stat-card-info"><span class="stat-card-label">En attente</span><span class="stat-card-value"><?= $sc['pending'] ?></span></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon confirmed"><i class="ph ph-fill ph-check-circle"></i></div>
          <div class="stat-card-info"><span class="stat-card-label">Confirmées</span><span class="stat-card-value"><?= $sc['confirmed'] ?></span></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon cancelled"><i class="ph ph-fill ph-x-circle"></i></div>
          <div class="stat-card-info"><span class="stat-card-label">Annulées</span><span class="stat-card-value"><?= $sc['cancelled'] ?></span></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon completed"><i class="ph ph-fill ph-flag-banner"></i></div>
          <div class="stat-card-info"><span class="stat-card-label">Terminées</span><span class="stat-card-value"><?= $sc['completed'] ?></span></div>
        </div>
      </div>

      <section class="admin-section">
        <div class="admin-section-head">
          <div>
            <h2><i class="ph ph-fill ph-calendar-check" aria-hidden="true"></i> Réservations</h2>
            <p>Gérez les demandes de réservation de vos clients.</p>
          </div>
        </div>

        <!-- Filtres par statut -->
        <div class="booking-tabs">
          <button class="booking-tab active" onclick="filterBookings('all')"><i class="ph ph-fill ph-squares-four" aria-hidden="true"></i> <span class="tab-label">Toutes</span> <span class="tab-count"><?= $total_bookings ?></span></button>
          <button class="booking-tab" onclick="filterBookings('pending')"><i class="ph ph-fill ph-clock" aria-hidden="true"></i> <span class="tab-label">En attente</span> <span class="tab-count"><?= $sc['pending'] ?></span></button>
          <button class="booking-tab" onclick="filterBookings('confirmed')"><i class="ph ph-fill ph-check-circle" aria-hidden="true"></i> <span class="tab-label">Confirmées</span> <span class="tab-count"><?= $sc['confirmed'] ?></span></button>
          <button class="booking-tab" onclick="filterBookings('cancelled')"><i class="ph ph-fill ph-x-circle" aria-hidden="true"></i> <span class="tab-label">Annulées</span> <span class="tab-count"><?= $sc['cancelled'] ?></span></button>
          <button class="booking-tab" onclick="filterBookings('completed')"><i class="ph ph-fill ph-flag-banner" aria-hidden="true"></i> <span class="tab-label">Terminées</span> <span class="tab-count"><?= $sc['completed'] ?></span></button>
        </div>

        <?php if (!$bookings): ?>
          <div class="booking-empty">
            <i class="ph ph-fill ph-calendar-blank"></i>
            <p>Aucune réservation pour le moment.</p>
          </div>
        <?php else: ?>
          <div class="booking-table-wrap">
            <table class="booking-table">
              <thead>
                <tr>
                  <th>Client</th>
                  <th>Appartement</th>
                  <th>Séjour</th>
                  <th>Téléphone</th>
                  <th>Statut</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($bookings as $b): ?>
                  <?php $booking_status = $b['status'] ?? 'pending'; ?>
                  <tr data-status="<?= htmlspecialchars($booking_status) ?>">
                    <td>
                      <div class="client-cell">
                        <div class="client-avatar <?= htmlspecialchars($booking_status) ?>"><?= strtoupper(mb_substr(htmlspecialchars($b['name']), 0, 1)) ?></div>
                        <div>
                          <div class="client-name"><?= htmlspecialchars($b['name']) ?></div>
                          <div class="client-email"><a href="mailto:<?= htmlspecialchars($b['email']) ?>"><?= htmlspecialchars($b['email']) ?></a></div>
                        </div>
                      </div>
                    </td>
                    <td><strong><?= htmlspecialchars($b['apartment']) ?></strong></td>
                    <td class="date-cell">
                      <?= date('d/m/Y', strtotime($b['check_in'])) ?> → <?= date('d/m/Y', strtotime($b['check_out'])) ?>
                    </td>
                    <td>
                      <?php if (!empty($b['phone'])): ?>
                        <div style="display:flex;align-items:center;gap:6px;">
                          <span><?= htmlspecialchars($b['phone']) ?></span>
                          <?php $wa_url = whatsapp_status_url($b['phone'], $booking_status, $b['apartment']); ?>
                          <?php if ($wa_url): ?>
                            <a href="<?= htmlspecialchars($wa_url) ?>" target="_blank" rel="noopener" class="whatsapp-btn" title="Envoyer WhatsApp"><i class="ph ph-fill ph-whatsapp-logo"></i></a>
                          <?php endif; ?>
                        </div>
                      <?php else: ?>
                        <span style="color:var(--text-muted);">—</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="status-badge <?= htmlspecialchars($booking_status) ?>">
                        <i class="ph ph-fill <?= $status_icons[$booking_status] ?? 'ph-clock' ?>"></i>
                        <?= $status_labels[$booking_status] ?? 'En attente' ?>
                      </span>
                    </td>
                    <td>
                      <form method="POST" action="actions.php" class="booking-status-form" onsubmit="return confirmBookingStatus(this);">
                        <input type="hidden" name="action" value="booking_status">
                        <?= csrf_field() ?>
                        <input type="hidden" name="section" value="booking">
                        <input type="hidden" name="item" value="<?= (int)$b['id'] ?>">
                        <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                        <div class="action-cell">
                          <select name="status">
                            <?php foreach ($status_labels as $value => $label): ?>
                              <option value="<?= $value ?>" <?= $booking_status === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                          </select>
                          <button type="submit" class="btn-status-update"><i class="ph ph-fill ph-check-circle"></i> Mettre à jour</button>
                        </div>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>

      <section class="admin-section">
        <div class="admin-section-head">
          <div>
            <h2><i class="ph ph-fill ph-envelope" aria-hidden="true"></i> Messages de contact</h2>
            <p>Messages envoyés depuis la page « Contact »</p>
          </div>
        </div>

    <?php elseif ($tab === 'blog'): ?>

      <section class="admin-section">
        <div class="admin-section-head">
          <div>
            <h2><i class="ph ph-fill ph-notebook" aria-hidden="true"></i> Articles du blog</h2>
            <p>Créez et gérez les articles publiés sur le blog</p>
          </div>
          <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('blog-form-wrap').style.display='block';document.getElementById('blog-edit-id').value='';document.getElementById('blog-edit-title').value='';document.getElementById('blog-edit-subtitle').value='';document.getElementById('blog-edit-slug').value='';document.getElementById('blog-edit-image').value='';document.getElementById('blog-edit-video').value='';document.getElementById('blog-edit-excerpt').value='';document.getElementById('blog-edit-content').value='';document.getElementById('blog-edit-published').value='1';"><i class="ph ph-fill ph-plus" aria-hidden="true"></i> Nouvel article</button>
        </div>

        <div id="blog-form-wrap" class="blog-form-wrap" style="display:none;">
          <form method="POST" action="actions.php" class="blog-edit-form">
            <input type="hidden" name="action" value="blog_save">
            <?= csrf_field() ?>
            <input type="hidden" name="tab" value="blog">
            <input type="hidden" name="id" id="blog-edit-id" value="">
            <div class="blog-form-grid">
              <div class="form-group">
                <label>Titre</label>
                <input type="text" name="title" id="blog-edit-title" placeholder="Titre de l'article" required>
              </div>
              <div class="form-group">
                <label>Sous-titre</label>
                <input type="text" name="subtitle" id="blog-edit-subtitle" placeholder="Courte description">
              </div>
              <div class="form-group">
                <label>Slug (URL)</label>
                <input type="text" name="slug" id="blog-edit-slug" placeholder="titre-de-l-article" required pattern="[a-z0-9\-]+">
              </div>
              <div class="form-group">
                <label>Image URL</label>
                <input type="url" name="image" id="blog-edit-image" placeholder="https://...">
              </div>
              <div class="form-group">
                <label>Vidéo URL (optionnel, MP4)</label>
                <input type="url" name="video_url" id="blog-edit-video" placeholder="chemin/vers/video.mp4">
              </div>
              <div class="form-group blog-form-full">
                <label>Extrait (affiché dans la liste)</label>
                <textarea name="excerpt" id="blog-edit-excerpt" rows="2" placeholder="Courte description pour la carte..."></textarea>
              </div>
              <div class="form-group blog-form-full">
                <label>Contenu de l'article (HTML accepté)</label>
                <textarea name="content" id="blog-edit-content" rows="10" placeholder="Contenu complet de l'article..." required></textarea>
              </div>
              <div class="form-group">
                <label>Statut</label>
                <select name="published" id="blog-edit-published">
                  <option value="1">Publié</option>
                  <option value="0">Brouillon</option>
                </select>
              </div>
            </div>
            <div style="margin-top:12px;display:flex;gap:8px;">
              <button type="submit" class="btn btn-primary btn-sm"><i class="ph ph-fill ph-floppy-disk" aria-hidden="true"></i> Enregistrer</button>
              <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('blog-form-wrap').style.display='none';">Annuler</button>
            </div>
          </form>
        </div>

        <?php if (empty($blog_posts)): ?>
          <div class="admin-note">Aucun article de blog pour le moment. Créez votre premier article !</div>
        <?php else: ?>
          <div class="table-wrap">
            <table class="admin-table">
              <thead>
                <tr><th>Titre</th><th>Slug</th><th>Statut</th><th>Créé le</th><th>Actions</th></tr>
              </thead>
              <tbody>
                <?php foreach ($blog_posts as $bp): ?>
                  <tr>
                    <td><strong><?= htmlspecialchars($bp['title']) ?></strong><br><small style="color:var(--text-muted);"><?= htmlspecialchars($bp['subtitle']) ?></small></td>
                    <td><code><?= htmlspecialchars($bp['slug']) ?></code></td>
                    <td><?= $bp['published'] ? '<span style="color:var(--success);">Publié</span>' : '<span style="color:var(--text-muted);">Brouillon</span>' ?></td>
                    <td><?= htmlspecialchars($bp['created_at']) ?></td>
                    <td style="white-space:nowrap;">
                      <button type="button" class="btn btn-outline btn-sm" onclick="editBlogPost(<?= (int)$bp['id'] ?>, <?= htmlspecialchars(json_encode($bp['title']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($bp['subtitle']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($bp['slug']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($bp['image']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($bp['video_url'] ?? ''), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($bp['excerpt']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($bp['content']), ENT_QUOTES) ?>, <?= (int)$bp['published'] ?>)"><i class="ph ph-fill ph-pencil-simple" aria-hidden="true"></i></button>
                      <form method="POST" action="actions.php" style="display:inline;" onsubmit="return confirm('Supprimer cet article ?');">
                        <input type="hidden" name="action" value="blog_delete">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$bp['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm"><i class="ph ph-fill ph-trash" aria-hidden="true"></i></button>
                      </form>
                      <?php if ($bp['published']): ?>
                        <a href="../article.php?slug=<?= urlencode($bp['slug']) ?>" target="_blank" class="btn btn-outline btn-sm" title="Voir"><i class="ph ph-fill ph-arrow-square-out" aria-hidden="true"></i></a>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>

    <?php else: ?>

      <div class="page-jump">
        <span>Pages :</span>
        <?php foreach ($page_sections as $ps): ?>
          <a href="#<?= $ps['id'] ?>"><?= htmlspecialchars($ps['title']) ?></a>
        <?php endforeach; ?>
        <a href="#contact">Contact</a>
      </div>

      <?php foreach ($page_sections as $ps): ?>
      <section class="admin-section" id="<?= $ps['id'] ?>">
        <div class="admin-section-head">
          <div>
            <h2><?= htmlspecialchars($ps['title']) ?></h2>
            <p><?= htmlspecialchars($ps['pages']) ?></p>
          </div>
        </div>
        <?php if (!empty($ps['note'])): ?>
          <div class="admin-note" style="margin-bottom:18px;"><?= htmlspecialchars($ps['note']) ?></div>
        <?php endif; ?>
        <div class="photo-list">
          <?php foreach ($ps['fields'] as $key): ?>
            <?php $field = $photo_fields[$key]; $fid = preg_replace('/[^a-zA-Z0-9\-]/', '-', $key); ?>
            <div class="photo-card">
              <div class="photo-thumb">
                <img src="<?= htmlspecialchars($field['current']) ?>" alt="<?= htmlspecialchars($field['label']) ?>"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="photo-thumb-fallback">
                  <i class="ph ph-fill ph-image" aria-hidden="true"></i>
                  <span>Image introuvable</span>
                </div>
                <?php if ($field['custom']): ?>
                  <span class="photo-badge">Personnalisée</span>
                <?php endif; ?>
              </div>

              <div class="photo-info">
                <h3><?= htmlspecialchars($field['label']) ?></h3>
                <div class="photo-url" title="<?= htmlspecialchars($field['current']) ?>"><?= htmlspecialchars($field['current']) ?></div>

                <div class="photo-actions">
                  <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('file-<?= $fid ?>').click()"><i class="ph ph-fill ph-upload" aria-hidden="true"></i> Remplacer</button>
                  <button type="button" class="btn btn-outline btn-sm" onclick="toggleEdit('edit-<?= $fid ?>')"><i class="ph ph-fill ph-pencil-simple" aria-hidden="true"></i> Modifier URL</button>
                  <?php if ($field['custom']): ?>
                    <button type="button" class="btn btn-danger btn-sm" onclick="submitAction('reset','<?= $field['section'] ?>','<?= htmlspecialchars($field['name'], ENT_QUOTES) ?>','<?= $ps['id'] ?>')"><i class="ph ph-fill ph-arrow-counter-clockwise" aria-hidden="true"></i> Réinitialiser</button>
                  <?php endif; ?>
                </div>

                <form id="upload-<?= $fid ?>" method="POST" action="actions.php" enctype="multipart/form-data" style="display:none;">
                  <input type="hidden" name="action" value="upload">
                  <?= csrf_field() ?>
                  <input type="hidden" name="anchor" value="<?= $ps['id'] ?>">
                  <input type="hidden" name="section" value="<?= $field['section'] ?>">
                  <input type="hidden" name="item" value="<?= htmlspecialchars($field['name']) ?>">
                  <input type="file" name="photo" id="file-<?= $fid ?>" accept="image/*" class="hidden-input"
                         onchange="document.getElementById('upload-<?= $fid ?>').submit();">
                </form>

                <form id="edit-<?= $fid ?>" method="POST" action="actions.php" class="edit-form" style="display:none;">
                  <input type="hidden" name="action" value="update">
                  <?= csrf_field() ?>
                  <input type="hidden" name="anchor" value="<?= $ps['id'] ?>">
                  <input type="hidden" name="section" value="<?= $field['section'] ?>">
                  <input type="hidden" name="item" value="<?= htmlspecialchars($field['name']) ?>">
                  <input type="url" name="image_url" value="<?= htmlspecialchars($field['current']) ?>" placeholder="https://...">
                  <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endforeach; ?>

      <section class="admin-section" id="contact">
        <div class="admin-section-head">
          <div>
            <h2>Contact</h2>
            <p>contact.php</p>
          </div>
        </div>
        <div class="admin-note">
          La page « Contact » affiche uniquement le logo du site (modifiable ci-dessus dans la section « Logo du site »)
          et une carte Google Map. Aucune autre image n'y est présente.
        </div>
      </section>

    <?php endif; ?>
  </main>

  <form id="action-form" method="POST" action="actions.php" style="display:none;">
    <input type="hidden" name="action" id="action-form-action">
    <?= csrf_field() ?>
    <input type="hidden" name="section" id="action-form-section">
    <input type="hidden" name="item" id="action-form-item">
    <input type="hidden" name="anchor" id="action-form-anchor">
  </form>

  <script>
    function toggleEdit(id) {
      const el = document.getElementById(id);
      el.style.display = el.style.display === 'none' ? 'flex' : 'none';
    }

    function submitAction(action, section, item, anchor) {
      document.getElementById('action-form-action').value = action;
      document.getElementById('action-form-section').value = section;
      document.getElementById('action-form-item').value = item;
      document.getElementById('action-form-anchor').value = anchor || '';
      document.getElementById('action-form').submit();
    }

    function editBlogPost(id, title, subtitle, slug, image, video_url, excerpt, content, published) {
      document.getElementById('blog-form-wrap').style.display = 'block';
      document.getElementById('blog-edit-id').value = id;
      document.getElementById('blog-edit-title').value = title;
      document.getElementById('blog-edit-subtitle').value = subtitle;
      document.getElementById('blog-edit-slug').value = slug;
      document.getElementById('blog-edit-image').value = image;
      document.getElementById('blog-edit-video').value = video_url || '';
      document.getElementById('blog-edit-excerpt').value = excerpt;
      document.getElementById('blog-edit-content').value = content;
      document.getElementById('blog-edit-published').value = published;
      document.getElementById('blog-form-wrap').scrollIntoView({ behavior: 'smooth' });
    }

    var _bookingForm = null;
    function confirmBookingStatus(form) {
      _bookingForm = form;
      var select = form.querySelector('select[name="status"]');
      var labels = {
        'pending': '⏳ En attente',
        'confirmed': '✅ Confirmée',
        'cancelled': '❌ Annulée',
        'completed': '🏁 Terminée'
      };
      var status = select.value;
      var label = labels[status] || status;
      document.getElementById('booking-confirm-text').innerHTML = 'Êtes-vous sûr de vouloir changer le statut vers <strong>\u00AB ' + label + ' \u00BB</strong> ?<br><br><small style="color:#666;">Un email et un message WhatsApp seront envoyés au client.</small>';
      document.getElementById('booking-confirm-modal').style.display = 'flex';
      return false;
    }
    function bookingConfirmYes() {
      document.getElementById('booking-confirm-modal').style.display = 'none';
      if (_bookingForm) { _bookingForm.submit(); }
    }
    function bookingConfirmNo() {
      document.getElementById('booking-confirm-modal').style.display = 'none';
      _bookingForm = null;
    }

    function filterBookings(status) {
      var rows = document.querySelectorAll(".booking-status-form");
      rows.forEach(function(form) {
        var tr = form.closest("tr");
        if (tr) {
          var rowStatus = tr.getAttribute("data-status");
          tr.style.display = (status === "all" || rowStatus === status) ? "" : "none";
        }
      });
      document.querySelectorAll(".booking-tab").forEach(function(tab) { tab.classList.remove("active"); });
      event.currentTarget.classList.add("active");
    }

    // Auto-open WhatsApp after status change
    window.addEventListener('load', function() {
      var waLink = document.querySelector('a[href*="wa.me"]');
      if (waLink) {
        setTimeout(function() { window.open(waLink.href, '_blank'); }, 1200);
      }
    });
  </script>

  <!-- Modale de confirmation -->
  <div id="booking-confirm-modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;justify-content:center;align-items:center;">
    <div style="background:#fff;border-radius:12px;padding:32px;max-width:420px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.2);text-align:center;">
      <div style="font-size:40px;margin-bottom:12px;">⚠️</div>
      <p id="booking-confirm-text" style="font-size:15px;color:#333;line-height:1.6;margin:0 0 24px;"></p>
      <div style="display:flex;gap:12px;justify-content:center;">
        <button onclick="bookingConfirmYes()" style="padding:10px 28px;background:#25D366;color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;">Oui</button>
        <button onclick="bookingConfirmNo()" style="padding:10px 28px;background:#e5e7eb;color:#333;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;">Annuler</button>
      </div>
    </div>
  </div>
</body>
</html>