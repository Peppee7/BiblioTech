<?php

// CONFIGURA DB
$host = getenv('DB_HOST');
if ($host === false || $host === '') {
    $host = 'db';
}

$db = getenv('DB_NAME');
if ($db === false || $db === '') {
    $db = 'bibliotech';
}

$user = getenv('DB_USER');
if ($user === false || $user === '') {
    $user = 'user';
}

$pass = getenv('DB_PASS');
if ($pass === false || $pass === '') {
    $pass = 'pass';
}

$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];


// CONNESSIONE DB
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Errore di connessione al database.");
}


// SESSIONE
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$sessionId = session_id();
$scadenza  = date('Y-m-d H:i:s', strtotime('+1 hour'));

// IP E USER AGENT
$ip = null;
if (isset($_SERVER['REMOTE_ADDR'])) {
    $ip = $_SERVER['REMOTE_ADDR'];
}

$userAgent = '';
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'], 0, 255);
}


// INSERIMENTO SESSIONE
$stmt = $pdo->prepare("
    INSERT INTO sessioni (session_id, scadenza, ip_address, user_agent)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE ultima_attivita = CURRENT_TIMESTAMP
");

$stmt->execute([
    $sessionId,
    $scadenza,
    $ip,
    $userAgent
]);

// LOG
if (isset($_SESSION['user_id'])) {

    $chk = $pdo->prepare("SELECT id FROM utenti WHERE id = ?");
    $chk->execute([$_SESSION['user_id']]);

    if ($chk->fetch()) {

        $upd = $pdo->prepare(
            "UPDATE sessioni SET user_id = ? WHERE session_id = ?"
        );
        $upd->execute([
            $_SESSION['user_id'],
            $sessionId
        ]);

    } else {
        session_destroy();
        header("Location: login.php");
        exit;
    }
}

?>