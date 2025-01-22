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

if (isset($_POST['livreur_id']) && isset($_POST['date'])) {
    $id_user = $_POST['livreur_id'];
    $date = $_POST['date'];

    // Exécuter la requête SQL pour récupérer les données
    $sql = "SELECT
                c.id AS commande_id,
                c.communes AS communes,
                c.cout_global AS cout_global,
                c.cout_livraison AS cout_livraison,
                c.cout_reel AS cout_reel,
                c.statut AS statut,
                c.date_reception AS date_reception,
                c.date_livraison AS date_livraison,
                c.date_retour AS date_retour,
                concat(u.nom, ' ', u.prenoms) as fullname_livreur,
                b.nom as boutique_nom
            FROM
                commandes c
            JOIN
                livreurs u ON c.livreur_id = u.id
            JOIN
                clients cl ON c.utilisateur_id = cl.id
            JOIN
                boutiques b ON b.id = cl.boutique_id
            WHERE
                DATE(c.date_reception) = :date
                AND u.id = :id_user
            ORDER BY c.date_reception DESC";

    $requete = $conn->prepare($sql);
    $requete->bindParam(':id_user', $id_user);
    $requete->bindParam(':date', $date);
    $requete->execute();
    $resultat = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Vérifier si des résultats ont été trouvés
    if (empty($resultat)) {
        echo "<div style='text-align: center; margin-top: 20px; font-family: Arial, sans-serif;'>";
        echo "<h3 style='color: #e74c3c;'>Aucune commande trouvée pour cette date</h3>";
        echo "<p>Veuillez sélectionner une autre date ou vérifier les critères de recherche.</p>";
        echo "<button onclick='window.history.back()' style='padding: 10px 20px; background-color: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer;'>Retour</button>";
        echo "</div>";
        exit;
    }

    // Créer un fichier PDF
    $pdf = new PDF('P', 'mm', 'A4');
    $pdf->SetAutoPageBreak(true, 25); // Marge bas de 25mm pour le footer
    $pdf->AddPage();

    // En-tête avec logo et infos OVL
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'OVL DELIVERY SERVICES', 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, utf8_decode('Sarl au Capital de 1 000 000 CFA'), 0, 1, 'C');
    $pdf->Cell(0, 5, 'Cocody Rivera Golf en face de l\'Ambassade des USA', 0, 1, 'C');
    $pdf->Cell(0, 5, utf8_decode('Tel: +225 0787703900 - +22505464283385'), 0, 1, 'C');
    $pdf->Cell(0, 5, 'Email: france@ovl-delivery.online', 0, 1, 'C');
    $pdf->Cell(0, 5, 'ovl-delivery.online', 0, 1, 'C');
    $pdf->Cell(0, 5, '+22505464283385', 0, 1, 'C');

    $pdf->Ln(10);

    // Cadre gris pour les informations du rapport
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Rect(10, $pdf->GetY(), 190, 20, 'F');
    
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 10, 'Informations du rapport:', 0, 1);
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(30, 5, 'Coursier: ', 0, 0);
    $pdf->Cell(0, 5, $resultat[0]['fullname_livreur'], 0, 1);
    
    $pdf->Cell(30, 5, 'Date: ', 0, 0);
    $pdf->Cell(0, 5, date('d/m/Y', strtotime($date)), 0, 1);

    $pdf->Ln(10);

    // En-têtes du tableau
    $pdf->SetFillColor(52, 152, 219); // Bleu clair
    $pdf->SetTextColor(255);
    $pdf->SetFont('Arial', 'B', 10);
    
    $pdf->Cell(40, 10, 'COMMUNES', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'MONTANT', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'STATUT', 1, 0, 'C', true);
    $pdf->Cell(40, 10, utf8_decode('RÉCEPTION'), 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'BOUTIQUE', 1, 1, 'C', true);

    // Données du tableau
    $pdf->SetTextColor(0);
    $pdf->SetFont('Arial', '', 10);
    $total = 0;
    $commandes_livrees = 0;

    foreach ($resultat as $row) {
        // Fond rose pour non livré
        if ($row['statut'] != 'Livré') {
            $pdf->SetFillColor(255, 228, 225);
        } else {
            $pdf->SetFillColor(255, 255, 255);
            $total += $row['cout_reel'];
            $commandes_livrees++;
        }

        $pdf->Cell(40, 10, utf8_decode($row['communes']), 1, 0, 'C', true);
        $pdf->Cell(40, 10, number_format($row['cout_reel'], 0, ',', ' ') . ' F', 1, 0, 'C', true);
        $pdf->Cell(35, 10, utf8_decode($row['statut']), 1, 0, 'C', true);
        $pdf->Cell(40, 10, date('d/m/Y', strtotime($row['date_reception'])), 1, 0, 'C', true);
        $pdf->Cell(35, 10, utf8_decode($row['boutique_nom']), 1, 1, 'C', true);
    }

    $pdf->Ln(10);

    // Tableau des statistiques
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(44, 62, 80); // Bleu foncé
    $pdf->SetTextColor(255);
    $pdf->Cell(0, 10, 'STATISTIQUES', 1, 1, 'C', true);
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(0);
    
    // Données statistiques
    $total_commandes = count($resultat);
    $taux_livraison = ($total_commandes > 0) ? ($commandes_livrees / $total_commandes) * 100 : 0;
    
    $pdf->Cell(150, 10, 'Nombre total de commandes:', 1, 0, 'L');
    $pdf->Cell(40, 10, $total_commandes, 1, 1, 'C');
    
    $pdf->Cell(150, 10, utf8_decode('Commandes livrées:'), 1, 0, 'L');
    $pdf->Cell(40, 10, $commandes_livrees, 1, 1, 'C');
    
    $pdf->Cell(150, 10, 'Taux de livraison:', 1, 0, 'L');
    $pdf->Cell(40, 10, number_format($taux_livraison, 1) . '%', 1, 1, 'C');
    
    $pdf->Cell(150, 10, 'Montant total des livraisons:', 1, 0, 'L');
    $pdf->Cell(40, 10, number_format($total, 0, ',', ' ') . ' F', 1, 1, 'C');

    // Format the date for the file name
    $nom_livreur = str_replace(' ', '_', trim($resultat[0]['fullname_livreur']));
    $date_formattee = date('d-m-Y', strtotime($date));
    $nom_fichier = utf8_decode('Rapport_de_' . $nom_livreur . '_du_' . $date_formattee . '.pdf');
    
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
    $message = "Rapport des livraisons de " . $resultat[0]['fullname_livreur'] . " du " . $date_formattee;
    $whatsapp_url = 'https://api.whatsapp.com/send?text=' . urlencode($message . "\n" . $file_url);

    // Afficher le PDF avec le bouton WhatsApp flottant
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Rapport de livraison</title>
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
    echo "Veuillez sélectionner un livreur et une date.";
}
?>
