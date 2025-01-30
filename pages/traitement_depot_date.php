<?php
require('../fpdf/fpdf.php');
require_once '../inc/functions/connexion.php';

if (isset($_POST['id_livreur']) && isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $id_user = $_POST['id_livreur'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Convert dates to French format
    $startDateObj = new DateTime($start_date);
    $endDateObj = new DateTime($end_date);

    $startDateFormatted = $startDateObj->format('d/m/Y');
    $endDateFormatted = $endDateObj->format('d/m/Y');

    // Exécuter la requête SQL pour récupérer les données
    $sql = "SELECT 
        DATE(c.date_reception) AS date_jour,
        CONCAT(u.nom, ' ', u.prenoms) AS fullname_livreur,
        SUM(c.cout_global) AS total_cout_global,
        COALESCE(de.depense, 0) AS total_depense,
        SUM(c.cout_global) - COALESCE(de.depense, 0) AS montant_depot
    FROM
        commandes c
    JOIN
        utilisateurs u ON c.livreur_id = u.id
    LEFT JOIN (
        SELECT 
            utilisateur_id,
            DATE(date_commande) as expense_date,
            MAX(depense) as depense
        FROM points_livreurs
        GROUP BY utilisateur_id, DATE(date_commande)
    ) de ON de.utilisateur_id = u.id 
        AND de.expense_date = DATE(c.date_reception)
    WHERE
        c.date_reception BETWEEN :start_date AND :end_date
        AND c.statut = 'Livré'
        AND u.id = :id_user
    GROUP BY
        DATE(c.date_reception),
        u.nom,
        u.prenoms,
        de.depense
    ORDER BY
        DATE(c.date_reception)";

    $requete = $conn->prepare($sql);
    $requete->bindParam(':id_user', $id_user);
    $requete->bindParam(':start_date', $start_date);
    $requete->bindParam(':end_date', $end_date);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Créer un fichier PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Set margins
    $pdf->SetMargins(15, 15, 15);
    
    // Header with logo if exists
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->SetTextColor(44, 62, 80); // Dark blue color
    $pdf->Cell(0, 15, utf8_decode('POINT DES VERSEMENTS'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, utf8_decode('État des versements à effectuer'), 0, 1, 'C');
    $pdf->Ln(5);

    // Add separator line
    $pdf->SetDrawColor(44, 62, 80);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(10);

    // Informations sur le livreur et la date
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 8, "Coursier:", 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, (count($resultat) > 0 ? $resultat[0]['fullname_livreur'] : 'Non spécifié'), 0, 1, 'L');
    
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 8, utf8_decode("Période:"), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, utf8_decode("Du " . $startDateFormatted . " au " . $endDateFormatted), 0, 1, 'L');
    $pdf->Ln(8);

    // En-tête du tableau
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetFillColor(44, 62, 80); // Dark blue background
    $pdf->SetTextColor(255, 255, 255); // White text
    $pdf->Cell(35, 10, 'Date', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Montant Global', 1, 0, 'C', true);
    $pdf->Cell(45, 10, utf8_decode("Dépenses"), 1, 0, 'C', true);
    $pdf->Cell(50, 10, utf8_decode("Montant à déposer"), 1, 0, 'C', true);
    $pdf->Ln();

    // Données du tableau
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(44, 62, 80); // Reset text color to dark blue
    $pdf->SetFillColor(245, 247, 250); // Light gray for alternating rows
    $total_depot = 0;
    $row_count = 0;

    foreach ($resultat as $row) {
        $fill = $row_count % 2 == 0;
        $pdf->Cell(35, 10, (new DateTime($row['date_jour']))->format('d/m/Y'), 1, 0, 'C', $fill);
        $pdf->Cell(50, 10, number_format($row['total_cout_global'], 0, '', ' ') . ' FCFA', 1, 0, 'R', $fill);
        $pdf->Cell(45, 10, number_format($row['total_depense'], 0, '', ' ') . ' FCFA', 1, 0, 'R', $fill);
        $pdf->Cell(50, 10, number_format($row['montant_depot'], 0, '', ' ') . ' FCFA', 1, 0, 'R', $fill);
        $pdf->Ln();
        $total_depot += $row['montant_depot'];
        $row_count++;
    }

    // Total
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetFillColor(44, 62, 80);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(130, 12, 'TOTAL', 1, 0, 'C', true);
    $pdf->Cell(50, 12, number_format($total_depot, 0, '', ' ') . ' FCFA', 1, 1, 'R', true);

    // Add footer
    $pdf->SetY(-30);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetTextColor(128);
    $pdf->Cell(0, 10, utf8_decode('Document généré le ' . date('d/m/Y à H:i')), 0, 1, 'C');
    $pdf->Cell(0, 10, utf8_decode('Page ' . $pdf->PageNo()), 0, 0, 'C');

    // Format the date for the file name
    $nom_fichier = 'Point_des_versements_du_' . str_replace('/', '-', $startDateFormatted) . '_au_' . str_replace('/', '-', $endDateFormatted) . '.pdf';
    
    // Créer le dossier reports s'il n'existe pas
    $upload_dir = dirname(__FILE__) . '/../reports/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Chemin complet du fichier
    $file_path = $upload_dir . $nom_fichier;
    
    // Sauvegarder le PDF
    $pdf->Output('F', $file_path);
    
    // URL complète du fichier
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $file_url = $base_url . '/ovl-delivery/reports/' . $nom_fichier;
    
    // Message pour WhatsApp
    $message = "Point des versements du " . $startDateFormatted . " au " . $endDateFormatted;
    $whatsapp_url = 'https://api.whatsapp.com/send?text=' . urlencode($message . "\n" . $file_url);
    
    // Afficher le PDF avec le bouton WhatsApp flottant
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Point des versements</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                background-color: #f8f9fa;
            }
            
            .loading-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.9);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                z-index: 2000;
            }
            
            .loading-spinner {
                width: 50px;
                height: 50px;
                border: 5px solid #f3f3f3;
                border-top: 5px solid #25d366;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin-bottom: 15px;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .whatsapp-button {
                position: fixed;
                bottom: 30px;
                right: 30px;
                background-color: #25d366;
                color: white;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
                transition: all 0.3s ease;
                z-index: 1000;
            }
            
            .whatsapp-button:hover {
                transform: scale(1.1);
                background-color: #20ba5a;
                box-shadow: 0 6px 16px rgba(37, 211, 102, 0.5);
            }
            
            .whatsapp-button i {
                font-size: 32px;
            }
        </style>
    </head>
    <body>
        <div class="loading-overlay">
            <div class="loading-spinner"></div>
            <p>Génération du PDF en cours...</p>
        </div>
        
        <a href="<?php echo $whatsapp_url; ?>" class="whatsapp-button" target="_blank" title="Partager via WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
        
        <script>
            window.onload = function() {
                document.querySelector('.loading-overlay').style.display = 'none';
            };
        </script>
        <?php
        // Afficher le PDF
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nom_fichier . '"');
        readfile($file_path);
        ?>
    </body>
    </html>
    <?php
} else {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Erreur - Point des versements</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                background-color: #f8f9fa;
            }
            
            .error-message {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: #fff;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                text-align: center;
            }
            
            .error-message i {
                font-size: 48px;
                color: #e74c3c;
                margin-bottom: 15px;
                display: block;
            }
            
            .error-message h2 {
                color: #2c3e50;
                margin: 0 0 15px 0;
            }
            
            .error-message p {
                color: #7f8c8d;
                margin: 0;
            }
            
            .back-button {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 20px;
                background-color: #3498db;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                transition: background-color 0.3s ease;
            }
            
            .back-button:hover {
                background-color: #2980b9;
            }
        </style>
    </head>
    <body>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <h2>Paramètres manquants</h2>
            <p>Veuillez sélectionner un livreur et une période pour générer le rapport.</p>
            <a href="javascript:history.back()" class="back-button">Retour</a>
        </div>
    </body>
    </html>
    <?php
}
?>
