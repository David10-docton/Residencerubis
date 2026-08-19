<?php
/**
 * Script de configuration — Résidence Rubis
 *
 * Génère le fichier .env avec des identifiants admin sécurisés.
 * Usage : php setup.php
 */

if (php_sapi_name() !== 'cli') {
  echo "Ce script doit être lancé en ligne de commande : php setup.php\n";
  exit(1);
}

$env_file = __DIR__ . '/.env';
$env_example = __DIR__ . '/.env.example';

echo "\n";
echo "╔══════════════════════════════════════════════╗\n";
echo "║   Résidence Rubis — Configuration .env       ║\n";
echo "╚══════════════════════════════════════════════╝\n\n";

// Vérifier si .env existe déjà
if (file_exists($env_file)) {
  echo "⚠  Le fichier .env existe déjà.\n";
  echo "   pour le régénérer, supprimez-le d'abord : rm .env\n\n";

  // Lire les valeurs actuelles
  $current = parse_ini_file($env_file) ?: [];
  $has_admin = !empty($current['ADMIN_USER']) && ($current['ADMIN_USER'] ?? '') !== 'admin';
  if ($has_admin) {
    echo "   Identifiant actuel : " . $current['ADMIN_USER'] . "\n\n";
  }
  exit(0);
}

// Vérifier .env.example
if (!file_exists($env_example)) {
  echo "✗  Fichier .env.example introuvable.\n";
  exit(1);
}

echo "📝 Configuration des identifiants d'administration.\n";
echo "   Ces identifiants sont requis pour accéder au panneau admin.\n\n";

// Demander l'identifiant
$username = '';
while ($username === '') {
  echo "Identifiant admin : ";
  $username = trim(fgets(STDIN));
  if ($username === '') {
    echo "   ✗ L'identifiant ne peut pas être vide.\n";
  }
}

// Demander le mot de passe
$password = '';
while ($password === '') {
  echo "Mot de passe      : ";
  // Masquer la saisie si possible
  if (function_exists('readline')) {
    $password = readline('');
  } else {
    // Fallback : lecture directe (non masquée)
    $password = trim(fgets(STDIN));
  }
  if ($password === '') {
    echo "   ✗ Le mot de passe ne peut pas être vide.\n";
  }
  if (strlen($password) < 6) {
    echo "   ⚠  Le mot de passe fait moins de 6 caractères. Recommencer ? (o/N) : ";
    $retry = trim(fgets(STDIN));
    if (strtolower($retry) === 'o') {
      $password = '';
    }
  }
}

// Générer le hash bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT);
$pass_env = getenv('DB_PASS') ?: '';
$db_user = getenv('DB_USER') ?: 'root';
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'residencerubis';

// Générer le contenu .env
$content = <<<ENV
# =============================================================
# Résidence Rubis — Configuration
# Généré par setup.php le {$current_date = date('d/m/Y à H:i')}
# =============================================================

# --- Base de données ---
DB_HOST={$db_host}
DB_USER={$db_user}
DB_PASS={$pass_env}
DB_NAME={$db_name}

# --- Admin ---
ADMIN_USER={$username}
ADMIN_PASS_HASH={$hash}

ENV;

// Écrire le fichier
if (file_put_contents($env_file, $content)) {
  echo "\n✅ Fichier .env créé avec succès !\n\n";
  echo "   Identifiant : {$username}\n";
  echo "   Hash bcrypt : {$hash}\n\n";
  echo "🔗 Pour vous connecter :\n";
  echo "   1. Lancez le serveur : php -S localhost:8000\n";
  echo "   2. Ouvrez : http://localhost:8000/mon-compte.php\n";
  echo "   3. Identifiant : {$username}\n";
  echo "   4. Mot de passe : [celui que vous avez saisi]\n\n";
  echo "⚠  Le mot de passe en clair n'est PAS stocké dans .env.\n";
  echo "   Seul le hash bcrypt est enregistré. Conservez votre mot de passe en lieu sûr.\n";
} else {
  echo "\n✗  Impossible de créer le fichier .env.\n";
  echo "   Vérifiez les droits d'écriture sur ce dossier.\n";
  exit(1);
}
