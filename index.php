<?php
// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'jeu_personnage');
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Récupération du personnage aléatoire si ce n'est pas encore défini
session_start();
if (!isset($_SESSION['personnage'])) {
    $result = $conn->query("SELECT * FROM personnages ORDER BY RAND() LIMIT 1");
    $_SESSION['personnage'] = $result->fetch_assoc();
}

$personnage = $_SESSION['personnage'];

$image = isset($personnage['image']) && !empty($personnage['image']) ? $personnage['image'] : 'default_image.jpg'; // Image par défaut si vide

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tentative = json_decode(file_get_contents("php://input"), true);
    $response = [];

    if ($tentative && isset($tentative['nom'])) {
        $nom = $tentative['nom'];

        $query = $conn->prepare("SELECT * FROM personnages WHERE nom = ?");
        $query->bind_param("s", $nom);
        $query->execute();
        $result = $query->get_result();

        if ($result->num_rows > 0) {
            $tentativePersonnage = $result->fetch_assoc();
            foreach ($personnage as $key => $value) {
                if ($key !== 'id' && $key !== 'nom') {
                    $response[$key] = [
                        'value' => $tentativePersonnage[$key],
                        'match' => $value === $tentativePersonnage[$key]
                    ];
                }
            }
            $response['image'] = isset($tentativePersonnage['image']) && !empty($tentativePersonnage['image']) ? $tentativePersonnage['image'] : 'default_image.jpg'; // Image par défaut
            
            $response['success'] = true;
            foreach ($response as $key => $data) {
                if ($key !== 'image' && $key !== 'success' && !$data['match']) {
                    $response['success'] = false; 
                    break;
                }
            }
        } else {
            $response['error'] = "Personnage non trouvé.";
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (isset($_GET['reset'])) {
    session_destroy();
    echo json_encode(['reset' => true]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devine le Personnage</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="game-container">
        <div class="game-box">
            <h1>Devine le Personnage</h1>
            <p class="instructions">Entrez le nom du personnage :</p>
            <form id="guess-form">
                <input type="text" id="guess-input" placeholder="Ex: Radhan Consort" autocomplete="off">
                <button type="submit" id="submit-btn">Soumettre</button>
            </form>
            <div id="success-message" style="display:none; text-align: center; margin-top: 30px;">
                <h2>Félicitations !</br> Vous avez trouvé vous êtes le real ELDEN LORD !</h2>
            </div>
            <div id="attempts"></div>
            <button id="reset-btn" style="display:none;">Recommencer</button>
        </div>
    </div>

    <script src="script.js"></script> 
</body>
</html>
