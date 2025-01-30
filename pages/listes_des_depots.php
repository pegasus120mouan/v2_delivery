<?php
//session_start();
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_commandes.php';
require_once '../inc/functions/requete/requetes_selection_boutique.php';
include('header.php');

// Vérifier si le bouton refresh a été cliqué
/*if(isset($_POST['refresh'])) {
    include 'script.php';
    header('Location: listes_des_depots.php');
    exit;
}*/

$query = $conn->prepare("
SELECT 
    ttc.id AS id_total_cout,
    ttc.nom_boutique,
    ttc.date_livraison,
    ttc.total_cout_reel_par_jour,
    ttc.statut_paiement,
    tp.operateur AS type_paiement_operateur,
    tp.logo AS type_paiement_logo
FROM 
    table_total_cout_par_jour ttc
LEFT JOIN 
    type_paiement tp
ON 
    ttc.type_paiement_id = tp.id ORDER BY TTC.date_livraison DESC
");

$query->execute();
$resultats = $query->fetchAll(PDO::FETCH_ASSOC);

$liste_boutiques = $getBoutique->fetchAll(PDO::FETCH_ASSOC);

$limit = $_GET['limit'] ?? 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$commande_pages = array_chunk($resultats, $limit );
//$commandes_list = $commande_pages[$_GET['page'] ?? ] ;
$commandes_list = $commande_pages[$page - 1] ?? [];

//var_dump($commandes_list);


?>




<!-- Main row -->
<style>
  .pagination-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 20px;
}

.pagination-link {
    padding: 8px;
    text-decoration: none;
    color: white;
    background-color: #007bff; 
    border: 1px solid #007bff;
    border-radius: 4px; 
    margin-right: 4px;
}

.items-per-page-form {
    margin-left: 20px;
}

label {
    margin-right: 5px;
}

.items-per-page-select {
    padding: 6px;
    border-radius: 4px; 
}

.submit-button {
    padding: 6px 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px; 
    cursor: pointer;
}
 .custom-icon {
            color: green;
            font-size: 24px;
            margin-right: 8px;
 }
 .spacing {
    margin-right: 10px; 
    margin-bottom: 20px;
}
</style>

  <style>
        @media only screen and (max-width: 767px) {
            
            th {
                display: none; 
            }
            tbody tr {
                display: block;
                margin-bottom: 20px;
                border: 1px solid #ccc;
                padding: 10px;
            }
            tbody tr td::before {

                font-weight: bold;
                margin-right: 5px;
            }
        }
        .margin-right-15 {
        margin-right: 15px;
       }
        .block-container {
      background-color:  #d7dbdd ;
      padding: 20px;
      border-radius: 5px;
      width: 100%;
      margin-bottom: 20px;
    }
    </style>

    <style>
.order-details-modal .modal-content {
    border: none;
    border-radius: 8px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    max-height: 90vh;
    overflow-y: auto;
}

.order-details-modal .modal-header {
    background: #2c3e50;
    color: white;
    border-radius: 8px 8px 0 0;
    padding: 15px 20px;
}

.order-details-modal .modal-body {
    padding: 20px;
}

.order-details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.detail-item {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 6px;
    border-left: 4px solid #3498db;
}

.detail-label {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 3px;
}

.detail-value {
    font-size: 1rem;
    color: #2c3e50;
    font-weight: 600;
}

.order-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 15px;
}

.action-btn {
    padding: 8px 15px;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
    width: 100%;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none !important;
}

.action-btn i {
    font-size: 16px;
}

.action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.action-btn:active {
    transform: translateY(0);
}

.btn-modify {
    background: #3498db;
    color: white !important;
}

.btn-modify:hover {
    background: #2980b9;
}

.btn-delivery {
    background: #95a5a6;
    color: white !important;
}

.btn-delivery:hover {
    background: #7f8c8d;
}

.btn-status {
    background: #e67e22;
    color: white !important;
}

.btn-status:hover {
    background: #d35400;
}

.btn-client {
    background: #2ecc71;
    color: white !important;
}

.btn-client:hover {
    background: #27ae60;
}

.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.status-delivered {
    background: #dff0d8;
    color: #3c763d;
}

.status-pending {
    background: #fcf8e3;
    color: #8a6d3b;
}
</style>

<style>
/* Styles généraux pour les textes */
.table {
    font-size: 16px !important;
}

.badge {
    font-size: 14px !important;
    padding: 8px 15px !important;
}

.btn {
    font-size: 14px !important;
}

.thead-dark th {
    font-size: 16px !important;
    padding: 15px !important;
}

/* Styles pour la pagination */
.pagination-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 30px 0;
    gap: 15px;
}

.pagination-info {
    font-size: 16px;
    font-weight: 500;
    padding: 10px 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pagination-button {
    padding: 10px 20px;
    font-size: 16px !important;
    font-weight: 500;
    color: #fff;
    background-color: #007bff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.pagination-button:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.pagination-button:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}

.items-per-page-form {
    display: flex;
    align-items: center;
    gap: 10px;
    background-color: #f8f9fa;
    padding: 10px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.items-per-page-form label {
    font-size: 16px;
    font-weight: 500;
    color: #495057;
}

.items-per-page-form select {
    font-size: 16px;
    padding: 8px 15px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    background-color: #fff;
    cursor: pointer;
}
</style>

<style>
.action-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
    align-items: center;
}

.btn-action {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    border: none;
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.btn-action::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(100%);
    transition: transform 0.2s ease;
}

.btn-action:hover::before {
    transform: translateY(0);
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.btn-action:active {
    transform: translateY(0);
}

.btn-action.btn-info {
    background: linear-gradient(145deg, #1ab6cf, #148a9c);
    box-shadow: 0 2px 10px rgba(23, 162, 184, 0.3);
}

.btn-action.btn-danger {
    background: linear-gradient(145deg, #e84c3d, #c0392b);
    box-shadow: 0 2px 10px rgba(220, 53, 69, 0.3);
}

.btn-action i {
    font-size: 14px;
    color: white;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    z-index: 1;
}

/* Tooltip personnalisé */
.btn-action[title]:hover::after {
    content: attr(title);
    position: absolute;
    bottom: -30px;
    left: 50%;
    transform: translateX(-50%);
    padding: 4px 8px;
    background: rgba(0,0,0,0.8);
    color: white;
    font-size: 12px;
    border-radius: 4px;
    white-space: nowrap;
    z-index: 10;
}
</style>

<style>
/* Styles généraux pour les textes */
.table {
    font-size: 16px !important;
}

.badge {
    font-size: 14px !important;
    padding: 8px 15px !important;
}

.btn {
    font-size: 14px !important;
}

.thead-dark th {
    font-size: 16px !important;
    padding: 15px !important;
}

/* Styles pour la pagination */
.pagination-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 30px 0;
    gap: 15px;
}

.pagination-info {
    font-size: 16px;
    font-weight: 500;
    padding: 10px 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pagination-button {
    padding: 10px 20px;
    font-size: 16px !important;
    font-weight: 500;
    color: #fff;
    background-color: #007bff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.pagination-button:hover {
    background-color: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.pagination-button:disabled {
    background-color: #6c757d;
    cursor: not-allowed;
}

.items-per-page-form {
    display: flex;
    align-items: center;
    gap: 10px;
    background-color: #f8f9fa;
    padding: 10px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.items-per-page-form label {
    font-size: 16px;
    font-weight: 500;
    color: #495057;
}

.items-per-page-form select {
    font-size: 16px;
    padding: 8px 15px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    background-color: #fff;
    cursor: pointer;
}
</style>

<style>
.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.btn-action {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.btn-action.btn-info {
    background: #17a2b8;
}

.btn-action.btn-danger {
    background: #dc3545;
}

.btn-action i {
    font-size: 16px;
    color: white;
}
</style>

<!-- Loader -->
<div id="loader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Chargement...</span>
        </div>
        <div class="text-light mt-2">Mise à jour des données en cours...</div>
    </div>
</div>

<div class="content-header">
    <div class="container-fluid">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success_message'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error_message'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </div>
</div>

<div class="table-responsive">
    <div style="margin-bottom: 20px;">
        <form action="refresh.php" method="POST">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-sync-alt mr-2"></i> Actualiser les données
            </button>
        </form>
    </div>
    <table id="example1" class="table table-bordered table-striped">
        <thead class="thead-dark">
        <tr>
            <th>Boutique</th>
            <th>Date Livraison</th>
            <th>Montant à regler</th>
            <th>Statut paiement</th>
            <th>Opérateur</th>
            <th>Actions</th>
        </tr>
      </thead>
    <tbody>
      <?php foreach ($commandes_list as $resultat) : ?>
        <tr>
          <td>
              <span class="badge badge-primary">
                <?= htmlspecialchars($resultat['nom_boutique']) ?>
              </span>
          </td>
          <td><?= date('d/m/Y', strtotime($resultat['date_livraison'])) ?></td>
          <td>
                <span class="badge badge-success">
                    <?= number_format($resultat['total_cout_reel_par_jour'], 0, ',', ' ') ?> FCFA
                </span>
           </td>
           <td>
                    <?php if ($resultat['statut_paiement'] === 'Payé'): ?>
                        <span class="badge badge-success">
                            <i class="fa fa-check"></i>
                        </span>
                    <?php else: ?>
                        <span class="badge badge-danger">
                            <i class="fa fa-times"></i>
                        </span>
                    <?php endif; ?>
            </td>

            <td>
                    <?php if ($resultat['type_paiement_logo']): ?>
                        <img src="../dossiers_paiement/<?= htmlspecialchars($resultat['type_paiement_logo']) ?>" alt="<?= htmlspecialchars($resultat['type_paiement_operateur']) ?>" style="max-height: 50px;">
                    <?php else: ?>
                        <span class="text-muted">Aucun logo</span>
                    <?php endif; ?>
             </td>


           <td>
                <div class="action-buttons">
                    <button class="btn-action btn-info" title="Voir les détails" data-toggle="modal" data-target="#updatePaymentModal<?= $resultat['id_total_cout'] ?>">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-action btn-danger" title="Supprimer" onclick="confirmDelete(<?= $resultat['id_total_cout'] ?>)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>


                <!-- Modal pour la mise à jour du paiement -->
                <div class="modal fade" id="updatePaymentModal<?= $resultat['id_total_cout'] ?>" tabindex="-1" role="dialog" aria-labelledby="updatePaymentModalLabel<?= $resultat['id_total_cout'] ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="updatePaymentModalLabel<?= $resultat['id_total_cout'] ?>">Mise à jour du paiement</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form id="updatePaymentForm<?= $resultat['id_total_cout'] ?>" method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="id_total_cout" value="<?= $resultat['id_total_cout'] ?>">
                                    
                                    <div class="form-group">
                                        <label for="statut_paiement<?= $resultat['id_total_cout'] ?>">Statut de paiement</label>
                                        <select class="form-control" id="statut_paiement<?= $resultat['id_total_cout'] ?>" name="statut_paiement" required>
                                            <option value="">Sélectionner un statut</option>
                                            <option value="Payé" <?= ($resultat['statut_paiement'] === 'Payé') ? 'selected' : '' ?>>Payé</option>
                                            <option value="Non payé" <?= ($resultat['statut_paiement'] === 'Non payé') ? 'selected' : '' ?>>Non payé</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="type_paiement<?= $resultat['id_total_cout'] ?>">Type de paiement</label>
                                        <select class="form-control" id="type_paiement<?= $resultat['id_total_cout'] ?>" name="type_paiement_id" required>
                                            <option value="">Sélectionner un type de paiement</option>
                                            <?php
                                            $query_type_paiement = $conn->query("SELECT * FROM type_paiement ORDER BY operateur");
                                            while ($type = $query_type_paiement->fetch(PDO::FETCH_ASSOC)) {
                                                $selected = ($type['id'] == $resultat['type_paiement_id']) ? 'selected' : '';
                                                echo "<option value='" . $type['id'] . "' " . $selected . ">" . htmlspecialchars($type['operateur']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal pour la suppression du paiement -->
                <div class="modal fade" id="deletePaymentModal<?= $resultat['id_total_cout'] ?>" tabindex="-1" role="dialog" aria-labelledby="deletePaymentModalLabel<?= $resultat['id_total_cout'] ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deletePaymentModalLabel<?= $resultat['id_total_cout'] ?>">Confirmer la suppression</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>Êtes-vous sûr de vouloir supprimer ce paiement ?</p>
                                <p><strong>Boutique:</strong> <?= $resultat['nom_boutique'] ?></p>
                                <p><strong>Date:</strong> <?= $resultat['date_livraison'] ?></p>
                                <p><strong>Montant:</strong> <?= number_format($resultat['total_cout_reel_par_jour'], 0, ',', ' ') ?> FCFA</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <form action="traitement_delete_depot.php" method="POST" style="display: inline;" id="deleteForm<?= $resultat['id_total_cout'] ?>">
                                    <input type="hidden" name="id_total_cout" value="<?= $resultat['id_total_cout'] ?>">
                                    <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
           </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<div class="pagination-container">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page-1 ?>&limit=<?= $limit ?>" class="pagination-button">
            <i class="fas fa-chevron-left"></i> Précédent
        </a>
    <?php endif; ?>

    <div class="pagination-info">
        Page <?= $page ?> sur <?= ceil(count($resultats) / $limit) ?>
    </div>

    <?php if ($page < ceil(count($resultats) / $limit)): ?>
        <a href="?page=<?= $page+1 ?>&limit=<?= $limit ?>" class="pagination-button">
            Suivant <i class="fas fa-chevron-right"></i>
        </a>
    <?php endif; ?>

    <div class="items-per-page-form">
        <form action="" method="GET" class="d-flex align-items-center">
            <label for="limit">Afficher :</label>
            <select name="limit" id="limit" onchange="this.form.submit()">
                <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100</option>
            </select>
        </form>
    </div>
</div>

<!-- Bouton refresh -->


<script>
document.getElementById('refreshForm').addEventListener('submit', function(e) {
    // Afficher le loader
    document.getElementById('loader').style.display = 'block';
    
    // Désactiver le bouton pendant le chargement
    this.querySelector('button[type="submit"]').disabled = true;
});

// Si un message de succès est affiché, le faire disparaître après 3 secondes
document.addEventListener('DOMContentLoaded', function() {
    var successMessage = document.querySelector('.alert-success');
    if (successMessage) {
        setTimeout(function() {
            successMessage.style.opacity = '0';
            setTimeout(function() {
                successMessage.remove();
            }, 300);
        }, 3000);
    }
});
</script>

<style>
#loader {
    transition: opacity 0.3s ease-in-out;
}

.alert-success {
    transition: opacity 0.3s ease-in-out;
}
</style>

<!-- /.row (main row) -->
</div><!-- /.container-fluid -->
<!-- /.content -->
</div>


<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
  <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<!-- <script>
  $.widget.bridge('uibutton', $.ui.button)
</script>-->
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../../plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="../../plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="../../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="../../plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="../../plugins/moment/moment.min.js"></script>
<script src="../../plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="../../plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="../../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>

</body>

</html>

<script>
$(document).ready(function() {
    // Gestion de la soumission du formulaire pour chaque modal
    $('[id^="updatePaymentForm"]').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        const modalId = $(this).closest('.modal').attr('id');

        $.ajax({
            url: 'update_payment_status.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Fermer le modal
                    $('#' + modalId).modal('hide');
                    // Recharger la page pour voir les modifications
                    location.reload();
                } else {
                    alert('Erreur lors de la mise à jour: ' + response.message);
                }
            },
            error: function() {
                alert('Erreur lors de la communication avec le serveur');
            }
        });
    });
});
</script>

<script>
    // Fonction pour confirmer la suppression
    function confirmDelete(id) {
    if (confirm("Voulez-vous vraiment supprimer ce paiement ?")) {
        window.location.href = "delete_table_total_cout_par_jour.php?id=" + id;
    }
}
</script>