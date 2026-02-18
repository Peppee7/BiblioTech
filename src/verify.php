<?php
require 'conn.php';

// VERIFICA ACCESSO ESEGUITO
if (!isset($_SESSION['auth_status']) || $_SESSION['auth_status'] !== 'pending_2fa') {
    header("Location: login.php"); 
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otpInput = trim($_POST['otp'] ?? '');
    $userId   = (int) $_SESSION['temp_user_id'];

    // CERCA OTP MAI USATO
    $stmt = $pdo->prepare("
        SELECT id FROM otp_sessions
        WHERE user_id  = ?
          AND otp_code = ?
          AND usato    = 0
          AND scadenza > NOW()
        LIMIT 1
    ");
    $stmt->execute([$userId, $otpInput]);
    $otp = $stmt->fetch();

    if ($otp) {
        
        $pdo->prepare("UPDATE otp_sessions SET usato = 1 WHERE id = ?")
            ->execute([$otp['id']]);

        // RECUPERO DATI SESSIONE
        $uStmt = $pdo->prepare("SELECT id, ruolo, username FROM utenti WHERE id = ?");
        $uStmt->execute([$userId]);
        $user = $uStmt->fetch();

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['ruolo']    = $user['ruolo'];
        $_SESSION['username'] = $user['username'];

        // COLLEGAMENTO SESSIONEA A UTENTE
        $pdo->prepare("UPDATE sessioni SET user_id = ? WHERE session_id = ?")
            ->execute([$user['id'], session_id()]);

        unset($_SESSION['auth_status'], $_SESSION['temp_user_id']);

        header("Location: dashboard.php"); 
        exit;
    } else {
        $error = "Codice non valido o scaduto.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech - Verifica 2FA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header">
    <div class="container header-inner">
        <div class="logo">
            <a href="index.php">Biblio<span>Tech</span></a>
        </div>
        <nav class="nav">           
            </nav>
    </div>
</header>

<hr class="divider">

<main class="container">
    <section class="hero">
        <h1>Verifica<span><br>Sicurezza</span></h1>
        <p class="hero-text">
            Ti abbiamo inviato un codice OTP via email. Inseriscilo per continuare.
        </p>

        <div class="auth-form">
            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label for="otp">Codice di verifica (6 cifre)</label>
                    <input type="text" id="otp" name="otp" placeholder="000000"
                           maxlength="6" pattern="\d{6}" autocomplete="one-time-code"
                           required style="letter-spacing: .4em; font-size: 1.4rem; text-align:center;">
                </div>

                <div class="hero-actions" style="margin-top: 30px;">
                    <button type="submit" class="btn-primary btn-submit">Verifica e Accedi</button>
                </div>
            </form>
        </div>
        
        <p class="hero-text" style="margin-top: 25px; font-size: 0.9rem;">
            Problemi col codice? <a href="login.php" class="btn-link">Torna al login</a>
        </p>
    </section>
</main>

<hr class="divider">

<footer class="footer">
    <p>BiblioTech - Sanese Giuseppe</p>
</footer>

</body>
</html>