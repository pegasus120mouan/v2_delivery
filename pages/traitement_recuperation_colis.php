<?php
session_start();
require_once '../inc/functions/connexion.php';

if (isset($_POST['recuperer_colis'])) {
    $commande_id = $_POST['commande_id'];
    $date_retour = $_POST['date_retour'];

    try {
        // Mise à jour de la date de retour
        $stmt = $conn->prepare("UPDATE commandes SET date_retour = :date_retour WHERE id = :commande_id");
        $stmt->execute([
            ':date_retour' => $date_retour,
            ':commande_id' => $commande_id
        ]);

        $_SESSION['success'] = "Le colis a été marqué comme récupéré avec succès.";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Une erreur est survenue lors de la récupération du colis.";
        error_log("Erreur lors de la récupération du colis: " . $e->getMessage());
    }

    header('Location: commandes_non_livrees.php');
    exit();
}

header('Location: commandes_non_livrees.php');
exit();
?>
