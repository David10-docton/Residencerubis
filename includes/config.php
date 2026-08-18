<?php
$site_name = "Résidence Rubis";
$site_tagline = "Vous êtes ici chez vous";
$site_email = "residencerubis26@gmail.com";
$site_phone = "(+229) 01 96 77 13 13";
$site_address = "Cotonou, Bénin";
$site_hours = "Lun - Sam : 9h00 - 13h00 & 14h00 - 18h00";
$rental_type = "Courte et longue durée";
$electricity_note = "Électricité à la charge du preneur.";
$current_year = date("Y");
$logo_image = "images/logo.png";
$car_rental_price = "35 000 XOF / jour";

$nav_links = [
  ["url" => "index.php", "label" => "Accueil"],
  ["url" => "a-propos.php", "label" => "À propos"],
  ["url" => "nos-appartements.php", "label" => "Nos Appartements"],
  ["url" => "nos-services.php", "label" => "Nos services"],
  ["url" => "decouvrez-le-benin.php", "label" => "Découvrez Le Bénin"],
  ["url" => "blog.php", "label" => "Blog"],
  ["url" => "contact.php", "label" => "Contact"],
];

$apartments = [
  [
    "name" => "ANAIS", "type" => "T2", "price" => "20 500", "price_eur" => "31",
    "surface" => "66 m²", "rooms" => "1 chambre – salon",
    "gallery" => "images/site-live/appartements/ANAIS",
    "image" => "images/residence/anais.jpg",
    "features" => ['<i class="ph ph-fill ph-waves"></i> Vue sur la Plage', '<i class="ph ph-fill ph-bed"></i> 1 Chambre', '<i class="ph ph-fill ph-snowflake"></i> Climatisation', '<i class="ph ph-fill ph-wifi-high"></i> Wi-Fi gratuit'],
    "description" => "Appartement 2 pièces avec vue sur mer"
  ],
  [
    "name" => "LAURA", "type" => "T2", "price" => "20 500", "price_eur" => "31",
    "surface" => "66 m²", "rooms" => "1 chambre – salon",
    "gallery" => "images/site-live/appartements/LAURA",
    "image" => "images/residence/laura.jpg",
    "features" => ['<i class="ph ph-fill ph-waves"></i> Vue sur la Plage', '<i class="ph ph-fill ph-bed"></i> 1 Chambre', '<i class="ph ph-fill ph-snowflake"></i> Climatisation', '<i class="ph ph-fill ph-wifi-high"></i> Wi-Fi gratuit'],
    "description" => "Appartement 2 pièces avec vue sur mer"
  ],
  [
    "name" => "LYS", "type" => "T2", "price" => "20 500", "price_eur" => "31",
    "surface" => "66 m²", "rooms" => "1 chambre – salon",
    "gallery" => "images/site-live/appartements/LYS",
    "image" => "images/residence/lys.jpg",
    "features" => ['<i class="ph ph-fill ph-waves"></i> Vue sur la Plage', '<i class="ph ph-fill ph-bed"></i> 1 Chambre', '<i class="ph ph-fill ph-snowflake"></i> Climatisation', '<i class="ph ph-fill ph-wifi-high"></i> Wi-Fi gratuit'],
    "description" => "Appartement 2 pièces avec vue sur mer"
  ],
  [
    "name" => "OCCITANIE", "type" => "T2", "price" => "20 500", "price_eur" => "31",
    "surface" => "66 m²", "rooms" => "1 chambre – salon",
    "gallery" => "images/site-live/appartements/OCCITANIE",
    "image" => "images/residence/occitanie.jpg",
    "video_url" => "https://www.youtube.com/embed/tyvsKxE-eHU?si=_exskQmS3l0fA0Ee&autoplay=1&mute=1",
    "features" => ['<i class="ph ph-fill ph-waves"></i> Vue sur la Plage', '<i class="ph ph-fill ph-bed"></i> 1 Chambre', '<i class="ph ph-fill ph-snowflake"></i> Climatisation', '<i class="ph ph-fill ph-wifi-high"></i> Wi-Fi gratuit'],
    "description" => "Appartement 2 pièces avec vue sur mer"
  ],
  [
    "name" => "JASMAIN", "type" => "T3", "price" => "30 500", "price_eur" => "46",
    "surface" => "106 m²", "rooms" => "2 chambres – salon",
    "gallery" => "images/site-live/appartements/JASMAIN",
    "image" => "images/residence/jasmain.jpg",
    "video_url" => "https://www.youtube.com/embed/NiwHkg_HiP8?si=WC_G537O9LzYq52q&autoplay=1&mute=1",
    "features" => ['<i class="ph ph-fill ph-waves"></i> Vue sur la Plage', '<i class="ph ph-fill ph-bed"></i> 2 Chambres', '<i class="ph ph-fill ph-snowflake"></i> Climatisation', '<i class="ph ph-fill ph-wifi-high"></i> Wi-Fi gratuit'],
    "description" => "Appartement 3 pièces spacieux avec vue sur mer"
  ],
  [
    "name" => "HORTENSIA", "type" => "T3", "price" => "30 500", "price_eur" => "46",
    "surface" => "106 m²", "rooms" => "2 chambres – salon",
    "gallery" => "images/site-live/appartements/HORTENCIA",
    "image" => "images/residence/hortensia.jpg",
    "features" => ['<i class="ph ph-fill ph-waves"></i> Vue sur la Plage', '<i class="ph ph-fill ph-bed"></i> 2 Chambres', '<i class="ph ph-fill ph-snowflake"></i> Climatisation', '<i class="ph ph-fill ph-wifi-high"></i> Wi-Fi gratuit'],
    "description" => "Appartement 3 pièces spacieux avec vue sur mer"
  ],
];

$free_services = [
  ["icon" => '<i class="ph ph-fill ph-wifi-high"></i>', "name" => "WiFi"],
  ["icon" => '<i class="ph ph-fill ph-shield-check"></i>', "name" => "Veilleur de Nuit"],
  ["icon" => '<i class="ph ph-fill ph-car-simple"></i>', "name" => "Parking Extérieur"],
  ["icon" => '<i class="ph ph-fill ph-hair-dryer"></i>', "name" => "Sèche cheveux", "hint" => "à la demande"],
  ["icon" => '<i class="ph ph-fill ph-table"></i>', "name" => "Table de repassage", "hint" => "à la demande"],
  ["icon" => '<i class="ph ph-fill ph-shirt-folded"></i>', "name" => "Fer à repasser", "hint" => "à la demande"],
];

$paid_services = [
  ["icon" => '<i class="ph ph-fill ph-bowl-steam"></i>', "name" => "Ménage cuisine", "price" => "8 000 XOF"],
  ["icon" => '<i class="ph ph-fill ph-spray-bottle"></i>', "name" => "Ménage T2 complet", "price" => "12 000 XOF"],
  ["icon" => '<i class="ph ph-fill ph-spray-bottle"></i>', "name" => "Ménage T3 complet", "price" => "15 000 XOF"],
  ["icon" => '<i class="ph ph-fill ph-bed"></i>', "name" => "Dressage de lit simple", "price" => "2 500 XOF"],
  ["icon" => '<i class="ph ph-fill ph-washing-machine"></i>', "name" => "Nettoyage Linge de maison", "price" => "5 000 XOF"],
  ["icon" => '<i class="ph ph-fill ph-shirt-folded"></i>', "name" => "Repassage Linge de maison", "price" => "1 200 XOF/h"],
  ["icon" => '<i class="ph ph-fill ph-bed"></i>', "name" => "Jeu de Lit supplémentaire", "price" => "5 000 XOF"],
  ["icon" => '<i class="ph ph-fill ph-baby-carriage"></i>', "name" => "Poussette bébé", "price" => "1 500 XOF/jour"],
  ["icon" => '<i class="ph ph-fill ph-airplane-takeoff"></i>', "name" => "Transfert Aéroport", "price" => "Sur devis"],
  ["icon" => '<i class="ph ph-fill ph-car-simple"></i>', "name" => "Location de voiture", "price" => "35 000 XOF/jour"],
];

$testimonials = [
  [
    "text" => "Notre séjour a été absolument parfait ! La résidence est impeccablement entretenue, le personnel est accueillant et les installations sont de haute qualité.",
    "author" => "Boris DJIMADJA", "initial" => "B",
    "image" => "images/site-live/team/boris.jpg"
  ],
  [
    "text" => "Nous avons été agréablement surpris par le niveau de service exceptionnel. Le personnel était toujours disponible, attentif à nos besoins.",
    "author" => "Isabella", "initial" => "I",
    "image" => "images/site-live/team/isabella.jpg"
  ],
  [
    "text" => "Une expérience mémorable ! Les logements étaient spacieux, propres et décorés avec goût. La résidence offre une atmosphère paisible et relaxante.",
    "author" => "Gertrude", "initial" => "G",
    "image" => "images/site-live/team/gertrude.jpg"
  ],
];

$team = [
  ["name" => "LADY", "role" => "Designer Intérieure", "image" => "images/site-live/about/lady.jpg"],
  ["name" => "Désiré A.", "role" => "CEO", "image" => "images/site-live/about/desire.jpg"],
  ["name" => "Équipe Ménage", "role" => "Propreté & Confort", "icon" => "cleaning", "image" => "images/menage.jpg"],
  ["name" => "Sécurité", "role" => "Veilleur de nuit", "icon" => "security", "image" => "images/Agent.jpg"],
];

$benin_destinations = [
  ["icon" => '<i class="ph ph-fill ph-buildings"></i>', "name" => "Cotonou", "desc" => "Capitale économique, ville dynamique avec sa plage, le marché Dantokpa, la fondation Zinsou, et la vie nocturne animée.", "image" => "images/Cotonou.jpg"],
  ["icon" => '<i class="ph ph-fill ph-sailboat"></i>', "name" => "Ouidah", "desc" => "Ville historique, porte du non-retour, temple des pythons, route des esclaves, et plages magnifiques.", "image" => "images/ouihda.jpg"],
  ["icon" => '<i class="ph ph-fill ph-crown"></i>', "name" => "Abomey", "desc" => "Ancienne capitale du royaume du Dahomey, ses palais royaux classés à l'UNESCO, musée historique.", "image" => "images/abomey.jpg"],
  ["icon" => '<i class="ph ph-fill ph-boat"></i>', "name" => "Ganvié", "desc" => "La Venise de l'Afrique, cité lacustre construite sur pilotis au milieu du lac Nokoué.", "image" => "images/ganvie.jpg"],
  ["icon" => '<i class="ph ph-fill ph-paw-print"></i>', "name" => "Parc W", "desc" => "Réserve de biosphère transfrontalière, safari, éléphants, lions, hippopotames et une nature préservée.", "image" => "images/benin/dest-parcw.jpg"],
  ["icon" => '<i class="ph ph-fill ph-sun"></i>', "name" => "Grand-Popo", "desc" => "Station balnéaire paisible, plages de sable fin, lagunes, idéal pour la détente et le farniente.", "image" => "images/grand-popo.jpg"],
];

$features_home = [
  ["icon" => '<i class="ph ph-fill ph-waves"></i>', "title" => "Emplacement pratique", "desc" => "Vue sur la mer à Cotonou"],
  ["icon" => '<i class="ph ph-fill ph-wifi-high"></i>', "title" => "Wi-Fi haut débit", "desc" => "Disponible gratuitement"],
  ["icon" => '<i class="ph ph-fill ph-snowflake"></i>', "title" => "Climatisation", "desc" => "Confort de haut standing"],
  ["icon" => '<i class="ph ph-fill ph-car-simple"></i>', "title" => "Parking sécurisé", "desc" => "Protection de vos véhicules"],
  ["icon" => '<i class="ph ph-fill ph-shield-check"></i>', "title" => "Veilleur de nuit", "desc" => "Sécurité 24h/24"],
  ["icon" => '<i class="ph ph-fill ph-wrench"></i>', "title" => "Équipements complets", "desc" => "Fer, sèche-cheveux, table de repassage"],
];

$about_image = "images/site-live/about/residence.jpg";
$benin_image = "images/benin/palais-marina.jpg";

$benin_monuments = [
  [
    "key" => "amazone",
    "name" => "Le Monument Amazone",
    "location" => "Cotonou — Esplanade des Amazones, Boulevard de la Marina",
    "description" => "Inaugurée le 30 juillet 2022 par le Président Patrice Talon, la statue de l'Amazone rend hommage aux Amazones du Dahomey, ces guerrières du royaume du Danxomè qui formèrent un corps d'élite redoutable du XVIIe au XIXe siècle. Haute de 30 mètres et pesant 150 tonnes, réalisée en bronze par le sculpteur chinois Li Xiangqun, elle figure une jeune guerrière fusil et épée à la main, tête levée en signe de victoire. Deuxième plus grande statue d'Afrique, elle trône sur l'esplanade des Amazones, entre le boulevard de la Marina et l'océan Atlantique, face au palais présidentiel.",
    "images" => [
      "images/benin/amazone-1.jpg",
      "images/benin/amazone-2.jpg",
      "images/benin/amazone-3.jpg",
      "images/benin/amazone-4.jpg",
    ],
  ],
  [
    "key" => "goho",
    "name" => "Place Goho",
    "location" => "Abomey — entrée de la ville, route de Bohicon",
    "description" => "À l'entrée d'Abomey, l'ancienne capitale du royaume du Dahomey, la place Goho rappelle le souvenir de la dernière grande bataille contre l'armée coloniale française en 1892 et la reddition du roi Béhanzin au général Dodds en 1894. Depuis 1978, une imposante statue de bronze du souverain, œuvre des studios Mansudae, domine la place. Surnommé « le Lion du Dahomey », Béhanzin fut le dernier roi indépendant du royaume, symbole d'une résistance farouche à la colonisation.",
    "images" => [
      "images/benin/goho-1.jpg",
      "images/benin/goho-2.jpg",
      "images/benin/goho-3.jpg",
      "images/benin/goho-4.jpg",
    ],
  ],
  [
    "key" => "bioguera",
    "name" => "Bio Guéra",
    "location" => "Cotonou — Rond-point de l'aéroport Cardinal Bernardin Gantin (Cadjehoun)",
    "description" => "Inaugurée le 30 juillet 2022 par le Président Patrice Talon, la statue de Bio Guéra trône sur le rond-point de l'aéroport international Cardinal Bernardin Gantin de Cadjehoun, à Cotonou, accueillant ainsi les passagers qui arrivent au Bénin. L'œuvre de fonte de cuivre, montée sur une structure en acier reposant sur un massif en béton armé, mesure 10 mètres de long pour 7 mètres de haut et pèse 13 tonnes. Elle représente le héros à cheval, la flèche à la main et le foulard au cou, symbole de la résistance à la colonisation. De son vrai nom Gbaasi N'Guerra, ce prince wassangari né en 1856 à Gbaasi (Kalalé) mena de nombreux combats contre l'impôt de capitation, le travail forcé et l'oppression coloniale dans le Nord du Bénin, avant de tomber le 17 décembre 1916. Proclamé « héros national » en 1975, il est aujourd'hui un symbole de la liberté et de la dignité africaine.",
    "images" => [
      "images/benin/bioguera-cotonou-2.jpg",
      "images/benin/bioguera-cotonou-3.jpg",
      "images/benin/bioguera-cotonou-4.jpg",
    ],
  ],
  [
    "key" => "tatasomba",
    "name" => "Les Tata Somba",
    "location" => "Nord-ouest du Bénin — Koutammakou (Natitingou, Boukoumbé, Toucountouna)",
    "description" => "Dans le nord-ouest du Bénin, le peuple otammari, dit « Somba », édifie depuis des siècles des maisons-forteresses en terre crue appelées tata somba. Ces demeures à deux étages, sans porte au rez-de-chaussée, protègent hommes, animaux et greniers ; on y accède par un tronc entaillé servant d'échelle. Leur architecture unique, aux murs rehaussés de motifs, fait partie du paysage culturel du Koutammakou, inscrit au patrimoine mondial de l'UNESCO depuis 2004 et étendu au Bénin en 2023. Les plus beaux exemples se visitent autour de Natitingou, Boukoumbé et Toucountouna.",
    "images" => [
      "images/tata_sombat.jpg",
      "images/tata_sombat1.jpg",
      "images/tata_sombat2.jpg",
    ],
  ],
  [
    "key" => "portenonretour",
    "name" => "La Porte du Non-Retour",
    "location" => "Ouidah — plage, bout de la route des esclaves",
    "description" => "Érigée sur la plage de Ouidah, la Porte du Non-Retour est un mémorial dédié aux millions d'Africains déportés lors de la traite transatlantique. Le monument, un grand arc de béton et de bronze orné de bas-reliefs, marque symboliquement le dernier lieu foulé par les captifs avant leur embarquement vers les Amériques. Réaménagé en un lieu de mémoire et de recueillement, il clôt la route des esclaves et invite chacun à une réflexion sur l'histoire, la dignité et la réconciliation.",
    "images" => [
      "images/porte_nonretour.jpg",
      "images/porte_nonretour1.jpeg",
      "images/porte_nonretour2.png",
    ],
  ],
];

if (file_exists(__DIR__ . '/db.php')) {
  require_once __DIR__ . '/db.php';
  if (function_exists('db_get_overrides')) {
    // Les surcharges (photos + prix) sont lues depuis un cache JSON,
    // rafraîchi par la base au plus toutes les 10 min ou après une
    // modification depuis l'admin (invalidation). Aucune requête MySQL
    // n'est nécessaire à chaque chargement de page.
    $overrides = db_get_overrides();

    if (is_array($overrides)) {
      $img = $overrides['images'];
      $price = $overrides['prices'];

      foreach ($apartments as &$a) {
        $key = 'apartment::' . $a['name'];
        if (!empty($img[$key]) && db_image_path_exists($img[$key])) $a['image'] = $img[$key];
        if (!empty($price[$key])) $a['price'] = $price[$key];
      }
      unset($a);

      foreach ($paid_services as &$s) {
        $key = 'service::' . $s['name'];
        if (!empty($price[$key])) $s['price'] = $price[$key];
      }
      unset($s);

      foreach ($testimonials as &$t) {
        $key = 'testimonial::' . $t['author'];
        if (!empty($img[$key]) && db_image_path_exists($img[$key])) $t['image'] = $img[$key];
      }
      unset($t);

      foreach ($team as &$m) {
        if (!empty($m['name'])) {
          $key = 'team::' . $m['name'];
          if (!empty($img[$key]) && db_image_path_exists($img[$key])) $m['image'] = $img[$key];
        }
      }
      unset($m);

      if (!empty($img['page::about']) && db_image_path_exists($img['page::about'])) $about_image = $img['page::about'];
      if (!empty($img['page::benin']) && db_image_path_exists($img['page::benin'])) $benin_image = $img['page::benin'];

      foreach ($benin_monuments as &$m) {
        foreach ($m['images'] as $i => $image) {
          $key = 'monument::' . $m['key'] . '-' . ($i + 1);
          if (!empty($img[$key]) && db_image_path_exists($img[$key])) $m['images'][$i] = $img[$key];
        }
      }
      unset($m);

      if (!empty($img['logo::logo']) && db_image_path_exists($img['logo::logo'])) $logo_image = $img['logo::logo'];
      if (!empty($price['car_rental::location'])) $car_rental_price = $price['car_rental::location'];
    }
  }
}
