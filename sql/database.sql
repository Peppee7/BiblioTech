CREATE TABLE IF NOT EXISTS utenti ( 
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(300) NOT NULL,
    ruolo ENUM('user', 'admin') DEFAULT 'user',
    otp_code VARCHAR(6) DEFAULT NULL
) ENGINE=InnoDB;

INSERT INTO utenti (username, email, password, ruolo) VALUES
('Giuseppe', 'giuseppe@panettipitagora.it', '$2y$10$fKVma9SUzSkK/b05S938FOyb7lSV0fa3MzozYzLrnKjfcOM2C1bAe', 'admin'),
('Teo', 'teo@panettipitagora.it', '$2y$10$FxLfgnrF9rfx4P4SSb7PQ.UWT2vHeP2Buve1O9.hKuV517bUy5nnq', 'user'),
('Marco', 'marco@panettipitagora.it', '$2y$10$Fk2NaqQOm7YqGe.yYMHv1OjiT0j7.zMa2XIVTanLC0KfhTBV43wfq', 'user'),
('Mattia', 'mattia@panettipitagora.it', '$2y$10$XWNjODGPcyYE7ylfBhj0Y.J3q0ofiHSh1KD.RlsBzYuzL/7fD6tNy', 'user'),
('Luca', 'luca@panettipitagora.it', '$2y$10$CMitPaxzAtPF87wb0L4jLunnufboES7A454N/mQFkbZtLglvvMpqK', 'user');


CREATE TABLE IF NOT EXISTS libri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(255) NOT NULL,
    autore VARCHAR(255) NOT NULL,
    quantita_totale INT NOT NULL DEFAULT 1,
    quantita_disponibile INT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

INSERT INTO libri (titolo, autore, quantita_totale, quantita_disponibile) VALUES 
('I promessi sposi', 'Alessandro Manzoni', 10, 10),
('La coscienza di Zeno', 'Italo Svevo', 10, 10),
('Il Fu Mattia Pascal', 'Luigi Pirandello', 10, 10),
('Il sentiero dei nidi di ragno', 'Italo Calvino', 10, 10),
('Il nome della rosa', 'Umberto Eco', 10, 10);

CREATE TABLE IF NOT EXISTS sessioni ( 
    session_id VARCHAR(300) PRIMARY KEY,
    user_id INT NULL,
    data_inizio DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultima_attivita DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    scadenza DATETIME NOT NULL,
    data_logout DATETIME DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL, 
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS otp_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    otp_code  VARCHAR(6) NOT NULL,
    creato_il DATETIME DEFAULT CURRENT_TIMESTAMP,
    scadenza  DATETIME NOT NULL,
    usato TINYINT(1) DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
) ENGINE=InnoDB;



CREATE TABLE IF NOT EXISTS prestiti (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT NOT NULL,
    id_libro INT NOT NULL,
    data_inizio DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_fine DATETIME NULL,
    stato ENUM('attivo', 'restituito') DEFAULT 'attivo',
    FOREIGN KEY (id_utente) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (id_libro) REFERENCES libri(id) ON DELETE CASCADE
) ENGINE=InnoDB;