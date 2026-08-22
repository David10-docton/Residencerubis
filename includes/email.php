<?php
/**
 * Module emails — Résidence Rubis
 *
 * Envoie des emails HTML formatés (marque bordeaux + or) pour :
 *  - Confirmation de réservation au client
 *  - Notification de réservation à l'admin
 *  - Mise à jour du statut d'une réservation
 *  - Confirmation de réservation groupée (panier)
 *
 * Utilise PHPMailer SMTP si disponible, sinon fallback mail().
 */

// Charger l'autoload de Composer (PHPMailer)
$has_phpmailer = false;
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
  require_once __DIR__ . '/../vendor/autoload.php';
  if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    $has_phpmailer = true;
  }
}

/**
 * Envoie un email HTML avec le branding de la Résidence Rubis.
 *
 * @param string $to        Adresse email du destinataire
 * @param string $subject   Objet du mail
 * @param string $html      Corps HTML
 * @param string $text      Corps plaintext (fallback auto si vide)
 * @param string $replyTo   Adresse Reply-To (optionnel)
 * @return bool
 */
function send_branded_email($to, $subject, $html, $text = '', $replyTo = '') {
  global $has_phpmailer;
  $to = trim($to);
  if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) return false;

  $site_name  = 'Résidence Rubis';
  $site_email = 'residencerubis4@gmail.com';
  $site_phone = '(+229) 01 96 77 13 13';
  $reply_addr = ($replyTo !== '') ? $replyTo : $site_email;

  // Plaintext fallback
  if ($text === '') {
    $text = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</h1>', '</h2>', '</h3>', '</li>', '</tr>'], ["\n", "\n", "\n", "\n", "\n\n", "\n\n", "\n", "\n", "\n"], $html));
    $text = preg_replace('/\n{3,}/', "\n\n", $text);
  }

  // Configuration SMTP — fallback durci pour InfinityFree
  $smtp_host     = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
  $smtp_port     = (int)(getenv('SMTP_PORT') ?: 587);
  $smtp_user     = getenv('SMTP_USER') ?: $site_email;
  $smtp_pass     = getenv('SMTP_PASS') ?: 'mtzedodoghbfttpp';
  $smtp_encrypt  = getenv('SMTP_ENCRYPTION') ?: 'tls';

  // Si PHPMailer n'est pas disponible, fallback sur mail()
  if (!$has_phpmailer) {
    if (!function_exists('mail')) return false;
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$site_name} <{$site_email}>\r\n";
    $headers .= "Reply-To: {$reply_addr}\r\n";
    return @mail($to, $subject, $html, $headers);
  }

  try {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    // Configuration SMTP
    $mail->isSMTP();
    $mail->Host       = $smtp_host;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp_user;
    $mail->Password   = $smtp_pass;
    $mail->SMTPSecure = ($smtp_encrypt === 'ssl') ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $smtp_port;
    $mail->CharSet    = 'UTF-8';

    // Expéditeur
    $mail->setFrom($site_email, $site_name);
    $mail->Sender = $site_email;
    $mail->addAddress($to);

    // Reply-To
    $mail->addReplyTo($reply_addr, $site_name);

    // Contenu
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $html;
    $mail->AltBody = $text;

    $mail->send();
    return true;
  } catch (\PHPMailer\PHPMailer\Exception $e) {
    // Log l'erreur
    error_log('[Résidence Rubis] Erreur SMTP à ' . $to . ' : ' . $e->getMessage());
    // Fallback mail()
    if (function_exists('mail')) {
      $headers  = "MIME-Version: 1.0\r\n";
      $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
      $headers .= "From: {$site_name} <{$site_email}>\r\n";
      $headers .= "Reply-To: {$reply_addr}\r\n";
      return @mail($to, $subject, $html, $headers);
    }
    return false;
  }
}

/**
 * Template HTML pour les emails de réservation.
 */
function email_layout($data) {
  $site_name  = $data['site_name']  ?? 'Résidence Rubis';
  $site_email = $data['site_email'] ?? 'residencerubis4@gmail.com';
  $site_phone = $data['site_phone'] ?? '(+229) 01 96 77 13 13';
  $title      = $data['title']      ?? '';
  $greeting   = $data['greeting']   ?? '';
  $body_html  = $data['body_html']  ?? '';
  $footer     = $data['footer_text'] ?? 'Merci de votre confiance.';

  return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#FDF8F0;font-family:Georgia,'Times New Roman',serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#FDF8F0;padding:32px 16px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#FFFFFF;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.06);">
  <tr><td style="background:linear-gradient(135deg,#B85D3F,#9A4A30);padding:32px 40px;text-align:center;">
    <h1 style="margin:0;color:#FFFFFF;font-size:24px;font-weight:700;letter-spacing:0.5px;">{$site_name}</h1>
    <p style="margin:8px 0 0;color:rgba(255,255,255,0.7);font-size:13px;letter-spacing:1px;">VOTRE RÉSIDENCE À COTONOU</p>
  </td></tr>
  <tr><td style="height:4px;background:linear-gradient(90deg,#DCB159,#E8C87A);"></td></tr>
  <tr><td style="padding:36px 40px;">
    {$title}
    {$greeting}
    <div style="font-size:15px;line-height:1.7;color:#3D3D3D;">{$body_html}</div>
  </td></tr>
  <tr><td style="background:#FAF5ED;padding:24px 40px;border-top:1px solid #EBE3DA;">
    <p style="margin:0 0 8px;font-size:13px;color:#9A8E85;text-align:center;">{$footer}</p>
    <table width="100%" cellpadding="0" cellspacing="0"><tr>
      <td style="text-align:center;padding:12px 0 0;">
        <span style="font-size:12px;color:#9A8E85;">📞 {$site_phone} &nbsp;|&nbsp; ✉ {$site_email}</span>
      </td>
    </tr></table>
    <p style="margin:12px 0 0;font-size:11px;color:#C4B8AA;text-align:center;">
      © {$data['year']} {$site_name} — Cotonou, Bénin
    </p>
  </td></tr>
</table>
</td></tr></table>
</body>
</html>
HTML;
}

/* ============================================================
 * Confirmation de réservation — CLIENT
 * ============================================================ */
function send_booking_confirmation_to_client($data) {
  $client_name     = $data['client_name'] ?? '';
  $email           = $data['email'] ?? '';
  $apartment       = $data['apartment'] ?? '';
  $check_in        = $data['check_in'] ?? '';
  $check_out       = $data['check_out'] ?? '';
  $nights          = $data['nights'] ?? 1;
  $price_per_night = $data['price_per_night'] ?? '';
  $total           = $data['total'] ?? 0;
  $site_phone      = '(+229) 01 96 77 13 13';
  $site_email      = 'residencerubis4@gmail.com';

  if ($email === '' || $client_name === '' || $apartment === '') return false;

  $date_in  = date('d/m/Y', strtotime($check_in));
  $date_out = date('d/m/Y', strtotime($check_out));
  $total_fmt = number_format($total, 0, ',', ' ');
  $price_label = is_numeric(str_replace(' ', '', $price_per_night))
    ? number_format((int)str_replace(' ', '', $price_per_night), 0, ',', ' ') . ' F'
    : $price_per_night;

  $title = '<h2 style="margin:0 0 8px;font-size:22px;color:#B85D3F;font-weight:700;">Confirmation de réservation</h2>';
  $greeting = '<p style="margin:0 0 20px;font-size:16px;color:#2C2C2C;">Bonjour <strong>' . htmlspecialchars($client_name) . '</strong>,</p>';

  $body = <<<HTML
<p style="margin:0 0 20px;">Merci pour votre demande de réservation. Voici le récapitulatif :</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;background:#FAF5ED;border-radius:12px;border:1px solid #EBE3DA;">
  <tr><td style="padding:20px 24px;"><table width="100%" cellpadding="0" cellspacing="0">
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;width:40%;">Appartement</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$apartment}</td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;">Arrivée</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$date_in}</td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;">Départ</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$date_out}</td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;">Durée</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$nights} nuit(s)</td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;">Tarif / nuit</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$price_label} F CFA</td></tr>
    <tr><td style="padding:8px 0 0;font-size:14px;color:#6B5E55;border-top:2px solid #EBE3DA;font-weight:700;">Total estimé</td><td style="padding:8px 0 0;font-size:18px;color:#B85D3F;font-weight:700;border-top:2px solid #EBE3DA;">{$total_fmt} F CFA</td></tr>
  </table></td></tr>
</table>
<p style="margin:0 0 12px;font-size:14px;color:#3D3D3D;"><strong>Statut :</strong> <span style="color:#DCB159;font-weight:700;">⏳ En attente de confirmation</span></p>
<p style="margin:0 0 12px;font-size:14px;color:#3D3D3D;">Notre équipe va vérifier la disponibilité de l'appartement et vous envoyer une confirmation dans les plus brefs délais.</p>
<p style="margin:0;font-size:14px;color:#3D3D3D;">Si vous avez des questions, n'hésitez pas à nous contacter au <strong>{$site_phone}</strong> ou par email à <strong>{$site_email}</strong>.</p>
HTML;

  $html = email_layout([
    'title' => $title, 'greeting' => $greeting, 'body_html' => $body,
    'footer_text' => 'Votre demande est enregistrée. Vous recevrez un email de confirmation dès que notre équipe aura validé vos dates.',
    'year' => date('Y'),
  ]);

  return send_branded_email($email, "Réservation {$apartment} — Résidence Rubis", $html, '', '');
}

/* ============================================================
 * Notification de réservation — ADMIN
 * ============================================================ */
function send_booking_notification_to_admin($data) {
  $client_name = $data['client_name'] ?? '';
  $email       = $data['email'] ?? '';
  $apartment   = $data['apartment'] ?? '';
  $check_in    = $data['check_in'] ?? '';
  $check_out   = $data['check_out'] ?? '';
  $nights      = $data['nights'] ?? 1;
  $total       = $data['total'] ?? 0;
  $phone       = $data['phone'] ?? '';

  $admin_email = getenv('ADMIN_EMAIL') ?: 'residencerubis4@gmail.com';
  $date_in  = date('d/m/Y', strtotime($check_in));
  $date_out = date('d/m/Y', strtotime($check_out));
  $total_fmt = number_format($total, 0, ',', ' ');

  $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
  $admin_url = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/admin/index.php?tab=requests';

  $title = '<h2 style="margin:0 0 8px;font-size:22px;color:#B85D3F;font-weight:700;">🔔 Nouvelle réservation</h2>';
  $greeting = '<p style="margin:0 0 20px;font-size:16px;color:#2C2C2C;">Un nouveau client vient de faire une demande de réservation :</p>';

  $body = <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;background:#FAF5ED;border-radius:12px;border:1px solid #EBE3DA;">
  <tr><td style="padding:20px 24px;"><table width="100%" cellpadding="0" cellspacing="0">
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;width:40%;">Client</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$client_name}</td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;">Email</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$email}</td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;">Téléphone</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$phone}</td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;">Appartement</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$apartment}</td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;">Arrivée</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$date_in}</td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;">Départ</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$date_out}</td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;">Durée</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$nights} nuit(s)</td></tr>
    <tr><td style="padding:8px 0 0;font-size:14px;color:#6B5E55;border-top:2px solid #EBE3DA;font-weight:700;">Total estimé</td><td style="padding:8px 0 0;font-size:18px;color:#B85D3F;font-weight:700;border-top:2px solid #EBE3DA;">{$total_fmt} F CFA</td></tr>
  </table></td></tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
  <tr><td align="center">
    <a href="{$admin_url}" style="display:inline-block;background:linear-gradient(135deg,#B85D3F,#9A4A30);color:#FFFFFF;font-size:16px;font-weight:700;text-decoration:none;padding:14px 36px;border-radius:8px;letter-spacing:0.5px;">Voir la réservation dans l'espace admin</a>
  </td></tr>
</table>
<p style="margin:0 0 12px;font-size:14px;color:#3D3D3D;">Un client vient de réserver. Cliquez sur le bouton ci-dessus pour consulter et gérer cette demande depuis votre espace d'administration.</p>
HTML;

  $html = email_layout([
    'title' => $title, 'greeting' => $greeting, 'body_html' => $body,
    'footer_text' => 'Notification automatique — Résidence Rubis', 'year' => date('Y'),
  ]);

  return send_branded_email($admin_email, "Nouvelle réservation {$apartment} — {$client_name}", $html, '');
}

/* ============================================================
 * Mise à jour du statut — CLIENT
 * ============================================================ */
function send_booking_status_update($data) {
  $client_name = $data['client_name'] ?? '';
  $email       = $data['email'] ?? '';
  $apartment   = $data['apartment'] ?? '';
  $check_in    = $data['check_in'] ?? '';
  $check_out   = $data['check_out'] ?? '';
  $status      = $data['status'] ?? 'pending';

  if ($email === '' || $client_name === '' || $apartment === '') return false;

  $date_in  = date('d/m/Y', strtotime($check_in));
  $date_out = date('d/m/Y', strtotime($check_out));

  $status_labels = [
    'pending'   => ['label' => '⏳ En attente', 'color' => '#DCB159'],
    'confirmed' => ['label' => '✅ Confirmée', 'color' => '#10B981'],
    'cancelled' => ['label' => '❌ Annulée', 'color' => '#DC2626'],
    'completed' => ['label' => '🏁 Terminée', 'color' => '#6B5E55'],
  ];
  $s = $status_labels[$status] ?? $status_labels['pending'];

  $title = '<h2 style="margin:0 0 8px;font-size:22px;color:#B85D3F;font-weight:700;">Mise à jour de votre réservation</h2>';
  $greeting = '<p style="margin:0 0 20px;font-size:16px;color:#2C2C2C;">Bonjour <strong>' . htmlspecialchars($client_name) . '</strong>,</p>';

  $status_color = $s['color'];
  $status_label = $s['label'];

  $body = <<<HTML
<p style="margin:0 0 20px;">Le statut de votre réservation a été mis à jour :</p>
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;background:#FAF5ED;border-radius:12px;border:1px solid #EBE3DA;">
  <tr><td style="padding:20px 24px;"><table width="100%" cellpadding="0" cellspacing="0">
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;width:40%;">Appartement</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$apartment}</td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;">Arrivée</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$date_in}</td></tr>
    <tr><td style="padding:6px 0;font-size:14px;color:#6B5E55;">Départ</td><td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$date_out}</td></tr>
    <tr><td style="padding:8px 0 0;font-size:14px;color:#6B5E55;border-top:2px solid #EBE3DA;font-weight:700;">Nouveau statut</td><td style="padding:8px 0 0;font-size:16px;color:{$status_color};font-weight:700;border-top:2px solid #EBE3DA;">{$status_label}</td></tr>
  </table></td></tr>
</table>
HTML;

  if ($status === 'confirmed') {
    $body .= '<p style="margin:0 0 12px;font-size:14px;color:#10B981;font-weight:700;">🎉 Votre réservation a été confirmée ! Nous avons hâte de vous accueillir.</p>';
  } elseif ($status === 'cancelled') {
    $body .= '<p style="margin:0 0 12px;font-size:14px;color:#DC2626;font-weight:700;">Votre réservation a été annulée.</p>';
    $body .= '<p style="margin:0;font-size:14px;color:#3D3D3D;">Si vous pensez qu\'il s\'agit d\'une erreur, contactez-nous au <strong>(+229) 01 96 77 13 13</strong>.</p>';
  } elseif ($status === 'completed') {
    $body .= '<p style="margin:0 0 12px;font-size:14px;color:#6B5E55;font-weight:700;">Votre séjour est terminé. Merci de votre visite !</p>';
  }

  $body .= '<p style="margin:16px 0 0;font-size:14px;color:#3D3D3D;">Des questions ? Contactez-nous au <strong>(+229) 01 96 77 13 13</strong>.</p>';

  $html = email_layout([
    'title' => $title, 'greeting' => $greeting, 'body_html' => $body,
    'footer_text' => 'Notification automatique — Résidence Rubis', 'year' => date('Y'),
  ]);

  return send_branded_email($email, "Réservation {$apartment} — {$s['label']} — Résidence Rubis", $html);
}

/* ============================================================
 * Confirmation réservation groupée — CLIENT (panier)
 * ============================================================ */
function send_cart_confirmation_to_client($lines, $email, $client_name, $total) {
  if ($email === '' || empty($lines)) return false;

  $total_fmt = number_format($total, 0, ',', ' ');
  $title = '<h2 style="margin:0 0 8px;font-size:22px;color:#B85D3F;font-weight:700;">Réservation groupée confirmée</h2>';
  $greeting = '<p style="margin:0 0 20px;font-size:16px;color:#2C2C2C;">Bonjour <strong>' . htmlspecialchars($client_name) . '</strong>,</p>';
  $body = '<p style="margin:0 0 20px;">Voici le récapitulatif :</p>';
  $body .= '<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;border-collapse:collapse;">';
  $body .= '<tr style="background:#FAF5ED;"><th style="padding:10px 12px;font-size:12px;color:#6B5E55;text-align:left;border-bottom:2px solid #EBE3DA;">Appartement</th><th style="padding:10px 12px;font-size:12px;color:#6B5E55;text-align:left;border-bottom:2px solid #EBE3DA;">Dates</th><th style="padding:10px 12px;font-size:12px;color:#6B5E55;text-align:right;border-bottom:2px solid #EBE3DA;">Nuits</th><th style="padding:10px 12px;font-size:12px;color:#6B5E55;text-align:right;border-bottom:2px solid #EBE3DA;">Total</th></tr>';
  foreach ($lines as $l) {
    $d_in = date('d/m/Y', strtotime($l['check_in']));
    $d_out = date('d/m/Y', strtotime($l['check_out']));
    $sub = number_format($l['sub'], 0, ',', ' ');
    $body .= '<tr><td style="padding:10px 12px;font-size:14px;border-bottom:1px solid #EBE3DA;">' . htmlspecialchars($l['name']) . '</td><td style="padding:10px 12px;font-size:13px;color:#6B5E55;border-bottom:1px solid #EBE3DA;">' . $d_in . ' → ' . $d_out . '</td><td style="padding:10px 12px;text-align:right;border-bottom:1px solid #EBE3DA;">' . $l['nights'] . '</td><td style="padding:10px 12px;font-weight:600;text-align:right;border-bottom:1px solid #EBE3DA;">' . $sub . ' F</td></tr>';
  }
  $body .= '<tr><td colspan="3" style="padding:12px;font-weight:700;text-align:right;border-top:2px solid #EBE3DA;">Total</td><td style="padding:12px;font-size:18px;color:#B85D3F;font-weight:700;text-align:right;border-top:2px solid #EBE3DA;">' . $total_fmt . ' F CFA</td></tr></table>';
  $body .= '<p style="margin:0;font-size:14px;color:#3D3D3D;">Notre équipe va examiner votre demande.</p>';

  $html = email_layout([
    'title' => $title, 'greeting' => $greeting, 'body_html' => $body,
    'footer_text' => 'Demande groupée enregistrée — Résidence Rubis', 'year' => date('Y'),
  ]);

  return send_branded_email($email, "Réservation groupée — Résidence Rubis", $html);
}

/* ============================================================
 * WhatsApp — helpers
 * ============================================================ */
function whatsapp_clean_phone($phone) {
  $phone = trim($phone);
  $has_plus = (strpos($phone, '+') === 0);
  $digits = preg_replace('/\D/', '', $phone);
  return $has_plus ? '+' . $digits : $digits;
}

function whatsapp_status_url($phone, $status, $apartment) {
  $clean = whatsapp_clean_phone($phone);
  if ($clean === '' || strlen($clean) < 8) return '';
  $status_messages = [
    'confirmed' => "Bonjour ! 👋\n\nBonne nouvelle : votre réservation pour l'appartement **{$apartment}** a été ✅ *confirmée* par l'équipe de la Résidence Rubis.\n\nNous avons hâte de vous accueillir !\n\n📍 Résidence Rubis — Cotonou, Bénin",
    'cancelled' => "Bonjour ! 👋\n\nNous vous informons que votre réservation pour l'appartement **{$apartment}** a malheureusement été ❌ *annulée*.\n\nContactez-nous au (+229) 01 96 77 13 13 si besoin.\n\n📍 Résidence Rubis — Cotonou, Bénin",
    'completed' => "Bonjour ! 👋\n\nVotre séjour à **{$apartment}** est terminé. Merci de votre visite à la Résidence Rubis ! 🙏\n\n📍 Résidence Rubis — Cotonou, Bénin",
  ];
  $msg = $status_messages[$status] ?? '';
  if ($msg === '') return '';
  $encoded = urlencode($msg);
  $wa_phone = ltrim($clean, '+');
  return "https://wa.me/{$wa_phone}?text={$encoded}";
}
