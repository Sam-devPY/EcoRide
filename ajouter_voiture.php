<?php
session_start();
require 'db.php'; // Inclusion de la connexion à la base de données

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$error = '';
$success = '';

// Récupération des marques pour le formulaire
try {
    $marquesQuery = $pdo->query("SELECT * FROM marque");
    $marques = $marquesQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur lors de la récupération des marques : ' . $e->getMessage());
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modele = $_POST['modele'] ?? '';
    $immatriculation = $_POST['immatriculation'] ?? '';
    $couleur = $_POST['couleur'] ?? '';
    $annee = $_POST['annee'] ?? '';
    $date_premiere_immatriculation = $_POST['date_premiere_immatriculation'] ?? '';
    $energie = $_POST['energie'] ?? '';
    $marque_id = $_POST['marque_id'] ?? '';

    // Validation des champs
    if (!empty($modele) && !empty($immatriculation) && !empty($couleur) && !empty($annee) && !empty($date_premiere_immatriculation) && !empty($energie) && !empty($marque_id)) {
        try {
            // Insertion dans la table voiture
            $query = $pdo->prepare("
                INSERT INTO voiture (modele, immatriculation, couleur, annee, date_premiere_immatriculation, energie)
                VALUES (:modele, :immatriculation, :couleur, :annee, :date_premiere_immatriculation, :energie)
            ");
            $query->execute([
                ':modele' => $modele,
                ':immatriculation' => $immatriculation,
                ':couleur' => $couleur,
                ':annee' => $annee,
                ':date_premiere_immatriculation' => $date_premiere_immatriculation,
                ':energie' => $energie,
            ]);

            // Récupération de l'ID de la voiture ajoutée
            $voiture_id = $pdo->lastInsertId();

            // Ajout dans la table detient pour lier la voiture à une marque
            $queryDetient = $pdo->prepare("
                INSERT INTO detient (voiture_id, marque_id)
                VALUES (:voiture_id, :marque_id)
            ");
            $queryDetient->execute([
                ':voiture_id' => $voiture_id,
                ':marque_id' => $marque_id,
            ]);

            // Ajout dans la table gere pour lier la voiture à l'utilisateur
            $queryGere = $pdo->prepare("
                INSERT INTO gere (utilisateur_id, voiture_id)
                VALUES (:utilisateur_id, :voiture_id)
            ");
            $queryGere->execute([
                ':utilisateur_id' => $_SESSION['user_id'],
                ':voiture_id' => $voiture_id,
            ]);

            $success = 'Voiture ajoutée avec succès !';
        } catch (PDOException $e) {
            $error = 'Une erreur est survenue : ' . $e->getMessage();
        }
    } else {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une voiture - EcoRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="index.php">EcoRide</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="profil.php">Mon profil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="covoiturages.php">Covoiturages</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="deconnexion.php">Se déconnecter</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <h1 class="text-center">Ajouter une voiture</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form action="ajouter_voiture.php" method="POST">
        <div class="mb-3">
            <label for="modele" class="form-label">Modèle</label>
            <input type="text" id="modele" name="modele" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="immatriculation" class="form-label">Immatriculation</label>
            <input type="text" id="immatriculation" name="immatriculation" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="couleur" class="form-label">Couleur</label>
            <input type="text" id="couleur" name="couleur" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="annee" class="form-label">Année</label>
            <input type="number" id="annee" name="annee" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="date_premiere_immatriculation" class="form-label">Date de première immatriculation</label>
            <input type="date" id="date_premiere_immatriculation" name="date_premiere_immatriculation" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="energie" class="form-label">Énergie</label>
            <input type="text" id="energie" name="energie" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="marque_id" class="form-label">Marque</label>
            <select id="marque_id" name="marque_id" class="form-select" required>
                <option value="">Sélectionnez une marque</option>
                <?php foreach ($marques as $marque): ?>
                    <option value="<?= htmlspecialchars($marque['marque_id']) ?>"><?= htmlspecialchars($marque['libelle']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-success">Ajouter</button>
        </div>
    </form>
</div>
</body>
</html>