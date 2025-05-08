<?php
// filepath: /Applications/MAMP/htdocs/EcoRide/connexion.php
require 'db.php'; // Inclusion de la connexion à la base de données
session_start(); // Démarrage de la session

$error = ''; // Variable pour stocker les messages d'erreur

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Vérification des champs
    if (!empty($email) && !empty($password)) {
        // Requête SQL pour vérifier l'utilisateur
        $query = $pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
        $query->execute([':email' => $email]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        // Vérification du mot de passe
        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie, stockage des informations utilisateur dans la session
            session_start();
            $_SESSION['user_id'] = $user['utilisateur_id'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            header('Location: index.php'); // Redirection vers la page d'accueil
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - EcoRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center">Connexion</h1>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form action="connexion.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Entrez votre email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Entrez votre mot de passe" required>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success">Se connecter</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <p>Pas encore inscrit ? <a href="inscription.php">Créer un compte</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>