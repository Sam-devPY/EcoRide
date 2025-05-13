<?php
session_start();
require 'db.php'; // Inclusion de la connexion à la base de données

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

// Récupération des données du formulaire
$covoiturage_id = $_POST['covoiturage_id'] ?? null;

if ($covoiturage_id) {
    // Vérifier si l'utilisateur est le chauffeur ou un participant
    $query = $pdo->prepare("
        SELECT c.covoiturage_id, c.prix_personne, c.nb_place, u.role AS utilisateur_role
        FROM covoiturage c
        JOIN participe p ON c.covoiturage_id = p.covoiturage_id
        JOIN utilisateur u ON p.utilisateur_id = u.utilisateur_id
        WHERE c.covoiturage_id = :covoiturage_id AND u.utilisateur_id = :utilisateur_id
    ");
    $query->execute([
        ':covoiturage_id' => $covoiturage_id,
        ':utilisateur_id' => $_SESSION['user_id'],
    ]);
    $covoiturage = $query->fetch(PDO::FETCH_ASSOC);

    if ($covoiturage) {
        if ($covoiturage['utilisateur_role'] === 'chauffeur') {
            // Si le chauffeur annule, envoyer un email aux participants
            $participantsQuery = $pdo->prepare("
                SELECT u.email
                FROM participe p
                JOIN utilisateur u ON p.utilisateur_id = u.utilisateur_id
                WHERE p.covoiturage_id = :covoiturage_id AND u.role = 'participant'
            ");
            $participantsQuery->execute([':covoiturage_id' => $covoiturage_id]);
            $participants = $participantsQuery->fetchAll(PDO::FETCH_ASSOC);

            foreach ($participants as $participant) {
                mail($participant['email'], 'Annulation de covoiturage', 'Le chauffeur a annulé le covoiturage.');
            }
        }

        // Supprimer l'utilisateur du covoiturage
        $deleteQuery = $pdo->prepare("
            DELETE FROM participe 
            WHERE covoiturage_id = :covoiturage_id AND utilisateur_id = :utilisateur_id
        ");
        $deleteQuery->execute([
            ':covoiturage_id' => $covoiturage_id,
            ':utilisateur_id' => $_SESSION['user_id'],
        ]);

        // Mettre à jour les places disponibles
        $updateQuery = $pdo->prepare("
            UPDATE covoiturage 
            SET nb_place = nb_place + 1 
            WHERE covoiturage_id = :covoiturage_id
        ");
        $updateQuery->execute([':covoiturage_id' => $covoiturage_id]);

        // Mettre à jour les crédits si nécessaire
        if ($covoiturage['utilisateur_role'] === 'participant') {
            $updateCreditsQuery = $pdo->prepare("
                UPDATE utilisateur 
                SET credits = credits + :prix 
                WHERE utilisateur_id = :utilisateur_id
            ");
            $updateCreditsQuery->execute([
                ':prix' => $covoiturage['prix_personne'],
                ':utilisateur_id' => $_SESSION['user_id'],
            ]);
        }
    }
}

header('Location: profil.php');
exit;
?>