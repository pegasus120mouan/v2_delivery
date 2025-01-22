<?php
require('../fpdf/fpdf.php');
require_once '../inc/functions/connexion.php';

if (isset($_POST['client']) && isset($_POST['date'])) {
    $client = $_POST['client'];
    $date = $_POST['date'];
    $formatted_date = date("d-m-Y", strtotime($date));

    // Execute SQL query to fetch data
    $sql = "SELECT 
                commandes.id as commande_id,
                utilisateur_id, livreur_id, communes, cout_global,
                cout_livraison, cout_reel, statut, date_commande, clients.id as id_client,
                clients.nom as client_nom, prenoms, contact, login, avatar, 
                boutique_id, 
                boutiques.nom as boutique_nom,
                boutiques.logo as boutique_logo
            FROM 
                `commandes`  
            JOIN 
                (SELECT * FROM utilisateurs WHERE role = 'clients') AS clients ON clients.id = commandes.utilisateur_id
            JOIN 
                boutiques ON clients.boutique_id = boutiques.id 
            HAVING 
                client_nom = :client AND date_commande = :date"; // Modifier la clause HAVING pour client_nom = :client
    $requete = $conn->prepare($sql);
    $requete->bindParam(':client', $client);
    $requete->bindParam(':date', $date);
    $requete->execute();
    $resultat = $requete->fetch(PDO::FETCH_ASSOC); // Utilisez fetch() au lieu de fetchAll()

    // Create PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Title
    $pdf->SetFont('Arial', 'B', 15);
    $pdf->Cell(0, 10, utf8_decode('Point des livraisons effectuées'), 1, 1, 'C');
    $pdf->Ln(7);

    // Client
    $pdf->SetFont('Arial', 'BU', 12);
    $pdf->Cell(0, 10, "Partenaire: " . $client, 0, 1, 'L');
    $logo_path = '../dossiers_images/' . $resultat['boutique_logo']; // Utilisez correctement le chemin du logo
    $pdf->Image($logo_path, 10, $pdf->GetY() + 10, 30); // Ajustez les coordonnées selon votre mise en page
    $pdf->Ln(10); 

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "Date: $formatted_date", 0, 1, 'L');

    // Table headers
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(192); 
    $pdf->Cell(60, 10, 'Communes', 1, 0, 'C', true); 
    $pdf->Cell(60, 10, 'Montant', 1, 0, 'C', true); 
    $pdf->Cell(60, 10, 'Statut', 1, 1, 'C', true); 
    $pdf->SetFillColor(255);

    // Data
    $pdf->SetFont('Arial', '', 12);
    $total = 0;
    $nb_livre = 0;
    $nb_non_livre = 0;
    foreach ($resultat as $row) {
        if ($row['statut'] == 'Livré') {
            $nb_livre++;
            $total += $row['cout_reel'];
            $pdf->Cell(60, 10, utf8_decode($row['communes']), 1, 0, 'C');
            $pdf->Cell(60, 10, $row['cout_reel'], 1, 0, 'C');
            $pdf->Cell(60, 10, utf8_decode($row['statut']), 1, 1, 'C');
        } else {
            $pdf->SetFillColor(255, 0, 0); // Rouge
            $pdf->SetTextColor(255, 0, 0); // Blanc pour le texte
            $nb_non_livre++;
            $pdf->Cell(60, 10, utf8_decode($row['communes']), 1, 0, 'C');
            $pdf->Cell(60, 10, $row['cout_reel'], 1, 0, 'C');
            $pdf->Cell(60, 10, utf8_decode($row['statut']), 1, 1, 'C');
        }
        $pdf->SetTextColor(0);
    }

    // Total
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetFillColor(0);
    $pdf->SetTextColor(255);
    $pdf->Cell(120, 10, 'Total', 1, 0, 'R', true);
    $pdf->Cell(60, 10, $total, 1, 1, 'C', true);

    // Output PDF
    $pdf->Output();

    // Close database connection
    $conn = null;
} else {
    echo "Veuillez sélectionner un client et une date.";
}
?>
