<?php 
require 'conn.php'; 

if (isset($_SESSION['user_id'])) { 
    header("Location: dashboard.php"); 
    exit; 
}

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

    if ($user && password_verify($password, $user['password'])) {
        $otp = rand(100000, 999999);
        $scadenza = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $stmt = $pdo->prepare("UPDATE otp_sessions SET usato = 1 WHERE user_id = :id AND usato = 0");
        $stmt->execute(['id' => $user['id']]);

        $stmt = $pdo->prepare("INSERT INTO otp_sessions (user_id, otp_code, scadenza) VALUES (:id, :otp, :scadenza)");
        $stmt->execute(['id' => $user['id'], 'otp' => $otp, 'scadenza' => $scadenza]);

        $_SESSION['temp_user_id'] = $user['id'];
        $_SESSION['auth_status'] = 'pending_2fa';

        if (sendOtpViaSMTP($user['email'], $otp)) {
            header("Location: verify.php");
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech - Accedi</title>

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
                <a href="register.php" class="btn-primary">Registrati</a>    
                <a href="index.php" class="btn-link">Home</a>       
        </nav>
    </div>
</header>

<hr class="divider">

<main class="container">
    <section class="hero">
        <h1>Accedi a<br><span>BiblioTech</span></h1>
        <p class="hero-text">
            Inserisci le tue credenziali per gestire i tuoi prestiti.
        </p>

        <div class="auth-form">
            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="username@panettipitagora.it" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="hero-actions">
                    <button type="submit" class="btn-primary btn-submit">Accedi</button>
                </div>
            </form>
        </div>
        
        <p class="hero-text" style="margin-top: 20px; font-size: 0.9rem;">
            Non hai un account? <a href="register.php" class="btn-link">Registrati</a>
        </p>
    </section>
</main>

<hr class="divider">

<footer class="footer">
    <p>BiblioTech - Sanese Giuseppe</p>
</footer>

</body>
</html>