<?php
//require_once '../inc/functions/connexion.php';

include('header.php');
require_once '../inc/functions/requete/requete_commandes.php';
//require_once '../inc/functions/requete/clients/requete_commandes_clients.php';

$statut = $_GET['statut'] ?? '';
$limit = $_GET['limit'] ?? 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);
$statuts = $getStatut->fetchAll(PDO::FETCH_ASSOC);

//$rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);
//$livreurs = $getStatut->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare(
    "SELECT 
    commandes.id AS commande_id, 
    commandes.communes AS commande_communes, 
    commandes.cout_global AS commande_cout_global, 
    commandes.cout_livraison AS commande_cout_livraison, 
    commandes.cout_reel AS commande_cout_reel, 
    commandes.statut AS commande_statut, 
    commandes.date_reception, commandes.date_livraison, commandes.date_retour,
    utilisateurs.nom AS nom_utilisateur, 
    utilisateurs.prenoms AS prenoms_utilisateur,
    boutiques.nom AS nom_boutique, 
    livreur.nom AS nom_livreur, 
    livreur.prenoms AS prenoms_livreur, 
    CONCAT(livreur.nom, ' ', livreur.prenoms) AS fullname,
    utilisateurs.role
FROM commandes
JOIN utilisateurs ON commandes.utilisateur_id = utilisateurs.id
JOIN boutiques ON utilisateurs.boutique_id = boutiques.id
LEFT JOIN utilisateurs AS livreur ON commandes.livreur_id = livreur.id 
WHERE commandes.statut = :statut
ORDER BY commandes.date_reception DESC"
);


$stmt->bindParam(':statut', $statut, PDO::PARAM_STR);
$stmt->execute();
$commandes = $stmt->fetchAll();

// Calculate total pages
$total_records = count($commandes);
$total_pages = ceil($total_records / $limit);

// Ensure current page is within valid range
$page = max(1, min($page, $total_pages));

$commande_pages = array_chunk($commandes, $limit);
$commandes_list = $commande_pages[$page - 1] ?? [];
?> 

<style>
  /* Your CSS styles */
</style>

<!-- Main row -->
<div class="row">
  <table id="example1" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Communes</th>
        <th>Coût Global</th>
        <th>Livraison</th>
        <th>Côut réel</th>
        <th>Boutique</th>
        <th>Livreur</th>
        <th>Statut</th>
        <th>Date de la reception</th>
        <th>Date de la livraison</th>
        <th>Date du retour</th>
        <th>Actions</th>
        <th>Attribuer un livreur</th>
        <th>Changer Statut livraison</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($commandes_list as $commande) : ?>
      <tr>
        <td><?= htmlspecialchars($commande['commande_communes']) ?></td>
        <td><?= htmlspecialchars($commande['commande_cout_global']) ?></td>
        <td><?= htmlspecialchars($commande['commande_cout_livraison']) ?></td>
        <td><?= htmlspecialchars($commande['commande_cout_reel']) ?></td>
        <td><?= htmlspecialchars($commande['nom_boutique']) ?></td>
        <td><?= htmlspecialchars($commande['fullname']) ?></td>
       <td>
            <?php if ($commande['commande_statut'] !== null) : ?>
              <?php if ($commande['commande_statut'] == 'Non Livré') : ?>
                <!-- Afficher une croix rouge avec tooltip -->
                <span class="badge badge-danger badge-lg" title="Non Livré">
                  <i class="fas fa-times"></i>
                </span>
              <?php else : ?>
                <!-- Afficher une coche verte avec tooltip -->
                <span class="badge badge-success badge-lg" title="Livré">
                  <i class="fas fa-check"></i>
                </span>
              <?php endif; ?>
            <?php else : ?>
              <!-- Pas de statut disponible -->
              <span class="badge badge-secondary badge-lg" title="Pas de point">
                <i class="fas fa-minus"></i>
              </span>
            <?php endif; ?>
       </td>

        <td><?= htmlspecialchars($commande['date_reception']) ?></td>
         <td><?= htmlspecialchars($commande['date_livraison']) ?></td>
          <td><?= htmlspecialchars($commande['date_retour']) ?></td>
        <td class="actions">
          <a href="commandes_update_recherche.php?id=<?= $commande['commande_id'] ?>&recherche=<?= urlencode($recherche) ?>&page=<?= $page ?>&limit=<?= $limit ?>" class="edit"><i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
          <a href="delete_commandes.php?id=<?= $commande['commande_id'] ?>&recherche=<?= urlencode($recherche) ?>&page=<?= $page ?>&limit=<?= $limit ?>" class="trash"><i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
        </td>
      <td>
          <?php if ($commande['nom_livreur']) : ?>
            <!-- Si un livreur est attribué, bouton blanc avec icône et tooltip -->
            <button class="btn btn-light" disabled title="<?= htmlspecialchars($commande['nom_livreur'] . ' ' . $commande['prenoms_livreur']) ?>">
              <i class="fas fa-user-check text-success"></i>
            </button>
          <?php else : ?>
            <!-- Si aucun livreur n'est attribué, bouton bleu avec icône -->
            <button class="btn btn-info" data-toggle="modal" data-target="#update-<?= $commande['commande_id'] ?>">
              <i class="fas fa-user-times text-danger"></i>
            </button>
          <?php endif; ?>
      </td>


        <td>
  <?php if ($commande['commande_statut'] == 'Livré') : ?>
    <!-- Bouton désactivé avec icône pour statut "Livré" -->
    <button class="btn btn-info" disabled title="Statut actuel : Livré">
      <i class="fas fa-lock"></i> 
    </button>
  <?php else : ?>
    <!-- Bouton actif pour changer le statut -->
    <button class="btn btn-warning" data-toggle="modal" data-target="#update_statut-<?= $commande['commande_id'] ?>" title="Changer le statut">
      <i class="fas fa-sync"></i>
    </button>
  <?php endif; ?>
</td>
      </tr>
      <div class="modal" id="update-<?= $commande['commande_id'] ?>">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-body">
              <form action="traitement_commande_livreurs_update.php" method="post">
                <input type="hidden" name="commande_id" value="<?= $commande['commande_id'] ?>">
                <input type="hidden" name="redirect_page" value="page_recherche_date_reception.php">
                <input type="hidden" name="date" value="<?= htmlspecialchars($recherche) ?>">
                <input type="hidden" name="page" value="<?= $page ?>">
                <input type="hidden" name="limit" value="<?= $limit ?>">
                <div class="form-group">
                  <label>Livreur</label>
                  <select name="livreur_id" class="form-control">
                    <?php
                      foreach ($rows as $row) {
                        echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['livreur_name']) . '</option>';
                      }
                    ?>
                  </select>
                </div>
                <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Attribuer</button>
                <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
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
                <input type="hidden" name="redirect_page" value="page_recherche_date_reception.php">
                <input type="hidden" name="date" value="<?= htmlspecialchars($recherche) ?>">
                <input type="hidden" name="page" value="<?= $page ?>">
                <input type="hidden" name="limit" value="<?= $limit ?>">
                <div class="form-group">
                  <label>Changer le statut de la commande</label>
                  <select name="statut" class="form-control">
                    <?php
                      foreach ($statuts as $statut) {
                        echo '<option value="' . htmlspecialchars($statut['statut']) . '">' . htmlspecialchars($statut['statut']) . '</option>';
                      }
                    ?>
                  </select>
                </div>
                <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Changer le statut</button>
                <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </tbody>
  </table>
  
  <!-- Pagination -->
  <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if ($page > 1): ?>
        <a href="page_recherche_date_reception.php?date=<?= urlencode($recherche) ?>&page=1&limit=<?= $limit ?>" class="btn btn-primary">&lt;&lt;</a>
        <a href="page_recherche_date_reception.php?date=<?= urlencode($recherche) ?>&page=<?= $page - 1 ?>&limit=<?= $limit ?>" class="btn btn-primary">&lt;</a>
    <?php endif; ?>

    <div class="page-info">
        Page <?= $page ?> sur <?= $total_pages ?> (<?= $total_records ?> résultats)
    </div>

    <?php if ($page < $total_pages): ?>
        <a href="page_recherche_date_reception.php?date=<?= urlencode($recherche) ?>&page=<?= $page + 1 ?>&limit=<?= $limit ?>" class="btn btn-primary">&gt;</a>
        <a href="page_recherche_date_reception.php?date=<?= urlencode($recherche) ?>&page=<?= $total_pages ?>&limit=<?= $limit ?>" class="btn btn-primary">&gt;&gt;</a>
    <?php endif; ?>
  </div>

</div>

<!-- /.row (main row) -->
</div><!-- /.container-fluid -->
<!-- /.content -->
</div>

<!-- Footer -->
<!-- <footer class="main-footer">
    <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.2.0
    </div>
</footer>-->
<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
  <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<!-- <script>
  $.widget.bridge('uibutton', $.ui.button)
</script>-->
<!-- Bootstrap 4 -->
<script src="../plugins/sweetalert2/sweetalert2.min.js"></script>

<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="../plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="../plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="../plugins/moment/moment.min.js"></script>
<script src="../plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="../plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.js"></script>

<?php
if (isset($_SESSION['popup']) && $_SESSION['popup'] == true) {
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

<!-- Delete Pop -->
<?php
if (isset($_SESSION['delete_pop']) && $_SESSION['delete_pop'] == true) {
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
