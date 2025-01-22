<?php
require_once '../inc/functions/connexion.php';

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer et valider les données
$id_total_cout = filter_input(INPUT_POST, 'id_total_cout', FILTER_VALIDATE_INT);
$statut_paiement = filter_input(INPUT_POST, 'statut_paiement', FILTER_SANITIZE_STRING);
$type_paiement_id = filter_input(INPUT_POST, 'type_paiement_id', FILTER_VALIDATE_INT);

// Vérifier si les données sont valides
if (!$id_total_cout || !$statut_paiement || !$type_paiement_id) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

try {
    // Préparer et exécuter la requête de mise à jour
    $query = $conn->prepare("
        UPDATE table_total_cout_par_jour 
        SET statut_paiement = :statut_paiement,
            type_paiement_id = :type_paiement_id
        WHERE id = :id
    ");

    $result = $query->execute([
        ':statut_paiement' => $statut_paiement,
        ':type_paiement_id' => $type_paiement_id,
        ':id' => $id_total_cout
    ]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Mise à jour réussie']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données']);
}
