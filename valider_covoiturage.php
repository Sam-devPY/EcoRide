<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$covoiturage_id = $_POST['covoiturage_id'] ?? null;
$validation = $_POST['validation'] ?? null;

if ($covoiturage_id && $validation) {
    if ($validation === 'valide') {
        // Ajouter les crédits au chauffeur
        $query = $pdo->prepare("
            UPDATE utilisateur 
            SET credits = credits + (
                SELECT prix_personne FROM covoiturage WHERE covoiturage_id = :covoiturage_id
            )
            WHERE utilisateur_id = (
                SELECT utilisateur_id FROM participe WHERE covoiturage_id = :covoiturage_id AND role = 'chauffeur'
            )
        ");
        $query->execute([':covoiturage_id' => $covoiturage_id]);
    } elseif ($validation === 'probleme') {
        // Enregistrer un problème pour traitement par un employé
        $query = $pdo->prepare("
            INSERT INTO signalements (covoiturage_id, utilisateur_id, commentaire, statut) 
            VALUES (:covoiturage_id, :utilisateur_id, :commentaire, 'en_attente')
        ");
        $query->execute([
            ':covoiturage_id' => $covoiturage_id,
            ':utilisateur_id' => $_SESSION['user_id'],
            ':commentaire' => $_POST['commentaire'] ?? 'Problème signalé.',
        ]);
    }
}

header('Location: profil.php');
exit;
?>