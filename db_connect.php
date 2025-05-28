<?php
// db_connect.php

$servername = "localhost"; // Généralement 'localhost'
$username = "root";       // Votre nom d'utilisateur MySQL
$password = "";           // Votre mot de passe MySQL (souvent vide pour XAMPP/WAMP)
$dbname = "fcbarca_tickets"; // Le nom de votre base de données

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion à la base de données : " . $conn->connect_error);
}

// Définir l'encodage des caractères
$conn->set_charset("utf8mb4");

?>