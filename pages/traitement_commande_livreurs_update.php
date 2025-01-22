<?php
// Connexion à la base de données (à adapter avec vos informations)
require_once '../inc/functions/connexion.php';   
//session_start(); 

// Récupération des données soumises via le formulaire
$commande_id = $_POST['commande_id'];
$livreur_id = $_POST['livreur_id'];


// Requête SQL d'update
$sql = "UPDATE commandes
        SET livreur_id = :livreur_id
        WHERE id = :id";

// Préparation de la requête
$requete = $conn->prepare($sql);

// Exécution de la requête avec les nouvelles valeurs
$query_execute = $requete->execute(array(
    ':livreur_id' => $livreur_id,
    ':id' => $commande_id
));

// Redirection vebarrs une page de confirmation ou de retour
//$query_execute = $requete->execute($data);
    
//var_dump($query_execute);
//die();
if($query_execute)
{
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
}
?>
