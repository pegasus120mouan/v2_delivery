<?php
require_once '../inc/functions/connexion.php';

// Vérification de la session
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Vérification de la méthode POST et du token CSRF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_total_cout'])) {
    $id_total_cout = filter_var($_POST['id_total_cout'], FILTER_VALIDATE_INT);
    
    if ($id_total_cout === false) {
        $_SESSION['error_message'] = "ID invalide.";
        header("Location: listes_des_depots.php");
        exit();
    }
    
    try {
        $conn->beginTransaction();
        
        // Vérifier d'abord si l'enregistrement existe
        $checkQuery = "SELECT id_total_cout FROM table_total_cout_par_jour WHERE id_total_cout = :id_total_cout";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->execute(['id_total_cout' => $id_total_cout]);
        
        if ($checkStmt->rowCount() === 0) {
            throw new Exception("Ce paiement n'existe pas ou a déjà été supprimé.");
        }
        
        // Préparer et exécuter la suppression
        $deleteQuery = "DELETE FROM table_total_cout_par_jour WHERE id_total_cout = :id_total_cout";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->execute(['id_total_cout' => $id_total_cout]);
        
        // Valider la transaction
        $conn->commit();
        
        $_SESSION['success_message'] = "Le paiement a été supprimé avec succès.";
        
    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        $conn->rollBack();
        $_SESSION['error_message'] = "Erreur lors de la suppression : " . $e->getMessage();
        error_log("Erreur de suppression - ID: $id_total_cout - " . $e->getMessage());
    }
} else {
    $_SESSION['error_message'] = "Requête invalide.";
}

// Redirection vers la page de liste
header("Location: listes_des_depots.php");
exit();
?>
