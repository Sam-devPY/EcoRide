<?php if (isset($_SESSION['user_name'])): ?>
    <p class="text-center">Bienvenue, <?= htmlspecialchars($_SESSION['user_name']) ?> !</p>
<?php endif; ?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'accueil - EcoRide</title>
    <!-- Lien vers Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Lien vers votre feuille de style personnalisée -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php
session_start(); // Démarrage de la session
?>

    <!-- Menu de navigation -->
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
    <a class="nav-link" href="profil.php">Mon profil</a>
</li>
                <li class="nav-item">
                    <a class="nav-link" href="covoiturages.php">Covoiturages</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact</a>
                </li>
                <?php if (isset($_SESSION['user_id'])): ?>
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
        <h1>Bienvenue chez EcoRide</h1>
    </header>

    <section class="presentation container my-5">
        <h2 class="text-center">Qui sommes-nous ?</h2>
        <p class="text-center">EcoRide est une entreprise dédiée à la mobilité durable et écologique. Nous proposons des solutions de transport respectueuses de l'environnement, adaptées à tous vos besoins de déplacement.</p>
        <p class="text-center">Notre mission est de réduire l'empreinte carbone des déplacements urbains tout en offrant un service de qualité. Nous croyons fermement que chaque geste compte et que chacun peut contribuer à un avenir plus vert.</p>
        <p class="text-center">En partageant votre voiture avec d'autres voyageurs, tout en faisant des rencontres, en covoiturant.</p>
        <div class="text-center">
            <img src="img/8A078034-DDC4-4091-994E-D7506153B1B7.png" alt="Présentation de l'entreprise" class="img-fluid">
        </div>
    </section>

    <section class="search-bar container my-5">
        <h2 class="text-center">Recherchez un itinéraire</h2>
        <form action="/recherche" method="GET" class="d-flex justify-content-center">
            <input type="text" name="itineraire" class="form-control w-50 me-2" placeholder="Entrez votre itinéraire" required>
            <button type="submit" class="btn btn-success">Rechercher</button>
        </form>
    </section>

    <footer class="bg-light text-center py-3">
        <p>Contactez-nous : <a href="mailto:contact@ecoride.com">contact@ecoride.com</a></p>
        <p><a href="/mentions-legales">Mentions légales</a></p>
        <p>&copy; 2025 EcoRide. Tous droits réservés.</p>
    </footer>

    <!-- Lien vers Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Lien vers votre fichier JavaScript -->
    <script src="script.js"></script>
</body>
</html>