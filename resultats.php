<?php
require 'db.php'; // Inclusion de la connexion à la base de données

// Récupération des données du formulaire
$depart = $_GET['depart'] ?? '';
$arrivee = $_GET['arrivee'] ?? '';
$date = $_GET['date'] ?? '';

// Récupération des filtres
$ecologique = $_GET['ecologique'] ?? '';
$prix_max = $_GET['prix_max'] ?? '';
$duree_max = $_GET['duree_max'] ?? '';
$note_min = $_GET['note_min'] ?? '';

// Construction de la requête SQL avec les filtres
$sql = "
    SELECT c.covoiturage_id, c.date_depart, c.heure_depart, c.lieu_depart, 
           c.date_arrivee, c.heure_arrivee, c.lieu_arrivee, c.nb_place, 
           c.prix_personne, u.nom AS chauffeur, u.photo, u.pseudo, 
           AVG(a.note) AS note, 
           TIMESTAMPDIFF(MINUTE, CONCAT(c.date_depart, ' ', c.heure_depart), CONCAT(c.date_arrivee, ' ', c.heure_arrivee)) AS duree,
           v.energie
    FROM covoiturage c
    JOIN participe p ON c.covoiturage_id = p.covoiturage_id
    JOIN utilisateur u ON p.utilisateur_id = u.utilisateur_id
    JOIN utilise ut ON c.covoiturage_id = ut.covoiturage_id
    JOIN voiture v ON ut.voiture_id = v.voiture_id
    LEFT JOIN depose d ON d.utilisateur_id = u.utilisateur_id
    LEFT JOIN avis a ON a.avis_id = d.avis_id
    WHERE c.lieu_depart = :depart
      AND c.lieu_arrivee = :arrivee
      AND c.date_depart = :date
      AND c.nb_place > 0
    GROUP BY c.covoiturage_id, c.date_depart, c.heure_depart, c.lieu_depart, 
             c.date_arrivee, c.heure_arrivee, c.lieu_arrivee, c.nb_place, 
             c.prix_personne, u.nom, u.photo, u.pseudo, v.energie
";
// Ajout des filtres dynamiques
$params = [
    ':depart' => $depart,
    ':arrivee' => $arrivee,
    ':date' => $date,
];

if (!empty($energie)) {
    $sql .= " AND v.energie = :energie";
    $params[':energie'] = $energie;
}

if (!empty($prix_max)) {
    $sql .= " AND c.prix_personne <= :prix_max";
    $params[':prix_max'] = $prix_max;
}

if (!empty($duree_max)) {
    $sql .= " AND TIMESTAMPDIFF(MINUTE, CONCAT(c.date_depart, ' ', c.heure_depart), CONCAT(c.date_arrivee, ' ', c.heure_arrivee)) <= :duree_max";
    $params[':duree_max'] = $duree_max;
}

if (!empty($note_min)) {
    $sql .= " HAVING AVG(a.note) >= :note_min";
    $params[':note_min'] = $note_min;
}

// Préparation et exécution de la requête
$query = $pdo->prepare($sql);
$query->execute($params);

$resultats = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats des covoiturages - EcoRide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Filtres -->
    <section class="container my-5">
        <h2 class="text-center">Filtres</h2>
        <form action="resultats.php" method="GET" class="mb-4">
            <input type="hidden" name="depart" value="<?= htmlspecialchars($depart) ?>">
            <input type="hidden" name="arrivee" value="<?= htmlspecialchars($arrivee) ?>">
            <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">

            <div class="row g-3">
                <div class="col-md-3">
                    <label for="energie" class="form-label">Voyage écologique</label>
                    <select id="energie" name="energie" class="form-select">
                        <option value="">Tous</option>
                        <option value="Essence" <?= isset($_GET['energie']) && $_GET['energie'] == 'Essence' ? 'selected' : '' ?>>Essence</option>
                        <option value="Diesel" <?= isset($_GET['energie']) && $_GET['energie'] == 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                        <option value="Électrique" <?= isset($_GET['energie']) && $_GET['energie'] == 'Électrique' ? 'selected' : '' ?>>Électrique</option>
                        <option value="Hybride" <?= isset($_GET['energie']) && $_GET['energie'] == 'Hybride' ? 'selected' : '' ?>>Hybride</option>
                        <option value="1" <?= isset($_GET['ecologique']) && $_GET['ecologique'] == '1' ? 'selected' : '' ?>>Oui</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="prix_max" class="form-label">Prix maximum (€)</label>
                    <input type="number" id="prix_max" name="prix_max" class="form-control" placeholder="Ex : 50" value="<?= htmlspecialchars($_GET['prix_max'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label for="duree_max" class="form-label">Durée maximum (minutes)</label>
                    <input type="number" id="duree_max" name="duree_max" class="form-control" placeholder="Ex : 120" value="<?= htmlspecialchars($_GET['duree_max'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label for="note_min" class="form-label">Note minimale</label>
                    <input type="number" id="note_min" name="note_min" class="form-control" placeholder="Ex : 4" step="0.1" min="0" max="5" value="<?= htmlspecialchars($_GET['note_min'] ?? '') ?>">
                </div>
            </div>
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-success">Appliquer les filtres</button>
            </div>
        </form>
    </section>

    <!-- Résultats -->
    <section class="container my-5">
        <?php if (!empty($resultats)): ?>
            <div class="row">
            <?php foreach ($resultats as $covoiturage): ?>
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <img src="<?= htmlspecialchars($covoiturage['photo'] ?? 'img/default-driver.jpg') ?>" alt="Photo du chauffeur" class="rounded-circle me-3" style="width: 60px; height: 60px;">
                    <div>
                        <h5 class="mb-0"><?= htmlspecialchars($covoiturage['pseudo'] ?? 'Inconnu') ?></h5>
                        <small>Note : <?= htmlspecialchars($covoiturage['note'] ?? '0') ?> / 5</small>
                    </div>
                </div>
                <p><strong>Départ :</strong> <?= htmlspecialchars($covoiturage['lieu_depart'] ?? 'Non spécifié') ?>, <?= htmlspecialchars($covoiturage['heure_depart'] ?? 'Non spécifiée') ?></p>
                <p><strong>Arrivée :</strong> <?= htmlspecialchars($covoiturage['lieu_arrivee'] ?? 'Non spécifié') ?>, <?= htmlspecialchars($covoiturage['heure_arrivee'] ?? 'Non spécifiée') ?></p>
                <p><strong>Places restantes :</strong> <?= htmlspecialchars($covoiturage['nb_place'] ?? '0') ?></p>
                <p><strong>Prix :</strong> <?= htmlspecialchars($covoiturage['prix_personne'] ?? '0') ?>€</p>
                <p><strong>Voyage écologique :</strong> <?= htmlspecialchars($covoiturage['ecologique'] ?? 'Non') ?></p>
                <div class="text-center">
                    <a href="details.php?id=<?= htmlspecialchars($covoiturage['covoiturage_id'] ?? '') ?>" class="btn btn-primary">Détail</a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center" role="alert">
                Aucun covoiturage ne correspond à vos critères. Essayez d'élargir vos filtres.
            </div>
        <?php endif; ?>
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