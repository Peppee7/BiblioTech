<?php
require 'conn.php';

if (isset($_SESSION['user_id'])) { 
    header("Location: dashboard.php"); 
    exit; 
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password =       $_POST['password'] ?? '';
    $confirm  =       $_POST['confirm']  ?? '';

    if (strlen($username) < 3) {
        $error = "Il nome utente deve avere almeno 3 caratteri.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Indirizzo email non valido.";
    } elseif (strlen($password) < 8) {
        $error = "La password deve avere almeno 8 caratteri.";
    } elseif ($password !== $confirm) {
        $error = "Le password non coincidono.";
    } else {
        $chk = $pdo->prepare("SELECT id FROM utenti WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $error = "Email già registrata.";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO utenti (username, email, password, ruolo) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $email, $hash]);
            header("Location: login.php?registered=1"); 
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech - Registrati</title>
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
            <a href="login.php" class="btn-primary">Accedi</a>  
            <a href="index.php" class="btn-link">Home</a>    
        </nav>
    </div>
</header>

<hr class="divider">

<main class="container">
    <section class="hero">
        <h1>Crea il tuo<br><span>Account</span></h1>
        <p class="hero-text">Registrati per consultare il catalogo e gestire i tuoi prestiti.</p>

        <div class="auth-form">
            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label for="username">Nome utente</label>
                    <input type="text" id="username" name="username" placeholder="es. mario_rossi" required
                           minlength="3" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="nome@scuola.it" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Minimo 8 caratteri" required minlength="8">
                </div>

                <div class="field">
                    <label for="confirm">Conferma Password</label>
                    <input type="password" id="confirm" name="confirm" placeholder="Ripeti la password" required>
                </div>

                <div class="hero-actions" style="margin-top: 30px;">
                    <button type="submit" class="btn-primary btn-submit">Registrati</button>
                </div>
            </form>
        </div>
        
        <p class="hero-text" style="margin-top: 25px; font-size: 0.9rem;">
            Hai già un account? <a href="login.php" class="btn-link">Accedi qui</a>
        </p>
    </section>
</main>

<hr class="divider">

<footer class="footer">
    <p>BiblioTech - Sanese Giuseppe</p>
</footer>

</body>
</html>