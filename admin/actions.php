<?php
require_once 'auth.php';
admin_require_login();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/email.php';

function redirect_with_message($msg, $type = 'success', $suffix = '') {
  $_SESSION['admin_msg'] = $msg;
  $_SESSION['admin_type'] = $type;
  header('Location: index.php' . $suffix);
  exit;
}

// Protection CSRF : toute écriture doit porter un jeton valide.
if (!csrf_verify()) {
  redirect_with_message('Session expirée : veuillez réessayer.', 'error');
}

$action = $_POST['action'] ?? '';
$section = $_POST['section'] ?? '';
$item = $_POST['item'] ?? '';

$tab = isset($_POST['tab']) ? '?tab=' . preg_replace('/[^a-z]/', '', $_POST['tab']) : '';
$anchor = preg_replace('/[^a-z0-9\-]/', '', strtolower($_POST['anchor'] ?? ''));
if ($anchor !== '' && $tab === '') $tab = '#' . $anchor;

// Les actions blog n'ont pas besoin de section/item
if (!in_array($action, ['blog_save', 'blog_delete'], true)) {
  if ($section === '' || $item === '') {
    redirect_with_message('Paramètres manquants.', 'error', $tab);
  }
  $section = preg_replace('/[^a-zA-Z0-9_\-]/', '', $section);
  $item = preg_replace('/[^a-zA-Z0-9_\-àâäéèêëîïôöùûüçÀÂÄÉÈÊËÎÏÔÖÙÛÜÇ .]/', '', $item);
}

switch ($action) {

  case 'booking_status':
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if ($booking_id <= 0 || !db_update_booking_status($booking_id, $status)) {
      redirect_with_message('Impossible de mettre à jour le statut de cette réservation.', 'error', '?tab=requests');
    }

    // Récupérer les infos de la réservation pour envoyer la notification
    $conn_book = db_connect();
    $bk_row = null;
    if ($conn_book) {
      $bk = $conn_book->prepare("SELECT name, email, phone, apartment, check_in, check_out FROM bookings WHERE id = ?");
      $bk->bind_param('i', $booking_id);
      $bk->execute();
      $bk_res = $bk->get_result();
      $bk_row = $bk_res->fetch_assoc();
      if ($bk_row && !empty($bk_row['email'])) {
        send_booking_status_update([
          'client_name' => $bk_row['name'],
          'email'       => $bk_row['email'],
          'apartment'   => $bk_row['apartment'],
          'check_in'    => $bk_row['check_in'],
          'check_out'   => $bk_row['check_out'],
          'status'      => $status,
        ]);
      }
    }

    // Construire le lien WhatsApp pour envoyer la notification
    $whatsapp_link = '';
    if ($bk_row && !empty($bk_row['phone'])) {
      $whatsapp_link = whatsapp_status_url($bk_row['phone'], $status, $bk_row['apartment'] ?? '');
    }
    // Stocker le lien WhatsApp séparément pour un rendu sécurisé dans le template
    $_SESSION['admin_whatsapp_link'] = $whatsapp_link;
    redirect_with_message('Statut de la réservation mis à jour. Le client a été notifié par email.', 'success', '?tab=requests');
    break;

  case 'upload':
    if (empty($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
      redirect_with_message('Veuillez sélectionner un fichier image.', 'error', $tab);
    }

    $file = $_FILES['photo'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
      redirect_with_message('Erreur lors de l\'envoi du fichier (code ' . $file['error'] . ').', 'error', $tab);
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!isset($allowed[$mime])) {
      redirect_with_message('Format non autorisé. Utilisez JPG, PNG, GIF ou WEBP.', 'error', $tab);
    }

    if ($file['size'] > 8 * 1024 * 1024) {
      redirect_with_message('Image trop lourde (maximum 8 Mo).', 'error', $tab);
    }

    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
      mkdir($upload_dir, 0755, true);
    }

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $item), '-'));
    $filename = $slug . '-' . time() . '.' . $allowed[$mime];
    $dest = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
      redirect_with_message('Impossible d\'enregistrer l\'image sur le serveur.', 'error', $tab);
    }

    $url = 'uploads/' . $filename;
    db_set_image($section, $item, $url);
    db_cache_invalidate();
    redirect_with_message('Photo remplacée avec succès !', 'success', $tab);
    break;

  case 'update':
    $url = trim($_POST['image_url'] ?? '');
    if ($url === '') {
      redirect_with_message('L\'URL ne peut pas être vide.', 'error', $tab);
    }
    if (!preg_match('#^https?://#i', $url) && strpos($url, 'uploads/') !== 0) {
      redirect_with_message('URL invalide. Elle doit commencer par http:// ou https://', 'error', $tab);
    }
    db_set_image($section, $item, $url);
    db_cache_invalidate();
    redirect_with_message('URL mise à jour avec succès !', 'success', $tab);
    break;

  case 'reset':
    $conn = db_connect();
    if ($conn) {
      $stmt = $conn->prepare("SELECT image_url FROM site_images WHERE section = ? AND item_name = ?");
      $stmt->bind_param('ss', $section, $item);
      $stmt->execute();
      $res = $stmt->get_result();
      if ($row = $res->fetch_assoc()) {
        $old = $row['image_url'];
        if (strpos($old, 'uploads/') === 0) {
          $file = __DIR__ . '/../' . $old;
          if (is_file($file)) unlink($file);
        }
      }
    }
    db_delete_image($section, $item);
    db_cache_invalidate();
    redirect_with_message('Photo réinitialisée à l\'image par défaut.', 'success', $tab);
    break;

  case 'price_update':
    $price = trim($_POST['price'] ?? '');
    if ($price === '') {
      redirect_with_message('Le prix ne peut pas être vide.', 'error', '?tab=prices');
    }
    if (mb_strlen($price) > 255) {
      redirect_with_message('Prix trop long (maximum 255 caractères).', 'error', '?tab=prices');
    }
    db_set_price($section, $item, $price);
    db_cache_invalidate();
    redirect_with_message('Prix mis à jour avec succès !', 'success', '?tab=prices');
    break;

  case 'price_reset':
    db_delete_price($section, $item);
    db_cache_invalidate();
    redirect_with_message('Prix réinitialisé au tarif par défaut.', 'success', '?tab=prices');
    break;

  case 'blog_save':
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $slug = strtolower(trim(preg_replace('/[^a-z0-9\-]/', '-', strtolower(trim($_POST['slug'] ?? ''))), '-'));
    $image = trim($_POST['image'] ?? '');
    $video_url = trim($_POST['video_url'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $published = (int)($_POST['published'] ?? 0);

    if ($title === '' || $slug === '' || $content === '') {
      redirect_with_message('Le titre, le slug et le contenu sont obligatoires.', 'error', '?tab=blog');
    }
    if (mb_strlen($slug) > 255) {
      redirect_with_message('Le slug est trop long (max 255 caractères).', 'error', '?tab=blog');
    }

    // Vérifier l'unicité du slug (exclure l'article en cours de modification)
    $conn = db_connect();
    if ($conn) {
      $check = $conn->prepare("SELECT id FROM blog_posts WHERE slug = ? AND id != ?");
      $check->bind_param('si', $slug, $id);
      $check->execute();
      if ($check->get_result()->num_rows > 0) {
        redirect_with_message('Ce slug existe déjà. Choisissez-en un autre.', 'error', '?tab=blog');
      }
    }

    if (db_blog_save($id ?: null, $title, $subtitle, $slug, $image, $excerpt, $content, $published, $video_url)) {
      redirect_with_message($id ? 'Article mis à jour avec succès !' : 'Article créé avec succès !', 'success', '?tab=blog');
    } else {
      redirect_with_message('Une erreur est survenue lors de l\'enregistrement.', 'error', '?tab=blog');
    }
    break;

  case 'blog_delete':
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
      redirect_with_message('ID invalide.', 'error', '?tab=blog');
    }
    if (db_blog_delete($id)) {
      redirect_with_message('Article supprimé.', 'success', '?tab=blog');
    } else {
      redirect_with_message('Impossible de supprimer cet article.', 'error', '?tab=blog');
    }
    break;

  default:
    redirect_with_message('Action inconnue.', 'error');
}
