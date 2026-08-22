<?php
require_once __DIR__ . '/security.php';

/**
 * Charge le fichier .env (racine du projet) s'il existe :
 * permet de ne jamais versionner les identifiants de connexion.
 */
load_env_file(__DIR__ . '/../.env');

function db_connect() {
  static $conn = null;
  static $migrated = false;
  if ($conn instanceof mysqli) return $conn;

  // Identifiants MySQL. En local (WAMP) : valeurs par défaut ci-dessous.
  // En production (InfinityFree) : surchargez-les via le fichier .env
  // (DB_HOST, DB_USER, DB_PASS, DB_NAME) ou des variables d'environnement.
  $host = getenv('DB_HOST') ?: 'localhost';
  $user = getenv('DB_USER') ?: 'root';
  $pass = getenv('DB_PASS') ?: '';
  $db   = getenv('DB_NAME') ?: 'residencerubis';

  $conn = @new mysqli($host, $user, $pass);
  if ($conn->connect_error) {
    $conn = null;
    return null;
  }
  $conn->set_charset('utf8mb4');

  @$conn->query("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

  if (!$conn->select_db($db)) {
    $conn = null;
    return null;
  }

  // Les CREATE TABLE + migrations ne s'exécutent qu'une seule fois par
  // processus PHP (static $migrated), puis le cache JSON prend le relais.
  // Cela évite ~7 requêtes DDL + SHOW COLUMNS à chaque chargement de page.
  if (!$migrated) {
    $migrated = true;
    db_run_migrations($conn);
  }

  return $conn;
}

/**
 * Exécute les CREATE TABLE et migrations une seule fois.
 * En production, le cache JSON (overrides.json) évite de retomber ici souvent.
 */
function db_run_migrations($conn) {
  $conn->query("CREATE TABLE IF NOT EXISTS site_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(50) NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    image_url TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_item (section, item_name)
  )");

  $conn->query("CREATE TABLE IF NOT EXISTS site_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section VARCHAR(50) NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    price VARCHAR(255) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_item (section, item_name)
  )");

  $conn->query("CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    apartment VARCHAR(100) NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    email VARCHAR(255) NOT NULL,
    client_id INT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");

  // Migrations légères pour les installations déjà existantes.
  $booking_columns = [];
  $columns = $conn->query("SHOW COLUMNS FROM bookings");
  if ($columns) while ($column = $columns->fetch_assoc()) $booking_columns[$column['Field']] = true;
  if (!isset($booking_columns['client_id'])) $conn->query("ALTER TABLE bookings ADD COLUMN client_id INT NULL AFTER email");
  if (!isset($booking_columns['status'])) $conn->query("ALTER TABLE bookings ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER client_id");
  if (!isset($booking_columns['name'])) $conn->query("ALTER TABLE bookings ADD COLUMN name VARCHAR(150) NOT NULL DEFAULT '' AFTER apartment");
  if (!isset($booking_columns['phone'])) $conn->query("ALTER TABLE bookings ADD COLUMN phone VARCHAR(30) NOT NULL DEFAULT '' AFTER email");

  $conn->query("CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");

  // NB : email en VARCHAR(190) — limite d'index InnoDB utf8mb4 (max 1000 octets).
  $conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    remember_token VARCHAR(64) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");

  $conn->query("CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");

  $conn->query("CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255) NOT NULL DEFAULT '',
    slug VARCHAR(190) NOT NULL UNIQUE,
    image VARCHAR(500) NOT NULL DEFAULT '',
    excerpt TEXT NOT NULL,
    content LONGTEXT NOT NULL,
    video_url VARCHAR(500) NOT NULL DEFAULT '',
    published TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )");

  // Migration : ajouter la colonne video_url si elle n'existe pas.
  $bp_cols = [];
  $bp_res = @$conn->query("SHOW COLUMNS FROM blog_posts");
  if ($bp_res) while ($c = $bp_res->fetch_assoc()) $bp_cols[$c['Field']] = true;
  if (!isset($bp_cols['video_url'])) $conn->query("ALTER TABLE blog_posts ADD COLUMN video_url VARCHAR(500) NOT NULL DEFAULT '' AFTER content");
}

function db_blog_seed() {
  $conn = db_connect();
  if (!$conn) return;

  // Vérifier que la colonne video_url existe ; sinon, recréer la table.
  $bp_cols = [];
  $bp_res = @$conn->query("SHOW COLUMNS FROM blog_posts");
  if ($bp_res) while ($c = $bp_res->fetch_assoc()) $bp_cols[$c['Field']] = true;
  if (!empty($bp_cols) && !isset($bp_cols['video_url'])) {
    $conn->query("DROP TABLE IF EXISTS blog_posts");
    $conn->query("CREATE TABLE blog_posts (
      id INT AUTO_INCREMENT PRIMARY KEY,
      title VARCHAR(255) NOT NULL,
      subtitle VARCHAR(255) NOT NULL DEFAULT '',
      slug VARCHAR(190) NOT NULL UNIQUE,
      image VARCHAR(500) NOT NULL DEFAULT '',
      excerpt TEXT NOT NULL,
      content LONGTEXT NOT NULL,
      video_url VARCHAR(500) NOT NULL DEFAULT '',
      published TINYINT(1) NOT NULL DEFAULT 0,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
  }

  $check = $conn->query("SELECT COUNT(*) AS cnt FROM blog_posts");
  if (!$check) return;
  if ((int)$check->fetch_assoc()['cnt'] > 0) return;

  // [title, subtitle, slug, image, excerpt, content, video_url]
  $posts = [
    ["Visite de l'appartement JASMAIN (T3)", "Découvrez notre T3 au 2ème étage — 3 chambres, vue panoramique", 'visite-appartement-jasmain', 'images/site-live/appartements/JASMAIN/01-principale.jpg',
     "Plongez dans l'univers de l'appartement JASMAIN, un T3 spacieux et lumineux situé au 2ème étage de la Résidence Rubis.",
     '<h2>L\'appartement JASMAIN — Un T3 d\'exception</h2><p>L\'appartement JASMAIN est un T3 situé au 2ème étage de la Résidence Rubis. Avec ses 3 chambres spacieuses, ses finitions soignées et sa vue imprenable, il offre tout le confort nécessaire pour un séjour agréable à Cotonou.</p><h3>Caractéristiques</h3><ul><li><strong>Type :</strong> T3 — 3 chambres</li><li><strong>Étage :</strong> 2ème étage</li><li><strong>Équipements :</strong> Climatisation, Wi-Fi haut débit, cuisine équipée</li><li><strong>Vue :</strong> Panoramique sur la ville</li></ul><h3>Confort et standing</h3><p>Chaque chambre dispose de sa propre salle de bain indépendante. La cuisine moderne est entièrement équipée pour préparer vos repas. Le salon offre un espace de détente idéal après une journée bien remplie.</p><p>Cet appartement est parfait pour les familles ou les groupes recherchant un espace de vie confortable et élégant au cœur de Cotonou.</p>', 'https://www.youtube.com/embed/NiwHkg_HiP8?si=WC_G537O9LzYq52q&autoplay=1&mute=1'],
    ["Visite de l'appartement OCCITANIE (T2)", 'Un T2 moderne et lumineux au cœur de la Résidence Rubis', 'visite-appartement-occitanie', 'images/site-live/appartements/OCCITANIE/01-principale.jpg',
     "Découvrez l'appartement OCCITANIE, un T2 moderne et confortable idéal pour vos séjours à Cotonou.",
     '<h2>L\'appartement OCCITANIE — Confort moderne</h2><p>L\'appartement OCCITANIE est un T2 moderne situé dans la Résidence Rubis. Avec ses 2 chambres élégantes, ses finitions haut de gamme et son ambiance chaleureuse, il est l\'endroit idéal pour un séjour réussi à Cotonou.</p><h3>Caractéristiques</h3><ul><li><strong>Type :</strong> T2 — 2 chambres</li><li><strong>Équipements :</strong> Climatisation, Wi-Fi gratuit, cuisine moderne</li><li><strong>Design :</strong> Intérieur contemporain avec éclairage soigné</li></ul><h3>Un cadre de vie agréable</h3><p>L\'appartement OCCITANIE vous séduira par son design contemporain et ses prestations de qualité. La cuisine ouverte sur le salon crée un espace de vie lumineux et convivial. Les chambres spacieuses garantissent un confort optimal.</p><p>Que vous soyez en déplacement professionnel ou en vacances, cet appartement saura répondre à toutes vos attentes.</p>', 'https://www.youtube.com/embed/tyvsKxE-eHU?si=_exskQmS3l0fA0Ee&autoplay=1&mute=1'],
    ['Bienvenue à la Résidence Rubis', 'Votre résidence de standing à Cotonou', 'bienvenue-residence-rubis', 'images/site-live/hero/hero-1.jpg',
     'Découvrez la Résidence Rubis, une résidence de luxe située au cœur de Cotonou avec vue sur mer, wifi gratuit et climatisation.',
     '<h2>Une expérience unique à Cotonou</h2><p>La Résidence Rubis vous accueille dans un cadre élégant et moderne au cœur de Cotonou, capitale économique du Bénin. Nos appartements meublés de standing sont idéaux pour vos séjours personnels ou professionnels.</p><h3>Nos atouts</h3><ul><li><strong>Vue sur mer</strong> — Profitez d\'une vue imprenable sur l\'océan depuis nos appartements.</li><li><strong>Wi-Fi gratuit</strong> — Restez connecté tout au long de votre séjour.</li><li><strong>Climatisation</strong> — Chaque appartement est équipé d\'une climatisation performante.</li><li><strong>Sécurité 24/24</strong> — Votre sécurité est notre priorité.</li></ul><h3>Un emplacement stratégique</h3><p>Implantés non loin du quartier des ambassades et très proche de la mer, nos appartements vous offrent la liberté de profiter de tout ce que Cotonou a à offrir. Que vous soyez en voyage d\'affaires ou en vacances, la Résidence Rubis est votre chez-vous.</p>', ''],
    ['Cotonou : 5 incontournables à découvrir', 'Explorez la capitale économique du Bénin', 'cotonou-incontournables', 'images/Cotonou.jpg',
     'Le Marché Dantokpa, la Route des Pêcheurs, le Port de Cotonou et bien d\'autres merveilles vous attendent.',
     '<h2>Cotonou, ville vibrante de vie</h2><p>Cotonou est la capitale économique du Bénin, une ville dynamique où tradition et modernité se côtoient.</p><h3>1. Le Marché Dantokpa</h3><p>Le plus grand marché à ciel ouvert d\'Afrique de l\'Ouest. Vous y trouverez de tout : tissus wax, artisanat local, épices et produits frais.</p><h3>2. La Route des Pêcheurs</h3><p>Cette pittoresque corniche longeant l\'océan Atlantique offre des vues spectaculaires.</p><h3>3. Le Port de Cotonou</h3><p>Le principal port du pays, vrai carrefour commercial.</p><h3>4. La Cathédrale de Cotonou</h3><p>Un chef-d\'œuvre architectural au centre-ville.</p><h3>5. Les plages de Fidjrossè</h3><p>Des plages de sable fin idéales pour une escapade.</p>', ''],
    ['Le Bénin : berceau du vaudou et de la culture africaine', 'Un pays d\'histoire, de culture et de découvertes', 'benin-culture-vaudou', 'images/abomey.jpg',
     'Des palais royaux d\'Abomey à la Porte du Non-Retour en passant par les Tata Somba, le Bénin regorge de trésors.',
     '<h2>Un patrimoine exceptionnel</h2><p>Le Bénin, berceau du vaudou et du royaume du Dahomey, est un pays riche en histoire et en culture.</p><h3>Les Palais Royaux d\'Abomey</h3><p>Classés au patrimoine mondial de l\'UNESCO, les anciens palais des rois du Dahomey témoignent de la grandeur de cette civilisation.</p><h3>La Porte du Non-Retour à Ouidah</h3><p>Mémorial émouvant de la traite des esclaves.</p><h3>Les Tata Somba</h3><p>Ces habitations fortifiées du nord du Bénin sont un exemple remarquable d\'architecture traditionnelle africaine.</p><p>En séjournant à la Résidence Rubis, vous aurez un point de départ idéal pour explorer toutes ces merveilles.</p>', ''],
  ];

  $stmt = $conn->prepare("INSERT INTO blog_posts (title, subtitle, slug, image, excerpt, content, video_url, published) VALUES (?,?,?,?,?,?,?,1)");
  foreach ($posts as $p) {
    $stmt->bind_param('sssssss', $p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6]);
    $stmt->execute();
  }
}

function db_get_image($section, $item_name) {
  $conn = db_connect();
  if (!$conn) return null;
  $stmt = $conn->prepare("SELECT image_url FROM site_images WHERE section = ? AND item_name = ?");
  $stmt->bind_param('ss', $section, $item_name);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) return $row['image_url'];
  return null;
}

function db_set_image($section, $item_name, $image_url) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("INSERT INTO site_images (section, item_name, image_url) VALUES (?, ?, ?)
                          ON DUPLICATE KEY UPDATE image_url = ?");
  $stmt->bind_param('ssss', $section, $item_name, $image_url, $image_url);
  return $stmt->execute();
}

function db_delete_image($section, $item_name) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("DELETE FROM site_images WHERE section = ? AND item_name = ?");
  $stmt->bind_param('ss', $section, $item_name);
  return $stmt->execute();
}

function db_image_path_exists($url) {
  if ($url === '') return false;
  if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) return true;
  $path = strtok($url, '?');
  if (is_file($path)) return true;
  return is_file(__DIR__ . '/../' . $path);
}

function db_get_all_images() {
  $conn = db_connect();
  if (!$conn) return [];
  $res = $conn->query("SELECT section, item_name, image_url, updated_at FROM site_images");
  $rows = [];
  while ($row = $res->fetch_assoc()) $rows[] = $row;
  return $rows;
}

function db_get_price($section, $item_name) {
  $conn = db_connect();
  if (!$conn) return null;
  $stmt = $conn->prepare("SELECT price FROM site_prices WHERE section = ? AND item_name = ?");
  $stmt->bind_param('ss', $section, $item_name);
  $stmt->execute();
  $res = $stmt->get_result();
  if ($row = $res->fetch_assoc()) return $row['price'];
  return null;
}

function db_set_price($section, $item_name, $price) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("INSERT INTO site_prices (section, item_name, price) VALUES (?, ?, ?)
                          ON DUPLICATE KEY UPDATE price = ?");
  $stmt->bind_param('ssss', $section, $item_name, $price, $price);
  return $stmt->execute();
}

function db_delete_price($section, $item_name) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("DELETE FROM site_prices WHERE section = ? AND item_name = ?");
  $stmt->bind_param('ss', $section, $item_name);
  return $stmt->execute();
}

function db_get_all_prices() {
  $conn = db_connect();
  if (!$conn) return [];
  $res = $conn->query("SELECT section, item_name, price, updated_at FROM site_prices");
  $rows = [];
  while ($row = $res->fetch_assoc()) $rows[] = $row;
  return $rows;
}

function db_save_booking($apartment, $name, $check_in, $check_out, $email, $client_id = null, $phone = '') {
  $conn = db_connect();
  if (!$conn) return false;
  $status = 'pending';
  $phone = trim($phone);
  $stmt = $conn->prepare("INSERT INTO bookings (apartment, name, check_in, check_out, email, phone, client_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param('ssssssis', $apartment, $name, $check_in, $check_out, $email, $phone, $client_id, $status);
  return $stmt->execute();
}

function db_booking_is_available($apartment, $check_in, $check_out) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("SELECT id FROM bookings WHERE apartment = ? AND status != 'cancelled' AND check_in < ? AND check_out > ? LIMIT 1");
  $stmt->bind_param('sss', $apartment, $check_out, $check_in);
  $stmt->execute();
  return $stmt->get_result()->num_rows === 0;
}

function db_save_contact_message($name, $email, $message) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
  $stmt->bind_param('sss', $name, $email, $message);
  return $stmt->execute();
}

function db_get_bookings() {
  $conn = db_connect();
  if (!$conn) return [];
  $res = $conn->query("SELECT id, apartment, name, check_in, check_out, email, phone, client_id, status, created_at FROM bookings ORDER BY created_at DESC");
  $rows = [];
  while ($row = $res->fetch_assoc()) $rows[] = $row;
  return $rows;
}

function db_get_bookings_for_user($client_id, $email) {
  $conn = db_connect();
  if (!$conn) return [];
  $stmt = $conn->prepare("SELECT id, apartment, name, check_in, check_out, email, phone, status, created_at FROM bookings WHERE client_id = ? OR (client_id IS NULL AND email = ?) ORDER BY check_in DESC, created_at DESC");
  $stmt->bind_param('is', $client_id, $email);
  $stmt->execute();
  $rows = [];
  $res = $stmt->get_result();
  while ($row = $res->fetch_assoc()) $rows[] = $row;
  return $rows;
}

function db_update_booking_status($booking_id, $status) {
  if (!in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'], true)) return false;
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
  $stmt->bind_param('si', $status, $booking_id);
  return $stmt->execute();
}

function db_get_contact_messages() {
  $conn = db_connect();
  if (!$conn) return [];
  $res = $conn->query("SELECT name, email, message, created_at FROM contact_messages ORDER BY created_at DESC");
  $rows = [];
  while ($row = $res->fetch_assoc()) $rows[] = $row;
  return $rows;
}

/* ============================================================
 * Comptes clients (connexion / inscription)
 * ============================================================ */

function db_user_find_by_email($email) {
  $conn = db_connect();
  if (!$conn) return null;
  $stmt = $conn->prepare("SELECT id, name, email, password_hash, remember_token, created_at FROM users WHERE email = ?");
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $res = $stmt->get_result();
  return $res->fetch_assoc();
}

function db_user_create($name, $email, $password_hash) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
  $stmt->bind_param('sss', $name, $email, $password_hash);
  return $stmt->execute();
}

function db_user_set_remember_token($user_id, $token) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
  $stmt->bind_param('si', $token, $user_id);
  return $stmt->execute();
}

function db_user_find_by_token($token) {
  $conn = db_connect();
  if (!$conn) return null;
  $stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE remember_token = ?");
  $stmt->bind_param('s', $token);
  $stmt->execute();
  $res = $stmt->get_result();
  return $res->fetch_assoc();
}

function db_password_reset_create($user_id, $token) {
  $conn = db_connect();
  if (!$conn) return false;
  $hash = hash('sha256', $token);
  $conn->query("DELETE FROM password_resets WHERE expires_at < NOW()");
  $stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
  $stmt->bind_param('i', $user_id);
  $stmt->execute();
  $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
  $stmt->bind_param('is', $user_id, $hash);
  return $stmt->execute();
}

function db_password_reset_consume($token, $password_hash) {
  $conn = db_connect();
  if (!$conn) return false;
  $hash = hash('sha256', $token);
  $stmt = $conn->prepare("SELECT id, user_id FROM password_resets WHERE token_hash = ? AND expires_at >= NOW() LIMIT 1");
  $stmt->bind_param('s', $hash);
  $stmt->execute();
  $reset = $stmt->get_result()->fetch_assoc();
  if (!$reset) return false;
  $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
  $stmt->bind_param('si', $password_hash, $reset['user_id']);
  if (!$stmt->execute()) return false;
  $stmt = $conn->prepare("DELETE FROM password_resets WHERE id = ?");
  $stmt->bind_param('i', $reset['id']);
  return $stmt->execute();
}

/* ============================================================
 * Blog — Articles
 * ============================================================ */

function db_blog_get_all($published_only = false) {
  $conn = db_connect();
  if (!$conn) return [];
  $sql = "SELECT id, title, subtitle, slug, image, excerpt, content, video_url, published, created_at, updated_at FROM blog_posts";
  if ($published_only) $sql .= " WHERE published = 1";
  $sql .= " ORDER BY created_at DESC";
  $res = @$conn->query($sql);
  if (!$res) return [];
  $rows = [];
  while ($row = $res->fetch_assoc()) $rows[] = $row;
  return $rows;
}

function db_blog_get_by_slug($slug) {
  $conn = db_connect();
  if (!$conn) return null;
  $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE slug = ?");
  $stmt->bind_param('s', $slug);
  $stmt->execute();
  $res = $stmt->get_result();
  return $res->fetch_assoc() ?: null;
}

function db_blog_get_by_id($id) {
  $conn = db_connect();
  if (!$conn) return null;
  $stmt = $conn->prepare("SELECT * FROM blog_posts WHERE id = ?");
  $stmt->bind_param('i', $id);
  $stmt->execute();
  $res = $stmt->get_result();
  return $res->fetch_assoc() ?: null;
}

function db_blog_save($id, $title, $subtitle, $slug, $image, $excerpt, $content, $published, $video_url = '') {
  $conn = db_connect();
  if (!$conn) return false;
  if ($id) {
    $stmt = $conn->prepare("UPDATE blog_posts SET title=?, subtitle=?, slug=?, image=?, excerpt=?, content=?, video_url=?, published=? WHERE id=?");
    $stmt->bind_param('sssssssii', $title, $subtitle, $slug, $image, $excerpt, $content, $video_url, $published, $id);
  } else {
    $stmt = $conn->prepare("INSERT INTO blog_posts (title, subtitle, slug, image, excerpt, content, video_url, published) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param('sssssssi', $title, $subtitle, $slug, $image, $excerpt, $content, $video_url, $published);
  }
  return $stmt->execute();
}

function db_blog_delete($id) {
  $conn = db_connect();
  if (!$conn) return false;
  $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
  $stmt->bind_param('i', $id);
  return $stmt->execute();
}

/* ============================================================
 * Cache des surcharges (images + prix)
 *
 * Les surcharges sont stockées dans un fichier JSON afin d'éviter
 * ~25 requêtes MySQL à chaque chargement de page. Le fichier est
 * invalidé par l'admin (actions.php) à chaque modification.
 * ============================================================ */

function db_cache_file() {
  return __DIR__ . '/cache/overrides.json';
}

/**
 * Lit le cache s'il existe et qu'il est assez récent (TTL par défaut : 10 min).
 */
function db_cache_read($ttl = 600) {
  $file = db_cache_file();
  if (!is_file($file) || (time() - filemtime($file)) > $ttl) return null;
  $data = json_decode((string)file_get_contents($file), true);
  if (!is_array($data) || !isset($data['images'], $data['prices'])) return null;
  return $data;
}

/**
 * Écrit (ou rafraîchit) le cache des surcharges. Échec silencieux :
 * le site continue de fonctionner même si le fichier n'est pas inscriptible.
 */
function db_cache_write($data) {
  $file = db_cache_file();
  $dir = dirname($file);
  if (!is_dir($dir)) {
    if (!@mkdir($dir, 0755, true)) return false;
  }
  return @file_put_contents($file, json_encode($data)) !== false;
}

/**
 * Invalide le cache (appelé par l'admin après chaque modification).
 */
function db_cache_invalidate() {
  $file = db_cache_file();
  if (is_file($file)) @unlink($file);
}

/**
 * Retourne les surcharges ['images' => [clé => url], 'prices' => [clé => prix]]
 * via le cache si possible, sinon par une requête à la base (puis mise en cache).
 * Retourne null si la base est injoignable : le site utilisera les valeurs par défaut.
 */
function db_get_overrides() {
  $cached = db_cache_read();
  if ($cached !== null) return $cached;

  $conn = db_connect();
  if (!$conn) {
    // Base indisponible : on retombe sur un cache périmé s'il existe.
    $file = db_cache_file();
    if (is_file($file)) {
      $data = json_decode((string)file_get_contents($file), true);
      if (is_array($data)) return $data;
    }
    return null;
  }

  $images = [];
  foreach (db_get_all_images() as $row) {
    $images[$row['section'] . '::' . $row['item_name']] = $row['image_url'];
  }

  $prices = [];
  foreach (db_get_all_prices() as $row) {
    $prices[$row['section'] . '::' . $row['item_name']] = $row['price'];
  }

  $data = ['images' => $images, 'prices' => $prices];
  db_cache_write($data);
  return $data;
}
