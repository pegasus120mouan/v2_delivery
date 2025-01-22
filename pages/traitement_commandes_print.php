<?php
require('../fpdf/fpdf.php');
require_once '../inc/functions/connexion.php';

class PDF extends FPDF {
    // Pied de page
    function Footer() {
        // Position à 1,5 cm du bas
        $this->SetY(-20);
        
        // Ligne de séparation
        $this->SetDrawColor(44, 62, 80);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        
        // Informations de l'entreprise
        $this->SetY(-15);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(44, 62, 80); // Bleu foncé
        $this->Cell(0, 5, utf8_decode('Sarl au Capital de 1 000 000 CFA // Tel: +225 0787703000 - +2250584828385 // whatsapp: +2250584828385'), 0, 1, 'C');
        
        // Numéro de page
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 5, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }

    // Méthode pour créer un rectangle avec coins arrondis
    function RoundedRect($x, $y, $w, $h, $r, $style = '') {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));

        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', 
            $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k,
            $x3*$this->k, ($h-$y3)*$this->k));
    }
}

if (isset($_POST['client']) && isset($_POST['date'])) {
    $client = $_POST['client'];
    $date = $_POST['date'];
    $formatted_date = date("d-m-Y", strtotime($date));

    // Execute SQL query to fetch data
    $sql = "SELECT 
                commandes.id as commande_id,
                utilisateur_id, livreur_id, communes, cout_global,
                cout_livraison, cout_reel, statut, date_reception, date_livraison, 
                clients.id as id_client,
                clients.nom as client_nom, prenoms, contact, login, avatar, 
                boutique_id, 
                boutiques.nom as boutique_nom, boutiques.logo as boutique_logo
            FROM 
                `commandes`  
            JOIN 
                (SELECT * FROM utilisateurs WHERE role = 'clients') AS clients ON clients.id = commandes.utilisateur_id
            JOIN 
                boutiques ON clients.boutique_id = boutiques.id 
            HAVING 
                boutique_nom = :client AND date_livraison = :date OR date_livraison IS nuLL and date_reception=:date";

    $requete = $conn->prepare($sql);
    $requete->bindParam(':client', $client);
    $requete->bindParam(':date', $date);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Create PDF
    $pdf = new PDF();
    $pdf->SetAutoPageBreak(true, 25);
    $pdf->AddPage();

    // Informations sur le client et la date dans un cadre
    $pdf->SetFillColor(236, 240, 241); // Gris très clair
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(44, 62, 80); // Bleu foncé
    
    // Rectangle d'information avec coins arrondis
    $pdf->RoundedRect(10, $pdf->GetY(), 190, 25, 3.5, 'DF');
    $pdf->SetX(15);
    $pdf->Cell(0, 8, "Informations du rapport:", 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->SetX(15);
    $pdf->Cell(0, 8, "Partenaire: " . utf8_decode($client), 0, 1);
    $pdf->SetX(15);
    $pdf->Cell(0, 8, "Date: " . date('d/m/Y', strtotime($date)), 0, 1);
    
    $pdf->Ln(10);

    // En-tête du tableau
    $pdf->SetFont('Arial', 'B', 11);
    
    $pdf->SetFillColor(52, 152, 219); // Bleu
    $pdf->SetTextColor(255, 255, 255); // Blanc

    // Définition des largeurs de colonnes
    $largeur_colonne1 = 40; // COMMUNES
    $largeur_colonne2 = 40; // DATE RÉCEPTION
    $largeur_colonne3 = 40; // DATE LIVRAISON
    $largeur_colonne4 = 40; // MONTANT
    $largeur_colonne5 = 30; // STATUT
    $largeur_total = $largeur_colonne1 + $largeur_colonne2 + $largeur_colonne3 + $largeur_colonne4 + $largeur_colonne5;

    $pdf->Cell($largeur_colonne1, 10, 'COMMUNES', 1, 0, 'C', true);
    $pdf->Cell($largeur_colonne2, 10, utf8_decode('DATE RÉCEPTION'), 1, 0, 'C', true);
    $pdf->Cell($largeur_colonne3, 10, 'DATE LIVRAISON', 1, 0, 'C', true);
    $pdf->Cell($largeur_colonne4, 10, 'MONTANT', 1, 0, 'C', true);
    $pdf->Cell($largeur_colonne5, 10, 'STATUT', 1, 1, 'C', true);

    // Données du tableau
    $pdf->SetFont('Arial', '', 10);
    $total = 0;
    $nb_livre = 0;
    $nb_non_livre = 0;

    foreach ($resultat as $row) {
        if ($row['statut'] == 'Livré') {
            $nb_livre++;
            $total += $row['cout_reel'];
            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(44, 62, 80);
        } else {
            $pdf->SetFillColor(255, 236, 236); // Rouge très clair
            $pdf->SetTextColor(231, 76, 60); // Rouge
            $nb_non_livre++;
        }
        
        $pdf->Cell($largeur_colonne1, 10, utf8_decode($row['communes']), 1, 0, 'C', true);
        $pdf->Cell($largeur_colonne2, 10, date('d/m/Y', strtotime($row['date_reception'])), 1, 0, 'C', true);
        $pdf->Cell($largeur_colonne3, 10, date('d/m/Y', strtotime($row['date_livraison'])), 1, 0, 'C', true);
        $pdf->Cell($largeur_colonne4, 10, number_format($row['cout_reel'], 0, ',', ' ') . ' F', 1, 0, 'C', true);
        $pdf->Cell($largeur_colonne5, 10, utf8_decode($row['statut']), 1, 1, 'C', true);
    }

    // Statistiques
    $pdf->Ln(10);
    
    // Titre des statistiques
    $pdf->SetFillColor(44, 62, 80); // Bleu foncé
    $pdf->SetTextColor(255, 255, 255); // Blanc
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell($largeur_total, 10, 'STATISTIQUES', 1, 1, 'C', true);
    
    // Contenu des statistiques
    $pdf->SetTextColor(0); // Noir
    $pdf->SetFont('Arial', '', 11);
    
    // Calcul du taux de livraison
    $total_commandes = $nb_livre + $nb_non_livre;
    $taux_livraison = ($total_commandes > 0) ? ($nb_livre / $total_commandes) * 100 : 0;
    
    // Tableau des statistiques avec la même largeur totale que le tableau principal
    $pdf->SetFillColor(255, 255, 255); // Blanc
    $largeur_stats_col1 = $largeur_total - 50; // Colonne des libellés
    $largeur_stats_col2 = 50; // Colonne des valeurs
    
    $pdf->Cell($largeur_stats_col1, 10, 'Nombre total de commandes:', 1, 0, 'L');
    $pdf->Cell($largeur_stats_col2, 10, $total_commandes, 1, 1, 'C');
    
    $pdf->Cell($largeur_stats_col1, 10, utf8_decode('Commandes livrées:'), 1, 0, 'L');
    $pdf->Cell($largeur_stats_col2, 10, $nb_livre, 1, 1, 'C');
    
    $pdf->Cell($largeur_stats_col1, 10, 'Taux de livraison:', 1, 0, 'L');
    $pdf->Cell($largeur_stats_col2, 10, number_format($taux_livraison, 1) . '%', 1, 1, 'C');
    
    $pdf->Cell($largeur_stats_col1, 10, 'Montant total des livraisons:', 1, 0, 'L');
    $pdf->Cell($largeur_stats_col2, 10, number_format($total, 0, ',', ' ') . ' F', 1, 1, 'C');

    // Total avec la même largeur totale
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(0); // Black background for total row
    $pdf->SetTextColor(255); // White text for total row
    $pdf->Cell($largeur_total, 10, 'Total: ' . number_format($total, 0, ',', ' ') . ' F', 1, 1, 'C', true);

    // Format the date for the file name
    $formatted_date_for_filename = date("d-m-Y", strtotime($date));
    $nom_fichier = 'Point_du_' . $formatted_date_for_filename . '_de_' . $client . '.pdf';
    
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
    $message = "Point des livraisons de " . $client . " du " . $formatted_date;
    $whatsapp_url = 'https://api.whatsapp.com/send?text=' . urlencode($message . "\n" . $file_url);
    
    // Afficher le PDF avec le bouton WhatsApp flottant
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Point des livraisons</title>
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

    // Close database connection
    $conn = null;
} else {
    echo "Veuillez sélectionner un client et une date.";
}
?>
