<?php
require 'db.php'; // Inclusion de la connexion à la base de données

// Récupération de l'ID du covoiturage
$id = $_GET['id'] ?? 0;

// Requête SQL pour récupérer les détails du covoiturage
$query = $pdo->prepare("
    SELECT c.covoiturage_id, c.date_depart, c.heure_depart, c.lieu_depart, 
           c.date_arrivee, c.heure_arrivee, c.lieu_arrivee, c.nb_place, 
           c.prix_personne, u.nom AS chauffeur, u.photo, u.pseudo, u.note, 
           v.modele, v.marque, v.energie, v.ecologique, u.preferences
    FROM covoiturage c
    JOIN utilisateur u ON c.utilisateur_id = u.utilisateur_id
    JOIN utilise ut ON c.covoiturage_id = ut.covoiturage_id
    JOIN voiture v ON ut.voiture_id = v.voiture_id
    WHERE c.covoiturage_id = :id
");
$query->execute([':id' => $id]);

$covoiturage = $query->fetch(PDO::FETCH_ASSOC);

if (!$covoiturage) {
    die("Covoiturage introuvable.");
}

// Requête SQL pour récupérer les avis du conducteur
$avisQuery = $pdo->prepare("
    SELECT a.commentaire, a.note, u.pseudo AS auteur
    FROM avis a
    JOIN utilisateur u ON a.utilisateur_id = u.utilisateur_id
    WHERE a.conducteur_id = :conducteur_id
");
$avisQuery->execute([':conducteur_id' => $covoiturage['chauffeur_id']]);
$avis = $avisQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du covoiturage - EcoRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center">Détails du covoiturage</h1>
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <img src="<?= htmlspecialchars($covoiturage['photo']) ?>" alt="Photo du chauffeur" class="rounded-circle me-3" style="width: 80px; height: 80px;">
                    <div>
                        <h5 class="mb-0"><?= htmlspecialchars($covoiturage['chauffeur']) ?></h5>
                        <small>Pseudo : <?= htmlspecialchars($covoiturage['pseudo']) ?></small><br>
                        <small>Note : <?= htmlspecialchars($covoiturage['note']) ?> / 5</small>
                    </div>
                </div>
                <p><strong>Départ :</strong> <?= htmlspecialchars($covoiturage['lieu_depart']) ?>, <?= $covoiturage['heure_depart'] ?></p>
                <p><strong>Arrivée :</strong> <?= htmlspecialchars($covoiturage['lieu_arrivee']) ?>, <?= $covoiturage['heure_arrivee'] ?></p>
                <p><strong>Date :</strong> <?= $covoiturage['date_depart'] ?></p>
                <p><strong>Places restantes :</strong> <?= $covoiturage['nb_place'] ?></p>
                <p><strong>Prix :</strong> <?= $covoiturage['prix_personne'] ?>€</p>
                <p><strong>Voyage écologique :</strong> <?= $covoiturage['ecologique'] ? 'Oui' : 'Non' ?></p>
                <p><strong>Véhicule :</strong> <?= htmlspecialchars($covoiturage['modele']) ?> (<?= htmlspecialchars($covoiturage['marque']) ?>, <?= htmlspecialchars($covoiturage['energie']) ?>)</p>
                <p><strong>Préférences du conducteur :</strong> <?= htmlspecialchars($covoiturage['preferences']) ?></p>
            </div>
        </div>

        <h2 class="mt-5">Avis du conducteur</h2>
        <?php if (!empty($avis)): ?>
            <ul class="list-group">
                <?php foreach ($avis as $avi): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($avi['auteur']) ?></strong> (Note : <?= htmlspecialchars($avi['note']) ?> / 5)
                        <p><?= htmlspecialchars($avi['commentaire']) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucun avis pour ce conducteur.</p>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="covoiturages.php" class="btn btn-secondary">Retour</a>
        </div>
    </div>
</body>
</html>