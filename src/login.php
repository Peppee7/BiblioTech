<?php
require 'db_connect.php';

if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }

$error = '';

function sendOtpViaSMTP(string $toEmail, string $otp): bool {
    $sock = @fsockopen('mailpit', 1025, $errno, $errstr, 5);
    if (!$sock) return false;
    $r = fn() => fgets($sock, 512);
    $s = fn(string $c) => fwrite($sock, $c . "\r\n");
    $r();
    $s("EHLO bibliotech.local");
    while (($line = $r()) && substr($line, 3, 1) === '-');
    $s("MAIL FROM:<noreply@bibliotech.local>"); $r();
    $s("RCPT TO:<{$toEmail}>");                $r();
    $s("DATA");                                 $r();
    $msg  = "From: BiblioTech <noreply@bibliotech.local>\r\nTo: {$toEmail}\r\n";
    $msg .= "Subject: Codice di verifica BiblioTech\r\n";
    $msg .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n";
    $msg .= "Il tuo codice OTP e': {$otp}\r\nValido per 10 minuti.\r\n";
    $s($msg . "."); $r();
    $s("QUIT"); fclose($sock);
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, password, email FROM utenti WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    // VERIFICA PASSWORD
    if ($user && password_verify($password, $user['password'])) {

        // CREAZIONE OTP
        $otp = rand(100000, 999999);
        $scadenza = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // VECCHI OTP
        $stmt = $pdo->prepare("UPDATE otp_sessions SET usato = 1 WHERE user_id = :id AND usato = 0");
        $stmt->execute(['id' => $user['id']]);

        // INSERIRE OTP
        $stmt = $pdo->prepare("INSERT INTO otp_sessions (user_id, otp_code, scadenza) VALUES (:id, :otp, :scadenza)");
        $stmt->execute([
            'id' => $user['id'],
            'otp' => $otp,
            'scadenza' => $scadenza
        ]);

        // INSERIRE UTENTE IN SESSIONE
        $_SESSION['temp_user_id'] = $user['id'];
        $_SESSION['auth_status'] = 'pending_2fa';

        // INVIO OTP
        if (sendOtpViaSMTP($user['email'], $otp)) {
            header("Location: verify_2fa.php");
            exit;
        } else {
            $error = "Non riesco a inviare l'OTP. Controlla Mailpit.";
        }
    } else {
        $error = "Email o password sbagliata.";
    }
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech — Accedi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
<div class="auth-split">
    <div class="auth-brand">
        <div class="brand-content">
            <div class="brand-logo">📚</div>
            <h1 class="brand-name">BiblioTech</h1>
            <p class="brand-tagline">La biblioteca scolastica<br>nell'era digitale.</p>
            <ul class="brand-features">
                <li>Catalogo libri in tempo reale</li>
                <li>Gestione prestiti e restituzioni</li>
                <li>Accesso sicuro con verifica 2FA</li>
            </ul>
        </div>
    </div>
    <div class="auth-form-wrap">
        <form method="POST" class="auth-form">
            <div class="form-header">
                <h2>Bentornato</h2>
                <p>Inserisci le tue credenziali per accedere.</p>
            </div>
            <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="nome@scuola.it" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-primary">Accedi</button>
            <p class="form-footer">Non hai un account? <a href="register.php">Registrati</a></p>
        </form>
    </div>
</div>
</body>
</html>