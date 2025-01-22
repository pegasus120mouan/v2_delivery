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
        COALESCE(SUM(p.depense), 0) AS total_depense,
        SUM(c.cout_global) - COALESCE(SUM(p.depense), 0) AS montant_depot
    FROM
        commandes c
    JOIN
        utilisateurs u ON c.livreur_id = u.id
    LEFT JOIN
        points_livreurs p ON u.id = p.utilisateur_id 
                          AND DATE(p.date_commande) = DATE(c.date_reception)
    WHERE
        c.date_reception BETWEEN :start_date AND :end_date
        AND c.statut = 'Livré'
        AND u.id = :id_user
    GROUP BY
        DATE(c.date_reception),
        u.nom,
        u.prenoms
    ORDER BY
        DATE(c.date_reception),
        fullname_livreur";

    $requete = $conn->prepare($sql);
    $requete->bindParam(':id_user', $id_user);
    $requete->bindParam(':start_date', $start_date);
    $requete->bindParam(':end_date', $end_date);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Créer un fichier PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Titre
    $pdf->SetFont('Arial', 'B', 15);
    $pdf->Cell(0, 10, utf8_decode('Point des versements à effectuer'), 1, 1, 'C');
    $pdf->Ln(7);

    // Informations sur le livreur et la date
    $pdf->SetFont('Arial', 'B', 12); // Set font to Arial bold for the courier name
    $pdf->Cell(0, 10, "Coursier: " . (count($resultat) > 0 ? $resultat[0]['fullname_livreur'] : 'Non spécifié'), 0, 1, 'L');

    $pdf->SetFont('Arial', '', 12); // Set font to Arial normal for the dates
    $pdf->Cell(10, 10, utf8_decode("Du : "), 0, 0, 'L'); // Normal text
    $pdf->SetFont('Arial', 'B', 12); // Bold start date
    $pdf->Cell(25, 10, utf8_decode($startDateFormatted), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12); // Normal text
    $pdf->Cell(10, 10, utf8_decode(" Au : "), 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 12); // Bold end date
    $pdf->Cell(25, 10, utf8_decode($endDateFormatted), 0, 1, 'L');
    $pdf->SetFont('Arial', '', 12); // Reset to normal text for further usage
    $pdf->Ln(10);

    // En-tête du tableau
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(192); 
    $pdf->Cell(30, 10, 'Date', 1, 0, 'C', true); 
    $pdf->Cell(50, 10, 'Montant Global', 1, 0, 'C', true); 
    $pdf->Cell(50, 10, utf8_decode("Dépenses"), 1, 0, 'C', true); 
    $pdf->Cell(50, 10, utf8_decode("Montant à déposer"), 1, 0, 'C', true); 
    $pdf->SetFillColor(255);
    $pdf->Ln();

    // Données du tableau
    $pdf->SetFont('Arial', '', 12);
    $total_depot = 0;
    foreach ($resultat as $row) {
        $pdf->Cell(30, 10, (new DateTime($row['date_jour']))->format('d/m/Y'), 1, 0, 'C');
        $pdf->Cell(50, 10, number_format($row['total_cout_global'], 0, '', ' '), 1, 0, 'C');
        $pdf->Cell(50, 10, number_format($row['total_depense'], 0, '', ' '), 1, 0, 'C');
        $pdf->Cell(50, 10, number_format($row['montant_depot'], 0, '', ' '), 1, 0, 'C');
        $pdf->Ln();
        $total_depot += $row['montant_depot'];
    }

    // Total
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(173, 216, 230);
    $pdf->Cell(130, 10, 'Total', 1, 0, 'C', true);
    $pdf->Cell(50, 10, number_format($total_depot, 0, '', ' '), 1, 1, 'C', true);

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
                box-shadow: 0 4px 8px rgba(0,0,0,0.3);
                transition: all 0.3s ease;
                z-index: 1000;
            }
            .whatsapp-button:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 12px rgba(0,0,0,0.4);
            }
            .whatsapp-button i {
                font-size: 32px;
            }
        </style>
    </head>
    <body style="margin: 0; padding: 0;">
        <a href="<?php echo $whatsapp_url; ?>" class="whatsapp-button" target="_blank">
            <i class="fab fa-whatsapp"></i>
        </a>
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
    echo "Veuillez sélectionner un livreur et une période.";
}
?>
