<?php
require 'conn.php';

//AGGIORNAMENTO SESSIONI
if (session_id()) {
    $stmt = $pdo->prepare("UPDATE sessioni SET scadenza = NOW() WHERE session_id = ?");
    $stmt->execute([session_id()]);
}

$_SESSION = []; 

// DISTRUZIONE COOKIE
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// DISTRUZIONE SESSIONE
session_destroy();

header("Location: login.php?logout=success");
exit;