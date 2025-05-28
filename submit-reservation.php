<?php
// Include the database connection file
require_once 'db_connect.php'; // Vérifiez le chemin selon votre structure

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize input
    $id_client = htmlspecialchars($_POST['id_client'] ?? ''); // Si vous utilisez id_client
    $id_match = $_POST['id_match'] ?? '';
    $type_billet = $_POST['type_billet'] ?? '';

    // Validate inputs
    if (empty($id_match) || empty($type_billet)) {
        $confirmation = false;
    } else {
        $statut_reservation = "confirmée"; // Statut par défaut

        // Construire la requête sans le prix_total
        $sql = "INSERT INTO reservations (id_match, type_billet, date_reservation, statut_reservation";

        // Vérifiez si vous utilisez id_client dans la table
        if (!empty($id_client)) {
            $sql .= ", id_client";
        }
        $sql .= ") VALUES (?, ?, NOW(), ?";

        if (!empty($id_client)) {
            $sql .= ", ?";
        }
        $sql .= ")";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            if (!empty($id_client)) {
                $stmt->bind_param("isss", $id_match, $type_billet, $statut_reservation, $id_client);
            } else {
                $stmt->bind_param("iss", $id_match, $type_billet, $statut_reservation);
            }

            if ($stmt->execute()) {
                $confirmation = true;

                // Infos des matchs statiques
                $matchs = [
                    "1" => "FC Barcelona vs Real Betis - 20 Juillet 2025 - Spotify Camp Nou",
                    "2" => "Real Sociedad vs FC Barcelona - 15 Septembre 2025 - Reale Arena (Copa del Rey)",
                    "3" => "FC Barcelona vs Manchester City - 25 Octobre 2025 - Spotify Camp Nou (UEFA Champions League)",
                    "4" => "FC Barcelona vs Real Madrid - 14 Décembre 2025 - Spotify Camp Nou (El Clásico)"
                ];
                $match_info = $matchs[$id_match] ?? 'Match inconnu';
            } else {
                $confirmation = false;
                error_log("Erreur lors de l'insertion : " . $stmt->error);
            }
            $stmt->close();
        } else {
            $confirmation = false;
            error_log("Erreur préparation requête : " . $conn->error);
        }

        $conn->close();
    }

} else {
    header("Location: billet.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Confirmation de Réservation</title>
    <link rel="icon" href="logobarça.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f1eaea;
            background-image: url('ff.jpg');
            background-size: cover;
            background-attachment: fixed;
            padding-bottom: 80px;
        }

        header {
            background-color: #A50044;
            color: white;
            padding: 1rem 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            margin: 0;
            font-size: 2.5rem;
        }

        header p {
            font-size: 1.2rem;
            margin-top: 0.5rem;
        }

        main {
            padding: 2rem;
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }

        .form-section {
            margin: 2rem auto;
            max-width: 600px;
            padding: 2rem;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .confirmation-message,
        .error-message {
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            font-size: 1.2rem;
            animation: fadeIn 0.5s forwards;
        }

        .confirmation-message {
            background-color: #4CAF50;
            color: white;
        }

        .error-message {
            background-color: #f44336;
            color: white;
        }

        .form-button {
            display: inline-block;
            padding: 1rem 2rem;
            margin-top: 1.5rem;
            background: linear-gradient(135deg, #A50044, #003E7E);
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            border-radius: 25px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .form-button:hover {
            background: linear-gradient(135deg, #7F0033, #002B5C);
            transform: translateY(-3px);
        }

        footer {
            background-color: #A50044;
            color: white;
            text-align: center;
            padding: 0rem 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            flex-wrap: wrap;
        }

        .social-media {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .social-media i {
            font-size: 1.5rem;
            color: white;
            transition: color 0.3s, transform 0.3s;
            cursor: pointer;
        }

        .social-media i:hover {
            color: #FFD700;
            transform: scale(1.2);
        }

        .social-media .fa-facebook:hover {
            color: #1877F2;
        }

        .social-media .fa-twitter:hover {
            color: #1DA1F2;
        }

        .social-media .fa-instagram:hover {
            color: #E4405F;
        }

        .social-media .fa-youtube:hover {
            color: #FF0000;
        }

        .social-media .fa-tiktok:hover {
            color: #000000;
        }

        .copyright {
            font-size: 0.9rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            header h1 {
                font-size: 2rem;
            }

            .footer-content {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>

<body>
    <header>
        <h1>Confirmation de Réservation</h1>
        <p>Merci pour votre réservation !</p>
    </header>

    <main>
        <section class="form-section">
            <?php if (isset($confirmation) && $confirmation): ?>
                <div class="confirmation-message">
                    <h2>Réservation Confirmée 🎉</h2>
                    <p><strong>Match :</strong> <?= htmlspecialchars($match_info ?? 'Information non disponible') ?></p>
                    <p><strong>Type de billet :</strong> <?= htmlspecialchars($type_billet) ?></p>
                    <a href="calendrier.html" class="form-button">Réservation terminée</a>
                </div>
            <?php else: ?>
                <div class="error-message">
                    <h2>Erreur de Réservation</h2>
                    <p>Veuillez remplir tous les champs correctement ou une erreur s'est produite lors de l'enregistrement.
                    </p>
                    <a href="billet.html" class="form-button" style="background-color: #fff; color: #A50044;">Réessayer</a>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="copyright">
                <p>© 2024 FC Barcelona. Tous droits réservés.</p>
            </div>
            <div class="social-media">
                <span style="margin-right: 1rem; font-weight: bold;">Suivez-nous :</span>
                <a href="https://www.facebook.com/fcbarcelona" target="_blank"
                    title="Facebook officiel du FC Barcelona">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="https://twitter.com/FCBarcelona" target="_blank" title="Twitter officiel du FC Barcelona">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="https://www.instagram.com/fcbarcelona/" target="_blank"
                    title="Instagram officiel du FC Barcelona">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="https://www.youtube.com/user/fcbarcelona" target="_blank"
                    title="YouTube officiel du FC Barcelona">
                    <i class="fab fa-youtube"></i>
                </a>
                <a href="https://www.tiktok.com/@fcbarcelona" target="_blank" title="TikTok officiel du FC Barcelona">
                    <i class="fab fa-tiktok"></i>
                </a>
            </div>
        </div>
    </footer>
</body>

</html>