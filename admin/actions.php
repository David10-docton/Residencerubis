<?php
require_once 'auth.php';
admin_require_login();
require_once __DIR__ . '/../includes/db.php';

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

$tab = isset($_POST['tab']) && $_POST['tab'] === 'prices' ? '?tab=prices' : '';
$anchor = preg_replace('/[^a-z0-9\-]/', '', strtolower($_POST['anchor'] ?? ''));
if ($anchor !== '' && $tab === '') $tab = '#' . $anchor;

if ($section === '' || $item === '') {
  redirect_with_message('Paramètres manquants.', 'error', $tab);
}

$section = preg_replace('/[^a-zA-Z0-9_\-]/', '', $section);
$item = preg_replace('/[^a-zA-Z0-9_\-àâäéèêëîïôöùûüçÀÂÄÉÈÊËÎÏÔÖÙÛÜÇ .]/', '', $item);

switch ($action) {

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

  default:
    redirect_with_message('Action inconnue.', 'error');
}
