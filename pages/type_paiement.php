<?php
require_once '../inc/functions/connexion.php';
include('header.php');

$message = '';
$redirect = false;

// Handle Add/Edit/Delete operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $operateur = $_POST['operateur'];
            
            // Gérer l'upload du logo
            $logo_path = '';
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['logo']['tmp_name'];
                $file_name = $_FILES['logo']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Vérifier le type de fichier
                $allowed = array('jpg', 'jpeg', 'png', 'gif');
                if (in_array($file_ext, $allowed)) {
                    // Générer un nom unique pour le fichier
                    $new_file_name = uniqid('logo_', true) . '.' . $file_ext;
                    $upload_path = '../dossiers_paiement/' . $new_file_name;
                    $db_path = $new_file_name; // Stocker uniquement le nom du fichier
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        $logo_path = $db_path;
                    }
                }
            }
            
            $stmt = $conn->prepare("INSERT INTO type_paiement (operateur, logo) VALUES (?, ?)");
            if ($stmt->execute([$operateur, $logo_path])) {
                $message = 'added';
                $redirect = true;
            }
        } elseif ($_POST['action'] === 'edit') {
            $id = $_POST['id'];
            $operateur = $_POST['operateur'];
            
            // Récupérer l'ancien logo
            $stmt = $conn->prepare("SELECT logo FROM type_paiement WHERE id = ?");
            $stmt->execute([$id]);
            $old_logo = $stmt->fetchColumn();
            
            $logo_path = $old_logo;
            
            // Gérer l'upload du nouveau logo
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['logo']['tmp_name'];
                $file_name = $_FILES['logo']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Vérifier le type de fichier
                $allowed = array('jpg', 'jpeg', 'png', 'gif');
                if (in_array($file_ext, $allowed)) {
                    // Générer un nom unique pour le fichier
                    $new_file_name = uniqid('logo_', true) . '.' . $file_ext;
                    $upload_path = '../dossiers_paiement/' . $new_file_name;
                    $db_path = $new_file_name; // Stocker uniquement le nom du fichier
                    
                    if (move_uploaded_file($file_tmp, $upload_path)) {
                        // Supprimer l'ancien logo si il existe
                        if (!empty($old_logo)) {
                            $old_file_path = '../dossiers_paiement/' . $old_logo;
                            if (file_exists($old_file_path)) {
                                unlink($old_file_path);
                            }
                        }
                        $logo_path = $db_path;
                    }
                }
            }
            
            $stmt = $conn->prepare("UPDATE type_paiement SET operateur = ?, logo = ? WHERE id = ?");
            if ($stmt->execute([$operateur, $logo_path, $id])) {
                $message = 'updated';
                $redirect = true;
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'];
            
            // Supprimer le logo associé
            $stmt = $conn->prepare("SELECT logo FROM type_paiement WHERE id = ?");
            $stmt->execute([$id]);
            $logo = $stmt->fetchColumn();
            
            if (!empty($logo)) {
                $file_path = '../dossiers_paiement/' . $logo;
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            $stmt = $conn->prepare("DELETE FROM type_paiement WHERE id = ?");
            if ($stmt->execute([$id])) {
                $message = 'deleted';
                $redirect = true;
            }
        }
    }
}

// Fetch all payment types
$query = $conn->query("SELECT * FROM type_paiement ORDER BY id DESC");
$types_paiement = $query->fetchAll(PDO::FETCH_ASSOC);

// Si une redirection est nécessaire, ajouter le script JavaScript
if ($redirect) {
    echo "<script>window.location.href = 'type_paiement.php?success=" . $message . "';</script>";
    exit;
}
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Types de Paiement</h1>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addModal">
                            <i class="fas fa-plus"></i> Ajouter un type de paiement
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php
                                    switch($_GET['success']) {
                                        case 'added':
                                            echo "Type de paiement ajouté avec succès!";
                                            break;
                                        case 'updated':
                                            echo "Type de paiement mis à jour avec succès!";
                                            break;
                                        case 'deleted':
                                            echo "Type de paiement supprimé avec succès!";
                                            break;
                                    }
                                ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <table class="table table-bordered table-striped" id="typePaiementTable">
                            <thead>
                                <tr>
                                    <th>Opérateur</th>
                                    <th>Logo</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($types_paiement as $type): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($type['operateur']) ?></td>
                                        <td>
                                            <?php if ($type['logo']): ?>
                                                <img src="../dossiers_paiement/<?= htmlspecialchars($type['logo']) ?>" alt="Logo" style="max-height: 50px;">
                                            <?php else: ?>
                                                <span class="text-muted">Aucun logo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info edit-btn" 
                                                    onclick="editType(<?= $type['id'] ?>, '<?= htmlspecialchars(addslashes($type['operateur'])) ?>', '<?= htmlspecialchars(addslashes($type['logo'])) ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-btn" 
                                                    onclick="deleteType(<?= $type['id'] ?>, '<?= htmlspecialchars(addslashes($type['operateur'])) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModalLabel">Ajouter un type de paiement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label for="operateur">Opérateur</label>
                        <input type="text" class="form-control" id="operateur" name="operateur" required>
                    </div>
                    <div class="form-group">
                        <label for="logo">Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        <small class="form-text text-muted">Formats acceptés: JPG, JPEG, PNG, GIF</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Modifier le type de paiement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="form-group">
                        <label for="edit_operateur">Opérateur</label>
                        <input type="text" class="form-control" id="edit_operateur" name="operateur" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_logo">Logo</label>
                        <input type="file" class="form-control" id="edit_logo" name="logo" accept="image/*">
                        <small class="form-text text-muted">Formats acceptés: JPG, JPEG, PNG, GIF</small>
                        <div id="current_logo" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmer la suppression</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete_id">
                    <p>Êtes-vous sûr de vouloir supprimer le type de paiement <strong id="delete_operateur"></strong> ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for handling modals -->
<script>
function editType(id, operateur, logo) {
    $('#edit_id').val(id);
    $('#edit_operateur').val(operateur);
    
    // Afficher l'image actuelle
    var currentLogoDiv = $('#current_logo');
    if (logo) {
        currentLogoDiv.html('<p>Logo actuel:</p><img src="../dossiers_paiement/' + logo + '" alt="Logo actuel" style="max-height: 100px;">');
    } else {
        currentLogoDiv.html('<p>Pas de logo actuellement</p>');
    }
    
    $('#editModal').modal('show');
}

function deleteType(id, operateur) {
    $('#delete_id').val(id);
    $('#delete_operateur').text(operateur);
    $('#deleteModal').modal('show');
}

// Initialisation de la table avec DataTables
$(document).ready(function() {
    $('#typePaiementTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        }
    });
});
</script>

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