<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_commandes.php';
include('header.php');

// Debug
//var_dump($commandes_livrees);

//$rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);

//$livreurs = $getStatut->fetchAll(PDO::FETCH_ASSOC);

////$stmt = $conn->prepare("SELECT * FROM users");
//$stmt->execute();
//$users = $stmt->fetchAll();
//foreach($users as $user)

?>
<style>
.table-container {
    width: 100%;
    overflow-x: auto;
}

.page-title {
    text-align: center;
    background-color: #28a745;
    color: white;
    padding: 10px;
    margin-bottom: 20px;
}

.custom-table {
    width: 100%;
    border-collapse: collapse;
}

.custom-table th {
    background-color: white;
    color: black;
    font-weight: bold;
    padding: 10px;
    text-align: left;
    border: 1px solid #dee2e6;
}

.custom-table tr:nth-child(even) {
    background-color: #f8f9fa;
}

.custom-table td {
    padding: 10px;
    border: 1px solid #dee2e6;
}

.custom-table tr:hover {
    background-color: #f5f5f5;
}

/* Pagination styles */
.pagination-container {
    width: 100%;
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

.pagination-wrapper {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.pagination {
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 0;
}

.pagination a, .pagination span {
    padding: 5px 10px;
    border: 1px solid #dee2e6;
    text-decoration: none;
    color: #333;
}

.pagination a:hover {
    background-color: #f8f9fa;
}

.pagination .active {
    background-color: #6c757d;
    color: white;
    border-color: #6c757d;
}

.go-to-section {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.go-to-section input {
    width: 60px;
    padding: 4px;
    border: 1px solid #dee2e6;
}

.go-to-section button {
    background-color: #ffc107;
    border: none;
    padding: 5px 15px;
    cursor: pointer;
}
</style>

<!-- Main row -->
<div class="row">
  <div class="col-12">
    <h1 class="page-title">Liste des colis livrés</h1>
  </div>
  <div class="table-container">
    <table class="custom-table">
      <thead>
        <tr>
          <th>Communes</th>
          <th>Coût Global</th>
          <th>Livraison</th>
          <th>Côut réel</th>
          <th>Client</th>
          <th>Livreur</th>
          <th>Statut</th>
          <th>Date réception</th>
          <th>Date Livraison</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!empty($liste_commandes_livrees) && is_array($liste_commandes_livrees)) : ?>
        <?php foreach ($liste_commandes_livrees as $commande) : ?>
          <tr>
            <td><?= htmlspecialchars($commande['commande_communes']) ?></td>
            <td><?= htmlspecialchars($commande['commande_cout_global']) ?></td>
            <td><?= htmlspecialchars($commande['commande_cout_livraison']) ?></td>
            <td><?= htmlspecialchars($commande['commande_cout_reel']) ?></td>
            <td><?= htmlspecialchars($commande['nom_boutique']) ?></td>
            <td>
            <?php if (!empty($commande['fullname'])) : ?>
              <?= htmlspecialchars($commande['fullname']) ?>
            <?php else : ?>
              <span class="badge badge-warning">Pas de livreur attribué</span>
            <?php endif; ?>
            </td>
            <td>
              <?php if ($commande['commande_statut'] == 'Livré') : ?>
              <i class="fas fa-check-circle text-success" style="font-size: 20px;"></i>
              <?php else : ?>
              <?= htmlspecialchars($commande['commande_statut']) ?>
               <?php endif; ?>
            </td>
            
            <td><?= htmlspecialchars($commande['date_reception']) ?></td>
            <td><?= htmlspecialchars($commande['date_livraison']) ?></td>
           
          </tr>
        <?php endforeach; ?>
      <?php else : ?>
        <tr>
          <td colspan="10" class="text-center">Aucune commande livrée trouvée</td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages > 1) : ?>
    <div class="pagination-container">
      <div class="pagination-wrapper">
        <div class="pagination">
          <?php if ($page_courante > 1) : ?>
            <a href="?page=<?= $page_courante - 1 ?>">‹ Previous</a>
          <?php endif; ?>

          <?php
          $start_page = max(1, min($page_courante - 2, $total_pages - 4));
          $end_page = min($total_pages, max(5, $page_courante + 2));
          
          for ($i = $start_page; $i <= $end_page; $i++) : 
          ?>
            <a href="?page=<?= $i ?>" class="<?= $i === $page_courante ? 'active' : '' ?>"><?= $i ?></a>
          <?php endfor; ?>

          <?php if ($end_page < $total_pages) : ?>
            <span>...</span>
            <a href="?page=<?= $total_pages ?>"><?= $total_pages ?></a>
          <?php endif; ?>

          <?php if ($page_courante < $total_pages) : ?>
            <a href="?page=<?= $page_courante + 1 ?>">Next ›</a>
          <?php endif; ?>
        </div>

        <div class="go-to-section">
          <span>Go to</span>
          <form action="" method="GET" class="d-flex align-items-center">
            <input type="number" name="page" min="1" max="<?= $total_pages ?>">
            <button type="submit">Go</button>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
</div>
</div>
<aside class="control-sidebar control-sidebar-dark">
</aside>
</div>


<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<!-- <script>
  $.widget.bridge('uibutton', $.ui.button)
</script>-->
<!-- Bootstrap 4 -->
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>

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
<!------- Delete Pop--->
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!--<script src="dist/js/pages/dashboard.js"></script>-->
</body>

</html>