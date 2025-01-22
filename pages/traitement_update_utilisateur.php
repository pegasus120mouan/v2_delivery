<?php
// Connexion à la base de données (à adapter avec vos informations)
require_once '../inc/functions/connexion.php';   
//session_start(); 

// Récupération des données soumises via le formulaire
$id = $_POST['id'];
$nom = $_POST['nom'];
$prenom = $_POST['prenoms'];
$email = $_POST['email'];
$contact = $_POST['contact'];


// Requête SQL d'update
$sql = "UPDATE utilisateurs
        SET nom = :nom, prenom = :prenom, email = :email, contact = :contact WHERE utilisateur_id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
    ':id' => $id,
    ':nom' => $nom,
    ':prenom' => $prenom,
    ':email' => $email,
    ':contact' => $contact
));

// Redirection vebarrs une page de confirmation ou de retour
//$query_execute = $requete->execute($data);
    
//var_dump($query_execute);
//die();
if($query_execute)
        {
           // $_SESSION['message'] = "Insertion reussie";
            $_SESSION['popup'] = true;
	       header('Location: utilisateurs.php');
	       exit(0);

            // Redirigez l'utilisateur vers la page d'accueil
            //header("Location: home1.php");
           // exit();
        }
?>
