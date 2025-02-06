<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$upload_dir = __DIR__ . "/dossiers_images/";

if (!is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
    die(json_encode(['status' => 'error', 'message' => 'Impossible de créer le dossier de stockage']));
}

if (!isset($_FILES['avatar'])) {
    die(json_encode(['status' => 'error', 'message' => 'Aucun fichier reçu']));
}

$file = $_FILES['avatar'];
$allowed_types = ['image/jpeg', 'image/png'];

if (!in_array($file['type'], $allowed_types)) {
    die(json_encode(['status' => 'error', 'message' => 'Format non autorisé']));
}

$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = hash('sha256', uniqid()) . "." . $extension;
$filepath = $upload_dir . $filename;

if (move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Fichier enregistré',
        'url' => $filename
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Échec du transfert']);
}
?>
