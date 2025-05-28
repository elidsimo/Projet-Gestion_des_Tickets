<?php
// traitement_inscription.php

// Définit l'en-tête pour indiquer que la réponse sera du JSON
header('Content-Type: application/json');

// Inclure le fichier de connexion à la base de données
require_once 'db_connect.php';

// Initialiser le tableau de réponse JSON
$response = [
    'success' => false,
    'message' => ''
];

// Vérifier si la requête est de type POST (le formulaire a été soumis)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Récupérer et nettoyer les données du formulaire
    $username = trim(htmlspecialchars($_POST['username'] ?? ''));
    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    // Validation basique côté serveur
    if (empty($username) || empty($email) || empty($password)) {
        $response['message'] = 'Veuillez remplir tous les champs.';
        echo json_encode($response);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Format d\'adresse e-mail invalide.';
        echo json_encode($response);
        exit();
    }

    // Hachage du mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Vérifier si l'email ou le nom d'utilisateur existe déjà
    $check_stmt = $conn->prepare("SELECT id_utilisateur FROM utilisateurs WHERE nom_utilisateur = ? OR email = ?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $response['message'] = 'Ce nom d\'utilisateur ou cette adresse e-mail est déjà enregistré(e).';
        $check_stmt->close();
        echo json_encode($response);
        exit();
    }
    $check_stmt->close();

    // Préparer et exécuter la requête d'insertion
    $stmt = $conn->prepare("INSERT INTO utilisateurs (nom_utilisateur, email, mot_de_passe) VALUES (?, ?, ?)");

    if ($stmt === false) {
        $response['message'] = 'Erreur interne du serveur (préparation SQL).';
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param("sss", $username, $email, $hashed_password);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
    } else {
        $response['message'] = 'Erreur lors de l\'inscription : ' . $stmt->error;
    }

    // Fermer la requête préparée
    $stmt->close();

} else {
    // Si la page est accédée directement sans méthode POST
    $response['message'] = 'Accès non autorisé.';
}

// Fermer la connexion à la base de données
$conn->close();

// Renvoyer la réponse JSON au front-end
echo json_encode($response);
?>