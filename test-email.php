<?php
/**
 * Script de diagnostic email — À SUPPRIMER après test
 * Uploadez-le sur InfinityFree, accédez-y via le navigateur, puis supprimez-le.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/includes/security.php';
load_env_file(__DIR__ . '/.env');

echo "<h2>Diagnostic Email — Résidence Rubis</h2>";
echo "<pre>";

// 1. PHP Info
echo "=== 1. Environnement ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "putenv(): " . (function_exists('putenv') ? 'OUI' : 'NON (DÉSACTIVÉ!)') . "\n";
echo "OpenSSL: " . (extension_loaded('openssl') ? 'OUI' : 'NON') . "\n";
echo "PHPMailer: " . (class_exists('PHPMailer\\PHPMailer\\PHPMailer') ? 'OUI' : 'NON') . "\n";

// 2. Variables lues via env_get()
echo "\n=== 2. Variables lues via env_get() ===\n";
foreach (['DB_HOST', 'DB_USER', 'DB_NAME', 'SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS', 'SMTP_ENCRYPTION', 'ADMIN_EMAIL'] as $key) {
    $val = env_get($key, 'NON DÉFINI');
    if ($key === 'SMTP_PASS' || $key === 'DB_PASS') $val = ($val !== 'NON DÉFINI' ? '***PRÉSENT***' : 'NON DÉFINI');
    echo "$key: [$val]\n";
}

// 3. Test DB
echo "\n=== 3. Test Base de données ===\n";
require_once __DIR__ . '/includes/db.php';
$conn = db_connect();
if ($conn) {
    echo "✅ Connexion DB OK\n";
} else {
    echo "❌ Connexion DB ÉCHOUÉE\n";
}

// 4. Test SMTP
echo "\n=== 4. Test SMTP ===\n";
require_once __DIR__ . '/includes/email.php';
$result = send_branded_email(
    env_get('SMTP_USER', ''),
    'Test diagnostic InfinityFree - ' . date('d/m/Y H:i'),
    '<h1 style="color:#B85D3F;">✅ Test OK depuis InfinityFree</h1><p>Date: ' . date('d/m/Y à H:i') . '</p>'
);
echo $result ? "✅ EMAIL ENVoyé AVEC SUCCÈS !" : "❌ EMAIL ÉCHOUÉ — vérifiez les logs PHP";

echo "\n</pre>";
echo "<p style='color:red;font-weight:bold;'>⚠️ SUPPRIMEZ CE FICHIER APRÈS LE TEST !</p>";
?>
