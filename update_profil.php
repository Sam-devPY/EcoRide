<?php
session_start();
require 'db.php'; // Inclusion de la connexion à la base de données

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté.']);
    exit;
}

// Vérification des données POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['field'], $_POST['value'])) {
    $field = $_POST['field'];
    $value = $_POST['value'];
    $userId = $_SESSION['user_id'];

    // Liste des champs autorisés pour la mise à jour
    $allowedFields = ['role', 'immatriculation', 'date_immatriculation', 'modele', 'couleur', 'marque', 'energie', 'nb_place', 'fumeur', 'animal'];

    if (in_array($field, $allowedFields)) {
        try {
            $query = $pdo->prepare("UPDATE utilisateur SET $field = :value WHERE utilisateur_id = :user_id");
            $query->execute([
                ':value' => $value,
                ':user_id' => $userId,
            ]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Champ non autorisé.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Données invalides.']);
}
?>