<?php

// Connexion à la base de données
require_once '../inc/functions/connexion.php';

// Configuration du mode d'erreur PDO pour les exceptions
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Requête pour récupérer les données
$query = "
    SELECT 
        boutiques.nom AS nom_boutique, 
        DATE(commandes.date_livraison) AS date_livraison, 
        SUM(commandes.cout_reel) AS total_cout_reel_par_jour
    FROM commandes
    JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id
    JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
    WHERE commandes.statut = 'Livré' AND commandes.date_livraison IS NOT NULL
    GROUP BY boutiques.nom, DATE(commandes.date_livraison)
";

// Exécuter la requête
$results = $conn->query($query);

// Vérifier si des résultats ont été retournés
if ($results->rowCount() > 0) {
    // Préparer la requête d'insertion
    $stmt = $conn->prepare("
        INSERT INTO table_total_cout_par_jour (nom_boutique, date_livraison, total_cout_reel_par_jour)
        VALUES (:nom_boutique, :date_livraison, :total_cout_reel_par_jour)
        ON DUPLICATE KEY UPDATE
            total_cout_reel_par_jour = IF(
                statut_paiement != 'Payé', 
                VALUES(total_cout_reel_par_jour), 
                total_cout_reel_par_jour
            )
    ");

    foreach ($results as $row) {
        // Exécution des données
        $sql = 'select  *  from table_total_cout_par_jour where nom_boutique = "'.$row['nom_boutique'].'" AND date_livraison = "'.$row['date_livraison'].'"';
        
        $q = $conn->query($sql);
        
        
        if($q->rowCount() == 0){
            
            $stmt->execute([
                'nom_boutique' => $row['nom_boutique'],
                'date_livraison' => $row['date_livraison'],
                'total_cout_reel_par_jour' => $row['total_cout_reel_par_jour'],
            ]);
        }
        
    }

    echo "Les données ont été insérées ou mises à jour si nécessaire.";
} else {
    echo "Aucun résultat à insérer.";
}
