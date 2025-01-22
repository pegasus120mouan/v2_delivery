<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_commandes.php';
include('header.php');

// Debug
error_log('Debug $liste_commandes_non_livrees: ' . print_r($liste_commandes_non_livrees, true));
?>

<style>
.table-container {
    width: 100%;
    overflow-x: auto;
}

.page-title {
    text-align: center;
    background-color: #dc3545;
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

<div class="row">
  <div class="col-12">
    <h1 class="page-title">Liste des colis non livrés</h1>
  </div>
  <div class="table-container">
    <table class="custom-table">
      <thead>
        <tr>
          <th>Communes</th>
          <th>Coût Global</th>
          <th>Livraison</th>
          <th>Coût réel</th>
          <th>Client</th>
          <th>Livreur</th>
          <th>Statut</th>
          <th>Date réception</th>
          <th>Date Retour</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!empty($liste_commandes_non_livrees) && is_array($liste_commandes_non_livrees)) : ?>
        <?php foreach ($liste_commandes_non_livrees as $commande) : ?>
          <tr>
            <td><?= htmlspecialchars($commande['commande_communes']) ?></td>
            <td><?= htmlspecialchars($commande['commande_cout_global']) ?></td>
            <td><?= htmlspecialchars($commande['commande_cout_livraison']) ?></td>
            <td><?= htmlspecialchars($commande['commande_cout_reel']) ?></td>
            <td><?= htmlspecialchars($commande['nom_boutique']) ?></td>
            <td>
            <?php if (!empty($commande['nom_livreur']) && !empty($commande['prenoms_livreur'])) : ?>
              <?= htmlspecialchars($commande['nom_livreur'] . ' ' . $commande['prenoms_livreur']) ?>
            <?php else : ?>
              <span class="badge badge-warning">Pas de livreur attribué</span>
            <?php endif; ?>
            </td>
            <td>
              <?php if ($commande['commande_statut'] !== null) : ?>
                <?php if ($commande['commande_statut'] == 'Non Livré') : ?>
                  <span class="badge badge-danger"><?= htmlspecialchars($commande['commande_statut']) ?></span>
                <?php else : ?>
                  <span class="badge badge-success"><?= htmlspecialchars($commande['commande_statut']) ?></span>
                <?php endif; ?>
              <?php else : ?>
                <span class="badge badge-warning">Statut inconnu</span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($commande['date_reception']) ?></td>
            <td>
  <?php if ($commande['date_retour'] === null) : ?>
    <button class="btn btn-secondary" disabled>Colis pas encore retourné</button>
  <?php else : ?>
    <?= htmlspecialchars($commande['date_retour']) ?>
  <?php endif; ?>
</td>
<td>
  <?php if ($commande['date_retour'] === null) : ?>
    <button class="btn btn-warning" data-toggle="modal" data-target="#recuperer-<?= $commande['commande_id'] ?>">
      <i class="fas fa-box"></i> Retourné le colis
    </button>
  <?php else : ?>
    <button class="btn btn-dark" disabled>
      <i class="fas fa-box"></i> Retourné le colis
    </button>
  <?php endif; ?>
</td>
          </tr>
          <!-- Modal pour la récupération -->
          <div class="modal fade" id="recuperer-<?= $commande['commande_id'] ?>">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Récupération du colis</h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                  <form action="traitement_recuperation_colis.php" method="post">
                    <input type="hidden" name="commande_id" value="<?= $commande['commande_id'] ?>">
                    <div class="form-group">
                      <label>Date de rétour</label>
                      <input type="date" name="date_retour" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="recuperer_colis">Confirmer la récupération</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <div class="modal" id="update-<?= $commande['commande_id'] ?>">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-body">
                  <form action="traitement_commande_livreurs_update.php" method="post">
                    <input type="hidden" name="commande_id" value="<?= $commande['commande_id'] ?>">
                    <div class="form-group">
                      <label>Livreur</label>
                      <select name="livreur_id" class="form-control">
                        <?php
                          foreach ($rows as $row) {
                            echo '<option value="' . $row['id'] . '">' . $row['livreur_name'] . '</option>';
                          }
                          ?></select>

                    </div>
                    <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Attribuer</button>
                    <button class="btn btn-light">Annuler</button>
                  </form>
                </div>
              </div>
            </div>
          </div>

          <div class="modal" id="update_statut-<?= $commande['commande_id'] ?>">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-body">
                  <form action="traitement_commande_statut_update.php" method="post">
                    <input type="hidden" name="commande_id" value="<?= $commande['commande_id'] ?>">
                    <div class="form-group">
                      <label>Changer le statut de la commande</label>
                      <select name="statut" class="form-control">
                        <?php
                          foreach ($livreurs as $livreur) {
                            echo '<option value="' . $livreur['statut'] . '">' . $livreur['statut'] . '</option>';
                          }
                          ?></select>

                    </div>
                    <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Changer le statut</button>
                    <button class="btn btn-light">Annuler</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else : ?>
        <tr>
          <td colspan="10" class="text-center">Aucune commande non livrée trouvée</td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages_non_livrees > 1) : ?>
    <div class="pagination-container">
      <div class="pagination-wrapper">
        <div class="pagination">
          <?php if ($page_courante_non_livrees > 1) : ?>
            <a href="?page=<?= $page_courante_non_livrees - 1 ?>">‹ Previous</a>
          <?php endif; ?>

          <?php
          $start_page = max(1, min($page_courante_non_livrees - 2, $total_pages_non_livrees - 4));
          $end_page = min($total_pages_non_livrees, max(5, $page_courante_non_livrees + 2));
          
          for ($i = $start_page; $i <= $end_page; $i++) : 
          ?>
            <a href="?page=<?= $i ?>" class="<?= $i === $page_courante_non_livrees ? 'active' : '' ?>"><?= $i ?></a>
          <?php endfor; ?>

          <?php if ($end_page < $total_pages_non_livrees) : ?>
            <span>...</span>
            <a href="?page=<?= $total_pages_non_livrees ?>"><?= $total_pages_non_livrees ?></a>
          <?php endif; ?>

          <?php if ($page_courante_non_livrees < $total_pages_non_livrees) : ?>
            <a href="?page=<?= $page_courante_non_livrees + 1 ?>">Next ›</a>
          <?php endif; ?>
        </div>

        <div class="go-to-section">
          <span>Go to</span>
          <form action="" method="GET" class="d-flex align-items-center">
            <input type="number" name="page" min="1" max="<?= $total_pages_non_livrees ?>">
            <button type="submit">Go</button>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>
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
<?php

if (isset($_SESSION['popup']) && $_SESSION['popup'] ==  true) {
?>
<script>
var Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000
});

Toast.fire({
  icon: 'success',
  title: 'Action effectuée avec succès.'
})
</script>

<?php
  $_SESSION['popup'] = false;
}
?>

<!------- Delete Pop--->
<?php

if (isset($_SESSION['delete_pop']) && $_SESSION['delete_pop'] ==  true) {
?>
<script>
var Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000
});

Toast.fire({
  icon: 'error',
  title: 'Action échouée.'
})
</script>

<?php
  $_SESSION['delete_pop'] = false;
}
?>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!--<script src="dist/js/pages/dashboard.js"></script>-->
</body>

</html>