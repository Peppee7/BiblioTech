<?php
require 'conn.php';

// VERIFICA ACCESSO
if (isset($_SESSION['user_id']) == false) {
    header("Location: login.php");
    exit;
}

$uid      = (int) $_SESSION['user_id'];
$ruolo    = $_SESSION['ruolo'];
$isAdmin  = ($ruolo == 'admin');

if (isset($_SESSION['username'])) {
    $username = htmlspecialchars($_SESSION['username']);
} else {
    $username = 'Utente';
}

// GESTIONE AZIONI (PRESTITO E RESTITUZIONE)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['loan']) && $isAdmin == false) {
        $lid = (int) $_POST['libro_id'];
        $chk = $pdo->prepare("SELECT id FROM prestiti WHERE id_utente=? AND id_libro=? AND stato='attivo'");
        $chk->execute([$uid, $lid]);
        if ($chk->fetch() == false) {
            $pdo->prepare("UPDATE libri SET quantita_disponibile = quantita_disponibile - 1 WHERE id=? AND quantita_disponibile > 0")->execute([$lid]);
            $pdo->prepare("INSERT INTO prestiti (id_utente, id_libro) VALUES (?,?)")->execute([$uid, $lid]);
        }
        header("Location: dashboard.php?tab=catalog&msg=loaned"); 
        exit;
    }

    if (isset($_POST['return']) && $isAdmin == true) {
        $pid = (int) $_POST['prestito_id'];
        $row = $pdo->prepare("SELECT id_libro FROM prestiti WHERE id=?");
        $row->execute([$pid]);
        $book = $row->fetch();
        if ($book) {
            $pdo->prepare("UPDATE prestiti SET data_fine=NOW(), stato='restituito' WHERE id=?")->execute([$pid]);
            $pdo->prepare("UPDATE libri SET quantita_disponibile = quantita_disponibile + 1 WHERE id=?")->execute([$book['id_libro']]);
        }
        header("Location: dashboard.php?tab=loans&msg=returned"); 
        exit;
    }
}

// RECUPERO DATI
$libri = $pdo->query("SELECT * FROM libri ORDER BY titolo ASC")->fetchAll();
$mieiLibri = [];
if ($isAdmin == false) {
    $s = $pdo->prepare("SELECT id_libro FROM prestiti WHERE id_utente=? AND stato='attivo'");
    $s->execute([$uid]);
    foreach($s->fetchAll() as $r) { $mieiLibri[] = $r['id_libro']; }
}

if ($isAdmin == true) {
    $prestitiAttivi = $pdo->query("SELECT p.id, u.username, l.titolo, p.data_inizio FROM prestiti p JOIN utenti u ON p.id_utente = u.id JOIN libri l ON p.id_libro = l.id WHERE p.stato = 'attivo' ORDER BY p.data_inizio ASC")->fetchAll();
} else {
    $s = $pdo->prepare("SELECT l.titolo, l.autore, p.data_inizio, p.data_fine, p.stato FROM prestiti p JOIN libri l ON p.id_libro = l.id WHERE p.id_utente = ? ORDER BY p.data_inizio DESC");
    $s->execute([$uid]);
    $storico = $s->fetchAll();
}

if (isset($_GET['tab'])) { $tab = $_GET['tab']; } else { $tab = ($isAdmin ? 'loans' : 'catalog'); }
if (isset($_GET['msg'])) { $msg = $_GET['msg']; } else { $msg = ''; }
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

<main class="container dash-layout">
    <aside class="dash-sidebar">
        <section class="hero hero-actions" style="text-align: left; padding: 0;">
            <nav class="sidebar-nav">
                <?php if ($isAdmin): ?>
                    <button class="nav-btn btn-primary btn-link <?php echo ($tab=='loans'?'active':''); ?>" onclick="location.href='?tab=loans'">Prestiti Attivi</button>
                <?php endif; ?>
                <button class="nav-btn btn-primary btn-link <?php echo ($tab=='catalog'?'active':''); ?>" onclick="location.href='?tab=catalog'">Catalogo</button>
                <?php if (!$isAdmin): ?>
                    <button class="nav-btn btn-link <?php echo ($tab=='history'?'active':''); ?>" onclick="location.href='?tab=history'">I miei prestiti</button>
                <?php endif; ?>
            </nav>
        </section>
    </aside>

    <section class="dash-main">
        <?php if ($msg == 'loaned'): ?>
            <div class="alert-ok">Libro preso in prestito con successo!</div>
        <?php endif; ?>
        <?php if ($msg == 'returned'): ?>
            <div class="alert-ok">Restituzione registrata correttamente.</div>
        <?php endif; ?>

        <div class="hero" style="text-align: left; padding-top: 0;">
            <?php if ($tab == 'catalog'): ?>
                <h1>Catalogo <span>Libri</span></h1>
                <div class="books-grid">
                    <?php foreach ($libri as $l): 
                        $disp = (int)$l['quantita_disponibile'];
                        $inPossesso = in_array($l['id'], $mieiLibri);
                    ?>
                        <div class="book-card">
                            <h3><?php echo htmlspecialchars($l['titolo']); ?></h3>
                            <p><?php echo htmlspecialchars($l['autore']); ?></p>
                            <div class="book-footer">
                                <?php if (!$isAdmin): ?>
                                    <?php if ($disp > 0 && !$inPossesso): ?>
                                        <form method="POST">
                                            <input type="hidden" name="libro_id" value="<?php echo $l['id']; ?>">
                                            <button type="submit" name="loan" class="btn-primary btn-submit">Prendi</button>
                                        </form>
                                    <?php elseif ($inPossesso): ?>
                                        <span class="badge-info">In possesso</span>
                                    <?php else: ?>
                                        <span class="badge-none">Esaurito</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="btn-link">Copie: <?php echo $disp; ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($tab == 'loans' && $isAdmin): ?>
                <h1>Gestione <span>Prestiti</span></h1>
                <table class="dash-table">
                    <thead>
                        <tr><th>Utente</th><th>Libro</th><th>Azione</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($prestitiAttivi as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p['username']); ?></td>
                            <td><?php echo htmlspecialchars($p['titolo']); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="prestito_id" value="<?php echo $p['id']; ?>">
                                    <button type="submit" name="return" class="btn-primary btn-submit">Restituisci</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php elseif ($tab == 'history' && !$isAdmin): ?>
                <h1>I miei <span>Prestiti</span></h1>
                <table class="dash-table">
                    <thead>
                        <tr><th>Titolo</th><th>Inizio</th><th>Stato</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($storico as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['titolo']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($s['data_inizio'])); ?></td>
                            <td><?php echo ($s['stato'] == 'attivo' ? 'In corso' : 'Restituito'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </section>
</main>

<hr class="divider">

<footer class="footer">
    <p>BiblioTech - Sanese Giuseppe</p>
</footer>

</body>
</html>