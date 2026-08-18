<?php
require_once 'auth.php';
admin_require_login();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';

$action_msg = '';
$action_type = '';

if (isset($_SESSION['admin_msg'])) {
  $action_msg = $_SESSION['admin_msg'];
  $action_type = $_SESSION['admin_type'] ?? 'success';
  unset($_SESSION['admin_msg'], $_SESSION['admin_type']);
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
  // Depuis admin/, les chemins relatifs racine doivent être préfixés avec ../
  $current = $raw;
  if ($raw !== '' && strpos($raw, 'http://') !== 0 && strpos($raw, 'https://') !== 0 && strpos($raw, 'uploads/') !== 0 && is_file(__DIR__ . '/../' . $raw)) {
    $current = '../' . $raw;
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
  <link rel="stylesheet" href="admin.css?v=7">
</head>
<body>
  <nav class="admin-nav">
    <div class="admin-nav-inner">
      <div class="admin-nav-brand"><i class="ph ph-fill ph-diamond" aria-hidden="true"></i> <span>Résidence Rubis</span> <small>Admin</small></div>
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

      <section class="admin-section">
        <div class="admin-section-head">
          <div>
            <h2><i class="ph ph-fill ph-calendar-check" aria-hidden="true"></i> Réservations</h2>
            <p>Les demandes en attente bloquent les dates jusqu’à leur confirmation ou annulation.</p>
          </div>
        </div>
        <?php if (!$bookings): ?>
          <div class="admin-note">Aucune réservation reçue pour le moment.</div>
        <?php else: ?>
          <div class="table-wrap">
            <table class="admin-table">
              <thead>
                <tr><th>Nom</th><th>Appartement</th><th>Arrivée</th><th>Départ</th><th>Email</th><th>Statut</th><th>Reçu le</th></tr>
              </thead>
              <tbody>
                <?php foreach ($bookings as $b): ?>
                  <tr>
                    <td><?= htmlspecialchars($b['name']) ?></td>
                    <td><?= htmlspecialchars($b['apartment']) ?></td>
                    <td><?= htmlspecialchars($b['check_in']) ?></td>
                    <td><?= htmlspecialchars($b['check_out']) ?></td>
                    <td><a href="mailto:<?= htmlspecialchars($b['email']) ?>"><?= htmlspecialchars($b['email']) ?></a></td>
                    <td>
                      <form method="POST" action="actions.php" class="booking-status-form">
                        <input type="hidden" name="action" value="booking_status">
                        <?= csrf_field() ?>
                        <input type="hidden" name="section" value="booking">
                        <input type="hidden" name="item" value="<?= (int)$b['id'] ?>">
                        <input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
                        <select name="status" aria-label="Statut de la réservation <?= htmlspecialchars($b['apartment']) ?>">
                          <?php foreach (['pending' => 'En attente', 'confirmed' => 'Confirmée', 'cancelled' => 'Annulée', 'completed' => 'Terminée'] as $value => $label): ?>
                            <option value="<?= $value ?>" <?= ($b['status'] ?? 'pending') === $value ? 'selected' : '' ?>><?= $label ?></option>
                          <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline btn-sm">Mettre à jour</button>
                      </form>
                    </td>
                    <td><?= htmlspecialchars($b['created_at']) ?></td>
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
        <?php if (!$messages): ?>
          <div class="admin-note">Aucun message reçu pour le moment.</div>
        <?php else: ?>
          <?php foreach ($messages as $m): ?>
            <div class="message-card">
              <div class="message-head">
                <strong><?= htmlspecialchars($m['name']) ?></strong>
                <a href="mailto:<?= htmlspecialchars($m['email']) ?>"><?= htmlspecialchars($m['email']) ?></a>
                <span><?= htmlspecialchars($m['created_at']) ?></span>
              </div>
              <p><?= nl2br(htmlspecialchars($m['message'])) ?></p>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>

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
                     onerror="this.parentElement.classList.add('img-error'); this.style.display='none';">
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
  </script>
</body>
</html>
