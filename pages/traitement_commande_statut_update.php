<?php
// Connexion à la base de données
require_once '../inc/functions/connexion.php';
session_start(); // Assurez-vous que la session est démarrée

// Validation et assainissement des données entrantes
$commande_id = filter_input(INPUT_POST, 'commande_id', FILTER_VALIDATE_INT);
$statut = filter_input(INPUT_POST, 'statut', FILTER_SANITIZE_STRING);

if ($commande_id === null || $commande_id === false || $statut === false) {
    // Gestion des erreurs de validation
    $_SESSION['error'] = "Données invalides fournies.";
    
    // Récupération des paramètres de redirection
    $redirect_page = $_POST['redirect_page'] ?? 'commandes.php';
    
    // Construction de l'URL de redirection avec les bons paramètres
    $params = [];
    
    // Gestion différente selon la page de redirection
    if ($redirect_page === 'page_recherche_date_reception.php') {
        if (isset($_POST['date'])) {
            $params[] = 'date=' . urlencode($_POST['date']);
        }
    } else {
        if (isset($_POST['recherche'])) {
            $params[] = 'recherche=' . urlencode($_POST['recherche']);
        }
    }
    
    if (isset($_POST['page'])) {
        $params[] = 'page=' . $_POST['page'];
    }
    if (isset($_POST['limit'])) {
        $params[] = 'limit=' . $_POST['limit'];
    }
    
    // Construction de l'URL finale
    $redirect_url = $redirect_page;
    if (!empty($params)) {
        $redirect_url .= '?' . implode('&', $params);
    }
    
    header('Location: ' . $redirect_url);
    exit(0);
}

try {
    // Démarrage d'une transaction
    $conn->beginTransaction();

    // Définir date_livraison selon le statut
    $date_livraison = ($statut === "Livré") ? date('Y-m-d') : null;

    // Mise à jour du statut de la commande
    $sql = "UPDATE commandes SET statut = :statut, date_livraison = :date_livraison WHERE id = :id";
    $requete = $conn->prepare($sql);
    $query_execute = $requete->execute([
        ':id' => $commande_id,
        ':statut' => $statut,
        ':date_livraison' => $date_livraison,
    ]);

    if (!$query_execute) {
        throw new Exception("Échec de la mise à jour du statut de la commande.");
    }

    // Si le statut devient "livrée", mettre à jour les points_livreurs
    if ($statut === "livrée") {
        // Récupérer l'utilisateur (livreur) et le coût de livraison
        $select_query = "SELECT utilisateur_id AS livreur_id, cout_livraison FROM commandes WHERE id = :id";
        $select_stmt = $conn->prepare($select_query);
        $select_stmt->execute([':id' => $commande_id]);
        $order = $select_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new Exception("Commande introuvable.");
        }

        $livreur_id = $order['livreur_id'];
        $cout_livraison = $order['cout_livraison'];
        // Vérifier si un enregistrement existe déjà pour ce livreur et cette date
        $check_query = "SELECT id FROM points_livreurs WHERE utilisateur_id = :utilisateur_id AND date_commande = :date_commande";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->execute([
            ':utilisateur_id' => $livreur_id,
            ':date_commande' => $date
        ]);
        $existing_record = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_record) {
            // Mettre à jour l'enregistrement existant
            $update_query = "UPDATE points_livreurs 
                             SET recette = recette + :recette, 
                                 gain_jour = (recette + :recette) - depense 
                             WHERE utilisateur_id = :utilisateur_id AND date_commande = :date_commande";
            $update_stmt = $conn->prepare($update_query);
            $update_execute = $update_stmt->execute([
                ':recette' => $cout_livraison,
                ':utilisateur_id' => $livreur_id,
                ':date_commande' => $date
            ]);

            if (!$update_execute) {
                throw new Exception("Échec de la mise à jour de points_livreurs.");
            }
        } else {
            // Insérer un nouvel enregistrement
            $insert_query = "INSERT INTO points_livreurs (utilisateur_id, recette, depense, gain_jour, date_commande) 
                             VALUES (:utilisateur_id, :recette, 0, :recette, :date_commande)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_execute = $insert_stmt->execute([
                ':utilisateur_id' => $livreur_id,
                ':recette' => $cout_livraison,
                ':date_commande' => $date
            ]);

            if (!$insert_execute) {
                throw new Exception("Échec de l'insertion dans points_livreurs.");
            }
        }
    }

    // Valider la transaction
    $conn->commit();
    $_SESSION['popup'] = true;
    
    // Récupération des paramètres de redirection
    $redirect_page = $_POST['redirect_page'] ?? 'commandes.php';
    
    // Construction de l'URL de redirection avec les bons paramètres
    $params = [];
    
    // Gestion différente selon la page de redirection
    if ($redirect_page === 'page_recherche_date_reception.php') {
        if (isset($_POST['date'])) {
            $params[] = 'date=' . urlencode($_POST['date']);
        }
    } else {
        if (isset($_POST['recherche'])) {
            $params[] = 'recherche=' . urlencode($_POST['recherche']);
        }
    }
    
    if (isset($_POST['page'])) {
        $params[] = 'page=' . $_POST['page'];
    }
    if (isset($_POST['limit'])) {
        $params[] = 'limit=' . $_POST['limit'];
    }
    
    // Construction de l'URL finale
    $redirect_url = $redirect_page;
    if (!empty($params)) {
        $redirect_url .= '?' . implode('&', $params);
    }
    
    header('Location: ' . $redirect_url);
    exit(0);

} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $conn->rollBack();
    $_SESSION['error'] = "Une erreur s'est produite : " . $e->getMessage();
    
    // Récupération des paramètres de redirection
    $redirect_page = $_POST['redirect_page'] ?? 'commandes.php';
    
    // Construction de l'URL de redirection avec les bons paramètres
    $params = [];
    
    // Gestion différente selon la page de redirection
    if ($redirect_page === 'page_recherche_date_reception.php') {
        if (isset($_POST['date'])) {
            $params[] = 'date=' . urlencode($_POST['date']);
        }
    } else {
        if (isset($_POST['recherche'])) {
            $params[] = 'recherche=' . urlencode($_POST['recherche']);
        }
    }
    
    if (isset($_POST['page'])) {
        $params[] = 'page=' . $_POST['page'];
    }
    if (isset($_POST['limit'])) {
        $params[] = 'limit=' . $_POST['limit'];
    }
    
    // Construction de l'URL finale
    $redirect_url = $redirect_page;
    if (!empty($params)) {
        $redirect_url .= '?' . implode('&', $params);
    }
    
    header('Location: ' . $redirect_url);
    exit(0);
}
?>
