<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../inc/functions/connexion.php';

header('Content-Type: application/json');

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    
    try {
        // Requête pour récupérer les détails de la commande
        $sql = "SELECT 
                id as commande_id,
                communes as commande_communes,
                cout_global as commande_cout_global,
                cout_livraison as commande_cout_livraison,
                statut as commande_statut,
                date_commande
                FROM commandes 
                WHERE id = :id";
                
        $stmt = $conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        $commande = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($commande) {
            echo json_encode($commande);
        } else {
            http_response_code(404);
            echo json_encode([
                'error' => 'Commande non trouvée',
                'id' => $id,
                'sql' => $sql
            ]);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Erreur de base de données',
            'message' => $e->getMessage(),
            'id' => $id
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        'error' => 'ID non fourni',
        'post_data' => $_POST
    ]);
}
