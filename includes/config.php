<?php
$site_name = "Résidence Rubis";
$site_tagline = "Vous êtes ici chez vous";
$site_email = "contact@residencerubis.com";
$site_phone = "(+229) 01 96 77 13 13";
$site_address = "Cotonou, Bénin";
$site_hours = "Lun - Sam : 9h00 - 13h00 & 14h00 - 18h00";
$current_year = date("Y");

$nav_links = [
  ["url" => "index.php", "label" => "Accueil"],
  ["url" => "a-propos.php", "label" => "À propos"],
  ["url" => "nos-appartements.php", "label" => "Nos Appartements"],
  ["url" => "nos-services.php", "label" => "Nos services"],
  ["url" => "decouvrez-le-benin.php", "label" => "Découvrez Le Bénin"],
  ["url" => "contact.php", "label" => "Contact"],
];

$apartments = [
  [
    "name" => "ANAIS", "type" => "T2", "price" => "30 000",
    "image" => "https://residencerubis.com/wp-content/uploads/2023/07/SEJOUR-T2-2ETAGE-2-768x498-3.jpg",
    "features" => ["🌊 Vue sur la Plage", "🛏️ 1 Chambre", "❄️ Climatisation", "📶 Wi-Fi gratuit"],
    "description" => "Appartement 2 pièces avec vue sur mer"
  ],
  [
    "name" => "LAURA", "type" => "T2", "price" => "30 000",
    "image" => "https://residencerubis.com/wp-content/uploads/2023/09/Residencerubis-Laura-40-Grande.jpg",
    "features" => ["🌊 Vue sur la Plage", "🛏️ 1 Chambre", "❄️ Climatisation", "📶 Wi-Fi gratuit"],
    "description" => "Appartement 2 pièces avec vue sur mer"
  ],
  [
    "name" => "LYS", "type" => "T2", "price" => "30 000",
    "image" => "https://residencerubis.com/wp-content/uploads/2023/07/Residencerubis-NAISS-1-768x512-2.jpg",
    "features" => ["🌊 Vue sur la Plage", "🛏️ 1 Chambre", "❄️ Climatisation", "📶 Wi-Fi gratuit"],
    "description" => "Appartement 2 pièces avec vue sur mer"
  ],
  [
    "name" => "OCCITANIE", "type" => "T2", "price" => "30 000",
    "image" => "https://residencerubis.com/wp-content/uploads/2024/07/IMG_1835-scaled.jpg",
    "features" => ["🌊 Vue sur la Plage", "🛏️ 1 Chambre", "❄️ Climatisation", "📶 Wi-Fi gratuit"],
    "description" => "Appartement 2 pièces avec vue sur mer"
  ],
  [
    "name" => "JASMAIN", "type" => "T3", "price" => "36 000",
    "image" => "https://residencerubis.com/wp-content/uploads/2023/07/Residencerubis-JASMIN-10-600x400-1.jpg",
    "features" => ["🌊 Vue sur la Plage", "🛏️ 2 Chambres", "❄️ Climatisation", "📶 Wi-Fi gratuit"],
    "description" => "Appartement 3 pièces spacieux avec vue sur mer"
  ],
  [
    "name" => "HORTENSIA", "type" => "T3", "price" => "36 000",
    "image" => "https://residencerubis.com/wp-content/uploads/2023/07/Residencerubis-HORTENCIA-8.jpg",
    "features" => ["🌊 Vue sur la Plage", "🛏️ 2 Chambres", "❄️ Climatisation", "📶 Wi-Fi gratuit"],
    "description" => "Appartement 3 pièces spacieux avec vue sur mer"
  ],
];

$free_services = [
  ["icon" => "📶", "name" => "WiFi"],
  ["icon" => "👮", "name" => "Veilleur de Nuit"],
  ["icon" => "🏨", "name" => "Parking Extérieur"],
  ["icon" => "💨", "name" => "Sèche cheveux", "hint" => "à la demande"],
  ["icon" => "📋", "name" => "Table de repassage", "hint" => "à la demande"],
  ["icon" => "🔥", "name" => "Fer à repasser", "hint" => "à la demande"],
];

$paid_services = [
  ["icon" => "🍳", "name" => "Ménage cuisine", "price" => "8 000 XOF"],
  ["icon" => "🛏️", "name" => "Ménage T2 complet", "price" => "12 000 XOF"],
  ["icon" => "🧺", "name" => "Ménage T3 complet", "price" => "15 000 XOF"],
  ["icon" => "✨", "name" => "Dressage de lit simple", "price" => "2 500 XOF"],
  ["icon" => "👗", "name" => "Nettoyage Linge de maison", "price" => "5 000 XOF"],
  ["icon" => "🧹", "name" => "Repassage Linge de maison", "price" => "1 200 XOF/h"],
  ["icon" => "🛏️", "name" => "Jeu de Lit supplémentaire", "price" => "5 000 XOF"],
  ["icon" => "👶", "name" => "Poussette bébé", "price" => "1 500 XOF/jour"],
  ["icon" => "🧳", "name" => "Transfert Aéroport", "price" => "Sur devis"],
  ["icon" => "🚗", "name" => "Location de voiture", "price" => "35 000 XOF/jour"],
];

$testimonials = [
  [
    "text" => "Notre séjour a été absolument parfait ! La résidence est impeccablement entretenue, le personnel est accueillant et les installations sont de haute qualité.",
    "author" => "Boris DJIMADJA", "initial" => "B"
  ],
  [
    "text" => "Nous avons été agréablement surpris par le niveau de service exceptionnel. Le personnel était toujours disponible, attentif à nos besoins.",
    "author" => "Isabella", "initial" => "I"
  ],
  [
    "text" => "Une expérience mémorable ! Les logements étaient spacieux, propres et décorés avec goût. La résidence offre une atmosphère paisible et relaxante.",
    "author" => "Gertrude", "initial" => "G"
  ],
];

$team = [
  ["name" => "LADY", "role" => "Designer Intérieure", "emoji" => "👩‍🎨"],
  ["name" => "Désiré A.", "role" => "CEO", "emoji" => "👨‍💼"],
  ["name" => "Équipe Ménage", "role" => "Propreté & Confort", "emoji" => "🧹"],
  ["name" => "Sécurité", "role" => "Veilleur de nuit", "emoji" => "🛡️"],
];

$benin_destinations = [
  ["name" => "Cotonou", "desc" => "Capitale économique, ville dynamique avec sa plage, le marché Dantokpa, la fondation Zinsou, et la vie nocturne animée."],
  ["name" => "Ouidah", "desc" => "Ville historique, porte du non-retour, temple des pythons, route des esclaves, et plages magnifiques."],
  ["name" => "Abomey", "desc" => "Ancienne capitale du royaume du Dahomey, ses palais royaux classés à l'UNESCO, musée historique."],
  ["name" => "Ganvié", "desc" => "La Venise de l'Afrique, cité lacustre construite sur pilotis au milieu du lac Nokoué."],
  ["name" => "Parc W", "desc" => "Réserve de biosphère transfrontalière, safari, éléphants, lions, hippopotames et une nature préservée."],
  ["name" => "Grand-Popo", "desc" => "Station balnéaire paisible, plages de sable fin, lagunes, idéal pour la détente et le farniente."],
];

$features_home = [
  ["icon" => "🌊", "title" => "Emplacement pratique", "desc" => "Vue sur la mer à Cotonou"],
  ["icon" => "📶", "title" => "Wi-Fi haut débit", "desc" => "Disponible gratuitement"],
  ["icon" => "❄️", "title" => "Climatisation", "desc" => "Confort de haut standing"],
  ["icon" => "🅿️", "title" => "Parking sécurisé", "desc" => "Protection de vos véhicules"],
  ["icon" => "👮", "title" => "Veilleur de nuit", "desc" => "Sécurité 24h/24"],
  ["icon" => "🛠️", "title" => "Équipements complets", "desc" => "Fer, sèche-cheveux, table de repassage"],
];
