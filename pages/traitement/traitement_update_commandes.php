<?php

// Connexion à la base de données (à adapter avec vos informations)
require_once '../../inc/functions/connexion.php';
//session_start(); 

// Récupération des données soumises via le formulaire
$id = $_POST['id'];
$communes = $_POST['communes'];
$cout_global = $_POST['cout_global'];
$livraison = $_POST['livraison'];
$statut_livraison=$_POST['statut_livraison'];
$cout_reel = $cout_global - $livraison;
$date = $_POST['date'];

// Requête SQL d'update
$sql = "UPDATE commandes
        SET communes = :communes, cout_global = :cout_global, cout_livraison = :livraison, 
        cout_reel = :cout_reel, statut = :statut, date_commande = :date
        WHERE id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
   ':id' => $id,
   ':communes' => $communes,
   ':cout_global' => $cout_global,
   ':livraison' => $livraison,
   ':statut' => $statut_livraison,
   ':cout_reel' => $cout_reel,
   ':date' => $date
));

// Redirection vebarrs une page de confirmation ou de retour
//$query_execute = $requete->execute($data);

if ($query_execute) {
   // $_SESSION['message'] = "Insertion reussie";
   $_SESSION['popup'] = true;
   header('Location: ../commandes.php');
   exit(0);
} else {
   $_SESSION['delete_pop'] = true;
   header('Location: ../commandes.php');
   exit(0);
}