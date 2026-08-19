<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/security.php';

$slug = trim($_GET['slug'] ?? '');
$post = $slug !== '' ? db_blog_get_by_slug($slug) : null;
if ($post && !empty($post['image'])) $post['image'] = bust($post['image']);

if (!$post) {
  header('HTTP/1.0 404 Not Found');
  require_once 'includes/header.php';
  echo '<section class="page-header"><div class="container"><h1>Article introuvable</h1><p>Cet article n\'existe pas ou a été supprimé.</p></div></section>';
  echo '<section class="section"><div class="container" style="text-align:center;padding:40px 0;"><a href="blog.php" class="btn btn-primary"><i class="ph ph-fill ph-arrow-left" aria-hidden="true"></i> Retour au blog</a></div></section>';
  require_once 'includes/footer.php';
  exit;
}

$page_title = $post['title'];
$meta_desc = $post['excerpt'];
$reading_time = max(1, ceil(str_word_count(strip_tags($post['content'])) / 200));
require_once 'includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <p><?= htmlspecialchars($post['subtitle']) ?></p>
  </div>
</section>

<article class="article-wrapper">
  <div class="article-inner">

    <!-- En-tête de l'article -->
    <header class="article-header animate-on-scroll">
      <div class="article-tags">
        <?php if (!empty($post['video_url'])): ?>
          <span class="article-tag article-tag--video"><i class="ph ph-fill ph-play-circle" aria-hidden="true"></i> Visite vidéo</span>
        <?php endif; ?>
        <span class="article-tag"><i class="ph ph-fill ph-calendar-blank" aria-hidden="true"></i> <?= date('d/m/Y', strtotime($post['created_at'])) ?></span>
        <span class="article-tag"><i class="ph ph-fill ph-clock" aria-hidden="true"></i> <?= $reading_time ?> min de lecture</span>
      </div>
      <h1 class="article-title"><?= htmlspecialchars($post['title']) ?></h1>
      <?php if (!empty($post['subtitle'])): ?>
        <p class="article-subtitle"><?= htmlspecialchars($post['subtitle']) ?></p>
      <?php endif; ?>
    </header>

    <!-- Séparateur décoratif -->
    <div class="article-divider animate-on-scroll">
      <span class="article-divider-line"></span>
      <i class="ph ph-fill ph-diamond article-divider-icon" aria-hidden="true"></i>
      <span class="article-divider-line"></span>
    </div>

    <!-- Vidéo ou image intégrée dans le contenu -->
    <?php if (!empty($post['video_url'])): ?>
    <div class="article-hero animate-on-scroll">
      <?php if (strpos($post['video_url'], 'youtube.com') !== false || strpos($post['video_url'], 'youtu.be') !== false): ?>
      <iframe src="<?= htmlspecialchars($post['video_url']) ?>" title="Vidéo de <?= htmlspecialchars($post['title']) ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen class="article-hero-iframe"></iframe>
      <?php else: ?>
      <video controls autoplay muted playsinline class="article-hero-video-local">
        <source src="<?= htmlspecialchars($post['video_url']) ?>" type="video/mp4">
        Votre navigateur ne supporte pas la lecture vidéo.
      </video>
      <?php endif; ?>
    </div>
    <?php elseif (!empty($post['image'])): ?>
    <div class="article-hero animate-on-scroll">
      <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="article-hero-image">
    </div>
    <?php endif; ?>

    <!-- Corps de l'article -->
    <div class="article-body animate-on-scroll">
      <?= $post['content'] ?>
    </div>

    <!-- Pied de l'article -->
    <footer class="article-footer animate-on-scroll">
      <div class="article-footer-divider">
        <span class="article-divider-line"></span>
        <i class="ph ph-fill ph-diamond article-divider-icon" aria-hidden="true"></i>
        <span class="article-divider-line"></span>
      </div>

      <div class="article-share">
        <span class="article-share-label">Partager cet article</span>
        <div class="article-share-links">
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://' . ($_SERVER['HTTP_HOST'] ?? 'residencerubis.com') . '/article.php?slug=' . $post['slug']) ?>" target="_blank" rel="noopener" class="article-share-btn" title="Facebook">
            <i class="ph ph-fill ph-facebook-logo" aria-hidden="true"></i>
          </a>
          <a href="https://wa.me/?text=<?= urlencode($post['title'] . ' — ') ?>&url=<?= urlencode('https://' . ($_SERVER['HTTP_HOST'] ?? 'residencerubis.com') . '/article.php?slug=' . $post['slug']) ?>" target="_blank" rel="noopener" class="article-share-btn" title="WhatsApp">
            <i class="ph ph-fill ph-whatsapp-logo" aria-hidden="true"></i>
          </a>
          <a href="https://twitter.com/intent/tweet?text=<?= urlencode($post['title']) ?>&url=<?= urlencode('https://' . ($_SERVER['HTTP_HOST'] ?? 'residencerubis.com') . '/article.php?slug=' . $post['slug']) ?>" target="_blank" rel="noopener" class="article-share-btn" title="Twitter / X">
            <i class="ph ph-fill ph-x-logo" aria-hidden="true"></i>
          </a>
        </div>
      </div>

      <div class="article-nav-back">
        <a href="blog.php" class="btn btn-primary">
          <i class="ph ph-fill ph-arrow-left" aria-hidden="true"></i> Retour au blog
        </a>
      </div>
    </footer>

  </div>
</article>

<?php require_once 'includes/footer.php'; ?>
