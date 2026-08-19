<?php
$page_title = "Blog";
$meta_desc = "Blog de la Résidence Rubis : articles sur Cotonou, le Bénin, conseils voyage et actualités de la résidence.";
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/security.php';

db_blog_seed();
$posts = db_blog_get_all(true);
foreach ($posts as &$bp) { if (!empty($bp['image'])) $bp['image'] = bust($bp['image']); }
unset($bp);
require_once 'includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1>Blog</h1>
    <p>Actualités, conseils et découvertes autour de la Résidence Rubis</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">Nos articles</span>
      <h2>Le blog de la <span>Résidence Rubis</span></h2>
      <p>Restez informé sur nos appartements, Cotonou et les merveilles du Bénin.</p>
    </div>

    <?php if (empty($posts)): ?>
      <div style="text-align:center;padding:60px 0;">
        <i class="ph ph-fill ph-notebook" style="font-size:3rem;color:var(--primary);opacity:0.4;" aria-hidden="true"></i>
        <p style="margin-top:16px;color:var(--text-muted);">Aucun article pour le moment. Revenez bientôt !</p>
      </div>
    <?php else: ?>
      <div class="blog-grid">
        <?php foreach ($posts as $post): ?>
        <a href="article.php?slug=<?= urlencode($post['slug']) ?>" class="blog-card animate-on-scroll">
          <?php if (!empty($post['video_url'])): ?>
          <?php if (strpos($post['video_url'], 'youtube.com') !== false || strpos($post['video_url'], 'youtu.be') !== false): ?>
          <div class="blog-card-image" data-bg="<?= htmlspecialchars($post['image']) ?>" data-gradient="linear-gradient(rgba(0,0,0,0.05),rgba(0,0,0,0.35))">
            <div class="blog-card-play"><i class="ph ph-fill ph-play-circle" aria-hidden="true"></i></div>
          </div>
          <?php else: ?>
          <video class="blog-card-video" autoplay muted loop playsinline preload="auto" poster="<?= htmlspecialchars($post['image']) ?>" aria-label="Vidéo de <?= htmlspecialchars($post['title']) ?>">
            <source src="<?= htmlspecialchars($post['video_url']) ?>" type="video/mp4">
          </video>
          <?php endif; ?>
          <?php else: ?>
          <div class="blog-card-image" data-bg="<?= htmlspecialchars($post['image']) ?>" data-gradient="linear-gradient(rgba(0,0,0,0.05),rgba(0,0,0,0.35))"></div>
          <?php endif; ?>
          <div class="blog-card-body">
            <span class="blog-card-date"><i class="ph ph-fill ph-calendar-blank" aria-hidden="true"></i> <?= date('d/m/Y', strtotime($post['created_at'])) ?></span>
            <?php if (!empty($post['video_url'])): ?>
            <span style="display:inline-flex;align-items:center;gap:5px;font-size:0.75rem;font-weight:700;color:var(--primary);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;"><i class="ph ph-fill ph-play-circle" aria-hidden="true"></i> Visite vidéo</span>
            <?php endif; ?>
            <h3><?= htmlspecialchars($post['title']) ?></h3>
            <p class="blog-card-subtitle"><?= htmlspecialchars($post['subtitle']) ?></p>
            <p class="blog-card-excerpt"><?= htmlspecialchars($post['excerpt']) ?></p>
            <span class="blog-card-link"><i class="ph ph-fill ph-arrow-right" aria-hidden="true"></i> Lire l'article</span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
