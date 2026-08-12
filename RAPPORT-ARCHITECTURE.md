# Rapport d'architecture — Résidence Rubis

> Date : août 2026
> Nature du projet : site vitrine + outil de réservation pour la « Résidence Rubis », résidence d'appartements meublés à Cotonou (Bénin).

---

## 1. Vue d'ensemble

Le projet est un **site vitrine en PHP natif (sans framework)**, complété par un **back-office de gestion**. Il permet :

- de présenter la résidence (appartements, services, témoignages, monuments du Bénin) ;
- de recevoir des **réservations** et des **messages de contact** (stockés en base et relayés par e-mail) ;
- à l'administrateur de **remplacer les photos et modifier les prix** sans toucher au code, et de consulter les demandes reçues.

Il est développé en local sous **WAMP** et hébergé en production sur **InfinityFree** (hébergement mutualisé gratuit) avec une base **MySQL** distante.

---

## 2. Stack technique

| Couche       | Technologie                                   |
|--------------|-----------------------------------------------|
| Backend      | PHP 7+/8 (procédural, sans framework)          |
| Base de données | MySQL / MariaDB (via `mysqli`, requêtes préparées) |
| Frontend     | HTML5, CSS (variables CSS), JavaScript vanilla |
| Police       | Google Fonts (Inter, Playfair Display)         |
| Hébergement  | WAMP (local) / InfinityFree (production)       |
| VCS          | Git — dépôt GitHub `David10-docton/Residencerubis` |

---

## 3. Arborescence

```
Residencerubis/
├── index.php              → Accueil (héros + réservation + atouts + appartements + témoignages)
├── a-propos.php           → Page « À propos » (histoire, stats, équipe)
├── nos-appartements.php   → Catalogue des appartements
├── nos-services.php       → Services gratuits / payants + location de voiture
├── decouvrez-le-benin.php → Monuments (sliders) + destinations touristiques
├── contact.php            → Coordonnées + formulaire de contact + carte Google Map
│
├── includes/
│   ├── config.php         → DONNÉES CENTRALES (contenu du site) + surcharges DB
│   ├── db.php             → Connexion MySQL + création des tables + fonctions CRUD
│   ├── header.php         → Balisage commun d'en-tête (nav, meta)
│   └── footer.php         → Pied de page commun (footer, cart flottant, script JS)
│
├── admin/                 → Back-office (voir §6)
│   ├── auth.php           → Session + identifiants admin (hash bcrypt)
│   ├── login.php          → Formulaire de connexion
│   ├── logout.php         → Déconnexion
│   ├── index.php          → Console admin (onglets Photos / Prix / Demandes)
│   ├── actions.php        → Traitement des actions (upload, URL, reset, prix)
│   └── admin.css          → Styles de la console
│
├── css/style.css          → Feuille de style globale
├── js/main.js             → Animations, sliders, compteurs, scroll, cart
├── images/                → Ressources (logo, résidence, monuments, équipe)
├── uploads/               → Images envoyées depuis l'admin (vides par défaut)
└── pages/                 → Dossier inutilisé (vide)
```

---

## 4. Architecture des données

### 4.1 Config centralisée (le cœur du site)

Tout le contenu vit dans `includes/config.php` sous forme de **tableaux PHP** :

- `$apartments` — 6 appartements (ANAIS, LAURA, LYS, OCCITANIE, JASMAIN, HORTENSIA) : nom, type (T2/T3), prix, image, équipements ;
- `$free_services` / `$paid_services` — services gratuits et payants ;
- `$testimonials`, `$team`, `$features_home` — témoignages, équipe, atouts ;
- `$benin_monuments`, `$benin_destinations` — monuments et lieux touristiques ;
- `$nav_links`, coordonnées, etc.

### 4.2 Surcharge dynamique par la base

Après définition des tableaux, `config.php` interroge la base (`site_images`, `site_prices`) et **remplace** les valeurs par défaut quand une personnalisation existe :

- photos : `db_get_image('apartment', $name)`, monuments, témoignages, logo, images de pages ;
- prix : `db_get_price('apartment'|'service'|'car_rental', ...)`.

Cette conception « défaut dans le code, personnalisation en base » permet à l'admin de modifier images et prix **sans déployer de code**, avec réinitialisation possible vers la valeur par défaut.

### 4.3 Schéma MySQL

Créé automatiquement à la connexion (`db.php`) :

| Table               | Rôle                                             |
|---------------------|--------------------------------------------------|
| `site_images`       | Images personnalisées (clé `section` + `item_name`) |
| `site_prices`       | Prix personnalisés (clé `section` + `item_name`) |
| `bookings`          | Demandes de réservation (appartement, dates, email) |
| `contact_messages`  | Messages du formulaire de contact                |

Toutes les requêtes passent par des **requêtes préparées** (`mysqli` + `bind_param`) pour se prémunir contre l'injection SQL.

---

## 5. Flux de rendu d'une page

```
Requête HTTP
   │
   ▼
[page.php]  définit $page_title / $meta_desc
   │
   ▼
includes/config.php   → charge le contenu + surcharges DB
   │
   ▼
includes/header.php   → <head>, nav (liens actifs via basename PHP_SELF), logo
   │
   ▼
contenu propre à la page (sections HTML + boucles PHP)
   │
   ▼
includes/footer.php   → footer, widget « cart » flottant, chargement de js/main.js
```

### Rôle du JavaScript (`js/main.js`)
- barre de progression de lecture + bouton « retour en haut » ;
- navigation mobile (menu hamburger) ;
- animations d'apparition au scroll (`IntersectionObserver`) avec délai en cascade ;
- compteurs animés (statistiques) ;
- chargement paresseux des images d'arrière-plan (`data-bg`) ;
- **sliders photo** des monuments du Bénin (auto-play 4,5 s, flèches, points, lazy-load) ;
- parallaxe du héros au survol de la souris.

---

## 6. Back-office (`admin/`)

- **Authentification** : session PHP + mot de passe haché en `bcrypt` (`password_verify`). Accès protégé sur `index.php` et `actions.php` via `admin_require_login()`.
- **Onglet Photos** : liste de toutes les images du site (logo, appartements, monuments, témoignages…). Actions : **remplacer** (upload → `uploads/`), **modifier l'URL**, **réinitialiser** (supprime l'override en base et le fichier uploadé).
- **Onglet Prix** : édition des tarifs (appartements, services, location de voiture) avec réinitialisation.
- **Onglet Demandes** : listing des réservations et des messages de contact reçus.
- **Traitement** : `actions.php` valide les entrées (extension MIME réelle via `finfo`, taille ≤ 8 Mo, URL `http(s)://` ou `uploads/`, nettoyage des champs) avant écriture en base.

---

## 7. Sécurité — points forts et faiblesses

### Points forts
- Requêtes SQL préparées (anti-injection) ;
- Échappement systématique en sortie (`htmlspecialchars`) ;
- Mots de passe hachés (bcrypt), session pour l'admin ;
- Uploads contrôlés (MIME réel, taille, nom régénéré, dossier dédié).

### Faiblesses / risques
1. **Secrets en clair dans le code versionné** : identifiants MySQL InfinityFree dans `includes/db.php` (avec mots de passe) et hash admin dans `admin/auth.php` — exposés sur GitHub.
2. **Dépendance forte à la base** : chaque chargement de page tente une connexion MySQL distante et exécute 4 `CREATE TABLE IF NOT EXISTS` (lent, fragile si le réseau tombe).
3. **Connexion DB sans poignée d'erreur visible** : en cas d'échec, le site continue en silencieux (image/prix par défaut) — acceptable mais peu débogable.
4. **Pas de `.gitignore` ni de fichiers `.env`** : les fichiers sensibles et `uploads/` sont sujets à commit accidentel (actuellement `db.php`, `admin/`, `uploads/`, `images/` ne sont pas encore suivis).
5. **`@mail()` non fiable** sur InfinityFree : les notifications e-mail peuvent ne jamais partir (faire de la DB la source de vérité).

---

## 8. Recommandations

1. Déplacer les identifiants DB et le hash admin vers des variables d'environnement / fichier hors Git + ajouter un `.gitignore` (`.env`, `uploads/`).
2. Mettre en cache les valeurs de surcharge (ex. un fichier JSON mis à jour par `actions.php`) pour éviter une requête DB à chaque page vue.
3. Créer un unique fichier d'installation SQL au lieu de `CREATE TABLE IF NOT EXISTS` à chaque connexion.
4. Vérifier la fiabilité de l'envoi d'e-mail (SMTP) ; sinon notifier l'admin uniquement via l'onglet « Demandes ».
5. Supprimer le dossier `pages/` inutilisé.
6. Ajouter des pages d'erreur (404) et un mécanisme de log (erreurs DB / upload).

---

## 9. Conclusion

Le projet adopte une architecture **simple, cohérente et sans dépendances externes**, facile à maintenir : contenu centralisé dans `config.php`, personnalisation pilotée par la base, back-office autonome. Ses principaux axes d'amélioration concernent la **sécurité des secrets** et la **robustesse / performance** de la couche base de données, plutôt qu'une refonte fonctionnelle.
