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

// Récupération des voitures gérées par l'utilisateur
try {
    $voituresQuery = $pdo->prepare("
        SELECT v.voiture_id, v.modele, m.libelle AS marque, v.energie
        FROM voiture v
        JOIN detient d ON v.voiture_id = d.voiture_id
        JOIN marque m ON d.marque_id = m.marque_id
        JOIN gere g ON v.voiture_id = g.voiture_id
        WHERE g.utilisateur_id = :utilisateur_id
    ");
    $voituresQuery->execute([':utilisateur_id' => $_SESSION['user_id']]);
    $voitures = $voituresQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur lors de la récupération des voitures : ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $lieu_depart = $_POST['lieu_depart'] ?? '';
    $lieu_arrivee = $_POST['lieu_arrivee'] ?? '';
    $date_depart = $_POST['date_depart'] ?? '';
    $heure_depart = $_POST['heure_depart'] ?? '';
    $date_arrivee = $_POST['date_arrivee'] ?? '';
    $heure_arrivee = $_POST['heure_arrivee'] ?? '';
    $nb_place = $_POST['nb_place'] ?? 0;
    $prix_personne = $_POST['prix_personne'] ?? 0;
    $voiture_id = $_POST['voiture_id'] ?? 0;

    // Validation des champs
    if (!empty($lieu_depart) && !empty($lieu_arrivee) && !empty($date_depart) && !empty($heure_depart) && 
        !empty($nb_place) && !empty($prix_personne) && !empty($voiture_id)) {
        try {
            // Insertion dans la table covoiturage
            $query = $pdo->prepare("
                INSERT INTO covoiturage (lieu_depart, lieu_arrivee, date_depart, heure_depart, date_arrivee, heure_arrivee, nb_place, prix_personne)
                VALUES (:lieu_depart, :lieu_arrivee, :date_depart, :heure_depart, :date_arrivee, :heure_arrivee, :nb_place, :prix_personne)
            ");
            $query->execute([
                ':lieu_depart' => $lieu_depart,
                ':lieu_arrivee' => $lieu_arrivee,
                ':date_depart' => $date_depart,
                ':heure_depart' => $heure_depart,
                ':date_arrivee' => $date_arrivee,
                ':heure_arrivee' => $heure_arrivee,
                ':nb_place' => $nb_place,
                ':prix_personne' => $prix_personne,
            ]);

            // Récupération de l'ID du covoiturage ajouté
            $covoiturage_id = $pdo->lastInsertId();

            // Insertion dans la table utilise pour associer la voiture au covoiturage
            $queryUtilise = $pdo->prepare("
                INSERT INTO utilise (voiture_id, covoiturage_id)
                VALUES (:voiture_id, :covoiturage_id)
            ");
            $queryUtilise->execute([
                ':voiture_id' => $voiture_id,
                ':covoiturage_id' => $covoiturage_id,
            ]);

            // Insertion dans la table participe pour lier l'utilisateur au covoiturage
            $queryParticipe = $pdo->prepare("
                INSERT INTO participe (utilisateur_id, covoiturage_id)
                VALUES (:utilisateur_id, :covoiturage_id)
            ");
            $queryParticipe->execute([
                ':utilisateur_id' => $_SESSION['user_id'],
                ':covoiturage_id' => $covoiturage_id,
            ]);

            $success = 'Covoiturage ajouté avec succès et lié à votre compte !';
        } catch (Exception $e) {
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
    <title>Ajouter un covoiturage - EcoRide</title>
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
                    <a class="nav-link" href="index.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="covoiturages.php">Covoiturages</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact</a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="ajouter_covoiturage.php">Ajouter un covoiturage</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="deconnexion.php">Se déconnecter</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="connexion.php">Se connecter</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <h1 class="text-center">Ajouter un covoiturage</h1>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form action="ajouter_covoiturage.php" method="POST">
        <div class="mb-3">
            <label for="lieu_depart" class="form-label">Lieu de départ</label>
            <input type="text" id="lieu_depart" name="lieu_depart" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="lieu_arrivee" class="form-label">Lieu d'arrivée</label>
            <input type="text" id="lieu_arrivee" name="lieu_arrivee" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="date_depart" class="form-label">Date de départ</label>
            <input type="date" id="date_depart" name="date_depart" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="heure_depart" class="form-label">Heure de départ</label>
            <input type="time" id="heure_depart" name="heure_depart" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="date_arrivee" class="form-label">Date d'arrivée (optionnel)</label>
            <input type="date" id="date_arrivee" name="date_arrivee" class="form-control">
        </div>
        <div class="mb-3">
            <label for="heure_arrivee" class="form-label">Heure d'arrivée (optionnel)</label>
            <input type="time" id="heure_arrivee" name="heure_arrivee" class="form-control">
        </div>
        <div class="mb-3">
            <label for="nb_place" class="form-label">Nombre de places disponibles</label>
            <input type="number" id="nb_place" name="nb_place" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="prix_personne" class="form-label">Prix par personne (€)</label>
            <input type="number" id="prix_personne" name="prix_personne" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="voiture_id" class="form-label">Voiture</label>
            <select id="voiture_id" name="voiture_id" class="form-select" required>
                <option value="">Sélectionnez une voiture</option>
                <?php if (!empty($voitures)): ?>
                    <?php foreach ($voitures as $voiture): ?>
                        <option value="<?= htmlspecialchars($voiture['voiture_id']) ?>">
                            <?= htmlspecialchars($voiture['marque'] ?? 'Marque inconnue') ?> - <?= htmlspecialchars($voiture['modele'] ?? 'Modèle inconnu') ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>Aucune voiture disponible. Ajoutez une voiture dans votre profil.</option>
                <?php endif; ?>
            </select>
        </div>
        <div class="text-center">
            <button type="submit" class="btn btn-success">Ajouter</button>
        </div>
    </form>
</div>
</body>
</html>