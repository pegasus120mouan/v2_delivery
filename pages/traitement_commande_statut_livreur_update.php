<?php
// Connexion à la base de données
require_once '../inc/functions/connexion.php';   
// session_start(); 

// Récupération des données soumises via le formulaire
$commande_id = $_POST['commande_id'];
$statut = $_POST['statut'];
$livreur_id = $_GET['id'];

// Initialisation des dates en fonction du statut
if ($statut === "Livré") {
    $date_livraison = date("Y-m-d"); // Date actuelle pour la livraison
    $date_retour = date("Y-m-d");    // Date actuelle pour le retour
} elseif ($statut === "Non Livré") {
    $date_livraison = NULL; // Pas de date pour la livraison
    $date_retour = NULL;    // Pas de date pour le retour
} elseif ($statut === "Retourné") {
    $date_livraison = NULL;         // Pas de date pour la livraison
    $date_retour = date("Y-m-d");   // Date actuelle pour le retour
} else {
    // Si le statut ne correspond pas à une option valide, redirection avec erreur
    $_SESSION['error_message'] = "Statut invalide.";
    header('Location: commandes_livreurs.php?id='.$livreur_id);
    exit(0);
}

// Requête SQL d'update
$sql = "UPDATE commandes
        SET statut = :statut, 
            date_livraison = :date_livraison, 
            date_retour = :date_retour
        WHERE id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les valeurs calculées
$query_execute = $requete->execute(array(
    ':id' => $commande_id,
    ':statut' => $statut,
    ':date_livraison' => $date_livraison,
    ':date_retour' => $date_retour
));

// Redirection selon le résultat de la mise à jour
if ($query_execute) {
    $_SESSION['popup'] = true;
    header('Location: commandes_livreurs.php?id='.$livreur_id);
    exit(0);
} else {
    $_SESSION['delete_pop'] = true;
    header('Location: commandes_livreurs.php?id='.$livreur_id);
    exit(0);
}
?>
