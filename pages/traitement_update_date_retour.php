<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $commande_id = $_POST['commande_id'];
    $id_user = $_POST['id_user'];
    $date_retour = $_POST['date_retour'];

    try {
        // Mise à jour de la date de retour
        $sql = "UPDATE commandes 
                SET date_retour = :date_retour
                WHERE id = :commande_id";
                
        $requete = $conn->prepare($sql);
        $requete->bindParam(':date_retour', $date_retour);
        $requete->bindParam(':commande_id', $commande_id);
        
        if ($requete->execute()) {
            $_SESSION['success'] = "Date de retour mise à jour avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour de la date de retour";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Erreur : " . $e->getMessage();
    }

    // Redirection vers la page des commandes
    header("Location: commandes_livreurs.php?id=" . $id_user);
    exit();
}

// Si accès direct au fichier, redirection
header("Location: index.php");
exit();
?>
