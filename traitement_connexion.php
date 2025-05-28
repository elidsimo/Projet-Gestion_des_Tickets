<?php
// traitement_connexion.php

// Démarre la session PHP. C'est essentiel pour stocker les informations de l'utilisateur.
session_start();

// Définit l'en-tête pour indiquer que la réponse sera au format JSON.
header('Content-Type: application/json');

require_once 'db_connect.php';

// Initialise le tableau de réponse JSON que nous enverrons au JavaScript.
$response = [
    'success' => false,
    'message' => '',
    'redirect' => '' // Cette URL sera utilisée par le JavaScript en cas de succès.
];

// Vérifie si la requête a été soumise via la méthode POST (formulaire HTML).
if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $email = trim(htmlspecialchars($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? ''; // Le mot de passe n'est pas nettoyé ici, il sera vérifié avec password_verify

    // Validation basique des champs non vides.
    if (empty($email) || empty($password)) {
        $response['message'] = 'Veuillez entrer votre adresse e-mail et votre mot de passe.';
        echo json_encode($response);
        exit(); // Arrête l'exécution du script.
    }

   
    $stmt = $conn->prepare("SELECT id_utilisateur, nom_utilisateur, mot_de_passe FROM utilisateurs WHERE email = ?");

    // Gère une éventuelle erreur de préparation de la requête.
    if ($stmt === false) {
        $response['message'] = 'Erreur interne du serveur lors de la préparation de la requête.';
        // Pour le débogage, vous pouvez ajouter $conn->error ici, mais pas en production.
        echo json_encode($response);
        exit();
    }

    $stmt->bind_param("s", $email);
    $stmt->execute(); // Exécute la requête.
    $stmt->store_result(); // Stocke le jeu de résultats pour vérifier le nombre de lignes.

    // Vérifie si un utilisateur correspondant à l'email a été trouvé.
    if ($stmt->num_rows === 1) {
        // Lie les colonnes de résultat aux variables PHP.
        $stmt->bind_result($id_utilisateur, $nom_utilisateur, $hashed_password_from_db);
        $stmt->fetch(); // Récupère les valeurs.

        // Vérifie si le mot de passe fourni correspond au mot de passe haché dans la base de données.
        // password_verify() est la fonction sécurisée pour cette vérification.
        if (password_verify($password, $hashed_password_from_db)) {
            // Connexion réussie !
            $response['success'] = true;
            $response['message'] = 'Connexion réussie ! ';
            $response['redirect'] = 'accueil.html'; // Définissez la page vers laquelle rediriger

            // Stocke les informations de l'utilisateur dans la session PHP.
            // Ces variables de session peuvent être utilisées sur d'autres pages pour identifier l'utilisateur connecté.
            $_SESSION['id_utilisateur'] = $id_utilisateur;
            $_SESSION['nom_utilisateur'] = $nom_utilisateur;
            $_SESSION['logged_in'] = true; // Un simple drapeau pour vérifier l'état de connexion.

        } else {
            // Mot de passe incorrect.
            $response['message'] = 'Mot de passe incorrect.';
        }
    } else {
        // Aucun utilisateur trouvé avec cet email.
        $response['message'] = 'Adresse e-mail non enregistrée.';
    }

    // Ferme la requête préparée pour libérer les ressources.
    $stmt->close();

} else {
    // Si la page est accédée directement (sans soumission de formulaire POST), renvoie un message d'erreur.
    $response['message'] = 'Accès non autorisé.';
}

// Ferme la connexion à la base de données.
$conn->close();

// Encode le tableau de réponse PHP en JSON et l'envoie au navigateur.
echo json_encode($response);
?>