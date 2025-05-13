<?php
session_start();
require 'db.php'; // Inclusion de la connexion à la base de données

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

// Récupération des informations de l'utilisateur
function getUtilisateur($pdo, $userId) {
    $query = $pdo->prepare("SELECT * FROM utilisateur WHERE utilisateur_id = :utilisateur_id");
    $query->execute([':utilisateur_id' => $userId]);
    return $query->fetch(PDO::FETCH_ASSOC);
}

// Mise à jour des informations utilisateur
function updateUtilisateur($pdo, $userId, $role, $photo) {
    $query = $pdo->prepare("
        UPDATE utilisateur 
        SET role = :role, photo = :photo
        WHERE utilisateur_id = :utilisateur_id
    ");
    $query->execute([
        ':role' => $role,
        ':photo' => $photo,
        ':utilisateur_id' => $userId,
    ]);
}

// Récupération des données utilisateur
$utilisateur = getUtilisateur($pdo, $_SESSION['user_id']);
if (!$utilisateur) {
    die('Utilisateur introuvable.');
}

// Traitement des requêtes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? $utilisateur['role'];
    $photo = $utilisateur['photo'] ?? 'default.jpg'; // Photo par défaut

    // Gestion de l'upload de la photo
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = "uploads/";
        $fileName = uniqid() . "_" . basename($_FILES['photo']['name']);
        $targetFilePath = $targetDir . $fileName;

        // Vérifiez si le fichier est une image valide
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($fileType), $allowedTypes)) {
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath)) {
                $photo = $fileName;
            }
        }
    }

    // Mise à jour des informations utilisateur
    updateUtilisateur($pdo, $_SESSION['user_id'], $role, $photo);

    // Redirection pour éviter la resoumission du formulaire
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
    <style>
        #specific-fields { display: none; } /* Masquer les champs spécifiques par défaut */
        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">EcoRide</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="profil.php">Mon profil</a></li>
                <li class="nav-item"><a class="nav-link" href="covoiturages.php">Covoiturages</a></li>
                <li class="nav-item"><a class="nav-link" href="deconnexion.php">Se déconnecter</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Mon Profil</h5>
            <form action="profil.php" method="POST" enctype="multipart/form-data">
            <div class="text-center mb-3">
    <label for="photo">
        <img src="uploads/<?= htmlspecialchars($utilisateur['photo'] ?? 'default.jpg') ?>" alt="Photo de profil" class="profile-photo">
    </label>
    <input type="file" id="photo" name="photo" class="d-none">
</div>
                <div class="mb-3">
    <label for="role" class="form-label">Votre rôle</label>
    <select name="role" id="role" class="form-select" data-field="role" required>
        <option value="passager" <?= $utilisateur['role'] === 'passager' ? 'selected' : '' ?>>Passager</option>
        <option value="chauffeur" <?= $utilisateur['role'] === 'chauffeur' ? 'selected' : '' ?>>Chauffeur</option>
        <option value="passager_chauffeur" <?= $utilisateur['role'] === 'passager_chauffeur' ? 'selected' : '' ?>>Passager et Chauffeur</option>
    </select>
</div>
<div id="specific-fields">
    <div class="mb-3">
        <label for="immatriculation" class="form-label">Plaque d'immatriculation</label>
        <input type="text" id="immatriculation" name="immatriculation" class="form-control" data-field="immatriculation">
    </div>
    <div class="mb-3">
        <label for="date_immatriculation" class="form-label">Date de première immatriculation</label>
        <input type="date" id="date_immatriculation" name="date_immatriculation" class="form-control" data-field="date_immatriculation">
    </div>
    <div class="mb-3">
        <label for="modele" class="form-label">Modèle</label>
        <input type="text" id="modele" name="modele" class="form-control" data-field="modele">
    </div>
    <div class="mb-3">
        <label for="couleur" class="form-label">Couleur</label>
        <input type="text" id="couleur" name="couleur" class="form-control" data-field="couleur">
    </div>
    <div class="mb-3">
        <label for="marque" class="form-label">Marque</label>
        <input type="text" id="marque" name="marque" class="form-control" data-field="marque">
    </div>
    <div class="mb-3">
        <label for="energie" class="form-label">Énergie</label>
        <select id="energie" name="energie" class="form-select" data-field="energie">
            <option value="Essence">Essence</option>
            <option value="Diesel">Diesel</option>
            <option value="Électrique">Électrique</option>
            <option value="Hybride">Hybride</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="nb_place" class="form-label">Nombre de places disponibles</label>
        <input type="number" id="nb_place" name="nb_place" class="form-control" min="1" data-field="nb_place">
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="fumeur" id="fumeur" data-field="fumeur">
        <label class="form-check-label" for="fumeur">Accepte les fumeurs</label>
    </div>
    <div class="form-check">
        <input class="form-check-input" type="checkbox" name="animal" id="animal" data-field="animal">
        <label class="form-check-label" for="animal">Accepte les animaux</label>
    </div>

    <!-- Bouton Actualiser -->
    <button type="button" id="update-vehicle" class="btn btn-success mt-3">Actualiser</button>
</div>
            
            </form>
        </div>
    </div>
</div>

<script src="/JS/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>