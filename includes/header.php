<?php
$current_page = basename($_SERVER['PHP_SELF']);
// Cache des versions statiques : évite 4 appels filemtime() (filesystem I/O)
// à chaque chargement de page. Le cache est valide 5 minutes.
static $versions = null;
if ($versions === null) {
  $cache_file = __DIR__ . '/cache/versions.json';
  if (is_file($cache_file) && (time() - filemtime($cache_file)) < 300) {
    $versions = json_decode((string)file_get_contents($cache_file), true);
  }
  if (!is_array($versions)) {
    $versions = [
      'css'      => @filemtime(__DIR__ . '/../css/style.css') ?: 0,
      'fa'       => @filemtime(__DIR__ . '/../vendor/fontawesome/css/all.min.css') ?: 0,
      'ph'       => @filemtime(__DIR__ . '/../vendor/phosphor/style.css') ?: 0,
      'responsive' => @filemtime(__DIR__ . '/../css/responsive.css') ?: 0,
    ];
    $dir = dirname($cache_file);
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    @file_put_contents($cache_file, json_encode($versions));
  }
}
$css_version = $versions['css'];
$fa_version = $versions['fa'];
$ph_version = $versions['ph'];
$responsive_version = $versions['responsive'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page_title ?? $site_name ?> - <?= $site_name ?> | Cotonou Bénin</title>
  <meta name="description" content="<?= $meta_desc ?? 'Appartements meublés de standing à Cotonou, Bénin. Vue sur mer, wifi gratuit, climatisation.' ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="vendor/fontawesome/css/all.min.css?v=<?= $fa_version ?>">
  <link rel="stylesheet" href="vendor/phosphor/style.css?v=<?= $ph_version ?>">
  <link rel="stylesheet" href="css/style.css?v=<?= $css_version ?>">
  <link rel="stylesheet" href="css/responsive.css?v=<?= $responsive_version ?>">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path fill='%23B85D3F' d='M116.7 33.8c4.5-6.1 11.7-9.8 19.3-9.8H376c7.6 0 14.8 3.6 19.3 9.8L512 184.3c3.6 4.8 4.6 11 2.8 16.8s-6.5 9.9-12.4 11.5L274 268.5c-4.8 1.4-10 1.4-14.8 0L9.6 212.6c-5.9-1.6-10.6-5.8-12.4-11.5S-2.7 189.1 0 184.3L116.7 33.8zM280 318.8L486.8 263 402.8 490c-2.4 6.7-8.6 11.6-15.7 12.4L280 318.8zM232 318.8L124.9 502.4c-7.1-.8-13.3-5.7-15.7-12.4L25.2 263 232 318.8z'/></svg>">
</head>
<body>
  <nav class="nav" role="navigation" aria-label="Navigation principale">
    <div class="container nav-main">
      <a href="index.php" class="nav-logo">
        <img src="<?= htmlspecialchars($logo_image) ?>" alt="<?= $site_name ?>" class="nav-logo-img">
      </a>
      <button class="nav-toggle" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
      <div class="nav-links">
        <div class="nav-links-inner">
          <?php foreach ($nav_links as $link): ?>
            <a href="<?= $link['url'] ?>" <?= $current_page === $link['url'] ? 'class="active"' : '' ?>><?= $link['label'] ?></a>
          <?php endforeach; ?>
        </div>
        <div class="nav-actions">
          <button type="button" id="navSearchBtn" class="nav-search-btn" aria-label="Rechercher" aria-expanded="false"><i class="ph ph-fill ph-magnifying-glass" aria-hidden="true"></i></button>
          <?php if (!empty($_SESSION['admin_logged_in'])): ?>
            <a href="admin/index.php" class="nav-login-btn nav-admin-link"><i class="ph ph-fill ph-buildings" aria-hidden="true"></i> Mon espace</a>
            <a href="mon-compte.php?action=logout" class="nav-logout-link"><i class="ph ph-fill ph-sign-out" aria-hidden="true"></i> Déconnexion</a>
          <?php else: ?>
            <a href="mon-compte.php" class="nav-login-btn"><i class="ph ph-fill ph-user" aria-hidden="true"></i> Se connecter</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>
  <main>
