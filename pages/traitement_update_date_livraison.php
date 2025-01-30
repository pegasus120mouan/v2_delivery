<?php
require_once '../inc/functions/connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $commande_id = $_POST['commande_id'];
    $id_user = $_POST['id_user'];
    $date_livraison = $_POST['date_livraison'];

    try {
        // Mise à jour de la date de livraison et du statut
        $sql = "UPDATE commandes 
                SET date_livraison = :date_livraison,
                    statut = 'Livré'
                WHERE id = :commande_id";
                
        $requete = $conn->prepare($sql);
        $requete->bindParam(':date_livraison', $date_livraison);
        $requete->bindParam(':commande_id', $commande_id);
        
        if ($requete->execute()) {
            $_SESSION['success'] = "Date de livraison mise à jour avec succès";
        } else {
            $_SESSION['error'] = "Erreur lors de la mise à jour de la date";
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
