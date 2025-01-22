<?php

// Connexion à la base de données (à adapter avec vos informations)
require_once '../inc/functions/connexion.php';
//session_start(); 

$id = $_POST['id'];
$communes = $_POST['communes'];
$cout_global = $_POST['cout_global'];
$livraison = $_POST['livraison'];
$cout_reel = $cout_global - $livraison;
$date_reception = $_POST['date_reception'];
//$date_livraison = $_POST['date_livraison'];

$sql = "UPDATE commandes
        SET communes = :communes, cout_global = :cout_global, cout_livraison = :livraison, 
        cout_reel = :cout_reel, date_reception = :date_reception WHERE id = :id";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':communes', $communes);
$stmt->bindParam(':cout_global', $cout_global);
$stmt->bindParam(':livraison', $livraison);
$stmt->bindParam(':cout_reel', $cout_reel);
$stmt->bindParam(':date_reception', $date_reception);
//$stmt->bindParam(':date_livraison', $date_livraison);
$stmt->bindParam(':id', $id);

if ($stmt->execute()) {
    $_SESSION['popup'] = true;
    header('Location: commandes.php');
    exit(0);
} else {
    $_SESSION['delete_pop'] = true;
   header('Location: commandes.php');
   exit(0);
}               
$conn = null; // Fermer la connexion    


?>