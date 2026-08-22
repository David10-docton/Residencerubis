<?php
/**
 * Script de diagnostic email — À SUPPRIMER après test
 * Accédez-y via : https://votre-site.com/email_debug.php
 */
session_start();
require_once __DIR__ . '/includes/security.php';
load_env_file(__DIR__ . '/.env');
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/email.php';

echo "<h2>Diagnostic Email — Résidence Rubis</h2>";
echo "<pre>";

// 1. Vérifier les variables d'environnement
echo "=== 1. Variables SMTP ===\n";
echo "SMTP_HOST: [" . (getenv('SMTP_HOST') ?: 'VIDE') . "]\n";
echo "SMTP_PORT: [" . (getenv('SMTP_PORT') ?: 'VIDE') . "]\n";
echo "SMTP_USER: [" . (getenv('SMTP_USER') ?: 'VIDE') . "]\n";
echo "SMTP_PASS: [" . (getenv('SMTP_PASS') ? '***PRÉSENT***' : 'VIDE') . "]\n";
echo "SMTP_ENCRYPTION: [" . (getenv('SMTP_ENCRYPTION') ?: 'VIDE') . "]\n";
echo "ADMIN_EMAIL: [" . (getenv('ADMIN_EMAIL') ?: 'VIDE') . "]\n";

// 2. Vérifier PHPMailer
echo "\n=== 2. PHPMailer ===\n";
echo "has_phpmailer: [" . ($has_phpmailer ? 'OUI' : 'NON') . "]\n";
if ($has_phpmailer) {
    echo "Version: " . \PHPMailer\PHPMailer\PHPMailer::VERSION . "\n";
}

// 3. Vérifier mail()
echo "\n=== 3. mail() ===\n";
echo "mail() disponible: [" . (function_exists('mail') ? 'OUI' : 'NON') . "]\n";

// 4. Test d'envoi
echo "\n=== 4. Test d'envoi ===\n";
$test_to = getenv('ADMIN_EMAIL') ?: 'residencerubis4@gmail.com';
echo "Destinataire: $test_to\n";

$result = send_branded_email(
    $test_to,
    'Test diagnostic email',
    '<h1 style="color:#B85D3F;">Test OK</h1><p>Ceci est un email de diagnostic envoyé le ' . date('d/m/Y à H:i') . '</p>'
);

echo "Résultat: " . ($result ? '✅ ENVoyé' : '❌ ÉCHOUÉ') . "\n";

// 5. Vérifier les logs
echo "\n=== 5. Dernières erreurs PHP ===\n";
$log = @file_get_contents('/tmp/php_errors.log');
if ($log) {
    $lines = explode("\n", $log);
    $recent = array_slice($lines, -10);
    echo implode("\n", $recent) . "\n";
} else {
    echo "Pas de fichier de log trouvé dans /tmp/\n";
    echo "Essayez: error_log('Test') et vérifiez le répertoire de logs du serveur.\n";
}

echo "\n</pre>";
echo "<p style='color:red;font-weight:bold;'>⚠️ SUPPRIMEZ CE FICHIER APRÈS LE DIAGNOSTIC!</p>";
