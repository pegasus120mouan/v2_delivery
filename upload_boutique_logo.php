<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

$upload_dir = __DIR__ . "/dossiers_images/";

// Vérification ou création du dossier d'upload
if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Impossible de créer le dossier de stockage']);
    exit;
}

// Vérification de la présence du fichier
if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Aucun fichier reçu ou erreur lors du transfert']);
    exit;
}

$file = $_FILES['logo'];
$allowed_types = ['image/jpeg', 'image/png'];
$max_size = 2 * 1024 * 1024; // 2MB

// Vérification du type MIME
if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Format non autorisé. Seuls les fichiers JPEG et PNG sont acceptés.']);
    exit;
}

// Vérification de la taille du fichier
if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Le fichier est trop volumineux. Taille maximale autorisée : 2MB.']);
    exit;
}

// Génération d'un nom de fichier sécurisé
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = bin2hex(random_bytes(16)) . "." . $extension;
$filepath = $upload_dir . $filename;

// Déplacement du fichier téléchargé
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Fichier enregistré avec succès.',
        'url' => $filename
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Échec du transfert du fichier.']);
}
?>
