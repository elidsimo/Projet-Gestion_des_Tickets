<?php
header('Content-Type: application/json');
require_once 'db_connect.php'; // Assure-toi que ce fichier existe et contient $conn

$response = ['success' => false, 'message' => 'Une erreur est survenue.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $nouveau_password = $_POST['new_password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Adresse e-mail invalide.";
        echo json_encode($response);
        exit;
    }

    // Vérifie si l'e-mail existe
    $stmt = $conn->prepare("SELECT id_utilisateur FROM utilisateurs WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();

        // Hache le mot de passe
        $hashed_password = password_hash($nouveau_password, PASSWORD_DEFAULT);

        // Mise à jour
        $update = $conn->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?");
        $update->bind_param("ss", $hashed_password, $email);

        if ($update->execute()) {
            $response['success'] = true;
            $response['message'] = "Mot de passe mis à jour avec succès.";
        } else {
            $response['message'] = "Erreur lors de la mise à jour.";
        }

        $update->close();
    } else {
        $response['message'] = "Aucun utilisateur trouvé avec cette adresse e-mail.";
    }

    $conn->close();
} else {
    $response['message'] = "Méthode non autorisée.";
}

echo json_encode($response);
?>