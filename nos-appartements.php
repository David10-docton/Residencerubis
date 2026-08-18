<?php
$page_title = "Nos Appartements";
$meta_desc = "Découvrez nos appartements meublés à Cotonou : ANAIS, LYS, OCCITANIE, LAURA, JASMAIN, HORTENSIA. T2 et T3 avec vue sur mer, climatisation, WiFi.";
require_once 'includes/config.php';

$search_query = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'default';
$search_results = $apartments;
if ($search_query !== '') {
  $search_results = array_values(array_filter($apartments, function ($a) use ($search_query) {
    $haystack = mb_strtolower($a['name'] . ' ' . $a['type'] . ' ' . $a['description']);
    return strpos($haystack, mb_strtolower($search_query)) !== false;
  }));
}

if ($sort === 'price_asc' || $sort === 'price_desc') {
  usort($search_results, function ($a, $b) use ($sort) {
    $result = ((int)preg_replace('/\D/', '', $a['price'])) <=> ((int)preg_replace('/\D/', '', $b['price']));
    return $sort === 'price_asc' ? $result : -$result;
  });
} elseif ($sort === 'name') {
  usort($search_results, fn($a, $b) => strcasecmp($a['name'], $b['name']));
}

require_once 'includes/header.php';
?>

<section class="page-header">
  <div class="container">
    <h1>Nos Appartements</h1>
    <p>Vous êtes ici chez vous</p>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">Nos bâtiments</span>
      <h2>Parcourez nos <span>bâtiments</span></h2>
    </div>
    <div class="building-slider animate-on-scroll">
      <?php foreach ($apartments as $i => $a): ?>
      <div class="building-slide<?= $i === 0 ? ' active' : '' ?>" style="background-image:url('<?= htmlspecialchars($a['image']) ?>')">
        <div class="building-caption">
          <span class="building-tag">Bâtiment</span>
          <h3><?= htmlspecialchars($a['name']) ?></h3>
          <span class="building-type"><?= htmlspecialchars($a['type']) ?> &bull; <?= htmlspecialchars($a['rooms']) ?> &bull; <?= htmlspecialchars($a['surface']) ?></span>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="building-dots"></div>
      <button type="button" class="building-prev" aria-label="Bâtiment précédent"><i class="ph ph-fill ph-caret-left" aria-hidden="true"></i></button>
      <button type="button" class="building-next" aria-label="Bâtiment suivant"><i class="ph ph-fill ph-caret-right" aria-hidden="true"></i></button>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="section-header animate-on-scroll">
      <span class="section-tag">À la Résidence Rubis</span>
      <h2>Choisissez votre <span>appartement</span></h2>
      <p><?= $search_query !== '' ? 'Résultats de la recherche pour « ' . htmlspecialchars($search_query) . ' »' : 'Location en courte et longue durée.' ?></p>
    </div>
    <form class="apartment-sort" method="GET" action="nos-appartements.php">
      <?php if ($search_query !== ''): ?><input type="hidden" name="q" value="<?= htmlspecialchars($search_query) ?>"><?php endif; ?>
      <label for="apartment-sort">Trier les appartements</label>
      <select id="apartment-sort" name="sort" onchange="this.form.submit()">
        <option value="default" <?= $sort === 'default' ? 'selected' : '' ?>>Sélection de la résidence</option>
        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Tarif croissant</option>
        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Tarif décroissant</option>
        <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Nom de l’appartement</option>
      </select>
    </form>
    <?php if (empty($search_results)): ?>
      <div style="text-align:center;padding:40px 0;">
        <p>Aucun appartement ne correspond à votre recherche.</p>
        <a href="nos-appartements.php" class="btn btn-gold">Voir tous nos appartements</a>
      </div>
    <?php else: ?>
    <div class="apartments-grid">
      <?php foreach ($search_results as $a): ?>
      <div class="apartment-card animate-on-scroll">
        <div class="apartment-image" data-bg="<?= htmlspecialchars($a['image']) ?>" data-gradient="linear-gradient(rgba(0,0,0,0.05),rgba(0,0,0,0.3))">
          <span class="apartment-badge"><?= $a['type'] ?></span>
          <span class="apartment-price"><?= $a['price'] ?> F CFA/nuit</span>
        </div>
        <div class="apartment-body">
          <h3><?= $a['name'] ?></h3>
          <p class="type"><?= $a['description'] ?></p>
          <div class="apartment-features">
            <?php foreach ($a['features'] as $feat): ?>
            <span><?= $feat ?></span>
            <?php endforeach; ?>
          </div>
          <p class="apartment-rental"><i class="ph ph-fill ph-calendar-check" aria-hidden="true"></i> Courte &amp; longue durée</p>
          <a href="produit.php?appartement=<?= urlencode(mb_strtolower($a['name'])) ?>" class="btn btn-primary"><i class="ph ph-fill ph-calendar-check" aria-hidden="true"></i> Voir plus</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="info-banner animate-on-scroll">
  <div class="container">
    <h2>Vous avez des questions ?</h2>
    <p>Notre équipe est à votre disposition pour vous aider à choisir l'appartement idéal</p>
    <div style="margin-top:20px;">
      <a href="tel:<?= str_replace([' ', '(', ')', '+'], '', $site_phone) ?>" class="btn btn-white"><i class="ph ph-fill ph-phone" aria-hidden="true"></i> <?= $site_phone ?></a>
    </div>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
