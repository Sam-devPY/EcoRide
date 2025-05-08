<?php
session_start();
require 'db.php'; // Inclusion de la connexion à la base de données

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

// Récupération des informations de l'utilisateur
$query = $pdo->prepare("SELECT * FROM utilisateur WHERE utilisateur_id = :utilisateur_id");
$query->execute([':utilisateur_id' => $_SESSION['user_id']]);
$utilisateur = $query->fetch(PDO::FETCH_ASSOC);

// Récupération des voitures gérées par l'utilisateur
try {
    $voituresQuery = $pdo->prepare("
        SELECT v.voiture_id, v.modele, v.couleur, v.immatriculation, v.date_premiere_immatriculation, m.libelle AS marque, c.nb_place
        FROM voiture v
        JOIN gere g ON v.voiture_id = g.voiture_id
        JOIN detient d ON v.voiture_id = d.voiture_id
        JOIN marque m ON d.marque_id = m.marque_id
        LEFT JOIN utilise u ON v.voiture_id = u.voiture_id
        LEFT JOIN covoiturage c ON u.covoiturage_id = c.covoiturage_id
        WHERE g.utilisateur_id = :utilisateur_id
    ");
    $voituresQuery->execute([':utilisateur_id' => $_SESSION['user_id']]);
    $voitures = $voituresQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur lors de la récupération des voitures : ' . $e->getMessage());
}

// Gestion du formulaire pour mettre à jour le rôle, les préférences et la photo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $fumeur = isset($_POST['fumeur']) ? 1 : 0;
    $animal = isset($_POST['animal']) ? 1 : 0;

    // Gestion de l'upload de la photo
    $photoPath = $utilisateur['photo']; // Conserver l'ancienne photo par défaut
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
        $targetFile = $uploadDir . $fileName;

        // Vérifier si le fichier est une image
        $fileType = mime_content_type($_FILES['photo']['tmp_name']);
        if (in_array($fileType, ['image/jpeg', 'image/png', 'image/gif'])) {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
                $photoPath = $targetFile;
            }
        }
    }

    // Mise à jour du rôle, des préférences et de la photo dans la base de données
    $updateQuery = $pdo->prepare("
        UPDATE utilisateur 
        SET role = :role, fumeur = :fumeur, animal = :animal, photo = :photo 
        WHERE utilisateur_id = :utilisateur_id
    ");
    $updateQuery->execute([
        ':role' => $role,
        ':fumeur' => $fumeur,
        ':animal' => $animal,
        ':photo' => $photoPath,
        ':utilisateur_id' => $_SESSION['user_id'],
    ]);

    header('Location: profil.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil utilisateur - EcoRide</title>
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
                    <a class="nav-link" href="covoiturages.php">Covoiturages</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="profil.php">Mon profil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="deconnexion.php">Se déconnecter</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <h1 class="text-center">Mon profil</h1>
    <div class="card my-4">
        <div class="card-body text-center">
            <h5 class="card-title">Informations personnelles</h5>
            <?php if (!empty($utilisateur['photo'])): ?>
                <img src="<?= htmlspecialchars($utilisateur['photo']) ?>" alt="Photo de profil" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
            <?php else: ?>
                <img src="IMG/8A078034-DDC4-4091-994E-D7506153B1B7.png" alt="Photo par défaut" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
            <?php endif; ?>
            <p><strong>Nom :</strong> <?= htmlspecialchars($utilisateur['nom']) ?></p>
            <p><strong>Email :</strong> <?= htmlspecialchars($utilisateur['email']) ?></p>
        </div>
    </div>

    <h2 class="text-center">Rôle et préférences</h2>
    <form action="profil.php" method="POST" enctype="multipart/form-data" class="my-4">
        <div class="mb-3">
            <label for="role" class="form-label">Votre rôle</label>
            <select name="role" id="role" class="form-select" required>
                <option value="passager" <?= $utilisateur['role'] === 'passager' ? 'selected' : '' ?>>Passager</option>
                <option value="chauffeur" <?= $utilisateur['role'] === 'chauffeur' ? 'selected' : '' ?>>Chauffeur</option>
                <option value="passager_chauffeur" <?= $utilisateur['role'] === 'passager_chauffeur' ? 'selected' : '' ?>>Passager et Chauffeur</option>
            </select>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="fumeur" id="fumeur" <?= $utilisateur['fumeur'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="fumeur">Accepte les fumeurs</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="animal" id="animal" <?= $utilisateur['animal'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="animal">Accepte les animaux</label>
        </div>
        <div class="mb-3">
            <label for="photo" class="form-label">Photo de profil</label>
            <input type="file" name="photo" id="photo" class="form-control" accept="image/*">
        </div>
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-success">Mettre à jour</button>
        </div>
    </form>

    <h2 class="text-center">Mes voitures</h2>
    <div class="text-center my-4">
        <a href="ajouter_voiture.php" class="btn btn-primary">Ajouter une voiture</a>
    </div>
    <?php if (!empty($voitures)): ?>
    <div class="row">
        <?php foreach ($voitures as $voiture): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($voiture['marque']) ?> - <?= htmlspecialchars($voiture['modele']) ?></h5>
                        <p><strong>Immatriculation :</strong> <?= htmlspecialchars($voiture['immatriculation']) ?></p>
                        <p><strong>Date de première immatriculation :</strong> <?= htmlspecialchars($voiture['date_premiere_immatriculation']) ?></p>
                        <p><strong>Couleur :</strong> <?= htmlspecialchars($voiture['couleur']) ?></p>
                        <p><strong>Places disponibles :</strong> <?= htmlspecialchars($voiture['nb_place'] ?? 'Non spécifié') ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
        <div class="alert alert-warning text-center">Vous ne gérez aucune voiture pour le moment.</div>
    <?php endif; ?>
</div>
</body>
</html>