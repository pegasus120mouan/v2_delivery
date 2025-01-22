<?php
require('../fpdf/fpdf.php');
require_once '../inc/functions/connexion.php'; // Assurez-vous que ce chemin est correct

if (isset($_POST['client']) && isset($_POST['month_start']) && isset($_POST['month_end']) && isset($_POST['year'])) {
    $month_start = (int)$_POST['month_start']; // Mois de début sélectionné
    $month_end = (int)$_POST['month_end']; // Mois de fin sélectionné
    $client = $_POST['client']; // Nom du client sélectionné
    $year = (int)$_POST['year']; // Année sélectionnée

    // Définir la locale en français
    setlocale(LC_TIME, 'fr_FR.UTF-8');

    // Tableau de correspondance des mois en français
    $mois_francais = [
        'January' => 'Janvier', 'February' => 'Février', 'March' => 'Mars', 'April' => 'Avril',
        'May' => 'Mai', 'June' => 'Juin', 'July' => 'Juillet', 'August' => 'Août',
        'September' => 'Septembre', 'October' => 'Octobre', 'November' => 'Novembre', 'December' => 'Décembre'
    ];

    // Execute SQL query to fetch data
    $sql = "SELECT
                b.nom AS nom_boutique,
                MONTH(c.date_reception) AS mois_num,
                MONTHNAME(c.date_reception) AS mois_commande,
                DATE_FORMAT(c.date_reception, '%Y') AS annee_commande,
                SUM(c.cout_reel) AS cout_reel_mensuel,
                COUNT(*) as nombre_commandes
            FROM
                boutiques b
            JOIN
                utilisateurs u ON b.id = u.boutique_id
            JOIN
                commandes c ON u.id = c.utilisateur_id
            WHERE
                MONTH(c.date_reception) BETWEEN :month_start AND :month_end
                AND b.nom = :client
                AND YEAR(c.date_reception) = :year
                AND c.statut = 'Livré'
            GROUP BY
                b.nom, 
                MONTH(c.date_reception),
                MONTHNAME(c.date_reception),
                DATE_FORMAT(c.date_reception, '%Y')
            ORDER BY
                b.nom, 
                annee_commande, 
                mois_num";

    $requete = $conn->prepare($sql);
    $requete->bindParam(':client', $client, PDO::PARAM_STR);
    $requete->bindParam(':month_start', $month_start, PDO::PARAM_INT);
    $requete->bindParam(':month_end', $month_end, PDO::PARAM_INT);
    $requete->bindParam(':year', $year, PDO::PARAM_INT);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Create PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Title
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, utf8_decode('Rapport des dépôts effectués'), 1, 1, 'C');

    // Client and date range
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, "Partenaire: $client", 0, 1, 'L');

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(20, 10, "De: ", 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(50, 10, utf8_decode("{$mois_francais[date('F', mktime(0, 0, 0, $month_start, 1))]} $year"), 0, 0, 'L');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(20, 10, "A: ", 0, 0, 'L');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, utf8_decode("{$mois_francais[date('F', mktime(0, 0, 0, $month_end, 1))]} $year"), 0, 1, 'L');

    // Table headers
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(192);
    $pdf->Cell(50, 10, 'Boutique', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Mois', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Montant', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Nbre Colis', 1, 1, 'C', true);
    $pdf->SetFillColor(255);

    // Data
    $pdf->SetFont('Arial', '', 12);
    $total = 0;
    $total_commandes = 0;
    foreach ($resultat as $row) {
        $total += $row['cout_reel_mensuel'];
        $total_commandes += $row['nombre_commandes'];
        $pdf->Cell(50, 10, utf8_decode($row['nom_boutique']), 1, 0, 'C');
        $mois_commande_fr = $mois_francais[$row['mois_commande']];
        $pdf->Cell(40, 10, utf8_decode($mois_commande_fr), 1, 0, 'C');
        $pdf->Cell(50, 10, number_format($row['cout_reel_mensuel'], 0, ',', ' '), 1, 0, 'C');
        $pdf->Cell(40, 10, number_format($row['nombre_commandes'], 0, ',', ' '), 1, 1, 'C');
    }

    // Total
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(0);
    $pdf->SetTextColor(255);
    $pdf->Cell(90, 10, 'Total', 1, 0, 'R', true);
    $pdf->Cell(50, 10, number_format($total, 0, ',', ' '), 1, 0, 'C', true);
    $pdf->Cell(40, 10, number_format($total_commandes, 0, ',', ' '), 1, 1, 'C', true);
    $pdf->SetTextColor(0);

    // Generate a dynamic filename
    $nom_fichier = sprintf(
        "Point_des_depots_effectues_du_%02d_%s_au_%02d_%s_en_%d_pour_%s.pdf",
        $month_start,
        strtolower($mois_francais[date('F', mktime(0, 0, 0, $month_start, 1))]),
        $month_end,
        strtolower($mois_francais[date('F', mktime(0, 0, 0, $month_end, 1))]),
        $year,
        str_replace(' ', '_', $client)
    );

    // Output the PDF with the dynamic filename
    $pdf->Output('I', $nom_fichier);

    // Close database connection
    $conn = null;
} else {
    echo "Veuillez sélectionner un client et une période de mois.";
}
?>
