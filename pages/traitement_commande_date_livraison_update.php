<?php
// Connexion à la base de données
require_once '../inc/functions/connexion.php';
//session_start(); // Assurez-vous que la session est démarrée

// Validation et assainissement des données entrantes
$commande_id = filter_input(INPUT_POST, 'commande_id', FILTER_VALIDATE_INT);

$date_livraison = $_POST['date_livraison'];



if ($commande_id === null || $commande_id === false) {
    // Gestion des erreurs de validation
    $_SESSION['error'] = "Données invalides fournies.";
    header('Location: commandes.php');
    exit(0);
}

try {
    // Démarrage d'une transaction
    $conn->beginTransaction();

    // Mise à jour du statut de la commande
    $sql = "UPDATE commandes SET date_livraison = :date_livraison WHERE id = :id";
    $requete = $conn->prepare($sql);
    $query_execute = $requete->execute([
        ':id' => $commande_id,
        ':date_livraison' => $date_livraison,
    ]);

    if (!$query_execute) {
        throw new Exception("Échec de la mise à jour du statut de la commande.");
    }

    // Validation de la transaction
    $conn->commit();

    // Message de succès et redirection
    $_SESSION['success'] = "La commande a été mise à jour avec succès.";
    header('Location: commandes.php');
    exit(0);

} catch (Exception $e) {
    // Annulation de la transaction en cas d'erreur
    $conn->rollBack();
    $_SESSION['error'] = "Une erreur est survenue : " . $e->getMessage();
    header('Location: commandes.php');
    exit(0);
}
?>
