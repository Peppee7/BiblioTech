<?php
require 'conn.php';

// VERIFICA ACCESSO
if (isset($_SESSION['user_id']) == false) {
    header("Location: login.php");
    exit;
}

$uid = (int) $_SESSION['user_id'];
$ruolo = $_SESSION['ruolo'];
$isAdmin = ($ruolo == 'admin');

if (isset($_SESSION['username'])) {
    $username = htmlspecialchars($_SESSION['username']);
} else {
    $username = 'Utente';
}



$GROQ_API_KEY = "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";
$url = "https://api.chucknorris.io/jokes/random";

$ch = curl_init($url);      // Crea oggetto "client curl"
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//curl_setopt($ch, CURLOPT_POST, true);

/*
    {
        "model": "llama-3.3-70b-versatile",
        "messages": [{
            "role": "user",
            "content": "Explain the importance of fast language models"
        }]
    }
*/

$request_array = array();

$request_array["model"] = "llama-3.3-70b-versatile";
$request_array["messages"] = array();       // Creare array associativo con i msg

//$json_string = json_encode($request_array);     // Trasformare array associativo in stringa JSON

//curl_setopt($ch, CURLOPT_POSTFIELDS, $json_string);     // Impostare il payload POST

/*curl_setopt($ch, CURLOPT_HTTPHEADER, [      // Se le API rischiedono autentificazione
    "Accept: application/json",
    "Authorization: Bearer QUI:API:KEY"
]);
*/
$risp = curl_exec($ch);         // Richiesta effettiva

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);         // Status code dalla risposta HTTP

//print($http_code);

$response_array = json_decode($risp, true);     // Trasforma la stringa JSON in array associativo


?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>BiblioTech - Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header">
    <div class="container header-inner">
        <div class="logo">
            <a href="index.php">Biblio<span>Tech</span></a>
        </div>
        <nav class="nav">
            <span>Ciao, <b><?php echo $username; ?></b></span>
            <a href="logout.php" class="btn-primary" style="margin-left: 15px;">Esci</a>
        </nav>
    </div>
</header>

<hr class="divider">

<h3><?php echo htmlspecialchars($l['titolo']); ?></h3>
<p><?php echo htmlspecialchars($l['autore']); ?></p>
<p>
    <?php print($response_array['value']);        // Stampa chiave 'value' dell'array associativo
    ?>
</p>


<hr class="divider">

<footer class="footer">
    <p>BiblioTech - Sanese Giuseppe</p>
</footer>

</body>
</html>