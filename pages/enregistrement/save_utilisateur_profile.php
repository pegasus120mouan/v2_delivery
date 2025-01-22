<?php
// Connexion à la base de données (à adapter avec vos informations)
require_once '../../inc/functions/connexion.php'; 

require_once '../../inc/functions/verification_password.php';  

//session_start(); 

// Récupération des données soumises via le formulaire
$id_utilisateur=$_POST['id'];
$nom = $_POST['nom'];
$prenoms = $_POST['prenoms'];
$contact = $_POST['contact'];

if (!isPhoneNumberValid($contact)) {
        $_SESSION['delete_pop'] = true;
        header('Location: ../liste_livreurs.php');
        exit(0);
} else {
        $sql = "UPDATE utilisateurs
        SET nom = :nom, prenoms = :prenoms, contact = :contact
        WHERE id = :id_utilisateur";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
    ':id_utilisateur' => $id_utilisateur,
    ':nom' => $nom,
    ':prenoms' => $prenoms,
    ':contact' => $contact
));

  
//var_dump($query_exec/die();
if($query_execute)
        {
           // $_SESSION['message'] = "Insertion reussie";
            $_SESSION['popup'] = true;
	       header('Location: ../liste_livreurs.php');
	       exit(0);

            // Redirigez l'utilisateur vers la page d'accueil
            //header("Location: home1.php");
           // exit();
        }

}



?>
