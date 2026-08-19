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
 * Utilise PHP mail() avec Content-Type text/html.
 * Les emails plaintext sont générés automatiquement en fallback.
 */

if (!function_exists('mail')) return; // Protection CLI / environnement sans mail

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
  $to = trim($to);
  if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) return false;

  $site_name = 'Résidence Rubis';
  $site_email = 'residencerubis26@gmail.com';
  $site_phone = '(+229) 01 96 77 13 13';
  $site_address = 'Cotonou, Bénin';
  $base_url = ''; // Sera rempli dynamiquement si besoin

  // Construire le mail complet
  $boundary = md5(uniqid(time()));

  $headers  = "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
  $headers .= "From: {$site_name} <{$site_email}>\r\n";
  $reply_addr = ($replyTo !== '') ? $replyTo : $site_email;
  $headers .= "Reply-To: {$reply_addr}\r\n";
  $headers .= "X-Mailer: RésidenceRubis/1.0\r\n";
  $headers .= "Date: " . date('r') . "\r\n";

  // Plaintext fallback
  if ($text === '') {
    $text = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</h1>', '</h2>', '</h3>', '</li>', '</tr>'], ["\n", "\n", "\n", "\n", "\n\n", "\n\n", "\n", "\n", "\n"], $html));
    $text = preg_replace('/\n{3,}/', "\n\n", $text);
  }

  $body  = "--{$boundary}\r\n";
  $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
  $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
  $body .= wordwrap($text, 72, "\r\n") . "\r\n\r\n";
  $body .= "--{$boundary}\r\n";
  $body .= "Content-Type: text/html; charset=UTF-8\r\n";
  $body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
  $body .= $html . "\r\n\r\n";
  $body .= "--{$boundary}--";

  return @mail($to, $subject, $body, $headers);
}

/**
 * Template HTML pour les emails de réservation.
 * Accepte un tableau $data pour personnaliser le contenu.
 *
 * @param array $data ['title', 'greeting', 'body_html', 'footer_text']
 * @return string HTML complet
 */
function email_layout($data) {
  $site_name  = $data['site_name']  ?? 'Résidence Rubis';
  $site_email = $data['site_email'] ?? 'residencerubis26@gmail.com';
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

  <!-- Header -->
  <tr><td style="background:linear-gradient(135deg,#B85D3F,#9A4A30);padding:32px 40px;text-align:center;">
    <h1 style="margin:0;color:#FFFFFF;font-size:24px;font-weight:700;letter-spacing:0.5px;">{$site_name}</h1>
    <p style="margin:8px 0 0;color:rgba(255,255,255,0.7);font-size:13px;letter-spacing:1px;">VOTRE RÉSIDENCE À COTONOU</p>
  </td></tr>

  <!-- Accent bar -->
  <tr><td style="height:4px;background:linear-gradient(90deg,#DCB159,#E8C87A);"></td></tr>

  <!-- Body -->
  <tr><td style="padding:36px 40px;">
    {$title}
    {$greeting}
    <div style="font-size:15px;line-height:1.7;color:#3D3D3D;">
    {$body_html}
    </div>
  </td></tr>

  <!-- Footer -->
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

/**
 * Envoie un email de confirmation au client après une réservation.
 *
 * @param array $data ['client_name', 'email', 'apartment', 'check_in', 'check_out', 'nights', 'price_per_night', 'total']
 * @return bool
 */
function send_booking_confirmation_to_client($data) {
  $client_name    = $data['client_name'] ?? '';
  $email          = $data['email'] ?? '';
  $apartment      = $data['apartment'] ?? '';
  $check_in       = $data['check_in'] ?? '';
  $check_out      = $data['check_out'] ?? '';
  $nights         = $data['nights'] ?? 1;
  $price_per_night = $data['price_per_night'] ?? '';
  $total          = $data['total'] ?? 0;

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
  <tr>
    <td style="padding:20px 24px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:6px 0;font-size:14px;color:#6B5E55;width:40%;">Appartement</td>
          <td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$apartment}</td>
        </tr>
        <tr>
          <td style="padding:6px 0;font-size:14px;color:#6B5E55;">Arrivée</td>
          <td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$date_in}</td>
        </tr>
        <tr>
          <td style="padding:6px 0;font-size:14px;color:#6B5E55;">Départ</td>
          <td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$date_out}</td>
        </tr>
        <tr>
          <td style="padding:6px 0;font-size:14px;color:#6B5E55;">Durée</td>
          <td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$nights} nuit(s)</td>
        </tr>
        <tr>
          <td style="padding:6px 0;font-size:14px;color:#6B5E55;">Tarif / nuit</td>
          <td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$price_label} F CFA</td>
        </tr>
        <tr>
          <td style="padding:8px 0 0;font-size:14px;color:#6B5E55;border-top:2px solid #EBE3DA;font-weight:700;">Total estimé</td>
          <td style="padding:8px 0 0;font-size:18px;color:#B85D3F;font-weight:700;border-top:2px solid #EBE3DA;">{$total_fmt} F CFA</td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<p style="margin:0 0 12px;font-size:14px;color:#3D3D3D;">
  <strong>Statut :</strong> <span style="color:#DCB159;font-weight:700;">⏳ En attente de confirmation</span>
</p>

<p style="margin:0 0 12px;font-size:14px;color:#3D3D3D;">
  Notre équipe va vérifier la disponibilité de l'appartement et vous envoyer une confirmation dans les plus brefs délais.
</p>

<p style="margin:0 0 12px;font-size:14px;color:#3D3D3D;">
  <strong>Note :</strong> L'électricité est à la charge du preneur.
</p>

<p style="margin:0;font-size:14px;color:#3D3D3D;">
  Si vous avez des questions, n'hésitez pas à nous contacter au <strong>{$data['site_phone']}</strong> ou par email à <strong>{$data['site_email']}</strong>.
</p>
HTML;

  $html = email_layout([
    'title'       => $title,
    'greeting'    => $greeting,
    'body_html'   => $body,
    'footer_text' => 'Votre demande est enregistrée. Vous recevrez un email de confirmation dès que notre équipe aura validé vos dates.',
    'year'        => date('Y'),
  ]);

  return send_branded_email(
    $email,
    "Réservation {$apartment} — Résidence Rubis",
    $html,
    '',
    ''
  );
}

/* ============================================================
 * Notification de réservation — ADMIN
 * ============================================================ */

/**
 * Envoie une notification à l'admin quand un client fait une réservation.
 *
 * @param array $data ['client_name', 'email', 'apartment', 'check_in', 'check_out', 'nights', 'total']
 * @return bool
 */
function send_booking_notification_to_admin($data) {
  $client_name = $data['client_name'] ?? '';
  $email       = $data['email'] ?? '';
  $apartment   = $data['apartment'] ?? '';
  $check_in    = $data['check_in'] ?? '';
  $check_out   = $data['check_out'] ?? '';
  $nights      = $data['nights'] ?? 1;
  $total       = $data['total'] ?? 0;

  $admin_email = getenv('ADMIN_EMAIL') ?: 'residencerubis26@gmail.com';

  $date_in  = date('d/m/Y', strtotime($check_in));
  $date_out = date('d/m/Y', strtotime($check_out));
  $total_fmt = number_format($total, 0, ',', ' ');

  $title = '<h2 style="margin:0 0 8px;font-size:22px;color:#B85D3F;font-weight:700;">🔔 Nouvelle réservation</h2>';
  $greeting = '<p style="margin:0 0 20px;font-size:16px;color:#2C2C2C;">Un nouveau client vient de faire une demande de réservation :</p>';

  $body = <<<HTML
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;background:#FAF5ED;border-radius:12px;border:1px solid #EBE3DA;">
  <tr>
    <td style="padding:20px 24px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:6px 0;font-size:14px;color:#6B5E55;width:40%;">Client</td>
          <td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$client_name}</td>
        </tr>
        <tr>
          <td style="padding:6px 0;font-size:14px;color:#6B5E55;">Email</td>
          <td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$email}</td>
        </tr>
        <tr>
          <td style="padding:6px 0;font-size:14px;color:#6B5E55;">Appartement</td>
          <td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$apartment}</td>
        </tr>
        <tr>
          <td style="padding:6px 0;font-size:14px;color:#6B5E55;">Arrivée</td>
          <td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$date_in}</td>
        </tr>
        <tr>
          <td style="padding:6px 0;font-size:14px;color:#6B5E55;">Départ</td>
          <td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$date_out}</td>
        </tr>
        <tr>
          <td style="padding:6px 0;font-size:14px;color:#6B5E55;">Durée</td>
          <td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$nights} nuit(s)</td>
        </tr>
        <tr>
          <td style="padding:8px 0 0;font-size:14px;color:#6B5E55;border-top:2px solid #EBE3DA;font-weight:700;">Total estimé</td>
          <td style="padding:8px 0 0;font-size:18px;color:#B85D3F;font-weight:700;border-top:2px solid #EBE3DA;">{$total_fmt} F CFA</td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<p style="margin:0 0 12px;font-size:14px;color:#3D3D3D;">
  Connectez-vous à l'<a href="#" style="color:#B85D3F;font-weight:600;">espace admin</a> pour confirmer ou refuser cette réservation.
</p>
HTML;

  $html = email_layout([
    'title'       => $title,
    'greeting'    => $greeting,
    'body_html'   => $body,
    'footer_text' => 'Notification automatique — Résidence Rubis',
    'year'        => date('Y'),
  ]);

  return send_branded_email($admin_email, "Nouvelle réservation {$apartment} — {$client_name}", $html, '');
}

/* ============================================================
 * Mise à jour du statut — CLIENT
 * ============================================================ */

/**
 * Envoie un email au client quand le statut de sa réservation change.
 *
 * @param array $data ['client_name', 'email', 'apartment', 'check_in', 'check_out', 'status']
 * @return bool
 */
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
  <tr>
    <td style="padding:20px 24px;">
      <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
          <td style="padding:6px 0;font-size:14px;color:#6B5E55;width:40%;">Appartement</td>
          <td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$apartment}</td>
        </tr>
        <tr>
          <td style="padding:6px 0;font-size:14px;color:#6B5E55;">Arrivée</td>
          <td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$date_in}</td>
        </tr>
        <tr>
          <td style="padding:6px 0;font-size:14px;color:#6B5E55;">Départ</td>
          <td style="padding:6px 0;font-size:14px;color:#2C2C2C;font-weight:600;">{$date_out}</td>
        </tr>
        <tr>
          <td style="padding:8px 0 0;font-size:14px;color:#6B5E55;border-top:2px solid #EBE3DA;font-weight:700;">Nouveau statut</td>
          <td style="padding:8px 0 0;font-size:16px;color:{$status_color};font-weight:700;border-top:2px solid #EBE3DA;">{$status_label}</td>
        </tr>
      </table>
    </td>
  </tr>
</table>
HTML;

  // Message contextuel selon le statut
  if ($status === 'confirmed') {
    $body .= '<p style="margin:0 0 12px;font-size:14px;color:#10B981;font-weight:700;">🎉 Votre réservation a été confirmée ! Nous avons hâte de vous accueillir.</p>';
    $body .= '<p style="margin:0;font-size:14px;color:#3D3D3D;">Pensez à prévoir un acompte ou le règlement complet selon les modalités communiquées par notre équipe.</p>';
  } elseif ($status === 'cancelled') {
    $body .= '<p style="margin:0 0 12px;font-size:14px;color:#DC2626;font-weight:700;">Votre réservation a été annulée.</p>';
    $body .= '<p style="margin:0;font-size:14px;color:#3D3D3D;">Si vous pensez qu\'il s\'agit d\'une erreur, contactez-nous au <strong>(+229) 01 96 77 13 13</strong>.</p>';
  } elseif ($status === 'completed') {
    $body .= '<p style="margin:0 0 12px;font-size:14px;color:#6B5E55;font-weight:700;">Votre séjour est terminé. Merci de votre visite !</p>';
    $body .= '<p style="margin:0;font-size:14px;color:#3D3D3D;">Nous espérons que vous avez passé un moment agréable. N\'hésitez pas à nous laisser un avis.</p>';
  }

  $body .= '<p style="margin:16px 0 0;font-size:14px;color:#3D3D3D;">Des questions ? Contactez-nous au <strong>(+229) 01 96 77 13 13</strong> ou par email.</p>';

  $html = email_layout([
    'title'       => $title,
    'greeting'    => $greeting,
    'body_html'   => $body,
    'footer_text' => 'Ce message est un notifications automatique — Résidence Rubis',
    'year'        => date('Y'),
  ]);

  return send_branded_email(
    $email,
    "Réservation {$apartment} — {$s['label']} — Résidence Rubis",
    $html
  );
}

/* ============================================================
 * Confirmation réservation groupée — CLIENT (panier)
 * ============================================================ */

/**
 * Envoie un email de confirmation pour une réservation groupée depuis le panier.
 *
 * @param array  $lines     Tableau ['name', 'check_in', 'check_out', 'nights', 'price', 'sub'] par ligne
 * @param string $email     Email du client
 * @param string $client_name Nom du client
 * @param int    $total     Total estimé
 * @return bool
 */
function send_cart_confirmation_to_client($lines, $email, $client_name, $total) {
  if ($email === '' || empty($lines)) return false;

  $total_fmt = number_format($total, 0, ',', ' ');

  $title = '<h2 style="margin:0 0 8px;font-size:22px;color:#B85D3F;font-weight:700;">Réservation groupée confirmée</h2>';
  $greeting = '<p style="margin:0 0 20px;font-size:16px;color:#2C2C2C;">Bonjour <strong>' . htmlspecialchars($client_name) . '</strong>,</p>';

  $body = '<p style="margin:0 0 20px;">Voici le récapitulatif de votre demande de réservation groupée :</p>';

  // Tableau récap
  $body .= '<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;border-collapse:collapse;">';
  $body .= '<tr style="background:#FAF5ED;">';
  $body .= '<th style="padding:10px 12px;font-size:12px;color:#6B5E55;text-align:left;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #EBE3DA;">Appartement</th>';
  $body .= '<th style="padding:10px 12px;font-size:12px;color:#6B5E55;text-align:left;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #EBE3DA;">Dates</th>';
  $body .= '<th style="padding:10px 12px;font-size:12px;color:#6B5E55;text-align:right;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #EBE3DA;">Nuits</th>';
  $body .= '<th style="padding:10px 12px;font-size:12px;color:#6B5E55;text-align:right;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #EBE3DA;">Sous-total</th>';
  $body .= '</tr>';

  foreach ($lines as $l) {
    $d_in  = date('d/m/Y', strtotime($l['check_in']));
    $d_out = date('d/m/Y', strtotime($l['check_out']));
    $sub_fmt = number_format($l['sub'], 0, ',', ' ');
    $body .= '<tr>';
    $body .= '<td style="padding:10px 12px;font-size:14px;color:#2C2C2C;border-bottom:1px solid #EBE3DA;">' . htmlspecialchars($l['name']) . '</td>';
    $body .= '<td style="padding:10px 12px;font-size:13px;color:#6B5E55;border-bottom:1px solid #EBE3DA;">' . $d_in . ' → ' . $d_out . '</td>';
    $body .= '<td style="padding:10px 12px;font-size:14px;color:#6B5E55;text-align:right;border-bottom:1px solid #EBE3DA;">' . $l['nights'] . '</td>';
    $body .= '<td style="padding:10px 12px;font-size:14px;color:#2C2C2C;font-weight:600;text-align:right;border-bottom:1px solid #EBE3DA;">' . $sub_fmt . ' F</td>';
    $body .= '</tr>';
  }

  $body .= '<tr>';
  $body .= '<td colspan="3" style="padding:12px 12px 0;font-size:14px;color:#6B5E55;font-weight:700;text-align:right;border-top:2px solid #EBE3DA;">Total estimé</td>';
  $body .= '<td style="padding:12px 12px 0;font-size:18px;color:#B85D3F;font-weight:700;text-align:right;border-top:2px solid #EBE3DA;">' . $total_fmt . ' F CFA</td>';
  $body .= '</tr>';
  $body .= '</table>';

  $body .= '<p style="margin:0 0 12px;font-size:14px;color:#3D3D3D;"><strong>Statut :</strong> <span style="color:#DCB159;font-weight:700;">⏳ En attente de confirmation</span></p>';
  $body .= '<p style="margin:0;font-size:14px;color:#3D3D3D;">Notre équipe va examiner votre demande et vous confirmer la disponibilité de chaque appartement.</p>';

  $html = email_layout([
    'title'       => $title,
    'greeting'    => $greeting,
    'body_html'   => $body,
    'footer_text' => 'Votre demande groupée est enregistrée — Résidence Rubis',
    'year'        => date('Y'),
  ]);

  return send_branded_email($email, "Réservation groupée — Résidence Rubis", $html);
}
