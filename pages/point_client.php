<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_commandes.php';
require_once '../inc/functions/requete/requetes_selection_boutique.php';
include('header.php');

$query = $conn->prepare("
    SELECT 
        boutiques.nom AS nom_boutique, 
        DATE(commandes.date_livraison) AS date_livraison, 
        SUM(commandes.cout_reel) AS total_cout_reel_par_jour
    FROM commandes
    JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id
    JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
    WHERE commandes.statut = 'Livré' AND commandes.date_livraison IS NOT NULL
    GROUP BY boutiques.nom, DATE(commandes.date_livraison)
    ORDER BY DATE(commandes.date_livraison) DESC, boutiques.nom ASC
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





<div class="table-responsive">
    <table id="example1" class="table table-bordered table-striped">
        <thead class="thead-dark">
        <tr>
            <th>Boutique</th>
            <th>Date Livraison</th>
            <th>Montant à regler</th>
            <th>Actions</th>
        </tr>
      </thead>
    <tbody>
      <?php foreach ($resultats as $resultat) : ?>
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
                <button class="btn btn-sm btn-info" title="Voir détails">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-success" title="Exporter">
                    <i class="fas fa-file-export"></i>
                </button>
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