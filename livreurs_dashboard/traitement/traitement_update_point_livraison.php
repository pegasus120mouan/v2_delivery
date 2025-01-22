<?php
// Connexion à la base de données (à adapter avec vos informations)
require_once '../../inc/functions/connexion.php';
//session_start(); 
// Récupération des données soumises via le formulaire
$id_points = $_POST['id'];
$utilisateur_id = $_SESSION['user_id'];
$depenses = $_POST['depense'];
$date = $_POST['date'];

// Requête SQL d'update
$sql = "UPDATE points_livreurs
        SET utilisateur_id = :utilisateur_id, depense = :depense, 
        date_commande = :date
        WHERE id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
   ':id' => $id_points,
   ':utilisateur_id' => $utilisateur_id,
   ':depense' => $depenses,
   ':date' => $date
));

// Redirection vebarrs une page de confirmation ou de retour
$query_execute = $requete->execute($data);

if ($query_execute) {
   // $_SESSION['message'] = "Insertion reussie";
   $_SESSION['popup'] = true;
   header('Location: ../livreurs_points.php');
   exit(0);

   // Redirigez l'utilisateur vers la page d'accueil
   //header("Location: home1.php");
   // exit();
} else {
   $_SESSION['delete_pop'] = true;
   header('Location: ../livreurs_points.php');
   exit(0);
}