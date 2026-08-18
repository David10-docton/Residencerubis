<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/security.php';

$slug = trim($_GET['slug'] ?? '');
$post = $slug !== '' ? db_blog_get_by_slug($slug) : null;

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
require_once 'includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <p><?= htmlspecialchars($post['subtitle']) ?></p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:800px;">
    <div class="article-meta animate-on-scroll">
      <span class="blog-card-date"><i class="ph ph-fill ph-calendar-blank" aria-hidden="true"></i> <?= date('d/m/Y', strtotime($post['created_at'])) ?></span>
      <span class="blog-card-date"><i class="ph ph-fill ph-clock" aria-hidden="true"></i> Mis à jour le <?= date('d/m/Y', strtotime($post['updated_at'])) ?></span>
    </div>

    <?php if (!empty($post['video_url'])): ?>
    <div class="article-hero animate-on-scroll">
      <?php if (strpos($post['video_url'], 'youtube.com') !== false || strpos($post['video_url'], 'youtu.be') !== false): ?>
      <iframe src="<?= htmlspecialchars($post['video_url']) ?>" title="Vidéo de <?= htmlspecialchars($post['title']) ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen style="width:100%;border-radius:16px;aspect-ratio:16/9;border:none;box-shadow:0 4px 20px rgba(0,0,0,0.12);"></iframe>
      <?php else: ?>
      <video controls autoplay muted playsinline style="width:100%;border-radius:16px;max-height:500px;background:#000;">
        <source src="<?= htmlspecialchars($post['video_url']) ?>" type="video/mp4">
        Votre navigateur ne supporte pas la lecture vidéo.
      </video>
      <?php endif; ?>
    </div>
    <?php elseif (!empty($post['image'])): ?>
    <div class="article-hero animate-on-scroll">
      <img src="<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" style="width:100%;border-radius:16px;object-fit:cover;max-height:450px;">
    </div>
    <?php endif; ?>

    <div class="article-content animate-on-scroll">
      <?= $post['content'] ?>
    </div>

    <div class="article-back animate-on-scroll">
      <a href="blog.php" class="btn btn-primary"><i class="ph ph-fill ph-arrow-left" aria-hidden="true"></i> Retour au blog</a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
