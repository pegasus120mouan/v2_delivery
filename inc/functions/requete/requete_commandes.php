<?php

//$nombreParPage = 10;
//$pageCourante = isset($_GET['page']) ? $_GET['page'] : 1;
//$offset = ($pageCourante - 1) * $nombreParPage;

//$stmt = $conn->prepare("SELECT * FROM commandes ORDER BY date DESC limit $offset,$pageCourante ");
//$stmt = $conn->prepare("SELECT * FROM commandes");

//$stmt->execute();
//$commandes = $stmt->fetchAll();

//
// Toutes les commandes
$stmt = $conn->prepare(
    "SELECT 
    commandes.id AS commande_id, 
    commandes.communes AS commande_communes, 
    commandes.cout_global AS commande_cout_global, 
    commandes.cout_livraison AS commande_cout_livraison, 
    commandes.cout_reel AS commande_cout_reel, 
    commandes.statut AS commande_statut, 

    commandes.date_reception, commandes.date_livraison,commandes.date_retour,
    
    utilisateurs.nom AS nom_utilisateur, 
    utilisateurs.prenoms AS prenoms_utilisateur,
    boutiques.nom AS nom_boutique, 
    livreur.nom AS nom_livreur, 
    livreur.prenoms AS prenoms_livreur, 
    concat(livreur.nom, ' ',livreur.prenoms) AS fullname,
    utilisateurs.role
FROM commandes
JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id
JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
LEFT JOIN utilisateurs AS livreur ON commandes.livreur_id = livreur.id ORDER BY commandes.date_reception DESC"


);
//$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$commandes = $stmt->fetchAll();


// Commandes livrees

// Pagination pour les commandes livrées
$commandes_par_page = 15;
$page_courante = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page_courante - 1) * $commandes_par_page;

// Compte total des commandes livrées
$count_query = $conn->prepare("SELECT COUNT(*) as total FROM commandes WHERE statut = 'Livré'");
$count_query->execute();
$total_commandes = $count_query->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_commandes / $commandes_par_page);

// Commandes livrees avec pagination
$stmt_livres = $conn->prepare("SELECT 
    commandes.id AS commande_id, 
    commandes.communes AS commande_communes, 
    commandes.cout_global AS commande_cout_global, 
    commandes.cout_livraison AS commande_cout_livraison, 
    commandes.cout_reel AS commande_cout_reel, 
    commandes.statut AS commande_statut, 
    commandes.date_reception, 
    commandes.date_livraison,
    utilisateurs.nom AS nom_utilisateur, 
    utilisateurs.prenoms AS prenoms_utilisateur,
    boutiques.nom AS nom_boutique, 
    livreur.nom AS nom_livreur, 
    livreur.prenoms AS prenoms_livreur, 
    CONCAT(COALESCE(livreur.nom, ''), ' ', COALESCE(livreur.prenoms, '')) AS fullname
FROM commandes
JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id
JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
LEFT JOIN utilisateurs AS livreur ON commandes.livreur_id = livreur.id 
WHERE commandes.statut = 'Livré'
ORDER BY commandes.date_reception DESC
LIMIT :limit OFFSET :offset"
);

$stmt_livres->bindValue(':limit', $commandes_par_page, PDO::PARAM_INT);
$stmt_livres->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_livres->execute();
$liste_commandes_livrees = $stmt_livres->fetchAll(PDO::FETCH_ASSOC);

// Debug dans le fichier de log PHP
error_log('Debug liste_commandes_livrees: ' . print_r($liste_commandes_livrees, true));

// Vérification des données
if ($liste_commandes_livrees === false) {
    die(print_r($stmt_livres->errorInfo(), true));
}

// Commandes non livrees
// Pagination pour les commandes non livrées
$commandes_non_livrees_par_page = 15;
$page_courante_non_livrees = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset_non_livrees = ($page_courante_non_livrees - 1) * $commandes_non_livrees_par_page;

// Compte total des commandes non livrées
$count_query_non_livrees = $conn->prepare("SELECT COUNT(*) as total FROM commandes WHERE statut = 'Non Livré'");
$count_query_non_livrees->execute();
$total_commandes_non_livrees = $count_query_non_livrees->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages_non_livrees = ceil($total_commandes_non_livrees / $commandes_non_livrees_par_page);

// Debug
error_log('Debug total_commandes_non_livrees: ' . $total_commandes_non_livrees);

// Commandes non livrées avec pagination
$stmt_non_livrees = $conn->prepare("SELECT 
    commandes.id AS commande_id, 
    commandes.communes AS commande_communes, 
    commandes.cout_global AS commande_cout_global, 
    commandes.cout_livraison AS commande_cout_livraison, 
    commandes.cout_reel AS commande_cout_reel, 
    commandes.statut AS commande_statut, 
    commandes.date_reception, 
    commandes.date_livraison,
    commandes.date_retour,
    utilisateurs.nom AS nom_utilisateur, 
    utilisateurs.prenoms AS prenoms_utilisateur,
    boutiques.nom AS nom_boutique, 
    livreur.nom AS nom_livreur, 
    livreur.prenoms AS prenoms_livreur
FROM commandes
JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id
JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
LEFT JOIN utilisateurs AS livreur ON commandes.livreur_id = livreur.id 
WHERE commandes.statut = 'Non Livré'
ORDER BY commandes.date_reception DESC
LIMIT :limit OFFSET :offset");

$stmt_non_livrees->bindValue(':limit', $commandes_non_livrees_par_page, PDO::PARAM_INT);
$stmt_non_livrees->bindValue(':offset', $offset_non_livrees, PDO::PARAM_INT);
$stmt_non_livrees->execute();
$liste_commandes_non_livrees = $stmt_non_livrees->fetchAll(PDO::FETCH_ASSOC);

// Debug
error_log('Debug requête non livrées: ' . print_r($liste_commandes_non_livrees, true));

// Vérification des erreurs
if ($liste_commandes_non_livrees === false) {
    error_log('Erreur SQL: ' . print_r($stmt_non_livrees->errorInfo(), true));
}

// Autres requêtes
$getClientsQuery = "SELECT utilisateurs.id as id, boutiques.nom as nom_boutique
                   FROM utilisateurs 
                    join boutiques on utilisateurs.boutique_id=boutiques.id
                    WHERE role NOT IN ('admin', 'livreur')";
$getClientsStmt = $conn->query($getClientsQuery);

$cout_livraison = $conn->query("SELECT cout_livraison FROM cout_livraison");

$cout_livraison1 = $conn->query("SELECT cout_livraison FROM cout_livraison")->fetchAll();

// Récupération des livreurs pour le select
$getLivreurs = $conn->query("SELECT id, CONCAT(nom, ' ', prenoms) AS livreur_name 
FROM utilisateurs 
WHERE role='livreur' AND statut_compte=1");
$getLivreurs->execute();




// Récupération des statuts possibles
$getStatut = $conn->prepare("SELECT  statut FROM statut_livraison");
$getStatut->execute();

$getBoutique = $conn->query("SELECT 
    utilisateurs.*, 
    boutiques.nom AS nom_boutique,
    boutiques.type_articles AS type_articles,
    boutiques.logo AS logo_boutique
FROM 
    utilisateurs
LEFT JOIN 
    boutiques ON utilisateurs.boutique_id = boutiques.id
WHERE 
    utilisateurs.role = 'clients';");