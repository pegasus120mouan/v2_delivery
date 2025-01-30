<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_commandes.php';
require_once '../inc/functions/requete/requetes_selection_boutique.php';
include('header.php');

$rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);

$statuts_livraisons = $getStatut->fetchAll(PDO::FETCH_ASSOC);

$liste_boutiques = $getBoutique->fetchAll(PDO::FETCH_ASSOC);



////$stmt = $conn->prepare("SELECT * FROM users");
//$stmt->execute();
//$users = $stmt->fetchAll();
//foreach($users as $user)

$limit = $_GET['limit'] ?? 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$commande_pages = array_chunk($commandes, $limit );
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


<div class="row">

    <div class="block-container">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-commande">
      <i class="fa fa-edit"></i>Enregistrer une commande
    </button>

    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#add-point">
      <i class="fa fa-print"></i> Imprimer un point
    </button>

    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#search-commande">
      <i class="fa fa-search"></i> Recherche un point
    </button>

    <button type="button" class="btn btn-dark" onclick="window.location.href='export_commandes.php'">
              <i class="fa fa-print"></i> Exporter la liste des commandes
             </button>
</div>



 <!-- <button type="button" class="btn btn-primary spacing" data-toggle="modal" data-target="#add-commande">
    Enregistrer une commande
  </button>


    <button type="button" class="btn btn-outline-secondary spacing" data-toggle="modal" data-target="#recherche-commande1">
        <i class="fas fa-print custom-icon"></i>
    </button>


  <a class="btn btn-outline-secondary" href="commandes_print.php"><i class="fa fa-print" style="font-size:24px;color:green"></i></a>


     Utilisation du formulaire Bootstrap avec ms-auto pour aligner à droite
<form action="page_recherche.php" method="GET" class="d-flex ml-auto">
    <input class="form-control me-2" type="search" name="recherche" style="width: 400px;" placeholder="Recherche..." aria-label="Search">
    <button class="btn btn-outline-primary spacing" style="margin-left: 15px;" type="submit">Rechercher</button>
</form>

-->




<div class="table-responsive">
    <table id="example1" class="table table-bordered table-striped">

 <!-- <table style="max-height: 90vh !important; overflow-y: scroll !important" id="example1" class="table table-bordered table-striped">-->
    <thead>
      <tr>
        <th>Communes</th>
        <th>Coût Global</th>
        <th>Livraison</th>
        <th>Côut réel</th>
        <th>Boutique</th>
        <th>Livreur</th>
        <th>Statut</th>
        <th>Date réception</th>
        <th>Date livraison</th>
        <th>Date Retour</th>
        <th>Actions</th>
  <!--      <th>Attribuer un livreur</th>
        <th>Changer Statut livraison</th>
        <th>Changer le client</th>-->
      </tr>
    </thead>
    <tbody>
      <?php foreach ($commandes_list as $commande) : ?>
        <tr>
          
        <td>
              <a href="#" data-toggle="modal" data-target="#details-commande-<?= $commande['commande_id'] ?>">
                  <?= $commande['commande_communes'] ?>
              </a>
          </td>

          <!-- Modal Détails Commande -->
<div class="modal fade order-details-modal" id="details-commande-<?= $commande['commande_id'] ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Détails de la commande #<?= $commande['commande_id'] ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="order-details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Commune</div>
                        <div class="detail-value"><?= $commande['commande_communes'] ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Boutique</div>
                        <div class="detail-value"><?= $commande['nom_boutique'] ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Coût Global</div>
                        <div class="detail-value"><?= number_format($commande['commande_cout_global'], 0, ',', ' ') ?> FCFA</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Coût Livraison</div>
                        <div class="detail-value"><?= number_format($commande['commande_cout_livraison'], 0, ',', ' ') ?> FCFA</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Coût Réel</div>
                        <div class="detail-value"><?= number_format($commande['commande_cout_reel'], 0, ',', ' ') ?> FCFA</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Statut</div>
                        <div class="detail-value">
                            <span class="status-badge <?= $commande['commande_statut'] == 'Livré' ? 'status-delivered' : 'status-pending' ?>">
                                <?= $commande['commande_statut'] ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="order-actions">
                    <button type="button" class="action-btn btn-modify" onclick="openModalWithoutClosing('#commande_update-<?= $commande['commande_id'] ?>')">
                        <i class="fas fa-edit"></i> Modifier
                    </button>
                    <?php if (!$commande['fullname']) : ?>
                        <button type="button" class="action-btn btn-delivery" onclick="openModalWithoutClosing('#update-<?= $commande['commande_id'] ?>')">
                            <i class="fas fa-truck"></i> Attribuer un livreur
                        </button>
                    <?php endif; ?>
                    <button type="button" class="action-btn btn-status" onclick="openModalWithoutClosing('#update_statut-<?= $commande['commande_id'] ?>')">
                        <i class="fas fa-sync-alt"></i> Changer Statut
                    </button>
                    <button type="button" class="action-btn btn-client" onclick="openModalWithoutClosing('#update_client<?= $commande['commande_id'] ?>')">
                        <i class="fas fa-user"></i> Changer Client
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
          <td><?= $commande['commande_cout_global'] ?></td>
          <td><?= $commande['commande_cout_livraison'] ?></td>
          <td><?= $commande['commande_cout_reel'] ?></td>
          <td><?= $commande['nom_boutique'] ?></td>
     


          <?php if ($commande['fullname']) : ?>
            <td><?= $commande["fullname"] ?></td>
          <?php else : ?>
            <td class="badge badge-warning badge-lg">Pas de livreur attribué</td>
          <?php endif; ?>


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

          <td><?= $commande['date_reception'] ?></td>
          <td>
            <?php if ($commande['date_livraison']): ?>
                <?= $commande['date_livraison'] ?>
            <?php else: ?>
                <button class="btn btn-secondary" disabled>Pas encore livré</button>
            <?php endif; ?>
          </td>
          <td>
    <?php if ($commande['date_retour']): ?>
        <!-- If date_retour is not NULL, show a green check icon -->
        <button class="btn btn-success" disabled>
            <i class="fas fa-check"></i>
        </button>
    <?php else: ?>
        <!-- If date_retour is NULL, show a red X icon -->
        <button class="btn btn-danger" disabled>
          <i class="fas fa-times"></i>
        </button>
    <?php endif; ?>
</td>

        <td class="actions">
          <a href="#" data-toggle="modal" data-target="#commande_update-<?= $commande['commande_id'] ?>" class="edit">
           <i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i>
           </a>
            <!-- <a href="commandes_update.php?id=<?= $commande['commande_id'] ?>" class="edit"><i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>-->
            <a href="delete_commandes.php?id=<?= $commande['commande_id'] ?>" class="trash" onclick="return confirmDelete(<?= $commande['commande_id'] ?>);"><i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
          </td>

          <div class="modal" id="commande_update-<?= $commande['commande_id'] ?>">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">Modifier commande #<?= $commande['commande_id'] ?></h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                  </div>
                  <div class="modal-body">
                    <form action="traitement_commande_update.php" method="post">
                      <input type="hidden" name="id" value="<?= $commande['commande_id'] ?>">
                      
                      <div class="form-group">
                        <label for="communes">Communes</label>
                        <input type="text" class="form-control" name="communes" value="<?= $commande['commande_communes'] ?>">
                      </div>
                      
                      <div class="form-group">
                        <label for="telephone">Côut Global</label>
                        <input type="text" class="form-control" name="cout_global" value="<?= isset($commande['commande_cout_global']) ? htmlspecialchars($commande['commande_cout_global']) : '' ?>">
                      </div>
                      
                      <div class="form-group">
                      <label>Côut Livraison</label>
                      <select id="select" name="livraison" class="form-control">
                        <?php
                          $defaultValue = $commande['commande_cout_livraison'];
                          foreach ($cout_livraison1 as $cout) {
                              $selected = ($cout['cout_livraison'] == $defaultValue) ? 'selected' : '';
                              echo '<option value="' . htmlspecialchars($cout['cout_livraison'], ENT_QUOTES) . '" ' . $selected . '>';
                              echo htmlspecialchars($cout['cout_livraison'], ENT_QUOTES);
                              echo '</option>';
                          }
                        ?>
                      </select>
                    </div>
                      
                      <div class="form-group">
                        <label for="date_reception">Date réception </label>
                        <input type="date" class="form-control" name="date_reception" value="<?= isset($commande['date_reception']) ? htmlspecialchars($commande['date_reception']) : '' ?>">
                      </div>
                      
                      <button type="submit" class="btn btn-success" name="updateCommande">Mettre à jour</button>
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    </form>
                  </div>
                </div>
              </div>
          </div>
<!--
          <td>
            <?php if ($commande['nom_livreur']) : ?>
              <button class="btn btn-secondary" disabled>Attribuer un livreur</button>
            <?php else : ?>
              <button class="btn btn-info" data-toggle="modal" data-target="#update-<?= $commande['commande_id'] ?>">Attribuer
                un livreur</button>
            <?php endif; ?>
         

          <td>
            <?php if ($commande['commande_statut'] == 'Livré') : ?>
              <button class="btn btn-info" disabled>Changer le statut</button>
            <?php else : ?>
              <button class="btn btn-warning" data-toggle="modal" data-target="#update_statut-<?= $commande['commande_id'] ?>">Changer le statut</button>
            <?php endif; ?>
          </td> 

          <td>
              <button class="btn btn-success" data-toggle="modal" data-target="#update_client<?= $commande['commande_id'] ?>">Changer le client</button>

         </td></td>-->
          
        </tr>
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
                      foreach ($statuts_livraisons as $statuts_livraison) {
                        $selected = ($statuts_livraison['statut'] === $commande['statut']) ? 'selected' : '';
                        echo '<option value="' . $statuts_livraison['statut'] . '" ' . $selected . '>' . $statuts_livraison['statut'] . '</option>';
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



       <div class="modal" id="update_date_livraison-<?= $commande['commande_id'] ?>">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-body">
                <form action="traitement_commande_date_livraison_update.php" method="post">
                  <input type="hidden" name="commande_id" value="<?= $commande['commande_id'] ?>">
                  <div class="form-group">
                <label for="exampleInputEmail1">Date de livraison</label>
                <input type="date" class="form-control" id="exampleInputEmail1" placeholder="date_livraison" name="date_livraison">
               </div>
                  <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Changer la date de livraison</button>
                  <button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button>
                </form>
              </div>
            </div>
          </div>
       </div>


         <div class="modal" id="update_client<?= $commande['commande_id'] ?>">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-body">
                <form action="traitement_commande_boutique_update.php" method="post">
                  <input type="hidden" name="commande_id" value="<?= $commande['commande_id'] ?>">
                  <div class="form-group">
                    <label>Changer le client</label>
                    <select name="id_boutique" class="form-control">
                      <?php
                      foreach ($liste_boutiques as $liste_boutique) {
                        echo '<option value="' . $liste_boutique['id'] . '">' . $liste_boutique['nom_boutique'] . '</option>';
                      }
                      ?></select>

                  </div>
                  <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Changer le client</button>
                  <button class="btn btn-light">Annuler</button>
                </form>
              </div>
            </div>
          </div>
        </div>


      <?php endforeach; ?>
    </tbody>
  </table>

</div>

  <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if($page > 1 ): ?>
        <a href="?page=<?= $page - 1 ?>" class="btn btn-primary"><</a>
    <?php endif; ?>

    <span><?= $page . '/' . count($commande_pages) ?></span>

    <?php if($page < count($commande_pages)): ?>
        <a href="?page=<?= $page + 1 ?>" class="btn btn-primary">></a>
    <?php endif; ?>

    <form action="" method="get" class="items-per-page-form">
        <label for="limit">Afficher :</label>
        <select name="limit" id="limit" class="items-per-page-select">
            <option value="5" <?php if ($limit == 5) { echo 'selected'; } ?> >5</option>
            <option value="10" <?php if ($limit == 10) { echo 'selected'; } ?>>10</option>
            <option value="15" <?php if ($limit == 15) { echo 'selected'; } ?>>15</option>
        </select>
        <button type="submit" class="submit-button">Valider</button>
    </form>
</div>



  <div class="modal fade" id="add-commande">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Enregistrer une commande</h4>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="save_commande.php">
            <div class="card-body">
              <div class="form-group">
                <label for="exampleInputEmail1">Communes</label>
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Commune destination" name="communes">
              </div>
              <div class="form-group">
                <label for="exampleInputPassword1">Côut Global</label>
                <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Coût global Colis" name="cout_global">
              </div>
              <div class="form-group">
                <label>Côut Livraison</label>
                <?php
                echo  '<select id="select" name="livraison" class="form-control">';
                while ($coutLivraison = $cout_livraison->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . $coutLivraison['cout_livraison'] . '">' . $coutLivraison['cout_livraison'] . '</option>';
                }
                echo '</select>'
                ?>
              </div>
              <div class="form-group">
                <label>Clients</label>
                <?php
                echo  '<select id="select" name="client_id" class="form-control">';
                while ($row = $getClientsStmt->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . $row['id'] . '">' . $row['nom_boutique'] . '</option>';
                }
                echo '</select>'
                ?>
              </div>
              <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Enregister</button>
              <button class="btn btn-light">Annuler</button>
            </div>
          </form>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>

<!-- Recherche par Communes -->
<div class="modal fade" id="search-commande-communes">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Recherche par Communes</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form class="forms-sample" method="GET" action="page_recherche.php">
          <div class="card-body">
            <div class="form-group">
              <label for="communeInput">Entrez la commune</label>
              <input type="text" class="form-control" id="communeInput" placeholder="Recherche une commune" name="recherche">
            </div>
            <button type="submit" class="btn btn-primary mr-2">Recherche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Recherche par Date -->
<div class="modal fade" id="search-commande-date-reception">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Recherche par Date de reception</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form class="forms-sample" method="GET" action="page_recherche_date_reception.php">
          <div class="card-body">
            <div class="form-group">
              <label for="dateInput">Sélectionner la date</label>
              <input type="date" class="form-control" id="dateInput" name="date">
            </div>
            <button type="submit" class="btn btn-primary mr-2">Recherche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="search-commande-date-livraison">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Recherche par date de livraison</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form class="forms-sample" method="GET" action="page_recherche_date_livraison.php">
          <div class="card-body">
            <div class="form-group">
              <label for="dateInput">Sélectionner la date</label>
              <input type="date" class="form-control" id="dateInput" name="date">
            </div>
            <button type="submit" class="btn btn-primary mr-2">Recherche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="search-commande-date-retour">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Recherche par date rétour</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form class="forms-sample" method="GET" action="page_recherche_date_retour.php">
          <div class="card-body">
            <div class="form-group">
              <label for="dateInput">Sélectionner la date</label>
              <input type="date" class="form-control" id="dateInput" name="date">
            </div>
            <button type="submit" class="btn btn-primary mr-2">Recherche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Recherche par Livreur -->
<div class="modal fade" id="search-commande-livreur">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Recherche par Livreur</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form class="forms-sample" method="GET" action="page_recherche_livreur.php">
          <div class="card-body">
              <div class="form-group">
                  <label>Selectionner le livreur</label>
                  <select name="livreur_id" class="form-control">
                    <?php
                      foreach ($rows as $row) {
                        echo '<option value="' . $row['id'] . '">' . $row['livreur_name'] . '</option>';
                      }
                      ?></select>

                </div>
            <button type="submit" class="btn btn-primary mr-2">Recherche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Recherche par Client -->
<div class="modal fade" id="search-commande-client">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Recherche par Client</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form class="forms-sample" method="GET" action="page_recherche_clients.php">
          <div class="card-body">
                   <div class="form-group">
                    <label>Selectionner le client</label>
                    <select name="id_boutique" class="form-control">
                      <?php
                      foreach ($liste_boutiques as $liste_boutique) {
                        echo '<option value="' . $liste_boutique['id'] . '">' . $liste_boutique['nom_boutique'] . '</option>';
                      }
                      ?></select>

                  </div>
            <button type="submit" class="btn btn-primary mr-2">Recherche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Recherche par Statut -->
<div class="modal fade" id="search-commande-statut">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Recherche par Statut</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form class="forms-sample" method="GET" action="page_recherche_statut.php">
          <div class="card-body">
            <div class="form-group">
                    <label>Selectionner le statut</label>
                    <select name="statut" class="form-control">
                    <option value="Livré">Livré</option>
                    <option value="Non Livré">Non Livré</option>
                    <option value="Retourné">Retourné</option>
                      
                      </select>

                  </div>
            <button type="submit" class="btn btn-primary mr-2">Recherche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="search-commande-remis">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Recherche commis par client</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form class="forms-sample" method="POST" action="page_recherche_commandes.php">
          <div class="card-body">
            <div class="form-group">
              <label>Selectionner le client</label>
              <select name="client" class="form-control">
                <?php
                foreach ($liste_boutiques as $liste_boutique) {
                  echo '<option value="' . $liste_boutique['nom_boutique'] . '">' . $liste_boutique['nom_boutique'] . '</option>';
                }
                ?>
              </select>
            </div>
            <div class="form-group">
              <label for="dateInput">Sélectionner la date</label>
              <input type="date" class="form-control" id="dateInput" name="date">
            </div>
            <button type="submit" class="btn btn-primary mr-2">Recherche</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>



<!-- Main Search Modal -->
<div class="modal fade" id="search-commande">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Recherche un point</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
      <button type="button" class="btn btn-outline-dark w-100 mb-2" onclick="showSearchModal('search-commande-communes')">
          <i class="fa fa-map-marker"></i>Recherche par Communes
      </button>  

       <button type="button" class="btn btn-outline-dark w-100 mb-2" onclick="showSearchModal('search-commande-date-reception')">
       <i class="fa fa-calendar"></i>Recherche par de réception
       </button>

        <button type="button" class="btn btn-outline-dark w-100 mb-2" onclick="showSearchModal('search-commande-date-livraison')">
       <i class="fa fa-calendar"></i>Recherche par date de livraison
       </button>

       <button type="button" class="btn btn-outline-dark w-100 mb-2" onclick="showSearchModal('search-commande-date-retour')">
       <i class="fa fa-calendar"></i>Recherche par date retour
       </button>

       

        <button type="button" class="btn btn-outline-dark w-100 mb-2" onclick="showSearchModal('search-commande-livreur')">
        <i class="fa fa-motorcycle"></i>Recherche par Livreur
        </button>

        <button type="button" class="btn btn-outline-dark w-100 mb-2" onclick="showSearchModal('search-commande-client')">
        <i class="fa fa-home"></i>Recherche par Client
        </button>

        <button type="button" class="btn btn-outline-dark w-100 mb-2" onclick="showSearchModal('search-commande-statut')">
        <i class="fa fa-check-circle"></i>Recherche par Statut
        </button>

        <button type="button" class="btn btn-outline-dark w-100 mb-2" onclick="showSearchModal('search-commande-remis')">
        <i class="fa fa-check-circle"></i>Recherche par colis remis
        </button>
      </div>
    </div>
  </div>
</div>



    <div class="modal fade" id="add-point">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <form class="forms-sample" method="post" action="traitement_commandes_print.php">
                        <div class="card-body">
                            <div class="form-group">
                                <label for="exampleInputEmail1">Client</label>
                                   <div class="form-group">
                                    <select name="client" class="form-control">
                                      <?php
                                      while ($selection = $stmt_select_boutique->fetch()) {
                                        echo '<option value="' . $selection['nom_boutique'] . '">' . $selection['nom_boutique'] . '</option>';
                                      }
                                      ?></select>
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Sélectionner la date</label>
                                  <input id="date" name="date" type="date" class="form-control">
                                  </div>
                            <input type="submit" class="btn btn-primary mr-2" value="Imprimer">
                        </div>
                    </form>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

  


<!-- /.row (main row) -->
</div><!-- /.container-fluid -->
<!-- /.content -->
</div>
<!-- /.content-wrapper -->
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

<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Cette action est irréversible !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler',
        background: '#fff',
        borderRadius: '15px',
        customClass: {
            popup: 'animated fadeInDown'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'delete_commandes.php?id=' + id;
        }
    });
    return false;
}
</script>

<script>
// Fonction pour ouvrir un modal sans fermer le modal de détails
function openModalWithoutClosing(modalId) {
    $(modalId).modal('show');
}
</script>

<?php

if (isset($_SESSION['popup']) && $_SESSION['popup'] ==  true) {
  ?>
    <script>
      var audio = new Audio("../inc/sons/notification.mp3");
      audio.volume = 1.0; // Assurez-vous que le volume n'est pas à zéro
      audio.play().then(() => {
        // Lecture réussie
        var Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
  
        Toast.fire({
          icon: 'success',
          title: 'Action effectuée avec succès.'
        });
      }).catch((error) => {
        console.error('Erreur de lecture audio :', error);
      });
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
<script>
function showSearchModal(modalId) {
  // Hide all modals
  document.querySelectorAll('.modal').forEach(modal => {
    $(modal).modal('hide');
  });

  // Show the selected modal
  $('#' + modalId).modal('show');
}
</script>

</body>

</html>