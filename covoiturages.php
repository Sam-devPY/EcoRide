<?php
session_start(); // Démarrage de la session
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Covoiturages - EcoRide</title>
    <!-- Lien vers Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Lien vers votre feuille de style personnalisée -->
    <link rel="stylesheet" href="styles.css">
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

<header class="bg-success text-white text-center py-4">
    <h1>Rechercher un covoiturage</h1>
</header>

<section class="container my-5">
    <!-- Formulaire de recherche -->
    
    <form action="resultats.php" method="GET" class="mb-5">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="depart" class="form-label">Adresse de départ</label>
                <input type="text" id="depart" name="depart" class="form-control" placeholder="Ville de départ" required>
            </div>
            <div class="col-md-4">
                <label for="arrivee" class="form-label">Adresse d'arrivée</label>
                <input type="text" id="arrivee" name="arrivee" class="form-control" placeholder="Ville d'arrivée" required>
            </div>
            <div class="col-md-4">
                <label for="date" class="form-label">Date de départ</label>
                <input type="date" id="date" name="date" class="form-control" required>
            </div>
        </div>
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-success">Rechercher</button>
        </div>
    </form>
</section>

<section class="container my-5">
    <!-- Résultats des covoiturages -->
    <h2 class="text-center">Résultats des covoiturages</h2>
    <div class="row">
        <!-- Exemple de résultat -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="img/IMG_4150-Photoroom.png" alt="Photo du chauffeur" class="rounded-circle me-3" style="width: 60px; height: 60px;">
                        <div>
                            <h5 class="mb-0">Samir Le Terrible</h5>
                            <small>Note : ⭐⭐⭐⭐☆</small>
                        </div>
                    </div>
                    <p><strong>Départ :</strong> Nancy, 10:00</p>
                    <p><strong>Arrivée :</strong> Metz, 10:45</p>
                    <p><strong>Places restantes :</strong> 2</p>
                    <p><strong>Prix :</strong> 25€</p>
                    <p><strong>Voyage écologique :</strong> Oui</p>
                    <div class="text-center">
                        <a href="details.php?id=1" class="btn btn-primary">Détail</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Si aucun résultat -->
        <div class="col-12">
            <div class="alert alert-warning text-center" role="alert">
                Aucun covoiturage disponible pour cette date. Essayez une autre date proche.
            </div>
        </div>
    </div>
</section>

<footer class="bg-light text-center py-3">
    <p>Contactez-nous : <a href="mailto:contact@ecoride.com">contact@ecoride.com</a></p>
    <p><a href="/mentions-legales">Mentions légales</a></p>
    <p>&copy; 2025 EcoRide. Tous droits réservés.</p>
</footer>

<!-- Lien vers Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>