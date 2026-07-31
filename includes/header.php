<?php
$current_page = basename($_SERVER['PHP_SELF']);
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💎</text></svg>">
</head>
<body>
  <nav class="nav" role="navigation" aria-label="Navigation principale">
    <div class="container">
      <a href="index.php" class="nav-logo">
        <svg width="38" height="38" viewBox="0 0 100 100" fill="none">
          <rect width="100" height="100" rx="20" fill="url(#g1)"/>
          <path d="M50 25L65 52H35L50 25Z" fill="white" opacity="0.9"/>
          <path d="M38 52L50 72L62 52H38Z" fill="white" opacity="0.5"/>
          <rect x="44" y="52" width="12" height="18" rx="3" fill="white"/>
          <defs><linearGradient id="g1" x1="0" y1="0" x2="100" y2="100"><stop stop-color="#7A5C4A"/><stop offset="1" stop-color="#A88B7A"/></linearGradient></defs>
        </svg>
        <span style="font-size:1.1rem;"><?= $site_name ?></span>
      </a>
      <button class="nav-toggle" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
      <div class="nav-links">
        <?php foreach ($nav_links as $link): ?>
          <a href="<?= $link['url'] ?>" <?= $current_page === $link['url'] ? 'class="active"' : '' ?>><?= $link['label'] ?></a>
        <?php endforeach; ?>
      </div>
    </div>
  </nav>
  <main>
