<?php
// filepath: /Applications/MAMP/htdocs/EcoRide/db.php

$host = 'localhost';
$dbname = 'EcoRide';
$username = 'root'; // Par défaut pour MAMP
$password = 'root'; // Par défaut pour MAMP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>