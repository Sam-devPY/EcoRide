<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$covoiturage_id = $_POST['covoiturage_id'] ?? null;

if ($covoiturage_id) {
    $query = $pdo->prepare("
        UPDATE covoiturage 
        SET statut = 'en_cours', date_demarrage = NOW() 
        WHERE covoiturage_id = :covoiturage_id AND statut = 'en_attente'
    ");
    $query->execute([':covoiturage_id' => $covoiturage_id]);
}

header('Location: profil.php');
exit;
?>