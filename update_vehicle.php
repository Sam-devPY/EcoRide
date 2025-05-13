<?php
session_start();
require 'db.php'; // Inclusion de la connexion à la base de données

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Utilisateur non connecté.']);
    exit;
}

// Vérification des données POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $immatriculation = $_POST['immatriculation'] ?? null;
    $dateImmatriculation = $_POST['date_immatriculation'] ?? null;
    $modele = $_POST['modele'] ?? null;
    $couleur = $_POST['couleur'] ?? null;
    $marque = $_POST['marque'] ?? null;
    $energie = $_POST['energie'] ?? null;
    $nbPlace = $_POST['nb_place'] ?? null;
    $fumeur = $_POST['fumeur'] ?? 0;
    $animal = $_POST['animal'] ?? 0;

    // Validation des données
    if (!$immatriculation || !$dateImmatriculation || !$modele || !$couleur || !$marque || !$energie || !$nbPlace) {
        echo json_encode(['success' => false, 'error' => 'Tous les champs obligatoires doivent être remplis.']);
        exit;
    }

    // Insertion des données dans la table voiture
    try {
        $pdo->beginTransaction();

        $query = $pdo->prepare("
            INSERT INTO voiture (immatriculation, date_premiere_immatriculation, modele, couleur, marque, energie, nb_place, fumeur, animal)
            VALUES (:immatriculation, :date_immatriculation, :modele, :couleur, :marque, :energie, :nb_place, :fumeur, :animal)
        ");
        $query->execute([
            ':immatriculation' => $immatriculation,
            ':date_immatriculation' => $dateImmatriculation,
            ':modele' => $modele,
            ':couleur' => $couleur,
            ':marque' => $marque,
            ':energie' => $energie,
            ':nb_place' => $nbPlace,
            ':fumeur' => $fumeur,
            ':animal' => $animal,
        ]);

        // Récupérer l'ID de la voiture insérée
        $voitureId = $pdo->lastInsertId();

        // Lier la voiture à l'utilisateur dans la table gere
        $query = $pdo->prepare("
            INSERT INTO gere (utilisateur_id, voiture_id)
            VALUES (:utilisateur_id, :voiture_id)
        ");
        $query->execute([
            ':utilisateur_id' => $userId,
            ':voiture_id' => $voitureId,
        ]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Requête invalide.']);
}
?>