CREATE TABLE IF NOT EXISTS utente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(300) NOT NULL,
    ruolo ENUM('user', 'admin') DEFAULT 'user',
    otp_code VARCHAR(6) DEFAULT NULL
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS sessione (
    session_id VARCHAR(300) PRIMARY KEY,
    user_id INT NULL,
    data_inizio DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultima_attivita DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    scadenza DATETIME NOT NULL,
    data_logout DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE SET NULL
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS libro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(255) NOT NULL,
    autore VARCHAR(255),
    quantita_totale INT NOT NULL,
    quantita_disponibile INT NOT NULL
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS prestito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    id_libro INT NOT NULL,
    data_inizio DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_fine DATETIME NULL,
    stato ENUM('attivo', 'restituito') DEFAULT 'attivo',
    FOREIGN KEY (id_utente) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (id_libro) REFERENCES libri(id) ON DELETE CASCADE
) ENGINE=InnoDB;



INSERT INTO libri (titolo, autore, quantita_totale, quantita_disponibile) VALUES 
('I promessi sposi', 'Alessandro Manzoni', 10, 10),
('La coscienza di Zeno', 'Italo Svevo', 10, 10),
('Se questo è un uomo', 'Primo Levi', 10, 10),
('Il sentiero dei nidi di ragno', 'Italo Calvino', 10, 10),
('Io non ho paura', 'Niccolò Ammaniti', 10, 10);