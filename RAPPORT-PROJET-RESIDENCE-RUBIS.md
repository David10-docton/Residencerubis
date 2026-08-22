# 📋 Rapport Détaillé — Projet Résidence Rubis

**Date du rapport :** 19 août 2026
**Version du code :** `4138207` (dernier commit)
**Environnement :** WAMP (Windows) — PHP 7.4.33 / MySQL 8.0.31

---

## 1. 🏗️ Vue d'ensemble

**Résidence Rubis** est un site web vitrine et de réservation pour une résidence de standing située à Cotonou, Bénin. Le site permet aux clients de :

- Découvrir les appartements (T2, T3) avec galerie photos et vidéos
- Réserver en ligne (courte et longue durée)
- Consulter les services (gratuits et payants)
- Explorer les destinations touristiques du Bénin
- Lire le blog de la résidence
- Contacter l'équipe


---

## 2. 📊 Statistiques du code

| Métrique | Valeur |
|---|---|
| **Lignes PHP** | 4 205 |
| **Lignes CSS** | 10 592 |
| **Lignes JavaScript** | 714 |
| **Total** | ~15 500 lignes |
| **Fichiers PHP** | 25 |
| **Images** | 115 fichiers (24 Mo) |
| **Uploads** | 6 fichiers (810 Ko) |
| **Commits Git** | 9 |
| **Tables MySQL** | 7 |

---

## 3. 🗂️ Structure du projet

```
Residencerubis/
├── admin/                    # Panneau d'administration
│   ├── auth.php              # Authentification + identifiants
│   ├── login.php             # Page de connexion admin
│   ├── index.php             # Dashboard (Photos/Prix/Demandes/Blog)
│   ├── actions.php           # Traitements POST (CRUD)
│   ├── logout.php            # Déconnexion sécurisée
│   └── admin.css             # Styles du panneau admin
│
├── includes/                 # Composants partagés
│   ├── config.php            # Configuration du site (données, prix, etc.)
│   ├── db.php                # Connexion MySQL + migrations + fonctions CRUD
│   ├── security.php          # CSRF, honeypot, rate-limiting, .env
│   ├── email.php             # Templates HTML d'emails
│   ├── header.php            # En-tête HTML (nav, CSS, fonts)
│   └── footer.php            # Pied de page (carte, panier, recherche)
│
├── css/
│   ├── style.css             # Styles principaux (5 030 lignes)
│   └── responsive.css        # Compléments responsive (298 lignes)
│
├── js/
│   └── main.js               # JavaScript (714 lignes)
│
├── vendor/                   # Bibliothèques externes
│   ├── phosphor/             # Icônes Phosphor
│   └── fontawesome/          # Icônes Font Awesome
│
├── images/                   # Images du site (115 fichiers)
├── uploads/                  # Photos uploadées via l'admin
│
├── index.php                 # Page d'accueil
├── a-propos.php              # Page À propos
├── nos-appartements.php      # Liste des appartements
├── nos-services.php          # Services gratuits et payants
├── decouvrez-le-benin.php    # Destinations touristiques
├── produit.php               # Fiche détaillée + réservation
├── blog.php                  # Liste des articles
├── article.php               # Article individuel
├── contact.php               # Page contact + formulaire
├── mon-compte.php            # Connexion utilisateur
├── 404.php                   # Page d'erreur 404
├── setup.php                 # Script CLI de configuration .env
│
├── .env                      # Variables d'environnement (non versionné)
├── .env.example              # Template .env
├── .gitignore                # Fichiers ignorés par Git
└── .htaccess                 # Sécurité Apache + rewrite rules
```

---

## 4. 🗄️ Base de données (MySQL 8.0.31)

| Table | Colonnes | Lignes | Description |
|---|---|---|---|
| `blog_posts` | 11 | 5 | Articles du blog |
| `bookings` | 11 | 3 | Réservations clients |
| `site_images` | 5 | 4 | images (admin) |
| `site_prices` | 5 | 0 |  prix (admin) |
| `contact_messages` | 5 | 0 | Messages de contact |
| `users` | 6 | 0 | Comptes clients |
| `password_resets` | 5 | 0 | Réinitialisation mot de passe |

**Migrations automatiques :** Le fichier `db.php` crée toutes les tables et colonnes manquantes au premier chargement. Les migrations sont exécutées une seule fois par processus PHP (optimisé avec `static $migrated`).

---

## 5. 🔐 Sécurité

### Protections implémentées

| Mécanisme | Fichier | Description |
|---|---|---|
| **CSRF** | `security.php` | Jeton par session, vérifié avec `hash_equals()` |
| **Honeypot** | `security.php` | Champ caché « website » pour détecter les robots |
| **Rate limiting** | `security.php` | `submission_too_fast()` — minimum 2 secondes entre envois |
| **Session sécurisée** | `auth.php` | `session_regenerate_id(true)` à la connexion |
| **Variables d'env** | `.env` | Identifiants hors Git, jamais commités |
| **Hachage bcrypt** | `auth.php` | `password_hash()` pour les mots de passe |
| **Validation email** | `actions.php` | `filter_var(FILTER_VALIDATE_EMAIL)` |
| **Validation MIME** | `actions.php` | `finfo(FILEINFO_MIME_TYPE)` pour les uploads |
| **Taille max upload** | `actions.php` | 8 Mo maximum par image |

### .htaccess — Règles de sécurité

```
✅ Blocage direct du fichier .env
✅ Blocage du dossier includes/ (sauf via PHP)
✅ Blocage du dossier vendor/ (sauf CSS/WOFF)
✅ Désactivation de l'exploration de dossiers (Options -Indexes)
✅ Headers : X-Content-Type-Options, X-Frame-Options, Referrer-Policy
✅ Suppression des en-têtes X-Powered-By et Server
```

### ⚠️ Points de vigilance

1. **Pas de HTTPS forcé** — À ajouter en production
2. **Pas de Content-Security-Policy** — Recommandé pour la production
3. **Session cookies** — Pas de flags `HttpOnly`/`Secure` configurés explicitement
4. **Brute force** — Le rate limiting est basique (2s); à renforcer avec un compteur d'échecs

---

## 6. 🔧 Bug corrigé (commit `4138207`)

### Bug critique : Login admin cassé

**Symptôme :** La page de connexion réapparaissait avec des erreurs PHP affichées.

**Cause racine :** Conflit de `session_start()` entre `admin/auth.php` et `includes/security.php`.

```php
// auth.php AVANT (❌) :
session_start();  // Appelé inconditionnellement

// auth.php APRÈS (✅) :
if (session_status() === PHP_SESSION_NONE) {
    session_start();  // Appelé conditionnellement
}
```

**Deuxième cause :** Les variables d'environnement système WAMP écrasaient les valeurs du `.env` :

```php
// load_env_file() ne surcharge PAS les variables déjà définies
// Solution : lecture directe du fichier .env dans auth.php
function _auth_read_env($path, $key, $default = '') { ... }
```

**Identifiants de connexion :**
- **URL :** `http://localhost/Residencerubis/mon-compte.php`
- **Identifiant :** `savplus`
- **Mot de passe :** `s@vplus`

---

## 7. 🎨 Design — Interface d'administration

### Avant (GitHub original)
- Navbar sombre (#1C1C1C) avec texte blanc
- Background gris froid (#F5F6FA)
- Bordures grises (#E5E7EB)
- Pas de hover states sur les lignes de tableau
- Pas de styles pour le blog
- Responsive limité

### Après (version corrigée)
- Navbar blanche avec ombre douce (alignée avec le site principal)
- Background beige chaud (#FDFAF6) avec variantes (#F5EDE4)
- Bordures beige (#EBE3DA)
- Hover states sur tous les éléments interactifs
- Styles complets pour blog, réservations, messages
- Responsive 768px et 480px

### Palette de couleurs

```
--primary:      #B85D3F  (Bordeaux)
--primary-dark: #9A4A30  (Bordeaux foncé)
--gold:         #DCB159  (Or)
--gold-light:   #E8C87A  (Or clair)
--bg:           #FDFAF6  (Beige chaud)
--bg-alt:       #F5EDE4  (Beige foncé)
--text:         #2C2C2C  (Gris foncé)
--border:       #EBE3DA  (Beige bordure)
```

---

## 8. 📧 Système d'emails

### Templates HTML (`includes/email.php`)

| Type | Destinataire | Fonction |
|---|---|---|
| Confirmation réservation | Client | `send_booking_confirmation_to_client()` |
| Notification réservation | Admin | `send_booking_notification_to_admin()` |
| Mise à jour statut | Client | `send_booking_status_update()` |
| Réservation groupée | Client | `send_cart_confirmation_to_client()` |

**Caractéristiques :**
- Emails HTML multipart (text/plain + text/html)
- Branding Résidence Rubis (header bordeaux, accent or)
- Fallback plaintext automatique
- Envoi via `mail()` PHP

---

## 9. 📱 Pages du site

| Page | Fichier | Lignes PHP | Description |
|---|---|---|---|
| Accueil | `index.php` | 297 | Hero slideshow, appartements, témoignages, réservation |
| À propos | `a-propos.php` | 147 | Histoire, équipe, statistiques |
| Appartements | `nos-appartements.php` | 116 | Grille des 6 appartements (T2/T3) |
| Services | `nos-services.php` | 99 | Services gratuits + payants |
| Bénin | `decouvrez-le-benin.php` | 97 | 6 destinations touristiques |
| Fiche produit | `produit.php` | 282 | Détail + galerie + réservation + vidéo |
| Blog | `blog.php` | — | Liste des articles |
| Article | `article.php` | 113 | Article individuel |
| Contact | `contact.php` | 220 | Formulaire + carte Google Map |
| Mon compte | `mon-compte.php` | 92 | Connexion admin |
| 404 | `404.php` | — | Page d'erreur personnalisée |

---

## 10. ⚡ Optimisations de performance

| Optimisation | Fichier | Impact |
|---|---|---|
| **Cache-busting images** | `config.php` | `?v=filemtime` sur toutes les images locales |
| **Cache versions CSS** | `header.php` | Cache JSON 5 min pour les `filemtime()` |
| **Migrations une seule fois** | `db.php` | `static $migrated` évite les DDL répétés |
| **Cache JSON overrides** | `db.php` | `overrides.json` TTL 10 min |
| **Lazy-load carte** | `footer.php` | iframe Google Map chargée au clic/viewport |
| **Font preload** | `header.php` | `<link rel="preload">` pour Google Fonts |

---

## 11. 🔍 Fonctionnalités JavaScript

| Fonctionnalité | Fichier | Description |
|---|---|---|
| Menu hamburger | `main.js` | Navigation mobile responsive |
| Barre de progression | `main.js` | Scroll progress en haut de page |
| Retour en haut | `main.js` | Bouton flottant après 400px de scroll |
| Animations au scroll | `main.js` | `IntersectionObserver` pour les éléments `.animate-on-scroll` |
| Compteur animé | `main.js` | Animation des chiffres sur la page À propos |
| Slideshow héros | `main.js` | Carrousel automatique (3s) |
| Diaporama monuments | `main.js` | Flèches + dots (4.5s) |
| Overlay recherche | `main.js` | Recherche plein écran avec raccourci clavier |
| Panier réservations | `main.js` | `localStorage` + drawer latéral |
| Lightbox galerie | `main.js` | Zoom photos sur fiche produit |
| Calcul prix temps réel | `main.js` | Estimation du coût avant réservation |

---

## 12. 📝 Recommandations

### 🔴 Prioritaires

1. **Mettre à jour PHP** — Le serveur utilise PHP 7.4 (EOL depuis nov 2022). Passer à PHP 8.1+
2. **Hébergement PHP** — Vercel ne supporte pas PHP. Utiliser InfinityFree, 000webhost, ou Hostinger
3. **HTTPS** — Forcer la connexion SSL en production

### 🟡 Importantes

4. **Backup automatique** — Sauvegarder la base de données régulièrement
5. **Journalisation** — Ajouter un log des tentatives de connexion échouées
6. **CAPTCHA** — Renforcer la protection anti-spam (reCAPTCHA v3)
7. **Compression** — Activer gzip/br pour les fichiers statiques
8. **Purge uploads** — Nettoyer les anciennes images remplacées

### 🟢 Améliorations

9. **API REST** — Créer une API pour le panier de réservations
10. **Multilingue** — Français + Anglais
11. **PWA** — Application progressive pour le panier
12. **Analytics** — Intégrer Google Analytics ou Matomo
13. **Tests** — Ajouter des tests unitaires PHP

---

## 13. 📦 Dépendances

| Composant | Version | Usage |
|---|---|---|
| PHP | 7.4.33 | Serveur |
| MySQL | 8.0.31 | Base de données |
| Apache | — | Serveur web (WAMP) |
| Phosphor Icons | — | Icônes vectorielles |
| Font Awesome | — | Icônes réseaux sociaux |
| Google Fonts | — | Inter + Playfair Display |

---
