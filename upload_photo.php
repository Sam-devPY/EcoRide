<?php
session_start();
require 'db.php'; // Inclusion de la connexion à la base de données

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté.']);
    exit;
}

// Vérification si un fichier a été envoyé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $userId = $_SESSION['user_id'];
    $targetDir = "uploads/";
    $fileName = uniqid() . "_" . basename($_FILES['photo']['name']);
    $targetFilePath = $targetDir . $fileName;

    // Vérifiez si le fichier est une image valide
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array(strtolower($fileType), $allowedTypes)) {
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath)) {
            // Mise à jour de la photo dans la base de données
            $query = $pdo->prepare("UPDATE utilisateur SET photo = :photo WHERE utilisateur_id = :user_id");
            $query->execute([
                ':photo' => $fileName,
                ':user_id' => $userId,
            ]);

            echo json_encode(['success' => true, 'fileName' => $fileName]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur lors du téléchargement du fichier.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Type de fichier non autorisé.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Aucun fichier reçu.']);
}
?>