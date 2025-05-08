<?php
require 'db.php'; // Inclusion de la connexion à la base de données

$error = ''; // Variable pour stocker les messages d'erreur
$success = ''; // Variable pour stocker les messages de succès

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $pseudo = $_POST['pseudo'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Vérification des champs
    if (!empty($pseudo) && !empty($nom) && !empty($prenom) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        if ($password === $confirm_password) {
            // Vérification de la sécurité du mot de passe
            if (strlen($password) >= 8 && preg_match('/[A-Z]/', $password) && preg_match('/[0-9]/', $password)) {
                // Vérification si l'email ou le pseudo existe déjà
                $query = $pdo->prepare("SELECT * FROM utilisateur WHERE email = :email OR pseudo = :pseudo");
                $query->execute([':email' => $email, ':pseudo' => $pseudo]);
                $user = $query->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    // Hachage du mot de passe
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insertion dans la base de données avec 20 crédits par défaut
                    $insert = $pdo->prepare("
                        INSERT INTO utilisateur (pseudo, nom, prenom, email, password, credits) 
                        VALUES (:pseudo, :nom, :prenom, :email, :password, :credits)
                    ");
                    $insert->execute([
                        ':pseudo' => $pseudo,
                        ':nom' => $nom,
                        ':prenom' => $prenom,
                        ':email' => $email,
                        ':password' => $hashed_password,
                        ':credits' => 20, // Ajout de 20 crédits par défaut
                    ]);

                    $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
                } else {
                    $error = 'Cet email ou ce pseudo est déjà utilisé.';
                }
            } else {
                $error = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.';
            }
        } else {
            $error = 'Les mots de passe ne correspondent pas.';
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
    <title>Inscription - EcoRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center">Inscription</h1>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <form action="inscription.php" method="POST">
                    <div class="mb-3">
                        <label for="pseudo" class="form-label">Pseudo</label>
                        <input type="text" name="pseudo" id="pseudo" class="form-control" placeholder="Entrez votre pseudo" required>
                    </div>
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom</label>
                        <input type="text" name="nom" id="nom" class="form-control" placeholder="Entrez votre nom" required>
                    </div>
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom</label>
                        <input type="text" name="prenom" id="prenom" class="form-control" placeholder="Entrez votre prénom" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Entrez votre email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Entrez votre mot de passe" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmez le mot de passe</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirmez votre mot de passe" required>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success">S'inscrire</button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <p>Déjà inscrit ? <a href="connexion.php">Connectez-vous</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>